<?php
if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}
require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_RAT_GPR_CRN_UPDATE));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';

if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute
    $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_RAT_GPR_CRN_UPDATE));
    if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run
     try{
               $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>CRON_RAT_GPR_CRN_UPDATE)); // updating cron status in t_cron table
                $cardholderModel = new Corp_Ratnakar_Cardholders();
               // $respCnt = $cardholderModel->updateCRNforApprovedCustomer();
                $respCntGPR = $cardholderModel->updateCRNforApprovedGPRCustomer(PRODUCT_CONST_RAT_GPR);
                $respCntCNY = $cardholderModel->updateCRNforApprovedCNYCustomer(PRODUCT_CONST_RAT_CNY);
                $respCntSUR = $cardholderModel->updateCRNforApprovedSURCustomer(PRODUCT_CONST_RAT_SUR);
                if(!$respCntGPR) {
                    $respCntGPR = 0;
                }
                 if(!$respCntCNY) {
                    $respCntCNY = 0;
                }
                if(!$respCntSUR) {
                    $respCntSUR = 0;
                }
                $msg= 'Total '.$respCntGPR.' for Ratnakar GRP , '.$respCntCNY.' for CNERGYIS and '.$respCntSUR.' for Suryoday records updated';
                $updParam = array('cron_id'=>CRON_RAT_GPR_CRN_UPDATE, 'message'=>$msg, 'id'=>$cronLogId);
                $crons->updateCronLog($updParam);
                $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_RAT_GPR_CRN_UPDATE)); // updating cron status in t_cron table
      } catch(Exception $e){
        $msg = $e->getMessage();
        $param = array('cron_id'=>CRON_RAT_GPR_CRN_UPDATE, 'message'=>$msg, 'id'=>$cronLogId);
        $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_RAT_GPR_CRN_UPDATE)); // updating cron status in t_cron table
        $crons->updateCronLog($param);         
      }
    } else {
        $param = array('cron_id'=>CRON_RAT_GPR_CRN_UPDATE, 'message'=>'The cron is already '.$runStatus.' and does not have '.STATUS_COMPLETED.' status', 'id'=>$cronLogId);
        $crons->updateCronLog($param);
    }
}