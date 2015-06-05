<?php

/**
 * That RemitterController is responsible for all remit operations at ops portal.
 */

class Remit_Kotak_RemitterController extends Remit_IndexController
{

    public function init()
    {
        parent::init();
    }    
    
    /* indexAction is landing page for remitter different operations       
    */
    
    public function indexAction()
    {
    }   
    
    
    public function searchAction(){
        
         $this->title = 'Search Remitter';
         $data['searchCriteria'] = $this->_getParam('searchCriteria');
         $data['keyword'] = $this->_getParam('keyword');
         $data['sub'] = $this->_getParam('sub');
         $page = $this->_getParam('page');
         $remitterModel = new Remit_Kotak_Remitter();
         $form = new Remit_Kotak_RemitterSearchForm(array('action' => $this->formatURL('/remit_kotak_remitter/search'),
                                             'method' => 'POST',
                                      ));
        
        if ($data['sub'] != '') {
            if($form->isValid($this->getRequest()->getPost())){
                $data['searchCriteria'] = $this->_getParam('searchCriteria');
                $data['keyword'] = $this->_getParam('keyword');
                 
                 $this->view->paginator = $remitterModel->searchRemitter($data,$page); 
                 
                 $this->view->sub = $data['sub'];
                 $form->populate($data);  
            
          }
        }
        
        
        $this->view->controllerName = Zend_Registry::get('controllerName');
        $this->view->form = $form;
    }

    
      public function beneficiaryAction(){
        
         $this->title = "Remitter's Beneficiaries";
         $rid = ($this->_getParam('rid') > 0) ? $this->_getParam('rid'): 0;
         if($rid>0){
            $remitterModel = new Remit_Kotak_Remitter();
            $remitterInfo = $remitterModel->getRemitterById($rid);
            $this->view->paginator = $remitterModel->getRemitterbeneficiaries($rid,$this->_getPage()); 
            $this->view->remitterInfo = $remitterInfo; 
         }
         $this->view->controllerName = Zend_Registry::get('controllerName');
    }
  

       public function holdtransactionsAction(){
         ini_set('max_execution_time', 120);
         $this->title = "Remitter's Hold Transactions";
         $data['sub'] = $this->_getParam('sub');
         $data['mobile'] = $this->_getParam('mobile');
         $this->view->sub = FALSE;
         $remitterModel = new Remit_Kotak_Remitter();
//         $form = new Remit_Kotak_RemitterHoldTransactionForm(array('action' => $this->formatURL('/remit_kotak_remitter/holdtransactions'),
//                                             'method' => 'POST',
//                                      ));
        $page = $this->_getParam('page');
        //if ($data['sub'] != '') {
//            if($form->isValid($this->getRequest()->getPost())){
                 $data['mobile'] = $this->_getParam('mobile');
                 $data['status'] = STATUS_HOLD; 
                 $ref = $remitterModel->getRemitterHoldTransactions($page); 
                 $this->view->paginator = $ref;
                    
                 $this->view->sub = $data['sub'];
                 //$form->populate(array('mobile'=> $data['mobile'],'sub'=> $data['sub']));  
            
//           }
        //}
        
        //$this->view->form = $form;
    }
    
    
    public function processtransactionAction(){
      $this->title = "Remitter's Hold Transactions"; 
      $traceNumber = $this->_getParam('txn_code');
      $newTraceNumber = Util::generateRandomNumber(10); //rand('1111111111','9999999999');
      $form = new Remit_Kotak_ProcessForm();
      $remittancerequest = new Remit_Kotak_Remittancerequest();
      $remittancestatuslog = new Remit_Kotak_Remittancestatuslog();
      $baseTxn = new BaseTxn();
      $bank = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
      $flg = FALSE;
      $backLink = 'mobile='.$this->_getParam('mobile').'&sub=1';
      $type = $this->_getParam('type');
      $user = Zend_Auth::getInstance()->getIdentity();
      $m = new App\Messaging\Remit\Kotak\Agent();
      //API call
      $api = new App_Api_Kotak_Remit_Transaction();
                try {
      if($type == STATUS_SUCCESS) {
                $remittanceDetailsArr = $remittancerequest->getRemitterRequestsInfoByTxnCode($traceNumber);
                $remittanceDetails = $remittancerequest->getRemitterRequestsInfo($remittanceDetailsArr['id']);          
                $paramsBaseTxn = array(
                    'remit_request_id' => $remittanceDetails['id'],
                    'product_id' => $remittanceDetails['product_id'],
                    'amount' => $remittanceDetails['amount'],
                    'bank_unicode' => $bank->bank->unicode,
                    'agent_id' => $remittanceDetails['agent_id']
                );                
                $smsData = array(
                    'amount' => $remittanceDetails['amount'],
                    'nick_name' => $remittanceDetails['nick_name'],
                    'beneficiary_name' => $remittanceDetails['name'],
                    'remitter_name' => $remittanceDetails['r_name'],
                    'contact_email' => KOTAK_SHMART_EMAIL,
                    'contact_number' => KOTAK_CALL_CENTRE_NUMBER,
                    'remitter_phone' => $remittanceDetails['mobile'],
                    'product_name' => KOTAK_SHMART_TRANSFER
                );
                    

                    $paramsBaseTxn['beneficiary_id'] = $remittanceDetails['beneficiary_id'];
                    $paramsBaseTxn['txn_code'] = $traceNumber;
                    $paramsBaseTxn['fee_amt'] = $remittanceDetails['fee'];
                    $paramsBaseTxn['service_tax'] = $remittanceDetails['service_tax'];
           
                    //echo '<pre>';print_r($smsData);
                    //echo '<pre>';print_r($paramsBaseTxn);exit;
                    $baseTxn->remitSuccess($paramsBaseTxn);
                    // Remit request table update Array
                    $updateStatusArr['is_complete'] = FLAG_YES;
                    $updateStatusArr['status'] = STATUS_SUCCESS;
                    $updateStatusArr['fund_holder'] = REMIT_FUND_HOLDER_BENEFICIARY;
                    $m->kotakNeftSuccessRemitter($smsData);
                    $flg = TRUE;
                    $datastatus['status_new'] = STATUS_SUCCESS;
                    
                    $datastatus['remittance_request_id'] = $remittanceDetails['id'];
                    $datastatus['status_old'] = $remittanceDetails['status'];
                    $datastatus['by_remitter_id'] = $remittanceDetails['remitter_id'];
                    $datastatus['by_ops_id'] = $user->id;
                    $datastatus['date_created'] = new Zend_Db_Expr('NOW()');
                    $resLog = $remittancestatuslog->addStatus($datastatus);
                
                
                    $updateStatusArr['cr_response'] = 'Transaction Marked as successful by Ops';
                    $updateStatusArr['final_response'] = 'Transaction Marked as successful by Ops';
                    $resUpdate = $remittancerequest->updateReq($remittanceDetails['id'],$updateStatusArr);                    
          
               $this->_helper->FlashMessenger(
                    array(
                          'msg-success' => 'Hold Transaction has been marked as successful.',
                    )
                );
               $this->_redirect($this->formatURL('/remit_kotak_remitter/holdtransactions?'.$backLink));    
               exit();
               
      } elseif($type == STATUS_FAILED) {
                $remittanceDetailsArr = $remittancerequest->getRemitterRequestsInfoByTxnCode($traceNumber);
                $remittanceDetails = $remittancerequest->getRemitterRequestsInfo($remittanceDetailsArr['id']);          
                $paramsBaseTxn = array(
                    'remit_request_id' => $remittanceDetails['id'],
                    'product_id' => $remittanceDetails['product_id'],
                    'amount' => $remittanceDetails['amount'],
                    'bank_unicode' => $bank->bank->unicode,
                    'agent_id' => $remittanceDetails['agent_id']
                );                               
                $smsData = array(
                    'amount' => $remittanceDetails['amount'],
                    'nick_name' => $remittanceDetails['nick_name'],
                    'beneficiary_name' => $remittanceDetails['name'],
                    'remitter_name' => $remittanceDetails['r_name'],
                    'contact_email' => KOTAK_SHMART_EMAIL,
                    'contact_number' => KOTAK_CALL_CENTRE_NUMBER,
                    'remitter_phone' => $remittanceDetails['mobile'],
                    'product_name' => KOTAK_SHMART_TRANSFER
                );

                    $paramsBaseTxn['reversal_fee_amt'] = $remittanceDetails['fee'];
                    $paramsBaseTxn['reversal_service_tax'] = $remittanceDetails['service_tax'];
                    $baseTxn->remitFailure($paramsBaseTxn);
                    // Remit request table update Array
                    $updateStatusArr['is_complete'] = FLAG_NO;
                    $updateStatusArr['status'] = STATUS_FAILURE;
                    $updateStatusArr['fund_holder'] = REMIT_FUND_HOLDER_REMITTER;
                    $m->kotakNeftFailureRemitter($smsData);
                    $datastatus['status_new'] = STATUS_FAILURE;          
                    $datastatus['remittance_request_id'] = $remittanceDetails['id'];
                    $datastatus['status_old'] = $remittanceDetails['status'];
                    $datastatus['by_remitter_id'] = $remittanceDetails['remitter_id'];
                    $datastatus['by_ops_id'] = $user->id;
                    $datastatus['date_created'] = new Zend_Db_Expr('NOW()');
                    $resLog = $remittancestatuslog->addStatus($datastatus);
                    $updateStatusArr['cr_response'] = 'Transaction Marked as failed by Ops';
                    $updateStatusArr['final_response'] = 'Transaction Marked as failed by Ops';
                    $resUpdate = $remittancerequest->updateReq($remittanceDetails['id'],$updateStatusArr);                    
          
                    $this->_helper->FlashMessenger(
                        array(
                              'msg-success' => 'Hold Transaction has been marked as failed.',
                        )
                    );
                    $this->_redirect($this->formatURL('/remit_kotak_remitter/holdtransactions?'.$backLink));             
        }  
                } catch(Exception $e) {
                    echo '<pre>';print_r($e);exit;
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        $this->_helper->FlashMessenger(
                        array(
                              'msg-success' => 'Unable to process hold transaction',
                        )
                    );
                    $this->_redirect($this->formatURL('/remit_kotak_remitter/holdtransactions?'.$backLink));                                 
                    //echo '<pre>';print_r($e);exit;
                }
      //} else {
            $resp = $api->queryAccountAndValidate(array(
                                   'qbTraceNumber' => $traceNumber,
                                   'traceNumber' => $newTraceNumber
                                   ));


             if($resp === TRANSACTION_SUCCESSFUL) {
             $displayMsg = "Remittance request has been successfully processed.";
                //Success
               $status = STATUS_SUCCESS;
               } else {
                 //Failure  
               $status = STATUS_FAILURE;
                  $displayMsg = 'Failure: '.$api->getMessage();
               }


             $std = new stdClass();
             $std->response = $displayMsg;
             $std->cr_response = ($api->getAccountCreditRespCode() != '')? '('.$api->getAccountCreditRespCode().') '.$api->getAccountCreditRespMsg():'-';
             $std->final_response = ($api->getMessage(TRUE) != '')? $api->getMessage(TRUE):'-';
             $this->view->item = $std;
      //}

       $remittanceDetailsArr = $remittancerequest->getRemitterRequestsInfoByTxnCode($traceNumber);
       // Populate Hidden Values
       $form->getElement('remit_request_id')->setValue($remittanceDetailsArr['id']);
       $form->getElement('status')->setValue($status);
       $form->getElement('final_response')->setValue($std->final_response);
       $form->getElement('cr_response')->setValue($std->cr_response);
       
       if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $formData  = $this->_request->getPost();
          $updateStatusArr = array();
          $datastatus = array();
          $remittanceDetails = $remittancerequest->getRemitterRequestsInfo($formData['remit_request_id']);
        //Confirm on the basis of the status
          $paramsBaseTxn = array(
                    'remit_request_id' => $remittanceDetails['id'],
                    'product_id' => $remittanceDetails['product_id'],
                    'amount' => $remittanceDetails['amount'],
                    'bank_unicode' => $bank->bank->unicode,
                    'agent_id' => $remittanceDetails['agent_id']
                );


                // SMS params
                $smsData = array(
                    'amount' => $remittanceDetails['amount'],
                    'nick_name' => $remittanceDetails['nick_name'],
                    'beneficiary_name' => $remittanceDetails['name'],
                    'remitter_name' => $remittanceDetails['r_name'],
                    'contact_email' => KOTAK_SHMART_EMAIL,
                    'contact_number' => KOTAK_CALL_CENTRE_NUMBER,
                    'remitter_phone' => $remittanceDetails['mobile'],
                    'product_name' => KOTAK_SHMART_TRANSFER
                );
               
                if ($formData['status'] == STATUS_SUCCESS) {
                    //Success
                    $paramsBaseTxn['beneficiary_id'] = $remittanceDetails['beneficiary_id'];
                    $paramsBaseTxn['txn_code'] = $traceNumber;
                    $paramsBaseTxn['fee_amt'] = $remittanceDetails['fee'];
                    $paramsBaseTxn['service_tax'] = $remittanceDetails['service_tax'];

                    $baseTxn->remitSuccess($paramsBaseTxn);
                    // Remit request table update Array
                    $updateStatusArr['is_complete'] = FLAG_YES;
                    $updateStatusArr['status'] = STATUS_SUCCESS;
                    $updateStatusArr['fund_holder'] = REMIT_FUND_HOLDER_BENEFICIARY;
                    $m->kotakNeftSuccessRemitter($smsData);
                    $flg = TRUE;
                    $datastatus['status_new'] = STATUS_SUCCESS;
         } else {

                    $paramsBaseTxn['reversal_fee_amt'] = $remittanceDetails['fee'];
                    $paramsBaseTxn['reversal_service_tax'] = $remittanceDetails['service_tax'];



                    $baseTxn->remitFailure($paramsBaseTxn);
                    // Remit request table update Array
                    $updateStatusArr['is_complete'] = FLAG_NO;
                    $updateStatusArr['status'] = STATUS_FAILURE;
                    $updateStatusArr['fund_holder'] = REMIT_FUND_HOLDER_REMITTER;
                    $m->kotakNeftFailureRemitter($smsData);
                    $datastatus['status_new'] = STATUS_FAILURE;
                }

                $datastatus['remittance_request_id'] = $remittanceDetails['id'];
                $datastatus['status_old'] = $remittanceDetails['status'];
                $datastatus['by_remitter_id'] = $remittanceDetails['remitter_id'];
                $datastatus['by_ops_id'] = $user->id;
                $datastatus['date_created'] = new Zend_Db_Expr('NOW()');
                $resLog = $remittancestatuslog->addStatus($datastatus);
                
                
                $updateStatusArr['cr_response'] = $formData['cr_response'];
                $updateStatusArr['final_response'] = $formData['final_response'];
                $resUpdate = $remittancerequest->updateReq($remittanceDetails['id'],$updateStatusArr);
               
                   $this->_helper->FlashMessenger(
                    array(
                          'msg-success' => 'Hold Transaction has been processed ',
                    )
                );
               $this->_redirect($this->formatURL('/remit_kotak_remitter/holdtransactions?'.$backLink));                       
                
            }
        }
       
       $this->view->form = $form; 
       $this->view->backlink = Zend_Controller_Front::getInstance()->getBaseUrl() . '/remit_kotak_remitter/holdtransactions?'.$backLink;
       
    }
    
    
}

