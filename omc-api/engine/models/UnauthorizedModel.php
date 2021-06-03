<?php
class UnauthorizedModel extends BaseModel
{

    function __construct() {
        parent::__construct();
    }
    
    public function index()
    {
        return $this->getUnauthorized();
    }

    public function getUnauthorized(){
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        $user_id = $this->http->json->user_id??null;
        $access_type = $this->http->json->access_type??null;
        $bank_type = $this->http->json->bank_type??null;
        $isbog = $this->http->json->isbog??null;

        if ($user_id == null) {
            $this->http->_403("Provide account id");
        }
        
        if($result_per_page === "all"){
            $result_per_page = 18446744073709551615;
        }

        $bid = $this->http->json->bid??null;
        $condition ="";
        if($search){
            $condition = "WHERE (`a`.`name` LIKE '%$search%' OR";
            $condition .= " `b`.`name` LIKE '%$search%' OR";
            $condition .= " `a`.`acc_num1` LIKE '%$search%' OR";
            $condition .= " `a`.`acc_num2` LIKE '%$search%') ";
        }

        if(isset($isbog) && !empty($isbog) && $isbog==="true"){
            if (!empty($condition)) {
                $condition = " AND `ut`.`escalated_to_bog`=1 ";
            } else {
                $condition = " WHERE `ut`.`escalated_to_bog`=1 ";
            }
        }else{
            if(strtolower($access_type)!=="admin"){
                $institute = 0;
                $orgname = "";
                $this->db->query("SELECT organization FROM `users` WHERE `id`={$user_id}");
                if($this->db->results() && $this->db->count > 0){
                    $user = $this->db->first();
                    $institute = $user->organization;
                }

                //bank, accounts, unauthorized_transaers
                //user belongs to organization which is equal to institute
                //institute = account.owner
                if ($institute!=0) {
                    if (!empty($condition)) {
                        $condition .= " AND (`a`.`owner` = $institute )";
                    } else {
                        $condition = " WHERE (`a`.`owner` = $institute )";
                    }
                }
            }

            if(!empty($condition)){
                if($bank_type!=="all"){
                    $condition .= " AND `a`.`bank_type` LIKE '%$bank_type%' ";
                }
            }else{
                if($bank_type!=="all"){
                    $condition = " WHERE `a`.`bank_type` LIKE '%$bank_type%' ";
                }
            }
        }
        if (!empty($condition)) {
            $condition .= " AND ut.`softdelete`=0";
        } else {
            $condition = " WHERE ut.`softdelete`=0";
        }
        // $this->paging->table("`audits_logs`");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->rawQuery("SELECT COUNT(*) AS infraction_count, SUM(ut.amount) AS amount, ut.account_from, ut.offset_account, ut.id, ut.statement_id, ut.time, a.name, a.acc_num1, a.acc_num2, b.name bank_name FROM `unauthorized_transfers` AS ut INNER JOIN `accounts` AS a ON a.id = ut.account_from LEFT JOIN `banks` b ON b.id=a.bank $condition GROUP BY `ut`.`account_from` Order By `ut`.`time`");
        $this->paging->execute();
        $this->paging->reset();
        // var_dump($this->paging->lastQuery());exit;
        $result = $this->paging->results();
        if(!empty($result)){
            foreach ($result as $key => &$value) {
                $value->amount = number_format($value->amount,2);
            }
            $response['success'] = true;
            $response["unauthorized"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = Msg::$no_bog_unauthorized_account;
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }

    public function recipients(){
        $response = array();
        $response['main_account'] = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        $user_id = $this->http->json->user_id??null;
        $account_id = $this->http->json->account_id??null;
        $access_type = $this->http->json->access_type??null;
        $isbog = $this->http->json->isbog??null;

        if ($user_id == null) {
            $this->http->_403("Provide account id");
        }
        
        $condition ="";
        if($search){
            $condition = "WHERE ( `ut`.`offset_account` LIKE '%$search%') ";
        } 


        if(isset($isbog) && !empty($isbog) && $isbog==="true"){
            if (!empty($condition)) {
                $condition = " AND `ut`.`escalated_to_bog`=1 ";
            } else {
                $condition = " WHERE `ut`.`escalated_to_bog`=1 ";
            }
        }

        if (!empty($condition)) {
            $condition .= " AND ut.`softdelete`=0";
        } else {
            $condition = " WHERE ut.`softdelete`=0";
        }
        
        if(empty($condition)){
            $condition = " WHERE `ut`.`account_from` = {$account_id}";
        }else{
            $condition .= " AND `ut`.`account_from` = {$account_id}";
        }
            
        if($result_per_page === "all"){
            $result_per_page = 18446744073709551615;
        }

        // $this->paging->table("`audits_logs`");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->rawQuery("SELECT COUNT(ut.offset_account) AS infraction_count, SUM(ut.amount) AS amount, ut.account_from, ut.offset_account, ut.statement_id, ut.time FROM `unauthorized_transfers` AS ut  $condition GROUP BY `ut`.`offset_account` Order By `ut`.`offset_account` DESC");
        $this->paging->execute();
        $this->paging->reset();
        // var_dump($this->paging);exit;
        $result = $this->paging->results();
        if(!empty($result)){
            foreach ($result as $key => &$value) {
                // var_dump($condition);
                $this->db->query("SELECT (SELECT COUNT(id) FROM `comments` WHERE `statement_id` IN (SELECT id FROM statements WHERE offset_acc_no = '{$value->offset_account}') AND `account_from` = {$account_id} AND `reviewedby`='org') as org_count, (SELECT COUNT(id) FROM `comments` WHERE `statement_id` IN (SELECT id FROM statements WHERE offset_acc_no = '{$value->offset_account}') AND `account_from` = {$account_id} AND `reviewedby`='bog') as bog_count ");
                $counts = $this->db->first();
                $value->org = $counts->org_count;
                $value->bog = $counts->bog_count;
                $value->amount = number_format($value->amount, 2);
            }
            $response['success'] = true;
            $response["unauthorized"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = Msg::$no_bog_unauthorized_account;
        }

        $this->db->query("SELECT name,acc_num1,acc_num2,status FROM `accounts` WHERE `id`={$account_id}");   
        if($this->db->results() && $this->db->count > 0){
            $acct = $this->db->first();
            $response['main_account'] = array(
                "name"=> $acct->name,
                "status"=> $acct->status,
                "acc_num1"=> $acct->acc_num1,
                "acc_num2"=> $acct->acc_num2
            );
        }
    
        // $this->http->reply($this->db)->dump();
        $response["pagination"] = $this->paging->paging();
        return $response;
    }

    public function transactions(){
        $response = array();
        $response['main_account'] = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        $user_id = $this->http->json->user_id??null;
        $org_status = $this->http->json->org_status??null;
        $bog_status = $this->http->json->bog_status??null;
        $access_type = $this->http->json->access_type??null;
        $isbog = $this->http->json->isbog??null;
        $condition = "";

        if ($user_id == null) {
            $this->http->_403("Provide account id");
        }
        
        if($bog_status === "all"){
            $bog_status = "";
        }

        if($org_status === "all"){
            $org_status = "";
        }

        $account_id = $this->http->json->account_id??null;
        $offset_account = $this->http->json->offset_account??null;
        if($search){
            $value = implode("", explode(",", $search));
            $condition = "WHERE `st`.`reference` LIKE '%$search%' OR `st`.`debit_amount` LIKE '%$value%'";
        }
        
        if (isset($isbog) && !empty($isbog) && $isbog==="true") {
            if (!empty($condition)) {
                $condition = " AND `ut`.`escalated_to_bog`=1 ";
            } else {
                $condition = " WHERE `ut`.`escalated_to_bog`=1 ";
            }
        }

        //introducr user access level `softdelete`=0
        if (!empty($condition)) {
            $condition .= " AND ut.`softdelete`=0";
        } else {
            $condition = " WHERE ut.`softdelete`=0";
        }

        if(empty($condition)){
            if(!empty($offset_account)){
                $condition = " WHERE ut.`bog_status` LIKE '%$bog_status%' AND ut.`org_status` LIKE '%$org_status%' AND ut.account_from = {$account_id} AND `ut`.`offset_account` = '{$offset_account}' ";
            }else{
                $condition = " WHERE ut.`bog_status` LIKE '%$bog_status%' AND ut.`org_status` LIKE '%$org_status%' AND ut.account_from = {$account_id} AND `ut`.`offset_account` = '' ";
            }
        }else{
            if(!empty($offset_account)){
                $condition .= " AND ut.`bog_status` LIKE '%$bog_status%' AND ut.`org_status` LIKE '%$org_status%' AND ut.account_from = {$account_id} AND `ut`.`offset_account` = '{$offset_account}' ";
            }else{
                $condition .= " AND ut.`bog_status` LIKE '%$bog_status%' AND ut.`org_status` LIKE '%$org_status%' AND ut.account_from = {$account_id} AND `ut`.`offset_account` = '' ";
            } 
        }
        
        if($result_per_page === "all"){
            $result_per_page = 18446744073709551615;
        }

        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->rawQuery("SELECT ut.id as ut_id, ut.`intval`, ut.`softdelete`, ut.account_from, ut.bog_status,ut.org_status, ut.offset_account, ut.statement_id, ut.time, st.post_date, st.reference, st.debit_amount, st.id AS statement_id, st.particulars, st.credit_amount, st.value_date FROM `unauthorized_transfers` AS ut LEFT JOIN `statements` AS st ON st.id = ut.statement_id $condition Order By `st`.`post_date` DESC");
        $this->paging->execute();
        $this->paging->reset();
        // var_dump($this->paging);exit;
        $result = $this->paging->results();
        if(!empty($result)){
            foreach ($result as $key => &$value) {
                $value->amount = number_format($value->debit_amount,2);
                $value->debit_amount = number_format($value->debit_amount,2);
                $value->credit_amount = number_format($value->credit_amount,2);
            }
            $response['success'] = true;
            $response["unauthorized"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = Msg::$no_bog_unauthorized_account;
        }

        $this->db->query("SELECT name,acc_num1,acc_num2,status,created FROM `accounts` WHERE `id`={$account_id}");   
        if($this->db->results() && $this->db->count > 0){
            $acct = $this->db->first();
            $response['main_account'] = array(
                "name"=> $acct->name,
                "status"=> $acct->status,
                "acc_num1"=> $acct->acc_num1,
                "acc_num2"=> $acct->acc_num2,
                "created"=> $acct->created
            );
        }
        $response["pagination"] = $this->paging->paging();
        return $response;
    }

    public function reciver_transactions(){
        $response = array();
        $response['main_account'] = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        $user_id = $this->http->json->user_id??null;
        $org_status = $this->http->json->org_status??null;
        $bog_status = $this->http->json->bog_status??null;
        $access_type = $this->http->json->access_type??null;
        $isbog = $this->http->json->isbog??null;
        $condition = "";

        if ($user_id == null) {
            $this->http->_403("Provide account id");
        }
        
        if($bog_status === "all"){
            $bog_status = "";
        }

        if($org_status === "all"){
            $org_status = "";
        }

        $account_id = $this->http->json->account_id??null;
        $offset_account = $this->http->json->offset_account??null;
        if($search){
            $condition = "WHERE `st`.`reference` LIKE '%$search%' ";
        }
        
        if (isset($isbog) && !empty($isbog) && $isbog==="true") {
            if (!empty($condition)) {
                $condition = " AND `ut`.`escalated_to_bog`=1 ";
            } else {
                $condition = " WHERE `ut`.`escalated_to_bog`=1 ";
            }
        }

        //introducr user access level `softdelete`=0
        if (!empty($condition)) {
            $condition .= " AND ut.`softdelete`=0";
        } else {
            $condition = " WHERE ut.`softdelete`=0";
        }

        if(empty($condition)){
            $condition = " WHERE ut.`bog_status` LIKE '%$bog_status%' AND ut.`org_status` LIKE '%$org_status%' AND ut.account_from = {$account_id} ";
        }else{
            $condition .= " AND ut.`bog_status` LIKE '%$bog_status%' AND ut.`org_status` LIKE '%$org_status%' AND ut.account_from = {$account_id} ";
        }
        
        if($result_per_page === "all"){
            $result_per_page = 18446744073709551615;
        }

        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->rawQuery("SELECT ut.id as ut_id, ut.`intval`, ut.`softdelete`, ut.account_from, ut.bog_status,ut.org_status, ut.offset_account, ut.statement_id, ut.time, st.post_date, st.reference, st.debit_amount, st.id AS statement_id FROM `unauthorized_transfers` AS ut LEFT JOIN `statements` AS st ON st.id = ut.statement_id $condition Order By `ut`.`statement_id` DESC");
        $this->paging->execute();
        $this->paging->reset();
        // var_dump($this->paging);exit;
        $result = $this->paging->results();
        if(!empty($result)){
            foreach ($result as $key => &$value) {
                $value->amount = number_format($value->debit_amount,2);
            }
            $response['success'] = true;
            $response["unauthorized"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = Msg::$no_bog_unauthorized_account;
        }

        $this->db->query("SELECT name,acc_num1,acc_num2,status FROM `accounts` WHERE `id`={$account_id}");   
        if($this->db->results() && $this->db->count > 0){
            $acct = $this->db->first();
            $response['main_account'] = array(
                "name"=> $acct->name,
                "status"=> $acct->status,
                "acc_num1"=> $acct->acc_num1,
                "acc_num2"=> $acct->acc_num2
            );
        }
    
        $response["pagination"] = $this->paging->paging();
        return $response;
    }

    public function response_transactions()
    {
        $stmid = $this->http->json->stmid;
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $user_id = $this->http->json->user_id??null;
        $access_type = $this->http->json->access_type??null;
        $isbog = $this->http->json->isbog??null;
        $account_id = $this->http->json->account_id??null;
        $offset_account = $this->http->json->offset_account??null;
        $condition = "";

        //introducr user access level `softdelete`=0
        if (!empty($condition)) {
            $condition .= " AND ut.`softdelete`=0";
        } else {
            $condition = " WHERE ut.`softdelete`=0";
        }

        if(empty($condition)){
            $condition = " WHERE ut.account_from = {$account_id} ";
        }else{
            $condition .= " AND ut.account_from = {$account_id} ";
        }
        
        $this->paging->rawQuery("SELECT ut.id as ut_id, ut.escalated_to_bog, ut.`intval`, ut.`softdelete`, ut.account_from, ut.bog_status,ut.org_status, ut.offset_account, ut.statement_id, ut.time, st.post_date, st.balance, st.reference,st.account_id, st.debit_amount, st.id AS statement_id, st.particulars, st.credit_amount, st.value_date FROM `unauthorized_transfers` AS ut LEFT JOIN `statements` AS st ON st.id = ut.statement_id $condition Order By `ut`.`statement_id` DESC");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
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

                $this->db->query("SELECT name,acc_num1,acc_num2,owner,bank,status FROM `accounts` WHERE `id` = {$value->account_id} LIMIT 1");
                if($this->db->results() && $this->db->count > 0){
                    $account = $this->db->first();
                    $value->name = $account->name;
                    $value->acc_num1 = $account->acc_num1;
                    $value->acc_num2 = $account->acc_num2;
                    $value->status = $account->status;

                    $this->db->query("SELECT name FROM `organizations` WHERE `id`={$account->owner} LIMIT 1");
                    if($this->db->results() && $this->db->count >0){
                        $organization = $this->db->first();
                        $value->owner = $organization->name;
                    }else{
                        $value->owner = '';
                    }
    
                    $this->db->query("SELECT name FROM `banks` WHERE `id`={$account->bank} LIMIT 1");
                    if($this->db->results() && $this->db->count >0){
                        $bank = $this->db->first();
                        $value->bank = $bank->name;
                    }else{
                        $value->bank = '';
                    }
                }

                $this->db->query("SELECT `c`.*, `u`.`fullname` FROM `comments` AS c INNER JOIN `users` AS u ON `c`.`user_id`=`u`.`id` WHERE `c`.`statement_id`={$value->statement_id} ");
                $value->reviews = $this->db->results();
            }
            $response["transactions"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = Msg::$no_bank_statement;
        }
        $response["pagination"] = $this->paging->paging();
        return $response;
    }

    public function get()
    {
        $stmid = $this->http->json->stmid;
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $user_id = $this->http->json->user_id??null;
        $access_type = $this->http->json->access_type??null;
        $isbog = $this->http->json->isbog??null;
        $account_id = $this->http->json->account_id??null;
        $offset_account = $this->http->json->offset_account??null;
        $condition = "";

        //donot select hidden records
        if (!empty($condition)) {
            $condition .= " AND ut.`softdelete`=0";
        } else {
            $condition = " WHERE ut.`softdelete`=0";
        }

        if(empty($condition)){
            if(!empty($offset_account)){
                $condition = " WHERE ut.account_from = {$account_id} AND `ut`.`offset_account` = '{$offset_account}' ";
            }else{
                $condition = " WHERE ut.account_from = {$account_id} AND `ut`.`offset_account` = '' ";
            }
        }else{
            if(!empty($offset_account)){
                $condition .= " AND ut.account_from = {$account_id} AND `ut`.`offset_account` = '{$offset_account}' ";
            }else{
                $condition .= " AND ut.account_from = {$account_id} AND `ut`.`offset_account` = '' ";
            } 
        }

        $this->paging->rawQuery("SELECT ut.id as ut_id, ut.escalated_to_bog, ut.`intval`, ut.`softdelete`, ut.account_from, ut.bog_status,ut.org_status, ut.offset_account, ut.statement_id, ut.time, st.post_date, st.balance, st.reference,st.account_id, st.debit_amount, st.id AS statement_id, st.particulars, st.credit_amount, st.value_date FROM `unauthorized_transfers` AS ut LEFT JOIN `statements` AS st ON st.id = ut.statement_id $condition Order By `st`.`post_date` DESC");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
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

                $this->db->query("SELECT name,acc_num1,acc_num2,owner,bank,status FROM `accounts` WHERE `id` = {$value->account_id} LIMIT 1");
                if($this->db->results() && $this->db->count > 0){
                    $account = $this->db->first();
                    $value->name = $account->name;
                    $value->acc_num1 = $account->acc_num1;
                    $value->acc_num2 = $account->acc_num2;
                    $value->status = $account->status;

                    $this->db->query("SELECT name FROM `organizations` WHERE `id`={$account->owner} LIMIT 1");
                    if($this->db->results() && $this->db->count >0){
                        $organization = $this->db->first();
                        $value->owner = $organization->name;
                    }else{
                        $value->owner = '';
                    }
    
                    $this->db->query("SELECT name FROM `banks` WHERE `id`={$account->bank} LIMIT 1");
                    if($this->db->results() && $this->db->count >0){
                        $bank = $this->db->first();
                        $value->bank = $bank->name;
                    }else{
                        $value->bank = '';
                    }
                }

                $this->db->query("SELECT `c`.*, `u`.`fullname` FROM `comments` AS c INNER JOIN `users` AS u ON `c`.`user_id`=`u`.`id` WHERE `c`.`statement_id`={$value->statement_id} ");
                $value->reviews = $this->db->results();
            }
            $response["transactions"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = Msg::$no_bank_statement;
        }
        $response["pagination"] = $this->paging->paging();
        return $response;
    }

    public function submitresponse(){
        $stmid = $this->http->json->stmid??null;
        $file = $this->http->json->file??null;
        $reviewedby = $this->http->json->reviewedby??null;
        $account_name = $this->http->json->account_name??null;
        $account_number = $this->http->json->account_number??null;
        $review_type = $this->http->json->review_type??null;
        $user_id = $this->http->json->user_id??null;
        $account_from = $this->http->json->account_from??null;
        $reference_no = $this->http->json->reference_no??null;
        $date = $this->http->json->date??null;
        $comments = $this->http->json->comments??null;

        $data = array( 
            'statement_id' =>  $stmid,
            'file' =>  $file,
            'reviewedby' =>  $reviewedby,
            'account_name' =>  $account_name,
            'account_number' =>  $account_number,
            'type' =>  $review_type,
            'user_id' =>  $user_id,
            'account_from' =>  $account_from,
            'reference_no' =>  $reference_no,
            'date' =>  $date,
            'comment' =>  $comments,
        );
        $done = $this->db->insert("comments", $data);
        if($done){
            if($reviewedby==="bog"){
                $this->db->query("UPDATE `unauthorized_transfers` SET `bog_status` = 'reviewed' WHERE `statement_id`={$stmid}");
            }else{
                $this->db->query("UPDATE `unauthorized_transfers` SET `org_status` = 'reviewed' WHERE `statement_id`={$stmid}");
            }
            $this->db->query("SELECT `c`.*, `u`.`fullname` FROM `comments` AS c INNER JOIN `users` AS u ON `c`.`user_id`=`u`.`id` WHERE `c`.`statement_id`={$stmid} ");
            $response["reviews"] = $this->db->results();

            $response['success'] = true;
            $response['message'] = "Response recieved successfully ";
            return $response;
        }else {
            $this->http->_500("Response could not be submited");
        }
    }

    public function updateresponse(){
        $id = $this->http->json->id??null;
        $stmid = $this->http->json->stmid??null;
        $file = $this->http->json->file??null;
        $reviewedby = $this->http->json->reviewedby??null;
        $account_name = $this->http->json->account_name??null;
        $account_number = $this->http->json->account_number??null;
        $review_type = $this->http->json->review_type??null;
        $user_id = $this->http->json->user_id??null;
        $account_from = $this->http->json->account_from??null;
        $reference_no = $this->http->json->reference_no??null;
        $date = $this->http->json->date??null;
        $comments = $this->http->json->comments??null;

        $data = array( 
            'statement_id' =>  $stmid,
            'file' =>  $file,
            'reviewedby' =>  $reviewedby,
            'account_name' =>  $account_name,
            'account_number' =>  $account_number,
            'type' =>  $review_type,
            'user_id' =>  $user_id,
            'account_from' =>  $account_from,
            'reference_no' =>  $reference_no,
            'date' =>  $date,
            'comment' =>  $comments,
        );
        $done = $this->db->updateByID("comments","id", $id, $data);
        if($done){
            if($reviewedby==="bog"){
                $this->db->query("UPDATE `unauthorized_transfers` SET `bog_status` = 'reviewed' WHERE `statement_id`={$stmid}");
            }else{
                $this->db->query("UPDATE `unauthorized_transfers` SET `org_status` = 'reviewed' WHERE `statement_id`={$stmid}");
            }
            $response['success'] = true;
            $response['message'] = "Response recieved successfully ";
            return $response;
        }else {
            $this->http->_500("Response could not be submited");
        }
    }

    public function upload_file(){
        $response = $this->files->upload_singleFile($this->http->files);
        return $response;
    }

    public function delete_file(){
        $response = array();
        if (isset($this->http->json->oldfile) && !empty($this->http->json->oldfile)) {
            $this->files->delete_file($this->http->json->oldfile);
            $response['success'] = true;
            $response['message'] = "File removed succefully";
        } else {
            $response['success'] = false;
            $response['message'] = "Error removing file";
        }
        return $response;
    }

    public function remove_reconcilation(){
        $response = array();
        $ids = implode(',', $this->http->json->id);
        $done = $this->db->query("Call rm_bank_reconcilation('$ids');");
        $response['success'] = true;
        $response['message'] = "Reconcilation removed succefully";
        return $response;
    }

    public function send_to_bog(){
        $response = array();
        $acc_from = $this->http->json->acc_id;
        $stmid = $this->http->json->stmid;
        $done = $this->db->query("UPDATE `unauthorized_transfers` SET `bog_status`='pending',`escalated_to_bog` = '1' WHERE `account_from`= $acc_from AND `statement_id` = $stmid;");
        if ($done) {
            $response['success'] = true;
            $response['message'] = "Infraction sent succefully";
        } else {
            $response['success'] = false;
            $response['message'] = "Error sending infraction";
        }
        return $response;
    }

    public function send_all_to_bog(){
        $response = array();
        $id = $this->http->json->acc_id;
        $acc_from = implode(',', array_map('intval', $id));
        $done = $this->db->query("UPDATE `unauthorized_transfers` SET `bog_status`='pending',`escalated_to_bog` = '1' WHERE `account_from` IN ($acc_from);update unauthorized_transfers set unauthorized_transfers.bog_status = 'reviewed' WHERE id IN (SELECT unauthorized_transfers.id FROM comments inner join unauthorized_transfers ON unauthorized_transfers.statement_id = comments.statement_id WHERE unauthorized_transfers.escalated_to_bog = 1);update unauthorized_transfers set unauthorized_transfers.bog_status = 'reviewed' WHERE id IN (SELECT unauthorized_transfers.id FROM comments inner join unauthorized_transfers ON unauthorized_transfers.statement_id = comments.statement_id WHERE unauthorized_transfers.escalated_to_bog = 1 AND comments.reviewedby='bog')");
        if ($done) {
            $response['success'] = true;
            $response['message'] = "Infractions sent succefully";
        } else {
            $response['success'] = false;
            $response['message'] = "Error sending infractions";
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

    public function hide(){
        $id = $this->http->json->id;
        $ids = implode(',', array_map('intval', $id));
        $done = $this->db->query("UPDATE `unauthorized_transfers` SET `softdelete`=1 WHERE statement_id IN ($ids)");
        if ($done) {
            $response['success'] = true;
            $response['message'] = "hidden successfully";
        }else{
            $response['success'] = false;
            $response['message'] = "Failed to hide statement";
        }
        return $response;
    }

    public function unhide(){
        $id = $this->http->json->id;
        $ids = implode(',', array_map('intval', $id));
        $done = $this->db->query("UPDATE `unauthorized_transfers` SET `softdelete`=0 WHERE id IN ($ids)");
        if ($done) {
            $response['success'] = true;
            $response['message'] = "unhidden successfully";
        }else{
            $response['success'] = false;
            $response['message'] = "Failed to unhide statement";
        }
        return $response;
    }

    public function group_hide(){
        $account_id = $this->http->json->account_id;
        $offset_account = $this->http->json->offset_account;
        
        $done = $this->db->query("UPDATE `unauthorized_transfers` SET `softdelete`=1 WHERE `account_from`=$account_id AND `offset_account`='$offset_account' ");
        if ($done) {
            $response['success'] = true;
            $response['message'] = "hidden successfully";
        }else{
            $response['success'] = false;
            $response['message'] = "Failed to hide statement";
        }
        return $response;
    }

    public function group_unhide(){
        $account_id = $this->http->json->account_id;
        $offset_account = $this->http->json->offset_account;
        
        $done = $this->db->query("UPDATE `unauthorized_transfers` SET `softdelete`=0 WHERE `account_from`=$account_id AND `offset_account`='$offset_account' ");
        if ($done) {
            $response['success'] = true;
            $response['message'] = "unhidden successfully";
        }else{
            $response['success'] = false;
            $response['message'] = "Failed to unhide statement";
        }
        return $response;
    }
}
