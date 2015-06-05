<?php

//Constants
if (!defined('STDIN')) {
    die('You must launch the worker from the command line');
}

if (!defined('ROOT_PATH')){
    define('ROOT_PATH', realpath(dirname(__FILE__) . '/../../../../../'));
}

//Include path
$paths = array(
    ROOT_PATH . '/library',
    get_include_path(),
);

set_include_path(implode(PATH_SEPARATOR, $paths));

//Autoloader
require_once 'Zend/Loader/Autoloader.php';

$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('App_');
$loader->registerNamespace('Zend_');
$loader->setFallbackAutoloader(TRUE);

//Require the environment file
require ROOT_PATH . '/application/configs/environment.php';

//Start the worker
//echo sprintf("%s: Analytics worker ready and waiting for tasks...\n", date('r'));
//$option =
//$message = $argv[1];
//echo "<pre>";print_r($argv);exit;
$socket = new App_Socket_Client('192.168.2.168','1234');


//$socket->create("MINITESTMINITESTMINITESTMINITEST");

//$socket->create("init");
//print $socket->getLastResponse();
//$socket->create("User Vikram");
//print $socket->getLastResponse();
//$socket->create("Password 1234");
//print $socket->getLastResponse();
//$socket->create("connect");
//print $socket->getLastResponse();

$buffer = file_get_contents("F:\\xampp\\htdocs\\projects\\other\\logs\\calls\\100");
//$buffer = file_get_contents("/var/www/logs/calls/120");
//$socket->isoCall("0067ISO0060000750800822000000000000004000000000000000904072533101033001");
//print 'Buffer: '.$buffer.PHP_EOL;
//$socket->recursiveIsoCall($buffer);
//for(;;) {
    $socket->isoCallECS($buffer);
    print $socket->getLastResponse().PHP_EOL;
    
$buffer = file_get_contents("F:\\xampp\\htdocs\\projects\\other\\logs\\calls\\120");
//$buffer = file_get_contents("/var/www/logs/calls/120");
//$socket->isoCall("0067ISO0060000750800822000000000000004000000000000000904072533101033001");
//print 'Buffer: '.$buffer.PHP_EOL;
//$socket->recursiveIsoCall($buffer);
//for(;;) {
    $socket->isoCallECS($buffer);
    print $socket->getLastResponse().PHP_EOL;
//}
//print $socket->getLastResponse();



//echo sprintf("%s: Analytics worker finished\n");

/**
 * Log an error 
 *
 * @param string $string 
 * @return void
 */
function logError($msg){
}

/**
 * Return the content of the config file
 *
 * @return object
 */
function getConfig(){
    return new Zend_Config_Ini(ROOT_PATH . '/application/configs/application.ini', APPLICATION_ENV);
}
