<?php
/**
 * Api Server Exchange
 *
 * @category App
 * @package Api_Server
 * @copyright transerv
 * @author Vikram Singh <vikram@transerv.co.in>
 */
abstract class App_ApiServer_Exchange
{
 
    public static $SUCCESS = 0;
    public static $INVALID_LOGIN = 1;
    public static $SESSION_EXPIRED = 2;
    public static $SYSTEM_ERROR = 3;
    public static $INVALID_IP = 4;
    public static $INVALID_RESPONSE = 5;
    public static $INVALID_METHOD = 6;
    
    //Messages
    //
    public static $INVALID_SESSION_EXPIRED_MSG = 'Session Expired';
    public static $INVALID_SYSTEM_ERROR_MSG = 'System Error';
    public static $INVALID_IP_MSG = 'Access Not Permitted';
    public static $INVALID_RESPONSE_MSG = 'Invalid Request';
    public static $INVALID_METHOD_MSG = 'Method Not Allowed';
    
    //public static $USER_NOT_FOUND = 7;

    const HEADER_NAMESPACE = 'ns1';
    const MESSAGE_INVALID_LOGIN = 'Invalid Login';
    
    const CUSTOMER_NOT_FOUND = '7';
    const CUSTOMER_NOT_FOUND_MSG = 'Active Customer Not Found';
    
    const BENE_CUSTOMER_NOT_FOUND_MSG = 'Active Beneficiary Not Found';
    
    
    const INVALID_PRODUCT_CODE = '7';
    const INVALID_PRODUCT_MSG = 'Invalid ProductCode';
    
    const INVALID_OTP_CODE = '119';
    const INVALID_OTP_MSG = 'Invalid OTP';
    const OTP_TYPE_LOAD = 'L';
    
    protected $_ENUM_YN = 'ENUM_YN';
    protected $_ENUM_YN_ARRAY = array('y','n');

    public $_ENUM_TYPE = 'ENUM_TYPE';
    public $_ENUM_TYPE_ARRAY = array('r','l');
    
    const FIELD_TYPE_STRING_ALPHA = 'string_al';
    const FIELD_TYPE_STRING = 'string';
    const FIELD_TYPE_ALNUM  = 'alnum';
    const FIELD_TYPE_DIGITS  = 'digit';
    const FIELD_TYPE_NUMBER = 'number';
    const FIELD_TYPE_CARDNUMBER = 'card';
    const FIELD_TYPE_CARDPACKID = 'cardpackid';
    const FIELD_TYPE_MEMBERID = 'memberid';
    const FIELD_TYPE_TITLE = 'title';
    const FIELD_TYPE_GENDER = 'sex';
    const FIELD_TYPE_EMAIL = 'email';
    const FIELD_TYPE_DOB = 'dob';
    const FIELD_TYPE_MOBILE = 'mobile';
    const FIELD_TYPE_DATETIME = 'datetime';
    const FIELD_TYPE_COUNTRY = 'country';
    const FIELD_TYPE_TRANSACTION = 'txntype';
    
    ########Transaction TYPE##########
    const TRANSACTION_TYPE_ECOMM = 'E';
    const TRANSACTION_TYPE_NORMAL = 'N';
    
    
    const FIELD_ALLOWED_CARD_DIGIT = '16';
    const FIELD_ALLOWED_CARD_PACKID_DIGIT = '20';
    
    const TYPE_CRN = 'CRN';
    const TYPE_MOB = 'MOB';
    
    public $_ENUM_DC = 'ENUM_DC';
    public $_ENUM_DC_ARRAY = array('dr','cr'); 
    
    /**
     * Validate User Login
     * 
     * @param int $userId 
     * @param int $sessionId
     * @return boolean
     */
    protected function isLogin($sessionId) {
        $apiUser = new ApiUser();
        $flg = $apiUser->validateSession($sessionId);
        if($flg) {
            return true;
        } 
        return false;
    }
            
    
    protected function login($username, $password,$tpid='') {
            $apiUser = new ApiUser();
            $arr = array(
                'username'  => $username,
                'password'  => $password,
                'ip'        => $this->getAPIUserIp()
            );
            if(!empty($tpid)) {
                $arr['tp_user_id'] =$tpid;
            }
            return $apiUser->login($arr);        
    }
    
    
    protected function logoff($sessionId) {
            $apiUser = new ApiUser();
            return $flg = $rs = $apiUser->logoff(array(
                'sessionId'  => $sessionId
            ));        
            
    }
    
   protected function chklogin($username, $password,$tpid='') {
            $apiUser = new ApiUser();
            $arr = array(
                'username'  => $username,
                'password'  => $password,
                'ip'        => $this->getAPIUserIp()
            );
            if(!empty($tpid)) {
                $arr['tp_user_id'] =$tpid;
            }
            return $apiUser->chklogin($arr);        
    }
    
    protected static function generateSuccessResponse($sessionId,$responseCode ='0', $responseMsg = 'Successful') {
        $obj = new stdClass();
        $obj->SessionID = $sessionId;
        $obj->ResponseCode = $responseCode;
        $obj->ResponseMessage = $responseMsg;
        return $obj;
    }
    
    protected static function Exception($responseMsg, $responseCode, $txnCode='') {
        $obj = new stdClass();
        if(!empty($txnCode)){
            $obj->AckNo = $txnCode;
        }
        $obj->ResponseMessage = $responseMsg;
        $obj->ResponseCode = $responseCode;
        return $obj;
    }

    protected static function generateSuccessResponsewithoutSessionID($responseCode ='0', $responseMsg = 'Successful') {
        $obj = new stdClass();
        $obj->ResponseCode = $responseCode;
        $obj->ResponseMessage = $responseMsg;
        return $obj;
    }    
    
    private function getAPIUserIp() {
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARTDED_FOR'] != '') {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
         } else {
            $ip_address = $_SERVER['REMOTE_ADDR'];
         }
         return $ip_address;
    }
    
    
    protected function validateCustAuthAllowedIP() {
        $ipArr =  App_DI_Container::get('ConfigObject')->system->api->customerauth->allowedip;
        $ipArr = $ipArr->toArray();
        $ip = $this->getAPIUserIp();
        return in_array($ip, $ipArr);
    }
    
    
    protected function validateECSAPIAllowedIP() {
        $ipArr =  App_DI_Container::get('ConfigObject')->system->api->ecsapi->allowedip;
        $ipArr = $ipArr->toArray();
        $ip = $this->getAPIUserIp();
        return in_array($ip, $ipArr);
    }
     
    

       /**
     * Extract Data from Authentication XML
     * @param type $xml
     * @return type
     */
    protected function extractDataFromAuthenticationXML($xml) {

        $sxml = simplexml_load_string($xml);
        $method= 'AuthenticationRequest';
        //$sxml = simplexml_load_string($xml);
        //foreach($sxml->xpath('//ns1:AuthenticationRequest') as $header)
        $ns = $this->getNamespace($xml, $method);
        $ns = ($ns =='') ? 'sas' : $ns;
        $xpath = $ns.':'.$method;
        foreach($sxml->xpath('//'.$xpath) as $header)        
        //foreach($sxml->xpath('//ns1:AuthenticationRequest') as $header)
        //foreach($sxml->xpath('//sas:AuthenticationRequest') as $header)
        {
            //var_export($header); // Should output 'something'.
//            print_r($header);
//            print 'SessionID : ' . $header->Message->SessionID . "\n";
//            print 'PAN : ' . $header->Message->PAN . "\n";
//            print 'Amount : ' . $header->Message->Amount . "\n";
//            print 'ExpiryDate : ' . $header->Message->ExpiryDate . "\n";
//            print 'OTP : ' . $header->Message->OTP . "\n";
            $arr = $header->Message->attributes();
            return array(
                   'MessageID'  => (string) $arr->id,
                   'PAN'        => (string) $header->Message->PAN,
                   'Amount'     => (string) $header->Message->Amount,
                   'ExpiryDate' => (string) $header->Message->ExpiryDate,
                   'OTP'        => (string) $header->Message->OTP,
                   'SessionID'  => (string) $header->Message->SessionID,
           );
        }
    }
    
    /**
     * logAuthenticationMessage
     * Log Authentication request and response
     * @param type $request
     * @param type $response
     * @param type $function
     */
    protected function logAuthenticationMessage($request,$response,$function ='AuthenticationRequest',$userId    = SOAP_SERVER_TP_ID) {
        $resArray['user_id']    = $userId;
        $resArray['method']     = $function;
        $resArray['request']    = $request;
        $resArray['response']   = $response;
        App_Logger::apilog($resArray);
    }
    
    /**
     * Filter Session ID
     * @param string $subject
     * @param string $sessionId
     * @param string $replaceWith
     * @return string
     */
    protected function filterSessionID($subject,$sessionId, $replaceWith) {
       if($sessionId == '' || $replaceWith == '') {
           //In case, if session id or replacewith is blank not valid return exsiting subject
           return $subject;
       }
       
       $search = '<SessionID>'.$sessionId.'</SessionID>';
       $replace = '<SessionID>'.$replaceWith.'</SessionID>';
       return str_replace($search, $replace, $subject);
    }
    
    
    /**
     * Filter Session ID
     * @param string $subject
     * @param string $sessionId
     * @param string $replaceWith
     * @return string
     */
    protected function filterSessionIDECS($subject,$sessionId, $replaceWith) {
       if($sessionId == '' || $replaceWith == '') {
           //In case, if session id or replacewith is blank not valid return exsiting subject
           return $subject;
       }
       
       $search = '<sessionKey>'.$sessionId.'</sessionKey>';
       $replace = '<sessionKey>'.$replaceWith.'</sessionKey>';
       return str_replace($search, $replace, $subject);
    }
    
    
    /**
     * Filter Session ID
     * @param string $subject
     * @param string $sessionId
     * @param string $replaceWith
     * @return string
     */
    protected function generateBalanceEnquiryResponse($subject,$arrFromMVC) {
       
        $sessionId           = isset($arrFromMVC['sessionKey']) ? $arrFromMVC['sessionKey'] : '';
        $echoData            = isset($arrFromMVC['echoData']) ? $arrFromMVC['echoData'] : '';
        $responseCode        = isset($subject->responseCode) ? $subject->responseCode : '';
        $msg                 = isset($subject->errorDesc) ? $subject->errorDesc : '';
        //print $responseCode."*";print '<pre>';var_dump($subject);exit;
        
        if(isset($subject->balanceInquiryList->availablebalance) && $msg =='') {
            $msg = 'Successful';
        } elseif($msg == '' && (!isset($subject->responseCode) || $subject->responseCode == '')) {
            $msg = 'Request timed out';
            $responseCode = '091';
        }     
        $availableBalance    = isset($subject->balanceInquiryList->availablebalance) ? $subject->balanceInquiryList->availablebalance : '';
        $cardnumber          = isset($subject->balanceInquiryList->cardnumber) ? $subject->balanceInquiryList->cardnumber : $arrFromMVC['cardNumber'];
      
       $balanceEnquiryResponse = '<soapenv:Envelope xmlns:soapenv ="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sas ="http://www.axiswebservice.net1.com/"><soapenv:Header/><soapenv:Body><sas:BalanceEnquiryResponse><SessionID>'.$sessionId.'</SessionID><EchoData>'.$echoData.'</EchoData><ResponseCode>'.$responseCode.'</ResponseCode><ResponseMessage>'.$msg.'</ResponseMessage><CRN>'.$cardnumber.'</CRN>';
       if($availableBalance != '') {
            $balanceEnquiryResponse .= '<AvailableBalance>'.$this->mvcAmountFilter($availableBalance).'</AvailableBalance>';
       }
       return $balanceEnquiryResponse .= '</sas:BalanceEnquiryResponse></soapenv:Body></soapenv:Envelope>';
    }
    
    
    /**
     * Filter Session ID
     * @param string $subject
     * @param string $sessionId
     * @param string $replaceWith
     * @return string
     */
    protected function generateTransactionHistoryResponse($subject,$arrFromMVC) {
        //print "<pre>";var_dump($subject);exit;
        $sessionId           = isset($arrFromMVC['SessionID']) ? $arrFromMVC['SessionID'] : '';
        $echoData            = isset($arrFromMVC['EchoData']) ? $arrFromMVC['EchoData'] : '';
        $cardnumber          = isset($arrFromMVC['cardNumber']) ? $arrFromMVC['cardNumber'] : '';
        $responseCode        = isset($subject->responseCode) ? $subject->responseCode : '';
        $msg                 = isset($subject->errorDesc) ? $subject->errorDesc : '';

        //echo "<pre>";print_r($arrFromMVC);exit;
        if(isset($subject->transactionHistory) && $msg =='' ) {
            $msg = 'Successful';
        } elseif($msg == '' && (!isset($subject->responseCode) || $subject->responseCode == '')) {
            $msg = 'Request timed out';
            $responseCode = '091';
        }
        
        $noOfRecord          = isset($subject->transactionHistory) ? count($subject->transactionHistory) : 0;
        $balanceEnquiryResponse = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sas="http://www.axiswebservice.net1.com/"><soapenv:Header/><soapenv:Body><sas:TransactionHistoryResponse><SessionID>'.$sessionId.'</SessionID><EchoData>'.$echoData.'</EchoData><ResponseCode>'.$responseCode.'</ResponseCode><ResponseMessage>'.$msg.'</ResponseMessage><CRN>'.$cardnumber.'</CRN>';
        
        if(isset($subject->transactionHistory) && $noOfRecord > 0) {
            $balanceEnquiryResponse .='<NumberOfRecords>'.$noOfRecord.'</NumberOfRecords>';
        }
        
        foreach ($subject->transactionHistory as $transaction) {
            $balanceEnquiryResponse .= '<sas:TransactionHistoryDetail><DateTime>'.$this->mvcDateFilter($transaction->txndatetime).'</DateTime><Description>'.$transaction->drcrflag.'</Description><ReferenceNumber>'.$transaction->txnlabel.'</ReferenceNumber><Amount>'.$this->mvcAmountFilter($transaction->txnamount).'</Amount></sas:TransactionHistoryDetail>';
        }
        $balanceEnquiryResponse.='</sas:TransactionHistoryResponse></soapenv:Body></soapenv:Envelope>';
        return $balanceEnquiryResponse;
    }
    
    /**
     * Filter Session ID
     * @param string $subject
     * @param string $sessionId
     * @param string $replaceWith
     * @return string
     */
    protected function generateMACardLoadResponse($subject,$arrFromMVC) {
        //print "<pre>";var_dump($subject);exit;
        $sessionId           = isset($arrFromMVC['SessionID']) ? $arrFromMVC['SessionID'] : '';
        $echoData            = isset($arrFromMVC['EchoData']) ? $arrFromMVC['EchoData'] : '';
        $cardnumber          = isset($arrFromMVC['cardNumber']) ? $arrFromMVC['cardNumber'] : '';
        $responseCode        = isset($subject->responseCode) ? $subject->responseCode : '';
        $msg                 = isset($subject->errorDesc) ? $subject->errorDesc : '';

        //echo "<pre>";print_r($arrFromMVC);exit;
        if(isset($subject->transactionHistory) && $msg =='' ) {
            $msg = 'Successful';
        } elseif($msg == '' && (!isset($subject->responseCode) || $subject->responseCode == '')) {
            $msg = 'Request timed out';
            $responseCode = '091';
        }
        
        $noOfRecord          = isset($subject->transactionHistory) ? count($subject->transactionHistory) : 0;
        $balanceEnquiryResponse = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sas="http://www.axiswebservice.net1.com/"><soapenv:Header/><soapenv:Body><sas:TransactionHistoryResponse><SessionID>'.$sessionId.'</SessionID><EchoData>'.$echoData.'</EchoData><ResponseCode>'.$responseCode.'</ResponseCode><ResponseMessage>'.$msg.'</ResponseMessage><CRN>'.$cardnumber.'</CRN>';
        
        if(isset($subject->transactionHistory) && $noOfRecord > 0) {
            $balanceEnquiryResponse .='<NumberOfRecords>'.$noOfRecord.'</NumberOfRecords>';
        }
        
        foreach ($subject->transactionHistory as $transaction) {
            $balanceEnquiryResponse .= '<sas:TransactionHistoryDetail><DateTime>'.$this->mvcDateFilter($transaction->txndatetime).'</DateTime><Description>'.$transaction->drcrflag.'</Description><ReferenceNumber>'.$transaction->txnlabel.'</ReferenceNumber><Amount>'.$this->mvcAmountFilter($transaction->txnamount).'</Amount></sas:TransactionHistoryDetail>';
        }
        $balanceEnquiryResponse.='</sas:TransactionHistoryResponse></soapenv:Body></soapenv:Envelope>';
        return $balanceEnquiryResponse;
    }
    
    
   /**
     * Extract Data from Authentication XML
     * @param type $xml
     * @return type
     */
    protected function extractDataFromBalanceXML($xml) {
        
        $method= 'callBalanceInquiry';
        $sxml = simplexml_load_string($xml);
        //foreach($sxml->xpath('//ns1:AuthenticationRequest') as $header)
        $ns = $this->getNamespace($xml, $method);
        $ns = ($ns =='') ? 'web' : $ns;
        $xpath = $ns.':'.$method;
        foreach($sxml->xpath('//'.$xpath) as $header)
        //foreach($sxml->xpath('//web:callBalanceInquiry') as $header)
        {
            $arr = $header->Message->attributes();
            return array(
                   'cardNumber'  => (string) $header->arg0->cardNumber,
                   'sessionKey'  => (string) $header->arg0->sessionKey,                    
           );
        }
    }   
    
   /**
     * Extract Data from Authentication XML
     * @param type $xml
     * @return type
     */
    protected function extractDataFromMVCBalanceXML($xml) {
        
        $method= 'BalanceEnquiry';
        $sxml = simplexml_load_string($xml);
        //foreach($sxml->xpath('//ns1:AuthenticationRequest') as $header)
        $ns = $this->getNamespace($xml, $method);
        $ns = ($ns =='') ? 'web' : $ns;
        $xpath = $ns.':'.$method;
        foreach($sxml->xpath('//'.$xpath) as $header)
        //foreach($sxml->xpath('//web:callBalanceInquiry') as $header)
        {
            $arr = $header->Message->attributes();
            return array(
                   'cardNumber'  => (string) $header->CRN,
                   'sessionKey'  => (string) $header->SessionID,                    
                   'echoData'    => (string) $header->EchoData,                    
           );
        }
    }   
    
    
   /**
     * Extract Data from Authentication XML
     * @param type $xml
     * @return type
     */
    protected function extractDataFromTransactionHistoryMVCXML($xml) {
        $method= 'TransactionHistoryEnquiry';
        $sxml = simplexml_load_string($xml);
        //foreach($sxml->xpath('//ns1:AuthenticationRequest') as $header)
        $ns = $this->getNamespace($xml, $method);
        $ns = ($ns =='') ? 'sas' : $ns;
        $xpath = $ns.':'.$method;
        foreach($sxml->xpath('//'.$xpath) as $header)
        {
            $arr = $header->Message->attributes();
            return array(
                   'cardNumber'  => (string) $header->CRN,
                   'SessionID'  => (string) $header->SessionID,                    
                   'EchoData'  => (string) $header->EchoData,                    
           );
        }
    } 
    
   /**
     * Extract Data from Authentication XML
     * @param type $xml
     * @return type
     */
    protected function extractMACardLoadRequestXML($xml) {
        $method= 'MACardloadRequest';
        $sxml = simplexml_load_string($xml);
        //foreach($sxml->xpath('//ns1:AuthenticationRequest') as $header)
        $ns = $this->getNamespace($xml, $method);
        $ns = ($ns =='') ? 'sas' : $ns;
        $xpath = $ns.':'.$method;
        foreach($sxml->xpath('//'.$xpath) as $header)
        {
            $arr = $header->Message->attributes();
            return array(
                   'MAID'           => (string) $header->MAID,
                   'SessionID'      => (string) $header->SessionID,                    
                   'HospitalMCC'    => (string) $header->HospitalMCC,                    
                   'HospitalID'     => (string) $header->HospitalID,                    
                   'HospitalTID'    => (string) $header->HospitalTID,                    
                   'Amount'         => (string) $header->Amount,                    
           );
        }
    } 
    
    
   /**
     * Extract Data from Authentication XML
     * @param type $xml
     * @return type
     */
    protected function extractCardTransactionRequestXML($xml,$method) {
        //$method= 'CardTransactionRequest';
        $sxml = simplexml_load_string($xml);
        $ns = $this->getNamespace($xml, $method);
        $ns = ($ns =='') ? 'sas' : $ns;
        $xpath = $ns.':'.$method;
        foreach($sxml->xpath('//'.$xpath) as $header)
        {
            
            $header->Message->attributes();
            return $header;
            return array(
                   'cardNumber'  => (string) $header->CRN,
                   'sessionKey'  => (string) $header->SessionID,                    
                   'echoData'    => (string) $header->EchoData,                    
           );
        }
    } 
    
   /**
     * Extract Data from Authentication XML
     * @param type $xml
     * @return type
     */
    protected function extractDataFromTransactionHistoryXML($xml) {
        $method= 'callTransactionHistory';
        $sxml = simplexml_load_string($xml);
        //foreach($sxml->xpath('//ns1:AuthenticationRequest') as $header)
        $ns = $this->getNamespace($xml, $method);
        $ns = ($ns =='') ? 'web' : $ns;
        $xpath = $ns.':'.$method;
        foreach($sxml->xpath('//'.$xpath) as $header)
        {
            $arr = $header->Message->attributes();
            return array(
                   'cardNumber'  => (string) $header->arg0->cardNumber,
                   'sessionKey'  => (string) $header->arg0->sessionKey,                    
                   'fetchFlag'   => (string) $header->arg0->fetchFlag,                    
                   'fromDate'    => (string) $header->arg0->fromDate,                    
                   'noOfTransactions'  => (string) $header->arg0->noOfTransactions,                    
                   'toDate'      => (string) $header->arg0->toDate,                    
           );
        }
    } 
    
    
   /**
     * Extract Data from Authentication XML
     * @param type $xml
     * @return type
     */
    protected function extractDataFromMiniSttXML($xml) {
        $method= 'MiniStatementRequest';
        $crn1 = strpos(strtolower($xml), '<crn>');
        $crn2 = strpos(strtolower($xml), '</crn>');
        $crn =  substr($xml, $crn1+5, $crn2-($crn1+5));
        
        //print .'**';
        //print $crn1.':'.$crn2;print $crn;exit;
        $sessionID1 = strpos(strtolower($xml), '<sessionid>');
        $sessionID2 = strpos(strtolower($xml), '</sessionid>');
        $sessionID =  substr($xml, $sessionID1+11, $sessionID2-($sessionID1+11));
        return array(
                   'cardNumber'  => (string) $crn,
                   'sessionKey'  => (string) $sessionID,                    
           );
        //exit();
        //print $crn1 . ':'.$crn2;exit;
        //$sxml = simplexml_load_string($xml);
        $sxml = new SimpleXMLElement($xml);
        //echo $xml;//exit('END');

        //echo $sxml;exit('here2');
        //foreach($sxml->xpath('//ns1:AuthenticationRequest') as $header)
        $ns = $this->getNamespace($xml, $method);
        $ns = ($ns =='') ? 'web' : $ns;
        //print $ns;exit('NS');
        $xpath = $ns.':'.$method;
        //print $xpath;
        //var_dump($sxml);//exit;        
        foreach($sxml->xpath('//'.$xpath) as $header)
        {
//            $arr = $header->Message->attributes();
//            echo '<pre>';print_r($arr);
            return array(
                   'cardNumber'  => (string) $header->CRN,
                   'sessionKey'  => (string) $header->SessionID,                    
           );
        }
    } 
    
    private function getNamespace($xml, $method) {
        $a = strstr($xml, ":".$method,true);
        $b = strrpos($a, '<');
        return substr($a, $b+1);        
    }
    
 /**
     * Extract Data from Authentication XML
     * @param type $xml
     * @return type
     */
    protected function extractDataFromECSBalanceResponseXML($xml) {
        
        $method= 'callBalanceInquiryResponse';
        $sxml = simplexml_load_string($xml);
        //foreach($sxml->xpath('//ns1:AuthenticationRequest') as $header)
        $ns = $this->getNamespace($xml, $method);
        //print $ns;exit;
        $ns = ($ns =='') ? 'ns2' : $ns;
        $xpath = $ns.':'.$method;
        //print_r($sxml->xpath('//'.$xpath));exit('12');
        foreach($sxml->xpath('//'.$xpath) as $header)
        //foreach($sxml->xpath('//web:callBalanceInquiry') as $header)
        {
            
            $arr = $header->Message->attributes();
            //print_r($arr);
            //print '<pre>**';print_r($header);exit;
            return array(
                   'cardNumber'  => (string) $header->CRN,
                   'sessionKey'  => (string) $header->SessionID,                    
                   'echoData'    => (string) $header->EchoData,                    
           );
        }
    }       
    
    private function mvcAmountFilter($amount) {
        
        if($amount =='0' || $amount =='0.00') {
            return '000';
        }
        
        if($amount > 0) {
            $amount = floatval($amount);
            $amount = $amount*100;
        }
        return $amount;
    }
    
    private function mvcDateFilter($date) {
        $date = date("Y-m-d g:i:s",strtotime($date));
        return $date;
    }
    
    
    protected function getResponseString($type) {
        
        if(empty($type)) {
            return;
        }
        $str ='';
        $host = App_DI_Container::get('ConfigObject')->api->url;
        switch ($type)
        {
            case 'header':
                $str = '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:'.self::HEADER_NAMESPACE.'="'.$host.'"><SOAP-ENV:Body>';
                break;
            
            case 'footer':
                $str = '</SOAP-ENV:Body></SOAP-ENV:Envelope>';
                break;
                
        }
        return $str;
    }
    
    protected function getMessage($cont)
    {
        return $this->getMessageArray($cont);
    }
    
    private function getMessageArray($key) {
        $msgArray = array(
            'login_failed'  => 'Invalid Login',
            'cust_not_found'  => 'Customer Not Found',
            'invalid_mobile'  => 'Mobile Not Found',
            'invalid_req_type'  => 'Invalid Request Type',
            'card_validation_failed'  => 'Invalid Card Number Provided',
            'card_pack_validation_failed'  => 'Invalid Card Pack ID Provided',
            'member_id_validation_failed'  => 'Member Id Validation failed',
            'title_validation_failed'  => 'Title Validation Failed',
            'title_invalid'  => 'Invalid Title Provided',
            'first_name_invalid'  => 'Invalid First Name Provided',
            'first_name_validation_failed'  => 'First Name Validation Failed',
            'middlename_validation_failed'  => 'Middle Name Validation Failed',
            'lastname_invalid'  => 'Invalid Last Name Provided',
            'lastname_validation_failed'  => 'Last Name Validation Failed',
            'nameoncard_validation_failed'  => 'Name On Card Validation Failed',
            'gender_invalid'  => 'Invalid Gender Provided',
            'gender_validation_failed'  => 'Gender Validation Failed',
            'dob_invalid'  => 'Invalid Date of Birth Provided',
            'dob_validation_failed'  => 'Date of Birth Validation Failed',
            'mobile_invalid'  => 'Invalid Mobile Number',
            'mobile_validation_failed'      => 'Mobile Validation Failed',
            'mobile2_validation_failed'     => 'Mobile2 Validation Failed',
            'email_validation_failed'       => 'Email Validation Failed',
            'mothermaidenname_invalid'      => 'Please provide Mother Maiden Name',
            'mothermaidenname_validation_failed'  => 'Mother Maiden Name validation failed',
            'id_proof_validation_failed'    => 'Identity Proof Type Validation failed',
            'id_proof_value_validation_failed'  => 'Identity Proof Detail Validation failed',
            'add_proof_validation_failed'   => 'Address Proof Validation Failed',
            'add_proof_value_validation_failed'  => 'Address Proof Detail Validation Failed',
            'landline_validation_failed'    => 'Landline Validation Failed',
            'address1_validation_failed'    => 'Address Line 1 validation failed',
            'address2_validation_failed'    => 'Address Line 2 validation failed',
            'city_validation_failed'        => 'City Validation Failed',
            'state_validation_failed'       => 'State Validation Failed',
            'amount_invalid'  => 'Invalid Amount Provided',
            'amount_validation_failed'  => 'Amount Validation Failed',
            'pincode_validation_failed' => 'Pincode Validation Failed',
            'invalid_bankaccount' => 'Invalid Value in Bank Account ',
            'invalid_vehicletype' => 'Invalid Vehicle Type',
            'invalid_deviceid' => 'Invalid Device ID',
            'invalid_nooffamailymember' => 'NoOfFamilyMember Validation Failed',
            'invalid_narration' => 'Invalid parameter Narration',
            'invalid_isoriginal' => 'Invalid parameter IsOriginal',
            'invalid_originalackno' => 'Invalid parameter OriginalAckNo'
        );
        if(isset($msgArray[$key])) {
            return $msgArray[$key];
        }
    }
    
    
    protected function validateProductCode($productCode) {
        $productModel = new Products();
        $rs = $productModel->isActiveProduct($productCode);
        //echo '<pre>';print_r($rs->toArray());exit;
        if(!empty($rs)) {
            return TRUE;
        }
        return FALSE;
    }
    
    protected function validateProductCodeByConst($productCode,$const) {
        $productModel = new Products();
        $rs = $productModel->isActiveProductByConst($productCode,$const);
        if(!empty($rs)) {
            return TRUE;
        }
        return FALSE;
    }
    
    protected function isValid($type, $value) {
        $flg = FALSE;
        switch ($type)
        {
            case $this->_ENUM_YN :
                $type = strtolower($type);
                $flg = in_array($value, $this->_ENUM_YN_ARRAY);
                break;
                
            case $this->_ENUM_DC :
                $type = strtolower($type);
                $value = strtolower($value);
                $flg = in_array($value, $this->_ENUM_DC_ARRAY); 
                break;
            case $this->_ENUM_TYPE :
                $type = strtolower($type);
                $value = strtolower($value);
                $flg = in_array($value, $this->_ENUM_TYPE_ARRAY); 
                break;
        }
        return $flg;
    }
    
    
    protected function fieldValidator($value,$type=  self::FIELD_TYPE_STRING, $min ='', $max= '') 
    {
        $flg = FALSE;
        switch ($type) {
            case self::FIELD_TYPE_STRING_ALPHA:
                $value = (string) $value;
                $validator = new Zend_Validate_Alpha(array('allowWhiteSpace' => TRUE));
                $value = str_replace(array('.',',','\\','/','-','#','&','(',')'),'', $value);
                if ($validator->isValid($value)) {
                   $valid  = new Zend_Validate_StringLength(array('min' => $min, 'max' => $max));
                   $flg = $valid->isValid($value);
                   
                } 
            break;
            case self::FIELD_TYPE_STRING:
                $value = (string) $value;
                $validator = new Zend_Validate_Alnum(array('allowWhiteSpace' => TRUE));
                $value = str_replace(array('.',',','\\','/','-','#','&','(',')'),'', $value);
                if ($validator->isValid($value)) {
                   $valid  = new Zend_Validate_StringLength(array('min' => $min, 'max' => $max));
                   $flg = $valid->isValid($value);
                   
                } 
            break;
            case self::FIELD_TYPE_ALNUM:
                $validator = new Zend_Validate_Alnum(array('allowWhiteSpace' => true));
                $value = str_replace(array('.',',','\\','/','-','#','&','(',')'),'', $value);
                if ($validator->isValid($value)) {
                    //$flg = TRUE;
                    $valid  = new Zend_Validate_StringLength(array('min' => $min, 'max' => $max));
                    $flg = $valid->isValid($value);
                } 
            break;
            case self::FIELD_TYPE_DIGITS:
                $validator = new Zend_Validate_Digits();
                $flg = $validator->isValid($value);
            break;
            case self::FIELD_TYPE_NUMBER:
                  $valid = new Zend_Validate_Digits();
                  if($valid->isValid($value)) {
                    $valid  = new Zend_Validate_StringLength(array('min' => $min, 'max' => $max));
                    $flg = $valid->isValid($value);
                  }
            break;
        
            case self::FIELD_TYPE_CARDNUMBER:
                  $valid  = new Zend_Validate_Between(array('min' => '1000000000000000', 'max' => '9999999999999999'));
                  $flg = $valid->isValid($value);
            break;
            case self::FIELD_TYPE_CARDPACKID:
                  $valid  = new Zend_Validate_Between(array('min' => '10000000000', 'max' => '99999999999999999999'));
                  $flg = $valid->isValid($value);
            break;
        
            case self::FIELD_TYPE_MEMBERID:
                  $valid  = new Zend_Validate_Between(array('min' => '10000', 'max' => '999999999999999'));
                  $flg = $valid->isValid($value);
            break;
        
            case self::FIELD_TYPE_TITLE:
                  $valid  = new Zend_Validate_InArray($this->fieldTypeArray('title'));
                  $flg = $valid->isValid(strtolower($value));
            break;
        
            case self::FIELD_TYPE_GENDER:
                  $valid  = new Zend_Validate_InArray($this->fieldTypeArray('gender'));
                  $flg = $valid->isValid(strtolower($value));
            break;
        
            case self::FIELD_TYPE_COUNTRY:
                  $valid  = new Zend_Validate_InArray($this->fieldTypeArray('country'));
                  $flg = $valid->isValid(strtolower($value));
            break;
        
            case self::FIELD_TYPE_TRANSACTION:
                  $valid  = new Zend_Validate_InArray($this->fieldTypeArray(self::FIELD_TYPE_TRANSACTION));
                  $flg = $valid->isValid(strtolower($value));
            break;
        
            case self::FIELD_TYPE_DOB:
                  $valid = new Zend_Validate_Date(array('format' => 'yyyy-mm-dd'));
                  $flg = $valid->isValid(strtolower($value));
            break;
        
            case self::FIELD_TYPE_DATETIME:
                  $valid = new Zend_Validate_Date(array('format' => 'yyyy-mm-dd h:i:s'));
                  $flg = $valid->isValid(strtolower($value));
            break;
        
            case self::FIELD_TYPE_MOBILE:
                  $valid = new Zend_Validate_Digits();
                  if($valid->isValid($value)) {
                    $valid  = new Zend_Validate_StringLength(array('min' => 10, 'max' => 10));
                    $flg = $valid->isValid($value);
                  }
            break;
        
            case self::FIELD_TYPE_EMAIL:
                  $valid = new Zend_Validate_EmailAddress();
                  if($valid->isValid($value)) {
                    $valid  = new Zend_Validate_StringLength(array('min' => $min, 'max' => $max));
                    $flg = $valid->isValid($value);
                  }
            break;
        
        

            default:
                break;
        }
        return $flg;
        
    }
    
    protected function fieldTypeArray($type) {
        $arr = array();
        switch ($type) {
            
            case 'title':
                $array = array('mr', 'mrs','ms');
                break;
            
            case 'gender':
                $array = array('male', 'female');
                break;
            case 'country':
                $array = array('in', 'india');
                break;
            case self::FIELD_TYPE_TRANSACTION:
                $array = array(
                    self::TRANSACTION_TYPE_ECOMM,
                    self::TRANSACTION_TYPE_NORMAL
                );
                break;
            
        }
        return $array;
    }
    
    
    public function isMVCBin($cardNumber) {
        $mvcAllowedBinsArray = Zend_Registry::get("MVC_ALLOWED_BIN");
        if(in_array($cardNumber, $mvcAllowedBinsArray)) {
            return TRUE;
        }
        return FALSE;
    }
    
    protected function isWalletAllowed($prod,$walletCode)
    {
        switch ($prod)
        {
            case BANK_RATNAKAR_PAT:
                $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_PAT);
                $pursePat = $product->purse->code->patwallet;
                if(strtolower($walletCode) == strtolower($pursePat)) {
                    return TRUE;
                }
                break;
        }
        return FALSE;
    }
    
 
    
   protected function extractDataFromECSTransactionHistoryXML($xml) {
 
        $xml = str_replace(' xmlns:ns2="http://webservice.epms.com/"', '', $xml);
        $xml = str_replace('<S:Envelope ', '<S:Envelope xmlns:ns2="http://webservice.epms.com/" ', $xml);
        //echo $a;exit;
        $response = $this->extractCardTransactionRequestXML($xml,'callTransactionHistoryResponse');    
        if(isset($response->return)) {
            return $response->return;
        }
        
        return '';
    } 

   
    protected  function generateMiniStatementXML($response, $request) {
            $ns = self::HEADER_NAMESPACE;
            $strResponse  = $this->getResponseString('header');
            $strResponse .= '<'.$ns.':MiniStatementResponse><return><SessionID>'.$request->SessionID.'</SessionID><ResponseCode>0</ResponseCode><ResponseMessage>Statement generated successfully</ResponseMessage><TxnIdentifierType>'.$request->TxnIdentifierType.'</TxnIdentifierType><MemberIDCardNo>'.$request->MemberIDCardNo.'</MemberIDCardNo>';
            if(isset($response->transactionHistory)) {
                $strResponse .= '<NumberOfRecords>'.count($response->transactionHistory).'</NumberOfRecords>';
                foreach ($response->transactionHistory as $transHis) {
                    $str .= '<'.$ns.':MiniStatementDetail><DateTime>'.$transHis->txndatetime.'</DateTime><Description>'.$transHis->txnlabel.'</Description><TxnIndicator>'.$transHis->drcrflag.'</TxnIndicator><Currency>'.$transHis->txncurrency.'</Currency><Amount>'.$transHis->txnamount.'</Amount><WalletCode></WalletCode></'.$ns.':MiniStatementDetail>';
                }
                $strResponse .= $str;
            } else {
                $strResponse .= '<NumberOfRecords>0</NumberOfRecords>';
            }
            $strResponse .= '</return></'.$ns.':MiniStatementResponse>';
            $strResponse .= $this->getResponseString('footer');
            return $strResponse;
    }
    
    protected  function generateMiniStatementXMLRAT($response, $request) {
            $ns = self::HEADER_NAMESPACE;
            $strResponse  = $this->getResponseString('header');
            $strResponse .= '<'.$ns.':MiniStatementResponse><return><SessionID>'.$request->SessionID.'</SessionID><ResponseCode>0</ResponseCode><ResponseMessage>Statement generated successfully</ResponseMessage><TxnIdentifierType>'.$request->TxnIdentifierType.'</TxnIdentifierType><MemberIDCardNo>'.$request->MemberIDCardNo.'</MemberIDCardNo>';
            if(isset($response)) {
                $strResponse .= '<NumberOfRecords>'.count($response).'</NumberOfRecords>';
                $no = 1;
                foreach ($response as $key=>$transHis) { //echo "<pre>"; print_r($transHis); exit;
                    $amount = Util::convertIntoPaisa($transHis['amount']);
                    $str .= '<'.$ns.$no.':MiniStatementDetail><DateTime>'.$transHis['txn_date'].'</DateTime><Description>'.$transHis['description'].'</Description><TxnNo>'.$transHis['txn_no'].'</TxnNo><TxnIndicator>'.strtoupper($transHis['mode']).'</TxnIndicator><Currency>INR</Currency><Amount>'.$amount.'</Amount><WalletCode>'.$transHis['wallet_code'].'</WalletCode></'.$ns.$no.':MiniStatementDetail>';
                    $no +=1;
                    
                }
                $strResponse .= $str;
            } else {
                $strResponse .= '<NumberOfRecords>0</NumberOfRecords>';
            }
            $strResponse .= '</return></'.$ns.':MiniStatementResponse>';
            $strResponse .= $this->getResponseString('footer');
            //echo $strResponse; exit;
            return $strResponse;
    }
    
    protected  function generateBeneficiaryListXMLRAT($response, $request) {
            $beneCount = count($response);
            $baseTxn = new BaseTxn();
            $txnCode = $baseTxn->generateTxncode();
               
            $ns = self::HEADER_NAMESPACE;
            $no = 1;
            $strResponse  = $this->getResponseString('header');
            $strResponse .= '<'.$ns.':QueryBeneficiaryListResponse><return><AckNo>'.$txnCode.'</AckNo><RemitterCode>'.$request->RemitterCode.'</RemitterCode><BeneficiaryCount>'.$beneCount.'</BeneficiaryCount><ResponseCode>0</ResponseCode><ResponseMessage>Query Beneficiary List Successfully</ResponseMessage>';
            if(isset($response)) {
                foreach ($response as $key=>$beneInfo) { //echo "<pre>"; print_r($beneInfo); exit;
                    $str .= '<'.$ns.$no.':BeneficiaryDetail><BeneficiaryCode>'.$beneInfo['bene_code'].'</BeneficiaryCode><FirstName>'.$beneInfo['first_name'].'</FirstName><LastName>'.$beneInfo['last_name'].'</LastName><BankName>'.$beneInfo['bank_name'].'</BankName><BankIfscode>'.$beneInfo['ifsc_code'].'</BankIfscode><BankAccountNumber>'.$beneInfo['bank_account_number'].'</BankAccountNumber></'.$ns.$no.':BeneficiaryDetail>';
                
                    $no +=1;
                    
                }
                $strResponse .= $str;
            }
            $strResponse .= '</return></'.$ns.':QueryBeneficiaryListResponse>';
            $strResponse .= $this->getResponseString('footer');
           // echo $strResponse; exit;
            return $strResponse;
    }

    
     protected  function generateWalletsBalanceXMLRAT($response, $request) {
            
            $ns = self::HEADER_NAMESPACE;
            $no = 1;
            $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->TxnIdentifierType = (string) trim($resp->TxnIdentifierType);
                $responseObj->MemberIDCardNo = (string) trim($resp->MemberIDCardNo);
            $strResponse  = $this->getResponseString('header');
            $strResponse .= '<'.$ns.':BalanceEnquiryResponse><return><SessionID>'.$request->SessionID.'</SessionID><TxnIdentifierType>'.$request->TxnIdentifierType.'</TxnIdentifierType><MemberIDCardNo>'.$request->MemberIDCardNo.'</MemberIDCardNo>';
            if(isset($response)) {
                foreach ($response as $key=>$balInfo) { //echo "<pre>"; print_r($beneInfo); exit;
                    $amount = Util::convertIntoPaisa($balInfo['sum']);
                    $str .= '<'.$ns.$no.':WalletBalanceDetail><WalletCode>'.$balInfo['code'].'</WalletCode><AvailableBalance>'.$amount.'</AvailableBalance></'.$ns.$no.':WalletBalanceDetail>';
                    $no +=1;
                   
                }
                $strResponse .= $str;
            }
            $strResponse .= '<Currency>INR</Currency><ResponseCode>0</ResponseCode><ResponseMessage>Successfully retrieved the Balance</ResponseMessage>';
            $strResponse .= '</return></'.$ns.':BalanceEnquiryResponse>';
            $strResponse .= $this->getResponseString('footer');
           // echo $strResponse; exit;
            return $strResponse;
    }


    public static function chkallowChar($code) {
        if(empty($code)) {
            return TRUE;
        }
       if(preg_match('/^[a-zA-Z0-9 !@#$%.?|,;*-=_()+\/[\]{}:]+$/', $code)) {
          return true; 
        }
        return false;
    }
    
    protected  function generateBeneficiaryListXMLKTK($response, $request) {
            $beneCount = count($response);
            $baseTxn = new BaseTxn();
            $txnCode = $baseTxn->generateTxncode();
               
            $ns = self::HEADER_NAMESPACE;
            $no = 1;
            $strResponse  = $this->getResponseString('header');
            $strResponse .= '<'.$ns.':QueryBeneficiaryListResponse><return><AckNo>'.$txnCode.'</AckNo><RemitterCode>'.$request->RemitterCode.'</RemitterCode><BeneficiaryCount>'.$beneCount.'</BeneficiaryCount><ResponseCode>0</ResponseCode><ResponseMessage>Query Beneficiary List Successfully</ResponseMessage>';
            if(isset($response)) {
                foreach ($response as $key=>$beneInfo) { //echo "<pre>"; print_r($beneInfo); exit;
                    $str .= '<'.$ns.$no.':BeneficiaryDetail><BeneficiaryCode>'.$beneInfo['bene_code'].'</BeneficiaryCode><FirstName>'.$beneInfo['name'].'</FirstName><LastName>'.$beneInfo['last_name'].'</LastName><BankName>'.$beneInfo['bank_name'].'</BankName><BankIfscode>'.$beneInfo['ifsc_code'].'</BankIfscode><BankAccountNumber>'.$beneInfo['bank_account_number'].'</BankAccountNumber></'.$ns.$no.':BeneficiaryDetail>';
                
                    $no +=1;
                    
                }
                $strResponse .= $str;
            }
            $strResponse .= '</return></'.$ns.':QueryBeneficiaryListResponse>';
            $strResponse .= $this->getResponseString('footer');
           // echo $strResponse; exit;
            return $strResponse;
    }
}
