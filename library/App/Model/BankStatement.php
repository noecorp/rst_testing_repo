<?php

/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */
class BankStatement extends App_Model {

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
    protected $_name = DbTable::TABLE_BANK_STATEMENT;

    /**
     * Constant thats hold bank statement status
     * @var string
     */

    const BANK_STATEMENT_STATUS_NEW = STATUS_NEW;
    const BANK_STATEMENT_STATUS_DUPLICATE = STATUS_DUPLICATE;
    const BANK_STATEMENT_STATUS_UNSETTLED = STATUS_UNSETTLED;
    const BANK_STATEMENT_STATUS_SETTLED = STATUS_SETTLED;

    function insertBankStatements(array $statements, $realBankStatementFileName) {
        $this->_db->beginTransaction();
        try {
            $ip = $this->formatIpAddress(Util::getIP());
            $user = Zend_Auth::getInstance()->getIdentity();
            $date_updated = new Zend_Db_Expr('NOW()');
            foreach ($statements as $statement) {

                $journal = explode(BANK_STATEMENT_JOURNAL_NO_EXPLODE_DELIMITER, $statement['Description']);
                $data['funding_no'] = '';
                $data['fund_transfer_type_id'] = 0;
                if (count($journal) > 1 && isset($journal[0])) { //Count condation for if array 0 index have journal_no 
                    $data['funding_no'] = $journal[0];
                    $data['fund_transfer_type_id'] = FUND_TRANSFER_TYPE_ID_NEFT;
                }

                $cheque = explode(BANK_STATEMENT_CHEQUE_NO_EXPLODE_DELIMITER, $statement['Description']);

                if (count($cheque) > 1 && isset($cheque[1])) { //Count condation for, if array 1 index have cheque_no 
                    $data['funding_no'] = str_replace('/', '', $cheque[1]);
                    $data['fund_transfer_type_id'] = FUND_TRANSFER_TYPE_ID_CASH;
                }

                $data['bank_stt_name'] = $realBankStatementFileName;
                $data['txn_date'] = $statement['Date'];
                $data['description'] = $statement['Description'];
                $data['mode'] = $statement['Inst-No Cr/Dr'];
                $data['amount'] = preg_replace('/[^\d.]/', '', $statement['Amount']);
                $data['balance'] = preg_replace('/[^\d.]/', '', $statement['Balance']);
                $data['status'] = self::BANK_STATEMENT_STATUS_NEW;
                $data['ip'] = $ip;
                $data['by_ops_id'] = $user->id;
                $data['date_updated'] = $date_updated;
                $this->_db->insert(DbTable::TABLE_BANK_STATEMENT, $data);
            }
            $this->_db->commit();
            return true;
        } catch (Exception $e) {
            $this->_db->rollBack();
            return $e;
        }
    }

    function isDuplicate($condition) {
        $select = $this->_db->select()
                ->from(DbTable::TABLE_BANK_STATEMENT, array('id'))
                ->where($condition);
        $row = $this->_db->fetchRow($select);
        if (!empty($row))
            return TRUE;
        else
            return FALSE;
    }

    function markNewBankStatementToUnsettled() {
        //First Select all record

        $query = $this->select()
                ->where("status='" . self::BANK_STATEMENT_STATUS_NEW . "'")
                ->where("mode='" . TXN_MODE_CR . "'");
                //->limit(AGENT_FUNDING_FETCH_DATA_LIMIT);
        $statements = $this->fetchAll($query);
        $duplicate = 0;
        $unsettled = 0;
        foreach ($statements as $statement) {
            $condition = " id <> " . $statement->id;
            if (!empty($statement->funding_no)) {
                $condition.=" AND funding_no='" . $statement->funding_no . "'";
            }

            $condition.=" AND amount='" . $statement->amount . "'";
            $condition.=" AND (status='" . self::BANK_STATEMENT_STATUS_DUPLICATE . "'";
            $condition.=" OR  status='" . self::BANK_STATEMENT_STATUS_UNSETTLED . "'";
            $condition.=" OR  status='" . self::BANK_STATEMENT_STATUS_SETTLED . "'";
            $condition.=")";

            if ($this->isDuplicate($condition)) {
                $statement->status = self::BANK_STATEMENT_STATUS_DUPLICATE;
                $duplicate++;
            } else {
                $statement->status = self::BANK_STATEMENT_STATUS_UNSETTLED;
                $unsettled++;
            }
            $statement->save();
        }
        $msg = "bnk stmt mark as duplicate=$duplicate  and unsettled=$unsettled ";
        return $msg;
    }

    function markUnsettledBankStatementSettled() {
        $query = $this->select()
                ->where("status='" . self::BANK_STATEMENT_STATUS_UNSETTLED . "'")
                ->where("mode='" . TXN_MODE_CR . "'")
                ->limit(AGENT_FUNDING_FETCH_DATA_LIMIT);
        $statements = $this->fetchAll($query);
        
        
        $msg = "";
        foreach ($statements as $statement) {
            $agentFunding = new AgentFunding();
            $resMsg = $agentFunding->findAgentFundingForBankStatementAndDoSettled($statement);
            
            $modelCorporateFunding = new CorporateFunding();
            $resMsg = $modelCorporateFunding->findAgentFundingForBankStatementAndDoSettled($statement);
            
            $msg .=$resMsg;
        }
        return $msg;
    }

    public function getAllUnsettledBankStatement(array $conditions = array()) {
        $query = $this->sqlAllUnsettledbankStatement($conditions);
        $bankStatements = $this->fetchAll($query);
        return $bankStatements;
    }

    
    private function sqlAllUnsettledbankStatement(array $conditions = array()){
      $query = $this->select()
                ->from($this->_name, array("id", "txn_date", "description", "mode", "status", "amount", "funding_no"));
        foreach ($conditions as $col => $val) {
            $query->where($col . '=?', $val);
        }
        $query->where('status=?', STATUS_UNSETTLED)
                ->where('mode=?', TXN_MODE_CR);
        return $query;
    }
    
     public function exportAllUnsettledBankStatement(array $conditions = array()){
        $retData = array();
        $query = $this->sqlAllUnsettledbankStatement($conditions);
        $data = $this->fetchAll($query);
        
        if(!empty($data))
        {  
            foreach($data as $key=>$data){
                    $retData[$key]['funding_no']          = $data->funding_no;
                    $retData[$key]['description']          = $data->description;
                    $retData[$key]['amount']              = $data->amount;
                    $retData[$key]['txn_date']            = $data->txn_date;
          }
        }
        return $retData;
    }
    public function getUnsettledBankStatementById($id) {
        $query = $this->select()
                ->from($this->_name, array("id as statement_id", "txn_date", "description", "mode", "status", "amount", "funding_no"))
                ->where('id=?', $id)
                ->where('status=?', STATUS_UNSETTLED)
                ->where('mode=?', TXN_MODE_CR);

        $bankStatement = $this->fetchRow($query);
        return $bankStatement;
    }

    public static function settledBankStatement($statement, $updTime) {
        $statement->status = STATUS_SETTLED;
        $statement->date_updated = $updTime;
        $statement->save();
        return $statement;
    }
    
    function addBankStatements(array $statements, $realBankStatementFileName) {
        $this->_db->beginTransaction();
        try {
            $ip = $this->formatIpAddress(Util::getIP());
            $user = Zend_Auth::getInstance()->getIdentity();
            $date_updated = new Zend_Db_Expr('NOW()');
            foreach ($statements as $statement) {

                $data['funding_no'] = $statement[1];
                $data['fund_transfer_type_id'] = FUND_TRANSFER_TYPE_ID_NEFT;
                $data['bank_stt_name'] = $realBankStatementFileName;
                $data['txn_date'] = date('Y-m-d H:i:s',strtotime($statement[0]));
                $data['description'] =$statement[2];
                $data['mode'] = TXN_MODE_CR;
                $data['amount'] = preg_replace('/[^\d.]/', '', $statement[3]);
                $data['balance'] = preg_replace('/[^\d.]/', '', 0.00);
                $data['status'] = self::BANK_STATEMENT_STATUS_NEW;
                $data['ip'] = $ip;
                $data['by_ops_id'] = $user->id;
                $data['bank_id'] = $statement['bank_id'];
                $data['date_updated'] = $date_updated;
                $this->_db->insert(DbTable::TABLE_BANK_STATEMENT, $data);
            }
            $this->_db->commit();
            return true;
        } catch (Exception $e) {
            echo "<pre>";print_r($e); exit;
            $this->_db->rollBack();
            return $e;
        }
    }
    
    function addiciciBankStatements(array $statements, $realBankStatementFileName) {
        $this->_db->beginTransaction();
        try {
            $ip = $this->formatIpAddress(Util::getIP());
            $user = Zend_Auth::getInstance()->getIdentity();
            $date_updated = new Zend_Db_Expr('NOW()');
            foreach ($statements as $statement) { 
                $txn_date = DateTime::createFromFormat('d/m/Y H:i:s', "$statement[3] 00:00:00");
                 
                $data['funding_no'] = $statement[4];
                $data['fund_transfer_type_id'] = FUND_TRANSFER_TYPE_ID_NEFT;
                $data['bank_stt_name'] = $realBankStatementFileName;
                $data['txn_date'] = $txn_date->format('Y-m-d H:i:s');
                $data['description'] =  '';
                $data['mode'] = TXN_MODE_CR;
                $data['amount'] = preg_replace('/[^\d.]/', '', $statement[2]);
                $data['balance'] = preg_replace('/[^\d.]/', '', 0.00);
                $data['status'] = self::BANK_STATEMENT_STATUS_NEW;
                $data['ip'] = $ip;
                $data['by_ops_id'] = $user->id;
                $data['bank_id'] = $statement['bank_id'];
                $data['date_updated'] = $date_updated;
                $this->_db->insert(DbTable::TABLE_BANK_STATEMENT, $data);
                $bank_statment_id = $this->_db->lastInsertId(DbTable::TABLE_BANK_STATEMENT, 'id');
               
                $data1['bank_statment_id']  =   $bank_statment_id ;
                $data1['customer_code']     =   $statement[0] ;
                $data1['vendor_code']       =   $statement[1] ;
                $data1['amount']            =   $statement[2] ;
                $data1['transaction_date']  =   $txn_date->format('Y-m-d H:i:s') ; 
                $data1['utr_no']            =   $statement[4] ;
                $data1['ifsc_code']         =   $statement[5] ;
                $data1['credit_account_no'] =   $statement[6] ;
                $data1['vendor_name']       =   $statement[7] ;
                $data1['payment_mode']      =   $statement[8] ;
                $data1['beneficiary_bank']  =   $statement[9] ; 
                $this->_db->insert(DbTable::TABLE_IPAY_BANK_STATEMENT, $data1); 
            }
            $this->_db->commit();
            return true;
        } catch (Exception $e) {
            $this->_db->rollBack();
            return $e;
        }
    }
}
