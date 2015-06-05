<?php
/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_Services_Switch extends App_ApiServer_Exchange
{
    private $_soapServer;
    const TP_ID = TP_SWITCH_ID;
    const LOAD_FAILED_RESPONSE_CODE = '110';
    const LOAD_FAILED_RESPONSE_MSG  = 'Unable to register Load Request';
    const LOAD_SUCCSSES_RESPONSE_CODE = '0000';
    const LOAD_SUCCSSES_RESPONSE_MSG = 'Successfully Registered Load Request';
    
    const BLOCK_SUCCSSES_RESPONSE_CODE = '107';
    const BLOCK_SUCCSSES_RESPONSE_MSG = 'Successfully blocked the account';
    ####INVALID AMOUNT MESSAGE####
    const INVALID_AMOUNT_CODE = '0013';
    const INVALID_AMOUNT_MESSAGE = 'Invalid Transaction Amount';
    
    ####INVALID BILLED AMOUNT MESSAGE####
    const INVALID_BILLED_AMOUNT_MESSAGE = 'Invalid Billed Amount';
    
    ####INVALID CARDNUMBER####
    const INVALID_CARD_CODE = '0014';
    
    const TXN_IDENTIFIER_TYPE_CRN = 'CN';    

    ##########OTP#################
    const OTP_INVALID_RESPONSE_CODE = '115';
    const OTP_INVALID_RESPONSE_MSG = 'INVALID OTP';    
    /**
     * Constructor
     * @param type $server
     */
    public function __construct($server) 
    {
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
            //echo $username. ' : '. $password;exit;
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
        
       if(!$this->isLogin($sessionId)) {
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

        $flg = parent::logoff($sessionId);
        if($flg) {
            return self::generateSuccessResponsewithoutSessionID();
        }
        return self::Exception('Invalid SessionID', '101');
        
    }
   
    
    public function CardTransactionRequest () {//Do not add comments for method summary
        try {


/*            $resArray['user_id'] = self::TP_ID;
            $resArray['method'] = 'CardTransaction';
            $resArray['request'] = $this->_soapServer->getLastRequest();
            $resArray['response'] = '';
            //$resArray['source'] = 'server';
            App_Logger::apilog($resArray);*/
            
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml,__FUNCTION__);

            if( !isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                 return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
             }

             
            if( !isset($resp->CRN) || empty($resp->CRN)) {
                 return self::Exception(self::INVALID_CARD_MESSAGE, self::INVALID_CARD_CODE);
            }

            if( !isset($resp->TID) || empty($resp->TID)) {
                 return self::Exception("Invalid TID", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            // Handling Amount
            if( !isset($resp->MCC) || empty($resp->MCC)) {
                 return self::Exception("Invalid MCC", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            // Currency
            if( !isset($resp->MID) || empty($resp->MID)) {
                 return self::Exception("Invalid MID", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if( !isset($resp->TransactionAmount) || empty($resp->TransactionAmount)) {
                 return self::Exception("Invalid Transaction Amount", self::INVALID_AMOUNT_CODE);
            }
            
            //TXNNo
            if( !isset($resp->BilledAmount) || empty($resp->BilledAmount)) {
                 return self::Exception("Invalid Billed Amount", self::INVALID_AMOUNT_CODE);
            }
            
            //CardType
            if( !isset($resp->Currency) || empty($resp->Currency)) {
                 return self::Exception("Invalid Currency", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            //TxnIndicator
            if( !isset($resp->TxnNo) || empty($resp->TxnNo)) {
                 return self::Exception("Invalid TxnNo", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if( !isset($resp->TxnIndicator) || empty($resp->TxnIndicator)) {
                 return self::Exception("Invalid TxnIndicator", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if( !isset($resp->RevIndicator) || empty($resp->RevIndicator)) {
                 return self::Exception("Invalid RevIndicator", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            //if( !isset($resp->TxnType) || empty($resp->TxnType)) {
            //if(!$this->fieldValidator($resp->TxnType, self::FIELD_TYPE_TRANSACTION)) {
            if(!in_array(strtoupper($resp->TxnType),array(self::TRANSACTION_TYPE_ECOMM, self::TRANSACTION_TYPE_NORMAL))) {
                 return self::Exception("Invalid Transaction Type " . $this->TxnType, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            
            if( strtolower($resp->RevIndicator) == 'y') {
                if( !isset($resp->OrgTxnNo) || empty($resp->OrgTxnNo)) {
                    return self::Exception("Invalid OrgTxnNo", App_ApiServer_Exchange::$INVALID_RESPONSE);
                }
            }

            try {
                
               if(strtoupper($resp->TxnType) == self::TRANSACTION_TYPE_ECOMM && strtolower($resp->RevIndicator) == 'n') {
                    //Validate Transaction details
                    
                $object = new CustomerTrack();                
                $param = array(
                    'card_number'        => (string) $resp->CRN,
                    //'product_id'         => (string) $resp->ProductCode,
                );
                $customerInfo = $object->getCustomerDetails($param);
                if(empty($customerInfo)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->Message->SessionID;
                    $responseObj->AckNo          = '';
                    $responseObj->ResponseCode   = self::CUSTOMER_NOT_FOUND;
                    $responseObj->ResponseMessage= self::CUSTOMER_NOT_FOUND_MSG;                    
                    //$this->buildSResponse($responseObj);
                    return $responseObj;                                 
                }
                //echo $customerInfo['product_id'];
                //echo 'HHH';exit;
                $otpVeriParam = array(
                  'product_id'      => $customerInfo['product_id'],
                  'customer_id'     =>  $customerInfo['customer_id'],
                  //'otp'             =>  (string) $resp->OTP,
                  //'amount'             =>  (string) $resp->Message->Amount,
                  'request_type'    =>  'L'
                );
                $ref = new Reference();
               //$otpResponse = $object->verifyCustomerOTP($otpVeriParam);
               $otpResponse = $ref->verifyCustomerVerifiedLoadOTP($otpVeriParam);
                if($otpResponse == FALSE) {
                    $responseObj = new stdClass();                                      
                    $responseObj->SessionID      = (string) $resp->Message->SessionID;
                    $responseObj->ResponseCode   = self::OTP_INVALID_RESPONSE_CODE;
                    $responseObj->ResponseMessage= self::OTP_INVALID_RESPONSE_MSG;
                    return $responseObj;               
                }                               
 
                
                }
                
                
                $baseTxn = new AuthRequest();
                $param = array(
                    'card_number'   => (string) $resp->CRN,
                    'tid'           => (string) $resp->TID,
                    'mcc_code'      => (string) $resp->MCC,
                    'mid'           => (string) $resp->MID,
                    'amount_txn'    => (string) $resp->TransactionAmount,
                    'amount_billed' => (string) $resp->BilledAmount,
                    'currency_iso'  => (string) $resp->Currency,
                    'narration'     => (string) $resp->Narration,
                    'txn_no'        => (string) $resp->TxnNo,
                    'mode'          => (string) $resp->TxnIndicator,
                    'rev_indicator' => (string) $resp->RevIndicator,
                    'original_txn_no'=> (string)$resp->OrgTxnNo,
                );
                $response = $baseTxn->authAdvice($param);
                $responseObj = new stdClass();  
                if($response['status'] == STATUS_SUCCESS) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->TxnNo          = (string) $resp->TxnNo;
                    $responseObj->AckNo          = $response['ack_no'];
                    $responseObj->ResponseCode   = self::LOAD_SUCCSSES_RESPONSE_CODE;
                    $responseObj->ResponseMessage= self::LOAD_SUCCSSES_RESPONSE_MSG;
                } else {
                    if(isset($response['error_msg_code']) && !empty($response['error_msg_code'])) {
                        $responseCode = $this->filterErrorCodes($response['error_msg_code']);
                        $responseCode = !empty($responseCode) ? $responseCode : self::LOAD_FAILED_RESPONSE_CODE;
                    }
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->TxnNo          = (string) $resp->TxnNo;
                    $responseObj->AckNo          = $response['ack_no'];
                    $responseObj->ResponseCode   = $responseCode;
                    $responseObj->ResponseMessage= (isset($response['error_msg']) && !empty($response['error_msg'])) ? $response['error_msg'] : self::LOAD_FAILED_RESPONSE_MSG ;
               }
                return $responseObj;               
            } catch (App_Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());                
               App_Logger::log(serialize($e), Zend_Log::ERR);//exit;
               return self::Exception($e->getMessage(), ErrorCodes::ERROR_SYSTEM_ERROR); 
            }
            
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $this->_soapServer->_getLogger()->__setException($e->getMessage());            
             return self::Exception($e->getMessage(), self::LOAD_FAILED_RESPONSE_CODE);
        }
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
            } elseif(!(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_CRN))) {
                 return self::Exception("Invalid TxnIdentifierType " .$resp->TxnIdentifierType, App_ApiServer_Exchange::$INVALID_RESPONSE);                
            }
             
            if( !isset($resp->MemberIDCardNo) || empty($resp->MemberIDCardNo)) {
                 return self::Exception("Invalid MemberIDCardNo", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }            

            if( (!isset($resp->ProductCode) || empty($resp->ProductCode))) {
                 return self::Exception("Invalid ProductCode", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }            

            $apiSwitch = new Api_Switch();
            if(!$apiSwitch->validateProductCode($resp->ProductCode) ) {
                 return self::Exception($apiSwitch->getErrorMsg(), $apiSwitch->getErrorCode());
            }            
            


            try {
                    if(!$apiSwitch->blockAccount(array(
                        'crn'   => $resp->MemberIDCardNo,
                        'productCode'   => $resp->ProductCode
                        )) ) {
                         return self::Exception($apiSwitch->getErrorMsg(), $apiSwitch->getErrorCode());
                    }            
                    $responseObj = new stdClass();  
                    $responseObj->SessionID          = (string) $resp->SessionID;
                    $responseObj->TxnIdentifierType  = (string) $resp->TxnIdentifierType;
                    $responseObj->ResponseCode       = self::BLOCK_SUCCSSES_RESPONSE_CODE;
                    $responseObj->ResponseMessage    = self::BLOCK_SUCCSSES_RESPONSE_MSG;
                    $responseObj->AccountBlockStatus =  '0';
                    $responseObj->AccountBlockDateTime= date('Y-m-d h:i:s');
                    return $responseObj;               
            } catch (App_Exception $e) {
               App_Logger::log(serialize($e), Zend_Log::ERR);//exit;
                $this->_soapServer->_getLogger()->__setException($e->getMessage());               
               return self::Exception($e->getMessage(), ErrorCodes::ERROR_SYSTEM_ERROR); 
            }
            
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $this->_soapServer->_getLogger()->__setException($e->getMessage());            
             return self::Exception($e->getMessage(), self::LOAD_FAILED_RESPONSE_CODE);
        }
    }    
    
    
    public function filterErrorCodes($errorCode) {
        $code= FALSE;
        switch ($errorCode) {
            case ErrorCodes::ERROR_CARDHOLDER_NOT_FOUND:
                   $code = self::INVALID_CARD_CODE;
                break;
            case ErrorCodes::ERROR_INSUFFICIENT_AMOUNT:
                   $code = '0051';
                break;
            case ErrorCodes::ERROR_INSUFFICIENT_BALANCE:
                   $code = '0051';
                break;
            case ErrorCodes::ERROR_INSUFFICIENT_DATA_FOR_PROCESSING:
                   $code = '0012';
                break;
            case ErrorCodes::ERROR_TRANSACTION_LIMIT:
                   $code = '0061';
                break;
            case ErrorCodes::ERROR_TRANSACTION_FREQUENCY:
                   $code = '0065';
                break;
            default:
                $code = $errorCode;
                break;
        }
        return $code;
    }
    
    public function __call($name, $arguments) {
        
        App_Logger::log('Invalid Method called : '.$name, Zend_Log::ERR);
        App_Logger::log(serialize($arguments), Zend_Log::ERR);
        return self::Exception("System Error", self::$INVALID_METHOD);
    }

}
