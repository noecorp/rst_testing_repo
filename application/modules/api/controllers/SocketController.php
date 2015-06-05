<?php

/**
 * Default entry point in the application
 *
 * @package api_controllers
 * @copyright transerv
 */

class SocketController extends Zend_Controller_Action
{
    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->_helper->viewRenderer->setNoRender(true);
    }
    
    /**
     * Controller's entry point
     *
     * @access public
     * @return void
     */
    public function indexAction()
    {
        print 'testing CLI';exit;
   
    }
    
   
  
    
    
}



