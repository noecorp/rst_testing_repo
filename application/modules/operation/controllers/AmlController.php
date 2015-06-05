<?php

/**
 * Allow the admins to manage Agent fee.
 *
 * @category Agent fee
 * @package operation_module
 * @copyright Transerv
 */
class AmlController extends App_Operation_Controller {

    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init() {
        // init the parent
        parent::init();
    }

    public function indexAction() {
        
    }

    public function kotakindexAction() {
        
    }

    public function bankindexAction()
    {
        
    }

    public function uploadamlAction() {
        $this->title = "Bulk AML Upload";
        $page = $this->_getParam('page');
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new AMLMasterForm();
        $formData = $this->_request->getPost();
        $this->view->incorrectData = FALSE;
        $amlModel = new AMLMaster();

        $this->view->records = FALSE;
        if ($this->getRequest()->isPost()) {

            if ($form->isValid($this->getRequest()->getPost())) {
                //if ($formData['xml_source'] == "UN") {
                    /*if (!empty($formData['url'])) {
                        $xml = XML2Array::exexuteCurl($formData['url']);
                        $source = $formData['url'];
                    } else {*/
                        $upload = new Zend_File_Transfer_Adapter_Http();
                        $upload->receive();
                        $name = $upload->getFileName('doc_path');
                        $xml = file_get_contents($name);
                        $source = $upload->getFileName('doc_path', $path = FALSE);
                    //}
                    try {
                        if ($xml) {
                            $array = XML2Array::createArray($xml);
                            foreach ($array['CONSOLIDATED_LIST']['INDIVIDUALS']['INDIVIDUAL'] as $data):
                                /* $insertData['LISTED_ON'] = str_replace("T"," ",$data['LISTED_ON']);
                                  $insertData['SORT_KEY_LAST_MOD'] = str_replace("T"," ",$data['SORT_KEY_LAST_MOD']); */
                                $insertData['dataid'] = $data['DATAID'];
                                $insertData['first_name'] = $data['FIRST_NAME'];
                                $insertData['second_name'] = $data['SECOND_NAME'];
                                $insertData['full_name'] = $data['SORT_KEY'];
                                $insertData['fake_names'] = serialize($data['INDIVIDUAL_ALIAS']);
                                $insertData['nationality'] = serialize($data['NATIONALITY']);
                                $insertData['fake_address'] = serialize($data['INDIVIDUAL_ADDRESS']);
                                $insertData['individual_date_of_birth'] = serialize($data['INDIVIDUAL_DATE_OF_BIRTH']);
                                $insertData['individual_document'] = serialize($data['INDIVIDUAL_DOCUMENT']);
                                $insertData['comments1'] = $data['COMMENTS1'];
                                $insertData['source'] = $source;
                                $insertData['date_created'] = date('Y-m-d H:i:s');
                                $insertData['by_ops_id'] = $user->id;

                                $amlModel->insertMasterCRN($insertData);
                            endforeach;
                            $this->_helper->FlashMessenger(array('msg-success' => 'AML records uploaded successfully',));
                        }
                    } catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                    }
                //}
            }
        }

        $this->view->form = $form;
    }

    public function amlbyopsAction() {

        $form = new AmlByOpsForm(array('action' => $this->formatURL('/aml/amlbyops'),
            'method' => 'POST',
        ));

        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurStr['sub'] = $sub;
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        if ($sub != '') {

            if ($form->isValid($qurStr)) {
                if ($qurStr['dur'] != '') {
                    $durationArr = Util::getDurationDates($qurStr['dur']);
                    $qurData['from'] = $durationArr['from'];
                    $qurData['to'] = $durationArr['to'];
                    $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                    $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                    $this->view->from = $fromDate[0];
                    $this->view->to = $toDate[0];
                    $this->view->title = 'AML Records Report for ' . Util::returnDateFormatted($fromDate[0], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to ' . Util::returnDateFormatted($toDate[0], "Y-m-d", "d-m-Y", "-");
                } elseif ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $this->view->title = 'AML Records Report for ' . Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to ' . Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurData['from'];
                    $this->view->to = $qurData['to'];
                }
            }
        }
        $amlModel = new AMLMaster();
        $this->view->paginator = $amlModel->getAmlByOps($qurData, $this->_getPage());
        $this->view->formData = $qurStr;
        $this->view->form = $form;
    }

    public function displayamlAction() {

        $form = new AmlByOpsForm(array('action' => $this->formatURL('/aml/displayaml'),
            'method' => 'POST',
        ));

        $request = $this->_getAllParams();
        $sub = $this->_getParam('sub');
        $qurStr['dur'] = $this->_getParam('dur');
        $qurData['by_ops_id'] = $this->_getParam('by_ops_id');
        $qurStr['sub'] = $sub;
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        if ($sub != '') {

            if ($form->isValid($qurStr)) {
                if ($qurStr['dur'] != '') {
                    $durationArr = Util::getDurationDates($qurStr['dur']);
                    $qurData['from'] = $durationArr['from'];
                    $qurData['to'] = $durationArr['to'];
                    $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
                    $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
                    $this->view->from = $fromDate[0];
                    $this->view->to = $toDate[0];
                    $this->view->title = 'AML Records Report for ' . Util::returnDateFormatted($fromDate['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to ' . Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                } elseif ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $this->view->title = 'AML Records Report for ' . Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
                    $this->view->title .= ' to ' . Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
                    $this->view->from = $qurData['from'];
                    $this->view->to = $qurData['to'];
                }
            }
        }
        $amlModel = new AMLMaster();
        $this->view->paginator = $amlModel->getAll($qurData, $this->_getPage());
        $this->view->formData = $qurStr;
        $this->view->form = $form;
    }

    public function kotakremittersAction() {

        $this->title = 'AML Matched Kotak Remitters Listing';
        $amlMater = new AMLMaster();
        $remitter = new Remit_Kotak_Remitter();
        $user = Zend_Auth::getInstance()->getIdentity();
        $qurStr['id'] = $this->_getParam('id', 0);
        $qurStr['status'] = $this->_getParam('status');
        if ($qurStr['id'] && $qurStr['status']) {
            $param = array('status' => $qurStr['status'], 'by_ops_id' => $user->id);
            $remitter->updateRemitter($param, $qurStr['id']);
            $this->_helper->FlashMessenger(array('msg-success' => 'Status updated successfully'));
        }

        $retData = $amlMater->getKotakRemitters($id, $this->_getPage());
        $this->view->paginator = $amlMater->paginateByArray($retData, $this->_getPage());
    }

    public function kotakremitterdetailAction() {

        $this->title = 'AML Matched Kotak Remitter Details';
        $amlMater = new AMLMaster();
        $remitter = new Remit_Kotak_Remitter();
        $user = Zend_Auth::getInstance()->getIdentity();
        $qurStr['id'] = $this->_getParam('id', 0);
        $qurStr['status'] = $this->_getParam('status');
        if ($qurStr['id'] && $qurStr['status']) {

            $param = array('status' => $qurStr['status'], 'by_ops_id' => $user->id);
            $remitter->updateRemitter($param, $qurStr['id']);
            $this->_helper->FlashMessenger(array('msg-success' => 'Status updated successfully'));
            $this->_redirect($this->formatURL('/aml/kotakremitters'));
        }

        $retData = $amlMater->getKotakSingleRemitter($qurStr['id']);
        $this->view->remitter = $retData['remitter'];
        $this->view->paginator = $amlMater->paginateByArray($retData['aml'], $this->_getPage());
        $this->view->amlMater = $amlMater;
    }

    public function kotakbeneficiariesAction() {

        $this->title = 'AML Matched Kotak Beneficiaries Listing';
        $amlMater = new AMLMaster();
        $user = Zend_Auth::getInstance()->getIdentity();
        $beneficiary = new Remit_Kotak_Beneficiary();
        $qurStr['id'] = $this->_getParam('id', 0);
        $qurStr['status'] = $this->_getParam('status');
        if ($qurStr['id'] && $qurStr['status']) {
            $param = array('status' => $qurStr['status'], 'by_ops_id' => $user->id);
            $beneficiary->updateBeneficiaryDetails($param, $qurStr['id']);
            $this->_helper->FlashMessenger(array('msg-success' => 'Status updated successfully'));
        }
        $retData = $amlMater->getKotakBeneficiaries();
        $this->view->paginator = $amlMater->paginateByArray($retData, $this->_getPage());
    }

    public function kotakbeneficiarydetailAction() {

        $this->title = 'AML Matched Kotak Beneficiary Details';
        $amlMater = new AMLMaster();
        $user = Zend_Auth::getInstance()->getIdentity();
        $beneficiary = new Remit_Kotak_Beneficiary();
        $qurStr['id'] = $this->_getParam('id', 0);
        $qurStr['status'] = $this->_getParam('status');
        if ($qurStr['id'] && $qurStr['status']) {
            $param = array('status' => $qurStr['status'], 'by_ops_id' => $user->id);
            $beneficiary->updateBeneficiaryDetails($param, $qurStr['id']);
            $this->_helper->FlashMessenger(array('msg-success' => 'Status updated successfully'));
            $this->_redirect($this->formatURL('/aml/kotakbeneficiaries'));
        }

        $retData = $amlMater->getKotakSingleBeneficiary($qurStr['id']);
        $this->view->beneficiary = $retData['beneficiary'];
        $this->view->paginator = $amlMater->paginateByArray($retData['aml'], $this->_getPage());
        $this->view->amlMater = $amlMater;
    }

    public function amlrejectedagentsAction() {

        $this->title = 'Rejected Agents Listing';

        $amlMater = new AMLMaster();
        $this->view->paginator = $amlMater->getrejectedDetails($this->_getPage());
    }

    public function exportamlrejectedagentsAction() {

        $this->title = 'Export Rejected Agents Listing';

        $amlMater = new AMLMaster();
        $exportData = $amlMater->getrejectedDetails($this->_getPage());
        $reportData = array();
        $i = 0;
        foreach ($exportData As $data) {
            $reportData[$i]['name'] = $data['name'];
            $reportData[$i]['agent_code'] = $data['agent_code'];
            $reportData[$i]['email'] = $data['email'];
            $reportData[$i]['mobile1'] = $data['mobile1'];
            $reportData[$i]['reg_datetime'] = $data['reg_datetime'];
            $i++;
        }
        $columns = array(
            'Name',
            'Agent Code',
            'Email',
            'Mobile no.',
            'Registration Date',
        );

        $objCSV = new CSV();
        try {
            $resp = $objCSV->export($reportData, $columns, 'aml_rejected_agents');
            exit;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
            $this->_redirect($this->formatURL('/aml/amlrejectedagents'));
        }
    }

    public function exportkotakbeneficiariesAction() {

        $this->title = 'Export Kotak Beneficiaries Listing';

        $amlMater = new AMLMaster();
        $exportData = $amlMater->getKotakBeneficiaries();
        $reportData = array();
        $i = 0;
        foreach ($exportData As $data) {
            $reportData[$i]['name'] = $data['name'];
            $reportData[$i]['email'] = $data['email'];
            $reportData[$i]['date_created'] = $data['date_created'];
            $reportData[$i]['status'] = $data['status'];
            $i++;
        }
        $columns = array(
            'Name',
            'Email',
            'Registration Date',
            'Status',
        );

        $objCSV = new CSV();
        try {
            $resp = $objCSV->export($reportData, $columns, 'aml_kotak_beneficiaries');
            exit;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
            $this->_redirect($this->formatURL('/aml/kotakbeneficiaries'));
        }
    }

    public function exportkotakremittersAction() {

        $this->title = 'Export AML Matched Kotak Remitters Listing';
        $amlMater = new AMLMaster();
        $exportData = $amlMater->getKotakRemitters();
        $reportData = array();
        $i = 0;
        foreach ($exportData As $data) {
            $reportData[$i]['name'] = $data['name'];
            $reportData[$i]['mobile'] = $data['mobile'];
            $reportData[$i]['email'] = $data['email'];
            $reportData[$i]['date_created'] = $data['date_created'];
            $reportData[$i]['status'] = $data['status'];
            $i++;
        }
      
        $columns = array(
            'Name',
            'Mobile No.',
            'Email',
            'Registration Date',
            'Action'
        );

        $objCSV = new CSV();
        try {
            $resp = $objCSV->export($reportData, $columns, 'aml_kotak_remitters');
            exit;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
            $this->_redirect($this->formatURL('/aml/kotakremitters'));
        }
    }

    public function kotakcardholdersAction() {

        $this->title = 'AML Matched Kotak Cardholders Listing';
        $amlMater = new AMLMaster();
        $user = Zend_Auth::getInstance()->getIdentity();
        $cardholder = new Corp_Kotak_Customers();
        $qurStr['id'] = $this->_getParam('id', 0);
        $qurStr['status'] = $this->_getParam('status');
        if ($qurStr['id'] && $qurStr['status']) {
            $param = array('status' => $qurStr['status'], 'by_ops_id' => $user->id);
            $cardholder->updateCardHoldersDetails($param, $qurStr['id']);
            $this->_helper->FlashMessenger(array('msg-success' => 'Status updated successfully'));
        }

        $retData = $amlMater->getKotakCorpCardholders();
        $this->view->paginator = $amlMater->paginateByArray($retData, $this->_getPage());
    }

    public function kotakcardholderdetailAction() {

        $this->title = 'AML Matched Kotak Cardholder Details';
        $amlMater = new AMLMaster();
        $user = Zend_Auth::getInstance()->getIdentity();
        $cardholder = new Corp_Kotak_Customers();
        $qurStr['id'] = $this->_getParam('id', 0);
        $qurStr['status'] = $this->_getParam('status');
        if ($qurStr['id'] && $qurStr['status']) {
            $param = array('status' => $qurStr['status'], 'by_ops_id' => $user->id);
            $cardholder->updateCardHoldersDetails($param, $qurStr['id']);
            $this->_helper->FlashMessenger(array('msg-success' => 'Status updated successfully'));
            $this->_redirect($this->formatURL('/aml/kotakcardholders'));
        }

        $retData = $amlMater->getKotakSingleCardHolder($qurStr['id']);
        $this->view->cardholder = $retData['cardholder'];
        $this->view->paginator = $amlMater->paginateByArray($retData['aml'], $this->_getPage());
        $this->view->amlMater = $amlMater;
    }

    public function exportkotakcardholdersAction() {

        $this->title = 'Export AML Kotak Cardholders Listing';

        $amlMater = new AMLMaster();
        $exportData = $amlMater->getKotakCorpCardholders();
        $reportData = array();
        $i = 0;
        foreach ($exportData As $data) {
            $reportData[$i]['name'] = $data['name'];
            $reportData[$i]['card_number'] = Util::maskCard($data['card_number'], 4);
            $reportData[$i]['member_id'] = $data['member_id'];
            $reportData[$i]['mobile'] = $data['mobile'];
            $reportData[$i]['date_created'] = $data['date_created'];
            $reportData[$i]['status'] = $data['status'];
            $i++;
        }
        $columns = array(
            'Name',
            'Card No.',
            'Member Id',
            'Mobile No',
            'Registration Date',
            'Status',
        );

        $objCSV = new CSV();
        try {
            $resp = $objCSV->export($reportData, $columns, 'aml_kotak_corp_cardholders');
            exit;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage()));
            $this->_redirect($this->formatURL('/aml/kotakcardholders'));
        }
    }

    public function kotakduplicateremittersAction() {

        $this->title = 'Duplicate Remitters';
        $amlMater = new AMLMaster();

        $retData = $amlMater->getKotakDuplicateRemitters();
        $this->view->paginator = $amlMater->paginateByArray($retData, $this->_getPage());
    }

    public function kotakremitterduplicatedetailsAction() {

        $this->title = 'Duplicate Remitters';
        $amlMater = new AMLMaster();
        $qurStr['id'] = $this->_getParam('id', 0);
        $retData = $amlMater->getKotakDuplicateRemitterDetail($qurStr['id']);
        $this->view->paginator = $amlMater->paginateByArray($retData, $this->_getPage(), false);
    }

    public function kotakduplicatebeneficiaryAction() {

        $this->title = 'Duplicate Beneficiary';
        $amlMater = new AMLMaster();
        $retData = $amlMater->getKotakDuplicateBeneficiary();
        $this->view->paginator = $amlMater->paginateByArray($retData, $this->_getPage());
    }

    public function kotakbeneficiaryduplicatedetailsAction() {

        $this->title = 'Duplicate Remitters';
        $amlMater = new AMLMaster();
        $qurStr['id'] = $this->_getParam('id', 0);
        $retData = $amlMater->getKotakDuplicateBeneficiaryDetail($qurStr['id']);
        $this->view->paginator = $amlMater->paginateByArray($retData, $this->_getPage(), false);
    }

    public function kotakduplicatecardholderAction() {

        $this->title = 'Duplicate CardHolders';
        $amlMater = new AMLMaster();
        $retData = $amlMater->getKotakDuplicateCardHolder();
        $this->view->paginator = $amlMater->paginateByArray($retData, $this->_getPage());
    }

    public function kotakcardholderduplicatedetailsAction() {

        $this->title = 'Duplicate Remitters';
        $amlMater = new AMLMaster();
        $qurStr['id'] = $this->_getParam('id', 0);
        $retData = $amlMater->getKotakDuplicateCardholderDetail($qurStr['id']);
        $this->view->paginator = $amlMater->paginateByArray($retData, $this->_getPage(), false);
    }

    public function ratnakarindexAction() {
        
    }

    public function ratnakarremittersAction() {

        $this->title = 'AML Matched Ratnakar Remitters Listing';
        $amlMater = new AMLMaster();
        $remitter = new Remit_Ratnakar_Remitter();
        $user = Zend_Auth::getInstance()->getIdentity();
        $qurStr['id'] = $this->_getParam('id', 0);
        $qurStr['status'] = $this->_getParam('status');
        if ($qurStr['id'] && $qurStr['status']) {
            $param = array('status' => $qurStr['status'], 'by_ops_id' => $user->id);
            $remitter->updateAMLRemitter($param, $qurStr['id']);
            $this->_helper->FlashMessenger(array('msg-success' => 'Status updated successfully'));
        }

        $retData = $amlMater->getRatnakarRemitters($id, $this->_getPage());
        $this->view->paginator = $amlMater->paginateByArray($retData, $this->_getPage());
    }

    public function exportratnakarremittersAction() {
        $this->title = 'Export AML Matched Ratnakar Remitters Listing';
        $amlMater = new AMLMaster();
        $exportData = $amlMater->getRatnakarRemitters();
        $reportData = array();
        $i = 0;
        foreach ($exportData As $data) {
            $reportData[$i]['name'] = $data['name'];
            $reportData[$i]['mobile'] = $data['mobile'];
            $reportData[$i]['email'] = $data['email'];
            $reportData[$i]['date_created'] = $data['date_created'];
            $reportData[$i]['status'] = $data['status'];
            $i++;
        }
        $columns = array(
            'Name',
            'Mobile',
            'Email',
            'Registration Date',
            'Status',
        );

        $objCSV = new CSV();
        try {
            $resp = $objCSV->export($reportData, $columns, 'aml_ratnakar_remitters');
            exit;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage()));
            $this->_redirect($this->formatURL('/aml/ratnakarremitters'));
        }
    }

    public function ratnakarremitterdetailAction() {
        $this->title = 'AML Matched Ratnakar Remitter Details';
        $amlMater = new AMLMaster();
        $remitter = new Remit_Ratnakar_Remitter();
        $user = Zend_Auth::getInstance()->getIdentity();
        $qurStr['id'] = $this->_getParam('id', 0);
        $qurStr['status'] = $this->_getParam('status');
        if ($qurStr['id'] && $qurStr['status']) {
            $param = array('status' => $qurStr['status'], 'by_ops_id' => $user->id);
            $remitter->updateAMLRemitter($param, $qurStr['id']);
            $this->_helper->FlashMessenger(array('msg-success' => 'Status updated successfully'));
            $this->_redirect($this->formatURL('/aml/ratnakarremitters'));
        }

        $retData = $amlMater->getRatnakarSingleRemitter($qurStr['id']);
        $this->view->remitter = $retData['remitter'];
        $this->view->paginator = $amlMater->paginateByArray($retData['aml'], $this->_getPage());
        $this->view->amlMater = $amlMater;
    }

    public function ratnakarbeneficiariesAction() {

        $this->title = 'AML Matched Ratnakar Beneficiaries Listing';
        $amlMater = new AMLMaster();
        $user = Zend_Auth::getInstance()->getIdentity();
        $beneficiary = new Remit_Ratnakar_Beneficiary();
        $qurStr['id'] = $this->_getParam('id', 0);
        $qurStr['status'] = $this->_getParam('status');
        if ($qurStr['id'] && $qurStr['status']) {

            $param = array('status' => $qurStr['status'], 'by_ops_id' => $user->id);
            $beneficiary->updateBeneficiaryDetails($param, $qurStr['id']);
            $this->_helper->FlashMessenger(array('msg-success' => 'Status updated successfully'));
        }
        $retData = $amlMater->getRatBeneficiaries();
        $this->view->paginator = $amlMater->paginateByArray($retData, $this->_getPage());
    }

    public function exportratnakarbeneficiariesAction() {

        $this->title = 'Export AML Ratnakar Beneficiaries Listing';

        $amlMater = new AMLMaster();
        $exportData = $amlMater->getRatBeneficiaries();
        $reportData = array();
        $i = 0;
        foreach ($exportData As $data) {
            $reportData[$i]['name'] = $data['name'];
            $reportData[$i]['email'] = $data['email'];
            $reportData[$i]['date_created'] = $data['date_created'];
            $reportData[$i]['status'] = $data['status'];
            $i++;
        }
        $columns = array(
            'Name',
            'Email',
            'Registration Date',
            'Status',
        );

        $objCSV = new CSV();
        try {
            $resp = $objCSV->export($reportData, $columns, 'aml_ratnakar_beneficiaries');
            exit;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage()));
            $this->_redirect($this->formatURL('/aml/ratnakarbeneficiaries'));
        }
    }

    public function ratnakarbeneficiarydetailAction() {

        $this->title = 'AML Matched Ratnakar Beneficiary Details';
        $amlMater = new AMLMaster();
        $user = Zend_Auth::getInstance()->getIdentity();
        $beneficiary = new Remit_Ratnakar_Beneficiary();
        $qurStr['id'] = $this->_getParam('id', 0);
        $qurStr['status'] = $this->_getParam('status');
        if ($qurStr['id'] && $qurStr['status']) {
            $param = array('status' => $qurStr['status'], 'by_ops_id' => $user->id);
            $beneficiary->updateBeneficiaryDetails($param, $qurStr['id']);
            $this->_helper->FlashMessenger(array('msg-success' => 'Status updated successfully'));
            $this->_redirect($this->formatURL('/aml/ratnakarbeneficiaries'));
        }

        $retData = $amlMater->getRatnakarSingleBeneficiary($qurStr['id']);
        $this->view->beneficiary = $retData['beneficiary'];
        $this->view->paginator = $amlMater->paginateByArray($retData['aml'], $this->_getPage());
        $this->view->amlMater = $amlMater;
    }

    public function ratnakarcardholdersAction() {

        $this->title = 'AML Matched Ratnakar Cardhoders Listing';
        $amlMater = new AMLMaster();
        $user = Zend_Auth::getInstance()->getIdentity();
        $cardholder = new Corp_Ratnakar_Cardholders();
        $qurStr['id'] = $this->_getParam('id', 0);
        $qurStr['status'] = $this->_getParam('status');
        if ($qurStr['id'] && $qurStr['status']) {
            $param = array('status' => $qurStr['status'], 'by_ops_id' => $user->id);
            $cardholder->updateCardholder($param, $qurStr['id']);
            $this->_helper->FlashMessenger(array('msg-success' => 'Status updated successfully'));
        }

        $retData = $amlMater->getRatnakarCorpCardholders();
        $this->view->paginator = $amlMater->paginateByArray($retData, $this->_getPage());
    }

    public function ratnakarcardholderdetailAction() {

        $this->title = 'AML Matched Ratnakar Cardhoders Details';
        $amlMater = new AMLMaster();
        $user = Zend_Auth::getInstance()->getIdentity();
        $cardholder = new Corp_Ratnakar_Cardholders();
        $qurStr['id'] = $this->_getParam('id', 0);
        $qurStr['status'] = $this->_getParam('status');
        if ($qurStr['id'] && $qurStr['status']) {
            $param = array('status' => $qurStr['status'], 'by_ops_id' => $user->id);
            $cardholder->updateCardholder($param, $qurStr['id']);
            $this->_helper->FlashMessenger(array('msg-success' => 'Status updated successfully'));
            $this->_redirect($this->formatURL('/aml/ratnakarcardholders'));
        }

        $retData = $amlMater->getRatnakarSingleCardHolder($qurStr['id']);
        $this->view->cardholder = $retData['cardholder'];
        $this->view->paginator = $amlMater->paginateByArray($retData['aml'], $this->_getPage());
        $this->view->amlMater = $amlMater;
    }

    public function exportratnakarcardholdersAction() {

        $this->title = 'Export AML Ratnakar Cardholders Listing';

        $amlMater = new AMLMaster();
        $exportData = $amlMater->getRatnakarCorpCardholders();
        $reportData = array();
        $i = 0;
        foreach ($exportData As $data) {
            $reportData[$i]['name'] = $data['name'];
            $reportData[$i]['email'] = $data['email'];
            $reportData[$i]['card_number'] = Util::maskCard($data['card_number'], 4);
            $reportData[$i]['medi_assist_id'] = $data['medi_assist_id'];
            $reportData[$i]['mobile'] = $data['mobile'];
            $reportData[$i]['date_created'] = $data['date_created'];
            $reportData[$i]['status'] = $data['status'];
            $i++;
        }
        $columns = array(
            'Name',
            'Email',
            'Card No.',
            'Medi Assist Id',
            'Mobile No',
            'Registration Date',
            'Status',
        );

        $objCSV = new CSV();
        try {
            $resp = $objCSV->export($reportData, $columns, 'aml_ratnakar_corp_cardholders');
            exit;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage()));
            $this->_redirect($this->formatURL('/aml/ratnakarcardholders'));
        }
    }

    public function boiindexAction() {
        
    }

    public function boiremittersAction() {

        $this->title = 'AML Matched Boi Remitters Listing';
        $amlMater = new AMLMaster();
        $remitter = new Remit_Boi_Remitter();
        $user = Zend_Auth::getInstance()->getIdentity();
        $qurStr['id'] = $this->_getParam('id', 0);
        $qurStr['status'] = $this->_getParam('status');
        if ($qurStr['id'] && $qurStr['status']) {
            $param = array('status' => $qurStr['status'], 'by_ops_id' => $user->id);
            $remitter->updateRemitter($param, $qurStr['id']);
            $this->_helper->FlashMessenger(array('msg-success' => 'Status updated successfully'));
        }

        $retData = $amlMater->getBoiRemitters($id, $this->_getPage());
        $this->view->paginator = $amlMater->paginateByArray($retData, $this->_getPage());
    }

    public function exportboiremittersAction() {
        $this->title = 'Export AML Matched Boi Remitters Listing';
        $amlMater = new AMLMaster();
        $exportData = $amlMater->getBoiRemitters();
        $reportData = array();
        $i = 0;
        foreach ($exportData As $data) {
            $reportData[$i]['name'] = $data['name'];
            $reportData[$i]['email'] = $data['email'];
            $reportData[$i]['mobile'] = $data['mobile'];
            $reportData[$i]['date_created'] = $data['date_created'];
            $reportData[$i]['status'] = $data['status'];
            $i++;
        }
        $columns = array(
            'Name',
            'Email',
            'Mobile No',
            'Registration Date',
            'Status',
        );

        $objCSV = new CSV();
        try {
            $resp = $objCSV->export($reportData, $columns, 'aml_boi_remitters');
            exit;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage()));
            $this->_redirect($this->formatURL('/aml/boiremitters'));
        }
    }

    public function boiremitterdetailAction() {
        $this->title = 'AML Matched Boi Remitter Details';
        $amlMater = new AMLMaster();
        $remitter = new Remit_Boi_Remitter();
        $user = Zend_Auth::getInstance()->getIdentity();
        $qurStr['id'] = $this->_getParam('id', 0);
        $qurStr['status'] = $this->_getParam('status');
        if ($qurStr['id'] && $qurStr['status']) {
            $param = array('status' => $qurStr['status'], 'by_ops_id' => $user->id);
            $remitter->updateRemitter($param, $qurStr['id']);
            $this->_helper->FlashMessenger(array('msg-success' => 'Status updated successfully'));
            $this->_redirect($this->formatURL('/aml/boiremitters'));
        }

        $retData = $amlMater->getBoiRemitters($qurStr['id']);
        $this->view->remitter = $retData['remitter'];
        $this->view->paginator = $amlMater->paginateByArray($retData['aml'], $this->_getPage());
        $this->view->amlMater = $amlMater;
    }

    public function boibeneficiariesAction() {

        $this->title = 'AML Matched Boi Beneficiaries Listing';
        $amlMater = new AMLMaster();
        $user = Zend_Auth::getInstance()->getIdentity();
        $beneficiary = new Remit_Boi_Beneficiary();
        $qurStr['id'] = $this->_getParam('id', 0);
        $qurStr['status'] = $this->_getParam('status');
        if ($qurStr['id'] && $qurStr['status']) {
            $param = array('status' => $qurStr['status'], 'by_ops_id' => $user->id);
            $beneficiary->updateBeneficiaryDetails($param, $qurStr['id']);
            $this->_helper->FlashMessenger(array('msg-success' => 'Status updated successfully'));
        }
        $retData = $amlMater->getBoiBeneficiaries();
        $this->view->paginator = $amlMater->paginateByArray($retData, $this->_getPage());
    }

    public function exportboibeneficiariesAction() {

        $this->title = 'Export AML Boi Beneficiaries Listing';

        $amlMater = new AMLMaster();
        $exportData = $amlMater->getBoiBeneficiaries();
        $reportData = array();
        $i = 0;
        foreach ($exportData As $data) {
            $reportData[$i]['name'] = $data['name'];
            $reportData[$i]['email'] = $data['email'];
            $reportData[$i]['date_created'] = $data['date_created'];
            $reportData[$i]['status'] = $data['status'];
            $i++;
        }
        $columns = array(
            'Name',
            'Email',
            'Registration Date',
            'Status',
        );

        $objCSV = new CSV();
        try {
            $resp = $objCSV->export($reportData, $columns, 'aml_boi_beneficiaries');
            exit;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage()));
            $this->_redirect($this->formatURL('/aml/boibeneficiaries'));
        }
    }

    public function boibeneficiarydetailAction() {

        $this->title = 'AML Matched Boi Beneficiary Details';
        $amlMater = new AMLMaster();
        $user = Zend_Auth::getInstance()->getIdentity();
        $beneficiary = new Remit_Boi_Beneficiary();
        $qurStr['id'] = $this->_getParam('id', 0);
        $qurStr['status'] = $this->_getParam('status');
        if ($qurStr['id'] && $qurStr['status']) {
            $param = array('status' => $qurStr['status'], 'by_ops_id' => $user->id);
            $beneficiary->updateBeneficiaryDetails($param, $qurStr['id']);
            $this->_helper->FlashMessenger(array('msg-success' => 'Status updated successfully'));
            $this->_redirect($this->formatURL('/aml/boibeneficiaries'));
        }

        $retData = $amlMater->getBoiBeneficiaries($qurStr['id']);
        $this->view->beneficiary = $retData['beneficiary'];
        $this->view->paginator = $amlMater->paginateByArray($retData['aml'], $this->_getPage());
        $this->view->amlMater = $amlMater;
    }

    public function boicardholdersAction() {

        $this->title = 'AML Matched Boi Cardhoders Listing';
        $amlMater = new AMLMaster();
        $user = Zend_Auth::getInstance()->getIdentity();
        $cardholder = new Corp_Boi_Customers();
        $qurStr['id'] = $this->_getParam('id', 0);
        $qurStr['status'] = $this->_getParam('status');
        if ($qurStr['id'] && $qurStr['status']) {
            $param = array('status' => $qurStr['status'], 'by_ops_id' => $user->id);
            $cardholder->updateCardholder($param, $qurStr['id']);
            $this->_helper->FlashMessenger(array('msg-success' => 'Status updated successfully'));
        }

        $retData = $amlMater->getBoiCorpCardholders();
        $this->view->paginator = $amlMater->paginateByArray($retData, $this->_getPage());
    }

    public function boicardholderdetailAction() {

        $this->title = 'AML Matched Boi Cardhoder Details';
        $amlMater = new AMLMaster();
        $user = Zend_Auth::getInstance()->getIdentity();
        $cardholder = new Corp_Boi_Customers();
        $qurStr['id'] = $this->_getParam('id', 0);
        $qurStr['status'] = $this->_getParam('status');
        if ($qurStr['id'] && $qurStr['status']) {
            $param = array('status' => $qurStr['status'], 'by_ops_id' => $user->id);
            $cardholder->updateCardholder($param, $qurStr['id']);
            $this->_helper->FlashMessenger(array('msg-success' => 'Status updated successfully'));
            $this->_redirect($this->formatURL('/aml/boicardholders'));
        }

        $retData = $amlMater->getBoiCorpCardholders($qurStr['id']);
        $this->view->cardholder = $retData['cardholder'];
        $this->view->paginator = $amlMater->paginateByArray($retData['aml'], $this->_getPage());
        $this->view->amlMater = $amlMater;
    }

    public function exportboicardholdersAction() {

        $this->title = 'Export AML Boi Cardholders Listing';

        $amlMater = new AMLMaster();
        $exportData = $amlMater->getBoiCorpCardholders();
        $reportData = array();
        $i = 0;
        foreach ($exportData As $data) {
            $reportData[$i]['name'] = $data['name'];
            $reportData[$i]['email'] = $data['email'];
            $reportData[$i]['card_number'] = $data['card_number'];
            $reportData[$i]['member_id'] = $data['member_id'];
            $reportData[$i]['mobile'] = $data['mobile'];
            $reportData[$i]['date_created'] = $data['date_created'];
            $reportData[$i]['status'] = $data['status'];
            $i++;
        }
        $columns = array(
            'Name',
            'Email',
            'Card No.',
            'Member Id',
            'Mobile No',
            'Registration Date',
            'Status',
        );

        $objCSV = new CSV();
        try {
            $resp = $objCSV->export($reportData, $columns, 'aml_boi_cardholders');
            exit;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage()));
            $this->_redirect($this->formatURL('/aml/boicardholders'));
        }
    }

}
