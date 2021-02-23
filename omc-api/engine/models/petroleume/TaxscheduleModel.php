<?php
class TaxscheduleModel extends BaseModel
{
    private static $table = "tax_schedule";

    public function __construct()
    {
        parent::__construct();
    }
    
    public function index()
    {
        return $this->schedules();
    }
    
    public function get()
    {
        $id = $this->http->json->id??null;
        if (empty($id)) {
            $this->http->_403("Tax schedule id is required");
        }
        return $this->schedule(" WHERE `id`= $id");
    }
    
    public function schedule($condition="")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        $taxtype = $this->http->json->taxtype??null;
        if ($search) {
            if (empty($condition)) {
                $condition .= " WHERE  (`tax_type` IN (SELECT id FROM `tax_type` WHERE `name` LIKE '%$search%') OR `tax_product` IN (SELECT id FROM `tax_schedule_products` WHERE `name` LIKE '%$search%') ) ";
            }else{
                $condition .= " AND  (`tax_type` IN (SELECT id FROM `tax_type` WHERE `name` LIKE '%$search%') OR `tax_product` IN (SELECT id FROM `tax_schedule_products` WHERE `name` LIKE '%$search%') ) ";
            }
        }

        if ($taxtype) {
            if (empty($condition)) {
                $condition .= " WHERE  `tax_type` = $taxtype ";
            }else{
                $condition .= " AND  `tax_type` = $taxtype ";
            }
        }

        $this->paging->table(self::$table);
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->condition("$condition");
        $this->paging->execute();
        $this->paging->reset();
        
        $results = $this->paging->results();
        if (!empty($results)) {
            $response['success'] = true;
            foreach ($results as $key => &$value) {
                $this->db->query("SELECT id,name FROM `tax_type` WHERE `id`={$value->tax_type} LIMIT 1");
                $taxtype = $this->db->results();
                if ($taxtype && $this->db->count > 0) {
                    $tax_type = $this->db->first();
                    $options = array();
                    $options['value'] = $tax_type->id;
                    $options['label'] = $tax_type->name;
                    $value->tax_type = $options;
                }

                $this->db->query("SELECT id,name FROM `tax_schedule_products` WHERE `id`={$value->tax_product} LIMIT 1");
                $tax_schedule_products = $this->db->results();
                if ($tax_schedule_products && $this->db->count > 0) {
                    $tax_product = $this->db->first();
                    $options = array();
                    $options['value'] = $tax_product->id;
                    $options['label'] = $tax_product->name;
                    $value->tax_product = $options;
                }
            }
            $response["schedules"] = $results;
        } else {
            $response['success'] = false;
            $response['message'] = "No tax available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function schedules($condition="")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        $taxtype = $this->http->json->taxtype??null;
        if ($search) {
            if (empty($condition)) {
                $condition .= " WHERE  (`tax_type` IN (SELECT id FROM `tax_type` WHERE `name` LIKE '%$search%') OR `tax_product` IN (SELECT id FROM `tax_schedule_products` WHERE `name` LIKE '%$search%') ) ";
            }else{
                $condition .= " AND  (`tax_type` IN (SELECT id FROM `tax_type` WHERE `name` LIKE '%$search%') OR `tax_product` IN (SELECT id FROM `tax_schedule_products` WHERE `name` LIKE '%$search%') ) ";
            }
        }

        if ($taxtype) {
            if (empty($condition)) {
                $condition .= " WHERE  `tax_type` = $taxtype ";
            }else{
                $condition .= " AND  `tax_type` = $taxtype ";
            }
        }

        $this->paging->rawQuery("SELECT *, (SELECT name FROM `tax_type` WHERE `id`=".self::$table.".tax_type LIMIT 1) as tax_type, (SELECT name FROM `tax_schedule_products` WHERE `id`=".self::$table.".tax_product LIMIT 1) as tax_product FROM ".self::$table." $condition");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();

        // $this->http->reply($this->paging->lastQuery())->dump();
        
        $results = $this->paging->results();
        if (!empty($results)) {
            $response['success'] = true;
            $response["schedules"] = $results;
        } else {
            $response['success'] = false;
            $response['message'] = "No tax available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function addtax()
    {
        $tax_type = $this->http->json->tax_type??null;
        if ($tax_type===null) {
            $this->http->_403("Please provide tax tax");
        }

        $tax_product = $this->http->json->tax_product??null;
        if ($tax_product===null) {
            $this->http->_403("Please provide tax products");
        }

        $rate = $this->http->json->rate??null;
        if ($rate===null) {
            $this->http->_403("Please provide tax schadule rate");
        }

        $date_from = $this->http->json->date_from??null;
        if ($date_from===null) {
            $this->http->_403("Please provide tax schadule date from");
        }

        $date_to = $this->http->json->date_to??null;
        if ($date_to===null) {
            $this->http->_403("Please provide tax schadule date to");
        }

        $data = array(
            "tax_type" =>  $tax_type,
            "date_from" =>  $this->date->sql_datetime($date_from),
            "date_to" =>  $this->date->sql_datetime($date_to),
            "tax_product"=>$tax_product,
            "rate"=>$rate,
        );
        if ($this->db->insert(self::$table, $data)) {
            $response['success'] = true;
            $response['message'] = "Tax Schedule has been created";
            return $response;
        } else {
            $this->http->_500("Error while creating tax schedule");
        }
    }
    
    public function updatetax()
    {
        $id = $this->http->json->id??null;
        if ($id===null) {
            $this->http->_403("Please provide tax products id");
        }
        $tax_type = $this->http->json->tax_type??null;
        if ($tax_type===null) {
            $this->http->_403("Please provide tax tax");
        }

        $tax_product = $this->http->json->tax_product??null;
        if ($tax_product===null) {
            $this->http->_403("Please provide tax products");
        }

        $rate = $this->http->json->rate??null;
        if ($rate===null) {
            $this->http->_403("Please provide tax schadule rate");
        }

        $date_from = $this->http->json->date_from??null;
        if ($date_from===null) {
            $this->http->_403("Please provide tax schadule date from");
        }

        $date_to = $this->http->json->date_to??null;
        if ($date_to===null) {
            $this->http->_403("Please provide tax schadule date to");
        }


        $data = array(
            "tax_type" =>  $tax_type,
            "date_from" =>  $this->date->sql_datetime($date_from),
            "date_to" =>  $this->date->sql_datetime($date_to),
            "tax_product"=>$tax_product,
            "rate"=>$rate,
        );
        if ($this->db->updateByID(self::$table, "id", $id, $data)) {
            $response['success'] = true;
            $response['message'] = "Tax Schedule has been updated";
            return $response;
        } else {
            $this->http->_500("Error while updating tax schedule");
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
