<?php

/**
 * Ops can view Reports 
 *
 * @package frontend_controllers
 * @copyright company
 */
class Corp_Boi_ReportsController extends App_Operation_Controller {

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

    /**
     * Controller's entry point
     *
     * @access public
     * @return void
     */
    public function indexAction() {
        
    }

    private function getReportTitle($reportTitle, $dur, $agentId = 0, $singleDayOnly = false, $bankUnicode = '') {

        $title = $reportTitle;
        if ($agentId > 0) {
            $objAgent = new Agents();
            $agentInfo = $objAgent->findById($agentId);
            $title .= ' For ' . $agentInfo->name;
        }
        if ($bankUnicode != '') {
            $objBank = new Banks();
            $bankInfo = $objBank->getBankbyUnicode($bankUnicode);
            $title .= ' of ' . $bankInfo->name;
        }
        if (!$singleDayOnly) {
            $durationArr = Util::getDurationDates($dur);
            $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
            $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
            switch ($dur) {
                case 'yesterday':
                    $title .= ' For ' . $toDate[0];
                    break;
                case 'today':
                    $title .= ' For ' . $toDate[0];
                    break;
                case 'week':
                case 'month':
                case 'default':
                    $title .= ' For ' . $fromDate[0] . ' to ' . $toDate[0];
                    break;
            }
        } else {
            $dt = explode(' ', Util::returnDateFormatted($dur, "Y-m-d", "d-m-Y", "-"));
            ;
            $title .= ' For ' . $dt[0];
        }

        return $title;
    }

    /*
     * Consolidated Debit Mandate Amount report
     */

    public function debitmandateamountAction() {
        $this->title = 'Debit Mandate Amount Report';
        // Get our form and validate it
        $form = new Corp_Boi_DebitMandateAmountForm(array('action' => $this->formatURL('/corp_boi_reports/debitmandateamount'),
            'method' => 'POST',
        ));

        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['btn_submit'] = $this->_getParam('btn_submit');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['submit'] = $this->_getParam('submit');
        $page = $this->_getParam('page');


        if ($qurStr['btn_submit']) {
            if ($form->isValid($qurStr)) {

                if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {

                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                    $titleDate['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $titleDate['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $pageTitle = 'Debit Mandate Amount Report from ' . Util::returnDateFormatted($titleDate['from'], "Y-m-d", "d-m-Y", "-");
                    $pageTitle .= ' to ' . Util::returnDateFormatted($titleDate['to'], "Y-m-d", "d-m-Y", "-");
                } else {
                    $this->_helper->FlashMessenger(array('msg-error' => 'Invalid date range'));
                }

                $customerModel = new Corp_Boi_Customers();
                $custArr = $customerModel->getDebitMandateAmount($qurData);

                $paginator = $customerModel->paginateByArray($custArr, $page, $paginate = NULL);
                $this->view->paginator = $paginator;
                $this->view->pageTitle = $pageTitle;
                $this->view->btnSubmit = $qurStr['btn_submit'];
            }
        }
        $this->view->form = $form;
        $this->view->formData = $qurStr;
    }

    /* exportfeereportAction() is responsible to create the csv file on fly with fee data of agents
     * and let user download that file.
     */

    public function exportdebitmandateamountAction() {
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $form = new Corp_Boi_DebitMandateAmountForm(array('action' => $this->formatURL('/corp_boi_reports/exportdebitmandateamount'),
            'method' => 'POST',
        ));

        if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {

            if ($form->isValid($qurStr)) {
                if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                }


                $customerModel = new Corp_Boi_Customers();
                $exportData = $customerModel->exportGetDebitMandateAmount($qurData);

                $columns = array(
                    'Account Number',
                    'Card Number',
                    'CRN',
                    'Legal ID / Member ID',
                    'Cust ID',
                    'Debit Mandate Amount',
                    'Wallet A (Restricted)',
                    'Status',
                    'Aadhaar No',
                );

                $objCSV = new CSV();
                try {
                    $resp = $objCSV->export($exportData, $columns, 'debit_mandate_amount');
                    exit;
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                    $this->_redirect($this->formatURL('/corp_boi_reports/exportdebitmandateamount?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date']));
                }
            } else {
                $this->_helper->FlashMessenger(array('msg-error' => 'Invalid data'));
                $this->_redirect($this->formatURL('/corp_boi_reports/exportdebitmandateamount?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date']));
            }
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Invalid duration'));
            $this->_redirect($this->formatURL('/corp_boi_reports/exportdebitmandateamount?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date']));
        }
    }
//start rnew code
public function walletbalanceAction() {
        $this->title = 'Wallet Balance Report';
        // Get our form and validate it
        $form = new Corp_Boi_WalletBalanceForm(array('action' => $this->formatURL('/corp_boi_reports/walletbalance'),
            'method' => 'POST',
        ));

        $qurStr['duration'] = $this->_getParam('duration');
        $qurStr['btn_submit'] = $this->_getParam('btn_submit');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['submit'] = $this->_getParam('submit');
        $page = $this->_getParam('page');


        if ($qurStr['btn_submit']) {
            if ($form->isValid($qurStr)) {

                if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {

                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                    $titleDate['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $titleDate['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $pageTitle = 'Wallet Balance Report from ' . Util::returnDateFormatted($titleDate['from'], "Y-m-d", "d-m-Y", "-");
                    $pageTitle .= ' to ' . Util::returnDateFormatted($titleDate['to'], "Y-m-d", "d-m-Y", "-");
                } else {
                    $this->_helper->FlashMessenger(array('msg-error' => 'Invalid date range'));
                }

                $customerModel = new Corp_Boi_Customers();
                $custArr = $customerModel->getWalletbalance($qurData);

                $paginator = $customerModel->paginateByArray($custArr, $page, $paginate = NULL);
                $this->view->paginator = $paginator;
                $this->view->pageTitle = $pageTitle;
                $this->view->btnSubmit = $qurStr['btn_submit'];
            }
        }
        $this->view->form = $form;
        $this->view->formData = $qurStr;
    }

    /* exportfeereportAction() is responsible to create the csv file on fly with fee data of agents
     * and let user download that file.
     */

    public function exportwalletbalanceAction() {
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $form = new Corp_Boi_WalletBalanceForm(array('action' => $this->formatURL('/corp_boi_reports/exportwalletbalance'),
            'method' => 'POST',
        ));

        if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {

            if ($form->isValid($qurStr)) {
                if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                }


                $customerModel = new Corp_Boi_Customers();
                $exportData = $customerModel->exportGetWalletbalance($qurData);

                $columns = array(
                    'Product Code',
                    'Bank Name',
                    'Aadhaar No',
                    'Currency ',
                    'Card Number ',
                    'CRN', 
                    'Legal ID / Member ID',
                    'Cust ID',
                    'Wallet A (Restricted)',
                    'Status',
                    'Auto Reversal date',
                );

                $objCSV = new CSV();
                try {
                    $resp = $objCSV->export($exportData, $columns, 'wallet_balance');
                    exit;
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                    $this->_redirect($this->formatURL('/corp_boi_reports/exportwalletbalance?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date']));
                }
            } else {
                $this->_helper->FlashMessenger(array('msg-error' => 'Invalid data'));
                $this->_redirect($this->formatURL('/corp_boi_reports/exportwalletbalance?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date']));
            }
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Invalid duration'));
            $this->_redirect($this->formatURL('/corp_boi_reports/exportwalletbalance?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date']));
        }
    }

    
    
    
    
    
    
    //start rnew code
    public function paymentstatusAction() {
        $this->title = 'Payment Status Report';
        
        
        
        // Get our form and validate it
        $form = new Corp_Boi_PaymentStatusForm(array('action' => $this->formatURL('/corp_boi_reports/paymentstatus'),
            'method' => 'POST',
        ));

        $qurStr['aof_number'] = $this->_getParam('aof_number');
        $qurStr['account_number'] = $this->_getParam('account_number');
        $qurStr['tp_name'] = $this->_getParam('tp_name');
        $qurStr['tp_account_number'] = $this->_getParam('tp_account_number');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        $qurStr['submit'] = $this->_getParam('submit');
        $page = $this->_getParam('page');
        
        
        if($qurStr['submit']) {
                
                if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {

                    $qurStr['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                    $qurStr['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                    $titleDate['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-");
                    $titleDate['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-");
                    $pageTitle = 'Payment Status Report from ' . Util::returnDateFormatted($titleDate['from'], "Y-m-d", "d-m-Y", "-");
                    $pageTitle .= ' to ' . Util::returnDateFormatted($titleDate['to'], "Y-m-d", "d-m-Y", "-");
                } else {
                    $this->_helper->FlashMessenger(array('msg-error' => 'Invalid date range'));
                }
                $customerModel = new Corp_Boi_Customers();
                $custArr = $customerModel->getPaymentstatus($qurStr);

                $paginator = $customerModel->paginateByArray($custArr, $page, $paginate = NULL);
                $this->view->paginator = $paginator;
                $this->view->pageTitle = $pageTitle;
                $this->view->sub = $qurStr['submit'];
        }
        $this->view->form = $form;
        $this->view->formData = $qurStr;
        $form->populate($qurStr);
    }

    /* exportfeereportAction() is responsible to create the csv file on fly with fee data of agents
     * and let user download that file.
     */

    public function exportpaymentstatusAction() {
        
        
        $qurStr['aof_number'] = $this->_getParam('aof_number');
        $qurStr['account_number'] = $this->_getParam('account_number');
        $qurStr['tp_name'] = $this->_getParam('tp_name');
        $qurStr['tp_account_number'] = $this->_getParam('tp_account_number');
        $qurStr['to_date'] = $this->_getParam('to_date');
        $qurStr['from_date'] = $this->_getParam('from_date');
        
        $form = new Corp_Boi_PaymentStatusForm(array('action' => $this->formatURL('/corp_boi_reports/exportpaymentstatus'),
            'method' => 'POST',
        ));

        if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {

            // if ($form->isValid($qurStr)) {
                if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
                    $qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-", "to");
                    $qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-", "from");
                }


                $customerModel = new Corp_Boi_Customers();
                $exportData = $customerModel->exportGetPaymentstatus($qurData);

                $columns = array(
                    'AOF Reference Number ',
                    'Account No.',
                    'Application Date',
                    'Branch Code',
                    'Title',
                    'Name (of the Trainee)', 
                    'Middle Name',
                    'Surname',
                    'Aadhaar Number',
                    'Aadhaar Enrollment ID',
                    'NSDC Enrollment Number',
                    'Debit Mandate Amount',
                    'Training Center ID',
                    'Traning Center Name',
                    'Training Partner Name',
                    'TP Account Number',   
                    'TP Bank name',
                    'TP IFSC',
                    'PAN',
                    'Gender',
                    'Date of Birth',
                    'Marital Status',
                    'Occupation',
                    'Permanent Address Line 1',
                    'Permanent Address Line 2',
                    'State',
                    'City',
                    'Pincode',
                    'Correspondence Address Line 1',
                    'Correspondence Address Line 2',
                    'Correspondence State',
                    'Correspondence City',
                    'Correspondence Pincode',
                    'Telephone',
                    'Mobile',
                    'Email',
                    'Nomination Flag',
                    'Nominee Name',
                    'Nominee_Relationship',
                    'Nominee DOB',
                    'Nominee Address 1',
                    'Nominee Address 2',
                    'Nominee City',
                    'Nominee Guardian',
                    'Card No',
                    'Authorizer Status',
                    'Checker Status',  
                    'ACTION DATE',
                    'UID',
                    'BoI Cust ID',
                    'Wallet Load date',
                    'Wallet load amount',
                    'Auto Reversal date',  
                    'Wallet balance',  
                    'Wallet status',   
                    
                );

                $objCSV = new CSV();
                try {
                    $resp = $objCSV->export($exportData, $columns, 'payment_status');
                    exit;
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                    $this->_redirect($this->formatURL('/corp_boi_reports/exportpaymentstatus?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date']));
                }
            // }
            
            //else {
            //    $this->_helper->FlashMessenger(array('msg-error' => 'Invalid data'));
            //    $this->_redirect($this->formatURL('/corp_boi_reports/exportpaymentstatus?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date']));
            // }
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Invalid duration'));
            $this->_redirect($this->formatURL('/corp_boi_reports/exportpaymentstatus?to_date=' . $qurStr['to_date'] . '&from_date=' . $qurStr['from_date']));
        }
    }
    
    
    
    //start rnew code
    public function rbiAction() {
        
        $form = new Corp_Boi_RbiReportForm(array('action' => $this->formatURL('/corp_boi_reports/rbi'),
            'method' => 'POST',
        ));
        $product = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NDSC);
        $productUnicode = $product->product->unicode;
        $productModel = new Products();
        $productInfo = $productModel->getProductInfoByUnicode($productUnicode);
        
        $qurStr['month'] = $this->_getParam('month');
        $qurStr['year'] = $this->_getParam('year');
        $currStr['month'] = $this->_getParam('month');
        $currStr['year'] = $this->_getParam('year');
         if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())){
                if($qurStr['month'] > date('m')  && $qurStr['year'] >= date('Y')){
                        $this->_helper->FlashMessenger( array('msg-error' => 'Selected month & year greater than todays date',) ); 
                }else{
                    if($qurStr['month']==1){
                        $qurStr['month']=12;
                        $qurStr['year']=$qurStr['year']-1;
                    }else{
                         $qurStr['month']=$qurStr['month']-1;
                    }
                    
                    $monthName = date("F", mktime(null, null, null, $qurStr['month']));
                    $currMonthName = date("F", mktime(null, null, null, $currStr['month']));
                    
                    $endDate = date($qurStr['year'].'-'.$qurStr['month'].'-t', strtotime($monthName." ".$qurStr['year']));
                    $startDate = $qurStr['year'].'-'.$qurStr['month'].'-01';
                    $qurStr['from_date'] = $startDate;
                    $qurStr['to_date'] = $endDate;
                    $this->title = 'Pre Paid Payment Statistics for the month of '.$currMonthName." ".$currStr['year'];
                    $this->view->pageTitle = 'Pre Paid Payment Statistics for the month of '.$currMonthName." ".$currStr['year'];
                    
                    $reportData = array();
                    
                    $cardMappingModel = new Corp_Boi_CardMapping();
                    $cardmappingArr = $cardMappingModel->getCustomerCount($startDate,$endDate);
                    
                    
                    
                    $cardLoadModel = new Corp_Boi_Cardload();
                    $cardLoad = $cardLoadModel->getSuccessfulLoads(array('from'=>$startDate, 'to'=>$endDate,'product_id'=>7));
                    
                      //  Debit
                    $qurStr['txn_type'] = TXNTYPE_CARD_DEBIT;
                    $qurStr['status'] = STATUS_DEBITED;
                    $qurStr['from'] = $startDate.' 00:00:00';
                    $qurStr['to'] = $endDate.' 23:59:59';
                    $qurStr['rbi_report'] = TRUE;
                    $qurStr['product_id'] = $productInfo->id;
                    
                    $boiDebit = $cardLoadModel->getTotalLoad($qurStr);
                    $boiDebit['total_load_amount'] = isset($boiDebit['total_load_amount'])? $boiDebit['total_load_amount']: 0;
                    $boiDebit['cnt'] = isset($boiDebit['cnt'])? $boiDebit['cnt']: 0;
                    
                    
                    $reportData['instrument_type'] = "NSDC";
                    $reportData['outstansding_balance_in_escrow'] = "-NA-";
                    $reportData['instruments_issued_with_reloadable'] = $cardmappingArr->mapped_customer;
                    $reportData['loading_unloading_transactions_details_count'] = $cardLoad->count;
                    $reportData['loading_unloading_transactions_details_amt'] = $cardLoad->total;
                    $reportData['number_of_retailer'] = "-NA-";
                    $reportData['denomination_of_pre_paid'] = "-NA-";
                    $reportData['pre_paid_instruments_issued_count'] = "-NA-";
                    $reportData['pre_paid_instruments_issued_amt'] = "-NA-";
                    $reportData['total_number_of_payment_transactions_card'] = $boiDebit['cnt'];
                    $reportData['total_number_of_payment_transactions_storage'] = "-NA-";
                    $reportData['total_number_of_value_transactions_card'] = $boiDebit['total_load_amount'];
                    $reportData['total_number_of_value_transactions_storage'] = "-NA-";
                    $reportData['number_of_pre_paid_card_terminals'] = "-NA-";
                    $reportData['number_of_loading_unloading_terminals'] = "-NA-";
                    $reportData['number_of_pre_paid_card_payment_terminals'] = "-NA-";
                    $reportData['pre_paid_instruments_outstanding_number'] = "-NA-";
                    $reportData['pre_paid_instruments_outstanding_amount'] = "-NA-";
                    
                    
                    $this->view->btnSubmit = true;
                    if(count($reportData)){
                        $this->view->showExport = true;
                    }
                }    
            }
        }  
        $this->view->form = $form;
        $this->view->formData = $qurStr;    
        //$this->view->pageTitle = $pageTitle;
        $this->view->reportData = $reportData;
        $form->populate($currStr);
        
        
    }

    /* exportfeereportAction() is responsible to create the csv file on fly with fee data of agents
     * and let user download that file.
     */

    public function exportrbiAction() {
        $endDate = $this->_getParam('to_date');
        $startDate = $this->_getParam('from_date');
        $qurStr = array();
        if ($startDate != '' && $endDate != '') {
            
                    
                $reportData = array();
                
                $cardMappingModel = new Corp_Boi_CardMapping();
                $cardmappingArr = $cardMappingModel->getCustomerCount($startDate,$endDate);
                               
                $cardLoadModel = new Corp_Boi_Cardload();
                $cardLoad = $cardLoadModel->getSuccessfulLoads(array('from'=>$startDate, 'to'=>$endDate,'product_id'=>7));
                //  Debit
                $qurStr['txn_type'] = TXNTYPE_CARD_DEBIT;
                $qurStr['status'] = STATUS_DEBITED;
                $qurStr['from'] = $startDate . ' 00:00:00';
                $qurStr['to'] = $endDate . ' 23:59:59';
                $qurStr['rbi_report'] = TRUE;
                $qurStr['product_id'] = $this->_getParam('product_id');

                $boiDebit = $cardLoadModel->getTotalLoad($qurStr);
                $boiDebit['total_load_amount'] = isset($boiDebit['total_load_amount']) ? $boiDebit['total_load_amount'] : 0;
                $boiDebit['cnt'] = isset($boiDebit['cnt']) ? $boiDebit['cnt'] : 0;

                $reportData[] = '-NA-';
                $reportData[] = $cardmappingArr->mapped_customer;
                $reportData[] = $cardLoad->count;
                $reportData[] = $cardLoad->total;
                $reportData[] = '-NA-';
                $reportData[] = '-NA-';
                $reportData[] = $boiDebit['cnt'];
                $reportData[] = $boiDebit['total_load_amount'];
                $reportData[] = '-NA-';
                $reportData[] = '-NA-';
           		
  
                $columns = array(
                    'Outstansding balance in Escrow A/c',
                    'Number of Pre Paid Instruments Issued with reloadable function.',
                    'Pre Paid Instruments loading/ Unloading transactions Details (COUNT)',
                    'Pre Paid Instruments loading/ Unloading transactions Details (AMOUNT)',
                    'Pre Paid Instruments issued during the month (COUNT)',
                    'Pre Paid Instruments issued during the month (AMOUNT)', 
                    'Total Number of Payment Transactions',
                    'Total Value of Payment Transactions',
                    'Pre Paid Instruments Outstanding (COUNT)',
                    'Pre Paid Instruments Outstanding (AMOUNT)',
                );
                $exportData = array($reportData);
                //print_r($exportData); exit;
                $objCSV = new CSV();
                try {
                    $resp = $objCSV->export($exportData, $columns, 'BOI_NSDC_RBI_Reporting');
                    exit;
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                     $this->_redirect($this->formatURL('/corp_boi_reports/exportrbi?month=' . date("m",strtotime($qurStr['to_date'])) . '&year=' . date("Y",strtotime($qurStr['to_date']))));
                }
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Invalid duration'));
            $this->_redirect($this->formatURL('/corp_boi_reports/exportrbi?month=' . date("m",strtotime($qurStr['to_date'])) . '&year=' . date("Y",strtotime($qurStr['to_date']))));
        }
    }

     
    
    public function tpmisreportAction(){
        $this->title = 'TP MIS Report';              
         // Get our form and validate it
         $form = new Corp_Boi_TpmisReportForm(array('action' => $this->formatURL('/corp_boi_reports/tpmisreport'),
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
        $qurStr['ref_num'] = $this->_getParam('ref_num');
        $qurStr['aadhaar_no'] = $this->_getParam('aadhaar_no');
        $qurStr['account_no'] = $this->_getParam('account_no');
        $qurStr['sub'] = $sub;
        
      
         if($sub!=''){ 
              if($form->isValid($qurStr)){ 
                
                
                 $this->view->title = 'TP MIS Report';
                    
                 $qurData['ref_num'] = $qurStr['ref_num'];
                 $qurData['aadhaar_no'] = $qurStr['aadhaar_no'];
                 $qurData['account_no'] = $qurStr['account_no'];
                 $qurData['product_id'] = $qurStr['product_id'];
                 $objReports = new Corp_Boi_Customers();
                 $cardholders = $objReports->getTPMisDetails($qurData);
                 
                 $this->view->paginator = $objReports->paginateByArray($cardholders, $page, $paginate = NULL);
                 $this->view->formData = $qurStr;
                 $form->populate($qurStr);
              }   
             
          }
            $this->view->form = $form;
            
    }
     public function exporttpmisreportAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
         // Get our form and validate it
         $form = new Corp_Boi_ConsolidatedReportForm(array('action' => $this->formatURL('/corp_boi_reports/exporttpmisreport'),
                                                    'method' => 'POST',
                                             )); 
        $qurStr['product_id'] = $this->_getParam('product_id');
        $qurStr['aadhaar_no'] = $this->_getParam('aadhaar_no');
        $qurStr['account_no'] = $this->_getParam('account_no');
        $qurStr['ref_num'] = $this->_getParam('ref_num');
             
              if($form->isValid($qurStr)){ 
               
                 $qurData['aadhaar_no'] = $qurStr['aadhaar_no'];
                 $qurData['account_no'] = $qurStr['account_no'];
                 $qurData['product_id'] = $qurStr['product_id'];
                 $qurData['ref_num'] = $qurStr['ref_num'];
                 $objReports = new Corp_Boi_Customers();
                 $exportData = $objReports->getTPMisDetails($qurData);
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
                    'Available Balance on Wallet',
                    'Amount debited through POS',
                    'Wallet Auto Debit Date',
                    'Wallet Auto Debit Amount',
                    'Traning Center BC Name',
                    'Training Center ID',
                    'Training Center Name',
                    'Training Partner Name',
                );


                $objCSV = new CSV();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'tp_mis_report');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/corp_boi_reports/exporttpmisreport?sub=1&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&ref_num='.$qurStr['ref_num'].'&nsdc_enrollment_no='.$qurStr['nsdc_enrollment_no'].'&date_approval='.$qurStr['date_approval'].'&aadhaar_no='.$qurStr['aadhaar_no'].'&wallet_load_status='.$qurStr['wallet_load_status'].'&agent_id='.$qurStr['agent_id'].'&account_no='.$qurStr['account_no'])); 
                                       }
                 
               } else {
                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
                         $this->_redirect($this->formatURL('/corp_boi_reports/exporttpmisreport?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&ref_num='.$qurStr['ref_num'].'&nsdc_enrollment_no='.$qurStr['nsdc_enrollment_no'].'&date_approval='.$qurStr['date_approval'].'&aadhaar_no='.$qurStr['aadhaar_no'].'&wallet_load_status='.$qurStr['wallet_load_status'].'&agent_id='.$qurStr['agent_id'].'&account_no='.$qurStr['account_no'])); 
                      }             
     
       }
       
       
       
       
       
       public function tpmisgenericreportAction(){
         $this->title = 'TP MIS Report'; 
         
        // Get our form and validate it
         $form = new Corp_Boi_TpmisGenericReportForm(array('action' => $this->formatURL('/corp_boi_reports/tpmisgenericreport'),
                                                    'method' => 'POST',
                                             )); 
        $formData  = $this->_request->getPost();
        $user = Zend_Auth::getInstance()->getIdentity();    
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
        $isError = FALSE;
        
        $qurStr['product_id'] = $productId['id'];
        $qurStr['tp_mobile'] = $this->_getParam('tp_mobile');
        $qurStr['agent_mobile'] = $this->_getParam('agent_mobile');
        $qurStr['agent_code'] = $this->_getParam('agent_code');
        $qurStr['tp_code'] = $this->_getParam('tp_code');
        $qurStr['wallet_load_from'] = $this->_getParam('wallet_load_from');
        $qurStr['wallet_load_to'] = $this->_getParam('wallet_load_to');
        $qurStr['sub'] = $sub;
        
         if($sub!=''){ 
              //if($form->isValid($qurStr)){
                 $this->view->title = 'TP MIS Generic Report';
                 $agentModel = new Agents();
                 if($qurStr['tp_mobile'] != ''){
                     $agentId = $agentModel->isDistByphone($qurStr['tp_mobile']);
                     if(!$agentId){
                      $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'TP with mobile no. does not exists',
                    )
                   ); 
                      $isError = TRUE;
                     }
                     
                 }
                  
                 if($qurStr['agent_mobile'] != ''){
                     $agentId = $agentModel->isAgentByphone($qurStr['agent_mobile']);
                     if(!$agentId){
                      $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Agent with mobile no. does not exists',
                    )
                   ); 
                      $isError = TRUE;
                     }
                     
                 }
                  if($qurStr['tp_code'] != '' && $qurStr['agent_code'] == ''){
                     $agentId = $agentModel->isDistByAgentCode($qurStr['tp_code']);
                     if(!$agentId){
                      $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'TP with Code does not exists',
                    )
                   ); 
                      $isError = TRUE;
                     }
                     
                 }
                  if($qurStr['agent_code'] != '' && $qurStr['tp_code'] == ''){
                     $agentId = $agentModel->isAgentByAgentCode($qurStr['agent_code']);
                     if(!$agentId){
                      $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Agent with Code does not exists',
                    )
                   ); 
                      $isError = TRUE;
                     }
                     
                 }
                 if($qurStr['agent_code'] != '' && $qurStr['tp_code'] != ''){
                     $agentId = $agentModel->findagentByAgentCode($qurStr['tp_code']);
                     if(!empty($agentId)){
                     $checkBC = $agentModel->getBCListing(
                        array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $agentId['id'], 'user_type' => DISTRIBUTOR_AGENT_DB_VALUE, 'ret_type' => 'arr'));
                     $agtId = $agentModel->findagentByAgentCode($qurStr['agent_code']);
                     
                     $pos = in_array($agtId['id'], $checkBC);
                     if ($pos === false) {
                    $this->_helper->FlashMessenger(
                            array(
                                'msg-error' => 'Please enter valid BC code',
                            )
                    );
                   $isError = TRUE;   
                   }
                  
                     }
                   
                 }
                if(!$isError){
                 $qurData = array(
                        'tp_mobile' => $formData['tp_mobile'],
                        'agent_mobile' => $formData['agent_mobile'],
                        'tp_code' => $formData['tp_code'],
                        'agent_code' => $formData['agent_code'],
                        //'wallet_load_from' => $formData['from_date'],
                        //'wallet_load_to' => $formData['to_date'],
                        'file_name' => '',
                        'date_request' => new Zend_Db_Expr('NOW()'),
                        'date_processed' => '',
                        'by_ops_id' => $user->id,
                        'status' => STATUS_PENDING,
                    );
                 
                 if ($qurStr['wallet_load_to'] != '' && $qurStr['wallet_load_from'] != '') {
                    $qurData['wallet_load_to'] = Util::returnDateFormatted($qurStr['wallet_load_to'], "d-m-Y", "Y-m-d", "-", "-");
                    $qurData['wallet_load_from'] = Util::returnDateFormatted($qurStr['wallet_load_from'], "d-m-Y", "Y-m-d", "-", "-");
                }
                 
                 $rs = array();
                 
                 $objReports = new Corp_Boi_Tpmis();
                 $return1 = $objReports->SaveTPMisGenericDetails($qurData);
                 
                 if($return1['status'] == 'in_process'){
                     $msg = 'Request is already being processed. Please check in sometime.'; 
                 }
                 elseif($return1['status'] == 'submitted'){
                     $msg = 'Request is submitted successfully. Please check in sometime.'; 
                 }
                 elseif($return1['status'] == 'processed'){
                     $rs = $return1['rs'];
                 }
                 else{
                    $msg = 'No. records found';  
                 }
                 
                 if($msg != ''){
                 $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => $msg,
                    )
                    );
                 }
         
                 $this->view->paginator = $objReports->paginateByArray($rs, $page, $paginate = NULL);
                 $this->view->formData = $qurStr;
                 }
                 $form->populate($qurStr);
            // }   
             
          }
            $this->view->form = $form;
            
    }
//     public function exporttpmisgenericreportAction(){
//        $this->_helper->viewRenderer->setNoRender(true);
//        $this->_helper->layout()->disableLayout();
//         // Get our form and validate it
//         $form = new Corp_Boi_ConsolidatedReportForm(array('action' => $this->formatURL('/corp_boi_reports/exporttpmisreport'),
//                                                    'method' => 'POST',
//                                             )); 
//        $qurStr['product_id'] = $this->_getParam('product_id');
//        $qurStr['aadhaar_no'] = $this->_getParam('aadhaar_no');
//        $qurStr['account_no'] = $this->_getParam('account_no');
//        $qurStr['ref_num'] = $this->_getParam('ref_num');
//             
//              if($form->isValid($qurStr)){ 
//               
//                 $qurData['aadhaar_no'] = $qurStr['aadhaar_no'];
//                 $qurData['account_no'] = $qurStr['account_no'];
//                 $qurData['product_id'] = $qurStr['product_id'];
//                 $qurData['ref_num'] = $qurStr['ref_num'];
//                 $objReports = new Corp_Boi_Customers();
//                 $exportData = $objReports->getTPMisDetails($qurData);
//                // column names & indexes
//                $columns = array(
//                    'AOF Reference Number',
//                    'Application Date',
//                    'Name (of the Trainee)',
//                    'NSDC Enrollment Number',
//                    'Aadhaar No.',
//                    'Linked Branch ID',
//                    'Transerv Status',
//                    'Bank Status',
//                    'Account No.',
//                    'IFSC Code',
//                    'Card No.',
//                    'Debit Mandate Amount',
//                    'NSDC Wallet Load Date',
//                    'NSDC Load Amount',
//                    'Available Balance on Wallet',
//                    'Amount debited through POS',
//                    'Wallet Auto Debit Date',
//                    'Wallet Auto Debit Amount',
//                    'Traning Center BC Name',
//                    'Training Center ID',
//                    'Training Center Name',
//                    'Training Partner Name',
//                );
//
//
//                $objCSV = new CSV();
//                 try{
//                        $resp = $objCSV->export($exportData, $columns, 'tp_mis_report');exit;
//                 }catch (Exception $e) {
//                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
//                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
//                                         $this->_redirect($this->formatURL('/corp_boi_reports/exporttpmisreport?sub=1&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&ref_num='.$qurStr['ref_num'].'&nsdc_enrollment_no='.$qurStr['nsdc_enrollment_no'].'&date_approval='.$qurStr['date_approval'].'&aadhaar_no='.$qurStr['aadhaar_no'].'&wallet_load_status='.$qurStr['wallet_load_status'].'&agent_id='.$qurStr['agent_id'].'&account_no='.$qurStr['account_no'])); 
//                                       }
//                 
//               } else {
//                         $this->_helper->FlashMessenger( array('msg-error' => 'Invalid Paramaters!') );
//                         $this->_redirect($this->formatURL('/corp_boi_reports/exporttpmisreport?dur='.$qurStr['dur'].'&sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date'].'&product_id='.$qurStr['product_id'].'&bank_unicode='.$qurStr['bank_unicode'].'&status='.$qurStr['status'].'&ref_num='.$qurStr['ref_num'].'&nsdc_enrollment_no='.$qurStr['nsdc_enrollment_no'].'&date_approval='.$qurStr['date_approval'].'&aadhaar_no='.$qurStr['aadhaar_no'].'&wallet_load_status='.$qurStr['wallet_load_status'].'&agent_id='.$qurStr['agent_id'].'&account_no='.$qurStr['account_no'])); 
//                      }             
//     
//       }
       
    public function tpmisgeneratedfilesAction() {

        $this->title = 'TP MIS Generated Report';
        $tpmisModel = new Corp_Boi_Tpmis();
        $page = $this->_getParam('page');
        $row = $tpmisModel->getGeneratedFileDetails($page);
        $this->view->backlink = '/corp_boi_reports/tpmisgenericreport/' ;
        $this->view->title = 'TP MIS Generated Report';
        $this->view->paginator = $row;
    }
}
