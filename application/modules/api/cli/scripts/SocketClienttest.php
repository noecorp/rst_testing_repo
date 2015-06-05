<?php

//Constants
if (!defined('STDIN')) {
    die('You must launch the worker from the command line');
}

if (!defined('ROOT_PATH')){
    define('ROOT_PATH', realpath(dirname(__FILE__) . '/../../../../../'));
}
if (!defined('APPLICATION_PATH')){
    define('APPLICATION_PATH', realpath(ROOT_PATH . '/application'));
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
//Require the webservice file
require ROOT_PATH . '/application/configs/webservice.php';
//Start the worker
//echo sprintf("%s: Analytics worker ready and waiting for tasks...\n", date('r'));
//$option =
//$message = $argv[1];
//echo "<pre>";print_r($argv);exit;
//$socket = new App_Socket_Client('192.168.2.50','1234');
 $trans = new App_Socket_MVC_Transaction();
 print $trans->sendDataToMVC('0067ISO0060000750800822000000000000004000000000000000904072533101033001');
//$socket->create("init");
//print $socket->getLastResponse();
//$socket->create("User Vikram");
//print $socket->getLastResponse();
//$socket->create("Password 1234");
//print $socket->getLastResponse();
//$socket->create("connect");
//print $socket->getLastResponse();
//$socket->create("qqqqqqqqqqqqqq11111111111");
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
