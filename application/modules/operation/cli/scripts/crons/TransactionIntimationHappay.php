<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_HAPPAY_TRANSACTION_INITIMATION));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';
$now = new Zend_Db_Expr('NOW()');
 
if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute
   $auth = new AuthRequest();
    
   if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run
    try{
        $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_HAPPAY_TRANSACTION_INITIMATION));
        $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_HAPPAY);
        $productCode = $product->product->unicode;
        $productModel = new Products();
        $productDetailsArr = $productModel->getProductInfoByUnicode($productCode);
        $productId = $productDetailsArr->id;
        $ratModel = new Corp_Ratnakar_Cardholders();
        $customerInfo = $ratModel->getCardholderInfo(array(
            'product_id'    => $productId,
            'cardholder_id' => $transinfo['cardholder_id']
        ));
         // updating in db for their status
        $transList = $auth->getSuccessfulTransactionForScheduler($productId);
        foreach ($transList as $transinfo) {
            $customerInfo = $ratModel->getCardholderInfo(array(
                'product_id'    => $productId,
                'cardholder_id' => $transinfo['cardholder_id']
            ));
            if(!empty($customerInfo['mobile'])) {
                $param = array(
                    'TxnIdentifierType'     => 'MOB',
                    'MemberIDCardNo'        => $customerInfo['mobile'],
                    'ResponseCode'          => '105',
                    'ResponseMessage'       => 'Successfully retrieved Transaction Details',
                    'DateTime'              => $transinfo['date_created'],
                    'TxnNo'                 => $transinfo['txn_no'],
                    'Amount'                => Util::filterAmount($transinfo['amount_txn']),
                    'Currency'              => $transinfo['currency_iso'],
                    'TxnIndicator'          => $transinfo['mode'],
                    'MCC'                   => $transinfo['mcc_code'],
                    'MerchantName'          => $transinfo['narration']
                );
            } else {
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
                    'MerchantName'          => $transinfo['narration']
                );
            }
            $api = new App_Api_Happay_Transactions();
            $flg = $api->sendIntimation($param);
            if($flg == TRUE ) {
                $auth->updateAckStatus($transinfo['id'],'y');
            }
        }
        $msg = ' Transaction intimation done successfully ';
        $param = array('cron_id'=>CRON_HAPPAY_TRANSACTION_INITIMATION, 'message'=>$msg, 'id'=>$cronLogId);
        $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_HAPPAY_TRANSACTION_INITIMATION)); // updating cron status in t_cron table
    } catch(Exception $e){
        $msg = $e->getMessage();
        $param = array('cron_id'=>CRON_HAPPAY_TRANSACTION_INITIMATION, 'message'=>$msg, 'id'=>$cronLogId);
        $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_HAPPAY_TRANSACTION_INITIMATION)); // updating cron status in t_cron table
      }
} else {
        $param = array('cron_id'=>CRON_HAPPAY_TRANSACTION_INITIMATION, 'message'=>'The cron is already '.$runStatus.' and does not have '.STATUS_COMPLETED.' status', 'id'=>$cronLogId);
        $crons->updateCronLog($param);
    }
    
  $crons->updateCronLog($param);
}