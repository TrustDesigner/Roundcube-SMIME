<?php
/**
 * Shotngetapi / CFIle
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
* A file regroup different information named "CParam"
* This class is use to ask information or sending information
* For example the Read Comand ask a CFile.
* This class contain file types.
**/
class CFile {
	// ********************************************************************************************
	/** An identity file regroup information about a person */
	const TYPE_IDENTITY = 'IDENTITY';
	/**  */
	const TYPE_OFFICIAL_DOCUMENT = 'OFFICIALDOC';
	/**  */
	const TYPE_CLOTHE_SIZE = 'CLOTHESIZE';
	/**  */
	const TYPE_CAR_INFO = 'CARINFO';
	/**  */
	const TYPE_BANK_ACCOUNT = 'BANKACCOUNT';
	/** An account file regroup information use to unlock a runable program */
	const TYPE_PROGRAMM_ACCOUNT = 'PROGRAMMACCOUNT';
	/** A webaccount file regroup information use to connect the user (username, password, ...) */
	const TYPE_WEBACCOUNT = 'WEBACCOUNT';
	/** A creditcard file regroup information about a credit card for a payment */
	const TYPE_CREDITCARD = 'CREDITCARD';
	
	// ********************************************************************************************
	private $fileId;

	/** the file type */
	private $type;
	/** The file display */
	private $display;

	/** List of CParam */
	private $params = array();
	
	private $ukey;
	
	private $crypted = true;
	
	// ********************************************************************************************
	/**
	* Initialize the comand.
	* @param string $type File type (use CFile constants).
	* @param string display The display file.
	*/
	public function __construct($type, $display = '') {
		CDebugger::$debug->tracein('__construct', 'CFile');
		
		$this->type = $type;
		$this->display = $display;
		
		CDebugger::$debug->traceout(true);
	}
	
	public function fromXml($node){
		CDebugger::$debug->tracein('fromXml', 'CFile');
		

		/* $id = $node->getAttribute('ID'); */

		/* if($id != false) { */
		/*   //$id = $node->getAttribute('ID');//utf8_decode($node->getAttribute('ID')); */
		/* 	$this->id = $id; */
		/* } /\* else *\/ */
		/*   /\* echo 'toto'; *\/ */
		
		$fileId = utf8_decode($node->getAttribute('ID'));

		$this->fileId = $fileId;

		$type = utf8_decode($node->getAttribute('TYPE'));
		$this->type = $type;
		
		$display = utf8_decode($node->getAttribute('DISPLAYNAME'));
		$this->display = $display;
		
		$xmlUkey = $node->getElementsByTagName('UKEY');
		if($xmlUkey->length == 1 && $xmlUkey->item(0)->firstChild != null)
			$this->ukey = $xmlUkey->item(0)->firstChild->nodeValue;
		
		$xmlParams = $node->getelementsByTagName('PARAMS')->item(0);
		$listParam = $xmlParams->getElementsByTagName('PARAM');
		
		foreach ($listParam as $xmlParam){
			$param = new CParam('', '');
			$param->fromXml($xmlParam);
			$this->addParam($param);
		}
		
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	public function serializer($dom) {
		CDebugger::$debug->tracein('serializer', 'CFile');
		
		$xmlCpdata = $dom->createElement('CPDATA');
		
		/* if($this->fileId != null) { */
		/* 	$id = utf8_encode($this->id); */
		/* 	$xmlCpdata->setAttribute('ID', $id); */
		/* } */

		$fileId = utf8_encode($this->getId());
		$xmlCpdata->setAttribute('ID', $fileId);

		$type = utf8_encode($this->getType());
		$xmlCpdata->setAttribute('TYPE', $type);
		
		$display = utf8_encode($this->getDisplay());
		$xmlCpdata->setAttribute('DISPLAYNAME', $display);
		
		$xmlUkey = $dom->createElement('UKEY');
		$xmlUkey->appendChild($dom->createTextNode($this->ukey));
		$xmlCpdata->appendChild($xmlUkey);
		
		$xmlParams = $dom->createElement('PARAMS');
		
		foreach ($this->params as $param) {
			$xmlParams->appendChild($param->serializer($dom));
		}
		
		$xmlCpdata->appendChild($xmlParams);
		
		CDebugger::$debug->traceout(true);
		return $xmlCpdata;
	}
	
	/**
	* Add a CParam into the CFile
	* @param CParam $param CParam for adding into the CFIle.
	*/
	public function addParam($param){
		$this->params[count($this->params)] = $param;
	}
	
	/**
	* Return a CParam specified by this $type
	* @param string $type The CParam type
	*/
	public function getParamByType($type){
		foreach ($this->params as $param) {
			if($param->getType() == $type)
				return $param;
		}
	}
	
	/**
	* Return a CParam specified by this $name
	* @param string $name The CParam name
	*/
	public function getParamByName($name){
		foreach ($this->params as $param) {
			if($param->getName() == $name)
				return $param;
		}
	}
	
	// ********************************************************************************************
	public function getId() { return $this->fileId; }
	public function setId($fileId) { $this->fileId = $fileId; }

	public function getType() { return $this->type; }
	public function setType($type) { $this->type = $type; }
	
	public function getDisplay() { return $this->display; }
	public function setDisplay($display) { $this->display = $display; }
	
	public function getUkey() { return $this->ukey; }
	public function setUkey($ukey) { $this->ukey = $ukey; }
	
	public function getParams() { return $this->params; }
	public function setParams($params) { $this->params = $params; }
	
	public function isCrypted() { return $this->crypted; }
	public function setCrypted($crypted) { $this->crypted = $crypted; }
}

?>