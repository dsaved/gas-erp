<?php
class AccountcategoryModel extends BaseModel
{

    function __construct() {
        parent::__construct();
    }
    
    public function index()
    {
        return $this->getBanks();
    }

    public function get()
    {
        $id = $this->http->json->id;
        return $this->getBanks("WHERE `id`=$id");
    }

    public function getBanks($condition=""){
        $response = array();
        $result_per_page = 20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        if($search){
            $condition = "WHERE `name` LIKE '%$search%' ";
        }
        $this->paging->table("`account_category`");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->condition("$condition Order By `name`");
        $this->paging->execute();
        $this->paging->reset();
        // var_dump($this->paging);exit;
        $result = $this->paging->results();
        if(!empty($result)){
            $response['success'] = true;
            $response["account_category"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No categories available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function create()
    {
        $name = $this->http->json->name??null;
        if($name===null){
            $this->http->_403(Msg::$provide_bank_name);
        }

        $this->db->query("SELECT name FROM `account_category` WHERE `name` = '$name'");
        if($this->db->results() && $this->db->count >0){
            $this->http->_403(Msg::bank_exist($name));
        }
        
        $data = array( 'name' =>  $name );
        if($this->db->insert("account_category",$data)){
            $response['success'] = true;
            $response['message'] = "Category has been created";
            return $response;
        }else {
            $this->http->_500(Msg::$bank_create_error);
        }
    }
    
    public function update()
    {
        $id = $this->http->json->id;
        $name = $this->http->json->name??null;
        if($name===null){
            $this->http->_403(Msg::$provide_bank_name);
        }

        $this->db->query("SELECT name,id FROM `account_category` WHERE `name` = '$name' AND `id`!=$id");
        if($this->db->results() && $this->db->count >0){
            $this->http->_403(Msg::bank_exist($name));
        }
        
        $data = array( 'name' =>  $name );
        if($this->db->updateByID("account_category","id", $id, $data)){
            $response['success'] = true;
            $response['message'] = "Category has been updated";
            return $response;
        }else {
            $this->http->_500(Msg::$bank_create_error);
        }
    }
    
    public function delete(){
        $response = array();
        $id = implode(',', array_map('intval', $this->http->json->id));
        $done = $this->db->query("DELETE FROM `account_category` WHERE `id` IN ($id)");
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
