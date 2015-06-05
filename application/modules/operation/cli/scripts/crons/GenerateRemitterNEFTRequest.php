<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_GENERATE_REMITTER_NEFT_REQUEST_ID));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';

if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute

    $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_GENERATE_REMITTER_NEFT_REQUEST_ID));
    //echo $cronLogId.'---------'; die;

if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run
    
$objRemittanceReq = new Remit_Remittancerequest();
 
try{
    $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>CRON_GENERATE_REMITTER_NEFT_REQUEST_ID)); // updating cron status in t_cron table
    $remitReqData = $objRemittanceReq->getRemitterRequestsForNEFT();
    $createNeft = '';// some function will call lator here to create file for neft
   
    // updating in db for their status
    $updData = $objRemittanceReq->updateRemitterRequestsForNEFT($remitReqData);
    
    $countReqs = count($remitReqData);
    $msg= $countReqs.' remitter requests have been processed for NEFT';
    $param = array('cron_id'=>CRON_GENERATE_REMITTER_NEFT_REQUEST_ID, 'message'=>$msg, 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_GENERATE_REMITTER_NEFT_REQUEST_ID)); // updating cron status in t_cron table
} catch(Exception $e){
    $msg = $e->getMessage();
    $param = array('cron_id'=>CRON_GENERATE_REMITTER_NEFT_REQUEST_ID, 'message'=>$msg, 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_GENERATE_REMITTER_NEFT_REQUEST_ID)); // updating cron status in t_cron table
  }
} else {
    $param = array('cron_id'=>CRON_GENERATE_REMITTER_NEFT_REQUEST_ID, 'message'=>'That cron already '.$runStatus.' and does not has complete status', 'id'=>$cronLogId);
}

$crons->updateCronLog($param);
}