<?php
/**
 * MVC Api Handler
 * 
 * Provide functionlity to connect to mvc server and call respective methods
 * @category API
 * @author Vikram Singh <vikram@transerv.co.in>
 * @company Transerv
 */
abstract class App_Api_MVC  {

        const TPUSERID = '1';
  
       protected $_error;
       protected $_sessionKey   ='';
       protected $_client       ='';
       //SoapClient
       protected $apiSession;
       protected $_soapClient;
       protected $_response;
       private $userId = "transerv";


       private $_errorCode = array(
        '0' => 'Successful Request',
        '1‐99' => 'System Error',
        '100' => 'Invalid Protocol',
        '101' => 'Invalid Mobile number',
        '102' => 'Mobile Number is already registered',
        '104' => 'Activation code mismatch',
        '105' => 'Unable to retrieve balance',
        '106' => 'Unable to retrieve transaction History',
    );
    
    //Useless consturctor
    public function __construct() {
        $this->apiSession = new Zend_Session_Namespace('App.Api.mvc');        
    }
    
    
    public function _getErrorMessage($errorCode)
    {
       $errorCode = trim($errorCode);
       if(empty($errorCode) || $errorCode =='') {
           throw new Exception ('Processor ECS Error Code: Invalid Error code ' . $errorCode . ' provided.');
       }
       
       if(!isset($this->_errorCode[$errorCode])) {
           throw new Exception ('Processor ECS Error Code: Unknow error code ' . $errorCode . ' provided.');           
       }
       //1-99
       if($errorCode > 0 && $errorCode < 100) {
           return $this->_errorCode['1‐99'];
       }
       return $this->_errorCode[$errorCode];
       
    }

    public function getError()
    {
        return $this->_error;
    }
    
    protected function setError($msg)
    {
        $this->_error = $msg;
    }
    
    
    protected function getConfig() {
        return App_Webservice::get('mvc_auth');
    }
    
    protected function setClient($client) {
        $this->_client = $client;
    }
    
    protected function setSessionKey($sessionKey) {
        $this->_sessionKey = $sessionKey;        
        //$this->apiSession->_sessionKey = $sessionKey;        
        $apiSession = new ApiSession();
        $apiSession->updateSession(array(
            'sessionId'  => $sessionKey,
            'userId'  => TP_MVC_ID,
            'status'  => API_LOGON_SUCCESS
        ));           
    }
    
    protected function getClient() {
        return $this->_client;
    }
    
    protected function getSessionKey() {
        $apiSession = new ApiSession();
        $data = $apiSession->getLastSession(TP_MVC_ID);
        return isset($data['session_id']) ? $data['session_id'] : '';             
        //return $this->_sessionKey;
        //return $this->apiSession->_sessionKey;
    }
    
    protected function getSoapClient() {
        if(!isset($this->_soapClient) || empty($this->_soapClient)) {
           $this->createSoapClient();
        }
        return $this->_soapClient;
    }
    
    protected function createSoapClient() {
        //Get Config data        
        try {
        $config = $this->getConfig();
        //Create Soap client

        //Create Soap client
        $option = array(
            "trace"      => 1,
            "exceptions" => 0,
            "uri"        => $config['uri'],
            "location"   => $config['gateway_url'],
           // 'version'    => SOAP_1_2
        );
        
        $this->_soapClient =  new App_Api_Soap(null, $option,self::TPUSERID);        
        //echo "<pre>";print_r($this->_soapClient);exit;
        } catch (Exception $e) {
            //echo "<pre>";print_r($e);exit;
        }
    }
    
    
    protected function getUserId() {
        return $this->userId;
    }
 
    public function getLastResponse() {
        return $this->_response;
    }
    
    protected function setLastResponse($response) {
        $this->_response = $response;
    }
 
    
    protected function getLastResponseError() {
        $response = $this->getLastResponse();
        if(isset($response->errorCode) && $response->errorCode != '000') {
            return $this->_getErrorMessage($response->errorCode);
        }
        //Either Response not set or transaction was successful
        return false;
    }
    
    
    public function getCustomerType($typeInText)
    {
        if(strtolower($typeInText) == 'mvcc') return 0;
        return 1;
    }
    
    protected function getCurrentTime()
    {
        return date('Y-m-d h:m:s');
    }
    
    public function logCustomRequest($func, $request, $response )
    {
        App_Logger::apilog(array(
            'user_id'    => self::TPUSERID,
            'method'        => $func,
            'request'       => $request,
            'response'       => $response,            
        ));
        
    }
    
    public function getLastSessionID() {
        return $this->_sessionKey;       
    }
    
    
}