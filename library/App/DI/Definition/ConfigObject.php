<?php
/**
 * Config object definition
 *
 * @category App
 * @package App_DI
 * @copyright company
 */
class App_DI_Definition_ConfigObject
{
    /**
     * This method will instantiate the object, configure it and return it
     *
     * @return Zend_Config_Ini
     */
    public static function getInstance(){
        if(class_exists('Zend_Config_Ini')) {
            return new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        } elseif(class_exists('\Zend\Config\Reader\Ini')) {
            $a = new \Zend\Config\Reader\Ini();
            $resp = $a->fromFile(ROOT_PATH . '/application/configs/application.ini');
            return $resp[APPLICATION_ENV];
        }
    }
}