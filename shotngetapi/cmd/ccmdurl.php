<?php

if(isset($init_flag) == false)
	die;
	
/**
* Display a web page at user mobile screen spécified by the command url.
**/
class CCmdUrl extends CCmd {
	// ********************************************************************************************
	/** The web page url */
	private $url;
	
	// ********************************************************************************************
	/**
	* Initialize the comand.
	* @param string $key: identifier for the comand.
	* @param string $url the web page url.
	**/
	public function __construct($url) {
		parent::setValue(CCmd::CMD_URL);
		
		CDebugger::$debug->tracein('__construct', 'CCmdUrl');
		
		$this->url = $url;
		
		CDebugger::$debug->traceout(true);
	}
	
	public function fromXml($node){
		CDebugger::$debug->tracein('fromXml', 'CCmdUrl');
		
		$this->url = $this->getXmlUTF8($node, 'URL');
		
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	public function serializer(&$dom, $kpub){
		CDebugger::$debug->tracein('serializer', 'CCmdUrl');
		
		$xmlCmd = $dom->createElement('CMD');
		$xmlCmd->setAttribute('VALUE', parent::getValue());
		
		$this->setXmlUTF8($xmlCmd, 'URL', $this->getUrl());
		
		CDebugger::$debug->traceout(true);
		return $xmlCmd;
	}
	
	// ********************************************************************************************
	public function getUrl() { return $this->url; }
	public function setUrl($url) { $this->url = $url; }
	
}

?>