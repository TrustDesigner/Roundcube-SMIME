<?php

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