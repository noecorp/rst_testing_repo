<?php
/**
 * Application Exception Class
 *
 * @category App
 * @package App_Exception
 * @copyright Transerv
 */

class App_Exception extends Zend_Exception
{
    
    public static $_ERROR_MSG = array(
        '1001'  => 'Unable to login',
        '1002'  => 'Invalid Username/Password',
    );

    /**
     * getCustomMessage
     * Function to get Custom Message for flash messanger
     * @param type $code
     * @param type $exception
     * @return string
     */
    public function getCustomMessage($code,$exception='') {
        if(isset($exception) && $exception != '') {
            $msg = 'User Defined Exception:' . $exception;
        } elseif($this->getMessage() != '' && $this->getCode() != '') {
            $msg = 'Exception ' .$this->getMessage() . " (Code: $this->getCode())";
        } elseif($this->getMessage() != '') {
            $msg = 'Exception ' .$this->getMessage();
        }
        if(isset(self::$_ERROR_MSG[$code])) {
            $res =  self::$_ERROR_MSG[$code] . " (Code: $code)";
        } else {
            $res = 'Unknow Error';
        }
        $msg = $msg . PHP_EOL . 'Displaying Message : ' .$res;
        App_Logger::log($msg);
        return $res;
    }
    
}