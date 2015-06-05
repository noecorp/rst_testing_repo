<?php

abstract class TxnRecord extends App_Model {

    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'agent_id';

    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_AGENT_BALANCE;
    private $_msg;

 public function debitCommissionFromAgent($params) {
        $this->_db->beginTransaction();

        try {
            $txncode = new Txncode();
            if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used

                $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']),
                    'date_modified' =>  new Zend_Db_Expr('NOW()'));
                $where = "agent_id = '" . $params['agent_id'] . "'";
                $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);

                $bankUnicodeArr = Util::bankUnicodesArray();
                
                switch ($params['bank_unicode']) {
                	case $bankUnicodeArr['3']:
                		$data['kotak_remitter_id'] = $params['remitter_id'];
                		$data['kotak_remittance_request_id'] = $params['remit_request_id'];
                		break;
                	case $bankUnicodeArr['2']:
                		$data['ratnakar_remitter_id'] = $params['remitter_id'];
                		$data['ratnakar_remittance_request_id'] = $params['remit_request_id'];
                		break;
                	case $bankUnicodeArr['1']:
                		$data['txn_remitter_id'] = $params['remitter_id'];
                		$data['remittance_request_id'] = $params['remit_request_id'];
                		break;
                }

                $data['txn_code'] = $paramsTxnCode;
                $data['ip'] = $this->formatIpAddress(Util::getIP());
                $data['currency'] = CURRENCY_INR;
                $data['amount'] = $params['amount'];
                $data['product_id'] = $params['product_id'];
                $data['txn_type'] = $params['txn_type'];
                $data['txn_status'] = $params['txn_status'];
                $data['remarks'] = $params['remarks'];
                $data['txn_ops_id'] = TXN_OPS_ID;
                $data['date_created'] = new Zend_Db_Expr('NOW()');

                $data['agent_id'] = $params['agent_id'];
                $data['mode'] = TXN_MODE_DR;
                $this->_db->insert(DbTable::TABLE_TXN_AGENT, $data);
                // OPS CR entry
                $data['mode'] = TXN_MODE_CR;
                $data['ops_id'] = TXN_OPS_ID;
                $data['txn_agent_id'] = $params['agent_id'];
                $this->insertTxnOps($data);
                
                $this->_db->commit();
                return true;
            }
            else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code could not be generated at this time. Please try later.", Zend_Log::ALERT);
                throw new Exception("Transaction Code could not be generated at this time. Please try later.");
            }
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    } 

    public function creditCommissionForAgent($params) {
        $this->_db->beginTransaction();
        
        try {
            $paramTxnCode = (isset($params['txn_code'])) ? $params['txn_code'] : '';
            $txncode = new Txncode();
            if($paramTxnCode !=''){
             $paramsTxnCode = $paramTxnCode;  
            }else if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used
            }else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code could not be generated at this time. Please try later.", Zend_Log::ALERT);
                throw new Exception("Transaction Code could not be generated at this time. Please try later.");
            }   
                $ageBal = array('amount' => $params['amount'], 'agent_id' => $params['agent_id']);
		$this->updateTxnAgentBalance($ageBal);

                $bankUnicodeArr = Util::bankUnicodesArray();
                switch ($params['bank_unicode']) {
                	case $bankUnicodeArr['3']:
                		$data['kotak_remitter_id'] = $params['remitter_id'];
                		$data['kotak_remittance_request_id'] = $params['remit_request_id'];
                		break;
                	case $bankUnicodeArr['2']:
                		$data['ratnakar_remitter_id'] = $params['remitter_id'];
                		$data['ratnakar_remittance_request_id'] = $params['remit_request_id'];
                		break;
                	case $bankUnicodeArr['1']:
                		$data['txn_remitter_id'] = $params['remitter_id'];
                		$data['remittance_request_id'] = $params['remit_request_id'];
                		break;
                }
                
                $data['txn_code'] = $paramsTxnCode;
                $data['ip'] = $this->formatIpAddress(Util::getIP());
                $data['currency'] = CURRENCY_INR;
                $data['amount'] = $params['amount'];
                $data['product_id'] = $params['product_id'];
                $data['txn_type'] = $params['txn_type'];
                $data['txn_status'] = $params['txn_status'];
                $data['remarks'] = $params['remarks'];
                $data['date_created'] = new Zend_Db_Expr('NOW()');
                $data['txn_ops_id'] = TXN_OPS_ID;
                $data['agent_id'] = $params['agent_id'];
                $data['load_request_id'] = (isset($params['load_request_id']) && $params['load_request_id'] > 0) ? $params['load_request_id'] : '0';
                $data['mode'] = TXN_MODE_CR;
                $this->_db->insert(DbTable::TABLE_TXN_AGENT, $data);
                 // OPS DR entry
                $data['mode'] = TXN_MODE_DR;
                $data['ops_id'] = TXN_OPS_ID;
                $data['txn_agent_id'] = $params['agent_id'];
                $this->insertTxnOps($data);
                
                $this->_db->commit();
                return true;
           // }
            
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }

    
    public function initiateTxnAgentToCardholder($params) {
        $this->_db->beginTransaction();

        try {
            $txncode = new Txncode();
            if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used

                $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']),
                    'block_amount' => new Zend_Db_Expr("block_amount + " . $params['amount']),
                    'date_modified' => new Zend_Db_Expr('NOW()'));
                $where = "agent_id = '" . $params['agent_id'] . "'";
                $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);


                $data['txn_code'] = $paramsTxnCode;
                $data['ip'] = $this->formatIpAddress(Util::getIP());
                $data['currency'] = CURRENCY_INR;
                $data['amount'] = $params['amount'];
                $data['product_id'] = $params['product_id'];
                $data['txn_type'] = $params['txn_type'];
                $data['txn_status'] = $params['txn_status'];
                $data['remarks'] = $params['remarks'];
                $data['date_created'] = new Zend_Db_Expr('NOW()');

                $data['agent_id'] = $params['agent_id'];
                $data['txn_cardholder_id'] = $params['cardholder_id'];
                $data['mode'] = TXN_MODE_DR;
                $this->_db->insert(DbTable::TABLE_TXN_AGENT, $data);

                unset($data['agent_id']);
                unset($data['txn_cardholder_id']);
                $data['txn_agent_id'] = $params['agent_id'];
                $data['cardholder_id'] = $params['cardholder_id'];
                $data['mode'] = TXN_MODE_CR;

                $this->_db->insert(DbTable::TABLE_TXN_CARDHOLDER, $data);
                $this->_db->commit();
            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code could not be generated at this time. Please try later.", Zend_Log::ALERT);
                throw new Exception("Transaction Code could not be generated at this time. Please try later.");
            }

            return $data['txn_code'];
        } catch (Exception $e) {
            // If any of the queries failed and threw an exception,
            // we want to roll back the whole transaction, reversing
            // changes made in the transaction, even those that succeeded.
            // Thus all changes are committed together, or none are.
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }

  
    public function completeTxnAgentToCardholder($params) {
        $this->_db->beginTransaction();

        try {
            if ($params['txn_status'] == FLAG_SUCCESS) {
                $updArr = array('block_amount' => new Zend_Db_Expr("block_amount - " . $params['amount']), 'date_modified' => new Zend_Db_Expr('NOW()'));
                $where = "agent_id = '" . $params['agent_id'] . "'";
                $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);
            } elseif ($params['txn_status'] == FLAG_FAILURE) {
                $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']),
                    'block_amount' => new Zend_Db_Expr("block_amount - " . $params['amount']),
                    'date_modified' => new Zend_Db_Expr('NOW()'));
                $where = "agent_id = '" . $params['agent_id'] . "'";
                $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);
            }

            $updTxnArr = array('txn_status' => $params['txn_status'], 'remarks' => $params['remarks']);
            $where = " txn_code = '" . $params['txn_code'] . "' 
                    AND agent_id = '" . $params['agent_id'] . "' 
                    AND txn_cardholder_id = '" . $params['cardholder_id'] . "'";
            $this->_db->update(DbTable::TABLE_TXN_AGENT, $updTxnArr, $where);

            $where = " txn_code = '" . $params['txn_code'] . "' 
                    AND txn_agent_id = '" . $params['agent_id'] . "' 
                    AND cardholder_id = '" . $params['cardholder_id'] . "'";
            $this->_db->update(DbTable::TABLE_TXN_CARDHOLDER, $updTxnArr, $where);

            $this->_db->commit();
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }

   
    public function insertTxnAgentToCardholder($params) {
        $this->_db->beginTransaction();

        try {
            $txncode = new Txncode();
            if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used

                $data['txn_code'] = $paramsTxnCode;
                $data['ip'] = $this->formatIpAddress(Util::getIP());
                $data['currency'] = CURRENCY_INR;
                $data['amount'] = $params['amount'];
                $data['product_id'] = $params['product_id'];
                $data['txn_type'] = $params['txn_type'];
                $data['txn_status'] = $params['txn_status'];
                $data['remarks'] = $params['remarks'];
                $data['date_created'] = new Zend_Db_Expr('NOW()');

                $data['agent_id'] = $params['agent_id'];
                $data['txn_cardholder_id'] = $params['cardholder_id'];
                $data['mode'] = TXN_MODE_DR;
                $this->_db->insert(DbTable::TABLE_TXN_AGENT, $data);

                unset($data['agent_id']);
                unset($data['txn_cardholder_id']);
                $data['txn_agent_id'] = $params['agent_id'];
                $data['cardholder_id'] = $params['cardholder_id'];
                $data['mode'] = TXN_MODE_CR;
                $this->_db->insert(DbTable::TABLE_TXN_CARDHOLDER, $data);
            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code could not be generated at this time. Please try later.", Zend_Log::ALERT);
                throw new Exception("Transaction Code could not be generated at this time. Please try later.");
            }
            $this->_db->commit();
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }

    public function insertTxnOpsToAgent($params) {
        $this->_db->beginTransaction();

        try {
            $txncode = new Txncode();
            if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used
                $ip = Util::getIP();
                $data['txn_code'] = $paramsTxnCode;
                $data['ip'] = empty($ip) ? '' : $this->formatIpAddress($ip);
                $data['currency'] = CURRENCY_INR;
                $data['amount'] = $params['amount'];
                $data['txn_type'] = $params['txn_type'];
                $data['txn_status'] = $params['txn_status'];
                $data['remarks'] = $params['remarks'];
                $data['agent_fund_request_id'] = (isset($params['agent_fund_request_id']) && $params['agent_fund_request_id'] > 0) ? $params['agent_fund_request_id'] : '0';
                $data['agent_funding_id'] = (isset($params['agent_funding_id']) && $params['agent_funding_id'] > 0) ? $params['agent_funding_id'] : '0';
                $data['date_created'] = new Zend_Db_Expr('NOW()');

                $data['ops_id'] = $params['ops_id'];
                $data['txn_agent_id'] = $params['agent_id'];
                $data['mode'] = TXN_MODE_DR;
                $this->_db->insert(DbTable::TABLE_TXN_OPS, $data);


                unset($data['ops_id']);
                unset($data['txn_agent_id']);
                $data['agent_id'] = $params['agent_id'];
                $data['txn_ops_id'] = $params['ops_id'];
                $data['mode'] = TXN_MODE_CR;
                $this->_db->insert(DbTable::TABLE_TXN_AGENT, $data);

                if ($params['txn_status'] == FLAG_SUCCESS) {
                    $ageBal = array('amount' => $params['amount'], 'agent_id' => $params['agent_id']);
                    $this->updateTxnAgentBalance($ageBal);
                }
            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code could not be generated at this time. Please try later.", Zend_Log::ALERT);
                throw new Exception("Transaction Code could not be generated at this time. Please try later.");
            }

            $this->_db->commit();
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }
    
    public function insertTxnOpsToAgentVirtual($params) {
        $this->_db->beginTransaction();
        try {
            $txncode = new Txncode();
            if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used 
                $ip = @$this->formatIpAddress(Util::getIP()); 
                    
                $data['txn_code'] = $paramsTxnCode; 
                $data['ip'] = (empty($ip))? '' : $ip; 
                $data['currency'] = CURRENCY_INR;
                $data['amount'] = $params['amount'];
                $data['txn_type'] = $params['txn_type'];
                $data['txn_status'] = $params['txn_status'];
                $data['remarks'] = $params['remarks']; 
                $data['agent_fund_request_id'] = (isset($params['agent_fund_request_id']) && $params['agent_fund_request_id'] > 0) ? $params['agent_fund_request_id'] : '0';
                $data['agent_funding_id'] = (isset($params['agent_funding_id']) && $params['agent_funding_id'] > 0) ? $params['agent_funding_id'] : '0';
                $data['date_created'] = new Zend_Db_Expr('NOW()'); 
                $data['ops_id'] = $params['ops_id'];
                $data['txn_agent_id'] = $params['agent_id'];
                $data['mode'] = TXN_MODE_DR;
                $data['is_virtual'] = FLAG_YES; 
                 
                
                $this->insertTxnOps($data);
                 
                 
               // $this->_db->insert(DbTable::TABLE_TXN_OPS, $data);
               
   
                unset($data['ops_id']);
                unset($data['txn_agent_id']);
                $data['agent_id'] = $params['agent_id'];
                $data['txn_ops_id'] = $params['ops_id'];
                $data['mode'] = TXN_MODE_CR;
                 
                $this->_db->insert(DbTable::TABLE_TXN_AGENT, $data);
                 
                if ($params['txn_status'] == FLAG_SUCCESS) {
                    $updArr = array(
                        'amount' => new Zend_Db_Expr("amount + " . $params['amount']), 
                        'date_modified' => new Zend_Db_Expr('NOW()')
                    ); 
                    $where = "agent_id = '" . $params['agent_id'] . "'";
                    $upd = $this->_db->update(DbTable::TABLE_AGENT_VIRTUAL_BALANCE, $updArr, $where); 
                    if (!$upd) {
                        $insArr['agent_id'] = $params['agent_id'];
                        $insArr['amount'] = $params['amount'];
                        $insArr['block_amount'] = 0;
                        $insArr['date_modified'] = new Zend_Db_Expr('NOW()'); 
                        $this->_db->insert(DbTable::TABLE_AGENT_VIRTUAL_BALANCE, $insArr); 
                    }
                } 
            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code could not be generated at this time. Please try later.", Zend_Log::ALERT);
                throw new Exception("Transaction Code could not be generated at this time. Please try later.");
            }
            $this->_db->commit();
            return $paramsTxnCode;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }

    /* 
     * for fee amount
     * for service tax amount
     */

    public function insertBoiTxnRemitterRegnFee($params) {
        $this->_db->beginTransaction();

        try {
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['remit_request_id'] = 0;
            $params['txn_status'] = FLAG_SUCCESS;

            /* Amount blocked in agent balance */
            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']),
                'block_amount' => new Zend_Db_Expr("block_amount + " . $params['amount']),
                'date_modified' => $params['date']);
            $where = "agent_id = '" . $params['agent_id'] . "'";
            $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);

            $txncode = new Txncode();
            if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used
            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code for remitter regn fees could not be generated at this time. Please try later.", Zend_Log::ALERT);
                throw new Exception("Transaction Code for remitter regn fees could not be generated at this time. Please try later.");
            }

            /* For Fee Amount */
            if ($params['fee_amt'] > 0) {
                $paramsFee['txn_code'] = $paramsTxnCode;
                $paramsFee['amount'] = $params['fee_amt'];
                $paramsFee['txn_type'] = TXNTYPE_REMITTER_REGISTRATION;

                /*                 * ******* Agent txn entry ****** */
                $agentFeeDr['mode'] = TXN_MODE_DR;
                $agentFeeDr['txn_remitter_id'] = $params['remitter_id'];
                $agentData = array_merge($params, $paramsFee, $agentFeeDr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */

                /*                 * *** Remitter Cr txn entry ********** */
                $remitterFeeCr['mode'] = TXN_MODE_CR;
                $remitterFeeCr['txn_agent_id'] = $params['agent_id'];
                $remitterDataCr = array_merge($params, $paramsFee, $remitterFeeCr);
                $this->insertRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterFeeDr['mode'] = TXN_MODE_DR;
                $remitterFeeDr['txn_ops_id'] = FEE_AC_ID;
                $remitterDataDr = array_merge($params, $paramsFee, $remitterFeeDr);
                $this->insertRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Ops 2 cr txn entry ****** */
                $opsFeeCr['mode'] = TXN_MODE_CR;
                $opsFeeCr['ops_id'] = FEE_AC_ID;
                $opsFeeCr['txn_remitter_id'] = $params['remitter_id'];
                $opsData = array_merge($params, $paramsFee, $opsFeeCr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops 2 cr txn entry over ****** */
            }
            /*             * ** Txns for Remit Fee Amount ends ******* */

            /* For Service Tax Amount */
            if ($params['service_tax'] > 0) {
                $paramsSt['txn_code'] = $paramsTxnCode;
                $paramsSt['amount'] = $params['service_tax'];
                $paramsSt['txn_type'] = TXNTYPE_REMITTANCE_SERVICE_TAX;

                /*                 * ******* Agent txn entry ****** */
                $agentStDr['mode'] = TXN_MODE_DR;
                $agentStDr['txn_remitter_id'] = $params['remitter_id'];
                $agentData = array_merge($params, $paramsSt, $agentStDr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */

                /*                 * *** Remitter Cr txn entry ********** */
                $remitterStCr['mode'] = TXN_MODE_CR;
                $remitterStCr['txn_agent_id'] = $params['agent_id'];
                $remitterDataCr = array_merge($params, $paramsSt, $remitterStCr);
                $this->insertRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterStDr['mode'] = TXN_MODE_DR;
                $remitterStDr['txn_ops_id'] = SERVICE_TAX_AC_ID;
                $remitterDataDr = array_merge($params, $paramsSt, $remitterStDr);
                $this->insertRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Ops 3 Cr txn entry ****** */
                $opsStCr['mode'] = TXN_MODE_CR;
                $opsStCr['ops_id'] = SERVICE_TAX_AC_ID;
                $opsStCr['txn_remitter_id'] = $params['remitter_id'];
                $opsData = array_merge($params, $paramsSt, $opsStCr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops 3 Cr txn entry over ****** */
            }
            /*             * ** Txns for Remit Service Tax Amount ends ******* */

            /* agent balance updated */
            $updArr = array('block_amount' => new Zend_Db_Expr("block_amount - " . $params['amount']), 'date_modified' => $params['date']);
            $where = "agent_id = '" . $params['agent_id'] . "'";
            $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);

            $this->_db->commit();

            return $paramsTxnCode;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }

    /*
     * for remit amount
     * for fee amount
     * for service tax amount
     */

    public function initiateBoiTxnRemit($params) {
        $this->_db->beginTransaction();

        try {
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_status'] = FLAG_SUCCESS;

            /* Amount blocked in agent balance */
            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['total_amount']),
                'block_amount' => new Zend_Db_Expr("block_amount + " . $params['total_amount']),
                'date_modified' => $params['date']);
            $where = "agent_id = '" . $params['agent_id'] . "'";
            $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);

            $txncode = new Txncode();
            if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used
            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code for remittance amount could not be generated at this time. Please try later.", Zend_Log::ALERT);
                throw new Exception("Transaction Code for remittance amount could not be generated at this time. Please try later.");
            }
            /* For Remit Amount */
            if ($params['amount'] > 0) {
                $paramsRemit['txn_code'] = $paramsTxnCode;
                $paramsRemit['amount'] = $params['amount'];
                $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE;

                /*                 * ******* Agent txn entry ****** */
                $agentRemitDr['mode'] = TXN_MODE_DR;
                $agentRemitDr['txn_remitter_id'] = $params['remitter_id'];
                $agentData = array_merge($params, $paramsRemit, $agentRemitDr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */

                /*                 * *** Remitter Cr txn entry ********** */
                $remitterRemitCr['mode'] = TXN_MODE_CR;
                $remitterRemitCr['txn_agent_id'] = $params['agent_id'];
                $remitterDataCr = array_merge($params, $paramsRemit, $remitterRemitCr);
                $this->insertRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterRemitDr['mode'] = TXN_MODE_DR;
                $remitterRemitDr['txn_ops_id'] = TXN_OPS_ID;
                $remitterDataDr = array_merge($params, $paramsRemit, $remitterRemitDr);
                $this->insertRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Ops txn entry ****** */
                $opsRemitCr['mode'] = TXN_MODE_CR;
                $opsRemitCr['ops_id'] = TXN_OPS_ID;
                $opsRemitCr['txn_remitter_id'] = $params['remitter_id'];
                $opsData = array_merge($params, $paramsRemit, $opsRemitCr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops txn entry over ****** */
            }
            /*             * ** Txns for Remit Data ends ******* */

            /* For Fee Amount */
            if ($params['fee_amt'] > 0) {
                $paramsFee['txn_code'] = $paramsTxnCode;
                $paramsFee['amount'] = $params['fee_amt'];
                $paramsFee['txn_type'] = TXNTYPE_REMITTANCE_FEE;

                /*                 * ******* Agent txn entry ****** */
                $agentFeeDr['mode'] = TXN_MODE_DR;
                $agentFeeDr['txn_remitter_id'] = $params['remitter_id'];
                $agentData = array_merge($params, $paramsFee, $agentFeeDr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */

                /*                 * *** Remitter Cr txn entry ********** */
                $remitterFeeCr['mode'] = TXN_MODE_CR;
                $remitterFeeCr['txn_agent_id'] = $params['agent_id'];
                $remitterDataCr = array_merge($params, $paramsFee, $remitterFeeCr);
                $this->insertRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterFeeDr['mode'] = TXN_MODE_DR;
                $remitterFeeDr['txn_ops_id'] = FEE_AC_ID;
                $remitterDataDr = array_merge($params, $paramsFee, $remitterFeeDr);
                $this->insertRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Ops 2 Cr txn entry ****** */
                $opsFeeCr['mode'] = TXN_MODE_CR;
                $opsFeeCr['ops_id'] = FEE_AC_ID;
                $opsFeeCr['txn_remitter_id'] = $params['remitter_id'];
                $opsData = array_merge($params, $paramsFee, $opsFeeCr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops 2 Cr txn entry over ****** */
            }
            /*             * ** Txns for Remit Fee Amount ends ******* */

            /* For Service Tax Amount */
            if ($params['service_tax'] > 0) {
                $paramsSt['txn_code'] = $paramsTxnCode;
                $paramsSt['amount'] = $params['service_tax'];
                $paramsSt['txn_type'] = TXNTYPE_REMITTANCE_SERVICE_TAX;

                /*                 * ******* Agent txn entry ****** */
                $agentStDr['mode'] = TXN_MODE_DR;
                $agentStDr['txn_remitter_id'] = $params['remitter_id'];
                $agentData = array_merge($params, $paramsSt, $agentStDr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */

                /*                 * *** Remitter Cr txn entry ********** */
                $remitterStCr['mode'] = TXN_MODE_CR;
                $remitterStCr['txn_agent_id'] = $params['agent_id'];
                $remitterDataCr = array_merge($params, $paramsSt, $remitterStCr);
                $this->insertRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterStDr['mode'] = TXN_MODE_DR;
                $remitterStDr['txn_ops_id'] = SERVICE_TAX_AC_ID;
                $remitterDataDr = array_merge($params, $paramsSt, $remitterStDr);
                $this->insertRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Ops 3 Cr txn entry ****** */
                $opsStCr['mode'] = TXN_MODE_CR;
                $opsStCr['ops_id'] = SERVICE_TAX_AC_ID;
                $opsStCr['txn_remitter_id'] = $params['remitter_id'];
                $opsData = array_merge($params, $paramsSt, $opsStCr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops 3 Cr txn entry over ****** */
            }
            /*             * ** Txns for Remit Service Tax Amount ends ******* */

            /* agent balance updated */
            $updArr = array('block_amount' => new Zend_Db_Expr("block_amount - " . $params['total_amount']), 'date_modified' => $params['date']);
            $where = "agent_id = '" . $params['agent_id'] . "'";
            $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);

            $this->_db->commit();

            return $paramsTxnCode;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }

    /*
     * $params['remit_request_id'] = remittance request id
     * $params['beneficiary_id'] = beneficiary id
     * $params['product_id'] = product id
     * $params['amount'] = amount
     * $params['txn_code'] = txn_code
     */

    public function remitBoiTxnSuccess($params) {
        $this->_db->beginTransaction();

        try {
            $params['remarks'] = '';
            $params['ip'] = ''; //$this->formatIpAddress(Util::getIP()); // cron would not return ip so commented yet
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_status'] = FLAG_SUCCESS;

            /* For Remit Amount */
            if ($params['amount'] > 0) {
                $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE;

                /*                 * *** Beneficiary Cr txn entry ********** */
                $paramsRemitCr['mode'] = TXN_MODE_CR;
                $paramsRemitCr['txn_ops_id'] = TXN_OPS_ID;
                $remitterData = array_merge($params, $paramsRemit, $paramsRemitCr);
                $this->insertRemitTxnBeneficiary($remitterData);
                /*                 * ******* Beneficiary Cr entry over ****** */

                /*                 * ******* Ops Dr txn entry ****** */
                $paramsRemitDr['mode'] = TXN_MODE_DR;
                $paramsRemitDr['txn_beneficiary_id'] = $params['beneficiary_id'];
                $paramsRemitDr['ops_id'] = TXN_OPS_ID;
                $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops Dr txn entry over ****** */
            }
            /*             * ** Txns for Remit Data ends ******* */

            $this->_db->commit();

            return true;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }

    /*
     * $params['remit_request_id'] = remittance request id
     * $params['product_id'] = product id
     * $params['amount'] = amount
     * $params['reversal_fee_amt'] = fee amount
     * $params['reversal_service_tax'] = reversal service tax
     */

    public function remitBoiTxnFailure($params) {
        $this->_db->beginTransaction();

        try {
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_status'] = FLAG_SUCCESS;

            $txncode = new Txncode();

            /* For Remit Amount */
            if ($params['amount'] > 0) {
                if ($txncode->generateTxncode()) {
                    $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                    $txncode->setUsedStatus(); //Mark Txncode as used

                    $paramsRemit['txn_code'] = $paramsTxnCode;
                    $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE_REFUND;

                    /*                     * ******* Ops 1 Dr txn entry ****** */
                    $paramsRemitDr['mode'] = TXN_MODE_DR;
                    $paramsRemitDr['ops_id'] = TXN_OPS_ID;
                    $paramsRemitDr['txn_ops_id'] = SUSPENSE_AC_ID;
                    $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                    $this->insertRemitTxnOps($opsData);
                    /*                     * ******* Ops 1 Dr txn entry over ****** */

                    /*                     * ******* Ops 4 Cr txn entry ****** */
                    $paramsRemitCr['mode'] = TXN_MODE_CR;
                    $paramsRemitCr['ops_id'] = SUSPENSE_AC_ID;
                    $paramsRemitCr['txn_ops_id'] = TXN_OPS_ID;
                    $opsData = array_merge($params, $paramsRemit, $paramsRemitCr);
                    $this->insertRemitTxnOps($opsData);
                    /*                     * ******* Ops 4 Cr txn entry over ****** */


                    /* For Reversal Fee Amount */
                    if ($params['reversal_fee_amt'] > 0) {
                        $paramsRemit['txn_code'] = $paramsTxnCode;
                        $paramsRemit['amount'] = $params['reversal_fee_amt'];
                        $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_REFUND_FEE;

                        /*                         * *** Ops Dr txn entry ********** */
                        $paramsRemitDr['mode'] = TXN_MODE_DR;
                        $paramsRemitDr['ops_id'] = FEE_AC_ID;
                        $paramsRemitDr['txn_ops_id'] = SUSPENSE_AC_ID;
                        $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                        $this->insertRemitTxnOps($opsData);
                        /*                         * ******* Ops Dr entry over ****** */

                        /*                         * ******* Remitter Cr txn entry ****** */
                        $paramsRemitCr['mode'] = TXN_MODE_CR;
                        $paramsRemitCr['ops_id'] = SUSPENSE_AC_ID;
                        $paramsRemitCr['txn_ops_id'] = FEE_AC_ID;
                        $opsData = array_merge($params, $paramsRemit, $paramsRemitCr);
                        $this->insertRemitTxnOps($opsData);
                        /*                         * ******* Remitter Cr entry over ****** */
                    }
                    /*                     * ** Txns for Reversal Fee Amount ends ******* */

                    /* For Reversal Service Tax Amount */
                    if ($params['reversal_service_tax'] > 0) {
                        $paramsRemit['txn_code'] = $paramsTxnCode;
                        $paramsRemit['amount'] = $params['reversal_service_tax'];
                        $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_SERVICE_TAX;

                        /*                         * *** Ops Dr txn entry ********** */
                        $paramsRemitDr['mode'] = TXN_MODE_DR;
                        $paramsRemitDr['ops_id'] = SERVICE_TAX_AC_ID;
                        $paramsRemitDr['txn_ops_id'] = SUSPENSE_AC_ID;
                        $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                        $this->insertRemitTxnOps($opsData);
                        /*                         * ******* Ops Dr entry over ****** */

                        /*                         * ******* Remitter Cr txn entry ****** */
                        $paramsRemitCr['mode'] = TXN_MODE_CR;
                        $paramsRemitCr['ops_id'] = SUSPENSE_AC_ID;
                        $paramsRemitCr['txn_ops_id'] = SERVICE_TAX_AC_ID;
                        $opsData = array_merge($params, $paramsRemit, $paramsRemitCr);
                        $this->insertRemitTxnOps($opsData);
                        /*                         * ******* Remitter Cr entry over ****** */
                    }
                    /*                     * ** Txns for Reversal Service Tax ends ******* */
                } else {
                    $this->_db->rollBack();
                    App_Logger::log("Transaction Code for remittance amount could not be generated at this time. Please try later.", Zend_Log::ALERT);
                    throw new Exception("Transaction Code for remittance amount could not be generated at this time. Please try later.");
                }
            }
            /*             * ** Txns for Remit Data ends ******* */

            $this->_db->commit();

            return true;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }

    /*
     * refund remit txn boi
     * dr entry in remitter txn, agent txn (cr), ops txn (cr), agent balance increased
     * 
     * for refund amount
     * for fee amount
     * for service tax amount
     * for reversal fee amount
     * for reversal service tax amount
     */

    public function remitBoiTxnRefund($params) {
        $this->_db->beginTransaction();

        try {
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_status'] = FLAG_SUCCESS;

            $txncode = new Txncode();
            if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used
            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code for refund amount could not be generated at this time. Please try later.", Zend_Log::ALERT);
		throw new Exception(ErrorCodes::ERROR_EDIGITAL_TXNCODE_NOT_GENERATED_MSG, ErrorCodes::ERROR_EDIGITAL_TXNCODE_NOT_GENERATED_CODE); 
		
            }
            /* For Total Amount */
            if ($params['total_amount'] > 0) {
                $paramsRemit['txn_code'] = $paramsTxnCode;
                $paramsRemit['amount'] = $params['total_amount'];
                $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE_REFUND;

                /*                 * *** Ops Dr txn entry ********** */
                $paramsRemitDr['mode'] = TXN_MODE_DR;
                $paramsRemitDr['ops_id'] = SUSPENSE_AC_ID;
                $paramsRemitDr['txn_remitter_id'] = $params['remitter_id'];
                $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops Dr entry over ****** */

                /*                 * ******* Remitter Cr txn entry ****** */
                $paramsRemitCr['mode'] = TXN_MODE_CR;
                $paramsRemitCr['txn_ops_id'] = SUSPENSE_AC_ID;
                $remitterDataCr = array_merge($params, $paramsRemit, $paramsRemitCr);
                $this->insertRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterRemitDr['mode'] = TXN_MODE_DR;
                $remitterRemitDr['txn_agent_id'] = $params['agent_id'];
                $remitterDataDr = array_merge($params, $paramsRemit, $remitterRemitDr);
                $this->insertRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Agent txn entry ****** */
                $agentRemitCr['mode'] = TXN_MODE_CR;
                $agentRemitCr['txn_remitter_id'] = $params['remitter_id'];
                $agentData = array_merge($params, $paramsRemit, $agentRemitCr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */
            }
            /*             * ** Txns for Total Amount ends ******* */

            /* For Reversal Fee Amount */
            if ($params['reversal_fee_amt'] > 0) {
                $paramsRemit['txn_code'] = $paramsTxnCode;
                $paramsRemit['amount'] = $params['reversal_fee_amt'];
                $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_REFUND_FEE;

                /*                 * *** Ops Dr txn entry ********** */
                $paramsRemitDr['mode'] = TXN_MODE_DR;
                $paramsRemitDr['ops_id'] = SUSPENSE_AC_ID;
                $paramsRemitDr['txn_remitter_id'] = $params['remitter_id'];
                $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops Dr entry over ****** */

                /*                 * ******* Remitter Cr txn entry ****** */
                $paramsRemitCr['mode'] = TXN_MODE_CR;
                $paramsRemitCr['txn_ops_id'] = SUSPENSE_AC_ID;
                $remitterDataCr = array_merge($params, $paramsRemit, $paramsRemitCr);
                $this->insertRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterRemitDr['mode'] = TXN_MODE_DR;
                $remitterRemitDr['txn_agent_id'] = $params['agent_id'];
                $remitterDataDr = array_merge($params, $paramsRemit, $remitterRemitDr);
                $this->insertRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Agent txn entry ****** */
                $agentRemitCr['mode'] = TXN_MODE_CR;
                $agentRemitCr['txn_remitter_id'] = $params['remitter_id'];
                $agentData = array_merge($params, $paramsRemit, $agentRemitCr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */
            }
            /*             * ** Txns for Reversal Fee Amount ends ******* */

            /* For Reversal Service Tax Amount */
            if ($params['reversal_service_tax'] > 0) {
                $paramsRemit['txn_code'] = $paramsTxnCode;
                $paramsRemit['amount'] = $params['reversal_service_tax'];
                $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_SERVICE_TAX;

                /*                 * *** Ops Dr txn entry ********** */
                $paramsRemitDr['mode'] = TXN_MODE_DR;
                $paramsRemitDr['ops_id'] = SUSPENSE_AC_ID;
                $paramsRemitDr['txn_remitter_id'] = $params['remitter_id'];
                $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops Dr entry over ****** */

                /*                 * ******* Remitter Cr txn entry ****** */
                $paramsRemitCr['mode'] = TXN_MODE_CR;
                $paramsRemitCr['txn_ops_id'] = SUSPENSE_AC_ID;
                $remitterDataCr = array_merge($params, $paramsRemit, $paramsRemitCr);
                $this->insertRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterRemitDr['mode'] = TXN_MODE_DR;
                $remitterRemitDr['txn_agent_id'] = $params['agent_id'];
                $remitterDataDr = array_merge($params, $paramsRemit, $remitterRemitDr);
                $this->insertRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Agent txn entry ****** */
                $agentRemitCr['mode'] = TXN_MODE_CR;
                $agentRemitCr['txn_remitter_id'] = $params['remitter_id'];
                $agentData = array_merge($params, $paramsRemit, $agentRemitCr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */
            }
            /*             * ** Txns for Reversal Service Tax Amount ends ******* */



            /* agent balance updated */
            $ageBal = array('amount' => $params['amount'], 'agent_id' => $params['agent_id']);
            $this->updateTxnAgentBalance($ageBal);

            $this->_db->commit();

            return $paramsTxnCode;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
	    throw new Exception(ErrorCodes::ERROR_EDIGITAL_REFUND_TXN_NOT_COMPLETED_MSG,ErrorCodes::ERROR_EDIGITAL_REFUND_TXN_NOT_COMPLETED_CODE);
        }
    }

    /*
     * Remittance entry in remitters txn table - boi
     */

    private function insertRemitTxnRemitter($params) {
        $txnRemitData['txn_code'] = $params['txn_code'];
        $txnRemitData['remitter_id'] = $params['remitter_id'];
        $txnRemitData['txn_agent_id'] = (isset($params['txn_agent_id']) && $params['txn_agent_id'] > 0) ? $params['txn_agent_id'] : 0;
        $txnRemitData['txn_ops_id'] = (isset($params['txn_ops_id']) && $params['txn_ops_id'] > 0) ? $params['txn_ops_id'] : 0;
        $txnRemitData['product_id'] = $params['product_id'];
        $txnRemitData['remittance_request_id'] = $params['remit_request_id'];
        $txnRemitData['ip'] = $params['ip'];
        $txnRemitData['currency'] = CURRENCY_INR;
        $txnRemitData['amount'] = $params['amount'];
        $txnRemitData['mode'] = $params['mode'];
        $txnRemitData['txn_type'] = $params['txn_type'];
        $txnRemitData['txn_status'] = FLAG_SUCCESS;
        $txnRemitData['remarks'] = $params['remarks'];
        $txnRemitData['date_created'] = $params['date'];
        $this->_db->insert(DbTable::TABLE_TXN_REMITTER, $txnRemitData);
    }

    /*
     * Remittance entry in agents txn table
     */

    private function insertRemitTxnAgent($params) {
        $txnAgentData['txn_code'] = $params['txn_code'];
        $txnAgentData['agent_id'] = $params['agent_id'];
        $txnAgentData['txn_remitter_id'] = (isset($params['txn_remitter_id']) && $params['txn_remitter_id'] > 0) ? $params['txn_remitter_id'] : 0;
        $txnAgentData['kotak_remitter_id'] = (isset($params['kotak_remitter_id']) && $params['kotak_remitter_id'] > 0) ? $params['kotak_remitter_id'] : 0;
        $txnAgentData['ratnakar_remitter_id'] = (isset($params['ratnakar_remitter_id']) && $params['ratnakar_remitter_id'] > 0) ? $params['ratnakar_remitter_id'] : 0;
        $txnAgentData['txn_ops_id'] = (isset($params['txn_ops_id']) && $params['txn_ops_id'] > 0) ? $params['txn_ops_id'] : 0;
        $txnAgentData['product_id'] = $params['product_id'];
        $txnAgentData['remittance_request_id'] = (isset($params['remit_request_id']) && $params['remit_request_id'] > 0) ? $params['remit_request_id'] : 0;
        $txnAgentData['kotak_remittance_request_id'] = (isset($params['kotak_remittance_request_id']) && $params['kotak_remittance_request_id'] > 0) ? $params['kotak_remittance_request_id'] : 0;
        $txnAgentData['ratnakar_remittance_request_id'] = (isset($params['ratnakar_remittance_request_id']) && $params['ratnakar_remittance_request_id'] > 0) ? $params['ratnakar_remittance_request_id'] : 0;
        $txnAgentData['ip'] = $params['ip'];
        $txnAgentData['currency'] = CURRENCY_INR;
        $txnAgentData['amount'] = $params['amount'];
        $txnAgentData['mode'] = $params['mode'];
        $txnAgentData['txn_type'] = $params['txn_type'];
        $txnAgentData['txn_status'] = (isset($params['txn_status']) && $params['txn_status'] != '') ? $params['txn_status'] : FLAG_SUCCESS;
        $txnAgentData['remarks'] = $params['remarks'];
        $txnAgentData['date_created'] = $params['date'];
        $txnAgentData['load_request_id'] = (isset($params['load_request_id']) && $params['load_request_id'] > 0) ? $params['load_request_id'] : '0';

        $this->_db->insert(DbTable::TABLE_TXN_AGENT, $txnAgentData);

    }

    /*
     * Remittance entry in ops txn table
     */

    private function insertRemitTxnOps($params) {
        $txnOpsData['txn_code'] = $params['txn_code'];
        $txnOpsData['ops_id'] = (isset($params['ops_id']) && $params['ops_id'] > 0) ? $params['ops_id'] : 0;
        
        $txnOpsData['bank_id'] = (isset($params['bank_id']) && $params['bank_id'] > 0) ? $params['bank_id'] : 0;
       
        $txnOpsData['txn_customer_master_id'] = (isset($params['txn_customer_master_id']) && $params['txn_customer_master_id'] > 0) ? $params['txn_customer_master_id'] : 0;
        $txnOpsData['purse_master_id'] = (isset($params['purse_master_id']) && $params['purse_master_id'] > 0) ? $params['purse_master_id'] : 0;
        $txnOpsData['customer_purse_id'] = (isset($params['customer_purse_id']) && $params['customer_purse_id'] > 0) ? $params['customer_purse_id'] : 0;
        $txnOpsData['txn_agent_id'] = (isset($params['txn_agent_id']) && $params['txn_agent_id'] > 0) ? $params['txn_agent_id'] : 0;
        $txnOpsData['txn_beneficiary_id'] = (isset($params['txn_beneficiary_id']) && $params['txn_beneficiary_id'] > 0) ? $params['txn_beneficiary_id'] : 0;
        $txnOpsData['kotak_beneficiary_id'] = (isset($params['kotak_beneficiary_id']) && $params['kotak_beneficiary_id'] > 0) ? $params['kotak_beneficiary_id'] : 0;
        $txnOpsData['ratnakar_beneficiary_id'] = (isset($params['ratnakar_beneficiary_id']) && $params['ratnakar_beneficiary_id'] > 0) ? $params['ratnakar_beneficiary_id'] : 0;
        $txnOpsData['txn_remitter_id'] = (isset($params['txn_remitter_id']) && $params['txn_remitter_id'] > 0) ? $params['txn_remitter_id'] : 0;
        $txnOpsData['kotak_remitter_id'] = (isset($params['kotak_remitter_id']) && $params['kotak_remitter_id'] > 0) ? $params['kotak_remitter_id'] : 0;
        $txnOpsData['ratnakar_remitter_id'] = (isset($params['ratnakar_remitter_id']) && $params['ratnakar_remitter_id'] > 0) ? $params['ratnakar_remitter_id'] : 0;
        $txnOpsData['txn_ops_id'] = (isset($params['txn_ops_id']) && $params['txn_ops_id'] > 0) ? $params['txn_ops_id'] : 0;
        $txnOpsData['product_id'] = $params['product_id'];
        $txnOpsData['remittance_request_id'] = (isset($params['remit_request_id']) && $params['remit_request_id'] > 0) ? $params['remit_request_id'] : 0;
        $txnOpsData['kotak_remittance_request_id'] = (isset($params['kotak_remittance_request_id']) && $params['kotak_remittance_request_id'] > 0) ? $params['kotak_remittance_request_id'] : 0;
        $txnOpsData['ratnakar_remittance_request_id'] = (isset($params['ratnakar_remittance_request_id']) && $params['ratnakar_remittance_request_id'] > 0) ? $params['ratnakar_remittance_request_id'] : 0;
        $txnOpsData['ip'] = $params['ip'];
        $txnOpsData['currency'] = CURRENCY_INR;
        $txnOpsData['amount'] = $params['amount'];
        $txnOpsData['mode'] = $params['mode'];
        $txnOpsData['txn_type'] = $params['txn_type'];
        $txnOpsData['txn_status'] = (isset($params['txn_status']) && $params['txn_status'] != '') ? $params['txn_status'] : FLAG_SUCCESS;
        $txnOpsData['remarks'] = $params['remarks'];
        $txnOpsData['date_created'] = $params['date'];
        $this->_db->insert(DbTable::TABLE_TXN_OPS, $txnOpsData);
    }

    /*
     * Remittance entry in beneficiary txn table
     */

    private function insertRemitTxnBeneficiary($params) {
        $txnRemitData['txn_code'] = $params['txn_code'];
        $txnRemitData['beneficiary_id'] = $params['beneficiary_id'];
        $txnRemitData['txn_ops_id'] = (isset($params['txn_ops_id']) && $params['txn_ops_id'] > 0) ? $params['txn_ops_id'] : 0;
        ;
        $txnRemitData['product_id'] = $params['product_id'];
        $txnRemitData['remittance_request_id'] = $params['remit_request_id'];
        $txnRemitData['ip'] = $params['ip'];
        $txnRemitData['currency'] = CURRENCY_INR;
        $txnRemitData['amount'] = $params['amount'];
        $txnRemitData['mode'] = $params['mode'];
        $txnRemitData['txn_type'] = $params['txn_type'];
        $txnRemitData['txn_status'] = FLAG_SUCCESS;
        $txnRemitData['remarks'] = $params['remarks'];
        $txnRemitData['date_created'] = $params['date'];

        $this->_db->insert(DbTable::TABLE_TXN_BENEFICIARY, $txnRemitData);
    }

    

    /* 
     * for fee amount
     * for service tax amount
     */

    public function insertKotakTxnRemitterRegnFee($params) {
        $this->_db->beginTransaction();

        try {
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['remit_request_id'] = 0;
            $params['kotak_remittance_request_id'] = 0;
            $params['txn_status'] = FLAG_SUCCESS;

            /* Amount blocked in agent balance */
            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']),
                'block_amount' => new Zend_Db_Expr("block_amount + " . $params['amount']),
                'date_modified' => $params['date']);
            $where = "agent_id = '" . $params['agent_id'] . "'";
            $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);

            $txncode = new Txncode();
            if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used
            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code for kotak remitter regn fee could not be generated at this time. ", Zend_Log::ALERT);
                throw new Exception("Transaction Code for kotak remitter regn fee could not be generated at this time. Please try later.");
            }

            /* For Fee Amount */
            if ($params['fee_amt'] > 0) {
                $paramsFee['txn_code'] = $paramsTxnCode;
                $paramsFee['amount'] = $params['fee_amt'];
                $paramsFee['txn_type'] = TXNTYPE_REMITTER_REGISTRATION;

                /*                 * ******* Agent txn entry ****** */
                $agentFeeDr['mode'] = TXN_MODE_DR;
                $agentFeeDr['kotak_remitter_id'] = $params['remitter_id'];
                $agentData = array_merge($params, $paramsFee, $agentFeeDr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */

                /*                 * *** Remitter Cr txn entry ********** */
                $remitterFeeCr['mode'] = TXN_MODE_CR;
                $remitterFeeCr['txn_agent_id'] = $params['agent_id'];
                $remitterDataCr = array_merge($params, $paramsFee, $remitterFeeCr);
                $this->insertKotakRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterFeeDr['mode'] = TXN_MODE_DR;
                $remitterFeeDr['txn_ops_id'] = FEE_AC_ID;
                $remitterDataDr = array_merge($params, $paramsFee, $remitterFeeDr);
                $this->insertKotakRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Ops 2 cr txn entry ****** */
                $opsFeeCr['mode'] = TXN_MODE_CR;
                $opsFeeCr['ops_id'] = FEE_AC_ID;
                $opsFeeCr['kotak_remitter_id'] = $params['remitter_id'];
                $opsData = array_merge($params, $paramsFee, $opsFeeCr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops 2 cr txn entry over ****** */
            }
            /*             * ** Txns for Remit Fee Amount ends ******* */

            /* For Service Tax Amount */
            if ($params['service_tax'] > 0) {
                $paramsSt['txn_code'] = $paramsTxnCode;
                $paramsSt['amount'] = $params['service_tax'];
                $paramsSt['txn_type'] = TXNTYPE_REMITTANCE_SERVICE_TAX;

                /*                 * ******* Agent txn entry ****** */
                $agentStDr['mode'] = TXN_MODE_DR;
                $agentStDr['kotak_remitter_id'] = $params['remitter_id'];
                $agentData = array_merge($params, $paramsSt, $agentStDr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */

                /*                 * *** Remitter Cr txn entry ********** */
                $remitterStCr['mode'] = TXN_MODE_CR;
                $remitterStCr['txn_agent_id'] = $params['agent_id'];
                $remitterDataCr = array_merge($params, $paramsSt, $remitterStCr);
                $this->insertKotakRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterStDr['mode'] = TXN_MODE_DR;
                $remitterStDr['txn_ops_id'] = SERVICE_TAX_AC_ID;
                $remitterDataDr = array_merge($params, $paramsSt, $remitterStDr);
                $this->insertKotakRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Ops 3 Cr txn entry ****** */
                $opsStCr['mode'] = TXN_MODE_CR;
                $opsStCr['ops_id'] = SERVICE_TAX_AC_ID;
                $opsStCr['kotak_remitter_id'] = $params['remitter_id'];
                $opsData = array_merge($params, $paramsSt, $opsStCr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops 3 Cr txn entry over ****** */
            }
            /*             * ** Txns for Remit Service Tax Amount ends ******* */

            /* agent balance updated */
            $updArr = array('block_amount' => new Zend_Db_Expr("block_amount - " . $params['amount']), 'date_modified' => $params['date']);
            $where = "agent_id = '" . $params['agent_id'] . "'";
            $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);

            $this->_db->commit();

            return $paramsTxnCode;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }

    private function insertKotakRemitTxnRemitter($params) {
        $txnRemitData['txn_code'] = $params['txn_code'];
        $txnRemitData['remitter_id'] = $params['remitter_id'];
        $txnRemitData['txn_agent_id'] = (isset($params['txn_agent_id']) && $params['txn_agent_id'] > 0) ? $params['txn_agent_id'] : 0;
        $txnRemitData['txn_ops_id'] = (isset($params['txn_ops_id']) && $params['txn_ops_id'] > 0) ? $params['txn_ops_id'] : 0;
        $txnRemitData['product_id'] = $params['product_id'];
        $txnRemitData['remittance_request_id'] = (isset($params['remit_request_id']) && $params['remit_request_id'] > 0) ? $params['remit_request_id'] : 0;
        $txnRemitData['ip'] = $params['ip'];
        $txnRemitData['currency'] = CURRENCY_INR;
        $txnRemitData['amount'] = $params['amount'];
        $txnRemitData['mode'] = $params['mode'];
        $txnRemitData['txn_type'] = $params['txn_type'];
        $txnRemitData['txn_status'] = (isset($params['txn_status']) && $params['txn_status'] != '') ? $params['txn_status'] : FLAG_SUCCESS;
        $txnRemitData['remarks'] = $params['remarks'];
        $txnRemitData['date_created'] = $params['date'];
        $this->_db->insert(DbTable::TABLE_KOTAK_TXN_REMITTER, $txnRemitData);
    }

    private function insertKotakRemitTxnBeneficiary($params) {
        $txnRemitData['txn_code'] = $params['txn_code'];
        $txnRemitData['beneficiary_id'] = $params['beneficiary_id'];
        $txnRemitData['txn_ops_id'] = (isset($params['txn_ops_id']) && $params['txn_ops_id'] > 0) ? $params['txn_ops_id'] : 0;
        ;
        $txnRemitData['product_id'] = $params['product_id'];
        $txnRemitData['remittance_request_id'] = $params['remit_request_id'];
        $txnRemitData['ip'] = $params['ip'];
        $txnRemitData['currency'] = CURRENCY_INR;
        $txnRemitData['amount'] = $params['amount'];
        $txnRemitData['mode'] = $params['mode'];
        $txnRemitData['txn_type'] = $params['txn_type'];
        $txnRemitData['txn_status'] = (isset($params['txn_status']) && $params['txn_status'] != '') ? $params['txn_status'] : FLAG_SUCCESS;
        $txnRemitData['remarks'] = $params['remarks'];
        $txnRemitData['date_created'] = $params['date'];

        $this->_db->insert(DbTable::TABLE_KOTAK_TXN_BENEFICIARY, $txnRemitData);
    }

    /*
     * for remit amount
     * for fee amount
     * for service tax amount
     */

    public function initiateKotakTxnRemit($params) {
        $this->_db->beginTransaction();

        try {
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_status'] = FLAG_SUCCESS;

            /* Amount blocked in agent balance */
            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['total_amount']),
                'block_amount' => new Zend_Db_Expr("block_amount + " . $params['total_amount']),
                'date_modified' => $params['date']);
            $where = "agent_id = '" . $params['agent_id'] . "'";
            $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);

            $txncode = new Txncode();
            if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used
            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code for kotak remittance amount could not be generated at this time. ", Zend_Log::ALERT);
                throw new Exception("Transaction Code for kotak remittance amount could not be generated at this time. Please try later.");
            }
            /* For Remit Amount */
            if ($params['amount'] > 0) {
                $paramsRemit['txn_code'] = $paramsTxnCode;
                $paramsRemit['amount'] = $params['amount'];
                $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE;

                /*                 * ******* Agent txn entry ****** */
                $agentRemitDr['mode'] = TXN_MODE_DR;
                $agentRemitDr['kotak_remitter_id'] = $params['remitter_id'];
                $agentRemitDr['kotak_remittance_request_id'] = $params['remit_request_id'];
                $agentRemitDr['remitter_id'] = 0;
                $agentRemitDr['remit_request_id'] = 0;
                $agentData = array_merge($params, $paramsRemit, $agentRemitDr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */

                /*                 * *** Remitter Cr txn entry ********** */
                $remitterRemitCr['mode'] = TXN_MODE_CR;
                $remitterRemitCr['txn_agent_id'] = $params['agent_id'];
                $remitterDataCr = array_merge($params, $paramsRemit, $remitterRemitCr);
                $this->insertKotakRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterRemitDr['mode'] = TXN_MODE_DR;
                $remitterRemitDr['txn_ops_id'] = TXN_OPS_ID;
                $remitterDataDr = array_merge($params, $paramsRemit, $remitterRemitDr);
                $this->insertKotakRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Ops txn entry ****** */
                $opsRemitCr['mode'] = TXN_MODE_CR;
                $opsRemitCr['ops_id'] = TXN_OPS_ID;
                $opsRemitCr['kotak_remitter_id'] = $params['remitter_id'];
                $opsRemitCr['kotak_remittance_request_id'] = $params['remit_request_id'];
                $opsRemitCr['remitter_id'] = 0;
                $opsRemitCr['remit_request_id'] = 0;
                $opsData = array_merge($params, $paramsRemit, $opsRemitCr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops txn entry over ****** */
            }
            /*             * ** Txns for Remit Data ends ******* */

            /* For Fee Amount */
            if ($params['fee_amt'] > 0) {
                $paramsFee['txn_code'] = $paramsTxnCode;
                $paramsFee['amount'] = $params['fee_amt'];
                $paramsFee['txn_type'] = TXNTYPE_REMITTANCE_FEE;

                /*                 * ******* Agent txn entry ****** */
                $agentFeeDr['mode'] = TXN_MODE_DR;
                $agentFeeDr['kotak_remitter_id'] = $params['remitter_id'];
                $agentFeeDr['kotak_remittance_request_id'] = $params['remit_request_id'];
                $agentFeeDr['remitter_id'] = 0;
                $agentFeeDr['remit_request_id'] = 0;
                $agentData = array_merge($params, $paramsFee, $agentFeeDr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */

                /*                 * *** Remitter Cr txn entry ********** */
                $remitterFeeCr['mode'] = TXN_MODE_CR;
                $remitterFeeCr['txn_agent_id'] = $params['agent_id'];
                $remitterDataCr = array_merge($params, $paramsFee, $remitterFeeCr);
                $this->insertKotakRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterFeeDr['mode'] = TXN_MODE_DR;
                $remitterFeeDr['txn_ops_id'] = FEE_AC_ID;
                $remitterDataDr = array_merge($params, $paramsFee, $remitterFeeDr);
                $this->insertKotakRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Ops 2 Cr txn entry ****** */
                $opsFeeCr['mode'] = TXN_MODE_CR;
                $opsFeeCr['ops_id'] = FEE_AC_ID;
                $opsFeeCr['kotak_remitter_id'] = $params['remitter_id'];
                $opsFeeCr['kotak_remittance_request_id'] = $params['remit_request_id'];
                $opsFeeCr['remitter_id'] = 0;
                $opsFeeCr['remit_request_id'] = 0;
                $opsData = array_merge($params, $paramsFee, $opsFeeCr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops 2 Cr txn entry over ****** */
            }
            /*             * ** Txns for Remit Fee Amount ends ******* */

            /* For Service Tax Amount */
            if ($params['service_tax'] > 0) {
                $paramsSt['txn_code'] = $paramsTxnCode;
                $paramsSt['amount'] = $params['service_tax'];
                $paramsSt['txn_type'] = TXNTYPE_REMITTANCE_SERVICE_TAX;

                /*                 * ******* Agent txn entry ****** */
                $agentStDr['mode'] = TXN_MODE_DR;
                $agentStDr['kotak_remitter_id'] = $params['remitter_id'];
                $agentStDr['kotak_remittance_request_id'] = $params['remit_request_id'];
                $agentStDr['remitter_id'] = 0;
                $agentStDr['remit_request_id'] = 0;
                $agentData = array_merge($params, $paramsSt, $agentStDr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */

                /*                 * *** Remitter Cr txn entry ********** */
                $remitterStCr['mode'] = TXN_MODE_CR;
                $remitterStCr['txn_agent_id'] = $params['agent_id'];
                $remitterDataCr = array_merge($params, $paramsSt, $remitterStCr);
                $this->insertKotakRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterStDr['mode'] = TXN_MODE_DR;
                $remitterStDr['txn_ops_id'] = SERVICE_TAX_AC_ID;
                $remitterDataDr = array_merge($params, $paramsSt, $remitterStDr);
                $this->insertKotakRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Ops 3 Cr txn entry ****** */
                $opsStCr['mode'] = TXN_MODE_CR;
                $opsStCr['ops_id'] = SERVICE_TAX_AC_ID;
                $opsStCr['kotak_remitter_id'] = $params['remitter_id'];
                $opsStCr['kotak_remittance_request_id'] = $params['remit_request_id'];
                $opsStCr['remitter_id'] = 0;
                $opsStCr['remit_request_id'] = 0;
                $opsData = array_merge($params, $paramsSt, $opsStCr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops 3 Cr txn entry over ****** */
            }
            /*             * ** Txns for Remit Service Tax Amount ends ******* */

            /* agent balance updated */
            $updArr = array('block_amount' => new Zend_Db_Expr("block_amount - " . $params['total_amount']),
                'date_modified' => $params['date']);
            $where = "agent_id = '" . $params['agent_id'] . "'";
            $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);

            $this->_db->commit();

            return $paramsTxnCode;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }

    /*
     * $params['remit_request_id'] = remittance request id
     * $params['beneficiary_id'] = beneficiary id
     * $params['product_id'] = product id
     * $params['amount'] = amount
     * $params['fee_amt'] = fee amount
     * $params['service_tax'] = service tax amount 
     * $params['total_amount'] = total amount 
     * $params['agent_id'] = agent id 
     * $params['txn_code'] = txn_code
     */

    public function remitKotakTxnSuccess($params) {
        $this->_db->beginTransaction();

        try {
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_status'] = FLAG_SUCCESS;

            /* For Remit Amount */
            if ($params['amount'] > 0) {
                $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE;

                /*                 * *** Beneficiary Cr txn entry ********** */
                $paramsRemitCr['mode'] = TXN_MODE_CR;
                $paramsRemitCr['txn_ops_id'] = TXN_OPS_ID;
                $remitterData = array_merge($params, $paramsRemit, $paramsRemitCr);
                $this->insertKotakRemitTxnBeneficiary($remitterData);
                /*                 * ******* Beneficiary Cr entry over ****** */

                /*                 * ******* Ops Dr txn entry ****** */
                $paramsRemitDr['mode'] = TXN_MODE_DR;
                $paramsRemitDr['kotak_beneficiary_id'] = $params['beneficiary_id'];
                $paramsRemitDr['ops_id'] = TXN_OPS_ID;
                $params['kotak_remittance_request_id'] = $params['remit_request_id'];
                $paramsRemitDr['remit_request_id'] = 0;
                $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops Dr txn entry over ****** */
            }
            /*             * ** Txns for Remit Data ends ******* */

            $this->_db->commit();

            return true;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }

    /*
     * $params['remit_request_id'] = remittance request id
     * $params['product_id'] = product id
     * $params['amount'] = amount
     * $params['reversal_fee_amt'] = fee amount
     * $params['reversal_service_tax'] = reversal service tax
     */

    public function remitKotakTxnFailure($params) {
        $this->_db->beginTransaction();

        try {
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_status'] = FLAG_SUCCESS;

            $txncode = new Txncode();

            /* For Remit Amount */
            if ($params['amount'] > 0) {
                if ($txncode->generateTxncode()) {
                    $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                    $txncode->setUsedStatus(); //Mark Txncode as used

                    $paramsRemit['txn_code'] = $paramsTxnCode;
                    $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE_REFUND;
                    $paramsRemit['kotak_remittance_request_id'] = $params['remit_request_id'];
                    $paramsRemit['remit_request_id'] = 0;

                    /*                     * ******* Ops 1 Dr txn entry ****** */
                    $paramsRemitDr['mode'] = TXN_MODE_DR;
                    $paramsRemitDr['ops_id'] = TXN_OPS_ID;
                    $paramsRemitDr['txn_ops_id'] = SUSPENSE_AC_ID;
                    $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                    $this->insertRemitTxnOps($opsData);
                    /*                     * ******* Ops 1 Dr txn entry over ****** */

                    /*                     * ******* Ops 4 Cr txn entry ****** */
                    $paramsRemitCr['mode'] = TXN_MODE_CR;
                    $paramsRemitCr['ops_id'] = SUSPENSE_AC_ID;
                    $paramsRemitCr['txn_ops_id'] = TXN_OPS_ID;
                    $opsData = array_merge($params, $paramsRemit, $paramsRemitCr);
                    $this->insertRemitTxnOps($opsData);
                    /*                     * ******* Ops 4 Cr txn entry over ****** */


                    /* For Reversal Fee Amount */
                    if ($params['reversal_fee_amt'] > 0) {
                        $paramsRemit['txn_code'] = $paramsTxnCode;
                        $paramsRemit['amount'] = $params['reversal_fee_amt'];
                        $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_REFUND_FEE;

                        /*                         * *** Ops Dr txn entry ********** */
                        $paramsRemitDr['mode'] = TXN_MODE_DR;
                        $paramsRemitDr['ops_id'] = FEE_AC_ID;
                        $paramsRemitDr['txn_ops_id'] = SUSPENSE_AC_ID;
                        $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                        $this->insertRemitTxnOps($opsData);
                        /*                         * ******* Ops Dr entry over ****** */

                        /*                         * ******* Remitter Cr txn entry ****** */
                        $paramsRemitCr['mode'] = TXN_MODE_CR;
                        $paramsRemitCr['ops_id'] = SUSPENSE_AC_ID;
                        $paramsRemitCr['txn_ops_id'] = FEE_AC_ID;
                        $opsData = array_merge($params, $paramsRemit, $paramsRemitCr);
                        $this->insertRemitTxnOps($opsData);
                        /*                         * ******* Remitter Cr entry over ****** */
                    }
                    /*                     * ** Txns for Reversal Fee Amount ends ******* */

                    /* For Reversal Service Tax Amount */
                    if ($params['reversal_service_tax'] > 0) {
                        $paramsRemit['txn_code'] = $paramsTxnCode;
                        $paramsRemit['amount'] = $params['reversal_service_tax'];
                        $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_SERVICE_TAX;

                        /*                         * *** Ops Dr txn entry ********** */
                        $paramsRemitDr['mode'] = TXN_MODE_DR;
                        $paramsRemitDr['ops_id'] = SERVICE_TAX_AC_ID;
                        $paramsRemitDr['txn_ops_id'] = SUSPENSE_AC_ID;
                        $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                        $this->insertRemitTxnOps($opsData);
                        /*                         * ******* Ops Dr entry over ****** */

                        /*                         * ******* Remitter Cr txn entry ****** */
                        $paramsRemitCr['mode'] = TXN_MODE_CR;
                        $paramsRemitCr['ops_id'] = SUSPENSE_AC_ID;
                        $paramsRemitCr['txn_ops_id'] = SERVICE_TAX_AC_ID;
                        $opsData = array_merge($params, $paramsRemit, $paramsRemitCr);
                        $this->insertRemitTxnOps($opsData);
                        /*                         * ******* Remitter Cr entry over ****** */
                    }
                    /*                     * ** Txns for Reversal Service Tax ends ******* */
                } else {
                    $this->_db->rollBack();
                    App_Logger::log("Transaction Code for remittance amount could not be generated at this time. Please try later.", Zend_Log::ALERT);
                    throw new Exception("Transaction Code for remittance amount could not be generated at this time. Please try later.");
                }
            }
            /*             * ** Txns for Remit Data ends ******* */

            $this->_db->commit();

            return true;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }

    /*
     * for refund amount
     * for fee amount
     * for service tax amount
     * for reversal fee amount
     * for reversal service tax amount
     */

    public function remitKotakTxnRefund($params) {
        $this->_db->beginTransaction();

        try {
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_status'] = FLAG_SUCCESS;

            $txncode = new Txncode();
            if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used
            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code for refund amount could not be generated at this time. Please try later.", Zend_Log::ALERT);
		throw new Exception(ErrorCodes::ERROR_EDIGITAL_TXNCODE_NOT_GENERATED_MSG, ErrorCodes::ERROR_EDIGITAL_TXNCODE_NOT_GENERATED_CODE);
            }
            /* For Total Amount */
            if ($params['total_amount'] > 0) {
                $paramsRemit['txn_code'] = $paramsTxnCode;
                $paramsRemit['amount'] = $params['total_amount'];
                $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE_REFUND;

                /*                 * *** Ops Dr txn entry ********** */
                $paramsRemitDr['mode'] = TXN_MODE_DR;
                $paramsRemitDr['ops_id'] = SUSPENSE_AC_ID;
                $paramsRemitDr['kotak_remittance_request_id'] = $params['remit_request_id'];
                $paramsRemitDr['remit_request_id'] = 0;
                $paramsRemitDr['kotak_remitter_id'] = $params['remitter_id'];
                $paramsRemitDr['txn_remitter_id'] = 0;
                $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops Dr entry over ****** */

                /*                 * ******* Remitter Cr txn entry ****** */
                $paramsRemitCr['mode'] = TXN_MODE_CR;
                $paramsRemitCr['txn_ops_id'] = SUSPENSE_AC_ID;
                $remitterDataCr = array_merge($params, $paramsRemit, $paramsRemitCr);
                $this->insertKotakRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterRemitDr['mode'] = TXN_MODE_DR;
                $remitterRemitDr['txn_agent_id'] = $params['agent_id'];
                $remitterDataDr = array_merge($params, $paramsRemit, $remitterRemitDr);
                $this->insertKotakRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Agent txn entry ****** */
                $agentRemitCr['mode'] = TXN_MODE_CR;
                $agentRemitCr['kotak_remittance_request_id'] = $params['remit_request_id'];
                $agentRemitCr['remit_request_id'] = 0;
                $agentRemitCr['kotak_remitter_id'] = $params['remitter_id'];
                $agentRemitCr['txn_remitter_id'] = 0;
                $agentData = array_merge($params, $paramsRemit, $agentRemitCr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */
            }
            /*             * ** Txns for Total Amount ends ******* */

            /* For Reversal Fee Amount */
            if ($params['reversal_fee_amt'] > 0) {
                $paramsRemit['txn_code'] = $paramsTxnCode;
                $paramsRemit['amount'] = $params['reversal_fee_amt'];
                $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_REFUND_FEE;

                /*                 * *** Ops Dr txn entry ********** */
                $paramsRemitDr['mode'] = TXN_MODE_DR;
                $paramsRemitDr['ops_id'] = SUSPENSE_AC_ID;
                $paramsRemitDr['kotak_remittance_request_id'] = $params['remit_request_id'];
                $paramsRemitDr['remit_request_id'] = 0;
                $paramsRemitDr['kotak_remitter_id'] = $params['remitter_id'];
                $paramsRemitDr['txn_remitter_id'] = 0;
                $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops Dr entry over ****** */

                /*                 * ******* Remitter Cr txn entry ****** */
                $paramsRemitCr['mode'] = TXN_MODE_CR;
                $paramsRemitCr['txn_ops_id'] = SUSPENSE_AC_ID;
                $remitterDataCr = array_merge($params, $paramsRemit, $paramsRemitCr);
                $this->insertKotakRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterRemitDr['mode'] = TXN_MODE_DR;
                $remitterRemitDr['txn_agent_id'] = $params['agent_id'];
                $remitterDataDr = array_merge($params, $paramsRemit, $remitterRemitDr);
                $this->insertKotakRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Agent txn entry ****** */
                $agentRemitCr['mode'] = TXN_MODE_CR;
                $agentRemitCr['kotak_remittance_request_id'] = $params['remit_request_id'];
                $agentRemitCr['remit_request_id'] = 0;
                $agentRemitCr['kotak_remitter_id'] = $params['remitter_id'];
                $agentRemitCr['txn_remitter_id'] = 0;
                $agentData = array_merge($params, $paramsRemit, $agentRemitCr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */
            }
            /*             * ** Txns for Reversal Fee Amount ends ******* */

            /* For Reversal Service Tax Amount */
            if ($params['reversal_service_tax'] > 0) {
                $paramsRemit['txn_code'] = $paramsTxnCode;
                $paramsRemit['amount'] = $params['reversal_service_tax'];
                $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_SERVICE_TAX;

                /*                 * *** Ops Dr txn entry ********** */
                $paramsRemitDr['mode'] = TXN_MODE_DR;
                $paramsRemitDr['ops_id'] = SUSPENSE_AC_ID;
                $paramsRemitDr['kotak_remittance_request_id'] = $params['remit_request_id'];
                $paramsRemitDr['remit_request_id'] = 0;
                $paramsRemitDr['kotak_remitter_id'] = $params['remitter_id'];
                $paramsRemitDr['txn_remitter_id'] = 0;
                $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops Dr entry over ****** */

                /*                 * ******* Remitter Cr txn entry ****** */
                $paramsRemitCr['mode'] = TXN_MODE_CR;
                $paramsRemitCr['txn_ops_id'] = SUSPENSE_AC_ID;
                $remitterDataCr = array_merge($params, $paramsRemit, $paramsRemitCr);
                $this->insertKotakRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterRemitDr['mode'] = TXN_MODE_DR;
                $remitterRemitDr['txn_agent_id'] = $params['agent_id'];
                $remitterDataDr = array_merge($params, $paramsRemit, $remitterRemitDr);
                $this->insertKotakRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Agent txn entry ****** */
                $agentRemitCr['mode'] = TXN_MODE_CR;
                $agentRemitCr['kotak_remittance_request_id'] = $params['remit_request_id'];
                $agentRemitCr['remit_request_id'] = 0;
                $agentRemitCr['kotak_remitter_id'] = $params['remitter_id'];
                $agentRemitCr['txn_remitter_id'] = 0;
                $agentData = array_merge($params, $paramsRemit, $agentRemitCr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */
            }
            /*             * ** Txns for Reversal Service Tax Amount ends ******* */

            /* agent balance updated */
            $ageBal = array('amount' => $params['amount'], 'agent_id' => $params['agent_id']);
            $this->updateTxnAgentBalance($ageBal);

            $this->_db->commit();

            return $paramsTxnCode;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_REFUND_TXN_NOT_COMPLETED_MSG,ErrorCodes::ERROR_EDIGITAL_REFUND_TXN_NOT_COMPLETED_CODE);
        }
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
    public function successTxnRatCorporateCardLoad($params)
    {
        $this->_db->beginTransaction(); 
        
        try 
        { 
            $params['remarks'] = '';
            $params['ip'] = '';//$this->formatIpAddress(Util::getIP()); // cron would not return ip so commented yet
            $params['date'] = new Zend_Db_Expr('NOW()');
            if($params['txn_type'] == '') {
                $params['txn_type'] = TXNTYPE_RAT_CORP_CORPORATE_LOAD;
            }
            $params['txn_status'] = FLAG_SUCCESS;

            /********* Ops Dr txn entry *******/
            $paramsDr['mode'] = TXN_MODE_DR;
            $paramsDr['ops_id'] = TXN_OPS_ID;
            $paramsDr['txn_customer_master_id'] = $params['customer_master_id'];
            $opsData = array_merge($params, $paramsDr);
            $this->insertTxnOps($opsData);
            /********* Ops Dr txn entry over *******/
            
            /***** Customer Cr txn entry ***********/
            $paramsCr['mode'] = TXN_MODE_CR;
            $paramsCr['txn_ops_id'] = TXN_OPS_ID;
            $custData = array_merge($params, $paramsCr);
            $this->insertTxnRatCustomer($custData);
            /********* Customer Cr entry over *******/

            $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => $params['date']);
            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);
            
            $this->_db->commit();
            
            return TRUE;

        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage());
        }
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
    public function successTxnRatMediAssistCardLoad($params)
    {
        $this->_db->beginTransaction(); 
        
        try 
        { 
            $params['date'] = new Zend_Db_Expr('NOW()');
            
            $params['remarks'] = '';
            $params['ip'] = '';//$this->formatIpAddress(Util::getIP()); // cron would not return ip so commented yet
            if($params['txn_type'] == '') {
                $params['txn_type'] = TXNTYPE_RAT_CORP_MEDIASSIST_LOAD;
            }
            $params['txn_status'] = FLAG_SUCCESS;

            /********* Agent Dr txn entry *******/
            $paramsDr['mode'] = TXN_MODE_DR;
            $paramsDr['txn_customer_master_id'] = $params['customer_master_id'];
            $agentData = array_merge($params, $paramsDr);
            $this->insertTxnAgent($agentData);
            /********* Agent Dr txn entry over *******/
            
            /***** Customer Cr txn entry ***********/
            $paramsCr['mode'] = TXN_MODE_CR;
            $paramsCr['txn_agent_id'] =  $params['agent_id'];
            $custData = array_merge($params, $paramsCr);
            $this->insertTxnRatCustomer($custData);
            /********* Customer Cr entry over *******/

            $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => $params['date']);
            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);
            
            /* agent balance updated */
            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']),
                'date_modified' => $params['date']);
            $where = "agent_id = '" . $params['agent_id'] . "'";
            $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);	
            
            $this->_db->commit();
            
            return TRUE;

        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage());
        }
    }
    
    
    /* 
     * $params['txn_code'] = txn code
     * $params['customer_master_id'] = customer master id
     * $params['product_id'] = product id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount   
     * $params['agent_id'] =>  agent id  
     * $params['txn_type'] =>  txn_type  
     */
    public function successTxnRatCardLoad($params)
    {

        $isReversal = isset($params['is_reversal'])?$params['is_reversal']:REVERSAL_FLAG_NO;
        $isSettledReversal = isset($params['is_settled_reversal'])?$params['is_settled_reversal']:REVERSAL_FLAG_NO;
        
        $isVirtual = isset($params['is_virtual']) ? $params['is_virtual'] : FLAG_NO;
        $this->_db->beginTransaction(); 
        if($params['manageType'] == CORPORATE_MANAGE_TYPE){
            $this->successTxnCorpCardLoad($params);
        }elseif($isSettledReversal == REVERSAL_FLAG_YES){
            $this->successTxnRatSettledReversalCardLoadAPI($params);
        }elseif($isReversal == REVERSAL_FLAG_YES){
            $this->successTxnRatReversalCardLoadAPI($params);
        }elseif($isVirtual == FLAG_YES){
            $this->successTxnAgentVirtualCardLoad($params);
        }
        else{
        try 
        { 
            $params['date'] = new Zend_Db_Expr('NOW()');
            
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());//$this->formatIpAddress(Util::getIP()); // cron would not return ip so commented yet
            $params['txn_status'] = FLAG_SUCCESS;

            /********* Agent Dr txn entry *******/
            $paramsDr['mode'] = TXN_MODE_DR;
            $paramsDr['txn_customer_master_id'] = $params['customer_master_id'];
            $agentData = array_merge($params, $paramsDr);
            $this->insertTxnAgent($agentData);
            /********* Agent Dr txn entry over *******/
            
            /***** Customer Cr txn entry ***********/
            $paramsCr['mode'] = TXN_MODE_CR;
            $paramsCr['txn_agent_id'] =  $params['agent_id'];
            $custData = array_merge($params, $paramsCr);
            $this->insertTxnRatCustomer($custData);
            /********* Customer Cr entry over *******/
            $agentBalanceModel = new AgentBalance();
            $agentAmt = $agentBalanceModel->getAgentBalanceLock($params['agent_id']);
            if($agentAmt >= $params['amount']) {
            $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => $params['date']);
            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);
            
            /* agent balance updated */
            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']),
                'date_modified' => $params['date']);
            $where = "agent_id = '" . $params['agent_id'] . "'";
            $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);	
            
            $this->_db->commit();
            
            return TRUE;
            }else{
                throw new Exception ("Agent does not have sufficient fund");
            }

        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage(), $e->getCode());
        }
       }
    }
    
    
    /* 
     * $params['txn_code'] = txn code
     * $params['customer_master_id'] = customer master id
     * $params['product_id'] = product id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount   
     * $params['agent_id'] =>  MEDIASSIST_AGENT_ID  
     * $params['manageType'] =>  corporate / agent  
     * $params['debit_api_cr'] =>  pool / payable  
     * $params['payable_ac_id'] =>  payable_ac_id  
     */
    public function successTxnRatMediAssistCardDebit($params)
    {
        
        if(!isset($params['debit_api_cr']) || $params['debit_api_cr'] != POOL_AC) {
          $params['debit_api_cr'] = PAYABLE_AC; 
        }
        if($params['debit_api_cr'] == PAYABLE_AC){
            
            if(!isset($params['payable_ac_id']) || empty($params['payable_ac_id'])) {
                $params['payable_ac_id'] = DEFAULT_PAYABLE_ID; 
            }
            unset($params['agent_id']);
            $params['ops_id'] = $params['payable_ac_id'];
        }
        $isVirtual = isset($params['is_virtual']) ? $params['is_virtual'] : FLAG_NO;
        if($params['manageType'] == CORPORATE_MANAGE_TYPE){
            $this->successTxnRatCorpCardDebit($params);
        }elseif($isVirtual == FLAG_YES){
             $this->successTxnRatAgentVirtualCardDebit($params);
        }
        else{
        try 
        { 
            $custPurseModel = new Corp_Ratnakar_CustomerPurse();
            $this->_db->beginTransaction(); 
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP()); 
            $params['txn_type'] = TXNTYPE_CARD_DEBIT;
            $params['txn_status'] = FLAG_SUCCESS;
            
            if($params['debit_api_cr'] == POOL_AC) {
                
                
                /********* Agent Cr txn entry *******/
                $paramsCr['mode'] = TXN_MODE_CR;
                $paramsCr['txn_customer_master_id'] = $params['customer_master_id'];
                $agentData = array_merge($params, $paramsCr);
                $this->insertTxnAgent($agentData);
                /********* Agent Cr txn entry over *******/
                
                /***** Customer Dr txn entry ***********/
                $paramsDr['mode'] = TXN_MODE_DR;
                $paramsDr['txn_agent_id'] =  $params['agent_id'];
                $custData = array_merge($params, $paramsDr);
                $this->insertTxnRatCustomer($custData);
                /********* Customer Dr entry over *******/
                
                /* agent balance updated */
                $ageBal = array('amount' => $params['amount'], 'agent_id' => $params['agent_id']);
		$this->updateTxnAgentBalance($ageBal);
                
            } else {
                
                
                /********* Ops Cr txn entry *******/
                $paramsCr['mode'] = TXN_MODE_CR;
                $paramsCr['txn_customer_master_id'] = $params['customer_master_id'];
                $agentData = array_merge($params, $paramsCr);
                $this->insertTxnOps($agentData);
                /********* Cr txn entry over *******/
                
                /***** Customer Dr txn entry ***********/
                $paramsDr['mode'] = TXN_MODE_DR;
                $paramsDr['txn_ops_id'] =  $params['ops_id'];
                $custData = array_merge($params, $paramsDr);
                $this->insertTxnRatCustomer($custData);
                /********* Customer Dr entry over *******/
            }
            
           $custBal = $custPurseModel->getCustBalanceByCustPurseID($params['customer_purse_id']);
            if($custBal >= $params['amount']) {
                $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']), 'date_updated' => $params['date']);
                $where = "id = '" . $params['customer_purse_id'] . "'";
                $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);

                $this->_db->commit();

                return TRUE;
            }else{
                 throw new Exception ("Customer does not have sufficient fund");
            }
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            $code = $e->getCode();
            if(empty($code)) {
                $code = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
            }                    
            //$code = (!empty($e->getCode())) ? $e->getCode() : ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
            throw new App_Exception($e->getMessage(), $code);
        }
        }
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
    public function successTxnKotakGPRCardDebit($params)
    {
        $this->_db->beginTransaction(); 
        
        try 
        { 
            $params['date'] = new Zend_Db_Expr('NOW()');
            
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP()); 
            $params['txn_type'] = TXNTYPE_CARD_DEBIT;
            $params['txn_status'] = FLAG_SUCCESS;

            /********* Agent Cr txn entry *******/
            $paramsDr['mode'] = TXN_MODE_CR;
            $paramsDr['txn_customer_master_id'] = $params['customer_master_id'];
            $agentData = array_merge($params, $paramsDr);
            $this->insertTxnAgent($agentData);
            /********* Agent Dr txn entry over *******/
            
            /***** Customer Dr txn entry ***********/
            $paramsCr['mode'] = TXN_MODE_DR;
            $paramsCr['txn_agent_id'] =  $params['agent_id'];
            $custData = array_merge($params, $paramsCr);
            $this->insertTxnKotakCustomer($custData);
            /********* Customer Cr entry over *******/

            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']), 'date_updated' => $params['date']);
            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_KOTAK_CUSTOMER_PURSE, $updArr, $where);
            
            /* agent balance updated */
            $ageBal = array('amount' => $params['amount'], 'agent_id' => $params['agent_id']);
            $this->updateTxnAgentBalance($ageBal);	
            
            $this->_db->commit();
            
            return TRUE;

        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage());
        }
    }
    
    
    /* 
     * $params['txn_code'] = txn code
     * $params['customer_master_id'] = customer master id
     * $params['product_id'] = product id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount   
     * $params['agent_id'] =>  agent id  
     * $params['txn_type'] =>  txn_type  
     */
    public function successTxnKotakCardLoad($params)
    {
        $this->_db->beginTransaction(); 
        
        try 
        { 
            $params['date'] = new Zend_Db_Expr('NOW()');
            
            $params['remarks'] = '';
            $params['ip'] = '';//$this->formatIpAddress(Util::getIP()); // cron would not return ip so commented yet
            $params['txn_status'] = FLAG_SUCCESS;

            /********* Agent Dr txn entry *******/
            $paramsDr['mode'] = TXN_MODE_DR;
            $paramsDr['txn_customer_master_id'] = $params['customer_master_id'];
            $agentData = array_merge($params, $paramsDr);
            $this->insertTxnAgent($agentData);
            /********* Agent Dr txn entry over *******/
            
            /***** Customer Cr txn entry ***********/
            $paramsCr['mode'] = TXN_MODE_CR;
            $paramsCr['txn_agent_id'] =  $params['agent_id'];
            $custData = array_merge($params, $paramsCr);
            $this->insertTxnKotakCustomer($custData);
            /********* Customer Cr entry over *******/

            $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => $params['date']);
            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_KOTAK_CUSTOMER_PURSE, $updArr, $where);
            
            /* agent balance updated */
            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']),
                'date_modified' => $params['date']);
            $where = "agent_id = '" . $params['agent_id'] . "'";
            $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);	
            
            $this->_db->commit();
            
            return TRUE;

        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage());
        }
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
     public function reversalTxnRatCardLoad($params)
    {
         $this->_db->beginTransaction(); 
        
        try 
        { 
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['remarks'] = '';
            $params['ip'] = '';//$this->formatIpAddress(Util::getIP()); // cron would not return ip so commented yet
            $params['txn_status'] = FLAG_SUCCESS;
            
                
            /* agent balance reverted*/
            if($params['txn_type'] == TXNTYPE_REVERSAL_RAT_CORP_MEDIASSIST_LOAD || $params['txn_type'] == TXNTYPE_REVERSAL_LOAD) {
                
                /***** Customer Dr txn entry ***********/
                $paramsDr['mode'] = TXN_MODE_DR;
                $paramsDr['txn_agent_id'] =  $params['agent_id'];
                $custData = array_merge($params, $paramsDr);
                $this->insertTxnRatCustomer($custData);
                /********* Customer Dr entry over *******/
                
                /********* Agent Cr txn entry *******/
                $paramsCr['mode'] = TXN_MODE_CR;
                $paramsCr['txn_customer_master_id'] = $params['customer_master_id'];
                $agentData = array_merge($params, $paramsCr);
                $this->insertTxnAgent($agentData);
                /********* Agent Cr txn entry over *******/
                
                $ageBal = array('amount' => $params['amount'], 'agent_id' => $params['agent_id']);
		$this->updateTxnAgentBalance($ageBal);
            }
            else {
                
                 /***** Customer Dr txn entry ***********/
                $paramsDr['mode'] = TXN_MODE_DR;
                $paramsDr['txn_ops_id'] = TXN_OPS_ID;
                $custData = array_merge($params, $paramsDr);
                $this->insertTxnRatCustomer($custData);
                /********* Customer Dr entry over *******/

                /********* Ops Cr txn entry *******/
                $paramsCr['mode'] = TXN_MODE_CR;
                $paramsCr['ops_id'] = TXN_OPS_ID;
                $paramsCr['txn_customer_master_id'] = $params['customer_master_id'];
                $opsData = array_merge($params, $paramsCr);
                $this->insertTxnOps($opsData);
                /********* Ops Cr txn entry over *******/
            }
            
            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']), 
                            'date_updated' => $params['date']);
            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);

            $this->_db->commit();
            
            return true;

        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage());
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
        'txn_type' => TXNTYPE_CORP_AUTH_TXN_PROCESSING/ TXNTYPE_REVERSAL_CORP_AUTH_TXN_PROCESSING/ TXNTYPE_RAT_CORP_AUTH_TXN_PROCESSING  / TXNTYPE_REVERSAL_RAT_CORP_AUTH_TXN_PROCESSING
     */
    public function successTxnRatMediAssistCardTxn($params)
    {
        $this->_db->beginTransaction(); 
        
        try 
        { 
            $params['remarks'] = '';
            $params['ip'] = '';
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_status'] = FLAG_SUCCESS;

            /* Customer Dr txn entry ******/
            $paramsDr['mode'] = ($params['txn_type'] == TXNTYPE_REVERSAL_CORP_AUTH_TXN_PROCESSING) ? TXN_MODE_CR : TXN_MODE_DR;
            $paramsDr['customer_purse_id'] = ($params['customer_master_id'] == CUSTOMER_MEDIASSIST_EXPENSE_ID) ? 0 : $params['customer_purse_id']; 
            $custData = array_merge($params, $paramsDr);
            $this->insertTxnRatCustomer($custData);
            /** Customer Dr entry over ****/
            
            /* Customer Cr txn entry ******/
            $paramsCr['mode'] = ($params['txn_type'] == TXNTYPE_REVERSAL_CORP_AUTH_TXN_PROCESSING) ? TXN_MODE_DR : TXN_MODE_CR;
            $paramsCr['customer_master_id'] =  $params['txn_customer_master_id'];
            $paramsCr['txn_customer_master_id'] =  $params['customer_master_id'];
            $paramsCr['customer_purse_id'] = ($paramsCr['customer_master_id'] == CUSTOMER_MEDIASSIST_EXPENSE_ID) ? 0 : $params['customer_purse_id'];
            $custData = array_merge($params, $paramsCr);
            $this->insertTxnRatCustomer($custData);
            /** Customer Cr entry over */

            if($params['txn_type'] == TXNTYPE_REVERSAL_CORP_AUTH_TXN_PROCESSING) {
                $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => $params['date']);
            }
            else {
                $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']), 'date_updated' => $params['date']);
            }
            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);
            
            $this->_db->commit();
            
            return TRUE;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage());
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
        'txn_type' => TXNTYPE_CORP_AUTH_TXN_PROCESSING / TXNTYPE_REVERSAL_CORP_AUTH_TXN_PROCESSING / TXNTYPE_BOI_CORP_AUTH_TXN_PROCESSING  / TXNTYPE_REVERSAL_BOI_CORP_AUTH_TXN_PROCESSING
     */
    public function successTxnBoiNsdcCardTxn($params)
    {
        $this->_db->beginTransaction(); 
        
        try 
        { 
            $params['remarks'] = '';
            $params['ip'] = '';
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_status'] = FLAG_SUCCESS;

            /***** Customer Dr txn entry ***********/
            $paramsDr['mode'] = ($params['txn_type'] == TXNTYPE_REVERSAL_CORP_AUTH_TXN_PROCESSING) ? TXN_MODE_CR : TXN_MODE_DR;
            $custData = array_merge($params, $paramsDr);
            $this->insertTxnBoiCustomer($custData);
            /********* Customer Dr entry over *******/
            
            /***** Customer Cr txn entry ***********/
            $paramsCr['mode'] = ($params['txn_type'] == TXNTYPE_REVERSAL_CORP_AUTH_TXN_PROCESSING) ? TXN_MODE_DR : TXN_MODE_CR;
            $paramsCr['customer_master_id'] =  $params['txn_customer_master_id'];
            $paramsCr['txn_customer_master_id'] =  $params['customer_master_id'];
            $custData = array_merge($params, $paramsCr);
            $this->insertTxnBoiCustomer($custData);
            /********* Customer Cr entry over *******/

            if($params['txn_type'] == TXNTYPE_REVERSAL_CORP_AUTH_TXN_PROCESSING) {
                $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => $params['date']);
            }
            else {
                $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']), 'date_updated' => $params['date']);
            }
            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_BOI_CUSTOMER_PURSE, $updArr, $where);
            
            $this->_db->commit();
            
            return TRUE;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage());
        }
    }
    
    /*
     * Entry in ops txn table
     */
    private function insertTxnOps($params) {
        $txnOpsData['txn_code'] = $params['txn_code'];
        $txnOpsData['ops_id'] = (isset($params['ops_id']) && $params['ops_id'] > 0) ? $params['ops_id'] : 0;
        ;
        $txnOpsData['txn_customer_master_id'] = (isset($params['txn_customer_master_id']) && $params['txn_customer_master_id'] > 0) ? $params['txn_customer_master_id'] : 0;
        $txnOpsData['purse_master_id'] = (isset($params['purse_master_id']) && $params['purse_master_id'] > 0) ? $params['purse_master_id'] : 0;
        $txnOpsData['customer_purse_id'] = (isset($params['customer_purse_id']) && $params['customer_purse_id'] > 0) ? $params['customer_purse_id'] : 0;
        $txnOpsData['txn_cardholder_id'] = (isset($params['txn_cardholder_id']) && $params['txn_cardholder_id'] > 0) ? $params['txn_cardholder_id'] : 0;
        $txnOpsData['txn_agent_id'] = (isset($params['txn_agent_id']) && $params['txn_agent_id'] > 0) ? $params['txn_agent_id'] : 0;
        $txnOpsData['txn_ops_id'] = (isset($params['txn_ops_id']) && $params['txn_ops_id'] > 0) ? $params['txn_ops_id'] : 0;
        $txnOpsData['txn_remitter_id'] = (isset($params['txn_remitter_id']) && $params['txn_remitter_id'] > 0) ? $params['txn_remitter_id'] : 0;
        $txnOpsData['kotak_remitter_id'] = (isset($params['kotak_remitter_id']) && $params['kotak_remitter_id'] > 0) ? $params['kotak_remitter_id'] : 0;
        $txnOpsData['txn_beneficiary_id'] = (isset($params['txn_beneficiary_id']) && $params['txn_beneficiary_id'] > 0) ? $params['txn_beneficiary_id'] : 0;
        $txnOpsData['kotak_beneficiary_id'] = (isset($params['kotak_beneficiary_id']) && $params['kotak_beneficiary_id'] > 0) ? $params['kotak_beneficiary_id'] : 0;
        $txnOpsData['product_id'] = $params['product_id'];
        $txnOpsData['bank_id'] = (isset($params['bank_id']) && $params['bank_id'] > 0) ? $params['bank_id'] : 0;
        $txnOpsData['agent_fund_request_id'] = (isset($params['agent_fund_request_id']) && $params['agent_fund_request_id'] > 0) ? $params['agent_fund_request_id'] : 0;
        $txnOpsData['agent_funding_id'] = (isset($params['agent_funding_id']) && $params['agent_funding_id'] > 0) ? $params['agent_funding_id'] : 0;
        $txnOpsData['remittance_request_id'] = (isset($params['remit_request_id']) && $params['remit_request_id'] > 0) ? $params['remit_request_id'] : 0;
        $txnOpsData['kotak_remittance_request_id'] = (isset($params['kotak_remittance_request_id']) && $params['kotak_remittance_request_id'] > 0) ? $params['kotak_remittance_request_id'] : 0;
        $txnOpsData['is_virtual'] = (isset($params['is_virtual']) && $params['is_virtual'] == FLAG_YES) ? $params['is_virtual'] : FLAG_NO;
        $txnOpsData['ip'] = $params['ip'];
        $txnOpsData['currency'] = CURRENCY_INR;
        $txnOpsData['amount'] = $params['amount'];
        $txnOpsData['mode'] = $params['mode'];
        $txnOpsData['txn_type'] = $params['txn_type'];
        $txnOpsData['txn_status'] = (isset($params['txn_status']) && $params['txn_status'] != '') ? $params['txn_status'] : FLAG_SUCCESS;
        $txnOpsData['remarks'] = $params['remarks'];
        $txnOpsData['date_created'] = $params['date'];
        $txnOpsData['is_virtual'] = (isset($params['is_virtual'])) ? $params['is_virtual'] : FLAG_NO;
       
        $this->_db->insert(DbTable::TABLE_TXN_OPS, $txnOpsData);
    }
    
    /*
     * entry in rat customer txn table
     */
    private function insertTxnRatCustomer($params)
    {
        $txnData['txn_code'] = $params['txn_code'];
        $txnData['customer_master_id'] = $params['customer_master_id'];
        $txnData['txn_customer_master_id'] = (isset($params['txn_customer_master_id']) && $params['txn_customer_master_id'] > 0) ? $params['txn_customer_master_id'] : 0;
        $txnData['txn_agent_id'] = (isset($params['txn_agent_id']) && $params['txn_agent_id'] > 0) ? $params['txn_agent_id'] : 0;
        $txnData['txn_corporate_id'] = (isset($params['txn_corporate_id']) && $params['txn_corporate_id'] > 0) ? $params['txn_corporate_id'] : 0;
        $txnData['txn_ops_id'] = (isset($params['txn_ops_id']) && $params['txn_ops_id'] > 0) ? $params['txn_ops_id'] : 0;
        $txnData['product_id'] = $params['product_id'];
        $txnData['bank_id'] = (isset($params['bank_id']) && $params['bank_id'] > 0) ? $params['bank_id'] : 0;
        $txnData['purse_master_id'] = $params['purse_master_id'];
        $txnData['customer_purse_id'] = (isset($params['customer_purse_id']) && $params['customer_purse_id'] > 0) ? $params['customer_purse_id'] : 0;
        $txnData['ip'] = $params['ip'];
        $txnData['currency'] = CURRENCY_INR;
        $txnData['amount'] = $params['amount'];
        $txnData['mode'] = $params['mode'];
        $txnData['txn_type'] = $params['txn_type'];
        $txnData['txn_status'] = $params['txn_status'];
        $txnData['remarks'] = $params['remarks'];
        $txnData['date_created'] = $params['date'];
        $txnData['is_virtual'] = (isset($params['is_virtual'])) ? $params['is_virtual'] : FLAG_NO;
        $this->_db->insert(DbTable::TABLE_RAT_TXN_CUSTOMER,$txnData);
    }
    
    /*
     * entry in agents txn table
     */
    private function insertTxnAgent($params)
    {
        $txnAgentData['txn_code'] = $params['txn_code'];
        $txnAgentData['agent_id'] = $params['agent_id'];
        $txnAgentData['txn_customer_master_id'] = (isset($params['txn_customer_master_id']) && $params['txn_customer_master_id'] > 0) ? $params['txn_customer_master_id'] : 0;
        $txnAgentData['txn_cardholder_id'] = (isset($params['txn_cardholder_id']) && $params['txn_cardholder_id'] > 0) ? $params['txn_cardholder_id'] : 0;
        $txnAgentData['txn_agent_id'] = (isset($params['txn_agent_id']) && $params['txn_agent_id'] > 0) ? $params['txn_agent_id'] : 0;
        $txnAgentData['txn_ops_id'] = (isset($params['txn_ops_id']) && $params['txn_ops_id'] > 0) ? $params['txn_ops_id'] : 0;
        $txnAgentData['txn_remitter_id'] = (isset($params['txn_remitter_id']) && $params['txn_remitter_id'] > 0) ? $params['txn_remitter_id'] : 0;
        $txnAgentData['kotak_remitter_id'] = (isset($params['kotak_remitter_id']) && $params['kotak_remitter_id'] > 0) ? $params['kotak_remitter_id'] : 0;
        $txnAgentData['product_id'] = $params['product_id'];
        $txnAgentData['bank_id'] = (isset($params['bank_id']) && $params['bank_id'] > 0) ? $params['bank_id'] : 0;
        $txnAgentData['agent_funding_id'] = (isset($params['agent_funding_id']) && $params['agent_funding_id'] > 0) ? $params['agent_funding_id'] : 0;
        $txnAgentData['remittance_request_id'] = (isset($params['remittance_request_id']) && $params['remittance_request_id'] > 0) ? $params['remittance_request_id'] : 0;
        $txnAgentData['kotak_remittance_request_id'] = (isset($params['kotak_remittance_request_id']) && $params['kotak_remittance_request_id'] > 0) ? $params['kotak_remittance_request_id'] : 0;
        $txnAgentData['purse_master_id'] = (isset($params['purse_master_id']) && $params['purse_master_id'] > 0) ? $params['purse_master_id'] : 0;
        $txnAgentData['customer_purse_id'] = (isset($params['customer_purse_id']) && $params['customer_purse_id'] > 0) ? $params['customer_purse_id'] : 0;
        $txnAgentData['ip'] = $params['ip'];
        $txnAgentData['currency'] = CURRENCY_INR;
        $txnAgentData['amount'] = $params['amount'];
        $txnAgentData['mode'] = $params['mode'];
        $txnAgentData['txn_type'] = $params['txn_type'];
        $txnAgentData['txn_status'] = $params['txn_status'];
        $txnAgentData['remarks'] = $params['remarks'];
        $txnAgentData['date_created'] = $params['date'];
        $txnAgentData['is_virtual'] = (isset($params['is_virtual'])) ? $params['is_virtual'] : FLAG_NO;
       
        $this->_db->insert(DbTable::TABLE_TXN_AGENT,$txnAgentData);
    }
    
    
    /**
     * insertTxnAgentToAgent
     * Transaction Agent to Agent fund transfer
     * @param type $params
     * @throws Exception
     */
    public function insertTxnAgentToAgent($params) {
        $this->_db->beginTransaction();

        try {
            $txncode = new Txncode();
            if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used
                $ip = Util::getIP();
                $data['txn_code'] = $paramsTxnCode;
                $data['ip'] = empty($ip) ? '' : $this->formatIpAddress($ip);
                $data['currency'] = CURRENCY_INR;
                $data['amount'] = $params['amount'];
                $data['txn_type'] = $params['txn_type'];
                $data['txn_status'] = $params['txn_status'];
                $data['remarks'] = $params['remarks'];
                $data['agent_fund_request_id'] = (isset($params['agent_fund_request_id']) && $params['agent_fund_request_id'] > 0) ? $params['agent_fund_request_id'] : '0';
                $data['agent_funding_id'] = (isset($params['agent_funding_id']) && $params['agent_funding_id'] > 0) ? $params['agent_funding_id'] : '0';
                $data['date_created'] = new Zend_Db_Expr('NOW()');

                $data['agent_id'] = $params['agent_id'];
                $data['txn_agent_id'] = $params['txn_agent_id'];
                $data['mode'] = TXN_MODE_DR;
                //Deduct Agent Balance
                if($params['txn_type'] == TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL) {
                    $data['mode'] = TXN_MODE_CR;                    
                } 
                $this->_db->insert(DbTable::TABLE_TXN_AGENT, $data);
                
                
                $this->updateAgentBalance($data);

                unset($data['agent_id']);
                unset($data['txn_agent_id']);
                $data['txn_agent_id'] = $params['agent_id'];
                $data['agent_id'] = $params['txn_agent_id'];
                $data['mode'] = TXN_MODE_CR;
                if($params['txn_type'] == TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL) {
                    $data['mode'] = TXN_MODE_DR;                    
                }                 
                
                //Credit Agent Balance
                $this->_db->insert(DbTable::TABLE_TXN_AGENT, $data);
                 
                $this->updateAgentBalance($data);
                
                
                $this->agentFundTransfer($paramsTxnCode,$params);

            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code could not be generated at this time. Please try later.", Zend_Log::ERR);
                throw new Exception("Transaction Code could not be generated at this time. Please try later.");
            }

            $this->_db->commit();
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception($e->getMessage());
        }
    }    
    
    
    /**
     * updateAgentBalance
     * Update Agent Balance
     * @param type $params
     * @return type
     */
    private function updateAgentBalance($params) {
        $agentBalance = new AgentBalance();
        $info = $agentBalance->getAgentBalanceInfo($params['agent_id']);
        if(empty($info) || $info === FALSE) {
            //insert
            $insArr['agent_id'] = $params['agent_id'];
            if($params['mode'] == TXN_MODE_CR) {            
                $insArr['amount'] = $params['amount'];
            } else {
                $insArr['amount'] = $params['amount'] * -1;                
            }
            $insArr['block_amount'] = 0;
            $insArr['date_modified'] = new Zend_Db_Expr('NOW()');
            return $this->_db->insert(DbTable::TABLE_AGENT_BALANCE, $insArr);
        } else {
            if($params['mode'] == TXN_MODE_CR) {
                $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_modified' => new Zend_Db_Expr('NOW()'));
            } else {
                $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']), 'date_modified' => new Zend_Db_Expr('NOW()'));                
            }
            $where = "agent_id = '" . $params['agent_id'] . "'";
            return  $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);
        }
    }
    
    
    private function agentFundTransfer($txnCode, $params) {
            $agentFundindData['txn_code'] = $txnCode;
            $agentFundindData['agent_id'] = $params['agent_id'];
            $agentFundindData['txn_agent_id'] = $params['txn_agent_id'];
            $agentFundindData['amount'] = $params['amount'];
            $agentFundindData['txn_type'] = $params['txn_type'];
            $agentFundindData['status'] = STATUS_SUCCESS;
            $agentFundindData['date_created'] = new Zend_Db_Expr('NOW()');
            return $this->_db->insert(DbTable::TABLE_AGENT_FUND_TRANSFER, $agentFundindData);    
    }    
    
    public function successTxnRatManualAdj($params)
    {
        $this->_db->beginTransaction(); 
        
        try 
        { 
            if(strtolower($params['mode']) == strtolower(TXN_MODE_CR))
            {
                $params['remarks'] = '';
                $params['ip'] = '';//$this->formatIpAddress(Util::getIP()); // cron would not return ip so commented yet
                $params['date'] = new Zend_Db_Expr('NOW()');
                $params['txn_type'] = TXNTYPE_CREDIT_MANUAL_ADJUSTMENT;
                $params['txn_status'] = FLAG_SUCCESS;

                /********* Ops Dr txn entry *******/
                $paramsDr['mode'] = TXN_MODE_DR;
                $paramsDr['ops_id'] = TXN_OPS_ID;
                $paramsDr['txn_customer_master_id'] = $params['customer_master_id'];
                $opsData = array_merge($params, $paramsDr);
                $this->insertTxnOps($opsData);
                /********* Ops Dr txn entry over *******/

                /***** Customer Cr txn entry ***********/
                $paramsCr['mode'] = TXN_MODE_CR;
                $paramsCr['txn_ops_id'] = TXN_OPS_ID;
                $custData = array_merge($params, $paramsCr);
                $this->insertTxnRatCustomer($custData);
                /********* Customer Cr entry over *******/

                $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => $params['date']);
                $where = "id = '" . $params['customer_purse_id'] . "'";
                $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);
            } elseif(strtolower ($params['mode']) == strtolower(TXN_MODE_DR)) {

                $params['remarks'] = '';
                $params['ip'] = '';//$this->formatIpAddress(Util::getIP()); // cron would not return ip so commented yet
                $params['date'] = new Zend_Db_Expr('NOW()');
                $params['txn_type'] = TXNTYPE_DEBIT_MANUAL_ADJUSTMENT;
                $params['txn_status'] = FLAG_SUCCESS;

                /********* Ops Dr txn entry *******/
                $paramsDr['mode'] = TXN_MODE_CR;
                $paramsDr['ops_id'] = TXN_OPS_ID;
                $paramsDr['txn_customer_master_id'] = $params['customer_master_id'];
                $opsData = array_merge($params, $paramsDr);
                $this->insertTxnOps($opsData);
                /********* Ops Dr txn entry over *******/

                /***** Customer Cr txn entry ***********/
                $paramsCr['mode'] = TXN_MODE_DR;
                $paramsCr['txn_ops_id'] = TXN_OPS_ID;
                $custData = array_merge($params, $paramsCr);
                $this->insertTxnRatCustomer($custData);
                /********* Customer Cr entry over *******/

                $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']), 'date_updated' => $params['date']);
                $where = "id = '" . $params['customer_purse_id'] . "'";
                $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);                
            }
            
            $this->_db->commit();
            
            return TRUE;

        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage());
        }
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
    public function successTxnKotakCorporateCardLoad($params)
    {
        $this->_db->beginTransaction(); 
        
        try 
        { 
            $params['remarks'] = '';
            $params['ip'] = '';//$this->formatIpAddress(Util::getIP()); // cron would not return ip so commented yet
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_type'] = TXNTYPE_KOTAK_CORP_CORPORATE_LOAD;
            $params['txn_status'] = FLAG_SUCCESS;

            if($params['corporate_id'] > 0) {
                /********* Corporate Dr txn entry *******/
                $paramsDr['mode'] = TXN_MODE_DR;
                $paramsDr['corporate_id'] = $params['corporate_id'];
                $paramsDr['txn_customer_master_id'] = $params['customer_master_id'];
                $opsData = array_merge($params, $paramsDr);
                $this->insertTxnCorp($opsData);
                /********* Corporate Dr txn entry over *******/

                /***** Customer Cr txn entry ***********/
                $paramsCr['mode'] = TXN_MODE_CR;
                $paramsCr['txn_corporate_id'] = $params['corporate_id'];
                $custData = array_merge($params, $paramsCr);
                $this->insertTxnKotakCustomer($custData);
                /********* Customer Cr entry over *******/
                
                $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']), 'date_modified' => $params['date']);
                $where = "corporate_id = '" . $params['corporate_id'] . "'";
                $this->_db->update(DbTable::TABLE_CORPORATE_BALANCE, $updArr, $where);
            
            }
            else {
            
                /********* Ops Dr txn entry *******/
                $paramsDr['mode'] = TXN_MODE_DR;
                $paramsDr['ops_id'] = TXN_OPS_ID;
                $paramsDr['txn_customer_master_id'] = $params['customer_master_id'];
                $opsData = array_merge($params, $paramsDr);
                $this->insertTxnOps($opsData);
                /********* Ops Dr txn entry over *******/

                /***** Customer Cr txn entry ***********/
                $paramsCr['mode'] = TXN_MODE_CR;
                $paramsCr['txn_ops_id'] = TXN_OPS_ID;
                $custData = array_merge($params, $paramsCr);
                $this->insertTxnKotakCustomer($custData);
                /********* Customer Cr entry over *******/          
            
            }

            $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => $params['date']);
            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_KOTAK_CUSTOMER_PURSE, $updArr, $where);
            
            $this->_db->commit();
            
            return TRUE;

        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage());
        }
    }
    
    /*
     * entry in rat customer txn table
     */
    private function insertTxnKotakCustomer($params)
    {
        $txnData['txn_code'] = $params['txn_code'];
        $txnData['customer_master_id'] = $params['customer_master_id'];
        $txnData['txn_customer_master_id'] = (isset($params['txn_customer_master_id']) && $params['txn_customer_master_id'] > 0) ? $params['txn_customer_master_id'] : 0;
        $txnData['txn_agent_id'] = (isset($params['txn_agent_id']) && $params['txn_agent_id'] > 0) ? $params['txn_agent_id'] : 0;
        $txnData['txn_ops_id'] = (isset($params['txn_ops_id']) && $params['txn_ops_id'] > 0) ? $params['txn_ops_id'] : 0;
        $txnData['txn_corporate_id'] = (isset($params['txn_corporate_id']) && $params['txn_corporate_id'] > 0) ? $params['txn_corporate_id'] : 0;
        $txnData['product_id'] = $params['product_id'];
        $txnData['purse_master_id'] = $params['purse_master_id'];
        $txnData['customer_purse_id'] = (isset($params['customer_purse_id']) && $params['customer_purse_id'] > 0) ? $params['customer_purse_id'] : 0;
        $txnData['ip'] = $params['ip'];
        $txnData['currency'] = CURRENCY_INR;
        $txnData['amount'] = $params['amount'];
        $txnData['mode'] = $params['mode'];
        $txnData['txn_type'] = $params['txn_type'];
        $txnData['txn_status'] = $params['txn_status'];
        $txnData['remarks'] = $params['remarks'];
        $txnData['date_created'] = $params['date'];
        $this->_db->insert(DbTable::TABLE_KOTAK_TXN_CUSTOMER,$txnData);
    }
    
    /* 
     * $params['txn_code'] = txn code
     * $params['customer_master_id'] = customer master id
     * $params['product_id'] = product id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount     
     */
    public function successTxnBoiCorporateCardLoad($params)
    {
        $this->_db->beginTransaction(); 
        
        try 
        { 
            $params['remarks'] = '';
            $params['ip'] = '';//$this->formatIpAddress(Util::getIP()); // cron would not return ip so commented yet
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_type'] = TXNTYPE_BOI_CORP_CORPORATE_LOAD;
            $params['txn_status'] = FLAG_SUCCESS;

            /********* Ops Dr txn entry *******/
            $paramsDr['mode'] = TXN_MODE_DR;
            $paramsDr['ops_id'] = TXN_OPS_ID;
            $paramsDr['txn_customer_master_id'] = $params['customer_master_id'];
            $opsData = array_merge($params, $paramsDr);
            $this->insertTxnOps($opsData);
            /********* Ops Dr txn entry over *******/
            
            /***** Customer Cr txn entry ***********/
            $paramsCr['mode'] = TXN_MODE_CR;
            $paramsCr['txn_ops_id'] = TXN_OPS_ID;
            $custData = array_merge($params, $paramsCr);
            $this->insertTxnBoiCustomer($custData);
            /********* Customer Cr entry over *******/

            $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => $params['date']);
            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_BOI_CUSTOMER_PURSE, $updArr, $where);
            
            $this->_db->commit();
            
            return TRUE;

        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage());
        }
    }
    
    /*
     * entry in rat customer txn table
     */
    private function insertTxnBoiCustomer($params)
    {
        $txnData['txn_code'] = $params['txn_code'];
        $txnData['customer_master_id'] = $params['customer_master_id'];
        $txnData['txn_customer_master_id'] = (isset($params['txn_customer_master_id']) && $params['txn_customer_master_id'] > 0) ? $params['txn_customer_master_id'] : 0;
        $txnData['txn_agent_id'] = (isset($params['txn_agent_id']) && $params['txn_agent_id'] > 0) ? $params['txn_agent_id'] : 0;
        $txnData['txn_ops_id'] = (isset($params['txn_ops_id']) && $params['txn_ops_id'] > 0) ? $params['txn_ops_id'] : 0;
        $txnData['product_id'] = $params['product_id'];
        $txnData['purse_master_id'] = $params['purse_master_id'];
        $txnData['customer_purse_id'] = (isset($params['customer_purse_id']) && $params['customer_purse_id'] > 0) ? $params['customer_purse_id'] : 0;
        $txnData['ip'] = $params['ip'];
        $txnData['currency'] = CURRENCY_INR;
        $txnData['amount'] = $params['amount'];
        $txnData['mode'] = $params['mode'];
        $txnData['txn_type'] = $params['txn_type'];
        $txnData['txn_status'] = $params['txn_status'];
        $txnData['remarks'] = $params['remarks'];
        $txnData['date_created'] = $params['date'];
        $this->_db->insert(DbTable::TABLE_BOI_TXN_CUSTOMER,$txnData);
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
     public function reversalTxnBoiCardLoad($params)
    {
         $this->_db->beginTransaction(); 
        
        try 
        { 
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['remarks'] = '';
            $params['ip'] = '';//$this->formatIpAddress(Util::getIP()); // cron would not return ip so commented yet
            $params['txn_status'] = FLAG_SUCCESS;
                
            /***** Customer Dr txn entry ***********/
           $paramsDr['mode'] = TXN_MODE_DR;
           $paramsDr['txn_ops_id'] = TXN_OPS_ID;
           $custData = array_merge($params, $paramsDr);
           $this->insertTxnBoiCustomer($custData);
           /********* Customer Dr entry over *******/

           /********* Ops Cr txn entry *******/
           $paramsCr['mode'] = TXN_MODE_CR;
           $paramsCr['ops_id'] = TXN_OPS_ID;
           $paramsCr['txn_customer_master_id'] = $params['customer_master_id'];
           $opsData = array_merge($params, $paramsCr);
           $this->insertTxnOps($opsData);
           /********* Ops Cr txn entry over *******/
           
            
            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']), 
                            'date_updated' => $params['date']);
            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_BOI_CUSTOMER_PURSE, $updArr, $where);

            $this->_db->commit();
            
            return true;

        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage());
        }
    }
    
    
    public function insertTxnOpsToCorporate($params) {
        $this->_db->beginTransaction();

        try {
            $txncode = new Txncode(); 
            if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used
                $ip = Util::getIP();
                $data['txn_code'] = $paramsTxnCode;
                $data['ip'] = empty($ip) ? '' : $this->formatIpAddress($ip);
                $data['currency'] = CURRENCY_INR;
                $data['amount'] = $params['amount'];
                
                $data['txn_type'] = $params['txn_type'];
                $data['txn_status'] = $params['txn_status'];
                $data['remarks'] = $params['remarks'];
                $data['corporate_fund_request_id'] = (isset($params['corporate_fund_request_id']) && $params['corporate_fund_request_id'] > 0) ? $params['corporate_fund_request_id'] : '0';
                $data['corporate_funding_id'] = (isset($params['corporate_funding_id']) && $params['corporate_funding_id'] > 0) ? $params['corporate_funding_id'] : '0';
                $data['date_created'] = new Zend_Db_Expr('NOW()');

                $data['ops_id'] = $params['ops_id'];
                $data['txn_corporate_id'] = $params['corporate_id'];
                $data['mode'] = TXN_MODE_DR;
                $this->_db->insert(DbTable::TABLE_TXN_OPS, $data);


                unset($data['ops_id']);
                unset($data['txn_agent_id']);
                $data['corporate_id'] = $params['corporate_id'];
                $data['txn_ops_id'] = $params['ops_id'];
                $data['mode'] = TXN_MODE_CR;
             
                
                $this->_db->insert(DbTable::TABLE_CORPORATE_TXN, $data);

                if ($params['txn_status'] == FLAG_SUCCESS) {
                    $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_modified' => new Zend_Db_Expr('NOW()'));
                    $where = "corporate_id = '" . $params['corporate_id'] . "'";
                    $upd = $this->_db->update(DbTable::TABLE_CORPORATE_BALANCE, $updArr, $where);
                    if (!$upd) {
                        //insert
                        $insArr['corporate_id'] = $params['corporate_id'];
                        $insArr['amount'] = $params['amount'];
                        $insArr['block_amount'] = 0;
                        $insArr['date_modified'] = new Zend_Db_Expr('NOW()');
                        $this->_db->insert(DbTable::TABLE_CORPORATE_BALANCE, $insArr);
                    }
                }
            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code could not be generated at this time. Please try later.", Zend_Log::ALERT);
                throw new Exception("Transaction Code could not be generated at this time. Please try later.");
            }

            $this->_db->commit();
        } catch (Exception $e) { 
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }
    
    
    /**
     * insertTxnCorporateToCorporate
     * Transaction Corporate to Corporate fund transfer
     * @param type $params
     * @throws Exception
     */
    public function insertTxnCorporateToCorporate($params) {
        $this->_db->beginTransaction();

        try {
            $txncode = new Txncode();
            if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used
                $ip = Util::getIP();
                $data['txn_code'] = $paramsTxnCode;
                $data['ip'] = empty($ip) ? '' : $this->formatIpAddress($ip);
                $data['currency'] = CURRENCY_INR;
                $data['amount'] = $params['amount'];
                $data['txn_type'] = $params['txn_type'];
                $data['txn_status'] = $params['txn_status'];
                $data['remarks'] = $params['remarks'];
                $data['corporate_fund_request_id'] = (isset($params['corporate_fund_request_id']) && $params['corporate_fund_request_id'] > 0) ? $params['corporate_fund_request_id'] : '0';
                $data['corporate_funding_id'] = (isset($params['corporate_funding_id']) && $params['corporate_funding_id'] > 0) ? $params['corporate_funding_id'] : '0';
                $data['date_created'] = new Zend_Db_Expr('NOW()');

                $data['corporate_id'] = $params['corporate_id'];
                $data['txn_corporate_id'] = $params['txn_corporate_id'];
                $data['mode'] = TXN_MODE_DR;
                //Deduct Agent Balance
                if($params['txn_type'] == TXNTYPE_CORPORATE_TOCORPORATE_FUND_REVERSAL) {
                    $data['mode'] = TXN_MODE_CR;                    
                } 
                $this->_db->insert(DbTable::TABLE_CORPORATE_TXN, $data);
                
                
                $this->updateCorporateBalance($data);

                unset($data['corporate_id']);
                unset($data['txn_corporate_id']);
                $data['txn_corporate_id'] = $params['corporate_id'];
                $data['corporate_id'] = $params['txn_corporate_id'];
                $data['mode'] = TXN_MODE_CR;
                if($params['txn_type'] == TXNTYPE_CORPORATE_TOCORPORATE_FUND_REVERSAL) {
                    $data['mode'] = TXN_MODE_DR;                    
                }                 
                
                //Credit Agent Balance
                $this->_db->insert(DbTable::TABLE_CORPORATE_TXN, $data);
                 
                $this->updateCorporateBalance($data);
                
                
                $this->corporateFundTransfer($paramsTxnCode,$params);

            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code could not be generated at this time. Please try later.", Zend_Log::ERR);
                throw new Exception("Transaction Code could not be generated at this time. Please try later.");
            }

            $this->_db->commit();
        } catch (Exception $e) {
            print_r($e);
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception($e->getMessage());
        }
    }
    
    /**
     * updateCorporateBalance
     * Update Corporate Balance
     * @param type $params
     * @return type
     */
    private function updateCorporateBalance($params) {
        $agentBalance = new CorporateBalance();
        $info = $agentBalance->getCorporateBalanceInfo($params['corporate_id']);
        if(empty($info) || $info === FALSE) {
            //insert
            $insArr['corporate_id'] = $params['corporate_id'];
            if($params['mode'] == TXN_MODE_CR) {            
                $insArr['amount'] = $params['amount'];
            } else {
                $insArr['amount'] = $params['amount'] * -1;                
            }
            $insArr['block_amount'] = 0;
            $insArr['date_modified'] = new Zend_Db_Expr('NOW()');
            return $this->_db->insert(DbTable::TABLE_CORPORATE_BALANCE, $insArr);
        } else {
            if($params['mode'] == TXN_MODE_CR) {
                $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_modified' => new Zend_Db_Expr('NOW()'));
            } else {
                $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']), 'date_modified' => new Zend_Db_Expr('NOW()'));                
            }
            $where = "corporate_id = '" . $params['corporate_id'] . "'";
            return  $this->_db->update(DbTable::TABLE_CORPORATE_BALANCE, $updArr, $where);
        }
    }
    
     private function corporateFundTransfer($txnCode, $params) {
            $agentFundindData['txn_code'] = $txnCode;
            $agentFundindData['corporate_id'] = $params['corporate_id'];
            $agentFundindData['txn_corporate_id'] = $params['txn_corporate_id'];
            $agentFundindData['amount'] = $params['amount'];
            $agentFundindData['txn_type'] = $params['txn_type'];
            $agentFundindData['status'] = STATUS_SUCCESS;
            $agentFundindData['date_created'] = new Zend_Db_Expr('NOW()');
            return $this->_db->insert(DbTable::TABLE_CORPORATE_FUND_TRANSFER, $agentFundindData);    
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
    public function successTxnDoCorporateCardLoad($params)
    {
        $this->_db->beginTransaction(); 
        
        try 
        { 
            $params['remarks'] = '';
            $params['ip'] = '';//$this->formatIpAddress(Util::getIP()); // cron would not return ip so commented yet
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_type'] = TXNTYPE_CARD_RELOAD;
            $params['txn_status'] = FLAG_SUCCESS;

          
            
            /********* Corporate Dr txn entry *******/
            $paramsDr['mode'] = TXN_MODE_DR;
            $paramsDr['corporate_id'] = $params['corporate_id'];
            $paramsDr['txn_customer_master_id'] = $params['customer_master_id'];
            $opsData = array_merge($params, $paramsDr);
            $this->insertTxnCorp($opsData);
            /********* Ops Dr txn entry over *******/
            
            /***** Customer Cr txn entry ***********/
            $paramsCr['mode'] = TXN_MODE_CR;
            $paramsCr['txn_corporate_id'] = $params['corporate_id'];
            $custData = array_merge($params, $paramsCr);
            $this->insertTxnRatCustomer($custData);
            /********* Customer Cr entry over *******/

            $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => $params['date']);
            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);
            // Update Corporate balance
            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']), 'date_modified' => $params['date']);
            $where = "corporate_id = '" . $params['corporate_id'] . "'";
            $this->_db->update(DbTable::TABLE_CORPORATE_BALANCE, $updArr, $where);
            $this->_db->commit();
            
            return TRUE;

        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage());
        }
    }
 	 	 	 
     private function insertTxnCorp($params) {
         
        $txnCorpData['txn_code'] = $params['txn_code'];
        $txnCorpData['corporate_id'] = (isset($params['corporate_id']) && $params['corporate_id'] > 0) ? $params['corporate_id'] : 0;
        $txnCorpData['txn_customer_master_id'] = (isset($params['txn_customer_master_id']) && $params['txn_customer_master_id'] > 0) ? $params['txn_customer_master_id'] : 0;
        $txnCorpData['txn_corporate_id'] = (isset($params['txn_corporate_id']) && $params['txn_corporate_id'] > 0) ? $params['txn_corporate_id'] : 0;
        $txnCorpData['txn_ops_id'] = (isset($params['txn_ops_id']) && $params['txn_ops_id'] > 0) ? $params['txn_ops_id'] : 0;
        $txnCorpData['corporate_fund_request_id'] = (isset($params['corporate_fund_request_id']) && $params['corporate_fund_request_id'] > 0) ? $params['corporate_fund_request_id'] : 0;
        $txnCorpData['corporate_funding_id'] = (isset($params['corporate_funding_id']) && $params['corporate_funding_id'] > 0) ? $params['corporate_funding_id'] : 0;
        $txnCorpData['purse_master_id'] = (isset($params['purse_master_id']) && $params['purse_master_id'] > 0) ? $params['purse_master_id'] : 0;
        $txnCorpData['customer_purse_id'] = (isset($params['customer_purse_id']) && $params['customer_purse_id'] > 0) ? $params['customer_purse_id'] : 0;
        $txnCorpData['product_id'] = $params['product_id'];
        $txnCorpData['ip'] = $params['ip'];
        $txnCorpData['currency'] = CURRENCY_INR;
        $txnCorpData['amount'] = $params['amount'];
        $txnCorpData['mode'] = $params['mode'];
        $txnCorpData['txn_type'] = $params['txn_type'];
        $txnCorpData['txn_status'] = (isset($params['txn_status']) && $params['txn_status'] != '') ? $params['txn_status'] : FLAG_SUCCESS;
        $txnCorpData['remarks'] = $params['remarks'];
        $txnCorpData['date_created'] = $params['date'];
        $this->_db->insert(DbTable::TABLE_CORPORATE_TXN, $txnCorpData);
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
    public function successTxnCorpKotakCorporateCardLoad($params)
    {
        $this->_db->beginTransaction(); 
        
        try 
        { 
            $params['remarks'] = '';
            $params['ip'] = '';//$this->formatIpAddress(Util::getIP()); // cron would not return ip so commented yet
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_type'] = TXNTYPE_KOTAK_CORP_CORPORATE_LOAD;
            $params['txn_status'] = FLAG_SUCCESS;

            /********* Ops Dr txn entry *******/
            $paramsDr['mode'] = TXN_MODE_DR;
            $paramsDr['corporate_id'] = $params['corporate_id'];
            $paramsDr['txn_customer_master_id'] = $params['customer_master_id'];
            $opsData = array_merge($params, $paramsDr);
            $this->insertTxnCorp($opsData);
            /********* Ops Dr txn entry over *******/
            
            /***** Customer Cr txn entry ***********/
            $paramsCr['mode'] = TXN_MODE_CR;
            $paramsCr['txn_corporate_id'] = $params['corporate_id'];
            $custData = array_merge($params, $paramsCr);
            $this->insertTxnKotakCustomer($custData);
            /********* Customer Cr entry over *******/

            $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => $params['date']);
            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_KOTAK_CUSTOMER_PURSE, $updArr, $where);
            
             // Update Corporate balance
            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']), 'date_modified' => $params['date']);
            $where = "corporate_id = '" . $params['corporate_id'] . "'";
            $this->_db->update(DbTable::TABLE_CORPORATE_BALANCE, $updArr, $where);
            
            $this->_db->commit();
            
            return TRUE;

        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage());
        }
    }
    
    
    /* 
     * for fee amount
     * for service tax amount
     */

    public function insertRatnakarTxnRemitterRegnFee($params) {
        $this->_db->beginTransaction();

        try {
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['remit_request_id'] = 0;
            $params['ratnakar_remittance_request_id'] = 0;
            $params['txn_status'] = FLAG_SUCCESS;

            /* Amount blocked in agent balance */
            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']),
                'block_amount' => new Zend_Db_Expr("block_amount + " . $params['amount']),
                'date_modified' => $params['date']);
            $where = "agent_id = '" . $params['agent_id'] . "'";
            $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);

            $txncode = new Txncode();
            if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used
            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code for ratnakar remitter regn fee could not be generated at this time. ", Zend_Log::ALERT);
                throw new Exception(ErrorCodes::ERROR_UNABLE_GENERATE_TXN_CODE_FOR_REMITTER_REG_MSG, ErrorCodes::ERROR_UNABLE_GENERATE_TXN_CODE_FOR_REMITTER_REG_CODE); 
              
            }

            /* For Fee Amount */
            if ($params['fee_amt'] > 0) {
                $paramsFee['txn_code'] = $paramsTxnCode;
                $paramsFee['amount'] = $params['fee_amt'];
                $paramsFee['txn_type'] = TXNTYPE_REMITTER_REGISTRATION;

                /*                 * ******* Agent txn entry ****** */
                $agentFeeDr['mode'] = TXN_MODE_DR;
                $agentFeeDr['ratnakar_remitter_id'] = $params['remitter_id'];
                $agentData = array_merge($params, $paramsFee, $agentFeeDr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */

                /*                 * *** Remitter Cr txn entry ********** */
                $remitterFeeCr['mode'] = TXN_MODE_CR;
                $remitterFeeCr['txn_agent_id'] = $params['agent_id'];
                $remitterDataCr = array_merge($params, $paramsFee, $remitterFeeCr);
                $this->insertRatnakarRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterFeeDr['mode'] = TXN_MODE_DR;
                $remitterFeeDr['txn_ops_id'] = FEE_AC_ID;
                $remitterDataDr = array_merge($params, $paramsFee, $remitterFeeDr);
                $this->insertRatnakarRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Ops 2 cr txn entry ****** */
                $opsFeeCr['mode'] = TXN_MODE_CR;
                $opsFeeCr['ops_id'] = FEE_AC_ID;
                $opsFeeCr['ratnakar_remitter_id'] = $params['remitter_id'];
                $opsData = array_merge($params, $paramsFee, $opsFeeCr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops 2 cr txn entry over ****** */
            }
            /*             * ** Txns for Remit Fee Amount ends ******* */

            /* For Service Tax Amount */
            if ($params['service_tax'] > 0) {
                $paramsSt['txn_code'] = $paramsTxnCode;
                $paramsSt['amount'] = $params['service_tax'];
                $paramsSt['txn_type'] = TXNTYPE_REMITTANCE_SERVICE_TAX;

                /*                 * ******* Agent txn entry ****** */
                $agentStDr['mode'] = TXN_MODE_DR;
                $agentStDr['ratnakar_remitter_id'] = $params['remitter_id'];
                $agentData = array_merge($params, $paramsSt, $agentStDr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */

                /*                 * *** Remitter Cr txn entry ********** */
                $remitterStCr['mode'] = TXN_MODE_CR;
                $remitterStCr['txn_agent_id'] = $params['agent_id'];
                $remitterDataCr = array_merge($params, $paramsSt, $remitterStCr);
                $this->insertRatnakarRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterStDr['mode'] = TXN_MODE_DR;
                $remitterStDr['txn_ops_id'] = SERVICE_TAX_AC_ID;
                $remitterDataDr = array_merge($params, $paramsSt, $remitterStDr);
                $this->insertRatnakarRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Ops 3 Cr txn entry ****** */
                $opsStCr['mode'] = TXN_MODE_CR;
                $opsStCr['ops_id'] = SERVICE_TAX_AC_ID;
                $opsStCr['ratnakar_remitter_id'] = $params['remitter_id'];
                $opsData = array_merge($params, $paramsSt, $opsStCr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops 3 Cr txn entry over ****** */
            }
            /*             * ** Txns for Remit Service Tax Amount ends ******* */

            /* agent balance updated */
            $updArr = array('block_amount' => new Zend_Db_Expr("block_amount - " . $params['amount']), 'date_modified' => $params['date']);
            $where = "agent_id = '" . $params['agent_id'] . "'";
            $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);

            $this->_db->commit();

            return $paramsTxnCode;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            $code = $e->getCode();
            $code = (empty($code)) ? ErrorCodes::ERROR_UNABLE_GENERATE_TXN_CODE_FAILURE_CODE : $code;
            $message = $e->getMessage();
            $message = (empty($message)) ? ErrorCodes::ERROR_UNABLE_GENERATE_TXN_CODE_FAILURE_MSG : $message;
            throw new Exception($e->getMessage());
        }
    }
    
    private function insertRatnakarRemitTxnRemitter($params) {
        $txnRemitData['txn_code'] = $params['txn_code'];
        $txnRemitData['remitter_id'] = $params['remitter_id'];
        $txnRemitData['txn_agent_id'] = (isset($params['txn_agent_id']) && $params['txn_agent_id'] > 0) ? $params['txn_agent_id'] : 0;
        $txnRemitData['txn_ops_id'] = (isset($params['txn_ops_id']) && $params['txn_ops_id'] > 0) ? $params['txn_ops_id'] : 0;
        $txnRemitData['product_id'] = $params['product_id'];
        $txnRemitData['remittance_request_id'] = (isset($params['remit_request_id']) && $params['remit_request_id'] > 0) ? $params['remit_request_id'] : 0;
        $txnRemitData['ip'] = $params['ip'];
        $txnRemitData['currency'] = CURRENCY_INR;
        $txnRemitData['amount'] = $params['amount'];
        $txnRemitData['mode'] = $params['mode'];
        $txnRemitData['txn_type'] = $params['txn_type'];
        $txnRemitData['txn_status'] = (isset($params['txn_status']) && $params['txn_status'] != '') ? $params['txn_status'] : FLAG_SUCCESS;
        $txnRemitData['remarks'] = $params['remarks'];
        $txnRemitData['date_created'] = $params['date'];
        $this->_db->insert(DbTable::TABLE_RATNAKAR_TXN_REMITTER, $txnRemitData);
    }
    
    
    
    /*
     * for remit amount
     * for fee amount
     * for service tax amount
     */

    public function initiateRatnakarTxnRemit($params) {
        $this->_db->beginTransaction();

        try {
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_status'] = FLAG_SUCCESS;

            /* Amount blocked in agent balance */
            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['total_amount']),
                'block_amount' => new Zend_Db_Expr("block_amount + " . $params['total_amount']),
                'date_modified' => $params['date']);
            $where = "agent_id = '" . $params['agent_id'] . "'";
            $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);

            $txncode = new Txncode();
            if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used
            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code for ratnakar remittance amount could not be generated at this time. ", Zend_Log::ALERT);
                throw new Exception("Transaction Code for ratnakar remittance amount could not be generated at this time. Please try later.");
            }
            /* For Remit Amount */
            if ($params['amount'] > 0) {
                $paramsRemit['txn_code'] = $paramsTxnCode;
                $paramsRemit['amount'] = $params['amount'];
                $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE;

                /*                 * ******* Agent txn entry ****** */
                $agentRemitDr['mode'] = TXN_MODE_DR;
                $agentRemitDr['ratnakar_remitter_id'] = $params['remitter_id'];
                $agentRemitDr['ratnakar_remittance_request_id'] = $params['remit_request_id'];
                $agentRemitDr['remitter_id'] = 0;
                $agentRemitDr['remit_request_id'] = 0;
                $agentData = array_merge($params, $paramsRemit, $agentRemitDr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */

                /*                 * *** Remitter Cr txn entry ********** */
                $remitterRemitCr['mode'] = TXN_MODE_CR;
                $remitterRemitCr['txn_agent_id'] = $params['agent_id'];
                $remitterDataCr = array_merge($params, $paramsRemit, $remitterRemitCr);
                $this->insertRatnakarRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterRemitDr['mode'] = TXN_MODE_DR;
                $remitterRemitDr['txn_ops_id'] = TXN_OPS_ID;
                $remitterDataDr = array_merge($params, $paramsRemit, $remitterRemitDr);
                $this->insertRatnakarRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Ops txn entry ****** */
                $opsRemitCr['mode'] = TXN_MODE_CR;
                $opsRemitCr['ops_id'] = TXN_OPS_ID;
                $opsRemitCr['ratnakar_remitter_id'] = $params['remitter_id'];
                $opsRemitCr['ratnakar_remittance_request_id'] = $params['remit_request_id'];
                $opsRemitCr['remitter_id'] = 0;
                $opsRemitCr['remit_request_id'] = 0;
                $opsData = array_merge($params, $paramsRemit, $opsRemitCr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops txn entry over ****** */
            }
            /*             * ** Txns for Remit Data ends ******* */

            /* For Fee Amount */
            if ($params['fee_amt'] > 0) {
                $paramsFee['txn_code'] = $paramsTxnCode;
                $paramsFee['amount'] = $params['fee_amt'];
                $paramsFee['txn_type'] = TXNTYPE_REMITTANCE_FEE;

                /*                 * ******* Agent txn entry ****** */
                $agentFeeDr['mode'] = TXN_MODE_DR;
                $agentFeeDr['ratnakar_remitter_id'] = $params['remitter_id'];
                $agentFeeDr['ratnakar_remittance_request_id'] = $params['remit_request_id'];
                $agentFeeDr['remitter_id'] = 0;
                $agentFeeDr['remit_request_id'] = 0;
                $agentData = array_merge($params, $paramsFee, $agentFeeDr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */

                /*                 * *** Remitter Cr txn entry ********** */
                $remitterFeeCr['mode'] = TXN_MODE_CR;
                $remitterFeeCr['txn_agent_id'] = $params['agent_id'];
                $remitterDataCr = array_merge($params, $paramsFee, $remitterFeeCr);
                $this->insertRatnakarRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterFeeDr['mode'] = TXN_MODE_DR;
                $remitterFeeDr['txn_ops_id'] = FEE_AC_ID;
                $remitterDataDr = array_merge($params, $paramsFee, $remitterFeeDr);
                $this->insertRatnakarRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Ops 2 Cr txn entry ****** */
                $opsFeeCr['mode'] = TXN_MODE_CR;
                $opsFeeCr['ops_id'] = FEE_AC_ID;
                $opsFeeCr['ratnakar_remitter_id'] = $params['remitter_id'];
                $opsFeeCr['ratnakar_remittance_request_id'] = $params['remit_request_id'];
                $opsFeeCr['remitter_id'] = 0;
                $opsFeeCr['remit_request_id'] = 0;
                $opsData = array_merge($params, $paramsFee, $opsFeeCr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops 2 Cr txn entry over ****** */
            }
            /*             * ** Txns for Remit Fee Amount ends ******* */

            /* For Service Tax Amount */
            if ($params['service_tax'] > 0) {
                $paramsSt['txn_code'] = $paramsTxnCode;
                $paramsSt['amount'] = $params['service_tax'];
                $paramsSt['txn_type'] = TXNTYPE_REMITTANCE_SERVICE_TAX;

                /*                 * ******* Agent txn entry ****** */
                $agentStDr['mode'] = TXN_MODE_DR;
                $agentStDr['ratnakar_remitter_id'] = $params['remitter_id'];
                $agentStDr['ratnakar_remittance_request_id'] = $params['remit_request_id'];
                $agentStDr['remitter_id'] = 0;
                $agentStDr['remit_request_id'] = 0;
                $agentData = array_merge($params, $paramsSt, $agentStDr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */

                /*                 * *** Remitter Cr txn entry ********** */
                $remitterStCr['mode'] = TXN_MODE_CR;
                $remitterStCr['txn_agent_id'] = $params['agent_id'];
                $remitterDataCr = array_merge($params, $paramsSt, $remitterStCr);
                $this->insertRatnakarRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterStDr['mode'] = TXN_MODE_DR;
                $remitterStDr['txn_ops_id'] = SERVICE_TAX_AC_ID;
                $remitterDataDr = array_merge($params, $paramsSt, $remitterStDr);
                $this->insertRatnakarRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Ops 3 Cr txn entry ****** */
                $opsStCr['mode'] = TXN_MODE_CR;
                $opsStCr['ops_id'] = SERVICE_TAX_AC_ID;
                $opsStCr['ratnakar_remitter_id'] = $params['remitter_id'];
                $opsStCr['ratnakar_remittance_request_id'] = $params['remit_request_id'];
                $opsStCr['remitter_id'] = 0;
                $opsStCr['remit_request_id'] = 0;
                $opsData = array_merge($params, $paramsSt, $opsStCr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops 3 Cr txn entry over ****** */
            }
            /*             * ** Txns for Remit Service Tax Amount ends ******* */

            /* agent balance updated */
            $updArr = array('block_amount' => new Zend_Db_Expr("block_amount - " . $params['total_amount']),
                'date_modified' => $params['date']);
            $where = "agent_id = '" . $params['agent_id'] . "'";
            $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);

            $this->_db->commit();


            return $paramsTxnCode;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }
    
    private function calculateCommission($agentId, $txn_code){
        $commissionObject = new CommissionReport();
        error_log('calculating commission for agentId: '. $agentId.' txn_code: '.$txn_code.' ');
        $curdate = date("Y-m-d");
        $param=array();
        $param['agent_id'] = $agentId;
        $param['from'] = $curdate;
        $param['to'] = $curdate;
        $param['txn_code'] = $txn_code;

        $commissionArray = $commissionObject->calculateCommission($param);

        //error_log($commissionArray);
        error_log($commissionArray[0]['comm_amount']);

    }  
    public function remitRatnakarTxnSuccess($params) {
        $this->_db->beginTransaction();

        try {
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_status'] = FLAG_SUCCESS;

            /* For Remit Amount */
            if ($params['amount'] > 0) {
                $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE;

                /*                 * *** Beneficiary Cr txn entry ********** */
                $paramsRemitCr['mode'] = TXN_MODE_CR;
                $paramsRemitCr['txn_ops_id'] = TXN_OPS_ID;
                $remitterData = array_merge($params, $paramsRemit, $paramsRemitCr);
                $this->insertRatnakarRemitTxnBeneficiary($remitterData);
                /*                 * ******* Beneficiary Cr entry over ****** */

                /*                 * ******* Ops Dr txn entry ****** */
                $paramsRemitDr['mode'] = TXN_MODE_DR;
                $paramsRemitDr['ratnakar_beneficiary_id'] = $params['beneficiary_id'];
                $paramsRemitDr['ops_id'] = TXN_OPS_ID;
                $params['ratnakar_remittance_request_id'] = $params['remit_request_id'];
                $paramsRemitDr['remit_request_id'] = 0;
                $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops Dr txn entry over ****** */
            }
            /*             * ** Txns for Remit Data ends ******* */

            $this->_db->commit();

            return true;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }
    
    private function insertRatnakarRemitTxnBeneficiary($params) {
        $txnRemitData['txn_code'] = $params['txn_code'];
        $txnRemitData['beneficiary_id'] = $params['beneficiary_id'];
        $txnRemitData['txn_ops_id'] = (isset($params['txn_ops_id']) && $params['txn_ops_id'] > 0) ? $params['txn_ops_id'] : 0;
        ;
        $txnRemitData['product_id'] = $params['product_id'];
        $txnRemitData['remittance_request_id'] = $params['remit_request_id'];
        $txnRemitData['ip'] = $params['ip'];
        $txnRemitData['currency'] = CURRENCY_INR;
        $txnRemitData['amount'] = $params['amount'];
        $txnRemitData['mode'] = $params['mode'];
        $txnRemitData['txn_type'] = $params['txn_type'];
        $txnRemitData['txn_status'] = (isset($params['txn_status']) && $params['txn_status'] != '') ? $params['txn_status'] : FLAG_SUCCESS;
        $txnRemitData['remarks'] = $params['remarks'];
        $txnRemitData['date_created'] = $params['date'];

        $this->_db->insert(DbTable::TABLE_RATNAKAR_TXN_BENEFICIARY, $txnRemitData);
    }
    
    /*
     * for refund amount
     * for fee amount
     * for service tax amount
     * for reversal fee amount
     * for reversal service tax amount
     */

    public function remitRatnakarTxnRefund($params) {
        $this->_db->beginTransaction();

        try {
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_status'] = FLAG_SUCCESS;

            $txncode = new Txncode();
            if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used
            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code for refund amount could not be generated at this time. Please try later.", Zend_Log::ALERT);
		throw new Exception(ErrorCodes::ERROR_EDIGITAL_TXNCODE_NOT_GENERATED_MSG, ErrorCodes::ERROR_EDIGITAL_TXNCODE_NOT_GENERATED_CODE); 	
	    }
            /* For Total Amount */
            if ($params['total_amount'] > 0) {
                $paramsRemit['txn_code'] = $paramsTxnCode;
                $paramsRemit['amount'] = $params['total_amount'];
                $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE_REFUND;

                /*                 * *** Ops Dr txn entry ********** */
                $paramsRemitDr['mode'] = TXN_MODE_DR;
                $paramsRemitDr['ops_id'] = SUSPENSE_AC_ID;
                $paramsRemitDr['ratnakar_remittance_request_id'] = $params['remit_request_id'];
                $paramsRemitDr['remit_request_id'] = 0;
                $paramsRemitDr['ratnakar_remitter_id'] = $params['remitter_id'];
                $paramsRemitDr['txn_remitter_id'] = 0;
                $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops Dr entry over ****** */

                /*                 * ******* Remitter Cr txn entry ****** */
                $paramsRemitCr['mode'] = TXN_MODE_CR;
                $paramsRemitCr['txn_ops_id'] = SUSPENSE_AC_ID;
                $remitterDataCr = array_merge($params, $paramsRemit, $paramsRemitCr);
                $this->insertRatnakarRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterRemitDr['mode'] = TXN_MODE_DR;
                $remitterRemitDr['txn_agent_id'] = $params['agent_id'];
                $remitterDataDr = array_merge($params, $paramsRemit, $remitterRemitDr);
                $this->insertRatnakarRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Agent txn entry ****** */
                $agentRemitCr['mode'] = TXN_MODE_CR;
                $agentRemitCr['ratnakar_remittance_request_id'] = $params['remit_request_id'];
                $agentRemitCr['remit_request_id'] = 0;
                $agentRemitCr['ratnakar_remitter_id'] = $params['remitter_id'];
                $agentRemitCr['txn_remitter_id'] = 0;
                $agentData = array_merge($params, $paramsRemit, $agentRemitCr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */
            }
            /*             * ** Txns for Total Amount ends ******* */

            /* For Reversal Fee Amount */
            if ($params['reversal_fee_amt'] > 0) {
                $paramsRemit['txn_code'] = $paramsTxnCode;
                $paramsRemit['amount'] = $params['reversal_fee_amt'];
                $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_REFUND_FEE;

                /*                 * *** Ops Dr txn entry ********** */
                $paramsRemitDr['mode'] = TXN_MODE_DR;
                $paramsRemitDr['ops_id'] = SUSPENSE_AC_ID;
                $paramsRemitDr['ratnakar_remittance_request_id'] = $params['remit_request_id'];
                $paramsRemitDr['remit_request_id'] = 0;
                $paramsRemitDr['ratnakar_remitter_id'] = $params['remitter_id'];
                $paramsRemitDr['txn_remitter_id'] = 0;
                $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops Dr entry over ****** */

                /*                 * ******* Remitter Cr txn entry ****** */
                $paramsRemitCr['mode'] = TXN_MODE_CR;
                $paramsRemitCr['txn_ops_id'] = SUSPENSE_AC_ID;
                $remitterDataCr = array_merge($params, $paramsRemit, $paramsRemitCr);
                $this->insertRatnakarRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterRemitDr['mode'] = TXN_MODE_DR;
                $remitterRemitDr['txn_agent_id'] = $params['agent_id'];
                $remitterDataDr = array_merge($params, $paramsRemit, $remitterRemitDr);
                $this->insertRatnakarRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Agent txn entry ****** */
                $agentRemitCr['mode'] = TXN_MODE_CR;
                $agentRemitCr['ratnakar_remittance_request_id'] = $params['remit_request_id'];
                $agentRemitCr['remit_request_id'] = 0;
                $agentRemitCr['ratnakar_remitter_id'] = $params['remitter_id'];
                $agentRemitCr['txn_remitter_id'] = 0;
                $agentData = array_merge($params, $paramsRemit, $agentRemitCr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */
            }
            /*             * ** Txns for Reversal Fee Amount ends ******* */

            /* For Reversal Service Tax Amount */
            if ($params['reversal_service_tax'] > 0) {
                $paramsRemit['txn_code'] = $paramsTxnCode;
                $paramsRemit['amount'] = $params['reversal_service_tax'];
                $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_SERVICE_TAX;

                /*                 * *** Ops Dr txn entry ********** */
                $paramsRemitDr['mode'] = TXN_MODE_DR;
                $paramsRemitDr['ops_id'] = SUSPENSE_AC_ID;
                $paramsRemitDr['ratnakar_remittance_request_id'] = $params['remit_request_id'];
                $paramsRemitDr['remit_request_id'] = 0;
                $paramsRemitDr['ratnakar_remitter_id'] = $params['remitter_id'];
                $paramsRemitDr['txn_remitter_id'] = 0;
                $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops Dr entry over ****** */

                /*                 * ******* Remitter Cr txn entry ****** */
                $paramsRemitCr['mode'] = TXN_MODE_CR;
                $paramsRemitCr['txn_ops_id'] = SUSPENSE_AC_ID;
                $remitterDataCr = array_merge($params, $paramsRemit, $paramsRemitCr);
                $this->insertRatnakarRemitTxnRemitter($remitterDataCr);
                /*                 * ******* Remitter Cr entry over ****** */

                /*                 * *** Remitter Dr txn entry ********** */
                $remitterRemitDr['mode'] = TXN_MODE_DR;
                $remitterRemitDr['txn_agent_id'] = $params['agent_id'];
                $remitterDataDr = array_merge($params, $paramsRemit, $remitterRemitDr);
                $this->insertRatnakarRemitTxnRemitter($remitterDataDr);
                /*                 * ******* Remitter Dr entry over ****** */

                /*                 * ******* Agent txn entry ****** */
                $agentRemitCr['mode'] = TXN_MODE_CR;
                $agentRemitCr['ratnakar_remittance_request_id'] = $params['remit_request_id'];
                $agentRemitCr['remit_request_id'] = 0;
                $agentRemitCr['ratnakar_remitter_id'] = $params['remitter_id'];
                $agentRemitCr['txn_remitter_id'] = 0;
                $agentData = array_merge($params, $paramsRemit, $agentRemitCr);
                $this->insertRemitTxnAgent($agentData);
                /*                 * ******* Agent entry over ****** */
            }
            /*             * ** Txns for Reversal Service Tax Amount ends ******* */

            /* agent balance updated */
            $ageBal = array('amount' => $params['amount'], 'agent_id' => $params['agent_id']);
            $this->updateTxnAgentBalance($ageBal);

            $this->_db->commit();

            return $paramsTxnCode;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
	    throw new Exception(ErrorCodes::ERROR_EDIGITAL_REFUND_TXN_NOT_COMPLETED_MSG,ErrorCodes::ERROR_EDIGITAL_REFUND_TXN_NOT_COMPLETED_CODE);
        }
    }
     /*
     * $params['remit_request_id'] = remittance request id
     * $params['product_id'] = product id
     * $params['amount'] = amount
     * $params['reversal_fee_amt'] = fee amount
     * $params['reversal_service_tax'] = reversal service tax
     */

    public function remitRatnakarTxnFailure($params) {
        $this->_db->beginTransaction();

        try {
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_status'] = FLAG_SUCCESS;
            $params['ratnakar_remittance_request_id'] = $params['remit_request_id'];
            $params['remit_request_id'] = 0;

            $txncode = new Txncode();

            /* For Remit Amount */
            if ($params['amount'] > 0) {
                if ($txncode->generateTxncode()) {
                    $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                    $txncode->setUsedStatus(); //Mark Txncode as used

                    $paramsRemit['txn_code'] = $paramsTxnCode;
                    $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE_REFUND;

                    /*                     * ******* Ops 1 Dr txn entry ****** */
                    $paramsRemitDr['mode'] = TXN_MODE_DR;
                    $paramsRemitDr['ops_id'] = TXN_OPS_ID;
                    $paramsRemitDr['txn_ops_id'] = SUSPENSE_AC_ID;
                    $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                    $this->insertRemitTxnOps($opsData);
                    /*                     * ******* Ops 1 Dr txn entry over ****** */

                    /*                     * ******* Ops 4 Cr txn entry ****** */
                    $paramsRemitCr['mode'] = TXN_MODE_CR;
                    $paramsRemitCr['ops_id'] = SUSPENSE_AC_ID;
                    $paramsRemitCr['txn_ops_id'] = TXN_OPS_ID;
                    $opsData = array_merge($params, $paramsRemit, $paramsRemitCr);
                    $this->insertRemitTxnOps($opsData);
                    /*                     * ******* Ops 4 Cr txn entry over ****** */


                    /* For Reversal Fee Amount */
                    if ($params['reversal_fee_amt'] > 0) {
                        $paramsRemit['txn_code'] = $paramsTxnCode;
                        $paramsRemit['amount'] = $params['reversal_fee_amt'];
                        $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_REFUND_FEE;

                        /*                         * *** Ops Dr txn entry ********** */
                        $paramsRemitDr['mode'] = TXN_MODE_DR;
                        $paramsRemitDr['ops_id'] = FEE_AC_ID;
                        $paramsRemitDr['txn_ops_id'] = SUSPENSE_AC_ID;
                        $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                        $this->insertRemitTxnOps($opsData);
                        /*                         * ******* Ops Dr entry over ****** */

                        /*                         * ******* Remitter Cr txn entry ****** */
                        $paramsRemitCr['mode'] = TXN_MODE_CR;
                        $paramsRemitCr['ops_id'] = SUSPENSE_AC_ID;
                        $paramsRemitCr['txn_ops_id'] = FEE_AC_ID;
                        $opsData = array_merge($params, $paramsRemit, $paramsRemitCr);
                        $this->insertRemitTxnOps($opsData);
                        /*                         * ******* Remitter Cr entry over ****** */
                    }
                    /*                     * ** Txns for Reversal Fee Amount ends ******* */

                    /* For Reversal Service Tax Amount */
                    if ($params['reversal_service_tax'] > 0) {
                        $paramsRemit['txn_code'] = $paramsTxnCode;
                        $paramsRemit['amount'] = $params['reversal_service_tax'];
                        $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_SERVICE_TAX;

                        /*                         * *** Ops Dr txn entry ********** */
                        $paramsRemitDr['mode'] = TXN_MODE_DR;
                        $paramsRemitDr['ops_id'] = SERVICE_TAX_AC_ID;
                        $paramsRemitDr['txn_ops_id'] = SUSPENSE_AC_ID;
                        $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                        $this->insertRemitTxnOps($opsData);
                        /*                         * ******* Ops Dr entry over ****** */

                        /*                         * ******* Remitter Cr txn entry ****** */
                        $paramsRemitCr['mode'] = TXN_MODE_CR;
                        $paramsRemitCr['ops_id'] = SUSPENSE_AC_ID;
                        $paramsRemitCr['txn_ops_id'] = SERVICE_TAX_AC_ID;
                        $opsData = array_merge($params, $paramsRemit, $paramsRemitCr);
                        $this->insertRemitTxnOps($opsData);
                        /*                         * ******* Remitter Cr entry over ****** */
                    }
                    /*                     * ** Txns for Reversal Service Tax ends ******* */
                } else {
                    $this->_db->rollBack();
                    App_Logger::log("Transaction Code for remittance amount could not be generated at this time. Please try later.", Zend_Log::ALERT);
                    throw new Exception("Transaction Code for remittance amount could not be generated at this time. Please try later.");
                }
            }
            /*             * ** Txns for Remit Data ends ******* */

            $this->_db->commit();

            return true;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }
 public function remitRatnakarTxnSuccessToFailure($params) {
        $this->_db->beginTransaction();

        try {
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_status'] = FLAG_SUCCESS;

            /* For Remit Amount */
            if ($params['amount'] > 0) {
                $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE_SUCCESS_TO_FAILURE;

                /*                 * *** Beneficiary Dr txn entry ********** */
                $paramsRemitCr['mode'] = TXN_MODE_DR;
                $paramsRemitCr['txn_ops_id'] = TXN_OPS_ID;
                $remitterData = array_merge($params, $paramsRemit, $paramsRemitCr);
                $this->insertRatnakarRemitTxnBeneficiary($remitterData);
                /*                 * ******* Beneficiary Dr entry over ****** */

                /*                 * ******* Ops Cr txn entry ****** */
                $paramsRemitDr['mode'] = TXN_MODE_CR;
                $paramsRemitDr['ratnakar_beneficiary_id'] = $params['beneficiary_id'];
                $paramsRemitDr['ops_id'] = TXN_OPS_ID;
                $params['ratnakar_remittance_request_id'] = $params['remit_request_id'];
                $paramsRemitDr['remit_request_id'] = 0;
                $opsData = array_merge($params, $paramsRemit, $paramsRemitDr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops Cr txn entry over ****** */
            }
            /*             * ** Txns for Remit Data ends ******* */

            $this->_db->commit();
        error_log('txn_code');
        error_log($params['txn_code']);
        //$commission = $this->calculateCommission($params['agent_id'],$params['txn_code']);

            return true;
   } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }

    }
    /*
     * for remit amount
     * for fee amount
     * for service tax amount
     */

    public function initiateRatnakarTxnRemitAPI($params) {
        $this->_db->beginTransaction();

        try {
            $params['remarks'] = '';
//            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_status'] = FLAG_SUCCESS;
            $paramTxnCode = (isset($params['txn_code'])) ? $params['txn_code'] : '';

            $txncode = new Txncode();
            if($paramTxnCode !=''){
             $paramsTxnCode = $paramTxnCode;  
            }else if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used
            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code for ratnakar remittance amount could not be generated at this time. ", Zend_Log::ALERT);
                throw new Exception("Transaction Code for ratnakar remittance amount could not be generated at this time. Please try later.");
            }
            /* For Remit Amount */
            if ($params['amount'] > 0) {
                $paramsRemit['txn_code'] = $paramsTxnCode;
                $paramsRemit['amount'] = $params['amount'];
                $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE;

                /*                 * ******* Customer DR txn entry ****** */


            $paramsDr['mode'] = TXN_MODE_DR;
            $paramsDr['txn_ops_id'] = TXN_OPS_ID;
            $paramsDr['customer_master_id'] = $params['customer_master_id'];
            $paramsDr['purse_master_id'] = $params['purse_master_id'];
            $paramsDr['customer_purse_id'] = $params['customer_purse_id'];
            
            $custData = array_merge($params, $paramsRemit, $paramsDr);
           
            $this->insertTxnRatCustomer($custData);
            /*                 * ******* Customer DR txn entry ****** */


                /*            * ******* Ops txn entry ****** */
                $opsRemitCr['mode'] = TXN_MODE_CR;
                $opsRemitCr['ops_id'] = TXN_OPS_ID;
                $opsRemitCr['ratnakar_remitter_id'] = $params['remitter_id'];
                $opsRemitCr['ratnakar_remittance_request_id'] = $params['remit_request_id'];
                $opsRemitCr['txn_customer_master_id'] = $params['customer_master_id'];
                $opsRemitCr['purse_master_id'] = $params['purse_master_id'];
                $opsRemitCr['customer_purse_id'] = $params['customer_purse_id'];
                $opsRemitCr['remitter_id'] = 0;
                $opsRemitCr['remit_request_id'] = 0;
                $opsData = array_merge($params, $paramsRemit, $opsRemitCr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops txn entry over ****** */
            }
            /*             * ** Txns for Remit Data ends ******* */

            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']), 'date_updated' => $params['date']);
            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);
           
            $this->_db->commit();

            return $paramsTxnCode;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }
    
    
    /*
     * for remit amount
     * for fee amount
     * for service tax amount
     */

    public function failureRatnakarTxnRemitAPI($params) {
        $this->_db->beginTransaction();

        try {
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_status'] = FLAG_SUCCESS;
            $paramTxnCode = (isset($params['txn_code'])) ? $params['txn_code'] : '';

            $txncode = new Txncode();
            if($paramTxnCode !=''){
             $paramsTxnCode = $paramTxnCode;  
            }else if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used
            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code for ratnakar remittance amount could not be generated at this time. ", Zend_Log::ALERT);
                throw new Exception("Transaction Code for ratnakar remittance amount could not be generated at this time. Please try later.");
            }
            /* For Remit Amount */
            if ($params['amount'] > 0) {
                $paramsRemit['txn_code'] = $paramsTxnCode;
                $paramsRemit['amount'] = $params['amount'];
                $paramsRemit['txn_type'] = TXNTYPE_REMITTANCE;

                /*                 * ******* Customer CR txn entry ****** */


            $paramsCr['mode'] = TXN_MODE_CR;
            $paramsCr['txn_ops_id'] = TXN_OPS_ID;
            $paramsCr['customer_master_id'] = $params['customer_master_id'];
            $paramsCr['purse_master_id'] = $params['purse_master_id'];
            $paramsCr['customer_purse_id'] = $params['customer_purse_id'];
            $custData = array_merge($params, $paramsRemit, $paramsCr);
            $this->insertTxnRatCustomer($custData);
            /*                 * ******* Customer DR txn entry ****** */


                /*            * ******* Ops txn entry ****** */
                $opsRemitDr['mode'] = TXN_MODE_DR;
                $opsRemitDr['ops_id'] = TXN_OPS_ID;
                $opsRemitDr['ratnakar_remitter_id'] = $params['remitter_id'];
                $opsRemitDr['ratnakar_remittance_request_id'] = $params['remit_request_id'];
                $opsRemitDr['txn_customer_master_id'] = $params['customer_master_id'];
                $opsRemitDr['purse_master_id'] = $params['purse_master_id'];
                $opsRemitDr['customer_purse_id'] = $params['customer_purse_id'];
                $opsRemitDr['remitter_id'] = 0;
                $opsRemitDr['remit_request_id'] = 0;
                $opsData = array_merge($params, $paramsRemit, $opsRemitDr);
                $this->insertRemitTxnOps($opsData);
                /*                 * ******* Ops txn entry over ****** */
            }
            /*             * ** Txns for Remit Data ends ******* */
            $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => $params['date']);
            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);
           
            $this->_db->commit();

            return $paramsTxnCode;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }
    
    
    /*
     * for remit amount
     * for fee amount
     * for service tax amount
     */

    public function initiateRatnakarWalletTransferAPI($params) {
        $this->_db->beginTransaction();

        try {
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_status'] = FLAG_SUCCESS;

            if(isset($params['txn_code']) && !empty($params['txn_code'])) {
                $paramsTxnCode = $params['txn_code'];
            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code for ratnakar remittance amount could not be generated at this time. ", Zend_Log::ALERT);
                throw new Exception("Transaction Code for ratnakar remittance amount could not be generated at this time. Please try later.");
            }
        
            $paramsRemit['txn_code'] = $paramsTxnCode;
            $paramsRemit['amount'] = $params['amount'];
            $paramsRemit['txn_type'] = TXNTYPE_WALLET_TOWALLET_FUND_TRANSFER;
            
            if(isset($params['request_type']) && $params['request_type'] == TXN_MODE_CR) {
                /********* To Customer CR txn entry starts *******/
                $paramsCr['mode'] = TXN_MODE_CR;
                $paramsCr['txn_ops_id'] = TXN_OPS_ID;
                $paramsCr['customer_master_id'] = $params['txn_customer_master_id'];
                $paramsCr['purse_master_id'] = $params['txn_purse_master_id'];
                $paramsCr['customer_purse_id'] = $params['txn_customer_purse_id'];
                $paramsCr['txn_ops_id'] = TXN_OPS_ID;

                $custData = array_merge($params, $paramsRemit, $paramsCr);
                $this->insertTxnRatCustomer($custData);
                /********* To Customer CR txn entry ends *******/
                
                /** ******* From Customer DR txn entry ****** */
                $paramsDr['mode'] = TXN_MODE_DR;
                $paramsDr['ops_id'] = TXN_OPS_ID;
                $paramsDr['txn_customer_master_id'] = $params['txn_customer_master_id'];

                $opsData = array_merge($params, $paramsRemit, $paramsDr);
                $this->insertTxnOps($opsData);
                
                $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => $params['date']);
                $where = "id = '" . $params['txn_customer_purse_id'] . "'";
                $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);                
            } elseif(isset($params['request_type']) && $params['request_type'] == TXN_MODE_DR) {
                /* For Remit Amount */
                if ($params['amount'] > 0) {

                    /** ******* From Customer DR txn entry ****** */
                    $paramsDr['mode'] = TXN_MODE_DR;
                    $paramsDr['txn_ops_id'] = TXN_OPS_ID;
                    $paramsDr['customer_master_id'] = $params['customer_master_id'];
                    $paramsDr['purse_master_id'] = $params['purse_master_id'];
                    $paramsDr['customer_purse_id'] = $params['customer_purse_id'];
                    $paramsDr['txn_ops_id'] = TXN_OPS_ID;

                    $custData = array_merge($params, $paramsRemit, $paramsDr);
                    $this->insertTxnRatCustomer($custData);
                    /** ******* Customer DR txn entry ****** */

                    /** ******* From Customer CR txn entry ****** */
                    $paramsCr['mode'] = TXN_MODE_CR;
                    $paramsCr['ops_id'] = TXN_OPS_ID;
                    $paramsCr['txn_customer_master_id'] = $params['customer_master_id'];

                    $opsData = array_merge($params, $paramsRemit, $paramsCr);
                    $this->insertTxnOps($opsData);
                    /** ******* Customer CR txn entry ****** */
                }
                /***** Txns for Remit Data ends ******* */

                $updArrFrom = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']), 'date_updated' => $params['date']);
                $where = "id = '" . $params['customer_purse_id'] . "'";
                $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArrFrom, $where);
            } else {
                /* For Remit Amount */
                if ($params['amount'] > 0) {

                    /** ******* From Customer DR txn entry ****** */
                    $paramsDr['mode'] = TXN_MODE_DR;
                    $paramsDr['txn_ops_id'] = TXN_OPS_ID;
                    $paramsDr['customer_master_id'] = $params['customer_master_id'];
                    $paramsDr['purse_master_id'] = $params['purse_master_id'];
                    $paramsDr['customer_purse_id'] = $params['customer_purse_id'];
                    $paramsDr['txn_ops_id'] = TXN_OPS_ID;

                    $custData = array_merge($params, $paramsRemit, $paramsDr);
                    $this->insertTxnRatCustomer($custData);
                    /** ******* Customer DR txn entry ****** */

                    /** ******* From Customer CR txn entry ****** */
                    $paramsCr['mode'] = TXN_MODE_CR;
                    $paramsCr['txn_ops_id'] = TXN_OPS_ID;
                    $paramsCr['customer_master_id'] = $params['txn_customer_master_id'];
                    $paramsCr['txn_customer_master_id'] = $params['customer_master_id'];
                    $paramsCr['purse_master_id'] = $params['txn_purse_master_id'];
                    $paramsCr['customer_purse_id'] = $params['txn_customer_purse_id'];
                    $paramsCr['txn_ops_id'] = TXN_OPS_ID;

                    $custData = array_merge($params, $paramsRemit, $paramsCr);
                    $this->insertTxnRatCustomer($custData);
                    /** ******* Customer CR txn entry ****** */
                }
                /**** Txns for Remit Data ends ******* */

                $updArrFrom = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']), 'date_updated' => $params['date']);
                $where = "id = '" . $params['customer_purse_id'] . "'";
                $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArrFrom, $where);

                $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => $params['date']);
                $where = "id = '" . $params['txn_customer_purse_id'] . "'";
                $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);
            }
           
            $this->_db->commit();
            return $paramsTxnCode;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }




    public function successTxnRatCorpCardDebit($params)
    {
        try 
        { 
            $this->_db->beginTransaction(); 
            
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP()); 
            $params['txn_type'] = TXNTYPE_CARD_DEBIT;
            $params['txn_status'] = FLAG_SUCCESS;
            
            
            if($params['debit_api_cr'] == POOL_AC) {

                /********* Corporate Cr txn entry *******/
                $paramsCr['mode'] = TXN_MODE_CR;
                $paramsCr['txn_customer_master_id'] = $params['customer_master_id'];
                $paramsCr['corporate_id'] = $params['agent_id'];
                $agentData = array_merge($params, $paramsCr);
                $this->insertTxnCorp($agentData);
                /********* Corporate Cr txn entry over *******/

                /***** Customer Dr txn entry ***********/
                $paramsDr['mode'] = TXN_MODE_DR;
                $paramsDr['txn_corporate_id'] =  $params['agent_id'];
                $custData = array_merge($params, $paramsDr);
                $this->insertTxnRatCustomer($custData);
                /********* Customer Dr entry over *******/

                /* corporate balance updated */
                $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']),
                    'date_modified' => $params['date']);
                $where = "corporate_id = '" . $params['agent_id'] . "'";
                $this->_db->update(DbTable::TABLE_CORPORATE_BALANCE, $updArr, $where);	
            
            } else {
                
                /********* Ops Cr txn entry *******/
                $paramsCr['mode'] = TXN_MODE_CR;
                $paramsCr['txn_customer_master_id'] = $params['customer_master_id'];
                $agentData = array_merge($params, $paramsCr);
                $this->insertTxnOps($agentData);
                /********* Ops Cr txn entry over *******/

                /***** Customer Dr txn entry ***********/
                $paramsDr['mode'] = TXN_MODE_DR;
                $paramsDr['txn_ops_id'] =  $params['ops_id'];
                $custData = array_merge($params, $paramsDr);
                $this->insertTxnRatCustomer($custData);
                /********* Customer Dr entry over *******/
            }

            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']), 'date_updated' => $params['date']);
            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);
            
            $this->_db->commit();
            
            return TRUE;

        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            $code = $e->getCode();
            if(empty($code)) {
                $code = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
            }            
            //$code = (!empty($e->getCode())) ? $e->getCode() : ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
            throw new App_Exception($e->getMessage(), $code);
        }
    }
    
     /* 
     * $params['txn_code'] = txn code
     * $params['customer_master_id'] = customer master id
     * $params['product_id'] = product id
     * $params['purse_master_id'] = purse master id
     * $params['customer_purse_id'] = customer purse id
     * $params['amount'] = amount   
     * $params['agent_id'] =>  Corporate ID
     * $params['txn_type'] =>  txn_type  
     */
    public function successTxnCorpCardLoad($params)
    {
        $this->_db->beginTransaction(); 
        
        try 
        { 
            $params['date'] = new Zend_Db_Expr('NOW()');
            
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());//$this->formatIpAddress(Util::getIP()); // cron would not return ip so commented yet
            $params['txn_status'] = FLAG_SUCCESS;

            /********* Corporate Dr txn entry *******/
            $paramsDr['mode'] = TXN_MODE_DR;
            $paramsDr['txn_customer_master_id'] = $params['customer_master_id'];
            $paramsDr['corporate_id'] = $params['agent_id'];
            $agentData = array_merge($params, $paramsDr);
            $this->insertTxnCorp($agentData);
            /********* Corporate Dr txn entry over *******/
            
            /***** Customer Cr txn entry ***********/
            $paramsCr['mode'] = TXN_MODE_CR;
            $paramsCr['txn_agent_id'] =  $params['agent_id'];
            $custData = array_merge($params, $paramsCr);
            $this->insertTxnRatCustomer($custData);
            /********* Customer Cr entry over *******/

            $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => $params['date']);
            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);
            
            /* agent balance updated */
            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']),
                'date_modified' => $params['date']);
            $where = "corporate_id = '" . $params['agent_id'] . "'";
            $this->_db->update(DbTable::TABLE_CORPORATE_BALANCE, $updArr, $where);	
            
            $this->_db->commit();
            
            return TRUE;

        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage(), $e->getCode());
        }
    }
    
    public function initiateTxnAgentToRblMvcCardholder($params) {
        $this->_db->beginTransaction();

        try {
            $txncode = new Txncode();
            if ($txncode->generateTxncode()) {
                $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
                $txncode->setUsedStatus(); //Mark Txncode as used

                $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']),
                    'block_amount' => new Zend_Db_Expr("block_amount + " . $params['amount']),
                    'date_modified' => new Zend_Db_Expr('NOW()'));
                $where = "agent_id = '" . $params['agent_id'] . "'";
                $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);


                $data['txn_code'] = $paramsTxnCode;
                $data['ip'] = $this->formatIpAddress(Util::getIP());
                $data['currency'] = CURRENCY_INR;
                $data['amount'] = $params['amount'];
                $data['product_id'] = $params['product_id'];
                $data['txn_type'] = $params['txn_type'];
                $data['txn_status'] = $params['txn_status'];
                $data['remarks'] = $params['remarks'];
                $data['date_created'] = new Zend_Db_Expr('NOW()');
                $data['bank_id'] = $params['bank_id'];
                $data['agent_id'] = $params['agent_id'];
                $data['txn_cardholder_id'] = $params['cardholder_id'];
                $data['mode'] = TXN_MODE_DR;
                $this->_db->insert(DbTable::TABLE_TXN_AGENT, $data);

                unset($data['agent_id']);
                unset($data['txn_cardholder_id']);
                $data['txn_agent_id'] = $params['agent_id'];
                $data['customer_master_id'] = $params['customer_master_id'];
                $data['mode'] = TXN_MODE_CR;
                $this->_db->insert(DbTable::TABLE_RAT_TXN_CUSTOMER,$data);
                $this->_db->commit();
            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code could not be generated at this time. Please try later.", Zend_Log::ALERT);
                throw new Exception("Transaction Code could not be generated at this time. Please try later.");
            }

            return $data['txn_code'];
        } catch (Exception $e) {
            // If any of the queries failed and threw an exception,
            // we want to roll back the whole transaction, reversing
            // changes made in the transaction, even those that succeeded.
            // Thus all changes are committed together, or none are.
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }
    
    public function completeTxnAgentToRblCardholder($params) {
    
     $this->_db->beginTransaction(); 
        
        try 
        {
            
            if ($params['txn_status'] == FLAG_SUCCESS) {
                $updArr = array('block_amount' => new Zend_Db_Expr("block_amount - " . $params['amount']), 'date_modified' => new Zend_Db_Expr('NOW()'));
                $where = "agent_id = '" . $params['agent_id'] . "'";
                $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);
                
                $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => new Zend_Db_Expr('NOW()'));
                $where = "id = '" . $params['customer_purse_id'] . "'";
                $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);
                
                $updTxnArr = array('status' => STATUS_LOADED, 'txn_load_id'=> $params['txn_load_id'],'date_load' => new Zend_Db_Expr('NOW()'));
                $where = " txn_code = '" . $params['txn_code'] . "' 
                        AND by_agent_id = '" . $params['agent_id'] . "' 
                        AND cardholder_id = '" . $params['cardholder_id'] . "'";
                $this->_db->update(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, $updTxnArr, $where);

                
            } elseif ($params['txn_status'] == FLAG_FAILURE) {
                $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']),
                    'block_amount' => new Zend_Db_Expr("block_amount - " . $params['amount']),
                    'date_modified' => new Zend_Db_Expr('NOW()'));
                $where = "agent_id = '" . $params['agent_id'] . "'";
                $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);
                
                $updTxnArr = array('status' => STATUS_FAILED, 'date_failed' => new Zend_Db_Expr('NOW()'), 'failed_reason' => $params['remarks']);
                $where = " txn_code = '" . $params['txn_code'] . "' 
                        AND by_agent_id = '" . $params['agent_id'] . "' 
                        AND cardholder_id = '" . $params['cardholder_id'] . "'";
                $this->_db->update(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, $updTxnArr, $where);
            }

            $updTxnArr = array('txn_status' => $params['txn_status'], 'remarks' => $params['remarks']);
            $where = " txn_code = '" . $params['txn_code'] . "' 
                    AND agent_id = '" . $params['agent_id'] . "' 
                    AND txn_cardholder_id = '" . $params['cardholder_id'] . "'";
            $this->_db->update(DbTable::TABLE_TXN_AGENT, $updTxnArr, $where);

            $where = " txn_code = '" . $params['txn_code'] . "' 
                    AND txn_agent_id = '" . $params['agent_id'] . "' 
                    AND customer_master_id = '" . $params['customer_master_id'] . "'";
            $this->_db->update(DbTable::TABLE_RAT_TXN_CUSTOMER, $updTxnArr, $where);
                $this->_db->commit();
            
            return TRUE;

        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage());
        }
    }

    public function successTxnAgentVirtualCardLoad($params){
        
        try 
        { 
            $params['date'] = new Zend_Db_Expr('NOW()');
            
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());//$this->formatIpAddress(Util::getIP()); // cron would not return ip so commented yet
            $params['txn_status'] = FLAG_SUCCESS;

            /********* Agent Dr txn entry *******/
            $paramsDr['mode'] = TXN_MODE_DR;
            $paramsDr['txn_customer_master_id'] = $params['customer_master_id'];
            $agentData = array_merge($params, $paramsDr);
            $this->insertTxnAgent($agentData);
            /********* Agent Dr txn entry over *******/
            
            /***** Customer Cr txn entry ***********/
            $paramsCr['mode'] = TXN_MODE_CR;
            $paramsCr['txn_agent_id'] =  $params['agent_id'];
            $custData = array_merge($params, $paramsCr);
            $this->insertTxnRatCustomer($custData);
            /********* Customer Cr entry over *******/

            $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => $params['date']);
            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);
            
            /* agent balance updated */
            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']),
                'date_modified' => $params['date']);
            $where = "agent_id = '" . $params['agent_id'] . "'";
            $this->_db->update(DbTable::TABLE_AGENT_VIRTUAL_BALANCE, $updArr, $where);	
           
            $this->_db->commit();
            
            return TRUE;

        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage());
        }
    }
    
    
      public function successTxnRatReversalCardLoadAPI($params){

        try 
        { 
            $params['date'] = new Zend_Db_Expr('NOW()');
            
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());//$this->formatIpAddress(Util::getIP()); // cron would not return ip so commented yet
            $params['txn_status'] = FLAG_SUCCESS;
            
            if(!isset($params['payable_ac_id']) || empty($params['payable_ac_id'])) {
                $params['payable_ac_id'] = DEFAULT_PAYABLE_ID; 
            }

            $params['ops_id'] = $params['payable_ac_id'];
            
            
                /********* Ops Dr txn entry *******/
                $paramsDr['mode'] = TXN_MODE_DR;
                $paramsDr['txn_customer_master_id'] = $params['customer_master_id'];
                $opsData = array_merge($params, $paramsDr);
                $this->insertTxnOps($opsData);
                /********* Dr txn entry over *******/
                
                $paramsCr['txn_ops_id'] =  $params['ops_id'];
           
                        
            /***** Customer Cr txn entry ***********/
            $paramsCr['mode'] = TXN_MODE_CR;            
            $custData = array_merge($params, $paramsCr);
            $this->insertTxnRatCustomer($custData);
            /********* Customer Cr entry over *******/

            $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => $params['date']);
              $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);
            
            $this->_db->commit();
            
            return TRUE;

        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage(), $e->getCode());
        }

        
    }
    
    public function successTxnRatAgentVirtualCardDebit($params){
        
        try 
        { 
            $this->_db->beginTransaction(); 
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP()); 
            $params['txn_type'] = TXNTYPE_CARD_DEBIT;
            $params['txn_status'] = FLAG_SUCCESS;
          
            if($params['debit_api_cr'] == POOL_AC) {
                
                
                /********* Agent Cr txn entry *******/
                $paramsCr['mode'] = TXN_MODE_CR;
                $paramsCr['txn_customer_master_id'] = $params['customer_master_id'];
                $agentData = array_merge($params, $paramsCr);
                $this->insertTxnAgent($agentData);
                /********* Agent Cr txn entry over *******/
                
                /***** Customer Dr txn entry ***********/
                $paramsDr['mode'] = TXN_MODE_DR;
                $paramsDr['txn_agent_id'] =  $params['agent_id'];
                $custData = array_merge($params, $paramsDr);
                $this->insertTxnRatCustomer($custData);
                /********* Customer Dr entry over *******/
                
                /* agent balance updated */
                $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']),
                    'date_modified' => $params['date']);
                $where = "agent_id = '" . $params['agent_id'] . "'";
                $this->_db->update(DbTable::TABLE_AGENT_VIRTUAL_BALANCE, $updArr, $where);
                
                
            } else {
                
                /********* Ops Cr txn entry *******/
                $paramsCr['mode'] = TXN_MODE_CR;
                $paramsCr['txn_customer_master_id'] = $params['customer_master_id'];
                $agentData = array_merge($params, $paramsCr);
                $this->insertTxnOps($agentData);
                /********* Cr txn entry over *******/
                
                /***** Customer Dr txn entry ***********/
                $paramsDr['mode'] = TXN_MODE_DR;
                $paramsDr['txn_ops_id'] =  $params['ops_id'];
                $custData = array_merge($params, $paramsDr);
                $this->insertTxnRatCustomer($custData);
                /********* Customer Dr entry over *******/
            }
            
            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']), 'date_updated' => $params['date']);

            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);
            
            $this->_db->commit();
            
            return TRUE;
            
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage(), $e->getCode());
        }
    }
//}

    public function successTxnRatSettledReversalCardLoadAPI($params){

        try 
        { 
            $params['date'] = new Zend_Db_Expr('NOW()');
            
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());//$this->formatIpAddress(Util::getIP()); // cron would not return ip so commented yet
            $params['txn_status'] = FLAG_SUCCESS;

            /********* Agent Dr txn entry *******/
            $paramsDr['mode'] = TXN_MODE_DR;
            $paramsDr['txn_customer_master_id'] = $params['customer_master_id'];
            $agentData = array_merge($params, $paramsDr);
            $this->insertTxnAgent($agentData);
            /********* Agent Dr txn entry over *******/
            
            /***** Customer Cr txn entry ***********/
            $paramsCr['mode'] = TXN_MODE_CR;
            $paramsCr['txn_agent_id'] =  $params['agent_id'];
            $custData = array_merge($params, $paramsCr);
            $this->insertTxnRatCustomer($custData);
            /********* Customer Cr entry over *******/

            $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => $params['date']);
            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);
            
            /* agent balance updated */
            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']),
                'date_modified' => $params['date']);
            $where = "agent_id = '" . $params['agent_id'] . "'";
            $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);	
            
            $this->_db->commit();
            
            return TRUE;

        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage());
        }
       
    }
 
    public function updateTxnAgentBalance($params) {
        $select = $this->_db->select()
                ->from(DbTable::TABLE_AGENT_BALANCE, array('amount'));
            $select->where("agent_id = ?", $params['agent_id']);
            $row = $this->_db->fetchRow($select);
            if(empty($row)) {
                //insert
                $insArr['agent_id'] = $params['agent_id'];
                $insArr['amount'] = $params['amount'];
                $insArr['block_amount'] = 0;
                $insArr['date_modified'] = new Zend_Db_Expr('NOW()');
                $this->_db->insert(DbTable::TABLE_AGENT_BALANCE, $insArr);
            } else {
                $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_modified' => new Zend_Db_Expr('NOW()'));
                $where = "agent_id = '" . $params['agent_id'] . "'";
                $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);
            }
    }
    
    public function initiateRatnakarWalletTransferRevertAPI($params) {
        $this->_db->beginTransaction();

        try {
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_status'] = FLAG_SUCCESS;

            if(isset($params['txn_code']) && !empty($params['txn_code'])) {
                $paramsTxnCode = $params['txn_code'];
            } else {
                $this->_db->rollBack();
                App_Logger::log("Transaction Code for ratnakar remittance amount could not be generated at this time. ", Zend_Log::ALERT);
                throw new Exception("Transaction Code for ratnakar remittance amount could not be generated at this time. Please try later.");
            }
        
            $paramsRemit['txn_code'] = $paramsTxnCode;
            $paramsRemit['amount'] = $params['amount'];
            $paramsRemit['txn_type'] = TXNTYPE_WALLET_TOWALLET_FUND_TRANSFER;
            
            if(isset($params['request_type']) && $params['request_type'] == TXN_MODE_CR) {
                /********* To Customer CR txn entry starts *******/
                $paramsCr['mode'] = TXN_MODE_CR;
                $paramsCr['txn_ops_id'] = TXN_OPS_ID;
                $paramsCr['customer_master_id'] = $params['customer_master_id'];
                $paramsCr['purse_master_id'] = $params['purse_master_id'];
                $paramsCr['customer_purse_id'] = $params['customer_purse_id'];
                $paramsCr['txn_ops_id'] = TXN_OPS_ID;

                $custData = array_merge($params, $paramsRemit, $paramsCr);
                $this->insertTxnRatCustomer($custData);
                /********* To Customer CR txn entry ends *******/
                
                /** ******* From Customer DR txn entry ****** */
                $paramsDr['mode'] = TXN_MODE_DR;
                $paramsDr['ops_id'] = TXN_OPS_ID;
                $paramsDr['customer_master_id'] = $params['customer_master_id'];

                $opsData = array_merge($params, $paramsRemit, $paramsDr);
                $this->insertTxnOps($opsData);
                
                $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => $params['date']);
                $where = "id = '" . $params['customer_purse_id'] . "'";
                $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);                
            }
           
            $this->_db->commit();
            return $paramsTxnCode;
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new Exception($e->getMessage());
        }
    }
    
    public function successTxnRatCardLoadByAgent($params)
    {
        
       $this->_db->beginTransaction(); 
       
        try 
        { 
            
            $agentBalanceModel = new AgentBalance();
            $agentAmt = $agentBalanceModel->getAgentBalanceLock($params['agent_id']);
            if($agentAmt >= $params['total_amount']) {
            $params['date'] = new Zend_Db_Expr('NOW()');
             $updArr = array('amount' => new Zend_Db_Expr("amount + " . $params['amount']), 'date_updated' => $params['date']);
            $where = "id = '" . $params['customer_purse_id'] . "'";
            $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);
            
            /* Amount blocked in agent balance */
            $updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['total_amount']),
                'block_amount' => new Zend_Db_Expr("block_amount + " . $params['total_amount']),
                'date_modified' => $params['date']);
            $where = "agent_id = '" . $params['agent_id'] . "'";
            $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);

            
            $params['remarks'] = '';
            $params['ip'] = $this->formatIpAddress(Util::getIP());
            $params['txn_status'] = FLAG_SUCCESS;
            
            /* For Load Amount */
            if ($params['amount'] > 0) {
                
            $paramsCr['mode'] = TXN_MODE_CR;
            $paramsCr['txn_agent_id'] =  $params['agent_id'];
            $custData = array_merge($params, $paramsCr);
            $this->insertTxnRatCustomer($custData);
             
            /********* Agent Dr txn entry *******/
            $paramsDr['mode'] = TXN_MODE_DR;
            $paramsDr['txn_customer_master_id'] = $params['customer_master_id'];
            $agentData = array_merge($params, $paramsDr);
            $this->insertTxnAgent($agentData);
            
            }
            
            /* For Fee Amount */
            if ($params['fee_amt'] > 0) {
                $paramsFee['txn_code'] = $params['txn_code'];
                $paramsFee['amount'] = $params['fee_amt'];
                $paramsFee['txn_type'] = TXNTYPE_REMITTANCE_FEE;
                
                /** ******* Agent DR:Fee txn entry ****** */
                $agentFeeDr['mode'] = TXN_MODE_DR;
                $agentFeeDr['txn_ops_id'] = FEE_AC_ID;
                $agentFeeDr['load_request_id'] = $params['load_request_id'];
               
                $agentData = array_merge($params, $paramsFee, $agentFeeDr); 
                $this->insertRemitTxnAgent($agentData);
                
                
                $opsFeeCr['mode'] = TXN_MODE_CR;
                $opsFeeCr['ops_id'] = FEE_AC_ID;
                $opsFeeCr['txn_agent_id'] = $params['agent_id'];
                $opsFeeCr['remitter_id'] = 0;
                $opsFeeCr['remit_request_id'] = 0;
                $opsData = array_merge($params, $paramsFee, $opsFeeCr);
                $this->insertRemitTxnOps($opsData);
                
            }
            
             /* For Service Tax Amount */
            if ($params['service_tax'] > 0) {
              
                $paramsSt['txn_code'] = $params['txn_code'];
                $paramsSt['amount'] = $params['service_tax'];
                $paramsSt['txn_type'] = TXNTYPE_REMITTANCE_SERVICE_TAX;
                
                /** ******* Agent DR: Service Tax entry ****** */
                $agentStDr['mode'] = TXN_MODE_DR;
                $agentStDr['txn_ops_id'] = SERVICE_TAX_AC_ID;
                $agentStDr['load_request_id'] = $params['load_request_id'];
                $agentData = array_merge($params, $paramsSt, $agentStDr); 
                $this->insertRemitTxnAgent($agentData);
                
                
                $opsServiceCr['mode'] = TXN_MODE_CR;
                $opsServiceCr['ops_id'] = SERVICE_TAX_AC_ID;
                $opsServiceCr['txn_agent_id'] = $params['agent_id'];
                $opsServiceCr['remitter_id'] = 0;
                $opsServiceCr['remit_request_id'] = 0;
                $opsData = array_merge($params, $paramsSt, $opsServiceCr);
                $this->insertRemitTxnOps($opsData);
            }
            
            /* agent balance updated */
            $updArr = array('block_amount' => new Zend_Db_Expr("block_amount - " . $params['total_amount']),
                'date_modified' => $params['date']);
            $where = "agent_id = '" . $params['agent_id'] . "'";
            $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $updArr, $where);

            /* ============================== */
            
            $this->_db->commit();
            
            return TRUE;
            }else{
                throw new Exception ("Agent does not have sufficient fund");
            }

        } catch (Exception $e) {
            
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage(), $e->getCode());
        }
    }
}
