<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class Corporatelimit extends App_Model
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
    protected $_name = DbTable::TABLE_BIND_CORPORATE_LIMIT;
    
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
    
   
    
    public function delete($id){
      $data = array('status' => STATUS_DELETED );
      
       $res =  $this->update($data,"id=$id");
        return 'deleted';
        
   }

  public function checkLastdetails($agentId){
         $select = $this->_db->select()
                  ->from(DbTable::TABLE_BIND_CORPORATE_LIMIT)
                  ->where("date_end = '0000-00-00'")
                  ->where("corporate_id = ".$agentId);
        
        $groupArr = $this->_db->fetchRow($select);
        if (!empty($groupArr))
            return $groupArr;
        else
            return FALSE;
     }
     
  
     public function getbindCorplimit(){
         
         $select = $this->_db->select()
                  ->from(DbTable::TABLE_BIND_CORPORATE_LIMIT, array('corporate_limit_id'))
                  ->group('corporate_limit_id');
        //echo $select->__toString();exit;

        $products = $this->_db->fetchAll($select);
        $dataArray = array();
        foreach ($products as $val) {
            $dataArray[] = $val['corporate_limit_id'];
        }
        return $dataArray;

    }
  public function add($data){
     
   $chkname = $this->checkname($data['name'],$data['product_id']);
     if (!empty($chkname))
        return 'name_dup';
   else {
       
       $res =  $this->insert($data);
        return 'added';
        
   }
    
  }
   public function savecorporatelimit($data){
     
    $insert = $this->_db->insert(DbTable::TABLE_BIND_CORPORATE_LIMIT,$data) ;
    return $insert;
  }
  
    public function updateLimit($val)
     {
         $data = array(
                'date_end' => date('Y-m-d', strtotime('-1 day', strtotime($val['date_start']))) );
         $id = $val['id'];
         $this->_db->update(DbTable::TABLE_BIND_CORPORATE_LIMIT,$data, "id = '$id'");
         return true;
     }
     
  public function getCorporatelimits(){
      $select = $this->_db->select()    
                 ->from(DbTable::TABLE_CORPORATE_LIMIT,array('id','name'))
                 ->where("status = '".STATUS_ACTIVE."'")
                ->order('name Asc');
        
        $groupArr = $this->_db->fetchAll($select);
        $dataArray = array();
        $dataArray = array('' => 'Select Limit Name');
        foreach ($groupArr as $id => $val) {
            $dataArray[$val['id']] = $val['name'];
        }
        return $dataArray;
  }
  
  public function checkname($name){
       $select = $this->select()    
                ->from(DbTable::TABLE_CORPORATE_LIMIT.' as l')  
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
    
    public function getlastId($agentId){
      $select = $this->_db->select() 
               ->from(DbTable::TABLE_BIND_CORPORATE_LIMIT,array('max(id) as max'))
               ->where("date_end >= date_start")
              ->where("date_end !='0000-00-00'")
              ->where("corporate_id = $agentId")
              ->group('corporate_id');
             
     
       return $this->_db->fetchRow($select);  
        
    }
    
     public function deleteLimit($id, $prevId)
     { 
        if(isset($prevId)) 
        {
             $dataPrev = array(
                'date_end' => '0000-00-00' );
             $upd = $this->_db->update(DbTable::TABLE_BIND_CORPORATE_LIMIT, $dataPrev, "id = $prevId");
           if($upd)
           {
                $data = array('status' => STATUS_INACTIVE, 'date_end' => new Zend_Db_Expr('NOW()'));
                $this->_db->update(DbTable::TABLE_BIND_CORPORATE_LIMIT,$data, "id = '$id'");
                return true;
           }
        }
         
         return false;
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
        //echo $productId;
        return $products;
    }
   public function findBinddetailsById($id){
       $select = $this->_db->select() 
               ->from(DbTable::TABLE_BIND_CORPORATE_LIMIT)
                 ->where("id = $id");
       return $this->_db->fetchRow($select);
   }
    public function editupdate($lastId){
     
       
       $editdata = array('status' => STATUS_INACTIVE );
       //print_r($editdata);
       //echo $lastId;
       $res =  $this->update($editdata,"id=$lastId");
       
        return 'updated';
        
  
  } 
 
    
    public function getCorporatelimit($page = 1,$id = 0){
       $paginate = NULL;
       $select = $this->getCorpLimitSql($id);
        //echo $select->__toString();
        
        return $this->_paginate($select, $page, $paginate);
        
    }
    
    private function getCorpLimitSql($id = 0){
        $select = $this->select()    
                ->from(DbTable::TABLE_BIND_CORPORATE_LIMIT.' as bal',array('bal.id AS bid','bal.*',"DATE_FORMAT(bal.date_end, '%d-%m-%Y') as date_end_formatted",
                    "DATE_FORMAT(bal.date_start, '%d-%m-%Y') as date_start_formatted"))
                 ->setIntegrityCheck(false)
                 ->joinLeft(DbTable::TABLE_CORPORATE_LIMIT.' as l', "l.id=bal.corporate_limit_id",array('l.name','l.id as lid'))
                 ->where("(date_end = '0000-00-00' OR date_end >= date_start)")
                 ->where('bal.corporate_id= ?', $id)
                 ->where("bal.status = '".STATUS_ACTIVE."'");
        return $select;
        
    }
    
    public function getAgentlimitAsArray($id){
         $select = $this->getAgentLimitSql($id);
       
         return $this->_db->fetchAll($select);
        
    }
    
    public function getCorporateByLimit($id){
       
         $select = $this->_db->select()    
                ->from(DbTable::TABLE_BIND_CORPORATE_LIMIT.' as bal')
                ->joinLeft(DbTable::TABLE_CORPORATE_USER.' as a', "a.id=bal.corporate_id",array('a.id','a.first_name','a.last_name'))
                ->where('bal.id= ?', $id);
         return $this->_db->fetchRow($select);
         
    
        
    }
    
    public function checkDateDuplicacy($agentId, $dateStart)
    {
        $select = $this->_db->select()
                  ->from(DbTable::TABLE_BIND_CORPORATE_LIMIT)
                  ->where("corporate_id = ".$agentId)
                  ->where("'".$dateStart."' BETWEEN date_start AND date_end");
        $dateArr = $this->_db->fetchAll($select);
        if (count($dateArr) > 0)
            return TRUE;
        else
            return FALSE;
        
     }
     
     public function getAgentLimitInfo($agId){
       
         $curdate = date("Y-m-d");
         $select = $this->_db->select()    
                  ->from(DbTable::TABLE_BIND_CORPORATE_LIMIT.' as bal', array('bal.corporate_limit_id'))
                  ->joinLeft(DbTable::TABLE_CORPORATE_LIMIT.' as al', "bal.corporate_limit_id=al.id",array('al.limit_out_min_txn', 'al.limit_out_max_txn'))                 
                 ->where("'".$curdate."' >= bal.date_start AND ('".$curdate."' <= bal.date_end OR bal.date_end = '0000-00-00')")
                  ->where('bal.corporate_id= ?', $agId);
         return $this->_db->fetchRow($select);
        
    }
      public function getActiveCorplimit($id){
         $curdate = date("Y-m-d");
         $select = $this->_db->select()    
                ->from(DbTable::TABLE_BIND_CORPORATE_LIMIT.' as bal',array('bal.id as bid','bal.*',"DATE_FORMAT(bal.date_end, '%d-%m-%Y') as date_end_formatted",
                    "DATE_FORMAT(bal.date_start, '%d-%m-%Y') as date_start_formatted"))
                 //->setIntegrityCheck(false)
                 ->joinLeft(DbTable::TABLE_CORPORATE_LIMIT.' as l', "l.id = bal.corporate_limit_id",array('l.name'))
                 ->joinLeft(DbTable::TABLE_CORPORATE_USER.' as a', "bal.corporate_id = a.id",array('a.id'))
                 //->where("bal.date_end = '0000-00-00'")
                 ->where("'".$curdate."' >= date_start AND ('".$curdate."' <= date_end OR date_end = '0000-00-00')")
                 ->where('a.id= ?', $id);
         return $this->_db->fetchRow($select);
        
    }
    
    /*
     * corp limit details
     */
     public function getCorporateLimitDetails($corpId)
     {
         $curdate = new Zend_Db_Expr('NOW()');
         $select = $this->_db->select()
                ->from(DbTable::TABLE_BIND_CORPORATE_LIMIT." as a")
                ->joinLeft(DbTable::TABLE_CORPORATE_LIMIT.' as b',"b.id = a.corporate_limit_id")
                ->where("a.corporate_id = ?", $corpId)
                ->where("$curdate >= a.date_start AND ($curdate <= a.date_end OR a.date_end = '0000-00-00' OR a.date_end is NULL)");
         //echo $select->__toString();exit;
          return $this->_db->fetchRow($select);
     }
}