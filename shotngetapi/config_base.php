<?php
/**
 * Shotngetapi / Config_Base
 *
 * shotngetapi is used to perform communication between a client and a smartphone with SHOTNGET application
 * Copyright (C) 2007-2014 Trust Designer, Gallet Kévin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
 
class Config
{
	/*
	* This config File contains global variable for the api
	* We recommanded to set path outside of your website or in securised folder (.htaccess) !
	*/
	
	//folder path: contains temporary files
	//add permission to create, modify and remove files into this folder.
	public static $TEMP_PATH = 'C:/certphone/temp/';
	
	public static $RESPONSE_URL = 'http://192.168.2.205/certphone_samples/response_handler.php';
			
	//target redirection url
	public static $TARGET_REDIRECT = 'http://192.168.2.205/certphone_samples/account.php';
			
	//Path for website signature.
	//TrustDesigner send a signature file, you have to set the path of the file here.
	public static $URLSIGN = 'C:/certphone/urlsign';
	
	public static $OLDURLSIGN = 'C:/certphone/oldurlsign';
	
	//Keypair path. This keypair is used by the api to crypt data.
	//RSA public key (in base64)
	public static $KPUB = 'C:/certphone/kpub';
	
	//RSA private key (in base 64)
	public static $KPRI = 'C:/certphone/kpri';
	
}

?>