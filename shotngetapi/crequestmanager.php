<?php

if(isset($init_flag) == false)
	die;

/**
* 
**/
abstract class CRequestManager {
	// ********************************************************************************************
	
	// ********************************************************************************************
	public function __construct() {
	
	}
	
	// ********************************************************************************************
	public abstract function onInitRequest($initCmd, $requestParameters, &$request);
	
	public abstract function onResponseReceived($response, $requestParameters, &$request);
	
	// ********************************************************************************************
	
}

?>