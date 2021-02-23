<?php
class HomeModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    public function statistics()
    {
        $response = array();
        $condition =" AND ut.`softdelete`=0 ";
        $user_id = $this->http->json->user_id??null;
        $access_type = $this->http->json->access_type??null;

        if (strtolower($access_type)!=="admin") {
            $institute = 0;
            $this->db->query("SELECT organization FROM `users` WHERE `id`={$user_id}");
            if ($this->db->results() && $this->db->count > 0) {
                $user = $this->db->first();
                $institute = $user->organization;
            }
            //bank, accounts, unauthorized_transaers
            //user belongs to organization which is equal to institute
            //institute = account.owner
            if ($institute!=0) {
                $condition .= " AND `a`.`owner` = $institute ";
            }
        }

        $statsbog = array();
        $statsbog["stats_for"] = date('Y')." Stats";
        $statsbog["name"] = "BOG Unauthorized count";
        $bog_data = array(null,null,null,null,null,null,null,null,null,null,null,null);
        $this->db->query("SELECT COUNT(ut.id) as statcount, MONTH(`st`.`value_date`) as month FROM `unauthorized_transfers` AS ut INNER JOIN statements AS st ON st.id = ut.`statement_id` INNER JOIN `accounts` AS a ON a.id = ut.account_from INNER JOIN `banks` b ON b.id=a.bank WHERE `a`.`bank_type` LIKE '%Bank Of Ghana%' AND YEAR(`st`.`value_date`) = YEAR(CURRENT_DATE()) $condition GROUP BY MONTH(`st`.`value_date`) Order By `st`.`value_date` DESC LIMIT 12");
        if ($this->db->results() && $this->db->count > 0) {
            $results = $this->db->results();
            foreach ($results as $key => $value) {
                $bog_data[(int)$value->month -1] = $value->statcount;
            }
            foreach ($bog_data as $key => &$value) {
                if($value === null && $key < date("m")){
                    $value =0;
                }
            }
        } 
        $statsbog['stats'] = [array("name"=>"Unauthorized Transfer","data"=>$bog_data)];


        $statsorg = array();
        $statsorg["stats_for"] = date('Y')." Stats";
        $statsorg["name"] = "ORG Unauthorized count";
        $org_data = array(null,null,null,null,null,null,null,null,null,null,null,null);
        $this->db->query("SELECT COUNT(ut.id) as statcount, MONTH(`st`.`value_date`) as month FROM `unauthorized_transfers` AS ut INNER JOIN statements AS st ON st.id = ut.`statement_id` INNER JOIN `accounts` AS a ON a.id = ut.account_from INNER JOIN `banks` b ON b.id=a.bank WHERE `a`.`bank_type` LIKE '%Other Banks%' AND YEAR(`st`.`value_date`) = YEAR(CURRENT_DATE()) $condition GROUP BY MONTH(`st`.`value_date`) Order By `st`.`value_date` DESC LIMIT 12");
        if ($this->db->results() && $this->db->count > 0) {
            $results = $this->db->results();
            foreach ($results as $key => $value) {
                $org_data[(int)$value->month -1] = $value->statcount;
            }
            foreach ($org_data as $key => &$value) {
                if($value === null && $key < date("m")){
                    $value =0;
                }
            }
        } 
        $statsorg['stats'] = [array("name"=>"Unauthorized Transfer","data"=>$org_data)];


        $bog_infraction = 0;
        $this->db->query("SELECT SUM(ut.amount) AS amount, ut.account_from, ut.offset_account, ut.statement_id, ut.time, a.name, a.acc_num1, a.acc_num2, b.name bank_name FROM `unauthorized_transfers` AS ut INNER JOIN `accounts` AS a ON a.id = ut.account_from LEFT JOIN `banks` b ON b.id=a.bank WHERE `a`.`bank_type` LIKE '%Bank Of Ghana%' $condition ");
        if ($this->db->results() && $this->db->count > 0) {
            $bog_infraction = $this->db->first()->amount;
        }

        $totalbogunauth = 0;
        $this->db->query("SELECT COUNT(*) AS infraction_count, SUM(ut.amount) AS amount, ut.account_from, ut.offset_account, ut.statement_id, ut.time, a.name, a.acc_num1, a.acc_num2, b.name bank_name FROM `unauthorized_transfers` AS ut INNER JOIN `accounts` AS a ON a.id = ut.account_from INNER JOIN `banks` b ON b.id=a.bank WHERE `a`.`bank_type` LIKE '%Bank Of Ghana%' $condition GROUP BY `ut`.`account_from` Order By `ut`.`time`");
        $totalbogunauth = $this->db->count;

        $totalotherunauth = 0;
        $this->db->query("SELECT COUNT(*) AS infraction_count, SUM(ut.amount) AS amount, ut.account_from, ut.offset_account, ut.statement_id, ut.time, a.name, a.acc_num1, a.acc_num2, b.name bank_name FROM `unauthorized_transfers` AS ut INNER JOIN `accounts` AS a ON a.id = ut.account_from INNER JOIN `banks` b ON b.id=a.bank WHERE `a`.`bank_type` LIKE '%Other Banks%' $condition GROUP BY `ut`.`account_from` Order By `ut`.`time`");
        $totalotherunauth = $this->db->count;

        $org_infraction = 0;
        $this->db->query("SELECT SUM(ut.amount) AS amount, ut.account_from, ut.offset_account, ut.statement_id, ut.time, a.name, a.acc_num1, a.acc_num2, b.name bank_name FROM `unauthorized_transfers` AS ut INNER JOIN `accounts` AS a ON a.id = ut.account_from LEFT JOIN `banks` b ON b.id=a.bank WHERE `a`.`bank_type` LIKE '%Other Banks%' $condition ");
        if ($this->db->results() && $this->db->count > 0) {
            $org_infraction = $this->db->first()->amount;
        }

        $response['totalunauthbank'] = number_format((int) $totalbogunauth + (int) $totalotherunauth);
        $response['totaltunauth'] = number_format((double) $bog_infraction + (double) $org_infraction, 2);
        $response['otherunauth']  = number_format($org_infraction, 2);
        $response['bogunauth']  = number_format($bog_infraction, 2);
        $response['totalbogunauth']  = number_format($totalbogunauth);
        $response['totalotherunauth']  = number_format($totalotherunauth);
        $response['statsbog']  = $statsbog;
        $response['statsorg']  = $statsorg;

        return $response;
    }

    public function load_balance()
    {
        $this->db->query("SELECT id FROM `accounts`");
        foreach ($this->db->results() as $key => $acc) {
            $this->db->query("SELECT post_date,balance FROm statements WHERE account_id={$acc->id} ORDER BY id DESC");
            if ($this->db->results() && $this->db->count > 0) {
                $value = $this->db->first();
                echo $value->post_date;
                $this->db->query("INSERT INTO account_balance(account_id, amount, date) VALUES({$acc->id}, {$value->balance}, '{$value->post_date}') ON DUPLICATE KEY UPDATE amount={$value->balance}, date='{$value->post_date}'");
            }
        }
    }
}
