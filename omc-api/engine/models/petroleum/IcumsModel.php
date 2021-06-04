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
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        if ($search) {
            $value = implode("", explode(",", $search));
            $condition .= " AND  (`omc` LIKE '%$search%' OR  `amount` LIKE '%$value%' )";
        }
        $this->paging->table(self::$table);
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->condition("$condition Order By `date` DESC");
        $this->paging->execute();
        $this->paging->reset();

        $result = $this->paging->results();
        if (!empty($result)) {
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $value->amount = number_format($value->amount, 2);
                $value->date = $this->date->human_date($value->date);
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
            $condition .= " AND omc = '$omc'";
        }

        $query = "SELECT * FROM ".self::$table." $condition ORDER BY date DESC";
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
                $exp_declaration_amount = $this->expected_declaration($value->omc, $value->date);
                $value->difference_amount = number_format($value->amount - $exp_declaration_amount, 2);
                $value->flagged  = $value->difference_amount <> 0;
                $value->exp_declaration_amount = number_format($exp_declaration_amount, 2);
                $value->amount = number_format($value->amount, 2);
                if ($status && $status!="All") {
                    if ($status==="Flagged") {
                        if(!$value->flagged){
                            unset($result[$key]);
                            continue;
                        }
                    }elseif ($status==="Not Flagged") {
                        if($value->flagged){
                            unset($result[$key]);
                            continue;
                        }
                    }
                }
            }
            $response["reports"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No reports available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function expected_declaration($omc, $date)
    {
        $result_total = 0;
        $computes = $this->getWaybills($omc, $date);
        foreach ($computes as $key => $compute) {
            $total = 0;
            $this->db->query("SELECT tw.*, tt.name tax FROM tax_window tw JOIN `tax_type` tt ON tw.tax_type=tt.id WHERE tw.`tax_product` = (SELECT id FROM tax_schedule_products WHERE name = '{$compute->product_type}' LIMIT 1) AND tw.`date_from`<= '{$date}' AND tw.`date_to` >= '{$date}'");
            $queryResult = $this->db->results();
            if($queryResult && $this->db->count > 0){
                foreach ($queryResult as $key => $tax) {
                    $total += $compute->volume * $tax->rate;
                }
            }
            $result_total+=$total;
        }
        return $result_total;
    }

    public function getWaybills($omc, $date){
        $this->db->query("SELECT SUM(volume) volume, MIN(date) date, product_type FROM petroleum_waybill WHERE omc='$omc' AND (date BETWEEN DATE((SELECT date_from FROM tax_window WHERE `date_from`<= '$date' AND `date_to` >= '$date' LIMIT 1 )) AND DATE((SELECT date_to FROM tax_window WHERE `date_from`<= '$date' AND `date_to` >= '$date' LIMIT 1 )) ) GROUP BY product_type, (SELECT CONCAT(tax_product, '-', name) FROM tax_window WHERE `date_from`<= '$date' AND `date_to` >= '$date' LIMIT 1) ORDER BY product_type ASC, date ASC ");
        // var_dump($this->db);
        return $this->db->results();
    }
}
