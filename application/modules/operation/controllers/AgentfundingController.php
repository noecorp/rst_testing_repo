<?php

/**
 * Default entry point in the application
 *
 * @package frontend_controllers
 * @copyright company
 */
class AgentfundingController extends App_Agent_Controller {

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

        $this->title = 'Agent Fund Requests';
    }

    /**
     * Action uploadbankstatement
     * User upload banks statement file
     * @access public
     * @return void
     */
    public function uploadbankstatementAction() {

        $this->title = $this->view->title = 'Upload Bank Statement';

        $form = new UploadBankStatementForm(array('method' => 'post',
            'id' => 'form-upload-bank-statement')
        );
        $this->view->form = $form;
        //After Upload file 
        $session = new Zend_Session_Namespace('App.Operation.Controller');
        if (isset($session->msg)) {

            $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => $session->msg,
                    )
            );
            unset($session->msg); //After show msg remove it from session
        }

        $formData = $this->getRequest()->getPost();
        if (isset($formData['submit'])) {

            if ($form->isValid($formData)) {
                if (!isset($_FILES['upload']['name']) || empty($_FILES['upload']['name'])) {
                    $isError = TRUE;
                    $errMsg = 'Please upload bank statement file';
                } else {
                    $destination = BANK_STATEMENT_UPLOAD_PATH . '/';

                    $upload = new Zend_File_Transfer_Adapter_Http();
                    $upload->addValidator('Extension', true, array('txt', 'case' => false))
                            ->addValidator('FilesSize', false, array('min' => '1kB', 'max' => 100 * 1024))
                            ->setDestination($destination);

                    $pathinfo = pathinfo($upload->getFileName());
                    //Array to Var
                    $renameFile = time() . '.' . (isset($pathinfo['extension']) ? $pathinfo['extension'] : '');
                    $upload->addFilter(new Zend_Filter_File_Rename(array('target' => $renameFile)));

                    try {

                        //All validations correct then upload file
                        if ($upload->isValid()) {
                            // upload received file
                            $upload->receive();
                            if ($upload->isUploaded()) {
                                $session = new Zend_Session_Namespace('App.Operation.Controller');
                                $session->uploadbankstatementFile = $destination . $renameFile;
                                $session->realBankStatementFileName = $_FILES['upload']['name'];
                                $this->_redirect($this->formatURL('/agentfunding/afteruploadbankstatement'));
                            }
                            $isError = false;
                        } else {
                            if ($pathinfo['extension'] != 'txt') {
                                $errMsg = 'Invalid file uploaded. Allowed Format is txt only.';
                            } elseif ($_FILES['upload']['size'] == 0) {
                                $errMsg = 'Bank statement file should not be blank.';
                            }
                            $isError = TRUE;
                        }
                    } catch (Zend_File_Transfer_Exception $e) {
                        $errMsg = $e->getMessage();
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $isError = TRUE;
                        unlink($destination . $renameFile);
                    }

                    if ($isError) {
                        $this->_helper->FlashMessenger(array('msg-error' => $errMsg));
                        $this->_redirect($this->formatURL('/agentfunding/uploadbankstatement'));
                    }
                }
            }
        }
    }

    /**
     * Action afteruploadbankstatement
     * After upload bank statment user redirect to this action
     * here we start process for insert bank statements and redirect
     * @access public
     * @return void
     * 
     */
    function afteruploadbankstatementAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $session = new Zend_Session_Namespace('App.Operation.Controller');
        $uploadFile = new UploadBankStatement($session->uploadbankstatementFile);
        $statements = $uploadFile->getStatements();
        $realBankStatementFileName = $session->realBankStatementFileName;

        //Insert IN To DB
        $bankStatement = new BankStatement();
        $result = $bankStatement->insertBankStatements($statements, $realBankStatementFileName);

        if ($result instanceof Exception) {
            $session->msg = 'There is some problem to upload in bank statment.';
            unlink($session->uploadbankstatementFile);
        } else {
            $session->msg = 'Bank statment upload successfully.';
        }

        $this->_redirect($this->formatURL('/agentfunding/uploadbankstatement'));
    }

    public function pendingfundrequestAction() {
        $this->title = 'Pending Fund Request';
        $msg = $this->_getParam('msg');
        if(!empty($msg)) {
            if($msg == 'success') {
                $this->_helper->FlashMessenger(
                        array(
                            'msg-success' => 'Bank statement settled successfully',
                        )
                );
            } else {
                $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => $msg,
                        )
                );
            }
        }
        
        $agentFunding = new AgentFunding();
        $this->view->paginator = $agentFunding->findAllPendingRequest($this->_getPage());
    }
    
    function exportpendingfundrequestAction() {
        $this->title = 'Export Pending Fund Request';
        $agentFunding = new AgentFunding();
        
         $columns = array(
                                     'Bank Name',
                                     'Product Name',
                                     'Agent Code',
                                     'Agent Name',
                                     'Amount',
                                     'Fund Transfer Type',
                                     'Journal/Cheque No.',
                                     'Request Date'
                                 );
                                  
                 $objCSV = new CSV();
                 $exportData = $agentFunding->exportAllPendingRequest();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'pending_fund_requests');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/agentfunding/pendingfundrequest')); 
                                       }
    }

    function confirmbeforesettlementAction() {
        $this->title = 'Approve Fund Request';
        $agentFundingObj = new AgentFunding();
        $bankStatement = new BankStatement();
        $id = $this->_getParam('id');
        $agentFunding = $agentFundingObj->getAgentFundingById($id);


        if ($agentFunding) {
            $this->view->agentFunding = $agentFunding;
            $this->view->bankStatements = $bankStatement->getAllUnsettledBankStatement(array('amount' => $agentFunding->amount));
            $session = new Zend_Session_Namespace('App.Operation.Controller');
            if (isset($session->msg)) {
                $this->view->msg = $session->msg;
                unset($session->msg); //After show msg remove it from session
            }

            $formData = $this->getRequest()->getPost();
            if (isset($formData['submit'])) {
                $session->viewPendingRequestFormData = $formData;
                $this->_redirect($this->formatURL('/agentfunding/confirmsettlement'));
            }
        }
    }

    function confirmsettlementAction() {
        $this->title = 'Confirm Settlement';

        $session = new Zend_Session_Namespace('App.Operation.Controller');
        $formData = $session->viewPendingRequestFormData;
        if (!isset($formData['submit'])) {
            //Condition: If User direct open this page 
            $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Invalid agent funding request id or bank statement id',
                    )
            );

            $this->_redirect($this->formatURL('/agentfunding/pendingfundrequest'));
        } else {
            $agent_funding_id = isset($formData['agent_funding_id']) ? $formData['agent_funding_id'] : $this->_getPage('id');
            $statement_id = $formData['statement_id'];

            //Condition: If user post empty values for $agent_funding_id or $statement_id 
            if (empty($agent_funding_id)) {
                $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'Invalid agent funding request id',
                        )
                );
                $this->_redirect($this->formatURL('/agentfunding/pendingfundrequest'));
            } elseif (empty($statement_id)) {
                $session->msg = 'Please select bank statement';
                $this->_redirect($this->formatURL('/agentfunding/confirmbeforesettlement?id=' . $agent_funding_id));
            } else {
                $agentFunding = new AgentFunding();
                $agentFundingRow = $agentFunding->getAgentFundingById($agent_funding_id);

                $bankStatement = new BankStatement();
                $bankStatementRow = $bankStatement->getUnsettledBankStatementById($statement_id);

                if (is_null($agentFundingRow) || is_null($bankStatementRow)) {
                    $session = new Zend_Session_Namespace('App.Operation.Controller');
                    $session->msg = 'Invalid agent funding request id or bank statement id';
                    $this->_redirect($this->formatURL('/agentfunding/confirmbeforesettlement?id=' . $agent_funding_id));
                } else {
                    $this->view->agentFunding = $agentFundingRow;
                    $this->view->bankStatement = $bankStatementRow;
                }
            }
        }
    }

    function dosettlementAction() {
        $this->title = 'Settlement';
        $session = new Zend_Session_Namespace('App.Operation.Controller');
        $firstFormData = $session->viewPendingRequestFormData;
        $formData = $this->getRequest()->getPost();
        if (isset($formData['submit'])) {
            $agent_funding = $formData['agent_funding_id'] == $firstFormData['agent_funding_id'];
            $statement = $formData['statement_id'] == $firstFormData['statement_id'];

            if (!$agent_funding || !$statement) { //If First Form value is not equal to Second form
                $session->msg = 'Invalid agent funding request id or bank statement id';
                $this->_redirect($this->formatURL('/agentfunding/confirmbeforesettlement?id=' . $firstFormData['agent_funding_id']));
            } else {
                $agentFunding = new AgentFunding();
                $bankStatement = new BankStatement();
                $user = Zend_Auth::getInstance()->getIdentity();

                $agentFundingRow = $agentFunding->findById($firstFormData['agent_funding_id']);
                $bankStatementRow = $bankStatement->findById($firstFormData['statement_id']);

                $msg = $agentFunding->settledFundRequest($agentFundingRow, $bankStatementRow, BY_OPS, $user->id, $formData['settlement_remarks'], $firstFormData['agent_funding_id']);
//                if($msg == 'success') {
//                    $this->_helper->FlashMessenger(
//                            array(
//                                'msg-success' => 'Bank statement settled successfully',
//                            )
//                    );
//                } else {
//                    $this->_helper->FlashMessenger(
//                            array(
//                                'msg-error' => $msg,
//                            )
//                    );
//                }
                $this->_redirect($this->formatURL('/agentfunding/pendingfundrequest/msg/'.$msg));
            }
        }
    }

    function unsettledbankstatementAction() {
        $this->title = 'Unsettled Bank Statement';
        $bankStatement = new BankStatement();
        $this->view->bankStatements = $bankStatement->getAllUnsettledBankStatement();
    }
   
    function exportunsettledbankstatementAction() {
        $this->title = 'Unsettled Bank Statement';
        $bankStatement = new BankStatement();
        
         $columns = array(
            'Journal/Cheque No',
            'Description',
            'Amount',
            'Date',
        );
                                  
                 $objCSV = new CSV();
                 $exportData = $bankStatement->exportAllUnsettledBankStatement();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'unsettled_bank_statement');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/agentfunding/unsettledbankstatement')); 
                                       }
    }
    
    function settledfundrequestAction() {
        $this->title = 'Settled Fund Request';
        $agentFundingObj = new AgentFunding();
       // $agent_funding =  $agentFundingObj->getAllApprovedFundRequestWithSettledBankStatement($this->_getPage());
        $this->view->agentFunding = $agentFundingObj->getAllApprovedFundRequestWithSettledBankStatement($this->_getPage());
    }

    
     function exportsettledfundrequestAction() {
        $this->title = 'Settled Fund Request';
        $agentFundingObj = new AgentFunding();
        
         $columns = array(
            'Agent Code',
            'Agent Name',
            'Fund Transfer Type',
            'Journal/Cheque No.',
            'Amount',
            'Details',
            'Settled By',
            'Settlement Remarks',
            'Request Date',
            'Settlement Date',
            'Status',
            'Product Name',
            'Bank Name'
             
        );

        $objCSV = new CSV();
                 $exportData = $agentFundingObj->exportAllApprovedFundRequestWithSettledBankStatement();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'settled_fund_request');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/agentfunding/settledfundrequest')); 
                                       }
    }
    function rejectfundrequestAction() {

        $formData = $this->getRequest()->getPost();

        if (!isset($formData['submit'])) {
            $msg = array('msg-error' => 'Invalid fund request id');
        } else {
            $id = $this->_getParam('agent_funding_id');
            $agentFundingObj = new AgentFunding();
            $agentFunding = $agentFundingObj->getNonApprovedAgentFundingId($id);
            if (!$agentFunding) {
                $msg = array('msg-error' => 'Invalid fund request id');
            } else {
                $user = Zend_Auth::getInstance()->getIdentity();
                $agentFundingObj->rejectFundRequest($agentFunding, $user->id, $formData['settlement_remarks']);
                $msg = array('msg-success' => 'Fund request has been reject successfully');
            }
        }

        $this->_helper->FlashMessenger($msg);
        $this->_redirect($this->formatURL('/agentfunding/pendingfundrequest'));
    }

    function confirmbeforerejectfundrequestAction() {
        $this->title = 'Confirm Before Reject Fund Request';
        $id = $this->_getParam('id');
        $error = false;

        $errMsg = array(
            'msg-error' => 'Invalid fund request id',
        );

        if (empty($id)) {
            $error = TRUE;
        } else {
            $agentFundingObj = new AgentFunding();
            $agentFunding = $agentFundingObj->getAgentFundingById($id);

            if (!$agentFunding) {
                $error = TRUE;
            } else {
                $this->view->agentFunding = $agentFunding;
                $this->view->form = new RejectFundRequestForm(
                        array(
                    'method' => 'post',
                    'id' => 'form-reject-fund-request',
                    'action' => $this->formatURL('/agentfunding/rejectfundrequest'),
                    'params' => array('agent_funding_id' => $agentFunding->agent_funding_id, 'cancelLink' => $this->formatURL('/agentfunding/pendingfundrequest'),)
                        )
                );
            }
        }
        if ($error) {
            $this->_helper->FlashMessenger(
                    $errMsg
            );
            $this->_redirect($this->formatURL('/agentfunding/pendingfundrequest'));
        }
    }
    
    /**
     * Action uploadkotakbanktatement
     * User upload banks statement file
     * @access public
     * @return void
     */
    public function uploadkotakbanktatementAction() {
        $this->title = $this->view->title = 'Upload Kotak/ Ratnakar/ ICICI Bank Statement';
        $submit = isset($formData['submit_success']) ? $formData['submit_success'] : '';
        $form = new UploadKotakBankStatementForm(array('method' => 'post',
            'id' => 'form-upload-bank-statement')
        );
        $this->view->form = $form;
        $bank = new Banks();
        //After Upload file 
        $session = new Zend_Session_Namespace('App.Operation.Controller');
        if (isset($session->msg)) {
            $this->_helper->FlashMessenger(
                    array(
                        'msg-success' => $session->msg,
                    )
            );
            unset($session->msg); //After show msg remove it from session
        }
        $formData = $this->getRequest()->getPost();
        if (isset($formData['submit'])) {
            if ($form->isValid($formData)) {
                if (!isset($_FILES['upload']['name']) || empty($_FILES['upload']['name'])) {
                    $isError = TRUE; 
                    $errMsg = 'Please upload bank statement file';
                } else { 
                    $destination = BANK_STATEMENT_UPLOAD_PATH . '/';
                    $bankunicode = $bank->getBankInfo($formData['bank_id'])->unicode;
                    $bankicici = App_DI_Definition_Bank::getInstance(BANK_ICICI);
                    $bankiciciUnicode = $bankicici->bank->unicode;
                    if($bankunicode == $bankiciciUnicode){
                        $upload = new Zend_File_Transfer_Adapter_Http();
                        $upload->addValidator('Extension', false, array('txt', 'case' => false))
                                ->setDestination($destination);
                        $pathinfo = pathinfo($upload->getFileName());
                        //Array to Var
                        $renameFile = time() . '.' . (isset($pathinfo['extension']) ? $pathinfo['extension'] : '');
                        $upload->addFilter(new Zend_Filter_File_Rename(array('target' => $renameFile)));
                        try {
                            if ($upload->isValid()) {
                                $upload->receive();
                                if ($upload->isUploaded()) {
                                    $uploadkotakbankstatementFile = $destination . $renameFile;
                                    $fp = fopen($uploadkotakbankstatementFile, 'r');
                                    $data = array();
                                    while (!feof($fp)) {
                                        $line = fgets($fp);
                                        $line = trim($line);
                                        if (!empty($line)) {
                                            $delimiter = CORP_CARDHOLDER_UPLOAD_DELIMITER;
                                            $dataArr = str_getcsv($line, $delimiter);
                                            $arrLength = Util::getArrayLength($dataArr);
                                            if (!empty($dataArr) && ($arrLength==ICICI_BANK_STATEMENT_COLUMNS)) {
                                                try {
                                                    $utr = $dataArr['4'];
                                                    $amount = $dataArr['2']; 
                                                    $txn_date = $dataArr['3'];   
                                                    if(trim($utr) == ''){
                                                        $dataArr['failed_reason'] = 'UTR No. can not be blank';
                                                        $dataFailed[] = $dataArr;
                                                    } elseif(trim($amount) == ''){
                                                        $dataArr['failed_reason'] = 'Amount can not be blank';
                                                        $dataFailed[] = $dataArr;
                                                    } elseif(!Util::validateAmount($amount)){
                                                        $dataArr['failed_reason'] = 'Amount : <strong>'. $amount .'</strong> is not numeric.';
                                                        $dataFailed[] = $dataArr;
                                                    } elseif(trim($txn_date) == ''){
                                                        $dataArr['failed_reason'] = 'Transaction Date can not be blank';
                                                        $dataFailed[] = $dataArr;
                                                    } elseif(!Util::dateValidCheck($txn_date,$separation ='dd/mm/yy')){
                                                        $dataArr['failed_reason'] = "Transaction Date : <b>$txn_date</b> is not valid date.";
                                                        $dataFailed[] = $dataArr;
                                                    } else {
                                                        $dataArr['bank_id'] = $formData['bank_id'];
                                                        $data[] = $dataArr;
                                                    }
                                                } catch (Exception $e) { 
                                                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                                }
                                            } else {
                                                $this->_helper->FlashMessenger(
                                                        array('msg-error' =>"Data format is not correct"));
                                                $this->_redirect($this->formatURL('/agentfunding/uploadkotakbanktatement'));
                                            }
                                        }
                                    }
                                    if((count($data)) || (count($dataFailed))){  
                                        $bankStatement = new BankStatement();
                                        $result = $bankStatement->addiciciBankStatements($data,$renameFile); 
                                        if ($result instanceof Exception) {
                                            $isError = TRUE;
                                            $errMsg = $result->getMessage();
                                            unlink($destination . $renameFile);
                                        } else {
                                            $isError = false; 
                                            $this->view->bank = BANK_ICICI;
                                            $this->view->rejectpaginator = $dataFailed ;
                                            $this->view->paginator = $data; 
                                        }
                                    }else{
                                        $errMsg = 'Bank statement file should not be blank.';
                                        $isError = TRUE;
                                    }
                                }
                            } else {
                                if ($pathinfo['extension'] != 'csv') {
                                    $errMsg = 'Invalid file uploaded. Allowed Format is Text only.';
                                } elseif ($_FILES['upload']['size'] == 0) {
                                    $errMsg = 'Bank statement file should not be blank.';
                                }
                                $isError = TRUE;
                            }
                        } catch (Zend_File_Transfer_Exception $e) {
                            $errMsg = $e->getMessage();
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            $isError = TRUE;
                            unlink($destination . $renameFile);
                        }
                    }else {
                        $upload = new Zend_File_Transfer_Adapter_Http();
                        $upload->addValidator('Extension', false, array('csv', 'case' => false))
                                ->setDestination($destination);
                        $pathinfo = pathinfo($upload->getFileName());
                        //Array to Var
                        $renameFile = time() . '.' . (isset($pathinfo['extension']) ? $pathinfo['extension'] : '');
                        $upload->addFilter(new Zend_Filter_File_Rename(array('target' => $renameFile)));
                        try {
                            //All validations correct then upload file
                            if ($upload->isValid()) {
                                // upload received file
                                $upload->receive();
                                if ($upload->isUploaded()) {
                                    $uploadkotakbankstatementFile = $destination . $renameFile;
                                    $fp = fopen($uploadkotakbankstatementFile, 'r');
                                    $data = array(); 
                                    while (!feof($fp)) {
                                        $line = fgets($fp);
                                        if (!empty($line)) {
                                            $delimiter = CORP_WALLET_UPLOAD_DELIMITER;
                                            $dataArr = str_getcsv($line, $delimiter);
                                            $arrLength = Util::getArrayLength($dataArr);
                                            if (!empty($dataArr) && ($arrLength==KOTAK_BANK_STATEMENT_COLUMNS)) {
                                                try {
                                                    $dataArr['bank_id'] = $formData['bank_id'];
                                                    $data[] = $dataArr;
                                                } catch (Exception $e) {
                                                   //echo $e->getMessage();
                                                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                                }
                                            } else {
                                                $this->_helper->FlashMessenger(array('msg-error' =>"Data format is not correct"));
                                                 $this->_redirect($this->formatURL('/agentfunding/uploadkotakbanktatement'));
                                                //throw new \Exception ("Data format is not correct");  
                                            }
                                        }
                                    }
                                    unset($data[0],$data[1]);   
                                    if(count($data)){
                                        $bankStatement = new BankStatement();
                                        $bankStatement->addBankStatements($data,$renameFile);
                                        $isError = false;
                                    }else{
                                        $errMsg = 'Bank statement file should not be blank.';
                                        $isError = TRUE;
                                    }
                                }
                            } else {
                                if ($pathinfo['extension'] != 'csv') {
                                    $errMsg = 'Invalid file uploaded. Allowed Format is csv only.';
                                } elseif ($_FILES['upload']['size'] == 0) {
                                    $errMsg = 'Bank statement file should not be blank.';
                                }
                                $isError = TRUE;
                            }
                        } catch (Zend_File_Transfer_Exception $e) {
                            $errMsg = $e->getMessage();
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            $isError = TRUE;
                            unlink($destination . $renameFile);
                        }
                    }    
                }
                if ($isError) {
                    $this->_helper->FlashMessenger(array('msg-error' => $errMsg));
                    $this->_redirect($this->formatURL('/agentfunding/uploadkotakbanktatement'));
                }else{ 
                    $this->_helper->FlashMessenger(array('msg-success' =>'Bank statement upload successfully.'));    
                }
            }
        }
    }
    
    
    public function virtualfundrequestsAction($param) {
        $this->title = 'Pending Virtual Fund Requests';
        $this->view->heading = 'Pending Virtual Fund Requests';
        $agentFunding = new AgentFunding();
        $this->view->paginator = $agentFunding->virtualFundRequestsPending($this->_getPage());
    }
    
    public function exportvirtualfundrequestAction($param) {
        $this->title = 'Export Pending Virtual Fund Requests';
        $agentFunding = new AgentFunding(); 
        $columns = array(
            'Request Date',
            'Agent Code',
            'Agent Name',
            'Agent Virtual Funding Amount',
            'National UTR No.',
        );
                                  
        $objCSV = new CSV();
        $exportData = $agentFunding->exportPendingVirtualFundRequest();
        try{
            $resp = $objCSV->export($exportData, $columns, 'pending_fund_requests');exit;
        }catch (Exception $e) {
            App_Logger::log($e->getMessage() , Zend_Log::ERR);
            $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
            $this->_redirect($this->formatURL('/agentfunding/pendingfundrequest')); 
        }
    }
    
    public function confirmvirtualfundrequsetAction() {
        
        $request = $this->_getAllParams();
        $param['id'] = $this->_getParam('id'); 
        $param['conformAction'] = $this->_getParam('conformAction'); 
        $param['reject_request'] = $this->_getParam('reject_request'); 
        $param['approve_request'] = $this->_getParam('approve_request');  
        $param['remarks'] = $this->_getParam('remarks');
        $param['agent_funding_id'] = $this->_getParam('agent_funding_id');
        
        $agentFunding = new AgentFunding();
        $virtualFundData = $agentFunding->pendingVirtualFundRequestById($param);
        
        if($param['reject_request'] != ''){
            $rejectReq = $agentFunding->rejectVirtualFundRequest($param); 
            if($rejectReq == FLAG_SUCCESS){
                $this->_helper->FlashMessenger(array(
                    'msg-success' => 'Virtual Fund Request is rejected successfully'
                )); 
            } else {
                $this->_helper->FlashMessenger(array(
                    'msg-error' => $rejectReq,
                )); 
            }
            $this->_redirect($this->formatURL('/agentfunding/virtualfundrequests'));  
        }
        if($param['approve_request'] != ''){
            $param['amount'] = $virtualFundData->amount ;
            $param['agent_id'] = $virtualFundData->agent_id ;
            $param['txn_type'] = $virtualFundData->txn_type ; 
            $rejectApprv = $agentFunding->approveVirtualFundRequest($param);  
            if($rejectApprv == FLAG_SUCCESS){
                $this->_helper->FlashMessenger(array(
                    'msg-success' => 'Virtual Fund Request is Approved successfully'
                )); 
            } else {
                $this->_helper->FlashMessenger(array(
                        'msg-error' => $rejectApprv,
                )); 
            }
            $this->_redirect($this->formatURL('/agentfunding/virtualfundrequests'));  
        }
        
        if(($param['conformAction'] != STATUS_APPROVED) && ($param['conformAction'] != STATUS_REJECTED)){ 
            $this->_helper->FlashMessenger(array(
                'msg-error' => 'Please choose Correct Action',
            ));
            $this->_redirect($this->formatURL('/agentfunding/virtualfundrequests')); 
        }else { 
            if(empty($virtualFundData)){ 
                $this->_helper->FlashMessenger(array(
                    'msg-error' => 'You have trying to access non-pending fund request.',
                ));
                $this->_redirect($this->formatURL('/agentfunding/virtualfundrequests')); 
            } else {
                $this->view->virtualFundData = $virtualFundData ;
                $this->view->conformAction = $param['conformAction'];
                $paramsArr = array(
                    'agent_funding_id'  =>  $param['id'], 
                    'cancelLink'        =>  '/agentfunding/virtualfundrequests'
                );
                if($param['conformAction'] == STATUS_APPROVED) { 
                    $this->title = 'Confirm Before Approve virtual Fund Request';
                    $this->view->heading = 'Approve virtual Fund Request';
                    $form = new AgentVirtualFundApproveForm(array(
                        'action'        =>  '',
                        'method'        =>  'POST',
                        'id'            =>  'form-approve-fund-request',
                        'params'        =>  $paramsArr  
                    ));
                } else if($param['conformAction'] == STATUS_REJECTED) {
                    $this->title = 'Confirm Before Reject virtual Fund Request';
                    $this->view->heading = 'Reject virtual Fund Request';
                    $form = new AgentVirtualFundRejectForm(array(
                        'action'        =>  '',
                        'method'        =>  'POST',
                        'id'            =>  'form-reject-fund-request',
                        'params'        =>  $paramsArr 
                    ));
                }
                $this->view->form = $form;
            }
        }       
    }
}
