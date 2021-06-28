<?php
class OutletmodelreportModel extends BaseModel
{
    private static $petroleum_order = "petroleum_order";
    private static $petroleum_outlet = "petroleum_outlet";
    public function __construct()
    {
        parent::__construct();
    }

    public function index($condition=" WHERE 1 ")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;

        $depot = $this->http->json->depot??null;
        $date_range = $this->http->json->date_range??null;
        $product_type = $this->http->json->product_type??null;
        $dateRang = "";
        $condition1=$condition;

        if ($date_range) {
            if ($date_range->endDate && $date_range->startDate) {
                $startDate = $this->date->sql_date($date_range->startDate);
                $endDate = $this->date->sql_date($date_range->endDate);
                $condition .= " AND (ord.order_date  BETWEEN '$startDate' AND '$endDate') ";
                $condition1 .= " AND (outlet.datetime  BETWEEN '$startDate' AND '$endDate') ";
                $dateRang = $this->date->month_year_day($startDate) ." - ". $this->date->month_year_day($endDate);
            }
        }

        if ($depot && $depot!="All") {
            $condition .= " AND ord.depot = '$depot'";
            $condition1 .= " AND outlet.depot = '$depot'";
        }

        if ($product_type && $product_type!="All") {
            $condition .= " AND ord.product_type = '$product_type'";
            $condition1 .= " AND outlet.product_type = '$product_type'";
        }
        
        $query = "SELECT DATE(ord.order_date) order_date, (SELECT name FROM tax_schedule_products WHERE code=ord.product_type LIMIT 1) product, 
        SUM(ord.volume) order_volume, SUM(ord.unit_price) order_amount, (SELECT name FROM depot WHERE code=ord.depot LIMIT 1) depot,
        SUM(outlet.volume) outlet_volume
        FROM ".self::$petroleum_order." ord LEFT JOIN ".self::$petroleum_outlet." outlet 
        ON ord.depot = outlet.depot AND ord.product_type = outlet.product_type $condition Group By order_date, product, depot 
        UNION
        SELECT DATE(outlet.datetime) order_date, (SELECT name FROM tax_schedule_products WHERE code=outlet.product_type LIMIT 1) product, 
        SUM(ord.volume) order_volume, SUM(ord.unit_price) order_amount, (SELECT name FROM depot WHERE code=outlet.depot LIMIT 1) depot,
        SUM(outlet.volume) outlet_volume
        FROM ".self::$petroleum_order." ord RIGHT JOIN ".self::$petroleum_outlet." outlet 
        ON ord.depot = outlet.depot AND ord.product_type = outlet.product_type $condition1 AND ord.id IS NULL
        Group By order_date, product, depot Order By order_date DESC";

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
                $value->difference_volume = number_format($value->order_volume - $value->outlet_volume);
                $value->order_volume = number_format($value->order_volume,1);
                $value->outlet_volume = number_format($value->outlet_volume,1);
                $value->order_amount = number_format($value->order_amount, 2);
                $value->order_date = $date_range && $date_range->endDate?$dateRang:$this->date->month_year_day($value->order_date);
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
