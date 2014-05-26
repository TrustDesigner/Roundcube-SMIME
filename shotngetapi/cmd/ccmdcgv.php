<?php

if(isset($init_flag) == false)
	die;
	
/**
* This comand display a web page. This page contains Term or reglements and the user have to agree
* this terms to continue the request.
* Tips: add this command at first of your request.
**/
class CCmdCGV extends CCmd {
	// ********************************************************************************************
	/** the web page url */
	private $url;
	
	// ********************************************************************************************
	/**
	* Initalize the comand
	* @param string $url The web page url
	*/
	public function __construct($url) {
		parent::setValue(CCmd::CMD_CGV);
		
		CDebugger::$debug->tracein('__construct', 'CCmdCGV');
		
		$this->url = $url;
		
		CDebugger::$debug->traceout(true);
	}
	
	public function fromXml($node){
		CDebugger::$debug->tracein('fromXml', 'CCmdCGV');
		
		$this->url = $this->getXmlUTF8($node, 'URL');
		
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	public function serializer(&$dom, $kpub){
		CDebugger::$debug->tracein('serializer', 'CCmdCGV');
		
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