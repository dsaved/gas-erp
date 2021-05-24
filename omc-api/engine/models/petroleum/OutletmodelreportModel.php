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
        $group_by = $this->http->json->group_by??null;
        $product_type = $this->http->json->product_type??null;
        $dateRang = "";

        if ($date_range) {
            if ($date_range->endDate && $date_range->startDate) {
                $startDate = $this->date->sql_date($date_range->startDate);
                $endDate = $this->date->sql_date($date_range->endDate);
                $condition .= " AND (ord.order_date  BETWEEN '$startDate' AND '$endDate') ";
                $dateRang = $this->date->month_year_day($startDate) ." - ". $this->date->month_year_day($endDate);
            }
        }

        if ($depot && $depot!="All") {
            $condition .= " AND ord.depot = '$depot'";
        }

        if ($product_type && $product_type!="All") {
            $condition .= " AND ord.product_type = '$product_type'";
        }
        
        $query = "SELECT DATE(ord.order_date) order_date, ord.product_type product, 
        SUM(ord.volume) order_volume, SUM(ord.unit_price) order_amount, ord.depot,
        SUM(outlet.volume) outlet_volume
        FROM ".self::$petroleum_order." ord LEFT JOIN ".self::$petroleum_outlet." outlet 
        ON ord.depot = outlet.depot AND ord.product_type = outlet.product_type
        $condition Group By DATE(ord.order_date), ord.product_type, ord.depot Order By DATE(ord.order_date)  DESC";

        $this->paging->rawQuery($query);
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();

        $result = $this->paging->results();
        // var_dump($this->paging->lastQuery());
        // var_dump($this->paging);
        if (!empty($result)) {
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $value->difference_volume = number_format($value->order_volume - $value->outlet_volume);
                $value->order_volume = number_format($value->order_volume);
                $value->outlet_volume = number_format($value->outlet_volume);
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
