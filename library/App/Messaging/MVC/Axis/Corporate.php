<?php
namespace App\Messaging\MVC\Axis;

/**
 * Description of Operation
 *
 * @author Vikram
 */
class Corporate extends \App\Messaging\MVC\Axis {

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
                $this->getSMS()->setParam('mobile', $userData['mobile']);
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
    
    /**
     * agentCreation() mail sent to admin on agent creation
     * @param array $userData
     * @return boolean
     */
    public function userCreation($userData) {
        $this->setTemplate(__FUNCTION__);

        $admin_mail = \App_DI_Container::get('ConfigObject')->system->notifications->recipients;
        $to_mail = $admin_mail->toArray();
        $userData['adminmail'] = $to_mail['0'];
        try {
            if (\App_DI_Container::get('ConfigObject')->operation->agentsignup->sendpasswordmail) {
                $this->getMail()->addTo($userData['adminmail']);
                $this->getMail()->setSubject('New Corporate account has been created');
                $this->getMail()->setParam('name', $userData['name']);
                $this->getMail()->setParam('email', $userData['email']);
                $this->getMail()->setParam('mobile', $userData['mobile']);
                $this->getMail()->setParam('agent_code', $userData['agent_code']);
                $this->sendMail();
            }
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }
    
     /**
     * corporateEmailVerification() sends email verification link to 
     * agent on being approved by Operation user
     * @param array $userData
     * @return boolean
     */
    public function corporateEmailVerification($userData) {
        $this->setTemplate(__FUNCTION__);
        $url = \App_DI_Container::get('ConfigObject')->corporate->url;

        $verifyEmailUrl = "/emailauthorization/index/code/" . $userData['verification_code'] . "/id/" . $userData['id'];
        $fullUrl = $url . \Util::formatURL($verifyEmailUrl);

        try {
            if (\App_DI_Container::get('ConfigObject')->operation->agentsignup->sendpasswordmail == TRUE) {
                $this->getMail()->addTo($userData['email']);
                $this->getMail()->setSubject('Email verification for your Shmart! Business Corporate Account');
                $this->getMail()->setParam('first_name', $userData['first_name']);
                $this->getMail()->setParam('last_name', $userData['last_name']);
                $this->getMail()->setParam('email', $userData['email']);
                $this->getMail()->setParam('agent_code', $userData['agent_code']);
                $this->getMail()->setParam('password', $userData['password']);
                $this->getMail()->setParam('id', $userData['id']);
                $this->getMail()->setParam('verify_url', $fullUrl);
                $this->sendMail();
            }
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }
    
    /**
     * updatecorporateEmailVerification() sends email verification link to 
     * agent on being approved by Operation user
     * @param array $userData
     * @return boolean
     */
    public function updateCorporateEmailVerification($userData) {
        $this->setTemplate(__FUNCTION__);
        $url = \App_DI_Container::get('ConfigObject')->corporate->url;

        $verifyEmailUrl = "/emailauthorization/updateemail/code/" . $userData['verification_code'] . "/id/" . $userData['id'];
        $fullUrl = $url . \Util::formatURL($verifyEmailUrl);

        try {
            if (\App_DI_Container::get('ConfigObject')->operation->agentsignup->sendpasswordmail == TRUE) {
                $this->getMail()->addTo($userData['email']);
                $this->getMail()->setSubject('Email verification for your Shmart! Business Corporate Account');
                $this->getMail()->setParam('first_name', $userData['first_name']);
                $this->getMail()->setParam('last_name', $userData['last_name']);
                $this->getMail()->setParam('email', $userData['email']);
                $this->getMail()->setParam('agent_code', $userData['agent_code']);
                $this->getMail()->setParam('password', $userData['password']);
                $this->getMail()->setParam('id', $userData['id']);
                $this->getMail()->setParam('verify_url', $fullUrl);
                $this->sendMail();
            }
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }
    
    
    /**
     * updatePasswordEmail
     * Send update password mail to corporate
     * @param type $userData
     * @return boolean
     */
    public function updatePasswordEmail($userData) {
        $this->setTemplate(__FUNCTION__);


        try {
            $this->getMail()->addTo($userData['email']);
            $this->getMail()->setSubject('New Password for Shmart login');
            $this->getMail()->setParam('first_name', $userData['first_name']);
            $this->getMail()->setParam('last_name', $userData['last_name']);
            $this->getMail()->setParam('password', $userData['password']);
            $this->getMail()->setParam('email', $userData['email']);
            $this->sendMail();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }
    
    
    /**
     * agentMinMaxloadMail
     * Send agent min max range mail
     * @param array $userData
     * @return boolean
     * @throws \Exception
     */
    public function corporateMinMaxLoad(array $userData) {



        $this->setTemplate(__FUNCTION__);

        try {
            $this->getMail()->addTo($userData['email']);
            $this->getMail()->setSubject('Low Balance in Account');
            $this->getMail()->setParam('name', $userData['name']);
            $this->getMail()->setParam('min', \Util::numberFormat($userData['min'], FLAG_NO));
            $this->getMail()->setParam('max', \Util::numberFormat($userData['max'], FLAG_NO));
            $this->getMail()->setParam('amt', \Util::numberFormat($userData['amt'], FLAG_NO));
            $this->getMail()->setParam('impText', \Util::getaltEmailImportant());
            $this->getMail()->setParam('disclaimerText', \Util::getaltEmailDisclaimer());
            $this->getMail()->setParam('tcText', \ Util::getEmailTC());
            $this->sendMail();

            return true;
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception($e->getMessage());
            return false;
        }
    }
    
    /**
     * agentFundRequest
     * Send agent fund request mail to agent
     * @param type $userData
     * @return boolean
     */
    public function corporateFundRequest($userData) {
        $this->setTemplate(__FUNCTION__);

        try {
            $this->getMail()->addTo($userData['email']);
            $this->getMail()->setSubject('Corporate Fund Request Intimation');
            $this->getMail()->setParam('amount', \Util::numberFormat($userData['amount'], FLAG_NO));
            $this->getMail()->setParam('comments', $userData['comments']);
            $this->getMail()->setParam('agent_code', $userData['agent_code']);
            $this->getMail()->setParam('agent_email', $userData['agent_email']);
            $this->getMail()->setParam('agent_mobile_number', $userData['agent_mobile_number']);
            $this->getMail()->setParam('onDate', \Util::getFormattedDate());
            $this->getMail()->setParam('serverName', \Util::getServerNameForCronAlert());
            $this->getMail()->setParam('impText', \Util::getaltEmailImportant());
            $this->getMail()->setParam('disclaimerText', \Util::getaltEmailDisclaimer());
            $this->sendMail();
            return true;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }
    
   /*  verificationCode
     * Send Phone verification code when Agent signs up from agent portal
     * @param type $userData
     * @return boolean
     */

    public function verificationCode($userData) {
        $this->setTemplate(__FUNCTION__);
        try {

            //Is it allowed to send SMS                
            if (\App_DI_Container::get('ConfigObject')->cardholder->reloadfund->sendsms) {
                $this->getSMS()->setParam('v_code', $userData['v_code']);
                $this->getSMS()->setParam('mobile', $userData['mobile1']);
                $this->sendSMS();
            }
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

}

