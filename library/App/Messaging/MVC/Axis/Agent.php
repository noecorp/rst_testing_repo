<?php
namespace App\Messaging\MVC\Axis;

/**
 * Description of Operation
 *
 * @author Vikram
 */
class Agent extends \App\Messaging\MVC\Axis {

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
                $this->getSMS()->setParam('mobile', $userData['mobile1']);
                $this->getSMS()->setParam('showBceContract', $userData['showBceContract']);
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
                $this->getMail()->setParam('email', $userData['email']);
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

    /*
     * Cardholder reload Auth
     */

    public function cardholderFundLoadAuth($userData, $resend = FALSE) {
        $this->setTemplate(__FUNCTION__);

        try {
            // mobile duplicacy check
            $session = new \Zend_Session_Namespace('App.Agent.Controller');
            $mobObj = new \Mobile();
            $mobNo = isset($userData['mobile1']) ? $userData['mobile1'] : '';
            if ($resend) {
                $userData['auth_code'] = $session->ch_fund_load_auth;
            } else {
                $userData['auth_code'] = $this->generateRandom6DigitCode();
            }
            if ($mobNo == '') {
                throw new Exception("No mobile number provided");
            }



            // Sending sms to cardholer for fund load auth code              
            if (\App_DI_Container::get('ConfigObject')->cardholder->loadbalance->sendsms) {



                $this->getSMS()->setParam('auth_code', $userData['auth_code']);
                $this->getSMS()->setParam('mobile', $userData['mobile1']);
                $this->getSMS()->setParam('account_name', $userData['account_name']);
                $this->getSMS()->setParam('amount', $userData['amount']);
                $this->getSMS()->setParam('currency', $userData['currency']);
                $this->sendSMS();
                $session->ch_fund_load_auth = $userData['auth_code'];
            }
        } catch (Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception($e->getMessage());
            //return false;
        }
    }

    /*
     * Card load failure
     */

    public function cardholderLoadFundFailure($userData) {
        $this->setTemplate(__FUNCTION__);
        try {

            //Is it allowed to send SMS                
            if (\App_DI_Container::get('ConfigObject')->cardholder->reloadfund->sendsms) {
                $this->getSMS()->setParam('account_name', $userData['account_name']);
                $this->getSMS()->setParam('mobile', $userData['mobile1']);
                $this->sendSMS();
            }
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /*
     * Cardholder Activation/balance email and SMS
     */

    public function cardholderBalance($userData) {
        $this->setTemplate(__FUNCTION__);
        try {



            if ($userData['ecsStatus'] == FLAG_SUCCESS) {
                if (\App_DI_Container::get('ConfigObject')->cardholder->loadbalance->sendmail) {
                    $mailSubject = 'Welcome to ' . $userData['program_name'] . ' Program';
                    $this->getMail()->addTo($userData['email']);
                    $this->getMail()->setSubject($mailSubject);
                    $this->getMail()->setParam('cardholder_name', $userData['cardholder_name']);
                    $this->getMail()->setParam('product_name', $userData['product_name']);
                    $this->sendMail();
                }
            }

            //Is it allowed to send SMS                
            if (\App_DI_Container::get('ConfigObject')->cardholder->loadbalance->sendsms) {

                $this->getSMS()->setParam('mobile', $userData['mobile1']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->getSMS()->setParam('call_centre_number', $userData['call_centre_number']);
                $this->getSMS()->setParam('customer_support_email', $userData['customer_support_email']);
                $this->getSMS()->setParam('ecsStatus', $userData['ecsStatus']);
                $this->sendSMS();
            }
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception($e->getMessage());
            //return false;
        }
    }

    /* termsConditionAuth() that function will send the terms n condition code to user
     * will accept the userData and module name
     */

    public function termsconditionsAuth($userData) {
        $this->setTemplate(__FUNCTION__);


        try {
            $session = new \Zend_Session_Namespace('App.Agent.Controller');

            // For Cardholder Module
            // Sending sms to cardholer for terms n condition auth code              
            if (\App_DI_Container::get('ConfigObject')->cardholder->termsconditionsauth->sendsms) {
                $mailSubject = 'Terms and Condition Authorization Code';
                $this->getMail()->addTo($userData['email']);
                $this->getMail()->setSubject($mailSubject);
                $this->getMail()->setParam('cardholder_name', $userData['cardholder_name']);
                $this->getMail()->setParam('termsconditions_auth', $userData['termsconditions_auth']);
                $this->getMail()->setParam('product_name', $userData['product_name']);
                $flgMail = $this->sendMail();
            }

            // Sending sms to cardholer for terms n condition auth code              
            if (\App_DI_Container::get('ConfigObject')->cardholder->termsconditionsauth->sendmail) {
                $this->getSMS()->setParam('mobile', $userData['mobile1']);
                $this->getSMS()->setParam('termsconditions_auth', $userData['termsconditions_auth']);
                $this->getSMS()->setParam('product_name', $userData['product_name']);
                $this->sendSMS();
            }


            $session->termscondition_auth = $userData['termsconditions_auth'];
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception($e->getMessage());
            //return false;
        }
    }

    /*     * verificationCode
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

    /**
     * agentCreation() mail sent to admin on agent creation
     * @param array $userData
     * @return boolean
     */
    public function agentCreation($userData) {
        $this->setTemplate(__FUNCTION__);

        $admin_mail = \App_DI_Container::get('ConfigObject')->system->notifications->recipients;
        $to_mail = $admin_mail->toArray();
        $userData['adminmail'] = $to_mail['0'];
        try {
            if (\App_DI_Container::get('ConfigObject')->operation->agentsignup->sendpasswordmail) {
                $this->getMail()->addTo($userData['adminmail']);
                $this->getMail()->setSubject('New Agent account has been created');
                $this->getMail()->setParam('name', $userData['name']);
                $this->getMail()->setParam('email', $userData['email']);
                $this->getMail()->setParam('mobile', $userData['mobile1']);
                $this->getMail()->setParam('agent_code', $userData['agent_code']);
                $this->sendMail();
            }
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /*     * agentCardholderReload
     * Send email to agent on cardholder reload failure
     * @param array $userData
     * @return boolean
     */

    public function agentCardholderReload($userData) {
        $this->setTemplate(__FUNCTION__);


        try {

            $this->getMail()->addTo($userData['email']);
            $this->getMail()->setSubject('Card NOT credited');
            $this->getMail()->setParam('name', $userData['name']);
            $this->getMail()->setParam('amount', $userData['amount']);
            $this->getMail()->setParam('endChars', $userData['endChars']);
            $this->getMail()->setParam('balance', $userData['balance']);
            $this->getMail()->setParam('dateTime', \Util::getCurrDateTime(FLAG_NO));
            $this->sendMail();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * updatePasswordEmail
     * Send update password mail to agent
     * @param type $userData
     * @return boolean
     */
    public function updatePasswordEmail($userData) {
        $this->setTemplate(__FUNCTION__);


        try {
            $this->getMail()->addTo($userData['email']);
            $this->getMail()->setSubject('New Password for Shmart!Pay login');
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
     * agentFundRequest
     * Send agent fund request mail to agent
     * @param type $userData
     * @return boolean
     */
    public function agentFundRequest($userData) {
        $this->setTemplate(__FUNCTION__);

        try {
            $this->getMail()->addTo($userData['email']);
            $this->getMail()->setSubject('Agent Fund Request Intimation');
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

    /*     * agentLowBalance
     * Send agent low balance mail to Agent
     * @param array $userData
     * @return boolean
     * @throws \Exception
     */

    public function agentLowBalance(array $userData) {
        $this->setTemplate(__FUNCTION__);


        try {
            if (\App_DI_Container::get('ConfigObject')->agent->minbal->sendmail) {//Is it allowed to send email 
                $this->getMail()->addTo($userData['email']);
                $this->getMail()->setSubject('Low Balance in Account');
                $this->getMail()->setParam('agent_name', $userData['agent_name']);
                $this->getMail()->setParam('current_balance', $userData['current_balance']);
                $this->getMail()->setParam('agent_minimum_balance', $userData['agent_minimum_balance']);
                $this->sendMail();

                return true;
            }
        } catch (\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            throw new \Exception($e->getMessage());
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
    public function agentMinMaxLoad(array $userData) {



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
     * agentEmailVerification() sends email verification link to 
     * agent on being approved by Operation user
     * @param array $userData
     * @return boolean
     */
    public function agentEmailVerification($userData) {
        $this->setTemplate(__FUNCTION__);
        $url = \App_DI_Container::get('ConfigObject')->agent->url;

        $verifyEmailUrl = "/emailauthorization/index/code/" . $userData['verification_code'] . "/id/" . $userData['id'];
        $fullUrl = $url . \Util::formatURL($verifyEmailUrl);

        try {
            if (\App_DI_Container::get('ConfigObject')->operation->agentsignup->sendpasswordmail == TRUE) {
                $this->getMail()->addTo($userData['email']);
                $this->getMail()->setSubject('Email verification for your Shmart! Business Partner Account');
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
     * agentAuthEmailVerification() sends email verification link to 
     * agent on being approved by Operation user
     * @param array $userData
     * @return boolean
     */
    public function agentAuthEmailVerification($userData) {
        $this->setTemplate(__FUNCTION__);
        $url = \App_DI_Container::get('ConfigObject')->agent->url;
        $verifyEmailUrl = "/authemailauthorization/index/code/" . $userData['verification_code'] . "/id/" . $userData['id'];
        $fullUrl = $url . \Util::formatURL($verifyEmailUrl);

        try {
            if (\App_DI_Container::get('ConfigObject')->operation->agentsignup->sendpasswordmail == TRUE) {
                $this->getMail()->addTo($userData['email']);
                $this->getMail()->setSubject('Secondary Email verification for your Shmart! Business Partner Account');
                $this->getMail()->setParam('first_name', $userData['first_name']);
                $this->getMail()->setParam('last_name', $userData['last_name']);
                $this->getMail()->setParam('email', $userData['auth_email']);
                $this->getMail()->setParam('agent_code', $userData['agent_code']);
//                $this->getMail()->setParam('password',$userData['password']);
                $this->getMail()->setParam('id', $userData['id']);
                $this->getMail()->setParam('verify_url', $fullUrl);
                $this->sendMail();
            }
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

}

