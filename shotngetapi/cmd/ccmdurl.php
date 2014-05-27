<?php
/**
 * Shotngetapi / CCmdUrl
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
* Display a web page at user mobile screen spécified by the command url.
**/
class CCmdUrl extends CCmd {
	// ********************************************************************************************
	/** The web page url */
	private $url;
	
	// ********************************************************************************************
	/**
	* Initialize the comand.
	* @param string $key: identifier for the comand.
	* @param string $url the web page url.
	**/
	public function __construct($url) {
		parent::setValue(CCmd::CMD_URL);
		
		CDebugger::$debug->tracein('__construct', 'CCmdUrl');
		
		$this->url = $url;
		
		CDebugger::$debug->traceout(true);
	}
	
	public function fromXml($node){
		CDebugger::$debug->tracein('fromXml', 'CCmdUrl');
		
		$this->url = $this->getXmlUTF8($node, 'URL');
		
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	public function serializer(&$dom, $kpub){
		CDebugger::$debug->tracein('serializer', 'CCmdUrl');
		
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