<?php
class Ghana_govModel extends BaseModel
{
    private static $table = "ghana_gov_omc_receipt";

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->receipts();
    }
    
    public function receipts($condition=" WHERE 1 ")
    {
        $table = self::$table;
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        if ($search) {
            $value = implode("", explode(",", $search));
            $condition .= " AND  (`omc` = (SELECT tin FROM omc WHERE name LIKE '%$search%' LIMIT 1) OR `bank` LIKE '%$search%' OR `mode_of_payment`)";
        }
        $this->paging->rawQuery("SELECT *, omc omc_tin, (SELECT name FROM omc WHERE tin=omc LIMIT 1) omc FROM $table $condition Order By `id`");
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
            $response["receipts"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No receipts available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }

    public function import()
    {
        $response = $this->files->upload_singleFile($this->http->files);
        if ($response['success'] === true) {
            $data = array(
                'path' =>  $response['base_location'],
                'account_id' => 0,
                'bank_id' => 0,
                'type' =>  "ghana_gov_omc_receipt",
                'description' =>  "Job queued",
            );
            $done = $this->db->insert("file_upload_receipt_status", $data);
            if ($done) {
                $jobid = $this->db->lastInsertId();
                $response['success'] = true;
                $response['jobid'] = $jobid;
                $response['message'] = "file uploaded successfully";
                return $response;
            } else {
                $this->files->delete_file($response['base_location']);
                $response['success'] = false;
                $response['message'] = "failed to start reading file content";
                return $response;
            }
        }
    }
    
    public function import_status()
    {
        $id = $this->http->json->jobid;
        $this->db->query("SELECT total,current,processing,status,description FROM `file_upload_receipt_status` WHERE `id`=$id");
        if ($this->db->results() && $this->db->count > 0) {
            $response['success'] = true;
            $response['message'] = "job found";
            $job = $this->db->first();
            
            $response['status'] = array(
                "description"=> $job->description,
                "status"=> $job->status,
                "processing"=> $job->processing,
                "details"=> "Importing data {$job->current} of {$job->total} ",
            );

            return $response;
        }
        $response['success'] = false;
        $response['message'] = "unknown jobid";
        return $response;
    }

    public function delete()
    {
        $response = array();
        $ids = $this->http->json->ids;
        if ($ids) {
            $id = implode(',', array_map('intval', $ids));
            $done = $this->db->query("DELETE FROM ".self::$table." WHERE `id` IN ($id)");
        }
        else{
            $done = $this->db->query("TRUNCATE `".self::$table."`;");
        }
        if ($done) {
            $response['success'] = true;
            $response['message'] = "Data removed successfully";
        } else {
            $response['success'] = false;
            $response['message'] = "Data could not be removed ";
        }
        return $response;
    }
    
    public function difference($condition=" WHERE 1 ")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;

        $omc = $this->http->json->omc??null;
        $status = $this->http->json->status??null;
        $window_code = $this->http->json->window_code??null;


        if ($omc && $omc!="All") {
            $condition .= " AND omc = '$omc'";
        }

        if ($window_code && $window_code!="All") {
            $condition .= " AND window_code = '$window_code'";
        }

        if ($status && $status!="All") {
            if ($status==="Flagged") {
                $condition .= " AND flagged = 1";
            }
            if ($status==="Not Flagged") {
                $condition .= " AND flagged = 0";
            }
        }

        $query = "SELECT *,omc omc_tin, (SELECT name FROM omc WHERE tin=omc LIMIT 1) omc FROM petroleum_good_standing $condition GROUP BY omc ORDER BY window_code";

        $this->paging->rawQuery($query);
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();

        $result = $this->paging->results();
        // var_dump($this->paging);
        if (!empty($result)) {
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $value->difference_amount_receipt_icums = number_format($value->amount - $value->dcl_amount_icum, 2);
                $value->difference_amount_expected_icums = number_format($value->amount - $value->exp_dcl_amount, 2);
                $value->amount = number_format($value->amount, 2);
                $value->exp_dcl_amount = number_format($value->exp_dcl_amount, 2);
                $value->dcl_amount_icum = number_format($value->dcl_amount_icum, 2);
                $value->flagged  = ((int)$value->flagged===1)? true:false;
            }
            $response["reports"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No reports available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function options()
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??40;
        $search = $this->http->json->search??null;
        $condition = "";
        if ($search) {
            $condition = " WHERE (`window_code` LIKE '%$search%') ";
        }
        $this->paging->rawQuery("SELECT DISTINCT window_code FROM petroleum_good_standing $condition Order By window_code");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum(1);
        $this->paging->execute();
        $this->paging->reset();
            
        $results = $this->paging->results();
        if (!empty($results)) {
            foreach ($results as $data) {
                array_push($response, $data->window_code);
            }
        }
        return $response;
    }
    
}
