<?php

/**
 * HIC Default entry point
 *
 * @author Vikram
 */
class Corp_Ratnakar_CardholderController extends App_Operation_Controller {

    //put your code here


    public function init() {
        parent::init();
    }

    public function indexAction() {
    }

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
        $bankObject   = new Banks();
        
//        $cardholdersModel->ratCorpECSRegn();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->receive();
                $name = $upload->getFileName('doc_path');

                $batchName = $upload->getFileName('doc_path', $path = FALSE) ; 
                $checkFile = $cardholdersModel->checkBatchFilename($batchName, $formData['product_id']);
                
                //Add Validators for uploaded file's extesion , mime type and size
               $upload->addValidator('Extension', false, array('txt'));
               
               //check file extension...
                $upload->addValidator('Extension', false, array(FILE_TYPE_TXT, 'case' => false));
                
                $file_ext = Util::getFileExtension($batchName);
                if(strtolower($file_ext) != strtolower(FILE_TYPE_TXT))
                {
                   $this->_helper->FlashMessenger( array('msg-error' => 'Invalid file uploaded. Allowed Format is txt only.',));
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
                    $fp = fopen($name, 'r');
                    while (!feof($fp)) {
                        $line = fgets($fp);
                        if (!empty($line)) {
                            $delimiter = CORP_CARDHOLDER_UPLOAD_DELIMITER;
                            $dataArr = str_getcsv($line, $delimiter);
                            $dataArr['product_id'] = $formData['product_id'];
                            
                            $bankInfo = $bankObject->getBankidByProductid($formData['product_id']);
                            $dataArr['bank_id'] = $bankInfo['bank_id'];
                            
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
                                $this->view->paginator = $cardholdersModel->showPendingCardholderDetails($batchName,$formData['product_id'], $page, $paginate = NULL,$force = TRUE);
                            } else {
    //                            $this->_helper->FlashMessenger(array('msg-error' => 'Data format is not correct'));
                            }
                        }
                    }


                    $this->view->records = TRUE;
                    $this->view->batch_name = $batchName;

                    fclose($fp);
                }
            }
        }

        if ($submit != '') {


            try {
		$channel = CHANNEL_OPS;
                $cardholdersModel->bulkAddCardholder($formData['reqid'], $formData['batch'],STATUS_ECS_PENDING,$channel);
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
    
    public function batchstatusAction() {
        $this->title = 'Cardholder Batch Status';

        $data['product_id'] = $this->_getParam('product_id');
        $data['batch_name'] = $this->_getParam('batch_name');
        $data['start_date'] = $this->_getParam('start_date');
        $data['end_date'] = $this->_getParam('end_date');
        $data['sub'] = $this->_getParam('sub');
        $cardholdersModel = new Corp_Ratnakar_Cardholders();
        $page = $this->_getParam('page');
        $form = new Corp_Ratnakar_CardholderBatchForm(array('action' => $this->formatURL('/corp_ratnakar_cardholder/batchstatus'),
            'method' => 'POST',
        ));
        if ($data['sub'] != '') {
            if ($this->getRequest()->isPost()) {
                if ($form->isValid($this->getRequest()->getPost())) {
                   
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
                        'product_id'  => $data['product_id'],
                        'batch_name'  => $data['batch_name'],
                        'start_date'  => $startdate,
                        'end_date'  => $enddate,
                    ));
                 
                $form->getElement('batch')->setValue($data['batch_name']);
                $this->view->paginator = $cardholdersModel->paginateByArray($batchdetails, $page, $paginate = NULL);
                $form->populate($data);
            }
        }
        
//        $this->view->backLink = 'searchCriteria=' . $data['searchCriteria'] . '&keyword=' . $data['keyword'] . '&sub=1';
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
        
        $qurStr['batch_name'] = $this->_getParam('batch_name');
        $qurStr['product_id'] = $this->_getParam('product_id');

        $data['start_date'] = $this->_getParam('start_date');
        $data['end_date'] = $this->_getParam('end_date');
        
        $form = new Corp_Ratnakar_CardholderBatchForm(array('action' => $this->formatURL('/corp_ratnakar_cardholder/batchstatus'),
            'method' => 'POST',
        ));
        
                 $cardholdersModel = new Corp_Ratnakar_Cardholders();
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
                 $exportData = $cardholdersModel->exportgetBatchDetailsByDate(array(
                        'batch_name'  => $qurStr['batch_name'],
                        'product_id'  => $qurStr['product_id'],
                        'start_date'  => $startdate,
                        'end_date'  => $enddate,
                    ));
                 
                 $columns = array(
                    'Medi Assist Id',
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
                                         $this->_redirect($this->formatURL('/corp_ratnakar_cardload/walletstatus?batch_name='.$qurStr['batch_name'].'&sub=1')); 
                                       }
}

    public function searchcardholderAction() {
        $this->title = 'Search Cardholders';


        $data['searchCriteria'] = $this->_getParam('searchCriteria');
        $data['keyword'] = $this->_getParam('keyword');
        $data['sub'] = $this->_getParam('sub');
        $data['product_id'] = $this->_getParam('product_id');
        $cardholdersModel = new Corp_Ratnakar_Cardholders();
        $page = $this->_getParam('page');
        $form = new Corp_Ratnakar_CardholderSearchForm(array('action' => $this->formatURL('/corp_ratnakar_cardholder/searchcardholder'),
            'method' => 'POST',
        ));
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                if ($data['sub'] != '') {

                    $dataRes = $cardholdersModel->searchCardholder($data);

                    $this->view->paginator = $cardholdersModel->paginateByArray($dataRes, $page, $paginate = NULL);
                    $form->populate($data);
                }
            }
        }

        $this->view->backLink = 'searchCriteria=' . $data['searchCriteria'] . '&keyword=' . $data['keyword'] . '&sub=1';
        $this->view->controllerName = Zend_Registry::get('controllerName');
        $this->view->form = $form;
        $this->view->sub = $data['sub'];
    }

    public function viewAction() {

        $this->title = 'Cardholder Details';
        $cardholdersModel = new Corp_Ratnakar_Cardholders();
        $cardholderStatus = Zend_Registry::get("CORP_CARDHOLDER_STATUS");
        $documentDetails = array();
        $id = $this->_getParam('id');
        $viewc = $this->_getParam('viewc');
        if (!is_numeric($id)) {
            $this->_helper->FlashMessenger(
                    array(
                        'error' => 'The user id you provided is invalid',
                    )
            );

            $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/index/'));
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

            $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/index/'));
        }
        
        
        if($viewc == 'approvalpending'){
            $product_id = $this->_getParam('product_id');
            $sub = $this->_getParam('sub');
            $backLink = 'product_id=' . $product_id . '&sub=1';
            $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/corp_ratnakar_cardholder/approvalpending?' . $backLink;
        }elseif($viewc == 'pendingkyc'){
            $product_id = $this->_getParam('product_id');
            $sub = $this->_getParam('sub');
            $backLink = 'product_id=' . $product_id . '&sub=1';
            $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/corp_ratnakar_cardholder/pendingkyc?' . $backLink;
        
        }elseif($viewc == 'batchstatus'){
            $product_id = $this->_getParam('product_id');
            $batch_name = $this->_getParam('batch_name');
            $start_date = $this->_getParam('start_date');
            $end_date = $this->_getParam('end_date');
            $sub = $this->_getParam('sub');
            $backLink = 'batch_name=' . $batch_name . '&start_date=' . $start_date . '&end_date=' . $end_date . '&sub=1&product_id='.$product_id;
            $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/corp_ratnakar_cardholder/batchstatus?' . $backLink;
        }else{
            $search = $this->_getParam('searchCriteria');
            $keyword = $this->_getParam('keyword');
            $sub = $this->_getParam('sub');
            $backLink = 'searchCriteria=' . $search . '&keyword=' . $keyword . '&sub=1';
            $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/corp_ratnakar_cardholder/searchcardholder?' . $backLink;
        }
        
        //Select Cardholder's Purse
        $this->view->cardholderPurses = array();
        if (isset($row->rat_customer_id) && $row->rat_customer_id > 0) {
            $cardHolder = new Corp_Ratnakar_Cardholders();
            $this->view->cardholderPurses = $cardHolder->getRatCardholderPurses($row->rat_customer_id);
        }
        
        //$this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/corp_ratnakar_cardholder/searchcardholder?' . $backLink;
        $this->view->item = $row;
    }

    public function editAction() {

        $this->title = 'Edit Cardholder';
        //$session = new Zend_Session_Namespace('App.Agent.Controller');
        $formData = $this->_request->getPost();
        $searchCriteria = $this->_getParam('searchCriteria');
        $keyword = $this->_getParam('keyword');
        $sub = $this->_getParam('sub');
        $id = $this->_getParam('id');
        $user = Zend_Auth::getInstance()->getIdentity();
        $queryString = 'searchCriteria=' . $searchCriteria . '&keyword=' . $keyword . '&sub=' . $sub;
        $session = new Zend_Session_Namespace('App.Agent.Controller');
        /* $minAge = $config->cardholder->age->min;
          $maxAge = $config->cardholder->age->max;
          $currDate = date('Y-m-d'); */

        $request = $this->getRequest();
        $objValidation = new Validator();
        $objCardholders = new Corp_Ratnakar_Cardholders();
        $objMobile = new Mobile();
        $objEmail = new Email();
        $objCRN = new CRN();
        //$products  = new Products();
        $errorExists = false;


        // Get our form and validate it
        $form = new Corp_Ratnakar_EditCardholderForm(array(
            'action' => $this->formatURL('/corp_ratnakar_cardholder/edit'),
            'method' => 'post',
            'name' => 'frmAdd',
            'id' => 'frmAdd'
        ));
        $this->view->form = $form;

        $dateOfBirth = isset($formData['date_of_birth']) ? $formData['date_of_birth'] : '';
        $formData['date_of_birth'] = Util::returnDateFormatted($dateOfBirth, "d-m-Y", "Y-m-d", "-");
        $btnEdit = isset($formData['btn_edit']) ? $formData['btn_edit'] : '';
        $row = $objCardholders->getCardholderInfo(array('cardholder_id' => $id));
        if (empty($row)) {
            $this->_helper->FlashMessenger(
                    array(
                        'msg-warning' => sprintf('We cannot find Cardholder with id %s', $id),
                    )
            );
//                $this->_redirect('/agentsummary/');
            $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/searchcardholder?' . $queryString));
        }

        $rowArr = $row->toArray();

        $dataOld = array(
            'afn' => $rowArr['afn'],
            'medi_assist_id' => $rowArr['medi_assist_id'],
            'employee_id' => $rowArr['employee_id'],
            'mobile_number' => $rowArr['mobile'],
            'first_name' => $rowArr['first_name'],
            'middle_name' => $rowArr['middle_name'],
            'last_name' => $rowArr['last_name'],
            'gender' => $rowArr['gender'],
            'date_of_birth' => $rowArr['date_of_birth'],
            'pan' => $rowArr['pan'],
            'aadhaar_no' => $rowArr['aadhaar_no'],
            'email' => $rowArr['email'],
            'employer_name' => $rowArr['employer_name'],
            'batch_name' => $rowArr['batch_name'],
            'corporate_id' => $rowArr['corporate_id'],
            'afn_old' => $rowArr['afn'],
            'id' => $rowArr['id'],
        );

        $populateData = array('pan_old' => $rowArr['pan'],
            'aadhaar_no_old' => $rowArr['aadhaar_no'],
            'email_old' => $rowArr['email'],
            'searchCriteria' => $searchCriteria,
            'keyword' => $keyword,
            'sub' => $sub,
        );
        $populateData = array_merge($dataOld, $populateData);
        $populateData['date_of_birth'] = Util::returnDateFormatted($populateData['date_of_birth'], "Y-m-d", "d-m-Y", "-");



        // adding details in db
        if ($btnEdit) {

            if ($form->isValid($this->getRequest()->getPost())) {

                $aadhaarNoOld = isset($formData['aadhaar_no_old']) ? trim($formData['aadhaar_no_old']) : '';
                $panOld = isset($formData['pan_old']) ? trim($formData['pan_old']) : '';
                $emailOld = isset($formData['email_old']) ? trim($formData['email_old']) : '';
                $afnOld = isset($formData['afn_old']) ? trim($formData['afn_old']) : '';
                $aadhaarNo = isset($formData['aadhaar_no']) ? trim($formData['aadhaar_no']) : '';
                $pan = isset($formData['pan']) ? trim($formData['pan']) : '';
                $email = isset($formData['email']) ? trim($formData['email']) : '';
                $afn = isset($formData['afn']) ? trim($formData['afn']) : '';

                /*                 * * checking for validation and duplication ** */

                try {

                    // afn check
                    if ($afnOld != $afn)
                        $afnCheck = $objCardholders->checkAFNDuplication($afn);

                    // pan card number check
                    if ($panOld != $pan) {
                        if ($pan == '') {
                            $isPanValid = true;
                        } else {
                            $isPanValid = $objValidation->validatePAN($pan);
                            if ($isPanValid)
                                $isPanValid = $objCardholders->checkPANDuplication($pan);
                        }
                    }


                    // aadhaar card number check
                    if ($aadhaarNoOld != $aadhaarNo) {
                        if ($aadhaarNo == '') {
                            $isAadhaarValid = true;
                        } else {
                            $isAadhaarValid = $objValidation->validateUID($aadhaarNo);
                            if ($isAadhaarValid)
                                $isAadhaarValid = $objCardholders->checkAadhaarDuplication($aadhaarNo);
                        }
                    }

                    // email check
                    if ($emailOld != $email) {
                        $emailCheck = $objEmail->checkCorpCardholderEmailDuplicate($email);
                    }
                } catch (Exception $e) {
                    $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-");
                    $errorExists = true;
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                    $form->populate($formData);
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                }

                /*                 * * checking for validation and duplication over here ** */


                /*                 * * adding cardholder details in db ** */
                if (!$errorExists) {
                    try {

                        $dataNew = array(
                            'afn' => $formData['afn'],
                            'medi_assist_id' => $formData['medi_assist_id'],
                            'employee_id' => $formData['employee_id'],
                            'employer_name' => $formData['employer_name'],
                            'first_name' => $formData['first_name'],
                            'middle_name' => $formData['middle_name'],
                            'last_name' => $formData['last_name'],
                            'aadhaar_no' => $formData['aadhaar_no'],
                            'pan' => $formData['pan'],
                            'mobile' => $formData['mobile_number'],
                            'email' => $formData['email'],
                            'gender' => $formData['gender'],
                            'date_of_birth' => $formData['date_of_birth'],
                            'corporate_id' => $formData['corporate_id'],
                            'batch_name' => $formData['batch_name'],
                            'by_ops_id' => $user->id,
                        );

                        $isCardholderUpdated = $objCardholders->updateCardholderById($dataOld, $dataNew, $user->id, '', USER_TYPE_OPS);
                    } catch (Exception $e) {
                        $formData['date_of_birth'] = Util::returnDateFormatted($formData['date_of_birth'], "Y-m-d", "d-m-Y", "-");
                        $errorExists = true;
                        $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                        $form->populate($formData);
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    }
                }
                /*                 * * adding cardholder details in db over here ** */


                if (!$errorExists && $isCardholderUpdated) {
                    $this->_helper->FlashMessenger(
                            array(
                                'msg-success' => 'Cardholder edited successfully',
                            )
                    );

                    $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/searchcardholder?' . $queryString));
                }
            } //  if form does not validate successfully 
        }

        if (!$errorExists && !$btnEdit) {
            $form->populate($populateData);
        }
    }

    
    //Add Docs for cardholder
      public function addcardholderdocsAction(){
          $objCardholders = new Corp_Ratnakar_Cardholders();
          $request = $this->getRequest(); 
          $this->title = 'Add Cardholder Documents';
          $id = $this->_getParam('id');
          $config = App_DI_Container::get('ConfigObject');
          $docModel = new Documents();
          $customerLogModel = new Corp_Ratnakar_CustomersLog();
          $form = new Corp_Ratnakar_CardholderDocsForm();
          $errMsg = '';
          $uploadlimit = $config->operation->uploadfile->size;
          $row = $objCardholders->findById($id);
          $custDetails = Util::toArray($row);
          $this->view->item = $row;
          $isError = FALSE;
          $user = Zend_Auth::getInstance()->getIdentity();
          $search = $this->_getParam('searchCriteria');
          $keyword = $this->_getParam('keyword');
          $product_id = $this->_getParam('product_id');
          $sub = $this->_getParam('sub');
          $backLink = '&searchCriteria=' . $search . '&keyword=' . $keyword . '&sub=1';
          
          $valid_product_const = array(PRODUCT_CONST_RAT_MEDI,PRODUCT_CONST_RAT_GPR,PRODUCT_CONST_RAT_SUR);
          $productModel = new Products();
          $valid_product_ids = $productModel->getProductIDbyConstArr($valid_product_const,$return_arr=TRUE);
        
          if(!in_array($product_id, $valid_product_ids))
          {
              $form->removeElement('is_check');
          }
          
          
          if ($this->getRequest()->isPost()) {
              
              
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
                 $formData  = $this->_request->getPost();
                 
                 // getting files name 
                $idDocFile = isset($_FILES['id_doc_path']['name'])?$_FILES['id_doc_path']['name']:'';
                $addrDocFile = isset($_FILES['address_doc_path']['name'])?$_FILES['address_doc_path']['name']:'';

                if(trim($idDocFile)=='')
                    unset($_FILES['id_doc_path']);
                if(trim($addrDocFile)=='')
                    unset($_FILES['address_doc_path']);
                    
                    
                 /* Uploading Document File on Server */  
                 if ($errMsg == '' && ($idDocFile!='' || $addrDocFile!='')) {
                   
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
//                   $uploadInfo = $upload->getFileInfo();
                       
                  // Add Validators for uploaded file's extesion , mime type and size
                  $upload->addValidator('Extension', false, array('jpg','jpeg','pdf','case' => false))
                      ->addValidator('FilesSize',false,array('min' => '5kB', 'max' => $uploadlimit));
                    //->addValidator('MimeType', false, array('application/octet-stream','image/gif', 'image/jpg','image/bmp','application/pdf'));
//                echo "<pre>";print_r($upload->getFileInfo());
            
                    $upload->setDestination(UPLOAD_PATH_RAT_CORP_DOC.'/');
                  
                try {
                    //All validations correct then upload file
                    if($upload->isValid()){
                        // upload received file(s)
                        $upload->receive();
                        
                 $uploadedData = $form->getValues();
                
//                 print_r($uploadedData); exit;
                 
                
                
                 $id_doc_path = $this->_getParam('id_doc_path');
                 $add_doc_path = $this->_getParam('address_doc_path'); 
                 
               
                

                $nameId = $upload->getFileName('id_doc_path');
                $nameAdd = $upload->getFileName('address_doc_path');
                //echo '<pre>';
                  //  print_r($upload);

                
                
                 /*** identification doc upload case ***/
                    if(!empty($nameId)){

                        $destId = $upload->getDestination('id_doc_path');
                        $sizeId = $upload->getFileSize('id_doc_path');

                        // get the file name and extension
                        $extId = explode(".", $nameId);

                        $checkDocId = $docModel->checkCustomerDoc($row->id_proof_doc_id);
                        if (!empty($checkDocId)) {
                            $docModel->updateDocs($row->id_proof_doc_id);
                        }
                        
                        
                        // add document details along with agent id to DB
                         $dataID = array('doc_rat_customer_id' => $row->rat_customer_id ,'doc_rat_corp_id' => $id, 'by_ops_id' => $user->id, 'ip' => $objCardholders->formatIpAddress(Util::getIP()),
                         'doc_type' => $row->id_proof_type, 'file_name' => '', 'file_type' => $extId['1'], 'status' => STATUS_ACTIVE);

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
                    /*** identification doc upload case over ***/


                    /*** address doc upload case ***/
                    if(!empty($nameAdd)){

                    $destAdd = $upload->getDestination('address_doc_path');
                    $sizeAdd = $upload->getFileSize('address_doc_path');

                    # Returns the mimetype for the 'doc_path' form element
                    //$mimeType = $upload->getMimeType($doc_path);
                    // get the file name and extension
                       $extAdd = explode(".", $nameAdd);

                        $checkDocAdd = $docModel->checkCustomerDoc($row->address_proof_doc_id);
                        if ($checkDocAdd > 0) {
                             $docModel->updateDocs($row->address_proof_doc_id);
                        }

                        
                 

                    // add document details along with agent id to DB
                    $dataAdd = array('doc_rat_customer_id' => $row->rat_customer_id ,'doc_rat_corp_id' => $id, 'by_ops_id' => $user->id, 'ip' => $objCardholders->formatIpAddress(Util::getIP()),
                         'doc_type' => $row->address_proof_type, 'file_name' => '', 'file_type' => $extId['1'], 'status' => STATUS_ACTIVE);


                    $resAdd = $docModel->saveAgentDocs($dataAdd);
                      if ($nameId == $nameAdd) { // if names are same, update same id
                        $renameFileAdd = $resId. '.' . $extAdd['1'];
                    } else {
                        $renameFileAdd = $resAdd . '.' . $extAdd['1'];
                    }
                      // rename the file and update the record
                    $dataArrAdd = array('file_name' => $renameFileAdd);
                    $updateAdd = $docModel->renameDocs($resAdd, $dataArrAdd);
                     

                    // Rename uploaded file using Zend Framework
                    $fullFilePathAdd = $destAdd . '/' . $renameFileAdd;
                    //echo 'ID'.$fullFilePathId;
                    //echo 'ADDRESS'.$fullFilePathAdd;

                    $filterFileRenameAdd = new Zend_Filter_File_Rename(array('target' => $fullFilePathAdd, 'overwrite' => true));
                    $filterFileRenameAdd->filter($nameAdd);
                    $dataArrId = array('address_proof_doc_id' => $resAdd);
                    $objCardholders->update($dataArrId ,"id = $id");
                } 
               /*** address doc upload case over ***/
                
                $updateArr = array('id_proof_type' => $formData['id_proof_type'],'id_proof_number' => $formData['id_proof_number'], 
                               'address_proof_type' => $formData['address_proof_type'],'address_proof_number' => $formData['address_proof_number']
                               );
                $whereupdate = "id = ".$id;
                $objCardholders->update($updateArr , $whereupdate);
                
                /// conditional
                if($formData['is_check'] == 'yes')
                {
                //product id in medi and gpr then
                $dataKyc = array('customer_type' => TYPE_KYC);
                $whereKyc = "id = ".$id." AND id_proof_doc_id > 0 AND address_proof_doc_id > 0";
                $objCardholders->update($dataKyc , $whereKyc);
                
                
                $logdata = array('product_customer_id' => $id,'by_type' => BY_CHECKER,'by_id' => $user->id, 
                               'status_kyc_old' => $custDetails['customer_type'],'status_kyc_new' => TYPE_KYC,'ip' => $objCardholders->formatIpAddress(Util::getIP()),'comments' => $formData['comments']
                               );
                $customerLogModel->save($logdata); 
                }

                //**//
                            
                
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
                
                } catch (Zend_File_Transfer_Exception $e) {
                  App_Logger::log($e->getMessage(), Zend_Log::ERR);
                  $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => $e->getMessage(),
                          )
                        );
                    $isError = TRUE;
                     
                }
             
              }
              
                // end of upload files
              
                    /******** updating form details in db if no error is there *******/
                if(!$isError && $errMsg==''){ 
                     
                  
                    try {  
                        
                         $this->_helper->FlashMessenger(
                                                           array(
                                                                   'msg-success' => 'Customer documents sucessfully updated',
                                                                )
                                                          );
                            $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/view?viewc=pendingkyc&id='.$id.$backLink));
                        }
                        catch(Exception $e){
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            $updateMsg = $e->getMessage();
                            $this->_helper->FlashMessenger(
                            array(
                                  'msg-error' => $updateMsg,
                                 )
                            );
                       }
                } 
                     
                 
                if($errMsg!='') {
                    $this->_helper->FlashMessenger(
                            array(
                                'msg-error' => $errMsg,
                            )
                    );
                }
         
            }                                
            
            
           
            $this->view->error = TRUE;
        } 
      
        
        $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/corp_ratnakar_cardholder/searchcardholder?' . $backLink;   
        
        $this->view->form = $form;
        $this->view->id = $id;
       
        $form->populate($custDetails);
     }

     
     public function pendingkycAction() {
        $this->title = 'Pending KYC Docs';

        $cardholdersModel = new Corp_Ratnakar_Cardholders();
        $form = new Corp_Ratnakar_PendingKYCForm();
        $page = $this->_getParam('page');
        
//        echo "<pre>";print_r($paginator);exit;
         if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                 $formData = $this->_request->getPost();
        $params = array('from_date'=> Util::returnDateFormatted($formData['from_date'], "d-m-Y", "Y-m-d", "-"),'to_date'=> Util::returnDateFormatted($formData['to_date'], "d-m-Y", "Y-m-d", "-"),'product_id'=> $formData['product_id']);
        $this->view->paginator = $cardholdersModel->getPendingKyc($page,$params);
        $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/corp_ratnakar_cardholder/pendingkyc';
            }
         }
         $this->view->form = $form;
    }
     

    public function uploadcrnAction() {
        $this->title = "Bulk CRN Upload";
        $page = $this->_getParam('page');
        $form = new Corp_Ratnakar_UploadcrnForm();
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $productId = isset($formData['product_id']) ? $formData['product_id'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $batch = $this->getRequest()->getParam('batch');
        $this->view->incorrectData = FALSE;
        $cardholdersModel = new CRNMaster();
        $this->view->records = FALSE;
        //$user = Zend_Auth::getInstance()->getIdentity();
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
                   $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/uploadcrn/'));
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
                                $data['product_id'] = $productId;
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
                        $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/uploadcrn/'));
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
        $form = new ManualAdjustmentForm(); 
        $form->getElement('doc_path')->setLabel('Manual Adjustment File');
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $cardholdersModel = new BatchAdjustment();
        $this->view->records = FALSE;
        $batch = $this->getRequest()->getParam('batch');
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
                //echo $name;exit;
                
                //check file extension...
                $upload->addValidator('Extension', false, array(FILE_TYPE_CSV, 'case' => false));
                
                $file_ext = Util::getFileExtension($name);
                if(strtolower($file_ext) != strtolower(FILE_TYPE_CSV))
                {
                   $this->_helper->FlashMessenger( array('msg-error' => 'Invalid file uploaded. Allowed Format is csv only.',) );
                   $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/uploadma/'));
                }
               
                
                $batchName = $upload->getFileName('doc_path', $path = FALSE);// . '_' . $user->id;
                if(!$cardholdersModel->checkDuplicateFile($batchName,$formData['product_id']))
                {
                $fp = fopen($name, 'r');
                $consolidateArr = array();
                while (!feof($fp)) {
                    $line = fgets($fp);
                    if (!empty($line)) {
                        $delimiter = $cardholdersModel::FILE_SEPRATOR;
                        $dataArr = str_getcsv($line, $delimiter);
                        $arrLength = Util::getArrayLength($dataArr);
                        if (!empty($dataArr)) {
                            if ($arrLength != CORP_RBL_MANUAL_ADJUSTMENT_COLUMNS){
                                $this->_helper->FlashMessenger( array('msg-error' => 'Invalid file uploaded. Column count not matched.',) );
                                $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/uploadma/'));
                            }
                            try {
                                // direct insert into rat_corp_cardholders
                                $data['card_number'] = $dataArr[0];
                                $data['wallet_code'] = $dataArr[1];
                                $data['mode'] = $dataArr[2];
                                $data['rrn'] = $dataArr[4];
                                $data['narration'] = $dataArr[5];
                                $data['file'] = $batchName;
                                $data['product_id'] = $formData['product_id'];
				$data['callecs'] = $formData['callecs'];
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
                        $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/uploadma/'));
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
        //$data['keyword'] = $this->_getParam('keyword');
        $data['sub'] = $this->_getParam('sub');
        $data['start_date'] = $this->_getParam('start_date');
        $data['end_date'] = $this->_getParam('end_date');
        $data['product_id'] = $this->_getParam('product_id');

        $cardholdersModel = new BatchAdjustment();
        $page = $this->_getParam('page');
        $agentModel = new Agents();
        $form = new Corp_Ratnakar_MASearchForm(array('action' => $this->formatURL('/corp_ratnakar_cardholder/searchma'),
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



        $this->view->backLink = 'searchCriteria=' . $data['searchCriteria'] . '&keyword=' . $data['keyword'] . '&sub=1';
        $this->view->controllerName = Zend_Registry::get('controllerName');
        $this->view->form = $form;
    }


    public function cardholderactivationreqAction() {
        $this->title = "Bulk Upload of Cardholders- Activation Required";
        $page = $this->_getParam('page');
        $form = new Corp_Ratnakar_CardholderactrequploadForm();
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $cardholdersModel = new Corp_Ratnakar_Cardholders();
        $this->view->records = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
        $bankObject   = new Banks();
        
//        $cardholdersModel->ratCorpECSRegn();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->receive();
                $name = $upload->getFileName('doc_path');

                $batchName = $upload->getFileName('doc_path', $path = FALSE) ; 
                $checkFile = $cardholdersModel->checkBatchFilename($batchName, $formData['product_id']);
                //Add Validators for uploaded file's extesion , mime type and size
               $upload->addValidator('Extension', false, array('txt'));
               
               //check file extension...
                $upload->addValidator('Extension', false, array(FILE_TYPE_TXT, 'case' => false));
                
                $file_ext = Util::getFileExtension($batchName);
                if(strtolower($file_ext) != strtolower(FILE_TYPE_TXT))
                {
                   $this->_helper->FlashMessenger( array('msg-error' => 'Invalid file uploaded. Allowed Format is txt only.',));
                   $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/cardholderactivationreq/'));
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
                            
                            $bankInfo = $bankObject->getBankidByProductid($formData['product_id']);
                            $dataArr['bank_id'] = $bankInfo['bank_id'];
                            
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
                                $this->view->paginator = $cardholdersModel->showPendingCardholderDetails($batchName,$dataArr['product_id'], $page, $paginate = NULL,$force = TRUE);
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
		$channel = CHANNEL_OPS;
                $cardholdersModel->bulkAddCardholder($formData['reqid'], $formData['batch'],STATUS_ACTIVATION_PENDING,$channel);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Cardholder details have been updated in our records',
                        )
                );
                $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/cardholderactivationreq/'));
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
    
    
    public function approvalpendingAction() {
        
        $this->title = 'Pending Cardholder Applications';
        $data['sub'] = $this->_getParam('sub');
        $data['product_id'] = $this->_getParam('product_id');
        
        $form = new Corp_Ratnakar_ApprovalPendingForm(array('action' => $this->formatURL('/corp_ratnakar_cardholder/approvalpending'),
            'method' => 'POST',
        ));
        $cardholdersModel = new Corp_Ratnakar_Cardholders();
        $page = $this->_getParam('page');
        $params = array('product_id' => $this->_getParam('product_id'),'sub' => $this->_getParam('sub'),'items_per_page' => 300);
        
        
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                if ($data['sub'] != '') {
                    $this->view->paginator = $cardholdersModel->getpendingcardholders($data,$this->_getPage());
                    $form->populate($params);
                    $this->view->sub = $data['sub'];
                    $this->view->formData = $params;
                    
                    
                }
            }
        }elseif($data['sub'] != '') {
                    
                    $this->view->paginator = $cardholdersModel->getpendingcardholders($data,$this->_getPage());
                    $form->populate($params);
                    $this->view->sub = $data['sub'];
                    $this->view->formData = $params;
                    
            
        }
        
        
                   
        
        
        /*
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                if ($data['sub'] != '') {
                
                    $this->view->paginator = $cardholdersModel->getpendingcardholders($data,$this->_getPage());
                }
            }
         }
         elseif($data['sub'] != '') {
                    $this->view->paginator = $cardholdersModel->getpendingcardholders($data,$this->_getPage());
         }
        
         $form->populate($data);
         $this->view->form = $form;
         $this->view->sub = $data['sub'];
         $this->view->formData = $params;
        */
        
         
         
        
        
        
        $this->view->form = $form;
        
    }
    
     public function exportapprovalpendingAction(){
         
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $qurStr['product_id'] = $this->_getParam('product_id');
        
        $form = new Corp_Ratnakar_ApprovalPendingForm(array('action' => $this->formatURL('/corp_ratnakar_cardholder/exportapprovalpending'),
                                              'method' => 'GET',
                                       ));  
        
        $cardholdersModel = new Corp_Ratnakar_Cardholders();
        $exportData = $cardholdersModel->exportpendingcardholdersdetails($qurStr);
                
        $columns = array(
            'Medi Assist Id',
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
                                    $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/exportapprovalpending?product_id='.$qurStr['product_id'])); 
            }
           
          
       }
       
       
    public function bulkapproveAction() {
        
        $this->title = "Bulk Approve";
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        
        $customerModel = new Corp_Ratnakar_Cardholders();
        //$customerLogModel = new Corp_Boi_CustomersLog();
        //$objCustomerDetailModel = new Corp_Boi_CustomerDetail();
       
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
                $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/approvalpending')); 
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
        $session = new Zend_Session_Namespace('App.Agent.Controller'); 
       
        $objCustomerModel = new Corp_Ratnakar_Cardholders();
        $id = $this->_getParam('id');
        $custDetails = $objCustomerModel->findById($id);
       
        $errorExists = false;
        // Get our form and validate it
        $form = new Corp_Ratnakar_ApproveForm();  
        $formData  = $this->_request->getPost();
        $customerLogModel = new Corp_Ratnakar_CustomersLog();
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
                 $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/approvalpending'));
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
        $this->title = 'Reject Agent';
        
        $user = Zend_Auth::getInstance()->getIdentity();        
        $session = new Zend_Session_Namespace('App.Agent.Controller'); 
       
        $objCustomerModel = new Corp_Ratnakar_Cardholders();
        $id = $this->_getParam('id');
        $custDetails = $objCustomerModel->findById($id);
       
        $errorExists = false;
        // Get our form and validate it
        $form = new Corp_Ratnakar_RejectForm();  
        $formData  = $this->_request->getPost();
        $customerLogModel = new Corp_Ratnakar_CustomersLog();
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
                 $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/approvalpending'));
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
   
   public function upgradekycsearchAction() {
        $this->title = 'Update KYC Details';
        $productId = $this->_getParam('product_id');
        $data['product_id'] = $productId;
        $data['searchCriteria'] = $this->_getParam('searchCriteria');
        $data['keyword'] = $this->_getParam('keyword');
        $data['sub'] = $this->_getParam('sub');
        
        $cardholdersModel = new Corp_Ratnakar_Cardholders();
        $page = $this->_getParam('page');
        $form = new Corp_Ratnakar_UpgradeKYCSearchForm(array('action' => $this->formatURL('/corp_ratnakar_cardholder/upgradekycsearch'),
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
        $objCustomerModel = new Corp_Ratnakar_Cardholders();
       
        $form = new Corp_Ratnakar_RevertKYCForm();  
        $formData  = $this->_request->getPost();
        $customerLogModel = new Corp_Ratnakar_CustomersLog();
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
                 $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/upgradekycsearch'));
                 
                 
                    
                
                                
                         }catch (Exception $e ) { 
                                                        $errorExists = true; 
                                                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                                                        $form->populate($formData);
                                                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                                    }                 
         
                }
            else{
                $this->_helper->FlashMessenger( array('msg-error' => 'Cardholder with Id does not exist',) ); 
                                                        
            }
            }
            
    }  
           
            $this->view->errorExists = $errorExists;
            $this->view->item = $custDetails;
    }
    
    public function upgradekycAction(){      
        $this->title = 'Upgrade to KYC';
        $user = Zend_Auth::getInstance()->getIdentity();        
        $objCustomerModel = new Corp_Ratnakar_Cardholders();
        $config = App_DI_Container::get('ConfigObject');
        $currDate = date('Y-m-d');
        $uploadlimit = $config->agent->uploadfile->size;
        $errorExists = false;
        $docModel = new Documents();
        $form = new Corp_Ratnakar_UpgradeKYCForm();  
        $formData  = $this->_request->getPost();
        $customerLogModel = new Corp_Ratnakar_CustomersLog();
        $this->view->form = $form;
        $id = $this->_getParam('id');
        $custDetails = $objCustomerModel->findById($id);
        $custDetails = Util::toArray($custDetails);
        $isError = FALSE;
        $productCorp = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_GENERIC_GPR);
        $productCorpUnicode = $productCorp->product->unicode;
        $productModel = new Products();
        $productId = $productModel->getProductInfoByUnicode($productCorpUnicode);
        
        if($this->getRequest()->isPost()){
            
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

                        if( $addrDocFile != '' || $idDocFile !=''){

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

                            $upload->setDestination(UPLOAD_PATH_RAT_CORP_DOC);
                        
                            try{

                                //All validations correct then upload file
                                if($upload->isValid()){
                                    $upload->receive(); // upload received file(s)

                                    $this->_helper->FlashMessenger(
                                        array(
                                               'msg-success' => 'File uploaded successfully',

                                             )
                                        );
                                }else{
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
                        if($isError == FALSE){
                            
                            //*********************upload files********************//
                            if ($addrDocFile != '' || $idDocFile !=''){    
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

                            $updateArr = array('id_proof_type' => $formData['id_proof_type'],'id_proof_number' => $formData['id_proof_number'], 
                               'address_proof_type' => $formData['address_proof_type'],'address_proof_number' => $formData['address_proof_number']
                               );
                            $whereupdate = "id = " . $id;
                            $objCustomerModel->update($updateArr , $whereupdate);
                
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
                            $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/upgradekycsearch'));
                        }
                
                                
                    }catch (Exception $e ){ 
                        $errorExists = true; 
                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                        $form->populate($formData);
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    }                 
         
                }else{
                    $this->_helper->FlashMessenger( array('msg-error' => 'Cardholder with Id does not exist',) ); 
                }
            }
            
        }else{
            $form->populate($custDetails);
        } 
           
        
        $this->view->errorExists = $errorExists;
    }
    
    public function crnstatusAction() {
        
        $this->title = 'CRN Status Report';
        
        // Get our form and validate it
        $form = new Corp_Ratnakar_CRNStatusForm(array('action' => $this->formatURL('/corp_ratnakar_cardholder/crnstatus'),
            'method' => 'POST',
        ));
        
        $formData = $this->_request->getPost();
        $page = $this->_getParam('page');
        $crn = $this->_getParam('crn');
        $status = $this->_getParam('crn_status');
        $card_pack_id = $this->_getParam('card_pack_id');
        $file = $this->_getParam('file');
        $sub = $this->_getParam('sub');
        $productId = $this->_getParam('product_id');
        
        $session = new Zend_Session_Namespace('App.Operation.Controller');
        $session->items_per_page=10; 
        
        if($sub == 1) {
                //$productModel = new Products();
                //$productId = $productModel->getProductIDbyConst(PRODUCT_CONST_RAT_GPR);
            
                $objCRNMaster = new CRNMaster();
                $sql = $objCRNMaster->searchCRNStatus(array(
                    'product_id' => $productId,
                    'status' => $status,
                    'card_number' => $crn,
                    'card_pack_id' => $card_pack_id,
                    'file' => $file,
                    ),'SQL');

                $this->view->paginator = $objCRNMaster->paginateByArray($sql, $page, $paginate = NULL);
                $form->getElement('crn')->setValue($crn);
                $form->getElement('crn_status')->setValue($status);
                $form->getElement('card_pack_id')->setValue($card_pack_id);
                $form->getElement('file')->setValue($file);
                $form->getElement('sub')->setValue($sub);
                $form->getElement('product_id')->setValue($productId);
                $this->view->sub = $sub;
                $this->view->title = $this->title;
        }
        $this->view->form = $form;
        $this->view->formData = $formData;
    }
    
    public function exportcrnstatusAction()
    {
        $qurStr['crn'] = $this->_getParam('crn');
        $qurStr['status'] = $this->_getParam('crn_status');
        $qurStr['card_pack_id'] = $this->_getParam('card_pack_id');
        $qurStr['file'] = $this->_getParam('file');
        $qurStr['product_id'] = $this->_getParam('product_id');
        
        // Get our form and validate it
        $form = new Corp_Ratnakar_CRNStatusForm(array('action' => $this->formatURL('/corp_ratnakar_cardholder/crnstatus'),
            'method' => 'POST',
        ));

        if ($form->isValid($qurStr)) {            

            //$productModel = new Products();
            //$qurStr['product_id'] = $productModel->getProductIDbyConst(PRODUCT_CONST_RAT_GPR);
                
            $cardholdersModel = new Corp_Ratnakar_Cardholders();
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
                $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/crnstatus?crn=' . $qurStr['crn']. '&crn_status=' . $qurStr['status']. '&card_pack_id=' . $qurStr['card_pack_id']. '&file=' . $qurStr['file']));
            }
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Invalid data'));
            $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/crnstatus?crn=' . $qurStr['crn']. '&crn_status=' . $qurStr['status']. '&card_pack_id=' . $qurStr['card_pack_id']. '&file=' . $qurStr['file']));
        }
    }
    
    public function kycupgradationAction()
    {
        $this->title = 'KYC Upgradation Report';
        
        // Get our form and validate it
        $form = new KycUpgradationForm(array('action' => $this->formatURL('/corp_ratnakar_cardholder/kycupgradation'),
                                                    'method' => 'POST',
                                             )); 
        $page = $this->_getParam('page');
        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $sub;
        $qurStr['to_date']    = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
      
        if($sub!=''){ 
             
            if($form->isValid($qurStr)){ 
                if ($qurStr['dur'] != '') {
                    $durationArr = Util::getDurationDates($qurStr['dur']);
                    $qurData['from'] = $durationArr['from'];
                    $qurData['to'] = $durationArr['to'];
                    $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                    $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                    $this->view->from = $fromDate[0];
                    $this->view->to   = $toDate[0];                 
                    $this->view->title = 'Kyc Upgradation Report from '.$fromDate[0];
                    $this->view->title .= ' to '.$toDate[0];

                } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurFrm['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $qurFrm['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');

                    $this->view->title = 'Kyc Upgradation Report from '.Util::returnDateFormatted($qurFrm['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to '.Util::returnDateFormatted($qurFrm['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurFrm['from'];
                    $this->view->to   = $qurFrm['to'];
                }

                $cardholdersModel = new Corp_Ratnakar_Cardholders();
                $cardholders = $cardholdersModel->getcustomerKYC($qurData);
                $this->view->paginator = $cardholdersModel->paginateByArray($cardholders, $page, $paginate = NULL);
                $this->view->formData = $qurStr;
            }             
        }
        $this->view->form = $form;
    }
    
    public function exportkycupgradationAction()
    {
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['to_date']    = $this->_getParam('to_date');
        $qurStr['from_date']  = $this->_getParam('from_date');
        
        // Get our form and validate it
        $form = new KycUpgradationForm(array('action' => $this->formatURL('/corp_ratnakar_cardholder/kycupgradation'),
            'method' => 'POST',
        ));

        if ($form->isValid($qurStr)) {            
                
            if ($qurStr['dur'] != '') {
                $durationArr = Util::getDurationDates($qurStr['dur']);
                $qurData['from'] = $durationArr['from'];
                $qurData['to'] = $durationArr['to'];
            } else if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", 'to');
                $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", 'from');
            }

            $cardholdersModel = new Corp_Ratnakar_Cardholders();
            $exportData = $cardholdersModel->exportKycupgradation($qurData);

            $columns = array(
                'Date',
                'Cardholder Name',
                'CRN',
                'Mobile Number',
                'Product Name',
                'Bank Name',
                'Employee Id',
                'Id Proof Type',
                'Id Proof Number',
                'Customer Address1',
                'Customer Address2',
                'Pincode',
                'State',    
                'Status',
                'Upgrade Date'
            );

            $objCSV = new CSV();
            try {
                $resp = $objCSV->export($exportData, $columns, 'kyc_upgrade_report');
                exit;
            } catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/kycupgradation?dur=' . $qurStr['dur']. '&from_date=' . $qurStr['from_date']. '&to_date=' . $qurStr['to_date']));
            }
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Invalid data'));
            $this->_redirect($this->formatURL('/corp_ratnakar_cardholder/kycupgradation?dur=' . $qurStr['dur']. '&from_date=' . $qurStr['from_date']. '&to_date=' . $qurStr['to_date']));
        }
    }
    
    
    
    
}
