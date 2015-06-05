<?php

/*
 * Validator class for remittance
 * 
 */

class Validator_Ratnakar_Remittance extends Validator_Ratnakar {
 
  /*  Customer Allowed remittance for the remitter for selected product     */
    public function chkAllowRemit($params)
    {
        if($params['agent_id'] == '' || $params['product_id'] == '' || $params['remitter_id'] == '' || $params['amount'] == '' )
            throw new App_Exception (ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_REMITTANCE_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_REMITTANCE_CODE);
       
        /* check Customer available balance */
        $availableCustBalance = $this->chkAvailableCustBalance($params['rat_customer_id'], $params['purse_master_id'],$params['amount']);
        if($availableCustBalance)
        {
            /* check product limit per remitter for remit */
            $allowRemitterAmt = $this->chkRemitterLimits($params);
            
        }
        return true;
        
    }
    
     /*
     * on basis of product remitter limits
     */
    public function chkRemitterLimits($params)
    {
       
        $config = App_DI_Container::get('ConfigObject');
        $maxAmount = $config->rbl->remit->max->amount;
        $maxAmountMonthly = $config->rbl->remit->max->monthly;
      
        $txnAmount = $params['amount'] - $params['fee_amt'] - $params['service_tax'];
        if($txnAmount > 0 && $txnAmount > $maxAmount){
            throw new Exception (ErrorCodes::ERROR_EDIGITAL_AMT_EXCEED_PER_TXN_MSG, ErrorCodes::ERROR_EDIGITAL_AMT_EXCEED_PER_TXN_CODE);
        }
        $this->validateRemitterProductTxnStats($params['remitter_id'], $params['product_id'], $txnAmount, $maxAmountMonthly);
       
        return true;
    }

    /* Balance available for remits / refunds / cardloads */
    public function chkAvailableCustBalance($ratCustID, $purseID ,$amount)
    {
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $custAmt = $custPurseModel->getCustBalanceByCustIDPurseID($ratCustID, $purseID);
	$custBlkAmt = $custPurseModel->getCustBlockBalanceByCustIDPurseID($ratCustID, $purseID);
	$custActualAmt = $custAmt - $custBlkAmt;
        $loadAmt = $amount;
        if($custActualAmt < $loadAmt)
        {
            throw new Exception (ErrorCodes::getRemitInsufficientFundMsg(Util::numberFormat($custActualAmt), Util::numberFormat($amount)), ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_REMITTANCE_CUST_BALANCE_CODE);
        }
        return true;
    }
    public function validateRemitterProductTxnStats($remitterId, $productId, $amount, $customer_limit_out_max_monthly)
    {
        $txnRemitterModel = new Remit_Ratnakar_Remittancerequest();
        
        // MONTHLY LIMITS
        $curMonth = date('m');
        $curYear = date('Y');
        $curMonthDays = Util::getMonthDays($curMonth, $curYear);
        $startDate = $curYear.'-'.$curMonth.'-01';
        $endDate = $curYear.'-'.$curMonth.'-'.$curMonthDays;   
        $row = $txnRemitterModel->getTxnRemitterProductDuration($remitterId, $productId, $startDate, $endDate);
        if($row)
        {
            $total = ($row['total'] > 0) ? $row['total'] : 0;
            $this->getRemitterLimitFlag($total, $amount, $customer_limit_out_max_monthly, "Monthly", "Remitter for this Product");
        }
        
    }
    
    /* 
     * Remitter Monthly Limit Flag
     */
    public function getRemitterLimitFlag($total, $amount, $amtMax, $period = "Monthly", $txt = "Remitter")
    {
        $totalAmt = $total+$amount;
        if($amtMax > 0 && $totalAmt > $amtMax)
        {
            throw new Exception (ErrorCodes::getRemitterLimitFlagMsg($period,$txt,$amtMax,$total,$amount), ErrorCodes::ERROR_EDIGITAL_REMITTER_LIMIT_FLAG_CODE);
            return false;
        } 
        return true;
    }
}
