<?php
class PreorderModel extends BaseModel
{
  private static $table = "petroleum_preorder";

  public function __construct()
  {
      parent::__construct();
  }

  public function index()
  {
      return $this->preorders();
  }
  
  public function preorders($condition=" WHERE 1 ")
  {
      $response = array();
      $result_per_page = $this->http->json->result_per_page??20;
      $page = $this->http->json->page??1;
      $search = $this->http->json->search??null;
      if ($search) {
          $condition .= " AND  (`product_type` LIKE '%$search%' OR `reference_number` LIKE '%$search%' OR `volume` LIKE '%$search%' OR `depot` LIKE '%$search%' OR `bdc` LIKE '%$search%' OR `omc` LIKE '%$search%' )";
      }
      $this->paging->table(self::$table);
      $this->paging->result_per_page($result_per_page);
      $this->paging->pageNum($page);
      $this->paging->condition("$condition Order By `id`");
      $this->paging->execute();
      $this->paging->reset();

      $result = $this->paging->results();
      if (!empty($result)) {
          $response['success'] = true;
          $response["preorders"] = $result;
      } else {
          $response['success'] = false;
          $response['message'] = "No preorders available";
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
              'type' =>  "petroleum_preorder_imp",
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
