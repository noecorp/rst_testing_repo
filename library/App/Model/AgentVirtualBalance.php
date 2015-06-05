<?php

class AgentVirtualBalance extends BaseUser {

    /**
     * Column for the primary key id
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'agent_id';

    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_AGENT_VIRTUAL_BALANCE;
    private $_msg;

    public function updateAgentVirtualClosingBalance() {
        $agentsModel = new Agents();

        $agentsBal = $this->getAgentsVirtualBalance();
        $totalAgents = sizeof($agentsBal);
        $yesterday = date('Y-m-d', strtotime('-1 days'));

        if ($totalAgents > 0) {
            $this->_db->beginTransaction();
            try {
                $agentUpdCount = 0;

                foreach ($agentsBal as $key => $val) {
                    $val['daybeforeyesterday'] = date('Y-m-d', strtotime('-2 days'));
                    $param = array(
                        'agent_id' => $val['agent_id'],
                        'daybeforeyesterday' => $val['daybeforeyesterday']
                    );

                    $bindArr = $agentsModel->getAgentBinding($val['agent_id'], $yesterday);
                    $bank_unicode = isset($bindArr[0]['bank_unicode']) ? $bindArr[0]['bank_unicode'] : '';
                    $agentArr = array(
                        'agent_id' => $val['agent_id'],
                        'agentId' => $val['agent_id'],
                        'date' => $yesterday,
                        'bank_unicode' => $bank_unicode,
                        'on_date' => TRUE,
                        'chk_custpurse_empty' => TRUE,
                    );


                    $agClosingBal_daybeforeyesterday = $this->getAgentClosingBalance($param);

                    if (!empty($agClosingBal_daybeforeyesterday)) {
                        $agentArr['closing_balance'] = $agClosingBal_daybeforeyesterday['closing_balance'];
                        $agentArr['closing_date'] = $agClosingBal_daybeforeyesterday['date'];
                    }

                    //Virtual Agent fund 
                    $agentArr['txn_type'] = TXNTYPE_AGENT_FUND_LOAD;
                    $agentVirtualFundTrfrDr = $this->getAgentTotalfunding($agentArr);

                    //Virtual Agent Total Load
                    $agentArr['status'] = '';
                    $agentArr['txn_type'] = '';
                    $agentVirtualLoadTrfrCr = $this->getAgentTotalVirtualLoad($agentArr);

                    //We Add All fund transfer Or Load (dr)                    
                    $addOnOpeningBal = $agentVirtualFundTrfrDr;


                    //We Add All fund transfer Or Load (Cr)               
                    $subtractOnOpeningBal = $agentVirtualLoadTrfrCr;
                    $closingBal = $addOnOpeningBal - $subtractOnOpeningBal;

                    // inserting balance if not already inserted earlier
                    $param = array('agent_id' => $val['agent_id'], 'date' => $yesterday);
                    $agClosingBalYesterday = $this->getAgentClosingBalance($param);
                    if (empty($agClosingBalYesterday)) {
                        $this->_db->insert(
                                DbTable::TABLE_AGENT_VIRTUAL_CLOSING_BALANCE, array(
                            'closing_balance' => $closingBal, 'agent_id' => $val['agent_id'], 'date' => $yesterday
                        ));
                    } else {
                        // updating balance if already added earlier
                        $where = "agent_id='" . $val['agent_id'] . "' AND date='" . $yesterday . "'";
                        $dateUpdated = new Zend_Db_Expr('NOW()');
                        $updData = array('closing_balance' => $closingBal, 'date_updated' => $dateUpdated);
                        $this->_db->update(DbTable::TABLE_AGENT_VIRTUAL_CLOSING_BALANCE, $updData, $where);
                    }
                    $agentUpdCount++;
                }
                $this->_db->commit();
                return $agentUpdCount;
            } catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $this->_db->rollBack();
                throw new Exception($e->getMessage());
            }
        } else
            return 0;
    }

    public function getAgentsVirtualBalance() {
        $select = $this->_db->select()
                ->from($this->_name, array('agent_id', 'amount as closing_balance'));
        return $this->_db->fetchAll($select);
    }

    public function getAgentClosingBalance($param) {
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $daybeforeyesterday = isset($param['daybeforeyesterday']) ? $param['daybeforeyesterday'] : '';
        $date = isset($param['date']) ? $param['date'] : '';

        $select = $this->_db->select();
        $select->from(DbTable::TABLE_AGENT_VIRTUAL_CLOSING_BALANCE, array('closing_balance', 'date'));
        if ($agentId != '') {
            $select->where('agent_id = ?', $agentId);
        }
        if ($date != '') {
            $select->where('date = ?', $date);
        } else if ($daybeforeyesterday != '') {
            $select->where('date = ?', $daybeforeyesterday);
        }
        $select->limit(1);
        return $this->_db->fetchRow($select);
    }

    public function getAgentTotalfunding($param) {

        $closing_balance = (isset($param['closing_balance'])) ? $param['closing_balance'] : 0;
        $closing_date = (isset($param['closing_date'])) ? $param['closing_date'] : '';
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $txn_type = (isset($param['txn_type'])) ? $param['txn_type'] : '';

        if ($agentId != '') {
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_AGENT_VIRTUAL_FUNDING, array('sum(amount) AS balance'));
            $select->where('status = ?', STATUS_APPROVED);
            $select->where('txn_type = ?', $txn_type);
            $select->where('agent_id = ?', $agentId);

            if ($closing_date != '') {
                $select->where('DATE_FORMAT(date_funded, "%Y-%m-%d") > ?', $closing_date);
            }
            $select->where('DATE_FORMAT(date_funded, "%Y-%m-%d") = ?', $param['date']);
        }

        $close_balanceArr = $this->_db->fetchRow($select);

        $close_balance = $closing_balance + $close_balanceArr['balance'];
        return $close_balance;
    }

    public function getAgentVirtualBalance($agentId) {
        $select = $this->select()
                ->from($this->_name, array('amount'))
                ->where('agent_id = ?', $agentId);
        $data = $this->fetchRow($select);
        if (isset($data->amount)) {
            return $data->amount;
        } else {
            return 0;
        }
    }

    public function getAgentTotalVirtualLoad($param) {

        $agentId = isset($param['agent_id']) ? $param['agent_id'] : 0;
        $onDate = isset($param['on_date']) ? $param['on_date'] : FALSE;
        $date = isset($param['date']) ? $param['date'] : '';
        $txnType = isset($param['txn_type']) ? $param['txn_type'] : '';
        $status = isset($param['status']) ? $param['status'] : '';
        $cutoff = isset($param['cutoff']) ? $param['cutoff'] : FALSE;


        if ($agentId > 0) {
            $load_field = ($cutoff) ? 'amount_cutoff' : 'amount';
            $date_field = ($cutoff) ? 'date_cutoff' : 'date_created';

            $select = $this->_db->select();
            $select->from(
                    DbTable::TABLE_RAT_CORP_LOAD_REQUEST . ' as clReq', array("sum(" . $load_field . ") as total_agent_load_amount"
            ));
            $select->join(
                    DbTable::TABLE_PURSE_MASTER . ' as pm', "pm.id = clReq.purse_master_id", array());

            $select->where('clReq.by_agent_id = ?', $agentId);

            if ($status == '') {
                $status = array(STATUS_LOADED, STATUS_CUTOFF);
            }
            $select->where('clReq.status IN (?)', $status);

            if ($txnType == '') {
                $txnType = array(TXNTYPE_RAT_CORP_CORPORATE_LOAD, TXNTYPE_RAT_CORP_MEDIASSIST_LOAD, TXNTYPE_CARD_RELOAD);
            }
            $select->where('clReq.txn_type IN (?)', $txnType);
            $select->where('pm.is_virtual = ?', FLAG_YES);

            $select->where('DATE_FORMAT(clReq.' . $date_field . ', "%Y-%m-%d") = ?', $date);
            $loadArr = $this->_db->fetchRow($select);
            return $loadArr['total_agent_load_amount'];
        } else
            return 0;
    }

    public function getAgentBalance($agentId) {
        $select = $this->select()
                ->from($this->_name, array('amount'))
                ->where('agent_id = ?', $agentId);
        $data = $this->fetchRow($select);
        if (isset($data->amount)) {
            return $data->amount;
        } else {
            return FLAG_NO;
        }
    }

}
