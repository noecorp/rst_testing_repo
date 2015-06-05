<?php

class TechController extends App_Operation_Controller
{
    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init(){
        // init the parent
        parent::init();
    }
    
    /**
     * Allows the user to view all the flags registered in the application
     *
     * @access public
     * @return void
     */
    public function indexAction(){
        echo "here";exit;
    }
    
 
}