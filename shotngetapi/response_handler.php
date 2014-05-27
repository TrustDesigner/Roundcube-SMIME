<?php
/**
 * Shotngetapi / Response_handler
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