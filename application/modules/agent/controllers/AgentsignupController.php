<?php
/**
 * Allow the admins to manage critical info, users, groups, permissions, etc.
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class AgentsignupController extends App_Agent_Controller
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
        // initiating the session
        $this->session = new Zend_Session_Namespace("App.Agent.Controller");
    }
    
    
    
    public function indexAction(){
        $this->title = 'Partner Signup, phone verification';
        $m = new App\Messaging\MVC\Axis\Agent();
        // Agent phone entry form.
        $form = new AgentphoneForm();      
        
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
                $agentModel = new AgentUser();
                
                try {
                $res = $agentModel->checkPhone($form->getValue('phone'));
                }
                 catch (Exception $e ) {
                   App_Logger::log($e->getMessage(), Zend_Log::ERR);
                   $errMsg = $e->getMessage();
                   
                        $this->_helper->FlashMessenger(
                            array(
                                    'msg-error' => $errMsg,
                                 )
                            );
                }  
              
                if($res =='phone_dup'){
                 
                $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Mobile number already registered with us.',
                    )
                );
                }            
                else 
                {
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Please check SMS on your mobile to get Verification Code.',
                    )
                );
                    
                    //Generate random verification code and store it in a session and send it to mobile phone in  SMS
                    $alerts = new Alerts();
                    $verificationCode = $alerts->generateAuthCode();
                    
                    $this->session->ver_code = $verificationCode;
                   
                    try{
                        $info = array ('v_code'=>$verificationCode,'mobile1'=>$form->getValue('phone'));
                        
                        $m->verificationCode($info);
                         
                         
                    } catch (Exception $e ) {    
                                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                $errMsg = $e->getMessage();
                                $this->_helper->FlashMessenger(
                                    array(
                                        'msg-error' => $errMsg,
                                    )
                                );
                    } 
                    //$this->_redirect('/agentsignup/verification/');
                    $this->_redirect($this->formatURL('/agentsignup/verification/'));
                    
                   
                }
                
            }
        }
        $this->view->form = $form;
    }
    
    //set verification code
     public function verificationAction()
    {
        $this->title = 'Mobile verification code';
        echo $this->session->ver_code;
        // use the login layout
        
        $form = new VerificationForm();
        if ($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
               if( $this->session->ver_code == $form->getValue('code') )
               {
                   
                   $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Mobile phone verified sucessfully. Please proceed.',
                    )
                );
//                   $this->_redirect('/system/add');
                   $this->_redirect($this->formatURL('/system/add'));
               }
               else
               {
                
                   
                   $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Incorrect Verification code entered.',
                    )
                );
               }
            }
            
            
        } 
        
        $this->view->form = $form;
    }
   
}