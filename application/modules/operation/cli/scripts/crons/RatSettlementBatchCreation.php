<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}
//echo CRON_PATH . '/cli.php'; exit;
require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_RAT_SETTLEMENT_BATCH_CREATION_ID));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';

if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute

    $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_RAT_SETTLEMENT_BATCH_CREATION_ID));
    //echo $cronLogId.'---------'; die;

    if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run
    
         
    $objLoad = new Corp_Ratnakar_Cardload();
    $objNetReq = new Remit_Ratnakar_NeftRequest();
    $batchName='not';
    $i=0;
    $msg='';
    $requestsCount=0;
    
    $batchDate =  new Zend_Db_Expr('NOW()'); ;//curdate;
    $debitDate = date("Y-m-d", strtotime("-1 day")); // yesterday;
 //    $debitDate = date('Y-m-d',strtotime('2015-01-28'));
    //print '<pre>';
    try{
        $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>CRON_RAT_SETTLEMENT_BATCH_CREATION_ID)); // updating cron status in t_cron table
      //  while($batchName!=''){
                $i = $i+1;
                  $unsettlementRecords =  $objLoad->getRatRequestsForSettlementBatch($params=array('type'=>'WITHOUT_CARD','batch_date'=>$batchDate,'debit_date'=>$debitDate));
                  //
                if(!empty($unsettlementRecords)){
                    try{
                        $batchName = $unsettlementRecords['batch_name'];
                         if($i!=1) 
                           $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_RAT_SETTLEMENT_BATCH_CREATION_ID));
                         
                        $getUnsettleRecords = $objLoad->setRatSettlementRecordsBatch($unsettlementRecords);
                        $requestsCount = count($getUnsettleRecords);
                        $createFileResp = $objNetReq->downloadUnsettlementTxt($batchName, $getUnsettleRecords,$createTxtFile=true, $downloadTxtFile=false, $filePermission=0755, FILE_CSV);
                        
                        $updateUnsettleRecords = $objLoad->UpdateRatRequestsForSettlementBatch($unsettlementRecords,$getUnsettleRecords);
                    }catch (App_Exception $e) {
                    $msg = $e->getMessage();
                    $param = array('cron_id'=>CRON_RAT_SETTLEMENT_BATCH_CREATION_ID, 'message'=>$msg, 'id'=>$cronLogId);
                    $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_RAT_SETTLEMENT_BATCH_CREATION_ID)); // updating cron status in t_cron table
                    $crons->updateCronLog($param); 
                    }

                }
                
                $unsettlementRecords =  $objLoad->getRatRequestsForSettlementBatch($params=array('type'=>'WITH_CARD','batch_date'=>$batchDate,'debit_date'=>$debitDate));
                  
                  //
                if(!empty($unsettlementRecords)){
                    try{
                        $batchName = $unsettlementRecords['batch_name'];
                         if($i!=1) 
                           $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_RAT_SETTLEMENT_BATCH_CREATION_ID));

                         $getUnsettleRecords = $objLoad->setRatSettlementRecordsBatch($unsettlementRecords);
                        $requestsCount = count($getUnsettleRecords);
                        $createFileResp = $objNetReq->downloadUnsettlementTxt($batchName, $getUnsettleRecords,$createTxtFile=true, $downloadTxtFile=false, $filePermission=0755, FILE_CSV);
                        
                        $updateUnsettleRecords = $objLoad->UpdateRatRequestsForSettlementBatch($unsettlementRecords,$getUnsettleRecords);
                       

                    }catch (App_Exception $e) {
                    $msg = $e->getMessage();
                    $param = array('cron_id'=>CRON_RAT_SETTLEMENT_BATCH_CREATION_ID, 'message'=>$msg, 'id'=>$cronLogId);
                    $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_RAT_SETTLEMENT_BATCH_CREATION_ID)); // updating cron status in t_cron table
                    $crons->updateCronLog($param); 
                    }

                }
                
                $msg= $requestsCount.' instructions set for unsettlement batch and unsettlement batch file ('.$batchName.') created';
                        $updParam = array('cron_id'=>CRON_RAT_SETTLEMENT_BATCH_CREATION_ID, 'message'=>$msg, 'id'=>$cronLogId);
                         if( ($batchName!='') || ($i==1) ) {
                                $crons->updateCronLog($updParam);
                            }
           // }
        
        $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_RAT_SETTLEMENT_BATCH_CREATION_ID)); // updating cron status in t_cron table
        
    } catch(Exception $e){
        $msg = $e->getMessage();
        App_Logger::log($e, Zend_Log::WARN);
        $param = array('cron_id'=>CRON_RAT_SETTLEMENT_BATCH_CREATION_ID, 'message'=>$msg, 'id'=>$cronLogId);
        $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_RAT_SETTLEMENT_BATCH_CREATION_ID)); // updating cron status in t_cron table
        $crons->updateCronLog($param);         
      }
    } else {
        $param = array('cron_id'=>CRON_RAT_SETTLEMENT_BATCH_CREATION_ID, 'message'=>'The cron is already '.$runStatus.' and does not have '.STATUS_COMPLETED.' status', 'id'=>$cronLogId);
        $crons->updateCronLog($param);
    }

    
}