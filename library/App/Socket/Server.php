<?php
/**
 * Socket Server Class
 * Class intended to handle socket functionlity 
 * 
 * @copany Transerv
 * @author Vikram Singh <vikram@transerv.co.in>
 */
class App_Socket_Server extends App_Socket {
    
    private $_session;
    private $_msg = '';
    private $_errormsg;
    public $__server_listening; 
    
    private $_regFunction = array(
        'init',
        'user',
        'password',
        'connect',
    );


    public function __construct($address, $port) {
        parent::__construct($address, $port);
        //$this->_session = new Zend_Session_Namespace('App.Api.Socket');
    }
    
    
 public function printiso($input) {
    $iso8583 = new App_ISO_ISO8583();          
    $covInput =  $asciiInput = $this->convert2ascii($input);
    print 'Convert ISO : '.$covInput. PHP_EOL;
    //print 'Length :'.strlen($covInput).PHP_EOL;
    //print 'LENGTH :'.$hex = (mb_substr($covInput, 0, 4)).PHP_EOL;
    //print 'CONVERTING LENGTH :'.hexdec($hex).PHP_EOL;
    //$this->logMessage('Convert ISO : '.$covInput);   

    $iso8583->addISOwithHeader($covInput);
    print 'Explaining ISO DATA'.PHP_EOL;
    //$this->logMessage('Explaining ISO DATA');   
    print 'MTI :'.$iso8583->getMTI() . PHP_EOL;
    //$this->logMessage('MTI :'.$iso8583->getMTI());       
    print 'DATA : ';
    print_r($iso8583->getData());
    print_r($iso8583->getBitmap());
    print 'Explaining ISO DATA END'.PHP_EOL;
}
    
    public function create2()
    {
        error_reporting(E_ALL);
        ob_implicit_flush();
        $sock = socket_create(AF_INET, SOCK_STREAM, 0);
        if (socket_bind($sock, $this->_address, $this->_port) === false) {
            if (!socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1)) {
                print socket_strerror(socket_last_error($sock));
                exit;
            }
        }
        if (socket_listen($sock, 5) === false) {
            print "socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
        } 
        //socket_set_nonblock($sock);
        do {
           // print "incoming". PHP_EOL;
            if (($msgsock = socket_accept($sock)) === false) {
                print "socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
                //break;
            } else {
                print "incoming 2". PHP_EOL;
            }
            print "$msgsock is connected". PHP_EOL;
            
            //$msg = "\nPOC-ISO Telnet Test. \n" . " Enter 'quit' to exit\n";
            $msg = "\n";
            //socket_write($msgsock, $msg, strlen($msg));
            do {
                $buf ='';//reset buffer
                //socket_set_option($msgsock, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 5, 'usec' => 0));
                //socket_set_option($msgsock, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 5, 'usec' => 0));
                if (false === ($buf = socket_read($msgsock, 2048))) {
                    print "socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
                    break 2;
                }
//                if (!$buf = trim($buf)) {
//                    continue;
//                }
                if ($buf == 'quit') {
                   // break;
                }
                //Validation pending 
                //After successful connection
               
                $talkback = '';
                $resp = '';
                    
                    //$resp = $this->getErrorMsg() == ''? $this->getErrorMsg() : 'Invalid Message: ' . $buf;
                $talkback .= "ISO Recived from ECS and Sending to MVC ". date("mdHis"). "\n";                    
                $talkback .= "ISO :" . $buf . "\n";

                $this->logMessage('ISO Recived from ECS and Sending to MVC');
                $this->logMessage($buf);
                
                $resp = $this->connectMVCServer($buf);
                // $resp = $buf;
                //$resp = '0045ISO016000075081082200000020000000400000000000000051503395812102400301';
                //$resp = $this->getDummyResponse();
                //$resp = '009308104230008000000002164444555566667777051713571123139500000012028Fri May 17 13:57:11 IST 2013';
                $this->logMessage('ISO Recived from MVC and Sending to ECS');
                $this->logMessage($resp);
                
                $talkback .= "ISO Recived from MVC and Sending to ECS " . date("mdHis"). "\n";
                $talkback .= "ISO :" . $resp . "\n";
                $talkback .= "Lenght default : " . strlen($resp) . "\n";
                $talkback .= "Lenght mb : " . $this->getSize($resp) . "\n";
                
//                $resp = $this->filterResponse($resp);
//                $talkback .= "After Alteration:"."\n";
//                $talkback .= "Lenght default : " . strlen($resp) . "\n";
//                $talkback .= "Lenght mb : " . $this->getSize($resp) . "\n";
                
               // }

                //$talkback = 'Response Server: ' . $resp;
                //$talkback =  $resp;
                $talkback = $talkback . "\n";
                //$resp = $resp . "\n";
                //$resp = $this->connectECSServer($resp);
                //$talkback = '0069ISO006000075081082200000020000000400000000000000062307253310103300001';
                //socket_write($msgsock, $resp, strlen($resp));
                //socket_write($msgsock, $resp, strlen($resp));
                //socket_write($msgsock, $resp, '0061');
                //$byt = socket_send($msgsock, $resp, strlen($resp));
                //$talkback .= "Byte Send :". $byt . "\n";
                //===========================================
                socket_write($msgsock, $resp, strlen($resp));
                print "$talkback\n";
                //$status = socket_get_status($sock);

//                if ($status['timed_out']) {
//                    echo "socket timed out\n";
//                }

                //print pack('N',$resp) . "\n";
                //print_r(unpack('S4', $resp));
           } while (true);
            
            socket_close($msgsock);
            print 'Closing:msgSock';
        } while (true);
        socket_close($sock);
        print 'Closing:sock';
    }
    
    
    public function create3()
    {
        
        $server = stream_socket_server("tcp://$this->_address:$this->_port", $errno, $errorMessage);

        if ($server === false) {
            throw new UnexpectedValueException("Could not bind to socket: $errorMessage");
        }

        for (;;) {
            $client = @stream_socket_accept($server);

            if ($client) {
                 $resp = $this->connectMVCServer($buf);
                stream_copy_to_stream($client, $client);
                fclose($client);
            }
        }
    }
    
    
    
    public function create() {

        $iso8583	= new App_ISO_ISO8583();                  
        // Set time limit to indefinite execution
        set_time_limit(0);

        // Set the ip and port we will listen on
        $address = $this->_address;
        $port = $this->_port;

        $max_clients = 100;

        // Array that will hold client information
        $clients = Array();

        // Create a TCP Stream socket
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        // Bind the socket to an address/port
        socket_bind($sock, $address, $port) or die('Could not bind to address');

        // Start listening for connections
        socket_listen($sock);

        // Loop continuously

        while (true) {

            // Setup clients listen socket for reading
            $read[0] = $sock;

            for ($i = 0; $i < $max_clients; $i++) {
                if (isset($client[$i])) {
                    if ($client[$i]['sock'] != null)
                        $read[$i + 1] = $client[$i]['sock'];
                }
            }

            // Set up a blocking call to socket_select()
            //$ready = socket_select($read,null,null,null);
            $ready = @socket_select($read, $write = NULL, $except = NULL, $tv_sec = NULL);

            /* if a new connection is being made add it to the client array */
            if (in_array($sock, $read)) {
                for ($i = 0; $i < $max_clients; $i++) {
                    if (@$client[$i]['sock'] == null) {
                        $client[$i]['sock'] = socket_accept($sock);
                        print $client[$i]['sock'] . ' Accepting connection' . PHP_EOL;
                        $this->logMessage($client[$i]['sock'] . ' Accepting connection');                        
                        break;
                    } elseif ($i == $max_clients - 1) {
                        print ("too many clients");
                    }
                }

                if (--$ready <= 0)
                    continue;
            } // end if in_array
            // If a client is trying to write - handle it now

            for ($i = 0; $i < $max_clients; $i++) { // for each client
                if (isset($client[$i])) {
                    if (in_array($client[$i]['sock'], $read)) {
                        $input = socket_read($client[$i]['sock'], 1024);
                        if ($input == null) {
                            // Zero length string meaning disconnected
                            //Might need to remove following 2 lines
                            print $client[$i]['sock'] . ' Closing connection' . PHP_EOL;
                            $this->logMessage($client[$i]['sock'] . ' Closing connection');                              
                            socket_close($client[$i]['sock']);
                            unset($client[$i]);
                        }

                        $n = trim($input);
                        if ($input) {
                            // strip white spaces and write back to user
                            //$output = ereg_replace("[ \t\n\r]","",$input).chr(0);
                            print 'Receving Data from '.$client[$i]['sock'] . PHP_EOL;
                            print 'Data: '.$input. PHP_EOL;
                            $this->logMessage('Receving Data from '.$client[$i]['sock']);                                 
                            $this->logMessage('Data: '.$input);   
                            
    
          //$iso = '0097ISO01600007502007238E00108C0800016333333000000144987000000000003500002051731402580591731400205020559993560641995300008760001230010001300100011234567356';
          //$iso = '0097ISO01600007502007238E00108C0800016333333000000000087000000000002900002051753067628851753060205020559993560641995300008760000930010001300100011234567356';
          //$iso = '0043ISO0060000750800822000000000000004000000000000000205171002553038001';
          //0067ISO0060000750800822000000000000004000000000000000623072533101033301
    
    //printf('%b','0045ISO016000075');exit;
    $covInput =  $asciiInput = $this->convert2ascii($input);
    print 'Convert ISO : '.$covInput. PHP_EOL;
    $this->logMessage('Convert ISO : '.$covInput);   

    $iso8583->addISOwithHeader($covInput);
    print 'Explaining ISO DATA'.PHP_EOL;
    $this->logMessage('Explaining ISO DATA');   
    print 'MTI :'.$iso8583->getMTI() . PHP_EOL;
    $this->logMessage('MTI :'.$iso8583->getMTI());       
    print 'DATA : ';
    print_r($iso8583->getData());
    $this->logMessage('DATA :');           
    $this->logMessage($iso8583->getData());           
    print  PHP_EOL;
    //print '<br /><br /><br />';
    print_r($iso8583->getBitmap());
    $this->logMessage('BITMAP :'.$iso8583->getBitmap());               
    print  PHP_EOL;
    
    print 'Explaining ISO DATA END'.PHP_EOL;
    $this->logMessage('Explaining ISO DATA END');                   
    //print '<br /><br /><br />';
                $resp='';
                $resp = $this->validateMessage($input);
                print 'OUR RESPONSE:'.PHP_EOL;
                print $resp.PHP_EOL;
                $this->logMessage('OUR RESPONSE');                                             
                $this->logMessage($resp);                                             
                $this->printiso($resp);
                //print 'OUR RESPONSE END';
                
//               $resp = $this->connectMVCServer($input);
//                print 'MVC RESPONSE:'.PHP_EOL;
//                print $resp.PHP_EOL;                
//                $this->logMessage('MVC RESPONSE');                                             
//                $this->logMessage($resp);          
//                $this->printiso($resp);
//                print 'MVC RESPONSE END';
                
                //$resp = $resp."\r\n".chr(0);
                socket_write($client[$i]['sock'], $resp);    
                //usleep(5);
                //socket_write($client[$i]['sock'], '');     
                //socket_write($client[$i]['sock'], $resp,  strlen($resp));     
                print 'Message Length :'.strlen($resp).PHP_EOL;
                $this->logMessage('Message Length :'.strlen($resp));
                //print 'Receving Data MVC Server and sending to the client '.$client[$i]['sock'] . PHP_EOL;
                //print 'Data: '.$resp. PHP_EOL;
                //$this->logMessage('Receving Data MVC Server and sending to the client '.$client[$i]['sock']);                          
                print 'Responding with data: '.$resp.PHP_EOL;
                $this->logMessage('Data: '.$resp);                             
  
                        }
                    } else {

                        // Close the socket
                        if (isset($client[$i])) {
                            print $client[$i]['sock'] . ' Closing connection!!!' . PHP_EOL;                            
                            $this->logMessage($client[$i]['sock'] . ' Closing connection!!!');                                                                                     
                            socket_close($client[$i]['sock']);
                            unset($client[$i]);
                        }
                    }
                }
            }
            
            flush();
            usleep(5);
        } // end while
        // Close the master sockets

        socket_close($sock);
    }

    private function getMessageType($msg)
    {
        $msgArry = explode(' ', $msg);
        if(isset($msgArry[0])) {
            $func = strtolower($msgArry[0]);
            if(in_array($func, $this->_regFunction)) {
                //return substr($msg, strlen($func) +1);
                $message = substr($msg, strlen($func) +1);
                if($message == '') {
                    $this->setErrorMsg("Invalid message");
                    return false; //print 'invalid message';
                }
                $this->setLastMessage($message);
                return $func;
            } 
        }
        
        return false;
    }
    
    private function getLastMessage()
    {
        return $this->_msg;
    }
    
    private function setLastMessage($msg)
    {
        $this->_msg = $msg;
    }
    
    
    private function login($username, $password)
    {
        $username = strtolower($username);
        $password = strtolower($password);
        print "\n" . "Login attempt" . "\n";
        print $username . ' : ' . $password . "\n";
        if($username =='vikram' && $password =='1234') {
            $this->_session->user->valid_login = true;
            return true;
        } 
        return false;
            
    }
    
    
    private function getErrorMsg()
    {
        return $this->_errormsg . "\n";
    }
    
    
    private function setErrorMsg($msg)
    {
        $this->_errormsg = $msg;
    }
    
    
    private function parseiso($iso) {
        /*$iso8583 = new App_ISO_ISO8583();
        $iso8583_v2 = clone $iso8583;
        //Validate ISO
        $iso8583->addISOwithHeader($iso);
        //$resp = $buf;
        //print '<pre>';
        //print_r($iso8583->getData());
        $resp = $iso8583->getData();

        $iso8583_v2->addMTI($iso8583->getMTI());
        //$iso8583->addData(7, date("mdHis"));
        $iso8583_v2->addData(7, $resp['7']);
        //$iso8583->addData(11, rand(1000, 999999));
        $iso8583_v2->addData(11, $resp['11']);
        $iso8583_v2->addData(70, $resp['70']);
        $iso8583_v2->addData(1, $resp['1']);
        $iso8583_v2->addData(39, '00');
        $iso8583_v2->addISOLiterals('ISO');
        $iso8583_v2->addMessage('0067');
        $iso8583_v2->addAdditionalHeader('006000075');
        return $iso8583_v2->getISOwithHeaders();*/
    }
    
    
    private function connectMVCServer($iso) {
        $trans = new App_Socket_MVC_Transaction();
        return $trans->sendDataToMVC($iso);
    }
    
    private function connectECSServer($iso) {
        $trans = new App_Socket_ECS_Transaction();
        return $trans->sendDatatoECS($iso);
    }
    
    private function logMessage($message) {
        //$fp = fopen('C:\\xampp\\htdocs\\shmart\\logs\\iso.log', 'a');
        $fp = fopen('/var/www/shmart/logs/iso.log', 'a');
        if($fp) {
            fwrite($fp, date('Y-m-d H:i:s').PHP_EOL);
            if(is_array($message)) {
                $msg = print_r($message,TRUE);
                fwrite($fp, $msg);
                
            } else {
                fwrite($fp, $message);
            }
            
            fwrite($fp, PHP_EOL);
            //fwrite($fp, '||');
            //fwrite($fp, "\n");
        }

    }
    
    private function filterResponse($resp) 
    {
        $req = substr($resp, 0, 1);
        if(bin2hex($req) == '00') {
            print 'removing first dot from string';
            $ret = substr($resp, 1, strlen($resp));
            return $ret;
        }
        return $rep;
        /*
        print 'response received:' . $resp. "\n";
        $req = substr($resp, 0, 1);
        print 'response response:' . $req. "\n";
        print 'response responsehax:' . bin2hex($req). "\n";
        //return substr($resp, 1, strlen($resp));
        $ret = substr($resp, 1, strlen($resp));
        print 'response response:' . $ret. "\n";
        return $ret;*/
    }
    
    private function getSize($str)
    {

       /* $has_mbstring = extension_loaded('mbstring') ||@dl(PHP_SHLIB_PREFIX.'mbstring.'.PHP_SHLIB_SUFFIX);
        $has_mb_shadow = (int) ini_get('mbstring.func_overload');

        if ($has_mbstring && ($has_mb_shadow & 2) ) {*/
           $size = mb_strlen($str,'latin1');
           //$size = mb_strlen($str,'8Bit');
        /*} else {
           $size = strlen($str);
        }*/
        return $size;
    }
    
    private function getDummyResponse()
    {
        $ecs = new App_Socket_ECS();
        return $ecs->generateDummyResponseISO();
    }
    
    private function loadTest() {
        try {
            $param = array(
                'amount' => '111',
                //'crn'       => '3333330000009228', 
                'crn' => '3333330000002470',
                'agentId' => '00000001',
                'transactionId' => '00000000' . rand(1111, 9999),
                'currencyCode' => CURRENCY_INR_CODE,
                'countryCode' => COUNTRY_IN_CODE
            );
            print 'Loading Amount!!!' . PHP_EOL;
            $ecsApi = new App_Socket_ECS_Transaction();
            //return $ecsApi->cardLoad($param);
            return $ecsApi->cardLoadNew($param);
        } catch (Exception $e) {
            print 'Error While performing load' . PHP_EOL;
            print_r($e);
        }
    }

//    
//    
//    
//    
//    
//    public function create()
//    {
//        $this->__server_listening = true; 
//
//        error_reporting(E_ALL); 
//        set_time_limit(0); 
//        ob_implicit_flush(); 
//        declare(ticks = 1); 
//
//        $this->become_daemon(); 
//
//        /* nobody/nogroup, change to your host's uid/gid of the non-priv user */ 
//        $this->change_identity(65534, 65534); 
//
//        /* handle signals */ 
//        
//        pcntl_signal(SIGTERM, 'sig_handler'); 
//        pcntl_signal(SIGINT, 'sig_handler'); 
//        pcntl_signal(SIGCHLD, 'sig_handler'); 
//
//        /* change this to your own host / port */ 
//        $this->server_loop($this->_address, $this->_port); 
//    }
//    
//    
//    
//    
//    public function change_identity( $uid, $gid ) 
//{ 
//    if( !posix_setgid( $gid ) ) 
//    { 
//        print "Unable to setgid to " . $gid . "!\n"; 
//        exit; 
//    } 
//
//    if( !posix_setuid( $uid ) ) 
//    { 
//        print "Unable to setuid to " . $uid . "!\n"; 
//        exit; 
//    } 
//} 
//
///** 
//  * Creates a server socket and listens for incoming client connections 
//  * @param string $address The address to listen on 
//  * @param int $port The port to listen on 
//  */ 
//public function server_loop($address, $port) 
//{ 
//    //$this->__server_listening; 
//
//    if(($sock = socket_create(AF_INET, SOCK_STREAM, 0)) < 0) 
//    { 
//        echo "failed to create socket: ".socket_strerror($sock)."\n"; 
//        exit(); 
//    } 
//
//    if(($ret = socket_bind($sock, $address, $port)) < 0) 
//    { 
//        echo "failed to bind socket: ".socket_strerror($ret)."\n"; 
//        exit(); 
//    } 
//
//    if( ( $ret = socket_listen( $sock, 0 ) ) < 0 ) 
//    { 
//        echo "failed to listen to socket: ".socket_strerror($ret)."\n"; 
//        exit(); 
//    } 
//
//    socket_set_nonblock($sock); 
//    
//    echo "waiting for clients to connect\n"; 
//
//    while ($this->__server_listening) 
//    { 
//        $connection = @socket_accept($sock); 
//        if ($connection === false) 
//        { 
//            usleep(100); 
//        }elseif ($connection > 0) 
//        { 
//            handle_client($sock, $connection); 
//        }else 
//        { 
//            echo "error: ".socket_strerror($connection); 
//            die; 
//        } 
//    } 
//} 
//
///** 
//  * Signal handler 
//  */ 
//public function sig_handler($sig) 
//{ 
//    switch($sig) 
//    { 
//        case SIGTERM: 
//        case SIGINT: 
//            exit(); 
//        break; 
//
//        case SIGCHLD: 
//            pcntl_waitpid(-1, $status); 
//        break; 
//    } 
//} 
//
///** 
//  * Handle a new client connection 
//  */ 
//public function handle_client($ssock, $csock) 
//{ 
//    GLOBAL $__server_listening; 
//
//    $pid = pcntl_fork(); 
//
//
//    if ($pid == -1) 
//    { 
//        /* fork failed */ 
//        echo "fork failure!\n"; 
//        die; 
//    }elseif ($pid == 0) 
//    { 
//        /* child process */ 
//        $__server_listening = false; 
//        socket_close($ssock); 
//        $this->interact($csock); 
//        socket_close($csock); 
//    }else 
//    { 
//        socket_close($csock); 
//    } 
//} 
//
//public function interact($socket) 
//{ 
//    /* TALK TO YOUR CLIENT */ 
//                if (false === ($buf = socket_read($socket, 2048))) {
//                    print "socket_read() failed: reason: " . socket_strerror(socket_last_error($socket)) . "\n";
//                    //break 2;
//                }
//               
//                $talkback = '';
//                $resp = '';
//                    
//                    //$resp = $this->getErrorMsg() == ''? $this->getErrorMsg() : 'Invalid Message: ' . $buf;
//                $talkback .= "ISO Recived from ECS and Sending to MVC ". date("mdHis"). "\n";                    
//                $talkback .= "ISO :" . $buf . "\n";
//
//                $this->logMessage('ISO Recived from ECS and Sending to MVC');
//                $this->logMessage($buf);
//                
//                $resp = $this->connectMVCServer($buf);
//                // $resp = $buf;
//                //$resp = '0045ISO016000075081082200000020000000400000000000000051503395812102400301';
//                //$resp = $this->getDummyResponse();
//                //$resp = '009308104230008000000002164444555566667777051713571123139500000012028Fri May 17 13:57:11 IST 2013';
//                $this->logMessage('ISO Recived from MVC and Sending to ECS');
//                $this->logMessage($resp);
//                
//                $talkback .= "ISO Recived from MVC and Sending to ECS " . date("mdHis"). "\n";
//                $talkback .= "ISO :" . $resp . "\n";
//                $talkback .= "Lenght default : " . strlen($resp) . "\n";
//                $talkback .= "Lenght mb : " . $this->getSize($resp) . "\n";
//                $talkback = $talkback . "\n";
//
//                socket_write($socket, $resp, strlen($resp));
//                print "$talkback\n";    
//} 
//
///** 
//  * Become a daemon by forking and closing the parent 
//  */ 
//public function become_daemon() 
//{ 
//    $pid = pcntl_fork(); 
//    
//    if ($pid == -1) 
//    { 
//        /* fork failed */ 
//        echo "fork failure!\n"; 
//        exit(); 
//    }elseif ($pid) 
//    { 
//        /* close the parent */ 
//        exit(); 
//    }else 
//    { 
//        /* child becomes our daemon */ 
//        posix_setsid(); 
//        chdir('/'); 
//        umask(0); 
//        return posix_getpid(); 
//
//    } 
//} 
    
    
 public function convert2ascii($buffer)   
 {
    $length = strtoupper(bin2hex(mb_substr($buffer, 0, 2)));
    $p2 = mb_substr($buffer, 2,16);
    $p3 = bin2hex(mb_substr($buffer, 18,16));
    $p4 = mb_substr($buffer, 34);
    $iso = $length . $p2 . $p3 . $p4;
    
    $iso8583	= new App_ISO_ISO8583();          
    $iso8583->addISOwithHeader($iso);
    if($iso8583->getMTI() == '0100') {
        $length = strtoupper(bin2hex(mb_substr($buffer, 0, 2)));
        $p2 = mb_substr($buffer, 2,16);
        //$p3 = bin2hex(mb_substr($buffer, 18,32));
        $p3 = bin2hex(mb_substr($buffer, 18,16));
        $p4 = mb_substr($buffer, 34);
        $iso = $length . $p2 . $p3 . $p4;
    } elseif ($iso8583->getMTI() == '0120' || $iso8583->getMTI() == '0130') {
        $length = strtoupper(bin2hex(mb_substr($buffer, 0, 2)));
        $p2 = mb_substr($buffer, 2,16);
        //$p3 = bin2hex(mb_substr($buffer, 18,32));
        $p3 = bin2hex(mb_substr($buffer, 18,8));
        $p4 = mb_substr($buffer, 26);
        $iso = $length . $p2 . $p3 . $p4;
    }
    return $iso;
    //print $iso;
 }
 
 public function validateMessage($input)
 {
    $asciiInput = $this->convert2ascii($input);    
    $output = $this->validateMessage2($asciiInput);
    print 'output :' . $output.PHP_EOL;
    $this->logMessage($output);
    return $output;
 }
 
 function filterIso($isoMsg,$msgt='0800') {
     if ($msgt == '0120' || $msgt == '0130') {
            $data = '';
            $data = hex2bin(mb_substr($isoMsg, 0, 4));
            $data2 = (mb_substr($isoMsg, 4, 16));
            $data3 = hex2bin(mb_substr($isoMsg, 20, 16));
            $data4 = (mb_substr($isoMsg, 36));
            $msg = $data2 . $data3 . $data4;
        } else {
            $data = '';
            $data = hex2bin(mb_substr($isoMsg, 0, 4));
            $data2 = (mb_substr($isoMsg, 4, 16));
            $data3 = hex2bin(mb_substr($isoMsg, 20, 32));
            $data4 = (mb_substr($isoMsg, 52));
            $msg = $data2 . $data3 . $data4;
        }
//    print 'Data Response in Binary : '.$data.PHP_EOL;
//    print 'LENGTH IN BINARY : '.strlen($msg).PHP_EOL;
//    print 'LENGTH IN BINARY OF MSG: '.strlen($data2 . $data3 . $data4).PHP_EOL;
    return $this->filterLength($msg);
    return $msg;  
 }    
 
 public function filterLength($bMsg) {
     $len = dechex(strlen($bMsg));
       if(strlen($len) == 3 ) {
            $length = '0'.  ($len);
        } else {
            $length = '00'.  ($len);
        }
        print 'Message Binary Length :'.$length.PHP_EOL;
        return hex2bin($length).$bMsg;
 }
 public function validateMessage2(
         $isomsg)
 {
     $iso8583	= new App_ISO_ISO8583();          
     //$isomsg2= $isomsg;
     $iso8583->addISOwithHeader($isomsg);
     $mti = $iso8583->getMTI();
     print 'Message MTI *' . $mti.'*'.PHP_EOL;
     
     if($mti =='0100') {
           $iso8583_100	= new App_ISO_ISO8583();          
            $iso8583_100->addISOwithHeader($isomsg);                
            $a = $iso8583_100->getData();
            print_r($a);
            //$a['7'];
            //$obj = $this->generateEchoObj();
            //$obj = $this->generateAuthObj();
            $obj = $this->generateAuthObj2($a);
            //$obj->removeData('22');
            //$obj->removeData('43');
//            $obj->addMTI('0110');
//            $obj->addData('39', '00');
//            //$obj->addData('1', $a['1']);
//            $obj->addData('2', $a['2']);
//            $obj->addData('3', $a['3']);
//            $obj->addData('4', $a['4']);
//            $obj->addData('7', $a['7']);
//            $obj->addData('11', $a['11']);
//            $obj->addData('12', $a['12']);
//            $obj->addData('13', $a['13']);
//            $obj->addData('14', $a['14']);
//            //$obj->addData('17', $a['17']);
//            $obj->addData('18', $a['18']);
//            $obj->addData('19', $a['19']);
//            //$obj->addData('22', $a['22']);
//            $obj->addData('32', $a['32']);
//            $obj->addData('35', $a['35']);
//            $obj->addData('37', $a['37']);
//            $obj->addData('41', $a['41']);
//            $obj->addData('42', $a['42']);
//            $obj->addData('49', $a['49']);
            //$obj->addData('70', $a['70']);
            //return $obj->getISOwithHeaders();
          //Another way to try
            //$iso8583_100->addMTI('0110');
            //$iso8583_100->addData('39','00');
            //unset($obj);
            //$obj = $iso8583_100;
            //$obj->removeData('22');
            //$obj->removeData('43');            
            $objRes= $obj->getISOwithHeaders();
            
    print 'Explaining AUTH RES ISO DATA'.PHP_EOL;
    $this->logMessage('Explaining AUTH RES ISO DATA');   
    
    print 'RES ISO ASCII:'.$objRes.PHP_EOL;
    $this->logMessage('RES ISO ASCII:'.$objRes);
    print 'MTI :'.$obj->getMTI() . PHP_EOL;
    $this->logMessage('MTI :'.$obj->getMTI());       
    print 'DATA : ';
    print_r($obj->getData());
    $this->logMessage('DATA :');           
    $this->logMessage($obj->getData());           
    print  PHP_EOL;
    print  'BITMAP :';
    //print '<br /><br /><br />';
    print_r($obj->getBitmap());
    $this->logMessage('BITMAP :'.$obj->getBitmap());               
    print  PHP_EOL;
    
    print 'Explaining AUTH RES ISO DATA END'.PHP_EOL;
    $this->logMessage('Explaining AUTH RES ISO DATA END');                   
    
            return $this->filterIso($objRes,'0110');
        
     }
     if($mti =='0120') {
           $iso8583_100	= new App_ISO_ISO8583();          
            $iso8583_100->addISOwithHeader($isomsg);                
            $a = $iso8583_100->getData();
            print_r($a);

            $obj = $this->generateAuthAdviceObj2($a);
            $objRes= $obj->getISOwithHeaders();
            return $this->filterIso($objRes,'0120');
        
     }
     switch ($mti) {
            case '0800':
                
            //Need to Comment Following Lines    
            /*
            print ('Transaction in HOLD sending data to LOAD' );                                 
            $this->logMessage('Transaction in HOLD sending data to LOAD' );                                 
            $flg = $this->loadTest();
            print ('LOAD Done ' . $flg  ).PHP_EOL;                                 
            $this->logMessage('LOAD Done '. $flg ); 
            */
            //Comment Lines
            
             //$iso8583->addMTI('0810');
             //$iso8583->addData(39, '00');
             //return $iso8583->getISOwithHeaders();
                //$iso8583->getData()
                //print $iso8583->getData()."**".PHP_EOL;
            //$iso8583_2->addMTI('0810');
                $a = $iso8583->getData();
                $a['7'];
            $obj = $this->generateEchoObj();
            
            $obj->addData('39', '00');
            $obj->addData('7', $a['7']);
            $obj->addData('11', $a['11']);
            $obj->addData('70', $a['70']);
            //$iso8583_2->addData('39', '00');
            //$iso8583_2->addData('7', $a['7']);
            //$iso8583_2->addData('11', $a['11']);
            //$iso8583_2->addData('70', $a['70']);
            $objRes = $obj->getISOwithHeaders();
            return $this->filterIso($objRes);
            break;
        
            case '0200':
            $iso8583->addMTI('0210');
            break;
        
        
        
            case '0120':
            $iso8583->addMTI('0121');
            break;
            case '0220':
            $iso8583->addMTI('0221');
            break;
         
     }
     
     $iso8583->addData(39, '00');
     return $iso8583->getISOwithHeaders();
 }
 
 
     protected function generateEchoObj() {
         
         $iso8583 = new App_ISO_ISO8583();
        //$preLenght      = bin2hex()
        //$param = $this->getLogonParam();
        //$length         =   isset($param['length']) ? $param['length'] : '0043';
        $isoLiterals    =   'ISO';
        $header         =   '006000075';
        $mti            =   '0810';
        $primary        =   '8220000000000000';
        $p1             =    '0400000000000000';
        $p7             =   isset($param['p7']) ? $param['p7'] : '';
        $p11            =   isset($param['p11']) ? $param['p11'] : '';
        //$p70            =   isset($param['p70']) ? $param['p70'] : '301';
        $p70            =   '301';

        $iso8583->addMTI($mti);
        $iso8583->addData(7, $p7);
        $iso8583->addData(11, $p11);
        $iso8583->addData(70, $p70);
        $iso8583->addData(1, $p1);
        $iso8583->addISOLiterals($isoLiterals);
        //$iso8583->addMessage($length);
        $iso8583->addAdditionalHeader($header);        

        return $iso8583;
        
    }
    
     protected function generateAuthObj() {
         
         $iso8583 = new App_ISO_ISO8583();
        //$preLenght      = bin2hex()
        //$param = $this->getLogonParam();
        //$length         =   isset($param['length']) ? $param['length'] : '0043';
        $isoLiterals    =   'ISO';
        $header         =   '006000075';
        $mti            =   '0110';
        $primary        =   '8220000000000000';
        $p1             =    '0400000000000000';
        //$p7             =   isset($param['p7']) ? $param['p7'] : '';
        //$p11            =   isset($param['p11']) ? $param['p11'] : '';
        //$p70            =   isset($param['p70']) ? $param['p70'] : '301';
        //$p70            =   '301';

        $iso8583->addMTI($mti);
        //$iso8583->addData(7, $p7);
        //$iso8583->addData(11, $p11);
        //$iso8583->addData(70, $p70);
        $iso8583->addData(1, $p1);
        $iso8583->addISOLiterals($isoLiterals);
        //$iso8583->addMessage($length);
        $iso8583->addAdditionalHeader($header);        

        return $iso8583;
        
    }

    
    function generateAuthObj2($arr) {
         //print_r($arr);
         //exit;
         $iso8583 = new App_ISO_ISO8583();
        //$preLenght      = bin2hex()
        //$param = $this->getLogonParam();
        //$length         =   isset($param['length']) ? $param['length'] : '0043';
        $isoLiterals    =   'ISO';
        $header         =   '006000075';
        $mti            =   '0110';
        //$primary        =   '8220000000000000';
        //$p1             =    '0400000000000000';

        $iso8583->addMTI($mti);
        //$iso8583->addData(7, $p7);
        //$iso8583->addData(11, $p11);
        //$iso8583->addData(70, $p70);
        //$iso8583->addData(1, $p1);
        foreach ($arr as $key => $value) {
            //print $key . ' : '.$value . PHP_EOL;
//            if($key == '126') {
//               $iso8583->addData($key, '');
//            } else {
               $iso8583->addData($key, $value);
            //}
        }
        //Approve all card transactions
       // if($arr['2'] =='4199532815827956' || $arr['2'] == '4199531845530887') {
             $iso8583->addData(39, '00');
             $iso8583->addData(102, '3333330000002470');
             
            print ('Transaction in HOLD sending data to LOAD' );                                 
            $this->logMessage('Transaction in HOLD sending data to LOAD' );                                 
            $flg = $this->loadTest();
            print ('LOAD Done ' . $flg  ).PHP_EOL;                                 
            $this->logMessage('LOAD Done '. $flg );                    
             
             
//        } else {
//             $iso8583->addData(39, '96');
//        }
        //$iso8583->removeData(126);
//exit;

        $iso8583->addISOLiterals($isoLiterals);
        //$iso8583->addMessage($length);
        $iso8583->addAdditionalHeader($header);        
        //print $iso8583->getISOwithHeaders();exit;
        return $iso8583;
        
    }
    
    function generateAuthAdviceObj2($arr) {
         //print_r($arr);
         //exit;
         $iso8583 = new App_ISO_ISO8583();
        //$preLenght      = bin2hex()
        //$param = $this->getLogonParam();
        //$length         =   isset($param['length']) ? $param['length'] : '0043';
        $isoLiterals    =   'ISO';
        $header         =   '006000075';
        $mti            =   '0130';
        //$primary        =   '8220000000000000';
        //$p1             =    '0400000000000000';

        $iso8583->addMTI($mti);
        //$iso8583->addData(7, $p7);
        //$iso8583->addData(11, $p11);
        //$iso8583->addData(70, $p70);
        //$iso8583->addData(1, $p1);
        foreach ($arr as $key => $value) {
               $iso8583->addData($key, $value);
        }
        $iso8583->addData(39, '00');
        //$iso8583->removeData(126);
//exit;

        $iso8583->addISOLiterals($isoLiterals);
        //$iso8583->addMessage($length);
        $iso8583->addAdditionalHeader($header);        
        //print $iso8583->getISOwithHeaders();exit;
        return $iso8583;
        
    }

 
}