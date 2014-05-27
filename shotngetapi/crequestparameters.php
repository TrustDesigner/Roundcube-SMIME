<?php
/**
 * Shotngetapi / CRequestParameters
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