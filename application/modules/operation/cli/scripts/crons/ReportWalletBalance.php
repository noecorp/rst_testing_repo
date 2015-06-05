<?php
ini_set('max_execution_time', 0);


if (!defined('CRON_PATH')) {
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}
require_once CRON_PATH . '/cli.php';


$date = date('Y-m-d',strtotime("-1 days"));
//$date ='2014-07-30';
$startDate = $date . ' 00:00:00';
$endDate = $date . ' 23:59:59';
$durationDates =array('0' => array
        (
            'from' => $startDate,
            'to' => $endDate
        )
);


                 $objReports = new Reports();
                 $exportData = $objReports->exportAgentBalanceSheet($durationDates);


                 $columns = array(
                                    'Transaction Date',
                                    'Agent/Distributor/Super Distributor Name',
                                    'Agent/Distributor/Super Distributor Code',
                                    'Bank Name',
                                    'Opening Balance',
                                    'Total Agent Funding Amount (Authorized)',
                                    'Unauthorized Fund Request Amount',
                                    'Total Transaction Amount',
                                    'Total Refund Transaction Amount',
                                    'Total Fee',
                                    'Total Service Tax',
                                    'Total Service Tax Reversal',
                                    'Total Fee Reversal',
                                    'Debits to Payable A/c',

                                    'Debit Reversals',
                                    'Commission',
                                    'Commission Reversal',
                                    'Closing Balance',
                                 );

                 $objCSV = new CSV();
try{

                        $resp = $objCSV->export($exportData, $columns, 'agent_balance_sheet');exit;
} catch (Exception $e) {
                                         App_Logger::log($e->getMessage() , Zend_Log::ERR);
}

