<?php
if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}
require_once CRON_PATH . '/cli.php';

$qurStr['dur'] = 'yesterday';
$qurStr['bank_unicode'] = 400;
//$qurStr['from_date']  = $this->_getParam('from_date');
//$qurStr['to_date']  = $this->_getParam('to_date');
//$qurStr['mobile_no'] =  $this->_getParam('mobile_no');
//$qurStr['txn_no'] =  $this->_getParam('txn_no');


if(!empty($qurStr)){
      $bankBoi = App_DI_Definition_Bank::getInstance(BANK_BOI);
        $bankBoiUnicode = $bankBoi->bank->unicode;
        
        /**
        *  Start code: Getting Ratnakar Bank Unicode 
        */
        $RatnakarBank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $bankRatnakarUnicode = $RatnakarBank->bank->unicode;
       /**
        ** End code: Getting Ratnakar Bank Unicode 
        */
    $objReports = new Remit_Reports();
    $exportData = $objReports->exportRemittance($qurData);
    $columns = array(
                       'Transaction Date',
                       'Super Distributor Code',
                       'Super Distributor Name',
                       'Distributor Code',
                       'Distributor Name',
                       'Agent Code',
                       'Agent Mobile Number',
                       'Agent Email ID',
                       'Agent Name',
                       'Agent City',
                       'Agent Pincode',
                       'Transaction Code',
                       'Transaction Amount',
                       'Customer Mobile Number',
                       'Product Code',
                       'Transaction Reference Number',
                       'Refund/Reversed Trx Ref Number',
                       'Remitter Name',
                       'Remitter Mobile Number',
                       'Remitter Email',
                       'Remitter Registration Date',
                       'Bene Name',
                       'Bene Bank Name',
                       'Bene IFSC Code',
                       'Current Transaction Status',
                       'Reason',
                       'Reason Code'
                    );
    if(( $qurStr['bank_unicode'] == $bankRatnakarUnicode) ){
        array_push($columns,'UTR No');
       
    }
    if(( $qurStr['bank_unicode'] == $bankBoiUnicode) || ( $qurStr['bank_unicode'] == $bankRatnakarUnicode) ){
        array_push($columns,'Batch Name');
       
    }
    $objCSV = new CSV();
    $resp = $objCSV->export($exportData, $columns, 'remittance_transaction');exit;
                 
}

