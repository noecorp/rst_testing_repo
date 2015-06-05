<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}
//echo CRON_PATH . '/cli.php'; exit;
require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_NEFT_BATCH_CREATION_ID));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';

if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute

    $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_NEFT_BATCH_CREATION_ID));
    //echo $cronLogId.'---------'; die;

    if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run

    $objRemittanceReq = new Remit_Remittancerequest();
    $objNetReq = new Remit_Boi_NeftRequest();
    $batchName='not';
    $i=0;
    $msg='';
    $requestsCount=0;
    //print '<pre>';
    try{
        $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>CRON_NEFT_BATCH_CREATION_ID)); // updating cron status in t_cron table
        while($batchName!=''){
                $i = $i+1;
                $batchName = $objRemittanceReq->updateRemitRequestsForNEFTBatch();
                
                if($batchName!=''){
                    
                    if($i!=1) 
                       $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_NEFT_BATCH_CREATION_ID));
                    
                    $batchArr =  $objRemittanceReq->getBatchRecords($batchName);
                    $requestsCount = count($batchArr);
                    $createFileResp = $objNetReq->downloadNeftTxt($batchName, $batchArr);
                }

                $msg= $requestsCount.' instructions set for neft batch and neft batch file ('.$batchName.') created';
                $updParam = array('cron_id'=>CRON_NEFT_BATCH_CREATION_ID, 'message'=>$msg, 'id'=>$cronLogId);
                
                if( ($batchName!='') || ($i==1) ) {
                    $crons->updateCronLog($updParam);
                }
        }
        
        $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_NEFT_BATCH_CREATION_ID)); // updating cron status in t_cron table
        
    } catch(Exception $e){
        $msg = $e->getMessage();
        $param = array('cron_id'=>CRON_NEFT_BATCH_CREATION_ID, 'message'=>$msg, 'id'=>$cronLogId);
        $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_NEFT_BATCH_CREATION_ID)); // updating cron status in t_cron table
        $crons->updateCronLog($param);         
      }
    } else {
        $param = array('cron_id'=>CRON_NEFT_BATCH_CREATION_ID, 'message'=>'The cron is already '.$runStatus.' and does not have '.STATUS_COMPLETED.' status', 'id'=>$cronLogId);
        $crons->updateCronLog($param);
    }

    
}