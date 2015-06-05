<?php
class App_Socket_ECS_Transaction extends App_Socket_ECS_Authentificator {


    public static $auditNumber;
    
    private $attempts = 0;
    private $maxAttempts = 3;
    
    private $loadAttempts = 0;
    private $loadMaxAttempts = 2;
    
    private $reversalAttempts = 0;
    private $reversalMaxAttempts = 2;
    private $_allowed_Calls = array ('0100','0800','0120');
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * createNewSession
     * 
     * App_Api_Exception exception. Returns void.
     *
     * @return \App_Api_ECS_Authentificator
     * */
    public function sendDatatoECS($iso) {
        
        try {
                 //$this->initSession();
           $this->createConnectionObject();
           // if($this->initSession() === true) {
           //print 'HERE';
                $resp = $this->getSocketObject()->isoCall("Transaction", $iso);    
                
                //print 'AAAAA :'.$resp;exit;
                //echo '**'. $resp . '**';exit;
                $respISO = $this->getSocketObject()->getLastResponse();
                //print $resp . ' : ' . $respISO;exit;
                  //return '**' .$respISO . '**';exit;
                //print "Response Recivedxx :" . $respISO . "\n";
                $this->parseResponseISO($respISO);
                return $respISO;
                
        return false;
        } catch (Exception $e) {
            print '<pre>';
            print_r($e);
            return false;
        }
    }
    
//Depricated Old function New function created with same name
/*    
public function convert2ascii($buffer)   
 {
    $length = strtoupper(bin2hex(mb_substr($buffer, 0, 2)));
    $p2 = mb_substr($buffer, 2,16);
    $p3 = bin2hex(mb_substr($buffer, 18,16));
    $p4 = mb_substr($buffer, 34);
    $iso = $length . $p2 . $p3 . $p4;
    
    $iso8583	= new App_ISO_ISO8583();          
    $iso8583->addISOwithHeader($iso);
    print PHP_EOL;
    print 'START'.PHP_EOL;
    print 'BUFFER : '.$buffer;
    print PHP_EOL;

    print 'ISO : '.$iso;
    print PHP_EOL;
    print_r($iso8583->getData());
    print 'END'.PHP_EOL;
    print PHP_EOL;
   if( $iso8583->getMTI() == '0120' || $iso8583->getMTI() == '0130') {
        $length = strtoupper(bin2hex(mb_substr($buffer, 0, 2)));
            $p2 = mb_substr($buffer, 2,16);
            $p3 = bin2hex(mb_substr($buffer, 18,8));
            $p4 = mb_substr($buffer, 26);
            $iso = $length . $p2 . $p3 . $p4;
    }
    return $iso;
    //print $iso;
 }
 */
    
    
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
    /**
     * validateSessionByCron
     * Method used to call from CRON
     * @return boolean
     */
    public function validateSessionByCron() {
        
        //Get Last Successful Session ID
        $apiSession    = new ApiSession();
        $apiModel   = new APISettings();
        //$iso8583    = new App_ISO_ISO8583();
        $resp       = $apiSession->getLastSession(TP_ECS_ID);
        $param      = array();
        $flg        = false;
        $this->attempts++;        
        if(!$this->_createConnection() == false) {
            
            if(!empty($resp)) {
            //print 'here';exit;    
                //Generate LOGON ISO
                $iso = $this->generateEchoISO();
                //Send Logon Request                
                //exit('here');
                //$iso = '009308004230008000000002164444555566667777052112202086415900000012028Tue May 21 12:20:20 IST 2013';
                //$iso = file_get_contents("C:\\xampp\\htdocs\\iso800.log");                
                //$iso = file_get_contents("C:\\xampp\\htdocs\\iso100respdummy2.log");                
                //print $iso.'**'.PHP_EOL;
                //print 'Sending LOGON ISO'.PHP_EOL;
                $flg = $this->ecsISOCall(ECS_ISO_ECHO,$iso);   
                
                //if(!$flg) {
                    //Need to Log this error
                  //  $flg = false;
                //} else {
                    $responseISO = $this->getResponse();
//                    print 'Length :'.strlen($responseISO).PHP_EOL;
//                    print 'LENGTH :'.$hex = bin2hex(mb_substr($responseISO, 0, 2)).PHP_EOL;
//                    print 'CONVERTING LENGTH :'.hexdec($hex).PHP_EOL;                    
//                    print 'RECV ECHO Response ISO:'.$responseISO.PHP_EOL;
//                    $this->printiso($responseISO);
//                    $file = fopen("C:\\xampp\\htdocs\\iso110-res.log", 'a', 1);
//    
//                    fwrite($file, $responseISO); 
//                    fclose($file);                    
                    //exit;
/*                $iso = file_get_contents("C:\\xampp\\htdocs\\iso800.log");                
                //$iso = file_get_contents("C:\\xampp\\htdocs\\iso100respdummy2.log");                
                print $iso.'**'.PHP_EOL;
                print 'ECHO sending ISO'.PHP_EOL;
                $flg = $this->ecsISOCall(ECS_ISO_ECHO,$iso);   
                
                //if(!$flg) {
                    //Need to Log this error
                  //  $flg = false;
                //} else {
                    $responseISO = $this->getResponse();
                    print 'Response ISO:'.$responseISO.PHP_EOL;
                    $this->printiso($responseISO);
                    $file = fopen("C:\\xampp\\htdocs\\iso110-res.log", 'a', 1);
    
                    fwrite($file, $responseISO); 
                    fclose($file);                    
                    //exit;
 * 
 */
                //$iso = file_get_contents("C:\\xampp\\htdocs\\iso800.log");                
//                $iso = file_get_contents("C:\\xampp\\htdocs\\iso100success.log");                
//                //$iso = file_get_contents("C:\\xampp\\htdocs\\iso100fail.log");                
//                print $iso.'**'.PHP_EOL;
//                print 'Sending AUTH ISO'.PHP_EOL;
//                $flg = $this->ecsISOCall(ECS_ISO_ECHO,$iso);   
                
                //if(!$flg) {
                    //Need to Log this error
                  //  $flg = false;
                //} else {
//                    $responseISO = $this->getResponse();
//                    print 'Length :'.strlen($responseISO).PHP_EOL;
//                    print 'LENGTH :'.$hex = bin2hex(mb_substr($responseISO, 0, 2)).PHP_EOL;
//                    print 'CONVERTING LENGTH :'.hexdec($hex).PHP_EOL;
//                    print 'RECV AUTH Response ISO:'.$responseISO.PHP_EOL;
//                    $this->printiso($responseISO);
//                    $file = fopen("C:\\xampp\\htdocs\\iso110-res.log", 'a', 1);
//    
//                    fwrite($file, $responseISO); 
//                    fclose($file);                    
//                    //exit;
//                $iso = file_get_contents("C:\\xampp\\htdocs\\iso120aa.log");                
//                //$iso = file_get_contents("C:\\xampp\\htdocs\\iso100fail.log");                
//                print $iso.'**'.PHP_EOL;
//                print 'Sending AUTH Advice ISO'.PHP_EOL;
//                $flg = $this->ecsISOCall(ECS_ISO_ECHO,$iso);   
                
                //if(!$flg) {
                    //Need to Log this error
                  //  $flg = false;
                //} else {
//                    $responseISO = $this->getResponse();
//                    print 'Length :'.strlen($responseISO).PHP_EOL;
//                    print 'LENGTH :'.$hex = bin2hex(mb_substr($responseISO, 0, 2)).PHP_EOL;
//                    print 'CONVERTING LENGTH :'.hexdec($hex).PHP_EOL;
//                    print 'RECV AUTH Response ISO:'.$responseISO.PHP_EOL;
//                    $this->printiso($responseISO);
//                    $file = fopen("C:\\xampp\\htdocs\\iso130-res.log", 'a', 1);
//    
//                    fwrite($file, $responseISO); 
//                    fclose($file);                    
//                    exit;
                    if($this->validateLogonResponse($responseISO)) {
                        $flg =true;
                    }
                //}
            } else {
            //print 'here2';exit;    
                //Generate LOGON ISO
                $iso = $this->generateLogonISO();
                //print $iso.'**'.PHP_EOL;
                //print 'Logon sending ISO'.PHP_EOL;
//                //$iso = file_get_contents("C:\\xampp\\htdocs\\iso800.log");                
//                $iso = file_get_contents("C:\\xampp\\htdocs\\iso100respdummy2.log");                
//                print $iso.'**'.PHP_EOL;
//                print 'ECHO sending ISO'.PHP_EOL;
                //print 'here';exit;
                //Send Logon Request                
                $flg = $this->ecsISOCall(ECS_ISO_LOGON,$iso);            
                //print 'here22';exit;
                if(!$flg) {
                    //Need to Log this error
                    $flg = false;
                } else {
                    $responseISO = $this->getResponse();
                    //print 'Respone ISO : '.$responseISO;
                    if($this->validateLogonResponse($responseISO)) {
                        $flg =true;
                    }
                }
            }
        } 
        
        //if failed try until allowed attempts
        if($flg == false) {
            if($this->attempts < $this->maxAttempts) {
                return self::validateSessionByCron();
            }
        }
        
        //Close the connection
        $this->_closeConnection();//void return nothing
        
        //$apiModel = new APISettings();
        $param['type'] = SETTING_API_ECS; // OR SETTING_API_MVC OR SETTING_API_ISO
        $param['value'] = ($flg == false) ? 0 : 1; // anything other than 1 will be 0
        $apiModel->updateSetting($param); // return true/false
        
        $status = ($flg == false) ? 'failure' : 'success'; // setting status
        $apiSession->updateSession(array(
            'status'    => $status,
            'sessionId' => '',
            'userId'    => TP_ECS_ID
        )); // return true/false
        
        if($flg == FALSE) {
            $m = new App\Messaging\MVC\Axis\Operation();
            $m->cronAlert(array(
                'cronName' => 'ECS ISO Validator',
                'message'   =>  'Error : ' . $this->getError()
            ));
        }
        

        //$flg = $this->initSession();
        return $flg;
    }
    
    /**
     * Card Load
     * @param array $param
     * @return Boolean
     * @throws App_Api_Exception
     */
    public function cardLoad(array $param) {

        if(!isset($param['amount']) || $param['amount'] == '') {
            throw new App_Api_Exception ("Invalid amount passed ". $param['amount']);
        }
        
        if(!isset($param['crn']) || $param['crn'] == '') {
            throw new App_Api_Exception ("Invalid CRN number ");
        }
        
        if(!isset($param['transactionId']) || $param['transactionId'] == '') {
            throw new App_Api_Exception ("Invalid transaction id ");
        }

        $agentId = isset($param['agentId']) ? $param['agentId'] : substr($param['agentId'],-8);
        
        //$transactionId = isset($param['agentId']) ? $param['agentId'] : ;        
        $reqParam['amount'] = $this->filterAmount($param['amount']);
        
        $reqParam['countryCode'] = isset($param['countryCode']) ? $param['countryCode'] : COUNTRY_IN_CODE;//Country Code
        $reqParam['merchantCode'] = '5999';//Merchant Code
        $reqParam['aiiCode'] = '419953'; //Acquiring Institution Identification Code
        //passing transaction Id in Retrieval Regerence No
        $reqParam['rrNo'] = sprintf("%012d", $param['transactionId']);///'000000005113';P-37 Retrieval Reference No
        //$param['agentId'] = substr($param['agentId'], $start);
        //Passing 8 digit agent code
        $reqParam['agentId'] = sprintf("%08d", $agentId);//41Card Acceptor Terminal No Or Agent Id in case of transaction coming through Agent portal
        $reqParam['caId'] = '300100011234567';//DE 42 Card Acceptor Identification        
        
        $reqParam['crn']    = isset($param['crn']) ? $param['crn'] : '';
        $reqParam['stan']    = $this->generateTraceAuditNumber();
        $reqParam['currency']= isset($param['currencyCode']) ? $param['currencyCode'] : CURRENCY_INR_CODE;
        
        //$reqParam['amount'] = isset($param['amount']) ? $param['amount'] : '';
            //Login
                //Create Connection
        //return $this->cardLoadTransaction($reqParam);
        if($this->_createConnection() == false) {
            //print 'here';exit;
               return false;
        }
           //Generate LOGON ISO
           $loginISO = $this->generateEchoISO();
           //Send Logon Request                
           $flg = $this->ecsISOCall(ECS_ISO_ECHO,$loginISO);            
            //Recreating Connection As failing to write over socket on same session
            $this->_closeConnection();   
            $responseISO = $this->getResponse();
            //print 'Login response:'.$responseISO.PHP_EOL;exit;
            if($flg) {
                if($this->validateLogonResponse($responseISO)) {     
                    $this->loadAttempts = 0;
                    //Do the transaction                        
                    return $this->cardLoadTransaction($reqParam);
                }
            }

        return false;

           
    }
      
    /**
     * Card Load
     * @param array $param
     * @return Boolean
     * @throws App_Api_Exception
     */
    public function cardLoadNew(array $param) {

        if(!isset($param['amount']) || $param['amount'] == '') {
            throw new App_Api_Exception ("Invalid amount passed ". $param['amount']);
        }
        
        if(!isset($param['crn']) || $param['crn'] == '') {
            throw new App_Api_Exception ("Invalid CRN number ");
        }
        
        if(!isset($param['transactionId']) || $param['transactionId'] == '') {
            throw new App_Api_Exception ("Invalid transaction id ");
        }

        $agentId = isset($param['agentId']) ? $param['agentId'] : substr($param['agentId'],-8);
        
        //$transactionId = isset($param['agentId']) ? $param['agentId'] : ;        
        $reqParam['amount'] = $this->filterAmount($param['amount']);
        
        $reqParam['countryCode'] = isset($param['countryCode']) ? $param['countryCode'] : COUNTRY_IN_CODE;//Country Code
        $reqParam['merchantCode'] = '5999';//Merchant Code
        $reqParam['aiiCode'] = '419953'; //Acquiring Institution Identification Code
        //passing transaction Id in Retrieval Regerence No
        $reqParam['rrNo'] = sprintf("%012d", $param['transactionId']);///'000000005113';P-37 Retrieval Reference No
        //$param['agentId'] = substr($param['agentId'], $start);
        //Passing 8 digit agent code
        $reqParam['agentId'] = sprintf("%08d", $agentId);//41Card Acceptor Terminal No Or Agent Id in case of transaction coming through Agent portal
        $reqParam['caId'] = '300100011234567';//DE 42 Card Acceptor Identification        
        
        $reqParam['crn']    = isset($param['crn']) ? $param['crn'] : '';
        $reqParam['stan']    = $this->generateTraceAuditNumber();
        $reqParam['currency']= isset($param['currencyCode']) ? $param['currencyCode'] : CURRENCY_INR_CODE;
        
        //$reqParam['amount'] = isset($param['amount']) ? $param['amount'] : '';
            //Login
                //Create Connection
        //return $this->cardLoadTransactionNew($reqParam);
        //return $this->cardLoadTransaction($reqParam);
        if($this->_createConnection() == false) {
            //print 'here';exit;
            print 'Unable to connect';
               //return false;
        }
           //Generate LOGON ISO
           $loginISO = $this->generateEchoISO();
           //print $loginISO;exit;
           //Send Logon Request                
           $flg = $this->ecsISOCall(ECS_ISO_ECHO,$loginISO);            
            //Recreating Connection As failing to write over socket on same session
            $this->_closeConnection();   
            $responseISO = $this->getResponse();
            //print 'Login response:'.$responseISO.PHP_EOL;exit;
            if($flg) {
                if($this->validateLogonResponse($responseISO)) {     
                    $this->loadAttempts = 0;
                    //Do the transaction                        
                    return $this->cardLoadTransactionNew($reqParam);
                }
            }

        return false;

           
    }
      
    /**
     * CardLoadTransaction
     * Function to initiate Card load transaction
     * @param type $param
     * @return boolean
     */
    public function cardLoadTransactionNew($param) {
        print 'Finally Reach to to Card Load Transaction'.PHP_EOL;
        //$iso8583    = new App_ISO_ISO8583();
        //Update No. of attempts
        $this->loadAttempts++;
        $flg = false;
           //print $iso = $this->generateFirstCardLoadISO($param);exit;
        if($this->_createConnection() == false) {
            print 'Unable to connect';
            return false;
        }
        //print '<pre>';
        //print_r($param);exit;
        //print '</pre>';
        //$iso = $this->generateFirstCardLoadISONew($param);
        $iso = '0097ISO01600007502007238E00108C0800016'.trim($param['crn']).'870000000000030000'.date('mdHis').$param['stan'].date('His').date('md').date('md').'59993560641995300007057506230010001300100011234567356';
        //print $iso;exit;
        $this->setRequestISO($iso);                            
        //print 'LOAD ISO: '.$iso.PHP_EOL;exit;
        //print 'ISO : '.$this->getRequestISO()."\n";exit('HERE');
        $flg = $this->ecsISOCall(ECS_ISO_CARDLOAD,$iso);     
        
        //Close Connection        
        $this->_closeConnection();        
       //Send Logon Request
        if($flg) {
            $responseISO = $this->getResponse();
            if($responseISO != '') {
                //Validate Echo Message Response    
                $flg = $this->validateCardLoadResponse($responseISO);
            }
            //No Response 
            //if($responseISO == '' || $flg == '' || $flg === FALSE ) {
            if($responseISO == '' || !$this->validateCardLoadResponseForReversal($responseISO) ) {		            
                $this->reversalAttempts = 0;
                $this->cardLoadReversalTransaction($iso, $param);
            }
        }                    

        if($responseISO == '' || $flg == '' || $flg === FALSE) {
            $alertArray = array(
                'Response Message ' => $this->getMessage(),
                'Process ' => 'Card Load failure'
            );
            $alertArray = array_merge($alertArray,$param );
            $m = new App\Messaging\MVC\Axis\Operation();
            $m->loadFail($alertArray);
            App_Logger::log($alertArray, Zend_Log::ERR);
        }
        
        if($flg == false) {
            if($this->isValidForLoadRetry($responseISO)) {
                if($this->loadAttempts < $this->loadMaxAttempts) {
                    //Recursive function call
                    return self::cardLoadTransaction($param);
                }
            }
        }
        return $flg;
    }
    /**
     * CardLoadTransaction
     * Function to initiate Card load transaction
     * @param type $param
     * @return boolean
     */
    public function cardLoadTransaction($param) {
        //print 'Finally Reach to to Card Load Transaction'.PHP_EOL;
        //$iso8583    = new App_ISO_ISO8583();
        //Update No. of attempts
        $this->loadAttempts++;
        $flg = false;
           //print $iso = $this->generateFirstCardLoadISO($param);exit;
        if($this->_createConnection() == false) {
            return false;
        }
        //print '<pre>';
        //print_r($param);
        //print '</pre>';
        $iso = $this->generateFirstCardLoadISO($param);
        $this->setRequestISO($iso);                            
        //print 'LOAD ISO: '.$iso.PHP_EOL;exit;
        //print 'ISO : '.$this->getRequestISO()."\n";exit('HERE');
        $flg = $this->ecsISOCall(ECS_ISO_CARDLOAD,$iso);     
        
        //Close Connection        
        $this->_closeConnection();        
       //Send Logon Request
        if($flg) {
            $responseISO = $this->getResponse();
            if($responseISO != '') {
                //Validate Echo Message Response    
                $flg = $this->validateCardLoadResponse($responseISO);
            }
            //No Response 
            //if($responseISO == '' || $flg == '' || $flg === FALSE ) {
            if($responseISO == '' || !$this->validateCardLoadResponseForReversal($responseISO) ) {		            
                $this->reversalAttempts = 0;
                $this->cardLoadReversalTransaction($iso, $param);
            }
        }                    

        if($responseISO == '' || $flg == '' || $flg === FALSE) {
            $alertArray = array(
                'Response Message ' => $this->getMessage(),
                'Process ' => 'Card Load failure'
            );
            $alertArray = array_merge($alertArray,$param );
            $m = new App\Messaging\MVC\Axis\Operation();
            $m->loadFail($alertArray);
            App_Logger::log($alertArray, Zend_Log::ERR);
        }
        
        if($flg == false) {
            if($this->isValidForLoadRetry($responseISO)) {
                if($this->loadAttempts < $this->loadMaxAttempts) {
                    //Recursive function call
                    return self::cardLoadTransaction($param);
                }
            }
        }
        return $flg;
    }

    /**
     * 
     */
    public function cuttOffReversal($param) {
        if(!isset($param['crn']) || empty($param['crn'])) {
            throw new App_Exception('Invalid CRN');
        }
        
        if(!isset($param['amount']) || empty($param['amount'])) {
            throw new App_Exception('Invalid Amount');
        }
        
        if(!isset($param['txn_load_id']) || empty($param['txn_load_id'])) {
            throw new App_Exception('Invalid Transaction Id');
        }
        $isoModel = new ApiISOCall();
        $txnInfo = $isoModel->getInfoById($param['txn_load_id']);
        if(empty($txnInfo['request'])) {
            throw new App_Exception('Invalid Transaction');            
        }
        return $this->cardLoadReversalTransaction($txnInfo['request'], array(
            'crn'   => $param['crn'],
            'amount'   => $param['amount']
        ));
    }

      /**
     * CardLoadTransaction
     * Function to initiate Card load transaction
     * @param type $param
     * @return boolean
     */
    //Making it public so it can be called from HELPDESK module
    public function cardLoadReversalTransaction($loadIso, $param) {
        //Update No. of attempts
        $this->reversalAttempts++;
        $flg = false;
//        $m = new App\Messaging\MVC\Axis\Operation();   
        if($this->_createConnection() == false) {
            return false;
        }

        $iso = $this->generateCardLoadReversalISO($loadIso,$param);
        $this->setRequestISO($iso);                            
        $flg = $this->ecsISOCall(ECS_ISO_CARDLOAD_REVERSAL,$iso);            
        if($flg) {
            $responseISO = $this->getResponse();
            $flg = $this->validateCardLoadReversalResponse($responseISO);
        }                    
        $this->_closeConnection();
        if($flg == false) {
             if($this->reversalAttempts < $this->reversalMaxAttempts) {
                 return $this->cardLoadReversalTransaction($loadIso,$param);
             }
         }
         
        if($flg == false) {
            $alertArray = array(
                'Response Message ' => $this->getMessage(),
                'Process ' => 'Card Load Reveresal Failure'
            );
            $alertArray = array_merge($alertArray,$param );
            
//            $m->loadFail($alertArray);
            App_Logger::log($alertArray, Zend_Log::ERR);
        } else {
            $alertArray = array(
                'Response Message ' => $this->getMessage(),
                'Process ' => 'Card Load Reveresal Successful'
            );
            $alertArray = array_merge($alertArray,$param );
//            $m->loadFail($alertArray);
            App_Logger::log($alertArray, Zend_Log::ERR);            
        }
        return $flg;
    }
    
    /**
 * Connection handler
 */
    public function onConnect($client) {
        $pid = pcntl_fork();

        if ($pid == -1) {
            die('could not fork');
        } else if ($pid) {
            // parent process
            return;
        }

        $read = '';
        printf("[%s] Connected from port %d\n", $client->getAddress(), $client->getPort());
        $cnt = 1;
        while (true) {
            $read = $client->read();
            /*if ($read !== null && $read != '') {
                print 'COUNTER:*' . $cnt . '*' . PHP_EOL;
                print 'Buffer:*' . $read . '*' . PHP_EOL;
                $cnt++;
                if ($this->validateLength($read)) {

                    $mti = $this->getMTI($read);
                    if ($mti != false) {
                        $resp = $this->validateRequest($mti, $read);
                        $client->send("[$mti] " . $resp);
                    }
                }
            }*/
            if ($read == '') {
                break;
            }

            if ($read === null) {
                printf("[%s] Disconnected\n", $client->getAddress());
                return false;
            } else {
                
                printf("[%s] recieved: %s", $client->getAddress(), $read);
                //printf( "[%s] recieved: %s", 'Client:'.client_ip(), $read );
                 if ($this->validateLength($read)) {
                    $mti = $this->getMTI($read);
                    if ($mti != false && $this->isCallAllowed($mti)) {
                        $resp = $this->validateRequest2($mti, $read);
                        $client->send("[$mti] " . $resp);
                    }
                }                
            }
        }
        $client->close();
        printf("[%s] Disconnected\n", $client->getAddress());
    }

    public function validateLength($buffer) {

        $length = strtoupper(bin2hex(mb_substr($buffer, 0, 2)));
        //print 'Length:'.$length.PHP_EOL;
        //print 'Length in DEC:'.hexdec($length).PHP_EOL;
        //print 'ISO Length:'.  mb_strlen($buffer).PHP_EOL;
        $len = hexdec($length) + 2;
        if(mb_strlen($buffer) != $len) {
            return false;
        } 
        return true;
    }
    
    public function getMTI($buffer) {

        $length = strtoupper(bin2hex(mb_substr($buffer, 0, 2)));
        $p2 = mb_substr($buffer, 2,16);
        $p3 = bin2hex(mb_substr($buffer, 18,16));
        $p4 = mb_substr($buffer, 34);
        $iso = $length . $p2 . $p3 . $p4;

        $iso8583	= new App_ISO_ISO8583();          
        $iso8583->addISOwithHeader($iso);
        return $iso8583->getMTI();
    }
     
    public function validateRequest2($mti,$buff) {
        $ascii = $this->convert2ascii($mti, $buff);
        $this->printData($ascii);
        $resp = $this->getResponse($mti, $buff, $ascii);
        return $this->formatResponseInBinary($resp);
    }
    
    private function isCallAllowed($mti) {
        if(in_array($mti, $this->_allowed_Calls)) {
            return TRUE;
        }
        return FALSE;
    }
    

    private function getBitmap($buffer,$type='b') {
        if($type == 'b') {
            $length = strtoupper(bin2hex(mb_substr($buffer, 0, 2)));
            $p2 = mb_substr($buffer, 2,16);
            $p3 = bin2hex(mb_substr($buffer, 18,16));
            $p4 = mb_substr($buffer, 34);
            $iso = $length . $p2 . $p3 . $p4;
        } else {
            $iso = $buffer;
        }
        $iso8583	= new App_ISO_ISO8583();          
        $iso8583->addISOwithHeader($iso);
        return $iso8583->getBitmap();
        
    }
    

 public function convert2ascii($mti,$buffer)   
 {
     
        $bitmap = $this->getBitmap($buffer,'b');
        //print "Bitmap:$bitmap".PHP_EOL;
        //print "BITMAP 1:$bitmap[0]".PHP_EOL;
        if(($bitmap[0])==1) {
            $length = strtoupper(bin2hex(mb_substr($buffer, 0, 2)));
            $p2 = mb_substr($buffer, 2,16);
            //$p3 = bin2hex(mb_substr($buffer, 18,32));
            $p3 = bin2hex(mb_substr($buffer, 18,16));
            $p4 = mb_substr($buffer, 34);
            $iso = $length . $p2 . $p3 . $p4;
        } else {
            $length = strtoupper(bin2hex(mb_substr($buffer, 0, 2)));
            $p2 = mb_substr($buffer, 2,16);
            $p3 = bin2hex(mb_substr($buffer, 18,8));
            $p4 = mb_substr($buffer, 26);
            $iso = $length . $p2 . $p3 . $p4;
            
        }
        return $iso;
 }
 
 //This is new function used in bin 
 public function __getResponse($mti,$buff,$ascii) {
     if($mti =='') {
         return false;
     }
//     echo PHP_EOL;
//     print 'MTI : '.$mti . PHP_EOL;
//     print 'Buff : ' . $buff . PHP_EOL;
//     print 'ASCII : ' . $ascii . PHP_EOL;
     $appISO            = new App_ISO_ISO8583();          
     $appISOResp            = new App_ISO_ISO8583();          
     $appISO->addISOwithHeader($ascii);  
     switch($mti)
     {
         case '0800':
            $appISOResp->addMTI("0810");
         //print '<pre>**';
             //print_r($appISO->getData());
            foreach ($appISO->getData() as $key => $value) {
                   $appISOResp->addData($key, $value);
            }
            $appISOResp->addData(39, '00');
             break;
         
         case '0100':
             $fData = $appISO->getData();
             $InsuranceClaim  = new Corp_Ratnakar_InsuranceClaim();
             
             $resp = $InsuranceClaim->validateECSInsuranceClaim($fData['2'], $fData['4'], $fData['41']);
             print 'Param :'.PHP_EOL;
             print 'Card Number : '.$fData['2'].PHP_EOL;
             print 'Amount : '.$fData['4'].' : '.  number_format($fData['4']).PHP_EOL;
             print 'Terminal ID : '.$fData['41'] .' : ' .number_format($fData['41']). PHP_EOL;
             print 'Response : '. $resp.PHP_EOL;
             $appISOResp->addMTI("0110");
             //echo '<pre>**';print_r($appISO->getData());
             
             
             switch ($resp) {
                    //Cardnumber not found in Insurance Claim Table
                    case INSURANCE_CLAIM_CARDNUMBER_NOT_MATCH:
                        foreach ($appISO->getData() as $key => $value) {
                            $appISOResp->addData($key, $value);
                        }
                        $appISOResp->addData(39, '00');
                        $appISOResp->addData(102, $fData['2']);
                        
                        break;
                    //Successful Cardnumber, Amount and terminal matched    
                    case STATUS_SUCCESS:
                        foreach ($appISO->getData() as $key => $value) {
                            $appISOResp->addData($key, $value);
                        }
                        $appISOResp->addData(39, '00');
                        $appISOResp->addData(102, $fData['2']);
                        
                        break;
                    //Terminal ID Not Matched    
                    case INSURANCE_CLAIM_TID_NOT_MATCH:
                        foreach ($appISO->getData() as $key => $value) {
                            $appISOResp->addData($key, $value);
                        }
                        $appISOResp->addData(39, '41');
                        break;
                        
                        
                    //Terminal ID Not Matched    
                    case INSURANCE_CLAIM_AMOUNT_NOT_MATCH:
                        foreach ($appISO->getData() as $key => $value) {
                            $appISOResp->addData($key, $value);
                        }
                        $appISOResp->addData(39, '41');
                        break;
                        
                }
         
         case '0120':
            $appISOResp->addMTI("0130");
            foreach ($appISO->getData() as $key => $value) {
                   $appISOResp->addData($key, $value);
            }
            $appISOResp->addData(39, '00');             
             break;
         
         
         case '0420':
            $appISOResp->addMTI("0130");
            foreach ($appISO->getData() as $key => $value) {
                   $appISOResp->addData($key, $value);
            }
            //Unblock amount (Loaded) as transaction is timeout
            $appISOResp->addData(39, '00');             
             break;
         
         default:
             break;
     }
     return $appISOResp->getISOwithHeaders();
 }
 
 
 private function formatResponseInBinary($isoMsg) {
     //print '**'.$isoMsg.PHP_EOL;exit;
     $bitmap = $this->getBitmap($isoMsg, 'a');
     $data = '';
     //print '**'.$bitmap.'**'.PHP_EOL;exit;
     //$this->getBitmap($buffer)
     if(($bitmap[0])==1) {
          //if ($msgt == '0120' || $msgt == '0130') {
            $data = hex2bin(mb_substr($isoMsg, 0, 4));
            $data2 = (mb_substr($isoMsg, 4, 16));
            $data3 = hex2bin(mb_substr($isoMsg, 20, 16));
            $data4 = (mb_substr($isoMsg, 36));
            $msg = $data2 . $data3 . $data4;
        } else {
            $data = hex2bin(mb_substr($isoMsg, 0, 4));
            $data2 = (mb_substr($isoMsg, 4, 16));
            $data3 = hex2bin(mb_substr($isoMsg, 20, 32));
            $data4 = (mb_substr($isoMsg, 52));
            $msg = $data2 . $data3 . $data4;
        }
        //print 'Message:'.$msg.PHP_EOL;
    return $this->filterLength($msg);

 }
 
  private function filterLength($bMsg) {
      //print 'Binary Message'.$bMsg.PHP_EOL;
     $len = dechex(strlen($bMsg));
     
       if(strlen($len) == 3 ) {
            $length = '0'.  ($len);
        } else {
            $length = '00'.  ($len);
        }
        //print 'Message Binary Length :'.$length.PHP_EOL;
        return hex2bin($length).$bMsg;
 }

 public function printData($iso){
    $iso8583    = new App_ISO_ISO8583();     
    $iso8583->addISOwithHeader($iso);
    //print 'MTI:'.$iso8583->getMTI().PHP_EOL;
    //print 'Bitmap:'.$iso8583->getMTI().PHP_EOL;
    //print 'Bitmap:'.$iso8583->getMTI().PHP_EOL;
    print_r($iso8583->getData());
    
 }
    
    
}
