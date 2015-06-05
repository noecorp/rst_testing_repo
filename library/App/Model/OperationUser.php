<?php
/**
 * Model that manages the users within the application
 *
 * @category backoffice
 * @package backoffice_models
 * @copyright company
 */
class OperationUser extends BaseUser
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
    protected $_name = DbTable::TABLE_OPERATION_USERS;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    protected $_rowClass = 'App_Table_OperationUser';
    
    /**
     * Updates the user's profile. 
     * 
     * @param array $data 
     * @access public
     * @return void
     */
    public function updateProfile(array $data){
        $user = Zend_Auth::getInstance()->getIdentity();
        $data['id'] = $user->id;
        
        $this->save($data);
    }
    
    /**
     * Overrides save() in App_Model
     * 
     * @param array $data 
     * @access public
     * @return int
     */
    public function save(array $data){
        $id = parent::save($data);
        /* user can be assigned single group */
         /* if (isset($data['groups']) && is_array($data['groups']) && !empty($data['groups'])) {*/
        if (isset($data['groups']) && !empty($data['groups'])) {
            $groups = $data['groups'];
        } else {
            $groups = array();
        }
        
        $userGroupModel = new OperationUserGroup();
        $userGroupModel->saveForUser($groups, $id);
        
        return $id;
    }
    
    /**
     * Overrides insert() in App_Model
     * 
     * @param array $data 
     * @access public
     * @return int
     */
    public function insert(array $data){
        //No Need to set last password update - It will cause Password set first time fail
        //$data['last_password_update'] = new Zend_Db_Expr('NOW()');
        $data['password'] = OperationUser::hashPassword($data['password']);
        $data['password_valid'] = 0;
        
        return parent::insert($data);
    }
    
    /**
     * Overrides getAll() in App_Model
     * 
     * @param int $page 
     * @access public
     * @return Zend_Paginator
     */
    public function findAll($page = 1,$paginate = NULL, $force = FALSE, $groupId = 0){
        $details = $this->select()
               ->from(DbTable::TABLE_OPERATION_USERS." as o",array('id','username', 'firstname', 'lastname'))
                ->setIntegrityCheck(false) 
                ->joinleft(DbTable::TABLE_OPERATION_USERS_GROUP." as og","o.id=og.user_id",array('og.group_id'))
                ->joinleft(DbTable::TABLE_GROUP." as g","og.group_id=g.id",array('g.name as groupname'));
       if($groupId > 0) {
             $details->where("g.id=$groupId");
         }         
        $details->order('o.username ASC');
         //   echo $details->__toString();   exit; 
      return $this->_paginate($details, $page, $paginate);
        
        
        /*
        $paginator = parent::findAll($page,$paginate,$force);
        $users = array();
        
        foreach($paginator as $user){
            $user->groups = $user->findManyToManyRowset('Group', 'OperationUserGroup');
            
            foreach($user->groups as $group){
                $user->groupNames[] = $group->name;
                $user->groupIds[] = $group->id;
            }
            
            $users[] = $user;
        }
        
        return Zend_Paginator::factory($users);*/
    }
    
    /**
     * Overrides findById() in App_Model
     * 
     * @param int $userId 
     * @access public
     * @return array
     */
    public function findById($userId,$force = false){
        $user = parent::findById($userId,$force);
        if(!empty($user)){
            $user->groups = $user->findManyToManyRowset('Group', 'OperationUserGroup');
            //echo "<pre>";print_r($user->groups);exit;
            /* user is assigned single group
             * foreach($user->groups as $group){
                $user->groupNames[] = $group->name;
                $user->groupIds[] = $group->id;
            }*/
            foreach($user->groups as $group){
                $user->groupNames = $group->name;
                $user->groupIds = $group->id;
            }
        }
        //echo "<pre>";print_r($user);
        //echo "<pre>";print_r($user->groupIds);exit;
        return $user;
    }
    
    /**
     * Overrides delete() in App_Model.
     *
     * When an user is deleted, all associated objects are also
     * deleted
     * 
     * @param mixed $where 
     * @access public
     * @return int
     */
    public function delete($where){
        if (is_numeric($where)) {
            $where = $this->_primary . ' = ' . $where;
        }
        
        $select = new Zend_Db_Select($this->_db);
        $select->from($this->_name);
        $select->where($where);
        
        $rows = $this->_db->fetchAll($select);
        $userGroupModel = new OperationUserGroup();
        
        foreach ($rows as $row) {
            $userGroupModel->deleteByUserId($row['id']);
        }
        
        return parent::delete($where);
    }
    
    /**
     * Changes the current user's password
     * 
     * @param string $password 
     * @access public
     * @return void
     */
    public function changePassword($password){
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            throw new Zend_Exception('You must be an authenticated user in the application in order to be able to call this method');
        }
        
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $password = OperationUser::hashPassword($password);
        
        $this->update(
            array(
                'password' => $password,
                'last_password_update' => new Zend_Db_Expr('NOW()'),
                'password_valid' => 1
            ),
            $this->_db->quoteInto('id = ?', $user->id)
        );
    }
    
     public function checkOpsDetails($data){
        $select  = $this->select()        
        ->from($this,array('id', 'username','email','mobile1','firstname','lastname'))
        ->where("username=?", $data['username'])
        ->where("email=?", $data['email'])
        ->where("mobile1=?", $data['mobile1']);
       
        
        $detailArr = $this->_db->fetchRow($select);
        //echo $select->__toString();
        //echo "<pre>";print_r($detailArr);exit;
        if(!empty($detailArr)){
        
        $logArr = array('ops_id'=>$detailArr['id'],'ip'=> $this->formatIpAddress(Util::getIP())
            );
         //echo "<pre>";print_r($logArr);exit;
        $insertIntoLog = $this->_db->insert(DbTable::TABLE_LOG_FORGOT_PASSWORD,$logArr);
        
        
        $alert = new Alerts();
        $detailArr['conf_code'] = Alerts::generateAuthCode();
        $m = new App\Messaging\MVC\Axis\Operation();
        $flg = $m->confCode($detailArr);
        return $detailArr;
        }
        else{
        return False;
        }
    }
    
    public function newPassword($password ,$id){
         
       $password = BaseUser::hashPassword($password);
        
        $update = $this->update(
            array('password' => $password),
            $this->_db->quoteInto('id = ?', $id)
        );
        
        $updateLogArr = array('date_modified' => new Zend_Db_Expr('NOW()'),'status'=> STATUS_INACTIVE);
        
        $this->_db->update("t_log_forgot_password", $updateLogArr, "ops_id = ".$id." AND status='".STATUS_ACTIVE."'");
       
        return TRUE;
        
    }
    
    
    
    /* checkPasswordDuplicate() will fetch last 4 pwds and check for duplicacy
     * as param : accepts ops id , password
     */
    public function checkPasswordDuplicate($param){
        $password = isset($param['password'])?$param['password']:'';
        $opsId = isset($param['ops_id'])?$param['ops_id']:'';
        
        if ($password!='' && $opsId!='') {
            
        $select  = $this->_db->select()   
        ->from(DbTable::TABLE_LOG_CHANGE_PASSWORD, array('password'))
        ->where("ops_id=?", $opsId)
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
    
    
    
    /* 
     * returns ops status : active/inactive/locked
     * used in login step 1
     */
    public function getOpsStatus($username){
        $select  = $this->select(array('status', 'id'))              
                ->where("username='$username'");
        
        $result = $this->fetchRow($select);
        return $result;
                
    }
    
        
    /* checkEmailDuplication() is responsible to verify the unique email of ops user
     * it will accept the old email and new email in param array, and will return the exception message if duplicate else will return false
     */
     public function checkEmailDuplication($param){
         $oldEmail = isset($param['oldEmail'])?$param['oldEmail']:'';
         $email = isset($param['email'])?$param['email']:'';
         
         if(trim($oldEmail)=='' || trim($email)=='')
             throw new Exception('Email not found!');
         
        $where = "LOWER(email) ='".strtolower($email)."' AND LOWER(email)!='".strtolower($oldEmail)."'";
        
        $select = $this->_db->select()
                ->from(DbTable::TABLE_OPERATION_USERS, array('id'))
                ->where($where);
               
        $row = $this->_db->fetchRow($select);
        if(empty($row))
            return false;
        else
            throw new Exception('Email Exists!');
        }
        
        
    /* checkMobileDuplication() is responsible to verify the unique email of ops user
     * it will accept the old username and new username in param array, and will return the exception message if duplicate else will return false
     */
     public function checkMobileDuplication($param){
         $oldMobile = isset($param['oldMobile'])?$param['oldMobile']:'';
         $mobile = isset($param['mobile'])?$param['mobile']:'';
         
         if(trim($oldMobile)=='' || trim($mobile)=='')
             throw new Exception('Mobile not found!');
         
         $where = "mobile1 ='".$mobile."' AND mobile1!='".$oldMobile."'";
        
         $select = $this->_db->select()
                 ->from(DbTable::TABLE_OPERATION_USERS, array('id'))
                 ->where($where);
         //echo $select; exit;
         $row = $this->_db->fetchRow($select);
         if(empty($row))
             return false;
         else
             throw new Exception('Mobile Exists!');
     }
 
     
     
     /** updateUser() will update details of operation user
     *   will accept param array including user details
     */
    public function updateUser($param){
        if(!empty($param)){
            $user = Zend_Auth::getInstance()->getIdentity();
            //echo $user->id; exit;
            return $this->_db->update(DbTable::TABLE_OPERATION_USERS, $param, 'id="'.$user->id.'"');
        }
        else return false;
    }
    
    public function findusersByGroupID($groupId = 0){
        $details = $this->select()
               ->from(DbTable::TABLE_OPERATION_USERS." as o",array('email','mobile1','concat(o.firstname," ",o.lastname) as name'))
                ->setIntegrityCheck(false) 
                ->joinleft(DbTable::TABLE_OPERATION_USERS_GROUP." as og","o.id=og.user_id",array('og.group_id'));
       if($groupId > 0) {
             $details->where("og.group_id = $groupId");
         }         
        $details->where("o.status = '".STATUS_ACTIVE."'");         
        $details->order('o.username ASC');
      
      return $this->fetchAll($details);
        
    }  
}