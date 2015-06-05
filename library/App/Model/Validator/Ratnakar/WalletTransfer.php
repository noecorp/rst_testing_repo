<?php

/*
 * Validator class for remittance
 * 
 */

class Validator_Ratnakar_WalletTransfer extends Validator_Ratnakar {
 
  /*  Customer Allowed remittance for the remitter for selected product     */
    public function chkAllowTransfer($params)
    {
        if($params['agent_id'] == '' || $params['product_id'] == '' || $params['remitter_id'] == '' || $params['amount'] == '' ) 
	    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_TO_VALIDATE_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_TO_VALIDATE_CODE);
	
        /* check From Customer available balance */
        $availableCustBalance = $this->chkAvailableCustBalance($params['rat_customer_id'], $params['purse_master_id']);
        if($availableCustBalance)
        {
            /* check product limit per remitter for remit */
            $allowRemitterAmt = $this->chkToCustomerMaxBal($params);
            
        }
        return true;
        
    }
    
   

    /* Balance available for remits / refunds / cardloads */
    public function chkAvailableCustBalance($ratCustID, $purseID ,$amount,$claim_amount=0)
    {
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $custAmt = $custPurseModel->getCustBalanceByCustIDPurseID($ratCustID, $purseID);
	$custBlkAmt = $custPurseModel->getCustBlockBalanceByCustIDPurseID($ratCustID, $purseID);
	$custActualAmt = $custAmt - $custBlkAmt + $claim_amount;	
	if($custActualAmt < $amount)
        {   
            throw new Exception ("Customer does not have sufficient fund. Available Balance: ".Util::numberFormat($custActualAmt)." Amount to be deducted: ".Util::numberFormat($amount), ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_REMITTANCE_CUST_BALANCE_CODE);
        }
       
        return true;
    }
  
    
    /* Balance available for remits / refunds / cardloads */
    public function chkToCustomerMaxBal($masterPurseID)
    {
        $masterPurseModel = new MasterPurse();
        $custMaxAmt = $masterPurseModel->findById($masterPurseID);
        $availableCustBalance = $this->chkAvailableCustBalance($params['txn_rat_customer_id'], $params['txn_purse_master_id']);
        $loadAmt = $params['amount'] + $availableCustBalance;
        if($loadAmt > $custMaxAmt['max_balance'])
        {
	    throw new Exception(ErrorCodes::ERROR_EDIGITAL_MAX_BALANCE_ALLOWED_EXCEED_MSG, ErrorCodes::ERROR_EDIGITAL_MAX_BALANCE_ALLOWED_EXCEED_CODE); 
        }
        return true;
    }
    
    /*  Customer Allowed remittance for the remitter for selected product from API request     */
    public function chkAllowTransferAPI($params)
    {
        if($params['product_id'] == '' || $params['rat_customer_id'] == '' || $params['amount'] == '' )
	    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_TO_VALIDATE_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_TO_VALIDATE_CODE); 
        $params['claim_amount'] = (isset($params['claim_amount']))?$params['claim_amount']:0;
        /* check From Customer available balance */ 
        $availableCustBalance = $this->chkAvailableCustBalance($params['rat_customer_id'], $params['purse_master_id'], $params['amount'],$params['claim_amount']);
        if($availableCustBalance)
        {
            /* check product limit per remitter for remit */
            $allowRemitterAmt = $this->chkToCustomerMaxBalAPI($params);
            
        }
        return true;
        
    }
    
    /* Balance available for remits / refunds / cardloads */
    public function chkToCustomerMaxBalAPI($params)
    {
        $masterPurseModel = new MasterPurse();
        $custMaxAmt = $masterPurseModel->findById($params['purse_master_id']);
	$params['claim_amount'] = (isset($params['claim_amount']))?$params['claim_amount']:0;
        $availableCustBalance = $this->chkAvailableCustBalance($params['rat_customer_id'], $params['purse_master_id'], $params['amount'],$params['claim_amount']);
        $loadAmt = $params['amount'] + $availableCustBalance;
        if($loadAmt > $custMaxAmt['max_balance'])
        {
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_MAX_BALANCE_ALLOWED_EXCEED_MSG, ErrorCodes::ERROR_EDIGITAL_MAX_BALANCE_ALLOWED_EXCEED_CODE);
        }
        
       return true;
    }
    
    /* Balance available for remits / refunds / cardloads */
    public function chkToBeneficiaryMaxBalAPI($params)
    {
        $masterPurseModel = new MasterPurse();
        $custMaxAmt = $masterPurseModel->findById($params['purse_master_id']);
        $availableCustBalance = $this->chkAvailableCustBalanceAPI($params['rat_customer_id'], $params['purse_master_id'], $params['amount']);
        $loadAmt = $params['amount'] + $availableCustBalance;

        if($loadAmt > $custMaxAmt['max_balance'])
        { 
	    throw new Exception(ErrorCodes::ERROR_EDIGITAL_MAX_BALANCE_ALLOWED_EXCEED_MSG, ErrorCodes::ERROR_EDIGITAL_MAX_BALANCE_ALLOWED_EXCEED_CODE);
        }
        
       return true;
    }
    
    /* Balance available for remits / refunds / cardloads */
    public function chkAvailableCustBalanceAPI($ratCustID, $purseID ,$amount)
    {
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        return $custPurseModel->getCustBalanceByCustIDPurseID($ratCustID, $purseID);
    }
}
