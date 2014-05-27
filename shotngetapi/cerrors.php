<?php
/**
 * Shotngetapi / CErrors
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