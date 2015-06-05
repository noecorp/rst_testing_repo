<?php

/*
 * Validator class for Purse Parameters
 * 
 */
class Validator_PurseParameterValidator extends App_Model
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
    protected $_name = DbTable::TABLE_PURSE_MASTER;
    
   
    public function getPurseParameters($purseMasterId = 0, $bankUnicode = '')
    {
        if(!is_numeric($purseMasterId) || !$purseMasterId > 0) {
            App_Logger::log("No record found with this purse master id.", Zend_Log::ALERT);
            throw new App_Exception(ErrorCodes::ERROR_INSUFFICIENT_DATA_AUTHENTICATION_MSG, ErrorCodes::ERROR_INSUFFICIENT_DATA_FOR_PROCESSING);
        }
        $select = $this->_db->select()
                ->from(DbTable::TABLE_PURSE_MASTER, array('id', 'bank_id', 'product_id', 'global_purse_id', 'code', 'name', 'description', 'max_balance', 'allow_remit', 'allow_mvc', 'load_channel', 'load_validity_day', 'load_validity_hr', 'load_validity_min', 'load_min', 'load_max', 'load_max_cnt_daily', 'load_max_val_daily', 'load_max_cnt_monthly', 'load_max_val_monthly', 'load_max_cnt_yearly', 'load_max_val_yearly', 'txn_restriction_type', 'txn_upload_list', 'txn_min', 'txn_max', 'txn_max_cnt_daily', 'txn_max_val_daily', 'txn_max_cnt_monthly', 'txn_max_val_monthly', 'txn_max_cnt_yearly', 'txn_max_val_yearly', 'debit_api_cr', 'payable_ac_id', 'priority', 'date_start', 'date_created', 'date_updated', 'by_ops_id', 'status', 'allow_debit'))
                ->where('id = ?', $purseMasterId);
        $row = $this->_db->fetchRow($select);      
        if(empty($row)) {
            App_Logger::log("No record found with this purse master id.", Zend_Log::ALERT);
            throw new App_Exception(ErrorCodes::ERROR_INSUFFICIENT_DATA_AUTHENTICATION_MSG, ErrorCodes::ERROR_INSUFFICIENT_DATA_FOR_PROCESSING);
        }
        return $row;
    }
    
    
    /* Chk against Customer Purse Balance */
    public function chkPurseBalance($customerPurseId, $amount, $maxBalance, $bankUnicode = '')
    {
        if($maxBalance > 0)
        {
            $custPurseDetail = $this->getCustomerPurseDetail($customerPurseId, $bankUnicode);
            $increasedAmout = $custPurseDetail['amount'] + $amount - $custPurseDetail['block_amount'];
            if($increasedAmout > $maxBalance){
                App_Logger::log("Max. Balance allowed in this purse: ".$maxBalance.". Existing Balance: ".$custPurseDetail['amount'].". Amount tried: ".$amount, Zend_Log::ALERT);
                throw new App_Exception("Max. Balance allowed in this purse: ".Util::numberFormat($maxBalance).". Existing Balance: ".Util::numberFormat($custPurseDetail['amount']).". Amount tried: ".Util::numberFormat($amount), ErrorCodes::ERROR_EDIGITAL_PURSE_MAX_BALANCE_CODE);
            }
        }
        return TRUE;
    }
    
    /* Customer Purse Detail */
    public function getCustomerPurseDetail($customerPurseId, $bankUnicode = '')
    {
        $bankUnicodeArr = Util::bankUnicodesArray();
        if ($bankUnicode == '') {
            $bankUnicode = $bankUnicodeArr['2']; // rat
        }
        switch ($bankUnicode) {
            case $bankUnicodeArr['3']:
                 $objCustPurse = new Corp_Kotak_CustomerPurse();
                break;
            case $bankUnicodeArr['2']:
//            default:
                 $objCustPurse = new Corp_Ratnakar_CustomerPurse();
                break;
            case $bankUnicodeArr['1']:
//            default:
                 $objCustPurse = new Corp_Boi_CustomerPurse();
                break;
        }
       
       
        return $objCustPurse->findById($customerPurseId);
    }
    
    /* if amount is within range */
    public function chkMinMaxRange($amount, $limitMin, $limitMax, $txt = "This Wallet")
    {
        /*if($limitMin > 0 && $amount < $limitMin)
        {
            App_Logger::log("Amount less than Min value for ".$txt.". Min Amount Allowed: ".Util::numberFormat($limitMin).". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new Exception ("Amount less than Min value for ".$txt.". Min Amount Allowed: ".Util::numberFormat($limitMin).". Amount tried: ".Util::numberFormat($amount),  ErrorCodes::ERROR_INSUFFICIENT_AMOUNT);
        }
        elseif($limitMax > 0 && $amount > $limitMax)
        {   
            App_Logger::log("Amount exceeds Max value for ".$txt.". Max Amount Allowed: ".Util::numberFormat($limitMax).". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new Exception ("Amount exceeds Max value for ".$txt.". Max Amount Allowed: ".Util::numberFormat($limitMax).". Amount tried: ".Util::numberFormat($amount),ErrorCodes::ERROR_INSUFFICIENT_AMOUNT);
        }*/
        
        if(($limitMin > 0 && $amount < $limitMin) || ($limitMax > 0 && $amount > $limitMax)) {
            App_Logger::log("Amount should be between ".Util::numberFormat($limitMin)." and ".Util::numberFormat($limitMax)." for ".$txt.". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new Exception ("Amount should be between ".Util::numberFormat($limitMin)." and ".Util::numberFormat($limitMax)." for ".$txt.". Amount tried: ".Util::numberFormat($amount),  ErrorCodes::ERROR_INSUFFICIENT_AMOUNT);
        }
        return TRUE;
    }
    
    /*  chk load stats    */
    public function chkLimits($limitDetails, $amount, $customerPurseId, $txnType = 'load', $bankUnicode = '' )
    {
        $bankUnicodeArr = Util::bankUnicodesArray();
        if ($bankUnicode == '') {
            $bankUnicode = $bankUnicodeArr['2']; // rat
        }
        switch ($bankUnicode) {
            case $bankUnicodeArr['3']:
                $objCardload = new Corp_Kotak_Cardload();
                break;
            case $bankUnicodeArr['2']:
//            default:
                $objCardload = new Corp_Ratnakar_Cardload();
                break;
            case $bankUnicodeArr['1']:
                $objCardload = new Corp_Boi_Cardload();
                break;
        }
        
        
        if($txnType == 'load')
        {
            $statusStr = "'".STATUS_LOADED."', '".STATUS_CUTOFF."'";
        }
        // DAILY LIMITS
        if($limitDetails['load_max_cnt_daily'] > 0 || $limitDetails['load_max_val_daily'] > 0)
        {
            $curDate = date('Y-m-d'); 
            $row = $objCardload->getStatsDaily($customerPurseId, $curDate, $statusStr);
            if($row)
            {
                $count = ($row['count'] > 0) ? $row['count'] : 0;
                $total = ($row['total'] > 0) ? $row['total'] : 0;
                $this->getLimitFlag($count, $total, $amount, $limitDetails['load_max_cnt_daily'], $limitDetails['load_max_val_daily'], "Daily", "Corporate Load");
            }
        }
        // MONTHLY LIMITS
        if($limitDetails['load_max_cnt_monthly'] > 0 || $limitDetails['load_max_val_monthly'] > 0)
        {
            $curMonth = date('m');
            $curYear = date('Y');
            $curMonthDays = Util::getMonthDays($curMonth, $curYear);
            $startDate = $curYear.'-'.$curMonth.'-01';
            $endDate = $curYear.'-'.$curMonth.'-'.$curMonthDays;   
            $row = $objCardload->getStatsDuration($customerPurseId, $startDate, $endDate, $statusStr);
//            echo "<pre>";print_r($row);
            if($row)
            {
                $count = ($row['count'] > 0) ? $row['count'] : 0;
                $total = ($row['total'] > 0) ? $row['total'] : 0;
                $this->getLimitFlag($count, $total, $amount, $limitDetails['load_max_cnt_monthly'], $limitDetails['load_max_val_monthly'], "Monthly", "Corporate Load");
            }
        }
        // YEARLY LIMITS
        if($limitDetails['load_max_cnt_yearly'] > 0 || $limitDetails['load_max_val_yearly'] > 0)
        {
            $startDate = date("Y-01-01");
            $endDate = date("Y-12-31");   
            $row = $objCardload->getStatsDuration($customerPurseId, $startDate, $endDate, $statusStr);
            if($row)
            {
                $count = ($row['count'] > 0) ? $row['count'] : 0;
                $total = ($row['total'] > 0) ? $row['total'] : 0;
                $this->getLimitFlag($count, $total, $amount, $limitDetails['load_max_cnt_yearly'], $limitDetails['load_max_val_yearly'], "Yearly", "Corporate Load");
            }
        }
        return TRUE;
    }
    
    private function getLimitFlag($count, $total, $amount, $cntMax, $amtMax, $period = "Daily", $txt = "Wallet")
    {
        $totalAmt = $total+$amount;
        if($cntMax > 0 && $count >= $cntMax)
        {
            App_Logger::log("Transaction will exceed Max. No. of ".$period." Txns Allowed for ".$txt.". Max ".$period." No. Allowed: ".$cntMax.". No. of Txns already done: ".$count, Zend_Log::ALERT);
            throw new App_Exception ("Transaction will exceed Max. No. of ".$period." Txns Allowed for ".$txt.". Max ".$period." No. Allowed: ".$cntMax.". No. of Txns already done: ".$count,  ErrorCodes::ERROR_TRANSACTION_FREQUENCY);
        }
        elseif($amtMax > 0 && $totalAmt > $amtMax)
        {
            App_Logger::log("Transaction Amount will exceed Max. Amount of ".$period." Txns Allowed for ".$txt.". Max ".$period." Txns Allowed: ".Util::numberFormat($amtMax).". Amount of Txns already done: ".Util::numberFormat($total).". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new App_Exception ("Transaction Amount will exceed Max. Amount of ".$period." Txns Allowed for ".$txt.". Max ".$period." Txns Allowed: ".Util::numberFormat($amtMax).". Amount of Txns already done: ".Util::numberFormat($total).". Amount tried: ".Util::numberFormat($amount),ErrorCodes::ERROR_TRANSACTION_LIMIT);
        } 
        
        return TRUE;
    }
    
    
    /*  chk auth txn stats    */
    public function chkAuthTxnLimits($limitDetails, $amount, $customerPurseId, $txnType = 'authtxn' )
    {
        $objCardload = new AuthRequest();
        
        if($txnType == 'authtxn')
        {
            $statusStr = "'".STATUS_COMPLETED."'";
            $txn_type = TXNTYPE_CORP_AUTH_TXN_PROCESSING;
        }
        // DAILY LIMITS
        if($limitDetails['txn_max_cnt_daily'] > 0 || $limitDetails['txn_max_val_daily'] > 0)
        {
            $curDate = date('Y-m-d'); 
            $row = $objCardload->getStatsDaily($customerPurseId, $curDate, $statusStr, $txn_type);
            if($row)
            {
                $count = ($row['count'] > 0) ? $row['count'] : 0;
                $total = ($row['total'] > 0) ? $row['total'] : 0;
                $this->getLimitFlag($count, $total, $amount, $limitDetails['txn_max_cnt_daily'], $limitDetails['txn_max_val_daily'], "Daily", "Wallet");
            }
        }
        // MONTHLY LIMITS
        if($limitDetails['txn_max_cnt_monthly'] > 0 || $limitDetails['txn_max_val_monthly'] > 0)
        {
            $curMonth = date('m');
            $curYear = date('Y');
            $curMonthDays = Util::getMonthDays($curMonth, $curYear);
            $startDate = $curYear.'-'.$curMonth.'-01';
            $endDate = $curYear.'-'.$curMonth.'-'.$curMonthDays;   
            $row = $objCardload->getStatsDuration($customerPurseId, $startDate, $endDate, $statusStr, $txn_type);
//            echo "<pre>";print_r($row);
            if($row)
            {
                $count = ($row['count'] > 0) ? $row['count'] : 0;
                $total = ($row['total'] > 0) ? $row['total'] : 0;
                $this->getLimitFlag($count, $total, $amount, $limitDetails['txn_max_cnt_monthly'], $limitDetails['txn_max_val_monthly'], "Monthly", "Wallet");
            }
        }
        // YEARLY LIMITS
        if($limitDetails['txn_max_cnt_yearly'] > 0 || $limitDetails['txn_max_val_yearly'] > 0)
        {
            $startDate = date("Y-01-01");
            $endDate = date("Y-12-31");   
            $row = $objCardload->getStatsDuration($customerPurseId, $startDate, $endDate, $statusStr, $txn_type);
            if($row)
            {
                $count = ($row['count'] > 0) ? $row['count'] : 0;
                $total = ($row['total'] > 0) ? $row['total'] : 0;
                $this->getLimitFlag($count, $total, $amount, $limitDetails['txn_max_cnt_yearly'], $limitDetails['txn_max_val_yearly'], "Yearly", "Wallet");
            }
        }
        return TRUE;
    }
    
}
