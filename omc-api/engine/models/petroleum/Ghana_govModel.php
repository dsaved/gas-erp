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
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        if ($search) {
            $value = implode("", explode(",", $search));
            $condition .= " AND  (`omc` LIKE '%$search%' OR `bank` LIKE '%$search%' OR `mode_of_payment`)";
        }
        $this->paging->table(self::$table);
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->condition("$condition Order By `id`");
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
        $date_range = $this->http->json->date_range??null;
        $status = $this->http->json->status??null;
        $nagatives = $this->http->json->nagatives??null;
        $dateRang = "";
        $on = "";

        if ($date_range) {
            if ($date_range->endDate && $date_range->startDate) {
                $startDate = $this->date->sql_date($date_range->startDate);
                $endDate = $this->date->sql_date($date_range->endDate);
                $condition .= " AND (rep.date  BETWEEN '$startDate' AND '$endDate') ";
                $on = " AND (icum_dcl.date  BETWEEN '$startDate' AND '$endDate') ";
                $dateRang = $this->date->month_year_day($startDate) ." - ". $this->date->month_year_day($endDate);
            }
        }

        if ($omc && $omc!="All") {
            $condition .= " AND rep.omc = '$omc'";
        }

        $HAVING=" HAVING 1";
        if ($status && $status!="All") {
            if ($status==="Flagged") {
                $HAVING .= " AND (SUM(rep.amount) <> SUM(icum_dcl.amount) OR SUM(icum_dcl.amount) IS NULL)";
            }
            if ($status==="Not Flagged") {
                $HAVING .= " AND SUM(rep.amount) = SUM(icum_dcl.amount) ";
            }
        }

        if ($nagatives && $nagatives!="All") {
            if ($nagatives==="Nagatives") {
                $HAVING .= " AND SUM(rep.amount) - SUM(icum_dcl.amount)  < 0 ";
            }
            if ($nagatives==="Positives") {
                $HAVING .= " AND (SUM(rep.amount) - SUM(icum_dcl.amount)  >= 0 OR SUM(icum_dcl.amount) IS NULL) ";
            }
        }

        $query = "SELECT 'All time' date, rep.omc, SUM(rep.amount) amount, icum_dcl.omc dcl_omc, SUM(icum_dcl.amount) dcl_amount FROM ".self::$table." rep LEFT JOIN petroleum_icums_declaration icum_dcl ON rep.omc=icum_dcl.omc $on $condition GROUP BY rep.omc $HAVING ORDER BY rep.id";
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
                $value->difference_amount = number_format($value->amount - $value->dcl_amount, 2);
                $value->amount = number_format($value->amount, 2);
                $value->dcl_amount = number_format($value->dcl_amount, 2);
                $value->date = $date_range && $date_range->endDate?$dateRang:$value->date;
                $value->flagged  = $value->difference_amount <> 0;
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
