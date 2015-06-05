<?php
/**
 * Model that manages the product limits mvc 
 *
 * @package Operation_Models
 * @copyright transerv
 */

class Mvc_Productlimit extends Mvc
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
    protected $_name = DbTable::TABLE_PRODUCT_LIMIT;
    
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
    protected $_referenceMap = array();   
     
    /**
     * Retrieves all the products attached to
     * the specified master product
     * 
     * @param integer $resourceId
     * @access public
     * @return void
     */
    
    public function findAll ($pid = 0,$plid = 0,$page = 1,$paginate = NULL,$force = FALSE){
       
       $details = $this->select()
                ->from(DbTable::TABLE_PRODUCT_LIMIT.' as pl',array(
                    'limit_out_max_daily','limit_out_max_monthly','limit_out_max_yearly','limit_out_first_load',
                    'limit_out_min_txn','limit_out_max_txn','name','currency','cnt_out_max_txn_daily','cnt_out_max_txn_monthly','cnt_out_max_txn_yearly','product_limit_code','id'));
       $details->setIntegrityCheck(false);  
     
       if($pid > 0){
               $details->where("pl.product_id=$pid");
       }
       if($plid > 0){
               $details->where("pl.id=$plid");
       }
               $details->where("pl.status='".STATUS_ACTIVE."'");
               $details->order('name Asc');
       
     // echo $details->__toString(); exit;       
                
       
       return $this->_paginate($details, $page, $paginate);
        
        
    }
   
  
  public function findByPlId($plid){
      $select = $this->select()
                ->from(DbTable::TABLE_PRODUCT_LIMIT.' as pl',array('pl.id','pl.product_id'))
                ->setIntegrityCheck(false)
                ->joinLeft(DbTable::TABLE_PRODUCTS.' as p',"p.id=pl.product_id",array('p.name','p.ecs_product_code','p.id'));
      $array = $this->fetchRow($select);
      return $array;
  }
  public function add($data){
     
   $chkname = $this->checkname($data['name'],$data['product_id']);
     if (!empty($chkname)){
       throw new Exception ('Product Limit name exists!');
       exit;
     }
   else {
       
       $res =  $this->insert($data);
        return 'added';
        
   }
  } 
  
  public function edit($data,$name){
    
   $chkname = $this->checkname($data['name'],$data['product_id']);
     if (!empty($chkname) && ($data['name'] != $name)){
       throw new Exception ('Product Limit name exists!');
       exit;
     }
   else {
      
       $res =  $this->insert($data);
        return 'edited';
        
   }
  } 
  
  public function editupdate($lastId){
     
       
       $editdata = array('status' => STATUS_INACTIVE );
       //print_r($editdata);exit;
       $res =  $this->update($editdata,"id=$lastId");
       
        return 'updated';
        
  
  } 
   
   public function delete($id){
      $data = array('status' => STATUS_DELETED );
      
       $res =  $this->update($data,"id=$id");
        return 'deleted';
        
   }
    
  
  public function getLastProductLimit(){
      $select = $this->select()
               ->from($this)
               ->order('id desc')
               ->limit('1');
      //echo $select->__toString();      exit;
     return $limitCodeArr = $this->fetchRow($select);
  }
  
  
  public function checkname($name,$pid){
       $select = $this->select()    
                ->from(DbTable::TABLE_PRODUCT_LIMIT.' as l')  
               ->where('l.product_id = ?', $pid)
               ->where('l.name = ?', $name);
               
        return $this->fetchRow($select);
      
  }
   /*
      * Overrides getAll() in App_Model
  }
     * 
     * @param int $page 
     * @access public
     * @return Zend_Paginator
     */
    public function getAll($page = 1){
        $paginator = $this->fetchAll();
      
        $paginator = Zend_Paginator::factory($paginator);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(App_DI_Container::get('ConfigObject')->paginator->items_per_page);
        
        return $paginator;
    }
    
    /**
     * Overrides findById() in App_Model
     *  public function findById($productId,$force = false){
        $products = parent::findById($productId);
        return $products;
    }
     * @param int $userId 
     * @access public
     * @return array
     */
    public function findById($productId,$force = false){
        $products = parent::findById($productId);
        return $products;
    }
 
}