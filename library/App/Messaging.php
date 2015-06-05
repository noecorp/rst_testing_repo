<?php
namespace App;

/**
 * Messaging
 * Default parent Messaging for all the messages in the application
 *
 * @category App
 * @author Vikram
 */
abstract class Messaging {

    private $_view = null;
    public $_mail = null;
    public $_sms = null;
    private $_module = null;
    private $_error = null;

    /**
     * Messaging Constructor
     * @param type $product
     */
    public function __construct($product) {
        $this->setModule($product, get_called_class());
        $this->_view = new \stdClass();
        $this->_sms = new \App\Messaging\Transport\SMS();
        //if(class_exists('\Zend_Mail'))
        $this->_mail = new \App\Messaging\Transport\Mail();
        $this->_mail->setCustomOption(TRUE);
//        elseif(class_exists(' \Zend\Mail\Message')) 
//                    $this->_mail = new \App\Messaging\Transport\Mail2();
    }

    //Abstract method
    abstract public function sendSMS();

    abstract public function sendMail();
    
    
    /**
     * __set Magic method
     * Set Value if not doesn't exsits
     * @param type $name
     * @param type $value
     */
    public function __set($name, $value) {
        $this->$name = $value;
    }

    /**
     * __get Magic Method
     * Used to get Value if not found get it from the class object
     * @param type $name
     * @return type
     */
    public function __get($name) {
        if(isset($this->$name)) {
            return $this->$name;
        }
    }

    /**
     * setValue
     * Method to set value
     * @param type $name
     * @param type $value
     */
    public function setValue($name, $value) {
        $this->$name = $value;
    }

    /**
     * getValue
     * Method to get value form class object
     * @param type $name
     * @return type
     */
    public function getValue($name) {
        if(isset($this->$name)) {
            return $this->$name;
        }
    }

    /**
     * setViewValue
     * Set View Param
     * @param type $name
     * @param type $value
     */
    public function setViewValue($name, $value) {
        $this->_view->$name = $value;
    }

    /**
     * getViewValue
     * Method to get View value
     * @param type $name
     * @return type
     */
    public function getViewValue($name) {
        if(isset($this->_view->$name)) { 
            return $this->_view->$name;
        }
    }

    /**
     * getAllViewValues
     * Get all view params
     * @return type
     */
    public function getAllViewValues() {
        return $this->_view;
    }

    
    /**
     * Setup
     * Method to setup products of Mail and SMS
     * @param type $product
     */
    public function setup($product) {
        //Setup Product
        $product = str_replace(__CLASS__, __CLASS__ . '\\Transport', $product);
        $this->_product = $product;
        //Set Mail
        $this->_mail->setView($this->_product);
        //Set SMS
        $this->_sms->setView($this->_product);
    }

    /**
     * setSMSViewParam
     * SMS Method to set SMS template view param
     * @param type $name
     * @param type $value
     */
    public function setSMSViewParam($name, $value) {
        $this->_sms->setViewParam($name, $value);
    }

    /**
     * setTemplate
     * Set Transport (MAIL/SMS) templates
     * @param type $name
     */
    public function setTemplate($name) {
        $tName = $this->formatTemplateName($name);
        $this->_sms->setTemplate(strtolower($this->_module . '\\' . $tName));
        $this->_mail->setTemplate(strtolower($this->_module . '\\' . $tName));
    }

    /**
     * setMailViewParam
     * Method to set Mail View parameters
     * @param type $name
     * @param type $value
     */
    public function setMailViewParam($name, $value) {
        $this->_mail->setViewParam($name, $value);
    }

    /**
     * getSMS
     * Method to get object of SMS
     * @return type
     */
    public function getSMS() {
        return $this->_sms;
    }

    /**
     * getMail
     * Method to get Mail object
     * @return type
     */
    public function getMail() {
        return $this->_mail;
    }

    /**
     * formatTemplateName
     * Method to convert CamelCase into underscore seperated.  
     * @param type $name
     * @return type
     */
    private function formatTemplateName($name) {
        return $name = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
    }

    /**
     * getClassWithoutNamespace
     * Method to extract class name only if it contain namespace
     * @param type $namespace
     * @param type $classWithNS
     * @return type
     */
    public function getClassWithoutNamespace($namespace, $classWithNS) {
        if ($namespace != '') {
            return str_replace($namespace . '\\', '', $classWithNS);
        }
    }

    /**
     * setModule
     * Method to set Module like (operation, agent etc)
     * @param type $namespace
     * @param type $classWithNS
     */
    public function setModule($namespace, $classWithNS) {
        if ($namespace != '') {
            $this->_module = str_replace($namespace . '\\', '', $classWithNS);
        }
    }

    /**
     * setError
     * Method to set error
     * @param type $errorMsg
     */
    public function setError($errorMsg) {
        \App_Logger::log($errorMsg, \Zend_Log::ERR);        
        $this->_error = $errorMsg;
    }

    /**
     * getError
     * Get Error Message
     * @return type
     */
    public function getError() {
        return $this->_error;
    }
    
    /**
     * generateRandom6DigitCode
     * Method to generate Random 6 digit code
     */
    public function generateRandom6DigitCode()
    {
        return rand(111111,999999);
    }
        
    // Remittance Amount checked for decimal
     public function validateAmount($amount){        
       if ($amount == ''){
                   throw new \App_Exception ("Please enter valid amount for fund transfer");  
                }
       if (!(ctype_digit($amount)) ) {  
           
             throw new \App_Exception("Amount should not be in decimals");
             exit;
        }  
        
        return true;
    }
    
}