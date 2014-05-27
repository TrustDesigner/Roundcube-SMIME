<?php

/**
 * Shotngetapi / ApiManager
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

if(isset($init_flag) == false)
	die;
	
/**
* 
**/
class ApiManager {
	// ********************************************************************************************
	private $errors;
	
	private $apiParameters;
	
	// ********************************************************************************************
	public function __construct($apiParameters) {
		CDebugger::$debug->tracein('__construct', 'CApiManager');
		
		$this->apiParameters = $apiParameters;
		
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	public function generateNewRequest($responseUrl, $waitResponsePage = null) {
		CDebugger::$debug->tracein('generateNewRequest', 'CApiManager');
		
		$parameters = new CRequestParameters();
		
		$request = new CRequest($responseUrl);
		$request->init();
		
		$qrcode = new CQrCode($this->apiParameters->getApiPath().'/qrcodes/');
		
		$parameters->setImgPath($qrcode->generate($request));
		
		if ($waitResponsePage == null)
		  $listening = $request->getListeningFunction($this->apiParameters->getApiPath().'waitresponse.php');
		else
		  $listening = $request->getListeningFunction($waitResponsePage);
		$parameters->setListeningFunction($listening);
		
		$dir = dirname($_SERVER['SCRIPT_NAME']).'/';
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "")
				$protocol = "http://";
			else
				$protocol = "https://";
		
		$shotngetLink = 'shotnget://localhost?url='.$responseUrl.'&shotnget='.$request->getRand()
			.'&urlret='.$protocol.$_SERVER['SERVER_NAME'].$dir;
		
		$parameters->setShotngetLink($shotngetLink);
		
		$parameters->setRand($request->getRand());
		
		$parameters->setRandPath(Config::$TEMP_PATH);
		
		CUtils::setUserDataWithRequestParameters($parameters, 'qrcode_img_name', $qrcode->getImgName());
		
		CDebugger::$debug->traceout(true);
		return $parameters;
	}
	
	// ********************************************************************************************
	public function getErrors() { return $this->errors; }
	
}

?>