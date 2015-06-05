<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_RAT_NEFT_RESPONSE_SEND_SMS_ID));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';

if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute

    $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_RAT_NEFT_RESPONSE_SEND_SMS_ID));
    //echo $cronLogId.'---------'; die;

if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run
    
    $objRemittanceReq = new Remit_Ratnakar_Remittancerequest();

    try{
        // ASSUMING DUMMY ARRAY RIGHT NOW FOR PROCESSING CODE, BUT LATER ON WILL BE DATA FROM FILE
        $responseData = $objRemittanceReq->getPendingSmsRecords();

        $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>CRON_RAT_NEFT_RESPONSE_SEND_SMS_ID)); // updating cron status in t_cron table

        // updating in db for neft response
        $countResp = $objRemittanceReq->sendNeftSms($responseData);

        //$msg= $countResp.' remitter NEFT responses have been updated in table t_remittance_request and t_remittance_status log';
        $msg= $countResp['success'].' success remittance SMS and '.$countResp['failure'].' failure SMS have been sent';
        $param = array('cron_id'=>CRON_RAT_NEFT_RESPONSE_SEND_SMS_ID, 'message'=>$msg, 'id'=>$cronLogId);
        $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_RAT_NEFT_RESPONSE_SEND_SMS_ID)); // updating cron status in t_cron table
    } catch(Exception $e){
        $msg = $e->getMessage();
        $param = array('cron_id'=>CRON_RAT_NEFT_RESPONSE_SEND_SMS_ID, 'message'=>$msg, 'id'=>$cronLogId);
        $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_RAT_NEFT_RESPONSE_SEND_SMS_ID)); // updating cron status in t_cron table
      }
} else {
    $param = array('cron_id'=>CRON_RAT_NEFT_RESPONSE_SEND_SMS_ID, 'message'=>'That cron already '.$runStatus.' and does not has complete status', 'id'=>$cronLogId);
}

$crons->updateCronLog($param);
}