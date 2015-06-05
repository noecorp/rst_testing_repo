<?php
/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_Services_Ratnakar extends App_ApiServer_Exchange
{
    private $_soapServer;
    const TP_ID = TP_RATNAKAR_ID;
    const LOAD_SUCCSSES_RESPONSE_CODE = '101';
    const LOAD_FAILED_RESPONSE_CODE = '13';
    const LOAD_FAILED_RESPONSE_MESSAGE = 'Load Failed';
    const DEBIT_FAILED_RESPONSE_MESSAGE = 'Debit Failed';
    const LOAD_SUCCSSES_RESPONSE_MSG = 'Successfully Registered Load Request';
    const TXN_IDENTIFIER_TYPE_MID = 'MID';
    const TXN_IDENTIFIER_TYPE_CRN = 'CRN';
    const TXN_IDENTIFIER_TYPE_AMI = 'AMI';
    //Wallet Code
    const WALLET_CODE_GENERAL = 'GNRL';
    const WALLET_CODE_MEDIASSIST = 'CHSP';

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
   
       
    
    public function CardTransactionRequest ($obj) {//Do not add comments for method summary
        
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);                     
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml,__FUNCTION__);
            if( !isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                 return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
             }
             
            // Handling TxnIndentifier Type
            if( !isset($resp->TxnIdentifierType) || empty($resp->TxnIdentifierType)) {
                 return self::Exception("Invalid TxnIdentifierType", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!in_array($resp->TxnIdentifierType,array(self::TXN_IDENTIFIER_TYPE_MID,self::TXN_IDENTIFIER_TYPE_CRN))) {
                 return self::Exception("TxnIdentifierType: ". $resp->TxnIdentifierType." is not supported" , App_ApiServer_Exchange::$INVALID_RESPONSE);                
            }
            
            // Handling MemberIDCardNo
            if( !isset($resp->MemberIDCardNo) || empty($resp->MemberIDCardNo)) {
                 return self::Exception("Invalid MemberIDCardNo", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            // Handling Amount
            if( !isset($resp->Amount) || empty($resp->Amount)) {
                 return self::Exception("Invalid Amount", App_ApiServer_Exchange::$INVALID_RESPONSE);
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
            
            try {
                $baseTxn = new Corp_Ratnakar_Cardload();
                
                if(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MID)) {
                    $txnIdentifierType = 'MI';
                } elseif(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_CRN)) {
                    $txnIdentifierType = 'CN';                    
                } else {
                    $txnIdentifierType = $resp->TxnIdentifierType;                                        
                }
                $walletCode = '';
                if(strtolower($resp->WalletCode) == strtolower(self::WALLET_CODE_GENERAL)) {
                    $walletCode = self::WALLET_CODE_GENERAL;
                } elseif(strtolower($resp->TxnIdentifierType) == strtolower(self::WALLET_CODE_MEDIASSIST)) {
                    $walletCode = 'MEDI';                    
                } elseif(!empty($resp->WalletCode)) {
                    $walletCode = $resp->WalletCode;                                        
                }
                
                
                
                $param = array(
                    'agent_id'  => MEDIASSIST_AGENT_ID,
                    'txn_identifier_type'   => $txnIdentifierType,
                    'member_id_card_no'     => (string) $resp->MemberIDCardNo,
                    'amount'                => (string) $resp->Amount,
                    'currency'              => (string) $resp->Currency,
                    'txn_no'                => (string) $resp->TxnNo,
                    'card_type'             => (string) $resp->CardType,
                    'mode'                  => (string) $resp->TxnIndicator,
                    'corporate_id'          => (empty($resp->CorporateID) ? '' : $resp->CorporateID),
                    'narration'             => (empty($resp->Narration) ? '' : $resp->Narration) ,
                    'wallet_code'           => $walletCode,
		    'channel'               =>  CHANNEL_API,
                );
                $response = $baseTxn->doMediAssistCardLoad($param);
                $responseObj = new stdClass();              
                if($response['status'] == STATUS_LOADED) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->TxnNo          = (string) $resp->TxnNo;
                    $responseObj->AckNo          = $response['ack_no'];
                    $responseObj->ResponseCode   = self::LOAD_SUCCSSES_RESPONSE_CODE;
                    $responseObj->ResponseMessage= self::LOAD_SUCCSSES_RESPONSE_MSG;
                } else {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->TxnNo          = (string) $resp->TxnNo;
                    $responseObj->AckNo          = $response['ack_no'];
                    $responseObj->ResponseCode   = self::LOAD_FAILED_RESPONSE_CODE;
                    $responseObj->ResponseMessage= empty($response['error_msg']) ? $this->getLoadFailedMsg($resp->TxnIndicator) : $response['error_msg'];
               }
                return $responseObj;               
            } catch (App_Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());                    
               App_Logger::log(serialize($e), Zend_Log::ERR);//exit;
               return self::Exception($e->getMessage(), '12'); 
            }
            
        } catch (Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());                
            //echo "<pre>";print_r($e);exit('here');
            App_Logger::log(serialize($e), Zend_Log::ERR);
            //return $this->Exception("System Error", self::$INVALID_RESPONSE);
             //return self::Exception("System Error22", self::$INVALID_METHOD);
             return self::Exception($e->getMessage(), self::$INVALID_METHOD);
        }
    }    
    
    
    private function generateCardLoadFormat($response, $resp) {
        echo $response;
        echo $resp;
        exit;
        //print "<pre>";var_dump($subject);exit;
        $sessionId           = isset($arrFromMVC['SessionID']) ? $arrFromMVC['SessionID'] : '';
        $echoData            = isset($arrFromMVC['EchoData']) ? $arrFromMVC['EchoData'] : '';
        $cardnumber          = isset($arrFromMVC['cardNumber']) ? $arrFromMVC['cardNumber'] : '';
        $responseCode        = isset($subject->responseCode) ? $subject->responseCode : '';
        $msg                 = isset($subject->errorDesc) ? $subject->errorDesc : '';

        //echo "<pre>";print_r($arrFromMVC);exit;
        if(isset($subject->transactionHistory) && $msg =='' ) {
            $msg = 'Successful';
        } elseif($msg == '' && (!isset($subject->responseCode) || $subject->responseCode == '')) {
            $msg = 'Request timed out';
            $responseCode = '091';
        }
        
        $noOfRecord          = isset($subject->transactionHistory) ? count($subject->transactionHistory) : 0;
        $balanceEnquiryResponse = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sas="http://www.axiswebservice.net1.com/"><soapenv:Header/><soapenv:Body><sas:TransactionHistoryResponse><SessionID>'.$sessionId.'</SessionID><EchoData>'.$echoData.'</EchoData><ResponseCode>'.$responseCode.'</ResponseCode><ResponseMessage>'.$msg.'</ResponseMessage><CRN>'.$cardnumber.'</CRN>';
        
        if(isset($subject->transactionHistory) && $noOfRecord > 0) {
            $balanceEnquiryResponse .='<NumberOfRecords>'.$noOfRecord.'</NumberOfRecords>';
        }
        
        foreach ($subject->transactionHistory as $transaction) {
            $balanceEnquiryResponse .= '<sas:TransactionHistoryDetail><DateTime>'.$this->mvcDateFilter($transaction->txndatetime).'</DateTime><Description>'.$transaction->drcrflag.'</Description><ReferenceNumber>'.$transaction->txnlabel.'</ReferenceNumber><Amount>'.$this->mvcAmountFilter($transaction->txnamount).'</Amount></sas:TransactionHistoryDetail>';
        }
        $balanceEnquiryResponse.='</sas:TransactionHistoryResponse></soapenv:Body></soapenv:Envelope>';
        return $balanceEnquiryResponse;

    }
    
    private function getLoadFailedMsg($txnType) {
        if(strtolower($txnType) == strtolower(TXN_MODE_CR)) {
            return self::LOAD_FAILED_RESPONSE_MESSAGE;
        } else {
            return self::DEBIT_FAILED_RESPONSE_MESSAGE;
        }
    }
    
    public function __call($name, $arguments) {
        
        App_Logger::log('Invalid Method called : '.$name, Zend_Log::ERR);
        App_Logger::log(serialize($arguments), Zend_Log::ERR);
        return self::Exception("System Error", self::$INVALID_METHOD);
    }

}