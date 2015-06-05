<?php
/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_EDigital extends App_ApiServer_Exchange
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

    const OTP_FAILED_RESPONSE_CODE = '21';
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
    
    const TXN_IDENTIFIER_TYPE_CRN = 'CN';    
    const TXN_IDENTIFIER_TYPE_MOB = 'MOB';    
    const TXN_IDENTIFIER_TYPE_MID = 'MID';    
    const REQUEST_TYPE_LOAD = 'L';
    const REQUEST_TYPE_TRANSFER = 'T';
    const REQUEST_TYPE_REGISTRATION = 'R';
    
    const TXN_IDENTIFIER_TYPE_MOBILE = 'MOB';
    const TXN_IDENTIFIER_TYPE_EMAIL = 'EML';    
    const TXN_IDENTIFIER_TYPE_PARTNER = 'PAR';
    
    const CUST_IDENTIFIER_TYPE_EMAIL = 'E';    
    const CUST_IDENTIFIER_TYPE_PARTNER = 'P';
    const CUST_IDENTIFIER_TYPE_MOBILE = 'M'; 
    const CUST_IDENTIFIER_TYPE_MOB = 'M';
    
    const SMS_FLAG_TYPE_YES = 'Y';    
    const SMS_FLAG_TYPE_NO = 'N';    
    
    const CARD_ACTIVATION_TYPE_YES = 'Y';    
    const CARD_ACTIVATION_TYPE_NO = 'N';
    
    const CARD_DISPATCH_TYPE_YES = 'Y';    
    const CARD_DISPATCH_TYPE_NO = 'N';
    
    const CUSTOMER_REGISTRATION_SUCC_CODE = '0';
    const CUSTOMER_REGISTRATION_SUCC_MSG = 'Customer Registered Successfully';
    
    const CUSTOMER_UPDATION_SUCC_CODE = '0';
    const CUSTOMER_UPDATION_SUCC_MSG = 'Customer Updated Successfully';
    
    const QUERY_BENEFICIARY_SUCC_CODE = '0';
    const QUERY_BENEFICIARY_SUCC_MSG = 'Query Beneficiary Successful';
    
    const QUERY_BENEFICIARY_FAIL_CODE = '114';
    const QUERY_BENEFICIARY_FAIL_MSG = 'Unable to get query beneficiary record';
    
    const QUERY_BENEFICIARY_LIST_SUCC_CODE = '0';
    const QUERY_BENEFICIARY_LIST_SUCC_MSG = 'Query Beneficiary List Successful';
    
    const QUERY_BENEFICIARY_LIST_FAIL_CODE = '114';
    const QUERY_BENEFICIARY_LIST_FAIL_MSG = 'Unable to get query beneficiary list record';
    
    const QUERY_REGISTRATION_SUCC_CODE = '0';
    const QUERY_REGISTRATION_SUCC_MSG = 'Query Registration Successful';
    
    const QUERY_REGISTRATION_FAIL_CODE = '114';
    const QUERY_REGISTRATION_FAIL_MSG = 'Unable to get query record';
    
    const DEACTIVE_BENEFICIARY_SUCC_CODE = '0';
    const DEACTIVE_BENEFICIARY_SUCC_MSG = 'Beneficiary Deactivated Successfully';
    
    const DEACTIVE_BENEFICIARY_FAIL_CODE = '114';
    const DEACTIVE_BENEFICIARY_FAIL_MSG = 'Unable to deactivate beneficiary record';
    
    const CUSTOMER_REGISTRATION_FAIL_CODE = '114';
    const CUSTOMER_REGISTRATION_FAIL_MSG = 'Unable to register Customer';
    
    const CUSTOMER_UPDATION_FAIL_CODE = '114';
    const CUSTOMER_UPDATION_FAIL_MSG = 'Unable to update Customer';
    
    const BENEFICIARY_REGISTRATION_SUCC_CODE = '0';
    const BENEFICIARY_REGISTRATION_SUCC_MSG = 'Beneficiary Registered Successfully';
    
    const BENEFICIARY_REGISTRATION_FAIL_CODE = '170';
    const BENEFICIARY_REGISTRATION_FAIL_MSG = 'Unable to register beneficiary';
    
    const LOAD_SUCC_CODE = '0';
    const LOAD_SUCC_MSG = 'Successfully Registered Load Request';
    
    //const TXN_IDENTIFIER_TYPE_MID = 'MID';

    ############BLOCK CARD####################
    const BLOCK_SUCCSSES_RESPONSE_CODE = '0';
    const BLOCK_SUCCSSES_RESPONSE_MSG = 'Successfully blocked the account';
    
    const BLOCK_FAILED_RESPONSE_CODE = '116';
    const BLOCK_FAILED_RESPONSE_MSG = 'Unable to block the account';
    
    const ACCOUNT_BLOCK_STATUS_BLOCKED = '0';
    const ACCOUNT_BLOCK_STATUS_ACTIVE = '1';
    
    ############UNBLOCK CARD####################
    const UNBLOCK_SUCCSSES_RESPONSE_CODE = '0';
    const UNBLOCK_SUCCSSES_RESPONSE_MSG = 'Successfully unblocked the account';
    
    const UNBLOCK_FAILED_RESPONSE_CODE = '118';
    const UNBLOCK_FAILED_RESPONSE_MSG = 'Unable to unblock the account';    

    ############UNBLOCK CARD####################
    const MINISTT_SUCCSSES_RESPONSE_CODE = '0';
    const MINISTT_SUCCSSES_RESPONSE_MSG = 'Statement generated successfully';
    
    const MINISTT_FAILED_RESPONSE_CODE = '112';
    const MINISTT_FAILED_RESPONSE_MSG = 'Unable to generate the Statement';    

    ############Card Balance#################### commented in qa merge with cequity
    #### const BALANCE_SUCCSSES_RESPONSE_CODE = '102';
    ############UNBLOCK CARD####################
    const BALANCE_SUCCSSES_RESPONSE_CODE = '0';
    const BALANCE_SUCCSSES_RESPONSE_MSG = 'Successfully retrieved the Balance';
    
    const BALANCE_FAILED_RESPONSE_CODE = '111';
    const BALANCE_FAILED_RESPONSE_MSG = 'Unable to check The Balance';
    
    const PARTNER_BALANCE_SUCCSSES_RESPONSE_CODE = '0';
    const PARTNER_BALANCE_SUCCSSES_RESPONSE_MSG = 'Partner Balance Provided Successfully';
    
    const PARTNER_BALANCE_FAILED_RESPONSE_CODE = '111';
    const PARTNER_BALANCE_FAILED_RESPONSE_MSG = 'Unable to check The Balance';    
    
    ############TRANSACTION####################
    const TRANSACTION_DEBIT_REQUEST_SUCCESS_CODE = '0';
    const TRANSACTION_DEBIT_REQUEST_SUCCESS_MSG = 'Successfully Registered Debit Request';
    
    const TRANSACTION_DEBIT_REQUEST_FAILURE_CODE = '170';
    const TRANSACTION_DEBIT_REQUEST_FAILURE_MSG = 'Debit Request not successfull';
    
    const TRANSACTION_CREDIT_REQUEST_SUCCESS_CODE = '0';
    const TRANSACTION_CREDIT_REQUEST_SUCCESS_MSG = 'Successfully Registered Credit Request';
    
    const TRANSACTION_CREDIT_REQUEST_FAILURE_CODE = '170';
    const TRANSACTION_CREDIT_REQUEST_FAILURE_MSG = 'Credit Request not successfull';
    
    ############WALLET TRANSFER####################
    const WALLET_TRANSFER_SUCCESS_CODE = '0';
    const WALLET_TRANSFER_SUCCESS_MSG = 'Wallet Transfer Successful';
    
    const WALLET_TRANSFER_FAILURE_CODE = '170';
    const WALLET_TRANSFER_FAILURE_MSG = 'Wallet Transfer not successful';
    
    ############QUERY REMITTANCE####################
    const QUERY_REMITTANCE_SUCCESS_CODE = '0';
    const QUERY_REMITTANCE_SUCCESS_MSG = 'Query Remittance Successful';
    
    const QUERY_REMITTANCE_FAILURE_CODE = '170';
    const QUERY_REMITTANCE_FAILURE_MSG = 'Query Remittance not successful';
    
    
    ############QUERY TRANSFER####################
    const QUERY_TRANSFER_SUCCESS_CODE = '0';
    const QUERY_TRANSFER_SUCCESS_MSG = 'Query Transfer Successful';
    
    const QUERY_TRANSFER_FAILURE_CODE = '170';
    const QUERY_TRANSFER_FAILURE_MSG = 'Query Transfer not successful';
    
    ############TRANSACTION REQUEST####################
    const TRANSACTION_REQUEST_SUCCESS_CODE = '0';
    const TRANSACTION_REQUEST_SUCCESS_MSG = 'Successfully Registered Transaction Request';
    
    const DEBIT_TRANSACTION_REQUEST_SUCCESS_MSG = 'Successfully Registered Debit Transaction Request';
    
    const TRANSACTION_REQUEST_FAILURE_CODE = '170';
    const TRANSACTION_REQUEST_FAILURE_MSG = 'Transaction Request not successful';
    const DEBIT_TRANSACTION_REQUEST_FAILURE_MSG = 'Debit Transaction Request not successful';
    
    
    ############QUERY TRANSACTION REQUEST####################
    const QUERY_TRANSACTION_REQUEST_SUCCESS_CODE = '0';
    const QUERY_TRANSACTION_REQUEST_SUCCESS_MSG = 'Query Transaction Successful';
    
    const QUERY_TRANSACTION_REQUEST_FAILURE_CODE = '170';
    const QUERY_TRANSACTION_REQUEST_FAILURE_MSG = 'Query Transaction not successful';
    
    ############QUERY TRANSACTION REQUEST####################
    const REMITTANCE_TRANSACTION_SUCCESS_CODE = '0';
    const REMITTANCE_TRANSACTION_SUCCESS_MSG = 'Remittance Transaction Successful';
    
    const REMITTANCE_TRANSACTION_NO_RESPONSE_CODE = '174';
    const REMITTANCE_TRANSACTION_NO_RESPONSE_MSG = 'Hold/No response';
    
    const REMITTANCE_TRANSACTION_FAILURE_CODE = '170';
    const REMITTANCE_TRANSACTION_FAILURE_MSG = 'Remittance Transaction not successful';
    
    ############QUERY BENEFICIARY REQUEST####################
    const REMITTANCE_TRANSACTION_BENEFICIARY_FAILURE_CODE = '170';
    const REMITTANCE_TRANSACTION_BENEFICIARY_FAILURE_MSG = 'Unable to get Beneficiary record';
    
    ############QUERY BENEFICIARY REQUEST####################
    const REMITTANCE_TRANSACTION_REQUEST_REFUND_SUCCESS_CODE = '0';
    const REMITTANCE_TRANSACTION_REQUEST_REFUND_SUCCESS_MSG = 'Refund Successful';
    
    const QUERY_EDIGITAL_REFUND_TXN_CODE  = '0';
    const QUERY_EDIGITAL_REFUND_TXN_MSG  = 'Query Refund Successful';

   ############GENERATE OTP REQUEST####################
    const GENERATE_OTP_FAILED_RESPONSE_MSG = 'Generate OTP feature is disabled for this product';
    
    const BENEFICIARY_REGISTRATION_FAILED_RESPONSE_MSG = 'Beneficiary Registration feature is disabled for this product';
    const QUERY_REMITTANCE_FAILURE_RESPONSE_MSG = 'Query Remittance feature is not allowed for this product';
    const REMITTANCE_TXN_FAILURE_RESPONSE_MSG = 'Remittance Transaction feature is disabled for this product';
    const QUERY_BENEFICIARY_FAILURE_RESPONSE_MSG = 'Query Beneficiary feature is not allowed for this product';
    const DEACTIVE_BENEFICIARY_FAILURE_RESPONSE_MSG = 'Deactivate Beneficiary feature is not allowed for this product';
    const QUERY_BENEFICIARY_LIST_FAILURE_RESPONSE_MSG = 'Query Beneficiary List feature is not allowed for this product';
    
    const FLAG_GIFT_VOUCHER = 'G';
    
    const FLAG_REVERSAL_TRANS = 'Y';  
    
     ############ WALLET BALANCE ENQUIRY ####################
    const WALLET_BAL_ENQ_SUCCSSES_RESPONSE_CODE = '0';
    const WALLET_BAL_ENQ_SUCCSSES_RESPONSE_MSG = 'Successfully checked the wallet balance';
    
    const WALLET_BAL_ENQ_FAILED_RESPONSE_CODE = '112';
    const WALLET_BAL_ENQ_FAILED_RESPONSE_MSG = 'Unable to check the wallet balance';    

    ############ WALLET BALANCE ENQUIRY ####################
    
    const UNABLE_TO_PROCESS_CODE = '111';
    
    const UNABLE_TO_PROCESS_MSG = 'Unable to Process Request';
    
    const CARD_MAPPING_SUCC_CODE = '0';
    const CARD_MAPPING_SUCC_MSG = 'Card Mapped Successful';
    const CARD_MAPPING_LOAD_SUCC_MSG = 'Card Mapped and initial Load Successful';
    const CARD_MAPPING_SUCCESS_LOAD_FAIL_MSG = 'Card Mapped Successful but initial load failed';
    
    const CARD_MAPPING_FAIL_CODE = '114';
    const CARD_MAPPING_FAIL_MSG = 'Unable to Map Card';
    
    const BLOCK_AMOUNT_SUCCSSES_RESPONSE_CODE = '0';
    const BLOCK_AMOUNT_SUCCSSES_RESPONSE_MSG = 'Successfully blocked the amount';
    
    const UNBLOCK_AMOUNT_SUCCSSES_RESPONSE_CODE = '0';
    const UNBLOCK_AMOUNT_SUCCSSES_RESPONSE_MSG = 'Successfully unblocked the amount';
    
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
            return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
        } catch (App_Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, self::$SYSTEM_ERROR);
        }
    }
    
   
    public function EchoMessage() {
       $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);        
       $sxml = $this->_soapServer->getLastRequest();
       $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);        
       if(!$this->isLogin($resp->SessionID)) {
            return self::Exception(self::MESSAGE_INVALID_LOGIN, App_ApiServer_Exchange::$INVALID_LOGIN);
        }
       $sess_id = (string) $resp->SessionID;
        return self::generateSuccessResponse($sess_id);

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

    public function getProductConstant() {
        return $this->_productConst;
    }
    
    public function setProductConstant($pConst) {
        $this->_productConst = $pConst;
    }
    
    public function setBankProductConstant($pConst) {
        $this->_bankproductConst = $pConst;
    }
    
    public function setAgentConstant($agentConst) {
        $this->_agentConst = $agentConst;
    }

    public function getAgentConstant() {
        return $this->_agentConst;
    }
    
    public function getBankProductConstant() {
        return $this->_bankproductConst;
    }
    
    public function __call($name, $arguments) {
        App_Logger::log('Invalid Method called : '.$name, Zend_Log::ERR);
        App_Logger::log(serialize($arguments), Zend_Log::ERR);
        return self::Exception("System Error", self::$INVALID_METHOD);
    }
    
    public function setManageTypeConstant($manageTypeConst) {
        $this->_manageTypeConst = $manageTypeConst;
    }

    public function getManageTypeConstant() {
        return $this->_manageTypeConst;
    }
    
     public function setLoadExpiryConstant($loadExpiryConst) {
        $this->_loadExpiryConst = $loadExpiryConst;
    }

    public function getLoadExpiryConstant() {
        return $this->_loadExpiryConst;
    }
    
    public function setOTPRequestConstant($OTPrequestConst) {
        $this->_OTPrequestConst = $OTPrequestConst;
    }

    public function getOTPRequestConstant() {
        return $this->_OTPrequestConst;
    }
    public function setSendSMSConstant($sendSMSConst) {
        $this->_sendSMSConst = $sendSMSConst;
    }

    public function getSendSMSConstant() {
        return $this->_sendSMSConst;
    }
    
    public function setUnblockOTPRequestConstant($OTPrequestConst) {
        $this->_UnblockOTPRequestConst = $OTPrequestConst;
    }
    
    public function getUnblockOTPRequestConstant() {
        return $this->_UnblockOTPRequestConst;
    }
}