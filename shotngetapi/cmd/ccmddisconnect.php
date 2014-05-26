<?php

if(isset($init_flag) == false)
	die;
	
/**
* This comand ask a web account formated using a file template.
* Have to specifiate type & password size.
**/
class CCmdDisconnect extends CCmd {
	// ********************************************************************************************
	private $result;
	private $url;
	private $msg;
	
	// ********************************************************************************************
	public function __construct() {
		parent::setValue(CCmd::CMD_DISCONNECT);
		
		CDebugger::$debug->tracein('__construct', 'CCmdDisconnect');
		CDebugger::$debug->traceout(true);
	}
	
	public function fromXml($node){
		CDebugger::$debug->tracein('fromXml', 'CCmdDisconnect');
		
		$this->result = $this->getXmlDefault($node, 'RESULT', -1);
		$this->url = $this->getXml($node, 'URL');
		$this->msg = $this->getXml($node, 'MSG');
		
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	public function serializer(&$dom, $kpub){
		CDebugger::$debug->tracein('serializer', 'CCmdDisconnect');
		
		$xmlCmd = $dom->createElement('CMD');
		$xmlCmd->setAttribute('VALUE', parent::getValue());
		
		$this->setXmlUTF8($xmlCmd, 'RESULT', $this->getResult());
		$this->setXmlUTF8($xmlCmd, 'URL', $this->getUrl());
		$this->setXmlUTF8($xmlCmd, 'MSG', $this->getMsg());
		
		CDebugger::$debug->traceout(true);
		return $xmlCmd;
	}
	
	// ********************************************************************************************
	
	// ********************************************************************************************
	public function getResult() { return $this->result; }
	public function setResult($result) { $this->result = $result; }
	
	public function getUrl() { return $this->url; }
	public function setUrl($url) { $this->url = $url; }
	
	public function getMsg() { return $this->msg; }
	public function setMsg($msg) { $this->msg = $msg; }
	
}

?>