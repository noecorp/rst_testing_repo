<?php
/**
 * Db Salt Key 
 * For Encryption and Decryptioin
 *
 * @category App
 * @package App_DI
 * @copyright company
 */
class App_DI_Definition_DbConfig
{
    /**
     * @return DbSalt
     */
    public static function getInstance(){
            return App_DI_Container::get('ConfigObject')->resources->db->param;
    }
}