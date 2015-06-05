<?php
/**
 * Add Beneficiary
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class Corp_Ratnakar_CardloadController extends App_Agent_Controller
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
    
    
    
    public function searchAction(){
        
        $this->title = 'Search Cardholder';
        unset($this->session->fundtransfer_amount);
        unset($this->session->fundtransfer_auth);
        unset($this->session->cardholder_mobile_number);
         // Agent phone entry form.
        $user = Zend_Auth::getInstance()->getIdentity(); 
        $form = new Corp_Ratnakar_SearchCardholderForm();
        $cardholdersModel = new Corp_Ratnakar_Cardholders();
        $agentsModel = new Agents();
        $submitForm = TRUE;
        $authEmailStatus = $agentsModel->getAgentAuthEmailVerifySatus($user->id);
        if($authEmailStatus['auth_email'] == ''){
           $this->_helper->FlashMessenger(array('msg-error' => 'Your email is required to proceed further, please contact system administrator '));
            $submitForm = FALSE;     
        }
        if($authEmailStatus['auth_email_verification_status'] == STATUS_PENDING && $authEmailStatus['auth_email_verification_id'] == 0){
         $this->_helper->FlashMessenger(array('msg-error' => 'Your email is not verified, please check your mail for further instructions or contact Agent help center'));
            $submitForm = FALSE;       
        }
        $formdata = $this->_request->getPost();
          if($submitForm){
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
              
            $medi_assist_id = $formdata['medi_assist_id'];
            $cardholderdetail = $cardholdersModel->searchByMediAssistId($medi_assist_id);
            if($medi_assist_id != '' && !empty($cardholderdetail)){
            
                $this->_redirect($this->formatURL("/corp_ratnakar_cardload/load?medi_assist_id=".$medi_assist_id));         
            }
            else
            {
               $this->_helper->FlashMessenger(array('msg-error' => 'Please enter a valid Medi assist id'));
                          
            }
           }
            
            }
    }
            
        $this->view->form = $form;
        
    }
    
    
    
    
    public function loadAction() {
        $this->title = 'Load Cardholder';
        $formdata = $this->_request->getPost();
        $form = new Corp_Ratnakar_LoadCardholderForm();  
        $user = Zend_Auth::getInstance()->getIdentity(); 
        $m = new App\Messaging\Corp\Ratnakar\Agent();
        $cardholdersModel = new Corp_Ratnakar_Cardholders();
        $insuranceClaimModel = new Corp_Ratnakar_InsuranceClaim();
        $agentsModel = new Agents();
        $purseModel = new MasterPurse();
       
        
       
        $btnAdd = isset($formdata['is_submit'])?$formdata['is_submit']:false;
        $medId = $this->_getParam('medi_assist_id');
        $btnAuth = isset($formdata['send_auth_code'])?$formdata['send_auth_code']:'0';
                        try {
                            $cardholderdetail = $cardholdersModel->searchByMediAssistId($medId);
                            $this->session->cardholder_mobile_number = $cardholderdetail['mobile'];
                            $this->view->details = $cardholderdetail;
                            
                        } catch (Exception $e) {
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            $msg = $e->getMessage();
                            $this->_helper->FlashMessenger(array('msg-error' => $msg,));
                        }
                    
                    
     
        if($btnAuth== 1){  
            try {
               
                $userData = array('mobile'=>$this->session->cardholder_mobile_number,
                    'email' =>$formdata['email'],
                    'auth_code' =>$formdata['auth_code'],
                    'amount' => $formdata['amount'],
                    'product' => CORP_PRODUCT,
                    'medi_assist_id' => $medId,
                    'cardholder_name' => $cardholderdetail['name'],
                    'employer_name' => $cardholderdetail['employer_name'],
                    'hospital_id' => $formdata['hospital_id']
                    );                               
                
                
                if(isset($this->session->fundtransfer_auth))
                    $m->cardLoadAuth($userData,$resend = TRUE);
                else
                     $m->cardLoadAuth($userData);
                $formdata['send_auth_code'] = 0;  
                $this->_helper->FlashMessenger( array('msg-success' => 'Authorization code has been sent on your mobile number.',) );
                $form->populate($formdata);
                
        }catch (Exception $e ) {  
                $errorExists = true;
                $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                
                   
            }  
        }
       echo 'dff'.$this->session->fundtransfer_auth;
        if($btnAdd){
           
           if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                
                try{
                    // CHECK AGENT LIMITS
                    if(TRUE){
                    if ($formdata['amount'] == $this->session->fundtransfer_amount){
                        if ($formdata['auth_code'] == $this->session->fundtransfer_auth){
                           // get master purse id and get purse code from product.ini
                            $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CORP);
                            $purseCode = $product->purse->code->corporateins; 
                            $purseDetails = $purseModel->getPurseIdByPurseCode($purseCode); 
                            $data = array();
                            $data['product_id'] = $cardholderdetail['product_id'];
                            $data['customer_id'] = $cardholderdetail['customer_master_id'];
                            $data['cardholder_id'] = $cardholderdetail['id'];
                            $data['amount'] = $this->session->fundtransfer_amount;
                            $data['hospital_id_code'] = $formdata['hospital_id'];

                            $data['medi_assist_id'] = $medId;
                            $data['purse_master_id'] = $purseDetails['id'];
//                            $data['txn_type'] = RAT_CORP_TXNTYPE_LOAD;

//                            $data['txn_type'] = TXNTYPE_RAT_CORP_LOAD;
//                            $data['by_agent_id'] = $user->id;
//                            $data['agent_id'] = $user->id;
//                            $data['ip'] = $cardholdersModel->formatIpAddress(Util::getIP());
//                            $data['status'] = STATUS_PENDING;
//                            $data['date_created'] = new Zend_Db_Expr('NOW()');
                            $res = $insuranceClaimModel->initiateCardLoad($data);
                         
                           
                          
                           if($res > 0 ){
   
                
                    $m->cardLoadSuccess($data);
                    $this->_helper->FlashMessenger(
                                array(
                                    'msg-success' => 'Your request for fund transfer has been initiated',
                                )
                        );
                    $this->_redirect($this->formatURL("/corp_ratnakar_cardload/search")); 
                    }
                    else {
                        $this->_helper->FlashMessenger(
                                array(
                                    'msg-error' => 'Your request for fund transfer could not be initiated. Acknowledgement no. '.$txnCode,
                                )
                        );
                       
                   
                        }
                     }
                        else
                        {
                             $this->_helper->FlashMessenger( array('msg-error' => 'Authorization code entered is not correct',) );  
                        }
                    }
                    else
                        {
                             $this->_helper->FlashMessenger( array('msg-error' => 'Please check your SMS for correct fund transfer amount',) );  
                        }
            }// Agent and product Limits 
                        else{
                         $this->_helper->FlashMessenger( array('msg-error' => 'Amount entered for fund transfer not is not within limits',) );  
                           
                        }
            }
            catch (Exception $e) {
                       
                        $errMsg = $e->getMessage();
                        $this->_helper->FlashMessenger( array('msg-error' => $errMsg) );  
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);

                    }
                 $formdata['send_auth_code'] = 0; 
                 $form->populate($formdata);
            }
            }
       
    }
      $this->view->form = $form;
    }
    
    
    
    public function checkstatusAction() {
        $this->title = 'Check Card Load Status';
    }
}