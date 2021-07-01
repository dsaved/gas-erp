<?php
class AuditlogsModel extends BaseModel
{

    function __construct() {
        parent::__construct();
    }
    
    public function index()
    {
        return $this->getAuditLogs();
    }

    public function get()
    {
        $id = $this->http->json->id;
        return $this->getAuditLogs("WHERE `id`=$id");
    }

    public function auditslogs_transactions()
    {
        $id = $this->http->json->account_id;
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        $user_id = $this->http->json->user_id??null;

        if ($user_id == null) {
            $this->http->_403("Provide user id");
        }

        $bid = $this->http->json->bid??null;
        if($search){
            $amount = implode("", explode(",", $search));;
            $condition = " WHERE (`account_name_from` LIKE '%$search%' OR";
            $condition .= " `account_number_from` LIKE '%$search%' OR";
            $condition .= " `debit_amount` LIKE '%$amount%' OR";
            $condition .= " `debit_date` LIKE '%$search%' OR";
            $condition .= " `description_from` LIKE '%$search%' OR";
            $condition .= " `account_name_to` LIKE '%$search%' OR";
            $condition .= " `account_number_to` LIKE '%$search%' OR";
            $condition .= " `credit_amount` LIKE '%$amount%' OR";
            $condition .= " `credit_date` LIKE '%$search%' OR";
            $condition .= " `description_to` LIKE '%$search%') ";
        }
        
        if (!empty($condition)) {
            $condition .= " AND `account_id_from`=$id";
        } else {
            $condition = " WHERE `account_id_from`=$id";
        }

        if($result_per_page === "all"){
            $result_per_page = 18446744073709551615;
        }

        $this->paging->rawQuery("SELECT * FROM `audits_logs` $condition Order By time ");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();
        // var_dump($this->paging);exit;
        $result = $this->paging->results();
        if(!empty($result)){
            foreach ($result as $key => &$value) {
               $value->debit_amount = number_format($value->debit_amount,2);
               $value->credit_amount = number_format($value->credit_amount,2);
            }
            $response['success'] = true;
            $response["auditlogs"] = $result;
        } else {
            // var_dump($this->paging);exit;
            $response['success'] = false;
            $response['message'] = Msg::$no_auditlogs;
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function getAuditLogs($condition=""){
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        $user_id = $this->http->json->user_id??null;

        if ($user_id == null) {
            $this->http->_403("Provide user id");
        }

        $bid = $this->http->json->bid??null;
        if($search){
            $condition = " WHERE `account_name_from` LIKE '%$search%' OR  `account_number_from` LIKE '%$search%' ";
        }
        
        if($result_per_page === "all"){
            $result_per_page = 18446744073709551615;
        }

        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        // AND `a`.`user_id` = $user_id
        $this->paging->rawQuery("SELECT *,COUNT(id) as total, SUM(debit_amount) as amount FROM `audits_logs` $condition Group by `account_name_from` Order By time ");
        $this->paging->execute();
        $this->paging->reset();
        // var_dump($this->paging);exit;
        $result = $this->paging->results();
        if(!empty($result)){
            foreach ($result as $key => &$value) {
               $value->amount = number_format($value->amount,2);
            }
            $response['success'] = true;
            $response["auditlogs"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = Msg::$no_auditlogs;
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function unreconcile(){
        $response = array();
        $id = implode(',', array_map('intval', $this->http->json->id));
        
        $done = $this->db->query("UPDATE `statements` SET `status`='', `locked_to`= 0 WHERE id IN (SELECT `statement_id_to` FROM `audits_logs` WHERE id IN ($id) )");
        if($done){
            $done = $this->db->query("DELETE FROM `audits_logs` WHERE `id` IN ($id)");
            if ($done) {
                $response['success'] = true;
                $response['message'] = "Record unreconciled successfully";
            } else {
                $response['success'] = false;
                $response['message'] = "Record could not be unreconciled ";
            }
        }
        return $response;
    }

    public function start_export(){
        $filename = $this->http->json->filename;
        $id = $this->http->json->id;
        $ids = implode(',', array_map('intval', $id));

        $jobid = "EXP".time();
        $data = array(
            'jobid' =>  $jobid,
            'filename' =>  $filename."-".time(),
            'ids' =>  $ids,
            'path' =>  ROOT,
            'current_count' =>  0,
            'total_account' =>  0,
            'status' =>  "Initializing",
            'description' =>  "file export request queued",
        );
        $done = $this->db->insert("file_export_status_logs", $data);
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
        $this->db->query("SELECT description,current_count,status,total_account FROM `file_export_status_logs` WHERE `jobid`='$id'");
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
