<?php
class WaybillsModel extends BaseModel
{
    private static $table = "petroleum_waybill";
    private static $petroleum_outlet = "petroleum_outlet";
    private static $table_manifest = "petroleum_manifest";
  
    public function __construct()
    {
        parent::__construct();
    }
  
    public function index()
    {
        return $this->waybills();
    }
      
    public function waybills($condition=" WHERE 1 ")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $search = $this->http->json->search??null;
        if ($search) {
            $condition .= " AND  (`depot` LIKE '%$search%' OR `bdc` LIKE '%$search%' OR `omc` LIKE '%$search%' OR `transporter` LIKE '%$search%' OR `product_type` LIKE '%$search%' OR `volume` LIKE '%$search%' OR `driver` LIKE '%$search%' OR `vehicle_number` LIKE '%$search%' OR `destination` LIKE '%$search%' )";
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
            foreach ($result as $key => &$value) {
                $value->volume = number_format($value->volume, 2);
            }
            $response["waybills"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No waybills available";
        }
  
        $response["pagination"] = $this->paging->paging();
        return $response;
    }

    public function reconcile($condition = " WHERE 1 ")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;

        $bdc = $this->http->json->bdc??null;
        $omc = $this->http->json->omc??null;
        $product_type = $this->http->json->product_type??null;
        $depot = $this->http->json->depot??null; //on declaration table
        $group_by = $this->http->json->group_by??null;
        $date_range = $this->http->json->date_range??null;
        $status = $this->http->json->status??null;
        $nagatives = $this->http->json->nagatives??null;

        if ($date_range) {
            if ($date_range->endDate && $date_range->startDate) {
                $startDate = $this->date->sql_date($date_range->startDate);
                $endDate = $this->date->sql_date($date_range->endDate);
                $condition .= " AND (waybill.date  BETWEEN '$startDate' AND '$endDate') ";
            }
        }

        if ($bdc && $bdc!="All") {
            $condition .= " AND waybill.bdc = '$bdc'";
        }

        if ($product_type && $product_type!="All") {
            $condition .= " AND waybill.product_type = '$product_type'";
        }
  
        if ($depot && $depot!="All") {
            $condition .= " AND waybill.depot ='$depot'";
        }

        if ($omc && $omc!="All") {
            $condition .= " AND waybill.omc = '$omc'";
        }

        $grouping = "waybill.date";
        if ($group_by) {
            if ($group_by==="Month") {
                $grouping = " CONCAT(YEAR(waybill.date), '/', MONTH(waybill.date))";
            }elseif ($group_by ==="Week") {
                $grouping = " CONCAT(YEAR(waybill.date), '/', WEEK(waybill.date))";
            }elseif ($group_by==="Day") {
                $grouping = " DATE(waybill.date)";
            }else{
                $grouping = " DATE(waybill.date)";
            }
            $group_by = "Group By $grouping ";
        } else {
            $group_by = "Group By waybill.date";
        }

        if ($status && $status!="All") {
            if ($status==="Flagged") {
                $condition .= " AND (waybill.volume <> outlet.volume OR outlet.volume IS NULL)";
            }
            if ($status==="Not Flagged") {
                $condition .= " AND waybill.volume = outlet.volume ";
            }
        }

        if ($nagatives && $nagatives!="All") {
            if ($nagatives==="Nagatives") {
                $condition .= " AND (waybill.volume - outlet.volume  < 0)";
            }
            if ($nagatives==="Positives") {
                $condition .= " AND (waybill.volume - outlet.volume  >= 0)";
            }
        }
        
        $query = "SELECT MIN(waybill.id) id, $grouping as grouping, waybill.depot, waybill.product_type, SUM(waybill.volume) waybill_volume, SUM(outlet.volume) outlet_volume, waybill.bdc bdc FROM ".self::$table." waybill JOIN ".self::$petroleum_outlet." outlet ON outlet.bdc = waybill.bdc AND outlet.depot = waybill.depot AND outlet.product_type = waybill.product_type $condition $group_by, waybill.bdc, waybill.depot , waybill.product_type  ORDER BY $grouping DESC";
        $this->paging->rawQuery($query);
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();

        $result = $this->paging->results();
        // var_dump($this->paging);
        if (!empty($result)) {
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $value->difference_volume = number_format($value->waybill_volume - $value->outlet_volume);
                $value->waybill_volume = number_format($value->waybill_volume);
                $value->outlet_volume = number_format($value->outlet_volume);
                $value->flagged  = $value->difference_volume <> 0;
            }
            $response["reports"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No reports available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }

    public function analytics($condition = " WHERE 1 ")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;

        $bdc = $this->http->json->bdc??null;
        $product_type = $this->http->json->product_type??null;
        $group_by = $this->http->json->group_by??null;
        $date_range = $this->http->json->date_range??null;
        $status = $this->http->json->status??null;
        $nagatives = $this->http->json->nagatives??null;

        if ($date_range) {
            if ($date_range->endDate && $date_range->startDate) {
                $startDate = $this->date->sql_date($date_range->startDate);
                $endDate = $this->date->sql_date($date_range->endDate);
                $condition .= " AND (manifest.arrival_date  BETWEEN '$startDate' AND '$endDate') ";
            }
        }

        if ($bdc && $bdc!="All") {
            $condition .= " AND manifest.importer_name = '$bdc'";
        }

        if ($product_type && $product_type!="All") {
            $condition .= " AND manifest.product_type = '$product_type'";
        }

        $grouping = "CONCAT(YEAR(manifest.arrival_date), '/', MONTH(manifest.arrival_date))";

        if ($status && $status!="All") {
            if ($status==="Flagged") {
                $condition .= " AND (manifest.volume <> waybill.volume OR waybill.volume IS NULL)";
            }
            if ($status==="Not Flagged") {
                $condition .= " AND manifest.volume = waybill.volume ";
            }
        }

        if ($nagatives && $nagatives!="All") {
            if ($nagatives==="Nagatives") {
                $condition .= " AND (manifest.volume - waybill.volume  < 0)";
            }
            if ($nagatives==="Positives") {
                $condition .= " AND (manifest.volume - waybill.volume  >= 0)";
            }
        }
        
        $query = "SELECT MIN(manifest.id) id, $grouping as grouping, manifest.product_type, SUM(manifest.volume) manifest_volume, SUM(waybill.volume) waybill_volume, manifest.importer_name bdc FROM ".self::$table_manifest." manifest JOIN ".self::$table." waybill ON waybill.bdc = manifest.importer_name AND waybill.product_type = manifest.product_type $condition GROUP BY $grouping, manifest.importer_name, manifest.product_type  ORDER BY $grouping DESC";
        $this->paging->rawQuery($query);
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();

        $result = $this->paging->results();
        // var_dump($this->paging);
        if (!empty($result)) {
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $value->difference_volume = number_format($value->manifest_volume - $value->waybill_volume);
                $value->manifest_volume = number_format($value->manifest_volume);
                $value->waybill_volume = number_format($value->waybill_volume);
                $value->flagged  = $value->difference_volume <> 0;
            }
            $response["reports"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No reports available";
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
                  'type' =>  "waybill_imp",
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
    
    public function expected_declaration()
    {
        $showBDC = $this->http->json->show_bdc??null;
        $omc = $this->http->json->omc??null;
        if (empty($omc)) {
            $this->http->_403("OMC is required");
        }

        $response = array();
        $response['computes'] = array();
        $response['total'] = 0;
        $computes = $this->getWaybills($omc)??null;
        if (empty($computes)) {
            $this->http->_403("Please provide variables to compute");
        }

        foreach ($computes as $key => $compute) {
            if(empty($compute->volume)){
                $compute->volume = 0;
            }

            $bdc_to_show = "";
            if($showBDC && $showBDC === "Show"){
                $bdc_to_show = ". Purchased from ".$compute->bdc;
            }
            $product = array();
            $product['computations'] = array();
            $date = $this->date->sql_date($compute->date);
            $product_title = $compute->product_type;
            $window = "";
            $product['total'] = 0;
            $this->db->query("SELECT tw.*, tt.name tax FROM tax_window tw JOIN `tax_type` tt ON tw.tax_type=tt.id WHERE tw.`tax_product` = (SELECT id FROM tax_schedule_products WHERE name = '{$compute->product_type}' LIMIT 1) AND tw.`date_from`<= '{$date}' AND tw.`date_to` >= '{$date}'");
            $queryResult = $this->db->results();
            if($queryResult && $this->db->count > 0){
                foreach ($queryResult as $key => $tax) {
                    $tax_schedule = array();
                    $window = ". {$this->date->day($tax->date_from)} - {$this->date->day($tax->date_to)} {$this->date->month_year($compute->date)} ";
                    $tax_schedule['calculation'] = $compute->volume." * ".$tax->rate." (volume)";
                    $tax_schedule['tax'] = $tax->tax;
                    $tax_schedule['amount'] = number_format($compute->volume * $tax->rate,2);
                    $product['total'] += $compute->volume * $tax->rate;
                    array_push($product['computations'], $tax_schedule);
                }
            }
            $product['product'] = $product_title . $window . $bdc_to_show;
            $response['total']+=$product['total'];
            $product['total'] = number_format($product['total'],2);
            array_push($response['computes'], $product);
        }
        $response['total'] = number_format($response['total'],2);
        return $response;
    }

    public function getWaybills($omc){
        $this->db->query("SELECT * FROM ".self::$table." WHERE omc='$omc' ");
        return $this->db->results();
    }

}