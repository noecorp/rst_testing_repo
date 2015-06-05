<?php
/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_Services_Ecomm extends App_ApiServer_Exchange
{
    private $_soapServer;
    const TP_ID = TP_ECOM_ID;
    const LOAD_FAILED_RESPONSE_CODE = '110';
    const LOAD_FAILED_RESPONSE_MSG  = 'Unable to register Load Request';
    
    const LOAD_SUCCSSES_RESPONSE_CODE = '0000';
    const LOAD_SUCCSSES_RESPONSE_MSG = 'Successfully Registered Load Request';
    
    const OTP_SUCCSSES_RESPONSE_CODE = '0';
    const OTP_SUCCSSES_RESPONSE_MSG = 'OTP Sent Successfully';

    const OTP_FAILED_RESPONSE_CODE = '115';
    const OTP_FAILED_RESPONSE_MSG = 'Unable to process OTP request';    
    
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
    const TXN_IDENTIFIER_TYPE_MOB = 'MOB';    
    const TXN_IDENTIFIER_TYPE_MID = 'MID';    
    const REQUEST_TYPE_LOAD = 'L';
    const REQUEST_TYPE_REGISTRATION = 'R';

    
    const CUSTOMER_REGISTRATION_SUCC_CODE = '0';
    const CUSTOMER_REGISTRATION_SUCC_MSG = 'Customer Registered Successfully';
    
    const CUSTOMER_REGISTRATION_FAIL_CODE = '0';
    const CUSTOMER_REGISTRATION_FAIL_MSG = 'Unable to register Customer';
    
    const LOAD_SUCC_CODE = '0';
    const LOAD_SUCC_MSG = 'Successfully Registered Load Request';
    
    //const TXN_IDENTIFIER_TYPE_MID = 'MID';
    
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

   
    
    public function GenerateOTPRequest () {//Do not add comments for method summary

        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);            
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml,__FUNCTION__);
            if( !isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                 return self::Exception($this->getMessage('login_failed'), App_ApiServer_Exchange::$INVALID_LOGIN);
             }
             
            if( !isset($resp->ProductCode) || empty($resp->ProductCode) || !$this->validateProductCode($resp->ProductCode)) {
                 //return self::Exception(parent::INVALID_PRODUCT_CODE, parent::INVALID_PRODUCT_MSG);
            }

            if( !isset($resp->Mobile) || empty($resp->Mobile)) {
                 return self::Exception($this->getMessage('invaild_mobile'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            // Handling Amount
            if( !isset($resp->RequestType) || empty($resp->RequestType) || strtolower($resp->RequestType) != 'l') {
                 return self::Exception($this->getMessage('invalid_req_type'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            // Currency
            if( !isset($resp->CardNumber) || empty($resp->CardNumber)) {
                 return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if( !isset($resp->IsOriginal) || empty($resp->IsOriginal) || $this->isValid($this->_ENUM_YN,$resp->IsOriginal)) {
                 return self::Exception("Invalid IsOriginal", App_ApiServer_Exchange::$INVALID_RESPONSE);
            }
            
            if(strtolower($resp->IsOriginal) == 'n') {
                if( !isset($resp->OriginalAckNo) || empty($resp->OriginalAckNo)) {
                     return self::Exception("Invalid Acknolegement Number", App_ApiServer_Exchange::$INVALID_RESPONSE);
                }
            }
            
            
            try {
                

                 $object = new CustomerTrack();
                $refObject = new Reference();
                if (strtolower($resp->RequestType) == 'r') {
                   $ackNo=''; 
                if(strtolower($resp->IsOriginal) == 'n') {
                    if( isset($resp->OriginalAckNo) && !empty($resp->OriginalAckNo)) {
                          $ackNo = $resp->OriginalAckNo;
                    }
                }                    
                   
                    $gInfo = $refObject->generateOTP(array(
                        'type' => (string) $resp->RequestType,
                        'mobile' => (string) $resp->Mobile,
                        'type' => (string) $resp->RequestType,
                        'user_type' => 'API',
                        'user_id' => ECOMM_AGENT_ID,
                        'ref_id' => '',
                        'ack_no' => $ackNo,
                        'length' => 4
                    ));
                } else {

//                    $param = array(
//                        'mobile' => (string) $resp->Mobile,
//                        'product_id' => (string) $resp->ProductCode,
//                    );
//                    $customerInfo = $object->getCustomerDetails($param);
                    
                    $param = array(
                        'mobile' => (string) $resp->Mobile,
                        'card_number' => (string) $resp->CardNumber,
                    );
                    $customerInfo = $object->getRatnakarCustomerDetails($param);

                    if (empty($customerInfo)) {
                        $responseObj = new stdClass();         
                        $responseObj->SessionID = (string) $resp->SessionID;
                        $responseObj->AckNo = '';
                        $responseObj->ResponseCode = self::CUSTOMER_NOT_FOUND;
                        $responseObj->ResponseMessage = self::CUSTOMER_NOT_FOUND_MSG;
                        return $responseObj;
                    }

                    $gInfo = $refObject->generateLoadOTP(array(
                        'product_id' => (string) $customerInfo['product_id'],
                        'customer_id' => (string) $customerInfo['customer_id'],
                        'type' => (string) $resp->RequestType,
                        'user_type' => BY_CUSTOMER,
                        'user_id' => (string) $customerInfo['customer_id'],
                        'amount' => $resp->Narration,
                        'length' => 4,
                        'request_from' => TYPE_REQUEST_ECOM,
                    ));
                 
                }
                
                $responseObj = new stdClass();                  
                if($gInfo == FALSE) {
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->AckNo          = '';
                    $responseObj->ResponseCode   = self::OTP_FAILED_RESPONSE_CODE;
                    $responseObj->ResponseMessage= self::OTP_FAILED_RESPONSE_MSG;
                } else {
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->AckNo          = $gInfo;
                    $responseObj->ResponseCode   = self::OTP_SUCCSSES_RESPONSE_CODE;
                    $responseObj->ResponseMessage= self::OTP_SUCCSSES_RESPONSE_MSG;                    
                }
                return $responseObj;          
            } catch (App_Exception $e) {
               $this->_soapServer->_getLogger()->__setException($e->getMessage());                       
               App_Logger::log(serialize($e), Zend_Log::ERR);
               $responseObj = new stdClass();                  
               $responseObj->SessionID      = (string) $resp->SessionID;
               $responseObj->AckNo          = '';
               $responseObj->ResponseCode   = self::OTP_FAILED_RESPONSE_CODE;
               $responseObj->ResponseMessage= $e->getMessage();
               return $responseObj;
            }
        } catch (Exception $e) {
               $this->_soapServer->_getLogger()->__setException($e->getMessage());                   
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $responseObj = new stdClass();                  
            $responseObj->SessionID      = (string) $resp->SessionID;
            $responseObj->AckNo          = '';
            $responseObj->ResponseCode   = self::OTP_FAILED_RESPONSE_CODE;
            $responseObj->ResponseMessage= $e->getMessage();
            return $responseObj;            
        }
    }    
    
    
    public function CardRegistrationRequest () {//Do not add comments for method summary
        try {
            
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml,__FUNCTION__);

            if( !isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                 return self::Exception($this->getMessage('login_failed'), App_ApiServer_Exchange::$INVALID_LOGIN);
             }
             
            if( !isset($resp->ProductCode) || empty($resp->ProductCode) || !$this->validateProductCode($resp->ProductCode)) {
                 return self::Exception(parent::INVALID_PRODUCT_MSG, parent::INVALID_PRODUCT_CODE);
            }

            if( !isset($resp->CardNumber) || empty($resp->CardNumber)) {
                 //return self::Exception($this->getMessage('invaild_mobile'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator($resp->CardNumber, self::FIELD_TYPE_CARDNUMBER)) {
                 return self::Exception($this->getMessage('card_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                
            }

            if( !isset($resp->CardPackId) || empty($resp->CardPackId)) {
                 //return self::Exception($this->getMessage('invaild_mobile'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator($resp->CardPackId, self::FIELD_TYPE_CARDPACKID)) {
                 return self::Exception($this->getMessage('card_pack_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                
            }

            if( !isset($resp->MemberId) || empty($resp->MemberId)) {
                 //return self::Exception($this->getMessage('invaild_mobile'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }elseif(!$this->fieldValidator($resp->MemberId, self::FIELD_TYPE_MEMBERID,'1','15')) {
                 return self::Exception($this->getMessage('member_id_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            
            if( !isset($resp->Title) || empty($resp->Title)) {
                 return self::Exception($this->getMessage('title_invalid'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator($resp->Title, self::FIELD_TYPE_TITLE)) {
                 return self::Exception($this->getMessage('title_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }

            if( !isset($resp->FirstName) || empty($resp->FirstName)) {
                 return self::Exception($this->getMessage('first_name_invalid'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->FirstName, self::FIELD_TYPE_STRING, 1, 50)) {
                 return self::Exception($this->getMessage('first_name_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }


            if( !isset($resp->MiddleName) || empty($resp->MiddleName)) {
                 //return self::Exception($this->getMessage('invaild_mobile'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->MiddleName, self::FIELD_TYPE_STRING, '1', '50')) {
                 return self::Exception($this->getMessage('middlename_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }

            if( !isset($resp->LastName) || empty($resp->LastName)) {
                 return self::Exception($this->getMessage('lastname_invalid'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->LastName,  self::FIELD_TYPE_STRING,'1', '50')) {
                 return self::Exception($this->getMessage('lastname_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }


            if( !isset($resp->NameOnCard) || empty($resp->NameOnCard)) {
                 //return self::Exception($this->getMessage('invaild_mobile'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }  elseif(!$this->fieldValidator((string) $resp->NameOnCard, self::FIELD_TYPE_STRING,'0', '50')) {
                 return self::Exception($this->getMessage('nameoncard_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }

            
            if( !isset($resp->Gender) || empty($resp->Gender)) {
                 return self::Exception($this->getMessage('gender_invalid'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }  elseif(!$this->fieldValidator($resp->Gender,self::FIELD_TYPE_GENDER)) {
                 return self::Exception($this->getMessage('gender_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            
            if( !isset($resp->DateOfBirth) || empty($resp->DateOfBirth)) {
                 return self::Exception($this->getMessage('dob_invalid'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }elseif(!$this->fieldValidator($resp->DateOfBirth,self::FIELD_TYPE_DOB)) {
                 return self::Exception($this->getMessage('dob_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }

            if( !isset($resp->Mobile) || empty($resp->Mobile)) {
                 return self::Exception($this->getMessage('mobile_invalid'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->Mobile,self::FIELD_TYPE_MOBILE)) {
                 return self::Excetion($this->getMessage('mobile_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            

            if( !isset($resp->Mobile2) || empty($resp->Mobile2)) {
//                 return self::Exception($this->getMessage('invaild_mobile'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            }elseif(!$this->fieldValidator((string) $resp->Mobile2,self::FIELD_TYPE_MOBILE)) {
                 return self::Exception($this->getMessage('mobile2_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            
            if( !isset($resp->Email) || empty($resp->Email)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->Email,self::FIELD_TYPE_EMAIL,'1','50')) {
                 return self::Exception($this->getMessage('email_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            
            if( !isset($resp->MotherMaidenName) || empty($resp->MotherMaidenName)) {
                 return self::Exception($this->getMessage('mothermaidenname_invalid'), App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->MotherMaidenName,self::FIELD_TYPE_STRING,'1','25')) {
                 return self::Exception($this->getMessage('mothermaidenname_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }

            $IdentityProofDetail = isset($resp->IdentityTroofType) ? $resp->IdentityTroofType : $resp->IdentityProofType;

            if( !isset($resp->IdentityTroofType) || empty($resp->IdentityTroofType)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->IdentityTroofType,self::FIELD_TYPE_STRING,'1','30')) {
                 return self::Exception($this->getMessage('id_proof_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            
            if( !isset($resp->IdentityProofDetail) || empty($resp->IdentityProofDetail)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->IdentityProofDetail,self::FIELD_TYPE_STRING,'1','50')) {
                 return self::Exception($this->getMessage('id_proof_value_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            
            if( !isset($resp->AddressProofType) || empty($resp->AddressProofType)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->AddressProofType,self::FIELD_TYPE_STRING,'1','50')) {
                 return self::Exception($this->getMessage('add_proof_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            
            if( !isset($resp->AddressProofDetail) || empty($resp->AddressProofDetail)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->AddressProofDetail,self::FIELD_TYPE_STRING,'1','50')) {
                //echo $resp->AddressProofDetail;exit;
                 //return self::Exception($this->getMessage('add_proof_value_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }


            if( !isset($resp->Landline) || empty($resp->Landline)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator( (string) $resp->Landline,self::FIELD_TYPE_NUMBER,'1','15')) {
                 ////////return self::Exception($this->getMessage('landline_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            
            if( !isset($resp->AddressLine1) || empty($resp->AddressLine1)) {
                 return self::Exception("Address Line 1 missing", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->AddressLine1,self::FIELD_TYPE_STRING,'1','50')) {
                 //return self::Exception($this->getMessage('address1_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            
            if( !isset($resp->AddressLine2) || empty($resp->AddressLine2)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->AddressLine2,self::FIELD_TYPE_STRING,'1','50')) {
                 //return self::Exception($this->getMessage('address2_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            
            if( !isset($resp->City) || empty($resp->City)) {
                 return self::Exception("Invalid City Provided", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->City,self::FIELD_TYPE_STRING,'1','50')) {
                 return self::Exception($this->getMessage('city_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            
            if( !isset($resp->State) || empty($resp->State)) {
                 return self::Exception("State Missing", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->State,self::FIELD_TYPE_STRING,'1','50')) {
                 return self::Exception($this->getMessage('state_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            


            if( !isset($resp->Country) || empty($resp->Country)) {
                 return self::Exception("Country Missing", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->Country,self::FIELD_TYPE_COUNTRY)) {
                 return self::Exception('Invalid Country Provided', App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }



            if( !isset($resp->Pincode) || empty($resp->Pincode)) {
                 return self::Exception("Pincode Missing", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->Pincode,self::FIELD_TYPE_NUMBER,'1','6')) {
                 //return self::Exception($this->getMessage('pincode_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
          

            if( !isset($resp->CommAddressLine1) || empty($resp->CommAddressLine1)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->CommAddressLine1,self::FIELD_TYPE_STRING,'1','50')) {
                 //return self::Exception("Invalid Communcation Address Line1", App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            


            if( !isset($resp->CommAddressLine2) || empty($resp->CommAddressLine2)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator($resp->CommAddressLine2,self::FIELD_TYPE_STRING,'1','50')) {
                 //return self::Exception("Invalid Communcation Address Line2", App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }

            
            
            if( !isset($resp->CommCity) || empty($resp->CommCity)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->City,self::FIELD_TYPE_STRING,'1','50')) {
                 return self::Exception("Communication City Validation Failed", App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            
            if( !isset($resp->CommState) || empty($resp->CommState)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->CommState,self::FIELD_TYPE_STRING,'1','50')) {
                 return self::Exception("Communication State Validation Failed", App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }



            if( !isset($resp->CommCountry) || empty($resp->CommCountry)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->CommCountry,self::FIELD_TYPE_COUNTRY)) {
                 return self::Exception($this->getMessage('comm_country_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            
            if( !isset($resp->CommPin) || empty($resp->CommPin)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->CommPin,self::FIELD_TYPE_NUMBER,'1','6')) {
                 //return self::Exception($this->getMessage('comm_pincode_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
           
            
            if( !isset($resp->Occupation) || empty($resp->Occupation)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->Occupation,self::FIELD_TYPE_STRING,'1','50')) {
                 return self::Exception("Occupation Validation Failed", App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            
            if( !isset($resp->EmployerName) || empty($resp->EmployerName)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->EmployerName,self::FIELD_TYPE_STRING,'1','50')) {
                 return self::Exception("Employer Name Validation Failed", App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            
            if( !isset($resp->EmployerAddressLine1) || empty($resp->EmployerAddressLine1)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator($resp->EmployerAddressLine1,self::FIELD_TYPE_STRING,'1','50')) {
                 //return self::Exception("Employer Address Line 1 Failed", App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            
            if( !isset($resp->EmployerAddressLine2) || empty($resp->EmployerAddressLine2)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator($resp->EmployerAddressLine2,self::FIELD_TYPE_STRING,'1','50')) {
                 //return self::Exception("Employer Address Line 2 Validation Failed", App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            
            if( !isset($resp->EmployerCity) || empty($resp->EmployerCity)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->EmployerCity,self::FIELD_TYPE_STRING,'1','50')) {
                 return self::Exception("Employer City Validation Failed", App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            
            if( !isset($resp->EmployerState) || empty($resp->EmployerState)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->EmployerState,self::FIELD_TYPE_STRING,'1','50')) {
                 return self::Exception("Employer State Validation Failed", App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            
            if( !isset($resp->EmployerCountry) || empty($resp->EmployerCountry)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->EmployerCountry,self::FIELD_TYPE_STRING,'1','5')) {
                 return self::Exception("Employer Country Validation Failed", App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            
            if( !isset($resp->EmployerPin) || empty($resp->EmployerPin)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator($resp->EmployerPin,self::FIELD_TYPE_NUMBER,'1','6')) {
                 //return self::Exception("Employer Pincode Validation Failed", App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }


            if( !isset($resp->IsCardActivated) || empty($resp->IsCardActivated)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator($resp->IsCardActivated,self::FIELD_TYPE_STRING,'1','1')) {
                 return self::Exception("Is Card Activated Validation Failed", App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            if( !isset($resp->ActivationDate) || empty($resp->ActivationDate)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator($resp->ActivationDate,self::FIELD_TYPE_DATETIME)) {
                 return self::Exception("Activation Date Validation Failed", App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            if( !isset($resp->IsCardDispatch) || empty($resp->IsCardDispatch)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator($resp->IsCardDispatch,self::FIELD_TYPE_STRING,'1','1')) {
                 return self::Exception("Is Card Dispatch Validation Failed", App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }
            if( !isset($resp->CardDispatchDate) || empty($resp->CardDispatchDate)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator($resp->CardDispatchDate,self::FIELD_TYPE_DATETIME)) {
                 return self::Exception("Card Dispatch Date Validation Failed", App_ApiServer_Exchange::$INVALID_RESPONSE);                                
            }

            //return self::Exception($this->getMessage('login_failed'), App_ApiServer_Exchange::$INVALID_LOGIN);   
            
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
                
                $param = array(
                    'mobile'        => (string) $resp->MemberIDCardNo,
                    'product_id'    => (string) $resp->ProductCode,
                );
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
                $walletCode = empty($resp->WalletCode) ? '':(string) $resp->WalletCode;
                
               $walletCodeResponse = $object->verifyWalletCode($productId, $walletCode);
              
                if($walletCodeResponse == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->AckNo          = ' ';
                    $responseObj->ResponseCode   = App_ApiServer_Exchange::$INVALID_RESPONSE;
                    $responseObj->ResponseMessage= 'Invalid Wallet Code';                    
                    return $responseObj;                                 
                }                                       
               
                $otpVeriParam = array(
                   'product_id' => $productId,
                   'request_type'=> self::OTP_TYPE_LOAD,
                   'customer_id'=>$customerInfo['customer_id'],
                   'otp'=>(string) $resp->OTP,
                   'amount'=>(string) $resp->Amount
               );
               
               $otpResponse = $refObject->verifyCustomerLoadOTP($otpVeriParam);
                 if($otpResponse == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->AckNo          = ' ';
                    $responseObj->ResponseCode   = self::INVALID_OTP_CODE;
                    $responseObj->ResponseMessage= self::INVALID_OTP_MSG;                    
                    return $responseObj;                                 
                }                        
               
                $baseTxn = new Corp_Ratnakar_Cardload();
                $param = array(
                    //'agent_id'  => MEDIASSIST_AGENT_ID,
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
                    'by_api_user_id'        => (string) PAYTRONIC_AGENT_ID,
		    'channel'               => CHANNEL_API,
                );
                $response = $baseTxn->doCardload($param);
                $errorMsg = $baseTxn->getError();
                //$responseObj = new stdClass();              
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
                    $responseObj->ResponseMessage= empty($errorMsg) ? $this->getLoadFailedMsg($resp->TxnIndicator) : $errorMsg;
               }
                return $responseObj;                   
            } catch (App_Exception $e) {
               $this->_soapServer->_getLogger()->__setException($e->getMessage());                       
               App_Logger::log(serialize($e), Zend_Log::ERR);
               return self::Exception($e->getMessage(), ErrorCodes::ERROR_SYSTEM_ERROR); 
            }
        } catch (Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());                   
            App_Logger::log(serialize($e), Zend_Log::ERR);
             return self::Exception($e->getMessage(), self::LOAD_FAILED_RESPONSE_CODE);
        }
    }    
    
    
    
    public function CardTransactionRequest ($obj) {//Do not add comments for method summary
        //return self::Exception($this->getMessage('login_failed'), App_ApiServer_Exchange::$INVALID_LOGIN);
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
            } elseif(!in_array($resp->TxnIdentifierType,array(self::TXN_IDENTIFIER_TYPE_MOB,self::TXN_IDENTIFIER_TYPE_CRN))) {
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
            
            if( !isset($resp->OTP) || empty($resp->OTP)) {
                 return self::Exception("Invalid OTP Provided", App_ApiServer_Exchange::$INVALID_RESPONSE);
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
                    $txnIdentifierType = 'MB';                    
                } else {
                    $txnIdentifierType = $resp->TxnIdentifierType;                                        
                }
                           
             
                $object = new CustomerTrack();
                $refObject = new Reference();

                $params = array(
                    'mobile'        => (string) $resp->MemberIDCardNo,
                    'product_id'    => (string) $resp->ProductCode,
                );
                $customerInfo = $object->getCustomerDetails($params);
                
                if(empty($customerInfo)) {
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->AckNo          = '';
                    $responseObj->ResponseCode   = self::CUSTOMER_NOT_FOUND;
                    $responseObj->ResponseMessage= self::CUSTOMER_NOT_FOUND_MSG;                    
                    return $responseObj;                                 
                }
                
                $otpVeriParam = array(
                  'product_id'      => (string) $resp->ProductCode,
                  'customer_id'     =>  $customerInfo['customer_id'],
                  'otp'             =>  (string) $resp->OTP,
                  'request_type'    =>  'L'
                );
                
                 $otpResponse = $refObject->verifyCustomerOTP($otpVeriParam);
               
                 if($otpResponse == FALSE) {
                     $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->AckNo          = ' ';
                    $responseObj->ResponseCode   = self::INVALID_OTP_CODE;
                    $responseObj->ResponseMessage= self::INVALID_OTP_MSG;                    
                    return $responseObj;                                 
                }                        
               
              
                
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->TxnNo          = (string) $resp->TxnNo;
                    $responseObj->AckNo          = ' ';
                    $responseObj->ResponseCode   = self::LOAD_SUCCSSES_RESPONSE_CODE;
                    $responseObj->ResponseMessage= self::LOAD_SUCCSSES_RESPONSE_MSG;
                    return $responseObj;       
               
                
                $param = array(
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
