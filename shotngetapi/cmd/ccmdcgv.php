<?php
/**
 * Shotngetapi / CCmdCGV
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
* This comand display a web page. This page contains Term or reglements and the user have to agree
* this terms to continue the request.
* Tips: add this command at first of your request.
**/
class CCmdCGV extends CCmd {
	// ********************************************************************************************
	/** the web page url */
	private $url;
	
	// ********************************************************************************************
	/**
	* Initalize the comand
	* @param string $url The web page url
	*/
	public function __construct($url) {
		parent::setValue(CCmd::CMD_CGV);
		
		CDebugger::$debug->tracein('__construct', 'CCmdCGV');
		
		$this->url = $url;
		
		CDebugger::$debug->traceout(true);
	}
	
	public function fromXml($node){
		CDebugger::$debug->tracein('fromXml', 'CCmdCGV');
		
		$this->url = $this->getXmlUTF8($node, 'URL');
		
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	public function serializer(&$dom, $kpub){
		CDebugger::$debug->tracein('serializer', 'CCmdCGV');
		
		$xmlCmd = $dom->createElement('CMD');
		$xmlCmd->setAttribute('VALUE', parent::getValue());
		
	  $this->setXmlUTF8($xmlCmd, 'URL', $this->getUrl());
		
		CDebugger::$debug->traceout(true);
		return $xmlCmd;
	}
	
	// ********************************************************************************************
	public function getUrl() { return $this->url; }
	public function setUrl($url) { $this->url = $url; }
	
}

?>