<?php
if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../'));
}
error_reporting(E_ALL);

echo "<h2>TCP/IP Connection</h2>\n";

/* Get the port for the WWW service. */
//$service_port = getservbyname('www', 'tcp');
//$service_port = '7002';
$service_port = '1234';

/* Get the IP address for the target host. */
//$address = gethostbyname('www.example.com');
//$address = '196.37.195.93'; -- //MVC ISO Conn Handle with Care
//$address = '192.168.2.168';

/* Create a TCP/IP socket. */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
} 
echo "Attempting to connect to '$address' on port '$service_port'...";
$result = socket_connect($socket, $address, $service_port);
if ($result === false) {
    echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
} 

//$in = "HEAD / HTTP/1.1\r\n";
//$in .= "Host: www.example.com\r\n";
//$in .= "Connection: Close\r\n\r\n";
//print CRON_PATH;exit;
$in = file_get_contents(CRON_PATH .'\socket\calls\100');
$out = '';

//echo "Sending HTTP HEAD request...";
socket_write($socket, $in, strlen($in));
//echo "OK.\n";

echo "Reading response:\n\n";
while ($out = socket_read($socket, 2048)) {
    echo $out;
    $fp = fopen(CRON_PATH .'\socket\calls\response', 'a');
    fwrite($fp, $out);
    fclose($fp);
}

echo "Closing socket...";
socket_close($socket);
echo "OK.\n\n";
?>