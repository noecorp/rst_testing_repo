<?php
if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo       = $crons->getCronInfo(array('cron_id'=>CRON_MVC_SESSION_VALIDATOR_ID)); 
$activeStatus   = isset($cronInfo['status']) ? $cronInfo['status'] : ''; // specify the cron active/inactive status here 
$runStatus      = isset($cronInfo['status_cron'])? $cronInfo['status_cron'] : ''; // it is cron last run status, means cron executed successfully last time or not

if($activeStatus==STATUS_ACTIVE){  // if cron active then only cron should execute
    $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_MVC_SESSION_VALIDATOR_ID));
    if($runStatus==STATUS_COMPLETED) {  //if cron in execution status as 'complete' then only will run
        try{
            $crons->updateCron(array('status_cron'=>STATUS_STARTED, 'id'=>CRON_MVC_SESSION_VALIDATOR_ID)); //  updating cron status in t_cron table
            $apiSession = new ApiSession();
            $ecsMVC = new App_Api_MVC_Transactions();
            //$flg =  $ecsMVC->initSession();
            $flg =  $ecsMVC->validateSessionByCron();
            
            //print 'Pass' . PHP_EOL;
            if($flg == false) {
                $msg= 'Error : '. $ecsMVC->getError();
                //print 'Message: ' . $msg;
                $apiSession->updateSession(array(
                        'sessionId'     => '',
                        'userId'        => TP_MVC_ID,
                        'status'        => 'failure',
                ));
                
            } else {
                $msg= 'Cron executed successfully.'.PHP_EOL;
                //print 'SESSION Key: ' .$ecsSOAP->getLastSessionID();
                $apiSession->updateSession(array(
                        //'sessionId'     => $ecsMVC->getSessionKey(),
                        'userId'        => TP_MVC_ID,
                        'status'        => 'success',
                ));
            }
             $param = array('cron_id'=>CRON_MVC_SESSION_VALIDATOR_ID, 'message'=>$msg, 'id'=>$cronLogId);
             $crons->updateCron(array(
                 'status_cron'  =>  STATUS_COMPLETED,
                 'id'   =>  CRON_MVC_SESSION_VALIDATOR_ID
               )); // updating cron status in t_cron table 
            } catch(Exception $e){
                $msg = $e->getMessage();
                $param = array('cron_id'=>CRON_MVC_SESSION_VALIDATOR_ID, 'message'=>$msg, 'id'=>$cronLogId);
                $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_MVC_SESSION_VALIDATOR_ID)); // updating cron status in t_cron table
                App_Logger::log('MVCSessionValidator cron :- '.$msg, Zend_Log::ERR);
            }
    } else {
        $param = array('cron_id'=>CRON_MVC_SESSION_VALIDATOR_ID, 'message'=>'That cron already '.$runStatus.' and does not has complete status', 'id'=>$cronLogId); 

    }
    $crons->updateCronLog($param);
}

