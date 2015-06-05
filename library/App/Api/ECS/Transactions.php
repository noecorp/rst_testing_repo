<?php
class App_Api_ECS_Transactions extends App_Api_ECS_Authentificator {


    private $regRefNO;


    public function cardholderRegistration(array $userArr)
    {
        //echo "<pre>";print_r($userArr);exit;

        $config = $this->getConfig();

        if(!isset($userArr['cardNumber']) || $userArr['cardNumber'] == '') {
            throw new App_Api_Exception ("Invalid CRN provided");
        }
        
        if(!isset($userArr['address1']) || $userArr['address1'] == '') {
            throw new App_Api_Exception (" Invalid address1 provided");
        }
        
        if(!isset($userArr['address4']) || $userArr['address4'] == '') {
            throw new App_Api_Exception (" Invalid state provided");
        }
        
        if(!isset($userArr['birthdate']) || $userArr['birthdate'] == '') {
            throw new App_Api_Exception (" Invalid birthdate provided");
        }
        
        if(!isset($userArr['citycode']) || $userArr['citycode'] == '') {
            throw new App_Api_Exception (" Invalid citycode provided");
        }

        
        if(!isset($userArr['countrycode']) || $userArr['countrycode'] == '') {
            throw new App_Api_Exception (" Invalid countrycode provided");
        }

        
        if(!isset($userArr['familyname']) || $userArr['familyname'] == '') {
            throw new App_Api_Exception (" Invalid familyname provided");
        }

        
        if(!isset($userArr['firstname']) || $userArr['firstname'] == '') {
            throw new App_Api_Exception (" Invalid firstname provided");
        }

        
        if(!isset($userArr['gender']) || $userArr['gender'] == '') {
            throw new App_Api_Exception (" Invalid gender provided");
        }

        
        if(!isset($userArr['mothersmaidenname']) || $userArr['mothersmaidenname'] == '') {
            throw new App_Api_Exception (" Invalid mothers maiden name provided");
        }

        
        if(!isset($userArr['zipcode']) || $userArr['zipcode'] == '') {
            throw new App_Api_Exception (" Invalid zipcode provided");
        }
        try { 
        //$authentification = new App_Api_ECS_Authentificator();
        $resp = $this->initSession();
        //Validate Login
        if($resp === false) {
            return false;
        }
       
        //$this->initSession();
        $soapClient = $this->getSoapClient();
        //echo $this->getSessionKey();exit;
        //echo "<pre>";print_r($userArr);
        $paramArray = $this->_filterRegistrationArray($userArr);
        
        $paramArray['sessionKey'] = $this->getSessionKey();
        $method = $config['auth_custromer_registration_method'];
        //echo "<pre>";print_r($paramArray);
        if(!isset($method) || $method == '') {
            //throw new Exception ("ECS: API Method not defined for cardholder registrations");
        }
        
        //Call ECS Cardholder Registration method
        //$userArr['sessionKey'] = $this->getSessionKey();
        $response = $soapClient->ecsSoapCall($method,$paramArray);
        //echo "<pre>1212";print_r($response);
        //exit;
        $this->setLastResponse($response);
        
        //echo "<pre>";print_r($response);exit;
        
        if(isset($response->responseCode) && !empty($response->responseCode) && $response->responseCode != '0') {
            //$msg = $this->getLastResponseError();
            $this->setError($response->errorDesc);
        }
        
        if(isset($response->responseCode) && $response->responseCode == '0') {
            //throw new Exception ("ECS: Invalid response recived from server");
            return true;
        }
        return false;
        
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }
    
    public function getRegistrationRefNo() {
        return $this->regRefNO;
    }
    
    
    public function FirstTimeCardLoad(array $userArr)
    {
        /*$config = App_Webservice::get('ecs_auth');
        $authentification = new App_Api_ECS_Authentificator();
        $authentification->initSession();
        $client = $authentification->getClient();
        $cardholderArray = array(
            'id'            =>  $userArr['id'],
            'SessionKey'    =>  $authentification->getSessionKey(),
            'Channel'       =>  $authentification->getChannel(),
            'PassCodeFlag'  =>  'N',
            'PassCode'      =>  '',
            'UniqueRefNumber' =>  $userArr['crn'],
            'ProductCode'   =>  $userArr['product_code'],
            'Currency'      =>  $userArr['currency_code'],
            'Amount'        =>  $userArr['amount'],
            
        );
        
        $method = $config['auth_custromer_first_load'];
        if(!isset($method) || $method == '') {
            throw new Exception ("ECS: API Method not defined for cardholder registrations");
        }
        //Call ECS Cardholder Registration method
        $apiResponse = $client->$method($cardholderArray);
        
        $response = Util::wsdlObjectToArray($apiResponse->item);

        if(empty($response) || $response == '') {
            throw new Exception ("ECS: Invalid response recived from server");
        }
        
        if(!isset($response['ResponseCode']) || $response['ResponseCode'] == '') {
            throw new Exception ("ECS: empty auth response");
        }
        //Cardholder Registration Successful
        if($response['ResponseCode'] == '000') {
            return true;
        }
        
        $errorCode = new App_Api_ECS_ErrorCode();
         $msg = $errorCode->_getError($response['ResponseCode']);
        $this->setError($msg);
        return false;
   */

    }
    
    private function _filterRegistrationArray($paramArray) {
       
        $respArray['cardNumber'] = isset($paramArray['cardNumber']) ? $paramArray['cardNumber'] : '';
        $respArray['channel'] = isset($paramArray['channel']) ? $paramArray['channel'] : 'IVR';
        $respArray['componentAuthKey'] = isset($paramArray['componentAuthKey']) ? $paramArray['componentAuthKey'] : 'transerv';
        $respArray['componentId'] = isset($paramArray['componentId']) ? $paramArray['componentId'] : '';
        $respArray['expiryDate'] = isset($paramArray['expiryDate']) ? $paramArray['expiryDate'] : '';
        $respArray['ip'] = isset($paramArray['ip']) ? $paramArray['ip'] : Util::getIP();
        $respArray['passCodeFlag'] = isset($paramArray['passCodeFlag']) ? $paramArray['passCodeFlag'] :'N';
        $respArray['passCodeValue'] = isset($paramArray['passCodeValue']) ? $paramArray['passCodeValue'] : '';
        $respArray['password'] = isset($paramArray['password']) ? $paramArray['password'] : '';'';
        $respArray['requestDateTime'] = isset($paramArray['requestDateTime']) ? $paramArray['requestDateTime'] : '';'';
        $respArray['serviceCode'] = isset($paramArray['serviceCode']) ? $paramArray['serviceCode'] : '205';
        $respArray['sessionKey'] = isset($paramArray['sessionKey']) ? $paramArray['sessionKey'] : $this->getSessionKey();
        $respArray['terminalType'] = isset($paramArray['terminalType']) ? $paramArray['terminalType'] : ''; '';
        $respArray['txnPassFlag'] = isset($paramArray['txnPassFlag']) ? $paramArray['txnPassFlag'] : ''; '';
        $respArray['txnPassword'] = isset($paramArray['txnPassword']) ? $paramArray['txnPassword'] : ''; '';
        $respArray['userId'] = isset($paramArray['userId']) ? $paramArray['userId'] : 'transerv';
        $respArray['address1'] = isset($paramArray['address1']) ? $paramArray['address1'] : '';
        $respArray['address2'] = isset($paramArray['address2']) ? $paramArray['address2'] : '';
        $respArray['address3'] = isset($paramArray['address3']) ? $paramArray['address3'] : '';
        //$respArray['address4'] = isset($paramArray['address4']) ? $paramArray['address4'] : ''; -- Removing As need to set statecode in seprate fields
        $respArray['bankcode'] = isset($paramArray['bankcode']) ? $paramArray['bankcode'] : '';
        $respArray['birthcity'] = isset($paramArray['birthcity']) ? $paramArray['birthcity'] : '';
        $respArray['birthcountry'] = isset($paramArray['birthcountry']) ? $paramArray['birthcountry'] : '';
        $respArray['birthdate'] = isset($paramArray['birthdate']) ? $paramArray['birthdate'] : '';
        $respArray['citycode'] = isset($paramArray['citycode']) ? $paramArray['citycode'] : '';
        $respArray['countrycode'] = isset($paramArray['countrycode']) ? $paramArray['countrycode'] : '356';
        $respArray['emailid'] = isset($paramArray['emailid']) ? $paramArray['emailid'] : '';
        $respArray['embossedname'] = isset($paramArray['embossedname']) ? $paramArray['embossedname'] : '';//First Name + Last Name
        $respArray['employer'] = isset($paramArray['employer']) ? $paramArray['employer'] : ''; '';
        $respArray['employmentstatus'] = isset($paramArray['employmentstatus']) ? $paramArray['employmentstatus'] : '';
        $respArray['familyname'] = isset($paramArray['familyname']) ? $paramArray['familyname'] : '';
        $respArray['firstname'] = isset($paramArray['firstname']) ? $paramArray['firstname'] : '';
        $respArray['gender'] = isset($paramArray['gender']) ? $paramArray['gender'] : '';
        $respArray['legalid'] = isset($paramArray['legalid']) ? $paramArray['legalid'] : '';
        $respArray['legalidentificationtype'] = isset($paramArray['legalidentificationtype']) ? $paramArray['legalidentificationtype'] : '';
        $respArray['mailingaddress1'] = isset($paramArray['mailingaddress1']) ? $paramArray['mailingaddress1'] : '';
        $respArray['mailingaddress2'] = isset($paramArray['mailingaddress2']) ? $paramArray['mailingaddress2'] : '';
        $respArray['mailingaddress3'] = isset($paramArray['mailingaddress3']) ? $paramArray['mailingaddress3'] : '';
        $respArray['mailingaddress4'] = isset($paramArray['mailingaddress4']) ? $paramArray['mailingaddress4'] : '';
        $respArray['mailingcitycode'] = isset($paramArray['mailingcitycode']) ? $paramArray['mailingcitycode'] : '';
        $respArray['mailingcountrycode'] = isset($paramArray['mailingcountrycode']) ? $paramArray['mailingcountrycode'] : '';
        $respArray['mailingstatecode'] = isset($paramArray['mailingstatecode']) ? $paramArray['mailingstatecode'] : '';
        $respArray['mailingzipcode'] = isset($paramArray['mailingzipcode']) ? $paramArray['mailingzipcode'] : '';
        $respArray['maritalstatus'] = isset($paramArray['maritalstatus']) ? $paramArray['maritalstatus'] : '';
        $respArray['middlename'] = isset($paramArray['middlename']) ? $paramArray['middlename'] : '';
        $respArray['middlename2'] = isset($paramArray['middlename2']) ? $paramArray['middlename2'] : '';
        $respArray['mothersmaidenname'] = isset($paramArray['mothersmaidenname']) ? $paramArray['mothersmaidenname'] : '';
        $respArray['nationalitycode'] = isset($paramArray['nationalitycode']) ? $paramArray['nationalitycode'] : '';
        $respArray['officeaddress1'] = isset($paramArray['officeaddress1']) ? $paramArray['officeaddress1'] : '';
        $respArray['officeaddress2'] = isset($paramArray['officeaddress2']) ? $paramArray['officeaddress2'] : '';
        $respArray['officeaddress3'] = isset($paramArray['officeaddress3']) ? $paramArray['officeaddress3'] : '';
        $respArray['officeaddress4'] = isset($paramArray['officeaddress4']) ? $paramArray['officeaddress4'] : '';
        $respArray['officecitycode'] = isset($paramArray['officecitycode']) ? $paramArray['officecitycode'] : '';
        $respArray['officecountrycode'] = isset($paramArray['officecountrycode']) ? $paramArray['officecountrycode'] : '';
        $respArray['officemobile'] = isset($paramArray['officemobile']) ? $paramArray['officemobile'] : '';
        $respArray['officephone1'] = isset($paramArray['officephone1']) ? $paramArray['officephone1'] : '';
        $respArray['officephone2'] = isset($paramArray['officephone2']) ? $paramArray['officephone2'] : '';
        $respArray['officestatecode'] = isset($paramArray['officestatecode']) ? $paramArray['officestatecode'] : '';
        $respArray['officezipcode'] = isset($paramArray['officezipcode']) ? $paramArray['officezipcode'] : '';
        $respArray['permanentmobile'] = isset($paramArray['permanentmobile']) ? $paramArray['permanentmobile'] : '';
        $respArray['permanentphone1'] = isset($paramArray['permanentphone1']) ? $paramArray['permanentphone1'] : '';
        $respArray['permanentphone2'] = isset($paramArray['permanentphone2']) ? $paramArray['permanentphone2'] : '';
        $respArray['phonealternate'] = isset($paramArray['phonealternate']) ? $paramArray['phonealternate'] : '';
        $respArray['phonehome'] = isset($paramArray['phonehome']) ? $paramArray['phonehome'] : '';
        $respArray['phonemobile'] = isset($paramArray['phonemobile']) ? $paramArray['phonemobile'] : '';
        $respArray['preferredmailingaddress'] = isset($paramArray['preferredmailingaddress']) ? $paramArray['preferredmailingaddress'] : '';
        $respArray['priorityrequest'] = isset($paramArray['priorityrequest']) ? $paramArray['priorityrequest'] : '';
        $respArray['remarks'] = isset($paramArray['remarks']) ? $paramArray['remarks'] : '';
        $respArray['residencestatus'] = isset($paramArray['residencestatus']) ? $paramArray['residencestatus'] : '';
        //Sending address 4 value in statecode
        $respArray['statecode'] = isset($paramArray['address4']) ? $paramArray['address4'] : '';
        $respArray['title'] = isset($paramArray['title']) ? $this->getTitleCode($paramArray['title']) : '';
        $respArray['userdefinedfield3'] = isset($paramArray['userdefinedfield3']) ? $paramArray['userdefinedfield3'] : '';
        $respArray['userdefinedfield4'] = isset($paramArray['userdefinedfield4']) ? $paramArray['userdefinedfield4'] : '';
        $respArray['userdefinedfield5'] = isset($paramArray['userdefinedfield5']) ? $paramArray['userdefinedfield5'] : '';
        $respArray['userdefinedfield6'] = isset($paramArray['userdefinedfield6']) ? $paramArray['userdefinedfield6'] : '';
        $respArray['userdefinedfield7'] = isset($paramArray['userdefinedfield7']) ? $paramArray['userdefinedfield7'] : '';
        $respArray['userdefinedfield8'] = isset($paramArray['userdefinedfield8']) ? $paramArray['userdefinedfield8'] : '';
        $respArray['userdefinedfield9'] = isset($paramArray['userdefinedfield9']) ? $paramArray['userdefinedfield9'] : '';
        $respArray['userid'] = isset($paramArray['userid']) ? $paramArray['userid'] : 'transerv';
        $respArray['zipcode'] = isset($paramArray['zipcode']) ? $paramArray['zipcode'] : '';   
        
        return $respArray;
    }
    
    

    /**
     * Stop Card
     * Used to stop card/CRN in ECS 
     * @param array $userArr
     * @return boolean
     * @throws App_Api_Exception
     */
    public function stopCard(array $userArr)
    {
        
        try { 
        $config = $this->getConfig();

        if(!isset($userArr['cardNumber']) || $userArr['cardNumber'] == '') {
            throw new App_Api_Exception ("Invalid CRN provided");
        }
        
        $resp = $this->initSession();
        
        //Validate Login
        if($resp === false) {
            return false;
        }
        $soapClient = $this->getSoapClient();

        $paramArray = $this->_filterStopCardArray($userArr);
        $paramArray['sessionKey'] = $this->getSessionKey();
        $method = $config['stop_card'];

        if(!isset($method) || $method == '') {
            throw new Exception ("ECS: API Method not defined for cardholder registrations");
        }
        
        //Call ECS Cardholder Registration method
        //$userArr['sessionKey'] = $this->getSessionKey();
        $response = $soapClient->ecsSoapCall($method,$paramArray);
        $this->setLastResponse($response);
        
        if(isset($response->responseCode) && !empty($response->responseCode) && $response->responseCode != '0') {
            //$msg = $this->getLastResponseError();
            $this->setError($response->errorDesc);
        }
        
        if(isset($response->responseCode) && $response->responseCode == '0') {
            return true;
        }
        return false;
        
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }    
    
    /**
     * balanceInquiry
     * Used to fetch the balance of the cardholder
     * @param array $userArr
     * @return boolean
     * @throws App_Api_Exception
     * @throws Exception
     */
    public function balanceInquiry(array $userArr)
    {
        

        $config = $this->getConfig();

        if(!isset($userArr['cardNumber']) || $userArr['cardNumber'] == '') {
            throw new App_Api_Exception ("Invalid CRN provided");
        }
        
        try { 
        //$authentification = new App_Api_ECS_Authentificator();
        $resp = $this->initSession();
        //Validate Login
        if($resp === false) {
            return false;
        }
       
        //$this->initSession();
        $soapClient = $this->getSoapClient();
        
        $paramArray = $this->_filterBalanceInquiryArray($userArr);
        
        $paramArray['sessionKey'] = $this->getSessionKey();
        $method = $config['balance_inquiry'];

        if(!isset($method) || $method == '') {
            throw new Exception ("ECS: API Method not defined for balance inquiry");
        }
        
        //Call ECS Cardholder Registration method
        $response = $soapClient->ecsSoapCall($method,$paramArray);

        $this->setLastResponse($response);
        
        if(isset($response->responseCode) && !empty($response->responseCode) && $response->responseCode != '0') {
            //$msg = $this->getLastResponseError();
            $this->setError($response->errorDesc);
        }
        
        if($response->balanceInquiryList->cardnumber != $userArr['cardNumber']) {
            $this->setError('Card validation failed');            
            return false;
        }
        
        //validate the response
        if(isset($response->responseCode) && $response->responseCode == '0') {
            //throw new Exception ("ECS: Invalid response recived from server");
            return true;
        }
        return false;
        
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }    
    

    /**
     * _filterBalanceInquiryArray
     * Used to filter Balance Inquiry Input data
     * @param type $paramArray
     * @return type
     */
  private function _filterBalanceInquiryArray($paramArray) {
       
        $respArray['cardNumber']        = isset($paramArray['cardNumber']) ? $paramArray['cardNumber'] : '';
        $respArray['channel']           = isset($paramArray['channel']) ? $paramArray['channel'] : ECS_API_CHANNEL;
        $respArray['componentAuthKey']  = isset($paramArray['componentAuthKey']) ? $paramArray['componentAuthKey'] : ECS_API_USERID;
        $respArray['componentId']       = isset($paramArray['componentId']) ? $paramArray['componentId'] : '';
        $respArray['expiryDate']        = isset($paramArray['expiryDate']) ? $paramArray['expiryDate'] : '';
        $respArray['ip']                = isset($paramArray['ip']) ? $paramArray['ip'] : Util::getIP();
        $respArray['passCodeFlag']      = 'N';
        $respArray['passCodeValue']     = isset($paramArray['passCodeValue']) ? $paramArray['passCodeValue'] : '';
        $respArray['password']          = isset($paramArray['password']) ? $paramArray['password'] : '';
        $respArray['requestDateTime']   = isset($paramArray['requestDateTime']) ? $paramArray['requestDateTime'] : '';
        $respArray['serviceCode']       = '401';//Service Code for Balance Inquiry
        $respArray['sessionKey']        = isset($paramArray['sessionKey']) ? $paramArray['sessionKey'] : $this->getSessionKey();
        $respArray['terminalType']      = isset($paramArray['terminalType']) ? $paramArray['terminalType'] : '';
        $respArray['txnPassFlag']       = isset($paramArray['txnPassFlag']) ? $paramArray['txnPassFlag'] : '';
        $respArray['txnPassword']       = isset($paramArray['txnPassword']) ? $paramArray['txnPassword'] : '';
        $respArray['userId']            = isset($paramArray['userId']) ? $paramArray['userId'] : ECS_API_USERID;
        return $respArray;
        
    }    
    
    
    /**
     * _filterBalanceInquiryArray
     * Used to filter Balance Inquiry Input data
     * @param type $paramArray
     * @return type
     */
  private function _filterStopCardArray($paramArray) {
       
        $respArray['cardNumber']        = isset($paramArray['cardNumber']) ? $paramArray['cardNumber'] : '';
        $respArray['channel']           = isset($paramArray['channel']) ? $paramArray['channel'] : ECS_API_CHANNEL;
        $respArray['componentAuthKey']  = isset($paramArray['componentAuthKey']) ? $paramArray['componentAuthKey'] : ECS_API_USERID;
        $respArray['componentId']       = isset($paramArray['componentId']) ? $paramArray['componentId'] : '';
        $respArray['expiryDate']        = isset($paramArray['expiryDate']) ? $paramArray['expiryDate'] : '';
        $respArray['ip']                = isset($paramArray['ip']) ? $paramArray['ip'] : Util::getIP();
        $respArray['passCodeFlag']      = 'N';
        $respArray['passCodeValue']     = isset($paramArray['passCodeValue']) ? $paramArray['passCodeValue'] : '';
        $respArray['password']          = isset($paramArray['password']) ? $paramArray['password'] : '';
        $respArray['requestDateTime']   = isset($paramArray['requestDateTime']) ? $paramArray['requestDateTime'] : '';
        $respArray['serviceCode']       = '206';//Service Code for Stop Card
        $respArray['sessionKey']        = isset($paramArray['sessionKey']) ? $paramArray['sessionKey'] : $this->getSessionKey();
        $respArray['terminalType']      = isset($paramArray['terminalType']) ? $paramArray['terminalType'] : '';
        $respArray['txnPassFlag']       = isset($paramArray['txnPassFlag']) ? $paramArray['txnPassFlag'] : '';
        $respArray['txnPassword']       = isset($paramArray['txnPassword']) ? $paramArray['txnPassword'] : '';
        $respArray['userId']            = isset($paramArray['userId']) ? $paramArray['userId'] : ECS_API_USERID;
        $respArray['reasonCode']        = '14';
        return $respArray;
        
    }    
    
    
  /**
     * balanceInquiry
     * Used to fetch the balance of the cardholder
     * @param array $userArr
     * @return boolean
     * @throws App_Api_Exception
     * @throws Exception
     */
    public function balanceInquiryFromMVC(array $userArr)
    {
        

        $config = $this->getConfig();

//        if(!isset($userArr['cardNumber']) || $userArr['cardNumber'] == '') {
//            throw new App_Api_Exception ("Invalid CRN provided");
//        }
        
        try { 
        //$authentification = new App_Api_ECS_Authentificator();
        $resp = $this->initSession();
        //Validate Login
//        if($resp === false) {
//            return false;
//        }
       
        //$this->initSession();
        $soapClient = $this->getSoapClient();
        
        $paramArray = $this->_filterBalanceInquiryArray($userArr);
        //echo "<pre>";print_r($paramArray);exit;
        $paramArray['sessionKey'] = $this->getSessionKey();
        $method = $config['balance_inquiry'];
        
//        if(!isset($method) || $method == '') {
//            throw new Exception ("ECS: API Method not defined for balance inquiry");
//        }
        
        //Call ECS Cardholder Registration method
        $response = $soapClient->ecsSoapCall($method,$paramArray);
        //$this->setLastResponse($soapClient->getLastResponse());
        $this->setLastResponse($response);        
        return $response;
        //$soapClient->getLastResponse();exit;
        
        
        //print 'Response: ' .$response;        exit;
        
        if(isset($response->responseCode) && !empty($response->responseCode) && $response->responseCode != '0') {
            //$msg = $this->getLastResponseError();
            $this->setError($response->errorDesc);
        }
        
        //validate the response
        if(isset($response->responseCode) && $response->responseCode == '0') {
            //throw new Exception ("ECS: Invalid response recived from server");
            return true;
        }
        return false;
        
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }    

    

   
    /**
     * balanceInquiry
     * Used to fetch the balance of the cardholder
     * @param array $userArr
     * @return boolean
     * @throws App_Api_Exception
     * @throws Exception
     */
    public function transactionHistory(array $userArr)
    {
        

        $config = $this->getConfig();

        if(!isset($userArr['cardNumber']) || $userArr['cardNumber'] == '') {
            throw new App_Api_Exception ("Invalid CRN provided");
        }
        
        try { 
        //$authentification = new App_Api_ECS_Authentificator();
        $resp = $this->initSession();
        //Validate Login
        if($resp === false) {
            return false;
        }
       
        //$this->initSession();
        $soapClient = $this->getSoapClient();
        
        $paramArray = $this->_filterTransactionHistoryArray($userArr);
        
        $paramArray['sessionKey'] = $this->getSessionKey();
        $method = $config['transaction_history'];

        if(!isset($method) || $method == '') {
            throw new Exception ("ECS: API Method not defined for balance inquiry");
        }
        
        //Call ECS Cardholder Registration method
        $response = $soapClient->ecsSoapCall($method,$paramArray);

        $this->setLastResponse($response);
        
        if(isset($response->responseCode) && !empty($response->responseCode) && $response->responseCode != '0') {
            //$msg = $this->getLastResponseError();
            $this->setError($response->errorDesc);
        }
        
        //validate the response
        if(isset($response->responseCode) && $response->responseCode == '0') {
            //throw new Exception ("ECS: Invalid response recived from server");
            return true;
        }
        return false;
        
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }    
        

  /**
     * _filterBalanceInquiryArray
     * Used to filter Balance Inquiry Input data
     * @param type $paramArray
     * @return type
     */
  private function _filterTransactionHistoryArray($paramArray) {
       
        $respArray['cardNumber']        = isset($paramArray['cardNumber']) ? $paramArray['cardNumber'] : '';
        $respArray['channel']           = isset($paramArray['channel']) ? $paramArray['channel'] : ECS_API_CHANNEL;
        $respArray['componentAuthKey']  = isset($paramArray['componentAuthKey']) ? $paramArray['componentAuthKey'] : ECS_API_USERID;
        $respArray['componentId']       = isset($paramArray['componentId']) ? $paramArray['componentId'] : '';
        $respArray['expiryDate']        = isset($paramArray['expiryDate']) ? $paramArray['expiryDate'] : '';
        $respArray['ip']                = isset($paramArray['ip']) ? $paramArray['ip'] : Util::getIP();
        $respArray['passCodeFlag']      = 'N';
        $respArray['passCodeValue']     = isset($paramArray['passCodeValue']) ? $paramArray['passCodeValue'] : '';
        $respArray['password']          = isset($paramArray['password']) ? $paramArray['password'] : '';
        $respArray['requestDateTime']   = isset($paramArray['requestDateTime']) ? $paramArray['requestDateTime'] : '';
        $respArray['serviceCode']       = '402';//Service Code for Balance Inquiry
        $respArray['sessionKey']        = isset($paramArray['sessionKey']) ? $paramArray['sessionKey'] : $this->getSessionKey();
        $respArray['terminalType']      = isset($paramArray['terminalType']) ? $paramArray['terminalType'] : '';
        $respArray['txnPassFlag']       = isset($paramArray['txnPassFlag']) ? $paramArray['txnPassFlag'] : '';
        $respArray['txnPassword']       = isset($paramArray['txnPassword']) ? $paramArray['txnPassword'] : '';
        $respArray['userId']            = isset($paramArray['userId']) ? $paramArray['userId'] : ECS_API_USERID;
        $respArray['fetchFlag']         = isset($paramArray['fetchFlag']) ? $paramArray['fetchFlag'] : '';
        $respArray['fromDate']          = isset($paramArray['fromDate']) ? $paramArray['fromDate'] : '';
        $respArray['noOfTransactions']  = isset($paramArray['noOfTransactions']) ? $paramArray['noOfTransactions'] : '';
        $respArray['toDate']            = isset($paramArray['toDate']) ? $paramArray['toDate'] : '';
        return $respArray;
        
    }       
    
    

    /**
     * balanceInquiry
     * Used to fetch the balance of the cardholder
     * @param array $userArr
     * @return boolean
     * @throws App_Api_Exception
     * @throws Exception
     */
    public function transactionHistoryFromMVC(array $userArr)
    {
        

        $config = $this->getConfig();

        if(!isset($userArr['cardNumber']) || $userArr['cardNumber'] == '') {
            throw new App_Api_Exception ("Invalid CRN provided");
        }
        
        try { 
        //$authentification = new App_Api_ECS_Authentificator();
        $resp = $this->initSession();
        //Validate Login
        if($resp === false) {
            return false;
        }
       
        //$this->initSession();
        $soapClient = $this->getSoapClient();
        
        $paramArray = $this->_filterTransactionHistoryArray($userArr);
        
        $paramArray['sessionKey'] = $this->getSessionKey();
        $method = $config['transaction_history'];

        if(!isset($method) || $method == '') {
            throw new Exception ("ECS: API Method not defined for balance inquiry");
        }
        
        //Call ECS Cardholder Registration method
        $response = $soapClient->ecsSoapCall($method,$paramArray);

        $this->setLastResponse($soapClient->getLastResponse());
        return $response;
        if(isset($response->responseCode) && !empty($response->responseCode) && $response->responseCode != '0') {
            //$msg = $this->getLastResponseError();
            $this->setError($response->errorDesc);
        }
        
        //validate the response
        if(isset($response->responseCode) && $response->responseCode == '0') {
            //throw new Exception ("ECS: Invalid response recived from server");
            return true;
        }
        return false;
        
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }    
    


        /**
     * Stop Card
     * Used to stop card/CRN in ECS 
     * @param array $userArr
     * @return boolean
     * @throws App_Api_Exception
     */
    public function unblockCard(array $userArr)
    {
        
        try { 
        $config = $this->getConfig();

        if(!isset($userArr['cardNumber']) || $userArr['cardNumber'] == '') {
            throw new App_Api_Exception ("Invalid CRN provided");
        }
        
        $resp = $this->initSession();
        
        //Validate Login
        if($resp === false) {
            return false;
        }
        $soapClient = $this->getSoapClient();

        $paramArray = $this->_filterUnblockArray($userArr);
        $paramArray['sessionKey'] = $this->getSessionKey();
        $method = $config['unblock_card'];

        
        //Call ECS Cardholder Registration method
        //$userArr['sessionKey'] = $this->getSessionKey();
        $response = $soapClient->ecsSoapCall($method,$paramArray);
        $this->setLastResponse($response);
        
        if(isset($response->responseCode) && !empty($response->responseCode) && $response->responseCode != '0') {
            //$msg = $this->getLastResponseError();
            $this->setError($response->errorDesc);
        }
        
        if(isset($response->responseCode) && $response->responseCode == '0') {
            return true;
        }
        return false;
        
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }    

        /**
     * _filterBalanceInquiryArray
     * Used to filter Balance Inquiry Input data
     * @param type $paramArray
     * @return type
     */
    private function _filterUnblockArray($paramArray) {
       
        $respArray['cardNumber']        = isset($paramArray['cardNumber']) ? $paramArray['cardNumber'] : '';
        $respArray['channel']           = isset($paramArray['channel']) ? $paramArray['channel'] : ECS_API_CHANNEL;
        $respArray['componentAuthKey']  = isset($paramArray['componentAuthKey']) ? $paramArray['componentAuthKey'] : ECS_API_USERID;
        $respArray['componentId']       = isset($paramArray['componentId']) ? $paramArray['componentId'] : '';
        $respArray['expiryDate']        = isset($paramArray['expiryDate']) ? $paramArray['expiryDate'] : '';
        $respArray['ip']                = isset($paramArray['ip']) ? $paramArray['ip'] : Util::getIP();
        $respArray['passCodeFlag']      = 'N';
        $respArray['passCodeValue']     = isset($paramArray['passCodeValue']) ? $paramArray['passCodeValue'] : '';
        $respArray['password']          = isset($paramArray['password']) ? $paramArray['password'] : '';
        $respArray['requestDateTime']   = isset($paramArray['requestDateTime']) ? $paramArray['requestDateTime'] : '';
        $respArray['serviceCode']       = '207';//Service Code for Unblock Card
        $respArray['sessionKey']        = isset($paramArray['sessionKey']) ? $paramArray['sessionKey'] : $this->getSessionKey();
        $respArray['terminalType']      = isset($paramArray['terminalType']) ? $paramArray['terminalType'] : '';
        $respArray['txnPassFlag']       = isset($paramArray['txnPassFlag']) ? $paramArray['txnPassFlag'] : '';
        $respArray['txnPassword']       = isset($paramArray['txnPassword']) ? $paramArray['txnPassword'] : '';
        $respArray['userId']            = isset($paramArray['userId']) ? $paramArray['userId'] : ECS_API_USERID;
        $respArray['reasonCode']        = '07';
        $respArray['unblockFee']        = '0';
        return $respArray;
        
    }    

    
        
            
}
