<?php
class TanksModel extends BaseModel
{
    private static $table = "petroleum_tanks";
    public function __construct()
    {
        parent::__construct();
    }

    public function index($condition=" WHERE tank='depot' ")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $depot = $this->http->json->depot??null;
        
        if ($depot && $depot!="All") {
            $condition .= " AND depot ='$depot'";
        }
      
        $this->paging->rawQuery("SELECT  depot, Count(product) product, SUM(volume) vol, SUM(full) full FROM ".self::$table." $condition GROUP BY depot Order By depot ");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();

        $result = $this->paging->results();
        if (!empty($result)) {
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $value->product = number_format($value->product);
                $value->level = number_format(($value->vol * 100) /$value->full, 2);
                $value->volume = number_format($value->vol , 2);
            }
            $response["tanks"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No tanks available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }

    public function depotsTank($condition=" WHERE tank='depot' ")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $depot = $this->http->json->depot??null;
        $product_type = $this->http->json->product_type??null;

        if ($depot && $depot!="All") {
            $condition .= " AND depot ='$depot'";
        }

        if ($product_type && $product_type!="All") {
            $condition .= " AND product = '$product_type'";
        }
      
        $this->paging->rawQuery("SELECT * FROM ".self::$table." $condition Order By `id`");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();

        $result = $this->paging->results();
        if (!empty($result)) {
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $value->percent = number_format(($value->volume * 100) /$value->full, 2);
                $value->volume = number_format($value->volume , 2);
                $value->notsold = false;
                $this->db->query("SELECT * FROM petroleum_alarm_notification WHERE product='{$value->product}' AND alarm= 'depot' AND depot='{$value->depot}' AND type='not sold for a week' ORDER BY time DESC LIMIT 1");
                if ($this->db->count > 0) {
                    $value->notsold = true;
                }
                
                $value->invalidpump = false;
                $this->db->query("SELECT * FROM petroleum_alarm_notification WHERE product='{$value->product}' AND alarm= 'depot' AND depot='{$value->depot}' AND type='discharge from empty tank' ORDER BY time DESC LIMIT 1");
                if ($this->db->count > 0) {
                    $value->invalidpump = true;
                }
            }
            $response["tanks"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No tanks available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }

    public function invalid_pump($condition=" WHERE type='discharge from empty tank' AND alarm='depot' ")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $depot = $this->http->json->depot??null;
        $product_type = $this->http->json->product??null;

        if ($depot && $depot!="All") {
            $condition .= " AND depot ='$depot'";
        }

        if ($product_type && $product_type!="All") {
            $condition .= " AND product = '$product_type'";
        }
      
        $this->paging->rawQuery("SELECT * FROM petroleum_alarm_notification $condition Order By `id`");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();

        $result = $this->paging->results();
        if (!empty($result)) {
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $value->time = $this->date->human_datetime_short($value->time);
                $value->volume = number_format($value->volume, 2);
            }
            $response["reports"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No reports available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }

    public function bdcs($condition=" WHERE tank='bdc' ")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $bdc = $this->http->json->bdc??null;
        
        if ($bdc && $bdc!="All") {
            $condition .= " AND bdc ='$bdc'";
        }
      
        $this->paging->rawQuery("SELECT  bdc, Count(product) product, SUM(volume) vol, SUM(full) full FROM ".self::$table." $condition GROUP BY bdc Order By bdc ");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();

        $result = $this->paging->results();
        if (!empty($result)) {
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $value->product = number_format($value->product);
                $value->level = number_format(($value->vol * 100) /$value->full, 2);
                $value->volume = number_format($value->vol , 2);
            }
            $response["tanks"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No tanks available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }

    public function bdcsTank($condition=" WHERE tank='bdc' ")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $bdc = $this->http->json->bdc??null;
        $product_type = $this->http->json->product_type??null;

        if ($bdc && $bdc!="All") {
            $condition .= " AND bdc ='$bdc'";
        }

        if ($product_type && $product_type!="All") {
            $condition .= " AND product = '$product_type'";
        }
      
        $this->paging->rawQuery("SELECT * FROM ".self::$table." $condition Order By `id`");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();

        $result = $this->paging->results();
        if (!empty($result)) {
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $value->percent = number_format(($value->volume * 100) /$value->full, 2);
                $value->volume = number_format($value->volume , 2);
                $value->notsold = false;
                $this->db->query("SELECT * FROM petroleum_alarm_notification WHERE product='{$value->product}' AND alarm= 'bdc' AND bdc='{$value->bdc}' AND type='not sold for a week' ORDER BY time DESC LIMIT 1");
                if ($this->db->count > 0) {
                    $value->notsold = true;
                }
                
                $value->invalidpump = false;
                $this->db->query("SELECT * FROM petroleum_alarm_notification WHERE product='{$value->product}' AND alarm= 'bdc' AND bdc='{$value->bdc}' AND type='discharge from empty tank' ORDER BY time DESC LIMIT 1");
                if ($this->db->count > 0) {
                    $value->invalidpump = true;
                }
            }
            $response["tanks"] = $result;
        } else {
            $response['success'] = false;
            $response['message'] = "No tanks available";
        }

        $response["pagination"] = $this->paging->paging();
        return $response;
    }

    public function bdc_invalid_pump($condition=" WHERE type='discharge from empty tank' AND alarm='bdc' ")
    {
        $response = array();
        $result_per_page = $this->http->json->result_per_page??20;
        $page = $this->http->json->page??1;
        $bdc = $this->http->json->bdc??null;
        $product_type = $this->http->json->product??null;

        if ($bdc && $bdc!="All") {
            $condition .= " AND bdc ='$bdc'";
        }

        if ($product_type && $product_type!="All") {
            $condition .= " AND product = '$product_type'";
        }
      
        $this->paging->rawQuery("SELECT * FROM petroleum_alarm_notification $condition Order By `id`");
        $this->paging->result_per_page($result_per_page);
        $this->paging->pageNum($page);
        $this->paging->execute();
        $this->paging->reset();

        $result = $this->paging->results();
        if (!empty($result)) {
            $response['success'] = true;
            foreach ($result as $key => &$value) {
                $value->time = $this->date->human_datetime_short($value->time);
                $value->volume = number_format($value->volume, 2);
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