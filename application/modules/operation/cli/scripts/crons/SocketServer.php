<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';
//Start the worker
//echo sprintf("%s: Analytics worker ready and waiting for tasks...\n", date('r'));
$config = App_Webservice::get('shmart_iso');
$socket = new App_Socket_Server($config['ip'],$config['port']);
print $socket->create();

