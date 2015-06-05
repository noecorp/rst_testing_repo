<?php
/**
 * ECS API
 * 
 * Base class for ECS API Request and response
 */
abstract class App_Api_ECS {

       const TPUSERID = '2';
       protected $_error;
       protected $_sessionKey   ='';
       protected $_client       ='';
       //SoapClient
       protected $apiSession;
       protected $_soapClient;
       private $userId = "transerv";


       private $_errorCode = array(
        '05' => 'Lost Card',
        '06' => 'Stolen Card',
        '07' => 'Counterfeit Card',
        '08' => 'Returned Card',
        '12' => 'Expired Card',
        '01' => 'Found Card',
        '03' => 'Institutions Decision',
        '06' => 'Miscellaneous',
        '0' => 'Not Activated',
        '1' => 'Activated',
        '050' => 'Error in allocating the buffer',
        '051' => 'Error in tpacall',
        '052' => 'Error in tpinit/tpalloc',
        '053' => 'Error in tpinit/tpalloc',
        '100' => 'Database Error',
        '101' => 'Query Failed',
        '102' => 'No Matching records',
        '110' => 'Invalid ID, Authkey and Channel combination',
        '111' => 'Invalid ID, Sessionkey and Channel combination',
        '112' => 'Deactivated Account',
        '113' => 'Invalid Cardnumber/Passcode',
        '114' => 'Error in generating STAN',
        '000' => 'Financial transaction has been approved',
        '001' => 'Honor With Id',
        '100' => 'Do Not Honor',
        '101' => 'Expired Card',
        '104' => 'Restricted Card',
        '107' => 'Error',
        '108' => 'Refer Issuer Special Conditions',
        '109' => 'Invalid Merchant',
        '111' => 'INVALID CARD NUMBER',
        '114' => 'Invalid account number',
        '115' => 'Requested function not supported (First two digits of processing code or Function code is invalid)',
        '116' => 'Insufficient funds',
        '118' => 'Card Record does not exist',
        '119' => 'Transaction not permitted to card holder',
        '121' => 'Withdrawal amount limit exceeded.',
        '123' => 'Withdrawal Frequency Exceeded',
        '125' => 'Card Not Effective',
        '180' => 'Transfer Limit Exceeded',
        '184' => 'Capture Card',
        '200' => 'Capture Card',
        '201' => 'Expired Card Capture',
        '202' => 'Suspected Fraud_202',
        '203' => 'Acceptor Contact Acquirer',
        '204' => 'Restricted Card Capture',
        '205' => 'Acceptor Call Acquirer',
        '206' => 'Pin Try Exceeded',
        '207' => 'Special Conditions',
        '208' => 'Lost Card',
        '209' => 'Stolen Card',
        '210' => 'Suspected Counterfeit Card_210',
        '307' => 'Format Error',
        '902' => 'Invalid transaction (Invalid function code within network management messages)',
        '904' => 'Format Error (Any format related errors etc)',
        '907' => 'Card issuer inoperative (Sent when cannot access customer account holding server/database or server has logged off or it is in fallback mode and financial transactions are not allowed)',
        '909' => 'System malfunction (Sent for errors like database corrupted)',
        '911' => 'Card issuer timed out',
        '912' => 'Bank Host not available',
        '913' => 'Duplicate transaction Id',
        '926' => 'Invalid Number',
        '927' => 'Invalid Amount',
        '992' => 'Sender Not Found',
        '994' => 'Impossible Transaction Treatment'
    );
    
    //Useless consturctor
    public function __construct() {
        $this->apiSession = new Zend_Session_Namespace('App.Api');        
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
        return App_Webservice::get('ecs_auth');
    }
    
    protected function setClient($client) {
        $this->_client = $client;
    }
    
    protected function setSessionKey($sessionKey) {
        $this->_sessionKey = $sessionKey;        
        $this->apiSession->_sessionKey = $sessionKey;    
        
        $apiSession = new ApiSession();
        $apiSession->updateSession(array(
            'sessionId'  => $sessionKey,
            'userId'  => TP_ECS_API_ID,
            'status'  => API_LOGON_SUCCESS
        ));        
        
        
    }
    
    protected function getClient() {
        return isset($this->_client) ? $this->_client : '';
    }
    
    public function getSessionKey() {
        //return $this->_sessionKey;
        //return $this->apiSession->_sessionKey;
        $apiSession = new ApiSession();
        $data = $apiSession->getLastSession(TP_ECS_API_ID);
        return $data['session_id'];        
    }
    
    protected function getSoapClient() {
        if(!isset($this->_soapClient) || empty($this->_soapClient)) {
           $this->createSoapClient();
        }
        return $this->_soapClient;
    }
    
    protected function createSoapClient() {
        $config = $this->getConfig();
        //Create Soap client
        //$this->_soapClient = new App_Api_Soap(null, array(
        $this->_soapClient = new App_Api_ZendSoap(null, array(
            'location'  => $config['location'],
            'uri'  => $config['uri'],
            //'version' => SOAP_1_1,
        ), self::TPUSERID);
//        if($this->_soapClient === false) {
//            //$this->setError($this->_so)
//        }
        $this->_soapClient->setSoapVersion(SOAP_1_1);
    }
    
    
    protected function getUserId() {
        return isset($this->userId) ? $this->userId : '';
    }
 
    public function getLastResponse() {
        return isset($this->_response) ? $this->_response : '';
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
    
    public function getLastSessionID() {
        return $this->_sessionKey;       
    }
    
    /**
     * getTitleCode
     * Function to get TitleCode on the basis of title
     * Function build as per ECS Document
     * @param string $title
     * @return string $code
     */
    protected function getTitleCode($title) {
        if($title == '') return '';
        $code ='';
        $title = strtolower($title);
        switch ($title) {
            case 'mr':
                $code = 1;
                break;
            case 'mrs':
                $code = 2;
                break;
            case 'miss':
                $code = 3;
                break;
            case 'dr':
                $code = 4;
                break;
            case 'chief':
                $code = 5;
                break;
            case 'sir':
                $code = 6;
                break;
            case 'ms':
                $code = 7;
                break;

            default:
                $code = '';
                break;
        }
        return $code;
    }
    
    
    protected function getAPISessionKeyFromDB() {
        $apiSession = new ApiSession();
        $data = $apiSession->getLastSession(TP_ECS_API_ID);
        return $data['session_id'];
    }
    
}