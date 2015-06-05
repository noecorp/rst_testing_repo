<?php

/**
 * Model that manages ratnakar cardloads
 *
 * @package Operation_Models
 * @copyright transerv
 */
class Corp_Ratnakar_SettlementResponse extends Corp_Ratnakar {

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
    protected $_name = DbTable::TABLE_RATNAKAR_SETTLEMENT_RESPONSE;

   public function insertResponse($insertDataArr, $datafiles) {
        try{


        $data = array(
        'sequence_no'=> $insertDataArr[1],
        'settlement_ref_no'=> $insertDataArr[2],
        'amount'=> $insertDataArr[3],
        'value_date'=> $insertDataArr[4],
        'batch_id'=> $insertDataArr[5],
        'sending_branch'=> $insertDataArr[6],    
        'sender_act_type'=> $insertDataArr[7],
        'sender_act_no'=> $insertDataArr[8],
        'sender_act_name'=> $insertDataArr[9],
        'bene_branch'=> $insertDataArr[10],
        'bene_act_type'=> $insertDataArr[11],
        'bene_act_no'=> $insertDataArr[12],
        'bene_act_name'=> $insertDataArr[13],
        'txn_status'=>$insertDataArr[14],
        'remittance_origin'=>$insertDataArr[15],
        'sender_remarks'=>$insertDataArr[16],
        'file_name'=> $datafiles['file_name'],    
        'by_ops_id'=> $datafiles['ops_id'],
        'date_created'=> new Zend_Db_Expr('NOW()')
        );
       
          $rs = $this->_db->insert(DbTable::TABLE_RATNAKAR_SETTLEMENT_RESPONSE, $data);
             return TRUE;
         } catch (Exception $e) {
              App_Logger::log($e->getMessage(), Zend_Log::ERR);
              throw new Exception($e->getMessage());
                return FALSE;
        }
          
    }
    
      public function checkresponseExist($tran_code) {
         
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RATNAKAR_SETTLEMENT_RESPONSE, array('id'));
        $select->where("settlement_ref_no= '".$tran_code."' ");
        
        $rs = $this->_db->fetchRow($select);
         if (empty($rs)) {
           return TRUE;
        } else {
           return FALSE;
        }
    }
    
    public function searchSettledRecords($params){
        
        $settledRecords = array();
        $from  =  $params['from_date'];
        $to = $params['to_date'];
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`l`.`card_number`,'".$decryptionKey."') as card_number");
        
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST . " as l", array('txn_code','medi_assist_id as member_id',$card_number,'txn_type','load_channel','txn_identifier_type','settlement_remarks','load_channel','mode','status_settlement','by_agent_id','by_corporate_id','date_settlement','status','narration', 'settlement_ref_no','amount','date_load','is_reversal','date_reversal'));
        $select->join(DbTable::TABLE_RATNAKAR_SETTLEMENT_RESPONSE . ' as resp', "resp.id = l.settlement_response_id ",array('id','sequence_no','bene_act_no','bene_act_name'));
        $select->join(DbTable::TABLE_RAT_DEBIT_DETAIL . ' as rdd', "rdd.debit_id = l.id", array('rdd.amount'));
        $select->join(DbTable::TABLE_PRODUCTS. " as p", "p.id = l.product_id ",array('p.name as product_name'));
        $select->joinLeft(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id = rdd.purse_master_id ",array('code as wlt_code'));
        $select->join(DbTable::TABLE_AGENTS . " as ag", "ag.id = l.by_agent_id  AND l.by_agent_id > 0",array('concat(ag.first_name," ",ag.last_name) as name','agent_code as code'));
        if(isset($params['card_number']) && $params['card_number'] !=''){ 
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $cardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$params['card_number']."','".$encryptionKey."')");
            $select->where("l.card_number = ? ", $cardNumber);
        }
        if(isset($params['settlement_ref_no']) && $params['settlement_ref_no'] !=''){
        $select->where("l.settlement_ref_no = '".$params['settlement_ref_no']."'");
         }
        $select->where("l.by_corporate_id = 0"); 
        $select->where("l.product_id = '".$params['product_id']."'");
        $select->where("l.status = '".STATUS_DEBITED."' ");
        $select->where("l.status_settlement = '".STATUS_SETTLED."'");
        if($from !=''){
            $select->where("l.date_load >=  '" .$from . "'");
        }
        if($to !=''){
            $select->where("l.date_load <=  '" .$to . "'");
        }
        $select->where('pm.is_virtual = ?', FLAG_NO);
        $agent_rows = $this->_db->fetchAll($select);
       if(!empty($agent_rows)){
           $settledRecords = array_merge($settledRecords,$agent_rows);
       }
       
       // Corporate Users 
       
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST . " as l", array('txn_code','medi_assist_id as member_id',$card_number,'txn_type','load_channel','txn_identifier_type','settlement_remarks','load_channel','mode','status_settlement','by_agent_id','by_corporate_id','date_settlement','status','narration', 'settlement_ref_no','amount','date_load','is_reversal','date_reversal'));
        $select->joinInner(DbTable::TABLE_RATNAKAR_SETTLEMENT_RESPONSE . ' as resp', "resp.id = l.settlement_response_id ",array('id','sequence_no','bene_act_no','bene_act_name'));
        $select->join(DbTable::TABLE_RAT_DEBIT_DETAIL . ' as rdd', "rdd.debit_id = l.id", array('rdd.amount'));
        $select->join(DbTable::TABLE_PRODUCTS. " as p", "p.id = l.product_id ",array('p.name as product_name'));
        $select->joinLeft(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id = rdd.purse_master_id ",array('code as wlt_code'));
        $select->join(DbTable::TABLE_CORPORATE_USER . " as cu", "cu.id = l.by_corporate_id AND l.by_corporate_id > 0 ",array('concat(cu.first_name," ",cu.last_name) as name','corporate_code as code'));
        if(isset($params['card_number']) && $params['card_number'] !=''){ 
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $cardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$params['card_number']."','".$encryptionKey."')");
            $select->where("l.card_number = ? ", $cardNumber);
        }
        if(isset($params['settlement_ref_no']) && $params['settlement_ref_no'] !=''){
            $select->where("l.settlement_ref_no = '".$params['settlement_ref_no']."'");
        }
        $select->where("l.by_agent_id = 0");
        $select->where("l.product_id = '".$params['product_id']."'");
        $select->where("l.status = '".STATUS_DEBITED."' ");
        $select->where("l.status_settlement = '".STATUS_SETTLED."'");
        if($from !=''){
            $select->where("l.date_load >=  '" .$from . "'");
        }
        if($to !=''){
            $select->where("l.date_load <=  '" .$to . "'");
        }
        $select->where('pm.is_virtual = ?', FLAG_NO);
       $corp_rows = $this->_db->fetchAll($select);
       if(!empty($corp_rows)){
           $settledRecords = array_merge($settledRecords,$corp_rows);
       }
       return $settledRecords;

    }
    
    
    public function searchUnSettledRecords($params){
        $unsettledRecords = array();
        $from  =  $params['from_date'];
        $to = $params['to_date'];
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`l`.`card_number`,'".$decryptionKey."') as card_number");

        
        // Agent Debited Records

        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST . " as l", array('txn_code','medi_assist_id as member_id',$card_number,'txn_type','load_channel','txn_identifier_type','settlement_remarks','load_channel','mode','status_settlement','by_agent_id','by_corporate_id','date_settlement','status','narration','date_load', 'settlement_ref_no','is_reversal','date_reversal'));
        $select->join(DbTable::TABLE_RAT_DEBIT_DETAIL . ' as rdd', "rdd.debit_id = l.id", array('rdd.amount'));
        $select->joinLeft(DbTable::TABLE_PRODUCTS. " as p", "p.id = l.product_id ",array('p.name as product_name'));
        $select->joinLeft(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id = rdd.purse_master_id ",array('code as wlt_code'));
        $select->joinLeft(DbTable::TABLE_AGENTS . " as ag", "ag.id = l.by_agent_id  AND l.by_agent_id > 0",array('concat(ag.first_name," ",ag.last_name) as name','agent_code as code'));        
        if(isset($params['card_number']) && $params['card_number'] !=''){
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $cardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$params['card_number']."','".$encryptionKey."')");

        $select->where("l.card_number = '".$cardNumber."'");

        }
       if(isset($params['settlement_ref_no']) && $params['settlement_ref_no'] !=''){
        $select->where("l.settlement_ref_no = '".$params['settlement_ref_no']."'");
         }
        $select->where("l.by_corporate_id = 0"); 
        $select->where("l.product_id = '".$params['product_id']."'");
        $select->where("l.status = '".STATUS_DEBITED."' ");
       // $select->where("l.is_reversal != '".REVERSAL_FLAG_YES."'");
        $select->where("l.status_settlement = '".STATUS_UNSETTLED."'");
        if($from !=''){
        $select->where("l.date_load >=  '" .$from . "'");
        }
        if($to !=''){
        $select->where("l.date_load <=  '" .$to . "'");
        }

        $select->where('pm.is_virtual = ?', FLAG_NO);
        $agent_rows = $this->_db->fetchAll($select);
       if(!empty($agent_rows)){
           $unsettledRecords = array_merge($unsettledRecords,$agent_rows);
       }
       
       // Corporate Users 
       
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST . " as l", array('txn_code','medi_assist_id as member_id',$card_number,'txn_type','load_channel','txn_identifier_type','settlement_remarks','load_channel','mode','status_settlement','by_agent_id','by_corporate_id','date_settlement','status','narration','date_load', 'settlement_ref_no','is_reversal','date_reversal'));
        $select->join(DbTable::TABLE_RAT_DEBIT_DETAIL . ' as rdd', "rdd.debit_id = l.id", array('rdd.amount'));
        $select->joinLeft(DbTable::TABLE_PRODUCTS. " as p", "p.id = l.product_id ",array('p.name as product_name'));
        $select->joinLeft(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id = rdd.purse_master_id ",array('code as wlt_code'));
        $select->joinLeft(DbTable::TABLE_CORPORATE_USER . " as cu", "cu.id = l.by_corporate_id AND l.by_corporate_id > 0 ",array('concat(cu.first_name," ",cu.last_name) as name','corporate_code as code'));

       
        if(isset($params['card_number']) && $params['card_number'] !=''){ 
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $cardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$params['card_number']."','".$encryptionKey."')");
            $select->where("l.card_number = ? ", $cardNumber);

        }
        if(isset($params['settlement_ref_no']) && $params['settlement_ref_no'] !=''){
        $select->where("l.settlement_ref_no = '".$params['settlement_ref_no']."'");
         }
        $select->where("l.by_agent_id = 0");
        $select->where("l.product_id = '".$params['product_id']."'");
        $select->where("l.status = '".STATUS_DEBITED."' ");
      //  $select->where("l.is_reversal != '".REVERSAL_FLAG_YES."'");
        $select->where("l.status_settlement = '".STATUS_UNSETTLED."'");
        if($from !=''){
        $select->where("l.date_load >=  '" .$from . "'");
        }
        if($to !=''){
        $select->where("l.date_load <=  '" .$to . "'");
        }
        $select->where('pm.is_virtual = ?', FLAG_NO);
       $corp_rows = $this->_db->fetchAll($select);
       if(!empty($corp_rows)){
           $unsettledRecords = array_merge($unsettledRecords,$corp_rows);
       }
     //  Util::debug($unsettledRecords);
       return $unsettledRecords;
    }
    
    
     public function exportsearchSettledRecords($params)
     {
        $dataArr = $this->searchSettledRecords($params);
        $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");
        $retData = array();
        if (!empty($dataArr)) {
            foreach ($dataArr as $key => $data) {
                $txn = isset($TXN_TYPE_LABELS[$data['txn_type']])?$TXN_TYPE_LABELS[$data['txn_type']]:'';
                $retData[$key]['transaction_date'] = $data['date_load'];
                $retData[$key]['product_name'] = $data['product_name'];
                $retData[$key]['bank_name'] = 'Ratnakar Bank';
                $retData[$key]['code'] = $data['code'];
                $retData[$key]['name'] = $data['name'];
//                $retData[$key]['card_pack_id'] = $data['card_pack_id'];
                $retData[$key]['card_number'] = Util::maskCard($data['card_number']);
                $retData[$key]['member_id'] = $data['member_id'];
                $retData[$key]['txn_code'] = $data['txn_code'];
                $retData[$key]['amount'] = $data['amount'];
                $retData[$key]['txn_type'] = $txn;
                $retData[$key]['status'] = ucfirst($data['status']);
                $retData[$key]['wlt_code'] = $data['wlt_code'];
                $retData[$key]['dr_cr'] = ucfirst($data['mode']);
                $retData[$key]['rrn_no'] = '';
                $retData[$key]['ack_no'] = '';
                $retData[$key]['decline_reason'] = '';
                $retData[$key]['mcc'] = '';
                $retData[$key]['tid'] = '';
                $retData[$key]['mid'] = '';
                $retData[$key]['load_channel'] = strtoupper($data['load_channel']);
                $retData[$key]['reversal_flag'] = ucwords($data['is_reversal']);
                $retData[$key]['date_reversal'] = $data['date_reversal'];
                $retData[$key]['mode'] = ucfirst($data['mode']);
                $retData[$key]['settlement_remarks'] = $data['settlement_remarks'];
                $retData[$key]['status_settlement'] = ucfirst($data['status_settlement']);
                $retData[$key]['date_settlement'] = $data['date_settlement'];
                $retData[$key]['bene_act_no'] = $data['bene_act_no'];
                $retData[$key]['bene_act_name'] = $data['bene_act_name'];
                $retData[$key]['settlement_ref_no'] = $data['settlement_ref_no'];
            }
        }
       
        return $retData;
     }
     
 
      public function exportsearchUnSettledRecords($params)
     {
        $dataArr = $this->searchUnSettledRecords($params);
        $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");
        $retData = array();
        if (!empty($dataArr)) {
            foreach ($dataArr as $key => $data) {
                $txn = isset($TXN_TYPE_LABELS[$data['txn_type']])?$TXN_TYPE_LABELS[$data['txn_type']]:'';
                $retData[$key]['date_load'] = $data['date_load'];
                $retData[$key]['product_name'] = $data['product_name'];
                $retData[$key]['bank_name'] = 'Ratnakar Bank';
                $retData[$key]['code'] = $data['code'];
                $retData[$key]['name'] = $data['name'];
                
//                $retData[$key]['card_pack_id'] = $data['card_pack_id'];
                $retData[$key]['card_number'] = Util::maskCard($data['card_number'],4);
                $retData[$key]['member_id'] = $data['member_id'];
                $retData[$key]['txn_code'] = $data['txn_code'];
                $retData[$key]['amount'] = $data['amount'];
                $retData[$key]['txn_type'] = $txn;
                $retData[$key]['status'] = ucfirst($data['status']);
                $retData[$key]['wlt_code'] = $data['wlt_code'];
                $retData[$key]['mode'] = ucfirst($data['mode']);
                $retData[$key]['rrn_no'] = '';
                $retData[$key]['ack_no'] = '';
                $retData[$key]['decline_reason'] = '';
                $retData[$key]['mcc'] = '';
                $retData[$key]['tid'] = '';
                $retData[$key]['mid'] = '';
                $retData[$key]['load_channel'] = strtoupper($data['load_channel']);
                $retData[$key]['reversal_flag'] = ucwords($data['is_reversal']);
                $retData[$key]['date_reversal'] = $data['date_reversal'];
                $retData[$key]['trans_mode'] = ucfirst($data['mode']);
                $retData[$key]['settlement_remarks'] = $data['narration'];
                $retData[$key]['status_settlement'] = ucfirst(STATUS_UNSETTLED);
                
            }
        }
       
        return $retData;
     }
}

	