<?php

/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_EDigital_Ratnakar extends App_ApiServer_Exchange_EDigital {

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
            
            Validator_Ratnakar::chkallParams($resp);
            /*
             * Product Code Validation
             */
            Validator_Ratnakar::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());

            
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
                Validator_Ratnakar::Filter2MOBValidation((string) trim($resp->Filler2));
                }else{
                  Validator_Ratnakar::mobileValidation((string) trim($resp->Mobile)); 
                }
            } elseif (strtolower(trim($resp->Filler1)) == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                 Validator_Ratnakar_Customer::partnerRefnoValidation((string) trim($resp->Filler2));
            }

            try {

                //$object = new CustomerTrack();
                $refObject = new Reference();
                $cardholderModel = new Corp_Ratnakar_Cardholders();
                $masterPurseModel = new MasterPurse();
                
                $baseTxn = new BaseTxn();
                $txnCode = $baseTxn->generateTxncode();
                // Check OTP
                $OTPrequest = TRUE; // Default OTP setting
                if($this->getOTPRequestConstant() == 'false'){
                     $OTPrequest = FALSE;
                 }
                  if (!$OTPrequest) {
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = ErrorCodes::GENERATE_OTP_FAILED_RESPONSE_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::GENERATE_OTP_FAILED_RESPONSE_MSG;
                        return $responseObj;
                    }
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
                     $dupli_params = array(); 
                    if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                         if((string) trim($resp->Filler2) !='' ){
                            $param['mobile'] = (string) trim($resp->Filler2);
                            
                         }else{
                            $param['mobile'] = (string) trim($resp->Mobile);     
                         }
                    } else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                       // $param['partner_ref_no'] = (string) $resp->Filler2;
                         
                         $param['mobile'] = (string) trim($resp->Mobile);
                    }else{
                        $param['mobile'] = (string) trim($resp->Mobile);
                    
                    }
                      Validator_Ratnakar::mobileValidation($param['mobile']); 
                    
                    $respMobile = $cardholderModel->checkDuplicateMobile(array(
                    'product_id' => $param['product_id'],
                    'mobile' => $param['mobile'],
                ));
                //if ($respMobile == TRUE) {
                if (!empty($respMobile['id'])) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_MOBILE_USED_MSG, ErrorCodes::ERROR_EDIGITAL_MOBILE_USED_CODE);
                }
                    $agent = $this->getAgentConstant(); 
                    $gInfo = $refObject->generateRatOTPAPI(array(
                        'type' => (string) trim($resp->RequestType),
                        'mobile' => $param['mobile'],
                        'user_type' => 'API',
                        'user_id' => $agent,
                        'ref_id' => '',
                        'ack_no' => $ackNo,
                        'product_id' => (string) trim($resp->ProductCode),
                    ));
                } else if (strtolower(trim($resp->RequestType)) == 'e') {
                    
                    if (strtolower(trim($resp->Filler1)) == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                    Validator_Ratnakar::Filter2MOBValidation((string) trim($resp->Filler2));
                    } elseif (strtolower(trim($resp->Filler1)) == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                    Validator_Ratnakar_Customer::partnerRefnoValidation((string) trim($resp->Filler2));
                    }
                    
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
                        $param['mobile'] = (string) trim($resp->Filler2);
                    } else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        $param['partner_ref_no'] = (string) trim($resp->Filler2);
                    }
                                
                    $custInfo = $cardholderModel->getCardholderInfo($param);
                    if (empty($custInfo)) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                        return $responseObj;
                    }elseif ($custInfo['cardholder_status'] == STATUS_BLOCKED) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG;
                        return $responseObj;
                    }else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        $param['status'] = STATUS_ACTIVE;
                        $custInfo = $cardholderModel->getCardholderInfo($param);
                        if (empty($custInfo)) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                        return $responseObj;
                        }else{
                           $param['mobile'] = $custInfo['mobile'];  
                        }
                    }
                    $customerId = $custInfo['id'];
                    if((string) trim($resp->Mobile) !='' ){
                        Validator_Ratnakar::mobileValidation((string) trim($resp->Mobile));
                            $bankObject   = new Banks();
                                $bankInfo = $bankObject->getBankidByProductid((string) trim($resp->ProductCode));
                                if(!empty($bankInfo) ){
                                $param['bank_id'] = $bankInfo['bank_id'];  
                                    $respMobile = $cardholderModel->checkEditDuplicateMobile($param,(string) trim($resp->Mobile));
                                    if ($respMobile == TRUE) {
                                        throw new Exception(ErrorCodes::ERROR_EDIGITAL_MOBILE_USED_MSG, ErrorCodes::ERROR_EDIGITAL_MOBILE_USED_CODE);
                                    }
                                }else{
                                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);   
                                }
                        $param['sms_mobile'] = (string) trim($resp->Mobile);
                         }else{
                              $param['sms_mobile'] = $param['mobile'];
                         }
                    $agent = $this->getAgentConstant();
                    $gInfo = $refObject->generateRatOTPAPI(array(
                        'type' => (string) trim($resp->RequestType),
                        'mobile' => $param['sms_mobile'],
                        'user_type' => 'API',
                        'user_id' => $agent,
                        'ref_id' => $customerId,
                        'ack_no' => $ackNo,
                        'product_id' => (string) trim($resp->ProductCode)
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
                    
                    $masterPurseDetails = $masterPurseModel->getPurseInfo($param);
                    
                    if(empty($masterPurseDetails)){
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = ErrorCodes::BENEFICIARY_REGISTRATION_FAILED_RESPONSE_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::BENEFICIARY_REGISTRATION_FAILED_RESPONSE_MSG;
                        return $responseObj;
                    }
                    
                    $customerInfo = $cardholderModel->getCustomerDetails($param);

                    if (empty($customerInfo)) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                        return $responseObj;
                    }elseif ($customerInfo['status'] == STATUS_BLOCKED) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG;
                        return $responseObj;
                    }else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                           $param['status'] = STATUS_ACTIVE;
                           $custInfo = $cardholderModel->getCardholderInfo($param);
                            if (empty($custInfo)) {
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->AckNo = $txnCode;
                            $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                            $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                            return $responseObj;
                            }else{
                               $param['mobile'] = $custInfo['mobile'];  
                            }
                        }
                    
                    $agent = $this->getAgentConstant();
                    $gInfo = $refObject->generateRatOTPAPI(array(
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
                    
                    $customerInfo = $cardholderModel->getCustomerDetails($param);

                    if (empty($customerInfo)) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                        return $responseObj;
                    }elseif ($customerInfo['status'] == STATUS_BLOCKED) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG;
                        return $responseObj;
                    }else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                           $param['status'] = STATUS_ACTIVE;
                           $custInfo = $cardholderModel->getCardholderInfo($param);
                            if (empty($custInfo)) {
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->AckNo = $txnCode;
                            $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                            $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                            return $responseObj;
                            }else{
                               $param['mobile'] = $custInfo['mobile'];  
                            }
                        }
                    
                    $agent = $this->getAgentConstant();
                    $gInfo = $refObject->generateRatOTPAPI(array(
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
                    
                    $customerInfo = $cardholderModel->getCustomerDetails($param);

                    if (empty($customerInfo)) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                        return $responseObj;
                    }elseif ($customerInfo['status'] == STATUS_BLOCKED) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG;
                        return $responseObj;
                    }else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                           $param['status'] = STATUS_ACTIVE;
                           $custInfo = $cardholderModel->getCardholderInfo($param);
                            if (empty($custInfo)) {
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->AckNo = $txnCode;
                            $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                            $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                            return $responseObj;
                            }else{
                               $param['mobile'] = $custInfo['mobile'];  
                            }
                        }
                    

                    $gInfo = $refObject->generateRatOTPAPI(array(
                        'product_id' => (string) trim($resp->ProductCode),
                        'customer_id' => (string) $customerInfo['id'],
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
                    
                    $masterPurseDetails = $masterPurseModel->getPurseInfo($param);
                    if(empty($masterPurseDetails)){
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_REMITTANCE_FEATURE_DISABLED_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_REMITTANCE_FEATURE_DISABLED_MSG;
                        return $responseObj;
                    }

                    $customerInfo = $cardholderModel->getCustomerDetails($param);

                    if (empty($customerInfo)) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                        return $responseObj;
                    }elseif ($customerInfo['status'] == STATUS_BLOCKED) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG;
                        return $responseObj;
                    }else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                           $param['status'] = STATUS_ACTIVE;
                           $custInfo = $cardholderModel->getCardholderInfo($param);
                            if (empty($custInfo)) {
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->AckNo = $txnCode;
                            $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                            $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                            return $responseObj;
                            }else{
                               $param['mobile'] = $custInfo['mobile'];  
                            }
                        }

                    $gInfo = $refObject->generateRatOTPAPI(array(
                        'product_id' => (string) trim($resp->ProductCode),
                        'customer_id' => (string) $customerInfo['id'],
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

                    $customerInfo = $cardholderModel->getCustomerDetails($param);

                    if (empty($customerInfo)) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                        return $responseObj;
                    }elseif ($customerInfo['status'] == STATUS_BLOCKED) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG;
                        return $responseObj;
                    }else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                           $param['status'] = STATUS_ACTIVE;
                           $custInfo = $cardholderModel->getCardholderInfo($param);
                            if (empty($custInfo)) {
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->AckNo = $txnCode;
                            $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                            $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                            return $responseObj;
                            }else{
                               $param['mobile'] = $custInfo['mobile'];  
                            }
                        }

                    $gInfo = $refObject->generateRatOTPAPI(array(
                        'product_id' => (string) trim($resp->ProductCode),
                        'customer_id' => (string) $customerInfo['id'],
                        'type' => (string) trim($resp->RequestType),
                        'mobile' => $param['mobile'],
                        'user_type' => BY_CUSTOMER,
                        'user_id' => (string) $customerInfo['id'],
                        'amount' => Util::convertToPaisa(trim($resp->Narration)),
                        'mode' => TXN_MODE_CR,
                    ));
                } elseif (strtolower(trim($resp->RequestType)) == 'd') {
                    
                } elseif (strtolower(trim($resp->RequestType)) == 'u') {
                    /*wallet unblock*/
                    if (strtolower(trim($resp->Filler1)) == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                        Validator_Ratnakar::Filter2MOBValidation((string) trim($resp->Filler2));
                    } elseif (strtolower(trim($resp->Filler1)) == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        Validator_Ratnakar_Customer::partnerRefnoValidation((string) trim($resp->Filler2));
                    }
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
                    if($resp->Filler2 !='') {
                        if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                            $param['mobile'] = (string) trim($resp->Filler2);
                        } else if ($uniqueCodeFlag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                            $param['partner_ref_no'] = (string) trim($resp->Filler2);
                        }
                    } else {
                        Validator_Ratnakar::mobileValidation((string) trim($resp->Mobile));
                        $param['mobile'] = (string) trim($resp->Mobile);
                    }
                    
                    $custInfo = $cardholderModel->getCardholderInfo($param); 
                    if (empty($custInfo)) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                        return $responseObj;
                    } else if($custInfo['cardholder_status'] != STATUS_BLOCKED){
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_ALREADY_ACTIVE_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_ALREADY_ACTIVE_MSG;
                        return $responseObj;
                    } else {
                        $param['mobile'] = $custInfo['mobile'];
                        $customerId = $custInfo['id'];
                    }   
                    
                    $param['sms_mobile'] = $param['mobile'];
                    $agent = $this->getAgentConstant();
                    $gInfo = $refObject->generateRatOTPAPI(array(
                        'type' => (string) trim($resp->RequestType),
                        'mobile' => $param['sms_mobile'],
                        'user_type' => 'API',
                        'user_id' => $agent,
                        'ref_id' => $customerId,
                        'ack_no' => $ackNo,
                        'product_id' => (string) trim($resp->ProductCode)
                    ));          
                }
                //echo $gInfo.'**';exit;
                $responseObj = new stdClass();
                if ($gInfo == FALSE) {
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = $txnCode;
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
                $baseTxn = new BaseTxn();
                $txnCode = $baseTxn->generateTxncode();
                $this->_soapServer->_getLogger()->__setException($e->getMessage());
                App_Logger::log($e, Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::OTP_FAILED_RESPONSE_CODE;
                    $message = ErrorCodes::OTP_FAILED_RESPONSE_MSG;
                }
                
                $responseObj = new stdClass();
                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->AckNo = $txnCode;
                $responseObj->ResponseCode = $code;
                $responseObj->ResponseMessage = $message;
                return $responseObj;
            }
        } catch (Exception $e) {
            $baseTxn = new BaseTxn();
            $txnCode = $baseTxn->generateTxncode();
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
            $responseObj->AckNo = $txnCode;
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
            $cardNum = '';
            /*
             * Login Validation
             */
            $sessionID = (string)trim($resp->SessionID);
            if (!isset($sessionID) || !$this->isLogin($sessionID)) {
               return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }
            Validator_Ratnakar::chkallParams($resp);
            
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
           // $dob = '0000-00-00';
           // Validator_Ratnakar_Customer::dateValidation((string) trim($resp->DateOfBirth));
            
           /// $dob = (string) trim($resp->DateOfBirth);
            
            /*
             * Gender Validation
             */
            if((string) trim($resp->Gender) != '') {
                Validator_Ratnakar::genderValidation((string) trim($resp->Gender));
            }
            
            if ((string) trim($resp->DateOfBirth) != '') {
            Validator_Ratnakar_Customer::dateValidation((string) trim($resp->DateOfBirth));
            Validator_Ratnakar_Customer::dobAgeValidation((string) trim($resp->DateOfBirth));
            $dob = (string) trim($resp->DateOfBirth);
             }else{
              $dob = '0000-00-00';   
             }

            /*
             * Mobile Validation
             */
            Validator_Ratnakar::mobileValidation((string) trim($resp->Mobile));

            /*
             * Email Validation
             */
            Validator_Ratnakar_Customer::emailValidation((string) trim($resp->Email));

            
            if( ($this->getProductConstant() == PRODUCT_CONST_RAT_CTY) || ($this->getProductConstant() == PRODUCT_CONST_RAT_HFCI)){
             Validator_Ratnakar::cardnoValidation((string) trim($resp->CardNumber)); 
             $cardNum = (string) trim($resp->CardNumber);
            }
            /*
             * Card Activation Type Validation
             */
            $isCardActivated = (string) trim($resp->IsCardActivated);
            $isCardActivated = strtolower($isCardActivated);
          //  $isCardActivated = strtolower(self::CARD_ACTIVATION_TYPE_NO);
 
            if ($isCardActivated != '') {
                if (strlen($isCardActivated) > 1 || ( $isCardActivated != strtolower(self::CARD_ACTIVATION_TYPE_YES) && $isCardActivated != strtolower(self::CARD_ACTIVATION_TYPE_NO) )) {
                    return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_ACTIVATED_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_ACTIVATED_CODE);
                }
            } else {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_ACTIVATED_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_ACTIVATED_CODE);
            }

            /*
             * Card Dispatch Validation
             */
            $isCardDispatch = (string) trim($resp->IsCardDispatch);
            $isCardDispatch = strtolower($isCardDispatch);
           // $isCardDispatch = strtolower(self::CARD_DISPATCH_TYPE_NO);
           
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
            
             $OTPrequest = TRUE; // Default OTP setting
                if($this->getOTPRequestConstant() == 'false'){
                     $OTPrequest = FALSE;
                 }
            
            $otp = (string) trim($resp->Filler1);
            if($OTPrequest){
                if ($otp != '') {
                    if (strlen($otp) != 6 || !(ctype_digit($otp))) {
                        throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
                    }
                } else {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
                }
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
                     throw new Exception(ErrorCodes::ERROR_EDIGITAL_WRONG_NON_KYC_CUST_REG_MSG, ErrorCodes::ERROR_EDIGITAL_WRONG_NON_KYC_CUST_REG_CODE);
                }
                $customerType = TYPE_NONKYC;
                if ($isCardActivated == strtolower(self::CARD_ACTIVATION_TYPE_YES)) {
                    $cardActivationDate = date('Y-m-d h:i:s');
                }

                if ($isCardDispatch == strtolower(self::CARD_DISPATCH_TYPE_YES)) {
                    $cardDispatchDate = date('Y-m-d h:i:s');
                }
                

                //$crnMaster
                $refObject = new Reference();
                $baseTxn = new BaseTxn();
                $txnCode = $baseTxn->generateTxncode();
                $bankObject   = new Banks();
                
                if($OTPrequest){
                    $responseOTP = $refObject->verifyCustomerOTPAPI(array(
                        'request_type' => 'R',
                        'otp' => (string) trim($resp->Filler1),
                        'mobile' => (string) trim($resp->Mobile),
                    ));

                    if ($responseOTP == FALSE) {
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = ErrorCodes::OTP_INVALID_RESPONSE_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::OTP_INVALID_RESPONSE_MSG;
                        return $responseObj;
                    }
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
		$params['channel'] = CHANNEL_API; 
		
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
                //if ($respMobile == TRUE) {
                if (!empty($respMobile['id'])) {
                    $txnCode = $respMobile['txncode'];
                    return self::Exception(ErrorCodes::ERROR_EDIGITAL_MOBILE_USED_MSG, ErrorCodes::ERROR_EDIGITAL_MOBILE_USED_CODE,$txnCode);
                }
                /*
                 * checkDuplicateMemberID : Checking duplicate member id for customer
                 */
                $respMemberID = $obj->checkDuplicatePartnerRefNo(array(
                    'product_id' => $params['ProductId'],
                    'partner_ref_no' => $params['PartnerRefNo'],
                ));
                if (!empty($respMemberID['id'])) {
                    $txnCode = $respMemberID['txncode'];
                     return self::Exception(ErrorCodes::ERROR_EDIGITAL_PAR_USED_MSG, ErrorCodes::ERROR_EDIGITAL_PAR_USED_CODE,$txnCode);
                }
                
                 /*
                 * checkDuplicateTransNum : Checking duplicate Transaction Referance Number for customer
                 */
                $respTrans = $obj->checkDuplicateTransNum(array(
                    'product_id' => $params['ProductId'],
                    'txnrefnum' => $strTansRefNo,
                ));
                if (!empty($respTrans['id'])) {
                    $txnCode = $respTrans['txncode'];
                    return self::Exception(ErrorCodes::ERROR_EDIGITAL_TRAN_REF_NO_USED_MSG, ErrorCodes::ERROR_EDIGITAL_TRAN_REF_NO_USED_CODE,$txnCode);
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
                  if(($params['manageType'] == CORPORATE_MANAGE_TYPE)|| ($this->getProductConstant() == PRODUCT_CONST_RAT_HFCI)){
                // get CRN info
                    $crnMaster = new CRNMaster();
                    $crnInfo = $crnMaster->getCRNInfo($params['CardNumber'], $params['CardPackId'], $params['MemberId']);
                    if(!empty($crnInfo)){
                    // update status CRN
                    $crnMaster->updateStatusById(array('status' => STATUS_USED), $crnInfo->id);
                    }else{
                      throw new Exception(ErrorCodes::ERROR_INVALID_CARD_FAILURE_MSG, ErrorCodes::ERROR_INVALID_CARD_FAILURE_CODE); 
                    }
                    
               }
                
                $responseCustID = $obj->addCustomerAPI($params);
                if($responseCustID > 0){
                $response = $obj->mapCardholderToRemitter($remit_params, $responseCustID);
                }else{
                    $errorMsg = $obj->getError();
                    $errorMsg = empty($errorMsg) ? ErrorCodes::ERROR_CUSTOMER_REGISTRATION_FAIL_MSG : $errorMsg;
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->PartnerRefNo = (string) trim($resp->PartnerRefNo);
                    $responseObj->AckNo = $txnCode;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_CUSTOMER_REGISTRATION_FAIL_CODE;
                    $responseObj->ResponseMessage = $errorMsg;   
                }
                //  $txnCode = $obj->getTxncode();
              
                if (($response == TRUE) && ($responseCustID > 0) ){
                 
//                    if ($smsflag == strtolower('Y')) {
//                    // Send SMS   
//                    $params = array(
//                        'product_id' => (string) trim($resp->ProductCode),
//                        'mobile' => (string) trim($resp->Mobile),
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
                    if($OTPrequest){
                        $upadteOTP = $refObject->updateCustomerOTPAPI(array(
                            'request_type' => 'R',
                            'id' => $responseOTP['id'],
                        ));
                     }
                } else {
                    $errorMsg = $obj->getError();
                    $errorMsg = empty($errorMsg) ? ErrorCodes::ERROR_CUSTOMER_REGISTRATION_FAIL_MSG : $errorMsg;
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->PartnerRefNo = (string) trim($resp->PartnerRefNo);
                    $responseObj->AckNo = $txnCode;
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
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $code = (empty($code)) ? self::UNABLE_TO_PROCESS_CODE : $code;
                $message = $e->getMessage();
                
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_CUSTOMER_REGISTRATION_FAIL_MSG;
                    $message = ErrorCodes::ERROR_CUSTOMER_REGISTRATION_FAIL_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
        }
    }
//
    public function BeneficiaryRegistrationRequest() {//Do not add comments for method summary
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);

            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
                 return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }
            Validator_Ratnakar::chkallParams($resp);
            Validator_Ratnakar::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());
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
            if(trim($resp->Filler1)  !=''){
                Validator_Ratnakar_Beneficiary::beneBankAccountTypeValidation((string) trim($resp->Filler1));
                $bankAccountType = strtolower((string) trim($resp->Filler1));
            }else{
                   $bankAccountType = SAVING_ACCOUNT_TYPE;
            }
            // Check OTP            
            $OTPrequest = TRUE; // Default OTP setting
                if($this->getOTPRequestConstant() == 'false'){
                     $OTPrequest = FALSE;
                 }
            $otp = (string) trim($resp->OTP);
            if($OTPrequest){
                if ($otp != '') {
                    if (strlen($otp) != 6 || !(ctype_digit($otp))) {
                         throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
                    }
                }else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
               } 
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
		/*
		    if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
			Validator_Ratnakar::mobileValidation((string) trim($resp->RemitterCode));
		    } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
			Validator_Ratnakar_Customer::partnerRefnoValidation((string) trim($resp->RemitterCode));
		    } */
		    Validator_Ratnakar::remitterCodeValidation($remitterflag,(string) trim($resp->RemitterCode));
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE);
            }
            // Bank details validation
            Validator_Ratnakar_Beneficiary::bankDetailsValidation(array(
                'ifsc_code' => (string) strtoupper(trim($resp->BankIfscode)),
                'bank_account_number' => (string) trim($resp->BankAccountNumber),
//                'bank_name' => (string) $resp->BankName,
//                'branch_name' => (string) $resp->BankBranch,
//                'branch_city' => (string) $resp->BankCity,
//                'branch_address' => (string) $resp->BankState,
            ));

            try {

                $cardholderModel = new Corp_Ratnakar_Cardholders();
                $beneficiaryModel = new Remit_Ratnakar_Beneficiary();
                $masterPurseDetails = new MasterPurse();

                // Get Customer details
                $param['product_id'] = (string) trim($resp->ProductCode);

                if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOB)) {
                    $param['mobile'] = (string) trim($resp->RemitterCode);
                } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                    $param['partner_ref_no'] = (string) trim($resp->RemitterCode);
                }
                
                $masterPurseDetails = $masterPurseDetails->getPurseInfo($param);
                if(empty($masterPurseDetails)){
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::BENEFICIARY_REGISTRATION_FAILED_RESPONSE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::BENEFICIARY_REGISTRATION_FAILED_RESPONSE_MSG;
                    return $responseObj;
                }
                
                // get Remitter id	 
                $custInfo = $cardholderModel->getCustomerDetails($param);
                 if($custInfo == FALSE){ 
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                } elseif($custInfo['status'] == STATUS_BLOCKED) {
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG;
                    return $responseObj;
                }
                
                $refObject = new Reference();
                $baseTxn = new BaseTxn();
                $queryRefNum = $baseTxn->generateTxncode();
                
                if($OTPrequest){
                    $responseOTP = $refObject->verifyCustomerOTPAPI(array(
                        'request_type' => 'B',
                        'otp' => (string) trim($resp->OTP),
                        'mobile' => (string) $custInfo['mobile'],
                    ));

                    if ($responseOTP == FALSE) {
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $queryRefNum;
                        $responseObj->ResponseCode = ErrorCodes::OTP_INVALID_RESPONSE_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::OTP_INVALID_RESPONSE_MSG;
                       return $responseObj;
                    }                                                       
                }
                $objectRelation = new ObjectRelations();
                $remitterId = $objectRelation->getToObjectInfo($custInfo['id'], RAT_MAPPER);

//
//                $txncode = new Benecode();
//                if ($txncode->generateTxncode()) {
//                    $paramsTxnCode = $txncode->getTxncode(); //Get Txncode
//                    $txncode->setUsedStatus(); //Mark Txncode as used
//                }
//               
              
                $params = array();
                $params['remitter_id'] = $remitterId['to_object_id'];
                $params['txnrefnum'] = (string) trim($resp->TransactionRefNo);
                $params['title'] = (string) trim($resp->Title);
                $params['name'] = (string) trim($resp->Name);
                $params['middle_name'] = (string) trim($resp->MiddleName);
                $params['last_name'] = (string) trim($resp->LastName);
//                $params['nick_name'] = (string) $resp->NickName;
                $params['ifsc_code'] = (string)strtoupper(trim($resp->BankIfscode));
                $params['bank_account_number'] = (string) trim($resp->BankAccountNumber);
                $params['bank_account_type'] = $bankAccountType;
    //                $params['bank_name'] = (string) $resp->BankName;
//                $params['branch_name'] = (string) $resp->BankBranch;
//                $params['branch_city'] = (string) $resp->BankCity;
//                $params['branch_address'] = (string) $resp->BankState;
//                $params['bank_account_type'] = (string) $resp->BankAccountType;
                $params['mobile'] = (string) trim($resp->Mobile);
//                $params['mobile2'] = (string) $resp->Mobile2;
                $params['email'] = (string) trim($resp->Email);
//                $params['landline'] = (string) $resp->Landline;
                $params['address_line1'] = (string) trim($resp->AddressLine1);
                $params['address_line2'] = (string) trim($resp->AddressLine2);

                $params['bank_account_type'] = $bankAccountType;  
                $params['bene_code'] = $paramsTxnCode;
//                $params['city'] = (string) $resp->City;
//                $params['state'] = (string) $resp->State;
//                $params['country'] = COUNTRY_CODE_INDIA;
//                $params['pincode'] = (string) $resp->Pincode;
//                $params['bene_code'] = $paramsTxnCode;

                $params['queryrefno'] = $queryRefNum;
                $params['by_agent_id'] = $this->getAgentConstant();
                $params['date_created'] =  new Zend_Db_Expr('NOW()');
                
                $bankObject   = new Banks();
                $bankInfo = $bankObject->getBankidByProductid((string) trim($resp->ProductCode));
                if(!empty($bankInfo) ){
                 $params['bank_id'] = $bankInfo['bank_id'];    
                }else{
                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);   
                }
               Validator_Ratnakar_Beneficiary::chkBeneAccountExists(array(
                    'remitter_id' => $remitterId['to_object_id'],
                    'ifsc_code' => $params['ifsc_code'],
                    'bank_account_number' => $params['bank_account_number']));
                
                Validator_Ratnakar_Beneficiary::chkTransRefNoExists(array(
                    'txnrefnum' => $params['txnrefnum'],
                    'bank_id' => $params['bank_id']));


                $beneCode = $beneficiaryModel->addbeneficiaryAPI($params);
//                $txnCode = $obj->getTxncode();


                if ($beneCode > 0) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->AckNo = $queryRefNum; //$baseTxn->getTxncode();
                    $responseObj->BeneficiaryCode = $beneCode;
                    $responseObj->ResponseCode = self::BENEFICIARY_REGISTRATION_SUCC_CODE;
                    $responseObj->ResponseMessage = self::BENEFICIARY_REGISTRATION_SUCC_MSG;
                    
                    // Update otp entry 
                    if($OTPrequest){
                        $upadteOTP = $refObject->updateCustomerOTPAPI(array(
                            'request_type' => 'B',
                            'id' => $responseOTP['id'],
                        ));
                    }
                } else {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) $resp->TransactionRefNo;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_BENE_REGISTRATION_FAIL_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_BENE_REGISTRATION_FAIL_MSG;
                }
                
               $sendSMS = TRUE; // Default Send SMS setting
                if($this->getSendSMSConstant() == SEND_SMS_FALSE){
                     $sendSMS = FALSE;
                 }
                 if($sendSMS){
                if (strtolower(trim($resp->SMSFlag)) == strtolower(FLAG_Y)) {
                    // Send SMS   
                    $params_sms = array(
                        'product_id' => $param['product_id'],
                        'bene_name' => $params['name'],
                        'mobile' => $custInfo['mobile'],
                    );
                    $cardholderModel->generateSMSDetails($params_sms, $smsType = BENE_REG_SMS);
                }
            }
                return $responseObj;
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                    $code = $e->getCode();
                    $message = $e->getMessage();
                    
                    if( (empty($code) ) || (empty($message)) ) {
                        $code = ErrorCodes::ERROR_BENE_REGISTRATION_FAIL_CODE;
                        $message = ErrorCodes::ERROR_BENE_REGISTRATION_FAIL_MSG;
                    }
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) $resp->TransactionRefNo;
                    $responseObj->ResponseCode = $code;
                    $responseObj->ResponseMessage = $message;
                    return $responseObj;
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
            return self::Exception($message, $code);
        }
    }

    // Account block request 

    public function AccountBlockRequest() {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
                 return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }

            Validator_Ratnakar::chkallParams($resp);
            Validator_Ratnakar::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());
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

                $param ['product_id'] = (string) trim($resp->ProductCode);

                if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_MOB)) {
                    $param['mobile'] = (string) trim($resp->MemberIDCardNo);
                } else if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER)) {
                    $param['partner_ref_no'] = (string) trim($resp->MemberIDCardNo);
                }
                
                $object = new Corp_Ratnakar_Cardholders();

                $custInfo = $object->getCardholderInfo($param);

                if (empty($custInfo)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                } elseif ($custInfo['cardholder_status'] == STATUS_BLOCKED) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG;
                    return $responseObj;
                }

                // Change 
                $accountBlockDate = date('Y-m-d h:i:s');
                $arr = array('status' => STATUS_BLOCKED,'date_blocked' => $accountBlockDate);
                $flg = $object->updateCardholderAPI($arr, $custInfo['id']);

                if ($flg == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnIdentifierType = (string) trim($resp->TxnIdentifierType);
                    $responseObj->MemberIDCardNo = (string) trim($resp->MemberIDCardNo);
                    $responseObj->ResponseCode = ErrorCodes::ERROR_BLOCK_FAILED_RESPONSE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_BLOCK_FAILED_RESPONSE_MSG;
                    $responseObj->AccountBlockStatus = self::ACCOUNT_BLOCK_STATUS_ACTIVE;
                    $responseObj->AccountBlockDateTime = $accountBlockDate;
                    return $responseObj;
                }
                $sendSMS = TRUE; // Default Send SMS setting  
                if(($this->getSendSMSConstant() == SEND_SMS_FALSE) || ($this->getBankProductConstant() == BANK_RATNAKAR_BOOKMYSHOW)){
                    $sendSMS = FALSE;
                }
                if($sendSMS){
                    if (strtolower($resp->SMSFlag) == strtolower(FLAG_Y)) {
                        // Send SMS   
                        $params = array(
                            'product_id' => $custInfo['product_id'],
                            'cust_id' => $custInfo['rat_customer_id'],
                            'mobile' => $custInfo['mobile'],
                        );
                        $object->generateSMSDetails($params, $smsType = CARD_BLOCK_SMS);
                    }
                }
                $responseObj = new stdClass();
                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->TxnIdentifierType = (string) trim($resp->TxnIdentifierType);
                $responseObj->MemberIDCardNo = (string) trim($resp->MemberIDCardNo);
                $responseObj->ResponseCode = self::BLOCK_SUCCSSES_RESPONSE_CODE;
                $responseObj->ResponseMessage = self::BLOCK_SUCCSSES_RESPONSE_MSG;
                $responseObj->AccountBlockStatus = self::ACCOUNT_BLOCK_STATUS_BLOCKED;
                $responseObj->AccountBlockDateTime = $accountBlockDate;

                return $responseObj;
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_BLOCK_FAILED_RESPONSE_CODE;
                    $message = ErrorCodes::ERROR_BLOCK_FAILED_RESPONSE_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_BLOCK_FAILED_RESPONSE_CODE;
                    $message = ErrorCodes::ERROR_BLOCK_FAILED_RESPONSE_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
        }
    }

    public function AccountUnBlockRequest() {
        
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            $refObject = new Reference();
            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {

                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);

            }
            Validator_Ratnakar::chkallParams($resp);
            Validator_Ratnakar::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());
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
            
            // Check OTP
            $OTPrequest = FALSE; // Default OTP setting  
            if($this->getUnblockOTPRequestConstant() == OTP_REQUEST_TRUE){
                $OTPrequest = TRUE;
            }
            $otp = (string) trim($resp->OTP);       
            if($OTPrequest){
                if ($otp != '') {
                    if (strlen($otp) != 6 || !(ctype_digit($otp))) {
                        throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
                    }
                } else {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
                }
            }
                                
            try {
                $param ['product_id'] = (string) trim($resp->ProductCode);

                if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_MOB)) {
                    $param['mobile'] = (string) trim($resp->MemberIDCardNo);
                } else if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER)) {
                    $param['partner_ref_no'] = (string) trim($resp->MemberIDCardNo);
                }

                $object = new Corp_Ratnakar_Cardholders();
                $custInfo = $object->getCardholderInfo($param);

                if (empty($custInfo)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                } elseif ($custInfo['cardholder_status'] == STATUS_ACTIVE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_ALREADY_ACTIVE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_ALREADY_ACTIVE_MSG;
                    return $responseObj;
                }
                
                if($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_MOB)){
                    $mobileNum = (string) trim($resp->MemberIDCardNo);
                }else if(!empty($custInfo)){                    
                    $mobileNum = $custInfo['mobile']; 
                }
                
                
                if($OTPrequest){
                    $responseOTP = $refObject->verifyCustomerOTPAPI(array(
                        'request_type' => 'U',
                        'otp' => (string) trim($resp->OTP),
                        'mobile' => $mobileNum,
                        'ref_id' => $custInfo['id'],
                    ));
                    if ($responseOTP == FALSE) {
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = self::UNABLE_TO_PROCESS_CODE;
                        $responseObj->ResponseMessage = self::OTP_INVALID_RESPONSE_MSG;
                        return $responseObj;
                    }
                } 

                // Change 
                $accountUnBlockDate = date('Y-m-d h:i:s');
                $arr = array('status' => STATUS_ACTIVE,'date_activation' => $accountUnBlockDate);
                $flg = $object->updateCardholderAPI($arr, $custInfo['id']);

                if ($flg == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnIdentifierType = (string) trim($resp->TxnIdentifierType);
                    $responseObj->MemberIDCardNo = (string) trim($resp->MemberIDCardNo);
                    $responseObj->ResponseCode = ErrorCodes::ERROR_UNBLOCK_FAILED_RESPONSE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_UNBLOCK_FAILED_RESPONSE_MSG;
                    $responseObj->AccountBlockStatus = self::ACCOUNT_BLOCK_STATUS_BLOCKED;
                    $responseObj->ProductCode = trim($resp->ProductCode);
                    return $responseObj;
                }
                $sendSMS = TRUE; // Default Send SMS setting 
                if(($this->getSendSMSConstant() == SEND_SMS_FALSE) || ($this->getBankProductConstant() == BANK_RATNAKAR_BOOKMYSHOW)){
                    $sendSMS = FALSE;
                }
                if($sendSMS){
                    if (strtolower(trim($resp->SMSFlag)) == strtolower(FLAG_Y)) {
                        // Send SMS
                        $params = array(
                            'product_id' => $custInfo['product_id'],
                            'cust_id' => $custInfo['rat_customer_id'],
                            'mobile' => $custInfo['mobile'],
                        );
                        $object->generateSMSDetails($params, $smsType = CARD_UNBLOCK_SMS);
                    }
                }
                $responseObj = new stdClass();
                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->TxnIdentifierType = (string) trim($resp->TxnIdentifierType);
                $responseObj->MemberIDCardNo = (string) trim($resp->MemberIDCardNo);
                $responseObj->ResponseCode = self::UNBLOCK_SUCCSSES_RESPONSE_CODE;
                $responseObj->ResponseMessage = self::UNBLOCK_SUCCSSES_RESPONSE_MSG;
                $responseObj->AccountBlockStatus = self::ACCOUNT_BLOCK_STATUS_ACTIVE;
                $responseObj->ProductCode = trim($resp->ProductCode);
                // Update otp entry 
                if($OTPrequest){ 
                    $upadteOTP = $refObject->updateCustomerOTPAPI(array(
                        'request_type' => 'U',
                        'id' => $responseOTP['id'],
                     ));
                }
                return $responseObj;
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_UNBLOCK_FAILED_RESPONSE_CODE;
                    $message = ErrorCodes::ERROR_UNBLOCK_FAILED_RESPONSE_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_UNBLOCK_FAILED_RESPONSE_CODE;
                    $message = ErrorCodes::ERROR_UNBLOCK_FAILED_RESPONSE_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
        }
    }

    public function BalanceEnquiryRequest() {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);


            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }
            Validator_Ratnakar::chkallParams($resp);
            Validator_Ratnakar::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());
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

                $object = new Corp_Ratnakar_Cardholders();
                $param ['product_id'] = (string) trim($resp->ProductCode);

                if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_MOB)) {
                    $param['mobile'] = (string) trim($resp->MemberIDCardNo);
                } else if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER)) {
                    $param['partner_ref_no'] = (string) trim($resp->MemberIDCardNo);
                }
                $param['status'] = STATUS_ACTIVE;
                $custInfo = $object->getCardholderInfo($param);

                if (empty($custInfo)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                }
                $obj = new Corp_Ratnakar_CustomerPurse();
                if ( ((string) trim($resp->WalletCode) != '') ) {
                  
                   
                   if( strtoupper((string) trim($resp->WalletCode))== WALLET_WISE_BALANCE ){
                    $custPurse = $obj->getCustBalanceWalletWise($custInfo['rat_customer_id']);
                   }
                    else{ // check if wallet code is valid for the product
                    
                   $isValidwallet = $obj->checkValidWallet((string) trim($resp->ProductCode),(string) trim($resp->WalletCode));
                  
                   if (!$isValidwallet) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_MSG;
                    return $responseObj;
                }
                   
                        
                    $custPurse = $obj->getCustBalanceByWallet($custInfo['rat_customer_id'], (string) trim($resp->WalletCode));
                } 
                }
                else {
                    $custPurse = $obj->getCustBalance($custInfo['rat_customer_id']);
                }


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
                 if( strtoupper((string) trim($resp->WalletCode))== WALLET_WISE_BALANCE ){
                    $return_array = new ArrayObject();
                    foreach ($custPurse as $key=>$transValue){
                       // $num = sprintf('%03u', $no);
                        $amount = Util::convertIntoPaisa($transValue['sum']);
                       // $walletDetail = self::HEADER_NAMESPACE.':WalletBalanceDetail'.$num;
                        $walletDetail = new stdClass();
                        $walletDetail->AvailableBalance = $amount;
                        $walletDetail->WalletCode = $transValue['code'];
                         $walletDetail = new SoapVar($walletDetail, SOAP_ENC_OBJECT, null, null, 'WalletDetail');
                         $return_array->append($walletDetail);
                      
                    }
                    $responseObj->WalletDetails = new SoapVar($return_array, SOAP_ENC_OBJECT, NULL, NULL, 'WalletDetails');

                }
                else{
                $responseObj->AvailableBalance = isset($custPurse['sum'])?Util::convertIntoPaisa($custPurse['sum']):0;
                $responseObj->ResponseCode = self::BALANCE_SUCCSSES_RESPONSE_CODE;
                $responseObj->ResponseMessage = self::BALANCE_SUCCSSES_RESPONSE_MSG;
                }
                
                
                return $responseObj;
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_CUSTOMER_BAL_ENQ_FAILED_RESPONSE_CODE;
                    $message = ErrorCodes::ERROR_CUSTOMER_BAL_ENQ_FAILED_RESPONSE_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_CUSTOMER_BAL_ENQ_FAILED_RESPONSE_CODE;
                    $message = ErrorCodes::ERROR_CUSTOMER_BAL_ENQ_FAILED_RESPONSE_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
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
            Validator_Ratnakar::chkallParams($resp);
            /*
             * Transaction Reference Numver Validation
             */
            Validator_Ratnakar::txnrefnoValidation((string) trim($resp->TransactionRefNo));

            /*
             * Product code validation
             */

            Validator_Ratnakar::productcodeValidation((string) trim($resp->ProductCode), $this->getProductConstant());

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

                // Check OTP
                $OTPrequest = TRUE; // Default OTP setting
                if($this->getOTPRequestConstant() == 'false'){
                     $OTPrequest = FALSE;
                 }

                $otp = (string) trim($resp->OTP);
                if($OTPrequest){
                    if ($otp != '') {
                        if (strlen($otp) != 6 || !(ctype_digit($otp))) {
                            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
                        }
                    } else {
                        throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
                    }
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
                
                $refObject = new Reference();
                 
                $params = array();
                $remit_params = array();

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
                       Validator_Ratnakar::customerMOBValidation((string) trim($resp->CustomerNo));    
                     } else if ($customerIdentifierType == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER)) {
                        Validator_Ratnakar_Customer::partnerRefnoValidation((string) trim($resp->CustomerNo));
                    }
                  }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_IDENTIFIRE_TYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_IDENTIFIRE_TYPE_CODE);
            }
            
               $dupli_params = array(); // For checking duplicate records
               $dupli_params['product_id'] = (string) trim($resp->ProductCode);
               $bankObject   = new Banks();
               $bankInfo = $bankObject->getBankidByProductid((string) trim($resp->ProductCode));
                if(!empty($bankInfo) ){
                 $dupli_params['bank_id'] = $bankInfo['bank_id'];    
                }else{
                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);   
                }
                
              if ($customerIdentifierType == strtolower(self::TXN_IDENTIFIER_TYPE_MOBILE)) {
                    $params['mobile'] = (string) trim($resp->CustomerNo);
                    $dupli_params['mobile'] = (string) trim($resp->CustomerNo);
                } else if ($customerIdentifierType == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER)) {
                    $params['partner_ref_no'] = (string) trim($resp->CustomerNo);
                    $dupli_params['partner_ref_no'] = (string) trim($resp->CustomerNo);
                }
                $params['TransactionRefNo'] = (string) $strTansRefNo; //$resp->TransactionRefNo;
                $params['CustomerIdentifierType'] = (string) trim($resp->CustomerIdentifierType); //$resp->PartnerRefNo;
                $params['CustomerNo'] = (string) trim($resp->CustomerNo);
                $params['ProductId'] = (string) trim($resp->ProductCode);
                if((string) trim($resp->Mobile) !=''){
                    $params['Mobile'] = (string) trim($resp->Mobile);
                    $remit_params['mobile'] = (string) trim($resp->Mobile);
                }
                if((string) trim($resp->Email) !=''){
                    $params['Email'] = (string) trim($resp->Email);
                    $remit_params['email'] = (string) trim($resp->Email);
                }
                if((string) trim($resp->Landline) !=''){
                    $params['Landline'] = (string) trim($resp->Landline);
                }
                if((string) trim($resp->AddressLine1) !=''){
                    $params['AddressLine1'] = (string) trim($resp->AddressLine1);
                    $remit_params['address'] = (string) trim($resp->AddressLine1);
                }
                if((string) trim($resp->AddressLine2) !=''){
                    $params['AddressLine2'] = (string) trim($resp->AddressLine2);
                    $remit_params['address_line2'] = (string) trim($resp->AddressLine2);
                }
                if((string) trim($resp->City) !=''){
                    $params['City'] = (string) trim($resp->City);
                    $remit_params['city'] = (string) trim($resp->City);
                }
                 if((string) trim($resp->State) !=''){
                    $params['State'] = (string) trim($resp->State);
                    $remit_params['state'] = (string) trim($resp->State);
                }
                 if((string) trim($resp->Pincode) !=''){
                    $params['Pincode'] = (string) trim($resp->Pincode);
                    $remit_params['pincode'] = (string) trim($resp->Pincode);
                }
                if((string) trim($resp->Country) !=''){
                    $params['Country'] = (string) trim($resp->Country);
                }
                $params['OTP'] = (int) trim($resp->OTP);
                 $agent = $this->getAgentConstant();
                $params['by_api_user_id'] = $agent;

               
            //    $remit_params['product_id'] = (string) trim($resp->ProductCode);
             //   $remit_params['by_agent_id'] = $agent;
                //$params['Filler3'] = (string) $resp->Filler3;
                //$params['Filler4'] = (string) $resp->Filler4;
                //$params['Filler5'] = (string) $resp->Filler5;

                $obj = new Corp_Ratnakar_Cardholders();
                $baseTxn = new BaseTxn();
                $txnCode = $baseTxn->generateTxncode();
                 $custRecord = array(
                    'mobile' => $params['mobile'],
                    'partner_ref_no' => $params['partner_ref_no'],
                  //  'txnrefnum' => $params['TransactionRefNo'],
                    'product_id' => $params['ProductId'],
                );
                $custDetail = $obj->getCustomerDetails($custRecord);
                
                if ($custDetail == FALSE) {
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                } elseif($custDetail['status'] == STATUS_BLOCKED) {
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG;
                    return $responseObj;
                }else if((string) trim($resp->Mobile) !=''){
                    $custDetails = Util::toArray($custDetail);
                     $custID = $custDetails['id'];
                     $txnCode = $custDetails['txn_code']; 
                     $custMobile = $custDetails['mobile']; 
                     if($custID > 0){
                     $objectRelation = new ObjectRelations();    
                     $remitterID = $objectRelation->getToObjectInfo($custID, RAT_MAPPER);
                     $custRemitterID = $remitterID['to_object_id']; 
                      if($custRemitterID > 0){
                          $dupli_params['remitter_id'] = $custRemitterID;
                          $ratRemitterObj = new Remit_Ratnakar_Remitter();
                          $respRemMobile = $ratRemitterObj->checkEditDuplicateMobile($dupli_params,$params['Mobile']);
                        if ($respRemMobile == TRUE) {
                            return self::Exception(ErrorCodes::ERROR_EDIGITAL_MOBILE_USED_MSG, ErrorCodes::ERROR_EDIGITAL_MOBILE_USED_CODE);
                        }
                      }else{
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                            $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                            return $responseObj;
                        }
                     }   
                }
                else{
                $custDetails = Util::toArray($custDetail);
                $txnCode = $custDetails['txn_code'];    
                }
                
                if((string) trim($resp->Mobile) !=''){
                    $mobileNum = (string) trim($resp->Mobile);
                }else if(!empty($custDetail)){                    
                    $mobileNum = $custDetail['mobile']; 
                }
               
                if($OTPrequest){
                    $responseOTP = $refObject->verifyCustomerOTPAPI(array(
                        'request_type' => 'E',
                        'otp' => (string) trim($resp->OTP),
                        'mobile' => $mobileNum,
                        'ref_id' => $custDetail['id'],
                    ));
                    if ($responseOTP == FALSE) {
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = ErrorCodes::OTP_INVALID_RESPONSE_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::OTP_INVALID_RESPONSE_MSG;
                        return $responseObj;
                    }
                } 
                
                /*
                 * checkEditDuplicateMobile : Checking duplicate mobile number for customer
                 */
                
                if( ((string) trim($resp->Mobile) !='') && ($custMobile!= (string) trim($resp->Mobile)) ){
                   
                    $respMobile = $obj->checkEditDuplicateMobile($dupli_params,$params['Mobile']);
                    if ($respMobile == TRUE) {
                        return self::Exception(ErrorCodes::ERROR_EDIGITAL_MOBILE_USED_MSG, ErrorCodes::ERROR_EDIGITAL_MOBILE_USED_CODE);
                    }
                }
                
                /*
                 * checkEditDuplicateEmail : Checking duplicate email id for customer
                 */
//                 if((string) trim($resp->Email) !=''){
//                   
//                    $respEmail = $obj->checkEditDuplicateEmail($dupli_params,$params['Email']);
//                    if ($respEmail == TRUE) {
//                        return self::Exception('Email address already in use', App_ApiServer_Exchange::$INVALID_RESPONSE);
//                    }
//                }
                $response = $obj->editCustomerAPI($params);
               
                if ($response == TRUE) {
                    /*
                     *Sending SMS
                     */
                    
                $sendSMS = TRUE; // Default Send SMS setting
                if($this->getSendSMSConstant() == SEND_SMS_FALSE){
                     $sendSMS = FALSE;
                 }
                 
                 if($this->getProductConstant() == PRODUCT_CONST_RAT_BMS){
                  $sendSMS = FALSE;   
                 }
                 if($sendSMS){
                    if (strtolower($resp->SMSFlag) == strtolower(FLAG_Y)) {
                    // Send SMS   
                    $params_sms = array(
                        'product_id' => $params['ProductId'],
                        'mobile' => $mobileNum,
                    );
                      $obj->generateSMSDetails($params_sms, $smsType = UPDATE_CUST_SMS);
                     }
                 }
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->AckNo = $txnCode; //$baseTxn->getTxncode();
                    $responseObj->CustomerIdentifierType = (string) trim($resp->CustomerIdentifierType);
                    $responseObj->CustomerNo = (string) trim($resp->CustomerNo);
                    $responseObj->ResponseCode = self::CUSTOMER_UPDATION_SUCC_CODE;
                    $responseObj->ResponseMessage = self::CUSTOMER_UPDATION_SUCC_MSG;
                     // Update otp entry 
                   if($OTPrequest){ 
                        $upadteOTP = $refObject->updateCustomerOTPAPI(array(
                            'request_type' => 'E',
                            'id' => $responseOTP['id'],
                        ));
                   }
                } else {
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
                return self::Exception($message, $code);
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
            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
                 return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }
            Validator_Ratnakar::chkallParams($resp);
            
            if((string) trim($resp->AckNo) == '' && (string) trim($resp->TransactionRefNo) == '') {
                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_ACK_TRAN_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_ACK_TRAN_NO_CODE);
               
            } else if((string) trim($resp->AckNo) != '' && (string) trim($resp->TransactionRefNo) == '') {
                Validator_Ratnakar::queryrefnoValidation((string) trim($resp->AckNo));
            } else if((string) trim($resp->AckNo) == '' && (string) trim($resp->TransactionRefNo) != '') {
                Validator_Ratnakar::txnrefnoValidation((string) trim($resp->TransactionRefNo));
            } else {
                /*
                 * Transaction Reference Numver Validation
                 */
                Validator_Ratnakar::txnrefnoValidation((string) trim($resp->TransactionRefNo));

                /*
                 * Query Reference Numver Validation
                 */
                Validator_Ratnakar::queryrefnoValidation((string) trim($resp->AckNo));
            }
            
            try {
                $strTansRefNo = (string) trim($resp->TransactionRefNo);
                $strQueryReqNo = (string) trim($resp->AckNo);

                $productConst = $this->getProductConstant();
                $productModel = new Products();
                $productID = $productModel->getProductIDbyConst($productConst); 
                $params['product_id'] = $productID;
                $params['txnrefnum'] = (string) $strTansRefNo; //$resp->TransactionRefNo;
                $params['txn_code'] = (string) $strQueryReqNo; 
                
              //  $params['by_api_user_id'] = $this->_TP_ID;
                //$params['Filler3'] = (string) $resp->Filler3;
                //$params['Filler4'] = (string) $resp->Filler4;
                //$params['Filler5'] = (string) $resp->Filler5;

                $obj = new Corp_Ratnakar_Cardholders();
                $cardholderData = $obj->getCardholderInfo($params);
              //  $baseTxn = new BaseTxn();
              //  $txnCode = $baseTxn->generateTxncode();
               
                if (!empty($cardholderData)) {
                    if($cardholderData['cardholder_status']!='' && $cardholderData['cardholder_status'] == CARDHOLDER_ACTIVE_STATUS){
                        $transactionStatus = FLAG_SUCCESS;
                    }else{
                        $transactionStatus = STATUS_BLOCKED;
                    }
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = $cardholderData['txn_code']; //$baseTxn->getTxncode();
                    $responseObj->TransactionRefNo = $cardholderData['txnrefnum']; 
                    $responseObj->PartnerRefNo = $cardholderData['partner_ref_no'];
                    $responseObj->ProductCode = $cardholderData['product_id'];
                    $responseObj->Mobile = $cardholderData['mobile'];
                    $responseObj->Email = $cardholderData['email'];
                    $responseObj->Name = $cardholderData['first_name'];
                    $responseObj->TransactionStatus = $transactionStatus;
                    $responseObj->ResponseCode = self::QUERY_REGISTRATION_SUCC_CODE;
                    $responseObj->ResponseMessage = self::QUERY_REGISTRATION_SUCC_MSG;
                } else {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_WRONG_ACK_TRAN_NO_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_WRONG_ACK_TRAN_NO_MSG;
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
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
             $code = $e->getCode();
                $code = (empty($code)) ? ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE : $code;
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_QUERY_REGISTRATION_FAIL_CODE;
                    $message = ErrorCodes::ERROR_QUERY_REGISTRATION_FAIL_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
            return self::Exception($message, $code);
        }
    }


    public function TransactionRequest() {
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            $voucherIndicator = '';
            $voucherCode = '';
            //generateSMSDetails
            if( !isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {

                  return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);

             }
            Validator_Ratnakar::chkallParams($resp);
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

                if (!ctype_digit((string) trim($resp->Amount)) || (trim($resp->Amount) < 1) ) {
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
            if((string) trim($resp->Narration)!=''){
            Validator_Ratnakar_Customer::transactionNarrationValidation((string) trim($resp->Narration));
            }
	    Validator_Ratnakar_Customer::walletCodeValidation((string) trim($resp->WalletCode));

           // Check OTP
            $OTPrequest = TRUE; // Default OTP setting
            if($this->getOTPRequestConstant() == 'false'){
                 $OTPrequest = FALSE;
             }
            $otp = (string) trim($resp->OTP);
            if($OTPrequest){
                if ($otp != '') {
                    if (strlen($otp) != 6 || !(ctype_digit($otp))) {
                       throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
                    }
                }
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
            $txnidentifierflag = (string) trim($resp->TxnIdentifierType);
            $txnidentifierflag = strtolower($txnidentifierflag);

            if ($txnidentifierflag != '') {
                if (strlen($txnidentifierflag) > 3 || ( $txnidentifierflag != strtolower(self::TXN_IDENTIFIER_TYPE_MOB) && $txnidentifierflag != strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER) )) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_CODE);
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_CODE);
            }

            
            $loadExiryDate = '';
            $voucherCode = '';

            $loadExpiry = (string) trim($resp->Filler1);
            if($loadExpiry !=''){
                Validator_Ratnakar_Customer::loadExpiryValidation($loadExpiry);
                $loadExpiry_str = strtotime($loadExpiry);
                $loadExiryDate = date('Y-m-d H:i:s',$loadExpiry_str);
               }

            // Check Filler2 and Filler3
            $filler2 = (string) trim($resp->Filler2);
            $filler2 = strtolower($filler2);
            $filler3 = (string) trim($resp->Filler3);
            $filler4 = (string) trim($resp->Filler4);
            $filler5 = (string) trim($resp->Filler5);
     
            if( ( ($filler2 !='') && ($filler2 != strtolower(self::FLAG_GIFT_VOUCHER)) ) || ( ($filler2 =='') && ($filler3 != '') ) ){

                  throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER2_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER2_CODE);
                  
            }elseif( ( ($filler2 !='') && ($filler3 == '') )){
                  throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER3_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER3_CODE);
                  
            }elseif( ( ($filler2 !='') && ($filler3 != '') )){
                    if (strlen($filler3) > 20 || !(ctype_alnum($filler3))) {
                        throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER3_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER3_CODE);
                    }
                   $voucherIndicator = $filler2;
                   $voucherCode = (string) trim($resp->Filler3);
            }
            
            if( (strtolower((string) trim($resp->TxnIndicator)) == TXN_MODE_DR) && (  ((string) trim($resp->Filler1) !='' )) ){
                     throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_LOAD_EXPIRY_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_LOAD_EXPIRY_CODE);   
//                       
            }
            

            /*
            * Reversal Validations
            */
             $balanceWalletCode = (string) trim($resp->WalletCode); 
            if( ($filler4 == '') && ($filler5 != '') ){
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER4_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER4_CODE);
	    } elseif(($filler4 != '') && ((strtolower($filler4) != strtolower(self::FLAG_REVERSAL_TRANS)) && (strtolower($filler4) != strtolower(CLAIM_BLOCK_AMOUNT_TYPE)))){
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER4_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER4_CODE);
            }elseif( ( $filler4 != '' ) && ( $filler5 == '') ){
             throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER5_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER5_CODE);
            }elseif((strtolower($filler4) == strtolower(self::FLAG_REVERSAL_TRANS)) && ($filler5 != '') ){
                Validator_Ratnakar_Customer::chkOriginalTxnNum($filler5);
                $balanceWalletCode = '';
	    } elseif((strtolower($filler4) == strtolower(CLAIM_BLOCK_AMOUNT_TYPE)) && ($filler5 != '')){
		Validator_Ratnakar_Customer::chkOriginalTxnNum($filler5);
	    }
	    
	    try {

                $refObject = new Reference();
                $object = new Corp_Ratnakar_Cardholders();
                $baseTxn = new BaseTxn();
                $masterPurseDetails = new MasterPurse();
                $ackno = $baseTxn->generateTxncode();
               
                $param['product_id'] = (string) trim($resp->ProductCode);

                if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_MOB)) {
                    $param['mobile'] = (string) trim($resp->MemberIDCardNo);
                } else if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER)) {
                    $param['partner_ref_no'] = (string) trim($resp->MemberIDCardNo);
                }

                $custInfo = $object->getCustomerDetails($param);
                if ($custInfo == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                } elseif($custInfo['status'] == STATUS_BLOCKED) {
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG;
                    return $responseObj;
                }
                
                
                if($OTPrequest){
                    if ($otp != '') {
                        $responseOTP = $refObject->verifyCustomerOTPAPI(array(
                            'request_type' => 'T',
                            'otp' => (string) trim($resp->OTP),
                            'mobile' => (string) $custInfo['mobile'],
                        ));
                    
                        if ($responseOTP == FALSE) {
                            $responseObj = new stdClass();
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->AckNo = $ackno;
                            $responseObj->ResponseCode = ErrorCodes::OTP_INVALID_RESPONSE_CODE;
                            $responseObj->ResponseMessage = ErrorCodes::OTP_INVALID_RESPONSE_MSG;
                            return $responseObj;
                        }
                    }
                }
                
                $obj = new Corp_Ratnakar_Cardload();
                $productConst = $this->getProductConstant();
                $productModel = new Products();
                $productID = $productModel->getProductIDbyConst($productConst); 
                $loadDetails = $obj->getLoadDetails(array('txn_no' => (string) trim($resp->TxnNo),'product_id'=>$productID));
                if (!empty($loadDetails) ) {
                    $txnCode = $loadDetails['txn_code'];
                    return self::Exception(ErrorCodes::ERROR_EDIGITAL_TRAN_REF_NO_USED_MSG, ErrorCodes::ERROR_EDIGITAL_TRAN_REF_NO_USED_CODE);
                }else{
               // $baseTxn = new BaseTxn();
                $txnCode = $baseTxn->generateTxncode();   
                }

                if($voucherIndicator != '') {
                    $loadDetails = $obj->getVoucherDetails(array('voucher_num' => $voucherCode, 'product_id'=>$productID, 'mode'=>(string) trim($resp->TxnIndicator)));
                    if (!empty($loadDetails) ) {
                        $voucherNum = $loadDetails['voucher_num'];
                        return self::Exception(ErrorCodes::ERROR_EDIGITAL_WRONG_VOUCHER_NUMBER_MSG, ErrorCodes::ERROR_EDIGITAL_WRONG_VOUCHER_NUMBER_CODE,$voucherNum);
                    }
                }
                
               
                
                //Do card load
                
                 if($voucherIndicator != '') {
                    $loadDetails = $obj->getVoucherDetails(array('voucher_num' => $voucherCode, 'product_id'=>$productID, 'mode'=>(string) trim($resp->TxnIndicator)));
                    if (!empty($loadDetails) ) {
                        $voucherNum = $loadDetails['voucher_num'];
                        return self::Exception(ErrorCodes::ERROR_EDIGITAL_WRONG_VOUCHER_NUMBER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE,$voucherNum);
                    }
                }
                
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
                $params['sms_flag'] = $smsflag;
                $params['manageType'] = $this->getManageTypeConstant();
                $params['date_expiry'] = $loadExiryDate;
                $params['Filler1'] = (string) trim($resp->Filler1);
                $params['Filler2'] = (string) trim($resp->Filler2);
                $params['Filler3'] = (string) trim($resp->Filler3);
                $params['Filler4'] = (string) trim($resp->Filler4);
                $params['Filler5'] = (string) trim($resp->Filler5);

                $params['voucher_num'] = $voucherCode; 
                $params['channel'] = CHANNEL_API;
                
                $bankInfo = $bankObject->getBankidByProductid($params['product_id']);
                if(!empty($bankInfo) ){
                 $params['bank_id'] = $bankInfo['bank_id'];    
                }else{
                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);   
                }

                $params['voucher_num'] = $voucherCode;
                
                 // Getting Customer Total Balance Parameter
                 $custParams = array(
                  'product_id' =>  (string) trim($resp->ProductCode),
                  'customer_master_id' =>  $custInfo ['customer_master_id'],
                  'rat_customer_id' =>  $custInfo ['rat_customer_id'],
                  'wallet_code' =>  $balanceWalletCode,  
                );   
                $custtotalAmount = 0;
                
		if((strtolower($params['Filler4']) == strtolower(self::FLAG_REVERSAL_TRANS)) && ($params['Filler5'] !='') ){
		    
		      $flg = $obj->doReversalCardloadAPI($params); 


//                $flg = $obj->doCardloadAPI($params); - due to conflict

                
                 
                if ($flg == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnNo = (string) trim($resp->TxnNo);
                    $responseObj->AckNo = $txnCode;
                    $responseObj->Filler1 = $voucherCode;
                    $responseObj->Filler2 = '';
                    $responseObj->ResponseCode = ErrorCodes::ERROR_TRANSACTION_REQUEST_FAILURE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_TRANSACTION_REQUEST_FAILURE_MSG;
                    return $responseObj;
                }
                
             
                $walletResponses = $obj->doWalletBalanceAPI($custParams);
                if(!empty($walletResponses)){
                    $allwalletBalance = array_column($walletResponses, 'wallet_balance');
                    $custtotalAmount = array_sum($allwalletBalance);
                    $custtotalAmount = Util::convertIntoPaisa($custtotalAmount);
                }
                $responseObj = new stdClass();
               
                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->AvailableBalance = $custtotalAmount;
                $responseObj->WalletCode = $balanceWalletCode;
                $responseObj->ResponseCode = self::TRANSACTION_REQUEST_SUCCESS_CODE;
                $responseObj->ResponseMessage = self::TRANSACTION_REQUEST_SUCCESS_MSG;
                
                $return_array = new ArrayObject();
                
                foreach ($flg as $key=>$transValue){
                                
                                $amount = Util::convertIntoPaisa($transValue['amount']);
                                $transactionReqDetail = new stdClass();
                                $transactionReqDetail->AckNo = $transValue['txn_code'];
                                $transactionReqDetail->TxnNo = $transValue['txn_no'];
                                $transactionReqDetail->Currency = 'INR';
                                $transactionReqDetail->Amount = $amount;
                                $transactionReqDetail->WalletCode = $transValue['wallet_code'];
                                $transactionReqDetail = new SoapVar($transactionReqDetail, SOAP_ENC_OBJECT, null, null, 'TransactionDetail');
                                $return_array->append($transactionReqDetail);
                            }
                $responseObj->TransactionDetails = new SoapVar($return_array, SOAP_ENC_OBJECT, NULL, NULL, 'TransactionDetails');
                
		}else{ 
		    // ===> Validate Claim Amount
				
		    $params['claim_amount'] = 0 ;
		    if((strtolower($params['mode']) == TXN_MODE_DR) && (strtolower($params['Filler4']) == strtolower(CLAIM_BLOCK_AMOUNT_TYPE) ) && ($params['Filler5'] !='')) {
			
			$objBlock = new Corp_Ratnakar_BlockAmount();
			$getblockdetails = $objBlock->getBlockDetail(array('txn_code' => $params['Filler5'])) ;
			
			if(empty($getblockdetails)){
			    return self::Exception(ErrorCodes::ERROR_CLAIM_AMOUNT_FAIL_MSG, ErrorCodes::ERROR_CLAIM_AMOUNT_FAIL_CODE);
			} else if($getblockdetails['status'] != STATUS_BLOCKED){
			    return self::Exception(ErrorCodes::ERROR_CLAIM_AMOUNT_FAIL_MSG, ErrorCodes::ERROR_CLAIM_AMOUNT_FAIL_CODE);
			}else if($getblockdetails['txn_type'] != TXNTYPE_CARD_DEBIT){
			    return self::Exception(ErrorCodes::ERROR_CLAIM_AMOUNT_FAIL_MSG, ErrorCodes::ERROR_CLAIM_AMOUNT_FAIL_CODE);
			}  else if(Util::convertToRupee((string) trim($resp->Amount)) != $getblockdetails['amount']){
			    return self::Exception(ErrorCodes::ERROR_INCORRECT_AMOUNT_MSG, ErrorCodes::ERROR_INCORRECT_AMOUNT_CODE);
			} else if($custInfo['customer_master_id']!= $getblockdetails['customer_master_id']) {
			    return self::Exception(ErrorCodes::ERROR_INCORRECT_CUST_DETAIL_MSG, ErrorCodes::ERROR_INCORRECT_CUST_DETAIL_CODE);
			} else {
			    $amount = $params['claim_amount'] = Util::convertToRupee((string) trim($resp->Amount)) ;
				
			    $productWallet = App_DI_Definition_BankProduct::getInstance($this->getBankProductConstant());
			    $genwalletCode = $productWallet->purse->code->genwallet;
			    $customer_master_id = $getblockdetails['customer_master_id'];

			    $objCust = new Corp_Ratnakar_CustomerPurse();
			    $genpurseParam = array(
				'wallet_code'=>$genwalletCode,'customer_master_id'=>$customer_master_id
			    );
			    $genWalletDetail = $objCust->getPurseAmountByWallet($genpurseParam) ;
			    $available_amount = $genWalletDetail['amount'] - $genWalletDetail['block_amount'] + $params['claim_amount'] ;   
			    if($available_amount < $amount) {
				return self::Exception ("Customer does not have sufficient fund. Customer Available Balance: ".Util::numberFormat($available_amount)." Amount to be deducted: ".Util::numberFormat($params['claim_amount']), ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_REMITTANCE_CUST_BALANCE_CODE);
			    }
			}
		    }	

		    
		    $flg = $obj->doCardloadAPI($params);
                
                 
                if ($flg == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnNo = (string) trim($resp->TxnNo);
                    $responseObj->AckNo = $txnCode;
                    $responseObj->Filler1 = $voucherCode;
                    $responseObj->Filler2 = '';
                    $responseObj->ResponseCode = ErrorCodes::ERROR_TRANSACTION_REQUEST_FAILURE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_TRANSACTION_REQUEST_FAILURE_MSG;
                    return $responseObj;
                } 
		
				
		if((strtolower($params['mode']) == TXN_MODE_DR) && (strtolower($params['Filler4']) == strtolower(CLAIM_BLOCK_AMOUNT_TYPE) ) && ($params['Filler5'] !='')) {
		    // claim
		    $blockObj = new Corp_Ratnakar_BlockAmount();
		    // ==> Check Valid $filler1 on status basis and existance of txn_code(TransactionRefNo) 
		    if($blockObj->chkTxnCodeStatus(array('txn_code' => $filler5,'status' => STATUS_BLOCKED))) {
			$params = array(
			    'txn_code'	    =>	$filler5,
			    'amount'	    =>	(string) trim($resp->Amount),
			    'claim_txn_code'=>	$txnCode
			);
			$claim = $blockObj->doWalletClaimAmount($params);
		    }   
		}
		
		
                $walletResponses = $obj->doWalletBalanceAPI($custParams);
                if(!empty($walletResponses)){
                    $allwalletBalance = array_column($walletResponses, 'wallet_balance');
                    $custtotalAmount = array_sum($allwalletBalance);
                    $custtotalAmount = Util::convertIntoPaisa($custtotalAmount);
                }
                
               
                $responseObj = new stdClass();
               
                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->TxnNo = (string) trim($resp->TxnNo);
                $responseObj->AckNo = $txnCode;
                $responseObj->AvailableBalance = $custtotalAmount;
                $responseObj->WalletCode = $balanceWalletCode;

                $responseObj->Filler1 = $voucherCode;
                $responseObj->Filler2 = '';
                $responseObj->ResponseCode = self::TRANSACTION_REQUEST_SUCCESS_CODE;
                $responseObj->ResponseMessage = self::TRANSACTION_REQUEST_SUCCESS_MSG;
                
                
                }
                
                if($OTPrequest){
                        if ($otp != '') {
                        $upadteOTP = $refObject->updateCustomerOTPAPI(array(
                        'request_type' => 'T',
                        'id' => $responseOTP['id'],
                         ));
                        }
                    }
                return $responseObj;
            } catch (App_Exception $e) {
                App_Logger::log($e, Zend_Log::ERR);
                $code = $e->getCode();               
                if(strlen($code) > 3) {
                    $code = $this->filterErrorCodes($code);
                }
                
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_TRANSACTION_REQUEST_FAILURE_CODE;
                    $message = ErrorCodes::ERROR_TRANSACTION_REQUEST_FAILURE_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
                App_Logger::log($e, Zend_Log::ERR);
                $code = $e->getCode();
                if(strlen($code) > 3) {
                    $code = $this->filterErrorCodes($code);
                }
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_TRANSACTION_REQUEST_FAILURE_CODE;
                    $message = ErrorCodes::ERROR_TRANSACTION_REQUEST_FAILURE_MSG;
                }
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
            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }
            Validator_Ratnakar::chkallParams($resp);
            /*
             * Product Code Validation
             */
            Validator_Ratnakar::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());


            if((string) trim($resp->AckNo) == '' && (string) trim($resp->TransactionRefNo) == '') {
                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_ACK_TRAN_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_ACK_TRAN_NO_CODE);
            } else if((string) trim($resp->AckNo) != '' && (string) trim($resp->TransactionRefNo) == '') {
                Validator_Ratnakar::queryrefnoValidation((string) trim($resp->AckNo));
            } else if((string) trim($resp->AckNo) == '' && (string) trim($resp->TransactionRefNo) != '') {
                Validator_Ratnakar::txnrefnoValidation((string) trim($resp->TransactionRefNo));
            } else {
                /*
                * Query Reference Numver Validation
                */
               Validator_Ratnakar::queryrefnoValidation((string) trim($resp->AckNo));

               /*
                * Transaction Reference Number Validation
                */
               Validator_Ratnakar::txnrefnoValidation((string) trim($resp->TransactionRefNo));
            }
            
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
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE);
                }else{
                   /*  if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                        Validator_Ratnakar::mobileValidation((string) trim($resp->RemitterCode));
			} else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
			    Validator_Ratnakar_Customer::partnerRefnoValidation((string) trim($resp->RemitterCode));
		      } */  
		    Validator_Ratnakar::remitterCodeValidation($remitterflag,(string) trim($resp->RemitterCode));
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE);
            }

            try {
                $strTansRefNo = (string) trim($resp->TransactionRefNo);
                $strQueryReqNo = (string) trim($resp->AckNo);

                $masterPurseDetails = new MasterPurse();
                
                                
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
                
                $masterPurseDetails = $masterPurseDetails->getPurseInfo($params);
                
                if(empty($masterPurseDetails)){
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_QUERY_BENEFICIARY_FAILURE_RESPONSE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_QUERY_BENEFICIARY_FAILURE_RESPONSE_MSG;
                    return $responseObj;
                }
                
                $obj = new Corp_Ratnakar_Cardholders();
                $beneficiaryModel = new Remit_Ratnakar_Beneficiary();
              
                /*
                 * Getiing customer Detail  
                 */
                $cardholderData = $obj->getCardholderInfo($params);
                /*
                 *  Featching Remitter Id from object relation
                 */
                if($cardholderData['cardholder_status'] == STATUS_BLOCKED) { 
                    return self::Exception(ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG, ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE);
                } elseif (!empty($cardholderData)) {
                    $objectRelation = new ObjectRelations();
                    $remitterId = $objectRelation->getToObjectInfo($cardholderData['id'], RAT_MAPPER);
                    if (!empty($remitterId)) {
                        $bene_param['bene_code'] = (string) trim($resp->BeneficiaryCode);
                        $bene_param['queryrefno'] = $strQueryReqNo;
                        $bene_param['remitter_id'] = $remitterId['to_object_id'];
                        $bene_param['status'] = STATUS_ACTIVE;
                        $bene_param['txnrefnum'] = $strTansRefNo;
                        $beneInfo = $beneficiaryModel->getBeneInfoRow($bene_param);
                        $baseTxn = new BaseTxn();
                        $txnCode = $baseTxn->generateTxncode();
               
                        $m = new \App\Messaging\Corp\Ratnakar\Operation();
                        if (!empty($beneInfo)) {
                            
                            $responseObj = new stdClass();
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->AckNo = $beneInfo['queryrefno']; //$baseTxn->getTxncode();
                            $responseObj->RemitterCode = (string) trim($resp->RemitterCode);
                            $responseObj->BeneficiaryCode = $beneInfo['bene_code'];
                            $responseObj->Name = $beneInfo['first_name'];
                            $responseObj->Mobile = $beneInfo['mobile'];
                            $responseObj->Email = $beneInfo['email'];
                            $responseObj->AddressLine1 = $beneInfo['address_line1'];
                            $responseObj->AddressLine2 = $beneInfo['address_line2'];
                            $responseObj->BankName = $beneInfo['bank_name'];
                            $responseObj->BankBranch = $beneInfo['branch_name'];
                            $responseObj->BankCity = $beneInfo['branch_city'];
                            $responseObj->BankState = $beneInfo['state'];
                            $responseObj->BankIfscode = $beneInfo['ifsc_code'];
                            $responseObj->BankAccountNumber = $beneInfo['bank_account_number'];
                            $responseObj->ResponseCode = self::QUERY_BENEFICIARY_SUCC_CODE;
                            $responseObj->ResponseMessage = self::QUERY_BENEFICIARY_SUCC_MSG;
                        } else {
                            $responseObj = new stdClass();
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_WRONG_ACK_TRAN_NO_BENE_CODE;
                            $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_WRONG_ACK_TRAN_NO_BENE_MSG;
                        }
                        return $responseObj;
                        //
                    } else {
                        return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE);
                     }
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
                return self::Exception($message, $code);
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
                return self::Exception($message, $code);
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
            Validator_Ratnakar::chkallParams($resp);
            
            if((string) trim($resp->AckNo) == '' && (string) trim($resp->TransactionRefNo) == '') {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_ACK_TRAN_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_ACK_TRAN_NO_CODE);
            } else if((string) trim($resp->AckNo) != '' && (string) trim($resp->TransactionRefNo) == '') {
                Validator_Ratnakar::queryrefnoValidation((string) trim($resp->AckNo));
            } else if((string) trim($resp->AckNo) == '' && (string) trim($resp->TransactionRefNo) != '') {
                Validator_Ratnakar::txnrefnoValidation((string) trim($resp->TransactionRefNo));
            } else {
                Validator_Ratnakar::queryrefnoValidation((string) trim((string)$resp->AckNo));
                Validator_Ratnakar::txnrefnoValidation((string) trim($resp->TransactionRefNo));
            }
            
            try {


                $object = new Remit_Ratnakar_Remittancerequest();
                $masterPurseDetails = new MasterPurse();
                $productConst = $this->getProductConstant();
                $productModel = new Products();
                $productID = $productModel->getProductIDbyConst($productConst); 
                
                $baseTxn = new BaseTxn();
                $txnCode = $baseTxn->generateTxncode();
                    
                $masterPurseDetails = $masterPurseDetails->getPurseInfo(array('product_id' => $productID));
                if(empty($masterPurseDetails)){
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::QUERY_REMITTANCE_FAILURE_RESPONSE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::QUERY_REMITTANCE_FAILURE_RESPONSE_MSG;
                    return $responseObj;
                }

                $remitttanceData = $object->getRemittanceTransaction(array('txn_code' => (string) trim($resp->AckNo),'product_id'=>$productID, 'txnrefnum' => (string) trim($resp->TransactionRefNo)));
                // Query Remittance request code
                if ($remitttanceData == FALSE) {
                  return self::Exception(ErrorCodes::ERROR_EDIGITAL_WRONG_ACK_TRAN_NO_MSG, ErrorCodes::ERROR_EDIGITAL_WRONG_ACK_TRAN_NO_CODE);
                }else{
                $curCode = CURRENCY_INR;
                $remittanceType = TXN_NEFT;
                $amount = Util::convertIntoPaisa($remitttanceData['amount']);
                
                if($remitttanceData['status'] == STATUS_IN_PROCESS) {
                   $status = 'In Process';  
                }else{
                 $status = ucwords($remitttanceData['status']);
                }
                
                $responseObj = new stdClass();

                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->AckNo = $remitttanceData['txn_code'];
                $responseObj->TransactionRefNo = $remitttanceData['remittace_txnrefnum'];
                $responseObj->ProductCode = $productID;
                $responseObj->WalletCode = $remitttanceData['wallet_code'];
                $responseObj->RemitterCode = $remitttanceData['partner_ref_no'];
                $responseObj->BeneficiaryCode = $remitttanceData['bene_code'];
                $responseObj->Narration = $remitttanceData['sender_msg'];
                $responseObj->Amount = $amount;
                $responseObj->RemittanceType = $remittanceType;
                $responseObj->TransactionStatus = $status;
                
                $responseObj->ResponseCode = self::QUERY_REMITTANCE_SUCCESS_CODE;
                $responseObj->ResponseMessage = self::QUERY_REMITTANCE_SUCCESS_MSG;
                }
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
                return self::Exception($message, $code);
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
                return self::Exception($message, $code);
        }
    }

    public function WalletTransferRequest() {
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            
            //Disabling Check for Beneficiary EMail as discussed with Aniket on 2015-06-01 19:35
            $resp->BeneficiaryEmail = '';

            if( !isset($resp->SessionID) || !$this->isLogin((string) $resp->SessionID)) {
                 return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
             }
            Validator_Ratnakar::chkallParams($resp); 
            //	Amount valid check  
            if ((string) trim($resp->Amount) != '') {
                if (!ctype_digit((string) trim($resp->Amount)) || (trim($resp->Amount) < 1) ) {
		    return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_CODE);
                }
            } else {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_CODE);
            }


            Validator_Ratnakar::productcodeValidation((string) $resp->ProductCode, $this->getProductConstant());
            Validator_Ratnakar::txnrefnoValidation((string) $resp->TransactionRefNo);
            Validator_Ratnakar_Beneficiary::benemobileValidation((string) $resp->BeneficiaryMobile);
            if((string) $resp->BeneficiaryEmail !=''){
            Validator_Ratnakar_Beneficiary::beneemailValidation((string) $resp->BeneficiaryEmail);
            }
            Validator_Ratnakar_Customer::narrationValidation((string) $resp->Narration);
            Validator_Ratnakar_Beneficiary::remitterwalletcodeValidation((string) trim($resp->RemitterWalletCode));
            Validator_Ratnakar_Beneficiary::benewalletcodeValidation((string) trim($resp->BeneficiaryWalletCode));

            // Check OTP
            $OTPrequest = TRUE; // Default OTP setting
            if($this->getOTPRequestConstant() == 'false'){
                 $OTPrequest = FALSE;
             }
            $otp = (string) trim($resp->OTP);
            if($OTPrequest){
                if ($otp != '') {
                    if (strlen($otp) != 6 || !(ctype_digit($otp))) {
                      return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
                    }
                } else {
                        throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
                    }
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
                  /*  if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)) {
                        Validator_Ratnakar::mobileValidation((string) trim($resp->RemitterCode));
                    } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        Validator_Ratnakar_Customer::partnerRefnoValidation((string) trim($resp->RemitterCode));
                  } */
		  Validator_Ratnakar::remitterCodeValidation($remitterflag,(string) trim($resp->RemitterCode));
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERCODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERCODE_CODE);
            }
				
	    // ==> Check Valid filler1 And filler2
	    $filler1 = (string) trim($resp->Filler1);
            $filler2 = (string) trim($resp->Filler2);
	    if(strtolower($filler1) == CLAIM_BLOCK_AMOUNT_TYPE){ 
		if ($filler2 != '') {
		    if(strlen($filler2) > 16 || !(ctype_digit($filler2)) || $filler2 < 1) {
			throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER2_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER2_CODE);
			}
		} else {
		    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER2_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER2_CODE);
		}
	    }
            
            /*
             * Wallet Transfer validation
             */
           
            try {
		    $blockObj = new Corp_Ratnakar_BlockAmount();
		    $baseTxn = new BaseTxn();
		    $ackno = $baseTxn->generateTxncode();

		   /*if($this->getProductConstant() == PRODUCT_CONST_RAT_SHOP){
		       $productWallet = App_DI_Definition_BankProduct::getInstance($this->getBankProductConstant());
		       $shopCluesBuckWalletCode = $productWallet->purse->code->bucks;
		       if(strtolower((string)trim($resp->BeneficiaryWalletCode)) == strtolower($shopCluesBuckWalletCode)){
			    throw new Exception('This fund can not be tranferred to Beneficiary Wallet');
		       }
		   }*/
		       $refObject = new Reference();
		       $object = new Corp_Ratnakar_Cardholders();
		       $param ['product_id'] = (string) trim($resp->ProductCode);

		       if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOB)) {
			   $param['mobile'] = (string) trim($resp->RemitterCode);
		       } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
			   $param['partner_ref_no'] = (string) trim($resp->RemitterCode);
		       }

		       $custInfo = $object->getCustomerDetails($param);

		       if ($custInfo == FALSE) {
			   $responseObj = new stdClass();
			   $responseObj->SessionID = (string) $resp->SessionID;
			   $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
			   $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
			   return $responseObj;
		       } elseif($custInfo['status'] == STATUS_BLOCKED) {
			   $responseObj->SessionID = (string) $resp->SessionID;
			   $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE;
			   $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG;
			   return $responseObj;
		       }

		       if($OTPrequest){

			       $responseOTP = $refObject->verifyCustomerOTPAPI(array(
				  'request_type' => 'T',
				  'otp' => (string) trim($resp->OTP),
				  'mobile' => (string) $custInfo['mobile'],
			      ));
			      if ($responseOTP == FALSE) {
				  $responseObj = new stdClass();
				  $responseObj->SessionID = (string) $resp->SessionID;
				  $responseObj->AckNo = $ackno;
				  $responseObj->ResponseCode = ErrorCodes::OTP_INVALID_RESPONSE_CODE;
				  $responseObj->ResponseMessage = ErrorCodes::OTP_INVALID_RESPONSE_MSG;
				  return $responseObj;
			      }

		       }
		       // Wallet Transfer
		       $walletTransfer = new Remit_Ratnakar_WalletTransfer();
		       $objBanks = new Banks();
		       $custInfoTo = $object->getCardholderInfo(array('mobile' =>(string) trim($resp->BeneficiaryMobile),'email' => (string) $resp->BeneficiaryEmail,'product_id' => (string) trim($resp->ProductCode)));
		       // Check product
		       if ($custInfoTo == FALSE) {
			   $responseObj = new stdClass();
			   $responseObj->SessionID = (string) $resp->SessionID;
			   $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_BENE_CUSTOMER_NOT_FOUND_CODE;
			   $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_BENE_CUSTOMER_NOT_FOUND_MSG;
			   return $responseObj;
		       } elseif ($custInfoTo['cardholder_status'] == STATUS_BLOCKED) {
			   $responseObj = new stdClass();
			   $responseObj->SessionID = (string) $resp->SessionID;
			   $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE;
			   $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG;
			   return $responseObj;
		       }
		       if ($custInfo['product_id'] != $custInfoTo['product_id']){
			   throw new Exception(ErrorCodes::ERROR_EDIGITAL_WRONG_WALLET_TRANSFER_MSG, ErrorCodes::ERROR_EDIGITAL_WRONG_WALLET_TRANSFER_CODE);
		       }else{
		       $bankDetailsFrom = $objBanks->getBankbyProductId($custInfo['product_id'],PROGRAM_TYPE_DIGIWALLET);
		       $bankUnicodeFrom = $bankDetailsFrom['unicode']; 
		       $bank_id = $bankDetailsFrom['bank_id']; 

		       $bankDetailsTo = $objBanks->getBankbyProductId($custInfoTo['product_id'],PROGRAM_TYPE_DIGIWALLET);
		       $bankUnicodeTo = $bankDetailsTo['unicode']; 

		       }

		       // Same Customer Wallet transfer
		      //if($this->getProductConstant() == PRODUCT_CONST_RAT_SHOP){
		       $productWallet = App_DI_Definition_BankProduct::getInstance($this->getBankProductConstant());
		       //$shopCluesBuckWalletCode = $productWallet->purse->code->bucks;

		       // $productWallet = App_DI_Definition_BankProduct::getInstance($this->getBankProductConstant());
		       $genwalletCode = $productWallet->purse->code->genwallet;

		       $rem_wallet_code = (string)trim($resp->RemitterWalletCode);
		       if($rem_wallet_code == ''){
			 $rem_wallet_code = $genwalletCode;
		       }
		       $txn_wallet_code = (string)trim($resp->BeneficiaryWalletCode);

		       if($txn_wallet_code == ''){
			 $txn_wallet_code = $genwalletCode;
		       }

		       if($custInfo['rat_customer_id'] != $custInfoTo['rat_customer_id']){
			   if(strtolower($rem_wallet_code) != strtolower($genwalletCode)) {
			       throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FUND_TRANSFER_FROM_PROMO_WALLET_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FUND_TRANSFER_FROM_PROMO_WALLET_CODE);
			   } elseif(strtolower($txn_wallet_code) != strtolower($genwalletCode)) {
			       throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FUND_TRANSFER_TO_PROMO_WALLET_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FUND_TRANSFER_TO_PROMO_WALLET_CODE);
			   }
		       }else{
			   throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FUND_TRANSFER_TO_WALLET_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FUND_TRANSFER_TO_WALLET_CODE);
		       }  


		       $respTrans = $walletTransfer->checkDuplicateTransNum(array(
			   'product_id' => (string) trim($resp->ProductCode),
			   'txnrefnum' => (string) trim($resp->TransactionRefNo),
		       ));

		       if (!empty($respTrans['id'])) {
			   $ackno = $respTrans['txncode'];
			   return self::Exception(ErrorCodes::ERROR_EDIGITAL_TRAN_REF_NO_USED_MSG, ErrorCodes::ERROR_EDIGITAL_TRAN_REF_NO_USED_CODE,$ackno);
		       }
				
		       $params = array(
			   'txnrefnum' => (string) $resp->TransactionRefNo ,
			   'product_id' => (string) $resp->ProductCode ,
			   'rem_wallet_code' => $rem_wallet_code ,
			   'remitter_id' => $custInfo['rat_customer_id'],
			   'txn_remitter_id' => $custInfoTo['rat_customer_id'] ,
			   'txn_wallet_code' => $txn_wallet_code ,
			   'amount' => (string) $resp->Amount ,
			   'narration' => (string) trim($resp->Narration) ,
			   'customer_master_id' => $custInfo['customer_master_id'],
			   'txn_customer_master_id' => $custInfoTo['customer_master_id'],
			   'txn_product_id' => $custInfoTo['product_id'],
			   'txn_type' => TXNTYPE_WALLET_TOWALLET_FUND_TRANSFER,
			   'bank_id' => $bank_id,
			   'agent_id' => $this->getAgentConstant(),
			   'card_number' => $custInfo['card_number'],
			   'txn_card_number' => $custInfoTo['card_number']
		       );
				
		       /// chk block
			$param['claim_amount'] = 0 ;
			if((!empty($filler1)) && (strtolower($filler1) == CLAIM_BLOCK_AMOUNT_TYPE)){
			    $chkClaimParam = array(
				'blockedAmt_txnCode'=>  $filler2,
				'claim_amount'	    =>  (string) trim($resp->Amount),
				'wallet_code'	    =>	$rem_wallet_code,
				'product_id'	    =>	(string) trim($resp->ProductCode),
				'mobile'	    =>	$custInfo['mobile'],
				'partner_ref_no'    =>	$custInfo['partner_ref_no'],
				'txn_type'	    =>	TXNTYPE_WALLET_TOWALLET_FUND_TRANSFER
			    );
			    if($blockObj->chkClaimAmount($chkClaimParam)){
				$params['claim_amount'] = Util::convertToRupee((string) trim($resp->Amount)) ;
			    }       
			}
				
			if ($bankUnicodeFrom != $bankUnicodeTo){
			   throw new Exception(ErrorCodes::ERROR_EDIGITAL_WALLET_PRD_NOT_SAME_MSG, ErrorCodes::ERROR_EDIGITAL_WALLET_PRD_NOT_SAME_CODE);
			 }else{
			      $params['bank_unicode'] = $bankUnicodeFrom;

			 }

		       $flg = $walletTransfer->walletTransfer($params);

		       if ($flg['response'] == FALSE) {
			   $responseObj = new stdClass();
			   $responseObj->SessionID = (string) $resp->SessionID;
			   $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
			   $responseObj->AckNo = $ackno;
			   $responseObj->ResponseCode = ErrorCodes::WALLET_TRANSFER_FAILURE_CODE;
			   $responseObj->ResponseMessage = ErrorCodes::WALLET_TRANSFER_FAILURE_MSG;
			   return $responseObj;
		       } else {
                          // $flag['response'] = TRUE;
                           
                        if((!empty($filler1)) && (strtolower($filler1) == CLAIM_BLOCK_AMOUNT_TYPE)){ 
                            // ==> Check Valid $filler1 on status basis and existance of txn_code(TransactionRefNo) 
                            if($blockObj->chkTxnCodeStatus(array(
                                'txn_code'  =>	(string) trim($filler2),
                                'status'    =>	STATUS_BLOCKED))){
                                $params =array(
                                    'txn_code'	    =>  $filler2,
                                    'amount'	    =>  (string) trim($resp->Amount),
				    'claim_txn_code'=>	$flg['txn_code']
                                ); 
                                $claim = $blockObj->doWalletClaimAmount($params);
                            }
                         }
                       }
				
		       $curCode = CURRENCY_INR;

		       $responseObj = new stdClass();

		       $sendSMS = TRUE; // Default Send SMS setting
		       if($this->getSendSMSConstant() == SEND_SMS_FALSE){
			    $sendSMS = FALSE;
			}
		       if($sendSMS){

		       if (strtolower($resp->SMSFlag) == strtolower(FLAG_Y)) {
			   //Send SMS  
			   $params = array(
			       'product_id' => $custInfo['product_id'],
			       'mobile' => $custInfo['mobile'],
			       'amount' => Util::convertToRupee((string) $resp->Amount),
			       'bene_name' => $custInfoTo['first_name'].' '.$custInfoTo['last_name'],
			       'wallet_code' => (string) $resp->RemitterWalletCode,
			       'ref_num' => (string) $resp->TransactionRefNo,
			   );
			   $object->generateSMSDetails($params, $smsType = TRANSFER);
			 }
			}
		       $responseObj->SessionID = (string) $resp->SessionID;
		       $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
		       $responseObj->AckNo = $flg['txn_code'];
		       $responseObj->ResponseCode = self::WALLET_TRANSFER_SUCCESS_CODE;
		       $responseObj->ResponseMessage = self::WALLET_TRANSFER_SUCCESS_MSG;

		       if($OTPrequest){
			    if ($otp != '') {
			   // Update otp entry 
			       $upadteOTP = $refObject->updateCustomerOTPAPI(array(
			       'request_type' => 'T',
			       'id' => $responseOTP['id'],
			       ));
			    }
			   }
		       return $responseObj;	 
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::WALLET_TRANSFER_FAILURE_CODE;
                    $message = ErrorCodes::WALLET_TRANSFER_FAILURE_MSG;
                }
                if(strlen($code) > 3) {
                    $code = $this->filterErrorCodes($code);
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::WALLET_TRANSFER_FAILURE_CODE;
                    $message = ErrorCodes::WALLET_TRANSFER_FAILURE_MSG;
                }
                if(strlen($code) > 3) {
                    $code = $this->filterErrorCodes($code);
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
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
            Validator_Ratnakar::chkallParams($resp);
            /*
             * Product Code Validation

             */
            Validator_Ratnakar::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());


            /*
             * Query Reference Numver Validation
             */
          //  Validator_Ratnakar::queryrefnoValidation((string) trim($resp->QueryReqNo));

            /*
             * Bene Code Validation
             */

            Validator_Ratnakar_Beneficiary::beneCodeValidation((string) trim($resp->BeneficiaryCode));


             /*
             * Transaction Reference Numver Validation
             */
            Validator_Ratnakar::txnrefnoValidation((string)trim($resp->TransactionRefNo));
            
           
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
                //$strQueryReqNo = (string) trim($resp->QueryReqNo);

                $refObject = new Reference();
                $masterPurseDetails = new MasterPurse();

              //  $params['txnrefnum'] = (string) $strTansRefNo; //$resp->TransactionRefNo;
                //$params['QueryReqNo'] = (string) $strQueryReqNo; //$resp->PartnerRefNo;
             //   $params['by_api_user_id'] = $this->_TP_ID;
                $params['product_id'] = (string) trim($resp->ProductCode);
                //$params['Filler3'] = (string) $resp->Filler3;
                //$params['Filler4'] = (string) $resp->Filler4;
                //$params['Filler5'] = (string) $resp->Filler5;
                if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOB)) {
                    $params['mobile'] = (string) trim($resp->RemitterCode);
                } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                    $params['partner_ref_no'] = (string) trim($resp->RemitterCode);
                }
                
                $masterPurseDetails = $masterPurseDetails->getPurseInfo($params);
                if(empty($masterPurseDetails)){
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_DEACTIVE_BENEFICIARY_FAILURE_RESPONSE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_DEACTIVE_BENEFICIARY_FAILURE_RESPONSE_MSG;
                    return $responseObj;
                }
                
                $obj = new Corp_Ratnakar_Cardholders();
                $beneficiaryModel = new Remit_Ratnakar_Beneficiary();
                /*
                 * Getiing customer Detail  
                 */
                $cardholderData = $obj->getCardholderInfo($params);
               


                /*
                 *  Featching Remitter Id from object relation
                 */
                if($cardholderData['cardholder_status'] == STATUS_BLOCKED) {
                    return self::Exception(ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG, ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE);
                } elseif (!empty($cardholderData)) {
                    $objectRelation = new ObjectRelations();
                    $remitterId = $objectRelation->getToObjectInfo($cardholderData['id'], RAT_MAPPER);
                    if (!empty($remitterId)) {
                        $bene_param['bene_code'] = (string) trim($resp->BeneficiaryCode);
                       // $bene_param['txnrefnum'] = $strTansRefNo;
                        $bene_param['status'] = STATUS_ACTIVE;
                        $bene_param['remitter_id'] = $remitterId['to_object_id'];
                        $beneInfo = $beneficiaryModel->getBeneInfoRow($bene_param);
                       
                        if (!empty($beneInfo)) {
                        $beneID = $beneInfo['id'];
                        $queryrefno = $beneInfo['queryrefno'];
                        $updatearr = array('status' => STATUS_INACTIVE);
                        $beneUpdate = $beneficiaryModel->updateBeneficiaryDetails($updatearr,$beneID);
                            if($queryrefno == ''){
                            $baseTxn = new BaseTxn();
                            $queryrefno = $baseTxn->generateTxncode();
                            } 
                            $responseObj = new stdClass();
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->AckNo = $queryrefno; //$baseTxn->getTxncode();
                            $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                            $responseObj->RemitterCode = (string) trim($resp->RemitterCode);
                            $responseObj->BeneficiaryCode = (string) trim($resp->BeneficiaryCode);
                            
                            /*$responseObj->Title = $beneInfo['title'];
                            $responseObj->BankAccountNumber = $beneInfo['bank_account_number'];*/
                            $responseObj->ResponseCode = self::DEACTIVE_BENEFICIARY_SUCC_CODE;
                            $responseObj->ResponseMessage = self::DEACTIVE_BENEFICIARY_SUCC_MSG;
                             return $responseObj;
                            
                            } else {
                            return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_BENEFICIARY_CODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_BENEFICIARY_CODE);
                        }
                       
                        //
                    } else {
                         return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE);
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
                return self::Exception($message, $code);
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
                return self::Exception($message, $code);
        }
    }

    public function QueryTransferRequest() {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);


            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }

            Validator_Ratnakar::chkallParams($resp);
            Validator_Ratnakar::productcodeValidation((string) trim($resp->ProductCode), $this->getProductConstant());
            
            if((string) trim($resp->AckNo) == '' && (string) trim($resp->TransactionRefNo) == '') {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_ACK_TRAN_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_ACK_TRAN_NO_CODE);
            } else if((string) trim($resp->AckNo) != '' && (string) trim($resp->TransactionRefNo) == '') {
                Validator_Ratnakar::queryrefnoValidation((string) trim($resp->AckNo));
            } else if((string) trim($resp->AckNo) == '' && (string) trim($resp->TransactionRefNo) != '') {
                Validator_Ratnakar::txnrefnoValidation((string) trim($resp->TransactionRefNo));
            } else {
                Validator_Ratnakar::txnrefnoValidation((string) trim($resp->TransactionRefNo));
                Validator_Ratnakar::queryrefnoValidation((string) trim($resp->AckNo));
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

            try {

               
                $object = new Corp_Ratnakar_Cardholders();
                $param ['product_id'] = (string) trim($resp->ProductCode);

                if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOB)) {
                    $param['mobile'] = (string) trim($resp->RemitterCode);
                } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                    $param['partner_ref_no'] = (string) trim($resp->RemitterCode);
                }
                $custInfo = $object->getCustomerDetails($param);
                $amount = 0;
                if ($custInfo == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                } elseif($custInfo['status'] == STATUS_BLOCKED) {
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG;
                    return $responseObj;
                }else{
               
                    $remitter_cust_id = $custInfo['rat_customer_id'];
                // Transfer Fund query
               
                $walletTransfer = new Remit_Ratnakar_WalletTransfer();
                $tansferDetails = $walletTransfer->getWalletTransferDetails(array('txn_code' => (string) $resp->AckNo,'product_id' => (string) $resp->ProductCode,'remitter_cust_id' => $remitter_cust_id, 'txnrefnum' => (string) trim($resp->TransactionRefNo)));
                if (empty($tansferDetails)) {
                    $baseTxn = new BaseTxn();
                   $txnCode = $baseTxn->generateTxncode();
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->AckNo = $txnCode;
                    $responseObj->ResponseCode = ErrorCodes::QUERY_TRANSFER_FAILURE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::QUERY_TRANSFER_FAILURE_MSG;
                    return $responseObj;
                }
                $curCode = CURRENCY_INR;
                $amount = Util::convertIntoPaisa($tansferDetails['amount']);
                $responseObj = new stdClass();

                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                $responseObj->AckNo = (string) $resp->AckNo;
                $responseObj->RemitterCode = (string) trim($resp->RemitterCode);
                $responseObj->RemitterWalletCode = $tansferDetails['remitter_wallet_code'];
                $responseObj->BeneficiaryEmail = $tansferDetails['bene_email'];
                $responseObj->BeneficiaryMobile = $tansferDetails['bene_mobile'];
                $responseObj->BeneficiaryWalletCode = $tansferDetails['bene_wallet_code'];
                $responseObj->Amount = $amount;
                $responseObj->TransactionStatus = ucwords($tansferDetails['status']);
                $responseObj->ResponseCode = self::QUERY_TRANSFER_SUCCESS_CODE;
                $responseObj->ResponseMessage = self::QUERY_TRANSFER_SUCCESS_MSG;

                return $responseObj;
            }                  

            } catch (App_Exception $e) {
               App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::QUERY_TRANSFER_FAILURE_CODE;
                    $message = ErrorCodes::QUERY_TRANSFER_FAILURE_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::QUERY_TRANSFER_FAILURE_CODE;
                    $message = ErrorCodes::QUERY_TRANSFER_FAILURE_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
        }
    }

    public function QueryTransactionRequest() {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);


            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }
            Validator_Ratnakar::chkallParams($resp);
            
            if((string) trim($resp->AckNo) == '' && (string) trim($resp->TxnNo) == '') {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_ACK_TXN_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_ACK_TXN_NO_CODE);
                 
            } else if((string) trim($resp->AckNo) != '' && (string) trim($resp->TxnNo) == '') {
                Validator_Ratnakar::queryrefnoValidation((string) trim($resp->AckNo));
            } else if((string) trim($resp->AckNo) == '' && (string) trim($resp->TxnNo) != '') {
                Validator_Ratnakar::txnNumValidation((string) trim($resp->TxnNo));
            } else {
                Validator_Ratnakar::txnNumValidation((string) trim($resp->TxnNo));
                Validator_Ratnakar::queryrefnoValidation((string) trim($resp->AckNo));
            }

            try {
                    
                $obj = new Corp_Ratnakar_Cardload();
                $productConst = $this->getProductConstant();
                $productModel = new Products();
                $productID = $productModel->getProductIDbyConst($productConst); 
                $loadDetails = $obj->getLoadDetails(array('txn_code' => (string) trim($resp->AckNo),'product_id'=>$productID, 'txn_no' => (string) trim($resp->TxnNo)));
                $amount = '0';
                $baseTxn = new BaseTxn();
                $ackno = $baseTxn->generateTxncode();
                if ($loadDetails == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_WRONG_ACK_TXN_NO_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_WRONG_ACK_TXN_NO_MSG;
                    return $responseObj;
                }else{
                    $curCode = CURRENCY_INR;
                    if(strtoupper($loadDetails['txn_identifier_type']) == TXN_IDENTIFIER_TYPE_PARTNER){
                        $memberIDCardNo = $loadDetails['partner_ref_no'];
                    }elseif(strtoupper($loadDetails['txn_identifier_type']) == TXN_IDENTIFIER_TYPE_MOBILE){
                        $memberIDCardNo = $loadDetails['mobile'];
                    }else{
                        $memberIDCardNo = '';   
                    }
                     $amount = Util::convertIntoPaisa($loadDetails['amount']);
                     $loadStatus = $loadDetails['status']; 
                     $loadMode = $loadDetails['mode']; 
                     if( ($loadStatus!= STATUS_DEBITED) ){
                        
                                if( ($loadStatus == STATUS_LOADED) ){
                                       $loadStatus = STATUS_SUCCESS;
                                }
                               $return_array = new ArrayObject();
                               $responseObj = new stdClass();

                               $responseObj->SessionID = (string) $resp->SessionID;
                               $responseObj->TxnNo = $loadDetails['txn_no'];
                               $responseObj->AckNo = $loadDetails['txn_code'];
                               $responseObj->ProductCode = $loadDetails['product_id'];
                               $responseObj->TxnIdentifierType = strtoupper($loadDetails['txn_identifier_type']);
                               $responseObj->MemberIDCardNo = $memberIDCardNo;
                               $responseObj->ResponseCode = self::QUERY_TRANSACTION_REQUEST_SUCCESS_CODE;
                               $responseObj->ResponseMessage = self::QUERY_TRANSACTION_REQUEST_SUCCESS_MSG;
                               $responseObj->NoOfRecords = FAILED_RECORD_NUM;

                               $transactionDetail = new stdClass();
                               $transactionDetail->WalletCode = $loadDetails['wallet_code'];
                               $transactionDetail->Amount = $amount;
                               if($loadDetails['narration'] !=''){
                               $transactionDetail->Narration = $loadDetails['narration'];
                               }
                               $transactionDetail->TxnIndicator = strtoupper($loadMode);
                               $transactionDetail->TransactionStatus = $loadStatus;
                               
                               $transactionDetail = new SoapVar($transactionDetail, SOAP_ENC_OBJECT, null, null, 'TransactionDetail');
                               $return_array->append($transactionDetail);
                               
                               $responseObj->TransactionDetails = new SoapVar($return_array, SOAP_ENC_OBJECT, NULL, NULL, 'TransactionDetails');
                               return $responseObj;
                    }
                     $custLoadInfo = array(
                      'product_id' =>  $productID,
                      'customer_master_id'=> $loadDetails['customer_master_id'], 
                      'txn_code' => $loadDetails['txn_code']  
                    );
                    
                    $loadDetailResponse = $obj->getCustLoadTransactions($custLoadInfo);
                    if (empty($loadDetailResponse)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = $loadDetails['txn_code'];
                    $responseObj->TxnNo = $loadDetails['txn_no'];
                    $responseObj->ResponseCode = ErrorCodes::ERROR_QUERY_TRANSACTION_REQUEST_FAILURE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_QUERY_TRANSACTION_REQUEST_FAILURE_MSG;
                    return $responseObj;
                }
                /*
                 * Getting sum of all wallet balance and convert into paisa
                 */
                   
                    $return_array = new ArrayObject();
                    $responseObj = new stdClass();

                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = $loadDetails['txn_code'];
                    $responseObj->TxnNo = $loadDetails['txn_no'];
                    $responseObj->ProductCode = $loadDetails['product_id'];
                    $responseObj->TxnIdentifierType = strtoupper($loadDetails['txn_identifier_type']);
                    $responseObj->MemberIDCardNo = $memberIDCardNo;
                    $responseObj->ResponseCode = self::QUERY_TRANSACTION_REQUEST_SUCCESS_CODE;
                    $responseObj->ResponseMessage = self::QUERY_TRANSACTION_REQUEST_SUCCESS_MSG;
                    $responseObj->NoOfRecords = count($loadDetailResponse);
                    
                    foreach ($loadDetailResponse as $key=>$transValue){
                           if( ($loadStatus== STATUS_DEBITED) ){
                                if($transValue['is_reversal'] == REVERSAL_FLAG_YES){
                                 $loadStatus = STATUS_REFUND;   
                                }else{
                                 $loadStatus = STATUS_SUCCESS;   
                                }
                                
                            }
                            
                            $amount = Util::convertIntoPaisa($transValue['amount']);
                            $transactionDetail = new stdClass();
                            $transactionDetail->WalletCode = $transValue['wallet_code'];
                            $transactionDetail->Amount = $amount;
                            if($transValue['description'] !=''){
                            $transactionDetail->Description = $transValue['description'];
                            }
                            $transactionDetail->TxnIndicator = strtoupper($loadMode);
                            $transactionDetail->TransactionStatus = $loadStatus;
                            $transactionDetail = new SoapVar($transactionDetail, SOAP_ENC_OBJECT, null, null, 'TransactionDetail');
                            $return_array->append($transactionDetail);
                    }
                     $responseObj->TransactionDetails = new SoapVar($return_array, SOAP_ENC_OBJECT, NULL, NULL, 'TransactionDetails');
                              
                    return $responseObj;
                    
                } // Close else
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_QUERY_TRANSACTION_REQUEST_FAILURE_CODE;
                    $message = ErrorCodes::ERROR_QUERY_TRANSACTION_REQUEST_FAILURE_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_QUERY_TRANSACTION_REQUEST_FAILURE_CODE;
                    $message = ErrorCodes::ERROR_QUERY_TRANSACTION_REQUEST_FAILURE_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
        }
    }

    public function RemittanceTransactionRequest() {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);


            if( !isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
             }
            Validator_Ratnakar::chkallParams($resp); 
            //	Amount valid check  
            if ((string) $resp->Amount != '') {
                if (!ctype_digit((string) trim($resp->Amount)) || (trim($resp->Amount) < 1) ) {
                   throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_CODE);
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_CODE);
            }

            Validator_Ratnakar::productcodeValidation((string) trim($resp->ProductCode), $this->getProductConstant());
            Validator_Ratnakar::txnrefnoValidation((string) trim($resp->TransactionRefNo));
            if((string) trim($resp->Narration)!=''){
            Validator_Ratnakar_Customer::transactionNarrationValidation((string) trim($resp->Narration));
            }
            Validator_Ratnakar_Beneficiary::remitterwalletcodeValidation((string) trim($resp->WalletCode));
            Validator_Ratnakar_Beneficiary::remittancetypeValidation((string) trim($resp->RemittanceType));
            Validator_Ratnakar_Beneficiary::beneCodeValidation((string) trim($resp->BeneficiaryCode));

            // Check OTP
            $OTPrequest = TRUE; // Default OTP setting
            if($this->getOTPRequestConstant() == 'false'){
                 $OTPrequest = FALSE;
             }
             
            $otp = (string) trim($resp->OTP);
            if($OTPrequest){
                if ($otp != '') {
                    if (strlen($otp) != 6 || !(ctype_digit($otp))) {
                       throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
                    }
                }
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
                        Validator_Ratnakar::mobileValidation((string) trim($resp->RemitterCode));
                    } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                        Validator_Ratnakar_Customer::partnerRefnoValidation((string) trim($resp->RemitterCode));
                  }
                }
            } else {
               throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE);
            }

            try {
                $refObject = new Reference();
                $object = new Corp_Ratnakar_Cardholders();
                $beneficiaryModel = new Remit_Ratnakar_Beneficiary();
                $remitRequest = new Remit_Ratnakar_Remittancerequest();
                $masterPurseDetails = new MasterPurse();
                
                $param ['product_id'] = (string) trim($resp->ProductCode);
                $baseTxn = new BaseTxn();
                $ackno = $baseTxn->generateTxncode();
                if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOB)) {
                    $param['mobile'] = (string) trim($resp->RemitterCode);
                } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                    $param['partner_ref_no'] = (string) trim($resp->RemitterCode);
                }
                
                $masterPurseDetails = $masterPurseDetails->getPurseInfo($param);
                if(empty($masterPurseDetails)){
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_REMITTANCE_FEATURE_DISABLED_CODE ;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_REMITTANCE_FEATURE_DISABLED_MSG;
                    return $responseObj;
                }
                
                $custInfo = $object->getCustomerDetails($param);

                if ($custInfo == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                } elseif($custInfo['status'] == STATUS_BLOCKED) {
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG;
                    return $responseObj;
                }
                if($OTPrequest){
                    if ($otp != '') {
                        $responseOTP = $refObject->verifyCustomerOTPAPI(array(
                            'request_type' => 'I',
                            'otp' => (string) trim($resp->OTP),
                            'mobile' => (string) $custInfo['mobile'],
                        ));
                    
                        if ($responseOTP == FALSE) {
                            $responseObj = new stdClass();
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->AckNo = $ackno;
                            $responseObj->ResponseCode = ErrorCodes::OTP_INVALID_RESPONSE_CODE;
                            $responseObj->ResponseMessage = ErrorCodes::OTP_INVALID_RESPONSE_MSG;
                            return $responseObj;
                        }
                    }
                }
                 
                 $respTrans = $remitRequest->checkDuplicateRemittanceTransNum(array(
                    'product_id' => (string) trim($resp->ProductCode),
                    'txnrefnum' => (string) trim($resp->TransactionRefNo),
                ));
                
                if (!empty($respTrans['id'])) {
                    $ackno = $respTrans['txncode'];
                    return self::Exception(ErrorCodes::ERROR_EDIGITAL_TRAN_REF_NO_USED_MSG, ErrorCodes::ERROR_EDIGITAL_TRAN_REF_NO_USED_CODE,$ackno);
                }
                
                 $objectRelation = new ObjectRelations();
                 $remitterId = $objectRelation->getToObjectInfo($custInfo['id'], RAT_MAPPER);
               
                 $beneId = $beneficiaryModel->getBeneficiaryDetailsByCode((string) trim($resp->BeneficiaryCode), $remitterId['to_object_id']);           
 
                if(!empty($beneId)) {
                    // Remittance
                    $remittancerequest = new Remit_Ratnakar_Remittancerequest();

                    $remittanceData['amount'] = (string) trim($resp->Amount);
                    $remittanceData['product_id'] = (string) trim($resp->ProductCode);
                    $remittanceData['fund_holder'] = REMIT_FUND_HOLDER_REMITTER;
                    $remittanceData['agent_id'] = $this->getAgentConstant();
                    $remittanceData['remitter_id'] = $remitterId['to_object_id'];
                    $remittanceData['beneficiary_id'] = $beneId['id'];
                    $remittanceData['rat_customer_id'] = $custInfo['rat_customer_id'];
                    $remittanceData['customer_master_id'] = $custInfo['customer_master_id'];
                    $remittanceData['txnrefnum'] = (string) trim($resp->TransactionRefNo);
                    $remittanceData['ops_id'] = 0;
                    $remittanceData['product_id'] = (string) trim($resp->ProductCode);
                    $remittanceData['date_created'] = new Zend_Db_Expr('NOW()');
                    $remittanceData['fee'] = 0;
                    $remittanceData['service_tax'] = 0;
                    $remittanceData['status'] = STATUS_INCOMPLETE;
                    $remittanceData['sender_msg'] = (string) trim($resp->Narration);
                    $remittanceData['wallet_code'] = (string) trim($resp->WalletCode);
                    $remittanceData['bank_product_const'] = $this->getBankProductConstant();
                    $remittanceData['manageType'] = $this->getManageTypeConstant();
                    $remittanceData['card_number'] = $custInfo['card_number'];
                    $remittanceData['channel'] = CHANNEL_API;
                    
                    $flg = $remittancerequest->remittanceTransaction($remittanceData);

                    if ($flg['response'] == FALSE) {
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                        $responseObj->AckNo = $ackno;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_REMITTANCE_TRANSACTION_FAILURE_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_REMITTANCE_TRANSACTION_FAILURE_MSG;
                        return $responseObj;
                    }
                    $curCode = CURRENCY_INR;

                    $responseObj = new stdClass();
                    
                    $sendSMS = TRUE; // Default Send SMS setting
                   if($this->getSendSMSConstant() == SEND_SMS_FALSE){
                     $sendSMS = FALSE;
                   }
                   if($sendSMS){

                    if (strtolower(trim($resp->SMSFlag)) == strtolower(FLAG_Y)) {
                        //Send SMS  
                        $params = array(
                        'product_id' => $custInfo['product_id'],
                        'mobile' => $custInfo['mobile'],
                        'amount' => Util::convertToRupee((string) $resp->Amount),
                        'bene_name' => $custInfoTo['first_name'].' '.$custInfoTo['last_name'],
                        'wallet_code' => (string) $resp->RemitterWalletCode,
                        'ref_num' => (string) $resp->TransactionRefNo,
                        );
                        $object->generateSMSDetails($params, $smsType = REMITTANCE);
                    }
                   }
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->AckNo = $flg['txn_code'];
                    $responseObj->ResponseCode = self::REMITTANCE_TRANSACTION_SUCCESS_CODE;
                    $responseObj->ResponseMessage = self::REMITTANCE_TRANSACTION_SUCCESS_MSG;
                    // Update otp entry 
                    if($OTPrequest){
                        if ($otp != '') {
                        $upadteOTP = $refObject->updateCustomerOTPAPI(array(
                        'request_type' => 'I',
                        'id' => $responseOTP['id'],
                         ));
                        }
                    }
                    return $responseObj;
                } else {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->AckNo = $ackno;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_REMITTANCE_TXN_BENEFICIARY_FAILURE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_REMITTANCE_TXN_BENEFICIARY_FAILURE_MSG;
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
                
                if(strlen($code) > 3) {
                    $code = $this->filterErrorCodes($code);
                }
                
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
                
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_REMITTANCE_TRANSACTION_FAILURE_CODE;
                    $message = ErrorCodes::ERROR_REMITTANCE_TRANSACTION_FAILURE_MSG;
                }
                
                if(strlen($code) > 3) {
                    $code = $this->filterErrorCodes($code);
                }
                
                $this->_soapServer->_getLogger()->__setException($message);
               return self::Exception($message, $code);
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
            Validator_Ratnakar::chkallParams($resp);
            /*
             * Product Code Validation
             */
            Validator_Ratnakar::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());


            /*
             * Query Reference Numver Validation
             */
            if((string) trim($resp->AckNo) != '') {
            Validator_Ratnakar::queryrefnoValidation((string) trim($resp->AckNo));
            }
           
            // Check Remitter Flag
            $remitterflag = (string) trim($resp->RemitterFlag);
            $remitterflag = strtolower($remitterflag);

            if ($remitterflag != '') {
                if (strlen($remitterflag) > 1 || ( $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE) && $remitterflag != strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER) )) {
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

            try {
              //  $strTansRefNo = (string) trim($resp->TransactionRefNo);
                $ackno = (string) trim($resp->AckNo);
                if($ackno == ''){
                $baseTxn = new BaseTxn();
                $ackno = $baseTxn->generateTxncode();
                }
                $masterPurseDetails = new MasterPurse();
                
             //   $params['txnrefnum'] = (string) $strTansRefNo; //$resp->TransactionRefNo;
              //  $params['QueryReqNo'] = (string) $strQueryReqNo; //$resp->PartnerRefNo;
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
                
                $masterPurseDetails = $masterPurseDetails->getPurseInfo($params);
                if(empty($masterPurseDetails)){
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_QUERY_BENEFICIARY_LIST_FAILURE_RESPONSE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_QUERY_BENEFICIARY_LIST_FAILURE_RESPONSE_MSG;
                    return $responseObj;
                }
                
                $obj = new Corp_Ratnakar_Cardholders();
                $beneficiaryModel = new Remit_Ratnakar_Beneficiary();
                /*
                 * Getiing customer Detail  
                 */
                $cardholderData = $obj->getCardholderInfo($params);

                /*
                 *  Featching Remitter Id from object relation
                 */
                if($cardholderData['cardholder_status'] == STATUS_BLOCKED) {
                    return self::Exception(ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG, ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE);
                } elseif (!empty($cardholderData)) {
                    $objectRelation = new ObjectRelations();
                    $remitterId = $objectRelation->getToObjectInfo($cardholderData['id'], RAT_MAPPER);
                    if (!empty($remitterId)) {
                        $bene_param['status'] = STATUS_ACTIVE;
                        $bene_param['remitter_id'] = $remitterId['to_object_id'];
                        $beneInfo = $beneficiaryModel->getBeneInfo($bene_param);
                        $beneDetails =   Util::toArray($beneInfo);
                       // $txnCode = $obj->getTxncode();
                        $m = new \App\Messaging\Corp\Ratnakar\Operation();
                        if (!empty($beneDetails)) {
                         
                            $no = 1; 
                            $responseObj = new stdClass();
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->AckNo = $ackno;
                            $responseObj->RemitterCode = (string) $resp->RemitterCode;
                            $responseObj->BeneficiaryCount =  count($beneDetails);;
                            $responseObj->ResponseCode = self::QUERY_BENEFICIARY_LIST_SUCC_CODE;
                            $responseObj->ResponseMessage = self::QUERY_BENEFICIARY_LIST_SUCC_MSG;
                            
                            
                            $return_array = new ArrayObject();
                            
                            foreach ($beneDetails as $transValue) {
                                $beneDetail  = new stdClass();
                                $beneDetail->BeneficiaryCode = $transValue['bene_code'];
                                $beneDetail->Name = $transValue['first_name'];
                                $beneDetail->BankName = $transValue['bank_name'];
                                $beneDetail->BankIfscode = $transValue['ifsc_code'];
                                $beneDetail->BankAccountNumber = $transValue['bank_account_number'];
                                $bene_detail = new SoapVar($beneDetail, SOAP_ENC_OBJECT, null, null, 'BeneficiaryDetail');
                                $return_array->append($bene_detail);
                            }
                        
                            $responseObj->BeneficiaryDetails = new SoapVar($return_array, SOAP_ENC_OBJECT, NULL, NULL, 'BeneficiaryDetails');

                          return $responseObj;

                        }
                        else {
                            $errorMsg = $obj->getError();
                            $errorMsg = empty($errorMsg) ? ErrorCodes::ERROR_QUERY_BENEFICIARY_LIST_FAIL_MSG : $errorMsg;
                            $responseObj = new stdClass();
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->AckNo = $ackno;
                            $responseObj->ResponseCode = ErrorCodes::ERROR_QUERY_BENEFICIARY_LIST_FAIL_CODE;
                            $responseObj->ResponseMessage = $errorMsg;
                        }
                        return $responseObj;
                        //
                    } else {
                        return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE);
                    }
                } else {
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
                return self::Exception($message, $code);
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
                return self::Exception($message, $code);
        }
    }
    

    
     public function MiniStatementRequest() {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            if (!isset($resp->SessionID) || !$this->isLogin((string) $resp->SessionID)) {
                 return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }
            Validator_Ratnakar::chkallParams($resp);
            Validator_Ratnakar::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());
            Validator_Ratnakar_Customer::memberIdCardNumValidation((string) trim($resp->MemberIDCardNo));
            if((string) trim($resp->WalletCode) !=''){
            Validator_Ratnakar_Beneficiary::benewalletcodeValidation((string) trim($resp->WalletCode));
            }            
// Check SMS Flag
//            $smsflag = (string) trim($resp->SMSFlag);
//            $smsflag = strtolower($smsflag);
//
//            if ($smsflag != '') {
//                if (strlen($smsflag) > 1 || ( $smsflag != strtolower(self::SMS_FLAG_TYPE_YES) && $smsflag != strtolower(self::SMS_FLAG_TYPE_NO) )) {
//                    throw new Exception('SMS Flag is not valid');
//                }
//            } else {
//                throw new Exception('SMS Flag is mandatory');
//            }
            // Check TxnIdentifierType
            $txnidentifierflag = (string) trim($resp->TxnIdentifierType);
            $txnidentifierflag = strtolower($txnidentifierflag);

            if ($txnidentifierflag != '') {
                if (strlen($txnidentifierflag) > 3 || ( $txnidentifierflag != strtolower(self::TXN_IDENTIFIER_TYPE_MOB) && $txnidentifierflag != strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER) )) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_IDENTIFIRE_TYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_IDENTIFIRE_TYPE_CODE);
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_IDENTIFIRE_TYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_IDENTIFIRE_TYPE_CODE);
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
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                }
                $objectRelation = new ObjectRelations();
                $remitterId = $objectRelation->getToObjectInfo($custInfo['id'], RAT_MAPPER);
                
                $custloadArr = array(
                  'customer_id'=> $custInfo['id'],
                  'customer_master_id'=> $custInfo['customer_master_id'], 
                  'product_id'=> $param['product_id']  
                );
                 $obj = new Corp_Ratnakar_CustomerPurse();
                if ( ((string) trim($resp->WalletCode) != '') ) {
                    
                    if( strtoupper((string) trim($resp->WalletCode))== WALLET_WISE_BALANCE ){
                      $custloadArr['wallet_code'] = 'all';  
                    }else{
                       $isValidwallet = $obj->checkValidWallet((string) trim($resp->ProductCode),(string) trim($resp->WalletCode)); 
                        if (!$isValidwallet) {
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_CODE;
                        $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_MSG;
                        return $responseObj;
                        }
                       $custloadArr['wallet_code'] = (string) trim($resp->WalletCode);   
                    }
                }else{
                  $custloadArr['wallet_code'] = '';   
                }
                $transResponse = array();
                $custloadArr['remitter_id'] = $remitterId['to_object_id']; 
                $obj = new Corp_Ratnakar_Cardload();
                $loadResponse = $obj->getCustTransactions($custloadArr);
                
                if (!empty($loadResponse)) {
                    foreach($loadResponse as $key=>$custRemittanceTrans){
                      
                            $transResponse[$key]['txn_date'] = $custRemittanceTrans['txn_date'];
                            $transResponse[$key]['wallet_code'] = $custRemittanceTrans['code'];
                            $transResponse[$key]['mode'] = strtoupper($custRemittanceTrans['mode']);
                            $transResponse[$key]['amount'] = $custRemittanceTrans['amount'];
                            $transResponse[$key]['txn_type'] = $custRemittanceTrans['txn_type'];
                                switch ($custRemittanceTrans['txn_type']) {
                                case TXNTYPE_REMITTANCE:
                                    $transResponse[$key]['description'] = $custRemittanceTrans['remittance_description'];
                                    $transResponse[$key]['txn_no'] = $custRemittanceTrans['remittance_txn_no'];
                                    $transResponse[$key]['status'] = $custRemittanceTrans['remittance_status'];
                                break;
                                case TXNTYPE_WALLET_TOWALLET_FUND_TRANSFER:
                                    $transResponse[$key]['description'] = $custRemittanceTrans['wallet_description'];
                                    $transResponse[$key]['txn_no'] = $custRemittanceTrans['wallet_txn_no'];
                                    $transResponse[$key]['status'] = $custRemittanceTrans['wallet_status'];
                                break;
                                case TXNTYPE_RAT_CORP_CORPORATE_LOAD:
                                    $transResponse[$key]['description'] = $custRemittanceTrans['load_description'];
                                    $transResponse[$key]['txn_no'] = $custRemittanceTrans['load_txn_no'];
                                    $transResponse[$key]['status'] = $custRemittanceTrans['load_status'];
                                break;
                                
                                default:
                                    $transResponse[$key]['description'] = $custRemittanceTrans['load_description'];
                                    $transResponse[$key]['txn_no'] = $custRemittanceTrans['load_txn_no'];
                                    $transResponse[$key]['status'] = $custRemittanceTrans['load_status'];
                                } 
                    }
                }
                
                if (empty($transResponse)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnIdentifierType = (string) trim($resp->TxnIdentifierType);
                    $responseObj->MemberIDCardNo = (string) trim($resp->MemberIDCardNo);
                    $responseObj->ResponseCode = ErrorCodes::ERROR_MINISTT_FAILED_RESPONSE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_MINISTT_FAILED_RESPONSE_MSG;
                    return $responseObj;
                }
              
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnIdentifierType = (string) trim($resp->TxnIdentifierType);
                    $responseObj->MemberIDCardNo = (string) trim($resp->MemberIDCardNo);
                    $responseObj->ResponseCode = self::MINISTT_SUCCSSES_RESPONSE_CODE;
                    $responseObj->ResponseMessage = self::MINISTT_SUCCSSES_RESPONSE_MSG;
                    $responseObj->NumberOfRecords = count($transResponse);
//                  
                     $return_array = new ArrayObject();
                            
                            foreach ($transResponse as $key=>$transValue){
                                
                                $amount = Util::convertIntoPaisa($transValue['amount']);
                                $miniStatementDetail = new stdClass();
                                $miniStatementDetail->DateTime = $transValue['txn_date']; 
                                $miniStatementDetail->Description = $transValue['description'];
                                $miniStatementDetail->TxnNo = $transValue['txn_no'];
                                $miniStatementDetail->TxnIndicator = strtoupper($transValue['mode']);
                                $miniStatementDetail->Currency = 'INR';
                                $miniStatementDetail->Amount = $amount;
                                $miniStatementDetail->WalletCode = $transValue['wallet_code'];
                                $miniStatementDetail = new SoapVar($miniStatementDetail, SOAP_ENC_OBJECT, null, null, 'MiniStatementDetail');
                                $return_array->append($miniStatementDetail);
                            }
                        
                            $responseObj->MiniStatementDetails = new SoapVar($return_array, SOAP_ENC_OBJECT, NULL, NULL, 'MiniStatementDetails');
                            return $responseObj;
                             
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_MINISTT_FAILED_RESPONSE_CODE;
                    $message = ErrorCodes::ERROR_MINISTT_FAILED_RESPONSE_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_MINISTT_FAILED_RESPONSE_CODE;
                    $message = ErrorCodes::ERROR_MINISTT_FAILED_RESPONSE_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
        }
    }
    
    
    public function WalletBalanceEnquiryRequest(){
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            if (!isset($resp->SessionID) || !$this->isLogin((string) $resp->SessionID)) {
                 return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }
            Validator_Ratnakar::chkallParams($resp);
            Validator_Ratnakar::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());
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
            Validator_Ratnakar_Customer::memberIdCardNumValidation((string) trim($resp->MemberIDCardNo));
            if((string) trim($resp->WalletCode) !=''){
            Validator_Ratnakar_Customer::walletCodeValidation((string) trim($resp->WalletCode));
            }  
            
            try {

		$object = new Corp_Ratnakar_Cardholders();
                $objLoad = new Corp_Ratnakar_Cardload();
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
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                } elseif($custInfo['cardholder_status'] == STATUS_BLOCKED) {
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG;
                    return $responseObj;
                }else{
                /*
                 * doWalletBalanceAPI : getting wallet balance list in an array
                 */
                $walletCode = (string) trim($resp->WalletCode);
                if(strtoupper($walletCode) == WALLET_WISE_BALANCE){
                  $walletCode = '';  
                }    
                $custParams = array(
                  'product_id' =>  (string) trim($resp->ProductCode),
                  'customer_master_id' =>  $custInfo ['customer_master_id'],
                  'rat_customer_id' =>  $custInfo ['rat_customer_id'],
                  'wallet_code' =>  $walletCode,  
                );    
               
                $walletResponses = $objLoad->doWalletBalanceAPI($custParams);
               
                if (empty($walletResponses)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnIdentifierType = (string) trim($resp->TxnIdentifierType);
                    $responseObj->MemberIDCardNo = (string) trim($resp->MemberIDCardNo);
                    $responseObj->ResponseCode = ErrorCodes::ERROR_WALLET_BAL_ENQ_FAILED_RESPONSE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_WALLET_BAL_ENQ_FAILED_RESPONSE_MSG;
                    return $responseObj;
                }
                /*
                 * Getting sum of all wallet balance and convert into paisa
                 */
                    $allWalletBalance = array_column($walletResponses, 'wallet_balance');
                    $totalWalletBalance = array_sum($allWalletBalance);
                    $totalWalletBalance = Util::convertIntoPaisa($totalWalletBalance);
                    
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnIdentifierType = (string) trim($resp->TxnIdentifierType);
                    $responseObj->MemberIDCardNo = (string) trim($resp->MemberIDCardNo);
                    $responseObj->AvailableBalance = $totalWalletBalance;
                    $responseObj->ResponseCode = self::WALLET_BAL_ENQ_SUCCSSES_RESPONSE_CODE;
                    $responseObj->ResponseMessage = self::WALLET_BAL_ENQ_SUCCSSES_RESPONSE_MSG;
                    $responseObj->NumberOfRecords = count($walletResponses);
                    
                    $return_array = new ArrayObject();
                    foreach ($walletResponses as $key=>$transValue){
                            $amount = Util::convertIntoPaisa($transValue['wallet_balance']);
                            $walletDetail = new stdClass();
                            if($transValue['date_created'] !=''){
                            $walletDetail->DateTime = $transValue['date_created']; 
                            }
                            if($transValue['description'] !=''){
                            $walletDetail->Description = $transValue['description'];
                            }
                            if($transValue['txn_no'] !=''){
                            $walletDetail->TxnNo = $transValue['txn_no'];
                            }
                            $walletDetail->Amount = $amount;
                            $walletDetail->Currency = 'INR';
                            if($transValue['date_expiry'] !=''){
                            $walletDetail->ExpiryDate = $transValue['date_expiry'];
                            }
                             if($transValue['voucher_num'] !=''){
                            $walletDetail->VoucherNumber = $transValue['voucher_num'];
                            }
                            $walletDetail->TxnIndicator = strtoupper(CR);

                            $walletDetail->WalletCode = $transValue['wallet_code'];

                            $walletDetail = new SoapVar($walletDetail, SOAP_ENC_OBJECT, null, null, 'WalletDetail');
                            $return_array->append($walletDetail);
                    }
                    $responseObj->WalletDetails = new SoapVar($return_array, SOAP_ENC_OBJECT, NULL, NULL, 'WalletDetails');
                    return $responseObj;
                }
                
            } catch (Exception $e) {
                    App_Logger::log(serialize($e), Zend_Log::ERR);
                    $code = $e->getCode();
                    $message = $e->getMessage();
                    if( (empty($code) ) || (empty($message)) ) {
                        $code = ErrorCodes::ERROR_WALLET_BAL_ENQ_FAILED_RESPONSE_CODE;
                        $message = ErrorCodes::ERROR_WALLET_BAL_ENQ_FAILED_RESPONSE_MSG;
                    }
                    $this->_soapServer->_getLogger()->__setException($message);
                    return self::Exception($message, $code);
            }
            
            
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_WALLET_BAL_ENQ_FAILED_RESPONSE_CODE;
                    $message = ErrorCodes::ERROR_WALLET_BAL_ENQ_FAILED_RESPONSE_MSG;
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
        }
    }
    
    
    public function DebitTransactionRequest() {
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            $voucherIndicator = '';
            $voucherCode = '';
            
            if( !isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
                  return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
             }
            Validator_Ratnakar::chkallParams($resp);
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
                if (!ctype_digit((string) trim($resp->Amount)) || (trim($resp->Amount) < 1) ) {
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
            if((string) trim($resp->Narration)!=''){
            Validator_Ratnakar_Customer::transactionNarrationValidation((string) trim($resp->Narration));
            }
            Validator_Ratnakar_Beneficiary::remitterwalletcodeValidation((string) trim($resp->WalletCode));
            
            // Check OTP
            $OTPrequest = TRUE; // Default OTP setting
            if($this->getOTPRequestConstant() == 'false'){
                 $OTPrequest = FALSE;
             }
            $otp = (string) trim($resp->OTP);
            if($OTPrequest){
                if ($otp != '') {
                    if (strlen($otp) != 6 || !(ctype_digit($otp))) {
                       throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_CODE);
                    }
                }
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
            $txnidentifierflag = (string) trim($resp->TxnIdentifierType);
            $txnidentifierflag = strtolower($txnidentifierflag);

            if ($txnidentifierflag != '') {
                if (strlen($txnidentifierflag) > 3 || ( $txnidentifierflag != strtolower(self::TXN_IDENTIFIER_TYPE_MOB) && $txnidentifierflag != strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER) )) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_CODE);
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_CODE);
            }
            $loadExiryDate = '';
            $loadExpiry = (string) trim($resp->Filler1);
            if($loadExpiry !=''){
                Validator_Ratnakar_Customer::loadExpiryValidation($loadExpiry);
                $loadExpiry_str = strtotime($loadExpiry);
                $loadExiryDate = date('Y-m-d H:i:s',$loadExpiry_str);
               }
            // Check Filler2 and Filler3
            $filler2 = (string) trim($resp->Filler2);
            $filler2 = strtolower($filler2);
            $filler3 = (string) trim($resp->Filler3);
            
            if( ( ($filler2 !='') && ($filler2 != strtolower(self::FLAG_GIFT_VOUCHER)) ) || ( ($filler2 =='') && ($filler3 != '') ) ){
                  throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER2_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER2_CODE);
                  
            }elseif( ( ($filler2 !='') && ($filler3 == '') )){
                  throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER3_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER3_CODE);
                  
            }elseif( ( ($filler2 !='') && ($filler3 != '') )){
                    if (strlen($filler3) > 20 || !(ctype_alnum($filler3))) {
                        throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER3_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER3_CODE);
                    }
                   $voucherIndicator = $filler2;
                   $voucherCode = (string) trim($resp->Filler3);
            }
            
            if(strtolower((string) trim($resp->TxnIndicator)) != TXN_MODE_DR){
                     throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_TXNINDICATOR_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TXNINDICATOR_CODE);
           
            }elseif( (strtolower((string) trim($resp->TxnIndicator)) == TXN_MODE_DR) && (  ((string) trim($resp->Filler1) !='' ) || ((string) trim($resp->Filler4) !='')  || ((string) trim($resp->Filler5) !='') ) ){
                     throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_LOAD_EXPIRY_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_LOAD_EXPIRY_CODE);   
//                       
            }
            
            try {

                $refObject = new Reference();
                $object = new Corp_Ratnakar_Cardholders();
                $baseTxn = new BaseTxn();
                $masterPurseDetails = new MasterPurse();
                $ackno = $baseTxn->generateTxncode();
               
                $param['product_id'] = (string) trim($resp->ProductCode);

                if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_MOB)) {
                    $param['mobile'] = (string) trim($resp->MemberIDCardNo);
                } else if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER)) {
                    $param['partner_ref_no'] = (string) trim($resp->MemberIDCardNo);
                }
                /*
                 * Validation for Load wallet Conditions
                 */
                
                $custInfo = $object->getCustomerDetails($param);
                
               
                if ($custInfo == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                } elseif($custInfo['status'] == STATUS_BLOCKED) {
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG;
                    return $responseObj;
                }                
                
                if($OTPrequest){
                    if ($otp != '') {
                        $responseOTP = $refObject->verifyCustomerOTPAPI(array(
                            'request_type' => 'T',
                            'otp' => (string) trim($resp->OTP),
                            'mobile' => (string) $custInfo['mobile'],
                        ));
                    
                        if ($responseOTP == FALSE) {
                            $responseObj = new stdClass();
                            $responseObj->SessionID = (string) $resp->SessionID;
                            $responseObj->AckNo = $ackno;
                            $responseObj->ResponseCode = ErrorCodes::OTP_INVALID_RESPONSE_CODE;
                            $responseObj->ResponseMessage = ErrorCodes::OTP_INVALID_RESPONSE_MSG;
                            return $responseObj;
                        }
                    }
                }
                
                $obj = new Corp_Ratnakar_Cardload();
                $productConst = $this->getProductConstant();
                $productModel = new Products();
                $productID = $productModel->getProductIDbyConst($productConst);
                
                
                $loadDetails = $obj->getLoadDetails(array('txn_no' => (string) trim($resp->TxnNo),'product_id'=>$productID));
                if (!empty($loadDetails) ) {
                    $txnCode = $loadDetails['txn_code'];
                    return self::Exception(ErrorCodes::ERROR_EDIGITAL_TRAN_REF_NO_USED_MSG, ErrorCodes::ERROR_EDIGITAL_TRAN_REF_NO_USED_CODE,$txnCode);
                }else{
               // $baseTxn = new BaseTxn();
                $txnCode = $baseTxn->generateTxncode();   
                }

                if(($voucherIndicator != '') && ($voucherCode !='') ) {
                    $loadDetails = $obj->getVoucherDetails(array('voucher_num' => $voucherCode, 'product_id'=>$productID, 'mode'=>(string) trim($resp->TxnIndicator)));
                    if (!empty($loadDetails) ) {
                        $voucherNum = $loadDetails['voucher_num'];
                        return self::Exception(ErrorCodes::ERROR_EDIGITAL_WRONG_VOUCHER_NUMBER_MSG, ErrorCodes::ERROR_EDIGITAL_WRONG_VOUCHER_NUMBER_CODE,$voucherNum);
                        
                    }
                }
                
                
                //Do card load
               
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
                $params['sms_flag'] = $smsflag;
                $params['manageType'] = $this->getManageTypeConstant();
                $params['date_expiry'] = $loadExiryDate;
                $params['Filler1'] = (string) trim($resp->Filler1);
                $params['Filler2'] = (string) trim($resp->Filler2);
                $params['Filler3'] = (string) trim($resp->Filler3);
                $params['Filler4'] = (string) trim($resp->Filler4);
                $params['Filler5'] = (string) trim($resp->Filler5);
                $params['channel'] = CHANNEL_API;
                
                $bankInfo = $bankObject->getBankidByProductid($params['product_id']);
                if(!empty($bankInfo) ){
                 $params['bank_id'] = $bankInfo['bank_id'];    
                }else{
                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);   
                }
                $params['voucher_num'] = $voucherCode;
                
                $flg = $obj->doCardloadAPI($params);
               // $flg = $obj->doCardloadExpiryAPI($params)
;                
                if ($flg == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnNo = (string) trim($resp->TxnNo);
                    $responseObj->AckNo = $txnCode;
                    $responseObj->ResponseCode = ErrorCodes::DEBIT_TRANSACTION_REQUEST_FAILURE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::DEBIT_TRANSACTION_REQUEST_FAILURE_MSG;
                    return $responseObj;
                }else{
                     $customerMasterId =  $custInfo['customer_master_id'];
                      $custLoadInfo = array(
                      'product_id' =>  $productID,
                      'customer_master_id'=> $customerMasterId, 
                      'txn_code' => $txnCode  
                    );
                    
                   
                $loadDetailResponse = $obj->getCustDebitLoadTransactions($custLoadInfo);
                
                  if (empty($loadDetailResponse)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = $txnCode;
                    $responseObj->TxnNo = (string) $resp->TxnNo;
                    $responseObj->ResponseCode = ErrorCodes::DEBIT_TRANSACTION_REQUEST_FAILURE_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::DEBIT_TRANSACTION_REQUEST_FAILURE_MSG;
                    return $responseObj;
                }
                /*
                 * Getting sum of all wallet balance and convert into paisa
                 */
                   
                    $responseObj = new stdClass();

                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnNo = (string) $resp->TxnNo;
                    $responseObj->AckNo = $txnCode;
                    $responseObj->ResponseCode = self::TRANSACTION_REQUEST_SUCCESS_CODE;
                    $responseObj->ResponseMessage = self::DEBIT_TRANSACTION_REQUEST_SUCCESS_MSG;
                    $responseObj->NoOfRecords = count($loadDetailResponse);
                   
                    $return_array = new ArrayObject();
                    $curCode = CURRENCY_INR;
                    foreach ($loadDetailResponse as $key=>$transValue){
                            if($transValue['debit_amount']!=''){
                                 $amount = Util::convertIntoPaisa($transValue['debit_amount']);
                            }else{
                                 $amount = Util::convertIntoPaisa($transValue['amount']);    
                            }
                            $transactionDetail = new stdClass();
                            $transactionDetail->WalletCode = $transValue['wallet_code'];
                            $transactionDetail->Amount = $amount;
                            if($transValue['description'] !=''){
                                  $transactionDetail->Narration = $transValue['description'];
                            }
                            $transactionDetail->Currency = $curCode;
                            $transactionDetail->ExpiryDate = $transValue['date_expiry'];
                            $transactionDetail->VoucherNumber = $transValue['voucher_num'];
                            $transactionDetail = new SoapVar($transactionDetail, SOAP_ENC_OBJECT, null, null, 'DebitTransactionDetail');
                            $return_array->append($transactionDetail);
                    }
                    
                    $responseObj->DebitTransactionDetails = new SoapVar($return_array, SOAP_ENC_OBJECT, NULL, NULL, 'DebitTransactionDetails');
                   
                 
                if($OTPrequest){
                        if ($otp != '') {
                        $upadteOTP = $refObject->updateCustomerOTPAPI(array(
                        'request_type' => 'T',
                        'id' => $responseOTP['id'],
                         ));
                        }
                    }
                return $responseObj;
                
              }    
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::DEBIT_TRANSACTION_REQUEST_FAILURE_CODE;
                    $message = ErrorCodes::DEBIT_TRANSACTION_REQUEST_FAILURE_MSG;
                }
                if(strlen($code)>3){
                    $code = $this->filterErrorCodes($code);
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {

                App_Logger::log($e, Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::DEBIT_TRANSACTION_REQUEST_FAILURE_CODE;
                    $message = ErrorCodes::DEBIT_TRANSACTION_REQUEST_FAILURE_MSG;
                }
                if(strlen($code)>3){
                    $code = $this->filterErrorCodes($code);
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
        }
    
    }
   
    public function filterErrorCodes($errorCode) {
        $code= FALSE;
        switch ($errorCode) {
            case ErrorCodes::ERROR_INSUFFICIENT_AMOUNT:
                   $code = ErrorCodes::ERROR_EDIGITAL_AMOUNT_LESS_PER_TXN_CODE;
                break;
            case ErrorCodes::ERROR_INSUFFICIENT_BALANCE:
                   $code = ErrorCodes::ERROR_INSUFFICIENT_BALANCE_CODE;
                break;
            case ErrorCodes::ERROR_INSUFFICIENT_DATA_FOR_PROCESSING:
                   $code = ErrorCodes::ERROR_INSUFFICIENT_DATA_AUTHENTICATION_CODE;
                break;
            case ErrorCodes::ERROR_TRANSACTION_LIMIT:
                   $code = ErrorCodes::ERROR_EDIGITAL_MAX_AMOUNT_LIMIT_EXCEEDS_CODE;
                break;
            case ErrorCodes::ERROR_TRANSACTION_FREQUENCY:
                   $code = ErrorCodes::ERROR_EDIGITAL_MAX_TXN_LIMIT_EXCEEDS_CODE;
                break;
            default:
                $code = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                break;            
            
        }
        return $code;
    }


    public function CardMappingRequest() {//Do not add comments for method summary
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
            Validator_Ratnakar::txnrefnoValidation((string) trim((string) $resp->TransactionRefNo));

            /*
             * Product code validation
             */
            Validator_Ratnakar::productcodeValidation((string) trim($resp->ProductCode), $this->getProductConstant());
            
            /*
             * Check Remitter Flag
             */
            
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
            
            if((string) trim($resp->CardNumber) != ''){
                Validator_Ratnakar::cardnoValidation((string) trim($resp->CardNumber)); 
            }
            
            /*
             * Check CardPackId Validation
             */
            Validator_Ratnakar::cardpackidValidation((string) trim($resp->CardPackId)); 

            try {
                
                $cardholderModel = new Corp_Ratnakar_Cardholders();
                $bankObject = new Banks();
                
                 // Get Customer details
                $param['product_id'] = (string) trim($resp->ProductCode);
                $param['status'] = STATUS_ACTIVE;
                
                if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOB)) {
                    $param['mobile'] = (string) trim($resp->RemitterCode);
                } else if ($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)) {
                    $param['partner_ref_no'] = (string) trim($resp->RemitterCode);
                }

                // get cardholder data	 
                $custInfo = $cardholderModel->getCardholderInfo($param);
                if($custInfo == FALSE) { 
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                } elseif($custInfo['card_number'] != '') {
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::CRN_ALREADY_ASSIGNED_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::CRN_ALREADY_ASSIGNED_MSG;
                    return $responseObj;
                }
                                               
                $bankInfo = $bankObject->getBankidByProductid((string) trim($resp->ProductCode));
                if(empty($bankInfo) ) {                    
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);   
                }
                              
                // get CRN info
                $crnMaster = new CRNMaster();
                $crnInfo = $crnMaster->fetchCRNforAPI(array('product_id' => (string) trim($resp->ProductCode), 'card_number' => (string) trim($resp->CardNumber), 'card_pack_id' => (string) trim($resp->CardPackId), 'status' => STATUS_FREE), 'DATA');
                if(empty($crnInfo)) {
                  throw new Exception(ErrorCodes::ERROR_INVALID_CARD_FAILURE_MSG, ErrorCodes::ERROR_INVALID_CARD_FAILURE_CODE); 
                }

                $response = $cardholderModel->mapCardAPI($custInfo, $crnInfo);  

                if ($response['status_map'] == STATUS_SUCCESS && $response['status_load'] == STATUS_SUCCESS) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = sprintf('%012d', $crnInfo['id']);
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->ResponseCode = self::CARD_MAPPING_SUCC_CODE;
                    $responseObj->ResponseMessage = self::CARD_MAPPING_LOAD_SUCC_MSG;
                } elseif($response['status_map'] == STATUS_SUCCESS && $response['status_load'] == STATUS_FAILED) { 
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = sprintf('%012d', $crnInfo['id']);
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->ResponseCode = self::CARD_MAPPING_SUCC_CODE;
                    $responseObj->ResponseMessage = self::CARD_MAPPING_SUCCESS_LOAD_FAIL_MSG;
                } elseif($response['status_map'] == STATUS_SUCCESS) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = sprintf('%012d', $crnInfo['id']);
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->ResponseCode = self::CARD_MAPPING_SUCC_CODE;
                    $responseObj->ResponseMessage = self::CARD_MAPPING_SUCC_MSG;
                } else {
                    $errorMsg = $cardholderModel->getError();
                    $errorMsg = ErrorCodes::ERROR_CARD_MAPPING_FAIL_MSG;
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TransactionRefNo = (string) trim($resp->TransactionRefNo);
                    $responseObj->ResponseCode = ErrorCodes::ERROR_CARD_MAPPING_FAIL_CODE;
                    $responseObj->ResponseMessage = $errorMsg;
                }
                return $responseObj;
            } catch (App_Exception $e) {
                App_Logger::log($e, Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_CARD_MAPPING_FAIL_CODE;
                    $message = ErrorCodes::ERROR_CARD_MAPPING_FAIL_MSG;
                }
                if(strlen($code)>3){
                    $code = $this->filterErrorCodes($code);
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
            App_Logger::log($e, Zend_Log::ERR);
                $code = $e->getCode();
                $message = $e->getMessage();
                if( (empty($code) ) || (empty($message)) ) {
                    $code = ErrorCodes::ERROR_CARD_MAPPING_FAIL_CODE;
                    $message = ErrorCodes::ERROR_CARD_MAPPING_FAIL_MSG;
                }
                if(strlen($code)>3){
                    $code = $this->filterErrorCodes($code);
                }
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
        }
    }
    
     // Card block request 

    public function CardBlockRequest() {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            if (!isset($resp->SessionID) || !$this->isLogin((string)$resp->SessionID)) {
                 return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }

            Validator_Ratnakar::chkallParams($resp);
            Validator_Ratnakar::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());
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

                $param ['product_id'] = (string) trim($resp->ProductCode);

                if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_MOB)) {
                    $param['mobile'] = (string) trim($resp->MemberIDCardNo);
                } else if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER)) {
                    $param['partner_ref_no'] = (string) trim($resp->MemberIDCardNo);
                }
                
               // $param['status'] = STATUS_ACTIVE;
                $object = new Corp_Ratnakar_Cardholders();

                $custInfo = $object->getCardholderInfo($param);

                if (empty($custInfo)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                }

                // Checking Card Number existance 
              if(empty($custInfo['card_number'])){
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CARD_MSG;
                    return $responseObj; 
                }
                /*
                 * Requesting to Card Block to ECS
                 */
                
                $api = new App_Api_ECS_Corp_Ratnakar();
                $flg = $api->stopCard(array(
                    'cardNumber' => $custInfo['card_number']
                ));
                
                if ($flg == FALSE) {
                    $msg = $api->getError();
                    $ResponseMsg = empty($msg) ? ErrorCodes::ERROR_CARD_BLOCK_FAILED_RESPONSE_MSG : $msg;
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnIdentifierType = (string) trim($resp->TxnIdentifierType);
                    $responseObj->MemberIDCardNo = (string) trim($resp->MemberIDCardNo);
                    $responseObj->ResponseCode = ErrorCodes::ERROR_CARD_BLOCK_FAILED_RESPONSE_CODE;
                    $responseObj->ResponseMessage = $ResponseMsg;
                    
                    return $responseObj;
                }
                $sendSMS = TRUE; // Default Send SMS setting
                if($this->getSendSMSConstant() == SEND_SMS_FALSE){
                     $sendSMS = FALSE;
                 }
                 if($sendSMS){
                if (strtolower($resp->SMSFlag) == strtolower(FLAG_Y)) {
                    // Send SMS   
                    $params = array(
                        'product_id' => $custInfo['product_id'],
                        'cust_id' => $custInfo['rat_customer_id'],
                        'mobile' => $custInfo['mobile'],
                        'card_number' => $custInfo['card_number'],
                    );
                    $object->generateSMSDetails($params, $smsType = CUST_CARD_BLOCK_SMS);
                }
                 }
                $responseObj = new stdClass();
                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->TxnIdentifierType = (string) trim($resp->TxnIdentifierType);
                $responseObj->MemberIDCardNo = (string) trim($resp->MemberIDCardNo);
                $responseObj->ResponseCode = self::BLOCK_SUCCSSES_RESPONSE_CODE;
                $responseObj->ResponseMessage = ErrorCodes::BLOCK_CARD_SUCCSSES_RESPONSE_MSG;
                

                return $responseObj;
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $code = (empty($code)) ? ErrorCodes::ERROR_CARD_BLOCK_FAILED_RESPONSE_CODE : $code;
                $message = $e->getMessage();
                $message = (empty($message)) ? ErrorCodes::ERROR_CARD_BLOCK_FAILED_RESPONSE_MSG : $message;
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $code = (empty($code)) ? ErrorCodes::ERROR_CARD_BLOCK_FAILED_RESPONSE_CODE : $code;
                $message = $e->getMessage();
                $message = (empty($message)) ? ErrorCodes::ERROR_CARD_BLOCK_FAILED_RESPONSE_MSG : $message;
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
        }
    }
    
    
    /*
     * API :: BlockAmountRequest
     * functionality :: Add Block Amount in customer wallet
     * Input Param :: { mobile, amount,  product, Transaction type, Purse Code }  
     */
    
    public function BlockAmountRequest() {
        try {
	    $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
	    
	    // ==> Check Login
            if( !isset($resp->SessionID) || !$this->isLogin((string) $resp->SessionID)) {
                 return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
	    }
            Validator_Ratnakar::chkallParams($resp);
	    
	    // ==> Check TxnIdentifierType & $memberidCardNo
	    $txnidtype = strtolower ((string)trim($resp->TxnIdentifierType));
	    if($txnidtype != ''){
		if (strlen($txnidtype) > 3 || ( $txnidtype != strtolower(self::TXN_IDENTIFIER_TYPE_MOBILE) && $txnidtype != strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER) )) {
                   throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_CODE);
                }else{
		    Validator_Ratnakar::MemberIDCardNoValidation($txnidtype,(string) trim($resp->MemberIDCardNo));
                }
	    } else {
		throw new Exception(ErrorCodes::ERROR_TXN_IDENTIFIER_TYPE_MANDATORY_MSG, ErrorCodes::ERROR_TXN_IDENTIFIER_TYPE_MANDATORY_CODE);
	    }
	    // ==> Check Valid Amount	
	    Validator_Ratnakar::amountValidation((string) trim($resp->Amount));
	    // ==> Check Valid SMS Flag	
	    Validator_Ratnakar::smsFlagValidation((string) trim($resp->SMSFlag));
	    // ==> Check Valid ProductCode
	    Validator_Ratnakar::productcodeValidation((string) trim($resp->ProductCode), $this->getProductConstant());
	    // ==> Check Valid WalletCode
	    if(($resp->WalletCode == '') || (strlen($resp->WalletCode) > 6)){
		return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE);
	    }
	    // ==> Check Valid WalletCode on Product Basis without Virtual Wallet
	    $masterPurse = new MasterPurse();
	    $masterPurseLists = $masterPurse->getProductPurseListsNovirtual((string)$resp->ProductCode,'priority');
	    $allPurseCodes = array_column($masterPurseLists, 'code');
	    if(($resp->WalletCode =='') || (!in_array($resp->WalletCode,$allPurseCodes))){
		return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_CODE);
	    }
	    // ==> Check Valid Customer
	    $param ['product_id'] = (string) trim($resp->ProductCode);
	    if(strtolower((string) trim($resp->TxnIdentifierType)) == strtolower(self::TXN_IDENTIFIER_TYPE_MOBILE)){
		$param['mobile'] = (string) trim($resp->MemberIDCardNo);
	    } else if(strtolower((string) trim($resp->TxnIdentifierType)) == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER))   {
		$param['partner_ref_no'] = (string) trim($resp->MemberIDCardNo);
	    }
	    
	    $object = new Corp_Ratnakar_Cardholders();
	    $custInfo = $object->getCustomerDetails($param);
	    if($custInfo == FALSE) {
		$responseObj = new stdClass();
		$responseObj->SessionID = (string) $resp->SessionID;
		$responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
		$responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
		return $responseObj;
	    } elseif($custInfo['status'] == STATUS_BLOCKED) {
		$responseObj->SessionID = (string) $resp->SessionID;
		$responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE;
		$responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG;
		return $responseObj;
	    }
	    
	    try {
		$params =array(
		    'product_id'	=>  (string) trim($resp->ProductCode),
		    'amount'		=>  (string) trim($resp->Amount),
		    'wallet_code'	=>  (string) trim($resp->WalletCode),
		    'narration'		=>  (string) trim($resp->Narration) ,
		    'txn_type'		=>  (string) trim($resp->TxnType),
		    'rat_customer_id'   =>  $custInfo['rat_customer_id'],
		    'customer_master_id'=>  $custInfo['customer_master_id'],
		    'agent_id'		=>  $this->getAgentConstant(),
		    'card_number'	=>  $custInfo['card_number'],
		);
		
		$blockObj = new Corp_Ratnakar_BlockAmount();
		$txnCode = $blockObj->doWalletBlockAmount($params);
		
		if (empty($txnCode)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID; 
                    $responseObj->ResponseCode = ErrorCodes::ERROR_BLOCK_AMOUNT_FAIL_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_BLOCK_AMOUNT_FAIL_MSG;
                    return $responseObj;
                } else {
		    $responseObj->SessionID = (string) $resp->SessionID;
		    $responseObj->AckNo = $txnCode;
		    $responseObj->ResponseCode = self::BLOCK_AMOUNT_SUCCSSES_RESPONSE_CODE;
		    $responseObj->ResponseMessage = self::BLOCK_AMOUNT_SUCCSSES_RESPONSE_MSG;
		    return $responseObj ;
		}
	    } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $code = (empty($code)) ? ErrorCodes::ERROR_BLOCK_AMOUNT_FAIL_CODE : $code;
                $message = $e->getMessage();
                $message = (empty($message)) ? ErrorCodes::ERROR_BLOCK_AMOUNT_FAIL_MSG : $message;
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
	    App_Logger::log(serialize($e), Zend_Log::ERR);
	    $code = $e->getCode();
	    $code = (empty($code)) ? ErrorCodes::ERROR_BLOCK_AMOUNT_FAIL_CODE : $code;
	    $message = $e->getMessage();
	    $message = (empty($message)) ? ErrorCodes::ERROR_BLOCK_AMOUNT_FAIL_MSG : $message;
	    $this->_soapServer->_getLogger()->__setException($message);
	    return self::Exception($message, $code);
        }
    }

        
    /*
     * API :: UnBlockAmountRequest 
     * functionality :: reduce Block Amount from customer wallet 
     * Input Param :: { mobile/PAR, amount,  product, TransactionRefNo, Purse Code }  
     */
    
    public function UnBlockAmountRequest() {
        try {
            $blockObj = new Corp_Ratnakar_BlockAmount();
	    
	    $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
	    // ==> Check Login
            if( !isset($resp->SessionID) || !$this->isLogin((string) $resp->SessionID)) {
                 return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
	    }
            Validator_Ratnakar::chkallParams($resp); 
	    // ==> Check Valid TransactionRefNo
	    Validator_Ratnakar::txnrefnoValidation((string) trim($resp->TransactionRefNo));
	    // ==> Check TxnIdentifierType & $memberidCardNo
	    $txnidtype = strtolower ((string)trim($resp->TxnIdentifierType));
	    if($txnidtype != ''){
		if (strlen($txnidtype) > 3 || ( $txnidtype != strtolower(self::TXN_IDENTIFIER_TYPE_MOBILE) && $txnidtype != strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER) )) {
                   throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_CODE);
                }else{
		    Validator_Ratnakar::MemberIDCardNoValidation($txnidtype,(string) trim($resp->MemberIDCardNo));
                }
	    } else {
		throw new Exception(ErrorCodes::ERROR_TXN_IDENTIFIER_TYPE_MANDATORY_MSG, ErrorCodes::ERROR_TXN_IDENTIFIER_TYPE_MANDATORY_CODE);
	    }
	    // ==> Check Valid Amount	
	    Validator_Ratnakar::amountValidation((string) trim($resp->Amount));
	    // ==> Check Valid SMS Flag	
	    Validator_Ratnakar::smsFlagValidation((string) trim($resp->SMSFlag));
	    // ==> Check Valid ProductCode
	    Validator_Ratnakar::productcodeValidation((string)trim($resp->ProductCode),$this->getProductConstant());
	    // ==> Check ValidWalletCode
	    $walletCode = (string) trim($resp->WalletCode) ;
	    if(($walletCode == '') || (strlen($walletCode) > 6)){
		return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE);
	    }
	    // ==> Check Valid WalletCode on Product Basis without Virtual Wallet
	    $masterPurse = new MasterPurse();
	    $masterPurseLists = $masterPurse->getProductPurseListsNovirtual((string)$resp->ProductCode,'priority');
	    $allPurseCodes = array_column($masterPurseLists, 'code');
	    if(($walletCode =='') || (!in_array($walletCode,$allPurseCodes))){
		return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_CODE);
	    }
	    // ==> Check Valid Customer
	    $param ['product_id'] = (string) trim($resp->ProductCode);
	    $param ['wallet_code'] = (string) trim($resp->WalletCode);
	    if(strtolower((string) trim($resp->TxnIdentifierType)) == strtolower(self::TXN_IDENTIFIER_TYPE_MOBILE)){
		$param['mobile'] = (string) trim($resp->MemberIDCardNo);
	    } else if(strtolower((string) trim($resp->TxnIdentifierType)) == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER))   {
		$param['partner_ref_no'] = (string) trim($resp->MemberIDCardNo);
	    }
	    
	    $object = new Corp_Ratnakar_CustomerPurse();
	    $custInfo = $object->getPurseCardInfo($param);
	    if($custInfo == FALSE) {
		$responseObj = new stdClass();
		$responseObj->SessionID = (string) $resp->SessionID;
		$responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
		$responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
		return $responseObj;
	    } elseif($custInfo['status'] == STATUS_BLOCKED) {
		$responseObj->SessionID = (string) $resp->SessionID;
		$responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE;
		$responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG;
		return $responseObj;
	    }
	    
	    //==> Now Check Existence of TransactionRefNo
	    $txnCode = (string) trim($resp->TransactionRefNo);
	    $getblockdetails = $blockObj->getBlockDetail(array('txn_code' => $txnCode)) ;
	    
	    if(empty($getblockdetails)){
		return self::Exception(ErrorCodes::ERROR_INCORRECT_TXN_REF_MSG, ErrorCodes::ERROR_INCORRECT_TXN_REF_CODE);
	    } else if($custInfo['customer_purse_id'] != $getblockdetails['customer_purse_id']){
		return self::Exception(ErrorCodes::ERROR_INCORRECT_CUST_DETAIL_MSG, ErrorCodes::ERROR_INCORRECT_CUST_DETAIL_CODE);
	    } else if ($getblockdetails['amount'] != Util::convertToRupee((string) trim($resp->Amount))){
		return self::Exception(ErrorCodes::ERROR_INCORRECT_AMOUNT_MSG, ErrorCodes::ERROR_INCORRECT_AMOUNT_CODE);
	    } else if($getblockdetails['status'] != STATUS_BLOCKED){
		return self::Exception(ErrorCodes::ERROR_UNBLOCK_AMOUNT_FAIL_MSG, ErrorCodes::ERROR_UNBLOCK_AMOUNT_FAIL_CODE);
	    }
	     try {
		/*
		 *  Start Unblock Amount  
		 */
                $params =array(
		    'txn_code'	    =>  (string) trim($resp->TransactionRefNo),
                    'amount'	    =>  (string) trim($resp->Amount),
		);
		
		$txnCode = $blockObj->doWalletUnBlockAmount($params);
		
		if (empty($txnCode)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID; 
                    $responseObj->ResponseCode = ErrorCodes::ERROR_UNBLOCK_AMOUNT_FAIL_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_UNBLOCK_AMOUNT_FAIL_MSG;
                    return $responseObj;
                } else {
		    $responseObj->SessionID = (string) $resp->SessionID;
		    $responseObj->AckNo = $txnCode;
		    $responseObj->ResponseCode = self::UNBLOCK_AMOUNT_SUCCSSES_RESPONSE_CODE;
		    $responseObj->ResponseMessage = self::UNBLOCK_AMOUNT_SUCCSSES_RESPONSE_MSG;
		    return $responseObj ;
		}
	    } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $code = (empty($code)) ? ErrorCodes::ERROR_UNBLOCK_AMOUNT_FAIL_CODE : $code;
                $message = $e->getMessage();
                $message = (empty($message)) ? ErrorCodes::ERROR_UNBLOCK_AMOUNT_FAIL_MSG : $message;
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
	    App_Logger::log(serialize($e), Zend_Log::ERR);
	    $code = $e->getCode();
	    $code = (empty($code)) ? ErrorCodes::ERROR_UNBLOCK_AMOUNT_FAIL_CODE : $code;
	    $message = $e->getMessage();
	    $message = (empty($message)) ? ErrorCodes::ERROR_UNBLOCK_AMOUNT_FAIL_MSG : $message;
	    $this->_soapServer->_getLogger()->__setException($message);
	    return self::Exception($message, $code);
        }
    }
    

}
