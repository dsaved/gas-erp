<?php
class OmcfalloutModel extends BaseModel
{
    private static $table = "omc_receipt";

    public function __construct()
    {
        parent::__construct();
    }
    
    public function index()
    {
        return $this->omcs(" WHERE omc_r.`status`= 'flagged'");
    }
    
    public function get()
    {
        $id = $this->http->json->id??null;
        if (empty($id)) {
            $this->http->_403("Tax type id is required");
        }
        return $this->omcs(" WHERE o.`id`= $id");
    }
    
    public function omcs($condition="")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        if ($search) {
            if (empty($condition)) {
                $condition = " WHERE (o.`name` LIKE '%$search%') ";
            } else {
                $condition .= " AND (o.`name` LIKE '%$search%') ";
            }
        }
        $this->paging->rawQuery("SELECT o.name,o.phone,o.email,o.location,o.region,o.district,o.id,SUM(omc_r.amount) as amount, COUNT(omc_r.id) as total FROM `omc_receipt`as omc_r LEFT JOIN `omc` as o ON o.id=omc_r.omc_id $condition GROUP BY o.name ORDER BY omc_r.`status` ASC ");
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
            $response["omcs"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No OMC Fallouts available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function receipts()
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $id = $this->http->json->id??null;
        $condition = "WHERE `omc_id`= $id AND status='flagged'";
        $search = $this->http->json->search??null;
        if ($search) {
            $condition .= " AND (`bank` LIKE '%$search%' OR `declaration_number` LIKE '%$search%'OR `receipt_number` LIKE '%$search%' OR `mode_of_payment` LIKE '%$search%' OR `amount` LIKE '%$search%' OR `status` LIKE '%$search%') ";
        }
        $this->paging->table("omc_receipt");
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

    public function receipt_omc()
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $id = $this->http->json->id??null;
        $omcid = $this->http->json->omcid??null;
        if (empty($id)) {
            $this->http->_403("Receipt id is required");
        }
        if (empty($omcid)) {
            $this->http->_403("OMC id is required");
        }

        $this->paging->rawQuery("SELECT ".self::$table.".*, omc.name,omc.phone,omc.location,omc.region,omc.email,omc.district FROM ".self::$table." JOIN omc ON ".self::$table.".omc_id = omc.id WHERE ".self::$table.".`status`= 'flagged' AND ".self::$table.".`omc_id`= $omcid Order By ".self::$table.".`id` DESC");
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

    public function start_export(){
        $filename = $this->http->json->filename;
        $id = $this->http->json->omc_id;
        $this->db->query("SELECT filename FROM `file_export_status` WHERE `filename`='$filename'");
        if($this->db->results() && $this->db->count > 0){
            $this->http->_403("File name already exixt");            
        }

        $jobid = "EXP".time();
        $data = array(
            'jobid' =>  $jobid,
            'filename' =>  $filename,
            'ids' =>  $id,
            'path' =>  ROOT,
            'export_type' =>  "OMC-FALLOUT",
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
