<?php
define("AES_256_CBC","aes-256-cbc");
/**
 * Public Functions Specific for this Framework
 * @category  General
 */
function encrypt($data){
    $encryption_key = openssl_random_pseudo_bytes(32);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(AES_256_CBC));
    $encrypted = openssl_encrypt($data, AES_256_CBC, $encryption_key, 0, $iv);
    //encrypted = data::key::iv
    return $encrypted."::".base64_encode($encryption_key)."::".base64_encode($iv);
}

function decrypt($data){
    if(strpos($data,"::")===false)return $data;
    $data = explode("::", $data);
    $encryption_key =  base64_decode($data[1]);
    $iv = base64_decode($data[2]);
    $encrypted = $data[0];
    $decrypted = openssl_decrypt($encrypted, AES_256_CBC, $encryption_key, 0, $iv);
    return $decrypted;
}

/**
 * Force Download of The File
 * @return boolean
 */
function download_file($file_path,$savename=null,$showsavedialog=false){
	if(!empty($file_path)){
		
		if($showsavedialog==false){
			header('Content-Type: application/octet-stream');
		}
		
		if(empty($savename)){
			$savename=basename($file_path);
		}
		
		header('Content-Transfer-Encoding: binary'); 
		header('Content-disposition: attachment; filename="'.$savename.'"'); 
		header('Content-Description: File Transfer');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		
		ob_clean();
		flush();
		readfile($file_path);
		
		return true;
	}
	return false;
}
	
/**
 * Retrieve Content of From external Url
 * @example echo httpGet("http://dsaved.com/system/phpcurget/");
 * @return string
 */
function http_get($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch,CURLOPT_HEADER, false); 
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}

function http_getp($url, $params = array())
{
	$postData = '';
		//create name value pairs seperated by &
	foreach ($params as $k => $v) {
		$postData .= $k . '=' . $v . '&';
	}
	$postData = rtrim($postData, '&');
	$ch = curl_init($url."?".$postData);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}

/**
 * Retrieve Content of From external Url Using POST Request
 * @example echo http_post("http://dsaved.com/system/phpcurlpost/");
 * @return string
 */
function http_post($url, $params = array())
{
	$postData = '';
		//create name value pairs seperated by &
	foreach ($params as $k => $v) {
		$postData .= $k . '=' . $v . '&';
	}
	$postData = rtrim($postData, '&');
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
	$output = curl_exec($ch);
	
	curl_close($ch);
	return $output;
}


/**
 * Retrieve Content of From external Url Using POST Request
 * @example echo http_post("http://dsaved.com/system/phpcurlpost/");
 * @return string
 */
function http_post_json($url, $params = array())
{
	$data_string = json_encode($params);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($data_string))
	);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

/**
 * Generate a Random String and characters From Set Of supplied data context
 * @return  string
 */
function random_chars($limit = 12, $context = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890!@#$%^&*_+-=')
{
	$l = ($limit <= strlen($context) ? $limit : strlen($context));
	return substr(str_shuffle($context), 0, $l);
}

/**
 * Generate a Random Token Hex with given length
 * @return  hexza-decimal
 */
function generateToken($length = 20)
{
	return random_str($length);
}

/**
 * Generate a Random String From Set Of supplied data context
 * @return  string
 */
function random_str($limit = 12, $context = 'abcdefghijklmnopqrstuvwxyz1234567890')
{
	$l = ($limit <= strlen($context) ? $limit : strlen($context));
	return substr(str_shuffle($context), 0, $l);
}

/**
 * Generate a Random String From Set Of supplied data context
 * @return  string
 */
function random_num($limit = 10, $context = '1234567890')
{
	$l = ($limit <= strlen($context) ? $limit : strlen($context));
	return substr(str_shuffle($context), 0, $l);
}

/**
 * Generate a Random color String 
 * @return  string
 */
function random_color($alpha = 1)
{
	$red = rand(0, 255);
	$green = rand(0, 255);
	$blue = rand(0, 255);
	return "rgba($red,$blue,$green,$alpha)";
}

/**
 * Will Return A clean Html entities free from xss attacks
 * @return  string
 */
function html_xss_clean($text)
{
	return htmlspecialchars($text);
}

/**
 * Will Return A clean String free from Sql Injection
 * @return  string
 */
function escape($string)
{
	return htmlentities($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Will Return A clean String free from bad url format
 * @return  string
 */
function clean($string)
{
	$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
	$string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
	return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}

/**
 * convert all value in array to string
 * @return  array
 */
function stringify($value)
{
	return '"' . $value . '"';
}

/**
 * Check if current browser platform is a mobile browser
 * Can Be Used to Switch Layouts and Views on A Mobile Platform
 * @return  object
 */
function is_mobile()
{
	return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

/**
 * Converting php array to php object
 * @param $array Data to convert to object
 * @return object array
 */
function array_to_object($array)
{
	return (object)$array;
}

/**
 * Converting php object to php array
 * @param $object Data to convert to array
 * @return array data
 */
function object_to_array($object)
{
	return (array)$object;
}

/**
 * Dispatch Content in JSON Formart
 * @param $data Data to be Output
 * @return JSON String
 */
function render_json($data){
	header("Content-Type: Application/json");
	render(json_encode($data));
	exit;
}

/*
*show data
*/
function render($value=""){
	if (!is_array($value)) {
		echo $value;
	}
}

function render_unnull($value){
	render((null!==$value && $value!=="")?$value:"");
}

/**
 * check if item in array is contained in a string
 * @return  boolean
 */
function stringpresent($needle,$haystack) {
	$bool = false;
	if(is_array($needle)){
		foreach ($needle as $value) {
			if (strpos(strtolower($haystack),strtolower($value))!==false) {
				$bool = true;
				break;
			}
		}
	}else{
		if (strpos(strtolower($haystack),strtolower($needle))!==false) {
			$bool = true;
		}
	}
	return $bool;
}

function logData($log_type="DEFAULT", $logInfo= null){
	savelog($log_type, json_decode(json_encode($logInfo)));
}

//write the file to directory
function savelog($log_type, $content){
	(new Files)->moveLogs();
	//check what type of error to log
	switch (strtolower($log_type)) {
		case "default":
			$filename = "default_log-";
			break;
		case "error":
			$filename = "error_log-";
			break;
		default:
			$filename = "error_log-";
			break;
	}
	//get the filename to write to
	$name =  (new DateTime)->format('Ymd');
	//file path and name
	if(DEVELOPER_MODE){
		if($filename ==="error_log-"){
			(new Files)->writeOrCreateFile(LOG_ADDR,"{$log_type}-".$name.".log", $content);
		}else{
			(new Files)->create_file(LOG_ADDR, "{$log_type}-{$name}.log");
			(new HttpLog)->execute(LOG_ADDR."{$log_type}-{$name}.log"); //enable more detailed log
		}
	}
}

function deleteOldFiles() {
    $db = (new DB)->getInstance();
    $db->query("SELECT * FROM file_download WHERE created < CURDATE()");
	foreach ($db->results() as $key => $value) {
        $link = str_replace("../omc-api/", "./", $value->link);
        $link = str_replace("../api/", "./", $link);
        $db->query("DELETE FROM file_download WHERE id={$value->id}");
		if(file_exists($link)){
			@unlink($link);
		}
	}
}