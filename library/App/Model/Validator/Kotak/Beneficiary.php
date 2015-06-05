<?php

/*
 * Validator class for remittance
 * 
 */
class Validator_Kotak_Beneficiary extends Validator_Kotak
{
    
 
    public static function nameValidation($name) { 
        if ($name != '') { 
            if (strlen($name) > 50 || !Util::aplhaValidation($name)) {
             throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_NAME_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_NAME_CODE);
            }
        } else {
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_NAME_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_NAME_CODE);
        }
    }

 
    
    public static function addressValidation($address) {
        if ($address != '') {
             if(strlen($address) > 50){
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_BENE_ADDR_LONG_MSG, ErrorCodes::ERROR_EDIGITAL_BENE_ADDR_LONG_CODE);
             } 
        } 
    }
    
    
    public static function landlineValidation($landline) {
        if ($landline != '') {
             if(strlen($landline) > 15){
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_LAND_LINE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
             } 
        } 
    }
    
    public static function cityValidation($city) {
        if ($city != '') {
             if(strlen($city) > 50){
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_CITY_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CITY_CODE);
             } 
        } 
    }
    
    public static function stateValidation($state) {
        if ($state != '') {
             if(strlen($state) > 50){
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_STATE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_STATE_CODE);
             } 
        } 
    }
    
    public static function pincodeValidation($pin) {
        if ($pin != '') {
             if(strlen($pin) != 6 || !(ctype_digit($pin))){
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
             } 
        } 
    }
    
   public static function bankDetailsValidation($bankarr) {
       
        $bankarr = array_filter($bankarr);
        if (!empty($bankarr)) { 
//            if(strlen($bankarr['bank_name']) > 50 || !Util::aplhaValidation($bankarr['bank_name'])){
//                throw new Exception('Bank Name is not valid');
//            }
//            
//            if(strlen($bankarr['branch_address']) > 50 || !Util::aplhanumericValidation($bankarr['branch_address'])){
//                throw new Exception('Bank State is not valid.');
//            }
//            
//            if(strlen($bankarr['branch_city']) > 50 || !(ctype_alpha($bankarr['branch_city']))){
//                throw new Exception('Bank City is not valid');
//            }
//           
//            if(strlen($bankarr['branch_name']) > 50 || !Util::aplhaValidation($bankarr['branch_name'])){
//                throw new Exception('Bank Branch is not valid');
//
//            }
            
            if(empty($bankarr['ifsc_code']) || strlen($bankarr['ifsc_code']) > 30 || !(ctype_alnum($bankarr['ifsc_code']))){
               throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_BANKIFSC_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_BANKIFSC_CODE);
            }
            
            //$bankarr['bank_account_number'] = intval($bankarr['bank_account_number']);
            if(empty($bankarr['bank_account_number']) || strlen($bankarr['bank_account_number']) > 20 || !(ctype_digit($bankarr['bank_account_number']))){
               throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_BANKACCOUNT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_BANKACCOUNT_CODE);
            }
            
            $bankifsc = new BanksIFSC();
            $bankDetailArr = $bankifsc->getDetailsByIFSCCode($bankarr['ifsc_code'], TXN_IMPS);
            if(empty($bankDetailArr)){
               throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_BANKIFSC_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_BANKIFSC_CODE);
            }
            
        }else{
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_BANK_DETAILS_INCOMPLETE_MSG, ErrorCodes::ERROR_EDIGITAL_BANK_DETAILS_INCOMPLETE_CODE);
        }
       
    }
    
    public static function otpValidation($otp) {
        if ($otp != '') {
             if(strlen($otp) != 6 && !(ctype_digit($otp))){
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
             } 
        } else{
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_OTP_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
        }
    }
   
    public static function chkBeneAccountExists($param) {
      $beneficiaryModel = new Remit_Kotak_Beneficiary();
      // check if bene exists with same account no.
      $beneficiaryModel->getBeneficiaryAccountNo(array('remitter_id' => $param['remitter_id'],'ifsc_code' => $param['ifsc_code'],'bank_account_number'=> $param['bank_account_number']));
                
    }
    
    public static function QueryReqNoValidation($queryreqnum){
        if($queryreqnum != ''){
            if(strlen($queryreqnum) > 12 || !ctype_digit($queryreqnum)){
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_QUERY_REQ_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
            }
        }else{
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_QUERY_REQ_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
        }
    }
    
    public static function remittanceflagValidation($remittanceflag) {
        $bene = new self();
        if ($remittanceflag != '') {
            if(strlen($remittanceflag) > 1 || !$bene->isValid($bene->_ENUM_EP,$remittanceflag)) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
            }
        }
    }
    
    public static function remitterflagValidation($remitterflag) {
        $bene = new self();
        if ($remittanceflag != '') {
            if(strlen($remittanceflag) > 1 || !$bene->isValid($bene->_ENUM_MP,$remitterflag)) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
            }
        }
    }
    
    public static function remitterCodeValidation($remittercode){
        if($remittercode != ''){
            if(strlen($remittercode) > 50 || !ctype_digit($remittercode)){
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERCODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
            }
        }else{
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERCODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
        }
    }
    
    public static function titleValidation($title){
        $bene = new self();
        if($title != ''){
            if(!$bene->isvalid($bene->_ENUM_TITLE,$title)){
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_TITLE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TITLE_CODE);
            }
        }
    }
    
    public static function genderValidation($gender){
        $bene = new self();
        if($gender != ''){
            if(!$bene->isvalid($bene->_ENUM_GENDER,$gender)){
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_GENDER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_GENDER_CODE);
            }
        }
    }
    
    public static function motherMaidenNameValidation($mothermaidenname) {
        if ($mothermaidenname != '') {
            if(strlen($mothermaidenname) > 25 || !Util::aplhaValidation($mothermaidenname)) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_MOTHER_MAIDEN_NAME_MSG, ErrorCodes::ERROR_EDIGITAL_MOTHER_MAIDEN_NAME_CODE);
            }
        }
    }
    
    public static function beneCodeValidation($benecode) {
        if ($benecode != '') {
            if(strlen($benecode) > 16 || !ctype_digit($benecode)) {
                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_BENECODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_BENECODE_CODE);
            }
        } else {
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_BENECODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_BENECODE_CODE);
        }
    }
    
    public static function emailValidation($email) {
            if($email != '' && (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$email))){
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_EMAIL_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_EMAIL_CODE);
            } 
    }
    
    public static function remittancetypeValidation($remittancetype) {
        
        if ($remittancetype != '') {
            if(strtolower($remittancetype) != strtolower(TXN_IMPS)) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTYPE_CODE);
            }
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTYPE_CODE);
        }
    }
    
    public static function remitterwalletcodeValidation($remitterwalletcode) {
        if ($remitterwalletcode != '') {
            
            if(strlen($remitterwalletcode) > 6 || !ctype_alnum($remitterwalletcode)) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTER_WALLET_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
           
            }
        }
    }
    
    public static function benewalletcodeValidation($benewalletcode) {
        if ($benewalletcode != '') {
            if(strlen($benewalletcode) > 6 || !ctype_alnum($benewalletcode)) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_BENEFICIARY_WALLET_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
            }
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_BENEFICIARY_WALLET_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
        }
    }
    
     public static function middlenameValidation($name) { 
        if ($name != '') {
            if (strlen($name) > 50 || !Util::aplhaValidation($name)) {
                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_MOTHER_MAIDEN_NAME_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
            }
        }
    }

    public static function mobile2Validation($mobile) {
        if ($mobile != '') {
            if (strlen($mobile) != 10 || !(ctype_digit($mobile))) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_MOBILE_2_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_MOBILE_2_CODE);
            }
        }
    }
    
    public static function benemobileValidation($mobile) {
        if ($mobile != '') {
            $mobile = intval($mobile);
            if (strlen($mobile) != 10 || !(ctype_digit($mobile))) {
                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_BENEMOBILE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
            }
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_BENEMOBILE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
        }
    }

      public static function beneemailValidation($email) {
        if ($email != '') {
            if($email == '' || (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$email))){
                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_BENEEMAIL_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
            } 
        } else {
                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_BENEEMAIL_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
        }
    }
    
    public static function beneBankAccountTypeValidation($accountType) {
            if(($accountType != '') && (strtolower($accountType)!= SAVING_ACCOUNT_TYPE && strtolower($accountType)!= CURRENT_ACCOUNT_TYPE)){
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER1_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
            } 
    }
}
