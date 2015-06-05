<?php

/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */
class BatchAdjustment extends App_Model {

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
    protected $_name = DbTable::TABLE_BATCH_ADJUSTMENT;
    
    //
    //$data['card_number'] = $dataArr[0];
//                                $data['wallet_code'] = $dataArr[1];
//                                $data['mode'] = $dataArr[2];
//                                $data['amount'] = ($dataArr[3] / 100);
//                                $data['rrn'] = $dataArr[4];
//                                $data['narration'] = $dataArr[5];
//                                $data['file'] = $batchName;
//                                $data['product_id'] = $productInfo->id;
    //
        
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
	    try {
		$data = array(
		    'card_number' => Util::insertCardCrn($dataArr['card_number']),
		    'wallet_code' => $dataArr['wallet_code'],
		    'mode' => $dataArr['mode'],
		    'txn_type' => $txnType,
		    'amount' => $dataArr['amount'],
		    'rrn' => $dataArr['rrn'],
		    'narration' => $dataArr['narration'],
		    'status' => $dataArr['status'],
		    'file' => $dataArr['file'],
		    'by_ops_id'	    =>	$user->id,
		    'product_id'    =>	$dataArr['product_id'],
		    'callecs'	    =>  $dataArr['callecs'],
		    'failed_reason' =>	$dataArr['failed_reason'],
		    'date_created'  =>	new Zend_Db_Expr('NOW()')
		);
		$this->_db->insert(DbTable::TABLE_BATCH_ADJUSTMENT, $data);
		return $this->_db->lastInsertId(DbTable::TABLE_BATCH_ADJUSTMENT);
	    } catch (Exception $e) {
		App_Logger::log($e->getMessage(), Zend_Log::ERR);
		return false;
	    }
        } 
        return false;
    }
    
    public function checkDuplicate($dataArr) {
	
        $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	$dataArr['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$dataArr['card_number']."','".$encryptionKey."')"); 
	
        $sql = $this->select()
                ->where('card_number=?',$dataArr['card_number'])
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
    
    public function checkDuplicateFile($fileName,$productID = 0) {
        $sql = $this->select()
                ->where('file=?',$fileName);
        if( $productID > 0){
           $sql->where('product_id = ?',$productID); 
        }
        $sql->where('status NOT IN ("'.STATUS_TEMP.'","'.STATUS_DUPLICATE.'")');
        
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
		->from($this->_name , array('id','product_id',$card_number,'customer_master_id','cardholder_id','purse_master_id','customer_purse_id','txn_type','wallet_code','mode','amount','rrn','status','narration','txn_code','failed_reason','file','by_ops_id','date_created','date_failed','date_updated'))
                ->where('id=?',$id);
        return $this->fetchRow($sql);
    }
    
    
    public function doManualAdjustment($dataArr) {
        $cardholderModel = new Corp_Ratnakar_Cardholders();
        $cardloadModel = new Corp_Ratnakar_Cardload();
        $customerMasterModel = new CustomerMaster();
        $apiResp = TRUE;
        
        $productInfo = $dataArr['product_id'];

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
                    'product_id'    => $productInfo
                ));
                $purseInfo = $customerMasterModel->getCustomerPurseInfo($cardholderInfo['rat_customer_id'], $dataArr['wallet_code']);
		
                if(!empty($cardholderInfo)) {
                    $customerMasterId = $cardholderInfo['customer_master_id'];
                    $cardholderId = $cardholderInfo['id'];
                    $purseMasterId = $purseInfo['purse_master_id'];
                    $custPurseId = $purseInfo['customer_purse_id'];
                    if(strtolower($dataArr['mode']) == strtolower(TXN_MODE_CR)) {
                        $validator = array(
                            'load_request_id' =>  $dataArr['id'],
                            'customer_master_id' =>  $customerMasterId,
                            'purse_master_id' =>  $purseMasterId,//WalletCode
                            'customer_purse_id' =>  $custPurseId,
                            'amount' =>  $dataArr['amount'],
                        );
                        $flgValidate = $baseTxn->chkAllowRatCrAdj($validator);
                        if($flgValidate) {                            
                            
                            if($purseInfo['is_virtual'] == FLAG_NO && $dataArr['card_number'] != '') {
                                $cardLoadData = array(
                                    'amount' => (string) $dataArr['amount'],
                                    'crn' => $dataArr['card_number'],
                                    'agentId' => $dataArr['by_ops_id'],
                                    'transactionId' => $txnCode,
                                    'currencyCode' => CURRENCY_INR_CODE,
                                    'countryCode' => COUNTRY_IN_CODE
                                    );
				/*
				 * Call to ECS
				 * if callecs is N there is no call to ecs
				 * else if callecs is Y then call to ecs is true
				 */ 
                                if(DEBUG_MVC || ($dataArr['callecs'] == FLAG_N)) { 
                                    $apiResp = TRUE;
                                } else {  
				    $ecsApi = new App_Socket_ECS_Corp_Transaction();
                                    $apiResp = $ecsApi->cardLoad($cardLoadData); 
				}
                            }

                            if ($apiResp === TRUE) {
                                $baseTxnParams = array(
                                    'txn_code' => $txnCode, 
                                    'customer_master_id' =>  $customerMasterId, 
                                    'product_id' =>  $productInfo, 
                                    'purse_master_id' =>  $purseMasterId, 
                                    'customer_purse_id' =>  $custPurseId, 
                                    'amount' =>  $dataArr['amount'], 
                                    'mode' => $dataArr['mode'], 
                               );

                                $res = $baseTxn->successRatManualAdj($baseTxnParams);

                                $resp = array(
                                    'status' => TRUE,
                                );
                            } else {
                                
                                $resp = array(
                                    'status'        => FALSE,
                                    'failed_reason' => $ecsApi->getError(),
                                );
                            }
                        } else {
                            $resp = array(
                                'status' => FALSE,
                                'failed_reaspon'    => 'Adjustment failed'
                            );                                    
                        }
                        
                    } elseif(strtolower($dataArr['mode']) == strtolower(TXN_MODE_DR)) {
                        //chkAllowDrAdj
                        $validator = array(
                            'customer_master_id' =>  $customerMasterId,
                            'purse_master_id' =>  $purseMasterId,//WalletCode
                            'customer_purse_id' =>  $custPurseId,
                            'amount' =>  $dataArr['amount'],
                            'product_id' =>  $productInfo,
                        );
                        $flgValidate = $baseTxn->chkAllowDrAdj($validator);
                        if(isset($flgValidate['status']) && $flgValidate['status'] == STATUS_SUCCESS) {

                            if($purseInfo['is_virtual'] == FLAG_NO && $dataArr['card_number'] != '') {
                                $cardLoadData = array(
                                    'amount' => (string) $dataArr['amount'],
                                    'crn' => $dataArr['card_number'],
                                    'agentId' => $dataArr['by_ops_id'],
                                    'transactionId' => $txnCode,
                                    'currencyCode' => CURRENCY_INR_CODE,
                                    'countryCode' => COUNTRY_IN_CODE
                                );
				/*
				 * Call to ECS
				 * if callecs is N there is no call to ecs
				 * else if callecs is Y then call to ecs is true
				 */
				if(DEBUG_MVC || ($dataArr['callecs'] == FLAG_N)) {
                                    $apiResp = TRUE;
                                } else {
                                    $ecsApi = new App_Socket_ECS_Corp_Transaction();
                                    $apiResp = $ecsApi->cardDebit($cardLoadData); // bypassing for testing
                                }
                            }

                            if ($apiResp === TRUE) {
                                $baseTxnParams = array(
                                    'txn_code' =>  $txnCode, 
                                    'customer_master_id' =>  $customerMasterId, 
                                    'product_id' =>  $productInfo, 
                                    'purse_master_id' =>  $purseMasterId, 
                                    'customer_purse_id' =>  $custPurseId, 
                                     'amount' =>  $dataArr['amount'], 
                                     'mode' => $dataArr['mode'], 
                                     'adjust_id' =>  $dataArr['id'],
                                );

                                $cardloadModel->manualMapLoadTxn($baseTxnParams);

                                $res = $baseTxn->successRatManualAdj($baseTxnParams);
                                $resp = array(
                                    'status' => TRUE,
                                );
                            } else {                                

                                $resp = array(
                                    'status'            => FALSE,
                                    'failed_reason'     => $ecsApi->getError(),
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
        
        $sql = $this->select();
        if($cardNumber != '') {
	    
	    $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	    $card_numberEnc = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')"); 
	    
	    $sql->where('card_number=?',$card_numberEnc);
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
      
	$encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	$card_numberEnc = new Zend_Db_Expr("AES_ENCRYPT('".$param['card_number']."','".$encryptionKey."')"); 
	
        $sql = $this->select()
                ->where('card_number=?',$card_numberEnc)
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
        /*
        $whereCon = ' 1 ';
        if(isset($param['card_number'])) {
            //throw new App_Exception('Invalid Card Number');
            $whereCon .= ' AND card_number="'.$param['card_number'].'" ';
        }
        
        if(isset($param['card_pack_id'])) {
            $whereCon .= ' AND card_pack_id="'.$param['card_pack_id'].'" ';
        }
        
        if(isset($param['product_id'])) {
           $whereCon .= ' AND product_id="'.$param['product_id'].'" ';
        }
        
        if(isset($param['product_id'])) {
           $whereCon .= ' AND product_id="'.$param['product_id'].'" ';
        } */ 
	$whereCon = array();
	if(isset($param['card_number'])) {
	    $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $param['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$param['card_number']."','".$encryptionKey."')");
	    $whereCon['card_number'] = $param['card_number'] ;  
	}
	if(isset($param['card_pack_id'])){
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
                ->from(DbTable::TABLE_BATCH_ADJUSTMENT, array('id', $card_number, 'wallet_code', 'mode', 'amount', 'rrn','narration', 'status','failed_reason'))
                ->where($whereString)
                ->where('status in("'.STATUS_SUCCESS.'","'.STATUS_FAILED.'","'.STATUS_DUPLICATE.'","'.STATUS_PENDING.'","'.STATUS_IN_PROCESS.'")')
                ->order('id DESC');
        return $this->_db->fetchAll($details);
    }
    
    
    public function searchBatchByDate($param) {
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	$card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $details = $this->_db->select()
                ->from(DbTable::TABLE_BATCH_ADJUSTMENT, array('id', $card_number, 'wallet_code', 'mode', 'amount', 'rrn','narration', 'status','failed_reason','date_created'))
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
    
    
    public function getPendingRecords($productConst,$status = STATUS_PENDING) {
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	$card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
	
        $details = $this->_db->select()
                ->from(DbTable::TABLE_BATCH_ADJUSTMENT ." as ba", array('id', $card_number, 'wallet_code', 'mode', 'amount', 'rrn','narration', 'status','failed_reason','product_id','callecs'))
                ->join(DbTable::TABLE_PRODUCTS ." as p","ba.product_id = p.id AND p.const = '".$productConst."'", array('p.id as pid','p.const'))
                ->where('ba.status=?',$status)
                ->order('ba.id DESC')
                ->limit(RAT_MANUAL_ADJUSTMENT_LIMIT);
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
      $sql = $this->select()
                ->where('id=?',$id);
        return $this->fetchRow($sql);
    }
    
    public function getDistinctBatchs($productId = 0) {
        $sql = $this->select()
                ->from($this->_name, array('id','file'))
                ->order('file asc')
                ->group('file');
        if($productId){
            $sql->where('product_id=?',$productId);
        }
        $batch = $this->fetchAll($sql);
       
        $dataArray = array();
        foreach ($batch as $val) {
            $dataArray[$val['file']] = $val['file'];
        }
       
        return $dataArray;        
    }
    
    public function batchAdjustmentProductWise($productConst){
    
    $flgSucc=0;
    $flgFail=0;    
        
    $maRequest = $this->getPendingRecords($productConst);
    $countTxn = count($maRequest);
    if($countTxn>0){
        foreach($maRequest as $req){
            $flg = $this->doManualAdjustment($req);
            if($flg == TRUE) $flgFail++;
            else $flgSucc++;
        }
    }
    $msg = $countTxn.' manual adjustment processed for '.$productConst.' and '.$flgFail.' transaction failed and '.$flgSucc.' transaction successfully executed. ';    
    return $msg;
    }
    
    
     public function getManualAdjustment($param) {
         //Enable DB Slave
        $this->_enableDbSlave();
        $details = $this->_db->select()
                ->from(DbTable::TABLE_BATCH_ADJUSTMENT, array('sum(amount) as manual_adj_total'))
                ->where('customer_purse_id =?',$param['customer_purse_id'])
                ->where('txn_type =?',$param['txn_type'])
                ->where("DATE(date_created) = '" . $param['date'] . "'")
                ->where('status in("'.STATUS_SUCCESS.'")');
        
        $row = $this->_db->fetchRow($details);
        //Disable DB Slave
        $this->_disableDbSlave();
        return $row;
    }
    
    public function getAllManualAdjustment($params) {
        //Enable DB Slave
        $this->_enableDbSlave();
        $details = $this->_db->select()
                ->from(DbTable::TABLE_BATCH_ADJUSTMENT, array('txn_type', 'sum(amount) as amount'))
                ->where('customer_purse_id =?',$params['customer_purse_id'])
                ->where("txn_type in ('".TXNTYPE_DEBIT_MANUAL_ADJUSTMENT."', '".TXNTYPE_CREDIT_MANUAL_ADJUSTMENT."')")
                ->where("DATE(date_created) = '" . $params['date'] . "'")
                ->where('status in("'.STATUS_SUCCESS.'")')
                ->group("customer_purse_id")
                ->group("txn_type")
                ->limit(2);
        
        $row = $this->_db->fetchAll($details);
        //Disable DB Slave
        $this->_disableDbSlave();
        $arr = array('manual_txn_cr' => 0, 'manual_txn_dr' => 0);
        if($row) {
        foreach($row as $val){
                if($val['txn_type'] == TXNTYPE_CREDIT_MANUAL_ADJUSTMENT) {
                    $arr['manual_txn_cr'] = $val['amount'];
                } else {
                    $arr['manual_txn_dr'] = $val['amount'];
                }
           }
        }
        return $arr;
    }
}