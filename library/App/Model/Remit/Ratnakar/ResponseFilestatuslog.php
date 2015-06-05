<?php

class Remit_Ratnakar_ResponseFilestatuslog extends Remit_Ratnakar
{
    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
//    protected $_primary = 'id';
    
    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_RATNAKAR_RESPONSE_FILE_STATUS_LOG;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    
    /*
     * Add remittance request in status change log 
     */
    
    
    public function addStatus($param)
    {
         
        
       try {       
          //return $this->_db->insert(DbTable::TABLE_RATNAKAR_RESPONSE_FILE_STATUS_LOG,$param); 
           $logger = new ShmartLogger(ShmartLogger::LOG_RATNAKAR_RESPONSE_FILE);
           return $logger->log($param);          
       }
       catch(Exception $e ) {
           App_Logger::log($e->getMessage(), Zend_Log::ERR);
           return false;
        }
    }
    
    
   
    
    
}