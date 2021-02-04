<?php
class ComputeModel extends BaseModel
{
    private static $table = "tax_schedule";

    public function __construct()
    {
        parent::__construct();
    }
    
    public function compute()
    {
        $response = array();
        $response['computes'] = array();
        $response['total'] = 0;
        $computes = $this->http->json->computes??null;

        foreach ($computes as $key => $compute) {
            if(empty($compute->product->label)){
                $this->http->_403("Please select product");
            }
            if(empty($compute->taxdate)){
                $this->http->_403("Please choose date");
            }
            if(empty($compute->litters)){
                $compute->litters = 0;
            }
            $product = array();
            $product['computations'] = array();
            $taxdate = $this->date->sql_date($compute->taxdate);
            $product['product'] = $compute->product->label." - ".$this->date->month_year($compute->taxdate);
            $product['total'] = 0;
            $this->db->query("SELECT * FROM ".self::$table." JOIN `tax_type` ON ".self::$table.".tax_type=tax_type.id WHERE `tax_product` = {$compute->product->value} AND `date_from`<= '{$taxdate}' AND `date_to` >= '{$taxdate}'");
            if($this->db->results() && $this->db->count > 0){
                // $this->http->reply($this->db->results())->json();
                foreach ($this->db->results() as $key => $tax) {
                    $tax_schedule = array();
                    $tax_schedule['calculation'] = $compute->litters."*".$tax->rate."(litters)";
                    $tax_schedule['tax'] = $tax->name;
                    $tax_schedule['amount'] = number_format($compute->litters * $tax->rate,2);
                    $product['total'] += $compute->litters * $tax->rate;
                    array_push($product['computations'], $tax_schedule);
                }
            }
            $response['total']+=$product['total'];
            $product['total'] = number_format($product['total'],2);
            array_push($response['computes'], $product);
        }
        $response['total'] = number_format($response['total'],2);
        return $response;
    }
    
}
