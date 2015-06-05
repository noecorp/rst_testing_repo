<?php
namespace App\Messaging\MVC\Axis;

/**
 * Description of Operation
 *
 * @author Vikram
 */
class Customer extends \App\Messaging\MVC\Axis {
    
    
    public function __construct() {
        parent::__construct(__NAMESPACE__);
    }
    
    /*
 *  Send Auth code on Partner portal signup
 */
   public function  authCode($userData) {
        $userData = \Util::objectToArray($userData) ;  
        
        $this->setTemplate(__FUNCTION__);
        try {
            $config = \App_DI_Container::get('ConfigObject');
            
            if (\App_DI_Container::get('ConfigObject')->operation->loginauth->sendmail) {
                $this->getMail()->addTo($userData['email']);
                $this->getMail()->setSubject('Authorization Code');
                $this->getMail()->setParam('first_name',$userData['first_name']);
                $this->getMail()->setParam('last_name',$userData['last_name']);
                $this->getMail()->setParam('user_ip',\Util::getIP());
                $this->getMail()->setParam('auth_code',$userData['auth_code']);
                $this->getMail()->setParam('date_time',\Util::getCurrDateTime());
                $this->getMail()->setParam('login_attempts',$config->system->login->attempts->allowed);
                $this->sendMail();

            }

            //Is it allowed to send SMS                
           if (\App_DI_Container::get('ConfigObject')->operation->loginauth->sendsms) {
                $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                $this->getSMS()->setParam('mobile', $userData['mobile1']);
                $this->sendSMS();

             
            }
         
            
        } catch (\Exception $e) {
            echo "<pre>";print_r($e);exit;
            $this->setError($e->getMessage());
            return false;
        }
        //print  $userData['mobile1'];
        //echo '<pre>';print_r($userData);exit('HERE');
    }

    

}
