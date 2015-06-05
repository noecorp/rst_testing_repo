<?php

class Logemail extends App_Model
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
    protected $_name = DbTable::TABLE_LOG_EMAIL;
    
  
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
        $recipients = (is_array($inputArray['to'])) ? implode(", ", $inputArray['to']) : $inputArray['to'];
        $inputArr = array(
                'to'    => $recipients,
                'from'        => $inputArray['from'],
                'subject'        => $inputArray['subject'],
                'body'        => $inputArray['body'],
                'template'        => $inputArray['template'],
                'date_created' => new Zend_Db_Expr('NOW()')
        );
        
           $logger = new ShmartLogger(ShmartLogger::LOG_EMAIL);
           $logger->log($inputArr);
        
        //return $this->insert($inputArr);
       }
       catch(Exception $e ) {
           App_Logger::log($e->getMessage(), Zend_Log::ERR);
           return false;
           // echo "<pre>";print_r($e);exit;
        }
    }
     
}