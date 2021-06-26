<?php
class IcumsModel extends BaseModel
{
    private static $table = "petroleum_icums_declaration";

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->declarations();
    }
    
    public function declarations($condition=" WHERE 1 ")
    {
        $table = self::$table;
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        if ($search) {
            $value = implode("", explode(",", $search));
            $condition .= " AND  (pid.`omc` LIKE '%$search%' OR pid.`window_code` LIKE '%$search%' OR  pid.`amount` LIKE '%$value%'  OR  pid.`product_type`= (SELECT code FROM tax_schedule_products WHERE `name` LIKE '%$value%' LIMIT 1))";
        }
        $this->paging->rawQuery("SELECT pid.*, tsp.name product, omc.name omc FROM $table AS pid LEFT JOIN tax_schedule_products tsp ON tsp.code=pid.product_type LEFT JOIN omc omc ON omc.tin=pid.omc $condition Order By `date` DESC");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();

        $result = $this->paging->results();
        // var_dump($this->paging);
        if (!empty($result)) {
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $value->amount = number_format($value->amount, 2);
            }
            $response["declarations"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No declarations available";
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
                'type' =>  "petroleum_icums_declaration_imp",
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
        $done = $this->db->query("TRUNCATE `".self::$table."`;");
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

        if ($omc && $omc!="All") {
            $condition .= " AND pid.omc = '$omc'";
        }

        if ($status && $status!="All") {
            if ($status==="Flagged") {
                $condition .= " AND pid.flagged = 1";
            } elseif ($status==="Not Flagged") {
                $condition .= " AND pid.flagged = 0";
            }
        }

        $query = "SELECT pid.*, o.name omc FROM petroleum_icums_differences as pid LEFT JOIN omc as o ON o.tin=pid.omc $condition ORDER BY o.name";

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
                $value->difference_amount = number_format($value->amount - $value->exp_declaration_amount, 2);
                $value->exp_declaration_amount = number_format($value->exp_declaration_amount, 2);
                $value->amount = number_format($value->amount, 2);
                $value->flagged = ((int)$value->flagged===1)? true:false;
            }
            $response["reports"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No reports available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
}
