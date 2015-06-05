<?php

/*
 * Ratnakar Remitter Model
 */

class Remit_Ratnakar_Paymenthistory extends Remit_Ratnakar {

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

   // protected $_name = DbTable::TABLE_RAT_PAYMENT_HISTORY;

    protected $_name = DbTable::TABLE_RATNAKAR_PAYMENT_HISTORY;

    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    public function mappingUTR(){
        $user = Zend_Auth::getInstance()->getIdentity();
        // get pending records for mapping
        $mappingArr = $this->getPendingRecords();
        $paymenthistoryModel = new Remit_Ratnakar_Paymenthistory();
        $mappingArr = Util::toArray($mappingArr);
        $remitreqModel = new Remit_Ratnakar_Remittancerequest();  
        $i = 0;
        foreach($mappingArr as $record){
           
            $txnCodeExists = $this->txnCodeExists($record['txn_code']);
           
            if(!empty($txnCodeExists)){
                // Check if already mapped
               
                if($txnCodeExists['utr'] != ''){
                    // Mark it as UTR already mapped
                $updateArr = array('upload_status' => STATUS_FAILED,'failed_reason' => 'UTR already mapped',
                    'date_updated' => new Zend_Db_Expr('NOW()'));
                $this->updatePaymentHistory($updateArr, $record['id']);
                }
                else{
               
                $utrLength = $paymenthistoryModel->checkUTRLength($record['utr']);
                $utrFormat = $paymenthistoryModel->checkUTRFormat($record['utr']);
                if(!$utrLength){
                    $updateArr = array('txn_id' => $txnCodeExists['id'],'status' => STATUS_FAILED,'upload_status' => STATUS_FAILED,
                    'date_updated' => new Zend_Db_Expr('NOW()'));
                }elseif(!$utrFormat){
                    $updateArr = array('txn_id' => $txnCodeExists['id'],'status' => STATUS_FAILED,'upload_status' => STATUS_FAILED,
                    'date_updated' => new Zend_Db_Expr('NOW()'));
                }else{
                    // update payment history table
                    $updateArr = array('txn_id' => $txnCodeExists['id'],'status' => STATUS_MAPPED,'upload_status' => STATUS_SUCCESS,
                    'date_updated' => new Zend_Db_Expr('NOW()'));
                }
                //map the utr in request table
                    $reqUpdateArr = array('utr' => $record['utr'],
                        'utr_by_ops_id'=> TXN_OPS_ID,
                        'date_utr' => new Zend_Db_Expr('NOW()'),
                        'status_utr' => STATUS_MAPPED);

                $remitreqModel->updateReqByTXNCode($record['txn_code'],$reqUpdateArr);
                $this->updatePaymentHistory($updateArr, $record['id']);
                $i++;
              }
            }
            else{// Mark it as Customer not found
                $updateArr = array('upload_status' => STATUS_FAILED,'failed_reason' => 'No Customer Ref found',
                    'date_updated' => new Zend_Db_Expr('NOW()'));
                $this->updatePaymentHistory($updateArr, $record['id']);
            }
            
            
        }
       return $i; 
    }
    
   
     public function getPendingRecords(){
        
        $select = $this->select()
                ->from(DbTable::TABLE_RATNAKAR_PAYMENT_HISTORY,array('id','utr','txn_code'))
                ->where("status = '" . STATUS_PENDING . "'");

        return $this->fetchAll($select);
        
        
    }
    
     public function txnCodeExists($txnCode){
        
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST,array('id','utr','txn_code'))
                ->where("txn_code =?",$txnCode);
//                ->where("ISNULL(utr) OR utr = ''");

        $res =  $this->_db->fetchRow($select);
        
         if (!empty($res)) {
            return $res;
        } else {
            return FALSE;
        }
        
    }  
    public function updatePaymentHistory($params,$id){
        $this->update($params,"id = '$id'");
        return true;
        
    }
    
     public function checkTransactionExist($txn_code, $utr_no = '') {
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RATNAKAR_PAYMENT_HISTORY, array('id'));
        $select->where("txn_code = '".$txn_code."' AND utr = '" . $utr_no."'");
        $rs = $this->_db->fetchRow($select);
        if (empty($rs)) {
           return TRUE;
        } else {
           return FALSE;
        }
        
    }
    
    /*
     * Insert data into table
     */
    public function insertTransaction($insertDataArr, $datafiles) {
        try{
            
        
        $date_transaction = Util::returnDateFormatted($insertDataArr[0], "d/m/Y", "Y-m-d", "/");
        $date_execution = Util::returnDateFormatted($insertDataArr[1], "d/m/Y", "Y-m-d", "/");
        
        $data = array(
        'txn_id'=>'',
        'ref_no'=> $insertDataArr[2],
        'from_account_no'=> $insertDataArr[3],
        'bene_account_no'=> $insertDataArr[4],
        'bene_name'=> $insertDataArr[5],
        'amount'=> $insertDataArr[6],    
        'transaction_status'=> $insertDataArr[7],
        'core_status'=> $insertDataArr[8],
        'narration'=> $insertDataArr[9],
        'type_of_transaction'=> $insertDataArr[10],
        'ifsc_code'=> $insertDataArr[11],
        'utr'=> $insertDataArr[12],
        'txn_code'=> $insertDataArr[13],
        'date_transaction'=> $date_transaction,
        'date_execution'=> $date_execution,
        'by_ops_id'=> $datafiles['ops_id'],
        'file_name'=> $datafiles['file_name'],
        'date_input'=> $datafiles['input_date'],
        'upload_status'=> $datafiles['upload_status'],
        'status'=> $datafiles['status'],
        'failed_reason'=> $datafiles['failed_reason'],
        'date_created'=> new Zend_Db_Expr('NOW()')
        );
      
        $rs = $this->_db->insert(DbTable::TABLE_RATNAKAR_PAYMENT_HISTORY, $data);
             return TRUE;
        } catch (Exception $e) {
           App_Logger::log($e->getMessage(), Zend_Log::ERR);
              throw new Exception($e->getMessage());
            return FALSE;
        }
        
    }
    
     public function checkUTRLength($utr, $length = 16) {
       if( strlen(trim($utr)) != $length ){
           return FALSE;
       } else{
            return TRUE;  
           }
       }
           
      
     public function checkUTRFormat($utr, $format = 'RATNN') {
        if (0 === strpos(trim($utr), $format)) {
           return TRUE;
       }else{
           return FALSE;
       }
        
    }
    
     public function getRecordStatus($param){
        $toDate = isset($param['to_date']) ? $param['to_date'] : '';
        $fromDate = isset($param['from_date']) ? $param['from_date'] : '';
        $select = $this->select()
                ->from(DbTable::TABLE_RATNAKAR_PAYMENT_HISTORY,array('*'))
                ->where("date_input >=  '" . $fromDate . "' ")
                ->where("date_input <= '" . $toDate . "' ");

        $data = $this->fetchAll($select);
        $retData = array();
        if (!empty($data)) {
            
            foreach ($data as $key => $data) {
                $retData[$key]['ref_no'] = $data['ref_no'];
                $retData[$key]['from_account_no'] = $data['from_account_no'];
                $retData[$key]['bene_account_no'] = $data['bene_account_no'];
                $retData[$key]['bene_name'] = $data['bene_name'];
                $retData[$key]['amount'] = $data['amount'];
                $retData[$key]['transaction_status'] = $data['transaction_status'];
                $retData[$key]['core_status'] = $data['core_status'];
                $retData[$key]['narration'] = $data['narration'];
                $retData[$key]['type_of_transaction'] = $data['type_of_transaction'];
                $retData[$key]['ifsc_code'] = $data['ifsc_code'];
                $retData[$key]['txn_code'] = $data['txn_code'];
                $retData[$key]['utr'] = $data['utr'];
                $retData[$key]['date_transaction'] = $data['date_transaction'];
                $retData[$key]['date_execution'] = $data['date_execution'];
                $retData[$key]['file_name'] = $data['file_name'];
                $retData[$key]['date_input'] = $data['date_input'];
                $retData[$key]['upload_status'] = $data['upload_status'];
                $retData[$key]['failed_reason'] = $data['failed_reason'];
            }
        }

        return $retData;
        
        
    }
}
