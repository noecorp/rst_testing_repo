<?php
/**
 * Allows user to manage their profile data
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class EmailauthorizationController extends App_Corporate_Controller
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
            
        $this->title = 'Email Authorization';
        $this->_helper->layout()->setLayout('withoutlogin');
        $id = $this->_getParam('id');
        $ver_code = $this->_getParam('code');
        $agentModel = new CorporateUser();
        $emailAuth = $agentModel->emailAuthChk($id,$ver_code);
        $url = App_DI_Container::get('ConfigObject')->corporate->url;
        $loginUrl = $this->formatURL($url.'/profile/login');
        if($emailAuth) {
            if($emailAuth === 'already_ver') {
            $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => "Your email has already been verified. Please <a href = '".$loginUrl."'>click here</a> to start accessing your Shmart! Partner portal.",
                    )
                );
             $this->view->msg = 'failure';
        }
        else{
             $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => "Your email has been verified. Please <a href = '".$loginUrl."'>click here</a> to start accessing your Shmart! Partner portal by using the temporary password sent to your email.
Do not forget to change your password IMMEDIATELY after you log into your Shmart! Partner portal for the first time.",
                    )
                );
             $this->view->msg = 'success';
            }
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
    
     public function updateemailAction(){
        $user = Zend_Auth::getInstance()->getIdentity();
        $this->title = 'Email Authorization';
        $this->_helper->layout()->setLayout('withoutlogin');
        $id = $this->_getParam('id');
        $ver_code = $this->_getParam('code');
        $corporateModel = new CorporateUser();
        //echo $id." , ".$ver_code; exit;
        $emailAuth = $corporateModel->updateAuthChk($id,$ver_code);
        
        
        $url = App_DI_Container::get('ConfigObject')->corporate->url;
        $loginUrl = $this->formatURL($url.'/profile/login');
        if($emailAuth) {
            if($emailAuth === 'already_ver') {
            $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => "Your email has already been verified. Please <a href = '".$loginUrl."'>click here</a> to start accessing your Shmart! Partner portal.",
                    )
                );
             $this->view->msg = 'failure';
        }
        else{
             $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => "Your email has been updated successfully. Please <a href = '".$loginUrl."'>click here</a> to start accessing your Shmart! Partner portal by using the temporary password sent to your email.
Do not forget to change your password IMMEDIATELY after you log into your Shmart! Partner portal for the first time.",
                    )
                );
             $this->view->msg = 'success';
            }
        }
        else{
             $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => "This link is not valid for email authorization. Please contact the Corporate Help Center.",
                    )
                );
             $this->view->msg = 'failure';
        }
        
       if(isset($user->id)){
            $sessionId    = Zend_Session::getId();
            $userModel = new CorporateUser();
            $session = new Zend_Session_Namespace('App.Corporate.Controller');
            $user = Zend_Auth::getInstance()->getIdentity();
            if(App_DI_Container::get('ConfigObject')->session->ssn_implementation) {
                $sessionModel = new Session();
                $sessionModel->logoutSession();
            }
            
            $logindata =  array('portal'=> MODULE_CORPORATE,'corporate_id' => $user->id,'datetime_logout' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId);  
            $userModel->insertLoginLog($logindata);
            // destroy the session
             Zend_Session::destroy();
            
        }
    }
}