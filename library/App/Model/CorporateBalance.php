<?php

class CorporateBalance extends BaseUser
{
    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'corporate_id';
    
    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_CORPORATE_BALANCE;
    
    private $_msg;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_CardholderUser';
    
    

    /*public function getList(){
           $select = $this->select()
                   ->setIntegrityCheck(false)
                ->from("t_agents as a",array('id'))
                   ->joinLeft("t_opertaion as o", "o.id = a.operation_id",array('id','name'))
                ->where('email =?','vikram@transerv.co.in');
           //echo $select->__toString();exit;
           return  $this->fetchAll($select);

    }*/
          
      
    public function getCorporateBalance($corporateId){
        $select = $this->_select()
                ->where('corporate_id = ?', $corporateId);
        //echo $select; exit;         
        $data = $this->fetchRow($select);      
        if(isset($data->amount)){
            return $data->amount;
        }
        else {
            return 0;
        }
    }    
    
    
    public function loadAgentBalance($param){

        $objBT = new BaseTxn();           

        try{
              $objBT->opsToAgent($param);  
           } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    throw new Exception($e->getMessage());exit;
                    return false;
          }             
         return true;        
    }
    
    public function getAgentLimit(){        
        return Validator_AgentBalance::getMaximumAllowedLimit();
    }
    
   
    public function getAgentActiveBalance($id)
    {
        $select = $this->_select();
        //$select->from($this->_name);
        $select->where('agent_id = ?', $id);        
        $data = $this->fetchRow($select);   
      
        if(!empty($data)) {
            return $data->amount;
        } else {
            return '0.00';
        }       
    }
    
    public function getAgentBlockedAmount($id){

        $select = $this->_select();
        //$select->from($this->_name);
        $select->where('agent_id = ?', $id);        
        $data = $this->fetchRow($select);      
        if(empty($data)) {
            return $data->block_amount;
        } else {
            return '0.00';
        }
    }   
    
    
    private function setMessage($msg)
    {
        $this->_msg = $msg;
    }
    
    
    private function getMessage()
    {
        return $this->_msg;
    }
    
    
    private function loadBalance($id, $loadAmount = '0.00')
    {
        
        $primary = (is_array($this->_primary)? $this->_primary[1] : $this->_primary);
        
        $data = array(
            'agent_id' => $id,
            'amount' => $this->getBalanceAfterLoad($id, $loadAmount),
            'date_modified' => new Zend_Db_Expr('NOW()')
        );
        
        if(isset($data[$primary]) && $data[$primary]) {
            
            // we have a non-null value for the primary key, check if we can update
            $select = $this->_select();
            $select->where($primary . '= ?', $data[$primary]);
            $select->reset(Zend_Db_Table::COLUMNS);
            $select->columns(array('COUNT(' . $primary . ') as cnt'));
            $rs = $this->fetchRow($select);
            if($rs['cnt'] == 1){
                // we have valid pk, update it
                $id = $data[$primary];
                
                $this->update($data, $this->_db->quoteInto($primary . '= ?', $id));
                return $id;
            } else {
                // we don't have a valid pk, insert it
                //$data[$primary] = NULL;
                //$date['date_modified'] = new Zend_Db_Expr('NOW()');
                return $this->insert($data);
            }
        } else {
            // no primary provided, do a regular insert
            //$date['date_modified'] = new Zend_Db_Expr('NOW()');
            return $this->insert($data);
        }
    

//        //Will Do Insert/Update bases on current state
//        $this->save($data);
    

    }
    
    
    
    public function getBalanceAfterLoad($id, $loadAmount = '0.00')
    {        
        $activeBalance = $this->getAgentActiveBalance($id);       
        return $this->sumAmount($activeBalance, $loadAmount);
    }
    
      public static function sumAmount($amount1, $amount2)
      {
        return $amount1 + $amount2;
      }

    
    
    public function updateAgentBalance($param){
        if($param['agent_id']=='' || $param['amount']=='')
           return false;
        
        $agentActiveBal = $this->getAgentActiveBalance($param['agent_id']);
        $filters['amount'] = $agentActiveBal - $param['amount'];  
        $filters['agent_id'] = $param['agent_id'];        
        $filters['date_modified'] = date('Y-m-d H:i:s');        
        
        if($filters['amount']<1)
            return false;        
       
        $this->_db->update(DbTable::TABLE_AGENT_BALANCE, $filters, 'agent_id="'.$filters['agent_id'].'"');
        
        return true;       
    }
    
    
    public function chkCanAssignProduct($param){
        if($param['agent_id'] == '' || $param['product_id'] == '')
            throw new Exception ('Insuffient Data');
        
        $objBTxn = new BaseTxn();
        try{
            $resp = $objBTxn->chkCanAssignProduct($param);
            //throw new Exception ('Low balance ...'); return false;
        }catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception($e->getMessage());
            return false;
        }    
        return $resp;
    }
    
    
    public function getAgentsBalance() {
        $select  = $this->_db->select()        
        //$select->setIntegrityCheck(FALSE)
        ->from(DbTable::TABLE_AGENT_BALANCE,array('agent_id', 'amount as closing_balance'));
        //echo $select; exit;     "DATE_FORMAT(date_modified, '%Y-%m-%d') as date"    
        return $this->_db->fetchAll($select);      
    }
    
    /* updateAgentClosingBalance() function will be called for cron for
     * daily agent closing balance update
     */
      public function updateAgentClosingBalance() {
            //throw new Exception('testing exception message');
           
            
            $agentsModel = new Agents();
            $cardloadsModel =  new CardLoads();
            $remittancerequestModel = new Remit_Remittancerequest();
            $remittersModel = new Remit_Remitter();
            $fundRequestModel = new FundRequest();
            $fundingModel = new AgentFunding();
            $fundtrfrModel = new AgentFundTransfer();
            $loadModel = new Corp_Cardload();
            $paytronicLoadModel = new Corp_Ratnakar_Cardload();
            $loadModel = new Corp_Cardload();
            
            $agentsBal = $this->getAgentsBalance();
            $totalAgents = sizeof($agentsBal);
            $yesterday = date('Y-m-d',strtotime('-1 days'));
            
           
            if($totalAgents>0){      
                
                $this->_db->beginTransaction(); 
        
                try 
                {
                    $agentUpdCount = 0;
                    foreach($agentsBal as $key=>$val){  
                      
                        $val['date'] = date('Y-m-d',strtotime('-2 days'));
                        $param = array('agent_id'=>$val['agent_id'], 'date'=>$val['date']);
                        $agClosingBal = $this->getAgentClosingBalance($param);
                        
                        $bindArr = $agentsModel->getAgentBinding($val['agent_id'], $yesterday);
                        $bank_unicode = isset($bindArr[0]['bank_unicode']) ? $bindArr[0]['bank_unicode'] : '';
                        $agentArr = array(
                                        'agent_id' => $val['agent_id'],
                                        'agentId' => $val['agent_id'],
                                        'date' => $yesterday,
                                        'bank_unicode' => $bank_unicode,
                                        'on_date' => TRUE
                                    );
                       // Loads / Reloads (t_cardloads)
                        $agentCardLoads = $cardloadsModel->getAgentTotalLoadReload($agentArr);
                         
                         //Remitter Rgn fee and service tax
                         $agentRemitterRgnFeeTax = $remittersModel->getAgentTotalRemitterRegnFeeSTax($agentArr);
                        // Remittance 
                         $agentRemittanceRequest = Util::toArray($remittancerequestModel->getAgentTotalRemittanceFeeSTax($agentArr));
                         //Remittance Refund, fee and service tax
                         $agentRefundRequest = $remittancerequestModel->getAgentTotalRemittanceRefundSTax($agentArr);
                        // Fund Requests approved
                         $agentFundrequests = $fundRequestModel->getTotalAgentFund($agentArr,$onDate = TRUE );
                         $agentFunding = $fundingModel->getAgentTotalFund($agentArr,$onDate = TRUE );
                         
                         $agentArr['txn_type'] = TXNTYPE_AGENT_TOAGENT_FUND_TRANSFER;
                         $agentFundTrfrDr = $fundtrfrModel->getAgentTotalFundTrfrDr($agentArr);
                         $agentArr['txn_type'] = TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL;
                         $agentFundTrfrRvslDr = $fundtrfrModel->getAgentTotalFundTrfrDr($agentArr);
                         $agentArr['txn_type'] = TXNTYPE_AGENT_TOAGENT_FUND_TRANSFER;
                         $agentFundTrfrCr = $fundtrfrModel->getAgentTotalFundTrfrCr($agentArr);
                         $agentArr['txn_type'] = TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL;
                         $agentFundTrfrRvslCr = $fundtrfrModel->getAgentTotalFundTrfrCr($agentArr);
                         
                         $agentArr['txn_type'] = TXNTYPE_RAT_CORP_MEDIASSIST_LOAD;
                         $corpLoad = $loadModel->getAgentTotalLoad($agentArr);
                         
                        // Paytronic Load
                         $agentArr['txn_type'] = TXNTYPE_CARD_RELOAD;
                         $paytronicCorpLoad = $paytronicLoadModel->getAgentTotalLoad($agentArr);
                        
                         // Paytronic Debit
                        $agentArr['txn_type'] = TXNTYPE_CARD_DEBIT;
                        $agentArr['status'] = STATUS_DEBITED;
                        $paytronicDebit = $paytronicLoadModel->getAgentTotalLoad($agentArr);
                        $paytronicDebit['total_agent_load_amount'] = (isset($paytronicDebit['total_agent_load_amount']))? $paytronicDebit['total_agent_load_amount']: 0;
                    
                        // Paytronic Reversal
                         $agentArr['cutoff'] = TRUE;
                         $paytronicReversal = $paytronicLoadModel->getAgentTotalLoad($agentArr);
                       

                         $agentFundrequests['total_agent_funding_amount'] = (isset($agentFundrequests['total_agent_funding_amount']))? $agentFundrequests['total_agent_funding_amount']: 0;
                         $agentFunding['total_agent_funding_amount'] = (isset($agentFunding['total_agent_funding_amount']))? $agentFunding['total_agent_funding_amount']: 0;
                         $agentRefundRequest['agent_total_remittance_refund'] = (isset($agentRefundRequest['agent_total_remittance_refund']))? $agentRefundRequest['agent_total_remittance_refund']: 0;
                         $agentRefundRequest['agent_total_reversal_refund_stax'] = (isset($agentRefundRequest['agent_total_reversal_refund_stax']))? $agentRefundRequest['agent_total_reversal_refund_stax']: 0;
                         $agentRefundRequest['agent_total_reversal_refund_fee'] = (isset($agentRefundRequest['agent_total_reversal_refund_fee']))? $agentRefundRequest['agent_total_reversal_refund_fee']: 0;
                         $agentCardLoads['total_agent_load_reload'] = (isset($agentCardLoads['total_agent_load_reload']))? $agentCardLoads['total_agent_load_reload'] : 0;
                         $agentRemittanceRequest['agent_total_remittance'] = (isset($agentRemittanceRequest['agent_total_remittance']))? $agentRemittanceRequest['agent_total_remittance'] : 0;
                         $agentRemittanceRequest['agent_total_remittance_fee'] = (isset($agentRemittanceRequest['agent_total_remittance_fee']))? $agentRemittanceRequest['agent_total_remittance_fee'] : 0;
                         $agentRemittanceRequest['agent_total_remittance_stax'] = (isset($agentRemittanceRequest['agent_total_remittance_stax']))? $agentRemittanceRequest['agent_total_remittance_stax'] : 0;
                         $agentRemitterRgnFeeTax['agent_total_remitter_regn_fee'] = (isset($agentRemitterRgnFeeTax['agent_total_remitter_regn_fee']))? $agentRemitterRgnFeeTax['agent_total_remitter_regn_fee'] : 0;
                         $agentRemitterRgnFeeTax['agent_total_remitter_regn_stax'] = (isset($agentRemitterRgnFeeTax['agent_total_remitter_regn_stax']))? $agentRemitterRgnFeeTax['agent_total_remitter_regn_stax'] : 0;
                         $agentFundTrfrCr['total_agent_fundtrfr_amount'] = (isset($agentFundTrfrCr['total_agent_fundtrfr_amount']))? $agentFundTrfrCr['total_agent_fundtrfr_amount']: 0;
                         $agentFundTrfrRvslCr['total_agent_fundtrfr_amount'] = (isset($agentFundTrfrRvslCr['total_agent_fundtrfr_amount']))? $agentFundTrfrRvslCr['total_agent_fundtrfr_amount']: 0;
                         $agentFundTrfrDr['total_agent_fundtrfr_amount'] = (isset($agentFundTrfrDr['total_agent_fundtrfr_amount']))? $agentFundTrfrDr['total_agent_fundtrfr_amount']: 0;
                         $agentFundTrfrRvslDr['total_agent_fundtrfr_amount'] = (isset($agentFundTrfrRvslDr['total_agent_fundtrfr_amount']))? $agentFundTrfrRvslDr['total_agent_fundtrfr_amount']: 0;
                         $corpLoad['total_agent_load_amount'] = (isset($corpLoad['total_agent_load_amount']))? $corpLoad['total_agent_load_amount']: 0;
                         $paytronicCorpLoad['total_agent_load_amount'] = (isset($paytronicCorpLoad['total_agent_load_amount']))? $paytronicCorpLoad['total_agent_load_amount']: 0;
                         $paytronicReversal['total_agent_load_amount'] = (isset($paytronicReversal['total_agent_load_amount']))? $paytronicReversal['total_agent_load_amount']: 0;
                         
                         $addOnOpeningBal = $agentFundrequests['total_agent_funding_amount'] 
                                            + $agentFunding['total_agent_funding_amount'] 
                                            + $agentRefundRequest['agent_total_remittance_refund'] 
                                            + $agentRefundRequest['agent_total_reversal_refund_stax'] 
                                            + $agentRefundRequest['agent_total_reversal_refund_fee']
                                            + $agentFundTrfrCr['total_agent_fundtrfr_amount']
                                            + $agentFundTrfrRvslCr['total_agent_fundtrfr_amount']
                                            + $paytronicReversal['total_agent_load_amount']
                                            + $paytronicDebit['total_agent_load_amount'];
                         $subtractOnOpeningBal = $agentCardLoads['total_agent_load_reload'] 
                                            + $agentRemittanceRequest['agent_total_remittance'] 
                                            + $agentRemittanceRequest['agent_total_remittance_fee'] 
                                            + $agentRemittanceRequest['agent_total_remittance_stax'] 
                                            +  $agentRemitterRgnFeeTax['agent_total_remitter_regn_fee'] 
                                            + $agentRemitterRgnFeeTax['agent_total_remitter_regn_stax']
                                            + $agentFundTrfrDr['total_agent_fundtrfr_amount']
                                            + $agentFundTrfrRvslDr['total_agent_fundtrfr_amount']
                                            + $corpLoad['total_agent_load_amount']
                                            + $paytronicCorpLoad['total_agent_load_amount'];
                        
                         $closingBal = $agClosingBal['closing_balance'] + $addOnOpeningBal - $subtractOnOpeningBal;
                         
                        // inserting balance if not already inserted earlier
                        $param = array('agent_id'=>$val['agent_id'], 'date'=>$yesterday);
                        $agClosingBalYesterday = $this->getAgentClosingBalance($param);
                        if(empty($agClosingBalYesterday)){ 
                           $this->_db->insert(DbTable::TABLE_AGENT_CLOSING_BALANCE, array('closing_balance'=> $closingBal,'agent_id'=> $val['agent_id'],'date' => $yesterday));                    
                        } else {
                            // updating balance if already added earlier
                            $where = "agent_id='".$val['agent_id']."' AND date='".$yesterday."'";
                            $dateUpdated = new Zend_Db_Expr('NOW()');
                            $updData = array('closing_balance'=> $closingBal, 'date_updated'=> $dateUpdated);
                            
                            $this->_db->update(DbTable::TABLE_AGENT_CLOSING_BALANCE, $updData, $where);                    
                        }
                        $agentUpdCount++;
                    }
                    $this->_db->commit();
                    return $agentUpdCount;
                }
                catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        // If any of the queries failed and threw an exception,
                        // we want to roll back the whole transaction, reversing
                        // changes made in the transaction, even those that succeeded.
                        // Thus all changes are committed together, or none are.
                        $this->_db->rollBack();
                        //echo $e->getMessage();
                        //throw new Exception ("Transaction not completed due to system failure");
                        throw new Exception($e->getMessage());
                }
            } else return 0;
    } 
    
    
     public function getAgentClosingBalance($param) {
         $agentId = isset($param['agent_id'])?$param['agent_id']:'';
         $date = isset($param['date'])?$param['date']:'';
         
         if($agentId>0 && $date!=''){
            $select  = $this->_db->select()        
            //->setIntegrityCheck(FALSE)
            ->from(DbTable::TABLE_AGENT_CLOSING_BALANCE, array('closing_balance'))
            ->where('agent_id = ?', $agentId)
            ->where('date = ?', $date);
//            echo $select; //exit;    
            return $this->_db->fetchRow($select);      
         } else return false;
    }
    
    /*
     * $params: agent_id, product_id, amount
     */
    public function chkAllowReLoad($param){
        if($param['agent_id'] == '' || $param['product_id'] == '' || $param['amount'] == '')
            throw new Exception ('Insuffient Data');
        
        $objBTxn = new BaseTxn();
        try{
            $resp = $objBTxn->chkAllowReLoad($param);
            //throw new Exception ('Low balance ...'); return false;
        }catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception($e->getMessage());
            return false;
        }    
        return $resp;
    }
    
    /**
     * getAgentBalanceInfo
     * Get Record of agent balance table instead of getting only balance
     * @param type $agentId
     * @return boolean
     */
    public function getCorporateBalanceInfo($agentId){
        $select = $this->_select()
                ->where('corporate_id = ?', $agentId);        
        //echo $select;
        $rs = $this->fetchRow($select);  
        
        if(empty($rs)) {
            return false;
        }
        return $rs;
    }        
    
     public function getBalance($agentId){
          $select = $this->select()
                ->from(DbTable::TABLE_AGENT_BALANCE." as b")
                ->joinLeft(DbTable::TABLE_AGENTS." as a", "b.agent_id = a.id",array('concat(a.first_name," ",a.last_name) as name'))
                ->where('b.agent_id =?',$agentId);
         return $this->fetchRow();
    }   
    
    public function getCorpinfoByCorpCode($params) {
        $corporateCode = $params['corpcode'];
        $productID = $params['product_id'];
        $curdate = date("Y-m-d");
        $sql = $this->_db->select();
        $sql->from(DbTable::TABLE_CORPORATE_USER . " as cu", array('cu.corporate_code', 'concat(cu.first_name," ",cu.last_name) as name'));
        $sql->joinLeft(DbTable::TABLE_CORPORATE_BALANCE . " as cb", "cb.corporate_id = cu.id", array('cb.corporate_id', 'cb.amount', 'cb.block_amount'));
        $sql->joinLeft(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION.' as bcp', "bcp.corporate_id = cu.id AND '".$curdate ."' >= bcp.date_start AND ('".$curdate."' <= bcp.date_end OR bcp.date_end = '0000-00-00' OR bcp.date_end is NULL)");
        $sql->where('cu.corporate_code =?', $corporateCode); 
        // $sql->where('cu.enroll_status =?',ENROLL_APPROVED_STATUS);
        $sql->where('cu.status =?', STATUS_UNBLOCKED);
        if ($productID != '') {
            $sql->where('bcp.product_id =?',$productID);
        }
        $rs = $this->_db->fetchRow($sql);
        if (empty($rs)) {
            return false;
        }
        return $rs;
    }
}