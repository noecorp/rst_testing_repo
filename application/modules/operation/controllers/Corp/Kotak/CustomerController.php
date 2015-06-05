<?php

/**
 * HIC Default entry point
 *
 * @author Mini
 */
class Corp_Kotak_CustomerController extends App_Operation_Controller {

    //put your code here



    public function init() {
        parent::init();
    }

    public function indexAction() {
        
    }

       public function searchAction() {
        $this->title = 'Pending Customers List';
        $customerModel = new Corp_Kotak_Customers();
        $form = new Corp_Kotak_PendingCardholderSearchForm();
        $formData  = $this->_request->getPost();
        $data['sub'] = $this->_getParam('sub');
        $page = $this->_getParam('page');
        $params = array('product_id' => $this->_getParam('product_id'),'state' => $this->_getParam('state'),'pincode' => $this->_getParam('pincode'),
            'date_created' => $this->_getParam('date_created'));
        if ($data['sub'] != '') {
        
             
        $params['pin'] =$params['pincode'];
        if(!empty($params['product_id'] )){
        $this->view->paginator = $customerModel->showPendingCustomerDetails($page,$params);
        }
        else
        {
            $this->_helper->FlashMessenger(
                            array('msg-error' => 'Please select Product',)
                                                      );
        }
        $form->populate($params);
            
        }
        $form->populate($params);
        $this->view->form = $form;
        $this->view->sub = $data['sub'];
    }
     
     public function approveAction(){      
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
        $form = new Corp_Kotak_ApproveCustomerDetailsForm();  
        $formData  = $this->_request->getPost();
        $customerLogModel = new Corp_Kotak_CustomersLog();
        $this->view->form = $form;
        $bank = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        $product = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_AMUL);
        $bankUnicode = $product->bank->unicode;
        $productUnicode = $product->product->unicode;
        $productList = $products->getProductDD($custDetails->product_id);       
        $form->getElement("product_id")->setMultiOptions($productList);
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
                                                'by_ops_id'=>$user->id,
                                                'ip'=> $ip,
                                                'id_proof_type' => $formData['id_proof_type'],
                                                'id_proof_number' => $formData['id_proof_number'],
                                                'address_proof_type' => $formData['address_proof_type'],
                                                'address_proof_number' => $formData['address_proof_number'],
                                                'date_approval' => new Zend_Db_Expr('NOW()')
                        
                        
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
//             echo '<pre>';print_r($customerData);exit('ghghghghg');
            $objCustomerModel->update($customerData,"id = $id");
            $objCustomerDetailModel->save($custDetails);
            
            
            
            
            // updating status 
            // 
              $data = array('product_customer_id' => $id,'by_type' => BY_CHECKER,'by_id' => $user->id, 
                    'status_ops_old' => STATUS_PENDING,'status_ops_new' => STATUS_APPROVED,'ip' => $ip,'comments' => $formData['comments']
                    );
               
                  
                    $customerLogModel->save($data);
                    $params = array('status' => STATUS_APPROVED,'id' => $id);
                    $res = $objCustomerModel->changeStatus($params);
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
                         $dataID = array('doc_kotak_amul_id' => $id , 'by_ops_id' => $user->id, 'ip' => $objCustomerModel->formatIpAddress(Util::getIP()),
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
                    $dataAdd = array('doc_kotak_amul_id' => $id ,'doc_rat_corp_id' => $id, 'by_ops_id' => $user->id, 'ip' => $objCustomerModel->formatIpAddress(Util::getIP()),
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
                         $dataphoto = array('doc_kotak_amul_id' => $id, 'by_ops_id' => $user->id, 'ip' => $objCustomerModel->formatIpAddress(Util::getIP()),
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
                                        'msg-success' => 'Customer approved',
                                    )
                            );
                 $this->_redirect($this->formatURL('/corp_kotak_customer/search'));
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
      
    }
    
    public function rejectAction(){
        $this->title = 'Reject Customer';
        

        $customerLogModel = new Corp_Kotak_CustomersLog();
        $customerModel = new Corp_Kotak_Customers();
        $id = $this->_getParam('id');
        $custInfo = $customerModel->findById($id);
        $form = new Corp_Kotak_RejectForm();        
        $ip = $customerModel->formatIpAddress(Util::getIP());
        $user = Zend_Auth::getInstance()->getIdentity();
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $formData  = $this->_request->getPost();
                
               
                
                try{
                    $data = array('product_customer_id' => $id,'by_type' => BY_CHECKER,'by_id' => $user->id, 
                    'status_ops_old' => STATUS_PENDING,'status_ops_new' => STATUS_REJECTED,'ip' => $ip,'comments' => $formData['remarks']
                    );
               
                    $params = array('status' => STATUS_REJECTED,'id' => $id,'date_approval' => new Zend_Db_Expr('NOW()'));
                    $res = $customerModel->changeStatus($params);
                    $customerLogModel->save($data);
                    
                } catch(Exception $e){
                     App_Logger::log($e->getMessage(), Zend_Log::ERR);
                     $Msg = $e->getMessage();
                }
             
                if($res){
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'Customer successfully Rejected.',
                    )
                );
                        $this->_redirect($this->formatURL('/corp_kotak_customer/search'));                                                
                    

                }
                else
                {
                    $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => $Msg,
                    )
                );
                }
                
                
            }
        }else{
            $row = $customerModel->findById($id);
            
            if (empty($row)) {
                $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => sprintf('We cannot find Customer with id %s', $id),
                    )
                );
                $this->_redirect($this->formatURL('/corp_kotak_customer/search/'));
            }
            
            $form->populate(Util::toArray($row));
            $this->view->item = $row;
        }
        $row = $customerModel->findById($id);
         
        $this->view->item = $row;
        $this->view->form = $form;
    }
    
      public function customerlistAction() {
        $this->title = 'Search Customers';


        $data['product_id'] = $this->_getParam('product_id');
        $data['searchCriteria'] = $this->_getParam('searchCriteria');
        $data['keyword'] = $this->_getParam('keyword');
        $data['sub'] = $this->_getParam('sub');
        $customerModel = new Corp_Kotak_Customers();
        $page = $this->_getParam('page');
        $form = new Corp_Kotak_CustomerSearchForm(array('action' => $this->formatURL('/corp_kotak_customer/customerlist'),
            'method' => 'POST',
        ));
//        if ($this->getRequest()->isPost()) {
//            if ($form->isValid($this->getRequest()->getPost())) {
                if ($data['sub'] != '') {

                    $dataRes = $customerModel->searchCustomer($data);

                    $this->view->paginator = $customerModel->paginateByArray($dataRes, $page, $paginate = NULL);
                    $form->populate($data);
                }
//            }
//        }

        $this->view->backLink = 'searchCriteria=' . $data['searchCriteria'] . '&keyword=' . $data['keyword'] . '&sub=1&product_id='.$data['product_id'];
        $this->view->controllerName = Zend_Registry::get('controllerName');
        $this->view->form = $form;
        $this->view->sub = $data['sub'];
    }
   
      public function uploaddeliveryflagAction() {
        $this->title = "Bulk Upload of Delivery Flag";
        $page = $this->_getParam('page');
        $form = new Corp_Kotak_DeliveryFlagForm();
        $formData = $this->_request->getPost();
        $deliveryFlag = new Corp_Kotak_DeliveryFlag();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $this->view->records = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->receive();
                $name = $upload->getFileName('doc_path');

                $batchName = $upload->getFileName('doc_path', $path = FALSE) ; 
                
                $chkName = $deliveryFlag->checkBatchName($batchName, $formData['product_id']);
                if($chkName){
                $fp = fopen($name, 'r');
                while (!feof($fp)) {
                    $line = fgets($fp);
                    if (!empty($line)) {
                        $delimiter = CORP_DELIVERY_FILE_DELIMITER;
                        $dataArr = str_getcsv($line, $delimiter);
//                        $dataArr['product_id'] = $formData['product_id'];
                        $dataArr['batch_name'] = $batchName;
                        $consolidateArr[] = $dataArr;
                        $arrLength = Util::getArrayLength($dataArr);
                        if (!empty($dataArr)) {
                           
                            try {
                                // direct insert into rat_corp_cardholders
                                $dataArr['product_id'] = $formData['product_id'];
                                $deliveryFlag->insertDeliveryFile($dataArr);
                
                            } catch (Exception $e) {
                                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            }

                        } 
                    }
                }
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => sprintf('Delivery file details added'),
                    )
                );
//                $this->_redirect($this->formatURL('/corp_kotak_customer/index/'));
                                
               $this->view->batch_name = $batchName;

                fclose($fp);
                }
            else
            {
                 $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => sprintf('File name exists in the records'),
                    )
                );
            }
            

               
            }
              }
        
       
        $this->view->form = $form;
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

            $this->_redirect($this->formatURL('/corp_kotak_customer/index/'));
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

            $this->_redirect($this->formatURL('/corp_kotak_customer/index/'));
        }
        $search = $this->_getParam('searchCriteria');
        $keyword = $this->_getParam('keyword');
        $sub = $this->_getParam('sub');
        $backLink = 'searchCriteria=' . $search . '&keyword=' . $keyword . '&sub=1';

        
        //Select Cardholder's Purse
        $this->view->cardholderPurses = array();
        if (isset($row->kotak_customer_id) && $row->kotak_customer_id > 0) {
            $cardHolder = new Corp_Kotak_Customers();
            $this->view->cardholderPurses = $cardHolder->getCardholderPurses($row->kotak_customer_id);
        }

       
         // Get status and comments
        $this->view->cardholderStatus = array();
            $cardHolderObj = new Corp_Kotak_CustomersLog();
            $this->view->cardholderStatus = $cardHolderObj->getCardholderStatus($row->id);
            
        
        
        $this->view->item = $row;
    }
    
    
   public function uploadcrnAction() {
        $this->title = "Bulk CRN Upload";
        $page = $this->_getParam('page');
        $form = new Corp_Kotak_CRNMasterForm();
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $batch = $this->getRequest()->getParam('batch');
        $this->view->incorrectData = FALSE;
        $cardholdersModel = new CRNMaster();
        $this->view->records = FALSE;
        if ($this->getRequest()->isPost()) {
            //check empty file fields
            if($batch==""){
                $DocFile = isset($_FILES['doc_path']['name'])?$_FILES['doc_path']['name']:'';
                if(trim($DocFile)==''){ 
    
                    $this->_helper->FlashMessenger(
                        array(
                                'msg-error' => 'Please select document.',
                             )
                    );
                }
            }
            if ($form->isValid($this->getRequest()->getPost())) {
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->receive();
                $name = $upload->getFileName('doc_path');

                $batchName = $upload->getFileName('doc_path', $path = FALSE);// . '_' . $user->id;
                
                //check file extension...
                $upload->addValidator('Extension', false, array(FILE_TYPE_CSV, 'case' => false));
                
                $file_ext = Util::getFileExtension($name);
                if(strtolower($file_ext) != strtolower(FILE_TYPE_CSV))
                {
                   $this->_helper->FlashMessenger( array('msg-error' => 'Invalid file uploaded. Allowed Format is csv only.',) );
                   $this->_redirect($this->formatURL('/corp_kotak_customer/uploadcrn/'));
                }
                
                $fp = fopen($name, 'r');
                $consolidateArr = array();
                while (!feof($fp)) {
                    
                    $line = fgets($fp);
                    if (!empty($line)) {
                        $delimiter = CRN_MASTER_FILE_SEPARATOR;
                        $dataArr = str_getcsv($line, $delimiter);
                        //$consolidateArr[] = $dataArr;
                        if (!empty($dataArr)) {
                            try {
                                // direct insert into rat_corp_cardholders
                                $data['card_number'] = $dataArr[0];
                                $data['card_pack_id'] = $dataArr[1];
                                $data['member_id'] = $dataArr[2];
                                $data['date_expiry'] = $dataArr[3];
                                $data['file'] = $batchName;
                                $data['product_id'] = $formData['product_id'];
                                if($cardholdersModel->checkDuplicate($data)) {
                                    $data['status'] = STATUS_DUPLICATE;
                                } else {
                                    $data['status'] = STATUS_TEMP;
                                }
                                $crnMasterId = $cardholdersModel->insertMasterCRN($data);
                                $data['master_id'] = $crnMasterId;
                                $consolidateArr[] = $data;
                            } catch (Exception $e) {
                                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            }
                        } else {
                            $this->_helper->FlashMessenger(array('msg-error' => 'Invalid file or format'));
                        }
                    }
                }
                //echo "<pre>";print_r($consolidateArr);exit;

                
                //$this->view->batch_name = $batchName;

                fclose($fp);
            }
            if(!empty($consolidateArr)) {
                //echo "<pre>";print_r($consolidateArr);exit;
                $this->view->batch = $batchName;
                $this->view->records = TRUE;
                 $this->view->paginator = $cardholdersModel->paginateByArray($consolidateArr, $page, NULL);
                 $this->view->paginator->setItemCountPerPage(0);
            }
        }
        if ($this->getRequest()->isPost()) {
            $batch = $this->getRequest()->getParam('batch');
                if ($batch != '') {
   
                    try {

                        $cardholdersModel->crnBulkUpdate($formData['reqid'], STATUS_FREE);
                        $this->_helper->FlashMessenger(
                                array(
                                    'msg-success' => 'CRN have been updated in our records',
                                )
                        );
                       $this->_redirect($this->formatURL('/corp_kotak_customer/uploadcrn/'));
                    } catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $this->_helper->FlashMessenger(
                                array(
                                    'msg-error' => $e->getMessage(),
                                )
                        );
                    }
                }
        }
        $this->view->form = $form;
    }    
    
        
      public function deliverystatusAction() {
        $this->title = 'Delivery File Status';
        $deliveryFlag = new Corp_Kotak_DeliveryFlag();
        $page = $this->_getParam('page');
        $form = new Corp_Kotak_DeliveryFileStatusForm(array('action' => $this->formatURL('/corp_kotak_customer/deliverystatus'),
            'method' => 'POST',
        ));
        $formData  = $this->_request->getPost();
        $data['sub'] = $this->_getParam('sub');
        $data['batchname'] = $this->_getParam('batchname');
        $data['batch'] = $this->_getParam('batchname');
        $data['product_id'] = $this->_getParam('product_id');
       if ($data['sub'] != '') {
                   

                    $dataRes = $deliveryFlag->findByBatchName($page,$data,$paginate = NULL);

                    $this->view->paginator = $dataRes;
//                    $data = array('batchname' => $formData['batchname'],'sub' => 1);
                    $form->populate($data);
                
            }

        $this->view->controllerName = Zend_Registry::get('controllerName');
        $this->view->form = $form;
        $this->view->sub = $data['sub'];
    }
   
    
    
       public function bankstatusAction() {
        $this->title = 'Bank Status Applications';
        $customerModel = new Corp_Kotak_Customers();
        $page = $this->_getParam('page');
        $form = new Corp_Kotak_BankStatusForm();
        $formData = $this->_request->getPost();
        $data['sub'] = $this->_getParam('sub');
        $state = new CityList();
        $params = array();
        $params['product_id'] = $this->_getParam('product_id');
        $params['pincode'] = $this->_getParam('pincode');
        $params['date_approval'] = $this->_getParam('date_approval');
        $params['status'] = $this->_getParam('status');
        $stateCode = $this->_getParam('state');
        $params['state'] = $state->getStateName($stateCode);
       
           if ($data['sub'] != '') {
           
             
              if(!empty($params['product_id'] )){ 
             $this->view->paginator = $customerModel->showBankStatusDetails($page,$params);
              }
              else
              {
            $this->_helper->FlashMessenger(
                            array('msg-error' => 'Please select Product',)
                                                      );
             }
           $form->populate($params);
           }
         $form->populate($params);
        $this->view->form = $form;
    }
    
     public function resubmitAction(){
        $this->title = 'Resubmit Customer Application';
            
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
        $form = new Corp_Kotak_ResubmitCustomerDetailsForm();  
        $formData  = $this->_request->getPost();
        $customerLogModel = new Corp_Kotak_CustomersLog();
        $this->view->form = $form;
        $bank = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        $product = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_AMUL);
        $bankUnicode = $product->bank->unicode;
        $productUnicode = $product->product->unicode;
        $productList = $products->getProductDD($custDetails->product_id);       
        $form->getElement("product_id")->setMultiOptions($productList);
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
                                                'by_ops_id'=>$user->id,
                                                'ip'=> $ip,
                                                'id_proof_type' => $formData['id_proof_type'],
                                                'id_proof_number' => $formData['id_proof_number'],
                                                'address_proof_type' => $formData['address_proof_type'],
                                                'address_proof_number' => $formData['address_proof_number'],
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
            $objCustomerDetailModel->save($custDetails);
            $objCustomerModel->update($customerData,"id = $id");
            
             
                    // Submit to Partner
                    if(isset($formData['submit_partner'])){
                    $params = array('status' => STATUS_REJECTED,'id' => $id);
                    $res = $objCustomerModel->changeStatus($params);
                    
                     $data = array('product_customer_id' => $id,'by_type' => BY_CHECKER,'by_id' => $user->id, 
                    'status_ops_old' => STATUS_APPROVED,'status_ops_new' => STATUS_REJECTED,'ip' => $ip,'comments' => $formData['comments']
                    );
                    }
                    else if(isset($formData['submit_bank'])){// submit to Bank
                    $data = array('product_customer_id' => $id,'by_type' => BY_CHECKER,'by_id' => $user->id, 
                    'status_bank_old' => STATUS_REJECTED,'status_bank_new' => STATUS_PENDING,'ip' => $ip,'comments' => $formData['comments']
                    );
               
                    $params = array('status' => STATUS_PENDING,'id' => $id);
                    $res = $objCustomerModel->changeBankStatus($params);
                    }
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
                         $dataID = array('doc_kotak_amul_id' => $id , 'by_ops_id' => $user->id, 'ip' => $objCustomerModel->formatIpAddress(Util::getIP()),
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
                    $dataAdd = array('doc_kotak_amul_id' => $id ,'doc_rat_corp_id' => $id, 'by_ops_id' => $user->id, 'ip' => $objCustomerModel->formatIpAddress(Util::getIP()),
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
                         $dataphoto = array('doc_kotak_amul_id' => $id, 'by_ops_id' => $user->id, 'ip' => $objCustomerModel->formatIpAddress(Util::getIP()),
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
                                        'msg-success' => 'Customer Resubmitted',
                                    )
                            );
                 $this->_redirect($this->formatURL('/corp_kotak_customer/bankstatus'));
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
      
  }
  
    public function authorizedapplicationsAction() {
        $this->title = 'Download Authorized Applications';
        $fileObject = new Files();
        $objectModel = new ObjectRelations();
        $form = new Corp_Kotak_AuthorizedApplicationForm();
        $page = $this->_getParam('page');
        $type = $this->_getParam('type');
        $file_id = $this->_getParam('id');
        $product_id = $this->_getParam('product_id');
        
        if($type == 'download' && $file_id > 0) {
            $fileInfo = $fileObject->getFileInfo($file_id);
            if(!empty($fileInfo)) {
                $fileObject->setFilepath(APPLICATION_UPLOAD_PATH);
                $fileObject->setFilename($fileInfo['file_name']);
                $fileObject->download();
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender(TRUE);
            } else {
                $this->_helper->FlashMessenger( array('msg-error' => 'File not found.') );                 
            }
        } 
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $formData = $this->_request->getPost();
                try {
                    $params = array(
                        'start_date'=> Util::returnDateFormatted($formData['from_date'], "d-m-Y", "Y-m-d", "-"),
                        'end_date'=> Util::returnDateFormatted($formData['to_date'], "d-m-Y", "Y-m-d", "-"),
                        'product_id' => $product_id);
                    $customerObject = new Corp_Kotak_Customers();
                    $flag = $customerObject->generateAuthorizeFile($params);
                    $this->_helper->FlashMessenger( array('msg-success' => 'File generated successfully.') );  
                 } catch (App_Exception $e) {
                     $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage()) ); 
                 } catch (Exception $e) {
                     $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage()) ); 
                 }
            }
            if($product_id != '') {
                $productModel = new Products();
                $prodInfo = $productModel->getProductInfo($product_id);
                if($prodInfo['const'] == PRODUCT_CONST_KOTAK_AMULWB) {
                    $label = KOTAK_AMUL_AUTH_FILE;
                } elseif($prodInfo['const'] == PRODUCT_CONST_KOTAK_AMULGUJ) {
                    $label = KOTAK_AMULGUJ_AUTH_FILE;
                }
                $this->view->paginator = $fileObject->getListByLabel($label,$page);       
            } else{
                $this->_helper->FlashMessenger( array('msg-error' => 'Product id missing') );
            }
        }
        $this->view->form = $form;
    }    

}
