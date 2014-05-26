<?php

if(isset($init_flag) == false)
	die;
	
/**
* Contants use by the Api.
**/
class CConstants{
	/* certphone api version. DO NOT CHANGE IT */
	const API_VERSION = '1';
	
	const AES_KEY_SIZE = 128;
	const RSA_KEY_SIZE = 1024;
	
	public static $API_PATH;
}

?>