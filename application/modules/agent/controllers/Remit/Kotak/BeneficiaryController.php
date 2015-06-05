<?php

/**
 * Add Beneficiary
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */
class Remit_Kotak_BeneficiaryController extends App_Agent_Controller {

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

    public function searchremitterAction() {
        $this->session = new Zend_Session_Namespace("App.Agent.Controller");
//        unset($this->session->fundtransfer_auth);
        unset($this->session->fundtransfer_amount);
        unset($this->session->beneficiary_auth);
//        unset($this->session->remitter_otp);
        $this->session->newRemitter = FALSE;
        $flgSess = ($this->_getParam('flgSess') > 0) ? $this->_getParam('flgSess') : 0;
        $remitterId = ($this->session->remitter_id > 0) ? $this->session->remitter_id : 0;
        if ($flgSess == 0) {
            unset($this->session->remitter_mobile_number);
            unset($this->session->beneficiary_auth);
            unset($this->session->remitter_id);
//            unset($this->session->fundtransfer_auth);
            unset($this->session->fundtransfer_amount);
            unset($this->session->refund_auth_code);
            unset($this->session->remittance_request_id);
            unset($this->session->refundable_amount);
            unset($this->session->refund_fee);
//            unset($this->session->remitter_otp);
        }

        $this->title = 'Fund Transfer';
        $formData = $this->_request->getPost();

        // Agent phone entry form.
        $form = new Remit_Kotak_SearchRemitterForm(array(
                    'action' => $this->formatURL('/remit_kotak_beneficiary/searchremitter'),
                    'method' => 'POST',
                    'name' => 'frmverify',
                    'id' => 'frmverify'
                ));
        $this->view->showlist = FALSE;
        $btnSearch = isset($formData['submit_form']) ? true : false;
        $objRemittanceRequest = new Remit_Kotak_Remittancerequest();
        $remitters = new Remit_Kotak_Remitter();
        $bank = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        
        // adding details in db
        if ($btnSearch) {
            if ($this->getRequest()->isPost()) {
                if ($form->isValid($this->getRequest()->getPost())) {

                    if ($formData['phone'] != '') {
                        try {
                            $remitterdetail = $remitters->getRemitter($formData['phone']);
                            $this->session->remitter_mobile_number = $formData['phone'];
                            $this->session->remitter_id = $remitterdetail['id'];
                            
//                            $checkStaticOTP = Util::sendStaticOTP($remitterdetail['id'], $bank->bank->unicode);
//                            if($checkStaticOTP != ''){
//                                $this->session->remitter_otp = $remitterdetail['otp'];
//                            }
                            
                            $this->view->remitterdetails = $remitterdetail;
                            $beneficiariesList = $remitters->getRemitterbeneficiaries($remitterdetail['id']);
                            $remittanceArr = $objRemittanceRequest->getRemitterRemittanceCountandSum($remitterdetail['id']);
                            $countRefunds = $objRemittanceRequest->getRemitterRefundCount($remitterdetail['id']);
                            $this->view->paginator = $beneficiariesList;
                            $this->view->showlist = TRUE;
                            $this->view->countRefunds = $countRefunds['count_refund_requests'];
                            $this->view->remittanceArr = $remittanceArr;
                        } catch (Exception $e) {
                            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
                            $msg = $e->getMessage();
                            $this->_helper->FlashMessenger(array('msg-error' => $msg,));
                        }
                    } else if ($mobNo == '') {
                        $this->_helper->FlashMessenger(array('msg-error' => 'No mobile number provided',));
                    }
                    $form->populate($formData);
                }
            }
        }
        //populating details
        if ($this->session->remitter_id > 0 && $flgSess > 0) {


            try {
//                echo $this->session->remitter_mobile_number."======";
                $remitterdetail = $remitters->getRemitter($this->session->remitter_mobile_number);
                $this->session->remitter_id = $remitterdetail['id'];
                
//                $checkStaticOTP = Util::sendStaticOTP($remitterdetail['id'], $bank->bank->unicode);
//                if($checkStaticOTP != ''){
//                    $this->session->remitter_otp = $remitterdetail['otp'];
//                }
                
                $this->view->remitterdetails = $remitterdetail;
                $beneficiariesList = $remitters->getRemitterbeneficiaries($remitterdetail['id']);
                $countRefunds = $objRemittanceRequest->getRemitterRefundCount($remitterdetail['id']);
                $remittanceArr = $objRemittanceRequest->getRemitterRemittanceCountandSum($remitterdetail['id']);
                $this->view->paginator = $beneficiariesList;
                $this->view->showlist = TRUE;
                $this->view->countRefunds = $countRefunds['count_refund_requests'];
                $this->view->remittanceArr = $remittanceArr;
                $formData['phone'] = $this->session->remitter_mobile_number;
//                $formData['auth_code'] = $this->session->remitter_search_auth;
                $form->populate($formData);
            } catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $msg = $e->getMessage();
                $this->_helper->FlashMessenger(array('msg-error' => $msg,));
            }
        }
        $this->view->form = $form;
    }

    public function addAction() {
        //unset($this->session->beneficiary_auth);
        //echo 'AUTH'.$this->session->beneficiary_auth;
//        echo App_DI_Container::get('DbConfig')->key;

        $this->title = 'Add Beneficiary Basic Details';
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new Remit_Kotak_AddBeneficiaryDetailsForm();
        $beneficiary = new Remit_Kotak_Beneficiary();
        $remitters = new Remit_Kotak_Remitter();
        $status = '';
        $smsMsg = '';
        $formdata = $this->_request->getPost();
        $remitter_id = $this->session->remitter_id;
        $btnAuth = isset($formdata['send_auth_code']) ? $formdata['send_auth_code'] : '0';
        $btnAdd = isset($formdata['submit_form']) ? true : false;
        $remitterdetail = $remitters->getRemitterById($remitter_id);
        $m = new App\Messaging\Remit\Kotak\Agent();

        $beneCount = $remitters->getRemitterbeneficiariesCount($remitter_id);
        if($beneCount  >=  KOTAK_MAX_BENFICIARY_COUNT){
            $this->_helper->FlashMessenger(array('msg-error' => "Remitter has reached the maximum no. of beneficiaries allowed",)
                                    );
            $this->_redirect($this->formatURL("/remit_kotak_beneficiary/searchremitter?flgSess=1"));
                                 
        }
        if ($btnAuth == 1) {
                   

            try {
               

                $userData = array('mobile1' => $this->session->remitter_mobile_number,
                    'name' => $formdata['name'],
                    'nick_name' => $formdata['nick_name'],
                    'bank_name' => $formdata['bank_name'],
                    'ifsc_code' => trim($formdata['ifsc_code']),
                    'bank_account_number' => $formdata['bank_account_number'],
                    'product_name' => KOTAK_SHMART_TRANSFER,
                    'remitter_id' => $remitter_id
                );
             

                if (isset($this->session->beneficiary_auth))
                    $resp = $m->addKotakBeneficiaryAuth($userData, $resend = TRUE);
                else
                    $resp = $m->addKotakBeneficiaryAuth($userData);
                $formdata['send_auth_code'] = 0;
                $formdata['ifsc'] = trim($formdata['ifsc_code']);
                $this->_helper->FlashMessenger(array('msg-success' => 'Authorization code has been sent on your mobile number.',));
                $form->populate($formdata);
            } catch (Exception $e) {
                $formdata['ifsc'] = trim($formdata['ifsc_code']);
                $errorExists = true;
                $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                $form->populate($formdata);
                App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            }
        }
        if ($btnAdd) {

            if ($this->getRequest()->isPost()) {
                if ($form->isValid($this->getRequest()->getPost())) {


                    $data = array();
                    $data['name'] = $formdata['name'];
                    $data['nick_name'] = $formdata['nick_name'];
                    $data['mobile'] = $formdata['mobile'];
                    $data['email'] = $formdata['email'];
                    $data['address_line1'] = $formdata['address_line1'];
                    $data['address_line2'] = $formdata['address_line2'];
                    $data['bank_name'] = $formdata['bank_name'];
                    $data['ifsc_code'] = strtoupper(trim($formdata['ifsc_code']));
                    $data['bank_account_number'] = $formdata['bank_account_number'];
                    $data['branch_address'] = $formdata['branch_address'];
                    $data['branch_city'] = $formdata['branch_city'];
                    $data['branch_name'] = $formdata['branch_name'];
                    $data['bank_account_type'] = $formdata['bank_account_type'];
                    $data['by_agent_id'] = $user->id;
                    $data['by_ops_id'] = TXN_OPS_ID;
                    $data['remitter_id'] = $remitter_id;
                    $data['date_created'] = new Zend_Db_Expr('NOW()');

                    $form->getElement("ifsc")->setValue(trim($formdata['ifsc_code']));
                    try {
                        if ($formdata['auth_code'] == $this->session->beneficiary_auth) {

                            if ($formdata['nick_name'] == $this->session->nick_name &&
                                    $formdata['bank_account_number'] == $this->session->bank_account_number &&
                                    $formdata['bank_name'] == $this->session->bank_name &&
                                     trim($formdata['ifsc_code']) == trim($this->session->ifsc_code)) {
                                $res = $beneficiary->addbeneficiary($data);
                                if ($res > 0) {
                                    
                                    $beneCode = Util::getBeneCodeFromId($res);
                                    $beneficiary->updateBeneficiaryDetails(array('bene_code'=> $beneCode),$res);

                                    $userArr = array(
                                        'mobile1' => $remitterdetail['mobile'],
                                        'status' => FLAG_SUCCESS,
                                        'nick_name' => $formdata['nick_name'],
                                        'product_name' => KOTAK_SHMART_TRANSFER
                                    );
                                    $m->beneficiaryEnrollment($userArr);
                                    $this->_helper->FlashMessenger(
                                            array(
                                                'msg-success' => "Beneficiary details were successfully added",
                                            )
                                    );
                                    $this->_redirect($this->formatURL("/remit_kotak_beneficiary/searchremitter?flgSess=1"));
                                } else {

                                    $userArr = array(
                                        'mobile1' => $remitterdetail['mobile'],
                                        'status' => FLAG_FAILURE,
                                        'nick_name' => $formdata['nick_name'],
                                        'product_name' => KOTAK_SHMART_TRANSFER
                                    );
                                    $m->beneficiaryEnrollment($userArr);
                                    $this->_helper->FlashMessenger(
                                            array(
                                                'msg-error' => $errMsg,
                                            )
                                    );
                                }
                            } else {
                                $this->_helper->FlashMessenger(array('msg-error' => 'Please check your SMS for correct beneficiary details',));
                            }
                        } else {
                            $this->_helper->FlashMessenger(array('msg-error' => 'Authorization code entered is not correct',));
                        }
                    } catch (Exception $e) {

                        $errMsg = $e->getMessage();
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    }




                    $formdata['send_auth_code'] = 0;
                    $formdata['ifsc'] = $formdata['ifsc_code'];
                    $form->populate($formdata);
                }
            }
        }
        $form->populate($formdata);
        $this->view->form = $form;
        $this->view->remitter_name = $remitterdetail['name'];
    }

    public function completeAction() {

        $this->title = 'Beneficiary Basic Details Complete';

        $this->view->msg = 'Add Another beneficiary';
    }



    public function transferfundAction() {

        $this->title = 'Transfer Funds';
        $user = Zend_Auth::getInstance()->getIdentity();
        $beneId = ($this->_getParam('id') > 0) ? $this->_getParam('id') : 0;        
        $form = new Remit_Kotak_FundTransferForm();
        $formdata = $this->_request->getPost();
        $btnAuth = isset($formdata['send_auth_code']) ? $formdata['send_auth_code'] : '0';
        $btnAdd = isset($formdata['is_submit']) ? $formdata['is_submit'] : false;
        $beneficiary = new Remit_Kotak_Beneficiary();
        $remittancerequest = new Remit_Kotak_Remittancerequest();
        $remittancestatuslog = new Remit_Kotak_Remittancestatuslog();
        
        $m = new App\Messaging\Remit\Kotak\Agent();
        if (!$beneId) {
            $this->_redirect($this->formatURL("/remit_kotak_beneficiary/searchremitter?flgSess=1"));
        }
        $detail = $beneficiary->getBeneficiaryDetails($beneId);

        $feeplan = new FeePlan();
        $feeArr = $feeplan->getRemitterFee($detail['product_id'], $user->id);
        $baseTxn = new BaseTxn();
        $bank = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        $displayMsg = '';
        $remitAmt = 0;
        $remitFee = 0;
        
        if ($btnAdd) {
            if ($this->getRequest()->isPost()) {
                if ($form->isValid($this->getRequest()->getPost())) {
                    try {
                        $fee = '0.00';
                        //Fees Check
                        
                        foreach ($feeArr as $val) {
                           if ($val['typecode'] == TXNTYPE_REMITTANCE_FEE) {
                                $val['amount'] = $formdata['amount'];
                                $val['return_type'] = TYPE_FEE;
                                $fee = Util::calculateRoundedFee($val);
                                App_Logger::log($fee, Zend_Log::ERR);
                                break;
                           }
                        }
                        
                        $feeComponent = Util::getFeeComponents($fee);
                        
                        $params = array(
                            'agent_id'      =>  $user->id,
                            'product_id'    =>  $detail['product_id'],
                            'remitter_id'   =>  $this->session->remitter_id,
                            'amount'        =>  $formdata['amount'],
                            'fee_amt'       =>  $feeComponent['partialFee'],
                            'service_tax'   =>  $feeComponent['serviceTax'],
                            'bank_unicode'  =>  $bank->bank->unicode
                        );
              

       //params - basetxn->chkallowremit
                        if ($remittancerequest->chkAllowRemit($params)) {
                            if (!empty($feeArr)) {
                                $userData = array(
                                        'mobile1'   =>  $this->session->remitter_mobile_number,
                                        'amount'    =>  $formdata['amount'],
                                        'nick_name' =>  $detail['nick_name'],
                                        'account_no'=>  $detail['bank_account_number'],
                                        'fee'       =>  $fee,
                                        'remitter_id'=> $this->session->remitter_id,
                                );
                               
                                $this->session->allow_remittance = TRUE;
                                $this->session->fundtransfer_amount = $formdata['amount'] ;
                                
                            } else {
                                $this->_helper->FlashMessenger(array(
                                    'msg-error' => 'Product not assigned to agent for the day',
                                ));
                            }
                        }
                        
                    if ($this->session->allow_remittance == TRUE) {
                        if ($formdata['amount'] == $this->session->fundtransfer_amount) {
                                /*$fee = '0.00';
                                foreach ($feeArr as $val) {
                                    if ($val['typecode'] == TXNTYPE_REMITTANCE_FEE) {
                                        $val['amount'] = $formdata['amount'];
                                        $val['return_type'] = TYPE_FEE;
                                        $fee = Util::calculateFee($val);
                                        break;
                                    }
                                }
                                $feeComponent = Util::getFeeComponents($fee);*/
                                
                                $data = array();
                                $data['amount'] = $formdata['amount'];
                                $data['fund_holder'] = REMIT_FUND_HOLDER_REMITTER;
                                $data['agent_id'] = $user->id;
                                $data['remitter_id'] = $this->session->remitter_id;
                                $data['beneficiary_id'] = $beneId;
                                $data['ops_id'] = TXN_OPS_ID;
                                $data['product_id'] = $detail['product_id'];
                                $data['date_created'] = new Zend_Db_Expr('NOW()');
                                $data['fee'] = $feeComponent['partialFee'];
                                $data['service_tax'] = $feeComponent['serviceTax'];
                                $data['status'] = STATUS_INCOMPLETE;
                                $data['sender_msg'] = ($formdata['sender_msg'] == '') ? '': $formdata['sender_msg'] ;
                                $data['otp'] = '';
                                $data['date_otp'] = '';
                                $this->session->allow_remittance = FALSE;
                                $res = $remittancerequest->save($data);
                                $datastatus = array();
                                $datastatus['remittance_request_id'] = $res;
                                $datastatus['status_old'] = '';
                                $datastatus['status_new'] = STATUS_INCOMPLETE;
                                $datastatus['by_remitter_id'] = $this->session->remitter_id;
                                $datastatus['by_agent_id'] = $user->id;
                                $datastatus['by_ops_id'] = 0;
                                $datastatus['date_created'] = new Zend_Db_Expr('NOW()');
                                $resLog = $remittancestatuslog->addStatus($datastatus);
                                if ($res > 0) {
                                    $paramsArr = array('agent_id' => $user->id,
                                        'product_id' => $detail['product_id'],
                                        'remitter_id' => $this->session->remitter_id,
                                        'amount' => $this->session->fundtransfer_amount,
                                        'remit_request_id' => $res,
                                        'fee_amt' => $feeComponent['partialFee'],
                                        'service_tax' => $feeComponent['serviceTax'],
                                        'bank_unicode' => $bank->bank->unicode
                                    );
                                    $txnCode = $remittancerequest->initiateRemit($paramsArr);
                                    if ($txnCode) {
                                        $updateArr = array(
                                            'status' => STATUS_IN_PROCESS,
                                            'fund_holder' => REMIT_FUND_HOLDER_NEFT,
                                            'txn_code' => $txnCode
                                        );
                                        $resUpdate = $remittancerequest->updateReq($res, $updateArr);
                                        $datastatus = array();
                                        $datastatus['remittance_request_id'] = $res;
                                        $datastatus['status_old'] = STATUS_INCOMPLETE;
                                        $datastatus['status_new'] = STATUS_IN_PROCESS;
                                        $datastatus['by_remitter_id'] = $this->session->remitter_id;
                                        $datastatus['by_agent_id'] = $user->id;
                                        $datastatus['by_ops_id'] = 0;
                                        $datastatus['date_created'] = new Zend_Db_Expr('NOW()');
                                        $resLog = $remittancestatuslog->addStatus($datastatus);
                                        if($formdata['sender_msg'] == ''){
                                            $remarks = '';
                                        } else {
                                            $remarks = Util::removeSpecialChars($formdata['sender_msg']);
                                            $remarks = Util::truncateString($remarks, 50);
                                        }
                                        $partnerName=SHMART_AGENT_NETWORK;
                                        $agentUser = new AgentUser();
                                        $agentDetails = $agentUser->getClosedLoopAgentDetailsById($user->id);                                        
                                         if(isset($agentDetails['group']))
                                            {
                                                       $partnerName = $agentDetails['group'];
                                            }
                                            
                                        $paramApi = array(
                                            'traceNumber' => $txnCode,
                                            'beneIFSC' => $detail['ifsc_code'],
                                            'beneAccount' => $detail['bank_account_number'],
                                            'amount' => $formdata['amount'],
                                            'remarks' => $remarks,
                                            'remitterName' => $detail['remitter_name'],
                                            'remitterMobile' => $detail['remitter_mobile'],
                                             'partnerName' => $partnerName
                                         
                                            );
                                        

                                        $remitanceStatus = $remittancerequest->getRemitterRequestsInfo($res);
                                        try {
                                            $api = new App_Api_Kotak_Remit_Transaction();
                                            $resp = $api->creditAccount($paramApi);
                                            $updateStatusArr = array();
                                            $paramsBaseTxn = array(
                                                'remit_request_id' => $res,
                                                'product_id' => $detail['product_id'],
                                                'amount' => $formdata['amount'],
                                                'bank_unicode' => $bank->bank->unicode,
                                                'agent_id' => $user->id
                                            );
                                        
                                        $datastatus = array();
                                        switch ($resp) {
                                                case TRANSACTION_SUCCESSFUL:
                                                    //Success
                                                    $paramsBaseTxn['beneficiary_id'] = $beneId;
                                                    $paramsBaseTxn['txn_code'] = $txnCode;
                                                    $paramsBaseTxn['fee_amt'] = $feeComponent['partialFee'];
                                                    $paramsBaseTxn['service_tax'] = $feeComponent['serviceTax'];

                                                    $baseTxn->remitSuccess($paramsBaseTxn);
                                                    // Remit request table update Array
                                                    $updateStatusArr['is_complete'] = FLAG_YES;
                                                    $updateStatusArr['status'] = STATUS_SUCCESS;
                                                    $updateStatusArr['fund_holder'] = REMIT_FUND_HOLDER_BENEFICIARY;
                                                    $displayMsg = "Your remittance request has been successfully processed.";
                                                    $datastatus['status_new'] = STATUS_SUCCESS;
                                                    break;

                                                case TRANSACTION_FAILED:
                                                case TRANSACTION_INVALID_PARAMS:
                                                    //Failure
//                                                $paramsBaseTxn['txn_code'] = $txnCode;
                                                        $paramsBaseTxn['reversal_fee_amt'] = $feeComponent['partialFee'];
                                                        $paramsBaseTxn['reversal_service_tax'] = $feeComponent['serviceTax'];



                                                        $baseTxn->remitFailure($paramsBaseTxn);
                                                        // Remit request table update Array
                                                        $updateStatusArr['is_complete'] = FLAG_NO;
                                                        $updateStatusArr['status'] = STATUS_FAILURE;
                                                        $updateStatusArr['fund_holder'] = REMIT_FUND_HOLDER_OPS;
//                                                print 'Failed : ' . $resp . PHP_EOL;
                                                        $refundUrl = '/remit_kotak_beneficiary/failuretxn?rrid=' . $res;
                                                        $displayMsg = "Failure: " . $api->getMessage() . ". To process refund, <a href='" . $this->formatURL($refundUrl) . "'>click here</a>";
                                                        $datastatus['status_new'] = STATUS_FAILURE;
                                                        break;

                                                    case TRANSACTION_NORESPONSE:
                                                    case TRANSACTION_INVALID_RESPONSE_CODE:
                                                    case TRANSACTION_TIMEOUT:
                                                    case TRANSACTION_CHECKSUM_FAILED:
                                                    case TRANSACTION_INVALID_RESPONSE:

                                                        //No response
//                                                $paramsBaseTxn['fee_amt'] = $feeComponent['partialFee'];
//                                                $paramsBaseTxn['service_tax'] = $feeComponent['serviceTax'];
//                                                
//                                                $baseTxn->remitOnHold($paramsBaseTxn);
                                                    // Remit request table update Array
                                                    $updateStatusArr['is_complete'] = FLAG_NO;
                                                    $updateStatusArr['status'] = STATUS_HOLD;
                                                    $updateStatusArr['fund_holder'] = REMIT_FUND_HOLDER_OPS;
                                                    $updateStatusArr['hold_reason'] = $api->getMessage();
                                                    $displayMsg = "Your request has been submitted, the beneficiary's account will be credited soon & you will get an sms regarding the same";
                                                    $datastatus['status_new'] = STATUS_HOLD;
                                                    
                                                    
                                                    break;
                                            }
                                                    $datastatus['remittance_request_id'] = $res;
                                                    $datastatus['status_old'] = $remitanceStatus['status'];
                                                    $datastatus['by_remitter_id'] = $this->session->remitter_id;
                                                    $datastatus['by_agent_id'] = $user->id;
                                                    $datastatus['by_ops_id'] = 0;
                                                    $datastatus['date_created'] = new Zend_Db_Expr('NOW()');
                                                    $resLog = $remittancestatuslog->addStatus($datastatus);
                                            // SMS params
                                            $smsData = array(
                                                'amount' => $formdata['amount'],
                                                'nick_name' => $detail['nick_name'],
                                                'beneficiary_name' => $detail['name'],
                                                'remitter_name' => $detail['remitter_name'],
                                                'contact_email' => KOTAK_SHMART_EMAIL,
                                                'contact_number' => KOTAK_CALL_CENTRE_NUMBER,
                                                'remitter_phone' => $this->session->remitter_mobile_number,
                                                'beneficiary_phone' => $detail['mobile'],
                                                'txn_code' => $txnCode,
                                                'account_no' => $detail['bank_account_number'],
                                                'product_name' => KOTAK_SHMART_TRANSFER
                                            );
                                            //Success SMS
                                            if ($resp == TRANSACTION_SUCCESSFUL) {


                                                $m->kotakNeftSuccessRemitter($smsData);
                                                $this->_helper->FlashMessenger(
                                                        array(
                                                            'msg-success' => $displayMsg,
                                                        )
                                                );
                                            }// Hold SMS
                                            else if ($resp == TRANSACTION_NORESPONSE || $resp == TRANSACTION_CHECKSUM_FAILED || $resp == TRANSACTION_INVALID_RESPONSE || $resp == TRANSACTION_INVALID_RESPONSE_CODE || $resp == TRANSACTION_TIMEOUT) {

                                                $m->kotakInitiateRemitter($smsData);
                                                $this->_helper->FlashMessenger(
                                                        array(
                                                            'msg-error' => $displayMsg,
                                                        )
                                                );
                                            }//Failure SMS
                                            else if ($resp == TRANSACTION_FAILED || $resp == TRANSACTION_INVALID_PARAMS) {
                                                $m->kotakNeftFailureRemitter($smsData);
                                                $this->_helper->FlashMessenger(
                                                        array(
                                                            'msg-error' => $displayMsg,
                                                        )
                                                );
                                                }
                                                // Call API to get cr_response and final_response
                                                //Concatenate  credit code and credit msg
                                                //Adding values in Array
                                                $updateStatusArr['cr_response'] = '(' . $api->getAccountCreditRespCode() . ') ' . $api->getAccountCreditRespMsg();
                                                $updateStatusArr['final_response'] = $api->getMessage(TRUE);
                                                $resUpdate = $remittancerequest->updateReq($res, $updateStatusArr);
                                            } catch (App_Exception $e) {
                                                $errMsg = $e->getMessage();
//                                                $this->_helper->FlashMessenger(array('msg-error' => $errMsg));
                                                $updateStatusArr['is_complete'] = FLAG_NO;
                                                $updateStatusArr['status'] = STATUS_HOLD;
                                                $updateStatusArr['fund_holder'] = REMIT_FUND_HOLDER_OPS;
                                                $updateStatusArr['hold_reason'] = $errMsg;
                                                $resUpdate = $remittancerequest->updateReq($res, $updateStatusArr);
                                                
                                                $displayMsg = "Your request has been submitted, the beneficiary's account will be credited soon & you will get an sms regarding the same";
                                                $datastatus['status_new'] = STATUS_HOLD;
                                                $datastatus['remittance_request_id'] = $res;
                                                $datastatus['status_old'] = $remitanceStatus['status'];
                                                $datastatus['by_remitter_id'] = $this->session->remitter_id;
                                                $datastatus['by_agent_id'] = $user->id;
                                                $datastatus['by_ops_id'] = 0;
                                                $datastatus['date_created'] = new Zend_Db_Expr('NOW()');
                                                $resLog = $remittancestatuslog->addStatus($datastatus);
                                                
                                                $this->_helper->FlashMessenger(
                                                        array(
                                                            'msg-error' => $errMsg,
                                                        )
                                                );
                                                    
                                            } catch (Exception $e) {
                                                $errMsg = $e->getMessage();
//                                                $this->_helper->FlashMessenger(array('msg-error' => $errMsg));
                                                $updateStatusArr['is_complete'] = FLAG_NO;
                                                $updateStatusArr['status'] = STATUS_HOLD;
                                                $updateStatusArr['fund_holder'] = REMIT_FUND_HOLDER_OPS;
                                                $updateStatusArr['hold_reason'] = $errMsg;
                                                $resUpdate = $remittancerequest->updateReq($res, $updateStatusArr);
                                                
                                                $displayMsg = "Your request has been submitted, the beneficiary's account will be credited soon & you will get an sms regarding the same";
                                                $datastatus['status_new'] = STATUS_HOLD;
                                                $datastatus['remittance_request_id'] = $res;
                                                $datastatus['status_old'] = $remitanceStatus['status'];
                                                $datastatus['by_remitter_id'] = $this->session->remitter_id;
                                                $datastatus['by_agent_id'] = $user->id;
                                                $datastatus['by_ops_id'] = 0;
                                                $datastatus['date_created'] = new Zend_Db_Expr('NOW()');
                                                $resLog = $remittancestatuslog->addStatus($datastatus);
                                                
                                                $this->_helper->FlashMessenger(
                                                        array(
                                                            'msg-error' => $errMsg,
                                                        )
                                                );
                                            }
                                        }
                                        $this->_redirect($this->formatURL("/remit_kotak_beneficiary/searchremitter?flgSess=1"));
                                    } else {
                                        $this->_helper->FlashMessenger(
                                                array(
                                                    'msg-error' => 'Your request for fund transfer could not be initiated',
                                                )
                                        );
                                    }
                            } else {
                                $this->_helper->FlashMessenger(array('msg-error' => 'Please check your SMS for correct fund transfer amount',));
                            }
                        
                            $remitAmt = $formdata['amount'];
                            $remitFee = $fee;
                            $formdata['send_auth_code'] = 0;
                            $form->populate($formdata);
                        } else {
                            $this->_helper->FlashMessenger(array('msg-error' => 'Transaction already initiated. For details, please check Remitter Transactions or contact Support Team.'));
                        }
                    } catch (App_Exception $e) {
                            $errMsg = $e->getMessage();
                            $this->_helper->FlashMessenger(array('msg-error' => $errMsg));
                    } catch (Exception $e) {

                        $errMsg = $e->getMessage();
                        $this->_helper->FlashMessenger(array('msg-error' => $errMsg));
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    }
                }
            }
        }

        $this->view->form = $form;
        $this->view->detail = $detail;

        $this->view->remittanceAmount = $remitAmt;
        $this->view->remittanceFee = $remitFee;
    }


    public function deactivatebeneficiaryAction() {
        $this->title = 'Deactivate Beneficiary';
        $id = ($this->_getParam('id') > 0) ? $this->_getParam('id') : 0;
        $beneficiary = new Remit_Kotak_Beneficiary;
        $beneArr = $beneficiary->getBeneficiaryDetails($id);
        $this->view->bene_name = $beneArr['name'];
        $changeLog = new LogStatus();
        $user = Zend_Auth::getInstance()->getIdentity();
        $data = array('status' => STATUS_INACTIVE);
        $logData = array(
            'kotak_beneficiary_id' => $id,
            'by_agent_id' => $user->id,
            'status_old' => STATUS_ACTIVE,
            'status_new' => STATUS_INACTIVE);
        $res = $beneficiary->updateBeneficiaryDetails($data, $id);
        if ($res) {
            $changeLog->log($logData);
            $this->_helper->FlashMessenger(array('msg-success' => 'Beneficiary has been deactivated successfully'));
        } else {
            $this->_helper->FlashMessenger(array('msg-error' => 'Beneficiary could not be deactivated'));
        }
        $this->_redirect($this->formatURL("/remit_kotak_beneficiary/searchremitter?flgSess=1"));
    }

    /*
     * failurelist displays neft failed transactions
     */

    public function failuretxnAction() {

        $this->title = 'Kotak Remittance Failed Transactions';
        $user = Zend_Auth::getInstance()->getIdentity();
        $agentId = $user->id;
        $session = new Zend_Session_Namespace('App.Agent.Controller');
        $objRemitterModel = new Remit_Kotak_Remitter();
        $remittancerequest = new Remit_Kotak_Remittancerequest();
        $m = new App\Messaging\Remit\Kotak\Agent();
        $bank = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        $remitterId = isset($session->remitter_id) ? $session->remitter_id : 0;

        if ($remitterId == 0) {
            $this->_redirect($this->formatURL("/remit_kotak_beneficiary/searchremitter?flgSess=1"));
        }

        $remitterDetail = $objRemitterModel->getRemitterById($remitterId);

        $remitRequestId = $this->_getParam('rrid');
        if (isset($remitRequestId) && $remitRequestId > 0) {
            $remitRequestDetail = $remittancerequest->getAgentRemittanceRequests(FLAG_FAILURE, 0, $remitRequestId);
//            echo '<pre>';print_r($remitRequestDetail);exit;
            $productId = $remitRequestDetail[0]['product_id'];

//            $refundAmt = $this->getRefundBreakup($productId, $agentId, $remitRequestDetail[0]['amount']);
            $refundAmt = $remitRequestDetail[0]['amount'] + $remitRequestDetail[0]['fee'] + $remitRequestDetail[0]['service_tax'];
            $reversalFee = $remitRequestDetail[0]['fee'];
            $reversalSt = $remitRequestDetail[0]['service_tax'];


            $limitObj = new BaseTxn();

            try {
                /* #1020 - no need to chk limit validations on refund
                 *    $params = array('agent_id' =>$agentId,
                  'product_id' =>$productId,
                  'remitter_id' =>$remitterId,
                  //                           'amount' =>$refundAmt['refundable_amount']
                  'amount' =>$refundAmt,
                  'reversal_fee_amt' =>$reversalFee,
                  'reversal_service_tax' =>$reversalSt,
                  );
                  if($limitObj->chkAllowRefundRemit($params)) { */
                if ($session->refund_auth_code == '') { // if not already assigned info in session
                    $authCode = $m->generateRandom6DigitCode();
                    $session->refund_auth_code = $authCode;
//                        $session->refundable_amount = $refundAmt['refundable_amount'];
//                        $session->refund_fee = $refundAmt['refund_fee'];
                    $session->refundable_amount = $refundAmt;
                    $session->remittance_request_id = $remitRequestId;
                }
                else
                    $authCode = $session->refund_auth_code;

                $smsData['auth_code'] = $authCode;
//                    $smsData['amount'] = $refundAmt['refundable_amount'];
                $smsData['amount'] = $refundAmt;
                $smsData['nick_name'] = $remitRequestDetail[0]['nick_name'];
                $smsData['mobile'] = $remitterDetail['mobile'];
                $smsData['txn_no'] = $remitRequestDetail[0]['txn_code'];
                $smsData['reject_reason'] = $remitRequestDetail[0]['final_response'];
                $m->kotakInitiateRefundRemitter($smsData);
                $this->_helper->FlashMessenger(array('msg-success' => 'Please check the authorization code on your mobile',));
                $this->_redirect($this->formatURL("/remit_kotak_beneficiary/refund"));
                /*               } */
            } catch (Exception $e) {
                $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
            }
        }

        $txnDetail = $remittancerequest->getAgentRemittanceRequests(FLAG_FAILURE, $remitterId);
        $cntTxn = count($txnDetail);
        for ($i = 0; $i < $cntTxn; $i++) {
//            $refundAmt = $this->getRefundBreakup($txnDetail[$i]['product_id'], $agentId, $txnDetail[$i]['amount']);
//            $txnDetail[$i]['refundable_amount'] = $refundAmt['refundable_amount'];
//            $txnDetail[$i]['refund_fee'] = $refundAmt['refund_fee'];
            $txnDetail[$i]['refundable_amount'] = $txnDetail[$i]['amount'] + $txnDetail[$i]['fee'] + $txnDetail[$i]['service_tax'];
        }
        $countTotalRefunds = $remittancerequest->getRemitterRefundCount($remitterId);
        $this->view->remitterDetail = $remitterDetail;
        $this->view->paginator = $txnDetail;
        $this->view->countTotalRefunds = $countTotalRefunds;
    }

    /* refundAction will refund amount to remitter if any refundable
     */

    public function refundAction() {
        $this->title = $this->view->pageTitle = 'Refund Remitter';
        $m = new App\Messaging\Remit\Kotak\Agent();
        $user = Zend_Auth::getInstance()->getIdentity();
        $agentId = $user->id;
        $session = new Zend_Session_Namespace('App.Agent.Controller');
        $bank = App_DI_Definition_Bank::getInstance(BANK_KOTAK);

        $remitRequestId = isset($session->remittance_request_id) ? $session->remittance_request_id : 0;
        $this->view->remitRequestId = $remitRequestId;
        if (!$remitRequestId) {
            $this->_helper->FlashMessenger(array('msg-error' => 'Refund could not be processed at the time',));
            $this->_redirect($this->formatURL("/remit_kotak_beneficiary/searchremitter?flgSess=1"));
        }
        $remitterId = isset($session->remitter_id) ? $session->remitter_id : 0;

        $objRemitterModel = new Remit_Kotak_Remitter();
        $remittancerequest = new Remit_Kotak_Remittancerequest();



        $remitterDetail = $objRemitterModel->getRemitterById($remitterId);
        $remitRequestDetail = $remittancerequest->getAgentRemittanceRequests(FLAG_FAILURE, 0, $remitRequestId);
        $productId = $remitRequestDetail[0]['product_id'];
        $reversalRemitFee = $remitRequestDetail[0]['fee'];
        $reversalRemitSt = $remitRequestDetail[0]['service_tax'];
//        $refundFee = isset($session->refund_fee)?$session->refund_fee:0;
        $refundableAmount = $remitRequestDetail[0]['amount'] + $remitRequestDetail[0]['fee'] + $remitRequestDetail[0]['service_tax'];
        $originalAgentId= $remitRequestDetail[0]['agent_id'];

        $this->view->remitterDetail = $remitterDetail;
        $this->view->remitRequestDetail = $remitRequestDetail;
        $this->view->refundable_amount = $refundableAmount;
//        $this->view->refund_fee = $refundFee;
        // Get our form and validate it
        $form = new Remit_Kotak_RefundRemitterForm(array(
                    'action' => $this->formatURL('/remit_kotak_beneficiary/refund'),
                    'method' => 'post',
                    'name' => 'frmRefund',
                    'id' => 'frmRefund'
                ));

        $this->view->form = $form;
        $formData = $this->_request->getPost();
        $authCode = isset($formData['auth_code']) ? $formData['auth_code'] : '';
        $btnRefund = isset($formData['is_submit']) ? $formData['is_submit'] : false;
        $refundAuthCode = isset($session->refund_auth_code) ? $session->refund_auth_code : 0;
        
        $agentUser = new AgentUser();
        $agentDetails = $agentUser->getClosedLoopAgentDetailsById($agentId);
        $originalAgentDetails = $agentUser->getClosedLoopAgentDetailsById( $originalAgentId);
        $groupCheck = true;        
        
        if(isset($agentDetails['group']))
        {     
            if(isset($originalAgentDetails['group']))
            {  
                if(strcasecmp($agentDetails['group'],$originalAgentDetails['group'])==0)
                { 
                     $groupCheck = true; 
                }
                else
                {   
                     $groupCheck = false; 
                }
            }
            else
            {  
                 $groupCheck = false;
            }
        }
        else
        {  
            if(isset($originalAgentDetails['group']))
            {                                
                $groupCheck = false;     
            }
            else
            {   
                $groupCheck = true;
            }   
        } 
                                                
        // adding details in db
        if ($btnRefund) {
            if ($form->isValid($this->getRequest()->getPost())) {
                if ($authCode == $refundAuthCode) {
                   if ($groupCheck) {
                    $objBaseTxn = new BaseTxn();
                    $objRemitStatusLog = new Remit_Kotak_Remittancestatuslog();

//                    $feeServicetaxInfo = Util::getFeeComponents($session->refund_fee);
//                    $calculatedFee = isset($feeServicetaxInfo['partialFee'])?$feeServicetaxInfo['partialFee']:'0';
//                    $serviceTax = isset($feeServicetaxInfo['serviceTax'])?$feeServicetaxInfo['serviceTax']:'0';
                    $calculatedFee = 0;
                    $serviceTax = 0;

                    $remitRefundParams = array(
                        'remit_request_id' => $remitRequestId,
                        'remitter_id' => $remitterId,
                        'agent_id' => $agentId,
                        'product_id' => $productId,
                        'amount' => $session->refundable_amount,
                        'fee_amt' => $calculatedFee,
                        'service_tax' => $serviceTax,
                        'reversal_fee_amt' => $reversalRemitFee,
                        'reversal_service_tax' => $reversalRemitSt,
                        'bank_unicode' => $bank->bank->unicode,
                        'original_agent_id'=>$originalAgentId,                        
                    );

                    /*                     * ********** doing txn here ********** */
                    try {
                        $txnCode = $objBaseTxn->remitRefund($remitRefundParams); //true;
                    } catch (Exception $e) {
                        $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                        //$form->populate($formdata);
                    }
                    /*                     * ********** doing txn here over ********** */

                    if ($txnCode) {

                        try {
                            // updating to remittance request table
                            $refundData = array('is_complete' => FLAG_YES,
                                'status' => STATUS_REFUND,
                                'fund_holder' => REMIT_FUND_HOLDER_REMITTER
                            );

                            $res = $remittancerequest->updateReq($remitRequestId, $refundData);

                            /*                             * ********** adding to remittance refund table ************ */
                            $refundAmt = $session->refundable_amount - $reversalRemitFee - $reversalRemitSt;
                            $remitRefundData = array(
                                'remitter_id' => $remitterId,
                                'remittance_request_id' => $remitRequestId,
                                'agent_id' => $agentId,
                                'product_id' => $productId,
                                'amount' => $refundAmt,
                                'fee' => $calculatedFee,
                                'service_tax' => $serviceTax,
                                'reversal_fee' => $reversalRemitFee,
                                'reversal_service_tax' => $reversalRemitSt,
                                'txn_code' => $txnCode,
                                'status' => STATUS_SUCCESS,
                                'date_created' => date('Y-m-d H:i:s')
                            );

                            $res = $remittancerequest->addRemittanceRefund($remitRefundData);
                            /*                             * ********** adding to remittance refund table over here ************ */
                            $smsArr = array('amount' => $session->refundable_amount,
                                'nick_name' => $remitRequestDetail[0]['nick_name'],
                                'remitter_phone' => $remitterDetail['mobile']);
                            $m->kotakRefundSmsRemitter($smsArr);

                            /*                             * ********** updating to remittance status log table ******* */
                            $logData = array(
                                'remittance_request_id' => $remitRequestId,
                                'status_old' => FLAG_FAILURE,
                                'status_new' => STATUS_REFUND,
                                'by_remitter_id' => $remitterId,
                                'by_agent_id' => $agentId,
                                'date_created' => date('Y-m-d H:i:s')
                            );

                            $objRemitStatusLog->addStatus($logData);
                            /*                             * ********** updating to remittance status log table over ******* */

                            $this->_helper->FlashMessenger(array('msg-success' => 'Amount has been refunded successfully',));
                            unset($session->remittance_request_id);
                            unset($session->refund_auth_code);
                            unset($session->refundable_amount);
//                            unset($session->refund_fee);

                            $this->_redirect($this->formatURL("/remit_kotak_beneficiary/searchremitter?flgSess=1"));
                        } catch (Exception $e) {
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                            $form->populate($formData);
                        }
                    }
                  }
                  else
                  {
                     $this->_helper->FlashMessenger(array('msg-error' => 'Sorry, you cannot initiate a refund as the remittance transaction originated from  other network',));    
                  }
                
                }
                else {
                    $this->_helper->FlashMessenger(array('msg-error' => 'Please check your mobile for correct auth code',));
                }
            }
        } //  if btnRefund ends
    }
  public function transferfundstaticcodeAction() {

        $this->title = 'Transfer Funds';
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new Remit_Kotak_FundTransferStaticCodeForm();
        $formdata = $this->_request->getPost();
        $beneficiary = new Remit_Kotak_Beneficiary();
        $remittancerequest = new Remit_Kotak_Remittancerequest();
        $remittancestatuslog = new Remit_Kotak_Remittancestatuslog();
        $beneId = ($this->_getParam('id') > 0) ? $this->_getParam('id') : 0;
        $m = new App\Messaging\Remit\Kotak\Agent();
        if (!$beneId) {
            $this->_redirect($this->formatURL("/remit_kotak_beneficiary/searchremitter?flgSess=1"));
        }
        $detail = $beneficiary->getBeneficiaryDetails($beneId);

        $feeplan = new FeePlan();
        $feeArr = $feeplan->getRemitterFee($detail['product_id'], $user->id);
        $baseTxn = new BaseTxn();
        $bank = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        $displayMsg = '';
        $remitAmt = 0;
        $remitFee = 0;
        $dbPass = $detail['static_code'];
        $this->session->fundtransfer_auth = $dbPass;
       
//        $this->session->fundtransfer_auth = $detail['static_code'];
            if ($this->getRequest()->isPost()) {
                if ($form->isValid($this->getRequest()->getPost())) {
                     
                    if ($this->session->allow_remittance == TRUE) {
                    try {
                         $formPass = BaseUser::hashPassword($formdata['auth_code'],'agent');
      
//                        if ($formdata['amount'] == $this->session->fundtransfer_amount) {
                            if ($formPass == $this->session->fundtransfer_auth) {
                                //Check Limits
                               
                $fee = '0.00';
                foreach ($feeArr as $val) {
                   if ($val['typecode'] == TXNTYPE_REMITTANCE_FEE) {
                        $val['amount'] = $formdata['amount'];
                        $val['return_type'] = TYPE_FEE;
                        $fee = Util::calculateRoundedFee($val);
                        App_Logger::log($fee, Zend_Log::ERR);
                        break;
                   }
                }
                
                $feeComponent = Util::getFeeComponents($fee);
                $params = array('agent_id' => $user->id,
                    'product_id' => $detail['product_id'],
                    'remitter_id' => $this->session->remitter_id,
                    'amount' => $formdata['amount'],
                    'fee_amt' => $feeComponent['partialFee'],
                    'service_tax' => $feeComponent['serviceTax'],
                    'bank_unicode' => $bank->bank->unicode
                );
                if ($remittancerequest->chkAllowRemit($params)) {
                    if (!empty($feeArr)) {
                        $userData = array('mobile1' => $this->session->remitter_mobile_number,
                            'amount' => $formdata['amount'],
                            'nick_name' => $detail['nick_name'],
                            'account_no' => $detail['bank_account_number'],
                            'fee' => $fee
                        );
                    
                        $this->session->allow_remittance = TRUE;

                        $form->populate($formdata);
                    } else {
                        $this->_helper->FlashMessenger(array('msg-error' => 'Product not assigned to agent for the day',));
                    }
                }
                $remitAmt = $formdata['amount'];
                $remitFee = $fee;
            
                                //Check Limits ends
                               
                                $data = array();
                                $data['amount'] = $formdata['amount'];
                                $data['fund_holder'] = REMIT_FUND_HOLDER_REMITTER;
                                $data['agent_id'] = $user->id;
                                $data['remitter_id'] = $this->session->remitter_id;
                                $data['beneficiary_id'] = $beneId;
                                $data['ops_id'] = TXN_OPS_ID;
                                $data['product_id'] = $detail['product_id'];
                                $data['date_created'] = new Zend_Db_Expr('NOW()');
                                $data['fee'] = $feeComponent['partialFee'];
                                $data['service_tax'] = $feeComponent['serviceTax'];
                                $data['status'] = STATUS_INCOMPLETE;
                                $data['sender_msg'] = ($formdata['sender_msg'] == '') ? '': $formdata['sender_msg'] ;
                                $this->session->allow_remittance = FALSE;
                                $res = $remittancerequest->save($data);
                                $datastatus = array();
                                $datastatus['remittance_request_id'] = $res;
                                $datastatus['status_old'] = '';
                                $datastatus['status_new'] = STATUS_INCOMPLETE;
                                $datastatus['by_remitter_id'] = $this->session->remitter_id;
                                $datastatus['by_agent_id'] = $user->id;
                                $datastatus['by_ops_id'] = 0;
                                $datastatus['date_created'] = new Zend_Db_Expr('NOW()');
                                $resLog = $remittancestatuslog->addStatus($datastatus);
                                if ($res > 0) {
                                    $paramsArr = array('agent_id' => $user->id,
                                        'product_id' => $detail['product_id'],
                                        'remitter_id' => $this->session->remitter_id,
                                        'amount' => $formdata['amount'],
                                        'remit_request_id' => $res,
                                        'fee_amt' => $feeComponent['partialFee'],
                                        'service_tax' => $feeComponent['serviceTax'],
                                        'bank_unicode' => $bank->bank->unicode
                                    );
                                    $txnCode = $remittancerequest->initiateRemit($paramsArr);
                                    if ($txnCode) {
                                        $updateArr = array(
                                            'status' => STATUS_IN_PROCESS,
                                            'fund_holder' => REMIT_FUND_HOLDER_NEFT,
                                            'txn_code' => $txnCode
                                        );
                                        $resUpdate = $remittancerequest->updateReq($res, $updateArr);
                                        $datastatus = array();
                                        $datastatus['remittance_request_id'] = $res;
                                        $datastatus['status_old'] = STATUS_INCOMPLETE;
                                        $datastatus['status_new'] = STATUS_IN_PROCESS;
                                        $datastatus['by_remitter_id'] = $this->session->remitter_id;
                                        $datastatus['by_agent_id'] = $user->id;
                                        $datastatus['by_ops_id'] = 0;
                                        $datastatus['date_created'] = new Zend_Db_Expr('NOW()');
                                        $resLog = $remittancestatuslog->addStatus($datastatus);
                                        if($formdata['sender_msg'] == ''){
                                            $remarks = '';
                                        } else {
                                            $remarks = Util::removeSpecialChars($formdata['sender_msg']);
                                            $remarks = Util::truncateString($remarks, 50);
                                        }
                                        
                                        $partnerName=SHMART_AGENT_NETWORK;
                                        $agentUser = new AgentUser();
                                        $agentDetails = $agentUser->getClosedLoopAgentDetailsById($user->id);                                        
                                         if(isset($agentDetails['group']))
                                            {
                                                       $partnerName = $agentDetails['group'];
                                            }

                                        $paramApi = array(
                                            'traceNumber' => $txnCode,
                                            'beneIFSC' => $detail['ifsc_code'],
                                            'beneAccount' => $detail['bank_account_number'],
                                            'amount' => $formdata['amount'],
                                            'remarks' => $remarks,
                                            'partnerName' => $partnerName
                                              );

                                        try {
                                            $api = new App_Api_Kotak_Remit_Transaction();
                                            $resp = $api->creditAccount($paramApi);
                                            $updateStatusArr = array();
                                            $paramsBaseTxn = array(
                                                'remit_request_id' => $res,
                                                'product_id' => $detail['product_id'],
                                                'amount' => $formdata['amount'],
                                                'bank_unicode' => $bank->bank->unicode,
                                                'agent_id' => $user->id
                                            );
                                        $remitanceStatus = $remittancerequest->getRemitterRequestsInfo($res);
                                        $datastatus = array();
                                        switch ($resp) {
                                                case TRANSACTION_SUCCESSFUL:
                                                    //Success
                                                    $paramsBaseTxn['beneficiary_id'] = $beneId;
                                                    $paramsBaseTxn['txn_code'] = $txnCode;
                                                    $paramsBaseTxn['fee_amt'] = $feeComponent['partialFee'];
                                                    $paramsBaseTxn['service_tax'] = $feeComponent['serviceTax'];

                                                    $baseTxn->remitSuccess($paramsBaseTxn);
                                                    // Remit request table update Array
                                                    $updateStatusArr['is_complete'] = FLAG_YES;
                                                    $updateStatusArr['status'] = STATUS_SUCCESS;
                                                    $updateStatusArr['fund_holder'] = REMIT_FUND_HOLDER_BENEFICIARY;
                                                    $displayMsg = "Your remittance request has been successfully processed.";
                                                    $datastatus['status_new'] = STATUS_SUCCESS;
                                                    break;

                                                case TRANSACTION_FAILED:
                                                case TRANSACTION_INVALID_PARAMS:
                                                    //Failure
//                                                $paramsBaseTxn['txn_code'] = $txnCode;
                                                        $paramsBaseTxn['reversal_fee_amt'] = $feeComponent['partialFee'];
                                                        $paramsBaseTxn['reversal_service_tax'] = $feeComponent['serviceTax'];



                                                        $baseTxn->remitFailure($paramsBaseTxn);
                                                        // Remit request table update Array
                                                        $updateStatusArr['is_complete'] = FLAG_NO;
                                                        $updateStatusArr['status'] = STATUS_FAILURE;
                                                        $updateStatusArr['fund_holder'] = REMIT_FUND_HOLDER_OPS;
//                                                print 'Failed : ' . $resp . PHP_EOL;
                                                        $refundUrl = '/remit_kotak_beneficiary/failuretxn?rrid=' . $res;
                                                        $displayMsg = "Failure: " . $api->getMessage() . ". To process refund, <a href='" . $this->formatURL($refundUrl) . "'>click here</a>";
                                                        $datastatus['status_new'] = STATUS_FAILURE;
                                                        break;

                                                    case TRANSACTION_NORESPONSE:
                                                    case TRANSACTION_INVALID_RESPONSE_CODE:
                                                    case TRANSACTION_TIMEOUT:
                                                    case TRANSACTION_CHECKSUM_FAILED:
                                                    case TRANSACTION_INVALID_RESPONSE:

                                                        //No response
//                                                $paramsBaseTxn['fee_amt'] = $feeComponent['partialFee'];
//                                                $paramsBaseTxn['service_tax'] = $feeComponent['serviceTax'];
//                                                
//                                                $baseTxn->remitOnHold($paramsBaseTxn);
                                                    // Remit request table update Array
                                                    $updateStatusArr['is_complete'] = FLAG_NO;
                                                    $updateStatusArr['status'] = STATUS_HOLD;
                                                    $updateStatusArr['fund_holder'] = REMIT_FUND_HOLDER_OPS;
                                                    $updateStatusArr['hold_reason'] = $api->getMessage();
                                                    $displayMsg = "Your request has been submitted, the beneficiary's account will be credited soon & you will get an sms regarding the same";
                                                    $datastatus['status_new'] = STATUS_HOLD;
                                                    
                                                    
                                                    break;
                                            }
                                                    $datastatus['remittance_request_id'] = $res;
                                                    $datastatus['status_old'] = $remitanceStatus['status'];
                                                    $datastatus['by_remitter_id'] = $this->session->remitter_id;
                                                    $datastatus['by_agent_id'] = $user->id;
                                                    $datastatus['by_ops_id'] = 0;
                                                    $datastatus['date_created'] = new Zend_Db_Expr('NOW()');
                                                    $resLog = $remittancestatuslog->addStatus($datastatus);
                                            // SMS params
                                            $smsData = array(
                                                'amount' => $formdata['amount'],
                                                'nick_name' => $detail['nick_name'],
                                                'beneficiary_name' => $detail['name'],
                                                'remitter_name' => $detail['remitter_name'],
                                                'contact_email' => KOTAK_SHMART_EMAIL,
                                                'contact_number' => KOTAK_CALL_CENTRE_NUMBER,
                                                'remitter_phone' => $this->session->remitter_mobile_number,
                                                'beneficiary_phone' => $detail['mobile'],
                                                'txn_code' => $txnCode,
                                                'account_no' => $detail['bank_account_number'],
                                                'product_name' => KOTAK_SHMART_TRANSFER
                                            );
                                            //Success SMS
                                            if ($resp == TRANSACTION_SUCCESSFUL) {


                                                $m->kotakNeftSuccessRemitter($smsData);
                                                $this->_helper->FlashMessenger(
                                                        array(
                                                            'msg-success' => $displayMsg,
                                                        )
                                                );
                                            }// Hold SMS
                                            else if ($resp == TRANSACTION_NORESPONSE || $resp == TRANSACTION_CHECKSUM_FAILED || $resp == TRANSACTION_INVALID_RESPONSE || $resp == TRANSACTION_INVALID_RESPONSE_CODE || $resp == TRANSACTION_TIMEOUT) {

                                                $m->kotakInitiateRemitter($smsData);
                                                $this->_helper->FlashMessenger(
                                                        array(
                                                            'msg-error' => $displayMsg,
                                                        )
                                                );
                                            }//Failure SMS
                                            else if ($resp == TRANSACTION_FAILED || $resp == TRANSACTION_INVALID_PARAMS) {
                                                $m->kotakNeftFailureRemitter($smsData);
                                                $this->_helper->FlashMessenger(
                                                        array(
                                                            'msg-error' => $displayMsg,
                                                        )
                                                );
                                                }
                                                // Call API to get cr_response and final_response
                                                //Concatenate  credit code and credit msg
                                                //Adding values in Array
                                                $updateStatusArr['cr_response'] = '(' . $api->getAccountCreditRespCode() . ') ' . $api->getAccountCreditRespMsg();
                                                $updateStatusArr['final_response'] = $api->getMessage(TRUE);
                                                $resUpdate = $remittancerequest->updateReq($res, $updateStatusArr);
                                            } catch (App_Exception $e) {
                                                $errMsg = $e->getMessage();
                                                $this->_helper->FlashMessenger(array('msg-error' => $errMsg));
                                            } catch (Exception $e) {
                                                $errMsg = $e->getMessage();
                                                $this->_helper->FlashMessenger(array('msg-error' => $errMsg));
                                            }
                                        }
                                        $this->_redirect($this->formatURL("/remit_kotak_beneficiary/searchremitter?flgSess=1"));
                                    } else {
                                        $this->_helper->FlashMessenger(
                                                array(
                                                    'msg-error' => 'Your request for fund transfer could not be initiated',
                                                )
                                        );
                                    }
                                } else {
                                    $this->_helper->FlashMessenger(array('msg-error' => 'Static code entered is not correct',));
                                }
//                            } else {
//                                $this->_helper->FlashMessenger(array('msg-error' => 'Please check your SMS for correct fund transfer amount',));
//                            }
                        } catch (App_Exception $e) {
                            $errMsg = $e->getMessage();
                            $this->_helper->FlashMessenger(array('msg-error' => $errMsg));
                        } catch (Exception $e) {

                            $errMsg = $e->getMessage();
                            $this->_helper->FlashMessenger(array('msg-error' => $errMsg));
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        }
                        $remitAmt = $formdata['amount'];
                        $remitFee = $fee;
                        $formdata['send_auth_code'] = 0;
                        $form->populate($formdata);
                    } else {
                        $this->_helper->FlashMessenger(array('msg-error' => 'Transaction already initiated. For details, please check Remitter Transactions or contact Support Team.'));
                    }
                }
            }
     

        $this->view->form = $form;
        $this->view->detail = $detail;

        $this->view->remittanceAmount = $remitAmt;
        $this->view->remittanceFee = $remitFee;
    }
}
