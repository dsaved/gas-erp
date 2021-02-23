<?php
class OmcModel extends BaseModel
{
    private static $table = "omc";

    public function __construct()
    {
        parent::__construct();
    }
    
    public function index()
    {
        return $this->omcs();
    }
    
    public function options()
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??40;
        $search = $this->http->json->search??null;
        $condition = "";
        if ($search) {
            $condition = " WHERE (`name` LIKE '%$search%') ";
        }
        $this->paging->table(self::$table);
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
    
    public function get()
    {
        $id = $this->http->json->id??null;
        if (empty($id)) {
            $this->http->_403("Tax type id is required");
        }
        return $this->omcs(" WHERE `id`= $id");
    }
    
    public function omcs($condition="")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        if ($search) {
            if (empty($condition)) {
                $condition .= " WHERE (`name` LIKE '%$search%' OR `email` LIKE '%$search%'OR `email` LIKE '%$search%' OR `region` LIKE '%$search%' OR `location` LIKE '%$search%' OR `district` LIKE '%$search%') ";
            } else {
                $condition .= " AND (`name` LIKE '%$search%' OR `email` LIKE '%$search%'OR `email` LIKE '%$search%' OR `region` LIKE '%$search%' OR `location` LIKE '%$search%' OR `district` LIKE '%$search%') ";
            }
        }
        $this->paging->table(self::$table);
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->condition("$condition Order By `name`");
        $this->paging->execute();
        $this->paging->reset();
        
        $result = $this->paging->results();
        if (!empty($result)) {
            $response['success'] = true;
            $response["omcs"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No OMC available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function addtax()
    {
        $email = $this->http->json->email??null;
        $phone = $this->http->json->phone??null;
        $location = $this->http->json->location??null;
        $district = $this->http->json->district??null;
        $region = $this->http->json->region??null;

        $name = $this->http->json->name??null;
        if ($name===null) {
            $this->http->_403("Please provide OMC name");
        }

        $this->db->query("SELECT * FROM ".self::$table." WHERE `name` = '$name'");
        if ($this->db->results() && $this->db->count >0) {
            $this->http->_403("This OMC name ($name) already exist");
        }

        $data = array(
            'name' => $name,
            "email"=>$email,
            "phone"=>$phone,
            "location"=>$location,
            "district"=>$district,
            "region"=>$region
        );
        if ($this->db->insert(self::$table, $data)) {
            $response['success'] = true;
            $response['message'] = "OMC has been created";
            return $response;
        } else {
            $this->http->_500("Error while creating OMC");
        }
    }
    
    public function updatetax()
    {
        $id = $this->http->json->id??null;
        if ($id===null) {
            $this->http->_403("Please provide OMC id");
        }
        $email = $this->http->json->email??null;
        $phone = $this->http->json->phone??null;
        $location = $this->http->json->location??null;
        $district = $this->http->json->district??null;
        $region = $this->http->json->region??null;

        $name = $this->http->json->name??null;
        if ($name===null) {
            $this->http->_403("Please provide OMC name");
        }

        $this->db->query("SELECT * FROM ".self::$table." WHERE `name` = '$name' AND `id`!=$id");
        if ($this->db->results() && $this->db->count >0) {
            $this->http->_403("This OMC name ($name) already exist");
        }

        $data = array(
            'name' => $name,
            "email"=>$email,
            "phone"=>$phone,
            "location"=>$location,
            "district"=>$district,
            "region"=>$region
        );
        if ($this->db->updateByID(self::$table, "id", $id, $data)) {
            $response['success'] = true;
            $response['message'] = "OMC has been updated";
            return $response;
        } else {
            $this->http->_500("Error while updating OMC");
        }
    }
    
    public function deletetax()
    {
        $response = array();
        $id = implode(',', array_map('intval', $this->http->json->id));
        $done = $this->db->query("DELETE FROM ".self::$table." WHERE `id` IN ($id)");
        if ($done) {
            $this->db->query("DELETE FROM `omc_receipt` WHERE `omc_id` = {$id}");
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
        $id = $this->http->post->id;
        $response = $this->files->upload_singleFile($this->http->files);
        if ($response['success'] === true) {
            $data = array(
                'path' =>  $response['base_location'],
                'account_id' => $id,
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
                "details"=> "Creating transaction {$job->current} of {$job->total} ",
            );

            return $response;
        }
        $response['success'] = false;
        $response['message'] = "unknown jobid";
        return $response;
    }

    //get accounts to select for reconcilation

    public function accounts()
    {
        return $this->getBankAccounts();
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

        $omcid = $this->http->json->omcid??null;
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
                if ($omcid) {
                    $this->db->query("SELECT id FROM `omc_mapped_account` WHERE `main_account`={$omcid} AND `child_account`= {$value->id} LIMIT 1");
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

    public function map()
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
            $this->db->query("SELECT * FROM `omc_mapped_account` WHERE `main_account`={$account} AND `child_account`= {$id} LIMIT 1");
            if (!$this->db->results()) {
                $done = $this->db->insert("omc_mapped_account", $data);
                if ($done) {
                    array_push($all, true);
                } else {
                    array_push($all, false);
                }
            }
        }
        $response['success'] = true;
        $response['message'] = "Account(s) has been mapped successfullly";
        return $response;
    }

    public function unmap()
    {
        $bank_type = $this->http->json->type;
        $account = $this->http->json->account;
        $id = implode(',', array_map('intval', $this->http->json->id));
        
        $done = $this->db->query("DELETE FROM`omc_mapped_account` WHERE `main_account`={$account} AND `child_account` IN ($id)");
        if ($done) {
            $response['success'] = true;
            $response['message'] = "Account(s) has been unmapped successfullly";
        } else {
            $response['success'] = true;
            $response['message'] = "Failed to remove account";
        }
        return $response;
    }

    public function start_reconcilation()
    {
        $account = $this->http->json->account;//omc id
        $user_id = $this->http->json->user_id;
        $interval = $this->http->json->interval??null;
        $category_group = $this->http->json->category_group;
        if ($category_group!=0 && $category_group!="0") {
            $this->db->query("SELECT reconciled FROM `omc` WHERE `id`={$account} LIMIT 1");
            if ($this->db->results() && $this->db->count > 0) {
                $mainAccount = $this->db->first();

                if ((int)$mainAccount->reconciled === 1) {
                    $response['success'] = false;
                    $response['message'] = "This omc has already been reconciled";
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
                
                $bank_type = "omc";
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
                    'reconcile_with' =>  $reconcileWith,
                    'reconciling_with' =>  "",
                    'proccessing_account' =>  0,
                    'total_account' =>  0,
                    'status' =>  "Initializing",
                    'description' =>  "reconcilation request queued",
                );
                $done = $this->db->insert("reconcilation_status", $data);
                if ($done) {
                    $this->db->updateByID("omc", "id", $account, array("reconciled"=>1));
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
                $response['message'] = "we could not identify the omc";
                return $response;
            }
        } else {
            $this->db->query("SELECT reconciled FROM `omc` WHERE `id`={$account} LIMIT 1");
            if ($this->db->results() && $this->db->count > 0) {
                $mainAccount = $this->db->first();
                if ((int)$mainAccount->reconciled === 1) {
                    $response['success'] = false;
                    $response['message'] = "This omc has already been reconciled";
                    return $response;
                }
            }

            $this->db->query("SELECT child_account,bank_type FROM `omc_mapped_account` WHERE `main_account`={$account}");
            $id = array();
            foreach ($this->db->results() as $key => $data) {
                array_push($id, $data->child_account);
                $bank_type = $data->bank_type;
            }

            if (empty($id) || count($id) < 1) {
                $response['success'] = false;
                $response['message'] = "There are no mapped accounts to reconcile with";
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
                'reconcile_with' =>  $reconcileWith,
                'reconciling_with' =>  "",
                'proccessing_account' =>  0,
                'total_account' =>  0,
                'status' =>  "Initializing",
                'description' =>  "reconcilation request queued",
            );
            $done = $this->db->insert("reconcilation_status", $data);
            if ($done) {
                $this->db->updateByID("omc", "id", $account, array("reconciled"=>1));
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

    public function empty_receipts()
    {
        $response = array();
        $id = implode(',', array_map('intval', $this->http->json->id));
        $done = $this->db->query("DELETE FROM `omc_receipt` WHERE `omc_id` IN ($id)");
        if ($done) {
            $response['success'] = true;
            $response['message'] = "Receipts removed successfully";
        } else {
            $response['success'] = false;
            $response['message'] = "Receipts could not be removed ";
        }
        return $response;
    }
}
