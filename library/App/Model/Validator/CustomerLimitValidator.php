<?php

/*
 * Validator class for Customer Limits
 * 
 */
class Validator_CustomerLimitValidator extends App_Model
{
    
  /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'id';
    
    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_PRODUCT_CUSTOMER_LIMITS;
    
    private $_message;
    private $_message_code;
    
    public function getMessage()
    {
        return $this->_message;
    }
    
    private function setMessage($msg)
    {
        $this->_message = $msg;
    }
    
    public function getMessageCode()
    {
        return $this->_message_code;
    }
    
    private function setMessageCode($msg)
    {
        $this->_message_code = $msg;
    }
    
    public function getCustomerLimits($productId = 0, $bankUnicode = '', $customerType = TYPE_NONKYC)
    {
        if(!is_numeric($productId) || !$productId > 0) {
            App_Logger::log("No record found with this product id.", Zend_Log::ALERT);
            throw new App_Exception(ErrorCodes::ERROR_INSUFFICIENT_DATA_AUTHENTICATION_MSG,  ErrorCodes::ERROR_INSUFFICIENT_DATA_FOR_PROCESSING);
        }
        $select = $this->select()
                ->from($this->_name, array('id', 'bank_id', 'product_id', 'code', 'customer_type', 'name', 'description', 'max_balance', 'load_min', 'load_max', 'load_max_val_daily', 'load_max_val_monthly', 'load_max_val_yearly', 'txn_min', 'txn_max', 'txn_max_val_daily', 'txn_max_val_monthly', 'txn_max_val_yearly', 'date_start', 'date_created', 'date_updated', 'by_ops_id', 'status'))
                ->where('product_id = ?', $productId)
                ->where('customer_type = ?', $customerType)
                ->where('status = ?', STATUS_ACTIVE);  
        $rs = $this->fetchRow($select);      
        if(empty($rs)) {
            return FALSE;
        }
        return $rs;
    }
    
    
    /*
     * $params['customer_master_id'] = customer master id
     * $params['amount'] = amount
     * $params['product_id'] =>  product_id,
     * $params['bank_unicode'] =>  bank_unicode,
     * 
     */
    public function chkAllowLoad($params)
    {
        $isReversal = isset($params['isReversal']) ? $params['isReversal'] : '';
       
        if ($params['customer_master_id'] == '' || $params['amount'] == '' 
              || $params['product_id'] == '' || $params['bank_unicode'] == ''){
            throw new App_Exception(ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_CARDLOAD_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_CARDLOAD_CODE);
        }
       
        $params['customer_type'] = TYPE_NONKYC;
        $bankUnicodeArr = Util::bankUnicodesArray();
        switch($params['bank_unicode']) {
            case $bankUnicodeArr['2']: // rat
//            default:
                $custModel = new Corp_Ratnakar_Cardholders();
                $custPurseModel = new Corp_Ratnakar_CustomerPurse();
                $custParams = array('customer_master_id' => $params['customer_master_id']);
                $custArr = $custModel->getCardholderInfo($custParams);
                if($custArr['customer_type'] == TYPE_KYC) {
                    $params['customer_type'] = TYPE_KYC;
                }
                
                $customerLimits = $this->getCustomerLimits($params['product_id'], $params['bank_unicode'], $params['customer_type']);
                if($customerLimits) {
                    if($isReversal!= REVERSAL_FLAG_YES){
                        $this->chkMinMaxRange($params['amount'], $customerLimits['load_min'], $customerLimits['load_max'], "Customer Load");

                        //  chk max balance
                        $existingBal = $custPurseModel->getCustProductBalance($params['customer_master_id'], $params['product_id']);
                        $newBal = $existingBal['sum'] + $params['amount'];
                        if($newBal > $customerLimits['max_balance']) {
                            App_Logger::log("Max. Balance allowed for the customer: ".$customerLimits['max_balance'].". Existing Balance: ".$existingBal['sum'].". Amount tried: ".$params['amount'], Zend_Log::ALERT);
                            throw new App_Exception("Max. Balance allowed for the customer: ".Util::numberFormat($customerLimits['max_balance']).". Existing Balance: ".Util::numberFormat($existingBal['sum']).". Amount tried: ".Util::numberFormat($params['amount']), ErrorCodes::ERROR_EDIGITAL_CUSTOMER_MAX_BALANCE_CODE);
                        }
                    }
                    
                    // D/M/Y
                    $this->chkLimits($customerLimits, $params, 'load',$params['bank_unicode']);
                    
                }
                break;
             case $bankUnicodeArr['3']: // kotak
//            default:
                $custModel = new Corp_Kotak_Customers();
                $custPurseModel = new Corp_Kotak_CustomerPurse();
                $custParams = array('customer_master_id' => $params['customer_master_id']);
                
                $custArr = $custModel->getCardholderInfo($custParams);
                
                if($custArr['customer_type'] == TYPE_KYC) {
                    $params['customer_type'] = TYPE_KYC;
                }
                
                $customerLimits = $this->getCustomerLimits($params['product_id'], $params['bank_unicode'], $params['customer_type']);
                 
               
                if($customerLimits) {
                    $this->chkMinMaxRange($params['amount'], $customerLimits['load_min'], $customerLimits['load_max'], "Customer Load");
                        
                    //  chk max balance
                    $existingBal = $custPurseModel->getCustProductBalance($params['customer_master_id'], $params['product_id']);
                    $newBal = $existingBal['sum'] + $params['amount'];
                    if($newBal > $customerLimits['max_balance']) {
                        App_Logger::log("Max. Balance allowed for the customer: ".$customerLimits['max_balance'].". Existing Balance: ".$existingBal['sum'].". Amount tried: ".$params['amount'], Zend_Log::ALERT);
                        throw new App_Exception("Max. Balance allowed for the customer: ".Util::numberFormat($customerLimits['max_balance']).". Existing Balance: ".Util::numberFormat($existingBal['sum']).". Amount tried: ".Util::numberFormat($params['amount']), ErrorCodes::ERROR_EDIGITAL_CUSTOMER_MAX_BALANCE_CODE);
                    }
                    
                    // D/M/Y
                    $this->chkLimits($customerLimits, $params, 'load',$params['bank_unicode']);
                    
                }
                break;    
        }
        
        return TRUE;
      
    }
    
    /* if amount is within range */
    public function chkMinMaxRange($amount, $limitMin, $limitMax, $txt = "Customer Load")
    {
        if($limitMin > 0 && $amount < $limitMin)
        {
            App_Logger::log("Amount less than Min value for ".$txt.". Min Amount Allowed: ".Util::numberFormat($limitMin).". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new Exception ("Amount less than Min value for ".$txt.". Min Amount Allowed: ".Util::numberFormat($limitMin).". Amount tried: ".Util::numberFormat($amount),  ErrorCodes::ERROR_INSUFFICIENT_AMOUNT);
        }
        elseif($limitMax > 0 && $amount > $limitMax)
        {   
            App_Logger::log("Amount exceeds Max value for ".$txt.". Max Amount Allowed: ".Util::numberFormat($limitMax).". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new Exception ("Amount exceeds Max value for ".$txt.". Max Amount Allowed: ".Util::numberFormat($limitMax).". Amount tried: ".Util::numberFormat($amount),ErrorCodes::ERROR_INSUFFICIENT_AMOUNT);
        }
        return TRUE;
    }
    
    
    /*  chk load stats    */
    public function chkLimits($limitDetails, $params, $txnType = 'load', $bankUnicode = '' )
    {
        $isReversal = isset($params['isReversal']) ? $params['isReversal'] : '';
        $bankUnicodeArr = Util::bankUnicodesArray();
        if ($bankUnicode == '') {
            $bankUnicode = $bankUnicodeArr['2']; // rat
        }
        switch ($bankUnicode) {
            
            case $bankUnicodeArr['3']: // rat
                $objCardload = new Corp_Kotak_Cardload();
                break;
            case $bankUnicodeArr['2']: // rat
                $objCardload = new Corp_Ratnakar_Cardload();
                break;
            
        }
        
        if($txnType == 'load')
        {
            $statusStr = "'".STATUS_LOADED."', '".STATUS_COMPLETED."'";
        }
        // DAILY LIMITS
        if($limitDetails['load_max_val_daily'] > 0)
        {
            $curDate = date('Y-m-d'); 
            $row = $objCardload->getCustomerProductStatsDaily($params['customer_master_id'], $params['product_id'], $curDate, $statusStr);
            if($row)
            {
                $total = ($row['total'] > 0) ? $row['total'] : 0;
                
                if($isReversal == REVERSAL_FLAG_YES){
                     $this->getLimitWithReversalFlag($total, $params['amount'], $limitDetails['load_max_val_daily'], "Daily", "Customer Load");
                }else{
                     $this->getLimitFlag($total, $params['amount'], $limitDetails['load_max_val_daily'], "Daily", "Customer Load");
                }
                
            }
        }
        // MONTHLY LIMITS
        if($limitDetails['load_max_val_monthly'] > 0)
        {
            $curMonth = date('m');
            $curYear = date('Y');
            $curMonthDays = Util::getMonthDays($curMonth, $curYear);
            $startDate = $curYear.'-'.$curMonth.'-01';
            $endDate = $curYear.'-'.$curMonth.'-'.$curMonthDays;   
            $row = $objCardload->getCustomerProductStatsDuration($params['customer_master_id'], $params['product_id'], $startDate, $endDate, $statusStr);
//            echo "<pre>";print_r($row);
            if($row)
            {
                $total = ($row['total'] > 0) ? $row['total'] : 0;
                if($isReversal == REVERSAL_FLAG_YES){
                $this->getLimitWithReversalFlag($total, $params['amount'], $limitDetails['load_max_val_monthly'], "Monthly", "Customer Load");
                }else{
                   
                 $this->getLimitFlag($total, $params['amount'], $limitDetails['load_max_val_monthly'], "Monthly", "Customer Load");
                }
            }
        }
        // YEARLY LIMITS
        if($limitDetails['load_max_val_yearly'] > 0)
        {
            $startDate = date("Y-01-01");
            $endDate = date("Y-12-31");   
            $row = $objCardload->getCustomerProductStatsDuration($params['customer_master_id'], $params['product_id'], $startDate, $endDate, $statusStr);
            if($row)
            {
                $total = ($row['total'] > 0) ? $row['total'] : 0;
                if($isReversal == REVERSAL_FLAG_YES){
                  $this->getLimitWithReversalFlag($total, $params['amount'], $limitDetails['load_max_val_yearly'], "Yearly", "Customer Load");   
                }else{
                $this->getLimitFlag($total, $params['amount'], $limitDetails['load_max_val_yearly'], "Yearly", "Customer Load");
                }
            }
        }
        return TRUE;
    }
    
    private function getLimitFlag($total, $amount, $amtMax, $period = "Daily", $txt = "Customer")
    {
        $totalAmt = $total+$amount;
        if($amtMax > 0 && $totalAmt > $amtMax)
        {
         
            App_Logger::log("Transaction Amount will exceed Max. Amount of ".$period." Txns Allowed for ".$txt.". Max ".$period." Txns Allowed: ".Util::numberFormat($amtMax).". Amount of Txns already done: ".Util::numberFormat($total).". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new App_Exception ("Transaction Amount will exceed Max. Amount of ".$period." Txns Allowed for ".$txt.". Max ".$period." Txns Allowed: ".Util::numberFormat($amtMax).". Amount of Txns already done: ".Util::numberFormat($total).". Amount tried: ".Util::numberFormat($amount),ErrorCodes::ERROR_TRANSACTION_LIMIT);
        } 
        
        return TRUE;
    }
    
    private function getLimitWithReversalFlag($total, $amount, $amtMax, $period = "Daily", $txt = "Customer")
    {
      //  $totalAmt = $total+$amount;
        $totalAmt = $total;
        if($amtMax > 0 && $totalAmt > $amtMax)
        {
            App_Logger::log("Transaction Amount will exceed Max. Amount of ".$period." Txns Allowed for ".$txt.". Max ".$period." Txns Allowed: ".Util::numberFormat($amtMax).". Amount of Txns already done: ".Util::numberFormat($total).". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new App_Exception ("Transaction Amount will exceed Max. Amount of ".$period." Txns Allowed for ".$txt.". Max ".$period." Txns Allowed: ".Util::numberFormat($amtMax).". Amount of Txns already done: ".Util::numberFormat($total).". Amount tried: ".Util::numberFormat($amount),ErrorCodes::ERROR_TRANSACTION_LIMIT);
        } 
        
        return TRUE;
    }
    
    
   
    /*
     * $params['customer_master_id'] = customer master id
     * $params['amount'] = amount
     * $params['product_id'] =>  product_id,
     * $params['bank_unicode'] =>  bank_unicode,
     * 
     */
    public function chkAllowAuth($params)
    {
        $flg = TRUE;
         if ($params['customer_master_id'] == '' || $params['amount'] == '' 
             || $params['product_id'] == '' ){
            throw new App_Exception(ErrorCodes::ERROR_INSUFFICIENT_DATA_AUTHENTICATION_MSG,  ErrorCodes::ERROR_INSUFFICIENT_DATA_FOR_PROCESSING);
        }
        try {
            
            $params['customer_type'] = TYPE_NONKYC;
            $bankUnicodeArr = Util::bankUnicodesArray();
            switch($params['bank_unicode']) {
                case $bankUnicodeArr['2']: // rat
    //            default:
                    $custModel = new Corp_Ratnakar_Cardholders();
                    $custParams = array('customer_master_id' => $params['customer_master_id']);
                    $custArr = $custModel->getCardholderInfo($custParams);
                    if($custArr['customer_type'] == TYPE_KYC) {
                        $params['customer_type'] = TYPE_KYC;
                    }

                    $customerLimits = $this->getCustomerLimits($params['product_id'], $params['bank_unicode'], $params['customer_type']);
                    if($customerLimits) {
                        $this->chkMinMaxRange($params['amount'], $customerLimits['txn_min'], $customerLimits['txn_max'], "Customer Transaction");

                        $this->chkAuthTxnLimits($customerLimits, $params, $txnType = 'authtxn' );

                    }
                    break;
            }
            
        } catch (App_Exception $e){
            $flg = FALSE;
            $error_msg = $e->getMessage();
            $error_msg_code = $e->getCode();
        }
        if($flg != TRUE)
        {
            $this->setMessage($error_msg);
            $this->setMessageCode($error_msg_code);
            return FALSE;
        }
        return TRUE;
      
    }
    
    
    
    
    /*  chk auth txn stats    */
    public function chkAuthTxnLimits($limitDetails, $params, $txnType = 'authtxn' )
    {
        $objCardload = new AuthRequest();
        $bankUnicodeArr = Util::bankUnicodesArray();
        if ($params['bank_unicode'] == '') {
            $bankUnicode = $bankUnicodeArr['2']; // rat
        } else {
            $bankUnicode = $params['bank_unicode'];
        }
        
        if($txnType == 'authtxn')
        {
            $statusStr = "'".STATUS_COMPLETED."'";
            $txn_type = TXNTYPE_CORP_AUTH_TXN_PROCESSING;
        }
        // DAILY LIMITS
        if($limitDetails['txn_max_val_daily'] > 0)
        {
            $curDate = date('Y-m-d');
            $row = $objCardload->getCustomerProductStatsDaily($params['customer_master_id'], $params['product_id'], $curDate, $statusStr, $txn_type);
            if($row)
            {
                $total = ($row['total'] > 0) ? $row['total'] : 0;
                $this->getLimitFlag( $total, $params['amount'], $limitDetails['txn_max_val_daily'], "Daily", "Customer Transaction");
            }
        }
        // MONTHLY LIMITS
        if($limitDetails['txn_max_val_monthly'] > 0)
        {
            $curMonth = date('m');
            $curYear = date('Y');
            $curMonthDays = Util::getMonthDays($curMonth, $curYear);
            $startDate = $curYear.'-'.$curMonth.'-01';
            $endDate = $curYear.'-'.$curMonth.'-'.$curMonthDays;   
            $row = $objCardload->getCustomerProductStatsDuration($params['customer_master_id'], $params['product_id'], $startDate, $endDate, $statusStr, $txn_type);
//            echo "<pre>";print_r($row);
            if($row)
            {
                $total = ($row['total'] > 0) ? $row['total'] : 0;
                $this->getLimitFlag($total, $params['amount'], $limitDetails['txn_max_val_monthly'], "Monthly", "Customer Transaction");
            }
        }
        // YEARLY LIMITS
        if($limitDetails['txn_max_val_yearly'] > 0)
        {
            $startDate = date("Y-01-01");
            $endDate = date("Y-12-31");   
            $row = $objCardload->getCustomerProductStatsDuration($params['customer_master_id'], $params['product_id'], $startDate, $endDate, $statusStr, $txn_type);
            if($row)
            {
                $total = ($row['total'] > 0) ? $row['total'] : 0;
                $this->getLimitFlag( $total, $params['amount'], $limitDetails['txn_max_val_yearly'], "Yearly", "Customer Transaction");
            }
        }
        return TRUE;
    }
    
}
