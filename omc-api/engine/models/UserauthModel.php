<?php
class UserauthModel extends BaseModel
{
    private static $authentication = MAIN_DB_NAME.".authentication";
    private static $user_types = ["system","bdc","omc"];

    function __construct() {
        parent::__construct();
    }
    
    public function index()
    {
        $this->http->_405();
    }

    public function login($user = null,$password=null)
    {
        $user = $this->http->json->user??$user;
        if($user===null){
            $this->http->_403(Msg::$provide_user);
        }
        
        $password= $this->http->json->password??$password;
        if($password===null){
            $this->http->_403(Msg::$provide_password);
        }
        
        // $type = $this->http->json->type??null;
        // if($type===null){
        //     $this->http->_403(Msg::$provide_access_type);
        // }

        $done = $this->user->login($user,$password,null);
        if ($done) {
            $userData = new stdClass();
            $userData = $this->user->data();
            $userData->permissions = ["read"];
            $userData->pages = ["Unauthorized"];
            $authorization = $this->getAuthorization($userData->id);
            $types = array();
            if($userData->access_level=="user"){
                $hasAccount = false;
                $org = $userData->organization;
                $this->db->query("SELECT bank_type FROM `accounts` WHERE `owner`=$org");
                foreach ($this->db->results() as $key => $value) {
                    if(strtolower($value->bank_type)==="bank of ghana"){
                        if(!in_array("bog", $types)){
                            array_push($types, "bog");
                            array_push($userData->pages, "Unauthorized Bank of Ghana");
                        }
                        $hasAccount = true;
                    }else{
                        if(!in_array("org", $types)){
                            array_push($types, "ob");
                            array_push($userData->pages, "Unauthorized Other Banks");
                        }
                        $hasAccount = true;
                    }
                }

                if($hasAccount==false){
                    $this->db->query("SELECT name FROM `organizations` WHERE id={$org}");
                    if($this->db->results() && $this->db->count > 0){
                        $name = $this->db->first()->name;
                        if(strtolower($name) === "bog"){
                            $types = array("bog","organization");
                            array_push($userData->pages, "Unauthorized Bank of Ghana");
                        }else{
                            $this->http->_500("No account associated with this user");
                        }
                    }else{
                        $this->http->_500("No account associated with this user");
                    }
                }
                $userData->baseurl = $userData->pages[0];
                $userData->permissions = ["create","update"];
            } elseif (in_array($userData->user_type,self::$user_types)) {
                $this->db->query("SELECT permissions FROM `users_role` WHERE id={$userData->role} LIMIT 1");
                if($this->db->results() && $this->db->count > 0){
                    $permissions = $this->db->first()->permissions;
                    $userData->permissions = explode(",", $permissions);
                }

                $this->db->query("SELECT pages FROM `users_page` WHERE user_id={$userData->id} LIMIT 1");
                if($this->db->results() && $this->db->count > 0){
                    $pages = $this->db->first()->pages;
                    $userData->pages = explode(",", $pages);
                }
            }

            $this->http->reply(
                [
                    "success" => true,
                    "message" =>  Msg::wecomeuser(),
                    "user" =>  $userData,
                    "types" =>  $types,
                    "authorization_token" =>  $authorization,
                ]
            )->json();
        }else{
            $this->http->_500(Msg::$loginerror);
        }
    }

    public function getAuthorization($id){
        $aes256Key = Hash::make(SALT, 12);
        $this->db->query("SELECT user_id FROM ".self::$authentication." WHERE `user_id` = $id LIMIT 1");
        $authData = array("user_id"=>$id,"auth"=>$aes256Key);
        if($this->db->results()){
            $this->db->updateByID(self::$authentication,'user_id',$id, $authData);
        }else{
            $this->db->insert(self::$authentication, $authData);
        }
        return base64_encode("$id:$aes256Key");
    }
}
