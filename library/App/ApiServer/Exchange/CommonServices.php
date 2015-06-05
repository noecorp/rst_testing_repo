<?php

/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_CommonServices extends App_ApiServer_Exchange {

    private $_soapServer;
    private $_PCONST;

    const TXN_IDENTIFIER_TYPE_MOB = 'MOB';   
    ############BLOCK CARD####################
    const BLOCK_SUCCSSES_RESPONSE_CODE = '0';
    const BLOCK_SUCCSSES_RESPONSE_MSG = 'Successfully blocked the account';
    
    const BLOCK_FAILED_RESPONSE_CODE = '116';
    const BLOCK_FAILED_RESPONSE_MSG = 'Unable to block the account';
    
    ############UNBLOCK CARD####################
    const UNBLOCK_SUCCSSES_RESPONSE_CODE = '0';
    const UNBLOCK_SUCCSSES_RESPONSE_MSG = 'Successfully unblocked the account';
    
    const UNBLOCK_FAILED_RESPONSE_CODE = '118';
    const UNBLOCK_FAILED_RESPONSE_MSG = 'Unable to block the account';    

    ############UNBLOCK CARD####################
    const MINISTT_SUCCSSES_RESPONSE_CODE = '0';
    const MINISTT_SUCCSSES_RESPONSE_MSG = 'Statement generated successfully';
    
    const MINISTT_FAILED_RESPONSE_CODE = '112';
    const MINISTT_FAILED_RESPONSE_MSG = 'Unable to generate the Statement';    

    ############UNBLOCK CARD####################
    const BALANCE_SUCCSSES_RESPONSE_CODE = '0';
    const BALANCE_SUCCSSES_RESPONSE_MSG = 'Successfully Checked Balance';
    
    const BALANCE_FAILED_RESPONSE_CODE = '111';
    const BALANCE_FAILED_RESPONSE_MSG = 'Unable to check the Balance';    
    
    
    
    
    
    
    public function __construct($server) {
        $this->_soapServer = $server;
    }

    // Set product const
    public function setProductConst($productConst = '') {
        if (!isset($this->_PCONST) || $this->_PCONST == '') {
            $this->_PCONST = $productConst;
        }
    }
    // Get product const
    public function getProductConst() {
        if (isset($this->_PCONST) && $this->_PCONST != '') {
            return $this->_PCONST;
        }
    }

    // Account block request 
    // 1) Basis Mobile Number, and last 4-digits of CardNumber
    // 2) Basis Mobile Number and ProductCode
    public function AccountBlockRequest($obj) {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            if (!isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
            }

            if (!isset($resp->TxnIdentifierType) || empty($resp->TxnIdentifierType)) {
                return self::Exception("Invalid TxnIdentifierType", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MOB) )) {
                return self::Exception("Invalid TxnIdentifierType " . $resp->TxnIdentifierType, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->MemberIDCardNo) || empty($resp->MemberIDCardNo)) {
                return self::Exception("Invalid MemberIDCardNo " . $resp->MemberIDCardNo, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->ProductCode) || empty($resp->ProductCode) || !$this->validateProductCodeByConst($resp->ProductCode, $this->_PCONST)) {
                return self::Exception(parent::INVALID_PRODUCT_MSG, parent::INVALID_PRODUCT_CODE);
            }
            if (!isset($resp->SMSFlag) || empty($resp->SMSFlag)) {
                return self::Exception("Invalid SMS Flag " . $resp->SMSFlag, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->CardNumber) || empty($resp->CardNumber)) {
                //return self::Exception("Invalid Card No. " . $resp->CardNumber, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            try {


                $object = new CustomerTrack();
                $param = array(
                    'mobile' => (string) $resp->MemberIDCardNo,
                    'card_number' => (string) $resp->CardNumber,
                    'product_id' => (string) $resp->ProductCode,
                );
                $custInfo = $object->getCardholderInfo($param);

                if (empty($custInfo) || !isset($custInfo['card_number'])) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = self::CUSTOMER_NOT_FOUND;
                    $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                    return $responseObj;
                }

                $api = new App_Api_ECS_Corp_Ratnakar();
                $flg = $api->stopCard(array(
                    'cardNumber' => $custInfo['card_number']
                ));
                if ($flg == FALSE) {
                    $msg = $api->getError();
                    $failedMsg = empty($msg) ? self::BLOCK_FAILED_RESPONSE_MSG : $msg;
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnIdentifierType = (string) $resp->TxnIdentifierType;
                    $responseObj->MemberIDCardNo = (string) $resp->MemberIDCardNo;
                    $responseObj->ResponseCode = self::BLOCK_FAILED_RESPONSE_CODE;
                    $responseObj->ResponseMessage = $failedMsg;
                    return $responseObj;
                }

                $responseObj = new stdClass();
                if (strtolower($resp->SMSFlag) == strtolower(FLAG_Y)) {
                    $responseObj->ResponseCode = self::BLOCK_SUCCSSES_RESPONSE_CODE;
                    $responseObj->ResponseMessage = self::BLOCK_SUCCSSES_RESPONSE_MSG;
                    // Send SMS   
                    $params = array(
                        'product_id' => $custInfo['product_id'],
                        'cust_id' => $custInfo['rat_customer_id'],
                        'mobile' => $custInfo['mobile'],
                        'card_number' => $custInfo['card_number'],
                    );
                    $object->generateSMSDetails($params, $smsType = CARD_BLOCK_SMS);
                } else {
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnIdentifierType = (string) $resp->TxnIdentifierType;
                    $responseObj->MemberIDCardNo = (string) $resp->MemberIDCardNo;
                    $responseObj->ResponseCode = self::BLOCK_SUCCSSES_RESPONSE_CODE;
                    $responseObj->ResponseMessage = self::BLOCK_SUCCSSES_RESPONSE_MSG;
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

    public function AccountUnBlockRequest($obj) {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            if (!isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
            }

            if (!isset($resp->TxnIdentifierType) || empty($resp->TxnIdentifierType)) {
                return self::Exception("Invalid TxnIdentifierType", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MOB) )) {
                return self::Exception("Invalid TxnIdentifierType " . $resp->TxnIdentifierType, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->MemberIDCardNo) || empty($resp->MemberIDCardNo)) {
                return self::Exception("Invalid MemberIDCardNo " . $resp->MemberIDCardNo, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->ProductCode) || empty($resp->ProductCode) || !$this->validateProductCodeByConst($resp->ProductCode, $this->_PCONST)) {
                return self::Exception(parent::INVALID_PRODUCT_MSG, parent::INVALID_PRODUCT_CODE);
            }
            if (!isset($resp->SMSFlag) || empty($resp->SMSFlag)) {
                return self::Exception("Invalid SMS Flag " . $resp->SMSFlag, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->CardNumber) || empty($resp->CardNumber)) {
                //return self::Exception("Invalid Card No. " . $resp->CardNumber, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            try {

                $object = new CustomerTrack();
                $param = array(
                    'mobile' => (string) $resp->MemberIDCardNo,
                    'card_number' => (string) $resp->CardNumber,
                    'product_id' => (string) $resp->ProductCode,
                );
                $custInfo = $object->getCardholderInfo($param);

                if (empty($custInfo) || !isset($custInfo['card_number'])) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = self::CUSTOMER_NOT_FOUND;
                    $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                    return $responseObj;
                }
                $api = new App_Api_ECS_Corp_Ratnakar();
                $flg = $api->unblockCard(array(
                    'cardNumber' => $custInfo['card_number']
                ));
                if ($flg == FALSE) {
                    $msg = $api->getError();
                    $failedMsg = empty($msg) ? self::UNBLOCK_FAILED_RESPONSE_MSG : $msg;
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnIdentifierType = (string) $resp->TxnIdentifierType;
                    $responseObj->MemberIDCardNo = (string) $resp->MemberIDCardNo;
                    $responseObj->ResponseCode = self::UNBLOCK_FAILED_RESPONSE_CODE;
                    $responseObj->ResponseMessage = $failedMsg;
                    return $responseObj;
                }

                $responseObj = new stdClass();
                if (strtolower($resp->SMSFlag) == strtolower(FLAG_Y)) {
                    $responseObj->ResponseCode = self::UNBLOCK_SUCCSSES_RESPONSE_CODE;
                    $responseObj->ResponseMessage = self::UNBLOCK_SUCCSSES_RESPONSE_MSG;
                    // Send SMS
                    $params = array(
                        'product_id' => $custInfo['product_id'],
                        'cust_id' => $custInfo['rat_customer_id'],
                        'mobile' => $custInfo['mobile'],
                        'card_number' => $custInfo['card_number'],
                    );
                    $object->generateSMSDetails($params, $smsType = CARD_UNBLOCK_SMS);
                } else {
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnIdentifierType = (string) $resp->TxnIdentifierType;
                    $responseObj->MemberIDCardNo = (string) $resp->MemberIDCardNo;
                    $responseObj->ResponseCode = self::UNBLOCK_SUCCSSES_RESPONSE_CODE;
                    $responseObj->ResponseMessage = self::UNBLOCK_SUCCSSES_RESPONSE_MSG;
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

    public function MiniStatementRequest($obj) {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            if (!isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
            }

            if (!isset($resp->TxnIdentifierType) || empty($resp->TxnIdentifierType)) {
                return self::Exception("Invalid TxnIdentifierType", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MOB) )) {
                return self::Exception("Invalid TxnIdentifierType " . $resp->TxnIdentifierType, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->MemberIDCardNo) || empty($resp->MemberIDCardNo)) {
                return self::Exception("Invalid MemberIDCardNo " . $resp->MemberIDCardNo, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            if (!isset($resp->SMSFlag) || empty($resp->SMSFlag)) {
                return self::Exception("Invalid SMS Flag " . $resp->SMSFlag, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->CardNumber) || empty($resp->CardNumber)) {
                //return self::Exception("Invalid Card No. " . $resp->CardNumber, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->WalletCode)) {
                //  return self::Exception("Invalid WalletCode ", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->ProductCode) || empty($resp->ProductCode) || !$this->validateProductCodeByConst($resp->ProductCode, $this->_PCONST)) {
                return self::Exception(parent::INVALID_PRODUCT_MSG, parent::INVALID_PRODUCT_CODE);
            }

            try {


                $object = new CustomerTrack();
                $param = array(
                    'mobile' => (string) $resp->MemberIDCardNo,
                    'card_number' => (string) $resp->CardNumber,
                    'product_id' => (string) $resp->ProductCode,
                );
                $custInfo = $object->getCardholderInfo($param);

                if (empty($custInfo) || !isset($custInfo['card_number'])) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = self::CUSTOMER_NOT_FOUND;
                    $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                    return $responseObj;
                }

                $cardholderArray['cardNumber'] = $custInfo['card_number'];
                $cardholderArray['fetchFlag'] = '1';
                $cardholderArray['fromDate'] = '';
                $cardholderArray['noOfTransactions'] = '5';
                $cardholderArray['toDate'] = '';

                $api = new App_Api_ECS_Corp_Ratnakar();
                $flg = $api->transactionHistory($cardholderArray);
                if ($flg == FALSE) {
                    $msg = $api->getError();
                    $failedMsg = empty($msg) ? self::MINISTT_FAILED_RESPONSE_MSG : $msg;
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnIdentifierType = (string) $resp->TxnIdentifierType;
                    $responseObj->MemberIDCardNo = (string) $resp->MemberIDCardNo;
                    $responseObj->ResponseCode = self::MINISTT_FAILED_RESPONSE_MSG;
                    $responseObj->ResponseMessage = $failedMsg;
                    return $responseObj;
                }

                $response = $api->getLastResponse();
                $rxml = $this->generateMiniStatementXML($response, $resp);
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

    public function BalanceEnquiryRequest($obj) {

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);


            if( !isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                 return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
             }

            if (!isset($resp->TxnIdentifierType) || empty($resp->TxnIdentifierType)) {
                return self::Exception("Invalid TxnIdentifierType", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MOB) )) {
                return self::Exception("Invalid TxnIdentifierType " . $resp->TxnIdentifierType, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->MemberIDCardNo) || empty($resp->MemberIDCardNo)) {
                return self::Exception("Invalid MemberIDCardNo " . $resp->MemberIDCardNo, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            if (!isset($resp->CardNumber) || empty($resp->CardNumber)) {
                //return self::Exception("Invalid Card No. " . $resp->CardNumber, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            if (!isset($resp->SMSFlag) || empty($resp->SMSFlag)) {
                return self::Exception("Invalid SMS Flag " . $resp->SMSFlag, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            if (!isset($resp->ProductCode) || empty($resp->ProductCode) || !$this->validateProductCodeByConst($resp->ProductCode, $this->_PCONST)) {
                return self::Exception(parent::INVALID_PRODUCT_MSG, parent::INVALID_PRODUCT_CODE);
            }

            try {

                $object = new CustomerTrack();
                $param = array(
                    'mobile' => (string) $resp->MemberIDCardNo,
                    'card_number' => (string) $resp->CardNumber,
                    'product_id' => (string) $resp->ProductCode,
                );
                $custInfo = $object->getCardholderInfo($param);

                if (empty($custInfo) || !isset($custInfo['card_number'])) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = self::CUSTOMER_NOT_FOUND;
                    $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                    return $responseObj;
                }
                $api = new App_Api_ECS_Corp_Ratnakar();
                $flg = $api->balanceInquiry(array(
                    'cardNumber' => $custInfo['card_number']
                ));
                if ($flg == FALSE) {
                    $msg = $api->getError();
                    $failedMsg = empty($msg) ? self::BALANCE_FAILED_RESPONSE_MSG : $msg;
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnIdentifierType = (string) $resp->TxnIdentifierType;
                    $responseObj->MemberIDCardNo = (string) $resp->MemberIDCardNo;
                    $responseObj->ResponseCode = self::BALANCE_FAILED_RESPONSE_CODE;
                    $responseObj->ResponseMessage = $failedMsg;
                    return $responseObj;
                }
                $response = $api->getLastResponse();
                $curCode = isset($response->balanceInquiryList->currencycode) ? $response->balanceInquiryList->currencycode : CURRENCY_INR;

                $responseObj = new stdClass();
                if (strtolower($resp->SMSFlag) == strtolower(FLAG_Y)) {
                    $responseObj->ResponseCode = self::BALANCE_SUCCSSES_RESPONSE_CODE;
                    $responseObj->ResponseMessage = self::BALANCE_SUCCSSES_RESPONSE_MSG;
                    //Send SMS  
                    $params = array(
                        'product_id' => $custInfo['product_id'],
                        'cust_id' => $custInfo['rat_customer_id'],
                        'mobile' => $custInfo['mobile'],
                        'card_number' => $custInfo['card_number'],
                    );
                    $object->generateSMSDetails($params, $smsType = BALANCE_ENQUIRY_SMS);
                } else {
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnIdentifierType = (string) $resp->TxnIdentifierType;
                    $responseObj->MemberIDCardNo = (string) $resp->MemberIDCardNo;
                    $responseObj->Currency = $curCode;
                    $responseObj->AvailableBalance = $response->balanceInquiryList->availablebalance;
                    $responseObj->ResponseCode = self::BALANCE_SUCCSSES_RESPONSE_CODE;
                    $responseObj->ResponseMessage = self::BALANCE_SUCCSSES_RESPONSE_MSG;
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

}
