<?php

/*
 * Validator class for remittance
 * 
 */

class Validator_Kotak_Customer extends Validator_Kotak {
 
    public static function nameValidation($name,$paramName=NULL) {
        if ($name != '') {
            if (strlen($name) > 50 || !Util::aplhaValidation($name)) {
                if($paramName=="FirstName"){
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FNAME_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FNAME_CODE);
                }elseif($paramName=="LastName"){
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LNAME_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LNAME_CODE);
                }else{
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_NAME_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_NAME_CODE);
                }
            }
        } else {
            if($paramName=="FirstName"){
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FNAME_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FNAME_CODE);
            }elseif($paramName=="LastName"){
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LNAME_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LNAME_CODE);
            }else{
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_NAME_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_NAME_CODE);
            }
        }
    }
  
    public static function requesttypeValidation($requesttype) {
        $cust = new self();
        if ($requesttype != '') {
            if(strlen($requesttype) > 1 || !$cust->isValid($cust->_ENUM_TYPE, $requesttype)) {
                  throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REQUEST_TYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REQUEST_TYPE_CODE);
            }
        }else{
              throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_REQUEST_TYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_REQUEST_TYPE_CODE);
        }
    }
    
    public static function isOriginalRequestValidation($isoriginalrequest) {
        $cust = new self();
        if ($isoriginalrequest != '') {
            if(strlen($isoriginalrequest) > 1 || !$cust->isValid($cust->_ENUM_YN,$isoriginalrequest)) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_IS_ORIGINAL_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_IS_ORIGINAL_CODE);
            }
        }
    }
    
    
    public static function originalAckNumValidation($isoriginalacknum) {
        if ($isoriginalacknum != '') {
            if(strlen($isoriginalacknum) > 12 || !(ctype_digit($isoriginalacknum))) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_ORIGINAL_ACK_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_ORIGINAL_ACK_CODE);
            }
        }
        else
        {
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_ORIGINAL_ACK_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_ORIGINAL_ACK_CODE);
        }
   }
    
    public static function narrationValidation($narration) {
        if ($narration != '') {
            if(strlen($narration) > 20) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_NARRATION_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_NARRATION_CODE);
            } 
        }
    }
    
    public static function transactionNarrationValidation($narration) {
        if ($narration != '') {
            if(strlen($narration) > 40) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_NARRATION_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
            } 
        }
    }
  
    
    public static function partnerRefnoValidation($partnerrefno) {
        if ($partnerrefno != '') {
            if(strlen($partnerrefno) > 50 || !(ctype_alnum($partnerrefno))) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PARTNER_REF_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARTNER_REF_CODE);
            } 
            
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PARTNER_REF_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARTNER_REF_CODE);
        }
    }
    
    public static function cardpackidValidation($cardpackid) {
        if ($cardpackid != '') {
            if(strlen($cardpackid) > 20 || !(ctype_alnum($cardpackid))) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_PACK_ID_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
            } 
        }
    }
    
    
    public static function memberIdCardNumValidation($memidcardnum) {
        if ($memidcardnum != '') {
            if(strlen($memidcardnum) > 50) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_MEMBER_ID_CARD_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
            } 
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_MEMBER_ID_CARD_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
        }
    }

    public static function accountBlockStatusValidation($accblockstatus) {
        $cust = new self();
        if ($accblockstatus != '') {
            if(strlen($accblockstatus) > 1 || !ctype_digit($accblockstatus) || !$cust->isValid($cust->_ENUM_YN_BOOL,$accblockstatus)) {
                throw new Exception('Account Block Status is not valid');
            }
        } else {
                throw new Exception('Account Block Status is mandatory');
        }
    }
    
    public static function currencyCodeValidation($currencycode) {
        if($currencycode != '') {
            if($currencycode != 356) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CURRENCY_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
            }
        } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CURRENCY_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
        }
    }
    
    
    public static function walletCodeValidation($walletcode) {
        if($walletcode != '') {
            if(strlen($walletcode) > 6) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
            }
       }
    }
    
    public static function uniqueCodeflagValidation($uniquecodeflag) {
        $cust = new self();
        if ($uniquecodeflag != '') {
            if(strlen($uniquecodeflag) > 1 || !$cust->isValid($cust->_ENUM_EP,$uniquecodeflag)) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER1_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
            }
       } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER1_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
       }
    }
    
    public static function uniqueCodeflagMPValidation($uniquecodeflag) {
        $cust = new self();
        if ($uniquecodeflag != '') {
            if(strlen($uniquecodeflag) > 1 || !$cust->isValid($cust->_ENUM_MP,$uniquecodeflag)) {
                 throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER1_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER1_CODE);
            }
       } else {
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER1_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER1_CODE);
       }
    }
    
    
    public static function uniqueCodeValidation($uniquecode) {
        if ($uniquecode != '') {
            if(strlen($uniquecode) > 50) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER2_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
            }
       } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FILLER2_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PARAMETER_CODE);
       }
    }
    
}
