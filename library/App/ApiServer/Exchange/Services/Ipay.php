<?php
/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_Services_Ipay extends App_ApiServer_Exchange
{
    private $_soapServer;
    const TP_ID = TP_IPAY_ID;
    const CLIENT_CODE = 'TRANSICICI';
    const LOAD_FAILED_RESPONSE_CODE = '110';
    const MESSAGE_INVALID_LOGIN  = 'Invalid Login';
    const LOAD_SUCCSSES_RESPONSE_CODE = '0000';
    const ERROR_INVALID_MOB_MSG  = 'Invalid parameter Mobile';
    const ERROR_INVALID_AMOUNT_MSG  = 'Invalid parameter Amount';
    const ERROR_INVALID_TRANS_DATE_MSG  = 'Invalid Transaction Date';
    const ERROR_INVALID_BANK_TRANS_MSG  = 'Invalid Bank Transaction Id';
    const ERROR_INVALID_PAY_MODE_MSG  = 'Invalid Pay Mode';
    const ERROR_INVALID_ISURE_ID_MSG  = 'Invalid parameter ISure Id';
    const ERROR_DUPLICATE_ISURE_ID_MSG  = 'Duplicate ISure Id';
    const ERROR_TRAN_REF_NO_USED_MSG  = 'Transaction Reference Number already used';
    const INVALID_CLIENT_CODE_MESSAGE = 'Invalid Client Code';
    public $_ENUM_PAY_MODE_ARRAY = array('c','l','f');
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
    
    /*
     * Define: default failed message and code
     */
    const UNABLE_TO_PROCESS_MSG = 'Unable to Process Request';
    const INVALID_PROCESS_CODE = '0011';
    /**
     * Constructor
     * @param type $server
     */
    public function __construct($server) 
    {
        $this->_soapServer = $server;
    }

public function chkMobile($mobile) {
        if ($mobile != '') {
            if (strlen($mobile) != 10 || !(ctype_digit($mobile))) {
                return false;
            }
        } else {
                return false;
        }
        return true;
    }
    
    public function chkPayMode($payMode) {
        if ($payMode != '') {
            $payMode = strtolower($payMode);
            $flg = in_array($payMode, $this->_ENUM_PAY_MODE_ARRAY);
            if($flg){
                return true;
            }
        } else {
                return false;
        }
        return false;
    }
    
    //Ydm
    public function chkTransDate($transDate) {
        if ($transDate != '') {
            if (strlen($transDate) != 8 || !(ctype_digit($transDate))) {
                return false;
            } else {
                $d = substr($transDate,4,2) ;
                $m = substr($transDate,6,2) ; 
                $y = substr($transDate,0,4) ; 
                $today = strtotime(date("Y-m-d"));
                            $transDate = date("Y-m-d",strtotime($y.$m.$d));
                if((!checkdate($m,$d,$y)) || (strtotime($transDate) > $today)){
                            return false;
                }
            }
        } else {
            return false;
        }
        return true;
    }
    
    /**
     * 
     * @param string $username
     * @param string $password
     */
    public function chkLogin($username, $password) {
        try {
            

          $flg = parent::chklogin($username, $password, self::TP_ID);
          if ($flg) {
                return true;
            }
           return false; 
        } catch (App_Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());            
            App_Logger::log($e->getMessage(), Zend_Log::ERR); //exit;
            return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
           
        }
    }
    
    public function chkClientCode($clientCode) {
        try {
            
            if(strtoupper($clientCode) == self::CLIENT_CODE){
                return true;
            }
            
         //  echo "=========".self::CLIENT_CODE;exit;
            return false; 
        } catch (App_Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());            
            App_Logger::log($e->getMessage(), Zend_Log::ERR); //exit;
               return self::Exception(self::INVALID_CLIENT_CODE_MESSAGE, self::LOAD_FAILED_RESPONSE_CODE);
           
        }
    }
    
    public function chkAmount($amount) {
          
            if(($amount > 0) && (is_numeric($amount)) ){
                return true;
            }
            return false; 
       
    }
    
    public function chkIsureID($iSureID) {
        if ($iSureID != '') {
            if (strlen($iSureID) < 6 || !(ctype_digit($iSureID))) {
                return false;
            }
        } else {
                return false;
        }
        return true;
    }

    public function chkDuplicate($CheckArr){
        $agentObj = new AgentUser();
        $Count = $agentObj->checkDuplicate($CheckArr);
        if ($Count != 0) {
            return false; 
        }
        return true; 
    }
    
    public function chkBnkTransID($iSureID) {
        if ($iSureID != '') {
            if (strlen($iSureID) < 1 || !(ctype_alnum($iSureID))) {
                return false;
            }
        } else {
                return false;
        }
        return true;
    }
    
    public function clientValidationRequest($sessionId) {
        try {   
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            $responseMsg = '';    
            $status = STATUS_IPAY_REJECT;
            $username = (string) trim($resp->UserId);
            $password = (string) trim($resp->UserPwd);
            $clientCode = (string) trim($resp->Client_Code);
            $mobileNo = (string) trim($resp->Client_Validate_1); 
            $response = TRUE;
            if(!$this->chkClientCode($clientCode)) {
              $responseMsg = self::INVALID_CLIENT_CODE_MESSAGE;
              $response = FALSE;
             }
            elseif(!$this->chkLogin($username,$password,self::TP_ID)) {
              $responseMsg = self::MESSAGE_INVALID_LOGIN;
              $response = FALSE;
             }elseif(!$this->chkMobile($mobileNo)) {
              $responseMsg = self::ERROR_INVALID_MOB_MSG;
              $response = FALSE;
             }
          
            $baseTxn = new BaseTxn();
            $txnCode = $baseTxn->generateTxncode();
            if($response){
            $agentObj = new AgentUser();
            $agentInfo = $agentObj->getAgentBindingByMobile($mobileNo); 
            if(!empty($agentInfo)){
                if($agentInfo['enroll_status'] != STATUS_APPROVED) {
                    $status = STATUS_IPAY_REJECT;
                    $responseMsg = 'This partner is not approved yet';
                } else if($agentInfo['status'] != STATUS_UNBLOCKED) {
                    $status = STATUS_IPAY_REJECT;
                    $responseMsg = 'This partner is Blocked';
                } else {
                    $status = STATUS_IPAY_ACCEPT ;
                }
            } else {
                $status = STATUS_IPAY_REJECT;
                $responseMsg = 'Partner does not exist';
            }
            }
            $responseObj = new stdClass();
            $responseObj->Client_Code = (string) $resp->Client_Code;
            $responseObj->Client_Validate_1 = (string) trim($resp->Client_Validate_1);
            $responseObj->Txn_Ref_No = $txnCode; //$baseTxn->getTxncode();
            $responseObj->Status = $status;
            if($status == STATUS_IPAY_ACCEPT) {
                $responseObj->AgentCode = (string) trim($agentInfo['agent_code']);
                $responseObj->Name = (string) trim($agentInfo['name']);
                $responseObj->EstablishmentName = (string) trim($agentInfo['estab_name']);
                $responseObj->EstablishmentCity = (string) trim($agentInfo['estab_city']);
            }
            $responseObj->Reject_Reason = $responseMsg;
            return  $responseObj;           
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $code = $e->getCode();
            $code = (empty($code)) ? self::INVALID_PROCESS_CODE : $code;
            $message = $e->getMessage();
            $message = (empty($message)) ? self::UNABLE_TO_PROCESS_MSG : $message;
            $this->_soapServer->_getLogger()->__setException($message);
            return self::Exception($message, $code);
        }
    }
    
    
    public function clientMoneyPayRequest() {
        try {  
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            $responseMsg = '';    
            $status = STATUS_IPAY_REJECT;
            $username = (string) trim($resp->UserId);
            $password = (string) trim($resp->UserPwd);
            $clientCode = (string) trim($resp->Client_Code);
            $mobileNo = (string) trim($resp->Client_Validate_1);
            $amount = (string) trim($resp->Amount);
            $transDate = (string) trim($resp->Transaction_Date);
            $bankTransID = (string) trim($resp->IBANK_Transaction_Id);
            $payMode = (string) trim($resp->Pay_Mode);
            $iSureID = (string) trim($resp->ISure_Id);
            $response = TRUE;
            if(!$this->chkClientCode($clientCode)) {
              $responseMsg = self::INVALID_CLIENT_CODE_MESSAGE;
              $response = FALSE;
             }
            elseif(!$this->chkLogin($username,$password,self::TP_ID)) {
              $responseMsg = self::MESSAGE_INVALID_LOGIN;
              $response = FALSE;
             }elseif(!$this->chkMobile($mobileNo)) {
              $responseMsg = self::ERROR_INVALID_MOB_MSG;
              $response = FALSE;
             }elseif(!$this->chkAmount($amount)) {
              $responseMsg = self::ERROR_INVALID_AMOUNT_MSG;
              $response = FALSE;
             }elseif(!$this->chkTransDate($transDate)) {
              $responseMsg = self::ERROR_INVALID_TRANS_DATE_MSG;
              $response = FALSE;
             }elseif(!$this->chkBnkTransID($bankTransID)) {
              $responseMsg = self::ERROR_INVALID_BANK_TRANS_MSG;
              $response = FALSE;
             }elseif(!$this->chkPayMode($payMode)) {
              $responseMsg = self::ERROR_INVALID_PAY_MODE_MSG;
              $response = FALSE;
             }elseif(!$this->chkISureID($iSureID)) {
              $responseMsg = self::ERROR_INVALID_ISURE_ID_MSG;
              $response = FALSE;
             } elseif(!$this->chkDuplicate(array('isure_id'=> $iSureID))) {
                $responseMsg = self::ERROR_DUPLICATE_ISURE_ID_MSG;
                $response = FALSE;
            }/*elseif(!$this->chkDuplicate(array('Txn_Ref_No'=>(string) trim($resp->Txn_Ref_No)))) {
                $responseMsg = self::ERROR_TRAN_REF_NO_USED_MSG;
                $response = FALSE;
            }*/
             
            
            if($response){
                $agentObj = new AgentUser();
                $agentInfo = $agentObj->getAgentBindingByMobile($mobileNo);  
            
                if(!empty($agentInfo)){
                    if($agentInfo['enroll_status'] != STATUS_APPROVED) {
                        $status = STATUS_IPAY_REJECT;
                        $responseMsg = 'This partner is not approved yet';
                    } else if($agentInfo['status'] != STATUS_UNBLOCKED) {
                        $status = STATUS_IPAY_REJECT;
                        $responseMsg = 'This partner is Blocked';
                    } else {
                        //Work here for Agent Exist           
                        $iPayArr['agent_id'] = $agentInfo['id'] ; 
                        $iPayArr['agent_mobile'] = $mobileNo ;
                        $iPayArr['amount'] = $amount ; 
                        $iPayArr['bank_transaction_id'] = $bankTransID;
                        $iPayArr['pay_mode'] = (string) trim($resp->Pay_Mode) ;
                        $iPayArr['isure_id'] = $iSureID;
                        $iPayArr['micro_code'] = (string) trim($resp->MICR_CODE) ;
                        $iPayArr['txn_ref_no'] = (string) trim($resp->Txn_Ref_No) ;                        
                        $iPayArr['bank_name'] = (string) trim($resp->Bank_Name) ;
                        $iPayArr['branch_name'] = (string) trim($resp->Branch_Name) ;
                        $iPayArr['instrument_number'] = (string) trim($resp->Instrument_Number) ;
                        $iPayArr['instrument_date'] = Util::returnDateFormattedFromString((string) trim($resp->Instrument_date) , "Ydm", "Y-m-d", "-");
                        $iPayArr['transaction_date'] = Util::returnDateFormattedFromString($transDate , "Ydm", "Y-m-d", "-");
                        $iPayArr['status'] = STATUS_PENDING;

                        $agentIpay = $agentObj->setAgentFundingIpay($iPayArr); 

                        if($agentIpay) {
                            $status = STATUS_IPAY_ACCEPT;
                            $responseMsg = '';
                        } else {
                            $status = STATUS_IPAY_REJECT;
                            $responseMsg = 'This partner payment is not approved';
                        }
                    }
                } else {
                    $status = STATUS_IPAY_REJECT;
                    $responseMsg = 'Partner does not exist'; 
                }
            }
            $responseObj = new stdClass();
            $responseObj->Client_Code = (string) $resp->Client_Code;
            $responseObj->Client_Validate_1 = (string) trim($resp->Client_Validate_1); 
            $responseObj->Txn_Ref_No = (string) trim($resp->Txn_Ref_No);
            $responseObj->Status = $status;
            $responseObj->Reject_Reason = $responseMsg; 
            return  $responseObj;
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $code = $e->getCode();
            $code = (empty($code)) ? self::INVALID_PROCESS_CODE : $code;
            $message = $e->getMessage();
            $message = (empty($message)) ? self::UNABLE_TO_PROCESS_MSG : $message;
            $this->_soapServer->_getLogger()->__setException($message);
            return self::Exception($message, $code);
        }
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