<?php
class InputreconciliationModel extends BaseModel
{
    private static $manifest = "petroleum_manifest";
    private static $declaration = "petroleum_declaration";
    public function __construct()
    {
        parent::__construct();
    }

    public function index($condition=" WHERE 1 ")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;

        $bdc = $this->http->json->bdc??null;
        $date_range = $this->http->json->date_range??null;
        $product_type = $this->http->json->product_type??null;
        $status = $this->http->json->status??null;
        $condition1 = $condition;

        if ($date_range) {
            if ($date_range->endDate && $date_range->startDate) {
                $startDate = $this->date->sql_date($date_range->startDate);
                $endDate = $this->date->sql_date($date_range->endDate);
                $condition .= " AND (m.arrival_date BETWEEN '$startDate' AND '$endDate') ";
                $condition1 .= " AND (d.declaration_date BETWEEN '$startDate' AND '$endDate') ";
            }
        }

        if ($status && $status!="All") {
            if ($status==="Flagged") {
                $condition .= " AND (m.volume <> d.volume OR m.amount <> d.amount OR d.amount IS NULL OR d.volume IS NULL)";
                $condition1 .= " AND (m.volume <> d.volume OR m.amount <> d.amount OR d.amount IS NULL OR d.volume IS NULL)";
            }
            if ($status==="Not Flagged") {
                $condition .= " AND m.volume = d.volume AND m.amount = d.amount";
                $condition1 .= " AND m.volume = d.volume AND m.amount = d.amount";
            }
        }

        if ($bdc && $bdc!="All") {
            $condition .= " AND m.importer_name = '$bdc'";
            $condition1 .= " AND d.importer_name = '$bdc'";
        }

        if ($product_type && $product_type!="All") {
            $condition .= " AND m.product_type = '$product_type'";
            $condition1 .= " AND d.product_type = '$product_type'";
        }

        $query = "SELECT m.id manifest_id, m.arrival_date arrival_date,d.declaration_date, m.vessel_name, m.vessel_number,
        (SELECT name FROM tax_schedule_products WHERE code=m.product_type LIMIT 1) manifest_product, 
        m.volume manifest_volume, m.amount manifest_amount, m.ucr_number manifest_ucr, m.exporter_name, 
        (SELECT name FROM bdc WHERE code=m.importer_name LIMIT 1) manifest_omc, d.*, d.amount declaration_amount, d.volume declaration_volume
        FROM ".self::$manifest." m LEFT JOIN ".self::$declaration." d 
        ON m.ucr_number = d.ucr_number $condition
        UNION
        SELECT m.id manifest_id, m.arrival_date arrival_date,d.declaration_date, m.vessel_name, m.vessel_number,
        (SELECT name FROM tax_schedule_products WHERE code=d.product_type LIMIT 1) manifest_product, 
        m.volume manifest_volume, m.amount manifest_amount, m.ucr_number manifest_ucr, m.exporter_name, 
        (SELECT name FROM bdc WHERE code=d.importer_name LIMIT 1) manifest_omc, d.*, d.amount declaration_amount, d.volume declaration_volume
        FROM ".self::$manifest." m RIGHT JOIN ".self::$declaration." d 
        ON m.ucr_number = d.ucr_number $condition1 AND m.id IS NULL Order By arrival_date DESC";

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
                $value->difference_volume = number_format($value->manifest_volume - $value->declaration_volume);
                $value->manifest_volume = number_format($value->manifest_volume);
                $value->declaration_volume = number_format($value->declaration_volume);
                $value->difference_amount = number_format($value->manifest_amount - $value->declaration_amount, 2);
                $value->manifest_amount = number_format($value->manifest_amount, 2);
                $value->declaration_amount = number_format($value->declaration_amount, 2);
                $value->arrival_date = $this->date->month_year_day($value->arrival_date);
                $value->flagged  = $value->difference_volume <> 0 || $value->difference_amount <> 0;
                $value->days = 0;
                if ($value->cleared_date && $value->arrival_date) {
                    $start_Date = strtotime($value->arrival_date);
                    $end_Date = strtotime($value->cleared_date);
                    $datediff = $start_Date - $end_Date;
                    $value->days = round($datediff / (60 * 60 * 24));
                }
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
