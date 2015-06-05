<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_UPDATE_AGENT_CLOSING_BALANCE_ID));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';

if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute

    $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_UPDATE_AGENT_CLOSING_BALANCE_ID));
    //echo $cronLogId.'---------'; die;

if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run
    
$objAgentBal = new AgentBalance();
try{
    $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>CRON_UPDATE_AGENT_CLOSING_BALANCE_ID)); // updating cron status in t_cron table
    $resp = $objAgentBal->updateAgentClosingBalance();
    $msg= $resp.' agents balance have been added/updated in table t_agent_closing_balance for '.date('Y-m-d',strtotime('-1 days'));
    $param = array('cron_id'=>CRON_UPDATE_AGENT_CLOSING_BALANCE_ID, 'message'=>$msg, 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_UPDATE_AGENT_CLOSING_BALANCE_ID)); // updating cron status in t_cron table
} catch(Exception $e){
    $msg = $e->getMessage();
    $param = array('cron_id'=>CRON_UPDATE_AGENT_CLOSING_BALANCE_ID, 'message'=>$msg, 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_UPDATE_AGENT_CLOSING_BALANCE_ID)); // updating cron status in t_cron table
  }
} else {
    $param = array('cron_id'=>CRON_UPDATE_AGENT_CLOSING_BALANCE_ID, 'message'=>'That cron already '.$runStatus.' and does not has complete status', 'id'=>$cronLogId);
}

$crons->updateCronLog($param);
}