<?php
class SurchargeModel extends BaseModel
{
    private static $table = "surcharge_rate";

    public function __construct()
    {
        parent::__construct();
    }
    
    public function index()
    {
        return $this->surcharges();
    }
    
    public function accounts()
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        $bank_type = $this->http->json->bank_type??null;
        $bank_name = $this->http->json->bank_name??null;
        $filter_category = $this->http->json->filter_category??null;
        $condition = "";

        if ($search) {
            $condition = "WHERE ( ac.`name` LIKE '%$search%' OR  ac.`acc_num1` LIKE '%$search%' OR  ac.`acc_num2` LIKE '%$search%')";
        }

        if ($bank_type) {
            if ($bank_type!=="all") {
                if (empty($condition)) {
                    $condition = "WHERE ac.`bank_type` LIKE '%$bank_type%' ";
                } elseif (!empty($condition)) {
                    $condition .=  " AND ac.`bank_type` LIKE '%$bank_type%' " ;
                }
            }
        }
        
        if ($bank_name) {
            $this->db->query("SELECT id FROM `banks` WHERE `name` LIKE '%$bank_name%' GROUP BY `name`");
            if ($this->db->results() && $this->db->count > 0) {
                $bankID = array();
                $rateResult = $this->db->results();
                foreach ($rateResult as $k => $val) {
                    array_push($bankID, $val->id);
                }

                if (!empty($bankID)) {
                    $ids = implode(",", array_map("intval", $bankID));
                    if (empty($condition)) {
                        $condition = "WHERE ac.`bank` IN ($ids) ";
                    } elseif (!empty($condition)) {
                        $condition .=  " AND (ac.`bank` IN ($ids)) " ;
                    }
                }
            }
        }

        if ($filter_category) {
            if ($filter_category!=="all") {
                if (empty($condition)) {
                    $condition = "WHERE ac.`category` =  $filter_category ";
                } elseif (!empty($condition)) {
                    $condition .=  " AND (ac.`category` =  $filter_category) " ;
                }
            }
        }
        
        if ($result_per_page === "all") {
            $result_per_page = 18446744073709551615;
        }

        // $this->paging->table("`accounts`");
        $this->paging->rawQuery("SELECT SUM(su.penalty) as total_penalty, COUNT(su.id) as surcharge, (SELECT name FROM `organizations` WHERE `id`=ac.owner LIMIT 1) as owner_name, (SELECT name FROM `banks` WHERE `id`=ac.bank LIMIT 1) as bank_name, ac.id, ac.name,ac.acc_num1,ac.acc_num2,ac.status, ac.date_inactive, su.*  FROM surcharge as su LEFT JOIN `accounts` as ac on ac.id=su.account_id $condition Group by ac.id Order By `name`");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        // $this->paging->condition("$condition Order By `name`");
        $this->paging->execute();
        $this->paging->reset();
        // var_dump($this->paging);exit;
        $result = $this->paging->results();
        if (!empty($result)) {
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $value->penalty = number_format($value->total_penalty, 2);
            }
            $response["surcharge"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = Msg::$no_bank_account;
        }

        $response["pagination"] = $this->paging->paging();
        $response["bank_accounts"] =  [];

        $this->db->query("SELECT * FROM accounts ORDER BY name");
        $results = $this->db->results();
        if (!empty($results)) {
            foreach ($results as $data) {
                $response["bank_accounts"][$data->id] = $data->name;
            }
        }

        return $response;
    }
    
    public function charges()
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        $id = $this->http->json->id??null;
        $condition = "WHERE su.account_id = {$id} ";
        if($search){
            $condition .= "AND ( su.`date` = '$search' )";
        }

        if ($result_per_page === "all") {
            $result_per_page = 18446744073709551615;
        }

        $this->paging->rawQuery("SELECT (SELECT Sum(penalty) as penalty FROM `surcharge` WHERE `account_id`=ac.id GROUP BY ac.id) as total_surcharge, (SELECT name FROM `organizations` WHERE `id`=ac.owner LIMIT 1) as owner_name, (SELECT name FROM `banks` WHERE `id`=ac.bank LIMIT 1) as bank_name, ac.id, ac.name,ac.acc_num1,ac.acc_num2,ac.status, ac.date_inactive, su.*  FROM surcharge as su LEFT JOIN `accounts` as ac on ac.id=su.account_id $condition Order By `name`");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();
        // var_dump($this->paging);exit;
        $result = $this->paging->results();
        if (!empty($result)) {
            $response['success'] = true;
            $response['total_surcharge'] = 0.0;
            foreach ($result as $key => &$value) {
                $value->penalty = number_format($value->penalty, 2);
                $value->total_credit = number_format($value->total_credit, 2);
                $value->total_debit = number_format($value->total_debit, 2);
                $value->comulative_balnace_previous = number_format($value->comulative_balnace_previous, 2);
                $value->untransfered_founds = number_format($value->untransfered_founds, 2);
                $response['total_surcharge'] = number_format($value->total_surcharge, 2);
            }
            $response["surcharge"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No penalty found";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function get()
    {
        $id = $this->http->json->id??null;
        if (empty($id)) {
            $this->http->_403("Tax id is required");
        }
        return $this->schedule(" WHERE `id`= $id");
    }
    
    public function schedule($condition="")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        
        if ($search) {
            if (empty($condition)) {
                $condition .= " WHERE  `name` LIKE '%$search%' ";
            }else{
                $condition .= " AND `name` LIKE '%$search%' ";
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
            $response["surcharges"] = $results;
        } else {
            $response['success'] = false;
            $response['message'] = "No tax available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function surcharges($condition="")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        
        if ($search) {
            if (empty($condition)) {
                $condition .= " WHERE  `name` LIKE '%$search%' ";
            }else{
                $condition .= " AND `name` LIKE '%$search%' ";
            }
        }

        $this->paging->rawQuery("SELECT * FROM ".self::$table." $condition");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();

        // $this->http->reply($this->paging->lastQuery())->dump();
        
        $results = $this->paging->results();
        if (!empty($results)) {
            $response['success'] = true;
            $response["surcharges"] = $results;
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
            $this->http->_403("Please provide name");
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
            "name" =>  $name,
            "date_from" =>  $this->date->sql_datetime($date_from),
            "date_to" =>  $this->date->sql_datetime($date_to),
            "rate"=>$rate,
        );
        if ($this->db->insert(self::$table, $data)) {
            $response['success'] = true;
            $response['message'] = "Rate has been created";
            return $response;
        } else {
            $this->http->_500("Error while creating Rate");
        }
    }
    
    public function updatetax()
    {
        $id = $this->http->json->id??null;
        if ($id===null) {
            $this->http->_403("Please provide rate id");
        }
        
        $name = $this->http->json->name??null;
        if ($name===null) {
            $this->http->_403("Please provide name");
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
            "name" =>  $name,
            "date_from" =>  $this->date->sql_datetime($date_from),
            "date_to" =>  $this->date->sql_datetime($date_to),
            "rate"=>$rate,
        );

        if ($this->db->updateByID(self::$table, "id", $id, $data)) {
            $response['success'] = true;
            $response['message'] = "Rate has been updated";
            return $response;
        } else {
            $this->http->_500("Error while updating Rate");
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
    
    public function compile()
    {
        $user_id = $this->http->json->user_id;
        $account_id = $this->http->json->account_id;
        $this->db->query("DELETE FROM `surcharge` WHERE account_id=$account_id;");
        $jobid = "CMP".time();
        $data = array(
            'jobid' =>  $jobid,
            'user_id' =>  $user_id,
            'bank_type' =>  "compile",
            'account' =>  0,
            'ids' =>  $account_id,
            'intval' =>  0,
            'reconcile_with' =>  "few",
            'reconciling_with' =>  "",
            'proccessing_account' =>  0,
            'total_account' =>  0,
            'status' =>  "Initializing",
            'description' =>  "compilation request queued",
        );
        $done = $this->db->insert("reconcilation_status", $data);
        if ($done) {
            $response['success'] = true;
            $response['jobid'] = $jobid;
            $response['message'] = "compilation submitted";
            return $response;
        } else {
            $response['success'] = false;
            $response['message'] = "Failed to initialize compilation";
            return $response;
        }
    }
    
    public function compilation_status()
    {
        $id = $this->http->json->jobid;
        $this->db->query("SELECT start_time,end_time,description,reconciling_with,status,proccessing_account,total_account FROM `reconcilation_status` WHERE `jobid`='$id'");
        if ($this->db->results() && $this->db->count > 0) {
            $response['success'] = true;
            $response['message'] = "job found";
            $data = $this->db->first();

            $start_time=date_create("$data->start_time");
            $started = date_format($start_time, "H:i:s");
            $end_time=date_create("$data->end_time");
            $ended = date_format($end_time, "H:i:s");
            $response['status'] = array(
                "description"=> $data->description,
                "status"=> $data->status,
                "account"=> $data->reconciling_with,
                "details"=> "",
            );
            if (strtolower($data->status) === "completed") {
                $response['status'] ['details'] = "Started: $started, Ended: $ended";
            } else {
                $response['status'] ['details'] = "Started: $started, progress {$data->proccessing_account} of {$data->total_account} ";
            }
            return $response;
        }
        $response['success'] = false;
        $response['message'] = "unknown jobid";
        return $response;
    }

    public function start_export(){
        $filename = $this->http->json->filename;
        $this->db->query("SELECT filename FROM `file_export_status` WHERE `filename`='$filename'");
        if($this->db->results() && $this->db->count > 0){
            $this->http->_403("File name already exixt");            
        }

        $jobid = "EXP".time();
        $data = array(
            'jobid' =>  $jobid,
            'filename' =>  $filename,
            'ids' =>  0,
            'path' =>  ROOT,
            'export_type' =>  "PENALTY",
            'current_count' =>  0,
            'total_account' =>  0,
            'status' =>  "Initializing",
            'description' =>  "file export request queued",
        );
        $done = $this->db->insert("file_export_status", $data);
        if ($done) {
            $response['success'] = true;
            $response['jobid'] = $jobid;
            $response['message'] = "file export submitted";
            return $response;
        }else{
            $response['success'] = false;
            $response['message'] = "Failed to initialize file export";
            return $response;
        }
    }

    public function file_export_status(){
        $id = $this->http->json->jobid;
        $this->db->query("SELECT description,current_count,status,total_account FROM `file_export_status` WHERE `jobid`='$id'");
        if($this->db->results() && $this->db->count > 0){
            $response['success'] = true;
            $response['message'] = "job found";
            $data = $this->db->first();

            $response['status'] = array(
                "description"=> $data->description,
                "status"=> $data->status,
                "details"=>  "Progress {$data->current_count} of {$data->total_account} ",
            );
            return $response;
        }
        $response['success'] = false;
        $response['message'] = "unknown jobid";
        return $response;
    }
}
