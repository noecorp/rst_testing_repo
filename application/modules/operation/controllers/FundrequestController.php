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
    
    
    
    
    public function indexAction(){
        
        $this->title = 'Agent Fund Requests';
        
        $objFR  = new FundRequest();                          
        $this->view->paginator = $objFR->getAgentFundRequests('', $this->_getPage());
       //echo '<pre>//';
        //print_r($objFR->getAgentFundRequests('', $this->_getPage()));
    } 
    
    
    public function responseAction(){
        
        $this->title = 'Agent Fund Response';
        
        $objFR  = new FundRequest();                  
        $requestId = $this->_getParam('id');        
        $this->view->requestId = $requestId;
        $objFTType = new FundRequest();
        $reqInfo = $this->view->agentInfo = $objFTType->getFundRequestInfo($requestId);
        
        if($requestId<1){
             $this->_helper->FlashMessenger( array( 'msg-error' => 'Invalid agent request Id',)  );
        }else{                   
            $this->view->paginator = $objFR->getAgentFundResponse($requestId, $this->_getPage());                       
        }
    } 
    
    
    /**
     * sendAction () is responsible for handling the send agent fund request
     *
     * @access public
     * @return void
     */
   
     public function addresponseAction(){
        $this->title = 'Add Response';
        //echo "<pre>";print_r($session);
        $form = new AgentFundRequestForm();               
        $objFTType = new FundRequest();
        $requestId = $this->_getParam('id');
        $reqInfo = $objFTType->getFundRequestInfo($requestId);
        $resp = '';
        if(isset($reqInfo->request_status) && $reqInfo->request_status!=FLAG_PENDING){
//            $this->_redirect('/fundrequest/index/');
            $this->_redirect($this->formatURL('/fundrequest/index/'));
            $this->_helper->FlashMessenger(array( 'msg-error' => 'Response can be added for pending request only',));
        }
        
        $opr = Zend_Auth::getInstance()->getIdentity();
        if($requestId<1){
            $this->_helper->FlashMessenger(
                                    array( 'msg-error' => 'No request id received',)
                                );
        } else{
            
        $form = $this->getFundRequestForm($requestId);                       
        $this->view->form = $form;
        $formData  = $this->_request->getPost();        
        
        $btnEdit = isset($formData['btn_edit'])?$formData['btn_edit']:'';

        if($btnEdit){       
           
            if($form->isValid($this->getRequest()->getPost())){
                $formData = $this->getRequest()->getPost();
                $formData['agent_fund_request_id'] = $requestId;
                $formData['by_ops_id'] = $opr->id;
                $objFR = new FundRequest();    
                
                try{                   
                      $resp = $objFR->updateFundRequest($formData); // adding to db 
                      
                     if($resp)
                          $successMsg = 'Response has been added successfully';
                      
                     if($resp && $formData['response_status']==TXN_APPROVE_STATUS){ 
                                
                                // checking min/max agent fund load limit
                                
                                try{
                                    $objBT = new BaseTxn();
                                    $chkMinMaxLimit = $objBT->chkAgentMaxMinLoad(array('section_id'=>AGENT_SECTION_SETTING_ID, 'amount'=>$formData['amt']));
                                } catch(Exception $e){
                                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                    $successMsg = $successMsg.' <b>but '.$e->getMessage().'</b>';
                                }

                             }
                } catch (Exception $e ) {   App_Logger::log($e->getMessage(), Zend_Log::ERR);              
                                            $errMsg = $e->getMessage();                   
                                            $this->_helper->FlashMessenger(
                                                array('msg-error' => $errMsg,)
                                            );
                                        }  
                                        
                 if($resp){                                      
                                $this->_helper->FlashMessenger(
                                    array( 'msg-success' => $successMsg,)
                                );
                                
                                $this->_redirect($this->formatURL('/fundrequest/index/'));
                          }
                 }
         }
         
             
             $reqInfoArr = $reqInfo->toArray();
             
             $objFTType = new FundTransferType();
             $ftTypes = $objFTType->getFundTransferTypeForDropDown();
             $responseStatus = isset($formData['response_status'])?$formData['response_status']:$reqInfoArr['request_status'];
             
             $form->getElement("response_status")->setValue($responseStatus);
             $form->getElement("amt")->setValue($reqInfo['amt']);
             //$form->populate($reqInfoArr);
             $reqInfoArr['fund_transfer_type'] = $ftTypes[$reqInfoArr['fund_transfer_type_id']]; 
             $this->view->reqInfo = $reqInfoArr;
        }
     }      
     
    
    
    private function getFundRequestForm($requestId){
        return new AgentFundRequestForm(array(
            'action' => $this->formatURL('/fundrequest/addresponse?id='.$requestId),
            'method' => 'post',
        ));
    }
  
}