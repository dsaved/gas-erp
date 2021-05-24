<?php
class MissingproductsModel extends BaseModel{
  private static $petroleum_declaration = "petroleum_declaration";
  public function __construct()
  {
      parent::__construct();
  }

  public function index()
  {
      $response = array();
      $result_per_page = $this->http->json->result_per_page??20;
      $page = $this->http->json->page??1;

      $bdc = $this->http->json->bdc??null;
      $date_range = $this->http->json->date_range??null;
      $product_type = $this->http->json->product_type??null;

      $condition = " WHERE missing=1 ";

      if ($date_range) {
          if ($date_range->endDate && $date_range->startDate) {
              $startDate = $this->date->sql_date($date_range->startDate);
              $endDate = $this->date->sql_date($date_range->endDate);
              $condition .= " AND (declaration_date  BETWEEN '$startDate' AND '$endDate') ";
          }
      }

      if ($bdc && $bdc!="All") {
          $condition .= " AND importer_name = '$bdc'";
      }

      if ($product_type && $product_type!="All") {
          $condition .= " AND product_type = '$product_type'";
      }

      $query = "SELECT * FROM ".self::$petroleum_declaration." $condition ORDER BY declaration_date";
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
              $value->volume = number_format($value->volume);
              $value->amount = number_format($value->amount, 2);
          }
          $response["reports"] = $result;
      } else {
          $response['success'] = false;
          $response['message'] = "No products available";
      }

      $response["pagination"] = $this->paging->paging();
      return $response;
  }
}