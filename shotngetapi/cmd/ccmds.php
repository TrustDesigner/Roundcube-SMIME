<?php

	if(isset($init_flag) == false)
		die;
	
	/*
	* This file require all comand class.
	*/
	
	require('ccmd.php');
	
	require('ccmdurl.php');
	
	require('ccmdcgv.php');
	
	require('ccmdconnect.php');

	require('ccmddisconnect.php');
	
	require('ccmdauth.php');
	
	require('ccmdread.php');
	
	require('ccmdresp.php');
	
	require('ccmdinit.php');
	
	require('ccmdpay.php');
	
	require('ccmdplugin.php');

	require('ccmdcbc.php');

	require('ccmdlist.php');

	require('ccmdcert.php');
	
?>