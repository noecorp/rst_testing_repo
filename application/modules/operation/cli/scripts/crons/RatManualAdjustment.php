<?php
if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}
require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_RAT_MANUAL_ADJUSTMENT));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';

if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute
    $now = new Zend_Db_Expr('NOW()');
    $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_RAT_MANUAL_ADJUSTMENT));

if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'completed' then only will run
    
$objMA = new BatchAdjustment();
 
try{
    $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>CRON_RAT_MANUAL_ADJUSTMENT)); // updating cron status in t_cron table
   
    $maRequestCorp = $objMA->batchAdjustmentProductWise(PRODUCT_CONST_RAT_MEDI);
    $maRequestCNY = $objMA->batchAdjustmentProductWise(PRODUCT_CONST_RAT_CNY);
    $maRequestSUR = $objMA->batchAdjustmentProductWise(PRODUCT_CONST_RAT_SUR);
    $maRequestGPR = $objMA->batchAdjustmentProductWise(PRODUCT_CONST_RAT_GPR);
    $maRequestHAP = $objMA->batchAdjustmentProductWise(PRODUCT_CONST_RAT_HAP);    
    $maRequestCOP = $objMA->batchAdjustmentProductWise(PRODUCT_CONST_RAT_COP);    
    $msg = $maRequestCorp.$maRequestCNY.$maRequestSUR.$maRequestGPR.$maRequestHAP.$maRequestCOP;
    $param = array('cron_id'=>CRON_RAT_MANUAL_ADJUSTMENT, 'message'=>$msg, 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_RAT_MANUAL_ADJUSTMENT)); // updating cron status in t_cron table
    
    $msg = $maRequestCorp.' '.$maRequestCNY.' '.$maRequestSUR;    
    
} catch(Exception $e){
    $msg = $e->getMessage();
    $param = array('cron_id'=>CRON_RAT_MANUAL_ADJUSTMENT, 'message'=>$msg, 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_RAT_MANUAL_ADJUSTMENT)); // updating cron status in t_cron table
  }
} else {
    $param = array('cron_id'=>CRON_RAT_MANUAL_ADJUSTMENT, 'message'=>'That cron already '.$runStatus.' and does not has complete status', 'id'=>$cronLogId);
}

    $crons->updateCronLog($param);
}
