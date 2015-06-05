<?php
/**
 * Kotak Remittance Reports Model
 *
 * @package Kotak Remittance
 * @copyright transerv
 */

class Remit_Kotak_Reports extends Remit_Kotak
{

    public function test()
    {
        print self::REMIT_NAME;
        print '<br />';
        print self::PRODUCT_TYPE;
        $select = $this->_db->select();
        $select->from("t_bank",array('id','name'));
        return  $this->_db->fetchAll($select);
    }
   
}