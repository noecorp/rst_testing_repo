<?php

/**
 * Default entry point in the application
 *
 * @package frontend_controllers
 * @copyright company
 */
class HelpdeskController extends App_Operation_Controller {

    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init() {

        // init the parent
        parent::init();

        //$this->_addCommand(new App_Command_SendEmail());
    }

    /**
     * Controller's entry point
     *
     * @access public
     * @return void
     */
    public function indexAction() {
        
    }

    /* resendactivationcodeAction function will hit the App_Api_MVC_Transactions api
     * for ResendActivationCode function, n in return will show the corresponding details
     */

    public function resendactivationcodeAction() {
        $this->title = 'Resend Activation Code';
        $request = $this->getRequest();

        // Get our form and validate it
        $form = new ResendActivationCodeForm(array('action' => $this->formatURL('/helpdesk/resendactivationcode'),
            'method' => 'post',
        ));
        $this->view->form = $form;
        $formData = $this->_request->getPost(); //$form->getValues();//
        $btnSubmit = isset($formData['btn_submit']) ? $formData['btn_submit'] : '';
        $isError = false;

        if ($btnSubmit) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $objCH = new Mvc_Axis_CardholderUser();
                $chInfo = $objCH->getCardHolderInfoApproved('', '', $formData['MobileNumber']);
                $mvcType = isset($chInfo->customer_mvc_type) ? $chInfo->customer_mvc_type : '';
                $existingDeviceId = isset($chInfo->device_id) ? $chInfo->device_id : '';

                // check for customber mvc type
                if ($mvcType == CUSTOMER_MVC_TYPE_MVCC) {
                    $deviceId = isset($formData['DeviceID']) ? $formData['DeviceID'] : '';
                    //echo $deviceId; exit;
                    if (trim($deviceId) == '' || (strlen($deviceId) < 7 || strlen($deviceId) > 10) || !is_numeric($deviceId)) {
                        $this->_helper->FlashMessenger(array('msg-error' => 'Please enter Device Id with 7-10 digits',));
                        $isError = true;
                    }
                }

                if (!$isError) {
                    if (!empty($chInfo)) {
                        $mvcData['MobileNumber'] = '+91' . $formData['MobileNumber'];
                        $mvcData['DeviceID'] = $formData['DeviceID'];
                        $mvcData['RequestRefNumber'] = $formData['RequestRefNumber'];
                        $mvcData['CRN'] = $chInfo->crn;

                        if ($mvcData['MobileNumber'] != '' || $mvcData['DeviceID'] != '' || $mvcData['RequestRefNumber'] != '' || $mvcData['CRN'] != '') {
                            try {
                                $mvc = new App_Api_MVC_Transactions();
                                $resp = $mvc->ResendActivationCode($mvcData);
                                $this->view->info = Util::filterMVCResponse($mvc->getLastResponse());
                            } catch (Exception $e) {
                                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                            }

                            if (isset($resp) && !$resp && $mvc->getError() != '')
                                $this->_helper->FlashMessenger(array('msg-error' => $mvc->getError(),));
                        }
                    } else {
                        $this->_helper->FlashMessenger(array('msg-error' => 'Mobile number not found',));
                    }
                    // getting response from MVC
                }
            }
        }


        $this->view->formData = $formData;
    }

    /* closeaccountAction function will hit the App_Api_MVC_Transactions api
     * for closeaccountAction function, n in return will show the corresponding details
     */

    public function closeaccountAction() {
        $this->title = 'Close Account';
        $request = $this->getRequest();
        $user = Zend_Auth::getInstance()->getIdentity();

        // Get our form and validate it
        $form = new CloseAccountForm(array('action' => $this->formatURL('/helpdesk/closeaccount'),
            'method' => 'post',
        ));

        $this->view->form = $form;
        $formData = $this->_request->getPost(); //$form->getValues();//
        $btnSubmit = isset($formData['btn_submit']) ? $formData['btn_submit'] : '';

        if ($btnSubmit) {
            if ($form->isValid($this->getRequest()->getPost())) {

                $objCH = new Mvc_Axis_CardholderUser();
                $chInfo = $objCH->getCardHolderInfoApproved('', '', $formData['MobileNumber']);

                if (!empty($chInfo)) {

                    $mvcData['CRN'] = $this->view->crn = $chInfo->crn;
                    $mvcData['RequestRefNumber'] = $formData['RequestRefNumber'];

                    // getting response from mvc

                    try {

                        $mvc = new App_Api_MVC_Transactions();
                        $resp = $mvc->CloseAccount($mvcData);
                        $this->view->info = Util::filterMVCResponse($mvc->getLastResponse());
                    } catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                    }
                    if (!$resp && $mvc->getError() != '') {
                        $this->_helper->FlashMessenger(array('msg-error' => $mvc->getError(),));
                    } else {
                        // updating closed status in t_cardholders table if ch is closed by mvc
                        $chData = array('enroll_status' => STATUS_CLOSED, 'close_datetime' => NEW Zend_Db_Expr('NOW()'));

                        // adding new log in t_cardholder_details table
                        $chDetailsData = array(
                            'email' => $chInfo->email,
                            'crn' => $chInfo->crn,
                            'title' => $chInfo->title,
                            'first_name' => $chInfo->first_name,
                            'middle_name' => $chInfo->middle_name,
                            'last_name' => $chInfo->last_name,
                            'mobile_country_code' => $chInfo->mobile_country_code,
                            'mobile_number' => $chInfo->mobile_number,
                            'arn' => $chInfo->arn,
                            'date_of_birth' => $chInfo->date_of_birth,
                            'gender' => $chInfo->gender,
                            'customer_mvc_type' => $chInfo->customer_mvc_type,
                            'device_id' => $chInfo->device_id,
                            'status' => STATUS_ACTIVE,
                            'address_line1' => $chInfo->address_line1,
                            'address_line2' => $chInfo->address_line2,
                            'country' => $chInfo->country,
                            'state' => $chInfo->state,
                            'city' => $chInfo->city,
                            'pincode' => $chInfo->pincode,
                            'alternate_contact_number' => $chInfo->alternate_contact_number,
                            'educational_qualifications' => $chInfo->educational_qualifications,
                            'mother_maiden_name' => $chInfo->mother_maiden_name,
                            'shmart_rewards' => $chInfo->shmart_rewards,
                            'family_members' => $chInfo->family_members,
                            'already_bank_account' => $chInfo->already_bank_account,
                            'vehicle_type' => $chInfo->vehicle_type,
                            'by_ops_id' => $user->id,
                            'products_acknowledgement' => $chInfo->products_acknowledgement,
                            'rewards_acknowledgement' => $chInfo->rewards_acknowledgement,
                            'date_created' => NEW Zend_Db_Expr('NOW()')
                        );

                        $objCH->updateCardholderDetail($chData, $chInfo->id, $chDetailsData);
                    }
                } else {
                    $this->_helper->FlashMessenger(array('msg-error' => 'Mobile number not found',));
                }
            }
            $this->view->formData = $formData;
        }
    }

    /* unblockaccountAction function will hit the App_Api_MVC_Transactions api
     * for closeaccountAction function, n in return will show the corresponding details
     */

    public function unblockaccountAction() {
        $this->title = 'Unblock Account';
        $request = $this->getRequest();

        // Get our form and validate it
        $form = new UnblockAccountForm(array('action' => $this->formatURL('/helpdesk/unblockaccount'),
            'method' => 'post',
        ));
        $this->view->form = $form;
        $formData = $this->_request->getPost(); //$form->getValues();//
        $btnSubmit = isset($formData['btn_submit']) ? $formData['btn_submit'] : '';

        if ($btnSubmit) {
            if ($form->isValid($this->getRequest()->getPost())) {

                $objCH = new Mvc_Axis_CardholderUser();
                $chInfo = $objCH->getCardHolderInfoApproved('', '', $formData['MobileNumber']);

                if (!empty($chInfo)) {

                    $mvcData['CRN'] = $this->view->crn = $chInfo->crn;
                    $mvcData['RequestRefNumber'] = $formData['RequestRefNumber'];
                    $resp = '';
                    // getting response from mvc

                    try {
                        $mvc = new App_Api_MVC_Transactions();
                        $resp = $mvc->UnblockAccount($mvcData);
                        $this->view->info = Util::filterMVCResponse($mvc->getLastResponse());
                    } catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                    }
                    if (!$resp && $mvc->getError() != '')
                        $this->_helper->FlashMessenger(array('msg-error' => $mvc->getError(),));
                }
                else {
                    $this->_helper->FlashMessenger(array('msg-error' => 'Mobile number not found',));
                }
            }
            $this->view->formData = $formData;
        }
    }

    /* changeregisteredmobileAction function will hit the App_Api_MVC_Transactions api
     * for changeregisteredmobileAction function, n in return will show the corresponding details
     */

    public function changeregisteredmobileAction() {
        $this->title = 'Change Registered Mobile Number';
        $request = $this->getRequest();
        $m = new App\Messaging\MVC\Axis\Operation();

        // Get our form and validate it
        $form = new ChangeRegisteredMobileForm(array('action' => $this->formatURL('/helpdesk/changeregisteredmobile'),
            'method' => 'post',
            'name' => 'frmUpdateMob',
        ));

        $this->view->form = $form;
        $formData = $this->_request->getPost(); //$form->getValues();//
        $btnSubmit = isset($formData['submit']) ? $formData['submit'] : '';
        $btnAuth = isset($formData['send_auth_code']) ? $formData['send_auth_code'] : '0';
        $session = new Zend_Session_Namespace('App.Operation.Controller');
        //unset($session->update_mobile_auth);
        $updateMobileAuth = isset($session->update_mobile_auth) ? $session->update_mobile_auth : '';
        //echo $session->update_mobile_auth;
        /*         * ***** auth code sending handling **** */
        if ($btnAuth == 1) {

            // sending auth code to new mobile number             
            if (trim($formData['OldMobileNumber']) == '' || trim($formData['NewMobileNumber']) == '') {
                $this->_helper->FlashMessenger(array('msg-error' => 'Old and new mobile number cannot be empty',));
            } else if ($updateMobileAuth == '') {
                try {
                    //$respAuth = $this->sendAuthCode($formData);
                    $m->updateNewMobileAuth($formData);
                    $this->_helper->FlashMessenger(array('msg-success' => 'Authorization code has been sent on your mobile number',));
                    $form->getElement("send_auth_code")->setValue("0");
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                }
            }
        } /*         * ***** auth code sending handling over **** */


        /*         * * form submiting handling *** */
        if ($btnSubmit) {

            if ($form->isValid($this->getRequest()->getPost())) {

                /*                 * * validating auth code ** */
                $validatedMobAuth = isset($session->validated_update_mobile_auth) ? $session->validated_update_mobile_auth : '0';
                $updateMobileAuth = isset($session->update_mobile_auth) ? $session->update_mobile_auth : '';

                if ($validatedMobAuth != 1 && $updateMobileAuth != $formData['auth_code']) {
                    $this->_helper->FlashMessenger(array('msg-error' => 'Invalid authorization code',));
                } else {

                    $session->validated_update_mobile_auth = 1;
                    $objCH = new Mvc_Axis_CardholderUser();
                    $chInfo = $objCH->getCardHolderInfoApproved('', '', $formData['OldMobileNumber']);

                    if (!empty($chInfo)) {

                        $mvcData['CRN'] = $this->view->crn = $chInfo->crn;
                        $mvcData['RequestRefNumber'] = $formData['RequestRefNumber'];
                        $mvcData['OldMobileNumber'] = '+91' . $formData['OldMobileNumber'];
                        $mvcData['NewMobileNumber'] = '+91' . $formData['NewMobileNumber'];
                        $resp = '';
                        // getting response from mvc

                        try {
                            $mvc = new App_Api_MVC_Transactions();
                            $resp = $mvc->UpdateMobileNumber($mvcData);
                            $this->view->info = Util::filterMVCResponse($mvc->getLastResponse());
                            if ($resp) {
                                $user = Zend_Auth::getInstance()->getIdentity();
                                $chInfo->mobile_number = $formData['NewMobileNumber'];
                                $filteredCH = $this->filterCardholderDetails($chInfo);
                                $ch = $filteredCH['ch'];
                                $chDetails = $filteredCH['chDetails'];
                                $chDetails['by_ops_id'] = $user->id;
                                $chDetails['date_created'] = NEW Zend_Db_Expr('NOW()');
                                $objCH->updateCardholderDetail($ch, $chInfo->id, $chDetails);
                                //$objAlerts = new Alerts();
                                //$objAlerts->sendCHMobileChange(array('new_mobile'=>$formData['NewMobileNumber'], 'mobile1'=>$formData['OldMobileNumber']),'operation');
                                $m->cardholderMobileChange(array('newPhone' => $formData['NewMobileNumber'], 'mobile1' => $formData['OldMobileNumber'], 'oldPhone' => $formData['OldMobileNumber']));
                                unset($session->validated_update_mobile_auth);
                                unset($session->update_mobile_auth);
                            }
                        } catch (Exception $e) {
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                        }
                        if (!$resp && $mvc->getError() != '')
                            $this->_helper->FlashMessenger(array('msg-error' => $mvc->getError()));
                    }
                    else {
                        $this->_helper->FlashMessenger(array('msg-error' => 'Mobile number not found',));
                    }
                }
            }
        } /*         * * form submiting handling over *** */

        $this->view->formData = $formData;
        $form->populate($formData);
    }

    /* That function will filter the cardholder details in two array according to ch tables
     * like t_cardholders and t_cardholder_details
     */

    private function filterCardholderDetails($param) {

        $ch = array(
            'mobile_number' => $param->mobile_number,
            'id' => $param->id
        );


        $chDetails = array(
            'cardholder_id' => $param->id,
            'crn' => $param->crn,
            'email' => $param->email,
            'title' => $param->title,
            'first_name' => $param->first_name,
            'middle_name' => $param->middle_name,
            'last_name' => $param->last_name,
            'mobile_country_code' => $param->mobile_country_code,
            'mobile_number' => $param->mobile_number,
            'arn' => $param->arn,
            'alternate_contact_number' => $param->alternate_contact_number,
            'mother_maiden_name' => $param->mother_maiden_name,
            'res_type' => $param->res_type,
            'date_of_birth' => $param->date_of_birth,
            'gender' => $param->gender,
            'address_line1' => $param->address_line1,
            'address_line2' => $param->address_line2,
            'city' => $param->city,
            'state' => $param->state,
            'country' => $param->country,
            'pincode' => $param->pincode,
            'customer_mvc_type' => $param->customer_mvc_type,
            'device_id' => $param->device_id,
            'already_bank_account' => $param->already_bank_account,
            'vehicle_type' => $param->vehicle_type,
            'educational_qualifications' => $param->educational_qualifications,
            'family_members' => $param->family_members,
            'shmart_rewards' => $param->shmart_rewards,
            'products_acknowledgement' => $param->products_acknowledgement,
            'rewards_acknowledgement' => $param->rewards_acknowledgement,
            'status' => AGENT_ACTIVE_STATUS
        );


        return array('ch' => $ch, 'chDetails' => $chDetails);
    }

    /* sendAuthCode function will be responsible for
     * check duplicacy of mobile and will send auth code sms
     */

    private function sendAuthCode($param) {

        $oldMobileNumber = isset($param['OldMobileNumber']) ? $param['OldMobileNumber'] : '';
        $newMobileNumber = isset($param['NewMobileNumber']) ? $param['NewMobileNumber'] : '';

        if ($oldMobileNumber == '' || $newMobileNumber == '') {
            throw new Exception("Old and New mobile number should be filled!");
        } else if ($newMobileNumber == $oldMobileNumber) {
            throw new Exception("Old and New mobile number cannot be same!");
        } else {
            $mobObj = new Mobile();
            try {
                $mobExists = $mobObj->checkExist(array('mobile_number' => $oldMobileNumber));
                if (!$mobExists) {
                    throw new Exception("Old mobile number does not exist!");
                }

                $mobCheck = $mobObj->checkDuplicate($newMobileNumber, 'cardholder');
                $smsData = array('mobile1' => $newMobileNumber);
                $objAlert = new Alerts();
                $objAlert->sendUpdateNewMobileAuth($smsData, 'operation'); // sending sms on new mobile no.
            } catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                throw new Exception($e->getMessage());
                exit;
            }

            return $mobCheck;
        }
    }

    /* blockaccountAction function will hit the App_Api_MVC_Transactions api
     * for blockaccountAction function, n in return will show the corresponding details
     */

    public function blockaccountAction() {
        $this->title = 'Block Account';
        $request = $this->getRequest();

        // Get our form and validate it
        $form = new BlockAccountForm(array('action' => $this->formatURL('/helpdesk/blockaccount'),
            'method' => 'post',
        ));

        $this->view->form = $form;
        $formData = $this->_request->getPost(); //$form->getValues();//
        $btnSubmit = isset($formData['btn_submit']) ? $formData['btn_submit'] : '';

        if ($btnSubmit) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $objCH = new Mvc_Axis_CardholderUser();
                $chInfo = $objCH->getCardHolderInfoApproved('', '', $formData['MobileNumber']);
                if (!empty($chInfo)) {
                    $mvcData['CRN'] = $this->view->crn = $chInfo->crn;
                    $mvcData['RequestRefNumber'] = $formData['RequestRefNumber'];

                    // getting response from mvc

                    try {
                        $mvc = new App_Api_MVC_Transactions();
                        $resp = $mvc->BlockAccount($mvcData);
                        $this->view->info = Util::filterMVCResponse($mvc->getLastResponse());
                    } catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                    }
                    if (!$resp && $mvc->getError() != '')
                        $this->_helper->FlashMessenger(array('msg-error' => $mvc->getError(),));
                }
                else {
                    $this->_helper->FlashMessenger(array('msg-error' => 'Mobile number not found',));
                }
            }
        }
        $this->view->formData = $formData;
    }

    /* balanceenquiryAction function will hit the App_Api_ECS_Transactions api
     *  for balanceenquiryAction function, n in return will show the corresponding details
     */

    public function balanceenquiryAction() {
        $this->title = 'Balance Enquiry';
        $request = $this->getRequest();

        // Get our form and validate it
        $form = new BalanceEnquiryForm(array('action' => $this->formatURL('/helpdesk/balanceenquiry'),
            'method' => 'post',
        ));

        $this->view->form = $form;
        $formData = $this->_request->getPost(); //$form->getValues();//
        $btnSubmit = isset($formData['btn_submit']) ? $formData['btn_submit'] : '';

        if ($btnSubmit) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $objCH = new Mvc_Axis_CardholderUser();
                $chInfo = $objCH->getCardHolderInfoApproved('', '', $formData['MobileNumber']);

                if (!empty($chInfo)) {
                    $ecsData['cardNumber'] = $this->view->crn = $chInfo->crn;

                    // getting response from ecs
                    try {
                        $ecsApi = new App_Api_ECS_Transactions();
                        $resp = $ecsApi->balanceInquiry($ecsData);
                        $this->view->info = $ecsResp = Util::filterMVCResponse($ecsApi->getLastResponse());
                        $this->view->mobile = $formData['MobileNumber'];
                        $balanceEnquiryList = isset($ecsResp->balanceInquiryList) ? $ecsResp->balanceInquiryList : '';
                    } catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                    }

                    if (empty($balanceEnquiryList)) {
                        $this->_helper->FlashMessenger(array('msg-error' => 'Invalid mobile number',));
                    } else if (!$resp && $ecsApi->getError() != '') {
                        $this->_helper->FlashMessenger(array('msg-error' => $ecsApi->getError(),));
                    }
                } else {
                    $this->_helper->FlashMessenger(array('msg-error' => 'Invalid mobile number',));
                }
            }
        }
        $this->view->formData = $formData;
    }

    /* queryAccountInfoAction function will hit the App_Api_MVC_Transactions api
     * for getAccountInfo function, n in return will show the corresponding details
     */

    public function queryaccountinfoAction() {
        $this->title = 'Account Information';
        $request = $this->getRequest();

        // Get our form and validate it
        $form = new QueryAccountInfoForm(array('action' => $this->formatURL('/helpdesk/queryaccountinfo'),
            'method' => 'post',
        ));
        $this->view->form = $form;
        $formData = $this->_request->getPost(); //$form->getValues();//
        $btnSubmit = isset($formData['btn_submit']) ? $formData['btn_submit'] : '';

        if ($btnSubmit) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $objCH = new Mvc_Axis_CardholderUser();
                $chInfo = $objCH->getCardHolderInfoApproved('', '', $formData['MobileNumber']);

                if (!empty($chInfo)) {
                    $mvcData['CRN'] = $chInfo->crn;

                    if ($mvcData['CRN'] != '') {
                        try {
                            $mvc = new App_Api_MVC_Transactions();
                            $resp = $mvc->getAccountInfo($mvcData);
                            $info = Util::filterMVCResponse($mvc->getLastResponse());
                            $this->view->info = $info;
                        } catch (Exception $e) {
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                        }

                        if (!$resp && $mvc->getError() != '')
                            $this->_helper->FlashMessenger(array('msg-error' => $mvc->getError(),));
                    }
                } else {
                    $this->_helper->FlashMessenger(array('msg-error' => 'Mobile number not found',));
                }
                // getting response from MVC
            }
        }
        $this->view->formData = $formData;
    }

    /* querymvcstatusAction function will hit the App_Api_MVC_Transactions api
     * for queryMvcStatus function, n in return will show the corresponding details
     */

    public function querymvcstatusAction() {
        $this->title = 'MVC Status';
        $request = $this->getRequest();

        // Get our form and validate it
        $form = new QueryMVCStatusForm(array('action' => $this->formatURL('/helpdesk/querymvcstatus'),
            'method' => 'post',
        ));
        $this->view->form = $form;
        $formData = $this->_request->getPost(); //$form->getValues();//
        $btnSubmit = isset($formData['btn_submit']) ? $formData['btn_submit'] : '';

        if ($btnSubmit) {
            if ($form->isValid($this->getRequest()->getPost())) {

                if ($formData['PAN'] != '' && $formData['ExpiryDate'] != '' && $formData['CVV2'] != '' && $formData['Amount'] != '') {
                    $mvcData['PAN'] = $formData['PAN'];
                    $mvcData['ExpiryDate'] = $formData['ExpiryDate'];
                    $mvcData['CVV2'] = $formData['CVV2'];
                    $mvcData['Amount'] = $formData['Amount'];
                    try {
                        $mvc = new App_Api_MVC_Transactions();
                        $resp = $mvc->queryMvcStatus($mvcData);
                        $info = Util::filterMVCResponse($mvc->getLastResponse());
                        //echo "<pre>";print_r($info);
                        $this->view->info = $info;
                    } catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $this->_helper->FlashMessenger(array('msg-error' => $mvc->getMessage(),));
                    }

                    if (!$resp && $mvc->getError() != '')
                        $this->_helper->FlashMessenger(array('msg-error' => $mvc->getError(),));
                }


                // getting response from MVC
            }
            $this->view->formData = $formData;
        }
    }

    /* querymvctransactionAction function will hit the App_Api_MVC_Transactions api
     * for queryMvcTransaction function, n in return will show the corresponding details
     */

    public function querymvctransactionAction() {
        $this->title = 'MVC Transaction';
        $request = $this->getRequest();

        // Get our form and validate it
        $form = new QueryMVCTransactionForm(array('action' => $this->formatURL('/helpdesk/querymvctransaction'),
            'method' => 'post',
        ));
        $this->view->form = $form;
        $formData = $this->_request->getPost(); //$form->getValues();//
        $btnSubmit = isset($formData['btn_submit']) ? $formData['btn_submit'] : '';
        if ($btnSubmit) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $objCH = new Mvc_Axis_CardholderUser();
                $chInfo = $objCH->getCardHolderInfoApproved('', '', $formData['MobileNumber']);

                if (!empty($chInfo)) {
                    $mvcData['CRN'] = $chInfo->crn;
                    /* $dateTime = explode(" ", $formData['From']);
                      $date = Util::returnDateFormatted($dateTime[0], "d-m-Y", "Y-m-d", "-");
                      $mvcData['FromDateTime'] = $date." ".$dateTime[1];
                      $dateTime2 = explode(" ", $formData['To']);
                      $date2 = Util::returnDateFormatted($dateTime2[0], "d-m-Y", "Y-m-d", "-");
                      $mvcData['ToDateTime'] = $date2." ".$dateTime2[1]; */

                    $date = Util::returnDateFormatted($formData['fromDate'], "d-m-Y", "Y-m-d", "-");
                    $mvcData['FromDateTime'] = $date . " " . $formData['fromTime'];
                    $date = Util::returnDateFormatted($formData['toDate'], "d-m-Y", "Y-m-d", "-");
                    $mvcData['ToDateTime'] = $date . " " . $formData['toTime'];
                    //echo "<pre>";print_r($mvcData);  
                    if ($mvcData['CRN'] != '' && $mvcData['FromDateTime'] != '' && $mvcData['ToDateTime'] != '') {
                        try {
                            $mvc = new App_Api_MVC_Transactions();
                            $resp = $mvc->queryMvcTransaction($mvcData);
                            $info = Util::filterMVCResponse($mvc->getLastResponse());
                            //$info = $mvc->getLastResponse();
                            //echo "<pre>";print_r($info);
                            $this->view->info = $info;
                        } catch (Exception $e) {
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);
                            $this->_helper->FlashMessenger(array('msg-error' => $mvc->getMessage(),));
                        }

                        if (!$resp && $mvc->getError() != '')
                            $this->_helper->FlashMessenger(array('msg-error' => $mvc->getError(),));
                    }
                } else {
                    $this->_helper->FlashMessenger(array('msg-error' => 'Mobile number not found',));
                }
                // getting response from MVC
            }
            $this->view->formData = $formData;
        }
    }

    /* cancelchangeregisteredmobileAction function will destroy the session of change registered mobile number form
     * it is not expecting any argument.
     */

    public function cancelchangeregisteredmobileAction() {
        $session = new Zend_Session_Namespace('App.Operation.Controller');
        unset($session->update_mobile_auth);
        unset($session->validated_update_mobile_auth);

        $this->_redirect($this->formatURL('/helpdesk/changeregisteredmobile/'));
    }

    /**
     * kotakremittance
     * Kotak Remittance will display the Kotak Remittance on the basis of Remitter Mobile.
     */
    public function kotakremittanceAction() {
        // Get our form and validate it
        $form = new KotakRemittanceForm(array('action' => $this->formatURL('/helpdesk/kotakremittance'),
            'method' => 'POST',
        ));

        $this->view->form = $form;
        $formData = $this->_getAllParams();
        //echo '<pre>';print_r($formData);
        //$formData = $this->getRequest()->getPost();
        $page = $this->_getParam('page');

        if (isset($formData['submit'])) {
            //if ($form->isValid($formData)) {//Form Validation not required as need to display on paging
                $ts = strtotime('today - ' . KOTAK_REMITTANCE_SELECT_DAYS_DATA . ' days');
                $phone = $formData['mobile'];
                $startDate = new DateTime();
                $startDate->setTimestamp($ts);
                $toDate = clone $startDate;
                $toDate->setTimestamp(time());
                $remitKotakRemitter = new Remit_Kotak_Remitter();
                $this->view->paginator = $remitKotakRemitter->getRemitterTransactionByPhone($phone, $startDate->format('Y-m-d'), $toDate->format('Y-m-d'),TRUE, $page);
            //}
        }
        $this->view->formData = $formData;
    }

    
    /**
     * txninfo
     * Transaction Info - Kotak Remittance Transaction detail page
     * Request will come from kotakremittanceAction()
     */
    public function txninfoAction() {
        $traceNumber = $this->getRequest()->getParam('txn_code');
        if (!empty($traceNumber)) {
            $newTraceNumber = Util::generateRandomNumber(10);
            $requestArr = array(
                'qbTraceNumber' => $traceNumber,
                'traceNumber' => $newTraceNumber
            );
            
            $api = new App_Api_Kotak_Remit_Transaction();
            $api->queryAccountAndValidate($requestArr);
            $queryResponse = $api->getAccountQueryResponse();
            $info['auth_status'] = $queryResponse['auth_status'];
            $info['resp_code'] = $api->getResponseCode();
            $info['resp_desc'] = $api->getMessage(FALSE);
            $this->view->info = $info;

        }
    }

}