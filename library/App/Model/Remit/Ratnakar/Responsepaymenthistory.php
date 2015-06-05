<?php

/*
 * Ratnakar Remitter Model
 */

class Remit_Ratnakar_Responsepaymenthistory extends Remit_Ratnakar {

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
    protected $_name = DbTable::TABLE_RAT_RESPONSE_PAYMENT_HISTORY;

    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    
     public function checkTransactionExist($tran_code, $utr_no = '') {
         
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_RESPONSE_PAYMENT_HISTORY, array('id'));
        $select->where("tran_id = '".$tran_code."' AND utr = '" . $utr_no."'");
        
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
       
        //$date_transaction = Util::returnDateFormatted($insertDataArr[0], "d/m/Y", "Y-m-d", "/");
        //$date_execution = Util::returnDateFormatted($insertDataArr[1], "d/m/Y", "Y-m-d", "/");
        
        $data = array(
        'payment_ref_no'=> $insertDataArr[1],
        'utr'=> $insertDataArr[2],
        'tran_id'=> $insertDataArr[3],
        'value_date'=> $insertDataArr[4],
        'batch_time'=> $insertDataArr[5],
        'sender_ifsc'=> $insertDataArr[6],    
        'sender_name'=> $insertDataArr[7],
        'sender_account_no'=> $insertDataArr[8],
        'bene_ifsc'=> $insertDataArr[9],
        'bene_name'=> $insertDataArr[10],
        'bene_account_no'=> $insertDataArr[11],
        'amount'=> $insertDataArr[12],
        'status'=> $insertDataArr[13],
       // 'rejection_code'=> $date_transaction,
       // 'rejection_remark'=> $date_execution,
      //  'returned_date'=> $date_execution,
         'rejection_code'=>$insertDataArr[14],
         'rejection_remark'=>$insertDataArr[15],
         'returned_date'=>$insertDataArr[16],
        'file_name'=> $datafiles['file_name'],    
        'by_ops_id'=> $datafiles['ops_id'],
        'date_input'=> $datafiles['input_date'],
        'date_created'=> new Zend_Db_Expr('NOW()')
        );
       
          $rs = $this->_db->insert(DbTable::TABLE_RAT_RESPONSE_PAYMENT_HISTORY, $data);
             return TRUE;
         } catch (Exception $e) {
              App_Logger::log($e->getMessage(), Zend_Log::ERR);
              throw new Exception($e->getMessage());
                return FALSE;
        }
          
    }
    
    
    
}