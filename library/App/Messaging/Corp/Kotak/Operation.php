<?php
namespace App\Messaging\Corp\Kotak;

/**
 * Description of Operation
 *
 * @author Vikram
 */
class Operation extends \App\Messaging\Corp\Kotak {

    public function __construct() {
        parent::__construct(__NAMESPACE__);
    }
          
  public function cardActivation(array $userData) {
        try {

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

            $this->setTemplate(__FUNCTION__);
            
            //Is it allowed to send SMS? If yes, send SMS
            
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('balance', $userData['balance']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('last_four', $userData['last_four']);
                $this->getSMS()->setParam('date_time', \Util::getFormattedDate());
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
