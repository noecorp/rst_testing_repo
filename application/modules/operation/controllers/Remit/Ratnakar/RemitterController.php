<?php

/**
 * That RemitterController is responsible for all remit operations at ops portal.
 */

class Remit_Ratnakar_RemitterController extends Remit_IndexController
{

    public function init()
    {
        parent::init();
    }    
    
    /* indexAction is landing page for remitter different operations       
    */
    
    public function indexAction()
    {
    }   
    
    
    public function searchAction(){
        
         $this->title = 'Search Remitter';
         $data['searchCriteria'] = $this->_getParam('searchCriteria');
         $data['keyword'] = $this->_getParam('keyword');
         $data['sub'] = $this->_getParam('sub');
         $page = $this->_getParam('page');
         $remitterModel = new Remit_Ratnakar_Remitter();
         $form = new Remit_Ratnakar_RemitterSearchForm(array('action' => $this->formatURL('/remit_ratnakar_remitter/search'),
                                             'method' => 'POST',
                                      ));
        
        if ($data['sub'] != '') {
           // if($form->isValid($this->getRequest()->getPost())){
                $data['searchCriteria'] = $this->_getParam('searchCriteria');
                $data['keyword'] = $this->_getParam('keyword');
                 
                 $this->view->paginator = $remitterModel->searchRemitter($data,$page); 
                 
                 $this->view->sub = $data['sub'];
                 $form->populate($data);  
            
          //}
        }
        
        
        $this->view->controllerName = Zend_Registry::get('controllerName');
        $this->view->form = $form;
    }

    
      public function beneficiaryAction(){
        
         $this->title = "Remitter's Beneficiaries";
         $rid = ($this->_getParam('rid') > 0) ? $this->_getParam('rid'): 0;
         if($rid>0){
            $remitterModel = new Remit_Ratnakar_Remitter();
            $remitterInfo = $remitterModel->getRemitterById($rid);
            //$remitterTransaction = $remitterModel->getRemitterTransactionByID($rid);
            $this->view->paginator = $remitterModel->getRemitterbeneficiaries($rid,$this->_getPage()); 
            $this->view->remitterInfo = $remitterInfo; 
            $this->view->remitterTransaction = $remitterTransaction; 
         }
         $this->view->controllerName = Zend_Registry::get('controllerName');
    }
  

       public function holdtransactionsAction(){
         ini_set('max_execution_time', 120);
         $this->title = "Ratnakar Remitter's Hold Transactions";
         $data['sub'] = $this->_getParam('sub');
         $data['mobile'] = $this->_getParam('mobile');
         $this->view->sub = FALSE;
         $remitterModel = new Remit_Ratnakar_Remitter();
//         $form = new Remit_Kotak_RemitterHoldTransactionForm(array('action' => $this->formatURL('/remit_kotak_remitter/holdtransactions'),
//                                             'method' => 'POST',
//                                      ));
        $page = $this->_getParam('page');
        //if ($data['sub'] != '') {
//            if($form->isValid($this->getRequest()->getPost())){
                 $data['mobile'] = $this->_getParam('mobile');
                 $data['status'] = STATUS_HOLD; 
                 $ref = $remitterModel->getRemitterHoldTransactions($page); 
                 $this->view->paginator = $ref;
                    
                 $this->view->sub = $data['sub'];
                 //$form->populate(array('mobile'=> $data['mobile'],'sub'=> $data['sub']));  
            
//           }
        //}
        
        //$this->view->form = $form;
    }
    
    
    public function processtransactionAction(){
      $this->title = "Remitter's Hold Transactions"; 
      $traceNumber = $this->_getParam('txn_code');
      $newTraceNumber = Util::generateRandomNumber(10); //rand('1111111111','9999999999');
      $form = new Remit_Ratnakar_ProcessForm();
      $remittancerequest = new Remit_Ratnakar_Remittancerequest();
      $remittancestatuslog = new Remit_Ratnakar_Remittancestatuslog();
      $baseTxn = new BaseTxn();
      $bank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
      $flg = FALSE;
      $backLink = 'mobile='.$this->_getParam('mobile').'&sub=1';
      $type = $this->_getParam('type');
      $user = Zend_Auth::getInstance()->getIdentity();
      $m = new App\Messaging\Remit\Ratnakar\Agent();
      //API call
      $api = new App_Api_Ratnakar_Remit_Transaction();
                try {
      if($type == STATUS_SUCCESS) {
                $remittanceDetailsArr = $remittancerequest->getRemitterRequestsInfoByTxnCode($traceNumber);
                $remittanceDetails = $remittancerequest->getRemitterRequestsInfo($remittanceDetailsArr['id']);          
                $paramsBaseTxn = array(
                    'remit_request_id' => $remittanceDetails['id'],
                    'product_id' => $remittanceDetails['product_id'],
                    'amount' => $remittanceDetails['amount'],
                    'bank_unicode' => $bank->bank->unicode,
                    'agent_id' => $remittanceDetails['agent_id']
                );                
                $smsData = array(
                    'amount' => $remittanceDetails['amount'],
                    'nick_name' => $remittanceDetails['nick_name'],
                    'beneficiary_name' => $remittanceDetails['name'],
                    'remitter_name' => $remittanceDetails['r_name'],
                    'contact_email' => RATNAKAR_REMITTANCE_EMAIL,
                    'contact_number' => RATNAKAR_CALL_CENTRE_NUMBER,
                    'remitter_phone' => $remittanceDetails['mobile'],
                    'product_name' => RATNAKAR_SHMART_REMIT
                );
                    

                    $paramsBaseTxn['beneficiary_id'] = $remittanceDetails['beneficiary_id'];
                    $paramsBaseTxn['txn_code'] = $traceNumber;
                    $paramsBaseTxn['fee_amt'] = $remittanceDetails['fee'];
                    $paramsBaseTxn['service_tax'] = $remittanceDetails['service_tax'];
           
                    //echo '<pre>';print_r($smsData);
                    //echo '<pre>';print_r($paramsBaseTxn);exit;
                    $baseTxn->remitSuccess($paramsBaseTxn);
                    // Remit request table update Array
                    $updateStatusArr['is_complete'] = FLAG_YES;
                    $updateStatusArr['status'] = STATUS_SUCCESS;
                    $updateStatusArr['fund_holder'] = REMIT_FUND_HOLDER_BENEFICIARY;
                    $m->ratnakarNeftSuccessRemitter($smsData);
                    $flg = TRUE;
                    $datastatus['status_new'] = STATUS_SUCCESS;
                    
                    $datastatus['remittance_request_id'] = $remittanceDetails['id'];
                    $datastatus['status_old'] = $remittanceDetails['status'];
                    $datastatus['by_remitter_id'] = $remittanceDetails['remitter_id'];
                    $datastatus['by_ops_id'] = $user->id;
                    $datastatus['date_created'] = new Zend_Db_Expr('NOW()');
                    $resLog = $remittancestatuslog->addStatus($datastatus);
                
                
                    $updateStatusArr['cr_response'] = 'Transaction Marked as successful by Ops';
                    $updateStatusArr['final_response'] = 'Transaction Marked as successful by Ops';
                    $resUpdate = $remittancerequest->updateReq($remittanceDetails['id'],$updateStatusArr);                    
          
               $this->_helper->FlashMessenger(
                    array(
                          'msg-success' => 'Hold Transaction has been marked as successful.',
                    )
                );
               $this->_redirect($this->formatURL('/remit_ratnakar_remitter/holdtransactions?'.$backLink));    
               exit();
               
      } elseif($type == STATUS_FAILED) {
                $remittanceDetailsArr = $remittancerequest->getRemitterRequestsInfoByTxnCode($traceNumber);
                $remittanceDetails = $remittancerequest->getRemitterRequestsInfo($remittanceDetailsArr['id']);          
                $paramsBaseTxn = array(
                    'remit_request_id' => $remittanceDetails['id'],
                    'product_id' => $remittanceDetails['product_id'],
                    'amount' => $remittanceDetails['amount'],
                    'bank_unicode' => $bank->bank->unicode,
                    'agent_id' => $remittanceDetails['agent_id']
                );                               
                $smsData = array(
                    'amount' => $remittanceDetails['amount'],
                    'nick_name' => $remittanceDetails['nick_name'],
                    'beneficiary_name' => $remittanceDetails['name'],
                    'remitter_name' => $remittanceDetails['r_name'],
                    'contact_email' => RATNAKAR_REMITTANCE_EMAIL,
                    'contact_number' => RATNAKAR_CALL_CENTRE_NUMBER,
                    'remitter_phone' => $remittanceDetails['mobile'],
                    'product_name' => RATNAKAR_SHMART_REMIT
                );

                    $paramsBaseTxn['reversal_fee_amt'] = $remittanceDetails['fee'];
                    $paramsBaseTxn['reversal_service_tax'] = $remittanceDetails['service_tax'];
                    $baseTxn->remitFailure($paramsBaseTxn);
                    // Remit request table update Array
                    $updateStatusArr['is_complete'] = FLAG_NO;
                    $updateStatusArr['status'] = STATUS_FAILURE;
                    $updateStatusArr['fund_holder'] = REMIT_FUND_HOLDER_REMITTER;
                    $m->ratnakarNeftFailureRemitter($smsData);
                    $datastatus['status_new'] = STATUS_FAILURE;          
                    $datastatus['remittance_request_id'] = $remittanceDetails['id'];
                    $datastatus['status_old'] = $remittanceDetails['status'];
                    $datastatus['by_remitter_id'] = $remittanceDetails['remitter_id'];
                    $datastatus['by_ops_id'] = $user->id;
                    $datastatus['date_created'] = new Zend_Db_Expr('NOW()');
                    $resLog = $remittancestatuslog->addStatus($datastatus);
                    $updateStatusArr['cr_response'] = 'Transaction Marked as failed by Ops';
                    $updateStatusArr['final_response'] = 'Transaction Marked as failed by Ops';
                    $resUpdate = $remittancerequest->updateReq($remittanceDetails['id'],$updateStatusArr);                    
          
                    $this->_helper->FlashMessenger(
                        array(
                              'msg-success' => 'Hold Transaction has been marked as failed.',
                        )
                    );
                    $this->_redirect($this->formatURL('/remit_ratnakar_remitter/holdtransactions?'.$backLink));             
        }  
                } catch(Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $this->_helper->FlashMessenger(
                        array(
                              'msg-success' => 'Unable to process hold transaction',
                        )
                    );
                    $this->_redirect($this->formatURL('/remit_ratnakar_remitter/holdtransactions?'.$backLink));                                 
                    //echo '<pre>';print_r($e);exit;
                }
      //} else {
            $resp = $api->queryAccountAndValidate(array(
                                   'qbTraceNumber' => $traceNumber,
                                   'traceNumber' => $newTraceNumber
                                   ));


             if($resp === TRANSACTION_SUCCESSFUL) {
             $displayMsg = "Remittance request has been successfully processed.";
                //Success
               $status = STATUS_SUCCESS;
               } else {
                 //Failure  
               $status = STATUS_FAILURE;
                  $displayMsg = 'Failure: '.$api->getMessage();
               }


             $std = new stdClass();
             $std->response = $displayMsg;
             $std->cr_response = ($api->getAccountCreditRespCode() != '')? '('.$api->getAccountCreditRespCode().') '.$api->getAccountCreditRespMsg():'-';
             $std->final_response = ($api->getMessage(TRUE) != '')? $api->getMessage(TRUE):'-';
             $this->view->item = $std;
      //}

       $remittanceDetailsArr = $remittancerequest->getRemitterRequestsInfoByTxnCode($traceNumber);
       // Populate Hidden Values
       $form->getElement('remit_request_id')->setValue($remittanceDetailsArr['id']);
       $form->getElement('status')->setValue($status);
       $form->getElement('final_response')->setValue($std->final_response);
       $form->getElement('cr_response')->setValue($std->cr_response);
       
       if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $formData  = $this->_request->getPost();
          $updateStatusArr = array();
          $datastatus = array();
          $remittanceDetails = $remittancerequest->getRemitterRequestsInfo($formData['remit_request_id']);
        //Confirm on the basis of the status
          $paramsBaseTxn = array(
                    'remit_request_id' => $remittanceDetails['id'],
                    'product_id' => $remittanceDetails['product_id'],
                    'amount' => $remittanceDetails['amount'],
                    'bank_unicode' => $bank->bank->unicode,
                    'agent_id' => $remittanceDetails['agent_id']
                );


                // SMS params
                $smsData = array(
                    'amount' => $remittanceDetails['amount'],
                    'nick_name' => $remittanceDetails['nick_name'],
                    'beneficiary_name' => $remittanceDetails['name'],
                    'remitter_name' => $remittanceDetails['r_name'],
                    'contact_email' => RATNAKAR_REMITTANCE_EMAIL,
                    'contact_number' => RATNAKAR_CALL_CENTRE_NUMBER,
                    'remitter_phone' => $remittanceDetails['mobile'],
                    'product_name' => RATNAKAR_SHMART_REMIT
                );
               
                if ($formData['status'] == STATUS_SUCCESS) {
                    //Success
                    $paramsBaseTxn['beneficiary_id'] = $remittanceDetails['beneficiary_id'];
                    $paramsBaseTxn['txn_code'] = $traceNumber;
                    $paramsBaseTxn['fee_amt'] = $remittanceDetails['fee'];
                    $paramsBaseTxn['service_tax'] = $remittanceDetails['service_tax'];

                    $baseTxn->remitSuccess($paramsBaseTxn);
                    // Remit request table update Array
                    $updateStatusArr['is_complete'] = FLAG_YES;
                    $updateStatusArr['status'] = STATUS_SUCCESS;
                    $updateStatusArr['fund_holder'] = REMIT_FUND_HOLDER_BENEFICIARY;
                    $m->ratnakarNeftSuccessRemitter($smsData);
                    $flg = TRUE;
                    $datastatus['status_new'] = STATUS_SUCCESS;
         } else {

                    $paramsBaseTxn['reversal_fee_amt'] = $remittanceDetails['fee'];
                    $paramsBaseTxn['reversal_service_tax'] = $remittanceDetails['service_tax'];



                    $baseTxn->remitFailure($paramsBaseTxn);
                    // Remit request table update Array
                    $updateStatusArr['is_complete'] = FLAG_NO;
                    $updateStatusArr['status'] = STATUS_FAILURE;
                    $updateStatusArr['fund_holder'] = REMIT_FUND_HOLDER_REMITTER;
                    $m->ratnakarNeftFailureRemitter($smsData);
                    $datastatus['status_new'] = STATUS_FAILURE;
                }

                $datastatus['remittance_request_id'] = $remittanceDetails['id'];
                $datastatus['status_old'] = $remittanceDetails['status'];
                $datastatus['by_remitter_id'] = $remittanceDetails['remitter_id'];
                $datastatus['by_ops_id'] = $user->id;
                $datastatus['date_created'] = new Zend_Db_Expr('NOW()');
                $resLog = $remittancestatuslog->addStatus($datastatus);
                
                
                $updateStatusArr['cr_response'] = $formData['cr_response'];
                $updateStatusArr['final_response'] = $formData['final_response'];
                $resUpdate = $remittancerequest->updateReq($remittanceDetails['id'],$updateStatusArr);
               
                   $this->_helper->FlashMessenger(
                    array(
                          'msg-success' => 'Hold Transaction has been processed ',
                    )
                );
               $this->_redirect($this->formatURL('/remit_ratnakar_remitter/holdtransactions?'.$backLink));                       
                
            }
        }
       
       $this->view->form = $form; 
       $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/remit_ratnakar_remitter/holdtransactions?'.$backLink;
       
    }

    /*
     * Shows the list of all in process remittance requests
     */

    public function neftpendingAction() {
        $this->title = "NEFT Instructions Pending";
        $page = $this->_getParam('page');
        $formData = $this->_request->getPost();
        $remitrequest = new Remit_Ratnakar_Remittancerequest();
        $remitRequests = $remitrequest->getPendingRemitRequests($page, $paginate = NULL, $force = FALSE);
        $this->view->paginator = $remitRequests;
        if ($this->getRequest()->isPost()) {

            $data = $this->getRemitRequestIdArray($formData);
            if (!empty($data)) {
                try {
                    $remitrequest->updateRemitRequests($data);
                } catch (Zend_Exception $e) {
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage()));
                    $form->populate($formData);
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                }
                $this->_helper->FlashMessenger(array('msg-success' => 'The NEFT instructions have been sent for processing'));
                $this->_redirect($this->formatURL('/remit_ratnakar_remitter/neftrequests/'));
            } else {
                $this->_helper->FlashMessenger(array('msg-error' => 'Please select at least one to process.'));
            }
        }
    }

    /*
     * NEFT requests to generate the NEFT details text file
     */

    public function neftrequestsAction() {
        $this->title = "NEFT Instruction Batches";
        $remitrequest = new Remit_Ratnakar_Remittancerequest();
        $session = new Zend_Session_Namespace('App.Agent.Controller');
        $requests = $this->_getAllParams();
        $page = $this->_getParam('page');
        $form = new Remit_Ratnakar_NeftRequestSearchForm();
        $formData = $this->_request->getPost();
        $itemPerPageFromPost = isset($formData['items_per_page']) ? $formData['items_per_page'] : 0;
        if ($itemPerPageFromPost < 1)
            $itemPerPage = isset($requests['items_per_page']) ? $requests['items_per_page'] : '';
        else
            $itemPerPage = $itemPerPageFromPost;
        $frmSubmit = isset($requests['frm_submit']) ? $requests['frm_submit'] : '';
        $frmAmount = isset($requests['amount']) ? $requests['amount'] : '0';
        $this->view->records = FALSE;
        $finalArr = array();

        //if ($this->getRequest()->isPost()) {exit('hhh');
        if ($form->isValid($requests)) {
            //echo $itemPerPage.'===';
            if ($session->items_per_page != $itemPerPage)
                $page = 1;

            $session->items_per_page = $itemPerPage;
            //$page=1;
            $this->view->records = TRUE;
            $fromDate = explode(' ', Util::returnDateFormatted($requests['from_date'], "d-m-Y", "Y-m-d", "-"));
            $toDate = explode(' ', Util::returnDateFormatted($requests['to_date'], "d-m-Y", "Y-m-d", "-"));

            $params = array('from_date' => $fromDate[0],
                'to_date' => $toDate[0],
                'amount' => $frmAmount);

            //echo '---page is -- '.$page;
            $batchFilesArray = $remitrequest->getBatchFilesArray($params, $page);

            $batchFilesCountArr = $remitrequest->getBatchFilesCountArray($batchFilesArray);

            for ($i = 0; $i < count($batchFilesArray); $i++) {

                $finalArr[] = array_merge($batchFilesArray[$i], $batchFilesCountArr[$i]);
            }

            $paginator = $remitrequest->paginateByArray($finalArr, $page, $paginate = NULL);

            $this->view->paginator = $paginator;

            $form->populate($formData);
            $this->view->items_per_page = $session->items_per_page;
            $this->view->from = $requests['from_date'];
            $this->view->to = $requests['to_date'];
            $this->view->amount = $frmAmount;
        }
        //}

        $this->view->form = $form;
    }

    /*
     * selected batch text file
     */

    public function neftbatchAction() {
        
        $this->title = "NEFT Batch";
        $this->_helper->layout()->disableLayout();
        if ($this->_getParam('batch') != '') {
            $user = Zend_Auth::getInstance()->getIdentity();
            $logModel = new Log();
            $insertArr = array(
                'batch_name' => $this->_getParam('batch'),
                'ops_id' => $user->id,
                'ip' => $logModel->formatIpAddress(Util::getIP())
            );

            $logModel->neftDownloadLogInsert($insertArr,DbTable::TABLE_RAT_LOG_NEFT_DOWNLOAD);

            $remitrequest = new Remit_Ratnakar_Remittancerequest();
            $batchName = $this->_getParam('batch');
            //$batchArr =  $remitrequest->getBatchRecords($batchName);

            $neftrequest = new Remit_Ratnakar_NeftRequest();
            try {
                $neftrequest->downloadNeftTxt($batchName, array(), false, true, $filePermission=0755, FILE_XLS);
            } catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
            }
        }
    }

    /*
     * Get remittance request id from the checkboxes
     */

    private function getRemitRequestIdArray($data) {
        $reqidArr = array();
        foreach ($data as $key => $value) {
            if ($key == 'reqid') {
                $reqidArr = $value;
            }
        }
        return $reqidArr;
    }

    /*
     * Shows the list of all in process remittance requests
     */

    public function neftresponseAction() {
        $this->title = "NEFT Response";
        $page = $this->_getParam('page');
        $form = new Remit_Ratnakar_NeftResponseForm(array('action' => $this->formatURL('/remit_ratnakar_remitter/neftresponse'),
            'method' => 'POST',
            'name' => 'frm_neft_response'
        ));
        $this->view->form = $form;
        $requests = $this->_getAllParams();
        $formData = $this->_request->getPost();
        $this->view->batchName = $batchName = isset($requests['batch_name']) ? $requests['batch_name'] : '';

        // $batchName  = isset($batchName)?$batchName:'';
        //$this->view->sub = isset($formData['sub'])?$formData['sub']:'';
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submitFailure = isset($formData['submit_failure']) ? $formData['submit_failure'] : '';
        $submitSuccess = isset($formData['submit_success']) ? $formData['submit_success'] : '';

        if ($batchName != '') {

            $remitrequest = new Remit_Ratnakar_Remittancerequest();
            $remitRequests = $remitrequest->getProcessedRecords(STATUS_PROCESSED, $page, NULL, $batchName);
            $this->view->paginator = $remitRequests;
            $form->getElement('batch_name')->setValue($batchName);
            $form->populate($requests);
        }

        if ($submitFailure != '' || $submitSuccess != '') {
            $doImplode = false;

            $btnSuccess = isset($formData['submit_success']) ? true : false;

            $data = $this->getRemitRequestIdArray($formData);
            if (!empty($data)) {
                try {
                    if ($btnSuccess) {
                        $status = FLAG_SUCCESS;
                    } else {
                        $status = FLAG_FAILURE;
                    }

                    if (is_array($data))
                        $str = implode(",", $data);
                    else
                        $str = $data;

                    //                      $this->neftupdateAction($str ,$status);  
                    $this->_redirect($this->formatURL('/remit_ratnakar_remitter/neftupdate?strReqId=' . $str . '&status=' . $status . '&batch_name=' . $batchName));
                } catch (Zend_Exception $e) {
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage()));
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                }
                //                  $this->_helper->FlashMessenger( array('msg-success' => 'The NEFT response have been sent for processing') ); 
                //$this->_redirect($this->formatURL('/remit_ratnakar_remitter/neftupdate/'));
            } else {
                $this->_helper->FlashMessenger(array('msg-error' => 'Please select at least one to process.'));
            }
        } // process instruction if closes here

        $this->view->formData = $formData;
    }

    /*
     * Process NEFT requests from NEFT response
     */

    public function neftupdateAction() {
        $this->title = "NEFT Instructions Update";
        $remitrequest = new Remit_Ratnakar_Remittancerequest();

        $arrReqId = array();
        $formData = $this->_request->getPost();

        if ($this->getRequest()->isPost()) {

            $arrReqId = explode(",", $formData['strReqId']);
            foreach ($arrReqId as $rrId) {
                $params['rrId'] = $rrId;
                $params['status'] = $formData['status'];
                $params['neftRemarks'] = $formData['neft_remarks'];
                $remitrequest->updateRemitterResponseFromNEFT($params);
                $batchName = $formData['batch_name'];
            } // end foreach
            $this->_helper->FlashMessenger(array('msg-success' => 'The NEFT response have been processed successfully'));
            $this->_redirect($this->formatURL('/remit_ratnakar_remitter/neftresponse/batch_name/' . $batchName));
        }

        $strReqId = $this->_getParam('strReqId');
        $status = $this->_getParam('status');
        $batchName = $this->_getParam('batch_name');
        $data['strReqId'] = $strReqId;
        $data['status'] = $status;
        $data['batch_name'] = $batchName;
        if ($status == FLAG_SUCCESS) {
            $data['neft_remarks'] = NEFT_RMKS_SUCCESS;
        } else {
            $data['neft_remarks'] = NEFT_RMKS_FAILURE;
        }

        $form = new Remit_Ratnakar_NeftUpdateForm();

        $form->populate($data);
        $this->view->form = $form;

        $selectedNeft = $remitrequest->getSelectedNeftRecords($strReqId);
        $this->view->paginator = $selectedNeft;
        $this->view->status = $status;
        $this->view->batch_name = $batchName;
    }

    /**
     * NEFT Log 
     */
    public function neftlogAction() {
        $this->title = "NEFT Download Log";
        $remitrequest = new Remit_Ratnakar_Remittancerequest();
        $batchName = $this->_getParam('batch');
        $page = $this->_getParam('page');
        $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/remit_ratnakar_remitter/neftrequests';
        $neftLog = $remitrequest->getNEFTlog($batchName);
        $neftLogPaginator = $remitrequest->paginateByArray($neftLog, $page, $paginate = NULL);
        $this->view->paginator = $neftLogPaginator;
    }

    /*
     * Shows the list of all batch details
     */

    public function neftbatchdetailsAction() {
        $this->title = "NEFT Batch Details";
        $page = $this->_getParam('page');
        $from = $this->_getParam('from_date');
        $to = $this->_getParam('to_date');
        $amount = $this->_getParam('amount');
        $items = $this->_getParam('items_per_page');

        $batchName = $this->_getParam('batch_name');
        $this->view->batchName = isset($batchName) ? $batchName : '';
        $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/remit_ratnakar_remitter/neftrequests?from_date=' . $from . '&to_date=' . $to . '&amount=' . $amount;
        if ($batchName != '') {
            $remitrequest = new Remit_Ratnakar_Remittancerequest();
            $remitRequests = $remitrequest->getProcessedRecords('', $page, NULL, $batchName);
            $this->view->paginator = $remitRequests;
        }
        $this->view->title = "NEFT Batch Details";
    }

    /*
     * Mark NEFT as processed
     */

    public function neftprocessedAction() {
        $this->title = "NEFT Processed";

        $batchName = $this->_getParam('batch_name');

        if ($batchName != '') {
            $neftProcessed = new Remit_Ratnakar_Remittancerequest();

            $markneftProcessed = $neftProcessed->neftProcessed($batchName);

            if ($markneftProcessed) {
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'NEFT Batch file has been marked as processed',
                        )
                );
            } else {
                $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'NEFT Batch file could not be marked as processed',
                        )
                );
            }

            $this->_redirect($this->formatURL('/remit_ratnakar_remitter/neftrequests/'));
        }
    }

    
    /*
     * Payment History Upload -
     */
        
    public function uploadpaymenthistoryAction() {
        $this->title = "Upload Payment History";
        $page = $this->_getParam('page');
        $form = new Remit_Ratnakar_PaymentHistoryUploadForm();
        $formData = $this->_request->getPost();
        $input_date = isset($formData['input_date']) ? Util::returnDateFormatted($formData['input_date'], "d-m-Y", "Y-m-d", "-") : date('Y-m-d H:i:s');
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
        $by_ops_id = $user->id;
        $paymenthistoryModel = new Remit_Ratnakar_Paymenthistory();
        $successInsertedRecords = 0;
        $noUTR = array(); // this array store txn_code value which UTR number missing.
        $insertDataArr = array(); // Storing all records which will store in database
        $duplicateDataArr = array();
        $failedInsertedRecords = array();
        $notxnCode = array();
        $totalsuccessAmount = array();
        $totalrecords = 0;
        $errorMsg = array();
        $this->view->records = FALSE;
        //$user = Zend_Auth::getInstance()->getIdentity();
//        $cardholdersModel->ratCorpECSRegn();
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
                    
                   $this->_helper->FlashMessenger( array('msg-error' => 'Invalid file uploaded. Allowed Format is csv only.',));
                   $this->_redirect($this->formatURL('/remit_ratnakar_remitter/uploadpaymenthistory/'));
                }else{
                  
                    /*
                     * Getting file values and match with existing values
                     */
                $datafiles = array();
                    $datafiles['label'] = 'Payment History file';
                    $datafiles['file_name'] = $phTransName;
                    $datafiles['ops_id'] = $user->id;
                    $datafiles['status'] = STATUS_PENDING;
                    $datafiles['upload_status'] = STATUS_PENDING;
                    $datafiles['failed_reason'] = '';
                    $datafiles['input_date'] = $input_date;
                    $datafiles['date_created'] = new Zend_Db_Expr('NOW()');
                    $utr_number ='';
                $txn_code = '';
                 //read and save contents of csv                
                $fp = fopen($name, 'r');
                $i = 1;
                while (!feof($fp)) {
                  
                    //$dataArr = fgetcsv($fp,'200',';');
                    $line = fgets($fp);
                  //  if($i > 1){
                      if (!empty($line)) {
                        $delimiter = PAYMENT_HISTORY_IMPORT_FILE_UPLOAD_DELIMITER;
                        $dataArr = str_getcsv($line, $delimiter);
                     
                        if (!empty($dataArr)) {
                        $arrLength = Util::getArrayLength($dataArr);    
                        if ($arrLength == PAYMENT_HISTORY_UPLOAD_COLUMNS){ 
                        $totalrecords +=1;
                        //  $data = fgetcsv($fp, 1000, ","); // 1000 is number of lines and "," is comma based delimiter
                        // Define the variables which will store in database 
                          $utr_number = $dataArr[12];
                          $txn_code = $dataArr[13];
                         
                        /*
                         * checkTransactionExist : checking current record is stored or not
                         */
                          try {
                              $datafiles['upload_status'] = STATUS_SUCCESS;
                                $datafiles['status'] = STATUS_PENDING;
                                $datafiles['failed_reason'] = '';
                                    
                                if( ($utr_number != '') && ($txn_code != '') ){
                                    // Length Check 
                                    $utrLength = $paymenthistoryModel->checkUTRLength($utr_number);
                                    if($utrLength){
                                        $utrFormat = $paymenthistoryModel->checkUTRFormat($utr_number);
                                        if(!$utrFormat){ //Format check failed
                                                   $datafiles['upload_status'] = STATUS_FAILED;
                                                   $datafiles['status'] = STATUS_FAILED;
                                                   $datafiles['failed_reason'] = 'UTR Format Validation Failed';;
                                        }
                                     
                                    }
                                    else{ // length check failed
                                                $datafiles['upload_status'] = STATUS_FAILED;
                                                $datafiles['status'] = STATUS_FAILED;
                                                $datafiles['failed_reason'] = 'UTR Length Validation Failed';
                                    }
                                    $distchk = $paymenthistoryModel->checkTransactionExist($txn_code,$utr_number);
                                    // define array for insertation
                                    if(!$distchk){
                                        $duplicateDataArr[] = $txn_code;
                                        $datafiles['upload_status'] = STATUS_FAILED;
                                        $datafiles['status'] = STATUS_FAILED;
                                        $datafiles['failed_reason'] = 'Duplicate Record';
                                    }
                                    
                                }else{
                                    $noUTR[] = $dataArr;
                                    $datafiles['upload_status'] = STATUS_FAILED;
                                    $datafiles['status'] = STATUS_FAILED;
                                    $datafiles['failed_reason'] = 'No UTR found';
                                    if($txn_code == ''){
                                        $notxnCode[] = $dataArr;
                                        $datafiles['upload_status'] = STATUS_FAILED;
                                        $datafiles['status'] = STATUS_FAILED;
                                        $datafiles['failed_reason'] = 'No Cust Ref no. found';
                                    }
                                }
                                
                               
                                // Insert date into InsertData Array;
                                $insertchk = $paymenthistoryModel->insertTransaction($dataArr,$datafiles);
                                if($insertchk){
                                    // Successfully Inserted records
                                    $successInsertedRecords += 1;
                                    $totalsuccessAmount[] = $dataArr[6];
                                }else{
                                    // Failed Inserted records
                                    $failedInsertedRecords[]= $dataArr;
                                }
                                      
                            } catch (Exception $e) {
                                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                    throw new Exception($e->getMessage());
                            }
                        }
                        else{
                           
                            $errorMsg[1] = 'Uploaded file have invalid number of columns.';
                        }
                        }else{
                           
                         //   $errorMsg[0] = 'Uploaded file not have some records.<br>';
                        }
                      }else{
                        //  $errorMsg[2] = 'Uploaded file have missed some rows.';
                      }
                   // } 
                // $i +=1;   
                }
              
                $this->view->records = TRUE;
                $this->view->totalrecords = $totalrecords;
                $this->view->file_name = $phTransName;
                $this->view->successInsertedRecords = $successInsertedRecords;
                $this->view->totalsuccessAmount = $totalsuccessAmount;
                $this->view->duplicaterecords = $duplicateDataArr;
                $this->view->failedInsertedRecords = $failedInsertedRecords;
                $this->view->noUTR = $noUTR;
                $this->view->notxnCode = $notxnCode;
                $this->view->errorMsg = $errorMsg;
                
                fclose($fp);
                }
               //  exit();
                
            }else{
                    
                   $this->_helper->FlashMessenger( array('msg-error' => 'Invalid file uploaded. Please upload proper file.',));
                   $this->_redirect($this->formatURL('/remit_ratnakar_remitter/uploadpaymenthistory/'));
                }
        }

       
        
        $this->view->form = $form;
    }
    
    /*
     * 
     */
    
     public function uploadresponsepaymenthistoryAction() {
        $this->title = "Upload Response of Payment History";
        $page = $this->_getParam('page');
        $form = new Remit_Ratnakar_ResponsePaymentHistoryUploadForm();
        $formData = $this->_request->getPost();
        $input_date = isset($formData['input_date']) ? Util::returnDateFormatted($formData['input_date'], "d-m-Y", "Y-m-d", "-") : date('Y-m-d H:i:s');
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $this->view->incorrectData = FALSE;
        $user = Zend_Auth::getInstance()->getIdentity();
        $by_ops_id = $user->id;
        $responsePaymenthistoryModel = new Remit_Ratnakar_Responsepaymenthistory();
        $successInsertedRecords = 0;
        $totalsuccessAmount = array();
        $noUTR = array(); // this array store txn_code value which UTR number missing.
        $insertDataArr = array(); // Storing all records which will store in database
        $duplicateDataArr = array();
        $failedInsertedRecords = array();
        $notranCode = array();
        $totalrecords = 0;
        $errorMsg = array();
        $this->view->records = FALSE;
        //$user = Zend_Auth::getInstance()->getIdentity();
//        $cardholdersModel->ratCorpECSRegn();
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
                   $this->_helper->FlashMessenger( array('msg-error' => 'Invalid file uploaded. Allowed Format is csv only.',));
                   $this->_redirect($this->formatURL('/remit_ratnakar_remitter/uploadresponsepaymenthistory/'));
                }else{
                    /*
                     * Getting file values and match with existing values
                     */
                $datafiles = array();
                $datafiles['label'] = 'Rensonse of Payment History file'; 
                $datafiles['file_name'] = $phTransName;
                $datafiles['ops_id'] = $user->id;
                $datafiles['status'] = STATUS_ACTIVE;
                $datafiles['input_date'] = $input_date;
                $datafiles['date_created'] = new Zend_Db_Expr('NOW()');
                $utr_number ='';
                $txn_code = '';
                 //read and save contents of csv                
                $fp = fopen($name, 'r');
                $i = 1;
                $invalidrows = 0;
                while (!feof($fp)) {
                    //$dataArr = fgetcsv($fp,'200',';');
                    $line = fgets($fp);
                   // if($i > 2){
                      if (!empty($line)) {
                        $delimiter = PAYMENT_HISTORY_RESPONSE_IMPORT_FILE_UPLOAD_DELIMITER;
                        $dataArr = str_getcsv($line, $delimiter);
                        $arrLength = Util::getArrayLength($dataArr);
                        if (!empty($dataArr)) {
                            if ($arrLength == RESPONSE_PAYMENT_HISTORY_UPLOAD_COLUMNS){    
                              // $totalrecords +=1;
                               //  $data = fgetcsv($fp, 1000, ","); // 1000 is number of lines and "," is comma based delimiter
                               // Define the variables which will store in database 
                                 $utr_number = $dataArr[2];
                                 $tran_code = $dataArr[3];
                               
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
                                       if( ($utr_number != '') && ($tran_code != '') && (intval($dataArr[0]) > 0) ){
                                           
                                            $distchk = $responsePaymenthistoryModel->checkTransactionExist($tran_code,$utr_number);
                                            // define array for insertation
                                            if(!$distchk){
                                                $duplicateDataArr[]= $tran_code;
                                               }else{
                                                   // Insert date into InsertData Array;
                                               $insertchk = $responsePaymenthistoryModel->insertTransaction($dataArr,$datafiles);
                                               if($insertchk){
                                                   // Successfully Inserted records
                                                  
                                                   $successInsertedRecords += 1;
                                                   $totalsuccessAmount[] = $dataArr[12];
                                               }else{
                                                   // Failed Inserted records
                                                   $failedInsertedRecords[]= $dataArr;
                                               }
                                                //$insertDataArr[]= $dataArr;
                                               }
                                            }else{
                                            
                                            if( ($tran_code == '') && (intval($dataArr[0]) > 0) ){
                                                $notranCode[] = $dataArr;
                                            }else if( ($utr_number == '') && (intval($dataArr[0]) > 0) ){
                                                $noUTR[] = $dataArr;
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
                $this->view->totalsuccessAmount = $totalsuccessAmount;
                $this->view->duplicaterecords = $duplicateDataArr;
                $this->view->failedInsertedRecords = $failedInsertedRecords;
                $this->view->noUTR = $noUTR;
                $this->view->invalidrows = $invalidrows;
                $this->view->notranCode = $notranCode;
                $this->view->errorMsg = $errorMsg;
                
                fclose($fp);
                }
               //  exit();
                
            }else{
                    
                   $this->_helper->FlashMessenger( array('msg-error' => 'Invalid file uploaded. Please upload proper file.',));
                   $this->_redirect($this->formatURL('/remit_ratnakar_remitter/uploadresponsepaymenthistory/'));
                }
        }

       
        
        $this->view->form = $form;
    }
    
    /*
     * Shows the list of all in process remittance requests
     */

    public function manualmappingAction() {
        $this->title = "Manual Mapping";
        $page = $this->_getParam('page');
        $form = new Remit_Ratnakar_ManualMappingForm(array('action' => $this->formatURL('/remit_ratnakar_remitter/manualmapping'),
            'method' => 'POST',
            'name' => 'frm_neft_response'
        ));
        $this->view->form = $form;
        $requests = $this->_getAllParams();
        $formData = $this->_request->getPost();
        $this->view->batchName = $batchName = isset($requests['batch_name']) ? $requests['batch_name'] : '';
        $txn_code = isset($requests['txn_code']) ? $requests['txn_code'] : '';

        // $batchName  = isset($batchName)?$batchName:'';
        //$this->view->sub = isset($formData['sub'])?$formData['sub']:'';
        $reqidArr = isset($formData['reqid']) ? $formData['reqid'] : '';
        $submitFailure = isset($formData['submit_failure']) ? $formData['submit_failure'] : '';
        $submitSuccess = isset($formData['submit_success']) ? $formData['submit_success'] : '';

        if ($batchName != '') {

            $remitrequest = new Remit_Ratnakar_Remittancerequest();
            $remitRequests = $remitrequest->getUnMappedRecords($batchName,$txn_code);
            $this->view->paginator = $remitRequests;
            $form->getElement('batch_name')->setValue($batchName);
            $form->populate($requests);
        }

        if ($submitFailure != '' || $submitSuccess != '') {
            $doImplode = false;

            $btnSuccess = isset($formData['submit_success']) ? true : false;

            $data = $this->getRemitRequestIdArray($formData);
            if (!empty($data)) {
                try {
                    if ($btnSuccess) {
                        $status = FLAG_SUCCESS;
                    } else {
                        $status = FLAG_FAILURE;
                    }

                    if (is_array($data))
                        $str = implode(",", $data);
                    else
                        $str = $data;

                    //                      $this->neftupdateAction($str ,$status);  
                    $this->_redirect($this->formatURL('/remit_ratnakar_remitter/manualmappingupdate?strReqId=' . $str . '&status=' . $status . '&batch_name=' . $batchName));
                } catch (Zend_Exception $e) {
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage()));
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                }
                //                  $this->_helper->FlashMessenger( array('msg-success' => 'The NEFT response have been sent for processing') ); 
                //$this->_redirect($this->formatURL('/remit_ratnakar_remitter/neftupdate/'));
            } else {
                $this->_helper->FlashMessenger(array('msg-error' => 'Please select at least one to process.'));
            }
        } // process instruction if closes here

        $this->view->formData = $formData;
    }

    /*
     * Process NEFT requests from NEFT response
     */

    public function manualmappingupdateAction() {
        $this->title = "Manual Mapping NEFT Response";
        $remitrequest = new Remit_Ratnakar_Remittancerequest();
        $form = new Remit_Ratnakar_ManualMappingUpdateForm();
        $paymenthistoryModel = new Remit_Ratnakar_Paymenthistory();
        $arrReqId = array();
        $formData = $this->_request->getPost();
        if ($this->getRequest()->isPost()) {
            if($form->isValid($formData)){
                $utrLength = $paymenthistoryModel->checkUTRLength($formData['utr']);
                $utrFormat = $paymenthistoryModel->checkUTRFormat($formData['utr']);
                if(!$utrLength || !$utrFormat){
                    $this->_helper->FlashMessenger(array('msg-error' => 'Invalid UTR Number'));
                    //return false;
                }else{
                    $arrReqId = explode(",", $formData['strReqId']);
                    foreach ($arrReqId as $rrId) {
                        $params['rrId'] = $rrId;
                        $params['status'] = $formData['status'];
                        $params['neftRemarks'] = $formData['manual_mapping_remarks'];
                        $params['utr'] = $formData['utr'];
                        if(!$utrLength){
                            $params['status'] = STATUS_FAILED;
                            $params['failed_reason'] = 'UTR Length Validation Failed';
                        }
                        if(!$utrFormat){
                            $params['status'] = STATUS_FAILED;
                            $params['failed_reason'] = 'UTR Format Validation Failed';
                        }
                        
                        $remitrequest->updateRemitterResponseManual($params);
                        $batchName = $formData['batch_name'];
                    } // end foreach
                    $this->_helper->FlashMessenger(array('msg-success' => 'The NEFT response have been processed successfully'));
                    $this->_redirect($this->formatURL('/remit_ratnakar_remitter/manualmapping/batch_name/' . $batchName));
                }    
            }
        }

        $strReqId = $this->_getParam('strReqId');
        $status = $this->_getParam('status');
        $batchName = $this->_getParam('batch_name');
        $data['strReqId'] = $strReqId;
        $data['status'] = $status;
        $data['batch_name'] = $batchName;
        if ($status == FLAG_SUCCESS) {
            $data['manual_mapping_remarks'] = NEFT_RMKS_SUCCESS;
        } else {
            $data['manual_mapping_remarks'] = NEFT_RMKS_FAILURE;
        }

        

        $form->populate($data);
        $this->view->form = $form;

        $selectedNeft = $remitrequest->getSelectedNeftRecords($strReqId);
        $this->view->paginator = $selectedNeft;
        $this->view->status = $status;
        $this->view->batch_name = $batchName;
    }
    
     public function searchreportAction(){
        
         $this->title = 'Search Remittance Report';
         $data['name'] = $this->_getParam('name');
         $data['from_date'] = $this->_getParam('from_date');
         $data['to_date'] = $this->_getParam('to_date');
         $data['status'] = $this->_getParam('status');
         $data['utr'] = $this->_getParam('utr');
         $data['txn_code'] = $this->_getParam('txn_code');
         $data['mobile'] = $this->_getParam('mobile');
         $data['bank_account_number'] = $this->_getParam('bank_account_number');
         $data['sub'] = $this->_getParam('sub');
         $page = $this->_getParam('page');
         $paginate = NULL;
         $report = 'view';
         $remitterModel = new Remit_Ratnakar_Remitter();
         $form = new Remit_Ratnakar_RemitterSearchReportForm(array('action' => $this->formatURL('/remit_ratnakar_remitter/searchreport'),
                                             'method' => 'POST',
                                      ));
       
        if ($data['sub'] != '') {
            // if($form->isValid($data)){ 
           // if($form->isValid($this->getRequest()->getPost())){
             //if($form->isValid($data)){ 
                if( ($this->_getParam('from_date')!='') && ($this->_getParam('to_date')!='') ) {
                 
                $fromDate = explode(' ', Util::returnDateFormatted($this->_getParam('from_date'), "d-m-Y", "Y-m-d", "-"));
                $toDate = explode(' ', Util::returnDateFormatted($this->_getParam('to_date'), "d-m-Y", "Y-m-d", "-"));
                $from_date = $fromDate[0];
                $to_date = $toDate[0];
                }else{
                $from_date = '';
                $to_date = '';   
                }
               
                $data['name'] = $this->_getParam('name');
                $data['from_date'] = $from_date;
                $data['to_date'] = $to_date;
                $data['status'] = $this->_getParam('status');
                $data['utr'] = $this->_getParam('utr');
                $data['txn_code'] = $this->_getParam('txn_code');
                $data['mobile'] = $this->_getParam('mobile');
                $data['bank_account_number'] = $this->_getParam('bank_account_number');
                $this->view->paginator = $remitterModel->searchRemitterReport($data,$page,$paginate,$report); 
                
                $data['from_date'] = $this->_getParam('from_date');
                $data['to_date'] = $this->_getParam('to_date');
                
                 $this->view->sub = $data['sub'];
                 $this->view->formData = $data;
                 $form->populate($data);  
            
          //}
        }
        
        
        $this->view->controllerName = Zend_Registry::get('controllerName');
        $this->view->form = $form;
    }
    /*
     * exportsearchreportAction: export search remitter report
     */
    
     public function exportsearchreportAction(){
         $this->title = 'Export Remittance Report';
         $data['name'] = $this->_getParam('name');
         $data['from_date'] = $this->_getParam('from_date');
         $data['to_date'] = $this->_getParam('to_date');
         $data['status'] = $this->_getParam('status');
         $data['utr'] = $this->_getParam('utr');
         $data['txn_code'] = $this->_getParam('txn_code');
         $data['mobile'] = $this->_getParam('mobile');
         $data['bank_account_number'] = $this->_getParam('bank_account_number');
         $data['sub'] = $this->_getParam('sub');
         $page = $this->_getParam('page');
         $paginate = NULL;
         $report = 'export';
         $remitterModel = new Remit_Ratnakar_Remitter();
         $form = new Remit_Ratnakar_RemitterSearchReportForm(array('action' => $this->formatURL('/remit_ratnakar_remitter/searchreport'),
                                             'method' => 'POST',
                                      ));
        
          if ($data['sub'] != '') {   
           //   if($form->isValid($data)){ 
                    
                if( ($this->_getParam('from_date')!='') && ($this->_getParam('to_date')!='') ) {
                 
                $fromDate = explode(' ', Util::returnDateFormatted($this->_getParam('from_date'), "d-m-Y", "Y-m-d", "-"));
                $toDate = explode(' ', Util::returnDateFormatted($this->_getParam('to_date'), "d-m-Y", "Y-m-d", "-"));
                $from_date = $fromDate[0];
                $to_date = $toDate[0];
                }else{
                $from_date = '';
                $to_date = '';   
                }
               
                $data['name'] = $this->_getParam('name');
                $data['from_date'] = $from_date;
                $data['to_date'] = $to_date;
                $data['status'] = $this->_getParam('status');
                $data['utr'] = $this->_getParam('utr');
                $data['txn_code'] = $this->_getParam('txn_code');
                $data['mobile'] = $this->_getParam('mobile');
                $data['bank_account_number'] = $this->_getParam('bank_account_number');
                $exportData = $remitterModel->searchRemitterReport($data,$page,$paginate,$report);
               
                $j = 0;
                 foreach($exportData as $data){
                 if($data['request_status'] == STATUS_IN_PROCESS) {
                                $request_status  = 'In Process';
                            } else {
                                $request_status  = ucwords($data['request_status']);                                
                    }    
          	 $formattedArr[$j] = array(
                     'transaction_date' =>$data['transaction_date'] ,'agent_code' => $data['agent_code'],'agent_name' => $data['agent_name'],
                     'estab_city' => $data['estab_city'], 'estab_pincode' =>$data['estab_pincode'],'txn_code' => $data['txn_code'],'utr' => $data['utr'],'amount' => $data['amount'],
                     'mobile' => $data['mobile'],'request_status' =>$request_status,'date_utr' =>$data['date_utr'],'date_status_response' =>$data['date_status_response'],'rejection_code' =>$data['rejection_code'],'rejection_remark' =>$data['rejection_remark']       
                 );
                 $j++;
                 }
               
                 $columns = array(
                    'Transaction Date',
                    'Agent Code',
                    'Agent Name',
                    'Agent City',
                    'Pin Number',
                    'Cust Ref No',
                    'UTR No',
                    'Transaction Amount',
                    'Mobile',
                    'Status',
                    'UTR Updation',
                    'Status Updation',
                    'Reason Code',
                    'Reason Remark',
                    );
                                  
                 $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($formattedArr, $columns, 'ratnakar_remittance_report');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/remit_ratnakar_remitter/searchreport?name='.$data['name'].'&from_date='.$data['from_date'].'&to_date='.$data['to_date'].'&status='.$data['status'].'&utr='.$data['utr'].'&txn_code='.$data['txn_code'].'&sub=1&mobile='.$data['mobile'].'&bank_account_number='.$data['bank_account_number'])); 
                                       }
                 
             //  } else {
              //           $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Params!') );
              //           $this->_redirect($this->formatURL('/remit_ratnakar_remitter/searchreport?name='.$data['name'].'&from_date='.$data['from_date'].'&to_date='.$data['to_date'].'&status='.$data['status'].'&utr='.$data['utr'].'&txn_code='.$data['txn_code'].'&sub=1&mobile='.$data['mobile'].'&bank_account_number='.$data['bank_account_number'])); 
              //        }             
          } else {
                    $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Params!') );
                    $this->_redirect($this->formatURL('/remit_ratnakar_remitter/searchreport?name='.$data['name'].'&from_date='.$data['from_date'].'&to_date='.$data['to_date'].'&status='.$data['status'].'&utr='.$data['utr'].'&txn_code='.$data['txn_code'].'&sub=1&mobile='.$data['mobile'].'&bank_account_number='.$data['bank_account_number'])); 
                 }    
       }
       
       
       
        /*
     * Shows the successful remittance requests
     */

    public function manualrejectionAction() {        
        $this->title = "Manual Rejection";
        //$page = $this->_getParam('page');
        $form = new Remit_Ratnakar_ManualRejectionForm(array('action' => $this->formatURL('/remit_ratnakar_remitter/manualrejection'),
            'method' => 'POST'
        ));
        $this->view->form = $form;
        $requests = $this->_getAllParams();
        $formData = $this->_request->getPost();
        
        $utrnumber = isset($requests['utr']) ? $requests['utr'] : '';
        $txncode = isset($requests['txn_code']) ? $requests['txn_code'] : '';
        $rejection_code = isset($requests['rejection_code']) ? $requests['rejection_code'] : '';
        $rejection_remarks = isset($requests['rejection_remark']) ? $requests['rejection_remark'] : '';

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {

                $user = Zend_Auth::getInstance()->getIdentity();
                $remitrequest = new Remit_Ratnakar_Remittancerequest();
                $remitresponse = new Remit_Ratnakar_Responsefile();
                $objRemitStatusLog = new Remit_Ratnakar_Remittancestatuslog();
                $objRemitterModel = new Remit_Ratnakar_Remitter();
                $beneficiary = new Remit_Ratnakar_Beneficiary();
                $responsefilestatuslog = new Remit_Ratnakar_ResponseFilestatuslog();
                $custModel = new Corp_Ratnakar_Cardholders();
                $masterPurseObj= new MasterPurse();
                $objBaseTxn = new BaseTxn();
                $masterPurseDetails = new MasterPurse();
                $custModel = new Corp_Ratnakar_Cardholders();
                $bank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
                $m = new App\Messaging\Remit\Ratnakar\Agent();
                
                $status =  STATUS_FAILURE; 
                $statusResponse = STATUS_REJECTED;
                $statusSMS = STATUS_SUCCESS;
                 
                try{
		    if($utrnumber != ''){ // on the basis of UTR 
			$utrExistsResponse = $remitresponse->utrExistsResponseFile($utrnumber);
			$responseId = $utrExistsResponse['id'];
			$utrExists = $remitresponse->utrExists($utrnumber);
			$rrId = $utrExists['id'];
			$reqOldStatus = $utrExists['status'];
			$neftRemarks = 'Manual Success to Failure By UTR';
			$field = "UTR"; 
		    } else if($txncode != ''){ //on the basis of TXN_CODE
			$txnCodeExists = $remitrequest->txncodeExists($txncode);
			$rrId = $txnCodeExists['id'];
			$reqOldStatus = $txnCodeExists['status'];
			$neftRemarks = 'Manual Success to Failure By TXN CODE';
			$field = "Txn Number";
		    }
                
		    if($utrExistsResponse['status'] == STATUS_REJECT || $txnCodeExists['status'] == STATUS_REJECT){
			$this->_helper->FlashMessenger(array('msg-error' => 'Already Marked as Rejected.'));
                        $this->_redirect($this->formatURL('/remit_ratnakar_remitter/manualrejection'));
                    }
		    
		    //if utr number exists update response file
                    if($utrExistsResponse && $utrExists && $utrnumber != '') {
                        $updateData = array('status' => STATUS_REJECT,
                                            'rejection_code' => $rejection_code,
                                            'rejection_remark' => $rejection_remarks,
                                            'date_updated' => new Zend_Db_Expr('NOW()'));
                        $remitRequests = $remitrequest->updateResponseByUTR($utrnumber,$updateData);

                       
                        $reqUpdateArr = array('status_response' => $statusResponse,
                            'status_response_by_ops_id'=> $user->id,
                            'date_status_response' => new Zend_Db_Expr('NOW()'),
                            'fund_holder' => USER_TYPE_OPS,
                            'neft_remarks' => $neftRemarks,
                            'status_sms'=> $statusSMS,
                            'status' => $status
			);
                        $remitrequest->updateReqByUTR($utrnumber,$reqUpdateArr);

                        //Insert log
                        $insertArr = array(
                            'response_file_id' => $responseId,
                            'status_old' => STATUS_PROCESS,
                            'status_new' => STATUS_REJECT,
                            'rejection_code' => $rejection_code,
                            'rejection_remark' => $rejection_remarks,
                            'description' => 'Manual success to failure',
                            'by_ops_id' => $user->id,
                            'date_created' => new Zend_Db_Expr('NOW()')
                        );

                        //insert into response status log
                        $res = $responsefilestatuslog->addStatus($insertArr);
                        
                        if($res){
                            $this->_helper->FlashMessenger(array('msg-success' => 'Manual Rejecton Successful.',));
                        }
                        else {
                            $this->_helper->FlashMessenger(array('msg-error' => $Msg,));
                            $this->_redirect($this->formatURL('/remit_ratnakar_remitter/manualrejection'));
                        }
                    } else if($txnCodeExists && $txncode != ''){
			//if TXN Code exists                         
                        $reqUpdateArr = array('status_response' => $statusResponse,
                            'status_response_by_ops_id'=> $user->id,
                            'date_status_response' => new Zend_Db_Expr('NOW()'),
                            'fund_holder' => USER_TYPE_OPS,
                            'neft_remarks' => $neftRemarks,
                            'status_sms'=> $statusSMS,
                            'status' => $status
                            );
                       $res = $remitrequest->updateReqByTXNCode($txncode,$reqUpdateArr); 
                       
                        if($res){
                            $this->_helper->FlashMessenger(array('msg-success' => 'Manual Rejecton Successful.',));
                        } else {
                            $this->_helper->FlashMessenger(array('msg-error' => $Msg,));
                            $this->_redirect($this->formatURL('/remit_ratnakar_remitter/manualrejection'));
                        }
                    } else{
                       $this->_helper->FlashMessenger(array('msg-error' => $field.' does not exist',));
		       $this->_redirect($this->formatURL('/remit_ratnakar_remitter/manualrejection'));
                    }
                    
                    if($res) {
			// Common function
			$rrInfo = $remitrequest->getRemitterRequestsInfo($rrId);
                        $remitterId = $rrInfo['remitter_id'];
                        $amount = $rrInfo['amount'];

                        $remitterArr = $objRemitterModel->findById($remitterId);
                        $beneficiaryArr = $beneficiary->findById($rrInfo['beneficiary_id']);    
                       // $beneficiaryPhone = (isset($beneficiaryArr->mobile))?$beneficiaryArr->mobile:0;
                        			
                        // Success to failure
                        $txnData = array('remit_request_id'=>$rrId, 
                         'beneficiary_id'=>$rrInfo['beneficiary_id'], 
                         'product_id'=>$rrInfo['product_id'], 'amount'=>$rrInfo['amount'], 'txn_code' => $rrInfo['txn_code'],'bank_unicode' => $bank->bank->unicode);
                        $txnResp = $objBaseTxn->remitSuccessToFailure($txnData);
                      
                        // failure
                        $txnData = array('remit_request_id'=>$rrId, 
                          'product_id'=>$rrInfo['product_id'], 'amount'=>$rrInfo['amount'],
                          'reversal_fee_amt'=>$rrInfo['fee'], 'reversal_service_tax'=>$rrInfo['service_tax'],'bank_unicode' => $bank->bank->unicode);
                        $txnResp = $objBaseTxn->remitFailure($txnData);
			
                        $remitReqLog = array('remittance_request_id' => $rrId,
                            'status_old' => $reqOldStatus, 'status_new' => $status,
                            'by_ops_id' => $user->id, 'date_created' => new Zend_Db_Expr('NOW()'));
                        $objRemitStatusLog->addStatus($remitReqLog);
                        			
			/*
			 * 
			 * Refund functionality if Wallet Exist
			 * 
			 */
			if($rrInfo['rat_customer_id'] == 0){
                            /*Send SMS to Remiiter */
                            $dataArr = array('amount' => $amount, 'nick_name' => $beneficiaryArr->nick_name,'remitter_phone' => $remitterArr->mobile );
                            $m->neftFailureRemitter($dataArr);
                        } else {
			    // Checking for ECS requirment
			    $requireECS = FLAG_NO;
			    $masterPurseDetails = $masterPurseObj->getPurseDetailsbyPurseId($rrInfo['purse_master_id']);
			    $isVirtual = isset($masterPurseDetails['is_virtual'])? $masterPurseDetails['is_virtual'] : FLAG_NO;
			    /*
			     * Getting Customer Detail Including CardDetail
			     */
			    $searchArr = array(
				'product_id'=> $rrInfo['product_id'],
				'rat_customer_id' => $rrInfo['rat_customer_id'],
				'customer_master_id' => $rrInfo['customer_master_id'],
				'status' => STATUS_ACTIVE,
			    );
			    $cardholderDetails = $custModel->getCardholderInfo($searchArr);
			    $custCardNumber = ($cardholderDetails->card_number != '') ? $cardholderDetails->card_number : '';
			    $agent_id = $rrInfo['agent_id'];
			    
			    $ecsCall = FALSE;
			    if(($isVirtual == FLAG_NO) && ($custCardNumber!='')) {
				$requireECS = FLAG_YES;  
			    }
			    
			    /*
			     * ECS call for card holder
			     */
			    try{
				if(($requireECS == FLAG_YES) && ($agent_id!='')){
				    $ecsCall = TRUE;  
				    $ecsApi = new App_Socket_ECS_Corp_Transaction();  
				    $txncode = new Txncode();
				    if ($txncode->generateTxncode()){
					$txnCode = $txncode->getTxncode();  
				    }
				 
				    $cardLoadData = array(
					    'amount' => $rrInfo['amount'],
					    'crn' => $custCardNumber,
					    'agentId' => $agent_id,
					    'transactionId' => $txnCode,
					    'currencyCode' => CURRENCY_INR_CODE,
					    'countryCode' => COUNTRY_IN_CODE
				    );
				    if(DEBUG_MVC) {
					$apiResp = TRUE;
					$ecsCall = FALSE;
				    } else {
					$ecsApi = new App_Socket_ECS_Corp_Transaction();
					$apiResp = $ecsApi->cardLoad($cardLoadData); // bypassing for testing
				    }
				} else {
				    $apiResp = TRUE; 
				    $ecsCall = FALSE;
				}
                                 
                                if ($apiResp === TRUE) {
				    $txn_load_id = $ecsCall == TRUE ? $ecsApi->getISOTxnId() : '';
                                    $reqUpdateArr['txn_load_id'] = $txn_load_id;
                                    $txnData = array(
					'remit_request_id' => $rrId,
					'product_id' => $rrInfo['product_id'],
					'amount' => $rrInfo['amount'],
					'reversal_fee_amt' => $rrInfo['fee'], 
					'reversal_service_tax' => $rrInfo['service_tax'], 
					'bank_unicode' => $bank->bank->unicode,
					'rat_customer_id' => $rrInfo['rat_customer_id'], 
					'purse_master_id' => $rrInfo['purse_master_id'],
					'customer_master_id' => $rrInfo['customer_master_id'],
					'customer_purse_id' => $rrInfo['customer_purse_id']
				    );
				    
                                    $txnResp = $objBaseTxn->remitFailureAPI($txnData);

                                    //Insert into rat_refund
                                    $refundArr = array(
					'bank_id' => $rrInfo['bank_id'],    
					'remitter_id' => $rrInfo['remitter_id'],
					'remittance_request_id' => $rrInfo['id'],
					'rat_customer_id' => $rrInfo['rat_customer_id'],
					'purse_master_id' => $rrInfo['purse_master_id'],
					'customer_purse_id' => $rrInfo['customer_purse_id'],
					'agent_id' => $rrInfo['agent_id'],
					'product_id' => $rrInfo['product_id'],
					'amount' => $rrInfo['amount'],
					'fee' => $rrInfo['fee'],
					'service_tax' => $rrInfo['service_tax'],
					'reversal_fee' => 0,
					'reversal_service_tax' => 0,
					'txn_code' => $txnResp,
					'status' => STATUS_SUCCESS,
                                        'channel' => CHANNEL_OPS
				    );
                                    $remitrequest->addRemittanceRefund($refundArr);
                                    $status = STATUS_REFUND ;
                                    $reqUpdateArr = array(
                                        'fund_holder' => REMIT_FUND_HOLDER_REMITTER,
                                        'neft_remarks' => 'Manual Rejection - Refund',
                                        'status' => $status,
                                        'is_complete' => FLAG_YES);
                                        $res = $remitrequest->updateReqByTXNCode($rrInfo['txn_code'],$reqUpdateArr); 

                                        if($res) {
                                            $remitReqLog = array('remittance_request_id' => $rrInfo['id'],
                                                'status_old' => $rrInfo['status'], 'status_new' => $status,
                                                'by_ops_id' => $user->id, 'date_created' => new Zend_Db_Expr('NOW()'));
                                            $responsefilestatuslog->addStatus($remitReqLog);
                                        }
                                    
                                } else {
				    $status = STATUS_PENDING;
                                }
			    } catch (Exception $e ) { 
				App_Logger::log(serialize($e) , Zend_Log::ERR);
			    }
			    
			}
			/*
			 * End ECS
			 */
			 
			
                        $this->_redirect($this->formatURL('/remit_ratnakar_remitter/manualrejection'));
                    }
                } catch(Exception $e){
                     App_Logger::log($e->getMessage(), Zend_Log::ERR);
                     $Msg = $e->getMessage();
                }
            }
        }

        $this->view->formData = $formData;
    }

   public function exportpaymenthistoryreportAction(){
         $this->title = 'Export Payment History Report';
         $data['from_date'] = $this->_getParam('from_date');
         $data['to_date'] = $this->_getParam('to_date');
         $data['sub'] = $this->_getParam('sub');
        
       
         $paymentHstModel = new Remit_Ratnakar_Paymenthistory();
         $form = new Remit_Ratnakar_RemitterPaymentHistoryForm(array('action' => $this->formatURL('/remit_ratnakar_remitter/exportpaymenthistoryreport'),
                                             'method' => 'POST',
                                      ));
        
          if ($data['sub'] != '') {   
         
                 
                $from_date =  Util::returnDateFormatted($this->_getParam('from_date'), "d-m-Y", "Y-m-d", "-","-","from");
                $to_date =  Util::returnDateFormatted($this->_getParam('to_date'), "d-m-Y", "Y-m-d", "-","-","to");
                
                
               
                $data['from_date'] = $from_date;
                $data['to_date'] = $to_date;
               
                $exportData = $paymentHstModel->getRecordStatus($data);
                $columns = array(
                'Ref Number',
                'From Account No.',
                'Beneficiary Account No.',
                'Beneficiary Name',
                'Amount',
                'Transaction Status',
                'Core Status',
                'Narration',
                'Type Of Txn',
                'IFSC Code',
                'Cust Ref No.',
                'UTR',    
                'Date of Transaction',
                'Date Of Execution',
                'File Name',
                'Input Date',
                'Status',
                'Reason',
               
            );

            $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'ratnakar_payment_history');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/remit_ratnakar_remitter/exportpaymenthistoryreport?from_date='.$data['from_date'].'&to_date='.$data['to_date'].'$sub=1')); 
                                       }
                    
          }  
                 
                 $this->view->form = $form;
}
}
