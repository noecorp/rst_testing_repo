<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IndexController
 *
 * @author Mini
 */
class Mvc_IndexController extends App_Operation_Controller
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
        //print 'EXIT';exit('here');
        $report = new Mvc_Axis_Reports();
        $arr = $report->test();
        print '<pre>';
        print_r($arr);
        print '<pre>';
    }
    

}
