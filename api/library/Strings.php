<?php 

class Strings{

	private static $_instance = null;
	private $_type = '';
  

	public static function getInstance(){
		if(!isset(self::$_instance)){
			self::$_instance = new Strings();
		}
		return self::$_instance;
	}

	/*
		Return string between opening and closing tags
	*/
	public function get_string_between($string, $start, $end){
		$string 			= strstr($string, $start);
		$split_string       = explode($end,$string);
		$split_string		= array_slice($split_string,0,-1);
		foreach($split_string as $data) {
		     $return[]      = trim($this->replace($start,'',$data));
		}
		return $return;
	}

	/* cut sstring short to spefic length
		@param $this_replace -> character to replace
		@param $str_replace -> character to replace with the existing string
		@param $input_text -> string to carry out the operation
	 */
	public function replace($this_replace, $str_replace, $input_text, $count = 1000)
	{
		$string = '~' . $this_replace . '~';
		return preg_replace($string, $str_replace, $input_text, $count);
	}

	/*
		Return true if the value of string is empty
	*/
	public function isEmpty($string){
	    return (!isset($string) || trim($string)==='');
	}

	/*
		Return string before the opening and after the closing tags
	*/
	public function get_string_beforafter($string, $start, $end){
		$first       = explode($start,$string);
		$last        = explode($end,$string);
		$num = count($last)-1;
		$return[0]      = $first[0];
		$return[1]      = $last[$num];
		return $return;
	}


	/*
		change all media elements found in a string to its resperctive type
	*/
	public function str_to_mediatag($value='',$start,$end)
	{
		$value3 = '';
		$str_arr = $this->get_string_between($value,$start, $end);
		$data_keep = $this->get_string_beforafter($value,$start, $end);
		for ($i=0; $i <count($str_arr) ; $i++) {
			$media = explode(' ', $str_arr[$i]);
			$num = count($media)-1;
			$data_tokeep = array_slice($media,0,-1);
			$data_tokeep = implode(' ', $data_tokeep);
			media_id($i);
			$value3.= $data_tokeep.' '.rendermedia2($media[$num]);
		}
		return $data_keep[0].$value3.$data_keep[1];
	}

	/*
		Return media type when string is passed
	*/
	public function getType($value)
	{
		$video_format = Files::getInstance()->video_format;
		$audio_format = Files::getInstance()->audio_format;
		$image_format = Files::getInstance()->image_format;

		$array = Arrays::getInstance();
		$fliped_array = array();


		if (is_array($value)) {
			$data = explode('.', strtolower($value[0]));
			$fliped_array = $array->flip_array($data);
			$media_type = $fliped_array[0];

			if (in_array($media_type, $video_format)) {
				$this->_type = 'video';
			}else if (in_array($media_type, $audio_format)) {
				$this->_type = 'audio';
			}else if (in_array($media_type, $image_format)) {
				$this->_type = 'image';
			}else if(filter_var(strtolower($value[0]), FILTER_VALIDATE_URL)){

				$parsed = parse_url(strtolower($value[0]));
				if (!empty($parsed['scheme']) && strpos(strtolower($value[0]), 'embed') !== false) {
					$this->_type = 'youtube';
				}else if (!empty($parsed['scheme']) && strpos(strtolower($value[0]), 'watch') !== false){
					$this->_type = 'youtube1';
				}else if (!empty($parsed['scheme']) && strpos(strtolower($value[0]), 'v/') !== false){
					$this->_type = 'youtube1';
				}else {
					$this->_type = 'Unknown type';
				}
				
			}else {
				$this->_type = 'Unknown type';
			}

		}else{
			$data = explode('.', strtolower($value));
			$fliped_array = $array->flip_array($data);
			$media_type = $fliped_array[0];

			if (in_array($media_type, $video_format)) {
				$this->_type = 'video';
			}else if (in_array($media_type, $audio_format)) {
				$this->_type = 'audio';
			}else if (in_array($media_type, $image_format)) {
				$this->_type = 'image';
			}else if(filter_var(strtolower($value), FILTER_VALIDATE_URL)){

				$parsed = parse_url(strtolower($value));
				if (!empty($parsed['scheme']) && strpos(strtolower($value), 'embed') !== false) {
					$this->_type = 'youtube';
				}else if (!empty($parsed['scheme']) && strpos(strtolower($value), 'watch') !== false){
					$this->_type = 'youtube1';
				}else if (!empty($parsed['scheme']) && strpos(strtolower($value), 'v/') !== false){
					$this->_type = 'youtube2';
				}else {
					$this->_type = 'Unknown type';
				}
				
			}else {
				$this->_type = 'Unknown type';
			}
		}

		return $this->_type;
	}

	public function genrand($length = 10) 
	{
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	public function flip($value){
		$num = strlen($value);
		$num = $num - 1;
		$newstring = "";

		$j = 0;
		for ($i=$num; $i >=0 ; $i--) { 
			$newstring .= $value[$i];
			$j++;
		}
		return $newstring;
	}
}

?>