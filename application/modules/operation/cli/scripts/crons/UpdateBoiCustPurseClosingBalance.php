<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_BOI_UPDATE_CUSTOMER_PURSE_CLOSING_BALANCE));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';

if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute

    $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_BOI_UPDATE_CUSTOMER_PURSE_CLOSING_BALANCE));
    //echo $cronLogId.'---------'; die;

if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run
    
$objCustBal = new CustPurseBalance();
try{
    $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>CRON_BOI_UPDATE_CUSTOMER_PURSE_CLOSING_BALANCE)); // updating cron status in t_cron table
    // Bank Unicode
    
    
    
    

    
    $bankBoi = App_DI_Definition_Bank::getInstance(BANK_BOI);
    $bankBoiUnicode = $bankBoi->bank->unicode;
    $respBoi = $objCustBal->updateCustPurseClosingbalance(array('bank_unicode' => $bankBoiUnicode));
    
   
    $msg= $respBoi.' customer purse balance have been added/updated in table for BOI Customer Purse on '.date('Y-m-d',strtotime('-1 days'));
    
    $param = array('cron_id'=>CRON_BOI_UPDATE_CUSTOMER_PURSE_CLOSING_BALANCE, 'message'=>$msg, 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_BOI_UPDATE_CUSTOMER_PURSE_CLOSING_BALANCE)); // updating cron status in t_cron table
} catch(Exception $e){
    $msg = $e->getMessage();
    $param = array('cron_id'=>CRON_BOI_UPDATE_CUSTOMER_PURSE_CLOSING_BALANCE, 'message'=>$msg, 'id'=>$cronLogId);
    $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_BOI_UPDATE_CUSTOMER_PURSE_CLOSING_BALANCE)); // updating cron status in t_cron table
  }
} else {
    $param = array('cron_id'=>CRON_BOI_UPDATE_CUSTOMER_PURSE_CLOSING_BALANCE, 'message'=>'That cron already '.$runStatus.' and does not has complete status', 'id'=>$cronLogId);
}

$crons->updateCronLog($param);
}