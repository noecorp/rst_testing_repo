<?php
/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_Services extends App_ApiServer_Exchange
{
    private $_soapServer;
    const TP_ID = TP_MVC_ID;
    
    const OTP_INVALID_RESPONSE_CODE = '115';
    const OTP_INVALID_RESPONSE_MSG = 'INVALID OTP';    
    
    const AUTH_SUCC_RESPONSE_CODE = '0';
    const AUTH_SUCC_RESPONSE_MSG = 'Transaction Approved';    
    
    
    /**
     * Constructor
     * @param type $server
     */
    public function __construct($server) {
        $this->_soapServer = $server;
    }

    /**
     * 
     * @param string $username
     * @param string $password
     */
    public function Login($username, $password) {
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            $flg = parent::login($resp->Username, $resp->Password, self::TP_ID);
            if ($flg) {
                return self::generateSuccessResponse($flg, self::$SUCCESS);
            }
            return self::Exception("Invalid Login", self::$INVALID_LOGIN);
        } catch (App_Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());
            App_Logger::log($e->getMessage(), Zend_Log::ERR); //exit;
            return self::Exception("Invalid Login", self::$INVALID_LOGIN);
        }
    }
    
   
    /**
     * 
     * @param string $sessionId
     * @return date
     * @throws App_ApiServer_Exception
     */
    public function EchoMessage($sessionId) {
       $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);        
       $sxml = $this->_soapServer->getLastRequest();
       $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);        
       if(!$this->isLogin($resp->SessionID)) {
            return self::Exception(self::MESSAGE_INVALID_LOGIN, App_ApiServer_Exchange::$INVALID_LOGIN);
        }
        return self::generateSuccessResponse($sessionId);

    }
    /**
     * 
     * @param string $sessionId
     * @throws App_ApiServer_Exception
     */
    public function Logoff($sessionId) {
       $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
       $sxml = $this->_soapServer->getLastRequest();
       $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);        
        $flg = parent::logoff($resp->SessionID);
        if($flg) {
            return self::generateSuccessResponsewithoutSessionID();
        }
        return self::Exception('Invalid SessionID', '101');
        
    }
    
    /**
     * SendMail
     * @param string SessionID
     * @param string Email
     * @param string Subject
     * @param string Body
     * @param string UserType
     * @param string UserID
     * @return array
     */
    public function SendMail($sessionId, $email, $subject, $body, $userType, $userId) {
        
       if(!$this->isLogin($sessionId)) {
            return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
        }
        
        try {
        
        //Validate template
        
        $m = new App\Messaging\MVC\Axis\Operation();
        $m->apiMail($email, $subject, $body);
        
        
        return $this->generateSuccessResponse($sessionId);
        
        //return true;//Return soap response
        } catch (Exception $e) { 
            //LOG ERROR
            self::Exception("System Error", self::$SYSTEM_ERROR);
        }
        //return response
        return $this->generateSuccessResponse($sessionId,App_ApiServer_Exchange::$SUCCESS);
        //return true;//Return succ soap response
        
    }
    
    
    /**
     * 
     * @param string $sessionId
     * @param Long $messageId
     * @param string $pan
     * @param string $amount
     * @param string $expiryDate
     * @param string $otp
     * @return array
     */
    //public function CardholderAuthentication($message) {
    public function CardholderAuthentication($sessionId, $messageId, $pan, $amount, $expiryDate, $otp) {
        
        //Removing Login Validation as per Aniket Request - 14-12-2012
       //if(!$this->isLogin($sessionId)) {
           // return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
        //}
        
        try {
            
        //Initiate Process
        
        //Validate messageID
        $validator = App_Validator::getInstanceByName(App_Validator::NOTEMPTY);
        if ($validator->isValid($messageId) !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' Empty Value provided');
        }             
        
        //Validate PAN
        $validator = App_Validator::getInstanceByName(App_Validator::NOTEMPTY);
        if ($validator->isValid($messageId) !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' Empty Value provided');
        }             
        
        
        //Validate AMOUNT
        $validator = App_Validator::getInstanceByName(App_Validator::FLOAT);
        if ($validator->isValid($amount) !== true) {
              return $this->Exception(App_Api_MVC_Consts::API_CUSTOMERAUTH_PARAM_AMOUNT_INVALID_MSG, App_Api_MVC_Consts::API_CUSTOMERAUTH_PARAM_AMOUNT_INVALID_CODE);
        }             
        
        
        //Validate ExpiryDate
        $validator = App_Validator::getInstanceByName(App_Validator::DATE,  array(App_Validator::TYPE_FORMAT =>'mmyy'));
        if ($validator->isValid($expiryDate) !== true) {
              return $this->Exception(App_Api_MVC_Consts::API_CUSTOMERAUTH_PARAM_EXPIRYDATE_INVALID_MSG, App_Api_MVC_Consts::API_CUSTOMERAUTH_PARAM_EXPIRYDATE_INVALID_CODE);
        }             
        
        
        //Validate OTP
        $validator = App_Validator::getInstanceByName(App_Validator::DIGITS);
        if ($validator->isValid($otp) !== true) {
              //return $this->Exception(App_Api_MVC_Consts::API_CUSTOMERAUTH_PARAM_OTP_INVALID_MSG, App_Api_MVC_Consts::API_CUSTOMERAUTH_PARAM_OTP_INVALID_CODE);
              return $this->Exception(App_Api_MVC_Consts::API_CUSTOMERAUTH_PARAM_OTP_INVALID_MSG, $otp);
        }             
        
        
            
    //MVC Registration
        $array = array(
            App_Api_MVC_Consts::API_CUSTOMERAUTH_PARAM_MESSAGEID    =>  $messageId, 
            App_Api_MVC_Consts::API_CUSTOMERAUTH_PARAM_PAN          =>  $pan,
            App_Api_MVC_Consts::API_CUSTOMERAUTH_PARAM_AMOUNT       =>  $amount, 
            App_Api_MVC_Consts::API_CUSTOMERAUTH_PARAM_EXPIRYDATE   =>  $expiryDate, 
            App_Api_MVC_Consts::API_CUSTOMERAUTH_PARAM_OTP          =>  $otp
        );
        
        //Cardholder registration with MVC
        //try {
            $mvc = new App_Api_MVC_Transactions();
            $flg = $mvc->CustomerAuthentication($array);
            /*if($flg !== true) {
                //print 'Failed';exit;
            }*/
            //Return response if irrespect of its status
            $response = $mvc->getLastResponse();
            exit;
            //echo "<pre>123";print_r($response);
            return $response;
            exit;
        
        //return true;//Return soap response
        } catch (Exception $e) { 
            //LOG ERROR
            self::Exception("System Error", self::$SYSTEM_ERROR);
        }
        //return response
        return $this->generateSuccessResponse($sessionId, $response['ResponseCode'], $response['ResponseMessage']);
        //return $this->generateSuccessResponse($sessionId,App_ApiServer_Exchange::$SUCCESS);
        //return true;//Return succ soap response
    }
    
    
/*    public function AuthenticationRequest($obj) {//Do not add comments for method summary
        try {
            //Not using incoming object instead using Last Request method of server

            
            //Not Validating System Data as per requirement
            //App_DI_Container::get('ConfigObject')->system->api->timeout_in_min;
            if(!$this->validateCustAuthAllowedIP()) {
                return self::Exception("System Error", self::$INVALID_IP);
            }

            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml,__FUNCTION__);
            
            //Get Last Request XML
            //$sxml = $this->_soapServer->getLastRequest();
            //Filter & extract data from xml
            //$resp = $this->extractDataFromAuthenticationXML($sxml);
            //Creating MVC API Object
            //echo 'HHH';exit;
            $pan = (string) $resp->Message->PAN;
            $bin = substr($pan, 0,6);
            if(parent::isMVCBin($bin)) {
//                echo 'MVC BIN';exit;
                $mvc = new App_Api_MVC_Transactions();
                $flg = $mvc->CustomerAuthentication($resp);
                $response=  $mvc->getLastResponse();
                $custAuthSessionId = $mvc->getCustomerAuthSesionId();
                $response = $this->filterSessionID($response, $custAuthSessionId, $resp['SessionID']);
                $this->logAuthenticationMessage($sxml, $response);
                //return the response and terminate the script, So Server will not able to generate its response 
                //Setup Header as not returning as part of application
                header ("Content-Type: text/xml; charset=utf-8");  
                print $response;exit;//DO NOT DELETE THIS LINE
            } else {
                
            
                 $sxml = $this->_soapServer->getLastRequest();
                 $resp = $this->extractCardTransactionRequestXML($sxml,__FUNCTION__);
                $object = new CustomerTrack();                
                $param = array(
                    'card_number'        => (string) $resp->Message->PAN,
                    //'product_id'         => (string) $resp->ProductCode,
                );
                $customerInfo = $object->getCustomerDetails($param);

                if(empty($customerInfo)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->Message->SessionID;
                    $responseObj->AckNo          = '';
                    $responseObj->ResponseCode   = self::CUSTOMER_NOT_FOUND;
                    $responseObj->ResponseMessage= self::CUSTOMER_NOT_FOUND_MSG;                    
                    $this->buildSResponse($responseObj);
                    //return $responseObj;                                 
                }
                //echo $customerInfo['product_id'];
                //echo 'HHH';exit;
                $otpVeriParam = array(
                  'product_id'      => $customerInfo['product_id'],
                  'customer_id'     =>  $customerInfo['customer_id'],
                  'otp'             =>  (string) $resp->Message->OTP,
                  //'amount'             =>  (string) $resp->Message->Amount,
                  'request_type'    =>  'L'
                );
                
               $ref = new Reference();
               //$otpResponse = $object->verifyCustomerOTP($otpVeriParam);
               $otpResponse = $ref->verifyCustomerLoadOTP($otpVeriParam);
                if($otpResponse == FALSE) {
                    $responseObj = new stdClass();                                      
                    $responseObj->SessionID      = (string) $resp->Message->SessionID;
                    $responseObj->ResponseCode   = self::OTP_INVALID_RESPONSE_CODE;
                    $responseObj->ResponseMessage= self::OTP_INVALID_RESPONSE_MSG;
                    $this->buildSResponse($responseObj);
                } else {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->Message->SessionID;                    
                    $responseObj->ResponseCode = self::AUTH_SUCC_RESPONSE_CODE;
                    $responseObj->ResponseMessage = self::AUTH_SUCC_RESPONSE_MSG;
                    $this->buildSResponse($responseObj);
                }                               
                return $responseObj;
            }
            //return $response;
        } catch (Exception $e) {
            //echo "<pre>";print_r($e);exit;
            App_Logger::log(serialize($e), Zend_Log::ERR);
            return $this->Exception("System Error", self::$INVALID_RESPONSE);
        }
    }*/

public function AuthenticationRequest($obj) {//Do not add comments for method summary
        try {
            //Not using incoming object instead using Last Request method of server


            //Not Validating System Data as per requirement
            //App_DI_Container::get('ConfigObject')->system->api->timeout_in_min;
            if(!$this->validateCustAuthAllowedIP()) {
                return self::Exception("System Error", self::$INVALID_IP);
            }

            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml,__FUNCTION__);

            //Get Last Request XML
            //$sxml = $this->_soapServer->getLastRequest();
            //Filter & extract data from xml
            $resp = $this->extractDataFromAuthenticationXML($sxml);
            //Creating MVC API Object
            //echo 'HHH';exit;
            //$pan = (string) $resp->Message->PAN;
            $pan = (string) $resp['PAN'];
            $bin = substr($pan, 0,6);
            if(parent::isMVCBin($bin)) {
//                echo 'MVC BIN';exit;
                $mvc = new App_Api_MVC_Transactions();
                $flg = $mvc->CustomerAuthentication($resp);
                $response=  $mvc->getLastResponse();
                $custAuthSessionId = $mvc->getCustomerAuthSesionId();
                $response = $this->filterSessionID($response, $custAuthSessionId, $resp['SessionID']);
                $this->logAuthenticationMessage($sxml, $response);
                //return the response and terminate the script, So Server will not able to generate its response
                //Setup Header as not returning as part of application
                header ("Content-Type: text/xml; charset=utf-8");
                print $response;exit;//DO NOT DELETE THIS LINE
            } else {


                 $sxml = $this->_soapServer->getLastRequest();
                 $resp = $this->extractCardTransactionRequestXML($sxml,__FUNCTION__);
                $object = new CustomerTrack();
                $param = array(
                    'card_number'        => (string) $resp->Message->PAN,
                    //'product_id'         => (string) $resp->ProductCode,
                );
                $customerInfo = $object->getCustomerDetails($param);
                
          
                if(empty($customerInfo)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->Message->SessionID;
                    $responseObj->AckNo          = '';
                    $responseObj->ResponseCode   = self::CUSTOMER_NOT_FOUND;
                    $responseObj->ResponseMessage= self::CUSTOMER_NOT_FOUND_MSG;
                    $this->buildSResponse($responseObj);
                    //return $responseObj;
                }
                //echo $customerInfo['product_id'];
                //echo 'HHH';exit;
/*                $otpVeriParam = array(
                  'product_id'      => $customerInfo['product_id'],
                  'customer_id'     =>  $customerInfo['customer_id'],
                  'otp'             =>  (string) $resp->Message->OTP,
                  'request_type'    =>  'L'
                );

               $this->logAuthenticationMessage($sxml, serialize($otpVeriParam),__FUNCTION__);            
               $otpResponse = $object->verifyCustomerOTP($otpVeriParam);

  */
           $otpVeriParam = array(
                  'product_id'      => $customerInfo['product_id'],
                  'customer_id'     =>  $customerInfo['customer_id'],
                  'otp'             =>  (string) $resp->Message->OTP,
                  //'amount'             =>  (string) $resp->Message->Amount,
                  'request_type'    =>  'L'
                );

               $ref = new Reference();
               //$otpResponse = $object->verifyCustomerOTP($otpVeriParam);
               $otpResponse = $ref->verifyCustomerLoadOTP($otpVeriParam);


               //$otpResponse = TRUE;
               //echo $otpResponse;exit;
                if($otpResponse == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->Message->SessionID;
                    $responseObj->ResponseCode   = self::OTP_INVALID_RESPONSE_CODE;
                    $responseObj->ResponseMessage= self::OTP_INVALID_RESPONSE_MSG;
                    $this->buildSResponse($responseObj);
                } else {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->Message->SessionID;
                    $responseObj->ResponseCode = self::AUTH_SUCC_RESPONSE_CODE;
                    $responseObj->ResponseMessage = self::AUTH_SUCC_RESPONSE_MSG;
                    $this->buildSResponse($responseObj);
                }
                return $responseObj;
            }
            //return $response;
        } catch (Exception $e) {
            //echo "<pre>";print_r($e);exit;
            App_Logger::log(serialize($e), Zend_Log::ERR);
            return $this->Exception("System Error", self::$INVALID_RESPONSE);
        }
    }


    
    
    public function callBalanceInquiry($obj) {//Do not add comments for method summary
        
        try {
            //Not using incoming object instead using Last Request method of server

            //Not Validating System Data as per requirement
            //App_DI_Container::get('ConfigObject')->system->api->timeout_in_min;
            if(!$this->validateECSAPIAllowedIP()) {
               return self::Exception("System Error", self::$INVALID_IP);
            }

            //Get Last Request XML
            $sxml = $this->_soapServer->getLastRequest();
            App_Logger::log($sxml, Zend_Log::ERR);//exit;
            //print $sxml;exit;
            //App_Logger::log($sxml, Zend_Log::ERR);exit;
            //print $sxml.'--';exit;
            //Filter & extract data from xml
            $resp = $this->extractDataFromBalanceXML($sxml);
            //print_r($resp);exit;
            //Creating MVC API Object
            $ecsApi = new App_Api_ECS_Transactions();
            //print_r($resp);exit;
            $response = $ecsApi->balanceInquiryFromMVC(array('cardNumber' => $resp['cardNumber']));            
            //exit;
            //$resp = $ecsApi->getLastResponse();
            //print $resp;exit;
            //Calling MVC API
            //$flg = $mvc->CustomerAuthentication($resp);
            
            //Sending response back irrespect of their status
            //$response = $ecsApi->getLastResponse();
            //print_r($resp);exit;
            $response = $this->filterSessionIDECS($response, $ecsApi->getLastSessionID(), $resp['sessionKey']);
            
            $this->logAuthenticationMessage($sxml, $response,'callBalanceInquiry');
            //return the response and terminate the script, So Server will not able to generate its response 
            //Setup Header as not returning as part of application
            header ("Content-Type: text/xml; charset=utf-8");  
            print $response;
            exit;//DO NOT DELETE THIS LINE
            //return $response;
        } catch (Exception $e) {
            //echo "<pre>";print_r($e);exit;
            App_Logger::log(serialize($e), Zend_Log::ERR);
            return $this->Exception("System Error", self::$INVALID_RESPONSE);
        }
    }
    
    
  public function callTransactionHistory($obj) {//Do not add comments for method summary
         
        try {
            //Not using incoming object instead using Last Request method of server

            //Not Validating System Data as per requirement
            //App_DI_Container::get('ConfigObject')->system->api->timeout_in_min;
            if(!$this->validateECSAPIAllowedIP()) {
                return self::Exception("System Error", self::$INVALID_IP);
            }

            //Get Last Request XML
            $sxml = $this->_soapServer->getLastRequest();
            App_Logger::log($sxml, Zend_Log::ERR);//exit;
            //print $sxml;exit;
            //App_Logger::log($sxml, Zend_Log::ERR);exit;
            //print $sxml.'--';exit;
            //Filter & extract data from xml
            $resp = $this->extractDataFromTransactionHistoryXML($sxml);
            //print_r($resp);exit;
            //Creating MVC API Object
            $ecsApi = new App_Api_ECS_Transactions();
            //print_r($resp);exit;
            $response = $ecsApi->transactionHistoryFromMVC(array(
                    'cardNumber' => $resp['cardNumber'],
                    'fetchFlag'  => $resp['fetchFlag'],
                    'fromDate'   => $resp['fromDate'],
                    'noOfTransactions' => $resp['noOfTransactions'],
                    'toDate' => $resp['toDate'],
                )
             );            
            //exit;
            //$resp = $ecsApi->getLastResponse();
            //print $resp;exit;
            //Calling MVC API
            //$flg = $mvc->CustomerAuthentication($resp);
            
            //Sending response back irrespect of their status
            //$response = $ecsApi->getLastResponse();
            //print_r($resp);exit;
            $response = $this->filterSessionIDECS($response, $ecsApi->getLastSessionID(), $resp['sessionKey']);
            
            $this->logAuthenticationMessage($sxml, $response,'callTransactionHistory');
            //return the response and terminate the script, So Server will not able to generate its response 
            //Setup Header as not returning as part of application
            header ("Content-Type: text/xml; charset=utf-8");  
            print $response;
            exit;//DO NOT DELETE THIS LINE
            //return $response;
        } catch (Exception $e) {
            //echo "<pre>";print_r($e);exit;
            App_Logger::log(serialize($e), Zend_Log::ERR);
            return $this->Exception("System Error", self::$INVALID_RESPONSE);
        }
    }
    
    public function __call($name, $arguments) {
        
        App_Logger::log('Invalid Method called : '.$name, Zend_Log::ERR);
        App_Logger::log(serialize($arguments), Zend_Log::ERR);
        return self::Exception("System Error", self::$INVALID_METHOD);
    }
    
    
    public function BalanceEnquiry($obj) {//Do not add comments for method summary
        
        try {
            //Not using incoming object instead using Last Request method of server


            //Not Validating System Data as per requirement
            //App_DI_Container::get('ConfigObject')->system->api->timeout_in_min;
            if(!$this->validateECSAPIAllowedIP()) {
               return self::Exception("System Error", self::$INVALID_IP);
            }

            //Get Last Request XML
            $sxml = $this->_soapServer->getLastRequest();
            //App_Logger::log($sxml, Zend_Log::ERR);//exit;
            //Filter & extract data from xml
            $resp = $this->extractDataFromMVCBalanceXML($sxml);

            //Creating MVC API Object
            $ecsApi = new App_Api_ECS_Transactions();
App_Logger::log('LOG 1', Zend_Log::ERR);
            $response = $ecsApi->balanceInquiryFromMVC(array('cardNumber' => $resp['cardNumber']));   
App_Logger::log('LOG 2 ', Zend_Log::ERR);
            //$this->logAuthenticationMessage($sxml, $response,'BalanceEnquiry');
             
           //$respXML = $ecsApi->getLastResponse();
            //print '<pre>';print_r($respXML);exit;
            $response = $this->generateBalanceEnquiryResponse($response, $resp);
            //print htmlentities($response);exit;
            $this->logAuthenticationMessage($sxml, $response,'BalanceEnquiry');
            //return the response and terminate the script, So Server will not able to generate its response 
            //Setup Header as not returning as part of application
            header ("Content-Type: text/xml; charset=utf-8");  
            print $response;
            exit();//DO NOT DELETE THIS LINE
            //return $response;
        } catch (Exception $e) {
            
            App_Logger::log(serialize($e), Zend_Log::ERR);
            //echo "<pre>";print_r($e);exit;
            return $this->Exception("System Error", self::$INVALID_RESPONSE);
        }
    }    
    
    
/*    public function TransactionHistoryEnquiry ($obj) {//Do not add comments for method summary
        
        try {
            //Not using incoming object instead using Last Request method of server

            //Not Validating System Data as per requirement
            //App_DI_Container::get('ConfigObject')->system->api->timeout_in_min;
            if(!$this->validateECSAPIAllowedIP()) {
               return self::Exception("System Error", self::$INVALID_IP);
            }

            //Get Last Request XML
            $sxml = $this->_soapServer->getLastRequest();
            //print htmlentities($sxml);//exit;
            //App_Logger::log($sxml, Zend_Log::ERR);//exit;
            $resp = $this->extractDataFromTransactionHistoryMVCXML($sxml);
            //$resp = $this->extractDataFromTransactionHistoryXML($sxml);
            //Creating MVC API Object
            $ecsApi = new App_Api_ECS_Transactions();
            $response = $ecsApi->transactionHistoryFromMVC(array(
                    'cardNumber' => $resp['cardNumber'],
                    'fetchFlag'  => '1',
                    'fromDate'   => '',
                    'noOfTransactions' => '5',
                    'toDate' => '',
                )
             );         
            $response = $ecsApi->getLastResponse();
            $resp = $this->extractDataFromECSTransactionHistoryXML($sxml);
            $this->logAuthenticationMessage($sxml, $response,'TransactionHistoryEnquiry');            
            //$response = $this->extractCardTransactionRequestXML($response, __FUNCTION__);            
            //$this->logAuthenticationMessage($sxml, $response,'TransactionHistoryEnquiry');            
            $response = $this->generateTransactionHistoryResponse($response, $resp);
            $this->logAuthenticationMessage($sxml, $response,'TransactionHistoryEnquiry');
            //return the response and terminate the script, So Server will not able to generate its response 
            //Setup Header as not returning as part of application
            header ("Content-Type: text/xml; charset=utf-8");  
            print $response;
            exit;//DO NOT DELETE THIS LINE
            //return $response;
        } catch (Exception $e) {
            //echo "<pre>";print_r($e);exit;
            App_Logger::log(serialize($e), Zend_Log::ERR);
            return $this->Exception("System Error", self::$INVALID_RESPONSE);
        }
    }    */

    public function TransactionHistoryEnquiry ($obj) {//Do not add comments for method summary

        try {
            //Not using incoming object instead using Last Request method of server

            //Not Validating System Data as per requirement
            //App_DI_Container::get('ConfigObject')->system->api->timeout_in_min;
            if(!$this->validateECSAPIAllowedIP()) {
               return self::Exception("System Error", self::$INVALID_IP);
            }

            //Get Last Request XML
            $sxml = $this->_soapServer->getLastRequest();
            //print htmlentities($sxml);//exit;
            //App_Logger::log($sxml, Zend_Log::ERR);//exit;
            $resp = $this->extractDataFromTransactionHistoryMVCXML($sxml);

            //Creating MVC API Object
            $ecsApi = new App_Api_ECS_Transactions();
            $response = $ecsApi->transactionHistoryFromMVC(array(
                    'cardNumber' => $resp['cardNumber'],
                    'fetchFlag'  => '1',
                    'fromDate'   => '',
                    'noOfTransactions' => '5',
                    'toDate' => '',
                )
             );
            $response = $ecsApi->getLastResponse();
           //$this->logAuthenticationMessage($sxml, $response,'TransactionHistoryEnquiry');

//App_Logger::log($response, 6);
           // $response = $this->extractDataFromTransactionHistoryXML($response, 'callTransactionHistoryResponse');
//          $response = $this->extractDataFromTransactionHistoryXML($response);
            $response = $this->extractDataFromECSTransactionHistoryXML($response);
//App_Logger::log($response, 6);
            //$this->logAuthenticationMessage($sxml, $response->transactionHistory,'TransactionHistoryEnquiry');
            $response = $this->generateTransactionHistoryResponse($response, $resp);
//App_Logger::log($response, 6);
            $this->logAuthenticationMessage($sxml, $response,'TransactionHistoryEnquiry');
            //return the response and terminate the script, So Server will not able to generate its response
            //Setup Header as not returning as part of application
            header ("Content-Type: text/xml; charset=utf-8");
            print $response;
            exit;//DO NOT DELETE THIS LINE
            //return $response;
        } catch (Exception $e) {
            //echo "<pre>";print_r($e);exit;
            App_Logger::log(serialize($e), Zend_Log::ERR);
            return $this->Exception("System Error", self::$INVALID_RESPONSE);
        }
    }



    
    private function buildSResponse($obj) {
        $str = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sas="http://api.shmart.in/"><soapenv:Header/><soapenv:Body><sas:AuthenticationResponse><Message id="123456">';
        foreach ($obj as $key => $value) {
            $st = $st. '<'.$key.'>'.$value.'</'.$key.'>';
        }
        $str = $str . $st;
        $str = $str . '</Message></sas:AuthenticationResponse></soapenv:Body></soapenv:Envelope>';
        header ("Content-Type: text/xml; charset=utf-8");  
        echo $str;
        exit;
    }
    
}


/*
 * 0	Successful Request
1-99	System Error 
100	Invalid Protocol
101	Invalid Mobile number
102	Mobile Number is already registered
104	Activation code mismatch
105	Unable to retrieve balance
106	Unable to retrieve transaction History
TBD	TBD

 */
