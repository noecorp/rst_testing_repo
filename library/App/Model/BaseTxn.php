<?php

/**
 * Base User Model
 *
 *
 * @category App
 * @package App_Model
 * @copyright company
 */
class BaseTxn extends TxnRecord {

    public $_msg;

    public function setMessage($msg) {
        $this->_msg = $msg;
    }

    public function getMessage() {
        return $this->_msg;
    }

    /*
     * $params['ops_id'] = ops id
     * $params['agent_id'] = agent id
     * $params['amount'] = amount
     * $params['txn_type'] = txn_type here TXNTYPE_AGENT_FUND_LOAD
     * $params['agent_fund_request_id'] = // fund request id optional
     * $params['agent_funding_id'] = // funding id 
     */

    public function opsToAgent($params) {
        if ($params['ops_id'] == '' || $params['agent_id'] == '' || $params['amount'] == ''){
            throw new Exception('Insufficient Data for fund load from ops to agent');
        }
        $params['txn_status'] = FLAG_SUCCESS;
        $params['remarks'] = '';
        $this->insertTxnOpsToAgent($params);

        return true;
    }
    
    public function opsToAgentVirtual($params) {
        if ($params['ops_id'] == '' || $params['agent_id'] == '' || $params['amount'] == ''){
            throw new Exception('Insufficient Data for virtual fund load from ops to agent');
        }
        $params['txn_status'] = FLAG_SUCCESS;
        $params['remarks'] = '';  
        return $this->insertTxnOpsToAgentVirtual($params);
    }
    
    
    /*
     * $params['agent_id'] = ops id
     * $params['txn_agent_id'] = agent id
     * $params['amount'] = amount
     * $params['txn_type'] = txn_type here TXNTYPE_AGENT_FUND_LOAD
     * $params['agent_fund_request_id'] = // fund request id optional
     * $params['agent_funding_id'] = // funding id 
     */

    public function agentToAgent($params) {
        if ($params['txn_agent_id'] == '' || $params['agent_id'] == '' || $params['amount'] == ''){
            throw new Exception('Insufficient Data for fund load from agent to agent');
        }
        $params['txn_status'] = FLAG_SUCCESS;
        $params['remarks'] = '';
        //$this->insertTxnOpsToAgent($params);
        $this->insertTxnAgentToAgent($params);

        return true;
    }

    /*
     * $params: agent_id, product_id
     */

    public function chkCanAssignProduct($params) {
        if ($params['agent_id'] == '' || $params['product_id'] == ''){
            throw new Exception('Insufficient Data for checking assignment of product by agent');
        }
        $agentBalanceValidator = new Validator_AgentBalance();
        $msg = $agentBalanceValidator->chkCanAssignProduct($params);
        return $msg;
    }

    /*
     * $params: setting section id, amount
     */

    public function chkAgentMaxMinLoad($params) {
        $agBalValid = new Validator_AgentBalance();
        return $agBalValid->chkAgentMaxMinLoad($params);
    }

    /*
     * $params['agent_id'] = agent id
     * $params['product_id'] = product id
     * $params['cardholder_id'] = cardholder id
     * $params['amount'] = amount
     * $params['txn_type'] = txn_type
     */

    public function initiateAgentToCardholder($params) {
        if ($params['agent_id'] == '' || $params['product_id'] == '' || $params['cardholder_id'] == '' || $params['amount'] == '' || $params['txn_type'] == ''){
            throw new Exception('Insufficient Data for initiating load/reload');
        }
        if ($params['txn_type'] == TXNTYPE_FIRST_LOAD) {
            $chkAllow = $this->chkAllowFirstLoad(array('agent_id' => $params['agent_id'], 'product_id' => $params['product_id'], 'amount' => $params['amount']));
        } else { ////if($params['txn_type'] == TXNTYPE_CARD_RELOAD)
            $chkAllow = $this->chkAllowReLoad(array('agent_id' => $params['agent_id'], 'product_id' => $params['product_id'], 'amount' => $params['amount']));
        }
        if ($chkAllow) {
            $params['txn_status'] = FLAG_PENDING;
            $params['remarks'] = '';
            $txnCode = $this->initiateTxnAgentToCardholder($params);
        }
        return array('flag' => true, 'txnCode' => $txnCode);
    }

    /*
     * $params: agent_id, product_id, amount
     */

    public function chkAllowFirstLoad($params) {
        if ($params['agent_id'] == '' || $params['product_id'] == '' || $params['amount'] == ''){
            throw new Exception('Insufficient Data for checking if agent can make first load');
        }
        $agentBalanceValidator = new Validator_AgentBalance();
        $msg = $agentBalanceValidator->chkAllowFirstLoad($params);
        return $msg;
    }

    /*
     * $params: agent_id, product_id, amount
     */

    public function chkAllowReLoad($params) {
        if ($params['agent_id'] == '' || $params['product_id'] == '' || $params['amount'] == ''){
            throw new Exception('Insufficient Data for checking if agent can do reload');
        }
        $agentBalanceValidator = new Validator_AgentBalance();
        $msg = $agentBalanceValidator->chkAllowReLoad($params);
        return $msg;
    }

    /*
     * $params['agent_id'] = agent id
     * $params['cardholder_id'] = cardholder id
     * $params['amount'] = amount
     * $params['txn_code'] = txn_code
     * $params['txn_status'] = txn_status
     * $params['remarks'] = remarks
     */

    public function completeAgentToCardholder($params) {
        if ($params['agent_id'] == '' || $params['cardholder_id'] == '' || $params['amount'] == '' || $params['txn_code'] == '' || $params['txn_status'] == '') {
            throw new Exception('Insufficient Data for completing load/reload');
        }
        $this->completeTxnAgentToCardholder($params);
        return true;
    }

    /*
     * $params['agent_id'] = agent id
     * $params['amount'] = amount
     * $params['bank_unicode'] = bank unicode
     */

    public function chkAllowRemitterRegn($params) {
        if ($params['agent_id'] == '' || $params['amount'] == '') {
            throw new Exception('Insufficient Data for validating Remitter Regn.');
        }

        $bankUnicodeArr = Util::bankUnicodesArray();
        if (!isset($params['bank_unicode']) || $params['bank_unicode'] == '') {
            $params['bank_unicode'] = $bankUnicodeArr['1']; // boi
        }
        
        switch ($params['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $remitValidator = new Remit_Kotak_Validator();
                $msg = $remitValidator->chkAllowRemitterRegn($params);
                break;
            case $bankUnicodeArr['2']:
                $remitValidator = new Remit_Ratnakar_Validator();
                $msg = $remitValidator->chkAllowRemitterRegn($params);
                break;
            case $bankUnicodeArr['1']:
//            default:
                $remitValidator = new Validator_Remittance();
                $msg = $remitValidator->chkAllowRemitterRegn($params);
                break;
        }
        
        return $msg;
        
    }

    /* 
     * $params['agent_id'] = agent_id
     * $params['remitter_id'] = remitter_id
     * $params['product_id'] = product_id
     * $params['fee_amt'] = fee_amt
     * $params['service_tax'] = service_tax
     * $params['bank_unicode'] = bank_unicode
     * 
     */

    public function remitterRegnFee($params) {
        if ($params['agent_id'] == '' || $params['product_id'] == '' || $params['fee_amt'] == '' || $params['service_tax'] == '' || $params['remitter_id'] == '') {
            
            throw new Exception(ErrorCodes::ERROR_INVALID_DATA_FOR_REMITTER_REG_FAILURE_MSG, ErrorCodes::ERROR_INVALID_DATA_FOR_REMITTER_REG_FAILURE_CODE); 
        }

        $bankUnicodeArr = Util::bankUnicodesArray();
        if (!isset($params['bank_unicode']) || $params['bank_unicode'] == '') {
            $params['bank_unicode'] = $bankUnicodeArr['1']; // boi
        }
        $params['amount'] = $params['fee_amt'] + $params['service_tax'];
        switch ($params['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $msg = $this->insertKotakTxnRemitterRegnFee($params);
                break;
            case $bankUnicodeArr['2']:
                $msg = $this->insertRatnakarTxnRemitterRegnFee($params);
                break;
            case $bankUnicodeArr['1']:
//            default:
                $msg = $this->insertBoiTxnRemitterRegnFee($params);
                break;
        }


        return $msg;
    }
    
    /*
     * $params['agent_id'] = agent id
     * $params['product_id'] = product id
     * $params['remitter_id'] = remitter id
     * $params['amount'] = amount
     * $params['fee_amt'] = fee amount
     * $params['service_tax'] = service tax amount
     * $params['bank_unicode'] = bank unicode
     */

    public function chkAllowRemit($params) {

        if ($params['agent_id'] == '' || $params['product_id'] == '' || $params['remitter_id'] == '' || $params['amount'] == '') {
	    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_REMITTANCE_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_REMITTANCE_CODE);
        }

        $bankUnicodeArr = Util::bankUnicodesArray();
        if (!isset($params['bank_unicode']) || $params['bank_unicode'] == '') {
            $params['bank_unicode'] = $bankUnicodeArr['1']; // boi
        }
        switch ($params['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $remitValidator = new Remit_Kotak_Validator();
                $totalAmount = $params['amount'] + $params['fee_amt'] + $params['service_tax'];
                $params['amount'] = $totalAmount;
                $msg = $remitValidator->chkAllowRemit($params);
                break;
            case $bankUnicodeArr['2']:
                $remitValidator = new Remit_Ratnakar_Validator();
                $totalAmount = $params['amount'] + $params['fee_amt'] + $params['service_tax'];
                $params['amount'] = $totalAmount;
                $msg = $remitValidator->chkAllowRemit($params);
                break;
            case $bankUnicodeArr['1']:
//            default:
                $remitValidator = new Validator_Remittance();
                $totalAmount = $params['amount'] + $params['fee_amt'] + $params['service_tax'];
                $params['amount'] = $totalAmount;
                $msg = $remitValidator->chkAllowRemit($params);
                break;
        }

        return $msg;
    }

    /*
     * $params['agent_id'] = agent id
     * $params['product_id'] = product id
     * $params['remitter_id'] = remitter id
     * $params['amount'] = amount
     * $params['reversal_fee_amt'] = reversal fee amt
     * $params['reversal_service_tax'] = reversal service tax
     */

    public function chkAllowRefundRemit($params) {
        return true;

        /*         * * #1020 - no need to chk limit validation on refund */
//        if($params['agent_id'] == '' || $params['product_id'] == '' || $params['amount'] == '' )
//                throw new Exception ('Insufficient Data');
//         
//        $remitValidator = new Validator_Remittance();
//        $msg = $remitValidator->chkAllowRefundRemit($params);
//        return $msg;
    }

    /*
     * $params['agent_id'] = agent id
     * $params['product_id'] = product id
     * $params['remitter_id'] = remitter id
     * $params['amount'] = amount
     * $params['remit_request_id'] = remittance request id
     * $params['fee_amt'] = fee amount
     * $params['service_tax'] = service tax amount
     * $params['bank_unicode'] = bank unicode
     */

    public function initiateRemit($params) {
        if ($params['agent_id'] == '' || $params['product_id'] == '' || $params['remitter_id'] == '' || $params['amount'] == '' || $params['remit_request_id'] == '') {
            throw new Exception('Insufficient Data for initiating Remittance');
        }
        $totalAmount = $params['amount'] + $params['fee_amt'] + $params['service_tax'];
        $params['total_amount'] = $totalAmount;

        $bankUnicodeArr = Util::bankUnicodesArray();
        if (!isset($params['bank_unicode']) || $params['bank_unicode'] == '') {
            $params['bank_unicode'] = $bankUnicodeArr['1']; // boi
        }
        switch ($params['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $msg = $this->initiateKotakTxnRemit($params);
                break;
            case $bankUnicodeArr['2']:
                $msg = $this->initiateRatnakarTxnRemit($params);
                break;
            case $bankUnicodeArr['1']:
//            default:
                $msg = $this->initiateBoiTxnRemit($params);
                break;
        }
        error_log('Inside initiateRemit: remit txn id: ');
        error_log($params['remit_request_id']);
        
        $commission = $this->creditCommission($params);

        return $msg;
    }
    public function creditCommission($params){
    	$commissionObject = new CommissionReport();
    	$curdate = date("Y-m-d");
    	$param=array();
    	$param['agent_id'] = $params['agent_id'];
    	$param['from'] = $curdate;
    	$param['to'] = $curdate;
    	$param['txn_id'] = $params['remit_request_id'];
    	$param['remitter_id'] = $params['remitter_id'];
    	$param['remit_request_id'] = $params['remit_request_id'];
    	$param['bank_unicode'] = $params['bank_unicode'];
    	 
    	$commissionArray = array();
        if ($params['fee_amt'] > 0)
        {
            $commissionArray = $commissionObject->calculateCommission($param);
        }
        else
            {
            $commissionArray[0]['comm_amount'] = 0;
            $commissionArray[0]['product_id'] = $params['product_id'];
            }
        
    	
    
    	$param['remarks'] = 'crediting commission to agent';
    
    	try{
    		error_log("Commission is: ");
    		error_log($commissionArray[0]['comm_amount']);
    		$txnRecord = new BaseTxn();
    		$param['amount'] = $commissionArray[0]['comm_amount'];
    		$param['product_id'] = $commissionArray[0]['product_id'];
    		$param['txn_status'] = STATUS_SUCCESS;
    		$param['txn_type'] = 'COMM';
    	}catch (Exception $e) {
    		error_log("Error in agent commission ");
    	}
    
    	if(isset($param['amount'])){
    		$txnRecord->creditCommissionForAgent($param);
    	}
    }
    
    /*
     * $params['remit_request_id'] = remittance request id
     * $params['beneficiary_id'] = beneficiary id
     * $params['product_id'] = product id
     * $params['amount'] = amount
     * $params['fee_amt'] = fee amount (for kotak)
     * $params['service_tax'] = service tax amount (for kotak)
     * $params['agent_id'] = agent id (for kotak)
     * $params['txn_code'] = txn_code
     * $params['bank_unicode'] = bank unicode
     */

    public function remitSuccess($params) {
        if ($params['remit_request_id'] == '' || $params['txn_code'] == '' || $params['beneficiary_id'] == '' || $params['product_id'] == '' || $params['amount'] == '') {
            throw new Exception('Insufficient Data for successful remittance');
        }

        $bankUnicodeArr = Util::bankUnicodesArray();
        if (!isset($params['bank_unicode']) || $params['bank_unicode'] == '') {
            $params['bank_unicode'] = $bankUnicodeArr['1']; // boi
        }
        switch ($params['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $totalAmount = $params['amount'] + $params['fee_amt'] + $params['service_tax'];
                $params['total_amount'] = $totalAmount;
                $msg = $this->remitKotakTxnSuccess($params);
                break;
            case $bankUnicodeArr['2']:
                $msg = $this->remitRatnakarTxnSuccess($params);
                break;
            case $bankUnicodeArr['1']:
//            default:
                $msg = $this->remitBoiTxnSuccess($params);
                break;
        }
        return $msg;
    }

    
    public function remitSuccessToFailure($params) {
        if ($params['remit_request_id'] == '' || $params['txn_code'] == '' || $params['beneficiary_id'] == '' || $params['product_id'] == '' || $params['amount'] == '') {
            throw new Exception('Insufficient Data for successful remittance');
        }

        $bankUnicodeArr = Util::bankUnicodesArray();
        if (!isset($params['bank_unicode']) || $params['bank_unicode'] == '') {
            $params['bank_unicode'] = $bankUnicodeArr['2']; // Rat
        }
        switch ($params['bank_unicode']) {
            case $bankUnicodeArr['2']:
                $msg = $this->remitRatnakarTxnSuccessToFailure($params);
                break;
           
        }
        return $msg;
    }
    /*
     * $params['remit_request_id'] = remittance request id 
     * $params['product_id'] = product id 
     * $params['amount'] = amount 
     * $params['reversal_fee_amt'] = reversal fee amount 
     * $params['reversal_service_tax'] = reversal service tax 
     * $params['bank_unicode'] = bank unicode
     */

    public function remitFailure($params) {
        if ($params['remit_request_id'] == '' || $params['product_id'] == '' || $params['amount'] == '') {
            throw new Exception('Insufficient Data for failed remittance');
        }

        $bankUnicodeArr = Util::bankUnicodesArray();
        if (!isset($params['bank_unicode']) || $params['bank_unicode'] == '') {
            $params['bank_unicode'] = $bankUnicodeArr['1']; // boi
        }
        switch ($params['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $totalAmount = $params['amount'] + $params['reversal_fee_amt'] + $params['reversal_service_tax'];
                $params['total_amount'] = $totalAmount;
                $msg = $this->remitKotakTxnFailure($params);
                break;
            case $bankUnicodeArr['2']:
//            default:
                $msg = $this->remitRatnakarTxnFailure($params);
                break;
            case $bankUnicodeArr['1']:
//            default:
                $msg = $this->remitBoiTxnFailure($params);
                break;
        }
        return $msg;
    }

    /*
     * $params['remit_request_id'] = remittance request id
     * $params['remitter_id'] = remitter id
     * $params['agent_id'] = agent id
     * $params['product_id'] = product id
     * $params['amount'] = amount
     * $params['fee_amt'] = fee amount
     * $params['service_tax'] = service tax amount
     * $params['reversal_fee_amt'] = reversal fee amount
     * $params['reversal_service_tax'] = reversal service tax amount
     * $params['bank_unicode'] = bank unicode
     */
    public function remitRefund($params) {
        if ($params['remit_request_id'] == '' || $params['remitter_id'] == '' || $params['agent_id'] == '' || $params['product_id'] == '' || $params['amount'] == '') {
	    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_REMIT_REFUND_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_REMIT_REFUND_CODE);
        }
//        $totalAmount = $params['amount'] + $params['fee_amt'] + $params['service_tax'];
//        $params['total_amount'] = $totalAmount;
        $totalAmount = $params['amount'] - $params['reversal_fee_amt'] - $params['reversal_service_tax'];
        $params['total_amount'] = $totalAmount;


        $bankUnicodeArr = Util::bankUnicodesArray();
        if (!isset($params['bank_unicode']) || $params['bank_unicode'] == '') {
            $params['bank_unicode'] = $bankUnicodeArr['1']; // boi
        }
        switch ($params['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $msg = $this->remitKotakTxnRefund($params);
                break;
            case $bankUnicodeArr['2']:
                $msg = $this->remitRatnakarTxnRefund($params);
                break;
            case $bankUnicodeArr['1']:
//            default:
                $msg = $this->remitBoiTxnRefund($params);
                break;
        }

        error_log('Inside remitRefund: Refund txn Id');
        error_log($params['remit_request_id']);
        if($params['remit_request_id'] > 0){
        	$commission = $this->debitCommission($params);
        }

        return $msg;
    }

    private function getReversalCommDetails($params){


      $select = $this->_db->select()
                ->from(DbTable::TABLE_TXN_AGENT, array('amount','product_id'));

      $select->where("agent_id = ?", $params['agent_id']);
      $select->where("txn_type = ?", TXNTYPE_AGENT_COMMISSION);

                $bankUnicodeArr = Util::bankUnicodesArray();

	switch ($params['bank_unicode']) {
                	case $bankUnicodeArr['3']:
                		$select->where("kotak_remittance_request_id = ?", $params['remit_request_id']);

                		break;
                	case $bankUnicodeArr['2']:
                		$select->where("ratnakar_remittance_request_id = ?", $params['remit_request_id']);
		        App_Logger::log(" RAT ID :", Zend_Log::INFO);

                		break;
                	case $bankUnicodeArr['1']:

                		$select->where("remittance_request_id = ?", $params['remit_request_id']);
                		break;
            }
                
		$row = $this->_db->fetchRow($select);
        return $row;
    }


    public function debitCommission($params){

        App_Logger::log("Inside debitCommission params :" .implode($params), Zend_Log::INFO);

    	$commissionObject = new CommissionReport();
    	$curdate = date("Y-m-d");
    	$param=array();
    	
    	if(isset($params['original_agent_id'])){
   		$param['agent_id'] =  $params['original_agent_id'];
   	}else{
    		$param['agent_id'] =  $params['agent_id'];
   	}

    	$param['from'] = $curdate;
    	$param['to'] = $curdate;
    	$param['refund_txn_id'] = $params['remit_request_id'];
    	$param['remitter_id'] = $params['remitter_id'];
    	$param['remit_request_id'] = $params['remit_request_id'];
    	$param['bank_unicode'] = $params['bank_unicode'];
    	 
    
    	$param['remarks'] = 'debiting commission from agent for refund';
   

	try{
    		$rcomDetails = $this->getReversalCommDetails($param);

            App_Logger::log("Inside debitCommission rcomDetails :". implode($rcomDetails), Zend_Log::INFO);

    		if(isset($rcomDetails['amount']) && $rcomDetails['amount'] > 0){
    			error_log("Commission is: ");
    			error_log($rcomDetails['amount']);
    			$txnRecord = new BaseTxn();
    			$param['amount'] = $rcomDetails['amount'];
    			$param['product_id'] = $rcomDetails['product_id'];
    			$param['txn_status'] = STATUS_SUCCESS;
    			$param['txn_type'] = TXNTYPE_AGENT_COMMISSION_REVERSAL;
    			$txnRecord->debitCommissionFromAgent($param);
    		}
    	}catch (Exception $e) {
    		error_log("Error in agent commission reversal");
    	}


 
    }
    /* 
     * Failed ECS Load for Medi Assist
     * $params['insurance_claim_id'] = insurance claim id
     * $params['txn_code'] = txn code
     * $params['amount'] = amount
     */
    public function failureRatMediAssistCardLoad($params)
    {
        if($params['insurance_claim_id'] == '' || $params['txn_code'] == '' || $params['amount'] == '') {
                throw new Exception ('Insufficient Data');
        }
        
        return $this->failureTxnRatMediAssistCardLoad($params);
    }
    
    /* 
     * Cut-Off ECS Load for Medi Assist
     * $params['insurance_claim_id'] = insurance claim id
     * $params['txn_code'] = txn code
     * $params['amount'] = amount
     * 
     * Right now, same as failure
     */
    public function cutoffRatMediAssistCardLoad($params)
    {
        if($params['insurance_claim_id'] == '' || $params['txn_code'] == '' || $params['amount'] == '') {
                throw new Exception ('Insufficient Data');
        }
        
        return $this->failureTxnRatMediAssistCardLoad($params);
    }

    
    
    
    /*
     * func to generate txn code
     */
    public function generateTxncode() {
        $txncode = new Txncode();
        if($txncode->generateTxncode()) 
        {
            $paramsTxnCode = $txncode->getTxncode();//Get Txncode
            $txncode->setUsedStatus();//Mark Txncode as used
            return $paramsTxnCode;
        }
        return FALSE;
    }
    
    /*
     * $params['load_request_id'] = load_request_id
     * $params['customer_master_id'] = customer master id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount
     * $params['product_id'] = product_id
     */
    public function chkAllowRatCorporateCardLoad($params) {
        if ($params['load_request_id'] == '' || $params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' ){
            throw new App_Exception('Insufficient Data for validating Corporate CardLoad');
        }

        $bankRatnakar = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $params['bank_unicode'] = $bankRatnakar->bank->unicode;
        
        $validator = new Validator_Mediassist();
        $msg = $validator->chkAllowRatCorporateCardLoad($params);
        return $msg;
    }
    
    public function chkAllowRatCrAdj($params) {
        if ($params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' ){
            throw new App_Exception('Insufficient Data for validating Cr. Adj');
        }

        $validator = new Validator_Mediassist();
        $msg = $validator->chkAllowRatCrAdj($params);
        return $msg;
    }
    
    /* 
     * $params['txn_code'] = txn code
     * $params['customer_master_id'] = customer master id
     * $params['product_id'] = product id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount     
     * $params['txn_type'] = txn_type     
     */
    public function successRatCorporateCardLoad($params)
    {
        if($params['txn_code'] == '' || $params['customer_master_id'] == '' || 
                $params['product_id'] == '' || $params['purse_master_id'] == '' || 
                $params['customer_purse_id'] == '' || $params['amount'] == '' ) {
                throw new App_Exception ('Insufficient Data for loading corporate wallet');
        }
        
        return $this->successTxnRatCorporateCardLoad($params);
    }
    
    public function successRatManualAdj($params)
    {
        if($params['txn_code'] == '' || $params['customer_master_id'] == '' || 
                $params['product_id'] == '' || $params['purse_master_id'] == '' || 
                $params['customer_purse_id'] == '' || $params['amount'] == '' ) {
                throw new App_Exception ('Insufficient Data for loading corporate wallet');
        }
        
        return $this->successTxnRatManualAdj($params);
    }
    
    /*
     * $params['load_request_id'] = load_request_id
     * $params['customer_master_id'] = customer master id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount
     * $params['agent_id'] =>  MEDIASSIST_AGENT_ID,
     * $params['product_id'] =>  product_id,
     */
    public function chkAllowRatMediAssistCardLoad($params) {
        
        if ($params['load_request_id'] == '' || $params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' 
             || $params['agent_id'] == '' || $params['product_id'] == '' ){
            throw new App_Exception('Insufficient Data for validating CardLoad');
        }

        $bankRatnakar = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $params['bank_unicode'] = $bankRatnakar->bank->unicode;
        
        $validator = new Validator_Mediassist();
        $msg = $validator->chkAllowRatMediAssistCardLoad($params);
        
        $validator = new Validator_CustomerLimitValidator();
        $msg = $validator->chkAllowLoad($params);
        return $msg;
    }

    
    /*
     * $params['load_request_id'] = load_request_id
     * $params['customer_master_id'] = customer master id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount
     * $params['agent_id'] =>  MEDIASSIST_AGENT_ID,
     * $params['product_id'] =>  product_id,
     */
    public function chkAllowRatMediAssistCardDebit($params) {
        if ($params['load_request_id'] == '' || $params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' 
             || $params['agent_id'] == '' || $params['product_id'] == '' ){
            throw new App_Exception('Insufficient Data for validating CardDebit');
        }

        $purseValidator = new Validator_PurseParameterValidator();
        $result = $purseValidator->getPurseParameters($params['purse_master_id']);
        
        if($result['allow_debit'] == FLAG_NO) {
            throw new App_Exception('CardDebit is not allowed for this Product');
        }
        
        $bankRatnakar = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $params['bank_unicode'] = $bankRatnakar->bank->unicode;
        $validator = new Validator_Mediassist();
        $msg = $validator->chkAllowRatMediAssistCardDebit($params);
        return $msg;
    }
    
    /*
     * $params['load_request_id'] = load_request_id
     * $params['customer_master_id'] = customer master id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount
     * $params['agent_id'] =>  MEDIASSIST_AGENT_ID,
     * $params['product_id'] =>  product_id,
     */
    public function chkAllowKotakGPRCardDebit($params) {
        if ($params['load_request_id'] == '' || $params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' 
             || $params['agent_id'] == '' || $params['product_id'] == '' ){
            throw new App_Exception('Insufficient Data for validating CardDebit');
        }

        $bankKotak = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        $params['bank_unicode'] = $bankKotak->bank->unicode;
        $validator =  new Validator_Kotakgpr();
        $msg = $validator->chkAllowKotakGPRCardDebit($params);
        return $msg;
    }
    
        
    /* 
     * $params['txn_code'] = txn code
     * $params['customer_master_id'] = customer master id
     * $params['product_id'] = product id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount   
     * $params['agent_id'] =>  MEDIASSIST_AGENT_ID
     * $params['txn_type'] =>  txn_type
     */
    public function successRatMediAssistCardLoad($params)
    {
        if($params['txn_code'] == '' || $params['customer_master_id'] == '' || 
                $params['product_id'] == '' || $params['purse_master_id'] == '' || 
                $params['customer_purse_id'] == '' || $params['amount'] == ''  || $params['agent_id'] == '') {
                throw new App_Exception ('Insufficient Data for loading wallet');
        }
        
        return $this->successTxnRatMediAssistCardLoad($params);
    }
    
    /* 
     * $params['txn_code'] = txn code
     * $params['customer_master_id'] = customer master id
     * $params['product_id'] = product id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount   
     * $params['agent_id'] =>  agent id
     */
    public function successRatCardLoad($params)
    {
        if($params['txn_code'] == '' || $params['customer_master_id'] == '' || 
                $params['product_id'] == '' || $params['purse_master_id'] == '' || 
                $params['customer_purse_id'] == '' || $params['amount'] == ''  || $params['agent_id'] == '') {
                throw new App_Exception (ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_LOAD_WALLET_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_LOAD_WALLET_CODE);
        }
        
        return $this->successTxnRatCardLoad($params);
    }
    
    /* 
     * $params['txn_code'] = txn code
     * $params['customer_master_id'] = customer master id
     * $params['product_id'] = product id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount   
     * $params['agent_id'] =>  MEDIASSIST_AGENT_ID
     */
    public function successRatMediAssistCardDebit($params)
    {
        if($params['txn_code'] == '' || $params['customer_master_id'] == '' || 
                $params['product_id'] == '' || $params['purse_master_id'] == '' || 
                $params['customer_purse_id'] == '' || $params['amount'] == ''  || $params['agent_id'] == '') {
                throw new App_Exception (ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_LOAD_WALLET_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_LOAD_WALLET_CODE);
        }
        
        return $this->successTxnRatMediAssistCardDebit($params);
    }
    
     /* 
     * $params['txn_code'] = txn code
     * $params['customer_master_id'] = customer master id
     * $params['product_id'] = product id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount   
     * $params['agent_id'] =>  KOTAK_GPR_AGENT_ID
     */
    public function successKotakGPRCardDebit($params)
    {
        if($params['txn_code'] == '' || $params['customer_master_id'] == '' || 
                $params['product_id'] == '' || $params['purse_master_id'] == '' || 
                $params['customer_purse_id'] == '' || $params['amount'] == ''  || $params['agent_id'] == '') {
                throw new App_Exception ('Insufficient Data for loading wallet');
        }
        
        return $this->successTxnKotakGPRCardDebit($params);
    }
    
     /* 
     * $params['txn_code'] = txn code
     * $params['customer_master_id'] = customer master id
     * $params['product_id'] = product id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount   
     * $params['agent_id'] =>  agent id
     */
    public function successKotakCardLoad($params)
    {
        if($params['txn_code'] == '' || $params['customer_master_id'] == '' || 
                $params['product_id'] == '' || $params['purse_master_id'] == '' || 
                $params['customer_purse_id'] == '' || $params['amount'] == ''  || $params['agent_id'] == '') {
                throw new App_Exception ('Insufficient Data for loading wallet');
        }
        
        return $this->successTxnKotakCardLoad($params);
    }
    
   
    /* 
     * $params['txn_code'] = txn code
     * $params['customer_master_id'] = customer master id
     * $params['product_id'] = product id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount   
     * $params['agent_id'] =>  MEDIASSIST_AGENT_ID
     * $params['txn_type'] =>  txn_type
     */
    public function cutoffRatCardLoad($params)
    {
        if($params['txn_code'] == '' || $params['customer_master_id'] == '' || 
                $params['product_id'] == '' || $params['purse_master_id'] == '' || 
                $params['customer_purse_id'] == '' || $params['amount'] == ''  || $params['txn_type'] == '' ) {
                throw new App_Exception ('Insufficient Data for Loads cut-off');
        }
        
        return $this->reversalTxnRatCardLoad($params);
    }
    
    /*
     * $params['customer_master_id'] = customer master id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount
     * $params['product_id'] =>  product_id
     * $params['bank_unicode'] =>  bank unicode
     */
    public function chkAllowCardAuthAdvice($params) {
        if ($params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' 
             || $params['product_id'] == '' ){
            return array(
                'status' => STATUS_FAILED,
                'failed_reason' => ErrorCodes::ERROR_INSUFFICIENT_DATA_AUTHENTICATION_MSG,
                'failed_reason_code' => ErrorCodes::ERROR_INSUFFICIENT_DATA_FOR_PROCESSING
                    );
        }
        try {
            $validator = new Validator_AuthRequest();
            $msg = $validator->chkAllowCardAuthAdvice($params);
            if($msg == TRUE)
            {
                $validator = new Validator_CustomerLimitValidator();
                $msg = $validator->chkAllowAuth($params);
                if($msg == TRUE)
                {
                    return array(
                        'status' => STATUS_SUCCESS,
                            );
                }
                else
                {
                    return array(
                    'status' => STATUS_FAILED,
                    'failed_reason' => $validator->getMessage(),
                    'failed_reason_code' => $validator->getMessageCode()
                        );
                }
            }
            else
            {
                return array(
                'status' => STATUS_FAILED,
                'failed_reason' => $validator->getMessage(),
                'failed_reason_code' => $validator->getMessageCode()
                    );
            }
        }catch (App_Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            return array(
                'status' => STATUS_FAILED,
                'failed_reason' => $e->getMessage(),
                'failed_reason_code' => $e->getCode()
                    );
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            return array(
                'status' => STATUS_FAILED,
                'failed_reason' => $e->getMessage(),
                'failed_reason_code' => $e->getCode()                    
                    );
        }
    }
    
    
    /* 
        'txn_code' 
        'customer_master_id' 
        'txn_customer_master_id'
        'purse_master_id'
        'customer_purse_id'
        'amount' 
        'product_id'
        'txn_type' 

     * 'bank_unicode'
     */
    public function cardTransaction($params)
    {
        if($params['txn_code'] == '' || $params['customer_master_id'] == '' || $params['purse_master_id'] == '' ||   
                $params['customer_purse_id'] == '' || $params['amount'] == ''  || $params['product_id'] == '') {
                throw new App_Exception ('Insufficient Data for txn processing wallet');
        }
        
        $bankUnicodeArr = Util::bankUnicodesArray();
        if (!isset($params['bank_unicode']) || $params['bank_unicode'] == '') {
            $params['bank_unicode'] = $bankUnicodeArr['2']; // rat
        }
        switch ($params['bank_unicode']) {
            case $bankUnicodeArr['2']:
                $msg = $this->successTxnRatMediAssistCardTxn($params);
                break;
            case $bankUnicodeArr['1']:
//            default:
                $msg = $this->successTxnBoiNsdcCardTxn($params);
                break;
        }
        return $msg;
        
    }
    
    /* 
        'txn_code' 
        'customer_master_id' 
        'txn_customer_master_id'
        'purse_master_id'
        'customer_purse_id'
        'amount' 
        'product_id'
        'txn_type' 
     */
    public function reversalCardTransaction($params)
    {
        if($params['txn_code'] == '' || $params['customer_master_id'] == '' || $params['purse_master_id'] == '' ||   
                $params['customer_purse_id'] == '' || $params['amount'] == ''  || $params['product_id'] == '') {
                throw new App_Exception (ErrorCodes::ERROR_INSUFFICIENT_DATA_AUTHENTICATION_MSG,  ErrorCodes::ERROR_INSUFFICIENT_DATA_FOR_PROCESSING);
        }
        
        $bankUnicodeArr = Util::bankUnicodesArray();
        if (!isset($params['bank_unicode']) || $params['bank_unicode'] == '') {
            $params['bank_unicode'] = $bankUnicodeArr['2']; // rat
        }
        switch ($params['bank_unicode']) {
            case $bankUnicodeArr['2']:
                $msg = $this->successTxnRatMediAssistCardTxn($params);
                break;
            case $bankUnicodeArr['1']:
//            default:
                $msg = $this->successTxnBoiNsdcCardTxn($params);
                break;
        }
        
        return $msg;
        
    }

    public function chkAllowDrAdj($params) {
        if ($params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' 
             || $params['product_id'] == '' ){
            return array(
                'status' => STATUS_FAILED,
                'failed_reason' => 'Insufficient Data for debit transaction wallet'
                    );
        }
        try {
            $validator = new Validator_AuthRequest();
            $msg = $validator->chkAllowDrAdj($params);
            if($msg == TRUE)
            {
                return array(
                    'status' => STATUS_SUCCESS,
                        );
            }
            else
            {
                return array(
                'status' => STATUS_FAILED,
                'failed_reason' => $validator->getMessage()
                    );
            }
        }catch (App_Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            return array(
                'status' => STATUS_FAILED,
                'failed_reason' => $e->getMessage()
                    );
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            return array(
                'status' => STATUS_FAILED,
                'failed_reason' => $e->getMessage()
                    );
        }
    }
    
    
    /*
     * $params['load_request_id'] = load_request_id
     * $params['customer_master_id'] = customer master id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount
     * $params['corporate_id'] = corporate_id
     */
    public function chkAllowKotakCorporateCardLoad($params) {
        if ($params['load_request_id'] == '' || $params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' ){
            throw new App_Exception('Insufficient Data for validating Kotak Corporate CardLoad');
        }
       
        $bankKotak = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        $params['bank_unicode'] = $bankKotak->bank->unicode;
       
        if($params['corporate_id'] > 0){
            $validator = new Validator_Kotakgpr();
        } else {
            $validator = new Validator_Kotakamul();
        }
        $msg = $validator->chkAllowKotakCorporateCardLoad($params);
        return $msg;
    }
    
     /*
     * $params['load_request_id'] = load_request_id
     * $params['customer_master_id'] = customer master id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount
     * $params['agent_id'] =>  KotakGPR_AGENT_ID,
     * $params['product_id'] =>  product_id,
     */
    public function chkAllowKotakGPRCardLoad($params) {
        
        if ($params['load_request_id'] == '' || $params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' 
             || $params['agent_id'] == '' || $params['product_id'] == '' ){
            throw new App_Exception('Insufficient Data for validating CardLoad');
        }
       
        $bankKotak = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        $params['bank_unicode'] = $bankKotak->bank->unicode;
        
        $validator = new Validator_Kotakgpr();
        $msg = $validator->chkAllowKotakGPRCardLoad($params);
        
        $validator = new Validator_CustomerLimitValidator();
        $msg = $validator->chkAllowLoad($params);
      
        return $msg;
    }
    
    /* 
     * $params['txn_code'] = txn code
     * $params['customer_master_id'] = customer master id
     * $params['product_id'] = product id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount     
     * $params['corporate_id'] = corporate_id     
     */
    public function successKotakCorporateCardLoad($params)
    {
        if($params['txn_code'] == '' || $params['customer_master_id'] == '' || 
                $params['product_id'] == '' || $params['purse_master_id'] == '' || 
                $params['customer_purse_id'] == '' || $params['amount'] == '' ) {
                throw new App_Exception ('Insufficient Data for loading Kotak corporate wallet');
        }
        
        return $this->successTxnKotakCorporateCardLoad($params);
    }
    
    /*
     * $params['load_request_id'] = load_request_id
     * $params['customer_master_id'] = customer master id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount
     */
    public function chkAllowBoiCorporateCardLoad($params) {
        if ($params['load_request_id'] == '' || $params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' ){
            throw new App_Exception('Insufficient Data for validating Corporate CardLoad');
        }

        $bankRatnakar = App_DI_Definition_Bank::getInstance(BANK_BOI);
        $params['bank_unicode'] = $bankRatnakar->bank->unicode;
        $validator = new Validator_Boinsdc();
        $msg = $validator->chkAllowBoiCorporateCardLoad($params);
        return $msg;
    }
    
    /* 
     * $params['txn_code'] = txn code
     * $params['customer_master_id'] = customer master id
     * $params['product_id'] = product id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount     
     */
    public function successBoiCorporateCardLoad($params)
    {
        if($params['txn_code'] == '' || $params['customer_master_id'] == '' || 
                $params['product_id'] == '' || $params['purse_master_id'] == '' || 
                $params['customer_purse_id'] == '' || $params['amount'] == '' ) {
                throw new App_Exception ('Insufficient Data for loading corporate wallet');
        }
        
        return $this->successTxnBoiCorporateCardLoad($params);
    }
    
    
    /* 
     * $params['txn_code'] = txn code
     * $params['customer_master_id'] = customer master id
     * $params['product_id'] = product id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount   
     * $params['agent_id'] =>  MEDIASSIST_AGENT_ID
     * $params['txn_type'] =>  txn_type
     */
    public function cutoffBoiCardLoad($params)
    {
        if($params['txn_code'] == '' || $params['customer_master_id'] == '' || 
                $params['product_id'] == '' || $params['purse_master_id'] == '' || 
                $params['customer_purse_id'] == '' || $params['amount'] == ''  || $params['txn_type'] == '' ) {
                throw new App_Exception ('Insufficient Data for Medi-Assist Loads cut-off');
        }
        
        return $this->reversalTxnBoiCardLoad($params);
    }
    
     /*
     * $params['ops_id'] = ops id
     * $params['corporate_id'] = corporate id
     * $params['amount'] = amount
     * $params['txn_type'] = txn_type here TXNTYPE_AGENT_FUND_LOAD
     * $params['agent_fund_request_id'] = // fund request id optional
     * $params['agent_funding_id'] = // funding id 
     */

    public function opsToCorporate($params) {
        if ($params['ops_id'] == '' || $params['corporate_id'] == '' || $params['amount'] == ''){
            throw new Exception('Insufficient Data for fund load from ops to corporate');
        }
        $params['txn_status'] = FLAG_SUCCESS;
        $params['remarks'] = '';
        $this->insertTxnOpsToCorporate($params);

        return true;
    }
    
    /*
     * $params['agent_id'] = ops id
     * $params['txn_agent_id'] = agent id
     * $params['amount'] = amount
     * $params['txn_type'] = txn_type here TXNTYPE_AGENT_FUND_LOAD
     * $params['agent_fund_request_id'] = // fund request id optional
     * $params['agent_funding_id'] = // funding id 
     */

    public function corporateToCorporate($params) {
        if ($params['txn_corporate_id'] == '' || $params['corporate_id'] == '' || $params['amount'] == ''){
            throw new Exception('Insufficient Data for fund load from corporate to corporate');
        }
        $params['txn_status'] = FLAG_SUCCESS;
        $params['remarks'] = '';
        //$this->insertTxnOpsToAgent($params);
        $this->insertTxnCorporateToCorporate($params);

        return true;
    }
    
     public function successDoCorporateCardLoad($params)
    {
        if($params['txn_code'] == '' || $params['customer_master_id'] == '' || 
                $params['product_id'] == '' || $params['purse_master_id'] == '' || 
                $params['customer_purse_id'] == '' || $params['amount'] == '' || $params['corporate_id'] == '') {
                throw new App_Exception ('Insufficient Data for loading corporate wallet');
        }
        
        return $this->successTxnDoCorporateCardLoad($params);
    }
    public function successCorpKotakCorporateCardLoad($params)
    {
        if($params['txn_code'] == '' || $params['customer_master_id'] == '' || 
                $params['product_id'] == '' || $params['purse_master_id'] == '' || 
                $params['customer_purse_id'] == '' || $params['amount'] == '' || $params['corporate_id'] == '' ) {
                throw new App_Exception ('Insufficient Data for loading Kotak corporate wallet');
        }
        
        return $this->successTxnCorpKotakCorporateCardLoad($params);
    }
    
    
    /*
     * $params['customer_type'] = customer type
     * $params['mcc_code'] =>  mcc code
     */
    public function chkAllowMcc($params) {
        if(empty($params['customer_type'])){
            $params['customer_type'] = TYPE_NONKYC;
        }
        try {
            if($params['customer_type'] == TYPE_KYC) {
                 return array(
                    'status' => STATUS_SUCCESS,
                        );
            }
            else {
                 $config = App_DI_Container::get('ConfigObject');
                 $mccArr = $config->mcc_code->restrict->nonkyc->toArray();
                 if(in_array($params['mcc_code'], $mccArr)) {
                     App_Logger::log("MCC Code: ".$params['mcc_code']." is not allowed", Zend_Log::ALERT);
                     return array(
                        'status' => STATUS_FAILED,
                        'failed_reason' => "MCC Code: ".$params['mcc_code']." is not allowed",
                        'failed_reason_code' => ErrorCodes::ERROR_INVALID_MCC
                    );
                 }
                else {
                    return array(
                        'status' => STATUS_SUCCESS,
                            );
                }
                
            }
            
           
        }catch (App_Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            return array(
                'status' => STATUS_FAILED,
                'failed_reason' => $e->getMessage(),
                'failed_reason_code' => $e->getCode()
                    );
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            return array(
                'status' => STATUS_FAILED,
                'failed_reason' => $e->getMessage(),
                'failed_reason_code' => $e->getCode()                    
                    );
        }
    }
    
    
    public function initiateRemitAPI($params) {
        if ($params['agent_id'] == '' || $params['product_id'] == '' || $params['remitter_id'] == '' || $params['amount'] == '' || $params['remit_request_id'] == '') {
	    throw new Exception(ErrorCodes::ERROR_EDIGITAL_REMITTANCE_REQ_DATA_MISSING_MSG, ErrorCodes::ERROR_EDIGITAL_REMITTANCE_REQ_DATA_MISSING_CODE);
	    
	    //
        }

        $totalAmount = $params['amount'] + $params['fee_amt'] + $params['service_tax'];
        $params['total_amount'] = $totalAmount;

        $bankUnicodeArr = Util::bankUnicodesArray();
        
        switch ($params['bank_unicode']) {
            case $bankUnicodeArr['2']:
                $msg = $this->initiateRatnakarTxnRemitAPI($params);
                break;
        }
        return $msg;
    }

    public function chkAllowRemitAPI($params) {
        if ($params['agent_id'] == '' || $params['product_id'] == '' || $params['remitter_id'] == '' || $params['amount'] == '' || $params['rat_customer_id'] == '' || $params['purse_master_id'] == '') {
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_REMITTANCE_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_REMITTANCE_CODE);
        }

        $bankUnicodeArr = Util::bankUnicodesArray();
        switch ($params['bank_unicode']) {
            
            case $bankUnicodeArr['2']:
                $remitValidator = new Validator_Ratnakar_Remittance();
                $totalAmount = $params['amount'] + $params['fee_amt'] + $params['service_tax'];
                $params['amount'] = $totalAmount;
                $msg = $remitValidator->chkAllowRemit($params);
                break;
           
        }

        return $msg;
    }
 
    public function initiateWalletTransferAPI($params) {
        if ($params['purse_master_id'] == '' || $params['txn_purse_master_id'] == '' || $params['amount'] == '' || $params['customer_purse_id'] == '' || $params['txn_customer_purse_id'] == '' || $params['txn_code'] == '') { 
	    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_TO_VALIDATE_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_TO_VALIDATE_CODE);
        }

        $totalAmount = $params['amount'] + $params['fee_amt'] + $params['service_tax'];
        $params['total_amount'] = $totalAmount;
        $bankUnicodeArr = Util::bankUnicodesArray();
        
        switch ($params['bank_unicode']) {
            case $bankUnicodeArr['2']:
                $msg = $this->initiateRatnakarWalletTransferAPI($params);
                break;
        }
        return $msg;
    }
   
     public function chkAllowTransferAPI($params) {
        if ($params['product_id'] == '' || $params['rat_customer_id'] == '' || $params['txn_rat_customer_id'] == '' || $params['customer_purse_id'] == '' || $params['txn_customer_purse_id'] == '' || $params['amount'] == '' || $params['txn_purse_master_id'] == '') {
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_TO_VALIDATE_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_TO_VALIDATE_CODE);
        }

        $bankUnicodeArr = Util::bankUnicodesArray();
        switch ($params['bank_unicode']) {
            
            case $bankUnicodeArr['2']:
                $remitValidator = new Validator_Ratnakar_WalletTransfer();
                $totalAmount = $params['amount'] + $params['fee_amt'] + $params['service_tax'];
                $params['amount'] = $totalAmount;
                $msg = $remitValidator->chkAllowTransferAPI($params);
               
                break;
           
        }

        return $msg;
    }
    
    public function remitFailureAPI($params) {
      if ($params['customer_purse_id'] == '' || $params['purse_master_id'] == '' || $params['customer_master_id'] == '' || $params['remit_request_id'] == '' || $params['product_id'] == '' || $params['amount'] == '') { 
	  throw new Exception(ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_TO_FAIL_REMITT_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_TO_FAIL_REMITT_CODE);
        }

        $totalAmount = $params['amount'] + $params['fee_amt'] + $params['service_tax'];
        $params['total_amount'] = $totalAmount;

        $bankUnicodeArr = Util::bankUnicodesArray();
        
        switch ($params['bank_unicode']) {
            case $bankUnicodeArr['2']:
                $msg = $this->failureRatnakarTxnRemitAPI($params);
                break;
        }
        return $msg;
    }
    
     public function chkAllowBeneficiaryTransferAPI($params) {
         if ($params['product_id'] == '' || $params['rat_customer_id'] == '' || $params['customer_purse_id'] == '' || $params['amount'] == '' || $params['purse_master_id'] == '') {
	    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_TO_VALIDATE_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_TO_VALIDATE_CODE);
        }

        $bankUnicodeArr = Util::bankUnicodesArray();
        switch ($params['bank_unicode']) {
            
            case $bankUnicodeArr['2']:
                $remitValidator = new Validator_Ratnakar_WalletTransfer();
                $totalAmount = $params['amount'] + $params['fee_amt'] + $params['service_tax'];
                $params['amount'] = $totalAmount;
                $msg = $remitValidator->chkToBeneficiaryMaxBalAPI($params);

                $validator = new Validator_CustomerLimitValidator();
                $msg = $validator->chkAllowLoad($params);

                if((isset($params['bank_id']) && $params['bank_id'] > 0)){
                    $validator = new Validator_BankLimitValidator();
                    $msg = $validator->chkAllowLoad($params);
                }
                break;
           
        }

        return $msg;
    }
    

    public function getOpsToAgentTxnCode($params) {
        if ($params['ops_id'] == '' || $params['agent_id'] == '' || $params['amount'] == ''){
            throw new Exception('Insufficient Data for fund load from ops to agent');
        }
        $params['txn_status'] = FLAG_SUCCESS;
        $params['remarks'] = '';
        $txnCode = $this->insertTxnOpsToAgent($params, TRUE);
        return $txnCode;
    }
    
    /*
     * $params['agent_id'] = agent id
     * $params['product_id'] = product id
     * $params['cardholder_id'] = cardholder id
     * $params['amount'] = amount
     * $params['txn_type'] = txn_type
     */

    public function initiateAgentToRblMvcCardholder($params) {
        if ($params['agent_id'] == '' || $params['product_id'] == '' || $params['cardholder_id'] == '' || $params['amount'] == '' || $params['txn_type'] == ''){
            throw new Exception('Insufficient Data for initiating load/reload');
        }
        if ($params['txn_type'] == TXNTYPE_FIRST_LOAD) {
            $chkAllow = $this->chkAllowFirstLoad(array('agent_id' => $params['agent_id'], 'product_id' => $params['product_id'], 'amount' => $params['amount']));
        } else { ////if($params['txn_type'] == TXNTYPE_CARD_RELOAD)
            $chkAllow = $this->chkAllowReLoad(array('agent_id' => $params['agent_id'], 'product_id' => $params['product_id'], 'amount' => $params['amount']));
        }
        if ($chkAllow) {
            $params['txn_status'] = FLAG_PENDING;
            $params['remarks'] = '';
            $txnCode = $this->initiateTxnAgentToRblMvcCardholder($params);
            return array('flag' => true, 'txnCode' => $txnCode);
        }
        return array('flag' => false, 'txnCode' => '');
    }
    
    /*
     * $params['load_request_id'] = load_request_id
     * $params['customer_master_id'] = customer master id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount
     * $params['product_id'] = product_id
     */
    public function chkAllowRatMvcCardLoad($params) {
        if ($params['load_request_id'] == '' || $params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' ){
            throw new App_Exception('Insufficient Data for validating Load');
        }

        $bankRatnakar = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $params['bank_unicode'] = $bankRatnakar->bank->unicode;
        
        if((isset($params['bank_id']) && $params['bank_id'] > 0)){
            $validator = new Validator_BankLimitValidator();
            $msg = $validator->chkAllowLoad($params);
        }   
        
        $validator = new Validator_Mediassist();
        $msg = $validator->chkAllowRatCorporateCardLoad($params);
        return $msg;
    }
    
    public function chkAllowRatMediAssistCardLoadAPI($params) {
        
        if ($params['load_request_id'] == '' || $params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' 
             || $params['agent_id'] == '' || $params['product_id'] == '' ){
            throw new App_Exception(ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_CARDLOAD_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_CARDLOAD_CODE);
        }
        
        $isReversal = isset($params['isReversal']) ? $params['isReversal'] : '';

        $bankRatnakar = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $params['bank_unicode'] = $bankRatnakar->bank->unicode;
        
        $validator = new Validator_Mediassist();
        $msg = $validator->chkAllowRatMediAssistCardLoad($params);
        
        if($isReversal != REVERSAL_FLAG_YES){
            $validator = new Validator_CustomerLimitValidator();
            $msg = $validator->chkAllowLoad($params);

            if((isset($params['bank_id']) && $params['bank_id'] > 0)){
            $validator = new Validator_BankLimitValidator();
            $msg = $validator->chkAllowLoad($params);

            }
        }
        return $msg;
    }
    
    public function chkAllowRatLimitCardLoad($params) {
        
        if ($params['load_request_id'] == '' || $params['amount'] == '' 
             || $params['agent_id'] == '' || $params['product_id'] == '' ){
            throw new App_Exception(ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_CARDLOAD_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_CARDLOAD_CODE);
        }

        $bankRatnakar = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $params['bank_unicode'] = $bankRatnakar->bank->unicode;
        
        $validator = new Validator_Mediassist();
        $msg = $validator->chkAllowRatReversalCardLoadAPI($params);
        return $msg;
    }
    
     public function chkAllowRatCardLoadLimit($params) {
        
        if ($params['customer_master_id'] == '' || $params['purse_master_id'] == '' || $params['customer_purse_id'] == '' || $params['amount'] == '' || $params['agent_id'] == '' || $params['product_id'] == '') {
            throw new App_Exception('Insufficient Data for validating CardLoad');
        } 
        
        $bankRatnakar = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $params['bank_unicode'] = $bankRatnakar->bank->unicode;
        
        $validator = new Validator_Mediassist();
        $msg = $validator->chkAllowRatCardLoadbyAgent($params);
        
        $validator = new Validator_CustomerLimitValidator();
        $msg = $validator->chkAllowLoad($params);

        if((isset($params['bank_id']) && $params['bank_id'] > 0)){
            $validator = new Validator_BankLimitValidator();
            $msg = $validator->chkAllowLoad($params);
        }
        return $msg;
    }
    
    public function chkAllowRatMediAssistCardLoadByAgent($params) {
        
        if ($params['load_request_id'] == '' || $params['customer_master_id'] == '' || $params['purse_master_id'] == '' 
             || $params['customer_purse_id'] == '' || $params['amount'] == '' 
             || $params['agent_id'] == '' || $params['product_id'] == '' ){
            throw new App_Exception(ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_CARDLOAD_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_CARDLOAD_CODE);
        }
        
       
        $bankRatnakar = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $params['bank_unicode'] = $bankRatnakar->bank->unicode;
        
        $validator = new Validator_Mediassist();
        $msg = $validator->chkAllowRatMediAssistCardLoad($params);
        
            $validator = new Validator_CustomerLimitValidator();
            $msg = $validator->chkAllowLoad($params);

            if((isset($params['bank_id']) && $params['bank_id'] > 0)){
            $validator = new Validator_BankLimitValidator();
            $msg = $validator->chkAllowLoad($params);

            }
       
        return $msg;
    }
    
    
    public function successRatCardLoadByAgent($params)
    {
        if($params['txn_code'] == '' || $params['customer_master_id'] == '' || 
                $params['product_id'] == '' || $params['purse_master_id'] == '' || 
                $params['customer_purse_id'] == '' || $params['amount'] == ''  || $params['load_request_id'] == '' || $params['agent_id'] == '') {
                throw new App_Exception (ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_LOAD_WALLET_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_LOAD_WALLET_CODE);
        }
        $totalAmount = $params['amount'] + $params['fee_amt'] + $params['service_tax'];
        $params['total_amount'] = $totalAmount;
        $msg = $this->successTxnRatCardLoadByAgent($params);
        error_log('Inside initiateLoad: Load request id: ');
        error_log($params['load_request_id']);
        $commission = $this->creditLoadCommission($params);
        
        return $msg;

    }
    
    public function creditLoadCommission($params){
    	
        $feeAmount = (isset($params['fee_amt']) && $params['fee_amt'] > 0) ? $params['fee_amt'] : 0;
        if($feeAmount > 0){
            $bindModel = new BindAgentProductCommission();
            $txnRecord = new BaseTxn();

            $bindParam=array();
            $bindParam['agent_id'] = $params['agent_id'];
            $bindParam['product_id'] = $params['product_id'];
            $bindParam['typecode'] = TXNTYPE_CARD_RELOAD;
            $commArr = $bindModel->getAgentBindingCommition($bindParam); 
                if (!empty($commArr)) {
                    
                    
                    $loadFeeAmt = $feeAmount;
                    $commArr['amount'] = $loadFeeAmt;
                    $commAmount = Util::calculateFee($commArr);
                    $remarks = "Commission Amount: ".$commAmount.". Min Commission: ".$commArr['txn_min'].". Max Commission: ".$commArr['txn_max'];

                    $params['txn_type'] = TYPE_COMMISSION;
                    $params['amount'] = $commAmount;
                    $params['txn_status'] = STATUS_SUCCESS;
                    $params['remarks'] = $remarks;
                    $txnRecord->creditCommissionForAgent($params);

                 }

        }
        
    }
}
