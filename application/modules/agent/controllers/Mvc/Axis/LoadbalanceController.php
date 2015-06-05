<?php
/**
 * Mvc Axis Bank
 *
 * @package frontend_controllers
 * @copyright company
 */

class Mvc_Axis_LoadBalanceController extends App_Agent_Controller
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
        
    }
    
    /**
     * Controller's entry point
     *
     * @access public
     * @return void
     */
   
     public function indexAction(){
            $this->title = 'Cardholder Card Load';
            $formData  = $this->_request->getPost();
            /* temporarily commented for revert
            if(isset($formData['btn_discard']) && $formData['btn_discard']!=''){
                    $this->discardCardholder(); exit;
            }        
            else if(isset($formData['btn_back']) && $formData['btn_back']!=''){
                $this->_redirect('/mvc_axis_cardholder/step3/'); exit;
            } */       
        
            $form = new Mvc_Axis_LoadlimitForm();
            $session = new Zend_Session_Namespace('App.Agent.Controller'); 
            $validator = new Validator();
            $util = new Util();
            $chId = isset($session->cardholder_id)?$session->cardholder_id:'';
            $chModel  = new Mvc_Axis_CardholderUser();
            $chInfo = $chModel->getCardHolderInfoApproved($chId);            
            $user = Zend_Auth::getInstance()->getIdentity(); 
            $objECS = new ECS();

            $this->getRedirectCheck();

            $objProd = new Products();
            $productLimit = $objProd->agentProductLimit(array('agent_id'=>$user->id, 'product_id'=>$session->cardholder_product_id));
            $productLimitArr = $productLimit->toArray(); 
            $this->view->product_first_load_limit = $productLimitArr['limit_out_first_load'];

            $objAgLimit = new Agentlimit();        
            $agLimitArr = $objAgLimit->getAgentLimitInfo($user->id);                
            $this->view->agentLimit = $agLimitArr;

            $form = $this->getForm();                       
            $this->view->form = $form;            
            $chbModel  = new CardholderBalance();
            $chProductId = isset($session->cardholder_product_id)?$session->cardholder_product_id:'';

            $btnLoad = isset($formData['btn_amount'])?$formData['btn_amount']:'';
            $amtLoad = isset($formData['amount'])?$formData['amount']:'0';

            $state = new CityList();
            $objLogStatus = new LogStatus();
           if($amtLoad){
            if($form->isValid($this->getRequest()->getPost())){
                $data = array('agent_id'=>$user->id,
                              'product_id'=>$chProductId,
                              'cardholder_id'=>$chId,
                              'amount'=>$formData['amount'],
                              'txn_type'=>TXNTYPE_FIRST_LOAD
                             );
                
                try{                   
                     $objCHBal = new CardholderBalance();
                     $initiateAgToTxn = $objCHBal->initiateAgentToCardholder($data);    
                } catch (Exception $e ) {
                        App_Logger::log(serialize($e) , Zend_Log::ERR);
                        $this->_helper->FlashMessenger(array( 'msg-error' => $e->getMessage(),));
                    } 
                    
                 $ecsApi = new App_Socket_ECS_Transaction();
                 
                 if(isset($initiateAgToTxn['flag']) && $initiateAgToTxn['flag']){                 
                    try{
                        $ecsResp='';
                        $param = array(
                                            'amount'    => $formData['amount'],
                                            'crn'       => $chInfo->crn, 
                                            'agentId'   => $user->id,
                                            'transactionId'=> $initiateAgToTxn['txnCode'],
                                            'currencyCode' => CURRENCY_INR_CODE,
                                            'countryCode'  => COUNTRY_IN_CODE                                            
                                        );
                                      
                                           
                                           $ecsResp = $ecsApi->cardLoad($param);
                                           
                    }   catch (Exception $e ) { 
                                App_Logger::log(serialize($e) , Zend_Log::ERR);
                                $this->discardCardholder(); 
                                $this->_helper->FlashMessenger(array( 'msg-error' => 'Card fund load transaction failed of amount '.$formData['amount'],));
                        }          
                    
                    if($ecsResp){
                        $cardloadData = array(
                              'agent_id'=>$user->id,
                              'cardholder_id'=>$chId,
                              'product_id'=>$chProductId,
                              'amount'=>$formData['amount'],
                              'txn_type'=>TXNTYPE_FIRST_LOAD,
                              'txn_code'=>$initiateAgToTxn['txnCode']
                            );
                        $objCHBal->saveCardloads($cardloadData);
                        
                         $smsData = array('product_name' => AXIS_BANK_SHMART_CARD,
                             'call_centre_number' => CALL_CENTRE_NUMBER, 'customer_support_email' => CUSTOMER_SUPPORT_EMAIL);
                         $chSmsMsg = 'Congratulations your '.$smsData['product_name'].' is now active.  You can reach us at '.$smsData['call_centre_number'].' or '.$smsData['customer_support_email'];
                         $txnMsg = 'Cardholder first load has been done successfully with amount '.$formData['amount'];
                         $ecsStatus = FLAG_SUCCESS;
                         $mailBody = 'Congratulations, your '.$smsData['product_name'].' Account is active. and your account has been credited with amount: '.$formData['amount'];
                    } else {
                                $updStopCH = $chModel->updateCardholderDetail(array('enroll_status'=>STATUS_FAILED), $chId);
                                $logData = array(
                                                    'cardholder_id'=>$chId,
                                                    'by_agent_id'=>$user->id,
                                                    'remarks'=>'Cardholder first load transaction failed',
                                                    'status_old'=>STATUS_INCOMPLETE,
                                                    'status_new'=>STATUS_FAILED,
                                                );                               

                                try{
                                        $objLogStatus->log($logData);
                                }
                                 catch (Exception $e) {
                                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                 }
                                 
                                $cardInfo = array( 'cardNumber'    =>$chInfo->crn,);

                                try {
                                    $excepMsg='';
                                    $ecsStopCard = new App_Api_ECS_Transactions();
                                    $flg = $ecsStopCard->stopCard($cardInfo);                                    
                                    
                                } catch (Exception $e) {
                                            $excepMsg = $e->getMessage();
                                            App_Logger::log($excepMsg, Zend_Log::ERR);
                                        }
                                if(!$flg && $excepMsg=='') {
                                    $errMsg = $ecsStopCard->getError();
                                    if($errMsg==''){
                                        $errMsg = 'Stop process of card number '.$cardInfo['cardNumber'].' failed.';
                                    }
                                    App_Logger::log($errMsg, Zend_Log::ERR);
                               }
                        $smsVariables = array('product' => AXIS_BANK_SHMART_PAY);
                         $chSmsMsg = 'Dear Customer, We regret to inform you that your '.$smsVariables['product'].' application could not be processed at this moment.';
                         $mailBody = 'Your card fund load transaction failed of amount '.$formData['amount'];
                         $ecsStatus = FLAG_FAILURE;
                         if($ecsApi->getError()!=''){
                             $txnMsg = 'Cardholder first load transaction failed with amount '.$formData['amount'].' as '.$ecsApi->getError();
                         }
                             else{
                             $txnMsg = 'Cardholder first load transaction failed with amount '.$formData['amount'];
                             }
                             
                    }
                                    
                    
                    try{
                        $completeData = array(  'agent_id'=>$user->id,
                                                'cardholder_id'=>$chId,
                                                'amount'=>$formData['amount'],
                                                'txn_code'=>$initiateAgToTxn['txnCode'],
                                                'txn_status'=>$ecsStatus,
                                                'remarks'=>$txnMsg,
                                             );
                        
                        $resp = $objCHBal->completeAgentToCardholder($completeData);
                        
                        $smsEmailData = array('amount' => $formData['amount'],
                                            'mobile1' => $chInfo['mobile_country_code'].$chInfo['mobile_number'],
                                            'email' => $chInfo['email'],
                                            'cardholder_name' => $chInfo['first_name'].' '.$chInfo['middle_name'].' '.$chInfo['last_name'],
                                            'smsMessage' => $chSmsMsg,
                                            'program_name' => AXIS_BANK_SHMART_PAY,                             
                                            'mailBody' => $mailBody,
                                            'ecsStatus'=>$ecsStatus,
                                            'product_name'=>AXIS_BANK_SHMART_CARD,
                                            'call_centre_number' => CALL_CENTRE_NUMBER, 
                                            'customer_support_email' => CUSTOMER_SUPPORT_EMAIL
                                           ); 
                        } catch (Exception $e ) { 
                           App_Logger::log(serialize($e) , Zend_Log::ERR);
                           $this->_helper->FlashMessenger(array( 'msg-error' => $e->getMessage(),));}
                    }
                        
                        if(isset($resp) && ($resp) && $ecsStatus==FLAG_SUCCESS){
                             
                             try{
                                $m = new App\Messaging\MVC\Axis\Agent();
                                $m->cardholderBalance($smsEmailData);
                             }catch (Exception $e) { 
                                        App_Logger::log(serialize($e) , Zend_Log::ERR);
                                    }   
                            
                                
                               //************* MVC Registration ***********//                            
                                $apisettingModel = new APISettings();

                                $validator = new Validator();
                                $array = $validator->validMvcCardholderData($chInfo);
                                $enrollMVCStatus = STATUS_PENDING;        
                                
                                if($chkApi = $apisettingModel->checkAPIresponse()){
                                    try {
                                        $mvcApi = new App_Api_MVC_Transactions();
                                        $flg = $mvcApi->Registration($array);
                                        if($flg)
                                            $enrollMVCStatus = STATUS_SUCCESS;
                                        else 
                                            App_Logger::log($mvcApi->getError(), Zend_Log::ERR);
                                        
                                    } catch (Exception $e) { 
                                        App_Logger::log(serialize($e) , Zend_Log::ERR);
                                    }     
                                    
                                } else {
                                    App_Logger::log(SETTING_API_ERROR_MSG , Zend_Log::ERR);
                                }
                                //********* MVC Registration Over **********//

                                
                               $mvcObj = new Mvc_Axis_CardholderUser();
                               $mvcData = array(
                                                'cardholder_id'=>$chId,
                                                'mvc_type'=>$chInfo->customer_mvc_type,
                                                'device_id'=>$chInfo->device_id,
                                                'mvc_enroll_status'=>$enrollMVCStatus,
                                                'mvc_enroll_attempts'=>'0',
                               );
                               
                               try {
                                    $mvcAdd = $mvcObj->addCardholderMVCDetails($mvcData);     
                               } catch (Exception $e) { 
                                        App_Logger::log(serialize($e) , Zend_Log::ERR);
                                    } 
                                    

                    $e = isset($e)?$e:'';
                    
                      $chModel->updateCardholderDetail(array('enroll_status'=>ENROLL_APPROVED_STATUS), $chId); 
                        $logData = array(
                                            'cardholder_id'=>$chId,
                                            'by_agent_id'=>$user->id,
                                            'remarks'=>'Cardholder registration and first load transaction done successfully',
                                            'status_old'=>STATUS_INCOMPLETE,
                                            'status_new'=>ENROLL_APPROVED_STATUS,
                                        );                               

                        try{
                                $objLogStatus->log($logData);
                        }
                        catch (Exception $e) {
                                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            }
                        //****** updating in log status in db over ********//
                                    
                      
                      $this->_helper->FlashMessenger(
                                                        array('msg-success' => 'Cardholder enrolled successfully and amount is loaded in account',)
                                                    );  
                      $this->_redirect($this->formatURL('/mvc_axis_cardholder/complete/'));       
                    } else { 
                            if(empty($e)){
                                        $msg = 'Card fund load transaction failed of amount '.$formData['amount'];
                                        $this->_helper->FlashMessenger(array( 'msg-error' => $msg,));
                                        $this->view->formData = $formData;
                            } else if(!isset($ecsApi) || $ecsApi->getError()!=''){
                                $msg = 'Card fund load transaction failed of amount '.$formData['amount'];
                                $this->_helper->FlashMessenger(array( 'msg-error' => $msg,));
                                $this->_redirect($this->formatURL('/mvc_axis_cardholder/complete/'));
                            }
                             
                      }
                    }
              }
         }
   
        
    
    private function getForm(){
        return new Mvc_Axis_LoadlimitForm(array(
            'action' => $this->formatURL('/mvc_axis_loadbalance/index'),
            'method' => 'POST',
            'name'=>'frmLoadBalance',
            'id'=>'frmLoadBalance'
        ));
    }
    
    private function getRedirectCheck(){
        $session = new Zend_Session_Namespace('App.Agent.Controller');         
        $step1 = isset($session->cardholder_step1)?$session->cardholder_step1:'';
        $step2 = isset($session->cardholder_step2)?$session->cardholder_step2:'';
        $step3 = isset($session->cardholder_step3)?$session->cardholder_step3:'';
        
        if($step1!=1){
            $this->_helper->FlashMessenger(array( 'msg-error' => 'Please complete step1 process!', ));
            $this->_redirect($this->formatURL('/mvc_axis_cardholder/step1/')); exit;     
        }
        else if($step2!=1){
            $this->_helper->FlashMessenger(array( 'msg-error' => 'Please complete step2 process!', ));
            $this->_redirect($this->formatURL('/mvc_axis_cardholder/step2/')); exit;
        }
        else if($step3!=1){
            $this->_helper->FlashMessenger(array( 'msg-error' => 'Please complete step3 process!', ));
            $this->_redirect($this->formatURL('/mvc_axis_cardholder/step3/')); exit;            
        }
    }
    
    
    
     Private Function discardCardholder(){
            $session = new Zend_Session_Namespace('App.Agent.Controller');
            unset($session->cardholder_id); 
            unset($session->cardholder_step1);
            unset($session->cardholder_step2);
            unset($session->cardholder_step3);
            unset($session->cardholder_product_id);
            unset($session->termsconditions_validated);
            unset($session->termscondition_auth);
            unset($session->cardholder_auth);
            unset($session->validated_cardholder_auth);
            unset($session->crn_assigned);
            unset($session->ecs_registeration);
            unset($session->cardholder_mobile_number);
            $this->_redirect($this->formatURL('/mvc_axis_cardholder/step1/')); 
                    
     }
  
}