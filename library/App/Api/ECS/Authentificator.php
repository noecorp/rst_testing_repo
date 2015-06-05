<?php
class App_Api_ECS_Authentificator extends App_Api_ECS {

    private $attempts = 0;
    private $maxAttempts = 3;    
    
    public function __construct() {
        //Calling Parent Constructor
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
            
        $config = $this->getConfig();

        //Validate webservice config
        if(empty($config['gateway_url']) || $config['gateway_url'] == '') {
            //throw new App_Api_Exception ("No gateway specified");
        }
        
        if(empty($config['auth_channel']) || $config['auth_channel'] == '') {
            //throw new App_Api_Exception ("No Channel Id specified");
        }

        if(empty($config['auth_key']) || $config['auth_key'] == '') {
            //throw new App_Api_Exception ("No Channel Id specified");
        }        
        
        if(empty($config['auth_method']) || $config['auth_method'] == '') {
           // throw new App_Api_Exception ("No Channel Id specified");
        }        

        $paramArray = $this->getParamArray('','101');        
        $paramArray['inOut']            =      "";
        
        

        
        //$this->setClient($client);
        //$method = $config['auth_login'];
        $client = $this->getSoapClient();
        //ID for the session
        //$id = 'ABCD';//Where to get this key
        // Now check of the method/pm match
        //$response = $client->$method($id,$config['auth_key'],$config['auth_channel']);

        $response = $client->ecsSoapCall($config['auth_login'], $paramArray);
        
        if(isset($response->responseCode) && $response->responseCode =='0') {
            //Successful Login
            $this->setSessionKey($response->sessionKey);
            $this->setClient($response->clientId);
            return true;
        } else {
            if ($this->attempts < $this->maxAttempts) {
                $this->attempts++;
                return $this->createNewSession();
            }            
        }
        return false;
        
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
            //print 'Message: ' . $e->getMessage();
        }        
    }
    
    
    /**
     * initSession
     * Validate and return new or validated session
     * @return \App_Api_ECS_Authentificator
     */
    public function initSession() {

        $sessionKey = $this->getSessionKey();
        //print $sessionKey;//exit;
        //$sessionKey = $this->getAPISessionKeyFromDB();        
        if($sessionKey == '' || empty($sessionKey)) {
            //Invalid response - init new session
            return $this->createNewSession();
        }

        $config = $this->getConfig();

        // Now check of the method/pm match
        $paramArray = $this->getParamArray($sessionKey,'103');
        
        
        $soapClient = $this->getSoapClient();
        $response = $soapClient->ecsSoapCall($config['auth_echo'], $paramArray);
        if(isset($response->responseCode) && $response->responseCode =='0') {
            $this->setSessionKey($sessionKey);            
            return true;
        } else {
            return $this->createNewSession();
        }
    }   
    
    
     /**
     * initSession
     * Validate and return new or validated session
     * @return \App_Api_ECS_Authentificator
     */
    public function logoff() {

        $sessionKey = $this->getSessionKey();
        if($sessionKey == '' || empty($sessionKey)) {
            //Invalid response - init new session
            return $this->createNewSession();
        }

        $config = $this->getConfig();

        // Now check of the method/pm match
        $paramArray = $this->getParamArray($sessionKey,'103');
        
        
        $soapClient = $this->getSoapClient();
        $response = $soapClient->ecsSoapCall($config['auth_echo'], $paramArray);
        if(isset($response->responseCode) && $response->responseCode =='0') {
            return true;
        } else {
            return $this->createNewSession();
        }
    }   
    
    
    protected function filterAmount($amt) {
        return $amt * 100;
    }
    
    
      /**
     * initSession
     * Validate and return new or validated session
     * @return \App_Api_ECS_Authentificator
     */
    public function initSessionByCron() {

        //$sessionKey = $this->getSessionKey();
        $sessionKey = $this->getAPISessionKeyFromDB();
        if($sessionKey == '' || empty($sessionKey)) {
            return $this->createNewSession();
        }

        $config = $this->getConfig();

        // Now check of the method/pm match
        $paramArray = $this->getParamArray($sessionKey,'103');
        
        
        $soapClient = $this->getSoapClient();
        $response = $soapClient->ecsSoapCall($config['auth_echo'], $paramArray);
        if(isset($response->responseCode) && $response->responseCode =='0') {
            return true;
        } else {
            return $this->createNewSession();
        }
    }   
    
    
    /**
     * getParamArray
     * Function used to get Param for ECHO and LOGON messages
     * @param type $sessionKey
     * @param type $serviceCode
     * @return type
     */
    private function getParamArray($sessionKey, $serviceCode='103') {
        
        $paramArray = array();
        $paramArray['cardNumber'] = '';
        $paramArray['channel'] = 'IVR';
        $paramArray['componentAuthKey'] = '';
        $paramArray['componentId'] = '';
        $paramArray['expiryDate'] = '';
        $paramArray['ip']           =         Util::getIP();
        $paramArray['passCodeFlag'] = 'N';
        $paramArray['passCodeValue'] = '';
        $paramArray['password'] = '';
        $paramArray['requestDateTime'] = '';
        $paramArray['serviceCode'] = $serviceCode;
        $paramArray['sessionKey'] = $sessionKey;
        $paramArray['terminalType'] = '';
        $paramArray['txnPassFlag'] = '';
        $paramArray['txnPassword'] = '';
        $paramArray['userId'] = $this->getUserId();
        
        return $paramArray;
    }
    
}
