<?php

if(isset($init_flag) == false)
	die;
	
/**
* This class contains methods to manage the certphone response. Contains list of cmds returned
* by the certphone app.
**/
class CResponse {
	// ********************************************************************************************
	/* the rand files directory */
	private $randPath;
	
	/* certphone response version */
	private $version;
	
	/* The rand value */
	private $rand;
	
	/* List of CCmd returned by Certphone app */
	private $cmds = array();
	
	/* this string contains last error message */
	private $error;
	
	private $urlSign;
	
	// ********************************************************************************************
	/*
	* Initialize response class.
	* @param string $randPath the rand files directiry
	* @paran string^$rand The rand value
	*/
	public function CResponse($apiParameters, $rand) {
		CDebugger::$debug->tracein('__construct', 'CResponse');
		
		$this->randPath = Config::$TEMP_PATH;
		$this->rand = $rand;
		
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	/*
	* This method gets xml certphone response into the rand file and parse it to make a list of CCmd.
	*/
	public function parse() {
		CDebugger::$debug->tracein('parse', 'CResponse');
		
		if(CUtils::checkRand($this->rand)){
			$filename = $this->randPath.$this->rand.'.cmds';
			
			if (!file_exists($filename)){
				$this->error = 'rand file \''.$filename.'\' not found.';
				CDebugger::$debug->traceout(false);
				return false;
			}
		
			$dom = new DOMdocument();
			@$dom->load($filename);
			
			$result = $this->parsing($dom);
			CDebugger::$debug->traceout($result);
			return $result;
		}
		else {
			CDebugger::$debug->traceout(false);
			return false;
		}
	}
	
	/**
	*
	*/
	public function parseData($data){
		CDebugger::$debug->tracein('parseData', 'CResponse');
		
		$dom = new DOMdocument();
		$dom->loadXml($data);
		
		$result = $this->parsing($dom);
		CDebugger::$debug->traceout($result);
		return $result;
	}
	
	/**
	*
	*/
	private function parsing($dom){
		CDebugger::$debug->tracein('parsing', 'CResponse');
		
		if($dom != null){
			//valide le dom
			//$dom->validate();
			
			$xmlVer = $dom->getElementsByTagName('VER');
			if($xmlVer->length == 1 && $xmlVer->item(0)->firstChild != null)
				$this->version = $xmlVer->item(0)->firstChild->nodeValue;
			
			$xmlRand = $dom->getElementsByTagName('RAND');
			if($xmlRand->length == 1 && $xmlRand->item(0)->firstChild != null)
				$this->rand = $xmlRand->item(0)->firstChild->nodeValue;
			
			$xmlUrlSign = $dom->getElementsByTagName('URLSIGN');
			if($xmlUrlSign->length == 1 && $xmlUrlSign->item(0)->firstChild != null)
				$this->urlSign = $xmlUrlSign->item(0)->firstChild->nodeValue;
			
			$xmlCmds = $dom->getElementsByTagName('CMDS')->item(0);
			
			if($xmlCmds == null) {
				CDebugger::$debug->traceout(false);
				return false;
			}
			
			$listCmd = $xmlCmds->getElementsByTagName('CMD');
			$keyCount = '0';
			
			foreach ($listCmd as $xmlCmd){
				$value = $xmlCmd->getAttribute('VALUE');
				
				switch($value){
					case CCmd::CMD_URL;
						$cmd = new CCmdUrl('');
						$cmd->fromXml($xmlCmd);
					break;
					
					case CCmd::CMD_CGV;
						$cmd = new CCmdCGV('');
						$cmd->fromXml($xmlCmd);
					break;
					
					case CCmd::CMD_CONNECT;
						$cmd = new CCmdConnect();
						$cmd->fromXml($xmlCmd);
					break;
					
					case CCmd::CMD_DISCONNECT;
						$cmd = new CCmdDisconnect();
						$cmd->fromXml($xmlCmd);
					break;
					
					case CCmd::CMD_AUTH;
						$cmd = new CCmdAuth('', '', null);
						$cmd->fromXml($xmlCmd);
					break;
					
					case CCmd::CMD_READ;
						$cmd = new CCmdRead(null);
						$cmd->fromXml($xmlCmd);
					break;
					
					case CCmd::CMD_RESP;
						$cmd = new CCmdResp();
						$cmd->fromXml($xmlCmd);
					break;
					
					case CCmd::CMD_INIT;
						$cmd = new CCmdInit();
						$cmd->fromXml($xmlCmd);
					break;
					
					case CCmd::CMD_PAY;
						$cmd = new CCmdPay(0, '');
						$cmd->fromXml($xmlCmd);
					break;
					
					case CCmd::CMD_PLUGIN;
						$cmd = new CCmdPlugin('', '', '');
						$cmd->fromXml($xmlCmd);
					break;
					
					case CCmd::CMD_CBC;
						$cmd = new CCmdCBC();
						$cmd->fromXml($xmlCmd);
					break;

					case CCmd::CMD_LIST;
						$cmd = new CCmdList('');
						$cmd->fromXml($xmlCmd);
					break;

					case CCmd::CMD_CERT;
						$cmd = new CCmdCert();
						$cmd->fromXml($xmlCmd);
					break;
				}
				
				if($cmd != null){
					$this->cmds[$keyCount] = $cmd;
					$keyCount++;
				}
			}
			
			CDebugger::$debug->traceout(true);
			return true;
		}
		else {
			$this->error = 'failed to load xml in rand file.';
			CDebugger::$debug->traceout(false);
			return false;
		}
	}
	
	/*
	* Return a coman specified by her $value
	* @param string $value The comand value
	* @return CCmds The target comand.
	*/
	public function getCmdByValue($value){
		foreach($this->cmds as $cmd){
			if($cmd->getValue() == $value)
				return $cmd;
		}
		
		return null;
	}
	
	// ********************************************************************************************
	public function getError(){ return $this->error; }
	
	public function getCmds() { return $this->cmds; }
	
	public function getVersion() { return $this->version; }
	
	public function getRand() { return $this->rand; }
	
	public function getUrlSign() { return $this->urlSign; }
}

?>