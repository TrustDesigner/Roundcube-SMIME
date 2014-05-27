<?php
/**
 * Shotngetapi / CCmdRead
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
* This comand ask a information from a specified file.
**/
class CCmdRead extends CCmd {
	// ********************************************************************************************
	private $label;
	/** the file asked */
	private $file;
	
	// ********************************************************************************************
	/**
	* Initalize the comand
	* @param CFile $file The template file asked to the user.
	*/
	public function __construct($file) {
		parent::setValue(CCmd::CMD_READ);
		
		CDebugger::$debug->tracein('__construct', 'CCmdRead');
		
		$this->file = $file;
		
		CDebugger::$debug->traceout(true);
	}
	
	public function fromXml($node){
		CDebugger::$debug->tracein('fromXml', 'CCmdRead');
		
		$this->label = $this->getXmlUTF8($node, 'LABEL');
		
		$xmlCpdatas = $node->getElementsByTagName('CPDATAS')->item(0);
		
		if($xmlCpdatas == null)
			$xmlCpdatas = $node->getElementsByTagName('CPDATA');
		else
			$xmlCpdatas = $xmlCpdatas->getElementsByTagName('CPDATA');
		
		if($xmlCpdatas->length == 1){
			$xmlCpdata = $node->getElementsByTagName('CPDATA')->item(0);
			$file = new CFile('', '');
			$file->fromXml($xmlCpdata);
			
			$this->file = $file;
		}
		else {
			$this->file = array();
			$i = 0;
			
			foreach($xmlCpdatas as $cpdata){
				$f = new CFile('', '');
				$f->fromXml($cpdata);
				
				$this->file[$i] = $f;
				$i++;
			}
		}
		
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	public function serializer(&$dom, $kpub){
		CDebugger::$debug->tracein('serializer', 'CCmdRead');
		
		$xmlCmd = $dom->createElement('CMD');
		$xmlCmd->setAttribute('VALUE', parent::getValue());
		
		$this->setXmlUTF8($xmlCmd, 'LABEL', $this->getLabel());
		
		//serialize template
		if($this->file != null)
			$xmlCmd->appendChild($this->file->serializer($dom));
		
		CDebugger::$debug->traceout(true);
		return $xmlCmd;
	}
	
	// ********************************************************************************************
	public function getLabel() { return $this->label; }
	public function setLabel($label) { $this->label = $label; }
	
	public function getFile() { return $this->file; }
	public function setFile($file) { $this->file = $file; }
	
}

?>