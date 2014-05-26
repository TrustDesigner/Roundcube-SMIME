<?php

require_once('shotngetapi/shotnget_api.php');

class shotnget_uncrypt {

  private $message = null;
  private $userMail = null;
  private $mailTo = null;
  private $isSigned = false;
  private $rand = null;
  private $password = null;

  /**
   * Constructor
   * @param $message Raw message in text format
   * @param $userMail The user email address
   * @param $mailTo Array containing the receivers address mail
   * @param $isSigned Boolean true if the message is signed / false otherwise
   * @param $rand The rans user for shotnget api
   * @param $password The password for the private key (Only used when the uncrypt is used without shotnget)
   */
  function __construct($message, $userMail, $mailTo, $isSigned = false, $rand = null, $password = null) {
    $this->message = $message;
    $this->userMail = $userMail;
    $this->mailTo = $mailTo;
    $this->isSigned = $isSigned;
    $this->password = $password;
    $this->rand = $rand;
  }

  /**
   * Deserialise response and return the files
   * @param $response Response object from the smartphone
   * @return true with the files or false ith the error message
   */
  private function get_files($response) {
    if (($listCmd = $response->getCmdByValue(CCmd::CMD_LIST)) == null)
      return array('ret' => false, 'error' => 'error_parse_response');

    if (($files = $listCmd->getFile()) == null)
      return array('ret' => false, 'error' => 'no_cert_found');

    if (!is_array($files)) {
      $files = array($files);
    }

    return array('ret' => true, 'files' => $files);
  }

  /**
   * Execute the shell command for openssl message uncrypt
   * @param $message Filename containing the message
   * @param $cert Filename containing the certificates
   * @param $response Filename where the stdout will be stored
   * @param $err Filename where the errors (stderr) messsages will be stored
   * @param $out Filename where the decryt result will be stored
   */
  private function exec_openssl_uncrypt($message, $cert, $result, $err, $out = "/dev/null") {
    exec("openssl smime -decrypt -in ".$message." -engine ".Config::$ENGINE." -keyform engine -out ".$out." -recip ".$cert." > ".$result." 2> ".$err);
  }

  /**
   * This function is called to uncrypt a mail when the files where given from the smartphone
   * @param $message Filename of the file containing the raw message (in text format)
   * @return true with the uncrypted email or false with the corresponding error message
   */
  private function uncrypt_with_files($message, $files) {
    $cert = tempnam("", "cert");
    $result = tempnam("", "result");
    $err = tempnam("", "err");

    foreach ($files as $file) {
      $data = $file->getParamByType(CParam::TYPE_ID_CERT)->getValue();
      $cert_content = shotnget_certificate::format_certificate($data);
      file_put_contents($cert, $cert_content);

      $ret = shotnget_certificate::verify_certificate_with_mail($cert_content, $this->userMail, false);
      if ($ret['ret'] == true) {
	$this->exec_openssl_uncrypt($message, $cert, $result, $err);
	$error = file_get_contents($err);

	if (strpos($error, "no recipient matches certificate") === false) {
	  
	  $res = file_get_contents($result);
	  if ($res != "") {

	    @unlink($err);
	    @unlink($result);
	    @unlink($cert);

	    $request = shotnget_request::decrypt_request($this->rand, $res, $file->getId());
	    shotnget_request::send_request($request, $this->rand);

	    $ret = shotnget_request::wait_response($this->rand);
	    if ($ret['ret'] === false)
	      return $ret;
	    if (($response = shotnget_request::get_response($this->rand)) === false)
	      return array('ret' => false, 'error' => 'error_parse_response');

	    if (($cbcCmd = $response->getCmdByValue(CCmd::CMD_CBC)) == null)
	      return array('ret' => false, 'error' => 'error_parse_response');

	    if (($result = $cbcCmd->getResult()) != null) {
	      if ($result != "0")
		return array('ret' => false, 'error' => 'error_parse_response');
	    }

	    $dec = base64_decode($cbcCmd->getData());

	    shotnget_request::send_request(shotnget_request::format_response_request(), $this->rand);
	    return array('ret' => true, 'data' => array('key' => $dec, 'cert' => $cert_content));
	  }
	}
      }
    }
    @unlink($err);
    @unlink($result);
    @unlink($cert);
    return array('ret' => false, 'error' => 'cert_not_found_decrypt');
  }

  /**
   * Uncrypt the message with the uncrypted key and certificate given
   * @param $certificate The certificate used to uncrypt this email
   * @param $key The key obtained from the uncipher command with shotnget smartphone
   * @return true with the uncrypted message or false
   */
  public function uncrypt_with_key($certificate, $key) {
    $cert = tempnam("", "cert");
    $out = tempnam("", "out");
    $err = tempnam("", "err");
    $message = tempnam("", "messageTmp");

    file_put_contents($cert, $key."\n");
    file_put_contents($cert, $certificate, FILE_APPEND);
    file_put_contents($message, $this->message);

    $this->exec_openssl_uncrypt($message, $cert, "/dev/null", $err, $out);
    $res = file_get_contents($out);
    $error = file_get_contents($err);
    @unlink($err);
    @unlink($cert);
    @unlink($out);
    @unlink($message);
    return array('ret' => !strpos($error, 'error'), 'data' => $res);
  }

  /**
   * Uncrypt the key to uncrypt the message
   * return false with corresponding error or true with an array contaning the uncrypted key and the used certificate
   */
  public function uncrypt_mail() {
    if ($this->rand == null || $this->message == null)
      return array('ret' => false, 'error' => 'error_parse_response');

    $randPath = Config::$TEMP_PATH;
    $filename = $randPath.$this->rand;

    $message = tempnam("", "crypted");
    file_put_contents($message, $this->message);

    if ($this->isSigned === true) {
      $shotnget_sign = new shotnget_sign($message, $this->userMail, $this->rand, $this->mailTo);
      $ret = $shotnget_sign->verify_signature();
      if ($ret['ret'] === false)
	return $ret;
      file_put_contents($message, $ret['data']);
    }

    @unlink($filename.".rep");
    @unlink($filename.".cmds");
    file_put_contents($filename.".rand", "");

    $ret = shotnget_request::wait_response($this->rand);
    if ($ret['ret'] === false)
      return $ret;

    if (($response = shotnget_request::get_response($this->rand)) === false)
      return array('ret' => false, 'error' => 'error_parse_response');
    if (($request = shotnget_request::init_request($response, $this->rand, false)) == false)
      return array('ret' => false, 'error' => 'error_parse_response');

    shotnget_request::send_request($request, $this->rand);
    $ret = shotnget_request::wait_response($this->rand);
    if ($ret['ret'] === false)
      return $ret;

    if (($response = shotnget_request::get_response($this->rand)) === false)
      return array('ret' => false, 'error' => 'error_parse_response');

    $ret = $this->get_files($response);
    if ($ret['ret'] === false)
      return $ret;

    return $this->uncrypt_with_files($message, $ret['files']);
  }

  /**
   * Default uncrypt mail with the private key stored on the server
   * @return true with the uncrypted mail / false with the corresponding error
   */
  public function uncrypt_basic_mail() {
    $my_cert = shotnget_certificate::get_certificate($this->userMail, true);
    if (file_exists($my_cert) == false)
      return array('ret' => false, 'error' => 'cert_not_found', 'email' => $this->userMail);
    $priv_key = shotnget_certificate::get_private_key($this->userMail, file_get_contents($my_cert), true);
    if (file_exists($priv_key) == false)
      return array('ret' => false, 'error' => 'private_key_not_found', 'email' => $this->userMail);

    $message = tempnam("", "messageTmp");
    $uncryptedMessage = tempnam("", "uncryptedMessage");
    file_put_contents($message, $this->message);

    if (openssl_pkcs7_decrypt($message, $uncryptedMessage, $my_cert, array($priv_key, $this->password)) == true) {

      $res = file_get_contents($uncryptedMessage);

      @unlink($uncryptedMessage);
      @unlink($message);

      return array('ret' => true, 'data' => $res);      
    }
    @unlink($uncryptedMessage);
    @unlink($message);

    $error = "";
    while ($msg = openssl_error_string())
      $error .= $msg;
    return array('ret' => false, 'error' => $error);
  }

  /**
   * Encrypt the email
   * @return true with the encrypted email / false with the corresponding error
   */
  public function encrypt_mail() {
    if (file_exists(shotnget_certificate::get_certificate($this->userMail, false)) == false)
      return array('ret' => false, 'error' => 'first_encryption_use');

    $pubkey = array();
    foreach ($this->mailTo as $emailAddress) {
      if (file_exists(shotnget_certificate::get_certificate($emailAddress, false)) == false) {
	return array('ret' => false, 'error' => 'cert_not_found', 'email' => $emailAddress);
      }
      $pubkey[] = file_get_contents(shotnget_certificate::get_certificate($emailAddress, false));
    }
    $enc = tempnam("", "encrypted");
    $message = tempnam("", "messageTmp");
    file_put_contents($message, $this->message);
    if (openssl_pkcs7_encrypt($message, $enc,
			      $pubkey, array(), 0, 1)) {
      $data = file_get_contents($enc);
      @unlink($enc);
      @unlink($message);
      return array('ret' => true, 'data' => $data);
    }
    @unlink($enc);
    @unlink($message);

    $error = "";
    while ($msg = openssl_error_string())
      $error .= $msg;
    return array('ret' => false, 'error' => $error);
  }

};

?>