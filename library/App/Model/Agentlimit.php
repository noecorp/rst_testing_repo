<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class Agentlimit extends App_Model
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
    protected $_name = DbTable::TABLE_AGENT_LIMIT;
    
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
    
    public function findAllLimit ($lid = 0,$page = 1){
       
     
        $paginate = NULL;
        $force = FALSE;
    
            $details = $this->select()
                ->from($this,array('id','limit_out_max_daily','limit_out_max_monthly',
                    'limit_out_max_yearly','limit_out_min_txn',                    
                    'limit_out_max_txn','name','currency',
                    'cnt_out_max_txn_daily','cnt_out_max_txn_monthly','cnt_out_max_txn_yearly','name','currency'));
            if($lid > 0)
            $details->where('id='.$lid); 
            $details->where("status='".STATUS_ACTIVE."'"); 
            $details->order('name Asc');
     
       // echo $details->__toString();
                
       
       return $this->_paginate($details, $page, $paginate);
        
        
    }
    
    public function delete($id){
      $data = array('status' => STATUS_DELETED );
      
       $res =  $this->update($data,"id=$id");
        return 'deleted';
        
   }

  public function checkLastdetails($agentId){
         $select = $this->_db->select()
                  ->from(DbTable::TABLE_BIND_AGENT_LIMIT)
                  ->where("date_end = '0000-00-00'")
                  ->where("agent_id = ".$agentId);
        
        $groupArr = $this->_db->fetchRow($select);
        if (!empty($groupArr))
            return $groupArr;
        else
            return FALSE;
     }
     
  
     public function getbindAgentlimit(){
         
         $select = $this->_db->select()
                  ->from(DbTable::TABLE_BIND_AGENT_LIMIT, array('agent_limit_id'))
                  ->group('agent_limit_id');
        //echo $select->__toString();exit;

        $products = $this->_db->fetchAll($select);
        $dataArray = array();
        foreach ($products as $val) {
            $dataArray[] = $val['agent_limit_id'];
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
   public function saveagentlimit($data){
     
    $insert = $this->_db->insert(DbTable::TABLE_BIND_AGENT_LIMIT,$data) ;
    return $insert;
  }
  
    public function updateLimit($val)
     {
         $data = array(
                'date_end' => date('Y-m-d', strtotime('-1 day', strtotime($val['date_start']))) );
         $id = $val['id'];
         $this->_db->update(DbTable::TABLE_BIND_AGENT_LIMIT,$data, "id = '$id'");
         return true;
     }
     
  public function getAgentlimits(){
      $select = $this->select()    
                 ->from(DbTable::TABLE_AGENT_LIMIT,array('id','name'))
                 ->where("status = '".STATUS_ACTIVE."'")
                ->order('name Asc');
        
        $groupArr = $this->fetchAll($select);
        $dataArray = array();
        $dataArray = array('' => 'Select Limit Name');
        foreach ($groupArr as $id => $val) {
            $dataArray[$val['id']] = $val['name'];
        }
        return $dataArray;
  }
  
  public function checkname($name){
       $select = $this->select()    
                ->from(DbTable::TABLE_AGENT_LIMIT.' as l')  
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
               ->from(DbTable::TABLE_BIND_AGENT_LIMIT,array('max(id) as max'))
               ->where("date_end >= date_start")
              ->where("date_end !='0000-00-00'")
              ->where("agent_id = $agentId")
              ->group('agent_id');
             
     
       return $this->_db->fetchRow($select);  
        
    }
    
     public function deleteLimit($id, $prevId)
     { 
        if(isset($prevId)) 
        {
             $dataPrev = array(
                'date_end' => '0000-00-00' );
             $upd = $this->_db->update(DbTable::TABLE_BIND_AGENT_LIMIT, $dataPrev, "id = $prevId");
           if($upd)
           {
                $data = array('status' => STATUS_INACTIVE, 'date_end' => new Zend_Db_Expr('NOW()'));
                $this->_db->update(DbTable::TABLE_BIND_AGENT_LIMIT,$data, "id = '$id'");
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
               ->from(DbTable::TABLE_BIND_AGENT_LIMIT)
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
 
    
    public function getAgentlimit($page = 1,$id = 0){
       $paginate = NULL;
       $select = $this->getAgentLimitSql($id);
        //echo $select->__toString();
        
        return $this->_paginate($select, $page, $paginate);
        
    }
    
    private function getAgentLimitSql($id = 0){
        $select = $this->select()    
                ->from(DbTable::TABLE_BIND_AGENT_LIMIT.' as bal',array('bal.id AS bid','bal.*',"DATE_FORMAT(bal.date_end, '%d-%m-%Y') as date_end_formatted",
                    "DATE_FORMAT(bal.date_start, '%d-%m-%Y') as date_start_formatted"))
                 ->setIntegrityCheck(false)
                 ->joinLeft(DbTable::TABLE_AGENT_LIMIT.' as l', "l.id=bal.agent_limit_id",array('l.name','l.id as lid'))
                 ->where("(date_end = '0000-00-00' OR date_end >= date_start)")
                 ->where('bal.agent_id= ?', $id)
                 ->where("bal.status = '".STATUS_ACTIVE."'");
        return $select;
        
    }
    
    public function getAgentlimitAsArray($id){
         $select = $this->getAgentLimitSql($id);
       
         return $this->_db->fetchAll($select);
        
    }
    
    public function getAgentByLimit($id){
       
         $select = $this->_db->select()    
                ->from(DbTable::TABLE_BIND_AGENT_LIMIT.' as bal')
                ->joinLeft(DbTable::TABLE_AGENTS.' as a', "a.id=bal.agent_id",array('a.id','a.first_name','a.last_name','id as agent_id'))
                
                ->where('bal.id= ?', $id);
        
         return $this->_db->fetchRow($select);
         
    
        
    }
    
    public function checkDateDuplicacy($agentId, $dateStart)
    {
        $select = $this->_db->select()
                  ->from(DbTable::TABLE_BIND_AGENT_LIMIT)
                 // ->where("date_end = '0000-00-00'")
                  ->where("agent_id = ".$agentId)
                  ->where("'".$dateStart."' BETWEEN date_start AND date_end");
        //echo $select->__toString();
        $dateArr = $this->_db->fetchAll($select);
        if (count($dateArr) > 0)
            return TRUE;
        else
            return FALSE;
        
     }
     
     public function getAgentLimitInfo($agId){
       
         $curdate = date("Y-m-d");
         $select = $this->_db->select()    
                  ->from(DbTable::TABLE_BIND_AGENT_LIMIT.' as bal', array('bal.agent_limit_id'))
                  //->setIntegrityCheck(false)
                  ->joinLeft(DbTable::TABLE_AGENT_LIMIT.' as al', "bal.agent_limit_id=al.id",array('al.limit_out_min_txn', 'al.limit_out_max_txn'))                 
                 // ->where('bal.date_start <= ?', new Zend_Db_Expr('CURDATE()'))
                 // ->where('bal.date_end = ?', '0000-00-00')
                 // ->where('bal.status = ?', 'active')
                 ->where("'".$curdate."' >= bal.date_start AND ('".$curdate."' <= bal.date_end OR bal.date_end = '0000-00-00')")
                  ->where('bal.agent_id= ?', $agId);
        //echo $select->__toString();exit;
         return $this->_db->fetchRow($select);
        
    }
    public function getActiveAgentlimit($id){
         $curdate = date("Y-m-d");
         $select = $this->_db->select()    
                ->from(DbTable::TABLE_BIND_AGENT_LIMIT.' as bal',array('bal.id as bid','bal.*',"DATE_FORMAT(bal.date_end, '%d-%m-%Y') as date_end_formatted",
                    "DATE_FORMAT(bal.date_start, '%d-%m-%Y') as date_start_formatted"))
                 //->setIntegrityCheck(false)
                 ->joinLeft(DbTable::TABLE_AGENT_LIMIT.' as l', "l.id=bal.agent_limit_id",array('l.name'))
                 ->joinLeft(DbTable::TABLE_AGENTS.' as a', "bal.agent_id=a.id",array('a.id'))
                 //->where("bal.date_end = '0000-00-00'")
                 ->where("'".$curdate."' >= date_start AND ('".$curdate."' <= date_end OR date_end = '0000-00-00')")
                 ->where('a.id= ?', $id);
        // echo $select->__toString();exit;
         return $this->_db->fetchRow($select);
        
    }
}