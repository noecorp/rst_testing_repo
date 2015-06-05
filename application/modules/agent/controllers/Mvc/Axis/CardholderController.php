<?php
/**
 * Mvc Axis Bank Cardholder Controller
 *
 * @package frontend_controllers
 * @copyright company
 */

class Mvc_Axis_CardholderController extends App_Agent_Controller
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
    
    /**
     * Controller's entry point
     *
     * @access public
     * @return void
     */
   
     public function indexAction(){

    } 
    
      
    public function step1Action()
    {      
         $this->title = 'Enroll Cardholder - Step 1';
        $formData  = $this->_request->getPost();
        
        /* temporarily commented for revert
         if(isset($formData['btn_discard']) && $formData['btn_discard']!=''){ 
            
            $this->discardCardholder(); exit;
        }
        
        else if(isset($formData['btn_next']) && $formData['btn_next']!=''){
                $this->_redirect('/mvc_axis_cardholder/step2/'); exit;
        }*/
        
        $user = Zend_Auth::getInstance()->getIdentity();        
        $session = new Zend_Session_Namespace('App.Agent.Controller'); 
        $session->current_page = 'step1';
        $chId = isset($session->cardholder_id)?$session->cardholder_id:0;
        $objAgBal = new AgentBalance();
        $config = App_DI_Container::get('ConfigObject');
       
        $minAge = $config->cardholder->age->min;
        $maxAge = $config->cardholder->age->max;
        $currDate = date('Y-m-d');
        
        $request = $this->getRequest();
        $chModel  = new Mvc_Axis_CardholderUser(); 
        $apisettingModel = new APISettings();
        $products  = new Products();
        $errorExists = false;
        
       
        // Get our form and validate it
        $form = $this->getForm();                       
        $this->view->form = $form;         
        
        
        $dateOfBirth = isset($formData['date_of_birth'])?$formData['date_of_birth']:'';
        $formData['date_of_birth'] = Util::returnDateFormatted($dateOfBirth, "d-m-Y", "Y-m-d", "-");
        $btnAuth = isset($formData['send_auth_code'])?$formData['send_auth_code']:'0';   
        $btnAdd = isset($formData['btn_add'])?$formData['btn_add']:'';
        $product = App_DI_Definition_BankProduct::getInstance(BANK_AXIS_MVC);
        $productUnicode = $product->product->unicode;
        $productList = $products->getAgentProducts($user->id, PROGRAM_TYPE_MVC, $productUnicode);       
       
        $form->getElement("product_id")->setMultiOptions($productList);
       if($chkApi = $apisettingModel->checkAPIresponse()){
        if( $btnAuth== 1 ){  
            
            try{
                $userData = array('mobile_country_code'=>$formData['mobile_country_code'], 
                                  'mobile1'=>$formData['mobile_number'],
                                  'mobile_number_old'=>$formData['mobile_number_old'],
                                  'product_name' => AXIS_BANK_SHMART_CARD);                               
               
                $m = new App\Messaging\MVC\Axis\Agent();
                if(isset($session->cardholder_auth))
                    $resp = $m->cardholderAuth($userData, $resend = TRUE);
                else
                     $resp = $m->cardholderAuth($userData);
                
                $formData['date_of_birth'] = Util::returnDateFormatted($dateOfBirth, "Y-m-d", "d-m-Y", "-"); 
                $this->view->cardholder_auth = $session->cardholder_auth;                       
                $session->cardholder_mobile_number = $formData['mobile_number'];                       
                
                $this->_helper->FlashMessenger( array('msg-success' => 'Authorization code has been sent on your mobile number.',) );
                $form->populate($formData);
                $form->getElement("send_auth_code")->setValue("0");                
                
        }catch (Exception $e ) {  
                $errorExists = true;
                $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                $form->populate($formData);
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
            }  
        }
        if($btnAdd){
            
            if($form->isValid($this->getRequest()->getPost())){
                
                try{
                        $isSufficientBal = $objAgBal->chkCanAssignProduct(array('agent_id'=>$user->id, 'product_id'=>$formData['product_id']));
                   } catch (Exception $e ) {  
                    $errorExists = true;
                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                    $form->populate($formData);   
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                }
                if(!$isSufficientBal){      
                        $errorExists = true;
                        $this->_helper->FlashMessenger(array('msg-error' => 'You have insufficient balance in your account. Please fund your account before proceeding with Customer Registration.',));
                        $form->populate($formData);
                }
                else
                {
                $authValidated = isset($session->validated_cardholder_auth)?$session->validated_cardholder_auth:'0';
            
                if($authValidated!=1 && $session->cardholder_auth != $formData['auth_code'] || ($session->cardholder_mobile_number!=$formData['mobile_number']) ){ // matching the auth code
                     $errorExists = true;
                     $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'Invalid Authorization Code',
                        )
                    );
                    $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-"); 
                    $this->view->formData = $formData;
                    $form->getElement("send_auth_code")->setValue("0");
                } else {                        
                                        
                $datetime1 = date_create($currDate);
                $datetime2 = date_create($formData['date_of_birth']);                
                $interval = date_diff($datetime1, $datetime2);
                $age = $interval->format('%y');              

                if ($age < $minAge){
                    $this->_helper->FlashMessenger(array( 'msg-error' => 'Minimum age should be 18 years', ));
                    $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-");
                    $this->view->formData = $formData;
                    $form->populate($formData);
                    $errorExists = true;
                }
                else if($formData['customer_mvc_type']=='mvcc' && trim($formData['device_id'])==''){
                       $errorExists = true;
                       $this->_helper->FlashMessenger(array('msg-error' => 'Device id is mandatory field',));
                       $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-");
                       $this->view->formData = $formData;
                       $form->populate($formData);
                } else {                   
                    
                        $session->validated_cardholder_auth = 1;
                        $chData = array('reg_agent_id'=>$user->id,
                                        'reg_datetime'=>date('Y-m-d H:i:s'),
                                        'email'=>$formData['email'],
                                        'title'=>$formData['title'],
                                        'first_name'=>$formData['first_name'], 
                                        'middle_name'=>$formData['middle_name'],
                                        'last_name'=>$formData['last_name'],  
                                        'mobile_country_code'=>$formData['mobile_country_code'],                            
                                        'mobile_number'=>$formData['mobile_number'],                                                       
                                        'enroll_status'=>STATUS_INCOMPLETE,
                                        'status'=>STATUS_UNBLOCKED
                                       ); 

                         $chDetails = array('email'=>$formData['email'],
                                            'title'=>$formData['title'],
                                            'first_name'=>$formData['first_name'], 
                                            'middle_name'=>$formData['middle_name'],
                                            'last_name'=>$formData['last_name'],  
                                            'mobile_country_code'=>$formData['mobile_country_code'],                            
                                            'mobile_number'=>$formData['mobile_number'],               
                                            'arn'=>$formData['arn'],
                                            'date_of_birth'=>$formData['date_of_birth'],
                                            'gender'=>$formData['gender'],
                                            'customer_mvc_type'=>$formData['customer_mvc_type'],
                                            'device_id' =>isset($formData['device_id'])?$formData['device_id']:'',                                
                                            'status'=>STATUS_ACTIVE,
                                            'date_created'=> date('Y-m-d H:i:s')
                                           ); 

                          $oldVals['mobile_number_old'] = isset($formData['mobile_number_old'])?$formData['mobile_number_old']:'';   
                          $oldVals['email_old'] = isset($formData['email_old'])?$formData['email_old']:''; 
                          $oldVals['chId'] = $chId;
                          $res = $chModel->addCardHolder( array('product_id'=>$formData['product_id'], 
                                                                 'chData'=>$chData, 
                                                                 'chDetails'=>$chDetails,
                                                                 'oldVals'=>$oldVals,
                                                                 'product_id'=>$formData['product_id']
                                                               ) );                 
          
                if($res!='success'){
                     $errorExists = true;
                     $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => $res,
                        )
                    );
                    $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-");
                    $this->view->formData = $formData;
                }
            
            
                if($res=='success'){
                           $this->_redirect($this->formatURL('/mvc_axis_cardholder/step2/'));
                } 
              }
            }
            }
          }
          else { 
                $errorExists = true; 
               }
        }
    }
    else
    { 
        $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => SETTING_API_ERROR_MSG,
                        )
                    );
        $errorExists = true;
    }
        
            if($chId>0){ // populating form values
                $row2 = $chModel->getCardHolderInfo($chId);                
              
                $row2Arr = Util::toArray($row2);
                $form->getElement("mobile_number_old")->setValue($row2Arr['mobile_number']);
                $form->getElement("email_old")->setValue($row2Arr['email']);
                $row2Arr['date_of_birth'] = Util::returnDateFormatted($row2Arr['date_of_birth'], "Y-m-d", "d-m-Y", "-");                              
                $form->populate($row2Arr);
                $this->view->chDetails = $row2;
            }
            
            $this->view->cardholder_auth = $session->cardholder_auth;
            $this->view->errorExists = $errorExists;
        }
        
  
    public function getForm(){
        return new Mvc_Axis_CardholderForm(array(
            'action' => $this->formatURL('/mvc_axis_cardholder/step1'),
            'method' => 'post',
            'name'=>'frmStep1',
            'id'=>'frmStep1'
        ));
    }
    
    public function getStep2Form(){
        return new Mvc_Axis_CardholderStep2Form(array(
            'action' => $this->formatURL('/mvc_axis_cardholder/step2'),
            'method' => 'post',
        ));
    }
    
    
    
    public function step2Action()
    {   
        $this->title = 'Enroll Cardholder - Step 2';
        $formData  = $this->_request->getPost();
        
        /* temporarily commented for revert
        if(isset($formData['btn_discard']) && $formData['btn_discard']!=''){
            $this->discardCardholder(); exit;
        }        
        else if(isset($formData['btn_back']) && $formData['btn_back']!=''){
            $this->_redirect('/mvc_axis_cardholder/step1/'); exit;
        }        
        else if(isset($formData['btn_next']) && $formData['btn_next']!=''){
            $this->_redirect('/mvc_axis_cardholder/step3/'); exit;
        }*/
        
        $session = new Zend_Session_Namespace('App.Agent.Controller');         
        $session->current_page = 'step2';
        $session->cardholder_step1 = 1;
        $chId = isset($session->cardholder_id)?$session->cardholder_id:0;        
        $cardholder_step2 = isset($session->cardholder_step2)?$session->cardholder_step2:0;  
        $state = new CityList();
        $errorExists = false;
         
        if($chId<1){
           $this->_helper->FlashMessenger(array( 'msg-error' => 'Please complete step1 process',));
           $this->_redirect($this->formatURL('/mvc_axis_cardholder/step1/')); 
        }
                 
        $request = $this->getRequest();
        $chModel  = new Mvc_Axis_CardholderUser();  
        $apisettingModel = new APISettings();
      
        $form = $this->getStep2Form();                       
        $this->view->form = $form;         
       
        $addch2 = isset($formData['addch2'])?$formData['addch2']:'';                             
       
        $moreData = array('date_created'=> date('Y-m-d H:i:s'),'cardholder_id'=>$session->cardholder_id);
        if($chkApi = $apisettingModel->checkAPIresponse()){
         if($addch2){
             
              if($form->isValid($this->getRequest()->getPost())){ 
                  $validateOffer=true;
                  
                   if($formData['shmart_rewards']==FLAG_YES){ 
                       
                       $validateOffer = $this->validateOffers($formData);
                         
                       if(!$validateOffer){
                           $errorExists = true;
                           $this->_helper->FlashMessenger(
                                                           array( 'msg-error' => 'Please select atleast one reward offer')
                                                         );
                       }                      
                    }                       
                 
                 $stateName =  $state->getStateName($formData['state']);
                 $data = array(                       
                                'address_line1'=>$formData['address_line1'],
                                'address_line2'=>$formData['address_line2'],
                                'country'=>$formData['country'],
                                'state'=>$stateName,
                                'city'=>$formData['city'],
                                'pincode'=>$formData['pincode'],
                                'alternate_contact_number'=>$formData['alternate_contact_number'],
                                'educational_qualifications'=>$formData['educational_qualifications'],
                                'mother_maiden_name'=>$formData['mother_maiden_name'],
                                'shmart_rewards'=>$formData['shmart_rewards'],
                                'family_members'=>$formData['family_members'],
                                'already_bank_account'=>$formData['already_bank_account'],
                                'vehicle_type'=>$formData['vehicle_type']
                               );
                  if($validateOffer){
                   
                  $res = $chModel->updateCardHolder(array_merge($moreData, $data));                   
                   
                   if($formData['shmart_rewards']==FLAG_YES && $validateOffer){  
                      $updOffers = $this->getOffersFilter(array_merge($moreData,$this->getRequest()->getPost()));
                      $chModel->updateOffers($updOffers);
                   }
                   
                   $this->_redirect($this->formatURL('/mvc_axis_cardholder/step3/'));                    
               }
            }  
            else { 
                    $errorExists = true; 
                 }
         }         
        
         if($chId>0 && $cardholder_step2==1){ // populating form values  
             
                $row2       = $chModel->getCardHolderInfo($chId); 
                $row2['state'] = $state->getStateCode($row2['state']); 
                $form->getElement('pin')->setValue($row2['pincode']);
                $chOffers   = $chModel->getCardHolderOffers($chId);
                $chDtl = Util::toArray($row2);
                $offersToShow=array();
                if($chDtl['shmart_rewards']==FLAG_YES && !empty($chOffers)){
                    if(!is_array($chOffers)){
                        $offersToShow = $chOffers->toArray();
                    } else {
                        $offersToShow = $chOffers;
                    }
                }else $offersToShow=array();
                
                
                $form->populate(array_merge($chDtl, $offersToShow));
                $this->view->chDetails = $row2;
            }else{
                $pinCode = isset($formData['pincode'])?$formData['pincode']:'';
                $form->getElement('pin')->setValue($pinCode);
                
            }   
          }
    else
    { 
        $errorExists = true; 
        $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => SETTING_API_ERROR_MSG,
                        )
                    );
    }
            
            $this->view->formData = $formData;     
            $this->view->errorExists = $errorExists;
       }   
       
       private function getOffersFilter($data){          
     
           $retArr['is_book'] = $data['is_book'];
           $retArr['is_travel'] = $data['is_travel'];
           $retArr['is_movies'] = $data['is_movies'];
           $retArr['is_shopping'] = $data['is_shopping'];
           $retArr['is_electronics'] = $data['is_electronics'];
           $retArr['is_music'] = $data['is_music'];
           $retArr['is_automobiles'] = $data['is_automobiles'];
           $retArr['cardholder_id'] = $data['cardholder_id'];
           $retArr['date_created'] = date('Y-m-d H:i:s');
           
          return $retArr;
       }
       
       
       Private function validateOffers($param){
          
        if(empty($param))
            return false;
           
        if($param['is_book']!=FLAG_YES && $param['is_travel']!=FLAG_YES && $param['is_movies']!=FLAG_YES && $param['is_shopping']!=FLAG_YES && $param['is_electronics']!=FLAG_YES && $param['is_music']!=FLAG_YES && $param['is_automobiles']!=FLAG_YES)
            return false;
        else 
            return true;   
       }
       
        public function step3Action(){
            $this->title = 'Enroll Cardholder - Step 3';
            $formData  = $this->_request->getPost();
            
            /* temporarily commented for revert
            if(isset($formData['btn_discard']) && $formData['btn_discard']!=''){
               $this->discardCardholder(); exit;
            }
            else if(isset($formData['btn_back']) && $formData['btn_back']!=''){
                    $this->_redirect('/mvc_axis_cardholder/step2/'); exit;
            }
            else if(isset($formData['btn_next']) && $formData['btn_next']!=''){
                    $this->_redirect('/mvc_axis_loadbalance/index/'); exit;
            }*/
        
            $session = new Zend_Session_Namespace('App.Agent.Controller');         
            $chId = isset($session->cardholder_id)?$session->cardholder_id:0;
            $cardholder_step2 = isset($session->cardholder_step2)?$session->cardholder_step2:0;
            $chModel  = new Mvc_Axis_CardholderUser();
            $chInfo = $chModel->getCardHolderInfo($chId);
            $user = Zend_Auth::getInstance()->getIdentity(); 
            $errorExists = false;
            $apisettingModel = new APISettings();
            if($chId<1){
                $this->_helper->FlashMessenger(array( 'msg-error' => 'Please complete step1 process',));
                $this->_redirect($this->formatURL('/mvc_axis_cardholder/step1/')); 
            }
            else if($cardholder_step2<1){
                $this->_helper->FlashMessenger(array( 'msg-error' => 'Please complete step2 process',));
                $this->_redirect($this->formatURL('/mvc_axis_cardholder/step2/')); 
            }

            $request = $this->getRequest();         
            $btnTC = isset($formData['btnTC'])?$formData['btnTC']:'';  
            $form = $this->getStep3Form(); 
      
            $form->populate(Util::toArray($chInfo));
            $this->view->chDetails = $chInfo;
              if($chkApi = $apisettingModel->checkAPIresponse()){
                if($btnTC){
                  if (isset($session->termscondition_auth)){
                    $termsAuth = $session->termscondition_auth;
                }
                else {
                    $termsAuth = Alerts::generateAuthCode();
                }
                    try{
                        $m = new App\Messaging\MVC\Axis\Agent();
                        $userData = array('mobile_country_code'=>$chInfo['mobile_country_code'], 
                                          'mobile1'=>$chInfo['mobile_number'],
                                          'cardholder_name'=>$chInfo['first_name'].' '.$chInfo['last_name'],
                                          'termsconditions_auth' => $termsAuth,
                                          'email' => $chInfo['email'],
                                          'mailSubject' => 'Terms and Condition Authorization Code',
                                          'product_name' => AXIS_BANK_SHMART_CARD
                                          );              
                        $resp = $m->termsconditionsAuth($userData);
                        
                        $form->populate($formData);       
                        $session = new Zend_Session_Namespace('App.Agent.Controller');                        
                        
                        $param['cardholder_id'] = $chId;
                        $param['email'] = $chInfo['email'];
                        $param['datetime_send'] = date('Y-m-d H:i:s');
                        $param['activation_status'] = STATUS_PENDING;
                        
                        $chModel->updateEmailVerification($param);                        
                        
                        $this->_helper->FlashMessenger( array('msg-success' => 'Terms and Conditions Authorization code has been sent on your mobile and email',) );                        

                }catch (Exception $e ) { 
                        $errorExists = true; 
                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                        $form->populate($formData);
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    }  
                }
            
                   $this->view->termscondition_auth = $session->termscondition_auth;
            
             
          }
    else
    { 
        $errorExists = true; 
        $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => SETTING_API_ERROR_MSG,
                        )
                    );
    }
                                
            $this->view->form = $form;             
                               
            $addch3 = isset($formData['addch3'])?$formData['addch3']:'';           

             if($addch3){
                
                $tcAuthValidated = isset($session->termsconditions_validated)?$session->termsconditions_validated:'0';            
                if($tcAuthValidated!=1 && ($session->termscondition_auth != $formData['terms_conditions_authcode']) || ($session->termscondition_auth=='' && $formData['terms_conditions_authcode']=='')){ // matching the auth code
                     $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Terms & Conditions Authorization Code',) );
                     $errorExists = true; 
                     $this->view->formData = $formData;
                     $form->populate($formData);
                 
                } 
                else if($form->isValid($this->getRequest()->getPost())){
                    
                        $session->termsconditions_validated = 1;
                        $data = array('products_acknowledgement'=>$formData['products_acknowledgement'],
                                      'rewards_acknowledgement'=>$formData['rewards_acknowledgement'],
                                      'cardholder_id'=>$session->cardholder_id
                                     );
                        
                        $res = $chModel->updateCardHolder($data);
                      
                        $objECS = new ECS();
                        if(!$session->crn_assigned){
                        try{
                            $resp = $objECS->assignCRN($chId); // assigning the crn to cardholder
                            $session->crn_assigned = 1;
                        } catch (Exception $e ) { 
                                                  $errorExists = true; 
                                                  $this->_helper->FlashMessenger(array( 'msg-error' => $e->getMessage(),));
                                                  App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                                  
                                                }
                                               
                        }
                         $initiateData = array('agent_id'=>$user->id, 'cardholder_id'=>$chId);
                         $this->_redirect($this->formatURL('/mvc_axis_cardholder/ecsregistration/'));  
                        
                }   
                else {
                        $errorExists = true; 
                     }
             }
            $form->setDefaults(array('terms_conditions_text' => (Util::getCardholderTC())));
            $this->view->errorExists = $errorExists;
       }
       
       
       public function getStep3Form(){
        $form = new Mvc_Axis_CardholderStep3Form(array(
            'action' => $this->formatURL('/mvc_axis_cardholder/step3'),
            'method' => 'post',
        ));
       
        return $form;
    }   
    
    public function ecsregistrationAction()
    {    
         $this->title = 'Enroll Cardholder - ECS Registration';
         $session = new Zend_Session_Namespace('App.Agent.Controller');  
         $chModel  = new Mvc_Axis_CardholderUser();
         $apisettingModel = new APISettings();
         $validator = new Validator();
         $chId = isset($session->cardholder_id)?$session->cardholder_id:0;
         $chInfo = $chModel->getCardHolderInfo($chId);
            $paramArray = $validator->validCardholderData($chInfo);
           if($chkApi = $apisettingModel->checkAPIresponse()){
            try{
            $cardholderArray = $paramArray;
            $ecsApi = new App_Api_ECS_Transactions();
           
            $resp = $ecsApi->cardholderRegistration($cardholderArray);

            App_Logger::log('Response : '.$resp, Zend_Log::DEBUG);
            App_Logger::log(serialize($cardholderArray), Zend_Log::DEBUG);
            $ecsArr = $ecsApi->getLastResponse();
            
            if($resp == true) {
               
               $session->ecs_registeration = 1;
               
               $callRefNo = isset($ecsArr->callRefNo)?$ecsArr->callRefNo:'';
               $chModel->updateCardholderDetail(array('ecs_ref_no' => $callRefNo), $chId);
               $session->cardholder_step3=  1;
               $this->_redirect($this->formatURL('/mvc_axis_loadbalance/index'));  
            } else {
                
                if($resp!=true && $resp!=false){
                    $this->_helper->FlashMessenger(array('msg-error' => 'We are experiencing some technical problem, please try later.'));
                    $this->_redirect($this->formatURL('/mvc_axis_cardholder/step3/')); exit;
                } 
                
                unset($session->ecs_registeration);
                $objTxnMsg = new TxnMessage();
                 $errorCode = isset($ecsArr->errorCode)?$ecsArr->errorCode:'';
                 $errorMsg = $ecsApi->getError();
                
                if($errorCode == '' && $errorMsg != '') {
                    
                  $txnMsg = $errorMsg . ' ';
                } else {
                        $txnMsg = $objTxnMsg->getMessage(array('error_group'=>TXNTYPE_CARDHOLDER_REGISTRATION, 'error_code'=>$ecsArr->errorCode));
                        
                        if($txnMsg=='')
                           $txnMsg = $errorMsg;
                }
               
                
                $msg = '. Please try again!';
                $this->_helper->FlashMessenger(array('msg-error' => $txnMsg.$msg,));
                $this->_redirect($this->formatURL('/mvc_axis_cardholder/step3/')); 
              }
            } 
            catch( Exception $e ) {
                App_Logger::log(serialize($e) , Zend_Log::ERR);
                $resp = false;
                $error = $e->getMessage();
                $this->_helper->FlashMessenger(array( 'msg-error' => $e->getMessage(),));
            }
                }
    else
    { 
        $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => SETTING_API_ERROR_MSG,
                        )
                    );
    }
        
         
    }
    
     public function completeAction()
     { 
         $this->title = 'Enroll Cardholder - Complete';
        $session = new Zend_Session_Namespace('App.Agent.Controller');
        
          
         unset($session->cardholder_id); 
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
            $this->_helper->FlashMessenger( array('msg-success' =>'Customer Registration has been discarded successfully',) ); 
            $this->_redirect($this->formatURL('/mvc_axis_cardholder/step1/')); 
                    
     }
       
}