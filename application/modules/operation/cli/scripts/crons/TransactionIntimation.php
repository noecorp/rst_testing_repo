<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';

$crons = new Crons();
//$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_TRANSACTION_INFORMATION));
//$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
//$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';
//$now = new Zend_Db_Expr('NOW()');
 
//if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute
   $auth = new AuthRequest();
    
  // if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run
    try{
     //   $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_PURSE_CUT_OFF_VALIDATION));
        //echo $cronLogId.'---------'; die;

        $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CORP);
        $productCode = $product->product->unicode;
        $productModel = new Products();
        $productDetailsArr = $productModel->getProductInfoByUnicode($productCode);
        $productId = $productDetailsArr->id;
        
         // updating in db for their status
        $transList = $auth->getSuccessfulTransactionForScheduler($productId);
        foreach ($transList as $transinfo) {
            
            $param = array(
                'TxnIdentifierType'     => 'CRN',
                'MemberIDCardNo'        => $transinfo['card_number'],
                'ResponseCode'          => '105',
                'ResponseMessage'       => 'Successfully retrieved Transaction Details',
                'DateTime'              => $transinfo['date_created'],
                'TxnNo'                 => $transinfo['txn_no'],
                'Amount'                => Util::filterAmount($transinfo['amount_txn']),
                'Currency'              => $transinfo['currency_iso'],
                'TxnIndicator'          => $transinfo['mode'],
                'MCC'                   => $transinfo['mcc_code'],
                'MerchantName'          => ''
            );
            $api = new App_Api_Mediassist_Transactions();
            $flg = $api->sendIntimation($param);
            if($flg == TRUE ) {
                $auth->updateAckStatus($transinfo['id'],'y');
            }
        }
//exit('END');
        ///$countReqs = count($remitReqData);
        //$msg = $clearLoadResp['cutoff'].' load request cutoff done successfully and '.$clearLoadResp['not_cutoff'].' failed for cutoff process';
        //$param = array('cron_id'=>CRON_PURSE_CUT_OFF_VALIDATION, 'message'=>$msg, 'id'=>$cronLogId);
        //$crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_PURSE_CUT_OFF_VALIDATION)); // updating cron status in t_cron table
    } catch(Exception $e){
        $msg = $e->getMessage();
        $param = array('cron_id'=>CRON_PURSE_CUT_OFF_VALIDATION, 'message'=>$msg, 'id'=>$cronLogId);
        $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_PURSE_CUT_OFF_VALIDATION)); // updating cron status in t_cron table
      }
//} else {
//        $param = array('cron_id'=>CRON_PURSE_CUT_OFF_VALIDATION, 'message'=>'The cron is already '.$runStatus.' and does not have '.STATUS_COMPLETED.' status', 'id'=>$cronLogId);
//        $crons->updateCronLog($param);
//    }
    
  //$crons->updateCronLog($param);
//}

    
