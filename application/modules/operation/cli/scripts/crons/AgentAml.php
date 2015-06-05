<?php
if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}
require_once CRON_PATH . '/cli.php';

$crons = new Crons();
$cronInfo = $crons->getCronInfo(array('cron_id'=>CRON_AML_ID));
$activeStatus = isset($cronInfo['status'])?$cronInfo['status']:'';
$runStatus = isset($cronInfo['status_cron'])?$cronInfo['status_cron']:'';

if($activeStatus==STATUS_ACTIVE){ // if cron active then only cron should execute
    $cronLogId = $crons->addCronLog(array('cron_id'=>CRON_AML_ID));
    if($runStatus==STATUS_COMPLETED) { //if cron in execution status as 'complete' then only will run
        try{
            $agent = new Agents();
            $agent->updateAmlAgent();

            $kotakcardholdersModel = new Corp_Kotak_Customers();
            $kotakcardholdersModel->updateAmlKotakCardholders();

            $ratcardholdersModel = new Corp_Ratnakar_Cardholders();
            $ratcardholdersModel->updateAmlRatnakarCardholders();

            //$boicardholdersModel = new Corp_Boi_Customers();
            //$boicardholdersModel->updateAmlBOICardholders();

            $remitkotak = new Remit_Kotak_Remitter();
            $remitkotak->updateAmlKotakRemitters();

            $remitrat = new Remit_Ratnakar_Remitter();
            $remitrat->updateAmlRatRemitters();

            //$remitboi = new Remit_Boi_Remitter();
            //$remitboi->updateAmlBoiRemitters();

            $kotakbeneficiary = new Remit_Kotak_Beneficiary();
            $kotakbeneficiary->updateAmlKotakBeneficiary();

            $ratbeneficiary = new Remit_Ratnakar_Beneficiary();
            $ratbeneficiary->updateAmlRatnakarBeneficiary();

            //$boibeneficiary = new Remit_Boi_Beneficiary();
            //$boibeneficiary->updateAmlBoiBeneficiary();
            
            $msg= 'Cron done successfully';
            $updParam = array('cron_id'=>CRON_AML_ID, 'message'=>$msg, 'id'=>$cronLogId);
            $crons->updateCronLog($updParam);
            $crons->updateCron(array('status_cron'=>STATUS_COMPLETED, 'id'=>CRON_AML_ID));
        } catch(Exception $e){
            $msg = $e->getMessage();
            $param = array('cron_id'=>CRON_AML_ID, 'message'=>$msg, 'id'=>$cronLogId);
            $crons->updateCron(array('status_cron'=>STATUS_STOPPED, 'id'=>CRON_AML_ID)); // updating cron status in t_cron table
            $crons->updateCronLog($param);         
          }
        } else {
            $param = array('cron_id'=>CRON_AML_ID, 'message'=>'The cron is already '.$runStatus.' and does not have '.STATUS_COMPLETED.' status', 'id'=>$cronLogId);
            $crons->updateCronLog($param);
        }
}