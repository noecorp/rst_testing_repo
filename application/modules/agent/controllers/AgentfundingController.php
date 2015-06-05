<?php

/**
 * Default entry point in the application
 *
 * @package frontend_controllers
 * @copyright company
 */
class AgentFundingController extends App_Agent_Controller {

    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init() {
        // init the parent
        parent::init();
        // initiating the session
        $this->session = new Zend_Session_Namespace("App.Agent.Controller");
    }
 
    public function indexAction() {
        $this->title = 'My Funding';
    }

    /**
     * Controller's entry point
     * requestfundAction
     * @access public
     * @return void
     */
    public function requestfundAction() {
        $this->title = 'Funding Request';
        $session = new Zend_Session_Namespace('App.Agent.Controller');
        $user = Zend_Auth::getInstance()->getIdentity();

        $form = new AgentFundingForm(array(
            'action' => $this->formatURL('/agentfunding/requestfund'),
            'method' => 'post',
        ));


        $this->view->form = $form;
        $formData = $this->getRequest()->getPost();
        $objFR = new AgentFunding();

        if (isset($formData['btn_send'])) {
            $validate = TRUE;
            $fund_transfer_cash_or_dd = ($formData['fund_transfer_type_id'] == FUND_TRANSFER_TYPE_ID_CASH || $formData['fund_transfer_type_id'] == FUND_TRANSFER_TYPE_ID_DD );
            $fund_transfer_chk = ($formData['fund_transfer_type_id'] == FUND_TRANSFER_TYPE_ID_CHEQUE);
            $fund_transfer_neft = ($formData['fund_transfer_type_id'] == FUND_TRANSFER_TYPE_ID_NEFT);
            $fund_transfer_bank = ($formData['fund_transfer_type_id'] == FUND_TRANSFER_TYPE_ID_BANK_TRANSFER);
            
            if ($fund_transfer_cash_or_dd && empty($formData['other_txn'])) {
                $validate = FALSE;
                $err = array('msg-error' => 'Please enter other transaction no.');
            } elseif ($fund_transfer_chk && empty($formData['cheque_no'])) {
                $validate = FALSE;
                $err = array('msg-error' => 'Please enter cheque no.');
            } elseif ($fund_transfer_neft && empty($formData['journal_no'])) {
                $validate = FALSE;
                $err = array('msg-error' => 'Please enter journal no.');
            } elseif ($fund_transfer_bank && empty($formData['journal_no'])) {
                $validate = FALSE;
                $err = array('msg-error' => 'Please enter journal no.');
            }


            if (!$validate) {
                $this->_helper->FlashMessenger($err);
            }

            $validate = $validate && $form->isValid($formData);
            $form->populate($formData);
            if ($validate) {

                if ($fund_transfer_cash_or_dd) {
                    $data['funding_no'] = $formData['other_txn'];
                    $data['funding_details'] = $formData['funding_details']; 
                } elseif ($fund_transfer_chk) {
                    $data['funding_no'] = $formData['cheque_no'];
                    $data['funding_details'] =
                            'Bank of cheque issue:'
                            . $formData['bank_of_cheque_issue']
                            . SEPARATOR_PIPE
                            . 'Branch of cheque:'
                            . $formData['branch_of_cheque']
                            . SEPARATOR_PIPE
                            . 'Date of issue:'
                            . $formData['date_of_cheque_issue'];
                } elseif ($fund_transfer_neft || $fund_transfer_bank) {
                    $data['funding_no'] = $formData['journal_no'];
                }
                
                $data['status'] = STATUS_PENDING;
                $data['agent_id'] = $user->id;
                $data['ip_agent'] = $objFR->formatIpAddress(Util::getIP());
                $data['amount'] = $formData['amount'];
                $data['fund_transfer_type_id'] = $formData['fund_transfer_type_id'];
                $data['comments'] = $formData['comments'];

                try {
                    $resp = $objFR->addAgentFunding($data);
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $errMsg = $e->getMessage();
                    $this->_helper->FlashMessenger(
                            array(
                                'msg-error' => $errMsg,
                            )
                    );
                }

                if ($resp) {
                    $minmax = $objFR->chkAgentMinMaxLoad($formData);

                    if ($minmax != FALSE) { //If chkAgentMinMaxLoad not return false 
                        $agentsModel = new Agents();
                        $row = $agentsModel->findById($user->id);
                        $mailArray = array(
                            'email' => $row['email'],
                            'name' => ucfirst($row['first_name']) . ' ' . ucfirst($row['last_name']),
                            'min' => $minmax['minValue'],
                            'max' => $minmax['maxValue'],
                            'amt' => $formData['amount']
                        );
                        $m = new App\Messaging\MVC\Axis\Agent();
                        $m->agentMinMaxLoad($mailArray);
                        $flashMsg = 'Fund request has been sent successfully.For future please request for fund between ' . CURRENCY_INR . ' ' . Util::numberFormat($minmax['minValue']) . ' and ' . CURRENCY_INR . ' ' . Util::numberFormat($minmax['maxValue']) . '.';
                    } else {
                        $mailArray = array(
                            'amount' => $formData['amount'],
                            'agent_code' => $user->agent_code,
                            'agent_email' => $user->email,
                            'agent_mobile_number' => $user->mobile1,
                            'comments' => $formData['comments'],
                        );
                        $m = new App\Messaging\MVC\Axis\Agent();
                        $m->agentFundRequest($mailArray);
                        $flashMsg = 'Fund request has been sent successfully';
                    }
                    //$flashMsg = 'Fund request has been sent successfully';
                    $this->_helper->FlashMessenger(array('msg-success' => $flashMsg,));
                    $this->_redirect($this->formatURL('/agentfunding/index/'));
                }
            }
        }
    }

    function fundrequestAction() {
        $this->title = 'My Fund Requests';
        $agentFunding = new AgentFunding();
        $user = Zend_Auth::getInstance()->getIdentity(); //get Agent Id from session
        $this->view->paginator = $agentFunding->findAllFundRequestByAgentId($user->id, $this->_getPage());
    }

    function viewfundrequestAction() {
        $this->title = 'View Fund Request';
        $id = $this->_getParam('id');
        $user = Zend_Auth::getInstance()->getIdentity();

        $agentFundingObj = new AgentFunding();
        $agentFunding = $agentFundingObj->getAgentFundingByAgentId($user->id, $id);
//var_dump($agentFunding);exit('END');
        if (!$agentFunding) {
            $this->_helper->FlashMessenger(array('msg-error' => 'Invalid fund request id '));
            $this->_redirect($this->formatURL('/agentfunding/fundrequest'));
        } else {
            $this->view->agentFunding = $agentFunding;
        }
    }
    
    public function requestvirtualfundAction(){
        $this->title = 'Partner Virtual Funding';
        $this->view->heading = 'Partner Virtual Funding'; 
        $session = new Zend_Session_Namespace('App.Agent.Controller');
        $user = Zend_Auth::getInstance()->getIdentity();

        $form = new AgentVirtualFundingForm(array(
            'action' => $this->formatURL('/agentfunding/requestvirtualfund'),
            'method' => 'post',
        ));
        $this->view->form = $form;
        $formData = $this->getRequest()->getPost();
        $objFR = new AgentFunding();
        if (isset($formData['btn_send'])) {
            $validate = TRUE; 
            if($formData['amount'] < 1) {
                $err = array('msg-error' => 'Amount less than 1');
                $validate = FALSE;
            }
            if (!$validate) {
                $this->_helper->FlashMessenger($err);
            } 
            $validate = $validate && $form->isValid($formData);
            $form->populate($formData);
            if ($validate) { 
                $data['agent_id'] = $user->id;
                $data['amount'] = $formData['amount']; 
                $data['utr'] = $formData['utr']; 
                $data['comments'] = $formData['comments'];
                
                try {
                    $resp = $objFR->addAgentVirtualFunding($data);
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $errMsg = $e->getMessage();
                    $this->_helper->FlashMessenger(array(
                        'msg-error' => $errMsg,
                    ));
                }
                if ($resp) {
                    $flashMsg = 'Virtual Fund request has been sent successfully.';
                    $this->_helper->FlashMessenger(array('msg-success' => $flashMsg,));
                    $this->_redirect($this->formatURL('/agentfunding/virtualfundrequest/'));
                }
            }
        }
    }
    
    public function virtualfundrequestAction(){ 
        $agentFunding = new AgentFunding();
        $user = Zend_Auth::getInstance()->getIdentity(); //get Agent Id from session 
        $id = $this->_getParam('id');
        
        if($id == ''){
            $this->view->detailPage = FLAG_NO; 
            $this->title = 'My Virtual Fund Requests';
            $this->view->heading = 'My Virtual Fund Requests';
            $this->view->paginator = $agentFunding->virtualFundRequestByAgentId($user->id, $this->_getPage());
        } else if($id != '') {
            $this->view->detailPage = FLAG_YES;
            $this->title = 'View Virtual Fund Request';
            $this->view->heading = 'View Virtual Fund Request'; 
            $agentFundingData = $agentFunding->getVirtualFundRequestById($user->id, $id);
            if (!$agentFundingData) {
                $this->_helper->FlashMessenger(array('msg-error' => 'Invalid virtual fund request id '));
                $this->_redirect($this->formatURL('/agentfunding/virtualfundrequest'));
            } else {
                $this->view->agentFunding = $agentFundingData;
            }
            $this->view->backlink = $this->formatURL('/agentfunding/virtualfundrequest');
        }        
    }
}