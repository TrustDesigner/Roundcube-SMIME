<?php

if(isset($init_flag) == false)
	die;
	
/**
* This class contains method to generate a request for sending comands to the user.
**/
class CRequest{

	// ********************************************************************************************
	/* rand files directory */
	private $randPath;
	
	/* the url on witch cerrtphone have to send reply (by default the response.php directory) */
	private $urlRep;
	
	/* Contains a list of CCmd. */
	private $cmds = array();
	
	/* The rand value is use to link certphone session & server session. */
	private $rand;
	
	private $kpub;
	private $SHOTNGETKpub;
	
	private $urlSign;
	
	private $response;
	
	// ********************************************************************************************
	/*
	* Initialize the request.
	* @param CConf A CConf object.
	*/
	public function __construct($urlRep) {
		CDebugger::$debug->tracein('__construct', 'CRequest');
		
		$this->randPath = Config::$TEMP_PATH;
		$this->urlRep = $urlRep;
		
		//$this->response = $response;
		
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	/**
	* You need to call this function to init the request & generate a rand value.
	*/
	public function init(){
		CDebugger::$debug->tracein('init', 'CRequest');
		
		$generate = true;
		while($generate){
			$rand = '';
			for( $i=0;$i<8;$i++ )
				$rand .= rand( 10, 99 );

			$randFile = $this->randPath.$rand.'.rand';

			if(!file_exists($randFile)){
				$file = fopen( $randFile, 'w+');
				fclose( $file );
				$generate = false;
			}
		}
		
		$this->rand = $rand;
		
		CDebugger::$debug->traceout(true);
	}
	
	public function initWithRand($rand){
		CDebugger::$debug->tracein('initWithRand', 'CRequest');
		
		$this->rand = $rand;
		
		CDebugger::$debug->traceout(true);
	}
	
	/*
	* Add to CCmd to the request. Comands are displayed in the same order as added.
	* @param CCmd $cmd The comand.
	*/
	public function addCmd($cmd){
		CDebugger::$debug->tracein('addCmd', 'CRequest');
		
		$result = true;
		
		if($cmd != null){
			$this->cmds[count($this->cmds)] = $cmd;
		}
		else {
			$result = false;
		}
		
		CDebugger::$debug->traceout($result);
		return $result;
	}
	
	/*
	* This method update the request by serializing all comands into the rand file.
	*/
	public function flush(){
		CDebugger::$debug->tracein('flush', 'CRequest');
		
		$result = true;
		$filename = $this->randPath.$this->rand.'.rand';
	
		//si le fichier n'existe plus c'est que la session a expirée
		if (!file_exists($filename))
		{
			$result = CErrors::RESULT_TIMEOUT;
		}
		else {
			$file = fopen($filename, 'w+');
			fputs($file, $this->serializer('SRVTOCP'));
			fclose($file);
			
			$result = CErrors::RESULT_NO_ERROR;
		}
		
		CDebugger::$debug->traceout($result);
		return $result;
	}
	
	/*
	* Serialize all commands
	* @return $string The serialized xml.
	*/
	public function serializer($racine){
		CDebugger::$debug->tracein('serializer', 'CRequest');
		
		$dom = new DomDocument('1.0', 'UTF-8');
		
		$srvtocp = $dom->createElement($racine);
		$ver = $dom->createElement('VER');
		$ver->appendChild($dom->createTextNode(CConstants::API_VERSION));
		$srvtocp->appendChild($ver);
		
		$xmlRand = $dom->createElement('RAND');
		$xmlRand->appendChild($dom->createTextNode($this->rand));
		$srvtocp->appendChild($xmlRand);
		
		/*$xmlKpub = $dom->createElement('KPUB');
		$xmlKpub->appendChild($dom->createTextNode($this->kpub));
		$srvtocp->appendChild($xmlKpub);*/
		
		if($this->urlSign != null)
		{
			$xmlUrlSign = $dom->createElement('URLSIGN');
			$xmlUrlSign->appendChild($dom->createTextNode($this->urlSign));
			$srvtocp->appendChild($xmlUrlSign);
		}
		else
		{
			if($this->response != null)
			{
				$version = $this->response->getVersion();
				$version = substr($version, 2);
			}
			else
				$version = '1.4';
			
			if($version == '1.3')
				$urlsign = file_get_contents(Config::$OLDURLSIGN);
			else
				$urlsign = file_get_contents(Config::$URLSIGN);
			
			$xmlUrlSign = $dom->createElement('URLSIGN');
			$xmlUrlSign->appendChild($dom->createTextNode($urlsign));
			$srvtocp->appendChild($xmlUrlSign);
		}
		
		$cmds = $dom->createElement('CMDS');
		foreach($this->cmds as $cmd){
			$node = $cmd->serializer($dom, $this->SHOTNGETKpub);
			
			if($node != null)
				$cmds->appendChild($node);
		}
		$srvtocp->appendChild($cmds);
		
		$dom->appendChild($srvtocp);
		
		CDebugger::$debug->traceout(true);
		
		$xml = $dom->saveXML();
		CDebugger::$debug->trace($xml);
		return $xml;
	}
	
  public function getListeningFunction($waitPage, $timeout = 60, $freq = 10) {
		return 'listening('.$this->getRand().', \''.$this->randPath.'\', \''.$waitPage.'\', '.$timeout.', '.$freq.')';
  }
	
	// ********************************************************************************************
	/**
	* handle the certphone response. This method is calling by the response.php file.
	* @param string $data The xml data sending by certphone.
	* @return string The xml response for certphone
	**/
	public static function handleResponse($apiParameters, $handler){
		CDebugger::$debug->tracein('handleResponse', 'CRequest');
		
		if( isset($HTTP_RAW_POST_DATA))
			$data = $HTTP_RAW_POST_DATA;
		else {
			$data = file_get_contents( 'php://input' );
		}
		
		if($data == ''){
			require($apiParameters->getApiPath().'/getcertphone.html');
		}
		else { //appel depuis Certphone
			header ("Content-Type:text/xml");
			
			$randPath = Config::$TEMP_PATH;
		
			//onrécupère la commande
			$dataxml = new DOMDocument();
			$dataxml->loadXML($data);
			
			$rand = CUtils::GetXMLValue($dataxml, 'RAND');
			
			$cmds = $dataxml->getElementsByTagName('CMDS')->item(0);

			$firstCmd = $cmds->getElementsByTagName('CMD')->item(0);

			if($firstCmd != null)
				$cmd = $firstCmd->getAttribute('VALUE');
			else
				$cmd = "";
			
			if($cmd == "INIT"){ //première requete
				$filename = $randPath.$rand;
				
				$initCmd = new CCmdInit();
				$initCmd->fromXml($firstCmd);
		
				if (!file_exists($filename.'.rand'))//si le fichier n'existe plus c'est qie la session a expirée
				{
					CDebugger::$debug->trace('Timeout: fichier .rand non trouvé ! filename: '.$filename);
					CDebugger::$debug->traceout(CErrors::RESULT_TIMEOUT);
					return CErrors::RESULT_TIMEOUT;
				}
				else {
					//parse the shonget response
					$responseData = new CResponse(null, null);
					$responseData->parseData($data);
					
					$request = new CRequest('');
					$request->setSHOTNGETResponse($responseData);
					$request->initWithRand($rand);
					
					$kpub = file_get_contents(Config::$KPUB);
					$request->setKpub($kpub);
					
					$parameters = new CRequestParameters();
					$parameters->setRand($rand);
					$parameters->setRandPath($randPath);
					
					$handler->onInitRequest($initCmd, $parameters, $request);
					
					//set the SHOTNGET kpub
					$request->setSHOTNGETKpub($initCmd->getKpub());
					
					$file = fopen($filename.'.temp', 'w+');
					fputs($file, $data);
					fclose($file);
					
					//create .cmds file & clear all datas
					$file = fopen($filename.'.cmds', 'w+');
					fputs($file, '');
					fclose($file);
					
					//create .cmds file & clear all datas
					$file = fopen($filename.'.rand', 'w+');
					fputs($file, $request->serializer('SRVTOCP'));
					fclose($file);

					$xml = @file_get_contents($filename.'.rand');
					CDebugger::$debug->trace($xml);
					
					CDebugger::$debug->traceout(true);
					return $xml;
				}
			}
			else { //seconde requête
				$filename = $randPath.$rand;
				if (!file_exists($filename.'.rand'))//si le fichier n'existe plus c'est qie la session a expirée
				{
					$req = new CRequest('');
					$req->initWithRand($rand);
					$cmdResp = new CCmdResp();
					$cmdResp->setResult(CErrors::RESULT_TIMEOUT);
					
					$req->addCmd($cmdResp);
					
					$xml = $req->serializer('SRVTOCP');
					CDebugger::$debug->trace($xml);
					
					CDebugger::$debug->traceout(true);
					return $xml;
				}
				else {
					//parse the shonget response
					$responseData = new CResponse($apiParameters, null);
					$responseData->parseData($data);
					$responseCmds = $responseData->getCmds();
					
					//check shotnget response
					$request = new CRequest('');
					
					$parameters = new CRequestParameters();
					$parameters->setRand($rand);
					$parameters->setRandPath($randPath);
					
					$handler->onResponseReceived($responseData, $parameters, $request);
					
					//if no RespCmd or RespCmd with no error
					//save current cmds and the old's.
					if($request != null){
						$cmds = $request->getCmds();
						
						if($cmds != null){
							$cmd = null;
							
							foreach($cmds as $reqCmd){
								if($reqCmd->getValue() == CCmd::CMD_RESP)
									$cmd = $reqCmd;
							}
							
							if($cmd != null){ //has a respcmd
								if($cmd->getValue() == CCmd::CMD_RESP){
									if($cmd->getResult() == CErrors::RESULT_NO_ERROR) {
										CRequest::saveCurrentCmds($apiParameters, $rand, $responseCmds);
										$file = fopen($filename.'.rep', 'w+');
										fclose($file);
									}
								}
							}
							else { //no RespCmd
								CRequest::saveCurrentCmds($apiParameters, $rand, $responseCmds);
							}
						}
						
						$xml = $request->serializer('SRVTOCP');
						CDebugger::$debug->trace($xml);
						
						CDebugger::$debug->traceout(true);
						return $xml;
					}
					else {
						$cmdResp = new CCmdResp();
						$cmdResp->setResult(CErrors::RESULT_SERVER_ERROR);
						$cmdResp->setUrl($url);
						$cmdResp->setMsg($msg);
						
						$req = new CRequest('');
						$req->initWithRand($rand);
						$req->addCmd($cmdResp);
						
						$file = fopen($filename.'.rep', 'w+');
						fclose($file);
						
						$xml = $req->serializer('SRVTOCP');
						CDebugger::$debug->trace($xml);
						
						CDebugger::$debug->traceout(true);
						return $xml;
					}
				}
			}
		}
	}
	
	/**
	* Get old cmds, push it in current cmds abd save all cmds.
	*/
	private static function saveCurrentCmds($apiParameters, $rand, $cmds) {
		CDebugger::$debug->tracein('saveCurrentCmds', 'CRequest');
		
		$randPath = Config::$TEMP_PATH;
		
		//récupère les anciennes cmds et les ajoute aux nouvelles
		$oldResponse = new CResponse($apiParameters, $rand);
		$oldResponse->parse();
		$oldCmds = $oldResponse->getCmds();
		
		if(count($oldCmds) != 0)
			array_push($cmds, $oldCmds);
		
		//construit une requete temp pour serializer les cmds
		$req = new CRequest(null);
		$req->initWithRand($rand);
		$req->setCmds($cmds);
		$filename = $randPath.$rand;
		$file = fopen($filename.'.cmds', 'w+');
		fputs($file, $req->serializer('CPTOSRV'));
		fclose($file);
		
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	public function getRand() { return $this->rand; }
	private function setRand($rand) { $this->rand = $rand; }
	
	public function getRandPath() { return $this->randPath; }
	
	public function getUrlRep() { return $this->urlRep; }
	
	public function getCmds(){ return $this->cmds;}
	public function setCmds($cmds){ $this->cmds = $cmds;}
	
	public function setKpub($kpub) { $this->kpub = $kpub; }
	
	public function setSHOTNGETKpub($SHOTNGETKpub) { $this->SHOTNGETKpub = $SHOTNGETKpub; }

	public function setUrlSign($urlSign) { $this->urlSign = $urlSign; }
	
	public function setSHOTNGETResponse($response) { $this->response = $response; }
	
}

?>