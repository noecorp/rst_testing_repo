<?php
/**
 * Allows user to manage their profile data
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class ServicesController extends App_Agent_Controller
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
     * Allows users to see their dashboards
     *
     * @access public
     * @return void
     */
    public function indexAction(){
                                //echo __CLASS__ . ":" . __FUNCTION__ . ":" . __LINE__;exit;
        $this->title = 'Dashboard';
    }
    
    
    public function cardholderRegiAction()
    {
        
    }
    
   
  
}