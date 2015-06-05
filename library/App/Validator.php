<?php
/**
 * Validator
 * 
 * 
 * @category Core
 * @package App_Validator
 * @copyright Transerv
 * @author Vikram Singh <vikram@transerv.co.in>
 */
abstract class App_Validator implements Zend_Validate_Interface {
    
    const ALNUM = 'Alnum';
    const ALPHA = "Alpha";
    const BETWEEN = "Between";
    const DATE = "Date";
    const DIGITS = "Digits";
    const EMAILADDRESS = "EmailAddress";
    const FLOAT = "Float";
    const GREATERTHAN = "GreaterThan";
    const LESSTHEN = "LessThan";
    const INARRAY = "InArray";
    const INT = "Int";
    const NOTEMPTY = "NotEmpty";
    const STRINGLENGTH = "StringLength";

    const TYPE_REGEXP = 'REGEXP';
    const TYPE_MIN_LENGTH = 'min';
    const TYPE_MAX_LENGTH = 'max';
    const TYPE_INCLUSIVE = 'inclusive';
    const TYPE_ALLOWED_SPACE= 'allowWhiteSpace';
    const TYPE_FORMAT= 'format';


    protected $name = false;
    protected $errors = false;
    protected $invalidCharacters = array();
    protected $options = array();
    protected $validOptions = array();

    /**
     * Useless constructor
     */
    protected function __construct() {

    }

    /**
     * Get validator by a givan name
     *
     * @param string $name
     * @throws InvalidArgumentException
     * @return object
     */
    public static function getInstanceByName($name, $options = null) {
        
        if ( $name == false) {
            throw new InvalidArgumentException(__METHOD__ . ' Invalid validator name');
        }

//        if (class_exists($expandClassName) == false) {
//            throw new InvalidArgumentException(__METHOD__ . ' Missing expand class about this validator name');
//        }
        
        $expandClassName = 'Zend_Validate_'.$name;
        //print $expandClassName;
        if(!empty($options)) {
            //echo "<pre>";print_r($options);
            return new $expandClassName($options);
        } 
        return new $expandClassName();        
    }

}