<?php
if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';

            $socket = new App_Socket_ECS_Transaction();
            $flg = $socket->validateSessionByCron();
            if($flg == false) {
                $msg= 'Error : '. $socket->getError();
            } else {
                $msg= 'Cron executed successfully.';
            }
