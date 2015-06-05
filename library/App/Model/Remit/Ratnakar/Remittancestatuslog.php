<?php

class Remit_Ratnakar_Remittancestatuslog extends Remit_Ratnakar
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
    protected $_name = DbTable::TABLE_RATNAKAR_REMITTANCE_STATUS_LOG;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    
    /*
     * Add remittance request in status change log 
     */
    
    
    public function addStatus($param)
    {
         
        
       try {       
          return $this->_db->insert(DbTable::TABLE_RATNAKAR_REMITTANCE_STATUS_LOG,$param); 
       }
       catch(Exception $e ) {
           App_Logger::log($e->getMessage(), Zend_Log::ERR);
           return false;
        }
    }
    
    
    
    /* getRemittanceRefundYetToClaim() will return the remittance requests which yet to claim
     */
     public function getRemittanceRefundYetToClaim($param){
        $decryptionKey = App_DI_Container::get('DbConfig')->key; 
        $toDate = isset($param['to'])?$param['to']:'';
        $fromDate = isset($param['from'])?$param['from']:'';
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'".$decryptionKey."') as bank_account_number");
        
        if($toDate!='' && $fromDate!=''){
        $select = $this->_db->select();   
        $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_STATUS_LOG." as rsl", array('rsl.date_created'));              
        $select->joinInner(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST." as rr", "rsl.remittance_request_id =rr.id", array('amount', 'txn_code', 'service_tax', 'fee','neft_remarks as remarks', 'utr', 'date_utr', 'status_utr', 'date_status_response', 'status_response', 'status', 'batch_name', 'batch_date', 'neft_processed', 'neft_processed_date', 'status_sms', 'date_updated'));              
        $select->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS." as r", "rr.remitter_id =r.id", array('r.name as remitter_name', 'r.mobile as remitter_mobile', 'r.email as remitter_email'));
        $select->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES." as b", "rr.beneficiary_id = b.id", array('b.name as beneficiary_name', $bankAccountNumber, 'b.by_agent_id'));
                
        $select->where("rsl.status_new = ?", STATUS_FAILURE);
        //$select->where("rr.status = ?", STATUS_FAILURE);
        $select->where("DATE(rsl.date_created) BETWEEN '".$fromDate."' AND '".$toDate."'");
        $select->order('rsl.date_created ASC');
      // echo $select->__toString();//exit;
        $rows = $this->_db->fetchAll($select);      

        $rsCount = count($rows);
        $retData = array();
        $i = 0;
        
        if($rsCount > 0)
        {
            foreach($rows as $val)
            {
                $retData[$i]['date_created'] = $val['date_created'];
                $retData[$i]['amount'] = $val['amount'];
                $retData[$i]['txn_code'] = $val['txn_code'];
                $retData[$i]['service_tax'] = $val['service_tax'];
                $retData[$i]['fee'] = $val['fee'];
                $retData[$i]['remarks'] = $val['remarks'];
                $retData[$i]['remitter_name'] = $val['remitter_name'];
                $retData[$i]['remitter_mobile'] = $val['remitter_mobile'];
                $retData[$i]['remitter_email'] = $val['remitter_email'];
                $retData[$i]['beneficiary_name'] = $val['beneficiary_name'];
                $retData[$i]['bank_account_number'] = $val['bank_account_number'];
                $retData[$i]['date_utr'] = $val['date_utr'];                
                $retData[$i]['status_utr'] = $val['status_utr'];
                $retData[$i]['date_status_response'] = $val['date_status_response'];
                $retData[$i]['status_response'] = $val['status_response'];
                $retData[$i]['status'] = $val['status'];
                $retData[$i]['batch_name'] = $val['batch_name'];
                $retData[$i]['batch_date'] = $val['batch_date'];
                $retData[$i]['neft_processed'] = $val['neft_processed'];
                $retData[$i]['neft_processed_date'] = $val['neft_processed_date'];
                $retData[$i]['status_sms'] = $val['status_sms'];
                $retData[$i]['date_updated'] = $val['date_updated'];
                
                $agentUser = new AgentUser();
                $usertype = $agentUser->getAgentDetailsById($val['by_agent_id']);
                $agentType = $agentUser->getAgentCodeName($usertype['user_type'], $val['by_agent_id']);
                $getBankame = $agentUser->getAgentBankName($val['by_agent_id']);
                
                $retData[$i] = array_merge($retData[$i], $agentType);
                
                $retData[$i]['utr'] = $val['utr'];
                $retData[$i]['mobile'] = $usertype['mobile1'];
                $retData[$i]['agent_code'] = $usertype['agent_code'];
                $retData[$i]['agent_name'] = $usertype['first_name'].' '.$usertype['last_name'];
                $retData[$i]['bank_name'] = $getBankame['bank_name'];
                
                $i++;
            }
        }
        
        return $retData;
    }
    else return array();
   }
   
   
    
    
}