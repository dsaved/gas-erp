<?php
class StatementsModel extends BaseModel
{

    function __construct() {
        parent::__construct();
    }
    
    public function index()
    {
        return $this->getAccountStatements();
    }

    public function get()
    {
        $id = $this->http->json->id;
        return $this->getAccountStatements("WHERE `id`=$id ");
    }

    public function account_statement()
    {
        $id = $this->http->json->id;
        return $this->getAccountStatements("WHERE `account_id`=$id ");
    }

    public function statement_account()
    {
        $id = $this->http->json->id??null;
        $accountid = $this->http->json->accountid??null;

        return $this->statementsWithAccount(" WHERE statements.`id`= $id AND statements.`account_id`= $accountid");
    }
    
    public function statementsWithAccount($condition="")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;

        $this->paging->rawQuery("SELECT statements.*, accounts.name as account_name, accounts.acc_num1, accounts.acc_num2, accounts.status,organizations.name as orgname,banks.name as bank_name FROM statements JOIN accounts ON statements.account_id = accounts.id JOIN organizations ON accounts.owner = organizations.id JOIN banks ON accounts.bank = banks.id Order By statements.`id` DESC");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();

        $result = $this->paging->results();
        if (!empty($result)) {
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $value->debit_amount = number_format($value->debit_amount, 2, '.', ',');
                $value->credit_amount = number_format($value->credit_amount, 2, '.', ',');
                $value->balance = number_format($value->balance, 2, '.', ',');
                $value->post_date = $this->date->month_year_day($value->post_date);
                $value->value_date = $this->date->month_year_day($value->value_date);
                $value->created = $this->date->format_datetime($value->created);
            }
            $response["transactions"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No transactions available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }

    public function getAccountStatements($condition=" WHERE 1 "){
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        $bank_type = $this->http->json->bank_type??null;
        $bank_name = $this->http->json->bank_name??null;
        
        if ($search) {
            $value = implode("", explode(",", $search));
            $condition .=  " AND (`post_date` LIKE '%$search%' OR `particulars` LIKE '%$search%' OR `reference` LIKE '%$search%'  OR `value_date` LIKE '%$search%' OR `debit_amount` LIKE '%$value%'  OR `credit_amount` LIKE '%$value%'  OR `balance` LIKE '%$value%'  OR `offset_acc_no` LIKE '%$search%' OR `status` LIKE '%$search%' ) " ;
        }

        $this->paging->table("`statements`");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->condition("$condition Order By `post_date` DESC");
        $this->paging->execute();
        $this->paging->reset();
        // var_dump($this->paging);exit;
        $result = $this->paging->results();
        if(!empty($result)){
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $value->debit_amount = number_format($value->debit_amount, 2, '.', ',');
                $value->credit_amount = number_format($value->credit_amount, 2, '.', ',');
                $value->balance = number_format($value->balance, 2, '.', ',');
                $value->post_date = $this->date->month_year_day($value->post_date);
                $value->value_date = $this->date->month_year_day($value->value_date);
                $value->created = $this->date->format_datetime($value->created);

                $this->db->query("SELECT name,acc_num1,acc_num2,owner,bank,status FROM `accounts` WHERE `id` = {$value->account_id} LIMIT 1");
                if($this->db->results() && $this->db->count > 0){
                    $account = $this->db->first();
                    $value->name = $account->name;
                    $value->acc_num1 = $account->acc_num1;
                    $value->acc_num2 = $account->acc_num2;
                    $value->status = $account->status;
                    $this->db->query("SELECT id,fullname FROM `users` WHERE `id`={$account->owner} LIMIT 1");
                    if($this->db->results() && $this->db->count >0){
                        $user = $this->db->first();
                        $value->owner = array("value"=>$user->id,"label"=>$user->fullname);
                    }else{
                        $value->owner = null;
                    }
    
                    $this->db->query("SELECT id,name FROM `banks` WHERE `id`={$account->bank} LIMIT 1");
                    if($this->db->results() && $this->db->count >0){
                        $bank = $this->db->first();
                        $value->bank = array("value"=>$bank->id,"label"=>$bank->name);
                    }else{
                        $value->bank = null;
                    }
                }
            }
            $response["transactions"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = Msg::$no_bank_statement;
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function create()
    {
        $acc_num1 = $this->http->json->acc_num1;
        $acc_num2 = $this->http->json->acc_num2;
        $name = $this->http->json->name;
        $bank_type = $this->http->json->bank_type;
        $bank = $this->http->json->bank;
        $owner = $this->http->json->owner;
        $num = !empty($acc_num1)?$acc_num1:$acc_num2;

        $this->db->query("SELECT acc_num1, acc_num2 FROM `statements` WHERE (`acc_num1` = '$num' OR `acc_num2` ='$num')");
        if($this->db->results() && $this->db->count >0){
            $this->http->_403(Msg::account_exist($num));
        }
        
        $data = array(
            'name' =>  $name,
            'owner' =>  $owner,
            'acc_num1' =>  $acc_num1,
            'acc_num2' =>  $acc_num2,
            'bank_type' =>  $bank_type,
            'bank' =>  $bank,
        );
        if($this->db->insert("statements",$data)){
            $response['success'] = true;
            $response['message'] = "Bank has been created";
            return $response;
        }else {
            $this->http->_500(Msg::$bank_create_error);
        }
    }
    
    public function update()
    {
        $id = $this->http->json->id;
        $acc_num1 = $this->http->json->acc_num1;
        $acc_num2 = $this->http->json->acc_num2;
        $name = $this->http->json->name;
        $bank_type = $this->http->json->bank_type;
        $bank = $this->http->json->bank;
        $owner = $this->http->json->owner;
        $status = $this->http->json->status;
        $num = !empty($acc_num1)?$acc_num1:$acc_num2;

        $this->db->query("SELECT acc_num1, acc_num2, id FROM `statements` WHERE (`acc_num1` = '$num' OR `acc_num2` ='$num') AND `id`!=$id");
        if($this->db->results() && $this->db->count > 0){
            $this->http->_403(Msg::account_exist($num));
        }
        
        $inactive_date = null;
        if($status === "Inactive"){
            $inactive_date = $this->date->getDate();
        }
        
        $data = array(
            'name' =>  $name,
            'owner' =>  $owner,
            'acc_num1' =>  $acc_num1,
            'acc_num2' =>  $acc_num2,
            'bank_type' =>  $bank_type,
            'status' =>  $status,
            'date_inactive' =>  $inactive_date,
            'bank' =>  $bank,
        );
        
        if($this->db->updateByID("statements","id", $id, $data)){
            $response['success'] = true;
            $response['message'] = "Bank has been updated";
            return $response;
        }else {
            $this->http->_500(Msg::$bank_create_error);
        }
    }
    
    public function delete(){
        $response = array();
        $id = implode(',', array_map('intval', $this->http->json->id));
        $done = $this->db->query("DELETE FROM `statements` WHERE `id` IN ($id)");
        if ($done) {
            $response['success'] = true;
            $response['message'] = "Record deleted successfully";
        } else {
            $response['success'] = false;
            $response['message'] = "Record could not be deleted ";
        }
        return $response;
    }
    
    public function import_status(){
        $id = $this->http->json->jobid;
        $this->db->query("SELECT total,current,processing,status,description FROM `file_upload_status` WHERE `id`=$id");
        if($this->db->results() && $this->db->count > 0){
            $response['success'] = true;
            $response['message'] = "job found";
            $job = $this->db->first();
            
            $response['status'] = array(
                "description"=> $job->description,
                "status"=> $job->status,
                "processing"=> $job->processing,
                "details"=> "Creating transaction {$job->current} of {$job->total} ",
            );

            return $response;
        }
        $response['success'] = false;
        $response['message'] = "unknown jobid";
        return $response;
    }

    public function import(){
        $id = $this->http->post->id;
        $response = $this->files->upload_singleFile($this->http->files);
        if ($response['success'] === true) {
            $data = array(
                'path' =>  $response['base_location'],
                'account_id' => $id,
                'description' =>  "Job queued",
            );
            $done = $this->db->insert("file_upload_status", $data);
            if ($done) {
                $jobid = $this->db->lastInsertId();
                $response['success'] = true;
                $response['jobid'] = $jobid;
                $response['message'] = "file uploaded successfully";
                return $response;
            }else{
                $this->files->delete_file($response['base_location']);
                $response['success'] = false;
                $response['message'] = "failed to start reading file content";
                return $response;
            }
        }
    }
}
