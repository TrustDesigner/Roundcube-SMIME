<?php
/**
 * Shotngetapi / CConf
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