<?php
/**
 * Shotngetapi / CWaitResponse
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
	
/*
* This class is only use by the javascript listener. Check the certphone response and return the result.
*/
class CWaitResponse {
	// ********************************************************************************************
	/* the rand value */
	private $rand;
	/* rand files directory. */
	private $randPath;
	/* time between 2 tests. */
	private $freq;

	// ********************************************************************************************
	/*
	* Initialize the class.
	* @param string $rand the rand value
	* string $randPath the rand files directory
	* @param int $freq Time between 2 tests.
	*/
	public function __construct($rand, $randPath, $freq) {
		CDebugger::$debug->tracein('__construct', 'CWaitResponse');
		
		$this->rand = $rand;
		$this->randPath = $randPath;
		$this->freq = $freq;
		
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	/*
	* Check the certphone response and wait a few secondes equals at $freq value.
	* @return int the code error. Use CError constants to handle the code.
	*/
	public function hasReplied() {
		CDebugger::$debug->tracein('hasReplied', 'CWaitResponse');
		
		$result = CErrors::RESULT_NO_ERROR;
		$filename = $this->randPath.$this->rand;
		$timeout = $this->freq;
		
		do {
  		if (file_exists($filename.'.rand')){
  			if(file_exists($filename.'.rep')){
  				$result = CErrors::RESULT_NO_ERROR;
				break;
  			}
  			else {
  				sleep(1);
  				
  				if(file_exists($filename.'.rep')){
  					$result = CErrors::RESULT_NO_ERROR;
  					break;
  				}
  				else {
  					$result = CErrors::RESULT_SERVER_ERROR;
  				}
  			}
  		}
  		else {
		  $result = CErrors::RESULT_TIMEOUT;
  		}
  	}while($timeout-- > 0);
		
		CDebugger::$debug->traceout($result);
		return $result;
	}
	
	// ********************************************************************************************
	
}

?>