<?php
/**
 * Socket Clent Class
 * Class intended to handle socket functionlity 
 * 
 * @copany Transerv
 * @author Vikram Singh <vikram@transerv.co.in>
 */
class App_Socket_Client extends App_Socket {
    
    private $lastResponse;
    
    private $_thirdPartyUserId;
    
    public function __construct($address, $port, $tpuId ='') {
        $this->thirdPartyUserId = $tpuId;
        parent::__construct($address, $port);
    }
    
    /*public function create($message)
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Error: SOCKET\n");
        $result = socket_connect($socket, $this->_address, $this->_port) or die("Error: Connection\n");
        socket_read ($socket, 2048) or die("Error: RESP\n");
        $message = $message . "\n";
            
        socket_write($socket, $message, strlen($message)) or die("Error: DATA\n");
        $result = socket_read($socket, 2048) or die("Error: RESP\n");
        //socket_write($socket, "quit", 4) or die("Error: QUIT\n");
        $this->setResponse($result);
        
        App_Logger::isolog(array(
            'user_id'    => $this->_thirdPartyUserId,
            'method'        => $function_name,
            'request'       => $message,
            'response'       => $result,            
        ));
        
    }*/

    public function isoCall2($method, $message) {
//    public function isoCall($message) {
            //$host="192.168.2.153";
        //$port = '1111';
        //$message = '0067ISO0060000750800822000000000000004000000000000000904072533101033001';
        $socket = socket_create(AF_INET, SOCK_STREAM, 0) or print "Unable to Connect MVC Socket"; //die("Error: SOCKET\n");
        $result = socket_connect($socket, $this->_address, $this->_port) or print "MVC: Connect Error ";// or die("Error: Connection\n");
        //socket_read ($socket, 2048) or die("Error: RESP\n");
	print 'Write 1 ' .  "\n";
        //$message = $message . "\n";
        socket_write($socket, $message, strlen($message)) or print "MVC: Data Error";// die("Error: DATA\n");
        //$message = $message . "\n";
        //$result = socket_read($socket, 2048,PHP_BINARY_READ) or print "MVC: Response Error";//die("Error: RESP\n");
        $result = socket_read($socket, 2048,PHP_BINARY_READ) or print "MVC: Response Error";//die("Error: RESP\n");
        print 'Result:'.$result;
        /*if (false !== ($bytes = socket_recv($socket, $buf, 2048, MSG_WAITALL))) {
            print "Read $bytes bytes from socket_recv(). Closing socket...";
        } else {
            print "socket_recv() failed; reason: " . socket_strerror(socket_last_error($socket)) . "\n";
        }*/
        //$result = $buf; 
        
        //socket_write($socket, "quit", 4) or print "MVC: Quit";//die("Error: QUIT\n");
        //print 'Response Recived : ' . $result;
        //$this->setResponse($result);
	socket_close($socket);
        return $result;
    }
    
    public function isoCall($method, $message)
    {
        error_reporting(E_ALL);
        ob_implicit_flush();
        $sock = socket_create(AF_INET, SOCK_STREAM, 0);
//        if (socket_bind($sock, $this->_address, $this->_port) === false) {
//            if (!socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1)) {
//                print socket_strerror(socket_last_error($sock));
//                exit;
//            }
//        }
        socket_connect($sock, $this->_address, $this->_port);
        /*if (socket_listen($sock, 5) === false) {
            print "socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
        }*/
        //do {
//            if (($msgsock = socket_accept($sock)) === false) {
//                print "socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
//                break;
//            }
            //$msg = "\nPOC-ISO Telnet Test. \n" . " Enter 'quit' to exit\n";
            //$msg = "\n";
            socket_write($sock, $message, strlen($message));
            //do {
                $buf ='';//reset buffer
                if (false === ($buf = socket_read($sock, 2048))) {
                    print "socket_read() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
                    //break 2;
                }
               
                
            //} while (true);
            //socket_close($msgsock);
        //} while (true);
        socket_close($sock);
        return $buf;
    }
    
    /*
    public function isoCall($method, $message)
    {
        print 'In isoCall';
        try {
        $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Error: SOCKET\n");
        //print $this->_address . ':' . $this->_port;
            print 'Socket Created';
            print $this->_address . ':' . $this->_port;
        socket_connect($socket, $this->_address, $this->_port) or die("Error: Connection\n");
        
       print 'Connected' . "\n";
                
        socket_read ($socket, 2048) or die("Error: RESP\n");
        print 'READ' . "\n";
        //$message = $message . "\n";
       // print 'Sending Message:'.$message;exit;
        print 'Writing Again';
        socket_write($socket, $message, strlen($message)) or die("Error: DATA\n");
         
        //print 'Message2 ' . $message . "\n";
        $result = socket_read($socket, 2048) or die("Error: RESP\n");
        //print 'Result Back:' . $result;exit;
        //socket_write($socket, "quit", 4) or die("Error: QUIT\n");
           // return 'ABC'.$message;
        $this->setResponse($result);
        socket_close($socket);
        print 'Connection Closed';
        
        } catch (Exception $e) {
           echo "<pre>";print_r($e);exit;
            //Generate Error Log
       //     App_Logger::log($e,  Zend_Log::ERR);
        //    $result = $result . "\n" . $e;
        }
        /*
        App_Logger::isolog(array(
            'user_id'    => $this->_thirdPartyUserId,
            'method'        => $method,
            'request'       => $message,
            'response'       => $result,            
        ));
        */
        //return true;
//        print 'Method : '.$method . "\n";
//        print 'Message : ' . $message . "\n";
//        print 'Result : ' . $result . "\n";
//        exit;            
        //print __CLASS__ . ':' . __METHOD__ . ' : '. $result . "\n";        
     //   return $result;        
   // }
    
    public function getLastResponse()
    {
        return $this->lastResponse;
    }
    
    private function setResponse($msg)
    {
        $this->lastResponse = $msg;
    }
    
    
    public function isoCallECS($iso) {
        
        try {
        $socket = socket_create(AF_INET, SOCK_STREAM, 0);
        if($socket == false ) {
            throw new Exception("Unable ");
        }
        $result = socket_connect($socket, $this->_address, $this->_port) or print "MVC: Connect Error ";// or die("Error: Connection\n");
        socket_write($socket, $iso, strlen($iso)) or print "MVC: Data Error";// die("Error: DATA\n");
        $result = socket_read($socket, 2048,PHP_BINARY_READ) or print "MVC: Response Error";//die("Error: RESP\n");
        $this->setResponse($result);
	socket_close($socket);
        return $result;        
        } catch (Exception $e) {
            print '<pre>';
            print_r($e);
            exit();                
        }
    }
    
    
    public function recursiveIsoCall($message) {
            //$host="192.168.2.153";
        //$port = '1111';
        //$message = '0067ISO0060000750800822000000000000004000000000000000904072533101033001';
        $socket = socket_create(AF_INET, SOCK_STREAM, 0) or print "Unable to Connect MVC Socket"; //die("Error: SOCKET\n");
        $result = socket_connect($socket, $this->_address, $this->_port) or print "MVC: Connect Error ";// or die("Error: Connection\n");
        //socket_read ($socket, 2048) or die("Error: RESP\n");
	//print 'Write 1 ' .  "\n";
        //$message = $message . "\n";
        for(;;) {
        socket_write($socket, $message, strlen($message)) or print "MVC: Data Error";// die("Error: DATA\n");
        //$message = $message . "\n";
        //$result = socket_read($socket, 2048,PHP_BINARY_READ) or print "MVC: Response Error";//die("Error: RESP\n");
        $result = socket_read($socket, 2048,PHP_BINARY_READ) or print "MVC: Response Error";//die("Error: RESP\n");
        print $result.PHP_EOL;
        }
        /*if (false !== ($bytes = socket_recv($socket, $buf, 2048, MSG_WAITALL))) {
            print "Read $bytes bytes from socket_recv(). Closing socket...";
        } else {
            print "socket_recv() failed; reason: " . socket_strerror(socket_last_error($socket)) . "\n";
        }*/
        //$result = $buf; 
        
        //socket_write($socket, "quit", 4) or print "MVC: Quit";//die("Error: QUIT\n");
        //print 'Response Recived : ' . $result;
        $this->setResponse($result);
	socket_close($socket);
        return $result;
    }
    
    
}