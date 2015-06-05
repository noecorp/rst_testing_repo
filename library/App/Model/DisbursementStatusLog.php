<?php

/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */
class DisbursementStatusLog extends App_Model {
    
    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_BOI_DISBURESEMENT_STATUS_LOG;
    
    
    public function insertLogData($dataArr) {
        if(!empty($dataArr)) {
          return $this->_db->insert(DbTable::TABLE_BOI_DISBURESEMENT_STATUS_LOG,$dataArr);
        } 
        return false;
    }
    

}