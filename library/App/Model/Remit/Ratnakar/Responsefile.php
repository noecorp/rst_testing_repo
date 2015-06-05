<?php

/*
 * Ratnakar Remitter Model
 */

class Remit_Ratnakar_Responsefile extends Remit_Ratnakar {

    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'id';

    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_RATNAKAR_RESPONSE_FILE;

    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    public function mapResponse(){
        $user = Zend_Auth::getInstance()->getIdentity();
        $m = new App\Messaging\Remit\Ratnakar\Agent();
        // get pending records for mapping
        $mappingArr = $this->getPendingRecords();
        $mappingArr = Util::toArray($mappingArr);
        $remitreqModel = new Remit_Ratnakar_Remittancerequest();  
        $objRemitStatusLog = new Remit_Ratnakar_Remittancestatuslog();
        $objResponseFileStatusLog = new Remit_Ratnakar_ResponseFilestatuslog();
        $custModel = new Corp_Ratnakar_Cardholders();
        $objBaseTxn = new BaseTxn();
        $productModel = new Products();
        $bank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $i = 0;
        foreach($mappingArr as $record){
            $utrExists = $this->utrExists($record['utr']);
            
            if(!empty($utrExists )){
                $resp_status = STATUS_MAPPED;
                $rrId = $utrExists['id'];
             
                $rrInfo = $remitreqModel->getRemitterRequestsInfo($utrExists['id']);
                
                $remitterId = $rrInfo['remitter_id'];
                
                $productdetails = $productModel->getProductInfo($rrInfo['product_id']);
                
                $objRemitterModel = new Remit_Ratnakar_Remitter();
                $beneficiary = new Remit_Ratnakar_Beneficiary();
                $masterPurseDetails = new MasterPurse();
                $remitRequestArr = $remitreqModel->getRemitterRequestsInfo($utrExists['id']);
                $remitterArr = $objRemitterModel->findById($remitterId);
                $remitterName = substr($remitterArr->name, 0, 20);
                $amount = $remitRequestArr['amount'];
                $beneficiaryArr = $beneficiary->findById($remitRequestArr['beneficiary_id']);    
                $beneficiaryPhone = (isset($beneficiaryArr->mobile))?$beneficiaryArr->mobile:0;
                $reqUpdateArr = array(
                    'status_response_by_ops_id'=> TXN_OPS_ID,
                    'date_status_response' => new Zend_Db_Expr('NOW()'),
                    'is_complete' => FLAG_YES
                    );
                //map the utr in request table
                if(strtolower($record['status']) == STATUS_PROCESS){
                  
                $status = STATUS_SUCCESS;
                $statusResponse = STATUS_PROCESSED;
                $statusSMS = STATUS_SUCCESS;
                
                $txnData = array('remit_request_id'=>$rrId, 
                    'beneficiary_id'=>$rrInfo['beneficiary_id'], 
                    'product_id'=>$rrInfo['product_id'], 'amount'=>$rrInfo['amount'], 'txn_code' => $rrInfo['txn_code'],'bank_unicode' => $bank->bank->unicode);
                $txnResp = $objBaseTxn->remitSuccess($txnData);
                /*Send SMS to Remiiter & to Bene
                     */
                    $dataArr = array(
                        'amount' => $amount,
                        'nick_name' =>$beneficiaryArr->nick_name,
                        'remitter_name' => $remitterName, 
                        'contact_email' => RATNAKAR_REMITTANCE_EMAIL,
                        'contact_number' => RATNAKAR_CALL_CENTRE_NUMBER,
                        'remitter_phone' => $remitterArr->mobile,
                        'beneficiary_phone' => $beneficiaryPhone,
                        'product_name' => RATNAKAR_SHMART_REMIT);
                    
                    if($productdetails['const'] != PRODUCT_CONST_RAT_SMP) {
                        $m->neftSuccessRemitter($dataArr);
                    }
                    if($beneficiaryPhone != 0){
                        $m->neftSuccessBeneficiary($dataArr);
                    }
                    $reqUpdateArr['fund_holder'] = REMIT_FUND_HOLDER_BENEFICIARY;
                    
                    if($productdetails['const'] == PRODUCT_CONST_RAT_SMP) {
                        $remitreqModel->updateReq($rrInfo['id'], array('flag_response' => FLAG_RESPONSE_ONE));
                    }
                }
                else if(strtolower($record['status']) == STATUS_REJECT){                  
                 
                //  $status = ($productdetails['const'] == PRODUCT_CONST_RAT_PAYU || $productdetails['const'] == PRODUCT_CONST_RAT_SMP)? STATUS_REFUND: STATUS_FAILURE; 
                  $statusResponse = STATUS_REJECTED;
                  $statusSMS = STATUS_FAILURE;
                  $status = $rrInfo['status'];
                  
//                  Refund
//		    if ($productdetails['const'] == PRODUCT_CONST_RAT_PAYU || $productdetails['const'] == PRODUCT_CONST_RAT_SMP) {
		    if($rrInfo['rat_customer_id'] > 0){
                        // Checking for ECS requirment
                        $requireECS = FLAG_NO;
                        $masterPurseDetails = $masterPurseDetails->getPurseDetailsbyPurseId($rrInfo['purse_master_id']);
                        $isVirtual = isset($masterPurseDetails['is_virtual'])? $masterPurseDetails['is_virtual'] : FLAG_NO;
                        
                        //******** Getting Customer Detail Including CardDetail
                        $searchArr = array(
                            'product_id'=> $rrInfo['product_id'],
                            'rat_customer_id' => $rrInfo['rat_customer_id'],
                            'customer_master_id' => $rrInfo['customer_master_id'],
                            'status' => STATUS_ACTIVE,
                        );
                        $cardholderDetails = $custModel->getCardholderInfo($searchArr);
                         $custCardNumber = ($cardholderDetails->card_number != '') ? $cardholderDetails->card_number : '';  
                         
                        $agent_id = $rrInfo['agent_id'];
                        $ecsCall = FALSE;
                         if( ($isVirtual == FLAG_NO) && ($custCardNumber!='') )
                         {
                           $requireECS = FLAG_YES;  
                         }
                         
                        //
                        
                         // ******** ECS call for card holder *********** //
                         
                         
                          
                            try{
                              
                            if( ($requireECS == FLAG_YES) && ($agent_id!='') ){
                              $ecsCall = TRUE;  
                              $ecsApi = new App_Socket_ECS_Corp_Transaction();  
                              $txncode = new Txncode();
                               if ($txncode->generateTxncode()){
                                  $txnCode = $txncode->getTxncode();  
                                }


                                $amount = $params['amount'];
                                $cardLoadData = array(
                                        'amount' => $rrInfo['amount'],
                                        'crn' => $custCardNumber,
                                        'agentId' => $agent_id,
                                        'transactionId' => $txnCode,
                                        'currencyCode' => CURRENCY_INR_CODE,
                                        'countryCode' => COUNTRY_IN_CODE
                                    );
                                if(DEBUG_MVC) {
                                        $apiResp = TRUE;
                                        $ecsCall = FALSE;
                                        } else {
                                            $ecsApi = new App_Socket_ECS_Corp_Transaction();
                                            $apiResp = $ecsApi->cardLoad($cardLoadData); // bypassing for testing
                                        }
                                 }else{
                                  $apiResp = TRUE; 
                                  $ecsCall = FALSE;
                                 }
                                 
                                if ($apiResp === TRUE) {
                                    $txn_load_id = $ecsCall == TRUE ? $ecsApi->getISOTxnId() : '';
                                    $reqUpdateArr['txn_load_id'] = $txn_load_id;
                                    $txnData = array('remit_request_id' => $rrId,
                                    'product_id' => $rrInfo['product_id'], 'amount' => $rrInfo['amount'],
                                    'reversal_fee_amt' => $rrInfo['fee'], 'reversal_service_tax' => $rrInfo['service_tax'], 'bank_unicode' => $bank->bank->unicode,
                                    'rat_customer_id' => $rrInfo['rat_customer_id'], 'purse_master_id' => $rrInfo['purse_master_id'],
                                    'customer_master_id' => $rrInfo['customer_master_id'],
                                    'customer_purse_id' => $rrInfo['customer_purse_id']);

                                    $txnResp = $objBaseTxn->remitFailureAPI($txnData);

                                    //                   Insert into rat_refund
                                    $refundArr = array(
                                    'bank_id' => $rrInfo['bank_id'],    
                                    'remitter_id' => $rrInfo['remitter_id'],
                                    'remittance_request_id' => $rrInfo['id'],
                                    'rat_customer_id' => $rrInfo['rat_customer_id'],
                                    'purse_master_id' => $rrInfo['purse_master_id'],
                                    'customer_purse_id' => $rrInfo['customer_purse_id'],
                                    'agent_id' => $rrInfo['agent_id'],
                                    'product_id' => $rrInfo['product_id'],
                                    'amount' => $rrInfo['amount'],
                                    'fee' => $rrInfo['fee'],
                                    'service_tax' => $rrInfo['service_tax'],
                                    'reversal_fee' => 0,
                                    'reversal_service_tax' => 0,
                                    'txn_code' => $txnResp,
                                    'status' => STATUS_SUCCESS,
                                    'channel' => CHANNEL_SYSTEM
                                    );
                                    $remitreqModel->addRemittanceRefund($refundArr);
                                    $status = STATUS_REFUND ;
                                }else{
                                     $resp_status = STATUS_PENDING;
                                }              

                                //
                             }catch (Exception $e ) { 
                                App_Logger::log(serialize($e) , Zend_Log::ERR);
                                }   
                         
                         
                         
                        // End ECS  
                        
                        
                    } else{// failure
                      $txnData = array('remit_request_id'=>$rrId, 
                    'product_id'=> $rrInfo['product_id'], 'amount'=>$rrInfo['amount'],
                    'reversal_fee_amt'=>$rrInfo['fee'], 'reversal_service_tax'=>$rrInfo['service_tax'],'bank_unicode' => $bank->bank->unicode);
                 
                  $txnResp = $objBaseTxn->remitFailure($txnData);
                  }
                  
                   /*Send SMS to Remiiter
                     */
                   /*$dataArr = array('amount' => $amount, 'nick_name' => $beneficiaryArr->nick_name,'remitter_phone' => $remitterArr->mobile );
                   $m->neftFailureRemitter($dataArr);*/
                   
                    $reqUpdateArr['fund_holder'] = REMIT_FUND_HOLDER_REMITTER;
                    
                }
                    $reqUpdateArr['status_response'] = $statusResponse;
                    $reqUpdateArr['status_sms'] = $statusSMS;
                    $reqUpdateArr['status'] = $status;
                 
                $remitreqModel->updateReqByUTR($record['utr'],$reqUpdateArr);
                $remitReqLog = array('remittance_request_id' => $utrExists['id'],
                    'status_old' => $utrExists['status'], 'status_new' => $status,
                    'by_ops_id' => TXN_OPS_ID, 'date_created' => new Zend_Db_Expr('NOW()'));
                $objRemitStatusLog->addStatus($remitReqLog);
                
                $responseLog = array('response_file_id' => $record['id'],
                    'status_old' => '', 'status_new' => $status,
                    'rejection_code' =>  $record['rejection_code'],
                    'rejection_remark' =>  $record['rejection_remark'],
                    'description' => '',
                    'by_ops_id' => TXN_OPS_ID, 'date_created' => new Zend_Db_Expr('NOW()'));
                $objResponseFileStatusLog->addStatus($responseLog);
                    

               
                // update payment history table
                $updateArr = array('status_response' => $resp_status,
                    'date_updated' => new Zend_Db_Expr('NOW()'));
                $this->updateResponse($updateArr, $record['id']);
                $i++;
            }
        }
       return $i; 
    }
    
     public function getPendingRecords(){
        
        $select = $this->select()
                ->from(DbTable::TABLE_RATNAKAR_RESPONSE_FILE,array('id','status','utr','rejection_code','rejection_remark'))
                ->where("status_response = '" . STATUS_PENDING . "'");

        return $this->fetchAll($select);
        
        
    }
    
     public function utrExists($utr){
        
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST,array('id','utr','txn_code','status'))
                ->where("utr =?",$utr)
                ->where("status_utr =?",STATUS_MAPPED);

        $res =  $this->_db->fetchRow($select);
        
         if (!empty($res)) {
            return $res;
        } else {
            return FALSE;
        }
        
    }
    
    public function updateResponse($params,$id){
        $this->update($params,"id='$id'");
        return true;
        
    }
    
     public function utrExistsResponseFile($utr){
        
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_RESPONSE_FILE,array('id','utr','status'))
                ->where("utr =?",$utr);
               

        $res =  $this->_db->fetchRow($select);
        
         if (!empty($res)) {
            return $res;
        } else {
            return FALSE;
        }
        
    }
    
//    $remReqModel = Remit_Ratnakar_Remittancerequest();
//    addRemittanceRefund
//        array('remitter_id' => '',
//        'remittance_request_id' =>'', 
//         'rat_customer_id' => ,
//         'purse_master_id' => ,
//         'customer_purse_id' => ,
//         'agent_id' => '', 
//            'product_id'  => '',
//            'amount'  => '',	
//            'fee'  => '',	
//            'service_tax'  => '',
//            'reversal_fee'  => '',
//            'reversal_service_tax'  => '',
//            'txn_code'  => '',
//            'status'  => '');
          
	
}
