<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'configs/background.conf';

$db = new DB();
$date = new Date();

$argv = $_SERVER['argv'];
$jobid = $argv[1];
$account_id = $argv[2];

$excelData = array();
$excelHeader = array();
$excelDataToClean = array();
$excelDataToInsert = array();

function get_colume($data){
    if(trim(strtolower($data)) === "transaction date"){
        $data = "Post Date";
    }
    if(trim(strtolower($data)) === "description"){
        $data = "Particulars";
    }
    if(strpos(strtolower($data), "debit")!==false){
        $data = "Debit Amount";
    }
    if(strpos(strtolower($data), "credit")!==false){
        $data = "Credit Amount";
    }
    $data = str_replace(' ', '_', $data);
    $data = str_replace('-', '_', $data);
    $data = str_replace('.', '', $data);
    return  trim(strtolower($data));
}

function updateStatus($status, $desc,$jobid){
    $time = date("H:i:s");
    echo "\n\ntime:$time\n$desc\nJob Id: $jobid\n";
    $db = new DB();
    $data = array(
        'status' =>  $status,
        'description' =>  $desc,
    );
    $db->updateByID("file_upload_status", "id", $jobid, $data);
}

$db->query("SELECT * FROM `file_upload_status` WHERE `id`=$jobid LIMIT 1");
if($db->results() && $db->count > 0){
    $job = $db->first();
    
    try {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($job->path);
        $spreadsheet->setActiveSheetIndex(0);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        unlink($job->path);

        $excelHeader = $sheetData[1];
        unset($sheetData[1]);
        $excelData = $sheetData;
        
        foreach ($excelData as $key => $data) {
            $arrayData = array();
            $arrayData[get_colume($excelHeader["A"])] = $data["A"];
            $arrayData[get_colume($excelHeader["B"])] = $data["B"];
            $arrayData[get_colume($excelHeader["C"])] = $data["C"];
            $arrayData[get_colume($excelHeader["D"])] = $data["D"];
            $arrayData[get_colume($excelHeader["E"])] = $data["E"];
            $arrayData[get_colume($excelHeader["F"])] = $data["F"];
            $arrayData[get_colume($excelHeader["G"])] = $data["G"];
            $arrayData[get_colume($excelHeader["H"])] = $data["H"];
            $arrayData["location"] = "$key";
            array_push($excelDataToClean, $arrayData);
        }

        updateStatus("Checking file", "File read successfully",$jobid);
        //Clean excel data // reference - duplicate's lines
        $fileCheckError = false;
        $lastKey = 0;
        foreach ($excelDataToClean as $key => $dataToClean) {
            if($dataToClean['post_date']===NULL){
                $excelDataToInsert[$lastKey-1]['particulars'] .= " ".$dataToClean['particulars'];
                continue;
            }
            if((int)$dataToClean['debit_amount'] >0 && (int)$dataToClean['credit_amount']>0){
                $fileCheckError = true;
                updateStatus("Error", "Error: Cannot have both credit and debit in same transaction statement: On line  $dataToClean[location]",$jobid);
            }
            $arrayData = array();
            $arrayData["account_id"] = $account_id;
            $arrayData["post_date"] = $date->sql_date($dataToClean['post_date']);
            $arrayData["particulars"] = $dataToClean['particulars'];
            $arrayData["reference"] = $dataToClean['reference'];
            $arrayData["value_date"] = $date->sql_date($dataToClean['value_date']);
            $arrayData["debit_amount"] = (double)str_replace(',', '', $dataToClean['debit_amount'] );
            $arrayData["credit_amount"] = (double)str_replace(',', '', $dataToClean['credit_amount'] );
            $arrayData["balance"] = (double)str_replace(',', '', $dataToClean['balance'] );
            $arrayData["offset_acc_no"] = str_replace('-', '', $dataToClean['offset_acc_no'] );
            array_push($excelDataToInsert, $arrayData);
            $lastKey++;
        }
        if($fileCheckError){
            exit();
        }
        
        updateStatus("Validating Statement", "Done checking file",$jobid);

        // var_dump($excelDataToInsert);exit;
        //get last balance and check agais the first balance in the excel.
        //using the credit or debit balance do a  subtraction or addition and check if 
        //the balance match
        $cmpData = $excelDataToInsert[0];
        $postDate = $cmpData['post_date'];
        $debit_amount = $cmpData['debit_amount'];
        $credit_amount = $cmpData['credit_amount'];
        $balance = $cmpData['balance'];

        $db->query("SELECT * FROM `statements` WHERE `account_id`=$account_id ORDER BY `id` DESC");
        if($db->results() && $db->count > 0){
            $result = $db->first();
            if($result->post_date === $postDate){
                updateStatus("Error", "Error In statemen. Date already exist. (".$date->sql_date($postDate).") for this account",$jobid);
            }else if($debit_amount > 0 && ($result->balance - $debit_amount) != $balance){
                updateStatus("Error", "Error: Opening balance is incorrect for the uploaded statement. closing balance: {$result->balance}, opening balance: {$balance}",$jobid);
            }else if($credit_amount > 0 && ($result->balance - $debit_amount) != $balance){
                updateStatus("Error", "Error: Opening balance is incorrect for the uploaded statement. closing balance: {$result->balance}, opening balance: {$balance}",$jobid);
            }else{
                updateStatus("Creating Transactions", "Validation successful",$jobid);
                foreach ($excelDataToInsert as $key => $data) {
                    $db->insert('statements', $data);
                }
                updateStatus("completed", "All done",$jobid);
            }
        }else{
            updateStatus("Creating Transactions", "Validation successful",$jobid);
            foreach ($excelDataToInsert as $key => $data) {
                $db->insert('statements', $data);
            }
            updateStatus("completed", "All done",$jobid);
        }
    } catch (Exception $ex) {
        unlink($job->path);
        updateStatus("Error","Error (" . $ex->getMessage() . ")",$jobid);
    }
}else{
    updateStatus("Error","Error, job not found",$jobid);
}