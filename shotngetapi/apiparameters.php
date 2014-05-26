<?php

if(isset($init_flag) == false)
	die;
	
/**
* 
**/
class ApiParameters {
	// ********************************************************************************************
	private $apiPath;
	
	private $conf;
	
	// ********************************************************************************************
	public function __construct($apiPath) {
		CConstants::$API_PATH = $apiPath;
		$this->apiPath = $apiPath;
	}
	
	// ********************************************************************************************
	public function getApiPath() { return $this->apiPath; }
	//public function setApiPath($apiPath) { $this->apiPath = $apiPath; }
	
	public function getConf() { return $this->conf; }
}

?>