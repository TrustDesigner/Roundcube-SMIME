<?php

if(isset($init_flag) == false)
	die;
	
/**
* This comand ask a web account formated using a file template.
* Have to specifiate type & password size.
**/
class CCmdAuth extends CCmd {
	// ********************************************************************************************
	/** ask a numeric password */
	const TYPEPWD_NUMERIC = '1';
	
	const TYPEPWD_ALPHA = '2';
	/** ask an alphanumeric password */
	const TYPEPWD_ALPHANUMERIC = '3';
	/** ask a password autorize special caractere and alphanumeric values */
	const TYPEPWD_SPECIAL = '4';
	
	const OPT_NEW = 'NEW';
	const OPT_CHANGE = 'CHANGE';
	const OPT_NONE = 'NONE';
	
	private $label;
	
	/** password type */
	private $typePwd;
	/** the max password size. */
	private $sizePwd;
	
	private $opt;
	
	/** the account file asked */
	private $file;
	
	// ********************************************************************************************
	/**
	* Initalize the comand
	* @param string $typePwd: password type (use class constant)
	* @param int $sizePwd Max password size
	* @param CFile $file The template file asked to the user.
	*/
	public function __construct($typePwd, $sizePwd, $file) {
		CDebugger::$debug->tracein('__construct', 'CCmdAuth');
		
		parent::setValue(CCmd::CMD_AUTH);
		
		$this->typePwd = $typePwd;
		$this->sizePwd = $sizePwd;
		$this->file = $file;
		$this->opt = CCmdAuth::OPT_NONE;
		
		CDebugger::$debug->traceout(true);
	}
	
	public function fromXml($node){
		CDebugger::$debug->tracein('fromXml', 'CCmdAuth');
		
		$this->label = $this->getXmlUTF8($node, 'LABEL');
		$this->typePwd = $this->getXmlUTF8($node, 'TYPEPWD');
		$this->sizePwd = $this->getXmlUTF8($node, 'SIZEPWD');
		$this->opt = $this->getXmlUTF8($node, 'OPT');
		
		$xmlCpdata = $node->getElementsByTagName('CPDATA')->item(0);
		$file = new CFile('', '');
		$file->fromXml($xmlCpdata);
		
		$this->file = $file;
		
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	public function serializer(&$dom, $kpub){
		CDebugger::$debug->tracein('serializer', 'CCmdAuth');
		
		$xmlCmd = $dom->createElement('CMD');
		$xmlCmd->setAttribute('VALUE', parent::getValue());
		
    $this->setXmlUTF8($xmlCmd, 'LABEL', $this->getLabel());
    $this->setXmlUTF8($xmlCmd, 'TYPEPWD', $this->getTypePwd());
    $this->setXmlUTF8($xmlCmd, 'SIZEPWD', $this->getSizePwd());
    $this->setXmlUTF8($xmlCmd, 'OPT', $this->getOpt());
		
		//serialize template
		$xmlCmd->appendChild($this->file->serializer($dom, $kpub));
		
		CDebugger::$debug->traceout(true);
		return $xmlCmd;
	}
	
	// ********************************************************************************************
	public function getFile()
	{
		if($this->file->isCrypted() == false)
			return $this->file;
		
		$kpri = @file_get_contents(Config::$KPRI);
		
		//uncrpt file
		$uncryptedFile = FileManager::uncryptFile($this->file, $kpri);
		
		return $uncryptedFile;
	}
	
	// ********************************************************************************************
	public function getLabel() { return $this->label; }
	public function setLabel($label) { $this->label = $label; }
	
	public function getTypePwd() { return $this->typePwd; }
	public function setTypePwd($typePwd) { $this->typePwd = $typePwd; }
	
	public function getSizePwd() { return $this->sizePwd; }
	public function setSizePwd($sizePwd) { $this->sizePwd = $sizePwd; }
	
	public function getOpt() { return $this->opt; }
	public function setOpt($opt) { $this->opt = $opt; }
	
	public function setFile($file) { $this->file = $file; }
	
}

?>