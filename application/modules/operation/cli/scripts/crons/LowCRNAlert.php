<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_LOW_CRN_ALERT_ID));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';

if($activeStatus==STATUS_ACTIVE) { // if cron active then only cron should execute
    $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_LOW_CRN_ALERT_ID));
          
if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run
    
$objECS = new ECS();
try{
    $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>CRON_LOW_CRN_ALERT_ID)); // updating cron status in t_cron table
    $freeCRNCount = $objECS->sendLowCRNAlert();
    $countRequired = App_DI_Container::get('ConfigObject')->cron->crn->count_required;
    if($freeCRNCount<$countRequired){
        $msg= $freeCRNCount.' free CRN left in system, '.$countRequired.' CRN minimum required, so email alert has sent to ops admin';
    }else {
        $msg= $freeCRNCount.' free CRN left in system which is more than minimum CRN('.$countRequired.') required, so no email alert sent';
    }
    $param = array('cron_id'=>CRON_LOW_CRN_ALERT_ID, 'message'=>$msg, 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_LOW_CRN_ALERT_ID)); // updating cron status in t_cron table
} catch(Exception $e){
    $param = array('cron_id'=>CRON_LOW_CRN_ALERT_ID, 'message'=>$e->getMessage(), 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_LOW_CRN_ALERT_ID)); // updating cron status in t_cron table
  }
} else {
    $param = array('cron_id'=>CRON_LOW_CRN_ALERT_ID, 'message'=>'Low CRN Alert cron already '.$runStatus.' and does not has complete status', 'id'=>$cronLogId);
}
  App_Logger::log($param['message']);
  $crons->updateCronLog($param);
}