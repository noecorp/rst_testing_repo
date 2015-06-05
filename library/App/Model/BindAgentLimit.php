<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class BindAgentLimit extends App_Model
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
    protected $_name = DbTable::TABLE_BIND_AGENT_LIMIT;
    
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
     * Overrides findById() in App_Model
     * 
     * @param int $userId 
     * @access public
     * @return array
     */
    public function findById($Id, $force = FALSE){
        if (!is_numeric($Id)) {
            return array();
        }
        
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BIND_AGENT_LIMIT);
        $select->setIntegrityCheck(false);
        $select->where('id = ?', $Id);
       
       
        return $this->fetchRow($select);
    }
    
    /*
     * agent limit details
     */
     public function getAgentLimitDetails($agentId)
     {
         $curdate = new Zend_Db_Expr('NOW()');
         $select = $this->_db->select()
                ->from(DbTable::TABLE_BIND_AGENT_LIMIT." as a", array('id', 'agent_id', 'agent_limit_id', 'date_start', 'date_end', 'by_ops_id', 'by_agent_id', 'date_created', 'status'))
                ->joinLeft(DbTable::TABLE_AGENT_LIMIT.' as b',"b.id = a.agent_limit_id", array('id', 'name', 'currency', 'cnt_out_max_txn_daily', 'cnt_out_max_txn_monthly', 'cnt_out_max_txn_yearly', 'limit_out_max_daily', 'limit_out_max_monthly', 'limit_out_max_yearly', 'limit_out_min_txn', 'limit_out_max_txn', 'by_ops_id', 'date_created', 'status'))
                ->where("a.agent_id = ?", $agentId)
                ->where("$curdate >= a.date_start AND ($curdate <= a.date_end OR a.date_end = '0000-00-00' OR a.date_end is NULL)");
          return $this->_db->fetchRow($select);
     }
    
    /*
     * agent limit details
     */
     public function getLimitByAgentId($id)
     {
         $select = $this->select()
                //->from(DbTable::TABLE_BIND_AGENT_LIMIT." as a")
                ->where("agent_id = ?", $id);
          return $this->fetchRow($select);
     }
    
   
}