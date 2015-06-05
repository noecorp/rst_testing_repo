<?php

class Logsms extends App_Model
{
    
  /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'id';
    
    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_LOG_SMS;
    
  
    /**
     * log api details into database
     *
     * @param array $inputArray 
     * @access public
     * @return bool
     */
    public function log(array $inputArray)
    {
       try {
        //$recipients = (is_array($inputArray['to'])) ? implode(", ", $inputArray['to']) : $inputArray['to'];
        $inputArr = array(
                'mobile'        => $inputArray['to'],
                'text'        => $inputArray['body'],
                'exception'        => $inputArray['exception'],
                'status'        => $inputArray['status'],
                'date_created' => new Zend_Db_Expr('NOW()')
        );
        //$this->insert($inputArr);
        $logger = new ShmartLogger(ShmartLogger::LOG_SMS);
        return $logger->log($inputArr);
        
       }
       catch(Exception $e ) {
           App_Logger::log($e->getMessage(), Zend_Log::ERR);
           return false;
        }
    }
     
}