<?php

if(isset($init_flag) == false)
	die;
	
/**
* 
**/
class CCmdPlugin extends CCmd {
	// ********************************************************************************************
	private $name;
	private $kpub;
	private $url;
	
	// ********************************************************************************************
	/**
	* Initalize the comand
	*/
	public function __construct($name, $kpub, $url) {
		parent::setValue(CCmd::CMD_PLUGIN);
		
		CDebugger::$debug->tracein('__construct', 'CCmdPlugin');
		
		$this->name = $name;
		$this->kpub = $kpub;
		$this->url = $url;
		
		CDebugger::$debug->traceout(true);
	}
	
	public function fromXml($node){
		CDebugger::$debug->tracein('fromXml', 'CCmdPlugin');
		
		$this->name = $this->getXmlUTF8($node, 'NAME');
		$this->kpub = $this->getXmlUTF8($node, 'KPUB');
		$this->url = $this->getXmlUTF8($node, 'URL');
		
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	public function serializer(&$dom, $kpub){
		CDebugger::$debug->tracein('serializer', 'CCmdPlugin');
		
		$xmlCmd = $dom->createElement('CMD');
		$xmlCmd->setAttribute('VALUE', parent::getValue());
		
		$this->setXmlUTF8($xmlCmd, 'NAME', $this->getName());
		$this->setXmlUTF8($xmlCmd, 'KPUB', $this->getKpub());
		$this->setXmlUTF8($xmlCmd, 'URL', $this->getUrl());
		
		CDebugger::$debug->traceout(true);
		return $xmlCmd;
	}
	
	// ********************************************************************************************
	public function getName() { return $this->name; }
	public function setName($name){ $this->name = $name; }
	
	public function getKpub() { return $this->kpub; }
	public function setKpub($kpub){ $this->kpub = $kpub; }
	
	public function getUrl() { return $this->url; }
	public function setUrl($url){ $this->url = $url; }
	
}

?>