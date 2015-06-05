<?php
/**
 * LogStatus that manages the Log status updations in the application
 * @package Core
 * @copyright transerv
 */

class LogMaster extends App_Model
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
    protected $_name = DbTable::TABLE_LOG_MASTER;
    
      
    /* log function will add change status log details in t_change_status_log 
     * it will accept the many params in $param array argument, e.g.. status new, status old, remarks etc....
     */
    
    public function insertlog($param)
    {
       try {
        
        return $this->_db->insert($this->_name,$param); 
       }
       catch(Exception $e ) {
           //Log Error
           return false;
        }
    }
}