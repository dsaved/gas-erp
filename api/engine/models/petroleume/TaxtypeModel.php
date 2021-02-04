<?php
class TaxtypeModel extends BaseModel
{
    private static $table = "tax_type";

    public function __construct()
    {
        parent::__construct();
    }
    
    public function index()
    {
        return $this->taxtypes();
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
                $options = array();
                $options['value'] = $data->id;
                $options['label'] = $data->name;
                array_push($response, $options);
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
        return $this->taxtypes(" WHERE `id`= $id");
    }
    
    public function taxtypes($condition="")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        if ($search) {
            if (empty($condition)) {
                $condition .= " WHERE (`name` LIKE '%$search%') ";
            }else{
                $condition .= " AND (`name` LIKE '%$search%') ";
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
            $response["taxes"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No tax available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function addtax()
    {
        $name = $this->http->json->name??null;
        if ($name===null) {
            $this->http->_403("Please provide tax type name");
        }

        $data = array('name' =>  $name);
        if ($this->db->insert(self::$table, $data)) {
            $response['success'] = true;
            $response['message'] = "Tax Type has been created";
            return $response;
        } else {
            $this->http->_500("Error while creating tax type");
        }
    }
    
    public function updatetax()
    {
        $id = $this->http->json->id??null;
        if ($id===null) {
            $this->http->_403("Please provide tax type id");
        }

        $name = $this->http->json->name??null;
        if ($name===null) {
            $this->http->_403("Please provide tax name");
        }

        $data = array('name' =>  $name);
        if ($this->db->updateByID(self::$table, "id", $id,$data)) {
            $response['success'] = true;
            $response['message'] = "Tax Type has been updated";
            return $response;
        } else {
            $this->http->_500("Error while updating tax type");
        }
    }
    
    public function deletetax()
    {
        $response = array();
        $id = implode(',', array_map('intval', $this->http->json->id));
        $done = $this->db->query("DELETE FROM ".self::$table." WHERE `id` IN ($id)");
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
