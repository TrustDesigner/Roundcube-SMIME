<?phpif(isset($init_flag) == false)	die;	/*** **/class CCmdResp extends CCmd {	// ********************************************************************************************	private $result;	private $url;	private $msg;		// ********************************************************************************************	/**	* Initalize the comand	*/	public function __construct() {		parent::setValue(CCmd::CMD_RESP);				CDebugger::$debug->tracein('__construct', 'CCmdResp');		CDebugger::$debug->traceout(true);	}		public function fromXml($node){		CDebugger::$debug->tracein('fromXml', 'CCmdResp');				$this->result = $this->getXmlUTF8($node, 'RESULT');		$this->url = $this->getXmlUTF8($node, 'URL');		$this->msg = $this->getXmlUTF8($node, 'MSG');				CDebugger::$debug->traceout(true);	}		// ********************************************************************************************	public function serializer(&$dom, $kpub){		CDebugger::$debug->tracein('serializer', 'CCmdResp');				$xmlCmd = $dom->createElement('CMD');		$xmlCmd->setAttribute('VALUE', parent::getValue());				$this->setXmlUTF8($xmlCmd, 'RESULT', $this->getResult());		$this->setXmlUTF8($xmlCmd, 'URL', $this->getUrl());		$this->setXmlUTF8($xmlCmd, 'MSG', $this->getMsg());				CDebugger::$debug->traceout(true);		return $xmlCmd;	}		// ********************************************************************************************	public function getResult() { return $this->result; }	public function setResult($result) { $this->result = $result; }		public function getUrl() { return $this->url; }	public function setUrl($url) { $this->url = $url; }		public function getMsg() { return $this->msg; }	public function setMsg($msg) { $this->msg = $msg; }	}?>