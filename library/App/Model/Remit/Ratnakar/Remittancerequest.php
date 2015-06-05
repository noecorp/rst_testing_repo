<?php

/*
 * Ratnakar Remittance
 */

class Remit_Ratnakar_Remittancerequest extends Remit_Ratnakar {

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
    protected $_name = DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST;


     /* 
     * get remitters successful remittance for the product for the duration
     */
    public function getTxnRemitterOnSpecificDate($remitterId, $productId, $specificDate, $agentId, $beneId) {
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST, array('sum(amount) as total', 'sum(fee) as fee_total', 'sum(service_tax) as st_total'))
                ->where('remitter_id=?', $remitterId)
                ->where('product_id=?', $productId)
                ->where('beneficiary_id=?', $beneId)
                ->where("DATE(date_created) = '" . $specificDate . "'")
                ->where("agent_id = '".$agentId."'")
                ->where("status IN ('". STATUS_IN_PROCESS."','".STATUS_PROCESSED."','".STATUS_SUCCESS."','".STATUS_HOLD."','".STATUS_FAILURE."')")
                ->group("remitter_id");
        //echo $select->__toString();
        $row = $this->_db->fetchRow($select);
        return $row;
    }

    /*
     * get remitters successful remittance for the product for the duration
     */

    public function getTxnRemitterProductDuration($remitterId, $productId, $startDate, $endDate) {
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST, array('sum(amount) as total'))
                ->where('remitter_id=?', $remitterId)
                ->where('product_id=?', $productId)
                ->where("DATE(date_created) BETWEEN '" . $startDate . "' AND '" . $endDate . "'")
                ->where("status != '" . STATUS_REFUND ."'")
                ->group("remitter_id");
        //echo $select->__toString();
        $row = $this->_db->fetchRow($select);
        return $row;
    }
    
    public function getValidateTxnRemitterProductDuration($remitterId, $productId, $startDate, $endDate) {
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST, array('sum(amount) as total'))
                ->where('remitter_id=?', $remitterId)
                ->where('product_id=?', $productId)
                ->where("DATE(date_created) BETWEEN '" . $startDate . "' AND '" . $endDate . "'")
                ->where("status = '" . FLAG_SUCCESS . "' OR status = '" . STATUS_IN_PROCESS . "' OR status = '" . STATUS_PROCESSED . "' OR status = '" . STATUS_INCOMPLETE . "'")
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
    	
    	if(!isset($params['txn_code'])){
    		if ($reqId == 0 || empty($params)){
    			throw new Exception('Remittance request data missing!');
    		}
    	}

        if(isset($reqId) && $reqId > 0){
        	$this->update($params, "id='$reqId'");
        }else{
        	$txn_code=$params['txn_code'];
        	$this->update($params, "txn_code='$txn_code'");
        }

        return true;
    }


    /* getRemitterRefund() will return the refund details of remitter
     */

    public function getRemitterRefundCount($remitterId = 0) {
        if ($remitterId ==0)
            throw new Exception('Remitter Id not found');

        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('count(*) as count_refund_requests'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", "rr.beneficiary_id =b.id and b.status = '" . STATUS_ACTIVE . "'", array(''))
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
            throw new Exception('Remittance Refund data not found!');


        $add = $this->_db->insert(DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND, $data);

        return $add;
    }

    /* getRemitterRequestsInfo() will return the remitters requests info
     * As param it will expect the remitter request id
     */

    public function getRemitterRequestsInfo($remitterRequestId = 0) {
        if ($remitterRequestId == 0)
            throw new Exception('Remitter Request id not found!');

        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as krt", array('id','product_id', 'beneficiary_id', 'amount', 'remitter_id', 'txn_code', 'fee', 'service_tax','agent_id','status', 'utr', 'rat_customer_id','purse_master_id','customer_purse_id','bank_id'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS . ' as kr', "krt.remitter_id = kr.id",array('id as r_id','mobile', 'email','name as r_name'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as kb", "krt.beneficiary_id = kb.id", array('id as b_id','name','nick_name'))
                ->joinLeft(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as rc", "krt.rat_customer_id = rc.rat_customer_id", array('rc.customer_master_id'))
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
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('id as remittance_request_id', 'remitter_id', 'beneficiary_id', 'agent_id', 'ops_id', 'amount', 'date_created', 'sender_msg', 'product_id', 'fee', 'service_tax','txn_code','rbl_transaction_id','flag'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", "rr.beneficiary_id =b.id and b.status = '" . STATUS_ACTIVE . "'", array('b.name as beneficiary_name', 'nick_name', 'ifsc_code', $bankAccountNumber, 'branch_name', 'branch_city', $branchAddress, 'bank_account_type', $mobile, 'bank_name'));



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
            //Util::debug($retData);
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
                    $retNewData[$k]['batch_name'] = $retData[$j]['batch_name'];
                    $retNewData[$k]['utr'] = $retData[$j]['utr'];
                    $k++;
                    $retNewData[$k] = $alterData;
                    $retNewData[$k]['amount'] = $retData[$j]['fee'];
                    $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_FEE;
                    $retNewData[$k]['txn_code'] = $retData[$j]['txn_code'];
                    $retNewData[$k]['batch_name'] = $retData[$j]['batch_name'];
                    $retNewData[$k]['utr'] = $retData[$j]['utr'];
                    $retNewData[$k]['flag'] = $retData[$j]['flag'];
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
        $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as r", array('r.fee', 'r.service_tax', 'r.amount', 'r.id AS rmid', 'r.product_id', 'DATE(r.date_created) as date_created', 'r.date_created as txn_date', 'r.txn_code', 'r.status as txn_status', 'r.neft_remarks','r.batch_name','r.utr','r.batch_date','r.flag'));
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_RATNAKAR_RESPONSE_FILE." as res", "r.utr = res.utr" , array('res.rejection_code', 'res.rejection_remark', 'res.returned_date'));
        $select->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS . " as rem", "rem.id = r.remitter_id", array('rem.mobile as mobile_number', 'concat(rem.name," ",rem.last_name) as remit_name','rem.email as remitter_email', 'rem.date_created as remit_regn_date'));
        $select->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", "r.beneficiary_id = b.id ", array('b.name as bene_name', 'b.bank_name as bene_bankname', 'b.ifsc_code as bene_ifsccode',$bankAccountNumber));
        $select->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "r.product_id = p.id ", array('p.ecs_product_code','p.unicode as pro_unicode'));
        $select->joinLeft(DbTable::TABLE_AGENTS . " as a", "r.agent_id = a.id ", array('a.id as agent_id', 'a.agent_code', 'concat(a.first_name," ",a.last_name) as name','a.mobile1 as agent_mobile','a.email as agent_email', 'a.user_type as agent_user_type'));
        $select->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "a.id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", array('ad.estab_city', 'ad.estab_pincode', 'ad.bank_ifsc_code as agent_ifsc_code', 'ad.bank_account_number as agent_bank_account_number'));
        $select->where("r.status = '" . STATUS_IN_PROCESS . "' OR r.status =  '" . STATUS_SUCCESS . "' OR r.status = '" . STATUS_REFUND . "' OR r.status = '" . FLAG_FAILURE . "' OR r.status = '".STATUS_PROCESSED."' OR r.status = '".STATUS_HOLD."'");
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
            $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND , array('txn_code as refund_txn_code'));
            
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
        $chkCustPurseEmpty = isset($param['chk_custpurse_empty']) ? $param['chk_custpurse_empty'] : FALSE;        
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
        $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('sum(rr.amount) as agent_total_remittance', 'sum(rr.fee) as agent_total_remittance_fee', 'sum(rr.service_tax) as agent_total_remittance_stax', 'count(rr.id) as agent_total_remittance_count'));
        $select->setIntegrityCheck(false);
        if ($statusWhere == '') {
            $select->where("rr.status = '" . STATUS_IN_PROCESS . "' OR rr.status = '" . STATUS_HOLD . "' OR rr.status =  '" . STATUS_SUCCESS . "' OR rr.status = '" . STATUS_REFUND . "' OR rr.status = '" . FLAG_FAILURE . "' OR rr.status = '".STATUS_PROCESSED."'");
        } else {
            $select->where($statusWhere);
        }
        if ($agentId > 0) {
            $select->where("rr.agent_id = ? ", $agentId);
        }
        if ($chkCustPurseEmpty == TRUE) {
            $select->where("rr.customer_purse_id = 0 ");
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
        $chkCustPurseEmpty = isset($param['chk_custpurse_empty']) ? $param['chk_custpurse_empty'] : FALSE;        
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
                    DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", 
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
                    "' OR rr.status = '" . FLAG_FAILURE . "' OR rr.status = '".STATUS_PROCESSED."'");
        } else {
            $select->where($statusWhere);
        }
        if ($agentId > 0) {
            $select->where("rr.agent_id = ? ", $agentId);
        }
        if ($chkCustPurseEmpty == TRUE) {
            $select->where("rr.customer_purse_id = 0 ");
        }
        if ($to != '' && $from != '') {
            $select->where("DATE(rr.date_created) BETWEEN '$from' AND '$to'");
        } else {
            $select->where("DATE(rr.date_created) = ?", $date);
        }
        $select->group('DATE_FORMAT(rr.date_created, "%Y-%m-%d")'); 
       // echo $select."<br/><br/>";
        $row = $this->fetchAll($select);
        return $row;
    }

    /*  getAgentTotalRemittanceRefundSTax() function is responsible fetch data for agent total remitter refund and Service Tax amount 
     *  as params it will accept agent id and transaction date
     */

    public function getAgentTotalRemittanceRefundSTax($param) {

        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $date = isset($param['date']) ? $param['date'] : '';
        $chkCustPurseEmpty = isset($param['chk_custpurse_empty']) ? $param['chk_custpurse_empty'] : FALSE;        

        if ($date != '') {
            //Enable DB Slave
            $this->_enableDbSlave();
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND . ' as rr', array('sum(rr.amount) as agent_total_remittance_refund', 'sum(rr.service_tax) as agent_total_remittance_refund_stax', 'count(rr.id) as agent_total_remittance_refund_count', 'sum(rr.reversal_service_tax) as agent_total_reversal_refund_stax', 'sum(rr.reversal_fee) as agent_total_reversal_refund_fee'));
            if ($agentId > 0){
                $select->where('rr.agent_id=?', $agentId);
            }
            if ($chkCustPurseEmpty == TRUE) {
                $select->where("rr.customer_purse_id = 0 ");
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
    
    public function getAgentAllRemittanceRefundSTax($param) {

        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $chkCustPurseEmpty = isset($param['chk_custpurse_empty']) ? $param['chk_custpurse_empty'] : FALSE;        

        if ($from != '' && $to != '') {
            $select = $this->_db->select();
            $select->from(
                        DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND . ' as rr', 
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
            if ($chkCustPurseEmpty == TRUE) {
                $select->where("rr.customer_purse_id = 0 ");
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

    /* for cron
     * getAgentRemittanceDetails() will return remittance amt details for an agent on a particular date 
     * for a product for comm report purpose
     * As Params:- agent id, product id, query date
     */

    public function getAgentRemittanceDetails($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $productId = isset($param['product_id']) ? $param['product_id'] : '';
        $date = isset($param['date']) ? $param['date'] : '';
        $txn_id = isset($param['txn_id']) ? $param['txn_id'] : '';

        $select = $this->select();
        $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('rr.amount as transaction_amount', 'rr.fee as transaction_fee', 'rr.service_tax as transaction_service_tax', 'rr.txn_code as transaction_ref_no'));
        $select->setIntegrityCheck(false);
        
        if(!isset($param['txn_id'])){
            $select->join(DbTable::TABLE_RATNAKAR_REMITTANCE_STATUS_LOG . " as rsl", "rr.id = rsl.remittance_request_id AND status_new = '" . STATUS_SUCCESS . "'", array('rsl.date_created as date_success'));
            $select->where("rr.status =  '" . STATUS_SUCCESS . "'");
            $select->where("DATE(rsl.date_created) = ?", $date);
        }

        if ($agentId > 0){
            $select->where('rr.agent_id =?', $agentId);
        }
        if ($productId > 0){
            $select->where('rr.product_id=?', $productId);
        }

        if($txn_id > 0){
            $select->where('rr.id=?', $txn_id);
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
        $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND . " as rr", array('rr.amount as transaction_amount', 'rr.fee as transaction_fee', 'rr.service_tax as transaction_service_tax', 'rr.txn_code as transaction_ref_no'));
        $select->where("rr.status = '" . STATUS_SUCCESS . "'");
        $select->where("DATE(rr.date_created)='" . $date . "'");

        if ($agentId > 0) {
            $select->where('rr.agent_id =?', $agentId);
        }
        if ($productId > 0){
            $select->where('rr.product_id =?', $productId);
        }
        if($refund_txn_id > 0){
            $select->where('rr.remittance_request_id=?', $refund_txn_id);
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
        $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as r", array('r.id AS rmid', 'r.product_id', 'DATE(r.date_created) as date_created', 'r.amount', 'r.txn_code'));
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS . " as rem", "rem.id = r.remitter_id", array('rem.mobile as mobile_number', 'rem.name as rem_name'));
        $select->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as ben", "r.beneficiary_id = ben.id", array('ben.id as ben_id', 'ben.name as ben_name', 'concat(ben.address_line1,", ",ben.address_line2) as ben_address', $bankAccountNumber));
        $select->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "r.product_id = p.id ", array('p.ecs_product_code'));
        $select->joinLeft(DbTable::TABLE_BANK . " as b", "p.bank_id = b.id ", array('b.name as bank_name'));
        $select->where("r.status = '" . STATUS_IN_PROCESS . "' OR r.status = '".STATUS_PROCESSED."'");
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
        $objRemitStatusLog = new Remit_Ratnakar_Remittancestatuslog();
        foreach ($data as $requestId) {
            echo $requestId;
            try {
                $remitReqData = array('status' => STATUS_PROCESSED,
                    'batch_name' => $batchName,
                    'ops_id' => $user->id,
                    'fund_holder' => REMIT_FUND_HOLDER_NEFT);
                $this->_db->update(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST, $remitReqData, "id = $requestId");

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
        $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as r", array('r.id as remittance_request_id', 'remitter_id', 'beneficiary_id', 'agent_id', 'amount', 'status', 'status_sms'));
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
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('id', 'remitter_id', 'beneficiary_id', 'amount', 'date_created'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", "rr.beneficiary_id =b.id and b.status = '" . STATUS_ACTIVE . "'", array('b.name as beneficiary_name', $mobile, $email));

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
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('count(*) as count', 'sum(amount) as total'));
        $select->where("rr.remitter_id = ?", $remitterId);
        $select->where("rr.status != '" . STATUS_REFUND. "'");
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
            $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('rr.beneficiary_id', 'rr.amount', 'DATE(rr.date_created) as txn_date', 'rr.batch_name', 'rr.utr', 'rr.txn_code', 'rr.status'));
            $select->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS . " as r", "rr.remitter_id = r.id", array('r.name as remitter_name', 'DATE(r.date_created) as remitter_reg_date', 'mobile'));
            $select->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", "rr.beneficiary_id = b.id", array('b.name as bene_name', 'b.bank_name as bene_bank_name', 'b.ifsc_code as bene_ifsc_code'));
            //  $select->where("rr.remitter_id = ?", $remitterId);
            $select->where("rr.status = '" . STATUS_SUCCESS . "' OR rr.status = '" . STATUS_IN_PROCESS . "' OR rr.status = '" . STATUS_PROCESSED . "'");
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
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`rcc`.`card_number`,'".$decryptionKey."') as card_number"); 
	$crn = new Zend_Db_Expr("AES_DECRYPT(`rcc`.`crn`,'".$decryptionKey."') as crn"); 
	
        if ($toDate != '' && $fromDate != '') {

            $select = $this->_db->select();
            $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND . " as rr", array('amount as refund_amount', 'txn_code', 'date_created as refund_date', 'reversal_fee', 'reversal_service_tax', 'status'));
            $select->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS . " as rem", "rr.remitter_id = rem.id", array('rem.name as remitter_name', 'rem.email as remitter_email', 'rem.mobile as remitter_mobile_number'));
            $select->joinLeft(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rrq", "rr.remittance_request_id = rrq.id",array('neft_remarks as remarks','utr', 'txn_code as request_txn_code'));
            $select->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", "rrq.beneficiary_id = b.id", array('b.name as beneficiary_name', $bankAccountNumber));
            $select->joinLeft(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as rcc","rr.rat_customer_id = rcc.rat_customer_id AND rcc.rat_customer_id != 0 ", array($card_number,$crn));
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
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('sum(rr.amount) as total_amount', 'count(rr.id) as total_count', 'rr.date_created'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS . " as r", "rr.remitter_id = r.id", array('r.name as remitter_name', 'r.mobile as remitter_mobile_number', 'r.email as remitter_email'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", "rr.beneficiary_id =b.id", array('b.name as beneficiary_name', $bankAccountNumber, 'b.bank_name', 'b.ifsc_code', 'b.by_agent_id'))
                ->group('rr.beneficiary_id')
                ->group('DATE(rr.date_created)')
                ->where("DATE(rr.date_created) BETWEEN '" . $fromDate . "' AND '" . $toDate . "'")
                ->where("rr.status = '" . STATUS_SUCCESS . "' OR rr.status = '" . STATUS_IN_PROCESS . "' OR rr.status = '" . STATUS_PROCESSED . "'");
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

 

    /* getAgentRemittanceCountandSum() will return the agent's remittance requests count and sum
     * 
     *   */

    public function getAgentRemittanceCountandSum($param) {

        $from = $param['from'];
        $to = $param['to'];
        $agentId = $param['agentId'];
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('count(*) as count', 'sum(amount) as total'));
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
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('amount', 'DATE(date_created) as date_created'));
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

    public function getRemitterRequestsInfoByTxnCode($txn_code) {
        

        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('id','product_id', 'beneficiary_id', 'amount', 'remitter_id', 'txn_code', 'fee', 'service_tax','agent_id','status'))
                ->where("rr.txn_code = ?", $txn_code);
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
                    $alterData['batch_name'] = $retData[$j]['batch_name'];

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
                    $retNewData[$k]['batch_name'] = $retData[$j]['batch_name'];;
                    $k++;
                    $retNewData[$k] = $alterData;
                    $retNewData[$k]['amount'] = $retData[$j]['reversal_service_tax'];
                    $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_SERVICE_TAX;
                    $retNewData[$k]['txn_code'] = $retData[$j]['tran_ref_num'];
                    $retNewData[$k]['batch_name'] = $retData[$j]['batch_name'];
        $retNewData[$k]['flag'] = $retData[$j]['flag'];
                    $retNewData[$k]['utr'] = $retData[$j]['utr'];
                                
	$k++;
                    $retNewData[$k] = $alterData;
                    $retNewData[$k]['amount'] = $retData[$j]['reversal_fee'];
                    $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_REFUND_FEE;
                    $retNewData[$k]['txn_code'] = $retData[$j]['tran_ref_num'];
                    $retNewData[$k]['batch_name'] = $retData[$j]['batch_name'];
		$retNewData[$k]['flag'] = $retData[$j]['flag'];
                    $retNewData[$k]['utr'] = $retData[$j]['utr'];
                    
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
        
        $details = $this->_db->select();
        $details->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND . " as rr", array('rr.fee', 'rr.service_tax', 'rr.amount', 'rr.id as rfid', 'rr.remittance_request_id', 'DATE(rr.date_created) as date_created', 'rr.date_created as txn_date', 'rr.txn_code', 'rr.reversal_service_tax', 'rr.reversal_fee'));
        $details->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS . " as rem", "rem.id = rr.remitter_id", array('rem.mobile as mobile_number', 'concat(rem.name," ",rem.last_name) as remit_name','rem.email as remitter_email', 'rem.date_created as remit_regn_date'));
        $details->joinLeft(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as req", "req.id = rr.remittance_request_id ", array('req.product_id', 'req.neft_remarks', 'req.txn_code as tran_ref_num', 'req.status as txn_status', 'req.utr','req.batch_date','req.batch_name','req.flag'));
        $details->joinLeft(DbTable::TABLE_RATNAKAR_RESPONSE_FILE." as res", "req.utr = res.utr" , array('res.rejection_code', 'res.rejection_remark', 'res.returned_date'));
        $details->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", "req.beneficiary_id = b.id ", array('b.name as bene_name', 'b.bank_name as bene_bankname', 'b.ifsc_code as bene_ifsccode'));
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
        } else if(!empty($date)) {
            $details->where("DATE(rr.date_created) = ?", $date);
        }
        if ($mobileno > 0)
            $details->where("rem.mobile = ? ", $mobileno);
	
	if ($productId != '')
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
            $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST." as rr", array('rr.amount', 'rr.txn_code', 'DATE_FORMAT(rr.date_created, "%d-%m-%Y") as date_created','rr.status','rr.neft_remarks as remarks','rr.status','rr.batch_name','neft_remarks','manual_mapping_remarks','batch_date'));
            $select->joinLeft(DbTable::TABLE_RATNAKAR_RESPONSE_FILE." as res", "rr.utr = res.utr" , array('res.rejection_code', 'res.rejection_remark', 'res.returned_date', 'res.utr'));
            $select->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS." as rem", "rr.remitter_id = rem.id" , array('rem.name as remitter_name', 'rem.mobile as remitter_mobile_number'));
            $select->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES." as b", "rr.beneficiary_id = b.id" , array('b.name as beneficiary_name', $bankAccountNumber));
            $select->where("rr.status = '".STATUS_SUCCESS."' OR rr.status = '".STATUS_FAILURE."' OR rr.status = '".STATUS_REFUND."'");
            $select->where("date(rr.date_created) BETWEEN '".$fromDate."' AND '".$toDate."'");
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
        $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('amount', 'fee', 'service_tax', 'txn_code', 'sender_msg', 'date_created'));
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS ." as r", "rr.remitter_id = r.id", array('name', 'last_name'));
        if ($statusWhere == '') {
            $select->where("rr.status = '" . STATUS_IN_PROCESS . "' OR rr.status = '" . STATUS_HOLD . "' OR rr.status =  '" . STATUS_SUCCESS . "' OR rr.status = '" . STATUS_REFUND . "' OR rr.status = '" . FLAG_FAILURE . "' OR rr.status = '" . STATUS_INCOMPLETE . "' OR rr.status = '".STATUS_PROCESSED."'");
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
        $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND . ' as rr', array('amount', 'reversal_fee', 'reversal_service_tax', 'txn_code', 'date_created'));
        $select->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS ." as r", "rr.remitter_id = r.id", array('name', 'last_name'));
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
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('count(*) as count', 'sum(amount) as total','sum(rr.fee) as fee'));
        $select->where("rr.status = '" . FLAG_SUCCESS . "' OR rr.status = '" . STATUS_IN_PROCESS . "' OR rr.status = '" . STATUS_PROCESSED . "'");
        if ($agentId > 0)
            $select->where("rr.agent_id = ? ", $agentId);
        
        
        $select->where("DATE(rr.date_created) = ?", $date);
        //echo $select."<br>";
        $rows = $this->_db->fetchRow($select);
        return $rows;
    }
    
    /* gets the Ratnakar Remittance Failure Recon for in_process, hold and failure */
    public function getRatnakarRemitFailureRecon() {

        $seprator = ',';
        $ext = '.csv';
        $fileName = '';
        $agentUser = new AgentUser();
        
        $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");
        try{
        
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'".$decryptionKey."') as bank_account_number");
        
        $select = $this->select();
        $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as r", array('r.fee', 'r.service_tax', 'r.amount', 'r.id AS rmid', 'DATE(r.date_created) as date_created', 'r.date_created as txn_date', 'r.txn_code', 'r.status as txn_status', 'r.neft_remarks', 'r.date_updated as txn_updated'));
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS . " as rem", "rem.id = r.remitter_id", array('rem.mobile as mobile_number', 'concat(rem.name," ",rem.last_name) as remit_name'));
        $select->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", "r.beneficiary_id = b.id ", array('b.name as bene_name', $bankAccountNumber));
        $select->joinLeft(DbTable::TABLE_AGENTS . " as a", "r.agent_id = a.id ", array('a.id as agent_id', 'a.agent_code', 'concat(a.first_name," ",a.last_name) as agent_name', 'a.user_type as agent_user_type'));
        $select->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "a.id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", array('ad.estab_city'));
        $select->where("r.status = '" . STATUS_IN_PROCESS . "' OR r.status = '" . STATUS_HOLD . "' OR r.status = '" . FLAG_FAILURE . "' OR r.status = '".STATUS_PROCESSED."'");

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
        //$arrReport[$i]['hold_reason'] = ' Hold Reason ';
        
        $i = 1;
        if($recordCount >= 1)
        {
            foreach($criteria as $data){
                
                $reason = explode(')', $data['neft_remarks']);
                
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
                $arrReport[$i]['final_resp']  = $data['neft_remarks'];
                $arrReport[$i]['date_created']  = $data['date_created'];
                $arrReport[$i]['date_updated']  = $data['txn_updated'];
                //$arrReport[$i]['hold_reason']  = $data['hold_reason'];

               $i++; 
            }
        }
        
        $fileName = 'REMIT_Ratnakar_FAILURE_RECON_'.date('Y-m-d_h:i:s').$ext;
        $file->setBatch($arrReport, $seprator);
        $file->setFilepath(UPLOAD_PATH_REMIT_Ratnakar_FAILURE_RECON_REPORTS);
        $file->setFilename($fileName);
        $file->generate(TRUE); 

        //insert file info in t_files table
        $msg = $recordCount.' records has been found';
        $file->insertFileInfo(array('label'=>REMIT_Ratnakar_FAILURE_RECON_FILE, 'file_name'=>$fileName, 'date_start'=>date('Y-m-d'), 'date_end'=>date('Y-m-d'), 'status'=>STATUS_ACTIVE, 'comment'=>$msg, 'date_created'=>new Zend_Db_Expr('NOW()')));

        return $recordCount;
        }catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
        }
    }
    
     /* getNeftBatchForDD() will return neft batch names for drop down
     */
     public function getNeftBatchForDD($status)
    {
                
        $select  = $this->_db->select();        
        $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST, array('batch_name'));
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
    
    
     public function   getBatchFilesArray($params,$page = 1, $paginate = NULL, $force = FALSE){
       
        $from = $params['from_date'].' 00:00:00';
        $to = $params['to_date'].' 23:59:59';
        $amount = $params['amount'];
        
        $select = $this->_db->select(); 
        $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST." as r", 
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
    
      /*
         * Get the NEFT batch log count
         */
    public function   getBatchFilesCountArray($param){
       
        $batchcountArr = array();
        foreach($param as $batchName){
        $select = $this->_db->select();     
        $select->from(DbTable::TABLE_RAT_LOG_NEFT_DOWNLOAD." as nd",array( 'count(*) as dn_count'));              
        $select->where("nd.batch_name = ?",$batchName['batch_name']);
        $row = $this->_db->fetchRow($select); 
        $batchcountArr[] = $row;
        }
        return $batchcountArr;


    }
    
     /*
    * Get the Processed records 
    */
    public function getProcessedRecords($status = '', $page = 1, $paginate = NULL, $batch_name=''){

        $select = $this->sqlNeftRecords($batch_name, $status);
        return $this->_paginate($select, $page, $paginate);  
        
    }
     private function sqlNeftRecords($batchName = '',$status = '', $strReqId = '')
    {
        
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'".$decryptionKey."') as bank_account_number");
        $mobile = new Zend_Db_Expr("AES_DECRYPT(`b`.`mobile`,'".$decryptionKey."') as mobile");
        $email = new Zend_Db_Expr("AES_DECRYPT(`b`.`email`,'".$decryptionKey."') as email");
         
        $select = $this->select();
        $select->setIntegrityCheck(false);       
        $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST." as r");              
        $select->join(DbTable::TABLE_RATNAKAR_REMITTERS. " as rem", "rem.id = r.remitter_id", array('bank_account_number as remitter_bank_account_number'));
        $select->join(DbTable::TABLE_RATNAKAR_BENEFICIARIES. " as b", "b.id = r.beneficiary_id", array("ifsc_code", "name as beneficiary_name","name", $bankAccountNumber, "bank_account_type", "address_line1", $email, $mobile,"bank_name as bene_bank_name"));
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
     * updateRemitterResponseFromNEFT() will update the t_remittance_request and t_remittance_status log tables for neft response 
     * $params['rrId'] = $rrId;
        $params['status'] = $status;
        $params['neftRemarks'] = neftRemarks
     */
    public function updateRemitterResponseFromNEFT($params)
    {
        $objBaseTxn = new BaseTxn();
        $objRemitStatusLog = new Remit_Ratnakar_Remittancestatuslog();
        $user = Zend_Auth::getInstance()->getIdentity();
        $rrId = $params['rrId'];
        $status = $params['status'];
        $rrInfo = $this->getRemitterRequestsInfo($rrId);
        $remitterId = $rrInfo['remitter_id'];
        $bank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        try 
        {
//            $this->_db->beginTransaction(); 
            
            if($status == FLAG_SUCCESS)
            {
            
                $txnData = array('remit_request_id'=>$rrId, 
                    'beneficiary_id'=>$rrInfo['beneficiary_id'], 
                    'product_id'=>$rrInfo['product_id'], 'amount'=>$rrInfo['amount'], 'txn_code' => $rrInfo['txn_code'],'bank_unicode' => $bank->bank->unicode);
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
                    'reversal_fee_amt'=>$rrInfo['fee'], 'reversal_service_tax'=>$rrInfo['service_tax'],'bank_unicode' => $bank->bank->unicode);
                $txnResp = $objBaseTxn->remitFailure($txnData);
                $remitReqData = array('status'=>$status, 
                    'fund_holder'=>REMIT_FUND_HOLDER_OPS,
                    'is_complete'=>FLAG_NO, 
                    'neft_remarks' => $params['neftRemarks'], 
                    'status_sms' => FLAG_PENDING);
            }

            $this->_db->update(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST, $remitReqData, "id = $rrId");

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
    /*
    * Get the NEFT batch records 
    */
    public function getSelectedNeftRecords($strReqId){

        $select = $this->sqlNeftRecords('', '', $strReqId);
        return $this->fetchAll($select); 
        
    }
 /* getneftlog() will return NEFT log
      */
     public function getNEFTlog($batchName = 0){

            $batchName = isset($batchName)?$batchName:0; 
            $select = $this->select();    
            $select->setIntegrityCheck(false);
            $select->from(DbTable::TABLE_RAT_LOG_NEFT_DOWNLOAD." as nd");              
            $select->joinLeft(DbTable::TABLE_OPERATION_USERS." as ops", "ops.id =nd.ops_id", array('concat(ops.firstname," ",ops.lastname) as name'));
            $select->where("nd.batch_name = ?",  $batchName);
            return $this->fetchAll($select);  
       
       
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
         * Get the NEFT batch records 
         */
    public function getBatchRecords($batchName){

        $select = $this->sqlNeftRecords($batchName);
        return $this->_db->fetchAll($select); 
        
    } 
      /* getRemitterRequestsForNEFT() will return the remitters requests for neft for cron
     */
     public function getRemitterRequestsForNEFT(){
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'".$decryptionKey."') as bank_account_number");
        $branchAddress = new Zend_Db_Expr("AES_DECRYPT(`b`.`branch_address`,'".$decryptionKey."') as branch_address");
        $mobile = new Zend_Db_Expr("AES_DECRYPT(`b`.`mobile`,'".$decryptionKey."') as mobile");
        
        $select = $this->_db->select()       
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST." as rr", array('id as remittance_request_id', 'remitter_id','beneficiary_id', 'agent_id', 'ops_id', 'amount',))              
                ->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES." as b", "rr.beneficiary_id =b.id and b.status = '".STATUS_ACTIVE."'", array('b.name as beneficiary_name', 'ifsc_code', $bankAccountNumber, 'branch_name', 'branch_city', $branchAddress, 'bank_account_type', $mobile))
                ->where("rr.status = ?", STATUS_IN_PROCESS);
//        echo $select->__toString();
        $rows = $this->_db->fetchAll($select);      
     
        return $rows;
    }
    
     /*
     * updateRemitterRequestsForNEFT() will update the t_remittance_request and t_remittance_status log tables for neft updations
     */
    public function updateRemitterRequestsForNEFT($params)
    {
        
        if(!empty($params)){
           $objRemitStatusLog = new Remit_Ratnakar_Remittancestatuslog();
                    
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
                
                $this->_db->update(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST, $remitReqData, "id = $rrId");
                
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
      * updateRemitRequestsForNEFTBatch() will update requests of in process status to processed & will return count.
      */
       public function updateRemitRequestsForNEFTBatch(){
           
        //$user = Zend_Auth::getInstance()->getIdentity();
        //$dateFormat = date("dmyHis", time());
        $dateFormat = Util::getNeftBatchFileName();
        $batchName ='';
        $batchDate = new Zend_Db_Expr('NOW()');
        $objRemitStatusLog = new Remit_Ratnakar_Remittancestatuslog();
        $inprocessRequests = $this->getAgentRemittanceRequestsForNeftBatch();
        $totalRequests = count($inprocessRequests);
        /*
         * InProcess Request limit on the basis of amount 
         */
        $requestAmount = 0;
        $requestLimit = 0;
        $updateIDs = '';
         foreach($inprocessRequests as $remitRequest){
            if( ($requestAmount < RATNAKAR_REMITTANCE_MAX_AMOUNT_LIMIT_PER_BATCHFILE) && ($requestLimit < RATNAKAR_REMITTANCE_TXN_LIMIT_PER_BATCHFILE) ){
                  if(floatval($remitRequest['amount'])) {
                  $requestAmount += $remitRequest['amount']; 
                  $updateIDs .= $remitRequest['remittance_request_id'].",";
                  $requestLimit +=1;
                  }         

            }else{
              break;  
            }
         }
        $updateIDs = rtrim($updateIDs,","); // Remove last comma from Remittance request id list
         /*
          * Set Request limit
          */
         if($requestLimit > RATNAKAR_REMITTANCE_TXN_LIMIT_PER_BATCHFILE){
            $requestLimit = RATNAKAR_REMITTANCE_TXN_LIMIT_PER_BATCHFILE;
         }
        if($totalRequests>0){
                    
        $batchName = RATNAKAR_REMIT_BATCH_NAME_PREFIX.$dateFormat;
        
        $updateSql = 'UPDATE '.DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST.' SET batch_name="'.$batchName.'", batch_date='.$batchDate.'';
        $updateSql .= ', status="'.STATUS_PROCESSED.'", ops_id="'.TXN_OPS_ID.'", fund_holder="'.REMIT_FUND_HOLDER_NEFT.'"';
        $updateSql .= ' WHERE status="'.STATUS_IN_PROCESS.'" AND id IN ('.$updateIDs.') LIMIT '.$requestLimit;

      //  print $updateSql.PHP_EOL;
        $this->_db->query($updateSql);
                       
        foreach($inprocessRequests as $key =>$request){
                    try{
                       
                       $remitReqLog = array ('remittance_request_id' => $request['remittance_request_id'],
                           'status_old' => STATUS_IN_PROCESS, 'status_new' => STATUS_PROCESSED,
                           'by_ops_id' => TXN_OPS_ID ,'date_created' => new Zend_Db_Expr('NOW()'));
                      $objRemitStatusLog->addStatus($remitReqLog);
                    }catch(Zend_Exception $e){
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    }
                    if($key == $requestLimit){
                        break;
                    }
                } 
        }
        
        return $batchName;
     }
      public function sendNeftSms($params)
    {
        
        $m = new App\Messaging\Remit\Ratnakar\Agent();
        $countSuccess = 0;
        $countFailure = 0;
        $count = 0;
        if(!empty($params)){
            $objRemitStatusLog = new Remit_Ratnakar_Remittancestatuslog();
            
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
                $objRemitterModel = new Remit_Ratnakar_Remitter();
                $beneficiary = new Remit_Ratnakar_Beneficiary();
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
                        'contact_email' => RATNAKAR_REMITTANCE_EMAIL,
                        'contact_number' => RATNAKAR_CALL_CENTRE_NUMBER,
                        'remitter_phone' => $remitterArr->mobile,
                        'beneficiary_phone' => $beneficiaryPhone,
                        'product_name' => RATNAKAR_SHMART_REMIT);
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
                
                $this->_db->update(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST, $remitReqData, "id = $rrId");
                
            
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
    public function updateReqByTXNCode($txnCode, $params = array()) {

        $this->update($params, "txn_code = '$txnCode'");
        return true;
    }
    
     public function updateReqByUTR($utr, $params = array()) {

        $this->update($params, "utr = '$utr'");
        return true;
    }
    
    /*
    * Get the Un mapped records 
    */
    public function getUnMappedRecords($batch_name='',$txn_code = ''){
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'".$decryptionKey."') as bank_account_number");
        $mobile = new Zend_Db_Expr("AES_DECRYPT(`b`.`mobile`,'".$decryptionKey."') as mobile");
        $email = new Zend_Db_Expr("AES_DECRYPT(`b`.`email`,'".$decryptionKey."') as email");
         
        $select = $this->select();
        $select->setIntegrityCheck(false);       
        $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST." as r");              
        $select->join(DbTable::TABLE_RATNAKAR_REMITTERS. " as rem", "rem.id = r.remitter_id", array('bank_account_number as remitter_bank_account_number'));
        $select->join(DbTable::TABLE_RATNAKAR_BENEFICIARIES. " as b", "b.id = r.beneficiary_id", array("ifsc_code", "name as beneficiary_name","name", $bankAccountNumber, "bank_account_type", "address_line1", $email, $mobile,"bank_name as bene_bank_name"));
        if ($batch_name != ''){
            $select->where("r.batch_name = ?", $batch_name);
        }
        if ($txn_code != ''){
            $select->where("r.txn_code = ?", $txn_code);
        }
        $select->where("ISNULL(r.utr) OR r.utr = ''");
        $select->where("r.status_utr = ?",STATUS_PENDING);   
        
       

        $res =  $this->_db->fetchAll($select);
        
        return $res;
    
        
        
    }
    
     public function updateRemitterResponseManual($params)
    {
        $objBaseTxn = new BaseTxn();
        $objRemitStatusLog = new Remit_Ratnakar_Remittancestatuslog();
        $user = Zend_Auth::getInstance()->getIdentity();
        $rrId = $params['rrId'];
        $status = $params['status'];
        $rrInfo = $this->getRemitterRequestsInfo($rrId);
        $remitterId = $rrInfo['remitter_id'];
        $m = new App\Messaging\Remit\Ratnakar\Agent();
        $productModel = new Products();
        $bank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        try 
        {
//            $this->_db->beginTransaction(); 
                $objRemitterModel = new Remit_Ratnakar_Remitter();
                $beneficiary = new Remit_Ratnakar_Beneficiary();
                $remitRequestArr = $this->getRemitterRequestsInfo($rrId);
                $remitterArr = $objRemitterModel->findById($remitterId);
                $remitterName = substr($remitterArr->name, 0, 20);
                $amount = $remitRequestArr['amount'];
                $beneficiaryArr = $beneficiary->findById($remitRequestArr['beneficiary_id']);    
                $beneficiaryPhone = (isset($beneficiaryArr->mobile))?$beneficiaryArr->mobile:0;
           
           if($status == FLAG_SUCCESS)
            {
            
                $txnData = array('remit_request_id'=>$rrId, 
                    'beneficiary_id'=>$rrInfo['beneficiary_id'], 
                    'product_id'=>$rrInfo['product_id'], 'amount'=>$rrInfo['amount'], 'txn_code' => $rrInfo['txn_code'],'bank_unicode' => $bank->bank->unicode);
                $txnResp = $objBaseTxn->remitSuccess($txnData);
                $remitReqData = array(
                    'utr' => $params['utr'],
                    'utr_by_ops_id'=> $user->id,
                    'date_utr' => new Zend_Db_Expr('NOW()'),
                    'status_utr' => STATUS_MAPPED,
                    'status_response' => STATUS_PROCESSED,
                    'status_response_by_ops_id'=> $user->id,
                    'date_status_response' => new Zend_Db_Expr('NOW()'),
                    'status'=> $status, 
                    'fund_holder'=>REMIT_FUND_HOLDER_BENEFICIARY, 
                    'is_complete'=>FLAG_YES, 
                    'manual_mapping_remarks' => $params['neftRemarks'], 
                    'status_sms' => STATUS_SUCCESS);
                
                
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
                    $m->neftSuccessRemitter($dataArr);
                    if($beneficiaryPhone != 0){
                        $m->neftSuccessBeneficiary($dataArr);
                    }

                
            } // success ends
            else 
            {  

                 $productdetails = $productModel->getProductInfo($rrInfo['product_id']);
                  
                 $status = ($productdetails['const'] == PRODUCT_CONST_RAT_PAYU)? STATUS_REFUND: STATUS_FAILURE; 
                  
//                 if ($productdetails['const'] == PRODUCT_CONST_RAT_PAYU) {
//
//                        $txnData = array('remit_request_id' => $rrId,
//                            'product_id' => $rrInfo['product_id'], 'amount' => $rrInfo['amount'],
//                            'reversal_fee_amt' => $rrInfo['fee'], 'reversal_service_tax' => $rrInfo['service_tax'], 'bank_unicode' => $bank->bank->unicode,
//                            'rat_customer_id' => $rrInfo['rat_customer_id'], 'purse_master_id' => $rrInfo['purse_master_id'],
//                            'customer_purse_id' => $rrInfo['customer_purse_id']);
//
//                      $txnResp = $objBaseTxn->remitFailureAPI($txnData);
//
////                   Insert into rat_refund
//                        $refundArr = array(
//                        'remitter_id' => $rrInfo['remitter_id'],
//                        'remittance_request_id' => $rrInfo['id'],
//                        'rat_customer_id' => $rrInfo['rat_customer_id'],
//                        'purse_master_id' => $rrInfo['purse_master_id'],
//                        'customer_purse_id' => $rrInfo['customer_purse_id'],
//                        'agent_id' => $rrInfo['agent_id'],
//                        'product_id' => $rrInfo['product_id'],
//                        'amount' => $rrInfo['amount'],
//                        'fee' => $rrInfo['fee'],
//                        'service_tax' => $rrInfo['service_tax'],
//                        'reversal_fee' => 0,
//                        'reversal_service_tax' => 0,
//                        'txn_code' => $rrInfo['txn_code'],
//                        'status' => STATUS_SUCCESS,
//                        );
//                        $this->addRemittanceRefund($refundArr);
//                    } else{// failure
                $txnData = array('remit_request_id'=>$rrId, 
                    'product_id'=>$rrInfo['product_id'], 'amount'=>$rrInfo['amount'],
                    'reversal_fee_amt'=>$rrInfo['fee'], 'reversal_service_tax'=>$rrInfo['service_tax'],'bank_unicode' => $bank->bank->unicode);
                $txnResp = $objBaseTxn->remitFailure($txnData);
//            }
                
                $remitReqData = array(
                    'utr' => $params['utr'],
                    'utr_by_ops_id'=> $user->id,
                    'date_utr' => new Zend_Db_Expr('NOW()'),
                    'status_utr' => STATUS_MAPPED,
                    'status_response' => STATUS_REJECTED,
                    'status_response_by_ops_id'=> $user->id,
                    'date_status_response' => new Zend_Db_Expr('NOW()'),
                    'status'=>$status, 
                    'fund_holder'=>REMIT_FUND_HOLDER_OPS,
                    'is_complete'=>FLAG_NO, 
                    'manual_mapping_remarks' => $params['neftRemarks'], 
                    'status_sms' => STATUS_FAILURE);
                
                
                /*Send SMS to Remiiter
                     */
                      $dataArr = array('amount' => $amount, 'nick_name' => $beneficiaryArr->nick_name,'remitter_phone' => $remitterArr->mobile );
                      $m->neftFailureRemitter($dataArr);
            }

            $this->_db->update(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST, $remitReqData, "id = $rrId");

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
    
    
    
    /* getBeneficiaryException() will return the beneficiary exception details
     */

    public function getBeneficiaryException($param) {

        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'" . $decryptionKey . "') as bank_account_number");
        $toDate = isset($param['to']) ? $param['to'] : '';
        $fromDate = isset($param['from']) ? $param['from'] : '';

        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_TXN_BENEFICIARY . " as tb", array('sum(tb.amount) as total_amount', 'count(tb.id) as total_count'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", "tb.beneficiary_id =b.id", array('b.name as beneficiary_name', $bankAccountNumber, 'b.bank_name', 'b.ifsc_code', 'b.by_agent_id', 'b.address_line1', 'b.address_line2'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS . " as r", "b.remitter_id = r.id", array('r.name as remitter_name', 'r.mobile as remitter_mobile_number', 'r.address as remitter_address'))
                ->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "tb.product_id = p.id", array('p.name as product_name', 'p.ecs_product_code as product_code'))
                ->group('tb.beneficiary_id')
                ->group('DATE(tb.date_created)')
                ->where("DATE(tb.date_created) BETWEEN '" . $fromDate . "' AND '" . $toDate . "'")
                ->where("tb.txn_status = '" . STATUS_SUCCESS ."'")
                ->having("SUM(tb.amount) >= 100000");
        //echo $select->__toString();//exit;
        $rows = $this->_db->fetchAll($select);

        $rsCount = count($rows);
        $retData = array();
        $i = 0;
        
        if($rsCount > 0)
        {
            foreach($rows as $val)
            {
                $retData[$i]['remitter_name'] = $val['remitter_name'];
                $retData[$i]['remitter_mobile_number'] = $val['remitter_mobile_number'];
                $retData[$i]['remitter_address'] = $val['remitter_address'];
                $retData[$i]['beneficiary_name'] = $val['beneficiary_name'];
                $retData[$i]['total_amount'] = $val['total_amount'];
                $retData[$i]['total_count'] = $val['total_count'];
                $agentUser = new AgentUser();
                $usertype = $agentUser->getAgentDetailsById($val['by_agent_id']);
                $agentType = $agentUser->getAgentCodeName($usertype['user_type'], $val['by_agent_id']);
                $retData[$i] = array_merge($retData[$i], $agentType);
                $retData[$i]['agent_name'] = $usertype['first_name'].' '.$usertype['last_name'];
                $retData[$i]['agent_code'] = $usertype['agent_code'];                
                $retData[$i]['bank_name'] = $val['bank_name'];
                $retData[$i]['bank_account_number'] = $val['bank_account_number'];
                $retData[$i]['ifsc_code'] = $val['ifsc_code'];
                $retData[$i]['address_line1'] = $val['address_line1'];
                $retData[$i]['address_line2'] = $val['address_line2'];
                $retData[$i]['product_name'] = $val['product_name'];
                $retData[$i]['product_code'] = $val['product_code'];
                $i++;
            }
        }

        return $retData;
    }
    
   

    public function getAgentRemittanceSuccessToFailure($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $productId = isset($param['product_id']) ? $param['product_id'] : '';
        $date = isset($param['date']) ? $param['date'] : '';

        $select = $this->select();
        $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('rr.amount as transaction_amount', 'rr.fee as transaction_fee', 'rr.service_tax as transaction_service_tax', 'rr.txn_code as transaction_ref_no'));
        $select->setIntegrityCheck(false);
        $select->join(DbTable::TABLE_RATNAKAR_REMITTANCE_STATUS_LOG . " as rsl", "rr.id = rsl.remittance_request_id AND status_old = '" . STATUS_SUCCESS . "' AND status_new = '".STATUS_FAILURE."'", array('rsl.date_created as date_success'));
        $select->where("rr.status =  '" . STATUS_FAILURE . "' OR rr.status = '".STATUS_REFUND."'");
        $select->where("DATE(rsl.date_created) = ?", $date);

        if ($agentId > 0){
            $select->where('rr.agent_id =?', $agentId);
        }
        if ($productId > 0){
            $select->where('rr.product_id=?', $productId);
        }
        return $this->fetchAll($select);
    }
    
    /*
    * Get the records 
    */
    public function updateResponseByUTR($utrnumber ='',$updateData){
        
        if ($utrnumber != ''){
            
            try{

               $res = $this->_db->update(DbTable::TABLE_RATNAKAR_RESPONSE_FILE, $updateData, "utr = '".$utrnumber."'");
             } catch (Zend_Exception $e) {
                 App_Logger::log($e->getMessage(), Zend_Log::ERR); exit;
             }        

            if ($res){ 
                return TRUE;

            }else{
                return FALSE;
            }
        
        }
  
    }
    
   
     /* getRemitterRefund() will return the refund details of remitter
     */

    public function getRemittanceCountandSum($params) {
      if(($params['agent_id_list'] == '') || (empty($params['agent_id_list']) )){
          throw new Exception('No Record found');
      }

        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('count(*) as txn_count', 'sum(amount) as total_amount','agent_id','date_updated'))
                ->join(DbTable::TABLE_AGENTS . " as a", "rr.agent_id = a.id",array('user_type','agent_code','concat(a.first_name," ",a.last_name) as agent_name'))
                ->where("rr.agent_id IN (".$params['agent_id_list'].")")
                ->where("rr.status = '" . STATUS_IN_PROCESS . "' OR rr.status = '" . STATUS_HOLD . "' OR rr.status =  '" . STATUS_SUCCESS . "' OR rr.status = '".STATUS_PROCESSED."'")
                ->where("rr.date_updated >= '".$params['from']."' AND rr.date_updated <= '".$params['to']."'")
                ->group('DATE(rr.date_updated)')
                ->group('rr.agent_id')
                ->order('rr.date_updated')
                ->order('rr.agent_id');
//        echo $select->__toString();
        $row = $this->_db->fetchAll($select);
        return $row;
    }
    
     public function txncodeExists($txnCode){
        
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST,array('id','utr','status'))
                ->where("txn_code =?",$txnCode);
               

        $res =  $this->_db->fetchRow($select);
        
         if (!empty($res)) {
            return $res;
        } else {
            return FALSE;
        }
        
    }
    
      public function getRemitWalletTrialBalance($param) 
    {
        $arrReport = array();
        $fundingModel = new AgentFunding();
        $productId = isset($param['product_id']) ? $param['product_id'] : 0;
        $bankUnicode = isset($param['bank_unicode']) ? $param['bank_unicode'] : '';
        $bindproductModel = new BindAgentProductCommission();
        $agentBalance = new AgentBalance();
        $bankModel = new Banks();
        $productModel = new Products();
        
        
        $bankDetails = $bankModel->getBankbyUnicode($bankUnicode);
        $remitProductList = $productModel->getProgramProducts($bankDetails['id'],TYPE_REMIT);
        $remitProductIDList = array_keys($remitProductList);
       
  
        $param['product_id_list'] = ($productId == 0 )? implode(",",$remitProductIDList):$productId;
        
        
        $agentList = $bindproductModel->getProductAgents($param);
        
        $param['agent_id_list'] = $agentList;
        // Fund Requests approved
        $agentFunding = $fundingModel->getAgentTotalFund($param);
                         
      
        $agentFundingAmt = (!empty($agentFunding['total_agent_funding_amount']))? $agentFunding['total_agent_funding_amount']: 0;
                         
        $arrReport = array();
     
        $dateselected = strtotime($param['from']);
        $dayBefore = strtotime("-1 day", $dateselected);
        $dayBefore = date('Y-m-d',$dayBefore);
       
        $param['date'] = $dayBefore;
        
        $openingBal = $agentBalance->getAgentListClosingBalance($param);
       
        $arrReport['opening_bal'] = isset($openingBal['closing_balance'])?$openingBal['closing_balance']:0;
        
        
        
        $arrReport['agent_funds_approved'] = $agentFundingAmt;
       
        $neftReject = $this->getRemittanceRejectDetails($param);
        $neftReject = isset($neftReject['total_amount'])? $neftReject['total_amount']:0;
       
        
        $arrReport['neft_reject'] = $neftReject;
        
        $arrReport['sub_totalAD'] = $arrReport['opening_bal'] + $arrReport['agent_funds_approved'] + $arrReport['neft_reject'];
        $arrReport['sub_totalAD'] = Util::numberFormat($arrReport['sub_totalAD'],FLAG_NO);
        
        
        $remitDetails = $this->getRemittanceTxns($param);
        $arrReport['txn_total'] = isset($remitDetails['total_amount'])? $remitDetails['total_amount']:0;
       
        
        $arrReport['sub_totalFH'] = Util::numberFormat($arrReport['txn_total'],FLAG_NO);
        
        $arrReport['sub_totalEI'] = $arrReport['sub_totalAD'] + $arrReport['sub_totalFH'];
        $arrReport['sub_totalEI'] = Util::numberFormat($arrReport['sub_totalEI'],FLAG_NO);
        
        
        $arrReport['txn_fee'] = isset($remitDetails['total_fee'])? $remitDetails['total_fee']:0;
        $arrReport['txn_service_tax'] = isset($remitDetails['total_service_tax'])? $remitDetails['total_service_tax']:0;
        
        $refundDetails = $this->getRefundTxns($param);
        $arrReport['txn_reversal_fee'] = isset($refundDetails['total_fee'])? $refundDetails['total_fee']:0;
        $arrReport['txn_reversal_service_tax'] = isset($refundDetails['total_service_tax'])? $refundDetails['total_service_tax']:0;
        
        $arrReport['sub_totalLO'] = $arrReport['txn_fee'] + $arrReport['txn_service_tax'] + $arrReport['txn_reversal_fee'] + $arrReport['txn_reversal_service_tax'];
        
        
        $arrReport['refund_amount'] = '';isset($refundDetails['total_amount'])? $refundDetails['total_amount']:0;
       
        $unclaimed = $this->getUnclaimedDetails($param);
        $arrReport['refund_yet_to_claim'] = isset($unclaimed['total_amount'])? $unclaimed['total_amount']:0;
       
        
        $unprocessedTxn = $this->getNonProcessedTxns($param);
        
        $arrReport['unprocessed_txn'] = isset($unprocessedTxn['total_amount'])? $unprocessedTxn['total_amount']:0;
        
        
        $arrReport['sub_totalU'] = $arrReport['sub_totalLO'] + $arrReport['refund_amount'] + $arrReport['refund_yet_to_claim'] + $arrReport['unprocessed_txn'];
       
        $todateselected = $param['to'];
        $todateselected = explode(" ",$todateselected);
        $param['date'] = $todateselected[0];
        
        $closingBal = $agentBalance->getAgentListClosingBalance($param);
        $arrReport['closing_balance'] =  isset($closingBal['closing_balance'])?$closingBal['closing_balance']:0;
        
        $arrReport['difference'] = $arrReport['closing_balance'] - $arrReport['sub_totalU'];
        
        return $arrReport;
   
    }
    
    
    public function getRemittanceRejectDetails($params) {

        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_STATUS_LOG . " as rsl",array('remittance_request_id'))
                ->distinct(TRUE)
                ->where("rsl.status_new = '" . STATUS_FAILURE . "' OR rsl.status_new = '" . STATUS_REFUND . "' ")
                ->where("rsl.date_created >= '".$params['from']."' AND rsl.date_created <= '".$params['to']."'");
        
        $row = $this->_db->fetchAll($select);
        $dataArray = array();
        foreach ($row as $val) {
            $dataArray[] = $val['remittance_request_id'];
        }
        $remitreqID = implode(",",$dataArray);
        $res  = $this->getremittanceDetailsByRemitReqID($remitreqID);
            
        return $res;
    }

     public function getRemittanceTxns($params) {
     
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array( 'sum(amount) as total_amount','sum(fee) as total_fee','sum(service_tax) as total_service_tax'))
                ->where("rr.status = '" . STATUS_IN_PROCESS . "' OR rr.status = '" . STATUS_SUCCESS . "' OR rr.status =  '" . STATUS_PROCESSED . "' OR rr.status = '".STATUS_FAILURE."' OR rr.status = '".STATUS_REFUND."'")
                ->where("rr.date_created >= '".$params['from']."' AND rr.date_created <= '".$params['to']."'");
        $row = $this->_db->fetchRow($select);
        return $row;
    }
    
     public function getNonProcessedTxns($params) {
     
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array( 'sum(amount) as total_amount'))
                ->where("rr.status = '" . STATUS_IN_PROCESS . "' ")
                ->where("rr.date_created >= '".$params['from']."' AND rr.date_created <= '".$params['to']."'");
        $row = $this->_db->fetchRow($select);
        return $row;
    }
         public function getRefundTxns($params) {
     
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND . " as rr", array( 'sum(amount) as total_amount','sum(reversal_fee) as total_fee','sum(reversal_service_tax) as total_service_tax'))
                ->where("rr.status = '" . STATUS_SUCCESS . "'")
                ->where("rr.date_created >= '".$params['from']."' AND rr.date_created <= '".$params['to']."'");
        $row = $this->_db->fetchRow($select);
        return $row;
    }
    
    public function getRemittanceSum($params) {
      if($params['agent_id_list'] == ''){
          throw new Exception('No Record found');
      }

        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('count(*) as count', 'sum(amount) as total'))
                ->join(DbTable::TABLE_AGENTS . " as a", "rr.agent_id = a.id",array('user_type'))
                ->where("rr.agent_id IN (".$params['agent_id_list'].")")
                ->where("rr.status = '" . STATUS_IN_PROCESS . "' OR rr.status = '" . STATUS_HOLD . "' OR rr.status =  '" . STATUS_SUCCESS . "' OR rr.status = '".STATUS_PROCESSED."'")
                ->where("rr.date_created >= '".$params['from']."' AND rr.date_created <= '".$params['to']."'")
                ->order('rr.agent_id');
        $row = $this->_db->fetchRow($select);
        
        return $row;
    }
     public function getUnclaimedDetails($params) {
        
        $select = "select remittance_request_id from rat_remittance_status_log where"
                . " (status_new = '".STATUS_FAILURE."' ) and date_created > '".$params['from']."' AND date_created < '".$params['to']."'"
                . " AND remittance_request_id NOT IN (select remittance_request_id from rat_remittance_status_log where status_new = '".STATUS_REFUND."' and date_created > '".$params['from']."' AND date_created < '".$params['to']."')";
        
        $row = $this->_db->fetchAll($select);
        
        $dataArray = array();
        foreach ($row as $val) {
            $dataArray[] = $val['remittance_request_id'];
        }
        $remitreqID = implode(",",$dataArray);
        $res  = $this->getremittanceDetailsByRemitReqID($remitreqID);
            
        return $res;
    }

    public function getremittanceDetailsByRemitReqID($remitreqID){
        $res = '';
        if($remitreqID != ''){
        $detail = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array( 'sum(amount) as total_amount','sum(fee) as total_fee','sum(service_tax) as total_service_tax'))
                ->where("rr.id IN ($remitreqID)");
        
        $res = $this->_db->fetchRow($detail);
        }
        return $res;
      
        }
        
/*    public function instatntTransfer($params){
        $user = Zend_Auth::getInstance()->getIdentity();    
        $beneficiary = new Remit_Ratnakar_Beneficiary();
        $remittancestatuslog = new Remit_Ratnakar_Remittancestatuslog();
        $m = new App\Messaging\Remit\Ratnakar\Agent();
        $beneId = ($params['beneID']> 0) ? $params['beneID'] : 0;
        $amount = ($params['amount']> 0) ? $params['amount'] : 0;
        
        $detail = $beneficiary->getBeneficiaryDetails($beneId);
        
        $feeplan = new FeePlan();
        $feeArr = $feeplan->getRemitterFee($user->product_id, $user->id);
        $bank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $session = new Zend_Session_Namespace('App.Agent.Controller');
        
        $formdata['amount'] = $amount;
        //$formdata['sender_msg'] = $this->_getParam('sender_msg');
                 
        try {
        		$txnResponse = array();
        		$txnResponse['isPosted']=false;
        		$txnResponse['success']=false;
        		
                $user = Zend_Auth::getInstance()->getIdentity();
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
                $param = array('agent_id' =>$user->id,
                               'product_id' =>$user->product_id,
                               'remitter_id' =>$session->remitter_id,
                               'amount' =>$amount,
                               'fee_amt' =>$feeComponent['partialFee'],
                               'service_tax' =>$feeComponent['serviceTax'],
                				'bank_unicode' => $bank->bank->unicode,
                               );
                //Fund transfer limit on the basis of Agent limit and product limit
                if ($this->chkAllowRemit($param)){
                    //If fee is assigned for the product assigned to the Agent for the day
                    if(empty($feeArr)){
                        //print_r( array('msg-error' => 'Product not assigned to agent for the day',) );
                        echo '<div class="msg msg-error"><p>Product not assigned to agent for the day</p></div>'; exit;
                        
                    }
                }
         
                $data = array();
                $data['amount'] = $formdata['amount'];
                $data['fund_holder'] = REMIT_FUND_HOLDER_REMITTER;
                $data['agent_id'] = $user->id;
                $data['remitter_id'] = $session->remitter_id;
                $data['beneficiary_id'] = $beneId;
                $data['ops_id'] = TXN_OPS_ID;
                $data['product_id'] = $user->product_id;
                $data['date_created'] = new Zend_Db_Expr('NOW()');
                $data['fee'] = $feeComponent['partialFee'];
                $data['service_tax'] = $feeComponent['serviceTax'];
                $data['status'] = STATUS_INCOMPLETE;
                $data['sender_msg'] = $formdata['sender_msg'];

                $res = $this->save($data);
                

                if($res > 0 ){

                $datastatus = array();
                $datastatus['remittance_request_id'] = $res;
                $datastatus['status_old'] = '';
                $datastatus['status_new'] = STATUS_INCOMPLETE;
                $datastatus['by_remitter_id'] = $session->remitter_id;
                $datastatus['by_agent_id'] = $user->id;
                $datastatus['by_ops_id'] = TXN_OPS_ID;
                $datastatus['date_created'] = new Zend_Db_Expr('NOW()');

                $resLog = $remittancestatuslog->addStatus($datastatus); 
                    
                    $paramsArr = array('agent_id' =>$user->id,
                            'product_id' =>$user->product_id,
                            'remitter_id' =>$session->remitter_id,
                            'amount' =>$formdata['amount'],
                            'remit_request_id' =>$res,
                            'fee_amt' =>$feeComponent['partialFee'],
                            'service_tax' =>$feeComponent['serviceTax'],
                            'bank_unicode' => $bank->bank->unicode
                        );       
                    $txnCode = $this->initiateRemit($paramsArr);
                    
                    if($txnCode){
                        $remitters = new Remit_Ratnakar_Remitter();
                        $remitter_data = $remitters->getRemitterById($session->remitter_id);
                        
                        $beneficiary = new Remit_Ratnakar_Beneficiary();
                        $beneficiariesList = $beneficiary->getBeneficiaryDetails($beneId);
						
						$callRblAPI = FALSE;
					    
                       
                      
	
						
			//if($flag == 3 &&  date('H') >= 8 && date('H') <= 18  && (date('D') != 'Sun') && (date('D') == 'Sat' && date('H') < 12 )) {
			if($flag == 3 &&  (date('H') >= 8 && date('H') <= 18  && (date('D') != 'Sun' && date('D') != 'Sat')) || 
                ($flag == 3 && date('D') == 'Sat' && date('H') < 12 ) ) {

							 
							$callRblAPI = TRUE;
						} else {
							$status = STATUS_IN_PROCESS;
						}
						
						if($flag == 2) {
							$callRblAPI = TRUE;
						}
						
			
						if($callRblAPI){
							$dataToApi = array('header' => array('sessiontoken' => $session->rblSessionID),
											'bcagent' => $user->bcagent,
											'remitterid' => $remitter_data['remitterid'],
											'beneficiaryid' => $beneficiariesList['beneficiary_id'],
											'amount' => $data['amount'],
											'remarks' => 'Transfer',
											'cpid' => 36,
											'channelpartnerrefno' => $txnCode,
											'flag' => $flag
							   );
	
							$rblApiObject = new App_Rbl_Api();
							$response = $rblApiObject->transaction($dataToApi);
							
							if(isset($response['status'])) {
								if($response['status'] == 1 && $flag == 2) {
									$status = STATUS_SUCCESS;
								}
								else if($response['status'] == 1 && $flag == 3){
									$status = STATUS_PROCESSED;
								}
								else {
									//for all other status mark as failure
									$status = STATUS_FAILURE;
									if(isset($response['NPCIResponsecode'])){
										$npciResponseCode = $response['NPCIResponsecode'];
										if(in_array($npciResponseCode,array(8,91,12))) {
											//these transactions with error codes have to be marked as Hold
											$status = STATUS_HOLD;
										}
									}
								}
							}
							
						}
                        
			//error_log('BANK REF NO:' .$response['bankrefno']);

                        $updateArr = array(
                            'status'        => $status,
                            'fund_holder'   => REMIT_FUND_HOLDER_OPS,
                            'txn_code'      => $txnCode,
							'flag' => $flag,
                            'rbl_transaction_id' => isset($response['RBLtransactionid']) ? $response['RBLtransactionid'] : '',
                        	'utr' => isset($response['bankrefno']) ? substr($response['bankrefno'],0,16) : ''
                        );
                        
                        $resUpdate = $this->updateReq($res,$updateArr);
						
						if($callRblAPI){
							$datastatus = array();
							$datastatus['remittance_request_id'] = $res;
							$datastatus['status_old'] = 'processed';
							$datastatus['status_new'] = $status;
							$datastatus['by_remitter_id'] = $session->remitter_id;
							$datastatus['by_agent_id'] = $user->id;
							$datastatus['by_ops_id'] = TXN_OPS_ID;
							$datastatus['date_created'] = new Zend_Db_Expr('NOW()');
							$resLog = $remittancestatuslog->addStatus($datastatus); 
							
							$smsData = array( 'beneficiary_name' => $detail['nick_name'],
							'amount' => $amount,'mobile' => $session->remitter_mobile_number);
	
							//$m->neftInitiateRemitter($smsData);  
						}
                   
                    
                    }

					if($status == STATUS_SUCCESS || $status == STATUS_PROCESSED){
							$txnStatusToDisplay = 'SUCCESS';
						}else{
							$txnStatusToDisplay = 'FAILURE';
						}

                        //$txnStatusToDisplay = ($status == STATUS_SUCCESS) ? 'SUCCESS' : 'FAILURE';
                        $messageOnSubmit = "";

                        //isLast, $totalAmount
						if($callRblAPI){
								$txnResponse['isPosted'] = true;
		                        if($txnStatusToDisplay == 'SUCCESS'){
		                        	$txnResponse['success'] = true;
	                	        }else{
	                	        	$txnResponse['success'] = false;
	                        	}
						}else{
							$txnResponse['isPosted'] = false;
							$txnResponse['success'] = true;
						}

						return $txnResponse;

                       // echo '<div class="msg-success"><p>Your request has been submitted, the beneficiarys account will be credited soon & you will get an sms regarding the success/failure</p></div>'; 
                        
                } else {
                    if($isLast){
                        return '<div class="msg msg-error"><p>Your request for fund transfer could not be initiated</p></div>'; 
                    }
                }
            } catch (Exception $e) {
    
                    $errMsg = $e->getMessage();
                    echo '<div class="msg msg-error"><p>'.$errMsg.'</p></div>';
                    //App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    exit;
            }
    }*/
     /* getRemittanceReqInfo() will return the remittance requests info
     * As param it will expect the txn_code
     */

    public function getRemittanceReqInfo($txnCode) {
        if ($txnCode == '')
            throw new Exception('Remittance Request not found!');

        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as krt", array('id','product_id', 'beneficiary_id', 'amount', 'remitter_id', 'txn_code', 'fee', 'service_tax','agent_id','status'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS . ' as kr', "krt.remitter_id = kr.id",array('id as r_id','mobile', 'email','name as r_name'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as kb", "krt.beneficiary_id = kb.id", array('id as b_id','name','nick_name'))
                ->where("krt.txn_code = ?", $txnCode);
//        echo $select->__toString();exit;
        $row = $this->_db->fetchRow($select);

        return $row;
    }
    
    
    
     public function transactionHistory($remitterId) {
            
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('rr.beneficiary_id', 'rr.amount', 'DATE(rr.date_created) as txn_date', 'rr.batch_name', 'rr.utr', 'rr.txn_code', 'rr.status', 'rr.sender_msg'));
            $select->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS . " as r", "rr.remitter_id = r.id", array('r.name as remitter_name', 'DATE(r.date_created) as remitter_reg_date', 'mobile'));
            $select->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", "rr.beneficiary_id = b.id", array('b.name as bene_name', 'b.bank_name as bene_bank_name', 'b.ifsc_code as bene_ifsc_code'));
            $select->where("rr.remitter_id = ?", $remitterId);
            $select->where("rr.status = '" . STATUS_SUCCESS . "' OR rr.status = '" . STATUS_REFUND . "'");
            $select->limit(TXN_HISTORY_COUNT);
            //echo $select->__toString();exit;
            return $this->_db->fetchAll($select);
        
    }
     public function remittanceTransaction($params) {
        
        $remittancestatuslog = new Remit_Ratnakar_Remittancestatuslog();
        $bank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $custModel = new Corp_Ratnakar_Cardholders();
        $ratCustomerId = $params['rat_customer_id'];
        $productId = $params['product_id'];
        $bankObject   = new Banks();
        $productModel = new Products();
        $productdetails = $productModel->getProductInfo($productId);
        $product = App_DI_Definition_BankProduct::getInstance($params['bank_product_const']);
        $genWalletCode = $product->purse->code->genwallet; 
        $masterPriPurseDetails = $masterPurseModel->getRemitPurseInfo($productId, 'priority');
        if(!empty($masterPriPurseDetails)){
                    $priorityWalletCode = $masterPriPurseDetails[0]['code'];
        }else{
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_REMITTANCE_NOT_ALLOWED_MSG, ErrorCodes::ERROR_EDIGITAL_REMITTANCE_NOT_ALLOWED_CODE);
        }
        $pursecode = ($params['wallet_code'] != '') ? $params['wallet_code'] : $priorityWalletCode;
        $flag = array();
        $flag['txn_code'] = '';
        $flag['response'] = FALSE;
        $requireECS = FLAG_NO;
        try{
        $masterPurseDetails = $masterPurseModel->getPurseIdByPurseCode($pursecode);

        if($ratCustomerId > 0) { 
            if(!empty($masterPurseDetails)){
                if($masterPurseDetails['allow_remit'] == FLAG_YES){
                    $purseDetails = $custPurseModel->getCustPurseDetails(array('rat_customer_id' => $ratCustomerId, 'purse_master_id' => $masterPurseDetails['id']));
                } else {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_REMITTANCE_NOT_ALLOWED_MSG, ErrorCodes::ERROR_EDIGITAL_REMITTANCE_NOT_ALLOWED_CODE);
                }
           $customerPurseId = (isset($purseDetails['id']) && $purseDetails['id'] > 0) ? $purseDetails['id'] : 0;
           }else{
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_CODE);
                }
           }
         $purseMasterId = $masterPurseDetails['id'];
        // add remittance req
         
         $params['purse_master_id'] = $purseMasterId;
         $params['customer_purse_id'] = $customerPurseId;
         $params['amount'] = Util::convertToRupee($params['amount']);
         // Check Allow remit
         $bankInfo = $bankObject->getBankidByProductid($params['product_id']);
         $params['bank_id'] = $bankInfo['bank_id'];
         
         
         //******** Getting Customer Detail Including CardDetail
         $searchArr = array(
             'product_id'=> $params['product_id'],
             'rat_customer_id' => $params['rat_customer_id'],
             'customer_master_id' => $params['customer_master_id'],
             'status' => STATUS_ACTIVE,
         );
         $cardholderDetails = $custModel->getCardholderInfo($searchArr);
         
         if(!isset($cardholderDetails->id) || $cardholderDetails->id == '')
            {
             throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE);
            }
        $custCardNumber = ($cardholderDetails->card_number != '') ? $cardholderDetails->card_number : '';    
         $agent_id = $params['agent_id'];
         
         // Validating ECS Request
        
         if( (strtolower($pursecode) == strtolower($genWalletCode) ) && ($custCardNumber !='') ){
            
             $requireECS = FLAG_YES;
         }
         
         $limitParams = array( 'agent_id' => $params['agent_id'],
                               'product_id' =>$params['product_id'],
                               'remitter_id' => $params['remitter_id'],
                               'amount' => $params['amount'],
                               'purse_master_id' => $masterPurseDetails['id'],
                               'customer_purse_id' => $customerPurseId,
                               'rat_customer_id' => $ratCustomerId,
                               'customer_master_id' => $params['customer_master_id'],
                               'fee_amt' => 0,
                               'service_tax' => 0,
                               'bank_unicode' => $bank->bank->unicode
                               );
                        //Fund transfer limit on the basis of remitter limit and purse level limit added in config
                        if ($this->chkAllowRemitAPI($limitParams)){
                                
                                $txncode = new Txncode();
                                if ($txncode->generateTxncode()){
                                  $txnCode = $txncode->getTxncode();  
                                }
                               
                                $params['txn_code'] = $txnCode ;   
                                    
                                $res = $this->save($params);
                                $datastatus = array();
                                $datastatus['remittance_request_id'] = $res;
                                $datastatus['status_old'] = '';
                                $datastatus['status_new'] = STATUS_INCOMPLETE;
                                $datastatus['by_remitter_id'] = $params['remitter_id'];
                                $datastatus['by_agent_id'] = $params['agent_id'];
                                $datastatus['by_ops_id'] = 0;
                                $datastatus['date_created'] = new Zend_Db_Expr('NOW()');

                                $resLog = $remittancestatuslog->addStatus($datastatus);
                                
                                if($res > 0 ){
                                    
                                    $txn_load_id = 0; // Define Default Txn Load ID
                                  
                                  if($txnCode){
                                        
                                        $flag['response'] = TRUE;
                                        $flag['txn_code'] = $txnCode;
                                        
                                        $updateArr = array(
                                            'status'        => STATUS_IN_PROCESS,
                                            'fund_holder'   => REMIT_FUND_HOLDER_OPS,
                                            'txn_code'      => $txnCode
                                        );
                                      
                                        // Default Remittance Log records
                                        
                                        $datastatus = array();
                                        $datastatus['remittance_request_id'] = $res;
                                        $datastatus['status_old'] = STATUS_INCOMPLETE;
                                        $datastatus['status_new'] = STATUS_IN_PROCESS;
                                        $datastatus['by_remitter_id'] = $params['remitter_id'];
                                        $datastatus['by_agent_id'] = $params['agent_id'];
                                        $datastatus['by_ops_id'] = TXN_OPS_ID;
                                        $datastatus['date_created'] = new Zend_Db_Expr('NOW()');
                                      
                                      // ******** ECS call for card holder *********** //
                                        $ecsApi = new App_Socket_ECS_Corp_Transaction();
                                        $ecsCall = FALSE;
                                        if( ($requireECS == FLAG_YES) && ($custCardNumber!='') && ($agent_id!='') ){
                                            $ecsCall = TRUE;
                                            $amount = $params['amount'];
                                            $cardLoadData = array(
                                                    'amount' => $amount,
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
                                                $apiResp = $ecsApi->cardDebit($cardLoadData); // bypassing for testing
                                            }
                                        }else{
                                         $apiResp = TRUE; 
                                         $ecsCall = FALSE;
                                       }                                       
                                             if ($apiResp === TRUE) {
                                                 $txn_load_id = $ecsCall == TRUE ? $ecsApi->getISOTxnId() : '';
                                                 $updateArr['txn_load_id'] = $txn_load_id;
                                                 
                                                 $paramsArr = array(
                                                'customer_master_id' => $params['customer_master_id'],
                                                'purse_master_id' => $masterPurseDetails['id'],
                                                'customer_purse_id' => $customerPurseId,
                                                'agent_id' => $params['agent_id'],
                                                'product_id' =>$params['product_id'],
                                                'remitter_id' => $params['remitter_id'],
                                                'amount' => $params['amount'],
                                                'remit_request_id' => $res,
                                                'fee_amt' => 0,
                                                'service_tax' => 0,
                                                'bank_unicode' => $bank->bank->unicode,
                                                'bank_id' => $bankInfo['bank_id'],
                                                'txn_code' => $txnCode,     
                                    );       
                                          $txnCode = $this->initiateRemitAPI($paramsArr);
                                                 
                                                 
                                                 
                                                // Updating customer Balance                                                
                                                   }else {
                                                 
                                                $flag['response'] = FALSE; 
                                                $failedReason = $ecsApi->getError();
                                                $updateArr['status'] = STATUS_FAILURE;
                                                $updateArr['failed_reason'] = $failedReason;        
                                                $datastatus['status_new'] = STATUS_FAILURE;
                                             
                                                if($productdetails['const'] == PRODUCT_CONST_RAT_SMP) {
                                                    $this->updateReq($res, array('flag_response' => FLAG_RESPONSE_ONE));
                                                }
                                        }    

//                                    } else {
//                                        // Updating customer Balance                                                
//                                        $updArr = array('block_amount' => new Zend_Db_Expr("block_amount - " . $params['amount']));
//                                        $where = "id = '" . $customerPurseId . "'";
//                                        $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);
//                                    }
                                        
                                        $resUpdate = $this->updateReq($res,$updateArr);
                                        
                                        $resLog = $remittancestatuslog->addStatus($datastatus); 
                                       
                                       
                                    }
                               
                            }
                             return $flag;
      }
  }
        catch (Exception $e) {
           // If any of the queries failed and threw an exception,
           // we want to roll back the whole transaction, reversing
           // changes made in the transaction, even those that succeeded.
           // Thus all changes are committed together, or none are.
           App_Logger::log($e->getMessage(), Zend_Log::ERR);
//           $this->_db->rollBack();
           //throw new Exception ("Transaction not completed due to system failure");
            $code = $e->getCode();
            if(empty($code)) {
                $code = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
            }            
           //$code = (!empty($e->getCode())) ? $e->getCode() : ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
           throw new Exception($e->getMessage(), $code);
       }
     }
     
       /*
     * initiates remittance txns
     */

    public function initiateRemitAPI($params) {
        $baseTxn = new BaseTxn();
        return $baseTxn->initiateRemitAPI($params);
    }
    
    public function getRemittanceTransaction($params){
        $productID = isset($params['product_id'])?$params['product_id']:'';
        $txnCode = isset($params['txn_code'])?$params['txn_code']:'';
        $txnRefNum = isset($params['txnrefnum'])?$params['txnrefnum']:'';
        
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as krt", array('id','product_id', 'beneficiary_id', 'amount', 'remitter_id', 'txn_code','sender_msg', 'fee', 'service_tax','agent_id','status','txnrefnum as remittace_txnrefnum'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS . ' as kr', "krt.remitter_id = kr.id",array('id as r_id'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as kb", "krt.beneficiary_id = kb.id", array('id as b_id','name','nick_name','bene_code'))
                ->joinLeft(DbTable::TABLE_PURSE_MASTER . ' as pm', "krt.purse_master_id = pm.id",array('code as wallet_code'))
                ->joinLeft(DbTable::TABLE_RAT_CORP_CARDHOLDER . ' as rcc', "krt.rat_customer_id = rcc.rat_customer_id",array('rcc.partner_ref_no'));
             
        if( ($txnCode!='')){
            $select->where('krt.txn_code = ?', $txnCode);
        }
        if(($txnRefNum != '' ) ){
            $select->where('krt.txnrefnum = ?', $txnRefNum);
        }
        if($productID !=''){
        $select->where('krt.product_id = ?', $productID);
        }
        $rs = $this->_db->fetchRow($select);
        if (!empty($rs)) {
            return $rs;
        } else {
            return FALSE;
        }
    }
    
      /*
     * chks remit limits
     */

    public function chkAllowRemitAPI($params) {
        $baseTxn = new BaseTxn();
        return $baseTxn->chkAllowRemitAPI($params);
    }
    
    public function getRemitRequestInfo($param)
    {
        $amount = isset($param['amount']) ? $param['amount'] : '';
        $txn_no = isset($param['txn_no']) ? $param['txn_no'] : '';
        $productId = isset($param['product_id']) ? $param['product_id'] : '';
        $date_txn = isset($param['date_txn']) ? $param['date_txn'] : '';
        
        $select = $this->_db->select();
        $select->from($this->_name . " as rr", array('rr.id as id', 'status_settlement'));

        if ($amount != ''){
            $select->where("rr.amount = '" . $amount . "'");
        }
        if ($txn_no != ''){
            $select->where("rr.txnrefnum = '" . $txn_no . "'");
        }
        if ($productId != ''){
            $select->where("rr.product_id = '" . $productId . "'");
        }
        if ($date_txn != ''){
            $select->where("DATE(rr.date_created) = '" . $date_txn . "'");
        }
        $select->where("rr.status IN ('". STATUS_IN_PROCESS."','".STATUS_PROCESSED."','".STATUS_SUCCESS."')"); 
        //echo $select;exit;
        return $this->_db->fetchRow($select);
    }
    
    public function getTotalRemittanceTxnLoad($param, $status = array()) {
        $date = isset($param['date']) ? $param['date'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $productId = isset($param['product_id']) ? $param['product_id'] : '';
        $custPurseId = isset($param['customer_purse_id']) ? $param['customer_purse_id'] : '';
        $wallet_type = isset($param['wallet_type']) ? $param['wallet_type'] : '';
        
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
        $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('sum(rr.amount) as agent_total_remittance', 'sum(rr.fee) as agent_total_remittance_fee', 'sum(rr.service_tax) as agent_total_remittance_stax', 'count(rr.id) as agent_total_remittance_count'));
        $select->joinLeft(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id = rr.purse_master_id ",array());
        
        $select->setIntegrityCheck(false);
        if ($statusWhere == '') {
            $select->where("rr.status = '" . STATUS_IN_PROCESS . "' OR rr.status = '" . STATUS_HOLD . "' OR rr.status =  '" . STATUS_SUCCESS . "' OR rr.status = '" . STATUS_REFUND . "' OR rr.status = '" . FLAG_FAILURE . "' OR rr.status = '".STATUS_PROCESSED."'");
        } else {
            $select->where($statusWhere);
        }
        
        if ($productId > 0) {
            $select->where('rr.product_id = ?', $productId);
        }
        if ($custPurseId != '') {
            $select->where('rr.customer_purse_id = ?', $custPurseId);
        }
        if (!empty($wallet_type)) {
            $select->where("pm.is_virtual = ? " , $wallet_type);
        }
        if ($date) {
            $select->where('DATE(rr.date_created) =?', $date);
        } else if ($to != '' && $from != '') {
            $select->where("rr.date_created BETWEEN '$from' AND '$to'");
        }
//        $select->group("product_id");
//        echo $select."<br>"; 
        $row = $this->fetchRow($select);
        return $row;
    }
    
    public function custTransactionHistory($params) {
            
            $productID = isset($params['product_id'])?$params['product_id']:'';
            $ratCustId = isset($params['customer_id'])?$params['customer_id']:'';
            $walletCode = isset($params['wallet_code'])?$params['wallet_code']:'';
            $remitterId = $params['remitter_id'];
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('rr.beneficiary_id', 'rr.amount', 'rr.date_created as txn_date','rr.txn_code', 'rr.status', 'rr.sender_msg as description',"UNIX_TIMESTAMP(rr.date_created) as strdate"));
            $select->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS . " as r", "rr.remitter_id = r.id", array('r.name as remitter_name'));
            $select->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", "rr.beneficiary_id = b.id", array('b.name as bene_name'));
            $select->joinLeft(DbTable::TABLE_PURSE_MASTER .' as pm', "rr.purse_master_id = pm.id",array("code"));
            $select->where("rr.remitter_id = ?", $remitterId);
            $select->where("rr.product_id = ?", $productID);
            if( ($walletCode !='') && ($walletCode == 'all') ){
              $select->where("rr.status = '" . STATUS_SUCCESS . "' OR rr.status = '" . STATUS_REFUND . "'");  
            }elseif($walletCode !=''){
              $select->where("pm.code =?",$walletCode);  
              $select->where("rr.status = '" . STATUS_SUCCESS . "' OR rr.status = '" . STATUS_REFUND . "' OR rr.status = '" . STATUS_PROCESSED . "' OR rr.status = '" . STATUS_IN_PROCESS . "'");
            }else{
                $select->where("rr.status = '" . STATUS_SUCCESS . "' OR rr.status = '" . STATUS_REFUND . "' OR rr.status = '" . STATUS_PROCESSED . "' OR rr.status = '" . STATUS_IN_PROCESS . "'");
            
            }
            $select->order('rr.id DESC');
            $select->limit(TXN_HISTORY_COUNT);
          //  echo $select->__toString();exit;
            return $this->_db->fetchAll($select);
        
    }
    
    
    public function getRatnakarRemittance($param){
        if(isset($param['duration']) && $param['duration'] != ''){
            $dates = Util::getDurationDates($param['duration']);
            $to = isset($dates['to']) ? $dates['to'] : '';
            $from = isset($dates['from']) ? $dates['from'] : ''; 
        } else { 
            $to = isset($param['to']) ? $param['to'] : '';
            $from = isset($param['from']) ? $param['from'] : ''; 
        }

        $retTxnData = array();

        $queryDate = array(
            'date'          =>  $to,
            'date_from'     =>  $from,
            'agent_id'      =>  $param['agent_id'],
            'bank_unicode'  =>  $param['bank_unicode']
        );

        if(isset($param['mobile_no'])){
            $queryDate['mobile_no'] = $param['mobile_no'];
        }
	
	if((isset($param['product_id'])) && ($param['product_id']!='')){
	    $queryDate['product_id'] = $param['product_id']; 
        }

        if(isset($param['txn_no'])){
            $queryDate['txn_no'] = $param['txn_no'];
        }

        /**** getting agent remitters registered for particular date ****/ 
        $remitters  = $this->getRemittersOnDateBasisNew($queryDate); 
        if(!empty($remitters)){
            $retTxnData = array_merge($retTxnData, $remitters);
        }
        /**** getting agent remitters's fund transfer request for particular date *****/
        $remitRequests  = $this->getRemitRequestOnDateBasisNew($queryDate); 
        if(!empty($remitRequests)){
            $retTxnData = array_merge($retTxnData, $remitRequests);
        }
        /**** getting agent remitters's refunds for particular date *****/
        $remitRefunds  = $this->getRemitRefundsOnDateBasisNew($queryDate);  
        if(!empty($remitRefunds)){
            $retTxnData = array_merge($retTxnData, $remitRefunds);
        }

	error_log('Getting commission details to report');
	/**** getting agent commission details *****/
	$commRequests  = $this->getCommissionTransactions($queryDate);
	if(!empty($commRequests)){
	    $retTxnData = array_merge($retTxnData, $commRequests);
	}
                

        return $retTxnData ;
    }
    
    public function getRemittersOnDateBasisNew($param) {
        $retData = array();
        $retNewData = array();
        $objRatRemmit = new Remit_Ratnakar_Remitter();
        if (!empty($param)) {
            $param['check_fee'] = false;
            $retData = $objRatRemmit->getRemitterRegnfee($param);
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

                    $alterData['refund_txn_code'] = ''; 
                     
                    if(!empty($agentType)) {
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
                    $retNewData[$k]['batch_name'] = '';
                    $k++;
                }
            }
        }
        return $retNewData;
    }
    
    public function getRemitRequestOnDateBasisNew($param) {
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
                for($j = 0; $j < $totalData; $j++){
                    // adding transaction type field
                    $alterData = $retData[$j];
                    $alterData['txn_type'] = TXNTYPE_REMITTANCE;
                    $alterData['crn'] = '';
                    $alterData['txn_date'] = Util::returnDateFormatted($retData[$j]['txn_date'], "Y-m-d", "d-m-Y", "-");
                    $alterData['agent_name'] = $retData[$j]['name'];
                    $agentUser = new AgentUser();
                    $agentType = $agentUser->getAgentCodeName($retData[$j]['agent_user_type'], $retData[$j]['agent_id']);
                    $refund_txn = $this->getRefundTxnRefNo($retData[$j]['rmid']);
                    
                    if(!empty($refund_txn)) {
                        $alterData['refund_txn_code'] = $refund_txn['refund_txn_code'];
                    } else {
                        $alterData['refund_txn_code'] = '';
                    }

                    if(!empty($agentType)) {
                        $alterData = array_merge($alterData, $agentType);
                    }
                    
                    // recreating array with adding new records for service tax and fee 
                    $retNewData[$k] = $alterData;
                    $k++;
                    $retNewData[$k] = $alterData;
                    $retNewData[$k]['amount'] = $retData[$j]['service_tax'];
                    $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_SERVICE_TAX;
                    $retNewData[$k]['txn_code'] = $retData[$j]['txn_code'];
                    $retNewData[$k]['batch_name'] = $retData[$j]['batch_name'];
                    $retNewData[$k]['utr'] = $retData[$j]['utr'];
                    $retNewData[$k]['flag'] = $retData[$j]['flag'];

                    $k++;
                    $retNewData[$k] = $alterData;
                    $retNewData[$k]['amount'] = $retData[$j]['fee'];
                    $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_FEE;
                    $retNewData[$k]['txn_code'] = $retData[$j]['txn_code'];
                    $retNewData[$k]['batch_name'] = $retData[$j]['batch_name'];
                    $retNewData[$k]['utr'] = $retData[$j]['utr'];
                    $retNewData[$k]['flag'] = $retData[$j]['flag'];

                    $k++;
                }
            }
        }
        return $retNewData;
    }
    
    public function getRemitRefundsOnDateBasisNew($param) {
        $retData = array();
        $retNewData = array();
        $agentUser = new AgentUser();
        
        if (!empty($param)) { 
            $param['check_fee'] = false;

            $retData = $this->getRemitRefundfee($param);
            $totalRemitRefundFee = count($retData);

            if ($totalRemitRefundFee >= 1) { 
                $totalData = count($retData);
                //adding moer fields and recreating array with adding new records for service tax and fee 
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
                    $alterData['batch_name'] = $retData[$j]['batch_name'];

                    $agentUser = new AgentUser();
                    $agentType = $agentUser->getAgentCodeName($retData[$j]['agent_user_type'], $retData[$j]['agent_id']);

                    $alterData['refund_txn_code'] = $retData[$j]['txn_code'];
                    $alterData['txn_code'] = $retData[$j]['tran_ref_num'];
                    
                    if(!empty($agentType)) {
                        $alterData = array_merge($alterData, $agentType);
                    }
                    
                    // recreating array with adding new records for service tax and fee 
                    $retNewData[$k] = $alterData;
                    $retNewData[$k]['batch_name'] = $retData[$j]['batch_name'];;
                    $k++;
                    $retNewData[$k] = $alterData;
                    $retNewData[$k]['amount'] = $retData[$j]['reversal_service_tax'];
                    $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_SERVICE_TAX;
                    $retNewData[$k]['txn_code'] = $retData[$j]['tran_ref_num'];
                    $retNewData[$k]['batch_name'] = $retData[$j]['batch_name'];
		    $retNewData[$k]['flag'] = $retData[$j]['flag'];
                    $retNewData[$k]['utr'] = $retData[$j]['utr'];
                    
                    $k++;
                    $retNewData[$k] = $alterData;
                    $retNewData[$k]['amount'] = $retData[$j]['reversal_fee'];
                    $retNewData[$k]['txn_type'] = TXNTYPE_REMITTANCE_REVERSAL_REFUND_FEE;
                    $retNewData[$k]['txn_code'] = $retData[$j]['tran_ref_num'];
                    $retNewData[$k]['batch_name'] = $retData[$j]['batch_name'];
		    $retNewData[$k]['flag'] = $retData[$j]['flag'];
                    $retNewData[$k]['utr'] = $retData[$j]['utr'];
                    $k++;
                }
            }
        }
        return $retNewData;
    }
    
    public function checkDuplicateRemittanceTransNum($params) {
        $select = $this->select();
        $select->from($this->_name);
        $select->where('product_id = ?', $params['product_id']);
        $select->where('txnrefnum = ?', $params['txnrefnum']); 
        $rs = $this->fetchRow($select);
        if( !empty($rs) ){
            //return TRUE;
            return array('id'=>$rs['id'], 'txncode'=>$rs['txn_code']);
        }
        else
            return FALSE;
   }
   

    public function getRemittancefeeAll($param){ 
        $to = $param['to'];
        $from = $param['from']; 
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : ''; 
         
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as r", 
                    array(
                        'r.fee', 'r.service_tax', 'r.amount','DATE_FORMAT(r.date_created,"%d-%m-%Y") as txn_date','DATE(r.date_created) as date_created','r.status','r.txn_code','r.utr'
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
                DbTable::TABLE_RATNAKAR_RESPONSE_FILE." as res", "r.utr = res.utr" , 
                    array(
                        'res.rejection_code', 'res.rejection_remark', 'res.returned_date'
                ));
        $select->joinLeft(
                DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND." as ref", "ref.remittance_request_id = r.id" , 
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
        
        $select->where("r.status = '" . STATUS_IN_PROCESS . "' OR r.status =  '" . STATUS_SUCCESS . "' OR r.status = '" . STATUS_REFUND . "' OR r.status = '" . FLAG_FAILURE . "' OR r.status = '".STATUS_PROCESSED."'");
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
                DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND . " as rr", 
                    array(
                        'rr.fee', 'rr.service_tax', 'rr.amount','DATE_FORMAT(rr.date_created,"%d-%m-%Y") as txn_date','DATE(rr.date_created) as date_created','rr.txn_code as refund_txn_code', 'rr.reversal_service_tax', 'rr.reversal_fee',
                ));
        $select->joinLeft(
                 DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as req", "req.id = rr.remittance_request_id ", 
                    array(
                       'req.txn_code', 'req.status','req.utr'
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
                DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as r", 
                    array(
                        'r.fee as fee_amount', 'r.service_tax as service_tax_amount', 'r.amount as transaction_amount','DATE_FORMAT(r.date_created,"%d-%m-%Y") as txn_date','DATE(r.date_created) as date_created','r.status as txn_status','r.txn_code','r.utr', new Zend_Db_Expr("'".TXNTYPE_REMITTANCE_FEE."' as transaction_type_name"), new Zend_Db_Expr("0 as reversal_fee"), new Zend_Db_Expr("0 as reversal_service_tax")
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
                DbTable::TABLE_RATNAKAR_RESPONSE_FILE." as res", "r.utr = res.utr" , 
                    array(
                        'res.rejection_code', 'res.rejection_remark', 'res.returned_date'
                ));
        $select->joinLeft(
                DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND." as ref", "ref.remittance_request_id = r.id" , 
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
        
        $select->where("r.status = '" . STATUS_IN_PROCESS . "' OR r.status =  '" . STATUS_SUCCESS . "' OR r.status = '" . STATUS_REFUND . "' OR r.status = '" . FLAG_FAILURE . "' OR r.status = '".STATUS_PROCESSED."'");
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
                DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND . " as rr", 
                    array(
                        'rr.fee as fee_amount', 'rr.service_tax as service_tax_amount', 'rr.amount as transaction_amount','DATE_FORMAT(rr.date_created,"%d-%m-%Y") as txn_date','DATE(rr.date_created) as date_created','rr.txn_code as refund_txn_code', 'rr.reversal_service_tax', 'rr.reversal_fee', new Zend_Db_Expr("'".TXNTYPE_REMITTANCE_REFUND_FEE."' as transaction_type_name")
                ));
        $select->joinLeft(
                 DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as req", "req.id = rr.remittance_request_id ", 
                    array(
                       'req.txn_code', 'req.status as txn_status','req.utr'
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
    
    /* gets the Ratnakar Remittance Transaction Recon for in_process, hold and failure */
    public function getRatRemittanceTxnRecon(){
        $seprator = ',';
        $ext = '.csv';
        $fileName = '';
        $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");
        
        $bankRat = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $bankRatUnicode = $bankRat->bank->unicode;
        
        $bankObj = new Banks();
        $bankInfo = $bankObj->getBankbyUnicode($bankRatUnicode);
        
        try{
            /************
            //Enable DB Slave
            $this->_enableDbSlave();

            $remitterRegnModel = new Remit_Ratnakar_Remitter();

            // Get Remitter registrations Fee, Transaction type code = TXNTYPE_REMITTER_REGISTRATION
            $agentRMRG = Util::toArray($remitterRegnModel->getRemitterRegistrationRecon());

            // Get Remittance Fee, typecode =  TXNTYPE_REMITTANCE_FEE
            $agentREMT = Util::toArray($this->getRemitRemittanceRecon());

            // Get Remittance refund Fee, typecode = TXNTYPE_REMITTANCE_REFUND_FEE
            $agentRMFE = Util::toArray($this->getRemittanceRefundRecon());
            //Disable DB Slave
            $this->_disableDbSlave();

            $result = array_merge($agentRMRG, $agentREMT, $agentRMFE);
            *********/
            
            
            //Enable DB Slave
            $this->_enableDbSlave();
            
            // Get Remittance Fee, typecode =  TXNTYPE_REMITTANCE_FEE
            $result = Util::toArray($this->getRemitRemittanceRecon());
            
            $this->_disableDbSlave();

            $recordCount = count($result);
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
                foreach($result as $data){
                    $arrReport[$i]['txn_date'] = $data['txn_date'];
                    $arrReport[$i]['txn_code']  = $data['txn_code'];
                    $arrReport[$i]['sup_dist_code']  = $data['sup_dist_code'];
                    $arrReport[$i]['sup_dist_name']  = $data['sup_dist_name'];
                    $arrReport[$i]['dist_code']  = $data['dist_code'];
                    $arrReport[$i]['dist_name']  = $data['dist_name'];
                    $arrReport[$i]['agent_code']  = $data['agent_code'];
                    $arrReport[$i]['agent_name']  = $data['name'];
                    $arrReport[$i]['card_num']  = Util::maskCard($data['card_number'], 4);
                    $arrReport[$i]['crn']  = $data['crn'];
                    $arrReport[$i]['remit_name']  = $data['remitter_name'];
                    $arrReport[$i]['remit_mobileno']  = $data['remitter_mobile'];
                    $arrReport[$i]['product_name']  = $data['product_name'];
                    $arrReport[$i]['bene_name']  = $data['bene_name'];
                    $arrReport[$i]['bene_acc_num']  = Util::maskCard($data['bene_account_number']);
                    $arrReport[$i]['shmart_txn_code']  = $data['txn_code'];
                    $arrReport[$i]['utr_num']  = $data['utr'];
                    $arrReport[$i]['txn_amt']  = $data['transaction_amount'];                    
                    $arrReport[$i]['txn_type']  = $TXN_TYPE_LABELS[$data['transaction_type_name']];
                    $arrReport[$i]['txn_status']  = Util::getStatusArray($data['txn_status']);
                    $arrReport[$i]['remarks']  = $data['neft_remarks'];
                    $arrReport[$i]['date_created']  = $data['date_created'];
                    $arrReport[$i]['date_updated']  = $data['date_updated'];
                    $arrReport[$i]['fee']  = $data['fee_amount'];
                    $arrReport[$i]['service_tax']  = $data['service_tax_amount'];
                    $arrReport[$i]['sender_msg']  = $data['sender_msg'];
                    $arrReport[$i]['status']  = Util::getStatusArray($data['txn_status']);
                    $arrReport[$i]['hold_reason']  = '';
                    $arrReport[$i]['cr_response']  = '';
                    $arrReport[$i]['final_resp']  = '';
                    $arrReport[$i]['fund_holder']  = ucfirst($data['fund_holder']);                    
                    $arrReport[$i]['txn_ref_num']  = $data['refund_txn_code'];
                    $arrReport[$i]['current_txn_status']  = Util::getStatusArray($data['txn_status']);
                    $arrReport[$i]['return_date']  = '';
                    $arrReport[$i]['rejection_code']  = '';
                    $arrReport[$i]['rejection_remarks']  = '';
                    
                    $i++; 
                }
            
                $fileName = 'RBL_'.Util::getNeftBatchFileName().$ext;
                $file->setBatch($arrReport, $seprator);
                $file->setFilepath(UPLOAD_PATH_RAT_REMIT_TXN_RECON_REPORTS);
                $file->setFilename($fileName);
                $file->generate(TRUE); 

                //insert file info in t_files table
                $msg = $recordCount.' records has been found';
                $file->insertFileInfo(array('bank_id'=>$bankInfo['id'], 'label'=>RAT_REMIT_TXN_RECON_FILE, 'file_name'=>$fileName, 'ops_id'=>TXN_OPS_ID, 'date_start'=>date('Y-m-d'), 'date_end'=>date('Y-m-d'), 'status'=>STATUS_ACTIVE, 'comment'=>$msg, 'date_created'=>new Zend_Db_Expr('NOW()')));
            }
            return $recordCount;
        }catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
        }
    }
    
    /*
     * getRemitRemittanceRecon() gets ratnakar remittanes for the previous day for status (in process, failure)
     */
    public function getRemitRemittanceRecon(){ 
        $prevDate = date('Y-m-d', strtotime('-1 days'));
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'" . $decryptionKey . "') as bene_account_number");
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as r", 
                    array(
                        'r.fee as fee_amount', 'r.service_tax as service_tax_amount', 'r.amount as transaction_amount','DATE_FORMAT(r.date_created,"%d-%m-%Y") as txn_date','DATE_FORMAT(r.date_created,"%d-%m-%Y") as date_created','r.status as txn_status','r.txn_code','r.utr', new Zend_Db_Expr("'".TXNTYPE_REMITTANCE_FEE."' as transaction_type_name"),'DATE_FORMAT(r.date_updated,"%d-%m-%Y") as date_updated', 'r.sender_msg', 'r.neft_remarks', 'r.fund_holder'
                ));
        $select->joinLeft(
                DbTable::TABLE_RAT_CORP_CARDHOLDER . " as rcc", "r.rat_customer_id  = rcc.rat_customer_id AND r.rat_customer_id <> 0", 
                    array('rcc.card_number', 'rcc.crn'));
        $select->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS . " as rem", "rem.id = r.remitter_id", array('rem.mobile as remitter_mobile', 'concat(rem.name," ",rem.last_name) as remitter_name'));
        $select->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", "r.beneficiary_id = b.id ", array('b.name as bene_name', $bankAccountNumber));
        $select->joinLeft(
                DbTable::TABLE_AGENTS . " as a", "r.agent_id = a.id AND a.status = '" . STATUS_UNBLOCKED . "'", 
                    array(
                        'a.agent_code', 'concat(a.first_name," ",a.last_name) as name'
                ));        
        $select->joinLeft(
                DbTable::TABLE_RATNAKAR_RESPONSE_FILE." as res", "r.utr = res.utr" , 
                    array(
                        'res.rejection_code', 'res.rejection_remark', 'res.returned_date'
                ));
        $select->joinLeft(
                DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND." as ref", "ref.remittance_request_id = r.id" , 
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
        $select->where("r.status = '" . STATUS_IN_PROCESS . "' OR r.status = '" . FLAG_FAILURE . "'");
        $select->where("DATE(r.date_created) = ?", $prevDate);

        return $this->fetchAll($select); 
    }

    /*
     * get ratnakar remittance refund recon with status 'failure'
     */
    public function getRemittanceRefundRecon() {
        $prevDate = date('Y-m-d', strtotime('-1 days'));
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'" . $decryptionKey . "') as bene_account_number");
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND . " as rr", 
                    array(
                        'rr.fee as fee_amount', 'rr.service_tax as service_tax_amount', 'rr.amount as transaction_amount','DATE_FORMAT(rr.date_created,"%d-%m-%Y") as txn_date','DATE_FORMAT(rr.date_created,"%d-%m-%Y") as date_created','rr.txn_code as refund_txn_code', 'rr.reversal_service_tax', 'rr.reversal_fee', new Zend_Db_Expr("'".TXNTYPE_REMITTANCE_REFUND_FEE."' as transaction_type_name"), new Zend_Db_Expr("'' as date_updated")
                ));
        $select->joinLeft(
                 DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as req", "req.id = rr.remittance_request_id ", 
                    array(
                       'req.txn_code', 'req.status as txn_status','req.utr','req.fund_holder', 'req.neft_remarks', 'req.sender_msg'
                ));
        $select->joinLeft(
                 DbTable::TABLE_RAT_CORP_CARDHOLDER . " as rcc", "rcc.rat_customer_id = rr.rat_customer_id AND rr.rat_customer_id <> 0", 
                    array('rcc.card_number', 'rcc.crn'));
        $select->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS . " as rem", "rem.id = rr.remitter_id", array('rem.mobile as remitter_mobile', 'concat(rem.name," ",rem.last_name) as remitter_name'));
        $select->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", "req.beneficiary_id = b.id ", array('b.name as bene_name', $bankAccountNumber));
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
    
    /**
     *  feeArr is kept even though the fee structure is changed.
     *  Because to consider "Product not assigned to agent for the day"
     *  TODO: We can remove this check after consulting with product on this.
     *
     */
    public function doTransaction($feeArr, $amount, $session, $remittancestatuslog, $flag, $beneId, $bank, $m, $detail, $beneficiary, $feeplan, $isLast, $totalAmount){

        try {
        		$txnResponse = array();
        		$txnResponse['isPosted']=false;
        		$txnResponse['success']=false;
        		
                $user = Zend_Auth::getInstance()->getIdentity();

                $currentDate = date( 'Y-m-d', time());
                $cumulativeAmtSoFar = 0;
                $totalFee = 0;
                $cumulativeAmtRow = $this->getTxnRemitterOnSpecificDate($session->remitter_id,$user->product_id,$currentDate,$user->id,$beneId);                
                if($cumulativeAmtRow ) {
                    $cumulativeAmtSoFar = ($cumulativeAmtRow['total'] > 0) ? $cumulativeAmtRow['total'] : 0; 
                    $totalFee = $cumulativeAmtRow['fee_total'] + $cumulativeAmtRow['st_total'];
                }
                $fee = $this->calculateFeeForRatnakar($cumulativeAmtSoFar, $amount, $totalFee); 
                
                                      
                // Calculate fee components
                $feeComponent = Util::getFeeComponents($fee);
                $param = array('agent_id' =>$user->id,
                               'product_id' =>$user->product_id,
                               'remitter_id' =>$session->remitter_id,
                               'amount' =>$amount,
                               'fee_amt' =>$feeComponent['partialFee'],
                               'service_tax' =>$feeComponent['serviceTax'],
                				'bank_unicode' => $bank->bank->unicode,
                               );
		 
		
                //Fund transfer limit on the basis of Agent limit and product limit
                if ($this->chkAllowRemit($param)){
                    //If fee is assigned for the product assigned to the Agent for the day
                    if(empty($feeArr)){
                        //print_r( array('msg-error' => 'Product not assigned to agent for the day',) );
                        echo '<div class="msg msg-error"><p>Product not assigned to agent for the day</p></div>'; exit;
                        
                    }
                }
         
                $data = array();
                $data['amount'] = $amount;
                $data['fund_holder'] = REMIT_FUND_HOLDER_REMITTER;
                $data['agent_id'] = $user->id;
                $data['remitter_id'] = $session->remitter_id;
                $data['beneficiary_id'] = $beneId;
                $data['ops_id'] = TXN_OPS_ID;
                $data['product_id'] = $user->product_id;
                $data['date_created'] = new Zend_Db_Expr('NOW()');
                $data['fee'] = $feeComponent['partialFee'];
                $data['service_tax'] = $feeComponent['serviceTax'];
                $data['status'] = 'processed';
                $data['sender_msg'] = '';
                $data['flag'] = $flag;
                $data['channel'] = CHANNEL_AGENT;
                        
                $res = $this->save($data);
                $datastatus = array();
                $datastatus['remittance_request_id'] = $res;
                $datastatus['status_old'] = '';
                $datastatus['status_new'] = 'processed';
                $datastatus['by_remitter_id'] = $session->remitter_id;
                $datastatus['by_agent_id'] = $user->id;
                $datastatus['by_ops_id'] = TXN_OPS_ID;
                $datastatus['date_created'] = new Zend_Db_Expr('NOW()');
                            

                $resLog = $remittancestatuslog->addStatus($datastatus); 
                

                if($res > 0 ){

                    $paramsArr = array('agent_id' =>$user->id,
                            'product_id' =>$user->product_id,
                            'remitter_id' =>$session->remitter_id,
                            'amount' =>$amount,
                            'remit_request_id' =>$res,
                            'fee_amt' =>$feeComponent['partialFee'],
                            'service_tax' =>$feeComponent['serviceTax'],
                            'bank_unicode' => $bank->bank->unicode
                        );       
                    $txnCode = $this->initiateRemit($paramsArr);
                    
                    if($txnCode){
                        $remitters = new Remit_Ratnakar_Remitter();
                        $remitter_data = $remitters->getRemitterById($session->remitter_id);
                        
                        $beneficiary = new Remit_Ratnakar_Beneficiary();
                        $beneficiariesList = $beneficiary->getBeneficiaryDetails($beneId);
						
						$callRblAPI = FALSE;
					    
                       
                      
	
					error_log('Hour :' .date('H'));	
			/*if($flag == 3 &&  date('H') >= 8 && date('H') <= 18  && (date('D') != 'Sun') && (date('D') == 'Sat' && date('H') < 12 )) {*/
			if($flag == 3 &&  (date('H') >= 9 && date('H') < 18  && (date('D') != 'Sun' && date('D') != 'Sat')) || 
                ($flag == 3 && date('D') == 'Sat' && date('H') < 12 ) ) {

							 
							$callRblAPI = TRUE;
						} else {
							$status = STATUS_IN_PROCESS;
						}
						
						if($flag == 2) {
							$callRblAPI = TRUE;
						}
						
						$rblStatusErrorCodeDesc = '';	

                                                /***
                                                if(date('Y-m-d h:i') <= '2015-04-15 10:19' && $flag == 3) {
                                                    $callRblAPI = FALSE;
                                                    $status = STATUS_IN_PROCESS;
                                                }**/

						if($callRblAPI){
							$dataToApi = array('header' => array('sessiontoken' => $session->rblSessionID),
											'bcagent' => $user->bcagent,
											'remitterid' => $remitter_data['remitterid'],
											'beneficiaryid' => $beneficiariesList['beneficiary_id'],
											'amount' => $data['amount'],
											'remarks' => 'Transfer',
											'cpid' => RBL_CHANNEL_CPID,
											'channelpartnerrefno' => $txnCode,
											'flag' => $flag
							   );
	
							$rblApiObject = new App_Rbl_Api();
							$response = $rblApiObject->transaction($dataToApi);
							
if(isset($response['NPCIResponsecode']) && in_array($response['NPCIResponsecode'],array('08','91','12'))){
									//these transactions with error codes have to be marked as Hold
									$status = STATUS_HOLD;
							}else if(isset($response['status'])) {
								$rblStatusErrorCodeDesc = 'Status: ' .$response['status'];
 if(isset($response['status']) && $response['status'] == 1 && isset($response['remarks']) &&  ( strcasecmp($response['remarks'],'SUCCESSUnKnown')==0)) {
									$status = STATUS_HOLD;
								} else {
								
								if($response['status'] == 1 && $flag == 2) {
									$status = STATUS_SUCCESS;
								}
								else if($response['status'] == 1 && $flag == 3){
									$status = STATUS_PROCESSED;
								}
								else {
									//for all other status mark as failure
									$status = STATUS_FAILURE;
								}
							}}
							if(isset($response['NPCIResponsecode'])){
								$rblStatusErrorCodeDesc = $rblStatusErrorCodeDesc .', Code: ' .$response['NPCIResponsecode'];
							}
							
						}                        

                        $updateArr = array(
                            'status'        => $status,
                            'fund_holder'   => REMIT_FUND_HOLDER_OPS,
                            'txn_code'      => $txnCode,
							'flag' => $flag,
                            'rbl_transaction_id' => isset($response['RBLtransactionid']) ? $response['RBLtransactionid'] : '',
                        	'utr' => isset($response['bankrefno']) ? substr($response['bankrefno'],0,16) : '',
                        	'neft_remarks' => substr($rblStatusErrorCodeDesc,0,250)
                        );
                        
                        $resUpdate = $this->updateReq($res,$updateArr);
						
					$datastatus = array();
						$datastatus['remittance_request_id'] = $res;
						$datastatus['status_old'] = STATUS_PROCESSED;
						$datastatus['status_new'] = $status;
						$datastatus['by_remitter_id'] = $session->remitter_id;
						$datastatus['by_agent_id'] = $user->id;
						$datastatus['by_ops_id'] = TXN_OPS_ID;
						$datastatus['date_created'] = new Zend_Db_Expr('NOW()');
						$resLog = $remittancestatuslog->addStatus($datastatus); 							
                   
                    
                    }
						if($status == STATUS_SUCCESS || $status == STATUS_PROCESSED){
							$txnStatusToDisplay = 'SUCCESS';
						}else{
							$txnStatusToDisplay = 'FAILURE';
						}

                        //$txnStatusToDisplay = ($status == STATUS_SUCCESS) ? 'SUCCESS' : 'FAILURE';
                        $messageOnSubmit = "";

                        //isLast, $totalAmount
						if($callRblAPI){
								$txnResponse['isPosted'] = true;
		                        if($txnStatusToDisplay == 'SUCCESS'){
		                        	$txnResponse['success'] = true;
	                	        }else{
	                	        	$txnResponse['success'] = false;
	                        	}
						}else{
							$txnResponse['isPosted'] = false;
							$txnResponse['success'] = true;
						}

						return $txnResponse;

                       /* echo '<div class="msg-success"><p>Your request has been submitted, the beneficiarys account will be credited soon & you will get an sms regarding the success/failure</p></div>'; 
                        */
                } else {
                    if($isLast){
                        return '<div class="msg msg-error"><p>Your request for fund transfer could not be initiated</p></div>'; 
                    }
                }
            } catch (Exception $e) {
    
                    $errMsg = $e->getMessage();
                    echo '<div class="msg msg-error"><p>'.$errMsg.'</p></div>';
                    //App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    exit;
            }
    }
    
    public function reQuery($remittance_request_id = false) {
         
        if($remittance_request_id) 
        {
            $status = $this->reQueryForAgent($remittance_request_id);
        }
        else
        {
            $status = $this->reQueryForCRON();
        }
         
        return $status;        
    }
    
    
	public function reQueryForAgent($remittance_request_id) {
		error_log('Doing requery for Agent');
		
		 $session = new Zend_Session_Namespace('App.Agent.Controller');
		 $agentsModel = new Agents();
			$user = Zend_Auth::getInstance()->getIdentity();
				 $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST);
                 
                  if($remittance_request_id) {
			   //add remitter request id if requeried manually
                        $select = $select->where("status = '" . STATUS_SUCCESS . "' OR status = '" . STATUS_PROCESSED . "' OR status = '" . STATUS_HOLD . "'")->order('agent_id ASC');
                	$select = $select->where("id = '".$remittance_request_id."'");
		   }
                  
			   
			$transactions = $this->_db->fetchAll($select);
			if(count($transactions) == 0) {
					return;
			}
			$map = array();
			
			foreach($transactions as $transaction) {
				$transaction = (object) $transaction;	
					$agentInfo = (object)$agentsModel->findagentById($transaction->agent_id);
					 if(!isset($map[$agentInfo->bcagent])) {
					 	if(!isset($session->rblSessionID)){
					 		$rblApiObject = new App_Rbl_Api();
					 		$rbiResponse = $rblApiObject->channelPartnerLogin(array('username' => RBL_CHANNEL_PARTNER_LOGIN_USERNAME,
					 				'password' => RBL_CHANNEL_PARTNER_LOGIN_PASSWORD,
					 				'bcagent' => RBL_CRON_SCHEDULER_BCAGENT));
					 			
					 		if(isset($rbiResponse['status']) && $rbiResponse['status']) {
					 			$session->rblSessionID = $rbiResponse['sessiontoken'];
					 			$map[$agentInfo->bcagent] = $session->rblSessionID;
					 		} else {
					 			if($remittance_request_id) {
					 				return false;
					 			}
					 			//App_Logger::log(print_r($response,true), Zend_Log::ERR);
					 		}
					 	}else{
					 		//reusing existing sessionID
					 	}
							
					 }else{
						$session->rblSessionID = $map[$agentInfo->bcagent];
					 }
                                         
                                    if((STATUS_PROCESSED == $transaction->status || STATUS_SUCCESS == $transaction->status || STATUS_HOLD == $transaction->status)&& $transaction->txn_code && $agentInfo->bcagent) {
                                        try {
                                            $dataToApi = array('header' => array('sessiontoken' => $session->rblSessionID),
                                                                                    'bcagent' => $agentInfo->bcagent,
                                                                                    'channelpartnerrefno' => $transaction->txn_code,
                                                       );

                                            $rblApiObject = new App_Rbl_Api();
                                            $response = $rblApiObject->transactionReQuery($dataToApi);

			
                                            $rblStatusErrorCodeDesc = '';
                                            $status = $transaction->status;
									
                                                    if(isset($response['status'])){
                                                    $rblStatusErrorCodeDesc = 'Status: ' .$response['status'];
                                                    }
                                                     
                                                    if(isset($response['status']) && $response['status'] == -1 ){
                                                    return false;
                                                    }

                                                    if(isset($response['NPCIResponsecode'])){
                                                            $rblStatusErrorCodeDesc = $rblStatusErrorCodeDesc .', Code: ' .$response['NPCIResponsecode'];
                                                    }

                                                    if(isset($response['paymentstatus'])) {

                                                    if(isset($response['status']) && $response['status'] == 1 && isset($response['remarks']) && ( strcasecmp($response['remarks'],'SUCCESSUnKnown')==0)) {
                                                                                    $status = STATUS_HOLD;
                                                                                    $updateArr = array(
                                                                                    'status'        => $status,
                                                                                    'rbl_transaction_id' => $response['transactionid'],
                                                                                    'txn_code' => $transaction->txn_code,
                                                                                    'neft_remarks' => substr($rblStatusErrorCodeDesc,0,250)
                                                                            );

                                                                            $resUpdate = $this->updateReq($remittance_request_id,$updateArr);
                                                    }                                                                                                
                                                    else
                                                    {
                                                        if($response['paymentstatus'] == 3 || $response['paymentstatus'] == 4 ) {
                                                         $status = STATUS_FAILURE;

                                                            $updateArr = array(
                                                                    'status'        => $status,
                                                                    'rbl_transaction_id' => $response['transactionid'],
                                                                    'txn_code' => $transaction->txn_code,
                                                                    'neft_remarks' => substr($rblStatusErrorCodeDesc,0,250)
                                                            );
                                                            $resUpdate = $this->updateReq($remittance_request_id,$updateArr);
                                                        }
                                                        else if(date_create($transaction->date_created) < (date('Y-m-d',strtotime("-3 days"))))
                                                        { 
                                                            if($response['paymentstatus'] == 2) {
                                                                    $status = STATUS_SUCCESS;
                                                                    $updateArr = array(
                                                                            'status'        => $status,
                                                                            'rbl_transaction_id' => $response['transactionid'],
                                                                            'txn_code' => $transaction->txn_code,
                                                                            'neft_remarks' => substr($rblStatusErrorCodeDesc,0,250)
                                                                    );

                                                                    $resUpdate = $this->updateReq($remittance_request_id,$updateArr);
                                                            } elseif($response['paymentstatus'] == 0 || $response['paymentstatus'] == 1 ) {
                                                                    $status = STATUS_PROCESSED;

                                                                    $updateArr = array(
                                                                            'status'        => $status,
                                                                            'rbl_transaction_id' => $response['transactionid'],
                                                                            'txn_code' => $transaction->txn_code,
                                                                           'neft_remarks' => substr($rblStatusErrorCodeDesc,0,250)
                                                                    );

                                                                    $resUpdate = $this->updateReq($remittance_request_id,$updateArr);
                                                            } 
                                                        }
                                                    }
                                                                    
                                                                    $datastatus = array();
                                                                    $datastatus['remittance_request_id'] = $transaction->id;
                                                                    $datastatus['status_old'] = $transaction->status;
                                                                    $datastatus['status_new'] = $status;
                                                                    $datastatus['by_remitter_id'] = $transaction->remitter_id;
                                                                    $datastatus['by_agent_id'] = $transaction->agent_id;
                                                                    $datastatus['by_ops_id'] = TXN_OPS_ID;
                                                                    $datastatus['date_created'] = new Zend_Db_Expr('NOW()');
                                                                    $remittancestatuslog = new Remit_Ratnakar_Remittancestatuslog();

                                                                    $resLog = $remittancestatuslog->addStatus($datastatus);

                                                    }									
			} catch(Exception $e) { }
			
			}
				
		} // foreach
		return true;	
	
	}
        
        
        
        
        public function reQueryForCron($remittance_request_id = false) {
		error_log('Doing requery for CRON');
		
		 $session = new Zend_Session_Namespace('App.Agent.Controller');
		 $agentsModel = new Agents();
			$user = Zend_Auth::getInstance()->getIdentity();
				 $select = $this->_db->select()
                 ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST)
                 ->where("status = '" . STATUS_PROCESSED . "' OR status = '" . STATUS_HOLD . "'")->order('agent_id ASC');
			 
			   	//if requeried from cron, pick only transactions that are older 
			$select = $select->where("((flag=2 and date_created <= date_sub(now(), interval 72 hour) and date_created >= date_sub(now(), interval 312 hour)) OR ( flag=3 and date_created >= date_sub(now(), interval 312 hour) and date_created <= date_sub(now(), interval 4 hour)))");
			 
			   
			$transactions = $this->_db->fetchAll($select);
			if(count($transactions) == 0) {
					return;
			}
			$map = array();
			
			foreach($transactions as $transaction) {
				$transaction = (object) $transaction;	
					$agentInfo = (object)$agentsModel->findagentById($transaction->agent_id);
					 if(!isset($map[$agentInfo->bcagent])) {
					 	if(!isset($session->rblSessionID)){
					 		$rblApiObject = new App_Rbl_Api();
					 		$rbiResponse = $rblApiObject->channelPartnerLogin(array('username' => RBL_CHANNEL_PARTNER_LOGIN_USERNAME,
					 				'password' => RBL_CHANNEL_PARTNER_LOGIN_PASSWORD,
					 				'bcagent' => RBL_CRON_SCHEDULER_BCAGENT));
					 			
					 		if(isset($rbiResponse['status']) && $rbiResponse['status']) {
					 			$session->rblSessionID = $rbiResponse['sessiontoken'];
					 			$map[$agentInfo->bcagent] = $session->rblSessionID;
					 		} else {
					 			if($remittance_request_id) {
					 				return false;
					 			}
					 			//App_Logger::log(print_r($response,true), Zend_Log::ERR);
					 		}
					 	}else{
					 		//reusing existing sessionID
					 	}
							
					 }else{
						$session->rblSessionID = $map[$agentInfo->bcagent];
					 }
		if((STATUS_PROCESSED == $transaction->status || STATUS_HOLD == $transaction->status)&& $transaction->txn_code && $agentInfo->bcagent) {
			try {
					$dataToApi = array('header' => array('sessiontoken' => $session->rblSessionID),
																'bcagent' => $agentInfo->bcagent,
																'channelpartnerrefno' => $transaction->txn_code,
												   );
						
												$rblApiObject = new App_Rbl_Api();
												$response = $rblApiObject->transactionReQuery($dataToApi);
												
			
                                $rblStatusErrorCodeDesc = '';
									
									if(isset($response['status'])){
    									$rblStatusErrorCodeDesc = 'Status: ' .$response['status'];
									}
									
									if(isset($response['NPCIResponsecode'])){
										$rblStatusErrorCodeDesc = $rblStatusErrorCodeDesc .', Code: ' .$response['NPCIResponsecode'];
									}
												
									if(isset($response['paymentstatus'])) {
                        if(isset($response['status']) && $response['status'] == 1 && isset($response['remarks']) && ( strcasecmp($response['remarks'],'SUCCESSUnKnown')==0)) {
                                                                        				$status = STATUS_HOLD;
                                                                                                        $updateArr = array(
													'status'        => $status,
													'rbl_transaction_id' => $response['transactionid'],
													'txn_code' => $transaction->txn_code,
													'neft_remarks' => substr($rblStatusErrorCodeDesc,0,250)
												);
												
												$resUpdate = $this->updateReq($remittance_request_id,$updateArr);
                                                                             }
                                                                                                
                                                                               else {
											if($response['paymentstatus'] == 2) {
												$status = STATUS_SUCCESS;
												$updateArr = array(
													'status'        => $status,
													'rbl_transaction_id' => $response['transactionid'],
													'txn_code' => $transaction->txn_code,
													'neft_remarks' => substr($rblStatusErrorCodeDesc,0,250)
												);
												
												$resUpdate = $this->updateReq($remittance_request_id,$updateArr);
											} elseif($response['paymentstatus'] == 0 || $response['paymentstatus'] == 1 ) {
												$status = STATUS_PROCESSED;
												
												$updateArr = array(
													'status'        => $status,
													'rbl_transaction_id' => $response['transactionid'],
													'txn_code' => $transaction->txn_code,
													'neft_remarks' => substr($rblStatusErrorCodeDesc,0,250)
												);
												
												$resUpdate = $this->updateReq($remittance_request_id,$updateArr);
											} elseif($response['paymentstatus'] == 3 || $response['paymentstatus'] == 4 ) {
												$status = STATUS_FAILURE;
												
												$updateArr = array(
													'status'        => $status,
													'rbl_transaction_id' => $response['transactionid'],
													'txn_code' => $transaction->txn_code,
													'neft_remarks' => substr($rblStatusErrorCodeDesc,0,250)
												);
												$resUpdate = $this->updateReq($remittance_request_id,$updateArr);
											}
										 }
											$datastatus = array();
											$datastatus['remittance_request_id'] = $transaction->id;
											$datastatus['status_old'] = $transaction->status;
											$datastatus['status_new'] = $status;
											$datastatus['by_remitter_id'] = $transaction->remitter_id;
											$datastatus['by_agent_id'] = $transaction->agent_id;
											$datastatus['by_ops_id'] = TXN_OPS_ID;
											$datastatus['date_created'] = new Zend_Db_Expr('NOW()');
											$remittancestatuslog = new Remit_Ratnakar_Remittancestatuslog();
											
											$resLog = $remittancestatuslog->addStatus($datastatus);
												
									}									
			} catch(Exception $e) { }
			
			}
				
		} // foreach
		return true;	
	
	}
        
      	private function splitTxnAmount($value, $count){
		$result = array();
		
		$runningTotal = 0;
		for ($i = 0; $i < $count; $i++) {
			$remainder = $value - $runningTotal;
			$share = $remainder > 0 ? floor($remainder / ($count - $i)) : 0;
			$result[$i] = $share;
			$runningTotal =  $runningTotal + $share;
		}
		
		if ($runningTotal < $value) {
			$result[$count - 1] =  $result[$count - 1] + $value - $runningTotal;
		}
		
		//return $result;
	 		for ($i = 0; $i < $count; $i++) { 
					error_log("\n Txn Amount:" .$i. ": " .$result[$i]);
			}
		return $result;
				
	}

    private function getRemittanceDoneForMonth($param){
    	$remittanceArr = $this->getRemitterRemittanceCountandSum($param['remitter_id']);
    	$remittanceDoneForMonth = $remittanceArr['total'];
    	return $remittanceDoneForMonth;
    }
        
        
    public function post($remittance_request_id = false){
    	error_log('Doing post');
    	$session = new Zend_Session_Namespace('App.Agent.Controller');
    	$agentsModel = new Agents();
    	$user = Zend_Auth::getInstance()->getIdentity();
    	$select = $this->_db->select()
    	->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST)
    	->where("status = '" . STATUS_IN_PROCESS . "'")->order('agent_id ASC');
    	if($remittance_request_id) {
    		//add remitter request id if requeried manually
    		$select = $select->where("id = '".$remittance_request_id."'");
    	}else{
    		$select = $select->where("date_created >= date_sub(now(), interval 120 hour)");
    	}
    	 
    	$transactions = $this->_db->fetchAll($select);
    	if(count($transactions) == 0) { 
    		return; 
    	}
    	
    	foreach($transactions as $transaction) {
    		$transaction = (object) $transaction;
    		$agentInfo = (object)$agentsModel->findagentById($transaction->agent_id);
    		App_Logger::log("POST : Doing channel login for :". $agentInfo->bcagent, Zend_Log::INFO);

    		$rblApiObject = new App_Rbl_Api();
    		$rbiResponse = $rblApiObject->channelPartnerLogin(array('username' => RBL_CHANNEL_PARTNER_LOGIN_USERNAME,
    				'password' => RBL_CHANNEL_PARTNER_LOGIN_PASSWORD,
    				'bcagent' => $agentInfo->bcagent));
    		
    		error_log('BC AGENT : '. $agentInfo->bcagent);
    	    	App_Logger::log("POST : BC AGENT :". $agentInfo->bcagent .", txn_code: " . $transaction->txn_code, Zend_Log::INFO);
	
    		if(isset($rbiResponse['status']) && $rbiResponse['status']) {
    			App_Logger::log("POST : Channel Partner login success for agentId :". $agentInfo->bcagent, Zend_Log::INFO);
    			 
    			$session->rblSessionID = $rbiResponse['sessiontoken'];
    			
    			try {
    				$remitters = new Remit_Ratnakar_Remitter();
    				$remitter_data = $remitters->getRemitterById($transaction->remitter_id);
    			
    				$beneficiary = new Remit_Ratnakar_Beneficiary();
    				$beneficiariesList = $beneficiary->getBeneficiaryDetails($transaction->beneficiary_id);
    				if($transaction->txn_code && $agentInfo->bcagent) {
    					App_Logger::log("POST : Sending transaction request to RBL :". $agentInfo->bcagent, Zend_Log::INFO);
    						
    					$dataToApi = array('header' => array('sessiontoken' => $session->rblSessionID),
    							'bcagent' => $agentInfo->bcagent,
    							'remitterid' => $remitter_data['remitterid'],
    							'beneficiaryid' => $beneficiariesList['beneficiary_id'],
    							'amount' => floor($transaction->amount),
    							'remarks' => 'Transfer',
    							'cpid' => RBL_CHANNEL_CPID,
    							'channelpartnerrefno' => $transaction->txn_code,
    							'flag' => $transaction->flag
    					);
    			
    					$rblApiObject = new App_Rbl_Api();
    					$response = $rblApiObject->transaction($dataToApi);
    			
    					App_Logger::log("POST : transaction response from RBL :". implode($response), Zend_Log::INFO);
if(isset($response['status'])) {
    						if(isset($response['NPCIResponsecode']) && in_array($response['NPCIResponsecode'],array('08','91','12'))){
    							//these transactions with error codes have to be marked as Hold
    							$status = STATUS_HOLD;
    						}else {
    							$rblStatusErrorCodeDesc = 'Status: ' .$response['status'];
    					  if(isset($response['status'])&& $response['status'] == 1 && isset($response['remarks']) &&  ( strcasecmp($response['remarks'],'SUCCESSUnKnown')==0)) {
									$status = STATUS_HOLD;
                                                        } 
                                                        else 
                                                        {	
    							if($response['status'] == 1 && $transaction->flag == 2) {
    								$status = STATUS_SUCCESS;
    							}
    							else if($response['status'] == 1 && $transaction->flag == 3){
    								$status = STATUS_PROCESSED;
    							}
    							else {
    								//for all other status mark as failure
    								$status = STATUS_FAILURE;
    							}
    						} }
    						if(isset($response['NPCIResponsecode'])){
    							$rblStatusErrorCodeDesc = $rblStatusErrorCodeDesc .', Code: ' .$response['NPCIResponsecode'];
    						}
    						
    						App_Logger::log("POST : Updating transaction response in DB :", Zend_Log::INFO);
    						
    						$updateArr = array(
    								'status'        => $status,
    								'rbl_transaction_id' => $response['RBLtransactionid'],
    								'txn_code' => $transaction->txn_code,
    								'utr' => isset($response['bankrefno']) ? substr($response['bankrefno'],0,16) : '',
    								'neft_remarks' => substr($rblStatusErrorCodeDesc,0,250)
    						);
    						
    						$resUpdate = $this->updateReq($remittance_request_id,$updateArr);
    						
    						$datastatus = array();
    						$datastatus['remittance_request_id'] = $transaction->id;
    						$datastatus['status_old'] = $transaction->status;
    						$datastatus['status_new'] = $status;
    						$datastatus['by_remitter_id'] = $transaction->remitter_id;
    						$datastatus['by_agent_id'] = $transaction->agent_id;
    						$datastatus['by_ops_id'] = TXN_OPS_ID;
    						$datastatus['date_created'] = new Zend_Db_Expr('NOW()');
    						$remittancestatuslog = new Remit_Ratnakar_Remittancestatuslog();
    						
    						$resLog = $remittancestatuslog->addStatus($datastatus);
    						    						
    						App_Logger::log("POST : Updated transaction response in DB :", Zend_Log::INFO);
    					}
    				}
    			} catch(Exception $e){
    				error_log('Error in NEFT POST');
    			}
    		}else{
    			App_Logger::log("POST : Channel Partner login failed for agentId :". $agentInfo->bcagent, Zend_Log::INFO);
    		}
    	
    	} // foreach
    	return true;
    }

    public function instatntTransfer($params){

        try{
                $user = Zend_Auth::getInstance()->getIdentity();    
                $beneficiary = new Remit_Ratnakar_Beneficiary();
                $remittancestatuslog = new Remit_Ratnakar_Remittancestatuslog();
                $m = new App\Messaging\Remit\Ratnakar\Agent();
                $beneId = ($params['beneID']> 0) ? $params['beneID'] : 0;
                $amount = ($params['amount']> 0) ? $params['amount'] : 0;
                
                $detail = $beneficiary->getBeneficiaryDetails($beneId);
                
                
                $bank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
                $session = new Zend_Session_Namespace('App.Agent.Controller');


                $currentDate = date( 'Y-m-d', time());
                $totalFee = 0;
                $cumulativeAmtSoFar = 0;
                $cumulativeAmtRow = $this->getTxnRemitterOnSpecificDate($session->remitter_id,$user->product_id,$currentDate,$user->id,$beneId);
                if($cumulativeAmtRow ) {
                    $cumulativeAmtSoFar = ($cumulativeAmtRow['total'] > 0) ? $cumulativeAmtRow['total'] : 0; 
                    $totalFee = $cumulativeAmtRow['fee_total'] + $cumulativeAmtRow['st_total'];
                }
                $fee = $this->calculateFeeForRatnakar($cumulativeAmtSoFar, $amount, $totalFee); 				
                App_Logger::log("FEE_LOGGER Cumulative:". $cumulativeAmtSoFar . " Amount:".$amount . "  Fee:".$fee , Zend_Log::INFO);


                $feeplan = new FeePlan();
                $feeArr = $feeplan->getRemitterFee($user->product_id, $user->id);


                $numberOfTransactionsRequired = floor($amount / NON_KYC_MAX_TXN_AMOUNT_LIMIT);
                $lastTransactionAmount = $amount% NON_KYC_MAX_TXN_AMOUNT_LIMIT;
		 
                $feeComponent = Util::getFeeComponents($fee);
		 
                $param = array('agent_id' =>$user->id,
                               'product_id' =>$user->product_id,
                               'remitter_id' =>$session->remitter_id,
                               'amount' =>$amount,
                               'fee_amt' =>$feeComponent['partialFee'],
                               'service_tax' =>$feeComponent['serviceTax'],
                		'bank_unicode' => $bank->bank->unicode,
                               );

                $totalAmount = $param['amount'] + $param['fee_amt'] + $param['service_tax'];
                $param['amount'] = $totalAmount;
                
                $balanceValidator = new Validator_LimitValidator();
                $isBalanceAvailable = $balanceValidator->chkAvailableAgentBalance($param['agent_id'],$param['amount']);
                $monthlyLimitValidatorResponse = array();
                     
                if ($isBalanceAvailable){
					error_log('balance check passed');
                	$ratValidator = new Remit_Ratnakar_Validator();
                	$param['amount'] = $amount; //the amount for monthly limit validator should be on txnamount only
                	$monthlyLimitValidatorResponse = $ratValidator->validateRemitterMonthlyLimit($param);
                }
                
                if(!$monthlyLimitValidatorResponse['success']){
                	//if failure
                	 error_log('monthly check failed');
                	$remittanceDoneForMonth = $this->getRemittanceDoneForMonth($param);
                	$messageOnSubmit = "Your monthly transaction limit is ".CURRENCY_RUPEES."".$monthlyLimitValidatorResponse['limit'];
                	$messageOnSubmit = $messageOnSubmit.". You have already made transactions for ".CURRENCY_RUPEES."".$remittanceDoneForMonth;
                	$messageOnSubmit = $messageOnSubmit.". Please try a lower amount";
                	 
                	echo '<script type="text/javascript">alert("'.$messageOnSubmit.'");</script>';
                	exit;
                }
			error_log('Checking for split...');

                if($lastTransactionAmount == 0){
                	//equal split possible
                	$numberOfTxns = $numberOfTransactionsRequired;
                }else{
                	$numberOfTxns = $numberOfTransactionsRequired+1;
                }
                
                $splitTxnAmounts = array();
                $splitTxnAmounts = $this->splitTxnAmount($amount, $numberOfTxns);
                $isLast = false;
                $successAmount = 0;
                $failAmount = 0;
                $isPosted = false;
                
                for($i = 1; $i <= $numberOfTxns; $i++){
                	if($i == $numberOfTxns){
                		$isLast = true;
                	}
                	$txnResponse = $this->doTransaction($feeArr,$splitTxnAmounts[$i-1],$session,$remittancestatuslog,$params['flag'],$beneId,$bank,$m,$detail,$beneficiary,$feeplan,$isLast,$amount);
                	error_log('IsPosted');
                	error_log($txnResponse['isPosted']);

                	error_log('success');
                	error_log($txnResponse['success']);
                	 
					if($txnResponse['isPosted']){
                		$isPosted = true;
                	}
                	 
					if($txnResponse['success']){
                		$successAmount = $successAmount + $splitTxnAmounts[$i-1];
                	}
                	 
                	if($txnResponse['success'] && !$isLast){
                		continue;
                	}else{
                		break;
                	}
                }
                
                $failAmount = $amount - $successAmount;
                

 				if($isPosted){
                	if($failAmount == 0){
                		$messageOnSubmit = "SUCCESS : Your fund transfer for ".CURRENCY_RUPEES.$amount." has been initiated";
                	}else{
                		if($successAmount > 0){
                			$messageOnSubmit = "PARTIAL SUCCESS : Your fund transfer for ".CURRENCY_RUPEES.$successAmount." has been initiated successfully. ";
                			$messageOnSubmit = $messageOnSubmit . "However another fund Transfer for ".CURRENCY_RUPEES.$failAmount." is NOT processed due to an error.";
                		}else{
                			$messageOnSubmit = "FAILURE : Your fund transfer for ".CURRENCY_RUPEES.$amount." has NOT been initiated";
                		}
                	}
                }else{
                	if($failAmount == 0){
                		$messageOnSubmit = "SUCCESS: Your remittance request is accepted. Credit will happen during next NEFT working hours.";
                	}else{
                		if($successAmount > 0){
                			$messageOnSubmit = "PARTIAL SUCCESS : Your fund transfer for ".CURRENCY_RUPEES.$successAmount." is accepted. Credit will happen during next NEFT working hours. ";
                			$messageOnSubmit = $messageOnSubmit . "However another fund transfer for ".CURRENCY_RUPEES.$failAmount." is NOT processed due to an error.";
                		}else{
                			$messageOnSubmit = "FAILURE : Your remittance request for ".CURRENCY_RUPEES.$amount." has NOT been accepted";
                		}
                	}
                }
                
                $remittanceDoneForMonth = $this->getRemittanceDoneForMonth($param);
				
                echo '<script type="text/javascript">$("#remittanceDone").text('.$remittanceDoneForMonth.');</script>';
                echo '<script type="text/javascript">alert("'.$messageOnSubmit.'");</script>';

				exit;    
            }
            catch (Exception $e) {
                $errMsg = $e->getMessage();
                echo '<div class="msg msg-error"><p>'.$errMsg.'</p></div>';
                //App_Logger::log($e->getMessage(), Zend_Log::ERR);
                exit;
            }
    }

    public function calculateFeeForRatnakar($cumulativeAmtSoFar, $currAmount, $totalFee) {
        $feeSoFar = 0.0;
                
        if($cumulativeAmtSoFar != 0) {
            //$feeSoFar = $this->calculateFeeForRatnakar(0,$cumulativeAmtSoFar);
            $feeSoFar = $totalFee;
        }
        
        $total = $cumulativeAmtSoFar + $currAmount;
        $feeComm = 0.0;
        
        $feeStructure = array();
        $user = Zend_Auth::getInstance()->getIdentity();
        $feeplan = new FeePlan();
        App_Logger::log("calculateFeeForRatnakar : productId :" .$user->product_id, Zend_Log::INFO);
        
        $feeStructure = $feeplan->getFeeStructureDetails($user->product_id, TXNTYPE_REMITTANCE_FEE);
        
        for($i = 0; $i < count($feeStructure); $i++) {
                App_Logger::log("Inside calculateFeeForRatnakar : Fee total :" .$feeStructure[$i]['f_min_cum_amount'], Zend_Log::INFO);
                App_Logger::log("Inside calculateFeeForRatnakar : Fee total :" .$feeStructure[$i]['f_max_cum_amount'], Zend_Log::INFO);
                App_Logger::log("Inside calculateFeeForRatnakar : Fee Pct :" .$feeStructure[$i]['f_is_pct'], Zend_Log::INFO);
                App_Logger::log("Inside calculateFeeForRatnakar : Fee Rate :" .$feeStructure[$i]['f_fee_rate'], Zend_Log::INFO);

        	        	 
            if($total <= $feeStructure[$i]['f_max_cum_amount']) {
                if($feeStructure[$i]['f_is_pct']) {  // percentage
                    $feeComm = ($feeStructure[$i]['f_fee_rate'] * $total) / 100;
                    if($feeComm < $feeStructure[$i]['f_min']) {
                        $feeComm = $feeStructure[$i]['f_min'];
                        if($feeSoFar < $feeStructure[$i]['f_max']) {
                            $feeSoFar = 0.0;
                        } elseif($feeSoFar == $feeStructure[$i]['f_max']) {
                            $feeSoFar = $feeStructure[$i]['f_min'];
                        }
                    } elseif($feeComm > $feeStructure[$i]['f_min']) {
                        $feeComm = $feeStructure[$i]['f_max'];
                    }
                } else {
                    $feeComm = $feeStructure[$i]['f_fee_rate'];    
                }
                break;
            }
        }
        App_Logger::log("calculateFeeForRatnakar : feeComm :" .$feeComm, Zend_Log::INFO);
        App_Logger::log("calculateFeeForRatnakar : feeSoFar :" .$feeSoFar, Zend_Log::INFO);
        
        return $feeComm - $feeSoFar;
    }


   public function getCommissionTransactions($param) {
	error_log('date is: ');
	error_log($param['date']);
        $mobileno = isset($param['mobile_no']) ? $param['mobile_no'] : '';
	$productId = isset($param['product_id']) ? $param['product_id'] : '';
        $txnno = isset($param['txn_no']) ? $param['txn_no'] : 0;

    	$date = $param['date'];


    	$agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
    	$decryptionKey = App_DI_Container::get('DbConfig')->key;
    	$bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'" . $decryptionKey . "') as ben_account_number");
    	
    	$select = $this->select();
		$select->from(DbTable::TABLE_TXN_AGENT . " as txn", array('txn.amount', 'txn.txn_type','txn.date_created as txn_date'));
		$select->setIntegrityCheck(false);
		$select->join(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST." as rat", "txn.ratnakar_remittance_request_id = rat.id" , array('rat.txn_code', 'rat.status as txn_status', 'rat.date_created','rat.utr','rat.batch_name','rat.flag'));
				
		$select->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS . " as rem", "rem.id = txn.ratnakar_remitter_id", array( 'concat(rem.name," ",rem.last_name) as remit_name','rem.mobile as mobile_number'));
		$select->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", "rat.beneficiary_id = b.id ", array('b.name as bene_name', 'b.bank_name as bene_bankname', 'b.ifsc_code as bene_ifsccode',$bankAccountNumber));
		
		$select->joinLeft(DbTable::TABLE_AGENTS . " as a", "txn.agent_id = a.id ", array('a.id as agent_id', 'a.agent_code', 'concat(a.first_name," ",a.last_name) as agent_name','a.mobile1 as agent_mobile','a.email as agent_email', 'a.user_type as agent_user_type'));
		
		$select->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "txn.agent_id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", array('ad.estab_city', 'ad.estab_pincode', 'ad.bank_ifsc_code as agent_ifsc_code', 'ad.bank_account_number as agent_bank_account_number'));
		
    	if ($agentId > 0){
			$select->where("txn.agent_id = ? ", $agentId);
                        $select->where("date(txn.date_created) = ?", $date);

		}else{
		
                        $datefrom = $param['date_from'];
                        
                        if(!empty($datefrom)){
                        	$select->where("txn.date_created >= ?", $datefrom);
                        	$select->where("txn.date_created <= ?", $date);
                        }
	
		}
             if ($mobileno != '')
              $select->where("rem.mobile = ? ", $mobileno);     
	     
	     if($productId != '')
		$select->where("rat.product_id = ? ", $productId);     
	     
             if ($txnno > 0)
              $select->where("rat.txn_code = ? ", $txnno);
             
              $select->where("txn.product_id = rat.product_id ");		
		$select->where("txn.txn_type IN('COMM','RCOM')");
		//$select->where("date(DATE_ADD(rat.date_created,interval 1 day)) = ?", $date);
//		$select->where("date(rat.date_created) = ?", $date);
			
    	$row = $this->fetchAll($select);
        
        $retData = $row->toArray();

 	$totalData = count($retData);
        
        for ($j = 0; $j < $totalData; $j++) {
        	$retData[$j]['txn_date'] = Util::returnDateFormatted($retData[$j]['txn_date'], "Y-m-d", "d-m-Y", "-");
		error_log(' mojo :' .$retData[$j]['txn_date']);
        }

      	return $retData;
    } 
 
  
    public function getRemittanceforPurseID($param) {
        
       $from = $param['from'];
       $to = $param['to'];
       $status = array(FLAG_SUCCESS,STATUS_IN_PROCESS,STATUS_PROCESSED,STATUS_FAILURE,STATUS_HOLD,STATUS_REFUND); 
       //Enable DB Slave
        $this->_enableDbSlave();
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('sum(amount) as total_remittance'));
        $select->where("rr.customer_purse_id = ?", $param['customer_purse_id']);
        $select->where("rr.status IN (?)",$status); 
        $select->where("rr.date_created BETWEEN '$from' AND '$to'");
//        echo $select->__toString();//exit;
        $rows = $this->_db->fetchRow($select);
        $this->_disableDbSlave();
        return $rows;
    } 
    
     public function getRefundforPurseID($param) {
       $from = $param['from'];
       $to = $param['to'];
       //Enable DB Slave
        $this->_enableDbSlave();
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND . " as rr", array('sum(amount) as total_refund'));
        $select->where("rr.customer_purse_id = ?", $param['customer_purse_id']);
        $select->where("rr.status = '" . STATUS_SUCCESS . "'");
        $select->where("rr.date_created BETWEEN '$from' AND '$to'");
//        echo $select->__toString();exit;
        $rows = $this->_db->fetchRow($select);
        $this->_disableDbSlave();
        return $rows;
    } 
    
    public function getRemitRefundforPurseID($param) {
       $from = $param['from'];
       $to = $param['to'];
       //Enable DB Slave
        $this->_enableDbSlave();
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND . " as rr", array('sum(rr.amount) as total_remit_refund'))
        ->join(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rrr", "rr.remittance_request_id = rrr.id AND date(rr.date_created) = date(rrr.date_created)", array());
        $select->where("rr.customer_purse_id = ?", $param['customer_purse_id']);
        $select->where("rr.status = '" . STATUS_SUCCESS . "'");
        $select->where("rr.date_created BETWEEN '$from' AND '$to'");
     //   echo $select->__toString();exit;
        $rows = $this->_db->fetchRow($select);
        $this->_disableDbSlave();
        return $rows;
    }
     
    /* 
     * getAgentRemittanceRequestsForNeftBatch() will return the remitters requests for neft for cron / for in_process txns
     */

    public function getAgentRemittanceRequestsForNeftBatch() {
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'" . $decryptionKey . "') as `bank_account_number`");
        $branchAddress = new Zend_Db_Expr("AES_DECRYPT(`b`.`branch_address`,'" . $decryptionKey . "') as branch_address");
        $mobile = new Zend_Db_Expr("AES_DECRYPT(`b`.`mobile`,'" . $decryptionKey . "') as mobile");
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('id as remittance_request_id', 'remitter_id', 'beneficiary_id', 'agent_id', 'ops_id', 'amount', 'date_created', 'sender_msg', 'product_id', 'fee', 'service_tax','txn_code','rbl_transaction_id','flag'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", "rr.beneficiary_id =b.id and b.status = '" . STATUS_ACTIVE . "'", array('b.name as beneficiary_name', 'nick_name', 'ifsc_code', $bankAccountNumber, 'branch_name', 'branch_city', $branchAddress, 'bank_account_type', $mobile, 'bank_name'));

        $select->join(DbTable::TABLE_PRODUCTS . " as p", "p.id=rr.product_id AND p.is_neftbatch='".FLAG_YES."'", array('p.id as product_id'));                
        $select->where("rr.status = ?", STATUS_IN_PROCESS);
        
        return $this->_db->fetchAll($select);
    }
    
    /** refund for trial balance **/
    public function getTotalRefundTxnLoad($param) {
        $date = isset($param['date']) ? $param['date'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $productId = isset($param['product_id']) ? $param['product_id'] : '';
        $custPurseId = isset($param['customer_purse_id']) ? $param['customer_purse_id'] : '';
        $wallet_type = isset($param['wallet_type']) ? $param['wallet_type'] : '';
        
        $select = $this->select();
        $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('sum(rr.amount) as agent_total_remittance', 'sum(rr.fee) as agent_total_remittance_fee', 'sum(rr.service_tax) as agent_total_remittance_stax', 'count(rr.id) as agent_total_remittance_count'));
        $select->setIntegrityCheck(false);
        $select->join(DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND . " as rf", "rf.remittance_request_id = rr.id", array());
        $select->join(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id = rr.purse_master_id ",array());
        
        $select->where("rr.status = '" . STATUS_REFUND."'");
        
        if ($productId > 0) {
            $select->where('rr.product_id = ?', $productId);
        }
        if ($custPurseId != '') {
            $select->where('rr.customer_purse_id = ?', $custPurseId);
        }
        if ($date) {
            $select->where('DATE(rf.date_created) =?', $date);
        } else if ($to != '' && $from != '') {
            $select->where("rf.date_created BETWEEN '$from' AND '$to'");
        }
        if (!empty($wallet_type)) {
            $select->where("pm.is_virtual = ? " , $wallet_type);
        }
//        $select->group("product_id");
//        echo $select."<br>"; 
        $row = $this->fetchRow($select);
        return $row;
    }
    
    public function getRemittanceforPurseIDClosing($param) {
       $from = $param['from'];
       $to = $param['to'];
       //Enable DB Slave
        $this->_enableDbSlave();
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('sum(amount) as total_remittance'));
        $select->where("rr.customer_purse_id = ?", $param['customer_purse_id']);
        $select->where("rr.status = '" . FLAG_SUCCESS . "' OR rr.status = '" . STATUS_IN_PROCESS . "' OR rr.status = '" . STATUS_PROCESSED . "' OR rr.status = '" . STATUS_REFUND . "' OR rr.status = '" . STATUS_HOLD . "'");
        $select->where("rr.date_created BETWEEN '$from' AND '$to'");
//        echo $select->__toString();//exit;
        $rows = $this->_db->fetchRow($select);
        $this->_disableDbSlave();
        return $rows;
    }
    

    /*
     * remittanceNotification() will update records for remittances after Bank confirmation
     */
    public function remittanceNotification() {
        $select = $this->select();
        $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST , array('id', 'txnrefnum', 'txn_code', 'status', 'date_created'))     
                ->where("flag_response = '" . FLAG_RESPONSE_ONE."'");
        $rs = $this->fetchAll($select);
        
        $count = 0;
        
        if(count($rs) > 0) {
            foreach($rs as $val) {
                if($val['status'] == STATUS_SUCCESS) {
                    //Enable DB Slave
                    $db = Zend_Registry::get('dbconsumerAdapter');
                        $rs = $db->query("CALL sp_RemittanceNotification('".$val['txnrefnum']."', '".$val['txn_code']."', '".STATUS_SUCCESS."', '".$val['date_created']."')");  
                        $rsLoad = $rs->fetch();
                    $db->closeConnection();

                    if($rsLoad['status'] == TRUE) {
                        $this->updateReq($val['id'], array('flag_response' => FLAG_RESPONSE_TWO));
                    }
                } elseif($val['status'] == STATUS_FAILURE) {
                    //Enable DB Slave
                    $db = Zend_Registry::get('dbconsumerAdapter');
                        $rs = $db->query("CALL sp_RemittanceNotification('".$val['txnrefnum']."', '".$val['txn_code']."', '".STATUS_FAILED."', '".$val['date_created']."')");  
                        $rsLoad = $rs->fetch();
                    $db->closeConnection();
                    
                    if($rsLoad['status'] == TRUE) {
                        $this->updateReq($val['id'], array('flag_response' => FLAG_RESPONSE_TWO));
                    }
                }
                
                $count++;
            }
        }
        return $count;
    }

}

