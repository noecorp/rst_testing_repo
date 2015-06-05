<?php
namespace App\Messaging\Remit\Kotak;

/**
 * Description of Operation
 *
 * @author Vikram
 */
class Api extends \App\Messaging\Remit\Kotak {

    public function __construct() {
        parent::__construct(__NAMESPACE__);
    }
    
     public function cardBlock(array $userData) {
        try {

            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('last_four', $userData['last_four']);
                $this->getSMS()->setParam('date_time', \Util::getCurrDateTime($showcomma = FLAG_NO));
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
            
        } catch (Exception $e) {
            //echo '<pre>';print_r($e);
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }
     public function cardUnblock(array $userData) {
        try {

            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('last_four', $userData['last_four']);
                $this->getSMS()->setParam('date_time', \Util::getCurrDateTime($showcomma = FLAG_NO));
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
            
        } catch (Exception $e) {
            //echo '<pre>';print_r($e);
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }
    
     public function balanceEnquiry(array $userData) {
        try {

            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('balance', $userData['balance']);
                $this->getSMS()->setParam('last_four', 'XXXX');
                $this->getSMS()->setParam('date_time', \Util::getCurrDateTime($showcomma = FLAG_NO));
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
            
        } catch (Exception $e) {
            //echo '<pre>';print_r($e);
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }
    
    
    public function cardActivation(array $userData) {
        try {

            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            
                $this->getSMS()->setParam('last_four', 'XXXX');
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setParam('product_supportemail', $userData['product_supportemail']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
            
        } catch (Exception $e) {
            //echo '<pre>';print_r($e);
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    } 
    
    public function cardLoad(array $userData) {
        try {

            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('balance', $userData['balance']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('last_four', 'XXXX');
                $this->getSMS()->setParam('date_time', \Util::getCurrDateTime($showcomma = FLAG_NO));
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
            
        } catch (Exception $e) {
            //echo '<pre>';print_r($e);
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }
    
     public function cardTransaction(array $userData,$getResponse = FALSE) {
        try {

            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('balance', $userData['balance']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('last_four', $userData['last_four']);
                $this->getSMS()->setParam('date_time', \Util::getCurrDateTime($showcomma = FLAG_NO));
                $this->getSMS()->setParam('transaction_place', $userData['transaction_place']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                if($getResponse == TRUE) {
                    return $this->getSMS()->getMessage();
                }
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
            
        } catch (Exception $e) {
            //echo '<pre>';print_r($e);
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }  
    
    
  
    
      public function cardLoadAuth(array $userData) {
        try {

            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
                $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('last_four', $userData['last_four']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
            
        } catch (Exception $e) {
            //echo '<pre>';print_r($e);
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }  
    
    
    public function cardActivationAuth($userData) {
        $this->setTemplate(__FUNCTION__);

        try {
            $this->getSMS()->setParam('auth_code', $userData['auth_code']);
            $this->getSMS()->setParam('mobile', $userData['mobile']);
            $this->getSMS()->setParam('product_name', $userData['product_name']);
            $this->getSMS()->setParam('product_supportemail', $userData['product_supportemail']);
            $this->getSMS()->setFrom($userData['sms_sender']);
            $this->sendSMS();
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception(\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG,\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_CODE);
            //return false;
        }
    }
    
    
    public function cardDebit(array $userData) {
        try {

            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('balance', $userData['balance']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('last_four', $userData['last_four']);
                $this->getSMS()->setParam('date_time', \Util::getCurrDateTime($showcomma = FLAG_NO));
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
            
        } catch (Exception $e) {
            //echo '<pre>';print_r($e);
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }
    
    
     public function cardDebitAuth(array $userData) {
        try {

            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
                $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('last_four', $userData['last_four']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
            
        } catch (Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }  
    
    
       public function customerEditAuth($userData) {
        $this->setTemplate(__FUNCTION__);

        try {
                $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('product_supportemail', $userData['product_supportemail']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception(\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG,\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_CODE);
        }
    }
    
    
     public function beneRegistrationAuth($userData) {
        $this->setTemplate(__FUNCTION__);

        try {
                $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('product_supportemail', $userData['product_supportemail']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception(\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG,\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_CODE);
        }
    }
     public function remittance($userData) {
        $this->setTemplate(__FUNCTION__);

       try {
            // Sending sms after wallet transfer          
                $this->getSMS()->setParam('bene_code', $userData['bene_code']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('bene_name', $userData['bene_name']);
                $this->getSMS()->setParam('wallet_code', $userData['wallet_code']);
                $this->getSMS()->setParam('ref_num', $userData['ref_num']);
                $this->getSMS()->setParam('date_time', \Util::getFormattedDate());
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('product_supportemail', $userData['product_supportemail']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
           
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception(\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG,\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_CODE);
            //return false;
        }
    }
     public function transfer($userData) {
        $this->setTemplate(__FUNCTION__);
       
        try {
            // Sending sms after wallet transfer          
                $this->getSMS()->setParam('bene_code', $userData['bene_code']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('bene_name', $userData['bene_name']);
                $this->getSMS()->setParam('wallet_code', $userData['wallet_code']);
                $this->getSMS()->setParam('ref_num', $userData['ref_num']);
                $this->getSMS()->setParam('date_time', \Util::getFormattedDate());
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('product_supportemail', $userData['product_supportemail']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
           
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception(\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG,\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_CODE);
            //return false;
        }
    }
     public function cardUpdation($userData) {
        $this->setTemplate(__FUNCTION__);

        try {
            $session = new \Zend_Session_Namespace('App.Operation.Controller');
            $mobObj = new \Mobile();
            $mobNo = isset($userData['mobile']) ? $userData['mobile'] : '';
            // Sending sms to cardholer for auth code            
            if (\App_DI_Container::get('ConfigObject')->cardholder->registerauth->sendsms) {
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setParam('last_four', $userData['last_four']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('sms_name', $userData['sms_name']);
                $this->getSMS()->setParam('product_supportemail', $userData['product_supportemail']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
            }
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception(\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG,\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_CODE);
            //return false;
        }
    }
    public function cardBeneReg($userData) {
        $this->setTemplate(__FUNCTION__);

        try {
            $session = new \Zend_Session_Namespace('App.Operation.Controller');
            $mobObj = new \Mobile();
            $mobNo = isset($userData['mobile']) ? $userData['mobile'] : '';
            // Sending sms to cardholer for auth code            
            if (\App_DI_Container::get('ConfigObject')->cardholder->registerauth->sendsms) {
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setParam('bene_name', $userData['bene_name']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('date', \Util::getFormattedDate('jS F Y'));
                $this->getSMS()->setParam('time', \Util::getFormattedDate('H:i:s'));
                $this->getSMS()->setParam('sms_name', $userData['sms_name']);
                $this->getSMS()->setParam('product_supportemail', $userData['product_supportemail']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
            }
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception(\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG,\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_CODE);
            //return false;
        }
    }
    
    public function transactionAuth($userData) {
        $this->setTemplate(__FUNCTION__);

        try {
             // Sending sms for fund transfer   
             $this->getSMS()->setParam('auth_code', $userData['auth_code']);
             $this->getSMS()->setParam('mobile', $userData['mobile']);
             $this->getSMS()->setParam('product_name', $userData['product_name']);
             $this->getSMS()->setParam('product_supportemail', $userData['product_supportemail']);
             $this->getSMS()->setFrom($userData['sms_sender']);
             $this->sendSMS();

         } catch (\Exception $e) {
             \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
             throw new \Exception(\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG,\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_CODE);
         }
    }
    
     public function remittanceAuth($userData) {
        $this->setTemplate(__FUNCTION__);

       try {
            // Sending sms for remittance 
                $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('product_supportemail', $userData['product_supportemail']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
           
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception(\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG,\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_CODE);
            //return false;
        }
    }
    
    public function custReg($userData) {
        $this->setTemplate(__FUNCTION__);

        try {
            $session = new \Zend_Session_Namespace('App.Operation.Controller');
            $mobObj = new \Mobile();
            $mobNo = isset($userData['mobile']) ? $userData['mobile'] : '';
            // Sending sms to cardholer for auth code            
            if (\App_DI_Container::get('ConfigObject')->cardholder->registerauth->sendsms) {
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setParam('cust_name', $userData['cust_name']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('date', \Util::getFormattedDate('jS F Y'));
                $this->getSMS()->setParam('time', \Util::getFormattedDate('H:i:s'));
                $this->getSMS()->setParam('sms_name', $userData['sms_name']);
                $this->getSMS()->setParam('product_supportemail', $userData['product_supportemail']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
            }
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception(\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG,\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_CODE);
            //return false;
        }
    }
    
    public function deactiveBene($userData) {
      
        $this->setTemplate(__FUNCTION__);

        try {
            $session = new \Zend_Session_Namespace('App.Operation.Controller');
            $mobObj = new \Mobile();
            $mobNo = isset($userData['mobile']) ? $userData['mobile'] : '';
            // Sending sms to cardholer for auth code            
            if (\App_DI_Container::get('ConfigObject')->cardholder->registerauth->sendsms) {
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setParam('bene_name', $userData['bene_name']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('date', \Util::getFormattedDate('jS F Y'));
                $this->getSMS()->setParam('time', \Util::getFormattedDate('H:i:s'));
                $this->getSMS()->setParam('sms_name', $userData['sms_name']);
                $this->getSMS()->setParam('product_supportemail', $userData['product_supportemail']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
            }
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception(\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG,\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_CODE);
            //return false;
        }
    }
    
    
    public function remittanceSuccess($userData) {
      
        $this->setTemplate(__FUNCTION__);

        try {
            $session = new \Zend_Session_Namespace('App.Operation.Controller');
            $mobObj = new \Mobile();
            $mobNo = isset($userData['mobile']) ? $userData['mobile'] : '';
            // Sending sms to cardholer for auth code            
            if (\App_DI_Container::get('ConfigObject')->cardholder->registerauth->sendsms) {
                // Sending sms after wallet transfer          
                $this->getSMS()->setParam('response_status', $userData['response_status']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('bene_name', $userData['bene_name']);
                $this->getSMS()->setParam('ref_num', $userData['ref_num']);
                $this->getSMS()->setParam('date_time', \Util::getFormattedDate());
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('product_supportemail', $userData['product_supportemail']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
            }
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
          //  throw new \Exception(\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG,\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_CODE);
            //return false;
        }
    }
    
    public function remittanceNoresponse($userData) {
      
        $this->setTemplate(__FUNCTION__);

        try {
            $session = new \Zend_Session_Namespace('App.Operation.Controller');
            $mobObj = new \Mobile();
            $mobNo = isset($userData['mobile']) ? $userData['mobile'] : '';
            // Sending sms to cardholer for auth code            
            if (\App_DI_Container::get('ConfigObject')->cardholder->registerauth->sendsms) {
                // Sending sms after wallet transfer          
                $this->getSMS()->setParam('response_status', $userData['response_status']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('bene_name', $userData['bene_name']);
                $this->getSMS()->setParam('ref_num', $userData['ref_num']);
                $this->getSMS()->setParam('date_time', \Util::getFormattedDate());
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('product_supportemail', $userData['product_supportemail']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
            }
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
         //   throw new \Exception(\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG,\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_CODE);
            //return false;
        }
    }
    
    public function remittanceFailure($userData) {
      
        $this->setTemplate(__FUNCTION__);

        try {
            $session = new \Zend_Session_Namespace('App.Operation.Controller');
            $mobObj = new \Mobile();
            $mobNo = isset($userData['mobile']) ? $userData['mobile'] : '';
            // Sending sms to cardholer for auth code            
            if (\App_DI_Container::get('ConfigObject')->cardholder->registerauth->sendsms) {
                // Sending sms after wallet transfer          
                $this->getSMS()->setParam('response_status', $userData['response_status']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('bene_name', $userData['bene_name']);
                $this->getSMS()->setParam('ref_num', $userData['ref_num']);
                $this->getSMS()->setParam('date_time', \Util::getFormattedDate());
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('product_supportemail', $userData['product_supportemail']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
            }
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception(\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG,\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_CODE);
            //return false;
        }
    }
    
    public function refundTransaction($userData) {
      
        $this->setTemplate(__FUNCTION__);

        try {
            $session = new \Zend_Session_Namespace('App.Operation.Controller');
            $mobObj = new \Mobile();
            $mobNo = isset($userData['mobile']) ? $userData['mobile'] : '';
            // Sending sms to cardholer for auth code            
            if (\App_DI_Container::get('ConfigObject')->cardholder->registerauth->sendsms) {
                // Sending sms after wallet transfer          
                $this->getSMS()->setParam('response_status', $userData['response_status']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('bene_name', $userData['bene_name']);
                $this->getSMS()->setParam('ref_num', $userData['ref_num']);
                $this->getSMS()->setParam('date_time', \Util::getFormattedDate());
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('product_supportemail', $userData['product_supportemail']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
            }
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
	 //   throw new \Exception(\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG,\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_CODE);
            //return false;
        }
    }
    
      public function remittanceStaticOtp($userData)
    {
        $this->setTemplate(__FUNCTION__);

        try {
            // Sending sms for remittance 
                $bankKotak = \App_DI_Definition_Bank::getInstance(BANK_KOTAK);
                $bankKotakUnicode = $bankKotak->bank->unicode;
                $maxTxn = $bankKotak->remit->otp->max_txn ;
                $otpLife = $bankKotak->remit->otp->life ;
                
                $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                $this->getSMS()->setParam('otp_life', $otpLife);
                $this->getSMS()->setParam('max_txn', $maxTxn);
                        
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('product_supportemail', $userData['product_supportemail']);
                $this->getSMS()->setFrom($userData['sms_sender']);
                $this->sendSMS();
           
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception(\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG,\ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_CODE);
            
        }
    }
   
}
