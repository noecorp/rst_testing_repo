<?php
/**
 * Default CLI configuration
 *
 * @category backoffice
 * @package backoffice_cli
 * @copyright casting.net
 */

// define the application path constant
if(!defined('APPLICATION_PATH')){
    define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../../'));
}

if(!defined('ROOT_PATH')){
    define('ROOT_PATH', realpath(APPLICATION_PATH . '/../'));
}

$paths = array(
    realpath(APPLICATION_PATH . '/../library'),
    ROOT_PATH . '/library/App/Model',
    get_include_path(),
);

set_include_path(implode(PATH_SEPARATOR, $paths));

date_default_timezone_set('Asia/Kolkata');//Need to move this on bootstrap

define('CURRENT_MODULE', 'operation');
define('CLI', TRUE);

// Run the main bootstrap
require_once APPLICATION_PATH . '/Bootstrap.php';
$bootstrap = new Bootstrap(false);
$bootstrap->bootstrap(array('Autoloader', 'Environment', 'Db', 'ModulePaths'));

Zend_Registry::set('Bootstrap', $bootstrap);
Zend_Session::start(TRUE);
// Run the current's module CliBootstrap
require_once APPLICATION_PATH . '/modules/operation/CliBootstrap.php';
//$cliModuleBootstrap = new CliBootstrap();
Zend_Session::start(true);

