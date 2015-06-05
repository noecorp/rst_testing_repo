<?php

/**
 * HIC Default entry point
 *
 * @author Vikram
 */
class Corp_Ratnakar_CardloadController extends App_Operation_Controller {

    //put your code here


    public function init() {
        parent::init();
    }

    public function indexAction() {
        
    }

    public function corporateloadAction() {
        $this->title = "Upload of Corporate Wallet";
        $page = $this->_getParam('page');
        $form = new Corp_Ratnakar_CardloadForm();
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $cardloadModel = new Corp_Ratnakar_Cardload();
        $this->view->records = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
//        $cardholdersModel->ratCorpECSRegn();
        if ($this->getRequest()->isPost()) {
            if ($submit == '') 
            {
                $idDocFile = isset($_FILES['doc_path']['name'])?$_FILES['doc_path']['name']:'';
                if(empty($idDocFile))
                {
                   $this->_helper->FlashMessenger( array('msg-error' => 'Invalid file uploaded.',) );
                   //$this->_redirect($this->formatURL('/corp_ratnakar_cardload/corporateload/'));
                }
            }
            if ($form->isValid($this->getRequest()->getPost())) {
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->receive();
                $name = $upload->getFileName('doc_path');

                $batchName = $upload->getFileName('doc_path', $path = FALSE) ;
                $checkFile = $cardloadModel->checkBatchFilename($batchName);
                
                //check file extension...
                $upload->addValidator('Extension', false, array(FILE_TYPE_CSV, 'case' => false));
                
                $file_ext = Util::getFileExtension($name);
                if(strtolower($file_ext) != strtolower(FILE_TYPE_CSV))
                {
                   $this->_helper->FlashMessenger( array('msg-error' => 'Invalid file uploaded. Allowed Format is csv only.',) );
                   $this->_redirect($this->formatURL('/corp_ratnakar_cardload/corporateload/'));
                }
                
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
                    //elseif($val - Util::filterAmount($formData['value']) != 0)
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
                        $this->_helper->FlashMessenger(
                            array(
                                'msg-success' => "File uploaded successfully",
                            )
                        );
                        $this->view->records = TRUE;
                        $this->view->paginator = $cardloadModel->showPendingCardloadDetails($batchName, $page, $paginate = NULL,$force = TRUE);
                        $this->view->rejectpaginator = $cardloadModel->showPendingFaildCardloadDetails($batchName, $page, $paginate = NULL,$force = TRUE);
                    }
                    $this->view->batch_name = $batchName;

                    fclose($fp);
                }
                
                    
                
            }
        }

        if ($submit != '') {


            try {

                $cardloadModel->bulkAddCardload($formData['reqid'], $formData['batch'],CHANNEL_OPS);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Corporate Wallet details have been updated in our records',
                        )
                );
                $this->_redirect($this->formatURL('/corp_ratnakar_cardload/corporateload/'));
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

    public function searchcardholderAction() {
        $this->title = 'Search Cardholders';


        $data['searchCriteria'] = $this->_getParam('searchCriteria');
        $data['keyword'] = $this->_getParam('keyword');
        $data['sub'] = $this->_getParam('sub');
        $cardholdersModel = new Corp_Ratnakar_Cardholders();
        $page = $this->_getParam('page');
        $agentModel = new Agents();
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
    }

    public function walletstatusAction(){
       $this->title = 'Wallet Status Report';  
       // Get our form and validate it
        $form = new Corp_Ratnakar_ExpWalletStatusForm(array('action' => $this->formatURL('/corp_ratnakar_cardload/walletstatus/'),
                                              'method' => 'POST',
                                       ));  
        $request = $this->getRequest();  
        $sub = $this->_getParam('sub');
        $qurStr['purse_master_id'] = $this->_getParam('purse_master_id');
        $qurStr['batch_name'] = $this->_getParam('batch_name');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['multi_status'] = "'".STATUS_LOADED."', '".STATUS_FAILED."', '".STATUS_PENDING."'";
         if($sub!=''){    
                $qurData['batch_name'] = $qurStr['batch_name'];  
              if($form->isValid($qurStr)){ 
                 $this->view->title = 'Wallet Details';
                 $this->view->batch_name = $qurData['batch_name'];
                 $page = $this->_getParam('page');
                 $cardloadModel = new Corp_Ratnakar_Cardload();
                 $loadreq = $cardloadModel->getLoadRequests($qurStr);
                 $paginator = $cardloadModel->paginateByArray($loadreq, $page, $paginate = NULL);
                 $form->getElement('purse_master_id')->setValue($qurStr['purse_master_id']);
                 $form->getElement('purse')->setValue($qurStr['purse_master_id']);
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
        
        $qurStr['batch_name'] = $this->_getParam('batch_name');
        $qurStr['purse_master_id'] = $this->_getParam('purse_master_id');
        $qurStr['product_id'] = $this->_getParam('product_id');
        
        $form = new Corp_Ratnakar_WalletStatusForm(array('action' => $this->formatURL('/corp_ratnakar_cardload/exportwalletstatus/'),
                                              'method' => 'POST',
                                       ));  
        
         if($qurStr['purse_master_id']!=''){    
//              if($form->isValid($qurStr)){ 
               
                 $qurData['batch_name'] =  $qurStr['batch_name'];       
                 $qurData['purse_master_id'] =  $qurStr['purse_master_id'];
                 $qurData['product_id'] =  $qurStr['product_id']; 
                 $qurData['multi_status'] = "'".STATUS_LOADED."', '".STATUS_FAILED."', '".STATUS_PENDING."'";
                 $cardloadModel = new Corp_Ratnakar_Cardload();
                 $exportData = $cardloadModel->exportRatLoadRequests($qurData);
                 
                 $columns = array(
                    'Product',
                    'Txn Identifier Type',
                    'Card Number',
                    'Medi Assist Id',
                    'Amount',
                    'Amount Cutoff',
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
                                         $this->_redirect($this->formatURL('/corp_ratnakar_cardload/walletstatus?batch_name='.$qurStr['batch_name'].'&sub=1')); 
                                       }
//                 
//               } else {
//                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
//                         $this->_redirect($this->formatURL('/corp_ratnakar_cardload/walletstatus?batch_name='.$qurStr['batch_name'].'&sub=1')); 
//                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Batch Name!') );
                    $this->_redirect($this->formatURL('/corp_ratnakar_cardload/walletstatus?batch_name='.$qurStr['batch_name'].'&sub=1')); 
                 }    
       }
   
    
    public function settlementresponseAction() {
        $this->title = "Upload Settlement Response";
        $form = new Corp_Ratnakar_ResponseSettlementUploadForm();
        $this->view->incorrectData = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
        $responseSettlementModel = new Corp_Ratnakar_SettlementResponse();
        $successInsertedRecords = 0;
        $duplicateDataArr = array();
        $failedInsertedRecords = array();
        $notranCode = array();
        $totalrecords = 0;
        $errorMsg = array();
        $this->view->records = FALSE;

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->receive();
                $name = $upload->getFileName('doc_path');

                $phTransName = $upload->getFileName('doc_path', $path = FALSE) ; 
                
                //Add Validators for uploaded file's extesion , mime type and size
               $upload->addValidator('Extension', false, array('csv'));
               
               //check file extension...
                $upload->addValidator('Extension', false, array(FILE_TYPE_CSV, 'case' => false));
                
                $file_ext = Util::getFileExtension($phTransName);
                if(strtolower($file_ext) != strtolower(FILE_TYPE_CSV))
                {
                   $this->_helper->FlashMessenger( array('msg-error' => 'Invalid file uploaded. Allowed Format is .csv only.',));
                   $this->_redirect($this->formatURL('/remit_ratnakar_remitter/uploadresponsepaymenthistory/'));
                }else{
                    /*
                     * Getting file values and match with existing values
                     */
                $datafiles = array();
                $datafiles['label'] = 'Response of Settlement'; 
                $datafiles['file_name'] = $phTransName;
                $datafiles['ops_id'] = $user->id;
                $datafiles['status'] = STATUS_ACTIVE;
                $datafiles['date_created'] = new Zend_Db_Expr('NOW()');
                
                 //read and save contents of csv                
                $fp = fopen($name, 'r');
                $invalidrows = 0;
                while (!feof($fp)) {
                    //$dataArr = fgetcsv($fp,'200',';');
                    $line = fgets($fp);
                   // if($i > 2){
                      if (!empty($line)) {
                        $delimiter = SETTLEMENT_RESPONSE_IMPORT_FILE_UPLOAD_DELIMITER;
                        $dataArr = str_getcsv($line, $delimiter);
                        $arrLength = Util::getArrayLength($dataArr);
                        if (!empty($dataArr)) {
                            if ($arrLength == SETTLEMENT_RESPONSE_UPLOAD_COLUMNS){    
                               // Define the variables which will store in database 
                                 $tran_code = $dataArr[2];
                               
                                 /*
                                  * Count total records
                                  */
                                 if(intval($dataArr[0]) > 0){
                                     $totalrecords +=1;
                                     
                                 }
                               /*
                                * checkTransactionExist : checking current record is stored or not
                                */
                                 try {
                                       if( ($tran_code != '') && (intval($dataArr[0]) > 0) ){
                                           
                                            $distchk = $responseSettlementModel->checkresponseExist($tran_code);
                                            // define array for insertation
                                            if(!$distchk){
                                                $duplicateDataArr[]= $tran_code;
                                               }else{
                                                   // Insert date into InsertData Array;
                                               $insertchk = $responseSettlementModel->insertResponse($dataArr,$datafiles);
                                               if($insertchk){
                                                   // Successfully Inserted records
                                                  
                                                   $successInsertedRecords += 1;
                                               }else{
                                                   // Failed Inserted records
                                                   $failedInsertedRecords[]= $dataArr;
                                               }
                                                //$insertDataArr[]= $dataArr;
                                               }
                                            }else{
                                            
                                            if( ($tran_code == '') && (intval($dataArr[0]) > 0) ){
                                                $notranCode[] = $dataArr;
                                            }else{
                                                $invalidrows += 1; 
                                            }
                                        }

                                     } catch (Exception $e) {
                                             App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                             throw new Exception($e->getMessage());
                                     }
                                }else{
                            $errorMsg[1] = 'Uploaded file have invalid number of columns.';
                        } 
                        }else{
                          //  $errorMsg[0] = 'Uploaded file not have some records.';
                        }
                      }else{
                         // $errorMsg[2] = 'Uploaded file have missed some rows.';
                      }
                   // } 
                 //$i +=1;   
                }
              
                $this->view->records = TRUE;
                $this->view->totalrecords = $totalrecords;
                $this->view->file_name = $phTransName;
                $this->view->successInsertedRecords = $successInsertedRecords;
                $this->view->duplicaterecords = $duplicateDataArr;
                $this->view->failedInsertedRecords = $failedInsertedRecords;
                $this->view->invalidrows = $invalidrows;
                $this->view->notranCode = $notranCode;
                $this->view->errorMsg = $errorMsg;
                
                fclose($fp);
                }
               //  exit();
                
            }else{
                    
                   $this->_helper->FlashMessenger( array('msg-error' => 'Invalid file uploaded. Please upload proper file.',));
                   $this->_redirect($this->formatURL('/corp_ratnakar_cardload/settlementresponse/'));
                }
        }

       $this->view->form = $form;
    }   

    /*
     * Unsettlement requests to generate the Unsettlement details text file
     */

    public function unsettlementrequestsAction() {
        $this->title = "Unsettlement Instruction Batches";
        $objLoad = new Corp_Ratnakar_Cardload();
        $session = new Zend_Session_Namespace('App.Operation.Controller');
        $requests = $this->_getAllParams();
        $page = $this->_getParam('page');
        $form = new Corp_Ratnakar_UnsettlementRequestSearchForm();
        $formData = $this->_request->getPost();
        $itemPerPageFromPost = isset($formData['items_per_page']) ? $formData['items_per_page'] : 0;
        if ($itemPerPageFromPost < 1)
            $itemPerPage = isset($requests['items_per_page']) ? $requests['items_per_page'] : '';
        else {
            $itemPerPage = $itemPerPageFromPost;
        }
        $this->view->records = FALSE;
        $finalArr = array();

        if ($form->isValid($requests)) {
            if ($session->items_per_page != $itemPerPage) {
                $page = 1;
            }

            $session->items_per_page = $itemPerPage;
            //$page=1;
            $this->view->records = TRUE;
            $fromDate = explode(' ', Util::returnDateFormatted($requests['from_date'], "d-m-Y", "Y-m-d", "-"));
            $toDate = explode(' ', Util::returnDateFormatted($requests['to_date'], "d-m-Y", "Y-m-d", "-"));

            $params = array('from_date' => $fromDate[0],
                'to_date' => $toDate[0]);
            $batchFilesCountArr = $objLoad->getUnsettlementBatchFilesCountArray($params);
            if(!empty($batchFilesCountArr)){
           
            
                for ($i = 0; $i < count($batchFilesCountArr); $i++) {

                   // $finalArr[] = array_merge($batchFilesArray[$i], $batchFilesCountArr[$i]);
                    $finalArr[] = $batchFilesCountArr[$i];
                }
            }
            $paginator = $objLoad->paginateByArray($finalArr, $page, $paginate = NULL);

            $this->view->paginator = $paginator;

            $form->populate($formData);
            $this->view->items_per_page = $session->items_per_page;
            $this->view->from = $requests['from_date'];
            $this->view->to = $requests['to_date'];
        }


        $this->view->form = $form;
    }
    
}