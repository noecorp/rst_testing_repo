<?php

/*
 * Validator class for remittance
 * 
 */
class Validator_Kotak extends Validator
{
   
    const TXN_IDENTIFIER_TYPE_EMAIL = 'EML';    
    const TXN_IDENTIFIER_TYPE_PARTNER = 'PAR';
    
    const CUST_IDENTIFIER_TYPE_EMAIL = 'E';    
    const CUST_IDENTIFIER_TYPE_PARTNER = 'P';
    
    public $_ENUM_YN = 'ENUM_YN';
    public $_ENUM_YN_ARRAY = array('y','n');

    public $_ENUM_TYPE = 'ENUM_TYPE';
    public $_ENUM_TYPE_ARRAY = array('r','i','t','b','e','n','f');
    
    public $_ENUM_YN_BOOL = 'ENUM_YN_BOOL';
    public $_ENUM_YN_BOOL_ARRAY = array(0,1);
    
    public $_ENUM_EP = 'ENUM_EP';
    public $_ENUM_EP_ARRAY = array('e','p');
    
    public $_ENUM_MP = 'ENUM_MP';
    public $_ENUM_MP_ARRAY = array('m','p');
    
    public $_ENUM_GENDER = 'ENUM_GENDER';
    public $_ENUM_GENDER_ARRAY = array('male','female');
    
    public $_ENUM_TITLE = 'ENUM_TITLE';
    public $_ENUM_TITLE_ARRAY = array('mr.', 'mrs.', 'miss.');
    
    public $_ENUM_CN = 'ENUM_CN';
    public $_ENUM_CN_ARRAY = array('c','n');
    
    public $_ENUM_DC = 'ENUM_DC';
    public $_ENUM_DC_ARRAY = array('dr','cr'); 
    
    public $_ENUM_STATUS = 'ENUM_STATUS';
    public $_ENUM_STATUS_ARRAY = array(STATUS_PENDING, STATUS_SUCCESS, STATUS_FAILURE);  

    public static function productcodeValidation($productcode,$prod_const) {
        if ($productcode != '') {
            if(strlen($productcode) > 11 || !self::validateProductCode($productcode) || !self::validateProductCodeByConst($productcode,$prod_const)) {
                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);
            }
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);
        }
    }
    
    public static function validateProductCode($productCode) {
        $productModel = new Products();
        $rs = $productModel->isActiveProduct($productCode);
        if(!empty($rs)) {
            return TRUE;
        }
        return FALSE;
    }
    
    public static function validateProductCodeByConst($productCode,$const) {
        $productModel = new Products();
        $rs = $productModel->isActiveProductByConst($productCode,$const);
        if(!empty($rs)) {
            return TRUE;
        }
        return FALSE;
    }
    
    public static function mobileValidation($mobile) {
        if ($mobile != '') {
            $mobile = intval($mobile);
            if (strlen($mobile) != 10 || !(ctype_digit($mobile))) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_MOB_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_MOB_CODE);
            }
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_MOB_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_MOB_CODE);
        }
    }
    
    public static function txnrefnoValidation($txnrefno) {
        if ($txnrefno != '') {
            if(strlen($txnrefno) > 16 || !(ctype_digit($txnrefno))) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_REF_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_REF_CODE);
            } 
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_REF_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_REF_CODE);
        }
    }
    
    public static function dateValidation($date) { 
        if($date != ''){ 
            $date_regex = '/^(19|20)\d\d[\-\/.](0[1-9]|1[012])[\-\/.](0[1-9]|[12][0-9]|3[01])$/';
            if (!preg_match($date_regex, $date)) {
                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_DOB_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_DOB_CODE);
            }
        }
    }
    
    public static function emailValidation($email) {
        if ($email != '') {
            if($email == '' || (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$email))){
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_EMAIL_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_EMAIL_CODE);
            } 
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_EMAIL_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_EMAIL_CODE);
        }
    }
    
    public static function queryrefnoValidation($queryrefno) {
        if ($queryrefno != '') {
            //$txnrefno = intval($txnrefno);
            if(strlen($queryrefno) > 12 || !(ctype_digit($queryrefno))) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_QUERY_REQ_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_QUERY_REQ_NO_CODE);
            } 
            
        } else {
               throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_QUERY_REQ_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_QUERY_REQ_NO_CODE);
        }
       
    }
    
    public static function pincodeValidation($pincode) {
        if ($pincode != '') {
            if (strlen($pincode) != 6 || !(ctype_digit($pincode))) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PIN_CODE);
            }
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PIN_CODE);
        }
    }
    
    public function isValid($type, $value) {
        $flg = FALSE;
        switch ($type)
        {
            case $this->_ENUM_TYPE :
                $type = strtolower($type);
                $value = strtolower($value);
                $flg = in_array($value, $this->_ENUM_TYPE_ARRAY);
                break;
            
            case $this->_ENUM_YN :
                $type = strtolower($type);
                $value = strtolower($value);
                $flg = in_array($value, $this->_ENUM_YN_ARRAY);
                break;

            case $this->_ENUM_YN_BOOL :
                //$type = strtolower($type);
                //$value = strtolower($value);
                $flg = in_array($value, $this->_ENUM_YN_BOOL_ARRAY);
                break;
            
            case $this->_ENUM_EP :
                $type = strtolower($type);
                $value = strtolower($value);
                $flg = in_array($value, $this->_ENUM_EP_ARRAY);
                break; 
            
            case $this->_ENUM_MP :
                $type = strtolower($type);
                $value = strtolower($value);
                $flg = in_array($value, $this->_ENUM_MP_ARRAY);
                break; 
            
            case $this->_ENUM_GENDER :
                $type = strtolower($type);
                $value = strtolower($value);
                $flg = in_array($value, $this->_ENUM_GENDER_ARRAY);
                break; 
            
            case $this->_ENUM_TITLE :
                $type = strtolower($type);
                $value = strtolower($value);
                $flg = in_array($value, $this->_ENUM_TITLE_ARRAY);
                break; 
            
            case $this->_ENUM_CN :
                $type = strtolower($type);
                $value = strtolower($value);
                $flg = in_array($value, $this->_ENUM_CN_ARRAY);
                break;
            
            case $this->_ENUM_DC :
                $type = strtolower($type);
                $value = strtolower($value);
                $flg = in_array($value, $this->_ENUM_DC_ARRAY); 
                break;
            
            case $this->_ENUM_STATUS :
                $type = strtolower($type);
                $value = strtolower($value);
                $flg = in_array($value, $this->_ENUM_STATUS_ARRAY);
                break;

            
        }
        return $flg;
    }
   
    public static function PartnerCodeValidation($partnercode) {
        if ($partnercode != '') {
            if (strlen($partnercode) > 12 || !(ctype_digit($partnercode))) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PARTNER_CODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARTNER_CODE);
            }
        } else {
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PARTNER_CODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARTNER_CODE);
        }
    }
    
    public static function addressLineValidation($address){
        if ($address == '') {
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_ADDRESSLINE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_ADDRESSLINE_CODE);
        }else{
             if(strlen($address) > 50) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_ADDR_LINE_1_LONG_MSG, ErrorCodes::ERROR_EDIGITAL_ADDR_LINE_1_LONG_CODE);
            }
        }
    }
    
    public static function addressLine2Validation($address){
        if( ($address != '') && (strlen($address) > 50) ) {
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_ADDRESSLINE2_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_ADDRESSLINE2_CODE);
        }
    }
    
    public static function orgtxnrefnoValidation($orgtxnrefno) {
        if ($orgtxnrefno != '') {
            if(strlen($orgtxnrefno) > 16 || !(ctype_digit($orgtxnrefno))) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_ORG_TXN_REF_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_ORG_TXN_REF_CODE);
            } 
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_QUERY_REFUND_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_QUERY_REFUND_CODE);
        }
    }
    
    public static function originaltxnrefnoValidation($orgtxnrefno) {
        if ($orgtxnrefno != '') {
            if(strlen($orgtxnrefno) > 16 || !(ctype_digit($orgtxnrefno))) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_ORG_TXN_REF_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_ORG_TXN_REF_CODE);
            } 
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_ORG_TXN_REF_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_ORG_TXN_REF_CODE);
        }
    }
}
