<?php
//exit;
//exit('Service is down for maintaince');
/**
* Default index.php file 
* All other {module}/index.php files should include this one
 *
 * @category public
 * @package public
 * @copyright transerv
 */
/*
//Need to Enable this during Production Push Process
global $time;
//Set time for count down (It will display on maintaince page)
$time ='2013-7-01 15:40:00 GMT+05:30';
include_once '../../scripts/under-construction/index.php';
exit;
*/
date_default_timezone_set('Asia/Calcutta');
//Need to move it to Constants
define('CR','cr');
define('DR','dr');
//error_reporting(1);
//..ini_set("display_errors", 1); 
//ini_set('error_reporting', E_ALL);
// define the application path constant
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));

$paths = array(
    realpath(dirname(__FILE__) . '/../library'),
    get_include_path(),
);

set_include_path(implode(PATH_SEPARATOR, $paths));
if(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) {
    ini_set("session.cookie_secure", '1');
} else {
    ini_set("session.cookie_secure", '0');
}
require APPLICATION_PATH . '/Bootstrap.php';
$bootstrap = new Bootstrap();

//define('UPLOAD_PATH', ROOT_PATH . '/uploads' );
//define('UPLOAD_PATH_AGENT_PHOTO', ROOT_PATH . '/public/agent/uploads/photo' );
//define('UPLOAD_PATH_REMITTER_PHOTO', ROOT_PATH . '/public/agent/uploads/photo/remitter' );
//define('UPLOAD_IMPORTCRN_PATH', ROOT_PATH . '/uploads/crn' );
//define('UPLOAD_REMIT_BOI_PATH', ROOT_PATH . '/uploads/remit/boi' );

Zend_Registry::set('Bootstrap', $bootstrap);

$bootstrap->runApp();
