<?php

/**
 * HIC Default entry point
 *
 * @author Mini
 */
class Corp_Boi_IndexController extends App_Operation_Controller {

    //put your code here


    public function init() {
        parent::init();
    }

    public function indexAction() {
        
    }

   public function uploadcrnAction() {
        $this->title = "Bulk CRN Upload";
        $page = $this->_getParam('page');
        $form = new Corp_Boi_UploadcrnForm();
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $batch = $this->getRequest()->getParam('batch');
        $this->view->incorrectData = FALSE;
        $cardholdersModel = new CRNMaster();
        $this->view->records = FALSE;
        if($this->getRequest()->isPost()) {
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
            if($form->isValid($this->getRequest()->getPost())) {
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
                   $this->_redirect($this->formatURL('/corp_boi_index/uploadcrn/'));
                }
                
                $fp = fopen($name, 'r');
                $consolidateArr = array();
                while (!feof($fp)) {
                    
                    $line = fgets($fp);
                    if(!empty($line)) {
                        $delimiter = CRN_MASTER_FILE_SEPARATOR;
                        $dataArr = str_getcsv($line, $delimiter);
                        //$consolidateArr[] = $dataArr;
                        if(!empty($dataArr)) {
                            try {
                                // direct insert into rat_corp_cardholders
                                $data['card_number'] = $dataArr[0];
                                $data['card_pack_id'] = $dataArr[1];
                                $data['member_id'] = $dataArr[2];
                                $data['date_expiry'] = $dataArr[3];
                                $data['file'] = $batchName;
                                $data['product_id'] = $formData['product_id'];
                                if($cardholdersModel->checkDuplicateWithProductId($data)) {
                                    $data['status'] = STATUS_DUPLICATE;
                                } else {
                                    $data['status'] = STATUS_TEMP;
                                }
                                //echo "<pre>"; print_r($data); exit;
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
                fclose($fp);
            }
            if(!empty($consolidateArr)) {
                $this->view->batch = $batchName;
                $this->view->records = TRUE;
                 $this->view->paginator = $cardholdersModel->paginateByArray($consolidateArr, $page, NULL);
                 $this->view->paginator->setItemCountPerPage(0);
            }
        }
        if($this->getRequest()->isPost()) {        
            $batch = $this->getRequest()->getParam('batch');
                if($batch != '') {
                    try {

                        $cardholdersModel->crnBulkUpdate($formData['reqid'], STATUS_FREE);
                        $this->_helper->FlashMessenger(
                                array(
                                    'msg-success' => 'CRN have been updated in our records',
                                )
                        );
                        $this->_redirect($this->formatURL('/corp_boi_index/uploadcrn/'));
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
    
    
    public function crnstatusAction() {
        $this->title = 'CRN Status Report';
        // Get our form and validate it
        $form = new CRNStatusForm(array('action' => $this->formatURL('/corp_boi_index/crnstatus/'),
            'method' => 'POST',
        ));
        $page = $this->_getParam('page');
        $crn = $this->_getParam('crn');
        $status = $this->_getParam('crn_status');
        $card_pack_id = $this->_getParam('card_pack_id');
        $file = $this->_getParam('file');
        $sub = $this->_getParam('sub');

        $session = new Zend_Session_Namespace('App.Operation.Controller');
        $session->items_per_page=10; 
        
        if($sub == 1) {

            $product = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NDSC);
            $productModel = new Products();
            $productInfo = $productModel->getProductInfoByUnicode($product->product->unicode);
            $objCRNMaster = new CRNMaster();
            $sql = $objCRNMaster->searchCRNStatus(array(
                'product_id' => $productInfo->id,
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
            $this->view->sub = $sub;
            $this->view->title = $this->title;
        }
        $this->view->form = $form;
    }
    
    public function loadAction() {
        
        $this->title = "Upload of Card Load";
        $page = $this->_getParam('page');
        $form = new Corp_Boi_CardloadForm();
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $cardloadModel = new Corp_Boi_Cardload();
        $this->view->records = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
//        $cardholdersModel->ratCorpECSRegn();
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->receive();
                $name = $upload->getFileName('doc_path');

                $batchName = $upload->getFileName('doc_path', $path = FALSE) ;
                $checkFile = $cardloadModel->checkFilename($batchName);
                if(!$checkFile) 
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
                        if(!empty($line) && strpos($line,CORP_WALLET_END_OF_FILE) === false) {
                            $delimiter = CORP_WALLET_UPLOAD_DELIMITER;
                            $dataArr = str_getcsv($line, $delimiter);
                            $dataArr['product_id'] = $formData['product_id'];
                            $dataArr['type'] = 'wlt';
                            $dataArr['count'] = $formData['count'];
                            $dataArr['value'] = $formData['value'];
                            $consolidateArr[] = $dataArr;
                            $arrLength = Util::getArrayLength($dataArr);
                            if(!empty($dataArr)) {
                                if($arrLength != CORP_WALLET_UPLOAD_COLUMNS)
                                    $this->view->incorrectData = TRUE;
                                try {
                                    // direct insert into rat_corp_cardholders

                                    if($dataArr[CORP_WALLET_MANDATORY_FIELD_INDEX] == '') {
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
                    elseif($val - (int) Util::filterAmount($formData['value']) != 0)
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

        if($submit != '') {


            try {

                $cardloadModel->bulkAddCardload($formData['reqid'], $formData['batch']);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Corporate Wallet details have been updated in our records',
                        )
                );
                $this->_redirect($this->formatURL('/corp_boi_index/load/'));
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
    
    public function disbursementloadAction() {
               set_time_limit(500); 
ini_set('memory_limit','500M');

        $this->title = "Upload Disbursement File";
        $page = $this->_getParam('page');
        $form = new Corp_Boi_DisbursementLoadForm();
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $cardloadModel = new Corp_Boi_Cardload();
        $this->view->records = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
//        $cardholdersModel->ratCorpECSRegn();
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
              $checkDuplicateNo = $cardloadModel->checkDisbursementNo($formData['disbursement_id']);
              if($checkDuplicateNo) {
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->receive();
                $name = $upload->getFileName('doc_path');

                $batchName = $upload->getFileName('doc_path', $path = FALSE) ;
                $checkFile = $cardloadModel->disbursementCheckFilename($batchName);
                if(!$checkFile) 
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
                        if(!empty($line) && strpos($line,CORP_WALLET_END_OF_FILE) === false) {
                            $delimiter = CORP_WALLET_UPLOAD_DELIMITER;
                            $dataArr = str_getcsv($line, $delimiter);
                            $dataArr['type'] = 'MI';
                            $dataArr['count'] = $formData['count'];
                            $dataArr['value'] = $formData['value'];
                            $dataArr['disbursement_number'] = $formData['disbursement_id'];
                            $consolidateArr[] = $dataArr;
                            $arrLength = Util::getArrayLength($dataArr);
                            if(!empty($dataArr)) { 
                                if($arrLength != CORP_DISBURESEMENT_UPLOAD_COLUMNS)
                                    $this->view->incorrectData = TRUE;
                                try {
                                    // direct insert into rat_corp_cardholders

                                    
                                    $cnt++;
                                    $val += $dataArr[4];
                                    $status = STATUS_TEMP;
                                     
                                    $cardloadModel->insertdisbursementRequestBatch($dataArr, $batchName, $status);
                                    
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
                        $data = array('status' => STATUS_TEMP, 
                            'failed_reason' => 'Count does not match the entered count',
                            'date_updated' => new Zend_Db_Expr('NOW()'));
                        $where = " batch_name = '$batchName'";
                        $cardloadModel->updateDisbursementLoad($data, $where);
                        $this->_helper->FlashMessenger(
                            array(
                                'msg-error' => "Count does not match the entered count",
                            )
                        );
                        $this->view->records = FALSE;
                    }
                    elseif(!Util::compareAmount ($val,Util::filterAmount($formData['value'])))
                    {
                        $data = array('status' => STATUS_TEMP, 
                            'failed_reason' => 'Value does not match the entered value of load',
                            'date_updated' => new Zend_Db_Expr('NOW()'));
                        $where = " batch_name = '$batchName'";
                        $cardloadModel->updateDisbursementLoad($data, $where);
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
                        $this->view->paginator = $cardloadModel->showPendingDisbursementCardloadDetails($batchName, $page, $paginate = NULL);
                    }
                    $this->view->batch_name = $batchName;
                    
                    fclose($fp);
                }
                
            }     
            else{
                $this->_helper->FlashMessenger(
                            array(
                                'msg-error' => "Disbursement no exists",
                            )
                        ); 
            }    
            }
        }
        
        if($submit != '') {
        
        
            try {
        
                $cardloadModel->updateDisbursementLoadStatus($formData['reqid'], $formData['batch']);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Corporate Disbursement details have been updated in our records',
                        )
                );
                $this->_redirect($this->formatURL('/corp_boi_index/disbursementload/'));
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
    //start rnew code
    public function disbursementreportAction() { 
         $this->title = 'Search Disbursement Details';
        // Get our form and validate it
        $form = new Corp_Boi_DisbursementLoadReport(array('action' => $this->formatURL('/corp_boi_index/disbursementreport'),
            'method' => 'POST',
        ));
        $cardloadModel = new Corp_Boi_Cardload();
        $qurStr['disbursement_number'] = $this->_getParam('disbursement_number');
        $qurStr['bucket'] = $this->_getParam('bucket');
        $qurStr['aadhar_no'] = $this->_getParam('aadhar_no');
        $qurStr['account_number'] = $this->_getParam('account_number');
        $qurStr['submit'] = $this->_getParam('submit');
        $updateStr['id'] = $this->_getParam('id');
        $updateStr['bucketId'] = $this->_getParam('bucketId');
        $updateStr['batch_name'] = $this->_getParam('batch_name');
        $qurStr['batch_name'] = $this->_getParam('batch_name');
        $qurStr['bucket'] = array_filter($qurStr['bucket']);
        $page = $this->_getParam('page');
        if($updateStr['id']!="" && $updateStr['bucketId']!=""){
            $id = $updateStr['id'];
            $data = array('bucket' => $updateStr['bucketId'],'date_updated' => new Zend_Db_Expr('NOW()'));
            $where = " id = '$id'"; 
            $cardloadModel->updateDisbursementLoad($data, $where);
            $this->_redirect($this->formatURL('/corp_boi_index/disbursementreport?disbursement_number&'.$qurStr['disbursement_number'].'&bucket='.$qurStr['bucket'].'&submit=Submit'));
        }
       
        
        if($qurStr['submit']) {
            if($form->isValid($qurStr)) {
                $this->view->records = TRUE;
                $this->view->paginator = $cardloadModel->getDisbursementLoad($page, $qurStr, $paginate = NULL);
                
            }
        }
        $this->view->returnManualArr = array('1','2','3','4','9');
        $this->view->form = $form;
        $this->view->formData = $qurStr;
        
    }

    /* exportfeereportAction() is responsible to create the csv file on fly with fee data of agents
     * and let user download that file.
     */

    public function exportdisbursementreportAction() {
set_time_limit(0);         
ini_set('memory_limit','400M');
//error_reporting(1);
//ini_set("display_errors", 1); 
//ini_set('error_reporting', E_ALL);
        $qurStr['disbursement_number'] = $this->_getParam('disbursement_number');
        $qurStr['bucket'] = $this->_getParam('bucket');
        $qurStr['aadhar_no'] = $this->_getParam('aadhar_no');
        $qurStr['account_number'] = $this->_getParam('account_number');
        $qurStr['batch_name'] = $this->_getParam('batch_name');
        $qurStr['bucket'] = array_filter($qurStr['bucket']); 
        $cardloadModel = new Corp_Boi_Cardload();
        $paginator = $cardloadModel->getDisbursementLoad($page = 1, $qurStr, $paginate = NULL, TRUE);


//echo '<!-- '.count($paginator);
//exit;
//echo '<pre>';
//print_r($paginator);
//echo '</pre> -->';
//exit;

        if(count($paginator)) {
 try {           
                    
                $reportData = array();
                $i=0;
                foreach($paginator as $item) :
                    
                    $reportData[$i]['txn_identifier']= $item['txn_identifier'];
                    $reportData[$i][]= $item['disbursement_number'];
                    $reportData[$i]['account_number']= $item['account_number'];
                    $reportData[$i]['ifsc_code']= $item['ifsc_code'];
                    $reportData[$i]['aadhar_no']= $item['aadhar_no'];
                    $reportData[$i]['bucket']= $item['bucket'];
                    $reportData[$i]['amount']= $item['amount'];
                    $reportData[$i]['currency']= $item['currency'];
                    $reportData[$i]['narration']= $item['narration'];
                    $reportData[$i]['wallet_code']= $item['wallet_code'];
                    $reportData[$i]['card_type']= $item['card_type'];
                    $reportData[$i]['mode']= $item['mode'];
                    $reportData[$i]['payment_status']= $item['payment_status'];
                    $reportData[$i]['batch_name']= $item['batch_name'];
                    $i++;
                endforeach;
 } catch (Exception $e) {
                echo '<!-- <pre> ';
                print_r($e);
                echo '</pre> -->';
                exit;
            }

                $columns = array(
                    'Txn Identifier Type',
                    'Disbursement Number',
                    'Account Number',
                    'IFSC Code',
                    'Aadhaar No',
                    'Bucket',
                    'Amount',
                    'Currency',
                    'Narration',
                    'Wallet Code',
                    'Card Type',
                    'Mode',
                    'Payment Status',
                    'File Name',
                );

                $objCSV = new CSV();
                try {
                    $resp = $objCSV->export($reportData, $columns, 'BOI_NSDC_DISBURSEMENT_LOAD_FILE_REPORT');
                    exit;
                } catch (Exception $e) {
//echo '<!-- ';
//echo '<pre>';
//print_r($e);
//echo '</pre>';
//echo '-->';
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                    $this->_redirect($this->formatURL('/corp_boi_index/disbursementreport?disbursement_number=' . $qurStr['disbursement_number'] . '&bucket=' . $qurStr['bucket'] . '&aadhar_no=' . $qurStr['aadhar_no'] . '&account_number=' . $qurStr['account_number']));
                }
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Invalid duration'));
            $this->_redirect($this->formatURL('/corp_boi_index/disbursementreport?disbursement_number=' . $qurStr['disbursement_number'] . '&bucket=' . $qurStr['bucket'] . '&aadhar_no=' . $qurStr['aadhar_no'] . '&account_number=' . $qurStr['account_number']));
        }
    }
    
    //start rnew code
    public function disbursemenfileAction() { 
         $this->title = 'Download Disbursement TTUM File';
        // Get our form and validate it
        $form = new Corp_Boi_DisbursementFileReport(array('action' => $this->formatURL('/corp_boi_index/disbursemenfile'),
            'method' => 'POST',
        ));
        $cardloadModel = new Corp_Boi_Cardload();
        $fileObject = new Files();
        $qurStr['disbursement_number'] = $this->_getParam('disbursement_number');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['batch_name'] = $this->_getParam('batch_name');
        $qurStr['submit'] = $this->_getParam('submit');
        $type = $this->_getParam('type');
        $file_id = $this->_getParam('file_id');
        $updateStr['id'] = $this->_getParam('id');
        $page = $this->_getParam('page');
        if(($type == 'download' || $type == 'download_wallet')&& $file_id > 0) {
            $fileInfo = $cardloadModel->getDisbursementFileInfo($file_id);
            //echo "<pre>"; print_r($fileInfo); exit;
            if(!empty($fileInfo)) {
                
                $fileObject->setFilepath(APPLICATION_UPLOAD_PATH);
                if($type == 'download'){
                    $fileObject->setFilename($fileInfo['file_name']);
                    if($fileInfo['updated_ttum_file_name']){
                        $fileObject->setDownloadFilename($fileInfo['updated_ttum_file_name']);
                    }else{
                        $fileObject->setDownloadFilename($fileInfo['file_name']);
                    }
                }
                
                $fileObject->downloadFile();
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender(TRUE);
            } else {
                 $this->_helper->FlashMessenger( array('msg-error' => 'File not found.') );                 
            }
        }
        
        if($updateStr['id']){
            $id = $updateStr['id'];
            $data = array('status' => 'processed','date_process' => new Zend_Db_Expr('NOW()'));
            $where = " id = '$id'"; 
            $cardloadModel->updateDisbursementFile($data, $where);
            $this->_redirect($this->formatURL('/corp_boi_index/disbursemenfile?disbursement_number=' . $qurStr['disbursement_number'] . '&from_date=' . $qurStr['from_date'] . '&to_date=' . $qurStr['to_date'].'&submit=Submit&page='.$page));
        }
       
        if($qurStr['submit']) {
            if($form->isValid($qurStr)) {
                $this->view->records = TRUE;
                $params = array();
                if(!empty($qurStr['to_date']) && !empty($qurStr['from_date'])){
                    $params['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-","-",'to');
                    $params['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-","-","from");
                }   
                $params['disbursement_number'] = $qurStr['disbursement_number'];
                $params['batch_name'] = $qurStr['batch_name'];
                $this->view->paginator = $cardloadModel->getDisbursementFile($page, $params, $paginate = NULL);
                
            }
        }
        
        $this->view->form = $form;
        $this->view->formData = $qurStr;
        
    }
    
       //start rnew code
    public function summarybucketreportAction() { 
         $this->title = 'Search Disbursement Details';
        // Get our form and validate it
        $form = new Corp_Boi_DisbursementSummaryReport(array('action' => $this->formatURL('/corp_boi_index/summarybucketreport'),
            'method' => 'POST',
        ));
        $cardloadModel = new Corp_Boi_Cardload();
        $qurStr['disbursement_number'] = $this->_getParam('disbursement_number');
        $qurStr['bucket'] = $this->_getParam('bucket');
        $qurStr['aadhar_no'] = $this->_getParam('aadhar_no');
        $qurStr['account_number'] = $this->_getParam('account_number');
        $qurStr['submit'] = $this->_getParam('submit');
        $page = $this->_getParam('page');
        $qurStr['bucket'] = array_filter($qurStr['bucket']);
        //echo count($qurStr['bucket']); exit;
        if($qurStr['submit']) {
            if($form->isValid($qurStr)) {
                $this->view->records = TRUE;
                $bucketData = $cardloadModel->getSummaryBucket($page, $qurStr, $paginate = NULL);
                $paginator = $cardloadModel->paginateByArray($bucketData, $page, $paginate = NULL);
                $this->view->paginator = $paginator;
            }
        }
        $this->view->globalBuckets = Zend_Registry::get("BOI_NSDC_DISBURSEMENT_BUCKETS");
        
        $this->view->form = $form;
        $this->view->formData = $qurStr;
        $this->records = TRUE;
        
    }

    /* exportfeereportAction() is responsible to create the csv file on fly with fee data of agents
     * and let user download that file.
     */

    public function exportsummarybucketreportAction() {
        
        $qurStr['disbursement_number'] = $this->_getParam('disbursement_number');
        $qurStr['bucket'] = $this->_getParam('bucket');
        $qurStr['bucket'] = explode(",",$this->_getParam('bucket'));
        $qurStr['aadhar_no'] = $this->_getParam('aadhar_no');
        $qurStr['account_number'] = $this->_getParam('account_number');
        $qurStr['bucket'] = array_filter($qurStr['bucket']);
        $cardloadModel = new Corp_Boi_Cardload();
        $bucketData = $cardloadModel->getSummaryBucket($page, $qurStr, $paginate = NULL);
        //echo "<pre>";print_r($bucketData); exit;
        if(count($bucketData)) {
            
                    
                $reportData = array();
                $i=0;
                foreach($bucketData as $item) :
                   
                    $globalBuckets = Zend_Registry::get("BOI_NSDC_DISBURSEMENT_BUCKETS");
                    $reportData[$i]['disbursement_number']= 'Disbursement No-'.$item['disbursement_number'];
                    $reportData[$i]['type']= 'Count';
                    //unset($globalBuckets['9']);
                    foreach($globalBuckets as $key => $val){ 
                        if(isset($item[$key])) { 
                              $reportData[$i][]=$item[$key]['count'];
                        } else {
                               $reportData[$i][]=0;         
                        } 
                    }
                    $reportData[$i][]=$item['totalCnt'];
                    $i++;
                    $reportData[$i]['disbursement_number']= 'Disbursement No-'.$item['disbursement_number'];
                    $reportData[$i]['type']= 'Amount';
                    foreach($globalBuckets as $key => $val){ 
                        if(isset($item[$key])) { 
                              $reportData[$i][]=$item[$key]['amount'];
                        } else {
                               $reportData[$i][]=0;         
                        } 
                    }
                    $reportData[$i][]=$item['totalAmt'];
                     $i++;
                endforeach;
                
                $columns = array(
                    '0'=>'Disbursement Number',
                    '1'=>'Type',
                );
                
                $globalBuckets = Zend_Registry::get("BOI_NSDC_DISBURSEMENT_BUCKETS");
                //unset($globalBuckets['9']);
                foreach($globalBuckets as $key => $val){
                    $columns[] = $val;
                }
                $columns[] = 'Total';
                $objCSV = new CSV();
                try {
                    $resp = $objCSV->export($reportData, $columns, 'BOI_NSDC_DISBURSEMENT_BUCKETS_REPORT');
                    exit;
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                    $this->_redirect($this->formatURL('/corp_boi_index/summarybucketreport?disbursement_number=' . $qurStr['disbursement_number'] . '&bucket=' . implode(",",$qurStr['bucket']) . '&aadhar_no=' . $qurStr['aadhar_no'] . '&account_number=' . $qurStr['account_number']));
                }
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Invalid Data'));
            $this->_redirect($this->formatURL('/corp_boi_index/summarybucketreport?disbursement_number=' . $qurStr['disbursement_number'] . '&bucket=' . implode(",",$qurStr['bucket']) . '&aadhar_no=' . $qurStr['aadhar_no'] . '&account_number=' . $qurStr['account_number']));
        }
    }
    
    //start rnew code
    public function summarypaymentreportAction() { 
         $this->title = 'Search Disbursement Details';
        // Get our form and validate it
        $form = new Corp_Boi_DisbursementSummaryPaymentReport(array('action' => $this->formatURL('/corp_boi_index/summarypaymentreport'),
            'method' => 'POST',
        ));
        $cardloadModel = new Corp_Boi_Cardload();
        $tpnameArr =  $cardloadModel->getTPName();
        //echo "<pre>";print_r($tpnameArr); exit;
        $form->tp_name->addMultiOptions($tpnameArr);
        $qurStr['disbursement_number'] = $this->_getParam('disbursement_number');
        $qurStr['payment_status'] = $this->_getParam('payment_status');
        $qurStr['aadhar_no'] = $this->_getParam('aadhar_no');
        $qurStr['account_number'] = $this->_getParam('account_number');
        $qurStr['submit'] = $this->_getParam('submit');
        $qurStr['payment_status'] = array_filter($qurStr['payment_status']);
        $qurStr['tp_name'] = $this->_getParam('tp_name');
        $page = $this->_getParam('page');
        if($qurStr['submit']) {
            if($form->isValid($qurStr)) {
                $this->view->records = TRUE;
                $bucketData = $cardloadModel->getSummaryPaymentBucket($page, $qurStr, $paginate = NULL);
                $paginator = $cardloadModel->paginateByArray($bucketData, $page, $paginate = NULL);
                $this->view->paginator = $paginator;
            }
        }
        $this->view->form = $form;
        $this->view->formData = $qurStr;
        $this->records = TRUE;
        
    }

    /* exportfeereportAction() is responsible to create the csv file on fly with fee data of agents
     * and let user download that file.
     */

    public function exportsummarypaymentreportAction() {
        
        $qurStr['disbursement_number'] = $this->_getParam('disbursement_number');
        $qurStr['payment_status'] = explode(",",$this->_getParam('payment_status'));
        $qurStr['aadhar_no'] = $this->_getParam('aadhar_no');
        $qurStr['account_number'] = $this->_getParam('account_number');
        $qurStr['tp_name'] = $this->_getParam('tp_name');
        $qurStr['payment_status'] = array_filter($qurStr['payment_status']);
        $cardloadModel = new Corp_Boi_Cardload();
        $bucketData = $cardloadModel->getSummaryPaymentBucket($page, $qurStr, $paginate = NULL);
       
        if(count($bucketData)) {
            
                    
                $reportData = array();
                $i=0;
                foreach($bucketData as $item) :
                    $globalBuckets = Zend_Registry::get("BOI_NSDC_DISBURSEMENT_BUCKETS");
                    $reportData[$i]['disbursement_number']= 'Disbursement No-'.$item['disbursement_number'];
                    $reportData[$i]['type']= 'Count';
                    $reportData[$i]['ttum_generated_count'] = $item['ttum_generated']['count'];
                    $reportData[$i]['ttum_processed_count'] = $item['ttum_processed']['count'];
                    $reportData[$i]['ttum_hold_count'] = $item['ttum_hold']['count'];
                    $reportData[$i]['ttum_manual_count'] = $item['ttum_manual']['count'];
                    $reportData[$i]['ttum_pending_count'] = $item['ttum_pending']['count'];
                    $reportData[$i]['count']=$item['count'];
                    $i++;
                    $reportData[$i]['disbursement_number']= 'Disbursement No-'.$item['disbursement_number'];
                    $reportData[$i]['type']= 'Amount';
                    $reportData[$i]['ttum_generated_amount'] = $item['ttum_generated']['total'];
                    $reportData[$i]['ttum_processed_amount'] = $item['ttum_processed']['total'];
                    $reportData[$i]['ttum_hold_amount'] = $item['ttum_hold']['total'];
                    $reportData[$i]['ttum_manual_amount'] = $item['ttum_manual']['total'];
                    $reportData[$i]['ttum_pending_amount'] = $item['ttum_pending']['total']; 
                    $reportData[$i]['total']=$item['total'];
                    $i++;
                endforeach;
                //echo "<pre>"; print_r($reportData); exit;
                $columns = array(
                    '0'=>'Disbursement Number',
                    '1'=>'Type',
                    '2'=>'TTUM Generated',
                    '3'=>'TTUM Processed',
                    '4'=>'Hold',
                    '5'=>'Processed Manually',
                    '6'=>'Pending',
                    '7'=>'Total',

                );
                
                $objCSV = new CSV();
                try {
                    $resp = $objCSV->export($reportData, $columns, 'BOI_NSDC_DISBURSEMENT_PAYMENT_REPORT');
                    exit;
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                    $this->_redirect($this->formatURL("/corp_boi_index/summarypaymentreport?disbursement_number=".$qurStr['disbursement_number']."&payment_status=".implode(",",$qurStr['payment_status'])."&account_number=".$qurStr['account_number']."&aadhar_no=".$qurStr['aadhar_no']."&tp_name=".$qurStr['tp_name']));
                }
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Invalid Data'));
            $this->_redirect($this->formatURL("/corp_boi_index/summarypaymentreport?disbursement_number=".$qurStr['disbursement_number']."&payment_status=".implode(",",$qurStr['payment_status'])."&account_number=".$qurStr['account_number']."&aadhar_no=".$qurStr['aadhar_no']."&tp_name=".$qurStr['tp_name']));
        }
    }
    
    public function updatedisbursementstatusAction() { 
         $this->title = 'Search Disbursement Details';
        // Get our form and validate it
        $form = new Corp_Boi_DisbursementUpdateStatus(array('action' => $this->formatURL('/corp_boi_index/updatedisbursementstatus'),
            'method' => 'POST',
        ));
        $cardloadModel = new Corp_Boi_Cardload();
        $qurStr['disbursement_number'] = $this->_getParam('disbursement_number');
        $qurStr['bucket'] = $this->_getParam('bucket');
        $qurStr['aadhar_no'] = $this->_getParam('aadhar_no');
        $qurStr['account_number'] = $this->_getParam('account_number');
        $qurStr['payment_status'] = $this->_getParam('payment_status');
        $qurStr['submit'] = $this->_getParam('submit');
        $updateStr['id'] = $this->_getParam('id');
        $updateStr['bucketId'] = $this->_getParam('bucketId');
        $updateStr['batch_name'] = $this->_getParam('batch_name');
        $qurStr['bucket'] = array_filter($qurStr['bucket']);
        $qurStr['noofrecords'] = $this->_getParam('noofrecords');
        
        if($qurStr['submit']) {
            if($form->isValid($qurStr)) {
                $this->view->records = TRUE;
                $this->view->paginator = $cardloadModel->getDisbursementLoad($page, $qurStr, $paginate = NULL, TRUE);
                
            }
        }
        $this->view->form = $form;
        $this->view->formData = $qurStr;
    }
    public function updatestatusconfirmAction() { 
ini_set('max_execution_time',0);   
        $this->title = 'Search Disbursement Details';
        // Get our form and validate it
        $form = new UpdateDisbursementStatusForm(array(
                            'params' => array('cancelLink' => $this->formatURL('/corp_boi_index/updatedisbursementstatus/'),)
                        )); 
        $cardloadModel = new Corp_Boi_Cardload();
        $qurStr['reqid'] = $this->_getParam('reqid');
        $qurStr['id'] = $this->_getParam('reqid');
        $qurStr['status'] = $this->_getParam('status');
        $qurStr['submit'] = $this->_getParam('submit');
        $updateStr['remarks'] = $this->_getParam('remarks');
        if($qurStr['submit']) {
            $qurStr['id'] = explode(",",$this->_getParam('update_ids'));
            $qurStr['status'] = $this->_getParam('update_status');
            if($form->isValid($this->getRequest()->getPost())) {
                $res = $cardloadModel->updateDisbursementPaymentStatus($form->getValues());
                if($res == 'updated'){
                    $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'The Payment status was successfully updated',
                        )
                    );                
                    $this->_redirect($this->formatURL('/corp_boi_index/updatedisbursementstatus/'));
                        
                }else{
                    $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'An error occurred. Please try again later.',
                        )
                    );                
                    $this->_redirect($this->formatURL('/corp_boi_index/updatedisbursementstatus/'));
                }
            }    
        }
        if($qurStr['status']=='' && empty($qurStr['reqid']) && !$qurStr['submit']){
            $this->_helper->FlashMessenger(array('msg-error' => 'Invalid Data'));
            $this->_redirect($this->formatURL("/corp_boi_index/updatedisbursementstatus"));
        }
        if(!$qurStr['submit']){
            $this->_helper->FlashMessenger(
               array(
                   'msg-success' => 'Following records payment status will be mark as: '.ucfirst($qurStr['status']),
               )
           );
        }
        $form->update_status->setValue($qurStr['status']);
        $form->update_ids->setValue(implode(",",$qurStr['id']));
        $this->view->records = TRUE;
        $this->view->paginator = $cardloadModel->getDisbursementLoad($page, $qurStr, $paginate = NULL, TRUE);
        $this->view->form = $form;
        $this->view->formData = $qurStr;
    }
    
    public function editdisbursemenfileAction() { 
         $this->title = 'Edit Disbursement Details';
          $user = Zend_Auth::getInstance()->getIdentity();
        // Get our form and validate it
        $form = new Corp_Boi_EditDisbursementFile(array('action' => $this->formatURL('/corp_boi_index/editdisbursemenfile'),
            'method' => 'POST',
        ));
        $cardloadModel = new Corp_Boi_Cardload();
        $qurStr['id'] = $this->_getParam('id');
        $qurStr['ttum_file_name'] = $this->_getParam('ttum_file_name');
        $qurStr['submit'] = $this->_getParam('submit');
        if(empty($qurStr['id'])){
            $this->_helper->FlashMessenger(
                array(
                    'msg-error' => 'Invalid Data',
                )
            );                
            $this->_redirect($this->formatURL('/corp_boi_index/disbursemenfile/'));
        }
        
        if($qurStr['submit']) {
            if($form->isValid($qurStr)) {
                $fileInfo = $cardloadModel->getDisbursementFileInfo($qurStr['id']);
                $fileNameArr = explode("_",$fileInfo['file_name']);
                $fileNameArr[1] = $qurStr['ttum_file_name'];
                $newFileName=implode("_",$fileNameArr);
                $data=array('updated_ttum_file_name'=>$newFileName);
                $where = 'id='.$qurStr['id'];
                $cardloadModel->updateDisbursementFile($data,$where);
                $filePathwithName = APPLICATION_UPLOAD_PATH."/".$fileInfo['file_name']; 
                $dataAarray = array();
                $result = substr($qurStr['ttum_file_name'], 0, 6);
                $newresult =  implode("-", str_split($result, 2));
                $dateData = explode("-",$newresult);
                $dateData[2] = "20".$dateData[2];
                $newresult = implode("-",$dateData);
                $file = fopen($filePathwithName, "r");
                while(!feof($file)){
                    $line = fgets($file);
                    $array = explode(' ', $line);
                    $array[count($array)-1] = $newresult; 
                    $dataAarray[] = $array;
                }
                
                unset($dataAarray[count($dataAarray)-1]);
                $seprator = ' ';
                $filenew = new Files();
                $filenew->setBatch($dataAarray, $seprator);
                $filenew->setFilepath(APPLICATION_UPLOAD_PATH);
                $filenew->setFilename($fileInfo['file_name']);
                $filenew->generate(TRUE);
                $disbursementStatusLog = new DisbursementStatusLog();
                
                $data=array();
                $data['disbursement_batch_id'] =  $fileInfo['id'];
                $data['status_type'] = 'editdisbursementfilename';
                $data['status'] =  'update';
                $data['note'] =  'Update disbursement file name From '.$fileInfo['updated_ttum_file_name'].' To '.$newFileName;
                $data['ttum_file_id'] =  $result['ttum_file_id'];
                $data['by_ops_id'] =  $user->id;
                $data['date_created'] = new Zend_Db_Expr('NOW()');
                $disbursementStatusLog->insertLogData($data);
                    
                $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => 'File name was successfully updated.',
                    )
                );                
                //$this->_redirect($this->formatURL('/corp_boi_index/disbursemenfile/'));
            }
        }
        
        //echo "<pre>"; print_r($abc); exit;
        $form->id->setValue($qurStr['id']);
        $this->view->records = TRUE;
        $this->view->paginator = $cardloadModel->getDisbursementFile($page, $qurStr, $paginate = NULL, TRUE);
        $this->view->form = $form;
        $this->view->formData = $qurStr;
    }
    
    //start rnew code
    public function disbursementstatusreportAction() { 
         $this->title = 'Search Disbursement Details';
        // Get our form and validate it
        $form = new Corp_Boi_DisbursementStatusReport(array('action' => $this->formatURL('/corp_boi_index/disbursementstatusreport'),
            'method' => 'POST',
        ));
        $cardloadModel = new Corp_Boi_Cardload();
        $tpnameArr =  $cardloadModel->getTPName();
        //echo "<pre>";print_r($tpnameArr); exit;
        $form->tp_name->addMultiOptions($tpnameArr);
        $qurStr['disbursement_number'] = $this->_getParam('disbursement_number');
        $qurStr['payment_status'] = $this->_getParam('payment_status');
        $qurStr['aadhar_no'] = $this->_getParam('aadhar_no');
        $qurStr['account_number'] = $this->_getParam('account_number');
        $qurStr['submit'] = $this->_getParam('submit');
        $qurStr['payment_status'] = array_filter($qurStr['payment_status']);
        $qurStr['tp_name'] = $this->_getParam('tp_name');
        $page = $this->_getParam('page');
        if($qurStr['submit']) {
            if($form->isValid($qurStr)) {
                $this->view->records = TRUE;
                $bucketData = $cardloadModel->getDisdursementStatus($page, $qurStr, $paginate = NULL);
                $paginator = $cardloadModel->paginateByArray($bucketData, $page, $paginate = NULL);
                $this->view->paginator = $paginator;
            }
        }
        $this->view->cardloadModel = $cardloadModel;
        $this->view->form = $form;
        $this->view->formData = $qurStr;
        $this->records = TRUE;
        
    }

    /* exportfeereportAction() is responsible to create the csv file on fly with fee data of agents
     * and let user download that file.
     */

    public function exportdisbursementstatusreportAction() {
                ini_set('memory_limit','400M');
        ini_set('max_execution_time',0);

        $qurStr['disbursement_number'] = $this->_getParam('disbursement_number');
        $qurStr['payment_status'] = explode(",",$this->_getParam('payment_status'));
        $qurStr['aadhar_no'] = $this->_getParam('aadhar_no');
        $qurStr['account_number'] = $this->_getParam('account_number');
        $qurStr['tp_name'] = $this->_getParam('tp_name');
        $qurStr['payment_status'] = array_filter($qurStr['payment_status']);
        $cardloadModel = new Corp_Boi_Cardload();
        $bucketData = $cardloadModel->getDisdursementStatus($page, $qurStr, $paginate = NULL);
       
        if(count($bucketData)) {
            
                    
                $reportData = array();
                $i=0;
                foreach($bucketData as $item) :
                    $note = $cardloadModel->getNote($item['id']);
                    $reportData[$i]['disbursement_number']= $item['disbursement_number'];
                    $reportData[$i]['date_updated']= $item['date_updated'];
                    $reportData[$i]['date_create'] = $item['date_create'];
                    $reportData[$i]['account_number'] = $item['account_number'];
                    $reportData[$i]['aadhar_no'] = $item['aadhar_no'];
                    $reportData[$i]['amount'] = $item['amount'];
                    $reportData[$i]['debit_mandate_amount'] = $item['debit_mandate_amount'];
                    $reportData[$i]['bucket']=$item['bucket'];
                    $reportData[$i]['payment_status']= $item['payment_status'];
                    $reportData[$i]['date_load']= $item['date_load'];
                    $reportData[$i]['tp_date_load'] = $item['date_load'];
                    $reportData[$i]['tp_amount'] = $item['amount'];
                    $reportData[$i]['note'] = $note;
                    $i++;
                endforeach;
                //echo "<pre>"; print_r($reportData); exit;
                $columns = array(
                    '0'=>'Disbursement Number',
                    '1'=>'Date of Instruction',
                    '2'=>'Date of upload',
                    '3'=>'Account Number',
                    '4'=>'Aadhaar No',
                    '5'=>'Disbursement Amount',
                    '6'=>'Debit Mandate Amount',
                    '7'=>'Bucket',
                    '8'=>'Payment Status',
                    '9'=>'Student Account Credit Date',
                    '10'=>'TP Account Credit Date',
                    '11'=>'Auto Debit Amount',
                    '12'=>'Note',

                );
                
                $objCSV = new CSV();
                try {
                    $resp = $objCSV->export($reportData, $columns, 'BOI_NSDC_DISBURSEMENT_STATUS_REPORT');
                    exit;
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                    $this->_redirect($this->formatURL("/corp_boi_index/disbursementstatusreport?disbursement_number=".$qurStr['disbursement_number']."&payment_status=".implode(",",$qurStr['payment_status'])."&account_number=".$qurStr['account_number']."&aadhar_no=".$qurStr['aadhar_no']."&tp_name=".$qurStr['tp_name']));
                }
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Invalid Data'));
            $this->_redirect($this->formatURL("/corp_boi_index/disbursementstatusreport?disbursement_number=".$qurStr['disbursement_number']."&payment_status=".implode(",",$qurStr['payment_status'])."&account_number=".$qurStr['account_number']."&aadhar_no=".$qurStr['aadhar_no']."&tp_name=".$qurStr['tp_name']));
        }
    }
    
    public function disbursementcardloadreportAction() {
         $this->title = 'Disbursement Wallet Card Load Status';
        // Get our form and validate it
        $form = new Corp_Boi_DisdursementCardLoadReport(array('action' => $this->formatURL('/corp_boi_index/disbursementcardloadreport'),
            'method' => 'POST',
        ));
        $cardloadModel = new Corp_Boi_Cardload();
        $qurStr['disbursement_number'] = $this->_getParam('disbursement_number');
        $qurStr['payment_status'] = $this->_getParam('payment_status');
        $qurStr['aadhar_no'] = $this->_getParam('aadhar_no');
        $qurStr['account_number'] = $this->_getParam('account_number');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['submit'] = $this->_getParam('submit');
        $page = $this->_getParam('page');
        if($qurStr['submit']) {
            if($form->isValid($qurStr)) {
                $this->view->records = TRUE;
                if($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {

                    $qurStr['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                    $qurStr['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                }
                $bucketData = $cardloadModel->getDisdursementCardLoad($page, $qurStr, $paginate = NULL);
                $paginator = $cardloadModel->paginateByArray($bucketData, $page, $paginate = NULL);
                $this->view->paginator = $paginator;
            }
        }
        $this->view->form = $form;
        $this->view->formData = $qurStr;
        $this->records = TRUE;
        
    }

    /* exportfeereportAction() is responsible to create the csv file on fly with fee data of agents
     * and let user download that file.
     */

    public function exportdisbursementcardloadreportAction() {
        ini_set('memory_limit','400M');
        ini_set('max_execution_time',0);                
        $qurStr['disbursement_number'] = $this->_getParam('disbursement_number');
        $qurStr['aadhar_no'] = $this->_getParam('aadhar_no');
        $qurStr['account_number'] = $this->_getParam('account_number');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['to_date'] = $this->_getParam('to_date');
        if($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
            $qurStr['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
            $qurStr['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
        }
        $cardloadModel = new Corp_Boi_Cardload();
        $bucketData = $cardloadModel->getDisdursementCardLoad($page, $qurStr, $paginate = NULL);
       
        if(count($bucketData)) {
            
                    
                $reportData = array();
                $i=0;
                foreach($bucketData as $item) :
                    $reportData[$i]['txn_identifier_type']= $item['txn_identifier_type'];
                    $reportData[$i]['member_id']= $item['member_id'];
                    $reportData[$i]['card_number'] = Util::maskCard($item['card_number'],4);
                    $reportData[$i]['amount'] = $item['amount'];
                    $reportData[$i]['currency'] = $item['currency'];
                    $reportData[$i]['narration'] = $item['narration'];
                    $reportData[$i]['wallet_code']=$item['wallet_code'];
                    $reportData[$i]['account_number']= Util::maskCard($item['account_number'],4);
                    $reportData[$i]['mode']= $item['mode'];
                    $reportData[$i]['txn_code']= $item['txn_code'];
                    $reportData[$i]['load_status'] = $item['load_status'];
                    $reportData[$i]['load_failed_reason'] = $item['load_failed_reason'];
                    $reportData[$i]['date_load'] = $item['date_load'];
                    $reportData[$i]['load_batch_name'] = $item['load_batch_name'];
                    $reportData[$i]['cust_id'] = $item['cust_id'];
                    $reportData[$i]['aadhar_no'] = $item['aadhar_no'];
                    $reportData[$i]['disbursement_number'] = $item['disbursement_number'];
                    $i++;
                endforeach;
                //echo "<pre>"; print_r($reportData); exit;
                $columns = array(
                    '0'=>'Txn Indentifier type',
                    '1'=>'Member Id',
                    '2'=>'Card Number',
                    '3'=>'Amount',
                    '4'=>'Currency',
                    '5'=>'Narration',
                    '6'=>'Wallet Code',
                    '7'=>'Customer Account number',
                    '8'=>'Mode',
                    '9'=>'Txn Reference No.',
                    '10'=>'File Status',
                    '11'=>'Failed Reason',
                    '12'=>'Txn/load Date',
                    '13'=>'File Name',
                    '14'=>'Cust ID',
                    '15'=>'Aadhaar Number',
                    '16'=>'Disbursement Number'

                );
                
                $objCSV = new CSV();
                try {
                    $resp = $objCSV->export($reportData, $columns, 'BOI_NSDC_DISBURSEMENT_CARDLOAD_REPORT');
                    exit;
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                    $this->_redirect($this->formatURL("/corp_boi_index/disbursementcardloadreport?disbursement_number=".$qurStr['disbursement_number']."&account_number=".$qurStr['account_number']."&aadhar_no=".$qurStr['aadhar_no']."&from_date=".$qurStr['from_date']."&to_date=".$qurStr['to_date']."&submit=true"));
                }
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Invalid Data'));
            $this->_redirect($this->formatURL("/corp_boi_index/disbursementcardloadreport?disbursement_number=".$qurStr['disbursement_number']."&account_number=".$qurStr['account_number']."&aadhar_no=".$qurStr['aadhar_no']."&from_date=".$qurStr['from_date']."&to_date=".$qurStr['to_date']."&submit=true"));
        }
    }
    

}
