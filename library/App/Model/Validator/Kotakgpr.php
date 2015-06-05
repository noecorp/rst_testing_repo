<?php

/*
 * Validator class for Kotak GPR
 * 
 */
class Validator_Kotakgpr extends Validator_PurseParameterValidator
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
    protected $_name = DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST;
    
    /*
     * $params['load_request_id'] = load_request_id
     * $params['customer_master_id'] = customer master id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount
     * $params['corporate_id'] = corporate_id
     */
    public function chkAllowKotakCorporateCardLoad($params)
    {
         if ($params['load_request_id'] == '' || $params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' || $params['corporate_id'] == '' ){
            throw new App_Exception('Insufficient Data for validating Kotak Corporate CardLoad');
        }
        
        /* check agent available balance */
        $availableCorpBalance = $this->chkAvailableCorporateBalance($params['corporate_id'], $params['amount']);
        if($availableCorpBalance)
        {
             /* chk agent limits */
            $corpLimitDetails = $this->getCorporateLimitDetails($params['corporate_id']);
            
            if($corpLimitDetails)
            {
                $flgTxnRange = $this->getMinMaxLimitFlag($params['amount'], $corpLimitDetails['limit_out_min_txn'], $corpLimitDetails['limit_out_max_txn'], "Corporate");
                if( $flgTxnRange )
                {
                    $this->validateCorporateTxnStats($params['corporate_id'], $params['amount'], $corpLimitDetails);
                }
            }
            
            $purseParams = $this->getPurseParameters($params['purse_master_id'], $params['bank_unicode']);
        
            /* chk against max balance param for the purse */
            if($this->chkPurseBalance($params['customer_purse_id'], $params['amount'], $purseParams['max_balance'], $params['bank_unicode']))
            {
                /* chk load min max range for the purse */
                if($this->chkMinMaxRange($params['amount'], $purseParams['load_min'], $purseParams['load_max'], "Corporate Load"))
                {
                    $this->chkLimits($purseParams, $params['amount'], $params['customer_purse_id'], 'load', $params['bank_unicode']);
                }
            }

        }
        
        
        
        
        //corporate limits
        return TRUE;
      
    }
    
    /*
     * thru API
     * $params['load_request_id'] = load_request_id
     * $params['customer_master_id'] = customer master id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount
     * $params['agent_id'] =>  KOTAK_GPR_AGENT_ID,
     * $params['product_id'] =>  product_id,
     */
    public function chkAllowKotakGPRCardLoad($params)
    {
        if ($params['load_request_id'] == '' || $params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' 
              || $params['agent_id'] == '' || $params['product_id'] == ''){
            throw new App_Exception('Insufficient Data for validating CardLoad');
        }
       
         /* check agent available balance */
        $availableAgentBalance = $this->chkAvailableAgentBalance($params['agent_id'], $params['amount']);
        if($availableAgentBalance)
        {
             /* chk agent limits */
            $agentLimitDetails = $this->getAgentLimitDetails($params['agent_id']);
          
            if($agentLimitDetails)
            {
                $flgAgentTxnRange = $this->getMinMaxLimitFlag($params['amount'], $agentLimitDetails['limit_out_min_txn'], $agentLimitDetails['limit_out_max_txn'], "Agent");
              
                if( $flgAgentTxnRange )
                {
                    $this->validateAgentTxnStats($params['agent_id'], $params['amount'], $agentLimitDetails);
                }
            }
            
            /* chk purse params */
            $purseParams = $this->getPurseParameters($params['purse_master_id']);
        
            /* chk against max balance param for the purse */
            if($this->chkPurseBalance($params['customer_purse_id'], $params['amount'], $purseParams['max_balance'], $params['bank_unicode']))
            {
                /* chk load min max range for the purse */
                if($this->chkMinMaxRange($params['amount'], $purseParams['load_min'], $purseParams['load_max'], "Corporate Load"))
                {
                    $this->chkLimits($purseParams, $params['amount'], $params['customer_purse_id'], 'load',$params['bank_unicode']);
                }
            }

        }
        
        return TRUE;
      
    }
    /*
     * thru API
     * $params['load_request_id'] = load_request_id
     * $params['customer_master_id'] = customer master id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount
     * $params['agent_id'] =>  KOTAK_GPR_AGENT_ID,
     * $params['product_id'] =>  product_id,
     */
    
    public function chkAllowKotakGPRCardDebit($params)
    {
        if ($params['load_request_id'] == '' || $params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' 
              || $params['agent_id'] == '' || $params['product_id'] == ''){
            throw new App_Exception('Insufficient Data for validating CardLoad');
        }
       
        $custBal = $this->getCustomerPurseDetail($params['customer_purse_id']);
        $proposedBal = $custBal['amount'] - $params['amount'];
        if($proposedBal < MIN_CUSTOMER_BALANCE)
        {
            App_Logger::log("Customer does not have sufficient fund in the wallet. Balance: ".Util::numberFormat($custBal['amount']).". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new App_Exception ("Customer does not have sufficient fund in the wallet. Balance: ".Util::numberFormat($custBal['amount']).". Amount tried: ".Util::numberFormat($amount),  ErrorCodes::ERROR_INSUFFICIENT_BALANCE);
        }
        return TRUE;
      
    }
    
    /* Balance available for remits / refunds / cardloads */
    public function chkAvailableAgentBalance($agentId, $amount)
    {
        $agentAmt = $this->getAgentBalance($agentId);
        $minAgentBalReqd = $this->getSettingMinBalance();
        $loadAmtAndBalance = $amount + $minAgentBalReqd;
        if($agentAmt < $loadAmtAndBalance)
        {
            App_Logger::log("Agent does not have sufficient fund. Agent Balance: ".Util::numberFormat($agentAmt).". Minimum Balance Reqd.: ".Util::numberFormat($minAgentBalReqd).". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new App_Exception ("Agent does not have sufficient fund. Agent Balance: ".Util::numberFormat($agentAmt).". Minimum Balance Reqd.: ".Util::numberFormat($minAgentBalReqd).". Amount tried: ".Util::numberFormat($amount));
        }
        return TRUE;
    }
    
     /* fetches agent balance */
    protected function getAgentBalance($agentId)
    {
        $agentBalanceModel = new AgentBalance();
        $agentAmt = $agentBalanceModel->getAgentBalance($agentId);
        if($agentAmt)
        {
            return $agentAmt;
        }
        App_Logger::log("Could not find Agent balance", Zend_Log::ALERT);
        throw new App_Exception ("Could not find Agent balance");
    }
    
     /* min balance reqd. in agent a/c */
    protected function getSettingMinBalance()
    {
        $settingModel = new AgentSetting();
        $minBal = $settingModel->getAgentSettingValue(SETTING_AGENT_MIN_BALANCE);
        if(isset($minBal) && $minBal > 0){
            return $minBal;
        }
        return 0;
    }
    
     
    /* 
     * fetches agent limits 
     */
    public function getAgentLimitDetails($agentId)
    {
        
        $agentLimitModel = new BindAgentLimit();
        $agentLimitDetails = $agentLimitModel->getAgentLimitDetails($agentId);
        if($agentLimitDetails)
        {
            return $agentLimitDetails;
        }
        return FALSE;
    }
    
   
    /*
     * chk agent txn stats, D M Y
     */
    private function validateAgentTxnStats($agentId, $amount, $agentLimitDetails)
    {
        $txnAgentModel = new TxnAgent();
        
        // DAILY LIMITS
        if($agentLimitDetails['cnt_out_max_txn_daily'] > 0 || $agentLimitDetails['limit_out_max_daily'] > 0)
        {
            $curDate = date('Y-m-d'); 
            $row = $txnAgentModel->getTxnAgentDaily($agentId, $curDate);
            if($row)
            {
                $count = ($row['count'] > 0) ? $row['count'] : 0;
                $total = ($row['total'] > 0) ? $row['total'] : 0;
                $flglimit = $this->getAgentLoadLimitFlag($count, $total, $amount, $agentLimitDetails['cnt_out_max_txn_daily'], $agentLimitDetails['limit_out_max_daily'], "Daily");
            }
        }
        // MONTHLY LIMITS
        if($agentLimitDetails['cnt_out_max_txn_monthly'] > 0 || $agentLimitDetails['limit_out_max_monthly'] > 0)
        {
            $curMonth = date('m');
            $curYear = date('Y');
            $curMonthDays = Util::getMonthDays($curMonth, $curYear);
            $startDate = $curYear.'-'.$curMonth.'-01';
            $endDate = $curYear.'-'.$curMonth.'-'.$curMonthDays;   
            $row = $txnAgentModel->getTxnAgentDuration($agentId, $startDate, $endDate);
            if($row)
            {
                $count = ($row['count'] > 0) ? $row['count'] : 0;
                $total = ($row['total'] > 0) ? $row['total'] : 0;
                $flglimit = $this->getAgentLoadLimitFlag($count, $total, $amount, $agentLimitDetails['cnt_out_max_txn_monthly'], $agentLimitDetails['limit_out_max_monthly'], "Monthly");
            }
        }
        // YEARLY LIMITS
        if($agentLimitDetails['cnt_out_max_txn_yearly'] > 0 || $agentLimitDetails['limit_out_max_yearly'] > 0)
        {
            $startDate = date("Y-01-01");
            $endDate = date("Y-12-31");   
            $row = $txnAgentModel->getTxnAgentDuration($agentId, $startDate, $endDate);
            if($row)
            {
                $count = ($row['count'] > 0) ? $row['count'] : 0;
                $total = ($row['total'] > 0) ? $row['total'] : 0;
                $flglimit = $this->getAgentLoadLimitFlag($count, $total, $amount, $agentLimitDetails['cnt_out_max_txn_yearly'], $agentLimitDetails['limit_out_max_yearly'], "Yearly");
            }
        }
        return TRUE;
    }
    
    private function getAgentLoadLimitFlag($count, $total, $amount, $cntMax, $amtMax, $period = "Daily", $txt = "Agent")
    {
        $totalAmt = $total+$amount;
        if($cntMax > 0 && $count >= $cntMax)
        {
            App_Logger::log("Transaction will exceed Max. No. of ".$period." Txns Allowed for ".$txt.". Max ".$period." No. of Txns Allowed: ".$cntMax.". No. of Txns already done: ".$count, Zend_Log::ALERT);
            throw new App_Exception ("Transaction will exceed Max. No. of ".$period." Txns Allowed for ".$txt.". Max ".$period." No. of Txns Allowed: ".$cntMax.". No. of Txns already done: ".$count);
        }
        elseif($amtMax > 0 && $totalAmt > $amtMax)
        {
            App_Logger::log("Transaction Amount will exceed Max. Amount of ".$period." Txns Allowed for ".$txt.". Max ".$period." Txns Allowed: ".Util::numberFormat($amtMax).". Amount of Txns already done: ".Util::numberFormat($total).". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new App_Exception ("Transaction Amount will exceed Max. Amount of ".$period." Txns Allowed for ".$txt.". Max ".$period." Txns Allowed: ".Util::numberFormat($amtMax).". Amount of Txns already done: ".Util::numberFormat($total).". Amount tried: ".Util::numberFormat($amount));
        } 
        return TRUE;
    }
    
    /* Balance available for  cardloads */
    public function chkAvailableCorporateBalance($corpId, $amount)
    {
        $corpAmt = $this->getCorporateBalance($corpId);
        $minCorpBalReqd = 0; //$this->getSettingMinBalance();
        $loadAmtAndBalance = $amount + $minCorpBalReqd;
        if($corpAmt < $loadAmtAndBalance)
        {
            App_Logger::log("Corporate does not have sufficient fund. Corporate Balance: ".Util::numberFormat($corpAmt).". Minimum Balance Reqd.: ".Util::numberFormat($minCorpBalReqd).". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new App_Exception ("Corporate does not have sufficient fund. Corporate Balance: ".Util::numberFormat($corpAmt).". Minimum Balance Reqd.: ".Util::numberFormat($minCorpBalReqd).". Amount tried: ".Util::numberFormat($amount));
        }
        return TRUE;
    }
    
    /* fetches balance */
    protected function getCorporateBalance($corpId)
    {
        $balanceModel = new CorporateBalance();
        $corpAmt = $balanceModel->getCorporateBalance($corpId);
        if($corpAmt)
        {
            return $corpAmt;
        }
        App_Logger::log("Could not find Corporate balance", Zend_Log::ALERT);
        throw new App_Exception ("Could not find Corporate balance");
    }
    
    /* min balance reqd. in agent a/c */
//    protected function getSettingMinBalance()
//    {
//        $settingModel = new AgentSetting();
//        $minBal = $settingModel->getAgentSettingValue(SETTING_AGENT_MIN_BALANCE);
//        if(isset($minBal) && $minBal > 0){
//            return $minBal;
//        }
//        return 0;
//    }
    
    /* 
     * fetches agent limits 
     */
    public function getCorporateLimitDetails($corpId)
    {
        
        $corpLimitModel = new Corporatelimit();
        $corpLimitDetails = $corpLimitModel->getCorporateLimitDetails($corpId);
        if($corpLimitDetails)
        {
            return $corpLimitDetails;
        }
        return FALSE;
    }
    
    /* if amount is within range */
    public function getMinMaxLimitFlag($amount, $limitOutMinTxn, $limitOutMaxTxn, $txt = "Corporate")
    {
        if($limitOutMinTxn > 0 && $amount < $limitOutMinTxn)
        {
            App_Logger::log("Amount less than Min. Per Txn for ".$txt.". Min Per Txn Amount Allowed: ".Util::numberFormat($limitOutMinTxn).". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new App_Exception ("Amount less than Min. Per Txn for ".$txt.". Min Per Txn Amount Allowed: ".Util::numberFormat($limitOutMinTxn).". Amount tried: ".Util::numberFormat($amount));
        }
        elseif($limitOutMaxTxn > 0 && $amount > $limitOutMaxTxn)
        {  
            App_Logger::log("Amount exceeds Max. Per Txn for ".$txt.". Max Per Txn Amount Allowed: ".Util::numberFormat($limitOutMaxTxn).". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new App_Exception ("Amount exceeds Max. Per Txn for ".$txt.". Max Per Txn Amount Allowed: ".Util::numberFormat($limitOutMaxTxn).". Amount tried: ".Util::numberFormat($amount));
        }
        return TRUE;
    }
   
    /*
     * chk agent txn stats, D M Y
     */
    private function validateCorporateTxnStats($corpId, $amount, $corpLimitDetails)
    {
        $txnCorpModel = new TxnCorporate();
        
        // DAILY LIMITS
        if($corpLimitDetails['cnt_out_max_txn_daily'] > 0 || $corpLimitDetails['limit_out_max_daily'] > 0)
        {
            $curDate = date('Y-m-d'); 
            $row = $txnCorpModel->getTxnCorporateDaily($corpId, $curDate);
            if($row)
            {
                $count = ($row['count'] > 0) ? $row['count'] : 0;
                $total = ($row['total'] > 0) ? $row['total'] : 0;
                $flglimit = $this->getLoadLimitFlag($count, $total, $amount, $corpLimitDetails['cnt_out_max_txn_daily'], $corpLimitDetails['limit_out_max_daily'], "Daily");
            }
        }
        // MONTHLY LIMITS
        if($corpLimitDetails['cnt_out_max_txn_monthly'] > 0 || $corpLimitDetails['limit_out_max_monthly'] > 0)
        {
            $curMonth = date('m');
            $curYear = date('Y');
            $curMonthDays = Util::getMonthDays($curMonth, $curYear);
            $startDate = $curYear.'-'.$curMonth.'-01';
            $endDate = $curYear.'-'.$curMonth.'-'.$curMonthDays;   
            $row = $txnCorpModel->getTxnCorporateDuration($corpId, $startDate, $endDate);
            if($row)
            {
                $count = ($row['count'] > 0) ? $row['count'] : 0;
                $total = ($row['total'] > 0) ? $row['total'] : 0;
                $flglimit = $this->getLoadLimitFlag($count, $total, $amount, $corpLimitDetails['cnt_out_max_txn_monthly'], $corpLimitDetails['limit_out_max_monthly'], "Monthly");
            }
        }
        // YEARLY LIMITS
        if($corpLimitDetails['cnt_out_max_txn_yearly'] > 0 || $corpLimitDetails['limit_out_max_yearly'] > 0)
        {
            $startDate = date("Y-01-01");
            $endDate = date("Y-12-31");   
            $row = $txnCorpModel->getTxnCorporateDuration($corpId, $startDate, $endDate);
            if($row)
            {
                $count = ($row['count'] > 0) ? $row['count'] : 0;
                $total = ($row['total'] > 0) ? $row['total'] : 0;
                $flglimit = $this->getLoadLimitFlag($count, $total, $amount, $corpLimitDetails['cnt_out_max_txn_yearly'], $corpLimitDetails['limit_out_max_yearly'], "Yearly");
            }
        }
        return TRUE;
    }
    
    private function getLoadLimitFlag($count, $total, $amount, $cntMax, $amtMax, $period = "Daily", $txt = "Corporate")
    {
        $totalAmt = $total+$amount;
        if($cntMax > 0 && $count >= $cntMax)
        {
            App_Logger::log("Transaction will exceed Max. No. of ".$period." Txns Allowed for ".$txt.". Max ".$period." No. of Txns Allowed: ".$cntMax.". No. of Txns already done: ".$count, Zend_Log::ALERT);
            throw new App_Exception ("Transaction will exceed Max. No. of ".$period." Txns Allowed for ".$txt.". Max ".$period." No. of Txns Allowed: ".$cntMax.". No. of Txns already done: ".$count);
        }
        elseif($amtMax > 0 && $totalAmt > $amtMax)
        {
            App_Logger::log("Transaction Amount will exceed Max. Amount of ".$period." Txns Allowed for ".$txt.". Max ".$period." Txns Allowed: ".Util::numberFormat($amtMax).". Amount of Txns already done: ".Util::numberFormat($total).". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new App_Exception ("Transaction Amount will exceed Max. Amount of ".$period." Txns Allowed for ".$txt.". Max ".$period." Txns Allowed: ".Util::numberFormat($amtMax).". Amount of Txns already done: ".Util::numberFormat($total).". Amount tried: ".Util::numberFormat($amount));
        } 
        return TRUE;
    }
    
    
}
