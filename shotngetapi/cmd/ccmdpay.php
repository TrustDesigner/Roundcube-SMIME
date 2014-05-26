<?php

if(isset($init_flag) == false)
	die;
	
/**
* This comand ask a creditcard for online payments.
**/
class CCmdPay extends CCmd {
	// ********************************************************************************************
	private $label;
	/** The amount of comand (displayed at certphone screen) */
	private $amount;
	/** the amount currency (ex: $, €, euro, dollars, ...) */
	private $currency;
	
	private $bankname;
	
	private $bankurl;
	
	private $confurl;
	
	/** the card returned by certphone */
	private $creditCard;
	
	// ********************************************************************************************
	/**
	* Initalize the comand
	* @param double $amount command amount.
	* @param string $currency amount currency.
	*/
	public function __construct($amount, $currency) {
		parent::setValue(CCmd::CMD_PAY);
		
		CDebugger::$debug->tracein('__construct', 'CCmdPay');
		
		$this->amount = $amount;
		$this->currency = $currency;
		
		CDebugger::$debug->traceout(true);
	}
	
	public function fromXml($node){
		CDebugger::$debug->tracein('fromXml', 'CCmdPay');
		
		$this->label = $this->getXmlUTF8($node, 'LABEL');
		$this->amount= $this->getXmlUTF8($node, 'AMOUNT');
		$this->currency = $this->getXmlUTF8($node, 'CURRENCY');
		$this->bankname = $this->getXmlUTF8($node, 'BANKNAME');
		$this->bankurl = $this->getXmlUTF8($node, 'BANKURL');
		$this->confurl = $this->getXmlUTF8($node, 'CONFURL');
		
		$xmlCpdata = $node->getElementsByTagName('CPDATA');
		if($xmlCpdata != null){
			$file = new CFile('', '');
			$file->fromXml($xmlCpdata->item(0));
		}
		
		$this->creditCard = $file;
		
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	public function serializer(&$dom, $kpub){
		CDebugger::$debug->tracein('serializer', 'CCmdPay');
		
		$xmlCmd = $dom->createElement('CMD');
		$xmlCmd->setAttribute('VALUE', parent::getValue());
		
		$this->setXmlUTF8($xmlCmd, 'LABEL', $this->getLabel());
		$this->setXmlUTF8($xmlCmd, 'AMOUNT', $this->getAmount());
		$this->setXmlUTF8($xmlCmd, 'CURRENCY', $this->getCurrency());
		$this->setXmlUTF8($xmlCmd, 'BANKNAME', $this->getBankname());
		$this->setXmlUTF8($xmlCmd, 'BANKURL', $this->getBankurl());
		$this->setXmlUTF8($xmlCmd, 'CONFURL', $this->getConfurl());
		
		//serialize template
		if($this->creditCard != null)
			$xmlCmd->appendChild($this->creditCard->serializer($dom));
		
		CDebugger::$debug->traceout(true);
		return $xmlCmd;
	}
	
	// ********************************************************************************************
	public function getLabel() { return $this->label; }
	public function setLabel($label) { $this->label = $label; }
	
	public function getCreditCard() { return $this->creditCard; }
	public function setCreditCard($creditCard) { $this->creditCard = $creditCard; }
	
	public function getAmount() { return $this->amount; }
	public function setAmount($amount) { $this->amount = $amount; }
	
	public function getCurrency() { return $this->currency; }
	public function setCurrency($currency) { $this->currency = $currency; }
	
	public function getBankname() { return $this->bankname; }
	public function setBankname($bankname) { $this->bankname = $bankname; }
	
	public function getBankurl() { return $this->bankurl; }
	public function setBankurl($bankurl) { $this->bankurl = $bankurl; }
	
	public function getConfurl() { return $this->confurl; }
	public function setConfurl($confurl) { $this->confurl = $confurl; }
	
}

?>