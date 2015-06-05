<?php

class AgentFunding extends App_Model {

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
    protected $_name = DbTable::TABLE_AGENT_FUNDING;

    public function addAgentFunding($param) {

        try {
            $this->save($param);
            return true;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception($e->getMessage());
            return false;
        }
    }


/*    public function markPendingAgentFundingToDuplicate() {
        $agentFundings = $this->fetchAll("status='" . STATUS_PENDING . "'");
        $duplicate = 0;

        foreach ($agentFundings as $agentFunding) {
            $fundingId = '';
            $fundingNo = '';
            $fundingAmount = '';
            
            $fundingId = $this->_db->quote($agentFunding->id);
            $condition = " id <> " . $fundingId;
            if (!empty($agentFunding->funding_no)) {
                $fundingNo = $this->_db->quote($agentFunding->funding_no);
                $condition.=" AND funding_no=" . $fundingNo ;
            }
            $fundingAmount = $this->_db->quote($agentFunding->amount);
            $condition.=" AND amount= " . $fundingAmount;
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
    }*/



    function markPendingAgentFundingToDuplicate() {
        $agentFundings = $this->fetchAll("status='" . STATUS_PENDING . "'");
        $duplicate = 0;
        $blank = 0 ;
        foreach ($agentFundings as $agentFunding) {
            if($agentFunding->agent_id == 0) {
                $agentFunding->status = STATUS_REJECTED;
                $agentFunding->save();
                $blank++;
            } else {
                $fundingId = '';
                $fundingNo = '';
                $fundingAmount = '';

                $fundingId = $this->_db->quote($agentFunding->id);
                $condition = " id <> " . $fundingId;
                if (!empty($agentFunding->funding_no)) {
                   // $fundingNo = $this->_db->quote($agentFunding->funding_no);

                    //$condition.=" AND funding_no=" . $fundingNo ;
                    $condition.=" AND funding_no='" . $agentFunding->funding_no. "'";
                }
                //$fundingAmount = $this->_db->quote($agentFunding->amount);
                $condition.=" AND agent_id > 0";
                $condition.=" AND amount='" . $agentFunding->amount . "'";
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
        }
        $msg = "funding duplicate=$duplicate, blank agent = $blank";
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
                        $baseTxnParams['agent_id'] = $agentFunding->agent_id;
                        $baseTxnParams['amount'] = $agentFunding->amount;
                        $baseTxnParams['txn_type'] = TXNTYPE_AGENT_FUND_LOAD;
                        $baseTxnParams['agent_funding_id'] = $agentFunding->id;
                        $baseTxn = new BaseTxn();
                        $baseTxn->opsToAgent($baseTxnParams); //Save BaseTxn
                        
                        $msg = 'success';
                    } else {
                        $msg = 'Fund Request already processed';
                    }
            } else {
            //Now Save Agent Funding
                if ($agentFunding->save()) {
                    //Now set Prams for BaseTxn
                    $baseTxnParams['ops_id'] = $by_ops_id;
                    $baseTxnParams['agent_id'] = $agentFunding->agent_id;
                    $baseTxnParams['amount'] = $agentFunding->amount;
                    $baseTxnParams['txn_type'] = TXNTYPE_AGENT_FUND_LOAD;
                    $baseTxnParams['agent_funding_id'] = $agentFunding->id;
                    $baseTxn = new BaseTxn();
                    $baseTxn->opsToAgent($baseTxnParams); //Save BaseTxn
                    
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
        $curdate = date("Y-m-d");   
        $details = $this->select()
                ->from($this->_name . " as af", array("af.id", "af.amount", "af.funding_no", "af.fund_transfer_type_id", "af.funding_details", "af.date_request", "af.status as agent_funding_status", "af.settlement_by", "af.settlement_remarks", "af.settlement_date"))
                ->setIntegrityCheck(false)
                ->joinleft(DbTable::TABLE_FUND_TRANSFER_TYPE . " as ftt", "ftt.id=af.fund_transfer_type_id", array('ftt.name as transfer_type_name'))
                ->joinleft(DbTable::TABLE_AGENTS . " as a", "a.id=af.agent_id", array('af.agent_id', 'agent_code', "CONCAT_WS(' ',a.first_name, a.middle_name,a.last_name) as agent_name"))
->joinLeft(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION." as bapc", "a.id=bapc.agent_id AND '".$curdate ."' >= bapc.date_start AND ('".$curdate."' <= bapc.date_end OR bapc.date_end = '0000-00-00' OR bapc.date_end is NULL)", array('bapc.plan_commission_id', 'bapc.product_id'))
                ->joinLeft(DbTable::TABLE_PRODUCTS." as p", "bapc.product_id=p.id", array("GROUP_CONCAT(p.name SEPARATOR ', ') AS product_name"))
                ->joinLeft(DbTable::TABLE_BANK." as b", "p.bank_id=b.id", array('b.name as bank_name'))
                ->where("af.status='" . STATUS_PENDING . "'")
                ->group("af.id")
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
                    $retData[$key]['bank_name']           = $data->bank_name;
                    $retData[$key]['product_name']        = $data->product_name;
                    $retData[$key]['agent_code']          = $data->agent_code;
                    $retData[$key]['agent_name']          = $data->agent_name;
                    $retData[$key]['amount']              = $data->amount;
                    $retData[$key]['transfer_type_name']  = $data->transfer_type_name;
                    $retData[$key]['funding_no']          = $data->funding_no;
                    $retData[$key]['date_request']        = $data->date_request;
                       
                   
          }
        }
        return $retData;
    }
    public function getAgentFundingById($id) {
        $details = $this->select()
                ->from($this->_name . " as af", array("af.id as agent_funding_id", "af.amount", "af.funding_no",
                    "af.fund_transfer_type_id", "af.funding_details",
                    "af.date_request", "af.status as agent_funding_status",
                    "af.settlement_by", "af.settlement_remarks", "af.settlement_date",
                    "af.comments"
                        )
                )
                ->setIntegrityCheck(false)
                ->joinleft(DbTable::TABLE_FUND_TRANSFER_TYPE . " as ftt", "ftt.id=af.fund_transfer_type_id", array('ftt.name as transfer_type_name'))
                ->joinleft(DbTable::TABLE_AGENTS . " as a", "a.id=af.agent_id", array('af.agent_id', 'agent_code', "CONCAT_WS(' ',a.first_name, a.middle_name,a.last_name) as agent_name"))
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

    public function chkAgentMinMaxLoad($param) {
        $agBalValid = new Validator_AgentBalance();
        $param['section_id'] = AGENT_SECTION_SETTING_ID;
        $minmax = $agBalValid->chkAgentMaxMinLoad($param, $returnValues = TRUE);
        if ($param['amount'] < $minmax['minValue'] || $param['amount'] > $minmax['maxValue']) {
            return $minmax;
        } else {
            return FALSE;
        }
    }

    public function findAllFundRequestByAgentId($agent_id, $page = 1, $paginate = NULL) {
        $query = $this->select()
                ->from($this->_name)
                ->where('agent_id = ?', $agent_id)
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
      $curdate = date("Y-m-d");   
      $query = $this->select()
                ->from($this->_name . " as af", array("af.id", "af.amount", "af.funding_no", "af.fund_transfer_type_id", "af.funding_details", "af.date_request", "af.status as agent_funding_status", "af.settlement_by", "af.settlement_remarks", "af.settlement_date"))
                ->setIntegrityCheck(false)
                ->joinleft(DbTable::TABLE_FUND_TRANSFER_TYPE . " as ftt", "ftt.id=af.fund_transfer_type_id", array('ftt.name as transfer_type_name'))
                ->joinleft(DbTable::TABLE_AGENTS . " as a", "a.id=af.agent_id", array('af.agent_id', 'agent_code', "CONCAT_WS(' ',a.first_name, a.middle_name,a.last_name) as agent_name"))
                ->joinLeft(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION." as bapc", "a.id=bapc.agent_id AND '".$curdate ."' >= bapc.date_start AND ('".$curdate."' <= bapc.date_end OR bapc.date_end = '0000-00-00' OR bapc.date_end is NULL)", array('bapc.plan_commission_id', 'bapc.product_id'))
                ->joinLeft(DbTable::TABLE_PRODUCTS." as p", "bapc.product_id=p.id", array("GROUP_CONCAT(p.name SEPARATOR ', ') AS product_name"))
                ->joinLeft(DbTable::TABLE_BANK." as b", "p.bank_id=b.id", array('b.name as bank_name'))
                ->where("(af.status='" . STATUS_APPROVED . "' OR af.status='" . STATUS_REJECTED . "')")
                ->group("af.id");

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
                    $retData[$key]['product_name']        = $data->product_name;
                    $retData[$key]['bank_name']           = $data->bank_name;
                       
          }
        }
        return $retData;
    }
    public function getAgentTotalFund($param, $onDate = FALSE) {
       
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $agentIdList = isset($param['agent_id_list']) ? $param['agent_id_list'] : '';
        if ($agentId > 0) {
            //Enable DB Slave
            $this->_enableDbSlave();
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

            $row = $this->fetchRow($select);
            //Disable DB Slave
            $this->_disableDbSlave();
            return $row;
        }else if($agentIdList != ''){
            //Enable DB Slave
            $this->_enableDbSlave();
            $fromDate = isset($param['from']) ? $param['from'] : '';
            $toDate = isset($param['to']) ? $param['to'] : '';
            $select = $this->select()
                    ->from($this->_name, array('sum(amount) as total_agent_funding_amount'))
                    ->where("agent_id IN ($agentIdList)")
                    ->where('status = ?', STATUS_APPROVED);
            $select->where('settlement_date >= ?', $fromDate);
            $select->where('settlement_date <= ?', $toDate);
            
            $row = $this->fetchRow($select);
            //Disable DB Slave
            $this->_disableDbSlave();
            return $row;
        }
        else{
            return 0;
        }
                    echo $select."<br/><br/>".exit; 
    }
    
    /* totalpendingAgentFundRequests function will return the SUM of Agent Pending Fund Requests report 
     * it will accept the duration as in param array
     */
    
    public function totalPendingAgentFundRequests($param, $page = 1, $paginate = NULL){
         $agentId = isset($param['agent_id'])?$param['agent_id']:'';
        
        //Enable DB Slave
        $this->_enableDbSlave();
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
                
        $row = $this->_db->fetchRow($select);
        //Disable DB Slave
        $this->_disableDbSlave();
        return $row;
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

      public function getAgentFunding($param){
        $fromDate = isset($param['from']) ? $param['from'] : '';
        $toDate = isset($param['to']) ? $param['to'] : '';
        $query = $this->_db->select()
                ->from($this->_name . " as af", array("af.id", "af.amount", "af.funding_no", "af.fund_transfer_type_id", "af.funding_details", "af.date_request", "af.status as agent_funding_status", "af.settlement_by", "af.settlement_remarks", "af.settlement_date"))
                ->joinleft(DbTable::TABLE_FUND_TRANSFER_TYPE . " as ftt", "ftt.id=af.fund_transfer_type_id", array('ftt.name as transfer_type_name'))
                ->joinleft(DbTable::TABLE_AGENTS . " as a", "a.id=af.agent_id", array('af.agent_id', 'agent_code', "CONCAT_WS(' ',a.first_name, a.middle_name,a.last_name) as agent_name"))
//                ->where("af.status =?", STATUS_APPROVED )
                ->where("af.agent_id =?" , $param['agent_id']);
                
                $query->where('af.settlement_date >= ?', $fromDate);
                $query->where('af.settlement_date <= ?', $toDate);
     
     
     $res = $this->_db->fetchAll($query);
     return $res;
    }
    
     public function findAgentFundingAndDoSettled() {
        $msg = '';
        $query = $this->select()
                ->from(DbTable::TABLE_AGENT_FUNDING_IPAY, array('*'))
                ->setIntegrityCheck(false)
                ->where("status='" . STATUS_PENDING . "'")
                ->limit(AGENT_FUNDING_FETCH_DATA_LIMIT);

        $agentFundings = $this->fetchAll($query);

        if (!empty($agentFundings)) {
            foreach ($agentFundings as $agentFunding) {
                $this->settledFundRequestIPay($agentFunding);
            }
        }
        return $msg;
    }
    
    public function settledFundRequestIPay($agentFunding) {
        $msg = '';
        $this->_db->beginTransaction();
        
        try{
            $agentFunding->status = STATUS_SUCCESS;
            $agentFunding->settlement_date = new Zend_Db_Expr('NOW()');
        
            $arrAgentFunding = Util::toArray($agentFunding);

            $data['agent_id'] = $arrAgentFunding['agent_id'];
            $data['amount'] = $arrAgentFunding['amount'];
            $data['fund_transfer_type_id'] = FUND_TRANSFER_TYPE_ID_NEFT;
            $data['mode'] = TXN_MODE_CR;
            $data['txn_type'] = TXNTYPE_AGENT_FUND_LOAD;
            $data['date_request'] = $arrAgentFunding['request_date'];
            $data['settlement_by'] = BY_API;
            $data['settlement_date'] = $arrAgentFunding['settlement_date'];
            $data['status'] = STATUS_APPROVED;

            $this->insert($data);
            $agentFundingId = $this->_db->lastInsertId(DbTable::TABLE_AGENT_FUNDING, 'id');

            if($agentFundingId > 0){
                if($this->_db->update(DbTable::TABLE_AGENT_FUNDING_IPAY, $arrAgentFunding, "id=".$agentFunding->id." AND status = '".STATUS_PENDING."'")) {
                
                    //Now set Prams for BaseTxn
                    $baseTxnParams['ops_id'] = TXN_OPS_ID;
                    $baseTxnParams['agent_id'] = $agentFunding->agent_id;
                    $baseTxnParams['amount'] = $agentFunding->amount;
                    $baseTxnParams['txn_type'] = TXNTYPE_AGENT_FUND_LOAD;
                    $baseTxnParams['agent_funding_id'] = $agentFundingId;
                    $baseTxn = new BaseTxn();
                    $txnCode = $baseTxn->getOpsToAgentTxnCode($baseTxnParams); //Save BaseTxn

                    $this->_db->update(DbTable::TABLE_AGENT_FUNDING, array('txn_code' => $txnCode), "id=".$agentFundingId);
                    $msg = 'success';
                }  else {
                    $msg = 'Fund Request already processed';
                }
            }
            $this->_db->commit();
            return $msg;
        } catch (Exception $e) {
            $this->_db->rollBack();
            throw $e;
        }
    }
    
    public function addAgentVirtualFunding($param) {
        $reqTime = new Zend_Db_Expr('NOW()');
        $agent_id = (isset($param['agent_id'])) ? $param['agent_id'] : 0;
        $amount = (isset($param['amount'])) ? $param['amount'] : 0;
        $comments = (isset($param['comments'])) ? $param['comments'] : '';
        $ip_agent = @$this->formatIpAddress(Util::getIP());
        $utr = (isset($param['utr'])) ? $param['utr'] : '';
        
        try {
            $virtual_fundingArr = array(
                'agent_id'      =>  $agent_id,
                'amount'        =>  $amount,
                'utr'           =>  $utr,
                'txn_type'      =>  TXNTYPE_AGENT_FUND_LOAD,
                'comments'      =>  $comments,
                'ip_agent'      =>  $ip_agent,
                'date_request'  =>  $reqTime,
                'by_ops_id'     =>  '',
                'ip_ops'        =>  '',
                'date_funded'   =>  '',
                'remarks'       =>  '',
                'status'        =>  STATUS_PENDING
            );
            $insertAVF = $this->_db->insert(DbTable::TABLE_AGENT_VIRTUAL_FUNDING,$virtual_fundingArr); 
            return true;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception($e->getMessage());
            return false;
        }
    }
    
    public function virtualFundRequestByAgentId($agent_id, $page = 1, $paginate = NULL) {

        $statusArr = array(
            STATUS_PENDING,
            STATUS_REJECTED,
            STATUS_DUPLICATE,
            STATUS_APPROVED
        );

        $query = $this->select();
        $query->from(DbTable::TABLE_AGENT_VIRTUAL_FUNDING, array(
            '*', 'DATE_FORMAT(date_request, "%d-%m-%Y %H:%i:%s") as format_date_request'
        ));
        $query->setIntegrityCheck(false);
        $query->where('agent_id = ?', $agent_id);
        $query->where('status IN (?)', $statusArr);
        $query->order('date_request DESC');

        return $this->_paginate($query, $page, $paginate);
    }
     
    public function getVirtualFundRequestById ($agent_id, $id) {
        
        $statusArr = array(
            STATUS_PENDING,
            STATUS_REJECTED,
            STATUS_DUPLICATE,
            STATUS_APPROVED
        );
        
        $sql = $this->select();
        $sql->from(
                DbTable::TABLE_AGENT_VIRTUAL_FUNDING . " as avf", 
                array(
                    'avf.id',
                    'avf.agent_id',
                    'avf.amount',
                    'avf.utr',
                    'avf.txn_type',
                    'avf.txn_code',
                    'avf.comments', 
                    'avf.remarks',
                    'avf.status',
                    'DATE_FORMAT(avf.date_request, "%d-%m-%Y %H:%i:%s") as format_date_request',
                    'DATE_FORMAT(avf.date_funded, "%d-%m-%Y %H:%i:%s") as format_date_funded'
        ));
        $sql->setIntegrityCheck(false);
        $sql->joinleft(
                DbTable::TABLE_AGENTS . " as a", 
                "a.id=avf.agent_id", 
                array(
                    'agent_code',
                    "CONCAT_WS(' ',a.first_name, a.middle_name,a.last_name) as agent_name"
        ));
        $sql->where('avf.agent_id = ?', $agent_id);
        $sql->where('avf.id = ?', $id);
        $sql->where('avf.status IN (?)', $statusArr); 
        return $this->fetchRow($sql);
    }
    
    
    public function virtualFundRequestsPending ($page = 1, $paginate = NULL) {
        $sql = $this->virtualFundRequestsPendingSQL();
        return $this->_paginate($sql, $page, $paginate);
    }
    
    public function virtualFundRequestsPendingSQL () {
        $sql = $this->select();
        $sql->from(
                DbTable::TABLE_AGENT_VIRTUAL_FUNDING . " as avf", 
                array(
                    'avf.id',
                    'avf.agent_id',
                    'avf.amount', 
                    'avf.utr',
                    'DATE_FORMAT(avf.date_request, "%d-%m-%Y %H:%i:%s") as format_date_request',
        ));
        $sql->setIntegrityCheck(false);
        $sql->joinleft(
                DbTable::TABLE_AGENTS . " as a", 
                "a.id=avf.agent_id", 
                array(
                    'agent_code',
                    "CONCAT_WS(' ',a.first_name, a.middle_name,a.last_name) as agent_name"
        )); 
        $sql->where('avf.status = ?', STATUS_PENDING); 
        $sql->order('date_request DESC');
        return $sql;
    }
    
    public function exportPendingVirtualFundRequest(){
        $retData = array();
        $details = $this->virtualFundRequestsPendingSQL();
        $data = $this->fetchAll($details); 
        if(!empty($data)) {
            foreach($data as $key=>$data){
                $retData[$key]['format_date_request']=   $data->format_date_request; 
                $retData[$key]['agent_code']        =   $data->agent_code; 
                $retData[$key]['agent_name']        =   $data->agent_name; 
                $retData[$key]['amount']            =   $data->amount; 
                $retData[$key]['utr']               =   $data->utr;
            }
        }
        return $retData;
    }
    
    public function pendingVirtualFundRequestById ($param) {
        
        $agent_id = isset($param['agent_id']) ? $param['agent_id'] : '';
        $id = isset($param['id']) ? $param['id'] : ''; 
        
        $sql = $this->select();
        $sql->from(
                DbTable::TABLE_AGENT_VIRTUAL_FUNDING . " as avf", 
                array(
                    'avf.id',
                    'avf.agent_id',
                    'avf.amount',
                    'avf.utr',
                    'avf.txn_type',
                    'avf.txn_code',
                    'avf.comments', 
                    'avf.remarks',
                    'avf.status',
                    'DATE_FORMAT(avf.date_request, "%d-%m-%Y %H:%i:%s") as format_date_request',
                    'DATE_FORMAT(avf.date_funded, "%d-%m-%Y %H:%i:%s") as format_date_funded'
        ));
        $sql->setIntegrityCheck(false);
        $sql->joinleft(
                DbTable::TABLE_AGENTS . " as a", 
                "a.id=avf.agent_id", 
                array(
                    'agent_code',
                    "CONCAT_WS(' ',a.first_name, a.middle_name,a.last_name) as agent_name"
        ));
        if($agent_id != ''){
            $sql->where('avf.agent_id = ?', $agent_id);
        }
        if($id != ''){
            $sql->where('avf.id = ?', $id);
        } 
        $sql->where('avf.status = ?', STATUS_PENDING); 
        return $this->fetchRow($sql);
    }
    
    public function rejectVirtualFundRequest ($param) { 
        try {
            $agent_funding_id = (isset($param['agent_funding_id'])) ? $param['agent_funding_id'] : 0;
            $remarks = (isset($param['remarks'])) ? $param['remarks'] : ''; 
            $ip_ops = @$this->formatIpAddress(Util::getIP());
            $user = Zend_Auth::getInstance()->getIdentity();
            $updateArr = array(
                'status'        =>  STATUS_REJECTED,
                'by_ops_id'     =>  $user->id,
                'ip_ops'        =>  $ip_ops,
                'remarks'       =>  $remarks
            );
            $update = $this->_db->update(DbTable::TABLE_AGENT_VIRTUAL_FUNDING, $updateArr, "id = $agent_funding_id");
            if($update){
                return FLAG_SUCCESS;
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }  
    }
    
    public function approveVirtualFundRequest ($param) { 
        try {
            $fundTime = new Zend_Db_Expr('NOW()');
            $remarks = (isset($param['remarks'])) ? $param['remarks'] : ''; 
            $ip_ops = @$this->formatIpAddress(Util::getIP());
            $user = Zend_Auth::getInstance()->getIdentity();
            $data['ops_id'] = $user->id;
            $data['agent_id'] = $param['agent_id'];
            $data['amount'] =  $param['amount'];
            $data['txn_type'] =  $param['txn_type'];
            $agent_funding_id = (isset($param['agent_funding_id'])) ? $param['agent_funding_id'] : 0;
            $objBTxn = new BaseTxn();
            $rtrnTxnCode = $objBTxn->opsToAgentVirtual($data);
            if($rtrnTxnCode){
                $updateArr = array(
                    'txn_code'      =>  $rtrnTxnCode,
                    'by_ops_id'     =>  $user->id,
                    'ip_ops'        =>  $ip_ops,
                    'remarks'       =>  $remarks,
                    'status'        =>  STATUS_APPROVED,
                    'date_funded'   =>  $fundTime
                );
                $update = $this->_db->update(DbTable::TABLE_AGENT_VIRTUAL_FUNDING, $updateArr, "id = $agent_funding_id");
                if($update){
                    return FLAG_SUCCESS;
                }
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    } 
}
