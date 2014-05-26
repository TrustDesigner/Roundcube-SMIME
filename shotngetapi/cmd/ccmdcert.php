<?php

if(isset($init_flag) == false)
	die;
	
/**
* 
**/
class CCmdCert extends CCmd {
	// ********************************************************************************************
	const MODE_P12 = 'P12';
	
	// ********************************************************************************************
	private $label;
	private $mode;
	private $data;
	private $password;
	
	// ********************************************************************************************
	public function __construct() {
		parent::setValue(CCmd::CMD_CERT);
		
		CDebugger::$debug->tracein('__construct', 'CCmdCert');
		CDebugger::$debug->traceout(true);
	}
	
	public function fromXml($node){
		CDebugger::$debug->tracein('fromXml', 'CCmdCert');
		
		$this->label = $this->getXmlUTF8($node, 'LABEL');
		$this->mode = $this->getXmlUTF8($node, 'MODE');
		$this->data = $this->getXmlUTF8($node, 'DATA');
		$this->password = $this->getXmlUTF8($node, 'PASSWORD');

		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	public function serializer(&$dom, $kpub) {
		CDebugger::$debug->tracein('serializer', 'CCmdCert');
		
		$xmlCmd = $dom->createElement('CMD');
		$xmlCmd->setAttribute('VALUE', parent::getValue());

    	$this->setXmlUTF8($xmlCmd, 'LABEL', $this->label);
    	$this->setXmlUTF8($xmlCmd, 'MODE', $this->mode);
    	$this->setXmlUTF8($xmlCmd, 'DATA', $this->data);
    	$this->setXmlUTF8($xmlCmd, 'PASSWORD', $this->password);

		CDebugger::$debug->traceout(true);
		return $xmlCmd;
	}
	
	// ********************************************************************************************
	public function getLabel() { return $this->label; }
	public function setLabel($label) { $this->label = $label; }

	public function getMode() { return $this->mode; }
	public function setMode($mode) { $this->mode = $mode; }

	public function getData() { return $this->data; }
	public function setData($data) { $this->data = $data; }

	public function getPassword() { return $this->password; }
	public function setPassword($password) { $this->password = $password; }

}

?>