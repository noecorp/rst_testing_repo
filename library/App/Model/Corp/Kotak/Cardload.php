<?php

/**
 * Model that manages ratnakar cardloads
 *
 * @package Operation_Models
 * @copyright transerv
 */
class Corp_Kotak_Cardload extends Corp_Kotak {

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
    protected $_name = DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST;
    const DATA_ELEMENT_BALANCE = '54';

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
        $select->from(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST_BATCH, array(
            'id', 'product_id', 'txn_identifier_type', $card_number, 'member_id', 'employee_id', 'amount', 'currency', 'narration', 'wallet_code', 'txn_no', 'card_type', 'mode', 'corporate_id', 'by_corporate_id', 'by_ops_id', 'ip', 'batch_name', 'date_created', 'date_updated', 'failed_reason', 'upload_status'
        ));
        $select->where('upload_status = ?', STATUS_TEMP);
        $select->where('batch_name = ?', $batchName);
        $select->order('id ASC');
        return $this->_db->fetchAll($select);
    }

    /*
     * showFailedPendingCardloadDetails , show load requests from batch table which have upload status as failed
     */
    public function showFailedPendingCardloadDetails($batchName) {
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST_BATCH, array(
            'id', 'product_id', 'txn_identifier_type', $card_number, 'member_id', 'employee_id', 'amount', 'currency', 'narration', 'wallet_code', 'txn_no', 'card_type', 'mode', 'corporate_id', 'by_corporate_id', 'by_ops_id', 'ip', 'batch_name', 'date_created', 'date_updated', 'failed_reason', 'upload_status'
        ));
        $select->where('upload_status = ?', STATUS_FAILED);
        $select->where('batch_name = ?', $batchName);
        $select->order('id ASC');
        return $this->_db->fetchAll($select);
    }
    
    /*
     * insertLoadrequestBatch , insert data from file into the batch table
     */

    public function insertLoadrequestBatch($dataArr, $batchName, $status) {
        $user = Zend_Auth::getInstance()->getIdentity();
        
	$validateArr = array();
        $failedReason = '';
        
        $validateArr['txn_identifier_type'] = $dataArr[0];
        $validateArr['card_number'] = $dataArr[1];
        $validateArr['amount'] = $dataArr[2];
        $validateArr['wallet_code'] = $dataArr[5];
        $validateArr['card_type'] = $dataArr[7];
        $validateArr['product_id'] = $dataArr['product_id'];

        $valid = $this->isValid($validateArr);

        if (!$valid) {
            $errMsg = $this->getError();
            $errorMsg = empty($errMsg) ? 'details invalid' : $errMsg;

            $status = STATUS_FAILED;
            $failedReason = $errorMsg;
        } 

        try {
            $data = array(
                'txn_identifier_type' => strtolower($dataArr[0]),
                'card_number' => (strtolower($dataArr[0]) == KOTAK_AMUL_WALLET_TXN_IDENTIFIER_CN) ? $dataArr[1] : 0,
                'member_id' => (strtolower($dataArr[0]) == KOTAK_AMUL_WALLET_TXN_IDENTIFIER_MI) ? $dataArr[1] : 0,
		'employee_id' =>  (strtolower($dataArr[0]) == KOTAK_AMUL_WALLET_TXN_IDENTIFIER_EI) ? $dataArr[1] : 0,
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
                'failed_reason' => $failedReason,
                'upload_status' => $status,
                'date_created' => new Zend_Db_Expr('NOW()')
            );
            if(!empty($user->corporate_code)){
                    $data['by_ops_id']= 0;
                    $data['by_corporate_id']= $user->id;
                 }
            
            /*
            * Encryption of Card Number
            */
	    if(($data['card_number'] != 0) &($data['card_number'] != '')){
		$encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
		$data['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$data['card_number']."','".$encryptionKey."')"); 
	    }
	    
            $this->_db->insert(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST_BATCH, $data);

            return TRUE;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            echo $e->getMessage();
        }
    }

    /*
     * insertLoadrequestBatch , insert data from file into the batch table
     */

    public function insertLoadrequestForLog($dataArr) {
        $user = Zend_Auth::getInstance()->getIdentity();

        try {
            
            $data = array(
                'txn_identifier_type' => $dataArr['txn_identifier_type'],
                'card_number' => $dataArr['card_number'],
                'member_id' => $dataArr['member_id'],
                'amount' => $dataArr['amount'],
                'currency' => $dataArr['currency'],
                'narration' => $dataArr['narration'],
                'wallet_code' => $dataArr['wallet_code'],
                'txn_no' => $dataArr['txn_no'],
                'card_type' => $dataArr['card_type'],
                'corporate_id' => $dataArr['corporate_id'],
                'mode' => TXN_MODE_CR,
                'ip' => $this->formatIpAddress(Util::getIP()),
                'by_ops_id' => $user->id,
                'batch_name' => '',
                'product_id' => $dataArr['product_id'],
                'upload_status' => STATUS_PASS,
                'date_created' => new Zend_Db_Expr('NOW()')
            );
            if(!empty($user->corporate_code)){
                    $data['by_ops_id']= 0;
                    $data['by_corporate_id']= $user->id;
                 }
                 
                /*
                * Encryption of Card Number
                */
		if(($data['card_number'] != 0) &($data['card_number'] != '')){
		    $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
		    $data['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$data['card_number']."','".$encryptionKey."')"); 
		}
                     
            $this->_db->insert(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST_BATCH, $data);
            return $this->_db->lastInsertId(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST_BATCH);
            
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            echo $e->getMessage();
        }
    }
    /*
     * bulkAddCardload add card load request from batch table
     */

    public function bulkAddCardload($idArr, $batchName) {
        if (empty($idArr)) {
            throw new Exception('Data missing for add cardholder');
        }
        $custModel = new Corp_Kotak_Customers();
        $custPurseModel = new Corp_Kotak_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $productModel = new Products();
        $baseTxn = new BaseTxn();
        $user = Zend_Auth::getInstance()->getIdentity();
        $str = '';
	//echo "<pre>"; print_r($idArr); exit;
        try {
            // Foreach selected id value
            foreach ($idArr as $id) {
                
                //Decryption of Card Number and CRN
                $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $card_number = new Zend_Db_Expr("AES_DECRYPT(`kclrb`.`card_number`,'".$decryptionKey."') as card_number");
               
                $select = $this->_db->select()
                        ->from(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST_BATCH. " as kclrb", array('id', 'product_id', 'txn_identifier_type', $card_number, 'member_id', 'employee_id', 'amount', 'currency', 'narration', 'wallet_code', 'txn_no', 'card_type', 'mode', 'corporate_id', 'by_corporate_id', 'by_ops_id', 'ip', 'batch_name', 'date_created', 'date_updated', 'failed_reason'))
                        ->where("id =?", $id);

                $dataArr = $this->_db->fetchRow($select);

                $searchArr = array(
                    'member_id' => ($dataArr['member_id'] != 0) ? $dataArr['member_id'] : '',
                    'card_number' => ($dataArr['card_number'] != 0) ? $dataArr['card_number'] : '',
		    'employee_id' => (!empty($dataArr['employee_id'])) ? $dataArr['employee_id'] : '',
                    'status' => STATUS_ACTIVE); 

                $cardholderDetails = $custModel->getCardholderInfo($searchArr);
                
		if(($dataArr['member_id']<=0 || $dataArr['member_id']=='') && ( $dataArr['card_number'] <=0 || $dataArr['card_number']=='') &&  $dataArr['employee_id']=='')
                {
                    $cardholderDetails = '';
                    $loadStatus = STATUS_FAILED;
                    $failedReason = 'Invalid card number or member id';
                    $dateFailed = new Zend_Db_Expr('NOW()');
                    $dateLoad = new Zend_Db_Expr('NOW()');   
                }
		elseif(!isset($cardholderDetails->id) || $cardholderDetails->id == '')
                {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = 'Active Cardholder not found';
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
						
			case STATUS_ACTIVATION_PENDING:
                                                 $loadStatus = STATUS_FAILED;
                                                 $failedReason = 'Cardholder Actiation Pending';
                                                 $dateFailed = new Zend_Db_Expr('NOW()');  
                                                 $dateLoad = new Zend_Db_Expr('NOW()');
                                                 break;
			
			case STATUS_ECS_PENDING:
                                                 $loadStatus = STATUS_FAILED;
                                                 $failedReason = 'Cardholder Registration pending with ECS';
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
                $ratCustomerId = ($cardholderDetails->kotak_customer_id != '') ? $cardholderDetails->kotak_customer_id : 0;
                $customerPurseId = 0;
                // Master Purse id
                
                $prodInfo = $productModel->getProductInfo($dataArr['product_id']);
                if($prodInfo['const'] == PRODUCT_CONST_KOTAK_AMULWB) {
                    $product = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_AMUL);
                    $pursecode = $product->purse->code->corporatewallet;
                } elseif($prodInfo['const'] == PRODUCT_CONST_KOTAK_AMULGUJ) {
                    $product = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_AMULGUJ);
                    $pursecode = $product->purse->code->corporatewallet;
                } elseif($prodInfo['const'] == PRODUCT_CONST_KOTAK_SEMICLOSE_GPR) {
                    $product = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_SEMICLOSE_GPR);
                    $pursecode = $product->purse->code->corporatewallet;
                } elseif($prodInfo['const'] == PRODUCT_CONST_KOTAK_OPENLOOP_GPR) {
                    $product = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_OPENLOOP_GPR);
                    $pursecode = $product->purse->code->corporatewallet;
                }
                
                $masterPurseDetails = $masterPurseModel->getPurseIdByPurseCode($pursecode);
                
                // Purse id 
                if($ratCustomerId > 0) { 
                    $purseDetails = $custPurseModel->getCustPurseDetails(array('kotak_customer_id' => $ratCustomerId, 'purse_master_id' => $masterPurseDetails['id']));
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
                    }elseif($dataArr['amount'] <= 0){
			$loadStatus = STATUS_FAILED;
			$failedReason = 'Invalid Amount Value';
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
                $loadChanel = (!empty($user->corporate_code))? BY_CORPORATE: BY_OPS;
                $txnCode = $baseTxn->generateTxncode();
                $data = array(
                    'customer_master_id' => $customerMasterId,
                    'cardholder_id' => $cardholderId,
                    'customer_purse_id' => $customerPurseId,
                    'txn_type' => TXNTYPE_KOTAK_CORP_CORPORATE_LOAD,
                    'load_channel' => $loadChanel,
                    'purse_master_id' => $masterPurseDetails['id'],
                    'txn_identifier_type' => $dataArr['txn_identifier_type'],
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
                    'by_corporate_id' => $dataArr['by_corporate_id'],
                    'batch_name' => $dataArr['batch_name'],
                    'product_id' => $dataArr['product_id'],
                    'status' => $loadStatus,
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'failed_reason' => $failedReason,
                    'date_failed' => $dateFailed,
                    'date_load' => $dateLoad

                );
                
                /*
                * Encryption of Card Number
                */ 
                $cardNumber = isset($data['card_number'])?trim($data['card_number']):'';     
                if($cardNumber!=''){
                     $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                     $data['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')");
                }
                
                $this->insert($data);
                $loadRequestId = $this->_db->lastInsertId(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST, 'id');
                $updateArr = array('upload_status' => STATUS_PASS);
                $this->_db->update(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST_BATCH, $updateArr, "id= $id");

                $this->_db->commit();
            }// END of foreach loop
            $notInid = implode(",", $idArr);
            $rejectedArr = array('upload_status' => STATUS_REJECTED);
            $this->_db->update(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST_BATCH, $rejectedArr, "id NOT IN ($notInid) AND batch_name = '$batchName'");
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

        $loadRequests = $this->getLoadRequests(array('limit' => KOTAK_CORPORATE_LOAD_LIMIT, 'status' => STATUS_PENDING));
        $count = count($loadRequests);
        $retResp = array('loaded' => 0, 'not_loaded' => 0, 'exception' => array());
        if ($count > 0) {
            $ecsApi = new App_Socket_ECS_Corp_Transaction();
            $baseTxn = new BaseTxn();
            $m = new \App\Messaging\Corp\Kotak\Operation();
            $cardholderModel = new Corp_Kotak_Customers();
            $custPurseModel = new Corp_Kotak_CustomerPurse();
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
                        'corporate_id' => $val['by_corporate_id']
                    );
                    //$flgValidate = $baseTxn->chkAllowKotakGPRCardLoad($validator);
                    $flgValidate = $baseTxn->chkAllowKotakCorporateCardLoad($validator);
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
                        $cardholderArray['cardNumber'] = $val['card_number'];
                        $ecsApi2 = new App_Api_ECS_Transactions();
                        try {
                            $resp = $ecsApi2->balanceInquiry($cardholderArray);
                            if($resp) {
                                $res = $ecsApi2->getLastResponse();
                                $balVal = $res->balanceInquiryList->availablebalance;
                            } else {
                                $balVal = FALSE;
                            }
                        } catch (Exception $e) {
                            $balVal = FALSE;
                            App_Logger::log($e->getMessage());
                        }
                        
                        

                        if($apiResp === TRUE){
                            
                            $updateArr = array(
                                    'amount_available' => $val['amount'],
                                    'amount_used' => 0,
                                    'amount_cutoff' => 0,
                                    'txn_load_id' => $ecsApi->getISOTxnId(),
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
                              'corporate_id' => $val['by_corporate_id']
                            );
                            $baseTxn->successKotakCorporateCardLoad($baseTxnParams);
                            //$baseTxn->successCorpKotakCorporateCardLoad($baseTxnParams);
                            
                            $objProducts = new Products();
                            $product_details = $objProducts->findById($val['product_id']);
                            $product_const = $product_details['const'];
                            
                            if($product_const == PRODUCT_CONST_KOTAK_AMULWB || $product_const == PRODUCT_CONST_KOTAK_AMULGUJ)
                            {
                                $product_nm = KOTAK_AMUL_PRODUCT;
                            }
                            if($product_const == PRODUCT_CONST_KOTAK_SEMICLOSE_GPR || $product_const == PRODUCT_CONST_KOTAK_OPENLOOP_GPR)
                            {
                                $product_nm = KOTAK_GPR_PRODUCT;
                            }
                            
                            
                            $cardholder = $cardholderModel->findById($val['cardholder_id']);
                            if($cardholder['mobile'] != '') {
                            //$custPurse = $custPurseModel->getCustBalance($cardholder['kotak_customer_id']);
                                $userData = array('last_four' =>substr($val['card_number'], -4),
                                    'product_name' => $product_nm,
                                    'amount' => $val['amount'],
                                    'balance' => $balVal, //Util::numberFormat($balVal / 100), //$custPurse['sum'],
                                    'mobile' => $cardholder['mobile'],
                                );
                               $resp = $m->cardLoad($userData);
                            }
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
                  } // END IF Condition
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
     * getLoadRequests will return the medi assist load requests
     */

    public function getLoadRequests($param) {
        $status = isset($param['status']) ? $param['status'] : '';
        $loadChannel = isset($param['load_channel']) ? $param['load_channel'] : '';
        $batchName = isset($param['batch_name']) ? $param['batch_name'] : '';
        $purseMasterId = isset($param['purse_master_id']) ? $param['purse_master_id'] : '';
        $limit = isset($param['limit']) ? $param['limit'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $employer_name = isset($param['employer_name']) ? $param['employer_name'] : '';
        $employer_loc = isset($param['employer_loc']) ? $param['employer_loc'] : '';
        $product = isset($param['product_id']) && $param['product_id'] > 0? $param['product_id'] : '';
        
        //Decryption of Card Number and CRN
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`l`.`card_number`,'".$decryptionKey."') as card_number");
        
        $select = $this->select()
          ->setIntegrityCheck(false)
          ->from($this->_name ." as l",array('id', 'product_id', 'customer_master_id', 'cardholder_id', 'purse_master_id', 'customer_purse_id', 'txn_type', 'load_channel', 'txn_identifier_type', $card_number, 'member_id', 'amount', 'amount_available', 'amount_used', 'amount_cutoff', 'currency', 'narration', 'wallet_code', 'txn_no', 'card_type', 'corporate_id', 'mode', 'txn_code', 'by_agent_id', 'by_corporate_id', 'by_ops_id', 'ip', 'batch_name', 'date_created', 'date_load', 'date_failed', 'date_cutoff', 'txn_load_id', 'failed_reason', 'date_updated', 'status'));
        $select->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "l.product_id  = p.id",array('p.name as product_name'))
                ->join(DbTable::TABLE_BANK . " as bank", "p.bank_id = bank.id",array('name as bank_name'))
                ->joinLeft(DbTable::TABLE_KOTAK_CORP_CARDHOLDER." as kcc", "kcc.id=l.cardholder_id", array());
        if ($product != '') {
            $select->where('l.product_id = ?', $product);
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
        if ($employer_name != '') {
            $select->where("kcc.employer_name LIKE  '%". $employer_name. "%'");
        }
        if ($employer_loc != '') {
            $select->where("kcc.comm_city LIKE  '%". $employer_loc. "%'");
        }
        if($status != ''){
	    $select->where('l.status = ?', $status);
	}
        if ($limit != '') {
            $select->limit($limit);
        }
        return $this->fetchAll($select);
    }

    public function getDuplicateLoadRequest($batchName, $cardholderId) {
        $select = $this->select()
            ->from(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST, array("count(*) as num"))
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
                    $retData[$key]['product_name']  = $data['product_name'];
                    $retData[$key]['bank_name']  = $data['bank_name'];
                    $retData[$key]['txn_identifier_type']  = $data['txn_identifier_type'];
                    $retData[$key]['partner_ref_no']  = '';
                    $retData[$key]['card_number']          = Util::maskCard($data['card_number']);
                    $retData[$key]['member_id']    = $data['member_id'];
                    $retData[$key]['amount']      = $data['amount'];
		    $retData[$key]['amount_cutoff']      = $data['amount_cutoff']; 
                    $retData[$key]['currency']        = $data['currency'];
                    $retData[$key]['narration'] = $data['narration'];
                    $retData[$key]['wallet_code'] = $data['wallet_code'];
                    $retData[$key]['txn_no'] = strtoupper($data['txn_no']);
                    $retData[$key]['card_type']      = strtoupper($data['card_type']); 
                    $retData[$key]['corporate_id']      = strtoupper($data['corporate_id']); 
                    $retData[$key]['mode']      = strtoupper($data['mode']); 
                    $retData[$key]['txn_code']      = $data['txn_code']; 
                    $retData[$key]['failed_reason']      = $data['failed_reason']; 
                    $retData[$key]['status']      = $data['status']; 
                    $retData[$key]['load_date']      = $data['date_load']; 
          }
        }
        
        return $retData;
         
     }
    /*
     * Stats Daily - loads
     */

    public function getStatsDaily($customerPurseId, $curDate, $statusStr) {
        $select = $this->select()
                ->from(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST, array('count(*) as count', 'sum(amount) as total'))
                ->where('customer_purse_id=?', $customerPurseId);
        if ($statusStr != '') {
            $select->where("status IN ($statusStr)");
        }
        $select->where("DATE(date_created) = '" . $curDate . "'")
                ->group("customer_purse_id"); 
        $row = $this->fetchRow($select);
        return $row;
    }

    /*
     * Stats Duration - loads
     */

    public function getStatsDuration($customerPurseId, $startDate, $endDate, $statusStr) {
        $select = $this->select()
                ->from(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST, array('count(*) as count', 'sum(amount) as total'))
                ->where('customer_purse_id=?', $customerPurseId);
        if ($statusStr != '') {
            $select->where("status IN ($statusStr)");
        }
        $select->where("DATE(date_created) BETWEEN '" . $startDate . "' AND '" . $endDate . "'")
                ->group("customer_purse_id"); 
        $row = $this->fetchRow($select);
        return $row;
    }
    
    public function getBatchName($masterPurseId){
        $select = $this->select()
                ->from(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST, array('batch_name'))
                ->distinct(TRUE)
                ->where('purse_master_id=?', $masterPurseId); 
        $row = $this->fetchAll($select);
        return $row;
    }
    
    public function checkFilename($fileName,$product_id = FALSE) {
        
        $select = $this->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST, array('id'));
        $select->where("batch_name =?", $fileName);
        if($product_id != '')
        {
            $select->where("product_id =?", $product_id);
        }
        $rs = $this->fetchRow($select);
        if (empty($rs)) {
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    
    public function isRecordPending($loadReqId) {
        $select = $this->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST, array('id'));
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
            if((isset($data['card_number'])) && ($data['card_number'] != '') ) {
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
            if((isset($data['card_number'])) && ($data['card_number'] != '') ) {
                $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $data['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$data['card_number']."','".$encryptionKey."')");
            }
            $this->_db->update(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST_BATCH, $data, 'batch_name="'.$batch.'"');
            return true;
        }
        return false;
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
        $select->from(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST . " as load", array('date_load', $card_number, 'member_id', 'txn_type', 'amount_cutoff', 'amount', 'status', 'txn_no', 'txn_code', 'load_channel', 'narration'));
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_KOTAK_CORP_CARDHOLDER . " as holder", "load.cardholder_id = holder.id",array('card_pack_id'));
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
            $arrReport[$i]['card_number'] = Util::maskCard($rsLoad[$i]['card_number']);
            $arrReport[$i]['card_pack_id'] = $rsLoad[$i]['card_pack_id'];
            $arrReport[$i]['member_id'] = $rsLoad[$i]['member_id'];
            $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[$rsLoad[$i]['txn_type']];
            $arrReport[$i]['status'] = $STATUS[$rsLoad[$i]['status']];
            $arrReport[$i]['wlt_code'] = $rsLoad[$i]['wlt_code'];
            $arrReport[$i]['mode'] = '-';
            $arrReport[$i]['wallet_hr_dr'] = ($rsLoad[$i]['txn_type'] == TXNTYPE_KOTAK_CORP_CORPORATE_LOAD) ? $rsLoad[$i]['amount_cutoff'] : 0;
            $arrReport[$i]['wallet_hr_cr'] = ($rsLoad[$i]['txn_type'] == TXNTYPE_KOTAK_CORP_CORPORATE_LOAD) ? $rsLoad[$i]['amount'] : 0;
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
        
        return $arrReport;
        
    } 
    public function getWalletTrialBalance($param) 
    {
        $retArr = array();
                return $retArr;
    }
    
    
    /*
     * getSuccessfullLoads will return the successfull Load reuqests
     */

    public function getSuccessfulLoads($param) {
        $type = isset($param['txn_type']) ? $param['txn_type'] : '';
        $purseMasterId = isset($param['purse_master_id']) ? $param['purse_master_id'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $select = $this->select() 
                ->from(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST, array('count(*) as count', 'sum(amount) as total','date_load'));
        
        if ($type != '') {
            $select->where('txn_type = ?', $type);
        }
        if ($purseMasterId != '') {
            $select->where('purse_master_id = ?', $purseMasterId);
        }
        if ($from != '' && $to != ''){
            $select->where("date_load >=  '" . $from . "'");
            $select->where("date_load <= '" . $to . "'");
            $select->where("status IN  ('" . STATUS_LOADED . "', '".STATUS_CUTOFF."')");
        }
        $select->group("purse_master_id");
       
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
                ->from(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST, array('count(*) as count', 'sum(amount_cutoff) as reversal_total','date_cutoff'));
        
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
    
    /*
     * Stats Daily - loads
     */

    public function getCustomerProductStatsDaily($customerMasterId, $productId, $curDate, $statusStr) {
        $select = $this->select()
                ->from(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST, array('count(*) as count', 'sum(amount) as total'))
                ->where('customer_master_id=?', $customerMasterId)
                ->where('product_id=?', $productId);
        if ($statusStr != '') {
            $select->where("status IN ($statusStr)");
        }
        $select->where("DATE(date_created) = '" . $curDate . "'")
                ->group("customer_master_id");
        $row = $this->fetchRow($select);
        return $row;
    }

    /*
     * Stats Duration - loads
     */

    public function getCustomerProductStatsDuration($customerMasterId, $productId, $startDate, $endDate, $statusStr) {
        $select = $this->select()
                ->from(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST, array('count(*) as count', 'sum(amount) as total'))
                ->where('customer_master_id=?', $customerMasterId)
                ->where('product_id=?', $productId);
        if ($statusStr != '') {
            $select->where("status IN ($statusStr)");
        }
        $select->where("DATE(date_created) BETWEEN '" . $startDate . "' AND '" . $endDate . "'")
                ->group("customer_master_id");
        $row = $this->fetchRow($select);
        return $row;
    }
   
    public function getAgentLoadsAndReversal($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $txnType = isset($param['txn_type']) ? $param['txn_type'] : '';
        $onDate = isset($param['on_date']) ? $param['on_date'] : FALSE;
        $status = isset($param['status']) ? $param['status'] : '';
       
        
        if ($agentId > 0) {
 
            $select = $this->select()
                    ->from($this->_name, array('*'));
            if($agentId != ''){
            $select->where('by_agent_id = ?', $agentId);
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
            $select->where("txn_type IN ('". TXNTYPE_KOTAK_CORP_CORPORATE_LOAD."','".TXNTYPE_KOTAK_CORP_GPR_LOAD."','".TXNTYPE_CARD_RELOAD."')");  
            }
            if ($onDate) {
                $date = isset($param['date']) ? $param['date'] : '';
                $select->where('DATE(date_created) =?', $date);
            } else {
                $fromDate = isset($param['from']) ? $param['from'] : '';
                $toDate = isset($param['to']) ? $param['to'] : '';
                $select->where('date_created >= ?', $fromDate);
                $select->where('date_created <= ?', $toDate);
            }
            return $this->fetchAll($select);
        }
        else
            return array();
    }
    
    public function getTotalLoad($param) {
        $purseId = isset($param['customer_purse_id']) ? $param['customer_purse_id'] : '';
        $txnType = isset($param['txn_type']) ? $param['txn_type'] : '';
        $onDate = isset($param['on_date']) ? $param['on_date'] : FALSE;
        $status = isset($param['status']) ? $param['status'] : '';
        $cutoff = isset($param['cutoff']) ? $param['cutoff'] : FALSE;
	$bycorporate_id = isset($param['by_corporate_id']) ? $param['by_corporate_id'] : FALSE;
        
        if ($purseId > 0 || $bycorporate_id) {
 
            $select = $this->select();
            if($cutoff){
                $select->from($this->_name, array('sum(amount_cutoff) as total_load_amount'));
            } else {
                $select->from($this->_name, array('sum(amount) as total_load_amount'));
            }
            if($purseId != ''){
		$select->where('customer_purse_id = ?', $purseId);
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
		$select->where("txn_type IN ('". TXNTYPE_KOTAK_CORP_CORPORATE_LOAD."','".TXNTYPE_CARD_RELOAD."')");  
            }
	    if ($bycorporate_id) {
		$select->where('by_corporate_id = ?', $bycorporate_id);
            }
	    
            if ($onDate) {
                $date = isset($param['date']) ? $param['date'] : '';
                $select->where('DATE(date_created) =?', $date);
            } else {
                $fromDate = isset($param['from']) ? $param['from'] : '';
                $toDate = isset($param['to']) ? $param['to'] : '';
                if($fromDate && $toDate){
		    $select->where('date_created >= ?', $fromDate);
		    $select->where('date_created <= ?', $toDate);
		}
            }
	    //echo $select; //exit;
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
		$select->where("txn_type IN ('". TXNTYPE_KOTAK_CORP_CORPORATE_LOAD."','".TXNTYPE_CARD_RELOAD."')");  
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
        $select->from(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST . ' as lr', array('lr.wallet_code','lr.amount','lr.date_load','lr.date_cutoff','lr.status','lr.amount_cutoff', 'DATE(lr.date_load) as date'));
        $select->join(DbTable::TABLE_KOTAK_CORP_CARDHOLDER . ' as c', "lr.cardholder_id = c.id", array($card_number,'c.aadhaar_no',$crn,'c.member_id','c.mobile','c.employee_id'));
        //$select->joinLeft(DbTable::TABLE_KOTAK_CUSTOMER_PURSE_CLOSING_BALANCE . ' as cb', "lr.customer_purse_id = cb.customer_purse_id and DATE(lr.date_load) = cb.date", array('cb.closing_balance'));
        $select->join(DbTable::TABLE_PRODUCTS . ' as p', "lr.product_id = p.id", array('p.name as product_name'));
        $select->joinLeft(DbTable::TABLE_CORPORATE_USER . ' as cu', "lr.by_corporate_id = cu.id", array('cu.corporate_code', 'concat(cu.first_name," ",cu.last_name) as corporate_name'));
        
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
                $retData[$key]['bank_name'] = KOTAK_BANK;
                $retData[$key]['aadhaar_no']    =   $data['aadhaar_no'];
                $retData[$key]['currency']      =   CURRENCY_INR;
                $retData[$key]['card_number']   =   Util::maskCard($data['card_number']);
                $retData[$key]['crn']           =   Util::maskCard($data['crn']);
		$retData[$key]['mobile'] = $data['mobile'];
                $retData[$key]['member_id'] = $data['member_id'];
                $retData[$key]['cust_id'] = $data['cust_id'];
                $retData[$key]['wallet_code'] = $data['wallet_code'];
                $retData[$key]['closing_balance'] = $data['closing_balance'];
                $retData[$key]['status'] = 'Active';
		$retData[$key]['corporate_code'] = $data['corporate_code'];
		$retData[$key]['corporate_name'] = $data['corporate_name'];
                $retData[$key]['report_date'] = Util::returnDateFormatted($data['date'], "d-m-Y", "Y-m-d", "-");
            }
        }

        return $retData;
    }
    
    /*
     * doCardload
     */
    
    public function doCardload($dataArr) {
         
        if (empty($dataArr)) {
            throw new Exception('Data missing for cardload');
        }
        $custModel = new Corp_Kotak_Customers();
        $custPurseModel = new Corp_Kotak_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $productModel = new Products();
        $baseTxn = new BaseTxn();
        $ecsApi = new App_Socket_ECS_Corp_Transaction();
        $m = new \App\Messaging\Corp\Kotak\Operation();
        $str = '';
        try {
            $cardholderId = $dataArr['cardholder_id'];
            $productId = $dataArr['product_id'];
            $pursecode = $dataArr['wallet_code'];

            $searchArr = array('cardholder_id' => $cardholderId, 'status' => STATUS_ACTIVE);
            $cardholderDetails = $custModel->getCardholderInfo($searchArr);
            if(!isset($cardholderDetails->id) || $cardholderDetails->id == '')
            {
                $loadStatus = STATUS_FAILED;
                $failedReason = 'Active Cardholder not found';
                $dateFailed = new Zend_Db_Expr('NOW()');   
                $dateLoad = new Zend_Db_Expr('NOW()');   
            } else {
                switch($cardholderDetails->cardholder_status)
                {
                    case STATUS_ECS_PENDING:
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
//echo $loadStatus;exit;
           
            $cardNumber = $cardholderDetails->card_number;
            $mediAssistId = $cardholderDetails->member_id;
            $customerMasterId = ($cardholderDetails->customer_master_id != '') ? $cardholderDetails->customer_master_id : 0;
            $ratCustomerId = ($cardholderDetails->kotak_customer_id != '') ? $cardholderDetails->kotak_customer_id : 0;
            $customerPurseId = 0;
            $masterPurseDetails = $masterPurseModel->getPurseIdByPurseCode($pursecode);
            if($ratCustomerId > 0) { 
                $purseDetails = $custPurseModel->getCustPurseDetails(array('kotak_customer_id' => $ratCustomerId, 'purse_master_id' => $masterPurseDetails['id']));
                $customerPurseId = (isset($purseDetails['id']) && $purseDetails['id'] > 0) ? $purseDetails['id'] : 0;
            }
            $purseMasterId = $masterPurseDetails['id'];
            $amount = 0;
            $productAmul = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_AMUL);
            $amulWalletCode = $productCop->purse->code->corporatewallet; 
            $productAmulguj = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_AMULGUJ);
            $amulgujWalletCode = $productAmulguj->purse->code->corporatewallet; 
            $productSemi = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_SEMICLOSE_GPR);
            $semiWalletCode = $productSemi->purse->code->corporatewallet;
            $productOpn = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_OPENLOOP_GPR);
            $opnWalletCode = $productOpn->purse->code->corporatewallet;
            
            
            if($loadStatus == STATUS_PENDING)
            {
                if(strtolower($dataArr['wallet_code']) != strtolower($amulWalletCode)  && strtolower($dataArr['wallet_code']) != strtolower($amulgujWalletCode)  && strtolower($dataArr['wallet_code']) != strtolower($semiWalletCode) && strtolower($dataArr['wallet_code']) != strtolower($opnWalletCode) )
                {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = 'Invalid Wallet Code';
                    $dateFailed = new Zend_Db_Expr('NOW()');  
                    $dateLoad = new Zend_Db_Expr('NOW()');
                    $amount = $dataArr['amount'];
                }
                elseif($dataArr['product_id'] != $masterPurseDetails['product_id'])
                {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = 'Invalid Wallet Code';
                    $dateFailed = new Zend_Db_Expr('NOW()');  
                    $dateLoad = new Zend_Db_Expr('NOW()');
                    $amount = $dataArr['amount'];
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
                elseif(strtolower($dataArr['card_type']) != strtolower(CORP_CARD_TYPE_NORMAL))
                {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = 'Card type Corporate ID Validation failed';
                    $dateFailed = new Zend_Db_Expr('NOW()');  
                    $dateLoad = new Zend_Db_Expr('NOW()');
                    $amount = $dataArr['amount'];
                }
            }
            //echo $dataArr['card_type'] . PHP_EOL;
            //echo $loadStatus;exit;
            
            if($amount != $dataArr['amount'])
            {
                $amount = Util::convertToPaisa($dataArr['amount']);
            }
            $txnCode = $baseTxn->generateTxncode();
            if (strtolower($dataArr['mode']) == TXN_MODE_DR) {
                $txnType = TXNTYPE_CARD_DEBIT;
                $loadStatus = STATUS_DEBITED;
            }
            else{
                $txnType = TXNTYPE_CARD_RELOAD;
            }
            
             
            $data = array(
                'customer_master_id' => $customerMasterId,
                'cardholder_id' => $cardholderId,
                'customer_purse_id' => $customerPurseId,
                'txn_type' => $txnType,
                'load_channel' => BY_API,
                'purse_master_id' => $masterPurseDetails['id'],
                'txn_identifier_type' => $dataArr['txn_identifier_type'],
                'card_number' => $cardNumber,
                'medi_assist_id' => $mediAssistId,
                'amount' => $amount,
                'amount_available' => 0,
                'amount_used' => 0,
                'amount_cutoff' => 0,
                'currency' => CURRENCY_INR_CODE,
                'narration' => $dataArr['narration'],
                'wallet_code' => strtoupper($dataArr['wallet_code']),
                'txn_no' => $dataArr['txn_no'],
                'card_type' => $dataArr['card_type'],
                'corporate_id' => $dataArr['corporate_id'],
                'mode' => $dataArr['mode'],
                'txn_code' => $txnCode,
                'ip' => '',
                'by_agent_id' => $dataArr['by_api_user_id'],
                'by_ops_id' => 0,
                'batch_name' => '',
                'product_id' => $dataArr['product_id'],
                'status' => $loadStatus,
                'date_created' => new Zend_Db_Expr('NOW()'),
                'failed_reason' => $failedReason,
                'date_failed' => $dateFailed,
                'date_load' => $dateLoad

            );
            if (strtolower($dataArr['mode']) == TXN_MODE_DR) {
                $data['txn_type'] = TXNTYPE_CARD_DEBIT;
            }
            
            /*
            * Encryption of Card Number
            */
	    if(($data['card_number'] != 0) &($data['card_number'] != '')){
		$encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
		$data['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$data['card_number']."','".$encryptionKey."')"); 
	    }
        
            $this->insert($data);
            $loadRequestId = $this->_db->lastInsertId(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST, 'id');
            $this->_db->beginTransaction();
            if($loadStatus == STATUS_PENDING) {
             
                if (strtolower($dataArr['mode']) == TXN_MODE_DR) {
                    $validator = array(
                        'load_request_id' => $loadRequestId,
                        'customer_master_id' => $customerMasterId,
                        'purse_master_id' => $purseMasterId,
                        'customer_purse_id' => $customerPurseId,
                        'amount' => $amount,
                        'agent_id' => $dataArr['by_api_user_id'],
                        'product_id' => $productId,
                    );
                   
                    $flgValidate = $baseTxn->chkAllowKotakGPRCardDebit($validator);
                    
                    if ($flgValidate) {
                      
                        $cardLoadData = array(
                            'amount' => (string) $amount,
                            'crn' => $cardNumber,
                            'agentId' => $dataArr['by_api_user_id'],
                            'transactionId' => $txnCode,
                            'currencyCode' => CURRENCY_INR_CODE,
                            'countryCode' => COUNTRY_IN_CODE
                        );
                        
                        $apiResp = $ecsApi->cardDebit($cardLoadData);
                        if ($apiResp === TRUE) {

                            $updateArr = array(
                                'amount_available' => 0,
                                'amount_used' => 0,
                                'amount_cutoff' => 0,
                                'txn_load_id' => $ecsApi->getISOTxnId(),
                                'status' => STATUS_DEBITED,
                                'date_load' => new Zend_Db_Expr('NOW()')
                            );

                            $this->updateLoadRequests($updateArr, $loadRequestId);

                            $baseTxnParams = array(
                                'txn_code' => $txnCode,
                                'customer_master_id' => $customerMasterId,
                                'product_id' => $productId,
                                'purse_master_id' => $purseMasterId,
                                'customer_purse_id' => $customerPurseId,
                                'amount' => $amount,
                                'agent_id' => $dataArr['by_api_user_id'],
                            );
                            $baseTxn->successKotakGPRCardDebit($baseTxnParams);
//                            
                        } else {
                            $failedReason = $ecsApi->getError();
                            $loadStatus = STATUS_FAILED;
                            $updateArr = array(
                                'amount_available' => 0,
                                'amount_used' => 0,
                                'amount_cutoff' => 0,
                                'status' => STATUS_FAILED,
                                'date_failed' => new Zend_Db_Expr('NOW()'),
                                'failed_reason' => $failedReason,
                                'date_load' => new Zend_Db_Expr('NOW()')
                            );
                            $this->updateLoadRequests($updateArr, $loadRequestId);
                        }
                    }
                             
                            $custPurse = $custPurseModel->getCustBalance($cardholderDetails->kotak_customer_id);
                            $productDetail = $productModel->getProductInfo($productId);
                           
                            $cardholderArray['cardNumber'] = $cardholderDetails->card_number;
                            
                          
                           $ecsApi = new App_Api_ECS_Transactions();
                            $res = $ecsApi->balanceInquiry($cardholderArray);
                            if($res){
                            $balVal = $res->balanceInquiryList->availablebalance;
                            }
                            else{
                              $balVal = '';  
                            }
                            // Send SMS
                            $userData = array(
                             'last_four' =>substr($cardholderDetails->card_number, -4),
                             'product_name' => $productDetail['name'],
                             'mobile' => $cardholderDetails->mobile,
                             'amount' => $amount,
                             'balance' => $balVal,
                            );
                            $m->cardLoad($userData);   
                } elseif (strtolower($dataArr['mode']) == TXN_MODE_CR) {

                    $validator = array(
                        'load_request_id' => $loadRequestId,
                        'customer_master_id' => $customerMasterId,
                        'purse_master_id' => $purseMasterId,
                        'customer_purse_id' => $customerPurseId,
                        'amount' => $amount,
                        'agent_id' => $dataArr['by_api_user_id'],
                        'product_id' => $productId,
                    );
                    
                    $flgValidate = $baseTxn->chkAllowKotakGPRCardLoad($validator);
                    
                    if ($flgValidate) {
                        $cardLoadData = array(
                            'amount' => (string) $amount,
                            'crn' => $cardNumber,
                            'agentId' => $dataArr['by_api_user_id'],
                            'transactionId' => $txnCode,
                            'currencyCode' => CURRENCY_INR_CODE,
                            'countryCode' => COUNTRY_IN_CODE
                        );
                        
                        
                        $apiResp = $ecsApi->cardLoad($cardLoadData);
                        
                        if ($apiResp === TRUE) {

                            $updateArr = array(
                                'amount_available' => $amount,
                                'amount_used' => 0,
                                'amount_cutoff' => 0,
                                'txn_load_id' => $ecsApi->getISOTxnId(),
                                'status' => STATUS_LOADED,
                                'date_load' => new Zend_Db_Expr('NOW()')
                            );
                            
                            $this->updateLoadRequests($updateArr, $loadRequestId);
                                
                            $baseTxnParams = array(
                                'txn_code' => $txnCode,
                                'customer_master_id' => $customerMasterId,
                                'product_id' => $productId,
                                'purse_master_id' => $purseMasterId,
                                'customer_purse_id' => $customerPurseId,
                                'amount' => $amount,
                                'agent_id' => $dataArr['by_api_user_id'],
                                'txn_type' => TXNTYPE_CARD_RELOAD,
                            );
                          
                            $baseTxn->successKotakCardLoad($baseTxnParams);
                            $custPurse = $custPurseModel->getCustBalance($cardholderDetails->kotak_customer_id);
                            $productDetail = $productModel->getProductInfo($productId);
                            
                            $cardholderArray['cardNumber'] = $cardholderDetails->card_number;

                            $ecsApi = new App_Api_ECS_Transactions();
                            $res = $ecsApi->balanceInquiry($cardholderArray);
                            if($res){
                             $balVal = $res->balanceInquiryList->availablebalance;
                           
                            }
                            else{
                              $balVal = '';  
                            }
                            // Send SMS
                             $userData = array(
                             'last_four' =>substr($cardholderDetails->card_number, -4),
                             'product_name' => $productDetail['name'],
                             'mobile' => $cardholderDetails->mobile,
                             'amount' => $amount,
                             'balance' => $balVal,
                                     
                                     );
                                       
                            $m->cardLoad($userData);
                        } else {
                            $failedReason = $ecsApi->getError();
                            $loadStatus = STATUS_FAILED;
                            $updateArr = array(
                                'amount_available' => 0,
                                'amount_used' => 0,
                                'amount_cutoff' => 0,
                                'status' => STATUS_FAILED,
                                'date_failed' => new Zend_Db_Expr('NOW()'),
                                'failed_reason' => $failedReason,
                                'date_load' => new Zend_Db_Expr('NOW()')
                            );
                            $this->updateLoadRequests($updateArr, $loadRequestId);
                            
                        }
                    }
                    
                }
            }
            
            $this->setError($failedReason);
            $this->setTxncode($txnCode);
            
            $this->_db->commit();
           
            if($loadStatus == STATUS_FAILED) {
                return FALSE;
            } 
           
        } catch (App_Exception $e) {
            $this->setError($e->getMessage());
            //App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_db->rollBack();
            $updateArr = array(
                    'amount_available' => 0,
                    'amount_used' => 0,
                    'amount_cutoff' => 0,
                    'status' => STATUS_FAILED,
                    'date_failed' => new Zend_Db_Expr('NOW()'),
                    'failed_reason' => $e->getMessage(),
                    'date_load' => new Zend_Db_Expr('NOW()')
                );
                $this->updateLoadRequests($updateArr, $loadRequestId);
                throw new Exception($e->getMessage());
          } catch (App_Api_Exception $e) {
              $this->setError($e->getMessage());
            //App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_db->rollBack();
            $updateArr = array(
                    'amount_available' => 0,
                    'amount_used' => 0,
                    'amount_cutoff' => 0,
                    'status' => STATUS_FAILED,
                    'date_failed' => new Zend_Db_Expr('NOW()'),
                    'failed_reason' => $e->getMessage(),
                    'date_load' => new Zend_Db_Expr('NOW()')
                );
                $this->updateLoadRequests($updateArr, $loadRequestId);
                throw new Exception($e->getMessage());
          }catch (Exception $e) {
            $this->setError($e->getMessage());
//            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_db->rollBack();
            $updateArr = array(
                    'amount_available' => 0,
                    'amount_used' => 0,
                    'amount_cutoff' => 0,
                    'status' => STATUS_FAILED,
                    'date_failed' => new Zend_Db_Expr('NOW()'),
                    'failed_reason' => $e->getMessage(),
                    'date_load' => new Zend_Db_Expr('NOW()')
                );
                $this->updateLoadRequests($updateArr, $loadRequestId);
            //throw new Exception ("Transaction not completed due to system failure");
            throw new Exception($e->getMessage());
        }
        return array('status' => STATUS_LOADED);
    }
    
    /*
     * addCardload add card load request from batch table
     */

    public function addCardload($id) {
        if (empty($id)) {
            throw new Exception('Data missing for load cardholder');
        }
        $custModel = new Corp_Kotak_Customers();
        $custPurseModel = new Corp_Kotak_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $productModel = new Products();
        $baseTxn = new BaseTxn();
        $user = Zend_Auth::getInstance()->getIdentity();
        $str = '';
        try {
            
            $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
            
            $select = $this->_db->select()
                    ->from(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST_BATCH, array(
                        'id', 'product_id', 'txn_identifier_type', $card_number, 'member_id', 'employee_id', 'amount', 'currency', 'narration', 'wallet_code', 'txn_no', 'card_type', 'mode', 'corporate_id', 'by_corporate_id', 'by_ops_id', 'ip', 'batch_name', 'date_created', 'date_updated', 'failed_reason', 'upload_status'
                    ))
                    ->where("id =?", $id);

                $dataArr = $this->_db->fetchRow($select); 

                $searchArr = array(
                    'member_id' => ($dataArr['member_id'] != 0) ? $dataArr['member_id'] : '',
                    'card_number' => ($dataArr['card_number'] != 0) ? $dataArr['card_number'] : '',
                    'status' => STATUS_ACTIVE);

                $cardholderDetails = $custModel->getCardholderInfo($searchArr);
                if(!isset($cardholderDetails->id) || $cardholderDetails->id == '')
                {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = 'Active Cardholder not found';
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
                $ratCustomerId = ($cardholderDetails->kotak_customer_id != '') ? $cardholderDetails->kotak_customer_id : 0;
                $customerPurseId = 0;
                // Master Purse id
                
                $prodInfo = $productModel->getProductInfo($dataArr['product_id']);
                if($prodInfo['const'] == PRODUCT_CONST_KOTAK_AMULWB) {
                    $product = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_AMUL);
                    $pursecode = $product->purse->code->corporatewallet;
                } elseif($prodInfo['const'] == PRODUCT_CONST_KOTAK_AMULGUJ) {
                    $product = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_AMULGUJ);
                    $pursecode = $product->purse->code->corporatewallet;
                } elseif($prodInfo['const'] == PRODUCT_CONST_KOTAK_SEMICLOSE_GPR) {
                    $product = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_SEMICLOSE_GPR);
                    $pursecode = $product->purse->code->corporatewallet;
                } elseif($prodInfo['const'] == PRODUCT_CONST_KOTAK_OPENLOOP_GPR) {
                    $product = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_OPENLOOP_GPR);
                    $pursecode = $product->purse->code->corporatewallet;
                }
                
                $masterPurseDetails = $masterPurseModel->getPurseIdByPurseCode($pursecode);
                
                // Purse id 
                if($ratCustomerId > 0) { 
                    $purseDetails = $custPurseModel->getCustPurseDetails(array('kotak_customer_id' => $ratCustomerId, 'purse_master_id' => $masterPurseDetails['id']));
                    $customerPurseId = (isset($purseDetails['id']) && $purseDetails['id'] > 0) ? $purseDetails['id'] : 0;
                }
                
                $amount = 0;
                if($loadStatus == STATUS_PENDING)
                {
                    if(strpos($dataArr['amount'],'.') !== FALSE)
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
                $loadChanel = (!empty($user->corporate_code))? BY_CORPORATE: BY_OPS;
                $txnCode = $baseTxn->generateTxncode();
			
		if(($cardNumber != 0) &($cardNumber != '')){
		    $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
		    $cardNumEnc = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')"); 
		} else {
		    $cardNumEnc = $cardNumber ;
		}
		
                $data = array(
                    'customer_master_id' => $customerMasterId,
                    'cardholder_id' => $cardholderId,
                    'customer_purse_id' => $customerPurseId,
                    'txn_type' => TXNTYPE_KOTAK_CORP_CORPORATE_LOAD,
                    'load_channel' => $loadChanel,
                    'purse_master_id' => $masterPurseDetails['id'],
                    'txn_identifier_type' => $dataArr['txn_identifier_type'],
                    'card_number' => $cardNumEnc,
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
                    'by_corporate_id' => $dataArr['by_corporate_id'],
                    'batch_name' => $dataArr['batch_name'],
                    'product_id' => $dataArr['product_id'],
                    'status' => $loadStatus,
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'failed_reason' => $failedReason,
                    'date_failed' => $dateFailed,
                    'date_load' => $dateLoad

                );
                
                $this->insert($data);
                $loadRequestId = $this->_db->lastInsertId(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST, 'id'); 
                $this->_db->commit();
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_db->rollBack();
            //throw new Exception ("Transaction not completed due to system failure");
            throw new Exception($e->getMessage());
        }
        return TRUE;
    }
    
    /*
     * Validates the fields of bulk cardload
    */
    public function isValid($param)
    {
        $purseMaster = new MasterPurse();
        
        $txn_identifier_type = isset($param['txn_identifier_type']) ? $param['txn_identifier_type'] : '';
        $card_number = isset($param['card_number']) ? $param['card_number'] : '';
        $amount = isset($param['amount']) ? $param['amount'] : '';
        $wallet_code = isset($param['wallet_code']) ? $param['wallet_code'] : '';
        $product_id = isset($param['product_id']) ? $param['product_id'] : '';
        $card_type = isset($param['card_type']) ? $param['card_type'] : '';

        if((strtolower($txn_identifier_type) != CORP_WALLET_TXN_IDENTIFIER_CN) && (strtolower($txn_identifier_type) != CORP_WALLET_TXN_IDENTIFIER_MI) && (strtolower($txn_identifier_type) != CORP_WALLET_TXN_IDENTIFIER_EI)){
                $this->setError('Invalid Txn Identifier Type');
                return FALSE;
        }
        
        if(strtolower($txn_identifier_type) == CORP_WALLET_TXN_IDENTIFIER_CN){
            if(strlen($card_number) != 16 || !(ctype_digit($card_number))){
                $this->setError('Invalid Card Number');
                return FALSE;
            }
        }
        
	if(strtolower($txn_identifier_type) == CORP_WALLET_TXN_IDENTIFIER_MI || strtolower($txn_identifier_type) == CORP_WALLET_TXN_IDENTIFIER_EI || strtolower($txn_identifier_type) == CORP_WALLET_TXN_IDENTIFIER_CN){
            $details = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER .' as kc', array('kc.id','kc.status'))
                ->where('product_id = ?',$product_id);
		
	    if(strtolower($txn_identifier_type) == CORP_WALLET_TXN_IDENTIFIER_CN){ 
                $crnkey = App_DI_Container::get('DbConfig')->crnkey; 
                $card_num = new Zend_Db_Expr("AES_DECRYPT(`kc`.`card_number`,'".$crnkey."')");
                $details->where("$card_num = ?",$card_number);
            }elseif(strtolower($txn_identifier_type) == CORP_WALLET_TXN_IDENTIFIER_MI){
		$details->where('member_id = ?',$card_number);
	    }elseif(strtolower($txn_identifier_type) == CORP_WALLET_TXN_IDENTIFIER_EI){
		$details->where('employee_id = ?',$card_number);
	    }	
	    //echo $details; //exit;	
            $row = $this->_db->fetchRow($details);
            if(!isset($row['id']) || empty($row['id'])){
                if(strtolower($txn_identifier_type) == CORP_WALLET_TXN_IDENTIFIER_CN){	
		    $this->setError('Cardholder not found');
		}elseif(strtolower($txn_identifier_type) == CORP_WALLET_TXN_IDENTIFIER_MI){
		    $this->setError('Cardholder not found');
		}elseif(strtolower($txn_identifier_type) == CORP_WALLET_TXN_IDENTIFIER_EI){
		    $this->setError('Cardholder not found');
		}	
                return FALSE;
            }
	    
            if($row['status'] == STATUS_ECS_PENDING)
            {
                 $this->setError('Cardholder Registration pending with ECS');
                 return FALSE;
            }elseif($row['status'] == STATUS_ECS_FAILED){
                 $this->setError('Cardholder Registration failed with ECS');
                 return FALSE;
            }elseif($row['status'] == STATUS_INACTIVE){
                $this->setError('Cardholder Inactive');
                 return FALSE;
            }elseif($row['status'] == STATUS_ACTIVATION_PENDING){
                $this->setError('Cardholder Actiation Pending');
                 return FALSE;
            }elseif($row['status'] == STATUS_PENDING){
                $this->setError('Cardholder Registration pending with ECS');
                return FALSE;
            }
        }
                
        if(!is_numeric($amount) || $amount <= 0){
                $this->setError('Invalid amount');
                return FALSE;
        }
        
        if(empty($product_id))
        {
            $this->setError('Invalid product id');
            return false;
        }else
        {
            $purseDetails = $purseMaster->getPurseDetailsbyProduct($product_id, $wallet_code);
            if(!isset($purseDetails['id']) || empty($purseDetails['id'])){
                $this->setError('Invalid Wallet Code');
                return FALSE;
            }
        }
    
        if(strtolower($card_type) != strtolower(CORP_CARD_TYPE_NORMAL) && strtolower($card_type) != strtolower(CORP_CARD_TYPE_CORPORATE) )
        {
            $this->setError('Card type Corporate ID Validation failed');
            return false;
        }
        
        return true;
    }
    
    public function checkBatchFilename($fileName) {
        
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST_BATCH, array('batch_name'));
        $select->where("batch_name =?", $fileName);
        $rs = $this->_db->fetchRow($select);
        if (empty($rs)) {
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
     public function getRemitWalletTrialBalance($param) 
    {
        $retArr = array();
                return $retArr;
    }
 
   
    public function getLoadBatch($batchLimit = 10) {
           $updateArr = array('flg' => FLAG_YES); 
           $loadRequests = $this->getLoadRequestId(array('limit' => KOTAK_CORP_LOAD_LIMIT, 'status' => STATUS_PENDING,'flag' => FLAG_NO));        
           $arr = array();
            //echo '<pre>';print_r($loadRequests);
            $ln = count($loadRequests) / $batchLimit;
            //echo $ln;
            $i = 0;
            $c = 1;
            foreach ($loadRequests as $req) {
            // Update the status
            $this->updateLoadRequests($updateArr, $req['id']);
                //echo '<pre>';print_r($req['id']);exit;
              $arr[$i][] = $req['id'];
              if($c >= $batchLimit) { 
                  $i++;
                  $c=0;
              }
              $c++;
            }
//            echo '<pre>';print_r($arr);exit;
            //$this->doSingleCorporateLoad($arr);
            return $arr;
    }
 
    
    public function getLoadRequestId($param) {
        $status = isset($param['status']) ? $param['status'] : '';
        $flag = isset($param['flag']) ? $param['flag'] : '';
        $loadChannel = isset($param['load_channel']) ? $param['load_channel'] : '';
        $batchName = isset($param['batch_name']) ? $param['batch_name'] : '';
        $purseMasterId = isset($param['purse_master_id']) ? $param['purse_master_id'] : '';
        $limit = isset($param['limit']) ? $param['limit'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $employer_name = isset($param['employer_name']) ? $param['employer_name'] : '';
        $employer_loc = isset($param['employer_loc']) ? $param['employer_loc'] : '';
        $product = isset($param['product_id']) && $param['product_id'] > 0? $param['product_id'] : '';
       
        $select = $this->_db->select()
                ->from($this->_name , array('id'));
        
        
          //->setIntegrityCheck(false)
          //->from($this->_name ." as l",array('l.id'));
        if ($product != '') {
            $select->where('product_id = ?', $product);
        }
        if ($loadChannel != '') {
            $select->where('load_channel = ?', $loadChannel);
        }
        if ($batchName != '') {
            $select->where('batch_name = ?', $batchName);
        }
        if ($purseMasterId != '') {
            $select->where('purse_master_id = ?', $purseMasterId);
        }
        if ($from != '' && $to != ''){
            $select->where("date_created >= '" . $from . "'");
            $select->where("date_created <= '" . $to . "'");
        }
        if($status != ''){
	    $select->where('status = ?', $status);
	}
        if($flag != ''){
	    $select->where('flg = ?', $flag);
	}
        if ($limit != '') {
            $select->limit($limit);
        }
	//echo $select;exit;
        return $this->_db->fetchAll($select);
    }
 
    
 
   
    public function doSingleCorporateLoad($loadRequestIDs) {
       // $c = rand(1, 20);
       // sleep($c);
//    echo "<pre>------";print_r($loadRequestIDs);
        //exit('gfgfg');
    $IDString = implode(",",$loadRequestIDs);
    
        $loadRequests = $this->getLoadRequestsByIDs(array('ids' => $IDString, 'status' => STATUS_PENDING,'flag' => FLAG_YES));
        $loadRequests = Util::toArray($loadRequests);
        //return TRUE;
        //echo '<pre>';print_r($loadRequests);//exit;
        $count = count($loadRequests);
        $retResp = array('loaded' => 0, 'not_loaded' => 0, 'exception' => array());
        if ($count > 0) {
            $ecsApi = new App_Socket_ECS_Corp_Transaction();
            $baseTxn = new BaseTxn();
            $m = new \App\Messaging\Corp\Kotak\Operation();
            $cardholderModel = new Corp_Kotak_Customers();
            $custPurseModel = new Corp_Kotak_CustomerPurse();
            
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
                        'corporate_id' => $val['by_corporate_id'],
                        'agent_id' => $val['by_agent_id'],
                        'product_id' => $val['product_id']
                            
                    );
                    $flgValidate = $baseTxn->chkAllowKotakCorporateCardLoad($validator);
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
                        $cardholderArray['cardNumber'] = $val['card_number'];
                        $ecsApi2 = new App_Api_ECS_Transactions();
                        try {
                            $resp = $ecsApi2->balanceInquiry($cardholderArray);
                            if($resp) {
                                $res = $ecsApi2->getLastResponse();
                                $balVal = $res->balanceInquiryList->availablebalance;
                            } else {
                                $balVal = FALSE;
                            }
                        } catch (Exception $e) {
                            $balVal = FALSE;
                            App_Logger::log($e->getMessage());
                        }
                        
                        //$balVal = $ecsApi->getElementResponse(self::DATA_ELEMENT_BALANCE);
                        //$balVal = FALSE;
                        //$cardholderArray['cardNumber'] = $val['card_number'];
                        //$ecsApi2 = new App_Api_ECS_Transactions();
                        //$resp = $ecsApi2->balanceInquiry($cardholderArray);
                        //if($resp) {
                        //    $res = $ecsApi2->getLastResponse();
                        //    $balVal = $res->balanceInquiryList->availablebalance;
                        //} else {
                        //    $balVal = '0.00';
                        //}
                        

                        if($apiResp === TRUE){
                            
                            $updateArr = array(
                                    'amount_available' => $val['amount'],
                                    'amount_used' => 0,
                                    'amount_cutoff' => 0,
                                    'txn_load_id' => $ecsApi->getISOTxnId(),
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
                              'corporate_id' => $val['by_corporate_id']
                            );
                            $baseTxn->successKotakCorporateCardLoad($baseTxnParams);
                            //$baseTxn->successCorpKotakCorporateCardLoad($baseTxnParams);
                            
                            $objProducts = new Products();
                            $product_details = $objProducts->findById($val['product_id']);
                            $product_const = $product_details['const'];
                            
                            if($product_const == PRODUCT_CONST_KOTAK_AMULWB || $product_const == PRODUCT_CONST_KOTAK_AMULGUJ)
                            {
                                $product_nm = KOTAK_AMUL_PRODUCT;
                            }
                            if($product_const == PRODUCT_CONST_KOTAK_SEMICLOSE_GPR || $product_const == PRODUCT_CONST_KOTAK_OPENLOOP_GPR)
                            {
                                $product_nm = KOTAK_GPR_PRODUCT;
                            }
                            
                            
                            $cardholder = $cardholderModel->findById($val['cardholder_id']);
                            //$custPurse = $custPurseModel->getCustBalance($cardholder['kotak_customer_id']);
                                $userData = array('last_four' =>substr($val['card_number'], -4),
                                    'product_name' => $product_nm,
                                    'amount' => $val['amount'],
                                    'balance' => $balVal, //Util::numberFormat($balVal / 100), //$custPurse['sum'],
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
                  } // END IF Condition
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

 
    
    public function getLoadRequestDetail($param) {
        $status = isset($param['status']) ? $param['status'] : '';
        $loadChannel = isset($param['load_channel']) ? $param['load_channel'] : '';
        $batchName = isset($param['batch_name']) ? $param['batch_name'] : '';
        $purseMasterId = isset($param['purse_master_id']) ? $param['purse_master_id'] : '';
        $limit = isset($param['limit']) ? $param['limit'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $employer_name = isset($param['employer_name']) ? $param['employer_name'] : '';
        $employer_loc = isset($param['employer_loc']) ? $param['employer_loc'] : '';
        $product = isset($param['product_id']) && $param['product_id'] > 0? $param['product_id'] : '';
        $loadId = isset($param['id']) && $param['id'] > 0? $param['id'] : '';
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`l`.`card_number`,'".$decryptionKey."') as card_number");
            
        $select = $this->select()
          ->setIntegrityCheck(false)
          ->from($this->_name ." as l",array(
               'id', 'product_id', 'customer_master_id', 'cardholder_id', 'purse_master_id', 'customer_purse_id', 'txn_type', 'load_channel', 'txn_identifier_type', $card_number, 'member_id', 'amount', 'amount_available', 'amount_used', 'amount_cutoff', 'currency', 'narration', 'wallet_code', 'txn_no', 'card_type', 'corporate_id', 'mode', 'txn_code', 'by_agent_id', 'by_corporate_id', 'by_ops_id', 'ip', 'batch_name', 'date_created', 'date_load', 'date_failed', 'date_cutoff', 'txn_load_id', 'failed_reason', 'date_updated', 'flg', 'status'
          ));
        $select->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "l.product_id  = p.id",array('p.name as product_name'))
               ->joinLeft(DbTable::TABLE_KOTAK_CORP_CARDHOLDER." as kcc", "kcc.id=l.cardholder_id", array());
        if ($product != '') {
            $select->where('l.product_id = ?', $product);
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
        if ($employer_name != '') {
            $select->where("kcc.employer_name LIKE  '%". $employer_name. "%'");
        }
        if ($employer_loc != '') {
            $select->where("kcc.comm_city LIKE  '%". $employer_loc. "%'");
        }
        if($status != ''){
	    $select->where('l.status = ?', $status);
	}
        if($loadId != ''){
	    $select->where('l.id = ?', $loadId);
	}
        if ($limit != '') {
            $select->limit($limit);
        } 
        return $this->fetchRow($select);
    }

     public function getLoadRequestsByIDs($param) {
        $status = isset($param['status']) ? $param['status'] : '';
        $flag = isset($param['flag']) ? $param['flag'] : '';
        $loadIds = isset($param['ids']) ? $param['ids'] : '';
        $retArray = array();
        if($loadIds == ''){
           return $retArray; 
        } else {
        
            $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $card_number = new Zend_Db_Expr("AES_DECRYPT(`l`.`card_number`,'".$decryptionKey."') as card_number");

            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(DbTable::TABLE_KOTAK_CORP_LOAD_REQUEST ." as l", array(
                        'id', 'product_id', 'customer_master_id', 'cardholder_id', 'purse_master_id', 'customer_purse_id', 'txn_type', 'load_channel', 'txn_identifier_type', $card_number, 'member_id', 'amount', 'amount_available', 'amount_used', 'amount_cutoff', 'currency', 'narration', 'wallet_code', 'txn_no', 'card_type', 'corporate_id', 'mode', 'txn_code', 'by_agent_id', 'by_corporate_id', 'by_ops_id', 'ip', 'batch_name', 'date_created', 'date_load', 'date_failed', 'date_cutoff', 'txn_load_id', 'failed_reason', 'date_updated', 'flg', 'status'
                    ));
            $select->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "l.product_id  = p.id",array('p.name as product_name'))
                    ->joinLeft(DbTable::TABLE_KOTAK_CORP_CARDHOLDER." as kcc", "kcc.id=l.cardholder_id", array())
                    ->where("l.id IN ($loadIds)");
            
            if($status != ''){
                $select->where('l.status = ?', $status);
            }
            if($flag != ''){
                $select->where('l.flg = ?', $flag);
            } 
            $retArray = $this->fetchAll($select);
            return $retArray;
        }
    }
    
    public function exportAmulLoadRequests($params){
        $data = $this->getLoadRequests($params);
        $data = $data->toArray();
        $retData = array();
        
        if(!empty($data))
        {         
            foreach($data as $key=>$data){
                    $retData[$key]['product_name']  = $data['product_name'];
                    $retData[$key]['txn_identifier_type']  = $data['txn_identifier_type'];
                    $retData[$key]['card_number']          = Util::maskCard($data['card_number']);
                    $retData[$key]['member_id']    = $data['member_id'];
                    $retData[$key]['amount']      = $data['amount'];
		    $retData[$key]['amount_cutoff']      = $data['amount_cutoff']; 
                    $retData[$key]['currency']        = $data['currency'];
                    $retData[$key]['narration'] = $data['narration'];
                    $retData[$key]['wallet_code'] = $data['wallet_code'];
                    $retData[$key]['txn_no'] = strtoupper($data['txn_no']);
                    $retData[$key]['card_type']      = strtoupper($data['card_type']); 
                    $retData[$key]['corporate_id']      = strtoupper($data['corporate_id']); 
                    $retData[$key]['mode']      = strtoupper($data['mode']); 
                    $retData[$key]['txn_code']      = $data['txn_code']; 
                    $retData[$key]['failed_reason']      = $data['failed_reason']; 
                    $retData[$key]['status']      = $data['status'];
            }
        }
        
        return $retData; 
     }
}
