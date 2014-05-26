<?php

if(isset($init_flag) == false)
	die;
	
/**
* 
**/
class CRequestParameters {
	// ********************************************************************************************
	private $imgPath;
	private $listeningFunction;
	private $shotngetLink;
	
	private $randPath;
	private $rand;
	
	// ********************************************************************************************
	public function __construct() {
		
	}
	
	// ********************************************************************************************
	public function getImgPath() { return $this->imgPath; }
	public function setImgPath($imgPath) { $this->imgPath = $imgPath; }
	
	public function getListeningFunction() { return $this->listeningFunction; }
	public function setListeningFunction($listeningFunction) { $this->listeningFunction = $listeningFunction; }
	
	public function getRand() { return $this->rand; }
	public function setRand($rand) { $this->rand = $rand; }
	
	public function getRandPath() { return $this->randPath; }
	public function setRandPath($randPath) { $this->randPath = $randPath; }
	
	public function getShotngetLink() { return $this->shotngetLink; }
	public function setShotngetLink($shotngetLink) { $this->shotngetLink = $shotngetLink; }
	
}

?>