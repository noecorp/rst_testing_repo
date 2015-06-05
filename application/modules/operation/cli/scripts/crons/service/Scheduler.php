<?php
if (!defined('CRON_PATH')) {
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../../'));
}

require_once CRON_PATH . '/cli.php';

$cronSchedule = new CronSchedule();
$crons = new Crons();

$param = array(
    'time' => date('H:i:') . '00',
    'day' => date('N')
);
$cronList = $cronSchedule->getCronlist($param);

$param = array(
    'cron_id' => CRON_SERVICE_SCHEDULER,
);

$cronLogId = $crons->addCronLog($param);
$output = '';

try {

    foreach ($cronList as $cron) {
        $path = realpath(dirname(__FILE__) . '/../');
        $filePath = $path . '/' . $cron['file_name'];
        $output.= 'CRON NAME: ' . $cron['name'] . PHP_EOL;
        $output.= 'Output :';
        $output.= shell_exec("/usr/bin/php -f $filePath");
        $output.= PHP_EOL;
    }
    $param['message'] = $output;
} catch (Exception $e) {
    $output = $e->getMessage();
}

$param['id'] = $cronLogId;
$crons->updateCronLog($param);
