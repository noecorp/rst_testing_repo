<?php
if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}
require_once CRON_PATH . '/cli.php';

class rejectUtr extends Remit_Ratnakar{
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    public function rejectUtrmapResponse($utr, $requestId=0){
        $user = Zend_Auth::getInstance()->getIdentity();
        $m = new App\Messaging\Remit\Ratnakar\Agent();
        // get pending records for mapping
        $mappingArr = $this->getUtrRecords($utr);
        $mappingArr = Util::toArray($mappingArr);
        $remitreqModel = new Remit_Ratnakar_Remittancerequest();  
        $objRemitStatusLog = new Remit_Ratnakar_Remittancestatuslog();
        $objBaseTxn = new BaseTxn();
        $bank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
	 $objResponseFileStatusLog = new Remit_Ratnakar_ResponseFilestatuslog();
        $i = 0;
        foreach($mappingArr as $record){
            $utrExists = $this->utrRequestExists($record['utr'],$requestId);
            if(!empty($utrExists )){
                $rrId = $utrExists['id'];
               
                $rrInfo = $remitreqModel->getRemitterRequestsInfo($utrExists['id']);
                $remitterId = $rrInfo['remitter_id'];
                
                $objRemitterModel = new Remit_Ratnakar_Remitter();
                $beneficiary = new Remit_Ratnakar_Beneficiary();
                $remitRequestArr = $remitreqModel->getRemitterRequestsInfo($utrExists['id']);
                $remitterArr = $objRemitterModel->findById($remitterId);
                $remitterName = substr($remitterArr->name, 0, 20);
                $amount = $remitRequestArr['amount'];
                $beneficiaryArr = $beneficiary->findById($remitRequestArr['beneficiary_id']);    
                $beneficiaryPhone = (isset($beneficiaryArr->mobile))?$beneficiaryArr->mobile:0;
           
                $status =  STATUS_FAILURE; 
                $statusResponse = STATUS_REJECTED;
                $statusSMS = STATUS_SUCCESS;
                
		$txnData = array('remit_request_id'=>$rrId, 
                    'beneficiary_id'=>$rrInfo['beneficiary_id'], 
                    'product_id'=>$rrInfo['product_id'], 'amount'=>$rrInfo['amount'], 'txn_code' => $rrInfo['txn_code'],'bank_unicode' => $bank->bank->unicode);
                $txnResp = $objBaseTxn->remitSuccessToFailure($txnData);
                // failure
                $txnData = array('remit_request_id'=>$rrId, 
                  'product_id'=>$rrInfo['product_id'], 'amount'=>$rrInfo['amount'],
                  'reversal_fee_amt'=>$rrInfo['fee'], 'reversal_service_tax'=>$rrInfo['service_tax'],'bank_unicode' => $bank->bank->unicode);
               
                $txnResp = $objBaseTxn->remitFailure($txnData);
                 /*Send SMS to Remiiter
                   */
                $dataArr = array('amount' => $amount, 'nick_name' => $beneficiaryArr->nick_name,'remitter_phone' => $remitterArr->mobile );
                $m->neftFailureRemitter($dataArr);
                
                $reqUpdateArr = array('status_response' => $statusResponse,
                    'status_response_by_ops_id'=> TXN_OPS_ID,
                    'date_status_response' => new Zend_Db_Expr('NOW()'),
                    'fund_holder' => USER_TYPE_OPS,
                    'neft_remarks' => 'Manual Success to Failure',
                    'status_sms'=> $statusSMS,
                    'status' => $status
                    );
                $remitreqModel->updateReqByUTR($record['utr'],$reqUpdateArr);
                $remitReqLog = array('remittance_request_id' => $utrExists['id'],
                    'status_old' => $utrExists['status'], 'status_new' => $status,
                    'by_ops_id' => TXN_OPS_ID, 'date_created' => new Zend_Db_Expr('NOW()'));
                $objRemitStatusLog->addStatus($remitReqLog);

		 $responseLog = array('response_file_id' => $record['id'],
                    'status_old' => STATUS_PROCESS, 'status_new' => $status,
                    'rejection_code' =>  $record['rejection_code'],
                    'rejection_remark' =>  $record['rejection_remark'],
                    'description' => 'Manual success to failure',
                    'by_ops_id' => TXN_OPS_ID, 'date_created' => new Zend_Db_Expr('NOW()'));
                $objResponseFileStatusLog->addStatus($responseLog);
                // update payment history table
                $updateArr = array('status_response' => STATUS_MAPPED,
                    'date_updated' => new Zend_Db_Expr('NOW()'));
                $this->updateResponse($updateArr, $record['id']);
                $i++;
            }
        }
       return $i; 
    }
    
    public function getUtrRecords($utr){
        
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_RESPONSE_FILE,array('id','status','utr'))
                ->where("utr = '" . $utr . "'");
        //echo $select; exit;
        return $this->_db->fetchAll($select);
        
        
    }
     public function utrRequestExists($utr,$requestId){
        
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST,array('id','utr','txn_code','status'))
                ->where("utr =?",$utr);
        if($requestId > 0){        
                $select->where("id =?",$requestId);
        }        

        $res =  $this->_db->fetchRow($select);
        
         if (!empty($res)) {
            return $res;
        } else {
            return FALSE;
        }
        
    }
    
    public function updateResponse($params,$id){
        //$this->update($params,);
        $this->_db->update(DbTable::TABLE_RATNAKAR_RESPONSE_FILE, $params, "id='$id'");
        return true;
        
    }
    
}



$utr = 'RATNN14238005210';
$requestID = '5232';
if(!empty($utr)){
    $model = new rejectUtr();
    $respCnt = $model->rejectUtrmapResponse($utr,$requestID);
}

