<?php

class CorporateUser extends BaseUser
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
    protected $_name = DbTable::TABLE_CORPORATE_USER;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    protected $_rowClass = 'App_Table_CorporateUser';
    
    
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
                ->from(DbTable::TABLE_CORPORATE_USER,array('id','status','enroll_status','email_verification_status','email_verification_id'));
                $select->where('id = ?', $agentId);
            
        return $this->fetchRow($select);
    }
    
    /*
     * getAllUsers will get all the corporate users based on the search criteria
     */
    public function getAllUsers($param, $page = 1, $paginate = NULL){
        $columnName = (isset($param['searchCriteria']) && !empty($param['searchCriteria'])) ? $param['searchCriteria'] : '';
        $keyword = (isset($param['keyword']) && !empty($param['keyword'])) ? $param['keyword'] : '';
      
        $select = $this->select() 
                ->from(DbTable::TABLE_CORPORATE_USER." as c",array('id','c.first_name','c.last_name','corporate_code','mobile','status','email','enroll_status','email_verification_status'))
                ->setIntegrityCheck(false)
                ->joinLeft(DbTable::TABLE_CORPORATE_USER_DETAILS." as cud", "c.id=cud.corporate_user_id", array("estab_city","estab_state","estab_district"));
        
        if(!empty($columnName) && !empty($keyword))
        {
            if($columnName == 'estab_city'){
                $whereString = "cud.$columnName LIKE '%$keyword%'";
            }
            else
            {
                $whereString = "c.$columnName LIKE '%$keyword%'";
            }
            $select->where($whereString);
        }
                
        $select->order('c.date_created DESC');
        return $this->_paginate($select, $page, $paginate);
    }
    
    public function getStatusByUsername($username){
        try {
        $select = $this->select() 
                ->from($this->_name, array('id','status'))
                //->from(DbTable::TABLE_CORPORATE_USER,array('id','status'));
                ->where('email = ?', $username);
        $flg = $this->fetchRow($select);
        } catch (Exception $e) {
           //echo '<pre>';print_r($e);exit;
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $signupMsg = $e->getMessage();
            //return '';
        }
        
        return $flg;
    }
    
   public function editCorporateUser($param, $id){
       if($id>0){
         $update = $this->_db->update(DbTable::TABLE_CORPORATE_USER,$param,"id=$id");
         return $update;
       } else return '';
    }
    public function editCorporateUserDetails($param, $id){
       try{
            if($id>0){
              $update = $this->_db->update(DbTable::TABLE_CORPORATE_USER_DETAILS,$param,"corporate_user_id =$id");
              return $update;
            } else return '';
        }catch(Exception $e){
           //echo '<pre>';print_r($e);exit;
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $signupMsg = $e->getMessage();
            return '';
        }
    }
    
    
    public function checkPasswordDuplicate($param){
        $password = isset($param['password'])?$param['password']:'';
        $agentId = isset($param['corporate_id'])?$param['corporate_id']:'';
        
        if($password!='' && $agentId!='') {
            
            $select  = $this->_db->select()   
            ->from(DbTable::TABLE_LOG_CHANGE_PASSWORD, array('password'))
            ->where("corporate_id=?", $agentId)
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
                ->from(DbTable::TABLE_CORPORATE_USER. " as bank_user",array('id','status','email','username'))
                ->setIntegrityCheck(false)
        				->joinLeft(DbTable::TABLE_CORPORATE_USERS_GROUP . " as bank_usergrp", "bank_usergrp.user_id = bank_user.id",array())
        				->joinLeft(DbTable::TABLE_BANK_GROUP . " as bank_group", "bank_usergrp.group_id = bank_group.id",array('name as groupName'))
                ->where('bank_user.status=?', STATUS_ACTIVE);
                //echo $select;
        return $this->fetchAll($select);
    }
    
    
    public function checkPhone($phone){
        try{
            
            $where = " mobile = '".$phone."' AND (enroll_status = '".STATUS_APPROVED."' OR enroll_status = '".STATUS_PENDING."')";
            $selectphone = $this->select()
                           ->where($where);
            //echo $selectphone.'===='; exit;
            $agntphData = $this->fetchRow($selectphone);
            
            if(!empty($agntphData)) {
                
                
                 return 'phone_dup';
                  
            }            
        }
        catch (Exception $e) {
            $this->setMessage($e->getMessage());
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            return false;
        }
        
    }
    public function signupCorporate($param, $corporate_info)
    {
        
        $emailcheck = $this->emailDuplication($param['email']);
        
        if(!empty($emailcheck)) {

            throw new Exception ('Email Exists!');
            exit;
        }  
        
        $param['ip'] = $this->formatIpAddress(Util::getIP());
        $this->insert($param);
        
        $id = $this->_db->lastInsertId(DbTable::TABLE_CORPORATE_USER, 'id');
        // Assign agent code and enter it in DB through the BaseUser::generateUserCode() function
        $corporate_code = $this->generateCorporateCode($id);
        
        $corporate_info['corporate_user_id'] = $id;
        
                   
        if($id > 0){ 
            $this->_db->insert(DbTable::TABLE_CORPORATE_USER_DETAILS,$corporate_info);   
        }
         return $id;         
    }

    
    public function emailDuplication($email){
        $where = " email = '".$email."' AND (enroll_status = '".STATUS_APPROVED."' OR enroll_status = '".STATUS_PENDING."')";
        $select = $this->select('id') 
                ->where($where);
        //echo $select    ; exit;        
        return $this->fetchRow($select);        
    }
    
   
    public function generateCorporateCode($id,$corporateCode = null){
        
                //Get user info
                $userModel = new CorporateUser();
                $select = $userModel->select()
                    ->where('id = ?', $id);
                $userInfo = $userModel->fetchRow($select);
                
                //Empty Validation & Checks
                if(empty($userInfo)) {
                    throw new Exception('Base User: Invalid corporate id');
                }
                
                if($corporateCode == null) {
                     $code = Util::encodeToNumericCode($userInfo->email);
                } else {
                    $code = Util::encodeToNumericCode($userInfo->email, 7, true );
                }

                $encodeId = Util::getLenghtId($userInfo->id);
                $corporateCode = Util::getAgentCode($code, $encodeId);
                //echo $corporateCode; exit;
                //Check Duplicate
                $sel = $userModel->select()
                    ->where('corporate_code = ?', $corporateCode);
                //echo $sel; exit;
                $corporateInfo = $userModel->fetchRow($sel);
                //echo $select->__toString();
                if(!empty($corporateInfo)) {
                    return self::generateCode($id, $corporateCode);
                }
                $data = array(
                    'corporate_code' => $corporateCode
                );
                //print_r($data); exit;
                $userModel->update($data, 'id='.$id);
                return $corporateCode;
                //exit;
        
    }
    
    public function getCorporateCode($id){
        $agentcode = $this->select('corporate_code')              
                ->where("id=$id");
        //echo $agentcode; exit; 
        $code = $this->fetchRow($agentcode);
        return $code['corporate_code'];
                
    }
    
    public function getCorporateType($id) {
        
        $select  = $this->select()        
            ->where("id=?", $id);
        $detailArr = $this->fetchRow($select);
        
        if($detailArr['user_type'] == SUPER_CORPORATE_DB_VALUE) {
            return HEAD_CORPORATE;
        }elseif($detailArr['user_type'] == DISTRIBUTOR_CORPORATE_DB_VALUE) {
            return REGIONAL_CORPORATE;
        } elseif($detailArr['user_type'] == SUB_CORPORATE_DB_VALUE ) {
            return LOCAL_CORPORATE;
        } else {
            return LOCAL_CORPORATE;
        }
        
    }
    

    
    public function isHead($agentId){
        $select  = $this->select('user_type')        
            ->where("id=?", $agentId);
        $detailArr = $this->fetchRow($select);
        if($detailArr['user_type'] == SUPER_CORPORATE_DB_VALUE) return TRUE;
        return false;
    } 
    
    public function isRegional($agentId){
        $select  = $this->select('user_type')        
            ->where("id=?", $agentId);
        $detailArr = $this->fetchRow($select);
        if($detailArr['user_type'] == DISTRIBUTOR_CORPORATE_DB_VALUE) return TRUE;
        return false;
    }
    
    public function emailAuthChk($id, $ver_code)
    {
        $select = $this->_db->select()
                    ->from(DbTable::TABLE_EMAIL_VERIFICATION)
                    ->where('corporate_id =?',$id)
                    ->where('verification_code =?',$ver_code); 
       // echo $select->__toString();
        
         $row = $this->_db->fetchRow($select);
         
         $verification_id = $this->getEmailVerificationId($ver_code);
         if(!empty($row)){
             
             $agentInfo = $this->findById($id);
             if($agentInfo['email_verification_status'] == STATUS_VERIFIED){
                 return 'already_ver';
             }
             else {
                $agentArr = array('email_verification_status'=>STATUS_VERIFIED,'email_verification_id'=>$verification_id);

                $activationArr = array('datetime_verified'=>new Zend_Db_Expr('NOW()'),'activation_status'=>STATUS_VERIFIED);

                $updateAgent = $this->update($agentArr,"id=$id");

                $updateActivation = $this->_db->update(DbTable::TABLE_EMAIL_VERIFICATION,$activationArr,"verification_code='$ver_code'");

                if($updateActivation && $updateAgent)
                    return TRUE;
                else 
                    return FALSE;
             }
         
         } 
             return FALSE;
      
    }
    
     public function getEmailVerificationId($ver_code){
        $select = $this->_db->select()
                    ->from(DbTable::TABLE_EMAIL_VERIFICATION)
                    ->where('verification_code =?',$ver_code); 
       
        
         $row = $this->_db->fetchRow($select);
         return $row['id'];
    }
    public function getObjectRelationshipLabel($userType) {
        if(empty($userType)) {
            return false;
        }
        
        switch ($userType)
        {
            case HEAD_CORPORATE:
                $flg = FALSE;
               break;
           
            case REGIONAL_CORPORATE:
                $flg = HEAD_TO_REGIONAL;
               break;
           
            case LOCAL_CORPORATE:
                $flg = REGIONAL_TO_LOCAL;
               break;
           
           default :
               $flg = FALSE;
               break;
        }
    
     return $flg;
    }
    
    
        
    public function checkRelation($childId,$parentId=''){
        $select  = $this->select(array('parent_agent_id'))        
            ->where("id=?", $agentId);
        if(!empty($superAgent)) {
            $select->where('parent_agent_id=?',$superAgent);
        }
        $detailArr = $this->fetchRow($select);
        if($detailArr['parent_agent_id'] > 0) return TRUE;
        return false;
    }
    
    public function checkCorporateDetails($data){
        $select  = $this->_db->select()        
        ->from(DbTable::TABLE_CORPORATE_USER.' as a',array('a.id', 'a.corporate_code','a.email','a.mobile','a.email','a.first_name','a.last_name','a.enroll_status','a.email_verification_status'))
        ->where("a.email=?", $data['email'])
        ->where("a.mobile=?", $data['phone']);
        $detailArr = $this->_db->fetchRow($select);
        if(!empty($detailArr) && $detailArr['enroll_status']==ENROLL_APPROVED_STATUS && $detailArr['email_verification_status']==EMAIL_VERIFIED_STATUS){
        
            $logArr = array('corporate_id'=>$detailArr['id'],'ip'=> $this->formatIpAddress(Util::getIP()));
            $insertIntoLog = $this->_db->insert(DbTable::TABLE_LOG_FORGOT_PASSWORD,$logArr);
            $alert = new Alerts();
            $detailArr['conf_code'] = Alerts::generateAuthCode();
            $m = new App\Messaging\MVC\Axis\Corporate();
            $flg = $m->confCode($detailArr);
            return $detailArr;
        }elseif(!empty($detailArr) && $detailArr['enroll_status']!=ENROLL_APPROVED_STATUS){
            return "enroll_status_error";
        }elseif(!empty($detailArr) && $detailArr['email_verification_status']!=EMAIL_VERIFIED_STATUS){
            return "email_status_error";
        }else{
             return false;
        }
    }
    
    public function newPassword($password ,$id){
      
       $password = BaseUser::hashPassword($password);
       //echo "<pre>$id";print_r(array('password' => $password)); exit;
        
        $update = $this->_db->update(DbTable::TABLE_CORPORATE_USER,array('password' => $password),'id = '."'".$id."'");
        
        $updateLogArr = array('date_modified' => new Zend_Db_Expr('NOW()'),'status'=> STATUS_INACTIVE);
        
        $this->_db->update(DbTable::TABLE_LOG_FORGOT_PASSWORD, $updateLogArr, "corporate_id = ".$id." AND status='".STATUS_ACTIVE."'");
        
       
        return TRUE;
        
    }
    /* checkIdNumberDuplication() is responsible to verify the unique indentification number
     * it will accept the identification number and agent id and identification type in param array and will return the exception message if duplicate else will return false
     */
    public function checkIdNumberDuplication($param){
         $idNo = isset($param['idNo'])?$param['idNo']:'';
         $idType = isset($param['idType'])?$param['idType']:'';
         $agentId = isset($param['corporateId'])?$param['corporateId']:'';
         
         if(trim($idNo)=='' || trim($idType)=='')
             throw new Exception('Identification Number not found!');
         
        $where = "cd.Identification_number ='".$idNo."' AND cd.Identification_type='".$idType."' AND cd.status='".STATUS_ACTIVE."'";
        if($agentId>0) 
            $where .= " AND corporate_user_id !='".$agentId."' AND (c.enroll_status = '".STATUS_APPROVED."' OR c.enroll_status = '".STATUS_PENDING."')";
        
        $select = $this->_db->select()
                ->from(DbTable::TABLE_CORPORATE_USER .' as c',array('id'))
                ->joinLeft(DbTable::TABLE_CORPORATE_USER_DETAILS .' as cd',"c.id = cd.corporate_user_id")
                ->where($where);
        
//      echo $select.'====='; exit;
        
        $row = $this->_db->fetchRow($select);
        if(empty($row))
            return false;
        else
            throw new Exception('Identification Number Exists!');
    }
        
        
    /* checkAddressNumberDuplication() is responsible to verify the unique address proof number
     * it will accept the address proof number and agent id in param array, and will return the exception message if duplicate else will return false
     */
    public function checkAddressNumberDuplication($param){
        $addressProofNo = isset($param['addressProofNo'])?$param['addressProofNo']:'';
        $corporateId = isset($param['corporateId'])?$param['corporateId']:'';
        $addressProofType = isset($param['addressProofType'])?$param['addressProofType']:'';
         
        if(trim($addressProofNo)=='' || trim($addressProofType)=='')
             throw new Exception('Address Proof Number not found!');
         
        $where = "cd.address_proof_number ='".$addressProofNo."' AND cd.address_proof_type='".$addressProofType."' AND cd.status='".STATUS_ACTIVE."'";
        if($corporateId > 0)
            $where .= " AND corporate_user_id !='".$corporateId."' AND (c.enroll_status = '".STATUS_APPROVED."' OR c.enroll_status = '".STATUS_PENDING."')";
        
          $select = $this->_db->select()
                ->from(DbTable::TABLE_CORPORATE_USER .' as c',array('id'))
                ->joinLeft(DbTable::TABLE_CORPORATE_USER_DETAILS .' as cd',"c.id = cd.corporate_user_id")
                ->where($where);
               
        $row = $this->_db->fetchRow($select);
        if(empty($row))
            return false;
        else
            throw new Exception('Address Proof Number Exists!');
    }
        
    /* checkPANDuplication() is responsible to verify the unique PAN number
     * it will accept the PAN and agent id in param array, and will return the exception message if duplicate else will return false
     */
    public function checkPANDuplication($param){
        $pan = isset($param['pan'])?$param['pan']:'';
        $corporateId = isset($param['corporateId'])?$param['corporateId']:'';
         
        if(trim($pan)=='')
             throw new Exception('PAN not found!');
         
        $where = "cd.pan_number ='".$pan."'";
        if($corporateId>0)
            $where .= " AND cd.corporate_user_id !='".$corporateId."' AND cd.status='".STATUS_ACTIVE."' AND (c.enroll_status = '".STATUS_APPROVED."' OR c.enroll_status = '".STATUS_PENDING."')";
        
        $select = $this->_db->select()
                ->from(DbTable::TABLE_CORPORATE_USER .' as c',array('id'))
                ->joinLeft(DbTable::TABLE_CORPORATE_USER_DETAILS .' as cd',"c.id = cd.corporate_user_id")
                ->where($where);
        //echo $select.'====='; exit;       
        $row = $this->_db->fetchRow($select);
        if(empty($row))
            return false;
        else
            throw new Exception('PAN Exists!');
    }
    
    public function updateCorporate($data,$id){
        $update = $this->_db->update(DbTable::TABLE_CORPORATE_USER_DETAILS,$data,"corporate_user_id=$id");
      
        return $update;
    }
    
    /**
        * 
        * @param type $corporateId
        * @return $arr;
    */
    public function getCorporateBinding($corporateId){
       $curdate = date("Y-m-d");   
    
       $select  = $this->_db->select()        
           ->from(DbTable::TABLE_CORPORATE_USER.' as a',array('a.id'))
           ->join(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION.' as ba', "ba.corporate_id = a.id AND '".$curdate ."' >= ba.date_start AND ('".$curdate."' <= ba.date_end OR ba.date_end = '0000-00-00' OR ba.date_end is NULL)", 'ba.product_id as bind_product_id')
           ->join(DbTable::TABLE_PRODUCTS.' as p', "ba.product_id = p.id ", array('p.bank_id as product_bank_id','p.id as product_id'))
           ->join(DbTable::TABLE_BANK.' as b', "p.bank_id = b.id ", array('b.logo as logo_bank','b.unicode as bank_unicode','p.unicode as product_unicode'))
           ->where("a.id=?", $corporateId)
           ->order("ba.id ASC");
       
       $detailArr = $this->_db->fetchRow($select);
       
       return $detailArr;
        
    }
    public function getApprovedCorporates()
    {
            return $this->fetchAll($this->select()
                            ->where('enroll_status=?', STATUS_APPROVED));
            
    }
    
     public function corpDoclist($corpId){
        
          if (!is_numeric($corpId)) {
            return array();
        }
        
        $select = $this->select() 
                ->from(DbTable::TABLE_CORPORATE_USER.' as a')
                 ->setIntegrityCheck(false)
                ->join(DbTable::TABLE_DOCS." as d", "a.id=d.doc_corporate_id",array('d.doc_corporate_id','d.doc_type','d.file_name','d.status'));
               $select->where('d.doc_corporate_id = ?', $corpId);
               $select->where("d.status = '".STATUS_ACTIVE."' ");
            
        return $this->fetchAll($select);
    }
    
     public function getCorpBalance($corpId){
        if (!is_numeric($corpId)) {
            return array();
        }
        
        $select = $this->select();
        
                $select->from(DbTable::TABLE_CORPORATE_BALANCE." as a",array('a.*',"DATE_FORMAT(date_modified,'%d-%m-%Y') as date_mod"))
                ->setIntegrityCheck(false)
                ->where('corporate_id = ?', $corpId);       
      // echo $select->__toString();
        return $this->fetchRow($select);
    }
   private function getCorpproductSql($id = 0){
         $details = $this->select()
                ->from(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION." as bapc",array('bapc.*',"DATE_FORMAT(bapc.date_end, '%d-%m-%Y') as date_end_formatted","DATE_FORMAT(bapc.date_start, '%d-%m-%Y') as date_start_formatted"))
                ->setIntegrityCheck(false)
                ->joinLeft(DbTable::TABLE_PLAN_COMMISSION." as pc", "bapc.plan_commission_id=pc.id",array('pc.id as c_id','pc.name as commission_name'))
                ->joinLeft(DbTable::TABLE_PLAN_FEE." as pf", "bapc.plan_fee_id=pf.id",array('pf.id as f_id','pf.name as fee_name'))
                ->joinLeft(DbTable::TABLE_CORPORATE_USER." as a", "bapc.corporate_id=a.id",array('a.id as a_id'))
                ->joinLeft(DbTable::TABLE_PRODUCTS." as p", "bapc.product_id = p.id ",array('p.name as product_name','p.id as pid'))
                ->where("(bapc.date_end = '0000-00-00' OR bapc.date_end >= bapc.date_start)")
                ->where ("a.enroll_status ='".STATUS_APPROVED."'")
                ->where ("a.id = $id")
                ->where ("bapc.status = '".STATUS_ACTIVE."'")
                ->order(array('p.name','bapc.date_start'));
        return $details;
        
    }
    public function getCorpproductDetailsAsArray ($id){
       
        $curdate = date("Y-m-d");
        $details = $this->getCorpproductSql($id);;
     
       //echo $details->__toString();
       
       return $this->fetchAll($details);
       
        
        
    }
    public function findDetailsById($agentId, $force = FALSE){
        if (!is_numeric($agentId)) {
            return array();
        }
        
        $select = $this->select()    
                ->from(DbTable::TABLE_CORPORATE_USER.' as a',array('*'))
                ->setIntegrityCheck(false)
                ->joinLeft(DbTable::TABLE_CORPORATE_USER_DETAILS." as ag", "ag.corporate_user_id=a.id AND ag.status = '".STATUS_ACTIVE."'",array('*'));
                 $select->where('a.id = ?', $agentId);
               //$select->where("ag.status = '".STATUS_ACTIVE."' ");
//       echo $select->__toString();  
        return $this->fetchRow($select);
    }
    
    public function updateMobile($mobile){
         
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            throw new Zend_Exception('You must be an authenticated user in the application in order to be able to call this method');
        }
        
        $user = Zend_Auth::getInstance()->getIdentity();
        $this->update(
            array('mobile' => $mobile,
                  'date_updated' => new Zend_Db_Expr('NOW()')),
            $this->_db->quoteInto('id = ?', $user->id)
        );
    }
    public function checkMobile($phone,$old_phone){
        try{
            $user = Zend_Auth::getInstance()->getIdentity();
            
              $where = " id = '".$user->id."' AND  mobile = '".$old_phone."' AND (enroll_status = '".STATUS_APPROVED."' OR enroll_status = '".STATUS_PENDING."')";
            $selectphone = $this->select()
                           ->where($where);
            $corporatePhnData = $this->fetchRow($selectphone);
            if(empty($corporatePhnData)) {
                 return 'invalid_user';
            } 
            
            $where = " mobile = '".$phone."' AND (enroll_status = '".STATUS_APPROVED."' OR enroll_status = '".STATUS_PENDING."')";
            $selectphone = $this->select()
                           ->where($where);
            //echo $selectphone.'===='; exit;
            $corporatePhnData = $this->fetchRow($selectphone);
            
            if(!empty($corporatePhnData)) {
                 return 'phone_dup';
            }            
        }
        catch (Exception $e) {
            $this->setMessage($e->getMessage());
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            return false;
        }
        
    }
    public function changeMobile($mobile){
         
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            throw new Zend_Exception('You must be an authenticated user in the application in order to be able to call this method');
        }
        
        $user = Zend_Auth::getInstance()->getIdentity();
        $this->update(
            array('mobile' => $mobile,
                  'date_updated' => new Zend_Db_Expr('NOW()')),
            $this->_db->quoteInto('id = ?', $user->id)
        );
    }
    public function checkEmail($oldemail,$newemail){
        try{
            $user = Zend_Auth::getInstance()->getIdentity();
            
              $where = " id = '".$user->id."' AND  email = '".$oldemail."' AND (enroll_status = '".STATUS_APPROVED."' OR enroll_status = '".STATUS_PENDING."')";
            $selectemail = $this->select()
                           ->where($where);
            $corporateEmailData = $this->fetchRow($selectemail);
            if(empty($corporateEmailData)) {
                 return 'invalid_user';
            } 
            
            $where = " email = '".$newemail."' AND (enroll_status = '".STATUS_APPROVED."' OR enroll_status = '".STATUS_PENDING."')";
            $selectemail = $this->select()
                           ->where($where);
            //echo $selectphone.'===='; exit;
            $corporateEmailData = $this->fetchRow($selectemail);
            
            if(!empty($corporateEmailData)) {
                 return 'email_dup';
            }            
        }
        catch (Exception $e) {
            $this->setMessage($e->getMessage());
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            return false;
        }
        
    }
    
    public function updateAuthChk($id, $ver_code)
    {
        $select = $this->_db->select()
                    ->from(DbTable::TABLE_EMAIL_VERIFICATION)
                    ->where('corporate_id =?',$id)
                    ->where('verification_code =?',$ver_code); 
        $row = $this->_db->fetchRow($select);
         
        $emailveification = $this->_db->select()
                    ->from(DbTable::TABLE_EMAIL_VERIFICATION)
                    ->where('verification_code =?',$ver_code); 
        $emailveificationData = $this->_db->fetchRow($emailveification);
        
        if(!empty($row)){
             
             
            $corporateArr = array('email'=>$emailveificationData['email'],'email_verification_id'=>$emailveificationData['id']);

            $activationArr = array('datetime_verified'=>new Zend_Db_Expr('NOW()'),'activation_status'=>STATUS_VERIFIED);

            $updateCorporate = $this->update($corporateArr,"id=$id");

            $updateActivation = $this->_db->update(DbTable::TABLE_EMAIL_VERIFICATION,$activationArr,"verification_code='$ver_code'");

            return TRUE;
            
        } 
        return FALSE;
      
    }
    
    public function sendCorporateEmailVerificationCode($email){
        
        $user = Zend_Auth::getInstance()->getIdentity();
       
        // generate Verification code for email verification
        $verification_code = Util::hashVerification($user->corporate_code);
        //echo $verificationCode;
        $this->session->ver_code = $verificationCode;
        $activationArr = array ('corporate_id'=>$user->id,
            'verification_code'=>$verification_code,'email'=>$email,
            'datetime_send'=>new Zend_Db_Expr('NOW()'),
            'activation_status'=>STATUS_PENDING,'remarks'=>'Email verification sent');
        
        $activation_id = $this->_db->insert(DbTable::TABLE_EMAIL_VERIFICATION,$activationArr);
        if($activation_id){
                $act_id = $this->_db->lastInsertId(DbTable::TABLE_EMAIL_VERIFICATION, 'id');
        }else{
               throw new Exception("Invalid data for insert. Please try again");
                exit;
        }
       
        $m = new App\Messaging\MVC\Axis\Corporate();
        try {
            $m->updateCorporateEmailVerification(array(
                'name'   => '',
                'first_name'   => $user->first_name,
                'last_name'   => $user->last_name,
                'email'        => $email,
                'verification_code' => $verification_code,
                'agent_code'   => $user->corporate_code,
                'password'   => $user->password,
                'id' => $user->id), 'operation');
        }
        catch (Exception $e ) {  
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $errMsg = $e->getMessage();
                    $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => $errMsg,
                        )
                    );
        }
    }
    
    public function getCorporatePhoto($corporateId){

        $select  = $this->_db->select()        
            ->from(DbTable::TABLE_CORPORATE_USER_DETAILS.' as a',array('a.profile_photo'))
            ->where("a.corporate_user_id =?", $corporateId)
            ->where("a.status=?", STATUS_ACTIVE);
        $detailArr = $this->_db->fetchRow($select);
        return $detailArr['profile_photo'];
        
    } 
}