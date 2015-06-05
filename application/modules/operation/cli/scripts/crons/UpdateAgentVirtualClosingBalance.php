<?php

if (!defined('CRON_PATH')) {
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';

$cron_id = CRON_UPDATE_AGENT_VIRTUAL_CLOSING_BALANCE_ID;

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id' => $cron_id));
$activeStatus = isset($cronInfo['status']) ? $cronInfo['status'] : '';
$runStatus = isset($cronInfo['status_cron']) ? $cronInfo['status_cron'] : '';

if ($activeStatus == STATUS_ACTIVE) {// if cron active then only cron should execute
    $cronLogId = $crons->addCronLog(array('cron_id' => $cron_id));
    if ($runStatus == STATUS_COMPLETED) {//if cron in execution status as 'complete' then only will run
        $objAgentBal = new AgentVirtualBalance();
        try {
            // updating cron status in t_cron table
            $crons->updateCron(array('status_cron' => STATUS_STARTED, 'id' => $cron_id));
            $resp = $objAgentBal->updateAgentVirtualClosingBalance();
            $msg = $resp . ' agents virtual balance have been added/updated in table t_agent_virtual_closing_balance for '
                    . date('Y-m-d', strtotime('-1 days'));
            $param = array('cron_id' => $cron_id, 'message' => $msg, 'id' => $cronLogId);
            $crons->updateCron(array('status_cron' => STATUS_COMPLETED, 'id' => $cron_id)); // updating cron status in t_cron table
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $param = array('cron_id' => $cron_id, 'message' => $msg, 'id' => $cronLogId);
            $crons->updateCron(array('status_cron' => STATUS_STOPPED, 'id' => $cron_id)); // updating cron status in t_cron table
            $crons->updateCronLog($param);
        }
    } else {
        $msg = 'That cron already ' . $runStatus . ' and does not has complete status';
        $param = array('cron_id' => $cron_id, 'message' => $msg, 'id' => $cronLogId);
    }
    $crons->updateCronLog($param);
}