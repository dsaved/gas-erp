<?php
class InletmodelreportModel extends BaseModel
{
    private static $petroleum_declaration = "petroleum_declaration";
    private static $petroleum_inlet = "petroleum_inlet";
    public function __construct()
    {
        parent::__construct();
    }

    public function index($condition = " WHERE 1 ")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;

        $bdc = $this->http->json->bdc??null;
        $date_range = $this->http->json->date_range??null;

        if ($date_range) {
            if ($date_range->endDate && $date_range->startDate) {
                $startDate = $this->date->sql_date($date_range->startDate);
                $endDate = $this->date->sql_date($date_range->endDate);
                $condition .= " AND (dcl.declaration_date  BETWEEN '$startDate' AND '$endDate') ";
            }
        }

        if ($bdc && $bdc!="All") {
            $condition .= " AND dcl.importer_name = '$bdc'";
        }

        $query = "SELECT MIN(dcl.id) id, CONCAT(YEAR(dcl.declaration_date), '/', WEEK(dcl.declaration_date)) AS week, SUM(dcl.volume) declared_vol, SUM(inlet.volume) inlet_vol, dcl.importer_name bdc FROM ".self::$petroleum_declaration." dcl LEFT JOIN ".self::$petroleum_inlet." inlet ON inlet.bdc = dcl.importer_name $condition GROUP BY CONCAT(YEAR(dcl.declaration_date), '/', WEEK(dcl.declaration_date)) , dcl.importer_name, inlet.bdc ORDER BY CONCAT(YEAR(dcl.declaration_date), '/', WEEK(dcl.declaration_date)) DESC";
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
                $value->diff_vol = number_format($value->declared_vol - $value->inlet_vol);
                $value->declared_vol = number_format($value->declared_vol);
                $value->inlet_vol = number_format($value->inlet_vol);
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