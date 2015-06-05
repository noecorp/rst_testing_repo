<?php

/**
 * Authentificator
 * 
 * Create and send message for keep alive session
 * 
 * @category API
 * @author Vikram Singh <Vikram@transerv.co.in>
 */
class App_Api_MVC_Authentificator extends App_Api_MVC {

    private $attempts = 0;
    private $maxAttempts = 3;    
    
    public function __construct() {
        parent::__construct();
    }
    
    
    /**
     * createNewSession
     * 
     * App_Api_Exception exception. Returns void.
     *
     * @return \App_Api_ECS_Authentificator
     * */
    public function createNewSession() {
        
        
        try {
        
        $config = App_Webservice::get('mvc_auth');
        
        //Validate webservice config
        
        if(empty($config['gateway_url']) || $config['gateway_url'] == '') {
            throw new App_Api_Exception ("No gateway specified");
        }
        
        if(empty($config['auth_user']) || $config['auth_user'] == '') {
            throw new App_Api_Exception ("MVC Authentificator: No username defined.");
        }        
        
        if(empty($config['auth_pass']) || $config['auth_pass'] == '') {
            throw new App_Api_Exception ("MVC Authentificator: No password defined.");
        }        
        
        if(empty($config['auth_method']) || $config['auth_method'] == '') {
            throw new App_Api_Exception ("MVC Authentificator: Login method not defined.");
        }        

        $client = $this->getSoapClient();

        $method = $config['auth_method'];

        $param = array(
            'username' => $config['auth_user'],
            'password' => $config['auth_pass']
            );

        $response = $client->soapCall($method, $param);
        
        
        //Validate Response
        if($response === false) {
            //$client->getErrorMsg() -- Timout in case of failuer
            //$client->__errorMsg  need to update with $client->getErrorMsg()
            //print 'Setting Error : ' . $client->_errorMsg . '<br />';
            $this->setError($client->_errorMsg);
            return false;
        }
        if(isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
            //echo "<pre>";print_r($response);
            //Successful Login
            $this->setSessionKey($response['SessionID']);
            //$this->setClient($response->clientId);
            return true;
        } else {
            if ($this->attempts < $this->maxAttempts) {
                $this->attempts++;
                return $this->createNewSession();
            }            
        }
        //Setup Param
        //$sessionKey = $response['SessionID'];
        //$this->setSessionKey($sessionKey);
        $this->setError("Unable to login into MVC");
        return false;
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;           // echo "<pre>";print_r($e);
            
        }
        //exit('END');
        
    }
    
    
    /**
     * initSession
     * Validate and return new or validated session
     * @return \App_Api_ECS_Authentificator
     */
    public function initSession() {

        $config = App_Webservice::get('mvc_auth');
        $sessionKey = $this->getSessionKey();
        if((!isset($sessionKey) || $sessionKey =='')) {
            //Invalid response - init new session
            return $this->createNewSession();
        }
        //print $sessionKey;
        //exit;

        $client = $this->getSoapClient();        
        $method = $config['auth_validate_method'];

        // Now check of the method/pm match
        //$response = $client->$method($this->getSessionKey(),$this->getCurrentTime());
        $param = array(
            'SessionID' => $this->getSessionKey(),
            'DateTime'  => $this->getCurrentTime()
        );
        $response = $client->soapCall($method, $param);
        $this->setLastResponse($response);

        if(isset($response) && !empty($response)) {
            if (date('Y-m-d H:i:s', strtotime($response)) == $response) {
                return true;
            }
        }
        //} else {        
        return $this->createNewSession();
        //}
    }    
    
    
    /**
     * initSession
     * Validate and return new or validated session
     * @return \App_Api_ECS_Authentificator
     */
    public function validateSessionByCron() {

        $config = App_Webservice::get('mvc_auth');
        $sessionKey = $this->getSessionKey();
        if((!isset($sessionKey) || $sessionKey =='')) {
            //Invalid response - init new session
            return $this->createNewSession();
        }
        //print $sessionKey;
        //exit;

        $client = $this->getSoapClient();        
        $method = $config['auth_validate_method'];

        // Now check of the method/pm match
        //$response = $client->$method($this->getSessionKey(),$this->getCurrentTime());
        $param = array(
            'SessionID' => $this->getSessionKey(),
            'DateTime'  => $this->getCurrentTime()
        );
        $response = $client->soapCall($method, $param);
        $this->setLastResponse($response);
        //$flg = false;
        if(isset($response) && !empty($response)) {
            if (date('Y-m-d H:i:s', strtotime($response)) == $response) {
                return true;
            }
//            list($yy,$mm,$dd)=explode("-",$response);
//            list($d,$tt)=explode(" ",$dd);
//            if(is_numeric($yy) && is_numeric($mm) && is_numeric($d)) {
//                if(checkdate($mm,$d,$yy)) {
//                    //$flg = true;
//                    return true;
//                }
//            }
            //print $yy . ' : ' . $mm. ' : ' . $d . ' * ' .$tt. PHP_EOL;
            //print checkdate($mm,$d,$yy);             
            //Successful Login
            //return true;
        } 
        //if($flg == false) {
        return $this->createNewSession();
        //}
        //else {        
            //var_dump($response);
            //print 'Failed**';
            //var_dump($client->__getLastResponse());
            //return $this->createNewSession();
        //}
    }    
    
//    public function getSoapClient() {
//        if(isset($this->_client)) {
//            return $this->_client;
//        }
//        return $this->createClient();
//        
//    }
    
//    private function setSoapClient($client) {
//        $this->_client = $client;
//    }
//    
//    public function getSessionKey()
//    {
//        $apiSession = new Zend_Session_Namespace('App.Api');
//        return $apiSession->_sessionKey;
//    }
//    
//    public function setSessionKey($sessionKey)
//    {
//        //Store in Session
//        $apiSession = new Zend_Session_Namespace('App.Api');
//        $apiSession->_sessionKey = $sessionKey;
//        //return self::$_sessionKey;
//    }
//    
    
    
//    private function createClient() {
//        
//        //Need to setup singltone Object
//        //Get Config data
//        $config = App_Webservice::get('mvc_auth');
//        //Create Soap client
//        $option = array(
//            "trace"      => 1,
//            "exceptions" => 0,
//            "uri"        => $config['uri'],
//            "location"   => $config['gateway_url'],
//            'version'    => SOAP_1_2
//        );
//        
//        $client = new App_Api_Soap(null, $option,self::TPID);        
//        $this->setSoapClient($client);
//        return $client;
//        
//    }
    
//    protected function _getError($errorCode) {
//        $mvcErrorHandler = new App_Api_MVC_ErrorCode();
//        return $mvcErrorHandler->_getError($errorCode);
//    }
//    
//    protected function setLastResponse($response)
//    {
//        $this->_response = $response;
//    }
//    
//    public function getLastResponse()
//    {
//        return $this->_response;
//    }
    
}
