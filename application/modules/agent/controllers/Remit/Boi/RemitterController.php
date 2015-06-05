<?php

/**
 * That RemitterController is responsible for all remit operations at partner portal.
 */

class Remit_Boi_RemitterController extends App_Agent_Controller
{

    public function init()
    {
        parent::init();
        //$this->session = new Zend_Session_Namespace("App.Agent.Controller");
    }    
    
     /* adddetailsAction is responsible for handling details of remitter        
     */
    
    public function adddetailsAction()
    {      
         $this->title = 'Add Remitter Details';
         
         //unset($this->session->remitter_auth); 
        //echo 'from add details.'; exit;
        //$session = new Zend_Session_Namespace('App.Agent.Controller');

         //echo 'from add details.'; exit;
        $m = new App\Messaging\Remit\BOI\Agent();
        $formData  = $this->_request->getPost();
        $user = Zend_Auth::getInstance()->getIdentity();        
        $session = new Zend_Session_Namespace('App.Agent.Controller'); 
        unset($session->remitter_id);
        $objRemitterModel = new Remit_Boi_Remitter();
        $remitterId = isset($session->remitter_id)?$session->remitter_id:0;
        $products  = new Products();
        $objBaseTxn = new BaseTxn();
        //$objAgBal = new AgentBalance();
        $config = App_DI_Container::get('ConfigObject');
        $minAge = $this->view->minAge = $config->remitter->age->min;
        $currDate = date('Y-m-d');
        //$maxAge = $config->remitter->age->max;
        $uploadlimit = $config->agent->uploadfile->size;
        $errorExists = false;
        
        // Get our form and validate it
        $form = new Remit_Boi_AddRemitterDetailsForm(array(
                                                'action' => $this->formatURL('/remit_boi_remitter/adddetails'),
                                                'method' => 'post',
                                                'name'=>'frmAdddetails',
                                                'id'=>'frmAdddetails'
                                        ));  
       
        $this->view->form = $form; 
        $product = App_DI_Definition_BankProduct::getInstance(BANK_BOI_REMIT);
        $productUnicode = $product->product->unicode;
        $productList = $products->getAgentProducts($user->id, PROGRAM_TYPE_REMIT,$productUnicode);       
        $form->getElement("product_id")->setMultiOptions($productList);
        $dob = isset($formData['dob'])?$formData['dob']:'';
        $btnAuth = isset($formData['send_auth_code'])?$formData['send_auth_code']:'0';   
        $btnAdd = isset($formData['btn_add'])?$formData['btn_add']:'';
        //echo $session->remitter_auth;
        
        // sending authrization code on mobile
        if( $btnAuth== 1 ){  
            
               try{
                   
                 if($formData['regn_fee'] > 0)
                 {
                     $regnFee = $formData['regn_fee'];
                     $paramsTxnLimit = array('agent_id'  => $user->id,
                                    'amount'    => $formData['regn_fee']);  
                     $flgTxnLimit = $objBaseTxn->chkAllowRemitterRegn($paramsTxnLimit);
                 }
                 else
                 {
                     $regnFee = 0;
                     $flgTxnLimit = TRUE;
                 }
                if($flgTxnLimit) {
                    if($user->mobile1 == $formData['mobile']) {
                        $form->populate($formData);
                        $this->_helper->FlashMessenger(array(
                            'msg-error' => 'Remitter\'s mobile number can not same as Partner\'s mobile number.'
                        ));
                        $errorExists = true;
                    } else {
                        $userData = array(
                            'mobile_country_code'=>$formData['mobile_country_code'], 
                            'mobile1'=>$formData['mobile'],
                            'mobile_old'=>$formData['mobile_old'],
                            'fee'=>$regnFee,
                            'product_name' => BOI_SHMART_TRANSFER
                        );                               
                        
                        if(isset($session->remitter_auth))
                            $resp = $m->remitterAuth($userData,$resend = TRUE);
                        else
                             $resp = $m->remitterAuth($userData);

                        //$formData['dob'] = Util::returnDateFormatted($dob, "Y-m-d", "d-m-Y", "-"); 
                        $this->view->remitter_auth = $session->remitter_auth;                       
                        $session->remitter_mobile_number = $formData['mobile'];                       
                        $this->view->remitterData = $formData;
                        $this->_helper->FlashMessenger( array('msg-success' => 'Authorization code has been sent on remitter mobile number.',) );
                        $form->populate($formData);
                        $form->getElement("send_auth_code")->setValue("0");    
                    }
                    //$session->cardholder_auth = 1;
                    //echo $session->cardholder_auth;
                }
        }catch (Exception $e ) {  
                $errorExists = true;
                $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                $form->populate($formData);
                $this->view->remitterData = $formData;
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
            }  
        //}
      }
        //echo $session->remitter_auth.'--';  
        
        // adding details in db
        if($btnAdd){
            
           
              
            $formData['dob'] = Util::returnDateFormatted($dob, "d-m-Y", "Y-m-d", "-");
            $this->view->remitterData = $formData;
            // getting current ip to store in db
            $ip = $objRemitterModel->formatIpAddress(Util::getIP());
            
            if($form->isValid($this->getRequest()->getPost())){
                
                //echo $session->remitter_auth.'==='; exit;
                $authValidated = isset($session->validated_remitter_auth)?$session->validated_remitter_auth:'0';
                //echo $authValidated;
                /*if($formData['auth_code'] =='' && $authValidated!=1){
                    $errorExists=true;
                    $this->_helper->FlashMessenger( array('msg-error' => 'Please enter authorization code.',) );
                } else {*/

                if($authValidated!=1 && $session->remitter_auth != $formData['auth_code'] || ($session->remitter_mobile_number!=$formData['mobile']) ){ // matching the auth code
                     //$this->view->msg = $res;
                     $errorExists = true;
                     $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'Invalid Authorization Code',
                        )
                    );
                    //$formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-"); 
                    $this->view->formData = $formData;
                    $form->getElement("send_auth_code")->setValue("0");
                } else {                        
                        $datetime1 = date_create($currDate);
                        $datetime2 = date_create($formData['dob']);                
                        $interval = date_diff($datetime1, $datetime2);
                        $age = $interval->format('%y');
                     
                    if ($formData['dob']==''){
                        $this->_helper->FlashMessenger(array( 'msg-error' => 'Date of Birth cannot left blank', ));
                        //$formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-");
                        $form->populate($formData);
                        $this->view->remitterData = $formData;
                        $errorExists = true;
                     } else if ($age < $minAge){
                        $this->_helper->FlashMessenger(array( 'msg-error' => 'Minimum age should be 18 years', ));
                        $formData['dob'] = Util::returnDateFormatted($formData['dob'], "Y-m-d", "d-m-Y", "-");
                        $this->view->formData = $formData;
                        $form->populate($formData);
                        $errorExists = true;
                    }
                    else if ($formData['regn_fee'] > 0){
                        try {
                            $regnFee = $formData['regn_fee'];
                            $paramsTxnLimit = array('agent_id'  => $user->id,
                                                    'amount'    => $formData['regn_fee']);  
                             $objBaseTxn->chkAllowRemitterRegn($paramsTxnLimit);

                        }catch (Exception $e ) {  
                            $errorExists = true;
                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                            $form->populate($formData);
                            $this->view->remitterData = $formData;
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        }  
                    }
                     
                     if(!$errorExists){
                        $session->validated_remitter_auth = 1;
                        $remitterData = array(  'name'=>$formData['name'],
                                                
                                                'product_id'=>$formData['product_id'],
                                                'bank_name'=>$formData['bank_name'], 
                                                'ifsc_code'=>trim($formData['ifsc_code']),
                                                'bank_account_number'=>$formData['bank_account_number'],
                                                'branch_name'=>$formData['branch_name'], 
                                                'branch_city'=>$formData['branch_city'],
                                                'branch_address'=>$formData['branch_address'],  
                                                'bank_account_type'=>$formData['bank_account_type'],
                                                'address'=>$formData['address'],
                                                'mobile_country_code'=>$formData['mobile_country_code'],
                                                'mobile'=>$formData['mobile'],                            
                                                'dob'=>$formData['dob'],                                                       
                                                'mother_maiden_name'=>$formData['mother_maiden_name'],                                                       
                                                'email'=>$formData['email'],
                                                'by_agent_id'=>$user->id,
                                                'ip'=>$ip,
                                                'date_created'=>date('Y-m-d H:i:s'),
                                                'status'=>STATUS_PENDING                                          
                                             ); 

                          //$oldVals['mobile_number_old'] = isset($formData['mobile_number_old'])?$formData['mobile_number_old']:'';   
                          $oldVals['email_old'] = isset($formData['email_old'])?$formData['email_old']:''; 
                         
                        $objUnicode = new Unicode();
                        $agentId = $user->id;
                        $objProducts = new Products();
                        $objBanks = new Banks();
                        $remitterAddResp=false;
                        
                        try{  
                                   
                                $agentProduct = $objProducts->findById($formData['product_id']); //getAgentRemitProduct($agentId);
                                $agentProduct = $agentProduct->toArray();
                                $productUnicode = isset($agentProduct['unicode'])?$agentProduct['unicode']:'';

                                $bankDetails = $objBanks->getBankbyProductId($formData['product_id'],PROGRAM_TYPE_REMIT);
                                $bankDetails = $bankDetails->toArray();
                                $bankUnicode = isset($bankDetails['unicode'])?$bankDetails['unicode']:'';

                                //echo $bankUnicode.'---'.$productUnicode; exit;
                                $objUnicode->_PROGRAM_TYPE = PROGRAM_TYPE_REMIT;
                                $objUnicode->_BANK_UNICODE = $bankUnicode;
                                $objUnicode->_PRODUCT_UNICODE = $productUnicode;
                                if($objUnicode->generateUnicode()) {
                                    $remitterData['unicode'] = $objUnicode->getUnicode();
                                    $objUnicode->setUsedStatus();
                                }
                                        
                                // adding remitter details in db.
                                $remitterAddResp = $objRemitterModel->addRemitter($remitterData, $remitterId, $oldVals);   
                                //Upload Remitter photo
            $profilePhotoName = REMITTER_PROFILE_PHOTO_PREFIX.$session->remitter_id; 
            $profilePhotoFile = isset($_FILES['profile_pic']['name'])?$_FILES['profile_pic']['name']:'';      
            
              
                  if(trim($profilePhotoFile)==''){
                    unset($_FILES['profile_pic']);
                    
                  }
                  if ($profilePhotoFile!='') {
                      
                        //upload files
                        $uploadphoto = new Zend_File_Transfer_Adapter_Http();     
                        
                        // Add Validators for uploaded file's extesion , mime type and size
                        $uploadphoto->addValidator('Extension', false, array('jpg', 'jpeg','pdf','png'=>false))
                                ->addValidator('FilesSize', false, array('min' => '5kB', 'max' => $uploadlimit));
                       
                        $uploadphoto->setDestination(UPLOAD_PATH_REMITTER_PHOTO);
                        
                        try{
                              
                                
                            //All validations correct then upload file
                            if ($uploadphoto->isValid()) {
                                
                                $uploadphoto->receive();
                              //  echo '<pre>';print_r($uploadphoto);exit;
                                 $namePhoto = $uploadphoto->getFileName('profile_pic');
                                 // get the file name and extension
//                                     $extPhoto = explode(".", $namePhoto);
                                     $extPhoto = pathinfo($namePhoto, PATHINFO_EXTENSION);
                                     $renameFilePhoto= $profilePhotoName. '.' . $extPhoto;
                                     
                                     $destPhoto = $uploadphoto->getDestination('profile_pic');
                                     
                                 // Rename uploaded file using Zend Framework
                                     $fullFilePathPhoto = $destPhoto . '/' . $renameFilePhoto;
//                                     echo $fullFilePathPhoto;exit;
                                     $filterFileRenamePhoto = new Zend_Filter_File_Rename(array('target' => $fullFilePathPhoto, 'overwrite' => true));
                                     $filterFileRenamePhoto->filter($namePhoto);
                                 
                              }
                            else {
                                    $this->_helper->FlashMessenger(
                                        array(
                                                'msg-error' => 'Profile photo could not be uploaded. Allowed Formats are Jpeg,Jpg,Pdf only.',
                                             )
                                    );
                                    $isError = TRUE;
                                }
                            
                      }catch (Zend_File_Transfer_Exception $e) {
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            $this->_helper->FlashMessenger(
                                    array(
                                        'msg-error' => $e->getMessage(),
                                    )
                            );
                             $isError = TRUE;
                        }
                            
                  }
  
                  $objRemitterModel->updateRemitter(array('profile_photo'=> $renameFilePhoto), $session->remitter_id);
                 
                  
                  // End of Upload remitter photo 
                                
                            }catch (Exception $e ) { 
                                                        $errorExists = true; 
                                                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                                                        $formData['dob'] = Util::returnDateFormatted($formData['dob'], "Y-m-d", "d-m-Y", "-");
                                                        $form->populate($formData);
                                                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                                    }                 
          
            
                if($remitterAddResp && !$errorExists){
                           // Call baseTxn function only when regn_fee is greater than 0
                            if($formData['regn_fee'] > 0){
                            $feeComponent = Util::getFeeComponents($formData['regn_fee']);
                            // checking for transaction response
                            
                            $txnCode = 0;
                            $txnResponse = FALSE;
                            $remitterTxnData = array(
                                                        'remitter_id'=>$session->remitter_id,
                                                        'agent_id'=>$user->id,
                                                        'product_id'=>$formData['product_id'],
//                                                      'amount' =>$formData['regn_fee'],
                                                        'fee_amt'=>$feeComponent['partialFee'],
                                                        'service_tax'=>$feeComponent['serviceTax'],
                                                    );
                            
                            try{
                                $txnCode = $objBaseTxn->remitterRegnFee($remitterTxnData);
                                $txnResponse = TRUE;                                 
                            }
                               catch (Exception $e ) { 
                                                        App_Logger::log($e->getMessage(), Zend_Log::ERR);                    
                                                        $this->_helper->FlashMessenger(array( 'msg-error' => $e->getMessage(),));
                                                     }
                              
                            }
                            else{
                                $txnResponse = TRUE;
                                $feeComponent['partialFee'] = 0;
                                $feeComponent['serviceTax'] = 0;
                            }
                               if($txnResponse){

                                    try{
                                        $session->unicode_assigned = 1;
                                        $objRemitterModel->updateRemitter(array('regn_fee'=>$feeComponent['partialFee'],'service_tax'=>$feeComponent['serviceTax'], 'txn_code' => $txnCode, 'status'=>STATUS_ACTIVE), $session->remitter_id);
                                    } catch (Exception $e ) { 
                                                                App_Logger::log($e->getMessage(), Zend_Log::ERR); 
                                                                $this->_helper->FlashMessenger(array( 'msg-error' => $e->getMessage(),));
                                                            }
                               }
                               //} 
                               // sending registration status sms 
                  
                               $smsData = array('mobile1'=>$formData['mobile'], 'mobile_country_code'=>$formData['mobile_country_code'],'product_name' => BOI_SHMART_TRANSFER,'call_centre_number' =>BOI_CALL_CENTRE_NUMBER, 'customer_support_email' =>BOI_SHMART_EMAIL);
                
                               if($txnResponse && empty($e)){
                                    $smsData['status'] = FLAG_SUCCESS;
                                    $m->remitterRegistration($smsData);
                                    $this->_helper->FlashMessenger(array( 'msg-success' => 'Remitter enrolled successully',));
                               } else {
                                    $smsData['status'] = FLAG_FAILURE;
                                    $m->remitterRegistration($smsData);
                                    if(empty($e))
                                        $this->_helper->FlashMessenger(array( 'msg-error' => 'Remitter enrollment failed',));
                               }
                               
                               $this->_redirect($this->formatURL('/remit_boi_remitter/registrationcomplete/'));
                               
                               
                               
                } 
              }
             }
            //}
           } // if form valid
            //}
          } //  if btnAdd end
         
       
  
        
            if($session->remitter_id>0){ // populating form values
                $remitterInfo = $objRemitterModel->getRemitterInfo($session->remitter_id);                
             
                $form->getElement("mobile_old")->setValue($remitterInfo['mobile']);
                $remitterInfo['dob'] = Util::returnDateFormatted($remitterInfo['dob'], "Y-m-d", "d-m-Y", "-");                              
                $remitterData = array(  'name'=>$remitterInfo['name'],
                                        'bank_name'=>$remitterInfo['bank_name'], 
                                        'ifsc_code'=>$remitterInfo['ifsc_code'],
                                        'bank_account_number'=>$remitterInfo['bank_account_number'],
                                        'branch_name'=>$remitterInfo['branch_name'], 
                                        'branch_city'=>$remitterInfo['branch_city'],
                                        'branch_address'=>$remitterInfo['branch_address'],  
                                        'bank_account_type'=>$remitterInfo['bank_account_type'],
                                        'address'=>$remitterInfo['address'],
                                        'mobile_country_code'=>$remitterInfo['mobile_country_code'],
                                        'mobile'=>$remitterInfo['mobile'],                            
                                        'dob'=>$remitterInfo['dob'],                                                       
                                        'mother_maiden_name'=>$remitterInfo['mother_maiden_name'],                                                       
                                        'email'=>$remitterInfo['email']
                                             );
                
                $form->populate($remitterData);
                $this->view->remitterData = $remitterData;
                //$this->view->chDetails = $row2;
            }
            
            $this->view->remitter_auth = $session->remitter_auth;
            $this->view->errorExists = $errorExists;
            //echo $session->cardholder_auth;
            
 } // adddetails action closes here
        
  
        
   
        
        /* registrationcompleteAction is responsible for handling unset the required session details of remitter       
        */
        public function registrationcompleteAction(){
            
         $this->title = 'Enroll Remitter - Complete';
         $session = new Zend_Session_Namespace('App.Agent.Controller');
        
         unset($session->remitter_id); 
         //unset($session->first_step1);
         unset($session->remitter_auth);
         unset($session->validated_remitter_auth);
         unset($session->unicode_assigned);
         unset($session->remitter_mobile_number);
        }
    
    

}
