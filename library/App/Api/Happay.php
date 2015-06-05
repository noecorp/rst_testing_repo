<?php
/**
 * HAPPY Api Handler
 * 
 * Provide functionlity to connect to mvc server and call respective methods
 * @category API
 * @author Vikram Singh <vikram@transerv.co.in>
 * @company Transerv
 */
abstract class App_Api_Happay  {

      const TPUSERID = TP_HAPPAY_ID;
       protected $_error;
       protected $_sessionKey   ='SESSION_KEY_HAPPY';
       protected $_client       ='';
       protected $apiSession;
       protected $_soapClient;
       protected $_response;
       private $userId = "transerv";
       private $_host;
       private $_method = 'TransactionInformationResponse';


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
        $this->apiSession = new Zend_Session_Namespace('App.Api.happay');        
    }
    
    
    public function _getErrorMessage($errorCode)
    {
       $errorCode = trim($errorCode);
       if(empty($errorCode) || $errorCode =='') {
           throw new Exception ('Processor Error Code: Invalid Error code ' . $errorCode . ' provided.');
       }
       
       if(!isset($this->_errorCode[$errorCode])) {
           throw new Exception ('Processor Error Code: Unknow error code ' . $errorCode . ' provided.');           
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
        return App_Webservice::get('happay_auth');
    }
    
    protected function setClient($client) {
        $this->_client = $client;
    }
    
    protected function setSessionKey($sessionKey) {
        $this->_sessionKey = $sessionKey;        
    }
    
    protected function getClient() {
        return $this->_client;
    }
    
    protected function getSessionKey() {
        return $this->_sessionKey;
    }
    
    protected function getSoapClient() {
        if(!isset($this->_soapClient) || empty($this->_soapClient)) {
           $this->createSoapClient();
        }
        return $this->_soapClient;
    }
    
    protected function postTransaction($param) {
        
        $paramStr = $this->generateParamString($param);
        $ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,  $this->_host);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $paramStr);
	$buffer = curl_exec($ch);
        curl_close($ch);
        $request = $this->_host . '?'.$paramStr;
        $response = $buffer;
        $this->logCustomRequest($this->_method, $request, $response);
	if(empty ($buffer)) {
	    return false;
        }  else  {
	    return $this->_parseResponse($buffer);
	}
	

    }
    
    private function generateParamString($param) {
        $resp ='';
        if(!empty($param)) {
            foreach ($param as $key => $value) {
                $resp.= $key . '='.$value.'&';
            }
        }
        return $resp;
    }
    
    private function _parseResponse($sXml) {
        //$sXml = '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sas="http://api.shmart.in/"><SOAP-ENV:Body><sas:TransactionInformationResponse><return><SessionID>SESSION_KEY_HAPPY</SessionID><TxnNo>12356</TxnNo><AckNo>12f8a4f4-e4b4-11e3-8b83-22000b20ae71</AckNo><ResponseCode>0</ResponseCode><ResponseMessage>Transaction details received</ResponseMessage></return></sas:TransactionInformationResponse></SOAP-ENV:Body></SOAP-ENV:Envelope>';
        return $this->extractXML($sXml, 'TransactionInformationResponse');
    }

    protected function extractXML($xml,$method) {
        $sxml = simplexml_load_string($xml);
        $ns = $this->getNamespace($xml, $method);
        $ns = ($ns =='') ? 'sas' : $ns;
        $xpath = $ns.':'.$method;
        foreach($sxml->xpath('//'.$xpath) as $header)
        {
            $header->Message->attributes();
            return $header;
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
    
    protected function setHost($host) {
        $this->_host = $host;
    }
    
    private function getNamespace($xml, $method) {
        $a = strstr($xml, ":".$method,true);
        $b = strrpos($a, '<');
        return substr($a, $b+1);        
    }    
    
}