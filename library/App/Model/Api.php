<?php

class Api extends App_Model
{
    
    private $_error_msg = '';
    private $_error_code = '';

    public function setError($code='',$msg='') {
        if(!empty($code)) {
            $this->_error_code = $code;
        }
        if(!empty($msg)) {
            $this->_error_msg = $msg;
        }
    }
    
    public function getErrorCode() {
        return $this->_error_code;
    }
    
    public function getErrorMsg() {
        return $this->_error_msg;
    }
   
}