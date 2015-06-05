<?php
namespace App\Messaging\MVC\Axis;

/**
 * Description of Operation
 *
 * @author Vikram
 */
class Bank extends \App\Messaging\MVC\Axis {

    public function __construct() {
        parent::__construct(__NAMESPACE__);
    }

    /*
     *  Send Auth code on Partner portal signup
     */

    public function authCode($userData) {
        $userData = \Util::objectToArray($userData);
        $this->setTemplate(__FUNCTION__);
        try {
            $config = \App_DI_Container::get('ConfigObject');

            if (\App_DI_Container::get('ConfigObject')->operation->loginauth->sendmail) {
                $this->getMail()->addTo($userData['email']);
                $this->getMail()->setSubject('Authorization Code');
                $this->getMail()->setParam('first_name', $userData['first_name']);
                $this->getMail()->setParam('last_name', $userData['last_name']);
                $this->getMail()->setParam('user_ip', \Util::getIP());
                $this->getMail()->setParam('auth_code', $userData['auth_code']);
                $this->getMail()->setParam('date_time', \Util::getCurrDateTime());
                $this->getMail()->setParam('login_attempts', $config->system->login->attempts->allowed);
                $this->sendMail();
            }

            //Is it allowed to send SMS                
            if (\App_DI_Container::get('ConfigObject')->operation->loginauth->sendsms) {

                $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                $this->getSMS()->setParam('mobile', $userData['mobile']);
                $this->sendSMS();
            }
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /*
     *  Send Conf code on Partner portal change password request
     */

    public function confCode($userData) {
        $this->setTemplate(__FUNCTION__);
        try {
            $config = \App_DI_Container::get('ConfigObject');

            if (\App_DI_Container::get('ConfigObject')->operation->loginauth->sendmail) {
                $this->getMail()->addTo($userData['email']);
                $this->getMail()->setSubject('Confirmation Code');
                $this->getMail()->setParam('first_name', $userData['first_name']);
                $this->getMail()->setParam('last_name', $userData['last_name']);
                $this->getMail()->setParam('user_ip', \Util::getIP());
                $this->getMail()->setParam('conf_code', $userData['conf_code']);
                $this->sendMail();
            }

            //Is it allowed to send SMS                
            if (\App_DI_Container::get('ConfigObject')->operation->loginauth->sendsms) {
                $this->getSMS()->setParam('conf_code', $userData['conf_code']);
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

    public function cardholderAuth($userData, $resend = FALSE) {
        $this->setTemplate(__FUNCTION__);

        try {
            // mobile duplicacy check
            $session = new \Zend_Session_Namespace('App.Agent.Controller');
            $mobObj = new \Mobile();
            $mobNo = isset($userData['mobile1']) ? $userData['mobile1'] : '';
            if ($resend) {
                $userData['auth_code'] = $session->cardholder_auth;
            } else {
                $userData['auth_code'] = $this->generateRandom6DigitCode();
            }


            if ($mobNo != '' && $userData['mobile_number_old'] != $mobNo) {
                try {
                    $mobCheck = $mobObj->checkDuplicate($mobNo, 'cardholder');
                } catch (\Exception $e) {
                    \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
                    $msg = $e->getMessage();
                    throw new \Exception($msg);
                    //return $e->getMessage();
                }
            } else if ($mobNo == '') {
                throw new \Exception("No mobile number provided");
            }


            // Sending sms to cardholer for auth code              
            if (\App_DI_Container::get('ConfigObject')->cardholder->registerauth->sendsms) {
                $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                $this->getSMS()->setParam('mobile', $userData['mobile1']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->sendSMS();
                $session->cardholder_auth = $userData['auth_code'];
            }
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception($e->getMessage());
            //return false;
        }
    }
}

