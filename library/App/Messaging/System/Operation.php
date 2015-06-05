<?php
namespace App\Messaging\System;

/**
 * Description of Operation
 *
 * @author Vikram
 */
class Operation extends \App\Messaging\System {

    public function __construct() {
        parent::__construct(__NAMESPACE__);
    }

    public function limitUpdates($userData) {
         
        $userData = \Util::objectToArray($userData) ;
        $this->setTemplate(__FUNCTION__);
        try {
                $opsUserModel = new \OperationUser();
                $userEmails = $opsUserModel->findusersByGroupID(ADMIN_USER_GROUP);
                $config = \App_DI_Container::get('ConfigObject');
                if (\App_DI_Container::get('ConfigObject')->operation->limitupdate->sendmail) {
                foreach($userEmails as $val){
                     $this->getMail()->addTo($val['email']);
                     $str .='<br>Mr. '.$val['name'].', mobile: '.$val['mobile1'].', email: '.$val['email'].'</br>';
                }
                
                $this->getMail()->setSubject('Notification - Change in Product Parameters');
                $this->getMail()->setParam('product_name',$userData['product_name']);
                $this->getMail()->setParam('limit_category',$userData['limit_category']);
                $this->getMail()->setParam('user_ip',\Util::getIP());
                $this->getMail()->setParam('param_name',$userData['param_name']);
                $this->getMail()->setParam('old_value',$userData['old_value']);
                $this->getMail()->setParam('new_value',$userData['new_value']);
                $this->getMail()->setParam('contacts_list',$str);
                $this->getMail()->setParam('date_time',\Util::getCurrDateTime());
                $this->sendMail();
                }
 
            
        } catch (\Exception $e) {
            \Util::debug($e,TRUE);exit('aa');
            $this->setError($e->getMessage());
            return false;
        }
    }  
    
     public function cardBlock(array $userData) {
        try {

            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            
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
     public function cardUnblock(array $userData) {
        try {

            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            
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
    
     public function balanceEnquiry(array $userData) {
        try {

            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('balance', $userData['balance']);
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
    
     public function miniStatement(array $userData) {
        try {

            $this->setTemplate(__FUNCTION__);
           
            //Is it allowed to send SMS? If yes, send SMS
            
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('mini_stmt', $userData['mini_stmt']);
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
    
    public function bankcustlimitUpdates($userData) {
         
        $userData = \Util::objectToArray($userData) ;
        $this->setTemplate(__FUNCTION__);
        try {
                $opsUserModel = new \OperationUser();
                $userEmails = $opsUserModel->findusersByGroupID(ADMIN_USER_GROUP);
                $config = \App_DI_Container::get('ConfigObject');
                if (\App_DI_Container::get('ConfigObject')->operation->limitupdate->sendmail) {
                foreach($userEmails as $val){
                     $this->getMail()->addTo($val['email']);
                     $str .='<br>Mr. '.$val['name'].', mobile: '.$val['mobile1'].', email: '.$val['email'].'</br>';
                     
                }
                $this->getMail()->setSubject('Notification - Change in Bank Parameters');
                $this->getMail()->setParam('bank_name',$userData['bank_name']);
                $this->getMail()->setParam('limit_category',$userData['limit_category']);
                $this->getMail()->setParam('user_ip',\Util::getIP());
                $this->getMail()->setParam('param_name',$userData['param_name']);
                $this->getMail()->setParam('old_value',$userData['old_value']);
                $this->getMail()->setParam('new_value',$userData['new_value']);
                $this->getMail()->setParam('contacts_list',$str);
                $this->getMail()->setParam('date_time',\Util::getCurrDateTime());
                $this->sendMail();
              
                }
        } catch (\Exception $e) {
            \Util::debug($e,TRUE);exit('aa');
            $this->setError($e->getMessage());
            return false;
        }
    }
}
