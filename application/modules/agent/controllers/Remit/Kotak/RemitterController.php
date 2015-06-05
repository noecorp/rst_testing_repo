<?php

/**
 * That RemitterController is responsible for all remit operations at partner portal.
 */

class Remit_Kotak_RemitterController extends App_Agent_Controller
{

	const TRANSACTION_NOT_FOUND_ERROR_CODE = 'KPYERR15';

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
         
         //echo 'from add details.'; exit;
        $m = new App\Messaging\Remit\Kotak\Agent();
        $formData  = $this->_request->getPost();
        $user = Zend_Auth::getInstance()->getIdentity();   
        $session = new Zend_Session_Namespace('App.Agent.Controller'); 
        unset($session->remitter_id);
       
        $objRemitterModel = new Remit_Kotak_Remitter();
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
        $form = new Remit_Kotak_AddRemitterDetailsForm(array(
                                                'action' => $this->formatURL('/remit_kotak_remitter/adddetails'),
                                                'method' => 'post',
                                                'name'=>'frmAdddetails',
                                                'id'=>'frmAdddetails'
                                        ));  
       
        $this->view->form = $form;
        $bank = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        $product = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_REMIT);
        $productUnicode = $product->product->unicode;
        $productList = $products->getAgentProducts($user->id, PROGRAM_TYPE_REMIT ,$productUnicode);       
        $form->getElement("product_id")->setMultiOptions($productList);
        $dob = isset($formData['dob'])?$formData['dob']:'';
        $btnAuth = isset($formData['send_auth_code'])?$formData['send_auth_code']:'0';   
        $btnAdd = isset($formData['btn_add'])?$formData['btn_add']:'';
        $state = new CityList(); 
        //echo $session->remitter_auth;
        if($btnAuth != 1 && !$btnAdd){
             unset($session->remitter_auth);
        }
        // sending authrization code on mobile
        if( $btnAuth== 1 ){  
        			
        			
            
               try{
                 
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
                                      'fee'=>$regnFee,
                                      'product_name' => KOTAK_SHMART_TRANSFER,
                                      'name' => $formData['name'].' '.$formData['last_name'],
                                      'address' => substr($formData['address'], 0, 20)
                                     );                               

                    if($user->mobile1 == $userData['mobile1']) { 
                        $formData['pin'] = $formData['pincode'];
                        $formData['cty'] = $formData['city'];
                        $form->populate($formData);
                        $this->_helper->FlashMessenger(array(
                            'msg-error' => 'Remitter\'s mobile number can not same as Partner\'s mobile number.'
                        ));
                        $errorExists = true;
                    } else if( $userData['name'] != '' &&   $userData['address'] != '' ) {
                    /*if(isset($session->remitter_auth))
                        $resp = $m->remitterAuthKotak($userData,$resend = TRUE);
                    else
                         $resp = $m->remitterAuthKotak($userData);*/
                         
                     if(isset($session->remitter_auth))
                     {
                    		if(isset($formData['beni_name']) && $formData['beni_name']!='' && isset($formData['beni_nick_name']) && $formData['beni_nick_name'] != '')
                    		{
                    			$userData["beni_nick_name"]= $formData['beni_nick_name'];
					              	$resp = $m->remitterBeniAuthKotak($userData,$resend = FALSE);
					              }else
					              {
					              	$resp = $m->remitterAuthKotak($userData,$resend = FALSE);
					              }	
                    }else{
                    		if(isset($formData['beni_name']) && $formData['beni_name']!='' && isset($formData['beni_nick_name']) && $formData['beni_nick_name'] != '')
                    		{
                    			$userData["beni_nick_name"]= $formData['beni_nick_name'];
					              	$resp = $m->remitterBeniAuthKotak($userData);
					              }else{
					              	$resp = $m->remitterAuthKotak($userData);
					              }	
                         
                    }
                    
                    //$formData['dob'] = Util::returnDateFormatted($dob, "Y-m-d", "d-m-Y", "-"); 
                    $this->view->remitter_auth = $session->remitter_auth;                       
                    $session->remitter_mobile_number = $formData['mobile'];                       
                    $this->view->remitterData = $formData;
                   
                    $formData['cty'] = $formData['city'];
                    $formData['pin'] = $formData['pincode'];

                    $form->populate($formData);
                    $form->getElement("send_auth_code")->setValue("0"); 
                    $this->_helper->FlashMessenger( array('msg-success' => 'Authorization code has been sent on remitter mobile number.',) );
}
                    else{
                    $formData['cty'] = $formData['city'];
                    $formData['pin'] = $formData['pincode'];

                    $form->populate($formData);
                      $this->_helper->FlashMessenger( array('msg-error' => 'Please fill in remitter name and address',) );
//             
                    }
//           

                    //$session->cardholder_auth = 1;
                    //echo $session->cardholder_auth;
                }
        }catch (App_Exception $e ) {  
                $errorExists = true;
                $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                $formData['cty'] = $formData['city'];
                $formData['pin'] = $formData['pincode'];
                $form->populate($formData);
                $this->view->remitterData = $formData;
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
            } catch (Exception $e ) {  
                $errorExists = true;
                $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                $formData['cty'] = $formData['city'];
                $formData['pin'] = $formData['pincode'];
                $form->populate($formData);
                $this->view->remitterData = $formData;
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
            }   
        //}
      }
        //echo $session->remitter_auth.'--';  
        // adding details in db
        if($btnAdd){
            
           
            $form->getElement("beni_bank_state")->setMultiOptions(array($formData["beni_bank_state"] => $formData["beni_bank_state"]));
						$form->getElement("beni_branch_name")->setMultiOptions(array($formData["beni_branch_name"] => $formData["beni_branch_name"]));
						$form->getElement("beni_branch_city")->setMultiOptions(array($formData["beni_branch_city"] => $formData["beni_branch_city"]));  
						$formData['cty'] = $formData['city'];
            $formData['pin'] = $formData['pincode'];
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
                    $form->getElement("cty")->setValue($formData['city']);
                    $form->getElement("pin")->setValue($formData['pincode']);
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
                        
                        $remitterData = array(  
                                                'name'=>$formData['name'],
                                                'middle_name'=>$formData['middle_name'],
                                                'last_name'=>$formData['last_name'],
                                                'product_id'=>$formData['product_id'],
                                                'bank_name'=>$formData['bank_name'], 
                                                'ifsc_code'=>$formData['ifsc_code'],
                                                'bank_account_number'=>$formData['bank_account_number'],
                                                'branch_name'=>$formData['branch_name'], 
                                                'branch_city'=>$formData['branch_city'],
                                                'branch_address'=>$formData['branch_address'],  
                                                'bank_account_type'=>$formData['bank_account_type'],
                                                'address'=>$formData['address'],
                                                'address_line2'=>$formData['address_line2'],
                                                'city'=>$formData['city'],
                                                'state'=> $state->getStateName($formData['state']),
                                                'pincode'=>$formData['pincode'],
                                                'mobile_country_code'=>$formData['mobile_country_code'],
                                                'mobile'=>$formData['mobile'],                            
                                                'dob'=>$formData['dob'],                                                       
                                                'mother_maiden_name'=>$formData['mother_maiden_name'],                                                       
                                                'email'=>$formData['email'],
                                                'legal_id'=>$formData['legal_id'],
                                                'by_agent_id'=>$user->id,
                                                'ip'=>$ip,
                                                'date_created'=>date('Y-m-d H:i:s'),
                                                'status'=>STATUS_PENDING                                          
                                             ); 
                        if($user->static_code == FLAG_YES){
                        $remitterData['static_code'] = BaseUser::hashPassword($staticCode, 'agent');
                        }
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
                       
                        $uploadphoto->setDestination(UPLOAD_PATH_KOTAK_REMITTER_PHOTO);
                        
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
                                                        'bank_unicode' => $bank->bank->unicode
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
                  
                               $smsData = array('mobile1'=>$formData['mobile'], 'mobile_country_code'=>$formData['mobile_country_code'],'product_name' => KOTAK_SHMART_TRANSFER,'call_centre_number' =>KOTAK_CALL_CENTRE_NUMBER, 'customer_support_email' =>KOTAK_SHMART_EMAIL);
                
                               if($txnResponse && empty($e)){
                                    $smsData['status'] = FLAG_SUCCESS;
                                    $m->remitterRegistration($smsData);
                                    $beneficiary = new Remit_Kotak_Beneficiary();
                                		$remitterDettails = $objRemitterModel->getRemitter($formData['mobile']);
	                                  try{
					                                  $resppose = $beneficiary->getBeneficiaryAccountNo(array('bank_account_number'=>$formData['beni_bank_account_number'],'remitter_id'=>$remitterDettails['id'],'ifsc_code'=>trim($formData['beni_ifsc_code'])));
					                                  if($resppose && $formData['beni_name'] && $formData['beni_nick_name'])
					                                  {
																		            $data = array();
																		            $data['name'] = $formData['beni_name'];
																		            $data['nick_name'] = $formData['beni_nick_name'];
																		            $data['mobile'] = $formData['beni_mobile'];
																		            $data['email'] = $formData['beni_email'];
																		            $data['address_line1'] = $formData['beni_address_line1'];
																		            $data['address_line2'] = $formData['beni_address_line2'];
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
																		            if ($res > 0) {
                                                                                                                                                                $beneCode = Util::getBeneCodeFromId($res);
                                                                                                                                                                $beneficiary->updateBeneficiaryDetails(array('bene_code'=> $beneCode),$res);

																		                $userArr = array(
																		                    'mobile1' => $remitterDettails['mobile'],
																		                    'status' => FLAG_SUCCESS,
																		                    'nick_name' => $formData['beni_nick_name'],
																		                    'product_name' => KOTAK_SHMART_TRANSFER
																		                );
																		                $m->beneficiaryEnrollment($userArr);
																		        		}
																			      }
																		} catch (Exception $e ) { 
                                        App_Logger::log($e->getMessage(), Zend_Log::ERR); 
                                        $this->_helper->FlashMessenger(array( 'msg-error' => $e->getMessage(),));
                                    }	      		
																	  		
                                    $this->_helper->FlashMessenger(array( 'msg-success' => 'Remitter enrolled successully',));
                               } else {
                                    $smsData['status'] = FLAG_FAILURE;
                                    $m->remitterRegistration($smsData);
                                    if(empty($e))
                                        $this->_helper->FlashMessenger(array( 'msg-error' => 'Remitter enrollment failed',));
                               }
                               
                               $this->_redirect($this->formatURL('/remit_kotak_remitter/registrationcomplete/'));
                               
                               
                               
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
                $remitterData = array(  
                                        'name'=>$remitterInfo['name'],
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
    

     /**
      * Kotak Remitter Transactions
      * This will display Kotak Remitter Transaction on the basis of remitter mobile number
      */
     public function transactionsAction() {

        // Get Kotak Remit Transaction Form
        $form = new Remit_Kotak_TransactionsForm(array('action' => $this->formatURL('/remit_kotak_remitter/transactions'),
                    'method' => 'post',
                ));

        $this->view->form = $form;
        $page = $this->_getParam('page');
        //$formData = $this->getRequest()->getPost();
        $formData = $this->_getAllParams();//Instead of getting it from POST we are getting it from REQUEST
 

//if($form->isValid($this->getRequest()->getPost())){
        if (isset($formData['submit'])) {
            
            
          //  if ($form->isValid($formData)) {//Form Validation not required as need to display with paging as well
                $ts = strtotime('today - ' . KOTAK_REMITTANCE_SELECT_DAYS_DATA . ' days');
                $phone = $formData['mobile'];
                $startDate = new DateTime();
                $startDate->setTimestamp($ts);
                $toDate = clone $startDate;
                $toDate->setTimestamp(time());
                $remitKotakRemitter = new Remit_Kotak_Remitter();
                $this->view->showBackLink = false;
                $form->populate($formData);
                $this->view->paginator = $remitKotakRemitter->getRemitterTransactionByPhone($phone, $startDate->format('Y-m-d'), $toDate->format('Y-m-d'), TRUE,$page);
            //}
        }elseif($formData['mobile'] && $formData['id']){
          
        				
                $ts = strtotime('today - ' . KOTAK_REMITTANCE_SELECT_DAYS_DATA . ' days');
                $phone = $formData['mobile'];
                $startDate = new DateTime();
                $startDate->setTimestamp($ts);
                $toDate = clone $startDate;
                $toDate->setTimestamp(time());
                $remitKotakRemitter = new Remit_Kotak_Remitter();
                $form->mobile->setValue($formData['mobile']);
                $this->view->showBackLink = TRUE;
                $form->populate($formData);
                $this->view->paginator = $remitKotakRemitter->getRemitterTransactionByPhone($phone, $startDate->format('Y-m-d'), $toDate->format('Y-m-d'), TRUE,$page,$formData['id']);
        }

        $this->view->formData = $formData;
    }

    /**
     * Transaction Info
     * Kotak Remitter Transaction Info page
     */
    public function transactioninfoAction() {
        
        $traceNumber = $this->getRequest()->getParam('txn_code');
        $mobileNumber = $this->getRequest()->getParam('mobile');
        
        if (!empty($traceNumber)) {
            $newTraceNumber = Util::generateRandomNumber(10);
            $requestArr = array(
                'qbTraceNumber' => $traceNumber,
                'traceNumber' => $newTraceNumber
            );
            $api = new App_Api_Kotak_Remit_Transaction();
            $api->queryAccountAndValidate($requestArr);
            $queryResponse = $api->getAccountQueryResponse();
            $info['auth_status'] = $queryResponse['auth_status'];
            $info['resp_code'] = $api->getResponseCode();
            $info['resp_desc'] = $api->getMessage(FALSE);
            $info['mobile'] = $mobileNumber;
            
error_log('ResponseCode received from Kotak: '. $info['resp_code']);
            
            if(isset($info['resp_code']) && $info['resp_code'] == self::TRANSACTION_NOT_FOUND_ERROR_CODE){
            	$remitRequestId = $this->getRequest()->getParam('id');
            	 
            	$remittanceRequestObj = new Remit_Kotak_Remittancerequest();
            	$params = array(
            			'id' => $remitRequestId,
            			'status' => STATUS_FAILURE
            	);
            	 
            	$remittanceRequestObj->updateRemittanceRequest($params);
            	error_log('Updated kotak remit request to failure');
            }


            $this->view->showBackLink = TRUE;
            $this->view->info = $info;
        }
    }

}
