<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class Corp_Kotak_CustomerPurse extends Corp_Kotak
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
    protected $_name = DbTable::TABLE_KOTAK_CUSTOMER_PURSE;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Privilege';
     public function getCustPurseDetails($data){
       $details = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_CUSTOMER_PURSE)
                ->where("kotak_customer_id =?",$data['kotak_customer_id'])
                ->where("purse_master_id =?",$data['purse_master_id']);
       return $this->_db->fetchRow($details);
       
   }
   
   public function getAllPurse($data){
       $details = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_CUSTOMER_PURSE)
                ->where("kotak_customer_id =?",$data['kotak_customer_id']);
       return $this->_db->fetchAll($details);
       
   }
   
   
     public function getPurseInfoByCode($code){
       $details = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_CUSTOMER_PURSE)
                ->where("code =?",$code)
                ->where("status= ?",STATUS_ACTIVE);
       return $this->_db->fetchRow($details);
   }
   
   public function getIncompletePurses() {
       $details = $this->select()
                ->from(DbTable::TABLE_KOTAK_CUSTOMER_PURSE)
                ->where("customer_master_id =?",0);
       return $this->fetchAll($details);
   }
   
   public function updateClosingBalance(){
           
            $loadModel = new Corp_Kotak_Cardload();
            $authRequestModel = new AuthRequest();
            $purseBal = $this->getPurseBalance();
            $manualAdjustment = new KotakBatchAdjustment();
            
            $totalPurse = sizeof($purseBal);
            $curdate = date("Y-m-d");
            $yesterday = date("Y-m-d",strtotime('-1 days'));
            
          
            if($totalPurse>0){      
                
                $this->_db->beginTransaction(); 
        
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
                       
                         
                        // Load
//                         $dataArr['txn_type'] = TXNTYPE_CARD_RELOAD;
                         $ratCorpLoad = $loadModel->getTotalLoad($dataArr);
                        
                         //  Debit
                        $dataArr['txn_type'] = TXNTYPE_CARD_DEBIT;
                        $dataArr['status'] = STATUS_DEBITED;
                        $ratDebit = $loadModel->getTotalLoad($dataArr);
                    
                        // Reversal
                         $dataArr['cutoff'] = TRUE;
                         $ratReversal = $loadModel->getTotalLoad($dataArr);
                       
                         $ratCorpLoad['total_load_amount'] = (isset($ratCorpLoad['total_load_amount']))? $ratCorpLoad['total_load_amount']: 0;
                         $ratReversal['total_load_amount'] = (isset($ratReversal['total_load_amount']))? $ratReversal['total_load_amount']: 0;
                         $ratDebit['total_load_amount'] = (isset($ratDebit['total_load_amount']))? $ratDebit['total_load_amount']: 0;
                         
                         // Fetch Misc DR/CR
                        $miscDRdetails = $authRequestModel->getCompletedTransactions($dataArr);
        
                        $ratDR['txn_dr'] =  (!empty($miscDRdetails))? $miscDRdetails->completed_total : 0;
        
        
                        $miscCRdetails = $authRequestModel->getReversedTransactions($dataArr);
       
                        $ratCR['txn_cr'] =  (!empty($miscCRdetails)) ? $miscCRdetails->reversed_total : 0;
                        
                        // Fetch Manual adjustment
                        $dataArr['txn_type'] = TXNTYPE_DEBIT_MANUAL_ADJUSTMENT;
                        $manualDRdetails = $manualAdjustment->getmanualAdjustment($dataArr);
        
                        $ratDR['manual_txn_dr'] =  (!empty($manualDRdetails)&& !is_null($manualDRdetails['manual_adj_total']))? $manualDRdetails->manual_adj_total : 0;
        
                        $dataArr['txn_type'] = TXNTYPE_CREDIT_MANUAL_ADJUSTMENT;
                        $manualCRdetails = $manualAdjustment->getmanualAdjustment($dataArr);
                        
                      
                        $ratCR['manual_txn_cr'] =  (!empty($manualCRdetails) && !is_null($manualCRdetails['manual_adj_total'])) ? $manualCRdetails->manual_adj_total : 0;
                         
                        
                        
                        $addOnOpeningBal =  $ratCorpLoad['total_load_amount'] + $ratCR['txn_cr']+ $ratCR['manual_txn_cr'];
                                
                                
                                
                               
                        $subtractOnOpeningBal =  $ratReversal['total_load_amount']
                                            + $ratDebit['total_load_amount']
                                            +  $ratDR['txn_dr']
                                            +  $ratDR['manual_txn_dr'];
                        
                         $closingBal = $purseOpeningBal['closing_balance'] + $addOnOpeningBal - $subtractOnOpeningBal ;
                         
                        // inserting balance if not already inserted earlier
                        $param = array('customer_purse_id'=>$val['customer_purse_id'], 'date'=>$yesterday);
                        $closingBalYesterday = $this-> getPurseClosingBalance($param);
                        $dateUpdated = new Zend_Db_Expr('NOW()');
                    
                        if(empty($closingBalYesterday)){ 
                           $this->_db->insert(DbTable::TABLE_KOTAK_CUSTOMER_PURSE_CLOSING_BALANCE, array('closing_balance'=> $closingBal,'product_id'=> $val['product_id'],'customer_master_id'=> $val['customer_master_id'],'purse_master_id' => $val['purse_master_id'],'customer_purse_id'=> $val['customer_purse_id'],'date' => $yesterday,'date_created' => $dateUpdated,'date_updated' => $dateUpdated));                    
                        } else {
                            // updating balance if already added earlier
                            $where = "customer_purse_id='".$val['customer_purse_id']."' AND date='".$yesterday."'";
                            
                            $updData = array('closing_balance'=> $closingBal, 'date_updated'=> $dateUpdated);
                            
                            $this->_db->update(DbTable::TABLE_KOTAK_CUSTOMER_PURSE_CLOSING_BALANCE, $updData, $where);                    
                        }
                        $purseUpdCount++;
                    }
                    $this->_db->commit();
                    return $purseUpdCount;
                }
                catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        // If any of the queries failed and threw an exception,
                        // we want to roll back the whole transaction, reversing
                        // changes made in the transaction, even those that succeeded.
                        // Thus all changes are committed together, or none are.
                        $this->_db->rollBack();
                        throw new Exception($e->getMessage());
                }
            } else return 0;
    }
    
     public function getPurseBalance() {
        $select  = $this->_db->select()        
        ->from(DbTable::TABLE_KOTAK_CUSTOMER_PURSE,array('id as customer_purse_id', 'amount as closing_balance','customer_master_id','purse_master_id','product_id'));
     
        return $this->_db->fetchAll($select);      
    }
    
    public function getPurseClosingBalance($param) {
         $purseId = isset($param['customer_purse_id'])?$param['customer_purse_id']:0;
         $date = isset($param['date'])?$param['date']:'';
         
         if($purseId > 0 && $date!=''){
            $select  = $this->_db->select()        
            ->from(DbTable::TABLE_KOTAK_CUSTOMER_PURSE_CLOSING_BALANCE, array('closing_balance'))
            ->where('customer_purse_id = ?', $purseId)
            ->where('date = ?', $date);
//            echo $select; //exit;    
            return $this->_db->fetchRow($select);      
         } else {
             return false;
         }
    }

     public function getCustBalance($ratCustId){
       $details = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_CUSTOMER_PURSE, array("SUM(AMOUNT) as sum"))
                ->where("kotak_customer_id =?",$ratCustId);
       return $this->_db->fetchRow($details);
       
   }
   
    public function getCustProductBalance($custMasterId, $productId){
       $details = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_CUSTOMER_PURSE, array("SUM(AMOUNT) as sum"))
                ->where("customer_master_id =?", $custMasterId)
                ->where("product_id =?", $productId);
       return $this->_db->fetchRow($details);
       
   }
}
