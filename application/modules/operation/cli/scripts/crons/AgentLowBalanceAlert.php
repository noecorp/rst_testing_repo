<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_AGENT_LOW_BALANCE_ALERT_ID));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';

if($activeStatus==STATUS_ACTIVE) { // if cron active then only cron should execute
    $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_AGENT_LOW_BALANCE_ALERT_ID));
          
if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run
    
$objAgentUser = new AgentUser();
try{
    $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>CRON_AGENT_LOW_BALANCE_ALERT_ID)); // updating cron status in t_cron table
    $resp = $objAgentUser->sendLowBalanceAlert();
    $msg= $resp.' agents low balance alert have been sent';
    $param = array('cron_id'=>CRON_AGENT_LOW_BALANCE_ALERT_ID, 'message'=>$msg, 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_AGENT_LOW_BALANCE_ALERT_ID)); // updating cron status in t_cron table
} catch(Exception $e){
    $param = array('cron_id'=>CRON_AGENT_LOW_BALANCE_ALERT_ID, 'message'=>$e->getMessage(), 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_AGENT_LOW_BALANCE_ALERT_ID)); // updating cron status in t_cron table
  }
} else {
    $param = array('cron_id'=>CRON_AGENT_LOW_BALANCE_ALERT_ID, 'message'=>'That cron already '.$runStatus.' and does not has complete status', 'id'=>$cronLogId);
}

  $crons->updateCronLog($param);
}