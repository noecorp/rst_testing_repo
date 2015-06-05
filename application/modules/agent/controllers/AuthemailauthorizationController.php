<?php
/**
 * Allows user to manage their profile data
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class AuthemailauthorizationController extends App_Agent_Controller
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
                        
        $this->title = 'Auth Email Authorization';
        $this->_helper->layout()->setLayout('withoutlogin');
        $id = $this->_getParam('id');
        $ver_code = $this->_getParam('code');
        
        $agentModel = new AgentUser();
       
        $emailAuth = $agentModel->authemailAuth($id,$ver_code);
        if($emailAuth){
             $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => "Auth Email authorized successfully",
                    )
                );
             $this->view->msg = 'success';
        }
        else{
             $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => "This link is not valid for email authorization. Please contact the Agent Help Center.",
                    )
                );
             $this->view->msg = 'failure';
        }
    }
    
   
}