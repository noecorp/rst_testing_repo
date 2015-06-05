<?php
/**
 * Allows user to manage their profile data
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class ProfileController extends App_Corporate_Controller
{
    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init(){
         $this->session = new Zend_Session_Namespace("App.Corporate.Controller");
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
              
        try {
               $user = Zend_Auth::getInstance()->getIdentity();
               if(!isset($user->id)) {
                  $this->_redirect($this->formatURL('/profile/login'));
                  exit;
               }
               $this->title = 'Dashboard';
               $dashboard = new Dashboard();
               
               $corporateStats = $dashboard->corporateDashboardStats();
               $this->view->corporateStats = $corporateStats;                
        
          } catch (Exception $e) {
               //echo "i am here"; exit;
               App_Logger::log($e->getMessage(), Zend_Log::ERR);
               print $e->getMessage();
          }
    }
    
    
    
     public function resendAuthcodeAction(){
     
        // use the login layout
        $this->_helper->layout()->setLayout('login');
        $user = Zend_Auth::getInstance()->getIdentity();
        try {
          $m = new App\Messaging\MVC\Axis\Corporate();
          $flg = $m->authCode($user);
          $this->_helper->FlashMessenger(
                     array(
                         'msg-success' => 'Authcode resent successfully',
                     )
                 );
          } catch(Exception $e){
                     App_Logger::log($e->getMessage(), Zend_Log::ERR);
                     $Msg = $e->getMessage();
                      $this->_helper->FlashMessenger(
                                 array(
                                      'msg-error' => $Msg,
                                 )
                             );
          }
               
          $this->_redirect($this->formatURL('/profile/authcode/'));

    }
    /**
     * Allows users to log into the application
     *
     * @access public
     * @return void
     */
   public function loginAction(){
        $sessionId    = Zend_Session::getId();
        $user = Zend_Auth::getInstance()->getIdentity();
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
                
                $loginClientKey = App_DI_Container::get('ConfigObject')->{corporate}->security->loginClientKey ;
                $password = Util::crypt_fn($loginClientKey,$form->getValue('password'),'descrypt');
                
                $userModel = new CorporateUser();  
                $userExists = $userModel->chkLoginCredentials($form->getValue('username'), $password);
                $userData = $userModel->getUserIdByUsername($form->getValue('username'),DbTable::TABLE_CORPORATE_USER);
                
                if(!empty($userData['id'])) {
                    $rowStatus = $userModel->getStatus($userData['id']);
                
                
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
                                //print_r($rowStatus['enroll_status']); exit;  
                                $res = $userModel->login($form->getValue('username'), $password);
                                if($res == 'correct_pass'){
                                   
                                    $session = new Zend_Session_Namespace('App.Operation.Controller');
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
                    
                    
                   
                    if($userData['id'] > 0) {
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
    }
    
    
    /**
     * Allows users to log out of the application
     *
     * @access public
     * @return void
     */
    public function logoutAction(){
        // log the user out
        //Zend_Auth::getInstance()->clearIdentity();
        $sessionId    = Zend_Session::getId();
        $userModel = new CorporateUser();
        $session = new Zend_Session_Namespace('App.Corporate.Controller');
        $user = Zend_Auth::getInstance()->getIdentity();
        if(!isset($user->id)) {
             $this->_redirect($this->formatURL('/profile/login'));
             exit;
        }
        if(App_DI_Container::get('ConfigObject')->session->ssn_implementation) {
            $sessionModel = new Session();
            $sessionModel->logoutSession();
        }
        
        $logindata =  array('portal'=> MODULE_CORPORATE,'corporate_id' => $user->id,'datetime_logout' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId);  
        $userModel->insertLoginLog($logindata);
        // destroy the session
        Zend_Session::destroy();
         
        // go to the login page
//        $this->_redirect('/profile/login/');
        $this->_redirect($this->formatURL('/profile/login/'));
    }
    
     
   
    public function authcodeAction()
    {   
        $sessionId    = Zend_Session::getId();
        $session = new Zend_Session_Namespace('App.Corporate.Controller');
       
        $this->title = 'Login Authcode';
        $user = Zend_Auth::getInstance()->getIdentity();
       // echo  "<pre>";print_r($user); exit;
       	// use the login layout
         $this->_helper->layout()->setLayout('login');
         $userModel = new CorporateUser();
         $sessionModel = new Session();
         $id = $userModel->getUserIdByUsername($user->email,DbTable::TABLE_CORPORATE_USER);
         $form = new AuthcodeForm();
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
                $alert = new Alerts();
                $a = $form->getValues();
              
                $res = $alert->ValidateAuth($a['authcode'],DbTable::TABLE_CORPORATE_USER);
              
                if($this->session->resent){
                     $this->view->resent = TRUE;
                }
                else if ($this->session->notresent){
                   $this->view->notresent = TRUE; 
                }
                
                if($res == 'locked'){
                     $this->view->locked = TRUE;
                     $logindata =  array('portal'=> MODULE_CORPORATE,'bank_id' => $user->id,'datetime_login_step2' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId,
                          'comment_auth' => STATUS_SUCCESS.':status='.STATUS_LOCKED);  
                     $userModel->insertLoginLog($logindata);
                      
                }elseif($res == 'inactive'){
                     $this->view->inactive = TRUE;
                     $logindata =  array('portal'=> MODULE_CORPORATE,'bank_id' => $user->id,'datetime_login_step2' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId,
                          'comment_auth' => STATUS_SUCCESS.':status='.STATUS_INACTIVE);  
                     $userModel->insertLoginLog($logindata);
                     
                }elseif(!$res){ 
                     $numAttempts = $userModel->updatenumLoginAttempts($user->id,DbTable::TABLE_CORPORATE_USER);
                     $config = App_DI_Container::get('ConfigObject');
                     $numAllowed = $config->system->login->attempts->allowed;
                     if($numAttempts >= $numAllowed){
                         $this->view->locked = TRUE;
                          
                     }
                     else{
                     $this->view->num = $numAllowed-$numAttempts;
                     $this->view->error = TRUE;
                    
                     }
                      $logindata =  array('portal'=> MODULE_CORPORATE,'bank_id' => $user->id,'datetime_login_step2' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId,
                          'comment_auth' => STATUS_FAILURE);  
                   
                    $userModel->insertLoginLog($logindata);
                }else{
                    /*if(App_DI_Container::get('ConfigObject')->session->ssn_implementation) {  
                        if(!$sessionModel->validateLoginSession()){
                            $this->_redirect($this->formatURL('/profile/login?sess=err'));
                        }
                    }*/
                    $updateAttempts = $userModel->removenumLoginAttempts($user->id,DbTable::TABLE_CORPORATE_USER);
                    
                    // updating last login in agent table
                    $agentArr = array('last_login'=>new Zend_Db_Expr('NOW()'));
                    $updLastLogin = $userModel->editCorporateUser($agentArr, $user->id);
                    
                    
                    // loggin at step 2
                    $logindata =  array('portal'=> MODULE_CORPORATE,'bank_id' => $user->id,'datetime_login_step2' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId,'comment_auth' =>STATUS_SUCCESS);  
        
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
                    $this->_redirect($this->formatURL('/profile/index/'));
                    if($changePasswordRequired)
                        $this->_redirect($this->formatURL('/profile/change-password/'));
                    else
                        $this->_redirect($this->formatURL('/profile/index/'));
                    
                    
                    //$this->_redirect($this->formatURL('/profile/index/'));
                }
                //}
            }
            
            //$this->view->empty = TRUE;
        } 
        
        $this->view->form = $form;
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
        $agentModel = new CorporateUser();
        
           
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                
                $isPasswordDuplicate = true;
                /**** checking new password with last 4 passwords for non duplicacy of password ****/
                try{
                     $passwordParam = array('corporate_id'=>$user->id, 'password'=>BaseUser::hashPassword($form->getValue('password')));
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
                $m = new App\Messaging\MVC\Axis\Corporate();
                $m->updatePasswordEmail($detailArr);
                
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Your password has been changed successfully',
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
    public function forgotPasswordAction(){
        $this->title = 'Forgot Password';
        $form = new ForgotPasswordForm();
        // use the withoutlogin layout
        $this->_helper->layout()->setLayout('withoutlogin');
        $agentModel = new CorporateUser();
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $res = $agentModel->checkCorporateDetails($form->getValues());
                if($res=="enroll_status_error"){
                    $this->_helper->FlashMessenger(array('msg-error' => 'Approval process in progress.',));
                }elseif($res=="email_status_error"){
                     $this->_helper->FlashMessenger(array('msg-error' => 'Oops! Your Email id is not verified. Please contact to Corporate Help Center.',));
                }elseif(!$res){
                     $this->_helper->FlashMessenger(array('msg-error' => 'Details do not match with our records',));
                }else{
                   $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Your Confirmation Code has been successfully sent to your email and mobile number',
                    )
                );
                $this->session->conf_code = $res['conf_code'];
                $this->session->corporate_id = $res['id'];
                $this->session->email = $res['email'];
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
        $agentModel = new CorporateUser();
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $confCode = $form->getValue('confcode');
                $username = $form->getValue('email');
                if( $confCode == $this->session->conf_code && $username == $this->session->email){
                
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
        $m = new App\Messaging\MVC\Axis\Corporate();
       
        $form = new NewPasswordForm();
        $agentModel = new CorporateUser();
        // use the withoutlogin layout
        $this->_helper->layout()->setLayout('withoutlogin');
           
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                if (isset($this->session->corporate_id)){
                
                if($agentModel->newPassword($form->getValue('password'),$this->session->corporate_id)) {
                    // Send new password email
                    $resArr = $agentModel->findById($this->session->corporate_id);
                                      
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
                unset($this->session->corporate_id);
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
        
        $user = Zend_Auth::getInstance()->getIdentity();
        $agentId = $user->id;
        $objAgent = new AgentBalance(); 
        try {
              $agentBalance = $objAgent->getAgentActiveBalance($agentId);
         
         } catch (Exception $exc) {
                echo $exc->getMessage();
         }
        
        // Get our form and validate it
        $form = $this->getForm();                       
        $this->view->form = $form; 
        $this->view->balance = Util::numberFormat($agentBalance);      
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
        $this->title = 'Resend Verification Link';
        $objLog = new Log();
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new ResendVerificationForm();
        $agentModel = new AgentUser();
        $approveModel = new Approveagent();
           
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
    public function webtermAction(){
     $user = Zend_Auth::getInstance()->getIdentity();
     
    }
    public function privacyAction(){
     $user = Zend_Auth::getInstance()->getIdentity();
    }
}