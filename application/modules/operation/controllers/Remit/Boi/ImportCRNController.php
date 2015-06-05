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
class Remit_Boi_ImportCRNController extends App_Agent_Controller
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
    
    
    /* adddetailsAction() will be responsible import file inputs from user
     */
    public function adddetailsAction()
    {
       // print 'EXIT';exit('here');
        $report = new Remit_Boi_Reports();
        $arr = $report->test();
        print '<pre>';
        print_r($arr);
        print '<pre>';
    }
}
