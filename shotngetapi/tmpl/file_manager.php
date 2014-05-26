<?php

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