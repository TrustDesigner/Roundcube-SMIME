<?php
/**
 * Shotnget Mail / shotnget_sign
 *
 * Class used to perform the signature
 * Signature can be performed by openssl or shotnget
 *
 * @version 1.0
 * @author Trust Designer, Tourte Alexis
 * @url
 */

require_once('mail.php');
require_once('shotngetapi/shotnget_api.php');
require_once('shotnget_request.php');
require_once('shotnget_certificate.php');

class shotnget_sign {

  private $rand;
  private $message = false;
  private $myMail;
  private $mailTo;

  const CA_DIRECTORY = "/etc/ssl/certs";

  function __construct($message, $myMail, $rand = null, $mailTo = false) {
    $this->rand = $rand;
    $this->message = $message;
    $this->myMail = $myMail;
    $this->mailTo = $mailTo;
  }

  /**
   * This function return the certidficate details
   * @param $data Deformated certificate
   */
  private function get_signature_info($x509cert_content) {
    // Format sign informations
    $data = openssl_x509_parse($x509cert_content);
    $certMsg = "Signed by "
      ."<div style='display: inline;' id='shotnget_sign_author' >"
      .$data['subject']['emailAddress']
      ."</div>";
    if ($data['subject']['O'] != "")
      $certMsg .= ", ".$data['subject']['O'];
    if ($data['subject']['ST'] != "")
      $certMsg .= ", ".$data['subject']['ST'];
    if ($data['subject']['C'])
      $certMsg .= ", ".$data['subject']['C'];
    return $certMsg;
  }


  /**
   * This function sign the message with the shotnget openSSL engine
   * @param $response Response object from the smartphone (Containe the deformated XML data)
   * @param $message The message to sign (tmp file)
   * @param &$fileId The id of the selected file
   * @param $mail The mail address of the user
   * @return $res true with the signed data / false with the error message
   */
  private function sign_message($response, $message, &$fileId, $mail) {
    if (($listCmd = $response->getCmdByValue(CCmd::CMD_LIST)) == null)
      return array('ret' => false, 'error' => 'error_parse_response');
    if (($fileId = $listCmd->getSelectedFileId()) == null)
      return array('ret' => false, 'error' => 'error_parse_response');
    if (($file = $listCmd->getSelectedFile()) == null)
      return array('ret' => false, 'error' => 'error_parse_response');
    if (($data = $file->getParamByType(CParam::TYPE_ID_CERT)->getValue()) == null)
      return array('ret' => false, 'error' => 'cert_not_found', 'email' => $mail);

    $cert_content = shotnget_certificate::format_certificate($data);

    $ret = shotnget_certificate::verify_certificate_with_mail($cert_content, $mail);
    if ($ret['ret'] === false)
      return $ret;

    $cert = tempnam("", "cert");
    $extracert = tempnam("", "extracert");
    $signed = tempnam("", "signed");

    $hasExtraCert = false;
    file_put_contents($cert, $cert_content);

    // Save certificate for encryption later
    file_put_contents(shotnget_certificate::get_certificate($mail), $cert_content);

    $params = $file->getParams();
    foreach ($params as $param) {
      if ($param->getType() == CParam::TYPE_ID_CERT_CA) {
	$extra = shotnget_certificate::format_certificate($param->getValue());
	file_put_contents($extracert, $extra."\n", FILE_APPEND);
        $hasExtraCert = true;
      }
    }

    if ($hasExtraCert == true)
      exec("openssl smime -sign -engine ".Config::$ENGINE." -in ".$message." -signer ".$cert." -keyform engine -certfile ".$extracert." -out ".$signed);
    else
      exec("openssl smime -sign -engine ".Config::$ENGINE." -in ".$message." -signer ".$cert." -keyform engine -out ".$signed);
    $signedContent = file_get_contents($signed);
    @unlink($signed);
    @unlink($cert);
    @unlink($extracert);
    return array('ret' => true, 'res' => $signedContent);
  }

  /**
   * This function get the hash information form the signed message containing the shotnget protocol
   * @param $signedContent The raw signed message with shotnget protocol
   * @return true with the hash details / false with the error mesage
   */
  private function get_hash_data($signedContent) {
    $mailDeserialize = new mail($signedContent, true);

    $deserialized = null;
    foreach ($mailDeserialize->parts as $part) {
      if ($part['headers']['Content-Type'] == "application/x-pkcs7-signature") {
        $deserialized = base64_decode($part['content']);
        $dataToChange = $part['content'];
      }
    }

    if ($deserialized == null) {
      return array('ret' => false, 'error' => 'error_parse_response');
    }

    if (($pos = strrpos($deserialized, "shotnget:")) === false)
      return array('ret' => false, 'error' => 'error_parse_response');

    $signHash = substr($deserialized, $pos + 9, 119);

    list($data_size, $hash_size, $hash_type, $hash_data) = explode(":", $signHash, 4);

    $res = array(substr($hash_data, 0, intval($hash_size)), $data_size, $hash_size, $hash_type, $dataToChange);
    return array('ret' => true, 'res' => $res);
  }

  /**
   * This function format the signature with the signed data received form the smartphone
   * @param $response Response object from the smartphone (Containe the deformated XML data)
   * @param $dataToChange Containe the base64 der containing the pkcs7 data (Correspond to the p7s attachement)
   * @param $signedContent Complete signed message with the shotnget protocol
   * @param $data_size Contains the length of the hash data (128 or 256)
   * @return $res Complete signed message with the hash included
   */
  private function format_signature($response, $dataToChange, $signedContent, $data_size) {
    $deserialized = base64_decode($dataToChange);

    if (($cbcCmd = $response->getCmdByValue(CCmd::CMD_CBC)) == null)
      return false;
    if (($pos = strrpos($deserialized, "shotnget:")) === false)
      return false;

    if (($result = $cbcCmd->getResult()) != null) {
      if ($result != "0")
        return false;
    }

    $sign = base64_decode($cbcCmd->getData());

    $toReplace = substr($deserialized, $pos, $data_size);
    $deserialized = str_replace($toReplace, $sign, $deserialized);
    $res = str_replace($dataToChange, chunk_split(base64_encode($deserialized), 64, "\n"), $signedContent);
    return $res;
  }

  /**
   * This function format the signature with the signed data received form the smartphone
   * @param $rand for the transfert
   */
  public function close_connexion() {
    if ($this->rand !== null)
      shotnget_request::send_request(shotnget_request::format_response_request(), $this->rand);
  }

  /**
   * This function format the signature with the signed data received form the smartphone
   * @return $ret with false if an error has occured (The error will be stored in $ret['error']
   *         $ret with true if all is good and the signed mail in $ret['data']
   */
  public function perform_signature() {
    if ($this->rand === null || CUtils::checkRand($this->rand) === false)
      return array('ret' => false, 'error' => 'error_sign');
    
    $filename = Config::$TEMP_PATH.$this->rand;

    $message = tempnam("", "messageTmp");
    file_put_contents($message, $this->message);

    @unlink($filename.".rep");
    @unlink($filename.".cmds");

    $ret = shotnget_request::wait_response($this->rand);
    if ($ret['ret'] === false)
      return $ret;
    
    if (($response = shotnget_request::get_response($this->rand)) === false)
      return array('ret' => false, 'error' => 'error_parse_response');

    $request = shotnget_request::init_request($response, $this->rand);
    shotnget_request::send_request($request, $this->rand);

    $ret = shotnget_request::wait_response($this->rand);
    if ($ret['ret'] === false)
      return $ret;


    if (($response = shotnget_request::get_response($this->rand)) === false)
      return array('ret' => false, 'error' => 'error_parse_response');

    $ret = $this->sign_message($response, $message, $fileId, $this->myMail);
    if ($ret['ret'] === false)
      return $ret;
    $signedContent = $ret['res'];

    $ret = $this->get_hash_data($signedContent);
    if ($ret['ret'] === false)
      return $ret;

    list($hash_data, $data_size, $hash_size, $hash_type, $dataToChange) = $ret['res'];

    $request = shotnget_request::sign_request($this->rand, $hash_data, $hash_type, $fileId);

    shotnget_request::send_request($request, $this->rand);
    $ret = shotnget_request::wait_response($this->rand);
    if ($ret['ret'] === false)
      return $ret;

    if (($response = shotnget_request::get_response($this->rand)) === false)
      return array('ret' => false, 'error' => 'error_parse_response');

    if (($data = $this->format_signature($response, $dataToChange, $signedContent, $data_size)) === false)
      return array('ret' => false, 'error' => 'error_sign');

    shotnget_request::send_request(shotnget_request::format_response_request(), $this->rand);
    return array('ret' => true, 'data' => $data);
  }

  /**
   * This function format the signature with the certificates on the server
   * @return $ret with false if an error has occured (The error will be stored in $ret['error']
   *         $ret with true if all is good and the signed mail in $ret['data']
   */
  public function perform_basic_signature() {
    $cert = shotnget_certificate::get_certificate($this->myMail, false);
    $privateKey = shotnget_certificate::get_private_key($this->myMail, file_get_contents($cert), true);
    $extracerts = shotnget_certificate::get_extra_certificate($this->myMail);

    if (file_exists($cert) == false)
      return array('ret' => false, 'error' => 'cert_not_found', 'email' => $this->myMail);
    if (file_exists($privateKey) == false)
      return array('ret' => false, 'error' => 'cert_not_found', 'email' => $this->myMail);
    if (file_exists($extracerts) == false)
      return array('ret' => false, 'error' => 'cert_not_found', 'email' => $this->myMail);

    $message = tempnam("", "signedTmp");
    $signed = tempnam("", "signed");

    file_put_contents($message, $this->message);

    if (openssl_pkcs7_sign($message, $signed, "file://".$cert,
  			   array($privateKey, $_SESSION['userHash']),
  			   $headers, PKCS7_DETACHED, $extracerts)) {
      $data = file_get_contents($signed);
      @unlink($signed);
      @unlink($message);
      return array('ret' => true, 'data' => $data);
    }
    @unlink($signed);
    @unlink($message);
    $error = "";
    while ($msg = openssl_error_string())
      $error .= "\n".$msg;
    return array('ret' => false, 'error' => 'error_sign', 'openssl_errors' => $error);
  }

  /**
   * This function is used to verify a signed mail
   * @return false if failed / true and the content of the signed message with the signer email otherwise
   */
  public function verify_signature() {
    $mailCertFile = tempnam("", "mailCertFile");
    $fullmessage = tempnam("", "fullmessage");
    $extracert_file = tempnam("", "extracert_file");
    $message = tempnam("", "message");

    if ($this->mailTo === false || $this->message === false)
      return array('ret' => false, 'error' => 'error_verify_sign');

    file_put_contents($message, $this->message);

    $extracert = shotnget_certificate::save_cert($message, $this->myMail);
    foreach ($extracert as $certificate) {
      file_put_contents($extracert_file, $certificate."\n", FILE_APPEND);
    }

    if (openssl_pkcs7_verify($message, PKCS7_DETACHED, $mailCertFile,
			     array(shotnget_sign::CA_DIRECTORY), $extracert_file, $fullmessage)) {
      $x509cert = file_get_contents($mailCertFile);
      $data = openssl_x509_parse($x509cert);
      if ($data['subject']['emailAddress'] == $this->myMail) {
	$infos = $this->get_signature_info($x509cert);

	$ret = file_get_contents($fullmessage);

	if (openssl_x509_checkpurpose($data, X509_PURPOSE_SMIME_ENCRYPT) == true)
	  file_put_contents(shotnget_certificate::get_certificate($this->myMail), $x509cert);

	@unlink($mailCertFile);
        @unlink($fullmessage);
	@unlink($extracert_file);
	@unlink($message);

	return array('ret' => true, 'data' => $ret, 'info' => $infos);
      }
      $error = "error_verify_mail_address";
    }
    
    @unlink($fullmessage);
    @unlink($mailCertFile);
    @unlink($extracert_file);
    @unlink($message);

    $openssl_error = "";
    while ($msg = openssl_error_string())
      $openssl_error .= "\n".$msg;
    return array('ret' => false, 'error' => $error, 'openssl_error' => $openssl_error);
  }

};

?>