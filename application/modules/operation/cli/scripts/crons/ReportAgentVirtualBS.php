<?php

ini_set('max_execution_time', 0);


if (!defined('CRON_PATH')) {
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}
require_once CRON_PATH . '/cli.php';

$date = date('Y-m-d', strtotime("-1 days"));
//$date ='2014-07-30';
$startDate = $date . ' 00:00:00';
$endDate = $date . ' 23:59:59';
$durationDates = array(
    '0' => array(
        'from' => $startDate,
        'to' => $endDate
    )
);
 
/*
 *                
 * 
 * 
 * 
 * Only for Testing Dates
 * 
 * 
 * 
 * 
 *
$durationDates = array(
    '0' => array(
        'from' => date('Y-m-d', strtotime('-0 days'))." 00:00:00",
        'to' => date('Y-m-d', strtotime('-0 days'))." 23:59:59",
    ),
    '1' => array(
        'from' => date('Y-m-d', strtotime('-1 days'))." 00:00:00",
        'to' => date('Y-m-d', strtotime('-1 days'))." 23:59:59",
    ),
    '2' => array(
        'from' => date('Y-m-d', strtotime('-2 days'))." 00:00:00",
        'to' => date('Y-m-d', strtotime('-2 days'))." 23:59:59",
    ),
    '3' => array(
        'from' => date('Y-m-d', strtotime('-3 days'))." 00:00:00",
        'to' => date('Y-m-d', strtotime('-3 days'))." 23:59:59",
    ),
    '4' => array(
        'from' => date('Y-m-d', strtotime('-4 days'))." 00:00:00",
        'to' => date('Y-m-d', strtotime('-4 days'))." 23:59:59",
    ),
    '5' => array(
        'from' => date('Y-m-d', strtotime('-5 days'))." 00:00:00",
        'to' => date('Y-m-d', strtotime('-5 days'))." 23:59:59",
    ),
    '6' => array(
        'from' => date('Y-m-d', strtotime('-6 days'))." 00:00:00",
        'to' => date('Y-m-d', strtotime('-6 days'))." 23:59:59",
    ),
);
 * 
 * 
 */
 
$objReports = new Reports();
$exportData = $objReports->exportAgentVirtualBalanceSheet($durationDates);


$columns = array(
    'Transaction Date',
    'Agent/Distributor/Super Distributor Name',
    'Agent/Distributor/Super Distributor Code',
    'Bank Name',
    'Opening Balance',
    'Total Agent Funding Amount (Authorized)',
    'Unauthorized Fund Request Amount',
    'Total Transaction Amount', //(Load Amount) 
    'Debits to Payable A/c',
    'Closing Balance',
);

$objCSV = new CSV();
try {

    $resp = $objCSV->export($exportData, $columns, 'agent_virtual_balance_sheet');
    exit;
} catch (Exception $e) {
    App_Logger::log($e->getMessage(), Zend_Log::ERR);
}

