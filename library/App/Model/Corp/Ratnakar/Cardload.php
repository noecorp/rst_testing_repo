<?php

/**
 * Model that manages ratnakar cardloads
 *
 * @package Operation_Models
 * @copyright transerv
 */
class Corp_Ratnakar_Cardload extends Corp_Ratnakar {

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
    protected $_name = DbTable::TABLE_RAT_CORP_LOAD_REQUEST;

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
    
    // protected $_txncode = '';
     
    /*
     * showPendingCardloadDetails , show load requests from batch table which have upload status as temp
     */
    public function showPendingCardloadDetails($batchName, $page = 1, $paginate = NULL, $force = FALSE) {
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST_BATCH, array(
            'id', 'bank_id', 'product_id', 'txn_identifier_type', $card_number, 'medi_assist_id', 'employee_id', 'amount', 'currency', 'narration', 'wallet_code', 'txn_no', 'card_type', 'mode', 'corporate_id', 'by_ops_id', 'by_corporate_id', 'ip', 'batch_name', 'date_created', 'date_updated', 'failed_reason', 'upload_status'
        ));
        $select->where('upload_status = ?', STATUS_TEMP);
        $select->where('batch_name = ?', $batchName);
        $select->order('id ASC');
        if($force){
            return $this->_db->fetchAll($select);
        }
        return $this->_paginate($select, $page, $paginate);
    }

    /*
     * insertLoadrequestBatch , insert data from file into the batch table
     */

   public function insertLoadrequestBatch($dataArr, $batchName, $status) {
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $productModel = new Products();
        $prodInfo = $productModel->getProductInfo($dataArr['product_id']);

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
            if(strtolower($dataArr[0]) == CORP_WALLET_TXN_IDENTIFIER_CN){ 
                $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $card_num = new Zend_Db_Expr("AES_ENCRYPT('".$dataArr[1]."','".$encryptionKey."')"); 
            } else {
                $card_num = 0;
            }
            $data = array(
                'txn_identifier_type' => strtolower($dataArr[0]),
                'card_number' => $card_num,
                'medi_assist_id' => (strtolower($dataArr[0]) == CORP_WALLET_TXN_IDENTIFIER_MI) ? $dataArr[1] : 0,
		'employee_id' =>  (strtolower($dataArr[0]) == CORP_WALLET_TXN_IDENTIFIER_EI) ? $dataArr[1] : 0,
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
                'bank_id' => $prodInfo['bank_id'],
                'product_id' => $dataArr['product_id'],
                'failed_reason' => $failedReason,
                'upload_status' => $status,
                'date_created' => new Zend_Db_Expr('NOW()')
            );
            if(!empty($user->corporate_code)){
                    $data['by_ops_id']= 0;
                    $data['by_corporate_id']= $user->id;
                 }
            $this->_db->insert(DbTable::TABLE_RAT_CORP_LOAD_REQUEST_BATCH, $data);

            return TRUE;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            return false;
        }
    }

    /*
     * bulkAddCardload add card load request from batch table
     */

    public function bulkAddCardload($idArr, $batchName, $channel) {
        if (empty($idArr)) {
            throw new Exception('Data missing for add cardholder');
        }
        $custModel = new Corp_Ratnakar_Cardholders();
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $baseTxn = new BaseTxn();
        $user = Zend_Auth::getInstance()->getIdentity();
        $productModel = new Products();
        $str = '';
        try {
            // Foreach selected id value
            foreach ($idArr as $id) {
                $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
                
                $select = $this->_db->select();
                $select->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST_BATCH,array(
                    'id', 'bank_id', 'product_id', 'txn_identifier_type', $card_number, 'medi_assist_id', 'employee_id', 'amount', 'currency', 'narration', 'wallet_code', 'txn_no', 'card_type', 'mode', 'corporate_id', 'by_ops_id', 'by_corporate_id', 'ip', 'batch_name', 'date_created', 'date_updated', 'failed_reason', 'upload_status'
                ));
                $select->where("id =?", $id);
                $dataArr = $this->_db->fetchRow($select);
                $searchArr = array(
                    'medi_assist_id' => ($dataArr['medi_assist_id'] != 0) ? $dataArr['medi_assist_id'] : '',
                    'card_number' => ($dataArr['card_number'] != 0) ? $dataArr['card_number'] : '',
                    'status' => STATUS_ACTIVE
                ); 
                $cardholderDetails = $custModel->getCardholderInfo($searchArr);
                if(($dataArr['medi_assist_id']<=0 || $dataArr['medi_assist_id']=='') && ( $dataArr['card_number'] <=0 || $dataArr['card_number']==''))
                {
                    $cardholderDetails = '';
                    $loadStatus = STATUS_FAILED;
                    $failedReason = 'Invalid card number or medi assist id';
                    $dateFailed = new Zend_Db_Expr('NOW()');
                    $dateLoad = new Zend_Db_Expr('NOW()');   
                }
		elseif(!isset($cardholderDetails->id) || $cardholderDetails->id == '')
                {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = 'Cardholder not found';
                    $dateFailed = new Zend_Db_Expr('NOW()');
                    $dateLoad = new Zend_Db_Expr('NOW()');   
                }
                
               
                $cardNumber = ($searchArr['card_number'] != '') ? $searchArr['card_number'] : $cardholderDetails->card_number;
                $mediAssistId = ($searchArr['medi_assist_id'] != '') ? $searchArr['medi_assist_id'] : $cardholderDetails->medi_assist_id;
                $customerMasterId = ($cardholderDetails->customer_master_id != '') ? $cardholderDetails->customer_master_id : 0;
                $cardholderId = ($cardholderDetails->id != '') ? $cardholderDetails->id : 0;
                $ratCustomerId = ($cardholderDetails->rat_customer_id != '') ? $cardholderDetails->rat_customer_id : 0;
                $customerPurseId = 0;
                // Master Purse id
                
                
                $prodInfo = $productModel->getProductInfo($dataArr['product_id']);
                if($prodInfo['const'] == PRODUCT_CONST_RAT_CNY) {
                    $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CNERGYIS);
                    $pursecode = $product->purse->code->genwallet;
                } elseif($prodInfo['const'] == PRODUCT_CONST_RAT_SUR) {
                    $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_SURYODAY);
                    $pursecode = $product->purse->code->genwallet;
                } elseif($prodInfo['const'] == PRODUCT_CONST_RAT_GPR) {
                    $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_GENERIC_GPR);
                    $pursecode = $product->purse->code->genwallet;
                } else{
                $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CORP);
                $pursecodeHr = $product->purse->code->corporatehr;
                $pursecodeGen = $product->purse->code->genwallet;
                if(strtolower($dataArr['wallet_code']) == strtolower($pursecodeGen)) {
                    $pursecode = $pursecodeGen;
                    $txnType = TXNTYPE_CARD_RELOAD;
                } else {
                    $pursecode = $pursecodeHr;
                    $txnType = TXNTYPE_RAT_CORP_CORPORATE_LOAD;
                }
                }
                $txnType = TXNTYPE_CARD_RELOAD;
                
              
                $masterPurseDetails = $masterPurseModel->getPurseIdByPurseCode($pursecode);
                // Purse id 
                if($ratCustomerId > 0) { 
                    $purseDetails = $custPurseModel->getCustPurseDetails(array('rat_customer_id' => $ratCustomerId, 'purse_master_id' => $masterPurseDetails['id']));
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
                    elseif($dataArr['amount'] <= 0){
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
                        $loadStatus = STATUS_FAILED;
                        $failedReason = 'Card type Corporate ID Validation failed';
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
                $loadChanel = (!empty($user->corporate_code))? BY_CORPORATE: BY_OPS;
                        
                $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $cardNumberEnc = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')"); 
                $data = array(
                    'customer_master_id' => $customerMasterId,
                    'cardholder_id' => $cardholderId,
                    'customer_purse_id' => $customerPurseId,
                    'txn_type' => $txnType,
                    'load_channel' => $loadChanel,
                    'purse_master_id' => $masterPurseDetails['id'],
                    'txn_identifier_type' => $dataArr['txn_identifier_type'],
                    'card_number' => $cardNumberEnc,
                    'medi_assist_id' => $mediAssistId,
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
                    'by_corporate_id'=> $dataArr['by_corporate_id'],
                    'batch_name' => $dataArr['batch_name'],
                    'bank_id' => $dataArr['bank_id'],
                    'product_id' => $dataArr['product_id'],
                    'status' => $loadStatus,
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'failed_reason' => $failedReason,
                    'date_failed' => $dateFailed,
                    'date_load' => $dateLoad,
                    'channel' => $channel
                );
                $this->insert($data);
                $loadRequestId = $this->_db->lastInsertId(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, 'id');
                $updateArr = array('upload_status' => STATUS_PASS);
                $this->_db->update(DbTable::TABLE_RAT_CORP_LOAD_REQUEST_BATCH, $updateArr, "id= $id");

                $this->_db->commit();
            }// END of foreach loop
            $notInid = implode(",", $idArr);
            $rejectedArr = array('upload_status' => STATUS_REJECTED);
            $this->_db->update(DbTable::TABLE_RAT_CORP_LOAD_REQUEST_BATCH, $rejectedArr, "id NOT IN ($notInid) AND batch_name = '$batchName'");
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
        $productModel = new Products();
        $loadChanelList = "'".BY_OPS."','".BY_CORPORATE."'";
        $loadRequests = $this->getLoadRequests(array('limit' => RAT_CORPORATE_LOAD_LIMIT, 'status' => STATUS_PENDING, 'load_channel_list' => $loadChanelList)); 
        /*$loadRequests = $this->getLoadRequests(array('limit' => RAT_CORPORATE_LOAD_LIMIT, 'status' => STATUS_PENDING, 'load_channel' => BY_CORPORATE));*/
        $count = count($loadRequests);
        $retResp = array('loaded' => 0, 'not_loaded' => 0, 'exception' => array());
        if ($count > 0) {
            $ecsApi = new App_Socket_ECS_Corp_Transaction();
            $baseTxn = new BaseTxn();
            $m = new \App\Messaging\Corp\Ratnakar\Operation();
            $cardholderModel = new Corp_Ratnakar_Cardholders();
            $custPurseModel = new Corp_Ratnakar_CustomerPurse();
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
                        'product_id' =>  $val['product_id'],
                    );
                    
                    $flgValidate = $baseTxn->chkAllowRatCorporateCardLoad($validator);
                    if($flgValidate)
                    {
                        $cardLoadData = array(
                            'amount' => $val['amount'],
                            'crn' => $val['card_number'],
                            'agentId' => TXN_OPS_ID, // any data to be provided here, so default ops id
                            'transactionId' => $val['txn_code'],
                            'currencyCode' => CURRENCY_INR_CODE,
                            'countryCode' => COUNTRY_IN_CODE
                        );
                        $apiResp = $ecsApi->cardLoad($cardLoadData);
                        
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
//                              'corporate_id' =>  $val['by_corporate_id'], 
                              'amount' =>  $val['amount'], 
                              'txn_type' => $val['txn_type'],
                            );
                            $productInfo = $productModel->getProductInfo($val['product_id']);
                            if($productInfo->const == PRODUCT_CONST_RAT_CNY ){
                                // success rat docorporatecardload
                                $baseTxnParams['corporate_id'] = $val['by_corporate_id'];
                                $baseTxn->successDoCorporateCardLoad($baseTxnParams);
                                $prodName = CNY_PRODUCT;
                                
                            } 
                            else if($productInfo->const == PRODUCT_CONST_RAT_SUR ){
                                // success rat docorporatecardload
                                $baseTxnParams['corporate_id'] = $val['by_corporate_id'];
                                $baseTxn->successDoCorporateCardLoad($baseTxnParams);
                                $prodName = SUR_PRODUCT;
                                
                            } else {
                                $baseTxn->successRatCorporateCardLoad($baseTxnParams);
                            
                                if($productInfo->const == PRODUCT_CONST_RAT_GPR) {
                                        $prodName = RBL_GPR_PRODUCT;
                                    } else {
                                        $prodName = MEDIASSIST_PRODUCT;
                                    }
                            }
                            
                            $cardholder = $cardholderModel->findById($val['cardholder_id']);
                            $custPurse = $custPurseModel->getCustBalance($cardholder['rat_customer_id']);
                            $userData = array('last_four' => substr($val['card_number'], -4),
                                'product_name' => $prodName,
                                'amount' => $val['amount'],
                                'balance' => $custPurse['sum'],
                                'mobile' => $cardholder['mobile'],
				'product_id' => $val['product_id'],
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
                 }                    
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
     * doMediAssistCardLoad will do MediAssist load (via API) on ECS - wallet ins
     */

    public function doMediAssistCardLoad($dataArr = array()) {
        $custModel = new Corp_Ratnakar_Cardholders();
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $baseTxn = new BaseTxn();
        $ecsApi = new App_Socket_ECS_Corp_Transaction();
        $m = new \App\Messaging\Corp\Ratnakar\Operation();
        $retResp = array('status' => 0, 'ack_no' => 0, 'error_msg' =>'');
        
        if(strtolower($dataArr['txn_identifier_type']) == CORP_WALLET_TXN_IDENTIFIER_CN)
        {
            $searchArr['card_number'] = $dataArr['member_id_card_no'];
            $searchArr['medi_assist_id'] = '';
        }
        else if(strtolower($dataArr['txn_identifier_type']) == CORP_WALLET_TXN_IDENTIFIER_MI){
            $searchArr['card_number'] = '';
            $searchArr['medi_assist_id'] = $dataArr['member_id_card_no'];
        }
        $searchArr['status'] = STATUS_ACTIVE;
        $cardholderDetails = $custModel->getCardholderInfo($searchArr);
        if(!isset($cardholderDetails->id) || $cardholderDetails->id == '')
        {
            $loadStatus = STATUS_FAILED;
            $failedReason = 'Cardholder not found';
            $dateFailed = new Zend_Db_Expr('NOW()');   
            $dateLoad = new Zend_Db_Expr('NOW()');   
        }
        
        // Master Purse id
        $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CORP);
        $pursecode = $product->purse->code->corporateins;
        $pursecodeGen = $product->purse->code->genwallet;
        $masterPurseDetails = $masterPurseModel->getPurseIdByPurseCode($pursecode);
        if(strtolower($dataArr['wallet_code']) != strtolower($pursecode) && strtolower($dataArr['wallet_code']) != strtolower($pursecodeGen))
        {
//            echo strtolower($dataArr['wallet_code']) ." != ". strtolower($pursecode); exit;
            $loadStatus = STATUS_FAILED;
            $failedReason = 'Wallet Code Validation failed';
            $dateFailed = new Zend_Db_Expr('NOW()');  
            $dateLoad = new Zend_Db_Expr('NOW()');
        }
        else{
            if(strtolower($dataArr['wallet_code']) == strtolower($pursecodeGen)){
                $masterPurseDetails = $masterPurseModel->getPurseIdByPurseCode($pursecodeGen);
            }
        }
        // Purse id 
        $ratCustomerId = ($cardholderDetails->rat_customer_id != '') ? $cardholderDetails->rat_customer_id : 0;
        if($ratCustomerId > 0) { 
            $purseDetails = $custPurseModel->getCustPurseDetails(array('rat_customer_id' => $ratCustomerId, 'purse_master_id' => $masterPurseDetails['id']));
        }
        $customerPurseId = (isset($purseDetails['id']) && $purseDetails['id'] > 0) ? $purseDetails['id'] : 0;
       
        $txnCode = $baseTxn->generateTxncode();
        $productId = $masterPurseDetails['product_id'];
        $cardNumber = ($searchArr['card_number'] != '') ? $searchArr['card_number'] : $cardholderDetails->card_number;
        
        $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $cardNumberEnc = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')");
                
        $mediAssistId = ($searchArr['medi_assist_id'] != '') ? $searchArr['medi_assist_id'] : $cardholderDetails->medi_assist_id;
        $purseMasterId = $masterPurseDetails['id'];
        $customerMasterId = ($cardholderDetails->customer_master_id != '') ? $cardholderDetails->customer_master_id : 0;
        $cardholderId = ($cardholderDetails->id != '') ? $cardholderDetails->id : 0;
        $amount = Util::convertToPaisa($dataArr['amount']);
        
        if(strtolower($dataArr['wallet_code']) == strtolower($pursecodeGen)){
            if(strtolower($dataArr['mode']) == TXN_MODE_CR) {
                 $txnType = TXNTYPE_CARD_RELOAD;
            } else {
                $txnType = TXNTYPE_CARD_DEBIT;      
            }
            $loadChannel = BY_API;
        } else {
            if(strtolower($dataArr['mode']) == TXN_MODE_CR) {
                 $txnType = TXNTYPE_CARD_RELOAD;
            } else {
                $txnType = TXNTYPE_CARD_DEBIT;      
            }
            $loadChannel = MEDIASSIST;
        }
        
         $data = array(
                'product_id' => $productId,
                'customer_master_id' => $customerMasterId,
                'cardholder_id' => $cardholderId,
                'purse_master_id' => $purseMasterId,
                'customer_purse_id' => $customerPurseId,
                'txn_type' => $txnType,
                'load_channel' => $loadChannel,
                'txn_identifier_type' => strtolower($dataArr['txn_identifier_type']),
                'card_number' => $cardNumberEnc,
                'medi_assist_id' => $mediAssistId,
                'amount' => $amount,
                'amount_available' => 0,
                'amount_used' => 0,
                'amount_cutoff' => 0,
                'currency' => $dataArr['currency'],
                'narration' => $dataArr['narration'],
                'wallet_code' => strtoupper($dataArr['wallet_code']),
                'txn_no' => $dataArr['txn_no'],
                'card_type' => strtolower($dataArr['card_type']),
                'corporate_id' => (strtolower($dataArr['card_type']) == CORP_CARD_TYPE_CORPORATE) ? $dataArr['corporate_id'] : 0,
                'mode' => strtolower($dataArr['mode']),
                'txn_code' => $txnCode,
                'by_agent_id' => $dataArr['agent_id'],
                'by_ops_id' => 0,
                'ip' => '',
                'batch_name' => '',
                'date_created' => new Zend_Db_Expr('NOW()'),
                'date_failed' => $dateFailed,
                'failed_reason' => $failedReason,
                'status'    => $loadStatus,
                'date_load' => $dateLoad,                
		'channel' => $dataArr['channel'],

            );
        $this->insert($data);
        $loadRequestId = $this->_db->lastInsertId(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, 'id');
        if($loadStatus == STATUS_PENDING)
        {
          try {
              if (strtolower($dataArr['mode']) == TXN_MODE_DR) {
                    $validator = array(
                        'load_request_id' => $loadRequestId,
                        'customer_master_id' => $customerMasterId,
                        'purse_master_id' => $purseMasterId,
                        'customer_purse_id' => $customerPurseId,
                        'amount' => $amount,
                        'agent_id' => $dataArr['agent_id'],
                        'product_id' => $productId,
                    );
                    $flgValidate = $baseTxn->chkAllowRatMediAssistCardDebit($validator);
                    if ($flgValidate) {
                        $cardLoadData = array(
                            'amount' => (string) $amount,
                            'crn' => $cardNumber,
                            'agentId' => $dataArr['agent_id'], // any data to be provided here, so default ops id
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
                            $retResp = array('status' => STATUS_LOADED, 'ack_no' => $txnCode, 'error_msg' => '');

                            $baseTxnParams = array(
                                'txn_code' => $txnCode,
                                'customer_master_id' => $customerMasterId,
                                'product_id' => $productId,
                                'purse_master_id' => $purseMasterId,
                                'customer_purse_id' => $customerPurseId,
                                'amount' => $amount,
                                'agent_id' => $dataArr['agent_id'],
                                'debit_api_cr' => $masterPurseDetails['debit_api_cr'],
                                'payable_ac_id' => $masterPurseDetails['payable_ac_id'],
                            );
                            $baseTxn->successRatMediAssistCardDebit($baseTxnParams);
                            $custPurse = $custPurseModel->getCustBalance($ratCustomerId);
                            $userData = array('last_four' => substr($cardNumber, -4),
                                'product_name' => MEDIASSIST_PRODUCT,
                                'amount' => $amount,
                                'balance' => $custPurse['sum'],
                                'mobile' => $cardholderDetails->mobile,
				'product_id' => $productId,
                            );
                            $resp = $m->cardLoad($userData);
                        } else {
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
                            $this->updateLoadRequests($updateArr, $loadRequestId);
                            $retResp = array('status' => STATUS_FAILED, 'ack_no' => $txnCode, 'error_msg' => $failedReason);
                        }
                    }
                } elseif (strtolower($dataArr['mode']) == TXN_MODE_CR) {


                    $validator = array(
                        'load_request_id' => $loadRequestId,
                        'customer_master_id' => $customerMasterId,
                        'purse_master_id' => $purseMasterId,
                        'customer_purse_id' => $customerPurseId,
                        'amount' => $amount,
                        'agent_id' => $dataArr['agent_id'],
                        'product_id' => $productId,
                    );
                    $flgValidate = $baseTxn->chkAllowRatMediAssistCardLoad($validator);
                    if ($flgValidate) {
                        $cardLoadData = array(
                            'amount' => (string) $amount,
                            'crn' => $cardNumber,
                            'agentId' => $dataArr['agent_id'], // any data to be provided here, so default ops id
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
                            $retResp = array('status' => STATUS_LOADED, 'ack_no' => $txnCode, 'error_msg' => '');

                            $baseTxnParams = array(
                                'txn_code' => $txnCode,
                                'customer_master_id' => $customerMasterId,
                                'product_id' => $productId,
                                'purse_master_id' => $purseMasterId,
                                'customer_purse_id' => $customerPurseId,
                                'amount' => $amount,
                                'agent_id' => $dataArr['agent_id'],
                                'txn_type' => $txnType,
                            );
                            $baseTxn->successRatMediAssistCardLoad($baseTxnParams);
                            $custPurse = $custPurseModel->getCustBalance($ratCustomerId);
                            $userData = array('last_four' => substr($cardNumber, -4),
                                'product_name' => MEDIASSIST_PRODUCT,
                                'amount' => $amount,
                                'balance' => $custPurse['sum'],
                                'mobile' => $cardholderDetails->mobile,
				'product_id' => $productId,
                            );
                            $resp = $m->cardLoad($userData);
                        } else {
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
                            $this->updateLoadRequests($updateArr, $loadRequestId);
                            $retResp = array('status' => STATUS_FAILED, 'ack_no' => $txnCode, 'error_msg' => $failedReason);
                        }
                    }
                }
            } catch (App_Exception $e) {
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
              $retResp = array('status' => STATUS_FAILED, 'ack_no' => $txnCode, 'error_msg' => $e->getMessage());
          } catch (App_Api_Exception $e) {
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
              $retResp = array('status' => STATUS_FAILED, 'ack_no' => $txnCode, 'error_msg' => $e->getMessage());
          } catch (Exception $e) {
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
              $retResp = array('status' => STATUS_FAILED, 'ack_no' => $txnCode, 'error_msg' => $e->getMessage());
          }
      }
      else {
          $retResp = array('status' => STATUS_FAILED, 'ack_no' => $txnCode, 'error_msg' => $failedReason);
      }
       return $retResp;
    }

    /*
     * getLoadRequests will return the medi assist load requests
     */

    public function getLoadRequests($param) {
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $product = isset($param['product_id']) && $param['product_id'] > 0? $param['product_id'] : '';
        $agent_id = isset($param['agent_id']) ? $param['agent_id'] : '';
        $status = isset($param['status']) ? $param['status'] : '';
        $department = isset($param['department']) ? $param['department'] : '';
        $location = isset($param['location']) ? $param['location'] : '';
        $txnType = isset($param['txn_type']) ? $param['txn_type'] : '';
        $loadChannel = isset($param['load_channel']) ? $param['load_channel'] : '';
        $loadChannelList = isset($param['load_channel_list']) ? $param['load_channel_list'] : '';
        $batchName = isset($param['batch_name']) ? $param['batch_name'] : '';
        $purseMasterId = isset($param['purse_master_id']) ? $param['purse_master_id'] : '';
        $corporateId = isset($param['by_corporate_id']) ? $param['by_corporate_id'] : '';
        $limit = isset($param['limit']) ? $param['limit'] : '';
        $chkAvailable = (isset($param['chk_amount_available']) && $param['chk_amount_available'] != '') ? $param['chk_amount_available'] : '';
        $multiStatus = (isset($param['multi_status']) && $param['multi_status'] != '') ? $param['multi_status'] : '';
        
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`l`.`card_number`,'".$decryptionKey."') as card_number");
        
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($this->_name ." as l",array('id', 'l.bank_id', 'l.product_id', 'l.customer_master_id', 'l.cardholder_id', 'l.purse_master_id', 'l.customer_purse_id', 'l.txn_type', 'l.load_channel', 'l.txn_identifier_type', $card_number, 'l.medi_assist_id', 'l.txn_identifier_num', 'l.employee_id', 'l.amount', 'l.amount_available', 'l.amount_used', 'l.amount_cutoff', 'l.currency', 'l.narration', 'l.wallet_code', 'l.txn_no', 'l.is_reversal', 'l.original_transaction_id', 'l.card_type', 'l.voucher_num', 'l.corporate_id', 'l.mode', 'l.txn_code', 'l.by_agent_id', 'l.by_ops_id', 'l.by_corporate_id', 'l.ip', 'l.batch_name', 'l.date_created', 'l.date_load', 'l.date_failed', 'l.date_cutoff', 'l.txn_load_id', 'l.failed_reason', 'l.date_updated', 'l.status', 'l.status_settlement', 'l.date_settlement', 'l.date_expiry', 'l.settlement_remarks', 'l.settlement_request_id', 'l.settlement_ref_no', 'l.settlement_response_id','l.medi_assist_id as member_id','l.channel','l.fee as fee_amt','l.service_tax'));
        $select->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "l.product_id  = p.id",array('p.name as product_name'))
               ->join(DbTable::TABLE_BANK . " as bank", "p.bank_id = bank.id",array('name as bank_name'))
               ->joinLeft(DbTable::TABLE_RAT_CORP_CARDHOLDER." as rcc", "rcc.id=l.cardholder_id", array('rcc.partner_ref_no'))
               ->joinLeft(DbTable::TABLE_RAT_CORP_CARDHOLDER_DETAILS . " as rhcd", "rhcd.rat_cardholder_id  = l.cardholder_id",array());
        
        if ($product != '') {
            $select->where('l.product_id = ?', $product);
        }
        if ($agent_id != '') {
            $select->where('l.by_agent_id = ?', $agent_id);
        }
        if ($corporateId != '') {
            $select->where('l.by_corporate_id = ?', $corporateId);
        }
        if ($loadChannel != '') {
            $select->where('l.load_channel = ?', $loadChannel);
        }
       if ($loadChannelList != '') {
            $select->where("l.load_channel IN ($loadChannelList)");
        }
        if ($batchName != '') {
            $select->where('l.batch_name = ?', $batchName);
        }
        if ($purseMasterId != '') {
            $select->where('l.purse_master_id = ?', $purseMasterId);
        }
         if ($from != '' && $to != ''){
            $select->where("l.date_created >= '" . $param['from'] . "'");
            $select->where("l.date_created <= '" . $param['to'] . "'");
        }
        if($department!=''){
            $select->where("rcc.employer_name LIKE  '%" . $department . "%'");
        }
        if($location!=''){
            $select->where("rhcd.emp_city LIKE  '%" . $location . "%'");
        }
        if ($txnType != '') {
            $select->where('l.txn_type = ?', $txnType);
        }
        else{
           $select->where("l.txn_type IN ('". TXNTYPE_RAT_CORP_CORPORATE_LOAD."','".TXNTYPE_RAT_CORP_MEDIASSIST_LOAD."','".TXNTYPE_CARD_RELOAD."')");  
        }
        if ($chkAvailable == GREATER){
            $select->where("l.amount_available > 0");
        }
        if($multiStatus != ''){
            $select->where("l.status IN ($multiStatus)");
        } else {
            if($status == STATUS_CUTOFF) {
                $select->where('l.status = ?', STATUS_LOADED);
                $select->where('l.amount_cutoff > ?', 0);
            }elseif($status != ''){
                $select->where('l.status = ?', $status);
            }else{
                $select->where('l.status = ?', STATUS_LOADED);
            }
        }
        
        if ($limit != '') {
            $select->limit($limit);
        } 
        return $this->fetchAll($select);
    }

    public function getDuplicateLoadRequest($batchName, $cardholderId) {
        $select = $this->select()
            ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array("count(*) as num"))
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
                    $retData[$key]['partner_ref_no']  = $data['partner_ref_no'];
                    $retData[$key]['card_number']          = util::maskCard($data['card_number'],4,6);
                    $retData[$key]['medi_assist_id']    = $data['medi_assist_id'];
                    $retData[$key]['amount']      = $data['amount'];
                    $retData[$key]['amount_cutoff']      = $data['amount_cutoff']; 
                    $retData[$key]['fee_amt']      = $data['fee_amt']; 
                    $retData[$key]['service_tax']      = $data['service_tax']; 
                    $retData[$key]['currency']        = $data['currency'];
                    $retData[$key]['narration'] = $data['narration'];
                    $retData[$key]['wallet_code'] = $data['wallet_code'];
                    $retData[$key]['txn_no'] = $data['txn_no'];
                    $retData[$key]['card_type']      = $data['card_type']; 
                    $retData[$key]['corporate_id']      = $data['corporate_id']; 
                    $retData[$key]['mode']      = $data['mode']; 
                    $retData[$key]['txn_code']      = $data['txn_code']; 
                    $retData[$key]['failed_reason']      = $data['failed_reason']; 
                    $retData[$key]['status']      = $data['status']; 
                    $retData[$key]['load_date']      = $data['date_load']; 
                    $retData[$key]['channel']   = ucfirst($data['channel']); 
          }
        }
        
        return $retData;
         
    }
    /*
     * Stats Daily - loads
     */

    public function getStatsDaily($customerPurseId, $curDate, $statusStr) {
        $select = $this->select()
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('count(id) as count', 'sum(amount) as total'))
                ->where('customer_purse_id=?', $customerPurseId);
        if ($statusStr != '') {
            $select->where("status IN ($statusStr)");
        }
        $select->where('original_transaction_id =?', 0);
        $select->where("DATE(date_created) = '" . $curDate . "'")
                ->group("customer_purse_id");

        return $this->fetchRow($select);
    }

    /*
     * Stats Duration - loads
     */

    public function getStatsDuration($customerPurseId, $startDate, $endDate, $statusStr) {
        $select = $this->select()
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('count(id) as count', 'sum(amount) as total'))
                ->where('customer_purse_id=?', $customerPurseId);
        if ($statusStr != '') {
            $select->where("status IN ($statusStr)");
        }
        $select->where('original_transaction_id =?', 0);
        $select->where("DATE(date_created) BETWEEN '" . $startDate . "' AND '" . $endDate . "'")
                ->group("customer_purse_id");

        return $this->fetchRow($select);
    }
    
    public function getBatchName($masterPurseId){
        $select = $this->select()
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('batch_name'))
                ->distinct(TRUE)
                ->where('purse_master_id=?', $masterPurseId);
//       echo $select;exit;
        $row = $this->fetchAll($select);
        return $row;
    }
    
    public function checkFilename($fileName) {
        $select = $this->select();
        $select->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('id'));
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
        $select->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('id'));
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
           //$this->update($data, 'id="'.$id.'"');
           $this->_db->update(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, $data, 'id="'.$id.'"');
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
	    $this->_db->update(DbTable::TABLE_RAT_CORP_LOAD_REQUEST_BATCH, $data, 'batch_name="'.$batch.'"');
	    return true;
        }
        return false;
    }
    
    /*
     * cutoffValidation will revert back the credited claimed amount from cardholder acnt, 
     * if amount not used within time duration
     */
    public function cutoffValidation(){
        
        $custLoadReq = $this->getLoadExpiryRequests(array('status'=>STATUS_LOADED, 'chk_amount_available' => GREATER));
        $custLoadReq = $custLoadReq->toArray();
        $count = count($custLoadReq);
        $retResp = array('cutoff'=> 0, 'not_cutoff'=> 0, 'exception'=>array());
        $masterPurseModel = new MasterPurse();
        $curdate = Date('Y-m-d');
        $cutOffReq = FALSE;
        
        if($count>0){            
           $ecsSocket = new App_Socket_ECS_Transaction();
           $objBaseTxn = new BaseTxn();
                foreach($custLoadReq as $key=>$val){
                  
                    $pursedetail = $masterPurseModel->findById($val['purse_master_id']);
                    // check for time duration 
                    $dateLoad = $val['date_load'];
                    $amtAvailable = $val['amount_available'];
                    
                    // Check date expiry
                    if(!empty($val['date_expiry'])){
                        if(strtotime($val['date_expiry']) <= strtotime($curdate)){
                            $cutOffReq = TRUE;
                        }
                    } else if($pursedetail->load_validity_day > 0 || $pursedetail->load_validity_hr > 0  || $pursedetail->load_validity_min > 0) {
                 
                    $validityParams = array(
                        'load_validity_day' => $pursedetail->load_validity_day,
                        'load_validity_hr' => $pursedetail->load_validity_hr,
                        'load_validity_min' => $pursedetail->load_validity_min,
                    );
                    $cuDate = date('Y-m-d H:i:s');
                    $cutOffValidity = Util::cutoffValidity($validityParams);
                    $timeDiffrence = Util::dateDiff($dateLoad, $cuDate); // difference in secs
                   
                    if($cutOffValidity < $timeDiffrence) {
                        $cutOffReq = TRUE;
                    }
                }
                
                if($cutOffReq == TRUE){
                    try{
                    
                    $apiResp = $ecsSocket->cuttOffReversal(array('crn' => $val['card_number'],
                              'amount' => $amtAvailable,
                              'txn_load_id' => $val['txn_load_id']));
                            if($apiResp === TRUE){

                                $baseTxnType = TXNTYPE_REVERSAL_LOAD; 
                                
                                $paramsBaseTxn = array(
                                                        'bank_id' => $val['bank_id'],
                                                        'customer_master_id' => $val['customer_master_id'],
                                                        'product_id' => $val['product_id'],
                                                        'purse_master_id' => $val['purse_master_id'],
                                                        'customer_purse_id' => $val['customer_purse_id'],
                                                        'txn_code' => $val['txn_code'],
                                                        'amount' => $amtAvailable,
                                                        'agent_id' => $val['by_agent_id'],
                                                        'txn_type' => $baseTxnType
                                                      );
                                
                                $respBaseTxn = $objBaseTxn->cutoffRatCardLoad($paramsBaseTxn);
                                
                                if($respBaseTxn){
                                    $retResp['cutoff'] = $retResp['cutoff'] + 1;
                                    $updLoadArr = array('amount_available' => 0,
                                                'amount_cutoff' => $amtAvailable,
                                                'date_cutoff' => new Zend_Db_Expr('NOW()'));
                                    $this->updateLoadRequests($updLoadArr, $val['id']);
                                    
                                    $detailArr = array(
                                        'product_id' => $val['product_id'],
                                        'load_request_id' => $val['id'],
                                        'txn_processing_id' => 0,
                                        'amount' => $amtAvailable,
                                        'txn_code' => $val['txn_code'],
                                        'txn_type' => $baseTxnType,
                                        'bank_id' => $val['bank_id']
                                    );
                                    $this->insertLoadDetail($detailArr);
                                    
                                }
                            }
                            
                           if(!$apiResp || !$respBaseTxn){
                               $retResp['not_cutoff'] = $retResp['not_cutoff'] + 1;
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
        
        return $retResp;
    }
       
     public function getAgentTotalLoad($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $txnType = isset($param['txn_type']) ? $param['txn_type'] : '';
        $onDate = isset($param['on_date']) ? $param['on_date'] : FALSE;
        $status = isset($param['status']) ? $param['status'] : '';
        $cutoff = isset($param['cutoff']) ? $param['cutoff'] : FALSE;
        
        if ($agentId > 0) {
        //Enable DB Slave
            $this->_enableDbSlave();

            $select = $this->select();
            if($cutoff){
                $select->from($this->_name. ' as clReq', array('sum(clReq.amount_cutoff) as total_agent_load_amount'));
            } else {
                $select->from($this->_name. ' as clReq', array('sum(clReq.amount) as total_agent_load_amount', 'sum(clReq.fee) as agent_total_load_fee', 'sum(clReq.service_tax) as agent_total_load_stax'));
            }   
            $select->join(
                    DbTable::TABLE_PURSE_MASTER.' as pm',
                    "pm.id = clReq.purse_master_id",array()
            );
            $select->where('pm.is_virtual = ?', FLAG_NO);
            
            if($agentId != ''){
                $select->where('clReq.by_agent_id = ?', $agentId);
            }
            if($status != ''){
                $select->where("clReq.status IN ('".$status."')");
            } else {
                $select->where("clReq.status = ?", STATUS_LOADED);
            }
            $select->where('clReq.original_transaction_id = 0');
            if ($txnType != '') {
                $select->where('clReq.txn_type = ?', $txnType);
            } else{
                $select->where("clReq.txn_type IN ('". TXNTYPE_RAT_CORP_CORPORATE_LOAD."','".TXNTYPE_RAT_CORP_MEDIASSIST_LOAD."','".TXNTYPE_CARD_RELOAD."')");  
            }
            if($cutoff){
                if ($onDate) {
                    $date = isset($param['date']) ? $param['date'] : '';
                    $select->where('DATE(clReq.date_cutoff) =?', $date);
                } else {
                    $fromDate = isset($param['from']) ? $param['from'] : '';
                    $toDate = isset($param['to']) ? $param['to'] : '';
                    $select->where('clReq.date_cutoff >= ?', $fromDate);
                    $select->where('clReq.date_cutoff <= ?', $toDate);
                }
            } else {
                if ($onDate) {
                    $date = isset($param['date']) ? $param['date'] : '';
                    $select->where('DATE(clReq.date_created) =?', $date);
                } else {
                    $fromDate = isset($param['from']) ? $param['from'] : '';
                    $toDate = isset($param['to']) ? $param['to'] : '';
                    $select->where('clReq.date_created >= ?', $fromDate);
                    $select->where('clReq.date_created <= ?', $toDate);
                }
            }
            $row = $this->fetchRow($select);
            //Disable DB Slave
            $this->_disableDbSlave();
            return $row;
        }
        else
            return 0;
    }
     
    public function getAgentAllLoad($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : ''; 
        $status = isset($param['status']) ? $param['status'] : ''; 
        $txnType = isset($param['txn_type']) ? $param['txn_type'] : ''; 
        
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
                $select->where("status = ?", STATUS_LOADED);
            }
            if ($txnType != '') {
                $select->where('txn_type = ?', $txnType);
            } else{
                $select->where(
                            "txn_type IN ('". 
                                TXNTYPE_RAT_CORP_CORPORATE_LOAD."','". 
                                TXNTYPE_RAT_CORP_MEDIASSIST_LOAD."','".
                                TXNTYPE_CARD_RELOAD
                        ."')");
            }
            $select->where("DATE(date_created) BETWEEN '". $from ."' AND '". $to ."'"); 
            $select->group('DATE_FORMAT(date_created, "%Y-%m-%d")');
            return $this->_db->fetchAll($select);
        } else
            return '';
    }
    
    public function getWalletTxn($param) //bene_act_no
    {
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $productId = isset($param['product_id']) && $param['product_id'] > 0 ? $param['product_id'] : '';
        $bankUnicode = isset($param['bank_unicode']) ? $param['bank_unicode'] : '';
        $wallet_type = isset($param['wallet_type']) ? $param['wallet_type'] : '';
        $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");
        $STATUS = Zend_Registry::get("REMIT_STATUS");
        $purseModel = new MasterPurse();
        $objectRelation = new ObjectRelations();
        $productModel = new Products();
        $arrReport = array();
        $productIdArr = array();
        $allProducts = FALSE;
        $bankObj = new Banks();    
        $bank = $bankObj->getBankbyUnicode($bankUnicode);
            
        if(empty($productId)) {
            
            $product = $productModel->getBankProducts($bank['id']);
            
            foreach($product as $pid => $pname){
                $productIdArr[] = $pid;
            }
            
            $productId = implode(',', $productIdArr);
            $allProducts = TRUE;
            $productDigi->program_type = '';
        } else {
            $productDigi = $productModel->getProductInfo($productId);
        }
                
        $productRatnakar = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CORP);
        $productRatUnicode = $productRatnakar->product->unicode;
        $productRat = $productModel->getProductInfoByUnicode($productRatUnicode);
        
        $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CORP);
        $purseCodeHr = $product->purse->code->corporatehr; 
        $purseCodeIns = $product->purse->code->corporateins; 
        $purseCodeGen = $product->purse->code->genwallet; 
        $purseDetailsHr = $purseModel->getPurseIdByPurseCode($purseCodeHr);
        $purseDetailsIns = $purseModel->getPurseIdByPurseCode($purseCodeIns);
        $purseDetailsGen = $purseModel->getPurseIdByPurseCode($purseCodeGen);
        
        //Enable DB Slave
        $this->_enableDbSlave();
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`l`.`card_number`,'".$decryptionKey."') as card_number");
        $select = $this->select();
        $select->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST . " as l", array('date_load as date_created', 'l.amount as tot_amount', $card_number, 'txn_type', 'status', 'product_id', new Zend_Db_Expr("CASE l.txn_type WHEN '".TXNTYPE_RAT_CORP_CORPORATE_LOAD."' THEN l.amount_cutoff ELSE 0 END as medi_wallet_hr_dr"), new Zend_Db_Expr("CASE l.txn_type WHEN '".TXNTYPE_RAT_CORP_CORPORATE_LOAD."' THEN l.amount ELSE 0 END as medi_wallet_hr_cr"), new Zend_Db_Expr("CASE l.txn_type WHEN '".TXNTYPE_RAT_CORP_MEDIASSIST_LOAD."' THEN l.amount_cutoff ELSE 0 END as medi_wallet_ins_dr"), new Zend_Db_Expr("CASE l.txn_type WHEN '".TXNTYPE_RAT_CORP_MEDIASSIST_LOAD."' THEN l.amount ELSE 0 END as medi_wallet_ins_cr"), new Zend_Db_Expr("CASE l.txn_type WHEN '".TXNTYPE_CARD_RELOAD."' THEN l.amount_cutoff ELSE 0 END as medi_wallet_gen_dr"), new Zend_Db_Expr("CASE l.txn_type WHEN '".TXNTYPE_CARD_RELOAD."' THEN l.amount ELSE 0 END as medi_wallet_gen_cr"), new Zend_Db_Expr("CASE l.txn_type WHEN '".TXNTYPE_CARD_RELOAD."' THEN l.amount_cutoff ELSE l.amount END as wallet_hr_dr"), new Zend_Db_Expr("CASE l.txn_type WHEN '".TXNTYPE_CARD_RELOAD."' THEN l.amount ELSE 0 END as wallet_hr_cr"), new Zend_Db_Expr("'0' as wallet_ins_dr"), new Zend_Db_Expr("'0' as wallet_ins_cr"), new Zend_Db_Expr("'0' as wallet_gen_dr"), new Zend_Db_Expr("'0' as wallet_gen_cr"), 'amount_cutoff', 'txn_no', 'txn_code', new Zend_Db_Expr("CASE l.status WHEN '".STATUS_FAILED."' THEN l.failed_reason ELSE '-' END as failed_reason"), 'load_channel','channel', 'mode', 'narration', new Zend_Db_Expr("'' as purse_master_id"), 'is_reversal as rev_indicator', 'date_reversal', 'original_transaction_id as original_transaction_id', new Zend_Db_Expr("'' as mcc_code"), new Zend_Db_Expr("'' as tid"), new Zend_Db_Expr("'' as mid"),'status_settlement','date_settlement','settlement_ref_no','l.fee as fee_amt','l.service_tax'));
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_RAT_DEBIT_DETAIL . ' as rdd', "l.id = rdd.debit_id",array('rdd.amount as debit_amount'));
        $select->joinLeft(DbTable::TABLE_RATNAKAR_SETTLEMENT_RESPONSE . ' as resp', "resp.id = l.settlement_response_id ",array('bene_act_no','bene_act_name'));
         $select->joinLeft(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id = l.purse_master_id OR pm.id = rdd.purse_master_id",array('code as wlt_code'));
        $select->joinLeft(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as holder", "l.cardholder_id = holder.id",array(new Zend_Db_Expr("'' as rat_card_number"), 'card_pack_id', 'medi_assist_id', 'partner_ref_no'));
        $select->join(DbTable::TABLE_PRODUCTS . " as product", "l.product_id = product.id",array('name as product_name', 'bank_id'));
        $select->join(DbTable::TABLE_BANK . " as bank", "product.bank_id = bank.id",array('name as bank_name'));
	$select->joinLeft(DbTable::TABLE_BLOCK_AMOUNT . " as ba", "ba.claim_txn_code = l.txn_code", array(
	    'DATE_FORMAT(ba.date_created, "%d-%m-%Y %H:%i:%s") as date_blocked',
	    'DATE_FORMAT(ba.date_unblocked, "%d-%m-%Y %H:%i:%s") as date_unblocked' ));
        if ($productId >0) {
            $select->where("l.product_id IN ( $productId )");
        }
        if ($bank['id'] > 0) {
            $select->where("bank.id = ? " , $bank['id']);
        }
        if (!empty($wallet_type)) {
            $select->where("pm.is_virtual = ? " , $wallet_type);
        }
        if ($from != '' && $to != ''){
            $select->where("l.date_load >=  ? " , $from );
            $select->where("l.date_load <= ? " , $to );
            $select->where("l.status IN  ('" . STATUS_LOADED . "', '".STATUS_FAILED."', '".STATUS_DEBITED."')");
        }
                
        $card_num = new Zend_Db_Expr("AES_DECRYPT(`auth`.`card_number`,'".$decryptionKey."') as card_number"); 
        $select_2 = $this->select();
        $select_2->from(DbTable::TABLE_CARD_AUTH_REQUEST . " as auth", array('date_created', "amount_txn as tot_amount", $card_num, 'txn_type', 'status', 'product_id', new Zend_Db_Expr("CASE WHEN (auth.purse_master_id='".$purseDetailsHr['id']."' AND auth.rev_indicator='n') THEN auth.amount_txn ELSE 0 END as medi_wallet_hr_dr"), new Zend_Db_Expr("CASE WHEN (auth.purse_master_id='".$purseDetailsHr['id']."' AND auth.rev_indicator='y') THEN auth.amount_txn ELSE auth.amount_txn END as medi_wallet_hr_cr"), new Zend_Db_Expr("CASE WHEN (auth.purse_master_id='".$purseDetailsIns['id']."' AND auth.rev_indicator='n') THEN auth.amount_txn ELSE 0 END as medi_wallet_ins_dr"), new Zend_Db_Expr("CASE WHEN (auth.purse_master_id='".$purseDetailsIns['id']."' AND auth.rev_indicator='y') THEN auth.amount_txn ELSE 0 END as medi_wallet_ins_cr"), new Zend_Db_Expr("CASE WHEN (auth.purse_master_id='".$purseDetailsGen['id']."' AND auth.rev_indicator='n') THEN auth.amount_txn ELSE 0 END as medi_wallet_gen_dr"), new Zend_Db_Expr("CASE WHEN (auth.purse_master_id='".$purseDetailsGen['id']."' AND auth.rev_indicator='y') THEN auth.amount_txn ELSE 0 END as medi_wallet_gen_cr"), new Zend_Db_Expr("CASE auth.rev_indicator WHEN 'n' THEN auth.amount_txn ELSE 0 END as wallet_hr_dr"), new Zend_Db_Expr("CASE auth.rev_indicator WHEN 'y' THEN auth.amount_txn WHEN 'n' THEN auth.amount_txn ELSE 0 END as wallet_hr_cr"), new Zend_Db_Expr("'0' as wallet_ins_dr"), new Zend_Db_Expr("'0' as wallet_ins_cr"), new Zend_Db_Expr("'0' as wallet_gen_dr"), new Zend_Db_Expr("'0' as wallet_gen_cr"), new Zend_Db_Expr("'' as amount_cutoff"), 'txn_no', 'txn_code', new Zend_Db_Expr("CASE auth.status WHEN '".STATUS_FAILED."' THEN auth.failed_reason ELSE '-' END as failed_reason"), new Zend_Db_Expr("'' as load_channel"), new Zend_Db_Expr("'API' as channel"), 'mode', 'narration', 'purse_master_id', new Zend_Db_Expr("CASE auth.rev_indicator WHEN 'y' THEN '".ucfirst(FLAG_YES)."' ELSE '".ucfirst(FLAG_NO)."' END as failed_reason"),  'date_reversal', new Zend_Db_Expr("'' as original_transaction_id"), 'mcc_code', 'tid', 'mid', new Zend_Db_Expr("'' as status_settlement"),new Zend_Db_Expr("'' as date_settlement"),new Zend_Db_Expr("'' as settlement_ref_no"), new Zend_Db_Expr("'' as fee_amt"), new Zend_Db_Expr("'' as service_tax"),new Zend_Db_Expr("'' as debit_amount"), new Zend_Db_Expr("'' as bene_act_no"),new Zend_Db_Expr("'' as bene_act_name")));
        $select_2->setIntegrityCheck(false);
        $select_2->joinLeft(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id = auth.purse_master_id ",array('code as wlt_code'));
        $select_2->joinLeft(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as holder", "auth.cardholder_id = holder.id",array(new Zend_Db_Expr("'' as rat_card_number"), 'card_pack_id', 'medi_assist_id', 'partner_ref_no'));
        $select_2->join(DbTable::TABLE_PRODUCTS . " as product", "auth.product_id = product.id",array('name as product_name', 'bank_id'));
        $select_2->join(DbTable::TABLE_BANK . " as bank", "product.bank_id = bank.id",array('name as bank_name'));
	$select_2->joinLeft(DbTable::TABLE_BLOCK_AMOUNT . " as ba", "ba.claim_txn_code = auth.txn_code", array(
	    'DATE_FORMAT(ba.date_created, "%d-%m-%Y %H:%i:%s") as date_blocked',
	    'DATE_FORMAT(ba.date_unblocked, "%d-%m-%Y %H:%i:%s") as date_unblocked' ));
        if ($productId > 0) {
            $select_2->where("auth.product_id IN ( $productId )");
        }
        if ($bank['id'] > 0) {
            $select_2->where("bank.id = ? ", $bank['id']);
        }
        if (!empty($wallet_type)) {
            $select_2->where("pm.is_virtual = ? " , $wallet_type);
        }
        if ($from != '' && $to != ''){
            $select_2->where("auth.date_created >=  ? " , $from );
            $select_2->where("auth.date_created <= ? " , $to );
            $select_2->where("auth.status IN  ('" . STATUS_COMPLETED . "', '".STATUS_FAILED."', '".STATUS_REVERSED."')");
        }        
        $card_num_ad = new Zend_Db_Expr("AES_DECRYPT(`adj`.`card_number`,'".$decryptionKey."') as card_number"); 
        $select_3 = $this->select();
        $select_3->from(DbTable::TABLE_BATCH_ADJUSTMENT . " as adj", array('date_created', "amount as tot_amount", $card_num_ad, 'txn_type', 'status', 'product_id', new Zend_Db_Expr("CASE WHEN (adj.purse_master_id='".$purseDetailsHr['id']."' AND adj.mode='".TXN_MODE_DR."') THEN adj.amount ELSE 0 END as medi_wallet_hr_dr"), new Zend_Db_Expr("CASE WHEN (adj.purse_master_id='".$purseDetailsHr['id']."' AND adj.mode='".TXN_MODE_CR."') THEN adj.amount ELSE adj.amount END as medi_wallet_hr_cr"), new Zend_Db_Expr("CASE WHEN (adj.purse_master_id='".$purseDetailsIns['id']."' AND adj.mode='".TXN_MODE_DR."') THEN adj.amount ELSE 0 END as medi_wallet_ins_dr"), new Zend_Db_Expr("CASE WHEN (adj.purse_master_id='".$purseDetailsIns['id']."' AND adj.mode='".TXN_MODE_CR."') THEN adj.amount ELSE 0 END as medi_wallet_ins_cr"), new Zend_Db_Expr("CASE WHEN (adj.purse_master_id='".$purseDetailsGen['id']."' AND adj.mode='".TXN_MODE_DR."') THEN adj.amount ELSE 0 END as medi_wallet_gen_dr"), new Zend_Db_Expr("CASE WHEN (adj.purse_master_id='".$purseDetailsGen['id']."' AND adj.mode='".TXN_MODE_CR."') THEN adj.amount ELSE 0 END as medi_wallet_gen_cr"), new Zend_Db_Expr("CASE adj.mode WHEN '".TXN_MODE_DR."' THEN adj.amount ELSE 0 END as wallet_hr_dr"), new Zend_Db_Expr("CASE adj.mode WHEN '".TXN_MODE_CR."' THEN adj.amount ELSE adj.amount END as wallet_hr_cr"), new Zend_Db_Expr("'0' as wallet_ins_dr"), new Zend_Db_Expr("'0' as wallet_ins_cr"), new Zend_Db_Expr("'0' as wallet_gen_dr"), new Zend_Db_Expr("'0' as wallet_gen_cr"), new Zend_Db_Expr("'' as amount_cutoff"), 'rrn as txn_no', 'txn_code', new Zend_Db_Expr("CASE adj.status WHEN '".STATUS_FAILED."' THEN adj.failed_reason ELSE '-' END as failed_reason"), new Zend_Db_Expr("'' as load_channel"), new Zend_Db_Expr("'OPS' as channel"), 'mode', 'narration', 'purse_master_id', new Zend_Db_Expr("'' as rev_indicator"), new Zend_Db_Expr("'' as date_reversal"), new Zend_Db_Expr("'' as original_transaction_id"), new Zend_Db_Expr("'' as mcc_code"), new Zend_Db_Expr("'' as tid"), new Zend_Db_Expr("'' as mid"), new Zend_Db_Expr("'' as status_settlement"),new Zend_Db_Expr("'' as date_settlement"),new Zend_Db_Expr("'' as settlement_ref_no"), new Zend_Db_Expr("'' as fee_amt"), new Zend_Db_Expr("'' as service_tax"), new Zend_Db_Expr("'' as debit_amount"), new Zend_Db_Expr("'' as bene_act_no"),new Zend_Db_Expr("'' as bene_act_name")));
        $select_3->setIntegrityCheck(false);
        $select_3->joinLeft(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id = adj.purse_master_id ",array('code as wlt_code'));
        $select_3->joinLeft(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as holder", "adj.cardholder_id = holder.id",array(new Zend_Db_Expr("'' as rat_card_number"), 'card_pack_id', 'medi_assist_id', 'partner_ref_no'));
        $select_3->join(DbTable::TABLE_PRODUCTS . " as product", "adj.product_id = product.id",array('name as product_name', 'bank_id'));
        $select_3->join(DbTable::TABLE_BANK . " as bank", "product.bank_id = bank.id",array('name as bank_name'));
	$select_3->joinLeft(DbTable::TABLE_BLOCK_AMOUNT . " as ba", "ba.claim_txn_code = adj.txn_code", array(
	    'DATE_FORMAT(ba.date_created, "%d-%m-%Y %H:%i:%s") as date_blocked',
	    'DATE_FORMAT(ba.date_unblocked, "%d-%m-%Y %H:%i:%s") as date_unblocked' ));
        if ($productId > 0) {
            $select_3->where("adj.product_id IN ( $productId )");
        }
        if ($bank['id'] > 0) {
            $select_3->where("bank.id = '".$bank['id']."'");
        }
        if (!empty($wallet_type)) {
            $select_3->where("pm.is_virtual = ? " , $wallet_type);
        }
        if ($from != '' && $to != ''){
            $select_3->where("adj.date_created >=  '" . $from . "'");
            $select_3->where("adj.date_created <= '" . $to . "'");
            $select_3->where("adj.status IN  ('" . STATUS_SUCCESS . "', '".STATUS_FAILED."')");
        }

        $union = $this->_db->select()->union(array($select, $select_2, $select_3));

        if($allProducts == TRUE || $productDigi->program_type == PROGRAM_TYPE_DIGIWALLET) {
            $objRelationTypeId = $objectRelation->getRelationTypeId(RAT_MAPPER); 
            $select_4 = $this->select();
            $card_num_rat = new Zend_Db_Expr("AES_DECRYPT(`holder`.`card_number`,'".$decryptionKey."') as rat_card_number");
	    $decryptionKey = App_DI_Container::get('DbConfig')->key;
	    $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'" . $decryptionKey . "') as `bene_act_no`");
            
            $select_4->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as rr", array('date_created', "amount as tot_amount", new Zend_Db_Expr("'' as card_number"), new Zend_Db_Expr("CASE rr.status WHEN '".STATUS_REFUND."' THEN '".TXNTYPE_REMITTANCE."' ELSE '".TXNTYPE_REMITTANCE."' END as txn_type"), 'status', 'product_id', new Zend_Db_Expr("'0' as medi_wallet_hr_dr"), new Zend_Db_Expr("'0' as medi_wallet_hr_cr"), new Zend_Db_Expr("'0' as medi_wallet_ins_dr"), new Zend_Db_Expr("'0' as medi_wallet_ins_cr"), new Zend_Db_Expr("'0' as medi_wallet_gen_dr"), new Zend_Db_Expr("'0' as medi_wallet_gen_cr"), 'amount as wallet_hr_dr', new Zend_Db_Expr("CASE rr.status WHEN '".STATUS_REFUND."' THEN rr.amount ELSE rr.amount END as wallet_hr_cr"), new Zend_Db_Expr("'0' as wallet_ins_dr"), new Zend_Db_Expr("'0' as wallet_ins_cr"), new Zend_Db_Expr("'0' as wallet_gen_dr"), new Zend_Db_Expr("'0' as wallet_gen_cr"), new Zend_Db_Expr("'' as amount_cutoff"), 'txnrefnum as txn_no', 'txn_code', new Zend_Db_Expr("'' as failed_reason"), new Zend_Db_Expr("'' as load_channel"),"channel",new Zend_Db_Expr("'".TXN_MODE_DR."' as mode"), 'sender_msg as narration', new Zend_Db_Expr("'' as purse_master_id"), new Zend_Db_Expr("'' as rev_indicator"), new Zend_Db_Expr("'' as date_reversal"), new Zend_Db_Expr("'' as original_transaction_id"), new Zend_Db_Expr("'' as mcc_code"), new Zend_Db_Expr("'' as tid"), new Zend_Db_Expr("'' as mid"), new Zend_Db_Expr("'' as status_settlement"),new Zend_Db_Expr("'' as date_settlement"),new Zend_Db_Expr("'' as settlement_ref_no"), "fee as fee_amt",'service_tax', new Zend_Db_Expr("'' as debit_amount"))); 

            $select_4->setIntegrityCheck(false);   
	    $select_4->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", "rr.beneficiary_id =b.id and b.status = '" . STATUS_ACTIVE . "'", array($bankAccountNumber,'b.name AS bene_act_name'));
            $select_4->join(DbTable::TABLE_BIND_OBJECT_RELATION . " as or", "rr.remitter_id = or.to_object_id", array());
            $select_4->joinLeft(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id = rr.purse_master_id ",array('code as wlt_code'));
            $select_4->join(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as holder", "holder.id = or.from_object_id AND or.object_relation_type_id=$objRelationTypeId AND holder.product_id = rr.product_id", array($card_num_rat, 'card_pack_id', 'medi_assist_id', 'partner_ref_no'));   
            $select_4->join(DbTable::TABLE_PRODUCTS . " as product", "rr.product_id = product.id",array('name as product_name', 'bank_id'));
            $select_4->join(DbTable::TABLE_BANK . " as bank", "product.bank_id = bank.id",array('name as bank_name'));
	$select_4->joinLeft(DbTable::TABLE_BLOCK_AMOUNT . " as ba", "ba.claim_txn_code = rr.txn_code", array(
	    'DATE_FORMAT(ba.date_created, "%d-%m-%Y %H:%i:%s") as date_blocked',
	    'DATE_FORMAT(ba.date_unblocked, "%d-%m-%Y %H:%i:%s") as date_unblocked' ));

            if ($productId > 0) {
                $select_4->where("rr.product_id IN ( $productId )");
            }
            if (!empty($wallet_type)) {
                $select_4->where("pm.is_virtual = ? " , $wallet_type);
            }
            if ($bank['id'] > 0) {
                $select_4->where("bank.id = ? ", $bank['id']);
            }
            if ($from != '' && $to != ''){
                $select_4->where("rr.date_created >=  ? " , $from );
                $select_4->where("rr.date_created <=  ?" , $to );
                //$select_4->where("rr.status IN  ('" . STATUS_IN_PROCESS . "', '".STATUS_PROCESSED."', '".STATUS_FAILURE."', '".STATUS_SUCCESS."', '".STATUS_HOLD."', '".STATUS_INCOMPLETE."')");
            }

            $union = $this->_db->select()->union(array($union, $select_4));
            
            // Wallet Transfer From
            $card_num_rat = new Zend_Db_Expr("AES_DECRYPT(`holder`.`card_number`,'".$decryptionKey."') as rat_card_number");
            $select_5 = $this->select();
            $select_5->from(DbTable::TABLE_RATNAKAR_WALLET_TRANSFER . " as rr", array('date_created', "amount as tot_amount", new Zend_Db_Expr("'' as card_number"), 'txn_type', 'status', 'product_id', new Zend_Db_Expr("'0' as medi_wallet_hr_dr"), new Zend_Db_Expr("'0' as medi_wallet_hr_cr"), new Zend_Db_Expr("'0' as medi_wallet_ins_dr"), new Zend_Db_Expr("'0' as medi_wallet_ins_cr"), new Zend_Db_Expr("'0' as medi_wallet_gen_dr"), new Zend_Db_Expr("'0' as medi_wallet_gen_cr"), 'amount as wallet_hr_dr', 'amount as wallet_hr_cr', new Zend_Db_Expr("'0' as wallet_ins_dr"), new Zend_Db_Expr("'0' as wallet_ins_cr"), new Zend_Db_Expr("'0' as wallet_gen_dr"), new Zend_Db_Expr("'0' as wallet_gen_cr"), new Zend_Db_Expr("'' as amount_cutoff"), 'txnrefnum as txn_no', 'txn_code', new Zend_Db_Expr("'' as failed_reason"), new Zend_Db_Expr("'' as load_channel"), new Zend_Db_Expr("'API' as channel"), new Zend_Db_Expr("'".TXN_MODE_DR."' as mode"), 'narration', new Zend_Db_Expr("'' as purse_master_id"), new Zend_Db_Expr("'' as rev_indicator"), new Zend_Db_Expr("'' as date_reversal"), new Zend_Db_Expr("'' as original_transaction_id"), new Zend_Db_Expr("'' as mcc_code"), new Zend_Db_Expr("'' as tid"), new Zend_Db_Expr("'' as mid"), new Zend_Db_Expr("'' as status_settlement"),new Zend_Db_Expr("'' as date_settlement"),new Zend_Db_Expr("'' as settlement_ref_no"), new Zend_Db_Expr("'' as fee_amt"), new Zend_Db_Expr("'' as service_tax"), new Zend_Db_Expr("'' as debit_amount"),new Zend_Db_Expr("'' as bene_act_no"),new Zend_Db_Expr("'' as bene_act_name"))); 
            $select_5->setIntegrityCheck(false);
            $select_5->join(DbTable::TABLE_RAT_CUSTOMER_PURSE. " as rcp", "rcp.id = rr.customer_purse_id ",array());
            $select_5->joinLeft(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id=rcp.purse_master_id ",array('code as wlt_code'));
            $select_5->join(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as holder", "holder.rat_customer_id = rr.rat_customer_id", array($card_num_rat, 'card_pack_id', 'medi_assist_id', 'partner_ref_no'));   
            $select_5->join(DbTable::TABLE_PRODUCTS . " as product", "rr.product_id = product.id",array('name as product_name', 'bank_id'));
            $select_5->join(DbTable::TABLE_BANK . " as bank", "product.bank_id = bank.id",array('name as bank_name'));
	    $select_5->joinLeft(DbTable::TABLE_BLOCK_AMOUNT . " as ba", "ba.claim_txn_code = rr.txn_code", array(
	    'DATE_FORMAT(ba.date_created, "%d-%m-%Y %H:%i:%s") as date_blocked',
	    'DATE_FORMAT(ba.date_unblocked, "%d-%m-%Y %H:%i:%s") as date_unblocked' ));

            if ($productId > 0) {
                $select_5->where("rr.product_id IN ( $productId )");
            }
            if (!empty($wallet_type)) {
                $select_5->where("pm.is_virtual = ? " , $wallet_type);
            }
            if ($from != '' && $to != ''){
                $select_5->where("rr.date_created >=  ? " , $from );
                $select_5->where("rr.date_created <=  ?" , $to );
                $select_5->where("rr.status = ?", STATUS_SUCCESS);
                $select_5->where("rr.txn_type = ?", TXNTYPE_WALLET_TOWALLET_FUND_TRANSFER);
            }
            
            $union = $this->_db->select()->union(array($union, $select_5));
            
            // Wallet Transfer To
            $card_num_rat = new Zend_Db_Expr("AES_DECRYPT(`holder`.`card_number`,'".$decryptionKey."') as rat_card_number");
            $select_6 = $this->select();
            $select_6->from(DbTable::TABLE_RATNAKAR_WALLET_TRANSFER . " as rr", array('date_created', "amount as tot_amount", new Zend_Db_Expr("'' as card_number"), 'txn_type', 'status', 'product_id', new Zend_Db_Expr("'0' as medi_wallet_hr_dr"), new Zend_Db_Expr("'0' as medi_wallet_hr_cr"), new Zend_Db_Expr("'0' as medi_wallet_ins_dr"), new Zend_Db_Expr("'0' as medi_wallet_ins_cr"), new Zend_Db_Expr("'0' as medi_wallet_gen_dr"), new Zend_Db_Expr("'0' as medi_wallet_gen_cr"), new Zend_Db_Expr("'0' as wallet_hr_dr"), 'amount as wallet_hr_cr', new Zend_Db_Expr("'0' as wallet_ins_dr"), new Zend_Db_Expr("'0' as wallet_ins_cr"), new Zend_Db_Expr("'0' as wallet_gen_dr"), new Zend_Db_Expr("'0' as wallet_gen_cr"), new Zend_Db_Expr("'' as amount_cutoff"), 'txnrefnum as txn_no', 'txn_code', new Zend_Db_Expr("'' as failed_reason"), new Zend_Db_Expr("'' as load_channel"), new Zend_Db_Expr("'API' as channel"), new Zend_Db_Expr("'".TXN_MODE_CR."' as mode"), 'narration', new Zend_Db_Expr("'' as purse_master_id"), new Zend_Db_Expr("'' as rev_indicator"), new Zend_Db_Expr("'' as date_reversal"), new Zend_Db_Expr("'' as original_transaction_id"), new Zend_Db_Expr("'' as mcc_code"), new Zend_Db_Expr("'' as tid"), new Zend_Db_Expr("'' as mid"), new Zend_Db_Expr("'' as status_settlement"),new Zend_Db_Expr("'' as date_settlement"),new Zend_Db_Expr("'' as settlement_ref_no"), new Zend_Db_Expr("'' as fee_amt"), new Zend_Db_Expr("'' as service_tax"), new Zend_Db_Expr("'' as debit_amount"),new Zend_Db_Expr("'' as bene_act_no"),new Zend_Db_Expr("'' as bene_act_name"))); 
            $select_6->setIntegrityCheck(false);
            $select_6->join(DbTable::TABLE_RAT_CUSTOMER_PURSE. " as rcp", "rcp.id = rr.txn_customer_purse_id ",array());
            $select_6->joinLeft(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id=rcp.purse_master_id ",array('code as wlt_code'));
            $select_6->join(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as holder", "holder.rat_customer_id = rr.txn_rat_customer_id", array($card_num_rat, 'card_pack_id', 'medi_assist_id', 'partner_ref_no'));   
            $select_6->join(DbTable::TABLE_PRODUCTS . " as product", "rr.product_id = product.id",array('name as product_name', 'bank_id'));
            $select_6->join(DbTable::TABLE_BANK . " as bank", "product.bank_id = bank.id",array('name as bank_name'));
	    $select_6->joinLeft(DbTable::TABLE_BLOCK_AMOUNT . " as ba", "ba.claim_txn_code = rr.txn_code", array(
		'DATE_FORMAT(ba.date_created, "%d-%m-%Y %H:%i:%s") as date_blocked',
		'DATE_FORMAT(ba.date_unblocked, "%d-%m-%Y %H:%i:%s") as date_unblocked' ));
	    
            if ($productId > 0) {
                $select_6->where("rr.product_id IN ( $productId )");
            }
            if (!empty($wallet_type)) {
                $select_6->where("pm.is_virtual = ? " , $wallet_type);
            }
            if ($from != '' && $to != ''){
                $select_6->where("rr.date_created >=  ? " , $from );
                $select_6->where("rr.date_created <=  ?" , $to );
                $select_6->where("rr.status = ?", STATUS_SUCCESS);
                $select_6->where("rr.txn_type = ?", TXNTYPE_WALLET_TOWALLET_FUND_TRANSFER);
            }
            
            $union = $this->_db->select()->union(array($union, $select_6));
            
            //Remittance Refund
            $objRelationTypeId = $objectRelation->getRelationTypeId(RAT_MAPPER); 
	    $decryptionKey = App_DI_Container::get('DbConfig')->key;
	    $bankAccountNumber = new Zend_Db_Expr("AES_DECRYPT(`b`.`bank_account_number`,'" . $decryptionKey . "') as `bene_act_no`");
            $select_7 = $this->select();

            $card_num_rat = new Zend_Db_Expr("AES_DECRYPT(`holder`.`card_number`,'".$decryptionKey."') as rat_card_number");
            $select_7->from(DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND . " as rr", array('date_created', "amount as tot_amount", new Zend_Db_Expr("'' as card_number"), new Zend_Db_Expr("'".TXNTYPE_REMITTANCE_REFUND."' as txn_type"), 'status', 'product_id', new Zend_Db_Expr("'0' as medi_wallet_hr_dr"), new Zend_Db_Expr("'0' as medi_wallet_hr_cr"), new Zend_Db_Expr("'0' as medi_wallet_ins_dr"), new Zend_Db_Expr("'0' as medi_wallet_ins_cr"), new Zend_Db_Expr("'0' as medi_wallet_gen_dr"), new Zend_Db_Expr("'0' as medi_wallet_gen_cr"), 'amount as wallet_hr_dr', 'amount as wallet_hr_cr', new Zend_Db_Expr("'0' as wallet_ins_dr"), new Zend_Db_Expr("'0' as wallet_ins_cr"), new Zend_Db_Expr("'0' as wallet_gen_dr"), new Zend_Db_Expr("'0' as wallet_gen_cr"), new Zend_Db_Expr("'' as amount_cutoff"), new Zend_Db_Expr("'' as txn_no"), 'txn_code', new Zend_Db_Expr("'' as failed_reason"), new Zend_Db_Expr("'' as load_channel"),"channel", new Zend_Db_Expr("'".TXN_MODE_CR."' as mode"), new Zend_Db_Expr("'' as narration"), 'purse_master_id', new Zend_Db_Expr("'' as rev_indicator"), new Zend_Db_Expr("'' as date_reversal"), new Zend_Db_Expr("'' as original_transaction_id"), new Zend_Db_Expr("'' as mcc_code"), new Zend_Db_Expr("'' as tid"), new Zend_Db_Expr("'' as mid"), new Zend_Db_Expr("'' as status_settlement"),new Zend_Db_Expr("'' as date_settlement"),new Zend_Db_Expr("'' as settlement_ref_no")));  
            $select_7->setIntegrityCheck(false);
            $select_7->join(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST . " as req", "req.id = rr.remittance_request_id", array("fee as fee_amt","service_tax", new Zend_Db_Expr("'' as debit_amount")));
	    $select_7->joinLeft(DbTable::TABLE_RATNAKAR_BENEFICIARIES . " as b", "req.beneficiary_id =b.id and b.status = '" . STATUS_ACTIVE . "'", array($bankAccountNumber,'b.name AS bene_act_name'));
            $select_7->join(DbTable::TABLE_BIND_OBJECT_RELATION . " as or", "rr.remitter_id = or.to_object_id", array());
            $select_7->joinLeft(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id = rr.purse_master_id ",array('code as wlt_code'));
            $select_7->join(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as holder", "holder.id = or.from_object_id AND or.object_relation_type_id=$objRelationTypeId AND holder.product_id = rr.product_id", array($card_num_rat, 'card_pack_id', 'medi_assist_id', 'partner_ref_no'));   
            $select_7->join(DbTable::TABLE_PRODUCTS . " as product", "rr.product_id = product.id",array('name as product_name', 'bank_id'));
            $select_7->join(DbTable::TABLE_BANK . " as bank", "product.bank_id = bank.id",array('name as bank_name'));
	    $select_7->joinLeft(DbTable::TABLE_BLOCK_AMOUNT . " as ba", "ba.claim_txn_code = rr.txn_code", array(
		'DATE_FORMAT(ba.date_created, "%d-%m-%Y %H:%i:%s") as date_blocked',
		'DATE_FORMAT(ba.date_unblocked, "%d-%m-%Y %H:%i:%s") as date_unblocked' ));

            if ($productId > 0) {
                $select_7->where("rr.product_id IN ( $productId )");
            }
            if (!empty($wallet_type)) {
                $select_7->where("pm.is_virtual = ? " , $wallet_type);
            }
            if ($bank['id'] > 0) {
                $select_7->where("bank.id = ? ", $bank['id']);
            }
            if ($from != '' && $to != ''){
                $select_7->where("rr.date_created >=  ? " , $from );
                $select_7->where("rr.date_created <=  ?" , $to );
                $select_7->where("rr.status = ?", STATUS_SUCCESS);
            }
	    
            $union = $this->_db->select()->union(array($union, $select_7));
        }
	
        $rsLoad = $this->_db->fetchAll($union);
        //Disable DB Slave
        $this->_disableDbSlave();
            
        $cntLoad = count($rsLoad);
        for($i = 0; $i < $cntLoad; $i++)
        {
            $arrReport[$i]['txn_date'] = $rsLoad[$i]['date_created'];
            $arrReport[$i]['product_name'] = $rsLoad[$i]['product_name'];
            $arrReport[$i]['bank_name'] = $rsLoad[$i]['bank_name'];
            $arrReport[$i]['card_number'] = !empty($rsLoad[$i]['card_number']) ? Util::maskCard($rsLoad[$i]['card_number'], 4) : Util::maskCard($rsLoad[$i]['rat_card_number'], 4);
            $arrReport[$i]['card_pack_id'] = $rsLoad[$i]['card_pack_id'];
            $arrReport[$i]['member_id'] = $rsLoad[$i]['medi_assist_id'] ? $rsLoad[$i]['medi_assist_id'] : $rsLoad[$i]['partner_ref_no'];
            $arrReport[$i]['txn_type'] = $TXN_TYPE_LABELS[$rsLoad[$i]['txn_type']];
            $arrReport[$i]['status'] = $STATUS[$rsLoad[$i]['status']];
            $arrReport[$i]['wlt_code'] = $rsLoad[$i]['wlt_code'];
            $arrReport[$i]['mode'] = strtoupper($rsLoad[$i]['mode']);
            
            if ($rsLoad[$i]['product_id'] == $productRat->id) {// mediassist 
                $arrReport[$i]['wallet_hr_dr'] = $rsLoad[$i]['debit_amount']!='' ? $rsLoad[$i]['debit_amount'] : $rsLoad[$i]['medi_wallet_hr_cr'];                
            } else {
                $arrReport[$i]['wallet_hr_dr'] = $rsLoad[$i]['debit_amount']!='' ? $rsLoad[$i]['debit_amount'] : $rsLoad[$i]['wallet_hr_cr'];                
            }

            $arrReport[$i]['trans_amount'] = $rsLoad[$i]['tot_amount'];
            $arrReport[$i]['fee_amt'] = $rsLoad[$i]['fee_amt'];
            $arrReport[$i]['service_tax'] = $rsLoad[$i]['service_tax'];
            $arrReport[$i]['txn_no'] = $rsLoad[$i]['txn_no'];
            $arrReport[$i]['txn_code'] = $rsLoad[$i]['txn_code'];
            $arrReport[$i]['failed_reason'] = $rsLoad[$i]['failed_reason'];
            $arrReport[$i]['mcc_code'] = $rsLoad[$i]['mcc_code'];
            $arrReport[$i]['tid'] = $rsLoad[$i]['tid'];
            $arrReport[$i]['mid'] = $rsLoad[$i]['mid'];
            $arrReport[$i]['channel'] = strtoupper($rsLoad[$i]['channel']);
            $arrReport[$i]['rev_indicator'] = ucwords($rsLoad[$i]['rev_indicator']);
            $arrReport[$i]['date_reversal'] = $rsLoad[$i]['date_reversal'];
            //
            $arrReport[$i]['original_txn_no'] = '';
            $arrReport[$i]['original_transaction_date'] = '';
            if($rsLoad[$i]['original_transaction_id'] > 0 ){
            $originalReversalArr = $this->getReversalLoadsRecordbyId($rsLoad[$i]['original_transaction_id']);
            if(!empty($originalReversalArr))
                {
                $arrReport[$i]['original_txn_no'] = $originalReversalArr['txn_no'];
                $arrReport[$i]['original_transaction_date'] = $originalReversalArr['date_created'];
                }
            }
        
            $arrReport[$i]['narration'] = $rsLoad[$i]['narration'];
            if( ($rsLoad[$i]['status_settlement'] == STATUS_UNSETTLED) && ( (strtolower($STATUS[$rsLoad[$i]['status']]) == STATUS_LOADED) || (strtolower($STATUS[$rsLoad[$i]['status']]) == STATUS_FAILED)) ){
            $arrReport[$i]['status_settlement'] = '';   
            $arrReport[$i]['date_settlement'] = '';
            }else{
               
             $arrReport[$i]['status_settlement'] = $rsLoad[$i]['status_settlement'];
                if($rsLoad[$i]['status_settlement'] == STATUS_UNSETTLED){
                     $arrReport[$i]['date_settlement'] = '';   
                    }else{
                     $arrReport[$i]['date_settlement'] = $rsLoad[$i]['date_settlement'];
                    }
            
             }
            $arrReport[$i]['settlement_ref_no'] = $rsLoad[$i]['settlement_ref_no'];
            $arrReport[$i]['bene_act_no'] = $rsLoad[$i]['bene_act_no'];
            $arrReport[$i]['bene_act_name'] = $rsLoad[$i]['bene_act_name'];
            $arrReport[$i]['date_blocked'] = $rsLoad[$i]['date_blocked'];
	    $arrReport[$i]['date_unblocked'] = $rsLoad[$i]['date_unblocked'];
            
        }
        
        return $arrReport;
    }
    
    
    public function exportgetWalletTxnAgent($param){
	
	$data = $this->getWalletTxn($param);
		
        if (!empty($data)) {
            foreach ($data as $key => $data) {
                $retData[$key]['txn_date'] = $data['txn_date'];
		$retData[$key]['product_name'] = $data['product_name'];
		$retData[$key]['bank_name'] = $data['bank_name'];
		$retData[$key]['card_number'] = $data['card_number'];
		$retData[$key]['card_pack_id'] = $data['card_pack_id'];
		$retData[$key]['member_id'] = $data['member_id'];
		$retData[$key]['txn_type'] = $data['txn_type'];
		$retData[$key]['status'] = $data['status'];
		$retData[$key]['wlt_code'] = $data['wlt_code'];
		$retData[$key]['mode'] = $data['mode'];
		$retData[$key]['wallet_hr_dr'] = Util::numberFormat($data['wallet_hr_dr']);
		$retData[$key]['trans_amount'] = Util::numberFormat($data['trans_amount']);
		$retData[$key]['fee_amt'] = Util::numberFormat($data['fee_amt']);
		$retData[$key]['service_tax'] = Util::numberFormat($data['service_tax']);
		$retData[$key]['txn_no'] = $data['txn_no'];
		$retData[$key]['txn_code'] = $data['txn_code'];
		$retData[$key]['failed_reason'] = $data['failed_reason'];
		$retData[$key]['mcc_code'] = $data['mcc_code'];
		$retData[$key]['tid'] = $data['tid'];
		$retData[$key]['mid'] = $data['mid']; 
		$retData[$key]['channel'] = $data['channel'];
		$retData[$key]['rev_indicator'] = $data['rev_indicator'];
		$retData[$key]['mode'] = $data['mode']; 
		$retData[$key]['narration'] = $data['narration']; 
		$retData[$key]['date_blocked'] = $data['date_blocked']; 
		$retData[$key]['date_unblocked'] = $data['date_unblocked'];
            }
        }

        return $retData;
	
	
    }


    /*
     * $params['cardholder_id'] 
     * $params['product_id'] 
     * $params['wallet_code'] 
     * $params['amount'] 
     * $params['txn_no'] 
     * $params['txn_identifier_type'] mob
     * $params['narration'] 
     * $params['card_type'] n
     * $params['corporate_id'] 0
     * $params['mode'] cr/dr
     * $params['by_api_user_id'] pat api const 
     */
    
    
    public function doCardload($dataArr) {
         
        if (empty($dataArr)) {
            throw new Exception('Data missing for cardload');
        }
        $custModel = new Corp_Ratnakar_Cardholders();
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $productModel = new Products();
        $baseTxn = new BaseTxn();
        $ecsApi = new App_Socket_ECS_Corp_Transaction();
        $m = new \App\Messaging\Corp\Ratnakar\Operation();
        $str = '';
        $loadStatus = STATUS_FAILED;
        try {
            $cardholderId = $dataArr['cardholder_id'];
            $productId = $dataArr['product_id'];
            $pursecode = $dataArr['wallet_code'];

            $searchArr = array('cardholder_id' => $cardholderId, 'status' => STATUS_ACTIVE);
            $cardholderDetails = $custModel->getCardholderInfo($searchArr);

            if(!isset($cardholderDetails->id) || $cardholderDetails->id == '')
            {
                $loadStatus = STATUS_FAILED;
                $failedReason = 'Cardholder not found';
                $dateFailed = new Zend_Db_Expr('NOW()');   
                $dateLoad = new Zend_Db_Expr('NOW()');   
            } else {
                $loadStatus = STATUS_PENDING;
                $failedReason = '';
                $dateFailed = '';   
                $dateLoad = '';
            }
           
            $cardNumber = $cardholderDetails->card_number;
            $mediAssistId = $cardholderDetails->medi_assist_id;
            $customerMasterId = ($cardholderDetails->customer_master_id != '') ? $cardholderDetails->customer_master_id : 0;
            $ratCustomerId = ($cardholderDetails->rat_customer_id != '') ? $cardholderDetails->rat_customer_id : 0;
            $cardholderDetails->bank_id   = !empty($cardholderDetails->bank_id) ? $cardholderDetails->bank_id : '3';
            $customerPurseId = 0;
            $masterPurseDetails = $masterPurseModel->getPurseIdByPurseCode($pursecode);
            if($ratCustomerId > 0) { 
                $purseDetails = $custPurseModel->getCustPurseDetails(array('rat_customer_id' => $ratCustomerId, 'purse_master_id' => $masterPurseDetails['id']));
                $customerPurseId = (isset($purseDetails['id']) && $purseDetails['id'] > 0) ? $purseDetails['id'] : 0;
            }
            $purseMasterId = $masterPurseDetails['id'];
            $amount = 0;
            $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_PAT);
            $patWalletCode = $product->purse->code->patwallet; 
            $productCop = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_COPASS);
            $copWalletCode = $productCop->purse->code->genwallet; 
            $productHap = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_HAPPAY);
            $hapWalletCode = $productHap->purse->code->genwallet; 
            $productGen = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_GENERIC_GPR);
            $genWalletCode = $productGen->purse->code->genwallet;
            $productCny = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CNERGYIS);
            $cnyWalletCode = $productCny->purse->code->genwallet;
            $productRblmvc = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_MVC);
            $mvcWalletCode = $productRblmvc->purse->code->genwallet;
            
            if($loadStatus == STATUS_PENDING)
            {
                if(strtolower($dataArr['wallet_code']) != strtolower($patWalletCode) && strtolower($dataArr['wallet_code']) != strtolower($copWalletCode)  && strtolower($dataArr['wallet_code']) != strtolower($hapWalletCode)  && strtolower($dataArr['wallet_code']) != strtolower($genWalletCode) && strtolower($dataArr['wallet_code']) != strtolower($cnyWalletCode) && strtolower($dataArr['wallet_code']) != strtolower($mvcWalletCode) )
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
            if($amount != $dataArr['amount'])
            {
                $amount = Util::convertToPaisa($dataArr['amount']);
            }
            $txnCode = $baseTxn->generateTxncode();
            if (strtolower($dataArr['mode']) == TXN_MODE_DR) {
                $txnType = TXNTYPE_CARD_DEBIT;
            }
            else{
                $txnType = TXNTYPE_CARD_RELOAD;
            } 
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $cardNumberEncrypt = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')");
            $data = array(
                'customer_master_id' => $customerMasterId,
                'cardholder_id' => $cardholderId,
                'customer_purse_id' => $customerPurseId,
                'txn_type' => $txnType,
                'load_channel' => BY_API,
                'purse_master_id' => $masterPurseDetails['id'],
                'txn_identifier_type' => $dataArr['txn_identifier_type'],
                'card_number' => $cardNumberEncrypt,
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
                'date_load' => $dateLoad,
                'bank_id' => $cardholderDetails->bank_id,
		'channel' => $dataArr['channel'],
            );
            $this->insert($data);
            $loadRequestId = $this->_db->lastInsertId(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, 'id');
            $this->_db->beginTransaction();
            if($loadStatus == STATUS_PENDING) {
             
                if (strtolower($dataArr['mode']) == TXN_MODE_DR) {
                    // chkRatCardLoadDebit();
                        $custDebitDetails = array();
                        $custDebitDetails = array_merge($custDebitDetails, $dataArr);
                        $custDebitDetails['load_request_id']= $loadRequestId;
                        $custDebitDetails['customer_master_id']= $customerMasterId;
                        $custDebitDetails['purse_master_id']= $purseMasterId;
                        $custDebitDetails['customer_purse_id']= $customerPurseId;
                        $custDebitDetails['rat_customer_id']= $cardholderDetails->rat_customer_id;
                        $custDebitDetails['card_number']= $cardholderDetails->card_number;
                        $custDebitDetails['mobile']= $cardholderDetails->mobile;
                        $custDebitDetails['txn_code']= $txnCode;
                        $custDebitDetails['require_ecs']= FLAG_YES;
                        $custDebitDetails['bank_id'] = !empty($custDebitDetails['bank_id']) ? $custDebitDetails['bank_id'] : '3';

                        $this->doCardDebit($custDebitDetails);
                  

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
                   
                    $flgValidate = $baseTxn->chkAllowRatMediAssistCardLoad($validator);
                    if ($flgValidate) {
                        $cardLoadData = array(
                            'amount' => (string) $amount,
                            'crn' => $cardNumber,
                            'agentId' => $dataArr['by_api_user_id'],
                            'transactionId' => $txnCode,
                            'currencyCode' => CURRENCY_INR_CODE,
                            'countryCode' => COUNTRY_IN_CODE
                        );
                             
                        if(DEBUG_MVC) {
                            $apiResp = TRUE;
                        } else {
                            $apiResp = $ecsApi->cardLoad($cardLoadData); // bypassing for testing
                        }
                        
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
                                'bank_id' => $cardholderDetails->bank_id
                            );
                            
                            $baseTxn->successRatCardLoad($baseTxnParams);
                            $custPurse = $custPurseModel->getCustBalance($cardholderDetails->rat_customer_id);
                            $productDetail = $productModel->getProductInfo($productId);
                            
                            $cardholderArray['cardNumber'] = $cardholderDetails->card_number;

                            $ecsApi = new App_Api_ECS_Transactions();
                            if(DEBUG_MVC) {
                                $res = FALSE;
                            } else {
                                $res = $ecsApi->balanceInquiry($cardholderArray); // bypassing for testing
                            }
                            
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
			     'product_id' => $productId,
                                     
                                     );
                                
                            $m->apiCardload($userData);
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
    
    public function getWalletTrialBalance($param) 
    {                
        $productId = isset($param['product_id']) ? $param['product_id'] : '';
        $bankUnicode = isset($param['bank_unicode']) ? $param['bank_unicode'] : '';
        $wallet_type = isset($param['wallet_type']) ? $param['wallet_type'] : '';
        
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $authRequestModel = new AuthRequest();
        $bankModel = new Banks();
        $bankDetails = $bankModel->getBankbyUnicode($bankUnicode);
        $remitterModel = new Remit_Ratnakar_Remittancerequest();
        $masterPurseModel = new MasterPurse();
        $purseMasterIds = '';
        /*
         * Getting Wallet Type
         */
        if($wallet_type!=''){
            $walletParam = array(
              'product_id'=> $productId,
              'is_virtual'=> $wallet_type  
            );
            $masterPriPurseDetails = $masterPurseModel->getProductWalletBasicDetailsByIsVirtual($walletParam);
            if(!empty($masterPriPurseDetails)){
            $purseMasterIdList = array_column($masterPriPurseDetails, 'id'); 
            $purseMasterIds = implode(",",$purseMasterIdList);
            }
        }
        $param['purse_master_id'] = $purseMasterIds;
        
        $datearr = explode(" ",$param['from']);

        $arrReport = array();
     
        $dateselected = strtotime($datearr[0]);
        $dayBefore = strtotime("-1 day", $dateselected);
        $dayBefore = date('Y-m-d',$dayBefore);
       
        $param['product_id'] = $productId;
        
        // get opening balance
        $param['date'] = $dayBefore;
        $purseOpeningBal = $custPurseModel->getProductWalletClosingBalance($param);
        $arrReport['opening_bal'] = (!empty($purseOpeningBal['balance']))? $purseOpeningBal['balance'] : 0;
        
        $param['date'] = $datearr[0];
        $param['onDate'] = FLAG_YES;
        
        // Fetch successfull(credit) loads from load request table
        $param['txn_type'] = TXNTYPE_CARD_RELOAD;
        $param['status'] = STATUS_LOADED;
        $loadsArr = $this->getSuccessfulLoads($param);
        $arrReport['loads'] = (!empty($loadsArr) && ($loadsArr->total !='')) ? $loadsArr->total : 0;
        $reversalLoadsArr = $this->getSuccessfulReversalLoads($param);
        $arrReport['reversal_loads'] = (!empty($reversalLoadsArr)) ? $reversalLoadsArr->total : 0;
        
        // Fetch Refund loads from remittance request table
        if($wallet_type == FLAG_YES){
         $arrReport['refund_loads'] = 0;   
        }else{
        $refundloadsArr = $remitterModel->getTotalRefundTxnLoad($param);
        $arrReport['refund_loads'] = (!empty($refundloadsArr)) ? $refundloadsArr->agent_total_remittance : 0;
        }
        
        // Fetch reverted loads from load request table
//        $param['settlement_status'] = STATUS_REVERTED;
//        $param['txn_type'] = TXNTYPE_CARD_DEBIT;
//        $param['status'] = STATUS_DEBITED;
//        $unsettleArr = $this->getSuccessfulLoads($param);
//        $arrReport['reverted_loads'] = (!empty($unsettleArr)) ? $unsettleArr->total : 0;
        
        // Fetch Intra wallet CR/DR from rat_wallet_transfer table
        if($wallet_type == FLAG_YES){
         $arrReport['intrawallet_loads'] = 0;   
        }else{
        $intraWalletLoadArr = $this->getWalletTransferLoads($param);
         $arrReport['intrawallet_loads'] = (!empty($intraWalletLoadArr)) ? $intraWalletLoadArr->wallet_transfer_amount : 0;
        }
        
        // Get total credit loads
        $arrReport['total_txn_cr'] = Util::numberFormat(($arrReport['loads'] + $arrReport['refund_loads'] + $arrReport['intrawallet_loads']), FLAG_NO);
        
        // Fetch successfull(debit) loads from load request table
        $param['txn_type'] = TXNTYPE_CARD_DEBIT;
        $drloadsArr = $this->getTotalDebitByPurse($param);
        $arrReport['debit_loads'] = (!empty($drloadsArr)) ? $drloadsArr->total_load_amount : 0;
        
        // Fetch remit loads(dr) from remittance request table
        $status = array(STATUS_SUCCESS, STATUS_PROCESSED, STATUS_IN_PROCESS, STATUS_FAILURE, STATUS_REFUND, STATUS_HOLD);
        if($wallet_type == FLAG_YES){
         $arrReport['debit_remit_loads'] = 0;   
        }else{
        $drRemitloadsArr = $remitterModel->getTotalRemittanceTxnLoad($param, $status);
        $arrReport['debit_remit_loads'] = (!empty($drRemitloadsArr)) ? $drRemitloadsArr->agent_total_remittance : 0;
        }
        
        // Get total credit loads
        $arrReport['total_txn_dr'] = Util::numberFormat(($arrReport['debit_loads'] + $arrReport['debit_remit_loads'] + $arrReport['intrawallet_loads']), FLAG_NO);
//        $arrReport['total_txn_dr'] = Util::numberFormat(($arrReport['debit_remit_loads'] + $arrReport['intrawallet_loads']), FLAG_NO);
        
        
        $drTxnArr = $authRequestModel->getProductCompletedTxn($param);
        $arrReport['txn_dr'] = (!empty($drTxnArr)) ? $drTxnArr->total : 0;
        $crTxnArr = $authRequestModel->getProductReversedTxn($param);
        $arrReport['txn_cr'] = (!empty($crTxnArr)) ? $crTxnArr->reversed_total : 0;
        $arrReport['txn_cr'] = Util::numberFormat($arrReport['txn_cr'] + $arrReport['reversal_loads'], FLAG_NO);
        
        $arrReport['calculated_balance'] =  Util::numberFormat(($arrReport['opening_bal'] + $arrReport['total_txn_cr'] + $arrReport['txn_cr'] - $arrReport['total_txn_dr'] - $arrReport['txn_dr']), FLAG_NO);
        
        $param['date'] = $datearr[0];
        $purseClosingBal = $custPurseModel->getProductWalletClosingBalance($param);
        $arrReport['closing_bal'] = $purseClosingBal['balance'];
        
        // Fetch actual wallet closing balance
        $arrReport['wallet_balance'] =(!empty($arrReport['closing_bal']))? $arrReport['closing_bal'] : 0;
        $arrReport['difference'] = Util::numberFormat(($arrReport['calculated_balance'] - $arrReport['wallet_balance']), FLAG_NO);
        
        return $arrReport;
    }
  
   
    /*
     * getSuccessfullLoads will return the successfull Load reuqests
     */

    public function getSuccessfulLoads($param) {
        $type = isset($param['txn_type']) ? $param['txn_type'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $status = isset($param['status']) ? $param['status'] : '';
        $wallet_type = isset($param['wallet_type']) ? $param['wallet_type'] : '';
//        $settlement_status = isset($param['settlement_status']) ? $param['settlement_status'] : '';
	$productId = isset($param['product_id'])?$param['product_id']:0;
        $onDate = (isset($param['onDate']) && $param['onDate'] == FLAG_YES) ? FLAG_YES : FLAG_NO;
        
        $select = $this->select() 
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST. " as rclr", array('count(*) as count', 'sum(amount) as total','date_load'))
                ->joinLeft(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id = rclr.purse_master_id ",array());
        if ($type != '') {
            $select->where('rclr.txn_type = ?', $type);
        }
       	if ($productId != '') {
            $select->where('rclr.product_id = ?', $productId);
        }
        $select->where('rclr.original_transaction_id = ?', 0);
        if ($onDate) {
            $date = isset($param['date']) ? $param['date'] : '';
            $select->where('DATE(rclr.date_load) =?', $date);
        } elseif ($from != '' && $to != ''){
            $select->where("rclr.date_load >=  '" . $from . "'");
            $select->where("rclr.date_load <= '" . $to . "'");
        }
        if (!empty($wallet_type)) {
            $select->where("pm.is_virtual = ? " , $wallet_type);
        }
        if(!empty($status))
        {
            $select->where("rclr.status = ?", $status);
        } else {
            $select->where("rclr.status = ? ", STATUS_LOADED);
        }
        
//        if ($settlement_status != '') {
//            $select->where('status_settlement = ?', $settlement_status);
//        }
//        $select->group("product_id");
//	echo $select."<br>"; //exit;
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
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('count(*) as count', 'sum(amount_cutoff) as reversal_total','date_cutoff'));
        
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
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('count(id) as count', 'sum(amount) as total'))
                ->where('customer_master_id=?', $customerMasterId)
                ->where('product_id=?', $productId);
        if ($statusStr != '') {
            $select->where("status IN ($statusStr)");
            $select->where('original_transaction_id=?', 0);
        }
        
        $select->where("DATE(date_created) = '" . $curDate . "'")
                ->group("customer_master_id");
        return $this->fetchRow($select);
    }

    /*
     * Stats Duration - loads
     */

    public function getCustomerProductStatsDuration($customerMasterId, $productId, $startDate, $endDate, $statusStr) {
        $select = $this->select()
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('count(id) as count', 'sum(amount) as total'))
                ->where('customer_master_id=?', $customerMasterId)
                ->where('product_id=?', $productId);
        if ($statusStr != '') {
            $select->where("status IN ($statusStr)");
        }
        $select->where('original_transaction_id =?', 0);
        $select->where("DATE(date_created) BETWEEN '" . $startDate . "' AND '" . $endDate . "'")
                ->group("customer_master_id");
        return $this->fetchRow($select);
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
            $select->where("status = ?", STATUS_LOADED);
            }
             if ($txnType != '') {
            $select->where('txn_type = ?', $txnType);
            }
            else{
            $select->where("txn_type IN ('". TXNTYPE_RAT_CORP_CORPORATE_LOAD."','".TXNTYPE_RAT_CORP_MEDIASSIST_LOAD."','".TXNTYPE_CARD_RELOAD."')");  
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
        $loadCutoff = isset($param['load_cutoff']) ? $param['load_cutoff'] : FALSE;
       
        
        if ($purseId > 0) {
            //Enable DB Slave
            $this->_enableDbSlave();
                $select = $this->select();
                if($loadCutoff) {
                    $select->from($this->_name, array('sum(amount) as total_load_amount', 'sum(amount_cutoff) as total_cutoff_amount'));
                } else if($cutoff){
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
                    $select->where("status = ? ", STATUS_LOADED);
                }
                
                if ($txnType != '') {
                      $select->where('txn_type = ?', $txnType);
                }
                else{
                     $select->where("txn_type = ? ", TXNTYPE_CARD_RELOAD);  
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
            
            $row = $this->fetchRow($select);
             //Disable DB Slave
            $this->_disableDbSlave();
            return $row;     

        }
        else
            return 0;
    }
    

     public function getWalletbalance($data) {
        $productId = isset($data['product_id']) && $data['product_id'] > 0 ? $data['product_id'] : '';
	$wallettype = (!empty($data['wallettype'])) ? $data['wallettype'] :'' ;
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
        
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . ' as c', array($card_number,$crn,'c.medi_assist_id as member_id', 'c.employee_id','c.aadhaar_no','c.mobile', 'c.partner_ref_no', 'status'));
        $select->joinLeft(DbTable::TABLE_RAT_CUSTOMER_PURSE_CLOSING_BALANCE . ' as cb', "c.customer_master_id = cb.customer_master_id AND c.product_id = cb.product_id", array('cb.closing_balance', 'cb.date', 'cb.customer_master_id'));
        $select->join(DbTable::TABLE_PRODUCTS . ' as p', "c.product_id = p.id", array('p.id as product_id','p.name as product_name','program_type'));
        $select->join(DbTable::TABLE_PURSE_MASTER . ' as pm', "cb.purse_master_id = pm.id", array('pm.code as wallet_code'));
        $select->joinLeft(DbTable::TABLE_CORPORATE_USER . ' as cu', "c.by_corporate_id = cu.id", array('cu.corporate_code', 'concat(cu.first_name," ",cu.last_name) as corporate_name'));
	
	$select->joinLeft(DbTable::TABLE_BLOCK_AMOUNT . ' as b',"b.customer_purse_id = cb.customer_purse_id",array(
	    new Zend_Db_Expr("SUM(CASE WHEN b.status = '".STATUS_BLOCKED."' THEN b.amount ELSE 0 END) AS blocked_amount"),
	    new Zend_Db_Expr("SUM(CASE WHEN b.status = '".STATUS_UNBLOCKED."' THEN b.amount ELSE 0 END) AS unblocked_amount"),
	    new Zend_Db_Expr("SUM(CASE WHEN b.status = '".STATUS_CLAIMED."' THEN b.amount ELSE 0 END) AS claimed_amount"),
	    new Zend_Db_Expr("SUM(CASE WHEN b.status = '".STATUS_RELEASED."' THEN b.amount ELSE 0 END) AS released_amount")
	));
		
        if($productId != ''){
            $select->where("c.product_id =?",$data['product_id']);
        }
	if($wallettype !=''){
	    $select->where("pm.is_virtual =?",$wallettype);
	}
        //$select->where("c.status = ?", STATUS_ACTIVE);
        $select->where("cb.date = '" . $data['to'] . "'");
	$select->group("cb.customer_purse_id");	
        $select->order('c.id ASC');
	return $this->_db->fetchAll($select);
    }

    public function exportGetWalletbalance($param) {

        $data = $this->getWalletbalance($param);

        $retData = array();

        $rsCount = count($data);
        $count = 1;
        $totalBal = '0.00';
        
        if (!empty($data)) {
            foreach ($data as $key => $data) {

                $retData[$key]['product_name'] = $data['product_name'];
                $retData[$key]['bank_name'] = RATNAKAR_BANK_NAME;
                $retData[$key]['aadhaar_no'] = $data['aadhaar_no'];
                $retData[$key]['currency'] = CURRENCY_INR;
                $retData[$key]['card_number'] = Util::maskCard($data['card_number'], 4);
                $retData[$key]['crn'] = Util::maskCard($data['crn']);
		$retData[$key]['mobile'] = $data['mobile'];
                $retData[$key]['member_id'] = $data['member_id'];
                $retData[$key]['partner_ref_no'] = $data['partner_ref_no'];
                $retData[$key]['status'] = ucfirst($data['status']);
		$retData[$key]['corporate_code'] = $data['corporate_code'];
		$retData[$key]['corporate_name'] = $data['corporate_name'];
                $retData[$key]['report_date'] = Util::returnDateFormatted($data['date'], "d-m-Y", "Y-m-d", "-");
                $retData[$key]['wallet_code'] = $data['wallet_code'];
		$retData[$key]['blocked_amount'] = $data['blocked_amount'];
		$retData[$key]['claimed_amount'] = $data['claimed_amount'];
		$retData[$key]['released_amount'] = $data['released_amount'] + $data['unblocked_amount'];
                $retData[$key]['closing_balance'] = $data['closing_balance'];
		
                if($count == 1) {
                    $totalBal = $data['closing_balance'];
                    $custMasterId = $data['customer_master_id'];
                    if($rsCount == $count) {
                        $retData[$key]['total_bal'] = $totalBal;
                    }
                } elseif($custMasterId == $data['customer_master_id']) {
                    $totalBal += $data['closing_balance'];
                    $custMasterId = $data['customer_master_id'];

                    if($rsCount == $count) { 
                        $retData[$key]['total_bal'] = $totalBal;
                    } 
                } elseif($custMasterId != $data['customer_master_id']) {
                    $retData[$key-1]['total_bal'] = $totalBal;
                    
                    if($rsCount == $count) {
                        $totalBal = 0;
                        $totalBal += $data['closing_balance'];
                        $custMasterId = $data['customer_master_id'];
                        $retData[$key]['total_bal'] = $totalBal;
                    } else {
                        $totalBal = 0;
                        $totalBal += $data['closing_balance'];
                        $custMasterId = $data['customer_master_id'];
			$retData[$key]['total_bal'] = '';
                    }
                }
                $count++;
            }
        }

        return $retData;
	}
    public function getCardloadCount($param)
    {
        
        $status = isset($param['status']) ? $param['status'] : '';
        $onDate = isset($param['on_date']) ? $param['on_date'] : FALSE;
        
        $select = $this->select()
                ->from($this->_name, array('sum(amount) as total_load_amount'));
        
        $select->where("status = ?", STATUS_LOADED);
        if(isset($param['product_id']) && !empty($param['product_id'])) {        
            $select->where("product_id =?", $param['product_id']);
        }
        if($param['by_corporate_id']){
            $select->where("by_corporate_id =?", $param['by_corporate_id']);
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
        //echo $select;//exit;
        $data = $this->fetchRow($select);
        if(isset($data->total_load_amount) && !empty($data->total_load_amount))
            return $data->total_load_amount;
        else
            return 0;

    }
    
    public function insertLoadDetail($param = array()) {
        
        $param['status'] = STATUS_SUCCESS;
        $param['date_created'] = new Zend_Db_Expr('NOW()');
        $this->_db->insert(DbTable::TABLE_RAT_CORP_LOAD_REQUEST_DETAIL, $param);
        return TRUE;
        
    }
    
    public function mapLoadTxn($arrTxn) {
        $select = $this->select()
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('id', 'amount_available'))
                ->where('product_id = ?', $arrTxn['product_id'])
                ->where('customer_master_id = ?', $arrTxn['customer_master_id'])
                ->where('purse_master_id = ?', $arrTxn['purse_master_id'])
                ->where('amount_available > 0')
                ->where('status = ?', STATUS_LOADED)
                ->order('id ASC');
        $rs = $this->_db->fetchAll($select);
        if($rs){
             $detailArr = array(
                            'product_id' => $arrTxn['product_id'],
                            'txn_processing_id' => $arrTxn['txn_processing_id'],
                            'txn_code' => $arrTxn['txn_code'],
                            'txn_type' => TXNTYPE_CORP_AUTH_TXN_PROCESSING,
                            'amount' => 0,
                            'load_request_id' => 0,
                            'adjust_id' => 0
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
    
    /*
     * bulkAddCardload add card load request from batch table
     */

    public function singleAddCardload($dataArr,$cardholderDetails) {
        if (empty($dataArr)) {
            throw new Exception('Data missing for card load');
        }
        $productModel = new Products();
        $custModel = new Corp_Ratnakar_Cardholders();
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $baseTxn = new BaseTxn();
        $user = Zend_Auth::getInstance()->getIdentity();
        $str = '';
        try {
            // Foreach selected id value
                
                if(!isset($cardholderDetails->id) || $cardholderDetails->id == '')
                {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = 'Cardholder not found';
                    $dateFailed = new Zend_Db_Expr('NOW()');
                    $dateLoad = new Zend_Db_Expr('NOW()');   
                }
                $cardNumber = ($dataArr['card_number'] != '') ? $dataArr['card_number'] : $cardholderDetails->card_number;
                $mediAssistId = ($dataArr['medi_assist_id'] != '') ? $dataArr['medi_assist_id'] : $cardholderDetails->medi_assist_id;
                $customerMasterId = ($cardholderDetails->customer_master_id != '') ? $cardholderDetails->customer_master_id : 0;
                $cardholderId = ($cardholderDetails->id != '') ? $cardholderDetails->id : 0;
                $ratCustomerId = ($cardholderDetails->rat_customer_id != '') ? $cardholderDetails->rat_customer_id : 0;
                
                $customerPurseId = 0;
                // Master Purse id
               $prodInfo = $productModel->getProductInfo($dataArr['product_id']);
                if($prodInfo['const'] == PRODUCT_CONST_RAT_CNY) {
                    $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CNERGYIS);
                    $pursecode = $product->purse->code->genwallet;
                } elseif($prodInfo['const'] == PRODUCT_CONST_RAT_SUR) {
                    $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_SURYODAY);
                    $pursecode = $product->purse->code->genwallet;
                } 
                
                $masterPurseDetails = $masterPurseModel->getPurseIdByPurseCode($pursecode);
                
                // Purse id 
                if($ratCustomerId > 0) { 
                    $purseDetails = $custPurseModel->getCustPurseDetails(array('rat_customer_id' => $ratCustomerId, 'purse_master_id' => $masterPurseDetails['id']));
                    $customerPurseId = (isset($purseDetails['id']) && $purseDetails['id'] > 0) ? $purseDetails['id'] : 0;
                }
               //echo $loadStatus; exit;
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
//                    elseif($dataArr['card_type'] != strtolower(CORP_CARD_TYPE_NORMAL))
//                    {
//                        $loadStatus = STATUS_FAILED;
//                        $failedReason = 'Card type Corporate ID Validation failed';
//                        $dateFailed = new Zend_Db_Expr('NOW()');  
//                        $dateLoad = new Zend_Db_Expr('NOW()');
//                    }
                }
               //echo "asdasd"; exit;
                $this->_db->beginTransaction();
                if($amount != $dataArr['amount'])
                {
                    $amount = Util::convertToPaisa($dataArr['amount']);
                }
                $txnCode = $baseTxn->generateTxncode();
                $loadChanel = (!empty($user->corporate_code))? BY_CORPORATE: BY_OPS;
                $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $cardNumberEnc = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')");
                $data = array(
                    'customer_master_id' => $customerMasterId,
                    'cardholder_id' => $cardholderId,
                    'customer_purse_id' => $customerPurseId,
                    'txn_type' => TXNTYPE_RAT_CORP_CORPORATE_LOAD,
                    'load_channel' => $loadChanel,
                    'purse_master_id' => $masterPurseDetails['id'],
                    'txn_identifier_type' => $dataArr['txn_identifier_type'],
                    'card_number' => $cardNumberEnc,
                    'medi_assist_id' => $mediAssistId,
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
                    'ip' => $this->formatIpAddress(Util::getIP()),
                    'by_ops_id' => $user->id,
                    'batch_name' => $dataArr['batch_name'],
                    'product_id' => $dataArr['product_id'],
                    'status' => $loadStatus,
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'failed_reason' => $failedReason,
                    'date_failed' => $dateFailed,
                    'date_load' => $dateLoad,
                    'channel' => $dataArr['channel']
                );
                if(!empty($user->corporate_code)){
                    $data['by_ops_id']= 0;
                    $data['by_corporate_id']= $user->id;
                 }
                //echo "<pre>"; print_r($data);exit;
                $this->insert($data);
                $loadRequestId = $this->_db->lastInsertId(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, 'id');
                $this->_db->commit();
                if($loadRequestId)
                    return $loadRequestId;
                else
                    return false;
        } catch (Exception $e) {
            // If any of the queries failed and threw an exception,
            // we want to roll back the whole transaction, reversing
            // changes made in the transaction, even those that succeeded.
            // Thus all changes are committed together, or none are.
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_db->rollBack();
            //throw new Exception ("Transaction not completed due to system failure");
            //throw new Exception($e->getMessage());
            return false;
        }
       // return TRUE;
    }
    
    public function exportSampleLoadRequests($params){
        $data = $this->getLoadRequests($params);
        $data = $data->toArray();
        $retData = array(); 
        if(!empty($data)) { 
            foreach($data as $key=>$data){ 
                if(strtolower($data['txn_identifier_type'])== strtolower(CORP_WALLET_TXN_IDENTIFIER_CN)) {
                    $card_number = $data['card_number'];
                } elseif(strtolower($data['txn_identifier_type'])== strtolower(CORP_WALLET_TXN_IDENTIFIER_MI)) {
                    $card_number = $data['medi_assist_id'];
                } 
                $retData[$key]['card_number']           =   $card_number ;
                $retData[$key]['txn_identifier_type']   =   $data['txn_identifier_type']; 
                $retData[$key]['amount']                =   0.00; 
                $retData[$key]['currency']              =   $data['currency'];
                $retData[$key]['narration']             =   $data['narration'];
                $retData[$key]['wallet_code']           =   $data['wallet_code'];
                $retData[$key]['txn_no']                =   '0';
                $retData[$key]['card_type']             =   $data['card_type']; 
                $retData[$key]['corporate_id']          =   '0'; 
                $retData[$key]['mode']                  =   $data['mode']; 
            }
        }
        return $retData;
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
        $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $cardNumberEnc = new Zend_Db_Expr("AES_ENCRYPT('".$card_number."','".$encryptionKey."')");
        
	if(strtolower($txn_identifier_type) == CORP_WALLET_TXN_IDENTIFIER_MI || strtolower($txn_identifier_type) == CORP_WALLET_TXN_IDENTIFIER_EI || strtolower($txn_identifier_type) == CORP_WALLET_TXN_IDENTIFIER_CN){
            $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER .' as kc', array('kc.id','kc.status'))
                ->where('product_id = ?',$product_id);
		
	    if(strtolower($txn_identifier_type) == CORP_WALLET_TXN_IDENTIFIER_CN){	
		$details->where('card_number = ?',$cardNumberEnc);
	    }elseif(strtolower($txn_identifier_type) == CORP_WALLET_TXN_IDENTIFIER_MI){
		$details->where('medi_assist_id = ?',$card_number);
	    }elseif(strtolower($txn_identifier_type) == CORP_WALLET_TXN_IDENTIFIER_EI){
		$details->where('employee_id = ?',$card_number);
	    }	
	    
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
     /*
     * showPendingFaildCardloadDetails , show load requests from batch table which have upload status as failed
     */
    public function showPendingFaildCardloadDetails($batchName, $page = 1, $paginate = NULL, $force = FALSE) {
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST_BATCH, array('id', 'bank_id', 'product_id', 'txn_identifier_type', $card_number, 'medi_assist_id', 'employee_id', 'amount', 'currency', 'narration', 'wallet_code', 'txn_no', 'card_type', 'mode', 'corporate_id', 'by_ops_id', 'by_corporate_id', 'ip', 'batch_name', 'date_created', 'date_updated', 'failed_reason', 'upload_status'));
        $select->where('upload_status = ?', STATUS_FAILED);
        $select->where('batch_name = ?', $batchName);
        $select->order('id ASC');

        return $this->_db->fetchAll($select);

    }

    
    public function checkBatchFilename($fileName) {
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST_BATCH, array('batch_name'));
        $select->where('batch_name = ?', $fileName);
        $rs = $this->_db->fetchRow($select);
        if (empty($rs)) {
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
     /*
     * insertLoadrequestForLog , insert data from file into the batch table
     */

    public function insertLoadrequestForLog($dataArr) {
        $user = Zend_Auth::getInstance()->getIdentity();

        try { 
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $dataArr['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$dataArr['card_number']."','".$encryptionKey."')");
            $data = array(
                'txn_identifier_type' => $dataArr['txn_identifier_type'],
                'card_number' => $dataArr['card_number'],
                'medi_assist_id' => $dataArr['medi_assist_id'],
                'employee_id' => $dataArr['employee_id'],
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
            $this->_db->insert(DbTable::TABLE_RAT_CORP_LOAD_REQUEST_BATCH, $data);
            return $this->_db->lastInsertId(DbTable::TABLE_RAT_CORP_LOAD_REQUEST_BATCH); 
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            //echo $e->getMessage();
	    return false;
        }
    }
     /*
     * showFailedPendingCardloadDetails , show load requests from batch table which have upload status as failed
     */
    public function showFailedPendingCardloadDetails($batchName) {
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
	
	$select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST_BATCH,array('id', 'bank_id', 'product_id', 'txn_identifier_type', $card_number, 'medi_assist_id', 'employee_id', 'amount', 'currency', 'narration', 'wallet_code', 'txn_no', 'card_type', 'mode', 'corporate_id', 'by_ops_id', 'by_corporate_id', 'ip', 'batch_name', 'date_created', 'date_updated', 'failed_reason', 'upload_status',));
        $select->where('upload_status = ?', STATUS_FAILED);
        $select->where('batch_name = ?', $batchName);
        $select->order('id ASC');
        return $this->_db->fetchAll($select);
    }
    
     public function getLoadDetails($params) {
        $txnNo = isset($params['txn_no'])?$params['txn_no']:'';
        $productID = isset($params['product_id'])?$params['product_id']:'';
        $txnCode = isset($params['txn_code'])?$params['txn_code']:'';
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST . " as cl", array('txn_code', 'product_id', 'wallet_code', 'amount', 'narration', 'mode', 'txn_identifier_type', 'status'))
        ->joinLeft(DbTable::TABLE_RAT_CORP_CARDHOLDER . ' as cc', "cl.cardholder_id = cc.id", array('mobile'));
       if($txnNo !=''){
        $select->where('cl.txn_no = ?', $txnNo);
       }
        if($txnCode !=''){
        $select->where('cl.txn_code = ?', $txnCode);
        }
        if($productID !=''){
        $select->where('cl.product_id = ?', $productID);
        }
        $rs = $this->_db->fetchRow($select);
        if (!empty($rs)) {
            return $rs;
        } else {
            return FALSE;
        }
     }
     
     public function getMultiWalletbalance($data)
     {
        $productId = isset($data['product_id']) && $data['product_id'] > 0 ? $data['product_id'] : '';
        
        $select = $this->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . ' as c', array('c.card_number','c.crn','c.medi_assist_id as member_id', 'c.status', 'c.partner_ref_no'));
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_RAT_CUSTOMER_PURSE_CLOSING_BALANCE . ' as cb', "c.customer_master_id = cb.customer_master_id", array('cb.closing_balance'));
        $select->joinLeft(DbTable::TABLE_PRODUCTS . ' as p', "c.product_id = p.id", array('p.name as product_name'));
        $select->joinLeft(DbTable::TABLE_BANK . ' as b', "p.bank_id = b.id", array('b.name as bank_name'));
        $select->joinLeft(DbTable::TABLE_PURSE_MASTER . ' as pm', "p.id = pm.product_id", array('pm.code as wallet_code'));
        
        if($productId != ''){
            $select->where("c.product_id =?",$data['product_id']);
        }
        
        $select->where("cb.date_created >= '" . $data['from'] . "'");
        $select->where("cb.date_created <= '" . $data['to'] . "'");        
        $select->order('c.id ASC');
        return $this->_db->fetchAll($select);
     }
     
     public function exportMultiWalletbalance($params)
     {
        $data = $this->getMultiWalletbalance($params);
        $status = Util::getCardHolderStatusList();
        
        $retData = array();

        if (!empty($data)) {
            foreach ($data as $key => $data) {
                $retData[$key]['product_name'] = $data['product_name'];
                $retData[$key]['bank_name'] = $data['bank_name'];
                $retData[$key]['member_id'] = $data['member_id'] ? $data['member_id'] : $data['partner_ref_no'];
                $retData[$key]['currency'] = CURRENCY_INR;
                $retData[$key]['wallet_code'] = $data['wallet_code'];
                $retData[$key]['amount'] = $data['closing_balance'];
                $retData[$key]['status'] = $status[$data['status']];
            }
        }
        return $retData;
     }
     
 
    public function getLoadRequestInfo($param)
    {
        $wallet_code = isset($param['wallet_code']) ? $param['wallet_code'] : '';
        $amount = isset($param['amount']) ? $param['amount'] : '';
        $mode = isset($param['mode']) ? $param['mode'] : '';
        $txn_no = isset($param['txn_no']) ? $param['txn_no'] : '';
        $productId = isset($param['product_id']) ? $param['product_id'] : '';
        $txn_identifier_type = isset($param['txn_identifier_type']) ? $param['txn_identifier_type'] : '';
        $txn_identifier_num = isset($param['txn_identifier_num']) ? $param['txn_identifier_num'] : '';
        $date_txn = isset($param['date_txn']) ? $param['date_txn'] : '';
                
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST . " as rcl", array('rcl.id as id', 'status_settlement'));

        if ($wallet_code != ''){
            $select->where("rcl.wallet_code = '" . $wallet_code . "'");
        }
        if ($amount != ''){
            $select->where("rcl.amount = '" . $amount . "'");
        }
        if ($mode != ''){
            $select->where("rcl.mode = '" . $mode . "'");
        }
        if ($txn_no != ''){
            $select->where("rcl.txn_no = '" . $txn_no . "'");
        }
        if ($productId != ''){
            $select->where("rcl.product_id = '" . $productId . "'");
        }
        if ($txn_identifier_type != ''){
            $select->where("rcl.txn_identifier_type = '" . $txn_identifier_type . "'");
        }
        if ($txn_identifier_num != ''){
            $select->where("rcl.txn_identifier_num = '" . $txn_identifier_num . "'");
        }
        if ($date_txn != ''){
            $select->where("DATE(rcl.date_created) = '" . $date_txn . "'");
        }
        $select->where("rcl.status IN ('". STATUS_LOADED."','".STATUS_DEBITED."')");
        return $this->_db->fetchRow($select);
    }
    
     /*
     * Stats Daily - loads
     */

    public function getCustomerBankStatsDaily($customerMasterId, $bankId, $curDate, $statusStr) {
        $select = $this->select()
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('count(id) as count', 'sum(amount) as total'))
                ->where("customer_master_id IN ( $customerMasterId )")
                ->where('bank_id=?', $bankId);
        if ($statusStr != '') {
            $select->where("status IN ($statusStr)");
        }
        $select->where('original_transaction_id =?', 0);
        $select->where("DATE(date_created) = '" . $curDate . "'")
                ->group("bank_id");
        return $this->fetchRow($select);
    }
    
    /*
     * Stats Duration - loads on bank basis
     */

    public function getCustomerBankStatsDuration($customerMasterId, $bankId, $startDate, $endDate, $statusStr) {
        $select = $this->select()
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('count(id) as count', 'sum(amount) as total'))
                ->where("customer_master_id IN ( $customerMasterId )")
                ->where('bank_id=?', $bankId);
        if ($statusStr != '') {
            $select->where("status IN ($statusStr)");
        }
        $select->where('original_transaction_id =?', 0);
        $select->where("DATE(date_created) BETWEEN '" . $startDate . "' AND '" . $endDate . "'")
                ->group("bank_id");
        return $this->fetchRow($select);
    }

    public function getWalletTransferLoads($param)
    {
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $productId = isset($param['product_id']) && $param['product_id'] > 0 ? $param['product_id'] : '';
        $onDate = (isset($param['onDate']) && $param['onDate'] == FLAG_YES) ? FLAG_YES : FLAG_NO;
        $wallet_type = isset($param['wallet_type']) ? $param['wallet_type'] : '';
        
        $select = $this->select()
                ->from(DbTable::TABLE_RATNAKAR_WALLET_TRANSFER. " as rwt", array('sum(rwt.amount) as wallet_transfer_amount'))
                ->join(DbTable::TABLE_RAT_CUSTOMER_PURSE. " as rcp", "rcp.id = rwt.customer_purse_id ",array())
                ->join(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id = rcp.purse_master_id ",array());
        $select->setIntegrityCheck(false);
        $select->where('rwt.product_id = ?', $productId);
        
        if ($onDate) {
            $date = isset($param['date']) ? $param['date'] : '';
            $select->where('DATE(rwt.date_created) =?', $date);
        } elseif ($from != '' && $to != ''){
            $select->where("rwt.date_created >=  '" . $from . "'");
            $select->where("rwt.date_created <= '" . $to . "'");
        }
        if (!empty($wallet_type)) {
            $select->where("pm.is_virtual = ? " , $wallet_type);
        }
        $select->where("rwt.status = ?", STATUS_SUCCESS);
        $select->group("rwt.product_id");        
//	echo $select."<br>"; 
        return $this->fetchRow($select);
    }
    
    /*
     * getDebitLoads will return the successfull debitted load reuqests
     */

    public function getDebitLoads($param) {
        $type = isset($param['txn_type']) ? $param['txn_type'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
//        $settlement_status = isset($param['settlement_status']) ? $param['settlement_status'] : '';
        $productId = isset($param['product_id']) && $param['product_id'] > 0 ? $param['product_id'] : '';
	$onDate = (isset($param['onDate']) && $param['onDate'] == FLAG_YES) ? FLAG_YES : FLAG_NO;
        
        $select = $this->select() 
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('count(*) as count', 'sum(amount) as total','date_load'));
        
        if ($type != '') {
            $select->where('txn_type = ?', $type);
        }
	if ($productId != '') {
            $select->where('product_id = ?', $productId);
        }
        if ($onDate) {
            $date = isset($param['date']) ? $param['date'] : '';
            $select->where('DATE(date_load) =?', $date);
        } elseif ($from != '' && $to != ''){
            $select->where("date_load >=  '" . $from . "'");
            $select->where("date_load <= '" . $to . "'");
        }
        $select->where("status = ? ", STATUS_DEBITED);
//        if ($settlement_status != '') {
//            $select->where('status_settlement != ?', $settlement_status);
//        }
        $select->group("product_id");
//	echo $select."<br>"; 
        return $this->fetchRow($select);
    }
    
     /*
     * $params['cardholder_id'] 
     * $params['product_id'] 
     * $params['wallet_code'] 
     * $params['amount'] 
     * $params['txn_no'] 
     * $params['txn_identifier_type'] mob
     * $params['narration'] 
     * $params['card_type'] n
     * $params['corporate_id'] 0
     * $params['mode'] cr/dr
     * $params['by_api_user_id'] pat api const 
     */
    
    
    public function doCardloadECSAPI($dataArr) {
       
        if (empty($dataArr)) {
            throw new Exception('Data missing for cardload');
        }
        $custModel = new Corp_Ratnakar_Cardholders();
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $baseTxn = new BaseTxn();
        $object = new Corp_Ratnakar_Cardholders();
        $productModel = new Products();
        $str = '';
        $loadStatus = STATUS_FAILED;
        $product = App_DI_Definition_BankProduct::getInstance($dataArr['bank_product_const']);
        $payuWalletCode = $product->purse->code->genwallet;
        $ecsApi = new App_Socket_ECS_Corp_Transaction();
        
        if($dataArr['bank_id'] ==''){
        $productDetail = $productModel->getProductInfo($dataArr['product_id']);
        $dataArr['bank_id'] = $productDetail['bank_id'];  
        }
        try {
            $cardholderId = $dataArr['cardholder_id'];
            $productId = $dataArr['product_id'];
            $pursecode = ($dataArr['wallet_code'] != '')?$dataArr['wallet_code']: $payuWalletCode;

            $searchArr = array('cardholder_id' => $cardholderId, 'status' => STATUS_ACTIVE);
            $cardholderDetails = $custModel->getCardholderInfo($searchArr);
            switch($cardholderDetails->cardholder_status)
            {
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

            $cardNumber = $cardholderDetails->card_number;
            $mediAssistId = $cardholderDetails->medi_assist_id;
            $customerMasterId = ($cardholderDetails->customer_master_id != '') ? $cardholderDetails->customer_master_id : 0;
            $ratCustomerId = ($cardholderDetails->rat_customer_id != '') ? $cardholderDetails->rat_customer_id : 0;
            $customerPurseId = 0;
            $masterPurseDetails = $masterPurseModel->getPurseIdByPurseCode($pursecode);
            
            if($ratCustomerId > 0) { 
                if(!empty($masterPurseDetails)){
                    $purseDetails = $custPurseModel->getCustPurseDetails(array('rat_customer_id' => $ratCustomerId, 'purse_master_id' => $masterPurseDetails['id']));
                    $customerPurseId = (isset($purseDetails['id']) && $purseDetails['id'] > 0) ? $purseDetails['id'] : 0;
                }else{
                    throw new Exception('Wallet Code is not valid');
                }
            }
            $purseMasterId = $masterPurseDetails['id'];
            $amount = 0;
           
            if($loadStatus == STATUS_PENDING)
            {
                if(strtolower($pursecode) != strtolower($payuWalletCode) )
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
        
            if($amount != $dataArr['amount'])
            {
                $amount = Util::convertToRupee($dataArr['amount']);
            }
            //$txnCode = $baseTxn->generateTxncode();
            $txnCode = $dataArr['txn_code'];
            if($txnCode == ''){
              $txnCode = $baseTxn->generateTxncode();   
            }
            if (strtolower($dataArr['mode']) == TXN_MODE_DR) {
                $txnType = TXNTYPE_CARD_DEBIT;
            }
            else{
                $txnType = TXNTYPE_CARD_RELOAD;
            }
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $cardNumberEnc = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')");
            $data = array(
                'customer_master_id' => $customerMasterId,
                'cardholder_id' => $cardholderId,
                'customer_purse_id' => $customerPurseId,
                'txn_type' => $txnType,
                'load_channel' => BY_API,
                'purse_master_id' => $masterPurseDetails['id'],
                'txn_identifier_type' => $dataArr['txn_identifier_type'],
                'txn_identifier_num' => $dataArr['txn_identifier_num'],
                'card_number' => $cardNumberEnc,
                'medi_assist_id' => $mediAssistId,
                'amount' => $amount,
                'amount_available' => 0,
                'amount_used' => 0,
                'amount_cutoff' => 0,
                'currency' => CURRENCY_INR_CODE,
                'narration' => $dataArr['narration'],
                'wallet_code' => strtoupper($pursecode),
                'txn_no' => $dataArr['txn_no'],
                'card_type' => $dataArr['card_type'],
                'corporate_id' => $dataArr['corporate_id'],
                'mode' => $dataArr['mode'],
                'txn_code' => $txnCode,
                'ip' => $this->formatIpAddress(Util::getIP()),
                'by_ops_id' => 0,
                'batch_name' => '',
                'bank_id' => $dataArr['bank_id'],
                'product_id' => $dataArr['product_id'],
                'status' => $loadStatus,
                'date_created' => new Zend_Db_Expr('NOW()'),
                'failed_reason' => $failedReason,
                'date_failed' => $dateFailed,
                'date_load' => $dateLoad,
		'channel' => $dataArr['channel'],
            );
            if($dataArr['manageType'] == CORPORATE_MANAGE_TYPE){
                $data['by_corporate_id'] = $dataArr['by_api_user_id'];
                $data['date_expiry'] = $dataArr['date_expiry'];
            }
            else{
                $data['by_agent_id'] = $dataArr['by_api_user_id'];
                $data['by_corporate_id'] = 0;
            }
            $this->insert($data);
            $loadRequestId = $this->_db->lastInsertId(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, 'id');
            $this->_db->beginTransaction();
            if($loadStatus == STATUS_PENDING) {
             
                if (strtolower($dataArr['mode']) == TXN_MODE_DR) {
                
                    // chkRatCardLoadDebit();
                        $custDebitDetails = array();
                        $custDebitDetails = array_merge($custDebitDetails, $dataArr);
                        $custDebitDetails['load_request_id']= $loadRequestId;
                        $custDebitDetails['customer_master_id']= $customerMasterId;
                        $custDebitDetails['purse_master_id']= $purseMasterId;
                        $custDebitDetails['customer_purse_id']= $customerPurseId;
                        $custDebitDetails['rat_customer_id']= $cardholderDetails->rat_customer_id;
                        $custDebitDetails['card_number']= $cardholderDetails->card_number;
                        $custDebitDetails['mobile']= $cardholderDetails->mobile;
                        $custDebitDetails['txn_code']= $txnCode;
                        $custDebitDetails['require_ecs']= FLAG_YES;
                        $this->doCardDebit($custDebitDetails);

                } elseif (strtolower($dataArr['mode']) == TXN_MODE_CR) {

                    $validator = array(
                        'load_request_id' => $loadRequestId,
                        'customer_master_id' => $customerMasterId,
                        'purse_master_id' => $purseMasterId,
                        'customer_purse_id' => $customerPurseId,
                        'amount' => $amount,
                        'agent_id' => $dataArr['by_api_user_id'],
                        'product_id' => $productId,
                        'bank_id' => $dataArr['bank_id'],
                        'manageType' => $dataArr['manageType']
                    );
                   
                    $flgValidate = $baseTxn->chkAllowRatMediAssistCardLoad($validator);
                    if ($flgValidate) {
                        $loadStatus = STATUS_SUCCESS;
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
                                'bank_id' => $dataArr['bank_id'],
                                'purse_master_id' => $purseMasterId,
                                'customer_purse_id' => $customerPurseId,
                                'amount' => $amount,
                                'agent_id' => $dataArr['by_api_user_id'],
                                'txn_type' => TXNTYPE_CARD_RELOAD,
                                'ip' => $this->formatIpAddress(Util::getIP()),
                                'manageType' => $dataArr['manageType']
                            );
                            $baseTxn->successRatCardLoad($baseTxnParams);
                            $custPurse = $custPurseModel->getCustBalance($cardholderDetails->rat_customer_id);
                            
                            $cardholderArray['cardNumber'] = $cardholderDetails->card_number;

                           // Get balance
                              $balVal = $custPurse['sum'];  
                           
                         
                        if (strtolower($dataArr['sms_flag']) == strtolower(FLAG_Y)) {
                            // Send SMS
                            $userData = array(
                             'last_four' =>substr($cardholderDetails->card_number, -4),
                             'product_id' => $productId,
                             'mobile' => $cardholderDetails->mobile,
                             'amount' => $amount,
                             'balance' => $balVal,
                            );
                          $object->generateSMSDetails($userData, $smsType = TRANSACTION_REQUEST_CR); 
                           }
                           }else {
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
          }
        return array('status' => STATUS_LOADED);
    }
    
    public function getCustLoadTransaction($params){
       $productID = isset($params['product_id'])?$params['product_id']:'';
       $ratCustId = isset($params['customer_id'])?$params['customer_id']:'';
       $walletCode = isset($params['wallet_code'])?$params['wallet_code']:'';
       $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST .' as rclr', array("rclr.amount as amount","rclr.wallet_code","rclr.currency","rclr.txn_no","rclr.narration as description","mode","txn_code","date_load as txn_date","UNIX_TIMESTAMP(date_load) as strdate"))
                ->join(DbTable::TABLE_PURSE_MASTER .' as pm', "rclr.purse_master_id = pm.id",array("code"));
        
        $details->where("rclr.cardholder_id =?",$ratCustId);
        if( ($walletCode !='') && ($walletCode == 'all') ){
          $details->where("rclr.status =?",STATUS_LOADED);  
        }elseif($walletCode !=''){
          $details->where("rclr.wallet_code =?",$walletCode);  
        }
        $details->order('rclr.id DESC');
        $details->limit(TXN_HISTORY_COUNT);      
       $res = $this->_db->fetchAll($details);
       // return $res;
       if(!empty($res)){
           return $res;
       }else{
           return FALSE;
       }
       
   }
   

   
   public function getVoucherDetails($params) {
        $voucherNum = isset($params['voucher_num']) ? $params['voucher_num'] : '';
        $productID = isset($params['product_id']) ? $params['product_id'] : '';
        $mode = isset($params['mode']) ? strtolower($params['mode']) : '';
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST . " as cl", array('*'))
        ->joinLeft(DbTable::TABLE_RAT_CORP_CARDHOLDER . ' as cc', "cl.cardholder_id = cc.id", array('mobile'));
        
        if($voucherNum !=''){
            $select->where('cl.voucher_num = ?', $voucherNum);
        }        
        if($productID !=''){
            $select->where('cl.product_id = ?', $productID);
        }
        if($mode !=''){
            $select->where('cl.mode = ?', $mode);
        }
        $select->where('cl.status = ?', STATUS_LOADED);
        $rs = $this->_db->fetchRow($select);
        if (!empty($rs)) {
            return $rs;
        } else {
            return FALSE;
        }
     }
     
    public function getAgentDebits($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
        $onDate = isset($param['on_date']) ? $param['on_date'] : FALSE;
        
        if(isset($param['debit_api_cr'])) {
            $debitAPICr = $param['debit_api_cr'];
        } else {
            $debitAPICr = POOL_AC;
        }
        
        if ($agentId ==  0) {
            return 0;
        }
        
        //Enable DB Slave
        $this->_enableDbSlave();
         $select = $this->select()
              ->setIntegrityCheck(false)
              ->from($this->_name ." as l", array('sum(l.amount) as total_agent_load_amount'))
              ->join(DbTable::TABLE_PURSE_MASTER . " as p", "l.purse_master_id  = p.id AND p.debit_api_cr = '".$debitAPICr."'",array('p.debit_api_cr'))
               ->where('l.by_agent_id = ?', $agentId);
            $select->where("l.status = ?", STATUS_DEBITED);
            $select->where('l.txn_type = ?', TXNTYPE_CARD_DEBIT);
            $select->where('p.is_virtual = ?', FLAG_NO);
            if ($onDate) {
                $date = isset($param['date']) ? $param['date'] : '';
                $select->where('DATE(l.date_created) =?', $date);
            } else {
                $fromDate = isset($param['from']) ? $param['from'] : '';
                $toDate = isset($param['to']) ? $param['to'] : '';
                $select->where('l.date_created >= ?', $fromDate);
                $select->where('l.date_created <= ?', $toDate);
            }
            
            $row = $this->fetchRow($select);
            //Disable DB Slave
            $this->_disableDbSlave();
            return $row;
    }
    
    
    public function getAgentDebitsDisplay($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
        $onDate = isset($param['on_date']) ? $param['on_date'] : FALSE;
       
        if ($agentId ==  0) {
            return 0;
        }
 
         $select = $this->select()
              ->setIntegrityCheck(false)
              ->from($this->_name ." as l", array('sum(l.amount) as total_agent_load_amount'))
               ->where('l.by_agent_id = ?', $agentId);
            $select->where("l.status = ?", STATUS_DEBITED);
            $select->where('l.txn_type = ?', TXNTYPE_CARD_DEBIT);
            
            if ($onDate) {
                $date = isset($param['date']) ? $param['date'] : '';
                $select->where('DATE(l.date_created) =?', $date);
            } else {
                $fromDate = isset($param['from']) ? $param['from'] : '';
                $toDate = isset($param['to']) ? $param['to'] : '';
                $select->where('l.date_created >= ?', $fromDate);
                $select->where('l.date_created <= ?', $toDate);
            }
            return $this->fetchRow($select);
       
    }
    
    public function exportRatLoadRequests($params){
        $data = $this->getLoadRequests($params);
        $data = $data->toArray();
        $retData = array();
        
        if(!empty($data))
        {
                     
            foreach($data as $key=>$data){
                    $retData[$key]['product_name']  = $data['product_name'];
                    $retData[$key]['txn_identifier_type']  = $data['txn_identifier_type'];
                    $retData[$key]['card_number']          = util::maskCard($data['card_number'],4,6);
                    $retData[$key]['medi_assist_id']    = $data['medi_assist_id'];
                    $retData[$key]['amount']      = $data['amount'];
                    $retData[$key]['amount_cutoff']      = $data['amount_cutoff']; 
                    $retData[$key]['currency']        = $data['currency'];
                    $retData[$key]['narration'] = $data['narration'];
                    $retData[$key]['wallet_code'] = $data['wallet_code'];
                    $retData[$key]['txn_no'] = $data['txn_no'];
                    $retData[$key]['card_type']      = $data['card_type']; 
                    $retData[$key]['corporate_id']      = $data['corporate_id']; 
                    $retData[$key]['mode']      = $data['mode']; 
                    $retData[$key]['txn_code']      = $data['txn_code']; 
                    $retData[$key]['failed_reason']      = $data['failed_reason']; 
                    $retData[$key]['status']      = $data['status'];
            }
        }
        
        return $retData;
    }
    
    
    public function getCustTransactions_ex($params){
       $productID = isset($params['product_id'])?$params['product_id']:'';
       $ratCustId = isset($params['customer_id'])?$params['customer_id']:'';
       $walletCode = isset($params['wallet_code'])?$params['wallet_code']:'';
       $ratCustMasterId = isset($params['customer_master_id'])?$params['customer_master_id']:'';
       $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_TXN_CUSTOMER .' as rtc', array("rtc.amount as amount","rtc.txn_code","rtc.txn_type","rtc.currency","rtc.mode","date_created as txn_date"))
                ->join(DbTable::TABLE_PURSE_MASTER .' as pm', "rtc.purse_master_id = pm.id",array("pm.code"))
                ->joinLeft(DbTable::TABLE_RAT_CORP_LOAD_REQUEST .' as rclr', "rtc.txn_code = rclr.txn_code", array("rclr.narration as load_description","rclr.status as load_status","rclr.txn_no as load_txn_no"))
                ->joinLeft(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST .' as rr', "rtc.txn_code = rr.txn_code",array("rr.status as remittance_status", "rr.sender_msg as remittance_description","rr.txnrefnum as remittance_txn_no"))
                ->joinLeft(DbTable::TABLE_RATNAKAR_WALLET_TRANSFER .' as rwt', "rtc.txn_code = rwt.txn_code",array("rwt.status as wallet_status", "rwt.narration as wallet_description","rwt.txnrefnum as wallet_txn_no"));
        
        $details->where("rtc.customer_master_id =?",$ratCustMasterId);
        $details->where("rtc.product_id =?",$productID);
        $details->where("rtc.txn_type IN ('". TXNTYPE_RAT_CORP_CORPORATE_LOAD."','".TXNTYPE_RAT_CORP_MEDIASSIST_LOAD."','".TXNTYPE_CARD_RELOAD."','".TXNTYPE_CARD_DEBIT."','".TXNTYPE_REMITTANCE."','".TXNTYPE_WALLET_TOWALLET_FUND_TRANSFER."')");  
   
        if( ($walletCode !='') && ($walletCode == 'all') ){
          $details->where( "(rclr.status = '".STATUS_LOADED."') OR (rwt.status = '".STATUS_SUCCESS."') OR  (rr.status = '" . STATUS_SUCCESS . "' OR rr.status = '" . STATUS_REFUND . "')"); 
        }elseif($walletCode !=''){
           $details->where("pm.code =?",$walletCode);  
        }

        $details->order('rtc.id DESC');
        $details->limit(TXN_HISTORY_COUNT);   
        $res = $this->_db->fetchAll($details);
       // return $res;
       if(!empty($res)){
           return $res;
       }else{
           return FALSE;
       }
       
   }
   
//   public function getVoucherDetails($params) {
//        $voucherNum = isset($params['voucher_num']) ? $params['voucher_num'] : '';
//        $productID = isset($params['product_id']) ? $params['product_id'] : '';
//        $mode = isset($params['mode']) ? strtolower($params['mode']) : '';
//        $select = $this->_db->select();
//        $select->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST . " as cl", array('*'))
//        ->joinLeft(DbTable::TABLE_RAT_CORP_CARDHOLDER . ' as cc', "cl.cardholder_id = cc.id", array('mobile'));
//        
//        if($voucherNum !=''){
//            $select->where('cl.voucher_num = ?', $voucherNum);
//        }        
//        if($productID !=''){
//            $select->where('cl.product_id = ?', $productID);
//        }
//        if($mode !=''){
//            $select->where('cl.mode = ?', $mode);
//        }
//        $select->where('cl.status = ?', STATUS_LOADED);
//        $rs = $this->_db->fetchRow($select);
//        if (!empty($rs)) {
//            return $rs;
//        } else {
//            return FALSE;
//        }
//     }
 
	public function doCardDebit($params){
        
        $loadRequestId = $params['load_request_id'];
        $customerMasterId = $params['customer_master_id'];
        $purseMasterId = $params['purse_master_id'];
        $customerPurseId = $params['customer_purse_id'];
        $productId = $params['product_id'];
        $amount = Util::convertToRupee($params['amount']);
        $ratCustomerId = $params['rat_customer_id'];
        $cardNumber = $params['card_number'];
        $mobile = $params['mobile'];
        $txnCode = $params['txn_code'];
        $agent_id = $params['by_api_user_id'];
        $bank_id = $params['bank_id'];
        $walletCode = isset($params['wallet_code']) ? $params['wallet_code'] : '';
        $voucher_num = isset($params['voucher_num']) ? $params['voucher_num'] : '';
        $isReversal = isset($params['isReversal']) ? $params['isReversal'] : '';
        $requireECS = isset($params['require_ecs']) ? $params['require_ecs'] : '';
        $manageType = isset($params['manageType']) ? $params['manageType'] : ''; 
        $bank_product_const = isset($params['bank_product_const']) ? $params['bank_product_const'] : '';
                
        $baseTxn = new BaseTxn();
        $object = new Corp_Ratnakar_Cardholders();
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        /*
         * getProductPurseBasicDetails :Getting all wallets with priority basis 
         */
        $param = array(
         'wallet_code' => $walletCode,
         'product_id'  => $productId
        );
        
        try 
        { 
            $masterPurseDetails = $masterPurseModel->getProductWalletPurseBasicDetails($param, 'priority');

            $custPurseLoadDetail = array();
            $loadAmount = $amount;
            $requireAmount = $amount;
            $custPurseLoadDetail[TOTAL_ACCEPTED_AMOUNT] = 0;
            $debitedGenWallet = array();
            $ECS_amount = $amount;
           foreach($masterPurseDetails as $key=>$masterPurse)
            {
               if($loadAmount > $custPurseLoadDetail[TOTAL_ACCEPTED_AMOUNT]){
                    $loadParams = array(
                     'customer_master_id' => $customerMasterId,
                     'purse_master_id' => $masterPurse['id'],
                     'product_id' => $productId,
                     'load_amount' => $amount 
                      );
                    $custPurse = $custPurseModel->getCustPurseDetails(array('rat_customer_id' => $ratCustomerId, 'purse_master_id' => $masterPurse['id']));
                    
                    if(!empty($custPurse) && ($custPurse['amount']>0)){
                       //
			$custPurseId = $custPurse['id'];
			$params['claim_amount'] = (isset($params['claim_amount']))?$params['claim_amount']:0;
			$custPurseAmount = $custPurse['amount'] - $custPurse['block_amount'] + $params['claim_amount'] ;
                       $loadParams['customer_purse_id'] = $custPurseId; 

                       if($requireAmount >= $custPurseAmount){
                        $loadParams['request_amount'] = $custPurseAmount; 
                        $requireAmount -= $custPurseAmount;
                       }else{
                        $loadParams['request_amount'] = $requireAmount; 
                        $requireAmount = 0;
                       }

                   //}
               
                   if($masterPurse['allow_expiry'] == FLAG_YES){
                   $loadParams['require_load'] = FLAG_YES;
                   $loadParams['voucher_num'] = $voucher_num;
                   $custPurseBal =  $this->chkAllowRatLoadCardDebitAPI($loadParams);
                   if(!empty($custPurseBal)){
                       $requireAmount += $custPurseBal['balance_amount'];
                       $custPurseLoadDetail[$key] = $custPurseBal;
                     }else{
                       //Error 
                       $requireAmount += $loadParams['request_amount'];
                     }
                   }elseif($voucher_num ==''){
                    $loadParams['accept_amount'] = $loadParams['request_amount'];
                    $loadParams['require_load'] = FLAG_NO;
                    $custPurseLoadDetail[$key] = $loadParams; 
                   }
                   
                   if($masterPurse['is_virtual'] == FLAG_NO){
                     $debitedGenWallet['amount']  +=  $loadParams['request_amount'];
                   }
                 
                  $custPurseLoadDetail[TOTAL_ACCEPTED_AMOUNT] += $custPurseLoadDetail[$key]['accept_amount']; 
                 }
              }
             
            }
               // if($isReversal!= REVERSAL_FLAG_YES){
                if( ($loadAmount != $custPurseLoadDetail[TOTAL_ACCEPTED_AMOUNT])){
                    $voucher_msg = '';
                    if($voucher_num !=''){
                     $voucher_msg = " for this voucher code:".$voucher_num;   
                    }
                    App_Logger::log("Customer does not have sufficient fund in the wallet".$voucher_msg.". Available Balance: ".Util::numberFormat($custPurseLoadDetail['total_accepted_amount']).". Amount tried: ".Util::numberFormat($loadAmount), Zend_Log::ALERT);
                    throw new App_Exception ("Customer does not have sufficient fund in the wallet".$voucher_msg.". Balance: ".Util::numberFormat($custPurseLoadDetail['total_accepted_amount']).". Available Amount tried: ".Util::numberFormat($loadAmount),  ErrorCodes::ERROR_INSUFFICIENT_BALANCE);
             //   }
            }else{
                
                if(!empty($debitedGenWallet) && ($requireECS != FLAG_YES ) ){
                    
                    $requireECS = FLAG_YES;
                    $ECS_amount = $debitedGenWallet['amount'];
                }
               
                /*
                 * ECS call:
                 */
                $ecsApi = new App_Socket_ECS_Corp_Transaction();
                $txn_load_id = 0;
                if( ($requireECS == FLAG_YES) && ($cardNumber!='') && ($agent_id!='') ){
                    
                    $cardLoadData = array(
                            'amount' => $ECS_amount,
                            'crn' => $cardNumber,
                            'agentId' => $agent_id,
                            'transactionId' => $txnCode,
                            'currencyCode' => CURRENCY_INR_CODE,
                            'countryCode' => COUNTRY_IN_CODE
                        );
                    
                    if(DEBUG_MVC) {
                        $apiResp = TRUE;
                    } else {
                        $apiResp = $ecsApi->cardDebit($cardLoadData); // bypassing for testing
                    }
                     if ($apiResp === TRUE) {
                         $txn_load_id = $ecsApi->getISOTxnId();
                     }else {
                            $failedReason = $ecsApi->getError();
                            $loadStatus = STATUS_FAILED;
                            $date_failed = new Zend_Db_Expr('NOW()');
                            $updateArr = array(
                                'amount_available' => 0,
                                'amount_used' => 0,
                                'amount_cutoff' => 0,
                                'status' => STATUS_FAILED,
                                'date_failed' => new Zend_Db_Expr('NOW()'),
                                'failed_reason' => $failedReason,
                                'date_load' => new Zend_Db_Expr('NOW()')
                            );
                            $this->_db->rollBack();
                            $this->updateLoadRequests($updateArr, $loadRequestId);
                            
                            throw new Exception(ErrorCodes::ERROR_EDIGITAL_TXN_FAILED_MSG, ErrorCodes::ERROR_EDIGITAL_TXN_FAILED_CODE);
                          
                        }    
                    
                }
                
                //
                foreach($custPurseLoadDetail as $PurseKey=> $custPurseRecords){
                    if((string)$PurseKey != TOTAL_ACCEPTED_AMOUNT){
                        
                        $baseTxnParams = array(
                        'txn_code' => $txnCode,
                        'customer_master_id' => $custPurseRecords['customer_master_id'],
                        'product_id' => $productId,
                        'bank_id' => $params['bank_id'],
                        'purse_master_id' => $custPurseRecords['purse_master_id'],
                        'customer_purse_id' => $custPurseRecords['customer_purse_id'],
                        'amount' => $custPurseRecords['accept_amount'],
                        'agent_id' => $params['by_api_user_id'],
                        'ip' => $this->formatIpAddress(Util::getIP()),
                        'manageType' => $params['manageType'],
                        'debit_load_id' => $loadRequestId,
                        'bank_id' => $bank_id,
                        'voucher_num'=> $voucher_num,
                        'manageType'=> $manageType    
                         );
                        
                       $debitedLoadId = $this->insertRatDebitDetail($baseTxnParams);
                        
                        if($custPurseRecords['require_load'] == FLAG_YES){
                            $baseTxnParams['debit_detail_id'] = $debitedLoadId;
                            $this->mapLoadDebitTxn($baseTxnParams);
                            
                        }
                        
                        
                        $baseTxn->successRatMediAssistCardDebit($baseTxnParams);
                        
                        
                    }      

                }
                
               // $loadStatus = STATUS_SUCCESS;

                $updateArr = array(
                    'amount_available' => 0,
                    'amount_used' => 0,
                    'amount_cutoff' => 0,
                    'txn_load_id' => $txn_load_id,
                    'status' => STATUS_DEBITED,
                    'date_load' => new Zend_Db_Expr('NOW()')
                );
               

                $this->updateLoadRequests($updateArr, $loadRequestId);

                $custPurse = $custPurseModel->getCustBalance($ratCustomerId);

                $cardholderArray['cardNumber'] = $cardNumber;


                // fetch Balance
                  $balVal = $custPurse['sum'];

                if (strtolower($params['sms_flag']) == strtolower(FLAG_Y)) {
                     // Send SMS
                      $userData = array(
                      'last_four' =>substr($cardNumber, -4),
                      'product_id' => $productId,
                      'mobile' => $mobile,
                      'amount' => $amount,
                      'balance' => $balVal,
                     );
                      $object->generateSMSDetails($userData, $smsType = TRANSACTION_REQUEST_DR); 
                      } 
            }
        } catch (Exception $e) {
          //  $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage(),$e->getCode());
        }          
    }
   
   public function doCardloadAPI($dataArr) {
       
        if (empty($dataArr)) {
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_CARDLOAD_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_CARDLOAD_CODE);
        }
        $custModel = new Corp_Ratnakar_Cardholders();
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $baseTxn = new BaseTxn();
        $object = new Corp_Ratnakar_Cardholders();
        $productModel = new Products();
        $str = '';
        $priorityWalletCode = '';
        $loadStatus = STATUS_FAILED;
        $product = App_DI_Definition_BankProduct::getInstance($dataArr['bank_product_const']);
        $genWalletCode = $product->purse->code->genwallet;
        $productId = $dataArr['product_id'];
        $ecsCall = FALSE;
        $apiResp = TRUE;
      //  $masterPriPurseDetails = $masterPurseModel->getProductPurseBasicDetails($productId, 'priority');
        //
        /*
         * Partical Transaction Validation
         */
        $validPurseCode = true;
        $validProduct = true;
        $allowVouncher  = true; 
        $walletCode = $dataArr['wallet_code'];
        $loadExpiry = $dataArr['Filler1'];
        $masterPriPurseDetails = $masterPurseModel->getProductPurseBasicDetails($productId, 'priority');
        
        if(!empty($masterPriPurseDetails)){
                    $priorityWalletCode = $masterPriPurseDetails[0]['code'];
                   
                    $allMasterPurseCodes = array_column($masterPriPurseDetails, 'code');
                     
                    $masterPurseDetail = $masterPurseModel->getPurseLoadInfo($param = array('product_id'=>$productId));
                    $expiryMasterPurseCodes = array_column($masterPurseDetail, 'code');
                    
                    if( ( ($walletCode !='') && (!in_array($walletCode,$allMasterPurseCodes)) ) )
                    {
                        $validPurseCode = false;
                    }elseif( ( ($walletCode !='') && (in_array($walletCode,$expiryMasterPurseCodes)) ) || ( (($walletCode =='') ) && ( ($priorityWalletCode !='' ) && (in_array($priorityWalletCode,$expiryMasterPurseCodes)) ) )  )
                    {
                       // Allow Load Expiry or Voucher Code
                         $allowVouncher  = true; 
                    }elseif( ( ( ($walletCode !='') && (!in_array($walletCode,$expiryMasterPurseCodes)) ) || ( (($walletCode =='') ) && ( ($priorityWalletCode !='' ) && (!in_array($priorityWalletCode,$expiryMasterPurseCodes)) ) )  ) && (strtolower($dataArr['mode']) == TXN_MODE_CR) )
                    {
                        if(  ($dataArr['Filler1'] !='' ) || ( $dataArr['Filler2'] !='' ) || ($dataArr['Filler3'] !='')  || ($dataArr['Filler4'] !='')  || ($dataArr['Filler5'] !='') ){
                        $allowVouncher  = false;   
                        }
                        $voucherIndicator = '';
                        $voucherCode = '';
                    }else{
                      //      
                    }

            }else{
                    $validProduct = false;  
            }       
        //
        if($dataArr['bank_id'] ==''){
        $productDetail = $productModel->getProductInfo($dataArr['product_id']);
        $dataArr['bank_id'] = $productDetail['bank_id'];  
        }
        
        try {
            $cardholderId = $dataArr['cardholder_id'];
            
            $pursecode = ($dataArr['wallet_code'] != '')?$dataArr['wallet_code']: $priorityWalletCode;
            
            $searchArr = array('cardholder_id' => $cardholderId, 'status' => STATUS_ACTIVE);
            $cardholderDetails = $custModel->getCardholderInfo($searchArr);
            if(!isset($cardholderDetails->id) || $cardholderDetails->id == '')
            {
                $loadStatus = STATUS_FAILED;
                $failedReason = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                $dateFailed = new Zend_Db_Expr('NOW()');   
                $dateLoad = new Zend_Db_Expr('NOW()');   
            } else {
                $loadStatus = STATUS_PENDING;
                $failedReason = '';
                $failedCode = '';
                $dateFailed = '';   
                $dateLoad = '';
            }
            
            $cardNumber = $cardholderDetails->card_number;
            $mediAssistId = $cardholderDetails->medi_assist_id;
            $customerMasterId = ($cardholderDetails->customer_master_id != '') ? $cardholderDetails->customer_master_id : 0;
            $ratCustomerId = ($cardholderDetails->rat_customer_id != '') ? $cardholderDetails->rat_customer_id : 0;
            $customerPurseId = 0;
            $masterPurseDetails = $masterPurseModel->getPurseIdByPurseCodeAPI($pursecode,$productId);
            if($ratCustomerId > 0) { 
                if(!empty($masterPurseDetails)){
                    $purseDetails = $custPurseModel->getCustPurseDetails(array('rat_customer_id' => $ratCustomerId, 'purse_master_id' => $masterPurseDetails['id']));
                    $customerPurseId = (isset($purseDetails['id']) && $purseDetails['id'] > 0) ? $purseDetails['id'] : 0;
                }else{
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_CODE);
                }
            }
            
            $isVirtual = strtolower($masterPurseDetails['is_virtual']);
            $purseMasterId = $masterPurseDetails['id'];
            $amount = 0;
            
            /*
             *Active voucher limit validation 
             */ 
            $allowVoucherLimit = true;
          if(!empty($masterPurseDetail)){
                $voucherPurseMasterIdList = array_column($masterPurseDetail, 'id'); 
                $voucherPurseMasterIds = implode(",",$voucherPurseMasterIdList);
                if( (strtolower($dataArr['mode']) == TXN_MODE_CR) && (in_array($purseMasterId,$voucherPurseMasterIdList) ) ){
              //    if( strtolower($dataArr['mode']) == TXN_MODE_CR ){
                $totalActiveVoucherNum = $this->getActiveVoucherNumofProductAPI(array('product_id' => $dataArr['product_id'],'customer_master_id' => $customerMasterId,'purse_master_id' => $voucherPurseMasterIds));

                if( (!empty($totalActiveVoucherNum) && $totalActiveVoucherNum['total_active_voucher_num'] > ALLOW_VOUCHER_LIMIT ) ){
                    $allowVoucherLimit = false;
                  }
                 }
            }
            //
            if($loadStatus == STATUS_PENDING)
            {
                if(!$validPurseCode)
                  {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_MSG;
                    $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_CODE;
                    $dateFailed = new Zend_Db_Expr('NOW()');  
                    $dateLoad = new Zend_Db_Expr('NOW()');
                    $amount = $dataArr['amount'];
                }
                elseif($dataArr['product_id'] != $masterPurseDetails['product_id'])
                {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_MSG;
                    $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_CODE;
                    $dateFailed = new Zend_Db_Expr('NOW()');  
                    $dateLoad = new Zend_Db_Expr('NOW()');
                    $amount = $dataArr['amount'];
                }
                elseif(strpos($dataArr['amount'],'.') !== FALSE)
                {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = ErrorCodes::ERROR_EDIGITAL_INVALID_REVERSAL_AMOUNT_MSG;
                    $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_REVERSAL_AMOUNT_CODE;
                    $dateFailed = new Zend_Db_Expr('NOW()');  
                    $dateLoad = new Zend_Db_Expr('NOW()');
                    $amount = $dataArr['amount'];
                }
                elseif(strpos($dataArr['amount'],' ') !== FALSE)
                {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = ErrorCodes::ERROR_EDIGITAL_INVALID_REVERSAL_AMOUNT_MSG;
                    $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_REVERSAL_AMOUNT_CODE;
                    $dateFailed = new Zend_Db_Expr('NOW()');  
                    $dateLoad = new Zend_Db_Expr('NOW()');
                    $amount = $dataArr['amount'];
                }
                elseif(strtolower($dataArr['card_type']) != strtolower(CORP_CARD_TYPE_NORMAL))
                {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_TYPE_MSG;
                    $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_TYPE_CODE;
                    $dateFailed = new Zend_Db_Expr('NOW()');  
                    $dateLoad = new Zend_Db_Expr('NOW()');
                    $amount = $dataArr['amount'];
                }
                elseif(!$allowVouncher)
                  {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = ErrorCodes::ERROR_EDIGITAL_VOUCHER_FEATURE_NOT_PERMITTED_MSG;
                    $failedCode = ErrorCodes::ERROR_EDIGITAL_VOUCHER_FEATURE_NOT_PERMITTED_CODE;
                    $dateFailed = new Zend_Db_Expr('NOW()');  
                    $dateLoad = new Zend_Db_Expr('NOW()');
                    $amount = $dataArr['amount'];
                }elseif(!$allowVoucherLimit)
                  {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = ErrorCodes::voucherLimitExceed();
                    $failedCode = ErrorCodes::ERROR_EDIGITAL_VOUCHER_LIMIT_EXCEED_CODE;
                    $dateFailed = new Zend_Db_Expr('NOW()');  
                    $dateLoad = new Zend_Db_Expr('NOW()');
                    $amount = $dataArr['amount'];
                }
            }

            if($amount != $dataArr['amount'])
            {
                $amount = Util::convertToRupee($dataArr['amount']);
            }
            //$txnCode = $baseTxn->generateTxncode();
            $txnCode = $dataArr['txn_code'];
            if($txnCode == ''){
              $txnCode = $baseTxn->generateTxncode();   
            }
            
            
            /*
             * Reversal Checks
             */
            $revOrigTxn_loadId = 0;
            $isReversal = 'n';
            /*
             * 
             */
            $date_expiry = $dataArr['date_expiry'];
            $loadMode = strtolower($dataArr['mode']);
            if (strtolower($dataArr['mode']) == TXN_MODE_DR) {
                $txnType = TXNTYPE_CARD_DEBIT;
              //  $loadStatus = STATUS_DEBITED;
            }elseif (strtolower($dataArr['mode']) == TXN_MODE_CR) {
                $txnType = TXNTYPE_CARD_RELOAD;
                if($date_expiry == ''){
                    $date_expiry = Util::getdefaultExpiryDate();
                }
            }else {
                $txnType = '';
                $loadStatus = STATUS_FAILED;
                $failedReason = ErrorCodes::getMode($dataArr['mode']);
                $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_MODE_CODE;
                $dateFailed = new Zend_Db_Expr('NOW()');  
                $dateLoad = new Zend_Db_Expr('NOW()');
                $amount = $dataArr['amount'];
            }  

            
            // Duplicacy check for TxnNo
            $loadDetails = $this->getLoadDetails(array('txn_no' => $dataArr['txn_no'], 'product_id'=>$dataArr['product_id']));
            if (!empty($loadDetails) ) {                
                $loadStatus = STATUS_FAILED;
                $failedReason = 'Txn No already in use';
                $dateFailed = new Zend_Db_Expr('NOW()');  
                $dateLoad = new Zend_Db_Expr('NOW()');
                $amount = $dataArr['amount'];
            }

            if ( (strtolower($dataArr['mode']) == TXN_MODE_DR) && ($dataArr['wallet_code']=='')  && (sizeof($masterPriPurseDetails) > 1) ){
                $customerPurseId = 0; 
                $purseMasterId = 0;
                $pursecode = '';
            }
            //$encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            //$cardNumberEnc = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')");
            $data = array(
                'customer_master_id' => $customerMasterId,
                'cardholder_id' => $cardholderId,
                'customer_purse_id' => $customerPurseId,
                'txn_type' => $txnType,
                'load_channel' => BY_API,
                'purse_master_id' => $purseMasterId,
                'txn_identifier_type' => $dataArr['txn_identifier_type'],
                'txn_identifier_num' => $dataArr['txn_identifier_num'],
                'card_number' => Util::insertCardCrn($cardNumber),
                'medi_assist_id' => $mediAssistId,
                'amount' => $amount,
                'amount_available' => 0,
                'amount_used' => 0,
                'amount_cutoff' => 0,
                'currency' => CURRENCY_INR_CODE,
                'narration' => $dataArr['narration'],
                'wallet_code' => strtoupper($pursecode),
                'txn_no' => $dataArr['txn_no'],
                'card_type' => $dataArr['card_type'],
                'corporate_id' => $dataArr['corporate_id'],
                'mode' => $dataArr['mode'],
                'txn_code' => $txnCode,
                'ip' => $this->formatIpAddress(Util::getIP()),
                'by_ops_id' => 0,
                'batch_name' => '',
                'bank_id' => $dataArr['bank_id'],
                'product_id' => $dataArr['product_id'],
                'status' => $loadStatus,
                'date_created' => new Zend_Db_Expr('NOW()'),
                'failed_reason' => $failedReason,
                'date_failed' => $dateFailed,
                'date_load' => $dateLoad,
                'date_expiry' => $date_expiry,
                'voucher_num' => $dataArr['voucher_num'],
                'is_reversal' => $isReversal,
                'original_transaction_id' => $revOrigTxn_loadId,
                'channel' => $dataArr['channel']

            );
            if($dataArr['manageType'] == CORPORATE_MANAGE_TYPE){
                $data['by_corporate_id'] = $dataArr['by_api_user_id'];
                
            }
            else{
                $data['by_agent_id'] = $dataArr['by_api_user_id'];
                $data['by_corporate_id'] = 0;
            }
            
            $this->insert($data);
            $loadRequestId = $this->_db->lastInsertId(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, 'id');
            $this->_db->beginTransaction();
            if($loadStatus == STATUS_PENDING ){
                    if($loadMode == TXN_MODE_DR) {
                        $custDebitDetails = array();
                        $custDebitDetails = array_merge($custDebitDetails, $dataArr);
                        $custDebitDetails['load_request_id']= $loadRequestId;
                        $custDebitDetails['customer_master_id']= $customerMasterId;
                        $custDebitDetails['purse_master_id']= $purseMasterId;
                        $custDebitDetails['customer_purse_id']= $customerPurseId;
                        $custDebitDetails['rat_customer_id']= $cardholderDetails->rat_customer_id;
                        $custDebitDetails['card_number']= $cardholderDetails->card_number;
                        $custDebitDetails['mobile']= $cardholderDetails->mobile;
                        $custDebitDetails['txn_code']= $txnCode;
                        $custDebitDetails['is_reversal']= $isReversal;
                        $custDebitDetails['original_transaction_id']= $revOrigTxn_loadId;
			
		
                        $this->doCardDebit($custDebitDetails);
                    }
                    elseif($loadMode == TXN_MODE_CR ) {

                        $validator = array(
                            'load_request_id' => $loadRequestId,
                            'customer_master_id' => $customerMasterId,
                            'purse_master_id' => $purseMasterId,
                            'customer_purse_id' => $customerPurseId,
                            'amount' => $amount,
                            'agent_id' => $dataArr['by_api_user_id'],
                            'product_id' => $productId,
                            'bank_id' => $dataArr['bank_id'],
                            'manageType' => $dataArr['manageType'],
                            'is_virtual' => $isVirtual,
                            'isReversal' => $isReversal,
                            'revLoadId' => $revOrigTxn_loadId    
                        );

                        $flgValidate = $baseTxn->chkAllowRatMediAssistCardLoadAPI($validator);
                        if ($flgValidate) {
                            $loadStatus = STATUS_SUCCESS;
			if($masterPurseDetails['code'] == $genWalletCode && $cardholderDetails->card_number != '') {
				$ecsCall = TRUE;
				$cardLoadData = array(
                                    'amount' => (string) $amount,
                                    'crn' => $cardholderDetails->card_number,
                                    'agentId' => $dataArr['by_api_user_id'],
				    'transactionId' => $txnCode,
				    'currencyCode' => CURRENCY_INR_CODE,
				    'countryCode' => COUNTRY_IN_CODE
				    );
                            if(DEBUG_MVC) {
                            $apiResp = TRUE;
                            $ecsCall = FALSE;
                            } else {
                                $ecsApi = new App_Socket_ECS_Corp_Transaction();
                                $apiResp = $ecsApi->cardLoad($cardLoadData);
                                }
                             }

        if ($apiResp === TRUE) {
                $updateArr = array(
                    'amount_available' => $amount,
                    'amount_used' => 0,
                    'amount_cutoff' => 0,
                    'txn_load_id' => $ecsCall == TRUE ? $ecsApi->getISOTxnId() : '',
                    'status' => STATUS_LOADED,
                    'date_load' => new Zend_Db_Expr('NOW()')
                );

                $this->updateLoadRequests($updateArr, $loadRequestId);

                $baseTxnParams = array(
                    'txn_code' => $txnCode,
                    'customer_master_id' => $customerMasterId,
                    'product_id' => $productId,
                    'bank_id' => $dataArr['bank_id'],
                    'purse_master_id' => $purseMasterId,
                    'customer_purse_id' => $customerPurseId,
                    'amount' => $amount,
                    'agent_id' => $dataArr['by_api_user_id'],
                    'txn_type' => TXNTYPE_CARD_RELOAD,
                    'ip' => $this->formatIpAddress(Util::getIP()),
                    'manageType' => $dataArr['manageType'],
                    'is_virtual' => $isVirtual
                );
                
                $baseTxn->successRatCardLoad($baseTxnParams);
                
                if($ecsCall == TRUE) {
                    $cardholderArray['cardNumber'] = $cardholderDetails->card_number;

                    $ecsApi = new App_Api_ECS_Transactions();
                    $res = $ecsApi->balanceInquiry($cardholderArray);
                                  
                    $sendSMS = TRUE;
                    if($dataArr['bank_product_const'] == BANK_RATNAKAR_BOOKMYSHOW){
                        $sendSMS = FALSE;
                    }
                    if($sendSMS){
                        if (strtolower($dataArr['sms_flag']) == strtolower(FLAG_Y)) {
                            // Send SMS
                            $userData = array(
                             'last_four' =>substr($cardholderDetails->card_number, -4),
                             'product_id' => $productId,
                             'mobile' => $cardholderDetails->mobile,
                             'amount' => $amount,
                             'balance' => $balVal,
                            );
                            $object->generateSMSDetails($userData, $smsType = TRANSACTION_REQUEST_CR); 
                        }
                    }

                    if($res){
                        $balVal = $res->balanceInquiryList->availablebalance;                          
                    } else {
                        $balVal = '';  
                    }
                } else {
                    $custPurse = $custPurseModel->getCustBalance($cardholderDetails->rat_customer_id);
                    $balVal = $custPurse['sum']; 
                }

                    if (strtolower($dataArr['sms_flag']) == strtolower(FLAG_Y)) {
                                                        // Send SMS
                    $userData = array(
                     'last_four' =>substr($cardholderDetails->card_number, -4),
                     'product_id' => $productId,
                     'mobile' => $cardholderDetails->mobile,
                     'amount' => $amount,
                     'balance' => $balVal,
                    );
           $object->generateSMSDetails($userData, $smsType = TRANSACTION_REQUEST_CR); 
                 }
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
        
                }else{
                  throw new Exception($failedReason,$failedCode);  
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
                $code = $e->getCode();
                if(empty($code)) {
                    $code = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                }
                //$code = (!empty($e->getCode())) ? $e->getCode() : ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                throw new Exception($e->getMessage(), $code);
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
                $code = $e->getCode();
                if(empty($code)) {
                    $code = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                }
                //$code = (!empty($e->getCode())) ? $e->getCode() : ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                throw new Exception($e->getMessage(), $code);
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
            $code = $e->getCode();
            if(empty($code)) {
                $code = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
            }
                
            //$code = (!empty($e->getCode())) ? $e->getCode() : ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
            throw new Exception($e->getMessage(), $code);
        }
        return array('status' => STATUS_LOADED);
    }
    
    public function chkAllowRatLoadCardDebitAPI($params){
        $voucher_num = isset($params['voucher_num']) ? $params['voucher_num'] : '';
        $currentDate = date('Y-m-d H:i:s');
        $custPurseInfo = array();
        $select = $this->select()
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('sum(amount_available) as total_amount'))
                ->where('product_id = ?', $params['product_id'])
                ->where('customer_purse_id = ?', $params['customer_purse_id'])
                ->where('amount_available > 0')
                ->where("date_expiry >='".$currentDate."' OR date_expiry='0000-00-00 00:00:00'")
                ->where('status = ?', STATUS_LOADED);
        if($voucher_num !=''){
        $select->where('voucher_num = ?', $voucher_num);
        }
        //$select->order('id ASC');
        $select->order('date_expiry ASC');
        $rs = $this->_db->fetchRow($select);
        if(!empty($rs) && ($rs['total_amount'] !='') ){
            $custPurseInfo = array_merge($custPurseInfo, $params);
            if($rs['total_amount'] !=''){
                if($params['request_amount'] > $rs['total_amount']){   
                $custPurseInfo['accept_amount'] = $rs['total_amount'];
                $custPurseInfo['balance_amount'] = $params['request_amount'] - $rs['total_amount']; 
                }else{
                $custPurseInfo['accept_amount'] = $params['request_amount'];
                $custPurseInfo['balance_amount'] = 0; 
                }
            }else{
                $custPurseInfo['accept_amount'] = 0;
                $custPurseInfo['balance_amount'] = $params['request_amount'];  
            }
         }
        
        return $custPurseInfo;
    }
    
   
//    public function getRatRequestsForSettlementBatch($params){
//      $recordType = isset($params['type']) ? $params['type'] : 'WITHOUT_CARD'; 
//      $batchDate = isset($params['batch_date']) ? $params['batch_date'] : new Zend_Db_Expr('NOW()');
//      $debitDate = isset($params['debit_date']) ? $params['debit_date'] : date("Y-m-d", strtotime("-1 day"));
//      $dateFormat = Util::getNeftBatchFileName();
//      $batchName ='';
//     // $batchDate = new Zend_Db_Expr('NOW()');
//      if($recordType == 'WITHOUT_CARD'){
//         $batchSubName = 'OW';
//      }else{
//         $batchSubName = 'CW';  
//      }
//     // $unsettledRequests = array();
//      $unsettledRequests = $this->getAgentUnSettementRequests(array('status' => STATUS_DEBITED,'type'=>$recordType,'status_settlement' => STATUS_UNSETTLED,'debit_date'=>$debitDate));
//      // Creating BatchName for without card records
//      $totalUnsettledRequests = count($unsettledRequests);
//       if($totalUnsettledRequests > 0){
//           
//            $batchName = RATNAKAR_REMIT_BATCH_NAME_PREFIX."_".$batchSubName.$dateFormat;
//            $param = array(
//                'request_file_name' => $batchName,
//                'status' => STATUS_STARTED
//            );
//            // Insert into rat_settlement_request table
//            $resp = $this->_db->insert(DbTable::TABLE_RAT_SETTLEMENT_REQUEST, $param); // adding cron log   
//            $settlementRequestId = $this->_db->lastInsertId(DbTable::TABLE_RAT_SETTLEMENT_REQUEST, 'id');
//            $unsettledRequests['settlement_req_id'] = $settlementRequestId;
//            $unsettledRequests['batch_name'] = $batchName;
//        return $unsettledRequests;    
//       }
//       
//       return false;
//    }

    public function getRatRequestsForSettlementBatch($params){
      $recordType = isset($params['type']) ? $params['type'] : 'WITHOUT_CARD'; 
      $batchDate = isset($params['batch_date']) ? $params['batch_date'] : new Zend_Db_Expr('NOW()');
      $debitDate = isset($params['debit_date']) ? $params['debit_date'] : date("Y-m-d", strtotime("-1 day"));
      $dateFormat = Util::getNeftBatchFileName();
      $batchName ='';
     // $batchDate = new Zend_Db_Expr('NOW()');
      if($recordType == 'WITHOUT_CARD'){
         $batchSubName = 'OW';
      }else{
         $batchSubName = 'CW';  
      }
     // $unsettledRequests = array();
      $unsettledRequests = $this->getAgentUnSettementRequests(array('status' => STATUS_DEBITED,'type'=>$recordType,'status_settlement' => STATUS_UNSETTLED,'debit_date'=>$debitDate));
      // Creating BatchName for without card records
      $totalUnsettledRequests = count($unsettledRequests);
       if($totalUnsettledRequests > 0){
           
            $batchName = RATNAKAR_REMIT_BATCH_NAME_PREFIX."_".$batchSubName.$dateFormat;
            $param = array(
                'request_file_name' => $batchName,
                'status' => STATUS_STARTED
            );
            // Insert into rat_settlement_request table
            $resp = $this->_db->insert(DbTable::TABLE_RAT_SETTLEMENT_REQUEST, $param); // adding cron log   
            $settlementRequestId = $this->_db->lastInsertId(DbTable::TABLE_RAT_SETTLEMENT_REQUEST, 'id');
            $unsettledRequests['settlement_req_id'] = $settlementRequestId;
            $unsettledRequests['batch_name'] = $batchName;
        return $unsettledRequests;    
       }
       
       return false;
    }
		
    public function manualMapLoadTxn($arrTxn) {
        
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
                            'txn_processing_id' => 0,
                            'txn_code' => $arrTxn['txn_code'],
                            'txn_type' => TXNTYPE_DEBIT_MANUAL_ADJUSTMENT,
                            'amount' => 0,
                            'load_request_id' => 0,
                            'adjust_id' =>  $arrTxn['adjust_id'],
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
    
		
    public function updateRatRequestsForSettlementBatch($params,$agentTxnNumList){
     
      // Creating BatchName for without card records
      $totalUnsettledRecords = count($params);
       if($totalUnsettledRecords > 0){
           try{
           //
                $settlement_request_id = $params['settlement_req_id'];
                foreach($params as $key=>$records){
                    if( ((string)$key !='settlement_req_id') && ((string)$key !='batch_name') ){
                        $requestId = $records['request_id'];
                        $agentId = $records['by_agent_id'];
                        $proId = $records['product_id'];
                        $unqueId = $agentId.$proId; 
                        $settlement_ref_no = $agentTxnNumList[$unqueId]['txn_code'];
                        $param = array(
                         'settlement_request_id' => $settlement_request_id,
                         'settlement_ref_no' => $settlement_ref_no
                                );
                       
                        //$this->_db->update(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, $param, "id= $requestId");
                        $updateRequest = $this->_db->update(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, $param, 'id = '.$requestId);

                    }
                }
                
                //Update Settlement Status
                $param_settlement = array(
                    'status' => STATUS_ACTIVE
                );
                $this->_db->update(DbTable::TABLE_RAT_SETTLEMENT_REQUEST, $param_settlement, "id= $settlement_request_id");
                return true;
          }catch (App_Exception $e) {
                
                $message = $e->getMessage();
                $message = (empty($message)) ? 'Failed updateRatRequestsForSettlementBatch' : $message;
                App_Logger::log($e, Zend_Log::WARN);
            }
              
       }
       
       return false;
    }
    
    public function   getUnsettlementBatchFilesCountArray($params){
       
        $from = $params['from_date'].' 00:00:00';
        $to = $params['to_date'].' 23:59:59';
        
        $select = $this->_db->select();     
        $select->from(DbTable::TABLE_RAT_SETTLEMENT_REQUEST." as sr",array( 'request_file_name as batch_name','sr.id as request_id','date_created as date'));              
        $select->where("sr.date_created BETWEEN '$from' AND '$to'");
        $select->where("sr.status = ?",STATUS_ACTIVE);
        $batchcountArr = $this->_db->fetchAll($select); 
        
       if(!empty($batchcountArr)){
        return $batchcountArr;
       }else{
           return false;
       }

    }
    
    
    public function   getUnsettlementResponse($param){
       
        // foreach($param as $batchName){
        $select = $this->_db->select();     
        $select->from(DbTable::TABLE_RATNAKAR_SETTLEMENT_RESPONSE." as rsr",array('id', 'settlement_ref_no','amount'));              
        $select->where("rsr.status = ?",FLAG_PENDING);
        $row = $this->_db->fetchAll($select); 
        if(!empty($row)){
            return $row;
        }else{
            return false;
        }

    }
    public function   updateUnsettlementResponseRecords($unsettlementResponseRecords){
        try{
             $count = 0;
                foreach($unsettlementResponseRecords as $key => $unsettlementRecord){

                    $responseId =  $unsettlementRecord['id'];
                    $settlementRefNo =  $unsettlementRecord['settlement_ref_no'];
                    $param_settlement = array(
                    'status_settlement' => STATUS_SETTLED,
                    'settlement_response_id' => $responseId,
                    'date_settlement' => new Zend_Db_Expr('NOW()')
                    );

                    $updateRequest = $this->_db->update(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, $param_settlement, 'settlement_ref_no = "'.$settlementRefNo.'" AND status_settlement ="'.STATUS_UNSETTLED.'" AND settlement_request_id > 0 AND status ="'.STATUS_DEBITED.'"');
                    
                    $param = array(
                    'status' => FLAG_SUCCESS   
                    );
                    if($updateRequest){
                      $this->_db->update(DbTable::TABLE_RATNAKAR_SETTLEMENT_RESPONSE, $param, "id= $responseId");  
                      $count++;
                    }

                }
                return $count;
        }catch (App_Exception $e) {
                App_Logger::log($e, Zend_Log::WARN);
                $message = $e->getMessage();
                $message = (empty($message)) ? 'Failed RatSettlementResponse' : $message;
                return self::Exception($message);
            }
    }
    
    public function setRatSettlementRecordsBatch($params){
        $curDate = date('ymd'); 
        $unsettledRecords = array();
        $uniqueList = array();
        
        foreach($params as $key=>$loadInfo){
             if( ((string)$key !='settlement_req_id') && ((string)$key !='batch_name') ){
                $agentId = $loadInfo['by_agent_id'];
                $proId = $loadInfo['product_id'];
                $unqueId = $agentId.$proId;    
                $unsettledRecords[$unqueId]['amount'] += $loadInfo['amount']; 
                if(!in_array($unqueId, $uniqueList)){
                    $timeFormat = Util::getRandTime();
                    $transactionRefNo = $curDate.$timeFormat;

                    $unsettledRecords[$unqueId]['txn_code'] = $transactionRefNo;
                    $unsettledRecords[$unqueId]['date'] = $loadInfo['date'];
                    $unsettledRecords[$unqueId]['agent_name'] = $loadInfo['agent_name'];
                    $unsettledRecords[$unqueId]['agent_account_number'] = $loadInfo['agent_account_number'];
                    $unsettledRecords[$unqueId]['agent_ifsc_code'] = $loadInfo['agent_ifsc_code'];
                    $unsettledRecords[$unqueId]['city'] = $loadInfo['city'];
                    $unsettledRecords[$unqueId]['email'] = $loadInfo['email'];
                    $uniqueList[]=$unqueId;
               
                }
                
             }
        }
      
        return $unsettledRecords;
    }
    
    public function doRblMvcCardLoad($dataArr = array()) {
	
        $custModel = new Corp_Ratnakar_Cardholders();
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        
	$searchArr['cardholder_id'] = $dataArr['cardholder_id'];
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
	    $loadStatus = STATUS_PENDING;
	    $cardNumber = ($searchArr['card_number'] != '') ? $searchArr['card_number'] : $cardholderDetails->card_number;
	    $mediAssistId = ($searchArr['medi_assist_id'] != '') ? $searchArr['medi_assist_id'] : $cardholderDetails->medi_assist_id;
	    $customerMasterId = ($cardholderDetails->customer_master_id != '') ? $cardholderDetails->customer_master_id : 0;
	    $cardholderId = ($cardholderDetails->id != '') ? $cardholderDetails->id : 0;
	    $ratCustomerId = ($cardholderDetails->rat_customer_id != '') ? $cardholderDetails->rat_customer_id : 0;
	    $customerPurseId = 0;
	    // Master Purse id
	    
	    
	   
	    $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_MVC);
	    $pursecode = $product->purse->code->genwallet;
            
	    $masterPurseDetails = $masterPurseModel->getPurseIdByPurseCode($pursecode);
	    // Purse id 
	    if($ratCustomerId > 0) { 
		$purseDetails = $custPurseModel->getCustPurseDetails(array('rat_customer_id' => $ratCustomerId, 'purse_master_id' => $masterPurseDetails['id']));
		$customerPurseId = (isset($purseDetails['id']) && $purseDetails['id'] > 0) ? $purseDetails['id'] : 0;
	    }
	   
	    $amount = 0;
	    if($loadStatus == STATUS_PENDING)
	    {
		if($dataArr['amount'] <= 0){
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
	    }
	   
	    
	    if($amount != $dataArr['amount'])
	    {
		$amount = $dataArr['amount'];
		//$amount = Util::convertToPaisa($dataArr['amount']);
	    }
	    
	    $loadChanel = !empty($dataArr['load_channel']) ? $dataArr['load_channel'] : BY_OPS;
	    
	    $data = array(
		'customer_master_id' => $customerMasterId,
		'cardholder_id' => $cardholderId,
		'customer_purse_id' => $customerPurseId,
		'txn_type' => $dataArr['txn_type'],
		'load_channel' => $loadChanel,
		'purse_master_id' => $masterPurseDetails['id'],
		'txn_identifier_type' => CORP_WALLET_TXN_IDENTIFIER_CN,
		'card_number' => $cardNumber,
		'medi_assist_id' => $mediAssistId,
		'amount' => $amount,
		'amount_available' => 0,
		'amount_used' => 0,
		'amount_cutoff' => 0,
		'currency' => CURRENCY_INR,
		'narration' => $dataArr['narration'],
		'wallet_code' => strtoupper($pursecode),
		'txn_no' => $dataArr['txn_no'],
		'card_type' => CORP_CARD_TYPE_NORMAL,
		'corporate_id' => $dataArr['corporate_id'],
		'mode' => $dataArr['mode'],
		'txn_code' => $dataArr['txn_code'],
		'ip' => $this->formatIpAddress(Util::getIP()),
		'by_ops_id' => $dataArr['by_ops_id'],
		'by_agent_id' => $dataArr['agent_id'],
		'by_corporate_id'=> $dataArr['by_corporate_id'],
		'batch_name' => $dataArr['batch_name'],
		'product_id' => $dataArr['product_id'],
		'status' => $loadStatus,
		'date_created' => new Zend_Db_Expr('NOW()'),
		'failed_reason' => $failedReason,
		'date_failed' => $dateFailed,
		'date_load' => $dateLoad,
		'bank_id' => $dataArr['bank_id'],

	    );
	    $this->insert($data);
        }

            }
     
    
    public function doWalletBalanceAPI($params){
         
        $productId = isset($params['product_id']) ? $params['product_id'] : '';
        $txnCode = isset($params['txn_code']) ? $params['txn_code'] : '';
        $walletCode = isset($params['wallet_code']) ? $params['wallet_code'] : '';
        $ratCustomerId = $params['rat_customer_id'];
        $customerMasterId = $params['customer_master_id'];
        $object = new Corp_Ratnakar_Cardholders();
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        /*
         * getProductPurseBasicDetails :Getting all wallets with priority basis 
         */
        $param = array(
         'wallet_code' => $walletCode,
         'product_id'  => $productId
        );
        $masterPurseDetails = $masterPurseModel->getProductWalletPurseBasicDetails($param, 'priority');
        
        $custPurseDetail = array();
        if(!empty($masterPurseDetails)){
        $balanceAmount = 0;
       // $custPurseDetail['total_balance'] = 0;
           foreach($masterPurseDetails as $key=>$masterPurse)
            {
                    $walletCode = $masterPurse['code'];
                    $loadParams = array(
                     'customer_master_id' => $customerMasterId,
                     'purse_master_id' => $masterPurse['id'],
                     'wallet_code' => $walletCode,   
                     'product_id' => $productId,
                      );

                    $custPurse = $custPurseModel->getCustPurseDetails(array('rat_customer_id' => $ratCustomerId, 'purse_master_id' => $masterPurse['id']));
                   

                if(!empty($custPurse)){
                   //

                    if($masterPurse['allow_expiry'] == FLAG_YES){
                    $custPurseBal =  $this->chkAllowRatLoadCardDebitBalanceAPI($loadParams);
                    
                    if(!empty($custPurseBal)){
                      $custPurseDetail = array_merge($custPurseDetail,$custPurseBal);
                    
                     }else{
                        //Error
                     //  $custPurseDetail[$key]['wallet_code'] = $walletCode;   
                     //  $custPurseDetail[$key]['wallet_balance'] = 0;
                      }
                    }else{
                     $column = count($custPurseDetail);
                     $custPurseDetail[$column]['wallet_balance'] = $custPurse['amount'];
                     $custPurseDetail[$column]['wallet_code'] = $walletCode;
                     $custPurseDetail[$column]['date_expiry'] = '0000-00-00 00:00:00';
                     $custPurseDetail[$column]['voucher_num'] = '';
                    }
                }   
            }
        }elseif($walletCode!=''){
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_CODE);
        }
        
        return $custPurseDetail;
                  
    }
    
    public function chkAllowRatLoadCardDebitBalanceAPI($params){
        $currentDate = date('Y-m-d H:i:s');
        $custPurseInfo = array();
        $select = $this->select()
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('amount_available as wallet_balance','voucher_num','txn_no','date_expiry','wallet_code','narration as description','date_created'))
                ->where('product_id = ?', $params['product_id'])
                ->where('customer_master_id = ?', $params['customer_master_id'])
                ->where('purse_master_id = ?', $params['purse_master_id'])
                ->where('amount_available > 0')
                ->where("date_expiry >='".$currentDate."' OR date_expiry='0000-00-00 00:00:00'")
                ->where('status = ?', STATUS_LOADED);
       
        $select->order('id ASC');
        $rs = $this->_db->fetchAll($select);
        if(!empty($rs)){
            return $rs;
         }else{
             return FALSE;
         }
        
    }
    
    public function getCustLoadTransactions($params){
       $productID = isset($params['product_id'])?$params['product_id']:'';
       $txnCode = isset($params['txn_code'])?$params['txn_code']:'';
       $walletCode = isset($params['wallet_code'])?$params['wallet_code']:'';
       $ratCustMasterId = isset($params['customer_master_id'])?$params['customer_master_id']:'';
       $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_DEBIT_DETAIL .' as rtc', array("rtc.amount as amount","rtc.txn_code","rtc.txn_type","date_created as txn_date"))
                ->join(DbTable::TABLE_PURSE_MASTER .' as pm', "rtc.purse_master_id = pm.id",array("pm.code as wallet_code"))
                ->joinLeft(DbTable::TABLE_RAT_CORP_LOAD_REQUEST .' as rclr', "rtc.txn_code = rclr.txn_code", array("rclr.narration as load_description","rclr.status as load_status","rclr.txn_no as load_txn_no","rclr.is_reversal as is_reversal"));
        if($ratCustMasterId!=''){       
        $details->where("rtc.customer_master_id =?",$ratCustMasterId);
        }
        if($productID !=''){
        $details->where("rtc.product_id =?",$productID);
        }
        if($txnCode !=''){
         $details->where("rtc.txn_code =?",$txnCode);   
        }
        $details->where("rtc.txn_type IN ('". TXNTYPE_RAT_CORP_CORPORATE_LOAD."','".TXNTYPE_RAT_CORP_MEDIASSIST_LOAD."','".TXNTYPE_CARD_RELOAD."','".TXNTYPE_CARD_DEBIT."','".TXNTYPE_REMITTANCE."','".TXNTYPE_WALLET_TOWALLET_FUND_TRANSFER."')");  
        
        $details->order('rtc.id DESC');
        $details->limit(TXN_HISTORY_COUNT);  
        $res = $this->_db->fetchAll($details);
       // return $res;
       if(!empty($res)){
           return $res;
       }else{
           return FALSE;
       }
       
   }
   
   public function getallcustwalletLoadAmountAPI($params){
        $currentDate = date('Y-m-d H:i:s');
        $custPurseInfo = array();
        $select = $this->select()
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('sum(amount_available) as wallet_balance'))
                ->where('product_id = ?', $params['product_id'])
                ->where('customer_master_id = ?', $params['customer_master_id'])
                ->where('purse_master_id = ?', $params['purse_master_id'])
                ->where('amount_available > 0')
                ->where("date_expiry >='".$currentDate."' OR date_expiry='0000-00-00 00:00:00'")
                ->where('status = ?', STATUS_LOADED);
       
        $select->order('id ASC');
        $rs = $this->_db->fetchRow($select);
        if(!empty($rs)){
            return $rs;
         }else{
             return FALSE;
         }
        
    }
    
	public function mapLoadDebitTxn($arrTxn) {
        
        $debitedInfo = array();    
        $debitedInfo = array_merge($debitedInfo, $arrTxn);    
        $voucher_num = isset($arrTxn['voucher_num']) ? $arrTxn['voucher_num'] : '';
        $currentDate = date('Y-m-d H:i:s');
        $select = $this->select()
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('id', 'amount_available'))
                ->where('product_id = ?', $arrTxn['product_id'])
              //  ->where('customer_master_id = ?', $arrTxn['customer_master_id'])
                ->where('customer_purse_id = ?', $arrTxn['customer_purse_id'])
              //  ->where('purse_master_id = ?', $arrTxn['purse_master_id'])
                ->where('amount_available > 0')
                ->where("date_expiry >='".$currentDate."' OR date_expiry='0000-00-00 00:00:00'")
                ->where('status = ?', STATUS_LOADED);
        if($voucher_num !=''){
        $select->where('voucher_num = ?', $voucher_num);
        }        
        //$select->order('id ASC');
        $select->order('date_expiry ASC');
        $rs = $this->_db->fetchAll($select);
        if($rs){
            $detailArr = array(
                            'product_id' => $arrTxn['product_id'],
                            'bank_id' => $arrTxn['bank_id'],
                            'debit_id' => $arrTxn['debit_load_id'],
                            'debit_detail_id' => $arrTxn['debit_detail_id'],
                            'txn_code' => $arrTxn['txn_code'],
                            'txn_type' => TXNTYPE_CARD_DEBIT,
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
		
	public function insertRatDebitDetail($params = array()) {
        
        $param = array(
        'bank_id' => $params['bank_id'],
        'product_id' => $params['product_id'],
        'customer_master_id' => $params['customer_master_id'],
        'customer_purse_id' => $params['customer_purse_id'],
        'purse_master_id' => $params['purse_master_id'],
        'txn_code' => $params['txn_code'], 
        'txn_type' => TXNTYPE_CARD_DEBIT,
        'debit_id' => $params['debit_load_id'],
        'amount' => $params['amount'],     
        'txn_status' => STATUS_SUCCESS,
        'date_created' => new Zend_Db_Expr('NOW()')    
        );
        $this->_db->insert(DbTable::TABLE_RAT_DEBIT_DETAIL, $param);
        $debitedId = $this->_db->lastInsertId(DbTable::TABLE_RAT_DEBIT_DETAIL, 'id');
        return $debitedId;
        
    }
    

    public function getCustDebitLoadTransactions($params){
       $productID = isset($params['product_id'])?$params['product_id']:'';
       $txnCode = isset($params['txn_code'])?$params['txn_code']:'';
       $walletCode = isset($params['wallet_code'])?$params['wallet_code']:'';
       $ratCustMasterId = isset($params['customer_master_id'])?$params['customer_master_id']:'';
       $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_DEBIT_DETAIL .' as rtc', array("rtc.amount as amount","rtc.txn_code","rtc.txn_type","date_created as txn_date"))
                ->join(DbTable::TABLE_PURSE_MASTER .' as pm', "rtc.purse_master_id = pm.id",array("pm.code as wallet_code"))
                ->joinLeft(DbTable::TABLE_RAT_CORP_LOAD_REQUEST_DETAIL .' as rclrd', "rtc.id = rclrd.debit_detail_id",array("rclrd.amount as debit_amount","rclrd.debit_id"))
                ->joinLeft(DbTable::TABLE_RAT_CORP_LOAD_REQUEST .' as rclr', "rclrd.load_request_id = rclr.id", array("rclr.narration as description","rclr.date_expiry","rclr.date_expiry","rclr.voucher_num"));
        if($ratCustMasterId!=''){       
        $details->where("rtc.customer_master_id =?",$ratCustMasterId);
        }
        if($productID !=''){
        $details->where("rtc.product_id =?",$productID);
        }
        if($txnCode !=''){
         $details->where("rtc.txn_code =?",$txnCode);   
        }
        $details->where("rtc.txn_type IN ('". TXNTYPE_RAT_CORP_CORPORATE_LOAD."','".TXNTYPE_RAT_CORP_MEDIASSIST_LOAD."','".TXNTYPE_CARD_RELOAD."','".TXNTYPE_CARD_DEBIT."','".TXNTYPE_REMITTANCE."','".TXNTYPE_WALLET_TOWALLET_FUND_TRANSFER."')");  
        
        $details->order('rtc.id ASC');
        $details->limit(TXN_HISTORY_COUNT);  
        $res = $this->_db->fetchAll($details);
       // return $res;
       if(!empty($res)){
           return $res;
       }else{
           return FALSE;
       }
       
   }

    public function getreversalTransaction($params){
      
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('id','amount','amount_available','date_expiry','mode','status','txn_code','is_reversal','original_transaction_id','status_settlement','status_settlement','settlement_request_id','settlement_ref_no'))
                ->where('product_id = ?', $params['product_id'])
                ->where('cardholder_id = ?', $params['cardholder_id'])
                ->where('txn_no = ?', $params['txn_no']);
        $select->order('id ASC');

        $rs = $this->_db->fetchRow($select);
        if(!empty($rs)){
            return $rs;
         }else{
             return FALSE;
         }
        
    }
    
    public function getReversaledAmountById($params){
        $Id = isset($params['original_transaction_id'])?$params['original_transaction_id']:'';
        $mode = isset($params['mode'])?$params['mode']:'';
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('sum(amount) as total_amount','sum(amount_available) as total_amount_available'));
            if($Id !=''){        
                $select->where('original_transaction_id = ?', $Id);
            }
//            if( ($mode !='') && ($mode == TXN_MODE_DR) ){
//                $select->where('status = ?', STATUS_DEBITED);  
//            }else{
//                $select->where('status = ?', STATUS_LOADED);  
//            }
            $select->where('status = ?', STATUS_LOADED);
           // $select->where('is_reversal = ?', REVERSAL_FLAG_YES);
          
        $rs = $this->_db->fetchRow($select);
        if(!empty($rs) && ( ($rs['total_amount'] !='') || ($rs['total_amount_available'] !='')) ){
            return $rs;
         }else{
             return FALSE;
         }
        
        
    }


    /*
     * getLoadExpiryRequests will return the load requests for cutoff cron
     */

    public function getLoadExpiryRequests($param) {        
        $status = isset($param['status']) ? $param['status'] : '';
        $chkAvailable = (isset($param['chk_amount_available']) && $param['chk_amount_available'] != '') ? $param['chk_amount_available'] : '';
        
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($this->_name ." as l",array('l.id', 'l.bank_id', 'l.product_id', 'l.customer_master_id', 'l.cardholder_id', 'l.purse_master_id', 'l.customer_purse_id', 'l.card_number', 'l.amount_available', 'l.txn_code', 'l.by_agent_id', 'l.date_load', 'l.txn_load_id', new Zend_Db_Expr("CASE l.date_expiry WHEN '0000-00-00 00:00:00' THEN '' ELSE date_expiry END as date_expiry")));
        $select->join(DbTable::TABLE_PURSE_MASTER . " as pm", "l.purse_master_id  = pm.id AND (pm.allow_expiry='".FLAG_YES."' OR pm.load_validity_day > 0 OR pm.load_validity_hr > 0 OR pm.load_validity_min > 0)", array('pm.code'));

        if ($chkAvailable == GREATER){
            $select->where("l.amount_available > 0");
        }

        if($status != ''){
            $select->where('l.status = ?', $status);
        }

        $select->where("l.by_agent_id <> 0");

        return $this->fetchAll($select);
    }
    
    

public function doReversalCardloadAPI($dataArr) {
        
        /*
         * Reversal cardload
         */
        
        if (empty($dataArr)) {
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_CARDLOAD_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_CARDLOAD_CODE);
        }
        $custModel = new Corp_Ratnakar_Cardholders();
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $baseTxn = new BaseTxn();
        $object = new Corp_Ratnakar_Cardholders();
        $productModel = new Products();
        $str = '';
        $priorityWalletCode = '';
        $loadStatus = STATUS_FAILED;
        $product = App_DI_Definition_BankProduct::getInstance($dataArr['bank_product_const']);
        $genWalletCode = $product->purse->code->genwallet;
        $productId = $dataArr['product_id'];
        $failedReason = '';
        $dateFailed = ''; 
        try {
            
            // Getting Customer detail   
         
            $cardholderId = $dataArr['cardholder_id'];

            $searchArr = array('cardholder_id' => $cardholderId, 'status' => STATUS_ACTIVE);
            $cardholderDetails = $custModel->getCardholderInfo($searchArr);
            if(!isset($cardholderDetails->id) || $cardholderDetails->id == '')
            {
                $loadStatus = STATUS_FAILED;
                $failedReason = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                $dateFailed = new Zend_Db_Expr('NOW()');   
                $dateLoad = new Zend_Db_Expr('NOW()');   
                $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
            } else {
                $loadStatus = STATUS_PENDING;
                $failedReason = '';
                $dateFailed = '';   
                $dateLoad = '';
                $failedCode = '';
            }

	    $cardNumber = $cardholderDetails->card_number;
            $mediAssistId = $cardholderDetails->medi_assist_id;
            $customerMasterId = ($cardholderDetails->customer_master_id != '') ? $cardholderDetails->customer_master_id : 0;
            $ratCustomerId = ($cardholderDetails->rat_customer_id != '') ? $cardholderDetails->rat_customer_id : 0;
            $customerPurseId = 0;
            
            /*
             * Reversal Checks
             */
            $revOrigTxn_loadId = 0;
            $isSettledReversal = REVERSAL_FLAG_NO;
            $isReversal = 'n';
            if( ($dataArr['Filler4'] !='') && ($dataArr['Filler5'] !='') ){
                $isReversal = strtolower($dataArr['Filler4']);
                $reversalParams = array(
                    'product_id'=> $dataArr['product_id'],
                    'cardholder_id'=> $dataArr['cardholder_id'],
                    'txn_no'=> $dataArr['Filler5'],
                    'amount'=> $dataArr['amount']
                    
                );
               
                $reversalOriginaltxn_Info = $this->getreversalTransaction($reversalParams);
                // Convert Requested Amount in Rs
                $dataArr['amount'] = Util::convertToRupee($dataArr['amount']);
                $amount = $dataArr['amount'];
                if(!empty($reversalOriginaltxn_Info)){
                    
                    // Current Date and Time
                    $currentDate = strtotime(date('Y-m-d H:i:s'));
                    $requestMode = strtolower($dataArr['mode']);
                    
                    $RevAmount = $dataArr['amount'];
                    
                    $revOrigTxn_loadId = $reversalOriginaltxn_Info['id'];
                    $revOrigTxn_status = $reversalOriginaltxn_Info['status'];
                    $revOrigTxn_mode = $reversalOriginaltxn_Info['mode'];
                    $revOrigTxn_dateExpiry = $reversalOriginaltxn_Info['date_expiry'];
                    $rev_status_settlement = $reversalOriginaltxn_Info['status_settlement'];
                    $rev_settlement_request_id = $reversalOriginaltxn_Info['settlement_request_id'];
                    
                    $rev_settlement_ref_no = $reversalOriginaltxn_Info['settlement_ref_no'];
                    $revOrigTxn_amount = $reversalOriginaltxn_Info['amount'];
                    $revOrigTxn_flag = $reversalOriginaltxn_Info['is_reversal'];
                    $revOrigTxn_Id = $reversalOriginaltxn_Info['original_transaction_id'];
                    $revOrigTxn_settlement_status = $reversalOriginaltxn_Info['status_settlement'];
                    
                    // Status Checking
                    
                    if(($revOrigTxn_flag == REVERSAL_FLAG_YES) ){
                        $loadStatus = STATUS_FAILED;
                        $failedReason = ErrorCodes::ERROR_EDIGITAL_TXN_ALREADY_REVERSED_MSG;
                        $failedCode = ErrorCodes::ERROR_EDIGITAL_TXN_ALREADY_REVERSED_CODE;
                        $dateFailed = new Zend_Db_Expr('NOW()');  
                        $dateLoad = new Zend_Db_Expr('NOW()');
                        $amount = $dataArr['amount'];  
                    }elseif(($revOrigTxn_status != STATUS_DEBITED) ){
                        $loadStatus = STATUS_FAILED;
                        $failedReason = ErrorCodes::ERROR_EDIGITAL_INVALID_ORIGINAL_TXN_NUMBER_MSG;
                        $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_ORIGINAL_TXN_NUMBER_CODE;
                        $dateFailed = new Zend_Db_Expr('NOW()');  
                        $dateLoad = new Zend_Db_Expr('NOW()');
                        $amount = $dataArr['amount'];  
                    }elseif( (strtolower($dataArr['mode']) != TXN_MODE_CR)  ){
                        $loadStatus = STATUS_FAILED;
                        $failedReason = ErrorCodes::ERROR_EDIGITAL_INVALID_REVERSAL_MODE_MSG;
                        $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_REVERSAL_MODE_CODE;
                        $dateFailed = new Zend_Db_Expr('NOW()');  
                        $dateLoad = new Zend_Db_Expr('NOW()');
                        $amount = $dataArr['amount'];  
                    }else if(( (int)$revOrigTxn_dateExpiry) && ($currentDate > strtotime($revOrigTxn_dateExpiry)) ){
                       // Expiry Validation
                        $loadStatus = STATUS_FAILED;
                        $failedReason = ErrorCodes::ERROR_EDIGITAL_TXN_EXPIRED_MSG;
                        $failedCode = ErrorCodes::ERROR_EDIGITAL_TXN_EXPIRED_CODE;
                        $dateFailed = new Zend_Db_Expr('NOW()');  
                        $dateLoad = new Zend_Db_Expr('NOW()');
                        $amount = $dataArr['amount'];  
                    }elseif( ($RevAmount > $revOrigTxn_amount) || ($RevAmount < $revOrigTxn_amount) ){
                    
                        //Amount validation
                        $loadStatus = STATUS_FAILED;
                        $failedReason = ErrorCodes::ERROR_EDIGITAL_INVALID_REVERSAL_AMOUNT_MSG;
                        $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_REVERSAL_AMOUNT_CODE;
                        $dateFailed = new Zend_Db_Expr('NOW()');  
                        $dateLoad = new Zend_Db_Expr('NOW()');
                        $amount = $dataArr['amount']; 

                    }else{
                        // Sum of Amounts of all reversals 
                        $reverseParams = array(
                         'original_transaction_id' => $revOrigTxn_loadId,
                          'mode' => $requestMode,
                          'product_id' =>  $dataArr['product_id'] 
                            
                        );
                        
                        if($rev_settlement_request_id > 0 ){
                            $isSettledReversal = REVERSAL_FLAG_YES;   
                        }
                        
                    }
                   
                }else{
                    $loadStatus = STATUS_FAILED;
                    $failedReason = ErrorCodes::ERROR_EDIGITAL_INVALID_REVERSAL_TXN_NUMBER_MSG;
                    $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_REVERSAL_TXN_NUMBER_CODE;
                    $dateFailed = new Zend_Db_Expr('NOW()');  
                    $dateLoad = new Zend_Db_Expr('NOW()');
                    $amount = $dataArr['amount'];  
                }
                
                //
                $loadMode = strtolower($dataArr['mode']);
                if (strtolower($dataArr['mode']) == TXN_MODE_DR) {
                    $txnType = '';
                    $loadStatus = STATUS_FAILED;
                    $failedReason = 'Invalid Mode: '.$dataArr['mode'];
                    $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_REVERSAL_MODE_CODE;
                    $dateFailed = new Zend_Db_Expr('NOW()');  
                    $dateLoad = new Zend_Db_Expr('NOW()');
                    $amount = $dataArr['amount'];
                }elseif (strtolower($dataArr['mode']) == TXN_MODE_CR) {
                    $txnType = TXNTYPE_CARD_RELOAD;
                }else {
                    $txnType = '';
                    $loadStatus = STATUS_FAILED;
                    $failedReason = 'Invalid Mode: '.$dataArr['mode'];
                    $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_REVERSAL_MODE_CODE;
                    $dateFailed = new Zend_Db_Expr('NOW()');  
                    $dateLoad = new Zend_Db_Expr('NOW()');
                    $amount = $dataArr['amount'];

                } 
                
                // SET Request records
                $purseMasterId = 0;   
                $pursecode = ($dataArr['wallet_code'] != '')?$dataArr['wallet_code']: '';    
                if($pursecode !=''){
                $masterPurseDetails = $masterPurseModel->getPurseIdByPurseCodeAPI($pursecode,$productId);

                $purseMasterId = $masterPurseDetails['id'];
                }
                
                $txnCode = $dataArr['txn_code'];
                    
                
                $data = array(
                'customer_master_id' => $customerMasterId,
                'cardholder_id' => $cardholderId,
                'customer_purse_id' => $customerPurseId,
                'txn_type' => $txnType,
                'load_channel' => BY_API,
                'purse_master_id' => $purseMasterId,
                'txn_identifier_type' => $dataArr['txn_identifier_type'],
                'txn_identifier_num' => $dataArr['txn_identifier_num'],
                'card_number' => Util::insertCardCrn($cardNumber),
                'medi_assist_id' => $mediAssistId,
                'amount' => $amount,
                'amount_available' => 0,
                'amount_used' => 0,
                'amount_cutoff' => 0,
                'currency' => CURRENCY_INR_CODE,
                'narration' => $dataArr['narration'],
                'wallet_code' => strtoupper($pursecode),
                'txn_no' => $dataArr['txn_no'],
                'card_type' => $dataArr['card_type'],
                'corporate_id' => $dataArr['corporate_id'],
                'mode' => $dataArr['mode'],
                'txn_code' => $txnCode,
                'ip' => $this->formatIpAddress(Util::getIP()),
                'by_ops_id' => 0,
                'batch_name' => '',
                'bank_id' => $dataArr['bank_id'],
                'product_id' => $dataArr['product_id'],
                'status' => $loadStatus,
                'date_created' => new Zend_Db_Expr('NOW()'),
                'failed_reason' => $failedReason,
                'date_failed' => $dateFailed,
                'date_load' => $dateLoad,
                'date_expiry' => $dataArr['date_expiry'],
                'voucher_num' => $dataArr['voucher_num'],
                'is_reversal' => $isReversal,
                'original_transaction_id' => $revOrigTxn_loadId,
                'channel' => $dataArr['channel']    

            );
            if($dataArr['manageType'] == CORPORATE_MANAGE_TYPE){
                $data['by_corporate_id'] = $dataArr['by_api_user_id'];
                
            }
            else{
                $data['by_agent_id'] = $dataArr['by_api_user_id'];
                $data['by_corporate_id'] = 0;
            }
                

                //If load is failed
                if($loadStatus == STATUS_FAILED){
                    // SET Request records
                        $purseMasterId = 0;   
                        $pursecode = ($dataArr['wallet_code'] != '')?$dataArr['wallet_code']: '';    
                        if($pursecode !=''){
                        $masterPurseDetails = $masterPurseModel->getPurseIdByPurseCodeAPI($pursecode,$productId);

                        $purseMasterId = $masterPurseDetails['id'];
                        }

                        $txnCode = $dataArr['txn_code'];

                        $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                        $cardNumberEnc = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')");

                        $data = array(
                        'customer_master_id' => $customerMasterId,
                        'cardholder_id' => $cardholderId,
                        'customer_purse_id' => $customerPurseId,
                        'txn_type' => $txnType,
                        'load_channel' => BY_API,
                        'purse_master_id' => $purseMasterId,
                        'txn_identifier_type' => $dataArr['txn_identifier_type'],
                        'txn_identifier_num' => $dataArr['txn_identifier_num'],
                        'card_number' => $cardNumberEnc,
                        'medi_assist_id' => $mediAssistId,
                        'amount' => $amount,
                        'amount_available' => 0,
                        'amount_used' => 0,
                        'amount_cutoff' => 0,
                        'currency' => CURRENCY_INR_CODE,
                        'narration' => $dataArr['narration'],
                        'wallet_code' => strtoupper($pursecode),
                        'txn_no' => $dataArr['txn_no'],
                        'card_type' => $dataArr['card_type'],
                        'corporate_id' => $dataArr['corporate_id'],
                        'mode' => $dataArr['mode'],
                        'txn_code' => $txnCode,
                        'ip' => $this->formatIpAddress(Util::getIP()),
                        'by_ops_id' => 0,
                        'batch_name' => '',
                        'bank_id' => $dataArr['bank_id'],
                        'product_id' => $dataArr['product_id'],
                        'status' => $loadStatus,
                        'date_created' => new Zend_Db_Expr('NOW()'),
                        'failed_reason' => $failedReason,
                        'date_failed' => $dateFailed,
                        'date_load' => $dateLoad,
                        'date_expiry' => $dataArr['date_expiry'],
                        'voucher_num' => $dataArr['voucher_num'],
                        'is_reversal' => $isReversal,
                        'original_transaction_id' => $revOrigTxn_loadId,
                        'channel' => $dataArr['channel']
                    );
                    if($dataArr['manageType'] == CORPORATE_MANAGE_TYPE){
                        $data['by_corporate_id'] = $dataArr['by_api_user_id'];

                    }
                    else{
                        $data['by_agent_id'] = $dataArr['by_api_user_id'];
                        $data['by_corporate_id'] = 0;
                    }
                    $this->insert($data);
                    $loadRequestId = $this->_db->lastInsertId(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, 'id');
                    throw new Exception($failedReason,$failedCode);
                }elseif( ($loadStatus !=STATUS_FAILED) && (strtolower($dataArr['mode'])) == TXN_MODE_CR){
                        
                        // getting customer purse detail with relevant amount
                        $custRevParams = array(
                          'product_id' => $dataArr['product_id'] ,
                          'original_transaction_id' => $revOrigTxn_loadId,  
                        );
                       
                        // Respose of amount with relevant wallet,TxnNo and Transaction Ref No
                        $flgValidate = true;
                        $responseArr = array(); 
                        $revECSAmtReq = array();
                        $customerPurseDetail = $this->getReversaledCustomerPurseInfo($custRevParams); 
                        
                     
                      //exit;
                      if(!empty($customerPurseDetail)){
                        
                          
                          
                          if($isSettledReversal == REVERSAL_FLAG_YES){
                          /*
                           *  Validation for Agent limit
                           */
                            $validator = array(
                                        'load_request_id' => $revOrigTxn_loadId,
                                       
                                        'amount' => $RevAmount,
                                        'agent_id' => $dataArr['by_api_user_id'],
                                        'product_id' => $productId,
                                        'bank_id' => $dataArr['bank_id'],
                                        'manageType' => $dataArr['manageType'],
                                        'isReversal' => REVERSAL_FLAG_YES,
                                        'revLoadId' => $revOrigTxn_loadId,
                                        'is_settled_reversal' =>  $isSettledReversal  
                                    );
                                      
                            $flgValidate = $baseTxn->chkAllowRatLimitCardLoad($validator); 
                          }
                          if($flgValidate){
                          
                          $this->_db->beginTransaction(); 
                          
                          //ECS call Request
                          foreach($customerPurseDetail as $key=>$custLoaded){
                          if(strtoupper($custLoaded['wallet_code']) == strtoupper($genWalletCode)){
                              $revECSAmtReq['amount'] += $custLoaded['amount'];
                              
                            }
                          }
                          
                          // ECS Call
                          
                          $txn_load_id = 0;
                          $iso_txn_load_id = 0;
                          if(!empty($revECSAmtReq) && $revECSAmtReq['amount'] !='' && $cardNumber != '' && $dataArr['by_api_user_id'] !='')
                          {
                              
                              
                            /*
                            * ECS call:
                            */
                            $ecsApi = new App_Socket_ECS_Corp_Transaction();
                            

                            $cardLoadData = array(
                            'amount' => $revECSAmtReq['amount'],
                            'crn' => $cardNumber,
                            'agentId' => $dataArr['by_api_user_id'],
                            'transactionId' => $txnCode,
                            'currencyCode' => CURRENCY_INR_CODE,
                            'countryCode' => COUNTRY_IN_CODE
                            );
                            if(DEBUG_MVC) {
                            $apiResp = TRUE;
                            } else {
                            $apiResp = $ecsApi->cardLoad($cardLoadData); // bypassing for testing
                            }
                            if ($apiResp === TRUE) {
                            $iso_txn_load_id = $ecsApi->getISOTxnId();
                            }else {
                            $failedReason = $ecsApi->getError();
                          //  $loadStatus = STATUS_FAILED;
                            $date_failed = new Zend_Db_Expr('NOW()');
                            $data['status'] = STATUS_FAILED;
                            $data['date_failed'] = $date_failed;
                            $data['failed_reason'] = $failedReason;
                            $data['date_load'] = $date_failed;
                            
                            $this->_db->rollBack();
                            
                            $this->insert($data);
                           
                           
                            throw new Exception('Transaction failed due to some technical problem, please try again later.');

                            } 
                              
                          }
                          
                          //Inserting new CR load
                        foreach($customerPurseDetail as $key=>$custLoadInfo){
                           if($custLoadInfo['debit_amount']!=''){
                                 $loadAmount = $custLoadInfo['debit_amount'];
                            }else{
                                 $loadAmount = $custLoadInfo['amount'];    
                            }
                            if($custLoadInfo['allow_expiry'] == FLAG_YES){
                             if($dataArr['date_expiry'] != ''){
                                $date_expiry = $dataArr['date_expiry'];    
                             }else{
                                $date_expiry = $custLoadInfo['date_expiry'];
                             }   
                            $voucher_num = $custLoadInfo['voucher_num'];
                            $cardType = $custLoadInfo['card_type'];
                            }else{
                                 $date_expiry = Util::getdefaultExpiryDate();
                                 $voucher_num = ''; 
                                 $cardType = CORP_CARD_TYPE_NORMAL;
                            }
                           
                            $txnNo = $baseTxn->generateTxncode();
                            $txnCode = $baseTxn->generateTxncode();
                            $txnNumber = $dataArr['txn_no'] + $key;
                            $loadDetails = $this->getLoadDetails(array('txn_no' => $txnNumber,'product_id'=>$productId));
                            if (!empty($loadDetails) ) {
                             $txnNo = $baseTxn->generateTxncode();   
                            }else{
                            $txnNo = $txnNumber;
                            }
                            if( ($loadStatus == STATUS_PENDING ) && ($loadMode == TXN_MODE_CR )){
                                
                            
                            $dataLoad = array(
                                'customer_master_id' => $custLoadInfo['customer_master_id'],
                                'cardholder_id' => $cardholderId,
                                'customer_purse_id' => $custLoadInfo['customer_purse_id'],
                                'txn_type' => $txnType,
                                'load_channel' => BY_API,
                                'purse_master_id' => $custLoadInfo['purse_master_id'],
                                'txn_identifier_type' => $dataArr['txn_identifier_type'],
                                'txn_identifier_num' => $dataArr['txn_identifier_num'],
                                'card_number' => Util::insertCardCrn($custLoadInfo['card_number']),
                                'medi_assist_id' => $custLoadInfo['medi_assist_id'],
                                'amount' => $loadAmount,
                                'amount_available' => $loadAmount,
                                'amount_used' => 0,
                                'amount_cutoff' => 0,
                                'currency' => CURRENCY_INR_CODE,
                                'narration' => $custLoadInfo['narration'],
                                'wallet_code' => strtoupper($custLoadInfo['wallet_code']),
                                'txn_no' => $txnNo,
                                'card_type' => $cardType,
                                'corporate_id' => $custLoadInfo['corporate_id'],
                                'mode' => TXN_MODE_CR,
                                'txn_code' => $txnCode,
                                'ip' => $this->formatIpAddress(Util::getIP()),
                                'by_ops_id' => 0,
                                'batch_name' => '',
                                'bank_id' => $custLoadInfo['bank_id'],
                                'product_id' => $custLoadInfo['product_id'],
                                'status' => $loadStatus,
                                'date_created' => new Zend_Db_Expr('NOW()'),
                                'date_load' => $dateLoad,
                                'date_expiry' => $date_expiry,
                                'voucher_num' => $voucher_num,
                                'by_agent_id' => $custLoadInfo['by_agent_id'],
                                'by_corporate_id' => $custLoadInfo['by_corporate_id'],
                                'original_transaction_id' => $revOrigTxn_loadId,
                                'channel' => $dataArr['channel']
                            ); 
                            if($dataArr['manageType'] == CORPORATE_MANAGE_TYPE){
                            $dataLoad['by_corporate_id'] = $dataArr['by_api_user_id'];

                            }
                            else{
                                $dataLoad['by_agent_id'] = $dataArr['by_api_user_id'];
                                $dataLoad['by_corporate_id'] = 0;
                            }
                            
                            if($isSettledReversal == REVERSAL_FLAG_YES){
                             $dataLoad['reversal_by'] = $dataArr['by_api_user_id'];   
                            }
                            
                            if(strtoupper($custLoaded['wallet_code']) == strtoupper($genWalletCode)){
                                
                                $dataLoad['txn_load_id'] = $iso_txn_load_id;
                            }
                            //Partial insert record 
                            
                               $this->insert($dataLoad);
                               $loadRequestId = $this->_db->lastInsertId(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, 'id');


                                    $updateArr = array(
                                        'amount_available' => $loadAmount,
                                        'amount_used' => 0,
                                        'amount_cutoff' => 0,
                                        'txn_load_id' => '',
                                        'status' => STATUS_LOADED,
                                        'date_load' => new Zend_Db_Expr('NOW()')
                                    );

                                    $this->updateLoadRequests($updateArr, $loadRequestId);

                                    $baseTxnParams = array(
                                        'txn_code' => $txnCode,
                                        'customer_master_id' => $dataLoad['customer_master_id'],
                                        'product_id' => $dataLoad['product_id'],
                                        'bank_id' => $dataLoad['bank_id'],
                                        'purse_master_id' => $dataLoad['purse_master_id'],
                                        'customer_purse_id' => $dataLoad['customer_purse_id'],
                                        'amount' => $loadAmount,
                                        'agent_id' => $dataArr['by_api_user_id'],
                                        'txn_type' => TXNTYPE_CARD_RELOAD,
                                        'ip' => $this->formatIpAddress(Util::getIP()),
                                        'manageType' => $dataArr['manageType'],
                                        'is_reversal' => REVERSAL_FLAG_YES,
                                        'is_settled_reversal' =>  $isSettledReversal
                                    );
                                    $baseTxn->successRatCardLoad($baseTxnParams);
                                   
                                    $responseArr[$key]['txn_no'] = $txnNo;
                                    $responseArr[$key]['txn_code'] = $txnCode;
                                    $responseArr[$key]['wallet_code'] = $dataLoad['wallet_code'];
                                    $responseArr[$key]['amount'] = $loadAmount;
                                
                              
                                    
                                 }
                              }
                              
                              /*
                               * Update Debited load request
                               */
                                $updateDebitArr = array(
                                           'is_reversal' => REVERSAL_FLAG_YES,
                                           'date_reversal' => new Zend_Db_Expr('NOW()')
                                          );
                              
                              $this->updateLoadRequests($updateDebitArr, $revOrigTxn_loadId);
                              $custPurse = $custPurseModel->getCustBalance($cardholderDetails->rat_customer_id);

                                    //$cardholderArray['cardNumber'] = $cardholderDetails->card_number;

                                   // Get balance
                                      $balVal = $custPurse['sum'];  


                                if (strtolower($dataArr['sms_flag']) == strtolower(FLAG_Y)) {
                                    // Send SMS
                                    $userData = array(
                                     'last_four' =>substr($cardholderDetails->card_number, -4),
                                     'product_id' => $productId,
                                     'mobile' => $cardholderDetails->mobile,
                                    // 'amount' => $amount,
                                     'amount' => Util::convertToRupee($dataArr['amount']),
                                     'balance' => $balVal,
                                    );
                                    
                                   $object->generateSMSDetails($userData, $smsType = TRANSACTION_REQUEST_CR); 
                                     }
                              
                          }
                         
                         $this->_db->commit();
                       
                       return $responseArr;
                      }
                    }else{
                        return false;
                    }
            }else{
                        return false;
                 }
            
        }  catch (App_Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);            
            $this->setError($e->getMessage());
            $code = $e->getCode();
            $code = (!empty($code)) ? $code : ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
            $this->_db->rollBack();
            
            throw new Exception($e->getMessage(), $code);

        }
        
    }

//    public function mapLoadDebitTxn($arrTxn) {
//        
//        $debitedInfo = array();    
//        $debitedInfo = array_merge($debitedInfo, $arrTxn);    
//        $voucher_num = isset($params['voucher_num']) ? $params['voucher_num'] : '';
//        $currentDate = date('Y-m-d H:i:s');
//        $select = $this->select()
//                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('id', 'amount_available'))
//                ->where('product_id = ?', $arrTxn['product_id'])
//                ->where('customer_master_id = ?', $arrTxn['customer_master_id'])
//                ->where('customer_purse_id = ?', $arrTxn['customer_purse_id'])
//                ->where('purse_master_id = ?', $arrTxn['purse_master_id'])
//                ->where('amount_available > 0')
//                ->where("date_expiry >='".$currentDate."' OR date_expiry='0000-00-00 00:00:00'")
//                ->where('status = ?', STATUS_LOADED);
//        if($voucher_num !=''){
//        $select->where('voucher_num = ?', $voucher_num);
//        }        
//        $select->order('id ASC');
//        $rs = $this->_db->fetchAll($select);
//        if($rs){
//            $detailArr = array(
//                            'product_id' => $arrTxn['product_id'],
//                            'bank_id' => $arrTxn['bank_id'],
//                            'debit_id' => $arrTxn['debit_load_id'],
//                            'debit_detail_id' => $arrTxn['debit_detail_id'],
//                            'txn_code' => $arrTxn['txn_code'],
//                            'txn_type' => TXNTYPE_CARD_DEBIT,
//                            'amount' => 0,
//                            'load_request_id' => 0
//                        );
//            $txnAmount = $arrTxn['amount'];
//            foreach($rs as $row){
//                if($txnAmount > 0) {
//                    if($row['amount_available'] >= $txnAmount) {
//                        $detailArr['amount'] = $txnAmount;
//                        $detailArr['load_request_id'] = $row['id'];
//                        $this->insertLoadDetail($detailArr);   
//
//                        $updLoadArr = array(
//                            'amount_available' => new Zend_Db_Expr("amount_available - " . $detailArr['amount']),
//                            'amount_used' => new Zend_Db_Expr("amount_used + " . $detailArr['amount'])
//                                );
//                        $this->updateLoadRequests($updLoadArr, $row['id']);
//                    } 
//                    else // txnamt > available
//                    {
//
//                        $detailArr['amount'] = $row['amount_available'];
//                        $detailArr['load_request_id'] = $row['id'];
//                        
//                        $this->insertLoadDetail($detailArr);   
//
//                        $updLoadArr = array(
//                            'amount_available' => new Zend_Db_Expr("amount_available - " . $detailArr['amount']),
//                            'amount_used' => new Zend_Db_Expr("amount_used + " . $detailArr['amount'])
//                                );
//                        $this->updateLoadRequests($updLoadArr, $row['id']);
//
//
//                    }
//                    $txnAmount -= $detailArr['amount'];
//                }
//                
//            }
//            
//        }
//    }
    
//    public function insertRatDebitDetail($params = array()) {
//        
//        $param = array(
//        'bank_id' => $params['bank_id'],
//        'product_id' => $params['product_id'],
//        'customer_master_id' => $params['customer_master_id'],
//        'customer_purse_id' => $params['customer_purse_id'],
//        'purse_master_id' => $params['purse_master_id'],
//        'txn_code' => $params['txn_code'], 
//        'txn_type' => TXNTYPE_CARD_DEBIT,
//        'debit_id' => $params['debit_load_id'],
//        'amount' => $params['amount'],     
//        'txn_status' => STATUS_SUCCESS,
//        'date_created' => new Zend_Db_Expr('NOW()')    
//        );
//        $this->_db->insert(DbTable::TABLE_RAT_DEBIT_DETAIL, $param);
//        $debitedId = $this->_db->lastInsertId(DbTable::TABLE_RAT_DEBIT_DETAIL, 'id');
//        return $debitedId;
//        
//    }
    
//    public function chkAllowRatLoadCardDebitAPI($params){
//        $voucher_num = isset($params['voucher_num']) ? $params['voucher_num'] : '';
//        $currentDate = date('Y-m-d H:i:s');
//        $custPurseInfo = array();
//        $select = $this->select()
//                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('sum(amount_available) as total_amount'))
//                ->where('product_id = ?', $params['product_id'])
//                ->where('customer_purse_id = ?', $params['customer_purse_id'])
//                ->where('amount_available > 0')
//                ->where("date_expiry >='".$currentDate."' OR date_expiry='0000-00-00 00:00:00'")
//                ->where('status = ?', STATUS_LOADED);
//        if($voucher_num !=''){
//        $select->where('voucher_num = ?', $voucher_num);
//        }
//        $select->order('id ASC');
//        $rs = $this->_db->fetchRow($select);
//        if(!empty($rs) && ($rs['total_amount'] !='') ){
//            $custPurseInfo = array_merge($custPurseInfo, $params);
//            if($rs['total_amount'] !=''){
//                if($params['request_amount'] > $rs['total_amount']){   
//                $custPurseInfo['accept_amount'] = $rs['total_amount'];
//                $custPurseInfo['balance_amount'] = $params['request_amount'] - $rs['total_amount']; 
//                }else{
//                $custPurseInfo['accept_amount'] = $params['request_amount'];
//                $custPurseInfo['balance_amount'] = 0; 
//                }
//            }else{
//                $custPurseInfo['accept_amount'] = 0;
//                $custPurseInfo['balance_amount'] = $params['request_amount'];  
//            }
//         }
//        
//        return $custPurseInfo;
//    }
    
    public function chkAllowSingleRatLoadCardDebitAPI($params){
        $voucher_num = isset($params['voucher_num']) ? $params['voucher_num'] : '';
        $currentDate = date('Y-m-d H:i:s');
        $custPurseInfo = array();
        $select = $this->select()
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('sum(amount_available) as total_amount'))
                ->where('product_id = ?', $params['product_id'])
                ->where('customer_purse_id = ?', $params['customer_purse_id'])
                ->where('amount_available > 0')
                ->where("date_expiry >='".$currentDate."' OR date_expiry='0000-00-00 00:00:00'")
                ->where('status = ?', STATUS_LOADED);
        if($voucher_num !=''){
        $select->where('voucher_num = ?', $voucher_num);
        }
        $select->order('id ASC');
        $rs = $this->_db->fetchRow($select);
        if(!empty($rs) && ($rs['total_amount'] !='') ){
            $custPurseInfo = array_merge($custPurseInfo, $params);
            if($rs['total_amount'] !=''){
                $custPurseInfo['accept_amount'] = $rs['total_amount'];
            }else{
                $custPurseInfo['accept_amount'] = 0;
            }
         }
        
        return $custPurseInfo;
    }
    
      public function getAgentTotalVirtualLoad($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $txnType = isset($param['txn_type']) ? $param['txn_type'] : '';
        $onDate = isset($param['on_date']) ? $param['on_date'] : FALSE;
        $status = isset($param['status']) ? $param['status'] : '';
        $cutoff = isset($param['cutoff']) ? $param['cutoff'] : FALSE;
                
        if ($agentId > 0) {
            $load_field = ($cutoff)?'amount_cutoff':'amount' ;
            $date_field = ($cutoff)?'date_cutoff':'date_created' ;
                
            $select = $this->select();
            $select->from(
                    $this->_name. ' as clReq', 
                    array("sum(" . $load_field . ") as total_agent_load_amount"
            )); 
            $select->join(
                    DbTable::TABLE_PURSE_MASTER.' as pm',
                    "pm.id = clReq.purse_master_id",array());
            
            if($agentId != ''){
                $select->where('clReq.by_agent_id = ?', $agentId);
            }
            
            if($status == '') {
                $status = array(STATUS_LOADED); 
            }
            $select->where('clReq.status IN (?)', $status);
            
            if($txnType == '') {
                $txnType = array(TXNTYPE_RAT_CORP_CORPORATE_LOAD,TXNTYPE_RAT_CORP_MEDIASSIST_LOAD,TXNTYPE_CARD_RELOAD); 
            }
            $select->where('clReq.txn_type IN (?)', $txnType);
            $select->where('pm.is_virtual = ?', FLAG_YES);
            if ($onDate) {
                $date = isset($param['date']) ? $param['date'] : '';
                $select->where('DATE(clReq.'.$date_field.') =?', $date);
            } else {
                $fromDate = isset($param['from']) ? $param['from'] : '';
                $toDate = isset($param['to']) ? $param['to'] : ''; 
                $select->where("DATE(clReq.".$date_field.") BETWEEN '" . $fromDate . "' AND '" . $toDate . "'"); 
            }
            return $this->fetchRow($select);
        }
        else
            return 0;
    }
    
    public function getAgentVirtualDebits($param) { 
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
        $onDate = isset($param['on_date']) ? $param['on_date'] : FALSE; 
        $debitAPICr = (isset($param['debit_api_cr'])) ? $param['debit_api_cr'] : POOL_AC ; 
        if($agentId != 0) {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(DbTable::TABLE_RAT_DEBIT_DETAIL ." as rdd", array('sum(rdd.amount) AS total_agent_debit_amount'));
            $select->joinLeft(
                    DbTable::TABLE_PURSE_MASTER.' as pm',
                    "pm.id = rdd.purse_master_id",array()); 
            $select->joinLeft(
                    DbTable::TABLE_RAT_CORP_LOAD_REQUEST.' as clreq',
                    "clreq.id = rdd.debit_id",array()); 
            $select->where('pm.is_virtual = ?', FLAG_YES);
            $select->where('pm.debit_api_cr = ?', $debitAPICr);            
            if($agentId != ''){
                $select->where('clreq.by_agent_id = ?', $agentId);
            }
            $select->where("clreq.status = ?", STATUS_DEBITED);
            $select->where('clreq.txn_type = ?', TXNTYPE_CARD_DEBIT);
            
            if ($onDate) {
                $date = isset($param['date']) ? $param['date'] : '';
                $select->where('DATE(l.date_created) =?', $date);
            } else {
                $fromDate = isset($param['from']) ? $param['from'] : '';
                $toDate = isset($param['to']) ? $param['to'] : ''; 
                $select->where("DATE(rdd.date_created) BETWEEN '" . $fromDate . "' AND '" . $toDate . "'"); 
            }
            return $this->fetchRow($select);
        } else {
            return FALSE;
        }
        /*
         $select = $this->select()
              ->setIntegrityCheck(false)
              ->from($this->_name ." as l", array('sum(l.amount) as total_agent_load_amount'))
              ->join(DbTable::TABLE_PURSE_MASTER . " as p", "l.purse_master_id  = p.id AND p.debit_api_cr = '".$debitAPICr."'",array('p.debit_api_cr'))
               ->where('l.by_agent_id = ?', $agentId);
            $select->where("l.status = ?", STATUS_DEBITED);
            $select->where('l.txn_type = ?', TXNTYPE_CARD_DEBIT);
            
            if ($onDate) {
                $date = isset($param['date']) ? $param['date'] : '';
                $select->where('DATE(l.date_created) =?', $date);
            } else {
                $fromDate = isset($param['from']) ? $param['from'] : '';
                $toDate = isset($param['to']) ? $param['to'] : '';
                $select->where('l.date_created >= ?', $fromDate);
                $select->where('l.date_created <= ?', $toDate);
            }
            
            return $this->fetchRow($select); 
         */
       
    }

    public function getCustTransactions($params){
       $productID = isset($params['product_id'])?$params['product_id']:'';
       $ratCustId = isset($params['customer_id'])?$params['customer_id']:'';
       $walletCode = isset($params['wallet_code'])?$params['wallet_code']:'';
       $ratCustMasterId = isset($params['customer_master_id'])?$params['customer_master_id']:'';
       $details = $this->_db->select()
                ->from(DbTable::TABLE_RAT_TXN_CUSTOMER .' as rtc', array("rtc.amount as amount","rtc.txn_code","rtc.txn_type","rtc.currency","rtc.mode","date_created as txn_date"))
                ->join(DbTable::TABLE_PURSE_MASTER .' as pm', "rtc.purse_master_id = pm.id",array("pm.code"))
                ->joinLeft(DbTable::TABLE_RAT_CORP_LOAD_REQUEST .' as rclr', "rtc.txn_code = rclr.txn_code", array("rclr.narration as load_description","rclr.status as load_status","rclr.txn_no as load_txn_no"))
                ->joinLeft(DbTable::TABLE_RATNAKAR_REMITTANCE_REQUEST .' as rr', "rtc.txn_code = rr.txn_code",array("rr.status as remittance_status", "rr.sender_msg as remittance_description","rr.txnrefnum as remittance_txn_no"))
                ->joinLeft(DbTable::TABLE_RATNAKAR_WALLET_TRANSFER .' as rwt', "rtc.txn_code = rwt.txn_code",array("rwt.status as wallet_status", "rwt.narration as wallet_description","rwt.txnrefnum as wallet_txn_no"));
        
        $details->where("rtc.customer_master_id =?",$ratCustMasterId);
        $details->where("rtc.product_id =?",$productID);
        $details->where("rtc.txn_type IN ('". TXNTYPE_RAT_CORP_CORPORATE_LOAD."','".TXNTYPE_RAT_CORP_MEDIASSIST_LOAD."','".TXNTYPE_CARD_RELOAD."','".TXNTYPE_CARD_DEBIT."','".TXNTYPE_REMITTANCE."','".TXNTYPE_WALLET_TOWALLET_FUND_TRANSFER."')");  
   
        if( ($walletCode !='') && ($walletCode == 'all') ){
          $details->where( "(rclr.status = '".STATUS_LOADED."') OR (rclr.status = '".STATUS_DEBITED."')  OR (rwt.status = '".STATUS_SUCCESS."') OR  (rr.status = '" . STATUS_SUCCESS . "' OR rr.status = '" . STATUS_REFUND . "')"); 
        }elseif($walletCode !=''){
           $details->where("pm.code =?",$walletCode);
         //  $details->where( "(rclr.status != '".STATUS_FAILED."') AND (rwt.status != '".FLAG_FAILURE."') AND  (rr.status != '" . FLAG_FAILURE . "')"); 
        }
        $details->order('rtc.id DESC');
        $details->limit(TXN_HISTORY_COUNT);   
        $res = $this->_db->fetchAll($details);
       // return $res;
       if(!empty($res)){
           return $res;
       }else{
           return FALSE;
       }
       
   }

   
    public function getActiveVoucherNumofProductAPI($params){
        
        $currentDate = date('Y-m-d H:i:s');
        $select = $this->select()
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('count(id) as total_active_voucher_num'))
                ->where('product_id = ?', $params['product_id'])
                ->where('customer_master_id = ?', $params['customer_master_id'])
                ->where('amount_available > 0')
                ->where('purse_master_id IN ('.$params['purse_master_id'].')')
                ->where("date_expiry >='".$currentDate."' OR date_expiry='0000-00-00 00:00:00'")
                
                ->where('status = ?', STATUS_LOADED);
       // echo $select;exit;
        $rs = $this->_db->fetchRow($select);
        if(!empty($rs) && ($rs['total_active_voucher_num'] !='') ){
            return $rs;
         }
        
        return false;
    }

    public function getAgentDebitReversals($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $txnType = isset($param['txn_type']) ? $param['txn_type'] : '';
        $onDate = isset($param['on_date']) ? $param['on_date'] : FALSE;
        $status = isset($param['status']) ? $param['status'] : '';       
        
        if ($agentId > 0) {
            //Enable DB Slave
            $this->_enableDbSlave();
            $select = $this->select();

            $select->from($this->_name . ' as l', array('sum(l.amount) as total_agent_load_amount'));
            $select->join($this->_name . ' as lr', "l.original_transaction_id=lr.id AND lr.status_settlement='".STATUS_SETTLED."'", array());
            if($agentId != ''){
                $select->where('l.by_agent_id = ?', $agentId);
            }
            if($status != ''){
                $select->where("l.status IN ('".$status."')");
            } else {
                $select->where("l.status = ?", STATUS_LOADED);
            }
            $select->where('l.original_transaction_id != 0');
            if ($txnType != '') {
                $select->where('l.txn_type = ?', $txnType);
            } else {
                $select->where("l.txn_type IN ('". TXNTYPE_RAT_CORP_CORPORATE_LOAD."','".TXNTYPE_RAT_CORP_MEDIASSIST_LOAD."','".TXNTYPE_CARD_RELOAD."')");  
            }
            
            if ($onDate) {
                $date = isset($param['date']) ? $param['date'] : '';
                $select->where('DATE(l.date_created) =?', $date);
            } else {
                $fromDate = isset($param['from']) ? $param['from'] : '';
                $toDate = isset($param['to']) ? $param['to'] : '';
                $select->where('l.date_created >= ?', $fromDate);
                $select->where('l.date_created <= ?', $toDate);
            }

            $row = $this->fetchRow($select);
            //Disable DB Slave
            $this->_disableDbSlave();
            return $row;
        }
        else
            return 0;
    }
    
    public function getSuccessfulReversalLoads($param) {
        $type = isset($param['txn_type']) ? $param['txn_type'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $status = isset($param['status']) ? $param['status'] : '';
//        $settlement_status = isset($param['settlement_status']) ? $param['settlement_status'] : '';
	$productId = isset($param['product_id'])?$param['product_id']:0;
        $wallet_type = isset($param['wallet_type']) ? $param['wallet_type'] : '';
        $onDate = (isset($param['onDate']) && $param['onDate'] == FLAG_YES) ? FLAG_YES : FLAG_NO;
        
        $select = $this->select() 
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST. " as rclr", array('count(*) as count', 'sum(amount) as total','date_load'))
                ->joinLeft(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id = rclr.purse_master_id ",array());
        
        if ($type != '') {
            $select->where('rclr.txn_type = ?', $type);
        }
       	if ($productId != '') {
            $select->where('rclr.product_id = ?', $productId);
        }
        $select->where('rclr.original_transaction_id > ?', 0);
        
        if ($onDate) {
            $date = isset($param['date']) ? $param['date'] : '';
            $select->where('DATE(rclr.date_load) =?', $date);
        } elseif ($from != '' && $to != ''){
            $select->where("rclr.date_load >=  '" . $from . "'");
            $select->where("rclr.date_load <= '" . $to . "'");
        }
        
        if(!empty($status))
        {
            $select->where("rclr.status = ?", $status);
        } else {
            $select->where("rclr.status  = ?",  STATUS_LOADED );
        }
        
        if (!empty($wallet_type)) {
            $select->where("pm.is_virtual = ? " , $wallet_type);
        }
//        if ($settlement_status != '') {
//            $select->where('status_settlement = ?', $settlement_status);
//        }
//        $select->group("product_id");
//	echo $select."<br>"; //exit;
        return $this->fetchRow($select);
    }
    
    public function getAgentDebitReversalsAmount($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $txnType = isset($param['txn_type']) ? $param['txn_type'] : '';
        $onDate = isset($param['on_date']) ? $param['on_date'] : FALSE;
        $status = isset($param['status']) ? $param['status'] : '';       
        
        if ($agentId > 0) {
            //Enable DB Slave
            $this->_enableDbSlave();
            $select = $this->select();

            $select->from($this->_name . ' as l', array('sum(l.amount) as total_agent_load_amount'));
            $select->where('l.reversal_by = ?', $agentId);
            $select->where('l.original_transaction_id > 0');
            
            if($status != ''){
                $select->where("l.status IN ('".$status."')");
            } else {
                $select->where("l.status = ?", STATUS_LOADED);
            }
            if ($txnType != '') {
                $select->where('l.txn_type = ?', $txnType);
            } else {
                $select->where("l.txn_type IN ('". TXNTYPE_RAT_CORP_CORPORATE_LOAD."','".TXNTYPE_RAT_CORP_MEDIASSIST_LOAD."','".TXNTYPE_CARD_RELOAD."')");  
            }
            
            if ($onDate) {
                $date = isset($param['date']) ? $param['date'] : '';
                $select->where('DATE(l.date_created) =?', $date);
            } else {
                $fromDate = isset($param['from']) ? $param['from'] : '';
                $toDate = isset($param['to']) ? $param['to'] : '';
                $select->where('l.date_created >= ?', $fromDate);
                $select->where('l.date_created <= ?', $toDate);
            }
           
            $row = $this->fetchRow($select);
            //Disable DB Slave
            $this->_disableDbSlave();
            return $row;
        }
        else
            return 0;
    }
    
    public function getReversalLoadsRecordbyId($id) {
       
      
        $select = $this->select() 
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, array('txn_no', 'date_created','date_load'));
        $select->where("status = '".STATUS_DEBITED."'");
        $select->where('id = ?', $id);
        return $this->fetchRow($select);
    }
    
    public function getReversaledCustomerPurseInfo($params) {
        $debitId = isset($params['original_transaction_id']) ? $params['original_transaction_id'] : '';
        $productId = isset($params['product_id']) ? $params['product_id'] : '';
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`rclr`.`card_number`,'".$decryptionKey."') as card_number");
        
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_DEBIT_DETAIL . ' as rtc', array("rtc.amount as amount", "rtc.txn_code", "rtc.txn_type", "date_created as txn_date", "rtc.bank_id", "rtc.product_id", "rtc.customer_master_id", "rtc.purse_master_id", "rtc.customer_purse_id"))
                ->join(DbTable::TABLE_PURSE_MASTER . ' as pm', "rtc.purse_master_id = pm.id", array("pm.code as wallet_code", "pm.allow_expiry"))
                ->joinLeft(DbTable::TABLE_RAT_CORP_LOAD_REQUEST_DETAIL . ' as rclrd', "rtc.id = rclrd.debit_detail_id", array("rclrd.amount as debit_amount", "rclrd.debit_id"))
                ->joinLeft(DbTable::TABLE_RAT_CORP_LOAD_REQUEST . ' as rclr', "rclrd.load_request_id = rclr.id", array("rclr.cardholder_id", "rclr.txn_type", "rclr.load_channel", "rclr.txn_identifier_type", $card_number, "rclr.currency", "rclr.narration", "rclr.txn_no", "rclr.is_reversal", "rclr.original_transaction_id", "rclr.card_type", "rclr.voucher_num", "rclr.corporate_id", "rclr.mode", "rclr.txn_code", "rclr.by_agent_id", "rclr.by_ops_id", "rclr.by_corporate_id", "rclr.ip", "rclr.date_expiry"));

        if ($debitId != '') {
            $select->where('rtc.debit_id = ?', $debitId);
        }
        if ($productId != '') {
            $select->where('rtc.product_id = ?', $productId);
        }
        $select->where('rtc.txn_status = ?', STATUS_SUCCESS);

        $rs = $this->_db->fetchAll($select);

        if (!empty($rs)) {
            return $rs;
        } else {
            return FALSE;
        }
		
    }
    
    public function getAgentUnSettementRequests($params) {
        $curdate = date("Y-m-d");
        $debitDate = isset($params['debit_date']) ? $params['debit_date'] : date("Y-m-d", strtotime("-1 day"));
        $requestRecord = array();
        $recordType = isset($params['type']) ? $params['type'] : 'WITHOUT_CARD';
        $status = isset($params['status']) ? $params['status'] : '';
        $statusSettlement = isset($params['status_settlement']) ? $params['status_settlement'] : '';
	
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`rclr`.`card_number`,'".$decryptionKey."') as card_number");
	
        // Getting Agent Records
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST . " as rclr", array('id as request_id', 'amount as t_amount', 'txn_code', 'txn_no', $card_number, 'product_id', 'by_agent_id', 'date_load as date'))
                ->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "rclr.by_agent_id = ad.agent_id AND ad.status = '" . AGENT_ACTIVE_STATUS . "'", array('concat(ad.first_name," ",ad.last_name) as agent_name', 'ad.bank_account_number as agent_account_number', 'ad.bank_ifsc_code as agent_ifsc_code', 'ad.estab_city as city', 'ad.email'))
                ->joinLeft(DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION . " as bp", "bp.agent_id = ad.agent_id AND bp.product_id =rclr.product_id AND '" . $debitDate . "' >= bp.date_start AND ('" . $debitDate . "' <= bp.date_end OR bp.date_end = '0000-00-00' OR bp.date_end is NULL) ", array())
                ->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "rclr.product_id  = p.id", array('p.name as product_name', 'p.unicode as unicode'));
        $select->join(DbTable::TABLE_PURSE_MASTER . ' as pm', "p.id = pm.product_id", array('pm.code as wallet_code'));
        $select->join(DbTable::TABLE_RAT_DEBIT_DETAIL . ' as rdd', "rdd.debit_id = rclr.id", array('rdd.amount'));
        if ($status != '') {
            $select->where('rclr.status = ?', $status);
        }
        if ($statusSettlement != '') {
            $select->where('rclr.status_settlement = ?', $statusSettlement);
        }
        $select->where('rclr.settlement_request_id = ?', 0);
        $select->where('rclr.product_id != ?', PRODUCT_ID_SMP);
        $select->where('pm.is_virtual = ?', FLAG_NO);
        $select->where('pm.id = rdd.purse_master_id');
        $select->where('rclr.by_agent_id != 0');
        if ($recordType == 'WITHOUT_CARD') {
            $select->where("rclr.card_number = '' OR isnull(rclr.card_number)");
        } else {
            $select->where("rclr.card_number !='' AND rclr.card_number!=0 AND rclr.card_number IS NOT NULL");
        }
        $select->where('date(rclr.date_load) = ?', $debitDate);
//        $select->where('date(rclr.date_reversal) != ?', $debitDate);
        $select->where("rclr.is_reversal != '".REVERSAL_FLAG_YES."' ");
        $select->order('rclr.by_agent_id DESC');

        $agentRecords = $this->_db->fetchAll($select);
        if (!empty($agentRecords)) {
            $requestRecord = array_merge($requestRecord, $agentRecords);
        }
        // Getting Corporate Records
        $select = $this->_db->select()
                ->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST . " as rclr", array('id as request_id', 'amount as t_amount', 'txn_code', 'txn_no', $card_number, 'product_id', 'by_corporate_id as by_agent_id', 'corporate_id', 'date_load as date'))
                ->joinLeft(DbTable::TABLE_CORPORATE_USER_DETAILS . " as ad", "rclr.by_corporate_id = ad.corporate_user_id AND ad.status = '" . AGENT_ACTIVE_STATUS . "'", array('concat(ad.first_name," ",ad.last_name) as agent_name', 'ad.bank_account_number as agent_account_number', 'ad.bank_ifsc_code as agent_ifsc_code', 'ad.estab_city as city', 'ad.email'))
                ->joinLeft(DbTable::TABLE_BIND_CORPORATE_PRODUCT_COMMISSION . " as bp", "bp.corporate_id = ad.corporate_user_id AND bp.product_id =rclr.product_id AND '" . $debitDate . "' >= bp.date_start AND ('" . $debitDate . "' <= bp.date_end OR bp.date_end = '0000-00-00' OR bp.date_end is NULL) ", array())
                ->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "rclr.product_id  = p.id", array('p.name as product_name', 'p.unicode as unicode'));
        $select->join(DbTable::TABLE_PURSE_MASTER . ' as pm', "p.id = pm.product_id", array('pm.code as wallet_code'));
        $select->join(DbTable::TABLE_RAT_DEBIT_DETAIL . ' as rdd', "rdd.debit_id = rclr.id", array('rdd.amount'));
        if ($status != '') {
            $select->where('rclr.status = ?', $status);
        }
        if ($statusSettlement != '') {
            $select->where('rclr.status_settlement = ?', $statusSettlement);
        }
        $select->where('rclr.settlement_request_id = ?', 0);
        $select->where('rclr.product_id != ?', PRODUCT_ID_SMP);
        $select->where('pm.is_virtual = ?', FLAG_NO);
        $select->where('pm.id = rdd.purse_master_id');
        $select->where('rclr.by_corporate_id != 0');
        if ($recordType == 'WITHOUT_CARD') {
            $select->where("rclr.card_number = '' OR isnull(rclr.card_number)");
        } else {
            $select->where("rclr.card_number !='' AND rclr.card_number!=0 AND rclr.card_number IS NOT NULL");
        }
        $select->where('date(rclr.date_load) = ?', $debitDate);
//        $select->where('date(rclr.date_reversal) != ?', $debitDate);
        $select->where("rclr.is_reversal != '".REVERSAL_FLAG_YES."' ");
        $select->order('rclr.by_agent_id DESC');

        $corporateRecords = $this->_db->fetchAll($select);
        if (!empty($corporateRecords)) {
            $requestRecord = array_merge($requestRecord, $corporateRecords);
        }

        // }
        return $requestRecord;
    }
   
    public function getvirtualWalletbalance($data) {
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`c`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`c`.`crn`,'".$decryptionKey."') as crn");
        
        $productId = isset($data['product_id']) && $data['product_id'] > 0 ? $data['product_id'] : ''; 
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . ' as c', array($card_number,$crn,'c.medi_assist_id as member_id', 'c.employee_id','c.aadhaar_no','c.mobile', 'c.partner_ref_no'));
        $select->joinLeft(DbTable::TABLE_RAT_CUSTOMER_PURSE_CLOSING_BALANCE . ' as cb', "c.customer_master_id = cb.customer_master_id AND c.product_id = cb.product_id", array('cb.closing_balance', 'cb.date', 'cb.customer_master_id'));
        $select->join(DbTable::TABLE_PRODUCTS . ' as p', "c.product_id = p.id", array('p.id as product_id','p.name as product_name','program_type'));
        $select->join(DbTable::TABLE_PURSE_MASTER . ' as pm', "cb.purse_master_id = pm.id", array('pm.code as wallet_code'));
        $select->joinLeft(DbTable::TABLE_CORPORATE_USER . ' as cu', "c.by_corporate_id = cu.id", array('cu.corporate_code', 'concat(cu.first_name," ",cu.last_name) as corporate_name'));
        
        if($productId != ''){
            $select->where("c.product_id =?",$data['product_id']);
        }
        $select->where("c.status = ?", STATUS_ACTIVE);
        $select->where("cb.date = '" . $data['to'] . "'");
        $select->where("pm.is_virtual = ?", FLAG_YES); 
        $select->order('c.id ASC'); 
	return $this->_db->fetchAll($select);
    }
    
    
    public function exportGetVirtualWalletbalance($param) {
        $data = $this->getvirtualWalletbalance($param);
        $retData = array();
        $rsCount = count($data);
        $count = 1;
        $totalBal = '0.00';
        if (!empty($data)) {
            foreach ($data as $key => $data) {
                $retData[$key]['product_name'] = $data['product_name'];
                $retData[$key]['bank_name'] = RATNAKAR_BANK_NAME;
                $retData[$key]['aadhaar_no'] = $data['aadhaar_no'];
                $retData[$key]['currency'] = CURRENCY_INR;
                $retData[$key]['card_number'] = Util::maskCard($data['card_number'], 4);
                $retData[$key]['crn'] = Util::maskCard($data['crn'], 4);
		$retData[$key]['mobile'] = $data['mobile'];
                $retData[$key]['member_id'] = $data['member_id'];
                $retData[$key]['partner_ref_no'] = $data['partner_ref_no'];
                $retData[$key]['status'] = 'Active';
		$retData[$key]['corporate_code'] = $data['corporate_code'];
		$retData[$key]['corporate_name'] = $data['corporate_name'];
                $retData[$key]['report_date'] = Util::returnDateFormatted($data['date'], "d-m-Y", "Y-m-d", "-");
                $retData[$key]['wallet_code'] = $data['wallet_code'];
                $retData[$key]['closing_balance'] = $data['closing_balance']; 
                if($count == 1) {
                    $totalBal = $data['closing_balance'];
                    $custMasterId = $data['customer_master_id'];
                    if($rsCount == $count) {
                        $retData[$key]['total_bal'] = $totalBal;
                    }
                } elseif($custMasterId == $data['customer_master_id']) {
                    $totalBal += $data['closing_balance'];
                    $custMasterId = $data['customer_master_id'];

                    if($rsCount == $count) { 
                        $retData[$key]['total_bal'] = $totalBal;
                    } 
                } elseif($custMasterId != $data['customer_master_id']) { 
                    $retData[$key-1]['total_bal'] = $totalBal;
                    
                    if($rsCount == $count) {
                        $totalBal = 0;
                        $totalBal += $data['closing_balance'];
                        $custMasterId = $data['customer_master_id'];
                        $retData[$key]['total_bal'] = $totalBal;
                    } else {
                        $totalBal = 0;
                        $totalBal += $data['closing_balance'];
                        $custMasterId = $data['customer_master_id'];
                    }
                }
                $count++;
            }
        }
        return $retData;
	
    }
        
    
    
     public function getTotalDebit($param) {
        $custPurseId = isset($param['customer_purse_id']) ? $param['customer_purse_id'] : '';
        $txnType = isset($param['txn_type']) ? $param['txn_type'] : '';
        $onDate = isset($param['on_date']) ? $param['on_date'] : FALSE;
		
        if ($custPurseId > 0) {
            //Enable DB Slave
            $this->_enableDbSlave();		
	    $select = $this->select();
	    $select->setIntegrityCheck(false);
	    $select->from(DbTable::TABLE_RAT_DEBIT_DETAIL, array('sum(amount) as total_load_amount'));

	    if($custPurseId != ''){
		$select->where('customer_purse_id = ?', $custPurseId);
	    }
	    $select->where("txn_status IN (?)",FLAG_SUCCESS);
	    $select->where('txn_type = ?', TXNTYPE_CARD_DEBIT);
            
	    if ($onDate) {
		$date = isset($param['date']) ? $param['date'] : '';
		$select->where('DATE(date_created) =?', $date);
	    } else {
		$fromDate = isset($param['from']) ? $param['from'] : '';
		$toDate = isset($param['to']) ? $param['to'] : '';
		$select->where('date_created >= ?', $fromDate);
		$select->where('date_created <= ?', $toDate);
	    } 
	    
	    $row = $this->fetchRow($select);
	    //Disable DB Slave
	    $this->_disableDbSlave();
	    return $row; 
        }
        else
	    return 0;
    }
    
    
     public function getTotalDebitByPurse($param) {
      //  $custPurseId = isset($param['customer_purse_id']) ? $param['customer_purse_id'] : '';
        $txnType = isset($param['txn_type']) ? $param['txn_type'] : '';
        $onDate = isset($param['on_date']) ? $param['on_date'] : FALSE;
        $wallet_type = isset($param['wallet_type']) ? $param['wallet_type'] : '';
        $productId = isset($param['product_id'])?$param['product_id']:'';
		
            //Enable DB Slave
            $this->_enableDbSlave();		
	    $select = $this->select();
	    $select->setIntegrityCheck(false);
	    $select->from(DbTable::TABLE_RAT_DEBIT_DETAIL. " as rdd", array('sum(amount) as total_load_amount'));
            $select->joinLeft(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id = rdd.purse_master_id ",array());

	    if($productId != ''){
		$select->where('rdd.product_id = ?', $productId);
	    }
	    $select->where("rdd.txn_status IN (?)",FLAG_SUCCESS);
	    $select->where('rdd.txn_type = ?', TXNTYPE_CARD_DEBIT);
            
	    if ($onDate) {
		$date = isset($param['date']) ? $param['date'] : '';
		$select->where('DATE(rdd.date_created) =?', $date);
	    } else {
		$fromDate = isset($param['from']) ? $param['from'] : '';
		$toDate = isset($param['to']) ? $param['to'] : '';
		$select->where('rdd.date_created >= ?', $fromDate);
		$select->where('rdd.date_created <= ?', $toDate);
	    } 
            
            if (!empty($wallet_type)) {
                $select->where("pm.is_virtual = ? " , $wallet_type);
            }
	    $row = $this->fetchRow($select);
	    //Disable DB Slave
	    $this->_disableDbSlave();
	    return $row; 
        
    }
    
    public function exportGetWallettxn($param) {

        $data = $this->getWalletTxn($param);
        $retData = array();
        
        if (!empty($data)) {
            foreach ($data as $key => $data) {
                $retData[$key]['txn_date'] = $data['txn_date'];
                $retData[$key]['product_name'] = $data['product_name'];
                $retData[$key]['bank_name'] = $data['bank_name'];
                $retData[$key]['card_number'] = $data['card_number'];
		$retData[$key]['card_pack_id'] = $data['card_pack_id'];
                $retData[$key]['member_id'] = $data['member_id'];
                $retData[$key]['txn_type'] = $data['txn_type'];
                $retData[$key]['status'] = ucfirst($data['status']);
		$retData[$key]['wlt_code'] = $data['wlt_code'];
		$retData[$key]['mode'] = $data['mode'];
                $retData[$key]['wallet_hr_dr'] = $data['wallet_hr_dr'];
                $retData[$key]['trans_amount'] = $data['trans_amount'];
                $retData[$key]['fee_amt'] = $data['fee_amt'];
                $retData[$key]['service_tax'] = $data['service_tax'];
                $retData[$key]['txn_no'] = $data['txn_no'];
                $retData[$key]['txn_code'] = $data['txn_code'];                
                $retData[$key]['failed_reason'] = $data['failed_reason'];
                $retData[$key]['mcc_code'] = $data['mcc_code'];
                $retData[$key]['tid'] = $data['tid'];
                $retData[$key]['mid'] = $data['mid'];                
                $retData[$key]['channel'] = $data['channel'];
                $retData[$key]['rev_indicator'] = $data['rev_indicator'];
                $retData[$key]['narration'] = $data['narration'];
            }
        }
        return $retData;
    }
    
    public function doCardloadByAgent($dataArr) {
       
        if (empty($dataArr)) {
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_CARDLOAD_MSG, ErrorCodes::ERROR_EDIGITAL_INSUFFICIENT_DATA_CARDLOAD_CODE);
        }
        $custModel = new Corp_Ratnakar_Cardholders();
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $baseTxn = new BaseTxn();
        $object = new Corp_Ratnakar_Cardholders();
        $productModel = new Products();
        $str = '';
        $priorityWalletCode = '';
        $loadStatus = STATUS_FAILED;
        $product = App_DI_Definition_BankProduct::getInstance($dataArr['bank_product_const']);
        $genWalletCode = $product->purse->code->genwallet;
        $productId = $dataArr['product_id'];
        $ecsCall = FALSE;
        $apiResp = TRUE;
        
        $cardloadFee = isset($dataArr['fee']) ? $dataArr['fee'] : 0;
        $cardloadServiceTax = isset($dataArr['service_tax']) ? $dataArr['service_tax'] : 0;
        
         
      //  $masterPriPurseDetails = $masterPurseModel->getProductPurseBasicDetails($productId, 'priority');
        //
        /*
         * Partical Transaction Validation
         */
        $validPurseCode = true;
        $validProduct = true;
        $allowVouncher  = true; 
        $walletCode = $dataArr['wallet_code'];
        $loadExpiry = $dataArr['Filler1'];
              
        //
        if($dataArr['bank_id'] ==''){
        $productDetail = $productModel->getProductInfo($dataArr['product_id']);
        $dataArr['bank_id'] = $productDetail['bank_id'];  
        }
        
        try {
            $cardholderId = $dataArr['cardholder_id'];
            
            $pursecode = ($dataArr['wallet_code'] != '')?$dataArr['wallet_code']: $priorityWalletCode;
            
            $searchArr = array('cardholder_id' => $cardholderId, 'status' => STATUS_ACTIVE);
            $cardholderDetails = $custModel->getCardholderInfo($searchArr);
            if(!isset($cardholderDetails->id) || $cardholderDetails->id == '')
            {
                $loadStatus = STATUS_FAILED;
                $failedReason = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                $dateFailed = new Zend_Db_Expr('NOW()');   
                $dateLoad = new Zend_Db_Expr('NOW()');   
            } else {
                $loadStatus = STATUS_PENDING;
                $failedReason = '';
                $failedCode = '';
                $dateFailed = '';   
                $dateLoad = '';
            }
            
            $cardNumber = $cardholderDetails->card_number;
            $mediAssistId = $cardholderDetails->medi_assist_id;
            $customerMasterId = ($cardholderDetails->customer_master_id != '') ? $cardholderDetails->customer_master_id : 0;
            $ratCustomerId = ($cardholderDetails->rat_customer_id != '') ? $cardholderDetails->rat_customer_id : 0;
            $customerPurseId = 0;
            $masterPurseDetails = $masterPurseModel->getPurseIdByPurseCodeAPI($pursecode,$productId);
            if($ratCustomerId > 0) { 
                if(!empty($masterPurseDetails)){
                    $purseDetails = $custPurseModel->getCustPurseDetails(array('rat_customer_id' => $ratCustomerId, 'purse_master_id' => $masterPurseDetails['id']));
                    $customerPurseId = (isset($purseDetails['id']) && $purseDetails['id'] > 0) ? $purseDetails['id'] : 0;
                }else{
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_CODE);
                }
            }
            
            $isVirtual = strtolower($masterPurseDetails['is_virtual']);
            $purseMasterId = $masterPurseDetails['id'];
            $amount = 0;
            
          
            //
            if($loadStatus == STATUS_PENDING)
            {
                if(!$validPurseCode)
                  {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_MSG;
                    $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_CODE;
                    $dateFailed = new Zend_Db_Expr('NOW()');  
                    $dateLoad = new Zend_Db_Expr('NOW()');
                    $amount = $dataArr['amount'];
                }
                elseif($dataArr['product_id'] != $masterPurseDetails['product_id'])
                {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_MSG;
                    $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_CODE;
                    $dateFailed = new Zend_Db_Expr('NOW()');  
                    $dateLoad = new Zend_Db_Expr('NOW()');
                    $amount = $dataArr['amount'];
                }
                elseif(strpos($dataArr['amount'],'.') !== FALSE)
                {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = ErrorCodes::ERROR_EDIGITAL_INVALID_REVERSAL_AMOUNT_MSG;
                    $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_REVERSAL_AMOUNT_CODE;
                    $dateFailed = new Zend_Db_Expr('NOW()');  
                    $dateLoad = new Zend_Db_Expr('NOW()');
                    $amount = $dataArr['amount'];
                }
                elseif(strpos($dataArr['amount'],' ') !== FALSE)
                {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = ErrorCodes::ERROR_EDIGITAL_INVALID_REVERSAL_AMOUNT_MSG;
                    $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_REVERSAL_AMOUNT_CODE;
                    $dateFailed = new Zend_Db_Expr('NOW()');  
                    $dateLoad = new Zend_Db_Expr('NOW()');
                    $amount = $dataArr['amount'];
                }
                elseif(strtolower($dataArr['card_type']) != strtolower(CORP_CARD_TYPE_NORMAL))
                {
                    $loadStatus = STATUS_FAILED;
                    $failedReason = ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_TYPE_MSG;
                    $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CARD_TYPE_CODE;
                    $dateFailed = new Zend_Db_Expr('NOW()');  
                    $dateLoad = new Zend_Db_Expr('NOW()');
                    $amount = $dataArr['amount'];
                }
                
            }

            if($amount != $dataArr['amount'])
            {
                $amount = Util::convertToRupee($dataArr['amount']);
            }
            //$txnCode = $baseTxn->generateTxncode();
            $txnCode = $dataArr['txn_code'];
            if($txnCode == ''){
              $txnCode = $baseTxn->generateTxncode();   
            }
            
            
            /*
             * Reversal Checks
             */
            $revOrigTxn_loadId = 0;
            $isReversal = 'n';
            /*
             * 
             */
            $date_expiry = $dataArr['date_expiry'];
            $loadMode = strtolower($dataArr['mode']);
            if (strtolower($dataArr['mode']) == TXN_MODE_DR) {
                $txnType = TXNTYPE_CARD_DEBIT;
              //  $loadStatus = STATUS_DEBITED;
            }elseif (strtolower($dataArr['mode']) == TXN_MODE_CR) {
                $txnType = TXNTYPE_CARD_RELOAD;
                if($date_expiry == ''){
                  //  $date_expiry = Util::getdefaultExpiryDate();
                }
            }else {
                $txnType = '';
                $loadStatus = STATUS_FAILED;
                $failedReason = ErrorCodes::getMode($dataArr['mode']);
                $failedCode = ErrorCodes::ERROR_EDIGITAL_INVALID_MODE_CODE;
                $dateFailed = new Zend_Db_Expr('NOW()');  
                $dateLoad = new Zend_Db_Expr('NOW()');
                $amount = $dataArr['amount'];
            }  

            
            // Duplicacy check for TxnNo
            $loadDetails = $this->getLoadDetails(array('txn_no' => $dataArr['txn_no'], 'product_id'=>$dataArr['product_id']));
            if (!empty($loadDetails) ) {                
                $loadStatus = STATUS_FAILED;
                $failedReason = 'Txn No already in use';
                $dateFailed = new Zend_Db_Expr('NOW()');  
                $dateLoad = new Zend_Db_Expr('NOW()');
                $amount = $dataArr['amount'];
            }

            if ( (strtolower($dataArr['mode']) == TXN_MODE_DR) && ($dataArr['wallet_code']=='')  && (sizeof($masterPriPurseDetails) > 1) ){
                $customerPurseId = 0; 
                $purseMasterId = 0;
                $pursecode = '';
            }
            //$encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            //$cardNumberEnc = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')");
            $data = array(
                'customer_master_id' => $customerMasterId,
                'cardholder_id' => $cardholderId,
                'customer_purse_id' => $customerPurseId,
                'txn_type' => $txnType,
                'load_channel' => BY_API,
                'purse_master_id' => $purseMasterId,
                'txn_identifier_type' => $dataArr['txn_identifier_type'],
                'txn_identifier_num' => $dataArr['txn_identifier_num'],
                'card_number' => Util::insertCardCrn($cardNumber),
                'medi_assist_id' => $mediAssistId,
                'amount' => $amount,
                'amount_available' => 0,
                'amount_used' => 0,
                'amount_cutoff' => 0,
                'fee' => $cardloadFee,
                'service_tax' => $cardloadServiceTax,
                'currency' => CURRENCY_INR_CODE,
                'narration' => $dataArr['narration'],
                'wallet_code' => strtoupper($pursecode),
                'txn_no' => $dataArr['txn_no'],
                'card_type' => $dataArr['card_type'],
                'corporate_id' => $dataArr['corporate_id'],
                'mode' => $dataArr['mode'],
                'txn_code' => $txnCode,
                'ip' => $this->formatIpAddress(Util::getIP()),
                'by_ops_id' => 0,
                'batch_name' => '',
                'bank_id' => $dataArr['bank_id'],
                'product_id' => $dataArr['product_id'],
                'status' => $loadStatus,
                'date_created' => new Zend_Db_Expr('NOW()'),
                'failed_reason' => $failedReason,
                'date_failed' => $dateFailed,
                'date_load' => $dateLoad,
                'date_expiry' => $date_expiry,
                'voucher_num' => $dataArr['voucher_num'],
                'is_reversal' => $isReversal,
                'original_transaction_id' => $revOrigTxn_loadId,
                'channel' => $dataArr['channel'],

            );
            if($dataArr['manageType'] == CORPORATE_MANAGE_TYPE){
                $data['by_corporate_id'] = $dataArr['by_api_user_id'];
                
            }
            else{
                $data['by_agent_id'] = $dataArr['by_api_user_id'];
                $data['by_corporate_id'] = 0;
            }
            $this->insert($data);
            $loadRequestId = $this->_db->lastInsertId(DbTable::TABLE_RAT_CORP_LOAD_REQUEST, 'id');
            $this->_db->beginTransaction();
            if($loadStatus == STATUS_PENDING ){
                    if($loadMode == TXN_MODE_CR ) {

                        $validator = array(
                            'load_request_id' => $loadRequestId,
                            'customer_master_id' => $customerMasterId,
                            'purse_master_id' => $purseMasterId,
                            'customer_purse_id' => $customerPurseId,
                            'amount' => $amount,
                            'agent_id' => $dataArr['by_api_user_id'],
                            'product_id' => $productId,
                            'bank_id' => $dataArr['bank_id'],
                            'manageType' => $dataArr['manageType'],
                            'is_virtual' => $isVirtual,
                            'isReversal' => $isReversal,
                            'revLoadId' => $revOrigTxn_loadId,
                            'fee_amt' => $cardloadFee,
                            'service_tax' => $cardloadServiceTax,
                        );

                        $flgValidate = $baseTxn->chkAllowRatMediAssistCardLoadByAgent($validator);
                        if ($flgValidate) {
                            $loadStatus = STATUS_SUCCESS;
			if($masterPurseDetails['code'] == $genWalletCode && $cardholderDetails->card_number != '') {
				$ecsCall = TRUE;
				$cardLoadData = array(
                                    'amount' => (string) $amount,
                                    'crn' => $cardholderDetails->card_number,
                                    'agentId' => $dataArr['by_api_user_id'],
				    'transactionId' => $txnCode,
				    'currencyCode' => CURRENCY_INR_CODE,
				    'countryCode' => COUNTRY_IN_CODE
				    );
                            if(DEBUG_MVC) {
                            $apiResp = TRUE;
                            $ecsCall = FALSE;
                            } else {
                                $ecsApi = new App_Socket_ECS_Corp_Transaction();
                                $apiResp = $ecsApi->cardLoad($cardLoadData);
                                }
                             }

        if ($apiResp === TRUE) {
                $updateArr = array(
                    'amount_available' => $amount,
                    'amount_used' => 0,
                    'amount_cutoff' => 0,
                    'txn_load_id' => $ecsCall == TRUE ? $ecsApi->getISOTxnId() : '',
                    'status' => STATUS_LOADED,
                    'date_load' => new Zend_Db_Expr('NOW()')
                );

                $this->updateLoadRequests($updateArr, $loadRequestId);

                $baseTxnParams = array(
                    'txn_code' => $txnCode,
                    'customer_master_id' => $customerMasterId,
                    'product_id' => $productId,
                    'bank_id' => $dataArr['bank_id'],
                    'purse_master_id' => $purseMasterId,
                    'customer_purse_id' => $customerPurseId,
                    'amount' => $amount,
                    'agent_id' => $dataArr['by_api_user_id'],
                    'txn_type' => TXNTYPE_CARD_RELOAD,
                    'ip' => $this->formatIpAddress(Util::getIP()),
                    'manageType' => $dataArr['manageType'],
                    'is_virtual' => $isVirtual,
                    'fee_amt' => $cardloadFee,
                    'service_tax' => $cardloadServiceTax,
                    'load_request_id' => $loadRequestId,
                );
                
                $baseTxn->successRatCardLoadByAgent($baseTxnParams);
                
                if($ecsCall == TRUE) {
                    $cardholderArray['cardNumber'] = $cardholderDetails->card_number;

                    $ecsApi = new App_Api_ECS_Transactions();
                    $res = $ecsApi->balanceInquiry($cardholderArray);
                                  
                    $sendSMS = TRUE;
                    if($dataArr['bank_product_const'] == BANK_RATNAKAR_BOOKMYSHOW){
                        $sendSMS = FALSE;
                    }
                    if($sendSMS){
                        if (strtolower($dataArr['sms_flag']) == strtolower(FLAG_Y)) {
                            // Send SMS
                            $userData = array(
                             'last_four' =>substr($cardholderDetails->card_number, -4),
                             'product_id' => $productId,
                             'mobile' => $cardholderDetails->mobile,
                             'amount' => $amount,
                             'balance' => $balVal,
                            );
                            $object->generateSMSDetails($userData, $smsType = TRANSACTION_REQUEST_CR); 
                        }
                    }

                    if($res){
                        $balVal = $res->balanceInquiryList->availablebalance;                          
                    } else {
                        $balVal = '';  
                    }
                } else {
                    $custPurse = $custPurseModel->getCustBalance($cardholderDetails->rat_customer_id);
                    $balVal = $custPurse['sum']; 
                }

                    if (strtolower($dataArr['sms_flag']) == strtolower(FLAG_Y)) {
                                                        // Send SMS
                    $userData = array(
                     'last_four' =>substr($cardholderDetails->card_number, -4),
                     'product_id' => $productId,
                     'mobile' => $cardholderDetails->mobile,
                     'amount' => $amount,
                     'balance' => $balVal,
                    );
           $object->generateSMSDetails($userData, $smsType = TRANSACTION_REQUEST_CR); 
                 }
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
        
                }else{
                  throw new Exception($failedReason,$failedCode);  
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
                $code = $e->getCode();
                if(empty($code)) {
                    $code = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                }
                //$code = (!empty($e->getCode())) ? $e->getCode() : ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                throw new Exception($e->getMessage(), $code);
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
                $code = $e->getCode();
                if(empty($code)) {
                    $code = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                }
                //$code = (!empty($e->getCode())) ? $e->getCode() : ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
                throw new Exception($e->getMessage(), $code);
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
            $code = $e->getCode();
            if(empty($code)) {
                $code = ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
            }
                
            //$code = (!empty($e->getCode())) ? $e->getCode() : ErrorCodes::ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE;
            throw new Exception($e->getMessage(), $code);
        }
        return array('status' => STATUS_LOADED);
    }
  

public function getRemitterLoadfee($param){
        $to = $param['to'];
        $from = $param['from'];
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : ''; 
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                DbTable::TABLE_RAT_CORP_LOAD_REQUEST . " as r", 
                    array(
                        'r.fee as fee_amount', 'r.service_tax as service_tax_amount' ,'DATE_FORMAT(r.date_created,"%d-%m-%Y") as txn_date','DATE(r.date_created) as date_created','r.status as txn_status','r.txn_code', new Zend_Db_Expr("'".TXNTYPE_CARD_RELOAD."' as transaction_type_name"),'r.amount as transaction_amount', new Zend_Db_Expr("0 as reversal_fee"), new Zend_Db_Expr("0 as reversal_service_tax"), new Zend_Db_Expr("'' as refund_txn_code"), new Zend_Db_Expr("'' as utr")
                ));
        $select->joinLeft(
                DbTable::TABLE_AGENTS . " as a", "r.by_agent_id = a.id ", 
                    array(
                        'a.agent_code', 'concat(a.first_name," ",a.last_name) as name', 'a.user_type as agent_user_type'
                ));
        $select->joinLeft(
                DbTable::TABLE_AGENT_DETAILS . " as ad", "a.id = ad.agent_id AND ad.status = '" . STATUS_ACTIVE . "'", 
                    array(
                        'ad.estab_city', 'ad.estab_pincode'
                ));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel", "a.id = orel.to_object_id" ,array( ));
        $select->joinLeft(
                DbTable::TABLE_AGENTS." as dis", "dis.id = orel.from_object_id" , 
                    array(
                        'dis.agent_code AS dist_code',' concat(dis.first_name," ",dis.last_name) as dist_name',
                ));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel_sdis", "dis.id = orel_sdis.to_object_id" ,array());
        $select->joinLeft(
                DbTable::TABLE_AGENTS." as sdis", "sdis.id = orel_sdis.from_object_id" , 
                    array(
                        'sdis.agent_code AS sup_dist_code' ,'concat(sdis.first_name," ",sdis.last_name) as sup_dist_name'
                ));
        $select->where("r.status = '" . STATUS_LOADED . "' OR r.status = '" . FLAG_FAILURE . "' ");
        $select->where("r.date_created >= ?", $from);
        $select->where("r.date_created <= ?", $to); 
        
        if ($agentId != '') {
            $select->where('r.by_agent_id=?', $agentId);
        }
                
      //  echo $select; exit();
        return $this->fetchAll($select); 	 
    }
    
    
    public function getAgentTotalFeeSTaxLoad($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $txnType = isset($param['txn_type']) ? $param['txn_type'] : '';
        $onDate = isset($param['on_date']) ? $param['on_date'] : FALSE;
        $status = isset($param['status']) ? $param['status'] : '';
        $cutoff = isset($param['cutoff']) ? $param['cutoff'] : FALSE;
        
        if ($agentId > 0) {
        //Enable DB Slave
            $this->_enableDbSlave();

            $select = $this->select();
            if($cutoff){
                $select->from($this->_name. ' as clReq', array( 'sum(clReq.fee) as agent_total_load_fee', 'sum(clReq.service_tax) as agent_total_load_stax', 'count(clReq.id) as agent_total_load_count'));
            } else {
                $select->from($this->_name. ' as clReq', array('sum(clReq.fee) as agent_total_load_fee', 'sum(clReq.service_tax) as agent_total_load_stax', 'count(clReq.id) as agent_total_load_count'));
            } 
            $select->join(
                    DbTable::TABLE_PURSE_MASTER.' as pm',
                    "pm.id = clReq.purse_master_id",array()
            );
            $select->where('pm.is_virtual = ?', FLAG_NO);
            
            if($agentId != ''){
                $select->where('clReq.by_agent_id = ?', $agentId);
            }
            if($status != ''){
                $select->where("clReq.status IN ('".$status."')");
            } else {
                $select->where("clReq.status = ?", STATUS_LOADED);
            }
            $select->where('clReq.original_transaction_id = 0');
            if ($txnType != '') {
                $select->where('clReq.txn_type = ?', $txnType);
            } else{
                $select->where("clReq.txn_type IN ('". TXNTYPE_RAT_CORP_CORPORATE_LOAD."','".TXNTYPE_RAT_CORP_MEDIASSIST_LOAD."','".TXNTYPE_CARD_RELOAD."')");  
            }
            if($cutoff){
                if ($onDate) {
                    $date = isset($param['date']) ? $param['date'] : '';
                    $select->where('DATE(clReq.date_cutoff) =?', $date);
                } else {
                    $fromDate = isset($param['from']) ? $param['from'] : '';
                    $toDate = isset($param['to']) ? $param['to'] : '';
                    $select->where('clReq.date_cutoff >= ?', $fromDate);
                    $select->where('clReq.date_cutoff <= ?', $toDate);
                }
            } else {
                if ($onDate) {
                    $date = isset($param['date']) ? $param['date'] : '';
                    $select->where('DATE(clReq.date_created) =?', $date);
                } else {
                    $fromDate = isset($param['from']) ? $param['from'] : '';
                    $toDate = isset($param['to']) ? $param['to'] : '';
                    $select->where('clReq.date_created >= ?', $fromDate);
                    $select->where('clReq.date_created <= ?', $toDate);
                }
            }
            
            $row = $this->fetchRow($select);
            //Disable DB Slave
            $this->_disableDbSlave();
            return $row;
        }
        else
            return 0;
    }
    
    public function getAgentCardLoads($param){
        
        $agentId = isset($param['agent_id'])?$param['agent_id']:'';
        $productId = isset($param['product_id'])?$param['product_id']:'';
        $date = isset($param['date'])?$param['date']:'';
        $txnType = isset($param['txn_type'])?$param['txn_type']:'';
        if($date=='')
            return array();
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(DbTable::TABLE_RAT_CORP_LOAD_REQUEST." as cl",array("cl.txn_code as transaction_ref_no", "cl.amount as transaction_amount",'cl.fee as transaction_fee', 'cl.service_tax as transaction_service_tax',));
        $select->where("cl.status='".STATUS_LOADED."'");         
        $select->where("DATE(cl.date_created) ='".$date."'");         
        $select->where("cl.txn_type='".$txnType."'");         
        
        if($agentId>=1)
            $select->where('cl.by_agent_id=?',$agentId); 

        if($productId>=1)
            $select->where('cl.product_id=?',$productId); 

        $select->order('cl.date_created ASC');
        
        return $this->_db->fetchAll($select);
    }
    
}
