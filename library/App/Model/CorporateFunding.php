<?php

class CorporateFunding extends App_Model {

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
    protected $_name = DbTable::TABLE_CORPORATE_FUNDING;

    public function addCorporateFunding($param) {

        try {
            //print_r($param); exit;
            $this->save($param);
            return true;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception($e->getMessage());
            return false;
        }
    }

    function markPendingAgentFundingToDuplicate() {
        $agentFundings = $this->fetchAll("status='" . STATUS_PENDING . "'");
        $duplicate = 0;

        foreach ($agentFundings as $agentFunding) {
            $fundingId = '';
            $fundingNo = '';
            $fundingAmount = '';
            
            $fundingId = $this->_db->quote($agentFunding->id);
            $condition = " id <> " . $fundingId;
            if (!empty($agentFunding->funding_no)) {
                //$fundingNo = $this->_db->quote($agentFunding->funding_no);
                $fundingNo = $agentFunding->funding_no;
                $condition.=" AND funding_no='" . $fundingNo . "'";
            }
            //$fundingAmount = $this->_db->quote($agentFunding->amount);
            $fundingAmount = $agentFunding->amount;
            $condition.=" AND amount='" . $fundingAmount . "'";
            $condition.=" AND (status='" . STATUS_APPROVED . "'";
            $condition.=" OR  status='" . STATUS_DUPLICATE . "'";
            $condition.=" OR  status='" . STATUS_REJECTED . "'";
            $condition.=")";
            if ($this->isDuplicate($condition)) {
                $agentFunding->status = STATUS_DUPLICATE;
                $agentFunding->save();
                $duplicate++;
            }
        }
        $msg = "pending age funding to duplicate=$duplicate ";
        return $msg;
    }

    function isDuplicate($condition) {
        $select = $this->_db->select()
                ->from(DbTable::TABLE_AGENT_FUNDING, array('id'))
                ->where($condition);
        $row = $this->_db->fetchRow($select);
        if (!empty($row))
            return TRUE;
        else
            return FALSE;
    }

    function findAgentFundingForBankStatementAndDoSettled($statement) {
        $condition = '';
        $msg = '';
        $agentFundings = array();
        if (!empty($statement->funding_no)) {
            $condition.=" funding_no='" . $statement->funding_no . "' AND ";


            $condition.=" amount='" . $statement->amount . "'";
            $condition.=" AND status='" . STATUS_PENDING . "'";
            $query = $this->select()
                    ->where($condition)
                    ->limit(AGENT_FUNDING_FETCH_DATA_LIMIT);
            $agentFundings = $this->fetchAll($query);
        }


        if (!empty($agentFundings)) {
            $i = 0;
            foreach ($agentFundings as $agentFunding) {
                if ($i < 1) {//If find more then 1 thne only 1 request will be approved
                    $this->settledFundRequest($agentFunding, $statement);
                }
                $i++;
            }
        }
        return $msg;
    }

    public function settledFundRequest($agentFunding, $statement, $settlement_by = BY_SYSTEM, $by_ops_id = TXN_OPS_ID, $settlement_remarks = '', $fundId = 0) {

        $msg = '';
        $this->_db->beginTransaction();
        try {
            $updTime = new Zend_Db_Expr('NOW()');
            if ($settlement_by == BY_OPS) {
                $agentFunding->settlement_by = $settlement_by;

                $agentFunding->settlement_by_ops_id = $by_ops_id;
                $agentFunding->settlement_remarks = $settlement_remarks;
                $agentFunding->settlement_ip_ops = @$this->formatIpAddress(Util::getIP());
            } else {
                $agentFunding->settlement_by = BY_SYSTEM;
            }

            $agentFunding->status = STATUS_APPROVED;

            $agentFunding->settlement_date = $updTime;

            //After save agent funding update bank statement unsettled to settled 
            //If Agent funding status is approved 
            if ($agentFunding->status == STATUS_APPROVED && !is_null($statement)) {
                //First Save Bank Statement because we are saving its id in Agent Funding 
                $statement = BankStatement::settledBankStatement($statement, $updTime);
                $agentFunding->bank_statement_id = $statement->id;
            }
            
            if ($settlement_by == BY_OPS) {
                    $arrAgentFunding = Util::toArray($agentFunding);
                    if($this->update($arrAgentFunding, "id=".$fundId." AND status = '".STATUS_PENDING."'")) {
                        //Now set Prams for BaseTxn
                        $baseTxnParams['ops_id'] = $by_ops_id;
                        $baseTxnParams['corporate_id'] = $agentFunding->corporate_id;
                        $baseTxnParams['amount'] = $agentFunding->amount;
                        $baseTxnParams['txn_type'] = TXNTYPE_CORPORATE_FUND_LOAD;
                        $baseTxnParams['corporate_funding_id'] = $agentFunding->id;
                        $baseTxn = new BaseTxn();
                        $baseTxn->opsToCorporate($baseTxnParams); //Save BaseTxn
                        $msg = 'success';
                    } else {
                        $msg = 'Fund Request already processed';
                    }
            } else {
            //Now Save Agent Funding
                if ($agentFunding->save()) {
                    //Now set Prams for BaseTxn
                    $baseTxnParams['ops_id'] = $by_ops_id;
                    $baseTxnParams['corporate_id'] = $agentFunding->corporate_id;
                    $baseTxnParams['amount'] = $agentFunding->amount;
                    $baseTxnParams['txn_type'] = TXNTYPE_CORPORATE_FUND_LOAD;
                    $baseTxnParams['corporate_funding_id'] = $agentFunding->id;
                    $baseTxn = new BaseTxn();
                    $baseTxn->opsToCorporate($baseTxnParams); //Save BaseTxn
                    
                    $msg = 'success';
                } else {
                    $msg = 'System Failure';
                }
            }
            $this->_db->commit();
            return $msg;
            
        } catch (Exception $e) {
            $this->_db->rollBack();
            throw $e;
        }
    }

    public function rejectFundRequest($agentFunding, $by_ops_id, $settlement_remarks = '') {

        $this->_db->beginTransaction();
        try {
            $updTime = new Zend_Db_Expr('NOW()');
            $agentFunding->settlement_by = BY_OPS;
            $agentFunding->settlement_by_ops_id = $by_ops_id;
            $agentFunding->settlement_remarks = $settlement_remarks;
            $agentFunding->settlement_ip_ops = $this->formatIpAddress(Util::getIP());
            $agentFunding->status = STATUS_REJECTED;
            $agentFunding->settlement_date = $updTime;
            $agentFunding->save(); //Now Save Agent Funding
            $this->_db->commit();
        } catch (Exception $e) {
            $this->_db->rollBack();
            throw $e;
        }
    }

    public function findAllPendingRequest($page = 1, $paginate = NULL) {
        
         $details = $this->sqlFindAllPendingRequest();
         return $this->_paginate($details, $page, $paginate);
        
    }

    
    private function sqlFindAllPendingRequest(){
        $details = $this->select()
                ->from($this->_name . " as af", array("af.id", "af.amount", "af.funding_no", "af.fund_transfer_type_id", "af.funding_details", "af.date_request", "af.status as agent_funding_status", "af.settlement_by", "af.settlement_remarks", "af.settlement_date"))
                ->setIntegrityCheck(false)
                ->joinleft(DbTable::TABLE_FUND_TRANSFER_TYPE . " as ftt", "ftt.id=af.fund_transfer_type_id", array('ftt.name as transfer_type_name'))
                ->joinleft(DbTable::TABLE_CORPORATE_USER . " as a", "a.id=af.corporate_id", array('af.corporate_id', 'corporate_code', "CONCAT_WS(' ',a.first_name,'' ,a.last_name) as agent_name"))
                ->where("af.status='" . STATUS_PENDING . "'")
                ->order('date_request DESC');
        return $details;
    }
    
    public function exportAllPendingRequest(){
        $retData = array();
        $details = $this->sqlFindAllPendingRequest();
        $data = $this->fetchAll($details);
        
        if(!empty($data))
        { 
            foreach($data as $key=>$data){
                    $retData[$key]['agent_code']          = $data->corporate_code;
                    $retData[$key]['agent_name']          = $data->agent_name;
                    $retData[$key]['amount']              = $data->amount;
                    $retData[$key]['transfer_type_name']  = $data->transfer_type_name;
                    $retData[$key]['funding_no']          = $data->funding_no;
                    $retData[$key]['date_request']        = $data->date_request;
                       
                   
          }
        }
        return $retData;
    }
    public function getCorporateFundingById($id) {
        $details = $this->select()
                ->from($this->_name . " as af", array("af.id as corporate_funding_id", "af.amount", "af.funding_no",
                    "af.fund_transfer_type_id", "af.funding_details",
                    "af.date_request", "af.status as corporate_funding_status",
                    "af.settlement_by", "af.settlement_remarks", "af.settlement_date",
                    "af.comments"
                        )
                )
                ->setIntegrityCheck(false)
                ->joinleft(DbTable::TABLE_FUND_TRANSFER_TYPE . " as ftt", "ftt.id=af.fund_transfer_type_id", array('ftt.name as transfer_type_name'))
                ->joinleft(DbTable::TABLE_CORPORATE_USER . " as a", "a.id=af.corporate_id", array('af.corporate_id', 'corporate_code', "CONCAT_WS(' ',a.first_name, '',a.last_name) as corporate_name"))
                ->where("af.id=?", $id)
                ->where("af.status='" . STATUS_PENDING . "'");


        return $this->fetchRow($details);
    }

    public function getNonApprovedAgentFundingId($id) {
        $details = $this->select()
                ->from($this->_name)
                ->where("id=?", $id)
                ->where('status = "' . STATUS_PENDING . '" OR status = "' . STATUS_REJECTED . '" OR status = "' . STATUS_DUPLICATE . '"');
        return $this->fetchRow($details);
    }

    public function findAllFundRequest($page = 1, $paginate = NULL) {
        $query = $this->select()
                ->from($this->_name)
                ->orwhere('status = ?', STATUS_PENDING)
                ->orWhere('status = ?', STATUS_REJECTED)
                ->orWhere('status = ?', STATUS_DUPLICATE)
                ->orWhere('status = ?', STATUS_APPROVED);
        return $this->_paginate($query, $page, $paginate);
    }

    public function chkCorporateMinMaxLoad($param) {
        $agBalValid = new Validator_AgentBalance();
        $param['section_id'] = CORPORATE_SECTION_SETTING_ID;
        $minmax = $agBalValid->chkAgentMaxMinLoad($param, $returnValues = TRUE);
        if ($param['amount'] < $minmax['minValue'] || $param['amount'] > $minmax['maxValue']) {
            return $minmax;
        } else {
            return FALSE;
        }
    }

    public function findAllFundRequestByCorporateId($agent_id, $page = 1, $paginate = NULL) {
        $query = $this->select()
                ->from($this->_name)
                ->where('corporate_id = ?', $agent_id)
                ->where('status = "' . STATUS_PENDING . '" OR status = "' . STATUS_REJECTED . '" OR status = "' . STATUS_DUPLICATE . '"  OR status = "' . STATUS_APPROVED . '"')
                ->order('date_request DESC');
        return $this->_paginate($query, $page, $paginate);
    }

    public function getAgentFundingByAgentId($agent_id, $id) {
        $details = $this->select()
                ->from($this->_name . " as af", array("af.id", "af.amount", "af.funding_no", "af.fund_transfer_type_id", "af.funding_details", "af.date_request", "af.status as agent_funding_status", "af.settlement_by", "af.settlement_remarks", "af.settlement_date"))
                ->setIntegrityCheck(false)
                ->joinleft(DbTable::TABLE_FUND_TRANSFER_TYPE . " as ftt", "ftt.id=af.fund_transfer_type_id", array('ftt.name as transfer_type_name'))
                ->joinleft(DbTable::TABLE_AGENTS . " as a", "a.id=af.agent_id", array('af.agent_id', 'agent_code', "CONCAT_WS(' ',a.first_name, a.middle_name,a.last_name) as agent_name"))
                ->where("af.agent_id=?", $agent_id)
                ->where("af.id=?", $id);
        return $this->fetchRow($details);
    }

    public function getAllApprovedFundRequestWithSettledBankStatement($page, $paginate = NULL) {
        $query = $this->sqlAllApprovedFundRequestWithSettledBankStatement();
        return $this->_paginate($query, $page, $paginate);
    }
    
    private function sqlAllApprovedFundRequestWithSettledBankStatement(){
      $query = $this->select()
                ->from($this->_name . " as af", array("af.id", "af.amount", "af.funding_no", "af.fund_transfer_type_id", "af.funding_details", "af.date_request", "af.status as corporate_funding_status", "af.settlement_by", "af.settlement_remarks", "af.settlement_date"))
                ->setIntegrityCheck(false)
                ->joinleft(DbTable::TABLE_FUND_TRANSFER_TYPE . " as ftt", "ftt.id=af.fund_transfer_type_id", array('ftt.name as transfer_type_name'))
                ->joinleft(DbTable::TABLE_CORPORATE_USER . " as a", "a.id=af.corporate_id", array('af.corporate_id', 'corporate_code', "CONCAT_WS(' ',a.first_name, '',a.last_name) as corporate_name"))
                ->where("(af.status='" . STATUS_APPROVED . "' OR af.status='" . STATUS_REJECTED . "')");
      
      return $query;
     }
     
      public function exportAllApprovedFundRequestWithSettledBankStatement(){
        $retData = array();
        $details = $this->sqlAllApprovedFundRequestWithSettledBankStatement();
        $data = $this->fetchAll($details);
        
        if(!empty($data))
        {  

            foreach($data as $key=>$data){
                    $retData[$key]['agent_code']          = $data->agent_code;
                    $retData[$key]['agent_name']          = $data->agent_name;
                    $retData[$key]['transfer_type_name']  = $data->transfer_type_name;
                    $retData[$key]['funding_no']          = $data->funding_no;
                    $retData[$key]['amount']              = $data->amount;
                    $retData[$key]['funding_details']     = $data->funding_details;
                    $retData[$key]['settlement_by']       = $data->settlement_by;
                    $retData[$key]['settlement_remarks']  = $data->settlement_remarks;
                    $retData[$key]['date_request']        = $data->date_request;
                    $retData[$key]['settlement_date']     = $data->settlement_date;
                    $retData[$key]['agent_funding_status']= $data->agent_funding_status;
                       
                   
          }
        }
        return $retData;
    }
    public function getAgentTotalFund($param, $onDate = FALSE) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        if ($agentId > 0) {

            $select = $this->select()
                    ->from($this->_name, array('sum(amount) as total_agent_funding_amount'))
                    ->where('agent_id = ?', $agentId)
                    ->where('status = ?', STATUS_APPROVED);
            if ($onDate) {
                $date = isset($param['date']) ? $param['date'] : '';
                $select->where('DATE(settlement_date) =?', $date);
            } else {
                $fromDate = isset($param['from']) ? $param['from'] : '';
                $toDate = isset($param['to']) ? $param['to'] : '';
                $select->where('settlement_date >= ?', $fromDate);
                $select->where('settlement_date <= ?', $toDate);
            }
//            echo $select."<br/><br/>"; 

            return $this->fetchRow($select);
        }
        else
            return 0;
    }
    
    /* totalpendingAgentFundRequests function will return the SUM of Agent Pending Fund Requests report 
     * it will accept the duration as in param array
     */
    
    public function totalPendingAgentFundRequests($param, $page = 1, $paginate = NULL){
         $agentId = isset($param['agent_id'])?$param['agent_id']:'';
        
        $select =   $this->select();
        $select->from($this->_name,array('sum(amount) as total_agent_pending_funding_amount' ));
        if($agentId > 0){                                                           
           $select->where('agent_id=?',$agentId);
        }    
        
        $select->where('status =?',FLAG_PENDING);
        $select->where('date_request >=?',$param['from']); 
        $select->where('date_request <=?',$param['to']); 
        $select->group('DATE(date_request)')    ;
//        echo $select."<br/><br/>";
                
        return $this->_db->fetchRow($select);
    }
    
/* pendingAgentFundRequests function will return query for Agent Pending Fund Requests report 
     * it will accept the duration as in param array
     */
    
    public function pendingAgentFundRequests($param){
        $finalArr = array();
        $agentId = isset($param['agent_id'])?$param['agent_id']:'';
         //$agentIdCond = 0;
        $agentBinding = new BindAgentProductCommission();
        $select =   $this->_db->select();
//        $select->setIntegrityCheck(false);  
//        $select->from(DbTable::TABLE_AGENT_FUND_REQUEST.' as afr',array('afr.id','afr.agent_id','afr.amt','afr.fund_transfer_type_id','afr.comments','afr.request_status','DATE(afr.datetime_request) as datetime_request'));
        $select->from(DbTable::TABLE_AGENT_FUNDING.' as afr',array('afr.id','afr.agent_id','afr.amount','afr.fund_transfer_type_id','afr.comments','afr.status','DATE(afr.settlement_date) as settlement_date'));
        $select->joinLeft(DbTable::TABLE_FUND_TRANSFER_TYPE.' as fr', "afr.fund_transfer_type_id=fr.id", array('fr.name as fund_name'));
        $select->joinLeft(DbTable::TABLE_AGENTS.' as a', "afr.agent_id=a.id", array('a.agent_code', 'concat(a.first_name," ",a.last_name) as agent_name'));
        
        if($agentId > 0){                                                           
           $select->where('afr.agent_id=?',$agentId);
        }    
        
        $select->where('afr.status =?',FLAG_PENDING);
        $select->where('afr.settlement_date >=?',$param['from']); 
        $select->where('afr.settlement_date <=?',$param['to']); 
            
        $select->order('agent_name ASC');
        $select->order('afr.settlement_date ASC');
//        echo $select;exit;
        $requestArr = $this->_db->fetchAll($select);  
//        echo '<pre>';print_r($requestArr);
        foreach($requestArr as $val){
            
            $bindArr[] = $agentBinding->getAgentProductAndBank($val['agent_id']);
        }
        for($i=0; $i<count($requestArr);$i++) {
           
           $finalArr[] = array_merge($requestArr[$i],$bindArr[$i]);
       }        
        return $finalArr;  
    }
    
    
    public function getAgentFunds($param, $onDate = FALSE) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        if ($agentId > 0) {

            $select = $this->_db->select()
                    ->from($this->_name. " as a", array('amount', 'settlement_date', 'comments'))
                    ->joinLeft(DbTable::TABLE_TXN_AGENT. " AS b", "a.id = b.agent_funding_id", array('txn_code'))              
                    ->where('a.agent_id = ?', $agentId)
                    ->where('a.status = ?', STATUS_APPROVED);
            if ($onDate) {
                $date = isset($param['date']) ? $param['date'] : '';
                $select->where('DATE(settlement_date) =?', $date);
            } else {
                $fromDate = isset($param['from']) ? $param['from'] : '';
                $toDate = isset($param['to']) ? $param['to'] : '';
                $select->where('settlement_date >= ?', $fromDate);
                $select->where('settlement_date <= ?', $toDate);
            }
   
            return $this->_db->fetchAll($select);
        }
        else
            return 0;
    }
    public function corporateFundingRecords($param){
        $fromDate = isset($param['from']) ? $param['from'] : '';
        $toDate = isset($param['to']) ? $param['to'] : '';
        $query = $this->_db->select()
                ->from($this->_name . " as af", array("af.id", "af.amount", "af.funding_no", "af.fund_transfer_type_id", "af.funding_details", "af.date_request", "af.status as agent_funding_status", "af.settlement_by", "af.settlement_remarks", "af.settlement_date"))
                ->joinleft(DbTable::TABLE_FUND_TRANSFER_TYPE . " as ftt", "ftt.id=af.fund_transfer_type_id", array('ftt.name as transfer_type_name'))
                ->joinleft(DbTable::TABLE_CORPORATE_USER . " as a", "a.id=af.corporate_id", array('af.corporate_id', 'corporate_code', "CONCAT_WS(' ',a.first_name, '',a.last_name) as corporate_name"))
//                ->where("af.status =?", STATUS_APPROVED )
                ->where("af.corporate_id =?" , $param['corporate_id']);
                
                $query->where('af.settlement_date >= ?', $fromDate);
                $query->where('af.settlement_date <= ?', $toDate);
     
     //echo $query; exit;
        $res = $this->_db->fetchAll($query);
        return $res;
    }
    
    public function getCorporateFunding($param , $corporateId = 0){
        $fundTransferModel = new CorporateFundTransfer();
        $cardLoadModel = new Corp_Ratnakar_Cardload();
        
        $user = Zend_Auth::getInstance()->getIdentity();
        $userModel = new CorporateUser();
        $agentProduct = $userModel->getCorporateBinding($user->id);
        $dataArr = array();
        $i = 0;
        
        foreach ($param as $queryDate) {
        
        
        $fundArr = array(
                         'corporate_id' => $corporateId,
                         'product_id' => $agentProduct['product_id'],
                         'to' => $queryDate['to'],
                         'from' => $queryDate['from'],
                         'txn_type' => TXNTYPE_AGENT_FUND_LOAD
        );
        $fundRequestDetails =  $this->corporateFundingRecords($fundArr);
         foreach($fundRequestDetails as  $fundReqDetails){
              $dataArr[$i]['date'] = $fundReqDetails['date_request'];
              $dataArr[$i]['transfer_type_name'] = $fundReqDetails['transfer_type_name'];
              $dataArr[$i]['funding_no'] = $fundReqDetails['funding_no'];
              $dataArr[$i]['amount'] = $fundReqDetails['amount'];
              $dataArr[$i]['status'] = $fundReqDetails['agent_funding_status'];
              $dataArr[$i]['remarks'] = $fundReqDetails['settlement_remarks'];
              $i++;
              
        }
        $trfrArr = array('corporate_id' => $corporateId,
                        'to' => $queryDate['to'],
                        'from' => $queryDate['from'],
                        'txn_type' => TXNTYPE_CORPORATE_TOCORPORATE_FUND_TRANSFER
           
        );
        
        
        $toAgentTrfer = $fundTransferModel->getCorporateFundsTransferDetails($trfrArr);
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
           
        $trfrArr['txn_type'] = TXNTYPE_CORPORATE_TOCORPORATE_FUND_REVERSAL;
        $ReversalAgentTrfer = $fundTransferModel->getCorporateFundsTransferDetails($trfrArr);
        foreach($ReversalAgentTrfer as $ReversalAgentTrfr){
           $dataArr[$i]['date'] = $ReversalAgentTrfr['date_created'];
           $dataArr[$i]['transfer_type_name'] = 'Fund Reversal to Agent';
           $dataArr[$i]['funding_no'] = $ReversalAgentTrfr['txn_code'];
           $dataArr[$i]['amount'] = $ReversalAgentTrfr['tr_amount'];
           $dataArr[$i]['status'] = $ReversalAgentTrfr['status'];
           $dataArr[$i]['remarks'] = '';
           $i++;
        }
        
        $trfrArr['txn_type'] = TXNTYPE_CORPORATE_TOCORPORATE_FUND_TRANSFER;
        $toSAgentTrfer = $fundTransferModel->getHeadCorporateFundsTransferDetails($trfrArr);
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
        $trfrArr['txn_type'] = TXNTYPE_CORPORATE_TOCORPORATE_FUND_REVERSAL;
        $ReversalSAgentTrfer = $fundTransferModel->getHeadCorporateFundsTransferDetails($trfrArr);
        foreach($ReversalSAgentTrfer as $ReversalSAgentTrfr){
           $dataArr[$i]['date'] = $ReversalSAgentTrfr['date_created'];
           $dataArr[$i]['transfer_type_name'] = 'Fund Reversal to Super Agent';
           $dataArr[$i]['funding_no'] = $ReversalSAgentTrfr['txn_code'];
           $dataArr[$i]['amount'] = $ReversalSAgentTrfr['tr_amount'];
           $dataArr[$i]['status'] = $ReversalSAgentTrfr['status'];
           $dataArr[$i]['remarks'] = '';
           $i++;
        }
         

        
     
      
        }
      // echo "<pre>";print_r($dataArr); exit;
        return $dataArr;
       
    }
    
 }