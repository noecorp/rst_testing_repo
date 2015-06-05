<?php
/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_Services_Oxigen extends App_ApiServer_Exchange
{
    private $_soapServer;
    const TP_ID = TP_OXIGEN_ID;
    const TXN_IDENTIFIER_TYPE_MID = 'MI';
    const TXN_IDENTIFIER_TYPE_CRN = 'CN';
    
    const BALANCE_ENQUIRY_RESPONSE_CODE = '102';
    const BALANCE_ENQUIRY_RESPONSE_MSG = 'Successfully Checked The Balance';
    
    const ACCOUNT_INFORMATION_RESPONSE_CODE = '104';
    const ACCOUNT_INFORMATION_RESPONSE_MSG = 'Successfully retrieved Account Details';
    
    const ACCOUNT_STATUS_RESPONSE_CODE = '106';
    const ACCOUNT_STATUS_RESPONSE_MSG = 'Successfully retrieved Account Status';
    
    const ACCOUNT_BLCOKED_RESPONSE_CODE = '107';
    const ACCOUNT_BLCOKED_RESPONSE_MSG = 'Successfully blocked the account';

    const CUSTOMER_REGISTRATION_FAIL_CODE = '114';    
    
    const CUSTOMER_REGISTRATION_SUCC_CODE = '0';    
    const CUSTOMER_REGISTRATION_SUCC_MSG = 'Cardholder Registered Successfully';    
    
    const CUSTOMER_LOAD_FAIL_CODE = '111';    
    
    const CUSTOMER_LOAD_SUCC_CODE = '0';    
    const CUSTOMER_LOAD_SUCC_MSG = 'Load Successfully';    
    const CUSTOMER_LOAD_FAIL_MSG = 'Unable to Load';    
    
    const CUSTOMER_ACTIVATION_SUCC_CODE = '0';    
    const CUSTOMER_ACTIVATION_SUCC_MSG = 'Card Activated Successfully';   
    
    const CUSTOMER_ACTIVATION_FAIL_CODE = '120';        
    const CUSTOMER_ACTIVATION_FAIL_MSG = 'Unable to Activate Card';    

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
    
    
   
    
    public function CardTransactionRequest() {//Do not add comments for method summary
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);

            if (!isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                return self::Exception($this->getMessage('login_failed'), App_ApiServer_Exchange::$INVALID_LOGIN);
            }

            if (!isset($resp->ProductCode) || empty($resp->ProductCode) || !$this->validateProductCodeByConst($resp->ProductCode,PRODUCT_CONST_AXS_MVC)) {
                return self::Exception(parent::INVALID_PRODUCT_MSG, parent::INVALID_PRODUCT_CODE);
            }
            
            if (!isset($resp->TxnIdentifierType) || empty($resp->TxnIdentifierType) || (strtolower($resp->TxnIdentifierType) != strtolower(self::TYPE_MOB))) {
                return self::Exception('Invalid Txn Identifier Type', App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if (!isset($resp->MemberIDCardNo) || empty($resp->MemberIDCardNo) ) {
                return self::Exception('Invalid CRN Provided', App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if (!isset($resp->Amount) || empty($resp->Amount) ) {
                return self::Exception($this->getMessage('amount_invalid'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            } 
            
            if (!isset($resp->Currency) || empty($resp->Currency) ) {
                return self::Exception('Invalid Currency', App_ApiServer_Exchange::$INVALID_RESPONSE);
            } 
            
            if (!isset($resp->Narration) || empty($resp->Narration) ) {
                return self::Exception('Invalid Narration', App_ApiServer_Exchange::$INVALID_RESPONSE);
            } 
            
            if (!isset($resp->TxnNo) || empty($resp->TxnNo) ) {
                return self::Exception('Invalid TxnNo', App_ApiServer_Exchange::$INVALID_RESPONSE);
            } 
            
            if (!isset($resp->TxnIndicator) || empty($resp->TxnIndicator) ) {
                return self::Exception('Invalid Txn Indicator', App_ApiServer_Exchange::$INVALID_RESPONSE);
            } 

            
            

            try {
                 
                $object = new CustomerTrack();
                $param = array(
                        'mobile' => (string) $resp->MemberIDCardNo,
                        'product_id' => (string) $resp->ProductCode,
                    );
                
                    //$customerInfo = $object->getCustomerDetails($param);
                    //$cardNumber = $object->getCustomerCardNumber($resp->ProductCode, $customerInfo['customer_id']);
                
                $mvcObject = new Mvc_Axis_CardholderUser();
                $customerInfo = $mvcObject->getCardHolderInfoMobile($param['mobile']);
                $cardNumber = $customerInfo['crn'];
                if (empty($customerInfo) || empty($cardNumber)) {
                    $responseObj = new stdClass();                        
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = '';
                    $responseObj->ResponseCode = self::CUSTOMER_NOT_FOUND;
                    $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                    return $responseObj;
                }
                $params = array();

                $params['product_id'] = (string) $resp->ProductCode;
                $params['agent_id'] = (string) API_OXIGEN_AGENT_ID;
                $params['amount'] = (string) $resp->Amount;
                $params['crn'] = (string) $cardNumber;
                $params['currency'] = (string) $resp->Currency;
                
                // IS PRODUCT ALLOWED
                try {
                    
                    //$mvc = new Mvc_Axis_CardholderUser();
                    $transaction = new Mvc_Axis_CardholderFund();
                    $response = $transaction->loadFromCustomerAPI($params);
                    
                    if ($response == TRUE) {
                        $ack = $transaction->getTxnCode();
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = (string) $ack;
                        $responseObj->TxnNo = (string) $resp->TxnNo;
                        $responseObj->ResponseCode = self::CUSTOMER_LOAD_SUCC_CODE;
                        $responseObj->ResponseMessage = self::CUSTOMER_LOAD_SUCC_MSG;
                    } else {
                        $errorMsg = $transaction->getError();
                        $errorMsg = empty($errorMsg) ? self::CUSTOMER_LOAD_FAIL_MSG : $errorMsg;
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->ResponseCode = self::CUSTOMER_LOAD_FAIL_CODE;
                        $responseObj->ResponseMessage = $errorMsg;
                    }
                    return $responseObj;
                } catch (Exception $e) {
                    $error = $transaction->getError();
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    //$responseObj->TxnNo          = (string) $resp->TxnNo;
                    //$responseObj->AckNo          = $txnCode;//$baseTxn->getTxncode();
                    $responseObj->ResponseCode = self::$INVALID_RESPONSE;
                    $responseObj->ResponseMessage = empty($error) ? $e->getMessage() : $error;
                    return $responseObj;
                }

                //$response = $obj->addCustomer($params);
                //$txnCode = $obj->getTxncode();
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $this->_soapServer->_getLogger()->__setException($e->getMessage());
                return self::Exception($e->getMessage(), ErrorCodes::ERROR_SYSTEM_ERROR);
            }
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $this->_soapServer->_getLogger()->__setException($e->getMessage());
            return self::Exception($e->getMessage(), self::CUSTOMER_LOAD_FAIL_CODE);
        }
    }
    
    
    public function __call($name, $arguments) {
        
        App_Logger::log('Invalid Method called : '.$name, Zend_Log::ERR);
        App_Logger::log(serialize($arguments), Zend_Log::ERR);
        return self::Exception("System Error", self::$INVALID_METHOD);
    }
    


}