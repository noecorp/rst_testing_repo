<?php
/**
 * Mvc Axis Bank Cardholder fund - CDRL
 *
 * @package frontend_controllers
 * @copyright company
 */

class Mvc_Axis_CardholderfundController extends App_Agent_Controller
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
    
       
    public function mobileAction()
    { 
        $apisettingModel = new APISettings();
        $this->title = 'Cardholder Reload - Enter Mobile';
        // initializing required classes     
        $session = new Zend_Session_Namespace('App.Agent.Controller');  
        
        
        $user = Zend_Auth::getInstance()->getIdentity(); 
        $session->ch_fund_load_auth = '';
        $session->chmobile_verified = ''; 
        $session->chmobile = '';
        $session->validated_ch_fund_load_auth = '';
        // Get our form and validate it            
        $form = new Mvc_Axis_FundReloadMobileForm(array('action' => $this->formatURL('/mvc_axis_cardholderfund/mobile'), 'method' => 'POST', 'name'=>'frmFund', 'id'=>'frmFund'
                                        ));                      
        $this->view->form = $form;         
       
        
           // If all API are working only then proceed with Card holder reload
       if($chkApi = $apisettingModel->checkAPIresponse()){  
         $formData  = $this->_request->getPost();
         $btnMob = isset($formData['btn_mob'])?$formData['btn_mob']:'';
        // verify cardholder mobile number        
         if($btnMob){            
           
            if($form->isValid($this->getRequest()->getPost())){
                $objBAPC = new BindAgentProductCommission();
                $chModel  = new Mvc_Axis_CardholderUser();
                $chInfo = $chModel->getCardHolderInfoApproved('','',$formData['mobile_number']);
                
                if(empty($chInfo)){
                    $this->_helper->FlashMessenger(array('msg-error' => 'Mobile does not exist',));
                } else {
                $chProd = $chModel->getCardHolderProducts($chInfo->id);
                
                if(!empty($chProd)){
                        $agentCurrentProduct = $objBAPC->checkAgentCurrentProduct(array('mobile_number'=>$formData['mobile_number'], 'agent_id'=>$user->id, 'product_id'=>$chProd['product_id']));                       
                        if(!$agentCurrentProduct){
                            $this->_helper->FlashMessenger(array('msg-error' => 'You are not assigned product',));                    
                            unset($session->chmobile_verified);
                            unset($session->chmobile);
                        }else {
                            $session->chmobile = $formData['mobile_number'];
                            $session->chmobile_verified = 1;                    
                            $this->_redirect($this->formatURL('/mvc_axis_cardholderfund/load'));exit;
                        }
                } else { 
                        $this->_helper->FlashMessenger(array('msg-error' => 'Cardholder is not assigned any product',)); 
                        unset($session->chmobile_verified);
                        unset($session->chmobile);
                       }           
                
              }
            }        
        }
       } else
    { 
        $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => SETTING_API_ERROR_MSG,
                        )
                    );
        $errorExists = true;
    }
    }
        
        
        public function loadAction(){
            $apisettingModel = new APISettings();
            $this->title = 'Cardholder Reload';
            $session = new Zend_Session_Namespace('App.Agent.Controller'); 
            $user = Zend_Auth::getInstance()->getIdentity(); 
            $mobVerified = isset($session->chmobile_verified)?$session->chmobile_verified:0;
            $ecsResp = '';
            $session->validated_ch_fund_load_auth = '';
            $agentId = $user->id;
            $m = new App\Messaging\MVC\Axis\Agent();
            $objCh = new Mvc_Axis_CardholderUser();
            $objChf = new Mvc_Axis_CardholderFund();
            $objAgBal = new AgentBalance();
            //$chProds = $objCh->getCardHolderProducts();
            
            $form = new Mvc_Axis_FundReloadForm(array('action' => $this->formatURL('/mvc_axis_cardholderfund/load'), 'method' => 'POST', 'name'=>'frmFund', 'id'=>'frmFund'
                                      ));                      
            
            $this->view->form = $form; 
             
             if($mobVerified!=1){
                $this->_helper->FlashMessenger(array( 'msg-error' => 'Please complete mobile detail step',));
                $this->_redirect($this->formatURL('/mvc_axis_cardholderfund/mobile'));exit;
            }
            $formData  = $this->_request->getPost();
             
            //echo "<pre>";print_r($formData);
            $btnAuth = isset($formData['send_auth_code'])?$formData['send_auth_code']:0;                 
            $submit = isset($formData['is_submit'])?$formData['is_submit']:'';
            $chInfo = $objCh->getCardHolderInfoApproved('','',$session->chmobile);
            $this->view->chInfo = $chInfo->toArray();
            $chProdsArr = $objCh->getCHDropDownProducts($chInfo->id);
            
            if(empty($chProdsArr)){                    
                $this->_helper->FlashMessenger( array('msg-error' =>'Cardholder is not assigned any product',) );
            }else{
                $form->getElement("product_id")->setMultiOptions($chProdsArr);
            }
           // If all API are working only then proceed with Card holder reload  
         if($chkApi = $apisettingModel->checkAPIresponse()){  
           
        // sending authrization code on mobile
        if($btnAuth==1 && !$submit){   

            if($formData['amount']=='' || $formData['amount']<1){
                $this->_helper->FlashMessenger( array('msg-error' =>'Invalid load amount',) ); 
                $form->populate($formData);
            } else { 
                    //if($session->ch_fund_load_auth==''){
                    try{
                        
                        $isSufficientBal = $objAgBal->chkAllowReLoad(array('agent_id'=>$user->id, 'product_id'=>$formData['product_id'], 'amount' => $formData['amount']));
                        if($isSufficientBal) {
                           
                            $userData = array('mobile1'=>$session->chmobile, 
                                              'amount'=>$formData['amount'],
                                              'cardholder_name'=>$chInfo->name,
                                              'account_name' => AXIS_BANK_SHMART_ACCOUNT,
                                              'currency' => CURRENCY_INR
                                             ); 

                             if($session->ch_fund_load_auth==''){
                            $resp = $m->cardholderFundLoadAuth($userData);
                             }
                               else {
                                  $resp = $m->cardholderFundLoadAuth($userData ,$resend = TRUE);  
                               }
                            //$form->populate($formData);
                
                            $this->_helper->FlashMessenger( array('msg-success' => 'Authorization code has been sent on cardholder mobile number',) );
                            $form->getElement("send_auth_code")->setValue("0");                
                           }
                    }catch (Exception $e ) {
                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    }  
                   //}
                  }
                }
        $form->populate($formData);
//echo $session->ch_fund_load_auth;
        if($submit){            
            if($form->isValid($this->getRequest()->getPost())){
                $authValidated = isset($session->validated_ch_fund_load_auth)?$session->validated_ch_fund_load_auth:0;
                
                // matching the auth code
                if($authValidated!=1 && $session->ch_fund_load_auth != $formData['auth_code']){ 
                  
                     $this->_helper->FlashMessenger(array('msg-error' => 'Invalid authorization code',));
                     $this->view->formData = $formData;
                     
                } else {  
                        $session->validated_ch_fund_load_auth = 1;
                        
                        // validating the agent n cardholder details
                        try{
                            $chkData = array('agent_id'=>$user->id, 'product_id'=>$formData['product_id'], 
                                             'amount'=>$formData['amount'], 'block_amount'=>true
                                            );
                            
                            //$resp = $objChf->chkAllowReLoad($chkData);
                            
                            $data = array(  'agent_id'=>$user->id,
                                            'product_id'=>$formData['product_id'],
                                            'cardholder_id'=>$chInfo->id,
                                            'amount'=>$formData['amount'],
                                            'txn_type'=>TXNTYPE_CARD_RELOAD
                                         );
                            
                            $objCHBal = new CardholderBalance();
                            $iniResp = $objCHBal->initiateAgentToCardholder($data); // initiate agent to cardholder txn
                        }catch (Exception $e ) {
                                //  var_dump($e);exit;
                                App_Logger::log(serialize($e) , Zend_Log::ERR);
                                $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                                $this->view->formData = $formData;
                        }  
                   
                        if(isset($iniResp['flag']) && $iniResp['flag']){
                                    // sending details to ecs
                                    try{

                                        $param = array(
                                            'amount'    => $formData['amount'],
                                            'crn'       => $chInfo->crn, 
                                            'agentId'   => $user->id,
                                            'transactionId'=> $iniResp['txnCode'],
                                            'currencyCode' => CURRENCY_INR_CODE,
                                            'countryCode'  => COUNTRY_IN_CODE                                            
                                        );
                                           $ecsApi = new App_Socket_ECS_Transaction();
                                           $ecsResp = $ecsApi->cardLoad($param);
                                    }catch (Exception $e ) {
//                                          var_dump($e);exit;
                                        App_Logger::log(serialize($e) , Zend_Log::ERR);
                                        //$this->_helper->FlashMessenger(array('msg-error' => 'Card fund load transaction failed of amount '.$formData['amount'],));
                                        $this->view->formData = $formData;
                                    }
                                    
                                    /******** deciding error message *******/
                                    // getting message for sms 
                                    $mobileEndChars = substr($chInfo->mobile_number, -4); 
                                    if($ecsResp){ 
                                        //not required
                                        //
                                         //$chSmsMsg = 'Your card loaded successfully with amount '.$formData['amount'];                                        
                                         
                                         
                                        // $agSmsMsg = 'Success! Amount of Rs.'.$formData['amount'].' loaded on card for mobile no ending '.$mobileEndChars.' on '.Util::getCurrDateTime(FLAG_NO).'.';                                        
                                        /* Entry in Card Loads Table */
                                        // $data
                                        $data['txn_code'] = $iniResp['txnCode'];
                                        $objCHBal->saveCardloads($data);
                                        $session->ch_fund_load_auth = '';
                                        $session->validated_ch_fund_load_auth = 0;
                                        
                                         $txnMsg = 'Cardholder loaded amount '.$formData['amount'].' successfully';
                                         $ecsStatus = FLAG_SUCCESS;
                                    } else {
                                        $objAgent = new AgentBalance();         
                                        $agentBalance = $objAgent->getAgentActiveBalance($agentId);
                                       
                                         $agSmsMsg = ' Failure! Amount of Rs. '.$formData['amount'].' could not be loaded on card for mobile no ending '.$mobileEndChars.' on '.Util::getCurrDateTime(FLAG_NO).'.';                                        
                                         $agemailData = array(
                                                    'email' => $user->email,
                                                    'name' => ucfirst($user->first_name).' '.ucfirst($user->last_name),
                                                    'amount' => Util::numberFormat($formData['amount']),
                                                    'endChars' => $mobileEndChars,
                                                    'balance' => Util::numberFormat($agentBalance)
                                                     
                                             );
                                         $m->agentCardholderReload($agemailData);
                                         $ecsStatus = FLAG_FAILURE;
                                         if($ecsApi->getError()!=''){
                                             $txnMsg = 'Cardholder fund load transaction failed with amount '.$formData['amount'].' as '.$ecsApi->getError();
                                            }
                                         else {
                                             $txnMsg = 'Cardholder fund load transaction failed with amount '.$formData['amount'];
                                         } 
                                    }
                                    /******** deciding error message over *******/
                                                                
                                    
                                    // completing the txn of fund relaod to CH
                                    $completeData = array(  'agent_id'=>$user->id,
                                                            'cardholder_id'=>$chInfo->id,
                                                            'amount'=>$formData['amount'],
                                                            'txn_code'=>$iniResp['txnCode'],
                                                            'txn_status'=>$ecsStatus,
                                                            'remarks'=>$txnMsg,
                                                         );
                                    
                                    try{
                                        $compResp = $objCHBal->completeAgentToCardholder($completeData);
                                    }catch (Exception $e ) {
                                        //  var_dump($e);exit;
                                        App_Logger::log(serialize($e) , Zend_Log::ERR);
                                        $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                                        $this->view->formData = $formData;
                                    } 
                                    
                                    
                                    // sending sms to cardholder and agent
                                   
                                    if ($ecsStatus == FLAG_FAILURE){
                                        $chSmsData = array('mobile1'=>$chInfo->mobile_number, 'account_name' => AXIS_BANK_SHMART_ACCOUNT);
                                        $chSmsResp = $m->cardholderLoadFundFailure($chSmsData);
                                    }
                                  
                                    //$agSmsData = array('mobile1'=>$user->mobile1, 'smsMessage'=>$agSmsMsg);
                                    //$agSmsResp = $objAlert->sendAgentCHLoadFund($agSmsData,'agent');
                                 
                                   
                                    /*$dataTxn = array('product_id'=>$formData['product_id'],
                                                     'agent_id'=>$user->id,
                                                     'cardholder_id'=>$chInfo->id,
                                                     'amount'=>$formData['amount'],
                                                     'message'=>$ecsErrMes,
                                                     'status_flag'=>$ecsResp,
                                                     'txn_type'=>TXNTYPE_CARD_RELOAD,
                                                    );
                                    
                                    // updating the txn status in txn tables
                                    try{
                                        $objChf->updateReloadTxn($param);
                                    } catch (Exception $e ) {
                                                             $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),)); exit;                                                              
                                                            }*/
                                    
                                    if(!$ecsResp){
                                         $this->_helper->FlashMessenger(array('msg-error' => $agSmsMsg,)); 
                                         $this->view->formData = $formData;
                                    } else {
                                            $this->_redirect($this->formatURL('/mvc_axis_cardholderfund/complete/'));
                                    }
                                       
                            }
                          }
                        } 
                      } 
                      } else
    { 
        $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => SETTING_API_ERROR_MSG,
                        )
                    );
        $errorExists = true;
    }
                    }
                    
           
     public function completeAction()
     { 
        $session = new Zend_Session_Namespace('App.Agent.Controller');                  
         
        unset($session->chmobile_verified); 
        unset($session->chmobile);
        unset($session->ch_fund_load_auth);
        unset($session->validated_ch_fund_load_auth);
        
        $this->_helper->FlashMessenger(array('msg-success' => 'Cardholder fund loaded successfully',));        
     }      
     
     
     public function cancelAction(){
        $session = new Zend_Session_Namespace('App.Agent.Controller');                  
         
        unset($session->chmobile_verified); 
        unset($session->chmobile);
        unset($session->ch_fund_load_auth);
        unset($session->validated_ch_fund_load_auth);
        
        //$this->_helper->FlashMessenger(array('msg-success' => 'Cardholder fund load canceled.',));   
        $this->_redirect($this->formatURL('/mvc_axis_cardholderfund/mobile/'));
     }
}