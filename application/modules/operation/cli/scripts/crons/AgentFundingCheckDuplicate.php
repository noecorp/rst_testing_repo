<?php
exit('Deprecated');
if (!defined('CRON_PATH')) {
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}
require_once CRON_PATH . '/cli.php';

$cron_id = CRON_AGENT_FUNDING_CHECK_DUPLICATE_BANK_STATEMENT;

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id' => $cron_id));
$activeStatus = isset($cronInfo['status']) ? $cronInfo['status'] : '';
$runStatus = isset($cronInfo['status_cron']) ? $cronInfo['status_cron'] : '';

if ($activeStatus == STATUS_ACTIVE) { // if cron active then only cron should execute
    $cronLogId = $crons->addCronLog(array('cron_id' => $cron_id));

    if ($runStatus == STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run
        try {
            $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>$cron_id)); // updating cron status in t_cron table            
            //NOTE:: Please don't chnage the hierarchy of calling function
            //First mark new bank statements to unsettled
//            $msg = '';
//            $modelBankStatement = new BankStatement();
//            $msg.=$modelBankStatement->markNewBankStatementToUnsettled();
//            
//            //now mark pending agent funding to duplicate
//            $modelAgentFunding = new AgentFunding();
//            $msg.=$modelAgentFunding->markPendingAgentFundingToDuplicate();
//            
//            //now mark pending corporate funding to duplicate
//            $modelAgentFunding = new CorporateFunding();
//            $msg.=$modelAgentFunding->markPendingAgentFundingToDuplicate();
//            
//            //after that mark unsettled bank statement settled
//            $msg.=$modelBankStatement->markUnsettledBankStatementSettled();
//            
            
            //Log Msg to Cron
            $updParam = array('cron_id' => $cron_id, 'message' => $msg, 'id' => $cronLogId);
            $crons->updateCronLog($updParam);
            $crons->updateCron(array('status_cron' => STATUS_COMPLETED, 'id' => $cron_id)); // updating cron status in t_cron table
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $param = array('cron_id' => $cron_id, 'message' => $msg, 'id' => $cronLogId);
            $crons->updateCron(array('status_cron' => STATUS_STOPPED, 'id' => $cron_id)); // updating cron status in t_cron table
            $crons->updateCronLog($param);
        }
    } else {
        $param = array('cron_id' => $cron_id, 'message' => 'The cron is already ' . $runStatus . ' and does not have ' . STATUS_COMPLETED . ' status', 'id' => $cronLogId);
        $crons->updateCronLog($param);
    }
}
