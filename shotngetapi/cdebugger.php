<?php
/**
 * Shotngetapi / CDebugger
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
* Global variables use by the Api.
**/
class CDebugger {

	public static $debug;
	
	public static function init($path = null, $id = 0) {
		if($path == null)
			$path = CConstants::$API_PATH.'log/';
	
		CDebugger::$debug = new cdebug($path, $id, 'certphoneapi');
		CDebugger::$debug->setlevel(2);
		
		CDebugger::$debug->enableclass('CConf', false);
		
		CDebugger::$debug->enableclass('CFile', false);
		CDebugger::$debug->enableclass('CParam', false);
		
		CDebugger::$debug->enableclass('CCmdAuth', false);
		CDebugger::$debug->enableclass('CCmdCGV', false);
		CDebugger::$debug->enableclass('CCmdConnect', false);
		CDebugger::$debug->enableclass('CCmdInit', false);
		CDebugger::$debug->enableclass('CCmdPay', false);
		CDebugger::$debug->enableclass('CCmdPlugin', false);
		CDebugger::$debug->enableclass('CCmdRead', false);
		CDebugger::$debug->enableclass('CCmdResp', false);
		CDebugger::$debug->enableclass('CCmdUrl', false);
		
		CDebugger::$debug->enableclass('CRequest', false);
		CDebugger::$debug->enableclass('CResponse', false);
		CDebugger::$debug->enableclass('CQrCode', false);
		CDebugger::$debug->enableclass('CWaitResponse', false);
		
		CDebugger::$debug->enableclass('CApiManager', false);
	}

	public static function reset() {
		if(CDebugger::$debug != null)
			CDebugger::$debug->init();
	}
}

?>