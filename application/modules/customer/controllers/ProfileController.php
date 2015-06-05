<?php
/**
 * Allows user to manage their profile data
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class ProfileController extends App_Customer_Controller
{
    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init(){
         $this->session = $this->_getSession();
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
        $agentModel = new CustomerMaster();
        $agentId = isset($user->id) ? $user->id : 0;
        //$count = $agentModel->getRegisteredCardholderCount($agentId);
        //$this->view->cardholderRegistedCount = $count;
        
        //getting concerned stats of agent for dashboard
        
        //$agentStats = $dashboard->agentDashboardStats();
        //$this->view->agentStats = $agentStats;
        
        //print $count.'**';
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            print $e->getMessage();
        }
    }
    
   
     public function resendAuthcodeAction(){
     
        // use the login layout
        $this->_helper->layout()->setLayout('login');
        //$this->session->resent = FALSE;
        //$this->session->notresent = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $userModel = new CustomerMaster();
        $dataArr = $userModel->findDetails($user->username,DbTable::TABLE_CUSTOMER_MASTER);
        $dataArr['username'] =  $user->username;
       
        try {
         $m = new App\Messaging\MVC\Axis\Customer();
         $flg = $m->authCode($dataArr);
         $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Authcode resent successfully',
                    )
                );
        } catch(Exception $e){
            echo '<pre>';print_r($e);exit;
                     App_Logger::log($e->getMessage(), Zend_Log::ERR);
                     $Msg = $e->getMessage();
                      $this->_helper->FlashMessenger(
                                 array(
                                      'msg-error' => $Msg,
                                 )
                             );
                }
               
      
//        $this->_redirect('/profile/authcode/');
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
                
                $userModel = new CustomerMaster();  
                $userExists = $userModel->chkLoginCredentials($form->getValue('username'), $form->getValue('password'));
                $id = $userModel->getUserIdByUsername($form->getValue('username'),DbTable::TABLE_CUSTOMER_MASTER);
               
                if($userExists) {
                $rowStatus = $userModel->getCustomerStatus($form->getValue('username'));
                
                //echo "<pre>";print_r($rowStatus);exit;
                if(!empty($rowStatus)){ 
                    //email_verification_status, email_verification_id,
                    if($rowStatus['is_portal_access'] == STATUS_ACTIVE) {
                        
                        // enroll_status
                        if($rowStatus['status'] == STATUS_ACTIVE) {
                            // status check
                            
                            /*if($rowStatus['status'] == STATUS_UNBLOCKED) {
                                if(App_DI_Container::get('ConfigObject')->session->ssn_implementation) {
                                    if(!$sessionModel->loginSession(array('user_id' => $rowStatus['id']))){
                                        $this->_redirect($this->formatURL('/profile/login?sess=err'));
                                    }
                                }*/
                                
                                $res = $userModel->login($form->getValue('username'), $form->getValue('password'));
                                //echo "<pre>";print_r($res);exit();
                                if($res){
                                 
                                    $session = $this->_getSession();
                                    $request = unserialize($session->request);
                                    //echo "<pre>";print($request);exit;
                                    if(!empty($request)){
                                       $previousUri = $request->getRequestUri();
                                        $this->_redirect($previousUri); 
                                    }else{
                                    
                                       // loggin login at step 1
                                    $updateAttempts = $userModel->removenumLoginAttempts($id['id'],DbTable::TABLE_CUSTOMER_MASTER);
                                            //Insert into login log
                             $loginLogArr = array(
                            'portal'=> MODULE_CUSTOMER,
                            'cardholder_id' => $id['id'],
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
                            //} // status unblocked ends
                            //else*/ 
                            //{
//                                if($rowStatus['status'] == STATUS_LOCKED){
//                                    $this->view->errType = 'locked';
//                                }
//                                elseif($rowStatus['status'] == STATUS_BLOCKED){
//                                    $this->view->errType = 'blocked';
//                                }
//                                else {
//                                    $this->view->errType = 'incorrectuser';
//                                }
                                   //Insert into login log
                           $loginLogArr = array(
                            'portal'=> MODULE_CUSTOMER,
                            'cardholder_id' => $rowStatus['id'],
                            'datetime_login_step1'=> new Zend_Db_Expr('NOW()'),
                            'username' => $form->getValue('username'),
                            'comment_username' => STATUS_SUCCESS.':status='.$rowStatus['status'],
                            'comment_password' => STATUS_SUCCESS,
                            'session_id' => $sessionId);

                            $userModel->insertLoginLog($loginLogArr); 
                            //}
                       
                  
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
                            'portal'=> MODULE_CUSTOMER,
                            'customer_id' => $rowStatus['id'],
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
                            'portal'=> MODULE_CUSTOMER,
                            'customer_id' => $rowStatus['id'],
                            'datetime_login_step1'=> new Zend_Db_Expr('NOW()'),
                            'username' => $form->getValue('username'),
                            'comment_username' => STATUS_SUCCESS.':email_status= '.$rowStatus['email_verification_status'],
                            'comment_password' => STATUS_SUCCESS,
                            'session_id' => $sessionId);
                            $userModel->insertLoginLog($loginLogArr);
                  
                        $this->view->errType = 'invalidportalaccess';
                    }
                    
                } // rowStatus !empty ends
                else {
                    $this->view->errType = 'incorrectuser';
                }
                 
                
                } // if userExists ends
                else {
                    
                    
                   
                    if($id['id'] > 0) {
                        $numAttempts = $userModel->updatenumLoginAttempts($id['id'],DbTable::TABLE_CUSTOMER_MASTER);

                        $config = App_DI_Container::get('ConfigObject');
                        $numAllowed = $config->system->login->attempts->allowed;
                        if($numAttempts >= $numAllowed){
                            // Login Step 1 status changed to locked

                            $data = array('cardholder_id'=>$id['id'],'status_old' => STATUS_UNBLOCKED,'status_new' => STATUS_LOCKED,'remarks' => 'Cardholder locked after '.$numAllowed.' attempts at Step 1');
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
                            'portal'=> MODULE_CUSTOMER,
                            'cardholder_id' => $id['id'],
                            'datetime_login_step1'=> new Zend_Db_Expr('NOW()'),
                            'username' => $form->getValue('username'),
                            'comment_username' => STATUS_SUCCESS.':status='.$id['status'],
                            'comment_password' => STATUS_FAILURE,
                            'session_id' => $sessionId);
                            $userModel->insertLoginLog($loginLogArr);
                    }
                    else {
                         $loginLogArr = array('portal'=> MODULE_CUSTOMER,'datetime_login_step1'=> new Zend_Db_Expr('NOW()'),
                            'username' => $form->getValue('username'),
                            'comment_username' => STATUS_FAILURE,
                            'session_id' => $sessionId);
                         $userModel->insertLoginLog($loginLogArr);
                        $this->view->errType = 'incorrectuser';
                    }
                }
            //exit('here');
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
        $userModel = new CustomerMaster();
        $session = $this->_getSession();
        $user = Zend_Auth::getInstance()->getIdentity();
        if(App_DI_Container::get('ConfigObject')->session->ssn_implementation) {
            $sessionModel = new Session();
            $sessionModel->logoutSession();
        }
        
        $logindata =  array('portal'=> MODULE_CUSTOMER,'cardholder_id' => $user->id,'datetime_logout' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId);  
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
        $session = $this->_getSession();
       
        $this->title = 'Login Authcode';
        $user = Zend_Auth::getInstance()->getIdentity();
       
        // use the login layout
         $this->_helper->layout()->setLayout('login');
         $userModel = new CustomerMaster();
         $sessionModel = new Session();
        $id = $userModel->getUserIdByUsername($user->username,DbTable::TABLE_CUSTOMER_MASTER);
       
        $form = new AuthcodeForm();
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
                $alert = new Alerts();
                $a = $form->getValues();
              
                $res = $alert->ValidateAuth($a['authcode'],DbTable::TABLE_CUSTOMER_MASTER);
                if($this->session->resent){
                     $this->view->resent = TRUE;
                }
                else if ($this->session->notresent){
                   $this->view->notresent = TRUE; 
                }
                if($res == 'locked'){
                     $this->view->locked = TRUE;
                     $logindata =  array('portal'=> MODULE_CUSTOMER,'cardholder_id' => $user->id,'datetime_login_step2' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId,
                          'comment_auth' => STATUS_SUCCESS.':status='.STATUS_LOCKED);  
                     $userModel->insertLoginLog($logindata);
                      
                }
              else if($res == 'inactive'){
                     $this->view->inactive = TRUE;
                     $logindata =  array('portal'=> MODULE_CUSTOMER,'customer_id' => $user->id,'datetime_login_step2' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId,
                          'comment_auth' => STATUS_SUCCESS.':status='.STATUS_INACTIVE);  
                     $userModel->insertLoginLog($logindata);
                     
                }
                
                else if($res != 'correct'){
                     $numAttempts = $userModel->updatenumLoginAttempts($user->id,DbTable::TABLE_CUSTOMER_MASTER);
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
                      $logindata =  array('portal'=> MODULE_CUSTOMER,'customer_id' => $user->id,'datetime_login_step2' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId,
                          'comment_auth' => STATUS_FAILURE);  
                   
                    $userModel->insertLoginLog($logindata);
                }                 
                  else{
                    if(App_DI_Container::get('ConfigObject')->session->ssn_implementation) {  
                        if(!$sessionModel->validateLoginSession()){
                            $this->_redirect($this->formatURL('/profile/login?sess=err'));
                        }
                    }
                    $updateAttempts = $userModel->removenumLoginAttempts($user->id,DbTable::TABLE_CUSTOMER_MASTER);
                    
                    // updating last login in agent table
                    $agentArr = array('last_login'=>new Zend_Db_Expr('NOW()'));
                    $updLastLogin = $userModel->editCustomerMaster($agentArr, $user->id);
                    
                    
                    // loggin at step 2
                    $logindata =  array('portal'=> MODULE_CUSTOMER,'cardholder_id' => $user->id,'datetime_login_step2' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId,'comment_auth' =>STATUS_SUCCESS);  
        
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
                            $daysToAdd = App_DI_Container::get('ConfigObject')->customer->password->expirydays;
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
        $agentModel = new CustomerMaster();
        
           
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                
                $isPasswordDuplicate = true;
                /**** checking new password with last 4 passwords for non duplicacy of password ****/
                try{
                     $passwordParam = array('cardholder_id'=>$user->id, 'password'=>BaseUser::hashPassword($form->getValue('password')));
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
                                        'cardholder_id'=>$user->id,
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
                $m = new App\Messaging\MVC\Axis\Customer();
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
    public function forgotPasswordAction(){
        $this->title = 'Forgot Password';
        $form = new ForgotPasswordForm();
        // use the withoutlogin layout
        $this->_helper->layout()->setLayout('withoutlogin');
        $agentModel = new CustomerMaster();
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                exit('Broken Code');
                //$res = $agentModel->checkAgentDetails($form->getValues());
                
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
                $this->session->customer_id = $res['id'];
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
        $agentModel = new CustomerMaster();
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
        
       
        $form = new NewPasswordForm();
        $agentModel = new CustomerMaster();
        // use the withoutlogin layout
        $this->_helper->layout()->setLayout('withoutlogin');
           
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                if (isset($this->session->customer_id)){
                
                if($agentModel->newPassword($form->getValue('password'),$this->session->customer_id)) {
                    // Send new password email
                    $resArr = $agentModel->findById($this->session->customer_id);
                                      
                    $detailArr = array(
                    'first_name'=>$resArr->first_name,
                    'last_name'=>$resArr->last_name,
                    'email'=>$resArr->email,
                    'password'=>$form->getValue('password'),
                    //'agent_code'=> $this->session->agent_code,
                );
                
                $alert = new Alerts();
                $alert->sendUpdatePasswordmail($detailArr,CURRENT_MODULE);
                
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Your password was successfully changed',
                    )
                );
                unset($this->session->customer_id);
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
        
//        $user = Zend_Auth::getInstance()->getIdentity();
//        $agentId = $user->id;
//        $objAgent = new AgentBalance(); 
//        try {
//              $agentBalance = $objAgent->getAgentActiveBalance($agentId);
//         
//         } catch (Exception $exc) {
//                echo $exc->getMessage();
//         }
//        
//        // Get our form and validate it
//        //$form = $this->getForm();                       
//        $this->view->form = $form; 
        $this->view->balance = Util::numberFormat($agentBalance);      
    }

    
        public function walletsAction(){
        
            $this->view->balance = '200';      
        }


        
}