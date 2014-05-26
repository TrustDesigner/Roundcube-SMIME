<?php
	$apiPath = '../certphoneapi/';
	
	require($apiPath.'certphone_api.php');
	
	/**
	* Require to handle SHOTNGET responses
	*/
	require('requestmanagers/myrequestmanager.php');
	
	$apiParameters = new CApiParameters($apiPath);
	$apiParameters->setConf($apiPath.'certphone_api.conf');
	$apiParameters->setTempPath('../certphoneapi/temp/');
	
	$myHandler = new MyRequestManager();
	
	/*
	* This function handle the SHOTNGET response.
	*/
	echo CRequest::handleResponse($apiParameters, $myHandler);
?>