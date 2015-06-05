<?php

/**
 * Model that manages auth requests n reversals
 * 
 * @package Operation_Models
 * @copyright transerv
 */
class AuthRequest extends App_Model {

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
    protected $_name = DbTable::TABLE_CARD_AUTH_REQUEST;

    public $_id;
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Privilege';

    /*
     * $params['card_number'] = crn
     * $params['tid'] = tid
     * $params['mcc_code'] = mcc code
     * $params['mid'] = mid
     * $params['amount_txn'] = amount txn
     * $params['amount_billed'] = amount billed
     * $params['currency_iso'] = currency iso 356
     * $params['narration'] = narration
     * $params['txn_no'] = txn no
     * $params['mode'] = txn indicator
     * $params['rev_indicator'] = rev indicator
     * $params['original_txn_no'] = original txn no
     * 
     */
    public function authAdvice($params)
    {
        $crnMaster = new CRNMaster();
        $const = $crnMaster->getProductConst($params['card_number']);
        $params['const'] = $const;
        if ($const == PRODUCT_CONST_RAT_MEDI || $const == PRODUCT_CONST_RAT_PAT || $const == PRODUCT_CONST_RAT_COP || $const == PRODUCT_CONST_RAT_HAP || $const == PRODUCT_CONST_RAT_CNY || $const == PRODUCT_CONST_RAT_GPR || $const == PRODUCT_CONST_RAT_CTY  || $const == PRODUCT_CONST_RAT_SMP ) {
            if (strtolower($params['rev_indicator']) == 'y') {
                return $this->reversalTxnProcessing($params);
            } else {
                return $this->txnProcessing($params);
            }
        } elseif($const == PRODUCT_CONST_BOI_NSDC) {
            if (strtolower($params['rev_indicator']) == 'y') {
                return $this->reversalTxnProcessingBoiNSDC($params);
            } else {
                return $this->txnProcessingBoiNSDC($params);
            }            
        } else {
            $retArr['error_msg'] = 'Cardholder not found';
            $retArr['error_msg_code'] = ErrorCodes::ERROR_CARDHOLDER_NOT_FOUND;
            return $retArr;
        }
    }
    
    public function txnProcessing($params)
    {
        $custModel = new Corp_Ratnakar_Cardholders();
        $masterPurseModel = new MasterPurse();
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $cardloadModel = new Corp_Ratnakar_Cardload();
        $baseTxn = new BaseTxn();
        $m = new \App\Messaging\Corp\Ratnakar\Operation();
        
        $amountTxn = Util::convertToPaisa($params['amount_txn']);
        $amountBilled = Util::convertToPaisa($params['amount_billed']);
        $const = $params['const'];
        $cardholderDetails = $custModel->getCardholderInfoByCard($params['card_number'], FLAG_YES);
        $txnCode = $baseTxn->generateTxncode();
        $failedReason = '';
        $failedReasonCode = '';
        $bankUnicodeArr = Util::bankUnicodesArray();
        $bankUnicode = $bankUnicodeArr['2']; // rat
        if(!isset($cardholderDetails->id) || $cardholderDetails->id == '')
        {
            $status = STATUS_FAILED;
            $failedReason       = 'Cardholder not found';
            $failedReasonCode   = ErrorCodes::ERROR_CARDHOLDER_NOT_FOUND;
            $productId = 0;
            //$retArr['error_msg'] = $failedReason;
            //$retArr['error_msg_code'] = $failedReasonCode;            
        }
        else 
        {
           
            $productId = $cardholderDetails->product_id;
            $masterPurseDetails = $masterPurseModel->getProductPurseBasicDetails($productId, 'priority');
           
            switch($cardholderDetails->cardholder_status){
                case STATUS_ECS_PENDING:
                                         $status = STATUS_FAILED;
                                         $failedReason = 'Cardholder Registration pending with ECS';
                                         $failedReasonCode   = ErrorCodes::ERROR_CARDHOLDER_NOT_FOUND;                                         
                                         break;
                case STATUS_ECS_FAILED:
                                         $status = STATUS_FAILED;
                                         $failedReason = 'Cardholder Registration failed with ECS';
                                         $failedReasonCode   = ErrorCodes::ERROR_CARDHOLDER_NOT_FOUND;                                         
                                         break;  
                case STATUS_INACTIVE:
                                         $status = STATUS_FAILED;
                                         $failedReason = 'Cardholder Inactive';
                                         $failedReasonCode   = ErrorCodes::ERROR_CARDHOLDER_NOT_FOUND;                                         
                                         break;  
                default :
                                         $status = STATUS_PENDING;
                                         $failedReason = '';
                                         $failedReasonCode   = '';                                         
                                         break;

            }
        }
        $retArr = array('status' => $status, 'ack_no' => $txnCode, 'error_msg' => $failedReason, 'error_msg_code' => $failedReasonCode);
        $customerMasterId = isset($cardholderDetails->customer_master_id) && !empty($cardholderDetails->customer_master_id) ? $cardholderDetails->customer_master_id : 0;
        $cardHolderId = isset($cardholderDetails->id) && !empty($cardholderDetails->id) ? $cardholderDetails->id : 0;
        
        $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $CardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$params['card_number']."','".$encryptionKey."')");
            
        $data = array(
            'product_id' => $productId,
            'customer_master_id' => $customerMasterId,
            'cardholder_id' => $cardHolderId,
            'purse_master_id' => 0,
            'customer_purse_id' => 0,
            'card_number' => $CardNumber,
            'txn_type' => TXNTYPE_CORP_AUTH_TXN_PROCESSING,
            'tid' => $params['tid'],
            'mcc_code' => $params['mcc_code'],
            'mid' => $params['mid'],
            'amount_txn' => $amountTxn,
            'amount_billed' => $amountBilled,
            'currency_iso' => $params['currency_iso'],
            'narration' => $params['narration'],
            'txn_no' => $params['txn_no'],
            'mode' => strtolower($params['mode']),
            'rev_indicator' => strtolower($params['rev_indicator']),
            'original_txn_no' => $params['original_txn_no'],
            'txn_code'  => $txnCode,
            'date_created' => new Zend_Db_Expr('NOW()'),
            'status' => $status,
            'failed_reason' => $failedReason,

        );
        $this->_db->insert(DbTable::TABLE_CARD_AUTH_REQUEST, $data);
        $cardAuthRequestId = $this->_db->lastInsertId();
        $this->setId($cardAuthRequestId);
        if($status == STATUS_PENDING)
        {
            try 
            {
               foreach($masterPurseDetails as $masterPurse)
                {
                    $custPurse = $custPurseModel->getCustPurseDetails(array('rat_customer_id' => $cardholderDetails->rat_customer_id, 'purse_master_id' => $masterPurse['id']));

                    $data = array(
                        'card_auth_request_id' => $cardAuthRequestId,
                        'product_id' => $productId,
                        'customer_master_id' => $customerMasterId,
                        'cardholder_id' => $cardholderDetails->id,
                        'purse_master_id' => $masterPurse['id'],
                        'customer_purse_id' => $custPurse['id'],
                        'card_number' => $CardNumber,
                        'amount_txn' => $amountTxn,
                        'date_created' => new Zend_Db_Expr('NOW()'),
                        'status' => STATUS_PENDING,

                    );
                    $this->_db->insert(DbTable::TABLE_CARD_AUTH_REQUEST_DETAIL, $data);
                    $cardAuthRequestDetailId = $this->_db->lastInsertId();

                    $paramsBase = array( 
                        'customer_master_id' => $customerMasterId,
                        'purse_master_id'=> $masterPurse['id'],
                        'customer_purse_id'=> $custPurse['id'],
                        'amount' => $amountTxn,
                        'product_id' =>  $productId,
                        'tid' => $params['tid'],
                        'mcc_code' => $params['mcc_code'],
                        'bank_unicode' => $bankUnicode
                            );
                    $validateAuth = $baseTxn->chkAllowCardAuthAdvice($paramsBase);
                    if($validateAuth['status'] == STATUS_SUCCESS  && $params['mcc_code'] != '') {
                        $paramsMcc = array(
                            'customer_type' => $cardholderDetails->customer_type,
                            'mcc_code' => $params['mcc_code']
                                );
                        $validateAuth = $baseTxn->chkAllowMcc($paramsMcc);
                    }
                    if($validateAuth['status'] == STATUS_SUCCESS)
                    {
                        $paramsBase = array( 
                            'txn_code' => $txnCode,
                            'customer_master_id' => $customerMasterId,
                            'txn_customer_master_id' => CUSTOMER_MEDIASSIST_EXPENSE_ID,
                            'purse_master_id'=> $masterPurse['id'],
                            'customer_purse_id'=> $custPurse['id'],
                            'amount' => $amountTxn,
                            'product_id' =>  $productId,
                            'txn_type' => TXNTYPE_CORP_AUTH_TXN_PROCESSING,
                            'bank_unicode' => $bankUnicode
                                );
                        $baseTxn->cardTransaction($paramsBase);

                        $updateArr = array('status' => STATUS_COMPLETED);
                        $this->_db->update(DbTable::TABLE_CARD_AUTH_REQUEST_DETAIL, $updateArr, "id= $cardAuthRequestDetailId");

                        $retArr['status'] = STATUS_SUCCESS;
                        $purseMasterId = $masterPurse['id'];
                        $custPurseId = $custPurse['id'];
                        $purseExpiry = $masterPurse['allow_expiry'];
                        break;
                    }
                    else 
                    {
                        $updateArr = array('status' => STATUS_FAILED,
                                            'failed_reason' => $validateAuth['failed_reason']);
                        $this->_db->update(DbTable::TABLE_CARD_AUTH_REQUEST_DETAIL, $updateArr, "id= $cardAuthRequestDetailId");

                        $retArr['status'] = STATUS_FAILED;
                        $failedReason .= "Wallet ".$masterPurse['code'].": ".$validateAuth['failed_reason'].". ";
                        $failedReasonCode = isset($validateAuth['failed_reason_code']) ? $validateAuth['failed_reason_code'] : '';
                    }

                }

                if($retArr['status'] == STATUS_SUCCESS)
                {
                    $updateArr = array('status' => STATUS_COMPLETED, 'purse_master_id' => $purseMasterId,
                                            'customer_purse_id' => $custPurseId);
                    $this->_db->update(DbTable::TABLE_CARD_AUTH_REQUEST, $updateArr, "id= $cardAuthRequestId");
                    if($purseExpiry == FLAG_YES){
                        $arrTxn = array(
                            'product_id' => $productId,
                            'customer_master_id' => $customerMasterId,
                            'purse_master_id' => $purseMasterId,
                            'amount' => $amountTxn,
                            'txn_processing_id' => $cardAuthRequestId,
                            'txn_code' => $txnCode,
                        );
                        $cardloadModel->mapLoadTxn($arrTxn);	
                    }
                    $custPurse = $custPurseModel->getCustBalance($cardholderDetails->rat_customer_id);

/*                    $cardholderArray['cardNumber'] = $cardholderDetails->card_number;
                    // Get balance from ECS
                    $ecsApi = new App_Api_ECS_Transactions();
                    $res = $ecsApi->balanceInquiry($cardholderArray);
                    if($res){
                    $res = $ecsApi2->getLastResponse();
                    $custPurse['sum'] = $res->balanceInquiryList->availablebalance;
                            }
                    else{
                    $custPurse['sum'] = '';  
                            }
*/
                    if($const == PRODUCT_CONST_RAT_PAT) {
                    $userData = array('last_four' =>substr($params['card_number'], -4),
                                'product_name' => PAT_PRODUCT,
                                'amount' => $amountTxn,
                                'balance' => $custPurse['sum'],
                                'transaction_place' => trim($params['narration']),
                                'mobile' => $cardholderDetails->mobile,
                                'product_id' => $productId,
                            );
                    } elseif($const == PRODUCT_CONST_RAT_COP) {
                    $userData = array('last_four' =>substr($params['card_number'], -4),
                                'product_name' => COP_PRODUCT,
                                'amount' => $amountTxn,
                                'balance' => $custPurse['sum'],
                                'transaction_place' => trim($params['narration']),
                                'mobile' => $cardholderDetails->mobile,
                                'product_id' => $productId,
                            );
                    } elseif($const == PRODUCT_CONST_RAT_HAP) {
                    $userData = array('last_four' =>substr($params['card_number'], -4),
                                'product_name' => HAP_PRODUCT,
                                'amount' => $amountTxn,
                                'balance' => $custPurse['sum'],
                                'transaction_place' => trim($params['narration']),
                                'mobile' => $cardholderDetails->mobile,
                                'product_id' => $productId,
                            );
                    } elseif($const == PRODUCT_CONST_RAT_CNY) {
                    $userData = array('last_four' =>substr($params['card_number'], -4),
                                'product_name' => CNY_PRODUCT,
                                'amount' => $amountTxn,
                                'balance' => $custPurse['sum'],
                                'transaction_place' => trim($params['narration']),
                                'mobile' => $cardholderDetails->mobile,
                                'product_id' => $productId,
                            );
                    } elseif($const == PRODUCT_CONST_RAT_SMP) {
                    $userData = array('last_four' =>substr($params['card_number'], -4),
                                'product_name' => SHMARTMONEY_PRODUCT,
                                'amount' => $amountTxn,
                                'balance' => $custPurse['sum'],
                                'transaction_place' => trim($params['narration']),
                                'mobile' => $cardholderDetails->mobile,
                                'product_id' => $productId,
                            );
                    } else {
                        $userData = array('last_four' =>substr($params['card_number'], -4),
                                'product_name' => MEDIASSIST_PRODUCT,
                                'amount' => $amountTxn,
                                'balance' => $custPurse['sum'],
                                'transaction_place' => trim($params['narration']),
                                'mobile' => $cardholderDetails->mobile,
                                'product_id' => $productId,
                            );
                    }
                    if($const != PRODUCT_CONST_RAT_HAP) {
                        $smsMessage = $m->cardTransaction($userData,TRUE);
                        $ref = new Reference();
                        $ref->customSMSLogger(array(
                            'type' => SMS_PENDING,
                            'product_id' => $productId,
                            'txn_no' => $params['txn_no'],
                            'method' => 'CardTransaction',
                            'mobile' => $cardholderDetails->mobile,
                            'message' => $smsMessage,
                            'exception' => ''            
                        ));
                    }
                }
                else
                {
                    $updateArr = array('status' => STATUS_FAILED, 'failed_reason' => $failedReason);
                    $this->_db->update(DbTable::TABLE_CARD_AUTH_REQUEST, $updateArr, "id= $cardAuthRequestId");

                    $retArr['error_msg'] = $failedReason;
                    $retArr['error_msg_code'] = $failedReasonCode;
                }

            }catch (App_Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $updateArr = array('status' => STATUS_FAILED,
                                            'failed_reason' => $e->getMessage());
                $this->_db->update(DbTable::TABLE_CARD_AUTH_REQUEST, $updateArr, "id= $cardAuthRequestId");

                $retArr['error_msg'] = "System Error ! Please try later.";
                $retArr['error_msg_code'] = ErrorCodes::ERROR_SYSTEM_ERROR;
            } catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $updateArr = array('status' => STATUS_FAILED,
                                            'failed_reason' => $e->getMessage());
                $this->_db->update(DbTable::TABLE_CARD_AUTH_REQUEST, $updateArr, "id= $cardAuthRequestId");

                $retArr['error_msg'] = "System Error ! Please try later.";
                $retArr['error_msg_code'] = ErrorCodes::ERROR_SYSTEM_ERROR;
                
            }
        }
        return $retArr;
    }
    
    public function reversalTxnProcessing($params)
    {
        $custModel = new Corp_Ratnakar_Cardholders();
        $baseTxn = new BaseTxn();
        $amountTxn = Util::convertToPaisa($params['amount_txn']);
        $amountBilled = Util::convertToPaisa($params['amount_billed']);
        $txnDetails = $this->getTxnInfo(array('txn_no' => $params['original_txn_no'], 'amount_txn' => $amountTxn, 
                                'status' => STATUS_COMPLETED));
        if($txnDetails->id == '')
        {
            $status = STATUS_FAILED;
            $failedReason = "Original Successful Transaction with same txn no. and amount not found.";
            $failedReasonCode = "0012";
        }
        else {
            $status = STATUS_PENDING;
            $failedReason = '';
            $failedReasonCode = '';
        }
        $paramsCardholder = array('card_number' => $params['card_number'], 'status' => STATUS_ACTIVE);
        $cardholderDetails = $custModel->getCardholderInfo($paramsCardholder);
        $productId = $cardholderDetails->product_id;
        $txnCode = $baseTxn->generateTxncode();
        $bankUnicodeArr = Util::bankUnicodesArray();
        $bankUnicode = $bankUnicodeArr['2']; // rat
        switch($cardholderDetails->cardholder_status){
                case STATUS_ECS_PENDING:
                                         $status = STATUS_FAILED;
                                         $failedReason .= 'Cardholder Registration pending with ECS';
                                         $failedReasonCode   = ErrorCodes::ERROR_CARDHOLDER_NOT_FOUND;                                            
                                         break;
                case STATUS_ECS_FAILED:
                                         $status = STATUS_FAILED;
                                         $failedReason .= 'Cardholder Registration failed with ECS';
                                         $failedReasonCode   = ErrorCodes::ERROR_CARDHOLDER_NOT_FOUND;                                            
                                         break;  
                case STATUS_INACTIVE:
                                         $status = STATUS_FAILED;
                                         $failedReason .= 'Cardholder Inactive';
                                         $failedReasonCode   = ErrorCodes::ERROR_CARDHOLDER_NOT_FOUND;                                            
                                         break;  
            }

        $retArr = array('status' => $status, 'ack_no' => $txnCode, 'error_msg' => $failedReason, 'error_msg_code' => $failedReasonCode);

        $purseMasterId = (isset($txnDetails->purse_master_id) && $txnDetails->purse_master_id > 0) ? $txnDetails->purse_master_id : 0;
        $custPurseId = (isset($txnDetails->customer_purse_id) && $txnDetails->customer_purse_id > 0) ? $txnDetails->customer_purse_id : 0;
        
        $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $CardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$params['card_number']."','".$encryptionKey."')");
        
        $data = array(
            'product_id' => $productId,
            'customer_master_id' => $cardholderDetails->customer_master_id,
            'cardholder_id' => $cardholderDetails->id,
            'purse_master_id' => $purseMasterId,
            'customer_purse_id' => $custPurseId,
            'card_number' => $CardNumber,
            'txn_type' => TXNTYPE_REVERSAL_CORP_AUTH_TXN_PROCESSING,
            'tid' => $params['tid'],
            'mcc_code' => $params['mcc_code'],
            'mid' => $params['mid'],
            'amount_txn' => $amountTxn,
            'amount_billed' => $amountBilled,
            'currency_iso' => $params['currency_iso'],
            'narration' => $params['narration'],
            'txn_no' => $params['txn_no'],
            'mode' => strtolower($params['mode']),
            'rev_indicator' => strtolower($params['rev_indicator']),
            'original_txn_no' => $params['original_txn_no'],
            'txn_code'  => $txnCode,
            'date_created' => new Zend_Db_Expr('NOW()'),
            'status' => $status,
            'failed_reason' => $failedReason,

        );
        $this->_db->insert(DbTable::TABLE_CARD_AUTH_REQUEST, $data);
        $cardAuthRequestId = $this->_db->lastInsertId();
        $this->setId($cardAuthRequestId);
        if($status == STATUS_PENDING)
        {
            try 
            {
                $paramsBase = array( 
                    'txn_code' => $txnCode,
                    'customer_master_id' => $cardholderDetails->customer_master_id,
                    'txn_customer_master_id' => CUSTOMER_MEDIASSIST_EXPENSE_ID,
                    'purse_master_id'=> $purseMasterId,
                    'customer_purse_id'=> $custPurseId,
                    'amount' => $amountTxn,
                    'product_id' =>  $productId,
                    'txn_type' => TXNTYPE_REVERSAL_CORP_AUTH_TXN_PROCESSING,
                    'bank_unicode' => $bankUnicode
                        );
                $baseTxn->reversalCardTransaction($paramsBase);
                
                $updateArr = array('status' => STATUS_COMPLETED);
                $this->_db->update(DbTable::TABLE_CARD_AUTH_REQUEST, $updateArr, "id= $cardAuthRequestId");
                $updateArr = array('status' => STATUS_REVERSED, 'date_reversal' => new Zend_Db_Expr('NOW()'));
                $this->_db->update(DbTable::TABLE_CARD_AUTH_REQUEST, $updateArr, "id= $txnDetails->id");
                $retArr['status'] = STATUS_SUCCESS;

                    $ref = new Reference();
                    $ref->revertCustomSMS(array(
                        'product_id' => $productId,
                        'txn_no' => $params['original_txn_no'],
                        'mobile' => $cardholderDetails->mobile,
                    ));
                
                
            }catch (App_Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $updateArr = array('status' => STATUS_FAILED,
                                            'failed_reason' => $e->getMessage());
                $this->_db->update(DbTable::TABLE_CARD_AUTH_REQUEST, $updateArr, "id= $cardAuthRequestId");

                $retArr['error_msg'] = "System Error ! Please try later.";
                $retArr['error_msg_code'] = ErrorCodes::ERROR_SYSTEM_ERROR;                
            } catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $updateArr = array('status' => STATUS_FAILED,
                                            'failed_reason' => $e->getMessage());
                $this->_db->update(DbTable::TABLE_CARD_AUTH_REQUEST, $updateArr, "id= $cardAuthRequestId");

                $retArr['error_msg'] = "System Error ! Please try later.";
                $retArr['error_msg_code'] = ErrorCodes::ERROR_SYSTEM_ERROR;
            }
        }
    
        return $retArr;
    }
    
    public function txnProcessingBoiNSDC($params)
    {
        $custModel = new Corp_Boi_Customers();
        $masterPurseModel = new MasterPurse();
        $custPurseModel = new Corp_Boi_CustomerPurse();
        $cardloadModel = new Corp_Boi_Cardload();
        $baseTxn = new BaseTxn();
        $m = new \App\Messaging\Corp\Boi\Operation();
        
        $amountTxn = Util::convertToPaisa($params['amount_txn']);
        $amountBilled = Util::convertToPaisa($params['amount_billed']);
        $paramsCardholder = array('card_number' => $params['card_number']);
        $cardholderDetails = $custModel->getCardholderInfo($paramsCardholder);
        $txnCode = $baseTxn->generateTxncode();
        $failedReason = '';
        $failedReasonCode = '';
        if(!isset($cardholderDetails->id) || $cardholderDetails->id == '')
        {
            $status = STATUS_FAILED;
            $failedReason       = 'Cardholder not found';
            $failedReasonCode   = ErrorCodes::ERROR_CARDHOLDER_NOT_FOUND;
            $productId = 0;
        }
        else 
        {
            $productId = $cardholderDetails->product_id;
            $masterPurseDetails = $masterPurseModel->getProductPurseDetails($productId, 'priority');
            switch($cardholderDetails->cardholder_status){
                case STATUS_ECS_PENDING:
                                         $status = STATUS_FAILED;
                                         $failedReason = 'Cardholder Registration pending with ECS';
                                         $failedReasonCode   = ErrorCodes::ERROR_CARDHOLDER_NOT_FOUND;                                         
                                         break;
                case STATUS_ECS_FAILED:
                                         $status = STATUS_FAILED;
                                         $failedReason = 'Cardholder Registration failed with ECS';
                                         $failedReasonCode   = ErrorCodes::ERROR_CARDHOLDER_NOT_FOUND;                                         
                                         break;  
                case STATUS_INACTIVE:
                                         $status = STATUS_FAILED;
                                         $failedReason = 'Cardholder Inactive';
                                         $failedReasonCode   = ErrorCodes::ERROR_CARDHOLDER_NOT_FOUND;                                         
                                         break;  
                default :
                                         $status = STATUS_PENDING;
                                         $failedReason = '';
                                         $failedReasonCode   = '';                                         
                                         break;

            }
        }
        $retArr = array('status' => $status, 'ack_no' => $txnCode, 'error_msg' => $failedReason, 'error_msg_code' => $failedReasonCode);
        $customerMasterId = isset($cardholderDetails->customer_master_id) && !empty($cardholderDetails->customer_master_id) ? $cardholderDetails->customer_master_id : 0;
        $cardHolderId = isset($cardholderDetails->id) && !empty($cardholderDetails->id) ? $cardholderDetails->id : 0;
        
        $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $CardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$params['card_number']."','".$encryptionKey."')");
        
        $data = array(
            'product_id' => $productId,
            'customer_master_id' => $customerMasterId,
            'cardholder_id' => $cardHolderId,
            'purse_master_id' => 0,
            'customer_purse_id' => 0,
            'card_number' => $CardNumber,
            'txn_type' => TXNTYPE_CORP_AUTH_TXN_PROCESSING,
            'tid' => $params['tid'],
            'mcc_code' => $params['mcc_code'],
            'mid' => $params['mid'],
            'amount_txn' => $amountTxn,
            'amount_billed' => $amountBilled,
            'currency_iso' => $params['currency_iso'],
            'narration' => $params['narration'],
            'txn_no' => $params['txn_no'],
            'mode' => strtolower($params['mode']),
            'rev_indicator' => strtolower($params['rev_indicator']),
            'original_txn_no' => $params['original_txn_no'],
            'txn_code'  => $txnCode,
            'date_created' => new Zend_Db_Expr('NOW()'),
            'status' => $status,
            'failed_reason' => $failedReason,

        );
        $this->_db->insert(DbTable::TABLE_CARD_AUTH_REQUEST, $data);
        $cardAuthRequestId = $this->_db->lastInsertId();
        $this->setId($cardAuthRequestId);
        if($status == STATUS_PENDING)
        {
            try 
            {
                foreach($masterPurseDetails as $masterPurse)
                {
                    $custPurse = $custPurseModel->getCustPurseDetails(array('boi_customer_id' => $cardholderDetails->boi_customer_id, 'purse_master_id' => $masterPurse['id']));
                    $data = array(
                        'card_auth_request_id' => $cardAuthRequestId,
                        'product_id' => $productId,
                        'customer_master_id' => $customerMasterId,
                        'cardholder_id' => $cardholderDetails->id,
                        'purse_master_id' => $masterPurse['id'],
                        'customer_purse_id' => $custPurse['id'],
                        'card_number' => $CardNumber,
                        'amount_txn' => $amountTxn,
                        'date_created' => new Zend_Db_Expr('NOW()'),
                        'status' => STATUS_PENDING,

                    );
                    $this->_db->insert(DbTable::TABLE_CARD_AUTH_REQUEST_DETAIL, $data);
                    $cardAuthRequestDetailId = $this->_db->lastInsertId();
                    $bankUnicodeArr = Util::bankUnicodesArray();
                    $bankUnicode = $bankUnicodeArr['1']; // boi
                    $paramsBase = array( 
                        'customer_master_id' => $customerMasterId,
                        'purse_master_id'=> $masterPurse['id'],
                        'customer_purse_id'=> $custPurse['id'],
                        'amount' => $amountTxn,
                        'product_id' =>  $productId,
                        'tid' => $params['tid'],
                        'mcc_code' => $params['mcc_code'],
                        'bank_unicode' => $bankUnicode
                            );
                    
                    $validateAuth = $baseTxn->chkAllowCardAuthAdvice($paramsBase);

                    if($validateAuth['status'] == STATUS_SUCCESS  && $params['mcc_code'] != '') {
                        $paramsMcc = array(
                            'customer_type' => '',//Sending blank as in BOI NSDC customer type does not exsits
                            'mcc_code' => $params['mcc_code']
                                );
                        $validateAuth = $baseTxn->chkAllowMcc($paramsMcc);
                    }
                    if($validateAuth['status'] == STATUS_SUCCESS)
                    {
                        $paramsBase = array( 
                            'txn_code' => $txnCode,
                            'customer_master_id' => $customerMasterId,
                            'txn_customer_master_id' => CUSTOMER_BOI_EXPENSE_ID,
                            'purse_master_id'=> $masterPurse['id'],
                            'customer_purse_id'=> $custPurse['id'],
                            'amount' => $amountTxn,
                            'product_id' =>  $productId,
                            'txn_type' => TXNTYPE_CORP_AUTH_TXN_PROCESSING,
                            'bank_unicode' => $bankUnicode
                                );
                        $baseTxn->cardTransaction($paramsBase);
                        
                        
                        $updateArr = array('status' => STATUS_COMPLETED);
                        $this->_db->update(DbTable::TABLE_CARD_AUTH_REQUEST_DETAIL, $updateArr, "id= $cardAuthRequestDetailId");

                        $retArr['status'] = STATUS_SUCCESS;
                        $purseMasterId = $masterPurse['id'];
                        $custPurseId = $custPurse['id'];
                        break;
                    }
                    else 
                    {
                        $updateArr = array('status' => STATUS_FAILED,
                                            'failed_reason' => $validateAuth['failed_reason']);
                        $this->_db->update(DbTable::TABLE_CARD_AUTH_REQUEST_DETAIL, $updateArr, "id= $cardAuthRequestDetailId");

                        $retArr['status'] = STATUS_FAILED;
                        $failedReason .= "Wallet ".$masterPurse['code'].": ".$validateAuth['failed_reason'].". ";
                        $failedReasonCode = isset($validateAuth['failed_reason_code']) ? $validateAuth['failed_reason_code'] : '';
                    }

                }

                if($retArr['status'] == STATUS_SUCCESS)
                {
                    $updateArr = array('status' => STATUS_COMPLETED, 'purse_master_id' => $purseMasterId,
                                            'customer_purse_id' => $custPurseId);
                    $this->_db->update(DbTable::TABLE_CARD_AUTH_REQUEST, $updateArr, "id= $cardAuthRequestId");
                    
                    $arrTxn = array(
                        'product_id' => $productId,
                        'customer_master_id' => $customerMasterId,
                        'purse_master_id' => $purseMasterId,
                        'amount' => $amountTxn,
                        'txn_processing_id' => $cardAuthRequestId,
                        'txn_code' => $txnCode,
                    );
                    $cardloadModel->mapLoadTxn($arrTxn);
                    
                    $custPurse = $custPurseModel->getCustBalance($cardholderDetails->boi_customer_id);
                    $userData = array('last_four' =>substr($cardholderDetails->account_no, -4),
                                'product_name' => BOI_NSDC_PRODUCT_WALLET,
                                'amount' => $amountTxn,
                                'balance' => $custPurse['sum'],
                                'transaction_place' => trim($params['narration']),
                                'mobile' => $cardholderDetails->mobile
                            );
                    //$resp = $m->cardTransaction($userData);
                    $smsMessage = $m->cardTransaction($userData,TRUE);
                    $ref = new Reference();
                    $ref->customSMSLogger(array(
                        'type' => SMS_PENDING,
                        'product_id' => $productId,
                        'txn_no' => $params['txn_no'],
                        'method' => 'CardTransaction',
                        'mobile' => $cardholderDetails->mobile,
                        'message' => $smsMessage,
                        'exception' => ''            
                    ));                    
                }
                else
                {
                    $updateArr = array('status' => STATUS_FAILED, 'failed_reason' => $failedReason);
                    $this->_db->update(DbTable::TABLE_CARD_AUTH_REQUEST, $updateArr, "id= $cardAuthRequestId");

                    $retArr['error_msg'] = $failedReason;
                    $retArr['error_msg_code'] = $failedReasonCode;
                }

            }catch (App_Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $updateArr = array('status' => STATUS_FAILED,
                                            'failed_reason' => $e->getMessage());
                $this->_db->update(DbTable::TABLE_CARD_AUTH_REQUEST, $updateArr, "id= $cardAuthRequestId");

                $retArr['error_msg'] = "System Error ! Please try later.";
                $retArr['error_msg_code'] = ErrorCodes::ERROR_SYSTEM_ERROR;
            } catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $updateArr = array('status' => STATUS_FAILED,
                                            'failed_reason' => $e->getMessage());
                $this->_db->update(DbTable::TABLE_CARD_AUTH_REQUEST, $updateArr, "id= $cardAuthRequestId");

                $retArr['error_msg'] = "System Error ! Please try later.";
                $retArr['error_msg_code'] = ErrorCodes::ERROR_SYSTEM_ERROR;
                
            }
        }
        return $retArr;
    }
    
    public function reversalTxnProcessingBoiNSDC($params)
    {
        $custModel = new Corp_Boi_Customers();
        $baseTxn = new BaseTxn();
        $amountTxn = Util::convertToPaisa($params['amount_txn']);
        $amountBilled = Util::convertToPaisa($params['amount_billed']);
        $txnDetails = $this->getTxnInfo(array('txn_no' => $params['original_txn_no'], 'amount_txn' => $amountTxn, 
                                'status' => STATUS_COMPLETED));
        if($txnDetails->id == '')
        {
            $status = STATUS_FAILED;
            $failedReason = "Original Successful Transaction with same txn no. and amount not found.";
            $failedReasonCode = "0012";
        }
        else {
            $status = STATUS_PENDING;
            $failedReason = '';
            $failedReasonCode = '';
        }
        $paramsCardholder = array('card_number' => $params['card_number']);
        $cardholderDetails = $custModel->getCardholderInfo($paramsCardholder);
        $productId = $cardholderDetails->product_id;
        $txnCode = $baseTxn->generateTxncode();

        switch($cardholderDetails->cardholder_status){
                case STATUS_ECS_PENDING:
                                         $status = STATUS_FAILED;
                                         $failedReason .= 'Cardholder Registration pending with ECS';
                                         $failedReasonCode   = ErrorCodes::ERROR_CARDHOLDER_NOT_FOUND;                                            
                                         break;
                case STATUS_ECS_FAILED:
                                         $status = STATUS_FAILED;
                                         $failedReason .= 'Cardholder Registration failed with ECS';
                                         $failedReasonCode   = ErrorCodes::ERROR_CARDHOLDER_NOT_FOUND;                                            
                                         break;  
                case STATUS_INACTIVE:
                                         $status = STATUS_FAILED;
                                         $failedReason .= 'Cardholder Inactive';
                                         $failedReasonCode   = ErrorCodes::ERROR_CARDHOLDER_NOT_FOUND;                                            
                                         break;  
            }

        $retArr = array('status' => $status, 'ack_no' => $txnCode, 'error_msg' => $failedReason, 'error_msg_code' => $failedReasonCode);

        $purseMasterId = (isset($txnDetails->purse_master_id) && $txnDetails->purse_master_id > 0) ? $txnDetails->purse_master_id : 0;
        $custPurseId = (isset($txnDetails->customer_purse_id) && $txnDetails->customer_purse_id > 0) ? $txnDetails->customer_purse_id : 0;
        
        $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $CardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$params['card_number']."','".$encryptionKey."')");
        
        $data = array(
            'product_id' => $productId,
            'customer_master_id' => $cardholderDetails->customer_master_id,
            'cardholder_id' => $cardholderDetails->id,
            'purse_master_id' => $purseMasterId,
            'customer_purse_id' => $custPurseId,
            'card_number' => $CardNumber,
            'txn_type' => TXNTYPE_REVERSAL_CORP_AUTH_TXN_PROCESSING,
            'tid' => $params['tid'],
            'mcc_code' => $params['mcc_code'],
            'mid' => $params['mid'],
            'amount_txn' => $amountTxn,
            'amount_billed' => $amountBilled,
            'currency_iso' => $params['currency_iso'],
            'narration' => $params['narration'],
            'txn_no' => $params['txn_no'],
            'mode' => strtolower($params['mode']),
            'rev_indicator' => strtolower($params['rev_indicator']),
            'original_txn_no' => $params['original_txn_no'],
            'txn_code'  => $txnCode,
            'date_created' => new Zend_Db_Expr('NOW()'),
            'status' => $status,
            'failed_reason' => $failedReason,

        );
        $this->_db->insert(DbTable::TABLE_CARD_AUTH_REQUEST, $data);
        $cardAuthRequestId = $this->_db->lastInsertId();
        $this->setId($cardAuthRequestId);
        if($status == STATUS_PENDING)
        {
            $bankUnicodeArr = Util::bankUnicodesArray();
            $bankUnicode = $bankUnicodeArr['1']; // boi
            try 
            {
                $bankUnicodeArr = Util::bankUnicodesArray();
                $bankUnicode = $bankUnicodeArr['1']; // boi
                $paramsBase = array( 
                    'txn_code' => $txnCode,
                    'customer_master_id' => $cardholderDetails->customer_master_id,
                    'txn_customer_master_id' => CUSTOMER_BOI_EXPENSE_ID,
                    'purse_master_id'=> $purseMasterId,
                    'customer_purse_id'=> $custPurseId,
                    'amount' => $amountTxn,
                    'product_id' =>  $productId,
                    'txn_type' => TXNTYPE_REVERSAL_CORP_AUTH_TXN_PROCESSING,
                    'bank_unicode' => $bankUnicode
                        );
                $baseTxn->reversalCardTransaction($paramsBase);
                
                $updateArr = array('status' => STATUS_COMPLETED);
                $this->_db->update(DbTable::TABLE_CARD_AUTH_REQUEST, $updateArr, "id= $cardAuthRequestId");
                $updateArr = array('status' => STATUS_REVERSED, 'date_reversal' => new Zend_Db_Expr('NOW()'));
                $this->_db->update(DbTable::TABLE_CARD_AUTH_REQUEST, $updateArr, "id= $txnDetails->id");
                $retArr['status'] = STATUS_SUCCESS;
                
                $ref = new Reference();
                $ref->revertCustomSMS(array(
                    'product_id' => $productId,
                    'txn_no' => $params['original_txn_no'],
                    'mobile' => $cardholderDetails->mobile,
                ));
                
                
            }catch (App_Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $updateArr = array('status' => STATUS_FAILED,
                                            'failed_reason' => $e->getMessage());
                $this->_db->update(DbTable::TABLE_CARD_AUTH_REQUEST, $updateArr, "id= $cardAuthRequestId");

                $retArr['error_msg'] = "System Error ! Please try later.";
                $retArr['error_msg_code'] = ErrorCodes::ERROR_SYSTEM_ERROR;                
            } catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $updateArr = array('status' => STATUS_FAILED,
                                            'failed_reason' => $e->getMessage());
                $this->_db->update(DbTable::TABLE_CARD_AUTH_REQUEST, $updateArr, "id= $cardAuthRequestId");

                $retArr['error_msg'] = "System Error ! Please try later.";
                $retArr['error_msg_code'] = ErrorCodes::ERROR_SYSTEM_ERROR;
            }
        }
    
        return $retArr;
    }
    /*
     * setId
     */
    public function setId( $id = 0) {
        $this->_id = $id;
    }
    
    /*
     * getId
     */
    public function getId() {
        if(isset($this->_id) && $this->_id != '') {
            return $this->_id;
        }
        else {
            return 0;
        }
    }
    
    public function getTxnInfo($param) {
        $select = $this->select();
        if ($param['txn_no'] != ''){
            $select->where("txn_no = '" . $param['txn_no'] . "'");
        }
        if ($param['amount_txn'] != ''){
            $select->where("amount_txn = '" . $param['amount_txn'] . "'");
        }
        if ($param['status'] != ''){
            $select->where("status = '" . $param['status'] . "'");
        }
        return $this->fetchRow($select);
    }

   /*
     * Stats Daily
     */

    public function getStatsDaily($customerPurseId, $curDate, $statusStr, $txnType = '') {
        $select = $this->select()
                ->from(DbTable::TABLE_CARD_AUTH_REQUEST, array('count(*) as count', 'sum(amount_txn) as total'))
                ->where('customer_purse_id=?', $customerPurseId);
        if ($statusStr != '') {
            $select->where("status IN ($statusStr)");
        }
         if($txnType != '') {
            $select->where("txn_type = ?", $txnType);
        }
        $select->where("DATE(date_created) = '" . $curDate . "'")
                ->group("customer_purse_id");
        return $this->fetchRow($select);
    }
    /*
     * Stats Duration
     */

    public function getStatsDuration($customerPurseId, $startDate, $endDate, $statusStr, $txnType = '') {
        $select = $this->select()
                ->from(DbTable::TABLE_CARD_AUTH_REQUEST, array('count(*) as count', 'sum(amount_txn) as total'))
                ->where('customer_purse_id=?', $customerPurseId);
        if ($statusStr != '') {
            $select->where("status IN ($statusStr)");
        }
        if($txnType != '') {
            $select->where("txn_type = ?", $txnType);
        }
        $select->where("DATE(date_created) BETWEEN '" . $startDate . "' AND '" . $endDate . "'")
                ->group("customer_purse_id");
        return $this->fetchRow($select);
    } 
    
    public function getSuccessfulTransactionForScheduler($productId)
    {
        $allowedSessionInMin = App_DI_Container::get('ConfigObject')->system->transaction_intimation->send_time;         
        $select = $this->select()
                ->where('product_id=?', $productId)
                ->where('status_ack=?', 'n')
                ->where('rev_indicator=?', 'n')
                ->where('status=?', STATUS_COMPLETED)
                ->where("now() >= " . new Zend_Db_Expr('DATE_ADD(date_created,INTERVAL '.$allowedSessionInMin.' SECOND)')  )
                ->order("date_created desc");                
        
        return $this->fetchAll($select);        
        
    }
    
    public function updateAckStatus($id, $status)
    {
        return $this->update(array(
                    'status_ack'    => $status
                ), "id=$id");
    }

    public function getReversedTxn($params) {
       
        $select = $this->select()
                ->from(DbTable::TABLE_CARD_AUTH_REQUEST, array('count(*) as count', 'sum(amount_txn) as reversed_total'))
                ->where('purse_master_id=?', $params['purse_master_id'])
                ->where('product_id=?', $params['product_id'])
                ->where("status =?",STATUS_COMPLETED)
                ->where("txn_type =?",TXNTYPE_REVERSAL_CORP_AUTH_TXN_PROCESSING)
                ->where("date_created >= '" . $params['from'] . "'")
                ->where("date_created <= '" . $params['to'] . "'")
                ->group("purse_master_id");
        return $this->fetchRow($select);
    }
    
    public function getPaytronicCompletedTxn($params) {
      $purseMasterId = isset($params['purse_master_id']) ? $params['purse_master_id'] : '';
        $select = $this->select()
                ->from(DbTable::TABLE_CARD_AUTH_REQUEST, array('count(*) as count', 'sum(amount_txn) as amount'));
        if($purseMasterId != ''){
                $select->where('purse_master_id=?', $params['purse_master_id']);
        }
        $select->where('product_id=?', $params['product_id'])
                ->where("status IN ('".STATUS_COMPLETED."','".STATUS_REVERSED."')")
                ->where("txn_type =?",TXNTYPE_CARD_RELOAD)
                ->where("date_created >= '" . $params['from'] . "'")
                ->where("date_created <= '" . $params['to'] . "'");
                if($purseMasterId != ''){       
        $select->group("purse_master_id");
        
        }
        else{
           $select->group("product_id"); 
        }
        
        return $this->fetchRow($select);
    }
    
     public function getPaytronicReversedTxn($params) {
        $purseMasterId = isset($params['purse_master_id']) ? $params['purse_master_id'] : '';
        $select = $this->select()
                ->from(DbTable::TABLE_CARD_AUTH_REQUEST, array('count(*) as count', 'sum(amount_txn) as reversed_total'));
        if($purseMasterId != ''){
                $select->where('purse_master_id=?', $params['purse_master_id']);
        }
        $select->where('product_id=?', $params['product_id'])
                ->where("status =?",STATUS_COMPLETED)
                ->where("txn_type =?",TXNTYPE_REVERSAL_LOAD)
                ->where("date_created >= '" . $params['from'] . "'")
                ->where("date_created <= '" . $params['to'] . "'");
        if($purseMasterId != ''){       
        $select->group("purse_master_id");
        
        }
        else{
           $select->group("product_id"); 
        }
        return $this->fetchRow($select);
    }
    
    public function getCompletedTxn($params) {
        $select = $this->select()
                ->from(DbTable::TABLE_CARD_AUTH_REQUEST, array('count(*) as count', 'sum(amount_txn) as completed_total','*'))
                ->where('purse_master_id=?', $params['purse_master_id'])
                ->where('product_id=?', $params['product_id'])
                ->where("status IN ('".STATUS_COMPLETED."','".STATUS_REVERSED."')")
                ->where("txn_type =?",TXNTYPE_CORP_AUTH_TXN_PROCESSING)
                ->where("date_created >= '" . $params['from'] . "'")
                ->where("date_created <= '" . $params['to'] . "'")
                ->group("purse_master_id");
        return $this->fetchRow($select);
    }
    public function getAllPaytronicCompletedTxn($params) {
        $select = $this->select()
                ->from(DbTable::TABLE_CARD_AUTH_REQUEST, array('*', 'amount_txn as amount'));
       
        $select->where('product_id=?', $params['product_id'])
                ->where("status IN ('".STATUS_COMPLETED."','".STATUS_REVERSED."')")
                ->where("txn_type =?",TXNTYPE_CORP_AUTH_TXN_PROCESSING)
                ->where("date_created >= '" . $params['from'] . "'")
                ->where("date_created <= '" . $params['to'] . "'");
        
        return $this->fetchAll($select);
    }
    
     public function getAllPaytronicReversedTxn($params) {
        $select = $this->select()
                ->from(DbTable::TABLE_CARD_AUTH_REQUEST, array('*', 'amount_txn as reversed'));
        $select->where('product_id=?', $params['product_id'])
                ->where("status =?",STATUS_COMPLETED)
                ->where("txn_type =?",TXNTYPE_REVERSAL_CORP_AUTH_TXN_PROCESSING)
                ->where("date_created >= '" . $params['from'] . "'")
                ->where("date_created <= '" . $params['to'] . "'");
        
        return $this->fetchAll($select);
    }
    
    /*
     * Stats Daily
     */

    public function getCustomerProductStatsDaily($customerMasterId, $productId, $curDate, $statusStr, $txnType = '') {
        $select = $this->select()
                ->from(DbTable::TABLE_CARD_AUTH_REQUEST, array('count(*) as count', 'sum(amount_txn) as total'))
                ->where('customer_master_id=?', $customerMasterId)
                ->where('product_id=?', $productId);
        if ($statusStr != '') {
            $select->where("status IN ($statusStr)");
        }
        if($txnType != '') {
            $select->where("txn_type = ?", $txnType);
        }
        $select->where("DATE(date_created) = '" . $curDate . "'")
                ->group("customer_master_id");
        return $this->fetchRow($select);
    }
    /*
     * Stats Duration
     */

    public function getCustomerProductStatsDuration($customerMasterId, $productId, $startDate, $endDate, $statusStr, $txnType = '') {
        $select = $this->select()
                ->from(DbTable::TABLE_CARD_AUTH_REQUEST, array('count(*) as count', 'sum(amount_txn) as total'))
                ->where('customer_master_id=?', $customerMasterId)
                ->where('product_id=?', $productId);
        if ($statusStr != '') {
            $select->where("status IN ($statusStr)");
        }
        if($txnType != '') {
            $select->where("txn_type = ?", $txnType);
        }
        $select->where("DATE(date_created) BETWEEN '" . $startDate . "' AND '" . $endDate . "'")
                ->group("customer_master_id");
        return $this->fetchRow($select);
    } 
    
     public function getCompletedTransactions($params) {
        //Enable DB Slave
        $this->_enableDbSlave();
        $select = $this->select()
                ->from(DbTable::TABLE_CARD_AUTH_REQUEST, array('count(*) as count', 'sum(amount_txn) as completed_total'))
                ->where('customer_purse_id=?', $params['customer_purse_id'])
                ->where('product_id=?', $params['product_id'])
                ->where("status IN ('".STATUS_COMPLETED."','".STATUS_REVERSED."')")
                ->where("txn_type =?",TXNTYPE_CORP_AUTH_TXN_PROCESSING)
                ->where("date_created >= '" . $params['from'] . "'")
                ->where("date_created <= '" . $params['to'] . "'")
                ->group("customer_purse_id");
        $row = $this->fetchRow($select);
        //Disable DB Slave
        $this->_disableDbSlave();
        return $row;
    }
     public function getReversedTransactions($params) {
       //Enable DB Slave
        $this->_enableDbSlave();
        $select = $this->select()
                ->from(DbTable::TABLE_CARD_AUTH_REQUEST, array('count(*) as count', 'sum(amount_txn) as reversed_total'))
                ->where('customer_purse_id=?', $params['customer_purse_id'])
                ->where('product_id=?', $params['product_id'])
                ->where("status =?",STATUS_COMPLETED)
                ->where("txn_type =?",TXNTYPE_REVERSAL_CORP_AUTH_TXN_PROCESSING)
                ->where("date_created >= '" . $params['from'] . "'")
                ->where("date_created <= '" . $params['to'] . "'")
                ->group("customer_purse_id");
        $row = $this->fetchRow($select);
        //Disable DB Slave
        $this->_disableDbSlave();
        return $row;
    }
    /*
     * getCustomerBankStatsDaily : getting records ob bank basis
     */
    public function getCustomerBankStatsDaily($customerMasterId, $bankId, $curDate, $statusStr) {
        $select = $this->select()
                ->from(DbTable::TABLE_CARD_AUTH_REQUEST, array('count(*) as count', 'sum(amount_txn) as total'))
                ->where('customer_master_id=?', $customerMasterId)
                ->where('bank_id=?', $bankId);
        if ($statusStr != '') {
            $select->where("status IN ($statusStr)");
        }
        $select->where("DATE(date_created) = '" . $curDate . "'")
                ->group("customer_master_id");
        return $this->fetchRow($select);
    }
    
    /*
     * getCustomerBankStatsDuration : getting records ob bank basis
     */
     public function getCustomerBankStatsDuration($customerMasterId, $bankId, $startDate, $endDate, $statusStr) {
        $select = $this->select()
                ->from(DbTable::TABLE_CARD_AUTH_REQUEST, array('count(*) as count', 'sum(amount_txn) as total'))
                ->where('customer_master_id=?', $customerMasterId)
                ->where('bank_id=?', $bankId);
        if ($statusStr != '') {
            $select->where("status IN ($statusStr)");
        }
        $select->where("DATE(date_created) BETWEEN '" . $startDate . "' AND '" . $endDate . "'")
                ->group("customer_master_id");
        return $this->fetchRow($select);
    }
    
    public function getProductCompletedTxn($params) {
        $date = isset($params['date']) ? $params['date'] : '';
        $from = isset($params['from']) ? $params['from'] : '';
        $to = isset($params['to']) ? $params['to'] : '';
        $wallet_type = isset($params['wallet_type']) ? $params['wallet_type']:'';
        $select = $this->select()
                ->from(DbTable::TABLE_CARD_AUTH_REQUEST. " as auth", array('count(*) as count', 'sum(amount_txn) as total'))
                ->join(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id = auth.purse_master_id ",array());
       
        $select->where('auth.product_id=?', $params['product_id'])
                ->where("auth.status IN ('".STATUS_COMPLETED."','".STATUS_REVERSED."')")
                ->where("auth.txn_type =?",TXNTYPE_CORP_AUTH_TXN_PROCESSING);
        if ($date) {
            $select->where('DATE(auth.date_created) =?', $date);
        } else if ($to != '' && $from != '') {
            $select->where("auth.date_created BETWEEN '$from' AND '$to'");
        }        
        if (!empty($wallet_type)) {
            $select->where("pm.is_virtual = ? " , $wallet_type);
        }        
        return $this->fetchRow($select);
    }

    public function getProductReversedTxn($params) {
        $date = isset($params['date']) ? $params['date'] : '';
        $from = isset($params['from']) ? $params['from'] : '';
        $to = isset($params['to']) ? $params['to'] : '';
        $wallet_type = isset($params['wallet_type']) ? $params['wallet_type']:'';
        $select = $this->select()
                ->from(DbTable::TABLE_CARD_AUTH_REQUEST. " as auth", array('count(*) as count', 'sum(amount_txn) as reversed_total'))
                ->join(DbTable::TABLE_PURSE_MASTER. " as pm", "pm.id = auth.purse_master_id ",array());
        $select->where('auth.product_id=?', $params['product_id'])
                ->where("auth.status =?",STATUS_COMPLETED)
                ->where("auth.txn_type =?",TXNTYPE_REVERSAL_CORP_AUTH_TXN_PROCESSING);
        if ($date) {
            $select->where('DATE(auth.date_created) =?', $date);
        } else if ($to != '' && $from != '') {
            $select->where("auth.date_created BETWEEN '$from' AND '$to'");
        }
        if (!empty($wallet_type)) {
            $select->where("pm.is_virtual = ? " , $wallet_type);
        }
        return $this->fetchRow($select);
    }
    
    public function getCatpRatpTransactions($params) {
        //Enable DB Slave
        $this->_enableDbSlave();
        $select = $this->select()
                ->from(DbTable::TABLE_CARD_AUTH_REQUEST, array('txn_type', 'sum(amount_txn) as amount'))
                ->where('customer_purse_id=?', $params['customer_purse_id'])
                ->where('product_id=?', $params['product_id'])
                ->where("status IN ('".STATUS_COMPLETED."','".STATUS_REVERSED."')")
                ->where("txn_type IN ('".TXNTYPE_CORP_AUTH_TXN_PROCESSING."', '".TXNTYPE_REVERSAL_CORP_AUTH_TXN_PROCESSING."')")
                ->where("date(date_created) = '" . $params['date'] . "'")
                ->group("customer_purse_id")
                ->group("txn_type")
                ->limit(2);
        $row = $this->fetchAll($select);
        //Disable DB Slave
        $this->_disableDbSlave();
        $arr = array('txn_cr' => 0, 'txn_dr' => 0);
        if($row) {
        foreach($row as $val){
                if($val['txn_type'] == TXNTYPE_CORP_AUTH_TXN_PROCESSING) {
                    $arr['txn_dr'] = $val['amount'];
                } else {
                    $arr['txn_cr'] = $val['amount'];
                }
           }
        }
        return $arr;
    }
}
