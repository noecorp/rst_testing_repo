<?php
/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_Package_Ratnakar_CNY extends App_ApiServer_Exchange_Package_Ratnakar
{
    const TP_ID = TP_RAT_CNY_ID;
    private $_soapServer;
    protected $_productConst;
    protected $_agentConst;
    public function __construct($server) {
        parent::__construct($server);
        $this->_soapServer = $server;
        $this->setTP(self::TP_ID);
        $this->setProductConstant(PRODUCT_CONST_RAT_CNY);
        $this->setAgentConstant(RBL_CNY_AGENT_ID);
    }
    
    
public function CardRegistrationRequest () {//Do not add comments for method summary
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);            
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml,__FUNCTION__);

            if( !isset($resp->SessionID) || !$this->isLogin($resp->SessionID)) {
                 return self::Exception($this->getMessage('login_failed'), App_ApiServer_Exchange::$INVALID_LOGIN);
             }
             
            if( !isset($resp->ProductCode) || empty($resp->ProductCode) || !$this->validateProductCode($resp->ProductCode) || !$this->validateProductCodeByConst($resp->ProductCode,PRODUCT_CONST_RAT_CNY)) {
                 return self::Exception(parent::INVALID_PRODUCT_MSG, parent::INVALID_PRODUCT_CODE);
            }

            if( !isset($resp->CardNumber) || empty($resp->CardNumber)) {
                 //return self::Exception('Invalid Card Number', App_ApiServer_Exchange::$INVALID_RESPONSE);
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

            $IdentityProofDetail = isset($resp->IdentityProofType) ? $resp->IdentityProofType : $resp->IdentityProofType;

            if( !isset($resp->IdentityProofType) || empty($resp->IdentityProofType)) {
                 //return self::Exception("Invalid Narration", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif(!$this->fieldValidator((string) $resp->IdentityProofType,self::FIELD_TYPE_STRING,'1','30')) {
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
            } elseif(!$this->fieldValidator((string) $resp->AddressLine1,self::FIELD_TYPE_STRING,'1','45')) {
                 return self::Exception($this->getMessage('address1_validation_failed'), App_ApiServer_Exchange::$INVALID_RESPONSE);                                
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

            
            if(  empty($resp->CardNumber) && empty($resp->CardPackId)) {
                 return self::Exception('Card details not found', App_ApiServer_Exchange::$INVALID_RESPONSE);
            }

            
            try {
                //Validate CRN & Card Pack ID
                $crnMaster = new CRNMaster();
                $crnParam = array();
                $crnParam['status'] = STATUS_FREE;
                $crnParam['product_id'] = (string) $resp->ProductCode;
                if(isset($resp->CardNumber) && !empty($resp->CardNumber)) {
                    $crnParam['card_number'] = (string) $resp->CardNumber;
                }
                
                if(isset($resp->CardPackId) && !empty($resp->CardPackId)) {
                    $crnParam['card_pack_id'] = (string) $resp->CardPackId;
                }
                
                if(isset($resp->MemberId) && !empty($resp->MemberId)) {
                    $crnParam['member_id'] = (string) $resp->MemberId;
                }
                
                $respCRN = $crnMaster->fetchCRNforAPI($crnParam,'');
                $respCRN = Util::toArray($respCRN);
                if(empty($respCRN)) {
                    return self::Exception('Invalid Card Details Provided', App_ApiServer_Exchange::$INVALID_RESPONSE);                    
                }
                
                $strCardNumber = (string) $resp->CardNumber;
                $cardNumber = !empty($strCardNumber) ? $strCardNumber : $respCRN['card_number'];
                $memberId = isset($resp->MemberId) && !empty($resp->MemberId) ? $resp->MemberId : $respCRN['member_id'];
                
                //$crnMaster
                $object = new CustomerTrack();
                $refObject = new Reference();
                
                $params = array();



                //$params['Sessionid'] = (string) $resp->Sessionid;
                $params['CardNumber'] = (string) $cardNumber;//$resp->CardNumber;
                $params['CardPackId'] = (string) $resp->CardPackId;
                $params['MemberId'] = (string) $memberId;//$resp->MemberId;
                $params['ProductId'] = (string) $resp->ProductCode;
                $params['Title'] = (string) $resp->Title;
                $params['FirstName'] = (string) $resp->FirstName;
                $params['MiddleName'] = (string) $resp->MiddleName;
                $params['LastName'] = (string) $resp->LastName;
                $params['NameOnCard'] = (string) $resp->NameOnCard;
                $params['Gender'] = (string) $resp->Gender;
                $params['DateOfBirth'] = (string) $resp->DateOfBirth;
                $params['Mobile'] = (string) $resp->Mobile;
                $params['Mobile2'] = (string) $resp->Mobile2;
                $params['Email'] = (string) $resp->Email;
                $params['MotherMaidenName'] = (string) $resp->MotherMaidenName;
                $params['id_proof_type'] = (string) $resp->IdentityProofType;
                $params['id_proof_number'] = (string) $resp->IdentityProofDetail;
                $params['address_proof_type'] = (string) $resp->AddressProofType;
                $params['address_proof_number'] = (string) $resp->AddressProofDetail;
                $params['Landline'] = (string) $resp->Landline;
                $params['AddressLine1'] = (string) $resp->AddressLine1;
                $params['AddressLine2'] = (string) $resp->AddressLine2;
                $params['City'] = (string) $resp->City;
                $params['State'] = (string) $resp->State;
                $params['Country'] = (string) $resp->Country;
                $params['Pincode'] = (string) $resp->Pincode;
                $params['CommAddressLine1'] = (string) $resp->CommAddressLine1;
                $params['CommAddressLine2'] = (string) $resp->CommAddressLine2;
                $params['CommCity'] = (string) $resp->CommCity;
                $params['CommState'] = (string) $resp->CommState;
                $params['CommCountry'] = (string) $resp->CommCountry;
                $params['CommPin'] = (string) $resp->CommPin;
                $params['Occupation'] = (string) $resp->Occupation;
                $params['EmployerName'] = (string) $resp->EmployerName;
                $params['corp_address_line1'] = (string) $resp->EmployerAddressLine1;
                $params['corp_address_line2'] = (string) $resp->EmployerAddressLine2;
                $params['corp_city'] = (string) $resp->EmployerCity;
                $params['corp_state'] = (string) $resp->EmployerState;
                $params['corp_country'] = (string) $resp->EmployerCountry;
                $params['corp_pin'] = (string) $resp->EmployerPin;
                $params['IsCardActivated'] = (string) $resp->IsCardActivated;
                $params['ActivationDate'] = (string) $resp->ActivationDate;
                $params['IsCardDispatch'] = (string) $resp->IsCardDispatch;
                $params['CardDispatchDate'] = (string) $resp->CardDispatchDate;
                $params['OTP'] = (string) $resp->Filler1;
                $params['CustomerType'] = (string) $resp->Filler2;
                $params['by_api_user_id'] = self::TP_ID;
                $params['customer_type'] = TYPE_NONKYC;
                $params['status_ops'] = STATUS_APPROVED;
                $params['status_ecs'] = STATUS_WAITING;
                
                //$params['Filler3'] = (string) $resp->Filler3;
                //$params['Filler4'] = (string) $resp->Filler4;
                //$params['Filler5'] = (string) $resp->Filler5;

              //  $obj = $object->getObject($params['ProductId'], $params);
                $obj = new Corp_Ratnakar_Cardholders();
                
                $respMobile = $obj->checkDuplicateMobile(array(
                    'product_id'    => $params['ProductId'],
                    'mobile'    => (string) $resp->Mobile,
                ));
                if($respMobile == TRUE) {
                    return self::Exception('Mobile Number already in use', App_ApiServer_Exchange::$INVALID_RESPONSE);                    
                }                
                
                $response = $obj->addCustomer($params);
                $txnCode = $obj->getTxncode();
                
                if($response == TRUE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->TxnNo          = (string) $resp->TxnNo;
                    $responseObj->AckNo          = $txnCode;//$baseTxn->getTxncode();
                    $responseObj->ResponseCode   = self::CUSTOMER_REGISTRATION_SUCC_CODE;
                    $responseObj->ResponseMessage= self::CUSTOMER_REGISTRATION_SUCC_MSG;
                } else {
                    $errorMsg = $obj->getError();
                    $errorMsg = empty($errorMsg) ? self::CUSTOMER_REGISTRATION_FAIL_MSG : $errorMsg;
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->ResponseCode   = self::CUSTOMER_REGISTRATION_FAIL_CODE;
                    $responseObj->ResponseMessage= $errorMsg;
               }
                return $responseObj;               
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $this->_soapServer->_getLogger()->__setException($e->getMessage());               
                return self::Exception($e->getMessage(), ErrorCodes::ERROR_SYSTEM_ERROR); 
            }
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $this->_soapServer->_getLogger()->__setException($e->getMessage());            
            return self::Exception($e->getMessage(), self::CUSTOMER_REGISTRATION_FAIL_CODE);
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
            
            if( !isset($resp->ProductCode) || empty($resp->ProductCode) || !$this->validateProductCode($resp->ProductCode) || !$this->validateProductCodeByConst($resp->ProductCode,PRODUCT_CONST_RAT_CNY)) {
                 return self::Exception(parent::INVALID_PRODUCT_MSG, parent::INVALID_PRODUCT_CODE);
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
            
            // Handling WalletCode
            if( !isset($resp->WalletCode) || empty($resp->WalletCode)) {
                 return self::Exception("Invalid Wallet Code", App_ApiServer_Exchange::$INVALID_RESPONSE);
            } elseif($this->isWalletAllowed(BANK_RATNAKAR_CNERGYIS, $resp->WalletCode)) {
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
                );
                $response = $baseTxn->doCardload($param);
                $errorMsg = $baseTxn->getError();
               
                if($response['status'] == STATUS_LOADED) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID      = (string) $resp->SessionID;
                    $responseObj->TxnNo          = (string) $resp->TxnNo;
                    $responseObj->AckNo          = $baseTxn->getTxncode();
                    $responseObj->ResponseCode   = self::LOAD_SUCCSSES_RESPONSE_CODE;
                    $responseObj->ResponseMessage= $this->getLoadSuccMsg($resp->TxnIndicator); //self::LOAD_SUCCSSES_RESPONSE_MSG;
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
    
}