<?php

/*
 * Validator class for agent limits
 * 
 */
class Validator_LimitValidator extends App_Model
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
    protected $_name = DbTable::TABLE_REMITTANCE_REQUEST;
    
   
    /* Balance available for remits / refunds / cardloads */
    public function chkAvailableAgentBalance($agentId, $amount)
    {
        $agentAmt = $this->getAgentBalance($agentId);
        $minAgentBalReqd = $this->getSettingMinBalance();
        $loadAmtAndBalance = $amount + $minAgentBalReqd;

        error_log('agentAmt');
        error_log($agentAmt);
        error_log('loadAmtAndBalance');
        error_log($loadAmtAndBalance);

        if($agentAmt < $loadAmtAndBalance)
        {
	    throw new Exception (ErrorCodes::getAgentnotHaveSuffienctFundMsg($agentAmt,$minAgentBalReqd,$amount), ErrorCodes::ERROR_EDIGITAL_AGENT_NOT_HAVE_SUFFICIENT_FUND_CODE);
	     
        }
        return true;
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
	throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_AGENT_BALANCE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AGENT_BALANCE_CODE);
        return false;
    }
    
    protected function getSettingMinBalance()
    {
        $settingModel = new AgentSetting();
        $minBal = $settingModel->getAgentSettingValue(SETTING_AGENT_MIN_BALANCE);
        if(isset($minBal) && $minBal > 0){
            return $minBal;
        }
        return 0;
    }
    
    
    
    
    /* if remit amount is within range */
    public function getMinMaxLimitFlag($amount, $limitOutMinTxn, $limitOutMaxTxn, $txt = "Remitter")
    {
        if($limitOutMinTxn > 0 && $amount < $limitOutMinTxn)
        {
	    throw new Exception (ErrorCodes::getAmountLessthanMinTxnMsg($txt,$limitOutMinTxn,$amount), ErrorCodes::ERROR_EDIGITAL_AMOUNT_LESS_THAN_MIN_TXN_CODE);
            return false;
        }
        elseif($limitOutMaxTxn > 0 && $amount > $limitOutMaxTxn)
        {
	    throw new Exception (ErrorCodes::getAmountExceedMaxTxnMsg($txt,$limitOutMaxTxn,$amount), ErrorCodes::ERROR_EDIGITAL_AMOUNT_EXCEED_MAX_TXN_CODE);
            return false;
        }
        return true;
    }
    
    
    /* 
     * Remitter Monthly Limit Flag
     */
    public function getRemitterLimitFlag($total, $amount, $amtMax, $period = "Monthly", $txt = "Remitter")
    {
        $totalAmt = $total+$amount;
        if($amtMax > 0 && $totalAmt > $amtMax)
        {
	    throw new Exception (ErrorCodes::getRemittanceAmountExceedMaxAmountPeriodMsg($period,$txt,$amtMax), ErrorCodes::ERROR_EDIGITAL_REMITTANCE_AMOUNT_EXCEED_MAX_AMOUNT_CODE);
	    
            return false;
        } 
        return true;
    }
    
    /*
     * on basis of agent limits
     */
    public function chkAllowAgentLoad($params)
    {
        $agentLimitDetails = $this->getAgentLimitDetails($params['agent_id']);
        if($agentLimitDetails)
        {
            $txnAmount = $params['amount'];
            if(isset($params['fee_amt'])){
                $txnAmount = $txnAmount - $params['fee_amt'];
            }
            if(isset($params['service_tax'])){
                $txnAmount = $txnAmount - $params['service_tax'];
            }
            $flgAgentTxnRange = $this->getMinMaxLimitFlag($txnAmount, $agentLimitDetails['limit_out_min_txn'], $agentLimitDetails['limit_out_max_txn'], "Agent");
            if( $flgAgentTxnRange )
            {
                $this->validateAgentTxnStats($params['agent_id'], $txnAmount, $agentLimitDetails);
            }
        }
        return true;
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
        else
            return false;
    }
    
    public function getAgentProductLimitDetails($agentId, $productId)
    {
        $agentProductModel = new BindAgentProductCommission();
        $agentProdLimitDetails = $agentProductModel->getAgentProductLimitDetails($agentId, $productId);
        if($agentProdLimitDetails)
        {
            return $agentProdLimitDetails;
        }
        else
            return false;
        //throw new Exception ("Could not find Agent Product Limit Details");
    }
    
    /*
     * chk agent txn stats, D M Y
     */
    private function validateAgentTxnStats($agentId, $amount, $agentLimitDetails)
    {
        $txnAgentModel = new TxnAgent();
        
        // DAILY LIMITS
        $curDate = date('Y-m-d'); 
        $row = $txnAgentModel->getTxnAgentDaily($agentId, $curDate);
        if($row)
        {
            $count = ($row['count'] > 0) ? $row['count'] : 0;
            $total = ($row['total'] > 0) ? $row['total'] : 0;
            $flglimit = $this->getAgentLoadLimitFlag($count, $total, $amount, $agentLimitDetails['cnt_out_max_txn_daily'], $agentLimitDetails['limit_out_max_daily'], "Daily");
        }
        // MONTHLY LIMITS
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
        // YEARLY LIMITS
        $startDate = date("Y-01-01");
        $endDate = date("Y-12-31");   
        $row = $txnAgentModel->getTxnAgentDuration($agentId, $startDate, $endDate);
        if($row)
        {
            $count = ($row['count'] > 0) ? $row['count'] : 0;
            $total = ($row['total'] > 0) ? $row['total'] : 0;
            $flglimit = $this->getAgentLoadLimitFlag($count, $total, $amount, $agentLimitDetails['cnt_out_max_txn_yearly'], $agentLimitDetails['limit_out_max_yearly'], "Yearly");
        }
        return true;
    }
    
    public function getAgentLoadLimitFlag($count, $total, $amount, $cntMax, $amtMax, $period = "Daily", $txt = "Agent")
    {
        $totalAmt = $total+$amount;
        if($cntMax > 0 && $count >= $cntMax)
        {
            throw new Exception ("Transaction will exceed Max. No. of ".$period." Txns Allowed for ".$txt.". Max ".$period." No. of Txns Allowed: ".$cntMax.". No. of Txns already done: ".$count);
            return false;
        }
        elseif($amtMax > 0 && $totalAmt > $amtMax)
        {
            throw new Exception ("Transaction Amount will exceed Max. Amount of ".$period." Txns Allowed for ".$txt.". Max ".$period." Txns Allowed: ".Util::numberFormat($amtMax).". Amount of Txns already done: ".Util::numberFormat($total).". Amount tried: ".Util::numberFormat($amount));
            return false;
        } 
        return true;
    }
    
    
    /*
     * on basis of product agent load limits
     */
    public function chkAgentProductReLoad($params)
    {
        $agentProdLimitDetails = $this->getAgentProductLimitDetails($params['agent_id'], $params['product_id']);
        if($agentProdLimitDetails)
        {
            $flgAgentTxnRange = $this->getMinMaxLimitFlag($params['amount'], $agentProdLimitDetails['limit_out_min_txn'], $agentProdLimitDetails['limit_out_max_txn'], "Agent for this Product");
            if( $flgAgentTxnRange )
            {
                $this->validateAgentProductTxnStats($params['agent_id'], $params['product_id'], $params['amount'], $agentProdLimitDetails);
            }
        }
        return true;
    }
    
    private function validateAgentProductTxnStats($agentId, $productId, $amount, $agentLimitDetails)
    {
        $txnAgentModel = new TxnAgent();
        
        // DAILY LIMITS
        $curDate = date('Y-m-d'); 
        $row = $txnAgentModel->getTxnAgentProductDaily($agentId, $productId, $curDate);
        if($row)
        {
            $count = ($row['count'] > 0) ? $row['count'] : 0;
            $total = ($row['total'] > 0) ? $row['total'] : 0;
            $flglimit = $this->getAgentLoadLimitFlag($count, $total, $amount, $agentLimitDetails['cnt_out_max_txn_daily'], $agentLimitDetails['limit_out_max_daily'], "Daily", "Agent for this Product");
        }
        // MONTHLY LIMITS
        $curMonth = date('m');
        $curYear = date('Y');
        $curMonthDays = Util::getMonthDays($curMonth, $curYear);
        $startDate = $curYear.'-'.$curMonth.'-01';
        $endDate = $curYear.'-'.$curMonth.'-'.$curMonthDays;   
        $row = $txnAgentModel->getTxnAgentProductDuration($agentId, $productId, $startDate, $endDate);
        if($row)
        {
            $count = ($row['count'] > 0) ? $row['count'] : 0;
            $total = ($row['total'] > 0) ? $row['total'] : 0;
            $flglimit = $this->getAgentLoadLimitFlag($count, $total, $amount, $agentLimitDetails['cnt_out_max_txn_monthly'], $agentLimitDetails['limit_out_max_monthly'], "Monthly", "Agent for this Product");
        }
        // YEARLY LIMITS
        $startDate = date("Y-01-01");
        $endDate = date("Y-12-31");   
        $row = $txnAgentModel->getTxnAgentProductDuration($agentId, $productId, $startDate, $endDate);
        if($row)
        {
            $count = ($row['count'] > 0) ? $row['count'] : 0;
            $total = ($row['total'] > 0) ? $row['total'] : 0;
            $flglimit = $this->getAgentLoadLimitFlag($count, $total, $amount, $agentLimitDetails['cnt_out_max_txn_yearly'], $agentLimitDetails['limit_out_max_yearly'], "Yearly", "Agent for this Product");
        }
        return true;
    }
    
    /* Balance available for remits / refunds / cardloads */
    public function chkAvailableCorporateBalance($corporateId, $amount)
    {
        $corporateAmt = $this->getCorporateBalance($corporateId);
        $minCorporateBalReqd = $this->getSettingCorporateMinBalance();
        $loadAmtAndBalance = $amount + $minCorporateBalReqd;
        if($corporateAmt < $loadAmtAndBalance)
        {
            throw new Exception ("Corporate does not have sufficient fund. Corporate Balance: ".Util::numberFormat($corporateAmt).". Minimum Balance Reqd.: ".Util::numberFormat($minCorporateBalReqd).". Amount tried: ".Util::numberFormat($amount));
        }
        return true;
    }
    
    /* fetches agent balance */
    protected function getCorporateBalance($corporateId)
    {
        $corporateBalanceModel = new CorporateBalance();
        $corpoorateAmt = $corporateBalanceModel->getCorporateBalance($corporateId);
        if($corpoorateAmt)
        {
            return $corpoorateAmt;
        }
        throw new Exception ("Could not find Corporate balance");
        return false;
    }
    
    protected function getSettingCorporateMinBalance()
    {
        $settingModel = new CorporateSetting();
        $minBal = $settingModel->getCorporateSettingValue(SETTING_CORPORATE_MIN_BALANCE);
        if(isset($minBal) && $minBal > 0){
            return $minBal;
        }
        return 0;
    }
    
    
}
