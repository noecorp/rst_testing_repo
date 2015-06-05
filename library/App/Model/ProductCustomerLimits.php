<?php

/**
 * MVC that manages the MVC releated stuff for defining
 * the MVC method in the application
 *
 * @package Core
 * @copyright transerv
 */
class ProductCustomerLimits extends App_Model {

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
    protected $_name = DbTable::TABLE_PRODUCT_CUSTOMER_LIMITS;

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

    public function getPurseDetailsbyBankIdProductId($productId, $bankId) {

        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(DbTable::TABLE_PRODUCT_CUSTOMER_LIMITS);
        $select->where('product_id= ?', $productId);
        $select->where('bank_id= ?', $bankId);
        $select->where('status= ?', STATUS_ACTIVE);

        $purse = $this->fetchAll($select);

        return $purse;
    }

    public function getPurseIdByPurseCode($purseCode) {

        $select = $this->_db->select();
        $select->from(DbTable::TABLE_PRODUCT_CUSTOMER_LIMITS);
        $select->where('code= ?', $purseCode);
        $select->where('status= ?', STATUS_ACTIVE);
        $purse = $this->_db->fetchRow($select);

        return $purse;
    }

    public function getLimitDetailsbyProductId($productId, $page = 1) {
        $select = $this->getLimitDetailsbyProductIdSql($productId);
        return $this->_paginate($select, $page, TRUE);
    }
    
    private function getLimitDetailsbyProductIdSql($productId, $orderBy = ''){
        $select = $this->select();
        $select->from(DbTable::TABLE_PRODUCT_CUSTOMER_LIMITS);
        $select->where('product_id= ?', $productId);
        $select->where('status= ?', STATUS_ACTIVE);
        if($orderBy != ''){
            $select->order($orderBy);
        }
        return $select;
    }

       public function insertLog($details = array()) {
        return $this->_db->insert(DbTable::TABLE_LOG_PRODUCT_CUSTOMER_LIMITS,$details);
    }
    
    public function getLimitDetailsbyPurseId($purseId) {
        $select = $this->select();
        $select->from(DbTable::TABLE_PRODUCT_CUSTOMER_LIMITS);
        $select->where('id= ?', $purseId);
        $select->where('status= ?', STATUS_ACTIVE);
        return $this->fetchRow($select);
    }
    
    
      public function getPurseList($productId) {
        $select = $this->getPurseDetailsbyProductIdSql($productId);
        $purse = $this->fetchAll($select);
        $dataArray = array();
        $dataArray[''] = 'Select Wallet';
        foreach ($purse as $id => $val) {
            $dataArray[$val['id']] = $val['name'];
        }
       
        return $dataArray;
    }
    
    public function getProductPurseDetails($productId, $orderBy = '') {
        $select = $this->getPurseDetailsbyProductIdSql($productId, $orderBy);
        return $this->_db->fetchAll($select);
    }
    
}