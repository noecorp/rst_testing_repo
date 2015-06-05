<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class Corp_Ratnakar_CustomerPurse extends Corp_Ratnakar
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
    protected $_name = DbTable::TABLE_RAT_CUSTOMER_PURSE;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Privilege';
     public function getCustPurseDetails($data){
       $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CUSTOMER_PURSE, array('id','amount', 'block_amount'))
                ->where("rat_customer_id =?",$data['rat_customer_id'])
                ->where("purse_master_id =?",$data['purse_master_id']);
//                ->where("status='".STATUS_ACTIVE."'");
      
       return $this->_db->fetchRow($details);
       
   }
   
   public function getAllPurse($data){
       $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CUSTOMER_PURSE)
                ->where("rat_customer_id =?",$data['rat_customer_id']);
       return $this->_db->fetchAll($details);
       
   }
   
   
     public function getPurseInfoByCode($code){
       $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CUSTOMER_PURSE)
                ->where("code =?",$code)
                ->where("status= ?",STATUS_ACTIVE);
       return $this->_db->fetchRow($details);
   }
   
   public function getCustBalance($ratCustId){
       $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CUSTOMER_PURSE, array("SUM(AMOUNT) as sum"))
                ->where("rat_customer_id =?",$ratCustId);
       return $this->_db->fetchRow($details);
       
   }
   
   public function getIncompletePurses() {
       $details = $this->select()
                ->from(DbTable::TABLE_RAT_CUSTOMER_PURSE)
                ->where("customer_master_id =?",0);
       return $this->fetchAll($details);
   }
   
   public function getCustProductBalance($custMasterId, $productId){
       $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CUSTOMER_PURSE, array("SUM(AMOUNT) as sum"))
                ->where("customer_master_id =?", $custMasterId)
                ->where("product_id =?", $productId);
       return $this->_db->fetchRow($details);
       
   }
    public function updateClosingBalance(){
          
            $loadModel = new Corp_Ratnakar_Cardload();
            $walletTransfer = new Remit_Ratnakar_WalletTransfer();
            $authRequestModel = new AuthRequest();
            $purseBal = $this->getPurseBalance();
            $manualAdjustment = new BatchAdjustment();
            $remittanceReq = new Remit_Ratnakar_Remittancerequest();
            
            $totalPurse = sizeof($purseBal);
            $curdate = date("Y-m-d");
            $yesterday = date("Y-m-d",strtotime('-1 days'));
         
          
            if($totalPurse > 0){      
                
//                $this->_db->beginTransaction(); 
        
                try 
                {
                    $purseUpdCount = 0;
                    $valDate = date("Y-m-d",strtotime('-2 days'));
                    foreach($purseBal as $key=>$val){  
                       
                        $param = array('customer_purse_id'=>$val['customer_purse_id'], 'date'=>$valDate);
                        $purseOpeningBal = $this->getPurseClosingBalance($param);
                       
                        $dataArr = array(
                                        'customer_purse_id' => $val['customer_purse_id'],
                                        'date' => $yesterday,
                                        'on_date' => TRUE,
                                        'from' => $yesterday.' 00:00:00',
                                        'to' => $yesterday.' 23:59:59',
                                        'product_id' => $val['product_id'],
                            
                                    );
                       
                        // remittance and refund
                         $ratRemittance = $remittanceReq->getRemittanceforPurseID($dataArr);
                         $ratRefund = $remittanceReq->getRefundforPurseID($dataArr);
                        
                         $ratRemittance['total_remittance'] = (isset($ratRemittance['total_remittance']))? $ratRemittance['total_remittance']:0;
                         $ratRefund['total_refund'] = (isset($ratRefund['total_refund']))? $ratRefund['total_refund']:0;
                        
                         /*************
                         $ratRemitRefund = $remittanceReq->getRemitRefundforPurseID($dataArr);
                         $ratRemitRefund['total_remit_refund'] = (isset($ratRemitRefund['total_remit_refund']))? $ratRemitRefund['total_remit_refund']:0;
                        ***********/
                        // Load & Cutoff
			$dataArr['load_cutoff'] = TRUE;
			$ratCorpLoad = Util::toArray($loadModel->getTotalLoad($dataArr));
			// Debit 
			$ratDebit = Util::toArray($loadModel->getTotalDebit($dataArr)); 
			  
			$ratCorpLoad['total_load_amount'] = (isset($ratCorpLoad['total_load_amount']))? $ratCorpLoad['total_load_amount']: 0;
			$ratCorpLoad['total_cutoff_amount'] = (isset($ratCorpLoad['total_cutoff_amount']))? $ratCorpLoad['total_cutoff_amount']: 0;
			$ratDebit['total_load_amount'] = (isset($ratDebit['total_load_amount']))? $ratDebit['total_load_amount']: 0;
                         
                        
                        /*
                         // Fetch Misc DR/CR
                        $miscDRdetails = $authRequestModel->getCompletedTransactions($dataArr);
                        $ratDR['txn_dr'] =  (!empty($miscDRdetails))? $miscDRdetails->completed_total : 0;
                        $miscCRdetails = $authRequestModel->getReversedTransactions($dataArr);
                        $ratCR['txn_cr'] =  (!empty($miscCRdetails)) ? $miscCRdetails->reversed_total : 0;
                        // Fetch Manual adjustment
                        $dataArr['txn_type'] = TXNTYPE_DEBIT_MANUAL_ADJUSTMENT;
                        $manualDRdetails = $manualAdjustment->getManualAdjustment($dataArr);
                        $ratDR['manual_txn_dr'] =  (!empty($manualDRdetails) && !is_null($manualDRdetails['manual_adj_total']))? $manualDRdetails['manual_adj_total'] : 0;
                        $dataArr['txn_type'] = TXNTYPE_CREDIT_MANUAL_ADJUSTMENT;
                        $manualCRdetails = $manualAdjustment->getManualAdjustment($dataArr);
                        $ratCR['manual_txn_cr'] =  (!empty($manualCRdetails) && !is_null($manualCRdetails['manual_adj_total'])) ? $manualCRdetails['manual_adj_total'] : 0;
                        */
                        
                        $auth = $authRequestModel->getCatpRatpTransactions($dataArr);
                        $manual = $manualAdjustment->getAllManualAdjustment($dataArr);
                        
                         // Wallet Transfer FROM/In Process
                         $dataArr['txn_type'] = TXNTYPE_WALLET_TOWALLET_FUND_TRANSFER;
                         $dataArr['fromId'] = TRUE;
                         $dataArr['toId'] = FALSE;
                         $ratWalletTransferFrom = Util::toArray($walletTransfer->getWalletLoadDetails($dataArr));
                         
                         // Wallet Transfer TO
                         $dataArr['fromId'] = FALSE;
                         $dataArr['toId'] = TRUE;
                         $ratWalletTransferTo = Util::toArray($walletTransfer->getWalletLoadDetails($dataArr));
                             
                         $ratWalletTransferFrom['total_load_amount'] = (isset($ratWalletTransferFrom['total_load_amount']))? $ratWalletTransferFrom['total_load_amount']: 0;
                         $ratWalletTransferTo['total_load_amount'] = (isset($ratWalletTransferTo['total_load_amount']))? $ratWalletTransferTo['total_load_amount']: 0;
                         
                        
                        $addOnOpeningBal =  $ratCorpLoad['total_load_amount'] 
                                + $auth['txn_cr'] 
                                + $manual['manual_txn_cr'] 
                                + $ratWalletTransferTo['total_load_amount'] 
                                + $ratRefund['total_refund'];
                               
                        $subtractOnOpeningBal =  $ratCorpLoad['total_cutoff_amount']
                                            + $ratDebit['total_load_amount']
                                            + $auth['txn_dr']
                                            + $manual['manual_txn_dr']
                                            + $ratWalletTransferFrom['total_load_amount']
                                            +$ratRemittance['total_remittance'];
                                            /** +$ratRemitRefund['total_remit_refund']; **/
                        
                         $closingBal = $purseOpeningBal['closing_balance'] + $addOnOpeningBal - $subtractOnOpeningBal ;
                      
                        // inserting balance if not already inserted earlier
                        $param = array('customer_purse_id'=>$val['customer_purse_id'], 'date'=>$yesterday);
                        $closingBalYesterday = $this->getPurseClosingBalance($param);
                        
                        $dateUpdated = new Zend_Db_Expr('NOW()');
                    
                        if(empty($closingBalYesterday)){
                         
                           $insertArr =  array(
                               'closing_balance'=> $closingBal,
                               'product_id'=> $val['product_id'],
                               'bank_id'=> $val['bank_id'],
                               'customer_master_id'=> $val['customer_master_id'],
                               'rat_customer_id'=> $val['rat_customer_id'],
                               'purse_master_id' => $val['purse_master_id'],
                               'customer_purse_id'=> $val['customer_purse_id'],
                               'date' => $yesterday,
                               'date_created' => $dateUpdated,
                               'date_updated' => $dateUpdated);
                          
                           $this->_db->insert(DbTable::TABLE_RAT_CUSTOMER_PURSE_CLOSING_BALANCE,$insertArr );                    
                        
                           
                        } else {
                            // updating balance if already added earlier
                            $where = "customer_purse_id='".$val['customer_purse_id']."' AND date='".$yesterday."'";
                            
                            $updData = array('closing_balance'=> $closingBal, 'date_updated'=> $dateUpdated);
                            
                            $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE_CLOSING_BALANCE, $updData, $where);                    
                        }
                        $purseUpdCount++;
                    }
//                    $this->_db->commit();
                    return $purseUpdCount;
                }
                catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        // If any of the queries failed and threw an exception,
                        // we want to roll back the whole transaction, reversing
                        // changes made in the transaction, even those that succeeded.
                        // Thus all changes are committed together, or none are.
//                        $this->_db->rollBack();
                        throw new Exception($e->getMessage());
                }
            } else return 0;
    }
    
     public function getPurseBalance() {
        $select  = $this->_db->select()        
        ->from(DbTable::TABLE_RAT_CUSTOMER_PURSE,array('id as customer_purse_id', 'amount as closing_balance','customer_master_id','purse_master_id','product_id', 'rat_customer_id','bank_id'));
   
        return $this->_db->fetchAll($select);      
    }
    
    public function getPurseClosingBalance($param) {
         $purseId = isset($param['customer_purse_id'])?$param['customer_purse_id']:0;
         $date = isset($param['date'])?$param['date']:'';
         
         if($purseId > 0 && $date!=''){
             //Enable DB Slave
           $this->_enableDbSlave();
            $select  = $this->_db->select()        
            ->from(DbTable::TABLE_RAT_CUSTOMER_PURSE_CLOSING_BALANCE, array('closing_balance'))
            ->where('customer_purse_id = ?', $purseId)
            ->where('date = ?', $date);
//            echo $select; //exit;    
            $row = $this->_db->fetchRow($select);  
            //Disable DB Slave
            $this->_disableDbSlave();
            return $row;
         } else {
             return false;
         }
    }

    public function getCustBalanceByWallet($ratCustId, $walletCode = ''){
       $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CUSTOMER_PURSE .' as rcp', array("SUM(rcp.amount) as sum"))
                ->join(DbTable::TABLE_PURSE_MASTER .' as pm', "rcp.purse_master_id = pm.id AND pm.code = '".$walletCode."'")
                ->where("rat_customer_id =?",$ratCustId);
              
      
       $res = $this->_db->fetchRow($details);
       if(!empty($res)){
           return $res;
       }else{
           return FALSE;
       }
       
   }
      public function updateCustPurseAmount($amount,$id){
           $updArr = array(
                'amount' => new Zend_Db_Expr("amount - " . $amount),
                'date_updated' => new Zend_Db_Expr('NOW()')
               );
            $where = "id = '" . $id . "'";
            return $this->update($updArr, $where);
      }
      
       public function getCustBalanceByCustIDPurseID($ratCustId, $purseID){
       $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CUSTOMER_PURSE .' as rcp', array('amount'))
                ->where("rat_customer_id =?",$ratCustId)
                ->where("purse_master_id =?",$purseID);
       
       $res = $this->_db->fetchRow($details);
       if(!empty($res)){
           return $res['amount'];
       }else{
           return 0;
       }
       
   }
   
   
   public function getCustBlockBalanceByCustIDPurseID($ratCustId, $purseID){
       $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CUSTOMER_PURSE .' as rcp', array('block_amount'))
                ->where("rat_customer_id =?",$ratCustId)
                ->where("purse_master_id =?",$purseID);
       
       $res = $this->_db->fetchRow($details);
       if(!empty($res)){
           return $res['block_amount'];
       }else{
           return 0;
       }
       
   }
   
   public function getWalletCode($custPurseId){
       
        $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CUSTOMER_PURSE .' as rcp', array("id"))
                ->join(DbTable::TABLE_PURSE_MASTER .' as pm', "rcp.purse_master_id = pm.id ",array('code'))
                ->where("rcp.id =?",$custPurseId);
       $purseDetails = $this->_db->fetchRow($details);
       
       return $purseDetails;
   }
   
    public function getWalletClosingBalance($param) {
        $purseId = isset($param['purse_master_id']) ? $param['purse_master_id']:0;
        $date = isset($param['date'])?$param['date']:'';
        $productId = isset($param['product_id'])?$param['product_id']:0;

        if($purseId > 0 && $date != ''){
           $select  = $this->_db->select()        
           ->from(DbTable::TABLE_RAT_CUSTOMER_PURSE_CLOSING_BALANCE, array('sum(closing_balance) as balance'))
           ->where('purse_master_id = ?', $purseId);
           $select->where('product_id = ?', $productId);
           
           $select->where('date = ?', $date);
           
//           echo $select."<br>";// exit;
           return $this->_db->fetchRow($select);      
        } else {
            return false;
        }
    }
    
    public function getCustBankBalance($custMasterId, $bankId){
       $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CUSTOMER_PURSE, array("SUM(AMOUNT) as sum"))
                ->where("customer_master_id IN ( $custMasterId )")
                ->where("bank_id =?", $bankId);
       return $this->_db->fetchRow($details);
       
   }
   
    public function getCustBalanceWalletWise($ratCustId){
       $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CUSTOMER_PURSE .' as rcp', array("SUM(rcp.amount) as sum","purse_master_id"))
                ->joinLeft(DbTable::TABLE_PURSE_MASTER .' as pm', "rcp.purse_master_id = pm.id",array('code'))
                ->where("rat_customer_id =?",$ratCustId) 
                ->group("purse_master_id");
              
     
       $res = $this->_db->fetchAll($details);
       if(!empty($res)){
           return $res;
       }else{
           return FALSE;
       }
       
   }
   
   public function getProductWalletClosingBalance($param) {
        $date = isset($param['date'])?$param['date']:'';
        $productId = isset($param['product_id'])?$param['product_id']:0;
        $wallet_type = isset($param['wallet_type']) ? $param['wallet_type'] : '';
        
           $select  = $this->_db->select()        
                ->from(DbTable::TABLE_RAT_CUSTOMER_PURSE_CLOSING_BALANCE. " as rcpcb", array('sum(closing_balance) as balance'))
                ->joinLeft(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id = rcpcb.purse_master_id ",array());
           if($productId != '') {
            $select->where('rcpcb.product_id = ?', $productId);
           }
           if (!empty($wallet_type)) {
                $select->where("pm.is_virtual = ? " , $wallet_type);
            }
           if($date != '') {
            $select->where('rcpcb.date = ?', $date);
           }
           
//           echo $select."<br>";// exit;
           return $this->_db->fetchRow($select);      
        
    }
   
       public function checkValidWallet($productId,$walletCode){
           
       $purseModel= new MasterPurse();
       $productPurse =  $purseModel->getProductPurseCode($productId);
       foreach($productPurse as $res){
        if(in_array($walletCode,$res)){
           return TRUE;
        }
       }
           return FALSE;
      
          
      
       
   }
   
   public function getCustBalanceByCustPurseID($ratCustPurseId){
       $details = $this->_db->select()
                ->forUpdate()
                ->from(DbTable::TABLE_RAT_CUSTOMER_PURSE .' as rcp', array('amount'))
                ->where("id =?",$ratCustPurseId);
              //  ->where("amount > 0");
       
       $res = $this->_db->fetchRow($details);
       if(!empty($res)){
           return $res['amount'];
       }else{
           return 0;
       }
       
   }
   
    /*
     * getCustomerGenWalletBalance() will get customer's total general wallet balance
    */
    public function getCustomerGenWalletBalance($customerMasterId, $ratCustomerId, $productId) {
        $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CUSTOMER_PURSE . " as rcp", array("SUM(rcp.amount) as sum"))
                ->join(DbTable::TABLE_PURSE_MASTER . " as pm", "rcp.purse_master_id=pm.id", array())
                ->where("pm.is_virtual =?", FLAG_NO)
                ->where("pm.product_id =?", $productId)
                ->where("rcp.customer_master_id =?", $customerMasterId)
                ->where("rcp.rat_customer_id =?", $ratCustomerId);
       return $this->_db->fetchRow($details);
    }
    
    
    public function getPurseCardInfo($param){
	$partnerRefNo = isset($param['partner_ref_no']) ? $param['partner_ref_no'] : '';
        $productID = isset($param['product_id']) ? $param['product_id'] : '';
        $mobile = isset($param['mobile']) ? $param['mobile'] : '';
	$walletCode = isset($param['wallet_code']) ? $param['wallet_code'] : '';
        
	$details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CUSTOMER_PURSE . " as rcp", array('id AS customer_purse_id'))
                ->join(DbTable::TABLE_PURSE_MASTER . " as pm", "rcp.purse_master_id=pm.id", array())
		->join(DbTable::TABLE_RAT_CORP_CARDHOLDER. " as c","c.rat_customer_id = rcp.rat_customer_id",array('status'))
                ->where("rcp.product_id =?", $productID)
                ->where("pm.code =?", $walletCode);
	
	if ($mobile != ''){
	    $details->where("c.mobile = '" . $mobile . "'");
	}
	if ($partnerRefNo != ''){
	    $details->where("c.partner_ref_no = '" . $partnerRefNo . "'");
	}
       return $this->_db->fetchRow($details);
    }
    
    public function getPurseAmountByWallet($param){
	$walletCode = isset($param['wallet_code']) ? $param['wallet_code'] : '';
	$customer_master_id = isset($param['customer_master_id']) ? $param['customer_master_id'] : '';
	
	$sql = $this->_db->select();
	$sql->from(DbTable::TABLE_RAT_CUSTOMER_PURSE . " as cp", array('cp.id AS customer_purse_id','cp.amount','cp.block_amount'));
	$sql->join(DbTable::TABLE_PURSE_MASTER . " as pm", "cp.purse_master_id=pm.id", array());
	$sql->where("cp.customer_master_id = ?",$customer_master_id);
	$sql->where("pm.code = ?",$walletCode);
	return $this->_db->fetchRow($sql);
    }
    
 }
