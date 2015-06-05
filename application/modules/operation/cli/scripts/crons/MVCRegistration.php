<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';

Zend_Session::start();

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_MVC_REGISTRATION_ID));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';

if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute

    $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_MVC_REGISTRATION_ID));
    //echo $cronLogId.'---------'; die;

if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run
    
$objMVC = new MVC();
try{
    $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>CRON_MVC_REGISTRATION_ID)); // updating cron status in t_cron table
    $resp = $objMVC->registerCardholder();
    if($resp['apiSettingError']!=''){
        $msg = 'MVC Registration cron could not run as '.$resp['apiSettingError'];
        App_Logger::log($msg, Zend_Log::ERR);
    } else {
             if($resp['chRegistered']>0)
                 $msg = $resp['chRegistered'].' MVC registration have been done successfully';
             if($resp['chNotRegistered']>0)
                 $msg = $resp['chNotRegistered'].' MVC registration have not been done successfully';
             if($resp['chFailed']>0)
                 $msg = $resp['chFailed'].' MVC registration have exceeded the maximum registration allowed limit and failed so intimated to ops by email.';
             if($msg==''){
                 $msg = 'There is no cardholder MVC registration required yet.';
             }
           
      }
    $param = array('cron_id'=>CRON_MVC_REGISTRATION_ID, 'message'=>$msg, 'id'=>$cronLogId);
   
    $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_MVC_REGISTRATION_ID)); // updating cron status in t_cron table
} catch(Exception $e){
    $msg = $e->getMessage();
    $param = array('cron_id'=>CRON_MVC_REGISTRATION_ID, 'message'=>$msg, 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_MVC_REGISTRATION_ID)); // updating cron status in t_cron table
    App_Logger::log($msg, Zend_Log::ERR);
  }
} else {
    $param = array('cron_id'=>CRON_MVC_REGISTRATION_ID, 'message'=>'That cron already '.$runStatus.' and does not has complete status', 'id'=>$cronLogId);
}

$crons->updateCronLog($param);
}