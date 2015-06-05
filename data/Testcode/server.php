     <?php

create();	 
	 
function create() {	 
        error_reporting(E_ALL);
        ob_implicit_flush();
        $sock = socket_create(AF_INET, SOCK_STREAM, 0);
        if (socket_bind($sock, '192.168.2.153', '1111') === false) {
            if (!socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1)) {
                print socket_strerror(socket_last_error($sock));
                exit;
            }
        }
        if (socket_listen($sock, 5) === false) {
            print "socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
        }
        do {
            if (($msgsock = socket_accept($sock)) === false) {
                print "socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
                break;
            }
            $msg = "\nPOC-ISO Telnet Test. \n" .
                    "Enter 'quit' to exit\n";
            socket_write($msgsock, $msg, strlen($msg));
            do {
                if (false === ($buf = socket_read($msgsock, 2048))) {
                    print "socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
                    break 2;
                }
                if (!$buf = trim($buf)) {
                    continue;
                }
                if ($buf == 'quit') {
                    break;
                }
                //$msg = $this->getMessageType($buf);
                $resp = '';
				/*
                if ($buf !== false) {
				*/
                    switch ($buf) {

                        case 'quit':
                            break 2;
						case '0067ISO0060000750800822000000000000004000000000000000904072533101033001':
							$talkback =  '0088ISO0060000750800822000000000000004000000000000000904072533101033001';
							//$talkback = $talkback . "\n";
							break;
						case '0067ISO0060000750800822000000000000004000000000000000623072533101033301':
							$talkback =  '0069ISO006000075081082200000020000000400000000000000062307253310103300301';
							//$talkback = $talkback . "\n";	
							break;						
							

                    }//END Switch
					if($talkback == '') {
						$talkback = $buf;
					}
				/*
                } else {//End IF
                    //print "Generated ISO : " . $iso8583_v2->getISOwithHeaders();
                    //$resp = $this->getErrorMsg() == ''? $this->getErrorMsg() : 'Invalid Message: ' . $buf;
                    //print "ISO Message : " . $buf . "\n";
					$talkback = 'Invalid Message ' . $buf;
                } */

                print "ISO Message : " . $buf . "\n";     
			   // $talkback = $buf;
                //$talkback = 'Response Server: ' . $resp;
                //$talkback =  '0088ISO0060000750800822000000000000004000000000000000904072533101033001';
                //$talkback = $talkback . "\n";
                //$talkback = '0069ISO006000075081082200000020000000400000000000000062307253310103300001';
				//$talkback = $buf;
                socket_write($msgsock, $talkback, strlen($talkback));
                //===========================================
                print "Response: $talkback\n";
            } while (true);
            socket_close($msgsock);
        } while (true);
        socket_close($sock);
	 }
	 
	 