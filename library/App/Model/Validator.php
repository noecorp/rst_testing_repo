<?php
/**
 * Validator
 * This will be responsible for handling balance Validation for agent and cardholder
 * 
 * @package Core
 * @copyright Transerv
 * @author Vikram Singh <Vikram@transerv.co.in>
 */
class Validator extends App_Model
{
    
    protected $_maximumAllowedLimit = '10000';
    protected $_minimumAllowedLimit = '0';
    
   

    public static function getCardholderMaximumAllowedLimit($id = 0)
    {
        return Validator::$_maximumAllowedLimit;
    }
    
    
    public static function getCardholderMinimumAllowedLimit($id = 0)
    {
        return Validator::$_minimumAllowedLimit;
    }
    
    public function getCardholderActiveBalance($id)
    {
        return Validator::$_minimumAllowedLimit;
    }
    
    
    public static function sumAmount($amount1, $amount2)
    {
        return $amount1 + $amount2;
    }

    public static function deductAmount($amount1, $amount2)
    {
        return $amount1 - $amount2;
    }
    
    
   /*
    * checkARNDuplicate()
    * $param is array which will have the module name and arn no. to varify
    */
    
    public function checkARNDuplicate($param){
       $objMob = new Mobile();
       $tableName = $param['tablename'];
        
       if($param['arn']=='' || $param['tablename']=='') {
            throw new Exception("ARN missing");
        }
        
        $tabName = $objMob->getTableName($tableName);        
     
        if($tabName!='') {
            
            $select = $this->_db->select();
                    if($tableName=='cardholder_details'){
                        $select->from(DbTable::TABLE_CARDHOLDER_DETAILS.' as chd');
                        $select->joinInner("t_cardholders as c", "chd.cardholder_id = c.id AND c.enroll_status='".STATUS_APPROVED."'", array('c.id'));   
                        $select->where('chd.arn=?',$param['arn']);
                        $select->where('chd.status=?',STATUS_ACTIVE);
                    }else {
                        $select->from($tabName);
                        $select->where('arn=?',$param['arn']);
                    }
                   
           $rs = $this->_db->fetchRow($select);
            
            if(empty($rs)) {
                return true;
            } else {
                throw new Exception("ARN already exists");
                exit;
            }            
        }
         exit;
    }
    
    /*
    * validatePAN()
    * $pan is PAN Number to be validated
    */
    
    public function validatePAN($pan,$throwException = TRUE){  
        if(trim($pan) == '') {
            
            return TRUE;
        }
        
        $panLen = strlen($pan);
        if($panLen != 10){
            if($throwException){
            throw new Exception("PAN number length should be 10 characters long");
            exit;
            }
            else{
                return FALSE;
            }
        }
        $f5Chars = substr($pan,0,5);
        $l1Char  = substr($pan,9,1);
        $m4Chars = substr($pan,5,4);
        
        if (!(ctype_alpha($f5Chars)) || !(ctype_alpha($l1Char)) || !(ctype_digit($m4Chars)) || !(ctype_alnum($pan))) {  
            if($throwException){
             throw new Exception("Invalid PAN number");
             exit;
            }
            else{
                return FALSE;
            }
        }  
        
        return true;
    }
    
     public function validateUID($uid){        
        $uidLen = strlen($uid);
       
        if($uidLen != 12){
            throw new Exception("UID number length should be 12 Digits long");
            exit;
        }
       if (!(ctype_digit($uid)) ) {  
           
             throw new Exception("Invalid UID number");
             exit;
        }  
        
        return true;
    }
    
     public function validatePassport($passport){        
        $passportLen = strlen($passport);
        
        if($passportLen !=8){
            throw new Exception("Passport number length should be 8 characters long");
            exit;
        }
       
        $f1Chars = substr($passport,0,1);
        $l7Char = substr($passport,1,8);
        
        if (!(ctype_alpha($f1Chars)) || !(ctype_digit($l7Char)) || !(ctype_alnum($passport))) {  
           
             throw new Exception("Invalid Passport number");
             exit;
        }  
        
        return true;
    }
    public function validCardholderData($chInfo){
         $state = new CityList();
         $util = new Util();
         
         $paramArray = array();
            $dob = Util::returnDateFormatted($chInfo['date_of_birth'],"Y-m-d","d-m-Y", "-");
            $stateCode = $state->getStateCode($chInfo['state']);
            $cityCode = $state->getCityCode($chInfo['city']);
            //$mobileCountryCode = $chInfo['mobile_country_code'];
            //$paramArray['cardNumber'] = '3333330000000987'; -- For testing
            $paramArray['cardNumber'] = $chInfo['crn'];
            $paramArray['address1'] = $chInfo['address_line1'];
            $paramArray['address2'] = $chInfo['address_line2'];
            $paramArray['address4'] = $stateCode;
            $paramArray['birthdate'] = preg_replace('/-|:/', null, $dob);
            $paramArray['citycode'] = $cityCode;
            $paramArray['countrycode'] = Util::getCountryCode($chInfo['country']);
            $paramArray['familyname'] = $chInfo['last_name'];
            $paramArray['firstname'] =$chInfo['first_name'];
            $paramArray['gender'] = $util->getGenderChar($chInfo['gender']);
            $paramArray['mothersmaidenname'] = $chInfo['mother_maiden_name'];
            $paramArray['zipcode'] = $chInfo['pincode'];
            $paramArray['phonemobile'] = $chInfo['mobile_number'];
            $paramArray['officemobile'] = $chInfo['mobile_number'];
            $paramArray['title'] = $chInfo['title'];
            
            
            return $paramArray;
    }
    
    public function validMvcCardholderData($chInfo){
                   $mobile = isset($chInfo['mobile_number']) ? $chInfo['mobile_number'] : '';
                   $countyrcode = isset($chInfo['mobile_country_code']) ? $chInfo['mobile_country_code'] : '';
                   $validMobilenumber = $countyrcode.$mobile;
                    $array = array(
                         'CAFNumber'     =>  isset($chInfo['arn']) ? $chInfo['arn'] : '', 
                         'FirstName'     =>  isset($chInfo['first_name']) ? $chInfo['first_name'] : '',
                         'LastName'      =>  isset($chInfo['last_name']) ? $chInfo['last_name'] : '', 
                         'MobileNumber'  =>  $validMobilenumber,
                         'DeviceID'      =>  isset($chInfo['device_id']) ? $chInfo['device_id'] : '', 
                         'CRN'           =>  isset($chInfo['crn']) ? $chInfo['crn'] : '', 
                         'CustommerType' =>  isset($chInfo['customer_mvc_type']) ? $chInfo['customer_mvc_type'] : ''
                     );
                
            
            return $array;
    }
    
    public static function isMinor($date) {
        
        if(trim($date) == '') {
            return TRUE;
        }
        if(!self::validateYear($date,18)) {
            throw new Exception("Applicant cannot be a minor");
        } 
        return TRUE;
    }


    public static function validateYear($date, $yearToCompare = 18) {
        $byr = date('Y',strtotime($date));
        $bmon = date('m',strtotime($date));
        $bday = date('d',strtotime($date));
        if (date('Y') - $byr > $yearToCompare) { return true; } else { 
             if (date('Y') - $byr == $yearToCompare) {  
                  if (date('m') - $bmon > 0) { return true; } else { 
                       if (date('m') - $bmon == 0) { 
                            if (date('d') - $bday >= 0) { return true; } 
                       } 
                  } 
             } 
        } 
        return false; 

    }
    

     
      public static function isMidMinor($birth_date, $minyear = 11, $maxyear = 18, $exception = true) {  
        
           $div = floor(60*60*24*365.2421896);  
           $age = floor((time() - strtotime($birth_date))/$div);
            if(($minyear <= $age) && ($age < $maxyear))
            {
                return true;
            }
            
            if($exception)
            {
                if($age <= $minyear)
                {
                    throw new Exception("Applicant cannot be less than $minyear years of age.");
                }   
            }
            
            return false;
        }
        
        
        
    
    
  public function validateAadhar($aadhar_no ,$throwException = TRUE) {

        
        $uidLen = strlen($aadhar_no);
       
        if($uidLen != 12){
            if($throwException){
            throw new Exception("Aadhar number length should be 12 Digits long");
            exit;
            
            }
            else{
               return FALSE ;
            }
        }
       if (!(ctype_digit($aadhar_no)) ) { 
           if($throwException){
             throw new Exception("Aadhar number should be 12 Digits long");
             exit;
              }
            else{
               return FALSE ;
            }
        }  
        
        if (!Validator_Verhoeff::validate($aadhar_no)) {
            if($throwException){
            throw new Exception("Please provide the correct Aadhaar Number. In case Aadhaar Number is not available, please leave this field blank.");
            exit;
             }
            else{
               return FALSE;
            }
        }
        return TRUE;
    }
  
    public function checkColDuplicacy($param){
       $productId = isset($param['product_id']) ? $param['product_id'] : '';
       $column = $param['col']; 
       $statusValue = $param['status']; 
       $checkStatus = isset($param['status'])?TRUE:FALSE;
       
       
       if($param['col_name'] == '' || $param['tablename'] == '') {
            throw new Exception("Details missing");
        }
      
        if($param['tablename'] != '') {
            $select = $this->_db->select();
            $select->from($param['tablename']);
            $select->where($param['col_name'].' =?',$param['col_value']);
            if($checkStatus){
            $select->where($param['col_status'] . " IN ('".$statusValue."')");
            }
            if ($productId != '') {
            $select->where('product_id =?',$productId);
            }
            
            
            $rs = $this->_db->fetchRow($select);
            
            if(empty($rs)) {
                return true;
            } else {
                throw new Exception("$column already exists");
                exit;
            }            
        }
         exit;
    }
    
    
    
    public function checkColDuplicacyTF($param){
       $productId = isset($param['product_id']) ? $param['product_id'] : '';
       $column = $param['col']; 
       $statusValue = $param['status']; 
       $checkStatus = isset($param['status'])?TRUE:FALSE;
       
       
       if($param['col_name'] == '' || $param['tablename'] == '') {
            throw new Exception("Details missing");
        }
      
        if($param['tablename'] != '') {
            $select = $this->_db->select();
            $select->from($param['tablename']);
            $select->where($param['col_name'].' =?',$param['col_value']);
            if($checkStatus){
            $select->where($param['col_status'] . " IN ('".$statusValue."')");
            }
            if ($productId != '') {
            $select->where('product_id =?',$productId);
            }
            
            $rs = $this->_db->fetchRow($select);
            
            if(empty($rs)) {
                return true;
            } else {
                return false;
            }            
        }
         exit;
    }
    
  
}
