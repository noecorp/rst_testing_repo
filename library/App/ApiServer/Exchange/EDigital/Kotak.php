<?php

/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_EDigital_Kotak extends App_ApiServer_Exchange_EDigital {

    public function __construct($server) {
        parent::__construct($server);
    }

    public function GenerateOTPRequest() {//Do not add comments for method summary
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
           
            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }
             

            /*
             * Product Code Validation
             */
            Validator_Kotak::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());

            
            /*
             * Request Type Validation
             */
            Validator_Kotak_Customer::requesttypeValidation((string) trim($resp->RequestType));

            /*
             * Narration Validation
             */
            if (strtolower(trim($resp->RequestType)) == strtolower(self::REQUEST_TYPE_TRANSFER )) {
                Validator_Kotak_Customer::narrationValidation((string) trim($resp->Narration));
            }
            /*
             * IsOriginal Validation
             */
            Validator_Kotak_Customer::isOriginalRequestValidation((string) trim($resp->IsOriginal));

            /*
             * OriginalAckNo Validation
             */
            if (strtolower(trim($resp->IsOriginal)) == 'n') {
                Validator_Kotak_Customer::originalAckNumValidation((string) trim($resp->OriginalAckNo));
            }

            /*
             * Unicode Flag validation for filter 1
             */
            Validator_Kotak_Customer::uniqueCodeflagMPValidation((string) strtolower(trim($resp->Filler1)));

            /*
             * Unicode Flag validation for filter 2
             */
          //  Validator_Kotak_Customer::uniqueCodeValidation((string) $resp->Filler2);

            if (strtolower(trim($resp->Filler1)) == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                if((string) trim($resp->Filler2) !='' ){
                    Validator_Kotak::mobileValidation((string) trim($resp->Filler2));
                }else{
                    Validator_Kotak::mobileValidation((string) trim($resp->Mobile)); 
                }
            } elseif (strtolower(trim($resp->Filler1)) == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                Validator_Kotak_Customer::partnerRefnoValidation((string) trim($resp->Filler2));
            }

            try {

                $object = new CustomerTrack();
                $refObject = new Reference();
                $remitterModel = new Remit_Kotak_Remitter();
                
                $baseTxn = new BaseTxn();
                $txnCode = $baseTxn->generateTxncode();
                
                if (strtolower(trim($resp->RequestType)) == 'r') {
                    $param = array(
                        'product_id' => (string) trim($resp->ProductCode),
                    );
                    $ackNo = '';
                    if (strtolower(trim($resp->IsOriginal)) == 'n') {
                        if (isset($resp->OriginalAckNo) && !empty($resp->OriginalAckNo)) {
                            $ackNo = trim($resp->OriginalAckNo);
                        }
                    }
                     $uniqueCodeFlag = (string) strtolower(trim($resp->Filler1));
                     
                    if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                         if((string) trim($resp->Filler2) !='' ){
                            $param['mobile'] = (string) trim($resp->Filler2);
                         }else{
                            $param['mobile'] = (string) trim($resp->Mobile);     
                         }
                    } else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                         $param['mobile'] = (string) trim($resp->Mobile);
                    }else{
                        $param['mobile'] = (string) trim($resp->Mobile);
                    
                    }
                    
                    $respMobile = $remitterModel->checkDuplicateMobile(array(
                        'product_id' => $param['product_id'],
                        'mobile' => $param['mobile'],
                    ));
                   
                    if ($respMobile == TRUE) {
                        return self::Exception(ErrorCodes::ERROR_EDIGITAL_MOBILE_USED_MSG, ErrorCodes::ERROR_EDIGITAL_MOBILE_USED_CODE);
                    }
                    
                    $agent = $this->getAgentConstant();
                    $gInfo = $refObject->generateOTPAPI(array(
                        'type' => (string) trim($resp->RequestType),
                        'mobile' => $param['mobile'],
                        'user_type' => 'API',
                        'user_id' => $agent,
                        'ref_id' => '',
                        'ack_no' => $ackNo,
                        'product_id' => (string) trim($resp->ProductCode),
                    ));
                } else if (strtolower(trim($resp->RequestType)) == 'e') {
                    $ackNo = '';
                    if (strtolower(trim($resp->IsOriginal)) == 'n') {
                        if (isset($resp->OriginalAckNo) && !empty($resp->OriginalAckNo)) {
                            $ackNo = trim($resp->OriginalAckNo);
                        }
                    }
                    $param = array(
                        'product_id' => (string) trim($resp->ProductCode),
                    );
                     
                    $uniqueCodeFlag = (string) strtolower(trim($resp->Filler1));
                    if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                        if((string) trim($resp->Filler2) !='' ){
                           $param['mobile'] = (string) trim($resp->Filler2);
                        }else{
                           $param['mobile'] = (string) trim($resp->Mobile);     
                        }
                    } else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        $param['partner_ref_no'] = (string) trim($resp->Filler2);
                    }else{
                        $param['mobile'] = (string) trim($resp->Mobile);
                    
                    }
                    if((string) trim($resp->Filler2) !='' ){
                        
                        $customerInfo = $remitterModel->getRemitterDetails($param);
                    
                        if (empty($customerInfo)) {
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                            $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                            return $responseObj;
                        }else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                            $param['status'] = STATUS_ACTIVE;
                            $custInfo = $remitterModel->getRemitterDetails($param);
                            if (empty($custInfo)) {
                                $responseObj->SessionID = (string) $resp->SessionID;
                                $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                                $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                                return $responseObj;
                            }else{
                                $param['mobile'] = $custInfo['mobile'];  
                            }
                        }
                    }else{
                        $param['mobile'] = (string) $resp->Mobile;  
                    }
                    if((string) trim($resp->Mobile) !='' ){
                        $param['sms_mobile'] = (string) trim($resp->Mobile);
                    }else{
                        $param['sms_mobile'] = $param['mobile'];
                    }
                    
                    $agent = $this->getAgentConstant();
                    $gInfo = $refObject->generateOTPAPI(array(
                        'type' => (string) trim($resp->RequestType),
                        'mobile' => $param['sms_mobile'],
                        'user_type' => 'API',
                        'user_id' => $agent,
                        'ref_id' => '',
                        'ack_no' => $ackNo,
                        'product_id' => (string) trim($resp->ProductCode),
                    ));
                }else if (strtolower(trim($resp->RequestType)) == 'b') {
                    $param = array(
                        'product_id' => (string) trim($resp->ProductCode),
                    );
                    $ackNo = '';
                    if (strtolower(trim($resp->IsOriginal)) == 'n') {
                        if (isset($resp->OriginalAckNo) && !empty($resp->OriginalAckNo)) {
                            $ackNo = trim($resp->OriginalAckNo);
                        }
                    }
                   
                    $uniqueCodeFlag = (string) strtolower(trim($resp->Filler1));
                    if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                         if((string) trim($resp->Filler2) !='' ){
                            $param['mobile'] = (string) trim($resp->Filler2);
                         }else{
                            $param['mobile'] = (string) trim($resp->Mobile);     
                         }
                    } else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        $param['partner_ref_no'] = (string) trim($resp->Filler2);
                    }
                    
                    $customerInfo = $remitterModel->getRemitterDetails($param);

                    if (empty($customerInfo)) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                        return $responseObj;
                    }else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        $param['status'] = STATUS_ACTIVE;
                        $custInfo = $remitterModel->getRemitterDetails($param);
                        if (empty($custInfo)) {
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                            $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                            return $responseObj;
                        }else{
                            $param['mobile'] = $custInfo['mobile'];  
                        }
                    }
                    
                    $agent = $this->getAgentConstant();
                    $gInfo = $refObject->generateOTPAPI(array(
                        'type' => (string) trim($resp->RequestType),
                        'mobile' => $param['mobile'],
                        'user_type' => 'API',
                        'user_id' => $agent,
                        'ref_id' => '',
                        'ack_no' => $ackNo,
                        'product_id' => (string) trim($resp->ProductCode),
                    ));
                }else if (strtolower(trim($resp->RequestType)) == 'n') {
                    $param = array(
                        'product_id' => (string) trim($resp->ProductCode),
                    );
                    $ackNo = '';
                    if (strtolower(trim($resp->IsOriginal)) == 'n') {
                        if (isset($resp->OriginalAckNo) && !empty($resp->OriginalAckNo)) {
                            $ackNo = trim($resp->OriginalAckNo);
                        }
                    }
                   
                    $uniqueCodeFlag = (string) strtolower(trim($resp->Filler1));
                    if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                         if((string) trim($resp->Filler2) !='' ){
                            $param['mobile'] = (string) trim($resp->Filler2);
                         }else{
                            $param['mobile'] = (string) trim($resp->Mobile);     
                         }
                    } else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        $param['partner_ref_no'] = (string) trim($resp->Filler2);
                    }
                    
                    $customerInfo = $remitterModel->getRemitterDetails($param);

                    if (empty($customerInfo)) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                        return $responseObj;
                    }else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        $param['status'] = STATUS_ACTIVE;
                        $custInfo = $remitterModel->getRemitterDetails($param);
                        if (empty($custInfo)) {
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                            $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                            return $responseObj;
                        }else{
                           $param['mobile'] = $custInfo['mobile'];  
                        }
                    }
                    
                    $agent = $this->getAgentConstant();
                    $gInfo = $refObject->generateOTPAPI(array(
                        'type' => (string) trim($resp->RequestType),
                        'mobile' => $param['mobile'],
                        'user_type' => 'API',
                        'user_id' => $agent,
                        'ref_id' => '',
                        'ack_no' => $ackNo,
                        'product_id' => (string) trim($resp->ProductCode),
                    ));
                }elseif (strtolower(trim($resp->RequestType)) == 't') {

                    $param = array(
                        'product_id' => (string) trim($resp->ProductCode),
                    );
                    $uniqueCodeFlag = (string) strtolower(trim($resp->Filler1));
                    if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                         if((string) trim($resp->Filler2) !='' ){
                            $param['mobile'] = (string) trim($resp->Filler2);
                         }else{
                            $param['mobile'] = (string) trim($resp->Mobile);     
                         }
                    } else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        $param['partner_ref_no'] = (string) trim($resp->Filler2);
                    }
                    
                    $customerInfo = $remitterModel->getRemitterDetails($param);

                    if (empty($customerInfo)) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                        return $responseObj;
                    }else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        $param['status'] = STATUS_ACTIVE;
                        $custInfo = $remitterModel->getRemitterDetails($param);
                        if (empty($custInfo)) {
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                            $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                            return $responseObj;
                        }else{
                            $param['mobile'] = $custInfo['mobile'];  
                        }
                    }
                    

                    $gInfo = $refObject->generateOTPAPI(array(
                        'product_id' => (string) trim($resp->ProductCode),
                        'type' => (string) trim($resp->RequestType),
                        'user_type' => BY_CUSTOMER,
                        'user_id' => (string) $customerInfo['id'],
                        'amount' => Util::convertToPaisa(trim($resp->Narration)),
                        'mode' => TXN_MODE_CR,
                        'mobile' => $param['mobile']
                    ));
                }elseif (strtolower(trim($resp->RequestType)) == 'i') {
                   $param = array(
                        'product_id' => (string) trim($resp->ProductCode),
                    );
                    $uniqueCodeFlag = (string) strtolower(trim($resp->Filler1));
                    if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                         if((string) trim($resp->Filler2) !='' ){
                            $param['mobile'] = (string) trim($resp->Filler2);
                         }else{
                            $param['mobile'] = (string) trim($resp->Mobile);     
                         }
                    } else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        $param['partner_ref_no'] = (string) trim($resp->Filler2);
                    }
                    $param['status'] = STATUS_ACTIVE;
                    $customerInfo = $remitterModel->getRemitterDetails($param);

                    if (empty($customerInfo)) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                        return $responseObj;
                    }else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        $param['status'] = STATUS_ACTIVE;
                        $custInfo = $remitterModel->getRemitterDetails($param);
                        if (empty($custInfo)) {
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                            $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                            return $responseObj;
                        }else{
                            $param['mobile'] = $custInfo['mobile'];  
                        }
                    }

                    $gInfo = $refObject->generateOTPAPI(array(
                        'product_id' => (string) trim($resp->ProductCode),
                        'type' => (string) trim($resp->RequestType),
                        'user_type' => BY_CUSTOMER,
                        'mobile' => $param['mobile'],
                        'user_id' => (string) $customerInfo['id'],
                        'amount' => Util::convertToPaisa($resp->Narration),
                        'mode' => TXN_MODE_CR,
                    ));
                } elseif (strtolower(trim($resp->RequestType)) == 'l') {
                   
                    $param = array(
                        'product_id' => (string) trim($resp->ProductCode),
                    );
                    $uniqueCodeFlag = (string) strtolower(trim($resp->Filler1));
                    if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                         if((string) trim($resp->Filler2) !='' ){
                            $param['mobile'] = (string) trim($resp->Filler2);
                         }else{
                            $param['mobile'] = (string) trim($resp->Mobile);     
                         }
                    } else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        $param['partner_ref_no'] = (string) trim($resp->Filler2);
                    }

                    $customerInfo = $remitterModel->getRemitterDetails($param);

                    if (empty($customerInfo)) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                        return $responseObj;
                    }else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        $param['status'] = STATUS_ACTIVE;
                        $custInfo = $remitterModel->getRemitterDetails($param);
                        if (empty($custInfo)) {
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                            $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                            return $responseObj;
                         }else{
                            $param['mobile'] = $custInfo['mobile'];  
                         }
                    }

                    $gInfo = $refObject->generateOTPAPI(array(
                        'product_id' => (string) trim($resp->ProductCode),
                        'type' => (string) trim($resp->RequestType),
                        'mobile' => $param['mobile'],
                        'user_type' => BY_CUSTOMER,
                        'user_id' => (string) $customerInfo['id'],
                        'amount' => Util::convertToPaisa(trim($resp->Narration)),
                        'mode' => TXN_MODE_CR,
                    ));
                } elseif (strtolower(trim($resp->RequestType)) == 'd') {
                    
                }elseif (strtolower(trim($resp->RequestType)) == 'f') {
                    
                    $ackNo = '';
                    if (strtolower(trim($resp->IsOriginal)) == 'n') {
                        if (isset($resp->OriginalAckNo) && !empty($resp->OriginalAckNo)) {
                            $ackNo = trim($resp->OriginalAckNo);
                        }
                    }
                     
                    $param = array(
                        'product_id' => (string) trim($resp->ProductCode),
                    );
                    
                    $uniqueCodeFlag = (string) strtolower(trim($resp->Filler1));
                    if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                         if((string) trim($resp->Filler2) !='' ){
                            $param['mobile'] = (string) trim($resp->Filler2);
                         }else{
                            $param['mobile'] = (string) trim($resp->Mobile);     
                         }
                    } else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        $param['partner_ref_no'] = (string) trim($resp->Filler2);
                    }

                    $customerInfo = $remitterModel->getRemitterDetails($param);

                    if (empty($customerInfo)) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                        return $responseObj;
                    }else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        $param['status'] = STATUS_ACTIVE;
                        $custInfo = $remitterModel->getRemitterDetails($param);
                        if (empty($custInfo)) {
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                            $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                            return $responseObj;
                         }else{
                            $param['mobile'] = $custInfo['mobile'];  
                         }
                    }

                    $gInfo = $refObject->generateOTPAPI(array(
                        'product_id' => (string) trim($resp->ProductCode),
                        'type' => (string) trim($resp->RequestType),
                        'mobile' => $param['mobile'],
                        'user_type' => BY_CUSTOMER,
                        'user_id' => (string) $customerInfo['id'],
                        'amount' => Util::convertToPaisa(trim($resp->Narration)),
                        'mode' => TXN_MODE_CR,
                    ));
                }
                //echo $gInfo.'**';exit;
                $responseObj = new stdClass();
                if ($gInfo == FALSE) {
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::OTP_FAILED_RESPONSE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::OTP_FAILED_RESPONSE_MSG;
                } else {
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = $gInfo;
                    $responseObj->ResponseCode = self::OTP_SUCCSSES_RESPONSE_CODE;
                    $responseObj->ResponseMessage = self::OTP_SUCCSSES_RESPONSE_MSG;
                }
                return $responseObj;
            } catch (App_Exception $e) {
                $this->_soapServer->_getLogger()->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::OTP_FAILED_RESPONSE_CODE;
                    $message = ErrorCodes::OTP_FAILED_RESPONSE_MSG;
                }
            
                $responseObj = new stdClass();
                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->ResponseCode = $code;
                $responseObj->ResponseMessage = $message;
                return $responseObj;
            }
        } catch (Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $code = $e->getCode();
            $message = $e->getMessage();
            if( (empty($code) ) || (empty($message)) ) {
                $code = ErrorCodes::OTP_FAILED_RESPONSE_CODE;
                $message = ErrorCodes::OTP_FAILED_RESPONSE_MSG;
            }
            $responseObj = new stdClass();
            $responseObj->SessionID = (string) $resp->SessionID;
            $responseObj->ResponseCode = $code;
            $responseObj->ResponseMessage = $message;
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
            if (!isset($sessionID) || !$this->isLogin($sessionID)) {
               return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }

            /*
             * Transaction Reference Numver Validation
             */
            Validator_Kotak::txnrefnoValidation((string) trim((string) $resp->TransactionRefNo));

            /*
             * Product code validation
             */

            Validator_Kotak::productcodeValidation((string) trim($resp->ProductCode), $this->getProductConstant());

            /*
             * Partner Reference Numver Validation
             */
            Validator_Kotak_Customer::partnerRefnoValidation((string) trim($resp->PartnerRefNo));

            /*
             * First Name Validation
             */
            Validator_Kotak_Customer::nameValidation((string) trim($resp->FirstName),'FirstName');

            /*
             * Last Name Validation
             */
            Validator_Kotak_Customer::nameValidation((string) $resp->LastName,'LastName');

            /*
             * Date of Birth Validation
             */
            Validator_Kotak::dateValidation((string) trim($resp->DateOfBirth));

            /*
             * Mobile Validation
             */
            Validator_Kotak::mobileValidation((string) trim($resp->Mobile));

            /*
             * Email Validation
             */
            if((string) trim($resp->Email) !=''){
            Validator_Kotak::emailValidation((string) trim($resp->Email));
            }
            /*
             * Address Line1 Validation
             */
            Validator_Kotak::addressLineValidation((string) trim($resp->AddressLine1));
            Validator_Kotak::addressLine2Validation((string) trim($resp->AddressLine1));
            
            /*
             * Mobile Validation
             */
            Validator_Kotak::pincodeValidation((string) trim($resp->Pincode));
            //Length Validation
            Validator_Kotak_Beneficiary::motherMaidenNameValidation((string) trim($resp->MotherMaidenName));
            //
            
            
            /*
             * SMS Flag Validation
             */
            $smsflag = (string) trim($resp->SMSFlag);
            $smsflag = strtolower($smsflag);

            if ($smsflag != '') {
                if (strlen($smsflag) > 1 || ( $smsflag != strtolower(self::SMS_FLAG_TYPE_YES) && $smsflag != strtolower(self::SMS_FLAG_TYPE_NO) )) {
                    return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_CODE);
                }
            } else {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_CODE);
            }

            /*
             * OTP Validation
             */
            $otp = (string) trim($resp->Filler1);
            if ($otp != '') {
                if (strlen($otp) != 6 || !(ctype_digit($otp))) {
                    
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER1_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER1_CODE);
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER1_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER1_CODE);
            }


            try {
                $pinCode = (string) trim($resp->Pincode);
                $citylist = new CityList();
                $strReturn = $citylist->getCityByPincodeArray($pinCode);
  
                $strReturns =   Util::toArray($strReturn);
//               
                if(empty($strReturns)){
                  return self::Exception(ErrorCodes::ERROR_PINCODE_NOT_EXIST_MSG, ErrorCodes::ERROR_PINCODE_NOT_EXIST_CODE);   
                }
                
                $cityName = isset($strReturns[0]['name']) ? $strReturns[0]['name'] : '';
                $stateCode = isset($strReturns[0]['state_code']) ? $strReturns[0]['state_code'] : '';
                
                
                $stateName = $citylist->getStateName($stateCode);
                //  $strCardNumber = (string) $resp->CardNumber;
                $strTansRefNo = (string) trim($resp->TransactionRefNo);
                $strPartnerRefNo = (string) trim($resp->PartnerRefNo);
                $customerType = !empty($resp->Filler2) ? (string) trim($resp->Filler2) : TYPE_NONKYC;
                
                //$crnMaster
                $refObject = new Reference();
                $baseTxn = new BaseTxn();
                $txnCode = $baseTxn->generateTxncode();
               
                $responseOTP = $refObject->verifyCustomerOTPAPI(array(
                    'request_type' => 'R',
                    'otp' => (string) trim($resp->Filler1),
                    'mobile' => (string) trim($resp->Mobile),
                ));

                if ($responseOTP == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::OTP_INVALID_RESPONSE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::OTP_INVALID_RESPONSE_MSG;
                    return $responseObj;
                }

                $params = array();
                $agent = $this->getAgentConstant();
                $obj = new Remit_Kotak_Remitter();
               
                $params['txnrefnum'] = $strTansRefNo; //$resp->TransactionRefNo;
                $params['partner_ref_no'] = (string) $strPartnerRefNo; //$resp->PartnerRefNo;
                $params['product_id'] = (string) trim($resp->ProductCode);
                $params['title'] = (string) trim($resp->Title);
                $params['name'] = (string) trim($resp->FirstName);
                $params['middle_name'] = (string) trim($resp->MiddleName);
                $params['last_name'] = (string) trim($resp->LastName);
                $params['gender'] = (string) trim($resp->Gender);
                $params['dob'] = (string) trim($resp->DateOfBirth);
                $params['mobile'] = (string) trim($resp->Mobile);
                $params['email'] = (string) trim($resp->Email);
                $params['mother_maiden_name'] = (string) trim($resp->MotherMaidenName);
                $params['landline'] = (string) trim($resp->Landline);
                $params['address'] = (string) trim($resp->AddressLine1);
                $params['address_line2'] = (string) trim($resp->AddressLine2);
                $params['city'] = $cityName;
                $params['state'] = $stateName;
                $params['pincode'] = (string) trim($resp->Pincode);
                $params['country'] = (string) trim($resp->Country);
                //$params['otp'] = (string) trim($resp->Filler1);
                $params['by_api_user_id'] = $agent;
                $params['by_agent_id'] = $agent;
                $params['customer_type'] = $customerType;
                $params['date_created'] = date('Y-m-d H:i:s');
                $params['status'] = STATUS_ACTIVE;
                $params['txn_code'] = $txnCode;
                //$remit_params['Filler3'] = (string) $resp->Filler3;
                //$remit_params['Filler4'] = (string) $resp->Filler4;
                //$remit_params['Filler5'] = (string) $resp->Filler5;

               
                /*
                 * checkDuplicateMobile : Checking duplicate mobile number for customer
                 */
                $respMobile = $obj->checkDuplicateMobile(array(
                    'product_id' => $params['product_id'],
                    'mobile' => (string) trim($resp->Mobile),
                ));
                if($respMobile == TRUE) {
                    return self::Exception(ErrorCodes::ERROR_EDIGITAL_MOBILE_USED_MSG, ErrorCodes::ERROR_EDIGITAL_MOBILE_USED_CODE);
                }if( (empty($code) ) || (empty($message)) ) {
                $code = ErrorCodes::OTP_FAILED_RESPONSE_CODE;
                $message = ErrorCodes::OTP_FAILED_RESPONSE_MSG;
            }
                /*
                 * checkDuplicateMemberID : Checking duplicate member id for customer
                 */
                $respMemberID = $obj->checkDuplicatePartnerRefNo(array(
                    'product_id' => $params['product_id'],
                    'partner_ref_no' => $params['partner_ref_no'],
                ));
                if ($respMemberID == TRUE) {
                    return self::Exception(ErrorCodes::ERROR_EDIGITAL_PAR_USED_MSG, ErrorCodes::ERROR_EDIGITAL_PAR_USED_CODE);
                }
                
                 /*
                 * checkDuplicateTransNum : Checking duplicate Transaction Referance Number for customer
                 */
                $respTransNum = $obj->checkDuplicateTransNum(array(
                    'product_id' => $params['product_id'],
                    'txnrefnum' => $strTansRefNo,
                    'agent_id' => $agent,
                ));
                if ($respTransNum == TRUE) {
                     return self::Exception(ErrorCodes::ERROR_EDIGITAL_TRAN_REF_NO_USED_MSG, ErrorCodes::ERROR_EDIGITAL_TRAN_REF_NO_USED_CODE);
                }
                
                /*
                 * checkDuplicateEmail : Checking duplicate email id for customer
                 */
//                $respEmail = $obj->checkDuplicateEmail(array(
//                    'product_id' => $params['product_id'],
//                    'email' => (string) trim($resp->Email),
//                ));
//                if ($respEmail == TRUE) {
//                    return self::Exception('Email address already in use', App_ApiServer_Exchange::$INVALID_RESPONSE);
//                }
                // txn Code
                $responseCustID = $obj->addRemitterApi($params);
                if ($responseCustID) {
                 
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
                    
                    if (strtolower(trim($resp->SMSFlag)) == strtolower(FLAG_Y)) {
                        try {
                            // Send SMS  
                            $params_sms = array(
                                'product_id' => $params['product_id'],
                                'cust_name' => $params['name'],
                                'mobile' => $params['mobile'],
                            );
                            $obj->generateSMSDetails($params_sms, $smsType = CUST_REGISTRATION_API);
                        } catch (Exception $e) {
                            //if SMS not sent Don't respond with failure
                            App_Logger::log(serialize($e), Zend_Log::ERR);
                        }
                    }
                } else {
                    $errorMsg = $obj->getError();
                    $errorMsg = empty($errorMsg) ? ErrorCodes::ERROR_CUSTOMER_REGISTRATION_FAIL_MSG : $errorMsg;
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->PartnerRefNo = (string) trim($resp->PartnerRefNo);
                    $responseObj->ResponseCode = ErrorCodes::ERROR_CUSTOMER_REGISTRATION_FAIL_CODE;
                    $responseObj->ResponseMessage = $errorMsg;
                }
                return $responseObj;
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_CUSTOMER_REGISTRATION_FAIL_CODE;
                    $message = ErrorCodes::ERROR_CUSTOMER_REGISTRATION_FAIL_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message,$code);
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_CUSTOMER_REGISTRATION_FAIL_CODE;
                    $message = ErrorCodes::ERROR_CUSTOMER_REGISTRATION_FAIL_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message,$code);
        }
    }
    public function BeneficiaryRegistrationRequest() {//Do not add comments for method summary

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
           
            /*
             * Login Validation
             */
            $sessionID = (string)trim($resp->SessionID);
            if (!isset($sessionID) || !$this->isLogin($sessionID)) {
               return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }

            Validator_Kotak::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());
            Validator_Kotak_Beneficiary::titleValidation((string) trim($resp->Title));
            Validator_Kotak_Beneficiary::nameValidation((string) trim($resp->Name));
            Validator_Kotak::mobileValidation((string) trim($resp->Mobile));
            Validator_Kotak_Beneficiary::emailValidation((string) trim($resp->Email));
            Validator_Kotak_Beneficiary::addressValidation((string) trim($resp->AddressLine1));
            Validator_Kotak_Beneficiary::addressValidation((string) trim($resp->AddressLine2));
            Validator_Kotak::txnrefnoValidation((string) trim($resp->TransactionRefNo));
           
            if(trim($resp->Filler1)  !=''){
             Validator_Ratnakar_Beneficiary::beneBankAccountTypeValidation((string) trim($resp->Filler1));
             $bankAccountType = strtolower((string) trim($resp->Filler1));
            }else{
                $bankAccountType = SAVING_ACCOUNT_TYPE;
            }
            // Check OTP
            $otp = (string) trim($resp->OTP);
            if ($otp != '') {
                if (strlen($otp) != 6 || !(ctype_digit($otp))) {
                     throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
                }
            }else {
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


            // Check Remitter Flag
            $remitterflag = (string) trim($resp->RemitterFlag);
            $remitterflag = strtolower($remitterflag);

            if ($remitterflag != '') {
                if (strlen($remitterflag) > 1 || ( $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_MOB) && $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER) )) {
                   throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE);
                }else{
                    if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                        Validator_Kotak::mobileValidation((string) trim($resp->RemitterCode));
                    } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        Validator_Kotak_Customer::partnerRefnoValidation((string) trim($resp->RemitterCode));
                  }
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE);
            }
            // Bank details validation
            Validator_Kotak_Beneficiary::bankDetailsValidation(array(
                'ifsc_code' => (string) trim($resp->BankIfscode),
                'bank_account_number' => (string) trim($resp->BankAccountNumber),
            ));

            try {

                $beneficiaryModel = new Remit_Kotak_Beneficiary();


                // Get Customer details
                $param['product_id'] = (string) trim($resp->ProductCode);

                if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOB)) {
                    $param['mobile'] = (string) trim($resp->RemitterCode);
                } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                    $param['partner_ref_no'] = (string) trim($resp->RemitterCode);
                }

                // get Remitter id	 
                $objectRelation = new Remit_Kotak_Remitter() ;
                $remitterObj = $objectRelation->getRemitterDetails($param);
                
                if($remitterObj == FALSE){ 
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                }
                
                $refObject = new Reference();
                
                $baseTxn = new BaseTxn();
                $queryRefNum = $baseTxn->generateTxncode();
                
                $responseOTP = $refObject->verifyCustomerOTPAPI(array(
                    'request_type' => 'B',
                    'otp' => (string) trim($resp->OTP),
                    'mobile' => (string) $remitterObj['mobile'],
                ));

                if ($responseOTP == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::OTP_INVALID_RESPONSE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::OTP_INVALID_RESPONSE_MSG;
                    return $responseObj;
                }

//                $txncode = new Benecode();
//                if ($txncode->generateTxncode()) {
//                    $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
//                    $txncode->setUsedStatus(); //Mark Txncode as used
//                }


                $params = array();
                $params['remitter_id'] = $remitterObj['id'];
                $params['title'] = (string) trim($resp->Title);
                $params['name'] = (string) trim($resp->Name);
                $params['ifsc_code'] = (string) strtoupper(trim($resp->BankIfscode));
                $params['bank_account_number'] = (string) trim($resp->BankAccountNumber);
                $params['bank_account_type'] = $bankAccountType;
                $params['mobile'] = (string) trim($resp->Mobile);
                $params['email'] = (string) trim($resp->Email);
                $params['address_line1'] = (string) trim($resp->AddressLine1);
                $params['address_line2'] = (string) trim($resp->AddressLine2);
//                $params['bene_code'] = $paramsTxnCode;
                $params['queryrefno'] = $queryRefNum;
                $params['by_agent_id'] = $this->getAgentConstant();
                $params['txnrefnum'] = (string) trim($resp->TransactionRefNo);
                $params['date_created'] =  new Zend_Db_Expr('NOW()');

                Validator_Kotak_Beneficiary::chkBeneAccountExists(array(
                    'remitter_id' => $remitterObj['id'],
                    'ifsc_code' => $params['ifsc_code'],
                    'bank_account_number' => $params['bank_account_number']));
                $strTansRefNo = (string) trim($resp->TransactionRefNo);
                $respTransNum = $beneficiaryModel->checkDuplicateTransNum(array(
                    'txnrefnum' => $strTansRefNo,
                    'agent_id' => $this->getAgentConstant()
                ));
                if ($respTransNum == TRUE) {
                    return self::Exception(ErrorCodes::ERROR_EDIGITAL_TRAN_REF_NO_USED_MSG, ErrorCodes::ERROR_EDIGITAL_TRAN_REF_NO_USED_CODE);
                }

                $beneCode = $beneficiaryModel->addbeneficiaryAPI($params);
                
                if ($beneCode > 0) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->AckNo = $queryRefNum; //$baseTxn->getTxncode();
                    $responseObj->BeneficiaryCode = $beneCode; //
                    $responseObj->ResponseCode = self::BENEFICIARY_REGISTRATION_SUCC_CODE;
                    $responseObj->ResponseMessage = self::BENEFICIARY_REGISTRATION_SUCC_MSG;
                    
                    // Update otp entry 
                    
                    $upadteOTP = $refObject->updateCustomerOTPAPI(array(
                        'request_type' => 'B',
                        'id' => $responseOTP['id'],
                    ));
                } else {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) $resp->TransactionRefNo;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_BENE_REGISTRATION_FAIL_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_BENE_REGISTRATION_FAIL_MSG;
                }

                if (strtolower(trim($resp->SMSFlag)) == strtolower(FLAG_Y)) {
                    try {
                        // Send SMS   
                        $params_sms = array(
                            'product_id' => $param['product_id'],
                            'bene_name' => $params['name'],
                            'mobile' => $remitterObj['mobile'],
                        );
                        $objectRelation->generateSMSDetails($params_sms, $smsType = BENE_REG_SMS);
                    } catch (Exception $e) {
                        //if SMS not sent Don't respond with failure
                        App_Logger::log(serialize($e), Zend_Log::ERR);
                    }
                }
                return $responseObj;
            } catch (App_Exception $e) {
                App_Logger::log($e, Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_BENE_REGISTRATION_FAIL_CODE;
                    $message = ErrorCodes::ERROR_BENE_REGISTRATION_FAIL_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message,$code);
            }
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $code = $e->getCode();
            $message = $e->getMessage();
            if( (empty($code) ) || (empty($message)) ) {
                $code = ErrorCodes::ERROR_BENE_REGISTRATION_FAIL_CODE;
                $message = ErrorCodes::ERROR_BENE_REGISTRATION_FAIL_MSG;
            }
            $this->_soapServer->_getLogger()->__setException($message);
            return self::Exception($message,$code);
        }
    }

    public function PartnerBalanceRequest() {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);


            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }

           Validator_Kotak::PartnerCodeValidation((string) trim($resp->PartnerCode));
           
            try {
                $partnerCode = (string) trim($resp->PartnerCode);
                
                $agentUser = new AgentUser();
                $object = new Agents();
                $agent = $this->getAgentConstant();
                
                $agentInfo = $agentUser->getAgentDetailsById($agent);

                if($agentInfo['agent_code'] != $partnerCode){
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_PARTNER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_PARTNER_CODE_MSG;
                    return $responseObj;
                }
                
                $custInfo = $object->getAgentBalance($agent);
                if (empty($custInfo)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->PartnerBalance = 0;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                }
                $baseTxn = new BaseTxn();
                $txnCode = $baseTxn->generateTxncode();
                $curCode = CURRENCY_INR;
                $responseObj = new stdClass();
                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->PartnerBalance = isset($custInfo['amount']) ? Util::convertIntoPaisa($custInfo['amount']) : 0;
                $responseObj->ResponseCode = self::PARTNER_BALANCE_SUCCSSES_RESPONSE_CODE;
                $responseObj->ResponseMessage = self::PARTNER_BALANCE_SUCCSSES_RESPONSE_MSG;
                return $responseObj;
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_PARTNER_BAL_ENQ_FAILED_RESPONSE_CODE;
                    $message = ErrorCodes::ERROR_PARTNER_BAL_ENQ_FAILED_RESPONSE_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
               return self::Exception($message,$code);
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_PARTNER_BAL_ENQ_FAILED_RESPONSE_CODE;
                    $message = ErrorCodes::ERROR_PARTNER_BAL_ENQ_FAILED_RESPONSE_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message,$code);
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
            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
             return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }
       
            /*
             * Transaction Reference Numver Validation
             */
            Validator_Kotak::txnrefnoValidation((string) trim($resp->TransactionRefNo));

            /*
             * Product code validation
             */

            Validator_Kotak::productcodeValidation((string) trim($resp->ProductCode), $this->getProductConstant());

            if((string) trim($resp->Mobile) !=''){
                 Validator_Kotak::mobileValidation((string) trim($resp->Mobile));
            }
            
            if((string) trim($resp->Email) !=''){
                 Validator_Kotak::emailValidation((string) trim($resp->Email));
            }
            try {
                //  $strCardNumber = (string) $resp->CardNumber;
                $txnrefnum = (string) trim($resp->TransactionRefNo);
                $CustomerNo = (string) trim($resp->CustomerNo);
                $params = array();
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
                
                // Check TxnIdentifierType
                $customerIdentifierType = (string) trim($resp->CustomerIdentifierType);
                $customerIdentifierType = strtolower($customerIdentifierType);
    
                if ($customerIdentifierType != '') {
                    if (strlen($customerIdentifierType) > 3 || ( $customerIdentifierType != strtolower(self::TXN_IDENTIFIER_TYPE_MOBILE) && $customerIdentifierType != strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER) )) {
                        throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_IDENTIFIRE_TYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_IDENTIFIRE_TYPE_CODE);
                    }else if((string) trim($resp->CustomerNo) ==''){
                      throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMERNO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMERNO_CODE);   
                    }else{
                        if ($customerIdentifierType == strtolower(self::TXN_IDENTIFIER_TYPE_MOBILE)){
                           Validator_Kotak::mobileValidation((string) trim($resp->CustomerNo));    
                        } else if ($customerIdentifierType == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER)) {
                            Validator_Kotak_Customer::partnerRefnoValidation((string) trim($resp->CustomerNo));
                        }
                    }
                } else {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_IDENTIFIRE_TYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_IDENTIFIRE_TYPE_CODE);
                }
                $search=array();
                if($customerIdentifierType == strtolower(self::TXN_IDENTIFIER_TYPE_MOBILE)) {
                    $search['mobile'] = (string) trim($resp->CustomerNo);
                    $search['product_id'] = (string) trim($resp->ProductCode);
                }elseif($customerIdentifierType == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER)) {
                    $search['partner_ref_no'] = (string) trim($resp->CustomerNo);
                    $search['product_id'] = (string) trim($resp->ProductCode);
                }
               
                $params['txnrefnum'] = (string) $txnrefnum; //$resp->TransactionRefNo;
                $params['product_id'] = (string) trim($resp->ProductCode);
                if((string) trim($resp->Mobile) !=''){
                    $params['mobile'] = (string) trim($resp->Mobile);
                }
                if((string) trim($resp->Email) !=''){
                    $params['email'] = (string) trim($resp->Email);
                }
                if((string) trim($resp->Landline) !=''){
                    $params['landline'] = (string) trim($resp->Landline);
                }
                if((string) trim($resp->AddressLine1) !=''){
                    $params['address'] = (string) trim($resp->AddressLine1);
                }
                if((string) trim($resp->AddressLine2) !=''){
                    $params['address_line2'] = (string) trim($resp->AddressLine2);
                }
               
                if((string) trim($resp->Pincode) !=''){
                   
                    //Validation 
                    
                        $pinCode = (string) trim($resp->Pincode);
                        $citylist = new CityList();
                        $strReturn = $citylist->getCityByPincodeArray($pinCode);

                        $strReturns =   Util::toArray($strReturn);

                        if(empty($strReturns)){
                         return self::Exception(ErrorCodes::ERROR_PINCODE_NOT_EXIST_MSG, ErrorCodes::ERROR_PINCODE_NOT_EXIST_CODE);   
                        }

                        $cityName = isset($strReturns[0]['name']) ? $strReturns[0]['name'] : '';
                        $stateCode = isset($strReturns[0]['state_code']) ? $strReturns[0]['state_code'] : '';


                        $stateName = $citylist->getStateName($stateCode);


                           $params['pincode'] = (string) trim($resp->Pincode);
                            $params['city'] = $cityName;
                            $params['state'] = $stateName;
                    
                    
                    
                }
                 if((string) trim($resp->Pincode) !=''){
                    $params['country'] = (string) trim($resp->Country);
                }
                //$params['OTP'] = (int) trim($resp->OTP);
                $agent = $this->getAgentConstant();
                $params['by_api_user_id'] = $agent;
               
                //$remit_params['product_id'] = (string) trim($resp->ProductCode);
                //$remit_params['by_agent_id'] = $agent;
                //$params['Filler3'] = (string) $resp->Filler3;
                //$params['Filler4'] = (string) $resp->Filler4;
                //$params['Filler5'] = (string) $resp->Filler5;

                //$obj = new Corp_Ratnakar_Cardholders();
                $baseTxn = new BaseTxn();
                $txnCode = $baseTxn->generateTxncode();
                $obj = new Remit_Kotak_Remitter();
                $refObject = new Reference();
                $custRecord = array(
                    'mobile' => $search['mobile'],
                    'partner_ref_no' => $search['partner_ref_no'],
                    'product_id' => $search['product_id'],
                );
                $custDetail = $obj->getRemitterDetails($custRecord);
                if ($custDetail == FALSE) {
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                }
                if((string) trim($resp->Mobile) !=''){
                    $mobileNum = (string) trim($resp->Mobile);
                }else if(!empty($custDetail)){                    
                    $mobileNum = $custDetail['mobile']; 
                }
                
                $responseOTP = $refObject->verifyCustomerOTPAPI(array(
                    'request_type' => 'E',
                    'otp' => (string) trim($resp->OTP),
                    'mobile' => $mobileNum,
                ));
                if ($responseOTP == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::OTP_INVALID_RESPONSE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::OTP_INVALID_RESPONSE_MSG;
                    return $responseObj;
                }
                 
                
                
                /*
                 * checkEditDuplicateMobile : Checking duplicate mobile number for customer
                 */
                if((string) trim($resp->Mobile) !=''){
                   
                    $respMobile = $obj->checkDuplicateMobile(array('mobile'=>$params['mobile'],'product_id'=>$params['product_id']),array('id'=>$custDetail['id']));
                    if ($respMobile == TRUE) {
                        return self::Exception(ErrorCodes::ERROR_EDIGITAL_MOBILE_USED_MSG, ErrorCodes::ERROR_EDIGITAL_MOBILE_USED_CODE);
                    }
                }
                
                /*
                 * checkEditDuplicateEmail : Checking duplicate email id for customer
                 */
                 if((string) trim($resp->Email) !=''){
                   
                    $respEmail = $obj->checkDuplicateEmail(array('email'=>$params['email'],'product_id'=>$params['product_id']),array('id'=>$custDetail['id']));
                    if ($respEmail == TRUE) {
                       return self::Exception(ErrorCodes::ERROR_EDIGITAL_EMAIL_USED_MSG, ErrorCodes::ERROR_EDIGITAL_EMAIL_USED_CODE);
                    }
                }
                $response = $obj->editRemitterApi($params,$custDetail['id']);
                if ($response == TRUE) {
                    /*
                     *Sending SMS
                     */
                    
                    if (strtolower(trim($resp->SMSFlag)) == strtolower(FLAG_Y)) {
                        // Send SMS  
                       try {
                            $params_sms = array(
                                'product_id' => $params['product_id'],
                                'mobile' => $mobileNum,
                            );
                            $obj->generateSMSDetails($params_sms, $smsType = UPDATE_CUST_SMS);
                        } catch (Exception $e) {
                            //if SMS not sent Don't respond with failure
                            App_Logger::log(serialize($e), Zend_Log::ERR);
                        }
                    }
                    
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->CustomerIdentifierType = (string) trim($resp->CustomerIdentifierType);
                    $responseObj->CustomerNo = (string) trim($resp->CustomerNo);
                    $responseObj->AckNo = $custDetail['txn_code']; //$baseTxn->getTxncode();
                    $responseObj->ResponseCode = self::CUSTOMER_UPDATION_SUCC_CODE;
                    $responseObj->ResponseMessage = self::CUSTOMER_UPDATION_SUCC_MSG;
                     // Update otp entry 
                    
                    $upadteOTP = $refObject->updateCustomerOTPAPI(array(
                        'request_type' => 'E',
                        'id' => $responseOTP['id'],
                    ));
                }else{
                    $errorMsg = $obj->getError();
                    $errorMsg = empty($errorMsg) ? ErrorCodes::ERROR_EDIGITAL_CUSTOMER_UPDATION_FAIL_MSG : $errorMsg;
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->CustomerIdentifierType = (string) trim($resp->CustomerIdentifierType);
                    $responseObj->CustomerNo = (string) trim($resp->CustomerNo);
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_UPDATION_FAIL_CODE;
                    $responseObj->ResponseMessage = $errorMsg;
                }
                return $responseObj;
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_UPDATION_FAIL_CODE;
                    $message = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_UPDATION_FAIL_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message,$code);
            }
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $code = $e->getCode();
            $message = $e->getMessage();
            if( (empty($code) ) || (empty($message)) ) {
                $code = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_UPDATION_FAIL_CODE;
                $message = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_UPDATION_FAIL_MSG;
            }
            $this->_soapServer->_getLogger()->__setException($message);
            return self::Exception($message,$code);
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
            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }

            /*
             * Transaction Reference Numver Validation
             */
            Validator_Kotak::txnrefnoValidation((string) trim($resp->TransactionRefNo));

            /*
             * Query Reference Numver Validation
             */
            Validator_Kotak::queryrefnoValidation((string) trim($resp->QueryReqNo));

            try {
                $strTansRefNo = (string) trim($resp->TransactionRefNo);
                $strQueryReqNo = (string) trim($resp->QueryReqNo);

                $refObject = new Reference();
                $productConst = $this->getProductConstant();
                $productModel = new Products();
                $productID = $productModel->getProductIDbyConst($productConst); 
                $params['product_id'] = $productID;
              //  $params['txnrefnum'] = (string) $strTansRefNo; //$resp->TransactionRefNo;
                $params['txn_code'] = $strQueryReqNo; //$resp->PartnerRefNo;
              //  $params['by_api_user_id'] = $this->_TP_ID;
                //$params['Filler3'] = (string) $resp->Filler3;
                //$params['Filler4'] = (string) $resp->Filler4;
                //$params['Filler5'] = (string) $resp->Filler5;

                $objectRelation = new Remit_Kotak_Remitter() ;
                $remitterObj = $objectRelation->getRemitterDetails($params);
               
                if (!empty($remitterObj)) {

                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->PartnerRefNo = $remitterObj['partner_ref_no'];
                    $responseObj->ProductCode = $remitterObj['product_id'];
                    $responseObj->Mobile = $remitterObj['mobile'];
                    $responseObj->Email = $remitterObj['email'];
                    $responseObj->Name = $remitterObj['name'];
                    $responseObj->TransactionStatus = $remitterObj['status'];
                    $responseObj->ResponseCode = self::QUERY_REGISTRATION_SUCC_CODE;
                    $responseObj->ResponseMessage = self::QUERY_REGISTRATION_SUCC_MSG;
                } else {
                    $errorMsg = $objectRelation->getError();
                    $errorMsg =  ErrorCodes::ERROR_QUERY_REGISTRATION_FAIL_MSG ;
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_QUERY_REGISTRATION_FAIL_CODE;
                    $responseObj->ResponseMessage = $errorMsg;
                }
                return $responseObj;
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_QUERY_REGISTRATION_FAIL_CODE;
                    $message = ErrorCodes::ERROR_QUERY_REGISTRATION_FAIL_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message,$code);
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_QUERY_REGISTRATION_FAIL_CODE;
                    $message = ErrorCodes::ERROR_QUERY_REGISTRATION_FAIL_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
               return self::Exception($message,$code);
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
            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
                   return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }

            /*
             * Product Code Validation
             */
            Validator_Kotak::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());


            /*
             * Query Reference Numver Validation
             */
            Validator_Kotak::queryrefnoValidation((string) trim($resp->QueryReqNo));

            /*
             * Bene Code Validation
             */
            if((string) trim($resp->BeneficiaryCode) !=''){
            Validator_Kotak_Beneficiary::beneCodeValidation((string) trim($resp->BeneficiaryCode));
            }

            // Check Remitter Flag
            $remitterflag = (string) trim($resp->RemitterFlag);
            $remitterflag = strtolower($remitterflag);

            if ($remitterflag != '') {
                if (strlen($remitterflag) > 1 || ( $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE) && $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER) )) {
                    return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE);
                }else{
                    if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                        Validator_Kotak::mobileValidation((string) trim($resp->RemitterCode));
                    } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        Validator_Kotak_Customer::partnerRefnoValidation((string) trim($resp->RemitterCode));
                  }
                }
            } else {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE);
            }

            try {
                //  $strTansRefNo = (string) trim($resp->TransactionRefNo);
                $strQueryReqNo = (string) trim($resp->QueryReqNo);
                $refObject = new Reference();


                //$params['txnrefnum'] = (string) $strTansRefNo;
                //$resp->TransactionRefNo;
                //$params['by_api_user_id'] = $this->_TP_ID;
                //$params['Filler3'] = (string) $resp->Filler3;
                //$params['Filler4'] = (string) $resp->Filler4;
                //$params['Filler5'] = (string) $resp->Filler5;
                
                $params['QueryReqNo'] = (string) $strQueryReqNo; //$resp->PartnerRefNo;
                $params['product_id'] = (string) trim($resp->ProductCode);
                
                if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                    $params['mobile'] = (string) trim($resp->RemitterCode);
                } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                    $params['partner_ref_no'] = (string) trim($resp->RemitterCode);
                }
                $obj = new Remit_Kotak_Remitter();
                $beneficiaryModel = new Remit_Kotak_Beneficiary();
              
                /*
                 * Getiing customer Detail  
                 */
                $remitterData = $obj->getRemitterDetails($params);


                /*
                 *  Featching Remitter Id from object relation
                 */
                if (!empty($remitterData)) {
                    
                    $bene_param['bene_code'] = (string) trim($resp->BeneficiaryCode);
                    $bene_param['queryrefno'] = (string) trim($resp->QueryReqNo);
                    $bene_param['remitter_id'] = $remitterData['id'];
                    $bene_param['status'] = STATUS_ACTIVE;
                    $beneInfo = $beneficiaryModel->getBeneInfoRow($bene_param);

                    if (!empty($beneInfo)) {

                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->RemitterCode = (string) trim($resp->RemitterCode);
                        $responseObj->BeneficiaryCode = (string) trim($resp->BeneficiaryCode);
                        //$responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                        $responseObj->Name = $beneInfo['name'];
                        $responseObj->Mobile = $beneInfo['mobile'];
                        $responseObj->Email = $beneInfo['email'];
                        $responseObj->AddressLine1 = $beneInfo['address_line1'];
                        $responseObj->AddressLine2 = $beneInfo['address_line2'];
                        $responseObj->BankName = $beneInfo['bank_name'];
                        $responseObj->BankBranch = $beneInfo['branch_name'];
                        $responseObj->BankCity = $beneInfo['branch_city'];
                        $responseObj->BankIfscode = $beneInfo['ifsc_code'];
                        $responseObj->BankAccountNumber = $beneInfo['bank_account_number'];
                        $responseObj->ResponseCode = self::QUERY_BENEFICIARY_SUCC_CODE;
                        $responseObj->ResponseMessage = self::QUERY_BENEFICIARY_SUCC_MSG;
                    } else {
                        $errorMsg = $obj->getError();
                        $errorMsg = empty($errorMsg) ? ErrorCodes::ERROR_EDIGITAL_INVALID_QUERY_REQ_NUM_MSG : $errorMsg;
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;

                        if((string) trim($resp->BeneficiaryCode) != ''){
                            $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_BENE_NOT_FOUND_MSG;
                            $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_BENE_NOT_FOUND_CODE;
                        } else {
                            $responseObj->ResponseMessage = $errorMsg;
                            $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_QUERY_REQ_NUM_CODE;
                        }
                    }
                    return $responseObj;
                } else {
                    return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE);
                }
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::QUERY_BENEFICIARY_FAIL_CODE;
                    $message = ErrorCodes::QUERY_BENEFICIARY_FAIL_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
               return self::Exception($message,$code);
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::QUERY_BENEFICIARY_FAIL_CODE;
                    $message = ErrorCodes::QUERY_BENEFICIARY_FAIL_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
               return self::Exception($message,$code);
        }
    }

   

    public function QueryRemittanceRequest() {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);


            if( !isset($resp->SessionID) || !$this->isLogin((string) $resp->SessionID)) {
                 return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
             }

            Validator_Kotak::queryrefnoValidation((string) trim((string)$resp->QueryReqNo));
            Validator_Kotak::txnrefnoValidation((string) trim($resp->TransactionRefNo));

            try {


                $object = new Remit_Kotak_Remittancerequest();
                $remitttanceData = $object->getRemitterRequestsInfoByTxnCode((string) trim($resp->QueryReqNo));
                // Query Remittance request code

                if (empty($remitttanceData)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_QUERY_REQ_NUM_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_QUERY_REQ_NUM_MSG;
                    return $responseObj;
                }
                
                $obj = new Remit_Kotak_Remitter();
                $beneficiaryModel = new Remit_Kotak_Beneficiary();
                $remitterData = $obj->getRemitterDetails(array('id'=>$remitttanceData['remitter_id']));
                $bene_param['remitter_id'] = $remitterData['id'];
                $bene_param['id'] = $remitttanceData['beneficiary_id'];
                $bene_param['status'] = STATUS_ACTIVE;
                $beneInfo = $beneficiaryModel->getBeneInfoRow($bene_param);
                
                
                $curCode = CURRENCY_INR;
                //echo "<pre>"; print_r($remitttanceData); exit;
                $responseObj = new stdClass();

                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                $responseObj->ProductCode = $remitttanceData['product_id'];
                $responseObj->RemitterCode = $remitterData['partner_ref_no'];
                $responseObj->BeneficiaryCode = $beneInfo['bene_code'];
                $responseObj->Narration = $remitttanceData['sender_msg'];
                $responseObj->Amount = Util::convertIntoPaisa($remitttanceData['amount']);
                $responseObj->TransactionStatus = $remitttanceData['status'];
                $responseObj->ResponseCode = self::QUERY_REMITTANCE_SUCCESS_CODE;
                $responseObj->ResponseMessage = self::QUERY_REMITTANCE_SUCCESS_MSG;

                return $responseObj;
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_QUERY_REMITTANCE_FAILURE_CODE;
                    $message = ErrorCodes::ERROR_QUERY_REMITTANCE_FAILURE_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
               return self::Exception($message,$code);
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_QUERY_REMITTANCE_FAILURE_CODE;
                    $message = ErrorCodes::ERROR_QUERY_REMITTANCE_FAILURE_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
              return self::Exception($message,$code);
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
            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
                   return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }

            /*
             * Product Code Validation

             */
            Validator_Kotak::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());


            /*
             * Query Reference Numver Validation
             */
          //  Validator_Ratnakar::queryrefnoValidation((string) trim($resp->QueryReqNo));

            /*
             * Bene Code Validation
             */

            Validator_Kotak_Beneficiary::beneCodeValidation((string) trim($resp->BeneficiaryCode));


             /*
             * Transaction Reference Numver Validation
             */
            Validator_Kotak::txnrefnoValidation((string)trim($resp->TransactionRefNo));
            
           
            // Check Remitter Flag
            $remitterflag = (string) trim($resp->RemitterFlag);
            $remitterflag = strtolower($remitterflag);

            if ($remitterflag != '') {
                if (strlen($remitterflag) > 1 || ( $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_MOB) && $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER) )) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE);          
                }else{
                    if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                        Validator_Kotak::mobileValidation((string) trim($resp->RemitterCode));
                    } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        Validator_Kotak_Customer::partnerRefnoValidation((string) trim($resp->RemitterCode));
                  }
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE);          
            }

             /*
             * SMS Flag Validation
             */
            $smsflag = (string)trim($resp->SMSFlag);
            $smsflag = strtolower($smsflag);
          
            if ($smsflag != '') {
            if(strlen($smsflag) > 1 ||( $smsflag != strtolower(self::SMS_FLAG_TYPE_YES) && $smsflag != strtolower(self::SMS_FLAG_TYPE_NO) )){
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_CODE);
             }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_CODE);
            }
            

            try {
                $strTansRefNo = (string) trim($resp->TransactionRefNo);
                $strQueryReqNo = (string) trim($resp->QueryReqNo);

                $refObject = new Reference();


                $params['txnrefnum'] = (string) $strQueryReqNo; //$resp->PartnerRefNo;
                $params['product_id'] = (string) trim($resp->ProductCode);
                //$params['Filler3'] = (string) $resp->Filler3;
                //$params['Filler4'] = (string) $resp->Filler4;
                //$params['Filler5'] = (string) $resp->Filler5;
                if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOB)) {
                    $params['mobile'] = (string) trim($resp->RemitterCode);
                } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                    $params['partner_ref_no'] = (string) trim($resp->RemitterCode);
                }
                $obj = new Remit_Kotak_Remitter();
                $beneficiaryModel = new Remit_Kotak_Beneficiary();
                /*
                 * Getiing customer Detail  
                 */
                $remitterData = $obj->getRemitterDetails($params);
                if (!empty($remitterData)) {
                    $bene_param['remitter_id'] = $remitterData['id'];
                    $bene_param['status'] = STATUS_ACTIVE;
                    $bene_param['bene_code'] = (string) trim($resp->BeneficiaryCode);
                    $beneInfo = $beneficiaryModel->getBeneInfoRow($bene_param);
                    if (!empty($beneInfo)) {
                        $beneID = $beneInfo['id'];
                        
                        $updatearr = array('status' => STATUS_INACTIVE);
                        $beneUpdate = $beneficiaryModel->updateBeneficiaryDetails($updatearr,$beneID);
                        $baseTxn = new BaseTxn();
                        $txnCode = $baseTxn->generateTxncode();
                        if (!empty($beneInfo) && $beneUpdate == TRUE ) {
                            
                            $responseObj = new stdClass();
                            $responseObj->SessionID = (string) $resp->SessionID;
                          //  $responseObj->AckNo = $txnCode; //$baseTxn->getTxncode();
                            $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                            $responseObj->RemitterCode = (string) trim($resp->RemitterCode);
                            $responseObj->BeneficiaryCode = (string) trim($resp->BeneficiaryCode);
                            $responseObj->ResponseCode = self::DEACTIVE_BENEFICIARY_SUCC_CODE;
                            $responseObj->ResponseMessage = self::DEACTIVE_BENEFICIARY_SUCC_MSG;
                       
                            if (strtolower(trim($resp->SMSFlag)) == strtolower(FLAG_Y)) {
                                // Send SMS  
                                try {
                                    $params_sms = array(
                                        'product_id' => $params['product_id'],
                                        'bene_name' => $beneInfo['name'],
                                        'mobile' => $remitterData['mobile'],
                                    );
                                    $obj->generateSMSDetails($params_sms, $smsType = DIACTIVE_BENE_SMS);
                                } catch (Exception $e) {
                                    //if SMS not sent Don't respond with failure instead log it
                                    App_Logger::log(serialize($e), Zend_Log::ERR);
                                }
                            }
                        } else {
                            $errorMsg = $obj->getError();
                            $errorMsg = empty($errorMsg) ? ErrorCodes::ERROR_DEACTIVE_BENEFICIARY_FAIL_MSG : $errorMsg;
                            $responseObj = new stdClass();
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->ResponseCode = ErrorCodes::ERROR_DEACTIVE_BENEFICIARY_FAIL_CODE;
                            $responseObj->ResponseMessage = $errorMsg;
                        }
                        return $responseObj;
                        //
                    } else {
                        return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_BENEFICIARY_CODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_BENEFICIARY_CODE);
                    }
                } else {
                    return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE);
                }
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_DEACTIVE_BENEFICIARY_FAIL_CODE;
                    $message = ErrorCodes::ERROR_DEACTIVE_BENEFICIARY_FAIL_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
              return self::Exception($message,$code);
            }
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $code = $e->getCode();
            $message = $e->getMessage();
            if( (empty($code) ) || (empty($message)) ) {
                $code = ErrorCodes::ERROR_DEACTIVE_BENEFICIARY_FAIL_CODE;
                $message = ErrorCodes::ERROR_DEACTIVE_BENEFICIARY_FAIL_MSG;
            }
            $this->_soapServer->_getLogger()->__setException($message);
            return self::Exception($message,$code);
        }
    }

    
    public function RemittanceTransactionRequest($obj) {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);

            if( !isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
             }
             
            //	Amount valid check  
            if ((string) $resp->Amount != '') {
                if (!is_numeric((string) trim($resp->Amount))) {
                   throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_CODE);
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_CODE);
            }

            Validator_Kotak::productcodeValidation((string) trim($resp->ProductCode), $this->getProductConstant());
            Validator_Kotak::txnrefnoValidation((string) trim($resp->TransactionRefNo));
            //Validator_Kotak_Customer::transactionNarrationValidation((string) trim($resp->Narration));
            //Validator_Kotak_Beneficiary::remitterwalletcodeValidation((string) trim($resp->WalletCode));
            Validator_Kotak_Beneficiary::remittancetypeValidation((string) trim($resp->RemittanceType));
            Validator_Kotak_Beneficiary::beneCodeValidation((string) trim($resp->BeneficiaryCode));

            // Check OTP
            $otp = (string) trim($resp->OTP);
            if ($otp != '') {
                if (strlen($otp) != 6 || !(ctype_digit($otp))) {
                   throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
                }
            }
//            else{
//                   throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
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
            // Check Remitter Flag
            $remitterflag = (string) trim($resp->RemitterFlag);
            $remitterflag = strtolower($remitterflag);

            if ($remitterflag != '') {
                if (strlen($remitterflag) > 1 || ( $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_MOB) && $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER) )) {
                     throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE);
                }else{
                    if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                        Validator_Kotak::mobileValidation((string) trim($resp->RemitterCode));
                    } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        Validator_Kotak_Customer::partnerRefnoValidation((string) trim($resp->RemitterCode));
                  }
                }
            } else {
               throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE);
            }

            try {
                $refObject = new Reference();
                $object = new Remit_Kotak_Remitter();
                $beneficiaryModel = new Remit_Kotak_Beneficiary();
                $param ['product_id'] = (string) trim($resp->ProductCode);
                $baseTxn = new BaseTxn();
                $ackno = $baseTxn->generateTxncode();
                if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOB)) {
                    $param['mobile'] = (string) trim($resp->RemitterCode);
                } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                    $param['partner_ref_no'] = (string) trim($resp->RemitterCode);
                }

                $param['status'] = STATUS_ACTIVE;
                $custInfo = $object->getRemitterDetails($param);

                if ($custInfo == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                }
                
                if ($otp != '') {// Check OTP only if it is there
                $bankKotak = \App_DI_Definition_Bank::getInstance(BANK_KOTAK);
                $bankKotakUnicode = $bankKotak->bank->unicode;
                $checkStaticOTP = Util::sendStaticOTP($custInfo['id'],$bankKotakUnicode);
                
              
               if((string) trim($resp->OTP) != $checkStaticOTP) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::OTP_INVALID_RESPONSE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::OTP_INVALID_RESPONSE_MSG;
                   
                    return $responseObj;
                }
                } 
                //active status
                $bene_param['status'] = STATUS_ACTIVE;
                $bene_param['remitter_id'] = $custInfo['id'];
                $bene_param['bene_code'] = (string) trim($resp->BeneficiaryCode);
                $beneInfo = $beneficiaryModel->getBeneInfoRow($bene_param);
                if(!empty($beneInfo)) {
                    
                    
                    // Remittance
                    $remittancerequest = new Remit_Kotak_Remittancerequest();
                    
                    //Check Duplicate Transaction Referance Number
                    
                   $respTransNum = $remittancerequest->checkDuplicateTransRefNum(array(
                    'product_id' => (string) trim($resp->ProductCode),
                    'txnrefnum' => (string) trim($resp->TransactionRefNo),
                    'agent_id' =>  $this->getAgentConstant() 
                    ));
                    if ($respTransNum == TRUE) {
                     return self::Exception(ErrorCodes::ERROR_EDIGITAL_TRAN_REF_NO_USED_MSG, ErrorCodes::ERROR_EDIGITAL_TRAN_REF_NO_USED_CODE);
                     }
                    
                     /*
                      * Agent Fee Plan
                      */
                    $remittanceFee = 0;
                    $remittanceServiceTax = 0;
                    $productID = (string) trim($resp->ProductCode);
                    $feeplan = new FeePlan();
                    $feeArr = $feeplan->getRemitterFee($productID, $this->getAgentConstant());
                    if (!empty($feeArr)) {
                    $remittanceAmount = (string) trim($resp->Amount); 
                    $remittanceAmount = Util::convertToRupee($remittanceAmount);
        
                    $feeAmount = '0.00';
                        //Fees Check
                        
                        foreach ($feeArr as $val) {
                            if ($val['typecode'] == TXNTYPE_REMITTANCE_FEE) {
                                $val['amount'] = $remittanceAmount;
                                $val['return_type'] = TYPE_FEE;
                                $feeAmount = Util::calculateRoundedFee($val);
                              //  App_Logger::log($fee, Zend_Log::ERR);
                                break;
                            }
                        }
                        
                        $feeComponent = Util::getFeeComponents($feeAmount);
                        $remittanceFee = isset($feeComponent['partialFee']) ? $feeComponent['partialFee'] : 0;
                        $remittanceServiceTax = isset($feeComponent['serviceTax']) ? $feeComponent['serviceTax'] : 0;
                      
                    } 
                    $remittanceData['amount'] = (string) trim($resp->Amount);
                    $remittanceData['fund_holder'] = REMIT_FUND_HOLDER_REMITTER;
                    $remittanceData['agent_id'] = $this->getAgentConstant();
                    $remittanceData['remitter_id'] = $custInfo['id'];
                    $remittanceData['beneficiary_id'] = $beneInfo['id'];
                    $remittanceData['txnrefnum'] = (string) trim($resp->TransactionRefNo);
                    $remittanceData['ops_id'] = 0;
                    $remittanceData['product_id'] = $productID;
                    $remittanceData['date_created'] = new Zend_Db_Expr('NOW()');
                    $remittanceData['fee'] = $remittanceFee;
                    $remittanceData['service_tax'] = $remittanceServiceTax;
                    $remittanceData['status'] = STATUS_INCOMPLETE;
                    $remittanceData['sender_msg'] = (string) trim($resp->Narration);
                    $remittanceData['wallet_code'] = (string) trim($resp->WalletCode);
                    $remittanceData['otp'] = $custInfo['otp'];
                    $remittanceData['date_otp'] = new Zend_Db_Expr('NOW()');
                    
                    $flg = $remittancerequest->remittanceTransaction($remittanceData,$custInfo,$beneInfo);
                    
//                    $upadteOTP = $refObject->updateCustomerOTPAPI(array(
//                        'request_type' => 'I',
//                        'id' => $responseOTP['id'],
//                    ));
                    
                    //var_dump($flg); exit;
                   
                    
                    if ($flg == FALSE) {
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                        $responseObj->ResponseCode = ErrorCodes::ERROR_REMITTANCE_TRANSACTION_FAILURE_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_REMITTANCE_TRANSACTION_FAILURE_MSG;
                        return $responseObj;
                    }elseif($flg['status']==TRANSACTION_SUCCESSFUL){
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                        $responseObj->AckNo = $flg['txncode'];
                        $responseObj->ResponseCode = self::REMITTANCE_TRANSACTION_SUCCESS_CODE;
                        $responseObj->ResponseMessage = self::REMITTANCE_TRANSACTION_SUCCESS_MSG;
                        return $responseObj;
                    }elseif($flg['status']==TRANSACTION_NORESPONSE){
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                        $responseObj->AckNo = $flg['txncode'];
                        $responseObj->ResponseCode = ErrorCodes::REMITTANCE_TRANSACTION_NO_RESPONSE_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::REMITTANCE_TRANSACTION_NO_RESPONSE_MSG;
                        return $responseObj;
                    }elseif($flg['status']==TRANSACTION_FAILED){
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                        $responseObj->AckNo = $flg['txncode'];
                        $responseObj->ResponseCode = ErrorCodes::ERROR_REMITTANCE_TRANSACTION_FAILURE_CODE;
                        $responseObj->ResponseMessage = $flg['final_response'];
                        return $responseObj;
                    }elseif($flg['status']==TRANSACTION_UNPROCESSED){
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                        $responseObj->AckNo = $flg['txncode'];
                        $responseObj->ResponseCode = ErrorCodes::ERROR_REMITTANCE_TRANSACTION_FAILURE_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_REMITTANCE_TRANSACTION_FAILURE_MSG;
                        return $responseObj;
                    }
                } else {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_BENEFICIARY_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_BENEFICIARY_CODE_MSG;
                    return $responseObj;
                }
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_REMITTANCE_TRANSACTION_FAILURE_CODE;
                    $message = ErrorCodes::ERROR_REMITTANCE_TRANSACTION_FAILURE_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
               return self::Exception($message,$code);
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_REMITTANCE_TRANSACTION_FAILURE_CODE;
                    $message = ErrorCodes::ERROR_REMITTANCE_TRANSACTION_FAILURE_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message,  $code);
        }
    }
    
    public function QueryBeneficiaryListRequest() {//Do not add comments for method summary

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);

            /*
             * Login Validation
             */
            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
                  return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }

            /*
             * Product Code Validation
             */
            Validator_Kotak::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());


            /*
             * Query Reference Numver Validation
             */
            if((string) trim($resp->QueryReqNo) !=''){
            Validator_Kotak::queryrefnoValidation((string) trim($resp->QueryReqNo));
            }
           
            // Check Remitter Flag
            $remitterflag = (string) trim($resp->RemitterFlag);
            $remitterflag = strtolower($remitterflag);

            if ($remitterflag != '') {
                if (strlen($remitterflag) > 1 || ( $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE) && $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER) )) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE);   
                }else{
                    if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                        Validator_Kotak::mobileValidation((string) trim($resp->RemitterCode));
                    } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        Validator_Kotak_Customer::partnerRefnoValidation((string) trim($resp->RemitterCode));
                  }
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE);   
            }

            try {
                
                //$params['txnrefnum'] = (string) $strTansRefNo; //$resp->TransactionRefNo;
               // $params['QueryReqNo'] = (string) $strQueryReqNo; //$resp->PartnerRefNo;
                //$params['by_api_user_id'] = $this->_TP_ID;
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
                $obj = new Remit_Kotak_Remitter();
                $beneficiaryModel = new Remit_Kotak_Beneficiary();
                /*
                 * Getiing customer Detail  
                 */
                $remitterData = $obj->getRemitterDetails($params);
                //echo "<pre>"; print_r($remitterData); exit;
                /*
                 *  Featching Remitter Id from object relation
                 */
                if (!empty($remitterData)) {
                        $bene_param['status'] = STATUS_ACTIVE;
                        $bene_param['remitter_id'] = $remitterData['id'];
                        $beneInfo = $beneficiaryModel->getBeneInfo($bene_param);
                        if (!empty($beneInfo)) {
                           
                            $beneDetails =   Util::toArray($beneInfo);
                            
                            /*
                             * 
                             */
                            if (!empty($beneDetails)) {

                                $responseObj = new stdClass();
                                $responseObj->SessionID = (string) $resp->SessionID;
                                $responseObj->BeneficiaryCount =  count($beneDetails);
                                $responseObj->ResponseCode = self::QUERY_BENEFICIARY_LIST_SUCC_CODE;
                                $responseObj->ResponseMessage = self::QUERY_BENEFICIARY_LIST_SUCC_MSG;


                                $return_array = new ArrayObject();

                                foreach ($beneDetails as $transValue) {
                                    $beneDetail  = new stdClass();
                                    $beneDetail->BeneficiaryCode = $transValue['bene_code'];
                                    $beneDetail->Name = $transValue['name'];
                                    $beneDetail->BankName = $transValue['bank_name'];
                                    $beneDetail->BankIfscode = $transValue['ifsc_code'];
                                    $beneDetail->BankAccountNumber = $transValue['bank_account_number'];
                                    $bene_detail = new SoapVar($beneDetail, SOAP_ENC_OBJECT, null, null, 'BeneficiaryDetail');
                                    $return_array->append($bene_detail);
                                }

                                $responseObj->BeneficiaryDetails = new SoapVar($return_array, SOAP_ENC_OBJECT, NULL, NULL, 'BeneficiaryDetails');

                            } else {
                                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_BENEFICIARY_LIST_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_BENEFICIARY_LIST_CODE);
                              }
                        }
                        else {
                           return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_BENEFICIARY_LIST_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_BENEFICIARY_LIST_CODE);
                        }
                        return $responseObj;
                        //
                    }else {
                    return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE);
                }
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_QUERY_BENEFICIARY_LIST_FAIL_CODE;
                    $message = ErrorCodes::ERROR_QUERY_BENEFICIARY_LIST_FAIL_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message,$code);
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_QUERY_BENEFICIARY_LIST_FAIL_CODE;
                    $message = ErrorCodes::ERROR_QUERY_BENEFICIARY_LIST_FAIL_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message,$code);
        }
    }
    
    public function RefundTransactionRequest() {//Do not add comments for method summary
        try { 
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);

            /*
             * Login Validation
             */
            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
                  return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }

            /*
             * Product Code Validation
             */
            Validator_Kotak::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());


            /*
             * Query Reference Numver Validation
             */
            //Validator_Kotak::queryrefnoValidation((string) trim($resp->QueryReqNo));
            Validator_Kotak::txnrefnoValidation((string) trim((string) $resp->TransactionRefNo));
            Validator_Kotak::originaltxnrefnoValidation((string) trim((string) $resp->OriginalTransactionRefNo));
            Validator_Kotak_Customer::originalAckNumValidation((string) trim($resp->OriginalAckNo));
            Validator_Kotak_Beneficiary::beneCodeValidation((string) trim($resp->BeneficiaryCode));
            Validator_Kotak_Beneficiary::remittancetypeValidation((string) trim($resp->RemittanceType));
            
            /*
             * OTP Validation
             */
            $otp = (string) trim($resp->OTP);
            if ($otp != '') {
                if (strlen($otp) != 6 || !(ctype_digit($otp))) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
            }


            
             //	Amount valid check  
            if ((string) trim($resp->Amount) != '') {
                if (!is_numeric((string) trim($resp->Amount))) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_CODE);
                }
            } else {
                   throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_CODE);
            }
            

           
            // Check Remitter Flag
            $remitterflag = (string) trim($resp->RemitterFlag);
            $remitterflag = strtolower($remitterflag);

            if ($remitterflag != '') {
                if (strlen($remitterflag) > 1 || ( $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE) && $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER) )) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE);   
                }else{
                    if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                        Validator_Kotak::mobileValidation((string) trim($resp->RemitterCode));
                        $param['mobile'] = (string) trim($resp->RemitterCode);
                    } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        Validator_Kotak_Customer::partnerRefnoValidation((string) trim($resp->RemitterCode));
                        $param['partner_ref_no'] = (string) trim($resp->RemitterCode);
                  }
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE);   
            }

            try {
                $productId = $param['product_id'] = (string) trim($resp->ProductCode);
                //$crnMaster
                $refObject = new Reference();   
                $objBaseTxn = new BaseTxn();
                
                $ackno = $objBaseTxn->generateTxncode();
                $objRemitStatusLog = new Remit_Kotak_Remittancestatuslog();
                $bank = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
                $m = new App\Messaging\Remit\Kotak\Agent();
                $object = new Remit_Kotak_Remitter();
                
                $custInfo = $object->getRemitterDetails($param);

                if ($custInfo == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                }
                $responseOTP = $refObject->verifyCustomerOTPAPI(array(
                    'request_type' => 'F',
                    'otp' => (string) trim($resp->OTP),
                    'mobile' => (string) $custInfo['mobile'],
                ));
                if ($responseOTP == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode =  ErrorCodes::OTP_INVALID_RESPONSE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::OTP_INVALID_RESPONSE_MSG;
                    return $responseObj;
                }
               
                $beneficiaryModel = new Remit_Kotak_Beneficiary();
                $bene_param['remitter_id'] = $custInfo['id'];
                $bene_param['bene_code'] = (string) trim($resp->BeneficiaryCode);
                $beneInfo = $beneficiaryModel->getBeneInfoRow($bene_param);

                if(empty($beneInfo)){
                    return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_BENEFICIARY_CODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_BENEFICIARY_CODE);
                }
                
                $remittancerequest = new Remit_Kotak_Remittancerequest();
                $remitRequest = $remittancerequest->getRemitterRequestsInfoByTxnCode($resp->OriginalAckNo, $resp->OriginalTransactionRefNo, $beneInfo['id']);
                
                if (empty($remitRequest)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_TXN_NOT_FOUND_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_TXN_NOT_FOUND_MSG;
                    return $responseObj;
                } elseif($remitRequest['status'] == STATUS_REFUND){
                     $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                  //  $responseObj->AckNo = $ackno;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_TXN_PROCESS_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_TXN_PROCESS_MSG;
                    return $responseObj;
                } elseif($remitRequest['status'] != STATUS_FAILURE){
                     $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                 //   $responseObj->AckNo = $ackno;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_REFUND_NOT_ALLOWED_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_REFUND_NOT_ALLOWED_MSG;
                    return $responseObj;
                } elseif((string) trim($resp->Amount) != Util::convertIntoPaisa($remitRequest['amount'])) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                 //   $responseObj->AckNo = $ackno;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_REFUND_AMOUNT_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_REFUND_AMOUNT_MSG;
                    return $responseObj;
                }
                
                if ($custInfo['id'] != $remitRequest['remitter_id']) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                 //   $responseObj->AckNo = $ackno;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_MSG;
                    return $responseObj;
                }
                
                $respTransNum = $remittancerequest->checkDeDupTransNum(array(
                    'txnrefnum' => (string) trim($resp->TransactionRefNo),
                    'agent_id' => $this->getAgentConstant(),
                    'product_id' => (string) trim($resp->ProductCode)
                ));
                
                if ($respTransNum == TRUE) {
                    return self::Exception(ErrorCodes::ERROR_EDIGITAL_TRAN_REF_NO_USED_MSG, ErrorCodes::ERROR_EDIGITAL_TRAN_REF_NO_USED_CODE);
                }
                
                
                $originalAgentId= $remitRequest['agent_id'];
                $agentId = $this->getAgentConstant();
                $agentUser = new AgentUser();
                $agentDetails = $agentUser->getClosedLoopAgentDetailsById($agentId);
                $originalAgentDetails = $agentUser->getClosedLoopAgentDetailsById( $originalAgentId);   
                $groupCheck = true;        
        
                if(isset($agentDetails['group']))
                {     
                    if(isset($originalAgentDetails['group']))
                    {  
                        if(strcasecmp($agentDetails['group'],$originalAgentDetails['group'])==0)
                        {  
                             $groupCheck = true; 
                        }
                        else
                        {  
                             $groupCheck = false; 
                        }
                    }
                    else
                    {  
                         $groupCheck = false;
                    }
                }
                else
                {  
                    if(isset($originalAgentDetails['group']))
                    {                             
                        $groupCheck = false;     
                    }
                    else
                    {  
                        $groupCheck = true;
                    }   
                } 
               

                 if (!($groupCheck)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_TRANSACTION_NETWORK_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_TRANSACTION_NETWORK_MSG;
                    return $responseObj;
                 }
                
                
                $refundAmt = $remitRequest['amount'] + $remitRequest['fee'] + $remitRequest['service_tax'];
                $reversalRemitFee = $remitRequest['fee'];
                $reversalRemitSt = $remitRequest['service_tax'];
                $calculatedFee = 0;
                $serviceTax = 0;
                
                
                $remitRefundParams = array(
                    'remit_request_id' => $remitRequest['id'],
                    'remitter_id' => $custInfo['id'],
                    'agent_id' => $agentId,
                    'product_id' => $param['product_id'],
                    'amount' => $refundAmt,
                    'fee_amt' => $calculatedFee,
                    'service_tax' => $serviceTax,
                    'reversal_fee_amt' => $reversalRemitFee,
                    'reversal_service_tax' => $reversalRemitSt,
                    'bank_unicode' => $bank->bank->unicode,
                );
                
                        $txnCode = $objBaseTxn->remitRefund($remitRefundParams); //true;
               
                
                if($txnCode){
                    $refundData = array('is_complete' => FLAG_YES,
                        'status' => STATUS_REFUND,
                        'fund_holder' => REMIT_FUND_HOLDER_REMITTER
                    );
                    $res = $remittancerequest->updateReq($remitRequest['id'], $refundData);
                    $refundAmt = $refundAmt - $reversalRemitFee - $reversalRemitSt;
                    $remitRefundData = array(
                        'remitter_id' => $custInfo['id'],
                        'remittance_request_id' => $remitRequest['id'],
                        'agent_id' => $agentId,
                        'product_id' => $productId,
                        'amount' => $refundAmt,
                        'fee' => $calculatedFee,
                        'service_tax' => $serviceTax,
                        'reversal_fee' => $reversalRemitFee,
                        'reversal_service_tax' => $reversalRemitSt,
                        'txn_code' => $txnCode,
                        'txnrefnum' => (string) $resp->TransactionRefNo,
                        'status' => STATUS_SUCCESS,
                        'date_created' => date('Y-m-d H:i:s')
                    );
                     
                    $res = $remittancerequest->addRemittanceRefund($remitRefundData);
                    //$smsArr = array('amount' => $session->refundable_amount,
                    //    'nick_name' => $remitRequest['nick_name'],
                    //    'remitter_phone' => $remitRequest['mobile']);
                    //$m->kotakRefundSmsRemitter($smsArr);
                    $logData = array(
                        'remittance_request_id' => $remitRequest['id'],
                        'status_old' => FLAG_FAILURE,
                        'status_new' => STATUS_REFUND,
                        'by_remitter_id' => $custInfo['id'],
                        'by_agent_id' => $agentId,
                        'date_created' => date('Y-m-d H:i:s')
                    );
                   
                    $objRemitStatusLog->addStatus($logData);
                     $upadteOTP = $refObject->updateCustomerOTPAPI(array(
                        'request_type' => 'F',
                        'id' => $responseOTP['id'],
                ));
                
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = $txnCode;
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->ResponseCode = self::REMITTANCE_TRANSACTION_REQUEST_REFUND_SUCCESS_CODE;
                    $responseObj->ResponseMessage = self::REMITTANCE_TRANSACTION_REQUEST_REFUND_SUCCESS_MSG;
                    
                    
                    // Send SMS  
                        $params_sms = array(
                            'product_id' => $productId,
                            'mobile' => $custInfo['mobile'],
                            'bene_name' => $beneInfo['name'],
                            'ref_num'=> $txnCode,
                            'amount' => $refundAmt
                        );
                        $object->generateSMSDetails($params_sms, $smsType = REFUND_TRANSACTION_API);
                    
                    return $responseObj;
                }else{
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                 //   $responseObj->AckNo = $ackno;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_MSG;
                    return $responseObj;
                }
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                    $message = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message,$code);
            }
        } catch (Exception $e) {
             App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                    $message = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message,$code);
        }
    }
    
     public function QueryRefundRequest() {//Do not add comments for method summary
        try { 
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);

            /*
             * Login Validation
             */
            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
                  return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }

            /*
             * Query Reference Numver Validation
             */
            Validator_Kotak::queryrefnoValidation((string) trim($resp->QueryReqNo));
            Validator_Kotak::txnrefnoValidation((string) trim($resp->TransactionRefNo));
            
            if(( (string) trim($resp->QueryReqNo)=='') ){
                Validator_Kotak::orgtxnrefnoValidation((string) trim($resp->OriginalTransactionRefNo));
            }
            
            try {
                $object = new Remit_Kotak_Remitter();
                $beneficiaryModel = new Remit_Kotak_Beneficiary();

                $QueryReqNo = (string) trim($resp->QueryReqNo);
                $OriginalTransactionRefNo = (string) trim($resp->OriginalTransactionRefNo);
                
                if(!empty($QueryReqNo)){
                    $remittancerequest = new Remit_Kotak_Remittancerequest();
                    $remitRequest = $remittancerequest->getRemitterRefundInfoByTxnCode($QueryReqNo);
                } elseif(!empty($OriginalTransactionRefNo)) {
                    $remittancerequest = new Remit_Kotak_Remittancerequest();
                    $remitRequest = $remittancerequest->getRemitterRequestsInfoByTxnCode($OriginalTransactionRefNo);
                }
                
                if (empty($remitRequest)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_REFUND_NOT_ALLOWED_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_REFUND_NOT_ALLOWED_MSG;
                    return $responseObj;
                }
                
                $custInfo = $object->getRemitterDetails(array('id'=>$remitRequest['remitter_id']));
                
                if ($custInfo == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                }
                $bene_param =array();
                $bene_param['remitter_id'] = $remitRequest['remitter_id'];
                $bene_param['id'] = $remitRequest['beneficiary_id'];
                $beneInfo = $beneficiaryModel->getBeneInfoRow($bene_param);
                if (empty($beneInfo)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                }
                
                $responseObj = new stdClass();
                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->TransactionRefNo = (string) $resp->TransactionRefNo;
                $responseObj->OriginalTransactionRefNo = (string) $resp->OriginalTransactionRefNo;
                $responseObj->RemitterCode = $custInfo['txn_code'];
                $responseObj->BeneficiaryCode = $beneInfo['bene_code'];
                $responseObj->Amount = Util::filterAmount($remitRequest['amount']);
                $responseObj->RemittanceType = TXN_IMPS;
                $responseObj->TransactionStatus = $remitRequest['status'];
                $responseObj->ResponseCode = self::QUERY_EDIGITAL_REFUND_TXN_CODE;
                $responseObj->ResponseMessage = self::QUERY_EDIGITAL_REFUND_TXN_MSG;
                return $responseObj;
                
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                    $message = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message,$code);
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                    $message = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message,$code);
        }
    }

}
