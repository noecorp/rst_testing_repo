<?php

class Alerts extends App_Model
{
    
    private $_error;

    public function sendAuthCode($userData, $module)
    {
  
        try {
           
           $sms = new App_SMS_SendSMS();           
           $m = new App_Mail_HtmlMailer();           
           //Convert object into array
           $userData = Util::objectToArray($userData);           
           //For Operation Module

            if($module == 'operation') {

                $host = App_DI_Container::get('ConfigObject')->operation->url ? App_DI_Container::get('ConfigObject')->operation->url : 'operation.shmart.in';//need  to place on config
                $userData['host'] = $host;
                //Is it allowed to send email                
                if(App_DI_Container::get('ConfigObject')->operation->loginauth->sendmail){
                   // echo "<pre>";print_r($userData);exit;
                    $m->sendAuthCode($userData);
                }
                
                //Is it allowed to send SMS                
                if(App_DI_Container::get('ConfigObject')->operation->loginauth->sendsms){
                    //$userData = Util::objectToArray($userData);
                    $sms->sendAuthSMS($userData);
                }

           } 
            //For Agent Module
            if($module == 'agent') {
                //Getting HOST from Config                
                $host = App_DI_Container::get('ConfigObject')->agent->url ? App_DI_Container::get('ConfigObject')->agent->url : 'agent.shmart.in';//need  to place on config                
                //$host = 'agent.shmart.in';//need  to place on config
                $userData['host'] = $host;
                //Is it allowed to send email                
                if(App_DI_Container::get('ConfigObject')->agent->loginauth->sendmail){
                    $m->sendAuthCode($userData);
                }                
                //Is it allowed to send SMS
                if(App_DI_Container::get('ConfigObject')->agent->loginauth->sendsms){
                   
                        $sms->sendAuthSMS($userData);
                       
                }
            }     
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }


    }
    
    
    
     public function sendAgentBalance($userData, $module)
    {
        
        try {
            //echo "<pre>";print_r($userData);exit;
            $sms = new App_SMS_SendSMS();           
            $m = new App_Mail_HtmlMailer();           
            //For Operation Module
            if($module == 'operation') {
                 //exit("In ==Sending Mail");
                //Getting HOST from Config
                //$host = App_DI_Container::get('ConfigObject')->operation->url ? App_DI_Container::get('ConfigObject')->operation->url : 'operation.shmart.in';//need  to place on config
                //$userData->host = $host;
                //Is it allowed to send email                
                if(App_DI_Container::get('ConfigObject')->operation->loadbalance->sendmail){
                    $m->sendAgentBalance($userData);
                }
                
                //Is it allowed to send SMS                
                if(App_DI_Container::get('ConfigObject')->operation->loadbalance->sendsms){                        
                    $sms->sendAgentBalanceSMS($userData);
                }
           } 
            
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }
    
    
    
     public function sendAgentRejectionMail($userData, $module)
    {
        
        try {
                          
                    $m = new App_Mail_HtmlMailer();             
               
                    $m->sendAgentRejection($userData);
                
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }

    public function sendCardholderUpdationEmail($emailData, $module)
    {
        
        try {
            //echo "<pre>";print_r($userData);exit;                      
            $m = new App_Mail_HtmlMailer();           
            //For Operation Module
            if($module == 'operation') {
                 //exit("In ==Sending Mail");
                //Getting HOST from Config
                //$host = App_DI_Container::get('ConfigObject')->operation->url ? App_DI_Container::get('ConfigObject')->operation->url : 'operation.shmart.in';//need  to place on config
                //$userData->host = $host;
                //Is it allowed to send email                
                if(App_DI_Container::get('ConfigObject')->operation->cardholder->sendupdatemail){
                    $m->sendCardholderUpdationEmail($userData);
                }                
              } 
            
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }
    
      public function sendCardholderBalance($userData, $module)
     {
        
        try {
            //echo "<pre>";print_r($userData);exit;
            $sms = new App_SMS_SendSMS();           
            $m = new App_Mail_HtmlMailer();           
            //For Operation Module
            if($module == 'agent') {
                
                if($userData['ecsStatus']==FLAG_SUCCESS){
                    if(App_DI_Container::get('ConfigObject')->cardholder->loadbalance->sendmail){
                        $m->sendCardholderBalance($userData);
                    }
                }
                
                //Is it allowed to send SMS                
                if(App_DI_Container::get('ConfigObject')->cardholder->loadbalance->sendsms){                        
                    $sms->sendCardholderBalanceSMS($userData);
                }
           } 
            
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }


    }

    
    public function sendAgentApproval($userData, $module)
    {
        
        try {
            
            $sms = new App_SMS_SendSMS();           
            $m = new App_Mail_HtmlMailer();           
           
            if($module == 'operation') {
                            
                if(App_DI_Container::get('ConfigObject')->system->notifications->agent_approved){
                                       
                    $m->sendAgentApprovalmail($userData);
                }
                
                             
                if(App_DI_Container::get('ConfigObject')->system->notifications->agent_approved){ 
                    
                    $sms->sendAgentApprovalSMS($userData);
                }
           } 
            
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }


    }
    public function sendAgentPassword($userData, $module)
    {
        
        try {
            
            $sms = new App_SMS_SendSMS();           
            $m = new App_Mail_HtmlMailer();           
           
            if($module == 'operation') {
                             
                if(App_DI_Container::get('ConfigObject')->operation->agentsignup->sendpasswordmail){
                    $admin_mail = App_DI_Container::get('ConfigObject')->system->notifications->recipients;
                  
                    $to_mail = $admin_mail->toArray();
                    
                    $userData['adminmail'] = $to_mail['0'];
                    
                    $m->sendAgentPasswordmail($userData);
                }
                
                             
                /*if(App_DI_Container::get('ConfigObject')->operation->agentsignup->sendpasswordsms){ 
                    
                    $sms->sendAgentPasswordSMS($userData);
                }*/
           } 
            
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }


    }
   
    
     public function sendUpdatePasswordmail($userData)
    {
        
        try {
            
            $sms = new App_SMS_SendSMS();           
            $m = new App_Mail_HtmlMailer();           
            $m->sendupdatePasswordmail($userData);
                
           } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }


    }
    
      public function beneficiaryenrollment($userData)
    {
        //echo '<pre>';print_r($userData);exit;
        try {
            
            $sms = new App_SMS_SendSMS();           
            $sms->sendbeneficiaryenrollment($userData);
                
           } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }


    }
    public function sendAgentMinMaxloadmail($userData)
    {
        
        try {
            
               
            $m = new App_Mail_HtmlMailer();           
            $m->sendAgentMinMaxload($userData);
                
           } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }


    }
     public function sendVerificationCode($userData, $module)
    {
       
        try {
            
            $sms = new App_SMS_SendSMS();           
                       
           
            if($module == 'operation') {
                             
             if(App_DI_Container::get('ConfigObject')->operation->agentsignup->sendverificationcodesms){ 
                    
                    $sms->sendVerificationcodeSMS($userData);
                }
           } 
            
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }


    }
    
    
    
      public function sendAgentEmailVerificationlink($userData, $module)
       {
        
        try {
            
               
            $m = new App_Mail_HtmlMailer();           
           
            if($module == 'operation') {
                             
                if(App_DI_Container::get('ConfigObject')->operation->agentsignup->sendpasswordmail){
                    //$admin_mail = App_DI_Container::get('ConfigObject')->operation->agentsignup->recipients;
                  
                    //$to_mail = $admin_mail->toArray();
                    
                   // $userData['adminmail'] = $to_mail['0'];
                    
                    $m->sendAgentEmailverificationmail($userData);
                }
                
               
           } 
            
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }


    }
    
    public static function generateAuthCode($length='')
    {
        if($length =='4') {
            return rand(1111,9999);                        
        } else {
            return rand(111111,999999);            
        }

    }
    
    
    public static function ValidateAuth($code, $tablename) {
        $user = Zend_Auth::getInstance()->getIdentity();
        $baseUserModel = new BaseUser();
//        echo $code, $tablename;
//        print '<pre>';        print_r($user);exit;
        if ($user->status == STATUS_INACTIVE || $user->status == STATUS_BLOCKED) {
            return 'inactive';
        } else if ($user->status == STATUS_LOCKED) {
            return 'locked';
        } else {
            if ($tablename == DbTable::TABLE_AGENTS) {
                $authCodeFromDb = $baseUserModel->getAgentAuthcodeFromDb($user->id);
            } elseif  ($tablename == DbTable::TABLE_CUSTOMER_MASTER || $tablename == DbTable::TABLE_BANK_USER) {//Customer Portal
            		$authCodeFromDb = $baseUserModel->getAuthcodeFromDb($user->id, $tablename);
            } elseif  ($tablename == DbTable::TABLE_CORPORATE_USER ) {//Customer Portal
            		
                $authCodeFromDb = $baseUserModel->getAuthcodeFromDb($user->id, $tablename);    
            } else {
                $authCodeFromDb = $baseUserModel->getAuthcodeFromDb($user->id);
            }

        //echo $authCodeFromDb; exit;

            if (isset($authCodeFromDb)) {

                if ($authCodeFromDb == $code) {
                    $user->authenticated = true;
                    return 'correct';
                } else {
                    return false;
                }
            } else {

                return 'not_allowed';
            }
        }
    }

    public function getErrorMsg()
    {
        return $this->_error;        
    }
    
    private function setErrorMsg($msg)
    {
        $this->_error = $msg;
    }
    
    
    
    public function sendCardholderAuth($userData, $module ,$resend = FALSE)
    {
        
        try {
                // mobile duplicacy check
                $session = new Zend_Session_Namespace('App.Agent.Controller');    
                $mobObj = new Mobile();
                $mobNo = isset($userData['mobile1'])?$userData['mobile1']:'';               
                if ($resend){
                    $userData['auth_code'] = $session->cardholder_auth;
                }
                else {
                    $userData['auth_code'] = Alerts::generateAuthCode();
                }
                    
                                        
                if($mobNo!='' && $userData['mobile_number_old']!=$mobNo) {
                 try {                
                        $mobCheck = $mobObj->checkDuplicate($mobNo,'cardholder');                
                   } catch (Exception $e ) {
                       App_Logger::log($e->getMessage(), Zend_Log::ERR);
                       $msg = $e->getMessage();
                     throw new Exception ($msg);
                       //return $e->getMessage();
                   }   
                } else if($mobNo==''){ throw new Exception ("No mobile number provided"); }                 
                
                $sms = new App_SMS_SendSMS();                                 
                // For Cardholder Module
                if($module == 'cardholder') {
                    // Sending sms to cardholer for auth code              
                    if(App_DI_Container::get('ConfigObject')->cardholder->registerauth->sendsms){
                        $sms->sendCardholderAuth($userData);                         
                        $session = new Zend_Session_Namespace('App.Agent.Controller');                         
                        $session->cardholder_auth = $userData['auth_code'];                        
                    }              
                } else if($module == 'operation') {
                    // Sending sms to cardholer for auth code              
                    if(App_DI_Container::get('ConfigObject')->cardholder->registerauth->sendsms){
                        $sms->sendCardholderAuth($userData);                        
                        $session = new Zend_Session_Namespace('App.Operation.Controller');             
                        $session->cardholder_auth = $userData['auth_code'];                        
                    }  
                    
                }     
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception ($e->getMessage());
            //return false;
        }
    }
    
    /* sendTermsConditionAuth() that function will send the terms n condition code to user
     * will accept the userData and module name
     */
    
    public function sendTermsConditionAuth($userData, $module)
    {
       
       
        
        try {                
                $sms = new App_SMS_SendSMS();
                $m = new App_Mail_HtmlMailer(); 
                
                // For Cardholder Module
                if($module == 'cardholder') {   
                    
                    // Sending sms to cardholer for terms n condition auth code              
                    if(App_DI_Container::get('ConfigObject')->cardholder->termsconditionsauth->sendsms){
                        $sms->sendTermsConditionAuth($userData);                                                
                    }                      
                    
                    // Sending sms to cardholer for terms n condition auth code              
                    if(App_DI_Container::get('ConfigObject')->cardholder->termsconditionsauth->sendmail){
                        $m->sendTermsConditionAuth($userData);                       
                    }    
                    
                    $session = new Zend_Session_Namespace('App.Agent.Controller');                        
                    $session->termscondition_auth = $userData['termsconditions_auth'];
            }            
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception ($e->getMessage());
            //return false;
        }
    }
    
    
    public function sendAgentFundRequest($userData, $module){
        
         try {                
                
                $m = new App_Mail_HtmlMailer(); 
                
                // For agent Module
                if($module == 'agent') {  
                    $emails = App_DI_Container::get('ConfigObject')->system->notifications->recipients;
                    $userData['email'] = $emails->toArray();
                }                     
                   
                    // Sending email to ops for intimation            
                    if(App_DI_Container::get('ConfigObject')->agent->fundrequest->sendmail){
                        $m->sendAgentFundRequest($userData);                       
                    }
                    return true;
                        
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception ($e->getMessage());
            //return false;
        }
    }
    
    
     public function sendOperationFundResponse($userData, $module){
        
         try {                
                
                $m = new App_Mail_HtmlMailer(); 
                
                // For agent Module
                if($module == 'operation') {  
                    $emails = App_DI_Container::get('ConfigObject')->system->notifications->recipients;
                    $userData['email'] = $emails->toArray();
                }                     
                   
                    // Sending email to ops for intimation        
                    if(App_DI_Container::get('ConfigObject')->operation->fundresponse->sendmail){
                        $m->sendOperationFundResponse($userData);                       
                    }
                    return true;
                        
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception ($e->getMessage());
            //return false;
        }
    }
    
    
    
       public function sendCHFundLoadAuth($userData, $module,$resend = FALSE)
    {
        
        try {
                // mobile duplicacy check
                $session = new Zend_Session_Namespace('App.Agent.Controller');   
                $mobObj = new Mobile();
                $mobNo = isset($userData['mobile1'])?$userData['mobile1']:''; 
                 if ($resend){
                    $userData['auth_code'] = $session->ch_fund_load_auth;
                }
                else
                {
                $userData['auth_code'] = Alerts::generateAuthCode();
                }                      
                if($mobNo==''){ throw new Exception ("No mobile number provided"); }                 
                
                $sms = new App_SMS_SendSMS();                                 
                // For Cardholder Module
                if($module == 'cardholder') {
                    // Sending sms to cardholer for fund load auth code              
                    if(App_DI_Container::get('ConfigObject')->cardholder->loadbalance->sendsms){
                    

                        
                        $userData['smsMessage'] = 'Dear Customer, Please provide the authorization code '. $userData['auth_code'].' to process a credit of '.$userData['currency'].' '.$userData['amount'].' in your '.$userData['account_name'] .'.' ;
                       
                        $sms->sendCardholderBalanceSMS($userData);                         
                        $session = new Zend_Session_Namespace('App.Agent.Controller');                         
                        $session->ch_fund_load_auth = $userData['auth_code'];                        
                    }              
                } 
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception ($e->getMessage());
            //return false;
        }
    }
    
    
    public function sendCardholderLoadFund($userData, $module)
     {
        
        try {
            //echo "<pre>";print_r($userData);exit;
            $sms = new App_SMS_SendSMS();           
            if($module == 'agent') {
                
                //Is it allowed to send SMS                
                if(App_DI_Container::get('ConfigObject')->cardholder->reloadfund->sendsms){                        
                    $sms->sendCardholderBalanceSMS($userData);
                }
           } 
            
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }


    }
    
    public function sendAgentCHLoadFund($userData, $module)
     {
        
        try {
            //echo "<pre>";print_r($userData);exit;
            //$sms = new App_SMS_SendSMS();
            $m = new App_Mail_HtmlMailer(); 
            if($module == 'agent') {
                
                //Is it allowed to send SMS                
               /* if(App_DI_Container::get('ConfigObject')->agent->cardholder->reloadfund->sendsms){                        
                    $sms->sendCardholderBalanceSMS($userData);
                }*/
                 $m->sendReloadEmailToAgent($userData);       
           } 
            
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }


    }
    
    
    
      public function sendLowBalanceMail($userData, $module)
     {
        
        try {
            $m = new App_Mail_HtmlMailer();           
            //For Operation Module
            if($module == 'agent') {                
                //Is it allowed to send email                
                if(App_DI_Container::get('ConfigObject')->agent->minbal->sendmail){
                    $m->sendAgentLowBalanceMail($userData);
                }
           } 
           
           if($module == 'operation') {                
                //Is it allowed to send email                
                if(App_DI_Container::get('ConfigObject')->operation->minbal->sendmail){
                    $emails = App_DI_Container::get('ConfigObject')->system->notifications->recipients;
                    $userData['ops_email'] = $emails->toArray();
                    $m->sendOpsLowBalanceMail($userData);
                }
           } 
            
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }
    
        public function sendUpdateNewMobileAuth($userData, $module)
        {
       
        try {                
                $sms = new App_SMS_SendSMS();
                
                // For Cardholder Module
                    
                    $sms = new App_SMS_SendSMS();                                 
                    // For Cardholder Module
                    if($module == 'operation') {
                        // Sending sms to cardholer for auth code              
                        $userData['auth_code'] = self::generateAuthCode();
                        if(App_DI_Container::get('ConfigObject')->helpdesk->updatemobileauth->sendsms){
                            $sms->sendUpdateNewMobileAuth($userData);                         
                            $session = new Zend_Session_Namespace('App.Operation.Controller');                         
                            $session->update_mobile_auth = $userData['auth_code'];  
                            //echo $session->update_mobile_auth.'-----';
                        }              
                    }                   
                    
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception ($e->getMessage());
            //return false;
        }
    }
    
    
     public function sendCHMobileChange($userData, $module)
     {
        try {
            //echo "<pre>";print_r($userData);exit;
            $sms = new App_SMS_SendSMS();           
            //For Operation Module
            if($module == 'operation') {
                //Is it allowed to send SMS                
                if(App_DI_Container::get('ConfigObject')->operation->chchangemobile->sendsms){                        
                    $sms->sendCHMobileChangeSMS($userData);
                }
           } 
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }
        public function sendConfirmationCode($userData, $module)
    {
  
        try {
           
           $sms = new App_SMS_SendSMS();           
           $m = new App_Mail_HtmlMailer();           
           //Convert object into array
           $userData = Util::objectToArray($userData);           
           //For Operation Module
           //echo "<pre>";print_r($userData);exit;
            if($module == 'operation') {

                  //echo "<pre>";print_r($userData);exit;
                    $m->sendConfCode($userData);
                    $sms->sendConfSMS($userData);
              

           } 
            //For Agent Module
            if($module == 'agent') {
                 
               //echo "<pre>";print_r($userData);exit;
                    $m->sendConfCode($userData);
               
                    
                    $sms->sendConfSMS($userData);
               
            }     
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }


    }
    
    
     public function sendLowCRNAlert($crnData)
    {
        
        try {
            $m = new App_Mail_HtmlMailer();           
                        
            if(App_DI_Container::get('ConfigObject')->cron->low_crn->mail){  //Is it allowed to send email 
                $emails = App_DI_Container::get('ConfigObject')->system->notifications->recipients;
                $crnData['email'] = $emails->toArray();
                $m->sendLowCRNAlert($crnData);
            }
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }
    
    /* sendFailedMVCRegistration funciton will send the failed mvc regisration information to ops
     *  it will expects the cardholder name, mobile, crn etc....
     */
    public function sendFailedMVCRegistration($param)
    {
        
        try {
            $m = new App_Mail_HtmlMailer();           
            if(App_DI_Container::get('ConfigObject')->mvc->failed_registration->sendmail){  //Is it allowed to send email 
                $emails = App_DI_Container::get('ConfigObject')->system->notifications->recipients;
                $param['email'] = $emails->toArray();
                $m->sendFailedMVCRegistration($param);
            }
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }
 
    /* sendRemitterAuth () will send remitter auth for mobile verification
     * it will expect the mobile number and country code , module name
     */
     public function sendRemitterAuth($data, $module, $resend = FALSE)
    {
        
        try {
                // mobile duplicacy check
                $session = new Zend_Session_Namespace('App.Agent.Controller');    
                $mobObj = new Mobile();
                $mobileNo = isset($data['mobile1'])?$data['mobile1']:'';   
                
                // assigning old auth code if exists already
                if ($resend)
                    $data['auth_code'] = $session->remitter_auth;
                else 
                    $data['auth_code'] = Alerts::generateAuthCode();
                    
                        
                if($mobileNo!='' && $data['mobile_old']!=$mobileNo) {
                    try {                
                           $mobCheck = $mobObj->checkRemitterMobileDuplicate($mobileNo);                
                      } catch (Exception $e ) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        throw new Exception ($e->getMessage());
                      }   
                } else if($mobileNo==''){ throw new Exception ("No mobile number provided"); }                 
                
                $sms = new App_SMS_SendSMS();                                 
                
                // For remitter Module
                if($module == 'remitter') {
                    
                    // Sending sms to remitter for auth code              
                    if(App_DI_Container::get('ConfigObject')->remitter->registerauth->sendsms){
                        $sms->sendRemitterAuth($data);                         
                        $session->remitter_auth = $data['auth_code'];                        
                    }              
                }    
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception ($e->getMessage());
            //return false;
        }
    }
     /*
      * Send Remitter Verification code during remitter search
      */
    public function sendRemitterVerificationAuth($userData,$resend = FALSE)
    {
        
        try {
                
                $session = new Zend_Session_Namespace('App.Agent.Controller'); 
                
                $remitters = new Remit_Boi_Remitter();
                $mobNo = isset($userData['mobile1'])?$userData['mobile1']:'';               
                if ($resend){
                    $userData['auth_code'] = $session->remitter_search_auth;
                }
                else {
                    $userData['auth_code'] = Alerts::generateAuthCode();
                }
                    
                                        
                if($mobNo!='' ) {
                 try {                
                       $mobCheck = $remitters->getRemitter($mobNo); 
                       $session->remitter_mobile_number = $mobNo;
                   } catch (Exception $e ) {
                       App_Logger::log($e->getMessage(), Zend_Log::ERR);
                       $msg = $e->getMessage();
                     throw new Exception ($msg);
                       //return $e->getMessage();
                   }   
                } else if($mobNo==''){ throw new Exception ("No mobile number provided"); }                 
                
                $sms = new App_SMS_SendSMS();                                 
                
               
                    // Sending sms to cardholer for auth code              
                   
                        $sms->sendRemitterVerifyAuth($userData);                         
                        $session = new Zend_Session_Namespace('App.Agent.Controller');                         
                        $session->remitter_search_auth = $userData['auth_code'];                        
                                
                
                    
              
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception ($e->getMessage());
            //return false;
     }
    


    }
    /*
     *   Send Add Beneficiary Auth code
     * 
     */
   
      public function sendAddbeneficiaryAuth($userData,$resend = FALSE)
    {
        
        try {
                $pattern  = '/[^a-z\s]/i';
               if($userData['name'] == '' ){
                 throw new Exception ("Please enter beneficiary name");   
                }
                else if(preg_match($pattern, $userData['name'])){
                 throw new Exception ("Only alphabets are allowed in beneficiary name");   
                }
                else if($userData['nick_name'] == '' ){
                 throw new Exception ("Please enter beneficiary nick name");   
                }
                else if(preg_match($pattern, $userData['nick_name'])){
                 throw new Exception ("Only alphabets are allowed in beneficiary nick name");   
                }
                else if($userData['bank_name'] == ''){
                 throw new Exception ("Please enter beneficiary bank name");   
                }
                
                else if($userData['ifsc_code'] == ''){
                 throw new Exception ("Please enter beneficiary bank IFSC code");   
                }
               else if($userData['bank_account_number'] == ''){
                 throw new Exception ("Please enter beneficiary bank account number");   

                }
                $session = new Zend_Session_Namespace('App.Agent.Controller'); 
                
                $mobNo = isset($userData['mobile1'])?$userData['mobile1']:'';               
                if ($resend){
                    $userData['auth_code'] = $session->beneficiary_auth;
                }
                else {
                    $userData['auth_code'] = Alerts::generateAuthCode();
                }
                    
                                        
                if($mobNo!='' ) {
                 try {                
                       $sms = new App_SMS_SendSMS();                                 
                    // Sending sms to Remitter with beneficiary details              
                   
                        $sms->sendAddBeneficairyAuth($userData);                         
                        $session = new Zend_Session_Namespace('App.Agent.Controller');                         
                        $session->beneficiary_auth = $userData['auth_code'];                        
                                
                   } catch (Exception $e ) {
                       App_Logger::log($e->getMessage(), Zend_Log::ERR);
                       $msg = $e->getMessage();
                       throw new Exception ($msg);
                   }   
                } else if($mobNo==''){ throw new Exception ("No mobile number provided"); }                 
                
                
                
                    
              
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception ($e->getMessage());
     }
    


    }

    
    /* sendRemitterRegistration () will send success updation to remitter
     * it will expect the mobile number and country code , module name
     */
     public function sendRemitterRegistration($data, $module)
    {
        
        try {
                // mobile duplicacy check
                $session = new Zend_Session_Namespace('App.Agent.Controller');    
                $mobObj = new Mobile();
                $mobileNo = isset($data['mobile1'])?$data['mobile1']:'';   
                
                if($mobileNo=='') {
                   throw new Exception ("No mobile number provided");
                }                 
                
                $sms = new App_SMS_SendSMS();                                 
                
                // For remitter Module
                if($module == 'remitter') {
                    
                    // Sending sms to remitter for auth code              
                    if(App_DI_Container::get('ConfigObject')->remitter->register->sendsms){
                        $sms->sendRemitterRegistration($data);                         
                    }              
                }    
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception ($e->getMessage());
            //return false;
        }
    }
     /*
     *   Send Fund transfer Auth code
     * 
     */
   
      public function sendfundtransferAuth($userData,$resend = FALSE)
    {
        
        try {
                  
                $session = new Zend_Session_Namespace('App.Agent.Controller'); 
                
                $mobNo = isset($userData['mobile1'])?$userData['mobile1']:'';  
                $amt = isset($userData['amount'])?$userData['amount']:''; 
                if ($amt == ''){
                   throw new Exception ("Please enter valid amount for fund transfer");  
                }
                if ($resend){
                    $userData['auth_code'] = $session->fundtransfer_auth;
                }
                else {
                    $userData['auth_code'] = Alerts::generateAuthCode();
                    $session->fundtransfer_amount = $userData['amount'];    
                }
                    
                                        
                if($mobNo!='' ) {
                 try {                
                       $sms = new App_SMS_SendSMS();                                 
                    // Sending sms to Remitter with beneficiary details              
                   
                        $sms->sendfundtransferAuth($userData);                         
                        $session = new Zend_Session_Namespace('App.Agent.Controller');                         
                        $session->fundtransfer_auth = $userData['auth_code'];                        
                        
                   } catch (Exception $e ) {
                       App_Logger::log($e->getMessage(), Zend_Log::ERR);
                       $msg = $e->getMessage();
                     throw new Exception ($msg);
                   }   
                } else if($mobNo==''){ throw new Exception ("No mobile number provided"); }                 
                
                
                
                    
              
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception ($e->getMessage());
     }
    


    }
    
    
    public static function generate4DigitCode()
    {
        return rand(1000,9999);
    }
    
}