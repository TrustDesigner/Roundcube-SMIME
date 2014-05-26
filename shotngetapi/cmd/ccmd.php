<?php

if(isset($init_flag) == false)
	die;
	
/**
* This class is a base class for all command avaible with the api
* In this class, you can get command values using constants.
**/
abstract class CCmd {
	// ********************************************************************************************
	/** Command url: display a web page with the specified url */
	const CMD_URL = 'URL';
	/** Command cgv: display terms of sale, the user have to agree this page to continue */
	const CMD_CGV = 'CGV';
	
	const CMD_CONNECT = 'CONNECT';

	const CMD_DISCONNECT = 'DISCONNECT';
	
	/** Command authentication: ask account information for registration or conextion */
	const CMD_AUTH = 'AUTH';
	/** Command read: ask information. can't be a web account or paycard file. */
	const CMD_READ = 'READ';
	
	const CMD_RESP = 'RESP';
	
	const CMD_INIT = 'INIT';
	
	/** Command Payment: Ask payment information for online payment */
	const CMD_PAY = 'PAY';
	/**  */
	const CMD_PLUGIN = 'PLUGIN';

	const CMD_CBC = 'CBC';

	const CMD_LIST = 'LIST';

	const CMD_CERT = 'CERT';
	
	/** Command type */
	private $value;
	
	// ********************************************************************************************
	public function __construct() {
		
	}
	
	/**
	* Load the command by passing a xml node in argument
	* @param $node The xml node who contains class atributes.
	*/
	public abstract function fromXml($node);
	
	// ********************************************************************************************
	/**
	* Use by the api. Convert the comand to xml for sending.
	* @param DOMDocument $dom: the target xml document
	* @return $node return a xml node can be added to the $dom
	*/
	public abstract function serializer(&$dom, $kpub);
	
	// ********************************************************************************************
	public function getValue() { return $this->value; }
	protected function setValue($value) { $this->value = $value; }
	
	public function getXmlDefault($node, $name, $default) {
  	$xmlNode = $node->getElementsByTagName($name);
		if($xmlNode->length == 1 && $xmlNode->item(0)->firstChild != null)
		{
			return $xmlNode->item(0)->firstChild->nodeValue;
		}
		
		return $default; 
  }
  
  public function getXml($node, $name) {
  	return $this->getXmlDefault($node, $name, false); 
  }
  
	public function getXmlUTF8($node, $name) {
  	$ret = $this->getXml($node, $name);
  	if($ret !== false)
  	  $ret = utf8_decode($ret);		
		
		return $ret; 
  }
  
  public function setXml($node, $name, $value) {
    if($node !== false && $name !== false && $value !== false && $value != "") {
      $xmlNode = $node->ownerDocument->createElement($name);
      if($xmlNode !== false) {
        $xmlNode->appendChild($node->ownerDocument->createTextNode($value));
        $node->appendChild($xmlNode);
        return true;
      }
    }
    return false;
  }
  
  public function setXmlUTF8($node, $name, $value) {
    return $this->setXml($node, $name, utf8_encode($value));
  }
}

?>