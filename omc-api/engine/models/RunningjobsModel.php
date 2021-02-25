<?php
class RunningjobsModel extends BaseModel
{

    function __construct() {
        parent::__construct();
    }
    
    public function index()
    {
        return $this->fileupload();
    }

    public function fileupload($condition=""){
        $response = array();
        $result_per_page = $this->http->json->result_per_page??18446744073709551615;
        $page = $this->http->json->page??1;
        
        // $this->paging->table("`auditslogs`");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->rawQuery("SELECT jobid,status,proccessing_account, total_account,description,processing,created FROM reconcilation_status UNION ALL SELECT id,status, current,total,description,processing,created FROM file_upload_status UNION ALL SELECT jobid, status,current_count,total_account,description,processing, created FROM file_export_status ORDER BY created DESC");
        $this->paging->execute();
        $this->paging->reset();
        // var_dump($this->paging);exit;
        $result = $this->paging->results();
        if(!empty($result)){
            $response['success'] = true;
            $response["runingjobs"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = Msg::$no_jobsavailable;
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }

    public function files($condition=""){
        $response = array();
        $result_per_page = $this->http->json->result_per_page??18446744073709551615;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        if($search){
            $condition = "WHERE (`filename` LIKE '%$search%' ) ";
        }
        
        // $this->paging->table("`auditslogs`");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->rawQuery("SELECT * FROM `file_download` $condition ORDER BY created DESC");
        $this->paging->execute();
        $this->paging->reset();
        // var_dump($this->paging);exit;
        $result = $this->paging->results();
        if(!empty($result)){
            $response['success'] = true;
            $response["files"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = Msg::$no_files_for_download;
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
}
