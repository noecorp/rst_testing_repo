<?php

/**
 * ErrorCode 
 * This will provide details of Mvc Error code
 *
 * @author Vikram
 * @company Transerv
 */
class App_Api_MVC_ErrorCode {
    
    private $_errorCode = array(
        '0' => 'Successful Request',
        '1‐99' => 'System Error',
        '100' => 'Invalid Protocol',
        '101' => 'Invalid Mobile number',
        '102' => 'Mobile Number is already registered',
        '104' => 'Activation code mismatch',
        '105' => 'Unable to retrieve balance',
        '106' => 'Unable to retrieve transaction History',
   
    );
    
    public function _getError($errorCode)
    {
        if($errorCode > 0 && $errorCode < 100) {
            return 'System Error';
        }
       $errorCode = trim($errorCode);
       if(empty($errorCode) || $errorCode =='') {
           throw new Exception ('Processor MVC Error Code: Invalid Error code ' . $errorCode . ' provided.');
       }
       
       if(!isset($this->_errorCode[$errorCode])) {
           throw new Exception ('Processor MVC Error Code: Unknow error code ' . $errorCode . ' provided.');           
       }
       
       return $this->_errorCode[$errorCode];
       
    }
    
}

?>
