<?php
/**
 * Shotngetapi / CParam
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
* A CParam reppresent an information qualified by a type, a name, a display name, options.
* Use in CFile for asking data.
* Contains a list of param type can be ask;
**/
class CParam {
	// ********************************************************************************************
	/** Generic parameter */
	const TYPE_NAME = 'NAME';
	const TYPE_GROUP = 'GROUP';
	const TYPE_TEXT = 'TEXT';
	const TYPE_NUMBER = 'NUMBER';
	const TYPE_NOTE = 'NOTE';
	const TYPE_PWD = 'PWD';
	const TYPE_OLDPWD = 'OLDPWD';
	
	/** Identity parameters */
	const TYPE_ID_TITLE = 'IDTITLE';
	const TYPE_ID_FIRSTNAME = 'IDFIRSTNAME';
	const TYPE_ID_LASTNAME = 'IDLASTNAME';
	const TYPE_ID_SEX = 'IDSEX';
	const TYPE_ID_BIRTHDAY = 'IDBIRTHDAY';
	const TYPE_ID_BIRTHCITY = 'IDBIRTHCITY';
	const TYPE_ID_ADDRESS = 'IDADDRESS';
	const TYPE_ID_CITY = 'IDCITY';
	const TYPE_ID_ZIP = 'IDZIP';
	const TYPE_ID_COUNTRY = 'IDCOUNTRY';
	const TYPE_ID_INITIALS = 'IDINITIALS';
	const TYPE_ID_SS_NUM = 'IDSSNUM';
	const TYPE_ID_PHONE = 'IDPHONE';
	const TYPE_ID_MOBILE = 'IDMOBILE';
	const TYPE_ID_MAIL = 'IDMAIL';
	const TYPE_ID_UID = 'IDUID';
	const TYPE_ID_PHOTO = 'IDPHOTO';
	const TYPE_ID_SIZE = 'IDSIZE';
	const TYPE_ID_WEIGHT = 'IDWEIGHT';
	const TYPE_ID_BLOOD = 'IDBLOOD';
	const TYPE_ID_CERT = 'IDCERT';
	const TYPE_ID_CERT_CA = 'IDCERTCA';
	const TYPE_ID_KPRI = 'IDKPRI';
	
	/** Official document parameters */
	const TYPE_OD_TYPE = 'ODTYPE';
	const TYPE_OD_NUMBER = 'ODNUMBER';
	const TYPE_OD_DATE = 'ODDATE';
	const TYPE_OD_VALIDITY = 'ODVALIDITY';
	const TYPE_OD_DELIVERY = 'ODDELIVERY';
	const TYPE_OD_IMG = 'ODIMG';
	
	/** Cloth size parameters */
	const TYPE_CS_CHESTSIZE = 'CSCHESTSIZE';
	const TYPE_CS_ROUNDBELLY = 'CSROUNDBELLY';
	const TYPE_CS_HIPS = 'CSHIPS';
	const TYPE_CS_CUPSIZEBRA = 'CSCUPSIZEBRA';
	const TYPE_CS_PANTSIZE = 'CSPANTSIZE';
	const TYPE_CS_DRESSSIZE = 'CSDRESSSIZE';
	const TYPE_CS_SHIRTSIZE = 'CSSHIRTSIZE';
	const TYPE_CS_SHOESSIZE = 'CSSHOESSIZE';
	
	/** Credit card parameters */
	const TYPE_CC_TYPE = 'CCTYPE';
	const TYPE_CC_NUMBER = 'CCNUMBER';
	const TYPE_CC_VALIDITY = 'CCVALIDITY';
	const TYPE_CC_CRYPTO = 'CCCRYPTO';
	const TYPE_CC_3DSECURE = 'CC3DSECURE';
	const TYPE_CC_PIN = 'CCPIN';
	const TYPE_CC_PHONEOPPO = 'CCPHONEOPPO';
	const TYPE_CC_IMG = 'CCIMG';
	
	/** Program account  */
	const TYPE_PA_PROG = 'PAPROG';
	
	/** Car info parameters */
	const TYPE_CI_MARK = 'CIMARK';
	const TYPE_CI_MODEL = 'CIMODEL';
	const TYPE_CI_NUMBER = 'CINUMBER';
	const TYPE_CI_SERIAL = 'CISERIAL';
	const TYPE_CI_DATE = 'CIDATE';
	const TYPE_CI_PHOTO = 'CIPHOTO';
	const TYPE_CI_DOCIMG = 'CIDOCIMG';
	
	/** Web account parameters */
	const TYPE_WA_URL = 'WAURL';
	const TYPE_WA_KEY16 = 'KEY16';
	const TYPE_WA_KEY32 = 'KEY32';
	const TYPE_WA_KEY64 = 'KEY64';
	const TYPE_WA_KEY128 = 'KEY128';

	/** Bank account parameters */
	const TYPE_BA_NAME = 'BANAME';
	const TYPE_BAADDRESS = 'BAADRESS';
	const TYPE_BA_PHONE = 'BAPHONE';
	const TYPE_BA_ACCOUNT = 'BAACCOUNT';
	const TYPE_BA_SWIFT = 'BASWIFT';
	const TYPE_BA_IBAN = 'BAIBAN';
	
	
	
	
	
	/* make this param needed. The user have to give a value for this. */
	const OPT_NEEDED = 'M';
	/* this param have to be unique into the file */
	const OPT_UNIQUE = 'U';
	/* make this param writable for user modification */
	const OPT_WRITABLE = 'W';
	const OPT_READ_ONLY = 'R';
	const OPT_HIDDEN = 'H';
	
	/** param type */
	private $type;
	/** param name */
	private $name;
	/** param display name (display at screen) */
	private $displayName;
	/** param option: is Unique ? Writable ? ... */
	private $opt;
	
	private $sec;
	
	/** the param value */
	private $value;
	
	// ********************************************************************************************
	/**
	* Initialize the comand
	* @param string $type param type
	* @param string $displayName A display name for the param
	**/
	public function __construct($type) {
		CDebugger::$debug->tracein('__construct', 'CParam');
		
		$this->type = $type;
		$this->name = '';
		$this->opt = '';
		$this->value = '';
		
		CDebugger::$debug->traceout(true);
	}
	
	public function fromXml($node){
		CDebugger::$debug->tracein('fromXml', 'CParam');
		
		$type = utf8_decode($node->getAttribute('TYPE'));
		$this->type = $type;
		
		$name = utf8_decode($node->getAttribute('NAME'));
		$this->name = $name;
		
		$displayname = utf8_decode($node->getAttribute('DISPLAYNAME'));
		$this->displayName = $displayname;
		
		$opt = utf8_decode($node->getAttribute('OPT'));
		$this->opt = $opt;
		
		$sec = utf8_decode($node->getAttribute('SEC'));
		$this->sec = $sec;
		
		if($node->firstChild != null)
			$this->value = $node->firstChild->nodeValue;
		
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	public function serializer($dom) {
		CDebugger::$debug->tracein('serializer', 'CParam');
		
		$xmlParam = $dom->createElement('PARAM');
		
		$type = utf8_encode($this->getType());
		$xmlParam->setAttribute('TYPE', $type);
		
		$name = utf8_encode($this->getName());
		$xmlParam->setAttribute('NAME', $name);
		
		$display = utf8_encode($this->getDisplayName());
		$xmlParam->setAttribute('DISPLAYNAME', $display);
		
		$opt = utf8_encode($this->getOpt());
		$xmlParam->setAttribute('OPT', $opt);
		
		$sec = utf8_encode($this->getSec());
		$xmlParam->setAttribute('SEC', $sec);
		
		$xmlParam->appendChild($dom->createTextNode($this->value));
		
		CDebugger::$debug->traceout(true);
		return $xmlParam;
	}
	
	/**
	* Add option to the param, use the constants class
	* @param string $opt param option
	*/
	public function addOpt($opt){
		for($i=0;$i<count($opt);$i++){
			if(strpos($this->opt, $opt[$i]) === false)
				$this->opt = $this->opt.$opt;
		}
	}
	
	/**
	* Remove specified option(s), use the constants class
	* @param string $opt param option
	*/
	public function removeOpt($opt){
		for($i=0;$i<count($opt);$i++){
			strreplace($this->opt[$i], $opt, '');
		}
	}
	
	// ********************************************************************************************
	public function getType() { return $this->type; }
	public function setType($type) { $this->type = $type; }
	
	public function getName() { return $this->name; }
	public function setName($name) { $this->name = $name; }
	
	public function getDisplayName() { return $this->displayName; }
	public function setDisplayName($displayName) { $this->displayName = $displayName; }
	
	public function getOpt() { return $this->opt; }
	public function setOpt($opt) { $this->opt = $opt; }
	
	public function getSec() { return $this->sec; }
	public function setSec($sec) { $this->sec = $sec; }
	
	public function getValue() { return $this->value; }
	public function setValue($value) { $this->value = $value; }
	
}

?>