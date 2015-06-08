<?php
/**
 * Allows user to manage their profile data
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class ProfileController extends App_Agent_Controller
{
    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init(){
         $this->session = new Zend_Session_Namespace("App.Agent.Controller");
        // init the parent
        parent::init();
        
    }
    
    
    /**
     * Allows users to see their dashboards
     *
     * @access public
     * @return void
     */
    public function indexAction(){
        
        //echo __CLASS__ . ":" . __FUNCTION__ . ":" . __LINE__;exit;
        $user = Zend_Auth::getInstance()->getIdentity();
        if(!isset($user->id)) {
            $this->_redirect($this->formatURL('/profile/login'));
            exit;
        }
        $this->title = 'Dashboard';
        try {
            $dashboard = new Dashboard();
            $agentModel = new AgentUser();
            $agentId = isset($user->id) ? $user->id : 0;
            $count = $agentModel->getRegisteredCardholderCount($agentId);
            $this->view->cardholderRegistedCount = $count;
        
            //getting concerned stats of agent for dashboard 
            $agentStats = $dashboard->agentDashboardStats(); 
            $this->view->agentStats = $agentStats;
            $user = Zend_Auth::getInstance()->getIdentity();        
        
            $agentDetailModel = new Agents();
            $agentProduct = $agentModel->getAgentBindingProducts($user->id);
            $product = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NDSC);
            $productUnicode = $product->product->unicode;
            $boiCustModel = new Corp_Boi_Customers();
            $duration = 'month';
            $dates = Util::getDurationDates($duration);
            $arrProductUnicode = array();
            if(count($agentProduct) > 0){
                foreach($agentProduct as $aprod) {
                    $arrProductUnicode[] = $aprod['product_unicode'];
                }
            }
            $this->view->agentProductUnicode = $arrProductUnicode;
            if(in_array($productUnicode, $arrProductUnicode)) {
                if(isset($user->id) && $agentModel->isSuperAgent($user->id)) {
                    //$this->_helper->layout()->setLayout('withoutlogin');
                    echo $this->view->render('profile/super-agent-dashboard.phtml');
                    $this->_helper->viewRenderer->setNoRender(true);
                }
                if(isset($user->id) && $agentModel->isSubAgent($user->id)) {
                    $this->_helper->viewRenderer->setNoRender(true);                                
                    $agentInfo = $agentDetailModel->findById($user->id);
                
                    $parantInfo = $agentModel->getParentInfo($user->id);                    
        
                    if(count($parantInfo)){
                        $parant = $agentModel->findById($parantInfo['id']);                    
                        $agentInfo['training_partner_name'] = $parant->institution_name;
                    }
                
                    $this->view->agentInfo = $agentInfo;
               
                
                    $data = array('agent_id' => $user->id,'from' => $dates['from'],'to' => $dates['to']);
                    //$custInfo = $boiCustModel->applicationApprovedCount($data);
                    $custInfo = $boiCustModel->applicationApprovedTotalCount($data);
                
                    $this->view->custInfo = $custInfo['count'];
                    // get Distributor's Institution Name
                    $distId = $agentModel->getParentInfo($user->id);
                    $distDetails = $agentDetailModel->findById($distId['id']);
                    $this->view->distInstitute = $distDetails;
                    echo $this->view->render('dashboard/boi/agent-nsdc-dashboard.phtml');
                } elseif(isset($user->id) && $agentModel->isDistributorAgent($user->id)) {
                    $this->_helper->viewRenderer->setNoRender(true);                
                    $agentInfo = $agentDetailModel->findById($user->id);
                    $this->view->agentInfo = $agentInfo;
                    $partnerArray = $agentDetailModel->getBCListUnderDist(array('agent_id' => $user->id,'status' => STATUS_UNBLOCKED,'enroll_status' => STATUS_APPROVED));
                    $data = array('agent_id' => $partnerArray,'from' => $dates['from'],'to' => $dates['to']);
                    //$custInfo = $boiCustModel->applicationApprovedCount($data);
                    $custInfo = $boiCustModel->applicationApprovedTotalCount($data);
                    $this->view->custInfo = $custInfo['count'];
                    $trainingcenter = $agentDetailModel->countBCListUnderDistributor(array('agent_id' => $user->id,'status' => STATUS_UNBLOCKED,'enroll_status' => STATUS_APPROVED));
                    $this->view->trainingCenterCount = $trainingcenter->count;
                    echo $this->view->render('dashboard/boi/distributor-nsdc-dashboard.phtml'); 
                } 
            } 
        //print $count.'**';
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            print $e->getMessage();
        }
    }
    
   
     public function resendAuthcodeAction(){
     
        // use the login layout
        $this->_helper->layout()->setLayout('login');
        $resend_verificationmsg = $this->_getParam('verify');
        //$this->session->resent = FALSE;
        //$this->session->notresent = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $userModel = new AgentUser();
        $dataArr = $userModel->findDetails($user->agent_code,DbTable::TABLE_AGENTS);
        $dataArr['username'] =  $user->agent_code;

        ### defined for checking Service Aggreement is accepted or not
        $dataArr['showBceContract'] = (intval($dataArr['bcecontract'])==0)? 1:'';

        if($resend_verificationmsg)
        {
           $dataArr['mobile1'] =  $this->session->new_mobile1;
           $dataArr['auth_code'] =  $this->session->ver_code;
        }
       
        try {
            if($resend_verificationmsg)
            {
                    $alerts = new Alerts();
                    $info = array ('v_code'=>$this->session->ver_code,'mobile1'=>$this->session->new_mobile1);
                    $sendConf = $alerts->sendVerificationCode($info, 'operation');
                $this->_helper->FlashMessenger(
                       array(
                           'msg-success' => 'Verification code resent successfully',
                       )
                 );
            }
            else
            {
                 $m = new App\Messaging\MVC\Axis\Agent();
                $flg = $m->authCode($dataArr);
        
                
                
                $this->_helper->FlashMessenger(
                       array(
                           'msg-success' => 'Authcode resent successfully',
                       )
                 );

            }
        } catch(Exception $e){
                     App_Logger::log($e->getMessage(), Zend_Log::ERR);
                     $Msg = $e->getMessage();
                      $this->_helper->FlashMessenger(
                                 array(
                                      'msg-error' => $Msg,
                                 )
                             );
                }
               
       
                if($resend_verificationmsg)
                    $this->_redirect($this->formatURL('/profile/mobileverification/'));
                else
                    $this->_redirect($this->formatURL('/profile/authcode/resendfrom/22'));
    }
    /**
     * Allows users to log into the application
     *
     * @access public
     * @return void
     */
    public function loginAction(){
	 //echo BaseUser::hashPassword('Test123456'); exit;
        $sessionId    = Zend_Session::getId();
        $user = Zend_Auth::getInstance()->getIdentity();
       
        if($this->_getParam('update') == 'mobile'){
           $this->view->msgType = 'mobilenumberupdated';
        }
        
        
        //If already logged in
       if(isset($user->id)) {
           $this->_redirect($this->formatURL("/profile/index/"));
           exit;
       }
       if($this->_getParam('sess') == 'err'){
           $this->view->errType = 'err_sess';
       }
        $sessionModel = new Session();
        $this->title = 'Login';
        // use the login layout
        $this->_helper->layout()->setLayout('login');
        
        $form = new LoginForm();
        
       
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){ 
                $userModel = new AgentUser(); 
                
                $loginClientKey = App_DI_Container::get('ConfigObject')->{agent}->security->loginClientKey ;
                $password = Util::crypt_fn($loginClientKey,$form->getValue('password'),'descrypt'); 
                $userExists = $userModel->chkLoginCredentials($form->getValue('username'), $password);
                $id = $userModel->getUserIdByUsername($form->getValue('username'),DbTable::TABLE_AGENTS);
               
                if($userExists) {
                $rowStatus = $userModel->getAgentStatus($form->getValue('username'));
                
                
                if(!empty($rowStatus)){ 
                    //email_verification_status, email_verification_id,
                    if($rowStatus['email_verification_status'] == STATUS_VERIFIED && $rowStatus['email_verification_id'] > 0) {
                        
                        // enroll_status
                        if($rowStatus['enroll_status'] == STATUS_APPROVED) {
                            // status check
                            
                            if($rowStatus['status'] == STATUS_UNBLOCKED) {
                                if(App_DI_Container::get('ConfigObject')->session->ssn_implementation) {
                                    if(!$sessionModel->loginSession(array('user_id' => $rowStatus['id']))){
                                        $this->_redirect($this->formatURL('/profile/login?sess=err'));
                                    }
                                }
                                
                                $res = $userModel->login($form->getValue('username'), $password);
                                if($res == 'correct_pass'){
                                 
                                    $session = new Zend_Session_Namespace('App.Operation.Controller');
									
									if(strlen($rowStatus['bcagent']) > 0) {

										//$rbiResponse = $this->loginToRbi(array('username' => $form->getValue('username'),
										//'password' => $form->getValue('password'),'bcagent' => $rowStatus['bcagent']));
										
										$rbiResponse = $this->loginToRbi(array('username' => RBL_CHANNEL_PARTNER_LOGIN_USERNAME,
										//'password' => '91488176b65e4328fd7fce3107dd69feba308278',
										'password'=> RBL_CHANNEL_PARTNER_LOGIN_PASSWORD,//'6a48fd501acd7d8e095ec0ea182ea96d37b7c1e6',
										'bcagent' => $rowStatus['bcagent']));
										//'bcagent' => 'TRA1000189'));
																				
										if(isset($rbiResponse['status']) && $rbiResponse['status']) {
											$this->session->rblSessionID = $rbiResponse['sessiontoken'];
										} 
										
										if(isset($rbiResponse['status']) && !$rbiResponse['status']) {
											//if Rbl login fails, we logout our application
											$this->logout();
										} 
									}
									
                                    $request = unserialize($session->request);
                                    if(!empty($request)){
                                       $previousUri = $request->getRequestUri();
                                        $this->_redirect($previousUri); /* no need to format url */
                                    }else{
                                    
                                       // loggin login at step 1
                                    $updateAttempts = $userModel->removenumLoginAttempts($id['id'],DbTable::TABLE_AGENTS);
                                            //Insert into login log
                             $loginLogArr = array(
                            'portal'=> MODULE_AGENT,
                            'agent_id' => $id['id'],
                            'datetime_login_step1'=> new Zend_Db_Expr('NOW()'),
                            'username' => $form->getValue('username'),
                            'comment_username' => STATUS_SUCCESS.':status= '.$id['status'],
                            'comment_password' => STATUS_SUCCESS,
                            'session_id' => $sessionId);
                            $userModel->insertLoginLog($loginLogArr); 

                                    $this->session->login_log_id = $lastId;

//                                       $this->_redirect('/profile/');
                                       $this->_redirect($this->formatURL('/profile/index/'));
                                    }
                                }
                                else { // if($res == 'incorrect_pass'){ // safe side
                                    $this->view->errType = 'incorrectuser';
                                } // incorrect pwd
                            } // status unblocked ends
                            else {
                                if($rowStatus['status'] == STATUS_LOCKED){
                                    $this->view->errType = 'locked';
                                }
                                elseif($rowStatus['status'] == STATUS_BLOCKED){
                                    $this->view->errType = 'blocked';
                                }
                                else {
                                    $this->view->errType = 'incorrectuser';
                                }
                                   //Insert into login log
                           $loginLogArr = array(
                            'portal'=> MODULE_AGENT,
                            'agent_id' => $rowStatus['id'],
                            'datetime_login_step1'=> new Zend_Db_Expr('NOW()'),
                            'username' => $form->getValue('username'),
                            'comment_username' => STATUS_SUCCESS.':status='.$rowStatus['status'],
                            'comment_password' => STATUS_SUCCESS,
                            'session_id' => $sessionId);
                            $userModel->insertLoginLog($loginLogArr); 
                            }
                       
                  
                        } // approved ends
                        else {
                            if($rowStatus['enroll_status'] == STATUS_PENDING){
                                    $this->view->errType = 'notapproved';
                        
                                }
                                elseif($rowStatus['enroll_status'] == STATUS_REJECTED){
                                    $this->view->errType = 'rejected';
                                }
                                else {
                                    $this->view->errType = 'incorrectuser'; // incomplete
                                }
                            $loginLogArr = array(
                            'portal'=> MODULE_AGENT,
                            'agent_id' => $rowStatus['id'],
                            'datetime_login_step1'=> new Zend_Db_Expr('NOW()'),
                            'username' => $form->getValue('username'),
                            'comment_username' => STATUS_SUCCESS.':enroll_status='.$rowStatus['enroll_status'],
                            'comment_password' => STATUS_SUCCESS,
                            'session_id' => $sessionId);
                            $userModel->insertLoginLog($loginLogArr);
                        }
                    } // verified ends
                    else {
                        $loginLogArr = array(
                            'portal'=> MODULE_AGENT,
                            'agent_id' => $rowStatus['id'],
                            'datetime_login_step1'=> new Zend_Db_Expr('NOW()'),
                            'username' => $form->getValue('username'),
                            'comment_username' => STATUS_SUCCESS.':email_status= '.$rowStatus['email_verification_status'],
                            'comment_password' => STATUS_SUCCESS,
                            'session_id' => $sessionId);
                            $userModel->insertLoginLog($loginLogArr);
                  
                        $this->view->errType = 'notverified';
                    }
                    
                } // rowStatus !empty ends
                else {
                    $this->view->errType = 'incorrectuser';
                }
                 
                
                } // if userExists ends
                else {
                    
                    
                   
                    if($id['id'] > 0) {
                        $numAttempts = $userModel->updatenumLoginAttempts($id['id'],DbTable::TABLE_AGENTS);

                        $config = App_DI_Container::get('ConfigObject');
                        $numAllowed = $config->system->login->attempts->allowed;
                        if($numAttempts >= $numAllowed){
                            // Login Step 1 status changed to locked

                            $data = array('agent_id'=>$id['id'],'status_old' => STATUS_UNBLOCKED,'status_new' => STATUS_LOCKED,'remarks' => 'Agent locked after '.$numAllowed.' attempts at Step 1');
                            $userModel->updateStatusLog($data);
                            $this->view->errType = 'locked';

                        }
                        else if($numAttempts >0)
                        {
                            $this->view->num = $numAllowed-$numAttempts;
                            $this->view->errType = 'incorrectpass';

                        }
                        // Insert into Login Log
                        $loginLogArr = array(
                            'portal'=> MODULE_AGENT,
                            'agent_id' => $id['id'],
                            'datetime_login_step1'=> new Zend_Db_Expr('NOW()'),
                            'username' => $form->getValue('username'),
                            'comment_username' => STATUS_SUCCESS.':status='.$id['status'],
                            'comment_password' => STATUS_FAILURE,
                            'session_id' => $sessionId);
                            $userModel->insertLoginLog($loginLogArr);
                    }
                    else {
                         $loginLogArr = array('portal'=> MODULE_AGENT,'datetime_login_step1'=> new Zend_Db_Expr('NOW()'),
                            'username' => $form->getValue('username'),
                            'comment_username' => STATUS_FAILURE,
                            'session_id' => $sessionId);
                         $userModel->insertLoginLog($loginLogArr);
                        $this->view->errType = 'incorrectuser';
                    }
                }
            
            } // form isvalid ends
            
            
        } 
        
        $this->view->form = $form;
        $this->view->showBcDivLogin = 'login';  // set flag for login page. 
    }
    
    /**
     * Allows users to log out of the application
     *
     * @access public
     * @return void
     */
    public function logoutAction(){
		$this->logout();
    }
	
	protected function logout() {
        // log the user out
        //Zend_Auth::getInstance()->clearIdentity();
        $sessionId    = Zend_Session::getId();
        $userModel = new AgentUser();
        $session = new Zend_Session_Namespace('App.Operation.Controller');
        $user = Zend_Auth::getInstance()->getIdentity();
        $new_mobile_update = $this->_getParam('update');
        
        if(App_DI_Container::get('ConfigObject')->session->ssn_implementation) {
            $sessionModel = new Session();
            $sessionModel->logoutSession();
        }
        
        $logindata =  array('portal'=> MODULE_AGENT,'agent_id' => $user->id,'datetime_logout' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId);  
        $userModel->insertLoginLog($logindata);
        // destroy the session
        Zend_Session::destroy();
         
        // go to the login page
//        $this->_redirect('/profile/login/');
        if($new_mobile_update)
        {
            $this->_redirect($this->formatURL('/profile/login?update=mobile'));
        }
        else
        {
            $this->_redirect($this->formatURL('/profile/login/'));
        }
	}
    
     
   
    public function authcodeAction()
    {   
        $sessionId    = Zend_Session::getId();
        $session = new Zend_Session_Namespace('App.Operation.Controller');
       
        $this->title = 'Login Authcode';
        $user = Zend_Auth::getInstance()->getIdentity();
       
        // use the login layout
         $this->_helper->layout()->setLayout('login');
         $userModel = new AgentUser();
         $sessionModel = new Session();
        $id = $userModel->getUserIdByUsername($user->username,DbTable::TABLE_AGENTS);

        $showServiceAgreement =''; // flag added when auth id is wrong. do not show popup again
       
        // Checking coming from resend auth code
        if(!empty($this->_getParam('resendfrom')))
        {
           $this->_setParam('econtractaccepted',22); 
        }
        
        $form = new AuthcodeForm();
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
                $alert = new Alerts();
                $a = $form->getValues();

                $showServiceAgreement = $a['econtractaccepted']; 
              
                $res = $alert->ValidateAuth($a['authcode'],DbTable::TABLE_AGENTS);
                if($this->session->resent){
                     $this->view->resent = TRUE;
                }
                else if ($this->session->notresent){
                   $this->view->notresent = TRUE; 
                }
                if($res == 'locked'){
                     $this->view->locked = TRUE;
                     $logindata =  array('portal'=> MODULE_AGENT,'agent_id' => $user->id,'datetime_login_step2' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId,
                          'comment_auth' => STATUS_SUCCESS.':status='.STATUS_LOCKED);  
                     $userModel->insertLoginLog($logindata);
                      
                }
              else if($res == 'inactive'){
                     $this->view->inactive = TRUE;
                     $logindata =  array('portal'=> MODULE_AGENT,'agent_id' => $user->id,'datetime_login_step2' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId,
                          'comment_auth' => STATUS_SUCCESS.':status='.STATUS_INACTIVE);  
                     $userModel->insertLoginLog($logindata);
                     
                }
                
                else if($res != 'correct'){
                     $numAttempts = $userModel->updatenumLoginAttempts($user->id,DbTable::TABLE_AGENTS);
                     $config = App_DI_Container::get('ConfigObject');
                     $numAllowed = $config->system->login->attempts->allowed;
                     if($numAttempts >= $numAllowed){
                         $this->view->locked = TRUE;
                           // Login Step 2
                     
                     }
                     else{
                     
                     
                     $this->view->num = $numAllowed-$numAttempts;
                     $this->view->error = TRUE;
                     
                     }
                      $logindata =  array('portal'=> MODULE_AGENT,'agent_id' => $user->id,'datetime_login_step2' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId,
                          'comment_auth' => STATUS_FAILURE);  
                   
                    $userModel->insertLoginLog($logindata);
                }                 
                  else{
                    if(App_DI_Container::get('ConfigObject')->session->ssn_implementation) {  
                        if(!$sessionModel->validateLoginSession()){
                            $this->_redirect($this->formatURL('/profile/login?sess=err'));
                        }
                    }
                    $updateAttempts = $userModel->removenumLoginAttempts($user->id,DbTable::TABLE_AGENTS);
                    
                    // updating last login in agent table
                    $agentArr = array('last_login'=>new Zend_Db_Expr('NOW()'));
                    $updLastLogin = $userModel->editAgent($agentArr, $user->id);
                    
                    
                    // loggin at step 2
                    $logindata =  array('portal'=> MODULE_AGENT,'agent_id' => $user->id,'datetime_login_step2' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId,'comment_auth' =>STATUS_SUCCESS);  
        
                    $userModel->insertLoginLog($logindata);
//                    $this->_redirect('/profile/');
                    
                    /**** Checking first login/Password update*/
                    
                     /**** checking last update date of password ****/
                        $lastPasswordUpdate = $user->last_password_update;
                        if($lastPasswordUpdate == NULL || $lastPasswordUpdate == ''){
                            $changePasswordRequired=true;  
                        }
                        else //if($lastPasswordUpdate!='')
                        {
                            $lastPasswordUpdateArr = explode(" ", $lastPasswordUpdate);
                            $lastPasswordUpdateDate = $lastPasswordUpdateArr[0];
                            $daysToAdd = App_DI_Container::get('ConfigObject')->agent->password->expirydays;
                            $passwordExpiryDate = strtotime(date("Y-m-d", strtotime($lastPasswordUpdateDate)) . " +$daysToAdd day");
                            $passwordExpiryDate = date ( 'Y-m-d' , $passwordExpiryDate );
                            $currentDate = date('Y-m-d');

                            if($currentDate > $passwordExpiryDate)
                               $changePasswordRequired=true;                            
                        }
                    /**** checking last update date of password over here ****/
                    
                    if($changePasswordRequired){
                        $this->_redirect($this->formatURL('/profile/change-password/'));
                   } else{
                         $a['econtractaccepted'] = (!empty($a['econtractaccepted']))? $a['econtractaccepted']:$this->_getParam('econtractaccepted');
                        #### checking, Agent has selected service aggreement or not 
                        if(isset($a['econtractaccepted']) && !empty($a['econtractaccepted'] && intval($a['econtractaccepted']) > 0))
                        {
                            if(intval($id['bcecontract']==0)){
                                $userModel->updateAgentsBcEcontract($id['id']);
                            }
                        }

                        $this->_redirect($this->formatURL('/profile/index/'));
                    }
                    
                    
                    //$this->_redirect($this->formatURL('/profile/index/'));
                }
                //}
            }
            
            //$this->view->empty = TRUE;
        } 
        
        $this->view->form = $form;
        $this->view->bcecontract = $id['bcecontract'];  // checking whether Agent accepted service agreement or not. 
        $this->view->showServiceAgreement = $showServiceAgreement;  //flag for show popup or not
        $this->view->showfromResendAuthCode = (!empty($this->_getParam('resendfrom'))) ? 1:'';
    }
    
    
    
       /**
     * Allows users to change their passwords
     *
     * @access public
     * @return void
     */
    public function changePasswordAction(){
        $this->title = 'Change Password';
        $objLog = new Log();
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new ChangePasswordForm();
        $agentModel = new AgentUser();
        
           
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                
                $isPasswordDuplicate = true;
                /**** checking new password with last 4 passwords for non duplicacy of password ****/
                try{
                     $passwordParam = array('agent_id'=>$user->id, 'password'=>BaseUser::hashPassword($form->getValue('password')));
                     $isPasswordDuplicate = $agentModel->checkPasswordDuplicate($passwordParam);
                    
                   } catch (Exception $e) {
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            $msg = $e->getMessage();
                            $this->_helper->FlashMessenger(array('msg-error' => $msg,));
                        }
                
                /**** checking new password with last 4 passwords for non duplicacy of password over  here ****/
                        
                if($isPasswordDuplicate==false){
               
                $agentModel->changePassword($form->getValue('password'));
                
                    /**** updating in change password log ****/

                    $logParam = array(
                                        'agent_id'=>$user->id,
                                        'ip'=>$agentModel->formatIpAddress(Util::getIP()),
                                        'password'=>BaseUser::hashPassword($form->getValue('password'))
                                     );
                    $objLog->insertlog($logParam, DbTable::TABLE_LOG_CHANGE_PASSWORD);

                    /**** updating in change password log ends here ****/
                        
                
                // Send new password email
                    $resArr = $agentModel->findById($user->id);
                                      
                    $detailArr = array(
                    'first_name'=>$resArr->first_name,
                    'last_name'=>$resArr->last_name,
                    'email'=>$resArr->email,
                    'password'=>$form->getValue('password'),
                    //'agent_code'=> $resArr->agent_code,
                );
                //echo '<pre>'; print_r($detailArr);exit;
                $m = new App\Messaging\MVC\Axis\Agent();
                $m->updatePasswordEmail($detailArr);
                
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Your password was successfully changed',
                    )
                );
                $user = Zend_Auth::getInstance()->getIdentity();                    
                //Password is successfully Updated So Removing the validation
                $user->passwordUpdateRequired = 0;
                
//                $this->_redirect('/profile/');
                $this->_redirect($this->formatURL('/profile/index/'));
              }
            }
        }
        
        
        $this->view->form = $form;
        
    }
    
    
    
    ////////////////////////////////////////*************/////////////////////////
     public function changemobilenumberAction(){
        $this->title = 'Change Mobile Number';
        $objLog = new Log();
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new ChangeMobileNumberForm();    
        $agentModel = new AgentUser();
        $this->title = 'Change Mobile Number';
        
        
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
                
                //check if both numbers entered are same.
                $old_num = $form->getValue('old_phone');
                $new_num = $form->getValue('new_phone');
                
                /* check if old number entered is correct
                $old_res = $agentModel->checkPhone($form->getValue('old_phone'),$user->id);
                
                if($old_num == $new_num)
                {
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Please Enter Different Mobile number.',
                    )
                    );
                }
                elseif($old_res != 'phone_dup')
                {
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Old Mobile number is not correct.',
                    )
                    );
                }
                */
                    try 
                    {
                    //check if new number entered already exists
                    $res = $agentModel->checkPhone($form->getValue('new_phone'));
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
                    
                    //check if new number entered already exists
                    if($res =='phone_dup')
                    {
                        $this->_helper->FlashMessenger(
                            array(
                                'msg-error' => 'Please enter different mobile number.',
                            )
                        );
                    }            
                    else 
                    {   $this->session->agent_id = $res;
                        $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Please check SMS on your mobile to get verification code',
                        )
                        );
                        $this->session->new_mobile1=$form->getValue('new_phone') ;

                        //Generate random verification code and store it in a session and send it to mobile phone in  SMS
                        $alerts = new Alerts();
                        $verificationCode = $alerts->generateAuthCode();
                        //echo $verificationCode;
                        $this->session->ver_code = $verificationCode;
                        //$this->session->ver_code;
                        try{

                            $info = array ('v_code'=>$verificationCode,'mobile1'=>$form->getValue('new_phone'));

                            $sendConf = $alerts->sendVerificationCode($info, 'operation');


                        } catch (Exception $e ) {    
                                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                    $errMsg = $e->getMessage();
                                    $this->_helper->FlashMessenger(
                                        array(
                                            'msg-error' => $errMsg,
                                        )
                                    );
                        } 
                        $this->_redirect($this->formatURL("/profile/mobileverification/"));
                    }
            }
        }
        $this->view->form = $form;
        $this->view->title = $this->title;
    }
    
    //set verification code
    public function mobileverificationAction()
    {
        $this->title = 'Mobile Verification Code';
        $agentModel = new AgentUser();
        $form = new MobileVerificationForm();
        $user = Zend_Auth::getInstance()->getIdentity();
        $userid = $user->id;
        
        $new_mobile = $this->session->new_mobile1; //$this->_getParam('new_mobile');
        
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
               if( $this->session->ver_code == $form->getValue('code') )
               {
                   $agentModel->changeMobileNumber($new_mobile,$userid);
                   $this->_redirect($this->formatURL("/profile/logout?update=mobile"));
               }
               else
               {
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Incorrect verification code entered',
                    )
                    );
               }
            }
        } 
        
        $this->view->form = $form;
    }
     ////////////////////////////////////////*************/////////////////////////
    
    
    
    
    
    public function forgotPasswordAction(){
        $this->title = 'Forgot Password';
        $form = new ForgotPasswordForm();
        // use the withoutlogin layout
        $this->_helper->layout()->setLayout('withoutlogin');
        $agentModel = new AgentUser();
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $res = $agentModel->checkAgentDetails($form->getValues());
                
                if(!$res){
                     $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Details do not match with our records',
                    )
                );
                
                }
                else{
                   $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Your Confirmation Code has been successfully sent to your email and mobile number',
                    )
                );
                $this->session->conf_code = $res['conf_code'];
                $this->session->agent_id = $res['id'];
                $this->session->email = $res['email'];
//                $this->_redirect('/profile/confirmation-code');
                $this->_redirect($this->formatURL('/profile/confirmation-code'));
                
                }
            }
        }
        
       
        $this->view->form = $form;
    }
    
    public function confirmationCodeAction(){
        //echo $this->session->conf_code;
        $this->title = 'Confirmation Code';
        $form = new ConfirmationcodeForm();
        // use the withoutlogin layout
        $this->_helper->layout()->setLayout('withoutlogin');
        $agentModel = new AgentUser();
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $confCode = $form->getValue('confcode');
                $username = $form->getValue('email');
                if( $confCode == $this->session->conf_code && strtolower(trim($username)) == strtolower(trim($this->session->email))){
                
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Please set a new password for your account',
                    )
                );
//                $this->_redirect('/profile/new-password');
                $this->_redirect($this->formatURL('/profile/new-password'));
                }
                else{
                     $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Wrong Details entered',
                    )
                );
                }
            }
        }
        
       
        $this->view->form = $form;
    }
    
        /**
     * Allows users to Add new passwords
     *
     * @access public
     * @return void
     */
    public function newPasswordAction(){
        $this->title = 'Create New Password';
        $m = new App\Messaging\MVC\Axis\Agent();
       
        $form = new NewPasswordForm();
        $agentModel = new AgentUser();
        // use the withoutlogin layout
        $this->_helper->layout()->setLayout('withoutlogin');
           
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                if (isset($this->session->agent_id)){
                
                if($agentModel->newPassword($form->getValue('password'),$this->session->agent_id)) {
                    // Send new password email
                    $resArr = $agentModel->findById($this->session->agent_id);
                                      
                    $detailArr = array(
                    'first_name'=>$resArr->first_name,
                    'last_name'=>$resArr->last_name,
                    'email'=>$resArr->email,
                    'password'=>$form->getValue('password'),
                    //'agent_code'=> $this->session->agent_code,
                );
                
                $m->updatePasswordEmail($detailArr);
                
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Your password was successfully changed',
                    )
                );
                unset($this->session->agent_id);
                unset($this->session->conf_code);
                unset($this->session->email);
//                $this->_redirect('/profile/login');
                $this->_redirect($this->formatURL('/profile/login'));
                }
                }
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'You are not authenticated to create new password',
                    )
                );
            }
        }
        
        
        $this->view->form = $form;
        
    }
    

    public function checkbalanceAction(){
        $this->title = 'Check Balance';
        $this->view->heading = 'Check Balance';
        $user = Zend_Auth::getInstance()->getIdentity();
        $agentId = $user->id;
        $objAgent = new AgentBalance(); 
        $objAgentVirtual = new AgentVirtualBalance();
        try {
            $agentBalance = $objAgent->getAgentActiveBalance($agentId);  
            $agentVirtualBalance = $objAgentVirtual->getAgentBalance($agentId);
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }
        $form = $this->getForm();                       
        $this->view->form = $form;
        $this->view->balance = Util::numberFormat($agentBalance);
        if($agentVirtualBalance != FLAG_NO){
            $this->view->virtualbalance = Util::numberFormat($agentVirtualBalance);
        }
    }

 
    public function getForm(){
        return new CheckBalanceForm(array(
            'action' => $this->formatURL('/agent/profile/checkbalance'),
            'method' => 'post',
            'name'=>'frmCheckBalance',
            'id'=>'frmCheckBalance'
        ));
    }
      public function sendotpAction(){
         $this->session = new Zend_Session_Namespace("App.Agent.Controller");
         unset($this->session->ver_code);
         
         $user = Zend_Auth::getInstance()->getIdentity();
             $this->title = 'Mobile Verification';
         $m = new App\Messaging\Corp\Boi\Agent();
        
         try{
                        
                        $info = array ('mobile1'=>$user->mobile1);
                            
                       $resp = $m->verificationCode($info);
                   $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Please check SMS on your mobile to get verification code',
                    )
                );       
                  $this->_redirect($this->formatURL('/profile/verification/'));        
                    } catch (Exception $e ) {    
                                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                $errMsg = $e->getMessage();
                                $this->_helper->FlashMessenger(
                                    array(
                                        'msg-error' => $errMsg,
                                    )
                                );
                    } 

        $this->view->title = $this->title;
    }
    
    //set verification code
     public function verificationAction()
    {
        $this->title = 'Mobile Verification Code';
        // use the login layout
        
        $form = new VerificationForm();
        
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
               if( $this->session->ver_code == $form->getValue('code') )
               {
                  
                   $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Mobile phone verified successfully. Please proceed.',
                    )
                );
//                   $this->_redirect('/signup/add');
                   $this->_redirect($this->formatURL('/profile/editbank'));
                  
               }
               else
               {
                
                   
                   $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Incorrect verification code entered',
                    )
                );
               }
            }
            
            
        } 
        
        $this->view->form = $form;
         
    }  
    
    public function editbankAction(){
        $this->title = 'Edit Bank Details';
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new AgentbankEditForm();
        $agentModel = new Agents();
        $formData  = $this->_request->getPost();
        $this->session->agent_id = $user->id;
        $id = $this->session->agent_id;
       
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                
                
         
         $agentdetails = array(
        'bank_name' =>$formData['bank_name'],
        'bank_account_number' => $formData['bank_account_number'],
        'bank_location' => $formData['bank_location'],'bank_city' => $formData['bank_city'],'bank_ifsc_code' => $formData['bank_ifsc_code'],
        'branch_id' => $formData['branch_id'],'bank_area' => $formData['bank_area'],'by_ops_id' =>$user->id,'ip' => $agentModel->formatIpAddress(Util::getIP())
                 );
                
          $allDetails = $agentModel->findagentDetailsById($id);
          
          
          $dataagentdetails = array_merge($allDetails, $agentdetails);
          //  echo '<pre>';print_r($dataagentdetails );        exit;   
          unset($dataagentdetails['id']);     
          
          $agentmodel = $agentModel->bankupdatedetails($dataagentdetails,$id);
                if($agentmodel){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The Bank details were successfully edited.',
                    )
                );
                   $detail_id =$form->getValue('agent_detail_id');
                   $inactiveArr = array('status' => STATUS_INACTIVE);
                   $agentmodel = $agentModel->inactiveAgentDetails($inactiveArr,$detail_id);
                   unset($this->session->agent_id);
                   $this->_redirect($this->formatURL('/profile/index/'));
            }
            else
                {
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'The Bank details could not be edited',
                    )
                );
                    
            }
                
                
                //Regenerate Flag and Flippers
                App_FlagFlippers_Manager::save();
                
                //$this->_redirect('/system/editbank');
            }
        }else{
            
            
            if (!is_numeric($id)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'The provided ID is invalid.',
                    )
                );
                
                //$this->_redirect('/system/editbank');
            }
            
            $row = $agentModel->findById($id);
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => 'The requested details could not be found.',
                    )
                );
                
                //$this->_redirect('/system/editbank');
            }
            
            $form->getElement('agent_detail_id')->setValue($row['row_id']);
            $form->getElement('ifsc')->setValue($row['bank_ifsc_code']);
            $form->populate($row->toArray());
            $this->view->item = $row;
        }
        $this->view->agent_id = $id;
        $this->view->form = $form;
        
    }    
     /**
     * Allows users to change their passwords
     *
     * @access public
     * @return void
     */
    public function resendVerificationlinkAction(){
        $this->title = 'Resend Verification Email';
        $this->view->title = 'Resend Verification Email';
        $objLog = new Log();
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new ResendVerificationForm();
        $agentModel = new AgentUser();
        $approveModel = new Approveagent();
         
        $this->_helper->layout()->setLayout('withoutlogin');
        
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                try {
                   
                    $rowStatus = $agentModel->checkAgentApproved($form->getValue('username'),$form->getValue('mobile1'));
                    if($rowStatus->enroll_status == STATUS_APPROVED){
                   
                      if($rowStatus->email_verification_status == STATUS_PENDING){
                    //Generate Password and update password on db
                    $password = Util::generateRandomPassword();

                    $db_password = BaseUser::hashPassword($password, 'agent');

                    $agent_code = $approveModel->getAgentCode($rowStatus->id);



                    // generate Verification code for email verification
                    $verification_code = Util::hashVerification($agent_code);

                    $dataarr = array(
                        'password' => $db_password,
                    );
                   
                    $agentModel->update($dataarr, "id = " . $rowStatus->id);
                    
                     $detailsArr = array(
                         'id' => $rowStatus->id,
                         'first_name'=> $rowStatus->first_name,
                         'last_name'=> $rowStatus->last_name,
                         'name'=> $rowStatus->name,
                         'email'=> $rowStatus->email,
                         'password'=>$password,
                         'agent_code'=>$agent_code);
          
                    
                    $activationLink = $approveModel->sendVerificationCode($rowStatus->id,$verification_code,$detailsArr);
           
                    if($activationLink > 0){

                    $this->_helper->FlashMessenger(
                            array(
                                'msg-success' => RESEND_VERIFICATION_SUCCESS,
                            )
                    );
                     }
                      }
                     else if($rowStatus->email_verification_status == STATUS_VERIFIED){
                     $this->_helper->FlashMessenger(
                            array(
                                'msg-error' => 'Email is already verified.',
                            )
                    );   
                    }
                    }
                    
                    else {
                    $this->_helper->FlashMessenger(
                            array(
                                'msg-error' => RESEND_VERIFICATION_ERROR,
                            )
                    );
                    }
                   
                    $this->_redirect($this->formatURL('/profile/index/'));
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $msg = $e->getMessage();
                    $this->_helper->FlashMessenger(array('msg-error' => $msg,));
                }
            }
        }


        $this->view->form = $form;
        
    }
	
	protected function loginToRbi($data) {
		$rblApiObject = new App_Rbl_Api();
		return $rblApiObject->channelPartnerLogin($data);
	}

}
