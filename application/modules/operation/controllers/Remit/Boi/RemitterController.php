<?php

/**
 * That RemitterController is responsible for all remit operations at partner portal.
 */
class Remit_Boi_RemitterController extends App_Agent_Controller {

    public function init() {
        parent::init();
    }

    /* indexAction is landing page for remitter different operations       
     */

    public function indexAction() {
        
    }

    public function searchAction() {

        $this->title = 'Remitter';
        $data['searchCriteria'] = $this->_getParam('searchCriteria');
        $data['keyword'] = $this->_getParam('keyword');
        $data['sub'] = $this->_getParam('sub');

        $remitterModel = new Remit_Boi_Remitter();
        $form = new Remit_Boi_RemitterSearchForm(array('action' => $this->formatURL('/remit_boi_remitter/search'),
            'method' => 'POST',
        ));

        if ($data['sub'] != '') {
            if ($form->isValid($this->getRequest()->getPost())) {
                $data['searchCriteria'] = $this->_getParam('searchCriteria');
                $data['keyword'] = $this->_getParam('keyword');

                $this->view->paginator = $remitterModel->searchRemitter($data, $this->_getPage());
                $this->view->sub = $data['sub'];
                $form->populate($data);
            }
        }


        $this->view->controllerName = Zend_Registry::get('controllerName');
        $this->view->form = $form;
    }

    public function beneficiaryAction() {

        $this->title = "Remitter's Beneficiaries";
        $rid = ($this->_getParam('rid') > 0) ? $this->_getParam('rid') : 0;
        if ($rid > 0) {
            $remitterModel = new Remit_Boi_Remitter();
            $remitterInfo = $remitterModel->getRemitterById($rid);
            $this->view->paginator = $remitterModel->getRemitterbeneficiaries($rid, $this->_getPage());
            $this->view->remitterInfo = $remitterInfo;
        }
        $this->view->controllerName = Zend_Registry::get('controllerName');
    }

    /*
     * Shows the list of all in process remittance requests
     */

    public function neftpendingAction() {
        $this->title = "NEFT Instructions Pending";
        $page = $this->_getParam('page');
        $formData = $this->_request->getPost();
        $remitrequest = new Remit_Remittancerequest();
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
                $this->_redirect($this->formatURL('/remit_boi_remitter/neftrequests/'));
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
        $remitrequest = new Remit_Remittancerequest();
        $session = new Zend_Session_Namespace('App.Agent.Controller');
        $requests = $this->_getAllParams();
        $page = $this->_getParam('page');
        $form = new Remit_Boi_NeftRequestSearchForm();
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

            $logModel->insertlog($insertArr, DbTable::TABLE_LOG_NEFT_DOWNLOAD);

            $remitrequest = new Remit_Remittancerequest();
            $batchName = $this->_getParam('batch');
            //$batchArr =  $remitrequest->getBatchRecords($batchName);

            $neftrequest = new Remit_Boi_NeftRequest();
            try {
                $neftrequest->downloadNeftTxt($batchName, array(), false, true);
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
        $form = new Remit_Boi_NeftResponseForm(array('action' => $this->formatURL('/remit_boi_remitter/neftresponse'),
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

            $remitrequest = new Remit_Remittancerequest();
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
                    echo $str;
                    //                      $this->neftupdateAction($str ,$status);  
                    $this->_redirect($this->formatURL('/remit_boi_remitter/neftupdate?strReqId=' . $str . '&status=' . $status . '&batch_name=' . $batchName));
                } catch (Zend_Exception $e) {
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage()));
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                }
                //                  $this->_helper->FlashMessenger( array('msg-success' => 'The NEFT response have been sent for processing') ); 
                //$this->_redirect($this->formatURL('/remit_boi_remitter/neftupdate/'));
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
        $remitrequest = new Remit_Remittancerequest();

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
            $this->_redirect($this->formatURL('/remit_boi_remitter/neftresponse/batch_name/' . $batchName));
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

        $form = new Remit_Boi_NeftUpdateForm();

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
        $remitrequest = new Remit_Remittancerequest();
        $batchName = $this->_getParam('batch');
        $page = $this->_getParam('page');
        $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/remit_boi_remitter/neftrequests';
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
        $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/remit_boi_remitter/neftrequests?from_date=' . $from . '&to_date=' . $to . '&amount=' . $amount;
        if ($batchName != '') {
            $remitrequest = new Remit_Remittancerequest();
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
            $neftProcessed = new Remit_Remittancerequest();

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

            $this->_redirect($this->formatURL('/remit_boi_remitter/neftrequests/'));
        }
    }

}
