<?php
/**
 * Bank Config object definition
 *
 * @category App
 * @package App_DI
 * @copyright company
 */
class App_DI_Definition_Bank
{
    /**
     * This method will instantiate the object, configure it and return it
     *
     * @return Zend_Config_Ini
     */
    public static function getInstance($bank){
        return new Zend_Config_Ini(APPLICATION_PATH . '/configs/bank.ini', $bank);
    }
}