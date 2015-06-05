<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IndexController
 *
 * @author Vikram
 */
class Remit_Boi_IndexController extends App_Agent_Controller
{
    //put your code here
    


    public function init()
    {
        parent::init();
    }    
     /**
     * Controller's entry point
     *
     * @access public
     * @return void
     */
    public function testAction()
    {
       // print 'EXIT';exit('here');
        $report = new Remit_Boi_Reports();
        $arr = $report->test();
        print '<pre>';
        print_r($arr);
        print '<pre>';
    }
    

}
