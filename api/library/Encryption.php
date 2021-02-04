<?php 

class Encryption extends Blowfish
{

	private static $_instance = null;  
	private static $MODE = "AES-128-ECB";  

	public static function getInstance(){
		if(!isset(self::$_instance)){
			self::$_instance = new Encryption();
		}
		return self::$_instance;
	}

	public function myencrypt($sValue, $sSecretKey,$_iv)
	{
		$data = $this->encrypt($sValue,22);
		$_data =  $this->_encrypt($data,$sSecretKey,$_iv);//blow fish
		return $this->fnEncrypt($_data, $sSecretKey,$_iv);//openssl
	}

	public function mydecrypt($sValue, $sSecretKey,$_iv) {
		$data = $this->openssl_decrypt($sValue,$sSecretKey);//openssl
		$_data =  $this->_decrypt($data, $sSecretKey,$_iv);//blow fish
		return $this->decrypt($_data, 22);
	}

	public function fnEncrypt($sValue, $sSecretKey,$_iv) {
	    return $this->openssl_encrypt($sValue, $sSecretKey, $_iv);
	}

	public function openssl_encrypt($data,$key){
		return openssl_encrypt($data,self::$MODE,$key);
	}

	public function openssl_decrypt($ciphertext,$key){
		return openssl_decrypt($ciphertext,self::$MODE,$key);
	}

	public function _encrypt($sValue, $sSecretKey,$_iv) {
		return $this->bfencrypt(
					          $sValue,
					          $sSecretKey, # encryption key
					          Blowfish::BLOWFISH_MODE_CBC, # Encryption Mode
					          Blowfish::BLOWFISH_PADDING_RFC, # Padding Style
					          $_iv  # Initialisation Vector - required for CBC
		);
	}

	public function _decrypt($sValue, $sSecretKey,$_iv) {
		return $this->bfdecrypt(
		                  $sValue,
		                  $sSecretKey,# decryption key
		                  Blowfish::BLOWFISH_MODE_CBC, # Encryption Mode
		                  Blowfish::BLOWFISH_PADDING_RFC, # Padding Style
		                  $_iv  # Initialisation Vector - required for CBC
		);
	}


}
?>