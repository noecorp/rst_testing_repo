<?php

/**
 * Model that manages the BLOCK_AMOUNT
 *
 * @package Operation_Models
 * @copyright transerv
 */
class Corp_Ratnakar_BlockAmount extends Corp_Ratnakar {

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
    protected $_name = DbTable::TABLE_BLOCK_AMOUNT;

    /*
     * 
     */

    public function getBlockDetail($params) {

	$txnCode = isset($params['txn_code']) ? $params['txn_code'] : 0;
	$amount = isset($params['amount']) ? $params['amount'] : 0;
	$select = $this->_db->select();
	$select->from(DbTable::TABLE_BLOCK_AMOUNT, array('id', 'customer_master_id', 'customer_purse_id', 'amount', 'txn_type', 'txn_code', 'narration', 'status'));
	if ($txnCode > 0) {
	    $select->where("txn_code = ?", $txnCode);
	}
	if ($amount > 0) {
	    $select->where("amount = ?", Util::convertToRupee($amount));
	}
	$row = $this->_db->fetchRow($select);
	if ($row) {
	    return $row;
	} else {
	    return FALSE;
	}
    }

    public function doWalletBlockAmount($params) {
	$masterPurseObj = new MasterPurse();
	$purseObj = new Corp_Ratnakar_CustomerPurse();
	$valObj = new Validator_Ratnakar_WalletTransfer();

	$pursecode = $params['wallet_code'];
	$ratCustomerId = $params['rat_customer_id'];
	$amount = Util::convertToRupee($params['amount']);

	// Validate Customer wallet
	$purseMasterDetails = $masterPurseObj->getPurseIdByPurseCode($pursecode);
	if ($ratCustomerId > 0) {
	    if (!empty($purseMasterDetails)) {
		$purseDetails = $purseObj->getCustPurseDetails(array(
		    'rat_customer_id' => $ratCustomerId,
		    'purse_master_id' => $purseMasterDetails['id']
		));
		$customerPurseId = (isset($purseDetails['id']) && $purseDetails['id'] > 0) ? $purseDetails['id'] : 0;
	    } else {
		throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_WALLET_CODE_CODE);
	    }
	}

	$params['customer_purse_id'] = $customerPurseId;
	$params['bank_id'] = $purseMasterDetails['bank_id'];
	$flg = $valObj->chkAvailableCustBalance($ratCustomerId, $purseMasterDetails['id'], $amount);

	// Block Amount in Purse
	if ($flg) {
	    if (($params['txn_type'] == TXNTYPE_WALLET_TOWALLET_FUND_TRANSFER) || ($params['txn_type'] == TXNTYPE_CARD_DEBIT)) {
		return $this->blockAmount($params);
	    }
	}
    }

    /*
     * Block the Amount 
     */

    public function blockAmount($params) {
	$this->_db->beginTransaction();
	try {
	    // Insert in block Amount 
	    $insertArr = array(
		'bank_id' => $params['bank_id'],
		'product_id' => $params['product_id'],
		'customer_master_id' => $params['customer_master_id'],
		'customer_purse_id' => $params['customer_purse_id'],
		'amount' => Util::convertToRupee($params['amount']),
		'txn_type' => $params['txn_type'],
		'narration' => $params['narration'],
		'status' => STATUS_BLOCKED,
		'date_created' => new Zend_Db_Expr('NOW()')
	    );
	    $this->_db->insert(DbTable::TABLE_BLOCK_AMOUNT, $insertArr);
	    $txnCode = $this->_db->lastInsertId(DbTable::TABLE_BLOCK_AMOUNT, 'id');
	    $this->update(array('txn_code' => $txnCode), "id='" . $txnCode . "'");

	    // ==> update in rat_ purse
	    $updArr = array(
		'block_amount' => new Zend_Db_Expr("block_amount+" . Util::convertToRupee($params['amount'])),
		'date_updated' => new Zend_Db_Expr('NOW()')
	    );
	    $where = "id = '" . $params['customer_purse_id'] . "'";
	    $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);

	    $this->_db->commit();
	    return $txnCode;
	} catch (Exception $e) {
	    App_Logger::log($e->getMessage(), Zend_Log::ERR);
	    $this->_db->rollBack();
	    throw new Exception($e->getMessage());
	}
    }

    /*
     * Check Valid txnCode
     */

    public function chkTxnCodeStatus($param) {
	$txnCode = isset($param['txn_code']) ? $param['txn_code'] : 0;
	$status = isset($param['status']) ? $param['status'] : '';
	if ($txnCode == 0) {
	    return FALSE;
	}

	$select = $this->_db->select();
	$select->from(DbTable::TABLE_BLOCK_AMOUNT, array("txn_code"));
	$select->where("txn_code = ?", $txnCode);
	if (!empty($status)) {
	    $select->where("status = ?", $status);
	}
	$row = $this->_db->fetchRow($select);
	if (!empty($row)) {
	    return TRUE;
	} else {
	    return FALSE;
	}
    }

    /*
     * Wallet Unblock AMount
     */

    public function doWalletUnBlockAmount($params) {

	// fetch detail
	$blockDetail = $this->getBlockDetail($params);
	if ($blockDetail) {
	    $updArrBlock = array(
		'status' => STATUS_UNBLOCKED,
		'date_unblocked' => new Zend_Db_Expr('NOW()')
	    );
	    $this->update($updArrBlock, "id = " . $blockDetail['id']);

	    // ==> update in 
	    $updArr = array(
		'block_amount' => new Zend_Db_Expr("block_amount - " . $blockDetail['amount']),
		'date_updated' => new Zend_Db_Expr('NOW()')
	    );
	    $where = "id = '" . $blockDetail['customer_purse_id'] . "'";
	    $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);

	    return $params['txn_code'];
	} else {
	    return FALSE;
	}
    }

    /*
     * Wallet Claim Amount
     */

    public function doWalletClaimAmount($params) {

	// fetch detail
	$blockDetail = $this->getBlockDetail($params);
	if ($blockDetail) {
	    $updArrBlock = array(
		'status'	    =>	STATUS_CLAIMED,
		'date_unblocked'    =>	new Zend_Db_Expr('NOW()'),
		'claim_txn_code'    =>	$params['claim_txn_code']
	    );
	    $this->_db->update(DbTable::TABLE_BLOCK_AMOUNT, $updArrBlock, "id = " . $blockDetail['id']);
	    // ==> update in 
	    $updArr = array(
		'block_amount'	    =>	new Zend_Db_Expr("block_amount - " . $blockDetail['amount']),
		'date_updated'	    =>	new Zend_Db_Expr('NOW()')
	    );
	    $where = "id = '" . $blockDetail['customer_purse_id'] . "'";
	    $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);

	    return $params['txn_code'];
	} else {
	    return FALSE;
	}
    }

    public function getBlockAmtList() {

	$sql = $this->_db->select();
	$sql->from(DbTable::TABLE_BLOCK_AMOUNT . ' as b', array('txn_code', 'date_created'));
	$sql->join(DbTable::TABLE_RAT_CUSTOMER_PURSE . ' as cp', "cp.id = b.customer_purse_id", array());
	$sql->join(DbTable::TABLE_PURSE_MASTER . ' as pm', "pm.id = cp.purse_master_id", array('block_validity_hr'));
	$sql->where("b.status = ?", STATUS_BLOCKED);
	$sql->order('b.id ASC');
	$row = $this->_db->fetchAll($sql);
	if ($row) {
	    return $row;
	} else {
	    return FALSE;
	}
    }

    public function doWalletReleaseAmount($params) {
	// fetch detail
	$blockDetail = $this->getBlockDetail($params);
	if ($blockDetail) {
	    // ==> Update Status of block Amount
	    $updArrBlock = array('status' => STATUS_RELEASED, 'date_unblocked' => new Zend_Db_Expr('NOW()'));
	    $this->update($updArrBlock, "id = " . $blockDetail['id']);
	    // ==> update block_amount of customer purse
	    $updArr = array(
		'block_amount' => new Zend_Db_Expr("block_amount - " . $blockDetail['amount']),
		'date_updated' => new Zend_Db_Expr('NOW()')
	    );
	    $where = "id = '" . $blockDetail['customer_purse_id'] . "'";
	    $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PURSE, $updArr, $where);
	    return $params['txn_code'];
	} else {
	    return FALSE;
	}
    }

    public function checkReleaseTime($blockdata) {
	$block_date = date_create($blockdata['date_created']);
	$hr_validity = $blockdata['block_validity_hr'];
	date_add($block_date, date_interval_create_from_date_string("$hr_validity hours"));
	$last_unblock_date = date_format($block_date, 'Y-m-d H:i:s'); 
	$date_unblock = new DateTime($last_unblock_date);
	$now = new DateTime("now");
	if ($now > $date_unblock) {
	    return TRUE;
	}  
	return FALSE;
	 
    }
    
    public function chkClaimAmount($param) {
	$blocked_txnCode = $param['blockedAmt_txnCode']; 
	$claim_amount = $param['claim_amount'];
	$wallet_code = $param['wallet_code'];
	$product_id = $param['product_id'];
	$mobile = $param['mobile'];
	$partner_ref_no = $param['partner_ref_no'];
	$txn_type = $param['txn_type'];
	  
	$getblockdetails = $this->getBlockDetail(array('txn_code' => $blocked_txnCode)) ;
	
	$object = new Corp_Ratnakar_CustomerPurse();
	$custInfo = $object->getPurseCardInfo(array(
	    'product_id'=> $product_id,'wallet_code'=> $wallet_code,'mobile'=> $mobile,'partner_ref_no'=>$partner_ref_no
	));
	
	if(empty($getblockdetails)){
	    throw new Exception(ErrorCodes::ERROR_CLAIM_AMOUNT_FAIL_MSG, ErrorCodes::ERROR_CLAIM_AMOUNT_FAIL_CODE);
	} else if($getblockdetails['status'] != STATUS_BLOCKED){
	    throw new Exception(ErrorCodes::ERROR_CLAIM_AMOUNT_FAIL_MSG, ErrorCodes::ERROR_CLAIM_AMOUNT_FAIL_CODE);
	} else if(Util::convertToRupee($claim_amount) != $getblockdetails['amount']){
	    throw new Exception(ErrorCodes::ERROR_INCORRECT_AMOUNT_MSG, ErrorCodes::ERROR_INCORRECT_AMOUNT_CODE);
	} else if($txn_type != $getblockdetails['txn_type']){
	    throw new Exception(ErrorCodes::ERROR_INCORRECT_TXNTYPE_MSG, ErrorCodes::ERROR_INCORRECT_TXNTYPE_CODE);
	} else if($custInfo['customer_purse_id']!= $getblockdetails['customer_purse_id']) {
	    throw new Exception(ErrorCodes::ERROR_INCORRECT_CUST_DETAIL_MSG, ErrorCodes::ERROR_INCORRECT_CUST_DETAIL_CODE);
	} else {
	    return TRUE;
	}
	return FALSE;
    }
    
}
