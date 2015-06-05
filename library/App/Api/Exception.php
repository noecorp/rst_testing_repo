<?php
class App_Api_Exception extends Exception {

    public function __construct($message, $code = null) {
        parent::__construct($message, $code);
    }
}
