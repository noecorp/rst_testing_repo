     <?php
//do {	 
    $host="192.168.2.153";
    $port = '1234';
    $message = '0067ISO0060000750800822000000000000004000000000000000904072533101033001';
    $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Error: SOCKET\n");
    $result = socket_connect($socket, $host, $port) or die("Error: Connection\n");
    socket_read ($socket, 2048) or die("Error: RESP\n");
	//print 'Write 1 ' .  "\n";
    $message = $message . "\n";
    socket_write($socket, $message, strlen($message)) or die("Error: DATA\n");
	//print 'Write success ' .  "\n";
//    $message = $message . "\n";
    $message = $message . "\n";

    $result = socket_read($socket, 2048) or die("Error: RESP\n");
    socket_write($socket, "quit", 4) or die("Error: QUIT\n");
	
    print 'Response Recived : ' . $result;
	socket_close($socket);

    
//} while(true);
