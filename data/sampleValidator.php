<?php

/*
Created a abstract class for Validation which implementing Zend_Validate_Interface . It can be used by extending it. OR we can call its method directly which will initiate specific validator class.

Validator can be called by 
$validator = App_Validator::getInstanceByName(VALIDATOR_NAME)

Error can be extracted using 
$validator->getErrors();//It will return array

Getting Error Message
$validator->getMessages();//It will return array
*/


/*Validating Email Address*/

        $validator = App_Validator::getInstanceByName(App_Validator::EMAILADDRESS);
        if ($validator->isValid("vikram0207@gmail.com") !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' Invalid email address given.');
        } 

/*Validating Not Empty*/

        $validator = App_Validator::getInstanceByName(App_Validator::NOTEMPTY);
        if ($validator->isValid(" ") !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' Empty Value provided');
        } 

/*Validating Alpha*/
        $validator = App_Validator::getInstanceByName(App_Validator::ALPHA);
        if ($validator->isValid("asdfasdf") !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' Alpha');
        } 
        
/*Validating Alpha with whitespaces*/
        $validator = App_Validator::getInstanceByName(App_Validator::ALPHA, array(App_Validator::TYPE_ALLOWED_SPACE => true));
        if ($validator->isValid("asdfasdfasd fas  dfasdf") !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' Alpha 2');
        } 

/*Validating Alphanumeric with whitespaces*/
        $validator = App_Validator::getInstanceByName(App_Validator::ALNUM, array(App_Validator::TYPE_ALLOWED_SPACE => true));
        if ($validator->isValid("asdfasdfasd fas  dfasdf213423") !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' Alphanumeric ');
        } 

/*Validating Float*/
        $validator = App_Validator::getInstanceByName(App_Validator::FLOAT);
        if ($validator->isValid("100.001") !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' Float ');
        } 

/*Validating Integer*/
        $validator = App_Validator::getInstanceByName(App_Validator::INT);
        if ($validator->isValid("100001") !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' INT ');
        } 


/*Validating Between without inclusive*/
        $validator = App_Validator::getInstanceByName(App_Validator::BETWEEN,array( App_Validator::TYPE_MIN_LENGTH => 1, App_Validator::TYPE_MAX_LENGTH =>10, App_Validator::TYPE_INCLUSIVE => false ));
        if ($validator->isValid("9") !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' between ');
        } 

/*Validating Between with inclusive*/
        $validator = App_Validator::getInstanceByName(App_Validator::BETWEEN,array( App_Validator::TYPE_MIN_LENGTH => 1, App_Validator::TYPE_MAX_LENGTH =>10, App_Validator::TYPE_INCLUSIVE => true ));
        if ($validator->isValid("10") !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' between ');
        } 
        
/*Validating String Length*/
        $validator2 = App_Validator::getInstanceByName(App_Validator::STRINGLENGTH,array(App_Validator::TYPE_MIN_LENGTH => 10,App_Validator::TYPE_MAX_LENGTH => 10));
        //$validator2 = new Zend_Validate_StringLength(array('min' => 100, 'max' => 200));        
        if($validator2->isValid("1234567890") !== true) {
                throw new InvalidArgumentException(__METHOD__ . ' Invalid Length.');                    
        }    

/*Validating Date with format*/
        $validator = App_Validator::getInstanceByName(App_Validator::DATE,array(App_Validator::TYPE_FORMAT => 'dd-mm-yyyy'));
        if($validator->isValid("10-10-2012") !== true) {
                throw new InvalidArgumentException(__METHOD__ . ' Date');                    
        }   


/*Validating Digits */
        /**
         * Note: Validating numbers
         * When you want to validate numbers or numeric values, be aware that this validator only validates digits. 
         * This means that any other sign like a thousand separator or a comma will not pass this validator. 
         * In this case you should use Int or Float. 
         */
        $validator = App_Validator::getInstanceByName(App_Validator::DIGITS);
        if($validator->isValid("111") !== true) {
                throw new InvalidArgumentException(__METHOD__ . ' DIGITS');                    
        }   

/*Validating Greater Than*/
        $validator = App_Validator::getInstanceByName(App_Validator::GREATERTHAN, array(App_Validator::TYPE_MIN_LENGTH => 10));
        if($validator->isValid("11") !== true) {
                throw new InvalidArgumentException(__METHOD__ . ' GREATERTHAN');                    
        }    

/*Validating Less than*/
        $validator = App_Validator::getInstanceByName(App_Validator::LESSTHEN, array(App_Validator::TYPE_MAX_LENGTH => 10));
        if($validator->isValid("9") !== true) {
                throw new InvalidArgumentException(__METHOD__ . ' LESSTHEN');                    
        }    

