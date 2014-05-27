<?php
/**
 * Shotngetapi / Shotnget_api
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