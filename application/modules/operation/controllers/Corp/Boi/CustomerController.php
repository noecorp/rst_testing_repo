<?php

/**
 * HIC Default entry point
 *
 * @author Mini
 */
class Corp_Boi_CustomerController extends App_Operation_Controller {

    //put your code here


    public function init() {
        parent::init();
    }

    public function indexAction() {
        
    }

       public function searchAction() {
        $this->title = 'Pending Customers Applications';
        $data['sub'] = $this->_getParam('sub');
        $form = new Corp_Boi_CardholderSearchSplForm(array('action' => $this->formatURL('/corp_boi_customer/search'),
            'method' => 'POST',
        ));
        $customerModel = new Corp_Boi_Customers();
        $page = $this->_getParam('page');
        $params = array('state' => $this->_getParam('state'),'pincode' => $this->_getParam('pincode'),
            'date_created' => $this->_getParam('date_created'),'mobile' => $this->_getParam('mobile'),
            'ref_num' => $this->_getParam('ref_num'),
            'items_per_page' => 300);
        
        if ($data['sub'] != '') {
            
        $this->view->paginator = $customerModel->showPendingCustomerDetails($page ,$params);
        
        }
        $form->populate($params);
        $this->view->form = $form;
        $this->view->sub = $data['sub'];
        $this->view->formData = $params;
     
    }
    
     public function exportsearchAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $qurStr['state'] = $this->_getParam('state');
        $qurStr['pincode'] = $this->_getParam('pincode');
        $qurStr['date_created'] = $this->_getParam('date_created');
        $qurStr['mobile'] = $this->_getParam('mobile');
        $qurStr['ref_num'] = $this->_getParam('ref_num');
       
        $form = new Corp_Boi_CardholderSearchSplForm(array('action' => $this->formatURL('/corp_boi_customer/exportsearch/'),
                                              'method' => 'GET',
                                       ));  
        
               
                 $qurData['state'] =  $qurStr['state'];       
                 $qurData['pincode'] =  $qurStr['pincode'];       
                 $qurData['date_created'] =  $qurStr['date_created'];       
                 $qurData['mobile'] =  $qurStr['mobile'];       
                 $qurData['ref_num'] =  $qurStr['ref_num'];       
                     
                 $customerModel = new Corp_Boi_Customers();
                 $exportData = $customerModel->exportshowPendingCustomerDetails($qurData);
                
                 $columns = array(
            'Application Reference Number',
            'Status of the application.',
            'Linked Branch SOL ID',
            'Title',
            'First Name',
            'Middle Name',
            'Surname',
            'Aadhaar Number',
            'Aadhaar Enrollment ID',
            'NSDC Enrollment No.',
            'Debit Mandate Amount',
            'Training Center ID',
            'Traning Center Name',
            'Training Partner Name',
            'PAN',
            'Sex',
            'Date of Birth',
            'Marital Status',
            'Occupation',
            'Permanent Address Line 1',
            'Permanent Address Line 2',
            'State',
            'City',
            'PIN Code',
            'Correspondence Address Line 1',
            'Correspondence Address Line 2',
            'Correspondence State',
            'Correspondence City',
            'Correspondence Pincode',
            'Telephone',
            'Mobile Number',
            'Email',
            'Nomination Required',
            'Nominee Name',
            'Nominee Relationship',
            'Nominee Date Of Birth: (e.g. dd-mm-yyyy)',
            'Nominee Address Line 1',
            'Nominee Address Line 2',
            'Nominee City',
            'Minor Guardian',
            'Minor Guardian Relationship',
            'Date Created',
            'Date Updated'
                     
        );


        $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'pending_customers');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/corp_boi_customer/exportsearch?state='.$qurStr['state'].'&pincode='.$qurStr['pincode'].'&mobile='.$qurStr['mobile'].'&ref_num='.$qurStr['ref_num'].'&date_created='.$qurStr['date_created'])); 
                                       }
           
          
       }
       
     public function approveAction(){      
        $this->title = 'Approve Customer Details';
        $m = new App\Messaging\Corp\Boi\Agent();
        $user = Zend_Auth::getInstance()->getIdentity();        
        $session = new Zend_Session_Namespace('App.Agent.Controller'); 
       
        $objCustomerModel = new Corp_Boi_Customers();
        $objCustomerDetailModel = new Corp_Boi_CustomerDetail();
        $id = $this->_getParam('id');
        $custDetails = $objCustomerModel->findById($id);
        $objCustomerProduct = new Corp_Boi_CustomerProduct();
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
        // Get our form and validate it
        $form = new Corp_Boi_ApproveCustomerDetailsForm();  
        $formData  = $this->_request->getPost();
        $customerLogModel = new Corp_Boi_CustomersLog();
        $this->view->form = $form;
        $custDetails = Util::toArray($custDetails);
        $product = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NDSC);
        $productUnicode = $product->product->unicode;
        $productModel = new Products();
        $productInfo = $productModel->getProductInfoByUnicode($productUnicode);
            // getting current ip to store in db
            $ip = $objCustomerModel->formatIpAddress(Util::getIP());
            
            if ($this->getRequest()->isPost()) {
                if($form->isValid($this->getRequest()->getPost())){
               
                    $this->view->formData = $formData;
                    
                    $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'] , "d-m-Y", "Y-m-d", "-");
                    $formData['nominee_dob'] = Util::returnDateFormatted($formData['nominee_dob'] , "d-m-Y", "Y-m-d", "-");
                   
                    $customerData =  array(
                    'product_id' => $formData['product_id'],
                    'ref_num' => $custDetails['ref_num'],
                    'title' => $formData['title'],
                    'first_name' => $formData['first_name'],
                    'middle_name' => $formData['middle_name'],
                    'last_name' => $formData['last_name'],
                    'date_of_birth' => $formData['date_of_birth'],
                    'gender' => $formData['gender'],
                    'occupation' => $formData['occupation'],
                    'mobile' => $formData['mobile'],
                    'landline' => $formData['landline'],
                    'email' => $formData['email'],
                    'aadhaar_no' => $formData['aadhaar_no'],
                    'pan' => $formData['pan'],
                    'uid_no' => $formData['uid_no'],
                    'address_line1' => $formData['address_line1'],
                    'address_line2' => $formData['address_line2'],
                    'city' => $formData['city'],
                    'state' => $formData['state'],
                    'pincode' => $formData['pincode'],
                    'comm_address_line1' => $formData['comm_address_line1'],
                    'comm_address_line2' => $formData['comm_address_line2'],
                    'comm_city' => $formData['comm_city'],
                    'comm_state' => $formData['comm_state'],
                    'comm_pin' => $formData['comm_pin'],
                    'marital_status' => $formData['marital_status'],
//                    'cust_comm_code' => $formData['cust_comm_code'],
                    'nsdc_enrollment_no' => $formData['nsdc_enrollment_no'],
                    'nomination_flg' => $formData['nomination_flg'],
                    'nominee_name' => $formData['nominee_name'],
                    'nominee_relationship' => $formData['nominee_relationship'],
                    'nominee_dob' => $formData['nominee_dob'],
                    'nominee_add_line1' => $formData['nominee_add_line1'],
                    'nominee_add_line2' => $formData['nominee_add_line2'],
                    'nominee_city_cd' => $formData['nominee_city_cd'],
                    'nominee_minor_flag' => $formData['nominee_minor_flag'],
                    'nominee_minor_guradian_cd' => $formData['nominee_minor_guradian_cd'],
                    'debit_mandate_amount' => $formData['debit_mandate_amount'],
                        'minor_flg' => $formData['minor_flg'],
                        'minor_guardian_name' => $formData['minor_guardian_name'],
                    'by_ops_id' => $user->id,
                    'ip' => $ip,
                    'date_updated' => date('Y-m-d H:i:s'),
                    'date_approval' => date('Y-m-d H:i:s'),
                );

                 
                        try{  
              
            $validateArr = array(
                     'tablename' => DbTable::TABLE_BOI_CORP_CARDHOLDER,
                     'product_id' => $productInfo->id,
                     'col_value' => $formData['pan'],
                     'col_name' => 'pan',
                     'col' => 'PAN',
                );
                /* Validating PAN Card */        
                $objValidator = new Validator();
                $objValidator->validatePAN($formData['pan']);
               if(trim($formData['pan']) != '' && $formData['pan'] != $custDetails['pan']){
                $objValidator->checkColDuplicacy($validateArr);
                }
                $aadhar_num = $formData['aadhaar_no'];
                if(trim($aadhar_num) != '')
                {
                   $objValidator->validateAadhar($aadhar_num);
                   $validateArr['col_value'] = $formData['aadhaar_no'];
                   $validateArr['col_name'] = 'aadhaar_no';
                   $validateArr['col'] = 'Aadhaar No.';
                   if($formData['aadhaar_no'] != $custDetails['aadhaar_no']){
                   $objValidator->checkColDuplicacy($validateArr);
                   }
                }
                
            $ismidminor = Validator::isMidMinor($formData['date_of_birth']); 
            if($ismidminor)
            {
                $customerData['minor_flg'] = 'Y';
            }
            else
            {
                $customerData['minor_flg'] = 'N';
            }
               if($formData['nomination_flg'] == 'Y'){
                    
                if(!$objValidator->validateYear($formData['nominee_dob'],18)) {
                    
                    $formData['nominee_minor_flag'] = 'Y';
                    if($formData['minor_guardian_name'] == '' || $formData['nominee_minor_guradian_cd'] == '') {
                        throw new Exception("Please enter Nominee Minor Guardian Details");
                    }
                }
                else
                {
                   $formData['nominee_minor_flag'] = 'N';
                   $customerData['nominee_minor_flag'] = 'N';
                   $customerData['minor_guardian_name'] ='';
                   $customerData['nominee_minor_guradian_cd'] ='';
                    
                }
                }
                else{
                   $customerData['nominee_name'] = '';
                   $customerData['nominee_relationship'] = '';
                   $customerData['nominee_dob'] = '';
                   $customerData['nominee_add_line1'] = '';
                   $customerData['nominee_add_line2'] = '';
                   $customerData['nominee_city_cd'] = '';
                   $customerData['nominee_minor_flag'] = 'N';
                   $customerData['minor_guardian_name'] ='';
                   $customerData['nominee_minor_guradian_cd'] ='';
                }
                 
            //echo "$ismidminor  <pre>";            print_r($customerData); exit;
            
            unset($custDetails['id']);
            $custDetails['product_customer_id'] = $id;
            // Saving in details table 
            $objCustomerModel->update($customerData,"id = $id");
            $objCustomerDetailModel->save($custDetails);
              
            
              $data = array('product_customer_id' => $id,'by_type' => BY_CHECKER,'by_id' => $user->id, 
                    'status_ops_old' => STATUS_PENDING,'status_ops_new' => STATUS_APPROVED,'ip' => $ip,'comments' => $formData['comments']
                    );
               
                  
                    $customerLogModel->save($data);
                    $params = array('status' => STATUS_APPROVED,'id' => $id);
                    $res = $objCustomerModel->changeStatus($params);
               
                  $this->_helper->FlashMessenger(
                                    array(
                                        'msg-success' => 'Customer approved',
                                    )
                            );
                 $this->_redirect($this->formatURL('/corp_boi_customer/search'));
                 $form->populate($formData);
            
                            
                  }catch (Exception $e ) { 
                                                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                                                        $form->populate($formData);
                                                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                                    }   



               
            }
            
    }  
            $custDetails['date_of_birth'] = Util::returnDateFormatted($custDetails['date_of_birth'] , "Y-m-d", "d-m-Y", "-");
            $custDetails['nominee_dob'] = Util::returnDateFormatted($custDetails['nominee_dob'], "Y-m-d" , "d-m-Y", "-");
            $form->populate($custDetails);
      
    }
    
    public function rejectAction(){
        $this->title = 'Reject Customer';
        

        $customerLogModel = new Corp_Boi_CustomersLog();
        $customerModel = new Corp_Boi_Customers();
        $id = $this->_getParam('id');
        $custInfo = $customerModel->findById($id);
        $form = new Corp_Boi_RejectForm();        
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
                        $this->_redirect($this->formatURL('/corp_boi_customer/search'));                                                
                    

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
                $this->_redirect($this->formatURL('/corp_boi_customer/search/'));
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


        $data['searchCriteria'] = $this->_getParam('searchCriteria');
        $data['keyword'] = $this->_getParam('keyword');
        $data['sub'] = $this->_getParam('sub');
        $customerModel = new Corp_Boi_Customers();
        $page = $this->_getParam('page');
        $form = new Corp_Boi_CustomerSearchForm(array('action' => $this->formatURL('/corp_boi_customer/customerlist'),
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

        $this->view->backLink = 'searchCriteria=' . $data['searchCriteria'] . '&keyword=' . $data['keyword'] . '&sub=1';
        $this->view->controllerName = Zend_Registry::get('controllerName');
        $this->view->form = $form;
        $this->view->sub = $data['sub'];
    }
   
      public function uploaddeliveryflagAction() {
        $this->title = "Bulk Upload of Account Activation File";
        $page = $this->_getParam('page');
        $form = new Corp_Boi_DeliveryFlagForm();
        $formData = $this->_request->getPost();
        $deliveryFlag = new Corp_Boi_DeliveryFlag();
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
                $product = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NSDC);                
                $productModel = new Products();
                $productInfo = $productModel->getProductInfoByUnicode($product->product->unicode);
                $chkName = $deliveryFlag->checkBatchName($batchName, $productInfo->id);
                if($chkName){
                $fp = fopen($name, 'r');
                while (!feof($fp)) {
                    $line = fgets($fp);
                    if (!empty($line)) {
                        $delimiter = BOI_NSDC_DELIVERY_FILE_DELIMITER;
                        $dataArr = str_getcsv($line, $delimiter);
//                        $dataArr['product_id'] = $formData['product_id'];
                        $dataArr['batch_name'] = $batchName;
                        $consolidateArr[] = $dataArr;
                        $arrLength = Util::getArrayLength($dataArr);
                        if (!empty($dataArr)) {
                           
                            try {
                                // direct insert into rat_corp_cardholders
                                $dataArr['product_id'] = $productInfo->id;
                                $deliveryFlag->insertDeliveryFile($dataArr);
                
                            } catch (Exception $e) {
                                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            }

                        } 
                    }
                }
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => sprintf('Account activation file details added'),
                    )
                );
                                
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
        $cardholdersModel = new Corp_Boi_Customers();
        $documentDetails = array();
        $id = $this->_getParam('id');
        if (!is_numeric($id)) {
            $this->_helper->FlashMessenger(
                    array(
                        'error' => 'The user id you provided is invalid',
                    )
            );

            $this->_redirect($this->formatURL('/corp_boi_customer/index/'));
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

            $this->_redirect($this->formatURL('/corp_boi_customer/index/'));
        }
        
        
        $backLink = 'state=' . $this->_getParam('state') . '&pincode=' . $this->_getParam('pincode') . '&sub=1&date_created='.$this->_getParam('date_created');

        
        //Select Cardholder's Purse
        $this->view->cardholderPurses = array();
        if (isset($row->boi_customer_id) && $row->boi_customer_id > 0) {
            $cardHolder = new Corp_Boi_Customers();
            $this->view->cardholderPurses = $cardHolder->getCardholderPurses($row->boi_customer_id);
        }

       
         // Get status and comments
        $this->view->cardholderStatus = array();
            $cardHolderObj = new Corp_Boi_CustomersLog();
            $this->view->cardholderStatus = $cardHolderObj->getCardholderStatus($row->id);
            
        
        $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/corp_boi_customer/search?'.$backLink;
        
        $this->view->item = $row;
    }
    
    
   public function uploadcrnAction() {
        $this->title = "Bulk CRN Upload";
        $page = $this->_getParam('page');
        $form = new CRNMasterForm();
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $cardholdersModel = new CRNMaster();
        $this->view->records = FALSE;
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $product = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_AMUL);                
                $productModel = new Products();
                $productInfo = $productModel->getProductInfoByUnicode($product->product->unicode);
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->receive();
                $name = $upload->getFileName('doc_path');

                $batchName = $upload->getFileName('doc_path', $path = FALSE);// . '_' . $user->id;
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
                                $data['file'] = $batchName;
                                $data['product_id'] = $productInfo->id;
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
                    //echo "<pre>";print_r($this->_getAllParams());exit;
//echo $batch;
  //      exit("Updating Records");
                    try {

                        $cardholdersModel->crnBulkUpdate($formData['reqid'], STATUS_FREE);
                        $this->_helper->FlashMessenger(
                                array(
                                    'msg-success' => 'CRN have been updated in our records',
                                )
                        );
                        //$this->_redirect($this->formatURL('/corp_ratnakar_cardholder/uploadcardholders/'));
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
        $this->title = 'Account Activation File Report';
        $deliveryFlag = new Corp_Boi_DeliveryFlag();
        $page = $this->_getParam('page');
        $form = new Corp_Boi_DeliveryFileStatusForm(array('action' => $this->formatURL('/corp_boi_customer/deliverystatus'),
            'method' => 'POST',
        ));
        
        
        
        $data['sub'] = $this->_getParam('sub');
        $data['batchname'] = $this->_getParam('batchname');
        $data['to_date'] = $this->_getParam('to_date');
        $data['from_date'] = $this->_getParam('from_date');
        $data['submit'] = $this->_getParam('submit');
        
        //if($data['sub'] != '') {
        if($data['submit']) {
            
  //          if ($form->isValid($this->getRequest()->getPost())) {
            
                    if ($data['to_date'] != '' && $data['from_date'] != '') {

                    $data['to'] = Util::returnDateFormatted($data['to_date'], "d-m-Y", "Y-m-d", "-", "-");
                    $data['from'] = Util::returnDateFormatted($data['from_date'], "d-m-Y", "Y-m-d", "-", "-");
                    $titleDate['to'] = Util::returnDateFormatted($data['to_date'], "d-m-Y", "Y-m-d", "-");
                    $titleDate['from'] = Util::returnDateFormatted($data['from_date'], "d-m-Y", "Y-m-d", "-");
                    $pageTitle = 'Account Activation File Report from ' . Util::returnDateFormatted($titleDate['from'], "Y-m-d", "d-m-Y", "-");
                    $pageTitle .= ' to ' . Util::returnDateFormatted($titleDate['to'], "Y-m-d", "d-m-Y", "-");
                    }
            
            
            
               
                    //$dataRes = $deliveryFlag->findByBatchName($page,$data['batchname'],$paginate = NULL);
                    $dataRes = $deliveryFlag->getDeliveryStatus($page,$data,$paginate = NULL);

                    $this->view->paginator = $dataRes;
//                    $data = array('batchname' => $formData['batchname'],'sub' => 1);
                    $form->populate($data);
//           }
                
        }
        $this->view->formData = $data;
        $this->view->controllerName = Zend_Registry::get('controllerName');
        $this->view->form = $form;
    }
   
     public function exportdeliverystatusAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        $page = $this->_getParam('page');
        
        $qurStr['batchname'] = $this->_getParam('batchname');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        
        $form = new Corp_Boi_DeliveryFileStatusForm(array('action' => $this->formatURL('/corp_boi_customer/exportdeliverystatus/'),
                                              'method' => 'GET',
                                       ));  
        
       //  if($qurStr['batchname']!=''){    
//              if($form->isValid($qurStr)){ 
             
             
             
                 if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {

                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-");
                 }
               
                 $qurData['batchname'] =  $qurStr['batchname'];  
                 $deliveryFlag = new Corp_Boi_DeliveryFlag();
                 $exportData = $deliveryFlag->exportDeliveryStatus($qurData);
                
                 $columns = array(
                      'Product Name',
                      'Member Id',
                      'Card Number',
                      'Card Pack Id',
                      'Date',
                      'Delivery Status',
                      'Date Registration',
                      'Failed Reason',
                      'ECS Status'
                );
                
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'account_activation');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/corp_boi_customer/deliverystatus')); 
                                       }
           
/*          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Batch Name!') );
                    $this->_redirect($this->formatURL('/corp_boi_customer/exportdeliverystatus?batchname='.$qurStr['batchname'])); 
                 }    */
       }
    
       public function bankstatusAction() {
        $this->title = 'Bank Status Applications';
        $form = new Corp_Boi_BankStatusForm();
        $customerModel = new Corp_Boi_Customers();
        $page = $this->_getParam('page');
        $formData = $this->_request->getPost();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $params = array();
                $params['to'] = Util::returnDateFormatted($formData['to_date'], "d-m-Y", "Y-m-d", "-","-",'to');
                $params['from'] = Util::returnDateFormatted($formData['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                $params['afn'] = $formData['afn'];
                
                $this->view->paginator = $customerModel->showBankStatusDetails($page,$params);
            }
        }
        $this->view->form = $form;
    }
    
     public function resubmitAction(){
        $this->title = 'Resubmit Customer Application';
            
        $m = new App\Messaging\Corp\Kotak\Agent();
        $user = Zend_Auth::getInstance()->getIdentity();        
        $session = new Zend_Session_Namespace('App.Agent.Controller'); 
       
        $objCustomerModel = new Corp_Boi_Customers();
        $objCustomerDetailModel = new Corp_Boi_CustomerDetail();
        $id = $this->_getParam('id');
        $custDetails = $objCustomerModel->findById($id);
        $objCustomerProduct = new Corp_Boi_CustomerProduct();
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
        $customerLogModel = new Corp_Boi_CustomersLog();
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
                            
                /*Validating Date of Birth*/
                //Validator::isMinor($formData['date_of_birth']);                                   
            
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
                 $this->_redirect($this->formatURL('/corp_boi_customer/bankstatus'));
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
        $form = new Corp_Boi_AuthorizedApplicationForm();
        $page = $this->_getParam('page');
        $type = $this->_getParam('type');
        $file_id = $this->_getParam('id');
        
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
                    $params = array('start_date'=> Util::returnDateFormatted($formData['from_date'], "d-m-Y", "Y-m-d", "-"),'end_date'=> Util::returnDateFormatted($formData['to_date'], "d-m-Y", "Y-m-d", "-"));
                    $customerObject = new Corp_Boi_Customers();
                    $flag = $customerObject->generateAuthorizeFile($params);
                    $this->_helper->FlashMessenger( array('msg-success' => 'File generated successfully.') ); 
                 } catch (App_Exception $e) {
                     $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage()) ); 
                 } catch (Exception $e) {
                     $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage()) ); 
                 }
            }
         }
         
         $this->view->paginator = $fileObject->getListByLabel(KOTAK_AMUL_AUTH_FILE,$page);
         $this->view->form = $form;
    }    

  public function customerregistrationAction(){
         $this->title = 'Application Status Report';              
         // Get our form and validate it
         $form = new Corp_Boi_CustomerRegistrationForm(array('action' => $this->formatURL('/corp_boi_customer/customerregistration'),
                                                    'method' => 'POST',
                                             )); 
        
        $bankUnicodeArr = Util::bankUnicodesArray();
        $page = $this->_getParam('page');
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $bankBoi = App_DI_Definition_Bank::getInstance(BANK_BOI);
        $bankBoiUnicode = $bankBoi->bank->unicode;
        $qurStr['bank_unicode'] = $bankBoiUnicode;
        $productNSDC = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NDSC);
        $productNSDCUnicode = $productNSDC->product->unicode;
        $productModel = new Products();
        $productId = $productModel->getProductInfoByUnicode($productNSDCUnicode);
        
        $qurStr['product_id'] = $productId['id'];
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['date_approval'] = $this->_getParam('date_approval');
        $qurStr['ref_num'] = $this->_getParam('ref_num');
        $qurStr['nsdc_enrollment_no'] = $this->_getParam('nsdc_enrollment_no');
        $qurStr['status'] = $this->_getParam('status');
        $qurStr['sub'] = $sub;
        $qurStr['to_date']    = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
      
         if($sub!=''){ 
              if($form->isValid($qurStr)){ 
                
                 $objBank = new Banks();
                 $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                 if ($qurStr['dur'] != '') {
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                 $this->view->from = $fromDate[0];
                 $this->view->to   = $toDate[0];                 
                 $this->view->title = $this->getReportTitle('Application Status Report', $qurStr['dur'],0,FALSE,$qurStr['bank_unicode']);
                 
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurFrm['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $qurFrm['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    
                    
                    $this->view->title = 'Application Status Report '.$bankInfo->name.' for '.Util::returnDateFormatted($qurFrm['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurFrm['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurFrm['from'];
                    $this->view->to   = $qurFrm['to'];
                }
                else{
                    $this->view->title = 'Application Status Report of '.$bankInfo->name.' for Checker Date '.Util::returnDateFormatted($qurStr['date_approval'], "Y-m-d", "d-m-Y", "-");
                    
                }
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $qurData['date_approval'] = Util::returnDateFormatted($qurStr['date_approval'], "d-m-Y", "Y-m-d", "-");
                 $qurData['ref_num'] = $qurStr['ref_num'];
                 $qurData['nsdc_enrollment_no'] = $qurStr['nsdc_enrollment_no'];
                 $qurData['status'] = $qurStr['status'];
                 $objReports = new Reports();
                 $cardholders = $objReports->getCardholdersOps($qurData);
                 $this->view->paginator = $objReports->paginateByArray($cardholders, $page, $paginate = NULL);
                 $this->view->formData = $qurStr;
              }   
             
          }
            $this->view->form = $form;
            
    }
    /* exportcustomerregistrationAction function is responsible to create the csv file on fly with agent fund requests report data
     * and let user download that file.
     */
   
     public function exportcustomerregistrationAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
         // Get our form and validate it
         $form = new Corp_Boi_CustomerRegistrationForm(array('action' => $this->formatURL('/corp_boi_customer/customerregistration'),
                                                    'method' => 'POST',
                                             )); 
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['agent_id'] = $this->_getParam('agent_id');
        $qurStr['ref_num'] = $this->_getParam('aof_ref_num');
        $qurStr['nsdc_enrollment_no'] = $this->_getParam('nsdc_enrollment_no');
        $qurStr['status'] = $this->_getParam('status');
        $qurStr['date_approval'] = $this->_getParam('date_approval');
         if($qurStr['dur']!=''|| ($qurStr['to_date'] !='' && $qurStr['from_date']!='') || $qurStr['date_approval']!=''){ 
             
              if($form->isValid($qurStr)){ 
                  if ($qurStr['dur'] != '') {
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                }
                else{
                    $qurData['date_approval'] = Util::returnDateFormatted($qurStr['date_approval'], "d-m-Y", "Y-m-d", "-"); 
                }
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $qurData['ref_num'] = $qurStr['ref_num'];
                 $qurData['nsdc_enrollment_no'] = $qurStr['nsdc_enrollment_no'];
                 $qurData['status'] = $qurStr['status'];
                 $qurData['agent_id'] = $qurStr['agent_id'];
                 $objReports = new Reports();
                 $exportData = $objReports->exportgetCardholdersOps($qurData);
// column names & indexes
                $columns = array(
                    'AOF Reference Number',
                    'Name (of the Trainee)',
                    'Aadhaar Number',
                    'NSDC Enrollment Number',
                    'Checker Status',
                    'Authorizer Status',
                    'IFSC Code',
                    'Account No.',
                    'Card No.',
                    'Traning Center BC Name',
                    'Debit Mandate Amount',
                    'Training Center ID',
                    'Traning Center Name',
                    'Training Partner Name',
                    'Application Date',
                    'Checker Date',
                );


                $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'nsdc_cardholder_registration');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/corp_boi_customer/customerregistration?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&ref_num='.$qurStr['ref_num'].'&agent_id='.$qurStr['agent_id'].'&nsdc_enrollment_no='.$qurStr['nsdc_enrollment_no'].'&date_approval='.$qurStr['date_approval'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
                         $this->_redirect($this->formatURL('/corp_boi_customer/customerregistration?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&ref_num='.$qurStr['ref_num'].'&agent_id='.$qurStr['agent_id'].'&nsdc_enrollment_no='.$qurStr['nsdc_enrollment_no'].'&date_approval='.$qurStr['date_approval'])); 
                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                    $this->_redirect($this->formatURL('/corp_boi_customer/customerregistration?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&ref_num='.$qurStr['ref_num'].'&agent_id='.$qurStr['agent_id'].'&nsdc_enrollment_no='.$qurStr['nsdc_enrollment_no'].'&date_approval='.$qurStr['date_approval'])); 
                 }    
       }

       
    private function getReportTitle($reportTitle, $dur, $agentId = 0, $singleDayOnly=false,$bankUnicode = '')
    {
        
        $title = $reportTitle;
        if($agentId > 0)
        {
            $objAgent = new Agents();
            $agentInfo = $objAgent->findById($agentId);
            $title .= ' For '. $agentInfo->name;
            
        }
        if($bankUnicode != '')
        {
            $objBank = new Banks();
            $bankInfo = $objBank->getBankbyUnicode($bankUnicode);
            $title .= ' of '. $bankInfo->name;
            
        }
        if(!$singleDayOnly){
            $durationArr = Util::getDurationDates($dur);
            $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
            $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
            switch($dur)
            {
                case 'yesterday': 
                    $title .= ' For ' .$toDate[0]; break;
                case 'today': 
                    $title .= ' For ' .$toDate[0]; break;
                case 'week':
                case 'month':
                case 'default':
                    $title .= ' For ' .$fromDate[0]. ' to '.$toDate[0];break;
            }
      } 
         else{
            $dt = explode(' ', Util::returnDateFormatted($dur, "Y-m-d", "d-m-Y", "-"));;
            $title .= ' For '.$dt[0];
              
      }
        
        return $title;
        
    }
    
    
      public function cardmappingAction() {
        $this->title = "Upload Card Mapping File";
        $page = $this->_getParam('page');
        $form = new Corp_Boi_CardMappingForm();
        $formData = $this->_request->getPost();
        $cardMapping = new Corp_Boi_CardMapping();
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
                $product = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NSDC);                
                $productModel = new Products();
                $productInfo = $productModel->getProductInfoByUnicode($product->product->unicode);
                $chkName = $cardMapping->checkBatchName($batchName, $productInfo->id);
                if($chkName){
                $fp = fopen($name, 'r');
                while (!feof($fp)) {
                    $line = fgets($fp);
                    if (!empty($line)) {
                        $delimiter = BOI_NSDC_CARD_MAPPING_FILE_DELIMITER;
                        $dataArr = str_getcsv($line, $delimiter);
//                        $dataArr['product_id'] = $formData['product_id'];
                        $dataArr['batch_name'] = $batchName;
                        $consolidateArr[] = $dataArr;
                        $arrLength = Util::getArrayLength($dataArr);
                        if (!empty($dataArr)) {
                           
                            try {
                                // direct insert into rat_corp_cardholders
                                $dataArr['product_id'] = $productInfo->id;
                                $cardMapping->insertCardMappingFile($dataArr);
                
                            } catch (Exception $e) {
                                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            }

                        } 
                    }
                }
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => sprintf('Card Mapping file details added'),
                    )
                );
                                
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
    
    
    
    public function cuttofffileAction() {
ini_set('max_execution_time', 0);
        ini_set('memory_limit','400M');
//set_time_limit(600);
        $this->title = "TTUM File Generation";
        $page = $this->_getParam('page');
        $form = new Corp_Boi_CuttoffFileForm();
        $cardload = new Corp_Boi_Cardload();
        $fileObject = new Files();        
        $formData = $this->_request->getPost();
        $cardMapping = new Corp_Boi_CardMapping();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $this->view->records = FALSE;
        //$user = Zend_Auth::getInstance()->getIdentity();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                 try {
                    $params = array('start_date'=> Util::returnDateFormatted($formData['start_date'], "d-m-Y", "Y-m-d", "-"),'end_date'=> Util::returnDateFormatted($formData['end_date'], "d-m-Y", "Y-m-d", "-"));

                    $flag = $cardload->generateCuttoffFile($params);
                    $this->_helper->FlashMessenger( array('msg-success' => 'File generated successfully.') ); 
                 } catch (App_Exception $e) {
                     $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage()) ); 
                 } catch (Exception $e) {
                     $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage()) ); 
                 }                
                
            }
        }
       
        $this->view->form = $form;
        $this->view->paginator = $fileObject->getListByLabel(BOI_TTUM_FILE,$page);        
    }
    
    
         public function cardmappingstatusAction() {
        $this->title = 'Card Mapping File Report';
        $cardMapping = new Corp_Boi_CardMapping();
        $page = $this->_getParam('page');
        $form = new Corp_Boi_CardMappingStatusForm(array('action' => $this->formatURL('/corp_boi_customer/cardmappingstatus'),
            'method' => 'POST',
        ));
        $data['sub'] = $this->_getParam('sub');
        $data['batchname'] = $this->_getParam('batchname');
        $data['from_date'] = $this->_getParam('from_date');
        $data['to_date'] = $this->_getParam('to_date');
        
        if($data['sub'] != '') {
           // if ($form->isValid($this->getRequest()->getPost())) {
                    $qurdata['sub'] = $data['sub'];
                    $qurdata['batchname'] = $data['batchname'];
                    
                    if ($data['to_date'] != '' && $data['from_date'] != '') {

                        $qurdata['from'] = Util::returnDateFormatted($data['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                        $qurdata['to'] = Util::returnDateFormatted($data['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                    
                    }
                    
                    $dataRes = $cardMapping->getCardMappingStatus($page,$qurdata,$paginate = NULL);

                    $this->view->paginator = $dataRes;
//                    $data = array('batchname' => $formData['batchname'],'sub' => 1);
                    $form->populate($data);
                    $this->view->records = true;
//             }
                
         }
        $this->view->formData = $data;
        $this->view->controllerName = Zend_Registry::get('controllerName');
        $this->view->form = $form;
    }
    
    public function exportcardmappingstatusAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $qurStr['batchname'] = $this->_getParam('batchname');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['to_date'] = $this->_getParam('to_date');
       
       
        $form = new Corp_Boi_CardMappingStatusForm(array('action' => $this->formatURL('/corp_boi_customer/cardmappingstatus'),
            'method' => 'POST',
        ));
               
        $qurData['batchname'] =  $qurStr['batchname']; 

        if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
           $qurData['from_date'] =  Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");       
           $qurData['to_date'] =  Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");       
        }

        $cardMapping = new Corp_Boi_CardMapping();
        $exportData = $cardMapping->exportgetCardMappingStatus($qurData);

        $columns = array(
           'Product Name',
           'Card Number',
           'BOI Account Number',
           'BOI Customer ID',
           'Date Registration',
           'Failed Reason',
           'Status'
        );


        $objCSV = new CSV();
        try{
               $resp = $objCSV->export($exportData, $columns, 'card_mapping_status');exit;
        }catch (Exception $e) {
                                App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                $this->_redirect($this->formatURL('/corp_boi_customer/cardmappingstatus?batchname='.$qurStr['batchname'].'&from_date='.$qurStr['from_date'].'&to_date='.$qurStr['to_date'])); 
        }
           
          
    }
    
    
   public function walletstatusAction(){
       $this->title = 'Wallet Status Report';  
       // Get our form and validate it
        $form = new Corp_Boi_ExpWalletStatusForm(array('action' => $this->formatURL('/corp_boi_customer/walletstatus/'),
                                              'method' => 'POST',
                                       ));  
        $request = $this->getRequest();  
        $sub = $this->_getParam('sub');
        $qurStr['purse_master_id'] = $this->_getParam('purse_master_id');
        $qurStr['batch_name'] = $this->_getParam('batch_name');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['to_date'] = $this->_getParam('to_date');
        
        
         if($sub!=''){    
                $qurData['batch_name'] = $qurStr['batch_name'];  
              if($form->isValid($qurStr)){ 
                 $this->view->title = 'Wallet Details';
                 $this->view->batch_name = $qurData['batch_name'];
                 $page = $this->_getParam('page');
                 
                 
                 if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {

                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                 }
                    $qurData['batch_name'] = $qurStr['batch_name'];
                    $qurData['purse_master_id'] = $qurStr['purse_master_id'];
                 
                 $cardloadModel = new Corp_Boi_Cardload();
                 $loadreq = $cardloadModel->getLoadRequests($qurData);
                 $paginator = $cardloadModel->paginateByArray($loadreq, $page, $paginate = NULL);
                 $form->getElement('purse_master_id')->setValue($qurStr['purse_master_id']);
                 $form->getElement('batch')->setValue($qurStr['batch_name']);
//                 echo "<pre>";print_r($paginator);
                 $this->view->paginator=$paginator;
                 $this->view->sub = $sub;
               }   
              
          }
            $form->populate($qurStr);
            $this->view->form = $form;
            $this->view->formData = $qurStr; 
    }
         
   
    /* exportremittancereportAction function is responsible to create the csv file on fly with agent load/reload/remittance txns report data
     * and let user download that file.
     */
    
     public function exportwalletstatusAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $qurStr['batch_name'] = $this->_getParam('batch_name');
        $qurStr['purse_master_id'] = $this->_getParam('purse_master_id');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['to_date'] = $this->_getParam('to_date');
       
        //$form = new Corp_Ratnakar_WalletStatusForm(array('action' => $this->formatURL('/corp_kotak_cardload/exportwalletstatus/'),
        $form = new Corp_Boi_ExpWalletStatusForm(array('action' => $this->formatURL('/corp_boi_customer/exportwalletstatus/'),
                                              'method' => 'POST',
                                       ));  
        
         if($qurStr['purse_master_id']!=''){    
//              if($form->isValid($qurStr)){ 
               
                 $qurData['batch_name'] =  $qurStr['batch_name'];       
                 $qurData['purse_master_id'] =  $qurStr['purse_master_id'];
                 
                 
                 if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                 }
                  
                 $cardloadModel = new Corp_Boi_Cardload();
                 $exportData = $cardloadModel->exportLoadRequests($qurData);
                 
                 $columns = array(
                    'Txn Identifier Type',
                    'Card Number',
                    'Member Id',
                    'Amount',
                    'Currency',
                    'Narration',
                    'Wallet Code',
                    'Txn Number',
                    'Card Type',
                    'Corporate Id',
                    'Mode',
                    'Txn Reference No.',
                    'Failed Reason',
                    'Status'
                );
                
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'load_request');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/corp_boi_customer/exportwalletstatus?batch_name='.$qurStr['batch_name'].'&sub=1&purse_master_id='.$qurStr['purse_master_id'])); 
                                       }
           
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Batch Name!') );
                    $this->_redirect($this->formatURL('/corp_boi_customer/exportwalletstatus?batch_name='.$qurStr['batch_name'].'&sub=1&purse_master_id='.$qurStr['purse_master_id'])); 
                 }    
       }
       
       
         
    public function accountloadAction() {
	ini_set('max_execution_time', 0);
        ini_set('memory_limit','400M');
        
        $this->title = "Upload of Wallet Load File";
        $page = $this->_getParam('page');
        $form = new Corp_Boi_CardloadForm();
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $cardloadModel = new Corp_Boi_Cardload();
        $this->view->records = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->receive();
                $name = $upload->getFileName('doc_path');

                $batchName = $upload->getFileName('doc_path', $path = FALSE) ;
                $checkFile = $cardloadModel->checkFilename($batchName);
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
                    $cnt = 0;
                    $val = 0;
                    $fp = fopen($name, 'r');
                    while (!feof($fp)) {
                        $line = fgets($fp);
                        if (!empty($line) && strpos($line,CORP_WALLET_END_OF_FILE) === false) {
                            $delimiter = CORP_WALLET_UPLOAD_DELIMITER;
                            $dataArr = str_getcsv($line, $delimiter);
                            $dataArr['product_id'] = $formData['product_id'];
                            $dataArr['type'] = 'act';
                            $dataArr['count'] = $formData['count'];
                            $dataArr['value'] = $formData['value'];
                            $consolidateArr[] = $dataArr;
                            $arrLength = Util::getArrayLength($dataArr);
                            if (!empty($dataArr)) {
                                if ($arrLength != CORP_WALLET_UPLOAD_COLUMNS)
                                    $this->view->incorrectData = TRUE;
                                try {
                                    // direct insert into rat_corp_cardholders

                                    if ($dataArr[CORP_WALLET_MANDATORY_FIELD_INDEX] == '') {
                                        $status = STATUS_INCOMPLETE;
                                    } else {
                                        $status = STATUS_TEMP;
                                    }
                                    $cnt++;
                                    $val += $dataArr[2];
                                    $cardloadModel->insertLoadrequestBatch($dataArr, $batchName, $status);
                                } catch (Exception $e) {
                                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                }

                                
                            } else {
    //                            $this->_helper->FlashMessenger(array('msg-error' => 'Data format is not correct'));
                            }
                        }
                    }
               
                    if($cnt != $formData['count'])
                    {
                        $data = array('upload_status' => STATUS_FAILED, 
                            'failed_reason' => 'Count does not match the entered count',
                            'date_updated' => new Zend_Db_Expr('NOW()'));
                        $cardloadModel->updateLoadBatch($data, $batchName);
                        $this->_helper->FlashMessenger(
                            array(
                                'msg-error' => "Count does not match the entered count",
                            )
                        );
                        $this->view->records = FALSE;
                    }
                    //elseif($val - (int) Util::filterAmount($formData['value']) != 0)
                    elseif(!Util::compareAmount ($val,Util::filterAmount($formData['value'])))
                    {
                        $data = array('upload_status' => STATUS_FAILED, 
                            'failed_reason' => 'Value does not match the entered value of load',
                            'date_updated' => new Zend_Db_Expr('NOW()'));
                        $cardloadModel->updateLoadBatch($data, $batchName);
                        $this->_helper->FlashMessenger(
                            array(
                                'msg-error' => "Value does not match the entered value of load",
                            )
                        );
                        $this->view->records = FALSE;
                    }
                    else
                    {
                        $this->view->records = TRUE;
                        $this->view->paginator = $cardloadModel->showPendingCardloadDetails($batchName, $page, $paginate = NULL);
                    }
                    $this->view->batch_name = $batchName;

                    fclose($fp);
                }
                
                    
                
            }
        }

        if ($submit != '') {


            try {

                $cardloadModel->bulkAddActCardload($formData['reqid'], $formData['batch']);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Account details have been updated in our records',
                        )
                );
                $this->_redirect($this->formatURL('/corp_boi_customer/accountload/'));
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
    
    public function outputfileAction() {
        $this->title = "Application Output File";
        $page = $this->_getParam('page');
        $outputfile = new Corp_Boi_OutputFile();
        $this->view->paginator = $outputfile->showOutputfileDetails($page);        
    }
    
  public function bulkapproveAction() {
     
        $this->title = "Bulk Approve";
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $customerModel = new Corp_Boi_Customers();
        $customerLogModel = new Corp_Boi_CustomersLog();
        $objCustomerDetailModel = new Corp_Boi_CustomerDetail();
        $this->view->records = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
        if ($submit != '') {
            try {
                $customerModel->bulkApproval( $formData['reqid']);
            
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Customers have been approved',
                        )
                );
                  $this->_redirect($this->formatURL('/corp_boi_customer/search')); 
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
    public function bankpendingAction() {
        $this->title = 'Bank Pending Applications';
        $data['sub'] = $this->_getParam('sub');
        $form = new Corp_Boi_BankPendingForm(array('action' => $this->formatURL('/corp_boi_customer/bankpending'),
            'method' => 'POST',
        ));
        $customerModel = new Corp_Boi_Customers();
        $page = $this->_getParam('page');
        $params = array(
            'date_approval' => $this->_getParam('date_approval'),    
            'appRefNo'          =>      $this->_getParam('appRefNo'),
            'items_per_page' => 300);
        
        if ($data['sub'] != '') {
        $this->view->paginator = $customerModel->showBankPendingCustomers($page ,$params);
        
        }
        $form->populate($params);
        $this->view->form = $form;
        $this->view->sub = $data['sub'];
     
    }
    
     public function manualrejectAction(){
        $this->title = 'Reject Customer';
        

        $customerLogModel = new Corp_Boi_CustomersLog();
        $customerModel = new Corp_Boi_Customers();
        $id = $this->_getParam('id');
        $custInfo = $customerModel->findById($id);
        $form = new Corp_Boi_RejectForm();        
        $ip = $customerModel->formatIpAddress(Util::getIP());
        $user = Zend_Auth::getInstance()->getIdentity();
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $formData  = $this->_request->getPost();
                
               
                
                try{
                    $data = array('product_customer_id' => $id,'by_type' => BY_CHECKER,'by_id' => $user->id, 
                    'status_ops_old' => STATUS_APPROVED,'status_ops_new' => STATUS_REJECTED,'ip' => $ip,'comments' => $formData['remarks']
                    );
                    if($custInfo['output_file_id'] > 0) {
                            if($custInfo->prev_output_file_ids != '') {
                            $prevOut = $custInfo['prev_output_file_ids'].", ".$custInfo['output_file_id'];
                        } else {
                            $prevOut = $custInfo['output_file_id'];
                        }
                    } else {
                        $prevOut = $custInfo['prev_output_file_ids'];
                    }
               
                    $params = array('status' => STATUS_REJECTED,'id' => $id,
                        'date_approval' => new Zend_Db_Expr('NOW()'),
                        'prev_output_file_ids' => $prevOut,
                        'output_file_id' => 0
                        );
                    $res = $customerModel->toggleStatus($params);
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
                        $this->_redirect($this->formatURL('/corp_boi_customer/bankpending'));                                                
                    

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
                $this->_redirect($this->formatURL('/corp_boi_customer/bankpending/'));
            }
            
            $form->populate(Util::toArray($row));
            $this->view->item = $row;
        }
        $row = $customerModel->findById($id);
         
        $this->view->item = $row;
        $this->view->form = $form;
    }
    public function consolidatedreportAction(){
         $this->title = 'Consolidated Report';              
         // Get our form and validate it
         $form = new Corp_Boi_ConsolidatedReportForm(array('action' => $this->formatURL('/corp_boi_customer/consolidatedreport'),
                                                    'method' => 'POST',
                                             )); 
        
        $bankUnicodeArr = Util::bankUnicodesArray();
        $page = $this->_getParam('page');
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $bankBoi = App_DI_Definition_Bank::getInstance(BANK_BOI);
        $bankBoiUnicode = $bankBoi->bank->unicode;
        $qurStr['bank_unicode'] = $bankBoiUnicode;
        $productNSDC = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NDSC);
        $productNSDCUnicode = $productNSDC->product->unicode;
        $productModel = new Products();
        $productId = $productModel->getProductInfoByUnicode($productNSDCUnicode);
        
        $qurStr['product_id'] = $productId['id'];
        $qurStr['product'] = $productId['id'];
        $qurStr['dur'] = $this->_getParam('dur');
//        $qurStr['date_approval'] = $this->_getParam('date_approval');
        $qurStr['ref_num'] = $this->_getParam('ref_num');
        $qurStr['aadhaar_no'] = $this->_getParam('aadhaar_no');
        $qurStr['account_no'] = $this->_getParam('account_no');
        $qurStr['nsdc_enrollment_no'] = $this->_getParam('nsdc_enrollment_no');
        $qurStr['status'] = $this->_getParam('status');
        $qurStr['wallet_load_status'] = $this->_getParam('wallet_load_status');
        $qurStr['sub'] = $sub;
        $qurStr['to_date']    = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['agent_id']  = $this->_getParam('agent_id');
        $qurStr['agent']  = $this->_getParam('agent_id');
        $qurStr['training_partner']  = $this->_getParam('training_partner');
        
      
         if($sub!=''){ 
              if($form->isValid($qurStr)){ 
                
                 $objBank = new Banks();
                 $bankInfo = $objBank->getBankbyUnicode($qurStr['bank_unicode']);
                 if ($qurStr['dur'] != '') {
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                 $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                 $this->view->from = $fromDate[0];
                 $this->view->to   = $toDate[0];                 
                 $this->view->title = $this->getReportTitle('Consolidated Application Status Report', $qurStr['dur'],0,FALSE,$qurStr['bank_unicode']);
                 
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurFrm['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $qurFrm['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    
                    
                    $this->view->title = 'Consolidated Report '.$bankInfo->name.' for '.Util::returnDateFormatted($qurFrm['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurFrm['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurFrm['from'];
                    $this->view->to   = $qurFrm['to'];
                }
                else{
                    $this->view->title = 'Consolidated Report of '.$bankInfo->name;
                    
                }
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
//                 $qurData['date_approval'] = Util::returnDateFormatted($qurStr['date_approval'], "d-m-Y", "Y-m-d", "-");
                 $qurData['ref_num'] = $qurStr['ref_num'];
                 $qurData['aadhaar_no'] = $qurStr['aadhaar_no'];
                 $qurData['account_no'] = $qurStr['account_no'];
                 $qurData['nsdc_enrollment_no'] = $qurStr['nsdc_enrollment_no'];
                 $qurData['status'] = $qurStr['status'];
                 $qurData['wallet_load_status'] = $qurStr['wallet_load_status'];
                 $qurData['agent_id'] = $qurStr['agent_id'];
                 $objReports = new Corp_Boi_Customers();
                 $cardholders = $objReports->getConsolidatedDetails($qurData);
                 
                 $this->view->paginator = $objReports->paginateByArray($cardholders, $page, $paginate = NULL);
                 $this->view->formData = $qurStr;
                 $form->getElement('agent')->setValue($qurData['agent_id']);
                 $form->populate($qurStr);
              }   
             
          }
            $this->view->form = $form;
            
    }
    /* exportcustomerregistrationAction function is responsible to create the csv file on fly with agent fund requests report data
     * and let user download that file.
     */
   
     public function exportconsolidatedreportAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
         // Get our form and validate it
         $form = new Corp_Boi_ConsolidatedReportForm(array('action' => $this->formatURL('/corp_boi_customer/exportconsolidatedreport'),
                                                    'method' => 'POST',
                                             )); 
        $qurStr['bank_unicode'] = $this->_getParam('bank_unicode');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date']  = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['aadhaar_no'] = $this->_getParam('aadhaar_no');
        $qurStr['account_no'] = $this->_getParam('account_no');
        $qurStr['ref_num'] = $this->_getParam('ref_num');
        $qurStr['nsdc_enrollment_no'] = $this->_getParam('nsdc_enrollment_no');
        $qurStr['status'] = $this->_getParam('status');
        $qurStr['wallet_load_status'] = $this->_getParam('wallet_load_status');
//        $qurStr['date_approval'] = $this->_getParam('date_approval');
        $qurStr['agent_id'] = $this->_getParam('agent_id');
//         if($qurStr['dur']!=''|| ($qurStr['to_date'] !='' && $qurStr['from_date']!='') || $qurStr['date_approval']!=''){ 
             
              if($form->isValid($qurStr)){ 
                  if ($qurStr['dur'] != '') {
                 $durationArr = Util::getDurationDates($qurStr['dur']);
                 $qurData['from'] = $durationArr['from'];
                 $qurData['to'] = $durationArr['to'];
                 
                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                }
//                else{
//                    $qurData['date_approval'] = Util::returnDateFormatted($qurStr['date_approval'], "d-m-Y", "Y-m-d", "-"); 
//                }
                 $qurData['aadhaar_no'] = $qurStr['aadhaar_no'];
                 $qurData['account_no'] = $qurStr['account_no'];
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['bank_unicode'] = $qurStr['bank_unicode'];
                 $qurData['ref_num'] = $qurStr['ref_num'];
                 $qurData['nsdc_enrollment_no'] = $qurStr['nsdc_enrollment_no'];
                 $qurData['status'] = $qurStr['status'];
                 $qurData['wallet_load_status'] = $qurStr['wallet_load_status'];
                 $qurData['agent_id'] = $qurStr['agent_id'];
                 $objReports = new Corp_Boi_Customers();
                 $exportData = $objReports->getConsolidatedDetails($qurData);
// column names & indexes
                $columns = array(
                    'AOF Reference Number',
                    'Application Date',
                    'Name (of the Trainee)',
                    'NSDC Enrollment Number',
                    'Aadhaar No.',
                    'Linked Branch ID',
                    'Transerv Status',
                    'Bank Status',
                    'Account No.',
                    'IFSC Code',
                    'Card No.',
                    'Debit Mandate Amount',
                    'NSDC Wallet Load Date',
                    'NSDC Load Amount',
//                    'Wallet Balance as on End Date',
                    'Available Balance on Wallet',
                    'Amount debited through POS',
                    'Wallet Auto Debit Date',
                    'Wallet Auto Debit Amount',
                    'Traning Center BC Name',
                    'Training Center ID',
                    'Training Center Name',
                    'Training Partner Name',
//                    'Current Balance on Wallet',
                );


                $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'consolidated_report');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/corp_boi_customer/exportconsolidatedreport?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&ref_num='.$qurStr['ref_num'].'&nsdc_enrollment_no='.$qurStr['nsdc_enrollment_no'].'&date_approval='.$qurStr['date_approval'].'&aadhaar_no='.$qurStr['aadhaar_no'].'&wallet_load_status='.$qurStr['wallet_load_status'].'&agent_id='.$qurStr['agent_id'].'&account_no='.$qurStr['account_no'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
                         $this->_redirect($this->formatURL('/corp_boi_customer/exportconsolidatedreport?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&ref_num='.$qurStr['ref_num'].'&nsdc_enrollment_no='.$qurStr['nsdc_enrollment_no'].'&date_approval='.$qurStr['date_approval'].'&aadhaar_no='.$qurStr['aadhaar_no'].'&wallet_load_status='.$qurStr['wallet_load_status'].'&agent_id='.$qurStr['agent_id'].'&account_no='.$qurStr['account_no'])); 
                      }             
      /*    } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
                    $this->_redirect($this->formatURL('/corp_boi_customer/exportconsolidatedreport?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&ref_num='.$qurStr['ref_num'].'&nsdc_enrollment_no='.$qurStr['nsdc_enrollment_no'].'&date_approval='.$qurStr['date_approval'].'&aadhaar_no='.$qurStr['aadhaar_no'].'&wallet_load_status='.$qurStr['wallet_load_status'].'&agent_id='.$qurStr['agent_id'].'&account_no='.$qurStr['account_no'])); 
                 }*/    
       }
}
