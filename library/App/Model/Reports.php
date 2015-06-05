<?php

class Reports extends App_Model {

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
    protected $_name = DbTable::TABLE_AGENTS;

    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    protected $_rowClass = 'App_Table_AgentUser';

    public function getAgentFundRequests($param, $page = 1, $paginate = NULL) {
        $objFundReq = new FundRequest();
        return $objFundReq->getRptAgentFundRequests($param, $page, $paginate);
    }
    public function getAgentVirtualFundRequests($param, $page = 1, $paginate = NULL) { 
        $objFundReq = new FundRequest();
        return $objFundReq->getRptAgentVirtualFundRequests($param, $page, $paginate);
    }
    
    public function getAgentWiseFundRequests($param, $page = 1, $paginate = NULL) {
        $objFundReq = new FundRequest();
        return $objFundReq->getRptAgentFundRequests($param, $page, $paginate);
    }

    /* public function getAgentActivation($param,  $page = 1, $paginate = NULL){
      $objAgentUser = new AgentUser();
      return $objAgentUser->getAgentActivation($param, $page,  $paginate);
      } */

    public function getAgentActivation($param, $page = 1, $paginate = NULL) {
        $objAgentUser = new AgentUser();
        return $objAgentUser->getAgentActivation($param, $page, $paginate);
    }

    /* exportAgentWiseLoadsFromAgent function will find data for agent wise load report. 
     * it will accept param array with query filters e.g.. duration and agent id
     */

    public function exportAgentWiseLoadsFromAgent($param) {
        $objCardLoads = new CardLoads();
        $data = $objCardLoads->exportAgentWiseLoads($param);
        $objAgent = new Agents();
        $agentInfo = $objAgent->findById($param['agent_id']);
        $retData = array();

        if (!empty($data)) {

            foreach ($data as $key => $data) {
                $retData[$key]['date_created'] = $data['date_created'];
                $retData[$key]['name'] = $agentInfo['name'];
                $retData[$key]['agent_code'] = $agentInfo['agent_code'];
                $retData[$key]['txn_type'] = $data['txn_type'];
                $retData[$key]['amount'] = $data['amount'];
                $retData[$key]['cardholder_name'] = $data['cardholder_name'];
                $retData[$key]['mobile_number'] = $data['mobile_number'];
                $retData[$key]['ecs_product_code'] = $data['ecs_product_code'];
                $retData[$key]['txn_code'] = $data['txn_code'];
            }
        }

        return $retData;
    }

    /* exportAgentFundRequests function will find data for Agent fund requests report. 
     * it will accept param array with query filters e.g.. duration
     */

    public function exportAgentFundRequests($param) {
        $objFundReq = new FundRequest();
        $data = $objFundReq->exportRptAgentFundRequests($param);

        $retData = array();

        if (!empty($data)) {
            foreach ($data as $key => $data) {
                $retData[$key]['txn_datetime'] = $data['txn_datetime'];
                $retData[$key]['agent_code'] = $data['agent_code'];
                $retData[$key]['agent_name'] = $data['agent_name'];
                $retData[$key]['agent_opening_balance'] = $data['agent_opening_balance'];
                $retData[$key]['txn_amount'] = $data['txn_amount'];
                $retData[$key]['ops_name'] = $data['ops_name'];
                $retData[$key]['comments'] = $data['comments'];
                $retData[$key]['agent_closing_balance'] = $data['agent_closing_balance'];
            }
        }

        return $retData;
    }
    
    public function exportAgentVirtualFund($param) {
        $objFundReq = new FundRequest();
        $data = $objFundReq->exportRptAgentvirtualFund($param);
        $retData = array(); 
        if (!empty($data)) {
            foreach ($data as $key => $data) { 
                $retData[$key]['req_datetime'] = $data['req_datetime'];
                $retData[$key]['txn_datetime'] = $data['txn_datetime'];
                $retData[$key]['agent_code'] = $data['agent_code'];
                $retData[$key]['agent_name'] = $data['agent_name']; 
                $retData[$key]['txn_amount'] = $data['txn_amount'];
                $retData[$key]['utr'] = $data['utr'];
                $retData[$key]['agent_opening_balance'] = $data['agent_opening_balance'];
                $retData[$key]['agent_closing_balance'] = $data['agent_closing_balance'];
                $retData[$key]['ops_name'] = $data['ops_name'];
                $retData[$key]['txn_code'] = $data['txn_code']; 
            }
        }

        return $retData;
    }
    
    public function exportUnauthVirtualFund($param) {
        $objFundReq = new FundRequest();
        $data = $objFundReq->exportRptAgentvirtualFund($param);
        $retData = array();
        if (!empty($data)) {
            foreach ($data as $key => $data) { 
                $retData[$key]['req_datetime'] = $data['req_datetime'];
                $retData[$key]['agent_code'] = $data['agent_code'];
                $retData[$key]['agent_name'] = $data['agent_name']; 
                $retData[$key]['txn_amount'] = $data['txn_amount'];
                $retData[$key]['utr'] = $data['utr'];
                $retData[$key]['agent_opening_balance'] = $data['agent_opening_balance'];
                $retData[$key]['agent_closing_balance'] = $data['agent_closing_balance'];
                $retData[$key]['status'] = $data['status']; 
            }
        }

        return $retData;
    }

    /* exportAgentWiseFundRequests function will find data for Agent wise fund requests report. 
     * it will accept param array with query filters e.g.. duration and agent id
     */

    public function exportAgentWiseFundRequests($param) {
        $objFundReq = new FundRequest();
        $data = $objFundReq->exportRptAgentWiseFundRequests($param);
        $retData = array();

        if (!empty($data)) {

            foreach ($data as $key => $data) {
                $retData[$key]['txn_datetime'] = $data['txn_datetime'];
                $retData[$key]['agent_code'] = $data['agent_code'];
                $retData[$key]['agent_name'] = $data['agent_name'];
                $retData[$key]['agent_opening_balance'] = $data['agent_opening_balance'];
                $retData[$key]['txn_amount'] = $data['txn_amount'];
                $retData[$key]['ops_name'] = $data['ops_name'];
                $retData[$key]['comments'] = $data['comments'];
                $retData[$key]['agent_closing_balance'] = $data['agent_closing_balance'];
            }
        }

        return $retData;
    }

    /* exportAgentWiseFundRequests function will find data for Agent wise fund requests report. 
     * it will accept param array with query filters e.g.. duration and agent id
     */

    public function exportAgentWiseFundRequestsFromAgent($param) {
        $objFundReq = new FundRequest();
        $data = $objFundReq->exportRptAgentWiseFundRequests($param);
        $retData = array();

        if (!empty($data)) {

            foreach ($data as $key => $data) {
                $retData[$key]['txn_datetime'] = $data['txn_datetime'];
                $retData[$key]['agent_code'] = $data['agent_code'];
                $retData[$key]['agent_name'] = $data['agent_name'];
                $retData[$key]['agent_opening_balance'] = $data['agent_opening_balance'];
                $retData[$key]['txn_amount'] = $data['txn_amount'];
                $retData[$key]['comments'] = $data['comments'];
                //$retData[$key]['agent_closing_balance']  = $data['agent_closing_balance'];                  
            }
        }

        return $retData;
    }

    /* exportAgentWiseFundRequests function will find data for Agent wise fund requests report. 
     * it will accept param array with query filters e.g.. duration and agent id
     */

    public function exportagentwiseCommReport($param) {
        $objComm = new CommissionReport();
        $data = $objComm->calculateCommission($param);
        $retData = array();
        $objAgent = new Agents();
        $agentInfo = $objAgent->findById($param['agent_id']);
        if ($agentInfo['estab_city'] == '')
            $agentInfo['estab_city'] = $agentInfo['res_city'];

        if ($agentInfo['estab_pincode'] == '')
            $agentInfo['estab_pincode'] = $agentInfo['res_pincode'];



        if (!empty($data)) {
            $j = 0;
            $len = count($data);

            for ($i = 0; $i < $len; $i++) {
                $retData[$j]['date_formatted'] = $data[$i]['date_formatted'];
                $retData[$j]['agent_code'] = $agentInfo['agent_code'];
                $retData[$j]['name'] = $agentInfo['name'];
                $retData[$j]['estab_city'] = $agentInfo['estab_city'];
                $retData[$j]['estab_pincode'] = $agentInfo['estab_pincode'];
                $retData[$j]['transaction_type_name'] = $data[$i]['transaction_type_name'];
                $retData[$j]['transaction_amount'] = $data[$i]['transaction_amount'];
                $retData[$j]['product_code'] = $data[$i]['product_code'];
                $retData[$j]['plan_commission_name'] = $data[$i]['plan_commission_name'];
                $retData[$j]['comm_amount'] = $data[$i]['comm_amount'];

                $j++;
            }
        }

        return $retData;
    }

    /* exportagentwiseCommReportFromAgent function will find data for Agent wise fund requests report. 
     * it will accept param array with query filters e.g.. duration and agent id
     */

    public function exportagentwiseCommReportFromAgent($param) {
        $objComm = new CommissionReport();
        $data = $objComm->calculateCommission($param);
        $retData = array();
        $objAgent = new Agents();
        $agentInfo = $objAgent->findById($param['agent_id']);

        if (!empty($data)) {
            $j = 0;
            $len = count($data);

            for ($i = 0; $i < $len; $i++) {
                $retData[$j]['date_formatted'] = $data[$i]['date_formatted'];
                $retData[$j]['agent_code'] = $agentInfo['agent_code'];
                $retData[$j]['name'] = $agentInfo['name'];
                $retData[$j]['transaction_ref_no'] = $data[$i]['transaction_ref_no'];
                $retData[$j]['transaction_type_name'] = $data[$i]['transaction_type_name'];
                $retData[$j]['transaction_amount'] = $data[$i]['transaction_amount'];
                $retData[$j]['product_code'] = $data[$i]['product_code'];
                $retData[$j]['product_name'] = $data[$i]['product_name'];
                $retData[$j]['bank_name'] = $data[$i]['bank_name'];
                $retData[$j]['plan_commission_name'] = $data[$i]['plan_commission_name'];
                $retData[$j]['comm_amount'] = $data[$i]['comm_amount'];

                $j++;
            }
        }

        return $retData;
    }

    /* exportAgentActivation function will find data for Agent activation details for export data to csv. 
     * it will accept param array with query filters e.g.. city
     */

    public function exportAgentActivation($param) {
        $objAgentUser = new AgentUser();
        $data = $objAgentUser->exportAgentActivation($param);

        $retData = array();

        if (!empty($data)) {

            foreach ($data as $key => $data) {
                $retData[$key]['agent_name'] = $data['agent_name'];
                $retData[$key]['agent_code'] = $data['agent_code'];
                $retData[$key]['email'] = $data['email'];
                $retData[$key]['mobile1'] = $data['mobile1'];
                $retData[$key]['agent_address'] = $data['agent_address'];
                $retData[$key]['agent_city'] = $data['agent_city'];
                $retData[$key]['agent_limit_name'] = $data['agent_limit_name'];
                $retData[$key]['status'] = $data['status'];
                $retData[$key]['agent_app_date'] = $data['agent_app_date'];
                $retData[$key]['rejected_remarks'] = $data['rejected_remarks'];
                $retData[$key]['commission_plan_name'] = $data['commission_plan_name'];
                $retData[$key]['bank_name'] = $data['bank_name'];
            }
        }

        return $retData;
    }

    /* getAgentBalanceSheet function will find data for Agent balance sheet data on transaction date basis. 
     * it will accept param array with query filters e.g.. duration
     */

/*    public function getAgentBalanceSheet($param) {
/*
    //    if (!empty($param)) {
      //      $retReportData = array();
        //    $objFundReq = new FundRequest();
            $objFunding = new AgentFunding();
            $objRemittance = new Remit_Remittancerequest();
            $objBAPC = new BindAgentProductCommission();
            $objCardloads = new CardLoads();
            $objRemitter = new Remit_Remitter();

            foreach ($param as $dates) {
                $fromDate = $dates['from'];
                $toDate = $dates['to'];
                $fromDateOnlyArr = explode(' ', $fromDate);
                $fromDateOnly = $fromDateOnlyArr[0];
                $reportData = '';
                $currentDate = date('Y-m-d');
                $str = "'" . STATUS_UNBLOCKED . "', '" . STATUS_BLOCKED . "', '" . STATUS_LOCKED . "'";
                //getting agents details 
                $select = $this->_db->select();
                $select->from(DbTable::TABLE_AGENTS . ' as a', array('a.agent_code', 'a.id as agent_id', 'concat(a.first_name," ",a.last_name) as agent_name'));
                $select->joinLeft(DbTable::TABLE_AGENT_CLOSING_BALANCE . ' as acb', "a.id=acb.agent_id AND '" . $fromDateOnly . "'=acb.date", array('acb.closing_balance as agent_closing_balance'));
                $select->joinLeft(DbTable::TABLE_AGENT_CLOSING_BALANCE . ' as acb2', "a.id=acb2.agent_id AND DATE_SUB('" . $fromDateOnly . "', INTERVAL 1 DAY)=acb2.date", array('acb2.closing_balance as agent_opening_balance'));
                $select->where('a.enroll_status=?', STATUS_APPROVED);
                $select->where("a.status IN ($str)");
                $select->order('agent_name ASC');
                $reportData = $this->_db->fetchAll($select);
                $totalRecs = count($reportData);



                // Finding agent total funding amount, total fund load/reload, total remittance , total fee and total service tax
                for ($i = 0; $i < $totalRecs; $i++) {

                    // fetching bank name for agent for particular product and date
                    $bankDetails = $objBAPC->getAgentBinding($reportData[$i]['agent_id'], $fromDateOnly);
                    $reportData[$i]['bank_name'] = isset($bankDetails[0]['bank_name']) ? $bankDetails[0]['bank_name'] : '';
                    $bank_unicode = isset($bankDetails[0]['bank_unicode']) ? $bankDetails[0]['bank_unicode'] : '';

                    $param = array('agent_id' => $reportData[$i]['agent_id'],
                        'date' => $fromDateOnly,
                        'bank_unicode' => $bank_unicode);


                    $totalPendingFund = $objFundReq->totalpendingAgentFundRequests(array('agent_id' => $reportData[$i]['agent_id'], 'to' => $toDate, 'from' => $fromDate));
                    $fundReqAmtPending = isset($totalPendingFund['total_agent_pending_funding_amount']) ? $totalPendingFund['total_agent_pending_funding_amount'] : '0.00';

                    $totalFundingPending = $objFunding->totalPendingAgentFundRequests(array('agent_id' => $reportData[$i]['agent_id'], 'to' => $toDate, 'from' => $fromDate));
                    $fundingAmtPending = isset($totalFundingPending['total_agent_pending_funding_amount']) ? $totalFundingPending['total_agent_pending_funding_amount'] : '0.00';

                    $reportData[$i]['total_agent_pending_funding_amount'] = $fundReqAmtPending + $fundingAmtPending;

                    $totalFund = $objFundReq->getTotalAgentFund(array('agent_id' => $reportData[$i]['agent_id'], 'to' => $toDate, 'from' => $fromDate));
                    $fundReqAmt = isset($totalFund['total_agent_funding_amount']) ? $totalFund['total_agent_funding_amount'] : '0.00';

                    $totalFunding = $objFunding->getAgentTotalFund(array('agent_id' => $reportData[$i]['agent_id'], 'to' => $toDate, 'from' => $fromDate));
                    $fundingAmt = isset($totalFunding['total_agent_funding_amount']) ? $totalFunding['total_agent_funding_amount'] : '0.00';

                    $reportData[$i]['total_agent_funding_amount'] = $fundReqAmt + $fundingAmt;

                    $totalLoadReloadArr = $objCardloads->getAgentTotalLoadReload($param);
                    $totalLoadReload = isset($totalLoadReloadArr['total_agent_load_reload']) ? $totalLoadReloadArr['total_agent_load_reload'] : '0.00';

                    $totalRemittanceFeeArr = $objRemittance->getAgentTotalRemittanceFeeSTax($param);
                    $totalRemittance = isset($totalRemittanceFeeArr['agent_total_remittance']) ? $totalRemittanceFeeArr['agent_total_remittance'] : '0.00';
                    $totalRemittanceFee = isset($totalRemittanceFeeArr['agent_total_remittance_fee']) ? $totalRemittanceFeeArr['agent_total_remittance_fee'] : '0.00';
                    $totalRemittanceSTax = isset($totalRemittanceFeeArr['agent_total_remittance_stax']) ? $totalRemittanceFeeArr['agent_total_remittance_stax'] : '0.00';
                    $reportData[$i]['txn_datetime'] = $fromDateOnly;

                    $refundData = $objRemittance->getAgentTotalRemittanceRefundSTax($param);
                    $reportData[$i]['agent_total_remittance_refund'] = isset($refundData['agent_total_remittance_refund']) ? $refundData['agent_total_remittance_refund'] : '0.00';
                    $reportData[$i]['agent_total_reversal_refund_stax'] = isset($refundData['agent_total_reversal_refund_stax']) ? $refundData['agent_total_reversal_refund_stax'] : '0.00';
                    $reportData[$i]['agent_total_reversal_refund_fee'] = isset($refundData['agent_total_reversal_refund_fee']) ? $refundData['agent_total_reversal_refund_fee'] : '0.00';

                    $feeSTaxData = $objRemitter->getAgentTotalRemitterRegnFeeSTax($param);
                    // agent total remitter regn fee
                    $totalRemitterRegnFee = isset($feeSTaxData['agent_total_remitter_regn_fee']) ? $feeSTaxData['agent_total_remitter_regn_fee'] : '0.00';
                    // agent total remitter regn service tax
                    $totalRemitterRegnSTax = isset($feeSTaxData['agent_total_remitter_regn_stax']) ? $feeSTaxData['agent_total_remitter_regn_stax'] : '0.00';



                    // adding agent all transactions fetched amount
                    $reportData[$i]['agent_total_agent_transactions'] = $totalLoadReload + $totalRemittance;

                    // adding agent all types of fee
                    $reportData[$i]['agent_total_remit_fee'] = $totalRemitterRegnFee + $totalRemittanceFee;

                    // adding agent all types of service tax
                    $reportData[$i]['agent_total_remit_service_tax'] = $totalRemittanceSTax + $totalRemitterRegnSTax;

                    // calculate closing balance of current date
                    if ($currentDate == $fromDateOnly) {
                        $deductableAmount = $totalLoadReload + $totalRemittance + $totalRemittanceFee + $totalRemittanceSTax + $totalRemitterRegnFee + $totalRemitterRegnSTax;
                        $addableAmount = $reportData[$i]['total_agent_funding_amount'] + $reportData[$i]['agent_total_remittance_refund'] + $reportData[$i]['agent_total_reversal_refund_stax'] + $reportData[$i]['agent_total_reversal_refund_fee'];
                        $reportData[$i]['agent_closing_balance'] = $reportData[$i]['agent_opening_balance'] + $addableAmount - $deductableAmount;
                    }

                }

                $retReportData = array_merge($retReportData, $reportData);
            }

            return $retReportData;
        } else
            return array();
    }
*/

public function getAgentBalanceSheet($param, $agentId = 0) {

        if (!empty($param)) {
            $retReportData = array();
            $objFundReq = new FundRequest();
            $objFunding = new AgentFunding();
            $objRemittance = new Remit_Remittancerequest();
            $objBAPC = new BindAgentProductCommission();
            $objCardloads = new CardLoads();
            $objRemitter = new Remit_Remitter();
            $fundtrfrModel = new AgentFundTransfer();
            $loadModel = new Corp_Cardload();
            $paytronicLoadModel = new Corp_Ratnakar_Cardload();
//            $productModel = new Products();
//            $boiProduct = $productModel->getProductIDbyConst(PRODUCT_CONST_BOI_REMIT);
            $bankBoi = App_DI_Definition_Bank::getInstance(BANK_BOI);
            $bankBoiUnicode = $bankBoi->bank->unicode;
	    $txnAgentModel = new TxnAgent();
	     $objCommission = new CommissionReport();            
            $i = 0;
            foreach ($param as $dates) {
                $fromDate = $dates['from'];
                $toDate = $dates['to'];
                $fromDateOnlyArr = explode(' ', $fromDate);
                $fromDateOnly = $fromDateOnlyArr[0];
                $reportData1 = array();
                $reportData = array();
                $currentDate = date('Y-m-d');
                $str = "'" . STATUS_UNBLOCKED . "', '" . STATUS_BLOCKED . "', '" . STATUS_LOCKED . "'";
                
                //Enable DB Slave
                $this->_enableDbSlave();
                //getting agents details 
                $select = $this->_db->select();
                $select->from(DbTable::TABLE_AGENTS . ' as a', array('a.agent_code', 'a.id as agent_id', 'concat(a.first_name," ",a.last_name) as agent_name'));
                $select->joinLeft(DbTable::TABLE_AGENT_CLOSING_BALANCE . ' as acb', "a.id=acb.agent_id AND '" . $fromDateOnly . "'=acb.date", array('acb.closing_balance as agent_closing_balance'));
                $select->joinLeft(DbTable::TABLE_AGENT_CLOSING_BALANCE . ' as acb2', "a.id=acb2.agent_id AND DATE_SUB('" . $fromDateOnly . "', INTERVAL 1 DAY)=acb2.date", array('acb2.closing_balance as agent_opening_balance'));
                //$select->join(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION.' as b', "a.id = b.agent_id AND b.product_id <> '".$boiProduct."' ", array());
                $select->where('a.enroll_status=?', STATUS_APPROVED);
                $select->where("a.status IN ($str)");
                if($agentId > 0){
                    $select->where("a.id = ?", $agentId);
                }
                $select->order('agent_name ASC');
                $reportData1 = $this->_db->fetchAll($select);
                //Disable DB Slave
                $this->_disableDbSlave();
                $totalRecs = count($reportData1);
                

                // Finding agent total funding amount, total fund load/reload, total remittance , total fee and total service tax
                for ($j = 0; $j < $totalRecs; $j++) {

$agentId = $reportData1[$j]['agent_id'];
                	$commissionData = $txnAgentModel->getCommTxnAgentDuration($agentId, $fromDate, $toDate);
                	$revCommissionData = $txnAgentModel->getRevCommTxnAgentDuration($agentId, $fromDate, $toDate);
                	
                	$queryParam = array();
                	$queryParam['agentId'] = $agentId;
                	$queryParam['dateTo'] = $toDate;
                	$queryParam['dateFrom'] = $fromDate;
                	
                	
                	$commissionFromCommReport = $objCommission->getAgentCommissionForBalanceSheet($queryParam);
                	 
                    // fetching bank name for agent for particular product and date
                    $bankDetails = $objBAPC->getAgentBinding($reportData1[$j]['agent_id'], $fromDateOnly);
                    
                    if($bankDetails[0]['bank_unicode'] != $bankBoiUnicode){
                        $reportData[$i] = $reportData1[$j];
                        
                    
                    $reportData[$i]['bank_name'] = isset($bankDetails[0]['bank_name']) ? $bankDetails[0]['bank_name'] : '';
                    $bank_unicode = isset($bankDetails[0]['bank_unicode']) ? $bankDetails[0]['bank_unicode'] : '';

                    $param = array('agent_id' => $reportData[$i]['agent_id'],
                        'date' => $fromDateOnly,
                        'bank_unicode' => $bank_unicode);


                    /*                     * ** getting agent total pending fund amount **** */
                    $totalPendingFund = $objFundReq->totalpendingAgentFundRequests(array('agent_id' => $reportData[$i]['agent_id'], 'to' => $toDate, 'from' => $fromDate));
                    $fundReqAmtPending = isset($totalPendingFund['total_agent_pending_funding_amount']) ? $totalPendingFund['total_agent_pending_funding_amount'] : '0.00';

                    /*                     * ** getting agent total pending funding amount **** */
                    $totalFundingPending = $objFunding->totalPendingAgentFundRequests(array('agent_id' => $reportData[$i]['agent_id'], 'to' => $toDate, 'from' => $fromDate));
                    $fundingAmtPending = isset($totalFundingPending['total_agent_pending_funding_amount']) ? $totalFundingPending['total_agent_pending_funding_amount'] : '0.00';

                    $reportData[$i]['total_agent_pending_funding_amount'] = $fundReqAmtPending + $fundingAmtPending;

                    /*                     * ** getting agent total fund request amount **** */
                    $totalFund = $objFundReq->getTotalAgentFund(array('agent_id' => $reportData[$i]['agent_id'], 'to' => $toDate, 'from' => $fromDate));
                    $fundReqAmt = isset($totalFund['total_agent_funding_amount']) ? $totalFund['total_agent_funding_amount'] : '0.00';

                    /*                     * ** getting agent total funding amount **** */
                    $totalFunding = $objFunding->getAgentTotalFund(array('agent_id' => $reportData[$i]['agent_id'], 'to' => $toDate, 'from' => $fromDate));
                    $fundingAmt = isset($totalFunding['total_agent_funding_amount']) ? $totalFunding['total_agent_funding_amount'] : '0.00';

                    /*                     * ** getting agent total funding amount **** */
                    $agentArr = array(
                                        'agent_id' => $reportData[$i]['agent_id'],
                                        'to' => $toDate,
                                        'from' => $fromDate,
                                        'bank_unicode' => $bank_unicode,
                                        'on_date' => FALSE
                                    );
                    $agentArr['txn_type'] = TXNTYPE_AGENT_TOAGENT_FUND_TRANSFER;
                    $agentFundTrfrDr = $fundtrfrModel->getAgentTotalFundTrfrDr($agentArr);
                    $agentArr['txn_type'] = TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL;
                    $agentFundTrfrRvslDr = $fundtrfrModel->getAgentTotalFundTrfrDr($agentArr);
                    $agentArr['txn_type'] = TXNTYPE_AGENT_TOAGENT_FUND_TRANSFER;
                    $agentFundTrfrCr = $fundtrfrModel->getAgentTotalFundTrfrCr($agentArr);
                    $agentArr['txn_type'] = TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL;
                    $agentFundTrfrRvslCr = $fundtrfrModel->getAgentTotalFundTrfrCr($agentArr);
                    $agentFundTrfrCrAmt = (isset($agentFundTrfrCr['total_agent_fundtrfr_amount']))? $agentFundTrfrCr['total_agent_fundtrfr_amount']: 0;
                    $agentFundTrfrRvslCrAmt = (isset($agentFundTrfrRvslCr['total_agent_fundtrfr_amount']))? $agentFundTrfrRvslCr['total_agent_fundtrfr_amount']: 0;
                    $agentFundTrfrDrAmt = (isset($agentFundTrfrDr['total_agent_fundtrfr_amount']))? $agentFundTrfrDr['total_agent_fundtrfr_amount']: 0;
                    $agentFundTrfrRvslDrAmt = (isset($agentFundTrfrRvslDr['total_agent_fundtrfr_amount']))? $agentFundTrfrRvslDr['total_agent_fundtrfr_amount']: 0;
                    
                   
                    
                    $reportData[$i]['total_agent_funding_amount'] = $fundReqAmt + $fundingAmt 
                            + $agentFundTrfrCrAmt + $agentFundTrfrRvslCrAmt 
                            - $agentFundTrfrDrAmt - $agentFundTrfrRvslDrAmt;

                    /*                     * ** getting agent total load, reload, remittance amount *** */
                    $totalLoadReloadArr = $objCardloads->getAgentTotalLoadReload($param);
                    $totalLoadReloadAmt = isset($totalLoadReloadArr['total_agent_load_reload']) ? $totalLoadReloadArr['total_agent_load_reload'] : '0.00';

                    $agentArr['txn_type'] = TXNTYPE_RAT_CORP_MEDIASSIST_LOAD;
                    $corpLoad = $loadModel->getAgentTotalLoad($agentArr);
                    $corpLoadAmt = (isset($corpLoad['total_agent_load_amount']))? $corpLoad['total_agent_load_amount']: 0;

                     // Paytronic Load
                    $agentArr['txn_type'] = TXNTYPE_CARD_RELOAD;
                    $paytronicCorpLoad = $paytronicLoadModel->getAgentTotalLoad($agentArr);
                    $paytronicCorpLoad['total_agent_load_amount'] = (isset($paytronicCorpLoad['total_agent_load_amount']))? $paytronicCorpLoad['total_agent_load_amount']: 0;
                    
                    //Load Fee And service Tax
                    $totalLoadFee = isset($paytronicCorpLoad['agent_total_load_fee']) ? $paytronicCorpLoad['agent_total_load_fee'] : '0.00';
                    
                    $totalLoadSTax = isset($paytronicCorpLoad['agent_total_load_stax']) ? $paytronicCorpLoad['agent_total_load_stax'] : '0.00';
                    //
                    // Paytronic Debit
//                    $agentArr['txn_type'] = TXNTYPE_CARD_DEBIT;
//                    $agentArr['status'] = STATUS_DEBITED;
                    $agentArr['debit_api_cr'] = POOL_AC;
                    $paytronicDebit = $paytronicLoadModel->getAgentDebits($agentArr);
                    $paytronicDebit['total_agent_load_amount'] = (isset($paytronicDebit['total_agent_load_amount']))? $paytronicDebit['total_agent_load_amount']: 0;
                    
                    $agentArr['debit_api_cr'] = PAYABLE_AC;
                    $agentDebit = $paytronicLoadModel->getAgentDebits($agentArr);
                    $reportData[$i]['total_agent_debit_amount'] = (isset($agentDebit['total_agent_load_amount']))? $agentDebit['total_agent_load_amount']: 0;
                    
                   // Paytronic Reversal
                    $agentArr['cutoff'] = TRUE;
                    $paytronicReversal = $paytronicLoadModel->getAgentTotalLoad($agentArr);
                    
                    $paytronicReversal['total_agent_load_amount'] = (isset($paytronicReversal['total_agent_load_amount']))? $paytronicReversal['total_agent_load_amount']: 0;
                    
                    // Debit Reversal
                 
                     $agentDebitReversal = $paytronicLoadModel->getAgentDebitReversalsAmount($agentArr);
                     
                     $agentDebitReversal['total_agent_load_amount'] = (isset($agentDebitReversal['total_agent_load_amount'])) ? $agentDebitReversal['total_agent_load_amount'] : 0;
                     
                    $reportData[$i]['total_agent_debit_reversal_amount'] = $agentDebitReversal['total_agent_load_amount']; 
                    $totalLoadReload = $totalLoadReloadAmt + $corpLoadAmt + $paytronicCorpLoad['total_agent_load_amount'];
                            
                    $param['chk_custpurse_empty'] = TRUE;
                    /*                     * ** getting agent total remittance amount, remittance fee and remittance service tax amount *** */
                    $totalRemittanceFeeArr = $objRemittance->getAgentTotalRemittanceFeeSTax($param);
                    $totalRemittance = isset($totalRemittanceFeeArr['agent_total_remittance']) ? $totalRemittanceFeeArr['agent_total_remittance'] : '0.00';
                    $totalRemittanceFee = isset($totalRemittanceFeeArr['agent_total_remittance_fee']) ? $totalRemittanceFeeArr['agent_total_remittance_fee'] : '0.00';
                    $totalRemittanceSTax = isset($totalRemittanceFeeArr['agent_total_remittance_stax']) ? $totalRemittanceFeeArr['agent_total_remittance_stax'] : '0.00';
                    $reportData[$i]['txn_datetime'] = $fromDateOnly;

                    /*                     * ** getting agent total remittance refund and service tax amount *** */
                    $refundData = $objRemittance->getAgentTotalRemittanceRefundSTax($param);
                    $remitRefund = isset($refundData['agent_total_remittance_refund']) ? $refundData['agent_total_remittance_refund'] : '0.00';
                    $reversals =  $paytronicReversal['total_agent_load_amount'] + $paytronicDebit['total_agent_load_amount'] ;
                    $reportData[$i]['agent_total_remittance_refund'] = $remitRefund + $reversals;
                    $reportData[$i]['agent_total_reversal_refund_stax'] = isset($refundData['agent_total_reversal_refund_stax']) ? $refundData['agent_total_reversal_refund_stax'] : '0.00';
                    $reportData[$i]['agent_total_reversal_refund_fee'] = isset($refundData['agent_total_reversal_refund_fee']) ? $refundData['agent_total_reversal_refund_fee'] : '0.00';

                    /*                     * ** getting agent total remit fee *** */
                    $feeSTaxData = $objRemitter->getAgentTotalRemitterRegnFeeSTax($param);
                    // agent total remitter regn fee
                    $totalRemitterRegnFee = isset($feeSTaxData['agent_total_remitter_regn_fee']) ? $feeSTaxData['agent_total_remitter_regn_fee'] : '0.00';
                    // agent total remitter regn service tax
                    $totalRemitterRegnSTax = isset($feeSTaxData['agent_total_remitter_regn_stax']) ? $feeSTaxData['agent_total_remitter_regn_stax'] : '0.00';


                    /*                     * ** now adding all fetched amounts above *** */

                    // adding agent all transactions fetched amount
                    $reportData[$i]['agent_total_agent_transactions'] = $totalLoadReload + $totalRemittance;

                    // adding agent all types of fee
                    $reportData[$i]['agent_total_remit_fee'] = $totalRemitterRegnFee + $totalRemittanceFee + $totalLoadFee;

                    // adding agent all types of service tax
                    $reportData[$i]['agent_total_remit_service_tax'] = $totalRemittanceSTax + $totalRemitterRegnSTax  + $totalLoadSTax;

                    $isCurrentDate = false;

                    // calculate closing balance of current date
                    if ($currentDate == $fromDateOnly) {
                        $deductableAmount = $totalLoadReload + $totalRemittance + $totalRemittanceFee + $totalRemittanceSTax + $totalRemitterRegnFee + $totalRemitterRegnSTax + $agentDebitReversal['total_agent_load_amount'] + $totalLoadFee + $totalLoadSTax;
                        $addableAmount = $reportData[$i]['total_agent_funding_amount'] + $reportData[$i]['agent_total_remittance_refund'] + $reportData[$i]['agent_total_reversal_refund_stax'] + $reportData[$i]['agent_total_reversal_refund_fee'];
                        $reportData[$i]['agent_closing_balance'] = $reportData[$i]['agent_opening_balance'] + $addableAmount - $deductableAmount;
                        $isCurrentDate = true;

//			error_log('addableAmount:' .$addableAmount);
//			error_log('deductableAmount: ' .$deductableAmount);

                    }

                    /*                     * ** now adding different fetched amounts over here *** */
		     if(!isset($commissionData['total'])){
	$commissionData['total']=0;
}
if(!isset($revCommissionData['total'])){
	$revCommissionData['total']=0;
}
if(isset($commissionFromCommReport['total_agent_commission'])){
	$commissionData['total']=$commissionData['total']+$commissionFromCommReport['total_agent_commission'];
}
$reportData[$i]['agent_commission'] = $commissionData['total'];
                    $reportData[$i]['agent_commission_rev'] = $revCommissionData['total'];

                    if($isCurrentDate){
                    	$reportData[$i]['agent_closing_balance'] = $reportData[$i]['agent_closing_balance'] + $reportData[$i]['agent_commission'] - $reportData[$i]['agent_commission_rev'];
                    }
                error_log('agent_opening_balance: ' .$reportData[$i]['agent_opening_balance']);
		error_log('agent_closing_balance: ' .$reportData[$i]['agent_closing_balance']);
//		error_log('agent_commission:' .$reportData[$i]['agent_commission']);
//		error_log('agent_commission_rev: ' .$reportData[$i]['agent_commission_rev']);
		
                $i++;
                    
                    }
                }
      $retReportData = array_merge($retReportData, $reportData);
            }

            return $retReportData;
        } else
            return array();
    }

    public function exportAgentBalanceSheet($param, $agentId = 0) {
        $data = $this->getAgentBalanceSheet($param, $agentId);

        $retData = array();

        if (!empty($data)) {
            foreach ($data as $key => $data) {
                $retData[$key]['txn_datetime'] = $data['txn_datetime'];
                $retData[$key]['agent_name'] = $data['agent_name'];
                $retData[$key]['agent_code'] = $data['agent_code'];
                $retData[$key]['bank_name'] = $data['bank_name'];
                $retData[$key]['agent_opening_balance'] = Util::numberFormat($data['agent_opening_balance']);
                $retData[$key]['total_agent_funding_amount'] = Util::numberFormat($data['total_agent_funding_amount']);
                $retData[$key]['total_agent_pending_funding_amount'] = Util::numberFormat($data['total_agent_pending_funding_amount']);
                $retData[$key]['agent_total_agent_transactions'] = Util::numberFormat($data['agent_total_agent_transactions']);
                $retData[$key]['agent_total_remittance_refund'] = Util::numberFormat($data['agent_total_remittance_refund']);
                $retData[$key]['agent_total_remit_fee'] = Util::numberFormat($data['agent_total_remit_fee']);
                $retData[$key]['agent_total_remit_service_tax'] = Util::numberFormat($data['agent_total_remit_service_tax']);
                $retData[$key]['agent_total_reversal_refund_stax'] = Util::numberFormat($data['agent_total_reversal_refund_stax']);
                $retData[$key]['agent_total_reversal_refund_fee'] = Util::numberFormat($data['agent_total_reversal_refund_fee']);
                $retData[$key]['debits_to_pay_acc'] = Util::numberFormat($data['total_agent_debit_amount']);
                $retData[$key]['total_agent_debit_reversal_amount'] = Util::numberFormat($data['total_agent_debit_reversal_amount']);
		$retData[$key]['agent_commission'] = Util::numberFormat($data['agent_commission']);
		$retData[$key]['agent_commission_rev'] = Util::numberFormat($data['agent_commission_rev']);
                 $retData[$key]['agent_closing_balance'] = Util::numberFormat($data['agent_closing_balance']);
            }
        } 
        return $retData;
    }
    
    public function exportAgentVirtualBalanceSheet($param, $agentId = 0) { 
        $data = $this->getAgentVirtualBalanceSheet($param, $agentId); 
        if(!empty($data)){
            $numVBdata = 0;
            foreach ($data as $key => $dayavbdata) {
                foreach ($dayavbdata as $avbkey => $avbdata) { 
                    $retData[$numVBdata]['txn_datetime'] = $avbdata['txn_date'];
                    $retData[$numVBdata]['agent_name'] = $avbdata['agent_name'];
                    $retData[$numVBdata]['agent_code'] = $avbdata['agent_code'];
                    $retData[$numVBdata]['bank_name'] = $avbdata['bank_name'];
                    $retData[$numVBdata]['agent_opening_balance'] = Util::numberFormat($avbdata['agent_opening_balance']);
                    $retData[$numVBdata]['total_Authorize_fund'] = Util::numberFormat($avbdata['total_Authorize_fund']);
                    $retData[$numVBdata]['total_Unauthorize_fund'] = Util::numberFormat($avbdata['total_Unauthorize_fund']);
                    $retData[$numVBdata]['card_load'] = Util::numberFormat($avbdata['card_load']);
                    $retData[$numVBdata]['debit_payable'] = Util::numberFormat($avbdata['debit_payable']); 
                    
                    if((int)$avbdata['agent_closing_balance'] == 0){ 
                        $agent_closing_balance = $avbdata['agent_opening_balance'] 
                                + $avbdata['total_Authorize_fund'] 
                                - $avbdata['card_load'] ;
                    } else {
                        $agent_closing_balance = $avbdata['agent_closing_balance']; 
                    } 
                    $retData[$numVBdata]['agent_closing_balance'] = Util::numberFormat($agent_closing_balance); 
                   $numVBdata++;
                }
            }
        } 
        return $retData;
    }
    
    /* exportAgentBalanceSheet function will find data for Agent Balance Sheet for export data to csv. 
     * it will accept param array with query filters e.g.. duration
     */

    public function exportAgentBalanceSheetAgent($param, $agentId = 0) {
        $data = $this->getAgentBalanceSheet($param, $agentId);

        $retData = array();

        if (!empty($data)) {

            foreach ($data as $key => $data) {
                $retData[$key]['txn_datetime'] = $data['txn_datetime'];
                $retData[$key]['agent_opening_balance'] = Util::numberFormat($data['agent_opening_balance']);
                $retData[$key]['total_agent_funding_amount'] = Util::numberFormat($data['total_agent_funding_amount']);
                $retData[$key]['total_agent_pending_funding_amount'] = Util::numberFormat($data['total_agent_pending_funding_amount']);
                $retData[$key]['agent_total_agent_transactions'] = Util::numberFormat($data['agent_total_agent_transactions']);
                $retData[$key]['agent_total_remittance_refund'] = Util::numberFormat($data['agent_total_remittance_refund']);
                $retData[$key]['agent_total_remit_fee'] = Util::numberFormat($data['agent_total_remit_fee']);
                $retData[$key]['agent_total_remit_service_tax'] = Util::numberFormat($data['agent_total_remit_service_tax']);
                $retData[$key]['agent_total_reversal_refund_stax'] = Util::numberFormat($data['agent_total_reversal_refund_stax']);
                $retData[$key]['agent_total_reversal_refund_fee'] = Util::numberFormat($data['agent_total_reversal_refund_fee']);
                $retData[$key]['agent_closing_balance'] = Util::numberFormat($data['agent_closing_balance']);
		$retData[$key]['agent_commission'] = Util::numberFormat($data['agent_commission']);
					$retData[$key]['agent_commission_rev'] = Util::numberFormat($data['agent_commission_rev']);
            }
        }

        return $retData;
    }

    /* getAgentTransactions() will return the data array of agents transactions like load,reload,remittance, 
     * remittance refund, remittance fee and service tax date wise
     * As params , date duration will be expected.
     */

    public function getAgentTransactions($param) {

        $rptDuration = $param['duration'];
        $dates = Util::getDurationAllDates($rptDuration);
        $totalDates = count($dates);
        $objRemitter = new Remit_Boi_Remitter();
        $objRemitRequest = new Remit_Remittancerequest();
        $objCardLoads = new CardLoads();
        $retTxnData = array();

        if (!empty($dates)) {

            foreach ($dates as $queryDate) {

                $to = isset($queryDate['to']) ? $queryDate['to'] : '';
                $from = isset($queryDate['from']) ? $queryDate['from'] : '';
                $param = array(
                    'to' => $to,
                    'from' => $from,
                );

                $queryDateArr = explode(' ', $to);
                $queryDate = array('date' => $queryDateArr[0]);

                /*                 * ** getting agent load reloads *** */
                $select = $objCardLoads->sqlAgentLoadReload($param);
                $agentLoadReloads = $this->_db->fetchAll($select);
                if (!empty($agentLoadReloads))
                    $retTxnData = array_merge($retTxnData, $agentLoadReloads);


                /*                 * ** getting agent remitters registered for particular date *** */
                $remitters = $objRemitter->getRemittersOnDateBasis($queryDate);
                if (!empty($remitters))
                    $retTxnData = array_merge($retTxnData, $remitters);


                /*                 * ** getting agent remitters's fund transfer request for particular date **** */
                $remitRequests = $objRemitRequest->getRemitRequestOnDateBasis($queryDate);
                if (!empty($remitRequests))
                    $retTxnData = array_merge($retTxnData, $remitRequests);

                /*                 * ** getting agent remitters's refunds for particular date **** */
                $remitRefunds = $objRemitter->getRemitRefundsOnDateBasis($queryDate);
                if (!empty($remitRefunds))
                    $retTxnData = array_merge($retTxnData, $remitRefunds);
            } // for each loop
        } // date check if
        //krsort($retTxnData); //sorting for date
        return $retTxnData;
    }

public function exportAgentBalanceSheetForOps($param, $agentId = 0) {
    	$data = $this->getAgentBalanceSheet($param, $agentId);
    
    	$retData = array();
    
    	if (!empty($data)) {
    		foreach ($data as $key => $data) {
    			$retData[$key]['txn_datetime'] = $data['txn_datetime'];
    			$retData[$key]['agent_name'] = $data['agent_name'];
    			$retData[$key]['agent_code'] = $data['agent_code'];
    			$retData[$key]['bank_name'] = $data['bank_name'];
    			$retData[$key]['agent_opening_balance'] = Util::numberFormat($data['agent_opening_balance']);
    			$retData[$key]['total_agent_funding_amount'] = Util::numberFormat($data['total_agent_funding_amount']);
    			$retData[$key]['total_agent_pending_funding_amount'] = Util::numberFormat($data['total_agent_pending_funding_amount']);
    			$retData[$key]['agent_total_agent_transactions'] = Util::numberFormat($data['agent_total_agent_transactions']);
    			$retData[$key]['agent_total_remittance_refund'] = Util::numberFormat($data['agent_total_remittance_refund']);
    			$retData[$key]['agent_total_remit_fee'] = Util::numberFormat($data['agent_total_remit_fee']);
    			$retData[$key]['agent_total_remit_service_tax'] = Util::numberFormat($data['agent_total_remit_service_tax']);
    			$retData[$key]['agent_total_reversal_refund_stax'] = Util::numberFormat($data['agent_total_reversal_refund_stax']);
    			$retData[$key]['agent_total_reversal_refund_fee'] = Util::numberFormat($data['agent_total_reversal_refund_fee']);
    			$retData[$key]['debits_to_pay_acc'] = Util::numberFormat($data['total_agent_debit_amount']);
                        $retData[$key]['total_agent_debit_reversal_amount'] = Util::numberFormat($data['total_agent_debit_reversal_amount']);
    			$retData[$key]['agent_commission'] = Util::numberFormat($data['agent_commission']);
    			$retData[$key]['agent_commission_rev'] = Util::numberFormat($data['agent_commission_rev']);
    			$retData[$key]['agent_closing_balance'] = Util::numberFormat($data['agent_closing_balance']);
    		}
    	}
    
    	return $retData;
    }


    /* exportAgentTransactions() will find data for agents transactions like load,reload,remittance, 
     * remittance refund, remittance fee and service tax
     * it will accept param array with query filters e.g.. duration of report
     */

    public function exportAgentTransactions($param) {

        $data = $this->getAgentTransactions($param);
        $retData = array();
        $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");

        if (!empty($data)) {

            foreach ($data as $key => $data) {
                $retData[$key]['txn_date'] = $data['txn_date'];
                $retData[$key]['agent_code'] = $data['agent_code'];
                $retData[$key]['agent_name'] = $data['agent_name'];
                $retData[$key]['estab_city'] = $data['estab_city'];
                $retData[$key]['estab_pincode'] = $data['estab_pincode'];
                $retData[$key]['txn_type'] = $TXN_TYPE_LABELS[$data['txn_type']];
                $retData[$key]['amount'] = $data['amount'];
                $retData[$key]['crn'] = $data['crn'];
                $retData[$key]['mobile_number'] = $data['mobile_number'];
                $retData[$key]['ecs_product_code'] = $data['ecs_product_code'];
                $retData[$key]['txn_code'] = $data['txn_code'];
            }
        }
        return $retData;
    }

    /* getAgentWiseTransactions() will get agent transactions including load,reload,remittance,refund, all remit txn fees, service tax
     * As params , agent id , date duration will be expected.
     */

    public function getAgentWiseTransactions($param) {

        $rptDuration = $param['duration'];
        $agentId = $param['agent_id'];
        $dates = Util::getDurationAllDates($rptDuration);
        $totalDates = count($dates);
        $objRemitter = new Remit_Boi_Remitter();
        $objRemitRequest = new Remit_Remittancerequest();
        $objCardLoads = new CardLoads();
        $retTxnData = array();

        if (!empty($dates)) {

            foreach ($dates as $queryDate) {

                $to = isset($queryDate['to']) ? $queryDate['to'] : '';
                $from = isset($queryDate['from']) ? $queryDate['from'] : '';
                $loadReloadParam = array(
                    'to' => $to,
                    'from' => $from,
                    'agent_id' => $agentId
                );

                $queryDateArr = explode(' ', $to);
                $remitQueryData = array('date' => $queryDateArr[0], 'agent_id' => $agentId);

                /*                 * ****** getting agent wise load reloads ******* */
                $select = $objCardLoads->sqlAgentWiseLoads($loadReloadParam);
                $agentLoadReloads = $this->_db->fetchAll($select);
                $totalLoadReloads = count($agentLoadReloads);

                if ($totalLoadReloads >= 1) {
                    for ($i = 0; $i < $totalLoadReloads; $i++) {
                        $agentLoadReloads[$i]['txn_date'] = $agentLoadReloads[$i]['date_created'];
                    }
                    $retTxnData = array_merge($retTxnData, $agentLoadReloads);
                }


                /*                 * ****** getting agent's remitters registered for particular date ******* */
                $remitters = $objRemitter->getRemittersOnDateBasis($remitQueryData);
                if (!empty($remitters))
                    $retTxnData = array_merge($retTxnData, $remitters);


                /*                 * ** getting agent remitters's fund transfer request for particular date **** */
                $remitRequests = $objRemitRequest->getRemitRequestOnDateBasis($remitQueryData);
                if (!empty($remitRequests))
                    $retTxnData = array_merge($retTxnData, $remitRequests);

                /*                 * ** getting agent remitters's refunds for particular date **** */
                $remitRefunds = $objRemitter->getRemitRefundsOnDateBasis($remitQueryData);
                if (!empty($remitRefunds))
                    $retTxnData = array_merge($retTxnData, $remitRefunds);
            } // for each loop
        } // date check if
        //krsort($retTxnData); //sorting for date
        return $retTxnData;
    }

    /* exportAgentWiseTransactions function will find data for agent wise transaction report. 
     * it will accept param array with query filters e.g.. duration and agent id
     */

    public function exportAgentWiseTransactions($param) {

        $data = $this->getAgentWiseTransactions($param);
        $objAgent = new Agents();
        $agentInfo = $objAgent->findById($param['agent_id']);
        $retData = array();
        $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");

        if (!empty($data)) {

            foreach ($data as $key => $data) {
                $retData[$key]['txn_date'] = $data['txn_date'];
                $retData[$key]['name'] = $agentInfo['name'];
                $retData[$key]['agent_code'] = $agentInfo['agent_code'];
                $retData[$key]['estab_city'] = $agentInfo['estab_city'];
                $retData[$key]['estab_pincode'] = $agentInfo['estab_pincode'];
                $retData[$key]['txn_type'] = $TXN_TYPE_LABELS[$data['txn_type']];
                $retData[$key]['amount'] = $data['amount'];
                $retData[$key]['crn'] = $data['crn'];
                $retData[$key]['mobile_number'] = $data['mobile_number'];
                $retData[$key]['ecs_product_code'] = $data['ecs_product_code'];
                $retData[$key]['txn_code'] = $data['txn_code'];
            }
        }

        return $retData;
    }

      /* 
       * getAgentSummary() will get 
       * agent load,reload,remitters,remittance,reload including service tax and fee with thier counts
       * As params , agent id(optional), date duration will be expected.
       * 
       */
    
    public function getAgentSummary($param) {
                        
                        
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '0';
        $to = isset($param['to']) ? $param['to'] : '';
        $from = isset($param['from']) ? $param['from'] : '';    
                        
        //Find All Agents List
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_AGENTS . ' as a',array('a.agent_code', 'a.id as agent_id', 'concat(a.first_name," ",a.last_name) as agent_name', 'a.user_type'));
        $select->joinLeft(DbTable::TABLE_AGENT_DETAILS . ' as ad',"a.id=ad.agent_id AND ad.status='" . STATUS_ACTIVE . "'",array('ad.email', 'ad.mobile1', 'ad.estab_city', 'ad.estab_pincode')); 
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION . ' as obr',"obr.to_object_id = a.id AND obr.object_relation_type_id = 2", array());
        $select->joinLeft(DbTable::TABLE_AGENTS . ' as dis',"obr.from_object_id = dis.id",array('concat(dis.first_name," ",dis.last_name) AS distributor_name','dis.agent_code as distributor_code'));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION . ' as obr2',"obr2.to_object_id = dis.id AND obr2.object_relation_type_id= 1",array());
        $select->joinLeft(DbTable::TABLE_AGENTS . ' as sag',"obr2.from_object_id = sag.id",array('concat(sag.first_name," ",sag.last_name) AS super_agent_name' ,'sag.agent_code as super_agent_code'));
        $select->where('a.enroll_status=?', STATUS_APPROVED);
        $select->where('a.status IN ('.'"'.STATUS_UNBLOCKED.'","'.STATUS_BLOCKED.'","'.STATUS_LOCKED.'"'.')');
        $select->where("a.user_type in ('0','-3')");
        if ($agentId > 0) {
            $select->where('a.id=?', $agentId);
        } else{
            $objAU = new AgentUser();
            $agentList = $objAU->getAgentsCommaList($param['bank_unicode']);
            $select->where("a.id IN ('". $agentList."')");
        }
        $select->order('agent_name ASC');
        $agentData = $this->_db->fetchAll($select);
        $totalRecs = count($agentData);
        
        
        // Finding agent total load, 
        // reload,
        // card Load request(RAT/BOI/Kotak)
        // remitters(fee+stax), total remittance(remittance+fee+stax)
        //  total refunds and counts for all 
        
        $retSummaryData = array();
        $queryParam = array(
            'to' => $to,
            'from' => $from,
            'bank_unicode' => $param['bank_unicode']
        );
                        
        $objCardLoads = new CardLoads(); 
        $objPrd = new Products();
        
                
        for ($agentNum = 0; $agentNum < $totalRecs; $agentNum++) {
            $agent_id = $queryParam['agent_id'] = $agentData[$agentNum]['agent_id'];  
            $fromDateOnlyArr = explode(' ', $from);  
            $bankUnicodeArr = Util::bankUnicodesArray();
                
            $productExcludeArr = array(PRODUCT_CONST_BOI_REMIT) ;
            $productExclude = implode("','", $productExcludeArr);
            $objBAPC = new BindAgentProductCommission();  
            $agentBindDetails = $objBAPC->getAgentBindingDur($agent_id, $from, $to,$productExclude);
                        
            // print_r($agentBindDetails);
             
            if(count($agentBindDetails) > 0){
                foreach ($agentBindDetails as $agentbind) {
                    $bank_unicode = isset($agentbind['bank_unicode']) ? $agentbind['bank_unicode'] : '';
                    $reportData[$i]['bank_name'] = isset($agentbind['bank_name']) ? $agentbind['bank_name'] : ''; 
                    $product_name = isset($agentbind['product_name']) ? $agentbind['product_name'] : ''; 
                        
                    $param = array(
                        'agent_id' => $agent_id,
                        'to' => $to,
                        'from' => $from,
                        'bank_unicode' => $bank_unicode
                    );  
                    //Getting Corp Load request (Rat/Boi/Kotak)
                    switch($bank_unicode) {
                        case $bankUnicodeArr['3']://Kotak
                            $corpKtkCardLoad = new Corp_Kotak_Cardload();
                            $totalAgentLoad = $corpKtkCardLoad->getAllLoad($param);
                            
                            //getting agent's remitters fee and count total for particular date  
                            $kotakRemitModel = new Remit_Kotak_Remitter();
                            $remitters = $kotakRemitModel->getAgentTotalRemitterRegnFeeAllSTax($param);
                            
                            $kotakRemitModel = new Remit_Kotak_Remittancerequest();
                            $remitRequests = $kotakRemitModel->getAgentAllRemittanceFeeSTax($param);
                            
                            $kotakRemitModel = new Remit_Kotak_Remittancerequest();
                            $remitRefundArr = $kotakRemitModel->getAgentAllRemittanceRefundSTax($param);
                            break;
                        case $bankUnicodeArr['2']://RAT 
                            $corpRatCardLoad = new Corp_Ratnakar_Cardload();
                            $totalAgentLoad = $corpRatCardLoad->getAgentAllLoad($param); 
                            
                            //getting agent's remitters fee and count total for particular date  
                            $ratnakarRemitModel = new Remit_Ratnakar_Remitter();
                            $remitters = $ratnakarRemitModel->getAgentTotalRemitterRegnFeeAllSTax($param);
                            
                            $ratnakarRemitModel = new Remit_Ratnakar_Remittancerequest();
                            $remitRequests = $ratnakarRemitModel->getAgentAllRemittanceFeeSTax($param);
                            
                            $ratnakarRemitModel = new Remit_Ratnakar_Remittancerequest();
                            $remitRefundArr = $ratnakarRemitModel->getAgentAllRemittanceRefundSTax($param);
                            break;
                        case $bankUnicodeArr['1']://BOI
                            $corpRatCardLoad = new Corp_Boi_Cardload();
                            $totalAgentLoad = $corpRatCardLoad->getAllLoad($param);
                            break;
                        case $bankUnicodeArr['0']://AXIS
                            $loadParam = $queryParam;
                            $totalAgentLoad = $objCardLoads->getAgentLoadsReloadsCountAll($loadParam); 
                            break;
                    }
                        
                    foreach($totalAgentLoad as $AgentLoaddata){
                        $txn_date = $AgentLoaddata['txn_date']; 

                        $retSummaryData[$txn_date][$agent_id]['total_agent_loads'][] = $AgentLoaddata['total_agent_loads'];
                        $retSummaryData[$txn_date][$agent_id]['total_agent_loads_count'][] = $AgentLoaddata['total_agent_loads_count'];
                        $retSummaryData[$txn_date][$agent_id]['product_name'][] = $product_name;
                    }
                
                    
                    //getting agent's remitters fee and count total for particular date   
                    foreach($remitters as $Remittdata){
                        $txn_date = $Remittdata['txn_date']; 
                        $retSummaryData[$txn_date][$agent_id]['agent_total_remitters_fee'] = $Remittdata['agent_total_remitter_regn_fee'] + $Remittdata['agent_total_remitter_regn_stax'];
                        $retSummaryData[$txn_date][$agent_id]['agent_total_remitters_count'] = $Remittdata['count_agent_total_remitters'];
                         $retSummaryData[$txn_date][$agent_id]['product_name'][] = $product_name;
                    }
                    
                        
                    //getting agent remittances total for particular date   
                    foreach($remitRequests as $RemitReqdata){
                        $txn_date = $RemitReqdata['txn_date']; 
                        $retSummaryData[$txn_date][$agent_id]['agent_total_remittance'] = $RemitReqdata['agent_total_remittance'] + $RemitReqdata['agent_total_remittance_fee'] + $RemitReqdata['agent_total_remittance_stax'];
                        $retSummaryData[$txn_date][$agent_id]['agent_total_remittance_count'] = $RemitReqdata['agent_total_remittance_count'];
                        $retSummaryData[$txn_date][$agent_id]['product_name'][] = $product_name;
                    }

                    // getting agent remitters's refunds for particular date  
                    foreach($remitRefundArr as $RemitRefdata){
                        $txn_date = $RemitRefdata['txn_date'] ; 

                        $retSummaryData[$txn_date][$agent_id]['agent_total_remittance_refund'] = $RemitRefdata['agent_total_remittance_refund'] + $RemitRefdata['agent_total_reversal_refund_fee'] + $RemitRefdata['agent_total_reversal_refund_stax'];
                        $retSummaryData[$txn_date][$agent_id]['agent_total_remittance_refund_count'] = $RemitRefdata['agent_total_remittance_refund_count'];
                        $retSummaryData[$txn_date][$agent_id]['product_name'][] = $product_name;
                    }
                }
            }
        }
                        
        $ad_idArr = array(); 
        foreach ($agentData as $key => $ad_id){ 
            $ad_idArr[$key] = $ad_id['agent_id']; 
        } 
      
        $agentSummaryArr = array();
        $list = 0;
        foreach ($retSummaryData as $key => $agentSummaryData) {
            foreach($agentSummaryData as $agKey => $agData){
                $agentSummaryArr[$list]['txn_date'] = $key;
                $agentSummaryArr[$list]['agent_id'] = $agKey;

                $Agent_key = array_search($agKey, $ad_idArr);  
                $agentSummaryArr[$list]['agent_code'] = $agentData[$Agent_key]['agent_code'];
                $agentSummaryArr[$list]['agent_name'] = $agentData[$Agent_key]['agent_name']; 
                $agentSummaryArr[$list]['estab_pincode'] = $agentData[$Agent_key]['estab_pincode'];
                $agentSummaryArr[$list]['mobile'] = $agentData[$Agent_key]['mobile1'];
                $agentSummaryArr[$list]['email'] = $agentData[$Agent_key]['email'];
                $agentSummaryArr[$list]['estab_city'] = $agentData[$Agent_key]['estab_city'];
                $agentSummaryArr[$list]['distributor_code'] = $agentData[$Agent_key]['distributor_code'];
                $agentSummaryArr[$list]['distributor_name'] = $agentData[$Agent_key]['distributor_name'];
                $agentSummaryArr[$list]['super_agent_code'] = $agentData[$Agent_key]['super_agent_code'];
                $agentSummaryArr[$list]['super_agent_name'] = $agentData[$Agent_key]['super_agent_name']; 

                $agentSummaryArr[$list]['agent_total_remitters_fee'] =  $agData['agent_total_remitters_fee'] ;
                $agentSummaryArr[$list]['agent_total_remitters_count'] = $agData['agent_total_remitters_count'] ;
                
                $agentSummaryArr[$list]['agent_total_remittance'] =  $agData['agent_total_remittance'] ;
                $agentSummaryArr[$list]['agent_total_remittance_count'] = $agData['agent_total_remittance_count'] ;
                
                $agentSummaryArr[$list]['agent_total_remittance_refund'] =  $agData['agent_total_remittance_refund'] ;
                $agentSummaryArr[$list]['agent_total_remittance_refund_count'] = $agData['agent_total_remittance_refund_count'] ;
                        
                $agentSummaryArr[$list]['total_agent_loads'] = array_sum($agData['total_agent_loads']) ;
                $agentSummaryArr[$list]['total_agent_loads_count'] = array_sum($agData['total_agent_loads_count']) ; 
                        
                $allPrdArr = array_unique($agData['product_name']);
                $agentSummaryArr[$list]['product_name'] =  implode(",", $allPrdArr); 
            }
            $list++;
        } 
        return $agentSummaryArr; 
    }    
    
			
    /* exportAgentSummary() will get agent load,reload,remitters,remittance,reload including service tax and fee with thier counts 
     *  As params , agent id(optional) , date duration will be expected.
     */

    public function exportAgentSummary($param) {

        $retData = Array();
        $summaryData = $this->getAgentSummary($param);

        if (!empty($summaryData)) {

            foreach ($summaryData as $key => $data) {

                $retData[$key]['txn_date'] = $data['txn_date'];
                $retData[$key]['super_agent_code'] = $data['super_agent_code'];
                $retData[$key]['super_agent_name'] = $data['super_agent_name'];
                $retData[$key]['distributor_code'] = $data['distributor_code'];
                $retData[$key]['distributor_name'] = $data['distributor_name'];
                $retData[$key]['agent_name'] = $data['agent_name'];
                $retData[$key]['agent_code'] = $data['agent_code'];
                $retData[$key]['email'] = $data['email'];
                $retData[$key]['mobile1'] = $data['mobile'];
                $retData[$key]['estab_city'] = $data['estab_city'];
                $retData[$key]['estab_pincode'] = $data['estab_pincode'];
                $retData[$key]['total_agent_loads_count'] = $data['total_agent_loads_count'];
                $retData[$key]['total_agent_loads'] = $data['total_agent_loads'];
             //   $retData[$key]['total_agent_reloads_count'] = $data['total_agent_reloads_count'];
              //  $retData[$key]['total_agent_reloads'] = $data['total_agent_reloads'];
                $retData[$key]['agent_total_remitters_count'] = $data['agent_total_remitters_count'];
                $retData[$key]['agent_total_remitters_fee'] = $data['agent_total_remitters_fee'];
                $retData[$key]['agent_total_remittance_count'] = $data['agent_total_remittance_count'];
                $retData[$key]['agent_total_remittance'] = $data['agent_total_remittance'];
                $retData[$key]['agent_total_remittance_refund_count'] = $data['agent_total_remittance_refund_count'];
                $retData[$key]['agent_total_remittance_refund'] = $data['agent_total_remittance_refund'];
                $retData[$key]['product_name'] = $data['product_name'];
            }
        }

        return $retData;
    }

    /* exportAgentSummaryFromAgent() will get agent load,reload,remitters,remittance,reload including service tax and fee with thier counts 
     *  As params , agent id(optional) , date duration will be expected.
     */

    public function exportAgentSummaryFromAgent($param) {

        $retData = Array();
        $summaryData = $this->getAgentSummary($param);

        if (!empty($summaryData)) {

            foreach ($summaryData as $key => $data) {

                $retData[$key]['txn_date'] = $data['txn_date'];
                $retData[$key]['agent_name'] = $data['agent_name'];
                $retData[$key]['agent_code'] = $data['agent_code'];
                $retData[$key]['total_agent_loads_count'] = $data['total_agent_loads_count'];
                $retData[$key]['total_agent_loads'] = $data['total_agent_loads'];
                $retData[$key]['total_agent_reloads_count'] = $data['total_agent_reloads_count'];
                $retData[$key]['total_agent_reloads'] = $data['total_agent_reloads'];
                $retData[$key]['agent_total_remitters_count'] = $data['agent_total_remitters_count'];
                $retData[$key]['agent_total_remitters_fee'] = $data['agent_total_remitters_fee'];
                $retData[$key]['agent_total_remittance_count'] = $data['agent_total_remittance_count'];
                $retData[$key]['agent_total_remittance'] = $data['agent_total_remittance'];
                $retData[$key]['agent_total_remittance_refund_count'] = $data['agent_total_remittance_refund_count'];
                $retData[$key]['agent_total_remittance_refund'] = $data['agent_total_remittance_refund'];
            }
        }

        return $retData;
    }

    /* getAgentCommissionSummary() will get agent commission including load/reload transaction amount and its commission amount, 
     * all remit actions amount total and its commission amount
     * As params :- agent id(optinal) , date duration will be expected.
     */

    public function getAgentCommissionSummary($param) {

        if (!empty($param)) {
            $agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
            $dates = Util::getDurationAllDates($param['duration']);
            $objCommission = new CommissionReport();
            $retSummaryData = array();
            $reportData = array();
            $j = 0;
            $str = "'" . STATUS_UNBLOCKED . "', '" . STATUS_BLOCKED . "', '" . STATUS_LOCKED . "'";

            if (!empty($dates)) {

                foreach ($dates as $queryDate) {

                    /*                     * ** getting agents details  *** */
                    $select = $this->_db->select();
                    $select->from(DbTable::TABLE_AGENTS . ' as a', array('a.agent_code', 'a.id as agent_id', 'concat(a.first_name," ",a.last_name) as agent_name'));
                    $select->joinLeft(DbTable::TABLE_AGENT_DETAILS . ' as ad', "a.id=ad.agent_id AND ad.status='" . STATUS_ACTIVE . "'", array('ad.estab_city', 'ad.estab_pincode'));
                        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION_TYPES . ' as ort',"ort.label = '".DISTRIBUTOR_TO_AGENT."'",array('id as dta'));
                $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION . ' as obr',"obr.to_object_id = a.id AND obr.object_relation_type_id = 2");
                $select->joinLeft(DbTable::TABLE_AGENTS . ' as dis',"obr.from_object_id = dis.id",array('concat(dis.first_name," ",dis.last_name) AS distributor_name', 'dis.agent_code as distributor_code'));
//                $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION_TYPES . ' as ort2',"ort2.label = '".SUPER_TO_DISTRIBUTOR."'",array('id as std'));
                $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION . ' as obr2',"obr2.to_object_id = dis.id AND obr2.object_relation_type_id= 1");
                $select->joinLeft(DbTable::TABLE_AGENTS . ' as sag',"obr2.from_object_id = sag.id",array('concat(sag.first_name," ",sag.last_name) AS super_agent_name' ,'sag.agent_code as super_agent_code'));
                $select->where('a.enroll_status=?', STATUS_APPROVED);
                $select->where('a.status IN (' . $str . ')');
//                $select->where("a.user_type in ('0','-3')");

                    if ($agentId > 0) {
                        $select->where('a.id=?', $agentId);
                    }
                    else{
                        $objAU = new AgentUser();
                        $agentList = $objAU->getAgentsCommaList($param['bank_unicode']);
                        $select->where("a.id IN ('". $agentList."')");
                    }
                    $select->order('agent_name ASC');
                    $agentData = $this->_db->fetchAll($select);
                    $totalRecs = count($agentData);
                    /*                     * ** getting agents details over here *** */


                    // getting to and from date
                    $queryParam['dateTo'] = isset($queryDate['to']) ? $queryDate['to'] : '';
                    $queryParam['dateFrom'] = isset($queryDate['from']) ? $queryDate['from'] : '';



                    /*                     * ** Finding agent total load, reload, remit all actions transaction amount and commission total *** */

                    for ($i = 0; $i < $totalRecs; $i++) {

                        // getting agent id on queryParam array for query
                        $queryParam['agentId'] = $agentData[$i]['agent_id'];
                        $queryParam['bank_unicode'] = $param['bank_unicode'];

                        /*                         * ** getting agent loads/reloads transaction and commission total *** */
                        $loadTxnTypesParam = array(
                            '0' => TXNTYPE_FIRST_LOAD,
                            '1' => TXNTYPE_CARD_RELOAD
                        );
                        $respLoad = $objCommission->getAgentCommission($queryParam, $loadTxnTypesParam);

                        $agentLoadReload = array(
                            'total_agent_load_reload_amount' => $respLoad['total_agent_transaction_amount'],
                            'total_agent_load_reload_comm' => $respLoad['total_agent_commission'],
                            'plan_commission_name' => $respLoad['plan_commission_name'],
                            'transaction_fee' => $respLoad['transaction_fee']
                        );
                        if (!empty($agentLoadReload)) {
                            $retSummaryData = array_merge($retSummaryData, $agentLoadReload);
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
                        if (!empty($agentRemit)) {
                            $retSummaryData = array_merge($retSummaryData, $agentRemit);
                        }
                        /*                         * ** getting agent remit all actions total like (remitter registration, remittance over here *** */


                        // consolidate all above info in single array to return
                        $dateTo = explode(' ', $queryParam['dateTo']);
                        $reportData[$j] = $retSummaryData;
                        $reportData[$j]['txn_date'] = $dateTo['0'];
                        $reportData[$j]['super_agent_code'] = $agentData[$i]['super_agent_code'];
                        $reportData[$j]['super_agent_name'] = $agentData[$i]['super_agent_name'];
                        $reportData[$j]['distributor_code'] = $agentData[$i]['distributor_code'];
                        $reportData[$j]['distributor_name'] = $agentData[$i]['distributor_name'];
                        $reportData[$j]['agent_name'] = $agentData[$i]['agent_name'];
                        $reportData[$j]['agent_code'] = $agentData[$i]['agent_code'];
                        $reportData[$j]['estab_city'] = $agentData[$i]['estab_city'];
                        $reportData[$j]['estab_pincode'] = $agentData[$i]['estab_pincode'];
                        $reportData[$j]['agent_id'] = $agentData[$i]['agent_id'];
                        $j++;
                    } // for each loop
                }
            } // date check if
            //Util::debug($reportData);
            return $reportData;
        } else
            return array();
    }

    /* getAgentCommissionSummary() will get agent commission including load/reload transaction amount and its commission amount, 
     * all remit actions amount total and its commission amount for generating csv file.
     * As params :- agent id(optinal) , date duration will be expected.
     */

    public function exportAgentCommissionSummary($param) {

        $summaryData = $this->getAgentCommissionSummary($param);
        $retData = array();

        if (!empty($summaryData)) {

            foreach ($summaryData as $key => $data) {

                $retData[$key]['txn_date'] = $data['txn_date'];
                $retData[$key]['super_agent_code'] = $data['super_agent_code'];
                $retData[$key]['super_agent_name'] = $data['super_agent_name'];
                $retData[$key]['distributor_code'] = $data['distributor_code'];
                $retData[$key]['distributor_name'] = $data['distributor_name'];
                $retData[$key]['agent_name'] = $data['agent_name'];
                $retData[$key]['agent_code'] = $data['agent_code'];
                $retData[$key]['estab_city'] = $data['estab_city'];
                $retData[$key]['estab_pincode'] = $data['estab_pincode'];
                $retData[$key]['total_agent_load_reload_amount'] = ($data['total_agent_load_reload_amount'] > 0) ? $data['total_agent_load_reload_amount'] : 0;
                $retData[$key]['total_agent_load_reload_comm'] = ($data['total_agent_load_reload_comm'] > 0) ? $data['total_agent_load_reload_comm'] : 0;
                $retData[$key]['total_agent_remit_amount'] = ($data['total_agent_remit_amount'] > 0) ? $data['total_agent_remit_amount'] : 0;
                $retData[$key]['total_agent_remit_comm'] = ($data['total_agent_remit_comm'] > 0) ? $data['total_agent_remit_comm'] : 0;
                $retData[$key]['plan_commission_name'] = $data['plan_commission_name'];
                $retData[$key]['transaction_fee'] = $data['transaction_fee'];
                ;
            }
        }

        return $retData;
    }

    public function getPendingAgentFundRequests($param) {
        $objFundReq = new AgentFunding();
        return $objFundReq->pendingAgentFundRequests($param);
    }

    public function exportPendingAgentFundRequests($param) {

        $summaryData = $this->getPendingAgentFundRequests($param);
        $retData = array();

        if (!empty($summaryData)) {

            foreach ($summaryData as $key => $data) {

                $retData[$key]['agent_name'] = $data['agent_name'];
                $retData[$key]['agent_code'] = $data['agent_code'];
                $retData[$key]['bank_name'] = $data['bank_name'];
                $retData[$key]['datetime_request'] = $data['datetime_request'];
                $retData[$key]['fund_name'] = $data['fund_name'];
                $retData[$key]['amt'] = $data['amt'];
                $retData[$key]['comments'] = $data['comments'];
            }
        }

        return $retData;
    }

    public function getUserLogin($param) {
        $user = $param['user_type'];
        $from = $param['from'];
        $to = $param['to'];
        if ($user == DbTable::TABLE_AGENTS) {
            $table = DbTable::TABLE_AGENTS;
            $fetch = array('u.agent_code as user_code', 'u.id',
                'concat(u.first_name," ",u.last_name) as user_name',
                'u.is_logged as login_status', 'u.status as user_status',
                'u.last_password_update as pwd_change_date', 'u.num_login_attempts');
            $id = 'log.agent_id';
            $portal = MODULE_AGENT;
        } else if ($user == DbTable::TABLE_OPERATION_USERS) {
            $table = DbTable::TABLE_OPERATION_USERS;
            $fetch = array('u.id', 'concat(u.firstname," ",u.lastname) as user_name'
                , 'u.is_logged as login_status',
                'u.status as user_status', 'u.last_password_update as pwd_change_date',
                'u.num_login_attempts');
            $id = 'log.ops_id';
            $portal = MODULE_OPERATION;
        }


        $select = $this->_db->select();
        $select->from($table . ' as u', $fetch);
        $select->joinRight(DbTable::TABLE_LOG_LOGIN . ' as log', "u.id = $id", array('log.datetime_login_step1 as login1_datetime', 'log.datetime_login_step2 as login2_datetime', 'log.datetime_logout', 'log.ip as system_ip',
            'log.comment_username', 'log.comment_password', 'log.comment_auth', 'log.username as logusername', 'log.portal'));
        $select->where("log.date_updated BETWEEN '$from' AND '$to'");
        $select->where("log.portal=?", $portal);
        $select->order('user_name ASC');
        $select->order('log.date_updated ASC');
        $agentData = $this->_db->fetchAll($select);
        return $agentData;
    }

    /* getLoginSummary() will fetch and filter data for login logs for ops/agent
     * as param: duration and user type (ops/agent)
     */

    public function getLoginSummary($param) {

        $user = $param['user_type'];
        $from = $param['from'];
        $to = $param['to'];
        $retLoginLog = array();

        if ($user == DbTable::TABLE_AGENTS) {
            $table = DbTable::TABLE_AGENTS;
            $portal = MODULE_AGENT;

            $select = $this->_db->select();
            $select->from($table . ' as a', array('a.agent_code as user_code', 'concat(a.first_name," ",a.last_name) as user_name', 'a.id as user_id','a.status as user_status'));
            $select->join(DbTable::TABLE_AGENT_DETAILS . ' as ad', "a.id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", array('ad.mobile1 as mobile', 'ad.email as email'));
            $select->joinRight(DbTable::TABLE_LOG_LOGIN . ' as log', "a.id = log.agent_id", array('log.datetime_login_step1 as login1_datetime', 'log.datetime_login_step2 as login2_datetime', 'log.datetime_logout',
                'log.comment_username', 'log.comment_password', 'log.comment_auth', 'log.date_updated'));
        } else if ($user == DbTable::TABLE_OPERATION_USERS) {
            $table = DbTable::TABLE_OPERATION_USERS;
            $portal = MODULE_OPERATION;

            $select = $this->_db->select();
            $select->from($table . ' as opr', array('concat(opr.firstname," ",opr.lastname) as user_name', 'opr.email as email', 'opr.mobile1 as mobile', 'opr.id as user_id','opr.status as user_status'));
            $select->joinRight(DbTable::TABLE_LOG_LOGIN . ' as log', "opr.id = log.ops_id", array('log.datetime_login_step1 as login1_datetime', 'log.datetime_login_step2 as login2_datetime', 'log.datetime_logout',
                'log.comment_username', 'log.comment_password', 'log.comment_auth', 'log.date_updated'));
        }

        $select->where("log.date_updated BETWEEN '$from' AND '$to'");
        $select->where("log.portal=?", $portal);
        $select->order('user_name ASC');
        $select->order('log.date_updated ASC');
       
        $data = $this->_db->fetchAll($select);

        if (empty($data)) {
            return $retLoginLog;
        } else {
            $i = 0;
            // preparing and filter data for login summary report
            foreach ($data as $key => $value) {

                if ($value['user_id'] != '' && $value['user_id'] > 0) {
                    $userIdToSearchFor = $value['user_id'];
                    $countLoginLog = count($retLoginLog);
                    $valueDateArr = explode(" ", $value['date_updated']);
                    $dateToSearchFor = $valueDateArr[0];

                    // check if pre existed the user id and with particular date
                    $userIdExistsInRetArr = array_filter($retLoginLog, function($x) use ($userIdToSearchFor) {
                                return $x['user_id'] == $userIdToSearchFor;
                            });


                    $dateExistsInRetArr = array_filter($userIdExistsInRetArr, function($y) use ($dateToSearchFor) {
                                return $y['date'] == $dateToSearchFor;
                            });




                    /*                     * * if not already in new array of report data ** */
                    if (empty($userIdExistsInRetArr) || empty($dateExistsInRetArr)) {
                        $retLoginLog[$countLoginLog] = array('user_id' => $value['user_id'],
                            'user_name' => $value['user_name'],
                            'mobile' => $value['mobile'],
                            'email' => $value['email'],
                            'date' => $dateToSearchFor,
                            'step1_failed_count' => 0,
                            'step2_failed_count' => 0,
                            'success_login_count' => 0,
                            'user_status' => $value['user_status'],
                        );
                        if ($portal == MODULE_AGENT)
                            $retLoginLog[$countLoginLog] = array_merge(array('user_code' => $value['user_code']), $retLoginLog[$countLoginLog]);
                    }


                    /*                     * * if not already in new array of report data over here ** */

                    // indentify the current key of array to manipulate the columns values
                    if (!empty($dateExistsInRetArr)) {

                        foreach ($dateExistsInRetArr as $retLoginLogKey => $dateValue) {
                            $recordKey = $retLoginLogKey;
                        }
                    } else {
                        $recordKey = $countLoginLog;
                    }


                    if ($recordKey >= 0) {

                        /*                         * * calculating the login failure /successs/ logout count ** */
                        if ($value['comment_username'] != STATUS_FAILURE && $value['comment_username'] != '') {

                            if ($value['comment_password'] == STATUS_FAILURE) {
                                $retLoginLog[$recordKey]['step1_failed_count'] = $retLoginLog[$recordKey]['step1_failed_count'] + 1;
                            } else if ($value['comment_password'] == STATUS_SUCCESS && $value['comment_auth'] == STATUS_FAILURE) {
                                $retLoginLog[$recordKey]['step2_failed_count'] = $retLoginLog[$recordKey]['step2_failed_count'] + 1;
                            } else if ($value['comment_password'] == STATUS_SUCCESS && $value['comment_auth'] != STATUS_FAILURE && $value['comment_auth'] != STATUS_NA) {
                                $retLoginLog[$recordKey]['success_login_count'] = $retLoginLog[$recordKey]['success_login_count'] + 1;
                            }
                        }

                        /*                         * * calculating the login failure /successs/ logout count over here ** */
                    }
                }
            }
        }

        // sorting array on date basis
        if (!empty($retLoginLog)) {

            function cmp($a, $b) {
                return $a["date"] < $b["date"];
            }

            usort($retLoginLog, "cmp");
        }

        return $retLoginLog;
    }

    /* exportLoginSummary() will fetch and filter data for login logs for ops/agent
     * as param: duration and user type (ops/agent)
     */

    public function exportLoginSummary($param) {

        $retLoginLog = array();
        $userType = isset($param['user_type']) ? $param['user_type'] : '';
        $retLoginLog = $this->getLoginSummary($param);
        $retData = array();

        if (!empty($retLoginLog)) {
            if ($userType == DbTable::TABLE_AGENTS) {

                foreach ($retLoginLog as $key => $data) {

                    $retData[$key]['user_code'] = $data['user_code'];
                    $retData[$key]['user_name'] = $data['user_name'];
                    $retData[$key]['mobile'] = $data['mobile'];
                    $retData[$key]['email'] = $data['email'];
                    $retData[$key]['date'] = $data['date'];
                    $retData[$key]['step1_failed_count'] = $data['step1_failed_count'];
                    $retData[$key]['step2_failed_count'] = $data['step2_failed_count'];
                    $retData[$key]['success_login_count'] = $data['success_login_count'];
                    $retData[$key]['user_status'] = $data['user_status'];
                }
            } else if ($userType == DbTable::TABLE_OPERATION_USERS) {

                foreach ($retLoginLog as $key => $data) {

                    $retData[$key]['user_name'] = $data['user_name'];
                    $retData[$key]['mobile'] = $data['mobile'];
                    $retData[$key]['email'] = $data['email'];
                    $retData[$key]['date'] = $data['date'];
                    $retData[$key]['step1_failed_count'] = $data['step1_failed_count'];
                    $retData[$key]['step2_failed_count'] = $data['step2_failed_count'];
                    $retData[$key]['success_login_count'] = $data['success_login_count'];
                    $retData[$key]['user_status'] = $data['user_status'];
                }
            }
        }

        return $retData;
    }

    public function getWalletTxn($param) 
    {  
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['3'];
        }
       
        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
              
                $objCardload = new Corp_Kotak_Cardload();
                $detailsArr = $objCardload->getWalletTxn($param);
                break;
            case $bankUnicodeArr['2']:
                $objCardload = new Corp_Ratnakar_Cardload();
                $detailsArr = $objCardload->getWalletTxn($param);
                break;
            case $bankUnicodeArr['1']:
                $objCardload = new Corp_Boi_Cardload();
                $detailsArr = $objCardload->getWalletTxn($param);
                break;
        }
        return $detailsArr;
        
    }
    
    public function exportgetWalletTxn($param) 
    {  
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['3'];
        }
       
        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
              
                $objCardload = new Corp_Kotak_Cardload();
                $detailsArr = $objCardload->getWalletTxn($param);
                break;
            case $bankUnicodeArr['2']:
                $objCardload = new Corp_Ratnakar_Cardload();
                $detailsArr = $objCardload->exportgetWalletTxnAgent($param);
                break;
            case $bankUnicodeArr['1']:
                $objCardload = new Corp_Boi_Cardload();
                $detailsArr = $objCardload->getWalletTxn($param);
                break;
        }
        return $detailsArr;
        
    }
    
    
    
    public function getCardholderWalletTxn($param,$limit = 5) 
    {
        $productId = isset($param['product_id']) ? $param['product_id'] : '';
        $purseModel = new MasterPurse();
        
        $select = $this->select();
        $select->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST . " as load");
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as holder", "load.cardholder_id = holder.id",array('card_pack_id'));
        $select->joinLeft(DbTable::TABLE_PRODUCTS . " as product", "load.product_id = product.id",array('ecs_product_code as product_code', 'name as product_name', 'bank_id'));
        $select->joinLeft(DbTable::TABLE_BANK . " as bank", "product.bank_id = bank.id",array('name as bank_name'));
        if ($productId != '') {
            $select->where("load.product_id = ?",$productId);
        }
        $select->where("load.status = ?", STATUS_LOADED);
        $select->where("holder.rat_customer_id = ?", $param['rat_customer_id']);
        $select->where("holder.product_id = ?", $param['product_id']);
        $select->order("load.date_load");
        $select->limit($limit);
        $rsLoad = $this->_db->fetchAll($select);
        $cntLoad = count($rsLoad);
        for($i = 0; $i < $cntLoad; $i++)
        {
            $arrReport[$i]['txn_date'] = $rsLoad[$i]['date_load'];
            $arrReport[$i]['card_number'] = Util::maskCard($rsLoad[$i]['card_number'], 4);
            $arrReport[$i]['card_pack_id'] = $rsLoad[$i]['card_pack_id'];
            $arrReport[$i]['medi_assist_id'] = $rsLoad[$i]['medi_assist_id'];
            $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[$rsLoad[$i]['txn_type']];
            $arrReport[$i]['status'] = $STATUS[$rsLoad[$i]['status']];
            $arrReport[$i]['wallet_hr_dr'] = ($rsLoad[$i]['txn_type'] == TXNTYPE_RAT_CORP_CORPORATE_LOAD) ? $rsLoad[$i]['amount_cutoff'] : 0;
            $arrReport[$i]['wallet_hr_cr'] = ($rsLoad[$i]['txn_type'] == TXNTYPE_RAT_CORP_CORPORATE_LOAD) ? $rsLoad[$i]['amount'] : 0;
            $arrReport[$i]['wallet_ins_dr'] = ($rsLoad[$i]['txn_type'] == TXNTYPE_RAT_CORP_MEDIASSIST_LOAD) ? $rsLoad[$i]['amount_cutoff'] : 0;
            $arrReport[$i]['wallet_ins_cr'] = ($rsLoad[$i]['txn_type'] == TXNTYPE_RAT_CORP_MEDIASSIST_LOAD) ? $rsLoad[$i]['amount'] : 0;
            $arrReport[$i]['txn_no'] = $rsLoad[$i]['txn_no'];
            $arrReport[$i]['narration'] = $rsLoad[$i]['narration'];
            $arrReport[$i]['mode'] = $rsLoad[$i]['mode'];
        }
       
        $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CORP);
        $purseCodeHr = $product->purse->code->corporatehr; 
        $purseCodeIns = $product->purse->code->corporateins; 
        $purseCodeGen = $product->purse->code->genwallet; 
        $purseDetailsHr = $purseModel->getPurseIdByPurseCode($purseCodeHr);
        $purseDetailsIns = $purseModel->getPurseIdByPurseCode($purseCodeIns);
        $purseDetailsGen = $purseModel->getPurseIdByPurseCode($purseCodeGen);
        
        $select = $this->select();
        $select->from(DbTable::TABLE_CARD_AUTH_REQUEST . " as auth");
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as holder", "auth.cardholder_id = holder.id",array('card_pack_id', 'medi_assist_id'));
        $select->joinLeft(DbTable::TABLE_PRODUCTS . " as product", "auth.product_id = product.id",array('ecs_product_code as product_code', 'name as product_name', 'bank_id'));
        $select->joinLeft(DbTable::TABLE_BANK . " as bank", "product.bank_id = bank.id",array('name as bank_name'));
        if ($productId != '') {
            $select->where("auth.product_id = '" . $productId . "'");
        }
        $select->where("auth.status =?",STATUS_COMPLETED);
        $select->where("holder.rat_customer_id = ?", $param['rat_customer_id']);
        $select->where("holder.product_id = ?", $param['product_id']);
        $select->order("auth.date_created");        
        $select->limit($limit);
        

        $rsAuth = $this->_db->fetchAll($select);
        
        $cntAuth = count($rsAuth);
        $j = $i;
        for($i = 0; $i < $cntAuth; $i++)
        {
            $arrReport[$j]['txn_date'] = $rsAuth[$i]['date_created'];
            $arrReport[$j]['txn_type'] = $TXN_TYPE_LABELS[$rsLoad[$i]['txn_type']];
            $arrReport[$j]['status'] = $STATUS[$rsAuth[$i]['status']];
            $arrReport[$j]['wallet_hr_dr'] = ($rsAuth[$i]['purse_master_id'] == $purseDetailsHr['id'] && strtolower($rsAuth[$i]['rev_indicator']) == 'n') ? $rsAuth[$i]['amount_txn']: 0;
            $arrReport[$j]['wallet_hr_cr'] = ($rsAuth[$i]['purse_master_id'] == $purseDetailsHr['id'] && strtolower($rsAuth[$i]['rev_indicator']) == 'y') ? $rsAuth[$i]['amount_txn']: 0;
            $arrReport[$j]['wallet_ins_dr'] = ($rsAuth[$i]['purse_master_id'] == $purseDetailsIns['id'] && strtolower($rsAuth[$i]['rev_indicator']) == 'n') ? $rsAuth[$i]['amount_txn']: 0;
            $arrReport[$j]['wallet_ins_cr'] = ($rsAuth[$i]['purse_master_id'] == $purseDetailsIns['id'] && strtolower($rsAuth[$i]['rev_indicator']) == 'y') ? $rsAuth[$i]['amount_txn']: 0;
            $arrReport[$j]['wallet_gen_dr'] = ($rsAuth[$i]['purse_master_id'] == $purseDetailsGen['id'] && strtolower($rsAuth[$i]['rev_indicator']) == 'n') ? $rsAuth[$i]['amount_txn']: 0;
            $arrReport[$j]['wallet_gen_cr'] = ($rsAuth[$i]['purse_master_id'] == $purseDetailsGen['id'] && strtolower($rsAuth[$i]['rev_indicator']) == 'y') ? $rsAuth[$i]['amount_txn']: 0;
            $arrReport[$j]['txn_no'] = $rsAuth[$i]['txn_no'];
            $arrReport[$j]['txn_code'] = $rsAuth[$i]['txn_code'];
            $arrReport[$j]['narration'] = $rsAuth[$i]['narration'];
            $j++;
        }
        
        return $arrReport;
        
    }
    
    public function getAgentDailyTxn($param, $agentId = 0, $agentName = '') 
    {
        $date = isset($param['date']) ? $param['date'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        
        $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");
        
        $objFundReq = new FundRequest();
        $objFunding = new AgentFunding();
        $fundtrfrModel = new AgentFundTransfer();
        $objRemittance = new Remit_Remittancerequest();
        $objRemitter = new Remit_Remitter();
        $paytronicLoadModel = new Corp_Ratnakar_Cardload();

        $objBAPC = new BindAgentProductCommission();
        $i = 0;
        $arrReport = array();
        foreach ($param as $dates) 
        {
            $arr = array(
                'from' => $dates['from'],
                'to' => $dates['to'],
                'agent_id' => $agentId,
		'txn_type' => ''
            );
            $fromDateOnlyArr = explode(' ', $dates['from']);
            $fromDateOnly = date('Y-m-d', strtotime($fromDateOnlyArr[0]. "-1 day"));
            
            $fromDateOnlyForBank = $fromDateOnlyArr[0];
        
            // fetching bank name for agent for particular product and date
            $bankDetails = $objBAPC->getAgentBinding($agentId, $fromDateOnlyForBank);
            $bank_unicode = isset($bankDetails[0]['bank_unicode']) ? $bankDetails[0]['bank_unicode'] : '';
            

            $select = $this->_db->select()       
                ->from(DbTable::TABLE_AGENT_CLOSING_BALANCE,array('closing_balance AS opening_bal'))              
                ->where('agent_id = ?', $agentId)
                ->where("date = ? ", $fromDateOnly);
            $openingBal = $this->_db->fetchRow($select);      
            $balance = ($openingBal['opening_bal'] > 0) ? $openingBal['opening_bal'] : 0;
            $select = $this->_db->select()       
                ->from(DbTable::TABLE_AGENT_CLOSING_BALANCE,array('closing_balance AS closing_bal'))              
                ->where('agent_id = ?',$agentId)
                ->where("date = ?", $fromDateOnlyArr[0]);
           $closingBal = $this->_db->fetchRow($select);      
            
            $agentFunds = $objFundReq->getAgentFunds($arr);
            foreach($agentFunds as $data)
            {
                $arrReport[$i]['date_created'] = $data['datetime_response'];
                $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_AGENT_FUND_LOAD];
                $arrReport[$i]['amount'] = $data['amount'];
                $arrReport[$i]['txn_code'] = $data['txn_code'];
                $arrReport[$i]['mode'] = TXN_MODE_CR;
                $arrReport[$i]['txn_status'] = STATUS_SUCCESS;
                $arrReport[$i]['narration'] = $agentName;
                $arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
                $arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
                $balance += $data['amount'];
                $arrReport[$i]['balance'] = $balance;
                $i++;
            }

            $agentFunding = $objFunding->getAgentFunds($arr);
            foreach($agentFunding as $data)
            {
                $arrReport[$i]['date_created'] = $data['settlement_date'];
                $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_AGENT_FUND_LOAD];
                $arrReport[$i]['amount'] = $data['amount'];
                $arrReport[$i]['txn_code'] = $data['txn_code'];
                $arrReport[$i]['mode'] = TXN_MODE_CR;
                $arrReport[$i]['txn_status'] = STATUS_SUCCESS;
                $arrReport[$i]['narration'] = $agentName;
                $arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
                $arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
                $balance += $data['amount'];
                $arrReport[$i]['balance'] = $balance;
                $i++;
            }
            
            $arr['txn_type'] = TXNTYPE_AGENT_TOAGENT_FUND_TRANSFER;
            $agentFundTrfrCr = $fundtrfrModel->getAgentFundsTrfrCr($arr);
            foreach($agentFundTrfrCr as $data)
            {
                $arrReport[$i]['date_created'] = $data['date_created'];
                $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_AGENT_TOAGENT_FUND_TRANSFER];
                $arrReport[$i]['amount'] = $data['amount'];
                $arrReport[$i]['txn_code'] = $data['txn_code'];
                $arrReport[$i]['mode'] = TXN_MODE_CR;
                $arrReport[$i]['txn_status'] = STATUS_SUCCESS;
                $arrReport[$i]['narration'] = "Transfer from ".$data['first_name'] . " " . $data['last_name'];
                $arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
                $arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
                $balance += $data['amount'];
                $arrReport[$i]['balance'] = $balance;
                $i++;
            }
            $arr['txn_type'] = TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL;
            $agentFundTrfrRvslCr = $fundtrfrModel->getAgentFundsTrfrCr($arr);
            foreach($agentFundTrfrRvslCr as $data)
            {
                $arrReport[$i]['date_created'] = $data['date_created'];
                $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL];
                $arrReport[$i]['amount'] = $data['amount'];
                $arrReport[$i]['txn_code'] = $data['txn_code'];
                $arrReport[$i]['mode'] = TXN_MODE_CR;
                $arrReport[$i]['txn_status'] = STATUS_SUCCESS;
                $arrReport[$i]['narration'] = "Reversal from ".$data['first_name'] . " " . $data['last_name'];
                $arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
                $arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
                $balance += $data['amount'];
                $arrReport[$i]['balance'] = $balance;
                $i++;
            }

            $arr['txn_type'] = TXNTYPE_AGENT_TOAGENT_FUND_TRANSFER;
            $agentFundTrfrDr = $fundtrfrModel->getAgentFundsTrfrDr($arr);
            foreach($agentFundTrfrDr as $data)
            {
                
                $arrReport[$i]['date_created'] = $data['date_created'];
                $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_AGENT_TOAGENT_FUND_TRANSFER];
                $arrReport[$i]['amount'] = $data['amount'];
                $arrReport[$i]['txn_code'] = $data['txn_code'];
                $arrReport[$i]['mode'] = TXN_MODE_DR;
                $arrReport[$i]['txn_status'] = STATUS_SUCCESS;
                $arrReport[$i]['narration'] = "Transfer to ".$data['first_name'] . " " . $data['last_name'];
                $arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
                $arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
                $balance -= $data['amount'];
                $arrReport[$i]['balance'] = $balance;
                $i++;
            }
            $arr['txn_type'] = TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL;
            $agentFundTrfrRvslDr = $fundtrfrModel->getAgentFundsTrfrDr($arr);
            foreach($agentFundTrfrRvslDr as $data)
            {
                $arrReport[$i]['date_created'] = $data['date_created'];
                $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL];
                $arrReport[$i]['amount'] = $data['amount'];
                $arrReport[$i]['txn_code'] = $data['txn_code'];
                $arrReport[$i]['mode'] = TXN_MODE_DR;
                $arrReport[$i]['txn_status'] = STATUS_SUCCESS;
                $arrReport[$i]['narration'] = "Reversal to ".$data['first_name'] . " " . $data['last_name'];
                $arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
                $arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
                $balance -= $data['amount'];
                $arrReport[$i]['balance'] = $balance;
                $i++;
            }
            
            
            $arr['bank_unicode'] = $bank_unicode;
            $rmtr = $objRemitter->getAgentRemitterRegnsFeeSTax($arr);
            foreach($rmtr as $data)
            {
                $arrReport[$i]['date_created'] = $data['date_created'];
                $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_REMITTER_REGISTRATION];
                $arrReport[$i]['amount'] = $data['regn_fee'];
                $arrReport[$i]['txn_code'] = $data['txn_code'];
                $arrReport[$i]['mode'] = TXN_MODE_DR;
                $arrReport[$i]['txn_status'] = STATUS_SUCCESS;
                $arrReport[$i]['narration'] = $data['name'] . " " . $data['last_name'];
                $arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
                $arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
                $balance -= $data['regn_fee'];
                $arrReport[$i]['balance'] = $balance;
                $i++;
                
                $arrReport[$i]['date_created'] = $data['date_created'];
                $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_REMITTANCE_SERVICE_TAX];
                $arrReport[$i]['amount'] = $data['service_tax'];
                $arrReport[$i]['txn_code'] = $data['txn_code'];
                $arrReport[$i]['mode'] = TXN_MODE_DR;
                $arrReport[$i]['txn_status'] = STATUS_SUCCESS;
                $arrReport[$i]['narration'] = $data['name'] . " " . $data['last_name'];
                $arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
                $arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
                $balance -= $data['service_tax'];
                $arrReport[$i]['balance'] = $balance;
                $i++;
            }
            
            $remt = $objRemittance->getAgentRemittancesFeeSTax($arr);
            foreach($remt as $data)
            {
                $arrReport[$i]['date_created'] = $data['date_created'];
                $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_REMITTANCE];
                $arrReport[$i]['amount'] = $data['amount'];
                $arrReport[$i]['txn_code'] = $data['txn_code'];
                $arrReport[$i]['mode'] = TXN_MODE_DR;
                $arrReport[$i]['txn_status'] = STATUS_SUCCESS;
                $arrReport[$i]['narration'] = $data['name'] . " " . $data['last_name'];
                $arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
                $arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
                $balance -= $data['amount'];
                $arrReport[$i]['balance'] = $balance;
                $i++;
                
                $arrReport[$i]['date_created'] = $data['date_created'];
                $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_REMITTANCE_FEE];
                $arrReport[$i]['amount'] = $data['fee'];
                $arrReport[$i]['txn_code'] = $data['txn_code'];
                $arrReport[$i]['mode'] = TXN_MODE_DR;
                $arrReport[$i]['txn_status'] = STATUS_SUCCESS;
                $arrReport[$i]['narration'] = $data['name'] . " " . $data['last_name'];
                $arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
                $arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
                $balance -= $data['fee'] ;
                $arrReport[$i]['balance'] = $balance;
                $i++;
                
                $arrReport[$i]['date_created'] = $data['date_created'];
                $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_REMITTANCE_SERVICE_TAX];
                $arrReport[$i]['amount'] = $data['service_tax'];
                $arrReport[$i]['txn_code'] = $data['txn_code'];
                $arrReport[$i]['mode'] = TXN_MODE_DR;
                $arrReport[$i]['txn_status'] = STATUS_SUCCESS;
                $arrReport[$i]['narration'] = $data['name'] . " " . $data['last_name'];
                $arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
                $arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
                $balance -= $data['service_tax'];
                $arrReport[$i]['balance'] = $balance;
                $i++;
            }
            
            $refunds = $objRemittance->getAgentRemittanceRefundsFeeSTax($arr);
            foreach($refunds as $data)
            {
                $arrReport[$i]['date_created'] = $data['date_created'];
                $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_REMITTANCE_REFUND];
                $arrReport[$i]['amount'] = $data['amount'];
                $arrReport[$i]['txn_code'] = $data['txn_code'];
                $arrReport[$i]['mode'] = TXN_MODE_CR;
                $arrReport[$i]['txn_status'] = STATUS_SUCCESS;
                $arrReport[$i]['narration'] = $data['name'] . " " . $data['last_name'];
                $arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
                $arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
                $balance += $data['amount'];
                $arrReport[$i]['balance'] = $balance;
                $i++;
                
                $arrReport[$i]['date_created'] = $data['date_created'];
                $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_REMITTANCE_REVERSAL_REFUND_FEE];
                $arrReport[$i]['amount'] = $data['reversal_fee'];
                $arrReport[$i]['txn_code'] = $data['txn_code'];
                $arrReport[$i]['mode'] = TXN_MODE_CR;
                $arrReport[$i]['txn_status'] = STATUS_SUCCESS;
                $arrReport[$i]['narration'] = $data['name'] . " " . $data['last_name'];
                $arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
                $arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
                $balance += $data['reversal_fee'];
                $arrReport[$i]['balance'] = $balance;
                $i++;
                
                $arrReport[$i]['date_created'] = $data['date_created'];
                $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_REMITTANCE_REVERSAL_SERVICE_TAX];
                $arrReport[$i]['amount'] = $data['reversal_service_tax'];
                $arrReport[$i]['txn_code'] = $data['txn_code'];
                $arrReport[$i]['mode'] = TXN_MODE_CR;
                $arrReport[$i]['txn_status'] = STATUS_SUCCESS;
                $arrReport[$i]['narration'] = $data['name'] . " " . $data['last_name'];
                $arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
                $arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
                $balance += $data['reversal_service_tax'];
                $arrReport[$i]['balance'] = $balance;
                $i++;
            }
            
            // Paytronic Load
             $arr['txn_type'] = TXNTYPE_CARD_RELOAD;
             $paytronicCorpLoad = $paytronicLoadModel->getAgentLoadsAndReversal($arr);
             
              foreach($paytronicCorpLoad as $data)
            {
                $arrReport[$i]['date_created'] = $data['date_created'];
                $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_CARD_RELOAD];
                $arrReport[$i]['amount'] = $data['amount'];
                $arrReport[$i]['txn_code'] = $data['txn_code'];
                $arrReport[$i]['mode'] = TXN_MODE_DR;
                $arrReport[$i]['txn_status'] = STATUS_SUCCESS;
                $arrReport[$i]['narration'] = "Customer Load";
                $arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
                $arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
                $balance -= $data['amount'];
                $arrReport[$i]['balance'] = $balance;
                $i++;
                
                
                if($data['amount_cutoff']  > 0){
                    $arrReport[$i]['date_created'] = $data['date_cutoff'];
                    $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_REVERSAL_LOAD];
                    $arrReport[$i]['amount'] = $data['amount_cutoff'];
                    $arrReport[$i]['txn_code'] = $data['txn_code'];
                    $arrReport[$i]['mode'] = TXN_MODE_CR;
                    $arrReport[$i]['txn_status'] = STATUS_SUCCESS;
                    $arrReport[$i]['narration'] = "Customer Load Reversal";
                    $arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
                    $arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
                    $balance += $data['amount'];
                    $arrReport[$i]['balance'] = $balance;
                    $i++;
                }
                
                $arrReport[$i]['date_created'] = $data['date_created'];
                $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_REMITTANCE_FEE];
                $arrReport[$i]['amount'] = $data['fee'];
                $arrReport[$i]['txn_code'] = $data['txn_code'];
                $arrReport[$i]['mode'] = TXN_MODE_DR;
                $arrReport[$i]['txn_status'] = STATUS_SUCCESS;
                $arrReport[$i]['narration'] = "Customer Load Fee";
                $arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
                $arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
                $balance -= $data['fee'] ;
                $arrReport[$i]['balance'] = $balance;
                $i++;
                
                $arrReport[$i]['date_created'] = $data['date_created'];
                $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_REMITTANCE_SERVICE_TAX];
                $arrReport[$i]['amount'] = $data['service_tax'];
                $arrReport[$i]['txn_code'] = $data['txn_code'];
                $arrReport[$i]['mode'] = TXN_MODE_DR;
                $arrReport[$i]['txn_status'] = STATUS_SUCCESS;
                $arrReport[$i]['narration'] = "Load Service Tax";
                $arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
                $arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
                $balance -= $data['service_tax'];
                $arrReport[$i]['balance'] = $balance;
                $i++;
            }
            
            // Paytronic Reversal
            /*$arr['cutoff'] = TRUE;
            $paytronicReversal = $paytronicLoadModel->getAgentLoadsAndReversal($arr);
            
              foreach($paytronicReversal as $data)
            {
                $arrReport[$i]['date_created'] = $data['date_created'];
                $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_REVERSAL_LOAD];
                $arrReport[$i]['amount'] = $data['amount'];
                $arrReport[$i]['txn_code'] = $data['txn_code'];
                $arrReport[$i]['mode'] = TXN_MODE_CR;
                $arrReport[$i]['txn_status'] = STATUS_SUCCESS;
                $arrReport[$i]['narration'] = "Customer Load Reversal";
                $arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
                $arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
                $balance += $data['amount'];
                $arrReport[$i]['balance'] = $balance;
                $i++;
            } */
            
             // Paytronic Reversal
            $arr['txn_type'] = TXNTYPE_CARD_DEBIT;
            $arr['status'] = STATUS_DEBITED;
            $paytronicReversal = $paytronicLoadModel->getAgentLoadsAndReversal($arr);
            
              foreach($paytronicReversal as $data)
            {
                $arrReport[$i]['date_created'] = $data['date_created'];
                $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_CARD_DEBIT];
                $arrReport[$i]['amount'] = $data['amount'];
                $arrReport[$i]['txn_code'] = $data['txn_code'];
                $arrReport[$i]['mode'] = TXN_MODE_CR;
                $arrReport[$i]['txn_status'] = STATUS_SUCCESS;
                $arrReport[$i]['narration'] = "Customer Load Reversal";
                $arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
                $arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
                $balance += $data['amount'];
                $arrReport[$i]['balance'] = $balance;
                $i++;
            }

 //START
            
            $arr['txn_type'] = TXNTYPE_AGENT_COMMISSION;
            $txnAgentObj = new TxnAgent();
            
            $rmtr = $txnAgentObj->getAgentTxnsForCommission($arr);
            foreach($rmtr as $data)
            {
            	$arrReport[$i]['date_created'] = $data['date_created'];
            	$arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_AGENT_COMMISSION];
            	$arrReport[$i]['amount'] = $data['amount'];
            	
            	if(isset($data['kot_txn_code']) && $data['kot_txn_code'] > 0){
            		$arrReport[$i]['txn_code'] = $data['kot_txn_code'];
            		$arrReport[$i]['narration'] = $data['kot_name'] . " " . $data['kot_last_name'];
            	}
            	
                if(isset($data['rat_txn_code']) && $data['rat_txn_code'] > 0){
            		$arrReport[$i]['txn_code'] = $data['rat_txn_code'];
            		$arrReport[$i]['narration'] = $data['rat_name'] . " " . $data['rat_last_name'];
            	}
            	            	
            	$arrReport[$i]['mode'] = TXN_MODE_CR;
            	$arrReport[$i]['txn_status'] = STATUS_SUCCESS;
            	$arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
            	$arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
            	$balance += $data['amount'];
            	$arrReport[$i]['balance'] = $balance;
            	$i++;
            }
            
            $arr['txn_type'] = TXNTYPE_AGENT_COMMISSION_REVERSAL;
            
            $rmtr = $txnAgentObj->getAgentTxnsForCommissionReversal($arr);
            foreach($rmtr as $data)
            {
            	$arrReport[$i]['date_created'] = $data['date_created'];
            	$arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[TXNTYPE_AGENT_COMMISSION_REVERSAL];
            	$arrReport[$i]['amount'] = $data['amount'];
            	 
            	if(isset($data['kot_txn_code']) && $data['kot_txn_code'] > 0){
            		$arrReport[$i]['txn_code'] = $data['kot_txn_code'];
            		$arrReport[$i]['narration'] = $data['kot_name'] . " " . $data['kot_last_name'];
            	}
            	 
            	if(isset($data['rat_txn_code']) && $data['rat_txn_code'] > 0){
            		$arrReport[$i]['txn_code'] = $data['rat_txn_code'];
            		$arrReport[$i]['narration'] = $data['rat_name'] . " " . $data['rat_last_name'];
            	}
            
            	$arrReport[$i]['mode'] = TXN_MODE_DR;
            	$arrReport[$i]['txn_status'] = STATUS_SUCCESS;
            	$arrReport[$i]['opening_bal'] = $openingBal['opening_bal'];
            	$arrReport[$i]['closing_bal'] = $closingBal['closing_bal'];
            	$balance -= $data['amount'];
            	$arrReport[$i]['balance'] = $balance;
            	$i++;
            }
            
            //END

        }
        return $arrReport;
        
    }
    public function getRemittersAndAmount($param, $agentId, $userName, $agentCode, $withfee = true) {

        $objRemitter = new Remit_Kotak_Remitter();
        $objRemrequest = new Remit_Kotak_Remittancerequest();

        $reportDataArr = array();
        foreach ($param as $queryDate) {

            $dataArr = array();
            $to = isset($queryDate['to']) ? $queryDate['to'] : '';
            $from = isset($queryDate['from']) ? $queryDate['from'] : '';
            $param = array('to' => $to, 'from' => $from, 'agentId' => $agentId);

            $remitterAmt = $objRemitter->getRemittersAmount($param);
            $tranData = $objRemrequest->getTotalRemittanceFee(array('date' => date("Y-m-d", strtotime($from)), 'agent_id' => $agentId, 'check_fee' => true));
            $refundData = $objRemrequest->getAgentTotalRemittanceRefundSTax(array('date' => date("Y-m-d", strtotime($from)), 'agent_id' => $agentId));

            $dataArr['date'] = date("d/m/Y", strtotime($from));
            $dataArr['name'] = $userName;
            $dataArr['code'] = $agentCode;
            $dataArr['remitter_count'] = isset($remitterAmt['count']) ? $remitterAmt['count'] : '0';
            $dataArr['remitter_amt'] = round($remitterAmt['remitter_fee'] + $remitterAmt['remitter_tax'], 2);
            $dataArr['remitter_txn_cnt'] = isset($tranData['count']) ? $tranData['count'] : '0';
            $dataArr['remitter_rfnd_cnt'] = isset($refundData['agent_total_remittance_refund_count']) ? $refundData['agent_total_remittance_refund_count'] : '0';

            if ($withfee) {
                $dataArr['remitter_txn_amt'] = round($tranData['fee'] + $tranData['total'], 2);
                $dataArr['remitter_rfnd_amt'] = round($refundData['agent_total_remittance_refund'] + $refundData['agent_total_reversal_refund_fee'], 2);
            } else {
                $dataArr['remitter_txn_amt'] = round($tranData['total'], 2);
                $dataArr['remitter_rfnd_amt'] = round($refundData['agent_total_remittance_refund'], 2);
            }

            $reportDataArr[] = $dataArr;
        }
        return $reportDataArr;
    }


    public function getApplications($param, $status = '') {
        if (!empty($param)) {
         
          $customerModel = new Corp_Kotak_Customers();
          $param['bank_status'] = $status;
          $retReportData = $customerModel->showApplicationDetails($param);
          return $retReportData;
        } else
            return array();
    }

    public function exportgetApplications($param, $status) {
        $data = $this->getApplications($param, $status);

        $retData = array();

        if (!empty($data)) {
            foreach ($data as $key => $data) {
   
                $retData[$key]['first_name'] = $data['first_name'];
                $retData[$key]['last_name'] = $data['last_name'];
                $retData[$key]['member_id'] = $data['member_id'];
                $retData[$key]['card_number'] = $data['card_number'];
                $retData[$key]['card_pack_id'] = $data['card_pack_id'];
                $retData[$key]['date_of_birth'] = $data['date_of_birth'];
                $retData[$key]['mobile'] = $data['mobile'];
                $retData[$key]['email'] = $data['email'];
                $retData[$key]['status_bank'] = $data['status_bank'];
                $retData[$key]['date_created'] = $data['date_created'];
                $retData[$key]['date_approval'] = $data['date_approval'];
                $retData[$key]['date_authorize'] = $data['date_authorize'];
                $retData[$key]['recd_doc'] = $data['recd_doc'];
                
            }
        }
        return $retData;
    }
    
        /*
     *  getCardholders function will fetch cardholders details registred during a time span
     */
    public function getCardholders($param, $dateCreated = FALSE) {
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['3'];
        }
        
        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $objCardholders = new Corp_Kotak_Customers();
                $detailsArr = $objCardholders->getCardholders($param);
                break;
            case $bankUnicodeArr['2']:
                $objCardholders = new Corp_Ratnakar_Cardholders();
                $detailsArr = $objCardholders->getCardholders($param,$dateCreated = TRUE);
                break;
            case $bankUnicodeArr['1']:
                $objCardholders = new Corp_Boi_Customers();
                $detailsArr = $objCardholders->getCardholders($param);
                break;
        }
        return $detailsArr;
    }
    
       /*
     *  getCardholders function will fetch cardholders details registred during a time span
     */
    public function getCardholdersOps($param) {
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['3'];
        }
        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $objCardholders = new Corp_Kotak_Customers();
                $detailsArr = $objCardholders->getCardholders($param);
                break;
            case $bankUnicodeArr['2']:
                $objCardholders = new Corp_Ratnakar_Cardholders();
                $detailsArr = $objCardholders->getCardholders($param);
                break;
            case $bankUnicodeArr['1']:
                $objCardholders = new Corp_Boi_Customers();
                $detailsArr = $objCardholders->getCardholders($param);
                break;
        }
        return $detailsArr;
    }
        /*
     *  getCardholders function will fetch cardholders details registred during a time span
     */
    public function exportgetCardholders($param ,$dateCreated = FALSE) {
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['3'];
        }
        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $objCardholders = new Corp_Kotak_Customers();
                $detailsArr = $objCardholders->exportgetCardholders($param);
                break;
            case $bankUnicodeArr['2']:
                $objCardholders = new Corp_Ratnakar_Cardholders();
                $detailsArr = $objCardholders->exportgetCardholders($param,$dateCreated = TRUE);
                break;
            case $bankUnicodeArr['1']:
                $objCardholders = new Corp_Boi_Customers();
                $detailsArr = $objCardholders->exportgetCardholders($param);
                break;
        }
        return $detailsArr;
    }
    
        /*
     *  getCardholders function will fetch cardholders details registred during a time span
     */
    public function exportgetCardholdersOps($param) {
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['3'];
        }
        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $objCardholders = new Corp_Kotak_Customers();
                $detailsArr = $objCardholders->exportgetCardholders($param);
                break;
            case $bankUnicodeArr['2']:
                $objCardholders = new Corp_Ratnakar_Cardholders();
                $detailsArr = $objCardholders->exportgetCardholders($param);
                break;
            case $bankUnicodeArr['1']:
                $objCardholders = new Corp_Boi_Customers();
                $detailsArr = $objCardholders->exportgetCardholdersOps($param);
                break;
        }
        return $detailsArr;
    }
     public function getBcListDetails($param) {
        if (!empty($param)) {
         
          $agentModel = new Agents();
          $param['enroll_status'] = STATUS_APPROVED;
          $param['status'] = STATUS_UNBLOCKED;
          $retReportData = $agentModel->getBCListUnderDistributor($param);
          return $retReportData;
        } else
            return array();
    }

    public function exportgetBcListDetails($param) {
        $data = $this->getBcListDetails($param);

        $retData = array();

        if (!empty($data)) {
            foreach ($data as $key => $data) {
                $retData[$key]['institution_name'] = $data['institution_name'];
                $retData[$key]['centre_id'] = $data['centre_id'];
                $retData[$key]['branch_id'] = $data['branch_id'];
                $retData[$key]['terminal_id_tid_1'] = $data['terminal_id_tid_1'];
                $retData[$key]['terminal_id_tid_2'] = $data['terminal_id_tid_2'];
                $retData[$key]['terminal_id_tid_3'] = $data['terminal_id_tid_3'];
                $retData[$key]['agent_name'] = $data['agent_name'];
                $retData[$key]['email'] = $data['email'];
                $retData[$key]['mobile1'] = $data['mobile1'];
                
                
            }
        }
        return $retData;
    }
    
     public function getWalletTrialBalance($param) 
    { 
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['3'];
        }
       
        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
              
                $objCardload = new Corp_Kotak_Cardload();
                $detailsArr = $objCardload->getWalletTrialBalance($param);
                break;
            case $bankUnicodeArr['2']:
                $objCardload = new Corp_Ratnakar_Cardload();
                $detailsArr = $objCardload->getWalletTrialBalance($param);
                break;
            case $bankUnicodeArr['1']:
                $objCardload = new Corp_Boi_Cardload();
                $detailsArr = $objCardload->getWalletTrialBalance($param);
                break;
        }
        return $detailsArr;
        
    }
    
    public function getLoadRequests($param) 
    {  
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['3'];
        }
       
        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
                
                $objCardload = new Corp_Kotak_Cardload();
                $detailsArr = $objCardload->getLoadRequests($param);
                break;
            case $bankUnicodeArr['2']:
                $objCardload = new Corp_Ratnakar_Cardload();
                $detailsArr = $objCardload->getLoadRequests($param);
                break;
            case $bankUnicodeArr['1']:
                $objCardload = new Corp_Boi_Cardload();
                $detailsArr = $objCardload->getLoadRequests($param);
                break;
        }
        return $detailsArr;
        
    }
    
    public function exportLoadRequests($param) 
    {  
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['3'];
        }
       
        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
              
                $objCardload = new Corp_Kotak_Cardload();
                $detailsArr = $objCardload->exportLoadRequests($param);
                break;
            case $bankUnicodeArr['2']:
                $objCardload = new Corp_Ratnakar_Cardload();
                $detailsArr = $objCardload->exportLoadRequests($param);
                break;
            case $bankUnicodeArr['1']:
                $objCardload = new Corp_Boi_Cardload();
                $detailsArr = $objCardload->exportLoadRequests($param);
                break;
        }
        return $detailsArr;
        
    }
    
     public function getAgentFunding($param , $agentId = 0) {
         $fundTransferModel = new AgentFundTransfer();
         $objFunding = new AgentFunding();
         $paytronicLoadModel = new Corp_Ratnakar_Cardload();
         $authRequestModel = new AuthRequest();
         $user = Zend_Auth::getInstance()->getIdentity();
         $userModel = new AgentUser();
         $agentProduct = $userModel->getAgentBinding($user->id);
         $dataArr = array();
         $i = 0;
         
         foreach ($param as $queryDate) {
        
        
         $fundArr = array(
                          'agent_id' => $agentId,
                          'product_id' => $agentProduct['product_id'],
                          'to' => $queryDate['to'],
                          'from' => $queryDate['from'],
                          'txn_type' => TXNTYPE_AGENT_FUND_LOAD
             
             );
          $fundRequestDetails =  $objFunding->getAgentFunding($fundArr);
//          $cntFund = count($fundRequestDetails);
//          for($i = 0; $i < $cntFund; $i++){
           foreach($fundRequestDetails as  $fundReqDetails){
            $dataArr[$i]['date'] = $fundReqDetails['date_request'];
            $dataArr[$i]['transfer_type_name'] = $fundReqDetails['transfer_type_name'];
            $dataArr[$i]['funding_no'] = $fundReqDetails['funding_no'];
            $dataArr[$i]['amount'] = $fundReqDetails['amount'];
            $dataArr[$i]['status'] = $fundReqDetails['agent_funding_status'];
            $dataArr[$i]['remarks'] = $fundReqDetails['settlement_remarks'];
            $i++;
            
         }
          $trfrArr = array('agent_id' => $agentId,
                          'to' => $queryDate['to'],
                          'from' => $queryDate['from'],
                          'txn_type' => TXNTYPE_AGENT_TOAGENT_FUND_TRANSFER
             
             );
         
        
         $toAgentTrfer = $fundTransferModel->getAgentFundsTransferDetails($trfrArr);
//         $cntToAgent = count($toAgentTrfr);
//         $j = $i;
//        for($i = 0; $i < $cntToAgent; $i++)
         foreach($toAgentTrfer as $toAgentTrfr)
        {
            $dataArr[$i]['date'] = $toAgentTrfr['date_created'];
            $dataArr[$i]['transfer_type_name'] = 'Fund Transfer to Agent';
            $dataArr[$i]['funding_no'] = $toAgentTrfr['txn_code'];
            $dataArr[$i]['amount'] = $toAgentTrfr['tr_amount'];
            $dataArr[$i]['status'] = $toAgentTrfr['status'];
            $dataArr[$i]['remarks'] = '';
            $i++;
           
         }
        
         $trfrArr['txn_type'] = TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL;
         $ReversalAgentTrfer = $fundTransferModel->getAgentFundsTransferDetails($trfrArr);
//         $cntRevAgent = count($ReversalAgentTrfr);
//         $k = $j;
//         for($i= 0; $i < $cntRevAgent; $i++){
         foreach($ReversalAgentTrfer as $ReversalAgentTrfr){
            $dataArr[$i]['date'] = $ReversalAgentTrfr['date_created'];
            $dataArr[$i]['transfer_type_name'] = 'Fund Reversal to Agent';
            $dataArr[$i]['funding_no'] = $ReversalAgentTrfr['txn_code'];
            $dataArr[$i]['amount'] = $ReversalAgentTrfr['tr_amount'];
            $dataArr[$i]['status'] = $ReversalAgentTrfr['status'];
            $dataArr[$i]['remarks'] = '';
            $i++;
         }
         
         $trfrArr['txn_type'] = TXNTYPE_AGENT_TOAGENT_FUND_TRANSFER;
         $toSAgentTrfer = $fundTransferModel->getSuperAgentFundsTransferDetails($trfrArr);
//         $cntToSAgent = count($toSAgentTrfr);
//         $l = $k;
       
//         for($i = 0; $i < $cntToSAgent; $i++)
         foreach($toSAgentTrfer as $toSAgentTrfr)
         {
            $dataArr[$i]['date'] = $toSAgentTrfr['date_created'];
            $dataArr[$i]['transfer_type_name'] = 'Fund Transfer from Super Agent';
            $dataArr[$i]['funding_no'] = $toSAgentTrfr['txn_code'];
            $dataArr[$i]['amount'] = $toSAgentTrfr['tr_amount'];
            $dataArr[$i]['status'] = $toSAgentTrfr['status'];
            $dataArr[$i]['remarks'] = '';
            $i++;
           
         }
         $trfrArr['txn_type'] = TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL;
         $ReversalSAgentTrfer = $fundTransferModel->getSuperAgentFundsTransferDetails($trfrArr);
//         $cntSRevAgent = count($ReversalSAgentTrfr);
//         
//         $m = $l;
        
         
             foreach($ReversalSAgentTrfer as $ReversalSAgentTrfr){
            $dataArr[$i]['date'] = $ReversalSAgentTrfr['date_created'];
            $dataArr[$i]['transfer_type_name'] = 'Fund Reversal to Super Agent';
            $dataArr[$i]['funding_no'] = $ReversalSAgentTrfr['txn_code'];
            $dataArr[$i]['amount'] = $ReversalSAgentTrfr['tr_amount'];
            $dataArr[$i]['status'] = $ReversalSAgentTrfr['status'];
            $dataArr[$i]['remarks'] = '';
            $i++;
         }
         
            // Paytronic Load
            $trfrArr['product_id'] = $agentProduct['product_id'];
            $paytronicCorporateLoad = $paytronicLoadModel->getLoadRequests($trfrArr);
//            $cntLoad = count($paytronicCorpLoad);
//         $n = $m;
         
             
         foreach($paytronicCorporateLoad as $paytronicCorpLoad){
            $dataArr[$i]['date'] = $paytronicCorpLoad['date_created'];
            $dataArr[$i]['transfer_type_name'] = 'Load Request';
            $dataArr[$i]['funding_no'] = $paytronicCorpLoad['txn_code'];
            $dataArr[$i]['amount'] = $paytronicCorpLoad['amount'];
            $dataArr[$i]['status'] = $paytronicCorpLoad['status'];
            $dataArr[$i]['remarks'] = $paytronicCorpLoad['failed_reason'];
           $i++;
         } 
         // Paytronic debit
         $trfrArr['txn_type'] = TXNTYPE_CARD_DEBIT;
         $trfrArr['status'] = STATUS_DEBITED;
         $paytronicDebit = $paytronicLoadModel->getLoadRequests($trfrArr);

         
             
         foreach($paytronicDebit as $paytronicDbt){
            $dataArr[$i]['date'] = $paytronicDbt['date_created'];
            $dataArr[$i]['transfer_type_name'] = 'Debit';
            $dataArr[$i]['funding_no'] = $paytronicDbt['txn_code'];
            $dataArr[$i]['amount'] = $paytronicDbt['amount'];
            $dataArr[$i]['status'] = $paytronicDbt['status'];
            $dataArr[$i]['remarks'] = $paytronicDbt['failed_reason'];
           $i++;
         } 
         
         $misclDRdetails = $authRequestModel->getAllPaytronicCompletedTxn($fundArr);
        
//         $cntDR = count($miscDRdetails);
//         $o = $n;
//         for($i= 0; $i < $cntDR; $i++){
         foreach($misclDRdetails as $miscDRdetails){
            $dataArr[$i]['date'] = $miscDRdetails['date_created'];
            $dataArr[$i]['transfer_type_name'] = 'Load Complete';
            $dataArr[$i]['funding_no'] = $miscDRdetails['txn_code'];
            $dataArr[$i]['amount'] = $miscDRdetails['amount'];
            $dataArr[$i]['status'] = $miscDRdetails['status'];
            $dataArr[$i]['remarks'] = $miscDRdetails['failed_reason'];
           $i++;
         } 
        
        
        $misclCRdetails = $authRequestModel->getAllPaytronicReversedTxn($fundArr);
        
//         for($i= 0; $i < $cntCR; $i++){
            foreach($misclCRdetails as $miscCRdetails) {
            $dataArr[$p]['date'] = $miscCRdetails[$i]['date_created'];
            $dataArr[$p]['transfer_type_name'] = 'Load Reversal';
            $dataArr[$p]['funding_no'] = $miscCRdetails[$i]['txn_code'];
            $dataArr[$p]['amount'] = $miscCRdetails[$i]['reversed'];
            $dataArr[$p]['status'] = $miscCRdetails[$i]['status'];
            $dataArr[$p]['remarks'] = $miscCRdetails[$i]['failed_reason'];
           $i++;
         } 
      
        }
       
        return $dataArr;
        
    }
    
    public function getWalletBalance($param) 
    {  
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['3'];
        }
       
        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
              
                $objCardload = new Corp_Kotak_Cardload();
                $detailsArr = $objCardload->getWalletbalance($param);
                break;
            case $bankUnicodeArr['2']:
                $objCardload = new Corp_Ratnakar_Cardload();
                $detailsArr = $objCardload->getWalletbalance($param);
                break;
            case $bankUnicodeArr['1']:
                $objCardload = new Corp_Boi_Cardload();
                $detailsArr = $objCardload->getWalletbalance($param);
                break;
        }
        return $detailsArr;
        
    }
    
     public function exportGetWalletbalance($param) 
    {  
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['3'];
        }
       
        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
              
                $objCardload = new Corp_Kotak_Cardload();
                $detailsArr = $objCardload->exportGetWalletbalance($param);
                break;
            case $bankUnicodeArr['2']:
                $objCardload = new Corp_Ratnakar_Cardload();
                $detailsArr = $objCardload->exportGetWalletbalance($param);
                break;
            case $bankUnicodeArr['1']:
                $objCardload = new Corp_Boi_Cardload();
                $detailsArr = $objCardload->exportGetWalletbalance($param);
                break;
        }
        return $detailsArr;
        
    }
    
    public function exportSampleLoadRequests($param) 
    {  
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['3'];
        }
       
        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
              
                $objCardload = new Corp_Kotak_Cardload();
                $detailsArr = $objCardload->exportSampleLoadRequests($param);
                break;
            case $bankUnicodeArr['2']:
                $objCardload = new Corp_Ratnakar_Cardload();
                $detailsArr = $objCardload->exportSampleLoadRequests($param);
                break;
            case $bankUnicodeArr['1']:
                $objCardload = new Corp_Boi_Cardload();
                $detailsArr = $objCardload->exportSampleLoadRequests($param);
                break;
        }
        return $detailsArr;
        
    }
    
    public function getBeneficiaryRegistrations($param) {
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1'];
        }

        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $remitModel = new Remit_Kotak_Beneficiary();
                $detailsArr = $remitModel->getBeneficiaryRegistrations($param);
                break;
             case $bankUnicodeArr['2']:
                $remitModel = new Remit_Ratnakar_Beneficiary();
                $detailsArr = $remitModel->getBeneficiaryRegistrations($param);
                break;
        }

        return $detailsArr;
    }
    
    public function exportGetBeneficiaryRegistrations($param) 
    {
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['3'];
        }
       
        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $objBeneficiary = new Remit_Kotak_Beneficiary();
                $detailsArr = $objBeneficiary->exportGetBeneficiaryRegistrations($param);
                break;
            case $bankUnicodeArr['2']:
                $objBeneficiary = new Remit_Ratnakar_Beneficiary();
                $detailsArr = $objBeneficiary->exportGetBeneficiaryRegistrations($param);
                break;
        }
        return $detailsArr;
    }
    
      public function getRemitWalletTrialBalance($param) 
    { 
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['3'];
        }
       
        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
              
                $objCardload = new Remit_Kotak_Remittancerequest();
                $detailsArr = $objCardload->getRemitWalletTrialBalance($param);
                break;
            case $bankUnicodeArr['2']:
                $objCardload = new Remit_Ratnakar_Remittancerequest();
                $detailsArr = $objCardload->getRemitWalletTrialBalance($param);
                break;
           
        }
        return $detailsArr;
    }    
    public function getMultiWalletbalance($param) {
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1'];
        }

        switch ($param['bank_unicode']) {
             case $bankUnicodeArr['2']:
                $objCardload = new Corp_Ratnakar_Cardload();
                $detailsArr = $objCardload->getMultiWalletbalance($param);
                break;
        }

        return $detailsArr;
    }
    
    public function exportMultiWalletbalance($param)
    {
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1'];
        }

        switch ($param['bank_unicode']) {
             case $bankUnicodeArr['2']:
                $objCardload = new Corp_Ratnakar_Cardload();
                $detailsArr = $objCardload->exportMultiWalletbalance($param);
                break;
        }

        return $detailsArr;        
    }
    

    public function getAgentVirtualBalanceSheet($param, $agentId = 0) {
        if (!empty($param)) {
             
            $objFundReq = new FundRequest(); 
            $objBAPC = new BindAgentProductCommission(); 
            $RatLoadModel = new Corp_Ratnakar_Cardload(); 
            
            $agStatus = array(
                STATUS_UNBLOCKED, STATUS_BLOCKED, STATUS_LOCKED
            ); 
            $avbsReportData = array();
            $i = 0;
            foreach ($param as $dates) {
                $fromDate = $dates['from'];
                $toDate = $dates['to'];
                $fromDateOnlyArr = explode(' ', $fromDate);
                $fromDateOnly = $fromDateOnlyArr[0];
                $from = new DateTime(Util::returnDateFormatted($fromDateOnly, "Y-m-d", "Y-m-d", "-","-","from")); 
                $to = new DateTime(Util::returnDateFormatted($fromDateOnly, "Y-m-d", "Y-m-d", "-","-","to"));
                $select = $this->_db->select();
                $select->from(DbTable::TABLE_AGENT_VIRTUAL_BALANCE . ' as avb',array('avb.agent_id'));
                $select->joinLeft(
                        DbTable::TABLE_AGENTS . ' as a', 
                        "a.id = avb.agent_id",
                        array(
                            'a.agent_code', 
                            'concat(a.first_name," ",a.last_name) as agent_name'
                ));
                $select->joinLeft(
                        DbTable::TABLE_AGENT_VIRTUAL_CLOSING_BALANCE . ' as acb', 
                        "avb.agent_id = acb.agent_id AND '" . $fromDateOnly . "'=acb.date", 
                        array(
                            'acb.closing_balance as agent_closing_balance'
                ));
                $select->joinLeft(
                        DbTable::TABLE_AGENT_VIRTUAL_CLOSING_BALANCE . ' as acb2', 
                        "avb.agent_id = acb2.agent_id AND DATE_SUB('" . $fromDateOnly . "', INTERVAL 1 DAY)=acb2.date", 
                        array(
                            'acb2.closing_balance as agent_opening_balance'
                ));
                $select->where('a.enroll_status=?', STATUS_APPROVED);
                $select->where("a.status IN (?)",$agStatus); 
                if($agentId > 0){
                    $select->where("avb.agent_id = ?", $agentId);
                }
                $select->order('agent_name ASC');
                $reportData1 = $this->_db->fetchAll($select); 
                $avbsReportData[$i] = $reportData1; 
                $totalRecs = count($reportData1);
                
                for ($j = 0; $j < $totalRecs; $j++) {
                    // fetching bank name for agent for particular product and date
                    $bankDetails = $objBAPC->getAgentBinding($reportData1[$j]['agent_id'], $fromDateOnly); 
             
                    $paramdata['agent_id'] = $reportData1[$j]['agent_id'] ;  
                    $paramdata['to'] = $to->format('Y-m-d H:i:s');
                    $paramdata['from'] = $from->format('Y-m-d H:i:s');
             
                    /*
                     * We get All Authorized Fund request for that day for this agent 
                     */
                    $paramdata['authorize'] = FLAG_YES;
                    $paramdata['status'] = array(STATUS_APPROVED);
                    $totalAuthorizeFundArr = $objFundReq->virtualFundRequests($paramdata); 
                    $totalAuthorizeFund = $totalAuthorizeFundArr['total_txn_amount'] ;
                    
                    /*
                     * We get All Un-Authorized Fund request for that day for this agent 
                     */
                    $paramdata['authorize'] = FLAG_NO;
                    $paramdata['status'] = array(STATUS_REJECTED,STATUS_PENDING);
                    $totalUnauthorizeFundArr = $objFundReq->virtualFundRequests($paramdata); 
                    $totalUnauthorizeFund = $totalUnauthorizeFundArr['total_txn_amount'] ; 
             
                    /*  
                     * We add All Load where is_virtual == yes
                     */ 
                    $paramdata['status'] = '';
                    $CorpLoadArr = $RatLoadModel->getAgentTotalVirtualLoad($paramdata); 
                    $CorpLoad = (isset($CorpLoadArr['total_agent_load_amount']))? $CorpLoadArr['total_agent_load_amount']: 0;
             
                    /*
                     * We get all debit of virtual balance from `rat_debit_detail`
                     */ 
                    $paramdata['debit_api_cr'] = PAYABLE_AC;
                    $agentDebitArr = $RatLoadModel->getAgentVirtualDebits($paramdata);
                    $debitPay = ($agentDebitArr['total_agent_debit_amount'] != 0)? $agentDebitArr['total_agent_debit_amount']: 0;
             
                    $avbsReportData[$i][$j]['txn_date'] = $from->format('d-m-Y');
                    $avbsReportData[$i][$j]['bank_name'] = $bankDetails[0]['bank_name'];
                    $avbsReportData[$i][$j]['total_Authorize_fund'] = $totalAuthorizeFund;
                    $avbsReportData[$i][$j]['total_Unauthorize_fund'] = $totalUnauthorizeFund;
                    $avbsReportData[$i][$j]['card_load'] = $CorpLoad; 
                    $avbsReportData[$i][$j]['debit_payable'] = $debitPay ;  
                }
                $i++;
            }
            return $avbsReportData;
        } else{
            return array();
        }
    }
    
    
    public function getVirtualWalletBalance($param) { 
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['2'];
        } 
        if($param['bank_unicode'] == $bankUnicodeArr['2']) {
            $objCardload = new Corp_Ratnakar_Cardload();
            $detailsArr = $objCardload->getvirtualWalletbalance($param); 
        } else {
            $detailsArr = array();
        }
        return $detailsArr;
    }
    
    
    public function exportGetVirtualWalletbalance($param) {
        $detailsArr = array();
        $objCardload = new Corp_Ratnakar_Cardload();
        $detailsArr = $objCardload->exportGetVirtualWalletbalance($param);
        return $detailsArr;
        
    }
    
    /* 
     * exportAgentWiseLoadsFromAgent function will find data for agent wise load report. 
     * it will accept param array with query filters e.g.. duration and agent id
     */

    public function exportw2wtransferdata($param) {
	$objReports = new Remit_Ratnakar_WalletTransfer();
	$data = $objReports->getListWalletTranfer($param);
        $retData = array();
        if (!empty($data)) {
            foreach ($data as $key => $data) {
                $retData[$key]['bank_name'] = $data['bank_name']; 
		$retData[$key]['product_name'] = $data['product_name'];
		$retData[$key]['agent_code'] = $data['agent_code']; 
		$retData[$key]['sender_name'] = $data['sender_name']; 
		$retData[$key]['sender_mobile'] = $data['sender_mobile']; 
		$retData[$key]['receiver_name'] = $data['receiver_name']; 
		$retData[$key]['recieve_mobile'] = $data['recieve_mobile']; 
		$retData[$key]['date_created'] = $data['date_created']; 
		$retData[$key]['amount'] = Util::numberFormat($data['amount']); 
		$retData[$key]['txnrefnum'] = $data['txnrefnum']; 
		$retData[$key]['txn_type'] = $data['txn_type']; 
		$retData[$key]['status'] = ucfirst($data['status']);
		$retData[$key]['txn_code'] = $data['txn_code']; 
		$retData[$key]['block_amount'] = Util::numberFormat($data['block_amount']); 
            }
        }
        return $retData;
    }
  

}

