<?php
class OptionsModel extends BaseModel
{
    private static $tbl_banks = "banks";

    public function __construct()
    {
        parent::__construct();
    }
    
    public function banks_options()
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??40;
        $search = $this->http->json->search??null;
        $condition = "";
        if ($search) {
            $condition = " WHERE (`name` LIKE '%$search%') ";
        }
        $this->paging->table(self::$tbl_banks);
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

    public function start_export(){
        $filename = $this->http->json->filename;
        $export_type = $this->http->json->export_type;
        $config_data = $this->http->json->config_data;
        $this->db->query("SELECT filename FROM `file_export_status` WHERE `filename`='$filename'");
        if($this->db->results() && $this->db->count > 0){
            $this->http->_403("File name already exixt");            
        }

        $jobid = "EXP".time();
        $data = array(
            'jobid' =>  $jobid,
            'filename' =>  $filename,
            'ids' =>  $config_data,
            'path' =>  ROOT,
            'export_type' =>  $export_type,
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
