<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cron_id = CRON_BOI_DISB_FILE_GENRATOR;
$cronInfo = $crons->getCronInfo(array('cron_id'=>$cron_id));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';

if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute
    $now = new Zend_Db_Expr('NOW()');
    $cronLogId = $crons->addCronLog(array('cron_id'=>$cron_id));

if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'completed' then only will run
    

 
try{
    $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>$cron_id)); // updating cron status in t_cron table
    $disFile = new Corp_Boi_DisbursementFile();
    // Corporate Load
    $cardLoadResp = $disFile->generateBOIDisbursementFile();
    
    
    $msg = $cardLoadResp['count'].' cards loaded successfully' . PHP_EOL;
    $msg .= $cardLoadResp['file'].' files generated successfully';
    $param = array('cron_id'=>$cronLogId, 'message'=>$msg, 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>$cron_id)); // updating cron status in t_cron table
    $exceptions = isset($cardLoadResp['exception']) ? $cardLoadResp['exception'] : '';
    $countExcep = count($exceptions);
    if($countExcep>0 && !empty($exceptions)){
        foreach($exceptions as $key=>$val){
                $cronData = array(
                                   'cron_id'=>$cron_id,
                                   'message'=>$val,
                                   'date_start'=>$now,
                                   'date_end'=>new Zend_Db_Expr('NOW()'),
                                 );
                
                $logResp = $crons->addCronLog($cronData);
        }
    }
} catch(Exception $e){
    //echo '<pre>';print_r($e);exit;
    $msg = $e->getMessage();
    $param = array('cron_id'=>$cron_id, 'message'=>$msg, 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>$cron_id)); // updating cron status in t_cron table
  }
} else {
    $param = array('cron_id'=>$cron_id, 'message'=>'This cron is already '.$runStatus.' and does not has complete status', 'id'=>$cronLogId);
}

    $crons->updateCronLog($param);
}
