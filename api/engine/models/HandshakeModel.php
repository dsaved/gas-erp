<?php
class HandshakeModel extends BaseModel
{
    private static $temp_authentication = "temp_authentication";
    private static $tokens = array("com.example.ovuru_client_clone", "com.ovuru.web.app");

    public function __construct()
    {
        parent::__construct();
    }

    public function initHandshake($data)
    {
        $device_id = $data[0];
        $device_token = $data[1];
        if (in_array($device_token, self::$tokens)) {
            $this->getAuthorization($device_id);
        } else {
            $this->http->_403();
        }
    }

    public function getAuthorization($id)
    {
        $aes256Key = Hash::make(SALT, 12);
        $this->db->query("SELECT * FROM ".self::$temp_authentication." WHERE `device_id` = '$id' LIMIT 1");
        $authData = array("device_id"=>$id,"auth"=>$aes256Key);
        if ($this->db->results()) {
            $this->db->update(self::$temp_authentication, 'device_id', $id, $authData);
        } else {
            $this->db->insert(self::$temp_authentication, $authData);
        }
        $this->http->reply(
            ["temp_authorization_token"=>base64_encode("$id:$aes256Key")]
        )->json();
    }
}