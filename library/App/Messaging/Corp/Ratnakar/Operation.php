<?php
namespace App\Messaging\Corp\Ratnakar;

/**
 * Description of Operation
 *
 * @author Vikram
 */
class Operation extends \App\Messaging\Corp\Ratnakar {

    public function __construct() {
        parent::__construct(__NAMESPACE__);
    }
    
      
  public function cardActivation(array $userData) {
        try {

            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            if(isset($userData['product_id'])){
                $blockMsg = $this->checkAllowMsg($userData['product_id']);
                if($blockMsg){
                    $isError = TRUE;
                    return FALSE;
                }
            }
                $this->getSMS()->setParam('last_four', $userData['last_four']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
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
            if(isset($userData['product_id'])){
                $blockMsg = $this->checkAllowMsg($userData['product_id']);
                if($blockMsg){
                    $isError = TRUE;
                    return FALSE;
                }
            }
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('balance', $userData['balance']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('last_four', $userData['last_four']);
                $this->getSMS()->setParam('date_time', \Util::getCurrDateTime($showcomma = FLAG_NO));
                $this->getSMS()->setParam('mobile', $userData['mobile']);
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
                if(isset($userData['product_id'])){
                    $blockMsg = $this->checkAllowMsg($userData['product_id']);
                    if($blockMsg){
                        $isError = TRUE;
                        return FALSE;
                    }
                }
            
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
                $this->sendSMS();
            
        } catch (Exception $e) {
            //echo '<pre>';print_r($e);
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }  
    
    
    
 public function cardLoadAuth($userData) {
        $this->setTemplate(__FUNCTION__);

        try {
            if(isset($userData['product_id'])){
                $blockMsg = $this->checkAllowMsg($userData['product_id']);
                if($blockMsg){
                    $isError = TRUE;
                    return FALSE;
                }
            }
            $session = new \Zend_Session_Namespace('App.Operation.Controller');
            $mobObj = new \Mobile();
            $mobNo = isset($userData['mobile']) ? $userData['mobile'] : '';
            // Sending sms to cardholer for auth code            
            if (\App_DI_Container::get('ConfigObject')->cardholder->registerauth->sendsms) {
                $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->sendSMS();
            }
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception($e->getMessage());
            //return false;
        }
    }
    
    
    public function apiCardload(array $userData) {
        try {

            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            if(isset($userData['product_id'])){
                $blockMsg = $this->checkAllowMsg($userData['product_id']);
                if($blockMsg){
                    $isError = TRUE;
                    return FALSE;
                }
            }
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('balance', $userData['balance']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('last_four', $userData['last_four']);
                $this->getSMS()->setParam('date_time', \Util::getCurrDateTime($showcomma = FLAG_NO));
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->sendSMS();
            
        } catch (Exception $e) {
            //echo '<pre>';print_r($e);
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }
    
    
     public function apiCardActivation(array $userData) {
        try {

            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            if(isset($userData['product_id'])){
                $blockMsg = $this->checkAllowMsg($userData['product_id']);
                if($blockMsg){
                    $isError = TRUE;
                    return FALSE;
                }
            }
                $this->getSMS()->setParam('last_four', $userData['last_four']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->sendSMS();
            
        } catch (Exception $e) {
            //echo '<pre>';print_r($e);
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }
    
    
      public function apiCardLoadAuth(array $userData) {
        try {

            $this->setTemplate(__FUNCTION__);
            if(isset($userData['product_id'])){
                $blockMsg = $this->checkAllowMsg($userData['product_id']);
                if($blockMsg){
                    $isError = TRUE;
                    return FALSE;
                }
            }
            //Is it allowed to send SMS? If yes, send SMS
                $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('last_four', $userData['last_four']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->sendSMS();
            
        } catch (Exception $e) {
            //echo '<pre>';print_r($e);
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }  
    
    
    public function apiCardActivationAuth($userData) {
        $this->setTemplate(__FUNCTION__);

        try {
            if(isset($userData['product_id'])){
                $blockMsg = $this->checkAllowMsg($userData['product_id']);
                if($blockMsg){
                    $isError = TRUE;
                    return FALSE;
                }
            }
            $session = new \Zend_Session_Namespace('App.Operation.Controller');
            $mobObj = new \Mobile();
            $mobNo = isset($userData['mobile']) ? $userData['mobile'] : '';
            // Sending sms to cardholer for auth code            
            if (\App_DI_Container::get('ConfigObject')->cardholder->registerauth->sendsms) {
                $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->sendSMS();
            }
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception($e->getMessage());
            //return false;
        }
    }
    
    
    public function apiCarddebit(array $userData) {
        try {

            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            if(isset($userData['product_id'])){
                $blockMsg = $this->checkAllowMsg($userData['product_id']);
                if($blockMsg){
                    $isError = TRUE;
                    return FALSE;
                }
            }
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('balance', $userData['balance']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('last_four', $userData['last_four']);
                $this->getSMS()->setParam('date_time', \Util::getCurrDateTime($showcomma = FLAG_NO));
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->sendSMS();
            
        } catch (Exception $e) {
            //echo '<pre>';print_r($e);
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }
    
    
     public function apiCardDebitAuth(array $userData) {
        try {
            
            if($userData['request_from']==TYPE_REQUEST_ECOM){
                $this->setTemplate('ecomApiCardDebitAuth');
            }else{
                $this->setTemplate(__FUNCTION__);
            }   

            //Is it allowed to send SMS? If yes, send SMS
                $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('last_four', $userData['last_four']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->sendSMS();
            
        } catch (Exception $e) {
            //echo '<pre>';print_r($e);
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }
    
    private function checkAllowMsg($productId){
        $productModel = new \Products();
        $productDetails = $productModel->getProductInfo($productId);
        return \Util::disabledSMSByProduct($productDetails['const']);
    }
}
