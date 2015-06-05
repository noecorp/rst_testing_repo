<?php
if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}
require_once CRON_PATH . '/cli.php';
define('BOI_CORP_CORPORATE_LOAD_SUCCESS_TO_FAILURE', 'CDRV');

class revertCardLoadBoi extends Corp_Boi{
    
    

    public function getLoadRequests($param) {
        $status = isset($param['status']) ? $param['status'] : '';
        $loadChannel = isset($param['load_channel']) ? $param['load_channel'] : '';
        $batchName = isset($param['batch_name']) ? $param['batch_name'] : '';
        $product = isset($param['product_id']) && $param['product_id'] > 0? $param['product_id'] : '';
        $chkAvailable = (isset($param['amount']) && $param['amount'] != '') ? $param['amount'] : '';
        $cardholder_id = (isset($param['cardholder_id']) && $param['cardholder_id'] != '') ? $param['cardholder_id'] : '';
        $load_request_id = (isset($param['load_request_id']) && $param['load_request_id'] != '') ? $param['load_request_id'] : '';
        
        
        
        $select = $this->_db->select()
        ->from(DbTable::TABLE_BOI_CORP_LOAD_REQUEST ." as l",array('l.*'));
        $select->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "l.product_id  = p.id",array('p.name as product_name'));
        
        
        if ($product != '') {
            $select->where('l.product_id = ?', $product);
        }
        if ($status != '') {
            $select->where('l.status = ?', $status);
        }
      
        if ($loadChannel != '') {
            $select->where('l.load_channel = ?', $loadChannel);
        }
        if ($batchName != '') {
            $select->where('l.batch_name = ?', $batchName);
        }
        if ($chkAvailable != ""){
            $select->where("l.amount = ?",$chkAvailable);
        }
        if ($cardholder_id != ""){
            $select->where("l.cardholder_id = ?",$cardholder_id);
        }
        if ($load_request_id != ""){
            $select->where("l.id = ?",$load_request_id);
        }
        //echo $select; exit;
        return $this->_db->fetchAll($select);
    }
    
   /*
     * doRejectAccountLoad will revert corporate load Accounts
     */

    public function doRejectAccountLoad($param) {

        $loadRequests = $this->getLoadRequests($param);
        $count = count($loadRequests);
        $retResp = array('loaded' => 0, 'not_loaded' => 0, 'exception' => array());
        if ($count > 0) {
            $baseTxn = new BaseTxn();
            $m = new \App\Messaging\Corp\Boi\Operation();
            $cardholderModel = new Corp_Boi_Customers();
            $custPurseModel = new Corp_Boi_CustomerPurse();
            foreach ($loadRequests as $key => $val) {
              
                $failedReason = 'Failed';
                $retResp['not_loaded'] = $retResp['not_loaded'] + 1;
                
                $baseTxnParams = array(
                              'txn_code' =>  $val['txn_code'], 
                              'customer_master_id' =>  $val['customer_master_id'], 
                              'product_id' =>  $val['product_id'], 
                              'purse_master_id' =>  $val['purse_master_id'], 
                              'customer_purse_id' =>  $val['customer_purse_id'], 
                              'amount' =>  $val['amount'], 
                            );
                
                
                    
                $this->faildBoiCorporateCardLoad($baseTxnParams);
                
                $updateArr = array(
                        'status' => STATUS_FAILED, 
                        'date_failed' => new Zend_Db_Expr('NOW()'),
                        'failed_reason' => $failedReason,
                        'date_load' => new Zend_Db_Expr('NOW()'),
                        'amount_available' => '0.00'
                    );
                
                $this->updateLoadRequests($updateArr, $val['id']);
                 
                  
            } // foreach
        }
        return $retResp;
    }
    
    
    public function faildBoiCorporateCardLoad($params) {
       $this->_db->beginTransaction(); 
        
        try 
        { 
            $params['remarks'] = '';
            $params['ip'] = '';//$this->formatIpAddress(Util::getIP()); // cron would not return ip so commented yet
            $params['date'] = new Zend_Db_Expr('NOW()');
            $params['txn_type'] = BOI_CORP_CORPORATE_LOAD_SUCCESS_TO_FAILURE;
            $params['txn_status'] = FLAG_SUCCESS;

            /********* Ops Cr txn entry *******/
            $paramsDr['mode'] = TXN_MODE_CR;
            $paramsDr['ops_id'] = TXN_OPS_ID;
            $paramsDr['txn_customer_master_id'] = $params['customer_master_id'];
            $opsData = array_merge($params, $paramsDr);
            //echo "<pre>"; print_r($opsData); exit;
            $this->insertTxnOps($opsData);
            /********* Ops Dr txn entry over *******/
            
            /***** Customer Dr txn entry ***********/
            $paramsCr['mode'] = TXN_MODE_DR;
            $paramsCr['txn_ops_id'] = TXN_OPS_ID;
            $custData = array_merge($params, $paramsCr);
            $this->insertTxnBoiCustomer($custData);
            /********* Customer Cr entry over *******/

            //$updArr = array('amount' => new Zend_Db_Expr("amount - " . $params['amount']), 'date_updated' => $params['date']);
            //$where = "id = '" . $params['customer_purse_id'] . "'";
            //$this->_db->update(DbTable::TABLE_BOI_CUSTOMER_PURSE, $updArr, $where);
            
            $this->_db->commit();
            
            return TRUE;

        } catch (Exception $e) {
            //echo "<pre>";print_r($e); exit;
            $this->_db->rollBack();
            App_Logger::log($e->getMessage(), Zend_Log::ALERT);
            throw new App_Exception($e->getMessage());
        }
    }
    
    public function updateLoadRequests($data, $id){
        if(!empty($data) && $id>0){
           $this->_db->update(DbTable::TABLE_BOI_CORP_LOAD_REQUEST,$data, 'id="'.$id.'"');
           return true;
        }
        return false;
    }
    
     /*
     * Entry in ops txn table
     */
    private function insertTxnOps($params) {
        $txnOpsData['txn_code'] = $params['txn_code'];
        $txnOpsData['ops_id'] = (isset($params['ops_id']) && $params['ops_id'] > 0) ? $params['ops_id'] : 0;
        
        $txnOpsData['txn_customer_master_id'] = (isset($params['txn_customer_master_id']) && $params['txn_customer_master_id'] > 0) ? $params['txn_customer_master_id'] : 0;
        $txnOpsData['purse_master_id'] = (isset($params['purse_master_id']) && $params['purse_master_id'] > 0) ? $params['purse_master_id'] : 0;
        $txnOpsData['customer_purse_id'] = (isset($params['customer_purse_id']) && $params['customer_purse_id'] > 0) ? $params['customer_purse_id'] : 0;
        $txnOpsData['txn_cardholder_id'] = (isset($params['txn_cardholder_id']) && $params['txn_cardholder_id'] > 0) ? $params['txn_cardholder_id'] : 0;
        $txnOpsData['txn_agent_id'] = (isset($params['txn_agent_id']) && $params['txn_agent_id'] > 0) ? $params['txn_agent_id'] : 0;
        $txnOpsData['txn_ops_id'] = (isset($params['txn_ops_id']) && $params['txn_ops_id'] > 0) ? $params['txn_ops_id'] : 0;
        $txnOpsData['txn_remitter_id'] = (isset($params['txn_remitter_id']) && $params['txn_remitter_id'] > 0) ? $params['txn_remitter_id'] : 0;
        $txnOpsData['kotak_remitter_id'] = (isset($params['kotak_remitter_id']) && $params['kotak_remitter_id'] > 0) ? $params['kotak_remitter_id'] : 0;
        $txnOpsData['txn_beneficiary_id'] = (isset($params['txn_beneficiary_id']) && $params['txn_beneficiary_id'] > 0) ? $params['txn_beneficiary_id'] : 0;
        $txnOpsData['kotak_beneficiary_id'] = (isset($params['kotak_beneficiary_id']) && $params['kotak_beneficiary_id'] > 0) ? $params['kotak_beneficiary_id'] : 0;
        $txnOpsData['product_id'] = $params['product_id'];
        $txnOpsData['agent_fund_request_id'] = (isset($params['agent_fund_request_id']) && $params['agent_fund_request_id'] > 0) ? $params['agent_fund_request_id'] : 0;
        $txnOpsData['agent_funding_id'] = (isset($params['agent_funding_id']) && $params['agent_funding_id'] > 0) ? $params['agent_funding_id'] : 0;
        $txnOpsData['remittance_request_id'] = (isset($params['remit_request_id']) && $params['remit_request_id'] > 0) ? $params['remit_request_id'] : 0;
        $txnOpsData['kotak_remittance_request_id'] = (isset($params['kotak_remittance_request_id']) && $params['kotak_remittance_request_id'] > 0) ? $params['kotak_remittance_request_id'] : 0;
        $txnOpsData['ip'] = $params['ip'];
        $txnOpsData['currency'] = CURRENCY_INR;
        $txnOpsData['amount'] = $params['amount'];
        $txnOpsData['mode'] = $params['mode'];
        $txnOpsData['txn_type'] = $params['txn_type'];
        $txnOpsData['txn_status'] = (isset($params['txn_status']) && $params['txn_status'] != '') ? $params['txn_status'] : FLAG_SUCCESS;
        $txnOpsData['remarks'] = $params['remarks'];
        $txnOpsData['date_created'] = $params['date'];
        //echo "<pre>inops";print_r($txnOpsData); exit; 
        $this->_db->insert(DbTable::TABLE_TXN_OPS, $txnOpsData);
    }
    
    private function insertTxnBoiCustomer($params)
    {
        $txnData['txn_code'] = $params['txn_code'];
        $txnData['customer_master_id'] = $params['customer_master_id'];
        $txnData['txn_customer_master_id'] = (isset($params['txn_customer_master_id']) && $params['txn_customer_master_id'] > 0) ? $params['txn_customer_master_id'] : 0;
        $txnData['txn_agent_id'] = (isset($params['txn_agent_id']) && $params['txn_agent_id'] > 0) ? $params['txn_agent_id'] : 0;
        $txnData['txn_ops_id'] = (isset($params['txn_ops_id']) && $params['txn_ops_id'] > 0) ? $params['txn_ops_id'] : 0;
        $txnData['product_id'] = $params['product_id'];
        $txnData['purse_master_id'] = $params['purse_master_id'];
        $txnData['customer_purse_id'] = (isset($params['customer_purse_id']) && $params['customer_purse_id'] > 0) ? $params['customer_purse_id'] : 0;
        $txnData['ip'] = $params['ip'];
        $txnData['currency'] = CURRENCY_INR;
        $txnData['amount'] = $params['amount'];
        $txnData['mode'] = $params['mode'];
        $txnData['txn_type'] = $params['txn_type'];
        $txnData['txn_status'] = $params['txn_status'];
        $txnData['remarks'] = $params['remarks'];
        $txnData['date_created'] = $params['date'];
        $this->_db->insert(DbTable::TABLE_BOI_TXN_CUSTOMER,$txnData);
    }
    
    
    
}



$param = array('status' => 'loaded', 'load_channel' => BY_OPS, 'load_request_id'=>'3');
$model = new revertCardLoadBoi();
$respCnt = $model->doRejectAccountLoad($param);


