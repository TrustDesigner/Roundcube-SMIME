<?php
/**
 * Shotngetapi / CCmdConnect
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
class CCmdConnect extends CCmd {
	// ********************************************************************************************
	private $kpub;
	private $kpri;
	
	private $os;
	private $model;
	
	private $img;
	private $imgData;
	
	private $useLogo;
	
	private $pub;
	
	private $multiCmds;
	
	// ********************************************************************************************
	/**
	* Initalize the comand
	*/
	public function __construct() {
		parent::setValue(CCmd::CMD_CONNECT);
		
		CDebugger::$debug->tracein('__construct', 'CCmdConnect');
		
		$this->useLogo = true;
		
		CDebugger::$debug->traceout(true);
	}
	
	public function fromXml($node){
		CDebugger::$debug->tracein('fromXml', 'CCmdConnect');
		
		$this->kpub = $this->getXmlUTF8($node, 'KPUB');
		$this->kpri = $this->getXmlUTF8($node, 'KPRI');
		$this->os = $this->getXmlUTF8($node, 'OS');
		$this->model = $this->getXmlUTF8($node, 'MODEL');
		$this->img = $this->getXmlUTF8($node, 'IMG');
		$this->imgData = $this->getXml($node, 'IMGDATA');
    $this->ver = $this->getXmlUTF8($node, 'VER');
		$this->useLogo = $this->getXmlDefault($node, 'USELOGO', true);
		$this->pub = $this->getXmlUTF8($node, 'PUB');
		$this->multiCmds = $this->getXml($node, 'MULTICMDS');
		
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	public function serializer(&$dom, $kpub){
		CDebugger::$debug->tracein('serializer', 'CCmdConnect');
		
		$xmlCmd = $dom->createElement('CMD');
		$xmlCmd->setAttribute('VALUE', parent::getValue());
		
		$this->setXmlUTF8($xmlCmd, 'KPUB', $this->getKpub());
		$this->setXmlUTF8($xmlCmd, 'KPRI', $this->getKpri());
		$this->setXmlUTF8($xmlCmd, 'OS', $this->getOs());
		$this->setXmlUTF8($xmlCmd, 'MODEL', $this->getModel());
		$this->setXmlUTF8($xmlCmd, 'IMG', $this->getImg());
		$this->setXml($xmlCmd, 'IMGDATA', $this->getImgData());
		$this->setXmlUTF8($xmlCmd, 'PUB', $this->getPub());
		$this->setXmlUTF8($xmlCmd, 'MULTICMDS', $this->multiCmds);
		
		CDebugger::$debug->traceout(true);
		return $xmlCmd;
	}
	
	// ********************************************************************************************
	public function getKpub() { return $this->kpub; }
	public function setKpub($kpub) { $this->kpub = $kpub; }
	
	public function getKpri() { return $this->kpri; }
	public function setKpri($kpri) { $this->kpri = $kpri; }
	
	public function getOs() { return $this->os; }
	public function setOs($os) { $this->os = $os; }
	
	public function getModel() { return $this->model; }
	public function setModel($model) { $this->model = $model; }
	
	public function getImg() { return $this->img; }
	public function setImg($img) { $this->img = $img; }
	
	public function getImgData() { return $this->imgData; }
	public function setImgData($imgData) { $this->imgData = $imgData; }
	
	public function getPub() { return $this->pub; }
	public function setPub($pub) { $this->pub = $pub; }
	
	public function isUseLogo() { return $this->useLogo; }
	public function setUseLogo($useLogo) { $this->useLogo = $useLogo; }
	
	public function getMultiCmds() { return $this->multiCmds; }

}

?>