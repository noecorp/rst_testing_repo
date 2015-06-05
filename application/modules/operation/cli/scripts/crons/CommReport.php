<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';

$objAgent = new AgentUser();
$objComm = new CommissionReport();
$curdate = date("Y-m-d");
//$curdate = '2014-07-18';
$qurData['from'] = $curdate;
$qurData['to'] = $curdate;

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_COMMISSION_REPORT_ID));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';

if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute

    $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_COMMISSION_REPORT_ID));

 if($runStatus==STATUS_COMPLETED) { // if cron execution's status is as 'complete' then only will run
        
    try{
        $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>CRON_COMMISSION_REPORT_ID)); // updating cron status in t_cron table
        $agentArr = $objAgent->getAgents(array());
        
        $totalAgents = $objComm->saveCommission($qurData, $agentArr);
        $totalRecs = count($agentArr);
        $msg= $totalAgents.' agents commissions have been added in table t_commission_report';
        $param = array('cron_id'=>CRON_COMMISSION_REPORT_ID, 'message'=>$msg, 'id'=>$cronLogId);
        $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_COMMISSION_REPORT_ID)); // updating cron status in t_cron table
    } catch(Exception $e){
        $msg = $e->getMessage();
        $param = array('cron_id'=>CRON_COMMISSION_REPORT_ID, 'message'=>$msg, 'id'=>$cronLogId);
        $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_COMMISSION_REPORT_ID)); // updating cron status in t_cron table
      }
    } else {
        $param = array('cron_id'=>CRON_COMMISSION_REPORT_ID, 'message'=>'The cron is already '.$runStatus.' and does not have '.STATUS_COMPLETED.' status', 'id'=>$cronLogId);
    }

    $crons->updateCronLog($param);
}
