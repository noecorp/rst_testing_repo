<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}
//echo CRON_PATH . '/cli.php'; exit;
require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_BOI_NSDC_GENERATE_TP_MIS));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';
$now = new Zend_Db_Expr('NOW()');
 
if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute
   $custModel = new Corp_Boi_Customers();
    
   if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run
    try{
        $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_BOI_NSDC_GENERATE_TP_MIS));
        $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>CRON_BOI_NSDC_GENERATE_TP_MIS)); // updating cron status in t_cron table
        //echo $cronLogId.'---------'; die;

         // updating in db for their status
        $fileResp = $custModel->generateTpMisFile();

        
        ///$countReqs = count($remitReqData);
        $msg = $fileResp.' files generated successfully';
        $param = array('cron_id'=>CRON_BOI_NSDC_GENERATE_TP_MIS, 'message'=>$msg, 'id'=>$cronLogId);
        $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_BOI_NSDC_GENERATE_TP_MIS)); // updating cron status in t_cron table
        $exceptions = $clearLoadResp['exception'];
        $countExcep = count($exceptions);
        if($countExcep>0){
            foreach($exceptions as $key=>$val){
                    $cronData = array(
                                       'cron_id'=>CRON_BOI_NSDC_GENERATE_TP_MIS,
                                       'message'=>$val,
                                       'date_start'=>$now,
                                       'date_end'=>new Zend_Db_Expr('NOW()'),
                                     );

                    $logResp = $crons->addCronLog($cronData);
            }
        }
    } catch(Exception $e){
        $msg = $e->getMessage();
        $param = array('cron_id'=>CRON_BOI_NSDC_GENERATE_TP_MIS, 'message'=>$msg, 'id'=>$cronLogId);
        $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_BOI_NSDC_GENERATE_TP_MIS)); // updating cron status in t_cron table
      }
} else {
        $param = array('cron_id'=>CRON_BOI_NSDC_GENERATE_TP_MIS, 'message'=>'The cron is already '.$runStatus.' and does not have '.STATUS_COMPLETED.' status', 'id'=>$cronLogId);
        $crons->updateCronLog($param);
    }
    
  $crons->updateCronLog($param);
}

    
