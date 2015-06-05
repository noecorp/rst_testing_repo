<?php

/**
 * Model that manages ratnakar cardloads
 *
 * @package Operation_Models
 * @copyright transerv
 */
class Corp_Boi_Cardload extends Corp_Boi {

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
    protected $_name = DbTable::TABLE_BOI_CORP_LOAD_REQUEST;
    const DATA_ELEMENT_BALANCE = '54';
    const INCOME_ACCOUNT_WITH_BOI = '01010PLCR099   ';
    const SERVICE_TAX_AC = '01010PLCR099   ';
    const CARD_POOL_AC = '01220SUNDEP031 ';
    const SERVICE_TAX = '14';
    const ACCOUNT_NARRATION_CAPTION = ' STAR Monetary Reward';
    const NARRATION_LENGTH = 29;

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

    /**
     * Retrieves all the products attached to
     * the specified master product
     * 
     * @param integer $resourceId
     * @access public
     * @return void
     */
    /*
     * showPendingCardloadDetails , show load requests from batch table which have upload status as temp
     */
    public function showPendingCardloadDetails($batchName) {
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	$card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BOI_CORP_LOAD_REQUEST_BATCH, array('id','product_id','type','txn_identifier_type',$card_number,'member_id','amount','currency','narration','wallet_code','txn_no','card_type','mode','corporate_id','by_ops_id','ip','batch_name','date_created','date_updated','failed_reason','upload_status'));
        $select->where('upload_status = ?', STATUS_TEMP);
        $select->where('batch_name = ?', $batchName);
        $select->order('id ASC');
        return $this->_db->fetchAll($select);
    }

    /*
     * insertLoadrequestBatch , insert data from file into the batch table
     */

    public function insertLoadrequestBatch($dataArr, $batchName, $status) {
        $user = Zend_Auth::getInstance()->getIdentity();
        try {
            $data = array(
                'txn_identifier_type' => strtolower($dataArr[0]),
                'card_number' => (strtolower($dataArr[0]) == BOI_NSDC_WALLET_TXN_IDENTIFIER_CN) ? $dataArr[1] : 0,
                'member_id' => (strtolower($dataArr[0]) == BOI_NSDC_WALLET_TXN_IDENTIFIER_MI) ? $dataArr[1] : 0,
                'amount' => $dataArr[2],
                'currency' => $dataArr[3],
                'narration' => $dataArr[4],
                'wallet_code' => $dataArr[5],
                'txn_no' => $dataArr[6],
                'card_type' => strtolower($dataArr[7]),
                'corporate_id' => $dataArr[8],
                'mode' => strtolower($dataArr[9]),
                'ip' => $this->formatIpAddress(Util::getIP()),
                'by_ops_id' => $user->id,
                'batch_name' => $batchName,
                'product_id' => $dataArr['product_id'],
                'type' => $dataArr['type'],
                'upload_status' => $status,
                'date_created' => new Zend_Db_Expr('NOW()')
            );
	    $data['card_number'] = Util::insertCardCrn($data['card_number']);
            $this->_db->insert(DbTable::TABLE_BOI_CORP_LOAD_REQUEST_BATCH, $data); 
            return TRUE;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
           // echo $e->getMessage(); 
	    throw new Exception($e->getMessage());
        }
    }

    /*
     * bulkAddCardload add card load request from batch table
     */

    public function bulkAddCardload($idArr, $batchName) {
        if (empty($idArr)) {
            throw new Exception('Data missing for add cardholder');
        }
        $custModel = new Corp_Boi_Customers();
        $custPurseModel = new Corp_Boi_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $baseTxn = new BaseTxn();
        $user = Zend_Auth::getInstance()->getIdentity();
        $str = '';
        try {
            // Foreach selected id value
            foreach ($idArr as $id) {
		$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
		$card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
	
                $select = $this->_db->select()
                        ->from(DbTable::TABLE_BOI_CORP_LOAD_REQUEST_BATCH,array('id','product_id','type','txn_identifier_type',$card_number,'member_id','amount','currency','narration','wallet_code','txn_no','card_type','mode','corporate_id','by_ops_id','ip','batch_name','date_created','date_updated','failed_reason','upload_status'))
                        ->where("id =?", $id);

                $dataArr = $this->_db->fetchRow($select);
              $searchArr = array(
                    'member_id' => ($dataArr['member_id'] != 0) ? $dataArr['member_id'] : '',
                    'card_number' => ($dataArr['card_number'] != 0) ? $dataArr['card_number'] : '');

                $cardholderDetails = $custModel->getCardholderInfo($searchArr);
                if(!isset($cardholderDetails->id) || $cardholderDetails->id == '')
                {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = 'Cardholder not found';
                    $dateFailed = new Zend_Db_Expr('NOW()');
                    $dateLoad = new Zend_Db_Expr('NOW()');   
                }
                else 
                {
                    switch($cardholderDetails->cardholder_status)
                    {
                        case STATUS_PENDING:
                                                 $loadStatus = STATUS_FAILED;
                                                 $failedReason = 'Cardholder Registration pending with ECS';
                                                 $dateFailed = new Zend_Db_Expr('NOW()');  
                                                 $dateLoad = new Zend_Db_Expr('NOW()');
                                                 break;
                        case STATUS_ECS_FAILED:
                                                 $loadStatus = STATUS_FAILED;
                                                 $failedReason = 'Cardholder Registration failed with ECS';
                                                 $dateFailed = new Zend_Db_Expr('NOW()'); 
                                                 $dateLoad = new Zend_Db_Expr('NOW()');
                                                 break;  
                        case STATUS_INACTIVE:
                                                 $loadStatus = STATUS_FAILED;
                                                 $failedReason = 'Cardholder Inactive';
                                                 $dateFailed = new Zend_Db_Expr('NOW()');  
                                                 $dateLoad = new Zend_Db_Expr('NOW()');
                                                 break;  
                        default :
                                                 $loadStatus = STATUS_PENDING;
                                                 $failedReason = '';
                                                 $dateFailed = '';   
                                                 $dateLoad = '';
                                                 break;

                    }
                }
               
                $cardNumber = ($searchArr['card_number'] != '') ? $searchArr['card_number'] : $cardholderDetails->card_number;
                $mediAssistId = ($searchArr['member_id'] != '') ? $searchArr['member_id'] : $cardholderDetails->member_id;
                $customerMasterId = ($cardholderDetails->customer_master_id != '') ? $cardholderDetails->customer_master_id : 0;
                $cardholderId = ($cardholderDetails->id != '') ? $cardholderDetails->id : 0;
                $ratCustomerId = ($cardholderDetails->boi_customer_id != '') ? $cardholderDetails->boi_customer_id : 0;
                $customerPurseId = 0;
                // Master Purse id
                $product = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NDSC);
                $pursecode = $product->purse->code->corporatewallet;
                $masterPurseDetails = $masterPurseModel->getPurseIdByPurseCode($pursecode);
                // Purse id 
                if($ratCustomerId > 0) { 
                    $purseDetails = $custPurseModel->getCustPurseDetails(array('boi_customer_id' => $ratCustomerId, 'purse_master_id' => $masterPurseDetails['id']));
                    $customerPurseId = (isset($purseDetails['id']) && $purseDetails['id'] > 0) ? $purseDetails['id'] : 0;
                }
                
                $amount = 0;
                if($loadStatus == STATUS_PENDING)
                {
                    $duplicate = $this->getDuplicateLoadRequest($dataArr['batch_name'], $cardholderId);
                    if($duplicate['num'] > 0)
                    {
                        $loadStatus = STATUS_FAILED;
                        $failedReason = 'Duplicate Load record basis Member ID /Card number in same file';
                        $dateFailed = new Zend_Db_Expr('NOW()');  
                        $dateLoad = new Zend_Db_Expr('NOW()');
                    }
                    elseif(strpos($dataArr['amount'],'.') !== FALSE)
                    {
                        $loadStatus = STATUS_FAILED;
                        $failedReason = 'Invalid Amount Value';
                        $dateFailed = new Zend_Db_Expr('NOW()');  
                        $dateLoad = new Zend_Db_Expr('NOW()');
                        $amount = $dataArr['amount'];
                    }
                    elseif(strpos($dataArr['amount'],' ') !== FALSE)
                    {
                        $loadStatus = STATUS_FAILED;
                        $failedReason = 'Invalid Amount Value';
                        $dateFailed = new Zend_Db_Expr('NOW()');  
                        $dateLoad = new Zend_Db_Expr('NOW()');
                        $amount = $dataArr['amount'];
                    }
                    elseif(strtolower($dataArr['wallet_code']) != strtolower($pursecode))
                    {
                        $loadStatus = STATUS_FAILED;
                        $failedReason = 'Wallet Code Validation failed';
                        $dateFailed = new Zend_Db_Expr('NOW()');  
                        $dateLoad = new Zend_Db_Expr('NOW()');
                    }
                    elseif($dataArr['card_type'] != strtolower(CORP_CARD_TYPE_NORMAL))
                    {
    //                    if($dataArr['card_type'] == strtolower(CORP_CARD_TYPE_CORPORATE))
    //                    {
                            $loadStatus = STATUS_FAILED;
                            $failedReason = 'Card type Corporate ID Validation failed';
                            $dateFailed = new Zend_Db_Expr('NOW()');  
                            $dateLoad = new Zend_Db_Expr('NOW()');
    //                    }
                    }
                    elseif($dataArr['mode'] == strtolower(TXN_MODE_DR) && strtoupper(substr($dataArr['batch_name'], 0, 3)) == 'BUP')
                    {
                        $loadStatus = STATUS_FAILED;
                        $failedReason = 'DR indicator for a BUP file';
                        $dateFailed = new Zend_Db_Expr('NOW()');  
                        $dateLoad = new Zend_Db_Expr('NOW()');
                    }
                }
                $this->_db->beginTransaction();
                if($amount != $dataArr['amount'])
                {
                    $amount = Util::convertToPaisa($dataArr['amount']);
                }
                $txnCode = $baseTxn->generateTxncode();
                $data = array(
                    'customer_master_id' => $customerMasterId,
                    'cardholder_id' => $cardholderId,
                    'customer_purse_id' => $customerPurseId,
                    'txn_type' => TXNTYPE_BOI_CORP_CORPORATE_LOAD,
                    'load_channel' => BY_OPS,
                    'purse_master_id' => $masterPurseDetails['id'],
                    'txn_identifier_type' => $dataArr['txn_identifier_type'],
                    'type' => $dataArr['type'],
                    'card_number' => $cardNumber,
                    'member_id' => $mediAssistId,
                    'amount' => $amount,
                    'amount_available' => 0,
                    'amount_used' => 0,
                    'amount_cutoff' => 0,
                    'currency' => $dataArr['currency'],
                    'narration' => $dataArr['narration'],
                    'wallet_code' => strtoupper($dataArr['wallet_code']),
                    'txn_no' => $dataArr['txn_no'],
                    'card_type' => $dataArr['card_type'],
                    'corporate_id' => $dataArr['corporate_id'],
                    'mode' => $dataArr['mode'],
                    'txn_code' => $txnCode,
                    'ip' => $dataArr['ip'],
                    'by_ops_id' => $dataArr['by_ops_id'],
                    'batch_name' => $dataArr['batch_name'],
                    'product_id' => $dataArr['product_id'],
                    'status' => $loadStatus,
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'failed_reason' => $failedReason,
                    'date_failed' => $dateFailed,
                    'date_load' => $dateLoad

                );
			
		if(($data['card_number'] != 0) &($data['card_number'] != '')){
		    $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
		    $data['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$data['card_number']."','".$encryptionKey."')");
		}
			
                $this->insert($data);
                $loadRequestId = $this->_db->lastInsertId(DbTable::TABLE_BOI_CORP_LOAD_REQUEST, 'id');
                $updateArr = array('upload_status' => STATUS_PASS);
                $this->_db->update(DbTable::TABLE_BOI_CORP_LOAD_REQUEST_BATCH, $updateArr, "id= $id");

                $this->_db->commit();
            }// END of foreach loop
            $notInid = implode(",", $idArr);
            $rejectedArr = array('upload_status' => STATUS_REJECTED);
            $this->_db->update(DbTable::TABLE_BOI_CORP_LOAD_REQUEST_BATCH, $rejectedArr, "id NOT IN ($notInid) AND batch_name = '$batchName'");
        } catch (Exception $e) {
            // If any of the queries failed and threw an exception,
            // we want to roll back the whole transaction, reversing
            // changes made in the transaction, even those that succeeded.
            // Thus all changes are committed together, or none are.
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_db->rollBack();
            //throw new Exception ("Transaction not completed due to system failure");
            throw new Exception($e->getMessage());
        }
        return TRUE;
    }
    
    /*
     * bulkAddCardload add card load request from batch table
     */

    public function bulkAddActCardload($idArr, $batchName) {
        if (empty($idArr)) {
            throw new Exception('Data missing for add cardholder');
        }
        $custModel = new Corp_Boi_Customers();
        $custPurseModel = new Corp_Boi_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $baseTxn = new BaseTxn();
        $user = Zend_Auth::getInstance()->getIdentity();
        $str = '';
        try {
            // Foreach selected id value
            foreach ($idArr as $id) {
                $select = $this->_db->select()
                        ->from(DbTable::TABLE_BOI_CORP_LOAD_REQUEST_BATCH)
                        ->where("id =?", $id);

                $dataArr = $this->_db->fetchRow($select);
                
                $product = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NDSC);
                $pursecode = $product->purse->code->corporatewallet;
                if($dataArr['wallet_code'] != $pursecode) {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = 'Invalid Wallet Code';
                    $dateFailed = new Zend_Db_Expr('NOW()');  
                    $dateLoad = new Zend_Db_Expr('NOW()');
                } 
                elseif($dataArr['member_id'] == 0) {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = 'Member Id is mandatory';
                    $dateFailed = new Zend_Db_Expr('NOW()');  
                    $dateLoad = new Zend_Db_Expr('NOW()');
                } else {
                    $searchArr = array(
                    'member_id' => $dataArr['member_id']);

                    $cardholderDetails = $custModel->getCardholderInfo($searchArr);
                    if(!isset($cardholderDetails->id) || $cardholderDetails->id == '')
                    {
                        $loadStatus = STATUS_FAILED;
                        $failedReason = 'Cardholder not found';
                        $dateFailed = new Zend_Db_Expr('NOW()');
                        $dateLoad = new Zend_Db_Expr('NOW()');   
                    } else {
                        if($cardholderDetails->status_bank != STATUS_APPROVED) {
                            $loadStatus = STATUS_FAILED;
                            $failedReason = 'Cardholder not approved by bank';
                            $dateFailed = new Zend_Db_Expr('NOW()');
                            $dateLoad = new Zend_Db_Expr('NOW()'); 
                        } elseif(!empty($cardholderDetails->card_number)) {
                            $loadStatus = STATUS_FAILED;
                            $failedReason = 'Account is mapped to a card';
                            $dateFailed = new Zend_Db_Expr('NOW()');
                            $dateLoad = new Zend_Db_Expr('NOW()'); 
                        } else {
                            $loadStatus = STATUS_PENDING;
                            $failedReason = '';
                            $dateFailed = '';   
                            $dateLoad = '';
                        }
                        
                    }
                    
                }
                
                $cardNumber = '';
                $mediAssistId = $searchArr['member_id'];
                $customerMasterId = ($cardholderDetails->customer_master_id != '') ? $cardholderDetails->customer_master_id : 0;
                $cardholderId = ($cardholderDetails->id != '') ? $cardholderDetails->id : 0;
                $ratCustomerId = ($cardholderDetails->boi_customer_id != '') ? $cardholderDetails->boi_customer_id : 0;
                $customerPurseId = 0;
                // Master Purse id
                $product = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NDSC);
                $pursecode = $product->purse->code->corporatewallet;
                $masterPurseDetails = $masterPurseModel->getPurseIdByPurseCode($pursecode);
                // Purse id 
                if($ratCustomerId > 0) { 
                    $purseDetails = $custPurseModel->getCustPurseDetails(array('boi_customer_id' => $ratCustomerId, 'purse_master_id' => $masterPurseDetails['id']));
                    $customerPurseId = (isset($purseDetails['id']) && $purseDetails['id'] > 0) ? $purseDetails['id'] : 0;
                }
                
                $amount = 0;
                if($loadStatus == STATUS_PENDING)
                {
                    $duplicate = $this->getDuplicateLoadRequest($dataArr['batch_name'], $cardholderId);
                    if($duplicate['num'] > 0)
                    {
                        $loadStatus = STATUS_FAILED;
                        $failedReason = 'Duplicate Load record basis Member ID /Card number in same file';
                        $dateFailed = new Zend_Db_Expr('NOW()');  
                        $dateLoad = new Zend_Db_Expr('NOW()');
                    }
                    elseif(strpos($dataArr['amount'],'.') !== FALSE)
                    {
                        $loadStatus = STATUS_FAILED;
                        $failedReason = 'Invalid Amount Value';
                        $dateFailed = new Zend_Db_Expr('NOW()');  
                        $dateLoad = new Zend_Db_Expr('NOW()');
                        $amount = $dataArr['amount'];
                    }
                    elseif(strpos($dataArr['amount'],' ') !== FALSE)
                    {
                        $loadStatus = STATUS_FAILED;
                        $failedReason = 'Invalid Amount Value';
                        $dateFailed = new Zend_Db_Expr('NOW()');  
                        $dateLoad = new Zend_Db_Expr('NOW()');
                        $amount = $dataArr['amount'];
                    }
                    elseif(strtolower($dataArr['wallet_code']) != strtolower($pursecode))
                    {
                        $loadStatus = STATUS_FAILED;
                        $failedReason = 'Wallet Code Validation failed';
                        $dateFailed = new Zend_Db_Expr('NOW()');  
                        $dateLoad = new Zend_Db_Expr('NOW()');
                    }
                    elseif($dataArr['card_type'] != strtolower(CORP_CARD_TYPE_NORMAL))
                    {
    //                    if($dataArr['card_type'] == strtolower(CORP_CARD_TYPE_CORPORATE))
    //                    {
                            $loadStatus = STATUS_FAILED;
                            $failedReason = 'Card type Corporate ID Validation failed';
                            $dateFailed = new Zend_Db_Expr('NOW()');  
                            $dateLoad = new Zend_Db_Expr('NOW()');
    //                    }
                    }
                    elseif($dataArr['mode'] == strtolower(TXN_MODE_DR) && strtoupper(substr($dataArr['batch_name'], 0, 3)) == 'BUP')
                    {
                        $loadStatus = STATUS_FAILED;
                        $failedReason = 'DR indicator for a BUP file';
                        $dateFailed = new Zend_Db_Expr('NOW()');  
                        $dateLoad = new Zend_Db_Expr('NOW()');
                    }
                }
                $this->_db->beginTransaction();
                if($amount != $dataArr['amount'])
                {
                    $amount = Util::convertToPaisa($dataArr['amount']);
                }
                $txnCode = $baseTxn->generateTxncode();
                $data = array(
                    'customer_master_id' => $customerMasterId,
                    'cardholder_id' => $cardholderId,
                    'customer_purse_id' => $customerPurseId,
                    'txn_type' => TXNTYPE_BOI_CORP_CORPORATE_LOAD,
                    'load_channel' => BY_OPS,
                    'purse_master_id' => $masterPurseDetails['id'],
                    'txn_identifier_type' => $dataArr['txn_identifier_type'],
                    'type' => $dataArr['type'],
                    'card_number' => $cardNumber,
                    'member_id' => $mediAssistId,
                    'amount' => $amount,
                    'amount_available' => 0,
                    'amount_used' => 0,
                    'amount_cutoff' => 0,
                    'currency' => $dataArr['currency'],
                    'narration' => $dataArr['narration'],
                    'wallet_code' => strtoupper($dataArr['wallet_code']),
                    'txn_no' => $dataArr['txn_no'],
                    'card_type' => $dataArr['card_type'],
                    'corporate_id' => $dataArr['corporate_id'],
                    'mode' => $dataArr['mode'],
                    'txn_code' => $txnCode,
                    'ip' => $dataArr['ip'],
                    'by_ops_id' => $dataArr['by_ops_id'],
                    'batch_name' => $dataArr['batch_name'],
                    'product_id' => $dataArr['product_id'],
                    'status' => $loadStatus,
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'failed_reason' => $failedReason,
                    'date_failed' => $dateFailed,
                    'date_load' => $dateLoad

                );
		
		if(($data['card_number'] != 0) &($data['card_number'] != '')){
		    $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
		    $data['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$data['card_number']."','".$encryptionKey."')");
		}
		
                $this->insert($data);
                $loadRequestId = $this->_db->lastInsertId(DbTable::TABLE_BOI_CORP_LOAD_REQUEST, 'id');
                $updateArr = array('upload_status' => STATUS_PASS);
                $this->_db->update(DbTable::TABLE_BOI_CORP_LOAD_REQUEST_BATCH, $updateArr, "id= $id");

                $this->_db->commit();
            }// END of foreach loop
            $notInid = implode(",", $idArr);
            $rejectedArr = array('upload_status' => STATUS_REJECTED);
            $this->_db->update(DbTable::TABLE_BOI_CORP_LOAD_REQUEST_BATCH, $rejectedArr, "id NOT IN ($notInid) AND batch_name = '$batchName'");
        } catch (Exception $e) {
            // If any of the queries failed and threw an exception,
            // we want to roll back the whole transaction, reversing
            // changes made in the transaction, even those that succeeded.
            // Thus all changes are committed together, or none are.
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_db->rollBack();
            //throw new Exception ("Transaction not completed due to system failure");
            throw new Exception($e->getMessage());
        }
        return TRUE;
    }

    public function generateRandom6DigitCode() {
        return rand(111111, 999999);
    }

    /*
     * doCorporateLoad will do corporate load on ECS - wallet hr
     */

    public function doCorporateLoad() {

        $loadRequests = $this->getLoadRequests(array('limit' => BOI_NSDC_CORPORATE_LOAD_LIMIT, 'status' => STATUS_PENDING, 'load_channel' => BY_OPS, 'type' => 'wlt'));
        $count = count($loadRequests);
        $retResp = array('loaded' => 0, 'not_loaded' => 0, 'exception' => array());
        if ($count > 0) {
            $ecsApi = new App_Socket_ECS_Corp_Transaction();
            $baseTxn = new BaseTxn();
            $m = new \App\Messaging\Corp\Boi\Operation();
            $cardholderModel = new Corp_Boi_Customers();
            $custPurseModel = new Corp_Boi_CustomerPurse();
            foreach ($loadRequests as $key => $val) {

                try {
                    $statusPending = $this->isRecordPending($val['id']);
                    if($statusPending) {
                        
                        $updateArrStatus = array(
                                    'status'=>STATUS_STARTED, 
                                    'date_load' => new Zend_Db_Expr('NOW()')
                                );
                        $this->updateLoadRequests($updateArrStatus, $val['id']);
                    
                    
                    $validator = array(
                        'load_request_id' =>  $val['id'],
                        'customer_master_id' =>  $val['customer_master_id'],
                        'purse_master_id' =>  $val['purse_master_id'],
                        'customer_purse_id' =>  $val['customer_purse_id'],
                        'amount' =>  $val['amount'],
                    );
                    $flgValidate = $baseTxn->chkAllowBoiCorporateCardLoad($validator);
                    if($flgValidate)
                    {
                        //$loadAmount = Util::getRs($val['amount']);
                        $cardLoadData = array(
                            'amount' => $val['amount'],
                            'crn' => $val['card_number'],
                            'agentId' => TXN_OPS_ID, // any data to be provided here, so default ops id
                            'transactionId' => $val['txn_code'],
                            'currencyCode' => CURRENCY_INR_CODE,
                            'countryCode' => COUNTRY_IN_CODE
                        );
                        $apiResp = $ecsApi->cardLoad($cardLoadData);
//                        $apiResp = TRUE;
                        //$balVal = $ecsApi->getElementResponse(self::DATA_ELEMENT_BALANCE);
                        $balVal = FALSE;
                        if($apiResp === TRUE){
                            
                            $updateArr = array(
                                    'txn_load_id' => $ecsApi->getISOTxnId(),
                                    'status'=>STATUS_LOADED, 
                                    'amount_available' => $val['amount'],
                                    'amount_used' => 0,
                                    'amount_cutoff' => 0,
                                    'date_load' => new Zend_Db_Expr('NOW()')
                                );
                            $this->updateLoadRequests($updateArr, $val['id']);
                            
                            $retResp['loaded'] = $retResp['loaded'] + 1;
                            
                            $baseTxnParams = array(
                              'txn_code' =>  $val['txn_code'], 
                              'customer_master_id' =>  $val['customer_master_id'], 
                              'product_id' =>  $val['product_id'], 
                              'purse_master_id' =>  $val['purse_master_id'], 
                              'customer_purse_id' =>  $val['customer_purse_id'], 
                              'amount' =>  $val['amount'], 
                            );
                            $baseTxn->successBoiCorporateCardLoad($baseTxnParams);
                            
                            $cardholder = $cardholderModel->findById($val['cardholder_id']);
                            //$custPurse = $custPurseModel->getCustBalance($cardholder['boi_customer_id']);
                                $userData = array('last_four' =>substr($cardholder['account_no'], -4),
                                    'product_name' => BOI_NSDC_PRODUCT_WALLET,
                                    'amount' => $val['amount'],
                                    'balance' => Util::numberFormat($balVal / 100), //$custPurse['sum'],
                                    'mobile' => $cardholder['mobile'],
                                );
                                $resp = $m->cardLoad($userData);
                            
                        }
                        else{
                            $failedReason = $ecsApi->getError();
                            $updateArr = array(
                                    'amount_available' => 0,
                                    'amount_used' => 0,
                                    'amount_cutoff' => 0,
                                    'status' => STATUS_FAILED, 
                                    'date_failed' => new Zend_Db_Expr('NOW()'),
                                    'failed_reason' => $failedReason,
                                    'date_load' => new Zend_Db_Expr('NOW()')
                                );
                            $this->updateLoadRequests($updateArr, $val['id']);
                            
                            $retResp['not_loaded'] = $retResp['not_loaded'] + 1;

                        }
                    }
                    } //Pending if condition
                } catch (App_Exception $e) {
                    $retResp['not_loaded'] = $retResp['not_loaded'] + 1;
                    $errorMsg = $e->getMessage();
                    $countException = count($retResp['exception']);
                    $retResp['exception'][$countException] = 'Exception of CRN ' . $val['card_number'] . ' with txn id ' . $val['txn_code'] . ' is ' . $errorMsg;
                    $updateArr = array(
                                    'amount_available' => 0,
                                    'amount_used' => 0,
                                    'amount_cutoff' => 0,
                                    'status' => STATUS_FAILED, 
                                    'date_failed' => new Zend_Db_Expr('NOW()'),
                                    'failed_reason' => $e->getMessage(),
                                    'date_load' => new Zend_Db_Expr('NOW()')
                                );
                    $this->updateLoadRequests($updateArr, $val['id']);
                } catch (Exception $e) {
                    $retResp['not_loaded'] = $retResp['not_loaded'] + 1;
                    $errorMsg = $e->getMessage();
                    $countException = count($retResp['exception']);
                    $retResp['exception'][$countException] = 'Exception of CRN ' . $val['card_number'] . ' with txn id ' . $val['txn_code'] . ' is ' . $errorMsg;
                    $updateArr = array(
                                    'amount_available' => 0,
                                    'amount_used' => 0,
                                    'amount_cutoff' => 0,
                                    'status' => STATUS_FAILED, 
                                    'date_failed' => new Zend_Db_Expr('NOW()'),
                                    'failed_reason' => $e->getMessage(),
                                    'date_load' => new Zend_Db_Expr('NOW()')
                                );
                    $this->updateLoadRequests($updateArr, $val['id']);
                }
            }
        }
        return $retResp;
    }

    

    /*
     * getLoadRequests will return the BOI NSDC load requests
     */

    public function getLoadRequests($param) {
        $status = isset($param['status']) ? $param['status'] : '';
        $type = isset($param['type']) ? $param['type'] : '';
        $loadChannel = isset($param['load_channel']) ? $param['load_channel'] : '';
        $batchName = isset($param['batch_name']) ? $param['batch_name'] : '';
        $purseMasterId = isset($param['purse_master_id']) ? $param['purse_master_id'] : '';
        $limit = isset($param['limit']) ? $param['limit'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $product = isset($param['product_id']) && $param['product_id'] > 0? $param['product_id'] : '';
        $chkAvailable = (isset($param['chk_amount_available']) && $param['chk_amount_available'] != '') ? $param['chk_amount_available'] : '';
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`l`.`card_number`,'".$decryptionKey."') as card_number");
        $select = $this->select()
          ->setIntegrityCheck(false)
          ->from($this->_name ." as l",array('id', 'product_id', 'type', 'customer_master_id', 'cardholder_id', 'purse_master_id', 'customer_purse_id', 'txn_type', 'load_channel', 'txn_identifier_type', $card_number, 'member_id', 'amount', 'amount_available', 'amount_used', 'amount_cutoff', 'currency', 'narration', 'wallet_code', 'txn_no', 'card_type', 'corporate_id', 'mode', 'txn_code', 'by_agent_id', 'by_ops_id', 'ip', 'batch_name', 'date_created', 'date_load', 'date_failed', 'date_cutoff', 'txn_load_id', 'failed_reason', 'date_updated', 'status'));
        $select->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "l.product_id  = p.id",array('p.name as product_name'));
         if ($product != '') {
            $select->where('l.product_id = ?', $product);
        }
        if ($status != '') {
            $select->where('l.status = ?', $status);
        }
        if ($type != '') {
            $select->where('l.type = ?', $type);
        }
        if ($loadChannel != '') {
            $select->where('l.load_channel = ?', $loadChannel);
        }
        if ($batchName != '') {
            $select->where('l.batch_name = ?', $batchName);
        }
        if ($purseMasterId != '') {
            $select->where('l.purse_master_id = ?', $purseMasterId);
        }
         if ($from != '' && $to != ''){
            $select->where("l.date_created >= '" . $from . "'");
            $select->where("l.date_created <= '" . $to . "'");
        }
         if ($chkAvailable == GREATER){
            $select->where("l.amount_available > 0");
        }
        if ($limit != '') {
            $select->limit($limit);
        }
              
        return $this->fetchAll($select);
    }

    public function getDuplicateLoadRequest($batchName, $cardholderId) {
        $select = $this->select()
            ->from(DbTable::TABLE_BOI_CORP_LOAD_REQUEST, array("count(*) as num"))
            ->where('batch_name = ?', $batchName)
            ->where('cardholder_id = ?', $cardholderId)
            ->where('status = ?', STATUS_PENDING);
        
        return $this->fetchRow($select);
    }
    
     public function exportLoadRequests($params){
         $data = $this->getLoadRequests($params);
         $data = $data->toArray();
         $retData = array();
        
        if(!empty($data))
        {
                     
            foreach($data as $key=>$data){
                    //$retData[$key]['product_name']  = $data['product_name'];
                    $retData[$key]['txn_identifier_type']  = strtoupper($data['txn_identifier_type']);
                    $retData[$key]['card_number']          = Util::maskCard($data['card_number']);
                    $retData[$key]['member_id']    = $data['member_id'];
                    $retData[$key]['amount']      = $data['amount']; 
                    $retData[$key]['currency']        = $data['currency'];
                    $retData[$key]['narration'] = $data['narration'];
                    $retData[$key]['wallet_code'] = $data['wallet_code'];
                    $retData[$key]['txn_no'] = $data['txn_no'];
                    $retData[$key]['card_type']      = strtoupper($data['card_type']); 
                    $retData[$key]['corporate_id']      = $data['corporate_id']; 
                    $retData[$key]['mode']      = strtoupper($data['mode']); 
                    $retData[$key]['txn_code']      = $data['txn_code']; 
                    $retData[$key]['failed_reason']      = $data['failed_reason']; 
                    $retData[$key]['status']      = $data['status']; 
                   
          }
        }
        
        return $retData;
         
     }
    /*
     * Stats Daily - loads
     */

    public function getStatsDaily($customerPurseId, $curDate, $statusStr) {
        $select = $this->select()
                ->from(DbTable::TABLE_BOI_CORP_LOAD_REQUEST, array('count(*) as count', 'sum(amount) as total'))
                ->where('customer_purse_id=?', $customerPurseId);
        if ($statusStr != '') {
            $select->where("status IN ($statusStr)");
        }
        $select->where("DATE(date_created) = '" . $curDate . "'")
                ->group("customer_purse_id");
//        echo "<br/>".$select->__toString();
        $row = $this->fetchRow($select);
        return $row;
    }

    /*
     * Stats Duration - loads
     */

    public function getStatsDuration($customerPurseId, $startDate, $endDate, $statusStr) {
        $select = $this->select()
                ->from(DbTable::TABLE_BOI_CORP_LOAD_REQUEST, array('count(*) as count', 'sum(amount) as total'))
                ->where('customer_purse_id=?', $customerPurseId);
        if ($statusStr != '') {
            $select->where("status IN ($statusStr)");
        }
        $select->where("DATE(date_created) BETWEEN '" . $startDate . "' AND '" . $endDate . "'")
                ->group("customer_purse_id");
//        echo "<br/>".$select->__toString();
        $row = $this->fetchRow($select);
        return $row;
    }
    
    public function getBatchName($masterPurseId){
        $select = $this->select()
                ->from(DbTable::TABLE_BOI_CORP_LOAD_REQUEST, array('batch_name'))
                ->distinct(TRUE)
                ->where('purse_master_id=?', $masterPurseId);
        $row = $this->fetchAll($select);
        return $row;
    }
    
    public function checkFilename($fileName) {
        $select = $this->select();
        $select->from(DbTable::TABLE_BOI_CORP_LOAD_REQUEST, array('id'));
        $select->where("batch_name =?", $fileName);
        $rs = $this->fetchRow($select);
        if (empty($rs)) {
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function isRecordPending($loadReqId) {
        $select = $this->select();
        $select->from(DbTable::TABLE_BOI_CORP_LOAD_REQUEST, array('id'));
        $select->where("status =?", STATUS_PENDING);
        $select->where("id =?", $loadReqId);
        $rs = $this->fetchRow($select);
        if (empty($rs)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function updateLoadRequests($data, $id){
        if(!empty($data) && $id>0){
	    if(!empty($data['card_number'])){
		$encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
		$data['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$data['card_number']."','".$encryptionKey."')");
	    }
           $this->update($data, 'id="'.$id.'"');
           return true;
        }
        return false;
    }
    
    public function updateLoadBatch($data, $batch){
        if(!empty($data) && $batch != ''){
	    if(!empty($data['card_number'])){
		$encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
		$data['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$data['card_number']."','".$encryptionKey."')");
	    }
	    $this->_db->update(DbTable::TABLE_BOI_CORP_LOAD_REQUEST_BATCH, $data, 'batch_name="'.$batch.'"');
	    return true;
        }
        return false;
    }
    
      /*
     * cutoffValidation will revert back the credited claimed amount from cardholder acnt, if amount not used within time duration 
     */
    public function cutoffValidation(){
        
        $custLoadReq = $this->getLoadRequests(array('status'=>STATUS_LOADED, 'chk_amount_available' => GREATER));
        $custLoadReq = $custLoadReq->toArray();
        $count = count($custLoadReq);
        $retResp = array('cutoff'=> 0, 'not_cutoff'=> 0, 'exception'=>array());
        $masterPurseModel = new MasterPurse();
        $m = new \App\Messaging\Corp\Boi\Operation();
        $cardholderModel = new Corp_Boi_Customers();
        if($count>0){            
           $ecsSocket = new App_Socket_ECS_Transaction();
           $objBaseTxn = new BaseTxn();
           
                foreach($custLoadReq as $key=>$val){
                    $pursedetail = $masterPurseModel->findById($val['purse_master_id']);
                    // check for time duration 
                    $dateLoad = $val['date_load'];
                    $amtAvailable = $val['amount_available'];
                    
                if($pursedetail->load_validity_day > 0 || $pursedetail->load_validity_hr > 0  || $pursedetail->load_validity_min > 0) {
                    $validityParams = array(
                        'load_validity_day' => $pursedetail->load_validity_day,
                        'load_validity_hr' => $pursedetail->load_validity_hr,
                        'load_validity_min' => $pursedetail->load_validity_min,
                    );
                    $cuDate = date('Y-m-d H:i:s');
                    $cutOffValidity = Util::cutoffValidity($validityParams);
                    $timeDifference = Util::dateDiff($dateLoad, $cuDate); // difference in secs
                   
                    if($cutOffValidity < $timeDifference) {

                      try{
                          if($val['type'] == 'act') {
                                $product = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NSDC);
                                $purseHr = $product->purse->code->corporatewallet;
                                
                                $paramsBaseTxn = array(
                                                        'customer_master_id' => $val['customer_master_id'],
                                                        'product_id' => $val['product_id'],
                                                        'purse_master_id' => $val['purse_master_id'],
                                                        'customer_purse_id' => $val['customer_purse_id'],
                                                        'txn_code' => $val['txn_code'],
                                                        'amount' => $amtAvailable,
                                                        'agent_id' => 0,
                                                        'txn_type' => TXNTYPE_REVERSAL_BOI_CORP_CORPORATE_LOAD,
                                                      );
                                
                                $respBaseTxn = $objBaseTxn->cutoffBoiCardLoad($paramsBaseTxn);
                                
                                if($respBaseTxn){
                                    $retResp['cutoff'] = $retResp['cutoff'] + 1;
                                    $updLoadArr = array('amount_available' => 0,
                                                'amount_cutoff' => $amtAvailable,
                                                'date_cutoff' => new Zend_Db_Expr('NOW()'));
                                    $updLoadReq = $this->updateLoadRequests($updLoadArr, $val['id']);
                                    
                                    $detailArr = array(
                                        'product_id' => $val['product_id'],
                                        'load_request_id' => $val['id'],
                                        'txn_processing_id' => 0,
                                        'amount' => $amtAvailable,
                                        'txn_code' => $val['txn_code'],
                                        'txn_type' => TXNTYPE_REVERSAL_BOI_CORP_CORPORATE_LOAD,
                                    );
                                    $this->insertLoadDetail($detailArr);
                                
                                    // sms addddd
                                $cardholder = $cardholderModel->findById($val['cardholder_id']);
                                $userData = array(
                                    'last_four' =>substr($cardholder['account_no'], -4),
                                    'product_name' => BOI_NSDC_PRODUCT_WALLET,
                                    'amount' => $amtAvailable,
                                    'mobile' => $cardholder['mobile'],
                                );
                                $resp = $m->autoDebit($userData);    
                                    
                                    
                                }
                            
                           if(!$respBaseTxn){
                               $retResp['not_cutoff'] = $retResp['not_cutoff'] + 1;
                            }
                          }
                          else {
                          $apiResp = $ecsSocket->cuttOffReversal(array('crn' => $val['card_number'],
                                                                'amount' => $amtAvailable,
                                                                'txn_load_id' => $val['txn_load_id']));
//                               $apiResp = TRUE;
                               if($apiResp === TRUE){
                                
                                $product = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NSDC);
                                $purseHr = $product->purse->code->corporatewallet;
                                
                                $cardholder = $cardholderModel->findById($val['cardholder_id']);
                                $userData = array(
                                    'last_four' =>substr($cardholder['account_no'], -4),
                                    'product_name' => BOI_NSDC_PRODUCT_WALLET,
                                    'amount' => $amtAvailable,
                                    'mobile' => $cardholder['mobile'],
                                );
                                $resp = $m->autoDebit($userData);
                                
                                
                                $paramsBaseTxn = array(
                                                        'customer_master_id' => $val['customer_master_id'],
                                                        'product_id' => $val['product_id'],
                                                        'purse_master_id' => $val['purse_master_id'],
                                                        'customer_purse_id' => $val['customer_purse_id'],
                                                        'txn_code' => $val['txn_code'],
                                                        'amount' => $amtAvailable,
                                                        'agent_id' => 0,
                                                        'txn_type' => TXNTYPE_REVERSAL_BOI_CORP_CORPORATE_LOAD,
                                                      );
                                
                                $respBaseTxn = $objBaseTxn->cutoffBoiCardLoad($paramsBaseTxn);
                                
                                if($respBaseTxn){
                                    $retResp['cutoff'] = $retResp['cutoff'] + 1;
                                    $updLoadArr = array('amount_available' => 0,
                                                'amount_cutoff' => $amtAvailable,
                                                'date_cutoff' => new Zend_Db_Expr('NOW()'));
                                    $updLoadReq = $this->updateLoadRequests($updLoadArr, $val['id']);
                                    
                                    $detailArr = array(
                                        'product_id' => $val['product_id'],
                                        'load_request_id' => $val['id'],
                                        'txn_processing_id' => 0,
                                        'amount' => $amtAvailable,
                                        'txn_code' => $val['txn_code'],
                                        'txn_type' => TXNTYPE_REVERSAL_BOI_CORP_CORPORATE_LOAD,
                                    );
                                    $this->insertLoadDetail($detailArr);
                                }
                            }
                            
                           if(!$apiResp || !$respBaseTxn){
                               $retResp['not_cutoff'] = $retResp['not_cutoff'] + 1;
                            }
                      }

                        } catch(App_Exception $e){
                                      $retResp['not_cutoff'] = $retResp['not_cutoff'] + 1;
                                      $errorMsg = $e->getMessage();
                                      $countException = count($retResp['exception']);
                                      $retResp['exception'][$countException] = 'Exception of cardholder id '.$val['cardholder_id'].' with txn id '.$val['txn_code'].' is '. $errorMsg;
                        } catch(Exception $e){
                                      $retResp['not_cutoff'] = $retResp['not_cutoff'] + 1;
                                      $errorMsg = $e->getMessage();
                                      $countException = count($retResp['exception']);
                                      $retResp['exception'][$countException] = 'Exception of cardholder id '.$val['cardholder_id'].' with txn id '.$val['txn_code'].' is '. $errorMsg;
                       }
                     }
                }
             }
           
        }
        
        return $retResp;
    }
    
   
     public function getWalletTxn($param) 
    {
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $productId = isset($param['product_id']) ? $param['product_id'] : '';
        $bankUnicode = isset($param['bank_unicode']) ? $param['bank_unicode'] : '';
        $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");
        $STATUS = Zend_Registry::get("STATUS");
        $arrReport = array();
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`load`.`card_number`,'".$decryptionKey."') as card_number");
        $select = $this->select();
        $select->from(DbTable::TABLE_BOI_CORP_LOAD_REQUEST . " as load", array('date_load', $card_number, 'member_id', 'txn_type', 'status', 'amount', 'amount_cutoff', 'txn_no', 'txn_code', 'failed_reason', 'load_channel', 'narration'));
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_BOI_CORP_CARDHOLDER . " as holder", "load.cardholder_id = holder.id",array('card_pack_id'));
        $select->join(DbTable::TABLE_PRODUCTS . " as product", "load.product_id = product.id",array('name as product_name'));
        $select->join(DbTable::TABLE_BANK . " as bank", "product.bank_id = bank.id",array('name as bank_name'));
        $select->joinLeft(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id = load.purse_master_id ",array('pm.code as wlt_code'));
        if ($productId >0) {
            $select->where("load.product_id = '" . $productId . "'");
        }
        if ($bankUnicode >0) {
            $select->where("bank.unicode = '".$bankUnicode."'");
        }
        if ($from != '' && $to != ''){
            $select->where("load.date_load >=  '" . $from . "'");
            $select->where("load.date_load <= '" . $to . "'");
            $select->where("load.status IN  ('" . STATUS_LOADED . "', '".STATUS_FAILED."', '".STATUS_CUTOFF."')");
        }
        $select->order("load.date_load");
        $rsLoad = $this->_db->fetchAll($select);
        $cntLoad = count($rsLoad);
        for($i = 0; $i < $cntLoad; $i++)
        {
            $arrReport[$i]['txn_date'] = $rsLoad[$i]['date_load'];
            $arrReport[$i]['product_name'] = $rsLoad[$i]['product_name'];
            $arrReport[$i]['bank_name'] = $rsLoad[$i]['bank_name'];
            $arrReport[$i]['card_number'] = Util::maskCard($rsLoad[$i]['card_number'], 4);
            $arrReport[$i]['card_pack_id'] = $rsLoad[$i]['card_pack_id'];
            $arrReport[$i]['member_id'] = $rsLoad[$i]['member_id'];
            $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[$rsLoad[$i]['txn_type']];
            $arrReport[$i]['status'] = $STATUS[$rsLoad[$i]['status']];
            $arrReport[$i]['wlt_code'] = $rsLoad[$i]['wlt_code'];
            $arrReport[$i]['mode'] = '-';
            $arrReport[$i]['wallet_hr_dr'] = ($rsLoad[$i]['txn_type'] == TXNTYPE_BOI_CORP_CORPORATE_LOAD) ? $rsLoad[$i]['amount_cutoff'] : 0;
            $arrReport[$i]['wallet_hr_cr'] = ($rsLoad[$i]['txn_type'] == TXNTYPE_BOI_CORP_CORPORATE_LOAD) ? $rsLoad[$i]['amount'] : 0;
//            $arrReport[$i]['wallet_ins_dr'] = ($rsLoad[$i]['status'] == STATUS_CUTOFF && $rsLoad[$i]['txn_type'] == TXNTYPE_RAT_CORP_MEDIASSIST_LOAD) ? $rsLoad[$i]['amount'] : 0;
//            $arrReport[$i]['wallet_ins_cr'] = ($rsLoad[$i]['txn_type'] == TXNTYPE_RAT_CORP_MEDIASSIST_LOAD) ? $rsLoad[$i]['amount'] : 0;
            $arrReport[$i]['txn_no'] = $rsLoad[$i]['txn_no'];
            $arrReport[$i]['txn_code'] = $rsLoad[$i]['txn_code'];
            $arrReport[$i]['failed_reason'] = ($rsLoad[$i]['status'] == STATUS_FAILED) ? $rsLoad[$i]['failed_reason'] : '-';
            $arrReport[$i]['mcc_code'] = '-';
            $arrReport[$i]['tid'] = '-';
            $arrReport[$i]['mid'] = '-';
            $arrReport[$i]['channel'] = strtoupper($rsLoad[$i]['load_channel']);
            $arrReport[$i]['rev_indicator'] = '-';            
            $arrReport[$i]['narration'] = $rsLoad[$i]['narration'];            
        }
	$card_numAuth = new Zend_Db_Expr("AES_DECRYPT(`auth`.`card_number`,'".$decryptionKey."') as card_number");
        $select = $this->select();
        $select->from(DbTable::TABLE_CARD_AUTH_REQUEST . " as auth", array('date_created', $card_numAuth, 'txn_type', 'status', 'amount_txn', 'txn_no', 'txn_code', 'failed_reason', 'mcc_code', 'tid', 'mid', 'narration', 'rev_indicator'));
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_BOI_CORP_CARDHOLDER . " as holder", "auth.cardholder_id = holder.id",array('card_pack_id', 'nsdc_enrollment_no'));
        $select->join(DbTable::TABLE_PRODUCTS . " as product", "auth.product_id = product.id",array('ecs_product_code as product_code', 'name as product_name', 'bank_id'));
        $select->join(DbTable::TABLE_BANK . " as bank", "product.bank_id = bank.id",array('name as bank_name'));
        $select->joinLeft(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.product_id = product.id ",array('pm.code as wlt_code'));
        if ($productId > 0) {
            $select->where("auth.product_id = '" . $productId . "'");
        }
        if ($bankUnicode >0) {
            $select->where("bank.unicode = '".$bankUnicode."'");
        }
        if ($from != '' && $to != ''){
            $select->where("auth.date_created >=  '" . $from . "'");
            $select->where("auth.date_created <= '" . $to . "'");
            $select->where("auth.status IN  ('" . STATUS_COMPLETED . "', '".STATUS_FAILED."', '".STATUS_REVERSED."')");
        }
        $select->order("auth.date_created");
        
        $rsAuth = $this->_db->fetchAll($select);
        $cntAuth = count($rsAuth);
        $j = $i;
        for($i = 0; $i < $cntAuth; $i++)
        {
            $arrReport[$j]['txn_date'] = $rsAuth[$i]['date_created'];
            $arrReport[$j]['product_name'] = $rsAuth[$i]['product_name'];
            $arrReport[$j]['bank_name'] = $rsAuth[$i]['bank_name'];
            $arrReport[$j]['card_number'] = Util::maskCard($rsAuth[$i]['card_number'], 4);
            $arrReport[$j]['card_pack_id'] = $rsAuth[$i]['card_pack_id'];
            $arrReport[$j]['member_id'] = $rsAuth[$i]['nsdc_enrollment_no'];
            $arrReport[$j]['txn_type'] = $TXN_TYPE_LABELS[$rsAuth[$i]['txn_type']];
            $arrReport[$j]['status'] = $STATUS[$rsAuth[$i]['status']];
            $arrReport[$j]['wlt_code'] = $rsAuth[$i]['wlt_code'];
            $arrReport[$j]['mode'] = '-';
            $arrReport[$j]['wallet_hr_dr'] = ( strtolower($rsAuth[$i]['rev_indicator']) == 'n') ? $rsAuth[$i]['amount_txn']: 0;
            $arrReport[$j]['wallet_hr_cr'] = ( strtolower($rsAuth[$i]['rev_indicator']) == 'y') ? $rsAuth[$i]['amount_txn']: 0;
//            $arrReport[$j]['wallet_ins_dr'] = ($rsAuth[$i]['purse_master_id'] == $purseDetailsIns['id'] && strtolower($rsAuth[$i]['rev_indicator']) == 'n') ? $rsAuth[$i]['amount_txn']: 0;
//            $arrReport[$j]['wallet_ins_cr'] = ($rsAuth[$i]['purse_master_id'] == $purseDetailsIns['id'] && strtolower($rsAuth[$i]['rev_indicator']) == 'y') ? $rsAuth[$i]['amount_txn']: 0;
            $arrReport[$j]['txn_no'] = $rsAuth[$i]['txn_no'];
            $arrReport[$j]['txn_code'] = $rsAuth[$i]['txn_code'];
            $arrReport[$j]['failed_reason'] = ($rsAuth[$i]['status'] == STATUS_FAILED) ? $rsAuth[$i]['failed_reason'] : '-';
            $arrReport[$j]['mcc_code'] = $rsAuth[$i]['mcc_code'];
            $arrReport[$j]['tid'] = $rsAuth[$i]['tid'];
            $arrReport[$j]['mid'] = $rsAuth[$i]['mid'];
            $arrReport[$j]['channel'] = '-';
            $arrReport[$j]['rev_indicator'] = (strtolower($rsAuth[$i]['rev_indicator']) == 'y') ? ucfirst(FLAG_YES) : ucfirst(FLAG_NO);            
            $arrReport[$j]['narration'] = $rsAuth[$i]['narration'];            
            $j++;
        }
        
        return $arrReport;
    }
    
    
    public function validateCuttoffFile(array $param) {
        $sql = $this->_db->select()
                ->from(DbTable::TABLE_FILES)
                ->where("label=?",BOI_TTUM_FILE)
                ->where("status=?",STATUS_ACTIVE)
                ->where("(date_start between '".$param['start_date']."' AND '".$param['end_date']."') OR (date_end between '".$param['start_date']."' AND '".$param['end_date']."')");
        return $this->_db->fetchAll($sql);
    }
    
    
    public function generateCuttoffFile(array $param) {
        if (!isset($param['start_date']) || !isset($param['end_date'])) {
            throw new App_Exception('Invalid Start or End Date');
            return;
        }
        $rs = $this->validateCuttoffFile($param);
        if (!empty($rs)) {
            throw new App_Exception('Record Already exists');
            return;
        }
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`lr`.`card_number`,'".$decryptionKey."') as card_number");
        
        $start_date = $param['start_date'] . ' 00:00:00';
        $end_date = $param['end_date'] . ' 23:59:59';
        $select = $this->_db->select()
                ->from(DbTable::TABLE_BOI_CORP_LOAD_REQUEST . ' as lr', array('id', 'product_id', 'type', 'customer_master_id', 'cardholder_id', 'purse_master_id', 'customer_purse_id', 'txn_type', 'load_channel', 'txn_identifier_type', $card_number, 'member_id', 'amount_cutoff as amount', 'currency', 'narration', 'wallet_code', 'txn_no', 'card_type', 'corporate_id', 'mode', 'txn_code', 'by_agent_id', 'by_ops_id', 'ip', 'batch_name', 'date_created', 'date_load', 'date_failed', 'date_cutoff', 'txn_load_id', 'status'))
                ->join(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as ch', 'lr.cardholder_id=ch.id', array('by_agent_id as agnt','aadhaar_no',"concat(first_name,' ' ,last_name) as cust_name"))
                ->where('lr.date_cutoff >= ?', $start_date)
                ->where('lr.date_cutoff <= ?', $end_date)
                ->where('lr.status = ?', STATUS_LOADED)
                ->where('lr.amount_cutoff > 0')
                ->order('lr.date_cutoff');
        //echo $select;
        $rs = $this->_db->fetchAll($select);
        $rs = Util::toArray($rs);
        if (empty($rs)) {
            throw new App_Exception('No Record Found');
            return;
        }
        // For 1000 records per file
        $countRecs = count($rs);
        $numrecordsPerFile  = 1000;
        $addOnOffset = ceil($countRecs/$numrecordsPerFile);
        $offset = 0;

        for($i = 0; $i <= $addOnOffset;$i++)
        {

        $arrayChunk = array_slice($rs, $offset, $numrecordsPerFile);
        $offset += $numrecordsPerFile;
        if(!empty($arrayChunk)){
            
        $this->generateTTUMFile($arrayChunk, $param);
        }
        }
        
        return TRUE;
    }

    private function getDistributorAccountDetails($param) {
        $agentModel = new AgentUser();
        $agents = new Agents();
        if (isset($param['by_agent_id']) && $param['by_agent_id'] > 0) {
            $distributorInfo = $agentModel->getParentInfo($param['by_agent_id']);
            $disInfo = $agents->findById($distributorInfo['id']);
            return $disInfo;
        } else if (isset($param['agnt']) && $param['agnt'] > 0) {
            $distributorInfo = $agentModel->getParentInfo($param['agnt']);
            $disInfo = $agents->findById($distributorInfo['id']);
            return $disInfo;
        } else {
            throw new App_Exception('Agent not found');
        }
    }

    public function generateTTUMFile(array $data, array $param) {
        $objR = New ObjectRelations();
        $seprator = '';
        $ext = 'txt';
        if (!empty($data)) {
            foreach ($data as $value) {
                $disInfo = $this->getDistributorAccountDetails($value);
                if (empty($disInfo['bank_account_number'])) {
                    //throw new App_Exception('Distributor Bank Account not found');
                }
//ENTRY First      
                $aadhaar_no = (isset($value['aadhaar_no']) && $value['aadhaar_no'] != '')? $value['aadhaar_no']." ":'';
                $name = Util::removeSpecialChars($value['cust_name'], true);
                $cust_narration = substr($aadhaar_no.$name, 0, self::NARRATION_LENGTH); 
                $cust_narration = ' '.$cust_narration;
                 
                $paramArr = array(
                    'account_no' => self::CARD_POOL_AC, //$value['boi_account_number'],
                    'currency_code' => CURRENCY_INR_CODE,
                    'service_outlet' => '',
                    'trans_type' => 'D',
                    'amount' => $acHolderArr['amount'] + $value['amount'],//Util::formatString($value['amount'], 'int', '14', ' '),
                    'particulars' => self::ACCOUNT_NARRATION_CAPTION,
//                    'particulars' => '',
                    'report_code' => '',
                    'ref_num' => $value['ref_num'],
                );
                $acHolderArr = $paramArr;
                //$acHolderArr = $this->generateCuttoffBatchArray($paramArr);
//ENTRY THIRD                              
//                $amountIncomeAc = ($value['amount'] / '100') * '1.5';
                $amountIncomeAc = ($value['amount'] / 100) * 1.5;
                $amountIncomeAc = round($amountIncomeAc,2);
                $amountIncomeAc = sprintf("%01.2f", $amountIncomeAc);                                
                $paramArr['account_no'] = self::INCOME_ACCOUNT_WITH_BOI;
                $paramArr['amount'] =  $acIncomeArr['amount'] + $amountIncomeAc;//Util::formatString($amountIncomeAc, 'int', '14', ' ');
                $paramArr['trans_type'] = 'C';
                $paramArr['particulars'] = self::ACCOUNT_NARRATION_CAPTION;
                $acIncomeArr = $paramArr;
                //$acIncomeArr = $this->generateCuttoffBatchArray($paramArr);
//ENTRY FOURTH                              
                $amountServiceTax = ($amountIncomeAc / 100) * self::SERVICE_TAX;
                //$amountServiceTax = round($amountServiceTax,2);
                $amountServiceTax = sprintf("%01.2f", $amountServiceTax);                
                $paramArr['account_no'] = self::SERVICE_TAX_AC;
                $paramArr['amount'] = $serTaxArr['amount'] + $amountServiceTax;//Util::formatString($amountServiceTax, 'int', '14', '0');
                $paramArr['trans_type'] = 'C';
                $paramArr['particulars'] = self::ACCOUNT_NARRATION_CAPTION;                
                $serTaxArr = $paramArr;
                //$serTaxArr = $this->generateCuttoffBatchArray($paramArr);

                
//ENTRY SECOND
//              $amountDis = ($value['amount'] / '100') * '98.5';
                $amountDis = $value['amount'] - ($amountIncomeAc + $amountServiceTax); //($value['amount'] / 100) * 98.5;
                //$amountDis = round($amountDis,2);
                $amountDis = sprintf("%01.2f", $amountDis);
                $paramArr['account_no'] = $disInfo['bank_account_number'];
                $paramArr['amount'] = $amountDis;//Util::formatString($amountDis, 'int', '14', ' ');
                $paramArr['trans_type'] = 'C';
                $paramArr['particulars'] = $cust_narration;                
                $acDisArr = $this->generateCuttoffBatchArray($paramArr);

                
                //$batchMainArr[] = $batchArr['action_code'] . '|' . $batchArr['card_number'] . '|' . $batchArr['client_code'] . '|' . $batchArr['institution_code'] . '|' . $batchArr['branch_code'] . '|' . $batchArr['vip_flag'] . '|' . $batchArr['owner_code'] . '|' . $batchArr['staff_id'] . '|' . $batchArr['basic_card'] . '|' . $batchArr['basic_card_number'] . '|' . $batchArr['title'] . '|' . $batchArr['family_name'] . '|' . $batchArr['first_name'] . '|' . $batchArr['middle_name'] . '|' . $batchArr['middle_name2'] . '|' . $batchArr['embossed_name'] . '|' . $batchArr['encoded_name'] . '|' . $batchArr['emboss_line3'] . '|' . $batchArr['marital_status'] . '|' . $batchArr['gender'] . '|' . $batchArr['legal_id'] . '|' . $batchArr['nationality_code'] . '|' . $batchArr['no_of_children'] . '|' . $batchArr['credit_limit'] . '|' . $batchArr['issuers_client'] . '|' . $batchArr['lodging_period'] . '|' . $batchArr['residence_status'] . '|' . $batchArr['net_yearly_income'] . '|' . $batchArr['no_of_dependents'] . '|' . $batchArr['birth_date'] . '|' . $batchArr['birth_city'] . '|' . $batchArr['birth_country'] . '|' . $batchArr['address1'] . '|' . $batchArr['address2'] . '|' . $batchArr['address3'] . '|' . $batchArr['zip_code'] . '|' . $batchArr['phone_no_1'] . '|' . $batchArr['phone_no_2'] . '|' . $batchArr['mobile_phone'] . '|' . $batchArr['email_id'] . '|' . $batchArr['mailing_address1'] . '|' . $batchArr['mailing_address2'] . '|' . $batchArr['mailing_address3'] . '|' . $batchArr['mailing_zip_code'] . '|' . $batchArr['phone_home'] . '|' . $batchArr['phone_alternate'] . '|' . $batchArr['phone_mobile'] . '|' . $batchArr['employment_status'] . '|' . $batchArr['employer'] . '|' . $batchArr['empl_address1'] . '|' . $batchArr['empl_address2'] . '|' . $batchArr['empl_address3'] . '|' . $batchArr['empl_zip_code'] . '|' . $batchArr['office_phone1'] . '|' . $batchArr['office_phone2'] . '|' . $batchArr['office_mobile'] . '|' . $batchArr['preferred_mailing_address'] . '|' . $batchArr['contract_start_date'] . '|' . $batchArr['opening_date'] . '|' . $batchArr['start_val_date'] . '|' . $batchArr['expiry_date'] . '|' . $batchArr['product_code'] . '|' . $batchArr['promo_code'] . '|' . $batchArr['tariff_code'] . '|' . $batchArr['cash_fees_code'] . '|' . $batchArr['primay_card_transaction_set'] . '|' . $batchArr['secondary_card_transaction_set'] . '|' . $batchArr['statement_group_id'] . '|' . $batchArr['account1'] . '|' . $batchArr['account1_currency'] . '|' . $batchArr['account1_type'] . '|' . $batchArr['limit_cash_dom'] . '|' . $batchArr['limit_purch_dom'] . '|' . $batchArr['limit_te_dom'] . '|' . $batchArr['reserved'] . '|' . $batchArr['limit_cash_int'] . '|' . $batchArr['limit_purch_int'] . '|' . $batchArr['limit_te_int'] . '|' . $batchArr['reserved'] . '|' . $batchArr['autho_limit_dom'] . '|' . $batchArr['autho_limit_int'] . '|' . $batchArr['reserved'] . '|' . $batchArr['activity_code'] . '|' . $batchArr['socio_prof_code'] . '|' . $batchArr['status_code'] . '|' . $batchArr['delivery_mode'] . '|' . $batchArr['delivery_flag'] . '|' . $batchArr['delivery_date'] . '|' . $batchArr['bank/_dsa_ref'] . '|' . $batchArr['photo_indicator'] . '|' . $batchArr['picture_code'] . '|' . $batchArr['language_ind'] . '|' . $batchArr['maiden_name'] . '|' . $batchArr['renewal_option'] . '|' . $batchArr['preference'] . '|' . $batchArr['sale_date'] . '|' . $batchArr['registration_flag'] . '|' . $batchArr['user_defined_field1'] . '|' . $batchArr['user_defined_field2'] . '|' . $batchArr['user_defined_field3'] . '|' . $batchArr['user_defined_field4'] . '|' . $batchArr['user_defined_field5'] . '|' . $batchArr['service_code'] . '|' . $batchArr['user_approved'] . '|' . $batchArr['beneficiary_family_name'] . '|' . $batchArr['beneficiary_first_name'] . '|' . $batchArr['beneficiary_middle_name1'] . '|' . $batchArr['beneficiary_middle_name2'] . '|' . $batchArr['beneficiary_address1'] . '|' . $batchArr['beneficiary_address2'] . '|' . $batchArr['beneficiary_address3'] . '|' . $batchArr['beneficiary_zip_code'] . '|' . $batchArr['beneficiary_telephone'] . '|' . $batchArr['legal_identification_type'] . '|' . $batchArr['register_with_load_agent'] . '|' . $batchArr['depositor_bank_id'] . '|' . $batchArr['user_defined_field6'] . '|' . $batchArr['user_defined_field7'] . '|' . $batchArr['user_defined_field8'] . '|' . $batchArr['user_defined_field9'] . '|' . $batchArr['card_type'] . '|' . $batchArr['card_classification'] . '|' . $batchArr['filler'] .'|';
                //$acHolderMainArr[] = $acHolderArr;
                $acDisMainArr[] = $acDisArr;
                //$acIncomeMainArr[] = $acIncomeArr;
                //$serTaxMainArr[] = $serTaxArr;
                //unset($acHolderArr);
                unset($acDisArr);
                //unset($acIncomeArr);
                //unset($serTaxArr);
            }
            //$acHolderArr = 
            
            //Getting decimal length issue
            //$acHolderArr['amount'] = number_format($acHolderArr['amount'], '2', '.', '');
            
            $acHolderArr['amount'] = sprintf("%01.2f", $acHolderArr['amount']);
            $acHolderMainArr = $this->generateCuttoffBatchArray($acHolderArr);
            $acIncomeMainArr    = $this->generateCuttoffBatchArray($acIncomeArr);
            $serTaxMainArr      = $this->generateCuttoffBatchArray($serTaxArr);
            
            array_unshift($acDisMainArr, $acHolderMainArr);
            array_push($acDisMainArr, $acIncomeMainArr);
            array_push($acDisMainArr, $serTaxMainArr);
            $batchMainArr = $acDisMainArr;
            $file_name = 'AUTODEBITTTUM' . $param['start_date'] . '_' . $param['end_date'] . '.' . $ext;
            $file = new Files();
            $id = $file->insert(array(
                'label' => BOI_TTUM_FILE,
                'file_name' => $file_name,
                'date_start' => $param['start_date'],
                'date_end' => $param['end_date'],
                'status' => STATUS_ACTIVE,
                'comments' => '',
                'date_created' => new Zend_Db_Expr('NOW()')
            ));
            if ($id > 0) {
                $file_name = 'AUTODEBITTTUM' . $param['start_date'] . '_' . $param['end_date'] . '_' . $id . '.' . $ext;
                $file->update(array('file_name' => $file_name), "id=$id");
            }

            $file->setBatch($batchMainArr, $seprator);
            $file->setFilepath(APPLICATION_UPLOAD_PATH);
            $file->setFilename($file_name);
            $file->generate(TRUE);
        }
        return TRUE;
    }

    
    
    public function generateTTUMFileForDisbursement($disNumber, array $data, array $param) {
        
        $fileName = $param['file_name'];
        $objR = New ObjectRelations();
        $seprator = '';
        $ext = 'txt';
        $acHolderArr = array();
        $acHolderArr['amount'] = '';
        if (!empty($data)) {
            foreach ($data as $value) {

//ENTRY First                 
                $paramArr = array(
                    'account_no' => '604820110000221',//'01220SUNDEP031 ', //$value['boi_account_number'],
                    'currency_code' => CURRENCY_INR_CODE,
                    'service_outlet' => '',
                    'trans_type' => 'D',
                    'amount' => $acHolderArr['amount'] + $value['amount'],
                    'particulars' => $value['narration'],
                    'report_code' => '',
                    'ref_num' => $value['ref_num'],
                );
                $acHolderArr = $paramArr;

//ENTRY SECOND
                $amountDis = ($value['amount']);//Util::convertToPaisa($value['amount']);
                $amountDis = number_format($amountDis, '2', '.', '');//Util::convertToPaisa($value['amount']);
                $paramArr['account_no'] = $value['account_number'];
                $paramArr['amount'] = $amountDis;//Util::formatString($amountDis, 'int', '14', ' ');
                $paramArr['trans_type'] = 'C';
                $acDisArr = $this->generateCuttoffBatchArray($paramArr);
                $acDisMainArr[ $value['account_number']]['c'] = $acDisArr;
                unset($acDisArr);
                
                
                $debitParamArr = array(
                    'account_no' => '01220SUNDEP031 ', //$value['boi_account_number'],
                    'currency_code' => CURRENCY_INR_CODE,
                    'service_outlet' => '',
                    'trans_type' => 'C',
                    'amount' => $debitHolderArr['amount'] + $value['debit_mandate_amount'],
                    'particulars' => $value['narration'],
                    'report_code' => '',
                    'ref_num' => $value['ref_num'],
                );
                $debitHolderArr = $debitParamArr;

//ENTRY SECOND
                $amountDis = ($value['debit_mandate_amount']);
                $amountDebitDis = number_format($amountDis, '2', '.', '');//Util::convertToPaisa($value['amount']);
                $debitParamArr['account_no'] = $value['account_number'];
                $debitParamArr['amount'] = $amountDebitDis;//Util::formatString($amountDis, 'int', '14', ' ');
                $debitParamArr['trans_type'] = 'D';
                $acDebitDisArr = $this->generateCuttoffBatchArray($debitParamArr);

                //$acDebitDisMainArr[] = $acDebitDisArr;
                $acDisMainArr[ $value['account_number']]['d'] = $acDebitDisArr;
                unset($acDebitDisArr);
                
            }
            
            
            //$acHolderArr['amount'] = number_format(Util::convertToPaisa($acHolderArr['amount']), '2', '.', '');
            $acHolderArr['amount'] = number_format(($acHolderArr['amount']), '2', '.', '');
            //$debitHolderArr['amount'] = number_format(Util::convertToPaisa($debitHolderArr['amount']), '2', '.', '');
            $debitHolderArr['amount'] = number_format(($debitHolderArr['amount']), '2', '.', '');
            
            $acHolderMainArr = $this->generateCuttoffBatchArray($acHolderArr);
            $debitHolderMainArr = $this->generateCuttoffBatchArray($debitHolderArr);
            $batchMainArr = array();
            
            array_push($batchMainArr, $debitHolderMainArr);
            foreach ($acDisMainArr as $array) {
                $batchMainArr[] = $array['c'];
                $batchMainArr[] = $array['d'];
            }            
            array_push($batchMainArr, $acHolderMainArr);
            
            $file = new Files();

            $file->setBatch($batchMainArr, $seprator);
            $file->setFilepath(APPLICATION_UPLOAD_PATH);
            $file->setFilename($fileName);
            $file->generate(TRUE);
            //return $file_name;
        }
        return TRUE;
    }
    
    public function generateWalletFileForDisbursement(array $data, array $param) {
        
        $fileName = $param['file_name'];
        //$objR = New ObjectRelations();
        $seprator = ',';
        //$ext = '.csv';
        
        if (!empty($data)) {
            foreach ($data as $value) {
                $paramArr = array(
                    'txn_identifier' => $value['txn_identifier'],
                    'account_number' => $value['account_number'],
                    'amount' => $value['debit_mandate_amount'],
                    'currency' => CURRENCY_INR_CODE,                    
                    'narration' => $value['narration'],
                    'wallet_code' => $value['wallet_code'],
                    'txn_no' => $value['txn_no'],
                    'card_type' => strtoupper($value['card_type']),
                    'corporate_id' => $value['corporate_id'],
                    'mode' => strtoupper($value['mode']),
                );
                $acMainArr[] = $paramArr;
               unset($paramArr);
            }
            $eof = 'EOF00000'.count($data);
            $acMainArr[] = array('txn_identifier' => $eof);
            $file = new Files();
            $file->setBatch($acMainArr, $seprator);
            $file->setFilepath(APPLICATION_UPLOAD_PATH);
            $file->setFilename($fileName);
            $file->generate(TRUE);
        }
        return TRUE;
    }

    private function generateCuttoffBatchArray($param) {
        //echo '<pre>';print_r($param);
        $batchArr['account_number'] = Util::formatString($param['account_no'], 'string', '15', '0') . ' ';
        $batchArr['currency_code'] = CURRENCY_INR;
        $batchArr['service_outlet'] = '00380   ';
        $batchArr['trans_type'] = $param['trans_type'];
        $batchArr['transaction_amount'] = Util::formatString($param['amount'], 'int', '14', '0');//$param['amount'];
        $batchArr['perticulars'] = Util::formatString($param['particulars'], 'string', '125', ' ','');
        $batchArr['date'] = date('d-m-Y');
        //echo Util::formatString($param['perticulars'], 'string', '125', ' ','').'<br />';
        //echo $param['perticulars']. ' <br />';
        //echo '<pre>';print_r($batchArr);exit;
        //$batchArr['report_code'] = '';
        //$batchArr['refrence_number'] = $param['ref_num'];
        //$batchArr['other_1'] = '';
        //$batchArr['report_code'] = '';
        return $batchArr;
    }
    
    
     /*
     * doAccountLoad will do corporate load Accounts
     */

    public function doAccountLoad() {

        $loadRequests = $this->getLoadRequests(array('limit' => BOI_NSDC_ACCOUNT_LOAD_LIMIT, 'status' => STATUS_PENDING, 'load_channel' => BY_OPS, 'type' => 'act'));
        $count = count($loadRequests);
        $retResp = array('loaded' => 0, 'not_loaded' => 0, 'exception' => array());
        if ($count > 0) {
            $baseTxn = new BaseTxn();
            $m = new \App\Messaging\Corp\Boi\Operation();
            $cardholderModel = new Corp_Boi_Customers();
            $custPurseModel = new Corp_Boi_CustomerPurse();
            foreach ($loadRequests as $key => $val) {
                try {
                    
                    $statusPending = $this->isRecordPending($val['id']);
                    if($statusPending) {
                        
                        $updateArrStatus = array(
                                    'status'=>STATUS_STARTED, 
                                    'date_load' => new Zend_Db_Expr('NOW()')
                                );
                        $this->updateLoadRequests($updateArrStatus, $val['id']);
                    
                        
                    $validator = array(
                        'load_request_id' =>  $val['id'],
                        'customer_master_id' =>  $val['customer_master_id'],
                        'purse_master_id' =>  $val['purse_master_id'],
                        'customer_purse_id' =>  $val['customer_purse_id'],
                        'amount' =>  $val['amount'],
                    );
                    $flgValidate = $baseTxn->chkAllowBoiCorporateCardLoad($validator);
                    if($flgValidate)
                    {
                        //$loadAmount = Util::getRs($val['amount']);
                        $cardLoadData = array(
                            'amount' => $val['amount'],
                            'crn' => $val['card_number'],
                            'agentId' => TXN_OPS_ID, // any data to be provided here, so default ops id
                            'transactionId' => $val['txn_code'],
                            'currencyCode' => CURRENCY_INR_CODE,
                            'countryCode' => COUNTRY_IN_CODE
                        );
                        
                        $apiResp = TRUE;
                        $balVal = FALSE;
                        if($apiResp === TRUE){
                            
                            $updateArr = array(
                                    'amount_available' => $val['amount'],
                                    'amount_used' => 0,
                                    'amount_cutoff' => 0,
                                    'txn_load_id' => 0,
                                    'status'=>STATUS_LOADED, 
                                    'date_load' => new Zend_Db_Expr('NOW()')
                                );
                            $this->updateLoadRequests($updateArr, $val['id']);
                            
                            $retResp['loaded'] = $retResp['loaded'] + 1;
                            
                            $baseTxnParams = array(
                              'txn_code' =>  $val['txn_code'], 
                              'customer_master_id' =>  $val['customer_master_id'], 
                              'product_id' =>  $val['product_id'], 
                              'purse_master_id' =>  $val['purse_master_id'], 
                              'customer_purse_id' =>  $val['customer_purse_id'], 
                               'amount' =>  $val['amount'], 
                            );
                            $baseTxn->successBoiCorporateCardLoad($baseTxnParams);
                            
                            $cardholder = $cardholderModel->findById($val['cardholder_id']);
                            $custPurse = $custPurseModel->getCustBalance($cardholder['boi_customer_id']);
                            $userData = array(
                                    'last_four' =>substr($cardholder['account_no'], -4),
                                    'product_name' => BOI_NSDC_PRODUCT_WALLET,
                                    'amount' => $val['amount'],
//                                    'balance' => Util::numberFormat($balVal / 100), //$custPurse['sum'],
                                    'mobile' => $cardholder['mobile'],
                                );
                                $resp = $m->cardLoad($userData);
                            
                        }
                        else{
                            $failedReason = 'Failed';
                            $updateArr = array(
                                    'status' => STATUS_FAILED, 
                                    'date_failed' => new Zend_Db_Expr('NOW()'),
                                    'failed_reason' => $failedReason,
                                    'date_load' => new Zend_Db_Expr('NOW()')
                                );
                            $this->updateLoadRequests($updateArr, $val['id']);
                            
                            $retResp['not_loaded'] = $retResp['not_loaded'] + 1;

                        }
                    }
                    } // if pendig ends
                    
                } catch (App_Exception $e) {
                    $retResp['not_loaded'] = $retResp['not_loaded'] + 1;
                    $errorMsg = $e->getMessage();
                    $countException = count($retResp['exception']);
                    $retResp['exception'][$countException] = 'Exception of CRN ' . $val['card_number'] . ' with txn id ' . $val['txn_code'] . ' is ' . $errorMsg;
                    $updateArr = array(
                                    'status' => STATUS_FAILED, 
                                    'date_failed' => new Zend_Db_Expr('NOW()'),
                                    'failed_reason' => $e->getMessage(),
                                    'date_load' => new Zend_Db_Expr('NOW()')
                                );
                    $this->updateLoadRequests($updateArr, $val['id']);
                } catch (Exception $e) {
                    $retResp['not_loaded'] = $retResp['not_loaded'] + 1;
                    $errorMsg = $e->getMessage();
                    $countException = count($retResp['exception']);
                    $retResp['exception'][$countException] = 'Exception of CRN ' . $val['card_number'] . ' with txn id ' . $val['txn_code'] . ' is ' . $errorMsg;
                    $updateArr = array(
                                    'status' => STATUS_FAILED, 
                                    'date_failed' => new Zend_Db_Expr('NOW()'),
                                    'failed_reason' => $e->getMessage(),
                                    'date_load' => new Zend_Db_Expr('NOW()')
                                );
                    $this->updateLoadRequests($updateArr, $val['id']);
                }
                  
                  
            } // foreach
        }
        return $retResp;
    }
    public function getWalletTrialBalance($param) 
    {
        $productId = isset($param['product_id']) ? $param['product_id'] : '';
        $bankUnicode = isset($param['bank_unicode']) ? $param['bank_unicode'] : '';
        
        $purseModel = new MasterPurse();
        $custPurseModel = new Corp_Boi_CustomerPurse();
        $authRequestModel = new AuthRequest();
        $bankModel = new Banks();
        $bankDetails = $bankModel->getBankbyUnicode($bankUnicode);
        
        $masterPurseDetails = $purseModel->getPurseDetailsbyBankIdProductId($productId, $bankDetails->id);
      
        $datearr = explode(" ",$param['from']);
        
        $arrReport = array();
     
        $dateselected = strtotime($datearr[0]);
        $dayBefore = strtotime("-1 day", $dateselected);
        $dayBefore = date('Y-m-d',$dayBefore);
       
        $param['date'] = $dayBefore;
        
        $param['purse_master_id'] = $masterPurseDetails[0]['id'];
         // get opening balance
        $purseOpeningBal = $custPurseModel->getWalletClosingBalance($param);
       
        $arrReport['opening_bal'] = (!empty($purseOpeningBal['balance']))? $purseOpeningBal['balance'] : 0;
        
        $param['date'] = '';
        
        $param['txn_type'] = TXNTYPE_BOI_CORP_CORPORATE_LOAD;
        
        // Fetch successfull Loads from request table
        $loadsArr = $this->getSuccessfulLoads($param);
        
        
        $arrReport['loads'] = (!empty($loadsArr))? $loadsArr->total : 0;
       
         // Fetch successful reversal at cutoff time from request table
       
        $arrReport['reversal'] = (!empty($loadsArr)) ? $loadsArr->cutoff_total : 0;
        $arrReport['sub_total'] = $arrReport['loads'] - $arrReport['reversal'];
        
        // Fetch Misc DR/CR
        $miscDRdetails = $authRequestModel->getCompletedTxn($param);
        
        $arrReport['txn_dr'] =  (!empty($miscDRdetails))? $miscDRdetails->completed_total : 0;
        
        
        $miscCRdetails = $authRequestModel->getReversedTxn($param);
       
        $arrReport['txn_cr'] =  (!empty($miscCRdetails)) ? $miscCRdetails->reversed_total : 0;

        
        $arrReport['txns'] =  $arrReport['txn_dr']- $arrReport['txn_cr'];
        
        $arrReport['calculated_balance'] =  $arrReport['sub_total'] - $arrReport['txns'];
        
        $param['date'] = $datearr[0];
        $purseClosingBal = $custPurseModel->getWalletClosingBalance($param);
        $arrReport['closing_bal'] = $purseClosingBal['balance'];
        
        // Fetch actual wallet closing balance
//        $walletBalance = $custPurseModel->getWalletBalance($param);
//        $arrReport['wallet_balance'] =(!empty($custPurseModel))? $walletBalance->wallet_sum : 0;
        $arrReport['wallet_balance'] =(!empty($arrReport['closing_bal']))? $arrReport['closing_bal'] : 0;
        $arrReport['difference'] = $arrReport['calculated_balance'] - $arrReport['wallet_balance'];
        
        return $arrReport;
        
    }
     /*
     * getSuccessfullLoads will return the successfull Load reuqests
     */

    public function getSuccessfulLoads($param) {
        $type = isset($param['txn_type']) ? $param['txn_type'] : '';
        $purseMasterId = isset($param['purse_master_id']) ? $param['purse_master_id'] : '';
        $productId = isset($param['product_id']) ? $param['product_id'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $select = $this->select() 
                ->from(DbTable::TABLE_BOI_CORP_LOAD_REQUEST, array('count(*) as count', 'sum(amount) as total','sum(amount_cutoff) as cutoff_total'));
        
        if ($type != '') {
            $select->where('txn_type = ?', $type);
        }
        if ($purseMasterId != '') {
            $select->where('purse_master_id = ?', $purseMasterId);
        }
        if ($productId != '') {
            $select->where('product_id = ?', $productId);
        }
        if ($from != '' && $to != ''){
            $select->where("date_load >=  '" . $from . "'");
            $select->where("date_load <= '" . $to . "'");
            $select->where("status IN  ('" . STATUS_LOADED . "', '".STATUS_CUTOFF."')");
        }
        $select->group("purse_master_id");
        
        //echo $select; exit;
        return $this->fetchRow($select);
    }
    
     /*
     * getSuccessfullLoads will return the successfull Load reuqests
     */

    public function getReversal($param) {
        $type = isset($param['txn_type']) ? $param['txn_type'] : '';
        $purseMasterId = isset($param['purse_master_id']) ? $param['purse_master_id'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $select = $this->select() 
                ->from(DbTable::TABLE_BOI_CORP_LOAD_REQUEST, array('count(*) as count', 'sum(amount_cutoff) as reversal_total'));
        
        if ($type != '') {
            $select->where('txn_type = ?', $type);
        }
        if ($purseMasterId != '') {
            $select->where('purse_master_id = ?', $purseMasterId);
        }
        if ($from != '' && $to != ''){
            $select->where("date_cutoff >=  '" . $from . "'");
            $select->where("date_cutoff <= '" . $to . "'");
            $select->where("status =?",STATUS_LOADED);
        }
         $select->group("purse_master_id");
        return $this->fetchRow($select);
    }
    
    
    public function disbursementCheckFilename($fileName) {
        $select = $this->_db->select();    
        $select->from(DbTable::TABLE_BOI_DISBURESEMENT_BATCH, array('id'));
        $select->where("batch_name =?", $fileName);
        $select->where("status =?", STATUS_ACTIVE);
        $rs = $this->_db->fetchRow($select); 
        if (empty($rs)) {
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    /*
     * insertdisbursementRequestBatch , insert data from file into the batch table
     */

    public function insertdisbursementRequestBatch($dataArr, $batchName, $status) {
        $product = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NSDC);                
        $productModel = new Products();
        $productInfo = $productModel->getProductInfoByUnicode($product->product->unicode);

        try {
            $data = array(
                'product_id' => $productInfo['id'],
                'customer_master_id' => '',//$dataArr['customer_master_id'],
                'txn_identifier' => strtolower($dataArr[0]),
                'account_number' => $dataArr[1],
                'ifsc_code' => $dataArr[2],
                'aadhar_no' => $dataArr[3],
                'amount' => Util::convertToPaisa($dataArr[4]),
                'currency' => $dataArr[5],
                'narration' => $dataArr[6],
                'wallet_code' => $dataArr[7],
                'txn_no' => $dataArr[8],
                'card_type' => strtolower($dataArr[9]),
                'corporate_id' => $dataArr[10],
                'mode' => strtolower($dataArr[11]),
                'bucket'=>'',
                'status' => $status,
                'batch_name' => $batchName,
                'disbursement_number' =>$dataArr['disbursement_number'],
                'date_create' => new Zend_Db_Expr('NOW()')
            );
           
            $this->_db->insert(DbTable::TABLE_BOI_DISBURESEMENT_BATCH, $data);

            return TRUE;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            echo $e->getMessage();
        }
    }
    
    /**
     * Retrieves all the products attached to
     * the specified master product
     * 
     * @param integer $resourceId
     * @access public
     * @return void
     */
    /*
     * showPendingCardloadDetails , show load requests from batch table which have upload status as temp
     */
    public function showPendingDisbursementCardloadDetails($batchName) {
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BOI_DISBURESEMENT_BATCH);
        $select->where('status = ?', STATUS_TEMP);
        $select->where('batch_name = ?', $batchName);
        $select->order('id ASC');
        return $this->_db->fetchAll($select);
    }
    
    public function updateDisbursementLoadStatus($idArr,$batchName) {
            $activeId = implode(",", $idArr);
            $activeArr = array('status' => STATUS_ACTIVE);
            $this->_db->update(DbTable::TABLE_BOI_DISBURESEMENT_BATCH, $activeArr, "id IN ($activeId) AND batch_name = '$batchName'");
    }
    
    public function updateDisbursementLoad($data,$where) {
            $this->_db->update(DbTable::TABLE_BOI_DISBURESEMENT_BATCH, $data, $where);
    }
    
    public function getDisbursementLoad($page = 1, $data = array(), $paginate = NULL, $force = FALSE) {
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BOI_DISBURESEMENT_BATCH);
        if(!empty($data['disbursement_number'])){
            $disbursementNumbers = explode(",",$data['disbursement_number']);
            $select->where('disbursement_number IN( ? )', $disbursementNumbers);
        }
        
        if(!empty($data['id'])){
            $select->where('id IN( ? )', $data['id']);    
        }
        if(!empty($data['bucket'])){
            //$bucketIds = explode(",",$data['bucket']);
            $select->where('bucket IN( ? )', $data['bucket']);    
        }

        if(!empty($data['aadhar_no'])){  
            $aadharNumbers = explode(",",$data['aadhar_no']);
            $select->where('aadhar_no IN( ? )', $aadharNumbers);
        }
        if(!empty($data['account_number'])){ 
            $accountNumbers = explode(",",$data['account_number']);
            $select->where('account_number IN( ? )', $accountNumbers);
        }
        if(!empty($data['batch_name'])){
            $select->where('batch_name LIKE ?', "%".$data['batch_name']."%");    
        }
        if(!empty($data['payment_status'])){
            $select->where('payment_status = ?', $data['payment_status']);    
        }
        $select->where('status = ?', STATUS_ACTIVE);
        $select->order('id ASC');
        //$select->limit(3000);

        if($data['noofrecords'] > 0){
            $select->limit($data['noofrecords']);
        }

        if($force){
            return $this->_db->fetchAll($select);
        }else{
            return $this->_paginate($select, $page, $paginate);
        }
    }
    
    public function getDisbursementFile($page = 1, $data = array(), $paginate = NULL, $force = FALSE) {
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BOI_DISBURESEMENT_FILE);
        
        if(!empty($data['id'])){
            $select->where('id= ?', $data['id']);
        }
        if(!empty($data['disbursement_number'])){
            $select->where('disbursement_no = ?', $data['disbursement_number']);
        }
        if(!empty($data['to']) && !empty($data['from'])){
            $select->where("date_created  >= '". $data['from']."' AND date_created  <= '". $data['to']."'");    
        }
        if(!empty($data['batch_name'])){
            $select->where("updated_ttum_file_name LIKE '%".$data['batch_name']."%' OR file_name LIKE '%".$data['batch_name']."%'" );
            //$select->orwhere('updated_ttum_file_name LIKE ?', "%".$data['batch_name']."%");
        }
        $select->order('id ASC');
        //echo $select; //exit; 
        return $this->_paginate($select, $page, $paginate);
    }
    public function updateDisbursementFile($data,$where) {
            $this->_db->update(DbTable::TABLE_BOI_DISBURESEMENT_FILE, $data, $where);
            
    }
    public function getDisbursementFileInfo($id) {
        if(!empty($id)){
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_BOI_DISBURESEMENT_FILE);
            $select->where('id = ?', $id);
            return $this->_db->fetchRow($select);
        }
        return false;
    }
     public function getTotalLoad($param) {
        $purseId = isset($param['customer_purse_id']) ? $param['customer_purse_id'] : '';
        $productId = isset($param['product_id']) ? $param['product_id'] : '';
        $txnType = isset($param['txn_type']) ? $param['txn_type'] : '';
        $onDate = isset($param['on_date']) ? $param['on_date'] : FALSE;
        $status = isset($param['status']) ? $param['status'] : '';
        $rbiReport = isset($param['rbi_report']) ? $param['rbi_report'] : FALSE;
        $cutoff = isset($param['cutoff']) ? $param['cutoff'] : FALSE;
       
        
        if ($purseId > 0 || $rbiReport) {
 
            $select = $this->select();
            if($cutoff){
                $select->from($this->_name, array('sum(amount_cutoff) as total_load_amount','count(id) as cnt'));
            } else {
                $select->from($this->_name, array('sum(amount) as total_load_amount','count(id) as cnt'));
            }
            if($purseId != ''){
            $select->where('customer_purse_id = ?', $purseId);
             }
             if($productId != ''){
            $select->where('product_id = ?', $productId);
             }
            if($status != ''){
                 $select->where("status IN ('".$status."')");
            }
            else{
            $select->where("status IN ('".STATUS_LOADED."','".STATUS_CUTOFF."')");
            }
            if ($txnType != '') {
            $select->where('txn_type = ?', $txnType);
            }
            else{
            $select->where("txn_type IN ('". TXNTYPE_BOI_CORP_CORPORATE_LOAD."','".TXNTYPE_CARD_RELOAD."')");  
            }
            if($cutoff){
                if ($onDate) {
                    $date = isset($param['date']) ? $param['date'] : '';
                    $select->where('DATE(date_cutoff) =?', $date);
                } else {
                    $fromDate = isset($param['from']) ? $param['from'] : '';
                    $toDate = isset($param['to']) ? $param['to'] : '';
                    $select->where('date_cutoff >= ?', $fromDate);
                    $select->where('date_cutoff <= ?', $toDate);
                }
            } else {
                if ($onDate) {
                    $date = isset($param['date']) ? $param['date'] : '';
                    $select->where('DATE(date_created) =?', $date);
                } else {
                    $fromDate = isset($param['from']) ? $param['from'] : '';
                    $toDate = isset($param['to']) ? $param['to'] : '';
                    $select->where('date_created >= ?', $fromDate);
                    $select->where('date_created <= ?', $toDate);
                }
            }
           
            return $this->fetchRow($select);
        }
        else
            return 0;
    }
    
   public function getAllLoad($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : ''; 
        
        if ($from != '' && $to != '') {
            $select = $this->select();
            $select->from($this->_name,
                        array(
                            'sum(amount) as total_agent_loads',
                            'count(id) as total_agent_loads_count',
                            'DATE_FORMAT(date_created,"%d-%m-%Y") AS txn_date' 
                        ));  
            if($agentId != ''){
                $select->where('by_agent_id = ?', $agentId);
            }
            
            if($status != ''){
                $select->where("status IN ('".$status."')");
            } else{
                $select->where("status IN ('".STATUS_LOADED."','".STATUS_CUTOFF."')");
            }
            if ($txnType != '') {
                $select->where('txn_type = ?', $txnType);
            } else{
                $select->where("txn_type IN ('". TXNTYPE_BOI_CORP_CORPORATE_LOAD."','".TXNTYPE_CARD_RELOAD."')");  
            }
            $select->where("DATE(date_created) BETWEEN '". $from ."' AND '". $to ."'"); 
            $select->group('DATE_FORMAT(date_created, "%Y-%m-%d")');
            return $this->_db->fetchAll($select);
        } else
            return ''; 
    }
    
    public function getWalletbalance($data) {
        $productId = isset($data['product_id']) && $data['product_id'] > 0 ? $data['product_id'] : '';
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`c`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`c`.`crn`,'".$decryptionKey."') as crn");
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BOI_CORP_LOAD_REQUEST . ' as lr', array('lr.wallet_code','lr.amount','lr.date_load','lr.date_cutoff','lr.status', 'lr.amount_cutoff', 'DATE(lr.date_load) as date'));
        $select->join(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as c', "lr.cardholder_id = c.id", array($card_number,'c.aadhaar_no','c.cust_id',$crn,'c.member_id','c.mobile'));
        //$select->joinLeft(DbTable::TABLE_BOI_CUSTOMER_PURSE_CLOSING_BALANCE . ' as cb', "lr.customer_purse_id = cb.customer_purse_id and DATE(lr.date_load) = cb.date", array('cb.closing_balance'));
        $select->joinleft(DbTable::TABLE_PRODUCTS.' as p','lr.product_id=p.id',array('p.id as product_id', 'p.name as product_name', 'p.ecs_product_code as product_code','program_type'));        
        if($productId != ''){
        $select->where("lr.product_id =?",$data['product_id']);
        }
        $select->where("lr.status IN ('". STATUS_LOADED."', '".STATUS_CUTOFF."')");
        $select->where("DATE(lr.date_load) = '" . $data['to'] . "'");
        $select->order('lr.id ASC');
        return $this->_db->fetchAll($select);
    }

    public function exportGetWalletbalance($param) {

        $data = $this->getWalletbalance($param);

        $retData = array();

        if (!empty($data)) {
            foreach ($data as $key => $data) {

                $retData[$key]['product_name'] = $data['product_name'];
                $retData[$key]['bank_name'] = BANK_OF_INDIA;
                $retData[$key]['aadhaar_no'] = $data['aadhaar_no'];
                $retData[$key]['currency'] = CURRENCY_INR;
                $retData[$key]['card_number'] = Util::maskCard($data['card_number']);
                $retData[$key]['crn'] = Util::maskCard($data['crn']);
                $retData[$key]['mobile'] = $data['mobile'];
                $retData[$key]['member_id'] = $data['member_id'];
                $retData[$key]['cust_id'] = $data['cust_id'];
                $retData[$key]['wallet_code'] = $data['wallet_code'];
                $retData[$key]['closing_balance'] = $data['closing_balance'];
                $retData[$key]['status'] = 'Active';
                $retData[$key]['corporate_code'] = "-";
		$retData[$key]['corporate_name'] = "-";
                $retData[$key]['report_date'] = Util::returnDateFormatted($data['date'], "d-m-Y", "Y-m-d", "-");
            }
        }

        return $retData;
    }
    
    public function checkDisbursementNo($disbNo) {
        $select = $this->_db->select();    
        $select->from(DbTable::TABLE_BOI_DISBURESEMENT_FILE, array('id'));
        $select->where("disbursement_no =?", $disbNo);
        $select->where("status IN ('". STATUS_ACTIVE."', '".STATUS_PROCESSED."')");
        $rs = $this->_db->fetchRow($select); 
        if (empty($rs)) {
            return TRUE;
        } else {
            return FALSE;
        }
        
    }

    public function getSummaryBucket($page = 1, $data = array(), $paginate = NULL, $force = FALSE) {
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_BOI_DISBURESEMENT_BATCH, array("count(*) as cnt","sum(amount) as amt","bucket","disbursement_number"));
            if(count($data['bucket'])){
            //$bucketIds = explode(",",$data['bucket']);
                $select->where('bucket IN( ? )', $data['bucket']);    
            }
            if(!empty($data['aadhar_no'])){
                $select->where('aadhar_no = ?', $data['aadhar_no']);    
            }
            if(!empty($data['account_number'])){
                $select->where('account_number = ?', $data['account_number']);    
            }
            if(!empty($data['disbursement_number'])){
                $disbursementNumbers = explode(",",$data['disbursement_number']);
                $select->where('disbursement_number IN( ? )', $disbursementNumbers);
            }
            $select->where('status = ?', STATUS_ACTIVE);
            $select->group('bucket');
            $select->group('disbursement_number'); 
            $select->order('id ASC');
            //echo $select; exit;
            $rs = $this->_db->fetchAll($select); 
            
           
            
            $globalBuckets = Zend_Registry::get("BOI_NSDC_DISBURSEMENT_BUCKETS");
            //unset($globalBuckets['9']);
            foreach($rs as $data){
             
                foreach($globalBuckets as $key => $val){
                    if($key == $data['bucket']){
                        //unset($globalBuckets[$key]);
                        $dataArr[$data['disbursement_number']][$key]['count'] = $data['cnt'];
                        $dataArr[$data['disbursement_number']]['totalCnt'] = $dataArr[$data['disbursement_number']]['totalCnt'] + $data['cnt'];
                        $dataArr[$data['disbursement_number']][$key]['amount'] = $data['amt'];
                        $dataArr[$data['disbursement_number']]['totalAmt'] = $dataArr[$data['disbursement_number']]['totalAmt'] + $data['amt'];
                        $dataArr[$data['disbursement_number']][$key]['bucket'] = $data['bucket'];
                         //$i++;
                         break;
                    }
                }
                $dataArr[$data['disbursement_number']]['disbursement_number'] = $data['disbursement_number'];
                
            }
            //echo "<pre>";print_r($dataArr); exit;
        return $dataArr;
    }
    
    public function getSummaryPaymentBucket($page = 1, $data = array(), $paginate = NULL, $force = FALSE) {
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BOI_DISBURESEMENT_BATCH.' as bd', array("disbursement_number",'payment_status','sum(amount) as total','count(bd.id) as count'));
        $select->joinleft(DbTable::TABLE_BOI_CORP_CARDHOLDER.' as bc','bd.product_customer_id=bc.id', array("bc.id as cardholder_id"));
        //echo $select; exit; 
        if(!empty($data['disbursement_number'])){
            $disbursementNumbers = explode(",",$data['disbursement_number']);
            $select->where('bd.disbursement_number IN( ? )', $disbursementNumbers);
        }
        if(count($data['payment_status'])){
            //$bucketIds = explode(",",$data['bucket']);
            $select->where('bd.payment_status IN( ? )', $data['payment_status']);    
        }
        if(!empty($data['aadhar_no'])){
            $select->where('bd.aadhar_no = ?', $data['aadhar_no']);    
        }
        if(!empty($data['account_number'])){
            $select->where('bd.account_number = ?', $data['account_number']);    
        }
        if(!empty($data['tp_name'])){
            $select->where('bc.training_partner_name = ?', $data['tp_name']);
             $select->where('bc.id IS NOT NULL');
        }
       
        $select->where('bd.status = ?', STATUS_ACTIVE);
        $select->group('bd.disbursement_number');
        $select->group('bd.payment_status');    
        $select->order('bd.id ASC');
        //echo $select; //exit; 
        $disbursementNumbersResult = $this->_db->fetchAll($select);
        
        $dataArr=array();
        $i=0;
        foreach($disbursementNumbersResult as $result){
            $dataArr[$result['disbursement_number']]['disbursement_number'] = $result['disbursement_number'];
            if($result['payment_status']=="generated"){
                $dataArr[$result['disbursement_number']]['ttum_generated']['count'] = $dataArr[$result['disbursement_number']]['ttum_generated']['count'] + $result['count'];
                $dataArr[$result['disbursement_number']]['ttum_generated']['total'] = $dataArr[$result['disbursement_number']]['ttum_generated']['total'] + $result['total'];
            }elseif($result['payment_status']=="processed"){
                $dataArr[$result['disbursement_number']]['ttum_processed']['count'] = $dataArr[$result['disbursement_number']]['ttum_processed']['count'] + $result['count'];
                $dataArr[$result['disbursement_number']]['ttum_processed']['total'] = $dataArr[$result['disbursement_number']]['ttum_processed']['total'] + $result['total'];
            }elseif($result['payment_status']=="hold"){ 
                $dataArr[$result['disbursement_number']]['ttum_hold']['count'] = $dataArr[$result['disbursement_number']]['ttum_hold']['count'] + $result['count'];
                $dataArr[$result['disbursement_number']]['ttum_hold']['total'] = $dataArr[$result['disbursement_number']]['ttum_hold']['total'] + $result['total'];
            }elseif($result['payment_status']=="manual"){
                $dataArr[$result['disbursement_number']]['ttum_manual']['count'] = $dataArr[$result['disbursement_number']]['ttum_manual']['count'] + $result['count'];
                $dataArr[$result['disbursement_number']]['ttum_manual']['total'] = $dataArr[$result['disbursement_number']]['ttum_manual']['total'] + $result['total'];
            }elseif($result['payment_status']=="pending"){
                $dataArr[$result['disbursement_number']]['ttum_pending']['count'] = $dataArr[$result['disbursement_number']]['ttum_pending']['count'] + $result['count'];
                $dataArr[$result['disbursement_number']]['ttum_pending']['total'] = $dataArr[$result['disbursement_number']]['ttum_pending']['total'] + $result['total'];
            }
            
            $dataArr[$result['disbursement_number']]['count'] = $dataArr[$result['disbursement_number']]['count'] + $result['count'];
            $dataArr[$result['disbursement_number']]['total'] = $dataArr[$result['disbursement_number']]['total'] + $result['total'];
            $i++;
        }
        //echo "<pre>"; print_r($dataArr); //exit;
        return $dataArr;
    }
    
    public function getTPName(){
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BOI_DISBURESEMENT_BATCH.' as bd', array());
        $select->joinleft(DbTable::TABLE_BOI_CORP_CARDHOLDER.' as bc','bd.product_customer_id=bc.id', array("id","training_partner_name"));
        $select->where('bd.status = ?', STATUS_ACTIVE);
        $select->where('bc.id IS NOT NULL');
        $select->group('bc.training_partner_name');
        $rs = $this->_db->fetchAll($select); 
        $dataArr=array(''=>'All');
        foreach($rs as $result){
            $dataArr[$result['training_partner_name']]=$result['training_partner_name'];
        }
        return $dataArr;
    }
    
    public function updateDisbursementPaymentStatus($data) {
            $user = Zend_Auth::getInstance()->getIdentity();
            $ids = explode(",",$data['update_ids']);
            $disbursementDetails = $this->getDisbursementLoad(NULL, array('id'=>$ids), NULL, TRUE);
            $disbursementStatusLog = new DisbursementStatusLog();
            $this->_db->beginTransaction();
            try {
                foreach($disbursementDetails as $result){
                    $dataArr=array();
                    $dataArr['disbursement_batch_id'] =  $result['id'];
                    $dataArr['status_type'] = 'payment';
                    $dataArr['status'] =  $data['update_status'];
                    $dataArr['note'] =  $data['remarks'];
                    $dataArr['ttum_file_id'] =  $result['ttum_file_id'];
                    $dataArr['by_ops_id'] =  $user->id;
                    $dataArr['date_created'] = new Zend_Db_Expr('NOW()');
                    $disbursementStatusLog->insertLogData($dataArr);
                }
                $batchupdateArr = array('payment_status' => $data['update_status']);
                if($data['update_status']=="pending"){
                    $batchupdateArr['ttum_file_id']=NULL;
                }elseif($data['update_status']=="processed"){
		    
		    $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
		    $card_number = new Zend_Db_Expr("AES_DECRYPT(`bc`.`card_number`,'".$decryptionKey."') as card_number");
	
                    $select = $this->_db->select();
                    $select->from(DbTable::TABLE_BOI_DISBURESEMENT_BATCH.' as bd', array('id','amount','currency','batch_name'));
                    $select->joinleft(DbTable::TABLE_BOI_CORP_CARDHOLDER.' as bc','bd.product_customer_id=bc.id', array("id as cardholder_id","product_id","boi_customer_id","customer_master_id",$card_number,"member_id","status","debit_mandate_amount"));
                    $select->joinleft(DbTable::TABLE_BOI_CUSTOMER_PURSE.' as bcp','bc.boi_customer_id=bcp.boi_customer_id AND bc.product_id=bcp.product_id', array("purse_master_id","id as customer_purse_id"));
                    $select->where('bd.status = ?', STATUS_ACTIVE);
                    $select->where('bd.id IN(?)', $ids);
                    $select->where('bc.id IS NOT NULL');
                    $rs = $this->_db->fetchAll($select);
                    foreach($rs as $result){
                        $dataArr=array();
                        if($result['status']==STATUS_ACTIVE){
                            $dataArr['type'] = "wlt";    
                        }elseif($result['status']==STATUS_ACTIVATED){
                            $dataArr['type'] = "act";    
                        }else{
                            $this->_db->update(DbTable::TABLE_BOI_DISBURESEMENT_BATCH, array('failed_reason'=>'Cardholder status is not valid.'), "id = ".$result['id']);
                            continue;
                        }
                        $dataArr['customer_master_id'] = $result['customer_master_id'];
                        $dataArr['cardholder_id'] = $result['cardholder_id'];
                        $dataArr['txn_type'] = TXNTYPE_BOI_CORP_CORPORATE_LOAD;
                        $dataArr['load_channel'] = BY_OPS;
                        $dataArr['customer_purse_id'] = $result['customer_purse_id'];
                        $dataArr['purse_master_id'] = $result['purse_master_id'];
                        if($result['card_number']){
                            $dataArr['txn_identifier_type'] = CORP_WALLET_TXN_IDENTIFIER_CN;    
                        }elseif($result['member_id']){
                            $dataArr['txn_identifier_type'] = CORP_WALLET_TXN_IDENTIFIER_MI;    
                        }
                        
                         $dataArr['card_number'] = $result['card_number'];
                         $dataArr['member_id'] = $result['member_id'];
                         $dataArr['amount'] = $result['debit_mandate_amount'];
                         $dataArr['currency'] = $result['currency'];
                         $dataArr['narration'] = '';
                         $dataArr['wallet_code'] = strtoupper('BCW710');
                         $dataArr['txn_no'] = 0;
                         $dataArr['card_type'] = 'n';
                         $dataArr['mode'] = 'cr';
                         $dataArr['batch_name'] = $result['batch_name'];
                         $dataArr['product_id'] = $result['product_id'];
                         $dataArr['status'] = STATUS_PENDING;
                         $dataArr['date_created'] = new Zend_Db_Expr('NOW()');
                         $loadRequestId = $this->insertLoadrequest($dataArr);
                         $this->_db->update(DbTable::TABLE_BOI_DISBURESEMENT_BATCH, array('load_request_id'=>$loadRequestId), "id = ".$result['id']);
                    }    
                    //echo "<pre>";print_r($rs); exit;
                }
                $this->_db->update(DbTable::TABLE_BOI_DISBURESEMENT_BATCH, $batchupdateArr, "id IN(".$data['update_ids'].")");
                $this->_db->commit();
                return 'updated';
            } catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $this->_db->rollBack();
                return 'error';
            }
    }
    
    public function insertLoadrequest($dataArr){
            $baseTxn = new BaseTxn();
            $user = Zend_Auth::getInstance()->getIdentity();
            $txnCode = $baseTxn->generateTxncode();
            $data = array(
                    'customer_master_id' => $dataArr['customer_master_id'],
                    'cardholder_id' => $dataArr['cardholder_id'],
                    'customer_purse_id' => $dataArr['customer_purse_id'],
                    'txn_type' => $dataArr['txn_type'],
                    'load_channel' => $dataArr['load_channel'],
                    'purse_master_id' =>  $dataArr['purse_master_id'],
                    'txn_identifier_type' => $dataArr['txn_identifier_type'],
                    'type' => $dataArr['type'],
                    'card_number' => $dataArr['card_number'],
                    'member_id' => $dataArr['member_id'],
                    'amount' => $dataArr['amount'],
                    'amount_available' => 0,
                    'amount_used' => 0,
                    'amount_cutoff' => 0,
                    'currency' => $dataArr['currency'],
                    'narration' => $dataArr['narration'],
                    'wallet_code' => strtoupper($dataArr['wallet_code']),
                    'txn_no' => $dataArr['txn_no'],
                    'card_type' => $dataArr['card_type'],
                    'corporate_id' => 0,
                    'mode' => $dataArr['mode'],
                    'txn_code' => $txnCode,
                    'ip' => $this->formatIpAddress(Util::getIP()),
                    'by_ops_id' =>  $user->id,
                    'batch_name' => $dataArr['batch_name'],
                    'product_id' => $dataArr['product_id'],
                    'status' =>$dataArr['status'],
                    'date_created' => new Zend_Db_Expr('NOW()'),
                );
                if(($data['card_number'] != 0) &($data['card_number'] != '')){
		    $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
		    $data['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$data['card_number']."','".$encryptionKey."')"); 
		}
                $this->insert($data);
                $loadRequestId = $this->_db->lastInsertId(DbTable::TABLE_BOI_CORP_LOAD_REQUEST, 'id');
                return $loadRequestId;
        
    }
    public function insertLoadDetail($param = array()) {
        
        $param['status'] = STATUS_SUCCESS;
        $param['date_created'] = new Zend_Db_Expr('NOW()');
        $this->_db->insert(DbTable::TABLE_BOI_CORP_LOAD_REQUEST_DETAIL, $param);

        return TRUE;
        
    }
    
    public function mapLoadTxn($arrTxn) {
        $select = $this->_select()
                ->where('product_id = ?', $arrTxn['product_id'])
                ->where('customer_master_id = ?', $arrTxn['customer_master_id'])
                ->where('purse_master_id = ?', $arrTxn['purse_master_id'])
                ->where('amount_available > 0')
                ->where('status = ?', STATUS_LOADED)
                ->order('id ASC');
        $rs = $this->fetchAll($select);
        if($rs){
             $detailArr = array(
                            'product_id' => $arrTxn['product_id'],
                            'txn_processing_id' => $arrTxn['txn_processing_id'],
                            'txn_code' => $arrTxn['txn_code'],
                            'txn_type' => TXNTYPE_CORP_AUTH_TXN_PROCESSING,
                            'amount' => 0,
                            'load_request_id' => 0
                        );
             $txnAmount = $arrTxn['amount'];
            foreach($rs as $row){
                if($txnAmount > 0) {
                    if($row['amount_available'] >= $txnAmount) {
                        $detailArr['amount'] = $txnAmount;
                        $detailArr['load_request_id'] = $row['id'];
                        $this->insertLoadDetail($detailArr);   

                        $updLoadArr = array(
                            'amount_available' => new Zend_Db_Expr("amount_available - " . $detailArr['amount']),
                            'amount_used' => new Zend_Db_Expr("amount_used + " . $detailArr['amount'])
                                );
                        $this->updateLoadRequests($updLoadArr, $row['id']);
                    } 
                    else // txnamt > available
                    {

                        $detailArr['amount'] = $row['amount_available'];
                        $detailArr['load_request_id'] = $row['id'];

                        $this->insertLoadDetail($detailArr);   

                        $updLoadArr = array(
                            'amount_available' => new Zend_Db_Expr("amount_available - " . $detailArr['amount']),
                            'amount_used' => new Zend_Db_Expr("amount_used + " . $detailArr['amount'])
                                );
                        $this->updateLoadRequests($updLoadArr, $row['id']);


                    }
                    $txnAmount -= $detailArr['amount'];
                }
            }
        }
    }
    
    public function getDisdursementStatus($page = 1, $data = array(), $paginate = NULL, $force = FALSE) {
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BOI_DISBURESEMENT_BATCH.' as bd', array("disbursement_number",'*'));
        $select->joinleft(DbTable::TABLE_BOI_CORP_LOAD_REQUEST.' as req','bd.load_request_id=req.id', array("req.status as load_status","req.date_load","txn_identifier_type"));
        $select->joinleft(DbTable::TABLE_BOI_CORP_CARDHOLDER.' as bc','bd.product_customer_id=bc.id', array("debit_mandate_amount"));
        //echo $select; exit; 
        if(!empty($data['disbursement_number'])){
            $disbursementNumbers = explode(",",$data['disbursement_number']);
            $select->where('bd.disbursement_number IN( ? )', $disbursementNumbers);
        }
        if(count($data['payment_status'])){
            //$bucketIds = explode(",",$data['bucket']);
            $select->where('bd.payment_status IN( ? )', $data['payment_status']);    
        }
        if(!empty($data['aadhar_no'])){
            $select->where('bd.aadhar_no = ?', $data['aadhar_no']);    
        }
        if(!empty($data['account_number'])){
            $select->where('bd.account_number = ?', $data['account_number']);    
        }
        if(!empty($data['tp_name'])){
            $select->where('bc.training_partner_name = ?', $data['tp_name']);
            $select->where('bc.id IS NOT NULL');
        }
       
        $select->where('bd.status = ?', STATUS_ACTIVE);
        $select->order('bd.id ASC');
        //echo $select; exit; 
        return $this->_db->fetchAll($select);
    }
    
    public function getDisdursementCardLoad($page = 1, $data = array(), $paginate = NULL, $force = FALSE) {
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`bc`.`card_number`,'".$decryptionKey."') as card_number"); 
        $from = isset($data['from']) ? $data['from'] : '';
        $to = isset($data['to']) ? $data['to'] : '';
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BOI_DISBURESEMENT_BATCH.' as bd', array("disbursement_number",'*'));
        $select->joinleft(DbTable::TABLE_BOI_CORP_LOAD_REQUEST.' as req','bd.load_request_id=req.id', array("req.status as load_status","req.date_load","req.date_created as load_date_created","req.batch_name as load_batch_name","txn_identifier_type",'failed_reason as load_failed_reason','status as load_status'));
        $select->joinleft(DbTable::TABLE_BOI_CORP_CARDHOLDER.' as bc','bd.product_customer_id=bc.id', array("debit_mandate_amount",$card_number,"member_id","cust_id"));
        //echo $select; exit; 
        if(!empty($data['disbursement_number'])){
            $disbursementNumbers = explode(",",$data['disbursement_number']);
            $select->where('bd.disbursement_number IN( ? )', $disbursementNumbers);
        }
        if(count($data['payment_status'])){
            //$bucketIds = explode(",",$data['bucket']);
            $select->where('bd.payment_status IN( ? )', $data['payment_status']);    
        }
        if(!empty($data['aadhar_no'])){
            $select->where('bd.aadhar_no = ?', $data['aadhar_no']);    
        }
        if(!empty($data['account_number'])){
            $select->where('bd.account_number = ?', $data['account_number']);    
        }
        if ($from != '' && $to != ''){
            $select->where("req.date_created >= '" . $from . "'");
            $select->where("req.date_created <= '" . $to . "'");
        }
       
        $select->where('bd.status = ?', STATUS_ACTIVE);
        $select->where('bd.load_request_id IS NOT NULL');
        $select->order('bd.id ASC');
        //echo $select; exit; 
        return $this->_db->fetchAll($select);
    }
    
    public function getNote($id){
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BOI_DISBURESEMENT_STATUS_LOG, array('note'));
        $select->where('disbursement_batch_id = ?', $id);
        $select->order('date_created DESC');
        $select->limit(1);
        $rs = $this->_db->fetchRow($select); 
        if($rs['note'])
            return $rs['note'];
        else
            return '';
    }
    
}
