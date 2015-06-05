<?php

/**
 * HIC Default entry point
 *
 * 
 */
class Corp_Kotak_CardholderController extends App_Operation_Controller {

    //put your code here



    public function init() {
        parent::init();
    }

    public function indexAction() {
        
    }

    public function uploadcardholdersAction() {
        
        $this->title = "Bulk Upload of Cardholders";
        $page = $this->_getParam('page');
        $form = new Corp_Kotak_UploadcardholdersForm();
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $cardholdersModel = new Corp_Kotak_Customers();
        $this->view->records = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
       
        if ($this->getRequest()->isPost()) {
            
            if ($submit != '') {
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
                                $this->view->rejectpaginator = $cardholdersModel->showFailedPendingCardholderDetails($batchName,$formData['product_id'], $page, $paginate = NULL,$force = TRUE);
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
                $statusECS = ($formData['crd_type'] == STATUS_ACTIVATION_PENDING) ? STATUS_ACTIVATION_PENDING : STATUS_ECS_PENDING;
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
    
    
    
    public function cardholderactivationreqAction() {
        $this->title = "Bulk Upload of Cardholders- Activation Required";
        $page = $this->_getParam('page');
        $form = new Corp_Kotak_CardholderactrequploadForm();
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $cardholdersModel = new Corp_Kotak_Customers();
        $this->view->records = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
        
        if ($this->getRequest()->isPost()) {
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
                   $this->_redirect($this->formatURL('/corp_kotak_cardholder/cardholderactivationreq/'));
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
                            $dataArr['product_id'] = $formData['product_id'];;
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
                                $this->view->rejectpaginator = $cardholdersModel->showFailedPendingCardholderDetails($batchName, $dataArr['product_id']);
                                $this->view->paginator = $cardholdersModel->showPendingCardholderDetails($batchName, $dataArr['product_id']);
                            } else {
                            }
                        }
                    }
                }

                $this->view->records = TRUE;
                $this->view->batch_name = $batchName;

                fclose($fp);
            }
        }

        if ($submit != '') {


            try {
                $cardholdersModel->bulkAddCardholder($formData['reqid'], $formData['batch'],STATUS_ACTIVATION_PENDING);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Cardholder details have been updated in our records',
                        )
                );
                $this->_redirect($this->formatURL('/corp_kotak_cardholder/cardholderactivationreq/'));
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
    
    public function batchstatusAction() {
        $this->title = 'Cardholder Batch Status';

        $data['product_id'] = $this->_getParam('product_id');
        $data['batch_name'] = $this->_getParam('batch_name');
        $data['start_date'] = $this->_getParam('start_date');
        $data['end_date'] = $this->_getParam('end_date');
        $data['sub'] = $this->_getParam('sub');
        $cardholdersModel = new Corp_Kotak_Customers();
        $page = $this->_getParam('page');
        $form = new Corp_Kotak_BatchStatusForm(array('action' => $this->formatURL('/corp_kotak_cardholder/batchstatus'),
            'method' => 'POST',
        ));
        if ($data['sub'] != '') {
            if ($this->getRequest()->isPost()) {
                if ($form->isValid($this->getRequest()->getPost())) {
                   
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
                        'product_id'  => $data['product_id'],
                    ));
                    
                    $form->getElement('batch')->setValue($data['batch_name']);
                    $this->view->paginator = $cardholdersModel->paginateByArray($batchdetails, $page, $paginate = NULL);
                    $form->populate($data);
                }
            }
            else {
                    if(isset($data['start_date']) && !empty($data['start_date'])) {
                        $startdate = Util::returnDateFormatted($data['start_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
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
                        'product_id'  => $data['product_id'],
                    ));
               
                $form->getElement('batch')->setValue($data['batch_name']);
                $this->view->paginator = $cardholdersModel->paginateByArray($batchdetails, $page, $paginate = NULL);
                $form->populate($data);
            }
        }
        
        $this->view->backLink = 'batch_name=' . $data['batch_name'] . '&product_id=' . $data['product_id'] . '&sub=1&start_date=' . $data['start_date'] . '&end_date=' . $data['end_date'];
        $this->view->controllerName = Zend_Registry::get('controllerName');
        $this->view->form = $form;
        $this->view->sub = $data['sub'];
        $this->view->batch_name = $data['batch_name'];
        $this->view->start_date = $data['start_date'];
        $this->view->end_date = $data['end_date'];
        $this->view->product_id = $data['product_id'];
        
        //$this->view->formData = $data; 
    }
    
    
    public function exportbatchstatusAction(){
        
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['batch_name'] = $this->_getParam('batch_name');
        $data['start_date'] = $this->_getParam('start_date');
        $data['end_date'] = $this->_getParam('end_date');
        
        $form = new Corp_Kotak_BatchStatusForm(array('action' => $this->formatURL('/corp_kotak_cardholder/batchstatus'),
            'method' => 'POST',
        ));
        
         //if($qurStr['batch_name']!=''){    
//              if($form->isValid($qurStr)){ 
               
                 $cardholdersModel = new Corp_Kotak_Customers();
                 
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
                        'batch_name'  => $qurStr['batch_name'],
                        'start_date'  => $startdate,
                        'end_date'  => $enddate,
                        'product_id'  => $qurStr['product_id'],
                    ));
                 
                 $columns = array(
                    'Member Id',
                    'Employee Id',
                    'Card Number',
                    'Name',
                    'Name on Card',
                    'Gender',
                    'Date of Birth',
                    'Mobile',
                    'Email',
                    'Employer Name',
                    'Corporate Id',
                    'Status',
                    'Failed Reason',
                );
                
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'batch_status');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/corp_kotak_cardholder/batchstatus?batch_name='.$qurStr['batch_name'].'&product_id='.$qurStr['product_id'].'&sub=1')); 
                                       }
  
       }
    
    public function searchcardholdersAction() {
        $this->title = 'Search Cardholders';

        $data['product_id'] = $this->_getParam('product_id');
        $data['searchCriteria'] = $this->_getParam('searchCriteria');
        $data['keyword'] = $this->_getParam('keyword');
        $data['status'] = STATUS_ACTIVE;
        $data['sub'] = $this->_getParam('sub');
        
        $cardholdersModel = new Corp_Kotak_Customers();
        $page = $this->_getParam('page');
        $form = new Corp_Kotak_SearchcardholdersForm(array('action' => $this->formatURL('/corp_kotak_cardholder/searchcardholders'),
            'method' => 'POST',
        ));
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                if ($data['sub'] != '') {

                    $dataRes = $cardholdersModel->searchCustomer($data);
                    $this->view->paginator = $cardholdersModel->paginateByArray($dataRes, $page, $paginate = NULL);
                    $form->populate($data);
                }
            }
        }
        elseif($data['sub'] != '') {
                    $dataRes = $cardholdersModel->searchCustomer($data);
                    $this->view->paginator = $cardholdersModel->paginateByArray($dataRes, $page, $paginate = NULL);
                    $form->populate($data);
        }
        
        $this->view->backLink = 'searchCriteria=' . $data['searchCriteria'] . '&keyword=' . $data['keyword'] . '&sub=1&status='.STATUS_ACTIVE. '&product_id='.$data['product_id'];
        $this->view->controllerName = Zend_Registry::get('controllerName');
        $this->view->form = $form;
        $this->view->formData = $data;
        
        $this->view->sub = $data['sub'];
    }
    
    
    
    public function exportsearchcardholdersAction() {
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['searchCriteria'] = $this->_getParam('searchCriteria');
        $qurStr['keyword'] = $this->_getParam('keyword');
        $qurStr['status'] = STATUS_ACTIVE;
        $qurStr['sub'] = $this->_getParam('sub');
        
        $form = new Corp_Kotak_SearchcardholdersForm(array('action' => $this->formatURL('/corp_kotak_cardholder/searchcardholders'),
            'method' => 'POST',
        ));

        
            if ($qurStr['searchCriteria'] != '' && $qurStr['keyword'] != '' && $qurStr['product_id'] !='') {
                

                $qurData['product_id'] = $qurStr['product_id'];
                $qurData['searchCriteria'] = $qurStr['searchCriteria'];
                $qurData['keyword'] = $qurStr['keyword'];
                $qurData['status'] = STATUS_ACTIVE;
                $qurData['sub'] = $qurStr['sub'];
                
                $customerModel = new Corp_Kotak_Customers();
                $exportData = $customerModel->exportsearchCustomer($qurData);

                $columns = array(
                    'Member Id',
                    'Employee Id',
                    'Card Number',
                    'Name',
                    'Name on Card',
                    'Gender',
                    'Date of Birth',
                    'Mobile',
                    'Email',
                    'Employer Name',
                    'Corporate Id',
                    'Status',
                );

                $objCSV = new CSV();
                try {
                    $resp = $objCSV->export($exportData, $columns, 'download_cardholder_report');
                    exit;
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                    $this->_redirect($this->formatURL('/corp_kotak_cardholder/exportsearchcardholders?searchCriteria=' . $qurStr['searchCriteria'] . '&keyword=' . $qurStr['keyword'] . '&sub=1&status='.STATUS_ACTIVE. '&product_id='.$qurStr['product_id']));
                }
            } else {
                $this->_helper->FlashMessenger(array('msg-error' => 'Invalid data'));
                $this->_redirect($this->formatURL('/corp_kotak_cardholder/exportsearchcardholders?searchCriteria=' . $qurStr['searchCriteria'] . '&keyword=' . $qurStr['keyword'] . '&sub=1&status='.STATUS_ACTIVE. '&product_id='.$qurStr['product_id']));
            }
        
    }
    
    
    public function pendingkycAction() {
        $this->title = 'Pending KYC Docs';

        $cardholdersModel = new Corp_Kotak_Customers();
        $form = new Corp_Kotak_PendingKYCForm();
        $page = $this->_getParam('page');
        
        //$data = array('from_date'=> Util::returnDateFormatted($this->_getParam('from_date'), "Y-m-d", "d-m-Y", "-"),'to_date'=> Util::returnDateFormatted($this->_getParam('to_date'), "Y-m-d", "d-m-Y", "-"));
        $data['from_date'] = $this->_getParam('from_date');
        $data['to_date'] = $this->_getParam('to_date');
        $data['product_id'] = $this->_getParam('product_id');
        $data['sub'] = $this->_getParam('sub');
        
        //echo "<pre>";print_r($data);exit;
         if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $formData = $this->_request->getPost();
                $params = array('from_date'=> Util::returnDateFormatted($formData['from_date'], "d-m-Y", "Y-m-d", "-"),'to_date'=> Util::returnDateFormatted($formData['to_date'], "d-m-Y", "Y-m-d", "-"),'product_id'=>$data['product_id']);
                $this->view->paginator = $cardholdersModel->getPendingKyc($page,$params);
                //$this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/corp_kotak_cardholder/pendingkyc';
                $this->view->backLink = 'from_date=' . $params['from_date'] . '&to_date=' . $params['to_date'] . '&sub=1&viewc=pendingkyc&product_id='.$formData['product_id'];
            }
         }
         elseif($data['sub'] != '') {
                $formData = $this->_request->getPost();
                $params = array('from_date'=> Util::returnDateFormatted($data['from_date'], "d-m-Y", "Y-m-d", "-"),'to_date'=> Util::returnDateFormatted($data['to_date'], "d-m-Y", "Y-m-d", "-"),'product_id'=>$data['product_id']);
                $this->view->paginator = $cardholdersModel->getPendingKyc($page,$params);
                //$this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/corp_kotak_cardholder/pendingkyc';
                $this->view->backLink = 'from_date=' . $params['from_date'] . '&to_date=' . $params['to_date'] . '&sub=1&viewc=pendingkyc&product_id='.$data['product_id'];
         }
        
         $form->populate($data);
         $this->view->form = $form;
    }
    
    public function uploadcrnAction() {
        $this->title = "Bulk CRN Upload";
        $page = $this->_getParam('page');
        $form = new Corp_Kotak_UploadcrnForm();
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
                   $this->_redirect($this->formatURL('/corp_kotak_cardholder/uploadcrn/'));
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
                       $this->_redirect($this->formatURL('/corp_kotak_cardholder/uploadcrn/'));
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
    
    
    public function uploadmaAction() {
        $this->title = "Manual Adjustment Upload";
        $page = $this->_getParam('page');
        $form = new Corp_Kotak_UploadcrnForm();
        $form->getElement('submitbutton')->setLabel('Upload Manual Adjustment');
        $form->getElement('doc_path')->setLabel('Manual Adjustment File *');
        $formData = $this->_request->getPost();
        
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $cardholdersModel = new KotakBatchAdjustment();
        $batch = $this->getRequest()->getParam('batch');
        $this->view->records = FALSE;
        if ($this->getRequest()->isPost()) {
            
            if ($batch == '') {    
                $idDocFile = isset($_FILES['doc_path']['name'])?$_FILES['doc_path']['name']:'';
                if(empty($idDocFile))
                {
                   $this->_helper->FlashMessenger( array('msg-error' => 'Please select document.',) );
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
                   $this->_redirect($this->formatURL('/corp_kotak_cardholder/uploadma/'));
                }
                
                if(!$cardholdersModel->checkDuplicateFile($batchName))
                {
                $fp = fopen($name, 'r');
                $consolidateArr = array();
                while (!feof($fp)) {
                    $line = fgets($fp);
                    if (!empty($line)) {
                        $delimiter = $cardholdersModel::FILE_SEPRATOR;
                        $dataArr = str_getcsv($line, $delimiter);
                        //echo "<pre>";print_r($dataArr);exit;
                        $arrLength = Util::getArrayLength($dataArr);
                        //echo $arrLength; exit; 
                        if (!empty($dataArr)) {
                            if ($arrLength != CORP_KOTAK_MANUAL_ADJUSTMENT_COLUMNS){
                                $this->_helper->FlashMessenger( array('msg-error' => 'Invalid file uploaded. Column count not matched.',) );
                                $this->_redirect($this->formatURL('/corp_kotak_cardholder/uploadma/'));
                            }
                            try {
                                $data['card_number'] = $dataArr[0];
                                $data['wallet_code'] = $dataArr[1];
                                $data['mode'] = $dataArr[2];
                                $data['rrn'] = $dataArr[4];
                                $data['narration'] = $dataArr[5];
                                $data['file'] = $batchName;
                                $data['product_id'] = $formData['product_id'];
                                $data['failed_reason'] = '';
                                if($cardholdersModel->checkDuplicate($data)) {
                                    $data['status'] = STATUS_DUPLICATE;
                                    $data['failed_reason'] = 'Duplicate entry';                                    
                                } else {
                                    if(!Util::validateAmount($dataArr[3],AMOUNT_INT)) {
                                        $data['status'] = STATUS_FAILED;
                                        $data['failed_reason'] = 'Invalid amount value "'.$dataArr[3].'"';
                                    } else {
                                        $data['status'] = STATUS_TEMP;
                                    }
                                }
                                $data['amount'] = Util::convertToPaisa($dataArr[3]);                                
                                
                                
                                $crnMasterId = $cardholdersModel->insertMasterData($data);
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
                fclose($fp);
                } else {
                           $this->_helper->FlashMessenger(array('msg-error' => 'Duplicate File'));                    
                }
            }
            if(!empty($consolidateArr)) {
                $this->_helper->FlashMessenger(array('msg-success' => 'File uploaded successfully'));      
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
                        $cardholdersModel->bulkManualUpdate($formData['reqid'], STATUS_PENDING);
                        $this->_helper->FlashMessenger(
                                array(
                                    'msg-success' => 'Manual Adjustment have been updated in our records',
                                )
                        );
                        $this->_redirect($this->formatURL('/corp_kotak_cardholder/uploadma/'));
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
    
    public function searchmaAction() {
        $this->title = 'Search Manual Adjustments';

        
        $data['file'] = $this->_getParam('file');
        $data['product_id'] = $this->_getParam('product_id');
        $data['sub'] = $this->_getParam('sub');
        $data['start_date'] = $this->_getParam('start_date');
        $data['end_date'] = $this->_getParam('end_date');
        $cardholdersModel = new KotakBatchAdjustment();
        $page = $this->_getParam('page');
        //$agentModel = new Agents();
        $form = new Corp_Kotak_SearchMAForm(array('action' => $this->formatURL('/corp_kotak_cardholder/searchma'),
            'method' => 'POST',
        ));
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                if ($data['sub'] != '') {

                    if(isset($data['start_date']) && !empty($data['start_date'])) {
                        $startdate = explode(' ', Util::returnDateFormatted($data['start_date'], "d-m-Y", "Y-m-d", "-"));
                    } else {
                        $startdate = '';
                    }
                    if(isset($data['end_date']) && !empty($data['end_date'])) {                    
                        $enddate = explode(' ', Util::returnDateFormatted($data['end_date'], "d-m-Y", "Y-m-d", "-"));
                    } else {
                        $enddate = '';
                    }
                    
                    $dataRes = $cardholdersModel->searchBatchByDate(array(
                        'start_date'    => $startdate,
                        'end_date'    => $enddate,
                        'file'    => $data['file'],
                        'product_id'    => $data['product_id'],
                    ));

                    $this->view->paginator = $cardholdersModel->paginateByArray($dataRes, $page, $paginate = NULL);
                    $form->populate($data);
                }
                $this->view->validFormSubmit= TRUE;
            }
        }



        //$this->view->backLink = 'searchCriteria=' . $data['searchCriteria'] . '&keyword=' . $data['keyword'] . '&sub=1';
        $this->view->controllerName = Zend_Registry::get('controllerName');
        $this->view->form = $form;
    }
    
    public function viewAction() {

        $viewc = $this->_getParam('viewc');
        $this->title = 'Cardholder Details';
        //$cardholdersModel = new Corp_Ratnakar_Cardholders();
        
        $cardholdersModel = new Corp_Kotak_Customers(); 
        $cardholderStatus = Zend_Registry::get("CORP_CARDHOLDER_STATUS");
        $documentDetails = array();
        $id = $this->_getParam('id');
        if (!is_numeric($id)) {
            $this->_helper->FlashMessenger(
                    array(
                        'error' => 'The user id you provided is invalid',
                    )
            );

            $this->_redirect($this->formatURL('/corp_kotak_cardholder/index/'));
        }

        $row = $cardholdersModel->findById($id);
        $documentsId = $cardholdersModel->customerIdDoclist($row->id_proof_doc_id);
        $documentsAdd = $cardholdersModel->customerAddDoclist( $row->address_proof_doc_id);
        $documentDetails = array('id_proof_doc' => (!empty($documentsId))?$documentsId['file_name']:0,'address_proof_doc' => (!empty($documentsAdd))?$documentsAdd['file_name']:0);
        
        $this->view->documents = $documentDetails;
        $row->gender = ucfirst($row->gender);
        $row->date_of_birth = Util::returnDateFormatted($row->date_of_birth, "Y-m-d", "d-m-Y", "-");
        $row->status = $cardholderStatus[$row->status];
        $row->date_failed = Util::returnDateFormatted($row->date_failed, "Y-m-d", "d-m-Y", "-");
        //echo '<pre>';print_r($row);exit;
        if (empty($row)) {
            $this->_helper->FlashMessenger(
                    array(
                        'error' => 'User with Id does not exist',
                    )
            );

            $this->_redirect($this->formatURL('/corp_kotak_cardholder/index/'));
        }
        
        
        
        if($viewc == 'kyc'){
            $product_id = $this->_getParam('product_id');
            $from_date = $this->_getParam('from_date');
            $to_date = $this->_getParam('to_date');
            $sub = $this->_getParam('sub');
            $backLink = 'from_date=' . $from_date . '&to_date=' . $to_date . '&sub=1&product_id='.$product_id;
            $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/corp_kotak_cardholder/pendingkyc?' . $backLink;
            //$this->view->backLink = 'from_date=' . $params['from_date'] . '&to_date=' . $params['to_date'] . '&sub=1&product_id='.$formData['product_id'];
            
        }elseif ($viewc == 'batchstatus') {
            $product_id = $this->_getParam('product_id');
            $batch_name = $this->_getParam('batch_name');
            $start_date = $this->_getParam('start_date');
            $end_date = $this->_getParam('end_date');
            $sub = $this->_getParam('sub');
            $backLink = 'batch_name=' . $batch_name . '&start_date=' . $start_date . '&end_date=' . $end_date . '&sub=1&product_id='.$product_id;
            $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/corp_kotak_cardholder/batchstatus?' . $backLink;
            
        }elseif ($viewc == 'approvalpending') {
            $product_id = $this->_getParam('product_id');
            $sub = $this->_getParam('sub');
            $backLink = 'product_id=' . $product_id . '&sub=1';
            $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/corp_kotak_cardholder/approvalpending?' . $backLink;
        
        }else{
            $product_id = $this->_getParam('product_id');
            $search = $this->_getParam('searchCriteria');
            $keyword = $this->_getParam('keyword');
            $sub = $this->_getParam('sub');
            $backLink = 'searchCriteria=' . $search . '&keyword=' . $keyword . '&sub=1&status='.STATUS_ACTIVE. '&product_id='.$product_id;
            $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/corp_kotak_cardholder/searchcardholders?' . $backLink;
        }
        
        
        //Select Cardholder's Purse
        $this->view->cardholderPurses = array();
        if (isset($row->kotak_customer_id) && $row->kotak_customer_id > 0) {
            $cardHolder = new Corp_Kotak_Customers();
            $this->view->cardholderPurses = $cardHolder->getCardholderPurses($row->kotak_customer_id);
        } 
        $this->view->item = $row;
    }

    public function upgradekycAction(){      
        $this->title = 'Upgrade to KYC';
        $user = Zend_Auth::getInstance()->getIdentity();        
        $objCustomerModel = new Corp_Kotak_Customers();
        $config = App_DI_Container::get('ConfigObject');
        $currDate = date('Y-m-d');
        $uploadlimit = $config->agent->uploadfile->size;
        $errorExists = false;
        $docModel = new Documents();
        $form = new Corp_Kotak_KYCupgradeDetailsForm();  
        $formData  = $this->_request->getPost();
        $customerLogModel = new Corp_Kotak_CustomersLog();
        $this->view->form = $form;
        $id = $this->_getParam('id');
        $custDetails = $objCustomerModel->findById($id);
        $custDetails = Util::toArray($custDetails);
//        echo '<pre>';print_r($custDetails);exit('nfnxc');
        $isError = FALSE;
        $productCorp = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_OPENLOOP_GPR);
        $productCorpUnicode = $productCorp->product->unicode;
        $productModel = new Products();
        $productId = $productModel->getProductInfoByUnicode($productCorpUnicode);
            if ($this->getRequest()->isPost()) {
                
                //check empty file fields
                $idDocFile = isset($_FILES['id_doc_path']['name'])?$_FILES['id_doc_path']['name']:'';
                $addrDocFile = isset($_FILES['address_doc_path']['name'])?$_FILES['address_doc_path']['name']:'';

                if(trim($idDocFile)=='' || trim($addrDocFile)==''){ 

                    $this->_helper->FlashMessenger(
                                                    array(
                                                            'msg-error' => 'Please select documents.',
                                                         )
                                                  );
                }
                  
                if($form->isValid($this->getRequest()->getPost())){
                 
                   
                  if(!empty($custDetails)){
                        try{  
                 // getting files name 
                $idDocFile = isset($_FILES['id_doc_path']['name'])?$_FILES['id_doc_path']['name']:'';
                $addrDocFile = isset($_FILES['address_doc_path']['name'])?$_FILES['address_doc_path']['name']:'';

                if(trim($idDocFile)=='')
                    unset($_FILES['id_doc_path']);
                if(trim($addrDocFile)=='')
                    unset($_FILES['address_doc_path']);
              
                  
                  if ( $addrDocFile != '' || $idDocFile !='') {
                      
                        /*** renaming upload files as same name files can also be upload successfully ***/
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
          
           
                 
                   
            //*********************upload files********************//
            //
              if ($addrDocFile != '' || $idDocFile !='') {    
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
                         $dataID = array('doc_cardholder_id' => $id,'doc_product_id'=> $productId->id, 'by_ops_id' => $user->id, 'ip' => $objCustomerModel->formatIpAddress(Util::getIP()),
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
                    $dataAdd = array('doc_cardholder_id' => $id,'doc_product_id'=> $productId->id , 'by_ops_id' => $user->id, 'ip' => $objCustomerModel->formatIpAddress(Util::getIP()),
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
                
                
                }

            //*********************END upload files********************//


                  
                
                 // Update kyc details
                  $params = array('customer_type' => TYPE_KYC,'recd_doc' => FLAG_YES,'date_recd_doc' => new Zend_Db_Expr('NOW()'),'date_toggle_kyc'=> new Zend_Db_Expr('NOW()'),'recd_doc_id' => $user->id);
             
                 $objCustomerModel->updateKYC($params,$id);
                 $data = array('product_customer_id' => $id,'by_type' => BY_CHECKER,'by_id' => $user->id, 
                    'status_kyc_old' => $custDetails['customer_type'],'status_kyc_new' => TYPE_KYC,'ip' => $objCustomerModel->formatIpAddress(Util::getIP()),'comments' => $formData['comments']
                    );
               
                  
                $customerLogModel->save($data); 
                 
                      $this->_helper->FlashMessenger(
                                    array(
                                        'msg-success' => 'KYC Details upgraded',
                                    )
                            );
                 $this->_redirect($this->formatURL('/corp_kotak_cardholder/upgradekycsearch'));
                 
                 
                    }
                
                                
                         }catch (Exception $e ) { 
                                                        $errorExists = true; 
                                                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                                                        $form->populate($formData);
                                                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                                    }                 
         
                }
            else{
                $this->_helper->FlashMessenger( array('msg-error' => 'Cardholder does not exist',) ); 
                                                        
            }
            }
            
    }  
           
            $form->populate($custDetails);
            $this->view->errorExists = $errorExists;
      
    }
    
    
    public function kycupgradereportAction() {
        $this->title = 'KYC Upgrade Report';
        
        $productCorp = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_OPENLOOP_GPR);
        $productCorpUnicode = $productCorp->product->unicode;
        $productModel = new Products();
        $productOptions = $productModel->getProductInfoByUnicode($productCorpUnicode); 
        
        $bankModel = new Banks();
        $bank_id = $productOptions['bank_id'];
        $bnk_arr = $bankModel->getBankInfo($bank_id);  
        $bank_name = $bnk_arr['name'];

        $data['product_id'] = $productOptions['id'];
        $data['from_date'] = $this->_getParam('from_date');
        $data['to_date'] = $this->_getParam('to_date');
        $data['dur'] = $this->_getParam('dur');
        $data['sub'] = $this->_getParam('sub');
        $page = $this->_getParam('page');
        
        $cardholdersModel = new Corp_Kotak_Customers();
        $form = new Corp_Kotak_KYCUpgradeReportForm(array('action' => $this->formatURL('/corp_kotak_cardholder/kycupgradereport'),
            'method' => 'POST',
        ));
        
   
        if ($data['sub'] != '') {
            if ($this->getRequest()->isPost()) {  
                if ($form->isValid($this->getRequest()->getPost())) {  
                   
                    if ($data['dur'] != '') {
                        $durationArr = Util::getDurationDates($data['dur']);
                        $startdate = $durationArr['from'];
                        $enddate = $durationArr['to'];
                    
                    } else if ($data['to_date'] != '' && $data['from_date'] != '') {
                        $startdate =  Util::returnDateFormatted($data['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                        $enddate = Util::returnDateFormatted($data['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    }
                    
                    $reportdetails = $cardholdersModel->getKycUpgradeReportByDate(array(
                       'from_date'  => $startdate,
                       'to_date'  => $enddate,
                       'product_id'  => $data['product_id'],
                    ));

                    $this->view->paginator = $cardholdersModel->paginateByArray($reportdetails, $page, $paginate = NULL);
                    $form->populate($data);
                }
            }
            else {
                    if ($data['dur'] != '') {
                        $durationArr = Util::getDurationDates($data['dur']);
                        $startdate = $durationArr['from'];
                        $enddate = $durationArr['to'];
                    
                    } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                       $startdate =  Util::returnDateFormatted($data['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                       $enddate = Util::returnDateFormatted($data['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                    }              
                 
                    $reportdetails = $cardholdersModel->getKycUpgradeReportByDate(array(
                        'from_date'  => $startdate,
                        'to_date'  => $enddate,
                        'product_id'  => $data['product_id'],
                    ));
               
                $this->view->paginator = $cardholdersModel->paginateByArray($reportdetails, $page, $paginate = NULL);
                $form->populate($data);
            }
        }
        
        $this->view->backLink = 'product_id=' . $data['product_id'] . '&sub=1&from_date=' . $data['from_date'] . '&to_date=' . $data['to_date'] . '&dur=' . $data['dur'];
        $this->view->controllerName = Zend_Registry::get('controllerName');
        $this->view->form = $form;
        $this->view->sub = $data['sub'];
        $this->view->dur = $data['dur'];
        $this->view->from_date = $data['from_date'];
        $this->view->to_date = $data['to_date'];
        $this->view->product_id = $data['product_id'];
        $this->view->bank_name = $bank_name;
    }
    
    
    public function exportkycupgradereportAction(){
        
        $productCorp = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_OPENLOOP_GPR);
        $productCorpUnicode = $productCorp->product->unicode;
        $productModel = new Products();
        $productOptions = $productModel->getProductInfoByUnicode($productCorpUnicode); 
        
        $bankModel = new Banks();
        $bank_id = $productOptions['bank_id'];
        $bnk_arr = $bankModel->getBankInfo($bank_id);  
        $bank_name = $bnk_arr['name'];
        
        $data['product_id'] = $productOptions['id'];
        $data['from_date'] = $this->_getParam('from_date');
        $data['to_date'] = $this->_getParam('to_date');
        $data['dur'] = $this->_getParam('dur');
        $data['sub'] = $this->_getParam('sub');
        
        $form = new Corp_Kotak_KYCUpgradeReportForm(array('action' => $this->formatURL('/corp_kotak_cardholder/kycupgradereport'),
            'method' => 'POST',
        ));
        

        $cardholdersModel = new Corp_Kotak_Customers();
                 
        if ($data['dur'] != '') {
                $durationArr = Util::getDurationDates($data['dur']);
                $startdate = $durationArr['from'];
                $enddate = $durationArr['to'];

        } else if ($data['to_date'] != '' && $data['from_date'] != '') {
                $startdate =  Util::returnDateFormatted($data['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                $enddate = Util::returnDateFormatted($data['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
        }

        $exportData = $cardholdersModel->exportgetKycUpgradeReportByDate(array(
                'from_date'  => $startdate,
                'to_date'  => $enddate,
                'product_id'  => $data['product_id'],
                'bank_name' => $bank_name,
            ));

        $columns = array(
            'Member Id',
            'Employee Id',
            'Card Number',
            'Name',
            'Name on Card',
            'Gender',
            'Date of Birth',
            'Mobile',
            'Email',
            'CRN',
            'Product Code',
            'Bank Name',
            'Aadhar Number',
            'Creation Date',
            'Customer Address 1',
            'Customer Address 2',
            'Pin Code',
            'State',
            'Upgrade Date',
            'KYC Status',

        );


         $objCSV = new CSV();
         try{
                $resp = $objCSV->export($exportData, $columns, 'kyc_upgrade_report');exit;
         }catch (Exception $e) {
                                 App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                 $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                 $this->_redirect($this->formatURL('/corp_kotak_cardholder/kycupgradereport?product_id=' . $data['product_id'] . '&sub=1&from_date=' . $data['from_date'] . '&to_date=' . $data['to_date'] . '&dur=' . $data['dur'])); 
         }

       }
    
    
     
    public function upgradekycsearchAction() {
        $this->title = 'Update KYC Details';
        $productId = $this->_getParam('product_id');
        $data['product_id'] = $productId;
        $data['searchCriteria'] = $this->_getParam('searchCriteria');
        $data['keyword'] = $this->_getParam('keyword');
//        $data['status'] = STATUS_ACTIVE;
        $data['sub'] = $this->_getParam('sub');
        
        $cardholdersModel = new Corp_Kotak_Customers();
        $page = $this->_getParam('page');
        $form = new Corp_Kotak_CardholderKYCSearchForm(array('action' => $this->formatURL('/corp_kotak_cardholder/upgradekycsearch'),
            'method' => 'POST',
        ));
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
              // if ($data['sub'] != '') 

                    $dataRes = $cardholdersModel->searchcustomerKYC($data);
                    $this->view->paginator = $cardholdersModel->paginateByArray($dataRes, $page, $paginate = NULL);
                    $form->populate($data);
               
            }
        }
        
        $this->view->backLink = 'searchCriteria=' . $data['searchCriteria'] . '&keyword=' . $data['keyword'] . '&sub=1&status='.STATUS_ACTIVE. '&product_id='.$data['product_id'];
        $this->view->controllerName = Zend_Registry::get('controllerName');
        $this->view->form = $form;
        $this->view->formData = $data;
        
        $this->view->sub = $data['sub'];
    }
      public function revertkycAction(){      
        $this->title = 'Revert To NON KYC';
        $user = Zend_Auth::getInstance()->getIdentity();        
        $objCustomerModel = new Corp_Kotak_Customers();
       
        $form = new Corp_Kotak_RevertKYCForm();  
        $formData  = $this->_request->getPost();
        $customerLogModel = new Corp_Kotak_CustomersLog();
        $this->view->form = $form;
        $id = $this->_getParam('id');
        $custDetails = $objCustomerModel->findById($id);
                    
        $isError = FALSE;
        
            if ($this->getRequest()->isPost()) {
                  
                if($form->isValid($this->getRequest()->getPost())){
                 
                   
                  if(!empty($custDetails)){
                        try{  
                 

                  
                
                 // Update kyc details
                  $params = array('customer_type' => TYPE_NONKYC,'date_toggle_kyc'=> new Zend_Db_Expr('NOW()'));
             
                 $objCustomerModel->updateKYC($params,$id);
                 $data = array('product_customer_id' => $id,'by_type' => BY_CHECKER,'by_id' => $user->id, 
                    'status_kyc_old' => $custDetails['customer_type'],'status_kyc_new' => TYPE_NONKYC,'ip' => $objCustomerModel->formatIpAddress(Util::getIP()),'comments' => $formData['comments']
                    );
               
                  
                $customerLogModel->save($data); 
                 
                      $this->_helper->FlashMessenger(
                                    array(
                                        'msg-success' => 'KYC Details upgraded',
                                    )
                            );
                 $this->_redirect($this->formatURL('/corp_kotak_cardholder/upgradekycsearch'));
                 
                 
                    
                
                                
                         }catch (Exception $e ) { 
                                                        $errorExists = true; 
                                                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                                                        $form->populate($formData);
                                                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                                    }                 
         
                }
            else{
                $this->_helper->FlashMessenger( array('msg-error' => 'Cardholder with Member Id does not exist',) ); 
                                                        
            }
            }
            
    }  
           
            $this->view->errorExists = $errorExists;
            $this->view->item = $custDetails;
    }
    
    /*
     * Gets the cardholder's details with staus=pending and change their staus
     */
    public function approvalpendingAction(){
        
        $this->title = 'Pending Cardholder Applications';
        $data['sub'] = $this->_getParam('sub');
        $data['product_id'] = $this->_getParam('product_id');
        $data['en_date'] = Util::returnDateFormatted($this->_getParam('enroll_date'), "d-m-Y", "Y-m-d", "-");
        
        $form = new Corp_Kotak_ApprovalPendingForm(array('action' => $this->formatURL('/corp_kotak_cardholder/approvalpending'),
            'method' => 'POST',
        ));
        $cardholdersModel = new Corp_Kotak_Customers();
        $page = $this->_getParam('page');
        $params = array('product_id' => $this->_getParam('product_id'),'sub' => $this->_getParam('sub'),'enroll_date' => $this->_getParam('enroll_date'),'items_per_page' => 300);
        
        
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                if ($data['sub'] != '') {
                    $this->view->paginator = $cardholdersModel->getpendingcardholders($data,$this->_getPage(),NULL,TRUE);
                    $form->populate($params);
                    $this->view->sub = $data['sub'];
                    $this->view->formData = $params;
                    
                    
                }
            }
        }elseif($data['sub'] != '') {
                    
                    $this->view->paginator = $cardholdersModel->getpendingcardholders($data,$this->_getPage(),NULL,TRUE);
                    $form->populate($params);
                    $this->view->sub = $data['sub'];
                    $this->view->formData = $params;
        }
        
        $this->view->form = $form;
        
    }
    
     public function exportapprovalpendingAction(){
         
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['en_date'] = Util::returnDateFormatted($this->_getParam('enroll_date'), "d-m-Y", "Y-m-d", "-");
        
        $form = new Corp_Kotak_ApprovalPendingForm(array('action' => $this->formatURL('/corp_kotak_cardholder/exportapprovalpending'),
                                              'method' => 'GET',
                                       ));  

        $cardholdersModel = new Corp_Kotak_Customers();
        $exportData = $cardholdersModel->exportpendingcardholdersdetails($qurStr);
                
        $columns = array(
            'Member Id',
            'Name',
            'Card Number',
            'Name on Card',
            'Date of Birth',
            'Mobile',
            'Email',
            'Employer Name',
            'Submission Date -Maker',
            'Submission Date -Checker'
                     
        );

            $objCSV = new CSV();
            try{
                   $resp = $objCSV->export($exportData, $columns, 'pending_cardholders');exit;
            }catch (Exception $e) {
                                    App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                    $this->_redirect($this->formatURL('/corp_kotak_cardholder/exportapprovalpending?product_id='.$qurStr['product_id'])); 
            }
           
          
       }
       
       
    public function bulkapproveAction() {
        
        $this->title = "Bulk Approve";
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        
        //$customerModel = new Corp_Ratnakar_Cardholders();
        $customerModel = new Corp_Kotak_Customers();
        
        $this->view->records = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
        if ($submit != '') {
            try {
                $customerModel->bulkApproval( $formData['reqid']);
            
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Cardholders have been approved',
                        )
                );
                $this->_redirect($this->formatURL('/corp_kotak_cardholder/approvalpending')); 
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
    
    
    public function approveAction(){
        $this->title = 'Approve Cardholder Details';
        $user = Zend_Auth::getInstance()->getIdentity();        
        $session = new Zend_Session_Namespace('App.Operation.Controller'); 
       
        $objCustomerModel = new Corp_Kotak_Customers();
        $id = $this->_getParam('id');
        $custDetails = $objCustomerModel->findById($id);
       
        $errorExists = false;
        // Get our form and validate it
        $form = new Corp_Kotak_ApproveForm();  
        $formData  = $this->_request->getPost();
        $customerLogModel = new Corp_Kotak_CustomersLog();
        $this->view->form = $form;
        $custDetails = Util::toArray($custDetails);
        
            // getting current ip to store in db
            $ip = $objCustomerModel->formatIpAddress(Util::getIP());
            
            if ($this->getRequest()->isPost()) {
                if($form->isValid($this->getRequest()->getPost())){
                        try{  
                   
             $data = array('product_customer_id' => $id,'by_type' => BY_CHECKER,'by_id' => $user->id, 
                    'status_ops_old' => STATUS_PENDING,'status_ops_new' => STATUS_APPROVED,'ip' => $ip,'comments' => $formData['remarks']
                    );
               
                  
                    $customerLogModel->save($data);
                    $params = array('status' => STATUS_APPROVED,'id' => $id);
                    $res = $objCustomerModel->changeStatus($params);
               
                  $this->_helper->FlashMessenger(
                                    array(
                                        'msg-success' => 'Cardholder approved',
                                    )
                            );
                 $this->_redirect($this->formatURL('/corp_kotak_cardholder/approvalpending'));
                 $form->populate($formData);
            
                            
                  }catch (Exception $e ) { 
                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                            $form->populate($formData);
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                  }   
            }
            
    }  
            $form->populate($custDetails);
            $this->view->item = (object)$custDetails;
      
    
    }
    
       /**
     * Allows the Operation user to Reject Agent
     *
     * @access public
     * @return void
     */
    
    public function rejectAction(){
        $this->title = 'Reject Cardholder';
        
        $user = Zend_Auth::getInstance()->getIdentity();        
        $session = new Zend_Session_Namespace('App.Operation.Controller'); 
       
        $objCustomerModel = new Corp_Kotak_Customers();
        $id = $this->_getParam('id');
        $custDetails = $objCustomerModel->findById($id);
       
        $errorExists = false;
        // Get our form and validate it
        $form = new Corp_Kotak_RejectForm();  
        $formData  = $this->_request->getPost();
        $customerLogModel = new Corp_Kotak_CustomersLog();
        $this->view->form = $form;
        $custDetails = Util::toArray($custDetails);
        
            // getting current ip to store in db
            $ip = $objCustomerModel->formatIpAddress(Util::getIP());
            
            if ($this->getRequest()->isPost()) {
                if($form->isValid($this->getRequest()->getPost())){
                        try{  
                   
             $data = array('product_customer_id' => $id,'by_type' => BY_CHECKER,'by_id' => $user->id, 
                    'status_ops_old' => STATUS_PENDING,'status_ops_new' => STATUS_REJECTED,'ip' => $ip,'comments' => $formData['remarks']
                    );
               
                  
                    $customerLogModel->save($data);
                    $params = array('status' => STATUS_REJECTED,'id' => $id);
                    $res = $objCustomerModel->changeStatus($params);
               
                  $this->_helper->FlashMessenger(
                                    array(
                                        'msg-success' => 'Cardholder Rejected',
                                    )
                            );
                 $this->_redirect($this->formatURL('/corp_kotak_cardholder/approvalpending'));
                 $form->populate($formData);
            
                            
                  }catch (Exception $e ) { 
                            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                            $form->populate($formData);
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                  }   
                }
            
            }  
            $form->populate($custDetails);
            $this->view->item = (object)$custDetails;
   }
   
   public function addcardholderdocsAction(){      
        $this->title = 'Upload Documents';
        $user = Zend_Auth::getInstance()->getIdentity();        
        
        $objCustomerModel = new Corp_Kotak_Customers();
        $config = App_DI_Container::get('ConfigObject');
        $currDate = date('Y-m-d');
        $uploadlimit = $config->agent->uploadfile->size;
        $errorExists = false;
        
        $docModel = new Documents();
        $form = new Corp_Kotak_AddCardholderDocsForm();  
        $formData  = $this->_request->getPost();
        $customerLogModel = new Corp_Kotak_CustomersLog();
        $this->view->form = $form;
        $id = $this->_getParam('id');
        $redirect_link = $this->_getParam('viewc');
        $custDetails = $objCustomerModel->findById($id);
        $custDetails = Util::toArray($custDetails);
        
        $isError = FALSE;
        $productCorp = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_OPENLOOP_GPR);
        $productCorpUnicode = $productCorp->product->unicode;
        $productModel = new Products();
        $productId = $productModel->getProductInfoByUnicode($productCorpUnicode);
            if ($this->getRequest()->isPost()) {
                  
                if($form->isValid($this->getRequest()->getPost())){
                 
                   
                  if(!empty($custDetails)){
                        try{  
                 // getting files name 
                $idDocFile = isset($_FILES['id_doc_path']['name'])?$_FILES['id_doc_path']['name']:'';
                $addrDocFile = isset($_FILES['address_doc_path']['name'])?$_FILES['address_doc_path']['name']:'';

                if ( $addrDocFile == '' ) {
                     $this->_helper->FlashMessenger(
                        array(
                                'msg-error' => 'Invalid Address Proof file.',
                           
                              )
                        );
                         $isError = TRUE;
                }
                 if ( $idDocFile == '' ) {
                     $this->_helper->FlashMessenger(
                        array(
                                'msg-error' => 'Invalid Identification Proof file.',
                           
                              )
                        );
                         $isError = TRUE;
                }
                if(trim($idDocFile)=='')
                    unset($_FILES['id_doc_path']);
                if(trim($addrDocFile)=='')
                    unset($_FILES['address_doc_path']);
                
                  
                  if ( $addrDocFile != '' || $idDocFile !='') {
                      
                        /*** renaming upload files as same name files can also be upload successfully ***/
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
          
           
                 
                   
            //*********************upload files********************//
            //
              if ($addrDocFile != '' || $idDocFile !='') {    
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
                         $dataID = array('doc_cardholder_id' => $id,'doc_product_id'=> $productId->id, 'by_ops_id' => $user->id, 'ip' => $objCustomerModel->formatIpAddress(Util::getIP()),
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
                    $dataAdd = array('doc_cardholder_id' => $id,'doc_product_id'=> $productId->id , 'by_ops_id' => $user->id, 'ip' => $objCustomerModel->formatIpAddress(Util::getIP()),
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
                
                
                }

            //*********************END upload files********************//

                 
                if($redirect_link == 'pendingkyc'){
                     $this->_redirect($this->formatURL('/corp_kotak_cardholder/pendingkyc'));
                }else{
                     $this->_redirect($this->formatURL('/corp_kotak_cardholder/searchcardholders'));
                }
                
                
                 
                 
                 
                    }
                
                                
                         }catch (Exception $e ) { 
                                                        $errorExists = true; 
                                                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                                                        $form->populate($formData);
                                                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                                    }                 
         
                }
            else{
                $this->_helper->FlashMessenger( array('msg-error' => 'Cardholder with Member Id does not exist',) ); 
                                                        
            }
            }
            
    }  
           
            $form->populate($custDetails);
            $this->view->errorExists = $errorExists;
            $this->view->formData = $custDetails;
      
    }
    
    
    public function crnstatusAction() {
        
        $this->title = 'CRN Status Report';
        
        // Get our form and validate it
        $form = new Corp_Kotak_CRNStatusForm(array('action' => $this->formatURL('/corp_kotak_cardholder/crnstatus'),
            'method' => 'POST',
        ));
        $formData = $this->_request->getPost(); 
        $page = $this->_getParam('page');
        $product_id = $this->_getParam('product_id');
        $crn = $this->_getParam('crn');
        $status = $this->_getParam('crn_status');
        $card_pack_id = $this->_getParam('card_pack_id');
        $file = $this->_getParam('file');
        $sub = $this->_getParam('sub');

        $session = new Zend_Session_Namespace('App.Operation.Controller');
        $session->items_per_page=10; 
           
        if($sub == 1) {
                $objCRNMaster = new CRNMaster();
                
                $sql = $objCRNMaster->searchCRNStatus(array(
                'product_id' => $product_id,
                'status' => $status,
                'card_number' => $crn,
                'card_pack_id' => $card_pack_id,
                'file' => $file,
                ),'SQL');

                
                $this->view->paginator = $objCRNMaster->paginateByArray($sql, $page, $paginate = NULL);
                $form->getElement('product_id')->setValue($product_id);
                $form->getElement('crn')->setValue($crn);
                $form->getElement('crn_status')->setValue($status);
                $form->getElement('card_pack_id')->setValue($card_pack_id);
                $form->getElement('file')->setValue($file);
                $form->getElement('sub')->setValue($sub);
                $this->view->sub = $sub;
                $this->view->title = $this->title;
                
        }
        $this->view->form = $form;
        $this->view->formData = $formData;
    }
    
    /*
     *  Exports the crn records for the selected product 
     */
    public function exportcrnstatusAction()
    {
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['crn'] = $this->_getParam('crn');
        $qurStr['status'] = $this->_getParam('crn_status');
        $qurStr['card_pack_id'] = $this->_getParam('card_pack_id');
        $qurStr['file'] = $this->_getParam('file');
        
        // Get our form and validate it
        $form = new Corp_Kotak_CRNStatusForm(array('action' => $this->formatURL('/corp_kotak_cardholder/crnstatus'),
            'method' => 'POST',
        ));

        if ($form->isValid($qurStr)) {            

            $cardholdersModel = new Corp_Kotak_Customers();
            $exportData = $cardholdersModel->exportCRNStatus($qurStr);

            $columns = array(
                'Card Number',
                'Card Pack Id',
                'Member Id',
                'Status',
                'File Name',
                'Uploaded On'
            );

            $objCSV = new CSV();
            try {
                $resp = $objCSV->export($exportData, $columns, 'download_crnstatus_reports');
                exit;
            } catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                $this->_redirect($this->formatURL('/corp_kotak_cardholder/crnstatus?product_id=' . $qurStr['product_id'] . '&crn=' . $qurStr['crn']. '&crn_status=' . $qurStr['status']. '&card_pack_id=' . $qurStr['card_pack_id']. '&file=' . $qurStr['file']));
            }
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Invalid data'));
            $this->_redirect($this->formatURL('/corp_kotak_cardholder/crnstatus?product_id=' . $qurStr['product_id'] . '&crn=' . $qurStr['crn']. '&crn_status=' . $qurStr['status']. '&card_pack_id=' . $qurStr['card_pack_id']. '&file=' . $qurStr['file']));
        }
    }
}
