<?php
/**
 * Allows user to manage their profile data
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class ProfileController extends App_Operation_Controller
{
    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init(){
        $this->session = new Zend_Session_Namespace("App.Operation.Controller");
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
      $user = Zend_Auth::getInstance()->getIdentity();
       if(!isset($user->id)) {
//           $this->_redirect("/profile/login");
           $this->_redirect($this->formatURL('/profile/login/'));
           exit;
       }
       
        $this->title = 'Dashboard';
        $this->view->msg ='DASHBOARD';
        
//        $agentModel = new AgentUser();
//        $agentStats = $agentModel->getOpsDashboardStats();
        
        $dashboardModel = new Dashboard();
        $opsStats = $dashboardModel->getOpsDashboardStats();
        
        $this->view->stats = $opsStats;
    }
    
    /**
     * Allows the users to update their profiles
     *
     * @access public
     * @return void
     */
    public function editAction(){
        $this->title = 'Edit Your Profile';
        
        $form = new ProfileForm();
        $userModel = new OperationUser();
        $formData = $this->_request->getPost();
        $user = Zend_Auth::getInstance()->getIdentity();
        $row = $userModel->findById($user->id);
        $userInfo = $row->toArray();
            
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                
                $userData = $userInfo['username'];
                try{
                        /*** checking duplicacy for email ***/
                        $emailParams = array('email'=>trim($formData['email']), 'oldEmail'=>$userInfo['email']);
                        $isEmailDuplicate = $userModel->checkEmailDuplication($emailParams);
                        
                        /*** checking duplicacy for mobile ***/
                        $mobileParams = array('mobile'=>$formData['mobile1'], 'oldMobile'=>$userInfo['mobile1']);
                        $isMobileDuplicate = $userModel->checkMobileDuplication($mobileParams);
                        
                        /******* updating ops user in db *******/
                        $data['username'] = $formData['username'];
                        $data['email'] = $formData['email'];
                        $data['firstname'] = $formData['firstname'];
                        $data['lastname'] = $formData['lastname'];
                        $data['mobile1'] = $formData['mobile1'];
                        $userModel->updateUser($data);
                        $this->_helper->FlashMessenger(
                            array(
                                'msg-success' => 'Your profile was successfully updated',
                            )
                        );
                        $this->_redirect($this->formatURL('/profile/edit/'));
                        /******* updating ops user in db over *******/
                        
                   } catch(Exception $e) {
                       App_Logger::log($e->getMessage(), Zend_Log::ERR);
                       $this->_helper->FlashMessenger(
                                array(
                                    'msg-error' => $e->getMessage(),
                                )
                            );
                   }
                
                
            }
        }else{
            $form->populate($userInfo);
            $this->view->item = $row;
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
        
        /*if($user->id == 1){
            $this->_helper->FlashMessenger(
                array(
                    'msg-warn' => 'Please don\'t change the admin password in this release.',
                )
            );
            $this->_redirect('/profile/');
        }*/
        
        $form = new ChangePasswordForm(array( 'method' => 'POST',
                                              'name' => 'frmchangepassword'
                                           ));
        $userModel = new OperationUser();
        
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $isPasswordDuplicate = true;
                /**** checking new password with last 4 passwords for non duplicacy of password ****/
                try{
                     $passwordParam = array('ops_id'=>$user->id, 'password'=>BaseUser::hashPassword($form->getValue('password')));
                     $isPasswordDuplicate = $userModel->checkPasswordDuplicate($passwordParam);
                    
                   } catch (Exception $e) {
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            $msg = $e->getMessage();
                            $this->_helper->FlashMessenger(array('msg-error' => $msg,));
                        }
                
                /**** checking new password with last 4 passwords for non duplicacy of password over  here ****/
                if($isPasswordDuplicate==false){
                    
                    $userModel->changePassword($form->getValue('password'));

                    /**** updating in change password log ****/

                        $logParam = array(
                                            'ops_id'=>$user->id,
                                            'ip'=>$userModel->formatIpAddress(Util::getIP()),
                                            'password'=>BaseUser::hashPassword($form->getValue('password'))
                                         );
                        $objLog->insertlog($logParam, DbTable::TABLE_LOG_CHANGE_PASSWORD);

                    /**** updating in change password log ends here ****/

                    // Send new password email
                        $resArr = $userModel->findById($user->id);

                        $detailArr = array(
                        'firstname'=>$resArr->firstname,
                        'lastname'=>$resArr->lastname,
                        'email'=>$resArr->email,
                        'password'=>$form->getValue('password'),
                        'username'=> $user->username,
                    );
                  // echo '<pre>';print_r($detailArr);exit;
                    $m = new App\Messaging\MVC\Axis\Operation();
                    $m->updatePasswordEmail($detailArr);
                    $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Your password was successfully changed',
                        )
                    );
                    $user = Zend_Auth::getInstance()->getIdentity();                    
                    //Password is successfully Updated So Removing the validation
                    $user->passwordUpdateRequired = 0;
                    //echo "<pre>";print_r($user);exit;
    //                $this->_redirect('/profile/');
                    $this->_redirect($this->formatURL('/profile/index/'));
              }
            }
        }
        
        $this->view->form = $form;
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
                         
                $userModel = new OperationUser(); 
                $loginClientKey = App_DI_Container::get('ConfigObject')->operation->security->loginClientKey ;
                $password = Util::crypt_fn($loginClientKey,$form->getValue('password'),'descrypt');
                  
                $userExists = $userModel->chkLoginCredentials($form->getValue('username'), $password);
                $id = $userModel->getUserIdByUsername($form->getValue('username'),  DbTable::TABLE_OPERATION_USERS);  
                
                if($userExists) {
                    $rowStatus = $userModel->getOpsStatus($form->getValue('username'));
                    if($rowStatus['status'] == STATUS_ACTIVE) {
                        
                            if(App_DI_Container::get('ConfigObject')->session->ssn_implementation) {
                                if(!$sessionModel->loginSession(array('user_id' => $rowStatus['id']))){
                                    $this->_redirect($this->formatURL('/profile/login?sess=err'));
                                }
                            }
                            $res = $userModel->login($form->getValue('username'), $password);
                            if($res) { //if($res == 'correct_pass'){

                                $session = new Zend_Session_Namespace('App.Operation.Controller');
                                //$session = new stdClass();
                                $request = unserialize($session->request);
                                //$request = unserialize($this->getRequest());
                                
                                if(!empty($request)){
                                    //echo __CLASS__ . ":" . __FUNCTION__ . ":" . __LINE__;exit;
                                    $previousUri = $request->getRequestUri();
                                    $this->_redirect($previousUri);
                                }else{
                                    //logging at step1
                                     
                                     $updateAttempts = $userModel->removenumLoginAttempts($form->getValue('username'),DbTable::TABLE_OPERATION_USERS);
                                     
                                     
                                     //Insert into login log
                              $loginLogArr = array(
                            'portal' => MODULE_OPERATION,
                            'ops_id' => $id['id'],
                            'datetime_login_step1'=> new Zend_Db_Expr('NOW()'),
                            'username' => $form->getValue('username'),
                            'comment_username' => STATUS_SUCCESS.':status='.$id['status'],
                            'comment_password' => STATUS_SUCCESS,
                            'session_id' => $sessionId);
                            $userModel->insertLoginLog($loginLogArr);
                                     $this->_redirect($this->formatURL('/profile/authcode/'));
                                }

                            }
                            else { // if $res == FALSE // if($res == 'incorrect_pass'){  // safe side                
                           
                            $this->view->errType = 'incorrectpass';  
                            }
                        } // active status
                        elseif($rowStatus['status'] == STATUS_LOCKED){
                            
                            $this->view->errType = 'locked';
                       }
                       else if($rowStatus['status'] == STATUS_INACTIVE){
                           
                            $this->view->errType = 'inactive';
                       }
                       else {
                          
                           $this->view->errType = 'incorrectuser';
                       }
                       //Insert into login log
                       $loginLogArr = array(
                           'portal' => MODULE_OPERATION,
                            'ops_id' => $id['id'],
                            'datetime_login_step1'=> new Zend_Db_Expr('NOW()'),
                            'username' => $form->getValue('username'),
                            'comment_username' => STATUS_SUCCESS.':status='.$id['status'],
                            'comment_password' => STATUS_SUCCESS,
                            'session_id' => $sessionId);
                            $userModel->insertLoginLog($loginLogArr);
                    
                } // $userExists ends
                else
                {
                    // if username is correct, give numallowed, else incorrect creds
                    if($id['id'] > 0) {
                        $numAttempts = $userModel->updatenumLoginAttempts($form->getValue('username'),DbTable::TABLE_OPERATION_USERS);

                        $config = App_DI_Container::get('ConfigObject');
                        $numAllowed = $config->system->login->attempts->allowed;
                        if($numAttempts >= $numAllowed){
                            //$this->view->locked = TRUE;
                             // Login Step 1
                            $data = array('ops_id'=>$id['id'],'status_old' => STATUS_ACTIVE,'status_new' => STATUS_LOCKED,'remarks' => 'Ops locked after '.$numAllowed.' attempts at Step 1'
                             );
                           $userModel->updateStatusLog($data);
                           $this->view->errType = 'locked';
                        }
                        else{
                           $this->view->num = $numAllowed-$numAttempts;
                           //$this->view->error = TRUE;
                            $this->view->errType = 'incorrectpass';
                        }
                        // Insert into Login Log
                        $loginLogArr = array(
                            'portal' => MODULE_OPERATION,
                            'ops_id' => $id['id'],
                            'datetime_login_step1'=> new Zend_Db_Expr('NOW()'),
                            'username' => $form->getValue('username'),
                            'comment_username' => STATUS_SUCCESS.':status='.$id['status'],
                            'comment_password' => STATUS_FAILURE,
                            'session_id' => $sessionId);
                            $userModel->insertLoginLog($loginLogArr);
                    }
                    else{
       
                        $loginLogArr = array('portal' => MODULE_OPERATION,'datetime_login_step1'=> new Zend_Db_Expr('NOW()'),
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
        $sessionId    = Zend_Session::getId();
        $userModel = new AgentUser();
        $session = new Zend_Session_Namespace('App.Operation.Controller');
        $user = Zend_Auth::getInstance()->getIdentity();
        if(App_DI_Container::get('ConfigObject')->session->ssn_implementation) {
            $sessionModel = new Session();
            $sessionModel->logoutSession();
        }
        $logindata =  array('portal' => MODULE_OPERATION,'ops_id' => $user->id,'datetime_logout' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId);  
        $userModel->insertLoginLog($logindata);
        // log the user out
        Zend_Auth::getInstance()->clearIdentity();
        
        // destroy the session
        Zend_Session::destroy();
        
        // go to the login page
        $this->_redirect($this->formatURL('/profile/login/'));
    }
    
    public function resendAuthcodeAction(){
        //$this->session->resent = FALSE;
        //$this->session->notresent = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $userModel = new OperationUser();
        $dataArr = $userModel->findDetails($user->username,DbTable::TABLE_OPERATION_USERS);
        //$dataArr['authcode'] =  $dataArr['auth_code'];
        //$alert = new Alerts();
         try {
             
             $m = new App\Messaging\MVC\Axis\Operation();
                        
             $flg = $m->authCode($dataArr);
                    
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
     
    public function authcodeAction()
    {   $sessionId    = Zend_Session::getId();
        $session = new Zend_Session_Namespace('App.Operation.Controller');
        $this->title = 'Login Authcode';
        // use the login layout
        $this->_helper->layout()->setLayout('login');
        $userModel = new OperationUser();
        $sessionModel = new Session();
        $form = new AuthcodeForm();
        $user = Zend_Auth::getInstance()->getIdentity();
//        echo '<pre>';print_r($user);exit;
        $id = $userModel->getUserIdByUsername($user->username,DbTable::TABLE_OPERATION_USERS);
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
                
                $alert = new Alerts();
                $a = $form->getValues();
                $res = $alert->ValidateAuth($a['authcode'],DbTable::TABLE_OPERATION_USERS);
                 if($this->session->resent){
                     $this->view->resent = TRUE;
                }
                else if ($this->session->notresent){
                   $this->view->notresent = TRUE; 
                }
                if($res == 'locked' || $res == 'not_allowed'){
                   
                     $this->view->locked = TRUE;
                     $logindata =  array('portal' => MODULE_OPERATION,'ops_id' => $user->id,'datetime_login_step2' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId,
                          'comment_auth' => STATUS_FAILURE.':status='.$id['status']);  
                    $userModel->insertLoginLog($logindata);
                    
                }
               
                else if($res != 'correct'){
                    
                     $numAttempts = $userModel->updatenumLoginAttempts($user->username,DbTable::TABLE_OPERATION_USERS);
                     $config = App_DI_Container::get('ConfigObject');
                     $numAllowed = $config->system->login->attempts->allowed;
                     if($numAttempts >= $numAllowed){ //if($numAttempts == 'locked'){
                         $this->view->locked = TRUE;
                           // Login Step 2
                      
                      $data = array('portal' => MODULE_OPERATION,'ops_id' => $user->id,'status_old' => STATUS_ACTIVE,'status_new' => STATUS_LOCKED,'remarks' => 'Ops locked after '.$numAllowed.' attempts at Step 2'
                     );
                       $userModel->updateStatusLog($data);
                     }
                     else{
                     $this->view->num = $numAllowed-$numAttempts;
                     $this->view->error = TRUE;
                     
                     }
                      
                    $logindata =  array('portal' => MODULE_OPERATION,'ops_id' => $user->id,'datetime_login_step2' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId,
                          'comment_auth' => STATUS_FAILURE);  
                   
                    $userModel->insertLoginLog($logindata);
                   
                }else{
                    if(App_DI_Container::get('ConfigObject')->session->ssn_implementation) {
                        if(!$sessionModel->validateLoginSession()){
                            $this->_redirect($this->formatURL('/profile/login?sess=err'));
                        }
                    }
                    $updateAttempts = $userModel->removenumLoginAttempts($user->username,DbTable::TABLE_OPERATION_USERS);
                    
                    // updating last login in operation table
                    $updLastLogin = $userModel->updateUser(array('last_login'=>new Zend_Db_Expr('NOW()')));
                        
                    
                    // loggin at step 2
                    $logindata =  array('portal' => MODULE_OPERATION,'ops_id' => $user->id,'datetime_login_step2' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId,'comment_auth' =>STATUS_SUCCESS.':status='.$id['status']);  
        
                    $userModel->insertLoginLog($logindata);
                    $changePasswordRequired = false;
                    
                    /**** checking last update date of password ****/
                        $lastPasswordUpdate = $user->last_password_update;
                         /**** Checking first login/Password update*/
                     if($lastPasswordUpdate == NULL || $lastPasswordUpdate == ''){
                            $changePasswordRequired=true;  
                        }
                        else //if($lastPasswordUpdate!='')
                        {
                            $lastPasswordUpdateArr = explode(" ", $lastPasswordUpdate);
                            $lastPasswordUpdateDate = $lastPasswordUpdateArr[0];
                            $daysToAdd = App_DI_Container::get('ConfigObject')->operation->password->expirydays;
                            $passwordExpiryDate = strtotime(date("Y-m-d", strtotime($lastPasswordUpdateDate)) . " +$daysToAdd day");
                            $passwordExpiryDate = date ( 'Y-m-d' , $passwordExpiryDate );
                            $currentDate = date('Y-m-d');

                            if($currentDate > $passwordExpiryDate)
                               $changePasswordRequired=true;                            
                        }
                    /**** checking last update date of password over here ****/
                    
                    if($changePasswordRequired)
                        $this->_redirect($this->formatURL('/profile/change-password/'));
                    else
                        $this->_redirect($this->formatURL('/profile/index/'));
                }
                //}
            }
            
            //$this->view->empty = TRUE;
        } 
        
        $this->view->form = $form;
    }
    
     /**
     * Allows users to get password in case of forgot password
     *
     * @access public
     * @return void
     */
   
     public function forgotPasswordAction(){
        $this->title = 'Forgot Password';
        $form = new ForgotPasswordForm();
        // use the withoutlogin layout
        $this->_helper->layout()->setLayout('withoutlogin');
        $opsModel = new OperationUser();
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $res = $opsModel->checkOpsDetails($form->getValues());
                
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
                $this->session->ops_id = $res['id'];
                $this->session->username = $res['username'];
//                $this->_redirect('/profile/confirmation-code');
                $this->_redirect($this->formatURL('/profile/confirmation-code/'));
                
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
                $username = $form->getValue('username');
                if( $confCode == $this->session->conf_code && $username == $this->session->username){
                
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Please set a new password for your account',
                    )
                );
//                $this->_redirect('/profile/new-password');
                $this->_redirect($this->formatURL('/profile/new-password/'));
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
        
       // use the withoutlogin layout
        $this->_helper->layout()->setLayout('withoutlogin');
        $form = new NewPasswordForm();
        $opsModel = new OperationUser();
        
           
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                if (isset($this->session->username)){
                
                if($opsModel->newPassword($form->getValue('password'),$this->session->ops_id)) {
                // Send new password email
                    $resArr = $opsModel->findById($this->session->ops_id);
                                
                    $detailArr = array(
                    'firstname'=>$resArr->firstname,
                    'lastname'=>$resArr->lastname,
                    'email'=>$resArr->email,
                    'password'=>$form->getValue('password'),
                    'username'=> $user->username,
                );
               
                $alert = new Alerts();
                $alert->sendUpdatePasswordmail($detailArr,CURRENT_MODULE);  
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Your password was successfully changed',
                    )
                );
                unset($this->session->agent_id);
                unset($this->session->conf_code);
                unset($this->session->agent_code);
//                $this->_redirect('/profile/login');
                $this->_redirect($this->formatURL('/profile/login/'));
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
    
    
 
}