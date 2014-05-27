<?php
/**
 * Shotngetapi / CCmdDisconnect
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
* This comand ask a web account formated using a file template.
* Have to specifiate type & password size.
**/
class CCmdDisconnect extends CCmd {
	// ********************************************************************************************
	private $result;
	private $url;
	private $msg;
	
	// ********************************************************************************************
	public function __construct() {
		parent::setValue(CCmd::CMD_DISCONNECT);
		
		CDebugger::$debug->tracein('__construct', 'CCmdDisconnect');
		CDebugger::$debug->traceout(true);
	}
	
	public function fromXml($node){
		CDebugger::$debug->tracein('fromXml', 'CCmdDisconnect');
		
		$this->result = $this->getXmlDefault($node, 'RESULT', -1);
		$this->url = $this->getXml($node, 'URL');
		$this->msg = $this->getXml($node, 'MSG');
		
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	public function serializer(&$dom, $kpub){
		CDebugger::$debug->tracein('serializer', 'CCmdDisconnect');
		
		$xmlCmd = $dom->createElement('CMD');
		$xmlCmd->setAttribute('VALUE', parent::getValue());
		
		$this->setXmlUTF8($xmlCmd, 'RESULT', $this->getResult());
		$this->setXmlUTF8($xmlCmd, 'URL', $this->getUrl());
		$this->setXmlUTF8($xmlCmd, 'MSG', $this->getMsg());
		
		CDebugger::$debug->traceout(true);
		return $xmlCmd;
	}
	
	// ********************************************************************************************
	
	// ********************************************************************************************
	public function getResult() { return $this->result; }
	public function setResult($result) { $this->result = $result; }
	
	public function getUrl() { return $this->url; }
	public function setUrl($url) { $this->url = $url; }
	
	public function getMsg() { return $this->msg; }
	public function setMsg($msg) { $this->msg = $msg; }
	
}

?>