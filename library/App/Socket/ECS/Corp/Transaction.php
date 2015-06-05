<?php
class App_Socket_ECS_Corp_Transaction extends App_Socket_ECS_Authentificator {


    public static $auditNumber;
    
    private $attempts = 0;
    private $maxAttempts = 3;
    
    private $loadAttempts = 0;
    private $loadMaxAttempts = 1;
    
    private $debitAttempts = 0;
    private $debitMaxAttempts = 1;
    
    private $reversalAttempts = 0;
    private $reversalMaxAttempts = 1;
    
    private $debitReversalAttempts = 0;
    private $debitReversalMaxAttempts = 1;
    
    public function __construct() {
        parent::__construct();
    }
    
    
public function convert2ascii($buffer)   
 {
    $length = strtoupper(bin2hex(mb_substr($buffer, 0, 2)));
    $p2 = mb_substr($buffer, 2,16);
    $p3 = bin2hex(mb_substr($buffer, 18,16));
    $p4 = mb_substr($buffer, 34);
    $iso = $length . $p2 . $p3 . $p4;
    
    $iso8583	= new App_ISO_ISO8583();          
    $iso8583->addISOwithHeader($iso);
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
            //print 'Login response:'.$responseISO.PHP_EOL;//exit;
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
    public function cardDebit(array $param) {

        if(!isset($param['amount']) || $param['amount'] == '') {
            throw new App_Api_Exception (ErrorCodes::getSocketAmountMsg($param['amount']), ErrorCodes::ERROR_EDIGITAL_SOCKET_AMOUNT_CODE);
        }
        
        if(!isset($param['crn']) || $param['crn'] == '') {
            throw new App_Api_Exception (ErrorCodes::ERROR_EDIGITAL_INVALID_CRN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CRN_CODE);
        }
        
        if(!isset($param['transactionId']) || $param['transactionId'] == '') {
            throw new App_Api_Exception (ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_ID_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_ID_CODE);
        }
        if(empty($param['expiry'])) {
            $crnMaster = new CRNMaster();
            $expiry = $crnMaster->getCardExpiry(array(
                'card_number' => $param['crn']
            ));
            if($expiry != FALSE) {
                $param['expiry'] = $expiry;
            }
        }

        $reqParam['expiry'] = $param['expiry'];
        
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
            //print 'Login response:'.$responseISO.PHP_EOL;//exit;
            if($flg) {
                if($this->validateLogonResponse($responseISO)) {     
                    $this->debitAttempts = 0;
                    //Do the transaction                        
                    return $this->cardDebitTransaction($reqParam);
                }
            }

        return false;

           
    }
    
    
    /**
     * MediAssist Insurance Claim Load Reversal
     * 
     * @param type $transactionID
     * @param type $card
     * @param type $amount
     * @param type $transactionDate
     * @return boolean
     * @throws App_Api_Exception
     */
    public function reversalMACardLoad($param) {

        
        if(!isset($param['amount']) || $param['amount'] == '') {
            throw new App_Api_Exception ("Invalid amount passed ". $param['amount']);
        }
        
        if(!isset($param['crn']) || $param['crn'] == '') {
            throw new App_Api_Exception ("Invalid CRN number ");
        }
        
        if(!isset($param['transactionId']) || $param['transactionId'] == '') {
            throw new App_Api_Exception ("Invalid transaction id ");
        }

        $agentId = isset($param['agentId']) ? $param['agentId'] : sprintf("%08d", MEDIASSIST_AGENT_ID);
        
        //$transactionId = isset($param['agentId']) ? $param['agentId'] : ;        
        $reqParam['amount'] = $this->filterAmount($param['amount']);
        
        $reqParam['countryCode'] = isset($param['countryCode']) ? $param['countryCode'] : COUNTRY_IN_CODE;//Country Code
        $reqParam['merchantCode'] = '5999';//Merchant Code
        $reqParam['aiiCode'] = '419953'; //Acquiring Institution Identification Code
        //passing transaction Id in Retrieval Regerence No
        $reqParam['rrNo'] = sprintf("%012d", $param['transactionId']);///'000000005113';P-37 Retrieval Reference No
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
            //print 'Login response:'.$responseISO.PHP_EOL;//exit;
            if($flg) {
                if($this->validateLogonResponse($responseISO)) {     
                    $this->loadAttempts = 0;
                    //Do the transaction                        
                    return $this->cardDebitTransaction($reqParam);
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
    public function cardDebitTransaction($param) {
        $this->debitAttempts++;
        $flg = false;
        if($this->_createConnection() == false) {
            return false;
        }

        $iso = $this->generateDebitCardISO($param);
        $this->setRequestISO($iso);                            
        $flg = $this->ecsISOCall(ECS_ISO_CARDDEBIT,$iso);     
        
        //Close Connection        
        $this->_closeConnection();        
       //Send Logon Request
        if($flg) {
            $responseISO = $this->getResponse();
            if($responseISO != '') {
                //Validate Echo Message Response    
                $flg = $this->validateCardLoadResponse($responseISO);
            }
            if($responseISO == '' || !$this->validateCardLoadResponseForReversal($responseISO) ) {		            
                $this->reversalAttempts = 0;
                $this->cardDebitReversalTransaction($iso, $param);
            }
        }                    

        if($responseISO == '' || $flg == '' || $flg === FALSE) {
            $alertArray = array(
                'Response Message ' => $this->getMessage(),
                'Process ' => 'Card Debit failure'
            );
            $alertArray = array_merge($alertArray,$param );
//            $m = new App\Messaging\MVC\Axis\Operation();
//            $m->loadFail($alertArray);
            App_Logger::log($alertArray, Zend_Log::ERR);
        }
        
        if($flg == false) {
            if($this->isValidForLoadRetry($responseISO)) {
                if($this->debitAttempts < $this->debitMaxAttempts) {
                    //Recursive function call
                    return $this->cardDebitTransaction($param);
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
        //$iso = $this->generateFirstCardLoadISONew($param);
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
        
        if($flg == '') {
            $this->reversalAttempts = 0;
            $this->cardLoadReversalTransaction($iso, $param);            
        }

        if($flg == '') {
            $this->reversalAttempts = 0;
            $this->cardLoadReversalTransaction($iso, $param);            
        }

        if($responseISO == '' || $flg == '' || $flg === FALSE) {
            $alertArray = array(
                'Response Message ' => $this->getMessage(),
                'Process ' => 'Card Load failure'
            );
//            $alertArray = array_merge($alertArray,$param );
//            $m = new App\Messaging\MVC\Axis\Operation();
//            $m->loadFail($alertArray);
//            App_Logger::log($alertArray, Zend_Log::ERR);
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
    //Making it public so it can be called from HELPDESK module
    public function cardLoadReversalTransaction($loadIso, $param) {
        //Update No. of attempts
        $this->reversalAttempts++;
        $flg = false;
        //$m = new App\Messaging\MVC\Axis\Operation();   
        if($this->_createConnection() == false) {
            return false;
        }

        $iso = $this->generateCardLoadReversalISO($loadIso,$param);
        $this->setRequestISO($iso);                            
        //print 'ISO : '.$this->getRequestISO()."\n";exit('HERE');
        $flg = $this->ecsISOCall(ECS_ISO_CARDLOAD_REVERSAL,$iso);            
       //Send Logon Request
        if($flg) {
            $responseISO = $this->getResponse();
            //Validate Echo Message Response    
            $flg = $this->validateCardLoadReversalResponse($responseISO);
        }                    
        //Close Connection
        $this->_closeConnection();
        if($flg == false) {
             if($this->reversalAttempts < $this->reversalMaxAttempts) {
                 //Recursive function call
                 return $this->cardLoadReversalTransaction($loadIso,$param);
             }
         }
         
         //Still failed, Generate Alert
        if($flg == false) {
            $alertArray = array(
                'Response Message ' => $this->getMessage(),
                'Process ' => 'Card Load Reveresal Failure'
            );
            $alertArray = array_merge($alertArray,$param );
            
            //$m->loadFail($alertArray);
            App_Logger::log($alertArray, Zend_Log::ERR);
        } else {
            $alertArray = array(
                'Response Message ' => $this->getMessage(),
                'Process ' => 'Card Load Reveresal Successful'
            );
            $alertArray = array_merge($alertArray,$param );
            //$m->loadFail($alertArray);
            App_Logger::log($alertArray, Zend_Log::ERR);            
        }
        return $flg;
    }
    
      /**
     * CardLoadTransaction
     * Function to initiate Card load transaction
     * @param type $param
     * @return boolean
     */
    //Making it public so it can be called from HELPDESK module
    public function cardDebitReversalTransaction($loadIso, $param) {
        //Update No. of attempts
        $this->reversalAttempts++;
        $flg = false;
        $m = new App\Messaging\MVC\Axis\Operation();   
        if($this->_createConnection() == false) {
            return false;
        }

        $iso = $this->generateCardDebitReversalISO($loadIso,$param);
        $this->setRequestISO($iso);                            
        //print 'ISO : '.$this->getRequestISO()."\n";exit('HERE');
        $flg = $this->ecsISOCall(ECS_ISO_CARDDEBIT_REVERSAL,$iso);            
       //Send Logon Request
        if($flg) {
            $responseISO = $this->getResponse();
            //Validate Echo Message Response    
            $flg = $this->validateCardLoadReversalResponse($responseISO);
        }                    
        //Close Connection
        $this->_closeConnection();
        if($flg == false) {
             if($this->reversalAttempts < $this->reversalMaxAttempts) {
                 //Recursive function call
                 return $this->cardDebitReversalTransaction($loadIso,$param);
             }
         }
         
         //Still failed, Generate Alert
        if($flg == false) {
            $alertArray = array(
                'Response Message ' => $this->getMessage(),
                'Process ' => 'Card Load Reveresal Failure'
            );
            $alertArray = array_merge($alertArray,$param );
            
            $m->loadFail($alertArray);
            App_Logger::log($alertArray, Zend_Log::ERR);
        } else {
            $alertArray = array(
                'Response Message ' => $this->getMessage(),
                'Process ' => 'Card Load Reveresal Successful'
            );
            $alertArray = array_merge($alertArray,$param );
            $m->loadFail($alertArray);
            App_Logger::log($alertArray, Zend_Log::ERR);            
        }
        return $flg;
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
     
    public function validateRequest($mti,$buff) {
        //return "response for MTI:".$mti;
        $ascii = $this->convert2ascii($mti, $buff);
        //print 'ASCII:'.$ascii.PHP_EOL;
        $this->printData($ascii);
        $resp = $this->getResponse($mti, $buff, $ascii);
        //print 'Response:*'.$resp;exit;
        return $this->formatResponseInBinary($resp);
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
    
 public function getISOResponse($mti,$buff,$ascii) {
     if($mti =='') {
         return false;
     }
     
     $appISO            = new App_ISO_ISO8583();          
     $appISOResp            = new App_ISO_ISO8583();          
     $appISO->addISOwithHeader($ascii);  
     switch($mti)
     {
         case '0800':
            $appISOResp->addMTI("0810");
            foreach ($appISO->getData() as $key => $value) {
                   $appISOResp->addData($key, $value);
            }
            $appISOResp->addData(39, '00');
             break;
         
         case '0100':
            $appISOResp->addMTI("0110");
            foreach ($appISO->getData() as $key => $value) {
                   $appISOResp->addData($key, $value);
            }
            $appISOResp->addData(39, '00');             
             break;
         
         case '0120':
            $appISOResp->addMTI("0130");
            foreach ($appISO->getData() as $key => $value) {
                   $appISOResp->addData($key, $value);
            }
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
    print 'MTI:'.$iso8583->getMTI().PHP_EOL;
    print 'Bitmap:'.$iso8583->getMTI().PHP_EOL;
    //print 'Bitmap:'.$iso8583->getMTI().PHP_EOL;
    print_r($iso8583->getData());
    
 }
    
    
}
