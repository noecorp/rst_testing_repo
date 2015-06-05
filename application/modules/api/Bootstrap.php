<?php
/**
 * Frontend bootstrap
 *
 * @package Frontend
 * @copyright company
 */

class Api_Bootstrap extends App_Bootstrap_Abstract
{
    
    protected function _initSetupDirectory()
    {
        //Create Cache directory if not exists
        if(!is_dir(ROOT_PATH . '/logs')) {
            mkdir(ROOT_PATH . '/logs', '0755');
        } 
        
        //Create Cache directory if not exists
        if(!is_dir(ROOT_PATH . '/logs/api')) {
            mkdir(ROOT_PATH . '/logs/api', '0755');
        } 
    }        
    
}