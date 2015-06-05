<?php

class TxnAgent extends App_Model
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
    protected $_name = DbTable::TABLE_TXN_AGENT;
    
    private $_msg;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_CardholderUser';
   
    public function getBCAgentForAgentId($agentId){
    	$select = $this->_db->select()
    	->from(DbTable::TABLE_AGENTS, array('bcagent'))
    	->where('id=?',$agentId);
    	$row = $this->_db->fetchRow($select);
    	    	return $row['bcagent'];
    }
    
      
    public function getTxnAgentDaily($agentId, $curDate){
        $select = $this->_db->select()       
                ->from(DbTable::TABLE_TXN_AGENT, array('count(*) as count', 'sum(amount) as total'))              
                ->where('mode=?',TXN_MODE_DR) 
                ->where('agent_id=?',$agentId)
                ->where("txn_type in ('".TXNTYPE_CARD_RELOAD."', '".TXNTYPE_FIRST_LOAD."', "
                        . "'".TXNTYPE_REMITTANCE."', '".TXNTYPE_REMITTANCE_REFUND."', "
                        . "'".TXNTYPE_RAT_CORP_CORPORATE_LOAD."' , '".TXNTYPE_RAT_CORP_MEDIASSIST_LOAD."', "
                        . "'".TXNTYPE_RAT_CORP_PAYTRONICS_LOAD."' )")
                ->where("txn_status = '".FLAG_SUCCESS."' OR txn_status = '".FLAG_PENDING."'")
                ->where("DATE(date_created) = '".$curDate."'")
                ->group("agent_id");
        //echo $select->__toString();
        $row = $this->_db->fetchRow($select);      
        return $row;
    }    
    
    public function getTxnAgentDuration($agentId, $startDate, $endDate){
        $select = $this->_db->select()       
                ->from(DbTable::TABLE_TXN_AGENT, array('count(*) as count', 'sum(amount) as total'))              
                ->where('mode=?',TXN_MODE_DR) 
                ->where('agent_id=?',$agentId)
                ->where("txn_type in ('".TXNTYPE_CARD_RELOAD."', '".TXNTYPE_FIRST_LOAD."', "
                        . "'".TXNTYPE_REMITTANCE."', '".TXNTYPE_REMITTANCE_REFUND."', "
                       . "'".TXNTYPE_RAT_CORP_CORPORATE_LOAD."' , '".TXNTYPE_RAT_CORP_MEDIASSIST_LOAD."', "
                        . "'".TXNTYPE_RAT_CORP_PAYTRONICS_LOAD."' )")
                ->where("txn_status = '".FLAG_SUCCESS."' OR txn_status = '".FLAG_PENDING."'")
                ->where("DATE(date_created) BETWEEN '".$startDate."' AND '".$endDate."'")
                ->group("agent_id");
        //echo $select->__toString();
        $row = $this->_db->fetchRow($select);      
        return $row;
    }    
   
    public function getCommTxnAgentDuration($agentId, $startDate, $endDate){
        //Enable DB Slave
        $this->_enableDbSlave();
        $select = $this->_db->select()
                ->from(DbTable::TABLE_TXN_AGENT, array('count(*) as count', 'sum(amount) as total'))
                ->where('mode=?',TXN_MODE_CR)
                ->where('agent_id=?',$agentId)
                ->where("txn_type in ( 'COMM' )")
                ->where("txn_status = '".FLAG_SUCCESS."' OR txn_status = '".FLAG_PENDING."'")
                ->where("DATE(date_created) BETWEEN '".$startDate."' AND '".$endDate."'")
                ->group("agent_id");
        //echo $select->__toString();
        $row = $this->_db->fetchRow($select);
        //Disable DB Slave
        $this->_disableDbSlave();
        return $row;
    }

	
public function getRevCommTxnAgentDuration($agentId, $startDate, $endDate){
        //Enable DB Slave
        $this->_enableDbSlave();
        $select = $this->_db->select()
                ->from(DbTable::TABLE_TXN_AGENT, array('count(*) as count', 'sum(amount) as total'))
                ->where('mode=?',TXN_MODE_DR)
                ->where('agent_id=?',$agentId)
                ->where("txn_type in ( 'RCOM' )")
                ->where("txn_status = '".FLAG_SUCCESS."' OR txn_status = '".FLAG_PENDING."'")
                ->where("DATE(date_created) BETWEEN '".$startDate."' AND '".$endDate."'")
                ->group("agent_id");
        //echo $select->__toString();
        $row = $this->_db->fetchRow($select);
        //Disable DB Slave
        $this->_disableDbSlave();
        return $row;
    }
 
    public function getTxnAgentProductDaily($agentId, $productId, $curDate){
        $select = $this->_db->select()       
                ->from(DbTable::TABLE_TXN_AGENT, array('count(*) as count', 'sum(amount) as total'))              
                ->where('mode=?',TXN_MODE_DR) 
                ->where('agent_id=?',$agentId)
                ->where('product_id=?',$productId)
                ->where("txn_type in ('".TXNTYPE_CARD_RELOAD."', '".TXNTYPE_FIRST_LOAD."', "
                        . "'".TXNTYPE_REMITTANCE."', '".TXNTYPE_REMITTANCE_REFUND."', "
                        . "'".TXNTYPE_RAT_CORP_CORPORATE_LOAD."' , '".TXNTYPE_RAT_CORP_MEDIASSIST_LOAD."', "
                        . "'".TXNTYPE_RAT_CORP_PAYTRONICS_LOAD."' )")
                ->where("txn_status = '".FLAG_SUCCESS."' OR txn_status = '".FLAG_PENDING."'")
                ->where("DATE(date_created) = '".$curDate."'")
                ->group("agent_id");
        //echo $select->__toString();
        $row = $this->_db->fetchRow($select);      
        return $row;
    }    
    
    public function getTxnAgentProductDuration($agentId, $productId, $startDate, $endDate){
        $select = $this->_db->select()       
                ->from(DbTable::TABLE_TXN_AGENT, array('count(*) as count', 'sum(amount) as total'))              
                ->where('mode=?',TXN_MODE_DR) 
                ->where('agent_id=?',$agentId)
                ->where('product_id=?',$productId)
                ->where("txn_type in ('".TXNTYPE_CARD_RELOAD."', '".TXNTYPE_FIRST_LOAD."', "
                        . "'".TXNTYPE_REMITTANCE."', '".TXNTYPE_REMITTANCE_REFUND."', "
                        . "'".TXNTYPE_RAT_CORP_CORPORATE_LOAD."' , '".TXNTYPE_RAT_CORP_MEDIASSIST_LOAD."', "
                        . "'".TXNTYPE_RAT_CORP_PAYTRONICS_LOAD."' )")
                ->where("txn_status = '".FLAG_SUCCESS."' OR txn_status = '".FLAG_PENDING."'")
                ->where("DATE(date_created) BETWEEN '".$startDate."' AND '".$endDate."'")
                ->group("agent_id");
        //echo $select->__toString();
        $row = $this->_db->fetchRow($select);      
        return $row;
    }    
public function getAgentTxnsForCommission($param, $status = array()) {
    	$date = isset($param['date']) ? $param['date'] : '';
    	$agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
    	$to = isset($param['to']) ? $param['to'] : '';
    	$from = isset($param['from']) ? $param['from'] : '';
    	$statusWhere = '';
    
    	$select = $this->select();
    	$select->from(DbTable::TABLE_TXN_AGENT . " as txn", array('txn.amount','txn.date_created'));
    	$select->setIntegrityCheck(false);
    	$select->joinLeft(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST ." as kot", "txn.kotak_remittance_request_id = kot.id", array('kot.txn_code as kot_txn_code', 'kot.status as kot_txn_status'));
    	$select->joinLeft(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST ." as rat", "txn.ratnakar_remittance_request_id = rat.id", array('rat.txn_code as rat_txn_code', 'rat.status as rat_txn_status'));
    	$select->joinLeft(DbTable::TABLE_KOTAK_REMITTERS ." as kotr", "kot.remitter_id = kotr.id", array('name as kot_name', 'last_name as kot_last_name'));
    	$select->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS ." as ratr", "rat.remitter_id = ratr.id", array('name as rat_name', 'last_name as rat_last_name'));
    	 
    	if ($agentId > 0) {
    		$select->where("txn.agent_id = ? ", $agentId);
    	}
    	
    	$select->where("txn.txn_type = ? ", TXNTYPE_AGENT_COMMISSION);

    	if ($to != '' && $from != '') {
    		$select->where("txn.date_created BETWEEN '$from' AND '$to'");
    	} else {
    		$select->where("DATE(txn.date_created) = ?", $date);
    	}
    
    	return $this->fetchAll($select);
    }
    
    public function getAgentTxnsForCommissionReversal($param, $status = array()) {
    	$date = isset($param['date']) ? $param['date'] : '';
    	$agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
    	$to = isset($param['to']) ? $param['to'] : '';
    	$from = isset($param['from']) ? $param['from'] : '';
    	$statusWhere = '';
    
    	$select = $this->select();
    	$select->from(DbTable::TABLE_TXN_AGENT . " as txn", array('txn.amount','txn.date_created'));
    	$select->setIntegrityCheck(false);
    	$select->joinLeft(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST ." as kot", "txn.kotak_remittance_request_id = kot.id", array('kot.txn_code as kot_txn_code', 'kot.status as kot_txn_status'));
    	$select->joinLeft(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST ." as rat", "txn.ratnakar_remittance_request_id = rat.id", array('rat.txn_code as rat_txn_code', 'rat.status as rat_txn_status'));
    	$select->joinLeft(DbTable::TABLE_KOTAK_REMITTERS ." as kotr", "kot.remitter_id = kotr.id", array('name as kot_name', 'last_name as kot_last_name'));
    	$select->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS ." as ratr", "rat.remitter_id = ratr.id", array('name as rat_name', 'last_name as rat_last_name'));
    
    	if ($agentId > 0) {
    		$select->where("txn.agent_id = ? ", $agentId);
    	}
    	 
    	$select->where("txn.txn_type = ? ", TXNTYPE_AGENT_COMMISSION_REVERSAL);
    
    	if ($to != '' && $from != '') {
    		$select->where("txn.date_created BETWEEN '$from' AND '$to'");
    	} else {
    		$select->where("DATE(txn.date_created) = ?", $date);
    	}
    
    	return $this->fetchAll($select);
    } 
   
    public function getAgentTotalForCommReversal($param, $status = array()) {
    	$date = isset($param['date']) ? $param['date'] : '';
    	$agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
    
    	$select = $this->select();
    	$select->from(DbTable::TABLE_TXN_AGENT . " as txn", array('sum(txn.amount) as tot_amount'));
    
    	if ($agentId > 0) {
    		$select->where("txn.agent_id = ? ", $agentId);
    	}

    	$select->where("txn.txn_type = ? ", TXNTYPE_AGENT_COMMISSION_REVERSAL);
    	$select->where("DATE(txn.date_created) = ?", $date);
    
    	$data=$this->fetchRow($select);
    	return $data['tot_amount'];
    }
    
    public function getAgentTotalForComm($param, $status = array()) {
    	$date = isset($param['date']) ? $param['date'] : '';
    	$agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
    
    	$select = $this->select();
    	$select->from(DbTable::TABLE_TXN_AGENT . " as txn", array('sum(txn.amount) as tot_amount'));
    
    	if ($agentId > 0) {
    		$select->where("txn.agent_id = ? ", $agentId);
    	}

    	$select->where("txn.txn_type = ? ", TXNTYPE_AGENT_COMMISSION);
    	$select->where("DATE(txn.date_created) = ?", $date);
    
    	$data=$this->fetchRow($select);
    	return $data['tot_amount'];
    }

} 
