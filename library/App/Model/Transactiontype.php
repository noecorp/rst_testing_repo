<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class Transactiontype extends App_Model
{
    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'typecode';
    
    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_TRANSACTION_TYPE;
    
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
    
    public function findAll ($page = 1,$paginate = NULL, $force = FALSE){
        
       $details = $this->select()
                ->from($this,array('typecode as id','name','status','DATE_FORMAT(date_created, "%d-%m-%Y") as date_created',"is_comm"))
                ->where("status='".STATUS_ACTIVE."'")
                ->order('date_created');
                
     // echo $details->__toString();
       //  exit;       
                
       
       return $this->_paginate($details, $page, $paginate);
        
        
    }
   public function finddetailsById($typecode){
      $select = $this->select()    
                ->from(DbTable::TABLE_TRANSACTION_TYPE.' as t', array('typecode', 'name', 'status', 'DATE_FORMAT(date_created, "%d-%m-%Y") as date_created','is_comm'));
               
               $select->where('t.typecode = ?', $typecode);
        return $this->fetchRow($select);
  }
  
  
  public function add($data){
      
    $chktypecode = $this->checktypecode($data['typecode']);
    $chkname = $this->checkname($data['name']);
    if (!empty($chktypecode))
        return 'typecode_dup';    
   else if (!empty($chkname))
        return 'name_dup';
   else {
       $dataArr = array('typecode'=>$data['typecode'],
           'name'=>$data['name'],'status'=>STATUS_ACTIVE,
           'date_created'=>new Zend_Db_Expr('NOW()'),'is_comm'=>$data['is_comm']);
       $res =  $this->insert($dataArr);
        return 'added';
        
   }
    
  }
    
  public function checktypecode($typecode){
       $select = $this->select()    
                ->from(DbTable::TABLE_TRANSACTION_TYPE.' as t');               
               $select->where('t.typecode = ?', $typecode);
               return $this->fetchRow($select);
      
  }
  public function checkname($name){
       $select = $this->select()    
                ->from(DbTable::TABLE_TRANSACTION_TYPE.' as t');
               
               $select->where('t.name = ?', $name);
               
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
 
    /*
     * Get associative array of typecode and name
     */
    
     public function getTypecodeArray() {

        $details = $this->select()
                ->from($this, array('typecode', 'name'))
                ->where("status='" . STATUS_ACTIVE . "'")
                ->order('date_created');
        $data = Util::toArray($this->fetchAll($details));
        foreach ($data as $typecode) {
            $typeCodeArr[$typecode['typecode']] = $typecode['name'];
        }
        return $typeCodeArr;
    }
}