<?php

/**
 * Default entry point in the application
 *
 * @package frontend_controllers
 * @copyright company
 */
class CorporateFundingController extends App_Corporate_Controller {

    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init() {

        // init the parent
        parent::init();
        $user = Zend_Auth::getInstance()->getIdentity();
        if(!isset($user->id)) {
           $this->_redirect($this->formatURL('/profile/login'));
           exit;
        }
    }

    /**
     * Controller's entry point
     * requestfundAction
     * @access public
     * @return void
     */
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
        $session = new Zend_Session_Namespace('App.Corporate.Controller');
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $form = new CorporateFundingForm(array(
            'action' => $this->formatURL('/corporatefunding/requestfund'),
            'method' => 'post',
        ));


        $this->view->form = $form;
        $formData = $this->getRequest()->getPost();
        $objFR = new CorporateFunding();

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
                $data['corporate_id'] = $user->id;
                $data['ip_agent'] = $objFR->formatIpAddress(Util::getIP());
                $data['amount'] = $formData['amount'];
                $data['fund_transfer_type_id'] = $formData['fund_transfer_type_id'];
                $data['comments'] = $formData['comments'];
                //print_r($data); exit;
                try {
                    $resp = $objFR->addCorporateFunding($data);
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
                    $minmax = $objFR->chkCorporateMinMaxLoad($formData);

                    if ($minmax != FALSE) { //If chkAgentMinMaxLoad not return false 
                        $agentsModel = new Corporates();
                        $row = $agentsModel->findById($user->id);
                        $mailArray = array(
                            'email' => $row['email'],
                            'name' => ucfirst($row['first_name']) . ' ' . ucfirst($row['last_name']),
                            'min' => $minmax['minValue'],
                            'max' => $minmax['maxValue'],
                            'amt' => $formData['amount']
                        );
                        $m = new App\Messaging\MVC\Axis\Corporate();
                        $m->corporateMinMaxLoad($mailArray);
                        $flashMsg = 'Fund request has been sent successfully.For future please request for fund between ' . CURRENCY_INR . ' ' . Util::numberFormat($minmax['minValue']) . ' and ' . CURRENCY_INR . ' ' . Util::numberFormat($minmax['maxValue']) . '.';
                    } else {
                        $mailArray = array(
                            'amount' => $formData['amount'],
                            'agent_code' => $user->corporate_code,
                            'agent_email' => $user->email,
                            'agent_mobile_number' => $user->mobile,
                            'comments' => $formData['comments'],
                        );
                        $m = new App\Messaging\MVC\Axis\Corporate();
                        $m->corporateFundRequest($mailArray);
                        $flashMsg = 'Fund request has been sent successfully';
                    }
                    //$flashMsg = 'Fund request has been sent successfully';
                    $this->_helper->FlashMessenger(array('msg-success' => $flashMsg,));
                    $this->_redirect($this->formatURL('/corporatefunding/index/'));
                }
            }
        }
    }

    function fundrequestAction() {
        $this->title = 'My Fund Requests';
        $agentFunding = new CorporateFunding();
        $user = Zend_Auth::getInstance()->getIdentity(); //get Agent Id from session
        $this->view->paginator = $agentFunding->findAllFundRequestByCorporateId($user->id, $this->_getPage());
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

}