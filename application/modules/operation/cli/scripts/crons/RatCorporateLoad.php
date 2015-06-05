<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_RAT_CORPORATE_LOAD));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';

if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute
    $now = new Zend_Db_Expr('NOW()');
    $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_RAT_CORPORATE_LOAD));

//if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'completed' then only will run
    
$objLoad = new Corp_Ratnakar_Cardload();
 
try{
    $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>CRON_RAT_CORPORATE_LOAD)); // updating cron status in t_cron table
   
    // Corporate Load
    $cardLoadResp = $objLoad->doCorporateLoad();
    
    $msg = $cardLoadResp['loaded'].' cards loaded successfully and '.$cardLoadResp['not_loaded'].' cards not loaded';
    $param = array('cron_id'=>CRON_RAT_CORPORATE_LOAD, 'message'=>$msg, 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_RAT_CORPORATE_LOAD)); // updating cron status in t_cron table
    $exceptions = $cardLoadResp['exception'];
    $countExcep = count($exceptions);
    if($countExcep>0){
        foreach($exceptions as $key=>$val){
                $cronData = array(
                                   'cron_id'=>CRON_RAT_CORPORATE_LOAD,
                                   'message'=>$val,
                                   'date_start'=>$now,
                                   'date_end'=>new Zend_Db_Expr('NOW()'),
                                 );
                
                $logResp = $crons->addCronLog($cronData);
        }
    }
} catch(Exception $e){
    $msg = $e->getMessage();
    $param = array('cron_id'=>CRON_RAT_CORPORATE_LOAD, 'message'=>$msg, 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_RAT_CORPORATE_LOAD)); // updating cron status in t_cron table
  }
} else {
    $param = array('cron_id'=>CRON_RAT_CORPORATE_LOAD, 'message'=>'This cron is already '.$runStatus.' and does not has complete status', 'id'=>$cronLogId);
}

//$crons->updateCronLog($param);
//}
