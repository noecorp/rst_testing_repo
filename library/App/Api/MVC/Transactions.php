<?php
class App_Api_MVC_Transactions extends App_Api_MVC_Authentificator {

    
    private $custAuthSessionId;


    public function sendDownloadLink($mobile)
    {
        $mobile = trim($mobile);
        try {
            
            $config = $this->getConfig();        
            $method = $config['send_download_link'];
            //print 'PRE<br />';
            $resp = $this->initSession();

            if($resp === false) {
                return false;
            }
            //exit;
            $soapClient = $this->getSoapClient();
            $sessionKey = $this->getSessionKey();

            $param = array(
                'SessionID' => $sessionKey,
                'MobileNumber' => $mobile,
            );
            //exit("PRE");
            $response = $soapClient->soapCall($method, $param);
            $this->setLastResponse($response);

            if(isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
                //Successful Login
                //$this->setSessionKey($response->sessionKey);
                return true;
            } 
            if($response === false) {
                //print $soapClient->_errorMsg;exit;
                $this->setError($soapClient->errorMsg);
                //print 'ERROR';exit;
                return false;
            }
            //echo "<pre>";print_r($response);exit;
            if(empty($response) || $response == '') {
                throw new Exception (__CLASS__.": Invalid response received from server");
            }

            if(!isset($response['ResponseCode']) || $response['ResponseCode'] == '') {
                throw new Exception (__CLASS__.": Empty response code");
            }

            //$mvcErrorHandler = new App_Processor_MVC_ErrorCode();
            //$this->setError($this->_getErrorMessage($response['ResponseCode']));
        $responseMessage = isset($response['ResponseMessage']) ? $response['ResponseMessage'] : '';
        $this->setError($responseMessage);

        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
        return false;
        //echo "<pre>";print_r($response);
                
    }
    
    
    
    public function Registration($userArray)
    {
        $config = App_Webservice::get('mvc_auth');        
        $method = $config['auth_custromer_registration_method'];
        //$authentification = new App_Api_MVC_Authentificator();
        $resp = $this->initSession();
        //Login Response
        if($resp === false) {
            return false;
        }        
        //exit('Registration -- END');
        $soapClient = $this->getSoapClient();
        $sessionKey = $this->getSessionKey();
        
        //Param Validation
                
                
        if(empty($userArray['CAFNumber']) || $userArray['CAFNumber'] == '') {
            throw new Exception ("MVC Input: Invalid CAF number provided");
        }                
        
        if(empty($userArray['FirstName']) || $userArray['FirstName'] == '') {
            throw new Exception ("MVC Input: Invalid First Name provided");
        }                
        
        if(empty($userArray['LastName']) || $userArray['LastName'] == '') {
            throw new Exception ("MVC Input: Invalid Last Name provided");
        }                
        
        if(empty($userArray['MobileNumber']) || $userArray['MobileNumber'] == '') {
            throw new Exception ("MVC Input: Invalid Mobile number provided");
        }                
        
        /*if(empty($userArray['DeviceID']) || $userArray['DeviceID'] == '') {
            throw new Exception ("MVC Input: Invalid Device ID number provided");
        } */               
        
        if(empty($userArray['CRN']) || $userArray['CRN'] == '') {
            throw new Exception ("MVC Input: Invalid CRN number provided");
        }                
        
        if(empty($userArray['CustommerType']) || $userArray['CustommerType'] == '') {
            throw new Exception ("MVC Input: Invalid Custommer type provided");
        }                
                
        //$userArray      
        
        $accountAlias = 'VCPay';//Need to move this to config
        $customerType = $this->getCustomerType($userArray['CustommerType']);
        
        //$response = $soapClient->$method($sessionKey,$userArray['CAFNumber'],$userArray['FirstName'],$userArray['LastName'],$userArray['MobileNumber'],$userArray['DeviceID'],$userArray['CRN'],$accountAlias,$customerType);
        $param = array(
            'SessionID' => $sessionKey,
            'CAFNumber' => $userArray['CAFNumber'],
            'FirstName' => $userArray['FirstName'],
            'LastName' => $userArray['LastName'],
            'MobileNumber' => $userArray['MobileNumber'],
            'DeviceID' => isset($userArray['DeviceID'])?$userArray['DeviceID']:'',
            'CRN' => $userArray['CRN'],
            'AccountAlias' => $accountAlias,
            'CustomerType' => $customerType
        );
        $response = $soapClient->soapCall($method, $param);
        $this->setLastResponse($response);
        

        if(isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
            //Successful Login

            return true;
        }         
        
        if(empty($response) || $response == '') {
            throw new Exception ("MVC: Invalid response received from server");
        }
        
        if(!isset($response['ResponseCode']) || $response['ResponseCode'] == '') {
            throw new Exception ("MVC: Empty response code");
        }
        
        
        //$this->setError($this->_getErrorMessage($response['ResponseCode']));
        $responseMessage = isset($response['ResponseMessage']) ? $response['ResponseMessage'] : '';
        $this->setError($responseMessage);

        
        return false;
                
    }
    
    
    
    public function getAccountInfo($userArray)
    {
        $config = App_Webservice::get('mvc_auth');        
        $method = $config['account_info'];
        //$authentification = new App_Api_MVC_Authentificator();
        $resp = $this->initSession();
        if($resp === false) {
            return false;
        }
        //exit('Registration -- END');
        $soapClient = $this->getSoapClient();
        $sessionKey = $this->getSessionKey();
        //print $this->getSessionKey();
        //Param Validation
                
                
        if(empty($userArray['CRN']) || $userArray['CRN'] == '') {
            throw new Exception ("MVC Input: Invalid CRN number provided");
        }                
                
        //$response = $soapClient->$method($sessionKey,$userArray['CAFNumber'],$userArray['FirstName'],$userArray['LastName'],$userArray['MobileNumber'],$userArray['DeviceID'],$userArray['CRN'],$accountAlias,$customerType);
        $param = array(
            'SessionID' => $sessionKey,
            'CRN' => $userArray['CRN'],
        );
        //echo "<pre>";print_r($data);
        $response = $soapClient->soapCall($method, $param);
        
        $this->setLastResponse($response);        
        
        if(isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
            return true;
        }
        if(isset($response['ResponseCode']) || $response['ResponseCode'] == 0) {
            return true;
        }
        
        if($response == false) {
            $this->setError($soapClient->_errorMsg);
            return false;
        }
        
        if(empty($response) || $response == '') {
            throw new Exception ("MVC: Invalid response received from server");
        }

        if(!isset($response['ResponseCode']) || $response['ResponseCode'] == '') {
            throw new Exception ("MVC: Empty response code");
        }
        
        
        //$this->setError($this->_getErrorMessage($response['ResponseCode']));
        $responseMessage = isset($response['ResponseMessage']) ? $response['ResponseMessage'] : '';
        $this->setError($responseMessage);

        
        return false;
                
    }
    

   
    public function queryMvcStatus($userArray)
    {
        $config = App_Webservice::get('mvc_auth');        
        $method = $config['mvc_query'];
        //$authentification = new App_Api_MVC_Authentificator();
        $resp = $this->initSession();
        if($resp === false) {
            return false;
        }
        //exit('Registration -- END');
        $soapClient = $this->getSoapClient();
        $sessionKey = $this->getSessionKey();
        //print $this->getSessionKey();
        //Param Validation
                
                
        if(empty($userArray['PAN']) || $userArray['PAN'] == '') {
            throw new Exception ("MVC Input: Invalid MVC PAN provided");
        }                
        if(empty($userArray['ExpiryDate']) || $userArray['ExpiryDate'] == '') {//Date Format Validation
            throw new Exception ("MVC Input: Invalid MVC expiry date provided");
        }                
        if(empty($userArray['CVV2']) || $userArray['CVV2'] == '') {
            throw new Exception ("MVC Input: Invalid MVC CVV2 provided");
        }                
        if(empty($userArray['Amount']) || $userArray['Amount'] == '') {//Amount Validation pending
            throw new Exception ("MVC Input: Invalid MVC amount provided");
        }                
                
        //$response = $soapClient->$method($sessionKey,$userArray['CAFNumber'],$userArray['FirstName'],$userArray['LastName'],$userArray['MobileNumber'],$userArray['DeviceID'],$userArray['CRN'],$accountAlias,$customerType);
        $param = array(
            'SessionID' => $sessionKey,
            'PAN' => $userArray['PAN'],
            'ExpiryDate' => $userArray['ExpiryDate'],
            'CVV2' => $userArray['CVV2'],
            'Amount' => App_Api_MVC_Transactions::filterAmount($userArray['Amount']),
        );
        //echo "<pre>";print_r($data);
        $response = $soapClient->soapCall($method, $param);
        
        $this->setLastResponse($response);        

        if(isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
            return true;
        }
        if(isset($response['ResponseCode']) && $response['ResponseCode'] == 0) {
            return true;
        }
        
        if($response == false) {
            $this->setError($soapClient->_errorMsg);
            return false;
        } 
        if(empty($response) || $response == '') {
            throw new Exception ("MVC: Invalid response received from server");
        }
        
        if(!isset($response['ResponseCode']) || $response['ResponseCode'] == '') {
            throw new Exception ("MVC: Empty response code");
        }
        
        //$this->setError($this->_getErrorMessage($response['ResponseCode']));
        $responseMessage = isset($response['ResponseMessage']) ? $response['ResponseMessage'] : '';
        $this->setError($responseMessage);

        
        return false;
                
    }    
    
    
    
    public function queryMvcTransaction($userArray)
    {
        $config = App_Webservice::get('mvc_auth');        
        $method = $config['transaction_enquiry'];
        //$authentification = new App_Api_MVC_Authentificator();
        $resp = $this->initSession();
        if($resp === false) {
            return false;
        }
        //exit('Registration -- END');
        $soapClient = $this->getSoapClient();
        $sessionKey = $this->getSessionKey();
        //print $this->getSessionKey();
        //Param Validation
                
        if(empty($userArray['CRN']) || $userArray['CRN'] == '' ) {
            throw new Exception ("MVC Input: Invalid CRN provided");
        }                
        if(empty($userArray['FromDateTime']) || $userArray['FromDateTime'] == '') {//Date Format Validation
            throw new Exception ("MVC Input: Invalid From Date provided");
        }                
        if(empty($userArray['ToDateTime']) || $userArray['ToDateTime'] == '') {//Date Format Validation
            throw new Exception ("MVC Input: Invalid To date provided");
        }                
                
        //$response = $soapClient->$method($sessionKey,$userArray['CAFNumber'],$userArray['FirstName'],$userArray['LastName'],$userArray['MobileNumber'],$userArray['DeviceID'],$userArray['CRN'],$accountAlias,$customerType);
        $param = array(
            'SessionID' => $sessionKey,
            'CRN' => $userArray['CRN'],
            'FromDateTime' => $userArray['FromDateTime'],
            'ToDateTime' => $userArray['ToDateTime'],
        );
        //echo "<pre>";print_r($data);
        $response = $soapClient->soapCall($method, $param);
        
        $this->setLastResponse($response);        

        if(isset($response['ResponseCode']) && $response['ResponseCode'] == 0) {
            return true;
        }
        if(isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
            return true;
        }
        
        if($response == false) {
            $this->setError($soapClient->_errorMsg);
            return false;
        }  
        
        if(empty($response) || $response == '' || is_a($response, 'SoapFault')) {
            throw new Exception ("MVC: Invalid response received from server");
        }
        


        if(!isset($response['ResponseCode']) || $response['ResponseCode'] == '') {
            throw new Exception ("MVC: Empty response code");
        }
        
        
        //$this->setError($this->_getErrorMessage($response['ResponseCode']));
        $responseMessage = isset($response['ResponseMessage']) ? $response['ResponseMessage'] : '';
        $this->setError($responseMessage);

        
        return false;
                
    }    
    
    
    
    
    public function ResendActivationCode($userArray)
    {
        $config = App_Webservice::get('mvc_auth');        
        $method = $config['resend_activation_code'];
        //$authentification = new App_Api_MVC_Authentificator();
        $resp = $this->initSession();
        if($resp === false) {
                return false;
        }
        
        //exit('Registration -- END');
        $soapClient = $this->getSoapClient();
        $sessionKey = $this->getSessionKey();
        
        //Param Validation
                
        if(empty($userArray['CRN']) || $userArray['CRN'] == '') {//Date Format Validation
            throw new Exception ("MVC Input: Invalid CRN provided");
        }                
        if(empty($userArray['MobileNumber']) || $userArray['MobileNumber'] == '') {
            throw new Exception ("MVC Input: Invalid Mobile number provided");
        }                
        if(empty($userArray['DeviceID']) || $userArray['DeviceID'] == '') {
            throw new Exception ("MVC Input: Invalid Device ID provided");
        }                
        
        if(empty($userArray['RequestRefNumber']) || $userArray['RequestRefNumber'] == '') {//Amount Validation pending
            throw new Exception ("MVC Input: Invalid reference number provided");
        }                
                
        //$response = $soapClient->$method($sessionKey,$userArray['CAFNumber'],$userArray['FirstName'],$userArray['LastName'],$userArray['MobileNumber'],$userArray['DeviceID'],$userArray['CRN'],$accountAlias,$customerType);
        $param = array(
            'SessionID' => $sessionKey,
            'MobileNumber' => $userArray['MobileNumber'],
            'DeviceID' => $userArray['DeviceID'],
            'RequestRefNumber' => $userArray['RequestRefNumber'],
            'CRN' => $userArray['CRN'],            
        );
        //echo "<pre>";print_r($data);
        
        $response = $soapClient->soapCall($method, $param);
        
        $this->setLastResponse($response);        
        
        if(isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
            return true;
        }         
        
        if($response == false) {
            $this->setError($soapClient->_errorMsg);
            return false;
        }                
        
        if(empty($response) || $response == '') {
            throw new Exception ("MVC: Invalid response received from server");
        }
        
        if(isset($response['ResponseCode']) || $response['ResponseCode'] == 0) {
            return true;
        }

        if(!isset($response['ResponseCode']) || $response['ResponseCode'] == '') {
            throw new Exception ("MVC: Empty response code");
        }
        
        
        //$this->setError($this->_getErrorMessage($response['ResponseCode']));
        $responseMessage = isset($response['ResponseMessage']) ? $response['ResponseMessage'] : '';
        $this->setError($responseMessage);

        
        return false;
                
    }    
    
    public function UpdateMobileNumber($userArray)
    {
        $config = App_Webservice::get('mvc_auth');        
        $method = $config['update_mobile_number'];
        //$authentification = new App_Api_MVC_Authentificator();
        $resp = $this->initSession();
        //Login failed
        if($resp === false) {
            return false;
        }
        //exit('Registration -- END');
        $soapClient = $this->getSoapClient();
        $sessionKey = $this->getSessionKey();
        
        //Param Validation
                
        if(empty($userArray['CRN']) || $userArray['CRN'] == '') {//Date Format Validation
            throw new Exception ("MVC Input: Invalid CRN provided");
        }                
        if(empty($userArray['NewMobileNumber']) || $userArray['NewMobileNumber'] == '') {
            throw new Exception ("MVC Input: Invalid New Mobile number provided");
        }                
        if(empty($userArray['OldMobileNumber']) || $userArray['OldMobileNumber'] == '') {
            throw new Exception ("MVC Input: Invalid Old Mobile number provided");
        }                
        
        if(empty($userArray['RequestRefNumber']) || $userArray['RequestRefNumber'] == '') {//Amount Validation pending
            throw new Exception ("MVC Input: Invalid reference number provided");
        }                
                
        //$response = $soapClient->$method($sessionKey,$userArray['CAFNumber'],$userArray['FirstName'],$userArray['LastName'],$userArray['MobileNumber'],$userArray['DeviceID'],$userArray['CRN'],$accountAlias,$customerType);
        $param = array(
            'SessionID' => $sessionKey,
            'CRN' => $userArray['CRN'],                    
            'RequestRefNumber' => $userArray['RequestRefNumber'],            
            'OldMobileNumber' => $userArray['OldMobileNumber'],
            'NewMobileNumber' => $userArray['NewMobileNumber']
        );

        $response = $soapClient->soapCall($method, $param);

        $this->setLastResponse($response);      
        if(isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
            return true;
        }        
        
        if($response == false) {
            $this->setError($soapClient->_errorMsg);
            return false;
        }                
        
        if(empty($response) || $response == '') {
            throw new Exception ("MVC: Invalid response received from server");
        }
        
        if(!isset($response['ResponseCode']) || $response['ResponseCode'] == '') {
            throw new Exception ("MVC: Empty response code");
        }
        
        //$this->setError($this->_getErrorMessage($response['ResponseCode']));
        $responseMessage = isset($response['ResponseMessage']) ? $response['ResponseMessage'] : '';
        $this->setError($responseMessage);

        
        return false;
                
    }    
    
    
    public function BlockAccount($userArray)
    {
        $config = App_Webservice::get('mvc_auth');        
        $method = $config['block_account'];
        //$authentification = new App_Api_MVC_Authentificator();
        $resp = $this->initSession();
        if($resp === false) {
            return false;
        }
        //exit('Registration -- END');
        $soapClient = $this->getSoapClient();
        $sessionKey = $this->getSessionKey();
        //print $this->getSessionKey();
        //Param Validation
                
        if(empty($userArray['CRN']) || $userArray['CRN'] == '') {//Date Format Validation
            throw new Exception ("MVC Input: Invalid CRN provided");
        }                
        
        if(empty($userArray['RequestRefNumber']) || $userArray['RequestRefNumber'] == '') {//Amount Validation pending
            throw new Exception ("MVC Input: Invalid reference number provided");
        }                
                
        //$response = $soapClient->$method($sessionKey,$userArray['CAFNumber'],$userArray['FirstName'],$userArray['LastName'],$userArray['MobileNumber'],$userArray['DeviceID'],$userArray['CRN'],$accountAlias,$customerType);
        $param = array(
            'SessionID' => $sessionKey,
            'CRN' => $userArray['CRN'],                    
            'RequestRefNumber' => $userArray['RequestRefNumber'],            
        );
        $response = $soapClient->soapCall($method, $param);
        
        $this->setLastResponse($response);        
        
        if(isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
            return true;
        }             

        if($response == false) {
            $this->setError($soapClient->_errorMsg);
            return false;
        }        
        
        if(empty($response) || $response == '') {
            throw new Exception ("MVC: Invalid response received from server");
        }
        
        if(isset($response['ResponseCode']) || $response['ResponseCode'] == 0) {
            return true;
        }

        if(!isset($response['ResponseCode']) || $response['ResponseCode'] == '') {
            throw new Exception ("MVC: Empty response code");
        }
        
        
        //$this->setError($this->_getErrorMessage($response['ResponseCode']));
        $responseMessage = isset($response['ResponseMessage']) ? $response['ResponseMessage'] : '';
        $this->setError($responseMessage);

        
        return false;
                
    }    
    
    
    
    public function UnblockAccount($userArray)
    {
        $config = App_Webservice::get('mvc_auth');        
        $method = $config['unblock_account'];
        //$authentification = new App_Api_MVC_Authentificator();
        $resp = $this->initSession();
        if($resp === false) {
            return false;
        }
        //exit('Registration -- END');
        $soapClient = $this->getSoapClient();
        $sessionKey = $this->getSessionKey();
        
        //Param Validation
                
        if(empty($userArray['CRN']) || $userArray['CRN'] == '') {//Date Format Validation
            throw new Exception ("MVC Input: Invalid CRN provided");
        }                
        
        if(empty($userArray['RequestRefNumber']) || $userArray['RequestRefNumber'] == '') {//Amount Validation pending
            throw new Exception ("MVC Input: Invalid reference number provided");
        }                
                
        //$response = $soapClient->$method($sessionKey,$userArray['CAFNumber'],$userArray['FirstName'],$userArray['LastName'],$userArray['MobileNumber'],$userArray['DeviceID'],$userArray['CRN'],$accountAlias,$customerType);
        $param = array(
            'SessionID' => $sessionKey,
            'CRN' => $userArray['CRN'],                    
            'RequestRefNumber' => $userArray['RequestRefNumber'],            
        );
        $response = $soapClient->soapCall($method, $param);
        
        $this->setLastResponse($response);        

        
        if(isset($response['ResponseCode']) && $response['ResponseCode'] == 0) {
            return true;
        }
        if($response == false) {
            $this->setError($soapClient->_errorMsg);
            return false;
        }        
        
        if(empty($response) || $response == '') {
            throw new Exception ("MVC: Invalid response received from server");
        }
        


        if(!isset($response['ResponseCode']) || $response['ResponseCode'] == '') {
            throw new Exception ("MVC: Empty response code");
        }
        
        //$this->setError($this->_getErrorMessage($response['ResponseCode']));
        $responseMessage = isset($response['ResponseMessage']) ? $response['ResponseMessage'] : '';
        $this->setError($responseMessage);
        

        
        return false;
                
    }    
        
    
    public function CloseAccount($userArray)
    {
        $config = App_Webservice::get('mvc_auth');        
        $method = $config['close_account'];
        //$authentification = new App_Api_MVC_Authentificator();
        $resp = $this->initSession();
        if($resp === false) {
            return false; 
        }
        //exit('Registration -- END');
        $soapClient = $this->getSoapClient();
        $sessionKey = $this->getSessionKey();
        
        //Param Validation
                
        if(empty($userArray['CRN']) || $userArray['CRN'] == '') {//Date Format Validation
            throw new Exception ("MVC Input: Invalid CRN provided");
        }                
        
        if(empty($userArray['RequestRefNumber']) || $userArray['RequestRefNumber'] == '') {//Amount Validation pending
            throw new Exception ("MVC Input: Invalid reference number provided");
        }                
                
        //$response = $soapClient->$method($sessionKey,$userArray['CAFNumber'],$userArray['FirstName'],$userArray['LastName'],$userArray['MobileNumber'],$userArray['DeviceID'],$userArray['CRN'],$accountAlias,$customerType);
        $param = array(
            'SessionID' => $sessionKey,
            'CRN' => $userArray['CRN'],                    
            'RequestRefNumber' => $userArray['RequestRefNumber'],            
        );
        //echo "<pre>";print_r($data);
        $response = $soapClient->soapCall($method, $param);
        
        $this->setLastResponse($response);        

        if(isset($response['ResponseCode']) && $response['ResponseCode'] == 0) {
            return true;
        }
        //print_r($response);exit;
        //$this->setError($this->_getErrorMessage($response['ResponseCode']));
        $responseMessage = isset($response['ResponseMessage']) ? $response['ResponseMessage'] : '';
        $this->setError($responseMessage);

        
        if(empty($response) || $response == '' || is_a($response, 'SoapFault')) {
            throw new Exception ("MVC: Invalid response received from server");
        }

        if(!isset($response['ResponseCode']) || $response['ResponseCode'] == '') {
            throw new Exception ("MVC: Empty response code");
        }

        return false;
                
    }    
    
    /**
     * CustomerAuthentication
     * Used to send request to MVC Server
     * @param type $userArray
     * @return boolean
     */
    public function CustomerAuthentication($userArray)
    {
        $config = App_Webservice::get('mvc_auth');        
        $method = $config['customer_authentication'];
        //$authentification = new App_Api_MVC_Authentificator();
        $this->initSession();
        //exit('Registration -- END');
        $soapClient = $this->getSoapClient();
        $sessionKey = $this->getSessionKey();
        $this->custAuthSessionId = $sessionKey;
        //Param Validation
              
                
        //$response = $soapClient->$method($sessionKey,$userArray['CAFNumber'],$userArray['FirstName'],$userArray['LastName'],$userArray['MobileNumber'],$userArray['DeviceID'],$userArray['CRN'],$accountAlias,$customerType);
        $messageId = isset($userArray['MessageID']) ? $userArray['MessageID'] : '';
        
        /*
        $obj = new stdClass();
        $obj->SessionID = $sessionKey;
        $obj->PAN = $userArray['PAN'];
        $obj->Amount = $userArray['Amount'];
        $obj->ExpiryDate = $userArray['ExpiryDate'];
        $obj->OTP = $userArray['OTP'];
        
        $ar = array(
            "Message"   => $obj,
            "_"         => "123456"
        );
        
        $param = array(
            new SoapParam($sessionKey, 'SessionID'),
            new SoapParam($userArray['PAN'],'PAN'),                    
            new SoapParam($userArray['Amount'],'Amount'),               
            new SoapParam($userArray['ExpiryDate'],'ExpiryDate'), 
            new SoapParam($userArray['OTP'],'OTP')
        );
        */

        $xmlDocument = '<Message id="'.$messageId.'">
                            <SessionID>'.$sessionKey.'</SessionID>
                            <PAN>'.$userArray["PAN"].'</PAN>
                            <Amount>'.$userArray["Amount"].'</Amount>
                            <ExpiryDate>'.$userArray["ExpiryDate"].'</ExpiryDate>
                            <OTP>'.$userArray["OTP"].'</OTP>
                        </Message>';


        $param = new SoapVar(
                    $xmlDocument,
                    XSD_ANYXML
        );
        $response = $soapClient->AuthenticationRequest($param);        

        $soapRequest = $soapClient->__getLastRequest();
        $soapResponse = $soapClient->__getLastResponse();
        $soapResponseHeader = $soapClient->__getLastRequestHeaders();
        
        $resArray['user_id'] = TP_MVC_ID;
        $resArray['method'] = 'CustomerAuthentication';
        $resArray['request'] = $soapRequest;
        $resArray['response'] = $soapResponse;
        App_Logger::apilog($resArray);        
        $this->logCustomRequest('AuthenticationRequest',$soapRequest,$soapResponse);
        $this->setLastResponse($soapResponse); 
        //return true;
        if(isset($response->ResponseCode) && $response->ResponseCode == 0) {
            return true;
        }      
        
        //$this->setError($this->_getErrorMessage($response->ResponseCode));
        $this->setError($response->ResponseMessage);
        
        return false;
                
    }    
    
    public function getCustomerAuthSesionId() {
        return $this->custAuthSessionId;
    }
        
    
    public function TransactionHistory($userArray)
    {
        $config = App_Webservice::get('mvc_auth');        
        $method = $config['transaction_history'];
        //$authentification = new App_Api_MVC_Authentificator();
        $this->initSession();
        //exit('Registration -- END');
        $soapClient = $this->getSoapClient();
        $sessionKey = $this->getSessionKey();
        
        //Param Validation
                
        if(empty($userArray['CRN']) || $userArray['CRN'] == '') {//Date Format Validation
            throw new Exception ("MVC Input: Invalid CRN provided");
        }                
        
        if(empty($userArray['EchoData']) || $userArray['EchoData'] == '') {//Amount Validation pending
            throw new Exception ("MVC Input: Invalid echo data provided");
        }                
                
        try {
        //$response = $soapClient->$method($sessionKey,$userArray['CAFNumber'],$userArray['FirstName'],$userArray['LastName'],$userArray['MobileNumber'],$userArray['DeviceID'],$userArray['CRN'],$accountAlias,$customerType);
        $param = array(
            'SessionID' => $sessionKey,
            'CRN' => $userArray['CRN'],                    
            'EchoData' => $userArray['EchoData'],            
        );
        //echo "<pre>";print_r($data);
        $response = $soapClient->soapCall($method, $param);
        } catch (Exception $e) {
            echo "Here";
            print "Request : " . htmlentities($soapClient->__getLastRequest()) . "\n";
            print "Response : " .htmlentities($soapClient->__getLastRequest()) . "\n";
            
            echo "<pre>";print_r($e);
        }
        
            echo "Here<pre>";
            print "Request : " . htmlentities($soapClient->__getLastRequest()) . "\n";
            print "Response : " .htmlentities($soapClient->__getLastResponse()) . "\n";
            print "Reponse : --";
            echo "<pre>";print_r($response);        
        exit("END");
//        print '<pre>';
//        print 'Request' . htmlentities($soapClient->__getLastRequest());
//        print 'Response' . htmlentities($soapClient->__getLastResponse());
//        
//        echo "<pre>";print_r($response);exit;
        
        
        $this->setLastResponse($response);        
        
        if(empty($response) || $response == '' || is_a($response, 'SoapFault')) {
            throw new Exception ("MVC: Invalid response received from server");
        }
        
        if(isset($response['ResponseCode']) || $response['ResponseCode'] == 0) {
            return true;
        }

        if(!isset($response['ResponseCode']) || $response['ResponseCode'] == '') {
            throw new Exception ("MVC: Empty response code");
        }
        
        
        //$this->setError($this->_getErrorMessage($response['ResponseCode']));
        $responseMessage = isset($response['ResponseMessage']) ? $response['ResponseMessage'] : '';
        $this->setError($responseMessage);
        
        return false;
                
    } 
    
    /**
     * filterAmount
     * Filter MVC Amount to its minor denomination
     * @param <Amount> $amount  (10(ten rupees), 10.50 (ten rupees and fifty paisa))
     * @return <Formated Amount> $amount (In Minor denomination like 1000 (ten rupees)
     */
    public static function filterAmount($amount) {
        if(strpos($amount, '.') == false) {//To handle 10.50 
            $retAmount =  $amount * 100;            
 
        }elseif(strpos($amount, ',') == false) {//To handle 10,50 
            $amount = str_replace(',', '.', $amount);
            $retAmount =  $amount * 100;            
        } else {
            $retAmount =  $amount * 100;
        }
        
        //Expected result in minor denomination 1 rupees = 100
        return $retAmount;
    }
    
    /**
     * displayAmount
     * Display Amount for MVC Related Transaction
     * @param <Amount> $amount  (1000(ten rupees), 1050 (ten rupees and fifty paisa))
     * @return <Formated Amount> $amount (In Minor denomination like 10 (ten rupees)
     */
    public static function displayAmount($amount) {
        //Expected result 100 will diplay as 1
        return $amount / 100;
    }
    
    
    
    

}
