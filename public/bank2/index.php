<?php

error_reporting(-1);
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
//chdir(dirname(__DIR__));

define('ROOT_PATH', realpath(dirname(__FILE__) . '/../..'));
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));
chdir(ROOT_PATH);

define('CURRENT_MODULE','bank');
//print ROOT_PATH;exit;
// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

//Setup Constants
require ROOT_PATH . '/application/configs/constants.php';

// Setup autoloading
require ROOT_PATH . '/public/init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require ROOT_PATH . '/application/configs/bank.config.php')->run();
