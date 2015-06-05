<?php

class AgentUser extends BaseUser
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
    protected $_name = DbTable::TABLE_AGENTS;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    protected $_rowClass = 'App_Table_AgentUser';
    
    
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
    
    public function checkPhone($phone,$userid = FALSE){
        
        try{
            
            $where = " mobile1 = '".$phone."' AND (enroll_status = '".STATUS_APPROVED."' OR enroll_status = '".STATUS_PENDING."')";
            
            
            if($userid)
            {
               $where .=  " AND id = ".$userid;
            }
            
            
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
     public function getRegisteredCardholderCount($agentId)
    {
        $select  = $this->_db->select()              
                    //->setIntegrityCheck(FALSE)
                    ->from(DbTable::TABLE_CARDHOLDER_DETAILS.' as cd',array("count(*) as count"))
                    ->joinLeft(DbTable::TABLE_CARDHOLDERS." as ch", "cd.cardholder_id=ch.id", array('id'))
                    ->where("ch.reg_agent_id=?",$agentId)
                    //->where("registration_type='agent'")
                    ->where("cd.status='active'");
        
        $rs = $this->_db->fetchRow($select);
        if(empty($rs)) {
            return 0;
        }
        return $rs['count'];
        //echo "<pre>";print_r($rs);
    }
    
    public function checkAfn($afn){
        
                 
                $select = $this->_db->select();                
                $select->from(DbTable::TABLE_AGENT_DETAILS.' as ad');
                $select->joinInner(DbTable::TABLE_AGENTS." as a", "ad.agent_id = a.id AND (a.enroll_status='".STATUS_APPROVED."' OR a.enroll_status='".STATUS_PENDING."')", array('a.id'));   
                $select->where('ad.afn=?',$afn);
                $select->where('ad.status=?',STATUS_ACTIVE);       
        
           $afnData = $this->_db->fetchRow($select);
           return $afnData;
        
    }
    
    public function getEmailVerificationId($ver_code){
        $select = $this->_db->select()
                    ->from(DbTable::TABLE_EMAIL_VERIFICATION)
                    ->where('verification_code =?',$ver_code); 
       
        
         $row = $this->_db->fetchRow($select);
         return $row['id'];
    }
      public function emailAuth($id, $ver_code)
    {
        $select = $this->_db->select()
                    ->from(DbTable::TABLE_EMAIL_VERIFICATION)
                    ->where('agent_id =?',$id)
                    ->where('verification_code =?',$ver_code); 
       // echo $select->__toString();
        
         $row = $this->_db->fetchRow($select);
         
         $verification_id = $this->getEmailVerificationId($ver_code);
         if(!empty($row)){
             
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

    
    public function emailAuthChk($id, $ver_code)
    {
        $select = $this->_db->select()
                    ->from(DbTable::TABLE_EMAIL_VERIFICATION)
                    ->where('agent_id =?',$id)
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







    public function updateAgent($data,$id){
       
        
         /*$agents = new Agents();
         $idNum = isset($data['Identification_number']) ? $data['Identification_number'] : '';
         $addNum = isset($data['address_proof_number']) ? $data['address_proof_number'] : '';
         $pan = isset($data['pan_number']) ? $data['pan_number'] : '';
         
         $chkpan = $agents->agentPan($pan,$id);
         $chkid = $agents->agentidNo($idNum,$id);
         $chkadd = $agents->agentaddressNo($addNum,$id);
         //checking Id num duplication
         
         
         if(isset($data['Identification_number']) && empty($chkid)){
            
         $selectidnum = $this->_db->select()
                    ->from("t_agent_details")
                    ->where('Identification_number =?',$idNum);
       
         $idData = $this->_db->fetchAll($selectidnum);
        
       
             if(!empty($idData) ) {
        
            throw new Exception ('Identification Number Exists!');
            exit;
             }  
         }
         //checking PAN num duplication
         if(isset($data['pan_number']) && empty($chkpan) && $data['pan_number']!=ucfirst(STATUS_APPLIED)){
             if(!empty($pan)){
         $selectidnum = $this->_db->select()
                    ->from("t_agent_details")
                    ->where('pan_number =?',$pan);
       
         $panData = $this->_db->fetchAll($selectidnum);
        
       
             if(!empty($panData) ) {
      
           throw new Exception ('Pan Number Exists!');
           exit;
             }  
         }
       }
         
        //checking Address proof num duplication
         if(isset($data['address_proof_number']) && empty($chkadd)){
         $selectidnum = $this->_db->select()
                    ->from("t_agent_details")
                    ->where('address_proof_number =?',$addNum);
       
         $addData = $this->_db->fetchAll($selectidnum);
       
       
             if(!empty($addData) ) {
       
           throw new Exception ('Address proof Number Exists!');
           exit;
             }  
         }*/
        
        $update = $this->_db->update(DbTable::TABLE_AGENT_DETAILS,$data,"agent_id=$id");
      
        return $update;
       
        
    }
    
    
    
    
      
    
     public function signupAgent($param, $agn_info)
    {             
        $agentsModel = new Agents();
        
        $emailcheck = $agentsModel->emailDuplication($param['email']);
        
//        $afncheck = $this->checkAfn($agn_info['afn']);
//         
//        if(!empty($afncheck) ) {
//           throw new Exception ('Application Form Number Exists!');
//           exit;
//        } 
        if(!empty($emailcheck)) {

            throw new Exception ('Email Exists!');
            exit;
        }  

        // Insert into t_agents
         if(isset($param['parent_id'])) {
             
             $parent_id = $param['parent_id'];
             unset($param['parent_id']);
         }
         
         $param['ip'] = $this->formatIpAddress(Util::getIP());
         $this->insert($param);
         
         $id = $this->_db->lastInsertId(DbTable::TABLE_AGENTS, 'id');
         // Assign agent code and enter it in DB through the BaseUser::generateUserCode() function
         $agent_code = BaseUser::generateUserCode($id);
         $agn_info['agent_id'] = $id;
         
                    
         if($id>0){ 
             
             // adding agent details to t_agent_details
              $this->_db->insert(DbTable::TABLE_AGENT_DETAILS,$agn_info);   
              //if(isset($param['parent_agent_id']) && !empty($param['parent_agent_id'])) {
              $agentType = $this->getAgentType($id);
              if(($agentType == SUB_AGENT || $agentType == DISTRIBUTOR_AGENT) && $parent_id > 0) {
                  $agentLimit = new Agentlimit();
                  
                  $agentLimitInfo = $agentLimit->getAgentlimitAsArray($parent_id);
                 if(!empty($agentLimitInfo) && !empty($agentLimitInfo[0]['agent_limit_id'])) {
                     $user = Zend_Auth::getInstance()->getIdentity();
                     $agentlimitModel = new Agentlimit();
                     $row['agent_id'] = $id;
                     $row['by_ops_id'] = '0';
                     $row['by_agent_id'] = $user->id;
                     $row['date_start'] = new Zend_Db_Expr('CURDATE()');
                     $row['agent_limit_id'] = $agentLimitInfo[0]['agent_limit_id'];
                     $agentlimitModel->saveagentlimit($row);
                  }
              }
         }
         return $id;         
    }
    
    public function sendVerificationCode($id,$verification_code,$detailsArr){
       
        $check = $this->_db->select('id')
               ->from(DbTable::TABLE_EMAIL_VERIFICATION)
                ->where('verification_code =?',$verification_code);
                $activation = $this->_db->fetchRow($check);
                
                if (empty($activation)){
                
        $activationArr = array ('agent_id'=>$id,
             'verification_code'=>$verification_code,'email'=>$detailsArr['email'],
             'datetime_send'=>new Zend_Db_Expr('NOW()'),
             'activation_status'=>STATUS_PENDING);
        $activation_id = $this->_db->insert(DbTable::TABLE_EMAIL_VERIFICATION,$activationArr);
        if($activation_id)
           $act_id = $this->_db->lastInsertId(DbTable::TABLE_EMAIL_VERIFICATION, 'id');
        
           $m = new App\Messaging\MVC\Axis\Agent();
             try {
             $m->agentEmailVerification(array(
                 'name'   => $detailsArr['name'],
                 'first_name'   => $detailsArr['first_name'],
                 'last_name'   => $detailsArr['last_name'],
                 'email'        => $detailsArr['email'],
                 'verification_code' => $verification_code,
                 'agent_code'   => $this->getAgentCode($id),
                 'password'   => $detailsArr['password'],
                 'id' => $id), 'operation');
             
             }
             catch (Exception $e ) { 
                                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                $errMsg = $e->getMessage();
                                $this->_helper->FlashMessenger(
                                    array(
                                        'msg-success' => $errMsg,
                                    )
                                );
                    }
                    
                    
                    return $act_id;
                }
                    else {
                        return FALSE;
                    }
        
    }
   
    private function setMessage($msg)
    {
        $this->_msg = $msg;
    }
    
    
    private function getMessage($msg)
    {
        return $this->_msg;
    }
    
    
    public function getAgentCode($id){
        $agentcode = $this->select('agent_code')              
                ->where("id=$id");
        $code = $this->fetchRow($agentcode);
        return $code['agent_code'];
                
    }
    
     public function emailVerified($agent_code){
        $verified = $this->_db->select()  
                ->from(DbTable::TABLE_EMAIL_VERIFICATION.' as e',array('e.agent_id','e.activation_status'))
                ->joinLeft(DbTable::TABLE_AGENTS.' as a',"e.agent_id=a.id",array('a.id','a.agent_code'))
                ->where("a.agent_code = '$agent_code'");
        
        
        $check = $this->_db->fetchRow($verified);
        return $check['activation_status'];
                
    }
    
    public function agentApproved($agent_code){
        $approved  = $this->select('enroll_status,status')              
                ->where("agent_code='$agent_code'")
                ->where("enroll_status='".STATUS_APPROVED."'")
                ->where("status='".STATUS_UNBLOCKED."'");
        
        $check = $this->fetchRow($approved);
        
        return $check;
                
    }
    public function getAgentStatus($email){
        $select  = $this->select()              
                ->where("email='$email'")
                ->where("enroll_status = '".STATUS_APPROVED."'
                    OR enroll_status = '".STATUS_PENDING."'  ");
               // ->where("enroll_status='".STATUS_APPROVED."'")
              //  ->where("status='".STATUS_UNBLOCKED."'");
        $result = $this->fetchRow($select);
        
        return $result;
                
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
    
    
    public function changeMobileNumber($mobile,$userid)
    {
        $this->update(
            array('mobile1' => $mobile),
            $this->_db->quoteInto('id = ?', $userid)
        );
        
    }
    
    /* checkPasswordDuplicate() will fetch last 4 pwds and check for duplicacy
     * as param : accepts ops id , password
     */
    public function checkPasswordDuplicate($param){
        $password = isset($param['password'])?$param['password']:'';
        $agentId = isset($param['agent_id'])?$param['agent_id']:'';
        
        if ($password!='' && $agentId!='') {
            
        $select  = $this->_db->select()   
        ->from(DbTable::TABLE_LOG_CHANGE_PASSWORD, array('password'))
        ->where("agent_id=?", $agentId)
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
  
     public function newPassword($password ,$id){
      
       
       $password = BaseUser::hashPassword($password);
        
        $update = $this->update(
            array('password' => $password),
            $this->_db->quoteInto('id = ?', $id)
        );
        
        $updateLogArr = array('date_modified' => new Zend_Db_Expr('NOW()'),'status'=> STATUS_INACTIVE);
        
        $this->_db->update(DbTable::TABLE_LOG_FORGOT_PASSWORD, $updateLogArr, "agent_id = ".$id." AND status='".STATUS_ACTIVE."'");
        
       
        return TRUE;
        
    }
    
    
    public function checkAgentDetails($data){
        $select  = $this->_db->select()        
        ->from(DbTable::TABLE_AGENTS.' as a',array('a.id', 'a.agent_code','a.email','a.mobile1','a.email','a.first_name','a.last_name'))
        ->joinLeft(DbTable::TABLE_AGENT_DETAILS." as ad", "a.id=ad.agent_id and ad.status = '".STATUS_ACTIVE."'", array('ad.Identification_type','ad.Identification_number','ad.date_of_birth'))
        ->where("a.email=?", $data['email'])
        ->where("a.mobile1=?", $data['mobile1'])
        ->where("a.enroll_status=?", STATUS_APPROVED)
        //->where("ad.Identification_type=?", $data['Identification_type'])
        ->where("ad.date_of_birth=?", Util::returnDateFormatted($data['date_of_birth'], 'd-m-Y', 'Y-m-d'));
        //->where("ad.Identification_number=?", $data['Identification_number']);
        
        $detailArr = $this->_db->fetchRow($select);
        //echo $select->__toString();exit;
         //echo "<pre>";print_r($detailArr);exit;
        if(!empty($detailArr)){
        
        $logArr = array('agent_id'=>$detailArr['id'],'ip'=> $this->formatIpAddress(Util::getIP())
            );
         //echo "<pre>";print_r($logArr);exit;
        $insertIntoLog = $this->_db->insert(DbTable::TABLE_LOG_FORGOT_PASSWORD,$logArr);
        
        
        $alert = new Alerts();
        $detailArr['conf_code'] = Alerts::generateAuthCode();
        $m = new App\Messaging\MVC\Axis\Agent();
        $flg = $m->confCode($detailArr);
        return $detailArr;
        }
        else{
        return False;
        }
    }
    
    public function getLowBalanceAgents($param)
    {
        $agMinBal = isset($param['agent_minimum_balance'])?$param['agent_minimum_balance']:'';
        $agentID = isset($param['agent_id'])?$param['agent_id']:'';
        if($agMinBal<1)
            return array();
        
        $select  = $this->_db->select()        
        //$select->setIntegrityCheck(FALSE)
        ->from(DbTable::TABLE_AGENTS.' as a',array('a.email', 'concat(a.first_name," ",a.last_name) as agent_name', 'a.mobile1', 'a.agent_code'))
        ->joinLeft(DbTable::TABLE_AGENT_DETAILS." as ad", "a.id=ad.agent_id", array())
        ->joinLeft(DbTable::TABLE_AGENT_BALANCE." as ab", "a.id=ab.agent_id", array('ab.amount as current_balance'))
      //   ->joinLeft(DbTable::TABLE_AGENT_BALANCE_SETTINGS." as abs", "a.id=abs.agent_id", array('abs.value as min_balance'))       
        ->where("a.status=?", UNBLOCKED_STATUS)
        ->where("a.enroll_status=?", ENROLL_APPROVED_STATUS)
        ->where("a.email_verification_status=?", EMAIL_VERIFIED_STATUS)
        ->where("ad.status=?", AGENT_ACTIVE_STATUS)
        ->where("ab.amount<?", $agMinBal);
        if($agentID !=''){
         $select->where("a.id=?", $agentID);   
        }
        
        //echo $select; exit;
        
        return $this->_db->fetchRow($select);      
    }
    
    /* sendLowBalanceAlert()
     * that function will fetch the agent with low balance and send the email to these agents and ops
     */
    public function sendLowBalanceAlert(){
        //throw new Exception('testing exception message');
        $opsData = array();
        $objAGSetting = new AgentSetting();
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $agSettingData = $objAGSetting->getAllAgentMinMaxSetting(array('type'=>SETTING_AGENT_MIN_BALANCE));
        $agSettingDataArr = $agSettingData->toArray(); 
        
        $totalAgent = 0;
        //
        $m = new App\Messaging\MVC\Axis\Operation();
        foreach($agSettingDataArr as $key=> $agSettingDataArrValue)
        {
        $agMinBal = $agSettingDataArrValue['value'];
        
        $agentId = $agSettingDataArrValue['agent_id'];
        $lba = $this->getLowBalanceAgents(array('agent_minimum_balance'=>$agMinBal,'agent_id'=>$agentId));   
       
        if(!empty($lba)){
         
            $totalAgents = count($lba);
            
            $a = new App\Messaging\MVC\Axis\Agent();
            $emailData = $lba;
            $emailData['agent_minimum_balance'] = $agMinBal;
            
            $opsData['agentInfo'][$totalAgent] = $lba;
            $opsData['agentInfo'][$totalAgent]['agent_minimum_balance'] = $agMinBal;
          //  $opsData['agent_minimum_balance'][] = $agMinBal;
        
            // sending notice email to agent now 

            $a->agentLowBalance($emailData); 
//            
            // sending notice mail to ops now 
          //  $opsData['agentInfo'] = $lba;
          //  $opsData['agent_minimum_balance'] = $agMinBal;
            //$opsData['ops_name'] = $user->firstname.' '.$user->lastname;
           
         //   $m->agentLowBalanceOps($opsData); 
         //   return $totalAgents;
               $totalAgent +=1; 
        }
        
        
        
    }//
   
    if($totalAgent > 0){
         $m->agentLowBalanceOps($opsData); 
     }
    return $totalAgent;
    //    return 0;
    }
    
     /* agentMaxBalanceValue()
     * that function will fetch the agent with low balance and send the email to these agents and ops
     */
    
    
    public function getAgentsForDD($param)
    {
                
        $select  = $this->_db->select();        
        //$select->setIntegrityCheck(FALSE)
        $select->from(DbTable::TABLE_AGENTS.' as a',array('a.id', 'concat(a.first_name," ",a.last_name) as agent_name',));
        $select->joinLeft(DbTable::TABLE_AGENT_DETAILS." as ad", "a.id=ad.agent_id", array());
                
        if(isset($param['status']))
            $select->where("a.status IN ('". $param['status']."')");
        
        if(isset($param['enroll_status']))
            $select->where("a.enroll_status=?", $param['enroll_status']);
        
//        if(isset($param['email_verified_status']))
//            $select->where("a.email_verification_status=?", $param['email_verified_status']);
        
        if(isset($param['agent_details_status']))
            $select->where("ad.status=?", $param['agent_details_status']);
        
         if(isset($param['bank_products'])){
           $select->joinLeft(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION." as bind", "a.id=bind.agent_id",array());
           $select->where("bind.product_id IN (".$param['bank_products'].")");
           
         }
        
         $select->order('a.first_name');
        $agents = $this->_db->fetchAll($select);     
        $dataArray = array();
        foreach ($agents as $id => $val) {
            $dataArray[$val['id']] = $val['agent_name'];
        }
        return $dataArray;     
        
    }
    
    
  
    
     
    /* getAgentActivation function will return provide the agent activation details for Agent Activation report 
     * it will accept the duration, city, and date as in param array
     */
    
    public function getAgentActivation($param, $page = 1, $paginate = NULL){ 
    
        $select = $this->sqlAgentActivation($param);
        //echo $select->__toString();exit;       
        return $this->_paginate($select, $page, $paginate);        
    }
    
    
    /* exportAgentActivation function will return provide the agent activation details for Agent Activation report csv
     * it will accept the duration, city, and date as in param array
     */
    
    public function exportAgentActivation($param){ 
    
        $select = $this->sqlAgentActivation($param);
        //echo $select->__toString();exit;       
        return $this->_db->fetchAll($select);       
    }
    
    /* sqlAgentActivation function will return query for Agent Activation report 
     * it will accept the duration, city, and date as in param array
     */
    
    public function sqlAgentActivation($param){
        
            $select  = $this->select();//$this->_db->select();        
            $select->setIntegrityCheck(FALSE);
            $agentEstabCity = isset($param['city'])?$param['city']:'';
            $agentEstabState = isset($param['state'])?$param['state']:'';
            $to = isset($param['to'])?$param['to']:'';
            $from = isset($param['from'])?$param['from']:'';
//            if($from=='')
//                $param['from']=$to;
            
            $whereCity = '';          
            if($agentEstabCity!=''){
               $whereCity = " AND (ad.estab_city = '".$agentEstabCity."')";
            }
            
            $whereState = "(ad.estab_state = '".$agentEstabState."' )";
            $select->from(DbTable::TABLE_AGENTS.' as a',array('a.agent_code', 'DATE_FORMAT(a.reg_datetime, "%d-%m-%Y") as agent_app_date', 'a.enroll_status as status'));
            $select->joinInner(DbTable::TABLE_AGENT_DETAILS." as ad", "a.id=ad.agent_id AND ad.status='".STATUS_ACTIVE."' $whereCity AND $whereState", array('concat(ad.first_name," ",ad.last_name) as agent_name', 'ad.mobile1', 'ad.email', 'concat(ad.estab_address1," ",ad.estab_address2) as agent_address', 'ad.estab_city as agent_city'));
            $select->joinLeft(DbTable::TABLE_CHANGE_STATUS_LOG." as csl", "a.id=csl.agent_id AND a.status='".STATUS_REJECTED."'", array('csl.remarks as rejected_remarks'));
            $select->joinLeft(DbTable::TABLE_BIND_AGENT_LIMIT." as bal", "a.id=bal.agent_id AND bal.date_start <= '".$param['to']."' AND (bal.date_end >= '".$param['to']."' OR bal.date_end = '0000-00-00')", array('bal.agent_limit_id'));
            $select->joinLeft(DbTable::TABLE_AGENT_LIMIT." as al", "bal.agent_limit_id=al.id", array('al.name as agent_limit_name'));
            $select->joinLeft(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION." as bapc", "a.id=bapc.agent_id AND bapc.date_start <= '".$param['to']."' AND (bapc.date_end >= '".$param['to']."' OR bapc.date_end = '0000-00-00')", array('bapc.plan_commission_id', 'bapc.product_id'));
            $select->joinLeft(DbTable::TABLE_PLAN_COMMISSION." as pc", "bapc.plan_commission_id=pc.id", array('pc.name as commission_plan_name'));
            $select->joinLeft(DbTable::TABLE_PRODUCTS." as p", "bapc.product_id=p.id", array('p.ecs_product_code as product_code'));
            $select->joinLeft(DbTable::TABLE_BANK." as b", "p.bank_id=b.id", array('b.name as bank_name'));
            
            
            if($from !='' && $to !='')
                $select->where("DATE(a.reg_datetime) BETWEEN '". $from ."' AND '". $to ."'");
            else if($to !='')
                $select->where(" DATE(a.reg_datetime) <= '".$to."'");
            
            $select->order('ad.first_name ASC');
         // echo $select; //EXIT;
         return  $select;
    } 
    
    
    public function editAgent($param, $id){
       if($id>0){
         $update = $this->_db->update(DbTable::TABLE_AGENTS,$param,"id=$id");
         return $update;
       } else return '';
    }
    
    /*  getAgentLoadAndCount function will fetch the count of agent's cardholder registrations successfully and total first load amount
     *  it will expect agent id and datefrom and dateto in param array
     */
    public function getAgentLoadAndCount($param){
        $agentId  = isset($param['agentId'])?$param['agentId']:'';
        $dateFrom = isset($param['dateFrom'])?$param['dateFrom']:'';
        $dateTo   = isset($param['dateTo'])?$param['dateTo']:'';
        
        if($agentId>0 && $dateFrom!='' && $dateTo!=''){
            $select  = $this->_db->select()        
            ->from(DbTable::TABLE_TXN_AGENT.' as ta',array('count(*) as total_ch_registered', 'sum(amount) as total_loaded_amount'))
            ->where("ta.agent_id=?", $agentId)
            ->where("ta.date_created>=?", $dateFrom)
            ->where("ta.date_created<=?", $dateTo)
            ->where("ta.txn_type=?", TXNTYPE_FIRST_LOAD)
            ->where("ta.txn_status=?", STATUS_SUCCESS)
            ->where("ta.mode=?", TXN_MODE_DR);
            //echo $select;
            return $this->_db->fetchRow($select);
        } else return '';
    }
    
    
    /*  getAgentReloadAndCount function will fetch the count of agent's cardholder reloaded and total reload amount
     *  it will expect agent id and datefrom and dateto in param array
     */
    public function getAgentReloadAndCount($param){
        $agentId  = isset($param['agentId'])?$param['agentId']:'';
        $dateFrom = isset($param['dateFrom'])?$param['dateFrom']:'';
        $dateTo   = isset($param['dateTo'])?$param['dateTo']:'';
        
        if($agentId>0 && $dateFrom!='' && $dateTo!=''){
            $select  = $this->_db->select()        
            ->from(DbTable::TABLE_TXN_AGENT.' as ta',array('count(*) as total_ch_reloaded', 'sum(amount) as total_reloaded_amount'))
            ->where("ta.agent_id=?", $agentId)
            ->where("ta.date_created>=?", $dateFrom)
            ->where("ta.date_created<=?", $dateTo)
            ->where("ta.txn_type=?", TXNTYPE_CARD_RELOAD)
            ->where("ta.txn_status=?", STATUS_SUCCESS)
            ->where("ta.mode=?", TXN_MODE_DR);
        
            return $this->_db->fetchRow($select);
        } else return '';
    }
   
    /*  getApprovedAgentsCount function will fetch the count of approved agents
     *  it will expect datefrom and dateto in param array
     */
    public function getApprovedAgentsCount($param){
        $dateFrom = isset($param['dateFrom'])?$param['dateFrom']:'';
        $dateTo   = isset($param['dateTo'])?$param['dateTo']:'';
        
        if($dateFrom!='' && $dateTo!=''){
            $select  = $this->_db->select()        
            ->from(DbTable::TABLE_AGENTS,array('count(*) as approved_agents_count'))
            ->where("approval_datetime>=?", $dateFrom)
            ->where("approval_datetime<=?", $dateTo)
            ->where("enroll_status=?", ENROLL_APPROVED_STATUS);
            //echo $select; exit;
            return $this->_db->fetchRow($select);
        } else return array();
    }
    
    
    /*  getPendingAgentsCount function will fetch the count of approved agents
     *  it will expect datefrom and dateto in param array
     */
    public function getPendingAgentsCount($param){
        $dateFrom = isset($param['dateFrom'])?$param['dateFrom']:'';
        $dateTo   = isset($param['dateTo'])?$param['dateTo']:'';
        
        if($dateFrom!='' && $dateTo!=''){
            $select  = $this->_db->select()        
            ->from(DbTable::TABLE_AGENTS,array('count(*) as pending_agents_count'))
            ->where("reg_datetime>=?", $dateFrom)
            ->where("reg_datetime<=?", $dateTo)
            ->where("enroll_status=?", STATUS_PENDING);
            //echo $select; exit;
            return $this->_db->fetchRow($select);
        } else return array();
    }
    
    
    /*  getAgentPendingFund function will fetch the agents funds request total in pending status
     *  it will expect datefrom and dateto in param array
     */
    public function getAgentPendingFund($param){
        $dateFrom = isset($param['dateFrom'])?$param['dateFrom']:'';
        $dateTo   = isset($param['dateTo'])?$param['dateTo']:'';
        
        if($dateFrom!='' && $dateTo!=''){
            $select  = $this->_db->select()        
            ->from(DbTable::TABLE_AGENT_FUND_REQUEST,array('sum(amt) as agent_pending_fund'))
            ->where("datetime_request>=?", $dateFrom)
            ->where("datetime_request<=?", $dateTo)
            ->where("request_status=?", STATUS_PENDING);
            
            //echo $select; exit;
            return $this->_db->fetchRow($select);
        } else return array();
    }
    
    
    /*  getAgents function will fetch the agent details from agents table on enroll status basis
     *  it will expect enroll status in param
     */
    public function getAgents($param){
            $enrollStatus = isset($param['enrollStatus'])?$param['enrollStatus']:'';
            
            $select  = $this->_db->select()        
            ->from(DbTable::TABLE_AGENTS,array('id'));
            
            if($enrollStatus!=''){
                $select->where("enroll_status=?", $enrollStatus);
            }
//            echo $select; //exit;
            return $this->_db->fetchAll($select);
    }
    
    
     /*  removeIncompleteAgents function will fetch the agents with 'incomplete' status and will remove 
      *  these agents with thier concerned details from db
      */
    public function removeIncompleteAgents(){
            $agentParams = array('enrollStatus'=>STATUS_INCOMPLETE);
            
            // getting agents with incomplete status
            $incompleteAgents =  $this->getAgents($agentParams);
            $totalAgents = count($incompleteAgents);
            
            // deleting agents from agent's tables
            for($i=0;$i<$totalAgents;$i++){
                $agentId = $incompleteAgents[$i]['id'];
                $this->_db->delete(DbTable::TABLE_AGENTS,"id=$agentId");
                $this->_db->delete(DbTable::TABLE_AGENT_DETAILS,"agent_id=$agentId");
            }
            
      return $totalAgents;            
    }
    
    /* checkIdNumberDuplication() is responsible to verify the unique indentification number
     * it will accept the identification number and agent id and identification type in param array and will return the exception message if duplicate else will return false
     */
     public function checkIdNumberDuplication($param){
         $idNo = isset($param['idNo'])?$param['idNo']:'';
         $idType = isset($param['idType'])?$param['idType']:'';
         $agentId = isset($param['agentId'])?$param['agentId']:'';
         
         if(trim($idNo)=='' || trim($idType)=='')
             throw new Exception('Identification Number not found!');
         
        $where = "ad.Identification_number ='".$idNo."' AND ad.Identification_type='".$idType."' AND ad.status='".STATUS_ACTIVE."'";
        if($agentId>0) 
            $where .= " AND agent_id !='".$agentId."' AND (a.enroll_status = '".STATUS_APPROVED."' OR a.enroll_status = '".STATUS_PENDING."')";
        
        $select = $this->_db->select()
                ->from(DbTable::TABLE_AGENT_DETAILS .' as ad',array('id'))
                ->joinLeft(DbTable::TABLE_AGENTS .' as a',"a.id = ad.agent_id")
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
         $agentId = isset($param['agentId'])?$param['agentId']:'';
         $addressProofType = isset($param['addressProofType'])?$param['addressProofType']:'';
         
         if(trim($addressProofNo)=='' || trim($addressProofType)=='')
             throw new Exception('Address Proof Number not found!');
         
        $where = "ad.address_proof_number ='".$addressProofNo."' AND ad.address_proof_type='".$addressProofType."' AND ad.status='".STATUS_ACTIVE."'";
        if($agentId>0)
            $where .= " AND agent_id !='".$agentId."' AND (a.enroll_status = '".STATUS_APPROVED."' OR a.enroll_status = '".STATUS_PENDING."')";
        
        $select = $this->_db->select()
                ->from(DbTable::TABLE_AGENT_DETAILS .' as ad',array('id'))
                ->joinLeft(DbTable::TABLE_AGENTS .' as a',"a.id = ad.agent_id")
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
         $agentId = isset($param['agentId'])?$param['agentId']:'';
         
         if(trim($pan)=='')
             throw new Exception('PAN not found!');
         
        $where = "ad.pan_number ='".$pan."'";
        if($agentId>0)
            $where .= " AND ad.agent_id !='".$agentId."' AND ad.status='".STATUS_ACTIVE."' AND (a.enroll_status = '".STATUS_APPROVED."' OR a.enroll_status = '".STATUS_PENDING."')";
        
        $select = $this->_db->select()
                ->from(DbTable::TABLE_AGENTS .' as a',array('id'))
                ->joinLeft(DbTable::TABLE_AGENT_DETAILS .' as ad',"a.id = ad.agent_id")
                ->where($where);
        //echo $select.'====='; exit;       
        $row = $this->_db->fetchRow($select);
        if(empty($row))
            return false;
        else
            throw new Exception('PAN Exists!');
        }
    
        
        public function getApprovedAgents()
        {
            return $this->fetchAll($this->select()
                            ->where('enroll_status=?', STATUS_APPROVED));
            
        }
       
        /**
         * 
         * @param type $agentId
         * @return $arr;
         */
       public function getAgentBinding($agentId){
        $curdate = date("Y-m-d");   

        $select  = $this->_db->select()        
            ->from(DbTable::TABLE_AGENTS.' as a',array('a.id'))
            ->join(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION.' as ba', "ba.agent_id = a.id AND '".$curdate ."' >= ba.date_start AND ('".$curdate."' <= ba.date_end OR ba.date_end = '0000-00-00' OR ba.date_end is NULL)", 'ba.product_id as bind_product_id')
            ->join(DbTable::TABLE_PRODUCTS.' as p', "ba.product_id = p.id ", array('p.bank_id as product_bank_id','p.id as product_id'))
            ->join(DbTable::TABLE_BANK.' as b', "p.bank_id = b.id ", array('b.logo as logo_bank','b.unicode as bank_unicode','p.unicode as product_unicode'))
            ->where("a.id=?", $agentId)
            ->order("ba.id ASC");
        
        $detailArr = $this->_db->fetchRow($select);
        
        return $detailArr;
        
    } 
    
    public function getAgentPhoto($agentId){

        $select  = $this->_db->select()        
            ->from(DbTable::TABLE_AGENT_DETAILS.' as a',array('a.profile_photo'))
            ->where("a.agent_id=?", $agentId)
            ->where("a.status=?", STATUS_ACTIVE);
        $detailArr = $this->_db->fetchRow($select);
        return $detailArr['profile_photo'];
        
    } 
      public function authemailAuth($id, $ver_code)
    {
        $select = $this->_db->select()
                    ->from(DbTable::TABLE_EMAIL_VERIFICATION)
                    ->where('agent_detail_id =?',$id)
                    ->where('verification_code =?',$ver_code); 
       // echo $select->__toString();
        
         $row = $this->_db->fetchRow($select);
         
         $verification_id = $this->getEmailVerificationId($ver_code);
         if(!empty($row)){
             
             $agentArr = array('auth_email_verification_status'=>STATUS_VERIFIED,'auth_email_verification_id'=>$verification_id);
             
             $activationArr = array('datetime_verified'=>new Zend_Db_Expr('NOW()'),'activation_status'=>STATUS_VERIFIED);
            
             $updateAgent = $this->_db->update(DbTable::TABLE_AGENT_DETAILS,$agentArr,"id=$id");
             
             $updateActivation = $this->_db->update(DbTable::TABLE_EMAIL_VERIFICATION,$activationArr,"verification_code='$ver_code'");
             
             if($updateActivation && $updateAgent)
                 return TRUE;
             else 
                 return FALSE;
         
         }
          
      
    }
    
    
    public function isSubAgent($agentId,$superAgent=''){
        $select  = $this->select(array('parent_agent_id'))        
            ->where("id=?", $agentId);
        if(!empty($superAgent)) {
            $select->where('parent_agent_id=?',$superAgent);
        }
        $detailArr = $this->fetchRow($select);
        if($detailArr['user_type'] == SUB_AGENT_DB_VALUE) return TRUE;
        return false;
    } 
    
    public function isSuperAgent($agentId){
        $select  = $this->select('user_type')        
            ->where("id=?", $agentId);
        $detailArr = $this->fetchRow($select);
        if($detailArr['user_type'] == SUPER_AGENT_DB_VALUE) return TRUE;
        return false;
    } 
    
    public function isDistributorAgent($agentId){
        $select  = $this->select('user_type')        
            ->where("id=?", $agentId);
        $detailArr = $this->fetchRow($select);
        if($detailArr['user_type'] == DISTRIBUTOR_AGENT_DB_VALUE) return TRUE;
        return false;
    } 
    
    public function getAgentType($id) {
        
        $select  = $this->select()        
            ->where("id=?", $id);
        $detailArr = $this->fetchRow($select);
        if($detailArr['user_type'] == SUPER_AGENT_DB_VALUE) {
            return SUPER_AGENT;
        }elseif($detailArr['user_type'] == DISTRIBUTOR_AGENT_DB_VALUE) {
            return DISTRIBUTOR_AGENT;
        } elseif($detailArr['user_type'] == SUB_AGENT_DB_VALUE ) {
            return SUB_AGENT;
        } else {
            return NORMAL_AGENT;
        }
        
    }
    
        
    public function getObjectRelationshipLabel($userType) {
        if(empty($userType)) {
            return false;
        }
        
        switch ($userType)
        {
            case SUPER_AGENT:
                $flg = FALSE;
               break;
           
            case DISTRIBUTOR_AGENT:
                $flg = SUPER_TO_DISTRIBUTOR;
               break;
           
            case SUB_AGENT:
                $flg = DISTRIBUTOR_TO_AGENT;
               break;
           
            case NORMAL_AGENT:
                $flg = FALSE;
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
    
    
    public function getParentInfo($id) 
    {
       $uType = $this->getAgentType($id);
       $label = $this->getObjectRelationshipLabel($uType);
       $res = array();
       if(($uType == DISTRIBUTOR_AGENT) || ($uType == SUB_AGENT)) {
       $object = new ObjectRelations();
       $info =  $object->getFromObjectInfo($id, $label);
        if(!empty($info) && isset($info['from_object_id'])) {
             $select  = $this->select()        
                        ->from($this->_name, array('id','email','agent_code', 'concat(first_name," ", last_name) as agent_name'))
                 ->where("id=?", $info['from_object_id']);
             $detailArr = $this->fetchRow($select);
             $detailArr = Util::toArray($detailArr);
             if(!empty($detailArr)) {
                 $res = $detailArr;
             }
        }
              
       }
       return $res;
    }
    
    public function getUserTypeinHRF($id) {
        
        $uType = $this->getAgentType($id);
        
        switch ($uType)
        {
            case SUPER_AGENT:
                $flg = 'Super Partner';
               break;
           
            case DISTRIBUTOR_AGENT:
                $flg = 'Distributor Partner';
               break;
           
            case SUB_AGENT:
                $flg = 'Sub Partner';
               break;
           
            case NORMAL_AGENT:
                $flg = 'Partner';
               break;
           
           default :
               $flg = '-';
               break;
        }        
        return $flg;
    }
 public function getAgentsCommaList($bankUnicode) {
        // Select all agents option
        $bank = new Banks();
        $bankProduct = $bank->getProductsByBankUnicode($bankUnicode);
        $objAU = new AgentUser();
        $str = STATUS_UNBLOCKED . "', '" . STATUS_BLOCKED . "', '" . STATUS_LOCKED;
        
        $param = array('status' => $str, 'enroll_status' => ENROLL_APPROVED_STATUS, 'agent_details_status' => STATUS_ACTIVE, 'bank_products' => $bankProduct);
        
         $select  = $this->_db->select();  
         $select->distinct(TRUE);
        //$select->setIntegrityCheck(FALSE)
        $select->from(DbTable::TABLE_AGENTS.' as a',array('a.id', 'concat(a.first_name," ",a.last_name) as agent_name',));
        $select->joinLeft(DbTable::TABLE_AGENT_DETAILS." as ad", "a.id=ad.agent_id", array());
                
        if(isset($param['status']))
            $select->where("a.status IN ('". $param['status']."')");
        
        if(isset($param['enroll_status']))
            $select->where("a.enroll_status=?", $param['enroll_status']);
        
//        if(isset($param['email_verified_status']))
//            $select->where("a.email_verification_status=?", $param['email_verified_status']);
        
        if(isset($param['agent_details_status']))
            $select->where("ad.status=?", $param['agent_details_status']);
        
         if(isset($param['bank_products'])){
           $select->joinLeft(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION." as bind", "a.id=bind.agent_id",array());
           $select->where("bind.product_id IN (".$param['bank_products'].")");
           
         }
        $agentsArr = $this->_db->fetchAll($select);   
       
        $agent_ids = '';
        foreach ($agentsArr as $agents) {
            $agent_ids .= $agents['id'] . "','";
        }
        // remove last ,
        return substr($agent_ids, 0, -3);
    }
    
     public function checkRegisteredPhone($phone){
        
        try{
            
            $where = " mobile1 = '".$phone."' AND (enroll_status = '".STATUS_APPROVED."'  AND status = '".STATUS_UNBLOCKED."')";
            $selectphone = $this->select()
                           ->where($where);
//            echo $selectphone.'===='; exit;
            $agntphData = $this->fetchRow($selectphone);
            
              
        if(empty($agntphData)) {
            
            
             return 'not_exists';
              
        }            
            
        
          
        }
        catch (Exception $e) {
            $this->setMessage($e->getMessage());
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            return false;

        }
        
    }
    
    public function getAgentBindingProducts($agentId){
        $curdate = date("Y-m-d");   

        $select  = $this->_db->select()        
            ->from(DbTable::TABLE_AGENTS.' as a',array('a.id'))
            ->join(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION.' as ba', "ba.agent_id = a.id AND '".$curdate ."' >= ba.date_start AND ('".$curdate."' <= ba.date_end OR ba.date_end = '0000-00-00' OR ba.date_end is NULL)", 'ba.product_id as bind_product_id')
            ->join(DbTable::TABLE_PRODUCTS.' as p', "ba.product_id = p.id ", array('p.bank_id as product_bank_id','p.const AS product_const','p.unicode as product_unicode'))
            ->join(DbTable::TABLE_BANK.' as b', "p.bank_id = b.id ", array('b.logo as logo_bank','b.unicode as bank_unicode'))
            ->where("a.id=?", $agentId);
        $detailArr = $this->_db->fetchAll($select);
        
        return $detailArr;
        
    }
    
    public function checkAgentApproved($email = '',$mobile = ''){
        $email = isset($email )? $email : '';
        $mobile = isset($mobile )? $mobile : '';
        $approved  = $this->select()    
                ->from(DbTable::TABLE_AGENTS,array('*','concat(first_name," ",last_name) as name'));
        if($email != ''){
        $approved->where("email =?",$email);
        }
        if($mobile != ''){
        $approved->where("mobile1 =?",$mobile);
        }
        
        $approved->where("enroll_status ='".STATUS_APPROVED."'");
        $approved->where("status='".STATUS_UNBLOCKED."'");
        $check = $this->fetchRow($approved);
        
        return $check;
                
    }
    
     /* Get Super/Distributor Agent Code and Name  */
    public function getAgentCodeName($agent_user_type,$agent_id)
    {
        $agentArr = array();
        
        if($agent_user_type == SUPER_AGENT_DB_VALUE || $agent_user_type == AGENT_DB_VALUE || empty($agent_user_type))
        {
            $agentArr['sup_dist_code'] = '';
            $agentArr['sup_dist_name'] = '';
            $agentArr['dist_code'] = '';
            $agentArr['dist_name'] = '';
        }
        elseif($agent_user_type == DISTRIBUTOR_AGENT_DB_VALUE)
        {
            $super_agent_type = $this->getParentInfo($agent_id);

            if(!empty($super_agent_type))
            {
                $agentArr['sup_dist_code'] = $super_agent_type['agent_code'];
                $agentArr['sup_dist_name'] = $super_agent_type['agent_name'];
                $agentArr['dist_code'] = '';
                $agentArr['dist_name'] = '';
            }
            else
            {
                $agentArr['sup_dist_code'] = '';
                $agentArr['sup_dist_name'] = '';
                $agentArr['dist_code'] = '';
                $agentArr['dist_name'] = '';
            }
        }
        elseif($agent_user_type == SUB_AGENT_DB_VALUE)
        {
            $agent_type = $this->getParentInfo($agent_id);

            if(!empty($agent_type))
            {
                if($this->isSuperAgent($agent_type['id']))
                {
                    $agentArr['sup_dist_code'] = $agent_type['agent_code'];
                    $agentArr['sup_dist_name'] = $agent_type['agent_name'];
                    $agentArr['dist_code'] = '';
                    $agentArr['dist_name'] = '';
                }
                elseif($this->isDistributorAgent($agent_type['id']))
                {
                    $super_agent_type = $this->getParentInfo($agent_type['id']);

                    if(!empty($super_agent_type))
                    {
                        if($this->isSuperAgent($super_agent_type['id']))
                        {
                            $agentArr['sup_dist_code'] = $super_agent_type['agent_code'];
                            $agentArr['sup_dist_name'] = $super_agent_type['agent_name'];
                            $agentArr['dist_code'] = $agent_type['agent_code'];
                            $agentArr['dist_name'] = $agent_type['agent_name'];
                        }
                        else
                        {
                            $agentArr['sup_dist_code'] = '';
                            $agentArr['sup_dist_name'] = '';
                            $agentArr['dist_code'] = $agent_type['agent_code'];
                            $agentArr['dist_name'] = $agent_type['agent_name'];
                        }
                    }
                    else
                    {
                        $agentArr['sup_dist_code'] = '';
                        $agentArr['sup_dist_name'] = '';
                        $agentArr['dist_code'] = $agent_type['agent_code'];
                        $agentArr['dist_name'] = $agent_type['agent_name'];
                    }
                }
            }
        }
        
        return $agentArr;
    }
    
     public function getClosedLoopAgentDetailsById($id)
    {
            $select  = $this->_db->select()        
            ->from(DbTable::TABLE_CLOSED_LOOP_AGENTS.' as a',array('a.f_group as group'))
            ->where("a.f_agent_id=?", $id)
            ->where("a.f_status=?", STATUS_ACTIVE);
        return $this->_db->fetchRow($select);
    }
    
    public function getAgentDetailsById($id)
    {
         $select  = $this->select()        
            ->where("id=?", $id);
        $detailArr = $this->fetchRow($select);
        return $detailArr;
    }
    
    public function getAgentBindingByMobile($mobile){
        $curdate = date("Y-m-d");
        $select = $this->_db->select();
        $select->from(
                DbTable::TABLE_AGENTS . ' as a', array(
            'a.id', 'a.agent_code', 'concat(a.first_name," ",a.last_name) as name', 'a.enroll_status', 'a.status'
        ));
        $select->join(
                DbTable::TABLE_AGENT_DETAILS . " as ad", "a.id=ad.agent_id", array(
            'estab_name', 'estab_address1', 'estab_pincode', 'estab_city'
        ));
        $select->where("a.mobile1 = $mobile OR a.mobile2 = $mobile");
        $select->where("a.enroll_status =?", STATUS_APPROVED);  
        $row = $this->_db->fetchRow($select);
        if(!empty($row)) {
            return $row;
        } else {
            return FALSE;
        }
    }
    
    public function setAgentFundingIpay($data){
        $this->_db->beginTransaction();
        try {
            $this->_db->insert(DbTable::TABLE_AGENT_FUNDING_IPAY,$data); 
            $this->_db->commit();
            return TRUE;
        } catch (Exception $e) {
            $this->_db->rollBack();
            return FALSE;
        }
    }  
    public function getAgentBankName($agentId){

        $select  = $this->_db->select()        
            ->from(DbTable::TABLE_AGENT_DETAILS.' as a',array('a.bank_name'))
            ->where("a.agent_id=?", $agentId)
            ->where("a.status=?", STATUS_ACTIVE);
        return $this->_db->fetchRow($select);
        
    } 
    
    public function getChildrenInfo($id, $uType) 
    {
        $userType = $uType == SUPER_AGENT ? DISTRIBUTOR_AGENT : SUB_AGENT;
        $label = $this->getObjectRelationshipLabel($userType);
        $info = array();
        if(($uType == SUPER_AGENT) || ($uType == DISTRIBUTOR_AGENT)) {
            $object = new ObjectRelations();
            $info =  $object->getToObjectInfo($id, $label, TRUE);                
       }
       return $info;
    }
    
    public function getDistributorList($params) {
        $productId = isset($params['product_id']) ? $params['product_id'] : '';
        $uType = isset($params['user_type']) ? $params['user_type'] : '';
        
        $curdate = date("Y-m-d");   
        $label = $this->getObjectRelationshipLabel($uType);
        
        $select  = $this->_db->select()        
            ->from(DbTable::TABLE_AGENTS.' as a',array('a.id', 'concat(a.first_name," ", a.last_name) as agent_name', 'a.agent_code'))
            ->join(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION.' as ba', "ba.agent_id = a.id AND '".$curdate ."' >= ba.date_start AND ('".$curdate."' <= ba.date_end OR ba.date_end = '0000-00-00' OR ba.date_end is NULL)",array())
            ->join(DbTable::TABLE_BIND_OBJECT_RELATION.' as ob', "ob.from_object_id = a.id",array())
            ->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION_TYPES . " as obt", 'ob.object_relation_type_id = obt.id',array())
            ->where("ba.product_id=?", $productId)
            ->where('obt.label=?',$label)
            ->where('ob.status=?',STATUS_ACTIVE);
            
        $result = $this->_db->fetchAll($select);
        
        $selectArr = array(''=>'Select');
        foreach($result as $val) {
            $selectArr[$val['id']] = $val['agent_name'].' ('.$val['agent_code'].')';
        }
        return $selectArr;
    }
    
    public function mapAgent($params) {
        if (empty($params)) {
            throw new Exception('Data missing for Agent Remapping');
        }
        
        if ($params['user_type'] == SUPER_AGENT || $params['user_type'] == NORMAL_AGENT) {
            throw new Exception('Agent can not be Remapped');
        }
        
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $userType = isset($params['user_type']) ? $params['user_type'] : '';
        $agentId = isset($params['agent_id']) ? $params['agent_id'] : '';
        $super_agent = isset($params['super_agent']) ? $params['super_agent'] : '';
        $dist_agent = isset($params['distributor_agent']) ? $params['distributor_agent'] : '';
        $product_id = isset($params['product_id']) ? $params['product_id'] : '';
        
        $agentlimitModel = new Agentlimit();
        $objectRelModel = new ObjectRelations();
        $agentproductModel = new BindAgentProductCommission();
        
        try {
            $startDate = date("Y-m-d", strtotime("+1 days"));
            
            $this->_db->beginTransaction();
            switch($userType) {
                case SUB_AGENT:
                    $addAgentLimit = TRUE;
                  //  $chkLastDetailId = 'NULL';
                    // get distributor agent limit
                    $distLastdetails = $agentlimitModel->checkLastdetails($dist_agent);                         
                    if(!empty($distLastdetails)){
                        
                       $addAgentLimit = TRUE;
                    }else{
                       $addAgentLimit = FALSE;
                    }

                    //get distributor agent's product limit, commission & fee plan
                    $checkDistLastdetails  = $agentproductModel->checkLastdetails($dist_agent, $product_id);

                    // Update agent limit
                    $chkLastdetails = $agentlimitModel->checkLastdetails($agentId);                                
                    if(!empty($chkLastdetails)){
                        
                        $updateArr = array('id' => $chkLastdetails['id'],
                                            'agent_id' => $agentId,
                                            'by_ops_id' => $user->id,
                                            'date_start' => $startDate,
                                        );

                        $agentlimitModel->updateLimit($updateArr);
                        unset($updateArr['id']);
                        $chkLastDetailId = $chkLastdetails['id'];
                    }
                    if($addAgentLimit){
                    $insertArr = array('agent_id' => $agentId,
                                        'by_ops_id' => $user->id,
                                        'date_start' => $startDate,
                                        'agent_limit_id' => $distLastdetails['agent_limit_id']);    
                    $agentlimitModel->saveagentlimit($insertArr);
                    }

                    //Update agent's product limit, commission & fee plan
                    $checkLastdetails  = $agentproductModel->checkLastdetails($agentId, $product_id);
                    $agentupdateArr = array('agent_id' => $agentId,
                                        'product_id' => $product_id,
                                        'by_ops_id' => $user->id,
                                        'product_limit_id' => $checkDistLastdetails['product_limit_id'],
                                        'plan_commission_id' => $checkDistLastdetails['plan_commission_id'],
                                        'plan_fee_id' => $checkDistLastdetails['plan_fee_id'],
                                        'date_start' => $startDate);

                    $agentproductModel->updagentProduct($checkLastdetails['id'] , $startDate); 
                    $agentproductModel->agentProduct($agentupdateArr);

                    $relationshipLabel = $this->getObjectRelationshipLabel($userType);
                    $objectRelModel->deleteToObjectInfo($agentId, $relationshipLabel);

                    $insertArr = array('from_object_id' => $dist_agent,
                                        'to_object_id' => $agentId
                                        );
                    $objectRelModel->insertWithLabel($insertArr, $relationshipLabel);
                    break;
                case DISTRIBUTOR_AGENT:
                    // Super Agent limit details
                    $addAgentLimit = TRUE;
                  //  $chkLastDetailId = 'NULL';
                    $supLastdetails = $agentlimitModel->checkLastdetails($super_agent);
                    
                    if(!empty($supLastdetails)){
                        
                       $addAgentLimit = TRUE;
                    }else{
                       $addAgentLimit = FALSE;
                    }
                    // Super Agent product limit,commission,fee plan details
                    $bindcommissiondetails  = $agentproductModel->checkLastdetails($super_agent, $product_id);

                    // Update agent limit
                    $chkLastdetails = $agentlimitModel->checkLastdetails($agentId);                                
                    if(!empty($chkLastdetails)){
                   
                    $updateArr = array('id' => $chkLastdetails['id'],
                                        'agent_id' => $agentId,
                                        'by_ops_id' => $user->id,
                                        'date_start' => $startDate,
                                );
                                
                     $agentlimitModel->updateLimit($updateArr);
                   // unset($updateArr['id']);
                  //  Util::debug($updateArr);
                   // $chkLastDetailId = $chkLastdetails['id'];
                     }
                    if($addAgentLimit){
                    $insertArra = array('agent_id' => $agentId,
                                        'by_ops_id' => $user->id,
                                        'date_start' => $startDate,
                                        'agent_limit_id' => $supLastdetails['agent_limit_id']);                     
                    $agentlimitModel->saveagentlimit($insertArra);
                    }
                    //Update agent's product limit, commission & fee plan
                    $checkLastdetails  = $agentproductModel->checkLastdetails($agentId, $product_id);
                    $agentupdateArr = array('agent_id' => $agentId,
                                        'product_id' => $product_id,
                                        'by_ops_id' => $user->id,
                                        'product_limit_id' => $bindcommissiondetails['product_limit_id'],
                                        'plan_commission_id' => $bindcommissiondetails['plan_commission_id'],
                                        'plan_fee_id' => $bindcommissiondetails['plan_fee_id'],
                                        'date_start' => $startDate);

                    $agentproductModel->updagentProduct($checkLastdetails['id'] , $startDate); 
                    $agentproductModel->agentProduct($agentupdateArr);

                    $info = $this->getChildrenInfo($agentId, $userType);
                    if(!empty($info)) {
                        foreach($info as $val) {
                            $subagentLastlimitdetails = $agentlimitModel->checkLastdetails($val['to_object_id']);
                            if(!empty($subagentLastlimitdetails)){
                                
                            
                            $updateArr = array(
                                'id' => $subagentLastlimitdetails['id'],
                                'agent_id' => $val['to_object_id'],
                                'by_ops_id' => $user->id,
                                'date_start' => $startDate,
                            );

                            $agentlimitModel->updateLimit($updateArr);
                            unset($updateArr['id']);
                            }
                            
                            if($addAgentLimit){
                            $insertArra = array('agent_id' => $val['to_object_id'],
                                                'by_ops_id' => $user->id,
                                                'date_start' => $startDate,
                                                'agent_limit_id' => $supLastdetails['agent_limit_id']);                     
                            $agentlimitModel->saveagentlimit($insertArra);
                            }
                    
                          //  $agentlimitModel->saveagentlimit($updateArr);
                             
                            $lastbindcommissiondetails  = $agentproductModel->checkLastdetails($val['to_object_id'], $product_id);
                            $agentupdateArr = array('agent_id' => $val['to_object_id'],
                                                'product_id' => $product_id,
                                                'by_ops_id' => $user->id,
                                                'product_limit_id' => $bindcommissiondetails['product_limit_id'],
                                                'plan_commission_id' => $bindcommissiondetails['plan_commission_id'],
                                                'plan_fee_id' => $bindcommissiondetails['plan_fee_id'],
                                                'date_start' => $startDate);
                            
                            if(!empty($lastbindcommissiondetails)){
                            $agentproductModel->updagentProduct($lastbindcommissiondetails['id'] , $startDate); 
                            }
                            $agentproductModel->agentProduct($agentupdateArr);
                        }
                    }

                    $relationshipLabel = $this->getObjectRelationshipLabel($userType);
                    $objectRelModel->deleteToObjectInfo($agentId, $relationshipLabel);

                    $insertArr = array('from_object_id' => $super_agent,
                                        'to_object_id' => $agentId
                                        );
                    $objectRelModel->insertWithLabel($insertArr, $relationshipLabel);
                    break;
            }
            
            $this->_db->commit();
            return TRUE;
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_db->rollBack();            
            throw new Exception($e->getMessage());
        }
        return FALSE;
    }
    
    
    public function checkDuplicate($param){
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_AGENT_FUNDING_IPAY,array('COUNT(id) AS num'));
        foreach ($param as $key=>$val){
            $select->where("$key=?", $val);   
        }
        $num = $this->_db->fetchRow($select); 
        return $num['num'];        
    } 
    
}
