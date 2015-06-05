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

$fileName="../logs/cron/remitter_";

$fileName=$fileName.date('m_d_Y_hia').'.log';

ini_set("error_log", $fileName);

error_log("Initiating session \n");

$remitterRegModel = new Remit_RemitterRegModel();

$session = $remitterRegModel->initSession("TRA1000315");

error_log("Starting registration \n");


$unRegRemittersData = $remitterRegModel->getUnregisteredRemittersAtRbl();

foreach ($unRegRemittersData as $remitterData) {

	error_log("\n Doing registration for". $remitterData['mobile'] );		
	$remitterRegModel->processRemitterRegistrationRequest($remitterData,$session);

}

error_log("Ending registration");





