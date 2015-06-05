<?php
namespace App\Messaging\MVC\Axis;

/**
 * Description of Operation
 *
 * @author Vikram
 */
class Operation extends \App\Messaging\MVC\Axis {

    //put your code here

    public function __construct() {
        parent::__construct(__NAMESPACE__);
    }

    public function authCode($userData) {
        $userData = \Util::objectToArray($userData) ;
        $this->setTemplate(__FUNCTION__);
        try {
            $config = \App_DI_Container::get('ConfigObject');
            
            if (\App_DI_Container::get('ConfigObject')->operation->loginauth->sendmail) {
                $this->getMail()->addTo($userData['email']);
                $this->getMail()->setSubject('Authorization Code');
                $this->getMail()->setParam('firstname',$userData['firstname']);
                $this->getMail()->setParam('lastname',$userData['lastname']);
                $this->getMail()->setParam('user_ip',\Util::getIP());
                $this->getMail()->setParam('auth_code',$userData['auth_code']);
                $this->getMail()->setParam('date_time',\Util::getCurrDateTime());
                $this->getMail()->setParam('login_attempts',$config->system->login->attempts->allowed);
                $flgMail = $this->sendMail();
//                if(!$flgMail) {
//                    $this->setError($this->getMail()->getError());
//                }
            }

            //Is it allowed to send SMS                
           if (\App_DI_Container::get('ConfigObject')->operation->loginauth->sendsms) {
                $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                $this->getSMS()->setParam('mobile', $userData['mobile1']);
                $flgSms = $this->sendSMS();
//                if(!$flgSms) {
//                    print 'Error :'. $this->getSMS()->getError();
//                }
             
            }
         
            
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    public function  confCode($userData) {
        $this->setTemplate(__FUNCTION__);
        try {
            
                $this->getMail()->addTo($userData['email']);
                $this->getMail()->setSubject('Confirmation Code');
                $this->getMail()->setParam('firstname',$userData['first_name']);
                $this->getMail()->setParam('lastname',$userData['last_name']);
                $this->getMail()->setParam('user_ip',\Util::getIP());
                $this->getMail()->setParam('conf_code',$userData['conf_code']);
                $flgMail = $this->sendMail();

            

            //Is it allowed to send SMS                
                $this->getSMS()->setParam('conf_code', $userData['conf_code']);
                $this->getSMS()->setParam('mobile', $userData['mobile1']);
                $flgSms = $this->sendSMS();

             
         
            
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }
    
    
     public function  agentBalance($userData) {
        $this->setTemplate(__FUNCTION__);
        try {
          
            if (\App_DI_Container::get('ConfigObject')->operation->loadbalance->sendmail) {
                $this->getMail()->addTo($userData['agent_email']);
                $this->getMail()->setSubject($userData['email_subject']);
                $this->getMail()->setParam('agent_name',$userData['agent_name']);
                $this->getMail()->setParam('on_date',\Util::getCurrDateTime(FALSE));
                $this->getMail()->setParam('operation_name',$userData['operation_name']);
                $this->getMail()->setParam('amount',$userData['amount']);
                $this->getMail()->setParam('response_status',isset($userData['response_status'])?$userData['response_status']:'');
                $this->getMail()->setParam('new_balance',isset($userData['new_balance'])?$userData['new_balance']:'');
                $this->getMail()->setParam('transaction_date',$userData['transaction_date']);
                $flgMail = $this->sendMail();

            }

            //Is it allowed to send SMS                
           if (\App_DI_Container::get('ConfigObject')->operation->loadbalance->sendsms) {
                $this->getSMS()->setParam('response_status', isset($userData['response_status'])?$userData['response_status']:'');
                $this->getSMS()->setParam('on_date', \Util::getFormattedDate());
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('new_balance', isset($userData['new_balance'])?$userData['new_balance']:'');
                $this->getSMS()->setParam('mobile', $userData['mobile1']);
                $this->sendSMS();

             
            }
         
            
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }
    
     /*
 *  Send Auth code on cardholder signup
 */ 
    public function cardholderAuth($userData, $module ,$resend = FALSE)
    {
         $this->setTemplate(__FUNCTION__);
       
        try { 
                // mobile duplicacy check
                $session = new \Zend_Session_Namespace('App.Operation.Controller');    
                $mobObj = new \Mobile();
                $mobNo = isset($userData['mobile1'])?$userData['mobile1']:'';               
                if ($resend){
                    $userData['auth_code'] = $session->cardholder_auth;
                }
                else {
                    $userData['auth_code'] = $this->generateRandom6DigitCode();
                }
                  
                                        
                if($mobNo!='' && $userData['mobile_number_old']!=$mobNo) {
                 try {                
                        $mobCheck = $mobObj->checkDuplicate($mobNo,'cardholder');                
                   } catch (\Exception $e ) {
                       \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
                       $msg = $e->getMessage();
                     throw new \Exception ($msg);
                       //return $e->getMessage();
                   }   
                } else if($mobNo==''){ throw new \Exception ("No mobile number provided"); }                 
                
             
                    // Sending sms to cardholer for auth code              
                    if(\App_DI_Container::get('ConfigObject')->cardholder->registerauth->sendsms){
                        $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                        $this->getSMS()->setParam('mobile', $userData['mobile1']);
                        $this->getSMS()->setParam('product_name', $userData['product_name']);
                        $flgSms = $this->sendSMS();                     
                        $session->cardholder_auth = $userData['auth_code'];                        
                    }  
                    
                
                
                        
            
        } catch(\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception ($e->getMessage());
            //return false;
        }
    }
    
    public function cardholderMobileChange($userData)
     {
        $this->setTemplate(__FUNCTION__);
        try {
                //Is it allowed to send SMS                
                if(\App_DI_Container::get('ConfigObject')->operation->chchangemobile->sendsms){                        
                        $this->getSMS()->setParam('newPhone', $userData['newPhone']);
                        $this->getSMS()->setParam('mobile', $userData['oldPhone']);
                        $this->getSMS()->setParam('oldPhone', $userData['oldPhone']);
                        $flgSms = $this->sendSMS();   
                        $this->getSMS()->setParam('mobile', $userData['newPhone']);
                        $this->sendSMS();   
                }
                
            
         
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }
    
     /**
      * updateNewMobileAuth
      * Send Auth code for changing the cardholder's registered mobile number
      * @param type $param
      * @return type
      * @throws Exception
      */
    public function updateNewMobileAuth($param){
        
        $this->setTemplate(__FUNCTION__);
        $oldMobileNumber = isset($param['OldMobileNumber'])?$param['OldMobileNumber']:'';
        $newMobileNumber = isset($param['NewMobileNumber'])?$param['NewMobileNumber']:'';
        
        if($oldMobileNumber=='' || $newMobileNumber==''){
            throw new \Exception ("Old and New mobile number should be filled!");
        }else if($newMobileNumber==$oldMobileNumber) {
            throw new \Exception ("Old and New mobile number cannot be same!");
        }else{
                    $mobObj = new \Mobile();
                 try {
                        $mobExists = $mobObj->checkExist(array('mobile_number'=>$oldMobileNumber));
                        if(!$mobExists){
                            throw new \Exception ("Old mobile number does not exist!");
                        }
                        
                       // $mobCheck = $mobObj->checkDuplicate($newMobileNumber,'cardholder');                
                        $userData['mobile1'] = $newMobileNumber;
                        $userData['auth_code'] = $this->generateRandom6DigitCode();
                        $this->getSMS()->setParam('mobile', $userData['mobile1']);
                        $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                        $flgSms = $this->sendSMS();  
                        $session = new \Zend_Session_Namespace('App.Operation.Controller');                         
                        $session->update_mobile_auth = $userData['auth_code'];   // sending sms on new mobile no.
                        
                   } catch (Exception $e ) {
                     \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
                   }
                   
                   return $mobCheck;                   
            }
    }
    
    /**
     * agentCreation() mail sent to admin on agent creation
     * @param array $userData
     * @return boolean
     */
     public function  agentCreation($userData) {
        $this->setTemplate(__FUNCTION__);
       
                  $admin_mail = \App_DI_Container::get('ConfigObject')->system->notifications->recipients;
                  $to_mail = $admin_mail->toArray();
                  $userData['adminmail'] = $to_mail['0'];
            try {
              if(\App_DI_Container::get('ConfigObject')->operation->agentsignup->sendpasswordmail == TRUE){
                $this->getMail()->addTo($userData['adminmail']);
                $this->getMail()->setSubject('New Agent account has been created');
                $this->getMail()->setParam('name',$userData['name']);
                $this->getMail()->setParam('email',$userData['email']);
                $this->getMail()->setParam('mobile',$userData['mobile1']);
                $this->getMail()->setParam('agent_code',$userData['agent_code']);
                $this->getMail()->setParam('server_name',\Util::getServerNameForCronAlert());
                $this->getMail()->setParam('request_date',\Util::getFormattedDate());
                $this->sendMail();

            }


         
            
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }
    /**
     * agentEmailVerification() sends email verification link to 
     * agent on being approved by Operation user
     * @param array $userData
     * @return boolean
     */
    public function  agentEmailVerification($userData) {

			  $this->setTemplate(__FUNCTION__);
              $url = \App_DI_Container::get('ConfigObject')->agent->url;
              $verifyEmailUrl = $url."/emailauthorization/index?code=".$userData['ver_code']."&id=".$userData['id']; 
              $fullUrl = $url.\Util::formatURL($verifyEmailUrl);

            try {
              if(\App_DI_Container::get('ConfigObject')->operation->agentsignup->sendpasswordmail == TRUE){
                $this->getMail()->addTo($userData['email']);
                $this->getMail()->setSubject('Email verification for your Shmart! Business Partner Account');
                $this->getMail()->setParam('first_name',$userData['first_name']);
                $this->getMail()->setParam('last_name',$userData['last_name']);
                $this->getMail()->setParam('email',$userData['email']);
                $this->getMail()->setParam('agent_code',$userData['agent_code']);
                $this->getMail()->setParam('ver_code',$userData['ver_code']);
                $this->getMail()->setParam('password',$userData['password']);
                $this->getMail()->setParam('id',$userData['id']);
                $this->getMail()->setParam('verify_url',$fullUrl);
                $this->sendMail();

            }


         
            
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }
    /**
     * Send Agent rejection status mail to Agent
     * @param type $userData
     * @return boolean
     */
    
     public function agentRejectionMail($userData) {
        $this->setTemplate(__FUNCTION__);
        try {
            
            
                $this->getMail()->addTo($userData['email']);
                $this->getMail()->setSubject('Application Status');
                $this->getMail()->setParam('name',$userData['name']);
                $this->sendMail();

            
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }
     /**
     * updatePasswordEmail
     * Send update password mail to ops user
     * @param type $userData
     * @return boolean
     */
     public function  updatePasswordEmail($userData) {
        $this->setTemplate(__FUNCTION__);
       
        
        try {
                $this->getMail()->addTo($userData['email']);
                $this->getMail()->setSubject('New Password for Shmart login');
                $this->getMail()->setParam('firstname',$userData['firstname']);
                $this->getMail()->setParam('lastname',$userData['lastname']);
                $this->getMail()->setParam('password',$userData['password']);
                $this->getMail()->setParam('username',$userData['username']);
                $this->sendMail();

           


         
            
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }
   /**
    * cardholderUpdationEmail
    * Send mail to cardholder on changes in his details
    * @param type $emailData
    * @return boolean
    */ 
     public function cardholderUpdationEmail($userData)
    {
         $this->setTemplate(__FUNCTION__);
        
        try {
            
                //Is it allowed to send email                
                if(\App_DI_Container::get('ConfigObject')->operation->cardholder->sendupdatemail == TRUE){
                    
                $this->getMail()->addTo($userData['email']);
                $this->getMail()->setSubject('Your details updation with shmart');
                $this->getMail()->setParam('cardholder_name',$userData['cardholder_name']);
                $this->getMail()->setParam('updation_date',$userData['updation_date']);
                $this->sendMail();
                    return true;
                }                
             
            
            
        } catch(Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }
    
   /**failedMvcRegistration
     * Send failed MVC registration mail
     * will send the failed mvc regisration information to ops
     *  it will expects the cardholder name, mobile, crn etc....
     * 
     */
      public function failedMvcRegistration(array $userData){
              
        $this->setTemplate(__FUNCTION__);
       
        
        try {
            if(\App_DI_Container::get('ConfigObject')->mvc->failed_registration->sendmail == TRUE){  //Is it allowed to send email 
                $emails = \App_DI_Container::get('ConfigObject')->system->notifications->recipients;
                $userData['email'] = $emails->toArray();
                $this->getMail()->addTo($userData['email']);
                $this->getMail()->setSubject('MVC registration failed and exceeds the maximum limit allowed Alert');
                $this->getMail()->setParam('chFailed',$userData['chFailed']);
                $this->getMail()->setParam('failedInfo',$userData['failedInfo']);
                $this->getMail()->setParam('mvcAttemptsAllowed',$userData['mvcAttemptsAllowed']);
                $this->getMail()->setParam('serverName',\Util::getServerNameForCronAlert());
                $this->sendMail();
                return true;
              }
            }
            
         catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * Send agent fund response to ops person
     * @param type $userData
     * @param type $module
     * @return boolean
     * @throws Exception
     */
     public function agentFundResponse($userData){
         $this->setTemplate(__FUNCTION__);
         try {                
                    $emails = \App_DI_Container::get('ConfigObject')->system->notifications->recipients;
                    $userData['email'] = $emails->toArray();
                               
                    if(\App_DI_Container::get('ConfigObject')->operation->fundresponse->sendmail == TRUE){
                        $this->getMail()->addTo($userData['email']);
                        $this->getMail()->setSubject('Agent Fund Response Intimation');
                        $this->getMail()->setParam('amount',$userData['amount']);
                        $this->getMail()->setParam('agent_code',$userData['agent_code']);
                        $this->getMail()->setParam('agent_email',$userData['agent_email']);
                        $this->getMail()->setParam('agent_mobile_number',$userData['agent_mobile_number']);
                        $this->getMail()->setParam('transaction_date',$userData['transaction_date']);
                        $this->getMail()->setParam('responseStatus',isset($userData['response_status'])?$userData['response_status']:'');
                        $this->getMail()->setParam('serverName',\Util::getServerNameForCronAlert());
                        $this->sendMail();                       
                    }
                    return true;
                        
            
        } catch(\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception ($e->getMessage());
            return false;
        }
    }
      /**
     * Send Alert Generated by Cron
     * @param array $userData
     * @return type
     */
    public function cronAlert(array $userData)
    {
return TRUE;
/*
       $this->setTemplate(__FUNCTION__);
       $emails = \App_DI_Container::get('ConfigObject')->system->notifications->recipients;
       $userData['email'] = $emails->toArray();
       try{
                        $this->getMail()->addTo($userData['email']);
                        $this->getMail()->setSubject("Cron Alert generated by ".$userData['cronName']);
                        $this->getMail()->setParam('cron_name',$userData['cron_name']);
                        $this->getMail()->setParam('message',$userData['message']);
                        $this->sendMail();  
            $this->sendMail();                       
                   
                    return true;
                        
            
        } catch(\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception ($e->getMessage());
            return false;
        }
  */     
    }
    /**
     * LowCrnAlert
     * Send low CRN alert to ops user
     * @param array $userData
     * @return boolean
     * @throws \Exception
     */
     public function lowCrnAlert(array $userData)
    {
       $this->setTemplate(__FUNCTION__);
   
       $emails = \App_DI_Container::get('ConfigObject')->system->notifications->recipients;
       $userData['email'] = $emails->toArray();
       try{
           if(\App_DI_Container::get('ConfigObject')->cron->low_crn->mail){  //Is it allowed to send email 
                        $this->getMail()->addTo($userData['email']);
                        $this->getMail()->setSubject('Low CRN Alert');
                        $this->getMail()->setParam('current_crn_count',$userData['current_crn_count']);
                        $this->getMail()->setParam('crn_count_required',$userData['crn_count_required']);
                        $this->getMail()->setParam('serverName',\Util::getServerNameForCronAlert());
                        $this->sendMail();                       
                   
                    return true;
           }  
            
        } catch(\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception ($e->getMessage());
            return false;
        }
       
    }
    
    /**agentLowBalanceOps
     * Send agent low balance mail to ops user
     * @param array $userData
     * @return boolean
     * @throws \Exception
     */
     public function agentLowBalanceOps(array $userData)
    {
       $this->setTemplate(__FUNCTION__);
   
       $emails = \App_DI_Container::get('ConfigObject')->system->notifications->recipients;
       $userData['email'] = $emails->toArray();
         try{
           if(\App_DI_Container::get('ConfigObject')->operation->minbal->sendmail){  //Is it allowed to send email 
                        $this->getMail()->addTo($userData['email']);
                        $this->getMail()->setSubject('Low Balance in Agents Account');
                        $this->getMail()->setParam('agentInfo',$userData['agentInfo']);
                      //  $this->getMail()->setParam('agent_minimum_balance',$userData['agent_minimum_balance']);
                        $this->getMail()->setParam('serverName',\Util::getServerNameForCronAlert());
                        $this->sendMail();                       
                   
                    return true;
           }  
            
        } catch(\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception ($e->getMessage());
            return false;
        }
       
    }
 /**
  * Send Error alert mail to ops users
  * @param array $userData
  * @return boolean
  * @throws \Exception
  */   
     public function errorAlert(array $userData)
    {
       $this->setTemplate(__FUNCTION__);
   
       $emails = \App_DI_Container::get('ConfigObject')->system->notifications->recipients;
       $userData['email'] = $emails->toArray();
         try{
                        $this->getMail()->addTo($userData['email']);
                        $this->getMail()->setSubject('Application Error');
                        $this->getMail()->setParam('message',$userData['message']);
                        $this->getMail()->setParam('serverName',\Util::getServerNameForCronAlert());
                        $this->sendMail();                       
                   
                    return true;
            
        } catch(\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception ($e->getMessage());
            return false;
        }
       
    }
   /**loadFail
    * Send load fail mail to ops user
    * @param array $userData
    * @return boolean
    * @throws \Exception
    */ 
    
      public function loadFail(array $userData)
    {
       $this->setTemplate(__FUNCTION__);
   
       $emails = \App_DI_Container::get('ConfigObject')->system->notifications->recipients;
       $userData['email'] = $emails->toArray();
         try{
                        $this->getMail()->addTo($userData['email']);
                        $this->getMail()->setSubject('Transaction Failure');
                        $this->getMail()->setParam('user_ip',$userData['user_ip']);
                        $this->getMail()->setParam('arrData',$userData['arrData']);
                        $this->getMail()->setParam('serverName',\Util::getServerNameForCronAlert());
                        $this->sendMail();                       
                   
                    return true;
            
        } catch(\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception ($e->getMessage());
            return false;
        }
       
    }
    /**
     * Send mail from API
     * @param array $userData
     * @return boolean
     * @throws \Exception
     */
      public function apiMail($email,$subject, $body)
    {
       $this->setTemplate(__FUNCTION__);
   
         try{
                        $this->getMail()->addTo($email);
                        $this->getMail()->setSubject($subject);
                        $this->getMail()->setParam('body',$body);
                        $this->sendMail();                       
                   
                    return true;
            
        } catch(\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception ($e->getMessage());
            return false;
        }
       
    }
    
       public function limitUpdates($userData) {
        
        $userData = \Util::objectToArray($userData) ;
       
        $this->setTemplate(__FUNCTION__);
        try {
                $this->getMail()->addTo($userData['email']);
                $this->getMail()->setSubject('Notification - Change in Product Parameters');
                $this->getMail()->setParam('product_name',$userData['product_name']);
                $this->getMail()->setParam('limit_category',$userData['limit_category']);
                $this->getMail()->setParam('user_ip',\Util::getIP());
                $this->getMail()->setParam('param_name',$userData['param_name']);
                $this->getMail()->setParam('old_value',$userData['old_value']);
                $this->getMail()->setParam('new_value',$userData['new_value']);
                $this->getMail()->setParam('date_time',\Util::getCurrDateTime());
                $this->sendMail();

 
            
        } catch (\Exception $e) {
            \Util::debug($e,TRUE);exit('aa');
            $this->setError($e->getMessage());
            return false;
        }
    }
}
