<?php

class BankUser extends BaseUser
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
    protected $_name = DbTable::TABLE_BANK_USER;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    protected $_rowClass = 'App_Table_BankUser';
    
    
      /**
     * Overrides findById() in App_Model
     * 
     * @param int $userId 
     * @access public
     * @return array
     */
    public function findById($userId, $force = FALSE){
        $user = parent::findById($userId);
       /* if(!empty($user)){
            //exit("here");
            $user->groups = $user->findManyToManyRowset('Group', 'AgentUserGroup');
            exit("here");
            foreach($user->groups as $group){
                $user->groupNames[] = $group->name;
                $user->groupIds[] = $group->id;
            }
        }*/
        
       
        $user->groups = $user;
        $user->groupNames[] = 'members';
        $user->groupIds[] = $userId;
     
        
        return $user;
    }
    
    public function getStatus($agentId){
        $select = $this->select() 
                ->from(DbTable::TABLE_AGENTS,array('id','status','enroll_status'));
                $select->where('id = ?', $agentId);
            
        return $this->fetchRow($select);
    }
    
    public function getActiveUsers(){
        $select = $this->select() 
                ->from(DbTable::TABLE_BANK_USER,array('id','status','email','username'))
                ->where('status=?', STATUS_ACTIVE);
        return $this->fetchAll($select);
    }
    
    public function getStatusByUsername($username){
        try {
        $select = $this->select() 
                ->from($this->_name, array('id','status'))
                //->from(DbTable::TABLE_BANK_USER,array('id','status'));
                ->where('username = ?', $username);
        $flg = $this->fetchRow($select);
        } catch (Exception $e) {
            //echo '<pre>';print_r($e);exit;
        }
        
        return $flg;
    }
    
   public function editBankUser($param, $id){
       if($id>0){
         $update = $this->_db->update(DbTable::TABLE_BANK_USER,$param,"id=$id");
         return $update;
       } else return '';
    }
    
    
    public function checkPasswordDuplicate($param){
        $password = isset($param['password'])?$param['password']:'';
        $agentId = isset($param['bank_id'])?$param['bank_id']:'';
        
        if ($password!='' && $agentId!='') {
            
        $select  = $this->_db->select()   
        ->from(DbTable::TABLE_LOG_CHANGE_PASSWORD, array('password'))
        ->where("bank_id=?", $agentId)
        ->order('date_created DESC')       
        ->limit('4');
        //echo $select->__toString();exit;
        $rows = $this->_db->fetchAll($select);
        
        foreach ($rows as $row) {
                 $dbPassword = $row['password'];
                 if($password==$dbPassword){
                     throw new Exception('New password cannot be same as your last four passwords');
                 }
        }
        
        return false;
    }
  }
    
  
       public function changePassword($password){
         
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            throw new Zend_Exception('You must be an authenticated user in the application in order to be able to call this method');
        }
        
        $user = Zend_Auth::getInstance()->getIdentity();
       
        
        $password = BaseUser::hashPassword($password);
        
        $this->update(
            array('password' => $password,
                  'last_password_update' => new Zend_Db_Expr('NOW()')),
            $this->_db->quoteInto('id = ?', $user->id)
        );
    }
    
    public function getActiveUsersGroups(){
        $select = $this->select() 
                ->from(DbTable::TABLE_BANK_USER. " as bank_user",array('id','status','email','username'))
                ->setIntegrityCheck(false)
        				->joinLeft(DbTable::TABLE_BANK_USERS_GROUP . " as bank_usergrp", "bank_usergrp.user_id = bank_user.id",array())
        				->joinLeft(DbTable::TABLE_BANK_GROUP . " as bank_group", "bank_usergrp.group_id = bank_group.id",array('name as groupName'))
                ->where('bank_user.status=?', STATUS_ACTIVE);
                //echo $select;
        return $this->fetchAll($select);
    }

}