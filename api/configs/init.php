<?php
if(isset($_SERVER['HTTP_ORIGIN'])){
    //Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
    //you want to allow, and if so
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400'); // cache for 1 day
}

if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
    if(isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        //MAY ALSO BE USING PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    
    
    if(isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    
    exit(0);
}

session_start();
require 'system.conf';
require 'errorhandler.php';

function sitelink(){
    $pageURL = (isset($_SERVER['HTTPS']) ? "https" : "http");
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443") {
        $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"]."/";
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"]."/";
    }
    return $pageURL;
}
// error_log("Error: sitelink()",1,"dsaved8291@gmail.com","From:".sitelink()." <info@".$_SERVER['HTTP_HOST'].">"); // send errors to mail address
setlocale(LC_ALL,null);
define('APP_BASE_URL', str_replace('\\', "/", dirname(__DIR__)).'/');
// define('APP_BASE_URL', $_SERVER['DOCUMENT_ROOT'].'/music/admin/configs/');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/*
*get the server link
*/
function getScriplinks()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $aother =  str_replace(str_replace('\\', "/",$_SERVER['DOCUMENT_ROOT']),'/',APP_BASE_URL);
    return $protocol.$_SERVER['HTTP_HOST'].$aother;
}

$GLOBALS['config'] = array(
          'mysql' => array(
            'host' => DB_HOST.":".DB_PORT,
            'username' => DB_USER,
            'password' => DB_PASS,
            'db' => DB_NAME
        ),
    'remember' => array(
        'cookie_name' => COOKIE_NAME,
        'cookie_expiry' => 86400
    ),
    'session' => array(
        'session_name' => SESSION_NAME,
        'token_name' => 'token'
    )
);

require APP_BASE_URL.'library/swiftmailer/vendor/autoload.php';
require APP_BASE_URL.'library/phpoffice/vendor/autoload.php';


/**
 * Initialize The Helper Class From helper Dir
 * @return null
 */
function autoloadClassesHelper($className) {
    $filename = APP_BASE_URL . 'library/' . $className . ".php";
    if (is_readable($filename)) {
        require $filename;
    }
}
spl_autoload_register("autoloadClassesHelper");

if(isset($_SESSION['time_zone'])){
  $TZ = $_SESSION['time_zone'];
}else{
  $TZ = DEFAULT_TIME_ZONE;
}

define('TIME_ZONE', $TZ);
// date_default_timezone_set(DEFAULT_TIME_ZONE);
date_default_timezone_set(TIME_ZONE);

/*
*@Param $time = time to convert
*@Param $fromTimezone = timezone at which to change from
*@Param $toTimzone = timezone to be changed to
*/
function changeTimezone($time="",$fromTimezone='',$toTimezone='')
{   
  // timezone by php friendly values
  $date = new DateTime($time, new DateTimeZone($fromTimezone));
  $date->setTimezone(new DateTimeZone($toTimezone));
  return $date->format('Y-m-d H:i:s');
}

function params()
{
    return array('tag', 'category','cat','c', 'search', 's','page','p','filter','f','query','q');
}

function checkParam($a)
{
    // Checks if key $a is in array of valid parameters
    return in_array($a, params());
}

function compare($a, $b)
{
    return array_search($a, params()) - array_search($b, params());
}

#get the current url of the viewing page and return with or without the paramiters
#@param $filter boolean true or false, to strip param or leave them
function getCanonicalUrl($filter=false)
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $querystring = '';

    // Copy and flip the array to allow filtering by key.
    $params = array_flip($_GET);

    // Filter out any params that are not wanted if user request it.
    if ($filter===true) {
        $params = array_filter($params, 'checkParam'); 

        // If none remain, we're done.
        if (count($params) !== 0)
        {
            // Sort the rest in given order
            uasort($params, 'compare');
            // Create a query string. Mind, name and value are still flipped.
            $querystring = '?'.http_build_query(array_flip($params));
        }
    }
    
    return 
        $protocol.
        $_SERVER['HTTP_HOST'] .
        $_SERVER['SCRIPT_NAME'] .
        $querystring;
}

#get the current link of the page viewing at any point.
function getCurrentlink()
{
    $pageURL = (isset($_SERVER['HTTPS']) ? "https" : "http");
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

define('BASE_URL', DB::getInstance()->replace('library/configs/','',APP_BASE_URL));
define('SITE_LINK', sitelink().SUB_FOLDER);

require_once APP_BASE_URL.'library/Functions.php';