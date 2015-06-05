<?php

/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */
class Corp_Kotak_CustomersLog extends Corp_Kotak {

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
    protected $_name = DbTable::TABLE_KOTAK_CORP_LOG_CARDHOLDER;

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
    
    
     public function getCardholderStatus($customer_id = 0, $byType = BY_OPS,$page = 1) {
        $select = $this->select()
                 ->setIntegrityCheck(false)
                ->from(DbTable::TABLE_KOTAK_CORP_LOG_CARDHOLDER .' as kc')
                ->joinLeft(DbTable::TABLE_AGENTS .' as a',"kc.by_id = a.id AND kc.by_type = '".BY_MAKER."'",array('concat(a.first_name," ",a.last_name) as m_name'))
                ->joinLeft(DbTable::TABLE_OPERATION_USERS .' as ops',"kc.by_id = ops.id AND kc.by_type = '".BY_CHECKER."'",array('concat(ops.firstname," ",ops.lastname) as c_name'))
                ->joinLeft(DbTable::TABLE_BANK_USER .' as bank',"kc.by_id = bank.id AND kc.by_type = '".BY_AUTHORIZER."'",array('concat(bank.first_name," ",bank.last_name) as a_name'))
                ->where('product_customer_id =?', $customer_id);
                if($byType == BY_OPS){
                $select->where("by_type IN ('".BY_MAKER."','".BY_CHECKER."','".BY_AUTHORIZER."')");
                }
                else if($byType == BY_BANK){
                  $select->where("by_type IN ('".BY_CHECKER."','".BY_AUTHORIZER."')");  
                }
                else if($byType == BY_MAKER){
                  $select->where("by_type IN ('".BY_CHECKER."','".BY_MAKER."')");  
                }
                $select->order('kc.date_created DESC');
        $status = $this->_paginate($select, $page, TRUE);
        
        return $status;
  
}
}