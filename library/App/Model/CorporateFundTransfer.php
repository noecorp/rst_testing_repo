<?php
/**
 * Model that manages Agent to agent fund transfer
 *
 * @package Operation_Models
 * @copyright transerv
 */

class CorporateFundTransfer extends App_Model
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
    protected $_name = DbTable::TABLE_CORPORATE_FUND_TRANSFER;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Privilege';
    
    /**
     * Define the relationship with another tables
     *
     * @var array
     */
    protected $_referenceMap = array(
        
    );
    
     
    
    
    /**
     * Overrides getAll() in App_Model
     * 
     * @param int $page 
     * @access public
     * @return Zend_Paginator
     */
    
    public function getCorporateFunding($corporateId, $txnCorporateId,$page =1){
        $sql = $this->select()              
                ->from($this->_name." as af")
                ->setIntegrityCheck(false)
                ->joinLeft(DbTable::TABLE_CORPORATE_USER." as a", "af.txn_corporate_id=a.id",array('concat(a.first_name," ",a.last_name) as corporate_name'))
                ->joinLeft(DbTable::TABLE_TRANSACTION_TYPE." as tt", "af.txn_type=tt.typecode",array('name as transaction_name'))
                ->where('af.corporate_id=?',$corporateId)
                ->where('af.txn_corporate_id=?',$txnCorporateId)
                ->where('af.status=?',STATUS_SUCCESS)
                ->order('af.date_created desc');
          //echo $sql; exit;      
        return $this->_paginate($sql, $page, NULL);        
                
    }
    
 
    public function getAgentTotalFundTrfrCr($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $txnType = isset($param['txn_type']) ? $param['txn_type'] : '';
        $onDate = isset($param['on_date']) ? $param['on_date'] : FALSE;
        if ($agentId > 0) {

            $select = $this->select()
                    ->from($this->_name, array('sum(amount) as total_agent_fundtrfr_amount'))
                    ->where('status = ?', STATUS_SUCCESS);
            if($txnType == TXNTYPE_AGENT_TOAGENT_FUND_TRANSFER) {
                $select->where('txn_type = ?', TXNTYPE_AGENT_TOAGENT_FUND_TRANSFER);
                $select->where('txn_agent_id = ?', $agentId);
            }
            elseif($txnType == TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL) {
                $select->where('txn_type = ?', TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL);
                $select->where('agent_id = ?', $agentId);
            }
            if ($onDate) {
                $date = isset($param['date']) ? $param['date'] : '';
                $select->where('DATE(date_created) =?', $date);
            } else {
                $fromDate = isset($param['from']) ? $param['from'] : '';
                $toDate = isset($param['to']) ? $param['to'] : '';
                $select->where('date_created >= ?', $fromDate);
                $select->where('date_created <= ?', $toDate);
            }
            return $this->fetchRow($select);
        }
        else
            return 0;
    }
    
    public function getAgentTotalFundTrfrDr($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $txnType = isset($param['txn_type']) ? $param['txn_type'] : '';
        $onDate = isset($param['on_date']) ? $param['on_date'] : FALSE;
        if ($agentId > 0) {

            $select = $this->select()
                    ->from($this->_name, array('sum(amount) as total_agent_fundtrfr_amount'))
                    ->where('status = ?', STATUS_SUCCESS);
            if($txnType == TXNTYPE_AGENT_TOAGENT_FUND_TRANSFER) {
                $select->where('txn_type = ?', TXNTYPE_AGENT_TOAGENT_FUND_TRANSFER);
                $select->where('agent_id = ?', $agentId);
            }
            elseif($txnType == TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL) {
                $select->where('txn_type = ?', TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL);
                $select->where('txn_agent_id = ?', $agentId);
            }
            if ($onDate) {
                $date = isset($param['date']) ? $param['date'] : '';
                $select->where('DATE(date_created) =?', $date);
            } else {
                $fromDate = isset($param['from']) ? $param['from'] : '';
                $toDate = isset($param['to']) ? $param['to'] : '';
                $select->where('date_created >= ?', $fromDate);
                $select->where('date_created <= ?', $toDate);
            }
            
            return $this->fetchRow($select);
        }
        else
            return 0;
    }
    
    public function getAgentFundsTrfrCr($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $txnType = isset($param['txn_type']) ? $param['txn_type'] : '';
        $onDate = isset($param['on_date']) ? $param['on_date'] : FALSE;
        if ($agentId > 0) {

            $select = $this->select()
                    ->from($this->_name." AS a", array('amount', 'txn_code', 'date_created'))
                    ->setIntegrityCheck(false) 
                    ->where('a.status = ?', STATUS_SUCCESS);
            if($txnType == TXNTYPE_AGENT_TOAGENT_FUND_TRANSFER) {
                $select->joinLeft(DbTable::TABLE_AGENTS ." AS b", "a.agent_id = b.id", array('first_name', 'last_name'));
                $select->where('a.txn_type = ?', TXNTYPE_AGENT_TOAGENT_FUND_TRANSFER);
                $select->where('a.txn_agent_id = ?', $agentId);
            }
            elseif($txnType == TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL) {
                $select->joinLeft(DbTable::TABLE_AGENTS ." AS b", "a.txn_agent_id = b.id", array('first_name', 'last_name'));
                $select->where('a.txn_type = ?', TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL);
                $select->where('a.agent_id = ?', $agentId);
            }
            if ($onDate) {
                $date = isset($param['date']) ? $param['date'] : '';
                $select->where('DATE(a.date_created) =?', $date);
            } else {
                $fromDate = isset($param['from']) ? $param['from'] : '';
                $toDate = isset($param['to']) ? $param['to'] : '';
                $select->where('a.date_created >= ?', $fromDate);
                $select->where('a.date_created <= ?', $toDate);
            }
            return $this->fetchAll($select);
        }
        else
            return 0;
    }
    
    public function getAgentFundsTrfrDr($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $txnType = isset($param['txn_type']) ? $param['txn_type'] : '';
        $onDate = isset($param['on_date']) ? $param['on_date'] : FALSE;
        if ($agentId > 0) {

            $select = $this->select()
                    ->from($this->_name . " AS a", array('amount', 'txn_code', 'date_created'))
                    ->setIntegrityCheck(false)  
                    ->where('a.status = ?', STATUS_SUCCESS);
            if($txnType == TXNTYPE_AGENT_TOAGENT_FUND_TRANSFER) {
                $select->joinLeft(DbTable::TABLE_AGENTS ." AS b", "a.txn_agent_id = b.id", array('first_name', 'last_name'));
                $select->where('a.txn_type = ?', TXNTYPE_AGENT_TOAGENT_FUND_TRANSFER);
                $select->where('a.agent_id = ?', $agentId);
            }
            elseif($txnType == TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL) {
                $select->joinLeft(DbTable::TABLE_AGENTS ." AS b", "a.agent_id = b.id", array('first_name', 'last_name'));
                $select->where('a.txn_type = ?', TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL);
                $select->where('a.txn_agent_id = ?', $agentId);
            }
            if ($onDate) {
                $date = isset($param['date']) ? $param['date'] : '';
                $select->where('DATE(a.date_created) =?', $date);
            } else {
                $fromDate = isset($param['from']) ? $param['from'] : '';
                $toDate = isset($param['to']) ? $param['to'] : '';
                $select->where('a.date_created >= ?', $fromDate);
                $select->where('a.date_created <= ?', $toDate);
            }
            return $this->fetchAll($select);
        }
        else
            return 0;
    }
    
    
    public function getCorporateFundsTransferDetails($param) {
        $corporateId = isset($param['corporate_id']) ? $param['corporate_id'] : '';
        $txnType = isset($param['txn_type']) ? $param['txn_type'] : '';
        $fromDate = isset($param['from']) ? $param['from'] : '';
        $toDate = isset($param['to']) ? $param['to'] : '';
        
        if ($agentId > 0) {

            $select = $this->select()
                    ->from($this->_name . " AS a", array('a.amount as tr_amount', 'a.txn_code', 'a.date_created','a.status'))
                    ->setIntegrityCheck(false)  
                    ->where('a.status = ?', STATUS_SUCCESS);
                $select->joinLeft(DbTable::TABLE_CORPORATE_USER ." AS b", "a.corporate_id = b.id", array('first_name', 'last_name'));
                $select->where('a.txn_type = ?', $txnType);
                $select->where('a.corporate_id = ?', $corporateId);
                $select->where('a.date_created >= ?', $fromDate);
                $select->where('a.date_created <= ?', $toDate);
          
                return $this->fetchAll($select);
            
        }
        else
            return array();
    }
       public function getHeadCorporateFundsTransferDetails($param) {
        $corporateId = isset($param['corporate_id']) ? $param['v'] : '';
        $txnType = isset($param['txn_type']) ? $param['txn_type'] : '';
        $fromDate = isset($param['from']) ? $param['from'] : '';
        $toDate = isset($param['to']) ? $param['to'] : '';
        
        if ($agentId > 0) {

            $select = $this->_db->select()
                    ->from($this->_name . " AS a", array('a.amount as tr_amount', 'a.txn_code', 'a.date_created','a.status'))
                    ->where('a.status = ?', STATUS_SUCCESS);
                $select->joinLeft(DbTable::TABLE_CORPORATE_USER ." AS b", "a.txn_corporate_id = b.id", array('first_name', 'last_name'));
                $select->where('a.txn_type = ?', $txnType);
                $select->where('a.txn_corporate_id = ?', $corporateId);
                $select->where('a.date_created >= ?', $fromDate);
                $select->where('a.date_created <= ?', $toDate);
              
                return $this->_db->fetchAll($select);
            
        }
        else
            return array();
    }
}