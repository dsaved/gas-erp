<?php
class DepotModel extends BaseModel
{
    private static $table = "depot";

    public function __construct()
    {
        parent::__construct();
    }
    
    public function index()
    {
        return $this->omcs();
    }
    
    public function options()
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??40;
        $search = $this->http->json->search??null;
        $condition = "";
        if ($search) {
            $condition = " WHERE (`name` LIKE '%$search%') ";
        }
        $this->paging->table(self::$table);
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum(1);
        $this->paging->condition("$condition Order By `name`");
        $this->paging->execute();
        $this->paging->reset();

        $results = $this->paging->results();
        if (!empty($results)) {
            foreach ($results as $data) {
                array_push($response, $data->name);
            }
        }
        return $response;
    }
    
    public function get()
    {
        $id = $this->http->json->id??null;
        if (empty($id)) {
            $this->http->_403("Tax type id is required");
        }
        return $this->omcs(" WHERE `id`= $id");
    }
    
    public function omcs($condition="")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        if ($search) {
            if (empty($condition)) {
                $condition .= " WHERE (`name` LIKE '%$search%' OR `email` LIKE '%$search%'OR `email` LIKE '%$search%') ";
            } else {
                $condition .= " AND (`name` LIKE '%$search%' OR `email` LIKE '%$search%'OR `email` LIKE '%$search%') ";
            }
        }
        $this->paging->table(self::$table);
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->condition("$condition Order By `name`");
        $this->paging->execute();
        $this->paging->reset();
        
        $result = $this->paging->results();
        if (!empty($result)) {
            $response['success'] = true;
            $response["omcs"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No Depot available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function addtax()
    {
        $email = $this->http->json->email??null;
        $phone = $this->http->json->phone??null;

        $name = $this->http->json->name??null;
        if ($name===null) {
            $this->http->_403("Please provide Depot name");
        }

        $this->db->query("SELECT * FROM ".self::$table." WHERE `name` = '$name'");
        if ($this->db->results() && $this->db->count >0) {
            $this->http->_403("This Depot name ($name) already exist");
        }

        $data = array(
            'name' => $name,
            "email"=>$email,
            "phone"=>$phone
        );
        if ($this->db->insert(self::$table, $data)) {
            $response['success'] = true;
            $response['message'] = "Depot has been created";
            return $response;
        } else {
            $this->http->_500("Error while creating Depot");
        }
    }
    
    public function updatetax()
    {
        $id = $this->http->json->id??null;
        if ($id===null) {
            $this->http->_403("Please provide Depot id");
        }
        $email = $this->http->json->email??null;
        $phone = $this->http->json->phone??null;

        $name = $this->http->json->name??null;
        if ($name===null) {
            $this->http->_403("Please provide Depot name");
        }

        $this->db->query("SELECT * FROM ".self::$table." WHERE `name` = '$name' AND `id`!=$id");
        if ($this->db->results() && $this->db->count >0) {
            $this->http->_403("This Depot name ($name) already exist");
        }

        $data = array(
            'name' => $name,
            "email"=>$email,
            "phone"=>$phone
        );
        if ($this->db->updateByID(self::$table, "id", $id, $data)) {
            $response['success'] = true;
            $response['message'] = "Depot has been updated";
            return $response;
        } else {
            $this->http->_500("Error while updating Depot");
        }
    }
    
    public function deletetax()
    {
        $response = array();
        $id = implode(',', array_map('intval', $this->http->json->id));
        $done = $this->db->query("DELETE FROM ".self::$table." WHERE `id` IN ($id)");
        if ($done) {
            $this->db->query("DELETE FROM `omc_receipt` WHERE `omc_id` = {$id}");
            $response['success'] = true;
            $response['message'] = "Record deleted successfully";
        } else {
            $response['success'] = false;
            $response['message'] = "Record could not be deleted ";
        }
        return $response;
    }
}
