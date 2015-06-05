<?php

/*
 * Validator class for kotak remittance
 * 
 */
class Remit_Kotak_Validator extends Validator_LimitValidator
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
    protected $_name = DbTable::TABLE_KOTAK_REMITTANCE_REQUEST;
    
    /*  Agent Allowed remitter regn for selected product     */
    public function chkAllowRemitterRegn($params)
    {
        if($params['agent_id'] == '' || $params['amount'] == '' )
            throw new App_Exception ('Insufficient Data for validating Kotak Remitter Regn.');
        
        /* check agent available balance */
        $this->chkAvailableAgentBalance($params['agent_id'], $params['amount']);
        
        /*
         * no need to check against agent limits
         * $availableAgentBalance = $this->chkAvailableAgentBalance($params['agent_id'], $params['amount']);
        if($availableAgentBalance)
        {
            $agentLimitDetails = $this->getAgentLimitDetails($params['agent_id']);
            if($agentLimitDetails)
            {
                $this->validateAgentTxnStats($params['agent_id'], $params['amount'], $agentLimitDetails);
            }
            return true;
        }*/
        return true;
        
    }
    

    /*  Agent Allowed remittance for the remitter for selected product     */
    public function chkAllowRemit($params)
    {
        if($params['agent_id'] == '' || $params['product_id'] == '' || $params['remitter_id'] == '' || $params['amount'] == '' )
	    throw new App_Exception(ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_KOTAK_REMITTANCE_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_KOTAK_REMITTANCE_CODE);
        
        /* check agent available balance */
        $availableAgentBalance = $this->chkAvailableAgentBalance($params['agent_id'], $params['amount']);
        if($availableAgentBalance)
        {
            /* check product limit per remitter for remit */
            $allowRemitterAmt = $this->chkRemitterLimits($params);
            if($allowRemitterAmt)
            {
                /* check agent limits */
                $this->chkAllowAgentLoad($params);

            }
        }
        return true;
        
    }
    
    /*
     * on basis of product remitter limits
     */
    public function chkRemitterLimits($params)
    {
        $agentProductModel = new BindAgentProductCommission();
        $prodLimitDetails = $agentProductModel->getAgentProductLimitDetails($params['agent_id'], $params['product_id']);
        if($prodLimitDetails)
        {
            $txnAmount = $params['amount'] - $params['fee_amt'] - $params['service_tax'];
            $flgRemitterTxnRange = $this->getMinMaxLimitFlag($txnAmount, KOTAK_REMITTANCE_MIN_AMOUNT_PER_TXN, $prodLimitDetails['customer_limit_out_max_txn'], "Remitter");
            if( $flgRemitterTxnRange )
            {
                $this->validateRemitterProductTxnStats($params['remitter_id'], $params['product_id'], $txnAmount, $prodLimitDetails);
            }
           
        }
        return true;
    }
    
    /*  Agent Allowed refund remittance for the remitter for selected product     */
    public function chkAllowRefundRemit($params)
    {
        return true;
        
        /*** #1020 - no need to chk limit validation on refund */
//        if($params['agent_id'] == '' || $params['product_id'] == '' || $params['amount'] == '' )
//            throw new App_Exception ('Insufficient Data');
//        
//        /* check agent available balance */
//        $availableAgentBalance = $this->chkAvailableAgentBalance($params['agent_id'], $params['amount']);
//        if($availableAgentBalance)
//        {
//            $params['fee_amt'] = $params['reversal_fee_amt'];
//            $params['service_tax'] = $params['reversal_service_tax'];
//            /* check agent limits */
//            $this->chkAllowAgentLoad($params);
//        }
//        return true;
        
    }
    
    /*
     * chk remitter txn stats for the product, right now monthly chk is reqd.
     */
    public function validateRemitterProductTxnStats($remitterId, $productId, $amount, $prodLimitDetails)
    {
        $txnRemitterModel = new Remit_Kotak_Remittancerequest();
        
        // MONTHLY LIMITS
        $curMonth = date('m');
        $curYear = date('Y');
        $curMonthDays = Util::getMonthDays($curMonth, $curYear);
        $startDate = $curYear.'-'.$curMonth.'-01';
        $endDate = $curYear.'-'.$curMonth.'-'.$curMonthDays;   
//        $row = $txnRemitterModel->getTxnRemitterProductDuration($remitterId, $productId, $startDate, $endDate);
        $row = $txnRemitterModel->getValidateTxnRemitterProductDuration($remitterId, $productId, $startDate, $endDate);
        if($row)
        {
            $total = ($row['total'] > 0) ? $row['total'] : 0;
            $this->getRemitterLimitFlag($total, $amount, $prodLimitDetails['customer_limit_out_max_monthly'], "Monthly", "Remitter for this Product");
        }
        
    }
    
}
