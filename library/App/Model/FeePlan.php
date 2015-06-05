<?php
/**
 * Model that manages the Fee
 *
 * @package Operation_Models
 * @copyright transerv
 */

class FeePlan extends App_Model
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
    protected $_name = DbTable::TABLE_PLAN_FEE;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Privilege';
    
    /**
     * Define the relationship with another tables
     *
     * @var array
     */
    protected $_referenceMap = array();   
     
   public function getFeePlanSelect()
    {
        $select = $this->select();
        $select->from(DbTable::TABLE_PLAN_FEE,array('id','name'));
        $select->setIntegrityCheck(false);
        $bankArr =  $this->fetchAll($select);
        $dataArray = array();
        $dataArray = array('' => 'Select Fee Plan');
        foreach ($bankArr as $id => $val) {
            $dataArray[$val['id']] = $val['name'];
        }
        return $dataArray;
  
    }
    
     public function getTypecode()
    {
        $select = $this->select();
        $select->from(DbTable::TABLE_TRANSACTION_TYPE,array('typecode','name'))
                ->where('status= ?', STATUS_ACTIVE)
                ->where("is_comm = '".FLAG_YES."'")
                ->order('name');
        $select->setIntegrityCheck(false);
        $typecodeArr =  $this->fetchAll($select);
        $dataArray = array();
        $dataArray = array('' => 'Select Transaction Type');
        foreach ($typecodeArr as $id => $val) {
            $dataArray[$val['typecode']] = $val['name'];
        }
        return $dataArray;
  
    }
    
    
    public function checkname($name){
       $select = $this->select()    
                ->from(DbTable::TABLE_PLAN_FEE);
               
               $select->where('name = ?', $name);
               $select->where("status = '".STATUS_ACTIVE."'");
        //echo $select->__toString();       
        return $this->fetchRow($select);
      
  }
    
    /**
     * Retrieves all the products attached to
     * the specified master product
     * 
     * @param integer $resourceId
     * @access public
     * @return void
     */
    
    public function findAll ($page = 1,$paginate = NULL, $force = FALSE){

        $details = $this->select()
        ->setIntegrityCheck(false)
        ->from(DbTable::TABLE_PLAN_FEE, array('id', 'name', 'description', 'DATE_FORMAT(date_created, "%d-%m-%Y") as date_created'))
        ->order('name Asc');
      //echo $details; exit;
      
       return $this->_paginate($details, $page, $paginate);
        
    }
       
    public function add($data){
      
        $chkname = $this->checkname($data['name']);
          if (!empty($chkname))
             return 'name_dup';
        else {

            $res =  $this->insert($data);
             return $res;

        }
   }
   
   
    public function findItemsById($id =0 , $page = 1, $paginate = NULL)
    {
     
       
        $details = $this->select()
        ->from(DbTable::TABLE_FEE_ITEMS." as fi",array('fi.id as id', 'fi.plan_fee_id','fi.typecode_name','fi.typecode','fi.txn_flat','fi.txn_pcnt','fi.txn_min','fi.txn_max'))
        ->setIntegrityCheck(false)
        ->joinLeft(DbTable::TABLE_PLAN_FEE.' as pf', "fi.plan_fee_id = pf.id AND pf.status='".STATUS_ACTIVE."'", array())
        ->where("fi.plan_fee_id=?",$id)
        ->where("fi.status=?",STATUS_ACTIVE)
        ->order(array("fi.typecode_name ASC"));
         
        //echo $details; exit;
        return $this->_paginate($details, $page, $paginate);
    }
   
    
    
    public function finddetailsById($id){
      $select = $this->select()    
                ->from(DbTable::TABLE_PLAN_FEE);
               
               $select->where('id= ?', $id);
               
        return $this->fetchRow($select);
  }
  
  public function getBindPlanItems(){
         
         $select = $this->_db->select()
                  ->from("t_bind_agent_product_commission", array('plan_fee_id'))
                  ->group('plan_fee_id');
        //echo $select->__toString();exit;

        $products = $this->_db->fetchAll($select);
        $dataArray = array();
        foreach ($products as $val) {
            $dataArray[] = $val['plan_fee_id'];
        }
        return $dataArray;

    }
  
    
      
     public function itemsById($id)
    {
                $details = $this->_db->select()
                ->from("t_fee_items as fi",array('fi.id as id','fi.plan_fee_id','fi.typecode_name','fi.typecode','fi.txn_flat','fi.txn_pcnt','fi.txn_min', 'fi.txn_max'))
                ->joinLeft(DbTable::TABLE_PLAN_FEE.' as pf',"fi.plan_fee_id = pf.id ",array('pf.id as fid','pf.name'))
                ->where("fi.id=?",$id);
                
        //echo $details->__toString();        exit;
        return $this->_db->fetchRow($details);
    }
   
     public function chkDuplicateTypecode($typecode,$id)
    {
        $select = $this->_db->select()
                ->from(DbTable::TABLE_FEE_ITEMS)
                ->where("plan_fee_id=".$id)
                ->where('status= ?', STATUS_ACTIVE)
                ->where("typecode = '".$typecode."'");
                
        
        $typecodeArr = $this->_db->fetchRow($select);
        if (empty($typecodeArr))
            return TRUE;
        else
            return FALSE;
 
       }
  public function insertItem($data){
      
      $insert = $this->_db->insert(DbTable::TABLE_FEE_ITEMS,$data);
      if($insert > 0)
          return TRUE;
  }
   public function updateItem($id){
      
      $data = array('status'=> STATUS_INACTIVE);
      $update = $this->_db->update(DbTable::TABLE_FEE_ITEMS,$data,"id=$id");
      if($update > 0)
          return TRUE;
  }
  
     public function deleteItem($id){
      
      $data = array('status'=> STATUS_DELETED);
      $update = $this->_db->update(DbTable::TABLE_FEE_ITEMS,$data,"id=$id");
      if($update > 0)
          return TRUE;
  }
  
  public function getTypecodeDetails($typecode)
    {
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_TRANSACTION_TYPE)
                ->where("typecode='".$typecode."'");
      //          ->limit(1);
      
        return $this->_db->fetchRow($select);
     }
     
     
     
      public function getRemitterRegistrationFee($productId, $agentId){
         if($productId>0 && $agentId>0){
             $curdate = date("Y-m-d");
         $select = $this->_db->select()
                  ->from(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION." as bapc", array(''))
                  ->joinLeft(DbTable::TABLE_PLAN_FEE.' as pf',"bapc.plan_fee_id = pf.id AND pf.status='".STATUS_ACTIVE."'", array(''))
                  ->joinLeft(DbTable::TABLE_FEE_ITEMS.' as fi',"bapc.plan_fee_id = fi.plan_fee_id AND fi.typecode = '".TXNTYPE_REMITTER_REGISTRATION."' AND fi.status='".STATUS_ACTIVE."'", array('fi.txn_flat'))
                  ->where("bapc.product_id='".$productId."'")
                  ->where("bapc.agent_id='".$agentId."'")
                  ->where("'".$curdate."' >= date_start AND ('".$curdate."' <= date_end OR date_end = '0000-00-00')");
//        echo $select->__toString();exit;

        $feeDetails = $this->_db->fetchRow($select);
        
        return $feeDetails;
      }
    }
    /*
     * Get Remitter Fee for Fund Transfer
     */
    
    public function getRemitterFee($productId, $agentId){
         if($productId>0 && $agentId>0){
         $curdate = date("Y-m-d");
         $select = $this->_db->select()
                  ->from(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION." as bapc", array(''))
                  ->joinLeft(DbTable::TABLE_PLAN_FEE.' as pf',"bapc.plan_fee_id = pf.id AND pf.status='".STATUS_ACTIVE."'", array(''))
                  ->joinLeft(DbTable::TABLE_FEE_ITEMS.' as fi',"bapc.plan_fee_id = fi.plan_fee_id  AND fi.status='".STATUS_ACTIVE."'")
                  ->where("'".$curdate."' >= date_start AND ('".$curdate."' <= date_end OR date_end = '0000-00-00')")
                  ->where("bapc.product_id='".$productId."'")
                  ->where("bapc.agent_id='".$agentId."'");
       // echo $select->__toString();exit;

        $feeDetails = $this->_db->fetchAll($select);
        
        return $feeDetails;
      }
    }
    
    public function getFeeStructureDetails($productId, $txnCode) {
    	$select = $this->_db->select()
    	->from(DbTable::TABLE_FEE_STRUCTURE, array('f_min_cum_amount','f_max_cum_amount','f_is_pct','f_fee_rate','f_min','f_max'))
    	->where('f_product_id=?', $productId)
    	->where('f_txn_type_code=?', $txnCode)
    	->where('f_status=?', STATUS_ACTIVE);
    	$rows = $this->_db->fetchAll($select);
    	
    	return $rows;
    }
    
    /*
     * getRemitterRefundFee() will return Remitter Refund Fee for refund amount
     */
    
    public function getRemitterRefundFee($productId, $agentId){
         if($productId>0 && $agentId>0){
         $curdate = date("Y-m-d");
         $select = $this->_db->select()
                  ->from(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION." as bapc", array(''))
                  ->joinLeft(DbTable::TABLE_PLAN_FEE.' as pf',"bapc.plan_fee_id = pf.id AND pf.status='".STATUS_ACTIVE."'", array(''))
                  ->joinLeft(DbTable::TABLE_FEE_ITEMS.' as fi',"bapc.plan_fee_id = fi.plan_fee_id AND fi.status='".STATUS_ACTIVE."' AND fi.typecode='".TXNTYPE_REMITTANCE_REFUND_FEE."'", array('fi.txn_flat','fi.txn_pcnt','fi.txn_min', 'fi.txn_max'))
                  ->where("'".$curdate."' >= date_start AND ('".$curdate."' <= date_end OR date_end = '0000-00-00')")
                  ->where("bapc.product_id='".$productId."'")
                  ->where("bapc.agent_id='".$agentId."'");
//        echo $select->__toString();//exit;

        $feeDetails = $this->_db->fetchRow($select);
        
        return $feeDetails;
      }
    }

/*
     * getAgentFeeArray() will return Agent fee details over a duration
     */
    

    public function getAgentFeeArray($param){
       //Enable DB Slave
       $this->_enableDbSlave();
            
       $remitRequestModel = new Remit_Remittancerequest();
       $remitterRegnModel = new Remit_Remitter();
       $transactiontype = new Transactiontype();
       $typecodeArr = $transactiontype->getTypecodeArray();
       $k=0;
       $agentArr = array();
            
       
        // Get Remitter registrations Fee, Transaction type code = TXNTYPE_REMITTER_REGISTRATION
        $agentRMRG = Util::toArray($remitterRegnModel->getRemitterRegnfee($param));
        if(!empty($agentRMRG)){
            foreach ($agentRMRG as $rgfee) {
                $agentArr[$k] =array(
                    'date_formatted' => $rgfee['txn_date'], 
                    'agent_code' => $rgfee['agent_code'],
                    'name' => $rgfee['name'],
                    'estab_city' => $rgfee['estab_city'],
                    'estab_pincode' => $rgfee['estab_pincode'],
                    'txn_code' => $rgfee['txn_code'],
                    'transaction_type_name' => $typecodeArr[TXNTYPE_REMITTER_REGISTRATION],
                    'transaction_amount' => '0.00',
                    'fee_amount' => $rgfee['fee'],
                    'service_tax_amount' => $rgfee['service_tax'],
                    'reversal_fee' => 0,
                    'reversal_service_tax' => 0,
                    'refund_txn_code' => $rgfee['refund_txn_code'],
                    'txn_status'    => $rgfee['status'],
                    'utr' => $rgfee['utr'],
                    'sup_dist_code' => $rgfee['sup_dist_code'] ,
                    'sup_dist_name' => $rgfee['sup_dist_name'],
                    'dist_code' => $rgfee['dist_code'], 
                    'dist_name' => $rgfee['dist_name'] 
                );
                $k++;                
            }
        }
        // Get Remittance Fee, typecode =  TXNTYPE_REMITTANCE_FEE
        $agentREMT = Util::toArray($remitRequestModel->getRemittancefee($param));
        
        if(!empty($agentREMT)){
            foreach($agentREMT as $rmfee) { 
                $agentArr[$k] = array(
                    'date_formatted' => $rmfee['txn_date'], 
                    'agent_code' => $rmfee['agent_code'],
                    'name' => $rmfee['name'],
                    'estab_city' => $rmfee['estab_city'],
                    'estab_pincode' => $rmfee['estab_pincode'],
                    'txn_code' => $rmfee['txn_code'],
                    'transaction_type_name' => $typecodeArr[TXNTYPE_REMITTANCE_FEE],
                    'transaction_amount' => $rmfee['amount'],
                    'fee_amount' => $rmfee['fee'],
                    'service_tax_amount' => $rmfee['service_tax'],
                    'reversal_fee' => 0,
                    'reversal_service_tax' => 0,
                    'refund_txn_code' => $rmfee['refund_txn_code'],
                    'txn_status'    => $rmfee['status'],
                    'utr' => $rmfee['utr'],
                    'sup_dist_code' => $rmfee['sup_dist_code'] ,
                    'sup_dist_name' => $rmfee['sup_dist_name'],
                    'dist_code' => $rmfee['dist_code'], 
                    'dist_name' => $rmfee['dist_name']
                );
                $k++;
            }
        }
        
        
        // Get Remittance refund Fee, typecode = TXNTYPE_REMITTANCE_REFUND_FEE
        $agentRMFE = Util::toArray($remitRequestModel->getRemitRefundfee($param));
        if(!empty($agentRMFE)){
            foreach($agentRMFE as $rffee) {
                $agentArr[$k] = array(
                    'date_formatted' => $rffee['txn_date'],
                    'agent_code' => $rffee['agent_code'],
                    'name' => $rffee['name'],
                    'estab_city' => $rffee['estab_city'],
                    'estab_pincode' => $rffee['estab_pincode'],
                    'txn_code' => $rffee['txn_code'],
                    'transaction_type_name' => $typecodeArr[TXNTYPE_REMITTANCE_REFUND_FEE],
                    'transaction_amount' => $rffee['amount'],
                    'fee_amount' => $rffee['fee'],
                    'service_tax_amount' => $rffee['service_tax'],
                    'reversal_fee' => $rffee['reversal_fee'],
                    'reversal_service_tax' => $rffee['reversal_service_tax'],
                    'refund_txn_code' => $rffee['refund_txn_code'],
                    'txn_status'    => $rffee['status'],
                    'utr' => $rffee['utr'],
                    'sup_dist_code' => $rmfee['sup_dist_code'] ,
                    'sup_dist_name' => $rmfee['sup_dist_name'],
                    'dist_code' => $rmfee['dist_code'], 
                    'dist_name' => $rmfee['dist_name']
                );
                $k++;
            }
        }
        
        //Disable DB Slave
        $this->_disableDbSlave();   
        return $agentArr;
        
    }
    
   
    /* get Refund/Reversed Transaction Reference No for Remit Remitters */
    public function getRefundRevTxnRefNo($rmid, $agent_id)
    {

            $select = $this->_db->select();
            $select->from(DbTable::TABLE_KOTAK_REMITTANCE_REFUND , array('txn_code as refund_txn_code'));
            
            $select->where("remitter_id = ?", $rmid);
            $select->where("agent_id = ?", $agent_id);

            return $this->_db->fetchRow($select);
    }
    
    /* get Refund/Reversed Transaction Reference No for Remit Remitters , we need product id for getting the remitter table name */
    public function getRemitRefundRevTxnRefNo($rmid, $agent_id,$productUnicode)
    {
            $bankProductUnicodeArr = Util::bankProductRemitUnicodesArray();
             $tableName = '';
       switch ($productUnicode) {
            case $bankProductUnicodeArr['0']:
                $tableName = DbTable::TABLE_REMITTANCE_REFUND;
                break;
            case $bankProductUnicodeArr['1']:
                $tableName = DbTable::TABLE_KOTAK_REMITTANCE_REFUND;
                break;
            case $bankProductUnicodeArr['2']:
                $tableName = DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND;
                break;
            default :
                $tableName = DbTable::TABLE_KOTAK_REMITTANCE_REFUND;
        }
       
            $select = $this->_db->select();
            $select->from($tableName , array('txn_code as refund_txn_code'));
            
            $select->where("remitter_id = ?", $rmid);
            $select->where("agent_id = ?", $agent_id);

            return $this->_db->fetchRow($select);
    }
    
    /* Get Refund/Reversed Transaction Reference No for a Remitter Request */
    public function getRefundTxnRefNo($rmid)
    {
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_KOTAK_REMITTANCE_REFUND , array('txn_code as refund_txn_code'));
            
            $select->where("remittance_request_id = ?", $rmid);

            return $this->_db->fetchRow($select);
    }
    
    /* Get Refund/Reversed Transaction Reference No for a Remitter Request , we need product id for getting the remitter table name*/
    public function getRemitRefundTxnRefNo($rmid,$productUnicode)
    {
       $bankProductUnicodeArr = Util::bankProductRemitUnicodesArray();
       $tableName = '';
       switch ($productUnicode) {
            case $bankProductUnicodeArr['0']:
                $tableName = DbTable::TABLE_REMITTANCE_REFUND;
                break;
            case $bankProductUnicodeArr['1']:
                $tableName = DbTable::TABLE_KOTAK_REMITTANCE_REFUND;
                break;
            case $bankProductUnicodeArr['2']:
                $tableName = DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND;
                break;
            default :
                $tableName = DbTable::TABLE_KOTAK_REMITTANCE_REFUND;
        }
            $select = $this->_db->select();
            $select->from($tableName , array('txn_code as refund_txn_code'));
            
            $select->where("remittance_request_id = ?", $rmid);

            return $this->_db->fetchRow($select);
    }
    
    /* getAgentFeePlanLogs() will return the fee plan changes details as logs.
     * Params/Fileters :- agent id and duration
     */
    
     public function getAgentFeePlanLogs($params){
        $logDuration = $params['duration']; 
        $agentId = $params['agent_id']; 
        $dates = Util::getDurationAllDates($logDuration);
        $totalDates = count($dates);
        $retLogData = array();
       
        if(!empty($dates)){
            
            foreach($dates as $queryDate){
                
                $to = isset($queryDate['to'])?$queryDate['to']:'';
                $queryDateArr = explode(' ', $to);
                $queryDate = $queryDateArr[0];
              
                
                /**** getting agent fee plans ****/
                
                $select = $this->_db->select();
                $select->from(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION." as bapc", array('bapc.plan_fee_id', 'bapc.by_ops_id'));
                $select->joinLeft(DbTable::TABLE_PLAN_FEE.' as pf',"bapc.plan_fee_id = pf.id AND pf.status='".STATUS_ACTIVE."'", array('name as fee_plan_name', 'description as fee_plan_description'));
                $select->joinLeft(DbTable::TABLE_PRODUCTS.' as p',"bapc.product_id = p.id", array('name as product_name', 'bank_id'));
                $select->joinLeft(DbTable::TABLE_BANK.' as b',"p.bank_id = b.id", array('name as bank_name'));
                $select->joinLeft(DbTable::TABLE_OPERATION_USERS." as ou", "bapc.by_ops_id=ou.id", 'concat(ou.firstname," ",ou.lastname) as by_ops_name');
                $select->joinLeft(DbTable::TABLE_AGENTS.' as a', "a.id=".$agentId, array('a.agent_code', 'a.id as agent_id', 'concat(a.first_name," ",a.last_name) as agent_name'));
                $select->joinLeft(DbTable::TABLE_AGENT_DETAILS.' as ad', "a.id=ad.agent_id AND ad.status='".STATUS_ACTIVE."'", array('ad.estab_city', 'ad.estab_pincode'));
                $select->where("'".$queryDate."' >= bapc.date_start AND ('".$queryDate."' <= bapc.date_end OR bapc.date_end = '0000-00-00')");
                $select->where("bapc.agent_id='".$agentId."'");
                //echo $select->__toString();exit;

                $feeLogs = $this->_db->fetchAll($select);
                if(!empty($feeLogs)){
                    $totalLogs = count($feeLogs);
                    for($i=0;$i<$totalLogs;$i++){
                        $feeLogs[$i]['date'] = $queryDate;
                    }
                    $retLogData = array_merge($retLogData, $feeLogs);
                }

          } // for each loop
     } else return array(); // date check if
     
     return $retLogData;
  }
    
  
    /*
     * getAgentFeeArray() will return Agent Wise fee details over a duration
     */

    public function getAgentWiseFeeArray($param){
        
        $agentArr = $this->getAgentFeeArray($param);        
        return $agentArr;
        /*
	//set_time_limit(150);
        //ini_set('memory_limit', '200M');
       $arrDates = Util::getDaysArr($param['from'], $param['to']);
       $agentArray = array();
       $remitRequestModel = new Remit_Remittancerequest();
       $remitterRegnModel = new Remit_Remitter();
       $transactiontype = new Transactiontype();
       $typecodeArr = $transactiontype->getTypecodeArray();
       $k=0;
       $agentArr = array();
       $agentUser = new AgentUser();
       foreach ($arrDates as $date) {
        $param['date'] = $date; 
            // Get Remitter registrations Fee, typecode = TXNTYPE_REMITTER_REGISTRATION
        $agentRMRG = Util::toArray($remitterRegnModel->getRemitterRegnfee($param));
       
        if (!empty($agentRMRG)) {
           foreach ($agentRMRG as $rmrg) {
               
                $agentType = $agentUser->getAgentCodeName($rmrg['agent_user_type'], $rmrg['agent_id']);
                
                $agentArr[$k] = array (
                'date_formatted' => Util::returnDateFormatted($date, "Y-m-d", "d-m-Y", "-"),
                'agent_code' => $rmrg['agent_code'],
                'name' => $rmrg['name'],
                'estab_city' => $rmrg['estab_city'],
                'estab_pincode' => $rmrg['estab_pincode'],
                'txn_code' => $rmrg['txn_code'],
                'transaction_type_name' => $typecodeArr[TXNTYPE_REMITTER_REGISTRATION],
                'transaction_amount' => '0.00',
                'fee_amount' => $rmrg['fee'],
                'service_tax_amount' => $rmrg['service_tax'],       
                'reversal_fee' => 0,
                'reversal_service_tax' => 0,
                'txn_status'    => $rmrg['txn_status'],
                'utr'    => $rmrg['utr']
                 );
                
                if(!empty($agentType))
                {
                    $agentArr[$k] = array_merge($agentArr[$k], $agentType);
                }
                
                $k++;
                
            }
            
        }
        
        // Get Remittance Fee, typecode =  TXNTYPE_REMITTANCE_FEE
        $agentREMT = Util::toArray($remitRequestModel->getRemittancefee($param));
       
        if (!empty($agentREMT)) {
            
            foreach ($agentREMT as $remt) {
                
                $agentType = $agentUser->getAgentCodeName($remt['agent_user_type'], $remt['agent_id']);

                $agentArr[$k] = array (
                'date_formatted' => Util::returnDateFormatted($date, "Y-m-d", "d-m-Y", "-"),
                'agent_code' => $remt['agent_code'],
                'name' => $remt['name'],
                'estab_city' => $remt['estab_city'],
                'estab_pincode' => $remt['estab_pincode'],
                'txn_code' => $remt['txn_code'],
                'transaction_type_name' => $typecodeArr[TXNTYPE_REMITTANCE_FEE],
                'transaction_amount' => $remt['amount'],
                'fee_amount' => $remt['fee'],
                'service_tax_amount' => $remt['service_tax'],
                'reversal_fee' => 0,
                'reversal_service_tax' => 0,
                'txn_status'    => $remt['txn_status'],
                'utr' => $remt['utr'] 
                 );
               
                if(!empty($agentType))
                {
                    $agentArr[$k] = array_merge($agentArr[$k], $agentType);
                }
               
                $k++;
            }
        }
        

        // Get Remittance refund Fee, typecode = TXNTYPE_REMITTANCE_REFUND_FEE
        $agentRMFE = Util::toArray($remitRequestModel->getRemitRefundfee($param));
       
        if (!empty($agentRMFE)) {
           foreach ($agentRMFE as $rmfe) {
                
                $agentType = $agentUser->getAgentCodeName($rmfe['agent_user_type'], $rmfe['agent_id']);
                
                $agentArr[$k] = array (
                'date_formatted' => Util::returnDateFormatted($date, "Y-m-d", "d-m-Y", "-"),
                'agent_code' => $rmfe['agent_code'],
                'name' => $rmfe['name'],
                'estab_city' => $rmfe['estab_city'],
                'estab_pincode' => $rmfe['estab_pincode'],
                'txn_code' => $rmfe['tran_ref_num'],
                'transaction_type_name' => $typecodeArr[TXNTYPE_REMITTANCE_REFUND_FEE],
                'transaction_amount' => $rmfe['amount'],
                'fee_amount' => $rmfe['fee'],
                'service_tax_amount' => $rmfe['service_tax'],
                'reversal_fee' => $rmfe['reversal_fee'],
                'reversal_service_tax' => $rmfe['reversal_service_tax'],
                'txn_status'    => $rmfe['txn_status'],
                'utr' => $rmfe['utr'] 
                 );
               
                if(!empty($agentType))
                {
                    $agentArr[$k] = array_merge($agentArr[$k], $agentType);
                }
                
                $k++;
            }
        }
         
        } //foreach($arrDates as $date)
        
        return $agentArr; 
        */
    }
    
      public function getCorpRemitterRegistrationFee($productId, $corporateId){
         if($productId>0 && $agentId>0){
             $curdate = date("Y-m-d");
         $select = $this->_db->select()
                  ->from(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION." as bapc", array(''))
                  ->joinLeft(DbTable::TABLE_PLAN_FEE.' as pf',"bapc.plan_fee_id = pf.id AND pf.status='".STATUS_ACTIVE."'", array(''))
                  ->joinLeft(DbTable::TABLE_FEE_ITEMS.' as fi',"bapc.plan_fee_id = fi.plan_fee_id AND fi.typecode = '".TXNTYPE_REMITTER_REGISTRATION."' AND fi.status='".STATUS_ACTIVE."'", array('fi.txn_flat'))
                  ->where("bapc.product_id='".$productId."'")
                  ->where("bapc.corporate_id='".$corporateId."'")
                  ->where("'".$curdate."' >= date_start AND ('".$curdate."' <= date_end OR date_end = '0000-00-00')");
//        echo $select->__toString();exit;

        $feeDetails = $this->_db->fetchRow($select);
        
        return $feeDetails;
      }
    }
    
    /*
     * getAgentFee($param) function gets Agent fee over a duration. This is called from Operation portal. Please do not modify this function
     */
    public function getAgentFee($param){
       //Enable DB Slave
       $this->_enableDbSlave();
            
       $remitRequestModel = new Remit_Remittancerequest();
       $remitterRegnModel = new Remit_Remitter();
       $remitloadModel = new Remit_Remitter();
       
       $agentArr = array();
       
        // Get Remitter registrations Fee, Transaction type code = TXNTYPE_REMITTER_REGISTRATION
        $agentRMRG = Util::toArray($remitterRegnModel->getRemitterRegnfeeOps($param));
        
        // Get Remittance Fee, typecode =  TXNTYPE_REMITTANCE_FEE
        $agentREMT = Util::toArray($remitRequestModel->getRemittancefeeOps($param));
        
        // Get Remittance refund Fee, typecode = TXNTYPE_REMITTANCE_REFUND_FEE
        $agentRMFE = Util::toArray($remitRequestModel->getRemitRefundfeeOps($param));
        
        // Get Remitter load Fee, typecode = TXNTYPE_REMITTANCE_FEE
        $agentCDRL = Util::toArray($remitterRegnModel->getRemitterLoadfeeOps($param));


        //Disable DB Slave
        $this->_disableDbSlave();
        
        return array_merge($agentRMRG, $agentREMT, $agentRMFE, $agentCDRL);
    }
    
    public function exportAgentFee($param) {
        $data = $this->getAgentFee($param);
        $retData = array();
        $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");

        if (!empty($data)) {

            foreach ($data as $key => $data) {
                $retData[$key]['txn_date'] = $data['txn_date'];                
                $retData[$key]['sup_dist_code'] = $data['sup_dist_code'];
                $retData[$key]['sup_dist_name'] = $data['sup_dist_name'];
                $retData[$key]['dist_code'] = $data['dist_code'];
                $retData[$key]['dist_name'] = $data['dist_name'];
                $retData[$key]['agent_code'] = $data['agent_code'];
                $retData[$key]['agent_name'] = $data['name'];
                $retData[$key]['estab_city'] = $data['estab_city'];
                $retData[$key]['estab_pincode'] = $data['estab_pincode'];
                $retData[$key]['txn_code'] = $data['txn_code'];
                $retData[$key]['refund_txn_code'] = $data['refund_txn_code'];
                $retData[$key]['txn_type'] = $TXN_TYPE_LABELS[$data['transaction_type_name']];
                $retData[$key]['amount'] = $data['transaction_amount'];
                $retData[$key]['fee_amount'] = $data['fee_amount'];
                $retData[$key]['service_tax_amount'] = $data['service_tax_amount'];
                $retData[$key]['reversal_fee'] = $data['reversal_fee'];
                $retData[$key]['reversal_service_tax'] = $data['reversal_service_tax'];
                $retData[$key]['utr'] = $data['utr'];
                $retData[$key]['txn_status'] = $data['txn_status'];
            }
        }
        return $retData;
    }
}
