<?php
class AnalyticsModel extends BaseModel
{
    private static $petroleum_manifest = "petroleum_manifest";
    private static $petroleum_declaration = "petroleum_declaration";
    private static $petroleum_order = "petroleum_order";
    private static $petroleum_preorder = "petroleum_preorder";
  
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
    }
    
    public function manifest($condition=" WHERE 1 ")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;

        $bdc = $this->http->json->bdc??null;
        $date_range = $this->http->json->date_range??null;
        $group_by = $this->http->json->group_by??null;
        $status = $this->http->json->status??null;  //on declaration table
        $product_type = $this->http->json->product_type??null;
        $idf_condition = $this->http->json->idf_condition??null; //on declaration table
        $declaration_condition = $this->http->json->declaration_condition??null;
        $dateRang = "";

        if ($date_range) {
            if ($date_range->endDate && $date_range->startDate) {
                $startDate = $this->date->sql_date($date_range->startDate);
                $endDate = $this->date->sql_date($date_range->endDate);
                $condition .= " AND (m.arrival_date  BETWEEN '$startDate' AND '$endDate') ";
                $dateRang = $this->date->month_year_day($startDate) ." - ". $this->date->month_year_day($endDate);
            }
        }

        if ($bdc && $bdc!="All") {
            $condition .= " AND m.importer_name = '$bdc'";
        }

        if ($product_type && $product_type!="All") {
            $condition .= " AND m.product_type = '$product_type'";
        }

        if ($status && $status!="All") {
            if ($status==="Cleared") {
                $condition .= " AND d.cleared_date IS NOT NULL";
            }
            if ($status==="Not Cleared") {
                $condition .= " AND d.cleared_date IS NULL";
            }
        }

        if ($idf_condition && $idf_condition!="All") {
            if ($idf_condition==="Has IDF") {
                $condition .= " AND d.idf_application_number IS NOT NULL";
            }
            if ($idf_condition==="No IDF") {
                $condition .= " AND d.idf_application_number IS NULL";
            }
        }

        if ($declaration_condition && $declaration_condition!="All") {
            if ($declaration_condition==="Declared") {
                $condition .= " AND d.id IS NOT NULL";
            }
            if ($declaration_condition==="Not Declared") {
                $condition .= " AND d.id IS NULL";
            }
        }

        if ($group_by) {
            $list = "";
            foreach ($group_by as $key => $group) {
                if ($group==="BDC") {
                    $list.= "m.importer_name,";
                }
                if ($group==="Product type") {
                    $list.= "m.product_type,";
                }
            }
            $group = trim($list, ',');
            $group_by = "Group By $group ";
        } else {
            $group_by = "Group By m.arrival_date";
        }
        
        $query = "SELECT m.id manifest_id, m.arrival_date, m.vessel_name, m.vessel_number,
        (SELECT name FROM tax_schedule_products WHERE code=m.product_type LIMIT 1) manifest_product, 
        SUM(m.volume) manifest_volume, SUM(m.amount) manifest_amount, m.ucr_number manifest_ucr, m.exporter_name, 
        (SELECT name FROM bdc WHERE code=m.importer_name LIMIT 1) manifest_omc, d.*, SUM(d.amount) declaration_amount, SUM(d.volume) declaration_volume
        FROM ".self::$petroleum_manifest." m LEFT JOIN ".self::$petroleum_declaration." d 
        ON m.ucr_number = d.ucr_number $condition $group_by Order By m.arrival_date DESC";

        $this->paging->rawQuery($query);
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();

        $result = $this->paging->results();
        // var_dump($this->paging->lastQuery());
        if (!empty($result)) {
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $value->difference_volume = number_format($value->manifest_volume - $value->declaration_volume);
                $value->manifest_volume = number_format($value->manifest_volume);
                $value->declaration_volume = number_format($value->declaration_volume);
                $value->difference_amount = number_format($value->manifest_amount - $value->declaration_amount, 2);
                $value->manifest_amount = number_format($value->manifest_amount, 2);
                $value->arrival_date = $date_range && $date_range->endDate?$dateRang:$this->date->month_year_day($value->arrival_date);
                $value->status = $status;
                $value->idf = $idf_condition;
            }
            $response["reports"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No reports available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
    
    public function orders($condition=" WHERE 1 ")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;

        $bdc = $this->http->json->bdc??null;
        $date_range = $this->http->json->date_range??null;
        $group_by = $this->http->json->group_by??null;
        $status = $this->http->json->status??null;  //on declaration table
        $product_type = $this->http->json->product_type??null;
        $depot = $this->http->json->depot??null; //on declaration table
        $omc = $this->http->json->omc??null;
        $condition1=$condition;
        $dateRang = "";

        if ($date_range) {
            if ($date_range->endDate && $date_range->startDate) {
                $startDate = $this->date->sql_date($date_range->startDate);
                $endDate = $this->date->sql_date($date_range->endDate);
                $condition .= " AND (prord.preorder_date  BETWEEN '$startDate' AND '$endDate') ";
                $condition1 .= " AND (ord.order_date  BETWEEN '$startDate' AND '$endDate') ";
                $dateRang = $this->date->month_year_day($startDate) ." - ". $this->date->month_year_day($endDate);
            }
        }

        if ($bdc && $bdc!="all") {
            $condition .= " AND prord.bdc = '$bdc'";
            $condition1 .= " AND ord.bdc  = '$bdc'";
        }

        if ($product_type && $product_type!="all") {
            $condition .= " AND prord.product_type = '$product_type'";
            $condition1 .= " AND ord.product_type  = '$product_type'";
        }

        if ($depot && $depot!="all") {
            $condition .= " AND prord.depot ='$depot'";
            $condition1 .= " AND ord.depot  = '$depot'";
        }

        if ($omc && $omc!="all") {
            $condition .= " AND prord.omc = '$omc'";
            $condition1 .= " AND ord.omc  = '$omc'";
        }

        if ($status && $status!="All") {
            if ($status==="Ordered") {
                $condition .= " AND ord.order_date IS NOT NULL";
                $condition1 .= " AND ord.order_date IS NOT NULL";
            }
            if ($status==="Not Ordered") {
                $condition .= " AND ord.order_date IS NULL AND prord.preorder_date IS NOT NULL";
                $condition1 .= " AND ord.order_date IS NULL AND prord.preorder_date IS NOT NULL";
            }
        }

        $group_by1 = "";
        if ($group_by) {
            $list = "";
            $list1 = "";
            foreach ($group_by as $key => $group) {
                if ($group==="Product type") {
                    $list.= "prord.product_type,";
                    $list1.= "ord.product_type,";
                }
                if ($group==="BDC") {
                    $list.= "prord.bdc,";
                    $list1.= "ord.bdc,";
                }
                if ($group==="Depot") {
                    $list.= "prord.depot,";
                    $list1.= "ord.depot,";
                }
                if ($group==="OMC") {
                    $list.= "prord.omc,";
                    $list1.= "ord.omc,";
                }
            }
            $group = trim($list, ',');
            $group1 = trim($list1, ',');
            $group_by = "Group By $group ";
            $group_by1 = "Group By $group1 ";
        } else {
            $group_by = "Group By prord.preorder_date";
            $group_by1 = "Group By ord.order_date";
        }
        
        $query = "SELECT prord.id, prord.preorder_date, prord.omc, prord.bdc, prord.depot, 
        (SELECT name FROM tax_schedule_products WHERE code=prord.product_type LIMIT 1) product_type, 
        (SELECT name FROM bdc WHERE code=prord.bdc LIMIT 1) bdc, 
        (SELECT name FROM depot WHERE code=prord.depot LIMIT 1) depot, 
        (SELECT name FROM omc WHERE tin=prord.omc LIMIT 1) omc, 
        SUM(prord.volume) preorder_volume, prord.reference_number,
         SUM(ord.unit_price) order_unit_price, SUM(ord.volume) order_volume, ord.order_date, prord.preorder_date date
        FROM ".self::$petroleum_preorder." prord LEFT JOIN ".self::$petroleum_order." ord 
        ON prord.reference_number = ord.reference_number $condition $group_by
        UNION
        SELECT prord.id, prord.preorder_date, ord.omc, ord.bdc,ord.depot, 
        (SELECT name FROM tax_schedule_products WHERE code=ord.product_type LIMIT 1) product_type, 
        (SELECT name FROM bdc WHERE code=ord.bdc LIMIT 1) bdc, 
        (SELECT name FROM depot WHERE code=ord.depot LIMIT 1) depot, 
        (SELECT name FROM omc WHERE tin=ord.omc LIMIT 1) omc, 
        SUM(prord.volume) preorder_volume, ord.reference_number, 
         SUM(ord.unit_price) order_unit_price, SUM(ord.volume) order_volume, ord.order_date, ord.order_date date
        FROM ".self::$petroleum_preorder." prord RIGHT JOIN ".self::$petroleum_order." ord 
        ON prord.reference_number = ord.reference_number $condition1 AND prord.id IS NULL $group_by1
        Order By date DESC";

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
                $value->difference_volume = number_format($value->preorder_volume - $value->order_volume);
                $value->preorder_volume = number_format($value->preorder_volume);
                $value->order_volume = number_format($value->order_volume);
                $value->order_unit_price = number_format($value->order_unit_price, 2);
                $value->preorder_date = $date_range && $date_range->endDate?$dateRang:$this->date->month_year_day($value->preorder_date);
                $value->order_date = $date_range && $date_range->endDate?$dateRang:$this->date->month_year_day($value->order_date);
                $value->status = $status;
            }
            $response["reports"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No reports available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }
}
