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
define('CURRENT_MODULE', 'api');

//Include path
$paths = array(
    ROOT_PATH . '/library',
    ROOT_PATH . '/library/Model',
    get_include_path(),
);
date_default_timezone_set('Asia/Kolkata');//Need to move this on bootstrap
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
$config = App_Webservice::get('shmart_iso');
$socket = new App_Socket_Server($config['ip'],$config['port']);
print $socket->create();



/**
 * Return the content of the config file
 *
 * @return object
 */
function getConfig(){
    return new Zend_Config_Ini(ROOT_PATH . '/application/configs/application.ini', APPLICATION_ENV);
}
