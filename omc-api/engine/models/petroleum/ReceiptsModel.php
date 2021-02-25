<?php
class ReceiptsModel extends BaseModel
{
    private static $table = "omc_receipt";

    public function __construct()
    {
        parent::__construct();
    }
    
    public function index()
    {
        $id = $this->http->json->id??null;
        if (empty($id)) {
            $this->http->_403("OMC id is required");
        }
        return $this->receipt(" WHERE `omc_id`= $id");
    }

    public function get()
    {
        $id = $this->http->json->id??null;
        if (empty($id)) {
            $this->http->_403("Receipt id is required");
        }
        return $this->receipt(" WHERE `id`= $id");
    }

    public function receipt_omc()
    {
        $id = $this->http->json->id??null;
        $omcid = $this->http->json->omcid??null;
        if (empty($id)) {
            $this->http->_403("Receipt id is required");
        }
        if (empty($omcid)) {
            $this->http->_403("OMC id is required");
        }
        return $this->receiptWithOmc(" WHERE ".self::$table.".`omc_id`= $omcid");
    }
    
    public function receiptWithOmc($condition="")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;

        $this->paging->rawQuery("SELECT ".self::$table.".*, omc.name,omc.phone,omc.location,omc.region,omc.email,omc.district FROM ".self::$table." JOIN omc ON ".self::$table.".omc_id = omc.id $condition Order By ".self::$table.".`id` DESC");
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
    
    public function receipt($condition="")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        if ($search) {
            if (empty($condition)) {
                $condition .= " WHERE (`bank` LIKE '%$search%' OR `declaration_number` LIKE '%$search%'OR `receipt_number` LIKE '%$search%' OR `mode_of_payment` LIKE '%$search%' OR `amount` LIKE '%$search%' OR `status` LIKE '%$search%') ";
            }else{
                $condition .= " AND (`bank` LIKE '%$search%' OR `declaration_number` LIKE '%$search%'OR `receipt_number` LIKE '%$search%' OR `mode_of_payment` LIKE '%$search%' OR `amount` LIKE '%$search%' OR `status` LIKE '%$search%') ";
            }
        }
        $this->paging->table(self::$table);
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->condition("$condition Order By `id` DESC");
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

    public function banks()
    {
        $response = array();
        $id = $this->http->json->bid; //id of current bank
        // var_dump("id is here ".$id);
        //make a query to choose banks excluding the current bank.
        //if bank is bog/bank of ghana list all similer banks
        $this->db->query("SELECT bank_type FROM `accounts` WHERE `id`=$id");
        if($this->db->results() && $this->db->count > 0){
            $account = $this->db->first();
            if(strtolower($account->bank_type) === "bank of ghana"){
                return $this->getBankAccounts("WHERE (`id`!=$id AND `bank_type` = 'Bank Of Ghana') ");
            }else{
                return $this->getBankAccounts("WHERE `id`!=$id");
            }
        }else{
            return $this->getBankAccounts("WHERE `id`!=$id ");
        }
    }

    public function getBankAccounts($condition=""){
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        $bank_type = $this->http->json->bank_type??null;
        $bank_name = $this->http->json->bank_name??null;
        $filter_category = $this->http->json->filter_category??null;

        $bid = $this->http->json->bid??null;
        if($search){
            $condition = "WHERE ( `name` LIKE '%$search%' OR  `acc_num1` LIKE '%$search%' OR  `acc_num2` LIKE '%$search%'  OR `status` LIKE '%$search%')";
            //ac.
        }

        if($bank_type){
            if(empty($condition)){
                $condition = "WHERE `bank_type` LIKE '%$bank_type%' ";//ac.bank_type
            }else if(!empty($condition)){
                $condition .=  " AND `bank_type` LIKE '%$bank_type%' " ;//ac.bank_type
            }
        }
        
        if($bank_name){
            $this->db->query("SELECT id FROM `banks` WHERE `name` LIKE '%$bank_name%' GROUP BY `name`");
            if($this->db->results() && $this->db->count > 0){
                $bankID = array();
                $rateResult = $this->db->results();
                foreach ($rateResult as $k => $val) {
                    array_push($bankID, $val->id);
                }

                if(!empty($bankID)){
                    $ids = implode(",", array_map("intval", $bankID));
                    if(empty($condition)){
                        $condition = "WHERE `bank` IN ($ids) ";//ac.bank
                    }else if(!empty($condition)){
                        $condition .=  " AND (`bank` IN ($ids)) " ;//ac.bank
                    }
                }
            }
        }
        
        if($filter_category){
            if($filter_category!=="all"){ 
                if(empty($condition)){
                    $condition = "WHERE `category` =  $filter_category ";
                }else if(!empty($condition)){
                    $condition .=  " AND (`category` =  $filter_category) " ;
                }
            }
        }
        
        if($result_per_page === "all"){
            $result_per_page = 18446744073709551615;
        }

        $this->paging->rawQuery("SELECT (SELECT name FROM `organizations` WHERE `id`=ac.owner LIMIT 1) as owner_name, (SELECT name FROM `banks` WHERE `id`=ac.bank LIMIT 1) as bank_name, (SELECT amount FROM `account_balance` WHERE `account_id`=ac.id LIMIT 1) as account_balance, (SELECT date FROM `account_balance` WHERE `account_id`=ac.id LIMIT 1) as post_date,ac.id, ac.name,ac.acc_num1,ac.acc_num2,ac.status, ac.date_inactive  FROM `accounts` as ac $condition Order By `name`");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();
        // var_dump($this->paging);exit;

        $result = $this->paging->results();
        if(!empty($result)){
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $value->account_balance = number_format($value->account_balance,2);
                $value->aschiled = false;
                if($bid){
                    $this->db->query("SELECT id FROM `asinged_account` WHERE `main_account`={$bid} AND `child_account`= {$value->id} LIMIT 1");
                    if($this->db->results() && $this->db->count >0){
                        $value->aschiled = true;
                    }
                }
                
                if($this->date->isValidDate($value->date_inactive)){
                    $value->date_inactive = $this->date->sql_date($value->date_inactive);
                }
            }
            $response["bankaccounts"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = Msg::$no_bank_account;
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }

    // remove
    
    public function remove()
    {
        $response = array();
        $done = $this->db->query("DELETE FROM ".self::$table." WHERE `omc_id` = {$this->http->json->id}");
        if ($done) {
            $response['success'] = true;
            $response['message'] = "Receipts Removed successfully";
        } else {
            $response['success'] = false;
            $response['message'] = "Receipts could not be Removed ";
        }
        return $response;
    }
    
    public function remove_reconcilation()
    {
        $response = array();
        $omc_id = $this->http->json->id;
        $done = $this->db->query("UPDATE omc_receipt SET omc_receipt.status='' WHERE omc_receipt.omc_id = {$omc_id}");
        if ($done) {
            $this->db->query("UPDATE statements SET receipt_status='' WHERE account_id IN (SELECT account_id FROM audits_logs_omc WHERE `omc_id`={$omc_id});DELETE FROM audits_logs_omc WHERE `omc_id` = {$omc_id}");
            $this->db->query("UPDATE omc SET reconciled='0' WHERE id = {$omc_id}");
            $this->paging->table("omc");
            $this->paging->result_per_page(1);
            $this->paging->pageNum(1);
            $this->paging->condition("WHERE `id`={$omc_id} Order By `name`");
            $this->paging->execute();
            $this->paging->reset();
            
            $result = $this->paging->results();
            if (!empty($result)) {
                $response["omc"] = $result[0];
            } 
            $response['success'] = true;
            $response['message'] = "Reconcilation Removed successfully";
        } else {
            $response['success'] = false;
            $response['message'] = "Reconcilation could not be Removed ";
        }
        return $response;
    }
}
