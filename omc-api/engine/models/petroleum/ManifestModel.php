<?php
class ManifestModel extends BaseModel
{
    private static $table = "petroleum_manifest";

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->manifest();
    }
    
    public function manifest($condition=" WHERE 1 ")
    {
        $table = self::$table;
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        if ($search) {
            $value = implode("", explode(",", $search));
            $condition .= " AND  (`vessel_name` LIKE '%$search%' OR `product_type` = (SELECT code FROM tax_schedule_products WHERE name LIKE '%$search%' LIMIT 1) OR `volume` LIKE '%$search%' OR `amount` LIKE '%$value%' OR `ucr_number` LIKE '%$search%' OR `exporter_name` LIKE '%$search%' OR `importer_name` = (SELECT code FROM bdc WHERE name LIKE '%$search%' LIMIT 1))";
        }
        $this->paging->rawQuery("SELECT *, (SELECT name FROM tax_schedule_products WHERE code=product_type LIMIT 1) product_type, (SELECT name FROM bdc WHERE code=importer_name LIMIT 1) importer_name FROM $table $condition Order By `id`");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();

        $result = $this->paging->results();
        if (!empty($result)) {
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $value->amount = number_format($value->amount, 2);
                $value->volume = number_format($value->volume, 1);
            }
            $response["manifest"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No manifest available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }

    public function import()
    {
        $response = $this->files->upload_singleFile($this->http->files);
        if ($response['success'] === true) {
            $data = array(
                'path' =>  $response['base_location'],
                'account_id' => 0,
                'bank_id' => 0,
                'type' =>  "manifest_imp",
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
                "details"=> "Importing data {$job->current} of {$job->total} ",
            );

            return $response;
        }
        $response['success'] = false;
        $response['message'] = "unknown jobid";
        return $response;
    }

    public function delete()
    {
        $response = array();
        $done = $this->db->query("TRUNCATE `".self::$table."`;");
        if ($done) {
            $response['success'] = true;
            $response['message'] = "Data removed successfully";
        } else {
            $response['success'] = false;
            $response['message'] = "Data could not be removed ";
        }
        return $response;
    }
}
