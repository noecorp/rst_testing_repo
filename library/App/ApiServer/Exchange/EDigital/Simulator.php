<?php

/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_EDigital_Simulator extends App_ApiServer_Exchange_EDigital {

    public function __construct($server) {
        parent::__construct($server);
        
    }
    
    

    public function GenerateOTPRequest() {//Do not add comments for method summary
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
           
            if (!isset($resp->SessionID) || !in_array($resp->SessionID,$this->sessionValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
           }
           

            /*
             * Product Code Validation
             */
             $productCode = (string)trim($resp->ProductCode);
           if (!isset($resp->ProductCode) || !in_array($productCode,$this->productValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);
           }
            /*
             * Request Type Validation
             */
            Validator_Ratnakar_Customer::requesttypeValidation((string) trim($resp->RequestType));

            /*
             * Narration Validation
             */
            if (strtolower(trim($resp->RequestType)) == strtolower(self::REQUEST_TYPE_TRANSFER )) {
                Validator_Ratnakar_Customer::narrationValidation((string) trim($resp->Narration));
            }
            /*
             * IsOriginal Validation
             */
            Validator_Ratnakar_Customer::isOriginalRequestValidation((string) trim($resp->IsOriginal));

            /*
             * OriginalAckNo Validation
             */
            if (strtolower(trim($resp->IsOriginal)) == 'n') {
                Validator_Ratnakar_Customer::originalAckNumValidation((string) trim($resp->OriginalAckNo));
            }

            /*
             * Unicode Flag validation for filter 1
             */
            Validator_Ratnakar_Customer::uniqueCodeflagMPValidation((string) strtolower(trim($resp->Filler1)));

            /*
             * Unicode Flag validation for filter 2
             */
          //  Validator_Ratnakar_Customer::uniqueCodeValidation((string) $resp->Filler2);

            if (strtolower(trim($resp->Filler1)) == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                if((string) trim($resp->Filler2) !='' ){
                Validator_Ratnakar::mobileValidation((string) trim($resp->Filler2));
                }else{
                  Validator_Ratnakar::mobileValidation((string) trim($resp->Mobile)); 
                }
            } elseif (strtolower(trim($resp->Filler1)) == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                 Validator_Ratnakar_Customer::partnerRefnoValidation((string) trim($resp->Filler2));
            }

            try {

              
                if (strtolower(trim($resp->RequestType)) == 'r') {
                    
                    $ackNo = '';
                    if (strtolower(trim($resp->IsOriginal)) == 'n') {
                        if (isset($resp->OriginalAckNo) && !empty($resp->OriginalAckNo)) {
                            $ackNo = trim($resp->OriginalAckNo);
                        }
                    }
                    $mobile = (string) trim($resp->Mobile);
                     $uniqueCodeFlag = (string) strtolower(trim($resp->Filler1));
                    if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                         if((string) trim($resp->Filler2) !='' ){
                            $mobile = (string) trim($resp->Filler2);
                         }else{
                            $mobile = (string) trim($resp->Mobile);     
                         }
                    }
                   
                    /*
                     * Varify Mobile Number 
                     */
                    if(in_array($mobile,$this->verifiedMobileNum)){
                        
                        // Generate OTP
                        $gInfo =  rand(111111,999999);
                    }else{
                         return self::Exception('Mobile Number is not authorised to get OTP', App_ApiServer_Exchange::$INVALID_RESPONSE);
                   
                    }
                      
                } else if (strtolower(trim($resp->RequestType)) == 'e') {
                    $ackNo = '';
                    if (strtolower(trim($resp->IsOriginal)) == 'n') {
                        if (isset($resp->OriginalAckNo) && !empty($resp->OriginalAckNo)) {
                            $ackNo = trim($resp->OriginalAckNo);
                        }
                    }
                    
                     $uniqueCodeFlag = (string) strtolower(trim($resp->Filler1));
                     $custvarifyvalue = (string) trim($resp->Filler2);
                     if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                        if(in_array($custvarifyvalue,$this->verifiedMobileNum)){
                         $customerInfo = TRUE;  
                        }
                    } else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        if(in_array($custvarifyvalue,$this->customerPARNum)){
                         $customerInfo = TRUE;  
                        }
                    }
                    
                    if ($customerInfo == FALSE ) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = '';
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                        $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                        return $responseObj;
                    }else{
                        $gInfo =  rand(111111,999999);
                    }
                   }else if (strtolower(trim($resp->RequestType)) == 'b') {
                   
                    $ackNo = '';
                    if (strtolower(trim($resp->IsOriginal)) == 'n') {
                        if (isset($resp->OriginalAckNo) && !empty($resp->OriginalAckNo)) {
                            $ackNo = trim($resp->OriginalAckNo);
                        }
                    }
                   
                    $uniqueCodeFlag = (string) strtolower(trim($resp->Filler1));
                    $custvarifyvalue = (string) trim($resp->Filler2);
                     if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                        if(in_array($custvarifyvalue,$this->verifiedMobileNum)){
                         $customerInfo = TRUE;  
                        }
                    } else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        if(in_array($custvarifyvalue,$this->customerPARNum)){
                         $customerInfo = TRUE;  
                        }
                    }
                    if ($customerInfo == FALSE ) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = '';
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                        $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                        return $responseObj;
                    }else{
                        $gInfo =  rand(111111,999999);
                    }
                    
                    
                }elseif (strtolower(trim($resp->RequestType)) == 't') {

                   $uniqueCodeFlag = (string) strtolower(trim($resp->Filler1));
                    $custvarifyvalue = (string) trim($resp->Filler2);
                    if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                        if(in_array($custvarifyvalue,$this->verifiedMobileNum)){
                         $customerInfo = TRUE;  
                        }
                    } else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                         if(in_array($custvarifyvalue,$this->customerPARNum)){
                         $customerInfo = TRUE;  
                        }
                    }
                    
                   if ($customerInfo == FALSE) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = '';
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                        $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                        return $responseObj;
                    }else{
                        $gInfo =  rand(111111,999999);
                    }
                }elseif (strtolower(trim($resp->RequestType)) == 'i') {
                   $uniqueCodeFlag = (string) strtolower(trim($resp->Filler1));
                    $custvarifyvalue = (string) trim($resp->Filler2);
                    if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                        if(in_array($custvarifyvalue,$this->verifiedMobileNum)){
                         $customerInfo = TRUE;  
                        }
                    } else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                         if(in_array($custvarifyvalue,$this->customerPARNum)){
                         $customerInfo = TRUE;  
                        }
                    }
                    if ($customerInfo == FALSE) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = '';
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                        $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                        return $responseObj;
                    }else{
                        $gInfo =  rand(111111,999999);
                    }
                } elseif (strtolower(trim($resp->RequestType)) == 'l') {
                   
                    $uniqueCodeFlag = (string) strtolower(trim($resp->Filler1));
                    $custvarifyvalue = (string) trim($resp->Filler2);
                    if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                        if(in_array($custvarifyvalue,$this->verifiedMobileNum)){
                         $customerInfo = TRUE;  
                        }
                    } else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                         if(in_array($custvarifyvalue,$this->customerPARNum)){
                         $customerInfo = TRUE;  
                        }
                    }
                  
                    if ($customerInfo == FALSE) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = '';
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                        $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                        return $responseObj;
                    }else{
                        $gInfo =  rand(111111,999999);
                    }
                }
                //echo $gInfo.'**';exit;
                $responseObj = new stdClass();
                if ($gInfo == FALSE) {
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = '';
                    $responseObj->ResponseCode = self::OTP_FAILED_RESPONSE_CODE;
                    $responseObj->ResponseMessage = self::OTP_FAILED_RESPONSE_MSG;
                } else {
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = $gInfo;
                    $responseObj->ResponseCode = self::OTP_SUCCSSES_RESPONSE_CODE;
                    $responseObj->ResponseMessage = self::OTP_SUCCSSES_RESPONSE_MSG;
                }
                return $responseObj;
            } catch (App_Exception $e) {
              //  $this->_soapServer->_getLogger()->__setException($e->getMessage());
               // App_Logger::log(serialize($e), Zend_Log::ERR);
                $responseObj = new stdClass();
                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->AckNo = '';
                $responseObj->ResponseCode = self::OTP_FAILED_RESPONSE_CODE;
                $responseObj->ResponseMessage = $e->getMessage();
                return $responseObj;
            }
        } catch (Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $code = $e->getCode();
            $code = (empty($code)) ? self::OTP_FAILED_RESPONSE_CODE : $code;
            $responseObj = new stdClass();
            $responseObj->SessionID = (string) $resp->SessionID;
            $responseObj->AckNo = '';
            $responseObj->ResponseCode = $code;
            $responseObj->ResponseMessage = $e->getMessage();
            return $responseObj;
        }
    }

    public function RegistrationRequest() {//Do not add comments for method summary
        try {
          
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            
            /*
             * Login Validation
             */
            $sessionID = (string)trim($resp->SessionID);
           if (!isset($resp->SessionID) || !in_array($sessionID,$this->sessionValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
           }

            /*
             * Transaction Reference Numver Validation
             */
            Validator_Ratnakar::txnrefnoValidation((string) trim((string) $resp->TransactionRefNo));

            /*
             * Product code validation
             */

            $productCode = (string)trim($resp->ProductCode);
           if (!isset($resp->ProductCode) || !in_array($productCode,$this->productValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);
           }
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
            Validator_Ratnakar_Customer::dateValidation((string) trim($resp->DateOfBirth));

            /*
             * Mobile Validation
             */
            Validator_Ratnakar::mobileValidation((string) trim($resp->Mobile));

            /*
             * Email Validation
             */
            Validator_Ratnakar_Customer::emailValidation((string) trim($resp->Email));

            /*
             * Card Activation Type Validation
             */
            $isCardActivated = (string) trim($resp->IsCardActivated);
            $isCardActivated = strtolower($isCardActivated);

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
                
                $txnCode = rand(111111,999999);
                //$crnMaster
                $responseOTP = FALSE; 
               
               if(in_array($otp,$this->generatedOTPNum)){
                 $responseOTP = TRUE;  
               }
               
               if ($responseOTP == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = $txnCode;
                    $responseObj->ResponseCode = self::OTP_INVALID_RESPONSE_CODE;
                    $responseObj->ResponseMessage = self::OTP_INVALID_RESPONSE_MSG;
                    return $responseObj;
                }

               
                /*
                 * checkDuplicateMobile : Checking duplicate mobile number for customer
                 */
               $mobile  = (string) trim($resp->Mobile);
               $respMobile = FALSE; 
               if(in_array($mobile,$this->customerMobileNum)){
                 $respMobile = TRUE;  
               }
                if ($respMobile == TRUE) {
                    return self::Exception('Mobile Number already in use', App_ApiServer_Exchange::$INVALID_RESPONSE);
                }
                /*
                 * checkDuplicateMemberID : Checking duplicate member id for customer
                 */
               $respMemberID = FALSE; 
               if(in_array($strPartnerRefNo,$this->customerPARNum)){
                 $respMemberID = TRUE;  
               }
                
                if ($respMemberID == TRUE) {
                    return self::Exception('Partner Referance Number already in use', App_ApiServer_Exchange::$INVALID_RESPONSE);
                }
                
                $respTransRef = FALSE; 
                if(in_array($strTansRefNo,$this->customerTranRefNum)){
                  $respTransRef = TRUE;  
                }
                if ($respTransRef == TRUE) {
                    return self::Exception('Transaction Referance Number already in use', App_ApiServer_Exchange::$INVALID_RESPONSE);
                }
                
                 $randValue = rand(0,3);
                 $txnCode = $this->generatedQueryRefNum[$randValue];
              
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->PartnerRefNo = (string) trim($resp->PartnerRefNo);
                    $responseObj->AckNo = $txnCode; //$baseTxn->getTxncode();
                    $responseObj->ResponseCode = self::CUSTOMER_REGISTRATION_SUCC_CODE;
                    $responseObj->ResponseMessage = self::CUSTOMER_REGISTRATION_SUCC_MSG;
                     // Update otp entry 
                
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
//
    public function BeneficiaryRegistrationRequest() {//Do not add comments for method summary
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);

            $sessionID = (string)trim($resp->SessionID);
           if (!isset($resp->SessionID) || !in_array($sessionID,$this->sessionValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
           }
           
           $productCode = (string)trim($resp->ProductCode);
           if (!isset($resp->ProductCode) || !in_array($productCode,$this->productValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);
           }
           
            Validator_Ratnakar::txnrefnoValidation((string) trim((string) $resp->TransactionRefNo));
            Validator_Ratnakar_Beneficiary::titleValidation((string) trim($resp->Title));
            Validator_Ratnakar_Beneficiary::nameValidation((string) trim($resp->Name));
//            Validator_Ratnakar_Beneficiary::nameValidation((string) $resp->LastName);
//            Validator_Ratnakar_Beneficiary::middlenameValidation((string)$resp->MiddleName);
            Validator_Ratnakar::mobileValidation((string) trim($resp->Mobile));
//            Validator_Ratnakar_Beneficiary::mobile2Validation((string)$resp->Mobile2);
//            Validator_Ratnakar_Beneficiary::landlineValidation((string)$resp->Landline);
            Validator_Ratnakar_Beneficiary::emailValidation((string) trim($resp->Email));
            Validator_Ratnakar_Beneficiary::addressValidation((string) trim($resp->AddressLine1));
            Validator_Ratnakar_Beneficiary::addressValidation((string) trim($resp->AddressLine2));
//            Validator_Ratnakar_Beneficiary::cityValidation((string)$resp->City);
//            Validator_Ratnakar_Beneficiary::stateValidation((string)$resp->State);
//            Validator_Ratnakar_Beneficiary::pincodeValidation((string)$resp->Pincode);
//           
            // Check OTP
            $otp = (string) trim($resp->OTP);
            if ($otp != '') {
                if (strlen($otp) != 6 || !(ctype_digit($otp))) {
                     throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
                }
            } 
//            else {
//                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
//            }
            // Check SMS Flag
            $smsflag = (string) trim($resp->SMSFlag);
            $smsflag = strtolower($smsflag);
            $transrefNo = (string) trim((string) $resp->TransactionRefNo);
            if ($smsflag != '') {
                if (strlen($smsflag) > 1 || ( $smsflag != strtolower(self::SMS_FLAG_TYPE_YES) && $smsflag != strtolower(self::SMS_FLAG_TYPE_NO) )) {
                      throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_CODE);
                }
            } else {
                     throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_CODE);
            }


            // Check Remitter Flag
            $remitterflag = (string) trim($resp->RemitterFlag);
            $remitterflag = strtolower($remitterflag);

            if ($remitterflag != '') {
                if (strlen($remitterflag) > 1 || ( $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_MOB) && $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER) )) {
                   throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE);
                }else{
                    if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                        Validator_Ratnakar::mobileValidation((string) trim($resp->RemitterCode));
                    } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        Validator_Ratnakar_Customer::partnerRefnoValidation((string) trim($resp->RemitterCode));
                  }
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE);
            }
            // Bank details validation
            Validator_Ratnakar_Beneficiary::bankDetailsValidation(array(
                'ifsc_code' => (string) trim($resp->BankIfscode),
                'bank_account_number' => (string) trim($resp->BankAccountNumber),
//                'bank_name' => (string) $resp->BankName,
//                'branch_name' => (string) $resp->BankBranch,
//                'branch_city' => (string) $resp->BankCity,
//                'branch_address' => (string) $resp->BankState,
            ));

            try {

                $customerInfo == FALSE;
                $custvarifyvalue = (string) trim($resp->RemitterCode);
                if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOB)) {
                     if(in_array($custvarifyvalue,$this->verifiedMobileNum)){
                         $customerInfo = TRUE;  
                        }
                } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                    if(in_array($custvarifyvalue,$this->customerPARNum)){
                         $customerInfo = TRUE;  
                        }
                }

                // get Remitter id	 
                 if($customerInfo == FALSE){ 
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                    $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                    return $responseObj;
                }
                
                $responseOTP = FALSE; 
                $txnCode = rand(111111,999999);
               if(in_array($otp,$this->generatedOTPNum)){
                 $responseOTP = TRUE;  
               }
                if ($responseOTP == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = $txnCode;
                    $responseObj->ResponseCode = self::OTP_INVALID_RESPONSE_CODE;
                    $responseObj->ResponseMessage = self::OTP_INVALID_RESPONSE_MSG;
                   return $responseObj;
                }

               
                $randValue = rand(0,4);
                $beneCode = $this->beneCodeArr[$randValue];
                $queryRefNum = $this->generatedQueryRefNum[$randValue];

                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->AckNo = $queryRefNum; //$baseTxn->getTxncode();
                    $responseObj->BeneficiaryCode = $beneCode; //$baseTxn->getTxncode();
                    $responseObj->ResponseCode = self::BENEFICIARY_REGISTRATION_SUCC_CODE;
                    $responseObj->ResponseMessage = self::BENEFICIARY_REGISTRATION_SUCC_MSG;
                   
                return $responseObj;
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                    $code = $e->getCode();
                    $code = (empty($code)) ? self::BENEFICIARY_REGISTRATION_FAIL_CODE : $code;
                    $message = $e->getMessage();
                    $message = (empty($message)) ? self::BENEFICIARY_REGISTRATION_FAIL_MSG : $message;
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) $resp->TransactionRefNo;
                    $responseObj->ResponseCode = $code;
                    $responseObj->ResponseMessage = $message;
            //    $this->_soapServer->_getLogger()->__setException($e->getMessage());
             //   return self::Exception($e->getMessage(), ErrorCodes::ERROR_SYSTEM_ERROR);
            }
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $code = $e->getCode();
            $code = (empty($code)) ? self::BENEFICIARY_REGISTRATION_FAIL_CODE : $code;
            $message = $e->getMessage();
            $message = (empty($message)) ? self::BENEFICIARY_REGISTRATION_FAIL_MSG : $message;
            $this->_soapServer->_getLogger()->__setException($message);
            return self::Exception($message, $code);
        }
    }

    // Account block request 

    public function AccountBlockRequest($obj) {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            $sessionID = (string)trim($resp->SessionID);
           if (!isset($resp->SessionID) || !in_array($sessionID,$this->sessionValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
           }
           
           $productCode = (string)trim($resp->ProductCode);
           if (!isset($resp->ProductCode) || !in_array($productCode,$this->productValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);
           }
           
            Validator_Ratnakar_Customer::memberIdCardNumValidation((string) trim($resp->MemberIDCardNo));
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
                $customerInfo = FALSE;
                $custvarifyvalue = (string) trim($resp->MemberIDCardNo);
                if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_MOB)) {
                    if(in_array($custvarifyvalue,$this->verifiedMobileNum)){
                         $customerInfo = TRUE;  
                        }
                } else if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER)) {
                    if(in_array($custvarifyvalue,$this->customerPARNum)){
                         $customerInfo = TRUE;  
                        }
                }
              
                if ($customerInfo == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                    $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                    return $responseObj;
                }

                // Change 
                $flg = true;
                
                $responseObj = new stdClass();
                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->TxnIdentifierType = (string) trim($resp->TxnIdentifierType);
                $responseObj->MemberIDCardNo = (string) trim($resp->MemberIDCardNo);
                $responseObj->ResponseCode = self::BLOCK_SUCCSSES_RESPONSE_CODE;
                $responseObj->ResponseMessage = self::BLOCK_SUCCSSES_RESPONSE_MSG;
                $responseObj->AccountBlockStatus = self::ACCOUNT_BLOCK_STATUS_BLOCKED;
                $responseObj->AccountBlockDateTime = Date('Y-m-d h:m:s');

                return $responseObj;
            } catch (App_Exception $e) {
                $this->_soapServer->_getLogger()->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                return self::Exception($e->getMessage(), '11');
            }
        } catch (Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());
            App_Logger::log(serialize($e), Zend_Log::ERR);
            return self::Exception($e->getMessage(), '12');
        }
    }

    public function AccountUnBlockRequest($obj) {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            $sessionID = (string)trim($resp->SessionID);
           if (!isset($resp->SessionID) || !in_array($sessionID,$this->sessionValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
           }
           
           $productCode = (string)trim($resp->ProductCode);
           if (!isset($resp->ProductCode) || !in_array($productCode,$this->productValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);
           }
           
            Validator_Ratnakar_Customer::memberIdCardNumValidation((string) trim($resp->MemberIDCardNo));
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

                $customerInfo = FALSE;
                $custvarifyvalue = (string) trim($resp->MemberIDCardNo);
                if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_MOB)) {
                    if(in_array($custvarifyvalue,$this->verifiedMobileNum)){
                         $customerInfo = TRUE;  
                        }
                } else if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER)) {
                    if(in_array($custvarifyvalue,$this->customerPARNum)){
                         $customerInfo = TRUE;  
                        }
                }
              
                if ($customerInfo == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                    $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                    return $responseObj;
                }
                // Change 
                
                $flg = true;
                $responseObj = new stdClass();
                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->TxnIdentifierType = (string) trim($resp->TxnIdentifierType);
                $responseObj->MemberIDCardNo = (string) trim($resp->MemberIDCardNo);
                $responseObj->ResponseCode = self::UNBLOCK_SUCCSSES_RESPONSE_CODE;
                $responseObj->ResponseMessage = self::UNBLOCK_SUCCSSES_RESPONSE_MSG;
                $responseObj->AccountBlockStatus = self::ACCOUNT_BLOCK_STATUS_ACTIVE;
                $responseObj->ProductCode = trim($resp->ProductCode);
                return $responseObj;
            } catch (App_Exception $e) {
                $this->_soapServer->_getLogger()->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                return self::Exception($e->getMessage(), '11');
            }
        } catch (Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());
            App_Logger::log(serialize($e), Zend_Log::ERR);
            return self::Exception($e->getMessage(), '12');
        }
    }

    public function BalanceEnquiryRequest($obj) {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);


            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }

            Validator_Ratnakar::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());
            Validator_Ratnakar_Customer::memberIdCardNumValidation((string) trim($resp->MemberIDCardNo));
            Validator_Ratnakar_Beneficiary::benewalletcodeValidation((string) trim($resp->WalletCode));
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

                $object = new Corp_Ratnakar_Cardholders();
                $param ['product_id'] = (string) trim($resp->ProductCode);

                if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_MOB)) {
                    $param['mobile'] = (string) trim($resp->MemberIDCardNo);
                } else if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER)) {
                    $param['partner_ref_no'] = (string) trim($resp->MemberIDCardNo);
                }
                $custInfo = $object->getCardholderInfo($param);

                if (empty($custInfo)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                    $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                    return $responseObj;
                }
                $obj = new Corp_Ratnakar_CustomerPurse();
                if ((string) trim($resp->WalletCode) != '') {
                    $custPurse = $obj->getCustBalanceByWallet($custInfo['rat_customer_id'], (string) trim($resp->WalletCode));
                } else {
                    $custPurse = $obj->getCustBalance($custInfo['rat_customer_id']);
                }

         
//                if ($custPurse == FALSE) {
//                    $responseObj = new stdClass();
//                    $responseObj->SessionID = (string) $resp->SessionID;
//                    $responseObj->TxnIdentifierType = (string) $resp->TxnIdentifierType;
//                    $responseObj->MemberIDCardNo = (string) $resp->MemberIDCardNo;
//                    $responseObj->Currency = $curCode;
//                    $responseObj->AvailableBalance = '';
//                    $responseObj->ResponseCode = self::BALANCE_FAILED_RESPONSE_CODE;
//                    $responseObj->ResponseMessage = self::BALANCE_FAILED_RESPONSE_MSG;
//                    return $responseObj;
//                }
                $curCode = CURRENCY_INR;

                $responseObj = new stdClass();
//                if (strtolower($resp->SMSFlag) == strtolower(FLAG_Y)) {
//                    //Send SMS  
//                    $params = array(
//                        'product_id' => $custInfo['product_id'],
//                        'balance' => isset($custPurse['sum'])?$custPurse['sum']:0,
//                        'mobile' => $custInfo['mobile'],
//                    );
//                    $object->generateSMSDetails($params, $smsType = BALANCE_ENQUIRY_SMS);
//                }

                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->TxnIdentifierType = (string) trim($resp->TxnIdentifierType);
                $responseObj->MemberIDCardNo = (string) trim($resp->MemberIDCardNo);
                $responseObj->Currency = $curCode;
                $responseObj->AvailableBalance = isset($custPurse['sum'])?$custPurse['sum']:0;
                $responseObj->ResponseCode = self::BALANCE_SUCCSSES_RESPONSE_CODE;
                $responseObj->ResponseMessage = self::BALANCE_SUCCSSES_RESPONSE_MSG;

                return $responseObj;
            } catch (App_Exception $e) {
                $this->_soapServer->_getLogger()->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                return self::Exception($e->getMessage(), $e->getCode);
            }
        } catch (Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());
            App_Logger::log(serialize($e), Zend_Log::ERR);
            return self::Exception($e->getMessage(), $e->getCode());
        }
    }

    public function MiniStatementRequest($obj) {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            if (!isset($resp->SessionID) || !$this->isLogin((string) $resp->SessionID)) {
                return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
            }
            Validator_Ratnakar::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());
            Validator_Ratnakar_Customer::memberIdCardNumValidation((string) trim($resp->MemberIDCardNo));
            Validator_Ratnakar_Beneficiary::benewalletcodeValidation((string) trim($resp->WalletCode));
            // Check SMS Flag
            $smsflag = (string) trim($resp->SMSFlag);
            $smsflag = strtolower($smsflag);

            if ($smsflag != '') {
                if (strlen($smsflag) > 1 || ( $smsflag != strtolower(self::SMS_FLAG_TYPE_YES) && $smsflag != strtolower(self::SMS_FLAG_TYPE_NO) )) {
                    throw new Exception('SMS Flag is not valid');
                }
            } else {
                throw new Exception('SMS Flag is mandatory');
            }
            // Check TxnIdentifierType
            $txnidentifierflag = (string) trim($resp->TxnIdentifierType);
            $txnidentifierflag = strtolower($txnidentifierflag);

            if ($txnidentifierflag != '') {
                if (strlen($txnidentifierflag) > 3 || ( $txnidentifierflag != strtolower(self::TXN_IDENTIFIER_TYPE_MOB) && $txnidentifierflag != strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER) )) {
                    throw new Exception('Txn Identifier Flag is not valid');
                }
            } else {
                throw new Exception('Txn Identifier Flag is mandatory');
            }



            try {


                $object = new Corp_Ratnakar_Cardholders();
                $param ['product_id'] = (string) trim($resp->ProductCode);

                if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_MOB)) {
                    $param['mobile'] = (string) trim($resp->MemberIDCardNo);
                } else if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER)) {
                    $param['partner_ref_no'] = (string) trim($resp->MemberIDCardNo);
                }
                $custInfo = $object->getCardholderInfo($param);

                if (empty($custInfo)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                    $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                    return $responseObj;
                }
                $objectRelation = new ObjectRelations();
                $remitterId = $objectRelation->getToObjectInfo($custInfo['id'], RAT_MAPPER);

                $remittanceModel = new Remit_Ratnakar_Remittancerequest();
                $response = $remittanceModel->transactionHistory($remitterId['to_object_id']);

                if (empty($response)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnIdentifierType = (string) trim($resp->TxnIdentifierType);
                    $responseObj->MemberIDCardNo = (string) trim($resp->MemberIDCardNo);
                    $responseObj->ResponseCode = self::MINISTT_FAILED_RESPONSE_MSG;
                    $responseObj->ResponseMessage = self::MINISTT_FAILED_RESPONSE_MSG;
                    return $responseObj;
                }

                $rxml = $this->generateMiniStatementXMLRAT($response, $resp);
                $this->logAuthenticationMessage($sxml, $rxml, 'MiniStatementRequest');


                //return the response and terminate the script, So Server will not able to generate its response 
                //Setup Header as not returning as part of application
                header("Content-Type: text/xml; charset=utf-8");
                print $rxml;
                exit; //DO NOT DELETE THIS LINE                
            } catch (App_Exception $e) {
                $this->_soapServer->_getLogger()->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                return self::Exception($e->getMessage(), '11');
            }
        } catch (Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());
            App_Logger::log(serialize($e), Zend_Log::ERR);
            return self::Exception($e->getMessage(), '12');
        }
    }

    public function UpdateCustomerRequest() {//Do not add comments for method summary
        try {
          
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);

            /*
             * Login Validation
             */
           $sessionID = (string)trim($resp->SessionID);
           if (!isset($resp->SessionID) || !in_array($sessionID,$this->sessionValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
           }
       
            /*
             * Transaction Reference Numver Validation
             */
            Validator_Ratnakar::txnrefnoValidation((string) trim($resp->TransactionRefNo));

            /*
             * Product code validation
             */
            $productCode = (string)trim($resp->ProductCode);
           if (!isset($resp->ProductCode) || !in_array($productCode,$this->productValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);
           }
            /*
             * Partner Reference Numver Validation
             */
           // Validator_Ratnakar_Customer::partnerRefnoValidation((string) $resp->PartnerRefNo);
            if((string) trim($resp->Mobile) !=''){
                 Validator_Ratnakar::mobileValidation((string) trim($resp->Mobile));
            }
            
             if((string) trim($resp->Email) !=''){
                 Validator_Ratnakar::emailValidation((string) trim($resp->Email));
            }
            try {
                //  $strCardNumber = (string) $resp->CardNumber;
                $strTansRefNo = (string) trim($resp->TransactionRefNo);
                $strPartnerRefNo = (string) trim($resp->PartnerRefNo);
                $cardNumber = !empty($strCardNumber) ? $strCardNumber : 0;
                $customerType = !empty($resp->Filler2) ? (string) trim($resp->Filler2) : TYPE_NONKYC;

                // Check OTP
                $otp = (string) trim($resp->OTP);
                if ($otp != '') {
                    if (strlen($otp) != 6 || !(ctype_digit($otp))) {
                        throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
                    }
                } else {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
                }
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
                //$crnMaster
            
                // Check TxnIdentifierType
            $customerIdentifierType = (string) trim($resp->CustomerIdentifierType);
            $customerIdentifierType = strtolower($customerIdentifierType);

            if ($customerIdentifierType != '') {
                if (strlen($customerIdentifierType) > 3 || ( $customerIdentifierType != strtolower(self::TXN_IDENTIFIER_TYPE_MOBILE) && $customerIdentifierType != strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER) )) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_IDENTIFIRE_TYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_IDENTIFIRE_TYPE_CODE);
                }else if((string) trim($resp->CustomerNo) ==''){
                  throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE);   
                }else{
                   
                     if ($customerIdentifierType == strtolower(self::TXN_IDENTIFIER_TYPE_MOBILE)){
                       Validator_Ratnakar::mobileValidation((string) trim($resp->CustomerNo));    
                     } else if ($customerIdentifierType == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER)) {
                        Validator_Ratnakar_Customer::partnerRefnoValidation((string) trim($resp->CustomerNo));
                    }
                  }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_IDENTIFIRE_TYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_IDENTIFIRE_TYPE_CODE);
            }
            
            
               $customerInfo = FALSE;
               $custvarifyvalue = (string) trim($resp->CustomerNo);
              if ($customerIdentifierType == strtolower(self::TXN_IDENTIFIER_TYPE_MOBILE)) {
                   if(in_array($custvarifyvalue,$this->verifiedMobileNum)){
                         $customerInfo = TRUE;  
                        }
                } else if ($customerIdentifierType == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER)) {
                    if(in_array($custvarifyvalue,$this->customerPARNum)){
                         $customerInfo = TRUE;  
                        }
                }
                
                $randValue = rand(0,3);
                $txnCode = $this->generatedQueryRefNum[$randValue];
                if ($customerInfo == FALSE) {
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = $txnCode;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                    $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                    return $responseObj;
                }
                    
                $responseOTP = FALSE; 
                $otp = $resp->OTP;
                if(in_array($otp,$this->generatedOTPNum)){
                  $responseOTP = TRUE;  
                }

                    if ($responseOTP == FALSE) {
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = self::OTP_INVALID_RESPONSE_CODE;
                        $responseObj->ResponseMessage = self::OTP_INVALID_RESPONSE_MSG;
                        return $responseObj;
                    }
                 
                
                $response = true;
               
                if ($response == TRUE) {
                    
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->CustomerIdentifierType = (string) trim($resp->CustomerIdentifierType);
                    $responseObj->CustomerNo = (string) trim($resp->CustomerNo);
                    $responseObj->QueryReqNo = $txnCode; //$baseTxn->getTxncode();
                    $responseObj->ResponseCode = self::CUSTOMER_UPDATION_SUCC_CODE;
                    $responseObj->ResponseMessage = self::CUSTOMER_UPDATION_SUCC_MSG;
                     // Update otp entry 
                  
                } else {
                    $errorMsg = '';
                    $errorMsg = empty($errorMsg) ? self::CUSTOMER_UPDATION_FAIL_MSG : $errorMsg;
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->CustomerIdentifierType = (string) trim($resp->CustomerIdentifierType);
                    $responseObj->CustomerNo = (string) trim($resp->CustomerNo);
                    $responseObj->ResponseCode = self::CUSTOMER_UPDATION_FAIL_CODE;
                    $responseObj->ResponseMessage = $errorMsg;
                }
                return $responseObj;
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $code = (empty($code)) ? self::CUSTOMER_UPDATION_FAIL_CODE : $code;
                $message = $e->getMessage();
                $message = (empty($message)) ? self::CUSTOMER_UPDATION_FAIL_MSG : $message;
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $code = $e->getCode();
            $code = (empty($code)) ? self::CUSTOMER_UPDATION_FAIL_CODE : $code;
            $message = $e->getMessage();
            $message = (empty($message)) ? self::CUSTOMER_UPDATION_FAIL_MSG : $message;
            $this->_soapServer->_getLogger()->__setException($message);
            return self::Exception($message, $code);
        }
    }


    public function QueryRegistrationRequest() {//Do not add comments for method summary

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);

            /*
             * Login Validation
             */
           $sessionID = (string)trim($resp->SessionID);
           if (!isset($resp->SessionID) || !in_array($sessionID,$this->sessionValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
           }

            /*
             * Transaction Reference Numver Validation
             */
            Validator_Ratnakar::txnrefnoValidation((string) trim($resp->TransactionRefNo));

            /*
             * Query Reference Numver Validation
             */
            Validator_Ratnakar::queryrefnoValidation((string) trim($resp->QueryReqNo));

            try {
                $strTansRefNo = (string) trim($resp->TransactionRefNo);
                $strQueryReqNo = (string) trim($resp->QueryReqNo);

                if(in_array($strTansRefNo,$this->customerTranRefNum)){
                  $respTransRef = TRUE;  
                }
                if ($respTransRef == FALSE) {
                    return self::Exception('Transaction Referance Number is not valid', App_ApiServer_Exchange::$INVALID_RESPONSE);
                }
                
                if(in_array($strQueryReqNo,$this->generatedQueryRefNum)){
                  $respQueryRef = TRUE;  
                }
                if ($respQueryRef == FALSE) {
                    return self::Exception('Query Referance Number is not valid', App_ApiServer_Exchange::$INVALID_RESPONSE);
                }
               
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = (string) $strQueryReqNo; //$baseTxn->getTxncode();
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->PartnerRefNo = $this->custInfo['PartnerRefNo'];
                    $responseObj->ProductCode = $this->custInfo['ProductId'];
                    $responseObj->Mobile = $this->custInfo['Mobile'];
                    $responseObj->Email = $this->custInfo['Email'];
                    $responseObj->Name = $this->custInfo['Name'];
                    $responseObj->TransactionStatus = $this->custInfo['TransactionStatus'];
                    $responseObj->ResponseCode = self::QUERY_REGISTRATION_SUCC_CODE;
                    $responseObj->ResponseMessage = self::QUERY_REGISTRATION_SUCC_MSG;
                
                return $responseObj;
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $code = (empty($code)) ? ErrorCodes::ERROR_SYSTEM_ERROR : $code;
                $message = $e->getMessage();
                $message = (empty($message)) ? self::QUERY_REGISTRATION_FAIL_MSG : $message;
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
             $code = $e->getCode();
                $code = (empty($code)) ? self::QUERY_REGISTRATION_FAIL_CODE : $code;
                $message = $e->getMessage();
                $message = (empty($message)) ? self::QUERY_REGISTRATION_FAIL_MSG : $message;
                $this->_soapServer->_getLogger()->__setException($message);
            return self::Exception($message, $code);
        }
    }


    public function TransactionRequest() {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);


           $sessionID = (string)trim($resp->SessionID);
           if (!isset($resp->SessionID) || !in_array($sessionID,$this->sessionValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
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
            
            $productCode = (string)trim($resp->ProductCode);
           if (!isset($resp->ProductCode) || !in_array($productCode,$this->productValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);
           }
           
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

                $customerInfo = FALSE;
                $custvarifyvalue = (string) trim($resp->MemberIDCardNo);
                if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_MOB)) {
                    if(in_array($custvarifyvalue,$this->verifiedMobileNum)){
                         $customerInfo = TRUE;  
                        }
                } else if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER)) {
                    if(in_array($custvarifyvalue,$this->customerPARNum)){
                         $customerInfo = TRUE;  
                        }
                }
                
                if ($customerInfo == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                    $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                    return $responseObj;
                }
              
                $randValue = rand(0,3);
                $txnCode = $this->generatedQueryRefNum[$randValue];
                $flg = true;
                
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
    public function QueryBeneficiaryRequest() {//Do not add comments for method summary

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);

            /*
             * Login Validation
             */
            $sessionID = (string)trim($resp->SessionID);
           if (!isset($resp->SessionID) || !in_array($sessionID,$this->sessionValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
           }

            /*
             * Product Code Validation
             */
             $productCode = (string)trim($resp->ProductCode);
           if (!isset($resp->ProductCode) || !in_array($productCode,$this->productValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);
           }

            /*
             * Query Reference Numver Validation
             */
            Validator_Ratnakar::queryrefnoValidation((string) trim($resp->QueryReqNo));

            /*
             * Bene Code Validation
             */
            
            if((string) trim($resp->BeneficiaryCode) !=''){
            Validator_Ratnakar_Beneficiary::beneCodeValidation((string) trim($resp->BeneficiaryCode));
            }

            // Check Remitter Flag
            $remitterflag = (string) trim($resp->RemitterFlag);
            $remitterflag = strtolower($remitterflag);

            if ($remitterflag != '') {
                if (strlen($remitterflag) > 1 || ( $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE) && $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER) )) {
                    throw new Exception('Remitter Flag is not valid');
                }else{
                    if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                        Validator_Ratnakar::mobileValidation((string) trim($resp->RemitterCode));
                    } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        Validator_Ratnakar_Customer::partnerRefnoValidation((string) trim($resp->RemitterCode));
                  }
                }
            } else {
                throw new Exception('Remitter Flag is mandatory');
            }

            try {
              //  $strTansRefNo = (string) trim($resp->TransactionRefNo);
                $strQueryReqNo = (string) trim($resp->QueryReqNo);
                $customerInfo = FALSE;
                $custvarifyvalue = (string) trim($resp->RemitterCode);
              if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                     if(in_array($custvarifyvalue,$this->verifiedMobileNum)){
                         $customerInfo = TRUE;  
                        }
                } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                    if(in_array($custvarifyvalue,$this->customerPARNum)){
                         $customerInfo = TRUE;  
                        }
                }
              
                if ($customerInfo == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                    $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                    return $responseObj;
                }else{
                   if(in_array($strQueryReqNo,$this->generatedQueryRefNum)){
                   
                            $responseObj = new stdClass();
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->AckNo = $strQueryReqNo; //$baseTxn->getTxncode();
                            $responseObj->RemitterCode = (string) trim($resp->RemitterCode);
                            $responseObj->BeneficiaryCode = $this->beneInfo['BeneCode'];
                            $responseObj->Title = $this->beneInfo['title'];
                            $responseObj->FirstName = $this->beneInfo['Name'];
                            $responseObj->MiddleName = $this->beneInfo['Mname'];
                            $responseObj->LastName = $this->beneInfo['Lname'];
                            $responseObj->Gender = $this->beneInfo['Gender'];
                            $responseObj->DateOfBirth = $this->beneInfo['DateOfBirth'];
                            $responseObj->Mobile = $this->beneInfo['Mobile'];
                            $responseObj->Mobile2 = $this->beneInfo['Mobile2'];
                            $responseObj->Email = $this->beneInfo['Email'];
                            $responseObj->MotherMaidenName = $this->beneInfo['MotherMaidenName'];
                            $responseObj->Landline = $this->beneInfo['Landline'];
                            $responseObj->AddressLine1 = $this->beneInfo['AddressLine1'];
                            $responseObj->AddressLine2 = $this->beneInfo['AddressLine2'];
                            $responseObj->City = $this->beneInfo['City'];
                            $responseObj->State = $this->beneInfo['State'];
                            $responseObj->Country = $this->beneInfo['Country'];
                            $responseObj->Pincode = $this->beneInfo['Pincode'];
                            $responseObj->BankName = $this->beneInfo['BankName'];
                            $responseObj->BankBranch = $this->beneInfo['BankBranch'];
                            $responseObj->BankCity = $this->beneInfo['BankCity'];
                            $responseObj->BankIfscode = $this->beneInfo['BankIfscode'];
                            $responseObj->BankAccountNumber = $this->beneInfo['BankAccountNumber'];
                            $responseObj->ResponseCode = self::QUERY_BENEFICIARY_SUCC_CODE;
                            $responseObj->ResponseMessage = self::QUERY_BENEFICIARY_SUCC_MSG;
                       
                   }else{
                     throw new Exception('Beneficiary does not exit.');  
                   } 
                     return $responseObj;
                }
                /*
                 *  Featching Remitter Id from object relation
                 */
               
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $code = (empty($code)) ? ErrorCodes::ERROR_SYSTEM_ERROR : $code;
                $message = $e->getMessage();
                $message = (empty($message)) ? self::QUERY_BENEFICIARY_FAIL_MSG : $message;
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $code = (empty($code)) ? self::QUERY_BENEFICIARY_FAIL_CODE : $code;
                $message = $e->getMessage();
                $message = (empty($message)) ? self::QUERY_BENEFICIARY_FAIL_MSG : $message;
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
        }
    }

   

//    public function QueryRemittanceRequest($obj) {
//
//        try {
//            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
//            $sxml = $this->_soapServer->getLastRequest();
//            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
//
//
//            if( !isset($resp->SessionID) || !$this->isLogin((string) $resp->SessionID)) {
//                 return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
//             }
//
//            Validator_Ratnakar::queryrefnoValidation((string) trim((string)$resp->QueryReqNo));
//            Validator_Ratnakar::txnrefnoValidation((string) trim($resp->TransactionRefNo));
//
//            try {
//
//
//                $object = new Remit_Ratnakar_Remittancerequest();
//                $remitttanceData = $object->getRemittanceTransaction((string) trim($resp->TransactionRefNo));
//                // Query Remittance request code
//
//                if ($remitttanceData == FALSE) {
//                    $responseObj = new stdClass();
//                    $responseObj->SessionID = (string) $resp->SessionID;
//                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
//                    $responseObj->AckNo = '';
//                    $responseObj->ResponseCode = self::QUERY_REMITTANCE_FAILURE_CODE;
//                    $responseObj->ResponseMessage = self::QUERY_REMITTANCE_FAILURE_MSG;
//                    return $responseObj;
//                }
//                $curCode = CURRENCY_INR;
//
//                $responseObj = new stdClass();
//
//                $responseObj->SessionID = (string) $resp->SessionID;
//                $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
//                $responseObj->ProductCode = (string) trim($resp->ProductCode);
//                $responseObj->WalletCode = $remitttanceData['wallet_code'];
//                $responseObj->RemitterCode = $remitttanceData['partner_ref_no'];
//                $responseObj->BeneficiaryCode = $remitttanceData['bene_code'];
//                $responseObj->Narration = $remitttanceData['sender_msg'];
//                $responseObj->Amount = $remitttanceData['amount'];
//                $responseObj->TransactionStatus = $remitttanceData['status'];
//                $responseObj->AckNo = $remitttanceData['txn_code'];
//                $responseObj->ResponseCode = self::QUERY_REMITTANCE_SUCCESS_CODE;
//                $responseObj->ResponseMessage = self::QUERY_REMITTANCE_SUCCESS_MSG;
//
//                return $responseObj;
//            } catch (App_Exception $e) {
//                $this->_soapServer->_getLogger()->__setException($e->getMessage());
//                App_Logger::log(serialize($e), Zend_Log::ERR);
//                return self::Exception($e->getMessage(), '11');
//            }
//        } catch (Exception $e) {
//            $this->_soapServer->_getLogger()->__setException($e->getMessage());
//            App_Logger::log(serialize($e), Zend_Log::ERR);
//            return self::Exception($e->getMessage(), '12');
//        }
//    }

    public function WalletTransferRequest() {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);


            $sessionID = (string)trim($resp->SessionID);
           if (!isset($resp->SessionID) || !in_array($sessionID,$this->sessionValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
           }
             
            //	Amount valid check  
            if ((string) trim($resp->Amount) != '') {
                if (!is_numeric((string) trim($resp->Amount))) {
                    return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_CODE);
                }
            } else {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_CODE);
            }


            $productCode = (string)trim($resp->ProductCode);
           if (!isset($resp->ProductCode) || !in_array($productCode,$this->productValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);
           }
           Validator_Ratnakar::txnrefnoValidation((string) $resp->TransactionRefNo);
            Validator_Ratnakar_Beneficiary::benemobileValidation((string) $resp->BeneficiaryMobile);
            Validator_Ratnakar_Beneficiary::beneemailValidation((string) $resp->BeneficiaryEmail);
            Validator_Ratnakar_Customer::narrationValidation((string) $resp->Narration);
            Validator_Ratnakar_Beneficiary::remitterwalletcodeValidation((string) $resp->RemitterWalletCode);
            Validator_Ratnakar_Beneficiary::remitterwalletcodeValidation((string) $resp->BeneficiaryWalletCode);

            // Check OTP
            $otp = (string) trim($resp->OTP);
            if ($otp != '') {
                if (strlen($otp) != 6 || !(ctype_digit($otp))) {
                  return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
                }
            } else {
                  return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
            }

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
            // Check Remitter Flag
            $remitterflag = (string) trim($resp->RemitterFlag);
            $remitterflag = strtolower($remitterflag);

            if ($remitterflag != '') {
                if (strlen($remitterflag) > 1 || ( $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_MOB) && $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER) )) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERCODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERCODE_CODE);
                }else{
                    if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                        Validator_Ratnakar::mobileValidation((string) trim($resp->RemitterCode));
                    } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        Validator_Ratnakar_Customer::partnerRefnoValidation((string) trim($resp->RemitterCode));
                  }
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERCODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERCODE_CODE);
            }
            /*
             * Wallet Transfer validation
             */
           
            try {
                
                
                $customerInfo = FALSE;
                $custvarifyvalue = (string) trim($resp->RemitterCode);
                if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOB)) {
                     if(in_array($custvarifyvalue,$this->verifiedMobileNum)){
                         $customerInfo = TRUE;  
                        }
                } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                    if(in_array($custvarifyvalue,$this->customerPARNum)){
                         $customerInfo = TRUE;  
                        }
                }
               
                if ($customerInfo == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                    $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                    return $responseObj;
                }
                $otp = (string) trim($resp->OTP);
                $responseOTP = FALSE; 
                $randValue = rand(0,3);
                 $txnCode = $this->generatedQueryRefNum[$randValue];
               if(in_array($otp,$this->generatedOTPNum)){
                 $responseOTP = TRUE;  
               }
                if ($responseOTP == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = $txnCode;
                    $responseObj->ResponseCode = self::OTP_INVALID_RESPONSE_CODE;
                    $responseObj->ResponseMessage = self::OTP_INVALID_RESPONSE_MSG;
                    return $responseObj;
                }
               
               $beneficiaryWalletCode = (string) strtolower(trim($resp->BeneficiaryWalletCode));
               if(in_array($beneficiaryWalletCode,$this->blockwallet)){
                 throw new Exception('This fund can not be tranfer into Beneficiary Wallet');
                        }
                
               // Check product
                 $othercustvarifymobile = (string) trim($resp->BeneficiaryMobile);
                 if(in_array($othercustvarifymobile,$this->verifiedMobileNum)){
                         $othercustomerInfo = TRUE;  
                        }
               
                if ($othercustomerInfo == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->AckNo = $txnCode;
                    $responseObj->ResponseCode = self::WALLET_TRANSFER_FAILURE_CODE;
                    $responseObj->ResponseMessage = 'Beneficiary customer is not exist';
                    return $responseObj;
                }else{
                $responseObj = new stdClass();
                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                $responseObj->AckNo = $txnCode;
                $responseObj->ResponseCode = self::WALLET_TRANSFER_SUCCESS_CODE;
                $responseObj->ResponseMessage = self::WALLET_TRANSFER_SUCCESS_MSG;
                // Update otp entry 
               }
                return $responseObj;
            } catch (App_Exception $e) {
                $this->_soapServer->_getLogger()->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                return self::Exception($e->getMessage(), '11');
            }
        } catch (Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());
            App_Logger::log(serialize($e), Zend_Log::ERR);
            return self::Exception($e->getMessage(), '12');
        }
    }

    public function DeactivateBeneficiaryRequest() {//Do not add comments for method summary
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);

            /*
             * Login Validation
             */
           $sessionID = (string)trim($resp->SessionID);
           if (!isset($resp->SessionID) || !in_array($sessionID,$this->sessionValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
           }

            /*
             * Product Code Validation

             */
            $productCode = (string)trim($resp->ProductCode);
           if (!isset($resp->ProductCode) || !in_array($productCode,$this->productValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);
           }
          
          //  Validator_Ratnakar::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());


            /*
             * Query Reference Numver Validation
             */
          //  Validator_Ratnakar::queryrefnoValidation((string) trim($resp->QueryReqNo));

            /*
             * Bene Code Validation
             */
           if((string) trim($resp->BeneficiaryCode) !=''){
            Validator_Ratnakar_Beneficiary::beneCodeValidation((string) trim($resp->BeneficiaryCode));
           }

             /*
             * Transaction Reference Numver Validation
             */
            Validator_Ratnakar::txnrefnoValidation((string)trim($resp->TransactionRefNo));
            
           
            // Check Remitter Flag
            $remitterflag = (string) trim($resp->RemitterFlag);
            $remitterflag = strtolower($remitterflag);

            if ($remitterflag != '') {
                if (strlen($remitterflag) > 1 || ( $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_MOB) && $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER) )) {
                    throw new Exception('Remitter Flag is not valid');
                }else{
                    if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                        Validator_Ratnakar::mobileValidation((string) trim($resp->RemitterCode));
                    } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        Validator_Ratnakar_Customer::partnerRefnoValidation((string) trim($resp->RemitterCode));
                  }
                }
            } else {
                throw new Exception('Remitter Flag is mandatory');
            }

             /*
             * SMS Flag Validation
             */
            $smsflag = (string)trim($resp->SMSFlag);
            $smsflag = strtolower($smsflag);
          
            if ($smsflag != '') {
            if(strlen($smsflag) > 1 ||( $smsflag != strtolower(self::SMS_FLAG_TYPE_YES) && $smsflag != strtolower(self::SMS_FLAG_TYPE_NO) )){
                throw new Exception('SMS Flag is not valid');
             }
            } else {
                throw new Exception('SMS Flag is mandatory');
            }
            

            try {
                $strTansRefNo = (string) trim($resp->TransactionRefNo);
                $strQueryReqNo = (string) trim($resp->QueryReqNo);
                
                $customerInfo = FALSE;
                $custvarifyvalue = (string) trim($resp->RemitterCode);    
             if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOB)) {
                    if(in_array($custvarifyvalue,$this->verifiedMobileNum)){
                         $customerInfo = TRUE;  
                        }
                } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                    if(in_array($custvarifyvalue,$this->customerPARNum)){
                         $customerInfo = TRUE;  
                        }
                }
                
                if($customerInfo == FALSE){ 
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                    $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                    return $responseObj;
                }
                
                if(in_array($strTansRefNo,$this->customerTranRefNum)){
                  
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $queryrefno; //$baseTxn->getTxncode();
                        $responseObj->RemitterCode = (string) trim($resp->RemitterCode);
                        $responseObj->BeneficiaryCode = (string) trim($resp->BeneficiaryCode);
                        $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                        $responseObj->Title = $this->beneInfo['title'];
                        $responseObj->FirstName = $this->beneInfo['Name'];
                        $responseObj->MiddleName = $this->beneInfo['Mname'];
                        $responseObj->LastName = $this->beneInfo['Lname'];
                        $responseObj->Gender = $this->beneInfo['Gender'];
                        $responseObj->DateOfBirth = $this->beneInfo['DateOfBirth'];
                        $responseObj->Mobile = $this->beneInfo['Mobile'];
                        $responseObj->Mobile2 = $this->beneInfo['Mobile2'];
                        $responseObj->Email = $this->beneInfo['Email'];
                        $responseObj->MotherMaidenName = $this->beneInfo['MotherMaidenName'];
                        $responseObj->Landline = $this->beneInfo['Landline'];
                        $responseObj->AddressLine1 = $this->beneInfo['AddressLine1'];
                        $responseObj->AddressLine2 = $this->beneInfo['AddressLine2'];
                        $responseObj->City = $this->beneInfo['City'];
                        $responseObj->State = $this->beneInfo['State'];
                        $responseObj->Country = $this->beneInfo['Country'];
                        $responseObj->Pincode = $this->beneInfo['Pincode'];
                        $responseObj->BankName = $this->beneInfo['BankName'];
                        $responseObj->BankBranch = $this->beneInfo['BankBranch'];
                        $responseObj->BankCity = $this->beneInfo['BankCity'];
                        // $responseObj->BankState             =   $beneInfo['email'];
                        $responseObj->BankIfscode = $this->beneInfo['BankIfscode'];
                        $responseObj->BankAccountNumber = $$this->beneInfo['BankAccountNumber'];
                        $responseObj->ResponseCode = self::DEACTIVE_BENEFICIARY_SUCC_CODE;
                        $responseObj->ResponseMessage = self::DEACTIVE_BENEFICIARY_SUCC_MSG;

                }else {
                            $errorMsg = '';
                            $errorMsg = empty($errorMsg) ? self::DEACTIVE_BENEFICIARY_FAIL_MSG : $errorMsg;
                            $responseObj = new stdClass();
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->ResponseCode = self::DEACTIVE_BENEFICIARY_FAIL_CODE;
                            $responseObj->ResponseMessage = $errorMsg;
                        }
                    return $responseObj;
               

               
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $code = (empty($code)) ? ErrorCodes::ERROR_SYSTEM_ERROR : $code;
                $message = $e->getMessage();
                $message = (empty($message)) ? self::DEACTIVE_BENEFICIARY_FAIL_MSG : $message;
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $code = (empty($code)) ? self::DEACTIVE_BENEFICIARY_FAIL_CODE : $code;
                $message = $e->getMessage();
                $message = (empty($message)) ? self::DEACTIVE_BENEFICIARY_FAIL_MSG : $message;
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
        }
    }

//    public function QueryTransferRequest($obj) {
//
//        try {
//            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
//            $sxml = $this->_soapServer->getLastRequest();
//            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
//
//
//            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
//                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
//            }
//
//
//            Validator_Ratnakar::productcodeValidation((string) trim($resp->ProductCode), $this->getProductConstant());
//            Validator_Ratnakar::txnrefnoValidation((string) trim($resp->TransactionRefNo));
//            Validator_Ratnakar::queryrefnoValidation((string) trim($resp->QueryReqNo));
//
//
//
//            // Check Remitter Flag
//            $remitterflag = (string) trim($resp->RemitterFlag);
//            $remitterflag = strtolower($remitterflag);
//
//            if ($remitterflag != '') {
//                if (strlen($remitterflag) > 1 || ( $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_MOB) && $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER) )) {
//                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERCODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERCODE_CODE);
//                }else{
//                    if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
//                        Validator_Ratnakar::mobileValidation((string) trim($resp->RemitterCode));
//                    } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
//                        Validator_Ratnakar_Customer::partnerRefnoValidation((string) trim($resp->RemitterCode));
//                  }
//                }
//            } else {
//                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERCODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERCODE_CODE);
//            }
//
//            try {
//
//               
//                $object = new Corp_Ratnakar_Cardholders();
//                $param ['product_id'] = (string) trim($resp->ProductCode);
//
//                if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOB)) {
//                    $param['mobile'] = (string) trim($resp->RemitterCode);
//                } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
//                    $param['partner_ref_no'] = (string) trim($resp->RemitterCode);
//                }
//                $custInfo = $object->getCustomerInfoBy($param);
//
//                if ($custInfo == FALSE) {
//                    $responseObj = new stdClass();
//                    $responseObj->SessionID = (string) $resp->SessionID;
//                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
//                    $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
//                    return $responseObj;
//                }
//
//
//                // Transfer Fund query
//               
//                $walletTransfer = new Remit_Ratnakar_WalletTransfer();
//                $tansferDetails = $walletTransfer->getWalletTransferDetails(array('txnrefnum' => (string) $resp->TransactionRefNo,'product_id' => (string) $resp->ProductCode));
//                if ($empty($tansferDetails)) {
//                    $responseObj = new stdClass();
//                    $responseObj->SessionID = (string) $resp->SessionID;
//                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
//                    $responseObj->AckNo = '';
//                    $responseObj->ResponseCode = self::QUERY_TRANSFER_FAILURE_CODE;
//                    $responseObj->ResponseMessage = self::QUERY_TRANSFER_FAILURE_MSG;
//                    return $responseObj;
//                }
//                $curCode = CURRENCY_INR;
//
//                $responseObj = new stdClass();
//
//                $responseObj->SessionID = (string) $resp->SessionID;
//                $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
//                $responseObj->AckNo = '';
//                $responseObj->RemitterCode = (string) trim($resp->RemitterCode);
//                $responseObj->RemitterWalletCode = $tansferDetails['remitter_wallet_code'];
//                $responseObj->BeneficiaryEmail = $tansferDetails['bene_email'];
//                $responseObj->BeneficiaryMobile = $tansferDetails['bene_mobile'];
//                $responseObj->BeneficiaryWalletCode = $tansferDetails['bene_wallet_code'];
//                $responseObj->Amount = $tansferDetails['amount'];
//                $responseObj->TransactionStatus = $tansferDetails['status'];
//                $responseObj->ResponseCode = self::QUERY_TRANSFER_SUCCESS_CODE;
//                $responseObj->ResponseMessage = self::QUERY_TRANSFER_SUCCESS_MSG;
//
//                return $responseObj;
//                               
//
//            } catch (App_Exception $e) {
//                $this->_soapServer->_getLogger()->__setException($e->getMessage());
//                App_Logger::log(serialize($e), Zend_Log::ERR);
//                return self::Exception($e->getMessage(), '11');
//            }
//        } catch (Exception $e) {
//            $this->_soapServer->_getLogger()->__setException($e->getMessage());
//            App_Logger::log(serialize($e), Zend_Log::ERR);
//
//            return self::Exception($e->getMessage(), '12');
//        }
//    }

    public function QueryTransactionRequest($obj) {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);


            $sessionID = (string)trim($resp->SessionID);
           if (!isset($resp->SessionID) || !in_array($sessionID,$this->sessionValues)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
           }

            Validator_Ratnakar::txnNumValidation((string) trim($resp->TxnNo));
            Validator_Ratnakar::queryrefnoValidation((string) trim($resp->QueryReqNo));
            
            $txnNo = (string) trim($resp->TxnNo);
                if(in_array($txnNo,$this->customerTranRefNum)){
                  $respTransRef = TRUE;  
                }else{
                    return self::Exception('Txn Number is not valid', App_ApiServer_Exchange::$INVALID_RESPONSE);
                }
            

            try {
               

                $responseObj = new stdClass();

                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->AckNo = '';
                $responseObj->TxnNo = (string) trim($resp->TxnNo);
                $responseObj->ProductCode = (string) trim($resp->ProductCode);
                $responseObj->WalletCode = $this->transInfo['WalletCode'];
                $responseObj->Amount = $this->transInfo['Amount'];
                $responseObj->Narration = $this->transInfo['Narration'];
                $responseObj->TxnIndicator = $this->transInfo['TxnIndicator'];
                $responseObj->TxnIdentifierType = $this->transInfo['TxnIdentifierType'];
                $responseObj->MemberIDCardNo = $this->transInfo['MemberIDCardNo'];
                $responseObj->TransactionStatus = $this->transInfo['TransactionStatus'];
                $responseObj->ResponseCode = self::QUERY_TRANSACTION_REQUEST_SUCCESS_CODE;
                $responseObj->ResponseMessage = self::QUERY_TRANSACTION_REQUEST_SUCCESS_MSG;

                return $responseObj;
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $code = (empty($code)) ? self::QUERY_TRANSACTION_REQUEST_FAILURE_CODE : $code;
                $message = $e->getMessage();
                $message = (empty($message)) ? self::QUERY_TRANSACTION_REQUEST_FAILURE_MSG : $message;
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $code = (empty($code)) ? self::QUERY_TRANSACTION_REQUEST_FAILURE_CODE : $code;
                $message = $e->getMessage();
                $message = (empty($message)) ? self::QUERY_TRANSACTION_REQUEST_FAILURE_MSG : $message;
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
        }
    }

//    public function RemittanceTransactionRequest($obj) {
//
//        try {
//            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
//            $sxml = $this->_soapServer->getLastRequest();
//            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
//
//
//            if( !isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
//                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
//             }
//             
//            //	Amount valid check  
//            if ((string) $resp->Amount != '') {
//                if (!is_numeric((string) trim($resp->Amount))) {
//                   throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_CODE);
//                }
//            } else {
//                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_CODE);
//            }
//
//            Validator_Ratnakar::productcodeValidation((string) trim($resp->ProductCode), $this->getProductConstant());
//            Validator_Ratnakar::txnrefnoValidation((string) trim($resp->TransactionRefNo));
//            Validator_Ratnakar_Customer::transactionNarrationValidation((string) trim($resp->Narration));
//            Validator_Ratnakar_Beneficiary::remitterwalletcodeValidation((string) trim($resp->WalletCode));
//            Validator_Ratnakar_Beneficiary::remittancetypeValidation((string) trim($resp->RemittanceType));
//            Validator_Ratnakar_Beneficiary::beneCodeValidation((string) trim($resp->BeneficiaryCode));
//
//            // Check OTP
//            $otp = (string) trim($resp->OTP);
//            if ($otp != '') {
//                if (strlen($otp) != 6 || !(ctype_digit($otp))) {
//                   throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
//                }
//            }
//
//            // Check SMS Flag
//            $smsflag = (string) trim($resp->SMSFlag);
//            $smsflag = strtolower($smsflag);
//
//            if ($smsflag != '') {
//                if (strlen($smsflag) > 1 || ( $smsflag != strtolower(self::SMS_FLAG_TYPE_YES) && $smsflag != strtolower(self::SMS_FLAG_TYPE_NO) )) {
//                   throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_CODE);
//                }
//            } else {
//                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_CODE);
//            }
//            // Check Remitter Flag
//            $remitterflag = (string) trim($resp->RemitterFlag);
//            $remitterflag = strtolower($remitterflag);
//
//            if ($remitterflag != '') {
//                if (strlen($remitterflag) > 1 || ( $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_MOB) && $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER) )) {
//                     throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE);
//                }else{
//                    if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
//                        Validator_Ratnakar::mobileValidation((string) trim($resp->RemitterCode));
//                    } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
//                        Validator_Ratnakar_Customer::partnerRefnoValidation((string) trim($resp->RemitterCode));
//                  }
//                }
//            } else {
//               throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE);
//            }
//
//            try {
//                $refObject = new Reference();
//                $object = new Corp_Ratnakar_Cardholders();
//                $beneficiaryModel = new Remit_Ratnakar_Beneficiary();
//                $param ['product_id'] = (string) trim($resp->ProductCode);
//
//                if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOB)) {
//                    $param['mobile'] = (string) trim($resp->RemitterCode);
//                } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
//                    $param['partner_ref_no'] = (string) trim($resp->RemitterCode);
//                }
//                $custInfo = $object->getCustomerInfoBy($param);
//
//                if ($custInfo == FALSE) {
//                    $responseObj = new stdClass();
//                    $responseObj->SessionID = (string) $resp->SessionID;
//                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
//                    $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
//                    return $responseObj;
//                }
//                $responseOTP = $refObject->verifyCustomerOTPAPI(array(
//                    'request_type' => 'I',
//                    'otp' => (string) trim($resp->OTP),
//                    'mobile' => (string) $custInfo['mobile'],
//                ));
//                if ($responseOTP == FALSE) {
//                    $responseObj = new stdClass();
//                    $responseObj->SessionID = (string) $resp->SessionID;
//                    $responseObj->AckNo = '';
//                    $responseObj->ResponseCode = self::OTP_INVALID_RESPONSE_CODE;
//                    $responseObj->ResponseMessage = self::OTP_INVALID_RESPONSE_MSG;
//                    return $responseObj;
//                }
//                 
//                 $objectRelation = new ObjectRelations();
//                 $remitterId = $objectRelation->getToObjectInfo($custInfo['id'], RAT_MAPPER);
//                 $beneId = $beneficiaryModel->getBeneficiaryDetailsByCode((string) trim($resp->BeneficiaryCode), $remitterId['to_object_id']);           
//
//                if(!empty($beneId)) {
//                    // Remittance
//                    $remittancerequest = new Remit_Ratnakar_Remittancerequest();
//
//                    $remittanceData['amount'] = (string) trim($resp->Amount);
//                    $remittanceData['product_id'] = (string) trim($resp->ProductCode);
//                    $remittanceData['fund_holder'] = REMIT_FUND_HOLDER_REMITTER;
//                    $remittanceData['agent_id'] = $this->getAgentConstant();
//                    $remittanceData['remitter_id'] = $remitterId['to_object_id'];
//                    $remittanceData['beneficiary_id'] = $beneId['id'];
//                    $remittanceData['rat_customer_id'] = $custInfo['rat_customer_id'];
//                    $remittanceData['customer_master_id'] = $custInfo['customer_master_id'];
//                    $remittanceData['txnrefnum'] = (string) trim($resp->TransactionRefNo);
//                    $remittanceData['ops_id'] = 0;
//                    $remittanceData['product_id'] = (string) trim($resp->ProductCode);
//                    $remittanceData['date_created'] = new Zend_Db_Expr('NOW()');
//                    $remittanceData['fee'] = 0;
//                    $remittanceData['service_tax'] = 0;
//                    $remittanceData['status'] = STATUS_INCOMPLETE;
//                    $remittanceData['sender_msg'] = (string) trim($resp->Narration);
//                    $remittanceData['wallet_code'] = (string) trim($resp->WalletCode);
//
//                    $flg = $remittancerequest->remittanceTransaction($remittanceData);
//
//                    if ($flg == FALSE) {
//                        $responseObj = new stdClass();
//                        $responseObj->SessionID = (string) $resp->SessionID;
//                        $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
//                        $responseObj->AckNo = '';
//                        $responseObj->ResponseCode = self::REMITTANCE_TRANSACTION_FAILURE_CODE;
//                        $responseObj->ResponseMessage = self::REMITTANCE_TRANSACTION_FAILURE_MSG;
//                        return $responseObj;
//                    }
//                    $curCode = CURRENCY_INR;
//
//                    $responseObj = new stdClass();
//                    if (strtolower(trim($resp->SMSFlag)) == strtolower(FLAG_Y)) {
//                        //Send SMS  
//                        $params = array(
//                        'product_id' => $custInfo['product_id'],
//                        'mobile' => $custInfo['mobile'],
//                        'amount' => Util::convertToRupee((string) $resp->Amount),
//                        'bene_name' => $custInfoTo['first_name'].' '.$custInfoTo['last_name'],
//                        'wallet_code' => (string) $resp->RemitterWalletCode,
//                        'ref_num' => (string) $resp->TransactionRefNo,
//                        );
//                        $object->generateSMSDetails($params, $smsType = REMITTANCE);
//                    }
//                    $responseObj->SessionID = (string) $resp->SessionID;
//                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
//                    $responseObj->AckNo = '';
//                    $responseObj->ResponseCode = self::REMITTANCE_TRANSACTION_SUCCESS_CODE;
//                    $responseObj->ResponseMessage = self::REMITTANCE_TRANSACTION_SUCCESS_MSG;
//                    // Update otp entry 
//                    
//                    $upadteOTP = $refObject->updateCustomerOTPAPI(array(
//                    'request_type' => 'I',
//                    'id' => $responseOTP['id'],
//                     ));
//                    return $responseObj;
//                } else {
//                    $responseObj = new stdClass();
//                    $responseObj->SessionID = (string) $resp->SessionID;
//                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
//                    $responseObj->AckNo = '';
//                    $responseObj->ResponseCode = self::REMITTANCE_TRANSACTION_BENEFICIARY_FAILURE_CODE;
//                    $responseObj->ResponseMessage = self::REMITTANCE_TRANSACTION_BENEFICIARY_FAILURE_MSG;
//                    return $responseObj;
//                }
//            } catch (App_Exception $e) {
//                $this->_soapServer->_getLogger()->__setException($e->getMessage());
//                App_Logger::log(serialize($e), Zend_Log::ERR);
//                return self::Exception($e->getMessage(), '11');
//            }
//        } catch (Exception $e) {
//            $this->_soapServer->_getLogger()->__setException($e->getMessage());
//            App_Logger::log(serialize($e), Zend_Log::ERR);
//            return self::Exception($e->getMessage(), '12');
//        }
//    }
    
     public function QueryBeneficiaryListRequest() {//Do not add comments for method summary

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);

            /*
             * Login Validation
             */
            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
             //   return self::Exception($this->getMessage('login_failed'), App_ApiServer_Exchange::$INVALID_LOGIN);
            }

            /*
             * Product Code Validation
             */
            Validator_Ratnakar::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());


            /*
             * Query Reference Numver Validation
             */
            Validator_Ratnakar::queryrefnoValidation((string) trim($resp->QueryReqNo));

           
            // Check Remitter Flag
            $remitterflag = (string) trim($resp->RemitterFlag);
            $remitterflag = strtolower($remitterflag);

            if ($remitterflag != '') {
                if (strlen($remitterflag) > 1 || ( $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE) && $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER) )) {
                    throw new Exception('Remitter Flag is not valid');
                }else{
                    if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                        Validator_Ratnakar::mobileValidation((string) trim($resp->RemitterCode));
                    } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        Validator_Ratnakar_Customer::partnerRefnoValidation((string) trim($resp->RemitterCode));
                  }
                }
            } else {
                throw new Exception('Remitter Flag is mandatory');
            }

            try {
              //  $strTansRefNo = (string) trim($resp->TransactionRefNo);
                $strQueryReqNo = (string) trim($resp->QueryReqNo);

                $refObject = new Reference();

             //   $params['txnrefnum'] = (string) $strTansRefNo; //$resp->TransactionRefNo;
                $params['QueryReqNo'] = (string) $strQueryReqNo; //$resp->PartnerRefNo;
              //  $params['by_api_user_id'] = $this->_TP_ID;
                $params['product_id'] = (string) trim($resp->ProductCode);
                //$params['Filler3'] = (string) $resp->Filler3;
                //$params['Filler4'] = (string) $resp->Filler4;
                //$params['Filler5'] = (string) $resp->Filler5;
                if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                    $params['mobile'] = (string) trim($resp->RemitterCode);
                } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                    $params['partner_ref_no'] = (string) trim($resp->RemitterCode);
                }
                $params['status'] = STATUS_ACTIVE;
                $obj = new Corp_Ratnakar_Cardholders();
                $beneficiaryModel = new Remit_Ratnakar_Beneficiary();
                /*
                 * Getiing customer Detail  
                 */
                $cardholderData = $obj->getCardholderInfo($params);

                /*
                 *  Featching Remitter Id from object relation
                 */
                if (!empty($cardholderData)) {
                    $objectRelation = new ObjectRelations();
                    $remitterId = $objectRelation->getToObjectInfo($cardholderData['id'], RAT_MAPPER);
                    if (!empty($remitterId)) {
                        $bene_param['status'] = STATUS_ACTIVE;
                        $bene_param['remitter_id'] = $remitterId['to_object_id'];
                        $beneInfo = $beneficiaryModel->getBeneInfo($bene_param);
                        $txnCode = $obj->getTxncode();
                        $m = new \App\Messaging\Corp\Ratnakar\Operation();
                        if (!empty($beneInfo)) {
                         $beneDetails =   Util::toArray($beneInfo);
                          $rxml = $this->generateBeneficiaryListXMLRAT($beneDetails, $resp);
                          $this->logAuthenticationMessage($sxml, $rxml, 'QueryBeneficiaryListRequest');
                          //return the response and terminate the script, So Server will not able to generate its response 
                            //Setup Header as not returning as part of application
                            header("Content-Type: text/xml; charset=utf-8");
                            print $rxml;
                            exit; //DO NOT DELETE THIS LINE 
                        }
                        else {
                            $errorMsg = '';
                            $errorMsg = empty($errorMsg) ? self::QUERY_BENEFICIARY_LIST_FAIL_MSG : $errorMsg;
                            $responseObj = new stdClass();
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->ResponseCode = self::QUERY_BENEFICIARY_LIST_FAIL_CODE;
                            $responseObj->ResponseMessage = $errorMsg;
                        }
                        return $responseObj;
                        //
                    } else {
                        throw new Exception('Customer does not exit.');
                    }
                } else {
                    throw new Exception('Customer does not exit.');
                }
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $code = (empty($code)) ? ErrorCodes::ERROR_SYSTEM_ERROR : $code;
                $message = $e->getMessage();
                $message = (empty($message)) ? self::QUERY_BENEFICIARY_LIST_FAIL_MSG : $message;
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
             App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $code = (empty($code)) ? self::QUERY_BENEFICIARY_LIST_FAIL_CODE : $code;
                $message = $e->getMessage();
                $message = (empty($message)) ? self::QUERY_BENEFICIARY_LIST_FAIL_MSG : $message;
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
        }
    }
    
}
