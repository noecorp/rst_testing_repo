<?php
namespace App\Messaging\Corp\Ratnakar;

/**
 * Description of Operation
 *
 * @author Vikram
 */
class Agent extends \App\Messaging\Corp\Ratnakar {

    public function __construct() {
        parent::__construct(__NAMESPACE__);
    }

    
      public function cardLoadAuth($userData,$resend = FALSE)
    {
        $this->setTemplate(__FUNCTION__);
        try {
                  
               $session = new \Zend_Session_Namespace('App.Agent.Controller');  
              
                $mobNo = isset($userData['mobile'])?$userData['mobile']:'';  
                $amt = isset($userData['amount'])?$userData['amount']:''; 
                $email = isset($userData['email'])?$userData['email']:''; 
                if ($amt == ''){
                   throw new \Exception ("Please enter valid amount for fund transfer");  
                }
                if ($resend){
                    $userData['auth_code'] = $session->fundtransfer_auth;
                }
                else {
                    $userData['auth_code'] = $this->generateRandom6DigitCode();
                    $session->fundtransfer_auth = $userData['auth_code'];    
                }
               
                    $session->fundtransfer_amount = $userData['amount'];   
                                           
                if($email != ''){
                $this->getMail()->addTo($userData['email']);
                $this->getMail()->setSubject('Authorization Code');
                $this->getMail()->setParam('name',$userData['cardholder_name']);
                $this->getMail()->setParam('product',$userData['product']);
                $this->getMail()->setParam('user_ip',\Util::getIP());
                $this->getMail()->setParam('auth_code',$userData['auth_code']);
                $this->getMail()->setParam('medi_assist_id',$userData['medi_assist_id']);
                $this->getMail()->setParam('employer_name',$userData['employer_name']);
                $this->getMail()->setParam('hospital_id',$userData['hospital_id']);
                $this->getMail()->setParam('date_time',\Util::getCurrDateTime());
                $this->sendMail();
                
                }
                
                    
              
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            return false;
     }
   }
   
   
   
   /*
    *  Send Auth code on cardholder signup
    */ 
    public function cardholderAuth($userData ,$resend = FALSE)
    {
         $this->setTemplate(__FUNCTION__);
         $isError = false;
         
        try { 
                
                if(isset($userData['product_id'])){
                    $blockMsg = $this->checkAllowMsg($userData['product_id']);
                    if($blockMsg){
                        $isError = TRUE;
                        return FALSE;
                    }
                }
                // mobile duplicacy check
                $session = new \Zend_Session_Namespace('App.Agent.Controller');    
                $mobObj = new \Mobile();
                $mobNo = isset($userData['mobile1'])?$userData['mobile1']:'';               
                if ($resend){
                    $userData['auth_code'] = $session->corp_cardholder_auth;
                }
                else {
                    $userData['auth_code'] = $this->generateRandom6DigitCode();
                }
                  
                                        
                if($mobNo!='' && $userData['mobile_number_old']!=$mobNo) {
                 try {                
                        $mobCheck = $mobObj->checkCorpCardholderMobileDuplicate($mobNo);                
                   } catch (\Exception $e ) {
                       \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
                       $msg = $e->getMessage();
                       $isError = true;
                     throw new \Exception ($msg);
                       //return $e->getMessage();
                   }   
                } else if($mobNo==''){ throw new \Exception ("No mobile number provided"); }                 
                
               
                    // Sending sms to cardholer for auth code              
                if(!$isError){
                    if(\App_DI_Container::get('ConfigObject')->corp->cardholder_registerauth->sendsms){
                        $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                        $this->getSMS()->setParam('mobile', $userData['mobile1']);
                        $this->getSMS()->setParam('product_name', $userData['product_name']);
                        $this->sendSMS();                         
                        $session->corp_cardholder_auth = $userData['auth_code'];                        
                    }              
                }
            
        } catch(\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception ($e->getMessage());
            //return false;
        }
    }
    
      public function cardLoadSuccess($userData,$resend = FALSE)
    {
        $this->setTemplate(__FUNCTION__);
        try {
                  
              
                $amt = isset($userData['amount'])?$userData['amount']:''; 
                $email = isset($userData['email'])?$userData['email']:''; 
                if ($amt == ''){
                   throw new \Exception ("Please enter valid amount for fund transfer");  
                }
                
                if($email != ''){
                $this->getMail()->addTo($userData['email']);
                $this->getMail()->setSubject('Authorization Code');
                $this->getMail()->setParam('name',$userData['cardholder_name']);
                $this->getMail()->setParam('product',$userData['product']);
                $this->getMail()->setParam('user_ip',\Util::getIP());
                $this->getMail()->setParam('auth_code',$userData['auth_code']);
                $this->getMail()->setParam('medi_assist_id',$userData['medi_assist_id']);
                $this->getMail()->setParam('employer_name',$userData['employer_name']);
                $this->getMail()->setParam('hospital_id',$userData['hospital_id']);
                $this->getMail()->setParam('date_time',\Util::getCurrDateTime());
                $this->sendMail();
                }
                
                    
              
            
        } catch(Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            return false;
     }
   }
   
   private function checkAllowMsg($productId){
        $productModel = new Products();
        $productDetails = $productModel->getProductInfo($productId);
        return Util::disabledSMSByProduct($productDetails['const']);
   }
   
   
   public function cardLoadAgentAuth($userData ,$resend = FALSE) {
        //print_r($userData);
        $this->setTemplate(__FUNCTION__);
        try {
            // mobile duplicacy check 
            $session = new \Zend_Session_Namespace('App.Agent.Controller');
            $mobObj = new \Mobile();
            
            $mobNo = isset($userData['mobile']) ? $userData['mobile'] : '';
            if ($resend) {
                $userData['auth_code'] = $session->ch_fund_load_auth; 
            } else {
                $userData['auth_code'] = $this->generateRandom6DigitCode();
            }
            if ($mobNo == '') {
                throw new \Exception ("No mobile number provided");
            }
 
            // Sending sms to cardholer for fund load auth code
            if(\App_DI_Container::get('ConfigObject')->corp->cardholder_registerauth->sendsms){
                $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setParam('account_name', $userData['account_name']);
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('currency', $userData['currency']);
                $this->sendSMS();
                $session->ch_fund_load_auth = $userData['auth_code'];
            }
        } catch (Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception ($e->getMessage());
            //return false;
        }
   }
   
   public function cardLoadAgentMsg($userData) {
        //print_r($userData);
        $this->setTemplate(__FUNCTION__);
        try {
            // mobile duplicacy check 
            $session = new \Zend_Session_Namespace('App.Agent.Controller');
            $mobObj = new \Mobile();
            
            $mobNo = isset($userData['mobile']) ? $userData['mobile'] : '';  
           
            if ($mobNo == '') {
                throw new \Exception ("No mobile number provided");
            }
  
            // Sending sms to cardholer for fund load auth code
            if(\App_DI_Container::get('ConfigObject')->corp->cardholder_registerauth->sendsms){
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('cardholder_name', $userData['cardholder_name']);
                $this->getSMS()->setParam('account_name', $userData['account_name']);
                $this->getSMS()->setParam('currency', $userData['currency']);
                $this->getSMS()->setParam('call_center_num', $userData['call_center_num']);
                $this->getSMS()->setParam('current_balance', $userData['current_balance']);
                $this->sendSMS();
            }
        } catch (Exception $e) {
           // \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception ($e->getMessage());
            //return false;
        }
   }
   
}
