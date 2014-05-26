<?php

if(isset($init_flag) == false)
	die;
	
/**
* This class contains static method to encryp, decrypt data and generate keys
* for AES and RSA algorhythm.
**/
class CCrypto {
	// ********************************************************************************************
	const BEGIN_PRIVATE_KEY = '-----BEGIN RSA PRIVATE KEY-----';
	const END_PRIVATE_KEY = '-----END RSA PRIVATE KEY-----';
	
	const BEGIN_PUBLIC_KEY = '-----BEGIN PUBLIC KEY-----';
	const END_PUBLIC_KEY = '-----END PUBLIC KEY-----';
	
	// ************************************************ ********************************************
	private static $IV = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
	
	// ********************************************************************************************
	/**
	* Encrypt the input data with the key parameter
	* Work with aes 128 only !
	* @param $key String contains the key bytes.
	* @param $data Input data to encrypt.
	* @return The encrypted data.
	*/
	public static function aesEncrypt($key, $data) {
		$enc = MCRYPT_RIJNDAEL_128;
		$mode = MCRYPT_MODE_CBC;
		
		//$block = mcrypt_get_block_size('aes', 'cbc');
		$block = 16;
		if (($pad = $block - (strlen($data) % $block)) < $block)
			$data .= str_repeat(chr($pad), $pad);
	
		return mcrypt_encrypt($enc, $key, $data, $mode, CCrypto::$IV); 
	}
	
	/**
	* Decrypt the input data with the key parameter.
	* Work with aes 128 only !
	* @param $key String contains the key bytes.
	* @param $data the input data for decryption.
	* @return the decrypted data.
	*/
	public static function aesDecrypt($key, $data) {
		$enc = MCRYPT_RIJNDAEL_128;
		$mode = MCRYPT_MODE_CBC;
		
		$decrypted = mcrypt_decrypt($enc, $key, $data, $mode, CCrypto::$IV);
		
		$length = strlen($decrypted);
		$padding = ord($decrypted[$length-1]);
		$decrypted = substr($decrypted, 0, -$padding);
		
		return $decrypted;
	}
	
	/**
	* Generate aes key.
	* @param $keySize Key size in bits.
	* @return The new generated key.
	*/
	public static function aesGenerateKey($keySize) {
		if($keySize % 8 != 0)
			return null;
			
		$strLen = $keySize / 8;
	
		$dic = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$key = '';
		
		for($i=0;$i<$strLen;$i++) {
			$rand = rand(0, strlen($dic) - 1);
			$key .= $dic[$rand];
		}
		return $key;
	}
	
	// ********************************************************************************************
	/**
	* Encrypt the input data with the public key parameter.
	* @param $publicKey The public key for encryption.
	* @param $data the data for encryption.
	* @return the encrypted data.
	*/
	public static function rsaPublicEncrypt($publicKey, $data) {
		if (strpos($key,CCrypto::BEGIN_PUBLIC_KEY) !== false) {
			$publicKey = chunk_split($publicKey);
		} else {
			$publicKey = CCrypto::BEGIN_PUBLIC_KEY."\r\n".chunk_split($publicKey).CCrypto::END_PUBLIC_KEY;
		}
	
		openssl_public_encrypt($data, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
		return $encrypted;
	}
	
	/**
	* Decrypt the input data with the private key parameter.
	* @param $privateKey the private key for decryption.
	* @param $data the crypted data.
	* @return the decrypted data.
	*/
	public static function rsaPrivateDecrypt($key, $data) {
		if (strpos($key,CCrypto::BEGIN_PRIVATE_KEY) !== false) {
			$privateKey = $key;
		} else {
			$privateKey = CCrypto::BEGIN_PRIVATE_KEY."\r\n".chunk_split($key).CCrypto::END_PRIVATE_KEY;
		}
		openssl_pkey_export($privateKey, $kpri);
		$pk = openssl_pkey_get_private($kpri);
		
		openssl_private_decrypt($data, $decrypted, $pk);
		
		return $decrypted;
	}
	
	/**
	* Generates rsa keypair.
	* The keys are the ouput parameters.
	* @param $kpri The private key parameter (out).
	* @param $kpub the public key parameter (out).
	* $keysize the rsa key size (ex: 1024).
	* @return boolean Indicate if keys have been successfully generated.
	*/
	public static function rsaGenerateKeys(&$kpri, &$kpub, $keySize) {
		if($keySize % 8 != 0)
			return false;
		
		$privateKey = openssl_pkey_new(array(
			'private_key_bits' => $keySize,
			'private_key_type' => OPENSSL_KEYTYPE_RSA,
		));
		 
		$keyDetails = openssl_pkey_get_details($privateKey);
		$publicKey = $keyDetails['key'];
		
		openssl_pkey_export($privateKey, $kpri);
		$kpri = str_replace(CCrypto::BEGIN_PRIVATE_KEY, '', $kpri);
		$kpri = str_replace(CCrypto::END_PRIVATE_KEY, '', $kpri);
		$kpri = trim($kpri);
		
		$kpub = $publicKey;
		$kpub = str_replace(CCrypto::BEGIN_PUBLIC_KEY, null, $kpub);
		$kpub = str_replace(CCrypto::END_PUBLIC_KEY, null, $kpub);
		$kpub = trim($kpub);
		
		return true;
	}
}

?>