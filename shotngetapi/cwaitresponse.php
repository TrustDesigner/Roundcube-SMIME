<?php

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