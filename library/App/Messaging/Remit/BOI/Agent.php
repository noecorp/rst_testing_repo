<?php
namespace App\Messaging\Remit\BOI;

/**
 * Description of Operation
 *
 * @author Vikram
 */
class Agent extends \App\Messaging\Remit\BOI {

    public function __construct() {
        parent::__construct(__NAMESPACE__);
    }

    
  public function neftFailureRemitter(array $userData) {
        try {

            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('nick_name', $userData['nick_name']);
                $this->getSMS()->setParam('mobile', $userData['remitter_phone']);
                $this->sendSMS();
            
        } catch (Exception $e) {
            //echo '<pre>';print_r($e);
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    } 
    
    /*
     * neftInitiateRefundRemitter
     */
  public function neftInitiateRefundRemitter(array $userData) {
       try{

            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                $this->getSMS()->setParam('nick_name', $userData['nick_name']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->sendSMS();
            
            } catch (Exception $e) {
                //echo '<pre>';print_r($e);
                \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
                $this->_error = $e->getMessage();
                return false;
            }
      }
    
    
     /*
     *   Send Add Beneficiary Auth code
     * 
     */
   
      public function addBeneficiaryAuth($userData,$resend = FALSE)
    {    
         $this->setTemplate(__FUNCTION__);   
        try {
                $pattern  = '/[^a-z\s]/i';
               if($userData['name'] == '' ){
                 throw new \Exception ("Please enter beneficiary name");   
                }
                else if(preg_match($pattern, $userData['name'])){
                 throw new \Exception ("Only alphabets are allowed in beneficiary name");   
                }
                else if($userData['nick_name'] == '' ){
                 throw new \Exception ("Please enter beneficiary nick name");   
                }
                else if(preg_match($pattern, $userData['nick_name'])){
                 throw new \Exception ("Only alphabets are allowed in beneficiary nick name");   
                }
                else if($userData['bank_name'] == ''){
                 throw new \Exception ("Please enter beneficiary bank name");   
                }
                
                else if(trim($userData['ifsc_code']) == ''){
                 throw new \Exception ("Please enter beneficiary bank IFSC code");   
                }
               else if($userData['bank_account_number'] == ''){
                 throw new \Exception ("Please enter beneficiary bank account number");   

                }
                $session = new \Zend_Session_Namespace('App.Agent.Controller'); 
                
                $mobNo = isset($userData['mobile1'])?$userData['mobile1']:'';               
                if ($resend){
                    $userData['auth_code'] = $session->beneficiary_auth;
                }
                else {
                    $userData['auth_code'] = $this->generateRandom6DigitCode();
                }
               $session->nick_name =  $userData['nick_name'];    
               $session->bank_account_number =  $userData['bank_account_number'];    
               $session->bank_name =  $userData['bank_name'];    
               $session->ifsc_code =  trim($userData['ifsc_code']);    
                                        
                if($mobNo!='' ) {
                 try {                
                    // Sending sms to Remitter with beneficiary details              
                    
                $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                $this->getSMS()->setParam('nick_name', $userData['nick_name']);
                $this->getSMS()->setParam('bank_account_number', $userData['bank_account_number']);
                $this->getSMS()->setParam('bank_name', $userData['bank_name']);
                $this->getSMS()->setParam('ifsc_code', trim($userData['ifsc_code']));
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('mobile', $userData['mobile1']);
                $this->sendSMS();
                $session->beneficiary_auth = $userData['auth_code'];                        
                                
                   } catch (Exception $e ) {
                       \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
                       $msg = $e->getMessage();
                       throw new \Exception ($msg);
                   }   
                } else if($mobNo==''){ throw new \Exception ("No mobile number provided"); }                 
                
                
                
                    
              
            
        } catch(Exception $e) {
            \App_Logger::log($e->getMessage(),\Zend_Log::ERR);
            throw new \Exception ($e->getMessage());
     }
    


    }
    
     public function beneficiaryEnrollment(array $userData) {
        try {

            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('status', $userData['status']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('nick_name', $userData['nick_name']);
                $this->getSMS()->setParam('mobile', $userData['mobile1']);
                $this->sendSMS();
            
        } catch (Exception $e) {
            //echo '<pre>';print_r($e);
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }    
    
    
     /* remitterAuth () will send remitter auth for mobile verification
     * it will expect the mobile number and country code , module name
     */
     public function remitterAuth($userData, $resend = FALSE)
    {
         $this->setTemplate(__FUNCTION__);
        try {
                // mobile duplicacy check
                $session = new \Zend_Session_Namespace('App.Agent.Controller');    
                $mobObj = new \Mobile();
                $mobileNo = isset($userData['mobile1'])?$userData['mobile1']:'';   
                
                // assigning old auth code if exists already
                if ($resend)
                    $userData['auth_code'] = $session->remitter_auth;
                else 
                    $userData['auth_code'] = $this->generateRandom6DigitCode();
                    
                        
                if($mobileNo!='' && $userData['mobile_old']!=$mobileNo) {
                    try {                
                           $mobCheck = $mobObj->checkRemitterMobileDuplicate($mobileNo);                
                      } catch (\Exception $e ) {
                        \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
                        throw new \Exception ($e->getMessage());
                      }   
                } else if($mobileNo==''){ throw new \Exception ("No mobile number provided"); }                 
                
                    
                    // Sending sms to remitter for auth code              
                    if(\App_DI_Container::get('ConfigObject')->remitter->registerauth->sendsms){
                        $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                        $this->getSMS()->setParam('product_name', $userData['product_name']);
                        $this->getSMS()->setParam('fee', $userData['fee']);
                        $this->getSMS()->setParam('mobile', $userData['mobile1']);
                        $this->sendSMS();                        
                        $session->remitter_auth = $userData['auth_code'];                        
                    }              
                   
            
        } catch(\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception ($e->getMessage());
            //return false;
        }
    }
    
     /* remitterRegistration() will send success updation to remitter
     * it will expect the mobile number and country code , module name
     */
     public function remitterRegistration($userData)
    {
         $this->setTemplate(__FUNCTION__);
        try {
                // mobile duplicacy check
                $session = new \Zend_Session_Namespace('App.Agent.Controller');    
                $mobObj = new \Mobile();
                $mobileNo = isset($userData['mobile1'])?$userData['mobile1']:'';   
                
                if($mobileNo=='') {
                   throw new \Exception ("No mobile number provided");
                }                 
                    // Sending sms to remitter for auth code              
                    if(\App_DI_Container::get('ConfigObject')->remitter->register->sendsms){
                        $this->getSMS()->setParam('product_name', $userData['product_name']);
                        $this->getSMS()->setParam('call_centre_number', $userData['call_centre_number']);
                        $this->getSMS()->setParam('customer_support_email', $userData['customer_support_email']);
                        $this->getSMS()->setParam('status', $userData['status']);
                        $this->getSMS()->setParam('mobile', $userData['mobile1']);
                        $this->sendSMS();                         
                    }              
                  
            
        } catch(\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
        }
    }
    
    
     public function beneficiaryFundTransferAuth($userData,$resend = FALSE)
    {
        $this->setTemplate(__FUNCTION__);
        try {
                  
                $session = new \Zend_Session_Namespace('App.Agent.Controller'); 
                
                $mobNo = isset($userData['mobile1'])?$userData['mobile1']:'';  
                $amt = isset($userData['amount'])?$userData['amount']:''; 
                if ($amt == ''){
                   throw new \Exception ("Please enter valid amount for fund transfer");  
                }
                if ($resend){
                    $userData['auth_code'] = $session->fundtransfer_auth;
                }
                else {
                    $userData['auth_code'] = $this->generateRandom6DigitCode();
                      
                }
                  $session->fundtransfer_amount = $userData['amount'];    
                                        
                if($mobNo!='' ) {
                 try {                
                    // Sending sms to Remitter with beneficiary details              
                   
                        $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                        $this->getSMS()->setParam('amount', $userData['amount']);
                        $this->getSMS()->setParam('nick_name', $userData['nick_name']);
                        $this->getSMS()->setParam('fee', $userData['fee']);
                        $this->getSMS()->setParam('mobile', $userData['mobile1']);
                        $this->sendSMS();                             
                        $session->fundtransfer_auth = $userData['auth_code'];                        
                        
                   } catch (Exception $e ) {
                       App_Logger::log($e->getMessage(), Zend_Log::ERR);
                       $msg = $e->getMessage();
                   }   
                } else if($mobNo=='')
                    { throw new Exception ("No mobile number provided"); }                 
                
                
                
                    
              
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            return false;
     }
   } 
   /**neftSuccessBeneficiary
    * Send NEFT success SMS to beneficiary
    * @param type $userData
    */
 public function neftSuccessBeneficiary($userData)
    {
         $this->setTemplate(__FUNCTION__);
        try {
                      
                   
                        $this->getSMS()->setParam('remitter_name', $userData['remitter_name']);
                        $this->getSMS()->setParam('product_name', $userData['product_name']);
                        $this->getSMS()->setParam('mobile', $userData['beneficiary_phone']);
                        $this->sendSMS();                         
                          
                  
            
        } catch(\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
        }
    }

    
      /**neftSuccessRemitter
    * Send NEFT success SMS to Remitter
    * @param type $userData
    */
 public function neftSuccessRemitter($userData)
    {
         $this->setTemplate(__FUNCTION__);
        try {
                        $this->getSMS()->setParam('amount', $userData['amount']);
                        $this->getSMS()->setParam('nick_name', $userData['nick_name']);
                        $this->getSMS()->setParam('contact_email', $userData['contact_email']);
                        $this->getSMS()->setParam('contact_number', $userData['contact_number']);
                        $this->getSMS()->setParam('mobile', $userData['remitter_phone']);
                        $this->sendSMS();    
                
                           
                  
            
        } catch(\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
        }
    }
/**
 * Refund amount and nick_name on remittance refund
 * @param type $userData
 */
    public function refundSmsRemitter($userData)
    {
         $this->setTemplate(__FUNCTION__);
        try {
                        $this->getSMS()->setParam('amount', $userData['amount']);
                        $this->getSMS()->setParam('nick_name', $userData['nick_name']);
                        $this->getSMS()->setParam('mobile', $userData['remitter_phone']);
                        $this->sendSMS();    
                
                           
                  
            
        } catch(\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
        }
    }
    
    public function neftInitiateRemitter($userData)
    {
         $this->setTemplate(__FUNCTION__);
        try {
                      
                   
                        $this->getSMS()->setParam('beneficiary_name', $userData['beneficiary_name']);
                        $this->getSMS()->setParam('amount', $userData['amount']);
                        $this->getSMS()->setParam('mobile', $userData['mobile']);
                        $this->sendSMS();                         
                          
                  
            
        } catch(\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
        }
    }
}
