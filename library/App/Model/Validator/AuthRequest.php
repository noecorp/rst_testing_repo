<?php

/*
 * Validator class for AuthRequest
 * 
 */
class Validator_AuthRequest extends Validator_PurseParameterValidator
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
    protected $_name = DbTable::TABLE_CARD_AUTH_REQUEST;
    
    private $_message;
    private $_message_code;
    /*
     * $params['customer_master_id'] = customer master id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount
     * $params['product_id'] =>  product_id
     * $params['bank_unicode'] =>  bank unicode
     */
    public function chkAllowCardAuthAdvice($params)
    {
        $flg = TRUE;
         if ($params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' 
             || $params['product_id'] == '' ){
            throw new App_Exception(ErrorCodes::ERROR_INSUFFICIENT_DATA_AUTHENTICATION_MSG,  ErrorCodes::ERROR_INSUFFICIENT_DATA_FOR_PROCESSING);
        }
        try {
            /* check available balance */
            $availablePurseBalance = $this->chkAvailablePurseBalance($params['customer_purse_id'], $params['amount'], $params['bank_unicode']);
            if($availablePurseBalance)
            {
                $purseParams = $this->getPurseParameters($params['purse_master_id']);
               
              
                if(strtolower($purseParams['txn_restriction_type']) == strtolower(TYPE_MCC))
                {
                    $this->chkValidMcc($params['purse_master_id'], $params['mcc_code']);
                }elseif(strtolower($purseParams['txn_restriction_type']) == strtolower(TYPE_TID))
                {
                    $this->chkValidTid($params['purse_master_id'], $params['tid']);
                }
                 
                /* chk txn min max range for the purse */
                if($this->chkMinMaxRange($params['amount'], $purseParams['txn_min'], $purseParams['txn_max'], "Transaction"))
                {
                    $this->chkAuthTxnLimits($purseParams, $params['amount'], $params['customer_purse_id'], $txnType = 'authtxn' );
                }
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
    
    
    /* Balance available  */
    public function chkAvailablePurseBalance($customerPurseId, $amount, $bankUnicode = '')
    {
        $custBal = $this->getCustomerPurseDetail($customerPurseId, $bankUnicode);
        $proposedBal = $custBal['amount'] - $amount - $custBal['block_amount'];
        if($proposedBal < MIN_CUSTOMER_BALANCE)
        {
            App_Logger::log("Customer does not have sufficient fund in the wallet. Balance: ".Util::numberFormat($custBal['amount']).". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new App_Exception ("Customer does not have sufficient fund in the wallet. Balance: ".Util::numberFormat($custBal['amount']).". Amount tried: ".Util::numberFormat($amount),  ErrorCodes::ERROR_INSUFFICIENT_BALANCE);
        }
        return TRUE;
    }
    
    
    
    /* validate mcc code against mcc available for that purse  */
    public function chkValidMcc($purseMasterId, $mccCode)
    {
        $mccModel = new MccMaster();
        $purseMcc = $mccModel->getPurseMcc($purseMasterId);
        foreach($purseMcc as $mcc)
        {
            if($mccCode == $mcc['mcc_code'])
            {
                return TRUE;
            }
        }
        App_Logger::log("MCC Code: ".$mccCode." is not allowed", Zend_Log::ALERT);
        throw new App_Exception ("MCC Code: ".$mccCode." is not allowed",  ErrorCodes::ERROR_INVALID_MCC);
    }
    
    /* validate TID code against tid available for that purse  */
    public function chkValidTid($purseMasterId, $tidCode)
    {
        $tidModel = new TidMaster();
        $purseTid = $tidModel->getPurseTid($purseMasterId);
        foreach($purseTid as $tid)
        {
            if($tidCode == $tid['tid_code'])
            {
                return TRUE;
            }
        }
        App_Logger::log("TID Code: ".$tidCode." is not allowed", Zend_Log::ALERT);
        throw new App_Exception ("TID Code: ".$tidCode." is not allowed",  ErrorCodes::ERROR_INVALID_TID);
    }
    
    /* if amount is within range */
    public function getMinMaxLimitFlag($amount, $limitOutMinTxn, $limitOutMaxTxn, $txt = "Agent")
    {
        if($limitOutMinTxn > 0 && $amount < $limitOutMinTxn)
        {
            throw new App_Exception ("Amount less than Min. Per Txn for ".$txt.". Min Per Txn Amount Allowed: ".Util::numberFormat($limitOutMinTxn).". Amount tried: ".Util::numberFormat($amount));
        }
        elseif($limitOutMaxTxn > 0 && $amount > $limitOutMaxTxn)
        {   
            throw new App_Exception ("Amount exceeds Max. Per Txn for ".$txt.". Max Per Txn Amount Allowed: ".Util::numberFormat($limitOutMaxTxn).". Amount tried: ".Util::numberFormat($amount));
        }
        return TRUE;
    }
   
    public function chkAllowDrAdj($params)
    {
        $flg = TRUE;
         if ($params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' 
             || $params['product_id'] == '' ){
            throw new App_Exception('Insufficient Data for validating Authentication Advice');
        }
        try {
            /* check available balance */
            return $this->chkAvailablePurseBalance($params['customer_purse_id'], $params['amount']);
        } catch (App_Exception $e){
            $flg = FALSE;
            $error_msg = $e->getMessage();
        }
        if($flg != TRUE)
        {
            $this->setMessage($error_msg);
            return FALSE;
        }
        return TRUE;
      
    }
    
}
