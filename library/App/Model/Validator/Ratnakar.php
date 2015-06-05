<?php

/*
 * Validator class for remittance
 * 
 */
class Validator_Ratnakar extends Validator
{
   
    const TXN_IDENTIFIER_TYPE_EMAIL = 'EML';    
    const TXN_IDENTIFIER_TYPE_PARTNER = 'PAR'; 
    const TXN_IDENTIFIER_TYPE_MOBILE = 'MOB';
    
    const CUST_IDENTIFIER_TYPE_EMAIL = 'E';    
    const CUST_IDENTIFIER_TYPE_PARTNER = 'P'; 
    const CUST_IDENTIFIER_TYPE_MOBILE = 'M'; 
    
    public $_ENUM_YN = 'ENUM_YN';
    public $_ENUM_YN_ARRAY = array('y','n');

    public $_ENUM_TYPE = 'ENUM_TYPE';
    public $_ENUM_TYPE_ARRAY = array('r','i','t','b','e','n','l','u');
    
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


    public static function mobileValidation($mobile) {
        if ($mobile != '') {
          //  $mobile = intval($mobile);
            if (strlen($mobile) != 10 || !(ctype_digit($mobile))) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_MOB_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_MOB_CODE);
            }else if(substr($mobile, 0, 1) == "0") {
             throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_MOB_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_MOB_CODE);   
            }
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_MOB_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_MOB_CODE);
        }
    }
    
    public static function customerMOBValidation($mobile) {
        if ($mobile != '') {
         //   $mobile = intval($mobile);
            if (strlen($mobile) != 10 || !(ctype_digit($mobile))) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE);
            }else if(substr($mobile, 0, 1) == "0") {
             throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE);   
            }
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE);
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
    
    
     public static function productcodeValidation($productcode,$prod_const) {
        if ($productcode != '') {
            if(strlen($productcode) > 11 || !(ctype_digit($productcode)) || !self::validateProductCode($productcode) || !self::validateProductCodeByConst($productcode,$prod_const)) {
                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);
		throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);
            }
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);
        }
    }
    public static function validateProductCode($productCode) {
        $productModel = new Products();
        $rs = $productModel->isActiveProduct($productCode);
        //echo '<pre>';print_r($rs->toArray());exit;
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
    
    public static function smsflagValidation($smsflag) {
        $rat = new self();
        if ($smsflag != '') {
            if(strlen($smsflag) > 1 || !$rat->isValid($rat->_ENUM_YN,$smsflag)) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_CODE);
            }
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_CODE);
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
    
    public static function txnrefnoValidation($txnrefno) {
        if ($txnrefno != '') {
            //$txnrefno = intval($txnrefno);
            if(strlen($txnrefno) > 16 || !(ctype_digit($txnrefno)) || $txnrefno < 1) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_REF_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_REF_CODE);
            } 
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_REF_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_REF_CODE);
        }
    }
    
    public static function txnidentifiertypeValidation($txnidtype) {
            $txnidtype = strtolower($txnidtype);
            $_TXN_ID_TYPE_PAR = strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER);
            $_TXN_ID_TYPE_EML = strtolower(self::TXN_IDENTIFIER_TYPE_EMAIL);
        
            if ($txnidtype != '') {
                if(strlen($txnidtype) > 3 || !(ctype_alpha($txnidtype))) {
                    throw new Exception('Invalid TxnIdentifierType');
                } elseif(($txnidtype != $_TXN_ID_TYPE_PAR) && ($txnidtype != $_TXN_ID_TYPE_EML)) {
                    throw new Exception('Invalid TxnIdentifierType');
                }
            }else{
                throw new Exception('Transaction Identifier Type is mandatory');
            }
    }
    
    public static function txnindicatorValidation($txnindicator) {
        $rat = new self();
        if ($txnindicator != '') {
            if(strlen($txnindicator) > 2 || !$rat->isValid($rat->_ENUM_DC,$txnindicator)) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_TXNINDICATOR_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TXNINDICATOR_CODE);
            }
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_TXNINDICATOR_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TXNINDICATOR_CODE);
        }
    }
    
    public static function cardtypeValidation($cardtype) {
        $rat = new self();
        if ($cardtype != '') {
            if(strlen($cardtype) > 1 || !$rat->isValid($rat->_ENUM_CN,$cardtype)) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CARDTYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CARDTYPE_CODE);
            }
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CARDTYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CARDTYPE_CODE);
        }
    }
    
    public static function txnstatusValidation($txnstatus) {
        $rat = new self();
        if ($txnstatus != '') {
            if(strlen($txnstatus) > 20 || !$rat->isValid($rat->_ENUM_STATUS,$txnstatus)) {
                throw new Exception('Transaction Status is not valid');
            }
        } else {
                throw new Exception('Transaction Status is mandatory');
        }
    }
    
    public static function queryrefnoValidation($queryrefno) {
        if ($queryrefno != '') {
            //$txnrefno = intval($txnrefno);
            if(strlen($queryrefno) > 12 || !(ctype_digit($queryrefno)) || ($queryrefno < 1)) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_QUERY_REQ_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_QUERY_REQ_NO_CODE);
            } 
            
        } else {
               throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_QUERY_REQ_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_QUERY_REQ_NO_CODE);
        }
       
    }
    
    public static function dateValidation($date) { 
        if($date != ''){ 
            $date_regex = '/^(19|20)\d\d[\-\/.](0[1-9]|1[012])[\-\/.](0[1-9]|[12][0-9]|3[01])$/';
            if (!preg_match($date_regex, $date)) {
                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_DOB_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_DOB_CODE);
            }
        } else{
             throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_DOB_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_DOB_CODE);
        }
    }
    
    public static function dobAgeValidation($dob) { 
        $dob = date("Y-m-d",strtotime($dob));
//
//        $dobObject = new DateTime($dob);
//        $nowObject = new DateTime();
//
//        $diff = $dobObject->diff($nowObject);
        $age = floor((time() - strtotime($dob))/31556926);
        
        if($age < MIN_AGE_ALLOW_18){
          throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_AGE_DOB_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AGE_DOB_CODE);   
        }
      
    }
    
    public static function txnNumValidation($txnno) {
        if ($txnno != '') {
            //$txnrefno = intval($txnrefno);
            if(strlen($txnno) > 20 || !(ctype_digit($txnno)) || $txnno < 1) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_TXNNUM_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TXNNUM_CODE);
            } 
            
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_TXNNUM_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TXNNUM_CODE);
           
        }
    }
    
    public static function cardnoValidation($cardno) {
        if ($cardno != '') {
            //$txnrefno = intval($txnrefno);
            if(strlen($cardno) > 16 || !(ctype_digit($cardno))) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_NO_CODE);
            } 
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_NO_CODE);
        }
    }
    
    public static function corporatecodeValidation($corpcode) {
        if ($corpcode != '') {
            if(strlen($corpcode) > 20 || !(ctype_digit($corpcode))) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CORPORATE_CODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CORPORATE_CODE_CODE);
            } 
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CORPORATE_CODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CORPORATE_CODE_CODE);
        }
    }
    
    public static function chkallowChar($code) {
        if(empty($code)) {
            return TRUE;
        }
       if(preg_match('/^[a-zA-Z0-9 !@#$%.?|,;*-=_()+\/[\]{}:]+$/', $code)) {
          return true; 
        }
        return false;
    }
    
    public static function chkallParams($params) {
       foreach($params as $field => $val) {
                $value = (string) trim($val);
                  if(!self::chkallowChar($value)) {
                  throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_MSG.$field);
                  } 
            }
    }
    
    public static function Filter2MOBValidation($mobile) {
        if ($mobile != '') {
            if (strlen($mobile) != 10 || !(ctype_digit($mobile))) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER2_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER2_CODE);
            }else if(substr($mobile, 0, 1) == "0") {
             throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER2_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER2_CODE);   
            }
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER2_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER2_CODE);
        }
    }
    

    public static function loadExpiryValidation($loadExpiry) { 
       $loadDT = strtotime($loadExpiry);
       $currentDT = time();

         if( (!ctype_digit($loadExpiry) ) || (strlen($loadExpiry) > 14 ) || ($currentDT > $loadDT) ){
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER1_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER1_CODE);   
        }
 
    }
    
    public static function chkOriginalTxnNum($txnno) {
        if ($txnno != '') {
            //$txnrefno = intval($txnrefno);
            if(strlen($txnno) > 20 || !(ctype_digit($txnno)) || $txnno < 1) {
                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER5_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER5_CODE);
            } 
            
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER5_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER5_CODE);
        }
        
        return true;
    }
    

    public static function remitterCodeValidation($remitterflag,$remitterCode){
	if($remitterCode != ''){
	    if($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_MOBILE)){
		if (strlen($remitterCode) != 10 || !(ctype_digit($remitterCode))) {
		    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERCODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERCODE_CODE);
		} else if(substr($remitterCode, 0, 1) == "0") {
		    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERCODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERCODE_CODE);   
		}
	    } else if($remitterflag == strtolower(self::CUST_IDENTIFIER_TYPE_PARTNER)){
		if(strlen($remitterCode) > 50 || !(ctype_alnum($remitterCode))) {
		    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERCODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERCODE_CODE);
		}
	    }
	} else {
	     throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERCODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REMITTERCODE_CODE);
	}
    }
    

    public static function cardpackidValidation($cardpackid) {
        if ($cardpackid != '') {
            if(strlen($cardpackid) > 20 || !(ctype_digit($cardpackid))) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_PACK_ID_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_PACK_ID_CODE);
            } 
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_PACK_ID_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_PACK_ID_CODE);
        }
    }
    
    public static function genderValidation($gender) {
        $rat = new self();
        if ($gender != '') {
            if(!$rat->isValid($rat->_ENUM_GENDER, $gender)) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_GENDER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_GENDER_CODE);
            }
        }
    }

    public static function amountValidation($amount) {
        if(((string)trim($amount) == '')||(!ctype_digit((string)trim($amount)))||(trim($amount) < 1)) {
	    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_CODE);
	}
    }
    
     public static function MemberIDCardNoValidation($txnidtype,$memberidCardNo){
	if($memberidCardNo != ''){
	    if($txnidtype == strtolower(self::TXN_IDENTIFIER_TYPE_MOBILE)){
		if (strlen($memberidCardNo) != 10 || !(ctype_digit($memberidCardNo))) {
		    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_MEMBER_ID_CARD_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_MEMBER_ID_CARD_NO_CODE);
		} else if(substr($memberidCardNo, 0, 1) == "0") {
		    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_MEMBER_ID_CARD_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_MEMBER_ID_CARD_NO_CODE);   
		}
	    } else if($txnidtype == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER)){
		if(strlen($memberidCardNo) > 50 || !(ctype_alnum($memberidCardNo))) {
		    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_MEMBER_ID_CARD_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_MEMBER_ID_CARD_NO_CODE);
		}
	    }
	} else {
	     throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_MEMBER_ID_CARD_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_MEMBER_ID_CARD_NO_CODE);
	}
    }
    
}
