<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class CommissionPlan extends App_Model
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
    protected $_name = DbTable::TABLE_PLAN_COMMISSION;
    
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
                ->from(DbTable::TABLE_PLAN_COMMISSION.' as c',array('c.*',"DATE_FORMAT(c.date_created, '%d-%m-%Y') as date_created",'c.id as cid'))
                ->setIntegrityCheck(false)
                ->joinleft(DbTable::TABLE_COMMISSION_ITEMS.' as ci',"ci.plan_commission_id = c.id and ci.status = '".STATUS_ACTIVE."' ",array('count(ci.id) as count','ci.*')) 
                ->group('c.id')               
                ->order('c.name Asc');
                
              
                
      
       return $this->_paginate($details, $page, $paginate);
        
        
    }
   public function finddetailsById($id){
      $select = $this->select()    
                ->from(DbTable::TABLE_PLAN_COMMISSION);
               
               $select->where('id= ?', $id);
               
        return $this->fetchRow($select);
  }
  
  
  public function add($data){
      
   $chkname = $this->checkname($data['name']);
     if (!empty($chkname))
        return 'name_dup';
   else {
       
       $res =  $this->insert($data);
        return $res;
        
   }
    
  }
  
  
  
   public function getBindPlanItems(){
         
         $select = $this->_db->select()
                  ->from("t_bind_agent_product_commission", array('plan_commission_id'))
                  ->group('plan_commission_id');
        //echo $select->__toString();exit;

        $products = $this->_db->fetchAll($select);
        $dataArray = array();
        foreach ($products as $val) {
            $dataArray[] = $val['plan_commission_id'];
        }
        return $dataArray;

    }
  public function commissionPlanAssigned($cid){
       $select = $this->_db->select();
       $select->from("t_bind_agent_product_commission")
                ->where("plan_commission_id='".$cid."'");
               // ->limit(1);
      
        $arr = $this->_db->fetchRow($select);
        if(empty($arr))
            return TRUE;
        else
            return FALSE;
      
  }
   public function getTypecodeDetails($typecode)
    {
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_TRANSACTION_TYPE)
                ->where("typecode='".$typecode."'");
      //          ->limit(1);
      
        return $this->_db->fetchRow($select);
     }
 
  
  public function checkname($name){
       $select = $this->select()    
                ->from(DbTable::TABLE_PLAN_COMMISSION.' as c');
               
               $select->where('c.name = ?', $name);
               $select->where("c.status = '".STATUS_ACTIVE."'");
        //echo $select->__toString();       
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
 
       public function getTypecode()
    {
        $select = $this->select();
        $select->from(DbTable::TABLE_TRANSACTION_TYPE,array('typecode','name'))
                ->where('status= ?', STATUS_ACTIVE)
                ->where("is_comm = '".FLAG_YES."'")
                ->order('name');
        $select->setIntegrityCheck(false);
        $typecodeArr =  $this->fetchAll($select);
        $dataArray = array();
        $dataArray = array('' => 'Select Transaction Type');
        foreach ($typecodeArr as $id => $val) {
            $dataArray[$val['typecode']] = $val['name'];
        }
        return $dataArray;
  
    }
    
     public function findItemsById($id =0 , $page = 1, $paginate = NULL, $force = FALSE)
    {
         
 	
                    
                $details = $this->select()
                ->from(DbTable::TABLE_COMMISSION_ITEMS." as ci",array('ci.id as id','ci.plan_commission_id','ci.typecode_name','ci.typecode','ci.txn_flat','ci.txn_pcnt','ci.txn_min'))
                ->setIntegrityCheck(false)
                ->joinLeft(DbTable::TABLE_PLAN_COMMISSION.' as c',"ci.plan_commission_id = c.id AND ci.status='".STATUS_ACTIVE."' ",array('c.id as cid','ci.plan_commission_id','ci.txn_flat','ci.txn_pcnt','ci.txn_min','ci.txn_max'));
                if($id > 0) {
                    $details->where("c.id=?",$id);
                }
                //$details->where("ci.status='active'");
                

                $details->order(array("ci.typecode_name ASC"));
         
        //echo $details->__toString();        exit;
        return $this->_paginate($details, $page, $paginate);
    }
     
     public function itemsById($id)
    {
         
 	
                    
                $details = $this->_db->select()
                ->from(DbTable::TABLE_COMMISSION_ITEMS." as ci",array('ci.id as id','ci.plan_commission_id','ci.typecode_name','ci.typecode','ci.txn_flat','ci.txn_pcnt','ci.txn_min', 'ci.txn_max'))
                ->joinLeft(DbTable::TABLE_PLAN_COMMISSION.' as c',"ci.plan_commission_id = c.id ",array('c.id as cid','c.name'))
                ->where("ci.id=?",$id);
                
        //echo $details->__toString();        exit;
        return $this->_db->fetchRow($details);
    }
     
    
     public function chkDuplicateTypecode($typecode,$id)
    {
        $select = $this->_db->select()
                ->from(DbTable::TABLE_COMMISSION_ITEMS)
                ->where("plan_commission_id=".$id)
                ->where('status= ?', STATUS_ACTIVE)
                ->where("typecode = '".$typecode."'");
                
        
        $typecodeArr = $this->_db->fetchRow($select);
        if (empty($typecodeArr))
            return TRUE;
        else
            return FALSE;
 
       }
  public function insertItem($data){
      
      $insert = $this->_db->insert(DbTable::TABLE_COMMISSION_ITEMS,$data);
      if($insert > 0)
          return TRUE;
  }
   public function updateItem($id){
      
      $data = array('status'=> STATUS_INACTIVE);
      $update = $this->_db->update(DbTable::TABLE_COMMISSION_ITEMS,$data,"id=$id");
      if($update > 0)
          return TRUE;
  }
    public function deleteItem($id){
      
      $data = array('status'=> STATUS_DELETED);
      $update = $this->_db->update(DbTable::TABLE_COMMISSION_ITEMS,$data,"id=$id");
      if($update > 0)
          return TRUE;
  }
    
  
    /* getAgentCommissionPlanLogs() will return the commission plan changes details as logs.
     * Params/Fileters :- agent id and duration
     */
    
     public function getAgentCommissionPlanLogs($params){
        $logDuration = $params['duration']; 
        $agentId = $params['agent_id']; 
        $dates = Util::getDurationAllDates($logDuration);
        $totalDates = count($dates);
        $retLogData = array();
       
        if(!empty($dates)){
            
            foreach($dates as $queryDate){
                
                $to = isset($queryDate['to'])?$queryDate['to']:'';
                $queryDateArr = explode(' ', $to);
                $queryDate = $queryDateArr[0];
              
                
                /**** getting agent commission plan logs for particular date ****/
                
                $select = $this->_db->select();
                $select->from(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION." as bapc", array('bapc.plan_commission_id', 'bapc.by_ops_id'));
                $select->joinLeft(DbTable::TABLE_PLAN_COMMISSION.' as pc',"bapc.plan_commission_id = pc.id AND pc.status='".STATUS_ACTIVE."'", array('name as commission_plan_name'));
                $select->joinLeft(DbTable::TABLE_PRODUCTS.' as p',"bapc.product_id = p.id", array('name as product_name', 'bank_id'));
                $select->joinLeft(DbTable::TABLE_BANK.' as b',"p.bank_id = b.id", array('name as bank_name'));
                $select->joinLeft(DbTable::TABLE_OPERATION_USERS." as ou", "bapc.by_ops_id=ou.id", 'concat(ou.firstname," ",ou.lastname) as by_ops_name');
                $select->joinLeft(DbTable::TABLE_AGENTS.' as a', "a.id=".$agentId, array('a.agent_code', 'a.id as agent_id', 'concat(a.first_name," ",a.last_name) as agent_name'));
                $select->joinLeft(DbTable::TABLE_AGENT_DETAILS.' as ad', "a.id=ad.agent_id AND ad.status='".STATUS_ACTIVE."'", array('ad.estab_city', 'ad.estab_pincode'));
                $select->where("'".$queryDate."' >= bapc.date_start AND ('".$queryDate."' <= bapc.date_end OR bapc.date_end = '0000-00-00')");
                $select->where("bapc.agent_id='".$agentId."'");
                //echo $select->__toString();exit;

                $commLogs = $this->_db->fetchAll($select);
                if(!empty($commLogs)){
                    $totalLogs = count($commLogs);
                    for($i=0;$i<$totalLogs;$i++){
                        $commLogs[$i]['date'] = $queryDate;
                    }
                    $retLogData = array_merge($retLogData, $commLogs);
                }

          } // for each loop
     } else return array(); // date check if
     
     return $retLogData;
  }
  
  
}