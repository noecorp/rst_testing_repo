<?php
/**
 * Mvc Axis Bank Reports Model
 *
 * @package MVC Axis Bank
 * @copyright transerv
 */

class Mvc_Axis_Reports extends Mvc_Axis
{

    public function test()
    {
        print self::PROGRAM_TYPE;
        print '<br />';
        //print self::PRODUCT_TYPE;
        $select = $this->_db->select();
        $select->from("t_bank",array('id','name'));
        return  $this->_db->fetchAll($select);
    }
   
}