<?php
class OrganizationsModel extends BaseModel
{
    private static $authentication = "authentication";

    function __construct() {
        parent::__construct();
    }
    
    public function index()
    {
        return $this->getOrganization();
    }

    public function get()
    {
        $id = $this->http->json->id;
        return $this->getOrganization("WHERE `id`=$id");
    }

    public function options()
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??40;
        $search = $this->http->json->search??null;
        $condition = "";
        if ($search) {
            $condition = " WHERE `name` LIKE '%$search%' Order by `name` ";
        }
        $this->paging->table('organizations');
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum(1);
        $this->paging->condition("$condition Order By `name`");
        $this->paging->execute();
        $this->paging->reset();
            
        $results = $this->paging->results();
        if (!empty($results)) {
            foreach ($results as $data) {
                $options = array();
                $options['value'] = $data->id;
                $options['label'] = $data->name;
                array_push($response, $options);
            }
        }
        return $response;
    }

    // public function options()
    // {
    //     $response = array();
    //     $response['organizations'] = array();
    //     $this->db->query("SELECT id,name FROM `organizations` ORDER BY `name`");
    //     if ($this->db->results() && $this->db->count > 0) {
    //         foreach ($this->db->results() as $data) {
    //             $accounts = array();
    //             $accounts['value'] = $data->id;
    //             $accounts['label'] = $data->name;
    //             array_push($response['organizations'], $accounts);
    //         }
    //     }
    //     return $response;
    // }

    public function getOrganization($condition=""){
        $response = array();
        $result_per_page = 20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        if($search){
            $condition = "WHERE (`name` LIKE '%$search%' OR `email` LIKE '%$search%' OR `phone` LIKE '%$search%') ";
        }
        $this->paging->table("`organizations`");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->condition("$condition Order By `name`");
        $this->paging->execute();
        $this->paging->reset();
        // var_dump($this->paging);exit;
        $result = $this->paging->results();
        if(!empty($result)){
            $response['success'] = true;
            $response["organizations"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No organization available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function create()
    {
        $name = $this->http->json->name??null;
        if($name===null){
            $this->http->_403(Msg::$provide_name);
        }

        $email = trim($this->http->json->email??null);
        // if(!$email){
        //     $this->http->_403(Msg::$provide_email);
        // }

        $phone = trim($this->http->json->phone??null);
        // if(!$phone){
        //     $this->http->_403(Msg::$provide_phone);
        // }

        //check if organizations with following information already exits in the system
        $this->db->query("SELECT name FROM `organizations` WHERE `name` = '$name'");
        if($this->db->results() && $this->db->count >0){
            $this->http->_403(Msg::company_exist($name));
        }
        // $this->db->query("SELECT * FROM `organizations` WHERE `email` = '$email'");
        // if($this->db->results() && $this->db->count >0){
        //     $this->http->_403(Msg::email_exist($email));
        // }
        
        $data = array(
            'name' =>  $name,
            'email' => $email,
            'phone' => $phone,
        );
        if($this->db->insert("organizations",$data)){
            $response['success'] = true;
            $response['message'] = "Organization has been created";
            return $response;
        }else {
            $this->http->_500("Cannot create organization");
        }
    }
    
    public function update()
    {
        $id = $this->http->json->id;
        $name = $this->http->json->name??null;
        if($name===null){
            $this->http->_403(Msg::$provide_name);
        }

        $email = trim($this->http->json->email??null);
        if(!$email){
            $this->http->_403(Msg::$provide_email);
        }

        $phone = trim($this->http->json->phone??null);
        if(!$phone){
            $this->http->_403(Msg::$provide_phone);
        }

        //check if organizations with following information already exits in the system
        $this->db->query("SELECT id,name FROM `organizations` WHERE `name` = '$name' AND `id`!=$id");
        if($this->db->results() && $this->db->count >0){
            $this->http->_403(Msg::company_exist($name));
        }
        $this->db->query("SELECT email,id FROM `organizations` WHERE `email` = '$email' AND `id`!=$id");
        if($this->db->results() && $this->db->count >0){
            $this->http->_403(Msg::email_exist($email));
        }

        $data = array(
            'name' =>  $name,
            'email' => $email,
            'phone' => $phone,
            );
        if($this->db->updateByID("organizations","id", $id, $data)){
            $response['success'] = true;
            $response['message'] = "Organization has been updated";
            return $response;
        }else {
            $this->http->_500("error updating organization");
        }

    }
    
    public function delete(){
        $response = array();
        $id = implode(',', array_map('intval', $this->http->json->id));
        $done = $this->db->query("DELETE FROM `organizations` WHERE `id` IN ($id)");
        if ($done) {
            $response['success'] = true;
            $response['message'] = "Record deleted successfully";
        } else {
            $response['success'] = false;
            $response['message'] = "Record could not be deleted ";
        }
        return $response;
    }
}
