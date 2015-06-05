<?php

/**
 * Add Beneficiary
 *
 * @category backoffice
 * @package backoffice_controllers
 * @copyright company
 */
class Remit_Ratnakar_BeneficiaryController extends App_Agent_Controller {

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

    public function redirectToSearch() {
        $this->_redirect($this->formatURL("/remit_ratnakar_beneficiary/searchremitter?flgSess=1"));
    }
	

    public function searchremitterAction() {
        $this->session = new Zend_Session_Namespace("App.Agent.Controller");
        unset($this->session->fundtransfer_auth);
        unset($this->session->fundtransfer_amount);
        unset($this->session->beneficiary_auth);
        $this->session->newRemitter = FALSE;
        $flgSess = ($this->_getParam('flgSess') > 0) ? $this->_getParam('flgSess') : 0;
        $remitterId = ($this->session->remitter_id > 0) ? $this->session->remitter_id : 0;
        if ($flgSess == 0) {
            unset($this->session->remitter_mobile_number);
            unset($this->session->beneficiary_auth);
            unset($this->session->remitter_id);
            unset($this->session->fundtransfer_auth);
            unset($this->session->fundtransfer_amount);
            unset($this->session->refund_auth_code);
            unset($this->session->remittance_request_id);
            unset($this->session->refundable_amount);
            unset($this->session->refund_fee);
        }

        $this->title = 'Fund Transfer';
        $formData = $this->_request->getPost();

        // Agent phone entry form.
        $form = new Remit_Ratnakar_SearchRemitterForm(array(
                    'action' => $this->formatURL('/remit_ratnakar_beneficiary/searchremitter'),
                    'method' => 'POST',
                    'name' => 'frmverify',
                    'id' => 'frmverify'
                ));
        $this->view->showlist = FALSE;
        $btnSearch = isset($formData['submit_form']) ? true : false;
        $objRemittanceRequest = new Remit_Ratnakar_Remittancerequest();
        $remitters = new Remit_Ratnakar_Remitter();

        // adding details in db
        if ($btnSearch) {
            if ($this->getRequest()->isPost()) {
                if ($form->isValid($this->getRequest()->getPost())) {

                    if ($formData['phone'] != '') {
                        try {

                            $remitterdetail = $remitters->getRemitterStatus($formData['phone']);

                error_log('The Remitter Id1 is : ');
                error_log($remitterdetail['id']);

                            $this->session->remitter_mobile_number = $formData['phone'];
                            $this->session->remitter_id = $remitterdetail['id'];
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
                $remitterdetail = $remitters->getRemitterStatus($this->session->remitter_mobile_number);
		error_log('The Remitter Id is : ');
		error_log($remitterdetail['id']);

                $this->session->remitter_id = $remitterdetail['id'];
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


		$formdata = $this->_request->getPost();
		//$messages = $this->_helper->flashMessenger->getMessages();
		if(count($formdata) == 0) {
			unset($this->session->enableTransfer);
			unset($this->session->fundtransfer_auth);
			unset($this->session->fundtransfer_amount);
			unset($this->session->beneficiary_auth);
			unset($this->session->local_beneficiaryid);
			unset($this->session->beneficiaryid);
			unset($this->session->enableTransfer);
		}
		
        $this->title = 'Add Beneficiary Basic Details';
        $user = Zend_Auth::getInstance()->getIdentity();
        $form = new Remit_Ratnakar_AddBeneficiaryDetailsForm();
        $beneficiary = new Remit_Ratnakar_Beneficiary();
        $remitters = new Remit_Ratnakar_Remitter();
        $status = '';
        $smsMsg = '';
        
        $remitter_id = $this->session->remitter_id;
        $btnAuth = isset($formdata['send_auth_code']) ? $formdata['send_auth_code'] : '0';
        $btnAdd = isset($formdata['submit_form']) ? true : false;
        $is_submit = isset($formdata['is_submit'])?$formdata['is_submit']:'';
        $remitterdetail = $remitters->getRemitterById($remitter_id);
        $m = new App\Messaging\Remit\Ratnakar\Agent();
        //echo $this->session->beneficiary_auth;
        $beneCount = $remitters->getRemitterbeneficiariesCount($remitter_id);
        if($beneCount  >=  RATNAKAR_MAX_BENFICIARY_COUNT){
            $this->_helper->FlashMessenger(array('msg-error' => "Remitter has reached the maximum no. of beneficiaries allowed",)
                                    );
            $this->_redirect($this->formatURL("/remit_ratnakar_beneficiary/searchremitter?flgSess=1"));
                                 
        }
			if (isset($this->session->rblSessionID) && !isset($this->session->enableTransfer) && isset($this->session->beneficiaryid) && isset($this->session->local_beneficiaryid)) {
				
				if( isset($formdata['auth_code']) && !empty($formdata['auth_code'])){
			    	$optResponse = $this->validateOpt(array('header' => array('sessiontoken' => $this->session->rblSessionID), 'beneficiaryid' => $this->session->beneficiaryid,'verficationcode' => $formdata['auth_code']));
				   if(isset($optResponse['status']) && $optResponse['status']==1) {
try {
				   //$beneficiary->updateBeneficiaryDetails(array('rat_status' => 1),$this->session->local_beneficiaryid);
                                   $beneficiary->updateBeneficiaryDetails(array('rat_status' => 1,'status'=> STATUS_ACTIVE),$this->session->local_beneficiaryid);                                   
                                   
				   } catch(Exception $e) { echo $e->getMessage(); }
					 $userArr = array(
                                        'mobile1' => $remitterdetail['mobile'],
                                        'status' => FLAG_SUCCESS,
                                        'nick_name' => $formdata['name'],
                                        'product_name' => RATNAKAR_REMITTANCE
                                    );
                                    //$m->beneficiaryEnrollment($userArr);
   				    //$session->bid = $this->session->beneficiaryid;
				    //$session->aid = $user->id;
$this->session->bid = $this->session->local_beneficiaryid;
$this->session->aid = $user->id;
									$this->session->enableTransfer = true;
									unset($this->session->local_beneficiaryid);
									unset($this->session->beneficiaryid);
                                    $this->_helper->FlashMessenger(
                                            array(
                                                'msg-success' => "Beneficiary details have been successfully added",
                                            )
                                    );				
				$formdata = array();					

                         //$this->_redirect($this->formatURL("/remit_ratnakar_beneficiary/searchremitter?flgSess=1"));
			   
			   
			   } else {
					$this->_helper->FlashMessenger(array('msg-error' => "Invalid Authorization Code ",)
                                    );
		             }
			   	} else {
				$this->beneficiaryOtpResend(array('header' => array('sessiontoken' => $this->session->rblSessionID),'remitterid' => $remitterdetail->remitterid, 'beneficiaryid' => $this->session->beneficiaryid)); 
			 	$this->_helper->FlashMessenger( array('msg-success' => 'Authorization code has been re-sent on remitter mobile number.'));

			   }
			}
      /*  if ($btnAuth == 1) {
                   

            try {
               

                $userData = array('mobile1' => $this->session->remitter_mobile_number,
                    'name' => $formdata['name'],
                    'bank_name' => $formdata['bank_name'],
                    'ifsc_code' => trim($formdata['ifsc_code']),
                    'bank_account_number' => $formdata['bank_account_number'],
                    'product_name' => RATNAKAR_REMITTANCE,
                    'remitter_id' => $remitter_id
                );
             

                if (isset($this->session->beneficiary_auth))
                    $resp = $m->addRatnakarBeneficiaryAuth($userData, $resend = TRUE);
                else
                    $resp = $m->addRatnakarBeneficiaryAuth($userData);
					
                $formdata['send_auth_code'] = 0;
                $formdata['ifsc'] = $formdata['ifsc_code'];
                $this->_helper->FlashMessenger(array('msg-success' => 'Authorization code has been sent on your mobile number.',));
                $form->populate($formdata);
            } catch (Exception $e) {
                $formdata['ifsc'] = trim($formdata['ifsc_code']);
                $errorExists = true;
                $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                $form->populate($formdata);
                App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            }
        }*/
		if (!isset($this->session->enableTransfer) && !isset($this->session->local_beneficiaryid) && !isset($this->session->local_beneficiaryid)) {

            if ($this->getRequest()->isPost()) {
			
                if ($form->isValid($this->getRequest()->getPost())) {


                    $data = array();
                    $data['name'] = $formdata['name'];
                    $data['nick_name'] = $formdata['name'];
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
                    $data['status'] = STATUS_INACTIVE;
                    
                    $form->getElement("ifsc")->setValue(trim($formdata['ifsc_code']));
                    try {
                      //  if ($formdata['auth_code'] == $this->session->beneficiary_auth) {
                      /*      if ($formdata['name'] == $this->session->nick_name &&
                                    $formdata['bank_account_number'] == $this->session->bank_account_number &&
                                    $formdata['bank_name'] == $this->session->bank_name &&
                                     trim($formdata['ifsc_code']) == trim($this->session->ifsc_code))
                                {*/
									


                                $benId = $beneficiary->checkbeneficiary($data);
                                if($benId==0){
                                	$beneficiary->addbeneficiary($data);
                                	$res = $beneficiary->getAdapter()->lastInsertId();
                                }else{
                                	$res = $benId;
                                }

                                if ($res > 0) {
                                    $beneCode = Util::getBeneCodeFromId($res);
                                    $beneficiary->updateBeneficiaryDetails(array('bene_code'=> $beneCode),$res);




							$dataToAPI = array('header' => array('sessiontoken' => $this->session->rblSessionID),
										//'bcagent' =>'TRA1000189',
										'bcagent' =>$user->bcagent,
										'remitterid' =>$remitterdetail->remitterid,
										'beneficiaryname' => $data['name'],
										'beneficiarymobilenumber' => '',
										'beneficiaryemailid' => '',
										'relationshipid' => 2,
										'ifscode' => $data['ifsc_code'],
										'accountnumber' => $formdata['bank_account_number'],
										'mmid' => '',
										'flag' => 2);
								
								$apiResponse = $this->addBEneficiaryToRbl($dataToAPI);
								if(!$apiResponse['status']) {
					 $this->_helper->FlashMessenger( array('msg-error' => $apiResponse['description']) );

								} else {
									$data1 = array();
									$data1['beneficiary_id'] = $apiResponse['beneficiaryid'];
									$this->session->beneficiaryid = $data1['beneficiary_id'];
									$this->session->local_beneficiaryid = $res;
									$res = $beneficiary->updateBeneficiaryDetails($data1,$res);
									$this->_helper->FlashMessenger( array('msg-success' => 'Authorization code has been sent on remitter mobile number.') );
								}
                               
                                } 
                           /* } else {
                                $this->_helper->FlashMessenger(array('msg-error' => 'Please check your SMS for correct beneficiary details',));
                            }*/
                  /*      } else {
                            $this->_helper->FlashMessenger(array('msg-error' => 'Authorization code entered is not correct',));
                        }*/
                    } catch (Exception $e) {

                        $errMsg = $e->getMessage();
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    }




                    $formdata['send_auth_code'] = 0;
                    $formdata['ifsc'] = trim($formdata['ifsc_code']);
                    $form->populate($formdata);
                }
            }
        }
        $form->populate($formdata);
        $this->view->form = $form;
        $this->view->remitter_name = $remitterdetail['name'];
    }
	
	protected function addBEneficiaryToRbl($data) {
		$rblApiObject = new App_Rbl_Api();
		return $rblApiObject->beneficiaryRegistration($data);
	}

    public function completeAction() {

        $this->title = 'Beneficiary Basic Details Complete';

        $this->view->msg = 'Add Another beneficiary';
    }
	
	protected function beneficiaryOtpResend($data) {
		$rblApiObject = new App_Rbl_Api();
		return $rblApiObject->beneficiaryResendOtp($data);

	}
	
	protected function validateOpt($data) {
		$rblApiObject = new App_Rbl_Api();
		return $rblApiObject->beneficiaryValidation($data);
	}

   public function transferfundAction() {
        
        $this->title = 'Transfer Funds';
        $user = Zend_Auth::getInstance()->getIdentity();    
        $form = new Remit_Ratnakar_FundTransferForm();
        $formdata = $this->_request->getPost();
        $bank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $beneficiary = new Remit_Ratnakar_Beneficiary();
        $remittancerequest = new Remit_Ratnakar_Remittancerequest();
        $remittancestatuslog = new Remit_Ratnakar_Remittancestatuslog();
        $beneId = ($this->_getParam('id')> 0) ? $this->_getParam('id') : 0;
        $m = new App\Messaging\Remit\Ratnakar\Agent();
        if(!$beneId) {
            $this->_redirect($this->formatURL("/remit_ratnakar_beneficiary/searchremitter?flgSess=1"));  
        }
        $detail = $beneficiary->getBeneficiaryDetails($beneId);
        $feeplan = new FeePlan();
        $feeArr = $feeplan->getRemitterFee($detail['product_id'], $user->id);

            if ($this->getRequest()->isPost()) {
                if ($form->isValid($this->getRequest()->getPost())) {
                    
                    try {

                        $fee = '0.00';
                        // Find the fee plan item details for Typecode = TXNTYPE_FUND_TRANSFER_FEE 
                        foreach($feeArr as $val){
                            if($val['typecode'] == TXNTYPE_REMITTANCE_FEE){
                                // Get Remitter Fee
                                $val['amount'] = $formdata['amount'];
                                $val['return_type'] = TYPE_FEE;
                                $fee = Util::calculateFee($val); 
                                break;
                            }
                        }
                        // Calculate fee components
                        $feeComponent = Util::getFeeComponents($fee);
                        
                        $params = array('agent_id' =>$user->id,
                               'product_id' =>$detail['product_id'],
                               'remitter_id' =>$this->session->remitter_id,
                               'amount' =>$formdata['amount'],
                               'fee_amt' =>$feeComponent['partialFee'],
                               'service_tax' =>$feeComponent['serviceTax'],
                               'bank_unicode' => $bank->bank->unicode,
                               );
                        //Fund transfer limit on the basis of Agent limit and product limit
                        if ($remittancerequest->chkAllowRemit($params)){

                            $this->session->fundtransfer_amount = $formdata['amount'];
                            //If fee is assigned for the product assigned to the Agent for the day
                            if(empty($feeArr)){
                                $this->_helper->FlashMessenger( array('msg-error' => 'Product not assigned to agent for the day',) );
                            }else{
                             $this->_redirect($this->formatURL("/remit_ratnakar_beneficiary/confirmtransferfund?sender_msg=".$formdata['sender_msg'].'&beneID='.$beneId));    
                            }
                        }
                    } catch (Exception $e) {

                            $errMsg = $e->getMessage();
                            $this->_helper->FlashMessenger( array('msg-error' => $errMsg) );  
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);

                    }
                    $form->populate($formdata);
                }
            }
            
     
        $this->view->showBackLink = TRUE;
        $this->view->form = $form;
        $this->view->detail = $detail;
    }


    public function deactivatebeneficiaryAction() {
        $this->title = 'Deactivate Beneficiary';
        $id = ($this->_getParam('id') > 0) ? $this->_getParam('id') : 0;
        $beneficiary = new Remit_Ratnakar_Beneficiary;
        $beneArr = $beneficiary->getBeneficiaryDetails($id);
	    $formData = $this->_request->getPost();	
	  	
		
		
		$delete = TRUE;
		if($beneArr->beneficiary_id && count($formData) == 0) {
			$delete = FALSE;
			$rblApiObject = new App_Rbl_Api();
			$remitters = new Remit_Ratnakar_Remitter();
			$remitterData = $remitters->getRemitterById($beneArr->remitter_id);
			$res = $rblApiObject->deleteBeneficiary(array('header' => array('sessiontoken'=> $this->session->rblSessionID),
						'remitterid' => $remitterData->remitterid,
						'beneficairyid' => $beneArr->beneficiary_id));
						
			if($res['status']) {
				$form = new Remit_Ratnakar_DeactivateBeneficiaryForm();
				$this->_helper->FlashMessenger(array('msg-success' => 'Please check the authorization code on your mobile'));
				$this->view->form = $form;
			} else {
				$this->_helper->FlashMessenger(array('msg-error' => 'Beneficiary could not be deactivated'));
			}
		}
		
		if(count($formData) > 0){
			$rblApiObject = new App_Rbl_Api();
			$res = $rblApiObject->deleteBeneficiaryValidation(array('header' => array('sessiontoken'=> $this->session->rblSessionID),
						'beneficairyid' => $beneArr->beneficiary_id,'verficationcode' => $formData['auth_code']));
			if($res['status']) {
				$delete = TRUE;
			}  else {
				$this->_helper->FlashMessenger(array('msg-error' => $res['description']));
			}
		}
		
		if($delete){	
			$this->view->bene_name = $beneArr['name'];
			$changeLog = new LogStatus();
			$user = Zend_Auth::getInstance()->getIdentity();
			$data = array('status' => STATUS_INACTIVE);
			$logData = array(
				'ratnakar_beneficiary_id' => $id,
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
			$this->_redirect($this->formatURL("/remit_ratnakar_beneficiary/searchremitter?flgSess=1"));
		}
    }

    /*
     * failurelist displays neft failed transactions
     */

    public function failuretxnAction() {
        $this->title = 'Ratnakar Remittance Failed Transactions';
        $user = Zend_Auth::getInstance()->getIdentity();
        $agentId = $user->id;
        $session = new Zend_Session_Namespace('App.Agent.Controller');
        $objRemitterModel = new Remit_Ratnakar_Remitter();
        $remittancerequest = new Remit_Ratnakar_Remittancerequest();
        $m = new App\Messaging\Remit\Ratnakar\Agent();
        $bank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $remitterId = isset($session->remitter_id) ? $session->remitter_id : 0;

        if ($remitterId == 0) {
            $this->_redirect($this->formatURL("/remit_ratnakar_beneficiary/searchremitter?flgSess=1"));
        }

        $remitterDetail = $objRemitterModel->findById($remitterId);

        $remitRequestId = $this->_getParam('rrid');
        if (isset($remitRequestId) && $remitRequestId > 0) {
            $remitRequestDetail = $remittancerequest->getAgentRemittanceRequests(FLAG_FAILURE, 0, $remitRequestId);
            $productId = $remitRequestDetail[0]['product_id'];

            $refundAmt = $remitRequestDetail[0]['amount'] + $remitRequestDetail[0]['fee'] + $remitRequestDetail[0]['service_tax'];
            $reversalFee = $remitRequestDetail[0]['fee'];
            $reversalSt = $remitRequestDetail[0]['service_tax'];


            $limitObj = new BaseTxn();

            try {
                    $session->refundable_amount = $refundAmt;
                    $session->remittance_request_id = $remitRequestId;			
					$session->refund_auth_code = 'GENERATED';		

					$rblApiObject = new App_Rbl_Api();
					$res = $rblApiObject->refundOtp(array('header' => array('sessiontoken'=> $this->session->rblSessionID),
							'RBLtransactionid' => $remitRequestDetail[0]['rbl_transaction_id']));
				
				$rblTransactionIdNotExist = false;
				
				if(!isset($remitRequestDetail[0]['rbl_transaction_id']) || 
						 (isset($remitRequestDetail[0]['rbl_transaction_id']) && (strlen($remitRequestDetail[0]['rbl_transaction_id']) == 0
					))){
					//if no txn from rbl earlier which means
					$rblTransactionIdNotExist = true;
				}
				if($rblTransactionIdNotExist){
					App_Logger::log("REFUND : rblTransactionIdNotExist , agentId :". $user->id, Zend_Log::INFO);

					$calculatedFee = 0;
					$serviceTax = 0;
					$reversalRemitFee = $remitRequestDetail[0]['fee'];
					$reversalRemitSt = $remitRequestDetail[0]['service_tax'];
						
					//mark txn as refund
					$this->doRefund($remitRequestId,$remitterId,$agentId,$productId,$calculatedFee,$serviceTax,$reversalRemitFee,$reversalRemitSt,
							$bank,$session,$remittancerequest,$remitRequestDetail);
					$this->_helper->FlashMessenger( array('msg-success' => 'This transaction failed due to internal error, please go ahead and refund. The transaction has been marked as refunded.',) );
					$this->_redirect($this->formatURL("/remit_ratnakar_beneficiary/searchremitter?flgSess=1"));
				}else{
					$this->_helper->FlashMessenger(array('msg-success' => 'Please check the authorization code on your mobile',));
					$this->_redirect($this->formatURL("/remit_ratnakar_beneficiary/refund?rrid=".$remitRequestId));
				}
                /*               } */
            } catch (Exception $e) {
                $this->_helper->FlashMessenger(array('msg-error' => $e->getMessage(),));
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
            }
        }

        $txnDetail = $remittancerequest->getAgentRemittanceRequests(FLAG_FAILURE, $remitterId);
        $cntTxn = count($txnDetail);
        for ($i = 0; $i < $cntTxn; $i++) {
            $txnDetail[$i]['refundable_amount'] = $txnDetail[$i]['amount'] + $txnDetail[$i]['fee'] + $txnDetail[$i]['service_tax'];
        }
        $countTotalRefunds = $remittancerequest->getRemitterRefundCount($remitterId);
        $this->view->remitterDetail = $remitterDetail;
        $this->view->paginator = $txnDetail;
        $this->view->countTotalRefunds = $countTotalRefunds;
    }

    /* refundAction will refund amount to remitter if any refundable
     */
  public function refundAction()
    {  
        $this->title = $this->view->pageTitle = 'Refund Remitter';
        $m = new App\Messaging\Remit\Ratnakar\Agent();
        $user = Zend_Auth::getInstance()->getIdentity();        
        $agentId = $user->id;
        $session = new Zend_Session_Namespace('App.Agent.Controller'); 
		$session->refund_auth_code;
         $bank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
//        $remitRequestId = isset($session->remittance_request_id) ? $session->remittance_request_id : 0;
        $remitRequestId = $this->_getParam('rrid');

        $this->view->remitRequestId = $remitRequestId;
        if(!$remitRequestId) {
            $this->_helper->FlashMessenger( array('msg-error' => 'Refund could not be processed at the time',) );  
            $this->_redirect($this->formatURL("/remit_ratnakar_beneficiary/searchremitter?flgSess=1"));  
        }
        //$remitterId = isset($session->remitter_id)?$session->remitter_id:0;
	$remitRequestId = $this->_getParam('rrid');
        
        $objRemitterModel = new Remit_Ratnakar_Remitter();
        $remittancerequest = new Remit_Ratnakar_Remittancerequest();
        
        
    		App_Logger::log("REFUND : Doing refund for agentId :". $user->id, Zend_Log::INFO);
 
        
        $remitRequestDetail = $remittancerequest->getAgentRemittanceRequests(FLAG_FAILURE, 0, $remitRequestId);
        $remitterId = $remitRequestDetail[0]['remitter_id'];
        $remitterDetail = $objRemitterModel->findById($remitterId);
	
		$productId = $remitRequestDetail[0]['product_id'];
        $reversalRemitFee = $remitRequestDetail[0]['fee'];
        $reversalRemitSt = $remitRequestDetail[0]['service_tax'];
	 $originalAgentId= $remitRequestDetail[0]['agent_id'];
 		
        $refundableAmount = $remitRequestDetail[0]['amount'] + $remitRequestDetail[0]['fee'] + $remitRequestDetail[0]['service_tax'];
        App_Logger::log("REFUND : refundable amount :" .$refundableAmount .", agentId :". $user->id, Zend_Log::INFO);
        App_Logger::log("REFUND : remitRequestId :" .$remitRequestId .", agentId :". $user->id, Zend_Log::INFO);

		
		
        $refundableAmount = isset($session->refundable_amount)?$session->refundable_amount:0;
//        $refundFee = isset($session->refund_fee)?$session->refund_fee:0;
        
        $this->view->remitterDetail = $remitterDetail;
        $this->view->remitRequestDetail = $remitRequestDetail;
        $this->view->refundable_amount = $refundableAmount;
//        $this->view->refund_fee = $refundFee;
        
        // Get our form and validate it
        $form = new Remit_Ratnakar_RefundRemitterForm(array(
                                                'action' => $this->formatURL('/remit_ratnakar_beneficiary/refund?rrid='.$remitRequestId),
                                                'method' => 'post',
                                                'name'=>'frmRefund',
                                                'id'=>'frmRefund'
                                        ));  
       
        $this->view->form = $form;         
        $formData  = $this->_request->getPost();
        $authCode = isset($formData['auth_code'])?$formData['auth_code']:'';   
        $btnRefund = isset($formData['is_submit'])?$formData['is_submit']:false;
        $refundAuthCode = isset($session->refund_auth_code)?$session->refund_auth_code:0;
        
        // adding details in db
        if($btnRefund)
        {
            if($form->isValid($this->getRequest()->getPost()))
            {
            	$txnAgentObj = new TxnAgent();
                
                $refundRequest = array('header' => array('sessiontoken'=> $this->session->rblSessionID),
				'bcagent' => $txnAgentObj->getBCAgentForAgentId($originalAgentId),
				'channelpartnerrefno' => $remitRequestDetail[0]['txn_code'],
				'verficationcode' => $authCode,
				'flag'=>1
				);
                        App_Logger::log("REFUND REQUEST: RId:". $remitRequestId, Zend_Log::INFO);
                        App_Logger::log($refundRequest, Zend_Log::INFO);
                
				$refundRespose = $this->refundOptValid($refundRequest);
                                
                                App_Logger::log("REFUND RESPONSE: RId:". $remitRequestId. '- status: '.$refundRespose['status'].' - desc: '.$refundRespose['description'], Zend_Log::INFO);
				
				$refundStatus = (bool)$refundRespose['status'];
				App_Logger::log("REFUND : $refundStatus :" .$refundStatus .", agentId :". $user->id, Zend_Log::INFO);

		if($refundStatus)
                {
                    $objBaseTxn = new BaseTxn();
                    $objRemitStatusLog = new Remit_Ratnakar_Remittancestatuslog();

                    $calculatedFee = 0;
                    $serviceTax = 0;
                  
                    App_Logger::log("REFUND : remit_request_id :". $remitRequestId, Zend_Log::INFO);
                    App_Logger::log("REFUND : remitter_id :". $remitterId, Zend_Log::INFO);
                    App_Logger::log("REFUND : agent_id :". $agentId, Zend_Log::INFO);
                    App_Logger::log("REFUND : product_id :". $productId, Zend_Log::INFO);
                    App_Logger::log("REFUND : amount :". $refundableAmount, Zend_Log::INFO);
  
                    $remitRefundParams = array(
                                                'remit_request_id'=>$remitRequestId,
                                                'remitter_id'=>$remitterId,
                                                'agent_id'=>$agentId,
                                                'product_id'=>$productId,
                                                'amount'=>$refundableAmount,
                                                'fee_amt'=>$calculatedFee,
                                                'service_tax'=>$serviceTax,
                                                'reversal_fee_amt'=>$reversalRemitFee,
                                                'reversal_service_tax'=>$reversalRemitSt,
                                                'bank_unicode' => $bank->bank->unicode,
                                                'original_agent_id'=>$originalAgentId,                     
                                            );

                    /************ doing txn here ***********/
                    try{
                    	App_Logger::log("REFUND : Doing txn in DB, agentId :". $user->id, Zend_Log::INFO);

                        $txnCode = $objBaseTxn->remitRefund($remitRefundParams); //true;
                    	App_Logger::log("REFUND : Done with txn in DB, agentId :". $user->id, Zend_Log::INFO);

                    }catch (Exception $e ) {  
                        $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                        //$form->populate($formdata);
                    }
                    /************ doing txn here over ***********/

                    if($txnCode)
                    {

                        try
                        {   
                            // updating to remittance request table
                             $refundData = array('is_complete'=>FLAG_YES,
                                 'status'=>STATUS_REFUND,
                                 'fund_holder'=>REMIT_FUND_HOLDER_REMITTER
                              ); 

                            $res = $remittancerequest->updateReq($remitRequestId, $refundData); 

                            /************ adding to remittance refund table *************/
                            $refundAmt = $session->refundable_amount - $reversalRemitFee - $reversalRemitSt;
                            $remitRefundData = array(
                                                       'remitter_id'=>$remitterId,
                                                       'remittance_request_id'=>$remitRequestId,
                                                       'agent_id'=>$agentId,
                                                       'product_id'=>$productId,
                                                       'amount'=>$refundAmt,
                                                       'fee'=>$calculatedFee,
                                                       'service_tax'=>$serviceTax,
                                                       'reversal_fee'=>$reversalRemitFee,
                                                       'reversal_service_tax'=>$reversalRemitSt,
                                                       'txn_code' => $txnCode,
                                                       'channel'=>CHANNEL_AGENT,
                                                       'status'=>STATUS_SUCCESS,
                                                       'date_created'=>date('Y-m-d H:i:s')
                                                    );

                            $res = $remittancerequest->addRemittanceRefund($remitRefundData); 
                            /************ adding to remittance refund table over here *************/
                            $smsArr = array('amount'=>$session->refundable_amount,
                                'nick_name' => $remitRequestDetail[0]['nick_name'],
                                'remitter_phone' => $remitterDetail['mobile']);
                            //$m->refundSmsRemitter($smsArr);

                            /************ updating to remittance status log table ********/
                            $logData = array(
                                               'remittance_request_id'=>$remitRequestId,
                                               'status_old'=>FLAG_FAILURE,
                                               'status_new'=>STATUS_REFUND,
                                               'by_remitter_id'=>$remitterId,
                                               'by_agent_id'=>$agentId,
                                               'date_created'=>date('Y-m-d H:i:s')
                                            );

                            $objRemitStatusLog->addStatus($logData);
                            /************ updating to remittance status log table over ********/

                            $this->_helper->FlashMessenger( array('msg-success' => 'Amount has been refunded successfully',) );
                            
                            unset($session->remittance_request_id);
                            unset($session->refund_auth_code);
                            unset($session->refundable_amount);
//                            unset($session->refund_fee);

                            $this->_redirect($this->formatURL("/remit_ratnakar_beneficiary/searchremitter?flgSess=1"));


                            }catch (Exception $e ) { 
                                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                                    $this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) ); 
                                    $form->populate($formData);
                                }                 
                          }   
                    }else {
                    	$this->_helper->FlashMessenger( array('msg-error' => $refundRespose['description'],) );
                    }
                }
            } //  if btnRefund ends
            

               
    }
	
	protected function refundOptValid($data) {
		$rblApiObject = new App_Rbl_Api();
		return $rblApiObject->refund($data);
		//return (bool)$response['status'];
	}
	
	protected function sendRefundOPt($data) {
		$rblApiObject = new App_Rbl_Api();
		$response = $rblApiObject->refund($data);
		return (bool)$response['status'];
	}
    
    public function confirmtransferfundAction() {
        
        $this->title = 'Confirm Transfer Funds';
        $user = Zend_Auth::getInstance()->getIdentity();    
        $form = new Remit_Ratnakar_ConfirmFundTransferForm();
        $beneficiary = new Remit_Ratnakar_Beneficiary();
        $remittancerequest = new Remit_Ratnakar_Remittancerequest();
        $remittancestatuslog = new Remit_Ratnakar_Remittancestatuslog();
        $m = new App\Messaging\Remit\Ratnakar\Agent();
        $formData = $this->_request->getPost();
        $beneId = ($this->_getParam('beneID')> 0) ? $this->_getParam('beneID') : 0;
        $btnSubmit = isset($formData['submit_form'])?$formData['submit_form']:FALSE;
        $btnCancel = isset($formData['cancel'])?$formData['cancel']:FALSE;

        $detail = $beneficiary->getBeneficiaryDetails($this->_getParam('beneID'));
        
        $feeplan = new FeePlan();
        $feeArr = $feeplan->getRemitterFee($detail['product_id'], $user->id);
        $bank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        
        $formdata['amount'] = $this->session->fundtransfer_amount ;
        $formdata['sender_msg'] = $this->_getParam('sender_msg');
        $form->populate($formdata);
        if($btnCancel){
         $this->session->fundtransfer_amount = '';
         $this->_redirect($this->formatURL("/remit_ratnakar_beneficiary/transferfund?id=".$this->_getParam('beneID')));    
                               
        }
        $fee = '0.00';
                        // Find the fee plan item details for Typecode = TXNTYPE_FUND_TRANSFER_FEE 
                        foreach($feeArr as $val){
                            if($val['typecode'] == TXNTYPE_REMITTANCE_FEE){
                                // Get Remitter Fee
                                $val['amount'] = $formdata['amount'];
                                $val['return_type'] = TYPE_FEE;
                                $fee = Util::calculateFee($val); 
                                break;
                            }
                        }
            
            if ($this->getRequest()->isPost()) {
                if ($form->isValid($this->getRequest()->getPost())) {
                  
                    try {

                        
                        // Calculate fee components
                        $feeComponent = Util::getFeeComponents($fee);
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
                                $data['sender_msg'] = $formdata['sender_msg'];

                                $res = $remittancerequest->save($data);
                                $datastatus = array();
                                $datastatus['remittance_request_id'] = $res;
                                $datastatus['status_old'] = '';
                                $datastatus['status_new'] = STATUS_INCOMPLETE;
                                $datastatus['by_remitter_id'] = $this->session->remitter_id;
                                $datastatus['by_agent_id'] = $user->id;
                                $datastatus['by_ops_id'] = TXN_OPS_ID;
                                $datastatus['date_created'] = new Zend_Db_Expr('NOW()');

                                $resLog = $remittancestatuslog->addStatus($datastatus); 

                                if($res > 0 ){

                                    $paramsArr = array('agent_id' =>$user->id,
                                          'product_id' =>$detail['product_id'],
                                          'remitter_id' =>$this->session->remitter_id,
                                          'amount' =>$formdata['amount'],
                                          'remit_request_id' =>$res,
                                          'fee_amt' =>$feeComponent['partialFee'],
                                          'service_tax' =>$feeComponent['serviceTax'],
                                          'bank_unicode' => $bank->bank->unicode
                                    );       
                                    $txnCode = $remittancerequest->initiateRemit($paramsArr);
                                    if($txnCode){

                                        $updateArr = array(
                                            'status'        => STATUS_IN_PROCESS,
                                            'fund_holder'   => REMIT_FUND_HOLDER_OPS,
                                            'txn_code'      => $txnCode
                                        );

                                        $resUpdate = $remittancerequest->updateReq($res,$updateArr);
                                        $datastatus = array();
                                        $datastatus['remittance_request_id'] = $res;
                                        $datastatus['status_old'] = STATUS_INCOMPLETE;
                                        $datastatus['status_new'] = STATUS_IN_PROCESS;
                                        $datastatus['by_remitter_id'] = $this->session->remitter_id;
                                        $datastatus['by_agent_id'] = $user->id;
                                        $datastatus['by_ops_id'] = TXN_OPS_ID;
                                        $datastatus['date_created'] = new Zend_Db_Expr('NOW()');
                                        $resLog = $remittancestatuslog->addStatus($datastatus); 
                                        $smsData = array( 'beneficiary_name' => $detail['nick_name'],
                                        'amount' => $formdata['amount'],'mobile' => $this->session->remitter_mobile_number);

                                       // $m->neftInitiateRemitter($smsData);  
                                    }
                                    $this->_helper->FlashMessenger(
                                        array(
                                            'msg-success' => "Your request has been submitted, the beneficiary's account will be credited soon & you will get an sms regarding the success/failure",
                                        )
                                    );
                                    $this->_redirect($this->formatURL("/remit_ratnakar_beneficiary/searchremitter?flgSess=1"));    
                                }
                                else {
                                    $this->_helper->FlashMessenger(
                                        array(
                                            'msg-error' => 'Your request for fund transfer could not be initiated',
                                        )
                                    );
                                }
                            
                     
                    } catch (Exception $e) {

                            $errMsg = $e->getMessage();
                            $this->_helper->FlashMessenger( array('msg-error' => $errMsg) );  
                            App_Logger::log($e->getMessage(), Zend_Log::ERR);

                    }
                }
            }
            
       
        $this->view->form = $form;
        $this->view->detail = $detail;
        $this->view->remittanceAmount = $formdata['amount'];         
        $this->view->remittanceFee = $fee;   
    }
    
    private function doRefund($remitRequestId,$remitterId,$agentId,$productId,$calculatedFee,$serviceTax,$reversalRemitFee,$reversalRemitSt,
    		$bank,$session,$remittancerequest,$remitRequestDetail){
        	$refundAmt = $remitRequestDetail[0]['amount'] + $remitRequestDetail[0]['fee'] + $remitRequestDetail[0]['service_tax'];
	
    	$objBaseTxn = new BaseTxn();
    	$objRemitStatusLog = new Remit_Ratnakar_Remittancestatuslog();
    	
    	$calculatedFee = 0;
    	$serviceTax = 0;
    	
    	App_Logger::log("REFUND : Doing refund for remitRequestId: ".$remitRequestId .", agentId :". $user->id, Zend_Log::INFO);

    	$remitRefundParams = array(
    			'remit_request_id'=>$remitRequestId,
    			'remitter_id'=>$remitterId,
    			'agent_id'=>$agentId,
    			'product_id'=>$productId,
    			'amount'=>$refundAmt,
    			'fee_amt'=>$calculatedFee,
    			'service_tax'=>$serviceTax,
    			'reversal_fee_amt'=>$reversalRemitFee,
    			'reversal_service_tax'=>$reversalRemitSt,
    			'bank_unicode' => $bank->bank->unicode,
                 	'original_agent_id'=>$remitRequestDetail[0]['agent_id'],                        
);
    	
    	/************ doing txn here ***********/
    	try{
    		$txnCode = $objBaseTxn->remitRefund($remitRefundParams); //true;
    	}catch (Exception $e ) {
    		$this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
    	}
    	/************ doing txn here over ***********/
    	if($txnCode)
    	{
    		try
    		{
    			// updating to remittance request table
    			$refundData = array('is_complete'=>FLAG_YES,
    					'status'=>STATUS_REFUND,
    					'fund_holder'=>REMIT_FUND_HOLDER_REMITTER
    			);
    	
    			$res = $remittancerequest->updateReq($remitRequestId, $refundData);
    	
    			/************ adding to remittance refund table *************/
    			$refundAmt = $session->refundable_amount - $reversalRemitFee - $reversalRemitSt;
    			$remitRefundData = array(
    					'remitter_id'=>$remitterId,
    					'remittance_request_id'=>$remitRequestId,
    					'agent_id'=>$agentId,
    					'product_id'=>$productId,
    					'amount'=>$refundAmt,
    					'fee'=>$calculatedFee,
    					'service_tax'=>$serviceTax,
    					'reversal_fee'=>$reversalRemitFee,
    					'reversal_service_tax'=>$reversalRemitSt,
    					'txn_code' => $txnCode,
    					'status'=>STATUS_SUCCESS,
    					'date_created'=>date('Y-m-d H:i:s')
    			);
    	
    			$res = $remittancerequest->addRemittanceRefund($remitRefundData);
    			/************ adding to remittance refund table over here *************/
    	
    			/************ updating to remittance status log table ********/
    			$logData = array(
    					'remittance_request_id'=>$remitRequestId,
    					'status_old'=>FLAG_FAILURE,
    					'status_new'=>STATUS_REFUND,
    					'by_remitter_id'=>$remitterId,
    					'by_agent_id'=>$agentId,
    					'date_created'=>date('Y-m-d H:i:s')
    			);
    	
    			$objRemitStatusLog->addStatus($logData);
    			/************ updating to remittance status log table over ********/
    		}catch(Exception $e){
    		
    		} 
    	}
    	
    }

}

