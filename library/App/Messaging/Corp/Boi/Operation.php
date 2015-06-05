<?php
namespace App\Messaging\Corp\Boi;

/**
 * Description of Operation
 *
 * @author Vikram
 */
class Operation extends \App\Messaging\Corp\Boi {

    public function __construct() {
        parent::__construct(__NAMESPACE__);
    }
          
  public function cardActivation(array $userData) {
        try {
            return TRUE;
            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            
                $this->getSMS()->setParam('last_four', $userData['last_four']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('date_time',\Util::getFormattedDate());
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
            return TRUE;
            $this->setTemplate(__FUNCTION__);
            
            //Is it allowed to send SMS? If yes, send SMS
            
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('balance', $userData['balance']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('last_four', $userData['last_four']);
                $this->getSMS()->setParam('date_time', \Util::getFormattedDate('d/m/y h:i A'));
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->sendSMS();
            
        } catch (Exception $e) {
            //echo '<pre>';print_r($e);
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }
    }
    
    public function accountActivation(array $userData) {
        try {
            return TRUE;
            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            
                $this->getSMS()->setParam('boi_account_number', $userData['boi_account_number']);
                $this->getSMS()->setParam('name', $userData['name']);
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
            return TRUE;
            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('balance', $userData['balance']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('last_four', $userData['last_four']);
                $this->getSMS()->setParam('date_time', \Util::getFormattedDate('d/m/y h:i A'));
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
    
    public function autoDebit(array $userData) {
        try {
            return TRUE;
            $this->setTemplate(__FUNCTION__);
            
            //Is it allowed to send SMS? If yes, send SMS
            
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('last_four', $userData['last_four']);
                $this->getSMS()->setParam('date_time', \Util::getFormattedDate('d/m/y h:i A'));
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->sendSMS();
            
        } catch (Exception $e) {
            //echo '<pre>';print_r($e);
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            $this->_error = $e->getMessage();
            return false;
        }

    }

}
