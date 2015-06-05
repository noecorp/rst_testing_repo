<?php
/**
 * Cardholder actions
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class Corp_Ratnakar_CardholderController extends App_Corporate_Controller
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
        $this->session = new Zend_Session_Namespace("App.Corporate.Controller");
        $user = Zend_Auth::getInstance()->getIdentity();
        if(!isset($user->id)) {
           $this->_redirect($this->formatURL('/profile/login'));
           exit;
        }
    }
    
    

    public function addAction(){
           
        $this->title = 'Add Cardholder';
        
        //$session = new Zend_Session_Namespace('App.Agent.Controller');
        $formData  = $this->_request->getPost();
        $formData['cty'] = isset($formData['city'])?$formData['city']:'';
        $formData['pin'] = isset($formData['pincode'])?$formData['pincode']:'';
        $formData['comm_cty'] = isset($formData['comm_city'])?$formData['comm_city']:'';
        $formData['comm_pin'] = isset($formData['comm_pincode'])?$formData['comm_pincode']:'';
        $formData['medi_assist_id'] = isset($formData['member_id'])?$formData['member_id']:'';
        $user = Zend_Auth::getInstance()->getIdentity();        
        $session = new Zend_Session_Namespace('App.Corporate.Controller');
        $config = App_DI_Container::get('ConfigObject');
        $uploadlimit = $config->corporate->uploadfile->size;
        $request = $this->getRequest();
       // $chModel  = new Mvc_Axis_CardholderUser(); 
        $objValidation = new Validator();
        $objCardholders = new Corp_Ratnakar_Cardholders();
        $objMobile = new Mobile();
        $objEmail = new Email();
        $objCRN = new CRN();
        $productModel = new Products();
        $docModel = new Documents();
        $errorExists = false;
       
        // Get our form and validate it
        $form = new Corp_Ratnakar_AddCardholderForm(array(
                                                    'action' => $this->formatURL('/corp_ratnakar_cardholder/add'),
                                                    'method' => 'post',
                                                    'name'=>'frmAdd',
                                                    'id'=>'frmAdd'
                                                ));                      
        $productInfo = $productModel->getCorporateProductsInfo($user->id);            
        if(!empty($productInfo)) 
        {
            $productArr = $this->filterProductArrayForForm($productInfo);
            $form->getElement('product_id')->setMultiOptions($productArr);
        }
        else{
            $this->_helper->FlashMessenger( array('msg-error' => 'No product assigned at this moment',) );
                   
        }
        $this->view->form = $form;         
         
        
        $dateOfBirth = isset($formData['date_of_birth'])?$formData['date_of_birth']:'';
        $formData['date_of_birth'] = Util::returnDateFormatted($dateOfBirth, "d-m-Y", "Y-m-d", "-");
        $btnAuth = isset($formData['send_auth_code'])?$formData['send_auth_code']:'0';   
        $resendAuth = isset($formData['resend_auth_code'])?$formData['resend_auth_code']:'';   
        $btnAdd = isset($formData['btn_add'])?$formData['btn_add']:'';
        
        if( $btnAuth== 1 && $btnAdd==""){  
          
            try{
          
                 $productOptionsArr = $productModel->getProductInfo($formData['product_id']);
                 $userData = array('mobile_country_code'=>$formData['mobile_country_code'], 
                                  'mobile1'=>$formData['mobile'],
                                  'mobile_number_old'=>$formData['mobile_number_old'],
                                  'product_name' => $productOptionsArr->name,
                                  'product_id' => $formData['product_id']
                    );                                        
          
                $objMsg = new App\Messaging\Corp\Ratnakar\Corporate();
                if(isset($session->corp_cardholder_auth)){
                    $resp = $objMsg->cardholderAuth($userData, $resend = TRUE);
                }else{
                    $resp = $objMsg->cardholderAuth($userData);
                }
                
                $formData['date_of_birth'] = $dateOfBirth;
                $this->view->corp_cardholder_auth = $session->corp_cardholder_auth;                       
                $session->corp_cardholder_mobile_number = $formData['mobile'];                       
                
                $this->_helper->FlashMessenger( array('msg-success' => 'Authorization code has been sent on your mobile number.',) );
                $form->populate($formData);
                $form->getElement("send_auth_code")->setValue("0");                
                
                //$session->cardholder_auth = 1;
                //echo $session->cardholder_auth;
            }catch (Exception $e ) {  
                    $errorExists = true;
                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                    $form->populate($formData);
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                }  
         }
        //echo $session->corp_cardholder_auth;
        
        // adding details in db
        if($btnAdd){
            
            if($form->isValid($this->getRequest()->getPost())){
                
                $aadhaarNo = isset($formData['aadhaar_no'])?trim($formData['aadhaar_no']):'';
                $pan = isset($formData['pan'])?trim($formData['pan']):'';
                $email = isset($formData['email'])?trim($formData['email']):'';
                $mobileNo = isset($formData['mobile'])?trim($formData['mobile']):'';
                $cardNo = isset($formData['card_number'])?trim($formData['card_number']):'';
                $afn = isset($formData['afn'])?trim($formData['afn']):'';
                
                
                $authValidated = isset($session->corp_validated_cardholder_auth)?$session->corp_validated_cardholder_auth:'0';
               // echo $session->corp_cardholder_auth."==".$formData['auth_code']; exit;
                //if(false){  
                if($authValidated!=1 && $session->corp_cardholder_auth != $formData['auth_code'] || ($session->corp_cardholder_mobile_number!=$formData['mobile']) ){ // matching the auth code
                     //$this->view->msg = $res;
                     $errorExists = true;
                     $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'Invalid Authorization Code',
                        )
                    );
                   // $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-"); 
                    $this->view->formData = $formData;
                    $form->getElement("send_auth_code")->setValue("0");
                } else {                        
                
                /*** checking for validation and duplication ***/
                    
                try{
                       
                        // card number check
                        if(!empty($cardNo)){
                            $cardNumCheck = $objCardholders->checkCardNumberDuplication($cardNo);
                        }
                        $crnObj = new CRNMaster();
                        $cardInfo = $crnObj->getCRNInfo($formData['card_number'],$formData['card_pack_id'],'');
                        if(!isset($cardInfo['id']) && empty($cardInfo['id'])){
                            $errorExists = true;
                           $objCardholders->setError('CARD PACK ID not found!');
                           
                        }
                        // afn check
                        if(!empty($afn)){
                            $afnCheck = $objCardholders->checkRatAFNDuplication($afn, $formData['product_id']);
                        }                       
                        // email check
                        $emailCheck = $objEmail->checkRatCardholderEmailDuplicate($email, $formData['product_id']);
                        
                               
                       // now getting the shmart crn
                        $shmartCRN = rand('012345','987654');   
                         $validateArr = array(
                           'tablename' => DbTable::TABLE_RAT_CORP_CARDHOLDER,
                           'product_id' => $formData['product_id'],
                           'col_value' => $formData['card_pack_id'],
                           'col_name' => 'card_pack_id',
                           'col' => 'Card Pack Id',
                           );
                           // card number check
                        if($formData['card_pack_id'] != ''){
                          $cardNumCheck = $objValidation->checkColDuplicacy($validateArr);
                        }
                        $crnObj = new CRNMaster();
                        $cardInfo = $crnObj->getCRNInfo('',$formData['card_pack_id'],'',$formData['product_id']);
                        if(!isset($cardInfo['id']) && empty($cardInfo['id'])){
                           $errorExists = true;
                           throw new Exception('CARD PACK ID not found');
                        }elseif(!empty($formData['card_number']) && $cardInfo['card_number'] != $formData['card_number']){
                           $errorExists = true;
                           throw new Exception('CARD PACK ID And Card Number combination not matched');
                        }else{
                           $crnObj->updateStatusByCardNumberNPackId(array('card_pack_id'=>$formData['card_pack_id'],'product_id'=>$formData['product_id'],'status'=>STATUS_USED, 'card_number' => $cardInfo['card_number']));
                        }

                        
                        if ($formData['aadhaar_no'] != '') {
                                $isAadhaarValid = $objValidation->validateUID($formData['aadhaar_no']);
                                if($isAadhaarValid)
                                   $isAadhaarValid = $objCardholders->checkRatAadhaarDuplication($formData['aadhaar_no'],  $formData['product_id']);
                        }
                        if ($formData['pan'] != '') { 
                             $isPanValid = $objValidation->validatePAN($formData['pan']);
                                if($isPanValid)
                                   $isPanValid = $objCardholders->checkRatPANDuplication($formData['pan'],  $formData['product_id']);
                        }
                         
                        if ($formData['Identification_type'] == 'uid') {
                                $isAadhaarValid = $objValidation->validateUID($formData['Identification_number']);
                                if($isAadhaarValid)
                                   $isAadhaarValid = $objCardholders->checkRatAadhaarDuplication($formData['Identification_number'],  $formData['product_id']);
                        }
                        if ($formData['Identification_type'] == 'pan') { 
                             $isPanValid = $objValidation->validatePAN($formData['Identification_number']);
                                if($isPanValid)
                                   $isPanValid = $objCardholders->checkRatPANDuplication($formData['Identification_number'],  $formData['product_id']);
                        }
                       
                        $idDocFile = isset($_FILES['id_doc_path']['name'])?$_FILES['id_doc_path']['name']:'';
                        $addrDocFile = isset($_FILES['address_doc_path']['name'])?$_FILES['address_doc_path']['name']:'';
                        if ($addrDocFile != '' || $idDocFile !='') {
                           $i=1;                
                           foreach($_FILES as $file_elem_name=>$file_info){
                             
                               if(trim($file_info['name'])!=''){
                                   $filenameArr = explode(".", $file_info['name']);   
                                   $ext = pathinfo($file_info['name'], PATHINFO_EXTENSION);
                                   $newFilename = $filenameArr[0].$i.'.'.$ext;
                                   $_FILES[$file_elem_name]['name'] = $newFilename;
                                   $i++;
                               }
                           }
                        }
                
                }
                catch (Exception $e ) { 
                    
                       // $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-"); 
                       
                    $errorExists = true;
                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                        $form->populate($formData);
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    } 

                if(!$errorExists){
                    try{
                        $bankProdInfo = $productModel->getProductInfo($formData['product_id']);
                        $bankId = isset($bankProdInfo['bank_id'])?$bankProdInfo['bank_id']:'';
                        $productId = isset($bankProdInfo['id'])?$bankProdInfo['id']:'';
                        // unicode will be from api function but taken temporarily here
                        $unicode = rand('123465','987456');                           
                       
                      //  $customerType = ($bankProdInfo['const'] == PRODUCT_CONST_RAT_CNY) ? TYPE_KYC : TYPE_NONKYC;
                        $customerType = TYPE_NONKYC;
                       // $status_ops = ($bankProdInfo['const'] == PRODUCT_CONST_RAT_CNY) ? STATUS_PENDING : STATUS_PENDING;
                        $status_ops = STATUS_APPROVED;
            
                        $status = ($formData['card_type'] == STATUS_ACTIVATION_PENDING) ? STATUS_ACTIVATION_PENDING : STATUS_ECS_PENDING;
                        $fname = ($formData['first_name'] != '')?$formData['first_name']:'' ;
                        $lname = ($formData['last_name'] != '')?' '.$formData['last_name']:'' ;
                        $name_on_card = $fname.$lname ;
                        $cardholderInfo = array(
                                                 'shmart_crn'=>'',
                                                 'card_number'=>$cardInfo['card_number'],
                                                 'card_pack_id'=>$cardInfo['card_pack_id'],
                                                 'CardNumber'=>$cardInfo['card_number'],
                                                 'CardPackId'=>$cardInfo['card_pack_id'],
                                                 'afn'=>$formData['afn'],
                                                 'MemberId'=>$formData['medi_assist_id'],
                                                 'medi_assist_id'=>$formData['medi_assist_id'],
                                                 'employee_id'=>$formData['employee_id'],
                                                 'employer_name'=>$formData['employer_name'],
                                                 'EmployerName'=>$formData['employer_name'],
                                                 'first_name'=>$formData['first_name'],
                                                 'FirstName'=>$formData['first_name'],
                                                 'middle_name'=>$formData['middle_name'],
                                                 'MiddleName'=>$formData['middle_name'],
                                                 'last_name'=>$formData['last_name'],
                                                 'LastName'=>$formData['last_name'],
                                                 'aadhaar_no'=>$formData['aadhaar_no'],
                                                 'pan'=>$formData['pan'],
                                                 'mobile_country_code'=>$formData['mobile_country_code'],
                                                 'mobile'=>$formData['mobile'],
                                                 'Mobile'=>$formData['mobile'],
                                                 'Email'=>$formData['email'],
                                                 'email'=>$formData['email'],
                                                 'Gender'=>$formData['gender'],
                                                 'gender'=>$formData['gender'],
                                                 'DateOfBirth'=>$formData['date_of_birth'],
                                                 'bank_id'=>$bankId,
                                                 'product_id'=>$productId,
                                                 'unicode'=>$unicode,
                                                 'corporate_id'=>$user->id,
                                                 'ProductId'=>$productId,
                                                 'customer_type' => $customerType,
                                                 'name_on_card' => $name_on_card,
                                                 'NameOnCard' => $formData['name_on_card'],
                                                 'mother_maiden_name' => $formData['mother_maiden_name'],
                                                 'MotherMaidenName' => $formData['mother_maiden_name'],
                                                 'AddressLine1' => $formData['address_line1'],
                                                 'address_line1' => $formData['address_line1'],
                                                 'address_line2' => $formData['address_line2'],
                                                 'AddressLine2' => $formData['address_line2'],
                                                 'State' => $formData['state'],
                                                 'City' => $formData['city'],
                                                 'Pincode' => $formData['pincode'],
                                                 'corporate_id'=>$formData['corporate_id'],
                                                 'by_corporate_id'=> $user->id,
                                                 'corp_address_line1' => $formData['comm_address_line1'],
                                                 'EmployerAddressLine1' => $formData['comm_address_line1'],
                                                 'corp_address_line2' => $formData['comm_address_line2'],
                                                 'EmployerAddressLine2' => $formData['comm_address_line2'],
                                                 'corp_state' => $formData['comm_state'],
                                                 'EmployerState' => $formData['comm_state'],
                                                 'corp_city' => $formData['comm_city'],
                                                 'EmployerCity' => $formData['comm_city'],
                                                 'corp_pin' => $formData['comm_pincode'],
                                                 'EmployerPin' => $formData['comm_pincode'],
                                                 'id_proof_type' => $formData['id_proof_type'],
                                                 'IdentityProofType' => $formData['id_proof_type'],
                                                 'id_proof_number' => $formData['id_proof_number'],
                                                 'IdentityProofDetail' => $formData['id_proof_number'],
                                                 'address_proof_type' => $formData['address_proof_type'],
                                                 'address_proof_number' => $formData['address_proof_number'],
                                                 'status' => $status,
                                                 'status_ops' => $status_ops,
                                                 'status_ecs' => STATUS_WAITING,
						 'channel' => CHANNEL_CORPORATE
                                               );
                            $idDocFile = isset($_FILES['id_doc_path']['name'])?$_FILES['id_doc_path']['name']:'';
                            $addrDocFile = isset($_FILES['address_doc_path']['name'])?$_FILES['address_doc_path']['name']:'';
                            if(trim($idDocFile)=='')
                                unset($_FILES['id_doc_path']);
                            if(trim($addrDocFile)=='')
                                unset($_FILES['address_doc_path']);
                            $upload = new Zend_File_Transfer_Adapter_Http(); 
                            if ($addrDocFile != '' || $idDocFile !='') {
                      
                                    //upload files
                                        
                                        
                                    // Add Validators for uploaded file's extesion , mime type and size
                                    $upload->addValidator('Extension', false, array('jpg', 'jpeg','pdf','png'=>false))
                                            ->addValidator('FilesSize', false, array('min' => '1kB', 'max' => $uploadlimit));
                                       
                                    $upload->setDestination(UPLOAD_PATH_RAT_CORP_DOC);
                                        
                                    try{
                                            
                                        //All validations correct then upload file
                                        if($upload->isValid()){
                                            // upload received file(s)
                                            $upload->receive();
                                            
                                           
                                            $this->_helper->FlashMessenger(
                                                array(
                                                    'msg-success' => 'File uploaded successfully',
                                                )
                                            );
                                        }else {
                                            $this->_helper->FlashMessenger(
                                                array(
                                                        'msg-error' => 'Documents could not be uploaded. Allowed Formats are Jpeg,Jpg,Pdf only.',
                                                     )
                                            );
                                            $errorExists = TRUE;
                                        }
                                
                                    }catch (Zend_File_Transfer_Exception $e) { 
                                          //  $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-");  
                                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                            $this->_helper->FlashMessenger(
                                                    array(
                                                        'msg-error' => $e->getMessage(),
                                                    )
                                            );
                                             $errorExists = TRUE;
                                    }
                                            
                            }    
                            if($errorExists == FALSE)  {    
                               
                                $customerAddResp = $objCardholders->addCorpCustomer($cardholderInfo);
                                if($customerAddResp)
                                {
                                    $uploadedData = $form->getValues();
                    
                                    $id_doc_path = $this->_getParam('id_doc_path');
                                    $add_doc_path = $this->_getParam('address_doc_path'); 
                                    
                                    $nameId = $upload->getFileName('id_doc_path');
                                    $nameAdd = $upload->getFileName('address_doc_path');
                                    if(!empty($nameId)){
    
                                        $destId = $upload->getDestination('id_doc_path');
                                        $sizeId = $upload->getFileSize('id_doc_path');
                
                                        // get the file name and extension
                                        $extId = explode(".", $nameId);
                
                                        
                                        // add document details along with agent id to DB
                                        $dataID = array('doc_product_id' => $productId,'doc_cardholder_id' => $customerAddResp , 'by_corporate_id' => $user->id, 'ip' => $objCardholders->formatIpAddress(Util::getIP()),
                                         'doc_type' => $formData['id_proof_type'], 'file_name' => '', 'file_type' => $extId['1'], 'status' => STATUS_ACTIVE);
                
                                        $resId = $docModel->saveCustomerDocs($dataID);
                                         
                                        $renameFileId = $resId. '.' . $extId['1'];
                                        // rename the file and update the record
                                        $dataArrId = array('file_name' => $renameFileId);
                                        $updateId = $docModel->renameDocs($resId, $dataArrId);
                                        
                
                                        // Rename uploaded file using Zend Framework
                                        $fullFilePathId = $destId . '/' . $renameFileId;
                                        $filterFileRenameId = new Zend_Filter_File_Rename(array('target' => $fullFilePathId, 'overwrite' => true));
                                        $filterFileRenameId->filter($nameId);
                                           // rename the file and update the record
                                        $dataArrId = array('id_proof_doc_id' => $resId);
                                        $objCardholders->update($dataArrId ,"id = $customerAddResp");
                
                                    }
                                        /*** address doc upload case ***/
                                    if(!empty($nameAdd)){
                
                                        $destAdd = $upload->getDestination('address_doc_path');
                                        $sizeAdd = $upload->getFileSize('address_doc_path');
                    
                                        # Returns the mimetype for the 'doc_path' form element
                                        //$mimeType = $upload->getMimeType($doc_path);
                                        // get the file name and extension
                                        $extAdd = explode(".", $nameAdd);
                    
                    
                                        // add document details along with agent id to DB
                                        $dataAdd = array('doc_product_id' => $productId,'doc_cardholder_id' => $customerAddResp , 'by_corporate_id' => $user->id, 'ip' => $objCardholders->formatIpAddress(Util::getIP()),
                                             'doc_type' => $formData['address_proof_type'], 'file_name' => '', 'file_type' => $extId['1'], 'status' => STATUS_ACTIVE);
                    
                    
                                        $resAdd = $docModel->saveCustomerDocs($dataAdd);
                                         
                                        $renameFileAdd = $resAdd . '.' . $extAdd['1'];
                                      
                                          // rename the file and update the record
                                        $dataArrAdd = array('file_name' => $renameFileAdd);
                                        $updateAdd = $docModel->renameDocs($resAdd, $dataArrAdd);
                                         
                    
                                        // Rename uploaded file using Zend Framework
                                        $fullFilePathAdd = $destAdd . '/' . $renameFileAdd;
                    
                                        $filterFileRenameAdd = new Zend_Filter_File_Rename(array('target' => $fullFilePathAdd, 'overwrite' => true));
                                        $filterFileRenameAdd->filter($nameAdd);
                                        $dataArrId = array('address_proof_doc_id' => $resAdd);
                                        $objCardholders->update($dataArrId ,"id = $customerAddResp");
                                    }
                                    
                                     // IF BOTH FILE UPLOADED SUCCESSFULLY THEN CUSTOMER WILL BE KYC TYPE 
                                              if( (!empty($addrDocFile)) && !empty($idDocFile) ){
                                                  $customerType = TYPE_NONKYC;
                                                  $status_ops = STATUS_PENDING;
                                                  $dataArr = array('customer_type' => $customerType,'status_ops' => $status_ops);
                                        $objCardholders->update($dataArr ,"id = $customerAddResp");
                                              }
                                 }   
                            }
                    }catch (Exception $e ) { 
                       // $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-");
                        //echo "<pre>"; print_r($e); exit;
                        $errorExists = true;
                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                        $form->populate($formData);
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        //echo "in cardholderError"; exit;
                    }
               
                     /*** adding cardholder details in db over here ***/
                         
                     if(!$errorExists && $customerAddResp){
                         unset($session->corp_cardholder_auth);
                         unset($session->corp_cardholder_mobile_number);
                         unset($session->corp_validated_cardholder_auth);
                         //$form->populate();
                         
                          $this->_helper->FlashMessenger(
                             array(
                                 'msg-success' => 'Cardholder details have been added in our records',
                             )
                         );
                         $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/enrolledcardholder/')); 
                    }else{
                         $this->_helper->FlashMessenger(
                             array(
                                  'msg-error' => $objCardholders->getError()
                               )
                         );
                    }
               }
              }
            }
             $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-"); 
             $form->populate($formData);    
          } //  if form does not validate successfully
       
          
    }
    
    
/*    public function addAction(){
           
        $this->title = 'Add Cardholder';
        
        //$session = new Zend_Session_Namespace('App.Agent.Controller');
        $formData  = $this->_request->getPost();
        $formData['cty'] = isset($formData['city'])?$formData['city']:'';
        $formData['pin'] = isset($formData['pincode'])?$formData['pincode']:'';
        $formData['comm_cty'] = isset($formData['comm_city'])?$formData['comm_city']:'';
        $formData['comm_pin'] = isset($formData['comm_pincode'])?$formData['comm_pincode']:'';
        $formData['medi_assist_id'] = isset($formData['member_id'])?$formData['member_id']:'';
        $user = Zend_Auth::getInstance()->getIdentity();        
        $session = new Zend_Session_Namespace('App.Corporate.Controller');
        $config = App_DI_Container::get('ConfigObject');
        $uploadlimit = $config->corporate->uploadfile->size;
        $request = $this->getRequest();
       // $chModel  = new Mvc_Axis_CardholderUser(); 
        $objValidation = new Validator();
        $objCardholders = new Corp_Ratnakar_Cardholders();
        $objMobile = new Mobile();
        $objEmail = new Email();
        $objCRN = new CRN();
        $productModel = new Products();
        $docModel = new Documents();
        $errorExists = false;
       
        // Get our form and validate it
        $form = new Corp_Ratnakar_AddCardholderForm(array(
                                                    'action' => $this->formatURL('/corp_ratnakar_cardholder/add'),
                                                    'method' => 'post',
                                                    'name'=>'frmAdd',
                                                    'id'=>'frmAdd'
                                                ));                      
        $productInfo = $productModel->getCorporateProductsInfo($user->id);            
        if(!empty($productInfo)) 
        {
            $productArr = $this->filterProductArrayForForm($productInfo);
            $form->getElement('product_id')->setMultiOptions($productArr);
        }
        else{
            $this->_helper->FlashMessenger( array('msg-error' => 'No product assigned at this moment',) );
                   
        }
        $this->view->form = $form;         
         
        
        $dateOfBirth = isset($formData['date_of_birth'])?$formData['date_of_birth']:'';
        $formData['date_of_birth'] = Util::returnDateFormatted($dateOfBirth, "d-m-Y", "Y-m-d", "-");
        $btnAuth = isset($formData['send_auth_code'])?$formData['send_auth_code']:'0';   
        $resendAuth = isset($formData['resend_auth_code'])?$formData['resend_auth_code']:'';   
        $btnAdd = isset($formData['btn_add'])?$formData['btn_add']:'';
        
        if( $btnAuth== 1 && $btnAdd==""){  
          
            try{
          
                 $productOptionsArr = $productModel->getProductInfo($formData['product_id']);
                 $userData = array('mobile_country_code'=>$formData['mobile_country_code'], 
                                  'mobile1'=>$formData['mobile'],
                                  'mobile_number_old'=>$formData['mobile_number_old'],
                                  'product_name' => $productOptionsArr->name,
                                  'product_id' => $formData['product_id']
                    );                                        
          
                $objMsg = new App\Messaging\Corp\Ratnakar\Corporate();
                if(isset($session->corp_cardholder_auth)){
                    $resp = $objMsg->cardholderAuth($userData, $resend = TRUE);
                }else{
                    $resp = $objMsg->cardholderAuth($userData);
                }
                
                $formData['date_of_birth'] = $dateOfBirth;
                $this->view->corp_cardholder_auth = $session->corp_cardholder_auth;                       
                $session->corp_cardholder_mobile_number = $formData['mobile'];                       
                
                $this->_helper->FlashMessenger( array('msg-success' => 'Authorization code has been sent on your mobile number.',) );
                $form->populate($formData);
                $form->getElement("send_auth_code")->setValue("0");                
                
                //$session->cardholder_auth = 1;
                //echo $session->cardholder_auth;
            }catch (Exception $e ) {  
                    $errorExists = true;
                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                    $form->populate($formData);
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                }  
         }
        //echo $session->corp_cardholder_auth;
        
        // adding details in db
        if($btnAdd){
            
            if($form->isValid($this->getRequest()->getPost())){
                
                $aadhaarNo = isset($formData['aadhaar_no'])?trim($formData['aadhaar_no']):'';
                $pan = isset($formData['pan'])?trim($formData['pan']):'';
                $email = isset($formData['email'])?trim($formData['email']):'';
                $mobileNo = isset($formData['mobile'])?trim($formData['mobile']):'';
                $cardNo = isset($formData['card_number'])?trim($formData['card_number']):'';
                $afn = isset($formData['afn'])?trim($formData['afn']):'';
                
                
                $authValidated = isset($session->corp_validated_cardholder_auth)?$session->corp_validated_cardholder_auth:'0';
               // echo $session->corp_cardholder_auth."==".$formData['auth_code']; exit;
                //if(false){  
                if($authValidated!=1 && $session->corp_cardholder_auth != $formData['auth_code'] || ($session->corp_cardholder_mobile_number!=$formData['mobile']) ){ // matching the auth code
                     //$this->view->msg = $res;
                     $errorExists = true;
                     $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'Invalid Authorization Code',
                        )
                    );
                   // $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-"); 
                    $this->view->formData = $formData;
                    $form->getElement("send_auth_code")->setValue("0");
                } else {                        
                
                    
                try{
                       
                        // card number check
                        if(!empty($cardNo)){
                            $cardNumCheck = $objCardholders->checkCardNumberDuplication($cardNo);
                        }
                        
                        // afn check
                        if(!empty($afn)){
                            $afnCheck = $objCardholders->checkRatAFNDuplication($afn, $formData['product_id']);
                        }                       
                        // email check
                        $emailCheck = $objEmail->checkRatCardholderEmailDuplicate($email, $formData['product_id']);
                        
                               
                       // now getting the shmart crn
                          $crnFuncCallCounter=0;
                          $shmartCRN='';
                          while($shmartCRN=='' && $crnFuncCallCounter<5){
                               $shmartCRN = rand('012345','987654');   
                               $crnFuncCallCounter++;                        
                         }
                        
                        
                        if ($formData['aadhaar_no'] != '') {
                                $isAadhaarValid = $objValidation->validateUID($formData['aadhaar_no']);
                                if($isAadhaarValid)
                                   $isAadhaarValid = $objCardholders->checkRatAadhaarDuplication($formData['aadhaar_no'],  $formData['product_id']);
                        }
                        if ($formData['pan'] != '') { 
                             $isPanValid = $objValidation->validatePAN($formData['pan']);
                                if($isPanValid)
                                   $isPanValid = $objCardholders->checkRatPANDuplication($formData['pan'],  $formData['product_id']);
                        }
                         
                        if ($formData['Identification_type'] == 'uid') {
                                $isAadhaarValid = $objValidation->validateUID($formData['Identification_number']);
                                if($isAadhaarValid)
                                   $isAadhaarValid = $objCardholders->checkRatAadhaarDuplication($formData['Identification_number'],  $formData['product_id']);
                        }
                        if ($formData['Identification_type'] == 'pan') { 
                             $isPanValid = $objValidation->validatePAN($formData['Identification_number']);
                                if($isPanValid)
                                   $isPanValid = $objCardholders->checkRatPANDuplication($formData['Identification_number'],  $formData['product_id']);
                        }
                       
                        $idDocFile = isset($_FILES['id_doc_path']['name'])?$_FILES['id_doc_path']['name']:'';
                        $addrDocFile = isset($_FILES['address_doc_path']['name'])?$_FILES['address_doc_path']['name']:'';
                        if ($addrDocFile != '' || $idDocFile !='') {
                           $i=1;                
                           foreach($_FILES as $file_elem_name=>$file_info){
                             
                               if(trim($file_info['name'])!=''){
                                   $filenameArr = explode(".", $file_info['name']);   
                                   $ext = pathinfo($file_info['name'], PATHINFO_EXTENSION);
                                   $newFilename = $filenameArr[0].$i.'.'.$ext;
                                   $_FILES[$file_elem_name]['name'] = $newFilename;
                                   $i++;
                               }
                           }
                        }
                
                }
                catch (Exception $e ) { 
                    
                       // $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-"); 
                       
                    $errorExists = true;
                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                        $form->populate($formData);
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    } 

                if(!$errorExists){
                    try{
                        $bankProdInfo = $productModel->getProductInfo($formData['product_id']);
                        $bankId = isset($bankProdInfo['bank_id'])?$bankProdInfo['bank_id']:'';
                        $productId = isset($bankProdInfo['id'])?$bankProdInfo['id']:'';
                        // unicode will be from api function but taken temporarily here
                        $unicode = rand('123465','987456');                           
                       
                      //  $customerType = ($bankProdInfo['const'] == PRODUCT_CONST_RAT_CNY) ? TYPE_KYC : TYPE_NONKYC;
                        $customerType = TYPE_NONKYC;
                       // $status_ops = ($bankProdInfo['const'] == PRODUCT_CONST_RAT_CNY) ? STATUS_PENDING : STATUS_PENDING;
                        $status_ops = STATUS_PENDING;
            
                        $status = ($formData['card_type'] == STATUS_ACTIVATION_PENDING) ? STATUS_ACTIVATION_PENDING : STATUS_ECS_PENDING;
                        $cardholderInfo = array(
                                                 'shmart_crn'=>'',
                                                 'card_number'=>$formData['card_number'],
                                                 'card_pack_id'=>$formData['card_pack_id'],
                                                 'CardNumber'=>$formData['card_number'],
                                                 'CardPackId'=>$formData['card_pack_id'],
                                                 'afn'=>$formData['afn'],
                                                 'MemberId'=>$formData['medi_assist_id'],
                                                 'medi_assist_id'=>$formData['medi_assist_id'],
                                                 'employee_id'=>$formData['employee_id'],
                                                 'employer_name'=>$formData['employer_name'],
                                                 'EmployerName'=>$formData['employer_name'],
                                                 'first_name'=>$formData['first_name'],
                                                 'FirstName'=>$formData['first_name'],
                                                 'middle_name'=>$formData['middle_name'],
                                                 'MiddleName'=>$formData['middle_name'],
                                                 'last_name'=>$formData['last_name'],
                                                 'LastName'=>$formData['last_name'],
                                                 'aadhaar_no'=>$formData['aadhaar_no'],
                                                 'pan'=>$formData['pan'],
                                                 'mobile_country_code'=>$formData['mobile_country_code'],
                                                 'mobile'=>$formData['mobile'],
                                                 'Mobile'=>$formData['mobile'],
                                                 'Email'=>$formData['email'],
                                                 'email'=>$formData['email'],
                                                 'Gender'=>$formData['gender'],
                                                 'gender'=>$formData['gender'],
                                                 'DateOfBirth'=>$formData['date_of_birth'],
                                                 'bank_id'=>$bankId,
                                                 'product_id'=>$productId,
                                                 'unicode'=>$unicode,
                                                 'corporate_id'=>$user->id,
                                                 'ProductId'=>$productId,
                                                 'customer_type' => $customerType,
                                                 'name_on_card' => $formData['name_on_card'],
                                                 'NameOnCard' => $formData['name_on_card'],
                                                 'mother_maiden_name' => $formData['mother_maiden_name'],
                                                 'MotherMaidenName' => $formData['mother_maiden_name'],
                                                 'AddressLine1' => $formData['address_line1'],
                                                 'address_line1' => $formData['address_line1'],
                                                 'address_line2' => $formData['address_line2'],
                                                 'AddressLine2' => $formData['address_line2'],
                                                 'State' => $formData['state'],
                                                 'City' => $formData['city'],
                                                 'Pincode' => $formData['pincode'],
                                                 'corporate_id'=>$formData['corporate_id'],
                                                 'by_corporate_id'=> $user->id,
                                                 'corp_address_line1' => $formData['comm_address_line1'],
                                                 'EmployerAddressLine1' => $formData['comm_address_line1'],
                                                 'corp_address_line2' => $formData['comm_address_line2'],
                                                 'EmployerAddressLine2' => $formData['comm_address_line2'],
                                                 'corp_state' => $formData['comm_state'],
                                                 'EmployerState' => $formData['comm_state'],
                                                 'corp_city' => $formData['comm_city'],
                                                 'EmployerCity' => $formData['comm_city'],
                                                 'corp_pin' => $formData['comm_pincode'],
                                                 'EmployerPin' => $formData['comm_pincode'],
                                                 'id_proof_type' => $formData['id_proof_type'],
                                                 'IdentityProofType' => $formData['id_proof_type'],
                                                 'id_proof_number' => $formData['id_proof_number'],
                                                 'IdentityProofDetail' => $formData['id_proof_number'],
                                                 'address_proof_type' => $formData['address_proof_type'],
                                                 'address_proof_number' => $formData['address_proof_number'],
                                                 'status' => $status,
                                                 'status_ops' => $status_ops,
                                                 'status_ecs' => STATUS_WAITING,
                                               );
                            $idDocFile = isset($_FILES['id_doc_path']['name'])?$_FILES['id_doc_path']['name']:'';
                            $addrDocFile = isset($_FILES['address_doc_path']['name'])?$_FILES['address_doc_path']['name']:'';
                            if(trim($idDocFile)=='')
                                unset($_FILES['id_doc_path']);
                            if(trim($addrDocFile)=='')
                                unset($_FILES['address_doc_path']);
                            $upload = new Zend_File_Transfer_Adapter_Http(); 
                            if ($addrDocFile != '' || $idDocFile !='') {
                      
                                    //upload files
                                        
                                        
                                    // Add Validators for uploaded file's extesion , mime type and size
                                    $upload->addValidator('Extension', false, array('jpg', 'jpeg','pdf','png'=>false))
                                            ->addValidator('FilesSize', false, array('min' => '5kB', 'max' => $uploadlimit));
                                       
                                    $upload->setDestination(UPLOAD_PATH_RAT_CORP_DOC);
                                        
                                    try{
                                            
                                        //All validations correct then upload file
                                        if($upload->isValid()){
                                            // upload received file(s)
                                            $upload->receive();
                                            
                                           
                                            $this->_helper->FlashMessenger(
                                                array(
                                                    'msg-success' => 'File uploaded successfully',
                                                )
                                            );
                                        }else {
                                            $this->_helper->FlashMessenger(
                                                array(
                                                        'msg-error' => 'Documents could not be uploaded. Allowed Formats are Jpeg,Jpg,Pdf only.',
                                                     )
                                            );
                                            $errorExists = TRUE;
                                        }
                                
                                    }catch (Zend_File_Transfer_Exception $e) { 
                                          //  $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-");  
                                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                            $this->_helper->FlashMessenger(
                                                    array(
                                                        'msg-error' => $e->getMessage(),
                                                    )
                                            );
                                             $errorExists = TRUE;
                                    }
                                            
                            }    
                            if($errorExists == FALSE)  {    
                               
                                $customerAddResp = $objCardholders->addCorpCustomer($cardholderInfo);
                                if($customerAddResp)
                                {
                                    $uploadedData = $form->getValues();
                    
                                    $id_doc_path = $this->_getParam('id_doc_path');
                                    $add_doc_path = $this->_getParam('address_doc_path'); 
                                    
                                    $nameId = $upload->getFileName('id_doc_path');
                                    $nameAdd = $upload->getFileName('address_doc_path');
                                    if(!empty($nameId)){
    
                                        $destId = $upload->getDestination('id_doc_path');
                                        $sizeId = $upload->getFileSize('id_doc_path');
                
                                        // get the file name and extension
                                        $extId = explode(".", $nameId);
                
                                        
                                        // add document details along with agent id to DB
                                        $dataID = array('doc_product_id' => $productId,'doc_cardholder_id' => $customerAddResp , 'by_corporate_id' => $user->id, 'ip' => $objCardholders->formatIpAddress(Util::getIP()),
                                         'doc_type' => $formData['id_proof_type'], 'file_name' => '', 'file_type' => $extId['1'], 'status' => STATUS_ACTIVE);
                
                                        $resId = $docModel->saveCustomerDocs($dataID);
                                         
                                        $renameFileId = $resId. '.' . $extId['1'];
                                        // rename the file and update the record
                                        $dataArrId = array('file_name' => $renameFileId);
                                        $updateId = $docModel->renameDocs($resId, $dataArrId);
                                        
                
                                        // Rename uploaded file using Zend Framework
                                        $fullFilePathId = $destId . '/' . $renameFileId;
                                        $filterFileRenameId = new Zend_Filter_File_Rename(array('target' => $fullFilePathId, 'overwrite' => true));
                                        $filterFileRenameId->filter($nameId);
                                           // rename the file and update the record
                                        $dataArrId = array('id_proof_doc_id' => $resId);
                                        $objCardholders->update($dataArrId ,"id = $customerAddResp");
                
                                    }
                                    if(!empty($nameAdd)){
                
                                        $destAdd = $upload->getDestination('address_doc_path');
                                        $sizeAdd = $upload->getFileSize('address_doc_path');
                    
                                        # Returns the mimetype for the 'doc_path' form element
                                        //$mimeType = $upload->getMimeType($doc_path);
                                        // get the file name and extension
                                        $extAdd = explode(".", $nameAdd);
                    
                    
                                        // add document details along with agent id to DB
                                        $dataAdd = array('doc_product_id' => $productId,'doc_cardholder_id' => $customerAddResp , 'by_corporate_id' => $user->id, 'ip' => $objCardholders->formatIpAddress(Util::getIP()),
                                             'doc_type' => $formData['address_proof_type'], 'file_name' => '', 'file_type' => $extId['1'], 'status' => STATUS_ACTIVE);
                    
                    
                                        $resAdd = $docModel->saveCustomerDocs($dataAdd);
                                         
                                        $renameFileAdd = $resAdd . '.' . $extAdd['1'];
                                      
                                          // rename the file and update the record
                                        $dataArrAdd = array('file_name' => $renameFileAdd);
                                        $updateAdd = $docModel->renameDocs($resAdd, $dataArrAdd);
                                         
                    
                                        // Rename uploaded file using Zend Framework
                                        $fullFilePathAdd = $destAdd . '/' . $renameFileAdd;
                    
                                        $filterFileRenameAdd = new Zend_Filter_File_Rename(array('target' => $fullFilePathAdd, 'overwrite' => true));
                                        $filterFileRenameAdd->filter($nameAdd);
                                        $dataArrId = array('address_proof_doc_id' => $resAdd);
                                        $objCardholders->update($dataArrId ,"id = $customerAddResp");
                                    }
                                    
                                     // IF BOTH FILE UPLOADED SUCCESSFULLY THEN CUSTOMER WILL BE KYC TYPE 
                                              if( (!empty($addrDocFile)) && !empty($idDocFile) ){
                                                  $customerType = TYPE_NONKYC;
                                                  $status_ops = STATUS_PENDING;
                                                  $dataArr = array('customer_type' => $customerType,'status_ops' => $status_ops);
                                        $objCardholders->update($dataArr ,"id = $customerAddResp");
                                              }
                                 }   
                            }
                    }catch (Exception $e ) { 
                       // $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-");
                        //echo "<pre>"; print_r($e); exit;
                        $errorExists = true;
                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                        $form->populate($formData);
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        //echo "in cardholderError"; exit;
                    }
                }
                    
                if(!$errorExists && $customerAddResp){
                    unset($session->corp_cardholder_auth);
                    unset($session->corp_cardholder_mobile_number);
                    unset($session->corp_validated_cardholder_auth);
                    //$form->populate();
                    
                     $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Cardholder details have been added in our records',
                        )
                    );
                    $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/enrolledcardholder/')); 
               }else{
                    $this->_helper->FlashMessenger(
                        array(
                             'msg-error' => $objCardholders->getError()
                          )
                    );
               }
              }
            }
             $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-"); 
             $form->populate($formData);    
          } //  if form does not validate successfully
       
          
    }
   */
 
    /*
     *Bulk upload
     *
     */
     
   public function uploadcardholdersAction() {
        $this->title = "Bulk Upload of Cardholders";
        $page = $this->_getParam('page');
        $form = new Corp_Ratnakar_CardholderuploadForm();
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $cardholdersModel = new Corp_Ratnakar_Cardholders();
        $this->view->records = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
        $productModel = new Products();
        $productInfo = $productModel->getCorporateProductsInfo($user->id);            
        if(!empty($productInfo)) 
        {
        $productArr = $this->filterProductArrayForForm($productInfo);
        $form->getElement('product_id')->setMultiOptions($productArr);
        }
        else{
         $this->_helper->FlashMessenger( array('msg-error' => 'No product assigned at this moment',) );
                   
        }

        if ($this->getRequest()->isPost() && $submit=='') {
            if ($form->isValid($this->getRequest()->getPost())) {
               $upload = new Zend_File_Transfer_Adapter_Http();
               $upload->receive();
               $batchName = $upload->getFileName('doc_path');
               
               $filename = $upload->getFileName('doc_path', $path = FALSE);
               $checkFile = $cardholdersModel->checkBatchFilename($filename, $formData['product_id']);
               
               //Add Validators for uploaded file's extesion , mime type and size
               $upload->addValidator('Extension', false, array('txt'));
               
               //check file extension...
                $upload->addValidator('Extension', false, array(FILE_TYPE_TXT, 'case' => false));
                
                $file_ext = Util::getFileExtension($batchName);
                if(strtolower($file_ext) != strtolower(FILE_TYPE_TXT))
                {
                   $this->_helper->FlashMessenger( array('msg-error' => 'Invalid file uploaded. Allowed Format is txt only.',) );
                   $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/uploadcardholders/'));
                }
               
                //check for same file name
                if (!$checkFile) 
                {
                    $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => "File already exists",
                        )
                     );
                }
                else
                {
                     $fp = fopen($batchName, 'r');
                     while (!feof($fp)) {
                         $line = fgets($fp);
                         if (!empty($line)) {
                            
                             $delimiter = CORP_CARDHOLDER_UPLOAD_DELIMITER;
                             $dataArr = str_getcsv($line, $delimiter);
                             //echo "<pre>"; print_r($dataArr); exit;
                             $dataArr['product_id'] = $formData['product_id'];
                             $consolidateArr[] = $dataArr;
                             $arrLength = Util::getArrayLength($dataArr);
                             if (!empty($dataArr)) {
                                 if ($arrLength != CORP_CARDHOLDER_UPLOAD_COLUMNS)
                                     $this->view->incorrectData = TRUE;
                                 try {
                                     // direct insert into rat_corp_cardholders
      
                                     if ($dataArr[CORP_CARDHOLDER_MANDATORY_FIELD_INDEX] == '') {
                                         $status = STATUS_INCOMPLETE;
                                     } else {
                                         $status = STATUS_TEMP;
                                     }
                                     $cardholdersModel->insertCardholderBatch($dataArr, $filename, $status);
                                 } catch (Exception $e) {
                                    echo $e->getMessage();
                                     App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                 }
      
                                
                             } else {
                                 $this->_helper->FlashMessenger(array('msg-error' => 'Data format is not correct'));
                             }
                         }
                     }
                     
                     $this->view->rejectpaginator = $cardholdersModel->showFailedPendingCardholderDetails($filename, $formData['product_id'],$page, $paginate = NULL,TRUE);
                     
                     $this->view->paginator = $cardholdersModel->showPendingCardholderDetails($filename, $formData['product_id'],$page, $paginate = NULL,TRUE);
                     $this->view->records = TRUE;
                     $this->view->batch_name = $filename;
                     $this->view->card_type = $formData['card_type'];
      
                     fclose($fp);
                }                  
            }     
        }

        if ($submit != '') {
            
            try {
                $statusECS = ($formData['crd_ype'] == STATUS_ACTIVATION_PENDING) ? STATUS_ACTIVATION_PENDING : STATUS_ECS_PENDING;
                
                $channel = CHANNEL_CORPORATE;
                $cardholdersModel->bulkAddCardholder($formData['reqid'], $formData['batch'],$statusECS,$channel);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Cardholder details have been updated in our records',
                        )
                );
                $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/uploadcardholders/'));
            } catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => $e->getMessage(),
                        )
                );
            }
        }
        $this->view->form = $form;
    }
     
     /* searchAction() will search the cardholders 
      * param: medi assis id, employer name, card number, mobile, email,aadhaar no, pan, 
      */
     
     public function searchAction(){
        $this->title = 'Search Cardholders';

        $data['medi_assist_id'] = $this->_getParam('medi_assist_id');
        $data['employer_name'] = $this->_getParam('employer_name');
        $data['card_number'] = $this->_getParam('card_number');
        $data['mobile'] = $this->_getParam('mobile');
        $data['email'] = $this->_getParam('email');
        $data['aadhaar_no'] = $this->_getParam('aadhaar_no');
        $data['pan'] = $this->_getParam('pan');
        $data['employee_id'] = $this->_getParam('employee_id');
        $param = $data;
        $data['submit_form'] = $this->_getParam('submit_form');
        $data['csrfhash'] = $this->_getParam('csrfhash');
        $data['formName'] = $this->_getParam('formName');
        $objCardholders = new Corp_Ratnakar_Cardholders();
        
        $form = new Corp_Ratnakar_CardholderSearchForm(array('action' => $this->formatURL('/corp_ratnakar_cardholder/search'),
                                                            'method' => 'POST',
                                                            'name'=>'frmSearch',
                                                            'id'=>'frmSearch',
                                                       ));
        
     
       if ($data['submit_form'] != '') {
            
           if($form->isValid($data)){ 
        
           
            $result = $objCardholders->getCardholderSearch($param, $this->_getPage()); 
            $this->view->paginator = $objCardholders->paginateByArray($result, $this->_getPage(), $paginate = NULL);
            $this->view->submit_form = $data['submit_form'];
         }
       }

       $this->view->form = $form;
       $this->view->formData = $data;
       $form->populate($data);
     }
    
  
     
     /**
     * activeAction will active the cardholder
     * param: it will accept the cardholder id with search cardholder querystring to return back search page
     */
    public function activeAction(){
        $this->title = 'Active Cardholder';
        
        $form = new Corp_Ratnakar_StatusActiveCardholderForm();
        $objCardholders = new Corp_Ratnakar_Cardholders();
        $user = Zend_Auth::getInstance()->getIdentity();
        $data['id'] = $this->_getParam('id');
        $data['medi_assist_id'] = $this->_getParam('medi_assist_id');
        $data['employer_name'] = $this->_getParam('employer_name');
        $data['card_number'] = $this->_getParam('card_number');
        $data['mobile'] = $this->_getParam('mobile');
        $data['email'] = $this->_getParam('email');
        $data['aadhaar_no'] = $this->_getParam('aadhaar_no');
        $data['pan'] = $this->_getParam('pan');
        $data['employee_id'] = $this->_getParam('employee_id');
        $data['csrfhash'] = $this->_getParam('csrfhash');
        $data['formName'] = $this->_getParam('formName');
        $data['remarks'] = $this->_getParam('remarks');
        $rowArr = array();
       
        $queryString = 'medi_assist_id='.$data['medi_assist_id'].'&employer_name='.$data['employer_name'];
        $queryString .= '&card_number='.$data['card_number'].'&mobile='.$data['mobile'].'&email='.$data['email'];
        $queryString .= '&aadhaar_no='.$data['aadhaar_no'].'&pan='.$data['pan'].'&employee_id='.$data['employee_id'];
        $queryString .= '&submit_form=Search Cardholder'.'&csrfhash='.$data['csrfhash'].'&formName='.$data['formName'];
        $redictUrl = $this->formatURL('/corp_ratnakar_cardholder/search?'.$queryString);
       // $form->$_cancelLinkUrl = $redictUrl;
        $form->setCancelLink($redictUrl);
        $row = $objCardholders->getCardholderInfo(array('cardholder_id'=>$data['id']));
        if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => sprintf('We cannot find Cardholder with id %s', $id),
                    )
                );
//                $this->_redirect('/agentsummary/');
                $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/search'.$queryString));
            }
            
        $rowArr = $row->toArray();
        
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
       
                $logData = array(
                                 'id'=>$data['id'],
                                 'card_number'=>$rowArr['card_number'],
                                 'medi_assist_id'=>$rowArr['medi_assist_id'],
                                 'employee_id'=>$rowArr['employee_id'],
                                 'cardholder_name'=>$rowArr['cardholder_name'],
                                 'aadhaar_no'=>$rowArr['aadhaar_no'],
                                 'pan'=>$rowArr['pan'],
                                 'gender'=>$rowArr['gender'],
                                 'mobile'=>$rowArr['mobile'],
                                 'email'=>$rowArr['email'],
                                 'employer_name'=>$rowArr['employer_name'],
                                 
                                );
                
                $dataOld = array_merge($logData, array('status'=>$rowArr['cardholder_status']));
                $dataNew = array_merge($logData, array('status'=>STATUS_ACTIVE));
                
                //echo $data['id']; exit;
                $objCardholders->updateCardholderById($dataOld, $dataNew, $user->id, $data['remarks']);
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Cardholder was successfully activated.',
                    )
                );
//                echo '/corp_ratnakar_cardholder/search'.$queryString;
//                exit;   
                $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/search?'.$queryString));
            }
        }else{
            
            
            
            
            
            $row['id'] = $id;
            $form->populate($row->toArray());
            $this->view->item = $row;
        }
        $this->view->form = $form;
        //$this->view->queryString = $this->formatURL('/agentsummary/index'.$queryString);
    }
    
     public function inactiveAction(){
        $this->title = 'Inactive Cardholder';
        
        $form = new Corp_Ratnakar_StatusDeactiveCardholderForm();
        $objCardholders = new Corp_Ratnakar_Cardholders();
        $user = Zend_Auth::getInstance()->getIdentity();
        $data['id'] = $this->_getParam('id');
        $data['medi_assist_id'] = $this->_getParam('medi_assist_id');
        $data['employer_name'] = $this->_getParam('employer_name');
        $data['card_number'] = $this->_getParam('card_number');
        $data['mobile'] = $this->_getParam('mobile');
        $data['email'] = $this->_getParam('email');
        $data['aadhaar_no'] = $this->_getParam('aadhaar_no');
        $data['pan'] = $this->_getParam('pan');
        $data['employee_id'] = $this->_getParam('employee_id');
        $data['csrfhash'] = $this->_getParam('csrfhash');
        $data['formName'] = $this->_getParam('formName');
        $data['remarks'] = $this->_getParam('remarks');
       
        $rowArr = array();
       
        $queryString = 'medi_assist_id='.$data['medi_assist_id'].'&employer_name='.$data['employer_name'];
        $queryString .= '&card_number='.$data['card_number'].'&mobile='.$data['mobile'].'&email='.$data['email'];
        $queryString .= '&aadhaar_no='.$data['aadhaar_no'].'&pan='.$data['pan'].'&employee_id='.$data['employee_id'];
        $queryString .= '&submit_form=Search Cardholder'.'&csrfhash='.$data['csrfhash'].'&formName='.$data['formName'];
        $redictUrl = $this->formatURL('/corp_ratnakar_cardholder/search?'.$queryString);
       // $form->$_cancelLinkUrl = $redictUrl;
        $form->setCancelLink($redictUrl);
        $row = $objCardholders->getCardholderInfo(array('cardholder_id'=>$data['id']));
        if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => sprintf('We cannot find Cardholder with id %s', $id),
                    )
                );
//                $this->_redirect('/agentsummary/');
                $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/search'.$queryString));
        }
            
        $rowArr = $row->toArray();
        
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
       
                $logData = array(
                                 'id'=>$data['id'],
                                 'card_number'=>$rowArr['card_number'],
                                 'medi_assist_id'=>$rowArr['medi_assist_id'],
                                 'employee_id'=>$rowArr['employee_id'],
                                 'cardholder_name'=>$rowArr['cardholder_name'],
                                 'aadhaar_no'=>$rowArr['aadhaar_no'],
                                 'pan'=>$rowArr['pan'],
                                 'gender'=>$rowArr['gender'],
                                 'mobile'=>$rowArr['mobile'],
                                 'email'=>$rowArr['email'],
                                 'employer_name'=>$rowArr['employer_name'],
                                 
                                ); 
                
                $dataOld = array_merge($logData, array('status'=>$rowArr['cardholder_status']));
                $dataNew = array_merge($logData, array('status'=>STATUS_INACTIVE));
                    
                //echo $data['id']; exit;
                $objCardholders->updateCardholderById($dataOld, $dataNew, $user->id, $data['remarks']);
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Cardholder was successfully deactivated.',
                    )
                );
//                echo '/corp_ratnakar_cardholder/search'.$queryString;
//                exit;   
                $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/search?'.$queryString));
            }
        }else{
            
            $id = $this->_getParam('id');
            $row = $objCardholders->getCardholderInfo(array('cardholder_id'=>$id));
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => sprintf('We cannot find Cardholder with id %s', $id),
                    )
                );
//                $this->_redirect('/agentsummary/');
                $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/search'.$queryString));
            }
            
            $row['id'] = $id;
            $form->populate($row->toArray());
            $this->view->item = $row;
        }
        $this->view->form = $form;
        //$this->view->queryString = $this->formatURL('/agentsummary/index'.$queryString);
    }
    
    public function batchstatusAction() {
        $this->title = 'Cardholder Batch Status';

        $data['batch_name'] = $this->_getParam('batch_name');
        $data['start_date'] = $this->_getParam('start_date');
        $data['end_date'] = $this->_getParam('end_date');
        $data['status'] = $this->_getParam('status');
        $data['sub'] = $this->_getParam('sub');
        $cardholdersModel = new Corp_Ratnakar_Cardholders();
        $page = $this->_getParam('page');
        $form = new Corp_Ratnakar_CardholderBatchForm(array('action' => $this->formatURL('/corp_ratnakar_cardholder/batchstatus'),
            'method' => 'POST',
        ));
        $user = Zend_Auth::getInstance()->getIdentity();
        $this->view->userId = $user->id;
        
        if ($data['sub'] != '') { ;
            if ($this->getRequest()->isPost()) {
                if ($form->isValid($this->getRequest()->getPost())) {
                   //echo "1"; exit;
                    if(isset($data['start_date']) && !empty($data['start_date'])) {
                        $startdate =  Util::returnDateFormatted($data['start_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    } else {
                        $startdate = '';
                    }
                    if(isset($data['end_date']) && !empty($data['end_date'])) {                    
                        $enddate = Util::returnDateFormatted($data['end_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    } else {
                        $enddate = '';
                    }
                    $batchdetails = $cardholdersModel->getBatchDetailsByDate(array(
                        'batch_name'  => $data['batch_name'],
                        'start_date'  => $startdate,
                        'end_date'  => $enddate,
                        'status'  => $data['status'],
                        'by_corporate_id'  => $user->id
                    ));
                    //echo "<pre>";print_r($batchdetails); exit;
                    $form->getElement('batch')->setValue($data['batch_name']);
                    $this->view->paginator = $cardholdersModel->paginateByArray($batchdetails, $page, $paginate = NULL);
                    $form->populate($data);
                }
            }
            else {//echo "2"; exit;
                    if(isset($data['start_date']) && !empty($data['start_date'])) {
                        $startdate = explode(' ', Util::returnDateFormatted($data['start_date'], "d-m-Y", "Y-m-d", "-"));
                    } else {
                        $startdate = '';
                    }
                    if(isset($data['end_date']) && !empty($data['end_date'])) {                    
                         $enddate = Util::returnDateFormatted($data['end_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    } else {
                        $enddate = '';
                    }                
                 $batchdetails = $cardholdersModel->getBatchDetailsByDate(array(
                        'batch_name'  => $data['batch_name'],
                        'start_date'  => $startdate,
                        'end_date'  => $enddate,
                        'status'  => $data['status'],
                        'by_corporate_id'  => $user->id
                    ));
                 
                $form->getElement('batch')->setValue($data['batch_name']);
                $this->view->paginator = $cardholdersModel->paginateByArray($batchdetails, $page, $paginate = NULL);
                $form->populate($data);
            }
        }
        
        //$this->view->backLink = 'searchCriteria=' . $data['searchCriteria'] . '&keyword=' . $data['keyword'] . '&sub=1';
        $this->view->controllerName = Zend_Registry::get('controllerName');
        $this->view->form = $form;
        $this->view->sub = $data['sub'];
        $this->view->batch_name = $data['batch_name'];
        $this->view->start_date = $data['start_date'];
        $this->view->status = $data['status'];
        $this->view->end_date = $data['end_date'];
        //$form->populate($data);
        
        
    }
     public function exportbatchstatusAction() {
        $this->title = 'Cardholder Batch Status';

        $data['batch_name'] = $this->_getParam('batch_name');
        $data['start_date'] = $this->_getParam('start_date');
        $data['end_date'] = $this->_getParam('end_date');
        $data['status'] = $this->_getParam('status');
        $data['sub'] = $this->_getParam('sub');
        $cardholdersModel = new Corp_Ratnakar_Cardholders();
         //echo "<pre>vijay"; print_r($data); exit; 
        
        $user = Zend_Auth::getInstance()->getIdentity();
        
        if(!empty($data)){
            //echo "1"; exit;
             if(isset($data['start_date']) && !empty($data['start_date'])) {
                 $startdate =  Util::returnDateFormatted($data['start_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
             } else {
                 $startdate = '';
             }
             if(isset($data['end_date']) && !empty($data['end_date'])) {                    
                 $enddate = Util::returnDateFormatted($data['end_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
             } else {
                 $enddate = '';
             }
             $exportData = $cardholdersModel->exportBatchDetailsByDate(array(
                 'batch_name'  => $data['batch_name'],
                 'start_date'  => $startdate,
                 'end_date'  => $enddate,
                 'status'  => $data['status'],
                 'by_corporate_id'  => $user->id
             ));
             //echo "<pre>vijay"; print_r($exportData); exit; 
            $columns = array(
                'Card NO',
                'Card Pack ID',
                'Appllication Form No.',
                'Member ID',
                'Employee ID',
                'First Name',
                'Middle Name',
                'Last Name',
                'Name on Card',
                'Gender',
                'Date of Birth',
                'Mobile Number',
                'Email ID',
                'Landline No.',
                'Address Line 1',
                'Address Line 2',
                'CITY / TOWN / VILLAGE',
                'PIN',
                'Mothers Maiden Name',
                'Employers Name',
                'Corporate ID',
                'Corporate Address Line 1',
                'Corporate Address Line 2',
                'Corporate CITY / TOWN / VILLAGE',
                'Corporate PIN',
                'Identity Proof',
                'Identity Proof - Details',
                'Address Proof',
                'Address Proof - Details',
                'Status',
                'Failed Reason'
                
            );

            $objCSV = new CSV();
            try{
                    $resp = $objCSV->export($exportData, $columns, 'ENROLLMENT_LOAD_SAMPLE');exit;
            }catch (Exception $e) {
                App_Logger::log($e->getMessage() , Zend_Log::ERR);
                $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                $this->_redirect($this->formatURL("/corp_ratnakar_reports/batchstatus?batch_name=".$data['batch_name']."&start_date=".$data['start_date']."&end_date=".$$data['end_date']."&status=".$data['status'])); 
            }
                
        } else {
            $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
            $this->_redirect($this->formatURL("/corp_ratnakar_reports/batchstatus?batch_name=".$data['batch_name']."&start_date=".$data['start_date']."&end_date=".$$data['end_date']."&status=".$data['status'])); 
        }    
        
    }
    
     private function filterProductArrayForForm($productInfo) {
        $productArr = array('' =>'Select Product');
        if (!empty($productInfo)) {
            foreach ($productInfo as $product) {
                $productArr[$product['product_id']] = $product['product_name'];
            }
        }
        return $productArr;
    }
    
    /**
     * Downloads a .txt file rather than to open the file directly into the browser 
     */
    public function downloadtxtfileAction()
    {
        
        $file_name = $this->_getParam('filename');
        $type = $this->_getParam('type');
        
        if($type != '')
        {
            $fileObject = new Files();

            $fileObject->setFilepath(UPLOAD_SAMPLE_PATH);

            $fileObject->setFilename($file_name);
            $fileObject->download();
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(TRUE);
        }
    }
    
    public function enrolledcardholderAction(){
        
    }
    
    public function opsrejectedAction() {
//error_reporting(1);
//ini_set("display_errors", 1); 

	$this->title = 'Operations Rejected Cardholder List';
	$user = Zend_Auth::getInstance()->getIdentity();
	$cardholderModel = new Corp_Ratnakar_Cardholders();
	$productModel = new Products();
	$form = new Corp_Ratnakar_OpsRejectedForm();
	$page = $this->_getParam('page');
	$formData  = $this->_request->getPost();
	$params = array('product_id' => $this->_getParam('product_id'));
	$productInfo = $productModel->getCorporateProductsInfo($user->id);
	if(!empty($productInfo))
	{
		$productArr = $this->filterProductArrayForForm($productInfo);
		$form->getElement('product_id')->setMultiOptions($productArr);
	}else{
		$this->_helper->FlashMessenger( array('msg-error' => 'No product assigned at this moment',) );
	}
	
	if ($formData['sub'] != '') {
            if(!empty($params['product_id'] )){
                    $this->view->paginator = $cardholderModel->showOpsrejectedCustomerDetails($page,$params);
            }
            else
            {
                    $this->_helper->FlashMessenger(
                                    array('msg-error' => 'Please select Product',)
                    );
            }
            $form->populate($params);
	}
	$this->view->form = $form;
	$this->view->sub = $formData['sub'];
    }
    
     public function viewAction() {

        $this->title = 'Customer Details';
        $cardholdersModel = new Corp_Ratnakar_Cardholders();
        $id = $this->_getParam('id');
        if (!is_numeric($id)) {
            $this->_helper->FlashMessenger(
                    array(
                        'error' => 'The user id you provided is invalid',
                    )
            );

            $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/opsrejected/'));
        }

        $row = $cardholdersModel->findById($id);

        $row->gender = ucfirst($row->gender);
        $row->date_of_birth = Util::returnDateFormatted($row->date_of_birth, "Y-m-d", "d-m-Y", "-");
        $row->date_failed = Util::returnDateFormatted($row->date_failed, "Y-m-d", "d-m-Y", "-");

        if (empty($row)) {
            $this->_helper->FlashMessenger(
                    array(
                        'error' => 'User with Id does not exist',
                    )
            );

            $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/opsrejected/'));
        }       
        
        //Select Cardholder's Purse
        $this->view->cardholderPurses = array();
        if (isset($row->rat_customer_id) && $row->rat_customer_id > 0) {
            $cardHolder = new Corp_Ratnakar_Cardholders();
            $this->view->cardholderPurses = $cardHolder->getRatCardholderPurses($row->rat_customer_id);
        }
       
        // Get status and comments
        $this->view->cardholderStatus = array();
        $cardHolderObj = new Corp_Ratnakar_CustomersLog();
        $this->view->cardholderStatus = $cardHolderObj->getCardholderStatus($row->id,$byType = BY_MAKER);
        $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/corp_ratnakar_cardholder/opsrejected';
        $this->view->item = $row;
    }

    public function editAction(){
           
        $this->title = 'Edit Cardholder';
        
        $formData  = $this->_request->getPost();
        $formData['cty'] = isset($formData['city'])?$formData['city']:'';
        $formData['pin'] = isset($formData['pincode'])?$formData['pincode']:'';
        $formData['comm_cty'] = isset($formData['corp_city'])?$formData['corp_city']:'';
        $formData['comm_pin'] = isset($formData['corp_pincode'])?$formData['corp_pincode']:'';
        $formData['medi_assist_id'] = isset($formData['medi_assist_id'])?$formData['medi_assist_id']:'';
        $user = Zend_Auth::getInstance()->getIdentity();        
        $session = new Zend_Session_Namespace('App.Corporate.Controller');
        $config = App_DI_Container::get('ConfigObject');
        $uploadlimit = $config->corporate->uploadfile->size;
        $objValidation = new Validator();
        $objCardholders = new Corp_Ratnakar_Cardholders();
        $objEmail = new Email();
        $productModel = new Products();
        $docModel = new Documents();
        $errorExists = false;
        $id = $this->_getParam('id');
        $custDetails = $objCardholders->findById($id);
	$custDetails = Util::toArray($custDetails);

        // Get our form and validate it
        $form = new Corp_Ratnakar_EditCardholderForm(array(
                                                    'action' => $this->formatURL('/corp_ratnakar_cardholder/edit?id='.$id),
                                                    'method' => 'post',
                                                    'name'=>'frmAdd',
                                                    'id'=>'frmAdd'
                                                ));    
        
        $productInfo = $productModel->getCorporateProductsInfo($user->id);            
        if(!empty($productInfo)) 
        {
            $productArr = $this->filterProductArrayForForm($productInfo);
            $form->getElement('product_id')->setMultiOptions($productArr);
        }
        else{
            $this->_helper->FlashMessenger( array('msg-error' => 'No product assigned at this moment',) ); 
        }
        $this->view->form = $form;     
        
        $dateOfBirth = isset($formData['date_of_birth']) ? $formData['date_of_birth'] : '';
        $formData['date_of_birth'] = Util::returnDateFormatted($dateOfBirth, "d-m-Y", "Y-m-d", "-");
        $btnAuth = isset($formData['send_auth_code']) ? $formData['send_auth_code'] : '0';   
        $resendAuth = isset($formData['resend_auth_code']) ? $formData['resend_auth_code'] : '';   
        $btnAdd = isset($formData['btn_add']) ? $formData['btn_add'] : '';
        
        if($btnAuth== 1 && $btnAdd==""){  
          
            try{
          
                 $productOptionsArr = $productModel->getProductInfo($formData['product_id']);
                 $userData = array('mobile_country_code'=>$formData['mobile_country_code'], 
                                  'mobile1'=>$formData['mobile'],
                                  'mobile_number_old'=>$formData['mobile_number_old'],
                                  'product_name' => $productOptionsArr->name,
                                  'product_id' => $formData['product_id']
                    );                                        
          
                $objMsg = new App\Messaging\Corp\Ratnakar\Corporate();
                if(isset($session->corp_cardholder_auth)){
                    $resp = $objMsg->cardholderAuth($userData, $resend = TRUE);
                }else{
                    $resp = $objMsg->cardholderAuth($userData);
                }
                
                $formData['date_of_birth'] = $dateOfBirth;
                $this->view->corp_cardholder_auth = $session->corp_cardholder_auth;                       
                $session->corp_cardholder_mobile_number = $formData['mobile'];                       
                
                $this->_helper->FlashMessenger( array('msg-success' => 'Authorization code has been sent on your mobile number.',) );
                $formData['comm_cty'] = $formData['corp_city'];
                $formData['comm_pin'] = $formData['corp_pincode'];
                $form->populate($formData);
                $form->getElement("send_auth_code")->setValue("0");                
            }catch (Exception $e ) {  
                    $errorExists = true;
                    $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'],"Y-m-d","d-m-Y", "-");
                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                    $form->populate($formData);
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                }  
         }
        
        // adding details in db
        if($btnAdd){
            if($form->isValid($this->getRequest()->getPost())){
                $email = isset($formData['email'])?trim($formData['email']):'';
                $cardNo = isset($formData['card_number'])?trim($formData['card_number']):'';
                $afn = isset($formData['afn'])?trim($formData['afn']):'';
                
                
                $authValidated = isset($session->corp_validated_cardholder_auth)?$session->corp_validated_cardholder_auth:'0';
                //if(false){  
                if($authValidated!=1 && $session->corp_cardholder_auth != $formData['auth_code'] || ($session->corp_cardholder_mobile_number!=$formData['mobile']) ){ // matching the auth code
                     //$this->view->msg = $res;
                     $errorExists = true;
                     $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'Invalid Authorization Code',
                        )
                    );
                    $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'],"Y-m-d","d-m-Y", "-");
                    $this->view->formData = $formData;
                    $form->getElement("send_auth_code")->setValue("0");
                } else {                        
                
                /*** checking for validation and duplication ***/
                    
                try{
                        // card number check
                        if(!empty($cardNo)){
                            $cardNumCheck = $objCardholders->checkCardNumberDuplication($cardNo);
                        }
                        
                        // afn check
                        if(!empty($afn)){
                            $afnCheck = $objCardholders->checkRatAFNDuplication($afn, $formData['product_id']);
                        }                       
                        // email check
                        $emailCheck = $objEmail->checkRatCardholderEmailDuplicate($email, $formData['product_id']);
                        
                               
                       // now getting the shmart crn
                          $crnFuncCallCounter=0;
                          $shmartCRN='';
                          while($shmartCRN=='' && $crnFuncCallCounter<5){
                               $shmartCRN = rand('012345','987654');   
                               $crnFuncCallCounter++;                        
                         }
                        
                        if ($formData['aadhaar_no'] != '') {
                            $isAadhaarValid = $objValidation->validateUID($formData['aadhaar_no']);
                            if($isAadhaarValid)
                                $isAadhaarValid = $objCardholders->checkRatAadhaarDuplication($formData['aadhaar_no'],  $formData['product_id']);
                            }
                        if ($formData['pan'] != '') { 
                             $isPanValid = $objValidation->validatePAN($formData['pan']);
                                if($isPanValid)
                                   $isPanValid = $objCardholders->checkRatPANDuplication($formData['pan'],  $formData['product_id']);
                        }
                         
                        if ($formData['id_proof_type'] == 'uid') {
                                $isAadhaarValid = $objValidation->validateUID($formData['id_proof_number']);
                                if($isAadhaarValid)
                                   $isAadhaarValid = $objCardholders->checkRatAadhaarDuplication($formData['id_proof_number'],  $formData['product_id']);
                        }
                        if ($formData['id_proof_type'] == 'pan') { 
                             $isPanValid = $objValidation->validatePAN($formData['id_proof_number']);
                                if($isPanValid)
                                   $isPanValid = $objCardholders->checkRatPANDuplication($formData['id_proof_number'],  $formData['product_id']);
                        }
                       
                        $idDocFile = isset($_FILES['id_doc_path']['name'])?$_FILES['id_doc_path']['name']:'';
                        $addrDocFile = isset($_FILES['address_doc_path']['name'])?$_FILES['address_doc_path']['name']:'';
                        if ($addrDocFile != '' || $idDocFile !='') {
                           $i=1;                
                           foreach($_FILES as $file_elem_name=>$file_info){
                             
                               if(trim($file_info['name'])!=''){
                                   $filenameArr = explode(".", $file_info['name']);   
                                   $ext = pathinfo($file_info['name'], PATHINFO_EXTENSION);
                                   $newFilename = $filenameArr[0].$i.'.'.$ext;
                                   $_FILES[$file_elem_name]['name'] = $newFilename;
                                   $i++;
                               }
                           }
                        }
                
                }
                catch (Exception $e ) { 
                    $errorExists = true;
                        //$this->_helper->FlashMessenger( array('msg-error' => $e->getMessage()) ); 
                        $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'],"Y-m-d","d-m-Y", "-");
                        $objCardholders->setError($e->getMessage());
                        $form->populate($formData);
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    } 

                if(!$errorExists){
                    try{
                        $bankProdInfo = $productModel->getProductInfo($formData['product_id']);
                        $bankId = isset($bankProdInfo['bank_id']) ? $bankProdInfo['bank_id'] : '';
                        $productId = isset($bankProdInfo['id']) ? $bankProdInfo['id'] : '';
                        $unicode = rand('123465','987456');                           
                       
                      //  $customerType = ($bankProdInfo['const'] == PRODUCT_CONST_RAT_CNY) ? TYPE_KYC : TYPE_NONKYC;
                        $customerType = TYPE_NONKYC;
                       // $status_ops = ($bankProdInfo['const'] == PRODUCT_CONST_RAT_CNY) ? STATUS_PENDING : STATUS_PENDING;
                        $status_ops = STATUS_APPROVED;
            
                        $status = ($formData['card_type'] == STATUS_ACTIVATION_PENDING) ? STATUS_ACTIVATION_PENDING : STATUS_ECS_PENDING;
                        $cardholderInfo = array(
                                                 'shmart_crn'=>'',
                                                 'card_number'=>$formData['card_number'],
                                                 'card_pack_id'=>$formData['card_pack_id'],
                                                 'afn'=>$formData['afn'],
                                                 'medi_assist_id'=>$formData['medi_assist_id'],
                                                 'employee_id'=>$formData['employee_id'],
                                                 'employer_name'=>$formData['employer_name'],
                                                 'first_name'=>$formData['first_name'],
                                                 'middle_name'=>$formData['middle_name'],
                                                 'last_name'=>$formData['last_name'],
                                                 'aadhaar_no'=>$formData['aadhaar_no'],
                                                 'pan'=>$formData['pan'],
                                                 'mobile_country_code'=>$formData['mobile_country_code'],
                                                 'mobile'=>$formData['mobile'],
                                                 'email'=>$formData['email'],
                                                 'landline'=>$formData['landline'],
                                                 'gender'=>$formData['gender'],
                                                 'date_of_birth' => $formData['date_of_birth'],
                                                 'bank_id'=>$bankId,
                                                 'product_id'=>$productId,
                                                 'unicode'=>$unicode,
                                                 'corporate_id'=>$user->id,
                                                 'customer_type' => $customerType,
                                                 'name_on_card' => $formData['name_on_card'],
                                                 'mother_maiden_name' => $formData['mother_maiden_name'],
                                                 'address_line1' => $formData['address_line1'],
                                                 'address_line2' => $formData['address_line2'],
                                                 'state' => $formData['state'],
                                                 'city' => $formData['city'],
                                                 'pincode' => $formData['pincode'],
                                                 'corporate_id'=>$formData['corporate_id'],
                                                 'by_corporate_id'=> $user->id,
                                                 'corp_address_line1' => $formData['corp_address_line1'],
                                                 'corp_address_line2' => $formData['corp_address_line2'],
                                                 'corp_state' => $formData['corp_state'],
                                                 'corp_city' => $formData['corp_city'],
                                                 'corp_pin' => $formData['corp_pincode'],
                                                 'id_proof_type' => $formData['id_proof_type'],
                                                 'id_proof_number' => $formData['id_proof_number'],
                                                 'address_proof_type' => $formData['address_proof_type'],
                                                 'address_proof_number' => $formData['address_proof_number'],
                                                 'status' => $status,
                                                 'status_ops' => STATUS_PENDING,
                                                 'status_ecs' => STATUS_WAITING,
                                               );
                                               //Util::debug($cardholderInfo);
                            $idDocFile = isset($_FILES['id_doc_path']['name']) ? $_FILES['id_doc_path']['name'] : '';
                            $addrDocFile = isset($_FILES['address_doc_path']['name']) ? $_FILES['address_doc_path']['name'] : '';
                            if(trim($idDocFile)=='')
                                unset($_FILES['id_doc_path']);
                            if(trim($addrDocFile)=='')
                                unset($_FILES['address_doc_path']);
                            
                            $upload = new Zend_File_Transfer_Adapter_Http(); 
                            
                            
                            if ($addrDocFile != '' || $idDocFile !='') {
                                    // Add Validators for uploaded file's extesion , mime type and size
                                    $upload->addValidator('Extension', false, array('jpg', 'jpeg','pdf','png'=>false))
                                            ->addValidator('FilesSize', false, array('min' => '5kB', 'max' => $uploadlimit));
                                    $upload->setDestination(UPLOAD_PATH_RAT_CORP_DOC);
                                        
                                    try{
                                        //All validations correct then upload file
                                        if($upload->isValid()){
                                            // upload received file(s)
                                            $upload->receive();
                                           
                                            $this->_helper->FlashMessenger(
                                                array(
                                                    'msg-success' => 'File uploaded successfully',
                                                )
                                            );
                                        }else {
                                            $this->_helper->FlashMessenger(
                                                array(
                                                        'msg-error' => 'Documents could not be uploaded. Allowed Formats are Jpeg,Jpg,Pdf only.',
                                                     )
                                            );
                                            $errorExists = TRUE;
                                        }
                                
                                    }catch (Zend_File_Transfer_Exception $e) { 
                                          //  $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-");  
                                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                            $this->_helper->FlashMessenger(
                                                    array(
                                                        'msg-error' => $e->getMessage(),
                                                    )
                                            );
                                             $errorExists = TRUE;
                                    }   
                            }
                            
                            if($errorExists == FALSE)  {    
                                unset($custDetails['id']);
				$custDetails['product_customer_id'] = $id;
				// Saving in details table    
				$objCardholders->update($cardholderInfo,"id = $id");
                                $customerAddResp = 1;
                                
                                $nameId = $upload->getFileName('id_doc_path');
                                $nameAdd = $upload->getFileName('address_doc_path');
                                if(!empty($nameId)){

                                    $destId = $upload->getDestination('id_doc_path');
                                    $sizeId = $upload->getFileSize('id_doc_path');

                                    // get the file name and extension
                                    $extId = explode(".", $nameId);

                                    // add document details along with agent id to DB
                                    $dataID = array('doc_product_id' => $productId, 'doc_cardholder_id' => $id , 'by_corporate_id' => $user->id, 'ip' => $objCardholders->formatIpAddress(Util::getIP()), 'doc_type' => $formData['id_proof_type'], 'file_name' => '', 'file_type' => $extId['1'], 'status' => STATUS_ACTIVE);

                                    $resId = $docModel->saveCustomerDocs($dataID);

                                    $renameFileId = $resId. '.' . $extId['1'];
                                    // rename the file and update the record
                                    $dataArrId = array('file_name' => $renameFileId);
                                    $updateId = $docModel->renameDocs($resId, $dataArrId);

                                    // Rename uploaded file using Zend Framework
                                    $fullFilePathId = $destId . '/' . $renameFileId;
                                    $filterFileRenameId = new Zend_Filter_File_Rename(array('target' => $fullFilePathId, 'overwrite' => true));
                                    $filterFileRenameId->filter($nameId);
                                       // rename the file and update the record
                                    $dataArrId = array('id_proof_doc_id' => $resId);
                                    $objCardholders->update($dataArrId ,"id = $id");

                                }
                                
                                /*** address doc upload case ***/
                                if(!empty($nameAdd)){

                                    $destAdd = $upload->getDestination('address_doc_path');

                                    # Returns the mimetype for the 'doc_path' form element
                                    //$mimeType = $upload->getMimeType($doc_path);
                                    // get the file name and extension
                                    $extAdd = explode(".", $nameAdd);

                                    // add document details along with agent id to DB
                                    $dataAdd = array('doc_product_id' => $productId, 'doc_cardholder_id' => $id , 'by_corporate_id' => $user->id, 'ip' => $objCardholders->formatIpAddress(Util::getIP()), 'doc_type' => $formData['address_proof_type'], 'file_name' => '', 'file_type' => $extId['1'], 'status' => STATUS_ACTIVE);

                                    $resAdd = $docModel->saveCustomerDocs($dataAdd);
                                    $renameFileAdd = $resAdd . '.' . $extAdd['1'];

                                    // rename the file and update the record
                                    $dataArrAdd = array('file_name' => $renameFileAdd);
                                    $updateAdd = $docModel->renameDocs($resAdd, $dataArrAdd);

                                    // Rename uploaded file using Zend Framework
                                    $fullFilePathAdd = $destAdd . '/' . $renameFileAdd;
                                    $filterFileRenameAdd = new Zend_Filter_File_Rename(array('target' => $fullFilePathAdd, 'overwrite' => true));
                                    $filterFileRenameAdd->filter($nameAdd);
                                    $dataArrId = array('address_proof_doc_id' => $resAdd);
                                    $objCardholders->update($dataArrId ,"id = $id");
                                }

                                // IF BOTH FILE UPLOADED SUCCESSFULLY THEN CUSTOMER WILL BE KYC TYPE 
                                if( (!empty($addrDocFile)) && !empty($idDocFile) ){
                                    $customerType = TYPE_KYC;
                                    $status_ops = STATUS_PENDING;
                                    $dataArr = array('customer_type' => $customerType,'status_ops' => $status_ops);
                                    $objCardholders->update($dataArr ,"id = $id");
                                }
                            }
                    }catch (Exception $e ) { 
                        $errorExists = true;
                        $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'],"Y-m-d","d-m-Y", "-");
                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                        $form->populate($formData);
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    }
                }
                /*** adding cardholder details in db over here ***/
                    
                if(!$errorExists && $customerAddResp > 0){
                    unset($session->corp_cardholder_auth);
                    unset($session->corp_cardholder_mobile_number);
                    unset($session->corp_validated_cardholder_auth);
                    //$form->populate();
                    
                     $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Customer details updated and application resubmitted to Operation',
                        )
                    );
                    $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/opsrejected/')); 
               }else{
                    $msg = $objCardholders->getError();
                   if(!empty($msg)){
                        $this->_helper->FlashMessenger(
                            array(
                                 'msg-error' => $msg
                              )
                        );
                   }
                }
            }
        }else{
            $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'],"Y-m-d","d-m-Y", "-");
            $form->populate($formData);    
        }
           
            
        } //  if form does not validate successfully       
        elseif(!$btnAuth && $resendAuth==''&& $btnAdd==''){
            $custDetails['date_of_birth'] = Util::returnDateFormatted($custDetails['date_of_birth'],"Y-m-d","d-m-Y", "-");
            $custDetails['cty'] = $custDetails['city'];
            $custDetails['pin'] = $custDetails['pincode'];
            $custDetails['comm_cty'] = $custDetails['corp_city'];
            $custDetails['comm_pin'] = $custDetails['corp_pin'];
            $form->populate($custDetails);
	}
    }
}
