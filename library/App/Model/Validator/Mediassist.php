<?php

/*
 * Validator class for Medi-assist
 * 
 */
class Validator_Mediassist extends Validator_PurseParameterValidator
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
    protected $_name = DbTable::TABLE_RAT_CORP_LOAD_REQUEST;
    
    /*
     * $params['load_request_id'] = load_request_id
     * $params['customer_master_id'] = customer master id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount
     */
    public function chkAllowRatCorporateCardLoad($params)
    {
         if ($params['load_request_id'] == '' || $params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' ){
            throw new App_Exception('Insufficient Data for validating Corporate CardLoad');
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
        
        return TRUE;
      
    }
    
    
    public function chkAllowRatCrAdj($params)
    {
         if ($params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' ){
            throw new App_Exception('Insufficient Data for validating Corporate CardLoad');
        }
        
        $purseParams = $this->getPurseParameters($params['purse_master_id']);
        $this->chkPurseBalance($params['customer_purse_id'], $params['amount'], $purseParams['max_balance']);
        
        return TRUE;
      
    }
    
    /*
     * thru API
     * $params['load_request_id'] = load_request_id
     * $params['customer_master_id'] = customer master id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount
     * $params['agent_id'] =>  MEDIASSIST_AGENT_ID,
     * $params['product_id'] =>  product_id,
     */
    public function chkAllowRatMediAssistCardLoad($params)
    {
        if ($params['load_request_id'] == '' || $params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' 
              || $params['agent_id'] == '' || $params['product_id'] == ''){
            throw new App_Exception(ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_CARDLOAD_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_CARDLOAD_CODE);
        }
       if($params['manageType'] == CORPORATE_MANAGE_TYPE){
           $txt = 'Corporate';
       }
       else{
           $txt = 'Agent';
       }
       
       $isVirtual = (isset($params['is_virtual'])) ? $params['is_virtual'] : FLAG_NO;
         /* check agent available balance */
        $availableAgentBalance = $this->chkAvailableAgentBalance($params['agent_id'], $params['amount'],$params['manageType'],$params['is_virtual']);
        if($availableAgentBalance)
        {
             /* chk agent limits */
            if($isVirtual != FLAG_YES){ // check only for non 
                $agentLimitDetails = $this->getAgentLimitDetails($params['agent_id'],$params['manageType']);

                if($agentLimitDetails)
                {
                    $flgAgentTxnRange = $this->getMinMaxLimitFlag($params['amount'], $agentLimitDetails['limit_out_min_txn'], $agentLimitDetails['limit_out_max_txn'], $txt);
                    if( $flgAgentTxnRange )
                    {
                        $this->validateAgentTxnStats($params['agent_id'], $params['amount'], $agentLimitDetails);
                    }
                }
            
            }
            /* chk purse params */
            $purseParams = $this->getPurseParameters($params['purse_master_id']);
        
            /* chk against max balance param for the purse */
            if($this->chkPurseBalance($params['customer_purse_id'], $params['amount'], $purseParams['max_balance'], $params['bank_unicode']))
            {
                /* chk load min max range for the purse */
                if($this->chkMinMaxRange($params['amount'], $purseParams['load_min'], $purseParams['load_max'], $txt." Load"))
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
     * $params['agent_id'] =>  MEDIASSIST_AGENT_ID,
     * $params['product_id'] =>  product_id,
     */
    public function chkAllowRatMediAssistCardDebit($params)
    {
        if ($params['load_request_id'] == '' || $params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' 
              || $params['agent_id'] == '' || $params['product_id'] == ''){
            throw new App_Exception('Insufficient Data for validating CardDebit');
        }
       
        $custBal = $this->getCustomerPurseDetail($params['customer_purse_id']);
        $amount = $params['amount'];
        $proposedBal = $custBal['amount'] - $params['amount'] - $custBal['block_amount'];
        if($proposedBal < MIN_CUSTOMER_BALANCE)
        {
            App_Logger::log("Customer does not have sufficient fund in the wallet. Balance: ".Util::numberFormat($custBal['amount']).". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new App_Exception ("Customer does not have sufficient fund in the wallet. Balance: ".Util::numberFormat($custBal['amount']).". Amount tried: ".Util::numberFormat($amount),  ErrorCodes::ERROR_INSUFFICIENT_BALANCE);
        }
        return TRUE;
      
    }
    
    
    /* Balance available for remits / refunds / cardloads */
    public function chkAvailableAgentBalance($agentId, $amount,$managerType = AGENT_MANAGE_TYPE,$is_virtual = FLAG_NO)
    {
        $managerTypetxt = 'Agent';
        if($managerType == CORPORATE_MANAGE_TYPE){
        $agentAmt = $this->getCorporateBalance($agentId);
        $managerTypetxt = 'Corporate';
        }
        else if($is_virtual == FLAG_YES){
        $agentAmt = $this->getVirtualAgentBalance($agentId);
        }
        else{
        $agentAmt = $this->getAgentBalance($agentId);
        }
        $minAgentBalReqd = $this->getSettingMinBalance();
        $loadAmtAndBalance = $amount + $minAgentBalReqd;
        if($agentAmt < $loadAmtAndBalance)
        {
            App_Logger::log("$managerTypetxt does not have sufficient fund. $managerTypetxt Balance: ".Util::numberFormat($agentAmt).". Minimum Balance Reqd.: ".Util::numberFormat($minAgentBalReqd).". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new App_Exception ("$managerTypetxt does not have sufficient fund. $managerTypetxt Balance: ".Util::numberFormat($agentAmt).". Minimum Balance Reqd.: ".Util::numberFormat($minAgentBalReqd).". Amount tried: ".Util::numberFormat($amount), ErrorCodes::ERROR_EDIGITAL_AGENT_INSUFFICIENT_FUND_CODE);
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
        throw new App_Exception (ErrorCodes::ERROR_EDIGITAL_INVALID_AGENT_BALANCE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AGENT_BALANCE_CODE);
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
    public function getAgentLimitDetails($agentId, $manageType = AGENT_MANAGE_TYPE)
    {
        if($manageType == CORPORATE_MANAGE_TYPE){
        $corpLimitModel = new Corporatelimit();
        $agentLimitDetails = $corpLimitModel->getCorporateLimitDetails($agentId);    
        }
        else{
        $agentLimitModel = new BindAgentLimit();
        $agentLimitDetails = $agentLimitModel->getAgentLimitDetails($agentId);
        }
        if($agentLimitDetails)
        {
            return $agentLimitDetails;
        }
        return FALSE;
    }
    
    /* if amount is within range */
    public function getMinMaxLimitFlag($amount, $limitOutMinTxn, $limitOutMaxTxn, $txt = "Agent")
    {
        if($limitOutMinTxn > 0 && $amount < $limitOutMinTxn)
        {
            App_Logger::log("Amount less than Min. Per Txn for ".$txt.". Min Per Txn Amount Allowed: ".Util::numberFormat($limitOutMinTxn).". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new App_Exception ("Amount less than Min. Per Txn for ".$txt.". Min Per Txn Amount Allowed: ".Util::numberFormat($limitOutMinTxn).". Amount tried: ".Util::numberFormat($amount), ErrorCodes::ERROR_EDIGITAL_AMOUNT_LESS_PER_TXN_CODE);
        }
        elseif($limitOutMaxTxn > 0 && $amount > $limitOutMaxTxn)
        { 
            App_Logger::log("Amount exceeds Max. Per Txn for ".$txt.". Max Per Txn Amount Allowed: ".Util::numberFormat($limitOutMaxTxn).". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new App_Exception ("Amount exceeds Max. Per Txn for ".$txt.". Max Per Txn Amount Allowed: ".Util::numberFormat($limitOutMaxTxn).". Amount tried: ".Util::numberFormat($amount), ErrorCodes::ERROR_EDIGITAL_AMOUNT_EXCEEDS_PER_TXN_CODE);
        }
        return TRUE;
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
            throw new App_Exception ("Transaction will exceed Max. No. of ".$period." Txns Allowed for ".$txt.". Max ".$period." No. of Txns Allowed: ".$cntMax.". No. of Txns already done: ".$count, ErrorCodes::ERROR_EDIGITAL_MAX_TXN_LIMIT_EXCEEDS_CODE);
        }
        elseif($amtMax > 0 && $totalAmt > $amtMax)
        {
            App_Logger::log("Transaction Amount will exceed Max. Amount of ".$period." Txns Allowed for ".$txt.". Max ".$period." Txns Allowed: ".Util::numberFormat($amtMax).". Amount of Txns already done: ".Util::numberFormat($total).". Amount tried: ".Util::numberFormat($amount), Zend_Log::ALERT);
            throw new App_Exception ("Transaction Amount will exceed Max. Amount of ".$period." Txns Allowed for ".$txt.". Max ".$period." Txns Allowed: ".Util::numberFormat($amtMax).". Amount of Txns already done: ".Util::numberFormat($total).". Amount tried: ".Util::numberFormat($amount), ErrorCodes::ERROR_EDIGITAL_MAX_AMOUNT_LIMIT_EXCEEDS_CODE);
        } 
        return TRUE;
    }
    /* fetches agent balance */
    protected function getCorporateBalance($agentId)
    {
        $corpBalanceModel = new CorporateBalance();
        $agentAmt = $corpBalanceModel->getCorporateBalance($agentId);
        if($agentAmt)
        {
            return $agentAmt;
        }
        App_Logger::log("Could not find Corporate balance", Zend_Log::ALERT);
        throw new App_Exception (ErrorCodes::ERROR_EDIGITAL_INVALID_CORPORATE_BALANCE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CORPORATE_BALANCE_CODE);
    }
    
    
     /*
     * chk agent txn stats, D M Y
     */
    private function validateCorpTxnStats($agentId, $amount, $agentLimitDetails)
    {
        $txnAgentModel = new TxnCorporate();
        
        // DAILY LIMITS
        if($agentLimitDetails['cnt_out_max_txn_daily'] > 0 || $agentLimitDetails['limit_out_max_daily'] > 0)
        {
            $curDate = date('Y-m-d'); 
            $row = $txnAgentModel->getTxnCorporateDaily($agentId, $curDate);
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
            $row = $txnAgentModel->getTxnCorporateDuration($agentId, $startDate, $endDate);
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
            $row = $txnAgentModel->getTxnCorporateDuration($agentId, $startDate, $endDate);
            if($row)
            {
                $count = ($row['count'] > 0) ? $row['count'] : 0;
                $total = ($row['total'] > 0) ? $row['total'] : 0;
                $flglimit = $this->getAgentLoadLimitFlag($count, $total, $amount, $agentLimitDetails['cnt_out_max_txn_yearly'], $agentLimitDetails['limit_out_max_yearly'], "Yearly");
            }
        }
        return TRUE;
    }

    public function chkAllowRatReversalCardLoadAPI($params)
    {
        if ($params['load_request_id'] == '' || $params['amount'] == '' 
              || $params['agent_id'] == '' || $params['product_id'] == ''){
            throw new App_Exception(ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_CARDLOAD_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_CARDLOAD_CODE);
        }
       if($params['manageType'] == CORPORATE_MANAGE_TYPE){
           $txt = 'Corporate';
       }
       else{
           $txt = 'Agent';
       }
         /* check agent available balance */
        $availableAgentBalance = $this->chkAvailableAgentBalance($params['agent_id'], $params['amount'],$params['manageType']);
        if($availableAgentBalance)
        {
             /* chk agent limits */
            $agentLimitDetails = $this->getAgentLimitDetails($params['agent_id'],$params['manageType']);
            
            if($agentLimitDetails)
            {
                $flgAgentTxnRange = $this->getMinMaxLimitFlag($params['amount'], $agentLimitDetails['limit_out_min_txn'], $agentLimitDetails['limit_out_max_txn'], $txt);
                if( $flgAgentTxnRange )
                {
                    $this->validateAgentTxnStats($params['agent_id'], $params['amount'], $agentLimitDetails);
                }
            }
            
        }
        
        return TRUE;
    }
    /* fetches virtual agent balance */
    protected function getVirtualAgentBalance($agentId)
    {
        $agentBalanceModel = new AgentVirtualBalance();
        $agentAmt = $agentBalanceModel->getAgentVirtualBalance($agentId);
        if($agentAmt)
        {
            return $agentAmt;
        }
        App_Logger::log("Could not find Agent virtual balance", Zend_Log::ALERT); 
	throw new App_Exception(ErrorCodes::ERROR_NO_AGENT_VIRTUAL_BAL_MSG, ErrorCodes::ERROR_NO_AGENT_VIRTUAL_BAL_CODE);
    }
    
    public function chkAllowRatCardLoadbyAgent($params) {
        if ( $params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' 
              || $params['agent_id'] == '' || $params['product_id'] == ''){
            throw new App_Exception('Insufficient Data for validating CardLoad');
        }
       if($params['manageType'] == CORPORATE_MANAGE_TYPE){
           $txt = 'Corporate';
       }
       else{
           $txt = 'Agent';
       }
         /* check agent available balance */
        $availableAgentBalance = $this->chkAvailableAgentBalance($params['agent_id'], $params['amount'],$params['manageType']);
        if($availableAgentBalance)
        {
             /* chk agent limits */
            $agentLimitDetails = $this->getAgentLimitDetails($params['agent_id'],$params['manageType']);
            
            if($agentLimitDetails)
            {
                $flgAgentTxnRange = $this->getMinMaxLimitFlag($params['amount'], $agentLimitDetails['limit_out_min_txn'], $agentLimitDetails['limit_out_max_txn'], $txt);
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
}
