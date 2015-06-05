<?php

/*
 * Kotak Remittance
 */

class Remit_Kotak_Remittancerequest extends Remit_Kotak {

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
    protected $_name = DbTable::TABLE_KOTAK_REMITTANCE_REQUEST;

public function getCommissionTransactions($param) {
    	$date = $param['date'];
        $mobileno = isset($param['mobile_no']) ? $param['mobile_no'] : '';
        $txnno = isset($param['txn_no']) ? $param['txn_no'] : 0;
    	$agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
	$productId = isset($param['product_id']) ? $param['product_id'] : '';
    	$decryptionKey = App_DI_Container::get('DbConfig')->key;
    	$bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'" . $decryptionKey . "') as ben_account_number");
    	 
    	$select = $this->select();
    	$select->from(DbTable::TABLE_TXN_AGENT . " as txn", array('txn.amount', 'txn.txn_type'));
    	$select->setIntegrityCheck(false);
    	$select->joinLeft(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST." as kot", "txn.kotak_remittance_request_id = kot.id" , array('kot.txn_code', 'kot.status as txn_status', 'kot.date_created as txn_date','kot.txnrefnum','kot.final_response',));
				    
    	$select->joinLeft(DbTable::TABLE_KOTAK_REMITTERS . " as rem", "rem.id = txn.kotak_remitter_id", array('rem.mobile as mobile_number', 'concat(rem.name," ",rem.last_name) as remit_name','rem.email as remitter_email', 'rem.date_created as remit_regn_date')); 	
    	$select->joinLeft(DbTable::TABLE_KOTAK_BENEFICIARIES . " as b", "kot.beneficiary_id = b.id ", array('b.name as bene_name', 'b.bank_name as bene_bankname', 'b.ifsc_code as bene_ifsccode',$bankAccountNumber));
    
    	$select->joinLeft(DbTable::TABLE_AGENTS . " as a", "txn.agent_id = a.id ", array('a.id as agent_id', 'a.agent_code', 'concat(a.first_name," ",a.last_name) as agent_name','a.mobile1 as agent_mobile','a.email as agent_email', 'a.user_type as agent_user_type'));
    
    	$select->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "txn.agent_id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", array('ad.estab_city', 'ad.estab_pincode', 'ad.bank_ifsc_code as agent_ifsc_code', 'ad.bank_account_number as agent_bank_account_number')); 
	$select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel", "a.id = orel.to_object_id" ,array( ));
        $select->joinLeft(DbTable::TABLE_AGENTS." as dis", "dis.id = orel.from_object_id" ,array('dis.agent_code AS dist_code',' concat(dis.first_name," ",dis.last_name) as dist_name',));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel_sdis", "dis.id = orel_sdis.to_object_id" ,array());
        $select->joinLeft(DbTable::TABLE_AGENTS." as sdis", "sdis.id = orel_sdis.from_object_id" ,array('sdis.agent_code AS sup_dist_code' ,'concat(sdis.first_name," ",sdis.last_name) as sup_dist_name'));
    	if ($agentId > 0){
    		$select->where("txn.agent_id = ? ", $agentId);
    		$select->where("date(kot.date_created) = ?", $date);

    	}else{
        
    	            $datefrom = $param['date_from'];
                        
                        if(!empty($datefrom)){
                        	$select->where("kot.date_created >= ?", $datefrom);
                        	$select->where("kot.date_created <= ?", $date);
                        }
	}
         if ($mobileno != '')
              $select->where("rem.mobile = ? ", $mobileno); 
	 
	 if($productId !='')
	      $select->where("txn.product_id = ? ", $productId); 
	 
             if ($txnno > 0)
              $select->where("kot.txn_code = ? ", $txnno);
    	$select->where("txn.txn_type IN('COMM','RCOM')");
//		$select->where("date(kot.date_created) = ?", $date);
    	    		
    	$row = $this->fetchAll($select);
    
    	$retData = $row->toArray();

    	$totalData = count($retData);
    	
    	for ($j = 0; $j < $totalData; $j++) {
    		$retData[$j]['txn_date'] = Util::returnDateFormatted($retData[$j]['txn_date'], "Y-m-d", "d-m-Y", "-");
    	}

    	return $retData;
    }


    /*
     * get remitters successful remittance for the product for the duration
     */

    public function getTxnRemitterProductDuration($remitterId, $productId, $startDate, $endDate) {
        $select = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST, array('sum(amount) as total'))
                ->where('remitter_id=?', $remitterId)
                ->where('product_id=?', $productId)
                ->where("DATE(date_created) BETWEEN '" . $startDate . "' AND '" . $endDate . "'")
                ->where("status = '" . FLAG_SUCCESS . "' OR status = '" . STATUS_IN_PROCESS . "' OR status = '" . STATUS_HOLD . "'")
                ->group("remitter_id");
        //echo $select->__toString();
        $row = $this->_db->fetchRow($select);
        return $row;
    }
    
    /*
     * get remitters successful remittance for the product for the duration
     */

    public function getValidateTxnRemitterProductDuration($remitterId, $productId, $startDate, $endDate) {
        $select = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST, array('sum(amount) as total'))
                ->where('remitter_id=?', $remitterId)
                ->where('product_id=?', $productId)
                ->where("DATE(date_created) BETWEEN '" . $startDate . "' AND '" . $endDate . "'")
                ->where("status = '" . FLAG_SUCCESS . "' OR status = '" . STATUS_IN_PROCESS . "' OR status = '" . STATUS_HOLD . "'  OR status = '" . STATUS_INCOMPLETE . "'")
                ->group("remitter_id");
        //echo $select->__toString();
        $row = $this->_db->fetchRow($select);
        return $row;
    }

    /*
     * chks remit limits
     */

    public function chkAllowRemit($params) {
        $baseTxn = new BaseTxn();
        return $baseTxn->chkAllowRemit($params);
    }

    /*
     * initiates remittance txns
     */

    public function initiateRemit($params) {
        $baseTxn = new BaseTxn();
        return $baseTxn->initiateRemit($params);
    }

    /*
     * Add remittance request
     */

    public function updateReq($reqId = 0, $params = array()) {
        if ($reqId == 0 || empty($params))
	    throw new Exception(ErrorCodes::ERROR_EDIGITAL_REMITTANCE_REQ_DATA_MISSING_MSG,ErrorCodes::ERROR_EDIGITAL_REMITTANCE_REQ_DATA_MISSING_CODE);

        $this->update($params, "id='$reqId'");
        return true;
    }

    /* getRemitterRefund() will return the refund details of remitter
     */

    public function getRemitterRefundCount($remitterId = 0) {
        if ($remitterId ==0)
            throw new Exception('Remitter Id not found');

        $select = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as rr", array('count(*) as count_refund_requests'))
                ->joinLeft(DbTable::TABLE_KOTAK_BENEFICIARIES . " as b", "rr.beneficiary_id =b.id and b.status = '" . STATUS_ACTIVE . "'", array(''))
                ->where('rr.remitter_id=?', $remitterId)
                ->where("rr.status = ?", FLAG_FAILURE);
        //echo $select->__toString();
        $row = $this->_db->fetchRow($select);
        return $row;
    }

    /*
     * addRemittanceRefund() will make entry remittance refund table
     */

    public function addRemittanceRefund($data) {

        if (empty($data))
	    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_REMIT_REFUND_MSG,ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_REMIT_REFUND_CODE);

        $add = $this->_db->insert(DbTable::TABLE_KOTAK_REMITTANCE_REFUND, $data);

        return $add;
    }

    /* getRemitterRequestsInfo() will return the remitters requests info
     * As param it will expect the remitter request id
     */

    public function getRemitterRequestsInfo($remitterRequestId = 0) {
        if ($remitterRequestId == 0)
            throw new Exception('Remitter Request id not found!');

        $select = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as krt", array('id','product_id', 'beneficiary_id', 'amount', 'remitter_id', 'txn_code', 'fee', 'service_tax','agent_id','status'))
                ->joinLeft(DbTable::TABLE_KOTAK_REMITTERS . ' as kr', "krt.remitter_id = kr.id",array('id as r_id','mobile', 'email','name as r_name'))
                ->joinLeft(DbTable::TABLE_KOTAK_BENEFICIARIES . " as kb", "krt.beneficiary_id = kb.id", array('id as b_id','name','nick_name'))
                ->where("krt.id = ?", $remitterRequestId);
//        echo $select->__toString();exit;
        $row = $this->_db->fetchRow($select);

        return $row;
    }

    /* getRemitterRequestsForNEFT() will return the remitters requests for neft for cron / for failed txns
     */

    public function getAgentRemittanceRequests($status = STATUS_IN_PROCESS, $remitterId = 0, $remitReqId = 0, $limit = '') {
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'" . $decryptionKey . "') as `bank_account_number`");
        $branchAddress = new Zend_Db_Expr("AES_DECRYPT(`b`.`branch_address`,'" . $decryptionKey . "') as branch_address");
        $mobile = new Zend_Db_Expr("AES_DECRYPT(`b`.`mobile`,'" . $decryptionKey . "') as mobile");
        $curdate = date("Y-m-d");
        $select = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as rr", array('id as remittance_request_id', 'remitter_id', 'beneficiary_id', 'agent_id', 'ops_id', 'amount', 'date_created', 'sender_msg', 'product_id', 'fee', 'service_tax','txn_code','final_response'))
                ->joinLeft(DbTable::TABLE_KOTAK_BENEFICIARIES . " as b", "rr.beneficiary_id =b.id and b.status = '" . STATUS_ACTIVE . "'", array('b.name as beneficiary_name', 'nick_name', 'ifsc_code', $bankAccountNumber, 'branch_name', 'branch_city', $branchAddress, 'bank_account_type', $mobile, 'bank_name'));


        if ($remitterId > 0) {
            $select->join(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION . " as bapc", "bapc.product_id=rr.product_id AND bapc.agent_id = rr.agent_id AND '" . $curdate . "' >= bapc.date_start AND ('" . $curdate . "' <= bapc.date_end OR bapc.date_end = '0000-00-00')", array(''));
            $select->where("rr.remitter_id = ?", $remitterId);
        }
        if ($remitReqId > 0) {
            $select->where("rr.id = ?", $remitReqId);
        }
        $select->where("rr.status = ?", $status);
        if ($limit != '' && $limit > 0)
            $select->limit($limit);
        $rows = $this->_db->fetchAll($select);

        return $rows;
    }

    /* getRemitRequestOnDateBasis() will return remit request for 'in_process','processed','success','failure','refund' status 
     */

    public function getRemitRequestOnDateBasis($param) {

        $retData = array();
        $retNewData = array();
        $objCommission = new CommissionReport();
        
        if (!empty($param)) {

            $param['check_fee'] = false;
            $retData = $this->getRemittancefee($param);
            $totalRemitFee = count($retData);

            if ($totalRemitFee >= 1) {
                $retData = $retData->toArray();
                $totalData = count($retData);

                $k = 0;
                $alterData = array();
                for ($j = 0; $j < $totalData; $j++) {

                    // adding transaction type field
                    $alterData = $retData[$j];
                    $alterData['txn_type'] = TXNTYPE_REMITTANCE;
                    $alterData['crn'] = '';
                    $alterData['txn_date'] = Util::returnDateFormatted($retData[$j]['txn_date'], "Y-m-d", "d-m-Y", "-");
                    $alterData['agent_name'] = $retData[$j]['name'];
                    
                    $agentUser = new AgentUser();
                    $agentType = $agentUser->getAgentCodeName($retData[$j]['agent_user_type'], $retData[$j]['agent_id']);

                    $refund_txn = $this->getRefundTxnRefNo($retData[$j]['rmid']);
                    
                    if(!empty($refund_txn))
                    {
                        $alterData['refund_txn_code'] = $refund_txn['refund_txn_code'];
                    }
                    else
                    {
                        $alterData['refund_txn_code'] = '';
                    }

                    if(!empty($agentType))
                    {
                        $alterData = array_merge($alterData, $agentType);
                    }
                    
                    // getting to and from date
                    $queryParam['dateTo'] = $retData[$j]['date_created'];
                    $queryParam['dateFrom'] = $retData[$j]['date_created'];
                    
                    // getting agent id on queryParam array for query
                        $queryParam['agentId'] = $retData[$j]['agent_id'];
                        $queryParam['bank_unicode'] = $param['bank_unicode'];

                        /** ** getting agent loads/reloads transaction and commission total *** */
                        $loadTxnTypesParam = array(
                            '0' => TXNTYPE_FIRST_LOAD,
                            '1' => TXNTYPE_CARD_RELOAD
                        );
                        $respLoad = $objCommission->getAgentCommission($queryParam, $loadTxnTypesParam);

                        $agentLoadReload = array(
                            'total_agent_load_reload_amount' => $respLoad['total_agent_transaction_amount'],
                            'total_agent_load_reload_comm' => $respLoad['total_agent_commission'],
                            'plan_commission_name' => $respLoad['plan_commission_name'],
                            'transaction_fee' => $respLoad['transaction_fee'],
                            'commission_amount' => $respLoad['commission_amount'],
                            'transaction_service_tax' => $respLoad['transaction_service_tax']
                        );
                        if (!empty($agentLoadReload)) {
                            $alterData = array_merge($alterData, $agentLoadReload);
                        }
                        /*                         * ** getting agent loads/reloads transaction and commission total over here *** */


                        /*                         * ** getting agent remit all actions total like (remitter registration, remittance, refund txn amount total & their commission amount total) *** */
                        $remitTxnTypesParam = array(
                            '0' => TXNTYPE_REMITTER_REGISTRATION,
                            '1' => TXNTYPE_REMITTANCE_FEE,
                            '2' => TXNTYPE_REMITTANCE_REFUND_FEE
                        );
                        $respRemit = $objCommission->getAgentCommission($queryParam, $remitTxnTypesParam);
                        $agentRemit = array(
                            'total_agent_remit_amount' => $respRemit['total_agent_transaction_amount'],
                            'total_agent_remit_comm' => $respRemit['total_agent_commission'],
                            
                        );
                        if(!empty($respRemit['plan_commission_name'])){
                            $agentRemit['plan_commission_name'] = $respRemit['plan_commission_name'];
                        }
                        if(!empty($respRemit['transaction_fee'])){
                            $agentRemit['transaction_fee'] = $respRemit['transaction_fee'];
                        }
                        
                        if(!empty($respRemit['commission_amount'])){
                            $agentRemit['commission_amount'] = $respRemit['commission_amount'];
                        }
                        
                        if(!empty($respRemit['transaction_service_tax'])){
                            $agentRemit['transaction_service_tax'] = $respRemit['transaction_service_tax'];
                        }
                        
                        if (!empty($agentRemit)) {
                            $alterData = array_merge($alterData, $agentRemit);
                        }
                        
                    // recreating array with adding new records for service tax and fee 
                    $retNewData[$k] = $alterData;
                    $k++;
                    $retNewData[$k] = $alterData;
                    $retNewData[$k]['amount'] = $retData[$j]['service_tax'];
                    $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_SERVICE_TAX;
                    $retNewData[$k]['txn_code'] = $retData[$j]['txn_code'];
                    $retNewData[$k]['batch_name'] = '';
                    $k++;
                    $retNewData[$k] = $alterData;
                    $retNewData[$k]['amount'] = $retData[$j]['fee'];
                    $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_FEE;
                    $retNewData[$k]['txn_code'] = $retData[$j]['txn_code'];
                    $retNewData[$k]['batch_name'] = '';
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
        $date = $param['date'];
        $datefrom = $param['date_from'];
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
        $checkRegFee = isset($param['check_fee']) ? $param['check_fee'] : true;
        $mobileno = isset($param['mobile_no']) ? $param['mobile_no'] : '';
	$productId = isset($param['product_id']) ? $param['product_id'] : '';
        $txnno = isset($param['txn_no']) ? $param['txn_no'] : 0;
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
    	$bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'" . $decryptionKey . "') as ben_account_number");
        $select = $this->select();
        $select->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as r", array('r.fee', 'r.service_tax', 'r.amount', 'r.id AS rmid', 'r.product_id', 'DATE(r.date_created) as date_created', 'r.date_created as txn_date', 'r.txn_code', 'r.status as txn_status', 'r.final_response','r.txnrefnum'));
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_KOTAK_REMITTERS . " as rem", "rem.id = r.remitter_id", array('rem.mobile as mobile_number', 'concat(rem.name," ",rem.last_name) as remit_name','rem.email as remitter_email', 'rem.date_created as remit_regn_date'));
        $select->joinLeft(DbTable::TABLE_KOTAK_BENEFICIARIES . " as b", "r.beneficiary_id = b.id ", array('b.name as bene_name', 'b.bank_name as bene_bankname', 'b.ifsc_code as bene_ifsccode',$bankAccountNumber));
        $select->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "r.product_id = p.id ", array('p.ecs_product_code','p.unicode as pro_unicode'));
        $select->joinLeft(DbTable::TABLE_AGENTS . " as a", "r.agent_id = a.id ", array('a.id as agent_id', 'a.agent_code', 'concat(a.first_name," ",a.last_name) as name','a.mobile1 as agent_mobile','a.email as agent_email', 'a.user_type as agent_user_type'));
        $select->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "a.id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", array('ad.estab_city', 'ad.estab_pincode', 'ad.bank_ifsc_code as agent_ifsc_code', 'ad.bank_account_number as agent_bank_account_number'));
        $select->where("r.status = '" . STATUS_IN_PROCESS . "' OR r.status = '" . STATUS_HOLD . "' OR r.status =  '" . STATUS_SUCCESS . "' OR r.status = '" . STATUS_REFUND . "' OR r.status = '" . FLAG_FAILURE . "'");
        if ($checkRegFee) {
            $select->where("r.fee > ? ", 0);
        }

        if ($agentId > 0)
            $select->where("r.agent_id = ? ", $agentId);
        
        if ($mobileno != '')
            $select->where("rem.mobile = ? ", $mobileno);
        
	if($productId != '')
	    $select->where("r.product_id = ? ", $productId);
	
        if ($txnno > 0)
            $select->where("r.txn_code = ? ", $txnno);
        if(!empty($datefrom)){
            $select->where("r.date_created >= ?", $datefrom);
            $select->where("r.date_created <= ?", $date);
        } else if(!empty($date)) {
            $select->where("DATE(r.date_created) = ?", $date);
        }
        $row = $this->fetchAll($select);
        
        return $row;
    }

    /* get Refund/Reversed Transaction Reference No for a Remittance Request */
    public function getRefundTxnRefNo($rmid)
    {

            $select = $this->_db->select();
            $select->from(DbTable::TABLE_KOTAK_REMITTANCE_REFUND , array('txn_code as refund_txn_code'));
            
            $select->where("remittance_request_id = ?", $rmid);

            return $this->_db->fetchRow($select);
    }
    
    /* getAgentTotalRemittance() will return the total agent remittance on date basis
     * Expected params:- date, agent id
     */

    public function getAgentTotalRemittanceFeeSTax($param, $status = array()) {
        $date = isset($param['date']) ? $param['date'] : '';
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
        $to = isset($param['to']) ? $param['to'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $statusWhere = '';

        if (!empty($status)) {
            foreach ($status as $statusVal) {
                if ($statusWhere != '') {
                    $statusWhere .= " OR ";
                }
                $statusWhere .= "rr.status='" . $statusVal . "'";
            }
        }
        
        //Enable DB Slave
        $this->_enableDbSlave();
        $select = $this->select();
        $select->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as rr", array('sum(rr.amount) as agent_total_remittance', 'sum(rr.fee) as agent_total_remittance_fee', 'sum(rr.service_tax) as agent_total_remittance_stax', 'count(rr.id) as agent_total_remittance_count'));
        $select->setIntegrityCheck(false);
        if ($statusWhere == '') {
            $select->where("rr.status = '" . STATUS_IN_PROCESS . "' OR rr.status = '" . STATUS_HOLD . "' OR rr.status =  '" . STATUS_SUCCESS . "' OR rr.status = '" . STATUS_REFUND . "' OR rr.status = '" . FLAG_FAILURE . "'");
        } else {
            $select->where($statusWhere);
        }
        if ($agentId > 0) {
            $select->where("rr.agent_id = ? ", $agentId);
        }
        if ($to != '' && $from != '') {
            $select->where("DATE(rr.date_created) BETWEEN '$from' AND '$to'");
        } else {
            $select->where("DATE(rr.date_created) = ?", $date);
        }
//        echo $select."<br/><br/>";
        $row = $this->fetchRow($select);
        //Disable DB Slave
        $this->_disableDbSlave();
        return $row;
    }
    
    
    public function getAgentAllRemittanceFeeSTax($param, $status = array()) {
        $date = isset($param['date']) ? $param['date'] : '';
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
        $to = isset($param['to']) ? $param['to'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $statusWhere = '';

        if (!empty($status)) {
            foreach ($status as $statusVal) {
                if ($statusWhere != '') {
                    $statusWhere .= " OR ";
                }
                $statusWhere .= "rr.status='" . $statusVal . "'";
            }
        }
        $select = $this->select();
        $select->from(
                    DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as rr", 
                    array(
                        'sum(rr.amount) as agent_total_remittance', 
                        'sum(rr.fee) as agent_total_remittance_fee', 
                        'sum(rr.service_tax) as agent_total_remittance_stax', 
                        'count(rr.id) as agent_total_remittance_count', 
                        'DATE_FORMAT(rr.date_created,"%d-%m-%Y") AS txn_date' 
                    ));
        $select->setIntegrityCheck(false);
        if ($statusWhere == '') {
            $select->where(
                    "rr.status = '" . STATUS_IN_PROCESS . "' OR rr.status = '" . STATUS_HOLD .
                    "' OR rr.status =  '" . STATUS_SUCCESS . "' OR rr.status = '" . STATUS_REFUND . 
                    "' OR rr.status = '" . FLAG_FAILURE . "'");
        } else {
            $select->where($statusWhere);
        }
        if ($agentId > 0) {
            $select->where("rr.agent_id = ? ", $agentId);
        }
        if ($to != '' && $from != '') {
            $select->where("DATE(rr.date_created) BETWEEN '$from' AND '$to'");
        } else {
            $select->where("DATE(rr.date_created) = ?", $date);
        }
        $select->group('DATE_FORMAT(rr.date_created, "%Y-%m-%d")'); 
//        echo $select."<br/><br/>";
        $row = $this->fetchAll($select);
        return $row;
    }
    
    
     public function getAgentAllRemittanceRefundSTax($param) {

        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';

        if ($from != '' && $to != '') {
            $select = $this->_db->select();
            $select->from(
                        DbTable::TABLE_KOTAK_REMITTANCE_REFUND . ' as rr', 
                        array(
                            'sum(rr.amount) as agent_total_remittance_refund', 
                            'sum(rr.service_tax) as agent_total_remittance_refund_stax', 
                            'count(rr.id) as agent_total_remittance_refund_count', 
                            'sum(rr.reversal_service_tax) as agent_total_reversal_refund_stax', 
                            'sum(rr.reversal_fee) as agent_total_reversal_refund_fee',
                            'DATE_FORMAT(rr.date_created,"%d-%m-%Y") AS txn_date' 
                        ));
            if ($agentId > 0){
                $select->where('rr.agent_id=?', $agentId);
            }
            $select->where("rr.status='" . FLAG_SUCCESS . "'"); 
            $select->where("DATE(rr.date_created) BETWEEN '". $from ."' AND '". $to ."'"); 
            $select->group('DATE_FORMAT(rr.date_created, "%Y-%m-%d")'); 
//            echo $select.'<br><br>'; //exit;
            return $this->_db->fetchAll($select);
        }
        else
            return '';
    }
    
    /*  getAgentTotalRemittanceRefundSTax() function is responsible fetch data for agent total remitter refund and Service Tax amount 
     *  as params it will accept agent id and transaction date
     */

    public function getAgentTotalRemittanceRefundSTax($param) {

        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $date = isset($param['date']) ? $param['date'] : '';

        if ($date != '') {
            //Enable DB Slave
            $this->_enableDbSlave();
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_KOTAK_REMITTANCE_REFUND . ' as rr', array('sum(rr.amount) as agent_total_remittance_refund', 'sum(rr.service_tax) as agent_total_remittance_refund_stax', 'count(rr.id) as agent_total_remittance_refund_count', 'sum(rr.reversal_service_tax) as agent_total_reversal_refund_stax', 'sum(rr.reversal_fee) as agent_total_reversal_refund_fee'));
            if ($agentId > 0){
                $select->where('rr.agent_id=?', $agentId);
            }
            $select->where("rr.status='" . FLAG_SUCCESS . "'");
            $select->where("DATE(rr.date_created) ='" . $date . "'");

//            echo $select.'<br><br>'; //exit;
            $row = $this->_db->fetchRow($select);
            //Disable DB Slave
            $this->_disableDbSlave();
            return $row;
        }
        else
            return '';
    }

    /* for cron
     * getAgentRemittanceDetails() will return remittance amt details for an agent on a particular date 
     * for a product for comm report purpose
     * As Params:- agent id, product id, query date
     */

    public function getAgentRemittanceDetails($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $productId = isset($param['product_id']) ? $param['product_id'] : '';
        $date = isset($param['date']) ? $param['date'] : '';
        $txnId = isset($param['txn_id']) ? $param['txn_id'] : '';


        $select = $this->select();
        $select->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as rr", array('rr.amount as transaction_amount', 'rr.fee as transaction_fee', 'rr.service_tax as transaction_service_tax', 'rr.txn_code as transaction_ref_no'));
        $select->setIntegrityCheck(false);

	if(!isset($param['txn_id'])){
        	$select->join(DbTable::TABLE_KOTAK_REMITTANCE_STATUS_LOG . " as rsl", "rr.id = rsl.remittance_request_id AND status_new = '" . STATUS_SUCCESS . "'", array('rsl.date_created as date_success'));
        	$select->where("rr.status =  '" . STATUS_SUCCESS . "'");
        	$select->where("DATE(rsl.date_created) = ?", $date);
	}
        if ($agentId > 0){
            $select->where('rr.agent_id =?', $agentId);
        }
        if ($productId > 0){
            $select->where('rr.product_id=?', $productId);
        }

	if($txnId > 0){
            $select->where('rr.id=?', $txnId);
        }

//         echo $select.'<br><br>';
        return $this->fetchAll($select);
    }

    /* getAgentRemittanceRefundDetails() will return remittance fee for an agent on a particular date for a product
     * As Params:- agent id, product id, query date
     */

    public function getAgentRemittanceRefundDetails($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $productId = isset($param['product_id']) ? $param['product_id'] : '';
        $date = isset($param['date']) ? $param['date'] : '';
        $refund_txn_id = isset($param['refund_txn_id']) ? $param['refund_txn_id'] : '';

        $select = $this->_db->select();
        $select->from(DbTable::TABLE_KOTAK_REMITTANCE_REFUND . " as rr", array('rr.amount as transaction_amount', 'rr.fee as transaction_fee', 'rr.service_tax as transaction_service_tax', 'rr.txn_code as transaction_ref_no'));
        $select->where("rr.status = '" . STATUS_SUCCESS . "'");
        $select->where("DATE(rr.date_created)='" . $date . "'");

        if ($agentId > 0) {
            $select->where('rr.agent_id =?', $agentId);
        }
        if ($productId > 0){
            $select->where('rr.product_id =?', $productId);
        }
	
	 if($refund_txn_id > 0){
            $select->where('rr.id=?', $refund_txn_id);
        }


        return $this->_db->fetchAll($select);
    }

    /*
     * get all in_process remit requests
     */

    public function getPendingRemitRequests($page = 1, $paginate = NULL, $force = FALSE) {
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`ben`.`bank_account_number`,'" . $decryptionKey . "') as ben_account_number");

        $select = $this->select();
        $select->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as r", array('r.id AS rmid', 'r.product_id', 'DATE(r.date_created) as date_created', 'r.amount', 'r.txn_code'));
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_KOTAK_REMITTERS . " as rem", "rem.id = r.remitter_id", array('rem.mobile as mobile_number', 'rem.name as rem_name'));
        $select->joinLeft(DbTable::TABLE_KOTAK_BENEFICIARIES . " as ben", "r.beneficiary_id = ben.id", array('ben.id as ben_id', 'ben.name as ben_name', 'concat(ben.address_line1,", ",ben.address_line2) as ben_address', $bankAccountNumber));
        $select->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "r.product_id = p.id ", array('p.ecs_product_code'));
        $select->joinLeft(DbTable::TABLE_BANK . " as b", "p.bank_id = b.id ", array('b.name as bank_name'));
        $select->where("r.status = '" . STATUS_IN_PROCESS . "'");
        $select->order('date_created');
        return $this->_paginate($select, $page, $paginate);
    }

    /*
     * Update selected NEFT requests
     */

    public function updateRemitRequests($data) {

        $user = Zend_Auth::getInstance()->getIdentity();
        //$dateFormat = date("dmyHis", time());
        $dateFormat = Util::getNeftBatchFileName();
        $batchName = REMIT_BATCH_NAME_PREFIX . $dateFormat;
        $objRemitStatusLog = new Remit_Remittancestatuslog();
        foreach ($data as $requestId) {
            echo $requestId;
            try {
                $remitReqData = array('status' => STATUS_PROCESSED,
                    'batch_name' => $batchName,
                    'ops_id' => $user->id,
                    'fund_holder' => REMIT_FUND_HOLDER_NEFT);
                $this->_db->update(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST, $remitReqData, "id = $requestId");

                $remitReqLog = array('remittance_request_id' => $requestId,
                    'status_old' => STATUS_IN_PROCESS, 'status_new' => STATUS_PROCESSED,
                    'by_ops_id' => $user->id, 'date_created' => new Zend_Db_Expr('NOW()'));
                $objRemitStatusLog->addStatus($remitReqLog);
            } catch (Zend_Exception $ze) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
            }
        }
    }

   
   
 

    public function getPendingSmsRecords() {

        $select = $this->_db->select();
        $select->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as r", array('r.id as remittance_request_id', 'remitter_id', 'beneficiary_id', 'agent_id', 'amount', 'status', 'status_sms'));
        $select->where("r.status_sms = '" . STATUS_PENDING . "'");
        $select->where("r.status = '" . FLAG_SUCCESS . "' || r.status = '" . FLAG_FAILURE . "'");
        return $this->_db->fetchAll($select);
    }

    /* getRemitterRemittanceRequests() will return the remitters requests for neft for cron / for failed txns
     */

    public function getRemitterRemittanceRequests($remitterId = 0) {
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $mobile = new Zend_Db_Expr("AES_DECRYPT(`b`.`mobile`,'" . $decryptionKey . "') as mobile");
        $email = new Zend_Db_Expr("AES_DECRYPT(`b`.`email`,'" . $decryptionKey . "') as email");
        $durationArr = Util::getDurationDates('month');
        $from = $durationArr['from'];
        $to = $durationArr['to'];
        $select = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as rr", array('id', 'remitter_id', 'beneficiary_id', 'amount', 'date_created'))
                ->joinLeft(DbTable::TABLE_KOTAK_BENEFICIARIES . " as b", "rr.beneficiary_id =b.id and b.status = '" . STATUS_ACTIVE . "'", array('b.name as beneficiary_name', $mobile, $email));
        $select->where("rr.remitter_id = ?", $remitterId);
        $select->where("rr.status = '" . FLAG_SUCCESS . "' OR rr.status = '" . STATUS_IN_PROCESS . "' OR rr.status = '" . STATUS_PROCESSED . "'");
        $select->where("rr.date_created BETWEEN '$from' AND '$to'");
        //echo $select->__toString();//exit;
        $rows = $this->_db->fetchAll($select);

        return $rows;
    }

    /* getRemitterRemittanceCount() will return the remitters requests count
     * 
     *   */

    public function getRemitterRemittanceCountandSum($remitterId = 0) {
        $durationArr = Util::getDurationDates('month');
        $from = $durationArr['from'];
        $to = $durationArr['to'];
        $select = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as rr", array('count(*) as count', 'sum(amount) as total'));
        $select->where("rr.remitter_id = ?", $remitterId);
        $select->where("rr.status = '" . FLAG_SUCCESS . "' OR rr.status = '" . STATUS_IN_PROCESS . "' OR rr.status = '" . STATUS_PROCESSED . "'");
        $select->where("rr.date_created BETWEEN '$from' AND '$to'");
        //echo $select->__toString();//exit;
        $rows = $this->_db->fetchRow($select);

        return $rows;
    }

    /* getRemitterRemittances() will return remitters remittances details
     * as params :- remitter id and txn date
     */

    public function getRemitterRemittances($param) {

        // $remitterId = isset($param['remitter_id'])?$param['remitter_id']:0; 
        $toDate = isset($param['to_date']) ? $param['to_date'] : '';
        $fromDate = isset($param['from_date']) ? $param['from_date'] : '';

        // if($toDate!='' && $fromDate!='' && $remitterId>=1){ 
        if ($toDate != '' && $fromDate != '') {
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as rr", array('rr.beneficiary_id', 'rr.amount', 'DATE(rr.date_created) as txn_date'));
            $select->joinLeft(DbTable::TABLE_KOTAK_REMITTERS . " as r", "rr.remitter_id = r.id", array('r.name as remitter_name', 'DATE(r.date_created) as remitter_reg_date', 'mobile'));
            $select->joinLeft(DbTable::TABLE_KOTAK_BENEFICIARIES . " as b", "rr.beneficiary_id = b.id", array('b.name as bene_name', 'b.bank_name as bene_bank_name', 'b.ifsc_code as bene_ifsc_code'));
            //  $select->where("rr.remitter_id = ?", $remitterId);
            $select->where("rr.status = '" . STATUS_SUCCESS . "' OR rr.status = '" . STATUS_IN_PROCESS . "' OR rr.status = '" . STATUS_HOLD . "'");
            $select->where("DATE(rr.date_created) BETWEEN '" . $fromDate . "' AND '" . $toDate . "'");
//            echo $select->__toString();exit;
//            echo '<pre>';
//            print_r($this->_db->fetchAll($select));
//            die;
            return $this->_db->fetchAll($select);
        }
        else
            return array();
    }

  
 
    /* getRemittanceRefunds() will return remittance refunds for particular from and to date
     * As Params:- query to and from date
     */

    public function getRemittanceRefunds($param) {
        $toDate = isset($param['to_date']) ? $param['to_date'] : '';
        $fromDate = isset($param['from_date']) ? $param['from_date'] : '';
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'" . $decryptionKey . "') as beneficiary_bank_account_number");
        $crn = new Zend_Db_Expr("'' as crn");
        $card_number = new Zend_Db_Expr("'' as card_number");
        
        if ($toDate != '' && $fromDate != '') {

            $select = $this->_db->select();
            $select->from(DbTable::TABLE_KOTAK_REMITTANCE_REFUND . " as rr", array('amount as refund_amount', 'txn_code', 'date_created as refund_date', 'reversal_fee', 'reversal_service_tax', 'status',$crn,$card_number));
            $select->joinLeft(DbTable::TABLE_KOTAK_REMITTERS . " as rem", "rr.remitter_id = rem.id", array('rem.name as remitter_name', 'rem.email as remitter_email', 'rem.mobile as remitter_mobile_number'));
            $select->joinLeft(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as rrq", "rr.remittance_request_id = rrq.id",array('final_response as remarks','txn_code as request_txn_code'));
            $select->joinLeft(DbTable::TABLE_KOTAK_BENEFICIARIES . " as b", "rrq.beneficiary_id = b.id", array('b.name as beneficiary_name', $bankAccountNumber));
             
            $select->joinLeft(DbTable::TABLE_AGENTS . " as a", "rr.agent_id = a.id ", array('a.agent_code', 'concat(a.first_name," ",a.last_name) as agent_name'));
            $select->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "a.id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", array('ad.estab_city', 'ad.estab_pincode', 'ad.estab_state'));
            
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
             
            $select->where("rr.status = '" . STATUS_SUCCESS . "'");
            $select->where("DATE(rr.date_created) BETWEEN '" . $fromDate . "' AND '" . $toDate . "'");  
            return $this->_db->fetchAll($select);
        }
        else
            return array();
    }
 
   
   
    /* getRemittanceException() will return the remittance exception details
     */

    public function getRemittanceException($param) {

        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'" . $decryptionKey . "') as bank_account_number");
        $toDate = isset($param['to']) ? $param['to'] : '';
        $fromDate = isset($param['from']) ? $param['from'] : '';
	$limit = isset($param['noofrecords']) ? $param['noofrecords'] : 0;

        $select = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as rr", array('sum(rr.amount) as total_amount', 'count(rr.id) as total_count', 'rr.date_created'))
                ->joinLeft(DbTable::TABLE_KOTAK_REMITTERS . " as r", "rr.remitter_id = r.id", array('r.name as remitter_name', 'r.mobile as remitter_mobile_number', 'r.email as remitter_email'))
                ->joinLeft(DbTable::TABLE_KOTAK_BENEFICIARIES . " as b", "rr.beneficiary_id =b.id", array('b.name as beneficiary_name', $bankAccountNumber, 'b.bank_name', 'b.ifsc_code', 'b.by_agent_id'))
                ->group('rr.beneficiary_id')
                ->group('DATE(rr.date_created)')
                ->where("DATE(rr.date_created) BETWEEN '" . $fromDate . "' AND '" . $toDate . "'")
                ->where("rr.status = '" . STATUS_SUCCESS . "' OR rr.status = '" . STATUS_IN_PROCESS . "' OR rr.status = '" . STATUS_HOLD . "'");
	if($limit > 0){
            $select->limit($limit);
        }
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

 

    /* getAgentRemittanceCountandSum() will return the agent's remittance requests count and sum
     * 
     *   */

    public function getAgentRemittanceCountandSum($param) {

        $from = $param['from'];
        $to = $param['to'];
        $agentId = $param['agentId'];
        $select = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as rr", array('count(*) as count', 'sum(amount) as total'));
        $select->where("rr.agent_id = ?", $agentId);
        $select->where("rr.date_created BETWEEN '$from' AND '$to'");
        $rows = $this->_db->fetchRow($select);

        return $rows;
    }

    /* getAgentRemittance() will return the agent's remittance
     * 
     *   */

    public function getAgentRemittance($param) {
        $from = $param['from'];
        $to = $param['to'];
        $agentId = $param['agentId'];
        $select = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as rr", array('amount', 'DATE(date_created) as date_created'));
        $select->where("rr.agent_id = ?", $agentId);
//        $select->where("rr.status = '".FLAG_SUCCESS."' OR rr.status = '".STATUS_IN_PROCESS."' OR rr.status = '".STATUS_PROCESSED."'");
        $select->where("rr.date_created BETWEEN '$from' AND '$to'");
//        echo $select->__toString();
        $rows = $this->_db->fetchAll($select);

        return $rows;
    }

    /* getRemitterRequestsInfoByTxnCode() will return the remitters requests info
     * As param it will expect the txn_code
     */

    public function getRemitterRequestsInfoByTxnCode($txn_code, $txnrefnum='', $beneid='') {
        

        $select = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as rr", array('id','product_id', 'beneficiary_id', 'amount', 'remitter_id', 'txn_code', 'fee', 'service_tax','agent_id','status', 'sender_msg'))
                ->where("rr.txn_code = ?", $txn_code);
        
        if(!empty($txnrefnum)){
            $select->where("rr.txnrefnum = ?", $txnrefnum);
        }
        
        if(!empty($beneid)){
            $select->where("rr.beneficiary_id = ?", $beneid);
        }
        //echo $select->__toString();
        $row = $this->_db->fetchRow($select);

        return $row;
    }
 /* getRemitRefundsOnDateBasis() will return remit refund on date basis
     */

    public function getRemitRefundsOnDateBasis($param) {

        $retData = array();
        $retNewData = array();
        $agentUser = new AgentUser();
        $objCommission = new CommissionReport();
        
        if (!empty($param)) {

            $param['check_fee'] = false;
            $retData = $this->getRemitRefundfee($param);
            $totalRemitRefundFee = count($retData);

            if ($totalRemitRefundFee >= 1) {
                //$retData =  $retData->toArray();
                $totalData = count($retData);

                // adding moer fields and recreating array with adding new records for service tax and fee 
                $k = 0;
                $alterData = array();
                for ($j = 0; $j < $totalData; $j++) {

                    // adding transaction type field
                    $alterData = $retData[$j];
                    $alterData['txn_type'] = TXNTYPE_REMITTANCE_REFUND;
                    $alterData['crn'] = '';
                    //$alterData['txn_code'] = '';
                    $alterData['txn_date'] = Util::returnDateFormatted($retData[$j]['txn_date'], "Y-m-d", "d-m-Y", "-");
                    $alterData['agent_name'] = $retData[$j]['name'];
                    $alterData['batch_name'] = '';

                    $agentUser = new AgentUser();
                    $agentType = $agentUser->getAgentCodeName($retData[$j]['agent_user_type'], $retData[$j]['agent_id']);

                    $alterData['refund_txn_code'] = $retData[$j]['txn_code'];
                    $alterData['txn_code'] = $retData[$j]['tran_ref_num'];
                    
                    if(!empty($agentType))
                    {
                        $alterData = array_merge($alterData, $agentType);
                    }
                    
                    // getting to and from date
                    $queryParam['dateTo'] = $retData[$j]['date_created'];
                    $queryParam['dateFrom'] = $retData[$j]['date_created'];
                    
                    // getting agent id on queryParam array for query
                        $queryParam['agentId'] = $retData[$j]['agent_id'];
                        $queryParam['bank_unicode'] = $param['bank_unicode'];

                        /** ** getting agent loads/reloads transaction and commission total *** */
                        $loadTxnTypesParam = array(
                            '0' => TXNTYPE_FIRST_LOAD,
                            '1' => TXNTYPE_CARD_RELOAD
                        );
                        $respLoad = $objCommission->getAgentCommission($queryParam, $loadTxnTypesParam);

                        $agentLoadReload = array(
                            'total_agent_load_reload_amount' => $respLoad['total_agent_transaction_amount'],
                            'total_agent_load_reload_comm' => $respLoad['total_agent_commission'],
                            'plan_commission_name' => $respLoad['plan_commission_name'],
                            'transaction_fee' => $respLoad['transaction_fee'],
                            'commission_amount' => $respLoad['commission_amount'],
                            'transaction_service_tax' => $respLoad['transaction_service_tax']
                        );
                        if (!empty($agentLoadReload)) {
                            $alterData = array_merge($alterData, $agentLoadReload);
                        }
                        /*                         * ** getting agent loads/reloads transaction and commission total over here *** */


                        /*                         * ** getting agent remit all actions total like (remitter registration, remittance, refund txn amount total & their commission amount total) *** */
                        $remitTxnTypesParam = array(
                            '0' => TXNTYPE_REMITTER_REGISTRATION,
                            '1' => TXNTYPE_REMITTANCE_FEE,
                            '2' => TXNTYPE_REMITTANCE_REFUND_FEE
                        );
                        $respRemit = $objCommission->getAgentCommission($queryParam, $remitTxnTypesParam);
                        $agentRemit = array(
                            'total_agent_remit_amount' => $respRemit['total_agent_transaction_amount'],
                            'total_agent_remit_comm' => $respRemit['total_agent_commission'],
                            
                        );
                        if(!empty($respRemit['plan_commission_name'])){
                            $agentRemit['plan_commission_name'] = $respRemit['plan_commission_name'];
                        }
                        if(!empty($respRemit['transaction_fee'])){
                            $agentRemit['transaction_fee'] = $respRemit['transaction_fee'];
                        }
                        
                        if(!empty($respRemit['commission_amount'])){
                            $agentRemit['commission_amount'] = $respRemit['commission_amount'];
                        }
                        
                        if(!empty($respRemit['transaction_service_tax'])){
                            $agentRemit['transaction_service_tax'] = $respRemit['transaction_service_tax'];
                        }
                        
                        if (!empty($agentRemit)) {
                            $alterData = array_merge($alterData, $agentRemit);
                        }
                        
                    // recreating array with adding new records for service tax and fee 
                    $retNewData[$k] = $alterData;
                    $retNewData[$k]['batch_name'] = '';
                    $k++;
                    $retNewData[$k] = $alterData;
                    $retNewData[$k]['amount'] = $retData[$j]['reversal_service_tax'];
                    $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_SERVICE_TAX;
                    $retNewData[$k]['txn_code'] = $retData[$j]['tran_ref_num'];
                    $retNewData[$k]['batch_name'] = '';
                    $k++;
                    $retNewData[$k] = $alterData;
                    $retNewData[$k]['amount'] = $retData[$j]['reversal_fee'];
                    $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_REFUND_FEE;
                    $retNewData[$k]['txn_code'] = $retData[$j]['tran_ref_num'];
                    $retNewData[$k]['batch_name'] = '';
                    $k++;
                }
            }
        }

        return $retNewData;
    }
    
    /*
     * Get remitter refund fee for an agent on a particular date for a product
     */

    public function getRemitRefundfee($param) {

        $checkRegFee = isset($param['check_fee']) ? $param['check_fee'] : true;
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
        $date = $param['date'];
        $datefrom = $param['date_from'];
        $mobileno = isset($param['mobile_no']) ? $param['mobile_no'] : 0;
	$productId = isset($param['product_id']) ? $param['product_id'] : '';
        $txnno = isset($param['txn_no']) ? $param['txn_no'] : 0;
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
    	$bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'" . $decryptionKey . "') as ben_account_number");
        $details = $this->_db->select();
        $details->from(DbTable::TABLE_KOTAK_REMITTANCE_REFUND . " as rr", array('rr.fee', 'rr.service_tax', 'rr.amount', 'rr.id as rfid', 'rr.remittance_request_id', 'DATE(rr.date_created) as date_created', 'rr.date_created as txn_date', 'rr.txn_code', 'rr.reversal_service_tax', 'rr.reversal_fee','rr.txnrefnum'));
        $details->joinLeft(DbTable::TABLE_KOTAK_REMITTERS . " as rem", "rem.id = rr.remitter_id", array('rem.mobile as mobile_number', 'concat(rem.name," ",rem.last_name) as remit_name','rem.email as remitter_email', 'rem.date_created as remit_regn_date'));
        $details->joinLeft(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as req", "req.id = rr.remittance_request_id ", array('req.product_id', 'req.final_response', 'req.txn_code as tran_ref_num', 'req.status as txn_status'));
        $details->joinLeft(DbTable::TABLE_KOTAK_BENEFICIARIES . " as b", "req.beneficiary_id = b.id ", array('b.name as bene_name', 'b.bank_name as bene_bankname', 'b.ifsc_code as bene_ifsccode',$bankAccountNumber));
        $details->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "req.product_id = p.id ", array('p.ecs_product_code','p.unicode as pro_unicode'));
        $details->joinLeft(DbTable::TABLE_AGENTS . " as a", "rr.agent_id = a.id ", array('a.id as agent_id', 'a.agent_code', 'concat(a.first_name," ",a.last_name) as name','a.mobile1 as agent_mobile','a.email as agent_email', 'a.user_type as agent_user_type'));
        $details->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "a.id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", array('ad.estab_city', 'ad.estab_pincode', 'ad.bank_ifsc_code as agent_ifsc_code', 'ad.bank_account_number as agent_bank_account_number'));
        $details->where("rr.status = ? ", STATUS_SUCCESS);
        if ($checkRegFee) {
            $details->where("rr.fee > ? ", 0);
        }

        if ($agentId > 0)
            $details->where("rr.agent_id = ? ", $agentId);

        if(!empty($datefrom)){
            $details->where("rr.date_created >= ?", $datefrom);
            $details->where("rr.date_created <= ?", $date);
        } else if(!empty($date)){
            $details->where("DATE(rr.date_created) = ?", $date);
        }
        if ($mobileno > 0)
            $details->where("rem.mobile = ? ", $mobileno);
        if($productId !='')
	    $details->where("req.product_id = ? ", $productId);	
        if ($txnno > 0)
            $details->where("rr.txn_code = ? ", $txnno);
        
        return $this->_db->fetchAll($details);
    }
    
    
  public function getRemittanceResp( $param, $page = 1, $paginate = NULL)
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
            $select->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST." as rr", array('rr.amount', 'rr.txn_code', 'DATE_FORMAT(rr.date_created, "%d-%m-%Y") as date_created','rr.status','rr.final_response as remarks'));              
            $select->joinLeft(DbTable::TABLE_KOTAK_REMITTERS." as rem", "rr.remitter_id = rem.id" , array('rem.name as remitter_name', 'rem.mobile as remitter_mobile_number'));
            $select->joinLeft(DbTable::TABLE_KOTAK_BENEFICIARIES." as b", "rr.beneficiary_id = b.id" , array('b.name as beneficiary_name', $bankAccountNumber));
            $select->where("rr.status = '".STATUS_SUCCESS."' OR rr.status = '".STATUS_FAILURE."' OR rr.status = '".STATUS_REFUND."'");
            $select->where("rr.date_created BETWEEN '".$fromDate."' AND '".$toDate."'");
            $queryArray = $this->_db->fetchAll($select);
            
                return $queryArray;
       
    }
    
    
    public function getAgentRemittancesFeeSTax($param, $status = array()) {
        $date = isset($param['date']) ? $param['date'] : '';
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
        $to = isset($param['to']) ? $param['to'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $statusWhere = '';

        if (!empty($status)) {
            foreach ($status as $statusVal) {
                if ($statusWhere != '') {
                    $statusWhere .= " OR ";
                }
                $statusWhere .= "rr.status='" . $statusVal . "'";
            }
        }
        $select = $this->select();
        $select->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as rr", array('amount', 'fee', 'service_tax', 'txn_code', 'sender_msg', 'date_created'));
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_KOTAK_REMITTERS ." as r", "rr.remitter_id = r.id", array('name', 'last_name'));
        if ($statusWhere == '') {
            $select->where("rr.status = '" . STATUS_IN_PROCESS . "' OR rr.status = '" . STATUS_HOLD . "' OR rr.status =  '" . STATUS_SUCCESS . "' OR rr.status = '" . STATUS_REFUND . "' OR rr.status = '" . FLAG_FAILURE . "' OR rr.status = '" . STATUS_INCOMPLETE . "'");
        } else {
            $select->where($statusWhere);
        }
        if ($agentId > 0) {
            $select->where("rr.agent_id = ? ", $agentId);
        }
        if ($to != '' && $from != '') {
            $select->where("rr.date_created BETWEEN '$from' AND '$to'");
        } else {
            $select->where("DATE(rr.date_created) = ?", $date);
        }
        
        return $this->fetchAll($select);
    }
    
    public function getAgentRemittanceRefundsFeeSTax($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $date = isset($param['date']) ? $param['date'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $from = isset($param['from']) ? $param['from'] : '';

       
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_KOTAK_REMITTANCE_REFUND . ' as rr', array('amount', 'reversal_fee', 'reversal_service_tax', 'txn_code', 'date_created'));
        $select->joinLeft(DbTable::TABLE_KOTAK_REMITTERS ." as r", "rr.remitter_id = r.id", array('name', 'last_name'));
        if ($agentId > 0){
            $select->where('rr.agent_id=?', $agentId);
        }
        $select->where("rr.status='" . FLAG_SUCCESS . "'");
        if ($to != '' && $from != '') {
            $select->where("rr.date_created BETWEEN '$from' AND '$to'");
        } else {
            $select->where("DATE(rr.date_created) = ?", $date);
        }

        return $this->_db->fetchAll($select);
        
    }
    
    public function getTotalRemittanceFee($param) {
        $date = $param['date'];
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
        $checkRegFee = isset($param['check_fee']) ? $param['check_fee'] : true;

        $select = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as rr", array('count(*) as count', 'sum(amount) as total','sum(rr.fee) as fee'));
        $select->where("rr.status = '" . FLAG_SUCCESS . "' OR rr.status = '" . STATUS_IN_PROCESS . "' OR rr.status = '" . STATUS_PROCESSED . "'");
        if ($agentId > 0)
            $select->where("rr.agent_id = ? ", $agentId);
        
        
        $select->where("DATE(rr.date_created) = ?", $date);
        //echo $select."<br>";
        $rows = $this->_db->fetchRow($select);
        return $rows;
    }
    
    /* gets the Kotak Remittance Failure Recon for in_process, hold and failure */
    public function getKotakRemitFailureRecon() {

        $seprator = ',';
        $ext = '.csv';
        $fileName = '';
        $agentUser = new AgentUser();
        
        $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");
        try{
        
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'".$decryptionKey."') as bank_account_number");
        
        $select = $this->select();
        $select->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as r", array('r.fee', 'r.service_tax', 'r.amount', 'r.id AS rmid', 'DATE(r.date_created) as date_created', 'r.date_created as txn_date', 'r.txn_code', 'r.status as txn_status', 'r.final_response', 'r.date_updated as txn_updated', 'r.hold_reason'));
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_KOTAK_REMITTERS . " as rem", "rem.id = r.remitter_id", array('rem.mobile as mobile_number', 'concat(rem.name," ",rem.last_name) as remit_name'));
        $select->joinLeft(DbTable::TABLE_KOTAK_BENEFICIARIES . " as b", "r.beneficiary_id = b.id ", array('b.name as bene_name', $bankAccountNumber));
        $select->joinLeft(DbTable::TABLE_AGENTS . " as a", "r.agent_id = a.id ", array('a.id as agent_id', 'a.agent_code', 'concat(a.first_name," ",a.last_name) as agent_name', 'a.user_type as agent_user_type'));
        $select->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "a.id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", array('ad.estab_city'));
        $select->where("r.status = '" . STATUS_IN_PROCESS . "' OR r.status = '" . STATUS_HOLD . "' OR r.status = '" . FLAG_FAILURE . "'");

        $criteria = $this->fetchAll($select);
        
        $recordCount = count($criteria);
                
        $i = 0;
        $arrReport = array();
        
        $file = new Files();                    
        $file->setFilePermission('');
                
        $arrReport[$i]['txn_date'] ='Transaction Date'; 
        $arrReport[$i]['sup_dist_name'] = 'Super Distributor Name'; 
        $arrReport[$i]['dist_name'] ='Distributor Name'; 
        $arrReport[$i]['agent_code'] = 'Agent Code'; 
        $arrReport[$i]['agent_name'] ='Agent Name'; 
        $arrReport[$i]['remit_name'] = 'Remitter Name'; 
        $arrReport[$i]['remit_mobileno'] ='Remitter Mobile Number'; 
        $arrReport[$i]['bene_name'] = 'Bene Name'; 
        $arrReport[$i]['bene_acc_num'] ='Bene Account Number'; 
        $arrReport[$i]['txn_type'] = 'Shmart Transaction Code';
        $arrReport[$i]['txn_amt'] = 'Transaction Amount';
        $arrReport[$i]['txn_ref_num'] = 'Transaction Reference Number';
        $arrReport[$i]['utr_num'] = 'Bank UTR Number';
        $arrReport[$i]['txn_status'] = 'Transaction Status';
        $arrReport[$i]['reason_code'] = 'Reason Code';
        $arrReport[$i]['final_resp'] = 'Final Response';
        $arrReport[$i]['date_created'] = 'Date Created';
        $arrReport[$i]['date_updated'] = 'Date Updated';
        $arrReport[$i]['hold_reason'] = ' Hold Reason ';
        
        $i = 1;
        if($recordCount >= 1)
        {
            foreach($criteria as $data){
                
                $reason = explode(')', $data['final_response']);
                
                $arrReport[$i]['txn_date']  = $data['txn_date'];

                $agentType = $agentUser->getAgentCodeName($data['agent_user_type'], $data['agent_id']);
                
                $arrReport[$i]['sup_dist_name']  = $agentType['sup_dist_name'];
                $arrReport[$i]['dist_name']  = $agentType['dist_name'];
                
                $arrReport[$i]['agent_code']  = $data['agent_code'];
                $arrReport[$i]['agent_name']  = $data['agent_name'];
                $arrReport[$i]['remit_name']  = $data['remit_name'];
                $arrReport[$i]['remit_mobileno']  = $data['mobile_number'];
                $arrReport[$i]['bene_name']  = $data['bene_name'];
                $arrReport[$i]['bene_acc_num']  = Util::maskCard($data['bank_account_number']);
                $arrReport[$i]['txn_type']  = isset($TXN_TYPE_LABELS[TXNTYPE_REMITTANCE]) ? $TXN_TYPE_LABELS[TXNTYPE_REMITTANCE] : '';
                $arrReport[$i]['txn_amt']  = $data['amount'];
                $arrReport[$i]['txn_ref_num']  = $data['txn_code'];
                $arrReport[$i]['utr_num']  = '';
                $arrReport[$i]['txn_status']  = $data['txn_status'];
                $arrReport[$i]['reason_code']  = str_replace('(', '', $reason[0]);
                $arrReport[$i]['final_resp']  = $data['final_response'];
                $arrReport[$i]['date_created']  = $data['date_created'];
                $arrReport[$i]['date_updated']  = $data['txn_updated'];
                $arrReport[$i]['hold_reason']  = $data['hold_reason'];

               $i++; 
            }
        }
        
        $fileName = 'REMIT_KOTAK_FAILURE_RECON_'.date('Y-m-d_h:i:s').$ext;
        $file->setBatch($arrReport, $seprator);
        $file->setFilepath(UPLOAD_PATH_REMIT_KOTAK_FAILURE_RECON_REPORTS);
        $file->setFilename($fileName);
        $file->generate(TRUE); 

        //insert file info in t_files table
        $msg = $recordCount.' records has been found';
        $file->insertFileInfo(array('label'=>REMIT_KOTAK_FAILURE_RECON_FILE, 'file_name'=>$fileName, 'date_start'=>date('Y-m-d'), 'date_end'=>date('Y-m-d'), 'status'=>STATUS_ACTIVE, 'comment'=>$msg, 'date_created'=>new Zend_Db_Expr('NOW()')));

        return $recordCount;
        }catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
        }
    }
    
    
    
    /* getBeneficiaryException() will return the Beneficiary exception details
     */

    public function getBeneficiaryException($param) {

        /*
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'" . $decryptionKey . "') as bank_account_number");
        $toDate = isset($param['to']) ? $param['to'] : '';
        $fromDate = isset($param['from']) ? $param['from'] : '';

        $select = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as rr", array('sum(rr.amount) as total_amount', 'count(rr.id) as total_count', 'rr.date_created'))
                ->joinLeft(DbTable::TABLE_KOTAK_REMITTERS . " as r", "rr.remitter_id = r.id", array('r.name as remitter_name', 'r.mobile as remitter_mobile_number', 'r.email as remitter_email'))
                ->joinLeft(DbTable::TABLE_KOTAK_BENEFICIARIES . " as b", "rr.beneficiary_id =b.id", array('b.name as beneficiary_name', $bankAccountNumber, 'b.bank_name', 'b.ifsc_code', 'b.by_agent_id'))
                ->group('rr.beneficiary_id')
                ->group('DATE(rr.date_created)')
                ->where("DATE(rr.date_created) BETWEEN '" . $fromDate . "' AND '" . $toDate . "'")
                ->where("rr.status = '" . STATUS_SUCCESS . "' OR rr.status = '" . STATUS_IN_PROCESS . "' OR rr.status = '" . STATUS_HOLD . "'");
        //echo $select->__toString();exit;
        $rows = $this->_db->fetchAll($select);
        
        $rsCount = count($rows);
         * 
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
    
     public function getRemitWalletTrialBalance($param) 
    {
        $retArr = array();
                return $retArr;
    }
    
    public function getRemitterOTPDetails($remitterId = 0) {
        $select = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_REMITTERS. " as rr", array('otp','date_otp'));
        $select->where("rr.id = ?", $remitterId);

        $rows = $this->_db->fetchRow($select);

        return $rows;
    }
    
    public function getRemitterRemittanceCount($remitterId = 0, $otp = '') {
        $select = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as rr", array('count(*) as count'));
        $select->where("rr.remitter_id = ?", $remitterId);
        if($otp != ''){
        $select->where("rr.otp = ?", $otp);
        }
        $rows = $this->_db->fetchRow($select);

        return $rows['count'];
    }
    
    
     /* getKotakRemittance() will return the number of remittance for the duration
     */
    public function getKotakRemittance($param){ 
        
        if(isset($param['duration']) && $param['duration'] != ''){
            $dates = Util::getDurationDates($param['duration']);
            $to = isset($dates['to']) ? $dates['to'] : '';
            $from = isset($dates['from']) ? $dates['from'] : '';
        } else {
            $to = isset($param['to']) ? $param['to'] : '';
            $from = isset($param['from']) ? $param['from'] : ''; 
        }

        $retTxnData = array();
            
        //$queryDateArr = explode(' ', $to);
        //$queryFromDateArr = explode(' ', $from);
        $queryDate = array('date'=>$to,
                        'date_from' => $from,
                        'agent_id' => $param['agent_id'],
                        'mobile_no' => $param['mobile_no'],
                        'txn_no' => $param['txn_no'],
			'product_id' => $param['product_id'],
                        'bank_unicode' => $param['bank_unicode']);


        /**** getting agent remitters registered for particular date ****/
        $remitters  = $this->getRemittersOnDateBasis($queryDate);
        if(!empty($remitters)){
            $retTxnData = array_merge($retTxnData, $remitters);
        }

        /**** getting agent remitters's fund transfer request for particular date *****/
        $remitRequests  = $this->getRemittanceRequestOnDateBasis($queryDate);
        if(!empty($remitRequests)){
            $retTxnData = array_merge($retTxnData, $remitRequests);
        }
        /**** getting agent remitters's refunds for particular date *****/
        $remitRefunds  = $this->getRemittanceRefundsOnDateBasis($queryDate);
        if(!empty($remitRefunds)){
            $retTxnData = array_merge($retTxnData, $remitRefunds);
        }

	/**** getting agent commission details *****/
	$commRequests  = $this->getCommissionTransactions($queryDate);
	if(!empty($commRequests)){
		$retTxnData = array_merge($retTxnData, $commRequests);
	}

        return $retTxnData ;
    }
    
    /* getRemittersOnDateBasis() will return remitters who got registered successfully on date basis
     */

    public function getRemittersOnDateBasis($param) {

        $retData = array();
        $retNewData = array();
        $objKotakRemitter = new Remit_Kotak_Remitter();
        if (!empty($param)) {

            $param['check_fee'] = false;
            $retData = $objKotakRemitter->getRemitterRegnfee($param);
            $totalRemitRegnFee = count($retData);

            if ($totalRemitRegnFee >= 1) {
                $retData = $retData->toArray();
                $totalData = count($retData);

                // recreating array with adding new records for service tax 
                $k = 0;
                $alterData = array();
                for ($j = 0; $j < $totalData; $j++) {

                    // adding transaction type field
                    $alterData = $retData[$j];
                    $alterData['txn_type'] = TXNTYPE_REMITTER_REGISTRATION;
                    $alterData['crn'] = '';
                    $alterData['amount'] = $retData[$j]['fee'];
                    $alterData['txn_date'] = Util::returnDateFormatted($retData[$j]['txn_date'], "Y-m-d", "d-m-Y", "-");
                    $alterData['agent_name'] = $retData[$j]['name'];
                    
                    $agentUser = new AgentUser();
                    $agentType = $agentUser->getAgentCodeName($retData[$j]['agent_user_type'], $retData[$j]['agent_id']);

                    $refund_txn = $this->getRefundTxnRefNo($retData[$j]['rid'], $retData[$j]['agent_id']);

                    if(!empty($refund_txn))
                    {
                        $alterData['refund_txn_code'] = $refund_txn['refund_txn_code'];
                    }
                    else
                    {
                        $alterData['refund_txn_code'] = '';
                    }
                    
                    if(!empty($agentType))
                    {
                        $alterData = array_merge($alterData, $agentType);
                    }
                        
                    // recreating array with adding new records for service tax and fee 
                    $retNewData[$k] = $alterData;
                    $retNewData[$k]['batch_name'] = '';
                    $k++;
                    $retNewData[$k] = $alterData;
                    $retNewData[$k]['amount'] = $retData[$j]['service_tax'];
                    $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_SERVICE_TAX;
                    $retNewData[$k]['txn_code'] = $retData[$j]['txn_code'];
		    $retNewData[$k]['txnrefnum'] = $retData[$j]['txnrefnum']; 
                    $retNewData[$k]['batch_name'] = '';
                    $k++;
                }
            }
        }

        return $retNewData;
    }
    
    /* getRemittanceRequestOnDateBasis() will return remit request for 'in_process','processed','success','failure','refund' status 
     */

    public function getRemittanceRequestOnDateBasis($param) {

        $retData = array();
        $retNewData = array();
        
        if (!empty($param)) {

            $param['check_fee'] = false;
            $retData = $this->getRemittancefee($param);
            $totalRemitFee = count($retData);

            if ($totalRemitFee >= 1) {
                $retData = $retData->toArray();
                $totalData = count($retData);

                $k = 0;
                $alterData = array();
                for ($j = 0; $j < $totalData; $j++) {

                    // adding transaction type field
                    $alterData = $retData[$j];
                    $alterData['txn_type'] = TXNTYPE_REMITTANCE;
                    $alterData['crn'] = '';
                    $alterData['txn_date'] = Util::returnDateFormatted($retData[$j]['txn_date'], "Y-m-d", "d-m-Y", "-");
                    $alterData['agent_name'] = $retData[$j]['name'];
                    
                    $agentUser = new AgentUser();
                    $agentType = $agentUser->getAgentCodeName($retData[$j]['agent_user_type'], $retData[$j]['agent_id']);

                    $refund_txn = $this->getRefundTxnRefNo($retData[$j]['rmid']);
                    
                    if(!empty($refund_txn))
                    {
                        $alterData['refund_txn_code'] = $refund_txn['refund_txn_code'];
                    }
                    else
                    {
                        $alterData['refund_txn_code'] = '';
                    }

                    if(!empty($agentType))
                    {
                        $alterData = array_merge($alterData, $agentType);
                    }
                        
                    // recreating array with adding new records for service tax and fee 
                    $retNewData[$k] = $alterData;
                    $k++;
                    $retNewData[$k] = $alterData;
                    $retNewData[$k]['amount'] = $retData[$j]['service_tax'];
                    $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_SERVICE_TAX;
                    $retNewData[$k]['txn_code'] = $retData[$j]['txn_code'];
		    $retNewData[$k]['txnrefnum'] = $retData[$j]['txnrefnum']; 
                    $retNewData[$k]['batch_name'] = '';
                    $k++;
                    $retNewData[$k] = $alterData;
                    $retNewData[$k]['amount'] = $retData[$j]['fee'];
                    $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_FEE;
                    $retNewData[$k]['txn_code'] = $retData[$j]['txn_code'];
		    $retNewData[$k]['txnrefnum'] = $retData[$j]['txnrefnum'];
                    $retNewData[$k]['batch_name'] = '';
                    $k++;
                }
            }
        }
        return $retNewData;
    }
    
    /* getRemittanceRefundsOnDateBasis() will return remit refund on date basis
     */

    public function getRemittanceRefundsOnDateBasis($param) {

        $retData = array();
        $retNewData = array();
        $agentUser = new AgentUser();
        
        if (!empty($param)) {

            $param['check_fee'] = false;
            $retData = $this->getRemitRefundfee($param);
            $totalRemitRefundFee = count($retData);

            if ($totalRemitRefundFee >= 1) {
                //$retData =  $retData->toArray();
                $totalData = count($retData);

                // adding moer fields and recreating array with adding new records for service tax and fee 
                $k = 0;
                $alterData = array();
                for ($j = 0; $j < $totalData; $j++) {

                    // adding transaction type field
                    $alterData = $retData[$j];
                    $alterData['txn_type'] = TXNTYPE_REMITTANCE_REFUND;
                    $alterData['crn'] = '';
                    //$alterData['txn_code'] = '';
                    $alterData['txn_date'] = Util::returnDateFormatted($retData[$j]['txn_date'], "Y-m-d", "d-m-Y", "-");
                    $alterData['agent_name'] = $retData[$j]['name'];
                    $alterData['batch_name'] = '';

                    $agentUser = new AgentUser();
                    $agentType = $agentUser->getAgentCodeName($retData[$j]['agent_user_type'], $retData[$j]['agent_id']);

                    $alterData['refund_txn_code'] = $retData[$j]['txn_code'];
                    $alterData['txn_code'] = $retData[$j]['tran_ref_num'];
                    
                    if(!empty($agentType))
                    {
                        $alterData = array_merge($alterData, $agentType);
                    }
                                            
                    // recreating array with adding new records for service tax and fee 
                    $retNewData[$k] = $alterData;
                    $retNewData[$k]['batch_name'] = '';
                    $k++;
                    $retNewData[$k] = $alterData;
                    $retNewData[$k]['amount'] = $retData[$j]['reversal_service_tax'];
                    $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_SERVICE_TAX;
                    $retNewData[$k]['txn_code'] = $retData[$j]['tran_ref_num'];
		    $retNewData[$k]['txnrefnum'] = $retData[$j]['txnrefnum']; 
                    $retNewData[$k]['batch_name'] = '';
                    $k++;
                    $retNewData[$k] = $alterData;
                    $retNewData[$k]['amount'] = $retData[$j]['reversal_fee'];
                    $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_REFUND_FEE;
                    $retNewData[$k]['txn_code'] = $retData[$j]['tran_ref_num'];
		    $retNewData[$k]['txnrefnum'] = $retData[$j]['txnrefnum'];
                    $retNewData[$k]['batch_name'] = '';
                    $k++;
                }
            }
        }

        return $retNewData;
    }
    
    public function getRemittancefeeAll($param){
        $to = $param['to'];
        $from = $param['from']; 
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : ''; 
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as r", 
                    array(
                        'r.fee', 'r.service_tax', 'r.amount','DATE_FORMAT(r.date_created,"%d-%m-%Y") as txn_date','DATE(r.date_created) as date_created','r.status','r.txn_code'
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
                DbTable::TABLE_KOTAK_REMITTANCE_REFUND." as ref", "ref.remittance_request_id = r.id" , 
                    array(
                        'ref.txn_code as refund_txn_code'
                ));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel", "a.id = orel.to_object_id" ,array( ));
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
        
        $select->where("r.status = '" . STATUS_IN_PROCESS . "' OR r.status = '" . STATUS_HOLD . "' OR r.status =  '" . STATUS_SUCCESS . "' OR r.status = '" . STATUS_REFUND . "' OR r.status = '" . FLAG_FAILURE . "'");
        $select->where("r.date_created >= ?", $from);
        $select->where("r.date_created <= ?", $to); 
        
        if ($agentId != '') {
            $select->where('r.agent_id=?', $agentId);
        }
        
        return $this->fetchAll($select); 
    }
    
    public function getRemitRefundfeeAll($param) {
        $to = $param['to'];
        $from = $param['from']; 
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : ''; 
        
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                DbTable::TABLE_KOTAK_REMITTANCE_REFUND . " as rr", 
                    array(
                        'rr.fee', 'rr.service_tax', 'rr.amount','DATE_FORMAT(rr.date_created,"%d-%m-%Y") as txn_date','DATE(rr.date_created) as date_created','rr.txn_code as refund_txn_code', 'rr.reversal_service_tax', 'rr.reversal_fee'
                ));
         $select->joinLeft(
                 DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as req", "req.id = rr.remittance_request_id ", 
                    array(
                        'req.final_response', 'req.txn_code', 'req.status'
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
     * Get remittance fee for an agent on a particular date for a product. This is called from Operation portal. Please do not modify this function
     */
    public function getRemitRemittancefee($param){
        $to = $param['to'];
        $from = $param['from']; 
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : ''; 
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as r", 
                    array(
                        'r.fee as fee_amount', 'r.service_tax as service_tax_amount', 'r.amount as transaction_amount','DATE_FORMAT(r.date_created,"%d-%m-%Y") as txn_date','DATE(r.date_created) as date_created','r.status as txn_status','r.txn_code', new Zend_Db_Expr("'".TXNTYPE_REMITTANCE_FEE."' as transaction_type_name"), new Zend_Db_Expr("0 as reversal_fee"), new Zend_Db_Expr("0 as reversal_service_tax"), new Zend_Db_Expr("'' as utr")
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
                DbTable::TABLE_KOTAK_REMITTANCE_REFUND." as ref", "ref.remittance_request_id = r.id" , 
                    array(
                        'ref.txn_code as refund_txn_code'
                ));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel", "a.id = orel.to_object_id" ,array( ));
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
        
        $select->where("r.status = '" . STATUS_IN_PROCESS . "' OR r.status = '" . STATUS_HOLD . "' OR r.status =  '" . STATUS_SUCCESS . "' OR r.status = '" . STATUS_REFUND . "' OR r.status = '" . FLAG_FAILURE . "'");
        $select->where("r.date_created >= ?", $from);
        $select->where("r.date_created <= ?", $to); 
        
        if ($agentId != '') {
            $select->where('r.agent_id=?', $agentId);
        }

        return $this->fetchAll($select); 	
    }
    
    /* 
     * Get refund fee for an agent on a particular date for a product. This is called from Operation portal. Please do not modify this function
     */
    public function getRemittanceRefundfee($param) {
        $to = $param['to'];
        $from = $param['from']; 
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : ''; 
        
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                DbTable::TABLE_KOTAK_REMITTANCE_REFUND . " as rr", 
                    array(
                        'rr.fee as fee_amount', 'rr.service_tax as service_tax_amount', 'rr.amount as transaction_amount','DATE_FORMAT(rr.date_created,"%d-%m-%Y") as txn_date','DATE(rr.date_created) as date_created','rr.txn_code as refund_txn_code', 'rr.reversal_service_tax', 'rr.reversal_fee', new Zend_Db_Expr("'".TXNTYPE_REMITTANCE_REFUND_FEE."' as transaction_type_name"), new Zend_Db_Expr("'' as utr")
                ));
         $select->joinLeft(
                 DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as req", "req.id = rr.remittance_request_id ", 
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
    
    /* gets the Kotak Remittance Transaction Recon for in_process, hold and failure */
    public function getKotakRemittanceTxnRecon() {

        $seprator = ',';
        $ext = '.csv';
        $fileName = '';
        $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");

        $bankRat = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        $bankRatUnicode = $bankRat->bank->unicode;
        
        $bankObj = new Banks();
        $bankInfo = $bankObj->getBankbyUnicode($bankRatUnicode);
        
        try{
        /*******
        //Enable DB Slave
        $this->_enableDbSlave();

        $remitterRegnModel = new Remit_Kotak_Remitter();

        // Get Remitter registrations Fee, Transaction type code = TXNTYPE_REMITTER_REGISTRATION
        $agentRMRG = Util::toArray($remitterRegnModel->getRemitterRegistrationRecon());

        // Get Remittance Fee, typecode =  TXNTYPE_REMITTANCE_FEE
        $agentREMT = Util::toArray($this->getRemitRemittanceRecon());

        // Get Remittance refund Fee, typecode = TXNTYPE_REMITTANCE_REFUND_FEE
        $agentRMFE = Util::toArray($this->getRemittanceRefundRecon());

        //Disable DB Slave
        $this->_disableDbSlave();

        $criteria = array_merge($agentRMRG, $agentREMT, $agentRMFE);
        *****/
        //Enable DB Slave
        $this->_enableDbSlave();
        
        // Get Remittance Fee, typecode =  TXNTYPE_REMITTANCE_FEE
        $criteria = Util::toArray($this->getRemitRemittanceRecon());
        //Disable DB Slave
        $this->_disableDbSlave();
        $recordCount = count($criteria);
                
        $i = 0;
        $arrReport = array();
        
        $file = new Files();                    
        $file->setFilePermission('');
                
        $arrReport[$i]['txn_date'] ='Transaction Date'; 
        $arrReport[$i]['txn_code'] ='Transaction Code'; 
        $arrReport[$i]['sup_dist_code'] = 'Super Distributor Code'; 
        $arrReport[$i]['sup_dist_name'] = 'Super Distributor Name'; 
        $arrReport[$i]['dist_code'] = 'Distributor Code'; 
        $arrReport[$i]['dist_name'] ='Distributor Name'; 
        $arrReport[$i]['agent_code'] = 'Agent Code'; 
        $arrReport[$i]['agent_name'] ='Agent Name'; 
        $arrReport[$i]['card_num'] ='Card No'; 
        $arrReport[$i]['crn'] ='CRN'; 
        $arrReport[$i]['remit_name'] = 'Remitter Name'; 
        $arrReport[$i]['remit_mobileno'] ='Remitter Mobile Number'; 
        $arrReport[$i]['product_name'] ='Product Name'; 
        $arrReport[$i]['bene_name'] = 'Bene Name'; 
        $arrReport[$i]['bene_acc_num'] ='Bene Account Number'; 
        $arrReport[$i]['shmart_txn_code'] = 'Shmart Transaction Code';
        $arrReport[$i]['utr_num'] ='Bank UTR Number'; 
        $arrReport[$i]['txn_amt'] = 'Transaction Amount';
        $arrReport[$i]['txn_type'] ='Transaction Type'; 
        $arrReport[$i]['txn_status'] = 'Transaction Status';
        $arrReport[$i]['remarks'] = 'Remarks';
        $arrReport[$i]['date_created'] = 'Date Created';
        $arrReport[$i]['date_updated'] = 'Date Updated';
        $arrReport[$i]['fee'] = 'Fee';
        $arrReport[$i]['service_tax'] = 'Service Tax';
        $arrReport[$i]['sender_msg'] = 'Sender Msg';
        $arrReport[$i]['status'] = 'Status';
        $arrReport[$i]['hold_reason'] = 'Hold Reason';
        $arrReport[$i]['cr_response'] = 'CR Response';
        $arrReport[$i]['final_resp'] = 'Final Response';
        $arrReport[$i]['fund_holder'] = 'Fund Holder';        
        $arrReport[$i]['txn_ref_num'] = 'Refund/Reversed Trx Ref Number';
        $arrReport[$i]['current_txn_status'] = 'Current Transaction Status';  
        $arrReport[$i]['return_date'] = 'Response file \u2013 Returned date';  
        $arrReport[$i]['rejection_code'] = 'Rejection Code';
        $arrReport[$i]['rejection_remarks'] = 'Rejection Remarks';
        
        $i++;
        if($recordCount >= 1)
        {
            foreach($criteria as $data){
                
                $reason = explode(')', $data['final_response']);
                
                $arrReport[$i]['txn_date']  = $data['txn_date'];
                $arrReport[$i]['txn_code']  = $data['txn_code'];
                $arrReport[$i]['sup_dist_code']  = $data['sup_dist_code'];
                $arrReport[$i]['sup_dist_name']  = $data['sup_dist_name'];
                $arrReport[$i]['dist_code']  = $data['dist_code'];
                $arrReport[$i]['dist_name']  = $data['dist_name'];
                $arrReport[$i]['agent_code']  = $data['agent_code'];
                $arrReport[$i]['agent_name']  = $data['name'];
                $arrReport[$i]['card_num']  = '';
                $arrReport[$i]['crn']  = '';                
                $arrReport[$i]['remit_name']  = $data['remitter_name'];
                $arrReport[$i]['remit_mobileno']  = $data['remitter_mobile'];
                $arrReport[$i]['product_name']  = $data['product_name'];
                $arrReport[$i]['bene_name']  = $data['bene_name'];
                $arrReport[$i]['bene_acc_num']  = Util::maskCard($data['bene_account_number']);
                $arrReport[$i]['shmart_txn_code']  = $data['txn_code'];
                $arrReport[$i]['utr_num']  = '';             
                $arrReport[$i]['txn_amt']  = $data['transaction_amount'];
                $arrReport[$i]['txn_type']  = $TXN_TYPE_LABELS[$data['transaction_type_name']];
                $arrReport[$i]['txn_status']  = Util::getStatusArray($data['txn_status']);
                $arrReport[$i]['remarks']  = '';
                $arrReport[$i]['date_created']  = $data['date_created'];
                $arrReport[$i]['date_updated']  = $data['date_updated'];
                $arrReport[$i]['fee']  = $data['fee_amount'];
                $arrReport[$i]['service_tax']  = $data['service_tax_amount'];
                $arrReport[$i]['sender_msg']  = $data['sender_msg'];
                $arrReport[$i]['status']  = Util::getStatusArray($data['txn_status']);
                $arrReport[$i]['hold_reason']  = $data['hold_reason'];
                $arrReport[$i]['cr_response']  = $data['cr_response'];
                $arrReport[$i]['final_resp']  = $data['final_response'];
                $arrReport[$i]['fund_holder']  = ucfirst($data['fund_holder']);
                $arrReport[$i]['txn_ref_num']  = $data['refund_txn_code'];
                $arrReport[$i]['current_txn_status']  = Util::getStatusArray($data['txn_status']);
                $arrReport[$i]['return_date']  = '';
                $arrReport[$i]['rejection_code']  = str_replace('(', '', $reason[0]);
                $arrReport[$i]['rejection_remarks']  = !empty($reason[1]) ? str_replace('(', '', $reason[1]) : '';

               $i++; 
            }
                
            $fileName = 'KTK_'.Util::getNeftBatchFileName().$ext;
            $file->setBatch($arrReport, $seprator);
            $file->setFilepath(UPLOAD_PATH_KOTAK_REMIT_TXN_RECON_REPORTS);
            $file->setFilename($fileName);
            $file->generate(TRUE); 

            //insert file info in t_files table
            $msg = $recordCount.' records has been found';
            $file->insertFileInfo(array('bank_id'=>$bankInfo['id'], 'label'=>KOTAK_REMIT_TXN_RECON_FILE, 'file_name'=>$fileName, 'ops_id'=>TXN_OPS_ID, 'date_start'=>date('Y-m-d'), 'date_end'=>date('Y-m-d'), 'status'=>STATUS_ACTIVE, 'comment'=>$msg, 'date_created'=>new Zend_Db_Expr('NOW()')));
        }
        return $recordCount;
        }catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
        }
    }
    
    /*
     * getRemitRemittanceRecon() gets kotak remittanes for the previous day for status (in process, hold, failure)
     */
    public function getRemitRemittanceRecon(){ 
        $prevDate = date('Y-m-d', strtotime('-1 days'));
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'" . $decryptionKey . "') as bene_account_number");
                
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as r", 
                    array(
                        'r.fee as fee_amount', 'r.service_tax as service_tax_amount', 'r.amount as transaction_amount','DATE_FORMAT(r.date_created,"%d-%m-%Y") as txn_date','DATE_FORMAT(r.date_created,"%d-%m-%Y") as date_created','r.status as txn_status','r.txn_code', new Zend_Db_Expr("'".TXNTYPE_REMITTANCE_FEE."' as transaction_type_name"), new Zend_Db_Expr("'' as utr"),'DATE_FORMAT(r.date_updated,"%d-%m-%Y") as date_updated', 'r.sender_msg', 'r.fund_holder', 'r.hold_reason', 'cr_response', 'r.final_response'
                ));
        $select->joinLeft(DbTable::TABLE_KOTAK_REMITTERS . " as rem", "rem.id = r.remitter_id", array('rem.mobile as remitter_mobile', 'concat(rem.name," ",rem.last_name) as remitter_name'));
        $select->joinLeft(DbTable::TABLE_KOTAK_BENEFICIARIES . " as b", "r.beneficiary_id = b.id ", array('b.name as bene_name', $bankAccountNumber));
        $select->joinLeft(
                DbTable::TABLE_AGENTS . " as a", "r.agent_id = a.id AND a.status = '" . STATUS_UNBLOCKED . "'", 
                    array(
                        'a.agent_code', 'concat(a.first_name," ",a.last_name) as name'
                ));        
        $select->joinLeft(
                DbTable::TABLE_KOTAK_REMITTANCE_REFUND." as ref", "ref.remittance_request_id = r.id" , 
                    array(
                        'ref.txn_code as refund_txn_code'
                ));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel", "a.id = orel.to_object_id" ,array( ));
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
        $select->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "r.product_id = p.id ", array('p.name as product_name'));
        $select->where("r.status = '" . STATUS_IN_PROCESS . "' OR r.status = '" . STATUS_HOLD . "' OR r.status = '" . FLAG_FAILURE . "'");
        $select->where("DATE(r.date_created) = ?", $prevDate);
        
        return $this->fetchAll($select); 
    }
    
    /*
     * get kotak remittance refund recon with status 'failure'
     */
    public function getRemittanceRefundRecon() {
        $prevDate = date('Y-m-d', strtotime('-1 days'));
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'" . $decryptionKey . "') as bene_account_number");
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                DbTable::TABLE_KOTAK_REMITTANCE_REFUND . " as rr", 
                    array(
                        'rr.fee as fee_amount', 'rr.service_tax as service_tax_amount', 'rr.amount as transaction_amount','DATE_FORMAT(rr.date_created,"%d-%m-%Y") as txn_date','DATE_FORMAT(rr.date_created,"%d-%m-%Y") as date_created','rr.txn_code as refund_txn_code', 'rr.reversal_service_tax', 'rr.reversal_fee', new Zend_Db_Expr("'".TXNTYPE_REMITTANCE_REFUND_FEE."' as transaction_type_name"), new Zend_Db_Expr("'' as utr"), new Zend_Db_Expr("'' as date_updated")
                ));
        $select->joinLeft(
                 DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as req", "req.id = rr.remittance_request_id ", 
                    array(
                        'req.txn_code', 'req.status as txn_status','req.fund_holder', 'req.sender_msg', 'req.hold_reason', 'req.cr_response', 'req.final_response'
                ));        
        $select->joinLeft(DbTable::TABLE_KOTAK_REMITTERS . " as rem", "rem.id = rr.remitter_id", array('rem.mobile as remitter_mobile', 'concat(rem.name," ",rem.last_name) as remitter_name'));
        $select->joinLeft(DbTable::TABLE_KOTAK_BENEFICIARIES . " as b", "req.beneficiary_id = b.id ", array('b.name as bene_name', $bankAccountNumber));
        $select->joinLeft(
                DbTable::TABLE_AGENTS . " as a", "rr.agent_id = a.id AND a.status = '" . STATUS_UNBLOCKED . "'", 
                    array(
                        'a.agent_code', 'concat(a.first_name," ",a.last_name) as name'
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
        $select->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "rr.product_id = p.id ", array('p.name as product_name'));
        $select->where("rr.status = ? ", STATUS_FAILURE);
        $select->where("DATE(rr.date_created) = ?", $prevDate);
        
        return $this->fetchAll($select);
    }
    
    public function remittanceTransaction($params,$remitterInfo,$beniInfo) {
         $remittanceFee = isset($params['fee']) ? $params['fee'] : 0;
         $remittanceServiceTax = isset($params['service_tax']) ? $params['service_tax'] : 0;
        
        $params['amount'] = Util::convertToRupee($params['amount']);
        $baseTxn = new BaseTxn();
        $bank = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        $remittancestatuslog = new Remit_Kotak_Remittancestatuslog();
        $m = new App\Messaging\Remit\Kotak\Agent();
        $object = new Remit_Kotak_Remitter();
        $param = array('agent_id' => $params['agent_id'],
                    'product_id' => $params['product_id'],
                    'remitter_id' => $params['remitter_id'],
                    'amount' => $params['amount'],
                    'fee_amt' => $remittanceFee,
                    'service_tax' => $remittanceServiceTax,
                    'bank_unicode' => $bank->bank->unicode
                );
        if($this->chkAllowRemit($param)) {
       // echo "<pre>";print_r($params);exit;
            $res = $this->save($params);
           // echo $res; exit;
            $datastatus = array();
            $datastatus['remittance_request_id'] = $res;
            $datastatus['status_old'] = '';
            $datastatus['status_new'] = STATUS_INCOMPLETE;
            $datastatus['by_remitter_id'] = $params['remitter_id'];
            $datastatus['by_agent_id'] = $params['agent_id'];
            $datastatus['by_ops_id'] = 0;
            $datastatus['date_created'] = new Zend_Db_Expr('NOW()');
            $resLog = $remittancestatuslog->addStatus($datastatus);
            if ($res > 0) {
                $paramsArr = array('agent_id' => $params['agent_id'],
                    'product_id' => $params['product_id'],
                    'remitter_id' => $params['remitter_id'],
                    'amount' => $params['amount'],
                    'remit_request_id' => $res,
                    'fee_amt' => $remittanceFee,
                    'service_tax' => $remittanceServiceTax,
                    'bank_unicode' => $bank->bank->unicode
                );
                $txnCode = $this->initiateRemit($paramsArr);
                
                try{
                     
                    if ($txnCode) {
                        $updateArr = array(
                            'status' => STATUS_IN_PROCESS,
                            'fund_holder' => REMIT_FUND_HOLDER_NEFT,
                            'txn_code' => $txnCode
                        );
                        $resUpdate = $this->updateReq($res, $updateArr);
                        $datastatus = array();
                        $datastatus['remittance_request_id'] = $res;
                        $datastatus['status_old'] = STATUS_INCOMPLETE;
                        $datastatus['status_new'] = STATUS_IN_PROCESS;
                        $datastatus['by_remitter_id'] = $params['remitter_id'];
                        $datastatus['by_agent_id'] = $params['agent_id'];
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
                        $agentDetails = $agentUser->getClosedLoopAgentDetailsById($params['agent_id']);                                        
                        if(isset($agentDetails['group']) && !empty($agentDetails['group']))
                            {
                                       $partnerName = $agentDetails['group'];
                            }
                            
                        $paramApi = array(
                            'traceNumber' => $txnCode,
                            'beneIFSC' => strtoupper($beniInfo['ifsc_code']),
                            'beneAccount' => $beniInfo['bank_account_number'],
                            'amount' => $params['amount'],
                            'remarks' => $remarks,
                            'remitterName' => $remitterInfo['name'],
                            'remitterMobile' => $remitterInfo['mobile'],
                            'partnerName' => $partnerName
                             );
                        try {
                                //echo "<pre>";print_r($paramApi); exit;
                                $api = new App_Api_Kotak_Remit_Transaction();
                                $resp = $api->creditAccount($paramApi);
                                $updateStatusArr = array();
                                $paramsBaseTxn = array(
                                    'remit_request_id' => $res,
                                    'product_id' => $params['product_id'],
                                    'amount' => $params['amount'],
                                    'bank_unicode' => $bank->bank->unicode,
                                    'agent_id' => $params['agent_id']
                                );
                                $remitanceStatus = $this->getRemitterRequestsInfo($res);
                                $datastatus = array();
                                switch ($resp) {
                                    case TRANSACTION_SUCCESSFUL:
                                        //Success
                                        $paramsBaseTxn['beneficiary_id'] = $params['beneficiary_id'];
                                        $paramsBaseTxn['txn_code'] = $txnCode;
                                        $paramsBaseTxn['fee_amt'] = $remittanceFee;
                                        $paramsBaseTxn['service_tax'] = $remittanceServiceTax;

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
                                            $paramsBaseTxn['reversal_fee_amt'] = $remittanceFee;
                                            $paramsBaseTxn['reversal_service_tax'] = $remittanceServiceTax;
                                            $baseTxn->remitFailure($paramsBaseTxn);
                                            $updateStatusArr['is_complete'] = FLAG_NO;
                                            $updateStatusArr['status'] = STATUS_FAILURE;
                                            $updateStatusArr['fund_holder'] = REMIT_FUND_HOLDER_OPS;
                                            $datastatus['status_new'] = STATUS_FAILURE;
                                            break;

                                        case TRANSACTION_NORESPONSE:
                                        case TRANSACTION_INVALID_RESPONSE_CODE:
                                        case TRANSACTION_TIMEOUT:
                                        case TRANSACTION_CHECKSUM_FAILED:
                                        case TRANSACTION_INVALID_RESPONSE:

                                            //No response
                                        $updateStatusArr['is_complete'] = FLAG_NO;
                                        $updateStatusArr['status'] = STATUS_HOLD;
                                        $updateStatusArr['fund_holder'] = REMIT_FUND_HOLDER_OPS;
                                        $updateStatusArr['hold_reason'] = $api->getMessage();
                                        $datastatus['status_new'] = STATUS_HOLD;
                                        break;
                                }
                                $datastatus['remittance_request_id'] = $res;
                                $datastatus['status_old'] = $remitanceStatus['status'];
                                $datastatus['by_remitter_id'] = $params['remitter_id'];
                                $datastatus['by_agent_id'] = $params['agent_id'];
                                $datastatus['by_ops_id'] = 0;
                                $datastatus['date_created'] = new Zend_Db_Expr('NOW()');
                                $resLog = $remittancestatuslog->addStatus($datastatus);
                                // SMS params
                                $smsData = array(
                                    'amount' => $params['amount'],
                                    //'nick_name' => $beniInfo['nick_name'],
                                    'bene_name' => $beniInfo['name'],
                                    //'remitter_name' => $remitterInfo['name'],
                                   // 'contact_email' => KOTAK_SHMART_EMAIL,
                                    //'contact_number' => KOTAK_CALL_CENTRE_NUMBER,
                                    'mobile' => $remitterInfo['mobile'],
                                    //'beneficiary_phone' => $beniInfo['mobile'],
                                    'ref_num' => $txnCode,
                                    //'account_number' => $txnCode,
                                    'response_status' => '',
                                   // 'product_name' => KOTAK_SHMART_TRANSFER,
                                    'product_id' => $params['product_id']
                                );
                                //echo  "<pre>"; print_r($smsData); exit;
                                //Success SMS

                                $updateStatusArr['cr_response'] = '(' . $api->getAccountCreditRespCode() . ') ' . $api->getAccountCreditRespMsg();
                                $updateStatusArr['final_response'] = $api->getMessage(TRUE);
                                $resUpdate = $this->updateReq($res, $updateStatusArr);
                                if ($resp == TRANSACTION_SUCCESSFUL) {
                                    //$m->kotakNeftSuccessRemitter($smsData);
                                    $smsData['response_status'] = STATUS_SUCCESS;
                                    $object->generateSMSDetails($smsData, $smsType = REMITTANCE_SUCCESS_REQUEST_SMS); 
                                    return array('status'=>TRANSACTION_SUCCESSFUL,'txncode'=>$txnCode);
                                }else if ($resp == TRANSACTION_NORESPONSE || $resp == TRANSACTION_CHECKSUM_FAILED || $resp == TRANSACTION_INVALID_RESPONSE || $resp == TRANSACTION_INVALID_RESPONSE_CODE || $resp == TRANSACTION_TIMEOUT) {
                                  //  $m->kotakInitiateRemitter($smsData);
                                    $smsData['response_status'] = $beniInfo['bank_account_number'];
                                    $object->generateSMSDetails($smsData, $smsType = REMITTANCE_NORESPONSE_REQUEST_SMS); 
                                    return array('status'=>TRANSACTION_NORESPONSE,'txncode'=>$txnCode);
                                }else if ($resp == TRANSACTION_FAILED || $resp == TRANSACTION_INVALID_PARAMS) {
                                  //  $m->kotakNeftFailureRemitter($smsData);
                                    $smsData['response_status'] = STATUS_FAILED;
                                    $object->generateSMSDetails($smsData, $smsType = REMITTANCE_FAILURE_REQUEST_SMS); 
                                    return array('status'=>TRANSACTION_FAILED,'txncode'=>$txnCode,'final_response'=>$updateStatusArr['final_response']);
                                }else{
                                   // return false;  
                                    return array('status'=>TRANSACTION_UNPROCESSED,'txncode'=>$txnCode);  

                                }


                            } catch (App_Exception $e) {
                                
                                $errMsg = $e->getMessage();
                                if($txnCode !=''){
                                  return array('status'=>TRANSACTION_UNPROCESSED,'txncode'=>$txnCode);  
                                }else{
                                return false;
                                }
                            } catch (Exception $e) {
                                $errMsg = $e->getMessage();
                                if($txnCode !=''){
                                  return array('status'=>TRANSACTION_UNPROCESSED,'txncode'=>$txnCode);  
                                }else{
                                return false;
                                }
                            }
                    }
                } catch (Exception $e) {
                            $errMsg = $e->getMessage();
                            if($txnCode !=''){
                                  return array('status'=>TRANSACTION_UNPROCESSED,'txncode'=>$txnCode);  
                                }else{
                                return false;
                               }
                        }
        
            }
 
        }
    }
    
    /* getRemitterRefundInfoByTxnCode() will return the remitters refund info
     * As param it will expect the txn_code
     */

    public function getRemitterRefundInfoByTxnCode($txn_code) {        

        $select = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_REMITTANCE_REFUND . " as ref", array('remitter_id', 'amount', 'status'))
                ->join(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as rr", "ref.remittance_request_id = rr.id", array('beneficiary_id', 'final_response'))
                ->where("ref.txn_code = ?", $txn_code);
        $row = $this->_db->fetchRow($select);

        return $row;
    }
    
    public function checkDuplicateTransRefNum($param){
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
        $select = $this->select();
        $select->from($this->_name, array('id'));
        $select->where('product_id = ?', $param['product_id']);
        $select->where('txnrefnum = ?', $param['txnrefnum']); 
        if($agentId > 0){
         $select->where('agent_id = ?', $agentId);   
        }
        $rs = $this->fetchRow($select);
        if( !empty($rs) ){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    /*
     * checkDeDupTransNum() checks duplicate txnrefnum from refund table
     */
    public function checkDeDupTransNum($param){
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_KOTAK_REMITTANCE_REFUND, array('id'));
        $select->where('product_id = ?', $param['product_id']);
        $select->where('txnrefnum = ?', $param['txnrefnum']); 
        if($agentId > 0){
         $select->where('agent_id = ?', $agentId);   
        }
        $rs = $this->_db->fetchRow($select);
        if( !empty($rs) ){
            return TRUE;
        }else{
            return FALSE;
        }
    }

public function getRemittanceRequestStatus($requestId) {
    	$select = $this->_db->select()
    	->from(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST . " as rr", array('status', 'remitter_id'));
    	$select->where("rr.id = ?", $requestId);

    	$rows = $this->_db->fetchRow($select);
    
    	return $rows;
    }
    
    public function updateRemittanceRequest($params) {
    	$user = Zend_Auth::getInstance()->getIdentity();
    	$objRemitStatusLog = new Remit_Kotak_Remittancestatuslog();
    	$requestId = $params['id'];
    	$prevData = $this->getRemittanceRequestStatus($requestId);
    	$old_status = $prevData['status'];
    	$remitter_id = $prevData['remitter_id'];
    	 
    	try {
    			$remitReqData = array('status' => $params['status'],
    			'date_updated' => new Zend_Db_Expr('NOW()'));
    			$this->_db->update(DbTable::TABLE_KOTAK_REMITTANCE_REQUEST, $remitReqData, "id = $requestId");
    			 
    			$remitReqLog = array('remittance_request_id' => $requestId,
    					'status_old' => $old_status, 'status_new' => $params['status'],
    					'by_ops_id' => $user->id,'by_agent_id' => $user->id,
    					'by_remitter_id' => $remitter_id,
    					'date_created' => new Zend_Db_Expr('NOW()'));
    			 
    			$objRemitStatusLog->addStatus($remitReqLog);
    	} catch (Zend_Exception $ze) {
    			App_Logger::log($e->getMessage(), Zend_Log::ERR);
    	}
    }
    
    public function remittanceByStaticOtp($userData)
    {
        try {
            
                $bankKotak = \App_DI_Definition_Bank::getInstance(BANK_KOTAK);
                $bankKotakUnicode = $bankKotak->bank->unicode;
                $maxTxn = $bankKotak->remit->otp->max_txn ;
                $otpLife = $bankKotak->remit->otp->life ;
                
                $mobNo = isset($userData['mobile'])?$userData['mobile']:'';  
                $checkStaticOTP = \Util::sendStaticOTP($userData['remitter_id'],$bankKotakUnicode);
                if($checkStaticOTP == ''){// new
                    //
                    if($userData['auth_code'] == ''){
                     $userData['auth_code'] = $this->generateRandom6DigitCode();
                    }
                    $otpUpdateArr = array(
                        'otp' => $userData['auth_code'],
                        'date_otp' => new \Zend_Db_Expr('NOW()'),
                    );
                        
                    $remitterObj = new \Remit_Kotak_Remitter();
                    $remitterObj->updateRemitter($otpUpdateArr, $userData['remitter_id']);
                   
                }
                else{
                    $userData['auth_code'] = $checkStaticOTP;
                }                         
                   // Sending sms to Remitter with beneficiary details              
                return $userData;                           
            
        } catch(\Exception $e) {
            \App_Logger::log($e->getMessage(), \Zend_Log::ERR);
            return false;
     }
   }

}
