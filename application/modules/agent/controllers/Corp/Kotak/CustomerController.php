<?php
/**
 * Cardholder actions
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */

class Corp_Kotak_CustomerController extends App_Agent_Controller
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
        
     public function indexAction() {
        
    }
   public function adddetailsAction()
    {      
         $this->title = 'Add Customer Details';
         
         //unset($this->session->remitter_auth); 
        //echo 'from add details.'; exit;
        //$session = new Zend_Session_Namespace('App.Agent.Controller');

         //echo 'from add details.'; exit;
        $m = new App\Messaging\Corp\Kotak\Agent();
        $formData  = $this->_request->getPost();
        $user = Zend_Auth::getInstance()->getIdentity();        
        $session = new Zend_Session_Namespace('App.Agent.Controller'); 
        unset($session->customer_id);
       
        $objCustomerModel = new Corp_Kotak_Customers();
        $objCustomerProduct = new Corp_Kotak_CustomerProduct();
        $customerId = isset($session->customer_id)?$session->customer_id:0;
        $products  = new Products();
        $bankModel  = new Banks();
        $objBaseTxn = new BaseTxn();
        //$objAgBal = new AgentBalance();
        $config = App_DI_Container::get('ConfigObject');
        $minAge = $this->view->minAge = $config->remitter->age->min;
        $currDate = date('Y-m-d');
        //$maxAge = $config->remitter->age->max;
        $uploadlimit = $config->agent->uploadfile->size;
        $errorExists = false;
        $docModel = new Documents();
        // Get our form and validate it
        $form = new Corp_Kotak_AddCustomerDetailsForm();  
        $customerLogModel = new Corp_Kotak_CustomersLog();
        $this->view->form = $form;
        $bank = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        $product = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_AMUL);
        $bankUnicode = $product->bank->unicode;
        $productUnicode = $product->product->unicode;
        $productList = $products->getAgentProducts($user->id, PROGRAM_TYPE_CORP , '');       
        $form->getElement("product_id")->setMultiOptions($productList);
        $form->getElement("bc_code")->setValue($user->agent_code);
        $isError = FALSE;
        $afn_no = new AfnNumber();
            if ($afn_no->generateTxncode()) {
                $paramsAfnNo = $afn_no->getTxncode(); //Get Txncode
               
         $form->getElement("afn")->setValue($paramsAfnNo);
            }
        
        $state = new CityList(); 
      
            $this->view->remitterData = $formData;
            // getting current ip to store in db
            $ip = $objCustomerModel->formatIpAddress(Util::getIP());
            
            if ($this->getRequest()->isPost()) {
                    $formData['cty'] = $formData['city'];
                    $formData['pin'] = $formData['pincode'];
                    $formData['comm_cty'] = $formData['comm_city'];
                    $formData['comm_pin'] = $formData['comm_pincode'];
                if($form->isValid($this->getRequest()->getPost())){
               
                   $checkMemId = $objCustomerModel->checkMemberId($formData['member_id']);
                   if($checkMemId){
                    $this->view->formData = $formData;
                    
                    $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'] , "d-m-Y", "Y-m-d", "-");
                    $customerData = array(  
                                                'member_id' => $formData['member_id'],
                                                'afn' => $formData['afn'],
                                                'place_application' => $formData['place_application'],
                                                'bc_code' => $formData['bc_code'],
                                                'landline' => $formData['landline'],
                                                'aadhaar_no' => $formData['aadhaar_no'],
                                                'society_id' => $formData['society_id'],
                                                'society_name' => $formData['society_name'],
                                                'nominee_name' => $formData['nominee_name'],
                                                'nominee_relationship' => $formData['nominee_relationship'],
                                                'member_id' => $formData['member_id'],
                                                'first_name'=>$formData['first_name'],
                                                'middle_name'=>$formData['middle_name'],
                                                'last_name'=>$formData['last_name'],
                                                'name_on_card'=>$formData['name_on_card'],
                                                'product_id'=>$formData['product_id'],
                                                'address_line1'=>$formData['address_line1'],
                                                'address_line2'=>$formData['address_line2'],
                                                'city'=>$formData['city'],
                                                'state'=> $state->getStateName($formData['state']),
                                                'pincode'=>$formData['pincode'],
                                                'comm_address_line1'=>$formData['comm_address_line1'],
                                                'comm_address_line2'=>$formData['comm_address_line2'],
                                                'comm_city'=>$formData['comm_city'],
                                                'comm_state'=> $state->getStateName($formData['comm_state']),
                                                'comm_pin'=>$formData['comm_pincode'],
                                                'mobile'=>$formData['mobile'],                            
                                                'date_of_birth'=>$formData['date_of_birth'],                                                       
                                                'mother_maiden_name'=>$formData['mother_maiden_name'],                                                       
                                                'email'=>$formData['email'],
                                                'by_agent_id'=>$user->id,
                                                'ip'=> $ip,
                                                'date_created'=> date('Y-m-d H:i:s'),
                                                'id_proof_type' => $formData['id_proof_type'],
                                                'id_proof_number' => $formData['id_proof_number'],
                                                'address_proof_type' => $formData['address_proof_type'],
                                                'address_proof_number' => $formData['address_proof_number'],
                                                'status' => STATUS_PENDING,
                                                'status_bank' => STATUS_PENDING,
                                                'status_ops' => STATUS_PENDING,
                                                'status_ecs' => STATUS_PENDING,
                        
                        
                        ); 

                 
                        try{  
                                   
            
              //Upload Remitter photo
            $profilePhotoFile = isset($_FILES['profile_pic']['name'])?$_FILES['profile_pic']['name']:'';      
            // getting files name 
                $idDocFile = isset($_FILES['id_doc_path']['name'])?$_FILES['id_doc_path']['name']:'';
                $addrDocFile = isset($_FILES['address_doc_path']['name'])?$_FILES['address_doc_path']['name']:'';

                if(trim($idDocFile)=='')
                    unset($_FILES['id_doc_path']);
                if(trim($addrDocFile)=='')
                    unset($_FILES['address_doc_path']);
              
                  if(trim($profilePhotoFile)==''){
                    unset($_FILES['profile_pic']);
                    
                  }
                  
                  if ($profilePhotoFile!='' || $addrDocFile != '' || $idDocFile !='') {
                      
                        //upload files
                        $upload = new Zend_File_Transfer_Adapter_Http();     
                        
                        // Add Validators for uploaded file's extesion , mime type and size
                        $upload->addValidator('Extension', false, array('jpg', 'jpeg','pdf','png'=>false))
                                ->addValidator('FilesSize', false, array('min' => '5kB', 'max' => $uploadlimit));
                       
                        $upload->setDestination(UPLOAD_PATH_KOTAK_AMUL_DOC);
                        
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
               
                
                    }
                    else {
                        $this->_helper->FlashMessenger(
                                                        array(
                                                                'msg-error' => 'Documents could not be uploaded. Allowed Formats are Jpeg,Jpg,Pdf only.',
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
                if($isError == FALSE)  {
            // adding remitter details in db.
            $customerAddResp = $objCustomerModel->save($customerData);
            $afn_no->setUsedStatus(); //Mark Txncode as used
            //*********************upload files********************//
            //
            $uploadedData = $form->getValues();

                $id_doc_path = $this->_getParam('id_doc_path');
                 $add_doc_path = $this->_getParam('address_doc_path'); 

                $nameId = $upload->getFileName('id_doc_path');
                $nameAdd = $upload->getFileName('address_doc_path');
                
                 /*** identification doc upload case ***/
                    if(!empty($nameId)){

                        $destId = $upload->getDestination('id_doc_path');
                        $sizeId = $upload->getFileSize('id_doc_path');

                        // get the file name and extension
                        $extId = explode(".", $nameId);

                        
                        // add document details along with agent id to DB
                         $dataID = array('doc_kotak_amul_id' => $customerAddResp , 'by_agent_id' => $user->id, 'ip' => $objCustomerModel->formatIpAddress(Util::getIP()),
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
                         $objCustomerModel->update($dataArrId ,"id = $customerAddResp");

                    } 
                    /*** identification doc upload case over ***/


                    /*** address doc upload case ***/
                    if(!empty($nameAdd)){

                    $destAdd = $upload->getDestination('address_doc_path');
                    $sizeAdd = $upload->getFileSize('address_doc_path');

                    # Returns the mimetype for the 'doc_path' form element
                    //$mimeType = $upload->getMimeType($doc_path);
                    // get the file name and extension
                       $extAdd = explode(".", $nameAdd);


                    // add document details along with agent id to DB
                    $dataAdd = array('doc_kotak_amul_id' => $customerAddResp ,'doc_rat_corp_id' => $id, 'by_agent_id' => $user->id, 'ip' => $objCustomerModel->formatIpAddress(Util::getIP()),
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
                    $objCustomerModel->update($dataArrId ,"id = $customerAddResp");
                } 
               /*** address doc upload case over ***/
                
                
                 $photo = $this->_getParam('profile_pic');
                 $namePhoto = $upload->getFileName('profile_pic');
                
                
                 /*** identification doc upload case ***/
                    if(!empty( $namePhoto)){

                        $destPhoto = $upload->getDestination('profile_pic');
                        $sizePhoto = $upload->getFileSize('profile_pic');

                        // get the file name and extension
                        $extphoto = explode(".",  $namePhoto);

                        
                        
                        // add document details along with agent id to DB
                         $dataphoto = array('doc_kotak_amul_id' => $customerAddResp, 'by_agent_id' => $user->id, 'ip' => $objCustomerModel->formatIpAddress(Util::getIP()),
                         'doc_type' => 'photo', 'file_name' => '', 'file_type' => $extphoto['1'], 'status' => STATUS_ACTIVE);

                         $resphoto = $docModel->saveCustomerDocs($dataphoto);
                         
                         $renameFilephooto = $resphoto. '.' . $extphoto['1'];
                         // rename the file and update the record
                         $dataArrphoto = array('file_name' => $renameFilephooto);
                         $updateId = $docModel->renameDocs($resphoto, $dataArrphoto);
                        

                         // Rename uploaded file using Zend Framework
                         $fullFilePathphoto = $destPhoto . '/' . $renameFilephooto;
                         $filterFileRenamephoto = new Zend_Filter_File_Rename(array('target' => $fullFilePathphoto, 'overwrite' => true));
                         $filterFileRenamephoto->filter( $namePhoto);
                            // rename the file and update the record
                         $dataArrphoto = array('photo_doc_id' => $resphoto);
                         $objCustomerModel->update($dataArrphoto ,"id = $customerAddResp");

                    } 
                    /*** identification doc upload case over ***/


            //*********************END upload files********************//
            $data = array('product_customer_id' => $customerAddResp,'by_type' => BY_MAKER,'by_id' => $user->id, 
                    'status_old' => STATUS_PENDING,'status_new' => STATUS_PENDING,
                    'status_ops_old' => STATUS_PENDING,'status_ops_new' => STATUS_PENDING,
                    'status_bank_old' => STATUS_PENDING,'status_bank_new' => STATUS_PENDING,
                    'status_ecs_old' => STATUS_PENDING,'status_ecs_new' => STATUS_PENDING,
                    'remarks' => 'Customer Registration');
               
                    $customerLogModel->save($data);
           
//                 $objCustomerModel->update(array('photo_doc_id'=> $resId), "id = $customerAddResp");
                //redirect and display message
                  
                  $bankDetail = $bankModel->getBankbyUnicode($bankUnicode);
                  $custProduct = array(
                      'product_customer_id' => $customerAddResp,
                      'kotak_customer_id' => 0,
                      'product_id' => $formData['product_id'],
                      'program_type' => PROGRAM_TYPE_CORP,
                      'bank_id' => $bankDetail['id'],
                      'by_agent_id' => $user->id,
                      'date_created' => new Zend_Db_Expr('NOW()')
                  );
                  $objCustomerProduct->saveCustProduct($custProduct);
                  
                  $this->_helper->FlashMessenger(
                                    array(
                                        'msg-success' => 'Customer details added successfully',
                                    )
                            );
                 $this->_redirect($this->formatURL('/corp_kotak_customer/complete'));
                 $form->populate($formData);
                        }
                  // End of Upload remitter photo 
                                
                         }catch (Exception $e ) { 
                                                        $errorExists = true; 
                                                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                                                        $form->populate($formData);
                                                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                                    }                 
         
              }
            else
            {
            
                 $this->_helper->FlashMessenger(
                                    array(
                                        'msg-error' => 'Member id exists',
                                    )
                            );
                 $form->populate($formData);
            }
            
            }
            
    }  
            $form->populate($formData);
            $this->view->errorExists = $errorExists;
            
 } // adddetails action closes here
        
        
        /* registrationcompleteAction is responsible for handling unset the required session details of remitter       
        */
        public function completeAction(){
        
         $this->title = 'Enroll Customer - Complete';
         
        }
      

        public function opsrejectedAction() {
        $this->title = 'Operations Rejected Customers List';
        $customerModel = new Corp_Kotak_Customers();
        $form = new Corp_Kotak_OpsRejectedForm();
        $page = $this->_getParam('page');
        $formData  = $this->_request->getPost();
        $params = array('product_id' => $this->_getParam('product_id'));
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
        $documentDetails = array();
        $id = $this->_getParam('id');
        if (!is_numeric($id)) {
            $this->_helper->FlashMessenger(
                    array(
                        'error' => 'The user id you provided is invalid',
                    )
            );

            $this->_redirect($this->formatURL('/corp_kotak_customer/opsrejected/'));
        }

        $row = $cardholdersModel->findById($id);
        $documentsId = $cardholdersModel->customerIdDoclist($row->id_proof_doc_id);
        $documentsAdd = $cardholdersModel->customerAddDoclist( $row->address_proof_doc_id);
        $documentsprofile = $cardholdersModel->customerProfileDoclist( $row->photo_doc_id);
        $documentDetails = array('id_proof_doc' => (!empty($documentsId))?$documentsId['file_name']:0,'address_proof_doc' => (!empty($documentsAdd))?$documentsAdd['file_name']:0
            ,'photo_doc' => (!empty($documentsprofile))?$documentsprofile['file_name']:0);
        
        $this->view->documents = $documentDetails;
        $row->gender = ucfirst($row->gender);
        $row->date_of_birth = Util::returnDateFormatted($row->date_of_birth, "Y-m-d", "d-m-Y", "-");
        $row->date_failed = Util::returnDateFormatted($row->date_failed, "Y-m-d", "d-m-Y", "-");
        //echo '<pre>';print_r($row);exit;
        if (empty($row)) {
            $this->_helper->FlashMessenger(
                    array(
                        'error' => 'User with Id does not exist',
                    )
            );

            $this->_redirect($this->formatURL('/corp_kotak_customer/opsrejected/'));
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
            
        
        
        $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/corp_kotak_customer/opsrejected';
        $this->view->item = $row;
    }
    
   public function editAction()
    {      
        $this->title = 'Edit Customer Details';
        $m = new App\Messaging\Corp\Kotak\Agent();
        $user = Zend_Auth::getInstance()->getIdentity();        
        $session = new Zend_Session_Namespace('App.Agent.Controller'); 
       
        $objCustomerModel = new Corp_Kotak_Customers();
        $objCustomerDetailModel = new Corp_Kotak_CustomerDetail();
        $id = $this->_getParam('id');
        $custDetails = $objCustomerModel->findById($id);
        $objCustomerProduct = new Corp_Kotak_CustomerProduct();
        $products  = new Products();
        $bankModel  = new Banks();
        $objBaseTxn = new BaseTxn();
        //$objAgBal = new AgentBalance();
        $config = App_DI_Container::get('ConfigObject');
        $minAge = $this->view->minAge = $config->remitter->age->min;
        $currDate = date('Y-m-d');
        //$maxAge = $config->remitter->age->max;
        $uploadlimit = $config->agent->uploadfile->size;
        $errorExists = false;
        $docModel = new Documents();
        // Get our form and validate it
        $form = new Corp_Kotak_EditCustomerDetailsForm();  
        $formData  = $this->_request->getPost();
        $customerLogModel = new Corp_Kotak_CustomersLog();
        $this->view->form = $form;
        $bank = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        $product = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_AMUL);
        $bankUnicode = $product->bank->unicode;
        $productUnicode = $product->product->unicode;
        $productList = $products->getAgentProducts($user->id, PROGRAM_TYPE_CORP);       
//        $form->getElement("product_id")->setMultiOptions($productList);
        $form->getElement("bc_code")->setValue($user->agent_code);
        $isError = FALSE;
        $custDetails = Util::toArray($custDetails);
        
        $state = new CityList(); 
      
            $this->view->remitterData = $formData;
            // getting current ip to store in db
            $ip = $objCustomerModel->formatIpAddress(Util::getIP());
            
            if ($this->getRequest()->isPost()) {
                    $formData['cty'] = $formData['city'];
                    $formData['pin'] = $formData['pincode'];
                    $formData['comm_cty'] = $formData['comm_city'];
                    $formData['c_pin'] = $formData['comm_pin'];
                if($form->isValid($this->getRequest()->getPost())){
               
                    $this->view->formData = $formData;
                    
                    $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'] , "d-m-Y", "Y-m-d", "-");
                    $customerData = array(  
                                                'member_id' => $formData['member_id'],
                                                'afn' => $formData['afn'],
                                                'place_application' => $formData['place_application'],
//                                                'bc_code' => $formData['bc_code'],
                                                'landline' => $formData['landline'],
                                                'aadhaar_no' => $formData['aadhaar_no'],
                                                'society_id' => $formData['society_id'],
                                                'society_name' => $formData['society_name'],
                                                'nominee_name' => $formData['nominee_name'],
                                                'nominee_relationship' => $formData['nominee_relationship'],
                                                'member_id' => $formData['member_id'],
                                                'first_name'=>$formData['first_name'],
                                                'middle_name'=>$formData['middle_name'],
                                                'last_name'=>$formData['last_name'],
                                                'name_on_card'=>$formData['name_on_card'],
                                                'product_id'=>$formData['product_id'],
                                                'address_line1'=>$formData['address_line1'],
                                                'address_line2'=>$formData['address_line2'],
                                                'city'=>$formData['city'],
                                                'state'=> $state->getStateName($formData['state']),
                                                'pincode'=>$formData['pincode'],
                                                'comm_address_line1'=>$formData['comm_address_line1'],
                                                'comm_address_line2'=>$formData['comm_address_line2'],
                                                'comm_city'=>$formData['comm_city'],
                                                'comm_state'=> $state->getStateName($formData['comm_state']),
                                                'comm_pin'=>$formData['comm_pin'],
                                                'mobile'=>$formData['mobile'],                            
                                                'date_of_birth'=>$formData['date_of_birth'],                                                       
                                                'mother_maiden_name'=>$formData['mother_maiden_name'],                                                       
                                                'email'=>$formData['email'],
                                                'by_agent_id'=>$user->id,
                                                'ip'=> $ip,
                                                'id_proof_type' => $formData['id_proof_type'],
                                                'id_proof_number' => $formData['id_proof_number'],
                                                'address_proof_type' => $formData['address_proof_type'],
                                                'address_proof_number' => $formData['address_proof_number'],
                                                'status' => STATUS_PENDING,
                                                'status_bank' => STATUS_PENDING,
                                                'status_ops' => STATUS_PENDING,
                                                'status_ecs' => STATUS_PENDING,
                        
                        ); 

                 
                        try{  
                                   
            
              //Upload Remitter photo
            $profilePhotoFile = isset($_FILES['profile_pic']['name'])?$_FILES['profile_pic']['name']:'';      
            // getting files name 
                $idDocFile = isset($_FILES['id_doc_path']['name'])?$_FILES['id_doc_path']['name']:'';
                $addrDocFile = isset($_FILES['address_doc_path']['name'])?$_FILES['address_doc_path']['name']:'';

                if(trim($idDocFile)=='')
                    unset($_FILES['id_doc_path']);
                if(trim($addrDocFile)=='')
                    unset($_FILES['address_doc_path']);
              
                  if(trim($profilePhotoFile)==''){
                    unset($_FILES['profile_pic']);
                    
                  }
                  
                  if ($profilePhotoFile!='' || $addrDocFile != '' || $idDocFile !='') {
                      
                        //upload files
                        $upload = new Zend_File_Transfer_Adapter_Http();     
                        
                        // Add Validators for uploaded file's extesion , mime type and size
                        $upload->addValidator('Extension', false, array('jpg', 'jpeg','pdf','png'=>false))
                                ->addValidator('FilesSize', false, array('min' => '5kB', 'max' => $uploadlimit));
                       
                        $upload->setDestination(UPLOAD_PATH_KOTAK_AMUL_DOC);
                        
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
               
                
                    }
                    else {
                        $this->_helper->FlashMessenger(
                                                        array(
                                                                'msg-error' => 'Documents could not be uploaded. Allowed Formats are Jpeg,Jpg,Pdf only.',
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
                if($isError == FALSE)  {
            // updating customer details in db.
                    unset($custDetails['id']);
                    $custDetails['product_customer_id'] = $id;
            // Saving in details table    
            $objCustomerModel->update($customerData,"id = $id");
            $objCustomerDetailModel->save($custDetails);
            
            
            
            
            // updating status 
            // 
              $data = array('product_customer_id' => $id,'by_type' => BY_MAKER,'by_id' => $user->id, 
                    'status_ops_old' => STATUS_REJECTED,'status_ops_new' => STATUS_PENDING,'ip' => $ip,'comments' => $formData['comments']
                    );
               
                  
                    $customerLogModel->save($data);
            //*********************upload files********************//
            //
              if ($profilePhotoFile!='' || $addrDocFile != '' || $idDocFile !='') {    
               $uploadedData = $form->getValues();

                $id_doc_path = $this->_getParam('id_doc_path');
                $add_doc_path = $this->_getParam('address_doc_path'); 

                $nameId = $upload->getFileName('id_doc_path');
                $nameAdd = $upload->getFileName('address_doc_path');
                
                 /*** identification doc upload case ***/
                    if(!empty($nameId)){

                        $destId = $upload->getDestination('id_doc_path');
                        $sizeId = $upload->getFileSize('id_doc_path');

                        // get the file name and extension
                        $extId = explode(".", $nameId);

                        
                        // add document details along with agent id to DB
                         $dataID = array('doc_kotak_amul_id' => $id , 'by_agent_id' => $user->id, 'ip' => $objCustomerModel->formatIpAddress(Util::getIP()),
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
                         $objCustomerModel->update($dataArrId ,"id = $id");

                    } 
                    /*** identification doc upload case over ***/


                    /*** address doc upload case ***/
                    if(!empty($nameAdd)){

                    $destAdd = $upload->getDestination('address_doc_path');
                    $sizeAdd = $upload->getFileSize('address_doc_path');

                    # Returns the mimetype for the 'doc_path' form element
                    //$mimeType = $upload->getMimeType($doc_path);
                    // get the file name and extension
                       $extAdd = explode(".", $nameAdd);


                    // add document details along with agent id to DB
                    $dataAdd = array('doc_kotak_amul_id' => $id ,'doc_rat_corp_id' => $id, 'by_agent_id' => $user->id, 'ip' => $objCustomerModel->formatIpAddress(Util::getIP()),
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
                    $objCustomerModel->update($dataArrId ,"id = $id");
                } 
               /*** address doc upload case over ***/
                
                
                 $photo = $this->_getParam('profile_pic');
                 $namePhoto = $upload->getFileName('profile_pic');
                
                
                 /*** identification doc upload case ***/
                    if(!empty( $namePhoto)){

                        $destPhoto = $upload->getDestination('profile_pic');
                        $sizePhoto = $upload->getFileSize('profile_pic');

                        // get the file name and extension
                        $extphoto = explode(".",  $namePhoto);

                        
                          //mark previous doc inactive
                         $docModel->updateDocs($custDetails['photo_doc_id']);
                        // add document details along with agent id to DB
                         $dataphoto = array('doc_kotak_amul_id' => $id, 'by_agent_id' => $user->id, 'ip' => $objCustomerModel->formatIpAddress(Util::getIP()),
                         'doc_type' => 'photo', 'file_name' => '', 'file_type' => $extphoto['1'], 'status' => STATUS_ACTIVE);
                         
                         if($custDetails['photo_doc_id'] > 0){ 
                         //mark previous doc inactive
                         $docModel->updateDocs($custDetails['photo_doc_id']);
                        }
                         $resphoto = $docModel->saveCustomerDocs($dataphoto);
                         
                         $renameFilephooto = $resphoto. '.' . $extphoto['1'];
                         // rename the file and update the record
                         $dataArrphoto = array('file_name' => $renameFilephooto);
                         $updateId = $docModel->renameDocs($resphoto, $dataArrphoto);
                        

                         // Rename uploaded file using Zend Framework
                         $fullFilePathphoto = $destPhoto . '/' . $renameFilephooto;
                         $filterFileRenamephoto = new Zend_Filter_File_Rename(array('target' => $fullFilePathphoto, 'overwrite' => true));
                         $filterFileRenamephoto->filter( $namePhoto);
                            // rename the file and update the record
                         $dataArrphoto = array('photo_doc_id' => $resphoto);
                         $objCustomerModel->update($dataArrphoto ,"id = $id");

                    } 
                    /*** identification doc upload case over ***/
                }

            //*********************END upload files********************//


                  
                  $this->_helper->FlashMessenger(
                                    array(
                                        'msg-success' => 'Customer details updated and application resubmitted to Operation',
                                    )
                            );
                 $this->_redirect($this->formatURL('/corp_kotak_customer/opsrejected'));
                 $form->populate($formData);
                        }
                  // End of Upload remitter photo 
                                
                         }catch (Exception $e ) { 
                                                        $errorExists = true; 
                                                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                                                        $form->populate($formData);
                                                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                                    }                 
         
       
            
            }
            
    }  
            $custDetails['state'] = $state->getStateCode($custDetails['state']); 
            $custDetails['comm_state'] = $state->getStateCode($custDetails['comm_state']); 
            $custDetails['date_of_birth'] = Util::returnDateFormatted($custDetails['date_of_birth'],"Y-m-d","d-m-Y", "-");
            $custDetails['cty'] = $custDetails['city'];
            $custDetails['pin'] = $custDetails['pincode'];
            $custDetails['comm_cty'] = $custDetails['comm_city'];
            $custDetails['c_pin'] = $custDetails['comm_pin'];
            $form->populate($custDetails);
            $this->view->errorExists = $errorExists;
            
 } // adddetails action closes here
        
        
        /* registrationcompleteAction is responsible for handling unset the required session details of remitter       
        */
        public function editcompleteAction(){
            
         $this->title = 'Edit Customer - Complete';
         
        }
   
}