<?php
class NationalsummaryModel extends BaseModel
{
    private static $table = "omc_receipt";

    public function __construct()
    {
        parent::__construct();
    }
    
    public function reconciled()
    {
        return $this->banks(" WHERE omc_r.`status`= 'done'");
    }
    
    public function unreconciled()
    {
        return $this->banks(" WHERE omc_r.`status`= 'flagged'");
    }
    
    public function banks($condition="")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        if ($search) {
            if (empty($condition)) {
                $condition = " WHERE (b.`name` LIKE '%$search%' OR omc_r.date = '$search') ";
            } else {
                $condition .= " AND (b.`name` LIKE '%$search%' OR omc_r.date = '$search') ";
            }
        }
        $this->paging->rawQuery("SELECT b.name as bank,b.id as bankid, omc_r.date, SUM(omc_r.amount) as amount, COUNT(omc_r.id) as total FROM `omc_receipt`as omc_r LEFT JOIN `banks` as b ON b.id=omc_r.bank_id $condition GROUP BY omc_r.date, omc_r.bank_id ORDER BY omc_r.`date` DESC ");
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
            $response["banks"] = $result;
        } else {
            // var_dump($this->paging);
            $response['success'] = false;
            $response['message'] = "No Banks Summary Available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function getsingle_reconciled()
    {
        $id = $this->http->json->id??null;
        $date = $this->http->json->date??null;
        if (empty($id)) {
            $this->http->_403("bank id is required");
        }
        if (empty($date)) {
            $this->http->_403("date is required");
        }
        return $this->receipt_omc(" WHERE omc_r.`bank_id`= $id AND omc_r.`status`= 'done' AND omc_r.date='$date' ");
    }
    
    public function getsingle_unreconciled()
    {
        $id = $this->http->json->id??null;
        $date = $this->http->json->date??null;
        if (empty($id)) {
            $this->http->_403("bank id is required");
        }
        if (empty($date)) {
            $this->http->_403("date is required");
        }
        return $this->receipt_omc(" WHERE omc_r.`bank_id`= $id AND omc_r.`status`= 'flagged' AND omc_r.date='$date' ");
    }

    public function receipt_omc($condition)
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        if ($search) {
            if (empty($condition)) {
                $condition = " WHERE (o.`name` LIKE '%$search%') ";
            } else {
                $condition .= " AND (o.`name` LIKE '%$search%') ";
            }
        }
        $this->paging->rawQuery("SELECT b.name as bank_name, o.name, omc_r.mode_of_payment, omc_r.bank, omc_r.date, omc_r.amount, omc_r.id FROM `omc_receipt`as omc_r LEFT JOIN `omc` as o ON o.id=omc_r.omc_id LEFT JOIN `banks` as b ON b.id=omc_r.bank_id $condition");
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
            $response["omc"] = $result;
            $response["bank"] = $result[0]->bank_name;
        } else {
            // var_dump($this->paging);
            $response['success'] = false;
            $response['message'] = "No Banks Summary Available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
}
