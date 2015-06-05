<?php

/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */
class Corp_Kotak_DeliveryFlag extends Corp_Kotak {

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
    protected $_name = DbTable::TABLE_DELIVERY_FLAG_MASTER;

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
    
   public function insertDeliveryFile($dataArr) {
        $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $dataArr[0] = new Zend_Db_Expr("AES_ENCRYPT('".$dataArr[0]."','".$encryptionKey."')"); 
        $data = array(
            'card_number' => $dataArr[0],
            'card_pack_id' => $dataArr[1],
            'member_id' => $dataArr[2],
            'delivery_date' => $dataArr[3],
            'delivery_status' => $dataArr[4],
            'batch_name' => $dataArr['batch_name'],
            'product_id' => $dataArr['product_id']
        );
        $this->_db->insert(DbTable::TABLE_DELIVERY_FLAG_MASTER, $data);

        return TRUE;
    }
    
       public function checkBatchName($batchName, $productId = 0) {
      
         $select = $this->select()
                ->where("batch_name = ?",$batchName);
         if($productId > 0) {
             $select->where("product_id = ?",$productId);
         }
        
        $res = $this->fetchRow($select);
        if(!isset($res) && empty($res)){
           return TRUE; 
        }
        else
        {
            return FALSE;
        }
    }
    
     
     public function getBatchName() {
      
         $select = $this->select()
                   ->distinct(TRUE)
                    ->from(DbTable::TABLE_DELIVERY_FLAG_MASTER,array('batch_name'));
        $res = $this->fetchAll($select);
        
        $dataArray = array();
        $dataArray[''] = 'Select File Name';
        foreach ($res as $val) {
            $dataArray[$val['batch_name']] = $val['batch_name'];
        }
        return $dataArray;   
        
    }
    
    
    
    public function findByBatchName ($page = 1,$param,$paginate = NULL, $force = FALSE){
        $productId = isset($param['product_id']) && $param['product_id'] > 0 ? $param['product_id'] : '';
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`df`.`card_number`,'".$decryptionKey."') as card_number"); 
        $details = $this->select()
                ->setIntegrityCheck(false) 
                ->from(DbTable::TABLE_DELIVERY_FLAG_MASTER .' as df',array(
                    'id', 'product_id',$card_number, 'card_pack_id', 'member_id', 'delivery_date', 'delivery_status', 'batch_name', 'date_ecs', 'failed_reason','status'
               ))
                ->joinLeft(DbTable::TABLE_PRODUCTS .' as p',"df.product_id = p.id",array('p.name'))
                ->where("df.batch_name =?",$param['batchname']);
        if($productId != ''){
            $details->where("df.product_id =?" , $productId);
        } 
        return $this->_paginate($details, $page, $paginate);
    }
    
    public function getBatchNameSql($param = array()) {
         
        $productId = isset($param['product_id']) && $param['product_id'] > 0 ? $param['product_id'] : '';
        $select = $this->select();
        $select->distinct(TRUE);
        $select->from(DbTable::TABLE_DELIVERY_FLAG_MASTER ." as fl",array('fl.batch_name'));
         if($productId != ''){
             $select->where("fl.product_id =?" , $productId);
        }
        $res = $this->fetchAll($select);
        
        
        return $res;   
        
    }
}