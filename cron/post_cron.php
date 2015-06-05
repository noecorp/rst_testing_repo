<?php
/**
 * Default entry point for the cron
 *
 * @category public
 * @package public
 * @subpackage public_frontend
 * @copyright company
 */
 

// holds the name of the current module
define('CURRENT_MODULE', 'agent');
require './index.base.php';
mkdir('../logs/cron/');
ini_set("log_errors", 1);

$fileName="../logs/cron/post_";

$fileName=$fileName.date('m_d_Y_hia').'.log';

ini_set("error_log", $fileName);

error_log("Starting cron \n");

$remittance = new Remit_Ratnakar_Remittancerequest();

$remittance->post();

error_log("Ending cron");

