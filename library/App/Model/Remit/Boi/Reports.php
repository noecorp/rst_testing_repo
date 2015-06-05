<?php
/**
 * BOI Remittance Reports Model
 *
 * @package BOI Remittance
 * @copyright transerv
 */

class Remit_Boi_Reports extends Remit_Boi
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