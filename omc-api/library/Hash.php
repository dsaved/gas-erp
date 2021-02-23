<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Hash
 *
 * @author kenny 
 * @modified by Dsaved
 */
class Hash {

	public static function make($string,$length=null){
		$time = time();
		if ($length!==null) {
		
			$text = hash('SHA256', $time.SALT.$string );
			return strlen($text) > $length ? substr($text, 0, $length) : $text;
		}
		return hash('SHA256', $time.SALT.$string);
	}

	public static function salt($length){
		return md5($length);
	}

	public static function unique(){
		return self::make(uniqid());
	}
	
	public static function encrypt($password,$aes256Key){
		$E = new Encryption(
			$aes256Key, # encryption key
				  Blowfish::BLOWFISH_MODE_CBC, # Encryption Mode
				  Blowfish::BLOWFISH_PADDING_RFC, # Padding Style
				  $aes256Key );
		$data =  $E->myencrypt($password, $aes256Key,$aes256Key);// return encrypted password 
		return Strings::getInstance()->flip($data);// return fliped encrypted password 
	}

	public static function decrypt($password,$salt){
		$Encryption = new Encryption(
			$salt, # encryption key
			Blowfish::BLOWFISH_MODE_CBC, # Encryption Mode
			Blowfish::BLOWFISH_PADDING_RFC, # Padding Style
			$salt);
		$data = Strings::getInstance()->flip($password);
		return $Encryption->mydecrypt($data, $salt,$salt);
	}
}
