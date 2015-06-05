<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class Corporatelimits extends App_Model
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
    protected $_name = DbTable::TABLE_CORPORATE_LIMIT;
    
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
     public function checkname($name){
       $select = $this->select()    
                ->from(DbTable::TABLE_CORPORATE_LIMIT.' as l')  
                ->where('l.name = ?', $name);
               
        return $this->fetchRow($select);
      
  }
  
   public function getCorporatelimits(){
      $select = $this->select()    
                 ->from(DbTable::TABLE_CORPORATE_LIMIT,array('id','name'))
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
  
    public function editupdate($lastId){
     
       
       $editdata = array('status' => STATUS_INACTIVE );
       //print_r($editdata);
       //echo $lastId;
       $res =  $this->update($editdata,"id=$lastId");
       
        return 'updated';
        
  
  } 
 
      
    public function delete($id){
      $data = array('status' => STATUS_DELETED );
      
       $res =  $this->update($data,"id=$id");
        return 'deleted';
        
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
    
    public function getCorplimitAsArray($id){
         $select = $this->getCorpLimitSql($id);
       
         return $this->_db->fetchAll($select);
        
    }
}