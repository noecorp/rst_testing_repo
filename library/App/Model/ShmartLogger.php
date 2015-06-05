<?php

class ShmartLogger extends App_Model
{
    //Directory Path
    static $base_path = BASE_URL_SHMART_LOGS;
    
    const LOG = 'log';
    const LOG_API_SOAP = 'log_api_soap';
    const LOG_RATNAKAR_RESPONSE_FILE = 'log_response_file';
    const LOG_SMS = 'log_sms';
    const LOG_EMAIL = 'log_email';
    const LOG_REF = 'log_ref';
    
    
    private $dir;
    private $logType;
    
    public function __construct($dir = self::LOG,$logType='JSON') {
        $this->dir = $dir;
        $this->logType = $logType;
        parent::__construct();
    }

    /**
     * log General messages into text files
     *
     * @param array $message 
     * @access public
     */
      public function log($message){
          $filePath = $this->getFilePath($this->dir);
          $writer = new Zend_Log_Writer_Stream($filePath);
          $logger = new Zend_Log($writer);
          $logger->info($this->encodeMessage($message));
     }
     
     
     public function encodeMessage($text){
         if(strtolower($this->logType) == 'json'){
           return json_encode($text);
         } 
         return $text;
     }

     private function getFilePath($dirName = 'log') {
         $logname  = date("Ymd") . '.log';
         return self::$base_path . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR . $logname;
     }
}
