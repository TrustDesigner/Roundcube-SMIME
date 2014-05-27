<?php
/**
 * Shotngetapi / FileManager
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
		
class FileManager
{
	// ********************************************************************************************
	
	// ********************************************************************************************
	
	// ********************************************************************************************
	public static function copyFile($file)
	{
		$copy = new CFile($file->getType());
		
		$copy->setId($file->getId());

		$copy->setDisplay($file->getDisplay());
		$copy->setUkey($file->getUkey());
		
		foreach($file->getParams() as $param)
			$copy->addParam(FileManager::copyParam($param));
		
		return $copy;
	}
	
	public static function copyParam($param)
	{
		$copy = new CParam($param->getType());
		
		$copy->setName($param->getName());
		$copy->setDisplayName($param->getDisplayName());
		$copy->setOpt($param->getOpt());
		$copy->setSec($param->getSec());
		$copy->setValue($param->getValue());
		
		return $copy;
	}
	
	// ********************************************************************************************
	public static function uncryptFile($file, $privateKey)
	{
		//local copy of file
		$uncrypted = FileManager::copyFile($file);
		
		//uncrypt ukey
		$cryptedUkey = $uncrypted->getUkey();
		$cryptedUkey = base64_decode($cryptedUkey);
		
		$uncryptedUkey = CCrypto::rsaPrivateDecrypt($privateKey, $cryptedUkey);
		
		//decrypt params
		foreach ($uncrypted->getParams() as $param)
		{
			if($param->getSec() != "P")
			{
				$cryptedValue = $param->getValue();
				$cryptedValue = base64_decode($cryptedValue);
				
				$uncryptedValue = CCrypto::aesDecrypt($uncryptedUkey, $cryptedValue);
				$param->setValue($uncryptedValue);
			}
		}
		
		return $uncrypted;
	}
	
	// ********************************************************************************************
	public static function cryptFile(&$file, $publicKey)
	{
		
	}
	
	// ********************************************************************************************
	
}

?>