<?php
/**
 * Description of IndexController
 *
 * @author Mini
 */
class Remit_Kotak_IndexController extends Remit_IndexController
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
        $report = new Remit_Kotak_Reports();
        $arr = $report->test();
        print '<pre>';
        print_r($arr);
        print '<pre>';
    }
    

}
