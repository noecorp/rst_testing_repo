<?php
/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_Package extends App_ApiServer_Exchange
{
    private $_soapServer;
    
    protected $_TP_ID;
    protected $_productConst;
    protected $_agentConst;

    const LOAD_FAILED_RESPONSE_CODE = '110';
    const LOAD_FAILED_RESPONSE_MSG  = 'Unable to register Load Request';
    const DEBIT_FAILED_RESPONSE_MSG  = 'Unable to register Debit Request';
    
    const LOAD_SUCCSSES_RESPONSE_CODE = '0000';
    const LOAD_SUCCSSES_RESPONSE_MSG = 'Successfully Registered Load Request';
    const DEBIT_SUCCSSES_RESPONSE_MSG = 'Successfully Registered Debit Request';
    
    const OTP_SUCCSSES_RESPONSE_CODE = '0';
    const OTP_SUCCSSES_RESPONSE_MSG = 'OTP Sent Successfully';

    const OTP_FAILED_RESPONSE_CODE = '115';
    const OTP_FAILED_RESPONSE_MSG = 'Unable to process OTP request';    
    
    const OTP_INVALID_RESPONSE_CODE = '115';
    const OTP_INVALID_RESPONSE_MSG = 'INVALID OTP';    
    
    //const BLOCK_SUCCSSES_RESPONSE_CODE = '107';
    //const BLOCK_SUCCSSES_RESPONSE_MSG = 'Successfully blocked the account';
    ####INVALID AMOUNT MESSAGE####
    const INVALID_AMOUNT_CODE = '0013';
    const INVALID_AMOUNT_MESSAGE = 'Invalid Transaction Amount';
    
    ####INVALID BILLED AMOUNT MESSAGE####
    const INVALID_BILLED_AMOUNT_MESSAGE = 'Invalid Billed Amount';
    
    ####INVALID CARDNUMBER####
    const INVALID_CARD_CODE = '0014';
    
    const TXN_IDENTIFIER_TYPE_CRN = 'CRN';    
    const TXN_IDENTIFIER_TYPE_MOB = 'MOB';    
    const TXN_IDENTIFIER_TYPE_MID = 'MID';    
    const REQUEST_TYPE_LOAD = 'L';
    const REQUEST_TYPE_REGISTRATION = 'R';

    
    const CUSTOMER_REGISTRATION_SUCC_CODE = '0';
    const CUSTOMER_REGISTRATION_SUCC_MSG = 'Customer Registered Successfully';
    
    const CUSTOMER_REGISTRATION_FAIL_CODE = '114';
    const CUSTOMER_REGISTRATION_FAIL_MSG = 'Unable to register Customer';
    
    const LOAD_SUCC_CODE = '0';
    const LOAD_SUCC_MSG = 'Successfully Registered Load Request';
    
    //const TXN_IDENTIFIER_TYPE_MID = 'MID';

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
    
    
    /**
     * Constructor
     * @param type $server
     */
    public function __construct($server) {
        $this->_soapServer = $server;
    }
    
    protected function setTP($tpId)
    {
        $this->_TP_ID = $tpId;
    }

    
    /**
     * 
     * @param string $username
     * @param string $password
     */
    public function Login() {
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            $flg = parent::login($resp->Username, $resp->Password, $this->_TP_ID);
            if ($flg) {
                return self::generateSuccessResponse($flg, self::$SUCCESS);
            }
            return self::Exception("Invalid Login", self::$INVALID_LOGIN);
        } catch (App_Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            return self::Exception("Invalid Login", self::$SYSTEM_ERROR);
        }
    }
    
   
    public function EchoMessage() {
       $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);        
       $sxml = $this->_soapServer->getLastRequest();
       $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);        
       if(!$this->isLogin($resp->SessionID)) {
            return self::Exception(self::MESSAGE_INVALID_LOGIN, App_ApiServer_Exchange::$INVALID_LOGIN);
        }
        return self::generateSuccessResponse($resp->SessionID);

    }
    
    public function Logoff() {
       $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
       $sxml = $this->_soapServer->getLastRequest();
       $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);        
        $flg = parent::logoff($resp->SessionID);
        if($flg) {
            return self::generateSuccessResponsewithoutSessionID();
        }
        return self::Exception('Invalid SessionID', self::$SYSTEM_ERROR);
    }

    
    
   public function AccountBlockRequest () {
        
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);            
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml,__FUNCTION__);
            
            if( !isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                 return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
             }
             
            if( !isset($resp->TxnIdentifierType) || empty($resp->TxnIdentifierType)) {
                 return self::Exception("Invalid TxnIdentifierType", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MOB) )) {
                 return self::Exception("Invalid TxnIdentifierType " .$resp->TxnIdentifierType, App_ApiServer_Exchange::$INVALID_RESPONSE);                
            }
            
            if( !isset($resp->MemberIDCardNo) || empty($resp->MemberIDCardNo)) {
                 return self::Exception("Invalid MemberIDCardNo ".$resp->MemberIDCardNo, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if (!isset($resp->ProductCode) || empty($resp->ProductCode) || !$this->validateProductCodeByConst($resp->ProductCode,PRODUCT_CONST_RAT_HAP)) {
                return self::Exception(parent::INVALID_PRODUCT_MSG, parent::INVALID_PRODUCT_CODE);
            }
           
            try {

                $object = new CustomerTrack();
                $param = array(
                    'mobile'        => (string) $resp->MemberIDCardNo,
                    'product_id'    => (string) $resp->ProductCode,
                );
                
                $customerInfo = $object->getCustomerDetails($param);
                
                if(!empty($customerInfo)) {
                    $CardholderInfo = new Corp_Ratnakar_Cardholders();
                    $custInfo = $CardholderInfo->getCardholderInfo(array(
                        'cardholder_id' => $customerInfo['customer_id'],
                        'product_id' => $customerInfo['product_id']
                    ));
                }
                
                if(empty($customerInfo) || !isset($custInfo['card_number'])) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->ResponseCode   = self::CUSTOMER_NOT_FOUND;
                    $responseObj->ResponseMessage= self::CUSTOMER_NOT_FOUND_MSG;                    
                    return $responseObj;                                 
                }

                $api = new App_Api_ECS_Corp_Ratnakar();
                $flg = $api->stopCard(array(
                    'cardNumber'    => $custInfo['card_number']
                ));
                
                if($flg == FALSE) {
                    $msg = $api->getError();
                    $failedMsg = empty($msg) ? self::BLOCK_FAILED_RESPONSE_MSG : $msg;
                    $responseObj = new stdClass();
                    $responseObj->SessionID             = (string) $resp->SessionID;
                    $responseObj->TxnIdentifierType     = (string) $resp->TxnIdentifierType;
                    $responseObj->MemberIDCardNo        = (string) $resp->MemberIDCardNo;
                    $responseObj->ResponseCode          = self::BLOCK_FAILED_RESPONSE_CODE;
                    $responseObj->ResponseMessage       = $failedMsg;
                    return $responseObj;               
                }
                $responseObj = new stdClass();
                $responseObj->SessionID             = (string) $resp->SessionID;
                $responseObj->TxnIdentifierType     = (string) $resp->TxnIdentifierType;
                $responseObj->MemberIDCardNo        = (string) $resp->MemberIDCardNo;
                $responseObj->ResponseCode   = self::BLOCK_SUCCSSES_RESPONSE_CODE;
                $responseObj->ResponseMessage= self::BLOCK_SUCCSSES_RESPONSE_MSG;
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
    


    public function AccountUnBlockRequest ($obj) {
        
        try {
            
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);            
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml,__FUNCTION__);
            
            if( !isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                 return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
            }
             
            if( !isset($resp->TxnIdentifierType) || empty($resp->TxnIdentifierType)) {
                 return self::Exception("Invalid TxnIdentifierType", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MOB) )) {
                 return self::Exception("Invalid TxnIdentifierType " .$resp->TxnIdentifierType, App_ApiServer_Exchange::$INVALID_RESPONSE);                
            }
            
            if( !isset($resp->MemberIDCardNo) || empty($resp->MemberIDCardNo)) {
                 return self::Exception("Invalid MemberIDCardNo ".$resp->MemberIDCardNo, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if (!isset($resp->ProductCode) || empty($resp->ProductCode) || !$this->validateProductCodeByConst($resp->ProductCode,PRODUCT_CONST_RAT_HAP)) {
                return self::Exception(parent::INVALID_PRODUCT_MSG, parent::INVALID_PRODUCT_CODE);
            }
           
            try {

                $object = new CustomerTrack();
                $param = array(
                    'mobile'        => (string) $resp->MemberIDCardNo,
                    'product_id'    => (string) $resp->ProductCode,
                );
                $customerInfo = $object->getCustomerDetails($param);
                if(!empty($customerInfo)) {
                    $CardholderInfo = new Corp_Ratnakar_Cardholders();
                    $custInfo = $CardholderInfo->getCardholderInfo(array(
                        'cardholder_id' => $customerInfo['customer_id'],
                        'product_id' => $customerInfo['product_id']
                    ));
                }
                if(empty($customerInfo) || !isset($custInfo['card_number'])) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->ResponseCode   = self::CUSTOMER_NOT_FOUND;
                    $responseObj->ResponseMessage= self::CUSTOMER_NOT_FOUND_MSG;                    
                    return $responseObj;                                 
                }
                $api = new App_Api_ECS_Corp_Ratnakar();
                $flg = $api->unblockCard(array(
                    'cardNumber'    => $custInfo['card_number']
                ));
                if($flg == FALSE) {
                    $msg = $api->getError();
                    $failedMsg = empty($msg) ? self::UNBLOCK_FAILED_RESPONSE_MSG : $msg;
                    $responseObj = new stdClass();
                    $responseObj->SessionID             = (string) $resp->SessionID;
                    $responseObj->TxnIdentifierType     = (string) $resp->TxnIdentifierType;
                    $responseObj->MemberIDCardNo        = (string) $resp->MemberIDCardNo;
                    $responseObj->ResponseCode          = self::UNBLOCK_FAILED_RESPONSE_CODE;
                    $responseObj->ResponseMessage       = $failedMsg;
                    return $responseObj;               
                }
                
                $responseObj = new stdClass();
                $responseObj->SessionID             = (string) $resp->SessionID;
                $responseObj->TxnIdentifierType     = (string) $resp->TxnIdentifierType;
                $responseObj->MemberIDCardNo        = (string) $resp->MemberIDCardNo;
                $responseObj->ResponseCode          = self::UNBLOCK_SUCCSSES_RESPONSE_CODE;
                $responseObj->ResponseMessage       = self::UNBLOCK_SUCCSSES_RESPONSE_MSG;
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

    
 

    public function MiniStatementRequest ($obj) {
        
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);            
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml,__FUNCTION__);
            if( !isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                 return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
             }
             
            if( !isset($resp->TxnIdentifierType) || empty($resp->TxnIdentifierType)) {
                 return self::Exception("Invalid TxnIdentifierType", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MOB) )) {
                 return self::Exception("Invalid TxnIdentifierType " .$resp->TxnIdentifierType, App_ApiServer_Exchange::$INVALID_RESPONSE);                
            }
            
            if( !isset($resp->MemberIDCardNo) || empty($resp->MemberIDCardNo)) {
                 return self::Exception("Invalid MemberIDCardNo ".$resp->MemberIDCardNo, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if( !isset($resp->WalletCode) ) {
               //  return self::Exception("Invalid WalletCode ", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if (!isset($resp->ProductCode) || empty($resp->ProductCode) || !$this->validateProductCodeByConst($resp->ProductCode,PRODUCT_CONST_RAT_HAP)) {
                return self::Exception(parent::INVALID_PRODUCT_MSG, parent::INVALID_PRODUCT_CODE);
            }
           
            try {

                $object = new CustomerTrack();
                
                $param = array(
                    'mobile'        => (string) $resp->MemberIDCardNo,
                    'product_id'    => (string) $resp->ProductCode,
                );
                
                $customerInfo = $object->getCustomerDetails($param);
                if(!empty($customerInfo)) {
                    $CardholderInfo = new Corp_Ratnakar_Cardholders();
                    $custInfo = $CardholderInfo->getCardholderInfo(array(
                        'cardholder_id' => $customerInfo['customer_id'],
                        'product_id' => $customerInfo['product_id']
                    ));
                }
                if(empty($customerInfo) || !isset($custInfo['card_number'])) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->ResponseCode   = self::CUSTOMER_NOT_FOUND;
                    $responseObj->ResponseMessage= self::CUSTOMER_NOT_FOUND_MSG;                    
                    return $responseObj;                                 
                }
                
                $cardholderArray['cardNumber'] = $custInfo['card_number'];
                $cardholderArray['fetchFlag'] = '1';
                $cardholderArray['fromDate'] = '';
                $cardholderArray['noOfTransactions'] = '5';
                $cardholderArray['toDate'] = '';                
            
                $api = new App_Api_ECS_Corp_Ratnakar();
                $flg = $api->transactionHistory($cardholderArray);
                if($flg == FALSE) {
                    $msg = $api->getError();
                    $failedMsg = empty($msg) ? self::MINISTT_FAILED_RESPONSE_MSG : $msg;
                    $responseObj = new stdClass();
                    $responseObj->SessionID             = (string) $resp->SessionID;
                    $responseObj->TxnIdentifierType     = (string) $resp->TxnIdentifierType;
                    $responseObj->MemberIDCardNo        = (string) $resp->MemberIDCardNo;
                    $responseObj->ResponseCode          = self::MINISTT_FAILED_RESPONSE_MSG;
                    $responseObj->ResponseMessage       = $failedMsg;
                    return $responseObj;               
                }

                $response = $api->getLastResponse();
                $rxml = $this->generateMiniStatementXML($response, $resp);
                $this->logAuthenticationMessage($sxml, $rxml,'MiniStatementRequest');
                header ("Content-Type: text/xml; charset=utf-8");  
                print $rxml;
                exit;//DO NOT DELETE THIS LINE                
                
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
    
   public function BalanceEnquiryRequest ($obj) {
        
        try {
            
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);            
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml,__FUNCTION__);
            
            if( !isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                 return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
             }
             
            if( !isset($resp->TxnIdentifierType) || empty($resp->TxnIdentifierType)) {
                 return self::Exception("Invalid TxnIdentifierType", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MOB) )) {
                 return self::Exception("Invalid TxnIdentifierType " .$resp->TxnIdentifierType, App_ApiServer_Exchange::$INVALID_RESPONSE);                
            }
            
            if( !isset($resp->MemberIDCardNo) || empty($resp->MemberIDCardNo)) {
                 return self::Exception("Invalid MemberIDCardNo ".$resp->MemberIDCardNo, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if (!isset($resp->ProductCode) || empty($resp->ProductCode) || !$this->validateProductCodeByConst($resp->ProductCode,PRODUCT_CONST_RAT_HAP)) {
                return self::Exception(parent::INVALID_PRODUCT_MSG, parent::INVALID_PRODUCT_CODE);
            }
           
            try {

                $object = new CustomerTrack();
                $param = array(
                    'mobile'        => (string) $resp->MemberIDCardNo,
                    'product_id'    => (string) $resp->ProductCode,
                );
                $customerInfo = $object->getCustomerDetails($param);
                if(!empty($customerInfo)) {
                    $CardholderInfo = new Corp_Ratnakar_Cardholders();
                    $custInfo = $CardholderInfo->getCardholderInfo(array(
                        'cardholder_id' => $customerInfo['customer_id'],
                        'product_id' => $customerInfo['product_id']
                    ));
                }
                if(empty($customerInfo) || !isset($custInfo['card_number'])) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->ResponseCode   = self::CUSTOMER_NOT_FOUND;
                    $responseObj->ResponseMessage= self::CUSTOMER_NOT_FOUND_MSG;                    
                    return $responseObj;                                 
                }
                $api = new App_Api_ECS_Corp_Ratnakar();
                $flg = $api->balanceInquiry(array(
                    'cardNumber'    => $custInfo['card_number']
                ));
                if($flg == FALSE) {
                    $msg = $api->getError();
                    $failedMsg = empty($msg) ? self::BALANCE_FAILED_RESPONSE_MSG : $msg;
                    $responseObj = new stdClass();
                    $responseObj->SessionID             = (string) $resp->SessionID;
                    $responseObj->TxnIdentifierType     = (string) $resp->TxnIdentifierType;
                    $responseObj->MemberIDCardNo        = (string) $resp->MemberIDCardNo;
                    $responseObj->ResponseCode          = self::BALANCE_FAILED_RESPONSE_CODE;
                    $responseObj->ResponseMessage       = $failedMsg;
                    return $responseObj;               
                }
                $response = $api->getLastResponse();
                $curCode = isset($response->balanceInquiryList->currencycode) ? $response->balanceInquiryList->currencycode : CURRENCY_INR;
                $responseObj = new stdClass();
                $responseObj->SessionID             = (string) $resp->SessionID;
                $responseObj->TxnIdentifierType     = (string) $resp->TxnIdentifierType;
                $responseObj->MemberIDCardNo        = (string) $resp->MemberIDCardNo;
                $responseObj->Currency              = $curCode;
                $responseObj->AvailableBalance      = $response->balanceInquiryList->availablebalance;
                $responseObj->ResponseCode          = self::BALANCE_SUCCSSES_RESPONSE_CODE;
                $responseObj->ResponseMessage       = self::BALANCE_SUCCSSES_RESPONSE_MSG;
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
    
    
        
    public function CardTransactionRequest ($obj) {//Do not add comments for method summary
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);            
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml,__FUNCTION__);
            if( !isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                 return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
             }
             $productConst = $this->getProductConst();
            if( !isset($resp->ProductCode) || empty($resp->ProductCode) || !$this->validateProductCode($resp->ProductCode) || !$this->validateProductCodeByConst($resp->ProductCode,$productConst)) {
//             return self::Exception($productConst . ' : ' . $resp->ProductCode, parent::INVALID_PRODUCT_CODE);

  //               return self::Exception(parent::INVALID_PRODUCT_MSG, parent::INVALID_PRODUCT_CODE);
            }
             
            // Handling TxnIndentifier Type
            if( !isset($resp->TxnIdentifierType) || empty($resp->TxnIdentifierType)) {
                 return self::Exception("Invalid TxnIdentifierType", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!in_array($resp->TxnIdentifierType,array(self::TXN_IDENTIFIER_TYPE_MOB,self::TXN_IDENTIFIER_TYPE_CRN))) {
                 //return self::Exception("TxnIdentifierType: ". $resp->TxnIdentifierType." is not supported" , App_ApiServer_Exchange::$INVALID_RESPONSE);                
            }
            
            // Handling MemberIDCardNo
            if( !isset($resp->MemberIDCardNo) || empty($resp->MemberIDCardNo)) {
                 return self::Exception("Invalid MemberIDCardNo", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            // Handling Amount
            if( !isset($resp->Amount) || empty($resp->Amount)) {
                 return self::Exception("Invalid Amount", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            // Handling WalletCode
            if( !isset($resp->WalletCode) || empty($resp->WalletCode)) {
                 return self::Exception("Invalid Wallet Code", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif($this->isWalletAllowed($productConst, $resp->WalletCode)) {
                 return self::Exception("Invalid Wallet Code", App_ApiServer_Exchange::$INVALID_RESPONSE);                
            }
            
            // Currency
            if( !isset($resp->Currency) || empty($resp->Currency)) {
                 return self::Exception("Invalid Currency", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            //TXNNo
            if( !isset($resp->TxnNo) || empty($resp->TxnNo)) {
                 return self::Exception("Invalid TxnNo", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            //CardType
            if( !isset($resp->CardType) || empty($resp->CardType)) {
                 return self::Exception("Invalid CardType", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            //TxnIndicator
            if( !isset($resp->TxnIndicator) || empty($resp->TxnIndicator)) {
                 return self::Exception("Invalid TxnIndicator", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if( !isset($resp->Filler1) || empty($resp->Filler1)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            if( !isset($resp->Filler2) || empty($resp->Filler2)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            if( !isset($resp->Filler3) || empty($resp->Filler3)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            if( !isset($resp->Filler4) || empty($resp->Filler4)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            if( !isset($resp->Filler5) || empty($resp->Filler5)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            
            try {

                
                if(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MID)) {
                    $txnIdentifierType = 'MI';
                } elseif(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_CRN)) {
                    $txnIdentifierType = 'CN';                    
                } elseif(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MOB)) {
                    $txnIdentifierType = CORP_WALLET_TXN_IDENTIFIER_MB;                    
                } else {
                    $txnIdentifierType = $resp->TxnIdentifierType;                                        
                }
                

                if(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MID)) {
                    $param = array(
                        'member_id'        => (string) $resp->MemberIDCardNo,
                        'product_id'    => (string) $resp->ProductCode,
                    );
                } elseif(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_CRN)) {
                    $param = array(
                        'card_number'        => (string) $resp->MemberIDCardNo,
                        'product_id'    => (string) $resp->ProductCode,
                    );
                } elseif(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MOB)) {
                    $param = array(
                        'mobile'        => (string) $resp->MemberIDCardNo,
                        'product_id'    => (string) $resp->ProductCode,
                    );
                } else {
                    return self::Exception("Invalid TxnIndentifier Type", App_ApiServer_Exchange::$INVALID_RESPONSE);
                }
             
                $object = new CustomerTrack();
                $refObject = new Reference();
                
               /* $param = array(
                    'mobile'        => (string) $resp->MemberIDCardNo,
                    'product_id'    => (string) $resp->ProductCode,
                ); */
                $customerInfo = $object->getCustomerDetails($param);
                
                if(empty($customerInfo)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->AckNo          = '';
                    $responseObj->ResponseCode   = self::CUSTOMER_NOT_FOUND;
                    $responseObj->ResponseMessage= self::CUSTOMER_NOT_FOUND_MSG;                    
                    return $responseObj;                                 
                }
                
        
                $productId = (string) $resp->ProductCode;
                $walletCode = (string) $resp->WalletCode;
                
               $walletCodeResponse = $object->verifyWalletCode($productId, $walletCode);
              
                if($walletCodeResponse == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->AckNo          = ' ';
                    $responseObj->ResponseCode   = App_ApiServer_Exchange::$INVALID_RESPONSE;
                    $responseObj->ResponseMessage= 'Invalid Wallet Code';                    
                    return $responseObj;                                 
                }                                       
               
                $agent = $this->getAgentConstant();
                $baseTxn = new Corp_Ratnakar_Cardload();
                $param = array(
                    'txn_identifier_type'   => $txnIdentifierType,
                    'cardholder_id'         => (string) $customerInfo['customer_id'],
                    'product_id'            => (string) $resp->ProductCode,
                    'amount'                => (string) $resp->Amount,
                    'currency'              => (string) $resp->Currency,
                    'txn_no'                => (string) $resp->TxnNo,
                    'card_type'             => (string) $resp->CardType,
                    'mode'                  => (string) $resp->TxnIndicator,
                    'corporate_id'          => '0',
                    'narration'             => (string) (empty($resp->Narration) ? '' : $resp->Narration) ,
                    'wallet_code'           => (string) $resp->WalletCode,
                    'by_api_user_id'        => $agent,
		    'channel'               => CHANNEL_API
                );
                $response = $baseTxn->doCardload($param);
                $errorMsg = $baseTxn->getError();
                if($response['status'] == STATUS_LOADED) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->TxnNo          = (string) $resp->TxnNo;
                    $responseObj->AckNo          = $baseTxn->getTxncode();
                    $responseObj->ResponseCode   = self::LOAD_SUCCSSES_RESPONSE_CODE;
                    $responseObj->ResponseMessage= self::LOAD_SUCCSSES_RESPONSE_MSG;
                } else {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->TxnNo          = (string) $resp->TxnNo;
                    $responseObj->AckNo          = $baseTxn->getTxncode();
                    $responseObj->ResponseCode   = self::LOAD_FAILED_RESPONSE_CODE;
                    $responseObj->ResponseMessage= empty($errorMsg) ? 'Unable to register Load Request' : $errorMsg;                    
               }
                return $responseObj;               
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);//exit;
                $this->_soapServer->_getLogger()->__setException($e->getMessage());               
                return self::Exception($e->getMessage(), '12'); 
            }
            
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $this->_soapServer->_getLogger()->__setException($e->getMessage());            
             return self::Exception($e->getMessage(), self::$INVALID_METHOD);
        }
    }    

    public function getProductConstant() {
        return $this->_productConst;
    }
    
    public function setProductConstant($pConst) {
        $this->_productConst = $pConst;
    }
    
    public function setAgentConstant($agentConst) {
        $this->_agentConst = $agentConst;
    }

    public function getAgentConstant() {
        return $this->_agentConst;
    }


    private function getLoadSuccMsg($txnType) {
        if(strtolower($txnType) == strtolower(TXN_MODE_CR)) {
            return self::LOAD_SUCCSSES_RESPONSE_MSG;
        } else {
            return self::DEBIT_SUCCSSES_RESPONSE_MSG;
        }
     }
    
    public function __call($name, $arguments) {
        App_Logger::log('Invalid Method called : '.$name, Zend_Log::ERR);
        App_Logger::log(serialize($arguments), Zend_Log::ERR);
        return self::Exception("System Error", self::$INVALID_METHOD);
    }

    
}
