<?php

/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */
class KotakBatchAdjustment extends App_Model {

    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'id';
    const FILE_SEPRATOR = ','; 

    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_KOTAK_BATCH_ADJUSTMENT;
    
     
    public function insertMasterData($dataArr) {
        if(!empty($dataArr) && isset($dataArr['card_number'])) {
        $user = Zend_Auth::getInstance()->getIdentity();
        if(strtolower($dataArr['mode']) == TXN_MODE_DR){
            $txnType = TXNTYPE_DEBIT_MANUAL_ADJUSTMENT;
        }elseif(strtolower($dataArr['mode']) == TXN_MODE_CR){
            $txnType = TXNTYPE_CREDIT_MANUAL_ADJUSTMENT;
        } else {
            $txnType = '';
        }
	if(!empty($dataArr['card_number'])){ 
	    $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	    $card_num = new Zend_Db_Expr("AES_ENCRYPT('".$dataArr['card_number']."','".$encryptionKey."')"); 
	} 
        $data = array(
            'card_number' => $card_num,
            'wallet_code' => $dataArr['wallet_code'],
            'mode' => $dataArr['mode'],
            'txn_type' => $txnType,
            'amount' => $dataArr['amount'],
            'rrn' => $dataArr['rrn'],
            'narration' => $dataArr['narration'],
            'status' => $dataArr['status'],
            'file' => $dataArr['file'],
            'by_ops_id' => $user->id,
            'product_id' => $dataArr['product_id'],
            'failed_reason' => $dataArr['failed_reason'],
            'date_created' => new Zend_Db_Expr('NOW()')
        );
        return $this->insert($data);
        } 
        return false;
    }
    
    public function checkDuplicate($dataArr) {
        $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	$card_num = new Zend_Db_Expr("AES_ENCRYPT('".$dataArr['card_number']."','".$encryptionKey."')"); 
        $sql = $this->select()
                ->where('card_number=?',$card_num)
                ->where('mode=?',$dataArr['mode'])
                ->where('rrn=?',$dataArr['rrn'])
                ->where('status<>?',STATUS_TEMP)
                ->where('wallet_code=?',$dataArr['wallet_code']);
                //->where('status="'.STATUS_FREE.'" OR status="'.STATUS_BLOCKED.'"');

        $rs = $this->fetchRow($sql);
        if(!empty($rs)) {
            return TRUE;
        }
        return FALSE;
    }
    
    public function checkDuplicateFile($fileName) {
        $sql = $this->select()
                ->where('file=?',$fileName)
                ->where('status NOT IN ("'.STATUS_TEMP.'","'.STATUS_DUPLICATE.'")');
        $rs = $this->fetchRow($sql);
        if(!empty($rs)) {
            return TRUE;
        }
        return FALSE;
    }
    
     
    public function getCrnInfobyMasterId($id) {
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
	
	$sql = $this->select()
	       ->from($this->_name, array('id','product_id',$card_number,'customer_master_id','cardholder_id','purse_master_id','customer_purse_id','txn_type','wallet_code','mode','amount','rrn','status','narration','txn_code','failed_reason','file','by_ops_id','date_created','date_failed','date_updated'))
                ->where('id=?',$id);
        return $this->fetchRow($sql);
    }
    
    
    public function doManualAdjustment($dataArr) {
        //$cardholderModel = new Corp_Ratnakar_Cardholders();
        $cardholderModel = new Corp_Kotak_Customers();
        $customerMasterModel = new CustomerMaster();
        
        if(!empty($dataArr)) {
            $baseTxn = new BaseTxn();            
            $customerMasterId = 0;
            $cardholderId = 0;
            $purseMasterId = 0;
            $custPurseId = 0;
              try {            
                $txnCode = $baseTxn->generateTxncode();                  
                $this->updateBatchRequest(array(
                       'id'     => $dataArr['id'],
                       'status' => STATUS_IN_PROCESS, 
                        'txn_code'  => $txnCode
                ));
                $cardholderInfo = $cardholderModel->getCardholderInfoByCardNumber(array(
                    'card_number'   => $dataArr['card_number'],
                    'product_id'    => $dataArr['product_id'],
                ));
                $purseInfo = $customerMasterModel->getKotakCustomerPurseInfo($cardholderInfo['kotak_customer_id'], $dataArr['wallet_code']);
                
                if(!empty($cardholderInfo)) {
                    $customerMasterId = $cardholderInfo['customer_master_id'];
                    $cardholderId = $cardholderInfo['id'];
                    $purseMasterId = $purseInfo['purse_master_id'];
                    $custPurseId = $purseInfo['customer_purse_id'];
                    if(strtolower($dataArr['mode']) == strtolower(CR)) {
                        $validator = array(
                            'load_request_id' =>  $dataArr['id'],
                            'customer_master_id' =>  $customerMasterId,
                            'purse_master_id' =>  $purseMasterId,//WalletCode
                            'customer_purse_id' =>  $custPurseId,
                            'amount' =>  $dataArr['amount'],
                        );
                        $flgValidate = $baseTxn->chkAllowRatCrAdj($validator);
                        if($flgValidate) {
                                $baseTxnParams = array(
                                  'txn_code' => $txnCode, 
                                  'customer_master_id' =>  $customerMasterId, 
                                  'product_id' =>  $dataArr['product_id'],
                                  'purse_master_id' =>  $purseMasterId, 
                                  'customer_purse_id' =>  $custPurseId, 
                                   'amount' =>  $dataArr['amount'], 
                                   'mode' => $dataArr['mode'], 
                                );

                                $res = $baseTxn->successRatManualAdj($baseTxnParams);
                                if($res == TRUE) {
                                    $resp = array(
                                        'status' => TRUE,
                                    );
                                } else {
                                    $resp = array(
                                        'status' => FALSE
                                    );                                    
                                }
                        } else {
                                    $resp = array(
                                        'status' => FALSE,
                                        'failed_reaspon'    => 'Adjustment failed'
                                    );                                    
                        }
                        
                    } elseif(strtolower($dataArr['mode']) == strtolower(DR)) {
                        //chkAllowDrAdj
                        $validator = array(
                            'customer_master_id' =>  $customerMasterId,
                            'purse_master_id' =>  $purseMasterId,//WalletCode
                            'customer_purse_id' =>  $custPurseId,
                            'amount' =>  $dataArr['amount'],
                            'product_id' =>  $dataArr['product_id'],
                        );
                        $flgValidate = $baseTxn->chkAllowDrAdj($validator);
                        if(isset($flgValidate['status']) && $flgValidate['status'] == STATUS_SUCCESS) {
                                $baseTxnParams = array(
                                  'txn_code' =>  $txnCode, 
                                  'customer_master_id' =>  $customerMasterId, 
                                  'product_id' =>  $dataArr['product_id'],
                                  'purse_master_id' =>  $purseMasterId, 
                                  'customer_purse_id' =>  $custPurseId, 
                                   'amount' =>  $dataArr['amount'], 
                                   'mode' => $dataArr['mode'], 
                                );

                                $res = $baseTxn->successRatManualAdj($baseTxnParams);
                                if($res == TRUE) {
                                    $resp = array(
                                        'status' => TRUE,
                                    );
                                } else {
                                    $resp = array(
                                        'status' => FALSE
                                    );                                    
                                }                        
                        } else {
                            $resp = array(
                                'status'            => ($flgValidate['status'] == STATUS_SUCCESS) ? TRUE : FALSE,
                                'failed_reason'     => $flgValidate['failed_reason'],
                            );
                        }
                    }
                    } else {
                        $resp = array(
                            'status'        => FALSE,
                            'failed_reason' => 'Invalid Cardholder',
                        );
                    }
                    $this->updateBatchRequest(array(
                       'id'     => $dataArr['id'],
                        'status' => ($resp['status'] == TRUE) ? STATUS_SUCCESS : STATUS_FAILED, 
                        'failed_reason' => (!empty($resp['failed_reason']) ? $resp['failed_reason'] : ''), 
                        'customer_master_id' => $customerMasterId,
                        'cardholder_id' => $cardholderId,
                        'purse_master_id' => $purseMasterId,
                        'customer_purse_id' => $custPurseId,
                    ));
                    } catch (App_Exception $e) {
                        $errorMsg = $e->getMessage();
                        $updateArr = array(
                                        'id'    => $dataArr['id'],
                                        'status' => STATUS_FAILED, 
                                        'date_failed' => new Zend_Db_Expr('NOW()'),
                                        'failed_reason' => $e->getMessage(),
                                        'customer_master_id' => $customerMasterId,
                                        'cardholder_id' => $cardholderId,
                                         'purse_master_id' => $purseMasterId,
                                         'customer_purse_id' => $custPurseId,
                                    );
                        $this->updateBatchRequest($updateArr);
                        return FALSE;
                    } catch (Exception $e) {
                        $errorMsg = $e->getMessage();
                        $updateArr = array(
                                         'id'    => $dataArr['id'],
                                        'status' => STATUS_FAILED, 
                                        'date_failed' => new Zend_Db_Expr('NOW()'),
                                        'failed_reason' => $e->getMessage(),
                                        'customer_master_id' => $customerMasterId,
                                        'cardholder_id' => $cardholderId,
                                        'purse_master_id' => $purseMasterId,
                                        'customer_purse_id' => $custPurseId,
                                    );
                        $this->updateBatchRequest($updateArr);
                        return FALSE;
                    }
                }
                if(isset($resp['status']) && $resp['status'] == TRUE) {
                    return TRUE;
                }
                return FALSE;
    }
    
    
  public function getCRNInfo($cardNumber, $cardPackId='', $maId='') {
        
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $cardFetch = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
	
        $sql = $this->select()
	       ->from($this->_name, array('id','product_id',$cardFetch,'customer_master_id','cardholder_id','purse_master_id','customer_purse_id','txn_type','wallet_code','mode','amount','rrn','status','narration','txn_code','failed_reason','file','by_ops_id','date_created','date_failed','date_updated'));
	
	
        if($cardNumber != '') {
	    $cardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$decryptionKey."')");
	    $sql->where('card_number=?',$cardNumber);
        }
        if($cardPackId != '') {
                $sql->where('card_pack_id=?',$cardPackId);
        }                        
        if($maId != '') {
                $sql->where('member_id=?',$maId);
        }                        
        $sql->where('status=?',STATUS_FREE);
        return $this->fetchRow($sql);
    }
    
    
  public function getInfoByCardNumberNPackId(array $param) {
//        if(!isset($param['medi_assist_id'])) {
//            throw new App_Exception('Invalid Medi Assit Id');
//        }
        
        if(!isset($param['card_number'])) {
            throw new App_Exception('Invalid Card Number');
        }
        
        if(!isset($param['card_pack_id'])) {
            throw new App_Exception('Invalid Card Pack Id');
        }
        
        if(!isset($param['product_id'])) {
            throw new App_Exception('Invalid Product Id');
        }
	
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $cardFetch = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
	$param['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$param['card_number']."','".$decryptionKey."')");
	
        $sql = $this->select()
		 ->from($this->_name, array('id','product_id',$cardFetch,'customer_master_id','cardholder_id','purse_master_id','customer_purse_id','txn_type','wallet_code','mode','amount','rrn','status','narration','txn_code','failed_reason','file','by_ops_id','date_created','date_failed','date_updated'))
                ->where('card_number=?',$param['card_number'])
                ->where('card_pack_id=?',$param['card_pack_id'])
                ->where('product_id=?',$param['product_id']);
        if(isset($param['status'])) {
                $sql->where('status=?',$param['status']);
        }
        //echo $sql;
        return $this->fetchRow($sql);
    }
        
    
    
  public function updateStatusByCardNumberNPackId(array $param) {
      
        if(!isset($param['status'])) {
            throw new App_Exception ('CRN Master: Invalid status provided');
        }               
        
        if(!isset($param['card_number'])) {
            throw new App_Exception ('CRN Master Update: Invalid Card Number provided');
        }
	
	$whereCon =array();
	if(isset($param['card_number'])) {
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $param['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$param['card_number']."','".$encryptionKey."')"); 
            $whereCon['card_number'] = $param['card_number'] ;  
        }
	if(isset($param['card_pack_id'])) { 
            $whereCon['card_pack_id'] = $param['card_pack_id'] ;
        }
        if(isset($param['product_id'])) {
            $whereCon['product_id'] = $param['product_id'] ;
        }

        $dataArr = array(
          'status'  => $param['status']
        );
        return $this->update($dataArr, $whereCon);


   }
        
 
    public function searchBatch($param) {
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $columnName = $param['searchCriteria'];
        $keyword = $param['keyword'];
        if($columnName == 'card_number'){
            $columnName = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."')");
        } 
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $whereString = "$columnName LIKE '%$keyword%'";
	 
        $details = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_BATCH_ADJUSTMENT, array('id', $card_number, 'wallet_code', 'mode', 'amount', 'rrn','narration', 'status','failed_reason'))
                ->where($whereString)
                ->where('status in("'.STATUS_SUCCESS.'","'.STATUS_FAILED.'","'.STATUS_DUPLICATE.'","'.STATUS_PENDING.'","'.STATUS_IN_PROCESS.'")')
                ->order('id DESC');
        return $this->_db->fetchAll($details);
    }
    
    
    public function searchBatchByDate($param) {
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	$card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $details = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_BATCH_ADJUSTMENT, array('id', $card_number, 'wallet_code', 'mode', 'amount', 'rrn','narration', 'status','failed_reason','date_created'))
                //->where($whereString)
                ->where('status in("'.STATUS_SUCCESS.'","'.STATUS_FAILED.'","'.STATUS_DUPLICATE.'","'.STATUS_PENDING.'","'.STATUS_IN_PROCESS.'")')
                ->order('id DESC');
        if($param['start_date'] != '') {
            $details->where ('date(date_created) >= ?',$param['start_date']);
        }
        
        if($param['end_date'] != '') {
            $details->where ('date(date_created) <= ?',$param['end_date']);
        }
        
        if($param['file'] != '') {
            $details->where ('file= ?',$param['file']);
        }
        if($param['product_id'] != '') {
            $details->where ('product_id= ?',$param['product_id']);
        }
        return $this->_db->fetchAll($details);
    }
    
    
    public function getPendingRecords($status = STATUS_PENDING) {
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	$card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $details = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_BATCH_ADJUSTMENT, array('id', $card_number, 'wallet_code', 'mode', 'amount', 'rrn','narration', 'status','failed_reason'))
                ->where('status=?',$status)
                ->order('id DESC')
                ->limit(KOTAK_MANUAL_ADJUSTMENT_LIMIT);
        return $this->_db->fetchAll($details);
    }
    
    
    public function updateBatchRequest($param) {
        $data = array(
          'status'  => $param['status'],
          'failed_reason'  => $param['failed_reason'],
          'customer_master_id' => $param['customer_master_id'],
            'cardholder_id' => $param['cardholder_id'],
            'purse_master_id' => $param['purse_master_id'],
            'customer_purse_id' => $param['customer_purse_id'],
        );
        
        if(isset($param['txn_code'])) {
            $data['txn_code'] = $param['txn_code'];
        }
        if($data['status'] == STATUS_FAILED) {
            $data['date_failed'] = new Zend_Db_Expr('NOW()');
        }
        $this->update($data, 'id="'.$param['id'].'"');
    }

    
    public function bulkManualUpdate($dataArr,$status) {
        if(!empty($dataArr)) {
            foreach ($dataArr as $id) {
                $info = $this->getBatchInfobyId($id);
                if(!empty($info)) {
                    $upArr = array(
                        'status' => $status
                    );
                    $this->update($upArr, "id='".$id."'");
                }
            }
        }
    }
    
        
    public function getBatchInfobyId($id) {
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	$card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
	
      $sql = $this->select()
		->from($this->_name, array('id','product_id',$card_number,'customer_master_id','cardholder_id','purse_master_id','customer_purse_id','txn_type','wallet_code','mode','amount','rrn','status','narration','txn_code','failed_reason','file','by_ops_id','date_created','date_failed','date_updated'))
                ->where('id=?',$id);
        return $this->fetchRow($sql);
    }
    
    public function getDistinctBatchs() {
        $sql = $this->select()
                ->from($this->_name, array('id','file'))
                ->order('file asc')
                ->group('file');
        $batch = $this->fetchAll($sql);
        $dataArray = array('' => 'Select Batch');
        foreach ($batch as $val) {
            $dataArray[$val['file']] = $val['file'];
        }
       
        return $dataArray;        
    }
    
   public function manualAdjustment($dataArr){
        $productModel = new Products(); 
        // Semi Close
        $product = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_SEMICLOSE_GPR);                
        $productInfo = $productModel->getProductInfoByUnicode($product->product->unicode);
        $dataArr['product_id'] = $productInfo->id;
        $this->doManualAdjustment($dataArr);
        //Open loop
        $product = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_OPENLOOP_GPR);                
        $productInfo = $productModel->getProductInfoByUnicode($product->product->unicode);
        $dataArr['product_id'] = $productInfo->id;
        $this->doManualAdjustment($dataArr);
   }
   
    public function getManualAdjustment($param) {

        $details = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_BATCH_ADJUSTMENT, array('sum(amount) as manual_adj_total'))
                ->where('customer_purse_id =?',$param['customer_purse_id'])
                ->where('txn_type =?',$param['txn_type'])
                ->where("DATE(date_created) = '". $param['date'] ."'")
                ->where('status in("'.STATUS_SUCCESS.'")');
        
        return $this->_db->fetchRow($details);
    }
}