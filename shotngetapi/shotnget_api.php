<?php
	/*
	* Require all certphone api class. Require this file to use the Api.
	Set a flag for control input
	*/
	
	$init_flag = true;
	
	// ********************************************************************************************
	require('config.php');
	
	// ********************************************************************************************
	require('cdebug.php');
	
	// ********************************************************************************************
	require('cconstants.php');
	
	require('cdebugger.php');
	CDebugger::init();
	
	require('cerrors.php');
	
	require('cutils.php');
	
	// ********************************************************************************************
	require('ccrypto.php');
	
	require('tmpl/ctmpl.php');
	
	require('cmd/ccmds.php');
	
	// ********************************************************************************************
	require('crequest.php');
	
	require('cresponse.php');
	
	require('crequestmanager.php');
	
	require('cqrcode.php');
	
	require('cwaitresponse.php');
	
	require('crequestparameters.php');
	
	require('apiparameters.php');
	
	require('apimanager.php');
	
?>