<?php

/**
 * MVC that manages the MVC releated stuff for defining
 * the MVC method in the application
 *
 * @package Core
 * @copyright transerv
 */
class MasterPurse extends App_Model {

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
    protected $_name = DbTable::TABLE_PURSE_MASTER;

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
        $select->from(DbTable::TABLE_PURSE_MASTER, array('id'));
        $select->where('product_id= ?', $productId);
        $select->where('bank_id= ?', $bankId);
        $select->where('status= ?', STATUS_ACTIVE);

        return $this->fetchAll($select);
    }

    public function getPurseIdByPurseCode($purseCode) {

        $select = $this->_db->select();
        $select->from(DbTable::TABLE_PURSE_MASTER);
        $select->where('code= ?', $purseCode);
        $select->where('status= ?', STATUS_ACTIVE);
        return $this->_db->fetchRow($select);
    }
    
    public function getPurseIdByPurseCodeAPI($purseCode,$productID='') {

        $select = $this->_db->select();
        $select->from(DbTable::TABLE_PURSE_MASTER, array('id', 'product_id', 'debit_api_cr', 'payable_ac_id','is_virtual','code'));
        $select->where('code= ?', $purseCode);
        if($productID !=''){
        $select->where('product_id= ?', $productID);
        }
        $select->where('status= ?', STATUS_ACTIVE);
        return $this->_db->fetchRow($select);
    }

    public function getPurseDetailsbyProductId($productId, $page = 1) {
        $select = $this->getPurseDetailsbyProductIdSql($productId);
        return $this->_paginate($select, $page, TRUE);
    }
    
    private function getPurseDetailsbyProductIdSql($productId, $orderBy = ''){
        $select = $this->select();
        $select->from(DbTable::TABLE_PURSE_MASTER);
        $select->where('product_id= ?', $productId);
        $select->where('status= ?', STATUS_ACTIVE);
        if($orderBy != ''){
            $select->order($orderBy);
        }
        return $select;
    }

       public function insertLog($details = array()) {
        return $this->_db->insert(DbTable::TABLE_LOG_PURSE_MASTER,$details);
    }
    
    public function getPurseDetailsbyPurseId($purseId) {
        $select = $this->select();
        $select->from(DbTable::TABLE_PURSE_MASTER);
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
    
    public function getPurseDetailsbyProduct($productId, $code) {
        $select = $this->select();
        $select->from(DbTable::TABLE_PURSE_MASTER);
        $select->where('product_id= ?', $productId);
        $select->where('code= ?', $code);
        $select->where('status= ?', STATUS_ACTIVE);
        //echo $select; exit; 
        return $this->fetchRow($select);
    }
       
   
    public function getProductPurseBasicDetails($productId, $orderBy) {
        $select = $this->select()

                ->from(DbTable::TABLE_PURSE_MASTER, array('id', 'code','allow_expiry','is_virtual'))
                ->where('product_id= ?', $productId)
                ->where('status= ?', STATUS_ACTIVE)
                ->order($orderBy);
        return $this->_db->fetchAll($select);
    }
    
    public function getProductPurseCode($productId) {
        $select = $this->select()
                ->from(DbTable::TABLE_PURSE_MASTER, array('code'))
                ->where('product_id= ?', $productId)
                ->where('status= ?', STATUS_ACTIVE);
        return $this->_db->fetchAll($select);
    }
    
     public function getProductPurse($productId) {
        $select = $this->select()
                ->from(DbTable::TABLE_PURSE_MASTER, array('code'))
                ->where('product_id = ?', $productId)
                ->where('status = ?', STATUS_ACTIVE);
        $res =  $this->_db->fetchAll($select);
        
   
        $codeArr = array();
        $i = 0;
        foreach($res as $value){
            $codeArr[$i] = strtolower($value['code']);
           $i++;
        }
        
        return $codeArr;
    }
//    public function getPurseLoadInfo($param) {
//
//        $select = $this->_db->select();
//        $select->from(DbTable::TABLE_PURSE_MASTER, array('code','id','allow_expiry'));
//        $select->where('product_id= ?', $param['product_id']);
//        $select->where('allow_expiry= ?', FLAG_YES);
//        $purse = $this->_db->fetchAll($select);
//
//        if(!empty($purse)) {
//            return $purse;
//        } else {
//            return FALSE;
//        }
//    }
    
    public function getProductWalletPurseBasicDetails($params, $orderBy) {
        $walletCode = isset($params['wallet_code']) ? $params['wallet_code'] : '';
        $productId = isset($params['product_id']) ? $params['product_id'] : '';
        $select = $this->select()
                ->from(DbTable::TABLE_PURSE_MASTER, array('id', 'code','allow_expiry','is_virtual'))
                ->where('product_id= ?', $productId);
        if($walletCode!=''){
        $select->where('code= ?', $walletCode);
        }
        $select->where('status= ?', STATUS_ACTIVE)
                ->order($orderBy);
        return $this->_db->fetchAll($select);
    }
    
    public function getPurseInfo($param) {

        $select = $this->_db->select();
        $select->from(DbTable::TABLE_PURSE_MASTER, array('code','id'));
        $select->where('product_id= ?', $param['product_id']);
        $select->where('allow_remit= ?', FLAG_YES);
        $purse = $this->_db->fetchAll($select);

        if(!empty($purse)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    public function getPurseLoadInfo($param) {

        $select = $this->_db->select();
        $select->from(DbTable::TABLE_PURSE_MASTER, array('code','id','allow_expiry'));
        $select->where('product_id= ?', $param['product_id']);
        $select->where('allow_expiry= ?', FLAG_YES);
        $select->where('status= ?', STATUS_ACTIVE);
        $purse = $this->_db->fetchAll($select);

        if(!empty($purse)) {
            return $purse;
        } else {
            return FALSE;
        }
    }
    

//    public function getProductWalletPurseBasicDetails($params, $orderBy) {
//        $walletCode = isset($params['wallet_code']) ? $params['wallet_code'] : '';
//        $productId = isset($params['product_id']) ? $params['product_id'] : '';
//        $select = $this->select()
//                ->from(DbTable::TABLE_PURSE_MASTER, array('id', 'code','allow_expiry'))
//                ->where('product_id= ?', $productId);
//        if($walletCode!=''){
//        $select->where('code= ?', $walletCode);
//        }
//        $select->where('status= ?', STATUS_ACTIVE)
//                ->order($orderBy);
//        return $this->_db->fetchAll($select);
//    }

    public function getRemitPurseInfo($productId,$orderBy='id') {

        $select = $this->_db->select();
        $select->from(DbTable::TABLE_PURSE_MASTER, array('code','id','allow_expiry','allow_remit'));
        $select->where('product_id= ?', $productId);
        $select->where('allow_remit= ?', FLAG_YES);
        $select->order($orderBy);
        $purse = $this->_db->fetchAll($select);

        if(!empty($purse)) {
            return $purse;
        } else {
            return FALSE;
        }
    }
    
    public function getProductWalletBasicDetailsByIsVirtual($params) {;
        $productId = isset($params['product_id']) ? $params['product_id'] : '';
        $isVirtual = isset($params['is_virtual']) ? $params['is_virtual'] : '';
        
        $select = $this->select()
                ->from(DbTable::TABLE_PURSE_MASTER, array('id'))
                ->where('product_id IN ('. $productId . ')');
        if($isVirtual!=''){
        $select->where('is_virtual= ?', $isVirtual);
        }
        $select->where('status= ?', STATUS_ACTIVE);
        return $this->_db->fetchAll($select);
    }
    
    public function getProductPurseListsNovirtual($productId, $orderBy) {
        $select = $this->select()

                ->from(DbTable::TABLE_PURSE_MASTER, array('id', 'code','allow_expiry','is_virtual'))
                ->where('product_id= ?', $productId)
                ->where('status= ?', STATUS_ACTIVE)
		->where('is_virtual= ?', FLAG_NO)
                ->order($orderBy);
        return $this->_db->fetchAll($select);
    }
}