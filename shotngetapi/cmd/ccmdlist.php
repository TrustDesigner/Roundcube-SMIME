<?php
/**
 * Shotngetapi / CCmdList
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
* 
**/
class CCmdList extends CCmd {
	// ********************************************************************************************
	
	// ********************************************************************************************
	private $label;
  private $option;
  private $selected;
	private $file;
	
	// ********************************************************************************************
	/**
	**/
	public function __construct($file) {
		parent::setValue(CCmd::CMD_LIST);
		
		CDebugger::$debug->tracein('__construct', 'CCmdList');

		$this->file = $file;
		
		CDebugger::$debug->traceout(true);
	}
	
	public function fromXml($node){
		CDebugger::$debug->tracein('fromXml', 'CCmdList');
		
		$this->label = $this->getXmlUTF8($node, 'LABEL');
		$this->selected = $this->getXmlUTF8($node, 'SELECTED');		
		$this->option = $this->getXmlUTF8($node, 'OPT');	

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
	public function serializer(&$dom, $kpub) {
		CDebugger::$debug->tracein('serializer', 'CCmdList');
		
		$xmlCmd = $dom->createElement('CMD');
		$xmlCmd->setAttribute('VALUE', parent::getValue());

    $this->setXmlUTF8($xmlCmd, 'LABEL', $this->label);
    $this->setXmlUTF8($xmlCmd, 'OPT', $this->option);
	$this->setXmlUTF8($xmlCmd, 'SELECTED', $this->selected);

		if(is_array($this->file)) {
			$xmlCpdatas = $dom->createElement('CPDATAS');
			
			foreach($this->file as $f)
				$xmlCpdatas->appendChild($f->serializer($dom, $kpub));
				
			$xmlCmd->appendChild($xmlCpdatas);
		}
		else
			$xmlCmd->appendChild($this->file->serializer($dom, $kpub));

		CDebugger::$debug->traceout(true);
		return $xmlCmd;
	}

	private function uncryptFile($file) {
	  if($file->isCrypted() == false)
	    return $file;

	  $kpri = @file_get_contents(Config::$KPRI);

	  //uncrpt file
	  $uncryptedFile = FileManager::uncryptFile($file, $kpri);

	  return $uncryptedFile;
	}
	
	// ********************************************************************************************
	public function getLabel() { return $this->label; }
	public function setLabel($label) { $this->label = $label; }

	public function getOption() { return $this->option; }
	public function setOption($option) { $this->option = $option; }

	public function getSelectedFileId() { return $this->selected; }
	public function setSelectedFileId($selected) { $this->selected = $selected; }
	
	public function getFile() {
	  if (is_array($this->file) == false) {
	    return $this->uncryptFile($this->file);
	  } else {
	    $uncryptFiles = array();
	    foreach ($this->file as $f) {
	      $uncryptFiles[] = $this->uncryptFile($f);
	    }
	    return $uncryptFiles;
	  }
	}
	public function setFile($file) { $this->file = $file; }
	
	public function getSelectedFile() {
	  if ($this->file == null)
	    return null;
	  if (is_array($this->file) == false) {
	    if ($this->file->getId() === $this->selected)
	      return $this->getFile();
	    return null;
	  }
	  foreach($this->file as $f) {
	    if($f->getId() == $this->selected) {
	      if($f->isCrypted() == false)
		return $f;
	      
	      $kpri = @file_get_contents(Config::$KPRI);
	      
	      //uncrpt file
	      $uncryptedFile = FileManager::uncryptFile($f, $kpri);
	      
	      return $uncryptedFile;
	    }
	  }
	  $f = $this->file[0];
	  if($f->isCrypted() == false)
	    return $f;
	  $kpri = @file_get_contents(Config::$KPRI);
	  
	  //uncrpt file
	  $uncryptedFile = FileManager::uncryptFile($f, $kpri);
	  
	  return $uncryptedFile;
	}

}

?>