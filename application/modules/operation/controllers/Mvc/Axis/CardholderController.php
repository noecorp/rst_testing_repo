<?php
/**
 * MVC Axis Bank Cardholder
 *
 * @package frontend_controllers
 * @copyright company
 */

class Mvc_Axis_CardholderController extends App_Operation_Controller
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

        //echo "<pre>";print_r($session);
        $this->title = 'Card Holders';
        
        $chModel  = new Mvc_Axis_CardholderUser();         
        $agent = Zend_Auth::getInstance()->getIdentity();
        $ag_id = $agent->id;
        $form = new Mvc_Axis_CardholderSearchForm();
        $data['searchCriteria'] = $this->_getParam('searchCriteria');
        $data['keyword'] = $this->_getParam('keyword');
        $data['sub'] = $this->_getParam('sub');
        if ($data['sub'] != '') {
               
               //$formData  = $this->_request->getPost();
        $this->view->paginator = $chModel->getCardholderList($data,$id=0,$this->_getPage());
         $form->populate($data);  
            }
       $this->view->backLink = 'searchCriteria='.$data['searchCriteria'].'&keyword='.$data['keyword'].'&sub=1';
       $this->view->form = $form;
    } 
    
    public function blockAction(){
         $user = Zend_Auth::getInstance()->getIdentity();
         $form = new Mvc_Axis_DeactivateForm();
         $this->title = 'Block Card Holder';
         $chModel  = new Mvc_Axis_CardholderUser();
         $id = $this->_getParam('id');
         $objLogStatus = new LogStatus();
         $dataArr['searchCriteria'] = $this->_getParam('searchCriteria');
         $dataArr['keyword'] = $this->_getParam('keyword');
         $dataArr['sub'] = $this->_getParam('sub');
        
          if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
         $formData  = $this->_request->getPost();
         
        $data = array('cardholder_id' => $id,'by_ops_id' => $user->id, 
                    'status_old' => STATUS_UNBLOCKED,'status_new' => STATUS_BLOCKED,'remarks' => $formData['remarks']
                     );
         
          try{
                                  $res = $chModel->changeStatus($id,STATUS_BLOCKED);              
                                  $reslog =  $objLogStatus->log($data);
                                }
                                 catch (Exception $e) {
                                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                 }
             //var_dump($res);exit;
         if($res)
         {
             
            $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => sprintf('Cardholder has been blocked'),
                    )
                ); 
            $this->_redirect($this->formatURL('/mvc_axis_cardholder/index?searchCriteria='.$dataArr['searchCriteria'].'&keyword='.$dataArr['keyword'].'&sub=1'));
         }
         else {
             $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => sprintf('Cardholder could not be blocked'),
                    )
                );
             $this->_redirect($this->formatURL('/mvc_axis_cardholder/index?searchCriteria='.$dataArr['searchCriteria'].'&keyword='.$dataArr['keyword'].'&sub=1'));
           }
         
            }
          }
          else{
            
            $row = $chModel->detailsById($id);
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => sprintf('We cannot find Cardholder with id %s', $id),
                    )
                );
                $this->_redirect($this->formatURL('/mvc_axis_cardholder/index?searchCriteria='.$dataArr['searchCriteria'].'&keyword='.$dataArr['keyword'].'&sub=1'));
            }
          
           $this->view->item = (object) $row;    
        }
        
        $this->view->form = $form;
        $this->view->queryString = $this->formatURL('/mvc_axis_cardholder/index?searchCriteria='.$dataArr['searchCriteria'].'&keyword='.$dataArr['keyword'].'&sub=1');
        
    }
    
     public function unblockAction(){
         $user = Zend_Auth::getInstance()->getIdentity();
         $form = new Mvc_Axis_ActivateForm();
         $this->title = 'Unblock Card Holder';
         $chModel  = new Mvc_Axis_CardholderUser();
         $id = $this->_getParam('id');
         $objLogStatus = new LogStatus();
         $dataArr['searchCriteria'] = $this->_getParam('searchCriteria');
         $dataArr['keyword'] = $this->_getParam('keyword');
         $dataArr['sub'] = $this->_getParam('sub');
          if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
         $formData  = $this->_request->getPost();
         
         $data = array('cardholder_id' => $id,'by_ops_id' => $user->id, 
                    'status_old' => STATUS_BLOCKED,'status_new' => STATUS_UNBLOCKED,'remarks' => $formData['remarks']
                   );
          
          try{
                                  $res = $chModel->changeStatus($id,STATUS_UNBLOCKED);
                                  $reslog =  $objLogStatus->log($data);
                                }
                                 catch (Exception $e) {
                                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                 }
             //var_dump($res);exit;
         
         if($res)
         {
            $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => sprintf('Cardholder has been Unblocked'),
                    )
                ); 
            $this->_redirect($this->formatURL('/mvc_axis_cardholder/index?searchCriteria='.$dataArr['searchCriteria'].'&keyword='.$dataArr['keyword'].'&sub=1'));
         }
         else {
             $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => sprintf('Cardholder could not be Unblocked'),
                    )
                );
             $this->_redirect($this->formatURL('/mvc_axis_cardholder/index?searchCriteria='.$dataArr['searchCriteria'].'&keyword='.$dataArr['keyword'].'&sub=1'));
           }
         
            }
          }
          else{
            
            $row = $chModel->detailsById($id);
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => sprintf('We cannot find Cardholder with id %s', $id),
                    )
                );
                $this->_redirect($this->formatURL('/mvc_axis_cardholder/index?searchCriteria='.$dataArr['searchCriteria'].'&keyword='.$dataArr['keyword'].'&sub=1'));
            }
            
          $this->view->item = (object) $row;  
        }
        
        $this->view->form = $form;
        $this->view->queryString = $this->formatURL('/mvc_axis_cardholder/index?searchCriteria='.$dataArr['searchCriteria'].'&keyword='.$dataArr['keyword'].'&sub=1');
        
    }
    
     public function viewAction() {
       $this->title = 'Cardholder details'; 
       $cardholderModel = new Mvc_Axis_CardholderUser();
       
       $id = $this->_getParam('id');
       $row = $cardholderModel->finddetailsById($id);
       $row->father_first_name = $row->father_first_name.' '.$row->father_middle_name.' '.$row->father_last_name;
       $row->spouse_first_name = $row->spouse_first_name.' '.$row->spouse_last_name.' '.$row->spouse_last_name;
       $row->date_of_birth = Util::returnDateFormatted($row->date_of_birth, "d-m-Y", "Y-m-d", "-");
       $row->gender = ucfirst($row->gender);
       $row->title= ucfirst($row->title);
       $row->customer_mvc_type = ucfirst($row->customer_mvc_type);
       $row->educational_qualifications = ucfirst($row->educational_qualifications);
       $row->shmart_rewards = ucfirst($row->shmart_rewards);
       $row->already_bank_account = ucfirst($row->already_bank_account);
       $row->vehicle_type = ucfirst($row->vehicle_type);
       //$row->registration_type = ucfirst($row->registration_type);
       $row->status = ucfirst($row->status);
       $row->products_acknowledgement = ucfirst($row->products_acknowledgement);
       $row->rewards_acknowledgement = ucfirst($row->rewards_acknowledgement);
       $row->date_created = Util::returnDateFormatted($row->date_created, "d-m-Y", "Y-m-d", "-");
//       $row->date_modified = Util::returnDateFormatted($row->date_modified, "d-m-Y", "Y-m-d", "-");
//       $row->date_activated = Util::returnDateFormatted($row->date_activated, "d-m-Y", "Y-m-d", "-");
       
       //preparing data to show in required format
       $row->crn;
       $this->view->detailsarray = $row;
       $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/mvc_axis_cardholder/';
       $this->view->item = $row;
       $this->view->cardholderStatus = $row->Cstatus;
        //$this->view->data = $row->toArray();
       $this->view->opsName = ucfirst($row->firstname). " ".ucfirst($row->lastname);
       
        
        
        
    }
    
    /* step1Action is responsible for handling step1 details updating of cardholder       
     */
    
    public function step1Action()
    {   
        $this->title = 'Edit Cardholder details'; 
        $user = Zend_Auth::getInstance()->getIdentity();        
        $session = new Zend_Session_Namespace('App.Operation.Controller'); 
        $session->current_page = 'step1';
        $m = new App\Messaging\MVC\Axis\Operation();
        //$chId = isset($session->cardholder_id)?$session->cardholder_id:0;
        
        $chId =  Util::getDecrypt($this->_getParam('ch'));
     
        if(!is_numeric($chId)){
            $this->_helper->FlashMessenger( array('msg-error' => 'Invalid cardholder id',) );
            
        } else {
           
        $chModel = new Mvc_Axis_CardholderUser();
        $products  = new Products();      
        $chDtls = $chModel->getCardHolderInfo($chId);      
        
        
        $chDtlsArr = Util::toArray($chDtls); 
       
           
        $chProd = $chModel->getCardHolderProducts($chDtls->id); //$products->getCardholderProducts(array('cardholder_id'=>$chId, 'status'=>'active'));
       
        if(!empty($chProd)){
           $chDtlsArr['product_id'] = $chProd->product_id;
        }
           
        $agentId = $chDtlsArr['reg_agent_id'];      
        $config = App_DI_Container::get('ConfigObject');
        
        $minAge = $config->cardholder->age->min;
        $maxAge = $config->cardholder->age->max;
        $currDate = date('Y-m-d');        
        $request = $this->getRequest();         
             
        // Get our form and validate it
        $form = $this->getForm($chId);                       
        $this->view->form = $form;         
        $formData  = $this->_request->getPost();
        $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "d-m-Y", "Y-m-d", "-");
        $btnAuth = isset($formData['send_auth_code'])?$formData['send_auth_code']:'0';         
        $btnAdd = isset($formData['btn_add'])?$formData['btn_add']:'';
        $productList = $products->getAgentProducts($agentId);       
        $form->getElement("product_id")->setMultiOptions($productList);
      
        // sending authrization code on mobile
        if($btnAuth==1){           
            try{
                $m = new App\Messaging\MVC\Axis\Operation();
                $userData = array('mobile_country_code'=>$formData['mobile_country_code'], 
                                  'mobile1'=>$formData['mobile_number'],
                                  'module'=>'operation',
                                  'mobile_number_old'=>$formData['mobile_number_old'],
                                  'product_name' => AXIS_BANK_SHMART_CARD);                               
                
                $resp = $m->cardholderAuth($userData);
                $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-"); 
                $form->populate($formData);
                $this->view->cardholder_auth = $session->cardholder_auth;                       
                
                $this->_helper->FlashMessenger( array('msg-success' => 'Authorization code has been sent on your mobile number.',) );
                $form->getElement("send_auth_code")->setValue("0");
                //$session->cardholder_auth = 1;
                
        }catch (Exception $e ) {  
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                $form->populate($formData);
            }  
        }
        
        
        // adding details in db
        if($btnAdd){
                
                // cardholder date check
                $datetime1 = date_create($currDate);
                $datetime2 = date_create($formData['date_of_birth']);                
                $interval = date_diff($datetime1, $datetime2);
                $age = $interval->format('%y');              

                if ($age < $minAge){
                    $this->_helper->FlashMessenger(array( 'msg-error' => 'Minimum age should be 18 years.', ));
                    $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-");
                    $this->view->formData = $formData;
                    $form->populate($formData);
                }
               /* else if($age > $maxAge) {
                    $this->_helper->FlashMessenger(array('msg-error' => 'You need to be below 60 years age to enroll.',));
                    $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-");
                    $this->view->formData = $formData;
                    $form->populate($formData);
                }*/ else if($formData['customer_mvc_type']=='mvcc' && trim($formData['device_id'])==''){
                       $this->_helper->FlashMessenger(array('msg-error' => 'Device Id is mandatory field.',));
                       $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-");
                       $this->view->formData = $formData;
                       $form->populate($formData);
                }              
               
                else{
                    
                    
                    // check for available bal of agent for selected product
                   /* $objVAB = new Validator_AgentBalance(); 
                    $objAF = new Agentfee();
                    $agentFee = $objAF->getAgentFeeDetails(array('agent_id'=>$agentId, 'product_id'=>$formData['product_id']));              

                    $isSufficientBal = $objVAB->isSufficientAgentBalance(array('agent_id'=>$agentId, 'amount'=>$agentFee['limit_first_load']));
                    if(!$isSufficientBal){
                        $this->_helper->FlashMessenger( 
                                         array('msg-error' => 'Insufficient Balance in your account for that product',)
                                        );
                    }           
     */      
            
            if($form->isValid($this->getRequest()->getPost())){
                
                $authValidated = isset($session->validated_cardholder_auth)?$session->validated_cardholder_auth:'0';
                
                //echo $authValidated.'----'.$session->cardholder_auth.'===='.$formData['auth_code'].'==='.$formData['mobile_number_old'].'==='.$formData['mobile_number'];
                //die;
                
                     //$this->view
                if($authValidated!=1 && $session->cardholder_auth != $formData['auth_code'] && $formData['mobile_number_old']!= $formData['mobile_number']){ // matching the auth code
                     //$this->view->msg = $res;
                     $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'Invalid Authorization Code',
                        )
                    );
                    $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-"); 
                    $this->view->formData = $formData;
                } else {                        
           
            $session->validated_cardholder_auth = 1;
            $chData = array(
                            'reg_datetime'=>date('Y-m-d H:i:s'),
                            'email'=>$formData['email'],
                            'title'=>$formData['title'],
                            'first_name'=>$formData['first_name'], 
                            'middle_name'=>$formData['middle_name'],
                            'last_name'=>$formData['last_name'],  
                            'mobile_country_code'=>$formData['mobile_country_code'],                            
                            'mobile_number'=>$formData['mobile_number'],                                                       
                            'enroll_status'=>STATUS_APPROVED,
                            'status'=>STATUS_UNBLOCKED                                          
                           ); 
             $deviceId = isset($formData['device_id'])?$formData['device_id']:'';
             $chDetails = array('by_ops_id'=>$user->id,
                                'cardholder_id'=>$chId,
                                'email'=>$formData['email'],
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
                                'device_id' =>$deviceId,                                
                                'status'=>STATUS_ACTIVE,
                                'date_created'=> new Zend_Db_Expr('NOW()') ,                 
                                'address_line1'=>$chDtlsArr['address_line1'],
                                'crn'=>$chDtlsArr['crn'],
                                'address_line2'=>$chDtlsArr['address_line2'],
                                'country'=>$chDtlsArr['country'],
                                'state'=>$chDtlsArr['state'],
                                'city'=>$chDtlsArr['city'],
                                'pincode'=>$chDtlsArr['pincode'],
                                'alternate_contact_number'=>$chDtlsArr['alternate_contact_number'],
                                'educational_qualifications'=>$chDtlsArr['educational_qualifications'],
                                'mother_maiden_name'=>$chDtlsArr['mother_maiden_name'],
                                'shmart_rewards'=>$chDtlsArr['shmart_rewards'],
                                'family_members'=>$chDtlsArr['family_members'],
                                'already_bank_account'=>$chDtlsArr['already_bank_account'],
                                'vehicle_type'=>$chDtlsArr['vehicle_type'],
                                'products_acknowledgement'=>$chDtlsArr['products_acknowledgement'],
                                'rewards_acknowledgement'=>$chDtlsArr['rewards_acknowledgement'],
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
                    //$this->view->msg = $res;
                     $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => $res,
                        )
                    );
                    $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-");
                    $this->view->formData = $formData;
                }
            
            
                if($res=='success'){ 
                        
                        // updating mvc type and device id in t_cardholders_mvc
                        $chMVCInfo = array('mvc_type'=>$formData['customer_mvc_type'], 'device_id'=>$deviceId);
                        $objMVC = new Mvc_Axis_CardholderUser();
                        $objMVC->updateCardholderMVCDetails($chMVCInfo, $chId);
                        
                        // updating email verification in t_email_verification table
                        $param['cardholder_id'] = $chId;
                        $param['email'] = $formData['email'];
                        $param['datetime_send'] = date('Y-m-d H:i:s');
                        $param['activation_status'] = STATUS_PENDING;
                                              
                        $chModel->updateEmailVerification($param);    
                        
                        // email sending to cardholder for changes in his db.
                        $emailData = array('email'=>$formData['email'],'updation_date'=>date('d-m-Y H:i:s'), 'cardholder_name'=>$formData['first_name'].' '.$formData['middle_name'].' '.$formData['last_name']);
                        $m->cardholderUpdationEmail($emailData);
                        
                        $this->_redirect($this->formatURL('/mvc_axis_cardholder/step2?ch='.Util::getEncrypt($chId)));
                           
                } 
              }
            } 
          } // date check closes here
        }
        
            if($chId>0){ // populating form values
                $form->getElement("mobile_number_old")->setValue($chDtlsArr['mobile_number']);
                $form->getElement("email_old")->setValue($chDtlsArr['email']);                                
            }
            
            $this->view->cardholder_auth = $session->cardholder_auth;
            //echo $session->cardholder_auth;
            $chDtlsArr['date_of_birth'] = Util::returnDateFormatted($chDtlsArr['date_of_birth'], "d-m-Y", "Y-m-d", "-");
            if($formData['date_of_birth']!='')
                $chDtlsArr['mobile_number'] = $formData['mobile_number'];
            
            $form->populate($chDtlsArr);
            
            $this->view->chDetails = $chDtls;
          }
          
          //echo $session->cardholder_auth;
        }
        
        
        private function checkAgentLoadLimit($param){ 
            //throw new Exception('Insufficient Balance for Minimum Load Limit.');            
        }
    
    public function getForm($chId){
        
        $chId = Util::getEncrypt($chId);
        return new Mvc_Axis_CardholderForm(array(
            'action' => $this->formatURL('/mvc_axis_cardholder/step1?ch='.$chId),
            'method' => 'POST',
            'name'=>'frmStep1',
            'id'=>'frmStep1'
        ));
    }
    
    public function getStep2Form($chId){
         $chId = Util::getEncrypt($chId);
        return new Mvc_Axis_CardholderStep2Form(array(
            'action' => $this->formatURL('/mvc_axis_cardholder/step2?ch='.$chId),
            'method' => 'POST',
        ));
    }
    
    
    
    public function step2Action()
    {   
        $this->title = 'Edit Cardholder details'; 
        $session = new Zend_Session_Namespace('App.Agent.Controller');      
        $user = Zend_Auth::getInstance()->getIdentity();  
        $session->current_page = 'step2';
        $session->cardholder_step1 = 1;
        $chModel  = new Mvc_Axis_CardholderUser();
        $state = new CityList();       
        $chId =  Util::getDecrypt($this->_getParam('ch'));
        $cardholder_step2 = isset($session->cardholder_step2)?$session->cardholder_step2:0;  
        $row2  = $chModel->getCardHolderInfo($chId, STATUS_ACTIVE);
        $row2['state'] = $state->getStateCode($row2['state']);
        //echo '<pre>';print_r($row2);exit;
        $chDtl = Util::toArray($row2);
        $m = new App\Messaging\MVC\Axis\Operation();
//        echo '<pre>';
//        print_r($chDtl);
//        die;
        $request = $this->getRequest();
        
        // Get our form and validate it
        $form = $this->getStep2Form($chId);                       
        $this->view->form = $form; 
        $this->view->cardholder_id = $chId; 
        $formData  = $this->_request->getPost();//$form->getValues();// 
       
        $addch2 = isset($formData['addch2'])?$formData['addch2']:'';                             
       
        $moreData = array('date_created'=> date('Y-m-d H:i:s'),'cardholder_id'=>$chId);
         
        if(!is_numeric($chId)){
            $this->_helper->FlashMessenger( array('msg-error' => 'Invalid cardholder id',) );
            
        } else {
            
        
         if($addch2){
              //echo 'dfasdf';die;
             
              if($form->isValid($this->getRequest()->getPost())){ 
                  $validateOffer=true;
                  
                   if($formData['shmart_rewards']==FLAG_YES){ 
                       
                       $validateOffer = $this->validateOffers($formData);
                         
                       if(!$validateOffer){
                           $this->_helper->FlashMessenger(
                                                           array( 'msg-error' => 'Please select atleast one reward offer.')
                                                         );
                       }                      
                    }                       
                  $stateName =  $state->getStateName($formData['state']);
                  $data = array(  
                                'email'=>$chDtl['email'],
                                'crn'=>$chDtl['crn'],
                                'title'=>$chDtl['title'],
                                'first_name'=>$chDtl['first_name'], 
                                'middle_name'=>$chDtl['middle_name'],
                                'last_name'=>$chDtl['last_name'],  
                                'mobile_country_code'=>$chDtl['mobile_country_code'],                            
                                'mobile_number'=>$chDtl['mobile_number'],               
                                'arn'=>$chDtl['arn'],
                                'date_of_birth'=>$chDtl['date_of_birth'],
                                'gender'=>$chDtl['gender'],
                                'customer_mvc_type'=>$chDtl['customer_mvc_type'],
                                'device_id' =>$chDtl['device_id'],                                
                                'status'=>STATUS_ACTIVE,                                
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
                                'vehicle_type'=>$formData['vehicle_type'],
                                'by_ops_id'=>$user->id,
                                'products_acknowledgement'=>$chDtl['products_acknowledgement'],
                                'rewards_acknowledgement'=>$chDtl['rewards_acknowledgement'],
                               );
                  
                  if($validateOffer){
                      
                     
                      
                      
                  $res = $chModel->updateCardHolder(array_merge($moreData, $data));                   
                   
                   if($formData['shmart_rewards']==FLAG_YES && $validateOffer){  
                      $updOffers = $this->getOffersFilter(array_merge($moreData,$this->getRequest()->getPost()));
                      $newOffers['shmart_rewards_old'] = $formData['shmart_rewards_old'];
                      $newOffers['param'] = $updOffers;
                      $newOffers['shmart_rewards'] = $formData['shmart_rewards'];
                      
                      $chModel->updateOffersOps($newOffers);
                   }
                   
                   // email sending to cardholder for changes in his db.
                        $emailData = array('email'=>$chDtl['email'],'updation_date'=>date('d-m-Y H:i:s'), 'cardholder_name'=>$chDtl['first_name'].' '.$chDtl['middle_name'].' '.$chDtl['last_name']);
                        $m->cardholderUpdationEmail($emailData);
                   
                   $this->_redirect($this->formatURL('/mvc_axis_cardholder/step3?ch='.Util::getEncrypt($chId)));
               }
            }    
         }         
        
         if($chId>0){ // populating form values            
                  
                $chOffers   = $chModel->getCardHolderOffers($chId);
              
                $offersToShow=array();
                $form->getElement("shmart_rewards_old")->setValue($chDtl['shmart_rewards']);
                $form->getElement('pin')->setValue($row2['pincode']);
                if($chDtl['shmart_rewards']==FLAG_YES){
                   $offersToShow = $chOffers->toArray();
                }else $offersToShow=array();
                
                
                $form->populate(array_merge($chDtl, $offersToShow));
                 //echo '<pre>';print_r($row2);exit;
                $this->view->chDetails = $row2;
            }   
            
            $this->view->formData = $formData;  
            
        }        
      }   
       
       // That function will convert offeres array to db storeable fields name
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
            $this->title = 'Edit Cardholder details'; 
            $session = new Zend_Session_Namespace('App.Agent.Controller');         
            $chId =  Util::getDecrypt($this->_getParam('ch'));
            $cardholder_step2 = isset($session->cardholder_step2)?$session->cardholder_step2:0;
            $chModel  = new Mvc_Axis_CardholderUser();
            $m = new App\Messaging\MVC\Axis\Operation();
            $chInfo = $chModel->getCardHolderInfo($chId);
            $user = Zend_Auth::getInstance()->getIdentity();              
            
            $request = $this->getRequest();            
            $formData  = $this->_request->getPost();
            $form = $this->getStep3Form($chId); 
            $form->populate(Util::toArray($chInfo));
            $this->view->chDetails = $chInfo;
            
            if(!is_numeric($chId)){
            $this->_helper->FlashMessenger( array('msg-error' => 'Invalid cardholder id',) );
            
            } else {           
             
            
            // Get our form and validate it
                                
            $this->view->form = $form;             
                              
            $addch3 = isset($formData['addch3'])?$formData['addch3']:'';           

             if($addch3){
                
                 if($form->isValid($this->getRequest()->getPost())){                    
                        
                        $data = array(  'products_acknowledgement'=>$formData['products_acknowledgement'],
                                        'rewards_acknowledgement'=>$formData['rewards_acknowledgement'],
                                        'cardholder_id'=>$chId,
                                        'by_ops_id'=>$user->id,
                                        'crn'=>$chInfo['crn'],
                                        'email'=>$chInfo['email'],
                                        'title'=>$chInfo['title'],
                                        'first_name'=>$chInfo['first_name'], 
                                        'middle_name'=>$chInfo['middle_name'],
                                        'last_name'=>$chInfo['last_name'],  
                                        'mobile_country_code'=>$chInfo['mobile_country_code'],                            
                                        'mobile_number'=>$chInfo['mobile_number'],               
                                        'arn'=>$chInfo['arn'],
                                        'date_of_birth'=>$chInfo['date_of_birth'],
                                        'gender'=>$chInfo['gender'],
                                        'customer_mvc_type'=>$chInfo['customer_mvc_type'],
                                        'device_id' =>$chInfo['device_id'],                                
                                        'status'=>STATUS_ACTIVE,                                
                                        'address_line1'=>$chInfo['address_line1'],
                                        'address_line2'=>$chInfo['address_line2'],
                                        'country'=>$chInfo['country'],
                                        'state'=>$chInfo['state'],
                                        'city'=>$chInfo['city'],
                                        'pincode'=>$chInfo['pincode'],
                                        'alternate_contact_number'=>$chInfo['alternate_contact_number'],
                                        'educational_qualifications'=>$chInfo['educational_qualifications'],
                                        'mother_maiden_name'=>$chInfo['mother_maiden_name'],
                                        'shmart_rewards'=>$chInfo['shmart_rewards'],
                                        'family_members'=>$chInfo['family_members'],
                                        'already_bank_account'=>$chInfo['already_bank_account'],
                                        'vehicle_type'=>$chInfo['vehicle_type'],
                                        'by_ops_id'=>$user->id,
                                        'date_created'=>$chInfo['date_created']
                                     );
                       
                        try{
                            $res = $chModel->updateCardHolder($data);
                        }catch(Exception $e ) { 
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                            $form->populate($formData);
                        }  
                        
                        // email sending to cardholder for changes in his db.
                        $emailData = array('email'=>$chInfo['email'],'updation_date'=>date('d-m-Y H:i:s'), 'cardholder_name'=>$chInfo['first_name'].' '.$chInfo['middle_name'].' '.$chInfo['last_name']);
                        $m->cardholderUpdationEmail($emailData);

                        $this->_redirect($this->formatURL('/mvc_axis_cardholder/complete/'));
                }    
             }            
          }
       }
       
       
       public function getStep3Form($chId){
        $chId = Util::getEncrypt($chId);
        return new Mvc_Axis_CardholderStep3Form(array(
            'action' => $this->formatURL('/mvc_axis_cardholder/step3?ch='.$chId),
            'method' => 'POST',
        ));
    }   
    
    
    
     public function completeAction()
     { 
        $session = new Zend_Session_Namespace('App.Operation.Controller');                  
        unset($session->cardholder_id); 
        unset($session->cardholder_step2);
        unset($session->cardholder_step3);
        unset($session->cardholder_product_id);
        unset($session->termsconditions_validated);
        unset($session->termscondition_auth);
        unset($session->cardholder_auth);
        unset($session->validated_cardholder_auth);
        
        $this->_helper->FlashMessenger(
            array(
                    'msg-success' => 'Cardholder edited successfully.',
                )
            );        
        
        $this->_redirect($this->formatURL('/mvc_axis_cardholder/index/'));
     } 
     
     
           
     
}