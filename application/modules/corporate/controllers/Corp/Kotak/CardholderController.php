<?php
/**
 * Cardholder actions
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class Corp_Kotak_CardholderController extends App_Corporate_Controller
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
        $formData  = $this->_request->getPost();
        
        $user = Zend_Auth::getInstance()->getIdentity();
        $session = new Zend_Session_Namespace('App.Corporate.Controller');
        
        $request = $this->getRequest();
        $objValidation = new Validator();
        $objCardholders = new Corp_Kotak_Customers();
        $objMobile = new Mobile();
        $objEmail = new Email();
        $objCRN = new CRN();
        $productModel = new Products();
        $errorExists = false;
        $id = $this->_getParam('id');
        $docModel = new Documents();
        $productCorp = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_OPENLOOP_GPR);
        $productCorpUnicode = $productCorp->product->unicode;
        $productIdInfo = $productModel->getProductInfoByUnicode($productCorpUnicode);
        $fielUploadError = false;
        $fielUploadErrorMsg = '';
    
        // Get our form and validate it
        $form = new Corp_Kotak_AddCardholderForm(array(
                                                    'action' => $this->formatURL('/corp_kotak_cardholder/add'),
                                                    'method' => 'post',
                                                    'name'=>'frmAdd',
                                                    'id'=>'frmAdd'
                                                ));                      
       
        $productInfo = $productModel->getCorporateProductsInfo($user->id);            
        if(!empty($productInfo)) 
        {
             $productArr = $this->filterProductArrayForForm($productInfo);
            $form->getElement('product_id')->setMultiOptions($productArr);
        }else{
            $this->_helper->FlashMessenger( array('msg-error' => 'No product assigned at this moment',) );
        }
        $this->view->form = $form;         
        
        if($formData['product_id'] != $productIdInfo->id){
            $form->id_proof_type->setRequired(false)->setValidators(array());
            $form->id_proof_number->setRequired(false)->setValidators(array());
            $form->address_proof_type->setRequired(false)->setValidators(array());
            $form->address_proof_number->setRequired(false)->setValidators(array());
        }
        
        
         
        $btnAuth = isset($formData['send_auth_code'])?$formData['send_auth_code']:'0';   
        $resendAuth = isset($formData['resend_auth_code'])?$formData['resend_auth_code']:'';   
        $btnAdd = isset($formData['btn_add'])?$formData['btn_add']:'';
        
        if( $btnAuth== 1 ){  
            
          // exit('here');
             if($formData['product_id'] != ''){
                 
              
            $productOptionsArr = $productModel->getProductInfo($formData['product_id']);
            
            try{
                $userData = array('mobile_country_code'=>$formData['mobile_country_code'], 
                                  'mobile1'=>$formData['mobile'],
                                  'mobile_number_old'=>$formData['mobile_number_old'],
                                  'product_name' => $productOptionsArr->name,
                                  'product_id' => $formData['product_id']
                    );                               
                $objMsg = new App\Messaging\Corp\Kotak\Corporate();
                if(isset($session->corp_cardholder_auth))
                    $resp = $objMsg->cardholderAuth($userData, $resend = TRUE);
                else
                    $resp = $objMsg->cardholderAuth($userData);
                
                $this->view->corp_cardholder_auth = $session->corp_cardholder_auth;                       
                $session->corp_cardholder_mobile_number = $formData['mobile_number'];                       
                
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
             else{
                 $this->_helper->FlashMessenger( array('msg-error' => 'Product not selected.',) );
                
             }
        }
        
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
                //echo $session->corp_cardholder_auth;
                if($authValidated!=1 && $session->corp_cardholder_auth != $formData['auth_code'] || ($session->corp_cardholder_mobile_number!=$formData['mobile_number']) ){ // matching the auth code
                     //$this->view->msg = $res;
                     $errorExists = true;
                     $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'Invalid Authorization Code',
                        )
                    );
                    $this->view->formData = $formData;
                    $form->getElement("send_auth_code")->setValue("0");
                } else {                        
                
                /*** checking for validation and duplication ***/
                    
                try{
                       
                     $validateArr = array(
                     'tablename' => DbTable::TABLE_KOTAK_CORP_CARDHOLDER,
                     //'product_id' => $formData['product_id'],
                     'col_value' => $formData['card_number'],
                     'col_name' => 'card_number',
                     'col' => 'Card Number',
                     );
                        // card number check
                     if($formData['card_number'] != ''){
                       $cardNumCheck = $objValidation->checkColDuplicacy($validateArr);
                     }
                     
                     $validateArr['product_id'] = $formData['product_id'];
                     $validateArr['col_value'] = $formData['afn'];
                     $validateArr['col_name'] = 'afn';
                     $validateArr['col'] = 'AFN';
                      // afn check
                     if($formData['afn'] != ''){
                      $afnCheck = $objValidation->checkColDuplicacy($validateArr);
                     }
                      $validateArr['col_value'] = $formData['mobile'];
                      $validateArr['col_name'] = 'mobile';
                      $validateArr['col'] = 'Mobile No.';
                      // mobile number check
                      $mobCheck = $objValidation->checkColDuplicacy($validateArr);

                        
                        // pan card number check
                        if($pan == ''){
                            
                            $isPanValid=true;
                        } else {
                                $isPanValid = $objValidation->validatePAN($pan);
                                if($isPanValid){
                                    $validateArr['col_value'] = $formData['pan'];
                                    $validateArr['col_name'] = 'pan';
                                    $validateArr['col'] = 'PAN';
                                   $isPanValid = $objValidation->checkColDuplicacy($validateArr);
                                }
                               }
                               
                               
                               
                        // aadhaar card number check
                        if($aadhaarNo == ''){
                            $isAadhaarValid=true;
                        } else {
                                $isAadhaarValid = $objValidation->validateAadhar($aadhaarNo);
                                if($isAadhaarValid){
                                 $validateArr['col_value'] = $formData['aadhaar_no'];
                                 $validateArr['col_name'] = 'aadhaar_no';
                                 $validateArr['col'] = 'Aadhaar No.';
                                 $isAadhaarValid = $objValidation->checkColDuplicacy($validateArr);
                                }
                              }
                          // ID proof type Aadhaar
                          if($formData['id_proof_type'] == 'aadhaar card'){
                              $isAadhaarValid = $objValidation->validateAadhar($formData['id_proof_number']);
                                if($isAadhaarValid){
                                 $validateArr['col_value'] = $formData['id_proof_number'];
                                 $validateArr['col_name'] = 'id_proof_number';
                                 $validateArr['col'] = 'Aadhaar No.';
                                 $isAadhaarValid = $objValidation->checkColDuplicacy($validateArr);
                                
                          }
                          }
                          // ID proof type PAN
                          if($formData['id_proof_type'] == 'pan'){
                              $isAadhaarValid = $objValidation->validatePAN($formData['id_proof_number']);
                                if($isAadhaarValid){
                                 $validateArr['col_value'] = $formData['id_proof_number'];
                                 $validateArr['col_name'] = 'id_proof_number';
                                 $validateArr['col'] = 'PAN';
                                 $isAadhaarValid = $objValidation->checkColDuplicacy($validateArr);
                                
                           }
                          }
                          $validateArr['col_value'] = $formData['email'];
                          $validateArr['col_name'] = 'email';
                          $validateArr['col'] = 'Email ID';
                        // email check
                        $emailCheck = $objValidation->checkColDuplicacy($validateArr);
                        
                         $shmartCRN = '';       
                       // now getting the shmart crn
                         // $crnFuncCallCounter=0;
                         // $shmartCRN='';
                         // while($shmartCRN=='' && $crnFuncCallCounter<5){
                         //      $shmartCRN = rand('012345','987654');   
                         //      $crnFuncCallCounter++;                        
                         //}
                         
                         
                         //if($shmartCRN==''){
                         //    throw new Exception('Shmart CRN not found');
                         //} else { // check shmart crn duplicate
                         //    $isCRNValid = $objCRN->checkCorpCRNDuplicate($shmartCRN);
                         //}
                         if($formData['product_id'] == $productIdInfo->id){
                           $idDocFile = isset($_FILES['id_doc_path']['name'])?$_FILES['id_doc_path']['name']:'';
                           $addrDocFile = isset($_FILES['address_doc_path']['name'])?$_FILES['address_doc_path']['name']:'';
                           if(trim($addrDocFile)=='' && trim($idDocFile)==''){
                              $fielUploadError = true;
                              $errorExists = true;
                              $fielUploadErrorMsg = 'Please Upload Identification Document File';
                           
                           }elseif(trim($idDocFile)==''){
                              $fielUploadError = true;
                              $errorExists = true;
                              $fielUploadErrorMsg = 'Please Upload Identification Document File';
                           }elseif(trim($addrDocFile)==''){
                              $fielUploadError = true;
                              $errorExists = true;
                              $fielUploadErrorMsg = 'Please Upload Address Document File';
                              
                           }else{
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
                         
                }
                catch (Exception $e ) {
                        $errorExists = true;
                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                        $form->populate($formData);
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    } 

                /*** checking for validation and duplication over here ***/
                  
               
                  //var_dump($errorExists); 
                /*** adding cardholder details in db ***/
                if(!$errorExists){
                  $idDocFile = isset($_FILES['id_doc_path']['name'])?$_FILES['id_doc_path']['name']:'';
                  $addrDocFile = isset($_FILES['address_doc_path']['name'])?$_FILES['address_doc_path']['name']:'';
                  try{
                        $bankProdInfo = $productModel->getProductInfo($formData['product_id']);
                        $bankId = isset($bankProdInfo['bank_id'])?$bankProdInfo['bank_id']:'';
                        $productId = $formData['product_id'];
                        $unicode = rand('123465','987456');                           
                        $cardholderInfo = array(
                                                 'shmart_crn'=> $shmartCRN,
                                                 'card_number'=>$formData['card_number'],
                                                 'card_pack_id'=>$formData['card_pack_id'],
                                                 'CardNumber'=>$formData['card_number'],
                                                 'CardPackId'=>$formData['card_pack_id'],
                                                 'afn'=>$formData['afn'],
                                                 'MemberId'=>$formData['medmber_id'],
                                                 'employee_id'=>$formData['employee_id'],
                                                 'employer_name'=>$formData['employer_name'],
                                                 'FirstName'=>$formData['first_name'],
                                                 'MiddleName'=>$formData['middle_name'],
                                                 'LastName'=>$formData['last_name'],
                                                 'aadhaar_no'=>$formData['aadhaar_no'],
                                                 'pan'=>$formData['pan'],
                                                 'mobile_country_code'=>$formData['mobile_country_code'],
                                                 'Mobile'=>$formData['mobile'],
                                                 'Email'=>$formData['email'],
                                                 'Gender'=>$formData['gender'],
                                                 'DateOfBirth'=> Util::returnDateFormatted($formData['date_of_birth'], "d-m-Y", "Y-m-d", "-"),
                                                 'bank_id'=>$bankId,
                                                 'product_id'=>$productId,
                                                 'unicode'=>$unicode,
                                                 'ProductId'=>$productId,
                                                 'customer_type' => TYPE_NONKYC,
                                                 'name_on_card' => $formData['name_on_card'],
                                                 'mother_maiden_name' => $formData['mother_maiden_name'],
                                                 'address_line1' => $formData['address_line1'],
                                                 'address_line2' => $formData['address_line2'],
                                                 'state' => $formData['state'],
                                                 'city' => $formData['city'],
                                                 'pincode' => $formData['pincode'],
                                                 'corporate_id'=>$formData['corporate_id'],
                                                 'by_corporate_id'=> $user->id,
                                                 'comm_address_line1' => $formData['comm_address_line1'],
                                                 'comm_address_line2' => $formData['comm_address_line2'],
                                                 'comm_state' => $formData['comm_state'],
                                                 'comm_city' => $formData['comm_city'],
                                                 'comm_pin' => $formData['comm_pincode'],
                                                 'id_proof_type' => $formData['id_proof_type'],
                                                 'id_proof_number' => $formData['id_proof_number'],
                                                 'address_proof_type' => $formData['address_proof_type'],
                                                 'address_proof_number' => $formData['address_proof_number'],
                                                 'status_bank' => STATUS_PENDING,
                                                 'status_ecs' => STATUS_WAITING,
                                               );

                        //upload files
                                    $upload = new Zend_File_Transfer_Adapter_Http();  
                          // Update kyc details only for openloop GPR
                       if($formData['product_id'] == $productIdInfo->id){
                        
                                
                            if ($addrDocFile != '' || $idDocFile !='') {
                      
                                     
                                        
                                    // Add Validators for uploaded file's extesion , mime type and size
                                    $upload->addValidator('Extension', false, array('jpg', 'jpeg','pdf','png'=>false))
                                            ->addValidator('FilesSize', false, array('min' => '5kB', 'max' => $uploadlimit));
                                       
                                    $upload->setDestination(UPLOAD_PATH_KOTAK_AMUL_DOC);
                                        
                                    try{
                                            
                                        //All validations correct then upload file
                                        if($upload->isValid()){
                                            // upload received file(s)
                                            $upload->receive();
                                          
                                        }else {
                                            $this->_helper->FlashMessenger(
                                                array(
                                                        'msg-error' => 'Documents could not be uploaded. Allowed Formats are Jpeg,Jpg,Pdf only.',
                                                     )
                                            );
                                            $errorExists = TRUE;
                                        }
                                
                                    }catch (Zend_File_Transfer_Exception $e) {
                                             $form->populate($formData);
                                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                            $this->_helper->FlashMessenger(
                                                    array(
                                                        'msg-error' => $e->getMessage(),
                                                    )
                                            );
                                             $errorExists = TRUE;
                                    }
                                            
                              }  
                           }
                       
                            if(!$errorExists)  {    
                                $status = ($formData['card_type'] == STATUS_ACTIVATION_PENDING) ? STATUS_ACTIVATION_PENDING : STATUS_ECS_PENDING;
                                $customerAddResp = $objCardholders->addCustomer($cardholderInfo,$status);
                    
                                if($formData['product_id'] == $productIdInfo->id){
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
                            
                               $this->_helper->FlashMessenger(
                                                array(
                                                    'msg-success' => 'File uploaded successfully',
                                                )
                                            );
                          
                          if($resId > 0 && $resAdd >0){
                            $params = array('customer_type' => TYPE_KYC,'recd_doc' => FLAG_YES,'date_recd_doc' => new Zend_Db_Expr('NOW()'),'recd_doc_id' => $user->id);
                            $objCardholders->updateKYC($params,$customerAddResp);
                            }
                           }   
                       }
                    
                    }catch (Exception $e ) {
//                        $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-"); 
                        $errorExists = true;
                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                        $form->populate($formData);
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    }
                }
                /*** adding cardholder details in db over here ***/
                    
          
               if($fielUploadError && $errorExists){
                   $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => $fielUploadErrorMsg,
                        )
                    );
               }elseif(!$errorExists && $customerAddResp > 0){
                    unset($session->corp_cardholder_auth);
                    unset($session->corp_cardholder_mobile_number);
                    unset($session->corp_validated_cardholder_auth);
                    
                    
                     $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Cardholder added successfully',
                        )
                    );
                    $this->_redirect($this->formatURL('/corp_kotak_cardholder/add/'));
               }else{
                   $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'Cardholder could not be added at this moment',
                        )
                    );
               }
              }
            }
             $formData['send_auth_code'] = 0;
             $formData['cty'] = $formData['city'];
             $formData['pin'] = $formData['pincode'];
             $formData['comm_cty'] = $formData['comm_city'];
             $formData['comm_pin'] = $formData['comm_pincode'];
             $form->populate($formData);
          } //  if form does not validate successfully 
          
    }
    
    /*
     *Bulk upload
     *
     */
     
   public function uploadcardholdersAction() {
        $this->title = "Bulk Upload of Cardholders";
        $page = $this->_getParam('page');
        $form = new Corp_Kotak_CardholderuploadForm();
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $cardholdersModel = new Corp_Kotak_Customers();
        $this->view->records = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
        $user_id = $user->id;
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
//        $cardholdersModel->ratCorpECSRegn();
        if ($this->getRequest()->isPost() && $submit =='') {
            if ($form->isValid($this->getRequest()->getPost())) {
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->receive();
                $name = $upload->getFileName('doc_path');

                $batchName = $upload->getFileName('doc_path', $path = FALSE) ;
                $checkFile = $cardholdersModel->checkBatchFilename($batchName, $formData['product_id']);
                
                //check file extension...
                $upload->addValidator('Extension', false, array(FILE_TYPE_TXT, 'case' => false));
                
                $file_ext = Util::getFileExtension($name);
                if(strtolower($file_ext) != strtolower(FILE_TYPE_TXT))
                {
                   $this->_helper->FlashMessenger( array('msg-error' => 'Invalid file uploaded. Allowed Format is txt only.',) );
                   $this->_redirect($this->formatURL('/corp_kotak_cardholder/uploadcardholders/'));
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
                    $fp = fopen($name, 'r');
                    while (!feof($fp)) {
                        $line = fgets($fp);
                        if (!empty($line)) {
                            $delimiter = CORP_CARDHOLDER_UPLOAD_DELIMITER;
                            $dataArr = str_getcsv($line, $delimiter);
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
                                    $cardholdersModel->insertCardholderBatch($dataArr, $batchName, $status);
                                } catch (Exception $e) {
                                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                }
                                
                                
                                $this->view->rejectpaginator = $cardholdersModel->showFailedPendingCardholderDetails($batchName, $formData['product_id'],$page, $paginate = NULL,TRUE);

                                $this->view->paginator = $cardholdersModel->showPendingCardholderDetails($batchName, $dataArr['product_id']);
                            } else {
    //                            $this->_helper->FlashMessenger(array('msg-error' => 'Data format is not correct'));
                            }
                        }
                    }


                    $this->view->records = TRUE;
                    $this->view->batch_name = $batchName;
                    $this->view->card_type = $formData['card_type'];

                    fclose($fp);
                }
            }
        }

        if ($submit != '') {


            try {
                 $statusECS = ($formData['crd_ype'] == STATUS_ACTIVATION_PENDING) ? STATUS_ACTIVATION_PENDING : STATUS_ECS_PENDING;
                $cardholdersModel->bulkAddCardholder($formData['reqid'], $formData['batch'],$statusECS);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Cardholder details have been updated in our records',
                        )
                );
                $this->_redirect($this->formatURL('/corp_kotak_cardholder/uploadcardholders/'));
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
        
        $form = new Corp_Ratnakar_CardholderSearchForm(array('action' => $this->formatURL('/corp_kotak_cardholder/search'),
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
        $redictUrl = $this->formatURL('/corp_kotak_cardholder/search?'.$queryString);
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
                $this->_redirect($this->formatURL('/corp_kotak_cardholder/search'.$queryString));
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
//                echo '/corp_kotak_cardholder/search'.$queryString;
//                exit;   
                $this->_redirect($this->formatURL('/corp_kotak_cardholder/search?'.$queryString));
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
        $redictUrl = $this->formatURL('/corp_kotak_cardholder/search?'.$queryString);
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
                $this->_redirect($this->formatURL('/corp_kotak_cardholder/search'.$queryString));
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
//                echo '/corp_kotak_cardholder/search'.$queryString;
//                exit;   
                $this->_redirect($this->formatURL('/corp_kotak_cardholder/search?'.$queryString));
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
                $this->_redirect($this->formatURL('/corp_kotak_cardholder/search'.$queryString));
            }
            
            $row['id'] = $id;
            $form->populate($row->toArray());
            $this->view->item = $row;
        }
        $this->view->form = $form;
        //$this->view->queryString = $this->formatURL('/agentsummary/index'.$queryString);
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
    
    public function opsrejectedAction() {
	$this->title = 'Operations Rejected Cardholder List';
	$user = Zend_Auth::getInstance()->getIdentity();
	$customerModel = new Corp_Kotak_Customers();
	$productModel = new Products();
	$form = new Corp_Kotak_OpsRejectedForm();
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
			$this->view->paginator = $customerModel->showOpsrejectedCustomerDetails($page,$params);
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
        $cardholdersModel = new Corp_Kotak_Customers();
        $id = $this->_getParam('id');
        if (!is_numeric($id)) {
            $this->_helper->FlashMessenger(
                    array(
                        'error' => 'The user id you provided is invalid',
                    )
            );

            $this->_redirect($this->formatURL('/corp_kotak_cardholder/opsrejected/'));
        }

        $row = $cardholdersModel->findById($id);
	//print_r($row); exit;
        //$documentsId = $cardholdersModel->customerIdDoclist($row->id_proof_doc_id);
        //$documentsAdd = $cardholdersModel->customerAddDoclist( $row->address_proof_doc_id);
        //$documentsprofile = $cardholdersModel->customerProfileDoclist( $row->photo_doc_id);
        //$documentDetails = array('id_proof_doc' => (!empty($documentsId))?$documentsId['file_name']:0,'address_proof_doc' => (!empty($documentsAdd))?$documentsAdd['file_name']:0,'photo_doc' => (!empty($documentsprofile))?$documentsprofile['file_name']:0);
        
        //$this->view->documents = $documentDetails;
        $row->gender = ucfirst($row->gender);
        $row->date_of_birth = Util::returnDateFormatted($row->date_of_birth, "Y-m-d", "d-m-Y", "-");
        $row->date_failed = Util::returnDateFormatted($row->date_failed, "Y-m-d", "d-m-Y", "-");

        if (empty($row)) {
            $this->_helper->FlashMessenger(
                    array(
                        'error' => 'User with Id does not exist',
                    )
            );

            $this->_redirect($this->formatURL('/corp_kotak_cardholder/opsrejected/'));
        }
       
        
        //Select Cardholder's Purse
        $this->view->cardholderPurses = array();
        if (isset($row->kotak_customer_id) && $row->kotak_customer_id > 0) {
            $cardHolder = new Corp_Kotak_Customers();
            $this->view->cardholderPurses = $cardHolder->getCardholderPurses($row->kotak_customer_id);
        }

         // Get status and comments
        $this->view->cardholderStatus = array();
        $cardHolderObj = new Corp_Kotak_CustomersLog();
        $this->view->cardholderStatus = $cardHolderObj->getCardholderStatus($row->id,$byType = BY_MAKER);
        $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/corp_kotak_cardholder/opsrejected';
        $this->view->item = $row;
    }
    
    public function editAction(){
           
        $this->title = 'Edit Cardholder';
        $formData  = $this->_request->getPost();
        
        $user = Zend_Auth::getInstance()->getIdentity();
        $session = new Zend_Session_Namespace('App.Corporate.Controller');
        
        $objValidation = new Validator();
        $objCardholders = new Corp_Kotak_Customers();
	$objCustomerDetailModel = new Corp_Kotak_CustomerDetail();
        $productModel = new Products();
        $errorExists = false;
        $docModel = new Documents();
        $productCorp = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_OPENLOOP_GPR);
        $productCorpUnicode = $productCorp->product->unicode;
        $productIdInfo = $productModel->getProductInfoByUnicode($productCorpUnicode);
        $fielUploadError = false;
        $fielUploadErrorMsg = '';
	$id = $this->_getParam('id');
        $custDetails = $objCardholders->findById($id);
	$custDetails = Util::toArray($custDetails);
	
        $form = new Corp_Kotak_EditCardholderForm(array(
                                                    'action' => $this->formatURL('/corp_kotak_cardholder/edit?id='.$id),
                                                    'method' => 'post',
                                                    'name'=>'frmAdd',
                                                    'id'=>'frmAdd'
                                                ));                      
       
        $productInfo = $productModel->getCorporateProductsInfo($user->id);            
        if(!empty($productInfo)) 
        {
             $productArr = $this->filterProductArrayForForm($productInfo);
            $form->getElement('product_id')->setMultiOptions($productArr);
        }else{
            $this->_helper->FlashMessenger( array('msg-error' => 'No product assigned at this moment',) );
        }
        $this->view->form = $form;
        
        if($custDetails['product_id'] != $productIdInfo->id){
            $form->id_proof_type->setRequired(false)->setValidators(array());
            $form->id_proof_number->setRequired(false)->setValidators(array());
            $form->address_proof_type->setRequired(false)->setValidators(array());
            $form->address_proof_number->setRequired(false)->setValidators(array());
        }
	
	$productInfo = $productModel->getCorporateProductsInfo($user->id);            
        if(!empty($productInfo)) 
        {
             $productArr = $this->filterProductArrayForForm($productInfo);
             $form->getElement('product_id')->setMultiOptions($productArr);
	      $form->getElement('product_id')->setValue($custDetails['product_id']);
        }else{
            $this->_helper->FlashMessenger( array('msg-error' => 'No product assigned at this moment',) );
        }
	
	
	$btnAuth = isset($formData['send_auth_code'])?$formData['send_auth_code']:'0';   
        $resendAuth = isset($formData['resend_auth_code'])?$formData['resend_auth_code']:'';   
        $btnAdd = isset($formData['btn_add'])?$formData['btn_add']:'';
        
        if( $btnAuth== 1 ){  
             if($formData['product_id'] != ''){
                 
			
		      $productOptionsArr = $productModel->getProductInfo($custDetails['product_id']);
		      
		      try{
			  $userData = array('mobile_country_code'=>$formData['mobile_country_code'], 
					    'mobile1'=>$formData['mobile'],
					    'mobile_number_old'=>$formData['mobile_number_old'],
					    'product_name' => $productOptionsArr->name,
					    'product_id' => $formData['product_id']
			      );                               
			  $objMsg = new App\Messaging\Corp\Kotak\Corporate();
			  if(isset($session->corp_cardholder_auth))
			      $resp = $objMsg->cardholderAuth($userData, $resend = TRUE);
			  else
			      $resp = $objMsg->cardholderAuth($userData);
			  
			  $this->view->corp_cardholder_auth = $session->corp_cardholder_auth;                       
			  $session->corp_cardholder_mobile_number = $formData['mobile_number'];                       
			  
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
		else{
		    $this->_helper->FlashMessenger( array('msg-error' => 'Product not selected.',) );
		   
		}
        }
         
      
        // adding details in db
        if($btnAdd){
            
            if($form->isValid($this->getRequest()->getPost())){
               
                $aadhaarNo = isset($formData['aadhaar_no'])?trim($formData['aadhaar_no']):'';
                $pan = isset($formData['pan'])?trim($formData['pan']):'';
                
                /*** checking for validation and duplication ***/
                $authValidated = isset($session->corp_validated_cardholder_auth)?$session->corp_validated_cardholder_auth:'0';
                if($authValidated!=1 && $session->corp_cardholder_auth != $formData['auth_code'] || ($session->corp_cardholder_mobile_number!=$formData['mobile_number']) ){ // matching the auth code
                     //$this->view->msg = $res;
                     $errorExists = true;
                     $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'Invalid Authorization Code',
                        )
                    );
                    $this->view->formData = $formData;
                    $form->getElement("send_auth_code")->setValue("0");
                } else {
                    
                try{
                     $validateArr = array(
                     'tablename' => DbTable::TABLE_KOTAK_CORP_CARDHOLDER,
                     'product_id' => $formData['product_id'],
                     'col_value' => $formData['card_number'],
                     'col_name' => 'card_number',
                     'col' => 'Card Number',
		     'status' => STATUS_ACTIVE,
		     'col_status' => 'status',
		     );
                        // card number check
                     if($formData['card_number'] != ''){
                       $cardNumCheck = $objValidation->checkColDuplicacy($validateArr);
                     }
                    
                     $validateArr['col_value'] = $formData['afn'];
                     $validateArr['col_name'] = 'afn';
                     $validateArr['col'] = 'AFN';
                      // afn check
                     if($formData['afn'] != ''){
                      $afnCheck = $objValidation->checkColDuplicacy($validateArr);
                     }
                      $validateArr['col_value'] = $formData['mobile'];
                      $validateArr['col_name'] = 'mobile';
                      $validateArr['col'] = 'Mobile No.';
                      // mobile number check
                      $mobCheck = $objValidation->checkColDuplicacy($validateArr);

                        
                        // pan card number check
                        if($pan == ''){
                            
                            $isPanValid=true;
                        } else {
                                $isPanValid = $objValidation->validatePAN($pan);
                                if($isPanValid){
                                    $validateArr['col_value'] = $formData['pan'];
                                    $validateArr['col_name'] = 'pan';
                                    $validateArr['col'] = 'PAN';
                                   $isPanValid = $objValidation->checkColDuplicacy($validateArr);
                                }
                               }
                               
                        // aadhaar card number check
                        if($aadhaarNo == ''){
                            $isAadhaarValid=true;
                        } else {
                                $isAadhaarValid = $objValidation->validateAadhar($aadhaarNo);
                                if($isAadhaarValid){
                                 $validateArr['col_value'] = $formData['aadhaar_no'];
                                 $validateArr['col_name'] = 'aadhaar_no';
                                 $validateArr['col'] = 'Aadhaar No.';
                                 $isAadhaarValid = $objValidation->checkColDuplicacy($validateArr);
                                }
                              }
                          // ID proof type Aadhaar
                          if($formData['id_proof_type'] == 'aadhaar card'){
                              $isAadhaarValid = $objValidation->validateAadhar($formData['id_proof_number']);
                                if($isAadhaarValid){
                                 $validateArr['col_value'] = $formData['id_proof_number'];
                                 $validateArr['col_name'] = 'id_proof_number';
                                 $validateArr['col'] = 'Aadhaar No.';
                                 $isAadhaarValid = $objValidation->checkColDuplicacy($validateArr);
                                
                          }
                          }
                          // ID proof type PAN
                          if($formData['id_proof_type'] == 'pan'){
                              $isAadhaarValid = $objValidation->validatePAN($formData['id_proof_number']);
                                if($isAadhaarValid){
                                 $validateArr['col_value'] = $formData['id_proof_number'];
                                 $validateArr['col_name'] = 'id_proof_number';
                                 $validateArr['col'] = 'PAN';
                                 $isAadhaarValid = $objValidation->checkColDuplicacy($validateArr);
                                
                           }
                          }
                          $validateArr['col_value'] = $formData['email'];
                          $validateArr['col_name'] = 'email';
                          $validateArr['col'] = 'Email ID';
                        // email check
                        $emailCheck = $objValidation->checkColDuplicacy($validateArr);
                        
                         $shmartCRN = '';       
                     
                         if($formData['product_id'] == $productIdInfo->id){
                           $idDocFile = isset($_FILES['id_doc_path']['name'])?$_FILES['id_doc_path']['name']:'';
                           $addrDocFile = isset($_FILES['address_doc_path']['name'])?$_FILES['address_doc_path']['name']:'';
                           if(trim($addrDocFile)=='' && trim($idDocFile)==''){
                              $fielUploadError = true;
                              $errorExists = true;
                              $fielUploadErrorMsg = 'Please Upload Identification Document File';
                           
                           }elseif(trim($idDocFile)==''){
                              $fielUploadError = true;
                              $errorExists = true;
                              $fielUploadErrorMsg = 'Please Upload Identification Document File';
                           }elseif(trim($addrDocFile)==''){
                              $fielUploadError = true;
                              $errorExists = true;
                              $fielUploadErrorMsg = 'Please Upload Address Document File';
                              
                           }else{
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
                         
                }
                catch (Exception $e ) {
                        $errorExists = true;
                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                        $form->populate($formData);
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    } 

                /*** checking for validation and duplication over here ***/
                  
               
                  //var_dump($errorExists); 
                /*** adding cardholder details in db ***/
                if(!$errorExists){
                  $idDocFile = isset($_FILES['id_doc_path']['name'])?$_FILES['id_doc_path']['name']:'';
                  $addrDocFile = isset($_FILES['address_doc_path']['name'])?$_FILES['address_doc_path']['name']:'';
                  try{
                        $bankProdInfo = $productModel->getProductInfo($formData['product_id']);
                        $bankId = isset($bankProdInfo['bank_id'])?$bankProdInfo['bank_id']:'';
                        $productId = $formData['product_id'];
                        $unicode = rand('123465','987456');                           
                        $cardholderInfo = array(
                                                 'shmart_crn'=> $shmartCRN,
                                                 'card_number'=>$formData['card_number'],
                                                 'card_pack_id'=>$formData['card_pack_id'],
                                                 'afn'=>$formData['afn'],
                                                 'member_id'=>$formData['member_id'],
                                                 'employee_id'=>$formData['employee_id'],
                                                 'employer_name'=>$formData['employer_name'],
                                                 'first_name'=>$formData['first_name'],
                                                 'middle_name'=>$formData['middle_name'],
                                                 'last_name'=>$formData['last_name'],
                                                 'landline' => $formData['landline'],
                                                 'aadhaar_no'=>$formData['aadhaar_no'],
                                                 'pan'=>$formData['pan'],
                                                 'mobile_country_code'=>$formData['mobile_country_code'],
                                                 'mobile'=>$formData['mobile'],
                                                 'email'=>$formData['email'],
                                                 'gender'=>$formData['gender'],
                                                 'date_of_birth'=> Util::returnDateFormatted($formData['date_of_birth'], "d-m-Y", "Y-m-d", "-"),
                                                 'bank_id'=>$bankId,
                                                 'product_id'=>$productId,
                                                 'unicode'=>$unicode,
                                                 'customer_type' => TYPE_NONKYC,
                                                 'name_on_card' => $formData['name_on_card'],
                                                 'mother_maiden_name' => $formData['mother_maiden_name'],
                                                 'address_line1' => $formData['address_line1'],
                                                 'address_line2' => $formData['address_line2'],
                                                 'state' => $formData['state'],
                                                 'city' => $formData['city'],
                                                 'pincode' => $formData['pincode'],
                                                 'corporate_id'=>$formData['corporate_id'],
                                                 'by_corporate_id'=> $user->id,
                                                 'comm_address_line1' => $formData['comm_address_line1'],
                                                 'comm_address_line2' => $formData['comm_address_line2'],
                                                 'comm_state' => $formData['comm_state'],
                                                 'comm_city' => $formData['comm_city'],
                                                 'comm_pin' => $formData['comm_pincode'],
                                                 'id_proof_type' => $formData['id_proof_type'],
                                                 'id_proof_number' => $formData['id_proof_number'],
                                                 'address_proof_type' => $formData['address_proof_type'],
                                                 'address_proof_number' => $formData['address_proof_number'],
                                                 'status_bank' => STATUS_PENDING,
                                                 'status_ecs' => STATUS_WAITING,
						 'status_ops' => STATUS_PENDING,
                                               );

                        //upload files
                                    $upload = new Zend_File_Transfer_Adapter_Http();  
                          // Update kyc details only for openloop GPR
                       if($formData['product_id'] == $productIdInfo->id){
                        
                                
                            if ($addrDocFile != '' || $idDocFile !='') {
                      
                                     
                                        
                                    // Add Validators for uploaded file's extesion , mime type and size
                                    $upload->addValidator('Extension', false, array('jpg', 'jpeg','pdf','png'=>false))
                                            ->addValidator('FilesSize', false, array('min' => '5kB', 'max' => $uploadlimit));
                                       
                                    $upload->setDestination(UPLOAD_PATH_KOTAK_AMUL_DOC);
                                        
                                    try{
                                            
                                        //All validations correct then upload file
                                        if($upload->isValid()){
                                            // upload received file(s)
                                            $upload->receive();
                                          
                                        }else {
                                            $this->_helper->FlashMessenger(
                                                array(
                                                        'msg-error' => 'Documents could not be uploaded. Allowed Formats are Jpeg,Jpg,Pdf only.',
                                                     )
                                            );
                                            $errorExists = TRUE;
                                        }
                                
                                    }catch (Zend_File_Transfer_Exception $e) {
                                             $form->populate($formData);
                                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                            $this->_helper->FlashMessenger(
                                                    array(
                                                        'msg-error' => $e->getMessage(),
                                                    )
                                            );
                                             $errorExists = TRUE;
                                    }
                                            
                              }  
                           }
                       
                            if(!$errorExists)  {    
                                $status = ($formData['card_type'] == STATUS_ACTIVATION_PENDING) ? STATUS_ACTIVATION_PENDING : STATUS_ECS_PENDING;
                                unset($custDetails['id']);
				$custDetails['product_customer_id'] = $id;
				$cardholderInfo['status'] = $status;
				// Saving in details table    
				$objCardholders->update($cardholderInfo,"id = $id");
				$objCustomerDetailModel->save($custDetails);
				$customerAddResp =1;
                    
                                if($formData['product_id'] == $productIdInfo->id){
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
                                    $dataID = array('doc_product_id' => $productId,'doc_cardholder_id' => $id , 'by_corporate_id' => $user->id, 'ip' => $objCardholders->formatIpAddress(Util::getIP()),
                                     'doc_type' => $formData['id_proof_type'], 'file_name' => '', 'file_type' => $extId['1'], 'status' => STATUS_ACTIVE);
				
				    if($custDetails['id_proof_doc_id'] > 0){ 
				     //mark previous doc inactive
				     $docModel->updateDocs($custDetails['id_proof_doc_id']);
				    }
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
                                    $sizeAdd = $upload->getFileSize('address_doc_path');
                
                                    # Returns the mimetype for the 'doc_path' form element
                                    //$mimeType = $upload->getMimeType($doc_path);
                                    // get the file name and extension
                                    $extAdd = explode(".", $nameAdd);
                
                
                                    // add document details along with agent id to DB
                                    $dataAdd = array('doc_product_id' => $productId,'doc_cardholder_id' => $id , 'by_corporate_id' => $user->id, 'ip' => $objCardholders->formatIpAddress(Util::getIP()),
                                         'doc_type' => $formData['address_proof_type'], 'file_name' => '', 'file_type' => $extId['1'], 'status' => STATUS_ACTIVE);
                
                                    if($custDetails['address_proof_doc_id'] > 0){ 
					//mark previous doc inactive
					$docModel->updateDocs($custDetails['address_proof_doc_id']);
				    }
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
                            
                               $this->_helper->FlashMessenger(
                                                array(
                                                    'msg-success' => 'File uploaded successfully',
                                                )
                                            );
                          
                          if($resId > 0 && $resAdd >0){
                            $params = array('customer_type' => TYPE_KYC,'recd_doc' => FLAG_YES,'date_recd_doc' => new Zend_Db_Expr('NOW()'),'recd_doc_id' => $user->id);
                            $objCardholders->updateKYC($params,$id);
                            }
                           }   
                       }
                    
                    }catch (Exception $e ) {
//                        $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-"); 
                        $errorExists = true;
                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                        $form->populate($formData);
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    }
                }
                /*** adding cardholder details in db over here ***/
                    
          
               if($fielUploadError && $errorExists){
                   $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => $fielUploadErrorMsg,
                        )
                    );
               }elseif(!$errorExists && $customerAddResp > 0){
                    unset($session->corp_cardholder_auth);
                    unset($session->corp_cardholder_mobile_number);
                    unset($session->corp_validated_cardholder_auth);
                    
                    
                    $this->_helper->FlashMessenger(
                                    array(
                                        'msg-success' => 'Customer details updated and application resubmitted to Operation',
                                    )
                            );
                 $this->_redirect($this->formatURL('/corp_kotak_cardholder/opsrejected'));
                 $form->populate($formData);
               }else{
                   $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'Cardholder could not be added at this moment',
                        )
                    );
               }
             }
            }
            //$formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'],"Y-m-d","d-m-Y", "-");
            $formData['cty'] = $formData['city'];
            $formData['pin'] = $formData['pincode'];
            $formData['comm_cty'] = $formData['comm_city'];
            $formData['comm_pin'] = $formData['comm_pincode'];
            $form->populate($formData);
           
	} //  if form does not validate successfully
	elseif(!$btnAuth && $resendAuth==''&& $btnAdd==''){
	      //$custDetails['state'] = $state->getStateCode($custDetails['state']); 
	      //$custDetails['comm_state'] = $state->getStateCode($custDetails['comm_state']); 
	      $custDetails['date_of_birth'] = Util::returnDateFormatted($custDetails['date_of_birth'],"Y-m-d","d-m-Y", "-");
	      $custDetails['cty'] = $custDetails['city'];
	      $custDetails['pin'] = $custDetails['pincode'];
	      $custDetails['comm_cty'] = $custDetails['comm_city'];
	      $custDetails['c_pin'] = $custDetails['comm_pin'];
	      $form->populate($custDetails);
	}
	$btnAuth = isset($formData['send_auth_code'])?$formData['send_auth_code']:'0';   
        $resendAuth = isset($formData['resend_auth_code'])?$formData['resend_auth_code']:'';   
        $btnAdd = isset($formData['btn_add'])?$formData['btn_add']:'';
          
    }
 	
}