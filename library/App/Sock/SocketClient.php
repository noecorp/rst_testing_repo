<?php

namespace App\Sock;

class SocketClient {

	private $connection;
	private $address;
	private $port;



	public function __construct( $connection ) {
		$address = ''; 
		$port = '';

		//socket_getsockname($connection, $address, $port);
                //socket_getsockname not returning proper addre and port, So using socket_getpeername() instead
		socket_getpeername($connection, $address, $port);
		$this->address = $address;
		$this->port = $port;
		$this->connection = $connection;
                $this->validateAllowedIP();
	}
	
	public function send( $message ) {	
		socket_write($this->connection, $message, strlen($message));
	}
	
	public function read($len = 1024) {
		if ( ( $buf = @socket_read( $this->connection, $len, PHP_BINARY_READ  ) ) === false ) {
				return null;
		}
		
		return $buf;
	}

	public function getAddress() {
		return $this->address;
	}
	
	public function getPort() {
		return $this->port;
	}
	
	public function close() {
		socket_shutdown( $this->connection );
		socket_close( $this->connection );
	}

        
        
        private function validateAllowedIP() {
            $config = \App_Webservice::get('shmart_iso');  
            if(is_array($config['allowed_ip'])) {
                if(!in_array($this->address, $config['allowed_ip'])) {
                    $this->close();
                }
            }
        }        

}
