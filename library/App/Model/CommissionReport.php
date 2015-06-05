<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class CommissionReport extends App_Model
{
    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    //protected $_primary = 'id';
    
    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_COMMISSION_REPORT;
    
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
    
    
   public function calculateCommission($param)
    { 
       
       $agentId = $param['agent_id'];
       $arrDates = Util::getDaysArr($param['from'], $param['to']);

       /**** initialising class object ****/
       $bindModel = new BindAgentProductCommission();
       $txnModel = new Transactiontype();
       $objAgent = new Agents();
       $objCardLoads = new CardLoads();
       $objRemitter = new Remit_Remitter();
       $objRemittanceRequest = new Remit_Remittancerequest();
       $ratnakarRemitModel = new Remit_Ratnakar_Remittancerequest();
       /**** initialising class object over here ****/
       
       $agentDetail = $objAgent->findById($agentId);
       $commArr = array();
       $i = 0;
       $agentSucToFail= array();
       // on date basis fetching and calculating commission
       foreach($arrDates as $date)
       {
           $this->clearPrevAgentComm(array('date' => $date,
                   'agent_id'=>$agentId));
            $commPlan = $bindModel->getAgentBinding($agentId, $date);
            foreach($commPlan as $plan)
            {
                
                if($plan['typecode'] == TXNTYPE_FIRST_LOAD || $plan['typecode'] == TXNTYPE_CARD_RELOAD || $plan['typecode'] == TXNTYPE_REMITTER_REGISTRATION || $plan['typecode'] == TXNTYPE_REMITTANCE_FEE || $plan['typecode'] == TXNTYPE_REMITTANCE_REFUND_FEE )
                {
                    $param['agent_id'] = $agentId;
                    $param['product_id'] = $plan['product_id'];
                    $param['date'] = $date;
                    $param['bank_unicode'] = $plan['bank_unicode'];
                    $paramRemit = $param;
                    $param['txn_type'] = $plan['typecode'];
             
                    /**** getting agent load/reload/remitter reg fee / remittance fee / remittance refund fee ****/

			if(isset($param['txn_id'])){
                            //get data for the individual txn id only
				$paramRemit['txn_id']=$param['txn_id'];
				error_log('Getting agent details in CommissionReport');
                             $agentLoads = $objRemittanceRequest->getAgentRemittanceDetails($paramRemit);
                             //$agentSucToFail = $ratnakarRemitModel->getAgentRemittanceSuccessToFailure($paramRemit);
                    }else if(isset($param['refund_txn_id'])){
                                $paramRemit['txn_id']=$param['refund_txn_id'];

                            //get data for the individual txn id only
                            $agentLoads = $objRemittanceRequest->getAgentRemittanceDetails($paramRemit);
                    }else{
 
                    switch($param['txn_type']) 
                    {
                        case TXNTYPE_FIRST_LOAD:
                        case TXNTYPE_CARD_RELOAD:
                             $agentLoads = $objCardLoads->getAgentCardLoads($param);
                             break;
                        case TXNTYPE_REMITTER_REGISTRATION:
                             $agentLoads = $objRemitter->getAgentRemitterRegnFee($paramRemit);
                             break;
                   
                            case TXNTYPE_REMITTANCE_FEE:
                            	if($objAgent->isAgentADistributorOrSuperDistributor($agentId)){
                            		$agentLoads = $objRemittanceRequest->getAgentRemittanceDetails($paramRemit);
                            		$agentSucToFail = $ratnakarRemitModel->getAgentRemittanceSuccessToFailure($paramRemit);
                            	}
                                 break;
                            case TXNTYPE_REMITTANCE_REFUND_FEE:
                            	if($objAgent->isAgentADistributorOrSuperDistributor($agentId)){
                                 $agentLoads = $objRemittanceRequest->getAgentRemittanceRefundDetails($paramRemit);
                            	}
                                 break;
                            default:
                                 $agentLoads = array();
                                 break;
 
		   } 
		}
                    
                    /**** getting agent load/reload/remitter reg fee / remittance fee / remittance refund fee over here ****/
//                   echo "<pre>";print_r($agentLoads);exit;
                    
                    $txnTypeDetail = $txnModel->finddetailsById($param['txn_type']);
                    foreach ($agentLoads as $loads)
                    {
			error_log('inside calculations');
                        $remarks = '';
                        $commAmount = 0;
                        
                        if($plan['typecode'] == TXNTYPE_REMITTER_REGISTRATION || $plan['typecode'] == TXNTYPE_REMITTANCE_FEE || $plan['typecode'] == TXNTYPE_REMITTANCE_REFUND_FEE)
                        {
                            if($plan['typecode'] == TXNTYPE_REMITTER_REGISTRATION){
                                $loads['transaction_amount'] = 0;
                            }
                            $plan['amount'] = $loads['transaction_fee'];
                        }
                        else if($plan['typecode'] == TXNTYPE_CARD_RELOAD){
                           $plan['amount'] = $loads['transaction_fee'];  
                        }
                        else  // if($plan['typecode'] == TXNTYPE_FIRST_LOAD || $plan['typecode'] == TXNTYPE_CARD_RELOAD)
                        {
                            $plan['amount'] = $loads['transaction_amount'];
                            $loads['transaction_fee'] = 0;
                            $loads['transaction_service_tax'] = 0;
                        }
                        $plan['return_type'] = TYPE_COMMISSION;
                        $commAmount = Util::calculateFee($plan);
                        if ($plan['txn_flat'] == 0){
                            $calculated = ($plan['txn_pcnt'] * $plan['amount'])/100;
                            $remarks = "Commission calculated: ".$calculated.". Min Commission: ".$plan['txn_min'].". Max Commission: ".$plan['txn_max'];
                        }
                        if($commAmount > 0)
                        {
                            $commArr[$i]['agent_id'] = $agentId;
                            $commArr[$i]['agent_code'] = $agentDetail['agent_code'];
                            $commArr[$i]['name'] = $agentDetail['name'];
                            $commArr[$i]['estab_city'] = ($agentDetail['estab_city']!= '')?$agentDetail['estab_city']:$agentDetail['res_city'];
                            $commArr[$i]['estab_pincode'] = ($agentDetail['estab_pincode']!= '')?$agentDetail['estab_pincode']:$agentDetail['res_pincode'];
                            $commArr[$i]['date'] = $date;
                            $commArr[$i]['date_formatted'] = Util::returnDateFormatted($date, "Y-m-d", "d-m-Y", "-");
                            $commArr[$i]['comm_amount'] = $commAmount;
                            $commArr[$i]['product_id'] = $plan['product_id'];
                            $commArr[$i]['product_code'] = $plan['product_code'];
                            $commArr[$i]['product_name'] = $plan['product_name'];
                            $commArr[$i]['plan_commission_id'] = $plan['plan_commission_id'];
                            $commArr[$i]['plan_commission_name'] = $plan['plan_commission_name'];
                            $commArr[$i]['commission_rate'] = ($plan['txn_flat'] > 0)?$plan['txn_flat']:$plan['txn_pcnt'].'%';
                            $commArr[$i]['transaction_type'] = $plan['typecode'];
                            $commArr[$i]['bank_name'] = $plan['bank_name'];
                            $commArr[$i]['transaction_type_name'] = $txnTypeDetail['name'];
                            $commArr[$i]['transaction_amount'] = $loads['transaction_amount'];
                            $commArr[$i]['transaction_fee'] = $loads['transaction_fee'];
                            $commArr[$i]['transaction_service_tax'] = $loads['transaction_service_tax'];
                            $commArr[$i]['transaction_ref_no'] = $loads['transaction_ref_no'];
                            $commArr[$i]['remarks'] = $remarks;
                            $i++;
                        }
                    }
                    
                    if(!empty($agentSucToFail)){
                    foreach ($agentSucToFail as $loads)
                    {
                        $remarks = '';
                        $commAmount = 0;
                                                 
                        $plan['amount'] = $loads['transaction_fee'];
                                              
                        $plan['return_type'] = TYPE_COMMISSION;
                        $commAmount = Util::calculateFee($plan);
                        if ($plan['txn_flat'] == 0){
                            $calculated = ($plan['txn_pcnt'] * $plan['amount'])/100;
                            $remarks = "Commission calculated: ".$calculated.". Min Commission: ".$plan['txn_min'].". Max Commission: ".$plan['txn_max'];
                        }
                        
                        $commAmount = 0 - $commAmount;
                        if($commAmount != 0)
                        {
                            $commArr[$i]['agent_id'] = $agentId;
                            $commArr[$i]['agent_code'] = $agentDetail['agent_code'];
                            $commArr[$i]['name'] = $agentDetail['name'];
                            $commArr[$i]['estab_city'] = ($agentDetail['estab_city']!= '')?$agentDetail['estab_city']:$agentDetail['res_city'];
                            $commArr[$i]['estab_pincode'] = ($agentDetail['estab_pincode']!= '')?$agentDetail['estab_pincode']:$agentDetail['res_pincode'];
                            $commArr[$i]['date'] = $date;
                            $commArr[$i]['date_formatted'] = Util::returnDateFormatted($date, "Y-m-d", "d-m-Y", "-");
                            $commArr[$i]['comm_amount'] = $commAmount;
                            $commArr[$i]['product_id'] = $plan['product_id'];
                            $commArr[$i]['product_code'] = $plan['product_code'];
                            $commArr[$i]['product_name'] = $plan['product_name'];
                            $commArr[$i]['plan_commission_id'] = $plan['plan_commission_id'];
                            $commArr[$i]['plan_commission_name'] = $plan['plan_commission_name'];
                            $commArr[$i]['commission_rate'] = ($plan['txn_flat'] > 0)?$plan['txn_flat']:$plan['txn_pcnt'].'%';
                            $commArr[$i]['transaction_type'] = $plan['typecode'];
                            $commArr[$i]['bank_name'] = $plan['bank_name'];
                            $commArr[$i]['transaction_type_name'] = $txnTypeDetail['name'];
                            $commArr[$i]['transaction_amount'] = $loads['transaction_amount'];
                            $commArr[$i]['transaction_fee'] = $loads['transaction_fee'];
                            $commArr[$i]['transaction_service_tax'] = $loads['transaction_service_tax'];
                            $commArr[$i]['transaction_ref_no'] = $loads['transaction_ref_no'];
                            $commArr[$i]['remarks'] = $remarks;
                            $i++;
                        }
                    }
                 } 
                }
                //echo "<hr/>".$plan['product_id']."==".$plan['product_name']."<pre>";print_r($commArr);
            }
            
        }
        ///echo "<hr/><pre>";print_r($commArr);exit;
        return $commArr;
        
        
   }
 
   /*
      * Overrides getAll() in App_Model
  }
     * 
     * @param int $page 
     * @access public
     * @return Zend_Paginator
     */
    public function getAll($page = 1){
        $paginator = $this->fetchAll();
      
        $paginator = Zend_Paginator::factory($paginator);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(App_DI_Container::get('ConfigObject')->paginator->items_per_page);
        
        return $paginator;
    }
    
    /**
     * Overrides findById() in App_Model
     *  public function findById($productId,$force = false){
        $products = parent::findById($productId);
        return $products;
    }
     * @param int $userId 
     * @access public
     * @return array
     */
    public function findById($productId,$force = false){
        $products = parent::findById($productId);
        return $products;
    }
 
    public function saveCommission($param, $agentArr)
    {
        //throw new Exception('testing exception message');
        $this->_db->beginTransaction(); 
        $agentCommCount = 0;
        
        try 
        {
            foreach($agentArr as $agents)
            {
               $param['agent_id'] = $agents['id'];
              
                    $arrComm = $this->calculateCommission($param);    
                    $ins = array();
                    if(!empty($arrComm))
                    {

                        foreach($arrComm as $comm)
                        {
                            $ins['agent_id'] = $param['agent_id'];
                            $ins['date'] = $comm['date'];
                            $ins['plan_commission_id'] = $comm['plan_commission_id'];
                            $ins['plan_commission_name'] = $comm['plan_commission_name'];
                            $ins['product_id'] = $comm['product_id'];
                            $ins['product_code'] = $comm['product_code'];
                            $ins['transaction_type'] = $comm['transaction_type'];
                            $ins['transaction_type_name'] = $comm['transaction_type_name'];
                            $ins['transaction_amount'] = $comm['transaction_amount'];
                            $ins['transaction_fee'] = $comm['transaction_fee'];
                            $ins['transaction_service_tax'] = $comm['transaction_service_tax'];
                            $ins['commission_amount'] = $comm['comm_amount'];
                            $ins['remarks'] = $comm['remarks'];
                            $ins['date_created'] = new Zend_Db_Expr('NOW()');
                            $this->_db->insert(DbTable::TABLE_COMMISSION_REPORT,$ins); 
                          // echo "<pre>";print_r($ins);
                        }
                       $agentCommCount++;
                   }
            }
            $this->_db->commit();
            
            return $agentCommCount;            
        } catch (Exception $e) {
            // If any of the queries failed and threw an exception,
            // we want to roll back the whole transaction, reversing
            // changes made in the transaction, even those that succeeded.
            // Thus all changes are committed together, or none are.
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception($e->getMessage());
        }
//       echo "<pre>";print_r($arrComm);
    }
       
     /* getAgentCommission function will fetch the sum of agent's commission for the month
     *  it will expect agent id and datefrom and dateto in param array
     */
    public function getAgentCommission($param, $txnTypes=array()){        
        $banks = new Banks();
        $productArr = $banks->getProductsByBankUnicode($param['bank_unicode']);
        $agentId  = isset($param['agentId'])?$param['agentId']:0;
        if(isset($param['dateFrom'])){
            $dateFrom = explode(" ", $param['dateFrom']);
            $dateFrom = $dateFrom[0];
        }
        else {
            $dateFrom = '';
        }
        if(isset($param['dateTo'])){
            $dateTo = explode(" ", $param['dateTo']);
            $dateTo = $dateTo[0];
        }
        else {
            $dateTo = '';
        }
       
        $selectTxnTypes='';
        
        // will put multiple txn types with OR operator in query
        if(!empty($txnTypes)){
            $countTxnTypes = count($txnTypes);
            for($i=0;$i<$countTxnTypes; $i++){
                if($i>0)
                    $selectTxnTypes .= " OR ";
                
                $selectTxnTypes .= "transaction_type = '".$txnTypes[$i]."'";
                
            }
        }
        
        if($agentId>0 && $dateFrom!='' && $dateTo!=''){
            
            $select  = $this->_db->select();        
            $select->from(DbTable::TABLE_COMMISSION_REPORT,array('sum(transaction_amount) as total_agent_transaction_amount', 'sum(commission_amount) as total_agent_commission', 'plan_commission_name', 'transaction_fee','transaction_service_tax','commission_amount'));
            $select->where("agent_id=?", $agentId);
            $select->where("product_id IN ($productArr)");
            if($dateFrom == $dateTo){
                $select->where("date =?", $dateFrom);
            }
            else {
                $select->where("date BETWEEN '". $dateFrom ."' AND '". $dateTo ."'");
            }
            if($selectTxnTypes!='')
                $select->where($selectTxnTypes);
            
           //echo $select; exit;
            return $this->_db->fetchRow($select);      
            
        } else return '';
    }
   
public function getAgentCommissionForBalanceSheet($param){
        $agentId  = isset($param['agentId'])?$param['agentId']:0;
        if(isset($param['dateFrom'])){
            $dateFrom = explode(" ", $param['dateFrom']);
            $dateFrom = $dateFrom[0];
        }
        else {
            $dateFrom = '';
        }
        if(isset($param['dateTo'])){
            $dateTo = explode(" ", $param['dateTo']);
            $dateTo = $dateTo[0];
        }
        else {
            $dateTo = '';
        }
        if($agentId>0 && $dateFrom!='' && $dateTo!=''){
            //Enable DB Slave
            $this->_enableDbSlave();
            $select  = $this->_db->select();
            $select->from(DbTable::TABLE_COMMISSION_REPORT,array('sum(transaction_amount) as total_agent_transaction_amount', 'sum(commission_amount) as total_agent_commission', 'plan_commission_name', 'transaction_fee','transaction_service_tax','commission_amount'));
            $select->where("agent_id=?", $agentId);
	    $select->where("commission_amount>0");
            if($dateFrom == $dateTo){
                $select->where("date =?", $dateFrom);
 }
            else {
                $select->where("date BETWEEN '". $dateFrom ."' AND '". $dateTo ."'");
            }
            
            $row = $this->_db->fetchRow($select);
            //Disable DB Slave
            $this->_disableDbSlave();
            return $row;
        } else return '';
    }

				 
    /*
     * clearPrevAgentComm -> deletes prev records of agent's comm for same date if cron has already run for the same date
     */
    public function clearPrevAgentComm($param){        
        
        $agentId = isset($param['agent_id'])?$param['agent_id']:'';
        $date = isset($param['date'])?$param['date']: '';
         
         if($agentId>0 && $date != ''){
             
            $where = "agent_id = ". $agentId. " AND date = '".$date ."'";
            $this->_db->delete(DbTable::TABLE_COMMISSION_REPORT, $where);
         } 
    }
    
    public function getCommission($param)
    { 
       
       $arrDates = Util::getDaysArr($param['from'], $param['to']);
       $commArr = array();
       $i = 0;
       foreach($arrDates as $date)
        {// for all dates
                    $param['date'] = $date;
                    if(isset($param['agent_id']) && $param['agent_id'] > 0){
                    $param['agent_id'] = $param['agent_id'];
                    }
                        
                    $commDetails = $this->getSavedComm($param);
                    foreach($commDetails as $commDetailArr){

                            $agentUser = new AgentUser();
                            $agentType = $agentUser->getAgentCodeName($commDetailArr['agent_user_type'], $commDetailArr['agent_id']);
                            $commArr[$i]['date_formatted'] = Util::returnDateFormatted($date, "Y-m-d", "d-m-Y", "-");
                            
                            if(!empty($agentType))
                            {
                                $commArr[$i] = array_merge($commArr[$i], $agentType);
                            }
                            
                            $commArr[$i]['agent_code'] = $commDetailArr['agent_code'];
                            $commArr[$i]['name'] = $commDetailArr['name'];
                            $commArr[$i]['estab_city'] = $commDetailArr['estab_city'];
                            $commArr[$i]['estab_pincode'] = $commDetailArr['estab_pincode'];
                            $commArr[$i]['transaction_type_name'] = $commDetailArr['transaction_type_name'];
                            $commArr[$i]['transaction_amount'] = $commDetailArr['transaction_amount'];
                            $commArr[$i]['transaction_fee'] = $commDetailArr['transaction_fee'];
                            $commArr[$i]['transaction_service_tax'] = $commDetailArr['transaction_service_tax'];
                            $commArr[$i]['plan_commission_name'] = $commDetailArr['plan_commission_name'];
                            $commArr[$i]['comm_amount'] = $commDetailArr['commission_amount'];
                            $commArr[$i]['bank_account_number'] = $commDetailArr['bank_account_number'];
                            $commArr[$i]['bank_ifsc_code'] = $commDetailArr['bank_ifsc_code'];
                            $i++;
                    }
        }
       
        return $commArr;
        
        
   }
   
    public function getCommissionCSV($param) {
       $arrDates = Util::getDaysArr($param['from'], $param['to']);
       $commArr = array();
       $i = 0;
       foreach($arrDates as $date) {// for all dates
            $param['date'] = $date;
            if(isset($param['agent_id']) && $param['agent_id'] > 0){
                $param['agent_id'] = $param['agent_id'];
            } 
            $commDetails = $this->getSavedComm($param);
            foreach($commDetails as $commDetailArr){ 
                $commArr[$i]['date_formatted'] = Util::returnDateFormatted($date, "Y-m-d", "d-m-Y", "-");
                $commArr[$i]['agent_code'] = $commDetailArr['agent_code'];
                $commArr[$i]['name'] = $commDetailArr['name'];
                $commArr[$i]['estab_city'] = $commDetailArr['estab_city'];
                $commArr[$i]['estab_pincode'] = $commDetailArr['estab_pincode'];
                $commArr[$i]['transaction_type_name'] = $commDetailArr['transaction_type_name'];
                $commArr[$i]['plan_commission_name'] = $commDetailArr['plan_commission_name'];
                $commArr[$i]['commission_amount'] = $commDetailArr['commission_amount']; 
                $i++;
            }
        } 
        return $commArr;
   }
   
   /*
     *Get saved Commission and returns amount, trasaction type , product code, agent code etc
     */
    public function getSavedComm($param){
        $date = $param['date'];
        $agentId = isset($param['agent_id'])?$param['agent_id']:0;
        $txnType = $param['txn_type'];
        $param['bank_unicode'];
        $banks = new Banks();
        $productArr = $banks->getProductsByBankUnicode($param['bank_unicode']);
        $details = $this->_db->select();
        $details->from(DbTable::TABLE_COMMISSION_REPORT." as r");
        $details->joinLeft(DbTable::TABLE_PRODUCTS." as p", "r.product_id = p.id",array('p.ecs_product_code','p.name as product_name'));
        $details->joinLeft(DbTable::TABLE_AGENTS." as a", "r.agent_id = a.id ",array('a.agent_code','concat(a.first_name," ",a.last_name) as name', 'a.user_type as agent_user_type'));
        $details->joinLeft(DbTable::TABLE_AGENT_DETAILS." as ad", "r.agent_id = ad.agent_id AND ad.status = '".STATUS_ACTIVE."'",array('ad.estab_city','ad.estab_pincode','ad.bank_ifsc_code','ad.bank_account_number'));
        $details->where("r.transaction_type IN ($txnType) ");
        $details->where("r.product_id IN ($productArr)");
        if($agentId > 0){
            $details->where("r.agent_id = ? ", $agentId); 
        }
//        $details->where("DATE(r.date) = ?", $date);
        $details->where("r.date = ?", $date);
       
       
        return $this->_db->fetchAll($details); 
    }
    /**
     * getHighestEarningAgent()
     * will return the Agents sorted by highest earning/commission agent
     * @param type $param
     * @return string
     */
    
     public function getHighestEarningAgent($param){
        if(isset($param['dateFrom'])){
            $dateFrom = explode(" ", $param['dateFrom']);
            $dateFrom = $dateFrom[0];
        }
        else {
            $dateFrom = '';
        }
        if(isset($param['dateTo'])){
            $dateTo = explode(" ", $param['dateTo']);
            $dateTo = $dateTo[0];
        }
        else {
            $dateTo = '';
        }
       
        
               
            $select  = $this->_db->select();        
            $select->from(DbTable::TABLE_COMMISSION_REPORT. ' as cr',array('sum(cr.commission_amount) as total_agent_commission','cr.agent_id'));
            $select->joinleft(DbTable::TABLE_AGENTS.' as a','a.id = cr.agent_id',array('a.email', 'concat(a.first_name," ",a.last_name) as agent_name', 'a.mobile1', 'a.agent_code'));
        
//          select->where("agent_id=?", $agentId);
            
            $select->where("cr.date BETWEEN '". $dateFrom ."' AND '". $dateTo ."'");
            $select->group("cr.agent_id");
            $select->order("total_agent_commission DESC");
            
//          echo $select; //exit;
            $resp = $this->_db->fetchAll($select);      
            return $resp;
            
        
    }
    
    /*
     *getAgentComm () Get saved Commission and returns amount sum by date
     */
    public function getAgentComm($param){
        $fromstr = $param['from'];
        $from =explode(" ",$fromstr);
        $tostr = $param['to'];
        $to =explode(" ",$tostr);
        $agentId = isset($param['agentId'])?$param['agentId']:0;
        $details = $this->_db->select();
        $details->from(DbTable::TABLE_COMMISSION_REPORT." as r",array('sum(r.commission_amount) as amount','date'));
        $details->where("r.agent_id = ? ", $agentId); 
        $details->where("r.date BETWEEN '". $from[0] ."' AND '". $to[0] ."'");
        $details->group("r.date");
        return $this->_db->fetchAll($details); 
    }
   
}
