<?php
/**
 * Log writer object definition
 *
 * @category App
 * @package App_DI
 * @copyright transerv
 * @author Mini <mini@transerv.co.in>
 */
class App_DI_Definition_EmailLog
{
    /**
     * This method will instantiate the object, configure it and return it
     *
     * @return Zend_Cache_Manager
     */
    public static function getInstance(){
        
        $path = APPLICATION_PATH . '/../logs/' . CURRENT_MODULE . '/email_'. date('Y_m_d') . '.log';
        if(!file_exists($path)) {
            fopen($path,'w') or die("Unable to create file on " . $path);
        }
            
        $path = realpath($path);
        
        $logger = new Zend_Log();
        $logger->addWriter(new Zend_Log_Writer_Stream($path));
        
        //if (!Zend_Registry::get('IS_PRODUCTION')) {
            //$logger->addWriter(new Zend_Log_Writer_Firebug());
        //}
        
        return $logger;
    }
}