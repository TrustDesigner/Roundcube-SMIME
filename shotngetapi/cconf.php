<?php

if(isset($init_flag) == false)
	die;

/**
* This class load the configuration file & storage a list of parameters found in the file.
* The configuration file is named "certphone_api.conf".
**/
class CConf {
	// ********************************************************************************************
	const RAND_PATH = 'randPath';
	const URLSIGN_PATH = 'urlSignPath';
	const KPUB_PATH = 'kpubPath';
	const KPRI_PATH = 'kpriPath';
	
	private $loaded = false;
	private $params = array();
	
	// ********************************************************************************************
	public function __construct($filename) {
		CDebugger::$debug->tracein('__construct', 'CConf');
		
		$this->loaded = $this->loadConf($filename);
		
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	/*
	* Try to Load configuration by loading file specified by the $filename.
	* @param string $filename Path of certphone_api.conf
	* @return boolean True if configuration successfuly loaded.
	*/
	private function loadConf($filename){
		CDebugger::$debug->tracein('loadConf', 'CConf');
		
		$result = true;
	
		if (!$fconf = fopen($filename,'r')) {
			CDebugger::$debug->trace('Impossible d\'ouvrir le fichier.');
			$result = false;
		}
		else {
			while(!feof($fconf)){
				$row = fgets($fconf, 1024);
				
				if(strlen($row) != 1 && strlen($row) != 0){
					if($row[0] != '#'){
						$param = preg_split('/=/', $row);
						
						if(count($param) == 2){
							$param[1] = substr($param[1], 0, strlen($param[1])-1);
							
							$this->params[$param[0]] = $param[1];
						}
						else {
							CDebugger::$debug->trace('Fichier de config vide ou aucun item charge');
							$result = false;
						}
					}
				}
			}
			
			CDebugger::$debug->traceout($result);
			return result;
		}
	}
	
	/*
	* Return the parameter value specified by $name.
	* @param string $name Parameter name
	* @return string parameter value.
	*/
	public function get($name){
		$value = $this->params[$name];
		
		//replace apiPath value
		$value = str_replace('[apiPath]', CConstants::$API_PATH, $value);
		
		return $value;
	}
	
	// ********************************************************************************************
	public function isLoaded() { return $this->loaded; }
	
}

?>