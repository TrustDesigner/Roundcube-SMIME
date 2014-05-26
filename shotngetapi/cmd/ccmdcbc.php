<?php

if(isset($init_flag) == false)
	die;
	
/**
* 
**/
class CCmdCBC extends CCmd {
	// ********************************************************************************************
	const TYPE_SIGN 		= 'SIGN';
	const TYPE_CIPHER 		= 'CIPHER';
	const TYPE_UNCIPHER 	= 'UNCIPHER';
	const TYPE_HASH 		= 'HASH';

	const ALGO_RSA 			= "RSA";
	const ALGO_DES3 		= "DES3";
	const ALGO_AES 			= "AES";
	
	const HASH_SAH1 		= "SHA1";
	const HASH_SAH256		= "SHA256";
	
	const PADDING_PKCS1		= "PKCS1";
	const PADDING_OAEP		= "OAEP";
	const PADDING_NOPADDING	= "NOPADDING";
	
	// ********************************************************************************************
	private $type;
	private $algo;
	private $hash;
	private $padding;
	private $fileId;
	private $paramId;
	private $result;

	private $data;
	
	// ********************************************************************************************
	/**
	**/
	public function __construct() {
		parent::setValue(CCmd::CMD_CBC);
		
		CDebugger::$debug->tracein('__construct', 'CCmdCBC');

		CDebugger::$debug->traceout(true);
	}
	
	public function fromXml($node){
		CDebugger::$debug->tracein('fromXml', 'CCmdCBC');
		
  	$this->result =  $this->getXmlUTF8($node, 'RESULT');		
  	$this->type =  $this->getXmlUTF8($node, 'TYPE');
		$this->algo =  $this->getXmlUTF8($node, 'ALGO');
		$this->hash =  $this->getXmlUTF8($node, 'HASH');
		$this->padding =  $this->getXmlUTF8($node, 'PADDING');
		$this->fileId =  $this->getXmlUTF8($node, 'ID');
		$this->paramId = $this->getXmlUTF8($node, 'IDPARAM');
  	$this->data = $this->getXmlUTF8($node, 'DATA');
	
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	public function serializer(&$dom, $kpub) {
		CDebugger::$debug->tracein('serializer', 'CCmdCBC');
		
		$xmlCmd = $dom->createElement('CMD');
		$xmlCmd->setAttribute('VALUE', parent::getValue());

    $this->setXmlUTF8($xmlCmd, 'RESULT', $this->result);
    $this->setXmlUTF8($xmlCmd, 'TYPE', $this->type);
    $this->setXmlUTF8($xmlCmd, 'ALGO', $this->algo);
    $this->setXmlUTF8($xmlCmd, 'HASH', $this->hash);
    $this->setXmlUTF8($xmlCmd, 'PADDING', $this->padding);
    $this->setXmlUTF8($xmlCmd, 'ID', $this->fileId);
    $this->setXmlUTF8($xmlCmd, 'IDPARAM', $this->paramId);
    $this->setXmlUTF8($xmlCmd, 'DATA', $this->data);

		CDebugger::$debug->traceout(true);
		return $xmlCmd;
	}
	
	// ********************************************************************************************
	public function getType() { return $this->type; }
	public function setType($type) { $this->type = $type; }

	public function getResult() { return $this->result; }
	public function setResult($result) { $this->result = $result; }

	public function getAlgo() { return $this->algo; }
	public function setAlgo($algo) { $this->algo = $algo; }

	public function getHash() { return $this->hash; }
	public function setHash($hash) { $this->hash = $hash; }

	public function getPadding() { return $this->padding; }
	public function setPadding($padding) { $this->padding = $padding; }

	public function getFileId() { return $this->fileId; }
	public function setFileId($fileId) { $this->fileId = $fileId; }

	public function getParamId() { return $this->paramId; }
	public function setParamId($paramId) { $this->paramId = $paramId; }

	public function getData() { return $this->data; }
	public function setData($data) { $this->data = $data; }
	
}

?>