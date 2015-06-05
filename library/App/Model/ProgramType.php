<?php
/**
 * Model that manages the Currency
 *
 * @package Operation_Models
 * @copyright transerv
 */

class ProgramType extends App_Model
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
    protected $_name = DbTable::TABLE_SETTINGS;
    
    public function getSettings($sectionId, $page = 1, $paginate = NULL){        
            if($sectionId<1)
                return false;
            
            $select = $this->getSettingSql(array('section_id'=>$sectionId));
            //echo $select->__toString();exit;        
        
        return $this->_paginate($select, $page, $paginate);       
    }
    
    private function getSettingSql($param){
        $type = isset($param['type'])?$param['type']:'';
        
        $select =   $this->select();
        $select->setIntegrityCheck(false);
        $select->from(DbTable::TABLE_SETTINGS.' as s',array("s.id, DATE_FORMAT(s.date_created, '%d-%m-%Y %h:%i:%s') as date_created", "s.name"
                , "s.description", "s.value", "s.ip", "s.type"));
        $select->joinLeft(DbTable::TABLE_OPERATION_USERS.' as ou',"s.by_ops_id = ou.id ",array('ou.username as ops_name'));
        $select->where('s.settings_section_id= ?', $param['section_id']);
        $select->where('s.status= ?', STATUS_ACTIVE);
                    
        if($type!=''){
             $select->where('s.type= ?', $type);
        }            
         $select->order('s.name ASC');
         
        return $select;
    }
    
     public function getAllSettings($param){        
            if($param['section_id']<1)
                return false;
            
            $select = $this->getSettingSql($param);
            //echo $select->__toString();exit;        
        
        return  $this->fetchAll($select);       
    }
    
    public function updateSetting($param) {
        
        if($param['id']<1 || $param['name']==''|| $param['value']==''){
            throw new Exception('Insufficient data to update'); exit;
        }
       
       $param['status'] = STATUS_ACTIVE;
       $param['ip'] = $this->formatIpAddress($param['ip']);
       $param['date_created'] = new Zend_Db_Expr('NOW()');
       
           $resp = $this->_db->update(DbTable::TABLE_SETTINGS, array('status'=>STATUS_INACTIVE), 'id="'.$param['id'].'"'); 
           $param['id']='';
           $resp = $this->_db->insert(DbTable::TABLE_SETTINGS, $param);    
       
       return true;
    }
  
    
    
    public function getSettingInfo($settingId){        
            if($settingId<1)
                return false;
            
                   $select =   $this->select()
                    ->setIntegrityCheck(false)
                    ->from(DbTable::TABLE_SETTINGS.' as s',array("s.id, DATE_FORMAT(s.date_created, '%d-%m-%Y %h:%i:%s') as date_created", "s.name"
                            , "s.description", "s.value", "s.ip", "s.settings_section_id", "s.type", "s.currency"))
                    ->joinLeft(DbTable::TABLE_SETTING_SECTIONS.' as ss',"s.settings_section_id = ss.id", array('ss.name as setting_section_name'))
                    ->where('s.id= ?', $settingId)
                    ->limit('1') ;  
             //echo $select->__toString();exit;        
        
        return  $this->fetchRow($select);       
    }
    
    public function agentMaxBalanceValue(){
         $select =   $this->select()
                    ->from(DbTable::TABLE_SETTINGS,array('value'))
                    ->where("status='".STATUS_ACTIVE."'")
                    ->where("type='".SETTING_AGENT_MAX_BALANCE."'");
           
        
        $maxBalance = $this->fetchRow($select); 
        return $maxBalance['value'];
    }
    
    public function getAgentSettingValue($type){
         $select =   $this->select()
                    ->from(DbTable::TABLE_SETTINGS,array('value'))
                    ->where("status='".STATUS_ACTIVE."'")
                    ->where("type='".$type."'");
           
        
        $maxBalance = $this->fetchRow($select); 
        return $maxBalance['value'];
    }
    
    public function add($data){
      
    $chkvalue = $this->checkvalue($data['value']);
    $chkname = $this->checkname($data['name']);
    if (!empty($chkvalue))
        return 'value_dup';    
   else if (!empty($chkname))
        return 'name_dup';
   else {
      
       $res =  $this->insert($data);
        return 'added';
        
   }
    
  }
  
   public function checkvalue($value){
       $select = $this->select()    
                ->from(DbTable::TABLE_SETTINGS);               
               $select->where('value = ?', $value);
               return $this->fetchRow($select);
      
  }
  public function checkname($name){
       $select = $this->select()    
               ->from(DbTable::TABLE_SETTINGS);   
               
               $select->where('name = ?', $name);
               
        return $this->fetchRow($select);
      
  }
  
  
}