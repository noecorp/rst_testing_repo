<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_CUSTOM_SMS));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';
$now = new Zend_Db_Expr('NOW()');
 
if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute
   $auth = new AuthRequest();
    
   if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run
    try{
        $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_CUSTOM_SMS));
        
        $ref = new Reference();
        $ref->sendCustomSMS();
        $msg = ' SMS Sent ';
        $param = array('cron_id'=>CRON_CUSTOM_SMS, 'message'=>$msg, 'id'=>$cronLogId);
        $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_CUSTOM_SMS)); // updating cron status in t_cron table
    } catch(Exception $e){
        $msg = $e->getMessage();
        $param = array('cron_id'=>CRON_CUSTOM_SMS, 'message'=>$msg, 'id'=>$cronLogId);
        $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_CUSTOM_SMS)); // updating cron status in t_cron table
      }
} else {
        $param = array('cron_id'=>CRON_CUSTOM_SMS, 'message'=>'The cron is already '.$runStatus.' and does not have '.STATUS_COMPLETED.' status', 'id'=>$cronLogId);
        $crons->updateCronLog($param);
    }
    
  $crons->updateCronLog($param);
}