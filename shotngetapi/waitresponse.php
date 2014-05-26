<?php
	/*
	* This cript is calling by the javascript listener. it call the ckeck method for certphone response.
	*/
	
	$rand = $_GET['rand'];
	$randPath = $_GET['randPath'];
	$freq = $_GET['freq'];
	
	require('shotnget_api.php');

	$waitResponse = new CWaitResponse($rand, $randPath, $freq);
	echo $waitResponse->hasReplied();
?>