<?php

class FundRequest extends BaseUser
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
    protected $_name = DbTable::TABLE_AGENT_FUND_REQUEST;
    
    private $_msg;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_CardholderUser';
       
    public function chkAgentMinMaxLoad($param){
            $agBalValid = new Validator_AgentBalance();
            $param['section_id'] = AGENT_SECTION_SETTING_ID;
            $param['amount'] = $param['amt'];
            $minmax = $agBalValid->chkAgentMaxMinLoad($param , $returnValues = TRUE);

            if ($param['amt'] < $minmax['minValue'] || $param['amt'] > $minmax['maxValue'])
            {
                $agentsModel = new Agents();
                $row = $agentsModel->findById($param['agent_id']);
                $mailArray = array(
                    'email' =>$row['email'],
                    'name' =>ucfirst($row['first_name']).' '.ucfirst($row['last_name']),
                    'min' =>$minmax['minValue'],
                    'max' =>$minmax['maxValue'],
                    'amt' => $param['amt']
                    );
                $m = new App\Messaging\MVC\Axis\Agent();
                $m->agentMinMaxLoad($mailArray);
               return $minmax;
            }
          else { 
              return FALSE;
          }
   
    }
    public function addFundRequest($param){
  
 
   
        try
        {
           $validFundAmount = true;//
           
           if($validFundAmount){
               $this->save($param);
           }
           return true;
               
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception($e->getMessage());
            return false;
        }
    }   
    
    

 public function getAgentFundRequests($agentId='',  $page = 1, $paginate = NULL){ 
        
        $select =   $this->select();
        $select->setIntegrityCheck(false);
        $select->from(DbTable::TABLE_AGENT_FUND_REQUEST.' as afr',array('afr.*','afr.amt',"DATE_FORMAT(afr.datetime_request, '%d-%m-%Y %h:%i:%s') as datetime_request"));
        $select->joinLeft(DbTable::TABLE_AGENTS.' as a',"afr.agent_id=a.id", array('a.agent_code', 'concat(a.first_name," ",a.last_name) as name', 'a.mobile1'));
        
            if($agentId>0){                                                           
               $select->where('afr.agent_id=?',$agentId);
            }      
            
        $select->order('afr.datetime_request DESC');
        //echo $select->__toString();exit;        
        
        return $this->_paginate($select, $page, $paginate);       
        
    }

 
     public function getAgentFundResponse($requestId, $page = 1, $paginate = NULL){
          if($requestId<1)
            throw new Exception('No agent request id received.');
          
                    $select =  $this->select()       
                    ->setIntegrityCheck(false)
                    ->from(DbTable::TABLE_AGENT_FUND_RESPONSE.' as afr',array('afr.*',"DATE_FORMAT(afr.datetime_response, '%d-%m-%Y %h:%i:%s') as datetime_response"))        
                    ->joinLeft(DbTable::TABLE_OPERATION_USERS.' as ou',"afr.by_ops_id	=ou.id",array('username'))
                    ->joinLeft(DbTable::TABLE_AGENT_FUND_REQUEST.' as afrt',"afr.agent_fund_request_id = afrt.id",array('amt', 'agent_id','request_status'))
                    ->joinLeft(DbTable::TABLE_AGENTS.' as a',"afrt.agent_id = a.id", array('concat(a.first_name," ",a.last_name) as agent_name'))
                    ->where('agent_fund_request_id=?',$requestId)
                    ->order('datetime_response DESC');        
        
        //echo $select->__toString();exit;        
        $reqInfo = $this->getFundRequestInfo($requestId);        
        $request_status = isset($reqInfo->request_status)?$reqInfo->request_status:'';
        
        $session = new Zend_Session_Namespace('App.Operation.Controller'); 
        $session->request_status = $request_status;        
                    
        return $this->_paginate($select, $page, $paginate);       
     }
    
    

public function getFundRequestInfo($requestId){        
          if($requestId<1)
               throw new Exception('No agent request id received.');
                      
            $select =  $this->select()       
            ->setIntegrityCheck(false)
            ->from(DbTable::TABLE_AGENT_FUND_REQUEST.' as afr')        
            ->joinLeft(DbTable::TABLE_AGENTS.' as a',"afr.agent_id = a.id", array('concat(a.first_name," ",a.last_name) as agent_name', 'email', 'agent_code', 'mobile1'))     
            ->joinLeft(DbTable::TABLE_FUND_TRANSFER_TYPE.' as ftt',"afr.fund_transfer_type_id = ftt.id", array('name as fund_transfer_type'))     
       ->where('afr.id=?',$requestId);
        
        return $this->fetchRow($select);     
    }
 
    
    public function updateFundRequest($param){
        
        //$formData['agent_fund_request_id']
        $reqInfo = $this->getFundRequestInfo($param['agent_fund_request_id']);
        $agentbalance = new AgentBalance();
        $agentSetting = new AgentSetting();
        $agentMaxBalance = $agentSetting->agentMaxBalanceValue();
        $agentInfo = new AgentBalance(); 
        // getting agent current balance
        $agentBal = $agentInfo->getAgentActiveBalance($reqInfo->agent_id);
                                
                                
                          
        $tnxData = array(
                            'ops_id'=>$param['by_ops_id'],
                            'agent_id'=>$reqInfo->agent_id,
                            'amount'=>$param['amt'],
                            'txn_type'=>TXNTYPE_AGENT_FUND_LOAD,
                            'agent_fund_request_id'=>$param['agent_fund_request_id'],
            
                        );
      
        if($param['response_status']==TXN_APPROVE_STATUS){ // getting update the transaction in corresponding db tables.
           
            $updatedBal = $param['amt'] + $agentBal;
           if ( $agentMaxBalance > $updatedBal){
            try{
                $objBaseTxn = new BaseTxn();
                $opsAgentTxn = $objBaseTxn->opsToAgent($tnxData);
            }catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                throw new Exception($e->getMessage());
                return false;
            }
            }
            else
            {
               throw new Exception('Load amount exceeds agent maximum balance allowed');
                return false; 
            }
        }
        
        // getting update in resopnse and request table of fund.
        $respData = array('agent_fund_request_id'=>$param['agent_fund_request_id'],
                          'comments'=>$param['rescomments'],
                          'response_status' => $param['response_status'],
                          'by_ops_id' => $param['by_ops_id'],
                         );

        $reqData = array('id'=>$param['agent_fund_request_id'], 'request_status'=>$param['response_status']);

        $insert = $this->_db->insert(DbTable::TABLE_AGENT_FUND_RESPONSE, $respData);
        $update = $this->_db->update(DbTable::TABLE_AGENT_FUND_REQUEST, $reqData, "id=".$reqData['id']);
        
        // After sucessfull update ,fetching the new balance
        //$agentInfo = new AgentBalance(); // getting agent latest balance
        $agentBal = $agentInfo->getAgentActiveBalance($reqInfo->agent_id);
        
        $reqData = $this->getFundRequestInfo($param['agent_fund_request_id']);
        $opr = Zend_Auth::getInstance()->getIdentity();
        if($param['response_status'] == TXN_APPROVE_STATUS){ 
            $emailSub = 'Balance loaded in your Account';
            
        }
        else if($param['response_status'] == TXN_DECLINE_STATUS){
            $emailSub = 'Balance NOT loaded in your Account';
        }
        
        $agentEmail = array(
                             'email_subject'=>$emailSub,
                             'agent_name'=>$reqInfo['agent_name'],
                             'agent_email'=>$reqInfo['email'],
                             'amount'=>$reqInfo['amt'],
                             'transaction_date'=>date('d-m-Y'),
                             'new_balance'=>$agentBal,
                             'agent_code'=>$reqInfo['agent_code'],
                             'mobile1'=>$reqInfo['mobile1'],
                             'comments'=>$param['rescomments'],
                             'operation_name'=> $opr->firstname.''.$opr->lastname,
                             'response_status'=>$param['response_status']
                           );
               try{

                    $m = new App\Messaging\MVC\Axis\Operation();
                    $mFundResponse = clone $m;
                    $m->agentBalance($agentEmail);
                    $mFundResponse->agentFundResponse($agentEmail);

               } catch (Exception $e ) { 
                           App_Logger::log($e->getMessage(), Zend_Log::ERR);
                           $e->getMessage();
               }
                    
        return true;
        
    }
    
    
    
    public function getRptAgentFundRequests($param, $page = 1, $paginate = NULL){ 
         
        $select = $this->sqlRptAgentFundRequests($param);
        //echo $select->__toString();//exit;                
        return $this->_paginate($select, $page, $paginate);     
    }
    
    /* sqlRptAgentFundRequests function will return query for Agent Fund Requests report 
     * it will accept the duration as in param array
     */
    
    public function sqlRptAgentFundRequests($param){
         $agentId = isset($param['agent_id'])?$param['agent_id']:'';
         //$agentIdCond = 0;
        
        $select =   $this->select();
        $select->setIntegrityCheck(false);
        $select->from(DbTable::TABLE_TXN_AGENT.' as ta', array('ta.amount as txn_amount', "DATE_FORMAT(ta.date_created, '%d-%m-%Y %h:%i:%s') as txn_datetime"));
        $select->joinLeft(DbTable::TABLE_AGENT_FUND_REQUEST.' as afr', "ta.agent_fund_request_id=afr.id", array('afr.comments'));
        $select->joinLeft(DbTable::TABLE_AGENT_FUND_RESPONSE.' as res', "res.agent_fund_request_id=afr.id", array('res.by_ops_id'));
        $select->joinLeft(DbTable::TABLE_AGENTS.' as a', "ta.agent_id=a.id", array('a.agent_code', 'concat(a.first_name," ",a.last_name) as agent_name'));
        $select->joinLeft(DbTable::TABLE_AGENT_CLOSING_BALANCE.' as acb', "ta.agent_id=acb.agent_id AND DATE_FORMAT(ta.date_created, '%Y-%m-%d')=acb.date", array('acb.closing_balance as agent_closing_balance'));
        $select->joinLeft(DbTable::TABLE_AGENT_CLOSING_BALANCE.' as acb2', "ta.agent_id=acb2.agent_id AND DATE_SUB(DATE_FORMAT(ta.date_created, '%Y-%m-%d'), INTERVAL 1 DAY)=acb2.date", array('acb2.closing_balance as agent_opening_balance'));
        $select->joinLeft(DbTable::TABLE_OPERATION_USERS.' as ops', "res.by_ops_id= ops.id ", array('concat(ops.firstname," ",ops.lastname) as ops_name'));
        
        if($agentId>0){                                                           
           $select->where('ta.agent_id=?',$agentId);
        }    
        
        //$select->where('ta.agent_fund_request_id>?', $agentIdCond);
        $select->where('ta.mode=?',TXN_MODE_CR);
        $select->where('ta.txn_type=?',TXNTYPE_AGENT_FUND_LOAD);
        $select->where('ta.txn_status=?',FLAG_SUCCESS);
        $select->where('ta.date_created>=?',$param['from']); 
        $select->where('ta.date_created<=?',$param['to']); 
        //$select->where('acb.date_created<=?',$param['to']); 
        $select->group('ta.txn_code');    
        $select->order('agent_name ASC');
        $select->order('ta.date_created ASC');
        
                
        return $select;
    }
    
    
    public function getRptAgentVirtualFundRequests($param, $page = 1, $paginate = NULL){
        $select = $this->sqlRptAgentVirtualFundRequests($param);                
        return $this->_paginate($select, $page, $paginate);     
    }
    
    public function sqlRptAgentVirtualFundRequests($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '0';
        $authorized = isset($param['authorize']) ? $param['authorize'] : FLAG_NO;
        $status = isset($param['status']) ? $param['status'] : '';

        if ($authorized == FLAG_YES) {
            $date_field = 'avf.date_funded';
        } else {
            $date_field = 'avf.date_request';
        }

        $avfsql = $this->select();
        $avfsql->setIntegrityCheck(false);
        $avfsql->from(
                DbTable::TABLE_AGENT_VIRTUAL_FUNDING . ' as avf', 
                array(
                    'amount as txn_amount', 'txn_code', 'utr', 'status',
                    "DATE_FORMAT(date_funded, '%d-%m-%Y %h:%i:%s') as txn_datetime",
                    "DATE_FORMAT(date_request, '%d-%m-%Y %h:%i:%s') as req_datetime"
        ));
        $avfsql->joinLeft(
                DbTable::TABLE_AGENTS . ' as a', "avf.agent_id=a.id", 
                array(
                    'a.agent_code', 'concat(a.first_name," ",a.last_name) as agent_name'
        ));
        $avfsql->joinLeft(
                DbTable::TABLE_AGENT_VIRTUAL_CLOSING_BALANCE . ' as avob', 
                "avf.agent_id=avob.agent_id AND DATE_SUB(DATE_FORMAT(" . $date_field . ", '%Y-%m-%d'), INTERVAL 1 DAY)=avob.date",
                array(
                    'avob.closing_balance as agent_opening_balance'
        ));
        $avfsql->joinLeft(
                DbTable::TABLE_AGENT_VIRTUAL_CLOSING_BALANCE . ' as avcb',
                "avf.agent_id=avcb.agent_id AND DATE_FORMAT(" . $date_field . ", '%Y-%m-%d')=avcb.date", 
                array(
                    'avcb.closing_balance as agent_closing_balance'
        ));
        $avfsql->joinLeft(
                DbTable::TABLE_OPERATION_USERS . ' as ops', "avf.by_ops_id= ops.id ", 
                array(
                    'concat(ops.firstname," ",ops.lastname) as ops_name'
        ));
        if ($agentId > 0) {
            $avfsql->where('avf.agent_id=?', $agentId);
        }
        $avfsql->where("DATE(" . $date_field . ") BETWEEN '" . $param['from'] . "' AND '" . $param['to'] . "'");
        if ($status != '') {
            $avfsql->where('avf.status IN (?)', $param['status']);
        }
        $avfsql->order($date_field . ' ASC');
        $avfsql->order('agent_name ASC');
        return $avfsql;
    }
    
    public function exportRptAgentFundRequests($param){
        
        $select = $this->sqlRptAgentFundRequests($param);        
        
        return  $this->_db->fetchAll($select);                      
    }
    
     /* exportRptAgentFundRequests function will query the Agent Fund Requests report data 
     *  for export to csv purpose
     */
    
    public function exportRptAgentvirtualFund($param){
        
        $select = $this->sqlRptAgentVirtualFundRequests($param);        
        
        return  $this->_db->fetchAll($select);                      
    }
    
     /* exportRptAgentWiseFundRequests function will query the Agent Wise Fund Requests report data 
     *  for export to csv purpose
     */
    public function exportRptAgentWiseFundRequests($param){
        
        $select = $this->sqlRptAgentFundRequests($param);        
        
        return  $this->_db->fetchAll($select);                      
    }
    
    
    /*  getTotalAgentFundAmount function will query to get agent total agent funding amount of fund requests aproved by ops 
     *  it will expect agent code and transaction date
     */
    
    public function getTotalAgentFund($param, $onDate = FALSE){
        $agentId = isset($param['agent_id'])?$param['agent_id']:'';
        $fromDate = isset($param['from'])?$param['from']:'';
        $toDate = isset($param['to'])?$param['to']:'';
        $date = isset($param['date'])?$param['date']:'';
        
        if($agentId>0){                                                           
            
//            $select =  $this->_db->select() ;
//            //$select->setIntegrityCheck(false);
//            $select->from(DbTable::TABLE_TXN_AGENT.' as ta', array('sum(ta.amount) as total_agent_funding_amount'));
//            $select->where('ta.agent_id=?',$agentId);
//            $select->where('ta.mode=?',TXN_MODE_CR);
//            $select->where('ta.txn_type=?',TXNTYPE_AGENT_FUND_LOAD);
//            $select->where('ta.txn_status=?',FLAG_SUCCESS);
//            if($onDate){
//               $select->where('DATE(ta.date_created) =?',$date);   
//            }
//            else{
//            $select->where('ta.date_created>=?',$fromDate); 
//            $select->where('ta.date_created<=?',$toDate); 
//            }
            
            //Enable DB Slave
            $this->_enableDbSlave();
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from($this->_name ." as afr", array('sum(afr.amt) as total_agent_funding_amount'))
                    ->join(DbTable::TABLE_AGENT_FUND_RESPONSE.' as resp',"resp.agent_fund_request_id = afr.id")     
                    ->where('afr.agent_id = ?', $agentId)
                    ->where('afr.request_status = ?', STATUS_APPROVE);
            if($onDate){
                $date = isset($param['date'])?$param['date']:'';
                $select->where('DATE(resp.datetime_response) =?',$date);   
                $select->where('resp.response_status = ?', STATUS_APPROVE); 
            }
            else{
                $fromDate = isset($param['from'])?$param['from']:'';
                $toDate = isset($param['to'])?$param['to']:'';
                $select->where('resp.datetime_response >= ?',$fromDate); 
                $select->where('resp.datetime_response <= ?',$toDate); 
                $select->where('resp.response_status = ?', STATUS_APPROVE); 
            }

            $row = $this->_db->fetchRow($select);
            //Disable DB Slave
            $this->_disableDbSlave();
            return $row;
       } else return 0;
    }
    
    
    /* pendingAgentFundRequests function will return query for Agent Pending Fund Requests report 
     * it will accept the duration as in param array
     */
    
    public function pendingAgentFundRequests($param){
        $agentId = isset($param['agent_id'])?$param['agent_id']:'';
         //$agentIdCond = 0;
        $agentBinding = new BindAgentProductCommission();
        $select =   $this->_db->select();
//        $select->setIntegrityCheck(false); 	
        $select->from(DbTable::TABLE_AGENT_FUND_REQUEST.' as afr',array('afr.id','afr.agent_id','afr.amt','afr.fund_transfer_type_id','afr.comments','afr.request_status','DATE(afr.datetime_request) as datetime_request'));
        $select->joinLeft(DbTable::TABLE_FUND_TRANSFER_TYPE.' as fr', "afr.fund_transfer_type_id=fr.id", array('fr.name as fund_name'));
        $select->joinLeft(DbTable::TABLE_AGENTS.' as a', "afr.agent_id=a.id", array('a.agent_code', 'concat(a.first_name," ",a.last_name) as agent_name'));
        
        if($agentId > 0){                                                           
           $select->where('afr.agent_id=?',$agentId);
        }    
        
        $select->where('afr.request_status =?',FLAG_PENDING);
        $select->where('afr.datetime_request >=?',$param['from']); 
        $select->where('afr.datetime_request <=?',$param['to']); 
            
        $select->order('agent_name ASC');
        $select->order('afr.datetime_request ASC');
//        echo $select;
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
    
     /* totalpendingAgentFundRequests function will return query for the SUM of Agent Pending Fund Requests report 
     * it will accept the duration as in param array
     */
    
    public function totalpendingAgentFundRequests($param, $page = 1, $paginate = NULL){
         $agentId = isset($param['agent_id'])?$param['agent_id']:'';
         //$agentIdCond = 0;
        
        //Enable DB Slave
        $this->_enableDbSlave();
        $select =   $this->select();
        $select->from(DbTable::TABLE_AGENT_FUND_REQUEST.' as afr',array('afr.id','afr.agent_id','afr.amt','afr.fund_transfer_type_id','afr.comments','afr.request_status','DATE(afr.datetime_request) as datetime_request','sum(afr.amt) as total_agent_pending_funding_amount' ));
        if($agentId > 0){                                                           
           $select->where('afr.agent_id=?',$agentId);
        }    
        
        $select->where('afr.request_status =?',FLAG_PENDING);
        $select->where('afr.datetime_request >=?',$param['from']); 
        $select->where('afr.datetime_request <=?',$param['to']); 
        $select->group('DATE(datetime_request)')    ;
//        echo $select;
                
        $row = $this->_db->fetchRow($select);
        //Disable DB Slave
        $this->_disableDbSlave();
        return $row;
    }
    
    
    /*  getTotalAgentApprovedFund() will return agent total approved fund amount
     *  it will expect date from and to
     */
    
    public function getTotalAgentApprovedFund($param){
        $dateFrom = isset($param['dateFrom'])?$param['dateFrom']:'';
        $dateTo = isset($param['dateTo'])?$param['dateTo']:'';
        
        if($dateFrom!='' && $dateTo!=''){                                                           
            
            $select =  $this->_db->select() ;
            //$select->setIntegrityCheck(false);
            $select->from(DbTable::TABLE_AGENT_FUND_REQUEST.' as afr', array('sum(afr.amt) as total_agent_approved_fund'));
            $select->where('afr.request_status=?', STATUS_APPROVE);
            $select->where("DATE(afr.datetime_request) BETWEEN '".$dateFrom."' AND '".$dateTo."'"); 
            //echo $select; exit;

            return $this->_db->fetchRow($select);
       } else return array();
    }
    
    
    
    public function getAgentFunds($param, $onDate = FALSE){
        $agentId = isset($param['agent_id'])?$param['agent_id']:'';
        $fromDate = isset($param['from'])?$param['from']:'';
        $toDate = isset($param['to'])?$param['to']:'';
        $date = isset($param['date'])?$param['date']:'';
        
        if($agentId>0){                                                           
       
            $select = $this->_db->select()
                    ->from(DbTable::TABLE_AGENT_FUND_REQUEST ." as afr", array('afr.amt as amount', 'comments'))
                    ->join(DbTable::TABLE_AGENT_FUND_RESPONSE.' as resp',"resp.agent_fund_request_id = afr.id", array("datetime_response"))     
                    ->joinLeft(DbTable::TABLE_TXN_AGENT. " AS b", "afr.id = b.agent_fund_request_id", array('txn_code'))  
                    ->where('afr.agent_id = ?', $agentId)
                    ->where('afr.request_status = ?', STATUS_APPROVE);
            if($onDate){
                $date = isset($param['date'])?$param['date']:'';
                $select->where('DATE(resp.datetime_response) =?',$date);   
            }
            else{
                $fromDate = isset($param['from'])?$param['from']:'';
                $toDate = isset($param['to'])?$param['to']:'';
                $select->where('resp.datetime_response >= ?',$fromDate); 
                $select->where('resp.datetime_response <= ?',$toDate); 
                $select->where('resp.response_status = ?',STATUS_APPROVE); 
            }

            return $this->_db->fetchAll($select);
       } else return 0;
    }
    
    
    public function virtualFundRequests($param) { 
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '0';
        $authorized = isset($param['authorize']) ? $param['authorize'] : FLAG_NO;
        $status = isset($param['status']) ? $param['status'] : '';

        if ($authorized == FLAG_YES) {
            $date_field = 'avf.date_funded';
        } else {
            $date_field = 'avf.date_request';
        }

        $avfsql = $this->select();
        $avfsql->setIntegrityCheck(false);
        $avfsql->from(
                DbTable::TABLE_AGENT_VIRTUAL_FUNDING . ' as avf',
                array('sum(amount) as total_txn_amount')); 
        if ($agentId > 0) {
            $avfsql->where('avf.agent_id=?', $agentId);
        }
        $avfsql->where("DATE_FORMAT(" . $date_field . ",'%Y-%m-%d %h:%i:%s') BETWEEN '" . $param['from'] . "' AND '" . $param['to'] . "'");
        
        if ($status != '') {
            $avfsql->where('avf.status IN (?)', $param['status']);
        }  
        return $this->_db->fetchRow($avfsql);
    }
}
