<?php
/**
 * Base User Model
 *
 *
 * @category App
 * @package App_Model
 * @copyright company
  */

class BaseUser extends App_Model
{
    
    
    
    public function updatenumLoginAttempts($username,$tablename)
    { 
    
        if($tablename == DbTable::TABLE_AGENTS || $tablename == DbTable::TABLE_BANK_USER  || $tablename == DbTable::TABLE_CORPORATE_USER) {
            //$updateString = "agent_code='$username'";
            $updateString = "id='$username'";
        } elseif($tablename == DbTable::TABLE_CUSTOMER_MASTER) {
             $updateString = "id='$username'";
        } else {
             $updateString = "username='$username'";
        }
        
        $config = App_DI_Container::get('ConfigObject');
        $numAllowed = $config->system->login->attempts->allowed;
       
        $dbNum = $this->getnumLoginAttempts($username,$tablename);
        
        $newNum = $dbNum['num_login_attempts'] + 1;
        if($tablename == DbTable::TABLE_CUSTOMER_MASTER) {
            if($newNum >= $numAllowed)
            {
                $customerModel = new CustomerMaster();
                $customerTable = $customerModel->getCustomerMasterTable($username);
                $this->_db->update($customerTable,array('status'=> STATUS_LOCKED),$updateString);
                $this->_db->update($tablename,array('num_login_attempts'=> $newNum),$updateString);                
            }
            else {
                $upp =  $this->_db->update($tablename,array('num_login_attempts'=> $newNum),$updateString);
            }            
        } else {
        
        if($newNum >= $numAllowed)
        {
            $upp =  $this->_db->update($tablename,array('num_login_attempts'=> $newNum, 'status'=> STATUS_LOCKED),$updateString);
        }
        else {
            $upp =  $this->_db->update($tablename,array('num_login_attempts'=> $newNum),$updateString);
        }
        }
        
        return $newNum;
        
    }
    
    
    /*
     * on successful login, update num login attempts to 0 in t_agents & t_operation_users
     */
    public function removenumLoginAttempts($username,$tablename){
        if($tablename == DbTable::TABLE_AGENTS || $tablename == DbTable::TABLE_CORPORATE_USER ){
       // $updateString = "agent_code='$username'";
        $updateString = "id='$username'";
        $status = STATUS_UNBLOCKED;
         }
       else if($tablename == DbTable::TABLE_BANK_USER ){
       // $updateString = "agent_code='$username'";
        $updateString = "id='$username'";
        $status = STATUS_ACTIVE;
         }
         else
        {
            $updateString = "username='$username'";
            $status = STATUS_ACTIVE; 
        }
        
        
      $this->_db->update($tablename,array('num_login_attempts'=> '0','status'=>$status),$updateString);
             
    }
    public function getnumLoginAttempts($username,$tablename)
    {
        if($tablename == DbTable::TABLE_AGENTS || $tablename == DbTable::TABLE_CUSTOMER_MASTER || $tablename == DbTable::TABLE_BANK_USER || $tablename == DbTable::TABLE_CORPORATE_USER){
            //$whereString = "agent_code='$username'";
            $whereString = "id='$username'";
        }
        else{
             $whereString = "username='$username'";
        }   
       $select = $this->_db->select()
               ->from($tablename,array('num_login_attempts'))
               ->where($whereString);
        //echo $select->__toString();

       $num =  $this->_db->fetchRow($select);
       return $num;
    }
    
    
    public function insertLoginLog($data){
        $data['ip'] = $this->formatIpAddress(Util::getIP());
        $insert = $this->_db->insert(DbTable::TABLE_LOG_LOGIN,$data);
        
        
        return TRUE;
    }
    
    
    public function updateStatusLog($data){
        
        $update = $this->_db->insert(DbTable::TABLE_CHANGE_STATUS_LOG,$data);
        return true;
        
    }    
   public function getUserIdByUsername($username,$tablename){
        if($tablename == DbTable::TABLE_AGENTS || $tablename == DbTable::TABLE_CORPORATE_USER){
        //$whereString = "agent_code='$username'";
        $whereString = "email='$username' AND enroll_status ='".STATUS_APPROVED."' AND status = '".STATUS_UNBLOCKED."'";
       
        // Field "bcecontract" needed for checking terms & condition selected or Not
       if($tablename == DbTable::TABLE_AGENTS)
        {
            $arr = array('id','status','enroll_status','email_verification_status','bcecontract');  
        }else{
            $arr = array('id','status','enroll_status','email_verification_status');
        }

        }elseif($tablename == DbTable::TABLE_CUSTOMER_MASTER){
        //$whereString = "agent_code='$username'";
        $whereString = "shmart_crn='$username'";
        $arr = array('id','is_portal_access','shmart_crn');
        } else {
             $whereString = "username='$username'";
            $arr =  array('id','status');
        }
        
        $select = $this->_db->select()
               ->from($tablename,$arr)
               ->where($whereString);
//        echo $select; exit;        
        $rs =  $this->_db->fetchRow($select);
       return $rs;
       
   } 
    
    
    
    public function getAuthcodeFromDb($userId, $table = '') {
        if ($table == DbTable::TABLE_CUSTOMER_MASTER) {
            $select = $this->_db->select()
                    ->from(DbTable::TABLE_CUSTOMER_MASTER, array('auth_code'))
                    //->where("status != '" . STATUS_LOCKED . "'")
                    ->where("id='$userId'");
            $authCode = $this->_db->fetchRow($select);
            return $authCode['auth_code'];
        } elseif ($table == DbTable::TABLE_BANK_USER) {
            $select = $this->_db->select()
                    ->from(DbTable::TABLE_BANK_USER, array('auth_code'))
                    ->where("id='$userId'");
            $authCode = $this->_db->fetchRow($select);
            return $authCode['auth_code'];
        } elseif ($table == DbTable::TABLE_CORPORATE_USER) {
            $select = $this->_db->select()
                    ->from(DbTable::TABLE_CORPORATE_USER, array('auth_code'))
                    ->where("id='$userId'");
            $authCode = $this->_db->fetchRow($select);
            return $authCode['auth_code'];
        } else {//Default is Operation Portal
            $select = $this->_db->select()
                    ->from(DbTable::TABLE_OPERATION_USERS, array('auth_code'))
                    ->where("status != '" . STATUS_LOCKED . "'")
                    ->where("id='$userId'");
            $authCode = $this->_db->fetchRow($select);
            return $authCode['auth_code'];
        }
    }

    public function getAgentAuthcodeFromDb($userId){
         $select = $this->_db->select()
               ->from(DbTable::TABLE_AGENTS,array('auth_code'))
               ->where("status != '".STATUS_LOCKED."'")
               ->where("id='$userId'");
       $authCode =  $this->_db->fetchRow($select);
       return $authCode['auth_code'];
    }
    
    public function findDetails($username,$tablename){
        if($tablename == DbTable::TABLE_AGENTS)
        $whereString = "agent_code='$username'";
        else
             $whereString = "username='$username'";
         $select = $this->_db->select()
               ->from($tablename)
               ->where($whereString);
        
       $authCode =  $this->_db->fetchRow($select);
      
       return $authCode;
            
        
    }
    
    /**
     * Logs an user in the application based on his
     * username and email
     * 
     * @param string $username
     * @param string $password
     * @param boolean $remember
     * @access public
     * @return void
     */
    public function login($username, $password, $rememberMe = FALSE){
       
        //print $username. ' : '.  $password.'<br />';exit('HERE');
        $usernameColumnName = 'username';
        if(CURRENT_MODULE == MODULE_AGENT || CURRENT_MODULE == MODULE_CORPORATE) {
            //$usernameColumnName = 'agent_code';
            $usernameColumnName = 'email';
        } elseif(CURRENT_MODULE == MODULE_CUSTOMER) {
            $usernameColumnName = 'shmart_crn';
        }
        // adapter cfg
        $adapter = new Zend_Auth_Adapter_DbTable($this->_db);
        $adapter->setTableName($this->_name);
        $adapter->setIdentityColumn($usernameColumnName);
        $adapter->setCredentialColumn('password');
        $adapter->setAmbiguityIdentity(TRUE);
        // checking credentials
        $adapter->setIdentity($username);
        $adapter->setCredential(BaseUser::hashPassword($password));
        try{
            $result = $adapter->authenticate();

        }catch(Zend_Auth_Adapter_Exception $e){
            App_Logger::log(sprintf("Exception catched while login: %s", $e->getMessage()),Zend_Log::ERR);
            
            return FALSE;
        }
        if($result->isValid()){
            // get the user row
            $loggedUser = $adapter->getResultRowObject(NULL, 'password');
            
            // clear the existing data
            $auth = Zend_Auth::getInstance();
            $auth->clearIdentity();
            $alert = new Alerts();
            if(!empty($loggedUser->id)){
                switch(CURRENT_MODULE){
                    case MODULE_AGENT:
                        $userModel = new AgentUser();
                         $user = $userModel->findById($loggedUser->id);
                        $session = new stdClass();

                        #####Agent not accepted terms and condition then check for same.
                         $session->showBceContract = (intval($user['bcecontract'])==0)? 1:'';

                        //$sessionController = new Zend_Session_Namespace('App.Operation.Controller');
                        foreach(get_object_vars($loggedUser) as $k => $v){
                            $session->{$k} = $v;
                            //$sessionController->{$k} = $v;
                        }
                        if(!isset($session->group)) {
                            $session->group = new stdClass();
                        }
                        
                        //$session->group->name = "administrators";
                        //echo $userModel->isSuperAgent($user['id']).'**';
                        //echo '<pre>';print_r($user);exit;
                        if($userModel->isSuperAgent($user['id']) || $userModel->isDistributorAgent($user['id'])) {
                            $session->group->name = "administrators";
                        } else {
                            $session->group->name = $user->email;
                        }
                        
                        $session->authenticated = false;
                        //$session->first_name = 'Komal';
                        //$session->last_name = 'Puri';
                        $agentProduct = $userModel->getAgentBinding($user->id);
                        if(!empty($agentProduct) && !empty($agentProduct['logo_bank'])){
                            $logoBank = $agentProduct['logo_bank'];
                        }
                        else {
                            $logoBank = '';
                        }
                        $session->logo_bank = $logoBank;
                        $session->static_code = 'no'; //$agentProduct['static_otp'];
                        $session->bank_unicode = $agentProduct['bank_unicode'];
                        $session->product_id = $agentProduct['product_id'];
			
			$agentAllProducts = $userModel->getAgentBindingProducts($user->id);
                        foreach ($agentAllProducts as $key => $product) {
                            $products[$key]['product_id'] =  $product['bind_product_id'];
                            $products[$key]['product_unicode'] =  $product['product_unicode'];
                            $products[$key]['product_const'] =  $product['product_const'];
                        }
			/*
			if(!empty($agentProduct['product_const']) && $agentProduct['product_const'] == PRODUCT_CONST_RAT_REMIT){
			    // ==> Also set SMP Product
			    $productModel = new Products();
			    $pinfo = $productModel->getProductDetailbyConst(PRODUCT_CONST_RAT_SMP);
			    if(!empty($pinfo)){
				$pValue['product_id'] =  $pinfo['id'];
				$pValue['product_unicode'] =  $pinfo['unicode'];
				$pValue['product_const'] =  $pinfo['const'];
				array_push($products,$pValue);
			    }
			} */
                        $session->product_ids = $products;
                        $config = App_DI_Container::get('ConfigObject');
                       
                    if ($config->agent->login->static->otp) {
                 
                            $currDate = date('Y-m-d');
                            $lastOTPUpdateDate = $session->last_auth_code_update;
                            // If last OTP update is NULL
                            if ($lastOTPUpdateDate == ''){
                              // generate Authcode  
                                $authCode = Alerts::generateAuthCode();
                                $session->auth_code = $authCode;
                                $update_authCode = $this->_db->update(DbTable::TABLE_AGENTS,array('auth_code'=> $authCode,'last_auth_code_update'=> new Zend_Db_Expr('NOW()')),"id='$user->id'");
                                $m = new App\Messaging\MVC\Axis\Agent();
                                $m->authCode($session);
                            }else{
                            $dateArr = explode(" ",$lastOTPUpdateDate);
                            $dateDB = $dateArr[0];
                           
                          
                            if($currDate != $dateDB){
                                // generate Authcode
                                $authCode = Alerts::generateAuthCode();
                                $session->auth_code = $authCode;
                                $update_authCode = $this->_db->update(DbTable::TABLE_AGENTS,array('auth_code'=> $authCode,'last_auth_code_update'=> new Zend_Db_Expr('NOW()')),"id='$user->id'");
                                $m = new App\Messaging\MVC\Axis\Agent();
                                $m->authCode($session);
                            }
                            else{
                                // fetch auth code from DB
                                 $authCode = $session->auth_code;
                                 $session->auth_code = $authCode;
                                 
                            }
                          }
                           
                        }
                        else{
                            // generate Authcode for every login
                                $authCode = Alerts::generateAuthCode();
                                $session->auth_code = $authCode;
                                $update_authCode = $this->_db->update(DbTable::TABLE_AGENTS,array('auth_code'=> $authCode,'last_auth_code_update'=> new Zend_Db_Expr('NOW()')),"id='$user->id'");
                                $m = new App\Messaging\MVC\Axis\Agent();
                                $m->authCode($session);
                        }
                        
                        $session->username = $session->email;
                        $session->agentCode = $session->agent_code;
                        $session->photo = $this->getAgentPhoto($user->id);
                        $session->user_type = $userModel->getAgentType($user->id);
                        $session->passwordUpdateRequired = $this->validateLastPasswordUpdate($session, MODULE_OPERATION);                        
                        
                        break;
                    case MODULE_BANK:
                        $userModel = new BankUser();
                        $user = $userModel->findById($loggedUser->id);
                        $bank = new Banks();
                        
                        $session = new stdClass();
                        //$sessionController = new Zend_Session_Namespace('App.Operation.Controller');
                        foreach(get_object_vars($loggedUser) as $k => $v){
                            $session->{$k} = $v;
                        }
                      	$userGroup = new BankUserGroup();
                      	$groupData = $userGroup->findByUserId($loggedUser->id,true);
                      	$session->group->name = '';
                        if(isset($groupData->name) && !empty($groupData->name))
                        	$session->group->name = $groupData->name;
                        
                        //$session->group->name = TYPE_ADMIN;
                        $session->authenticated = false;
                        $bankDetail = $bank->findById($user->bank_id);
                        $logoBank = $bankDetail->logo;
                        //}
                        $session->logo_bank = $logoBank;
                        $session->unicode = $bankDetail->unicode;
                        ///$session->bank_unicode = $agentProduct['bank_unicode'];
                        $session->auth_code = Alerts::generateAuthCode();
                        $session->username = $session->username;
                         
                        $update_authCode = $this->_db->update(DbTable::TABLE_BANK_USER,array('auth_code'=>$session->auth_code),"id='$user->id'");
                        $session->passwordUpdateRequired = $this->validateLastPasswordUpdate($session, MODULE_BANK);                        
                        $m = new App\Messaging\MVC\Axis\Bank();
                        $m->authCode($session);
                        break;
                    case MODULE_CORPORATE:
                        //Util::debug($loggedUser);
                        $userModel = new CorporateUser();
                        $user = $userModel->findById($loggedUser->id);
                        $session = new stdClass();
                        foreach(get_object_vars($loggedUser) as $k => $v){
                            $session->{$k} = $v;
                        }
                        $session->group->name = $user->email;
                        
                        $session->authenticated = false;
                        $userDetail = $userModel->findById($user->id);
                        
                        $corporateProduct = $userModel->getCorporateBinding($user->id);
                        if(!empty($corporateProduct) && !empty($corporateProduct['bank_unicode'])){
                            $logoBank = Util::getCorporateBankLogo($corporateProduct['bank_unicode']);
                        }
                        else {
                            $logoBank = '';
                        }
                        $session->logo_bank = $logoBank;
                        $session->bank_unicode = $corporateProduct['bank_unicode'];
                        $session->auth_code = Alerts::generateAuthCode();
                        $session->email = $session->email;
                        $session->user_type = $userModel->getCorporateType($user->id);
                        $session->photo = $userModel->getCorporatePhoto($user->id);
                        $update_authCode = $this->_db->update(DbTable::TABLE_CORPORATE_USER,array('auth_code'=>$session->auth_code),"id='$user->id'");
                        $session->passwordUpdateRequired = $this->validateLastPasswordUpdate($session, MODULE_CORPORATE);                        
                        $m = new App\Messaging\MVC\Axis\Corporate();
                        $m->authCode($session);
                        break;
                    
                    case MODULE_CUSTOMER:
                        $userModel = new CustomerMaster();
                        $user = $userModel->findById($loggedUser->id);
                       

                        $userInfo = $userModel->getUserInfo($loggedUser->id);
                        //print '<pre>';print_r($userInfo);exit;
                        $session = new stdClass();
                        //$sessionController = new Zend_Session_Namespace('App.Operation.Controller');
                        foreach(get_object_vars($loggedUser) as $k => $v){
                            $session->{$k} = $v;
                            //$sessionController->{$k} = $v;
                        }
                        
                        $session->group = new stdClass();
                        $session->group->name = "administrators";
                        //$session->group->name = $user->shmart_crn;
                        $session->authenticated = false;
                        $session->first_name = $userInfo->first_name;
                        $session->last_name = $userInfo->last_name;
                        $session->mobile = $userInfo->mobile;
                        $session->mobile1 = $userInfo->mobile;
                        //echo '<pre>';print_r($agentProduct);
                        //echo '<pre>';print_r($session);exit('here');      
                        //Get Customer Product Details
                        $agentProduct = $userModel->getBankInfo($user->id);
                        if(!empty($agentProduct) && !empty($agentProduct['logo_bank'])){
                            $logoBank = $agentProduct['logo_bank'];
                        }
                        else {
                            $logoBank = '';
                        }
//                        echo '<pre>';print_r($agentProduct);
//                        echo '<pre>';print_r($session);
//                        exit;                        
                        $session->logo_bank = $logoBank;
                        $session->auth_code = Alerts::generateAuthCode();
                        $session->username = $userInfo->shmart_crn;
                        $session->status = $userInfo->status;
                        $session->email = $userInfo->email;

                         
                        //$session->photo = $this->getAgentPhoto($user->id);
                        $update_authCode = $this->_db->update(DbTable::TABLE_CUSTOMER_MASTER,array('auth_code'=>$session->auth_code),"id='$user->id'");
                        $session->passwordUpdateRequired = $this->validateLastPasswordUpdate($session, MODULE_CUSTOMER);    
                        //print __CLASS__ . ' : '. __FUNCTION__ . ' : ' . __LINE__.'<br />';exit;
                        //$alert->sendAuthCode($session,CURRENT_MODULE);
                        //echo "<pre>";print_r($session);
                        //echo "<pre>";print_r($userInfo);exit;
//                        print '<pre>';print_r($session);
//                        print '<pre>';print_r($user->toArray());
//                        exit('HERE');  
                     
                        $m = new App\Messaging\MVC\Axis\Customer();
                        //$m = new App\Messaging\MVC\Axis\Customer();
                        $m->authCode($session);
                        //print '<pre>';print_r($session);
                        //print '<pre>';print_r($user);
                        //exit('HERE');          
                        
                        break;
                    case MODULE_OPERATION:

                        $userModel = new OperationUser();
                        $user = $userModel->findById($loggedUser->id);
                        $user->groups = $user->findManyToManyRowset('Group', 'OperationUserGroup');
                        
                        $user->group = isset($user->groups[0]) ? $user->groups[0] : array();
                        
                        $session = new stdClass();
                        
                        foreach(get_object_vars($loggedUser) as $k => $v){
                            $session->{$k} = $v;
                        }
                        if(!isset($session->group)) {
                            $session->group = new stdClass();
                        }
                        $session->group->name = $user->group->name;
                        $session->group->group_id = $user->group->id;
                        //echo "<pre>";print_r($session);exit;
                        $session->authenticated = false;
                        $session->auth_code = Alerts::generateAuthCode();
                        $update_authCode = $this->_db->update(DbTable::TABLE_OPERATION_USERS,array('auth_code'=>$session->auth_code),"id='$user->id'");
                        //$alert->sendAuthCode($session,CURRENT_MODULE);
                        $session->passwordUpdateRequired = $this->validateLastPasswordUpdate($session, MODULE_OPERATION);
                        //echo '<pre>';print_r($session);exit;
                        $m = new App\Messaging\MVC\Axis\Operation();
                        
                        $flg = $m->authCode($session);
                        
//                        if (!$flg) {
//                            //print 'Getting Error in Sending Message : ' . $m->getError();
//                        }
                        break;
                }
                
                $auth->getStorage()->write($session);
            }

            if(isset($this->_db)) {
//                $this->update(
//                    array(
//                        'last_login' => new Zend_Db_Expr('NOW()')
//                    ), 
//                    $this->_db->quoteInto('id = ?', $user->id)
//                );
            }
            
//            if($rememberMe){
//                Zend_Session::rememberMe(App_DI_Container::get('ConfigObject')->session->remember_me->lifetime);
//            }else{
//                Zend_Session::forgetMe();
//            }
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    
    
    private function validateLastPasswordUpdate($user, $module)
    {
         $changePasswordRequired = FALSE;
        //if($module == MODULE_OPERATION) {
            /**** checking last update date of password ****/
           $lastPasswordUpdate = $user->last_password_update;
           if($lastPasswordUpdate == NULL || $lastPasswordUpdate == ''){
               $changePasswordRequired = TRUE;  
           }
           else //if($lastPasswordUpdate!='')
           {
               $lastPasswordUpdateArr = explode(" ", $lastPasswordUpdate);
               $lastPasswordUpdateDate = $lastPasswordUpdateArr[0];
               $daysToAdd = App_DI_Container::get('ConfigObject')->agent->password->expirydays;
               $passwordExpiryDate = strtotime(date("Y-m-d", strtotime($lastPasswordUpdateDate)) . " +$daysToAdd day");
               $passwordExpiryDate = date ( 'Y-m-d' , $passwordExpiryDate );
               $currentDate = date('Y-m-d');
               
               if($currentDate > $passwordExpiryDate){
                  $changePasswordRequired = TRUE;     
               }
           }
//        } elseif($module == MODULE_AGENT) {
//            
//        }
        return $changePasswordRequired;
    }
    
    /**
     * Hashes a password using the salt in the app.ini
     *
     * @param string $password 
     * @static
     * @access public
     * @return string
     */
    public static function hashPassword($password,$moduleFor =''){
        $config = App_DI_Container::get('ConfigObject');
        if($moduleFor == '') {
            $module = strtolower(CURRENT_MODULE);
        } else {
            $module = strtolower($moduleFor);
        }
        return sha1($config->{$module}->security->passwordsalt . $password);
    }
    
    /**
     * Return the App_Table_User instance based on the logged info
     *
     * @return void
     */
    public static function getUserInstance(){
        $userModel = new User();
        
        $session = BaseUser::getSession();
        
        return isset($session->id)? $userModel->findById($session->id) : NULL;
    }
    
    /**
     * Check if the current user is logged
     *
     * @return void
     */
    public static function isLogged(){
        $user = BaseUser::getSession();
        
        return isset($user->id);
    }
    
    /**
     * Reload the data of the user in the session
     *
     * @return void
     */
    public static function reloadSession(){
        $auth = Zend_Auth::getInstance();
        
        switch(CURRENT_MODULE){
            case 'agent':
                $userModel = new User();
                $user = $userModel->findById(self::getSession()->id);
                $user->get('group');
                break;
            case 'operation':
                $userModel = new OperationUser();
                $user = $userModel->findById(self::getSession()->id);
                $user->groups = $user->findManyToManyRowset('Group', 'OperationUserGroup');
                $user->group = $user->groups[0];
                break;
        }
        
        $session = new stdClass();
        foreach($user as $k => $v){
            $session->{$k} = $v;
        }
        $session->group->name = $user->get('group')->name;
        
        $auth->getStorage()->write($session);
    }
    
    /**
     * Return the current user auth instance
     *
     * @return stdClass
     */
    public static function getSession(){
        $auth = Zend_Auth::getInstance();
        
        // load the identity
        if(!$auth->hasIdentity()){
            $user = new stdClass();
            @$user->group->name = 'guests';
            $auth->getStorage()->write($user);
        }
        
        $user = $auth->getIdentity();
        return $user;
    }
    
     /**
     * Reload the data of the user in the session
     *
     * @return void
     */
    public static function generateUserCode($id,$agentCode = null){
        
                //Get user info
                $userModel = new AgentUser();
                $select = $userModel->select()
                    ->where('id = ?', $id);
                $userInfo = $userModel->fetchRow($select);
                
                //Empty Validation & Checks
                if(empty($userInfo)) {
                    throw new Exception('Base User: Invalid agent id');
                }
                
                /*if($userInfo->agent_code == '') {
                    throw new Exception('Base User: Agent code already exsits');
                }*/
                
                if($agentCode == null) {
                     $code = Util::encodeToNumericCode($userInfo->email);
                } else {
                    $code = Util::encodeToNumericCode($userInfo->email, 7, true );
                }

                $encodeId = Util::getLenghtId($userInfo->id);
                $agentCode = Util::getAgentCode($code, $encodeId);
                //Check Duplicate
                $sel = $userModel->select()
                    ->where('agent_code = ?', $agentCode);
                $agentInfo = $userModel->fetchRow($sel);
                //echo $select->__toString();
                if(!empty($agentInfo)) {
                    return self::generateUserCode($id, $agentCode);
                }
                $data = array(
                    'agent_code' => $agentCode
                );
                $userModel->update($data, 'id='.$id);
                return $agentCode;
                //exit;
        
    }
    
    /**
     * Check Login username & Password for correct/incorrect entry
     * username and email
     * 
     * @param string $username
     * @param string $password
     * @access public
     * @return void
     * used in login step 1
     */
    public function chkLoginCredentials($username, $password){
        $usernameColumnName = 'username';
        if(CURRENT_MODULE == MODULE_AGENT || CURRENT_MODULE == MODULE_CORPORATE) {
            //$usernameColumnName = 'agent_code';
            $usernameColumnName = 'email';
        } elseif (CURRENT_MODULE == MODULE_CUSTOMER) {
            $usernameColumnName = 'shmart_crn';
        }
       
        // adapter cfg
        $adapter = new Zend_Auth_Adapter_DbTable($this->_db);
        $adapter->setTableName($this->_name);
        $adapter->setIdentityColumn($usernameColumnName);
        $adapter->setCredentialColumn('password');
        if(CURRENT_MODULE == MODULE_AGENT) {
        $adapter->setStatusCondition("enroll_status = '".STATUS_APPROVED."'");
        }
        $adapter->setAmbiguityIdentity(TRUE);
        
        // checking credentials
        $adapter->setIdentity($username);
        $adapter->setCredential(BaseUser::hashPassword($password));
        try{
            $result = $adapter->authenticate();
        }catch(Zend_Auth_Adapter_Exception $e){
            App_Logger::log(sprintf("Exception catched while login: %s", $e->getMessage()),Zend_Log::ERR);
            
            return FALSE;
        }
        if($result->isValid()){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    #### Function defined for updating the E-contract details if accepted by Agent. 
    public function updateAgentsBcEcontract($id){
        $update_bcEcontract = $this->_db->update(DbTable::TABLE_AGENTS,array('bcecontract'=>1,'bcecontract_accepted'=> new Zend_Db_Expr('NOW()')),"id='$id'");
        return true;
        }
    
}