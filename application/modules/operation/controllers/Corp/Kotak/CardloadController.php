<?php

/**
 * HIC Default entry point
 *
 * @author Vikram
 */
class Corp_Kotak_CardloadController extends App_Operation_Controller {

    //put your code here


    public function init() {
        parent::init();
    }

    public function indexAction() {
        
    }

    public function cardloadAction() {
        
        $this->title = "Upload of Card Load";
        $page = $this->_getParam('page');
        $form = new Corp_Kotak_CardloadForm();
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $cardloadModel = new Corp_Kotak_Cardload();
        $this->view->records = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
//        $cardholdersModel->ratCorpECSRegn();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->receive();
                $name = $upload->getFileName('doc_path');

                $batchName = $upload->getFileName('doc_path', $path = FALSE) ;
                $checkFile = $cardloadModel->checkBatchFilename($batchName);
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
                        if (!empty($line) && strpos($line,KOTAK_AMUL_WALLET_END_OF_FILE) === false) {
                            $delimiter = KOTAK_AMUL_WALLET_UPLOAD_DELIMITER;
                            $dataArr = str_getcsv($line, $delimiter);
                            $dataArr['product_id'] = $formData['product_id'];
                            $dataArr['count'] = $formData['count'];
                            $dataArr['value'] = $formData['value'];
                            $consolidateArr[] = $dataArr;
                            $arrLength = Util::getArrayLength($dataArr);
                            if (!empty($dataArr)) {
                                if ($arrLength != KOTAK_AMUL_WALLET_UPLOAD_COLUMNS)
                                    $this->view->incorrectData = TRUE;
                                try {
                                    // direct insert into rat_corp_cardholders

                                    if ($dataArr[KOTAK_AMUL_WALLET_MANDATORY_FIELD_INDEX] == '') {
                                        $status = STATUS_INCOMPLETE;
                                    } else {
                                        $status = STATUS_TEMP;
                                    }
                                    $cnt++;
                                    $val +=  $dataArr[2];
                                    
                                    $cardloadModel->insertLoadrequestBatch($dataArr, $batchName, $status);
                                } catch (Exception $e) {
                                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                }

                                
                            } else {
                                $this->_helper->FlashMessenger(array('msg-error' => 'Data format is not correct'));
                            }
                        }
                    }
                    //echo $val . ' : '.  (int) Util::filterAmount($formData['value']). ' : '.Util::filterAmount($formData['value']).' : '.$formData['value'].'**<br />';exit;
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
                    elseif(!Util::compareAmount($val,Util::filterAmount($formData['value'])))
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
                        $this->view->paginator = $cardloadModel->showPendingCardloadDetails($batchName);
                    }
                    $this->view->batch_name = $batchName;

                    fclose($fp);
                }
                
                   
                
            }
        }

        if ($submit != '') {

            try {

                
                $cardloadModel->bulkAddCardload($formData['reqid'], $formData['batch']);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Corporate Wallet details have been updated in our records',
                        )
                );
                $this->_redirect($this->formatURL('/corp_kotak_cardload/cardload/'));
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
        $form = new Corp_Kotak_ExpWalletStatusForm(array('action' => $this->formatURL('/corp_kotak_cardload/walletstatus/'),
                                              'method' => 'POST',
                                       ));  
        $request = $this->getRequest();  
        $sub = $this->_getParam('sub');
        $qurStr['purse_master_id'] = $this->_getParam('purse_master_id');
        $qurStr['batch_name'] = $this->_getParam('batch_name');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['purse'] = $this->_getParam('purse_master_id');
        $qurStr['batch'] = $this->_getParam('batch_name');
        if($sub!=''){    
                $qurData['batch_name'] = $qurStr['batch_name'];  
              if($form->isValid($qurStr)){ 
                 $this->view->title = 'Wallet Details';
                 $this->view->batch_name = $qurData['batch_name'];
                 $page = $this->_getParam('page');
                 $cardloadModel = new Corp_Kotak_Cardload();
                 $loadreq = $cardloadModel->getLoadRequests($qurStr);
                 $paginator = $cardloadModel->paginateByArray($loadreq, $page, $paginate = NULL);
                 $form->getElement('purse_master_id')->setValue($qurStr['purse_master_id']);
                 $form->getElement('purse')->setValue($qurStr['purse_master_id']);
                 $form->getElement('batch')->setValue($qurStr['batch_name']);
                 $form->getElement('product_id')->setValue($qurStr['product_id']);
                 
                 $this->view->paginator=$paginator;
                 $this->view->sub = $sub;
                  $form->populate($qurStr);
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
       
        //$form = new Corp_Ratnakar_WalletStatusForm(array('action' => $this->formatURL('/corp_kotak_cardload/exportwalletstatus/'),
        $form = new Corp_Kotak_ExpWalletStatusForm(array('action' => $this->formatURL('/corp_kotak_cardload/exportwalletstatus/'),
                                              'method' => 'POST',
                                       ));  
        
         if($qurStr['purse_master_id']!=''){    
//              if($form->isValid($qurStr)){ 
               
                 $qurData['batch_name'] =  $qurStr['batch_name'];       
                 $qurData['purse_master_id'] =  $qurStr['purse_master_id'];       
                 $cardloadModel = new Corp_Kotak_Cardload();
                 $exportData = $cardloadModel->exportAmulLoadRequests($qurData);
                 
                 $columns = array(
                    'Product Name',
                     'Txn Identifier Type',
                    'Card Number',
                    'Member Id',
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
                                         $this->_redirect($this->formatURL('/corp_kotak_cardload/exportwalletstatus?batch_name='.$qurStr['batch_name'].'&sub=1&purse_master_id='.$qurStr['purse_master_id'])); 
                                       }
//                 
//               } else {
//                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
//                         $this->_redirect($this->formatURL('/corp_kotak_cardload/walletstatus?batch_name='.$qurStr['batch_name'].'&sub=1')); 
//                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Batch Name!') );
                    $this->_redirect($this->formatURL('/corp_kotak_cardload/exportwalletstatus?batch_name='.$qurStr['batch_name'].'&sub=1&purse_master_id='.$qurStr['purse_master_id'])); 
                 }    
       }
       
       
       
       public function corporateloadAction() {
        $this->title = "Upload of Corporate Wallet";
        $page = $this->_getParam('page');
        $form = new Corp_Kotak_CorporateLoadForm();
        $formData = $this->_request->getPost();
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $cardloadModel = new Corp_Kotak_Cardload();
        $this->view->records = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
  
        if ($this->getRequest()->isPost()) {
            if ($submit == '') 
            {
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
                $batchName = $upload->getFileName('doc_path', $path = FALSE) ;
                $checkFile = $cardloadModel->checkBatchFilename($batchName,$formData['product_id']);
                
                //check file extension...
                $upload->addValidator('Extension', false, array(FILE_TYPE_CSV, 'case' => false));
                
                $file_ext = Util::getFileExtension($name);
                if(strtolower($file_ext) != strtolower(FILE_TYPE_CSV))
                {
                   $this->_helper->FlashMessenger( array('msg-error' => 'Invalid file uploaded. Allowed Format is csv only.',) );
                   $this->_redirect($this->formatURL('/corp_kotak_cardload/corporateload/'));
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
                        $this->view->paginator = $cardloadModel->showPendingCardloadDetails($batchName, $page, $paginate = NULL);
                        $this->view->rejectpaginator = $cardloadModel->showFailedPendingCardloadDetails($batchName, $page, $paginate = NULL);
                    }
                    $this->view->batch_name = $batchName;

                    fclose($fp);
                }
                
                    
                
            }
        }

        if ($submit != '') {


            try {

                $cardloadModel->bulkAddCardload($formData['reqid'], $formData['batch']);
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Corporate Wallet details have been updated in our records',
                        )
                );
                $this->_redirect($this->formatURL('/corp_kotak_cardload/corporateload/'));
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
    
    
    
    public function walletstatusgprAction(){
       $this->title = 'Wallet Status Report';  
       // Get our form and validate it
        $form = new Corp_Kotak_WalletStatusGPRForm(array('action' => $this->formatURL('/corp_kotak_cardload/walletstatusgpr/'),
                                              'method' => 'POST',
                                       ));  
        $request = $this->getRequest();  
        $sub = $this->_getParam('sub');
        $qurStr['purse_master_id'] = $this->_getParam('purse_master_id');
        $qurStr['batch_name'] = $this->_getParam('batch_name');
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['purse'] = $this->_getParam('purse_master_id');
        $qurStr['batch'] = $this->_getParam('batch_name');
        if($sub!=''){    
                $qurData['batch_name'] = $qurStr['batch_name'];  
              if($form->isValid($qurStr)){ 
                 $this->view->title = 'Wallet Details';
                 $this->view->batch_name = $qurData['batch_name'];
                 $page = $this->_getParam('page');
                 $cardloadModel = new Corp_Kotak_Cardload();
                 $loadreq = $cardloadModel->getLoadRequests($qurStr);
                 $paginator = $cardloadModel->paginateByArray($loadreq, $page, $paginate = NULL);
                 $form->getElement('purse_master_id')->setValue($qurStr['purse_master_id']);
                 $form->getElement('purse')->setValue($qurStr['purse_master_id']);
                 $form->getElement('batch')->setValue($qurStr['batch_name']);
                 $form->getElement('product_id')->setValue($qurStr['product_id']);
                 
                 $this->view->paginator=$paginator;
                 $this->view->sub = $sub;
                  $form->populate($qurStr);
               }   
              
          }
            
            $form->populate($qurStr);
            $this->view->form = $form;
            $this->view->formData = $qurStr; 
    }
         
   
    /* exportremittancereportAction function is responsible to create the csv file on fly with agent load/reload/remittance txns report data
     * and let user download that file.
     */
    
     public function exportwalletstatusgprAction(){
        
        $qurStr['batch_name'] = $this->_getParam('batch_name');
        $qurStr['purse_master_id'] = $this->_getParam('purse_master_id');
       
        $form = new Corp_Kotak_WalletStatusGPRForm(array('action' => $this->formatURL('/corp_kotak_cardload/exportwalletstatusgpr/'),
                                              'method' => 'POST',
                                       ));  
        
         if($qurStr['purse_master_id']!=''){    
//              if($form->isValid($qurStr)){ 
               
                 $qurData['batch_name'] =  $qurStr['batch_name'];       
                 $qurData['purse_master_id'] =  $qurStr['purse_master_id'];       
                 $cardloadModel = new Corp_Kotak_Cardload();
                 $exportData = $cardloadModel->exportLoadRequests($qurData);
                 
                 $columns = array(
                    'Product Name',
                     'Txn Identifier Type',
                    'Card Number',
                    'Member Id',
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
                                         $this->_redirect($this->formatURL('/corp_kotak_cardload/exportwalletstatusgpr?batch_name='.$qurStr['batch_name'].'&sub=1&purse_master_id='.$qurStr['purse_master_id'])); 
                                       }
//                 
//               } else {
//                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid duration!') );
//                         $this->_redirect($this->formatURL('/corp_kotak_cardload/walletstatus?batch_name='.$qurStr['batch_name'].'&sub=1')); 
//                      }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Batch Name!') );
                    $this->_redirect($this->formatURL('/corp_kotak_cardload/exportwalletstatusgpr?batch_name='.$qurStr['batch_name'].'&sub=1&purse_master_id='.$qurStr['purse_master_id'])); 
                 }    
       }
    
   
}
