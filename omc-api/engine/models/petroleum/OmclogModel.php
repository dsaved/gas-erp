<?php
class OmclogModel extends BaseModel
{
    private static $table = "audits_logs_omc";

    public function __construct()
    {
        parent::__construct();
    }
    
    public function index()
    {
        return $this->omcs();
    }

    public function omcs($condition="")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        if ($search) {
            if (empty($condition)) {
                $condition = " WHERE (`omc` LIKE '%$search%') ";
            } else {
                $condition .= " AND (`omc` LIKE '%$search%') ";
            }
        }
        $this->paging->rawQuery("SELECT SUM(alo.amount) as amount, COUNT(alo.omc_id) as total, alo.omc_id, alo.omc FROM `audits_logs_omc` as alo $condition GROUP BY alo.omc ORDER BY alo.omc ");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();
        
        $result = $this->paging->results();
        if (!empty($result)) {
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $value->amount = number_format($value->amount, 2);
            }
            $response["auditlogs"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No logs available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function transactions()
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $id = $this->http->json->account_id??null;
        $condition = "WHERE `omc_id`= $id";
        $search = $this->http->json->search??null;
        if ($search) {
            $condition .= " AND (`bank` LIKE '%$search%' OR `amount` = '$search' OR `credit_amount` = '$search' OR `omc` LIKE '%$search%' OR `account` LIKE '%$search%' OR `account_number` LIKE '%$search%' OR `description` LIKE '%$search%' OR `creadit_date` = '{$this->date->sql_date($search)}' OR `date` = '{$this->date->sql_date($search)}' ) ";
        }
        $this->paging->table("audits_logs_omc");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->condition("$condition Order By `date`");
        $this->paging->execute();
        $this->paging->reset();

        $result = $this->paging->results();
        if (!empty($result)) {
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $value->amount = number_format($value->amount, 2);
                $value->credit_amount = number_format($value->credit_amount, 2);
            }
            $response["auditlogs"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No logs available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
}
