<?php
class BankaccountsModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }
    

    public function get()
    {
        $id = $this->http->json->id;
        return $this->getBankAccounts("WHERE `id`=$id");
    }

    public function reconcile()
    {
        $response = array();
        $id = $this->http->json->bid??0; //id of current bank
        // var_dump("id is here ".$id);
        //make a query to choose banks excluding the current bank.
        //if bank is bog/bank of ghana list all similer banks
        $this->db->query("SELECT bank_type FROM `accounts` WHERE `id`=$id");
        if ($this->db->results() && $this->db->count > 0) {
            $account = $this->db->first();
            if (strtolower($account->bank_type) === "bank of ghana") {
                return $this->getBankAccounts("WHERE (`id`!=$id AND `bank_type` = 'Bank Of Ghana') ");
            } else {
                return $this->getBankAccounts("WHERE `id`!=$id");
            }
        } else {
            return $this->getBankAccounts("WHERE `id`!=$id ");
        }
    }

    public function options_otherbanks()
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??40;
        $search = $this->http->json->search??null;
        $condition = "WHERE (`name` NOT LIKE '%bank of ghana%' AND `name` NOT LIKE '%bog%') ";
        if ($search) {
            $condition = " AND `name` LIKE '%$search%' Order by `name` ";
        }
        $this->paging->table('banks');
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum(1);
        $this->paging->condition("$condition Order By `name`");
        $this->paging->execute();
        $this->paging->reset();
            
        $results = $this->paging->results();
        if (!empty($results)) {
            foreach ($results as $data) {
                $options = array();
                $options['value'] = $data->id;
                $options['label'] = $data->name;
                array_push($response, $options);
            }
        }
        return $response;
    }

    public function options_owners()
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??40;
        $search = $this->http->json->search??null;
        $condition = "";
        if ($search) {
            $condition = " WHERE `name` LIKE '%$search%'";
        }
        $this->paging->table('organizations');
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum(1);
        $this->paging->condition("$condition Order By `name`");
        $this->paging->execute();
        $this->paging->reset();
            
        $results = $this->paging->results();
        if (!empty($results)) {
            foreach ($results as $data) {
                $options = array();
                $options['value'] = $data->id;
                $options['label'] = $data->name;
                array_push($response, $options);
            }
        }
        return $response;
    }

    public function options_category()
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??40;
        $search = $this->http->json->search??null;
        $condition = "";
        if ($search) {
            $condition = " WHERE `name` LIKE '%$search%' ";
        }
        $this->paging->table('account_category');
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum(1);
        $this->paging->condition("$condition Order By `name`");
        $this->paging->execute();
        $this->paging->reset();
            
        $results = $this->paging->results();
        if (!empty($results)) {
            foreach ($results as $data) {
                $options = array();
                $options['value'] = $data->id;
                $options['label'] = $data->name;
                array_push($response, $options);
            }
        }
        return $response;
    }

    public function bogbank()
    {
        $user_id = $this->http->json->user_id??null;
        $access_type = $this->http->json->access_type??null;
        $response = array();
        $this->db->query("SELECT id,name FROM `banks` WHERE (`name` LIKE '%bank of ghana%' OR `name` LIKE '%bog%')  Order by `name` LIMIT 1");
        if ($this->db->results() && $this->db->count > 0) {
            $data = $this->db->first();
            $accounts['value'] = $data->id;
            $accounts['label'] = $data->name;
            $response = $accounts;
        }
        return $response;
    }

    public function index($condition="")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        $bank_type = $this->http->json->bank_type??null;
        $bank_name = $this->http->json->bank_name??null;
        $user_id = $this->http->json->user_id??null;
        $access_type = $this->http->json->access_type??null;
        $filter_category = $this->http->json->filter_category??null;

        $bid = $this->http->json->bid??null;
        if ($search) {
            $condition = "WHERE ( `name` LIKE '%$search%' OR  `acc_num1` LIKE '%$search%' OR  `acc_num2` LIKE '%$search%'  OR `status` LIKE '%$search%')";
        }

        if ($bank_type) {
            if ($bank_type!=="all") {
                if (empty($condition)) {
                    $condition = "WHERE `bank_type` LIKE '%$bank_type%' ";
                } elseif (!empty($condition)) {
                    $condition .=  " AND `bank_type` LIKE '%$bank_type%' " ;
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
                        $condition = "WHERE `bank` IN ($ids) ";
                    } elseif (!empty($condition)) {
                        $condition .=  " AND (`bank` IN ($ids)) " ;
                    }
                }
                // var_dump($condition);exit;
            }
        }

        if (strtolower($access_type)!=="admin") {
            $institute = 0;
            $this->db->query("SELECT organization FROM `users` WHERE `id`={$user_id}");
            // var_dump( $this->db);exit;
            if ($this->db->results() && $this->db->count > 0) {
                $user = $this->db->first();
                $institute = $user->organization;
            }
            //bank, accounts, unauthorized_transaers
            //user belongs to organization which is equal to institute
            //institute = account.owner
            if ($institute!=0) {
                if (!empty($condition)) {
                    $condition .= " AND `owner` = $institute ";
                } else {
                    $condition = " WHERE `owner` = $institute ";
                }
            }
        }
        
        if ($filter_category) {
            if ($filter_category!=="all") {
                if (empty($condition)) {
                    $condition = "WHERE `category` =  $filter_category ";
                } elseif (!empty($condition)) {
                    $condition .=  " AND (`category` =  $filter_category) " ;
                }
            }
        }
        
        if ($result_per_page === "all") {
            $result_per_page = 18446744073709551615;
        }

        // $this->paging->table("`accounts`");
        $this->paging->rawQuery("SELECT (SELECT name FROM `organizations` WHERE `id`=ac.owner LIMIT 1) as owner_name, (SELECT name FROM `banks` WHERE `id`=ac.bank LIMIT 1) as bank_name, (SELECT amount FROM `account_balance` WHERE `account_id`=ac.id LIMIT 1) as account_balance, (SELECT date FROM `account_balance` WHERE `account_id`=ac.id LIMIT 1) as post_date, ac.id, ac.name,ac.acc_num1,ac.acc_num2,ac.status, ac.date_inactive  FROM `accounts` as ac $condition Order By `name`");
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
                $value->account_balance = number_format($value->account_balance, 2);

                if (!empty($this->http->json->id)) {
                    $value->canreconcile = false;
                    $this->db->query("SELECT id FROM `statements` WHERE `account_id`={$value->id} AND `locked_to` = 0");
                    if ($this->db->results() && $this->db->count >0) {
                        $value->canreconcile = true;
                    }
                }
                if ($this->date->isValidDate($value->date_inactive)) {
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

    public function getBankAccounts($condition="")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        $bank_type = $this->http->json->bank_type??null;
        $bank_name = $this->http->json->bank_name??null;
        $filter_category = $this->http->json->filter_category??null;

        $bid = $this->http->json->bid??null;
        if ($search) {
            $condition = "WHERE ( `name` LIKE '%$search%' OR  `acc_num1` LIKE '%$search%' OR  `acc_num2` LIKE '%$search%'  OR `status` LIKE '%$search%')";
            //ac.
        }

        if ($bank_type) {
            if ($bank_type!=="all") {
                if (empty($condition)) {
                    $condition = "WHERE `bank_type` LIKE '%$bank_type%' ";//ac.bank_type
                } elseif (!empty($condition)) {
                    $condition .=  " AND `bank_type` LIKE '%$bank_type%' " ;//ac.bank_type
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
                        $condition = "WHERE `bank` IN ($ids) ";//ac.bank
                    } elseif (!empty($condition)) {
                        $condition .=  " AND (`bank` IN ($ids)) " ;//ac.bank
                    }
                }
            }
        }
        
        if ($filter_category) {
            if ($filter_category!=="all") {
                if (empty($condition)) {
                    $condition = "WHERE `category` =  $filter_category ";
                } elseif (!empty($condition)) {
                    $condition .=  " AND (`category` =  $filter_category) " ;
                }
            }
        }
        
        if ($result_per_page === "all") {
            $result_per_page = 18446744073709551615;
        }

        // $this->paging->table("`accounts`");
        $this->paging->rawQuery("SELECT (SELECT name FROM `organizations` WHERE `id`=ac.owner LIMIT 1) as owner_name, (SELECT name FROM `banks` WHERE `id`=ac.bank LIMIT 1) as bank_name, (SELECT amount FROM `account_balance` WHERE `account_id`=ac.id LIMIT 1) as account_balance, (SELECT date FROM `account_balance` WHERE `account_id`=ac.id LIMIT 1) as post_date,ac.id, ac.name,ac.acc_num1,ac.acc_num2,ac.status, ac.date_inactive  FROM `accounts` as ac $condition Order By `name`");
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
                $value->account_balance = number_format($value->account_balance, 2);
                $value->aschiled = false;
                if ($bid) {
                    $this->db->query("SELECT id FROM `asinged_account` WHERE `main_account`={$bid} AND `child_account`= {$value->id} LIMIT 1");
                    if ($this->db->results() && $this->db->count >0) {
                        $value->aschiled = true;
                    }
                }
                if ($this->date->isValidDate($value->date_inactive)) {
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
    
    public function editbank()
    {
        $response = array();
        $id = $this->http->json->id;

        $this->paging->table("`accounts`");
        $this->paging->result_per_page(1);
        $this->paging->pageNum(1);
        $this->paging->condition("WHERE `id`=$id");
        $this->paging->execute();
        $this->paging->reset();
        // var_dump($this->paging);exit;
        $result = $this->paging->results();
        if (!empty($result)) {
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $this->db->query("SELECT date,amount FROM `account_balance` WHERE `account_id`={$value->id} LIMIT 1");
                if ($this->db->results() && $this->db->count >0) {
                    $bank = $this->db->first();
                    $value->balance = number_format($bank->amount, 2);
                    $value->post_date = $bank->date;
                } else {
                    $value->balance = null;
                    $value->post_date = null;
                }
                
                $this->db->query("SELECT id,name FROM `organizations` WHERE `id`={$value->owner} LIMIT 1");
                if ($this->db->results() && $this->db->count >0) {
                    $user = $this->db->first();
                    $value->owner = array("value"=>$user->id,"label"=>$user->name);
                } else {
                    $value->owner = null;
                }

                $this->db->query("SELECT id,name FROM `banks` WHERE `id`={$value->bank} LIMIT 1");
                if ($this->db->results() && $this->db->count >0) {
                    $bank = $this->db->first();
                    $value->bank = array("value"=>$bank->id,"label"=>$bank->name);
                } else {
                    $value->bank = null;
                }

                $this->db->query("SELECT id,name FROM `account_category` WHERE `id`={$value->category} LIMIT 1");
                if ($this->db->results() && $this->db->count >0) {
                    $account_category = $this->db->first();
                    $value->category = array("value"=>$account_category->id,"label"=>$account_category->name);
                } else {
                    $value->category = null;
                }
                
                if ($this->date->isValidDate($value->date_inactive)) {
                    $value->date_inactive = $this->date->sql_date($value->date_inactive);
                }
            }
            $response["bankaccounts"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = Msg::$no_bank_account;
        }
        return $response;
    }
    
    public function create()
    {
        $acc_num1 = $this->http->json->acc_num1;
        $acc_num2 = $this->http->json->acc_num2;
        $name = $this->http->json->name;
        $bank_type = $this->http->json->bank_type;
        $bank = $this->http->json->bank;
        $category = $this->http->json->category??null;
        $owner = $this->http->json->owner;
        $num = !empty($acc_num1)?$acc_num1:$acc_num2;

        $this->db->query("SELECT * FROM `accounts` WHERE (`acc_num1` = '$num' OR `acc_num2` ='$num')");
        if ($this->db->results() && $this->db->count >0) {
            $this->http->_403(Msg::account_exist($num));
        }
        
        $data = array(
            'name' =>  $name,
            'owner' =>  $owner,
            'acc_num1' =>  $acc_num1,
            'acc_num2' =>  $acc_num2,
            'bank_type' =>  $bank_type,
            'bank' =>  $bank,
            'category' =>  $category,
        );
        if ($this->db->insert("accounts", $data)) {
            $response['success'] = true;
            $response['message'] = "Bank has been created";
            return $response;
        } else {
            $this->http->_500(Msg::$bank_create_error);
        }
    }
    
    public function update()
    {
        $id = $this->http->json->id;
        $date_inactive = $this->http->json->date_inactive??null;
        $acc_num1 = $this->http->json->acc_num1;
        $acc_num2 = $this->http->json->acc_num2;
        $name = $this->http->json->name;
        $bank_type = $this->http->json->bank_type;
        $bank = $this->http->json->bank;
        $category = $this->http->json->category??null;
        $owner = $this->http->json->owner;
        $status = $this->http->json->status;
        $num = !empty($acc_num1)?$acc_num1:$acc_num2;

        if ($status === "Inactive") {
            if (empty($date_inactive) || $date_inactive==="null") {
                $this->http->_403("Please provie date of inactivity");
            }
        }
        
        if ($status === "Active") {
            $date_inactive = null;
        }
        
        $data = array(
            'name' =>  $name,
            'owner' =>  $owner,
            'acc_num1' =>  $acc_num1,
            'acc_num2' =>  $acc_num2,
            'bank_type' =>  $bank_type,
            'status' =>  $status,
            'date_inactive' =>  $date_inactive,
            'bank' =>  $bank,
            'category' =>  $category,
        );
        
        if ($this->db->updateByID("accounts", "id", $id, $data)) {
            $response['success'] = true;
            $response['message'] = "Bank Account has been updated";
            return $response;
        } else {
            $this->http->_500(Msg::$bank_create_error);
        }
    }
    
    public function delete()
    {
        $response = array();
        $id = implode(',', array_map('intval', $this->http->json->id));
        $done = $this->db->query("DELETE FROM `accounts` WHERE `id` IN ($id);DELETE FROM `statements` WHERE `account_id` IN ($id)");
        if ($done) {
            $response['success'] = true;
            $response['message'] = "Record deleted successfully";
        } else {
            $response['success'] = false;
            $response['message'] = "Record could not be deleted ";
        }
        return $response;
    }

    public function empty_statement()
    {
        $response = array();
        $id = implode(',', array_map('intval', $this->http->json->id));
        $done = $this->db->query("DELETE FROM `statements` WHERE `account_id` IN ($id);UPDATE `account_balance` SET `amount` = '0.0', `date` = '0000-00-00' WHERE `account_balance`.`id` IN ($id)");
        if ($done) {
            $response['success'] = true;
            $response['message'] = "Record deleted successfully";
        } else {
            $response['success'] = false;
            $response['message'] = "Record could not be deleted ";
        }
        return $response;
    }
    
    public function import()
    {
        $excelData = array();

        $response = $this->files->upload_singleFile($this->http->files);
        if ($response['success'] === true) {
            if (false) {
                $this->files->delete_file($response['relative']);
            }
            //read excel files from link $response['relative']
            try {
                if ($this->db->updateByID("users", "id", $this->http->post->id, $data)) {
                    $response['success'] = true;
                    $response['message'] = "Successfully uploaded user image";
                } else {
                    $response['success'] = false;
                    $response['message'] = "Error Uploading user image";
                    $this->files->delete_file();
                }
            } catch (Exception $ex) {
                $response['success'] = false;
                $response['message'] = "Error (" . $ex->getMessage() . ")";
                $this->files->delete_file($response['relative']);
            }
        }
        return $response;
    }

    public function add_to_sub_account()
    {
        $ids = $this->http->json->id;
        $bank_type = $this->http->json->type;
        $account = $this->http->json->account;
        
        $num = count($ids);
        $all = array();
        foreach ($ids as $key => $id) {
            $data = array(
                'bank_type' =>  $bank_type,
                'child_account' =>  $id,
                'main_account' =>  $account,
            );
            $this->db->query("SELECT * FROM `asinged_account` WHERE `main_account`={$account} AND `child_account`= {$id} LIMIT 1");
            if (!$this->db->results()) {
                $done = $this->db->insert("asinged_account", $data);
                if ($done) {
                    array_push($all, true);
                } else {
                    array_push($all, false);
                }
            }
        }
        $response['success'] = true;
        $response['message'] = "Account(s) has been added successfullly";
        return $response;
    }

    public function remove_sub_account()
    {
        $bank_type = $this->http->json->type;
        $account = $this->http->json->account;
        $id = implode(',', array_map('intval', $this->http->json->id));
        
        $done = $this->db->query("DELETE FROM`asinged_account` WHERE `main_account`={$account} AND `child_account` IN ($id)");
        if ($done) {
            $response['success'] = true;
            $response['message'] = "Account(s) has been removed successfullly";
        } else {
            $response['success'] = true;
            $response['message'] = "Failed to remove account";
        }
        return $response;
    }

    public function start_reconcilation()
    {
        $account = $this->http->json->account;
        $user_id = $this->http->json->user_id;
        $interval = $this->http->json->interval??null;
        $transtype = $this->http->json->transtype??null;
        $category_group = $this->http->json->category_group;
        if (is_array($category_group)) {
            $this->db->query("SELECT bank_type,name,reconciled FROM `accounts` WHERE `id`={$account} LIMIT 1");
            if ($this->db->results() && $this->db->count > 0) {
                $mainAccount = $this->db->first();

                if ((int)$mainAccount->reconciled === 1) {
                    $response['success'] = false;
                    $response['message'] = "This account has already been reconciled";
                    return $response;
                }
                
                $category_groupIDS = implode(',', array_map('intval', $category_group));
                $this->db->query("SELECT id FROM `accounts` WHERE `category` IN ({$category_groupIDS})");
                $id = array();
                foreach ($this->db->results() as $key => $data) {
                    array_push($id, $data->id);
                }

                if (empty($id) || count($id) < 1) {
                    $response['success'] = false;
                    $response['message'] = "There are no accounts in the selected category";
                    return $response;
                }
                
                if ($mainAccount->bank_type=="Bank Of Ghana") {
                    $bank_type = "Bank Of Ghana";
                } else {
                    $bank_type = "Other Banks";
                }
                
                $ids = implode(',', array_map('intval', $id));

                $reconcileWith = "few";
                $jobid = "RJ".time();
                $data = array(
                    'jobid' =>  $jobid,
                    'user_id' =>  $user_id,
                    'bank_type' =>  $bank_type,
                    'account' =>  $account,
                    'ids' =>  $ids,
                    'intval' =>  $interval,
                    'transtype' =>  $transtype,
                    'reconcile_with' =>  $reconcileWith,
                    'reconciling_with' =>  "",
                    'proccessing_account' =>  0,
                    'total_account' =>  0,
                    'status' =>  "Initializing",
                    'description' =>  "reconcilation request queued",
                );
                $done = $this->db->insert("reconcilation_status", $data);
                if ($done) {
                    $this->db->updateByID("accounts", "id", $account, array("reconciled"=>1));
                    $response['success'] = true;
                    $response['jobid'] = $jobid;
                    $response['message'] = "reconcilation submitted";
                    return $response;
                } else {
                    $response['success'] = false;
                    $response['message'] = "Failed to initialize reconcilation";
                    return $response;
                }
            } else {
                $response['success'] = false;
                $response['message'] = "we could not identify the parent account";
                return $response;
            }
        } else {
            $this->db->query("SELECT reconciled FROM `accounts` WHERE `id`={$account} LIMIT 1");
            if ($this->db->results() && $this->db->count > 0) {
                $mainAccount = $this->db->first();
                if ((int)$mainAccount->reconciled === 1) {
                    $response['success'] = false;
                    $response['message'] = "This account has already been reconciled";
                    return $response;
                }
            }

            $this->db->query("SELECT child_account,bank_type FROM `asinged_account` WHERE `main_account`={$account}");
            $id = array();
            foreach ($this->db->results() as $key => $data) {
                array_push($id, $data->child_account);
                $bank_type = $data->bank_type;
            }

            if (empty($id) || count($id) < 1) {
                $response['success'] = false;
                $response['message'] = "There are no sub accounts to reconcile with";
                return $response;
            }
            $ids = implode(',', array_map('intval', $id));

            $reconcileWith = "few";
            $jobid = "RJ".time();
            $data = array(
                'jobid' =>  $jobid,
                'user_id' =>  $user_id,
                'bank_type' =>  $bank_type,
                'account' =>  $account,
                'ids' =>  $ids,
                'intval' =>  $interval,
                'transtype' =>  $transtype,
                'reconcile_with' =>  $reconcileWith,
                'reconciling_with' =>  "",
                'proccessing_account' =>  0,
                'total_account' =>  0,
                'status' =>  "Initializing",
                'description' =>  "reconcilation request queued",
            );
            $done = $this->db->insert("reconcilation_status", $data);
            if ($done) {
                $this->db->updateByID("accounts", "id", $account, array("reconciled"=>1));
                $response['success'] = true;
                $response['jobid'] = $jobid;
                $response['message'] = "reconcilation submitted";
                return $response;
            } else {
                $response['success'] = false;
                $response['message'] = "Failed to initialize reconcilation";
                return $response;
            }
        }
    }
    
    public function reconcilation_status()
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
}
