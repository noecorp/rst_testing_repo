<?php

if (!defined('CRON_PATH')) {
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}
//echo CRON_PATH . '/cli.php'; exit;
require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id' => CRON_RAT_SETTLEMENT_RESPONSE_ID));
$activeStatus = isset($cronInfo['status']) ? $cronInfo['status'] : '';
$runStatus = isset($cronInfo['status_cron']) ? $cronInfo['status_cron'] : '';

if ($activeStatus == STATUS_ACTIVE) { // if cron active then only cron should execute
    $cronLogId = $crons->addCronLog(array('cron_id' => CRON_RAT_SETTLEMENT_RESPONSE_ID));
    //echo $cronLogId.'---------'; die;


    if ($runStatus == STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run
        $objLoad = new Corp_Ratnakar_Cardload();
        $objNetReq = new Remit_Ratnakar_NeftRequest();
        $batchName = 'not';
        $i = 0;
        $msg = '';
        $requestsCount = 0;
        //print '<pre>';
        try {
            $crons->updateCron(array('status_cron' => STATUS_STARTED, 'id' => CRON_RAT_SETTLEMENT_RESPONSE_ID)); // updating cron status in t_cron table
            $i = $i + 1;
            $unsettlementResponseRecords = $objLoad->getUnsettlementResponse($params = array()); 
            if (!empty($unsettlementResponseRecords)) {
                try {
                    $numSettledRecords = $objLoad->updateUnsettlementResponseRecords($unsettlementResponseRecords);
                    $msg = $numSettledRecords." Unsettled records settled." ;
                    $param = array('cron_id' => CRON_RAT_SETTLEMENT_RESPONSE_ID, 'message' => $msg, 'id' => $cronLogId);
                } catch (App_Exception $e) {
                    $msg = $e->getMessage();
                    $param = array('cron_id' => CRON_RAT_SETTLEMENT_RESPONSE_ID, 'message' => $msg, 'id' => $cronLogId);
                    $crons->updateCron(array('status_cron' => STATUS_STOPPED, 'id' => CRON_RAT_SETTLEMENT_RESPONSE_ID)); // updating cron status in t_cron table
                    $crons->updateCronLog($param);
                }
            } else {
                $msg = "0 Unsettled records found";
                $param = array('cron_id' => CRON_RAT_SETTLEMENT_RESPONSE_ID, 'message' => $msg, 'id' => $cronLogId); 
            }
            $crons->updateCron(array('status_cron' => STATUS_COMPLETED, 'id' => CRON_RAT_SETTLEMENT_RESPONSE_ID)); // updating cron status in t_cron table
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $param = array('cron_id' => CRON_RAT_SETTLEMENT_RESPONSE_ID, 'message' => $msg, 'id' => $cronLogId);
            $crons->updateCron(array('status_cron' => STATUS_STOPPED, 'id' => CRON_RAT_SETTLEMENT_RESPONSE_ID)); // updating cron status in t_cron table
            $crons->updateCronLog($param);
        }
    } else {
        $param = array('cron_id' => CRON_RAT_SETTLEMENT_RESPONSE_ID, 'message' => 'The cron is already ' . $runStatus . ' and does not have ' . STATUS_COMPLETED . ' status', 'id' => $cronLogId);
    }
    $crons->updateCronLog($param);
}