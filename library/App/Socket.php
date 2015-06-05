<?php
/**
 * App Socket
 *
 * @category App
 * @package App_Socket
 * @copyright company
 * @author Vikram Singh <vikram@transerv.co.in>
 */
class App_Socket
{
    protected $_address;
    protected $_port;
    
    public function __construct($address, $port) {
        $this->_address = $address;
        $this->_port = $port;
    }
 
}