<?php

/*
 * Ratnakar Remitter Model
 */

class Remit_Ratnakar_Remitter extends Remit_Ratnakar {

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
    protected $_name = DbTable::TABLE_RATNAKAR_REMITTERS;

    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Remitters';



    /* public function getList(){
      $select = $this->select()
      ->setIntegrityCheck(false)
      ->from("t_agents as a",array('id'))
      ->joinLeft("t_opertaion as o", "o.id = a.operation_id",array('id','name'))
      ->where('email =?','vikram@transerv.co.in');
      //echo $select->__toString();exit;
      return  $this->fetchAll($select);

      } */

    /*
     * Get Remitter details by mobile number
     */
/*    public function getRemitter($mobile) {
        if (strlen($mobile) <> Mobile::$length || !is_numeric($mobile)) {
            throw new Exception("Invalid mobile number");
        }
        

        $select = $this->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTERS,array('id','concat(name," ",last_name) as name','profile_photo','product_id','unicode','ifsc_code','bank_account_number','bank_name','branch_name','branch_city','branch_address','bank_account_type','address','address_line2','city','state','pincode','mobile_country_code','mobile','dob','mother_maiden_name','email','legal_id','regn_fee','service_tax','txn_code','by_agent_id','by_ops_id','ip','date_created','date_modified','status'))
                ->where("mobile = '$mobile' AND status = '" . STATUS_ACTIVE . "'");

        $res = $this->fetchRow($select);
        if (!empty($res)) {
            return $res;
        } else {
            throw new Exception("Mobile not registered");
        }
    }*/

	public function getRemitter($mobile) {
        if (strlen($mobile) <> Mobile::$length || !is_numeric($mobile)) {
            throw new Exception("Invalid mobile number");
        }
        

        $select = $this->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTERS,array('id','concat(name," ",last_name) as name','profile_photo','product_id','unicode','ifsc_code','bank_account_number','bank_name','branch_name','branch_city','branch_address','bank_account_type','address','address_line2','city','state','pincode','mobile_country_code','mobile','dob','mother_maiden_name','email','legal_id','regn_fee','service_tax','txn_code','by_agent_id','by_ops_id','ip','date_created','date_modified','status'))
                ->where("mobile = '$mobile'");
        $res = $this->fetchRow($select);
        if (!empty($res)) {
            if($res['status'] == STATUS_ACTIVE)
            {
            return $res;
            }else{
             throw new Exception("Remitter is blocked.");   
            }
        } else {
            throw new Exception("Mobile not registered");
        }
    }

    /*
     * Get Remitter details by mobile number
     */

    public function getRemitterById($id) {
        $select = $this->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTERS)
                ->where("id = '$id'")
                ->where("status = '" . STATUS_ACTIVE . "'");
        //echo $select;exit;
        $res = $this->fetchRow($select);
        if (!empty($res)) {
            return $res;
        } else {
            throw new Exception("Remitter with Id does not exist");
        }
    }

    public function getRemitterByMobileNumber($mobile) {
        $select = $this->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTERS)
                ->where("mobile = '$mobile'");
        return $this->fetchRow($select);
    }


    public function addRemitter($param, $remitterId = 0, $oldVals = array()) {

        $mobile = isset($param['mobile']) ? $param['mobile'] : '';
        $email = isset($param['email']) ? $param['email'] : '';
        $emailOld = isset($oldVals['email_old']) ? $oldVals['email_old'] : '';

        $mobObj = new Mobile();
        $emailObj = new Email();
        $session = new Zend_Session_Namespace('App.Agent.Controller');
		$agentid= isset($param['by_agent_id']) ? $param['by_agent_id'] : '';

        // mobile duplicacy check
        if (($remitterId == 0) && ($mobile != '')) {
            try {
                $remitterId = $mobObj->checkRatnakarRemitterMobileDuplicateNew($mobile,$agentid);
				if( $remitterId == 0 ){
					$r = $this->getRemitterByMobileNumber($mobile);
					$remitterId = $r->id;
					//return FALSE;
				}
            } catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                throw new Exception($e->getMessage());
            }
        }

        // email duplicacy check
//        if (($email != '') || ($emailOld != '' && $email != '' && strtolower($emailOld) != strtolower($email))) {
//            try {
//                $emailCheck = $emailObj->checkKotakRemitterEmailDuplicate($email);
//            } catch (Exception $e) {
//                App_Logger::log($e->getMessage(), Zend_Log::ERR);
//                throw new Exception($e->getMessage());
//            }
//        }


        if ($remitterId < 1) {

            $resp = $this->_db->insert(DbTable::TABLE_RATNAKAR_REMITTERS, $param); // adding remitter to t_remitters   
            if ($resp)
                $remitterId = $this->_db->lastInsertId(DbTable::TABLE_RATNAKAR_REMITTERS, 'id');

            $session->remitter_id = $remitterId;
        } else {
            $chResp = $this->_db->update(DbTable::TABLE_RATNAKAR_REMITTERS, $param, 'id="' . $remitterId . '"');
        }

			$session->remitter_id = $remitterId;
			return TRUE;
    }
	
	

    public function updateRemitter($param, $remitterId) {
        if ($remitterId > 0 && !empty($param)) {
            $resp = $this->_db->update(DbTable::TABLE_RATNAKAR_REMITTERS, $param, 'id="' . $remitterId . '"');
        }
        else
            throw new Exception('Unicode not found for update!');
    }

    public function getRemitterInfo($remitterId = 0, $mobile = 0) {

        $mobLen = strlen($mobile);
        $whereString = '';

        if (($remitterId != 0 && is_numeric($remitterId)) || $mobLen == 10) {

            if ($remitterId != 0)
                $whereString = " id = '$remitterId'";
            else if ($mobLen == 10)
                $whereString = " mobile = '$mobile'";

            $select = $this->_db->select()
                    //->setIntegrityCheck(false)
                    ->from(DbTable::TABLE_RATNAKAR_REMITTERS)
                    ->where($whereString);

            // echo $select->__toString();exit;
            return $this->_db->fetchRow($select);
        }
        else
            throw new Exception('Remitter id or mobile missing!');
    }

    /*
     * Get Remitter beneficiaries details by mobile number
     */

    public function getRemitterbeneficiaries($id) {
        $page = 1;
        $paginate = NULL;
        $decryptionKey = App_DI_Container::get('DbConfig')->key;
        $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'" . $decryptionKey . "') as bank_account_number");
        $branchAddress = new Zend_Db_Expr("AES_DECRYPT(`b`.`branch_address`,'" . $decryptionKey . "') as branch_address");
        $mobile = new Zend_Db_Expr("AES_DECRYPT(`b`.`mobile`,'" . $decryptionKey . "') as mobile");
        $email = new Zend_Db_Expr("AES_DECRYPT(`b`.`email`,'" . $decryptionKey . "') as email");

        $select = $this->select()
                ->from(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", array('id', 'remitter_id', 'name', 'nick_name', 'ifsc_code', $bankAccountNumber, 'bank_name', 'branch_name', 'branch_city', $branchAddress, 'bank_account_type', 'address_line1', 'address_line2', $mobile, $email, 'by_agent_id', 'by_ops_id', 'date_created', 'date_modified', 'status','beneficiary_id','rat_status'))
                ->setIntegrityCheck(false)
                ->joinLeft(DbTable::TABLE_RATNAKAR_REMITTERS . " as r", "r.id = b.remitter_id and b.status = '" . STATUS_ACTIVE . "'", array('id as rid'))
->joinLeft(DbTable::TABLE_BANK_IFSC . " as i", "i.ifsc_code = b.ifsc_code ",array('enable_for'))
                ->where('r.id =?', $id);
        return $this->_paginate($select, $page, $paginate);
    }

    /* searchRemitter() will return remitters details after searching remitter
     */

    public function searchRemitter($param, $page = 1, $paginate = NULL, $force = FALSE) {

        $columnName = $param['searchCriteria'];
        $keyword = $param['keyword'];
        $whereString = "$columnName LIKE '%$keyword%'";

        $details = $this->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTERS, array('id', 'name', 'address', 'mobile', 'email'))
                ->setIntegrityCheck(false)
                ->where("status='" . STATUS_ACTIVE . "'")
                ->where($whereString)
                ->order('date_created DESC');
        return $this->_paginate($details, $page, $paginate);
    }
    
    
    /*
     * searchRemitterReport
     */
    public function searchRemitterReport($param, $page = 1, $paginate = NULL, $report = 'view') {
        
        $rName = isset($param['name']) ? $param['name'] : '';
        $fromDate = isset($param['from_date']) ? $param['from_date'] : '';
        $toDate = isset($param['to_date']) ? $param['to_date'] : '';
        $status = isset($param['status']) ? $param['status'] : '';
        $utr = isset($param['utr']) ? $param['utr'] : '';
        $txn_code = isset($param['txn_code']) ? $param['txn_code'] : '';
        $mobile = isset($param['mobile']) ? $param['mobile'] : '';
        $bank_account_number = isset($param['bank_account_number']) ? $param['bank_account_number'] : '';
        if($status == 'all'){
            $status = '';
        }
        
        $details = $this->select();
        $details->from(DbTable::TABLE_RATNAKAR_REMITTERS ." as r", array('id', 'name', 'address', 'mobile', 'email'));
        
        $details->joinLeft(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rrr", "r.id = rrr.remitter_id", array('txn_code','amount','utr','status as request_status','date_utr','date_created as transaction_date','date_status_response'));
        $details->joinLeft(DbTable::TABLE_RATNAKAR_RESPONSE_FILE." as res", "rrr.utr = res.utr" , array('res.rejection_code', 'res.rejection_remark', 'res.returned_date'));
        $details->joinLeft(DbTable::TABLE_AGENTS . " as a", "r.by_agent_id = a.id ", array('a.id as agent_id', 'a.agent_code', 'concat(a.first_name," ",a.last_name) as agent_name','a.mobile1 as agent_mobile','a.email as agent_email', 'a.user_type as agent_user_type'));
        $details->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "a.id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", array('ad.estab_city', 'ad.estab_pincode'));
        $details->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as rb", "rrr.beneficiary_id = rb.id ", array('name as beneficiary_name'));
        $details->setIntegrityCheck(false);
        $details->where("r.status='" . STATUS_ACTIVE . "'");
        if($rName != '') {
            $details->where("r.name LIKE '%".$rName."%'");
        }
        if( ($fromDate != '') && ($toDate != '') ) {
            $details->where("date(rrr.date_created) between '".$fromDate."' AND '".$toDate."'");
        }
        if($status != '') {
            $details->where("rrr.status = '".$status."'");
        }
        if($utr != '') {
            $details->where("rrr.utr LIKE '%".$utr."%'");
        }
        if($txn_code != '') {
            $details->where("rrr.txn_code LIKE '%".$txn_code."%'");
        }
        if($mobile != '') {
            $details->where("r.mobile LIKE '%".$mobile."%'");
        }
        if($bank_account_number != '') {
           $encryptionKey = App_DI_Container::get('DbConfig')->key;
           $bene_bank_account_number = new Zend_Db_Expr("AES_ENCRYPT('".$bank_account_number."','".$encryptionKey."')");
            $details->where("rb.bank_account_number = ".$bene_bank_account_number);
        }
       
        
        $details->order('r.date_created DESC');
        if($report == 'export'){
          return $this->fetchAll($details);
        }else{
        return $this->_paginate($details, $page, $paginate);
        }
    }
    /*
     * getRemitterTransactionByID : getting last 5 transaction of a remitter 
     */
    public function getRemitterTransactionByID($rid,$page =1,$filltered = TRUE,$beneficiary_id=0) {
        //$page = 1;
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(DbTable::TABLE_RATNAKAR_REMITTERS . " as rr", array('mobile','name'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rrr", "rr.id = rrr.remitter_id", array('id','txn_code', 'status', 'sender_msg', 'date_created','amount','utr'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as rb", "rrr.beneficiary_id = rb.id ", array('name as beneficiary_name'))
                ->where('rr.id = rrr.remitter_id')
                ->where('rr.id =?', $rid);
        
        if($filltered == TRUE) {
            $select->where('rrr.status in ("'.STATUS_SUCCESS.'","'.STATUS_FAILURE.'","'.STATUS_HOLD.'","'.STATUS_REFUND.'","'.STATUS_IN_PROCESS.'")');
        }
        if($beneficiary_id) {
            $select->where('rrr.beneficiary_id =?', $beneficiary_id);
        }
        
        $select->order('rrr.date_created DESC');
       // return $this->_paginate($select, $page, true);
        
        $res = $this->fetchRow($select);
        if (!empty($res)) {
            return $res;
        } else {
            throw new Exception("Remitter with Id does not exist");
        }
    }
    
    /*
     * Get remitter registration fee for an agent on a particular date for a product
     */

    public function getRemitterRegnfee($param) {
        $date = $param['date'];
        $datefrom = $param['date_from'];
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
        $checkRegFee = isset($param['check_fee']) ? $param['check_fee'] : true;
        $mobileno = isset($param['mobile_no']) ? $param['mobile_no'] : '';
	$productId = isset($param['product_id']) ? $param['product_id'] : '';
        $txnno = isset($param['txn_no']) ? $param['txn_no'] : 0;
        
        $details = $this->select();
        $details->from(DbTable::TABLE_RATNAKAR_REMITTERS . " as r", array('r.regn_fee as fee', 'r.mobile as mobile_number', 'r.service_tax', 'r.id AS rid', 'r.product_id', 'DATE(r.date_created) as date_created', 'r.date_created as txn_date', 'r.txn_code', 'concat(r.name," ",r.last_name) as remit_name','r.email as remitter_email', 'r.date_created as remit_regn_date', 'r.status as txn_status'));
        $details->setIntegrityCheck(false);
        $details->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "r.product_id = p.id ", array('p.ecs_product_code','p.unicode as pro_unicode'));
        $details->joinLeft(DbTable::TABLE_AGENTS . " as a", "r.by_agent_id = a.id ", array('a.id as agent_id', 'a.agent_code', 'concat(a.first_name," ",a.last_name) as name','a.mobile1 as agent_mobile','a.email as agent_email', 'a.user_type as agent_user_type'));
        $details->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "r.by_agent_id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", array('ad.estab_city', 'ad.estab_pincode'));
        $details->where("r.status = '" . STATUS_ACTIVE . "' OR r.status = '" . STATUS_INACTIVE . "' ");
        if ($checkRegFee)
            $details->where("r.regn_fee > ? ", 0);


        if ($agentId > 0)
            $details->where("r.by_agent_id = ? ", $agentId);

        if(!empty($datefrom)){
            $details->where("r.date_created >= ?", $datefrom);
            $details->where("r.date_created <= ?", $date);
        } else if(!empty($date)) {
            $details->where("DATE(r.date_created) = ?", $date);
        }
        if ($mobileno != '')
            $details->where("r.mobile = ? ", $mobileno);
	
	if ($productId != '')
            $details->where("r.product_id = ? ", $productId);
        
        if ($txnno > 0)
            $details->where("r.txn_code = ? ", $txnno);
        
      //  echo $details;exit;
        return $this->fetchAll($details);
    }

    
    /* getRemittersOnDateBasis() will return remitters who got registered successfully on date basis
     */

    public function getRemittersOnDateBasis($param) {

        $retData = array();
        $retNewData = array();
        
        if (!empty($param)) {

            $param['check_fee'] = false;
            $retData = $this->getRemitterRegnfee($param);
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
                   /*$refund_txn = $this->getRefundTxnRefNo($retData[$j]['rid'], $retData[$j]['agent_id']);

                    if(!empty($refund_txn))
                    {
                        $alterData['refund_txn_code'] = $refund_txn['refund_txn_code'];
                    }
                    else
                    {
                        $alterData['refund_txn_code'] = '';
                    }*/
                    
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
                    $retNewData[$k]['batch_name'] = '';
                    $k++;
                }
            }
        }

        return $retNewData;
    }

    
    /* get Refund/Reversed Transaction Reference No for a transaction */
    public function getRefundTxnRefNo($rmid, $agent_id)
    {

            $select = $this->_db->select();
            $select->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND , array('txn_code as refund_txn_code'));
            
            $select->where("remitter_id = ?", $rmid);
            $select->where("agent_id = ?", $agent_id);

            return $this->_db->fetchRow($select);
    }

    /*
     *  getRemitterRegistrations function will fetch remitters details registred during a time span
     */

    public function getRemitterRegistrations($param) {
       //Enable DB Slave
        $this->_enableDbSlave();
        $from = $param['from'];
        $to = $param['to'];
        $mobileno = isset($param['mobile_no']) ? $param['mobile_no'] : '';

        $details = $this->select();
        $details->from(DbTable::TABLE_RATNAKAR_REMITTERS . " as r", array('r.name', 'r.mobile', 'r.unicode', 'r.id AS rid', 'r.product_id', "DATE_FORMAT(DATE(r.date_created),'%m-%d-%Y') as date_created", 'r.address'));
        $details->setIntegrityCheck(false);
        $details->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "r.product_id = p.id ", array('p.ecs_product_code', 'p.bank_id','p.name AS product_name'));
        $details->joinLeft(DbTable::TABLE_BANK . " as b", "b.id = p.bank_id ", array('b.name as bank_name'));
        $details->joinLeft(DbTable::TABLE_AGENTS . " as a", "r.by_agent_id = a.id ", array('a.agent_code', 'concat(a.first_name," ",a.last_name) as agent_name'));
        $details->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "a.id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", array('ad.estab_city', 'ad.estab_pincode', 'ad.estab_state'));
        
        $details->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel", "a.id = orel.to_object_id" ,array());
        $details->joinLeft(
                DbTable::TABLE_AGENTS." as dis", "dis.id = orel.from_object_id" , 
                    array(
                        'dis.agent_code AS dist_code',' concat(dis.first_name," ",dis.last_name) as dist_name',
                ));
        $details->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel_sdis", "dis.id = orel_sdis.to_object_id" ,array());
        $details->joinLeft(
                DbTable::TABLE_AGENTS." as sdis", "sdis.id = orel_sdis.from_object_id" , 
                    array(
                        'sdis.agent_code AS sup_dist_code' ,'concat(sdis.first_name," ",sdis.last_name) as sup_dist_name'
                )); 
        
        $details->where("r.status = '" . STATUS_ACTIVE . "' OR r.status = '" . STATUS_INACTIVE . "' ");
        $details->where("DATE(r.date_created) >= '$from' AND DATE(r.date_created) <= '$to'");
        if ($mobileno != '') {
            $details->where("r.mobile = ? ", $mobileno);
        }
        //Disable DB Slave
        $this->_disableDbSlave(); 
        return $this->fetchAll($details);
    }

    /*  getAgentTotalRemitterRegnFeeSTax() is responsible for fetch data for agent total remitter regn fee & Service Tax amount 
     *  as params it will accept agent id and transaction date
     */

    public function getAgentTotalRemitterRegnFeeSTax($param) {

        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $date = isset($param['date']) ? $param['date'] : '';

        if ($date != '') {
            //Enable DB Slave
            $this->_enableDbSlave();
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_RATNAKAR_REMITTERS . ' as r', array('sum(r.regn_fee) as agent_total_remitter_regn_fee', 'sum(r.service_tax) as agent_total_remitter_regn_stax', 'count(r.id) as count_agent_total_remitters'));
            if ($agentId >= 1)
                $select->where('r.by_agent_id=?', $agentId);

            $select->where("r.status='" . STATUS_ACTIVE . "'");
            $select->where("DATE(r.date_created) ='" . $date . "'");

//            echo $select.'<br><br>'; 
            $row = $this->_db->fetchRow($select);
            //Disable DB Slave
            $this->_disableDbSlave();
            return $row;
        }
        else
            return array();
    }
    
    public function getAgentTotalRemitterRegnFeeAllSTax($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';

        if ($from != '' && $to != '') {
            $select = $this->_db->select();
            $select->from(
                        DbTable::TABLE_RATNAKAR_REMITTERS . ' as r', 
                        array(
                            'sum(r.regn_fee) as agent_total_remitter_regn_fee',
                            'sum(r.service_tax) as agent_total_remitter_regn_stax', 
                            'count(r.id) as count_agent_total_remitters',
                            'DATE_FORMAT(r.date_created,"%d-%m-%Y") AS txn_date' 
                        ));
            if ($agentId >= 1)
                $select->where('r.by_agent_id=?', $agentId); 
            $select->where("r.status='" . STATUS_ACTIVE . "'");
            $select->where("DATE(r.date_created) BETWEEN '". $from ."' AND '". $to ."'"); 
            $select->group('DATE_FORMAT(r.date_created, "%Y-%m-%d")'); 
            return $this->_db->fetchAll($select);
        }
        else
            return array();
    }

    /*  getAgentRemitterRegnFee() is responsible for fetch data for agent total remitter regn fee & Service Tax amount 
     *  as params it will accept agent id and transaction date
     */

    public function getAgentRemitterRegnFee($param) {

        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $productId = isset($param['product_id']) ? $param['product_id'] : '';
        $date = isset($param['date']) ? $param['date'] : '';

        if ($date == ''){
            return array();
        }
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RATNAKAR_REMITTERS . ' as r', array('r.regn_fee as transaction_fee', 'r.service_tax as transaction_service_tax', 'r.txn_code as transaction_ref_no'));
        $select->where("r.status='" . STATUS_ACTIVE . "'");
        $select->where("DATE(r.date_created) ='" . $date . "'");

        if($agentId > 0) {
            $select->where('r.by_agent_id=?',$agentId); 
        }
        if($productId > 0){
            $select->where('r.product_id=?',$productId); 
        }
//        echo $select.'<br><br>';
        return $this->_db->fetchAll($select);
    }

    /* getRemittersForDD() will return remitters array for drop down
     */

    public function getRemittersForDD() {

        $select = $this->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTERS, array('id', 'name as remitter_name'))
                ->where("status = '" . STATUS_ACTIVE . "'");
        //echo $select; exit;
        $remitters = $this->_db->fetchAll($select);

        $dataArray = array();
        //$dataArray[''] = "Select Fund Transfer Type";
        foreach ($remitters as $id => $val) {
            $dataArray[$val['id']] = $val['remitter_name'];
        }
        return $dataArray;
    }

    /*
     *  getRemitterRegistrationsCount function will fetch remitters registred count for a time span
     */

    public function getRemitterRegistrationsCount($param) {
        $from = $param['from'];
        $to = $param['to'];

        $details = $this->_db->select();
        $details->from(DbTable::TABLE_RATNAKAR_REMITTERS . " as r", array('count(r.id) as total_remitter_count'));
        $details->where("r.status = '" . STATUS_ACTIVE . "' OR r.status = '" . STATUS_INACTIVE . "'");
        $details->where("DATE(r.date_created) >= '$from' AND DATE(r.date_created) <= '$to'");
        //ECHO $details; EXIT;
        return $this->_db->fetchRow($details);
    }

    /* getRemittersCount() will return the number of remitters registerd for the month
     */

    public function getRemittersCount($param) {
        $agentId = isset($param['agentId']) ? $param['agentId'] : '';
        $from = $param['from'];
        $to = $param['to'];
        $select = $this->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTERS, array('count(id) as remitter_count', 'DATE(date_created) as date_created'))
                ->where("status = '" . STATUS_ACTIVE . "'");
        if ($agentId != '') {
            $select->where('by_agent_id=?', $agentId);
        }
        $select->where("date_created between '$from' AND '$to'");
        $select->group('date_created');
       
        $remitters = $this->_db->fetchAll($select);

        return $remitters;
    }

    /* getRemittersRgnCount() will return the number of remitters registerd for the month
     */

    public function getRemittersRgnCount($param) {
        $agentId = isset($param['agentId']) ? $param['agentId'] : '';
        $from = $param['from'];
        $to = $param['to'];
        $select = $this->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTERS, array('count(id) as remitter_count', 'DATE(date_created) as date_created'))
                ->where("status = '" . STATUS_ACTIVE . "'");
        if ($agentId != '') {
            $select->where('by_agent_id=?', $agentId);
        }
        $select->where("date_created between '$from' AND '$to'");
//        $select->group('date_created');
//         echo $select;
        $remitters = $this->_db->fetchRow($select);

        return $remitters;
    }

    public function getRemitterTransactionByPhone($phone, $startDate = null, $toDate = null,$filltered = FALSE,$page =1,$beneficiary_id=0) {
        //$page = 1;
        $startDate = $startDate . ' 00:00:00';
        $toDate = $toDate . ' 23:59:59';
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(DbTable::TABLE_RATNAKAR_REMITTERS . " as krr", array('mobile','name'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as krt", "krr.id = krt.remitter_id", array('id as remittance_request_id','txn_code', 'status', 'sender_msg', 'date_created','amount','utr','flag'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as kb", "krt.beneficiary_id = kb.id ", array('name as beneficiary_name'))
                ->where('krr.id = krt.remitter_id')
                ->where('krr.mobile =?', $phone)
                ->where("krt.date_created between '".$startDate."' AND '".$toDate."'");
        
        if($filltered == TRUE) {
            $select->where('krt.status in ("'.STATUS_SUCCESS.'","'.STATUS_FAILURE.'","'.STATUS_HOLD.'","'.STATUS_REFUND.'","'.STATUS_IN_PROCESS.'","'.STATUS_PROCESSED.'")');
        }
        if($beneficiary_id) {
            $select->where('krt.beneficiary_id =?', $beneficiary_id);
        }
        
        $select->order('krt.date_created DESC');
        return $this->_paginate($select, $page, true);
    }

    
    /* getRemitterHoldTransactions() will return remitter's HOld Transaction details after searching remitter using his mobile
     */

    public function getRemitterTransactions($params, $page = 1, $paginate = NULL) {

        $details = $this->select()
                ->setIntegrityCheck(false)
                ->from(DbTable::TABLE_RATNAKAR_REMITTERS . ' as kr', array('id','mobile', 'email','name as r_name'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as krt", "kr.id = krt.remitter_id", array('id','txn_code','amount' ,'status', 'sender_msg', 'date_created','fee','service_tax'))

                ->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as kb", "krt.beneficiary_id = kb.id", array('id','name'))
                ->where("krt.status='" . $params['status'] . "'")
                ->where("kr.mobile =?",$params['mobile'])
                ->order('krt.date_created DESC');
        //echo $details;//exit;
        return $this->_paginate($details, $page, $paginate);
    }
    
    
    public function getRemitterHoldTransactions($page = 1, $paginate = NULL) {

        $details = $this->select()
                ->setIntegrityCheck(false)
                ->from(DbTable::TABLE_RATNAKAR_REMITTERS . ' as kr', array('id','mobile', 'email','name as r_name'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as krt", "kr.id = krt.remitter_id", array('id','txn_code','amount' ,'status', 'sender_msg', 'date_created','fee','service_tax'))
                ->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as kb", "krt.beneficiary_id = kb.id", array('id','name'))
                ->where("krt.status=?",STATUS_HOLD)
                ->order('krt.date_created DESC');
        return $this->_paginate($details, $page, $paginate);
    }
    
     /* getRatnakarRemittance() will return the number of remittance for the duration
     */
    public function getRatnakarRemittance($param){ 
       //Enable DB Slave
       $this->_enableDbSlave();
        
        if(isset($param['duration']) && $param['duration'] != ''){
            $dates = Util::getDurationAllDates($param['duration']);
           
            }
            else{
                
            $dates = Util::getDurationRangeAllDates($param);  
            }
         
        $retTxnData = array();
        $objRatnakarRemitRequest = new Remit_Ratnakar_Remittancerequest();
        foreach($dates as $queryDate){
                $to = isset($queryDate['to'])?$queryDate['to']:'';
                $from = isset($queryDate['from'])?$queryDate['from']:'';
              
                $queryDateArr = explode(' ', $to);
                
                     
                     $queryDate = array('date'=>$queryDateArr[0],
                                'agent_id' => $param['agent_id'],
                                );
                     if(isset($param['mobile_no'])){
                     $queryDate['mobile_no'] = $param['mobile_no'];
                     }
                     if(isset($param['txn_no'])){
                     $queryDate['txn_no'] = $param['txn_no'];
                     }
                   
               
                /**** getting agent remitters registered for particular date ****/
                $remitters  = $this->getRemittersOnDateBasis($queryDate);
                if(!empty($remitters)){
                    $retTxnData = array_merge($retTxnData, $remitters);
                }

                /**** getting agent remitters's fund transfer request for particular date *****/
                $remitRequests  = $objRatnakarRemitRequest->getRemitRequestOnDateBasis($queryDate);
            
         
                if(!empty($remitRequests)){
                    $retTxnData = array_merge($retTxnData, $remitRequests);
                }
              
                /**** getting agent remitters's refunds for particular date *****/
                $remitRefunds  = $objRatnakarRemitRequest->getRemitRefundsOnDateBasis($queryDate);
                if(!empty($remitRefunds)){
                    $retTxnData = array_merge($retTxnData, $remitRefunds);
                }

		/**** getting agent commission details *****/
                $commRequests  = $objRatnakarRemitRequest->getCommissionTransactions($queryDate);
                if(!empty($commRequests)){
                	$retTxnData = array_merge($retTxnData, $commRequests);
                }

          } // for each loop 
          
        //Disable DB Slave
       $this->_disableDbSlave();  
          return $retTxnData ;
    }
     
    /*
     * Get Remitter beneficiaries details by mobile number
     */

    public function getRemitterbeneficiariesCount($id) {

        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", array('count(b.id) as count' ,'b.status'))
                ->where('b.remitter_id =?', $id)
                ->where("b.status = '" . STATUS_ACTIVE . "'");
        $res = $this->fetchRow($select);
       
        return $res['count'];
    }
    
    public function getAgentRemitterRegnsFeeSTax($param) {

        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $date = isset($param['date']) ? $param['date'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $from = isset($param['from']) ? $param['from'] : '';

        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RATNAKAR_REMITTERS . ' as r', array('regn_fee', 'service_tax', 'txn_code', 'date_created', 'name', 'last_name'));
        if ($agentId > 0) {
            $select->where('r.by_agent_id=?', $agentId);
        }

        $select->where("r.status='" . STATUS_ACTIVE . "'");
        if ($to != '' && $from != '') {
        $select->where("date(r.date_created) BETWEEN '$from' AND '$to'");
        } else {
            $select->where("DATE(r.date_created) = ?", $date);
        }
//            echo $select.'<br><br>'; 
        return $this->_db->fetchAll($select);
        
    }
    public function getRemittersAmount($param) {
    	
        $agentId = isset($param['agentId']) ? $param['agentId'] : '';
        $from = $param['from'];
        $to = $param['to'];
        $select = $this->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTERS, array('count(*) as count','SUM(regn_fee) as remitter_fee', 'SUM(service_tax) as remitter_tax'))
                ->where("status = '" . STATUS_ACTIVE . "'");
        if ($agentId != '') {
            $select->where('by_agent_id=?', $agentId);
        }
        $select->where("date_created between '$from' AND '$to'");
        
        $remitters = $this->_db->fetchRow($select);
				
        return $remitters;
    }

    public function updateAmlRatRemitters(){
        $details = $this->_db->select()
                       ->from($this->_name,array('id','name', 'last_name'))
                       ->where ("last_name <>''")
                       ->where ("status ='".STATUS_ACTIVE."'")
                       ->where ("aml_status ='".STATUS_AML."'")
                       ->order('date_created ASC');

                       $results = $this->_db->fetchAll($details);
                       $reportsData = array();
                       foreach($results AS $data){
                           $select = $this->_db->select()
                                       ->from(DbTable::TABLE_AML_MASTER." AS a" , array('*'))
                                       ->where('a.first_name = "'. $data['name'] .'" AND a.second_name = "'. $data['last_name'].'"')
                                       ->Orwhere('a.full_name = ?', $data['name'].' '.$data['last_name'])
                                       ->Orwhere('trim(concat(a.first_name," ",a.second_name))   = trim("'. $data['name'] .' '. $data['last_name'].'")') 
                                       ->Orwhere('a.fake_names LIKE ?','%'.$data['name'].' '.$data['last_name'].'%');

                               $row = $this->_db->fetchRow($select);

                               if($row['id']){
                                   $this->_db->update($this->_name, array('aml_status' => STATUS_IS_AML), 'id='.$data['id']);	
                               } else {
                                   $this->_db->update($this->_name, array('aml_status' => STATUS_AML_UPDATE), 'id='.$data['id']);	
                               }
                          }
        return $reportsData;
    }
    
    public function updateAMLRemitter($param, $remitterId) {
        if ($remitterId > 0 && !empty($param)) {
            $resp = $this->_db->update(DbTable::TABLE_RATNAKAR_REMITTERS, $param, 'id="' . $remitterId . '"');
        }
        else
            throw new Exception('Unicode not found for update!');
    }

    public function getAllAgentList($param){
        $userType = $param['user_type'];
             $agentModel = new Agents();
                  
              switch($userType){
                    case DISTRIBUTOR_AGENT:
                         $agentList = $agentModel->getBCListing(
                         array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $param['user_id'], 'user_type' => DISTRIBUTOR_AGENT));
                        break;
                    case SUPER_AGENT:
                        
                        $agentList = array();
                        /*
                         * getSuperToDistributorListing: Getting all distrobutor id list
                         */
                         $distributorList = $agentModel->getSuperToDistributorListing(
                         array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $param['user_id'], 'user_type' => $param['user_type'],'ret_type' => 'arr'));
                        
                         if(!empty($distributorList)){
                              $userType = DISTRIBUTOR_AGENT;
                              foreach($distributorList as $key => $distributorID){
                                  $distributorAgentList = $agentModel->getBCListing(
                                  array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $distributorID, 'user_type' => $userType, 'ret_type' => 'arr'));
                                 
                                  if(!empty($distributorAgentList)){
                                  $agentList = array_merge($agentList,$distributorAgentList);
                                  
                                  
                                  }
                                  
                              }
                              $agentList = implode(',', $agentList);
                          }
                     break; 
                }
                return $agentList;
          
    }
	
	/*
     * Get Remitter details by mobile number
     */
    public function getRemitterStatus($mobile) {
        if (strlen($mobile) <> Mobile::$length || !is_numeric($mobile)) {
            throw new Exception("Invalid mobile number");
        }
        

        $select = $this->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTERS,array('id','concat(name," ",last_name) as name','profile_photo','product_id','unicode','ifsc_code','bank_account_number','bank_name','branch_name','branch_city','branch_address','bank_account_type','address','address_line2','city','state','pincode','mobile_country_code','mobile','dob','mother_maiden_name','email','legal_id','regn_fee','service_tax','txn_code','by_agent_id','by_ops_id','ip','date_created','date_modified','status'))
                ->where("mobile = '$mobile'");

        $res = $this->fetchRow($select);
        if (!empty($res)) {
            return $res;
        } else {
            throw new Exception("Mobile not registered");
        }
    }


       /*
     * getRatnakarDistMultiMIS
     */
    public function getRatnakarDistMultiMIS($param){
        $agentModel = new Agents();
        $detailsArrMain = array();
        if(isset($param['agent_id']) && $param['agent_id'] != ''){
        if($param['agent_id'] == 'all'){
            $agentList = $agentModel->getBCListing(
                        array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $param['dist_id'], 'user_type' => DISTRIBUTOR_AGENT, 'ret_type' => ''));
         
            }
            else{
                
                 $agentList = $param['agent_id']; 
          }
    }else{
           
            if(isset($param['user_type']) && $param['user_type'] != ''){
             $userType = $param['user_type'];
             $agentModel = new Agents();
              switch($userType){
                    case DISTRIBUTOR_AGENT:
                         $agentList = $agentModel->getBCListing(
                         array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $param['user_id'], 'user_type' => DISTRIBUTOR_AGENT));
                        break;
                  
                }
                
                 
            
            }
                  
         }
         
                
                  $param['agent_id_list'] = $agentList;
                  $detailsArrMain = $this->getRatnakarMIS($param);
             
         
        return $detailsArrMain;   
    }
    
    public function getRatnakarMIS($param){ 
        $agentUserModel = new AgentUser();
        if(isset($param['duration']) && $param['duration'] != ''){
            $dates = Util::getDurationDates($param['duration']);
            $param['from'] = $dates['from'];
            $param['to'] = $dates['to'];
            }
        
        
        $objRatnakarRemitRequest = new Remit_Ratnakar_Remittancerequest();
        
        $retData = $objRatnakarRemitRequest->getRemittanceCountandSum($param);
        
        $misDetailArr = array();
        if(isset($param['dist_report']) && $param['dist_report'] == FLAG_YES ){
         foreach($retData as $key => $misdata){
         $transactionDate = Util::returnDateFormatted($misdata['date_updated'], "d-m-Y", "Y-m-d", "-");
         $misDetailArr[$key]['date_updated'] = $transactionDate;       
         $misDetailArr[$key]['agent_code'] = $misdata['agent_code'];
         $misDetailArr[$key]['agent_name'] = $misdata['agent_name'];
         $misDetailArr[$key]['txn_count'] = $misdata['txn_count'];
         $misDetailArr[$key]['total_amount'] = $misdata['total_amount'];
        
           } 
          }
        else{
        foreach($retData as $key => $misdata){
        
         $agentDetails = $agentUserModel->getAgentCodeName($misdata['user_type'], $misdata['agent_id']) ;
         $transactionDate = Util::returnDateFormatted($misdata['date_updated'], "d-m-Y", "Y-m-d", "-");
         $misDetailArr[$key]['date_updated'] = $transactionDate; 
         $misDetailArr[$key]['dist_code'] = $agentDetails['dist_code'];
         $misDetailArr[$key]['dist_name'] = $agentDetails['dist_name'];
         $misDetailArr[$key]['agent_code'] = $misdata['agent_code'];
         $misDetailArr[$key]['agent_name'] = $misdata['agent_name'];
         $misDetailArr[$key]['txn_count'] = $misdata['txn_count'];
         $misDetailArr[$key]['total_amount'] = $misdata['total_amount'];
        
        }
        }
        return $misDetailArr ;
    }
    
     /*
     * getRatnakarMultiMIS
     */
    public function getRatnakarMultiMIS($param){
       
        $agentModel = new Agents();
        $detailsArrMain = array();
        if(isset($param['dist_id']) && $param['dist_id'] != ''){
        if(isset($param['agent_id']) && $param['agent_id'] != '' && $param['agent_id'] != 'all'){
            $agentList = $param['agent_id'];
            
        }
        else{
            $agentList = $agentModel->getBCListing(
                        array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $param['dist_id'], 'user_type' => DISTRIBUTOR_AGENT, 'ret_type' => ''));
         
            }
        }
        else{
           
            if(isset($param['user_type']) && $param['user_type'] != ''){
             $userType = $param['user_type'];
             $agentModel = new Agents();
                  
              switch($userType){
                    case DISTRIBUTOR_AGENT:
                         $agentList = $agentModel->getBCListing(
                         array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $param['user_id'], 'user_type' => DISTRIBUTOR_AGENT));
                        break;
                    case SUPER_AGENT:
                        $agentList = array();
                        /*
                         * getSuperToDistributorListing: Getting all distrobutor id list
                         */
                         $distributorList = $agentModel->getSuperToDistributorListing(
                         array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $param['user_id'], 'user_type' => $param['user_type'],'ret_type' => 'arr'));
                        
                         if(!empty($distributorList)){
                              $userType = DISTRIBUTOR_AGENT;
                              foreach($distributorList as $key => $distributorID){
                                  $distributorAgentList = $agentModel->getBCListing(
                                  array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $distributorID, 'user_type' => $userType, 'ret_type' => 'arr'));
                                  if(!empty($distributorAgentList)){
                                  $agentList = array_merge($agentList,$distributorAgentList);
                                 
                                  
                                  }
                                  
                              }
                             $agentList = implode(',', $agentList);  
                          }
                     break; 
                }
                
                 
            
            }
                  
         }
         
                 
                  $param['agent_id_list'] = $agentList;
                  $detailsArrMain = $this->getRatnakarMIS($param);
             
         
        return $detailsArrMain;   
    }
    
    /*
     * getRatnakarMultiRemittance
     */
    public function getRatnakarMultiRemittance($param){
        
        $agentModel = new Agents();
        $detailsArr = array();
        $detailsArrMain = array();
        if(isset($param['dist_id']) && $param['dist_id'] != ''){
        if(isset($param['agent_id']) && $param['agent_id'] != '' && $param['agent_id'] != 'all'){
           $detailsArrMain = $this->getRatnakarRemittance($param);
        }else{
          
            $agentList = $agentModel->getBCListing(
                        array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $param['dist_id'], 'user_type' => DISTRIBUTOR_AGENT, 'ret_type' => 'arr'));
            
            if(!empty($agentList)){
                foreach($agentList as $key => $agentID){
                  $param['agent_id'] = $agentID;
                  $detailsArr = $this->getRatnakarRemittance($param);
                  $detailsArrMain = array_merge($detailsArrMain,$detailsArr);
                    }
                }
            }
        }
        else{
            if(isset($param['user_type']) && $param['user_type'] != ''){
             $userType = $param['user_type'];
                        
              switch($userType){
                    case DISTRIBUTOR_AGENT:
                         $agentList = $agentModel->getBCListing(
                         array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $param['user_id'], 'user_type' => $param['user_type'], 'ret_type' => 'arr'));
                        break;
                    case SUPER_AGENT:
                        $agentList = array();
                        /*
                         * getSuperToDistributorListing: Getting all distrobutor id list
                         */
                         $distributorList = $agentModel->getSuperToDistributorListing(
                         array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $param['user_id'], 'user_type' => $param['user_type'], 'ret_type' => 'arr'));
                         if(!empty($distributorList)){
                              $userType = DISTRIBUTOR_AGENT;
                              foreach($distributorList as $key => $distributorID){
                                  $distributorAgentList = $agentModel->getBCListing(
                                  array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $distributorID, 'user_type' => $userType, 'ret_type' => 'arr'));
                                  if(!empty($distributorAgentList)){
                                  $agentList = array_merge($agentList,$distributorAgentList);
                                  }
                                  
                              }
                              
                          }
                     break; 
                }
                        
                 if(!empty($agentList)){
                    foreach($agentList as $key => $agentID){
                  $param['agent_id'] = $agentID;
                  $detailsArr = $this->getRatnakarRemittance($param);
                  $detailsArrMain = array_merge($detailsArrMain,$detailsArr);
                    }
                }
            
            }
                  
         }
        return $detailsArrMain;   
    }
    
     public function remitterExists($param) {
    	
        $mobile = isset($param['mobile']) ? $param['mobile'] : '';
        $select = $this->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTERS, array('id'))
                ->where("status = '" . STATUS_ACTIVE . "'");
              //  ->where("product_id = '0'");
        if ($mobile != '') {
            $select->where('mobile =?', $mobile);
        }
       
        $remitters = $this->_db->fetchRow($select);
				
        return $remitters;
    }

    public function checkEditDuplicateMobile($params,$mobile){
      
        $chkRemID = isset($params['remitter_id']) ? $params['remitter_id'] : '';
        $select = $this->select();
        $select->from($this->_name,array('id','mobile'));
       // $select->where('product_id = 0');
        $select->where('bank_id = ?', $params['bank_id']);
        $select->where('mobile = ?', $mobile);
        if($chkRemID !=''){
        $select->where('id != ?',$params['remitter_id']);
        }
        $select->where('status = ?', STATUS_ACTIVE); 
       // $select->where("status IN ('".STATUS_ACTIVE."', '".STATUS_BLOCKED."')");
       $rs = $this->fetchRow($select);
        if( !empty($rs) ){
            return TRUE;
        }
        else
            return FALSE;
   }
   
    public function getRemitterRegnfeeAll($param){
        $to = $param['to'];
        $from = $param['from'];
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : ''; 
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                DbTable::TABLE_RATNAKAR_REMITTERS . " as r", 
                    array(
                        'r.regn_fee as fee', 'r.service_tax' ,'DATE_FORMAT(r.date_created,"%d-%m-%Y") as txn_date','DATE(r.date_created) as date_created','r.status','r.txn_code'
                ));
        $select->joinLeft(
                DbTable::TABLE_AGENTS . " as a", "r.by_agent_id = a.id ", 
                    array(
                        'a.agent_code', 'concat(a.first_name," ",a.last_name) as name', 'a.user_type as agent_user_type'
                ));
        $select->joinLeft(
                DbTable::TABLE_AGENT_DETAILS . " as ad", "a.id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", 
                    array(
                        'ad.estab_city', 'ad.estab_pincode'
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
        $select->where("r.status = '" . STATUS_ACTIVE . "' OR r.status = '" . STATUS_INACTIVE . "' ");
        $select->where("r.date_created >= ?", $from);
        $select->where("r.date_created <= ?", $to); 
        
        if ($agentId != '') {
            $select->where('r.by_agent_id=?', $agentId);
        }
                
        //echo $select; exit();
        $row = $this->fetchAll($select); 
        return $row; 	 
    }
    
    /*
     * getRemitterRegistrationfee() remitter registration fee for an agent on a particular date for a product. This is called from Operation portal. Please do not modify this function
     */
    public function getRemitterRegistrationfee($param){
        $to = $param['to'];
        $from = $param['from'];
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : ''; 
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                DbTable::TABLE_RATNAKAR_REMITTERS . " as r", 
                    array(
                        'r.regn_fee as fee_amount', 'r.service_tax as service_tax_amount' ,'DATE_FORMAT(r.date_created,"%d-%m-%Y") as txn_date','DATE(r.date_created) as date_created','r.status as txn_status','r.txn_code', new Zend_Db_Expr("'".TXNTYPE_REMITTER_REGISTRATION."' as transaction_type_name"), new Zend_Db_Expr("'0.00' as transaction_amount"), new Zend_Db_Expr("0 as reversal_fee"), new Zend_Db_Expr("0 as reversal_service_tax"), new Zend_Db_Expr("'' as refund_txn_code"), new Zend_Db_Expr("'' as utr")
                ));
        $select->joinLeft(
                DbTable::TABLE_AGENTS . " as a", "r.by_agent_id = a.id ", 
                    array(
                        'a.agent_code', 'concat(a.first_name," ",a.last_name) as name', 'a.user_type as agent_user_type'
                ));
        $select->joinLeft(
                DbTable::TABLE_AGENT_DETAILS . " as ad", "a.id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", 
                    array(
                        'ad.estab_city', 'ad.estab_pincode'
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
        $select->where("r.status = '" . STATUS_ACTIVE . "' OR r.status = '" . STATUS_INACTIVE . "' ");
        $select->where("r.date_created >= ?", $from);
        $select->where("r.date_created <= ?", $to); 
        
        if ($agentId != '') {
            $select->where('r.by_agent_id=?', $agentId);
        }
                
        //echo $select; exit();
        return $this->fetchAll($select); 	 
    }
    
    /*
    getRemitterRegistrationRecon() gets inactive remiiters for the previous day
     */
    public function getRemitterRegistrationRecon(){
        $prevDate = date('Y-m-d', strtotime('-1 days'));
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                DbTable::TABLE_RATNAKAR_REMITTERS . " as r", 
                    array(
                        'r.regn_fee as fee_amount', 'r.service_tax as service_tax_amount' ,'DATE_FORMAT(r.date_created,"%d-%m-%Y") as txn_date','DATE_FORMAT(r.date_created,"%d-%m-%Y") as date_created','DATE_FORMAT(r.date_modified,"%d-%m-%Y") as date_updated','r.status as txn_status','r.txn_code', new Zend_Db_Expr("'".TXNTYPE_REMITTER_REGISTRATION."' as transaction_type_name"), new Zend_Db_Expr("'0.00' as transaction_amount"), new Zend_Db_Expr("'' as refund_txn_code"), new Zend_Db_Expr("'' as utr"), new Zend_Db_Expr("'' as card_number"), new Zend_Db_Expr("'' as crn"), 'concat(r.name," ",r.last_name) as remitter_name', 'r.mobile as remitter_mobile', new Zend_Db_Expr("'' as fund_holder"), new Zend_Db_Expr("'' as bank_account_number"), new Zend_Db_Expr("'' as neft_remarks"), new Zend_Db_Expr("'' as sender_msg"), new Zend_Db_Expr("'' as bene_name"), new Zend_Db_Expr("'' as bene_account_number")
                ));
        $select->joinLeft(
                DbTable::TABLE_AGENTS . " as a", "r.by_agent_id = a.id AND a.status = '" . STATUS_UNBLOCKED . "'", 
                    array(
                        'a.agent_code', 'concat(a.first_name," ",a.last_name) as name'
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
        $select->joinLeft(
                DbTable::TABLE_PRODUCTS . " as p", "r.product_id = p.id ", array('p.name as product_name'));
        $select->where("r.status = '" . STATUS_INACTIVE . "'");
        $select->where("DATE(r.date_created) = ?", $prevDate);
                 
        return $this->fetchAll($select); 	 
    }
    
     /* callEnrollRemitterSP() will call sp_EnrollRemitter stored procedure for enrolling remmitter
     */
    public function callEnrollRemitterSP($param) {
        try {
            //Enable DB Slave
            $db = Zend_Registry::get('dbconsumerAdapter');
            $rs = $db->query("CALL sp_ins_customer_creation('".$param['mobile']."','".$param['name']."', '".$param['password']."',  '".$param['email']."','".$param['ip']."','".$param['partner_ref_num']."')");  
            $rsLoad = $rs->fetch();
            $db->closeConnection();
            return $rsLoad;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
