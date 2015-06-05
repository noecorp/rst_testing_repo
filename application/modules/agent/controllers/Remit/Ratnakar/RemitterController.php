<?php

/**
 * That RemitterController is responsible for all remit operations at partner portal.
 */

class Remit_Ratnakar_RemitterController extends App_Agent_Controller
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
        $m = new App\Messaging\Remit\Ratnakar\Agent();   
        $formData  = $this->_request->getPost();
        $user = Zend_Auth::getInstance()->getIdentity();   
        $session = new Zend_Session_Namespace('App.Agent.Controller'); 
        
        App_Logger::log("REM-REG : STARTED for agentId :". $user->id, Zend_Log::INFO);
        
		if(count($formData) == 0) {
			 unset($session->remitter_id); 
			 //unset($session->first_step1);
			 unset($session->remitter_auth);
			 unset($session->validated_remitter_auth);
			 unset($session->unicode_assigned);
			 unset($session->remitter_mobile_number);
			 unset($session->beneficiaryid);
			 unset($session->local_beneficiaryid);	
		}
       
        $objRemitterModel = new Remit_Ratnakar_Remitter();
        $remitterId = isset($session->remitter_id)? $session->remitter_id: 0;
        $products  = new Products();
        $objBaseTxn = new BaseTxn();
        $objBank = new Banks();
        $cardholderModel = new Corp_Ratnakar_Cardholders();
        $config = App_DI_Container::get('ConfigObject');
        $minAge = $this->view->minAge = $config->remitter->age->min;
        $currDate = date('Y-m-d');
        //$maxAge = $config->remitter->age->max;
        $uploadlimit = $config->agent->uploadfile->size;  
        $errorExists = false;
        // Get our form and validate it
        $form = new Remit_Ratnakar_AddRemitterDetailsForm(array(
                        'action' => $this->formatURL('/remit_ratnakar_remitter/adddetails'),
                        'method' => 'post',
                        'name'=>'frmAdddetails',
                        'id'=>'frmAdddetails'
                ));  
       
        $this->view->form = $form;
        $bank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $bankRatUnicode = $bank->bank->unicode;
        
        $productIds = $user->product_ids;
        $prodConstArr = Util::getArrayBykey($productIds, 'product_const'); 
        
        $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_REMIT);
        $productUnicode = $product->product->unicode;
        $productList = $products->getAgentProducts($user->id, PROGRAM_TYPE_REMIT ,$productUnicode);   
        foreach($productList as $key => $val)
        {
            $product_id = $key;
        }

        //$form->getElement("product_id")->setMultiOptions($productList);
        $dob = isset($formData['dob'])?$formData['dob']:'';
        $btnAuth = isset($formData['send_auth_code'])?$formData['send_auth_code']:'0';   
        $btnAdd = isset($formData['btn_add'])?$formData['btn_add']:'';
        $is_submit = isset($formData['is_submit'])?$formData['is_submit']:'';
        $bankInfo = $objBank->getBankbyUnicode($bankRatUnicode);
        $bank_id = $bankInfo['id'];
        $state = new CityList(); 
        
        //echo $session->remitter_auth;
        if($btnAuth != 1 && !$is_submit){
             unset($session->remitter_auth);
        }
        
        /*
            *   After hide city namae and state name with address 
            *   we find all detail on the basis of pincode 
        */
        if((isset($formData["pincode"])) && ($formData["pincode"] != '')) {
            $StateCityArr = explode('^', $state->getCityByPincode($formData["pincode"])) ; 
            $formData['state'] =  $StateCityArr[0] ;
            $formData['city'] =  $StateCityArr[1] ;
        } 
        
		if($session->remitter_id>0){
		}else{
			$formData['beni_name']='';
			$formData['beni_bank_account_number']='';
		}

        // sending authrization code on mobile
        if( $btnAuth == 1 ){  
        	try{
                 
        			App_Logger::log("REM-REG : Before sending OTP for agentId: ". $user->id ." ,bcagent: " .$user->bcagent, Zend_Log::INFO);
        		
                    $form->getElement("beni_bank_state")->setMultiOptions(array($formData["beni_bank_state"] => $formData["beni_bank_state"]));
                    $form->getElement("beni_branch_name")->setMultiOptions(array($formData["beni_branch_name"] => $formData["beni_branch_name"]));
                    $form->getElement("beni_branch_city")->setMultiOptions(array($formData["beni_branch_city"] => $formData["beni_branch_city"]));
                   
                    if($formData['regn_fee'] > 0)
                    {
                        $regnFee = $formData['regn_fee'];
                        $paramsTxnLimit = array('agent_id'  => $user->id,
                                       'amount'    => $formData['regn_fee'],
                                       'bank_unicode' => $bank->bank->unicode);  
                        $flgTxnLimit = $objBaseTxn->chkAllowRemitterRegn($paramsTxnLimit);
                    }
                    else
                    {
                        $regnFee = 0;
                        $flgTxnLimit = TRUE;
                    }
                    if($flgTxnLimit)
                    {   
                        $userData = array('mobile_country_code'=> $formData['mobile_country_code'], 
                                      'mobile1'=>$formData['mobile'],
                                      'mobile_old'=>$formData['mobile_old'],
                                      //'fee'=>$regnFee,
                                      'product_name' => RATNAKAR_REMITTANCE,
                                      'name' => $formData['name'],
                                      'address' => $formData['city']
                                     );                               

                        if($user->mobile1 == $userData['mobile1']){ 
                            $formData['cty'] = $formData['city'];
                            $formData['pin'] = $formData['pincode']; 
                            $form->populate($formData);
                            $this->_helper->FlashMessenger(array(
                                'msg-error' => 'Remitter\'s mobile number can not same as Partner\'s mobile number.'
                            ));
                            $errorExists = true;
                        } else if($userData['name'] != '' && $userData['address'] != '') {
                            if(isset($session->remitter_auth)) {
                                if(isset($formData['beni_name']) && $formData['beni_name']!='') { 
                                    $userData["beni_nick_name"] = $formData['beni_name'];
                                    $resp = $m->remitterBeniAuthRatnakar($userData,$resend = FALSE);
                                } else { 
                                    $resp = $m->remitterAuthRatnakar($userData,$resend = FALSE);
                                }	
                            }else{ 
                                if(isset($formData['beni_name']) && $formData['beni_name']!='') {   
                                    $userData["beni_nick_name"]= $formData['beni_name'];
                                    $resp = $m->remitterBeniAuthRatnakar($userData);
                                }else{ 
                                    $resp = $m->remitterAuthRatnakar($userData);
                                }

                            }

                            $this->view->remitter_auth = $session->remitter_auth;                       
                            $session->remitter_mobile_number = $formData['mobile'];                       
                            $this->view->remitterData = $formData;

                            $formData['cty'] = $formData['city'];
                            $formData['pin'] = $formData['pincode'];

                            $form->populate($formData);
                            $form->getElement("send_auth_code")->setValue("0");
                            App_Logger::log("REM-REG : Sending authCode for Mobile: " . $formData['mobile']." , agentId: ". $user->id ." ,bcagent: " .$user->bcagent, Zend_Log::INFO);
                            
                            $this->_helper->FlashMessenger( array('msg-success' => 'Authorization code has been sent on remitter mobile number.',) );
                        }else{
                            $formData['cty'] = $formData['city'];
                            $formData['pin'] = $formData['pincode'];

                            $form->populate($formData);
                            $this->_helper->FlashMessenger( array('msg-error' => 'Please fill in remitter name and pincode',) );
                        }

                    }
                }catch (App_Exception $e ) {  
                    $errorExists = true;
                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                    $formData['cty'] = $formData['city'];
                    $formData['pin'] = $formData['pincode'];
                    $formData['dob'] = $dob;
                    $form->populate($formData);
                    $this->view->remitterData = $formData;
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                } catch (Exception $e ) {  
                    $errorExists = true;
                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                    $formData['cty'] = $formData['city'];
                    $formData['pin'] = $formData['pincode'];
                    $formData['dob'] = $dob;
                    $form->populate($formData);
                    $this->view->remitterData = $formData;
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                }   
        }
      
        // adding details in db
        if($is_submit){
          
        	App_Logger::log("REM-REG : After submitting OTP for Mobile: " . $formData['mobile']." , agentId: ". $user->id ." ,bcagent: " .$user->bcagent, Zend_Log::INFO);
        	 
            $form->getElement("beni_bank_state")->setMultiOptions(array($formData["beni_bank_state"] => $formData["beni_bank_state"]));
						$form->getElement("beni_branch_name")->setMultiOptions(array($formData["beni_branch_name"] => $formData["beni_branch_name"]));
						$form->getElement("beni_branch_city")->setMultiOptions(array($formData["beni_branch_city"] => $formData["beni_branch_city"]));  
						$formData['cty'] = $formData['city'];
            $formData['pin'] = $formData['pincode'];
            if($dob !=''){
            $formData['dob'] = Util::returnDateFormatted($dob, "d-m-Y", "Y-m-d", "-");
            }else{
             $formData['dob'] = '';   
            }
            $this->view->remitterData = $formData;
            // getting current ip to store in db
            $ip = $objRemitterModel->formatIpAddress(Util::getIP());
            
            //if($form->isValid($this->getRequest()->getPost())){
			if($form->isValid($formData)){
                
                $authValidated = isset($session->validated_remitter_auth)?$session->validated_remitter_auth:'0';
                if($authValidated!=1 && $session->remitter_auth != $formData['auth_code'] || ($session->remitter_mobile_number!=$formData['mobile']) ){ // matching the auth code
				
                     $errorExists = true;
                     $formData['dob'] = $dob;
                     $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'Invalid Authorization Code',
                        )
                    );
                    $this->view->formData = $formData;
                    $form->getElement("send_auth_code")->setValue("0");
                    $form->getElement("cty")->setValue($formData['city']);
                    $form->getElement("pin")->setValue($formData['pincode']);
                    $form->populate($formData);
                } else {  
				
                        if($formData['dob']!=''){
                        $datetime1 = date_create($currDate);
                        $datetime2 = date_create($formData['dob']);                
                        $interval = date_diff($datetime1, $datetime2);
                        $age = $interval->format('%y');
                        }

                     
                    if ( ($age < $minAge) && ($formData['dob']!='')){
                        $this->_helper->FlashMessenger(array( 'msg-error' => 'Minimum age should be 18 years', ));
                        $formData['dob'] = $dob;
                        $this->view->formData = $formData;
                        $form->populate($formData);
                        $errorExists = true;
                    }else if ($formData['regn_fee'] > 0){
                        try {
                            $regnFee = $formData['regn_fee'];
                            $paramsTxnLimit = array('agent_id'  => $user->id,
                                                    'amount'    => $formData['regn_fee'],
                                                    'bank_unicode' => $bank->bank->unicode
                                );  
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
                        $staticCode = Util::generate6DigitCode();
                        list($firstName,$lastName)  = split(' ',$formData['name'],2);
                        
                        if(!isset($lastName)){
                            $lastName = '';
                        }
                        
                        $remitterData = array(  
                                        'name'=>$firstName,
                                        'middle_name'=>$formData['middle_name'],
                                        'last_name'=>$lastName,
                                        'product_id'=>$product_id,
                                        'bank_name'=> isset($formData['bank_name'])?$formData['bank_name']:'', 
                                        'ifsc_code'=>isset($formData['ifsc_code'])?$formData['ifsc_code']:'',
                                        'bank_account_number'=>isset($formData['bank_account_number'])?$formData['bank_account_number']:'',
                                        'branch_name'=>isset($formData['branch_name'])?$formData['branch_name']:'', 
                                        'branch_city'=>isset($formData['branch_city'])?$formData['branch_city']:'',
                                        'branch_address'=>isset($formData['branch_address'])?$formData['branch_address']:'',  
                                        'bank_account_type'=>isset($formData['bank_account_type'])?$formData['bank_account_type']:'',
                                        'address'=>$formData['city'],
                                        'city'=>$formData['city'],
                                        'state'=> $state->getStateName($formData['state']),
                                        'pincode'=>$formData['pincode'],
                                        'mobile_country_code'=>$formData['mobile_country_code'],
                                        'mobile'=>$formData['mobile'],                            
                                        'dob'=>$formData['dob'],                                                       
                                        'mother_maiden_name'=>$formData['mother_maiden_name'],                              
                                        'by_agent_id'=>$user->id,
                                        'ip'=>$ip,
                                        'date_created'=>date('Y-m-d H:i:s'),
                                        'bank_id'=> $bank_id,
                                        'status'=>STATUS_ACTIVE                                          
                                     ); 

                          $oldVals['email_old'] = isset($formData['email_old'])?$formData['email_old']:''; 
                         
                        $objUnicode = new Unicode();
                        $agentId = $user->id;
                        $objProducts = new Products();
                        $objBanks = new Banks();
                        $remitterAddResp=false;
                        
                        $txn_code = $objBaseTxn->generateTxncode();
                        try{  
                                   
                            $agentProduct = $objProducts->findById($product_id); //getAgentRemitProduct($agentId);
                            $agentProduct = $agentProduct->toArray();
                            $productUnicode = isset($agentProduct['unicode'])?$agentProduct['unicode']:'';

                            $bankDetails = $objBanks->getBankbyProductId($product_id,PROGRAM_TYPE_REMIT);
                            $bankDetails = $bankDetails->toArray();
                            $bankUnicode = isset($bankDetails['unicode'])?$bankDetails['unicode']:'';

                            $objUnicode->_PROGRAM_TYPE = PROGRAM_TYPE_REMIT;
                            $objUnicode->_BANK_UNICODE = $bankUnicode;
                            $objUnicode->_PRODUCT_UNICODE = $productUnicode;
                            if($objUnicode->generateUnicode()) {
                                $remitterData['unicode'] = $objUnicode->getUnicode();
                                $objUnicode->setUsedStatus();
                            }

                            App_Logger::log("REM-REG : Before adding in DB: RemitterId : " . $remitterId ." , agentId: ". $user->id ." ,bcagent: " .$user->bcagent, Zend_Log::INFO);
                            App_Logger::log("REM-REG : Before adding in DB: RemitterData : " . implode($remitterData) ." , agentId: ". $user->id ." ,bcagent: " .$user->bcagent, Zend_Log::INFO);
                            
                            // checking cardholder mobile number
                             
                        $respMobile = $cardholderModel->checkCardholderByMobile(array(
                       // 'product_id' => $product_id,
                        'mobile' => $remitterData['mobile'],
                        ));
                        //if ($respMobile == TRUE) {
                        if (!empty($respMobile['id'])) {
                        $this->_helper->FlashMessenger(array( 'msg-error' => 'Mobile number is already used.'));
                        return;
                        }
                            
                            // adding remitter details in db.
                            $remitterAddResp = $objRemitterModel->addRemitter($remitterData, $remitterId, $oldVals); 
							
                            App_Logger::log("REM-REG : After adding in DB: remitterAddResp : " . $remitterAddResp ." , agentId: ". $user->id ." ,bcagent: " .$user->bcagent, Zend_Log::INFO);
                            
                         $smp_product_id = ''; 
                         $responseCustID = '';
                         $smpProductinfo = $products->getProductDetailbyConst(PRODUCT_CONST_RAT_SMP);
                        if(!empty($smpProductinfo)){
                            $smp_product_id = $smpProductinfo['id']; 
                        }
                        
                             if( ($remitterAddResp) && ($session->remitter_id > 0) && ($smp_product_id!='') )

                {
                            //////////////***********************************
                    
                    
                    $custParams['TransactionRefNo'] = $txn_code; //$resp->TransactionRefNo;
                    $custParams['PartnerRefNo'] = $remitterData['mobile']; //$resp->PartnerRefNo;
                    $custParams['ProductId'] = $smp_product_id;
                    $custParams['FirstName'] = $remitterData['name'];
                    $custParams['MiddleName'] = $remitterData['middle_name'];
                    $custParams['LastName'] = $remitterData['last_name'];
                    $custParams['DateOfBirth'] = $remitterData['dob'];
                    $custParams['Mobile'] = $remitterData['mobile'];
                    $custParams['Email'] = '';
                    
                    $custParams['by_api_user_id'] = $remitterData['by_agent_id'];   
                    $custParams['MotherMaidenName'] = $remitterData['mother_maiden_name'];
                    $custParams['AddressLine1'] = $remitterData['address'];
                    $custParams['Pincode'] = $remitterData['pincode'];
                    $custParams['City'] = $remitterData['city'];
                    $custParams['State'] = $remitterData['state'];
                    $custParams['Country'] = COUNTRY_CODE_INDIA;
                    $custParams['customer_type'] = TYPE_NONKYC;
                    $custParams['status_ops'] = STATUS_APPROVED;
                    $custParams['status_ecs'] = STATUS_SUCCESS;
                    $custParams['status'] = STATUS_INACTIVE; 
                    $custParams['txnCode'] = $txn_code; 
                    $custParams['bank_id'] = $bank_id;
                    $custParams['manageType'] = AGENT_MANAGE_TYPE;
                    $custParams['channel']= CHANNEL_AGENT;
                    
                    $responseCustID = $cardholderModel->addCustomerAPI($custParams);
                    
                    if($responseCustID > 0){
                        $mapperCustInfo = array(
                         'cardholder_id' => $responseCustID,
                         'remitter_id' => $session->remitter_id  
                            
                        );
                        $response = $cardholderModel->addMapperRemitterAPI($mapperCustInfo);
                        
                    }else{
                      $this->_helper->FlashMessenger(array( 'msg-error' => 'Remitter enrollment failed.',));
                        
                    }
                        
                    }
                            
							if(!$remitterAddResp) {
								$this->_helper->FlashMessenger(array( 'msg-error' => 'Remitter already registered'));
								return;
							}
												  
                            
                            //Upload Remitter photo
                            $profilePhotoName = REMITTER_PROFILE_PHOTO_PREFIX.$session->remitter_id; 
                            $profilePhotoFile = isset($_FILES['profile_pic']['name'])?$_FILES['profile_pic']['name']:'';      

                                
                        }catch (Exception $e ){ 
                            $errorExists = true; 
                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                            $formData['dob'] = $dob;
                            $form->populate($formData);
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        }                 
			
                        if($remitterAddResp && !$errorExists){
                            // Call baseTxn function only when regn_fee is greater than 0
                            if($formData['regn_fee'] > 0){
                                $feeComponent = Util::getFeeComponents($formData['regn_fee']);
                                App_Logger::log("REM-REG : After getFeeComponents: " . $feeComponent ." , agentId: ". $user->id ." ,bcagent: " .$user->bcagent, Zend_Log::INFO);
                                
                                // checking for transaction response
                                $txnCode = 0;
                                $txnResponse = FALSE;
                                $remitterTxnData = array(
                                                    'remitter_id'=>$session->remitter_id,
                                                    'agent_id'=>$user->id,
                                                    'product_id'=>$product_id,
    //                                              'amount' =>$formData['regn_fee'],
                                                    'fee_amt'=>$feeComponent['partialFee'],
                                                    'service_tax'=>$feeComponent['serviceTax'],
                                                    'bank_unicode' => $bank->bank->unicode
                                                    );

                                try{
                                    $txnCode = $objBaseTxn->remitterRegnFee($remitterTxnData);
                                    $txnResponse = TRUE;                                 
                                }catch (Exception $e ){ 
                                    App_Logger::log($e->getMessage(), Zend_Log::ERR);                    
                                    $this->_helper->FlashMessenger(array( 'msg-error' => $e->getMessage(),));
                                }
                            }else{
                                $txnResponse = TRUE;
                                $txnCode = 0;
                                $feeComponent['partialFee'] = 0;
                                $feeComponent['serviceTax'] = 0;
                            }
                            App_Logger::log("REM-REG : After remitter fee txn txnCode: " . $txnCode ." , txnResponse: ".$txnResponse ." , agentId: ". $user->id ." ,bcagent: " .$user->bcagent, Zend_Log::INFO);
                            
                            if($txnResponse){
                                try{
                                    $session->unicode_assigned = 1;
                                    $objRemitterModel->updateRemitter(array('regn_fee'=>$feeComponent['partialFee'],'service_tax'=>$feeComponent['serviceTax'], 'txn_code' => $txnCode, 'status'=>STATUS_ACTIVE), $session->remitter_id);
                                     // Update CardHolder
                                    if($responseCustID !=''){
                                    $resp = $cardholderModel->updateCardholderAPI(array('status'=>STATUS_ACTIVE), $responseCustID);
                                    }
                                    App_Logger::log("REM-REG : Updated remitter regn_fee: " . $feeComponent['partialFee'] ." , service_tax: ".$feeComponent['serviceTax'] ." , agentId: ". $user->id ." ,bcagent: " .$user->bcagent, Zend_Log::INFO);
                                }catch (Exception $e ){ 
                                    App_Logger::log($e->getMessage(), Zend_Log::ERR); 
                                    $this->_helper->FlashMessenger(array( 'msg-error' => $e->getMessage(),));
                                }
                            }
                            App_Logger::log("REM-REG : Before sending remitter reg SMS for agentId: ". $user->id ." ,bcagent: " .$user->bcagent, Zend_Log::INFO);
                            
                            // sending registration status sms 
                           // $smsData = array('mobile1'=>$formData['mobile'], 'mobile_country_code'=>$formData['mobile_country_code'],'product_name' => RATNAKAR_REMITTANCE,'call_centre_number' =>RATNAKAR_CALL_CENTRE_NUMBER, 'customer_support_email' =>RATNAKAR_REMITTANCE_EMAIL);
                             $smsData = array('mobile1'=>$formData['mobile'], 'mobile_country_code'=>$formData['mobile_country_code'],'product_name' => RATNAKAR_REMITTANCE,'customer_service_by' => RATNAKAR_MONEY_SERVICES,'call_centre_number' =>RATNAKAR_CALL_CENTRE_NUMBER, 'customer_support_email' =>RATNAKAR_REMITTANCE_EMAIL,'name'=>$formData['name']);
                            
                            App_Logger::log("REM-REG : After sending remitter reg SMS for agentId: ". $user->id ." ,bcagent: " .$user->bcagent, Zend_Log::INFO);
                            
                            if($txnResponse && empty($e)){
                                
                                $beneficiary = new Remit_Ratnakar_Beneficiary();
                                $remitterDettails = $objRemitterModel->getRemitter($formData['mobile']);
                                try{
                                    $resppose = $beneficiary->getBeneficiaryAccountNo(array('bank_account_number'=>$formData['beni_bank_account_number'],'remitter_id'=>$remitterDettails['id'],'ifsc_code'=>trim($formData['beni_ifsc_code'])));
                                    if($resppose && $formData['beni_name'])
                                    {
                                        $data = array();
                                        $data['name'] = $formData['beni_name'];
                                        $data['nick_name'] = $formData['beni_name'];
                                        //$data['mobile'] = $formData['beni_mobile'];
                                        //$data['email'] = $formData['beni_email'];
                                        //$data['address_line1'] = $formData['beni_address_line1'];
                                        //$data['address_line2'] = $formData['beni_address_line2'];
                                        $data['bank_name'] = $formData['beni_bank_name'];
                                        $data['ifsc_code'] = strtoupper(trim($formData['beni_ifsc_code']));
                                        $data['bank_account_number'] = $formData['beni_bank_account_number'];
                                        $data['branch_address'] = $formData['beni_branch_address'];
                                        $data['branch_city'] = $formData['beni_branch_city'];
                                        $data['branch_name'] = $formData['beni_branch_name'];
                                        $data['bank_account_type'] = $formData['beni_bank_account_type'];
                                        $data['by_agent_id'] = $user->id;
                                        $data['by_ops_id'] = TXN_OPS_ID;
                                        $data['remitter_id'] = $remitterDettails['id'];
                                        $data['date_created'] = new Zend_Db_Expr('NOW()');
                                        $res = $beneficiary->addbeneficiary($data);
                                        if($res > 0) {
                                            $beneCode = Util::getBeneCodeFromId($res);
                                            $beneficiary->updateBeneficiaryDetails(array('bene_code'=> $beneCode),$res);

                                            $userArr = array(
                                                'mobile1' => $remitterDettails['mobile'],
                                                'status' => FLAG_SUCCESS,
                                                'nick_name' => $formData['beni_name'],
                                                'product_name' => RATNAKAR_REMITTANCE
                                            );
                                            $m->beneficiaryEnrollment($userArr);
                                        }
                                    }
                                } catch (Exception $e ) { 
                                    App_Logger::log($e->getMessage(), Zend_Log::ERR); 
                                    $this->_helper->FlashMessenger(array( 'msg-error' => $e->getMessage(),));
                                }	
								
							$dataToRblRemitterRegister = $this->_prepareRemiterDataToRblApi($remitterData,$user->bcagent);
							$rblRemiterRegisterRespose = $this->remitterRegistrationRemittance($dataToRblRemitterRegister);
				
							App_Logger::log("REM-REG : Remitter reg response: " . $rblRemiterRegisterRespose." , agentId: ". $user->id ." ,bcagent: " .$user->bcagent, Zend_Log::INFO);
								
							if(isset($rblRemiterRegisterRespose['status']) && $rblRemiterRegisterRespose['status']) {
								App_Logger::log("REM-REG : SUCCESS : ". $user->id, Zend_Log::INFO);
								$this->saveRemitterID($rblRemiterRegisterRespose['remitterid'],$remitterDettails['id']);	
                                                            $password = Util::generate_random_password();
                                                            $hash = new App_ShmartConsumer_PasswordHash(8,1);
                                                            $hashPwd = $hash->HashPassword($password);
                                                            try {
                                                                /* Call stored procedure starts */
                                                                $remitterArr = array(
                                                                    'mobile' => $formData['mobile'],
                                                                    'name' => $firstName,
                                                                    'password' => $hashPwd,  
                                                                    'email' => 'test@test.com',
                                                                    'ip'    => Util::getIP(),
                                                                    'partner_ref_num' => $formData['mobile']
                                                                );                                
                                                                $resp = $objRemitterModel->callEnrollRemitterSP($remitterArr);
                                                                App_Logger::log('Request SMP: Mobile => '.$remitterArr['mobile'].', Name => '.$remitterArr['name'].', Password => '.$password.', Email => '.$remitterArr['email'].', IP => '.$remitterArr['ip'].', Partner Reference No => '.$remitterArr['partner_ref_num']);
                                                                App_Logger::log('Response SMP: Status => '.$resp['Status']);
                                                            } catch (Exception $e) {
                                                                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                                            }
                                                            /* Call stored procedure ends */
                                                            if(!empty($resp) && $resp['Status'] == 'Consumer Registered Successfully') {
                                                                $smsData['status'] = FLAG_SUCCESS;
                                                                $smsData['password'] = $password;
                                                                $m->remitterRegistration($smsData);
                                                                $m->remitterRegistrationSecondSMS($smsData); 
                                                            }
                                
                                $this->_helper->FlashMessenger(array( 'msg-success' => 'Remitter enrolled successully'));
                                $this->_redirect($this->formatURL('/remit_ratnakar_beneficiary/searchremitter/'));
							}else if(isset($rblRemiterRegisterRespose['status']) && $rblRemiterRegisterRespose['status'] == 0
									&& $rblRemiterRegisterRespose['description'] == 'MOBILE NUMBER ALREADY REGISTERED') {
										App_Logger::log("REM-REG : MOBILE NUMBER ALREADY REGISTERED : ". $user->id, Zend_Log::INFO);
										
										$rblRemitterDetailsResponse = $this->getRemitterDetails($remitterData,$user->bcagent);
									error_log($rblRemitterDetailsResponse['remitterdetail']['remitterid']);
									
									if(isset($rblRemitterDetailsResponse['status']) && $rblRemitterDetailsResponse['status']) {
										$this->saveRemitterID($rblRemitterDetailsResponse['remitterdetail']['remitterid'],$remitterDettails['id']);
										$this->_helper->FlashMessenger(array( 'msg-success' => 'Remitter enrolled successully'));
                                                                                 $this->_redirect($this->formatURL('/remit_ratnakar_beneficiary/searchremitter/'));
									}
							}else{
								App_Logger::log("REM-REG : FAILED : ". $user->id, Zend_Log::INFO);
									$remitterModelObject = new Remit_Ratnakar_Remitter();
									$remitterModelObject->updateRemitter(array('status'=>STATUS_PENDING),$session->remitter_id);
                                                                        if($responseCustID !=''){
                                                                        $resp = $cardholderModel->updateCardholderAPI(array('status'=>STATUS_INACTIVE), $responseCustID);
                                                                        }
									$this->_helper->FlashMessenger(array( 'msg-error' => 'Remitter enrollment failed',));
                                                                       
							}

								
                            }else{
                            	App_Logger::log("REM-REG : SMS FAILURE : ". $user->id, Zend_Log::INFO);
                            	 
                                $smsData['status'] = FLAG_FAILURE;
                                $m->remitterRegistration($smsData);
                                if(empty($e))
                                    $this->_helper->FlashMessenger(array( 'msg-error' => 'Remitter enrollment failed',));
                            }
                               
                           // $this->_redirect($this->formatURL('/remit_ratnakar_remitter/registrationcomplete/'));
		 
                        } 
                    }
                }
           } // if form valid
           else{
           		App_Logger::log("REM-REG : Form Invalid for agentId: ". $user->id ." ,bcagent: " .$user->bcagent, Zend_Log::INFO);
           }
        } //  if btnAdd end
         
        if($session->remitter_id>0){ // populating form values
                $remitterInfo = $objRemitterModel->getRemitterInfo($session->remitter_id);                
                App_Logger::log("REM-REG : Populating form values : ". $user->id, Zend_Log::INFO);
                
                $form->getElement("mobile_old")->setValue($remitterInfo['mobile']);
                if($remitterInfo['dob'] !=''){
                $remitterInfo['dob'] = Util::returnDateFormatted($remitterInfo['dob'], "Y-m-d", "d-m-Y", "-");                              
                }
                $remitterData = array(  
                                'name'=>$remitterInfo['name']." ".$remitterInfo['last_name'],
                                'middle_name'=>$remitterInfo['middle_name'],
                                'last_name'=>$remitterInfo['last_name'],
                                'bank_name'=>$remitterInfo['bank_name'], 
                                'ifsc_code'=>$remitterInfo['ifsc_code'],
                                'bank_account_number'=>$remitterInfo['bank_account_number'],
                                'branch_name'=>$remitterInfo['branch_name'], 
                                'branch_city'=>$remitterInfo['branch_city'],
                                'branch_address'=>$remitterInfo['branch_address'],  
                                'bank_account_type'=>$remitterInfo['bank_account_type'],
                                'address'=>$remitterInfo['address'],
                                'address_line2'=>$remitterInfo['address_line2'],
                                'state'=>$state->getStateCode($remitterInfo['state']),
                                'city'=>$remitterInfo['city'],
                                'pincode'=>$remitterInfo['pincode'],
                                'mobile_country_code'=>$remitterInfo['mobile_country_code'],
                                'mobile'=>$remitterInfo['mobile'],                            
                                'dob'=>$remitterInfo['dob'],                                                       
                                'mother_maiden_name'=>$remitterInfo['mother_maiden_name'],                                                       
                                'email'=>$remitterInfo['email'],
                                'cty'=>$remitterInfo['city'],
                                'pin'=>$remitterInfo['pincode']
                                );
                $form->populate($remitterData);
                $this->view->remitterData = $remitterData;
        }
            
        $this->view->remitter_auth = $session->remitter_auth;
        $this->view->errorExists = $errorExists;
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
		
   protected function _prepareRemiterDataToRblApi($remitterData,$bcagent) {
   	$user = Zend_Auth::getInstance()->getIdentity();
   	
	App_Logger::log("REM-REG : Before preparing remitter data for agentId: ". $user->id ." ,bcagent: " .$user->bcagent, Zend_Log::INFO);
   		   $session = new Zend_Session_Namespace("App.Agent.Controller");
	 return array('header' => array('sessiontoken' => $session->rblSessionID),
//								'bcagent' => 'TRA1000189',
								'bcagent' => $bcagent,
								'remittermobilenumber' => $remitterData['mobile'],
								'remittername' => $remitterData['name'].' '.$remitterData['middle_name'].' '.$remitterData['last_name'],
								'remitteraddress' => $remitterData['address'],
								'remitteraddress1' => $remitterData['address'],
								'pincode' => $remitterData['pincode'],
								'cityname' => $remitterData['city'],
								'statename' => $remitterData['state'],
								'alternatenumber' => $remitterData['mobile'],
								'idproof' => '',
								'idproofnumber' => '',
								'idproofissuedate' => '',
								'idproofexpirydate' => '',
								'idproofissueplace' => '',
								'lremitteraddress' => $remitterData['address'],
								'lpincode' => $remitterData['pincode'],
								'lstatename' => $remitterData['state'],
								'lcityname' => $remitterData['city']);
  }
	// create remitter in the RBL
	protected function remitterRegistrationRemittance($data) {
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$rblApiObject = new App_Rbl_Api();
		App_Logger::log("REM-REG : Request is : ". implode($data) ." agentId: ". $user->id ." ,bcagent: " .$user->bcagent, Zend_Log::INFO);
		
		$rblRemiterRegisterRespose = $rblApiObject->remitterRegistrationRemittance($data);
		App_Logger::log("REM-REG : Response is : ". implode($rblRemiterRegisterRespose) ." agentId: ". $user->id ." ,bcagent: " .$user->bcagent, Zend_Log::INFO);
		
		return $rblRemiterRegisterRespose;
	}
	
	private function getRemitterDetails($remitterData,$bcagent){
		error_log("Getting remitter details from rbl");
		
		$session = new Zend_Session_Namespace("App.Agent.Controller");
		$data = array('header' => array('sessiontoken' => $session->rblSessionID),
				'bcagent' => $bcagent,
				'mobilenumber' => $remitterData['mobile'],
				'flag' => 1);
		
		$rblApiObject = new App_Rbl_Api();
		$rblRemitterDetails = $rblApiObject->remitterDetails($data);
		return $rblRemitterDetails;
	}
	
	// save rbl Remitter id in the datbase.
	protected function saveRemitterID($id,$where) {
		$remitterModelObject = new Remit_Ratnakar_Remitter();
		return $remitterModelObject->updateRemitter(array('remitterid' => $id),$where);
	}



     /**
      * Ratnakar Remitter Transactions
      * This will display Ratnakar Remitter Transaction on the basis of remitter mobile number
      */
     public function transactionsAction() {

        // Get Ratnakar Remit Transaction Form
        $form = new Remit_Ratnakar_TransactionsForm(array('action' => $this->formatURL('/remit_ratnakar_remitter/transactions'),
                    'method' => 'post',
                ));

        $this->view->form = $form;
        $page = $this->_getParam('page');
        $formData = $this->_getAllParams();//Instead of getting it from POST we are getting it from REQUEST
        if (isset($formData['submit'])) {
            $ts = strtotime('today - ' . RATNAKAR_REMITTANCE_SELECT_DAYS_DATA . ' days');
            $phone = $formData['mobile'];
            $startDate = new DateTime();
            $startDate->setTimestamp($ts);
            $toDate = clone $startDate;
            $toDate->setTimestamp(time());
            $remitRatnakarRemitter = new Remit_Ratnakar_Remitter();
            $this->view->showBackLink = false;
            $form->populate($formData);
            $this->view->paginator = $remitRatnakarRemitter->getRemitterTransactionByPhone($phone, $startDate->format('Y-m-d'), $toDate->format('Y-m-d'), TRUE,$page);

        }elseif(isset($formData['mobile']) && isset($formData['id'])){
            $ts = strtotime('today - ' . RATNAKAR_REMITTANCE_SELECT_DAYS_DATA . ' days');
            $phone = $formData['mobile'];
            $startDate = new DateTime();
            $startDate->setTimestamp($ts);
            $toDate = clone $startDate;
            $toDate->setTimestamp(time());
            $remitRatnakarRemitter = new Remit_Ratnakar_Remitter();
            $form->mobile->setValue($formData['mobile']);
            $this->view->showBackLink = TRUE;
            $form->populate($formData);
            $this->view->paginator = $remitRatnakarRemitter->getRemitterTransactionByPhone($phone, $startDate->format('Y-m-d'), $toDate->format('Y-m-d'), TRUE,$page,$formData['id']);
        }

        $this->view->formData = $formData;
    }
	
	

    public function requeryAction() {
	    $this->title = 'Re-Query';
		$remitterModel = new Remit_Ratnakar_Remittancerequest();
		$isPost = $this->_getParam('postTxn',false);
		
		if($isPost){
			$this->postAction();
		}else{
			$remitter_request_id = $this->_getParam('transid',false);
			if($remitter_request_id) {
				if($remitterModel->reQuery($remitter_request_id)) {
					$this->_helper->FlashMessenger(array('msg-success' => 'Re-Query run successully'));
				} else {
					$this->_helper->FlashMessenger(array('msg-success' => 'Re-Query failed, please try again'));
				}
				$this->_redirect($this->formatURL('/remit_ratnakar_remitter/transactions'));
			} else {
				$remitterModel->reQuery();
				$this->_helper->FlashMessenger(array('msg-success' => 'Re-Query run successully'));
				$this->_redirect($this->formatURL('/remit_ratnakar_remitter/adddetails/'));
			}
		}
  	} 
  	
  	public function postAction() {
  		$this->title = 'Post';
  		$remitterModel = new Remit_Ratnakar_Remittancerequest();
  		$remitterModel->post();
  		$this->_helper->FlashMessenger(array('msg-success' => 'Post run successully'));
  		$this->_redirect($this->formatURL('/remit_ratnakar_remitter/adddetails/'));
  	}

}



