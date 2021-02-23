<?php
class LiftingsModel extends BaseModel
{
    private static $table = "tax_schedule";

    public function __construct()
    {
        parent::__construct();
    }
    
    public function index()
    {
        $id = $this->http->json->id??null;
        if (empty($id)) {
            $this->http->_403("OMC id is required");
        }
        return $this->compute();
    }
    
    public function compute()
    {
        $id = $this->http->json->id??null;
        if (empty($id)) {
            $this->http->_403("OMC id is required");
        }

        $response = array();
        $response['computes'] = array();
        $response['total'] = 0;
        $computes = $this->http->json->computes??null;
        if (empty($computes)) {
            $this->http->_403("Please provide variables to compute");
        }

        foreach ($computes as $key => $compute) {
            if(empty($compute->litres)){
                $compute->litres = 0;
            }
            $product = array();
            $product['computations'] = array();
            $date = $this->date->sql_date($compute->date);
            $product['product'] = $compute->product." - ".$this->date->month_year($compute->date);
            $product['total'] = 0;
            $this->db->query("SELECT * FROM ".self::$table." JOIN `tax_type` ON ".self::$table.".tax_type=tax_type.id WHERE `tax_product` = (SELECT id FROM tax_schedule_products WHERE name = '{$compute->product}' LIMIT 1) AND `date_from`<= '{$date}' AND `date_to` >= '{$date}'");
            if($this->db->results() && $this->db->count > 0){
                // $this->http->reply($this->db->results())->json();
                foreach ($this->db->results() as $key => $tax) {
                    $tax_schedule = array();
                    $tax_schedule['calculation'] = $compute->litres." * ".$tax->rate." (litres)";
                    $tax_schedule['tax'] = $tax->name;
                    $tax_schedule['amount'] = number_format($compute->litres * $tax->rate,2);
                    $product['total'] += $compute->litres * $tax->rate;
                    array_push($product['computations'], $tax_schedule);
                }
            }
            $response['total']+=$product['total'];
            $product['total'] = number_format($product['total'],2);
            array_push($response['computes'], $product);
        }
        $receipts = $this->getReceipts($id);
        $response['receipts'] = $receipts['receipts'];
        $response['receipt_total_amount'] = number_format($receipts['total_amount'], 2);
        $response['remaining_balance'] = number_format(((double)$response['total'] - (double)$receipts['total_amount']),2);
        $response['total'] = number_format($response['total'],2);
        return $response;
    }

    public function getReceipts($id){
        $response = array();
        $this->db->query("SELECT * FROM omc_receipt WHERE omc_id=$id ORDER By date");
        $result = $this->db->results();
        $total_amount = 0.0;
        $jsonReceiptStr = [];
        if (!empty($result)) {
            foreach ($result as $key => &$value) {
                $total_amount += (double)$value->amount;
                // $value->amount = number_format($value->amount, 2);
                if(!empty($jsonReceiptStr) && $value->date === $jsonReceiptStr[count($jsonReceiptStr)-1]['id']){
                    $jsonReceiptStr[count($jsonReceiptStr)-1]['subtotal'] += (double)$value->amount;
                    array_push($jsonReceiptStr[count($jsonReceiptStr)-1]['payments'], ["bank"=>$value->bank,"amount"=>number_format($value->amount, 2)]);
                }else{
                    array_push($jsonReceiptStr, array("id"=>$value->date,"name"=>$this->date->_human_date($value->date), "subtotal"=> $value->amount,"payments"=> [["bank"=>$value->bank,"amount"=>number_format($value->amount, 2)]]));
                }
                // Group by date and add all its transactions to same array
            }
            $response['receipts'] = $jsonReceiptStr;
            $response['total_amount'] = $total_amount;
        } else {
            $response['receipts']  = [];
            $response['total_amount'] = 0.0;
        }
        return $response;
    }
}
