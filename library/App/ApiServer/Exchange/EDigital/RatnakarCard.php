<?php

/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_EDigital_RatnakarCard extends App_ApiServer_Exchange_EDigital_Ratnakar {

    public function __construct($server) {
        parent::__construct($server);
    }
 
 public function RegistrationRequest() {//Do not add comments for method summary
        try {
          
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            $cardNum = '';
            /*
             * Login Validation
             */
            $sessionID = (string)trim($resp->SessionID);
            if (!isset($sessionID) || !$this->isLogin($sessionID)) {
               return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }

            /*
             * Transaction Reference Numver Validation
             */
            Validator_Ratnakar::txnrefnoValidation((string) trim((string) $resp->TransactionRefNo));

            /*
             * Product code validation
             */

            Validator_Ratnakar::productcodeValidation((string) trim($resp->ProductCode), $this->getProductConstant());

            /*
             * Partner Reference Numver Validation
             */
            Validator_Ratnakar_Customer::partnerRefnoValidation((string) trim($resp->PartnerRefNo));

            /*
             * First Name Validation
             */
            Validator_Ratnakar_Customer::nameValidation((string) trim($resp->FirstName),'FirstName');

            /*
             * Last Name Validation
             */
           // Validator_Ratnakar_Customer::nameValidation((string) $resp->LastName,'LastName');

            /*
             * Date of Birth Validation
             */
             $dob = '0000-00-00';
            if ((string) trim($resp->DateOfBirth) != '') {
            Validator_Ratnakar_Customer::dateValidation((string) trim($resp->DateOfBirth));
            $dob = (string) trim($resp->DateOfBirth);
             }

            /*
             * Mobile Validation
             */
            Validator_Ratnakar::mobileValidation((string) trim($resp->Mobile));

            /*
             * Email Validation
             */
            Validator_Ratnakar_Customer::emailValidation((string) trim($resp->Email));

            
            if($this->getProductConstant() == PRODUCT_CONST_RAT_CTY){
             Validator_Ratnakar::cardnoValidation((string) trim($resp->CardNumber)); 
             $cardNum = (string) trim($resp->CardNumber);
            }
            /*
             * Card Activation Type Validation
             */
            $isCardActivated = (string) trim($resp->IsCardActivated);
            $isCardActivated = strtolower($isCardActivated);
            $isCardActivated = strtolower(self::CARD_ACTIVATION_TYPE_NO);
 
            if ($isCardActivated != '') {
                if (strlen($isCardActivated) > 1 || ( $isCardActivated != strtolower(self::CARD_ACTIVATION_TYPE_YES) && $isCardActivated != strtolower(self::CARD_ACTIVATION_TYPE_NO) )) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_ACTIVATED_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_ACTIVATED_CODE);
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_ACTIVATED_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_ACTIVATED_CODE);
            }

            /*
             * Card Dispatch Validation
             */
            $isCardDispatch = (string) trim($resp->IsCardDispatch);
            $isCardDispatch = strtolower($isCardDispatch);
            $isCardDispatch = strtolower(self::CARD_DISPATCH_TYPE_NO);
           
            if ($isCardDispatch != '') {
                if (strlen($isCardDispatch) > 1 || ( $isCardDispatch != strtolower(self::CARD_DISPATCH_TYPE_YES) && $isCardDispatch != strtolower(self::CARD_DISPATCH_TYPE_NO) )) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_DISPATCH_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_DISPATCH_CODE);
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_DISPATCH_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_DISPATCH_CODE);
            }

            /*
             * SMS Flag Validation
             */
            $smsflag = (string) trim($resp->SMSFlag);
            $smsflag = strtolower($smsflag);

            if ($smsflag != '') {
                if (strlen($smsflag) > 1 || ( $smsflag != strtolower(self::SMS_FLAG_TYPE_YES) && $smsflag != strtolower(self::SMS_FLAG_TYPE_NO) )) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_CODE);
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_CODE);
            }

            /*
             * OTP Validation
             */
            $otp = (string) trim($resp->Filler1);
            if ($otp != '') {
                if (strlen($otp) != 6 || !(ctype_digit($otp))) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
            }


            try {
                //  $strCardNumber = (string) $resp->CardNumber;
                $strTansRefNo = (string) trim($resp->TransactionRefNo);
                $strPartnerRefNo = (string) trim($resp->PartnerRefNo);
                $cardNumber = !empty($strCardNumber) ? $strCardNumber : 0;
                $cardActivationDate = (string) trim($resp->ActivationDate);
                $cardDispatchDate = (string) trim($resp->CardDispatchDate);
                $cardActivationDate = '0000-00-00 00:00:00';
                $cardDispatchDate = '0000-00-00 00:00:00';
                
                if(!empty($resp->Filler2) && (string) trim($resp->Filler2) == TYPE_KYC){
                     throw new Exception('Only Non-KYC customer registration is allowed for this program');
                }
                $customerType = TYPE_NONKYC;
                if ($isCardActivated == strtolower(self::CARD_ACTIVATION_TYPE_YES)) {
                    $cardActivationDate = date('Y-m-d h:i:s');
                }

                if ($isCardDispatch == strtolower(self::CARD_DISPATCH_TYPE_YES)) {
                    $cardDispatchDate = date('Y-m-d h:i:s');
                }

                //$crnMaster
                $object = new CustomerTrack();
                $refObject = new Reference();
                $baseTxn = new BaseTxn();
                $txnCode = $baseTxn->generateTxncode();
                $bankObject   = new Banks();
                $responseOTP = $refObject->verifyCustomerOTPAPI(array(
                    'request_type' => 'R',
                    'otp' => (string) trim($resp->Filler1),
                    'mobile' => (string) trim($resp->Mobile),
                ));

                if ($responseOTP == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = $txnCode;
                    $responseObj->ResponseCode = self::OTP_INVALID_RESPONSE_CODE;
                    $responseObj->ResponseMessage = self::OTP_INVALID_RESPONSE_MSG;
                    return $responseObj;
                }

                $params = array();
                $remit_params = array();
                $agent = $this->getAgentConstant();
                $obj = new Corp_Ratnakar_Cardholders();
               

                //$params['Sessionid'] = (string) $resp->Sessionid;
                // $params['CardNumber'] = (string) $cardNumber;//$resp->CardNumber;
                $params['TransactionRefNo'] = $strTansRefNo; //$resp->TransactionRefNo;
                //  $params['MemberId'] = (string) $strPartnerRefNo;//$resp->PartnerRefNo;
                $params['PartnerRefNo'] = (string) $strPartnerRefNo; //$resp->PartnerRefNo;
                $params['CardPackId'] = (string) trim($resp->CardPackId);
                $params['CardNumber'] = $cardNum;//$resp->MemberId;
                $params['ProductId'] = (string) trim($resp->ProductCode);
                $params['Title'] = (string) trim($resp->Title);
                $params['FirstName'] = (string) trim($resp->FirstName);
                $params['MiddleName'] = (string) trim($resp->MiddleName);
                $params['LastName'] = (string) trim($resp->LastName);
                // $params['NameOnCard'] = (string) $resp->NameOnCard;
                $params['Gender'] = (string) trim($resp->Gender);
                $params['DateOfBirth'] = $dob;
                $params['Mobile'] = (string) trim($resp->Mobile);
                $params['Mobile2'] = (string) trim($resp->Mobile2);
                $params['Email'] = (string) trim($resp->Email);
                $params['MotherMaidenName'] = (string) trim($resp->MotherMaidenName);
                $params['id_proof_type'] = (string) trim($resp->IdentityProofType);
                $params['id_proof_number'] = (string) trim($resp->IdentityProofDetail);
                $params['address_proof_type'] = (string) trim($resp->AddressProofType);
                $params['address_proof_number'] = (string) trim($resp->AddressProofDetail);
                $params['Landline'] = (string) trim($resp->Landline);
                $params['AddressLine1'] = (string) trim($resp->AddressLine1);
                $params['AddressLine2'] = (string) trim($resp->AddressLine2);
                $params['City'] = (string) trim($resp->City);
                $params['State'] = (string) trim($resp->State);
                $params['Country'] = (string) trim($resp->Country);
                $params['Pincode'] = (string) trim($resp->Pincode);
                $params['CommAddressLine1'] = (string) trim($resp->AddressLine1);
                $params['CommAddressLine2'] = (string) trim($resp->AddressLine2);
                $params['CommCity'] = (string) trim($resp->City);
                $params['CommState'] = (string) trim($resp->State);
                $params['CommCountry'] = (string) trim($resp->Country);
                $params['CommPin'] = (string) trim($resp->Pincode);
                // $params['Occupation'] = (string) $resp->Occupation;
                $params['EmployerName'] = (string) trim($resp->EmployerName);
                $params['corp_address_line1'] = (string) trim($resp->EmployerAddressLine1);
                $params['corp_address_line2'] = (string) trim($resp->EmployerAddressLine2);
                $params['corp_city'] = (string) trim($resp->EmployerCity);
                $params['corp_state'] = (string) trim($resp->EmployerState);
                $params['corp_country'] = (string) trim($resp->EmployerCountry);
                $params['corp_pin'] = (string) trim($resp->EmployerPin);
                $params['IsCardActivated'] = (string) trim($resp->IsCardActivated);
                $params['ActivationDate'] = $cardActivationDate;
                $params['IsCardDispatch'] = (string) trim($resp->IsCardDispatch);
                $params['CardDispatchDate'] = $cardDispatchDate;
                $params['OTP'] = (string) trim($resp->Filler1);
                $params['by_api_user_id'] = $agent;
                $params['customer_type'] = $customerType;
                $params['status_ops'] = STATUS_APPROVED;
                $params['status_ecs'] = STATUS_SUCCESS;
                $params['status'] = STATUS_INACTIVE;
                $params['txnCode'] = $txnCode;
                $params['manageType'] = $this->getManageTypeConstant();
		$params['channel'] =  CHANNEL_API ;

                $remit_params['product_id'] = (string) trim($resp->ProductCode);
                $remit_params['name'] = (string) trim($resp->FirstName);
                $remit_params['middle_name'] = (string) trim($resp->MiddleName);
                $remit_params['last_name'] = (string) trim($resp->LastName);
                $remit_params['address'] = (string) trim($resp->AddressLine1);
                $remit_params['address_line2'] = (string) trim($resp->AddressLine2);
                $remit_params['city'] = (string) trim($resp->City);
                $remit_params['state'] = (string) trim($resp->State);
                $remit_params['pincode'] = (string) trim($resp->Pincode);
                $remit_params['mobile'] = (string) trim($resp->Mobile);
                $remit_params['dob'] = $dob;
                $remit_params['mother_maiden_name'] = (string) trim($resp->MotherMaidenName);
                $remit_params['email'] = (string) trim($resp->Email);
                $remit_params['by_agent_id'] = $agent;
                $remit_params['txn_code'] = $txnCode;
                $remit_params['manageType'] = $this->getManageTypeConstant();
                //$params['Filler3'] = (string) $resp->Filler3;
                //$params['Filler4'] = (string) $resp->Filler4;
                //$params['Filler5'] = (string) $resp->Filler5;
              
                /*
                 * checkDuplicateMobile : Checking duplicate mobile number for customer
                 */
                $respMobile = $obj->checkDuplicateMobile(array(
                    'product_id' => $params['ProductId'],
                    'mobile' => (string) trim($resp->Mobile),
                ));
                if ($respMobile == TRUE) {
                    return self::Exception('Mobile Number already in use', App_ApiServer_Exchange::$INVALID_RESPONSE);
                }
                /*
                 * checkDuplicateMemberID : Checking duplicate member id for customer
                 */
                $respMemberID = $obj->checkDuplicatePartnerRefNo(array(
                    'product_id' => $params['ProductId'],
                    'partner_ref_no' => $params['PartnerRefNo'],
                ));
                if ($respMemberID == TRUE) {
                    return self::Exception('Partner Referance Number already in use', App_ApiServer_Exchange::$INVALID_RESPONSE);
                }
                
                 /*
                 * checkDuplicateTransNum : Checking duplicate Transaction Referance Number for customer
                 */
                $respTrans = $obj->checkDuplicateTransNum(array(
                    'product_id' => $params['ProductId'],
                    'txnrefnum' => $strTansRefNo,
                ));
                if ($respTrans == TRUE) {
                    return self::Exception('Transaction Referance Number already in use', App_ApiServer_Exchange::$INVALID_RESPONSE);
                }
                
                /*
                 * checkDuplicateEmail : Checking duplicate email id for customer
                 */
//                $respEmail = $obj->checkDuplicateEmail(array(
//                    'product_id' => $params['ProductId'],
//                    'email' => (string) trim($resp->Email),
//                ));
//                if ($respEmail == TRUE) {
//                    return self::Exception('Email address already in use', App_ApiServer_Exchange::$INVALID_RESPONSE);
//                }
                
                $bankInfo = $bankObject->getBankidByProductid($params['ProductId']);
                if(!empty($bankInfo) ){
                 $params['bank_id'] = $bankInfo['bank_id'];    
                 $remit_params['bank_id'] = $bankInfo['bank_id'];   
                }else{
                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);   
                }
                // txn Code
                  if(($params['manageType'] == CORPORATE_MANAGE_TYPE)){
                // get CRN info
                    $crnMaster = new CRNMaster();
                    $crnInfo = $crnMaster->getCRNInfo($params['CardNumber'], $params['CardPackId'], $params['MemberId']);
                    if(!empty($crnInfo)){
                    // update status CRN
                    $crnMaster->updateStatusById(array('status' => STATUS_USED), $crnInfo->id);
                    }else{
                      throw new Exception('Invalid Card Details Provided');  
                    }
                    
               }
                
                $responseCustID = $obj->addCustomerECSAPI($params);
                if($responseCustID > 0){
                $response = $obj->mapCardholderToRemitter($remit_params, $responseCustID);
                }else{
                    $errorMsg = $obj->getError();
                    $errorMsg = empty($errorMsg) ? self::CUSTOMER_REGISTRATION_FAIL_MSG : $errorMsg;
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                     $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->PartnerRefNo = (string) trim($resp->PartnerRefNo);
                    $responseObj->ResponseCode = self::CUSTOMER_REGISTRATION_FAIL_CODE;
                    $responseObj->ResponseMessage = $errorMsg;   
                }
                //  $txnCode = $obj->getTxncode();
              
                if (($response == TRUE) && ($responseCustID > 0) ){
                 
//                    if ($smsflag == strtolower('Y')) {
//                    // Send SMS   
//                    $params = array(
//                        'product_id' => (string) $resp->ProductCode,
//                        'mobile' => (string) $resp->Mobile,
//                    );
//                    $obj->generateSMSDetails($params, $smsType = CUST_REGISTRATION);
//                    }
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->PartnerRefNo = (string) trim($resp->PartnerRefNo);
                    $responseObj->AckNo = $txnCode; //$baseTxn->getTxncode();
                    $responseObj->ResponseCode = self::CUSTOMER_REGISTRATION_SUCC_CODE;
                    $responseObj->ResponseMessage = self::CUSTOMER_REGISTRATION_SUCC_MSG;
                     // Update otp entry 
                    
                    $upadteOTP = $refObject->updateCustomerOTPAPI(array(
                    'request_type' => 'R',
                    'id' => $responseOTP['id'],
                ));
                } else {
                    $errorMsg = $obj->getError();
                    $errorMsg = empty($errorMsg) ? self::CUSTOMER_REGISTRATION_FAIL_MSG : $errorMsg;
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                     $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->PartnerRefNo = (string) trim($resp->PartnerRefNo);
                    $responseObj->ResponseCode = self::CUSTOMER_REGISTRATION_FAIL_CODE;
                    $responseObj->ResponseMessage = $errorMsg;
                }
                return $responseObj;
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $code = (empty($code)) ? self::ERROR_SYSTEM_ERROR : $code;
                $message = $e->getMessage();
                $message = (empty($message)) ? self::CUSTOMER_REGISTRATION_FAIL_MSG : $message;
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, ErrorCodes::ERROR_SYSTEM_ERROR);
            }
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $this->_soapServer->_getLogger()->__setException($e->getMessage());
            return self::Exception($e->getMessage(), self::CUSTOMER_REGISTRATION_FAIL_CODE);
        }
    }
    
    
    
 
 public function TransactionRequest() {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);


            if( !isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
             //    return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
             }
            // Currency Check
            if ((string) trim($resp->Currency) != '') {
                if ((string) trim($resp->Currency) != CURRENCY_INR_CODE) {
                  throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CURRENCY_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CURRENCY_CODE);
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CURRENCY_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CURRENCY_CODE);
            }

            //	Amount valid check  
            if ((string) trim($resp->Amount) != '') {
                if (!is_numeric((string) trim($resp->Amount))) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_CODE);
                }
            } else {
                   throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_CODE);
            }


            Validator_Ratnakar::productcodeValidation((string) trim($resp->ProductCode), $this->getProductConstant());
            Validator_Ratnakar::txnindicatorValidation((string) trim($resp->TxnIndicator));
            Validator_Ratnakar::cardtypeValidation((string) trim($resp->CardType));
            Validator_Ratnakar::txnNumValidation((string) trim($resp->TxnNo));
            Validator_Ratnakar_Customer::memberIdCardNumValidation((string) trim($resp->MemberIDCardNo));
            Validator_Ratnakar_Customer::transactionNarrationValidation((string) trim($resp->Narration));
            Validator_Ratnakar_Beneficiary::remitterwalletcodeValidation((string) trim($resp->WalletCode));

            // Check OTP
//            $otp = (string) $resp->OTP;
//            if ($otp != '') {
//                if (strlen($otp) != 6 || !(ctype_digit($otp))) {
//                    throw new Exception('OTP is not valid');
//                }
//            } else {
//                throw new Exception('OTP is mandatory');
//            }

            // Check SMS Flag
            $smsflag = (string) trim($resp->SMSFlag);
            $smsflag = strtolower($smsflag);

            if ($smsflag != '') {
                if (strlen($smsflag) > 1 || ( $smsflag != strtolower(self::SMS_FLAG_TYPE_YES) && $smsflag != strtolower(self::SMS_FLAG_TYPE_NO) )) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_CODE);
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_CODE);
            }
            // Check TxnIdentifierType
            $txnidentifierflag = (string) trim($resp->TxnIdentifierType);
            $txnidentifierflag = strtolower($txnidentifierflag);

            if ($txnidentifierflag != '') {
                if (strlen($txnidentifierflag) > 3 || ( $txnidentifierflag != strtolower(self::TXN_IDENTIFIER_TYPE_MOB) && $txnidentifierflag != strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER) )) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_CODE);
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_CODE);
            }

            try {

                $refObject = new Reference();

                $object = new Corp_Ratnakar_Cardholders();
                $param['product_id'] = (string) trim($resp->ProductCode);

                if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_MOB)) {
                    $param['mobile'] = (string) trim($resp->MemberIDCardNo);
                } else if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER)) {
                    $param['partner_ref_no'] = (string) trim($resp->MemberIDCardNo);
                }
                $custInfo = $object->getCustomerInfoBy($param);
                
                if ($custInfo == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                }
                $obj = new Corp_Ratnakar_Cardload();
                $productConst = $this->getProductConstant();
                $productModel = new Products();
                $productID = $productModel->getProductIDbyConst($productConst); 
                $loadDetails = $obj->getLoadDetails(array('txn_no' => (string) trim($resp->TxnNo),'product_id'=>$productID));
                if (!empty($loadDetails) ) {
                    return self::Exception('Transaction number already in use', App_ApiServer_Exchange::$INVALID_RESPONSE);
                }
                //Do card load
                $baseTxn = new BaseTxn();
                $txnCode = $baseTxn->generateTxncode();
                $bankObject   = new Banks();
                
                $params['cardholder_id'] = $custInfo['id'];
                $params['product_id'] = (string) trim($resp->ProductCode);
                $params['wallet_code'] = (string) trim($resp->WalletCode);
                $params['amount'] = (string) trim($resp->Amount);
                $params['txn_no'] = (string) trim($resp->TxnNo);
                $params['txn_identifier_type'] = (string) trim($resp->TxnIdentifierType);//mob
                $params['txn_identifier_num'] = (string) trim($resp->MemberIDCardNo);//mob
                $params['narration'] = (string) trim($resp->Narration);//mob
                $params['card_type']= trim($resp->CardType);// nCardType
                $params['corporate_id'] = 0;
                $params['mode'] = (string) trim($resp->TxnIndicator);//CR/DR
                $params['by_api_user_id'] = $this->getAgentConstant();//pat api const
                $params['bank_product_const'] = $this->getBankProductConstant();
                $params['txn_code'] = $txnCode;
                $params['manageType'] = $this->getManageTypeConstant();
		$params['channel'] = CHANNEL_API ;
              
                if($this->getProductConstant() == PRODUCT_CONST_RAT_CTY){
                $LoadExpiry = (string) trim($resp->Filler1);
                $Date = date('Y-m-d');
                if (ctype_digit($LoadExpiry) && $LoadExpiry > 0){
                $params['date_expiry'] = date('Y-m-d', strtotime($Date. " + $LoadExpiry days"));
                }
                else{
                $params['date_expiry'] = '';
                }
                }
                
                $bankInfo = $bankObject->getBankidByProductid($params['product_id']);
                if(!empty($bankInfo) ){
                 $params['bank_id'] = $bankInfo['bank_id'];    
                }else{
                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);   
                }
                
                $flg = $obj->doCardloadECSAPI($params);
               
                if ($flg == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnNo = (string) trim($resp->TxnNo);
                    $responseObj->AckNo = $txnCode;
                    $responseObj->Filler1 = '';
                    $responseObj->Filler2 = '';
                    $responseObj->ResponseCode = self::TRANSACTION_REQUEST_FAILURE_CODE;
                    $responseObj->ResponseMessage = self::TRANSACTION_REQUEST_FAILURE_MSG;
                    return $responseObj;
                } 
                $curCode = CURRENCY_INR;

                $responseObj = new stdClass();
               
                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->TxnNo = (string) trim($resp->TxnNo);
                $responseObj->AckNo = $txnCode;
                $responseObj->Filler1 = '';
                $responseObj->Filler2 = '';
                $responseObj->ResponseCode = self::TRANSACTION_REQUEST_SUCCESS_CODE;
                $responseObj->ResponseMessage = self::TRANSACTION_REQUEST_SUCCESS_MSG;

                return $responseObj;
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $code = (empty($code)) ? self::TRANSACTION_REQUEST_FAILURE_CODE : $code;
                $message = $e->getMessage();
                $message = (empty($message)) ? self::TRANSACTION_REQUEST_FAILURE_MSG : $message;
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $code = (empty($code)) ? self::TRANSACTION_REQUEST_FAILURE_CODE : $code;
                $message = $e->getMessage();
                $message = (empty($message)) ? self::TRANSACTION_REQUEST_FAILURE_MSG : $message;
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
        }
    
    }
}
