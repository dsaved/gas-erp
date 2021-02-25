<?php
class TaxexemptionModel extends BaseModel
{
    private static $table = "tax_exemptions";

    public function __construct()
    {
        parent::__construct();
    }
    
    public function index()
    {
        return $this->exemptions();
    }
    
    public function get()
    {
        $id = $this->http->json->id??null;
        if (empty($id)) {
            $this->http->_403("Tax exemption id is required");
        }
        return $this->exemption(" WHERE `id`= $id");
    }
    
    public function exemption($condition="")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        $omcfilter = $this->http->json->omcfilter??null;
        if ($search) {
            if (empty($condition)) {
                $condition .= " WHERE  (`omc` IN (SELECT id FROM `omc` WHERE `name` LIKE '%$search%') OR `tax_product` IN (SELECT id FROM `tax_schedule_products` WHERE `name` LIKE '%$search%') ) ";
            }else{
                $condition .= " AND  (`omc` IN (SELECT id FROM `omc` WHERE `name` LIKE '%$search%') OR `tax_product` IN (SELECT id FROM `tax_schedule_products` WHERE `name` LIKE '%$search%') ) ";
            }
        }

        if ($omcfilter) {
            if (empty($condition)) {
                $condition .= " WHERE  `omc` = $omcfilter ";
            }else{
                $condition .= " AND  `omc` = $omcfilter ";
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
                $this->db->query("SELECT id,name FROM `omc` WHERE `id`={$value->omc} LIMIT 1");
                $omcfilter = $this->db->results();
                if ($omcfilter && $this->db->count > 0) {
                    $tax_type = $this->db->first();
                    $options = array();
                    $options['value'] = $tax_type->id;
                    $options['label'] = $tax_type->name;
                    $value->omc = $options;
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
            $response["exemptions"] = $results;
        } else {
            $response['success'] = false;
            $response['message'] = "No exemptions available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function exemptions($condition="")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        $omcfilter = $this->http->json->omcfilter??null;
        if ($search) {
            if (empty($condition)) {
                $condition .= " WHERE  (`omc` IN (SELECT id FROM `omc` WHERE `name` LIKE '%$search%') OR `tax_product` IN (SELECT id FROM `tax_schedule_products` WHERE `name` LIKE '%$search%') ) ";
            }else{
                $condition .= " AND  (`omc` IN (SELECT id FROM `omc` WHERE `name` LIKE '%$search%') OR `tax_product` IN (SELECT id FROM `tax_schedule_products` WHERE `name` LIKE '%$search%') ) ";
            }
        }

        if ($omcfilter) {
            if (empty($condition)) {
                $condition .= " WHERE  `omc` = $omcfilter ";
            }else{
                $condition .= " AND  `omc` = $omcfilter ";
            }
        }

        $this->paging->rawQuery("SELECT *, (SELECT name FROM `omc` WHERE `id`=".self::$table.".omc LIMIT 1) as omc, (SELECT name FROM `tax_schedule_products` WHERE `id`=".self::$table.".tax_product LIMIT 1) as tax_product FROM ".self::$table." $condition");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();
        
        $results = $this->paging->results();
        if (!empty($results)) {
            $response['success'] = true;
            $response["exemptions"] = $results;
        } else {
            $response['success'] = false;
            $response['message'] = "No exemptions available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function addtax()
    {
        $omc = $this->http->json->omc??null;
        if ($omc===null) {
            $this->http->_403("Please provide omc");
        }

        $date_from = $this->http->json->date_from??null;
        if ($date_from===null) {
            $this->http->_403("Please provide tax exemption date from");
        }

        $date_to = $this->http->json->date_to??null;
        if ($date_to===null) {
            $this->http->_403("Please provide tax exemption date to");
        }

        $tax_product = $this->http->json->tax_product??null;
        if ($tax_product===null) {
            $this->http->_403("Please provide tax products");
        }

        $litters = $this->http->json->litters??null;
        if ($litters===null) {
            $this->http->_403("Please provide tax exemption litters");
        }

        $data = array(
            "omc" =>  $omc,
            "date_from" =>  $this->date->sql_date($date_from),
            "date_to" =>  $this->date->sql_date($date_to),
            "tax_product"=>$tax_product,
            "litters"=>$litters,
        );
        if ($this->db->insert(self::$table, $data)) {
            $response['success'] = true;
            $response['message'] = "Tax exemption has been created";
            return $response;
        } else {
            $this->http->_500("Error while creating tax exemption");
        }
    }
    
    public function updatetax()
    {
        $id = $this->http->json->id??null;
        if ($id===null) {
            $this->http->_403("Please provide tax products id");
        }

        $omc = $this->http->json->omc??null;
        if ($omc===null) {
            $this->http->_403("Please provide omc");
        }

        $date_from = $this->http->json->date_from??null;
        if ($date_from===null) {
            $this->http->_403("Please provide tax exemption date from");
        }

        $date_to = $this->http->json->date_to??null;
        if ($date_to===null) {
            $this->http->_403("Please provide tax exemption date to");
        }

        $tax_product = $this->http->json->tax_product??null;
        if ($tax_product===null) {
            $this->http->_403("Please provide tax products");
        }

        $litters = $this->http->json->litters??null;
        if ($litters===null) {
            $this->http->_403("Please provide tax exemption litters");
        }

        $data = array(
            "omc" =>  $omc,
            "date_from" => $this->date->sql_date($date_from),
            "date_to" =>  $this->date->sql_date($date_to),
            "tax_product"=>$tax_product,
            "litters"=>$litters,
        );
        if ($this->db->updateByID(self::$table, "id", $id, $data)) {
            $response['success'] = true;
            $response['message'] = "Tax exemption has been updated";
            return $response;
        } else {
            $this->http->_500("Error while updating tax exemption");
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
