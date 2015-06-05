<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';

$objFP = new ForgotPassword();


$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_PASSWORD_REQUEST_INACTIVE_ID));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';

if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute

    $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_PASSWORD_REQUEST_INACTIVE_ID));

 if($runStatus==STATUS_COMPLETED) { // if cron execution's status is as 'complete' then only will run
        
    try{
        $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>CRON_PASSWORD_REQUEST_INACTIVE_ID)); // updating cron status in t_cron table
        
        $totalRows = $objFP->updateActiveRecords();
        //$totalRecs = count($agentArr);
        $msg= $totalRows.' '.STATUS_ACTIVE.'records have been made'.STATUS_INACTIVE;
        $param = array('cron_id'=>CRON_PASSWORD_REQUEST_INACTIVE_ID, 'message'=>$msg, 'id'=>$cronLogId);
        $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_PASSWORD_REQUEST_INACTIVE_ID)); // updating cron status in t_cron table
    } catch(Exception $e){
        $msg = $e->getMessage();
        $param = array('cron_id'=>CRON_PASSWORD_REQUEST_INACTIVE_ID, 'message'=>$msg, 'id'=>$cronLogId);
        $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_PASSWORD_REQUEST_INACTIVE_ID)); // updating cron status in t_cron table
      }
    } else {
        $param = array('cron_id'=>CRON_PASSWORD_REQUEST_INACTIVE_ID, 'message'=>'The cron is already '.$runStatus.' and does not have '.STATUS_COMPLETED.' status.', 'id'=>$cronLogId);
    }

    $crons->updateCronLog($param);
}