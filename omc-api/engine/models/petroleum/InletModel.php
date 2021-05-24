<?php
class InletModel extends BaseModel {
  private static $petroleum_inlet = "petroleum_inlet";

  public function __construct()
  {
      parent::__construct();
  }

  public function inlets($condition=" WHERE 1 ")
  {
      $response = array();
      $result_per_page = $this->http->json->result_per_page??20;
      $page = $this->http->json->page??1;

      $bdc = $this->http->json->bdc??null;
      $date_range = $this->http->json->date_range??null;
      $group_by = $this->http->json->group_by??null;
      $product_type = $this->http->json->product_type??null;
      $depot = $this->http->json->depot??null; //on declaration table
      $omc = $this->http->json->omc??null;
      $dateRang = "";

      if ($date_range) {
          if ($date_range->endDate && $date_range->startDate) {
              $startDate = $this->date->sql_date($date_range->startDate);
              $endDate = $this->date->sql_date($date_range->endDate);
              $condition .= " AND (datetime  BETWEEN '$startDate' AND '$endDate') ";
              $dateRang = $this->date->month_year_day($startDate) ." - ". $this->date->month_year_day($endDate);
          }
      }

      if ($bdc && $bdc!="All") {
          $condition .= " AND bdc = '$bdc'";
      }

      if ($product_type && $product_type!="All") {
          $condition .= " AND product_type = '$product_type'";
      }

      if ($depot && $depot!="All") {
          $condition .= " AND depot ='$depot'";
      }

      if ($group_by) {
          $list = "";
          foreach ($group_by as $key => $group) {
              if ($group==="Product type") {
                  $list.= "product_type,";
              }
              if ($group==="BDC") {
                  $list.= "bdc,";
              }
              if ($group==="Depot") {
                  $list.= "depot,";
              }
          }
          $group = trim($list, ',');
          $group_by = "Group By $group ";
      } else {
          $group_by = "Group By datetime";
      }
      
      $query = "SELECT *, SUM(volume) volume FROM ".self::$petroleum_inlet." $condition $group_by Order By datetime DESC";
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
              $value->volume = number_format($value->volume);
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
