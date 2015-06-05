<?php
/*
 * Remittance Request
 */
class Remit_Remittancerequest extends Remit
{
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
    protected $_name = DbTable::TABLE_REMITTANCE_REQUEST;
    
   /*
    * Add remittance request
    */
    public function updateReq($reqId,$params){
        if($reqId<1 || empty($params))
            throw new Exception('Remittance request data missing!');
            
        $this->update($params,"id='$reqId'");
        return true;
    }
    
    /*
     * get remitters successful remittance for the product for the duration
     */
     public function getTxnRemitterProductDuration($remitterId, $productId, $startDate, $endDate){
        $select = $this->_db->select()       
                ->from(DbTable::TABLE_REMITTANCE_REQUEST, array('sum(amount) as total'))              
                ->where('remitter_id=?',$remitterId)
                ->where('product_id=?',$productId)
                ->where("DATE(date_created) BETWEEN '".$startDate."' AND '".$endDate."'")
                ->where("status = '".FLAG_SUCCESS."' OR status = '".STATUS_IN_PROCESS."' OR status = '".STATUS_PROCESSED."'")
                ->group("remitter_id");
        //echo $select->__toString();
        $row = $this->_db->fetchRow($select);      
        return $row;
    } 
    

    /* getRemitterRefund() will return the refund details of remitter
     */
     public function getRemitterRefundCount($remitterId){
         if($remitterId<1)
             throw new Exception('Remitter Id not found');
         
        $select = $this->_db->select()       
                ->from(DbTable::TABLE_REMITTANCE_REQUEST." as rr", array('count(*) as count_refund_requests'))              
                ->joinLeft(DbTable::TABLE_BENEFICIARIES." as b", "rr.beneficiary_id =b.id and b.status = '".STATUS_ACTIVE."'", array(''))
                ->where('rr.remitter_id=?',$remitterId)
                ->where("rr.status = ?", FLAG_FAILURE);
        //echo $select->__toString();
        $row = $this->_db->fetchRow($select);      
        return $row;
    } 
    
    /*
     * chks remit limits
     */
     public function chkAllowRemit($params){
        $baseTxn = new BaseTxn();
        return $baseTxn->chkAllowRemit($params);
    } 
    
    /*
     * initiates remittance txns
     */
    public function initiateRemit($params){
        $baseTxn = new BaseTxn();
        return $baseTxn->initiateRemit($params);
    }
    
    
    /*
     * addRemittanceRefund() will make entry remittance refund table
     */
    public function addRemittanceRefund($data){
       
        if(empty($data))
           throw new Exception ('Remittance Refund data not found!');
        
        
        $add = $this->_db->insert(DbTable::TABLE_REMITTANCE_REFUND,$data);
      
        return $add;
        
    }
    
    
     /* getRemitterRequestsForNEFT() will return the remitters requests for neft for cron
     */
     public function getRemitterRequestsForNEFT(){
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'".$decryptionKey."') as bank_account_number");
        $branchAddress = new Zend_Db_Expr("AES_DECRYPT(`b`.`branch_address`,'".$decryptionKey."') as branch_address");
        $mobile = new Zend_Db_Expr("AES_DECRYPT(`b`.`mobile`,'".$decryptionKey."') as mobile");
        
        $select = $this->_db->select()       
                ->from(DbTable::TABLE_REMITTANCE_REQUEST." as rr", array('id as remittance_request_id', 'remitter_id','beneficiary_id', 'agent_id', 'ops_id', 'amount',))              
                ->joinLeft(DbTable::TABLE_BENEFICIARIES." as b", "rr.beneficiary_id =b.id and b.status = '".STATUS_ACTIVE."'", array('b.name as beneficiary_name', 'ifsc_code', $bankAccountNumber, 'branch_name', 'branch_city', $branchAddress, 'bank_account_type', $mobile))
                ->where("rr.status = ?", STATUS_IN_PROCESS);
        //echo $select->__toString();
        $rows = $this->_db->fetchAll($select);      
     
        return $rows;
    }
    
    
    
    /*
     * updateRemitterRequestsForNEFT() will update the t_remittance_request and t_remittance_status log tables for neft updations
     */
    public function updateRemitterRequestsForNEFT($params)
    {
        
        if(!empty($params)){
           $objRemitStatusLog = new Remit_Remittancestatuslog();
                    
        $this->_db->beginTransaction(); 
        
        try 
        {
            foreach($params as $data)
            {
                $rrId = $data['remittance_request_id'];
                $remitterId = $data['remitter_id'];
                $remitReqData = array('status'=>STATUS_PROCESSED, 'fund_holder'=>REMIT_FUND_HOLDER_NEFT);
                //$updArr = array('block_amount'=> new Zend_Db_Expr("block_amount - ".$params['amount']), 'date_modified' => new Zend_Db_Expr('NOW()'));
                //$where = "agent_id = '".$params['agent_id']."'";
                
                $this->_db->update(DbTable::TABLE_REMITTANCE_REQUEST, $remitReqData, "id = $rrId");
                
                $remitStatusLog = array(
                                        'remittance_request_id'=>$rrId, 
                                        'status_old'=>STATUS_IN_PROCESS, 
                                        'status_new'=>STATUS_PROCESSED, 
                                        'by_remitter_id'=>$remitterId,
                                        'by_ops_id'=>TXN_OPS_ID,
                                        'date_created'=>new Zend_Db_Expr('NOW()')
                                       );
                //$this->_db->insert("t_remittance_status_log", $remitStatusLog);
                $objRemitStatusLog->addStatus($remitStatusLog);
            }
            $this->_db->commit();
         }
         catch (Exception $e) {
            // If any of the queries failed and threw an exception,
            // we want to roll back the whole transaction, reversing
            // changes made in the transaction, even those that succeeded.
            // Thus all changes are committed together, or none are.
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_db->rollBack();
            //throw new Exception ("Transaction not completed due to system failure");
            throw new Exception($e->getMessage());
            App_Logger::log($e->getMessage(),  Zend_Log::ERR);
        }
      }
    }
    
    
    /*
     * updateRemitterResponseFromNEFT() will update the t_remittance_request and t_remittance_status log tables for neft response 
     * $params['rrId'] = $rrId;
        $params['status'] = $status;
        $params['neftRemarks'] = neftRemarks
     */
    public function updateRemitterResponseFromNEFT($params)
    {
        $objBaseTxn = new BaseTxn();
        $objRemitStatusLog = new Remit_Remittancestatuslog();
        $user = Zend_Auth::getInstance()->getIdentity();
        $rrId = $params['rrId'];
        $status = $params['status'];
        $rrInfo = $this->getRemitterRequestsInfo($rrId);
        $remitterId = $rrInfo['remitter_id'];
        
        try 
        {
//            $this->_db->beginTransaction(); 
            
            if($status == FLAG_SUCCESS)
            {
            
                $txnData = array('remit_request_id'=>$rrId, 
                    'beneficiary_id'=>$rrInfo['beneficiary_id'], 
                    'product_id'=>$rrInfo['product_id'], 'amount'=>$rrInfo['amount'], 'txn_code' => $rrInfo['txn_code']);
                $txnResp = $objBaseTxn->remitSuccess($txnData);
                $remitReqData = array('status'=>$status, 
                    'fund_holder'=>REMIT_FUND_HOLDER_BENEFICIARY, 
                    'is_complete'=>FLAG_YES, 
                    'neft_remarks' => $params['neftRemarks'], 
                    'status_sms' => FLAG_PENDING);
                
            } // success ends
            else 
            {  // failure
                $txnData = array('remit_request_id'=>$rrId, 
                    'product_id'=>$rrInfo['product_id'], 'amount'=>$rrInfo['amount'],
                    'reversal_fee_amt'=>$rrInfo['fee'], 'reversal_service_tax'=>$rrInfo['service_tax']);
                $txnResp = $objBaseTxn->remitFailure($txnData);
                $remitReqData = array('status'=>$status, 
                    'fund_holder'=>REMIT_FUND_HOLDER_OPS,
                    'is_complete'=>FLAG_NO, 
                    'neft_remarks' => $params['neftRemarks'], 
                    'status_sms' => FLAG_PENDING);
            }

            $this->_db->update(DbTable::TABLE_REMITTANCE_REQUEST, $remitReqData, "id = $rrId");

            $remitStatusLog = array(
                                   'remittance_request_id'=>$rrId, 
                                   'status_old'=>STATUS_PROCESSED, 
                                   'status_new'=>$status, 
                                   'by_remitter_id'=>$remitterId,
                                   'by_ops_id'=>$user->id,
                                   'date_created'=>new Zend_Db_Expr('NOW()')
                                  );
           $objRemitStatusLog->addStatus($remitStatusLog);


//           $this->_db->commit();
        
        
        }
        catch (Exception $e) {
           // If any of the queries failed and threw an exception,
           // we want to roll back the whole transaction, reversing
           // changes made in the transaction, even those that succeeded.
           // Thus all changes are committed together, or none are.
           App_Logger::log($e->getMessage(), Zend_Log::ERR);
//           $this->_db->rollBack();
           //throw new Exception ("Transaction not completed due to system failure");
           throw new Exception($e->getMessage());
       }
       
         
    }
    
    
    public function sendNeftSms($params)
    {
        $m = new App\Messaging\Remit\BOI\Agent();
        $countSuccess = 0;
        $countFailure = 0;
        $count = 0;
        if(!empty($params)){
            $objRemitStatusLog = new Remit_Remittancestatuslog();
            
        $this->_db->beginTransaction(); 
        try 
        {
            foreach($params as $data)
            {
                
                $rrId = $data['remittance_request_id'];
                $remitterId = $data['remitter_id'];
                $status = $data['status'];
                $amount = $data['amount'];
                if($status=='success')
                    $status=STATUS_SUCCESS;
                else 
                    $status=FLAG_FAILURE;
                $objRemitterModel = new Remit_Boi_Remitter();
                $beneficiary = new Remit_Boi_Beneficiary();
                $remitRequestArr = $this->getRemitterRequestsInfo($rrId);
                $remitterArr = $objRemitterModel->findById($remitterId);
                $remitterName = substr($remitterArr->name, 0, 20);
                
                $beneficiaryArr = $beneficiary->findById($remitRequestArr['beneficiary_id']);    
                $beneficiaryPhone = (isset($beneficiaryArr->mobile))?$beneficiaryArr->mobile:0;
                if($status==STATUS_SUCCESS){
                    
                    $remitReqData = array('status_sms'=>$status);
                    
                    /*Send SMS to Remiiter & to Bene
                     */
                    $dataArr = array(
                        'amount' => $amount,
                        'nick_name' =>$beneficiaryArr->nick_name,
                        'remitter_name' => $remitterName, 
                        'contact_email' => BOI_REMITTANCE_EMAIL,
                        'contact_number' => BOI_CALL_CENTRE_NUMBER,
                        'remitter_phone' => $remitterArr->mobile,
                        'beneficiary_phone' => $beneficiaryPhone,
                        'product_name' => BOI_SHMART_REMIT);
                    $m->neftSuccessRemitter($dataArr);
                    if($beneficiaryPhone != 0){
                        $m->neftSuccessBeneficiary($dataArr);
                    }
                    $countSuccess++;
                    
                } else if($status==FLAG_FAILURE){
                    
                    $remitReqData = array('status_sms'=>$status);
                    
                    /*Send SMS to Remiiter
                     */
                      $dataArr = array('amount' => $amount, 'nick_name' => $beneficiaryArr->nick_name,'remitter_phone' => $remitterArr->mobile );
                      $m->neftFailureRemitter($dataArr);
                   $countFailure++; 
                }
                
                $this->_db->update(DbTable::TABLE_REMITTANCE_REQUEST, $remitReqData, "id = $rrId");
                
            
          }
            $this->_db->commit();
         }
         catch (Exception $e) {
           
            throw new Exception($e->getMessage());
            App_Logger::log($e->getMessage(),  Zend_Log::ERR);
        }
      }
      
      $countArr = array('success' => $countSuccess,'failure' => $countFailure);
      return $countArr;
    }
    
    
    /* getRemitterRequestsInfo() will return the remitters requests info
     * As param it will expect the remitter request id
     */
     public function getRemitterRequestsInfo($remitterRequestId){
         if($remitterRequestId<1)
             throw new Exception('Remitter Request id not found!');
         
        $select = $this->_db->select()       
                ->from(DbTable::TABLE_REMITTANCE_REQUEST." as rr", array('product_id','beneficiary_id','amount', 'remitter_id', 'txn_code', 'fee', 'service_tax'))              
                ->where("rr.id = ?", $remitterRequestId);
        //echo $select->__toString();
        $row = $this->_db->fetchRow($select);      
     
        return $row;
    }
    
    /* getRemitterRequestsForNEFT() will return the remitters requests for neft for cron / for failed txns
     */
     public function getAgentRemittanceRequests($status = STATUS_IN_PROCESS, $remitterId = 0, $remitReqId = 0, $limit=''){
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'".$decryptionKey."') as `bank_account_number`");
        $branchAddress = new Zend_Db_Expr("AES_DECRYPT(`b`.`branch_address`,'".$decryptionKey."') as branch_address");
        $mobile = new Zend_Db_Expr("AES_DECRYPT(`b`.`mobile`,'".$decryptionKey."') as mobile"); 
        $curdate = date("Y-m-d");
        $select = $this->_db->select()    
                ->from(DbTable::TABLE_REMITTANCE_REQUEST." as rr", array('id as remittance_request_id', 'remitter_id','beneficiary_id', 'agent_id', 'ops_id', 'amount', 'date_created', 'sender_msg', 'product_id', 'fee', 'service_tax'))              
                ->joinLeft(DbTable::TABLE_BENEFICIARIES." as b", "rr.beneficiary_id =b.id and b.status = '".STATUS_ACTIVE."'", array('b.name as beneficiary_name', 'nick_name', 'ifsc_code', $bankAccountNumber, 'branch_name', 'branch_city', $branchAddress, 'bank_account_type', $mobile, 'bank_name'));
                
                    
        if($remitterId > 0){
            $select->join(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION." as bapc", "bapc.product_id=rr.product_id AND bapc.agent_id = rr.agent_id AND '".$curdate."' >= bapc.date_start AND ('".$curdate."' <= bapc.date_end OR bapc.date_end = '0000-00-00')", array(''));
            $select->where("rr.remitter_id = ?", $remitterId);
        }
        if($remitReqId > 0){
            $select->where("rr.id = ?", $remitReqId);
        }
        $select->where("rr.status = ?", $status);
        if($limit!='' && $limit>0)
           $select->limit($limit);
        $rows = $this->_db->fetchAll($select);      
     
        return $rows;
    }
    
     /* getRemitRequestOnDateBasis() will return remit request for 'in_process','processed','success','failure','refund' status 
     */
    public function getRemitRequestOnDateBasis($param){ 
       
       $retData = array();
       $retNewData = array();
       
       if(!empty($param)){        
                
         $param['check_fee'] = false;
         $retData = $this->getRemittancefee($param);  
         $totalRemitFee = count($retData);
         
         if($totalRemitFee>=1){
            $retData =  $retData->toArray();
            $totalData = count($retData);
            
            $k=0;
            $alterData=array();  
            for($j=0;$j<$totalData;$j++){
                
                // adding transaction type field
                $alterData = $retData[$j];
                $alterData['txn_type'] = TXNTYPE_REMITTANCE;
                $alterData['crn'] = '';
                $alterData['txn_date'] = Util::returnDateFormatted($retData[$j]['txn_date'], "Y-m-d", "d-m-Y", "-");
                $alterData['agent_name'] = $retData[$j]['name'];
                
                // recreating array with adding new records for service tax and fee 
                $retNewData[$k] = $alterData;
                $k++;
                $retNewData[$k] = $alterData;
                $retNewData[$k]['amount'] = $retData[$j]['service_tax'];
                $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_SERVICE_TAX;
                $retNewData[$k]['txn_code'] = $retData[$j]['txn_code'];
                $retNewData[$k]['batch_name'] = $retData[$j]['batch_name'];
                $k++;
                $retNewData[$k] = $alterData;
                $retNewData[$k]['amount'] = $retData[$j]['fee'];
                $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_FEE;
                $retNewData[$k]['txn_code'] = $retData[$j]['txn_code'];
                $retNewData[$k]['batch_name'] = $retData[$j]['batch_name'];
                $k++;
            }
         }
       }
       return $retNewData;
    }
    
    
    /* Get remittance fee for an agent on a particular date for a product
     * 
     */

    public function getRemittancefee($param) {

        $detailsArr = array();


        $bankUnicodeArr = Util::bankUnicodesArray();

        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1'];
        }

        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $objKotakRemitRequest = new Remit_Kotak_Remittancerequest();
                $detailsArr = $objKotakRemitRequest->getRemittancefeeAll($param);
                break;
            case $bankUnicodeArr['2']:
                $objRatnakarRemitRequest = new Remit_Ratnakar_Remittancerequest();
                $detailsArr = $objRatnakarRemitRequest->getRemittancefeeAll($param);
                break;
            case $bankUnicodeArr['1']:
                $detailsArr = $this->getRemittancefeeboi($param); 
        } 
        return $detailsArr;
    }
    
    
    
    /* getAgentTotalRemittance() will return the total agent remittance on date basis
     * Expected params:- date, agent id
     */

    public function getAgentTotalRemittanceFeeSTax($param, $status = array()) {
        $detailsArr = array();
        $bankUnicodeArr = Util::bankUnicodesArray();
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1']; // default boi
        }
        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $kotakRemitModel = new Remit_Kotak_Remittancerequest();
                $detailsArr = $kotakRemitModel->getAgentTotalRemittanceFeeSTax($param);
                break;
            case $bankUnicodeArr['2']:
                $ratnakarRemitModel = new Remit_Ratnakar_Remittancerequest();
                $detailsArr = $ratnakarRemitModel->getAgentTotalRemittanceFeeSTax($param);
                break;
            case $bankUnicodeArr['1']:
                $date = isset($param['date']) ? $param['date'] : '';
                $agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
                $to = isset($param['to']) ? $param['to'] : '';
                $from = isset($param['from']) ? $param['from'] : '';
                $statusWhere = '';

                if (!empty($status)) {
                    foreach ($status as $statusVal) {
                        if ($statusWhere != ''){
                            $statusWhere .= " OR ";
                        }
                        $statusWhere .= "rr.status='" . $statusVal . "'";
                    }
                }
                //Enable DB Slave
                $this->_enableDbSlave();
                $select = $this->select();
                $select->from(DbTable::TABLE_REMITTANCE_REQUEST . " as rr", array('sum(rr.amount) as agent_total_remittance', 'sum(rr.fee) as agent_total_remittance_fee', 'sum(rr.service_tax) as agent_total_remittance_stax', 'count(rr.id) as agent_total_remittance_count'));
                $select->setIntegrityCheck(false);
                if ($statusWhere == '') {
                    $select->where("rr.status = '" . STATUS_IN_PROCESS . "' OR rr.status = '" . STATUS_PROCESSED . "' OR rr.status =  '" . STATUS_SUCCESS . "' OR rr.status = '" . STATUS_REFUND . "' OR rr.status = '" . FLAG_FAILURE . "'");
                } else {
                    $select->where($statusWhere);
                }
                if ($agentId > 0) {
                    $select->where("rr.agent_id = ? ", $agentId);
                }
                if ($to != '' && $from != ''){
                    $select->where("DATE(rr.date_created) BETWEEN '$from' AND '$to'");
                } else {
                    $select->where("DATE(rr.date_created) = ?", $date);
                }
                $detailsArr = $this->fetchRow($select);
                //Disable DB Slave
                $this->_disableDbSlave();
                break;
        }

        return $detailsArr;
    }
    
    
    
    /*  getAgentTotalRemittanceRefundSTax() function is responsible fetch data for agent total remitter refund and Service Tax amount 
     *  as params it will accept agent id and transaction date
     */
    
    public function getAgentTotalRemittanceRefundSTax($param){
        $detailsArr = array();
        $bankUnicodeArr = Util::bankUnicodesArray();
        if(!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1']; // default boi
        }
        $agentId = isset($param['agent_id'])?$param['agent_id']:'';
        $date = isset($param['date'])?$param['date']:'';
        switch($param['bank_unicode'])
        {
            case $bankUnicodeArr['3']:
                $kotakRemitModel = new Remit_Kotak_Remittancerequest();
                $detailsArr = $kotakRemitModel->getAgentTotalRemittanceRefundSTax($param);
                break;
            case $bankUnicodeArr['2']:
                $ratnakarRemitModel = new Remit_Ratnakar_Remittancerequest();
                $detailsArr = $ratnakarRemitModel->getAgentTotalRemittanceRefundSTax($param);
                break;
            case $bankUnicodeArr['1']:
                //Enable DB Slave
                $this->_enableDbSlave();
                $select =  $this->_db->select() ; 
                $select->from(DbTable::TABLE_REMITTANCE_REFUND.' as rr', array('sum(rr.amount) as agent_total_remittance_refund', 'sum(rr.service_tax) as agent_total_remittance_refund_stax', 'count(rr.id) as agent_total_remittance_refund_count', 'sum(rr.reversal_service_tax) as agent_total_reversal_refund_stax', 'sum(rr.reversal_fee) as agent_total_reversal_refund_fee'));
                if($agentId > 0){            
                   $select->where('rr.agent_id=?',$agentId);
                }
                $select->where("rr.status='".FLAG_SUCCESS."'");         
                $select->where("DATE(rr.date_created) ='".$date."'"); 
                $detailsArr = $this->_db->fetchRow($select); 
                //Disable DB Slave
                $this->_disableDbSlave();
                break;
        }
           //echo $select.'<br><br>'; exit;
         return $detailsArr;
    }
    
    
    
    /* for cron
     * getAgentRemittanceDetails() will return remittance amt details for an agent on a particular date 
     * for a product for comm report purpose
     * As Params:- agent id, product id, query date
     */
     public function getAgentRemittanceDetails($param){
        $agentId = isset($param['agent_id'])?$param['agent_id']:'';
        $productId = isset($param['product_id'])?$param['product_id']:'';
        $date = isset($param['date'])?$param['date']:'';
               $txn_id = isset($param['txn_id']) ? $param['txn_id'] : '';
 
        $bankUnicodeArr = Util::bankUnicodesArray();
        if(!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1']; // default boi
        }
        switch($param['bank_unicode'])
        {
            case $bankUnicodeArr['3']:
                $kotakRemitModel = new Remit_Kotak_Remittancerequest();
                $detailsArr = $kotakRemitModel->getAgentRemittanceDetails($param);
                break;
            case $bankUnicodeArr['2']:
                $ratnakarRemitModel = new Remit_Ratnakar_Remittancerequest();
                $detailsArr = $ratnakarRemitModel->getAgentRemittanceDetails($param);
                break;
            case $bankUnicodeArr['1']:
                $select = $this->select();       
                $select->from(DbTable::TABLE_REMITTANCE_REQUEST." as rr", array('rr.amount as transaction_amount', 'rr.fee as transaction_fee', 'rr.service_tax as transaction_service_tax', 'rr.txn_code as transaction_ref_no'));              
                $select->setIntegrityCheck(false);
	if(!isset($param['txn_id'])){

                $select->join(DbTable::TABLE_REMITTANCE_STATUS_LOG." as rsl", "rr.id = rsl.remittance_request_id AND status_new = '".STATUS_SUCCESS."'" , array('rsl.date_created as date_success'));
                $select->where("rr.status =  '".STATUS_SUCCESS."'");
                $select->where("DATE(rsl.date_created) = ?",  $date);               
	}
                if($agentId> 0){
                   $select->where('rr.agent_id =?', $agentId); 
                }
                 if($productId> 0){
                    $select->where('rr.product_id=?',$productId); 
                 }

		if($txn_id > 0){
		            $select->where('rr.id=?', $txn_id);
		        }

                 $detailsArr = $this->fetchAll($select);
                break;
        }
        
        return $detailsArr;      
    }
    
    
    /* getAgentRemittanceRefundDetails() will return remittance fee for an agent on a particular date for a product
     * As Params:- agent id, product id, query date
     */
     public function getAgentRemittanceRefundDetails($param){
        $agentId = isset($param['agent_id'])?$param['agent_id']:'';
        $productId = isset($param['product_id'])?$param['product_id']:'';
        $date = isset($param['date'])?$param['date']:'';
        $refund_txn_id = isset($param['refund_txn_id']) ? $param['refund_txn_id'] : '';
        
        $bankUnicodeArr = Util::bankUnicodesArray();
        if(!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1']; // default boi
        }
        switch($param['bank_unicode'])
        {
            case $bankUnicodeArr['3']:
                $kotakRemitModel = new Remit_Kotak_Remittancerequest();
                $detailsArr = $kotakRemitModel->getAgentRemittanceRefundDetails($param);
                break;
            case $bankUnicodeArr['2']:
                $ratnakarRemitModel = new Remit_Ratnakar_Remittancerequest();
                $detailsArr = $ratnakarRemitModel->getAgentRemittanceRefundDetails($param);
                break;
            case $bankUnicodeArr['1']:
                $select =  $this->_db->select() ; 
                $select->from(DbTable::TABLE_REMITTANCE_REFUND." as rr", array('rr.amount as transaction_amount', 'rr.fee as transaction_fee', 'rr.service_tax as transaction_service_tax', 'rr.txn_code as transaction_ref_no'));              
                $select->where("rr.status = '".STATUS_SUCCESS."'");
                $select->where("DATE(rr.date_created)='".$date."'");               

                if($agentId > 0){
                   $select->where('rr.agent_id =?', $agentId); 
                }
                 if($productId > 0){
                    $select->where('rr.product_id =?', $productId); 
                 }

		if($refund_txn_id > 0){
                    $select->where('rr.id=?', $refund_txn_id);
                }

                 $detailsArr = $this->_db->fetchAll($select);  
                break;
        }
        
        return $detailsArr;
    }
    
    
    /*
     * get all in_process remit requests
     */
     public function getPendingRemitRequests( $page = 1, $paginate = NULL, $force = FALSE){
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`ben`.`bank_account_number`,'".$decryptionKey."') as ben_account_number");
        
        $select = $this->select();       
        $select->from(DbTable::TABLE_REMITTANCE_REQUEST." as r", array('r.id AS rmid','r.product_id','DATE(r.date_created) as date_created','r.amount', 'r.txn_code'));              
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_REMITTERS." as rem", "rem.id = r.remitter_id" , array('rem.mobile as mobile_number','rem.name as rem_name'));
        $select->joinLeft(DbTable::TABLE_BENEFICIARIES." as ben", "r.beneficiary_id = ben.id" , array('ben.id as ben_id','ben.name as ben_name','concat(ben.address_line1,", ",ben.address_line2) as ben_address', $bankAccountNumber));
        $select->joinLeft(DbTable::TABLE_PRODUCTS." as p", "r.product_id = p.id ",array('p.ecs_product_code'));
        $select->joinLeft(DbTable::TABLE_BANK." as b", "p.bank_id = b.id ",array('b.name as bank_name'));
        $select->where("r.status = '".STATUS_IN_PROCESS."'");
        $select->order('date_created');
        return $this->_paginate($select, $page, $paginate);
         
     }
     
     /*
      * Update selected NEFT requests
      */
       public function updateRemitRequests($data){
           
        $user = Zend_Auth::getInstance()->getIdentity();
        //$dateFormat = date("dmyHis", time());
        $dateFormat = Util::getNeftBatchFileName();
        $batchName = REMIT_BATCH_NAME_PREFIX.$dateFormat;
        $objRemitStatusLog = new Remit_Remittancestatuslog();
        foreach($data as $requestId){
            echo $requestId;
                    try{
                       $remitReqData = array('status' => STATUS_PROCESSED ,
                           'batch_name' => $batchName,
                           'ops_id'     => $user->id,
                           'fund_holder'=> REMIT_FUND_HOLDER_NEFT);
                       $this->_db->update(DbTable::TABLE_REMITTANCE_REQUEST, $remitReqData, "id = $requestId"); 
                       
                       $remitReqLog = array ('remittance_request_id' => $requestId,
                           'status_old' => STATUS_IN_PROCESS, 'status_new' => STATUS_PROCESSED,
                           'by_ops_id' => $user->id ,'date_created' => new Zend_Db_Expr('NOW()'));
                      $objRemitStatusLog->addStatus($remitReqLog);
                    }catch(Zend_Exception $ze){
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    }
                } 
            
        }
        
        /*
         * Get the NEFT batch files name 
         */
    public function   getBatchFilesArray($params,$page = 1, $paginate = NULL, $force = FALSE){
       
        $from = $params['from_date'].' 00:00:00';
        $to = $params['to_date'].' 23:59:59';
        $amount = $params['amount'];
        
        $select = $this->_db->select(); 
        $select->from(DbTable::TABLE_REMITTANCE_REQUEST." as r", 
                array('r.batch_name', 'count(*) as txn_count', 'sum(r.amount) as txn_amount','neft_processed'));              
        $select->where("r.batch_name != ''");
        $select->where("r.batch_date BETWEEN '$from' AND '$to'");
       
        $select->group('r.batch_name');
        if(isset($amount) && $amount > 0){
            $select->having("txn_amount = $amount "); 
        }
        $select->order('r.batch_name DESC');
        $BFiles = $this->_db->fetchAll($select);
        
        return $BFiles;
    }
    
    private function sqlNeftRecords($batchName = '',$status = '', $strReqId = '')
    {
        
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'".$decryptionKey."') as bank_account_number");
        $mobile = new Zend_Db_Expr("AES_DECRYPT(`b`.`mobile`,'".$decryptionKey."') as mobile");
        $email = new Zend_Db_Expr("AES_DECRYPT(`b`.`email`,'".$decryptionKey."') as email");
         
        $select = $this->select();
        $select->setIntegrityCheck(false);       
        $select->from(DbTable::TABLE_REMITTANCE_REQUEST." as r");              
        $select->join(DbTable::TABLE_BENEFICIARIES. " as b", "b.id = r.beneficiary_id", array("ifsc_code", "name", $bankAccountNumber, "bank_account_type", "address_line1", $email, $mobile));
        if ($batchName != ''){
            $select->where("r.batch_name = ?", $batchName);
        }
        if($status != ''){
            $select->where("r.status = ?", $status);   
        }
        if($strReqId != ''){
            $select->where("r.id IN ($strReqId)");   
        }
        $select->order('date_created');
        
        return $select;
    }
        /*
         * Get the NEFT batch records 
         */
    public function getBatchRecords($batchName){

        $select = $this->sqlNeftRecords($batchName);
        return $this->_db->fetchAll($select); 
        
    }
    
    /*
    * Get the Processed records 
    */
    public function getProcessedRecords($status = '', $page = 1, $paginate = NULL, $batch_name=''){

        $select = $this->sqlNeftRecords($batch_name, $status);
        return $this->_paginate($select, $page, $paginate);  
        
    }
    
    /*
    * Get the NEFT batch records 
    */
    public function getSelectedNeftRecords($strReqId){

        $select = $this->sqlNeftRecords('', '', $strReqId);
        return $this->fetchAll($select); 
        
    }

    /*
    * Get the pending status_sms records 
    */
    public function getPendingSmsRecords(){

        $select = $this->_db->select();       
        $select->from(DbTable::TABLE_REMITTANCE_REQUEST." as r", array('r.id as remittance_request_id','remitter_id','beneficiary_id','agent_id','amount','status','status_sms'));              
        $select->where("r.status_sms = '".STATUS_PENDING."'");
        $select->where("r.status = '".FLAG_SUCCESS."' || r.status = '".FLAG_FAILURE."'");
        return $this->_db->fetchAll($select); 
        
    }
   /* getRemitterRemittanceRequests() will return the remitters requests for neft for cron / for failed txns
     */
     public function getRemitterRemittanceRequests($remitterId = 0){
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $mobile = new Zend_Db_Expr("AES_DECRYPT(`b`.`mobile`,'".$decryptionKey."') as mobile");
        $email = new Zend_Db_Expr("AES_DECRYPT(`b`.`email`,'".$decryptionKey."') as email"); 
        $durationArr = Util::getDurationDates('month');
        $from = $durationArr['from'];
        $to = $durationArr['to'];  
        $select = $this->_db->select()    
                ->from(DbTable::TABLE_REMITTANCE_REQUEST." as rr", array('id', 'remitter_id','beneficiary_id', 'amount', 'date_created'))              
                ->joinLeft(DbTable::TABLE_BENEFICIARIES." as b", "rr.beneficiary_id =b.id and b.status = '".STATUS_ACTIVE."'", array('b.name as beneficiary_name',  $mobile, $email));
        $select->where("rr.remitter_id = ?", $remitterId);
        $select->where("rr.status = '".FLAG_SUCCESS."' OR rr.status = '".STATUS_IN_PROCESS."' OR rr.status = '".STATUS_PROCESSED."'");
        $select->where("rr.date_created BETWEEN '$from' AND '$to'");
        //echo $select->__toString();//exit;
        $rows = $this->_db->fetchAll($select);      
     
        return $rows;
    } 
    
     /* getRemitterRemittanceCount() will return the remitters requests count
      * 
      *   */
     public function getRemitterRemittanceCountandSum($remitterId = 0){
        $durationArr = Util::getDurationDates('month');
        $from = $durationArr['from'];
        $to = $durationArr['to'];  
        $select = $this->_db->select()    
                ->from(DbTable::TABLE_REMITTANCE_REQUEST." as rr", array('count(*) as count','sum(amount) as total'));              
        $select->where("rr.remitter_id = ?", $remitterId);
        $select->where("rr.status = '".FLAG_SUCCESS."' OR rr.status = '".STATUS_IN_PROCESS."' OR rr.status = '".STATUS_PROCESSED."'");
        $select->where("rr.date_created BETWEEN '$from' AND '$to'");
        //echo $select->__toString();//exit;
        $rows = $this->_db->fetchRow($select);      
      
        return $rows;
    } 
    
    
    
     /* getRemitterRemittances() will return remitters remittances details
      * as params :- remitter id and txn date
      */
     public function getRemitterRemittances($param){

       // $remitterId = isset($param['remitter_id'])?$param['remitter_id']:0; 
        $toDate = isset($param['to_date'])?$param['to_date']:''; 
        $fromDate = isset($param['from_date'])?$param['from_date']:''; 
        
       // if($toDate!='' && $fromDate!='' && $remitterId>=1){ 
        if($toDate!='' && $fromDate!=''){ 
            $select = $this->_db->select();    
            $select->from(DbTable::TABLE_REMITTANCE_REQUEST." as rr", array('rr.beneficiary_id', 'rr.amount', 'DATE(rr.date_created) as txn_date', 'rr.batch_name'));              
            $select->joinLeft(DbTable::TABLE_REMITTERS." as r", "rr.remitter_id = r.id", array('r.name as remitter_name', 'DATE(r.date_created) as remitter_reg_date', 'mobile'));              
            $select->joinLeft(DbTable::TABLE_BENEFICIARIES." as b", "rr.beneficiary_id = b.id", array('b.name as bene_name', 'b.bank_name as bene_bank_name', 'b.ifsc_code as bene_ifsc_code'));              
          //  $select->where("rr.remitter_id = ?", $remitterId);
            $select->where("rr.status = '".FLAG_SUCCESS."' OR rr.status = '".STATUS_IN_PROCESS."' OR rr.status = '".STATUS_PROCESSED."'");
            $select->where("DATE(rr.date_created) BETWEEN '".$fromDate."' AND '".$toDate."'");
           //echo $select->__toString();exit;
//            echo '<pre>';
//            print_r($this->_db->fetchAll($select));
//            die;
            return $this->_db->fetchAll($select);      
        }
        else return array();
    } 
    
     /* getneftlog() will return NEFT log
      */
     public function getNEFTlog($batchName = 0){

            $batchName = isset($batchName)?$batchName:0; 
            $select = $this->select();    
            $select->setIntegrityCheck(false);
            $select->from(DbTable::TABLE_LOG_NEFT_DOWNLOAD." as nd");              
            $select->joinLeft(DbTable::TABLE_OPERATION_USERS." as ops", "ops.id =nd.ops_id", array('concat(ops.firstname," ",ops.lastname) as name'));
            $select->where("nd.batch_name = ?",  $batchName);
            return $this->fetchAll($select);  
       
       
    }
    
     /*
         * Get the NEFT batch log count
         */
    public function   getBatchFilesCountArray($param){
       
        $batchcountArr = array();
        foreach($param as $batchName){
        $select = $this->_db->select();     
        $select->from(DbTable::TABLE_LOG_NEFT_DOWNLOAD." as nd",array( 'count(*) as dn_count'));              
        $select->where("nd.batch_name = ?",$batchName['batch_name']);
        $row = $this->_db->fetchRow($select); 
        $batchcountArr[] = $row;
        }
        return $batchcountArr;


    }
 
    
    /* getRemittanceRefunds() will return remittance refunds for particular from and to date
     * As Params:- query to and from date
     */
     public function getRemittanceRefunds($param){
        $toDate = isset($param['to_date'])?$param['to_date']:'';
        $fromDate = isset($param['from_date'])?$param['from_date']:'';
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'".$decryptionKey."') as beneficiary_bank_account_number");
         
        if($toDate!='' && $fromDate!=''){
            
            $select =  $this->_db->select() ; 
            $select->from(DbTable::TABLE_REMITTANCE_REFUND." as rr", array('amount as refund_amount', 'txn_code', 'date_created as refund_date', 'reversal_fee', 'reversal_service_tax', 'status'));              
            $select->joinLeft(DbTable::TABLE_REMITTERS." as rem", "rr.remitter_id = rem.id" , array('rem.name as remitter_name', 'rem.email as remitter_email', 'rem.mobile as remitter_mobile_number'));
            $select->joinLeft(DbTable::TABLE_REMITTANCE_REQUEST." as rrq", "rr.remittance_request_id = rrq.id" , array('rrq.neft_remarks','rrq.neft_remarks as remarks','txn_code as request_txn_code'));
            $select->joinLeft(DbTable::TABLE_BENEFICIARIES." as b", "rrq.beneficiary_id = b.id" , array('b.name as beneficiary_name', $bankAccountNumber));
            $select->where("rr.status = '".STATUS_SUCCESS."'");
            $select->where("DATE(rr.date_created) BETWEEN '".$fromDate."' AND '".$toDate."'");
            
            //echo $select->__toString().'<br><br>';
            return $this->_db->fetchAll($select); 
        
        } else return array();
    }
    
    /* getNeftBatchForDD() will return neft batch names for drop down
     */
     public function getNeftBatchForDD($status)
    {
                
        $select  = $this->_db->select();        
        $select->from(DbTable::TABLE_REMITTANCE_REQUEST, array('batch_name'));
        $select->distinct(TRUE);
        
        if($status!='')
           $select->where("status = '".$status."'");       
       // echo $select; exit;
        
        $batchNames = $this->_db->fetchAll($select);     
           
        $dataArray = array();
        //$dataArray[''] = "Select Fund Transfer Type";
        
        foreach ($batchNames as $id => $batchName) {
            $dataArray[$batchName['batch_name']] = $batchName['batch_name'];
        }

        return $dataArray;     
    }
    public function getneftResponse( $param, $page = 1, $paginate = NULL)
    {       
            $decryptionKey = App_DI_Container::get('DbConfig')->key;
            $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'".$decryptionKey."') as beneficiary_bank_account_number");
            if(isset($param['duration']) && $param['duration'] != ''){
            $dates = Util::getDurationDates($param['duration']);
            $fromDate = $dates['from'];           
            $toDate = $dates['to'];  
            }
            else{
            $fromDate = $param['from'];           
            $toDate = $param['to'];    
            }
            $select =  $this->_db->select() ; 
            $select->from(DbTable::TABLE_REMITTANCE_REQUEST." as rr", array('rr.amount', 'rr.txn_code', 'DATE_FORMAT(rr.date_created, "%d-%m-%Y") as date_created','rr.neft_remarks as remarks','rr.status','rr.batch_name','neft_remarks'));              
            $select->joinLeft(DbTable::TABLE_REMITTERS." as rem", "rr.remitter_id = rem.id" , array('rem.name as remitter_name', 'rem.mobile as remitter_mobile_number'));
            $select->joinLeft(DbTable::TABLE_BENEFICIARIES." as b", "rr.beneficiary_id = b.id" , array('b.name as beneficiary_name', $bankAccountNumber));
            $select->where("rr.status = '".STATUS_SUCCESS."' OR rr.status = '".STATUS_FAILURE."' OR rr.status = '".STATUS_REFUND."'");
            $select->where("date(rr.date_created) BETWEEN '".$fromDate."' AND '".$toDate."'");
            $queryArray = $this->_db->fetchAll($select);
            
                return $queryArray;
       
    }
    
     
    
    /*
      * Update selected NEFT as processed
      */
       public function neftProcessed($batchName){
           
        $user = Zend_Auth::getInstance()->getIdentity();
           
                    try{
                        
                       $neftProcessedData = array(
                           'neft_processed' => FLAG_YES,
                           'neft_processed_date' => new Zend_Db_Expr("NOW()"),
                           'neft_processed_ops_id' => $user->id);
                       $update = $this->update($neftProcessedData,"batch_name = '$batchName'"); 
                       
                       return $update;
                       
                    }catch(Zend_Exception $e){
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    }
             
            
        }
        
        
        /*
      * updateRemitRequestsForNEFTBatch() will update requests of in process status to processed & will return count.
      */
       public function updateRemitRequestsForNEFTBatch(){
           
        //$user = Zend_Auth::getInstance()->getIdentity();
        //$dateFormat = date("dmyHis", time());
        $dateFormat = Util::getNeftBatchFileName();
        $batchName ='';
        $batchDate = new Zend_Db_Expr('NOW()');
        $objRemitStatusLog = new Remit_Remittancestatuslog();
        $inprocessRequests = $this->getAgentRemittanceRequests();
        $totalRequests = count($inprocessRequests);
        
        if($totalRequests>0){
            
        /*$remitReqData = array('status' => STATUS_PROCESSED ,
                              'batch_name' => $batchName,
                              'batch_date' => $batchDate,
                              'ops_id'     => TXN_OPS_ID,
                              'fund_holder'=> REMIT_FUND_HOLDER_NEFT
                             );
         */
        $batchName = REMIT_BATCH_NAME_PREFIX.$dateFormat;
        
        $updateSql = 'UPDATE '.DbTable::TABLE_REMITTANCE_REQUEST.' SET batch_name="'.$batchName.'", batch_date='.$batchDate.'';
        $updateSql .= ', status="'.STATUS_PROCESSED.'", ops_id="'.TXN_OPS_ID.'", fund_holder="'.REMIT_FUND_HOLDER_NEFT.'"';
        $updateSql .= ' WHERE status="'.STATUS_IN_PROCESS.'" LIMIT '.BOI_REMITTANCE_TXN_LIMIT_PER_BATCHFILE;
        //echo $updateSql; exit;
        //print $updateSql.PHP_EOL;
        $this->_db->query($updateSql);
                       
        foreach($inprocessRequests as $request){
                    try{
                       
                       $remitReqLog = array ('remittance_request_id' => $request['remittance_request_id'],
                           'status_old' => STATUS_IN_PROCESS, 'status_new' => STATUS_PROCESSED,
                           'by_ops_id' => TXN_OPS_ID ,'date_created' => new Zend_Db_Expr('NOW()'));
                      $objRemitStatusLog->addStatus($remitReqLog);
                    }catch(Zend_Exception $e){
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    }
                } 
        }
        
        return $batchName;
     }
     
     
     
   /* getRemittanceException() will return the remittance exception details
     */
     public function getRemittanceException($param){
        
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'".$decryptionKey."') as bank_account_number"); 
        $toDate = isset($param['to'])?$param['to']:'';
        $fromDate = isset($param['from'])?$param['from']:'';
        $limit = isset($param['noofrecords']) ? $param['noofrecords'] : 0;
        
        $select = $this->_db->select()    
                ->from(DbTable::TABLE_REMITTANCE_REQUEST." as rr", array('sum(rr.amount) as total_amount', 'count(rr.id) as total_count', 'rr.date_created'))              
                ->joinLeft(DbTable::TABLE_REMITTERS." as r", "rr.remitter_id = r.id" , array('r.name as remitter_name', 'r.mobile as remitter_mobile_number', 'r.email as remitter_email'))
                ->joinLeft(DbTable::TABLE_BENEFICIARIES." as b", "rr.beneficiary_id =b.id", array('b.name as beneficiary_name', $bankAccountNumber, 'b.bank_name', 'b.ifsc_code', 'b.by_agent_id'))
                ->group('rr.beneficiary_id')
                ->group('DATE(rr.date_created)')
                ->where("DATE(rr.date_created) BETWEEN '".$fromDate."' AND '".$toDate."'")
                ->where("rr.status = '".STATUS_SUCCESS."' OR rr.status = '".STATUS_IN_PROCESS."' OR rr.status = '".STATUS_PROCESSED."'");
        if($limit > 0){
            $select->limit($limit);
        }

        //echo $select->__toString();//exit;
        $rows = $this->_db->fetchAll($select);      
     
        $rsCount = count($rows);
        $retData = array();
        $i = 0;
        
        if($rsCount > 0)
        {
            foreach($rows as $val)
            {
                $retData[$i]['date_created'] = $val['date_created'];
                $retData[$i]['total_amount'] = $val['total_amount'];
                $retData[$i]['total_count'] = $val['total_count'];
                $retData[$i]['remitter_name'] = $val['remitter_name'];
                $retData[$i]['remitter_mobile_number'] = $val['remitter_mobile_number'];
                $retData[$i]['remitter_email'] = $val['remitter_email'];
                $retData[$i]['beneficiary_name'] = $val['beneficiary_name'];
                $retData[$i]['bank_account_number'] = $val['bank_account_number'];

                $agentUser = new AgentUser();
                $usertype = $agentUser->getAgentDetailsById($val['by_agent_id']);
                $agentType = $agentUser->getAgentCodeName($usertype['user_type'], $val['by_agent_id']);

                $retData[$i] = array_merge($retData[$i], $agentType);
                
                $retData[$i]['agent_name'] = $usertype['first_name'].' '.$usertype['last_name'];
                $retData[$i]['agent_code'] = $usertype['agent_code'];                
                $retData[$i]['bank_name'] = $val['bank_name'];
                $retData[$i]['ifsc_code'] = $val['ifsc_code'];
                
                
                $i++;
            }
        }

        return $retData;
    }
    
    
     /*  getTotalNEFTRequest() is responsible for count of neft batch files or un-processed neft batch files
     *  as params it will accept batch date or neft processed status
     */
    
    public function getTotalNEFTRequest($param){

        $batchDate = isset($param['batchDate'])?$param['batchDate']:'';
        $neftStatus = isset($param['neftStatus'])?$param['neftStatus']:'';
                
            $select =  $this->_db->select() ; 
            $select->from(DbTable::TABLE_REMITTANCE_REQUEST.' as rr', array('COUNT(DISTINCT(rr.batch_name)) as total_batch_files'));
            $select->where('rr.batch_name<>""');
            if($batchDate!='')            
               $select->where('DATE(rr.batch_date)=?', $batchDate);
            
            if($neftStatus!='')            
               $select->where('rr.neft_processed=?', $neftStatus);
            
           //echo $select.'<br><br>'; 
         return $this->_db->fetchRow($select);
    }

     /* getAgentRemittanceCountandSum() will return the agent's remittance requests count and sum
      * 
      *   */
     public function getAgentRemittanceCountandSum($param) {

        $detailsArr = array();
        $bankUnicodeArr = Util::bankUnicodesArray();

        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1'];
        }

        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $objKotakRemitRequest = new Remit_Kotak_Remittancerequest();
                $detailsArr = $objKotakRemitRequest->getAgentRemittanceCountandSum($param);
                break;
            case $bankUnicodeArr['2']:
                $objRatnakarRemitRequest = new Remit_Ratnakar_Remittancerequest();
                $detailsArr = $objRatnakarRemitRequest->getAgentRemittanceCountandSum($param);
                break;
            case $bankUnicodeArr['1']:
                $from = $param['from'];
                $to = $param['to'];
                $agentId = $param['agentId'];
                $select = $this->_db->select()
                        ->from(DbTable::TABLE_REMITTANCE_REQUEST . " as rr", array('count(*) as count', 'sum(amount) as total'));
                $select->where("rr.agent_id = ?", $agentId);
                $select->where("rr.date_created BETWEEN '$from' AND '$to'");
//        echo $select->__toString();//exit;
                $detailsArr = $this->_db->fetchRow($select);
        }
        return $detailsArr;
    }

    /* getAgentRemittance() will return the agent's remittance
      * 
      *   */
     public function getAgentRemittance($param) {
        $detailsArr = array();
        $bankUnicodeArr = Util::bankUnicodesArray();

        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1'];
        }

        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $objKotakRemitRequest = new Remit_Kotak_Remittancerequest();
                $detailsArr = $objKotakRemitRequest->getAgentRemittance($param);
                break;
            case $bankUnicodeArr['2']:
                $objRatnakarRemitRequest = new Remit_Ratnakar_Remittancerequest();
                $detailsArr = $objRatnakarRemitRequest->getAgentRemittance($param);
                break;
            case $bankUnicodeArr['1']:
                $from = $param['from'];
                $to = $param['to'];
                $agentId = $param['agentId'];
                $select = $this->_db->select()
                        ->from(DbTable::TABLE_REMITTANCE_REQUEST . " as rr", array('amount', 'DATE(date_created) as date_created'));
                $select->where("rr.agent_id = ?", $agentId);
//        $select->where("rr.status = '".FLAG_SUCCESS."' OR rr.status = '".STATUS_IN_PROCESS."' OR rr.status = '".STATUS_PROCESSED."'");
                $select->where("date(rr.date_created) BETWEEN '$from' AND '$to'");
//        echo $select->__toString();
                $detailsArr = $this->_db->fetchAll($select);
        }
        return $detailsArr;
    }

    /* getRemitRefundsOnDateBasis() will return remit refund on date basis
     */
    public function getRemitRefundsOnDateBasis($param){ 
       
       $retData = array();
       $retNewData = array();
       
       if(!empty($param)){        
                
         $param['check_fee'] = false;
         $retData = $this->getRemitRefundfee($param); 
         $totalRemitRefundFee = count($retData);
         
         if($totalRemitRefundFee>=1){
            //$retData =  $retData->toArray();
            $totalData = count($retData);
            
            // adding moer fields and recreating array with adding new records for service tax and fee 
            $k=0;
            $alterData=array();
            for($j=0;$j<$totalData;$j++){
                
                // adding transaction type field
                $alterData = $retData[$j];
                $alterData['txn_type'] = TXNTYPE_REMITTANCE_REFUND;
                $alterData['crn'] = '';
                //$alterData['txn_code'] = '';
                $alterData['txn_date'] = Util::returnDateFormatted($retData[$j]['txn_date'], "Y-m-d", "d-m-Y", "-");
                $alterData['agent_name'] = $retData[$j]['name'];
                $alterData['batch_name'] = '';
                
                // recreating array with adding new records for service tax and fee 
                $retNewData[$k] = $alterData;
                $retNewData[$k]['batch_name'] = '';
                $k++;
                $retNewData[$k] = $alterData;
                $retNewData[$k]['amount'] = $retData[$j]['reversal_service_tax'];
                $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_SERVICE_TAX;
                $retNewData[$k]['txn_code'] = $retData[$j]['txn_code'];
                $retNewData[$k]['batch_name'] = '';
                $k++;
                $retNewData[$k] = $alterData;
                $retNewData[$k]['amount'] = $retData[$j]['reversal_fee'];
                $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_REFUND_FEE;
                $retNewData[$k]['txn_code'] = $retData[$j]['txn_code'];
                $retNewData[$k]['batch_name'] = '';
                $k++;
            }
            
         }
       }
      
       return $retNewData;
    }
    

    
    
     /*   Get remitter refund fee for an agent on a particular date for a product
     */
    
     public function getRemitRefundfee($param) {
         $detailsArr = array(); 
        $bankUnicodeArr = Util::bankUnicodesArray();

        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1'];
        }

        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $objKotakRemitRequest = new Remit_Kotak_Remittancerequest();
                $detailsArr = $objKotakRemitRequest->getRemitRefundfeeAll($param);
                break;
            case $bankUnicodeArr['2']:
                $objRatnakarRemitRequest = new Remit_Ratnakar_Remittancerequest();
                $detailsArr = $objRatnakarRemitRequest->getRemitRefundfeeAll($param);
                break;
            case $bankUnicodeArr['1']:
                $detailsArr = $this->getRemitRefundfeeAll($param); 
                break;
        }



        return $detailsArr;
    }
    
    public function getAgentRemittancesFeeSTax($param){
        $bankUnicodeArr = Util::bankUnicodesArray();
        if(!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['3']; // default kotak
        }
        switch($param['bank_unicode'])
        {
            case $bankUnicodeArr['3']:
                $kotakRemitModel = new Remit_Kotak_Remittancerequest();
                $detailsArr = $kotakRemitModel->getAgentRemittancesFeeSTax($param);
                break;
            case $bankUnicodeArr['2']:
                $ratnakarRemitModel = new Remit_Ratnakar_Remittancerequest();
                $detailsArr = $ratnakarRemitModel->getAgentRemittancesFeeSTax($param);
                break;
            
        }
        
        return $detailsArr;      
    }
    
    public function getAgentRemittanceRefundsFeeSTax($param){
        $bankUnicodeArr = Util::bankUnicodesArray();
        if(!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['3']; // default kotak
        }
        switch($param['bank_unicode'])
        {
            case $bankUnicodeArr['3']:
                $kotakRemitModel = new Remit_Kotak_Remittancerequest();
                $detailsArr = $kotakRemitModel->getAgentRemittanceRefundsFeeSTax($param);
                break;
            case $bankUnicodeArr['2']:
                $ratnakarRemitModel = new Remit_Ratnakar_Remittancerequest();
                $detailsArr = $ratnakarRemitModel->getAgentRemittanceRefundsFeeSTax($param);
                break;
            
        }
        
        return $detailsArr;      
    }
    
    
    
     
   /* getBeneficiaryException() will return the Beneficiary exception details
     */
     public function getBeneficiaryException($param){
        /*
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'".$decryptionKey."') as bank_account_number"); 
        $toDate = isset($param['to'])?$param['to']:'';
        $fromDate = isset($param['from'])?$param['from']:'';
        
        $select = $this->_db->select()    
                ->from(DbTable::TABLE_REMITTANCE_REQUEST." as rr", array('sum(rr.amount) as total_amount', 'count(rr.id) as total_count', 'rr.date_created'))              
                ->joinLeft(DbTable::TABLE_REMITTERS." as r", "rr.remitter_id = r.id" , array('r.name as remitter_name', 'r.mobile as remitter_mobile_number', 'r.email as remitter_email'))
                ->joinLeft(DbTable::TABLE_BENEFICIARIES." as b", "rr.beneficiary_id =b.id", array('b.name as beneficiary_name', $bankAccountNumber, 'b.bank_name', 'b.ifsc_code', 'b.by_agent_id'))
                ->group('rr.beneficiary_id')
                ->group('DATE(rr.date_created)')
                ->where("DATE(rr.date_created) BETWEEN '".$fromDate."' AND '".$toDate."'")
                ->where("rr.status = '".STATUS_SUCCESS."' OR rr.status = '".STATUS_IN_PROCESS."' OR rr.status = '".STATUS_PROCESSED."'");
        //echo $select->__toString();//exit;
        $rows = $this->_db->fetchAll($select);      
     
        $rsCount = count($rows);
         * 
         */
        $retData = array();
        
        /*
        $i = 0;
        
        if($rsCount > 0)
        {
            foreach($rows as $val)
            {
                $retData[$i]['date_created'] = $val['date_created'];
                $retData[$i]['total_amount'] = $val['total_amount'];
                $retData[$i]['total_count'] = $val['total_count'];
                $retData[$i]['remitter_name'] = $val['remitter_name'];
                $retData[$i]['remitter_mobile_number'] = $val['remitter_mobile_number'];
                $retData[$i]['remitter_email'] = $val['remitter_email'];
                $retData[$i]['beneficiary_name'] = $val['beneficiary_name'];
                $retData[$i]['bank_account_number'] = $val['bank_account_number'];

                $agentUser = new AgentUser();
                $usertype = $agentUser->getAgentDetailsById($val['by_agent_id']);
                $agentType = $agentUser->getAgentCodeName($usertype['user_type'], $val['by_agent_id']);

                $retData[$i] = array_merge($retData[$i], $agentType);
                
                $retData[$i]['agent_name'] = $usertype['first_name'].' '.$usertype['last_name'];
                $retData[$i]['agent_code'] = $usertype['agent_code'];                
                $retData[$i]['bank_name'] = $val['bank_name'];
                $retData[$i]['ifsc_code'] = $val['ifsc_code'];
                
                
                $i++;
            }
        }
         * 
         */

        return $retData;
    }
    
    public function getRemittancefeeboi($param){
        $to = $param['to'];
        $from = $param['from']; 
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : ''; 
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                DbTable::TABLE_REMITTANCE_REQUEST . " as r", 
                    array(
                        'r.fee', 'r.service_tax', 'r.amount','DATE_FORMAT(r.date_created,"%d-%m-%Y") as txn_date','DATE(r.date_created) as date_created','r.status','r.txn_code','r.batch_name'
                ));
        $select->joinLeft(
                DbTable::TABLE_AGENTS . " as a", "r.agent_id = a.id ", 
                    array(
                        'a.agent_code', 'concat(a.first_name," ",a.last_name) as name', 'a.user_type as agent_user_type'
                ));
        $select->joinLeft(
                DbTable::TABLE_AGENT_DETAILS . " as ad", "a.id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", 
                    array(
                        'ad.estab_city', 'ad.estab_pincode'
                ));
        $select->joinLeft(
                DbTable::TABLE_REMITTANCE_REFUND." as ref", "ref.remittance_request_id = r.id" , 
                    array(
                        'ref.txn_code as refund_txn_code'
                ));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel", "a.id = orel.to_object_id" ,array());
        $select->joinLeft(
                DbTable::TABLE_AGENTS." as dis", "dis.id = orel.from_object_id" , 
                    array(
                        'dis.agent_code AS dist_code',' concat(dis.first_name," ",dis.last_name) as dist_name',
                ));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel_sdis", "dis.id = orel_sdis.to_object_id" ,array());
        $select->joinLeft(
                DbTable::TABLE_AGENTS." as sdis", "sdis.id = orel_sdis.from_object_id" , 
                    array(
                        'sdis.agent_code AS sup_dist_code' ,'concat(sdis.first_name," ",sdis.last_name) as sup_dist_name'
                ));
        $select->where(
                "r.status = '" . STATUS_IN_PROCESS . "' OR r.status = '" . STATUS_PROCESSED . "'"
                . " OR r.status =  '" . STATUS_SUCCESS . "' OR r.status = '" . STATUS_REFUND . "' "
                . "OR r.status = '" . FLAG_FAILURE . "'"
                );
        $select->where("r.date_created >= ?", $from);
        $select->where("r.date_created <= ?", $to); 
        if ($agentId != '') {
            $select->where('r.agent_id=?', $agentId);
        }
        //echo $select; exit();
        $row = $this->fetchAll($select); 
        return $row; 	 
    }
    
    public function getRemitRefundfeeAll($param){
        $to = $param['to'];
        $from = $param['from'];
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : ''; 
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                DbTable::TABLE_REMITTANCE_REFUND . " as rr", 
                    array(
                        'rr.fee', 'rr.service_tax', 'rr.amount','DATE_FORMAT(rr.date_created,"%d-%m-%Y") as txn_date','DATE(rr.date_created) as date_created','rr.txn_code as refund_txn_code', 'rr.reversal_service_tax', 'rr.reversal_fee'
                ));
        $select->joinLeft(
                DbTable::TABLE_REMITTANCE_REQUEST . " as req", "req.id = rr.remittance_request_id ", 
                    array(
                        'req.txn_code', 'req.status'
                ));
        $select->joinLeft(
                DbTable::TABLE_AGENTS . " as a", "rr.agent_id = a.id ", 
                    array(
                        'a.agent_code', 'concat(a.first_name," ",a.last_name) as name', 'a.user_type as agent_user_type'
                ));
        $select->joinLeft(
                DbTable::TABLE_AGENT_DETAILS . " as ad", "a.id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", 
                    array(
                        'ad.estab_city', 'ad.estab_pincode'
                ));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel", "a.id = orel.to_object_id" ,array());
        $select->joinLeft(
                DbTable::TABLE_AGENTS." as dis", "dis.id = orel.from_object_id" , 
                    array(
                        'dis.agent_code AS dist_code',' concat(dis.first_name," ",dis.last_name) as dist_name',
                ));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel_sdis", "dis.id = orel_sdis.to_object_id" ,array());
        $select->joinLeft(
                DbTable::TABLE_AGENTS." as sdis", "sdis.id = orel_sdis.from_object_id" , 
                    array(
                        'sdis.agent_code AS sup_dist_code' ,'concat(sdis.first_name," ",sdis.last_name) as sup_dist_name'
                ));    
        
        $select->where("rr.status = ? ", STATUS_SUCCESS);
        $select->where("r.date_created >= ?", $from);
        $select->where("r.date_created <= ?", $to);  
        if ($agentId != '') {
            $select->where('rr.agent_id=?', $agentId);
        }
        //echo $select; exit();
        $row = $this->fetchAll($select); 
        return $row; 
    }
    
    /*
     * getRemitRemittancefee() gets remittance fee for BOI bank. This is called from Operation portal. Please do not modify this function
     */
    public function getRemitRemittancefee($param){
        $to = $param['to'];
        $from = $param['from']; 
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : ''; 
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                DbTable::TABLE_REMITTANCE_REQUEST . " as r", 
                    array(
                        'r.fee as fee_amount', 'r.service_tax as service_tax_amount', 'r.amount as transaction_amount','DATE_FORMAT(r.date_created,"%d-%m-%Y") as txn_date','DATE(r.date_created) as date_created','r.status as txn_status','r.txn_code', new Zend_Db_Expr("'".TXNTYPE_REMITTANCE_FEE."' as transaction_type_name"), new Zend_Db_Expr("0 as reversal_fee"), new Zend_Db_Expr("0 as reversal_service_tax"), new Zend_Db_Expr("'' as utr")
                ));
        $select->joinLeft(
                DbTable::TABLE_AGENTS . " as a", "a.id = r.agent_id", 
                    array(
                        'a.agent_code', 'concat(a.first_name," ",a.last_name) as name', 'a.user_type as agent_user_type'
                ));
        $select->joinLeft(
                DbTable::TABLE_AGENT_DETAILS . " as ad", "ad.agent_id = a.id AND ad.status = '" . STATUS_ACTIVE . "'", 
                    array(
                        'ad.estab_city', 'ad.estab_pincode'
                ));
        $select->joinLeft(
                DbTable::TABLE_REMITTANCE_REFUND." as ref", "ref.remittance_request_id = r.id" , 
                    array(
                        'ref.txn_code as refund_txn_code'
                ));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel", "orel.to_object_id = a.id" ,array());
        $select->joinLeft(
                DbTable::TABLE_AGENTS." as dis", "dis.id = orel.from_object_id" , 
                    array(
                        'dis.agent_code AS dist_code',' concat(dis.first_name," ",dis.last_name) as dist_name',
                ));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel_sdis", "dis.id = orel_sdis.to_object_id" ,array());
        $select->joinLeft(
                DbTable::TABLE_AGENTS." as sdis", "sdis.id = orel_sdis.from_object_id" , 
                    array(
                        'sdis.agent_code AS sup_dist_code' ,'concat(sdis.first_name," ",sdis.last_name) as sup_dist_name'
                ));
        $select->where(
                "r.status = '" . STATUS_IN_PROCESS . "' OR r.status = '" . STATUS_PROCESSED . "'"
                . " OR r.status =  '" . STATUS_SUCCESS . "' OR r.status = '" . STATUS_REFUND . "' "
                . "OR r.status = '" . FLAG_FAILURE . "'"
                );
        $select->where("r.date_created >= ?", $from);
        $select->where("r.date_created <= ?", $to); 
        if ($agentId != '') {
            $select->where('r.agent_id=?', $agentId);
        }

        return $this->fetchAll($select); 
    }
    
    /*
     *  getRemittanceRefundfee() gets refund fee for BOI bank. This is called from Operation portal. Please do not modify this function
     */
    public function getRemittanceRefundfee($param){
        $to = $param['to'];
        $from = $param['from'];
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : ''; 
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                DbTable::TABLE_REMITTANCE_REFUND . " as rr", 
                    array(
                        'rr.fee as fee_amount', 'rr.service_tax as service_tax_amount', 'rr.amount as transaction_amount','DATE_FORMAT(rr.date_created,"%d-%m-%Y") as txn_date','DATE(rr.date_created) as date_created','rr.txn_code as refund_txn_code', 'rr.reversal_service_tax', 'rr.reversal_fee', new Zend_Db_Expr("'".TXNTYPE_REMITTANCE_REFUND_FEE."' as transaction_type_name"), new Zend_Db_Expr("'' as utr")
                ));
        $select->joinLeft(
                DbTable::TABLE_REMITTANCE_REQUEST . " as req", "req.id = rr.remittance_request_id ", 
                    array(
                        'req.txn_code', 'req.status as txn_status'
                ));
        $select->joinLeft(
                DbTable::TABLE_AGENTS . " as a", "rr.agent_id = a.id ", 
                    array(
                        'a.agent_code', 'concat(a.first_name," ",a.last_name) as name', 'a.user_type as agent_user_type'
                ));
        $select->joinLeft(
                DbTable::TABLE_AGENT_DETAILS . " as ad", "a.id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", 
                    array(
                        'ad.estab_city', 'ad.estab_pincode'
                ));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel", "a.id = orel.to_object_id" ,array());
        $select->joinLeft(
                DbTable::TABLE_AGENTS." as dis", "dis.id = orel.from_object_id" , 
                    array(
                        'dis.agent_code AS dist_code',' concat(dis.first_name," ",dis.last_name) as dist_name',
                ));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel_sdis", "dis.id = orel_sdis.to_object_id" ,array());
        $select->joinLeft(
                DbTable::TABLE_AGENTS." as sdis", "sdis.id = orel_sdis.from_object_id" , 
                    array(
                        'sdis.agent_code AS sup_dist_code' ,'concat(sdis.first_name," ",sdis.last_name) as sup_dist_name'
                ));    
        
        $select->where("rr.status = ? ", STATUS_SUCCESS);
        $select->where("rr.date_created >= ?", $from);
        $select->where("rr.date_created <= ?", $to);  
        if ($agentId != '') {
            $select->where('rr.agent_id=?', $agentId);
        }

        return $this->fetchAll($select);  
    }
    
    /*
     * . This is called from Operation portal. Please do not modify this function
     */
    public function getRemittancefeeOps($param) {
        $detailsArr = array();
        $bankUnicodeArr = Util::bankUnicodesArray();
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1'];
        }

        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $objKotakRemitRequest = new Remit_Kotak_Remittancerequest();
                $detailsArr = $objKotakRemitRequest->getRemitRemittancefee($param);
                break;
            case $bankUnicodeArr['2']:
                $objRatnakarRemitRequest = new Remit_Ratnakar_Remittancerequest();
                $detailsArr = $objRatnakarRemitRequest->getRemitRemittancefee($param);
                break;
            case $bankUnicodeArr['1']:
                $detailsArr = $this->getRemitRemittancefee($param); 
        } 
        return $detailsArr;
    }
    
     /*   Get remitter refund fee for an agent on a particular date for a product. This is called from Operation portal. Please do not modify this function
     */
    
     public function getRemitRefundfeeOps($param) {
        $detailsArr = array(); 
        $bankUnicodeArr = Util::bankUnicodesArray();

        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1'];
        }

        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $objKotakRemitRequest = new Remit_Kotak_Remittancerequest();
                $detailsArr = $objKotakRemitRequest->getRemittanceRefundfee($param);
                break;
            case $bankUnicodeArr['2']:
                $objRatnakarRemitRequest = new Remit_Ratnakar_Remittancerequest();
                $detailsArr = $objRatnakarRemitRequest->getRemittanceRefundfee($param);
                break;
            case $bankUnicodeArr['1']:
                $detailsArr = $this->getRemittanceRefundfee($param); 
                break;
        }

        return $detailsArr;
    }
}
