<?php

/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */
class Corp_Boi_CardMapping extends Corp_Boi {

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
    protected $_name = DbTable::TABLE_BOI_CARD_MAPPING;

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
    
   public function insertCardMappingFile($dataArr) { 
	if(($dataArr[2] != 0) &($dataArr[2] != '')){
	    $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	    $dataArr[2] = new Zend_Db_Expr("AES_ENCRYPT('".$dataArr[2]."','".$encryptionKey."')"); 
	}
	
       $data = array(
           'boi_account_number' => $dataArr[0],
           'boi_customer_id' => $dataArr[1],
           'card_number' => $dataArr[2],
           'batch_name' => $dataArr['batch_name'],
           'product_id' => $dataArr['product_id'],
           'date_created' => new Zend_Db_Expr('NOW()')
        );
        $this->insert($data);

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
                    ->from(DbTable::TABLE_BOI_CARD_MAPPING,array('batch_name'))
                    ->order("date_created DESC");
        $res = $this->fetchAll($select);
        
        $dataArray = array();
        $dataArray[''] = 'Select File Name';
        foreach ($res as $val) {
            $dataArray[$val['batch_name']] = $val['batch_name'];
        }
        return $dataArray;   
        
    }
    
    
    public function findByBatchName ($page = 1,$batchName,$paginate = NULL, $force = FALSE){
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	$card_number = new Zend_Db_Expr("AES_DECRYPT(`df`.`card_number`,'".$decryptionKey."') as card_number");
	$details = $this->select()
		->setIntegrityCheck(false)
		->from(DbTable::TABLE_BOI_CARD_MAPPING .' as df', array('id','product_id',$card_number,'card_pack_id','boi_account_number','boi_customer_id','batch_name','date_created','date_ecs','failed_reason','date_failed','status'))
		->joinLeft(DbTable::TABLE_PRODUCTS .' as p',"df.product_id = p.id",array('name'))
		->where("batch_name ='".$batchName."'");
	return $this->_paginate($details, $page, $paginate);
    }
    
    
    
     public function getCardMappingStatus ($page = 1,$data,$paginate = NULL, $force = FALSE){
                //Enable DB Slave
                $this->_enableDbSlave();
                $from_date = isset($data['from']) ? $data['from'] : '';
                $to_date = isset($data['to']) ? $data['to'] : '';
                $batchname = isset($data['batchname']) ? $data['batchname'] : '';
		$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
		$card_number = new Zend_Db_Expr("AES_DECRYPT(`df`.`card_number`,'".$decryptionKey."') as card_number");
		
                $details = $this->select();
                $details->setIntegrityCheck(false); 
                $details->from(DbTable::TABLE_BOI_CARD_MAPPING .' as df', array('id','product_id',$card_number,'card_pack_id','boi_account_number','boi_customer_id','batch_name','date_created','date_ecs','failed_reason','date_failed','status'));
                $details->joinLeft(DbTable::TABLE_PRODUCTS .' as p',"df.product_id = p.id",array('name'));
                
                if ($from_date != '') {
                  $details->where("df.date_created >= '" . $data['from'] . "'");
                }
                if ($to_date != '') {
                  $details->where("df.date_created <= '" . $data['to'] . "'");
                }
                if ($batchname != '') {
                  $details->where("df.batch_name ='".$data['batchname']."'");
                } 
                //Disable DB Slave
                $this->_disableDbSlave();  
                return $this->_paginate($details, $page, $paginate);
        
        
    }
    
    
   public function getPendingRecrods($productId, $status = '', $limit = '') {
       $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	$card_number = new Zend_Db_Expr("AES_DECRYPT(`df`.`card_number`,'".$decryptionKey."') as card_number");
       $sql = $this->select()
                ->from(DbTable::TABLE_BOI_CARD_MAPPING . ' as df', array('id','product_id',$card_number,'card_pack_id','boi_account_number','boi_customer_id','batch_name','date_created','date_ecs','failed_reason','date_failed','status'))
                ->where("df.product_id=?", $productId);
        if (!empty($status)) {
            $sql->where("df.batch_name ='" . $status . "'");
        } else {
            $sql->where("df.status =?", STATUS_PENDING);
        }
        $sql->order('df.id DESC');
        if($limit != ''){
            $sql->limit($limit);
        }
        
        return $this->fetchAll($sql);
    }

     public function updateRecords($params, $id) {
      $this->update($params,"id = $id");
      return TRUE;
    }
    
    public function getCustomerCount($startDate, $endDate, $status = '', $limit = '') {
       $sql = $this->select()
                ->from(DbTable::TABLE_BOI_CARD_MAPPING . ' as df', array('count(*) as mapped_customer'));
        if (!empty($status)) {
            $sql->where("status ='" . $status . "'");
        }
        $sql->where("date_created BETWEEN '".$startDate."' AND '".$endDate."'");
        if($limit != ''){
            $sql->limit($limit);
        }
        //echo $sql; exit;
        return $this->fetchRow($sql);
    }
    
    public function exportgetCardMappingStatus ($dataArr){
                $from_date = isset($dataArr['from_date']) ? $dataArr['from_date'] : '';
                $to_date = isset($dataArr['to_date']) ? $dataArr['to_date'] : '';
                $batchname = isset($dataArr['batchname']) ? $dataArr['batchname'] : '';
		$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
		$card_number = new Zend_Db_Expr("AES_DECRYPT(`df`.`card_number`,'".$decryptionKey."') as card_number");
		
                $details = $this->select();
                $details->setIntegrityCheck(false);  
		$details->from(DbTable::TABLE_BOI_CARD_MAPPING .' as df', array('id','product_id',$card_number,'card_pack_id','boi_account_number','boi_customer_id','batch_name','date_created','date_ecs','failed_reason','date_failed','status'));
                $details->joinLeft(DbTable::TABLE_PRODUCTS .' as p',"df.product_id = p.id",array('name'));
               
       
                if ($from_date != '') {
                  $details->where("df.date_created >= '" . $from_date . "'");
                }
                if ($to_date != '') {
                  $details->where("df.date_created <= '" . $to_date . "'");
                }
                if ($batchname != '') {
                  $details->where("df.batch_name ='".$batchname."'");
                } 
        $res = $this->fetchAll($details);
                
        $retData = array();
            foreach ($res as $key => $data) {
                $retData[$key]['name'] = $data['name'];
                $retData[$key]['card_number'] = Util::maskCard($data['card_number']);
                $retData[$key]['boi_account_number'] = $data['boi_account_number'];
                $retData[$key]['boi_customer_id'] = $data['boi_customer_id'];
                $retData[$key]['date_ecs'] = $data['date_ecs'];
                $retData[$key]['failed_reason'] = $data['failed_reason'];
                $retData[$key]['status'] = $data['status'];
                
                
        }
        return $retData;
    }
}