<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_REMITTANCE_TRANSACTION_RECON));
$activeStatus = isset($cronInfo['status']) ? $cronInfo['status'] : '';
$runStatus = isset($cronInfo['status_cron']) ? $cronInfo['status_cron'] : '';

if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute

$cronLogId = $crons->addCronLog(array('cron_id'=>CRON_REMITTANCE_TRANSACTION_RECON));

if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run
    
$objRatReq = new Remit_Ratnakar_Remittancerequest();
$objKotakReq = new Remit_Kotak_Remittancerequest();
 
try{
    $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>CRON_REMITTANCE_TRANSACTION_RECON)); // updating cron status in t_cron table
    $reqRatData = $objRatReq->getRatRemittanceTxnRecon();
    $reqData = $objKotakReq->getKotakRemittanceTxnRecon();
    $countReqs = $reqRatData + $reqData;
    $msg= $countReqs.' records have been added';
    $param = array('cron_id'=>CRON_REMITTANCE_TRANSACTION_RECON, 'message'=>$msg, 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_REMITTANCE_TRANSACTION_RECON)); // updating cron status in t_cron table
} catch(Exception $e){
    $msg = $e->getMessage();
    $param = array('cron_id'=>CRON_REMITTANCE_TRANSACTION_RECON, 'message'=>$msg, 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_REMITTANCE_TRANSACTION_RECON)); // updating cron status in t_cron table
  }
} else {
    $param = array('cron_id'=>CRON_REMITTANCE_TRANSACTION_RECON, 'message'=>'That cron already '.$runStatus.' and does not has complete status', 'id'=>$cronLogId);
}

$crons->updateCronLog($param);
}