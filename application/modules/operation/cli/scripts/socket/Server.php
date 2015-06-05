<?php
if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}

require_once CRON_PATH . '/cli.php';
//Start the worker
//echo sprintf("%s: Analytics worker ready and waiting for tasks...\n", date('r'));
$config = App_Webservice::get('shmart_iso');
//$socket = new App_Socket_Server($config['ip'],$config['port']);
//print $socket->create();


/**
 * Check dependencies
 */
if( ! extension_loaded('sockets' ) ) {
	echo "Socket extension is required".PHP_EOL;
	exit(-1);
}

if( ! extension_loaded('pcntl' ) ) {
	echo "PCNTL extension is required ".PHP_EOL;
	//exit(-1);
}

/**
 * Connection handler
 */
function onConnect( $client ) {
	$pid = pcntl_fork();
	
	if ($pid == -1) {
		 die('could not fork');
	} else if ($pid) {
		// parent process
		return;
	}
	
	$read = '';
	printf( "[%s] Connected from port %d\n", $client->getAddress(), $client->getPort() );
	
	while( true ) {
		$read = $client->read();
		if( $read != '' ) {
			$client->send( '[' . date( DATE_RFC822 ) . '] ' . $read  );
		}
		else {
			break;
		}
		
		if( preg_replace( '/[^a-z]/', '', $read ) == 'exit' ) {
			break;
		}
		if( $read === null ) {
			printf( "[%s] Disconnected\n", $client->getAddress() );
			return false;
		}
		else {
			printf( "[%s] recieved: %s", $client->getAddress(), $read );

			//printf( "[%s] recieved: %s", 'Client:'.client_ip(), $read );
		}
	}
	$client->close();
	printf( "[%s] Disconnected\n", $client->getAddress() );
	
}

$reqHandler = new App_Socket_ECS_Transaction();
$server = new App\Sock\SocketServer($config['port'],$config['ip']);
$server->init();
$server->setConnectionHandler( array($reqHandler,'onConnect') );
$server->listen();
