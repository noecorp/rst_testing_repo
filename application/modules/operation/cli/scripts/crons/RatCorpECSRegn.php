<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}
//echo CRON_PATH . '/cli.php'; exit;
require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_RAT_CORP_ECS_REGN));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';

if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute

    $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_RAT_CORP_ECS_REGN));
    //echo $cronLogId.'---------'; die;

    if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run

    
    try{
               $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>CRON_RAT_CORP_ECS_REGN)); // updating cron status in t_cron table
               
                $cardholderModel = new Corp_Ratnakar_Cardholders();
                $ECSResponse = $cardholderModel->ratMediAssistECSRegn();
                $ECSGRPResponse = $cardholderModel->ratGPRECSRegn();
                $ECSCNYResponse = $cardholderModel->ratECSRegn(PRODUCT_CONST_RAT_CNY);
                $ECSSURResponse = $cardholderModel->ratECSRegn(PRODUCT_CONST_RAT_SUR);

                $msg= 'Total '.$ECSResponse.' Medi Assist and '.$ECSGRPResponse.' GPR records sent for ECS registration and '.$ECSCNYResponse.' CNY records sent for ECS registration and '.$ECSSURResponse.' SUR records sent for ECS registration';
                $updParam = array('cron_id'=>CRON_RAT_CORP_ECS_REGN, 'message'=>$msg, 'id'=>$cronLogId);
                
                $crons->updateCronLog($updParam);
                
                $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_RAT_CORP_ECS_REGN)); // updating cron status in t_cron table
        
    } catch(Exception $e){
        $msg = $e->getMessage();
        $param = array('cron_id'=>CRON_RAT_CORP_ECS_REGN, 'message'=>$msg, 'id'=>$cronLogId);
        $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_RAT_CORP_ECS_REGN)); // updating cron status in t_cron table
        $crons->updateCronLog($param);         
      }
    } else {
        $param = array('cron_id'=>CRON_RAT_CORP_ECS_REGN, 'message'=>'The cron is already '.$runStatus.' and does not have '.STATUS_COMPLETED.' status', 'id'=>$cronLogId);
        $crons->updateCronLog($param);
    }

    
}