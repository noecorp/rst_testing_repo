<?php
/**
 * ECS that manages the ECS releated stuff for defining
 * the ECS method in the application
 *
 * @package Core
 * @copyright transerv
 */

class TxnMessage extends App_Model
{

    /**
     * Returns an message on basis of error group and code of processor (ECS)
     * expects array with 'error_code' and 'error_group'
     */
    public function getMessage($param){
        $errorGroup = isset($param['error_group'])?$param['error_group']:'';
        $errorCode = isset($param['error_code'])?$param['error_code']:'';
        
       if($errorGroup=='' || $errorCode=='') {
           throw new Exception("Insufficient message data recieved!");
       }
       
        $tnxMessages = Zend_Registry::get(TXN_ERROR_MESSAGES);
       
        $msg = isset($tnxMessages[$errorGroup][$errorCode])?$tnxMessages[$errorGroup][$errorCode]:'';
        
        return $msg;
    }
   
}