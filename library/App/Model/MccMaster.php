<?php

/**
 * Model that manages mcc master & bind purse mccs
 * 
 * @package Operation_Models
 * @copyright transerv
 */
class MccMaster extends App_Model {

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
    protected $_name = DbTable::TABLE_MCC_MASTER;

    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Privilege';

    /* get Mcc Codes for purse */
    public function getPurseMcc($purseMasterId = 0)
    {
       if($purseMasterId > 0)
       {
           $select = $this->_db->select()
                ->from(DbTable::TABLE_BIND_PURSE_MCC)
                ->where('purse_master_id = ?', $purseMasterId);  
            $rs = $this->_db->fetchAll($select);      
            return $rs;
       }
       return 0;
    }
   
    
}