<?php

/*
 * Ratnakar Remittance
 */

class Remit_Ratnakar_WalletTransfer extends Remit_Ratnakar {

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
    protected $_name = DbTable::TABLE_RATNAKAR_WALLET_TRANSFER;

  
    /*
     * chks remit limits
     */

    public function chkAllowTransferAPI($params) {
        $baseTxn = new BaseTxn();
        return $baseTxn->chkAllowTransferAPI($params);
    }

    /*
     * chks beneficiary limits
     */

    public function chkAllowBeneficiaryTransferAPI($params) {
        $baseTxn = new BaseTxn();
        return $baseTxn->chkAllowBeneficiaryTransferAPI($params);
    }
    
    /*
     * initiates remittance txns
     */

    public function initiateTransferAPI($params) {
        $baseTxn = new BaseTxn();
        return $baseTxn->initiateWalletTransferAPI($params);
    }

   public function walletTransfer($params) {
       $custPurseModel = new Corp_Ratnakar_CustomerPurse();
       $masterPurseModel = new MasterPurse();
       $flag = array();
       $flag['txn_code'] = '';
       $flag['response'] = FALSE;
       $ecsCall = FALSE;
       $res = 0;
       
       try{
       // From Wallet Details
       $pursecodeFrom = $params['rem_wallet_code'];
       $ratCustomerIdFrom = $params['remitter_id'];
        
       $masterPurseDetailsFrom = $masterPurseModel->getPurseIdByPurseCode($pursecodeFrom);
      
        if($ratCustomerIdFrom > 0) { 
            if(!empty($masterPurseDetailsFrom)){
           $purseDetailsFrom = $custPurseModel->getCustPurseDetails(array('rat_customer_id' => $ratCustomerIdFrom, 'purse_master_id' => $masterPurseDetailsFrom['id']));
           $customerPurseIdFrom = (isset($purseDetailsFrom['id']) && $purseDetailsFrom['id'] > 0) ? $purseDetailsFrom['id'] : 0;
           }else{
		    throw new Exception(ErrorCodes::ERROR_EDIGITAL_REMITTER_INVALID_WALLET_MSG, ErrorCodes::ERROR_EDIGITAL_REMITTER_INVALID_WALLET_CODE);
                }
           }
        
       // To wallet details
         $ratCustomerIdTo = $params['txn_remitter_id'];
         $pursecodeTo = $params['txn_wallet_code'];
          
         $masterPurseDetailsTo = $masterPurseModel->getPurseIdByPurseCode($pursecodeTo);
        if($ratCustomerIdTo > 0) { 
            if(!empty($masterPurseDetailsTo)){
           $purseDetailsTo = $custPurseModel->getCustPurseDetails(array('rat_customer_id' => $ratCustomerIdTo, 'purse_master_id' => $masterPurseDetailsTo['id']));
           $customerPurseIdTo = (isset($purseDetailsTo['id']) && $purseDetailsTo['id'] > 0) ? $purseDetailsTo['id'] : 0;
           }else{
		    throw new Exception(ErrorCodes::ERROR_EDIGITAL_BENE_WALLET_CODE_NOT_VALID_MSG, ErrorCodes::ERROR_EDIGITAL_BENE_WALLET_CODE_NOT_VALID_CODE);
		    
                }
           }
         

       $params['customer_purse_id'] = $customerPurseIdFrom;
       $params['txn_customer_purse_id'] = $customerPurseIdTo;

       $limitParams = array(
                    'product_id' => $params['product_id'],
                    'rat_customer_id' => $params['remitter_id'],
                    'txn_rat_customer_id' => $params['txn_remitter_id'],
                    'customer_purse_id' => $params['customer_purse_id'],
                    'txn_customer_purse_id' => $params['txn_customer_purse_id'],
                    'bank_unicode' => $params['bank_unicode'],
                    'fee_amt' => '0',
                    'service_tax' => '0',
                    'amount' => Util::convertToRupee($params['amount']),
                    'purse_master_id' => $masterPurseDetailsFrom['id'],
                    'txn_purse_master_id' => $masterPurseDetailsTo['id'],
                    'customer_master_id' => $params['customer_master_id'],
                    'txn_customer_master_id' => $params['txn_customer_master_id'],
                    'bank_id' => $params['bank_id'],
		    'claim_amount' => $params['claim_amount']
                );
               
         if ($this->chkAllowTransferAPI($limitParams)){
            $beneLimitParams = array(
                'product_id' => $params['product_id'],
                'rat_customer_id' => $params['txn_remitter_id'],
                'customer_purse_id' => $params['txn_customer_purse_id'],
                'bank_unicode' => $params['bank_unicode'],
                'fee_amt' => '0',
                'service_tax' => '0',
                'amount' => Util::convertToRupee($params['amount']),
                'purse_master_id' => $masterPurseDetailsTo['id'],
                'customer_master_id' => $params['txn_customer_master_id'],
                'bank_id' => $params['bank_id']
            );
            
            if($this->chkAllowBeneficiaryTransferAPI($beneLimitParams)) {
                 // Assign value
            $insertArr = array(
                        'txnrefnum' => $params['txnrefnum'],
                        'product_id' => $params['product_id'],
                        'txn_product_id' => $params['txn_product_id'],
                        'rat_customer_id' => $params['remitter_id'],
                        'txn_rat_customer_id' => $params['txn_remitter_id'],
                        'customer_purse_id' => $params['customer_purse_id'],
                        'txn_customer_purse_id' => $params['txn_customer_purse_id'],
                        'amount' => Util::convertToRupee($params['amount']),
                        'txn_type' => $params['txn_type'],
                        'narration' => $params['narration'],
                        'date_created' => date('Y-m-d H:i:s'),
                        'status' => STATUS_PENDING,
                        'by_agent_id' => $params['agent_id'],
                        'bank_id' => $params['bank_id']
                     );
                 $res = $this->save($insertArr);

                 if ($res > 0) {

                    $baseTxn = new BaseTxn();
                    $txnCode = $baseTxn->generateTxncode();
                    $flag['txn_code'] = $txnCode;
                            
                    // $initiateParams['product_id'] = $bank->bank->unicode;
                    //$initiateParams['bank_unicode'] = $params['product_id'];
                    $initiateParams['product_id'] = $params['product_id'];
                    $initiateParams['bank_unicode'] = $params['bank_unicode'];
                    $initiateParams['customer_master_id'] = $params['customer_master_id'];
                    $initiateParams['purse_master_id'] = $masterPurseDetailsFrom['id'];
                    $initiateParams['customer_purse_id'] = $params['customer_purse_id'];
                    $initiateParams['txn_customer_master_id'] = $params['txn_customer_master_id'];
                    $initiateParams['txn_purse_master_id'] = $masterPurseDetailsTo['id'];
                    $initiateParams['txn_customer_purse_id'] = $params['txn_customer_purse_id'];
                    $initiateParams['amount'] =  Util::convertToRupee($params['amount']);
                    $initiateParams['fee_amt'] = '0';
                    $initiateParams['service_tax'] = '0';
                    $initiateParams['txn_code'] = $txnCode;
                    $initiateParams['bank_id'] = $params['bank_id'];
                    
                    // ECS call starts
                    if(($masterPurseDetailsFrom['is_virtual'] == FLAG_NO && $params['card_number'] != '') || ($masterPurseDetailsTo['is_virtual'] == FLAG_NO && $params['txn_card_number'] != '') ) {
                        $amount = Util::convertToRupee($params['amount']);
                       
                        //ECS dr call
                        
                        /*
                         * checking amount sender card detail
                         */
                        $ecsSenderReq = TRUE;
                        $ecsSenderRes = FALSE;
                        $resTxnCode = 0;
                        if(($params['card_number'] == '') || is_null($params['card_number'])){
                            $ecsSenderReq = FALSE;
                           
                        }
                        
                        $updArr = array();
                        $updArr['txn_code'] = $txnCode;
                        if($ecsSenderReq){
                            if(DEBUG_MVC) {
                                $apiResp = TRUE;
                            } else {
                                $ecsCall = TRUE;
                                $cardDebitData = array(
                                    'amount' => $amount,
                                    'crn' => $params['card_number'],
                                    'agentId' => $params['agent_id'],
                                    'transactionId' => $txnCode,
                                    'currencyCode' => CURRENCY_INR_CODE,
                                    'countryCode' => COUNTRY_IN_CODE
                                );

                                $ecsApi = new App_Socket_ECS_Corp_Transaction();
                                $apiResp = $ecsApi->cardDebit($cardDebitData); // bypassing for testing
                                $updArr['date_ecs'] = new Zend_Db_Expr('NOW()');
                                $updArr['txn_debit_id'] = $ecsApi->getISOTxnId();
                            }
                            
                        } else {
                            $apiResp = TRUE;
                        }
                        if ($apiResp === TRUE){
                            $initiateParams['request_type'] = TXN_MODE_DR;
                            $resTxnCode = $this->initiateTransferAPI($initiateParams); // For From customer
                            $updArr['status'] = STATUS_IN_PROCESS;
                            $this->update($updArr, "id = $res");
                            $status = STATUS_IN_PROCESS;
                            $ecsSenderRes = TRUE;
                        } else {
                            $updArr['failed_reason'] = 'Debit failed: '.$ecsApi->getError();
                            $updArr['status'] = STATUS_FAILURE;
                            $this->update($updArr, "id = $res");
                            
                            $flag['response'] = FALSE;
                        }
                        
                        
                        if($status == STATUS_IN_PROCESS) {
                            
                        /*
                         * checking amount receiver card detail
                         */
                        $ecsReceiverReq = TRUE;
                        if(($params['txn_card_number'] == '') || is_null($params['txn_card_number'])){
                            $ecsReceiverReq = FALSE;
                          
                        }
                        $updArr = array();
                        $updArr['txn_code'] = $txnCode;
                        if($ecsReceiverReq){
                            
                             // ECS cr call
                            if(DEBUG_MVC) {
                                $apiResp = TRUE;
                            } else {
                                $cardLoadData = array(
                                'amount' => $amount,
                                'crn' => $params['txn_card_number'],
                                'agentId' => $params['agent_id'],
                                'transactionId' => $txnCode,
                                'currencyCode' => CURRENCY_INR_CODE,
                                'countryCode' => COUNTRY_IN_CODE
                                );
                                $ecsApi = new App_Socket_ECS_Corp_Transaction();
                                $apiResp = $ecsApi->cardLoad($cardLoadData);
                                $updArr['date_ecs'] = new Zend_Db_Expr('NOW()');
                                $updArr['txn_credit_id'] = $ecsApi->getISOTxnId();
                                 
                            }
                            
                        }else{
                            $apiResp = TRUE;
                        }
                          if($apiResp === TRUE) { 
                            $initiateParams['request_type'] = TXN_MODE_CR;
                            $this->initiateTransferAPI($initiateParams); // for receiver
                            $updArr['status'] = STATUS_SUCCESS;
                            $this->update($updArr ,"id = $res");
                            $flag['response'] = TRUE;
                            
                        } else {
                            if($ecsSenderRes){
                                $initiateParams['request_type'] = TXN_MODE_CR;
                                $this->initiateTransferRevertAPI($initiateParams);
                            }
                            $updArr['failed_reason'] = 'Credit failed: '.$ecsApi->getError();
                            $updArr['status'] = STATUS_FAILURE;
                            $this->update($updArr, "id = $res");
                            
                            $flag['response'] = FALSE;
                                
                        }
                        
                        } //  status in process
                        
                        
                        
//                        if ($apiResp === TRUE) { // ECS dr call success
////                            $txn_dr_load_id = $ecsCall == TRUE ? $ecsApi->getISOTxnId() : '';
////
////                            $updArr = array('txn_code' => $txnCode, 'date_ecs' => new Zend_Db_Expr('NOW()'), 'status' => STATUS_IN_PROCESS, 'txn_debit_id' => $txn_dr_load_id);
////                            $this->update($updArr, "id = $res");
////                            
////                            $initiateParams['request_type'] = TXN_MODE_DR;
////                            $this->initiateTransferAPI($initiateParams); // For From customer
////                            
//                            // ECS cr call
//                            $cardLoadData = array(
//                                'amount' => $amount,
//                                'crn' => $params['txn_card_number'],
//                                'agentId' => $params['agent_id'],
//                                'transactionId' => $txnCode,
//                                'currencyCode' => CURRENCY_INR_CODE,
//                                'countryCode' => COUNTRY_IN_CODE
//                                );
//                            
//                            if(DEBUG_MVC) {
//                                $apiResp = TRUE;
//                            } else {
//                                $ecsApi = new App_Socket_ECS_Corp_Transaction();
//                                $apiResp = $ecsApi->cardLoad($cardLoadData);
//                            }
//                            
//                            if ($apiResp === TRUE) { // ECS cr call success
//                                $txn_cr_load_id = $ecsCall == TRUE ? $ecsApi->getISOTxnId() : '';
//                                
//                                $initiateParams['request_type'] = TXN_MODE_CR;
//                                $this->initiateTransferAPI($initiateParams); // For To customer
//                                
//                                $updArr = array('status' => STATUS_SUCCESS, 'txn_credit_id' => $txn_cr_load_id);
//                                $this->update($updArr, "id = $res");
//                                
//                                $flag['response'] = TRUE;
//                                $flag['txn_code'] = $txnCode;
//                            }
//                        } else {
//                            $txn_debit_id = $ecsCall == TRUE ? $ecsApi->getISOTxnId() : '';
//                            $updArr = array('failed_reason' => 'Debit failed: '.$ecsApi->getError(), 'date_ecs' => new Zend_Db_Expr('NOW()'), 'status' => STATUS_FAILURE, 'txn_debit_id' => $txn_debit_id);
//                            $this->update($updArr, "id = $res");
//                            
//                            $flag['response'] = FALSE;
//                        }
                    } else {
                       
                        // Initiate Transfer
                        $txnCode = $this->initiateTransferAPI($initiateParams);
                        if ($txnCode) {
                            $flag['response'] = TRUE;
                            $updArr = array(
                                'status' => STATUS_SUCCESS,
                                'txn_code' => $txnCode
                            );
                            $flag['txn_code'] = $txnCode;
                            $this->update($updArr ,"id = $res");
                        }
                    }
                }
            }
        }
       return $flag;
     }
        catch (Exception $e) {

           // If any of the queries failed and threw an exception,
           // we want to roll back the whole transaction, reversing
           // changes made in the transaction, even those that succeeded.
           // Thus all changes are committed together, or none are.
	    App_Logger::log($e->getMessage(), Zend_Log::ERR); 
            $code = $e->getCode();
            if(empty($code)) {
                $code = ErrorCodes::WALLET_TRANSFER_FAILURE_CODE;
            }            
	    //$code = (empty($e->getCode())) ? ErrorCodes::WALLET_TRANSFER_FAILURE_CODE : $e->getCode();
	    $message = $e->getMessage();
	    $message = (empty($message)) ? ErrorCodes::WALLET_TRANSFER_FAILURE_MSG : $message; 
           throw new Exception($message,$code);
           return FALSE;
       }
    }

       public function getWalletTransferDetails($params) {
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $object = new Corp_Ratnakar_Cardholders();
        $remitterCustID = isset($params['remitter_cust_id']) ? $params['remitter_cust_id'] : '';
        $productID = isset($params['product_id']) ? $params['product_id'] : '';
        $txnRefNum = isset($params['txnrefnum']) ? $params['txnrefnum'] : '';
        $select = $this->_select()
                ->where("txn_code = '". $params['txn_code'] ."'");
        if ($productID != ''){
            $select->where("product_id = '" . $productID . "'");
        }
        
         if ($remitterCustID != ''){
            $select->where("rat_customer_id = '" . $remitterCustID . "'");
        }
        
        if ($txnRefNum != ''){
            $select->where("txnrefnum = '" . $txnRefNum . "'");
        }
        $data = $this->fetchRow($select);      
        if(!empty($data)){
        $remitterWalletCode = $custPurseModel->getWalletCode($data['customer_purse_id']);
        $beneWalletCode = $custPurseModel->getWalletCode($data['txn_customer_purse_id']);
        
        $toCustDetails = $object->getCardholderInfo(array('rat_customer_id' => $data['txn_rat_customer_id'] ,'product_id' => $params['product_id']));
        $dataArr = array(
            'remitter_wallet_code' => $remitterWalletCode['code'],
            'bene_email' => $toCustDetails['email'],
            'bene_mobile' => $toCustDetails['mobile'],
            'bene_wallet_code' => $beneWalletCode['code'],
            'amount' => $data['amount'],
            'status' => $data['status'],
        );
        return $dataArr;
        }else{
            return FALSE;
        }
    }
    
    public function getWalletLoadDetails($params) {
        $fromId = (isset($params['fromId']) && $params['fromId'] == TRUE ) ? $params['customer_purse_id'] : '';
        $toId = (isset($params['toId']) && $params['toId'] == TRUE) ? $params['customer_purse_id'] : '';
        $onDate = isset($params['on_date']) ? $params['on_date'] : FALSE;
        //Enable DB Slave
        $this->_enableDbSlave();
        $select = $this->select();
        $select->from($this->_name, array('sum(amount) as total_load_amount'))
                ->where('txn_type = ?', $params['txn_type'])      
                ->where('product_id = ?', $params['product_id']);
        /*if(isset($params['status'])) {
            $select->where('status = ?', $params['status']);
        }*/
        if($fromId != ''){
            $select->where('customer_purse_id = ?', $fromId);
            $select->where("status IN ('".STATUS_SUCCESS."', '".STATUS_IN_PROCESS."')");
        }
        if($toId != ''){
            $select->where('txn_customer_purse_id = ?', $toId);
            $select->where('status = ?', STATUS_SUCCESS);
        }
        if ($onDate) {
                    $date = isset($params['date']) ? $params['date'] : '';
                    $select->where('DATE(date_created) =?', $date);
                } 
        
        $row = $this->fetchRow($select);      
        //Disable DB Slave
        $this->_disableDbSlave();       
        return $row;
    }
    
    public function checkDuplicateTransNum($params) {
        $select = $this->select();
        $select->from($this->_name);
        $select->where('product_id = ?', $params['product_id']);
        $select->where('txnrefnum = ?', $params['txnrefnum']); 
        $rs = $this->fetchRow($select);
        if( !empty($rs) ){
            return array('id'=>$rs['id'], 'txncode'=>$rs['txn_code']);
        }
        else
            return FALSE;
   }
    
    public function getListWalletTranfer($params) {
	$productId = ($params['product_id'] != '') ? $params['product_id'] : '';
	$status = ($params['status'] != '') ? $params['status'] : '';
	$agentId = ($params['agent_id'] != '') ? $params['agent_id'] : '';
	
	$sql = $this->select();
        $sql->from($this->_name .' AS wt',array('id','product_id','bank_id','date_created','amount','status','txn_type','rat_customer_id','txn_rat_customer_id','txnrefnum','txn_code'));
	$sql->setIntegrityCheck(false);
	$sql->join(DbTable::TABLE_BANK . " as b", "b.id = wt.bank_id", array('b.name AS bank_name'));
	$sql->join(DbTable::TABLE_PRODUCTS . " as p", "p.id = wt.product_id", array('p.name AS product_name'));
	$sql->join(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as rc", "rc.rat_customer_id = wt.rat_customer_id", array('concat(rc.first_name," ",rc.last_name) as sender_name','rc.mobile AS sender_mobile'));
	$sql->join(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as trc", "trc.rat_customer_id = wt.txn_rat_customer_id", array('concat(trc.first_name," ",trc.last_name) as receiver_name','trc.mobile AS recieve_mobile'));
	$sql->join(DbTable::TABLE_AGENTS . " as a", "a.id = wt.by_agent_id", array('agent_code'));
	$sql->joinLeft(DbTable::TABLE_BLOCK_AMOUNT . " as ba", "ba.claim_txn_code = wt.txn_code", array('ba.amount AS block_amount'));	
	$sql->where("DATE(wt.date_created) BETWEEN '". $params['from'] ."' AND '". $params['to'] ."'");
	if ($productId != '') {
	    $sql->where("wt.product_id = ?", $productId);
	}
	if ($status != '') {
	    $sql->where("wt.status IN (?)", $status);
	}
	if ($agentId != '') {
	    $sql->where("wt.by_agent_id = ?", $agentId);
	} 
       return $this->fetchAll($sql);	
   }

 
   /*
    * creditIncompleteTransfer() will revert back the amount to the customer wallet
    */
   public function creditIncompleteTransfer() {
        $prevTime = date('Y-m-d H:i:s', strtotime('-30 min'));
        $count = 0;
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number"); 
        
        $select = $this->_db->select();
        $select->from($this->_name . " as wwt", array('id', 'product_id', 'txn_code', 'rat_customer_id', 'customer_purse_id', 'amount', 'txn_type', 'by_agent_id'));
        $select->join(DbTable::TABLE_RAT_CORP_CARDHOLDER . ' as rcc', "rcc.rat_customer_id = wwt.rat_customer_id", array($card_number));
        $select->join(DbTable::TABLE_RAT_CUSTOMER_PURSE . ' as rcp', "rcp.id = wwt.customer_purse_id", array());
        $select->where('wwt.status = ?', STATUS_IN_PROCESS);
        $select->where('wwt.date_created <= ?', $prevTime); 
        $rs = $this->_db->fetchAll($select);
        
        if(count($rs) > 0) {
            foreach($rs as $val) {
                $cardLoadData = array(
                            'amount' => $val['amount'],
                            'crn' => $val['card_number'],
                            'agentId' => $val['by_agent_id'],
                            'transactionId' => $val['txn_code'],
                            'currencyCode' => CURRENCY_INR_CODE,
                            'countryCode' => COUNTRY_IN_CODE
                            );

                if(DEBUG_MVC) {
                    $apiResp = TRUE;
                } else {
                    $ecsApi = new App_Socket_ECS_Corp_Transaction();
                    $apiResp = $ecsApi->cardLoad($cardLoadData);
                }
                
                if ($apiResp === TRUE) {
                    // Updating customer Balance
                    $updArr = array('amount' => new Zend_Db_Expr("amount + " . (string) $val['amount']));
                    $where = "id = '" . $val['customer_purse_id'] . "'";
                    $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);

                    // Updating rat_wallet_transfer table
                    $transferUpdateArr = array('status' => STATUS_FAILURE, 'date_reversal' => new Zend_Db_Expr('NOW()'));
                    $this->_db->update(DbTable::TABLE_RATNAKAR_WALLET_TRANSFER, $transferUpdateArr, "id = '" . $val['id'] . "'");
                    
                    // insert into log table                                                
                    $insertArr = array('wallet_transfer_id' => $val['id'], 'product_id' => $val['product_id'], 'txn_code' => $val['txn_code'], 'rat_customer_id' => $val['rat_customer_id'], 'customer_purse_id' => $val['customer_purse_id'], 'amount' => $val['amount'], 'txn_type' => $val['txn_type'], 'date_ecs' => new Zend_Db_Expr('NOW()'), 'status' => STATUS_SUCCESS);
                    $this->insertLog($insertArr);
                    
                    $count++;
                } else {
                    // insert into log table                                                
                    $insertArr = array('wallet_transfer_id' => $val['id'], 'product_id' => $val['product_id'], 'txn_code' => $val['txn_code'], 'rat_customer_id' => $val['rat_customer_id'], 'customer_purse_id' => $val['customer_purse_id'], 'amount' => $val['amount'], 'txn_type' => $val['txn_type'], 'failed_reason' => 'Credit failed: '.$ecsApi->getError(), 'date_ecs' => new Zend_Db_Expr('NOW()'), 'status' => STATUS_FAILED);
                    $this->insertLog($insertArr);                    
                }
            }
        }
        return $count;
    }
    
    public function insertLog($param) {
        try {
            return $this->_db->insert(DbTable::TABLE_WALLET_CREDIT_INFO, $param); 
        } catch(Exception $e ) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            return false;
        }
    }
    
    public function initiateTransferRevertAPI($params) {
        $baseTxn = new BaseTxn();
        return $baseTxn->initiateRatnakarWalletTransferRevertAPI($params);
    }

}