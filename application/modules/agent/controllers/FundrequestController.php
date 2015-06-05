<?php
/**
 * Default entry point in the application
 *
 * @package frontend_controllers
 * @copyright company
 */

class FundrequestController extends App_Agent_Controller
{
    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init()
    {
        
        // init the parent
        parent::init();
        
        //$this->_addCommand(new App_Command_SendEmail());
        
    }
    
    public function indexAction()
    {
        $this->title = 'Agent Fund Requests';
        
        $objFR  = new FundRequest();  
        $user = Zend_Auth::getInstance()->getIdentity();     
        $this->view->paginator = $objFR->getAgentFundRequests($user->id, $this->_getPage());
    }
    
    /**
     * Controller's entry point
     *
     * @access public
     * @return void
     */
   
     public function sendAction(){
        $this->title = 'Agent Fund Request';
        $session = new Zend_Session_Namespace('App.Agent.Controller');   
        $user = Zend_Auth::getInstance()->getIdentity();               
        
        $form = $this->getSendRequestForm();                       
        $this->view->form = $form;
        $formData  = $this->_request->getPost();  
        $objFR = new FundRequest();
        
        $btnSend = isset($formData['btn_send'])?$formData['btn_send']:'';

        if($btnSend){
            
            if($form->isValid($this->getRequest()->getPost())){
                $formData = $this->getRequest()->getPost();
                $formData['request_status'] = STATUS_PENDING;
                $formData['agent_id'] = $user->id;
              
                try{                   
                      $resp = $objFR->addFundRequest($formData);                      
                } catch (Exception $e ) {                 
                                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                            $errMsg = $e->getMessage();                   
                                            $this->_helper->FlashMessenger(
                                                array(
                                                        'msg-error' => $errMsg,
                                                     )
                                            );
                                        }  
                                        
              if($resp){
                  $alertMsg = $objFR->chkAgentMinMaxLoad($formData);
                  if(!empty($alertMsg)){
                      $flashMsg = 'Fund request has been sent successfully.For future please request for fund between '.CURRENCY_INR.' '.Util::numberFormat($alertMsg['minValue']).' and '.CURRENCY_INR.' '.Util::numberFormat($alertMsg['maxValue']).'.'; 
                  }
                 
                  else{
                    $mailArray = array(
                    'amount' =>$formData['amt'],
                    'agent_code' =>$user->agent_code,
                    'agent_email' =>$user->email,
                    'agent_mobile_number' => $user->mobile1,
                    'comments' => $formData['comments'],
                    );
                $m = new App\Messaging\MVC\Axis\Agent();
                $m->agentFundRequest($mailArray);
                  $flashMsg = 'Fund request has been sent successfully';
                  }
                  $this->_helper->FlashMessenger( array( 'msg-success' => $flashMsg, ) );
//                  $this->_redirect('/fundrequest/index/');
                  $this->_redirect($this->formatURL('/fundrequest/index/'));
              }                                        
          }
        }      
    } 
    
    
    private function getSendRequestForm(){
        return new FundRequestForm(array(
            'action' => $this->formatURL('/fundrequest/send'),
            'method' => 'post',
        ));
    }
    
    
      public function responseAction(){
      
      $this->title = 'Agent Fund Response';
      
      $objFR  = new FundRequest();                  
      $requestId = $this->_getParam('id');        
      
      if($requestId<1){
           $this->_helper->FlashMessenger( array( 'msg-error' => 'Invalid request Id',)  );
      }else{
          
          $this->view->paginator = $objFR->getAgentFundResponse($requestId, $this->_getPage());
      }
  } 
  
}