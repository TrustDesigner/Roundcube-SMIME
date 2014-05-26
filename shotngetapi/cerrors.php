<?php

if(isset($init_flag) == false)
	die;
	
/**
* This class contains constants for different error result retourned by the api.
**/
class CErrors{
	// ********************************************************************************************
	const RESULT_NO_ERROR = "0";
	const RESULT_RAND_ERROR = "1";
	const RESULT_TIMEOUT = "2";
	const RESULT_SERVER_ERROR = "3";
	const RESULT_PLUGIN_ERROR = "4";
	const RESULT_SERVER_ERROR_WITH_RETRY = "10";
	const RESULT_CERTPHONE_ERROR = "11";
	
	// ********************************************************************************************
	
}

?>