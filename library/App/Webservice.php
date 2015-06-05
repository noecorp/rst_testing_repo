<?php
/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_Webservice
{
 
    /**
     *  get Webservice
     * @param type $key
     * @return boolean
     */
    public static function get($key = '') {
        $_Webservice = Zend_Registry::get("WEBSERVICE");

        if($key == '') {
            //return all webservices - Need to restrict
            return $_Webservice;
        }
        
        if(!isset($_Webservice[$key]) || $_Webservice[$key] == '') {
            //webservice not found
            return false;
        }
        
        if(!isset($_Webservice[$key][APPLICATION_ENV]) || $_Webservice[$key][APPLICATION_ENV] == '') {
            //webservice not set for current application envoirment
            return false;
        }
        
        return $_Webservice[$key][APPLICATION_ENV];
        
    }
    
}