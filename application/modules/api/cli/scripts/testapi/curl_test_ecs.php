<?php
if (!defined('CRON_PATH')) {
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../../'));
}

require_once CRON_PATH . '/cli.php';
//Customer Authentication
try {
   $ecsSOAP = new App_Api_ECS_Transactions();
  $flg =  $ecsSOAP->initSession();
  echo 'Flag :'.$flg;
} catch (Exception $e) { 
    echo '<pre>';print_r($e);exit;
}