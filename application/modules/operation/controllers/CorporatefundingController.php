<?php

/**
 * Default entry point in the application
 *
 * @package frontend_controllers
 * @copyright company
 */
class CorporatefundingController extends App_Agent_Controller {

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

        $this->title = 'Corporate Fund Requests';
    }

    /**
     * Action uploadbankstatement
     * User upload banks statement file
     * @access public
     * @return void
     
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
                                $this->_redirect($this->formatURL('/corporatefunding/afteruploadbankstatement'));
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
                        $this->_redirect($this->formatURL('/corporatefunding/uploadbankstatement'));
                    }
                }
            }
        }
    }  */

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

        $this->_redirect($this->formatURL('/corporatefunding/uploadbankstatement'));
    }

    function pendingfundrequestAction() {
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
        
        $agentFunding = new CorporateFunding();
        $this->view->paginator = $agentFunding->findAllPendingRequest($this->_getPage());
    }
    
    function exportpendingfundrequestAction() {
        $this->title = 'Export Pending Fund Request';
        $corporateFunding = new CorporateFunding();
        
         $columns = array(
                                     'Corporate Code',
                                     'Corporate Name',
                                     'Amount',
                                     'Fund Transfer Type',
                                     'Journal/Cheque No.',
                                     'Request Date'  
                                 );
                                  
                 $objCSV = new CSV();
                 $exportData = $corporateFunding->exportAllPendingRequest();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'pending_fund_requests');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/corporatefunding/pendingfundrequest')); 
                                       }
    }

    function confirmbeforesettlementAction() {
        $this->title = 'Approve Fund Request';
        $agentFundingObj = new CorporateFunding();
        $corporateBindObj = new BindCorporateProductCommission();
        $bankStatement = new BankStatement();
        $id = $this->_getParam('id');
        $agentFunding = $agentFundingObj->getCorporateFundingById($id);
        $corporateProducts = $corporateBindObj->getCorporateProductAndBank($agentFunding->corporate_id);

        if ($agentFunding) {
            $this->view->corporateFunding = $agentFunding;
            $bankStatementData = '';
            if($corporateProducts['bank_id']){
                $bankStatementData = $bankStatement->getAllUnsettledBankStatement(array('amount' => $agentFunding->amount,'bank_id' => $corporateProducts['bank_id']));
            }
            $this->view->bankStatements = $bankStatementData;
            $session = new Zend_Session_Namespace('App.Operation.Controller');
            if (isset($session->msg)) {
                $this->view->msg = $session->msg;
                unset($session->msg); //After show msg remove it from session
            }

            $formData = $this->getRequest()->getPost();
            if (isset($formData['submit'])) {
                $session->viewPendingRequestFormData = $formData;
                $this->_redirect($this->formatURL('/corporatefunding/confirmsettlement'));
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

            $this->_redirect($this->formatURL('/corporatefunding/pendingfundrequest'));
        } else {
            $agent_funding_id = isset($formData['corporate_funding_id']) ? $formData['corporate_funding_id'] : $this->_getPage('id');
            $statement_id = $formData['statement_id'];
                
            //Condition: If user post empty values for $agent_funding_id or $statement_id 
            if (empty($agent_funding_id)) {
                $this->_helper->FlashMessenger(
                        array(
                            'msg-error' => 'Invalid agent funding request id',
                        )
                );
                $this->_redirect($this->formatURL('/corporatefunding/pendingfundrequest'));
            } elseif (empty($statement_id)) {
                $session->msg = 'Please select bank statement';
                $this->_redirect($this->formatURL('/corporatefunding/confirmbeforesettlement?id=' . $agent_funding_id));
            } else {
                $agentFunding = new CorporateFunding();
                $agentFundingRow = $agentFunding->getCorporateFundingById($agent_funding_id);

                $bankStatement = new BankStatement();
                $bankStatementRow = $bankStatement->getUnsettledBankStatementById($statement_id);

                if (is_null($agentFundingRow) || is_null($bankStatementRow)) {
                    $session = new Zend_Session_Namespace('App.Operation.Controller');
                    $session->msg = 'Invalid agent funding request id or bank statement id';
                    $this->_redirect($this->formatURL('/corporatefunding/confirmbeforesettlement?id=' . $agent_funding_id));
                } else {
                    $this->view->corporateFunding = $agentFundingRow;
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
            $agent_funding = $formData['corporate_funding_id'] == $firstFormData['corporate_funding_id'];
            $statement = $formData['statement_id'] == $firstFormData['statement_id'];

            if (!$agent_funding || !$statement) { //If First Form value is not equal to Second form
                $session->msg = 'Invalid corporate funding request id or bank statement id';
                $this->_redirect($this->formatURL('/corporatefunding/confirmbeforesettlement?id=' . $firstFormData['corporate_funding_id']));
            } else {
                $agentFunding = new CorporateFunding();
                $bankStatement = new BankStatement();
                $user = Zend_Auth::getInstance()->getIdentity();

                $agentFundingRow = $agentFunding->findById($firstFormData['corporate_funding_id']);
                $bankStatementRow = $bankStatement->findById($firstFormData['statement_id']);
                
                $msg = $agentFunding->settledFundRequest($agentFundingRow, $bankStatementRow, BY_OPS, $user->id, $formData['settlement_remarks'], $firstFormData['corporate_funding_id']);
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
                $this->_redirect($this->formatURL('/corporatefunding/pendingfundrequest/msg/'.$msg));
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
                                         $this->_redirect($this->formatURL('/corporatefunding/unsettledbankstatement')); 
                                       }
    }
    
    function settledfundrequestAction() {
        $this->title = 'Settled Fund Request';
        $agentFundingObj = new CorporateFunding();
        $this->view->corporateFunding = $agentFundingObj->getAllApprovedFundRequestWithSettledBankStatement($this->_getPage());
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
            'Status'
        );

        $objCSV = new CSV();
                 $exportData = $agentFundingObj->exportAllApprovedFundRequestWithSettledBankStatement();
                 try{
                        $resp = $objCSV->export($exportData, $columns, 'settled_fund_request');exit;
                 }catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
                                         $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
                                         $this->_redirect($this->formatURL('/corporatefunding/settledfundrequest')); 
                                       }
    }
    function rejectfundrequestAction() {
            
        $formData = $this->getRequest()->getPost();
        
        if (!isset($formData['submit'])) {
            $msg = array('msg-error' => 'Invalid fund request id');
        } else {
            $id = $this->_getParam('corporate_funding_id');
            $agentFundingObj = new CorporateFunding();
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
        $this->_redirect($this->formatURL('/corporatefunding/pendingfundrequest'));
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
            $agentFundingObj = new CorporateFunding();
            $agentFunding = $agentFundingObj->getCorporateFundingById($id);

            if (!$agentFunding) {
                $error = TRUE;
            } else {
                $this->view->corporateFunding = $agentFunding;
                $this->view->form = new RejectCorporateFundRequestForm(
                        array(
                    'method' => 'post',
                    'id' => 'form-reject-fund-request',
                    'action' => $this->formatURL('/corporatefunding/rejectfundrequest'),
                    'params' => array('corporate_funding_id' => $agentFunding->corporate_funding_id, 'cancelLink' => $this->formatURL('/corporatefunding/pendingfundrequest'),)
                        )
                );
            }
        }
        if ($error) {
            $this->_helper->FlashMessenger(
                    $errMsg
            );
            $this->_redirect($this->formatURL('/corporatefunding/pendingfundrequest'));
        }
    }

}
