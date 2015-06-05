<?php
/**
 * Ratnakar Remittance Reports Model
 *
 * @package Ratnakar Remittance
 * @copyright transerv
 */

class Remit_Ratnakar_Reports extends Remit_Ratnakar
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