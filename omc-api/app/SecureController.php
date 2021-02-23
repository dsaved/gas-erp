<?php
defined('APP') or exit('No direct script access allowed');

/**
* Controller Which Needs Authentication And Access Control Must Inherit From This Class
*/
class SecureController extends BaseController
{
    private static $authentication = MAIN_DB_NAME.".authentication";
    private static $temp_authentication = "temp_authentication";

	function __construct($group=null){
		parent::__construct();
        $this->require_auth();
    }
    
    private function require_auth() {
        $token = null;
        $headers = apache_request_headers();
        if(isset($headers['Authorization'])){
            $token = substr($headers['Authorization'], 7);
        }elseif(isset($headers['authorization'])){
            $token = substr($headers['authorization'], 7);
        }

        header('Cache-Control: no-cache, must-revalidate, max-age=0');
        $has_supplied_credentials = !empty($token);
        if (!$has_supplied_credentials) {
            $this->http->_401();
        }elseif(!self::validate($token)) {
            $this->http->_401("401 token invalid");
        }
    }

    private function validate($authorization_token){
        $token = base64_decode($authorization_token);
        $access_credentials = explode(":", $token);
        if(is_array($access_credentials) && count($access_credentials)>1){
            $user_id = trim($access_credentials[0]);
            $auth = trim($access_credentials[1]);
            $this->db->query("SELECT * FROM ".self::$authentication." WHERE `user_id` = $user_id AND `auth`='$auth' LIMIT 1");
            if($this->db->results() && $this->db->count > 0){
                $data = $this->db->first();
                $authData = array("requests"=>(int)$data->requests+1);
                $this->db->updateByID(self::$authentication,'user_id',$user_id, $authData);
                return true;
            }else{
                $this->db->query("SELECT * FROM ".self::$temp_authentication." WHERE `device_id` = '$user_id' AND `auth`='$auth' LIMIT 1");
                if($this->db->results() && $this->db->count > 0){
                    $data = $this->db->first();
                    $authData = array("requests"=>(int)$data->requests+1);
                    $this->db->update(self::$temp_authentication,'device_id',$user_id, $authData);
                    return true;
                }
            }
        }
        return false;
    }
}