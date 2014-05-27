<?php
/**
 * Shotnget SMIME / shotnget_request
 *
 * This class is used for request functions
 *
 * @version 1.0
 *
 * shotnget_smime is a roundcube plugin used for SMIME signature / decipherment and connections
 * Copyright (C) 2007-2014 Trust Designer,  Tourte Alexis
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
require_once('shotngetapi/shotnget_api.php');

class shotnget_request {
  

  /**
   * This function cancel the wait for the smartphone response
   * @param $rand for the transfert
   */
  public static function cancel_action($rand) {
    $filename = Config::$TEMP_PATH.$rand;

    $file = fopen($filename.'.rand', 'w+');
    fputs($file, 'canceled');
    fclose($file);

    $file = fopen($filename.'.rep', 'w+');
    fclose($file);
  }

  /**
   * This function waits for the response of the smartphone
   * @param $rand for the transfert
   * @return true / false with the error
   */
  public static function wait_response($rand) {
    $waitResponse = new CWaitResponse($rand, Config::$TEMP_PATH, 60);
    $filename = Config::$TEMP_PATH.$rand;

    $res = $waitResponse->hasReplied();
    
    if ($res !== CErrors::RESULT_NO_ERROR) {
      $error = 'server_timeout';//$res == CErrors::RESULT_TIMEOUT ? "Server timeout" : "Server error";
      $ret = array('ret' => false, 'error' => $error);
    } else {
      $data = file_get_contents($filename.'.rand');
      if ($data == 'canceled')
	$ret = array('ret' => false, 'error' => "Action canceled");
      else
	$ret = array('ret' => true);
    }
    
    return $ret;
  }

  /**
   * This function search for the response of the smartphone
   * @param $rand Rand for the transfert
   * @return false / $response
   */
  public static function get_response($rand) {
    $randPath = Config::$TEMP_PATH;
    $filename = $randPath.$rand;

    $data = file_get_contents($filename.'.rand');

    $response = new CResponse($randPath, null);
    if ($response->parseData($data) == false)
      return false;
    return $response;
  }

  public static function init_login_request($response, $rand) {
    $randPath = Config::$TEMP_PATH;

    $cmdInit = $response->getCmdByValue(CCmd::CMD_INIT);

    $request = new CRequest('');
    $request->setSHOTNGETResponse($response);
    $request->initWithRand($rand);

    $kpub = file_get_contents(Config::$KPUB);
    $request->setKpub($kpub);

    $parameters = new CRequestParameters();
    $parameters->setRand($rand);
    $parameters->setRandPath($randPath);

    $file = new CFile(CFile::TYPE_WEBACCOUNT);

    $param = new CParam(CParam::TYPE_ID_UID);
    $param->addOpt(CParam::OPT_NEEDED);
    $param->addOpt(CParam::OPT_WRITABLE);
    $param->addOpt(CParam::OPT_UNIQUE);
    $file->addParam($param);

    $param = new CParam(CParam::TYPE_PWD);
    $opt = CParam::OPT_NEEDED.CParam::OPT_WRITABLE.CParam::OPT_UNIQUE;
    $param->addOpt($opt);
    $file->addParam($param);

    $param = new CParam(CParam::TYPE_WA_KEY32);
    $param->addOpt(CParam::OPT_HIDDEN);
    $file->addParam($param);

    //build a new comand
    $cmd = new CCmdAuth(CCmdAuth::TYPEPWD_ALPHANUMERIC, 12, $file);
    $cmd->setLabel('Webmail');
    $request->addCmd($cmd);

    $request->setSHOTNGETKpub($cmdInit->getKpub());
    return $request;
  }

  public static function init_upload_cert_request($response, $rand, $data) {
    $randPath = Config::$TEMP_PATH;

    $cmdInit = $response->getCmdByValue(CCmd::CMD_INIT);

    $request = new CRequest('');
    $request->setSHOTNGETResponse($response);
    $request->initWithRand($rand);

    $kpub = file_get_contents(Config::$KPUB);
    $request->setKpub($kpub);

    $parameters = new CRequestParameters();
    $parameters->setRand($rand);
    $parameters->setRandPath($randPath);

    //build a new comand
    $cmd = new CCmdCert();
    $cmd->setLabel('Webmail');
    $cmd->setMode(CCmdCert::MODE_P12);
    $cmd->setData($data);
    $request->addCmd($cmd);

    $request->setSHOTNGETKpub($cmdInit->getKpub());
    return $request;
  }

  /**
   * This function format the init request when a smartphone started to connect with shotnget
   * @param $response Response object from the smartphone (Containe the deformated XML data)
   * @param $rand Rand for the transfert
   * @param $select Decide if the use have to select a file
   * @return $request
   */
  public static function init_request($response, $rand, $select = true) {
    $randPath = Config::$TEMP_PATH;

    $cmdInit = $response->getCmdByValue(CCmd::CMD_INIT);

    $request = new CRequest('');
    $request->setSHOTNGETResponse($response);
    $request->initWithRand($rand);

    $kpub = file_get_contents(Config::$KPUB);
    $request->setKpub($kpub);

    $parameters = new CRequestParameters();
    $parameters->setRand($rand);
    $parameters->setRandPath($randPath);

    $file = new CFile(CFile::TYPE_IDENTITY);

    $param = new CParam(CParam::TYPE_ID_CERT);
    $param->addOpt(CParam::OPT_NEEDED);
    $file->addParam($param);

    $cmd = new CCmdList($file);
    $cmd->setLabel('Sign Message');

    if ($select == false)
      $cmd->setOption('NOSELECT');

    $request->addCmd($cmd);

    $request->setSHOTNGETKpub($cmdInit->getKpub());
    return $request;
  }
  
  /**
   * This function create the files to send the request to the smartphone side
   * @param $request Request to send
   * @param $rand Rand for the transfert
   */
  public static function  send_request($request, $rand) {
    $randPath = Config::$TEMP_PATH;
    $filename = $randPath.$rand;

    $file = fopen($filename.'.rand', 'w+');
    fputs($file, $request->serializer('SRVTOCP'));
    fclose($file);

    @unlink($filename.'.rep');

    $file = fopen($filename.'.flag', 'w+');
    fclose($file);
  }

  /**
   * This function format the signature request with the hash type, the hash data and the file ID
   * @param $rand The rand for the communication
   * @param $hash_data The hash to sign by the smatphone
   * @param $hash_type The type of hash (SHA1 / SHA256)
   * @param $fileId The id of the selected file to sign
   * @return $request
   */
  public static function sign_request($rand, $hash_data, $hash_type, $fileId) {
    $request = new CRequest('');
    $request->initWithRand($rand);

    $cmd = new CCmdCBC();
    $cmd->setType(CCmdCBC::TYPE_SIGN);
    $cmd->setAlgo(CCmdCBC::ALGO_RSA);
    $cmd->setHash($hash_type == "SHA1" ? CCmdCBC::HASH_SAH1 : CCmdCBC::HASH_SAH256);
    $cmd->setPadding(CCmdCBC::PADDING_PKCS1);

    $cmd->setData(base64_encode($hash_data));
    $cmd->setFileId($fileId);
    $request->addCmd($cmd);
    return $request;
  }

  /**
   * This function format the decrypt request with the hash data and the file ID
   * @param $rand The rand for the communication
   * @param $data The hash to decrypt by the smatphone
   * @param $fileId The id of the selected file to sign
   * @return $request
   */
  function decrypt_request($rand, $data, $fileId) {
    $request = new CRequest('');
    $request->initWithRand($rand);

    $cmd = new CCmdCBC();
    $cmd->setType(CCmdCBC::TYPE_UNCIPHER);
    $cmd->setAlgo(CCmdCBC::ALGO_RSA);
    $cmd->setPadding(CCmdCBC::PADDING_PKCS1);
    $cmd->setData(base64_encode($data));
    $cmd->setFileId($fileId);

    $request->addCmd($cmd);
    return $request;
  }
  
  /**
  * This function format the response request to end the communication with the smartphone
  * @param $error Error type
  * @return $message Error message
  */
  public static function format_response_request($error = CErrors::RESULT_NO_ERROR, $message = '') {
    $request = new CRequest('');
    $request->initWithRand($rand);
    $cmdResp = new CCmdResp();
    $cmdResp->setResult($error);
    $cmdResp->setMsg($message);
    $request->addCmd($cmdResp);
    return $request;
  }

};

?>