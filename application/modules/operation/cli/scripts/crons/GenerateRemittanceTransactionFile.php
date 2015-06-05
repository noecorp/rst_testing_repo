<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_REMITTANCE_TRANSACTION_FILE));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';

if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute

    $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_REMITTANCE_TRANSACTION_FILE));

    if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run

    $objReports = new Remit_Reports();

    try{
        $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>CRON_REMITTANCE_TRANSACTION_FILE)); // updating cron status in t_cron table
        $fileResp = $objReports->generateRemittanceTransactionFile();

        $msg= $fileResp.' files generated successfully';
        $param = array('cron_id'=>CRON_REMITTANCE_TRANSACTION_FILE, 'message'=>$msg, 'id'=>$cronLogId);
        $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_REMITTANCE_TRANSACTION_FILE)); // updating cron status in t_cron table
    } catch(Exception $e){
        $msg = $e->getMessage();
        $param = array('cron_id'=>CRON_REMITTANCE_TRANSACTION_FILE, 'message'=>$msg, 'id'=>$cronLogId);
        $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_REMITTANCE_TRANSACTION_FILE)); // updating cron status in t_cron table
      }
    } else {
        $param = array('cron_id'=>CRON_REMITTANCE_TRANSACTION_FILE, 'message'=>'That cron already '.$runStatus.' and does not has complete status', 'id'=>$cronLogId);
    }

    $crons->updateCronLog($param);
}