<?php
/**
 * Shotngetapi / CUtils
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
* Miscellaneous method use by the api.
**/
class CUtils{
	
	/**
		Get an XML value from a DOMDocument.
		@param $doc DOMDocument The source DOMDocument.
		@param $name string The Tag value.
		@return Return the Value or false
	**/
	public static function getXMLValue($doc, $name){
		$items = $doc->getElementsByTagName($name);
		if( $items == NULL )
			return false;
			
		$item = $items->item(0);
		if( $item == NULL )
			return false;
			
		if( $item->firstChild == NULL )
			return false;
     
		return $item->firstChild->nodeValue;
	}
	
	/**
	* This function delete all temp files used by the api.
	* @param string $randPath the rand files directory.
	* @param string $tempPath the temp files directory
	* @param string $rand the rand value
	*/
	public static function cleanTempFiles($apiParameters, $rand){
		$randPath = $apiParameters->getConf()->getRandPath();
		$filename = $randPath.$rand;
		$userData = CUtils::getUserData($randPath, $rand);
		
		if (file_exists($filename.'.rand'))
			unlink($filename.'.rand'); //supprime le fichier .rand
		
		if (file_exists($filename.'.rep'))
			unlink($filename.'.rep'); //supprime le fichier .rep
			
		if (file_exists($filename.'.temp'))
			unlink($filename.'.temp'); //supprime le fichier .temp
			
		if (file_exists($filename.'.cmds'))
			unlink($filename.'.cmds'); //supprime le fichier .cmds
			
		if (file_exists($filename.'.data'))
			unlink($filename.'.data'); //supprime le fichier .data
		
		$filename = $apiParameters->getTempPath().$userData->qrcode_img_name;
		
		if (file_exists($filename) && is_dir($filename) == false)
			unlink($filename); //supprime l'image du qr code
			
		
		$maxTime = 5 * 60; //seconds
		CUtils::deleteOldFiles($apiParameters->getTempPath(), $maxTime);
		CUtils::deleteOldFiles($apiParameters->getConf()->getRandPath(), $maxTime);
	}
	
	/**
	* Deletes old files in temp & rand path
	*/
	public static function deleteOldFiles($path, $maxTime) {
		$dir = opendir($path);
		$hasIndex = false;
		
		while($file = @readdir($dir)) {
			if(is_dir($path.'/'.$file)&& $file != '.' && $file != '..') {
				CUtils::deleteOldFiles($path.'/'.file.'/', $time);
			}
			else {
				if($file != 'index.php') {
					$lastAccessTime = fileatime($path.'/'.$file);
					$currentTime = time();
					$time = ($currentTime - $lastAccessTime);
					
					if($time >= $maxTime) {
						@unlink($path.'/'.$file);
					}
				}
				else
					$hasIndex = true;
            }
		}
		
		if($hasIndex == false)
			CUtils::generateIndexFile($path);
	}
	
	/**
	* Create an index.php file with a redirection instruction 
	* to protect the path passed in parameter
	*/
	public static function generateIndexFile($path) {
		$file = fopen($path.'index.php', 'w+');
		fputs($file, '<?php header("Location: /"); ?>');
		fclose($file);
	}
	
	/**
	* Verify the Rand value(is numeric ....)
	*/
	public static function checkRand($rand){
		$autorized = '0123456789';
		
		$ok = true;
		
		for($i=0;$i<strlen($rand);$i++){
			$chr = $rand[$i];
			
			if(strpos($autorized, $chr) === false)
				$ok = false;
		}
		
		return $ok;
	}
	
	/**
	* 
	*/
	public static function setUserData($randPath, $rand, $key, $data) {
		$userData = CUtils::getUserData($randPath, $rand);
		$userData->$key = $data;
		
		$file = fopen($randPath.$rand.'.data', 'w+');
		$jsonData = json_encode($userData);
		fputs($file, $jsonData);
		fclose($file);
	}
	
	public static function setUserDataWithRequestParameters($requestParameters, $key, $data) {
		$randPath = $requestParameters->getRandPath();
		$rand = $requestParameters->getRand();;
		CUtils::setUserData($randPath, $rand, $key, $data);
	}
	
	/**
	* 
	*/
	public static function getUserData($randPath, $rand) {
		$filename = $randPath.$rand.'.data';
		
		if(file_exists($filename)) {
			$jsonData = file_get_contents($filename);
			return json_decode ($jsonData);
		}
	}
	
	public static function getUserDataWithRequestParameters($requestParameters) {
		$randPath = $requestParameters->getRandPath();
		$rand = $requestParameters->getRand();;
		return CUtils::getUserData($randPath, $rand);
	}
}

?>