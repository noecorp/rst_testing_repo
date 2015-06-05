<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_REMOVE_INCOMPLETE_AGENTS_CARDHOLDERS));

$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';

if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute

    $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_REMOVE_INCOMPLETE_AGENTS_CARDHOLDERS));
    //echo $cronLogId.'---------'; die;

if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run
    
try{
    $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>CRON_REMOVE_INCOMPLETE_AGENTS_CARDHOLDERS)); // updating cron status in t_cron table
    $resp = $crons->removeIncompleteAgentsCardholders();
    $msg = $resp['agentsRemoved']." agents and ".$resp['cardholdersRemoved'] ." cardholders have been removed from db successfully.";
    $param = array('cron_id'=>CRON_REMOVE_INCOMPLETE_AGENTS_CARDHOLDERS, 'message'=>$msg, 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_REMOVE_INCOMPLETE_AGENTS_CARDHOLDERS)); // updating cron status in t_cron table
} catch(Exception $e){
    $msg = $e->getMessage();
    $param = array('cron_id'=>CRON_REMOVE_INCOMPLETE_AGENTS_CARDHOLDERS, 'message'=>$msg, 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_REMOVE_INCOMPLETE_AGENTS_CARDHOLDERS)); // updating cron status in t_cron table
    App_Logger::log('RemoveIncompleteAgentsCardholders cron :- '.$msg, Zend_Log::ERR);
  }
} else {
    $param = array('cron_id'=>CRON_REMOVE_INCOMPLETE_AGENTS_CARDHOLDERS, 'message'=>'That cron already '.$runStatus.' and does not has complete status', 'id'=>$cronLogId);
}

$crons->updateCronLog($param);
}