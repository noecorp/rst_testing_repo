<?php
/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_Services_RatnakarMvc extends App_ApiServer_Exchange
{
    private $_soapServer;
    const TP_ID = TP_RBLMVC_ID;
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
    const CUSTOMER_REGISTRATION_FAIL_MSG = 'Unable to register Customer';
    
    const CUSTOMER_REGISTRATION_SUCC_CODE = '0';    
    const CUSTOMER_REGISTRATION_SUCC_MSG = 'Customer Registered Successfully';    
    
    const CUSTOMER_LOAD_FAIL_CODE = '111';    
    
    const CUSTOMER_LOAD_SUCC_CODE = '0';    
    const CUSTOMER_LOAD_SUCC_MSG = 'Successfully Registered Load Request';    
    const CUSTOMER_LOAD_FAIL_MSG = 'Unable to Load Customer’s account';    
    
    const CUSTOMER_ACTIVATION_SUCC_CODE = '0';    
    const CUSTOMER_ACTIVATION_SUCC_MSG = 'Card Activated Successfully';   
    
    const CUSTOMER_ACTIVATION_FAIL_CODE = '120';        
    const CUSTOMER_ACTIVATION_FAIL_MSG = 'Unable to Activate Card';    

    const REQUEST_TYPE_TRANSFER = 'L';
    const CUST_IDENTIFIER_TYPE_MOBILE = 'M';
    const CUST_IDENTIFIER_TYPE_PARTNER = 'P';
    
    const OTP_SUCCSSES_RESPONSE_CODE = '0';
    const OTP_SUCCSSES_RESPONSE_MSG = 'OTP Sent Successfully';

    const OTP_FAILED_RESPONSE_CODE = '115';
    const OTP_FAILED_RESPONSE_MSG = 'Unable to process OTP request';    
    
    const OTP_INVALID_RESPONSE_CODE = '115';
    const OTP_INVALID_RESPONSE_MSG = 'INVALID OTP'; 
    
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
    
    
    public function AccountInformationRequest ($obj) {
        
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);            
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml,__FUNCTION__);
            if( !isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                 return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
             }
             
            if( !isset($resp->TxnIdentifierType) || empty($resp->TxnIdentifierType)) {
                 return self::Exception("Invalid TxnIdentifierType", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_CRN) || strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MID))) {
                 return self::Exception("Invalid TxnIdentifierType " .$resp->TxnIdentifierType, App_ApiServer_Exchange::$INVALID_RESPONSE);                
            }
            
            if( !isset($resp->MemberIDCardNo) || empty($resp->MemberIDCardNo)) {
                 return self::Exception("Invalid MemberIDCardNo", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if( (!isset($resp->ProductCode) || empty($resp->ProductCode)) && $resp->TxnIdentifierType == self::TXN_IDENTIFIER_TYPE_MID) {
                 return self::Exception("Invalid ProductCode", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            try {

                if(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MID))
                {
                    $corp = new Corp_Ratnakar_Cardholders();
                    $productCode = (string) $resp->ProductCode;
                    $memberId = (string) $resp->MemberIDCardNo;
                    $cardholderInfo = $corp->getCardholderInfoByMAID($productCode, $memberId);
                    if(empty($cardholderInfo) || !isset($cardholderInfo['card_number'])) {
                        return self::Exception("Account not found", App_ApiServer_Exchange::$INVALID_RESPONSE);                        
                    }
                    
                    $address    = $cardholderInfo['address_line1']. ' '. $cardholderInfo['address_line2'] .', '.$cardholderInfo['city'].' - '.$cardholderInfo['pincode'];
                    $mobile     = (isset($cardholderInfo['mobile']) && !empty($cardholderInfo['mobile'])) ? $cardholderInfo['mobile'] : ' ';
                    $email      = (isset($cardholderInfo['email']) && !empty($cardholderInfo['email'])) ? $cardholderInfo['email'] : ' ';
                    $dob        = (isset($cardholderInfo['date_of_birth']) && !empty($cardholderInfo['date_of_birth'])) ? $cardholderInfo['date_of_birth'] : ' ';
                    $mmName     = (isset($cardholderInfo['mother_maiden_name']) && !empty($cardholderInfo['mother_maiden_name'])) ? $cardholderInfo['mother_maiden_name'] : ' ';
                    $AccountBlockStatus     = '1';
                    $AccountClose           = '1';
                    $ActivationStatus       = '1';
                    $AccountBlockDateTime   = ' ';
                    
                    $cardNumber = $cardholderInfo['card_number'];
                    
                } else {
                    $cardNumber = (string) $resp->MemberIDCardNo;                    
                    $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CORP);                
                    $productModel = new Products();
                    $productInfo = $productModel->getProductInfoByUnicode($product->product->unicode);
                    if(!empty($productInfo)) {
                        $corp = new Corp_Ratnakar_Cardholders();
                        $cardholderInfo = $corp->getCardholderInfoByCardNumber(array(
                                'card_number'   => $cardNumber,
                                'product_id'   => $productInfo['id'],
                        ));
                        if(empty($cardholderInfo) || !isset($cardholderInfo['card_number'])) {
                            return self::Exception("Card not found", App_ApiServer_Exchange::$INVALID_RESPONSE);                        
                        }                        
                        $address    = $cardholderInfo['address_line1']. ' '. $cardholderInfo['address_line2'] .', '.$cardholderInfo['city'].' - '.$cardholderInfo['pincode'];
                        $mobile     = (isset($cardholderInfo['mobile']) && !empty($cardholderInfo['mobile'])) ? $cardholderInfo['mobile'] : ' ';
                        $email      = (isset($cardholderInfo['email']) && !empty($cardholderInfo['email'])) ? $cardholderInfo['email'] : ' ';
                        $dob        = (isset($cardholderInfo['date_of_birth']) && !empty($cardholderInfo['date_of_birth'])) ? $cardholderInfo['date_of_birth'] : ' ';
                        $mmName     = (isset($cardholderInfo['mother_maiden_name']) && !empty($cardholderInfo['mother_maiden_name'])) ? $cardholderInfo['mother_maiden_name'] : ' ';
                        $AccountBlockStatus     = '1';
                        $AccountClose           = '1';
                        $ActivationStatus       = '1';
                        $AccountBlockDateTime   = '';
                        
                    } else {
                        return self::Exception("Unable to fetch product information", App_ApiServer_Exchange::$INVALID_RESPONSE);                                                
                    }

                }

                $responseObj = new stdClass();
                $responseObj->SessionID             = (string) $resp->SessionID;
                $responseObj->TxnIdentifierType     = (string) $resp->TxnIdentifierType;
                $responseObj->MemberIDCardNo        = (string) $resp->MemberIDCardNo;
                $responseObj->MobileNumber          = $mobile;
                $responseObj->EmailID               = $email;
                $responseObj->DateofBirth           = $dob;
                $responseObj->MothersMaidenName     = $mmName;
                $responseObj->Address               = $address;
                $responseObj->AccountBlockStatus    = $address;
                $responseObj->Address               = $address;
                $responseObj->AccountBlockStatus    = $AccountBlockStatus;
                $responseObj->AccountClose          = $AccountClose;
                $responseObj->ActivationStatus      = $ActivationStatus;
                $responseObj->AccountBlockDateTime  = $AccountBlockDateTime;
                $responseObj->ResponseCode   = self::ACCOUNT_INFORMATION_RESPONSE_CODE;
                $responseObj->ResponseMessage= self::ACCOUNT_INFORMATION_RESPONSE_MSG;
                return $responseObj;               
            } catch (App_Exception $e) {
                $this->_soapServer->_getLogger()->__setException($e->getMessage());                    
               App_Logger::log(serialize($e), Zend_Log::ERR);//exit;
               return self::Exception($e->getMessage(), '12'); 
               
            }
            
        } catch (Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());                
            App_Logger::log(serialize($e), Zend_Log::ERR);
             return self::Exception($e->getMessage(), self::$INVALID_METHOD);
        }
    }    
    
    
    public function AccountStatusRequest ($obj) {
        
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml,__FUNCTION__);
            if( !isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                 return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
             }
             
            if( !isset($resp->TxnIdentifierType) || empty($resp->TxnIdentifierType)) {
                 return self::Exception("Invalid TxnIdentifierType", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_CRN) || strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MID))) {
                 return self::Exception("Invalid TxnIdentifierType " .$resp->TxnIdentifierType, App_ApiServer_Exchange::$INVALID_RESPONSE);                
            }
            
            if( !isset($resp->MemberIDCardNo) || empty($resp->MemberIDCardNo)) {
                 return self::Exception("Invalid MemberIDCardNo", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if( (!isset($resp->ProductCode) || empty($resp->ProductCode)) && $resp->TxnIdentifierType == self::TXN_IDENTIFIER_TYPE_MID) {
                 return self::Exception("Invalid ProductCode", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            try {

                if(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MID))
                {
                    $corp = new Corp_Ratnakar_Cardholders();
                    $productCode = (string) $resp->ProductCode;
                    $memberId = (string) $resp->MemberIDCardNo;
                    $cardholderInfo = $corp->getCardholderInfoByMAID($productCode, $memberId);
                    if(empty($cardholderInfo) || !isset($cardholderInfo['card_number'])) {
                        return self::Exception("Account not found", App_ApiServer_Exchange::$INVALID_RESPONSE);                        
                    }
                    if($cardholderInfo['status'] == STATUS_BLOCKED) {
                        $AccountBlockStatus     = '0';
                        $AccountBlockDateTime   = $cardholderInfo['date_blocked'];                        
                    } else {
                        $AccountBlockStatus     = '1';
                        $AccountBlockDateTime   = ' ';                                                
                    }
                    if($cardholderInfo['status'] == STATUS_ACTIVE) {
                        $AccountClose           = '1';
                        $ActivationStatus       = '1';
                    } else {
                        $AccountClose           = '2';
                        $ActivationStatus       = '0';
                    }
                    $cardNumber = $cardholderInfo['card_number'];
                    
                } else {
                    $cardNumber = (string) $resp->MemberIDCardNo;                    
                    $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CORP);                
                    $productModel = new Products();
                    $productInfo = $productModel->getProductInfoByUnicode($product->product->unicode);
                    if(!empty($productInfo)) {
                        $corp = new Corp_Ratnakar_Cardholders();
                        $cardholderInfo = $corp->getCardholderInfoByCardNumber(array(
                                'card_number'   => $cardNumber,
                                'product_id'   => $productInfo['id'],
                        ));
                        if(empty($cardholderInfo) || !isset($cardholderInfo['card_number'])) {
                            return self::Exception("Card not found", App_ApiServer_Exchange::$INVALID_RESPONSE);                        
                        }                       
                        
                        if($cardholderInfo['status'] == STATUS_BLOCKED) {
                            $AccountBlockStatus     = '0';
                            $AccountBlockDateTime   = $cardholderInfo['date_blocked'];                        
                        } else {
                            $AccountBlockStatus     = '1';
                            $AccountBlockDateTime   = ' ';                                                
                        }
                        if($cardholderInfo['status'] == STATUS_ACTIVE) {
                            $AccountClose           = '1';
                            $ActivationStatus       = '1';
                        } else {
                            $AccountClose           = '2';
                            $ActivationStatus       = '0';
                        }
                        $cardNumber = $cardholderInfo['card_number'];
                        
                        
                    } else {
                        return self::Exception("Unable to fetch product information", App_ApiServer_Exchange::$INVALID_RESPONSE);                                                
                    }

                }

                $responseObj = new stdClass();
                $responseObj->SessionID             = (string) $resp->SessionID;
                $responseObj->TxnIdentifierType     = (string) $resp->TxnIdentifierType;
                $responseObj->MemberIDCardNo        = (string) $resp->MemberIDCardNo;
                $responseObj->AccountBlockStatus    = $AccountBlockStatus;
                $responseObj->AccountClose          = $AccountClose;
                $responseObj->ActivationStatus      = $ActivationStatus;
                $responseObj->AccountBlockDateTime  = $AccountBlockDateTime;
                $responseObj->ResponseCode   = self::ACCOUNT_STATUS_RESPONSE_CODE;
                $responseObj->ResponseMessage= self::ACCOUNT_STATUS_RESPONSE_MSG;
                return $responseObj;               
            } catch (App_Exception $e) {
               $this->_soapServer->_getLogger()->__setException($e->getMessage());                    
               App_Logger::log(serialize($e), Zend_Log::ERR);
               return self::Exception($e->getMessage(), '12'); 
            }
            
        } catch (Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());                
            App_Logger::log(serialize($e), Zend_Log::ERR);
            return self::Exception($e->getMessage(), self::$INVALID_METHOD);
        }
    }    
    
    
    
    public function AccountBlockRequest ($obj) {
        
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);            
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml,__FUNCTION__);
            if( !isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                 return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
             }
             
            if( !isset($resp->TxnIdentifierType) || empty($resp->TxnIdentifierType)) {
                 return self::Exception("Invalid TxnIdentifierType", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_CRN) || strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MID))) {
                 return self::Exception("Invalid TxnIdentifierType " .$resp->TxnIdentifierType, App_ApiServer_Exchange::$INVALID_RESPONSE);                
            }
            
            if( !isset($resp->MemberIDCardNo) || empty($resp->MemberIDCardNo)) {
                 return self::Exception("Invalid MemberIDCardNo", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if( (!isset($resp->ProductCode) || empty($resp->ProductCode)) && $resp->TxnIdentifierType == self::TXN_IDENTIFIER_TYPE_MID) {
                 return self::Exception("Invalid ProductCode", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            try {

                if(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MID))
                {
                    $corp = new Corp_Ratnakar_Cardholders();
                    $productCode = (string) $resp->ProductCode;
                    $memberId = (string) $resp->MemberIDCardNo;
                    $flg = $corp->blockCardholderInfoByMAID($productCode, $memberId);
                    if($flg == FALSE) {
                        return self::Exception("Account not found", App_ApiServer_Exchange::$INVALID_RESPONSE);                        
                    }
                    $cardholderInfo = $corp->getCardholderInfoByMAID($productCode, $memberId);                    
                    $AccountBlockDateTime   = $cardholderInfo['date_blocked'];
                    if($cardholderInfo['status'] == STATUS_BLOCKED) {
                        $AccountStatus = '0';
                    } else {
                        $AccountStatus = '1';
                    }                    
                    
                } else {
                    $cardNumber = (string) $resp->MemberIDCardNo;                    
                    $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CORP);                
                    $productModel = new Products();
                    $productInfo = $productModel->getProductInfoByUnicode($product->product->unicode);
                    if(!empty($productInfo)) {
                        $corp = new Corp_Ratnakar_Cardholders();
                        $flg = $corp->blockCardholderInfoByCardNumber(array(
                                'card_number'   => $cardNumber,
                                'product_id'   => $productInfo['id'],
                        ));
                        if($flg == FALSE) {
                            return self::Exception("Account not found", App_ApiServer_Exchange::$INVALID_RESPONSE);                        
                        }
                        $cardholderInfo = $corp->getCardholderInfoByCardNumber(array(
                                'card_number'   => $cardNumber,
                                'product_id'   => $productInfo['id'],
                        ));                        
                        $AccountBlockDateTime   = $cardholderInfo['date_blocked'];
                        if($cardholderInfo['status'] == STATUS_BLOCKED) {
                            $AccountStatus = '0';
                        } else {
                            $AccountStatus = '1';
                        }
                        
                    } else {
                        return self::Exception("Unable to fetch product information", App_ApiServer_Exchange::$INVALID_RESPONSE);                                                
                    }

                }

                $responseObj = new stdClass();
                $responseObj->SessionID             = (string) $resp->SessionID;
                $responseObj->TxnIdentifierType     = (string) $resp->TxnIdentifierType;
                $responseObj->MemberIDCardNo        = (string) $resp->MemberIDCardNo;
                $responseObj->ActivationStatus      = $AccountStatus;
                $responseObj->AccountBlockDateTime  = $AccountBlockDateTime;
                $responseObj->ResponseCode   = self::ACCOUNT_BLCOKED_RESPONSE_CODE;
                $responseObj->ResponseMessage= self::ACCOUNT_BLCOKED_RESPONSE_MSG;
                return $responseObj;               
            } catch (App_Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());                    
               App_Logger::log(serialize($e), Zend_Log::ERR);
               return self::Exception($e->getMessage(), '12'); 
            }
            
        } catch (Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());                
            App_Logger::log(serialize($e), Zend_Log::ERR);
             return self::Exception($e->getMessage(), self::$INVALID_METHOD);
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
            } elseif(!(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_CRN) || strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MID))) {
                 return self::Exception("Invalid TxnIdentifierType " .$resp->TxnIdentifierType, App_ApiServer_Exchange::$INVALID_RESPONSE);                
            }
            
            if( !isset($resp->MemberIDCardNo) || empty($resp->MemberIDCardNo)) {
                 return self::Exception("Invalid MemberIDCardNo", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if( (!isset($resp->ProductCode) || empty($resp->ProductCode)) && $resp->TxnIdentifierType == self::TXN_IDENTIFIER_TYPE_MID) {
                 return self::Exception("Invalid ProductCode", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            try {

                $corp = new Corp_Ratnakar_Cardholders();                
                if(strtolower($resp->TxnIdentifierType) == strtolower(self::TXN_IDENTIFIER_TYPE_MID))
                {
                    $cardholderInfo = $corp->getCardholderInfoByMAID($resp->ProductCode, $resp->MemberIDCardNo);
                    
                    if(empty($cardholderInfo) || !isset($cardholderInfo['card_number'])) {
                        return self::Exception("Card not found", App_ApiServer_Exchange::$INVALID_RESPONSE);                        
                    }
                    $cardNumber = $cardholderInfo['card_number'];
                } else {
                    if(!isset($resp->ProductCode) || $resp->ProductCode == '') {
                        $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CORP);
                        $productModel = new Products();
                        $productInfo = $productModel->getProductInfoByUnicode($product->product->unicode);    
                        if(!isset($productInfo['id']) || empty($productInfo['id'])) {
                            return self::Exception("Unable to fetch Product Info", App_ApiServer_Exchange::$INVALID_RESPONSE);                                                    
                        }
                        $productId = $productInfo['id'];
                    } else {
                        $productId = $resp->ProductCode;
                    }                    
                    $cardNumber = (string) $resp->MemberIDCardNo;                    
                    $cardholderInfo = $corp->getCardholderInfoByCardNumber(array(
                        'product_id'  => $productId, 
                        'card_number'  => $cardNumber
                    ));
                    $cardholderInfo = Util::toArray($cardholderInfo);
                }

                if(!isset($cardholderInfo['card_number']) || empty($cardholderInfo)) {
                    return self::Exception("Card not found", App_ApiServer_Exchange::$INVALID_RESPONSE);                        
                }                                        

                if(isset($resp->WalletCode) && !empty($resp->WalletCode)) {
                    $purseInfo = $corp->getPurseInfoByCode($resp->WalletCode);
                    if(empty($purseInfo)) {
                        return self::Exception("Invallid WalletCode", App_ApiServer_Exchange::$INVALID_RESPONSE);                                                
                    }
                    $purseModel = new Corp_Ratnakar_CustomerPurse();
                    $custPurseInfo = $purseModel->getCustPurseDetails(array(
                        'rat_customer_id'   => $cardholderInfo['rat_customer_id'],
                        'purse_master_id'   => $purseInfo['id']
                    ));

                    if(isset($custPurseInfo['amount']) && !empty($custPurseInfo['amount'])) {
                        $balance = $custPurseInfo['amount'];
                    } else {
                        return self::Exception("Unable to fetch Balance", App_ApiServer_Exchange::$INVALID_RESPONSE);                                                                        
                    }
                } else {
                    
                    $purseModel = new Corp_Ratnakar_CustomerPurse();
                    $custPurseInfo = $purseModel->getAllPurse(array(
                        'rat_customer_id'   => $cardholderInfo['rat_customer_id']
                    ));
                    if(!empty($custPurseInfo)) {
                        $balance = 0;
                        foreach ($custPurseInfo as $purse) {
                            if(isset($purse['amount'])) {
                                $balance = $balance + $purse['amount'];
                            }
                        }
                    } else {
                        return self::Exception("Unable to fetch Balance", App_ApiServer_Exchange::$INVALID_RESPONSE);                                                                                                
                    }
                }
                $responseObj = new stdClass();
                $responseObj->SessionID         = (string) $resp->SessionID;
                $responseObj->TxnIdentifierType = (string) $resp->TxnIdentifierType;
                $responseObj->MemberIDCardNo    = (string) $resp->MemberIDCardNo;
                $responseObj->WalletCode        = (string) $resp->WalletCode;
                $responseObj->AvailableBalance  = Util::filterAmount($balance);
                $responseObj->ResponseCode      = self::BALANCE_ENQUIRY_RESPONSE_CODE;
                $responseObj->ResponseMessage   = self::BALANCE_ENQUIRY_RESPONSE_MSG;
                return $responseObj;               
            } catch (App_Exception $e) {
                $this->_soapServer->_getLogger()->__setException($e->getMessage());                    
               App_Logger::log(serialize($e), Zend_Log::ERR);//exit;
               return self::Exception($e->getMessage(), '12'); 
            }
            
        } catch (Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());                
            App_Logger::log(serialize($e), Zend_Log::ERR);
             return self::Exception($e->getMessage(), self::$INVALID_METHOD);
        }
    }    
    
    
    


    public function MVCRegistrationRequest() {//Do not add comments for method summary
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);

            if (!isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                return self::Exception($this->getMessage('login_failed'), App_ApiServer_Exchange::$INVALID_LOGIN);
            }

            if (!isset($resp->ProductCode) || empty($resp->ProductCode) || !$this->validateProductCodeByConst($resp->ProductCode,PRODUCT_CONST_RAT_MVC)) {
                return self::Exception(parent::INVALID_PRODUCT_MSG, parent::INVALID_PRODUCT_CODE);
            }

            if (!isset($resp->ARN) || empty($resp->ARN)) {
                return self::Exception('Please provide Application Reference Number', App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator((string) $resp->ARN, self::FIELD_TYPE_ALNUM, '1', '10')) {
                return self::Exception('Invalid Application Reference Number:'.(string) $resp->ARN, App_ApiServer_Exchange::$INVALID_RESPONSE);
            }


            if (!isset($resp->Title) || empty($resp->Title)) {
                return self::Exception($this->getMessage('title_invalid'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator($resp->Title, self::FIELD_TYPE_TITLE)) {
                return self::Exception($this->getMessage('title_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->FirstName) || empty($resp->FirstName)) {
                return self::Exception($this->getMessage('first_name_invalid'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator((string) $resp->FirstName, self::FIELD_TYPE_STRING_ALPHA, 1, 50)) {
                return self::Exception($this->getMessage('first_name_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }


            if (!isset($resp->MiddleName) || empty($resp->MiddleName)) {
                //return self::Exception($this->getMessage('invaild_mobile'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator((string) $resp->MiddleName, self::FIELD_TYPE_STRING_ALPHA, '1', '50')) {
                return self::Exception($this->getMessage('middlename_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->LastName) || empty($resp->LastName)) {
                return self::Exception($this->getMessage('lastname_invalid'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator((string) $resp->LastName, self::FIELD_TYPE_STRING_ALPHA, '1', '50')) {
                return self::Exception($this->getMessage('lastname_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }


            if (!isset($resp->Gender) || empty($resp->Gender)) {
                return self::Exception($this->getMessage('gender_invalid'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator($resp->Gender, self::FIELD_TYPE_GENDER)) {
                return self::Exception($this->getMessage('gender_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->DateOfBirth) || empty($resp->DateOfBirth)) {
                return self::Exception($this->getMessage('dob_invalid'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator($resp->DateOfBirth, self::FIELD_TYPE_DOB)) {
                return self::Exception($this->getMessage('dob_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->Mobile) || empty($resp->Mobile)) {
                return self::Exception($this->getMessage('mobile_invalid'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator((string) $resp->Mobile, self::FIELD_TYPE_MOBILE)) {
                return self::Exception($this->getMessage('mobile_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }


             if (!isset($resp->Email) || empty($resp->Email)) {
                //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator((string) $resp->Email, self::FIELD_TYPE_EMAIL, '1', '50')) {
                return self::Exception($this->getMessage('email_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->MVCType) || empty($resp->MVCType)) {
                return self::Exception("Please provide MVC Type", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!Util::isValidMVCType((string) $resp->MVCType)) {
                return self::Exception('Invalid MVC Type', App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->MotherMaidenName) || empty($resp->MotherMaidenName)) {
                return self::Exception($this->getMessage('mothermaidenname_invalid'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator((string) $resp->MotherMaidenName, self::FIELD_TYPE_STRING_ALPHA, '1', '25')) {
                return self::Exception($this->getMessage('mothermaidenname_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->Landline) || empty($resp->Landline)) {
                //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator((string) $resp->Landline, self::FIELD_TYPE_NUMBER, '1', '15')) {
                ////////return self::Exception($this->getMessage('landline_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }

            if (!isset($resp->AddressLine1) || empty($resp->AddressLine1)) {
                return self::Exception("Address Line 1 missing", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator((string) $resp->AddressLine1, self::FIELD_TYPE_STRING, '1', '50')) {
                return self::Exception($this->getMessage('address1_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->AddressLine2) || empty($resp->AddressLine2)) {
                //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator((string) $resp->AddressLine2, self::FIELD_TYPE_STRING, '1', '50')) {
                //return self::Exception($this->getMessage('address2_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }

            if (!isset($resp->City) || empty($resp->City)) {
                return self::Exception("Invalid City Provided", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator((string) $resp->City, self::FIELD_TYPE_STRING_ALPHA, '1', '50')) {
                return self::Exception($this->getMessage('city_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->State) || empty($resp->State)) {
                return self::Exception("State Missing", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator((string) $resp->State, self::FIELD_TYPE_STRING_ALPHA, '1', '50')) {
                return self::Exception($this->getMessage('state_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->Country) || empty($resp->Country)) {
                return self::Exception("Country Missing", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator((string) $resp->Country, self::FIELD_TYPE_STRING_ALPHA, '1', '5')) {
    //            return self::Exception('Invalid Country Provided', App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->Pincode) || empty($resp->Pincode)) {
                return self::Exception("Pincode Missing", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator((string) $resp->Pincode, self::FIELD_TYPE_NUMBER, '6', '6')) {
                return self::Exception($this->getMessage('pincode_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }

            if (!isset($resp->Education) || empty($resp->Education)) {
                //return self::Exception("Education Qualification is missing", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } //elseif(!$this->fieldValidator((string) $resp->Education,self::FIELD_TYPE_NUMBER,'1','6')) {
            //return self::Exception($this->getMessage('pincode_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            //}

            if (!isset($resp->BankAccount) || empty($resp->BankAccount)) {
                return self::Exception("Bank Account is missing", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->isValid($this->_ENUM_YN, strtolower($resp->BankAccount))) {
                return self::Exception($this->getMessage("invalid_bankaccount") . strtolower($resp->BankAccount), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->VehicleType) || empty($resp->VehicleType)) {
                //return self::Exception("Vehicle Type Missing", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator((string) $resp->VehicleType, self::FIELD_TYPE_STRING, '1', '20')) {
                return self::Exception($this->getMessage('invalid_vehicletype'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!isset($resp->NoOfFamilyMember) || empty($resp->NoOfFamilyMember)) {
                return self::Exception("No. Of Family Member Missing", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator((string) $resp->NoOfFamilyMember, self::FIELD_TYPE_NUMBER, '1', '2')) {
                return self::Exception($this->getMessage('invalid_nooffamailymember'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            if (!empty($resp->MVCType) && (strtolower($resp->MVCType) == CUSTOMER_MVC_TYPE_MVCC)) {
                if (!isset($resp->DeviceId) || empty($resp->DeviceId)) {
                    return self::Exception("DeviceID is missing", App_ApiServer_Exchange::$INVALID_RESPONSE);
                } elseif (!$this->fieldValidator((string) $resp->DeviceId, self::FIELD_TYPE_STRING, '1', '20')) {
                    return self::Exception($this->getMessage('invalid_deviceid'), App_ApiServer_Exchange::$INVALID_RESPONSE);
                }
            }
            
            $otp = (string) trim($resp->OTP);
            if ($otp != '') {
                if (strlen($otp) != 6 || !(ctype_digit($otp))) {
                    throw new Exception(self::OTP_INVALID_RESPONSE_MSG, self::OTP_INVALID_RESPONSE_CODE);
                }
            } else {
                throw new Exception(self::OTP_INVALID_RESPONSE_MSG, self::OTP_INVALID_RESPONSE_CODE);
            }
            
            try {
                $refObject = new Reference();
                $baseTxn = new BaseTxn();
                $obj = new Corp_Ratnakar_Cardholders();
                $txnCode = $baseTxn->generateTxncode();
                $params = array();

                $responseOTP = $refObject->verifyCustomerOTPAPI(array(
                    'request_type' => 'R',
                    'otp' => (string) trim($resp->OTP),
                    'mobile' => (string) trim($resp->Mobile),
                ));

                if ($responseOTP == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = $txnCode;
                    $responseObj->ResponseCode = self::OTP_INVALID_RESPONSE_CODE;
                    $responseObj->ResponseMessage = self::OTP_INVALID_RESPONSE_MSG;
                    return $responseObj;
                }

                $params['product_id'] = (string) $resp->ProductCode;
                $params['arn'] = (string) $resp->ARN;
                $params['title'] = (string) $resp->Title;
                $params['first_name'] = (string) $resp->FirstName;
                $params['middle_name'] = (string) $resp->MiddleName;
                $params['last_name'] = (string) $resp->LastName;
                $params['gender'] = (string) $resp->Gender;
                $params['date_of_birth'] = (string) $resp->DateOfBirth;
                $params['mobile_number'] = (string) $resp->Mobile;
                $params['email'] = (string) $resp->Email;
                $params['customer_mvc_type'] = (string) strtolower($resp->MVCType);
                $params['mother_maiden_name'] = (string) $resp->MotherMaidenName;
                $params['landline'] = (string) $resp->Landline;
                $params['address_line1'] = (string) $resp->AddressLine1;
                $params['address_line2'] = (string) $resp->AddressLine2;
                $params['city'] = (string) $resp->City;
                $params['state'] = (string) $resp->State;
                $params['country'] = (string) $resp->Country ? (string) $resp->Country : COUNTRY_CODE_INDIA;
                $params['pincode'] = (string) $resp->Pincode;
                $params['educational_qualifications'] = (string) $resp->Education;
                $params['already_bank_account'] = (string) $resp->BankAccount;
                $params['vehicle_type'] = (string) $resp->VehicleType;
                $params['family_members'] = (string) $resp->NoOfFamilyMember;
                $params['device_id'] = (string) $resp->DeviceId;
                $params['reg_agent_id'] = API_MVC_AGENT_ID;
                $params['txnCode'] = $txnCode;
		$params['channel'] = CHANNEL_API;
                
                /*
                 * checkDuplicateMobile : Checking duplicate mobile number for customer
                 */
                $respMobile = $obj->checkDuplicateMobile(array(
                    'product_id' => $params['product_id'],
                    'mobile' => (string) trim($resp->Mobile),
                ));

                if (!empty($respMobile['id'])) {
                    $txnCode = $respMobile['txncode'];
                    return self::Exception('Mobile Number already in use', App_ApiServer_Exchange::$INVALID_RESPONSE, $txnCode);
                }
                
                try {
                    $mvc = new Mvc_Ratnakar_CardholderUser();
                    $respCustID = $mvc->addCardHolderAPI($params);
                    
                    if ($respCustID > 0) {
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->MOB = (string) $resp->Mobile;
                        $responseObj->ResponseCode = self::CUSTOMER_REGISTRATION_SUCC_CODE;
                        $responseObj->ResponseMessage = self::CUSTOMER_REGISTRATION_SUCC_MSG;
                        
                        $refObject->updateCustomerOTPAPI(array(
                            'request_type' => 'R',
                            'id' => $responseOTP['id'],
                        ));
                    } else {
                        $errorMsg = $obj->getError();
                        $errorMsg = empty($errorMsg) ? self::CUSTOMER_REGISTRATION_FAIL_MSG : $errorMsg;
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->MOB = (string) $resp->Mobile;
                        $responseObj->ResponseCode = self::CUSTOMER_REGISTRATION_FAIL_CODE;
                        $responseObj->ResponseMessage = $errorMsg;                        
                    }
                    return $responseObj;
                } catch (Exception $e) {
                    App_Logger::log(serialize($e), Zend_Log::ERR);
                    $this->_soapServer->_getLogger()->__setException($e->getMessage());
                    return self::Exception($e->getMessage(), self::CUSTOMER_REGISTRATION_FAIL_CODE);
                }
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
    
    
    public function CardTransactionRequest() {//Do not add comments for method summary
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);

            if (!isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                return self::Exception($this->getMessage('login_failed'), App_ApiServer_Exchange::$INVALID_LOGIN);
            }

            if (!isset($resp->ProductCode) || empty($resp->ProductCode) || !$this->validateProductCodeByConst($resp->ProductCode,PRODUCT_CONST_RAT_MVC)) {
                return self::Exception(parent::INVALID_PRODUCT_MSG, parent::INVALID_PRODUCT_CODE);
            }
            
            if (!isset($resp->TxnIdentifierType) || empty($resp->TxnIdentifierType) || (strtolower($resp->TxnIdentifierType) != strtolower(self::TYPE_MOB))) {
                return self::Exception('Invalid Txn Identifier Type', App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if (!isset($resp->MemberIDCardNo) || empty($resp->MemberIDCardNo) ) {
                return self::Exception('Invalid parameter MemberIDCardNo', App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator((string) $resp->MemberIDCardNo, self::FIELD_TYPE_MOBILE)) {
                return self::Exception('Invalid parameter MemberIDCardNo', App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if (!isset($resp->Amount) || empty($resp->Amount) ) {
                return self::Exception($this->getMessage('amount_invalid'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!is_numeric((string) trim($resp->Amount))) {
                throw new Exception($this->getMessage('amount_invalid'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if (!isset($resp->Currency) || empty($resp->Currency) ) {
                return self::Exception('Invalid Currency', App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif ((string) trim($resp->Currency) != CURRENCY_INR_CODE) {
                throw new Exception('Invalid Currency', App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if (!isset($resp->Narration) || empty($resp->Narration) ) {
                return self::Exception('Invalid Narration', App_ApiServer_Exchange::$INVALID_RESPONSE);
            } 
            
            if (!isset($resp->TxnNo) || empty($resp->TxnNo) ) {
                return self::Exception('Invalid TxnNo', App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->TxnNo, self::FIELD_TYPE_NUMBER, '1', '20')) {
                throw new Exception('Invalid TxnNo', App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if (!isset($resp->TxnIndicator) || empty($resp->TxnIndicator) ) {
                return self::Exception('Invalid parameter TxnIndicator', App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(strlen($resp->TxnIndicator) > 2 || !$this->isValid($this->_ENUM_DC, $resp->TxnIndicator)) {
                throw new Exception('Invalid parameter TxnIndicator', App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            $otp = (string) trim($resp->OTP);
            if ($otp != '') {
                if (strlen($otp) != 6 || !(ctype_digit($otp))) {
                    throw new Exception(self::OTP_INVALID_RESPONSE_MSG, self::OTP_INVALID_RESPONSE_CODE);
                }
            } else {
                throw new Exception(self::OTP_INVALID_RESPONSE_MSG, self::OTP_INVALID_RESPONSE_CODE);
            }
            
            try {
                
                $refObject = new Reference();
                //$object = new Mvc_Ratnakar_CardholderUser();
                $object = new Corp_Ratnakar_Cardholders();
                
                $params = array();

                $responseOTP = $refObject->verifyCustomerOTPAPI(array(
                    'request_type' => 'L',
                    'otp' => (string) trim($resp->OTP),
                    'mobile' => (string) trim($resp->MemberIDCardNo),
                ));

                if ($responseOTP == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = self::OTP_INVALID_RESPONSE_CODE;
                    $responseObj->ResponseMessage = self::OTP_INVALID_RESPONSE_MSG;
                    return $responseObj;
                }
                
                $param['product_id'] = (string) trim($resp->ProductCode);
                $param['mobile'] = (string) trim($resp->MemberIDCardNo);
                
                $custInfo = $object->getCustomerInfoBy($param);

                if ($custInfo == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = self::CUSTOMER_NOT_FOUND;
                    $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                    return $responseObj;
                }
                
                $obj = new Corp_Ratnakar_Cardload();
                $productModel = new Products();
                $productID = $productModel->getProductIDbyConst(PRODUCT_CONST_RAT_MVC); 
                
                $loadDetails = $obj->getLoadDetails(array('txn_no' => (string) trim($resp->TxnNo),'product_id'=>$productID));
                if (!empty($loadDetails) ) {
                    $txnCode = $loadDetails['txn_code'];
                    return self::Exception('Transaction number already in use', App_ApiServer_Exchange::$INVALID_RESPONSE, $loadDetails['txn_code']);
                }
                
                $bankObject   = new Banks();
                
                $params['cardholder_id'] = $custInfo['id'];
                $params['product_id'] = (string) trim($resp->ProductCode);
                $params['amount'] = (string) trim($resp->Amount);
                $params['txn_no'] = (string) trim($resp->TxnNo);
                $params['narration'] = (string) trim($resp->Narration);//mob
                $params['mode'] = (string) trim($resp->TxnIndicator);//CR/DR
                $params['by_api_user_id'] = API_MVC_AGENT_ID;
                $params['bank_product_const'] = PRODUCT_CONST_RAT_MVC;
                $params['manageType'] = AGENT_MANAGE_TYPE;
                $params['card_type'] = CORP_CARD_TYPE_NORMAL;
                        
                $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_MVC);
                $params['wallet_code'] = $product->purse->code->genwallet;
                        
                $bankInfo = $bankObject->getBankidByProductid($params['product_id']);
                if(!empty($bankInfo) ){
                    $params['bank_id'] = $bankInfo['bank_id'];    
                }else{
                    throw new Exception(parent::INVALID_PRODUCT_MSG, parent::INVALID_PRODUCT_CODE);   
                }
		$params['channel'] = CHANNEL_API;
                $loadObj = new Corp_Ratnakar_Cardload();
                $response = $loadObj->doCardload($params);
                
                if ($response == TRUE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnNo = (string) $resp->TxnNo;
                    $responseObj->AckNo = $loadObj->getTxncode();
                    $responseObj->ResponseCode = self::CUSTOMER_LOAD_SUCC_CODE;
                    $responseObj->ResponseMessage = self::CUSTOMER_LOAD_SUCC_MSG;                    
                } else {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnNo = (string) $resp->TxnNo;
                    $responseObj->AckNo = $loadObj->getTxncode();
                    $responseObj->ResponseCode = self::CUSTOMER_LOAD_FAIL_CODE;
                    $responseObj->ResponseMessage = self::CUSTOMER_LOAD_FAIL_MSG;
                }
                
                $refObject->updateCustomerOTPAPI(array(
                        'request_type' => 'L',
                        'id' => $responseOTP['id'],
                    ));
                
                return $responseObj;
            } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $code = (empty($code)) ? self::CUSTOMER_LOAD_FAIL_CODE : $code;
                $message = $e->getMessage();
                $message = (empty($message)) ? self::CUSTOMER_LOAD_FAIL_MSG : $message;
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $this->_soapServer->_getLogger()->__setException($e->getMessage());
            return self::Exception($e->getMessage(), self::CUSTOMER_LOAD_FAIL_CODE);
        }
    }


    
    public function CardActivationRequest() {//Do not add comments for method summary
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);

            if (!isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                return self::Exception($this->getMessage('login_failed'), App_ApiServer_Exchange::$INVALID_LOGIN);
            }

            if (!isset($resp->ProductCode) || empty($resp->ProductCode) || !$this->validateProductCodeByConst($resp->ProductCode,PRODUCT_CONST_RAT_MEDI)) {
                return self::Exception(parent::INVALID_PRODUCT_MSG, parent::INVALID_PRODUCT_CODE);
            }
            
            if (!isset($resp->TxnIdentifierType) || empty($resp->TxnIdentifierType) || (strtolower($resp->TxnIdentifierType) != strtolower(self::TYPE_MOB))) {
                return self::Exception('Invalid Txn Identifier Type', App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if (!isset($resp->MemberIDCardNo) || empty($resp->MemberIDCardNo) ) {
                return self::Exception('Invalid Mobile Provided', App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
//            
//            if (!isset($resp->DOB) || empty($resp->DOB) ) {
//                return self::Exception('Invalid DOB Provided', App_ApiServer_Exchange::$INVALID_RESPONSE);
//            } 
            
            if (!isset($resp->CardPackID) || empty($resp->CardPackID) ) {
                return self::Exception('Invalid Card Pack ID', App_ApiServer_Exchange::$INVALID_RESPONSE);
            } 
            
            if (!isset($resp->Narration) || empty($resp->Narration) ) {
                return self::Exception('Invalid Narration', App_ApiServer_Exchange::$INVALID_RESPONSE);
            } 
            
            if (!isset($resp->TxnNo) || empty($resp->TxnNo) ) {
                return self::Exception('Invalid TxnNo', App_ApiServer_Exchange::$INVALID_RESPONSE);
            } 
            

            try {
                //echo $resp->DOB. ' : ';
                //echo Util::returnDateFormatted($resp->DOB, "d-m-y", "Y-m-d", "-");
                //echo date('Y-m-d',strtotime($resp->DOB));
                //echo date_format(date_create_from_format('dmy', $dateString), 'Y-m-d'));exit;
                
                $ratCorp = new Corp_Ratnakar_Cardholders();
                $param = array(
                    'mobile'    => (string) $resp->MemberIDCardNo,
//                    'date_of_birth' => Util::returnDateFormatted($resp->DOB, "d-m-y", "Y-m-d", "-"),
//                    'last_4_digit'  => (string) $resp->Last4Digit,
                    'product_id'  => (string) $resp->ProductCode,
                    'card_pack_id'  => (string) $resp->CardPackID,
                );
                $custTrackModel = new CustomerTrack();
                $cardholderData = $custTrackModel->getCardholderInfoForActivationAPI($param);
                //echo '<pre>';print_r($cardholderData);exit;
                if(empty($cardholderData)) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = self::$INVALID_RESPONSE;
                    $responseObj->ResponseMessage = 'Cadholder not found';
                    return $responseObj;    
                }
            
                if(!empty($cardholderData) && $cardholderData['status'] == STATUS_ACTIVE) {
                    return $this->getCardholderActivationResponse($cardholderData,$resp);
                }
                
                try {
                    $cardholderData['narration'] = (string) $resp->Narration;
                    $cardholderData['txn_code'] = (string) $resp->TxnNo;
                    $response = $ratCorp->ratCorpRegisterActivationPending($cardholderData);
                    
                    if ($response == TRUE) {
                        return $this->getCardholderActivationResponse($cardholderData,$resp);
                    } else {
                        $errorMsg = $ratCorp->getError();
                        $errorMsg = empty($errorMsg) ? self::CUSTOMER_ACTIVATION_FAIL_MSG : $errorMsg;
                        $responseObj = new stdClass();
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->ResponseCode = self::CUSTOMER_ACTIVATION_FAIL_CODE;
                        $responseObj->ResponseMessage = $errorMsg;
                        return $responseObj;                        
                    }

                } catch (Exception $e) {
                    $error = $mvc->getError();
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = self::$INVALID_RESPONSE;
                    $responseObj->ResponseMessage = empty($error) ? $e->getMessage() : $error;
                    return $responseObj;
                }
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $this->_soapServer->_getLogger()->__setException($e->getMessage());
                return self::Exception($e->getMessage(), ErrorCodes::ERROR_SYSTEM_ERROR);
            }
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $this->_soapServer->_getLogger()->__setException($e->getMessage());
            return self::Exception($e->getMessage(), self::CUSTOMER_ACTIVATION_FAIL_CODE);
        }
    }
    
    
    
    private function getCardholderActivationResponse($param, $resp, $status) {

        $responseObj = new stdClass();
        $responseObj->SessionID = (string) $resp->SessionID;
        $responseObj->TxnNo = (string) $resp->TxnNo;
        $responseObj->CardNumber = (string) $param['card_number'];
        $responseObj->CardPackId = (string) $param['card_pack_id'];
        $responseObj->MemberId = (string) $param['medi_assist_id'];
        $responseObj->FirstName = (string) $param['first_name'];
        $responseObj->MiddleName = (string) $param['middle_name'];
        $responseObj->LastName = (string) $param['last_name'];
        $responseObj->Mobile = (string) $param['mobile'];
        $responseObj->Gender = (string) $param['gender'];
        $responseObj->DateOfBirth = (string) $param['date_of_birth'];
        $responseObj->Email = (string) $param['email'];
        $responseObj->MotherMaidenName = (string) $param['mother_maiden_name'];
        $responseObj->AddressLine1 = (string) $param['address_line1'];
        $responseObj->AddressLine2 = (string) $param['address_line2'];
        $responseObj->City = (string) $param['city'];
        $responseObj->State = (string) $param['state'];
        $responseObj->Country = (string) $param['country'];
        $responseObj->Pincode = (string) $param['pincode'];
        
        $responseObj->ResponseCode = self::CUSTOMER_ACTIVATION_SUCC_CODE;
        $responseObj->ResponseMessage = self::CUSTOMER_ACTIVATION_SUCC_MSG;
        return $responseObj;
    }
    
    
    
    public function __call($name, $arguments) {
        
        App_Logger::log('Invalid Method called : '.$name, Zend_Log::ERR);
        App_Logger::log(serialize($arguments), Zend_Log::ERR);
        return self::Exception("System Error", self::$INVALID_METHOD);
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
            if (!isset($resp->ProductCode) || empty($resp->ProductCode) || !$this->validateProductCodeByConst($resp->ProductCode,PRODUCT_CONST_RAT_MVC)) {
                return self::Exception(parent::INVALID_PRODUCT_MSG, parent::INVALID_PRODUCT_CODE);
            }
            
            if (!isset($resp->Mobile) || empty($resp->Mobile)) {
                return self::Exception($this->getMessage('mobile_invalid'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif (!$this->fieldValidator((string) $resp->Mobile, self::FIELD_TYPE_MOBILE)) {
                return self::Exception($this->getMessage('mobile_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            /*
             * Request Type Validation
             */
            if( !isset($resp->RequestType) || empty($resp->RequestType) || !$this->isValid($this->_ENUM_TYPE, strtolower($resp->RequestType))) {
                 return self::Exception($this->getMessage('invalid_req_type'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            /*
             * Narration Validation
             */
            if (strtolower(trim($resp->RequestType)) == strtolower(self::REQUEST_TYPE_TRANSFER )) {
                if ($resp->Narration != '') {
                    if( (strlen($resp->Narration) > 20)  || (!$this->chkallowChar($resp->Narration)) ) {
                      return self::Exception($this->getMessage('invalid_narration'), App_ApiServer_Exchange::$INVALID_RESPONSE);
                   } 
                }
            }
            /*
             * IsOriginal Validation
             */
            if( !empty($resp->IsOriginal)) {
                if(strlen($resp->IsOriginal) > 1 || !$this->isValid($this->_ENUM_YN, strtolower($resp->IsOriginal))) {
                    return self::Exception($this->getMessage('invalid_isoriginal'), App_ApiServer_Exchange::$INVALID_RESPONSE);
                }
            }

            /*
             * OriginalAckNo Validation
             */
            if (strtolower(trim($resp->IsOriginal)) == 'n') {
                if(strlen(trim($resp->OriginalAckNo)) > 12 || !(ctype_digit(trim($resp->OriginalAckNo)))) {
                    return self::Exception($this->getMessage('invalid_originalackno'), App_ApiServer_Exchange::$INVALID_RESPONSE);
                }
            }

            try {

                $object = new CustomerTrack();
                $refObject = new Reference();
                $cardholderModel = new Corp_Ratnakar_Cardholders();
                
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

                    $param['mobile'] = (string) trim($resp->Mobile);     
                    
                    $respMobile = $cardholderModel->checkDuplicateMobile(array(
                        'product_id' => $param['product_id'],
                        'mobile' => $param['mobile'],
                    ));

                if (!empty($respMobile['id'])) {
                    return self::Exception('Mobile Number already in use', App_ApiServer_Exchange::$INVALID_RESPONSE);
                }
                    $agent = API_MVC_AGENT_ID;
                    $gInfo = $refObject->generateRatOTPAPI(array(
                        'type' => (string) trim($resp->RequestType),
                        'mobile' => $param['mobile'],
                        'user_type' => 'API',
                        'user_id' => $agent,
                        'ref_id' => '',
                        'ack_no' => $ackNo,
                        'product_id' => (string) trim($resp->ProductCode),
                    ));
                } elseif (strtolower(trim($resp->RequestType)) == 'l') {
                   
                    $param = array(
                        'product_id' => (string) trim($resp->ProductCode),
                    );

                    $param['mobile'] = (string) trim($resp->Mobile);     
                         
                    $customerInfo = $object->getCustomerDetails($param);

                    if (empty($customerInfo)) {
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = $txnCode;
                        $responseObj->ResponseCode = self::CUSTOMER_NOT_FOUND;
                        $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                        return $responseObj;
                    }

                    $gInfo = $refObject->generateRatOTPAPI(array(
                        'product_id' => (string) trim($resp->ProductCode),
                        'customer_id' => (string) $customerInfo['customer_id'],
                        'type' => (string) trim($resp->RequestType),
                        'mobile' => $param['mobile'],
                        'user_type' => BY_CUSTOMER,
                        'user_id' => (string) $customerInfo['customer_id'],
                        'amount' => Util::convertToPaisa(trim($resp->Narration)),
                        'mode' => TXN_MODE_CR,
                    ));
                }

                $responseObj = new stdClass();
                if ($gInfo == FALSE) {
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->AckNo = $txnCode;
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
                $baseTxn = new BaseTxn();
                $txnCode = $baseTxn->generateTxncode();
                $this->_soapServer->_getLogger()->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $responseObj = new stdClass();
                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->AckNo = $txnCode;
                $responseObj->ResponseCode = self::OTP_FAILED_RESPONSE_CODE;
                $responseObj->ResponseMessage = $e->getMessage();
                return $responseObj;
            }
        } catch (Exception $e) {
            $baseTxn = new BaseTxn();
            $txnCode = $baseTxn->generateTxncode();
            $this->_soapServer->_getLogger()->__setException($e->getMessage());
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $code = $e->getCode();
            $code = (empty($code)) ? self::OTP_FAILED_RESPONSE_CODE : $code;
            $responseObj = new stdClass();
            $responseObj->SessionID = (string) $resp->SessionID;
            $responseObj->AckNo = $txnCode;
            $responseObj->ResponseCode = $code;
            $responseObj->ResponseMessage = $e->getMessage();
            return $responseObj;
        }
    }
}
