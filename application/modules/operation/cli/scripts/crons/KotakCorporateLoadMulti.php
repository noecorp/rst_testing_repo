<?php
exit('STOP');
if (!defined('CRON_PATH')) {
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';
//
//$crons = new Crons();
//$cronInfo = $crons->getCronInfo(array('cron_id' => CRON_KOTAK_CORPORATE_LOAD_MULTI));
//$activeStatus = isset($cronInfo['status']) ? $cronInfo['status'] : '';
//$runStatus = isset($cronInfo['status_cron']) ? $cronInfo['status_cron'] : '';
//
//if ($activeStatus == STATUS_ACTIVE) { // if cron active then only cron should execute
//    $now = new Zend_Db_Expr('NOW()');
//    $cronLogId = $crons->addCronLog(array('cron_id' => CRON_KOTAK_CORPORATE_LOAD_MULTI));
//
//    if ($runStatus == STATUS_COMPLETED) { //if cron in execution status as 'completed' then only will run       
        /*
         * Corporate Load
         */
        $objLoad = new Corp_Kotak_Cardload();

        try {
            /*
             * updating cron status in t_cron table
             */
//            $crons->updateCron(
//                    array(
//                        'status_cron' => STATUS_STARTED,
//                        'id' => CRON_KOTAK_CORPORATE_LOAD_MULTI)
//            );



            $cardLoadResp = $objLoad->getLoadBatch(LOAD_REQ_BATCH_LIMIT);

            $loadCount = count($cardLoadResp);

            if ($loadCount > 0) {
                foreach ($cardLoadResp as $item) {
                    $pid = pcntl_fork();
                    switch ($pid) {
                        case -1: // Error
                            die();
                            break;
                        case 0: // Child
                            // Remove Signal Handlers in Child
                            pcntl_signal(SIGCHLD, SIG_DFL);
                            //setDB();
                            $config = App_DI_Container::get('ConfigObject');
                            $dbAdapter = Zend_Db::factory($config->resources->db);
                            Zend_Db_Table_Abstract::setDefaultAdapter($dbAdapter);
                            Zend_Registry::set('dbAdapter', $dbAdapter);                               
                            
                            $objLoad = new Corp_Kotak_Cardload();
                            $objLoad->doSingleCorporateLoad($item);
                            unset($objLoad);
                            exit(0);
                            break;
                        default: // Parent
                            if ($child_list >= 2) {
                                // Just wait for one to die
                                pcntl_wait($x);
                                $child_list--;
                            }
                            $child_list++;
                            break;
                    }
                }
            }


/*            $msg = $loadCount . ' cards loaded picked for processing';
            $param = array(
                'cron_id' => CRON_KOTAK_CORPORATE_LOAD_MULTI,
                'message' => $msg,
                'id' => $cronLogId
            );
 * 
 */

            /*
             * updating cron status in t_cron table
             */
//            $crons->updateCron(
//                    array(
//                        'status_cron' => STATUS_COMPLETED,
//                        'id' => CRON_KOTAK_CORPORATE_LOAD_MULTI)
//            );
        } catch (Exception $e) {
            echo '<pre>';print_r($e);exit;
            /*
             * updating cron status in t_cron table :-  Stopped 
             */
            $msg = $e->getMessage();
//            $param = array(
//                'cron_id' => CRON_KOTAK_CORPORATE_LOAD_MULTI,
//                'message' => $msg,
//                'id' => $cronLogId
//            );
//            setDB();
//            $crons->updateCron(
//                    array(
//                        'status_cron' => STATUS_STOPPED,
//                        'id' => CRON_KOTAK_CORPORATE_LOAD_MULTI)
//            );
        }
//    } else {
//        $param = array(
//            'cron_id' => CRON_KOTAK_CORPORATE_LOAD_MULTI,
//            'message' => 'This cron is already ' . $runStatus . ' and does not has complete status', 'id' => $cronLogId
//        );
//    }

    /*
     * updating cron LOG message in t_log_cron table
     */
//    setDB();
//    $crons->updateCronLog($param);
//}



//function setDB()
//{
//    $config = App_DI_Container::get('ConfigObject');
//    $dbAdapter = Zend_Db::factory($config->resources->db);
//    Zend_Db_Table_Abstract::setDefaultAdapter($dbAdapter);
//    Zend_Registry::set('dbAdapter', $dbAdapter);   
//}
