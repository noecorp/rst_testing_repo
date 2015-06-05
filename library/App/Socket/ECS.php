<?php
/**
 * Description of ECS
 *
 * @author Vikram
 */
class App_Socket_ECS {
    
    const TPUSERID = '2';

    protected $objISO ='';
    protected $responseAuditNumber ='';
    
    protected $socketObject ='';
    protected $_errorMsg ='';
    protected $_msg = '';
    protected $_isoTxnId = '';
    //ECS Response Code and their description
    protected $_responseCode = array(
                '00' => 'Transaction Approved',
                '02' => 'Refer Issuer Special Condition',
                '03' => 'Invalid Merchant',
                '04' => 'Capture Card / Invalid Mobile Number',
                '05' => 'Do Not Honor',
                '06' => 'Duplicate Transmission',
                '07' => 'Special Conditions',
                '08' => 'Honor With ID (Refer to Issuer)',
                '09' => 'Financial Liability Accepted',
                '10' => 'Partial Amount Approved',
                '11' => 'Approved VIP',
                '12' => 'Invalid Transaction',
                '13' => 'Invalid Interchange',
                '14' => 'Invalid Card Number / Invalid Mobile Number',
                '15' => 'Invalid Card Issuer',
                '16' => 'Card already Activate/Card Already sold',
                '17' => 'Card status other than Ready for Card Sale',
                '18' => 'Card already registered with another mobile number',
                '19' => 'Re Enter Transaction',
                '21' => 'No Action Taken',
                '23' => 'Invalid Unacceptable Fee',
                '24' => 'Not Supported By Receiver',
                '25' => 'Record Not Found',
                '26' => 'Duplicate Record',
                '27' => 'Field Edit Error',
                '28' => 'File Locked',
                '29' => 'Not Successful',
                '30' => 'Format Error',
                '31' => 'Acquirer Not Supported',
                '33' => 'Expired Card Capture',
                '34' => 'Suspected Fraud',
                '35' => 'Acceptor Contact Acquirer',
                '36' => 'Restricted Card Capture',
                '37' => 'Acceptor Call Acquirer',
                '38' => 'PIN Retries Exceeded',
                '39' => 'Account Type does not exist',
                '40' => 'Function Not Supported',
                '41' => 'Lost Card',
                '42' => 'Account Type does not exist',
                '43' => 'Stolen Card',
                '44' => 'Account Type does not exist',
                '45' => 'Invalid Agent ID',
                '52' => 'Maximum Balance reached for card credit.',
                '54' => 'Expired Card',
                '55' => 'Incorrect PIN',
                '56' => 'Card Record does not exist',
                '57' => 'Transaction Not Permitted by Issuer',
                '58' => 'Transaction Not Permitted by Acquirer',
                '59' => 'Suspected Fraud',
                '60' => 'Contact Acquirer',
                '61' => 'Exceeds Withdrawal Amount Limit',
                '62' => 'Restricted Card Not Permitted',
                '63' => 'Security Violation',
                '64' => 'Invalid Interchange',
                '65' => 'Exceeds Withdrawal Count Limit',
                '66' => 'Call Acquirer Security',
                '67' => 'Capture Card',
                '68' => 'Receive Late',
                '75' => 'Pin Retries Exceeded',
                '76' => 'Invalid To Account Specified',
                '77' => 'Invalid From Account Specified',
                '78' => 'Invalid Account Specified',
                '79' => 'Key Exchange Validation Failed',
                '80' => 'System Not Available',
                '82' => 'Invalid CVV',
                '86' => 'Impossible Check Pin',
                '87' => 'Stop Reconciliation',
                '88' => 'Totals Not Available',
                '89' => 'No Reconciliation',
                '90' => 'Cutover In Process',
                '91' => 'Issuer Or Switch Inoperative',
                '92' => 'Unable To Route Foreign Card',
                '93' => 'Violation Law',
                '94' => 'Duplicate Transmission',
                '95' => 'Checkpoint Cutover Error',
                '96' => 'System Malfunction'

    );
    
    private $responseMsg = '';
    
    private $_address;
    private $_port;
    
    protected $_requestISO;
    
    
    public function __construct() {
        $this->objISO = new App_ISO_ISO8583();
        $config = App_Webservice::get('ecs_iso');
        if(empty($config)) {
            throw new Exception('Unable to fetch iso communication credentials');
        }
        
        if(!isset($config['ip']) || $config['ip'] =='') {
            throw new Exception('Unable to fetch IP for iso communication');
        }

        if(!isset($config['port']) || $config['port'] =='') {
            throw new Exception('Unable to fetch port for iso communication');
        }        
        $this->_address = $config['ip'];
        $this->_port = $config['port'];
    }
    
    /**
     * getResponse
     * @param ECS_RESPONSE_CODE $responseCode
     * @return string
     */
    public function getResponse() {
        return $this->responseMsg;
    }
    
    /**
     * setResponse
     * Used to store response ISO/Messages
     *      
     * @param type $responseMessage
     */
    protected function setResponse($responseCode) {
       $this->responseMsg = $responseCode;
    }

    /**
     * setResponse
     * Need to call this in case of Error Response Code
     *      
     * @param type $responseMessage
     */
    protected function setResponseCode($responseCode) {
        if(array_key_exists($responseCode, $this->_responseCode )) {
            $this->responseMsg = $this->_responseCode[$responseCode];
        }
        //invalid message
    }
    
    
    protected function generateLoginISO(array $iso) {
        

        
        $preLenght      =   isset($iso['preLenght']) ? $iso['preLenght'] : '0067';
        $isoLiterals    =   isset($iso['isoLiterals']) ? $iso['isoLiterals'] : 'ISO';
        $header         =   isset($iso['header']) ? $iso['header'] : '006000075';
        $mti            =   isset($iso['mti']) ? $iso['mti'] : '0800';
        $primary        =   isset($iso['primary']) ? $iso['primary'] : '8220000000000000';
        $p1             =   isset($iso['p1']) ? $iso['primary'] : '0400000000000000';
        $p7             =   date('mdHis');
        $p11            =   $this->generateTraceAuditNumber();
        $p70            =   isset($iso['p70']) ? $iso['p70'] : '001';
        
        $this->getISOObject()->addMTI($mti);
        $this->getISOObject()->addData(7, $p7);
        $this->getISOObject()->addData(11, $p11);
        $this->getISOObject()->addData(70, $p70);
        $this->getISOObject()->addData(1, $p1);
        $this->getISOObject()->addISOLiterals($isoLiterals);
        $this->getISOObject()->addMessage($preLenght);
        $this->getISOObject()->addAdditionalHeader($header);        

        return $this->getISOObject()->getISOwithHeaders();
        
        
    }
    
    
    protected function generateLogonISO() {
        try {
        //$preLenght      = bin2hex()
        $param = $this->getLogonParam();
        $length         =   isset($param['length']) ? $param['length'] : '0043';
        $isoLiterals    =   isset($param['isoLiterals']) ? $param['isoLiterals'] : 'ISO';
        $header         =   isset($param['header']) ? $param['header'] : '006000075';
        $mti            =   isset($param['mti']) ? $param['mti'] : '0800';
        $primary        =   isset($param['primary']) ? $param['primary'] : '8220000000000000';
        $p1             =   isset($param['p1']) ? $param['primary'] : '0400000000000000';
        $p7             =   isset($param['p7']) ? $param['p7'] : '';
        $p11            =   isset($param['p11']) ? $param['p11'] : '';
        $p70            =   isset($param['p70']) ? $param['p70'] : '001';

        $this->getISOObject()->addMTI($mti);
        $this->getISOObject()->addData(7, $p7);
        $this->getISOObject()->addData(11, $p11);
        $this->getISOObject()->addData(70, $p70);
        $this->getISOObject()->addData(1, $p1);
        $this->getISOObject()->addISOLiterals($isoLiterals);
        $this->getISOObject()->addMessage($length);
        $this->getISOObject()->addAdditionalHeader($header);        

        return $this->getISOObject()->getISOwithHeaders();
        } catch(Exception $e) {
            print_r($e);
        }
        
    }
    
    /*protected function generateEchoISO() {
         $iso8583 = new App_ISO_ISO8583();
        //$preLenght      = bin2hex()
        $param = $this->getLogonParam();
        //$length         =   isset($param['length']) ? $param['length'] : '0043';
        //$isoLiterals    =   isset($param['isoLiterals']) ? $param['isoLiterals'] : 'ISO';
        //$header         =   isset($param['header']) ? $param['header'] : '006000075';
        $mti            =   isset($param['mti']) ? $param['mti'] : '0800';
        //$primary        =   isset($param['primary']) ? $param['primary'] : '8220000000000000';
        //$p1             =   isset($param['p1']) ? $param['primary'] : '0400000000000000';
        $p7             =   isset($param['p7']) ? $param['p7'] : '';
        $p11            =   isset($param['p11']) ? $param['p11'] : '';
        //$p70            =   isset($param['p70']) ? $param['p70'] : '301';
        $p70            =   '301';

        $iso8583->addMTI($mti);
        $iso8583->addData(7, $p7);
        $iso8583->addData(11, $p11);
        $iso8583->addData(70, $p70);
        //$iso8583->addData(1, $p1);
        //$iso8583->addISOLiterals($isoLiterals);
        //$iso8583->addMessage($length);
        //$iso8583->addAdditionalHeader($header);        
//        $this->getISOObject()->addMTI($mti);
//        $this->getISOObject()->addData(7, $p7);
//        $this->getISOObject()->addData(11, $p11);
//        $this->getISOObject()->addData(70, $p70);
//        $this->getISOObject()->addData(1, $p1);
//        $this->getISOObject()->addISOLiterals($isoLiterals);
//        $this->getISOObject()->addMessage($length);
//        $this->getISOObject()->addAdditionalHeader($header);        

        return $iso8583->getISOwithHeaders();
        //return $this->getISOObject()->getISOwithHeaders();
        
        
    }*/
    protected function generateEchoISO() {
         $iso8583 = new App_ISO_ISO8583();
        //$preLenght      = bin2hex()
        $param = $this->getLogonParam();
        $length         =   isset($param['length']) ? $param['length'] : '0043';
        $isoLiterals    =   isset($param['isoLiterals']) ? $param['isoLiterals'] : 'ISO';
        $header         =   isset($param['header']) ? $param['header'] : '006000075';
        $mti            =   isset($param['mti']) ? $param['mti'] : '0800';
        $primary        =   isset($param['primary']) ? $param['primary'] : '8220000000000000';
        $p1             =   isset($param['p1']) ? $param['primary'] : '0400000000000000';
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
        $iso8583->addMessage($length);
        $iso8583->addAdditionalHeader($header);        
//        $this->getISOObject()->addMTI($mti);
//        $this->getISOObject()->addData(7, $p7);
//        $this->getISOObject()->addData(11, $p11);
//        $this->getISOObject()->addData(70, $p70);
//        $this->getISOObject()->addData(1, $p1);
//        $this->getISOObject()->addISOLiterals($isoLiterals);
//        $this->getISOObject()->addMessage($length);
//        $this->getISOObject()->addAdditionalHeader($header);        

        return $iso8583->getISOwithHeaders();
        //return $this->getISOObject()->getISOwithHeaders();
        
        
    }
    
    protected function generateISO(array $iso) {
        
        $preLenght      =   isset($iso['preLenght']) ? $iso['preLenght'] : '0067';
        $isoLiterals    =   isset($iso['isoLiterals']) ? $iso['isoLiterals'] : 'ISO';
        $header         =   isset($iso['header']) ? $iso['header'] : '006000075';
        $mti            =   isset($iso['mti']) ? $iso['mti'] : '0800';
        $primary        =   isset($iso['primary']) ? $iso['primary'] : '8220000000000000';
        $p1             =   isset($iso['p1']) ? $iso['primary'] : '0400000000000000';
        $p7             =   date('mdHis');
        $p11            =   $this->generateTraceAuditNumber();
        $p70            =   isset($iso['p70']) ? $iso['p70'] : '001';

        
        $this->getISOObject()->addMTI($mti);
        $this->getISOObject()->addData(7, $p7);
        $this->getISOObject()->addData(11, $p11);
        $this->getISOObject()->addData(70, $p70);
        $this->getISOObject()->addData(1, $p1);
        $this->getISOObject()->addISOLiterals($isoLiterals);
        $this->getISOObject()->addMessage($preLenght);
        $this->getISOObject()->addAdditionalHeader($header);        
        return $this->getISOObject()->getISOwithHeaders();
        
        
    }

    
    protected function generateTraceAuditNumber() {
        return rand(111111,999999);
    }
    
    protected function parseResponseISO($iso) {
        $this->objISO->addISOwithHeader($iso);
        //echo '<pre>';print_r($this->objISO->getData());
        //$this->setResponseTraceAuditNumber();
    }
    
    protected function getISOObject() {
        return $this->objISO;
    }
    
    protected function isResponseSuccessful() {
        $resp = $this->getISOObject()->getData();
        if(isset($resp['39'])) {
            $this->setResponse($resp['39']);
            return $resp['39'] == '00' ? true : false;
        }
        return false; 
    }
    
    protected function getResponseTraceAuditNumber() {
        return $this->responseAuditNumber;
    }
    
   /* protected function getSocketObject() {
        return $this->socketObject;
    }*/
    
    /*protected function createConnection($ip,$port) {
        $socket = new App_Socket_Client($ip, $port, self::TPUSERID);
        $this->socketObject = $socket;
    }*/
    
    private function getLogonParam()
    {
        return array(
                'length' => '00'.dechex('67'),
                'isoLiterals' => 'ISO',
                'header' => '006000075',
                'mti' => '0800',
                'primary' => '8220000000000000',
                'p1' => '006000075',
                'p7' => date('mdHis'),
                'p11' => $this->generateTraceAuditNumber(),
                'p70' => '001'
            );;
    }
    
    
    /**
     * ecsISOCall
     * ISO Call to ECS to fetch response call $this->getResponse(); in case of true
     * @param ACSII ISO String $iso
     * @return boolean
     */
    protected function ecsISOCall($method, $iso) 
    {
        $flg = true;
        $socket = $this->_getSocketConnection();  
        if($socket == FALSE) {
            $flg = FALSE;
        }

        $sent = socket_write($socket, $iso, strlen($iso));
        
        if ($sent === FALSE) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);

            $this->setError("Couldn't write socket: [$errorcode] $errormsg");
            $flg = FALSE;
        }
        
        //$result = socket_read($socket, 2048,PHP_BINARY_READ) or print "ECS: Response Error";//die("Error: RESP\n");
        
        if (FALSE === ($buf = socket_read($socket, 2048,PHP_BINARY_READ))) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            $this->setError("Couldn't write socket: [$errorcode] $errormsg");
            $flg = FALSE;
        }        
        //print $iso.PHP_EOL;
        //print '*Respone*'. PHP_EOL;
        //print $buf.PHP_EOL;
        $this->setResponse($buf);
        
        //Preparing Logger Param
        if($flg == FALSE) {
            $param['exception']  = $this->getError();
        } else {
            $param['exception']  = '';//No Exception
        }
        
        $param['user_id']   = TP_ECS_ID;
        $param['method']    = $method;
        $param['request']   = $iso;
        $param['response']  = $buf;
        $param['response_message'] = $this->getMessageByISO($buf);
        $txnId = App_Logger::isolog($param);
        $this->setISOTxnId($txnId);
        return $flg;
    
    }
    
    protected function setError($msg) {
        $this->_errorMsg = $msg;
    }
    
    public function getError() {
        return $this->_errorMsg;
    }
    
    protected function setISOTxnId($id) {
        $this->_isoTxnId = $id;
    }
    
    public function getISOTxnId() {
        return $this->_isoTxnId;
    }
    
    protected function _getSocketConnection() {
        if(isset($this->socketObject) && is_a($this->socketObject,'socket_create')) {
            return $this->socketObject;
        }
        return $this->_createConnection();
    }
    
    /*
     * _closeConnection
     * Need to close connection explicitly
     */
    protected function _closeConnection() {
	socket_close($this->socketObject);
    }
    
    /*
     * _createConnection
     * Create Socket Connection
     * @return Socket Object or false
     */
    protected function _createConnection() {
        ini_set('default_socket_timeout', 10);
        $flg = true;//Setting default flag
        $this->socketObject = socket_create(AF_INET, SOCK_STREAM, 0);
        socket_set_option($this->socketObject, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 10, 'usec' => 0));
        socket_set_option($this->socketObject, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 10, 'usec' => 0));
        if(!$this->socketObject) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);

            $this->setError("Couldn't create socket: [$errorcode] $errormsg");
            $flg = false;
        }
        
        $flgConn = @socket_connect($this->socketObject, $this->_address, $this->_port);
        
        if(!$flgConn) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);

            $this->setError("Couldn't connect socket: [$errorcode] $errormsg");
            $flg = false;
        }        
        if($flg == false) {
            App_Logger::isolog(array(
                'user_id'       => TP_ECS_ID,
                'exception'     => $this->getError()
            ));
            return $flg;
        }
        return $this->socketObject;
    }
    
    
    public function generateDebitCardISO($param) {
        $iso8583 = new App_ISO_ISO8583();

        $isoLiterals    =   'ISO';
        $header         =   '016000075';
        $mti            =    '0200';
        $primary        =    'B238800128E19018';
        $p2             =   isset($param['crn']) ? $param['crn'] : '';
        $p3             =   '750000';//75 for debit transaction
        $p4             =   $param['amount'];
        $p7             =   isset($param['transactionTime']) ? $param['transactionTime'] : date('mdHis');
        $p11            =   $param['stan'];
        $p12            =   isset($param['localTime']) ? $param['localTime'] : date('His');
        $p13            =   isset($param['localDate']) ? $param['localDate'] : date('md');
        //p14 - Expiry Date
        //$p14            =   '0319';        
        $p17            =   isset($param['captureDate']) ? $param['captureDate'] : date('md');
        //p18 - Merchant Code
        //$p19            =   isset($param['countryCode']) ? $param['countryCode'] : '356';
        
        $p19            =   isset($param['countryCode']) ? $param['countryCode'] : COUNTRY_IN_CODE;//'356';
        
        $p18            =   isset($param['merchantCode']) ? $param['merchantCode'] : '5999';
        $p32            =   isset($param['aiiCode']) ? $param['aiiCode'] : '419953';
        $p37            =   isset($param['rrNo']) ? $param['rrNo'] : '000000005113';
        $p41            =   '30010001';
        $p42            =   '300100011234567';
        
        //P32 - Acquiring Institution Identification Code
        //P37 - Retrieval Reference No       
        //P41 - Card Acceptor Terminal No / Agent ID - Passing Agent Id for now  
        //$p41            =   isset($param['agentId']) ? $param['agentId'] : '';
        //P-42 - Card Acceptor Identification 
        //$p42            =   isset($param['agentId']) ? $param['agentId'] : '';
        $p49            =   isset($param['currency']) ? $param['currency'] : CURRENCY_INR_CODE;
        
        //exit($p41);
        $iso8583->addMTI($mti);
        $iso8583->addISOLiterals($isoLiterals);
        //$this->getISOObject()->addMessage($length);
        $iso8583->addAdditionalHeader($header);        
        $iso8583->addData(1, $primary);        
        //$iso8583->addData(1, $p1);        

        $iso8583->addData(2, $p2);        
        $iso8583->addData(3, $p3);
        $iso8583->addData(4, $p4);
        $iso8583->addData(7, $p7);
        $iso8583->addData(11, $p11);
        $iso8583->addData(12, $p12);
        $iso8583->addData(13, $p13);
if(!empty($param['expiry'])) {        
        $iso8583->addData(14, $param['expiry']);
}
        $iso8583->addData(17, $p17);
        $iso8583->addData(19, $p19);
        //$this->getISOObject()->addData(41, $p41);
        $iso8583->addData(49, $p49);
        //Dummy Elements
        $iso8583->addData(18, $p18);
        $iso8583->addData(32, $p32);
        $iso8583->addData(37, $p37);
        $iso8583->addData(41, $p41);
        $iso8583->addData(42, $p42);
//        print $primary . ':'.$p2.PHP_EOL;
//        print '*MTI*:'.$iso8583->getMTI().PHP_EOL;
//        print 'Bitmap:'.$iso8583->getBitmap().PHP_EOL;
//        print_r($iso8583->getData());
//        print $iso8583->getISOwithHeaders();
//        exit;
        
        return $iso8583->getISOwithHeaders();
    }
      
   public function generateFirstCardLoadISO($param) {
        $iso8583 = new App_ISO_ISO8583();

        //$preLenght      = bin2hex()
        //$param = $this->getLogonParam();
        //$length         =   isset($param['length']) ? $param['length'] : '0043';
        $isoLiterals    =   'ISO';
        $header         =   '016000075';
        $mti            =    '0200';
        $primary        =    'B238800128E19018';
        //$p1             =    'B238800128E19018';
        $primary      =    '0400000000000000';
        //$p1           =   isset($param['p1']) ? $param['primary'] : '';
        $p2             =   isset($param['crn']) ? $param['crn'] : '';
        $p3             =   '870000';
        $p4             =   $param['amount'];
        $p7             =   isset($param['transactionTime']) ? $param['transactionTime'] : date('mdHis');
        $p11            =   $param['stan'];
        $p12            =   isset($param['localTime']) ? $param['localTime'] : date('His');
        $p13            =   isset($param['localDate']) ? $param['localDate'] : date('md');
        //p14 - Expiry Date
        $p17            =   isset($param['captureDate']) ? $param['captureDate'] : date('md');
        //p18 - Merchant Code
        //$p19            =   isset($param['countryCode']) ? $param['countryCode'] : '356';
        
        $p19            =   isset($param['countryCode']) ? $param['countryCode'] : COUNTRY_IN_CODE;//'356';
        
        $p18            =   isset($param['merchantCode']) ? $param['merchantCode'] : '5999';
        $p32            =   isset($param['aiiCode']) ? $param['aiiCode'] : '419953';
        $p37            =   isset($param['rrNo']) ? $param['rrNo'] : '000000005113';
        $p41            =   '30010001';
        $p42            =   '300100011234567';
        
        //P32 - Acquiring Institution Identification Code
        //P37 - Retrieval Reference No       
        //P41 - Card Acceptor Terminal No / Agent ID - Passing Agent Id for now  
        //$p41            =   isset($param['agentId']) ? $param['agentId'] : '';
        //P-42 - Card Acceptor Identification 
        //$p42            =   isset($param['agentId']) ? $param['agentId'] : '';
        $p49            =   isset($param['currency']) ? $param['currency'] : CURRENCY_INR_CODE;
        
        //exit($p41);
        $iso8583->addMTI($mti);
        $iso8583->addISOLiterals($isoLiterals);
        //$this->getISOObject()->addMessage($length);
        $iso8583->addAdditionalHeader($header);        
        $iso8583->addData(1, $primary);        
        //$iso8583->addData(1, $p1);        

        $iso8583->addData(2, $p2);        
        $iso8583->addData(3, $p3);
        $iso8583->addData(4, $p4);
        $iso8583->addData(7, $p7);
        $iso8583->addData(11, $p11);
        $iso8583->addData(12, $p12);
        $iso8583->addData(13, $p13);
        $iso8583->addData(17, $p17);
        $iso8583->addData(19, $p19);
        //$this->getISOObject()->addData(41, $p41);
        $iso8583->addData(49, $p49);
        //Dummy Elements
        $iso8583->addData(18, $p18);
        $iso8583->addData(32, $p32);
        $iso8583->addData(37, $p37);
        $iso8583->addData(41, $p41);
        $iso8583->addData(42, $p42);
//        print 'MTI:'.$iso8583->getMTI().PHP_EOL;
//        print 'Bitmap:'.$iso8583->getBitmap().PHP_EOL;
//        print_r($iso8583->getData());
//        print $iso8583->getISOwithHeaders();
//        exit;
        return $iso8583->getISOwithHeaders();
    }
    
    
   public function generateCardDebitISO($param) {
        $iso8583 = new App_ISO_ISO8583();

        //$preLenght      = bin2hex()
        //$param = $this->getLogonParam();
        //$length         =   isset($param['length']) ? $param['length'] : '0043';
        $isoLiterals    =   'ISO';
        $header         =   '016000075';
        $mti            =    '0200';
        $primary        =    'B238800128E19018';
        $primary      =    '0400000000000000';
        $p2             =   isset($param['crn']) ? $param['crn'] : '';
        $p3             =   '750000';
        $p4             =   $param['amount'];
        $p7             =   isset($param['transactionTime']) ? $param['transactionTime'] : date('mdHis');
        $p11            =   $param['stan'];
        $p12            =   isset($param['localTime']) ? $param['localTime'] : date('His');
        $p13            =   isset($param['localDate']) ? $param['localDate'] : date('md');
        $p17            =   isset($param['captureDate']) ? $param['captureDate'] : date('md');
        $p19            =   isset($param['countryCode']) ? $param['countryCode'] : COUNTRY_IN_CODE;//'356';
        $p18            =   isset($param['merchantCode']) ? $param['merchantCode'] : '5999';
        $p32            =   isset($param['aiiCode']) ? $param['aiiCode'] : '06012345';
        $p37            =   isset($param['rrNo']) ? $param['rrNo'] : '000000005113';
        $p41            =   '30010001';
        $p42            =   '300100011234567';
        $p49            =   isset($param['currency']) ? $param['currency'] : CURRENCY_INR_CODE;
        $iso8583->addMTI($mti);
        $iso8583->addISOLiterals($isoLiterals);
        $iso8583->addAdditionalHeader($header);        
        $iso8583->addData(1, $primary);        
        $iso8583->addData(2, $p2);        
        $iso8583->addData(3, $p3);
        $iso8583->addData(4, $p4);
        $iso8583->addData(7, $p7);
        $iso8583->addData(11, $p11);
        $iso8583->addData(12, $p12);
        $iso8583->addData(13, $p13);
        $iso8583->addData(17, $p17);
        $iso8583->addData(19, $p19);
        $iso8583->addData(49, $p49);
        //Dummy Elements
        $iso8583->addData(18, $p18);
        $iso8583->addData(32, $p32);
        $iso8583->addData(37, $p37);
        $iso8583->addData(41, $p41);
        $iso8583->addData(42, $p42);
        return $iso8583->getISOwithHeaders();
    }
    
      
    public function generateCardLoadReversalISO($transISO, $param) {
        $iso8583 = new App_ISO_ISO8583();
        $iso8583_2 = new App_ISO_ISO8583();
        
        $iso8583->addISOwithHeader($transISO); 
        $transISOData   = $iso8583->getData();
        $p32            = sprintf("%011d", $transISOData['32']);
        $p90            = $iso8583->getMTI().$transISOData['11'].$transISOData['7'].$p32.'00000000000';
        
        $isoLiterals    =   'ISO';
        $header         =   '016000075';
        $mti            =   '0420';
        $primary        =   'B238800128E19018';
        //$p1             =   isset($param['p1']) ? $param['primary'] : '';
        $p2             =   isset($param['crn']) ? $param['crn'] : '';
        $p3             =   '870000';
        $p4             =   $this->filterAmount($param['amount']);
        $p7             =   isset($param['transactionTime']) ? $param['transactionTime'] : date('mdHis');
        $p11            =   $transISOData['11'];
        $p12            =   $transISOData['12'];
        $p13            =   $transISOData['13'];
        //p14 - Expiry Date
        $p17            =   $transISOData['17'];
        //p18 - Merchant Code
        $p18            =   isset($param['merchantCode']) ? $param['merchantCode'] : '5999';
        //$p19            =   isset($param['countryCode']) ? $param['countryCode'] : '356';
        
        $p19            =   isset($param['countryCode']) ? $param['countryCode'] : COUNTRY_IN_CODE;//'356';
        $p32            =   isset($param['aiiCode']) ? $param['aiiCode'] : '419953';
        $p37            =   $transISOData['37'];
        $p41            =   isset($param['agentId']) ? $param['agentId'] : '30010001';
        $p42            =   isset($param['caId']) ? $param['caId'] : '300100011234567';
        $p49            =   isset($param['currency']) ? $param['currency'] : CURRENCY_INR_CODE;

        //exit($p41);
        $iso8583_2->addMTI($mti);
        $iso8583_2->addISOLiterals($isoLiterals);
        //$this->getISOObject()->addMessage($length);
        $iso8583_2->addAdditionalHeader($header);        
        $iso8583_2->addData(1, $primary);        
        $iso8583_2->addData(2, $p2);        
        $iso8583_2->addData(3, $p3);
        $iso8583_2->addData(4, $p4);
        $iso8583_2->addData(7, $p7);
        $iso8583_2->addData(11, $p11);
        $iso8583_2->addData(12, $p12);
        $iso8583_2->addData(13, $p13);
        $iso8583_2->addData(17, $p17);
        $iso8583_2->addData(19, $p19);
        //$this->getISOObject()->addData(41, $p41);
        $iso8583_2->addData(49, $p49);
        //Dummy Elements
        $iso8583_2->addData(18, $p18);
        $iso8583_2->addData(32, $p32);
        $iso8583_2->addData(37, $p37);
        $iso8583_2->addData(41, $p41);
        $iso8583_2->addData(42, $p42);
        $iso8583_2->addData(90, $p90);
        
        
        

        return $iso8583_2->getISOwithHeaders();
    }
      
    public function generateCardDebitReversalISO($transISO, $param) {
        $iso8583 = new App_ISO_ISO8583();
        $iso8583_2 = new App_ISO_ISO8583();
        $iso8583->addISOwithHeader($transISO); 
        $transISOData   = $iso8583->getData();
        $p32            = sprintf("%011d", $transISOData['32']);
        $p90            = $iso8583->getMTI().$transISOData['11'].$transISOData['7'].$p32.'00000000000';
        $isoLiterals    =   'ISO';
        $header         =   '016000075';
        $mti            =   '0420';
        $primary        =   'B238800128E19018';
        $p2             =   isset($param['crn']) ? $param['crn'] : '';
        $p3             =   '750000';//75 for debit
        $p4             =   $param['amount'];
        $p7             =   isset($param['transactionTime']) ? $param['transactionTime'] : date('mdHis');
        $p11            =   $transISOData['11'];
        $p12            =   $transISOData['12'];
        $p13            =   $transISOData['13'];
        $p17            =   $transISOData['17'];
        $p18            =   isset($param['merchantCode']) ? $param['merchantCode'] : '5999';
        $p19            =   isset($param['countryCode']) ? $param['countryCode'] : COUNTRY_IN_CODE;//'356';
        $p32            =   isset($param['aiiCode']) ? $param['aiiCode'] : '419953';
        $p37            =   $transISOData['37'];
        $p41            =   isset($param['agentId']) ? $param['agentId'] : '30010001';
        $p42            =   isset($param['caId']) ? $param['caId'] : '300100011234567';
        $p49            =   isset($param['currency']) ? $param['currency'] : CURRENCY_INR_CODE;
        $iso8583_2->addMTI($mti);
        $iso8583_2->addISOLiterals($isoLiterals);
        $iso8583_2->addAdditionalHeader($header);        
        $iso8583_2->addData(1, $primary);        
        $iso8583_2->addData(2, $p2);        
        $iso8583_2->addData(3, $p3);
        $iso8583_2->addData(4, $p4);
        $iso8583_2->addData(7, $p7);
        $iso8583_2->addData(11, $p11);
        $iso8583_2->addData(12, $p12);
        $iso8583_2->addData(13, $p13);
        $iso8583_2->addData(17, $p17);
        $iso8583_2->addData(19, $p19);
        $iso8583_2->addData(49, $p49);
        //Dummy Elements
        $iso8583_2->addData(18, $p18);
        $iso8583_2->addData(32, $p32);
        $iso8583_2->addData(37, $p37);
        $iso8583_2->addData(41, $p41);
        $iso8583_2->addData(42, $p42);
        $iso8583_2->addData(90, $p90);
        return $iso8583_2->getISOwithHeaders();
    }

    /**
     * validateLogonResponse
     * Validate Logon Response ISO - This will validate on the basis on MTI and response code (39)
     * @param type $iso
     * @return boolean
     */
    protected function validateLogonResponse($iso) {
        $iso8583    = new App_ISO_ISO8583();
        $iso8583->addISOwithHeader($iso);
        $mti = $iso8583->getMTI();
        $data = $iso8583->getData();
        if($mti == '0810' && $data['39'] == '00') {
            return true;
        }
        return false;
    }
    
    /**
     * validateLogonResponse
     * Validate Logon Response ISO - This will validate on the basis on MTI and response code (39)
     * @param type $iso
     * @return boolean
     */
    protected function validateCardLoadResponse($iso) {
        $iso8583    = new App_ISO_ISO8583();
        $iso8583->addISOwithHeader($iso);
        $mti = $iso8583->getMTI();
        $data = $iso8583->getData();
        $this->setMessage($this->getMessageByCode($data['39']));
        if($mti == '0210' && $data['39'] == '00') {
            return TRUE;
        }
        return FALSE;
    }
    
     /**
     * isValidForLoadRetry 
     * Validate Load retry - Function to check if allowed for retry
     * @param type $iso
     * @return boolean
     */
    protected function isValidForLoadRetry($iso) {
        $iso8583    = new App_ISO_ISO8583();
        $iso8583->addISOwithHeader($iso);
        $data = $iso8583->getData();
        //Retry in case of timout or 19 (Re Enter Transaction)
        if(!isset($data['39']) ||  $data['39'] == '' || $data['39'] == '19') {
            return TRUE;//Allowed retry attempt
        }
        return FALSE;
    }
    
    /**
     * validateCardLoadResponseForReversal
     * Validate Logon Response ISO - This will validate on the basis on MTI and response code (39)
     * @param type $iso
     * @return boolean
     */
    protected function validateCardLoadResponseForReversal($iso) {
        $iso8583    = new App_ISO_ISO8583();
        $iso8583->addISOwithHeader($iso);
        $mti = $iso8583->getMTI();
        $data = $iso8583->getData();
        $this->setMessage($this->getMessageByCode($data['39']));

        if(array_key_exists($data['39'], $this->_responseCode)) {
            if($mti == '0210' && ($data['39'] != '91')) { //$data['39'] == '00' ||         
                return TRUE;
            }
        }
        return FALSE;
    }
    
    
    /**
     * validateLogonResponse
     * Validate Logon Response ISO - This will validate on the basis on MTI and response code (39)
     * @param type $iso
     * @return boolean
     */
    protected function validateCardLoadReversalResponse($iso) {
        $iso8583    = new App_ISO_ISO8583();
        $iso8583->addISOwithHeader($iso);
        $mti = $iso8583->getMTI();
        $data = $iso8583->getData();
        $this->setMessage($this->getMessageByCode($data['39']));
        if($mti == '0430' && ($data['39'] == '00' || $data['39'] == '12')) {
            return TRUE;
        }
        return FALSE;
    }
    
    protected function setRequestISO($iso) {
        $this->_requestISO = $iso;
    }
    
    public function getRequestISO() {
        return $this->_requestISO;
    }
    
    
    protected function filterAmount($amt) {
        return $amt*100;
    }
    
    public function getMessage() {
        return $this->_msg;
    }
    
    public function setMessage($msg) {
        $this->_msg = $msg;
    }
    
    private function getMessageByISO($iso) {
        $iso8583    = new App_ISO_ISO8583();
        $iso8583->addISOwithHeader($iso);
        $data = $iso8583->getData();
        $code = isset($data['39']) ? $data['39'] : '';
        $msg = $this->getMessageByCode($code);
        return ($msg != '') ? $msg : 'No message';
    }
    
    public function getElementResponse($element,$response ='') {
        if($response == '') {
            $response = $this->getResponse();
        }
        if(!empty($response)) {
            $iso8583    = new App_ISO_ISO8583();
            $iso8583->addISOwithHeader($response);
            $data = $iso8583->getData();
            if(isset($data[$element]) && !empty($data[$element])) {
                return $data[$element];
            }
        }
        return FALSE;
    }    
    
    private function getMessageByCode($code ='') {
        if($code == '')  {
            return 'Invalid Response Code';
        }
       return isset($this->_responseCode[$code]) ? $this->_responseCode[$code] : '';
    }
    
    
    public function generateDummyResponseISO() {
        try {
        //$preLenght      = bin2hex()
        $param = $this->getLogonParam();
        //$length         =   isset($param['length']) ? $param['length'] : '0043';
        //$isoLiterals    =   isset($param['isoLiterals']) ? $param['isoLiterals'] : 'ISO';
        $header         =   '006000075';
        $mti            =   '0810';
        $primary        =   '8220000000000000';
        $p1             =   '0400000000000000';
        $p7             =   '057163744';
        $p11            =   '864159';
        //$p70            =   isset($param['p70']) ? $param['p70'] : '001';

        $this->getISOObject()->addMTI($mti);
        $this->getISOObject()->addData(7, $p7);
        $this->getISOObject()->addData(11, $p11);
       // $this->getISOObject()->addData(70, $p70);
        $this->getISOObject()->addData(1, $p1);
        $this->getISOObject()->addData(39, '00');
        //$this->getISOObject()->addISOLiterals($isoLiterals);
        //$this->getISOObject()->addMessage($length);
        $this->getISOObject()->addAdditionalHeader($header);        

        return $this->getISOObject()->getISO();
        } catch(Exception $e) {
            print_r($e);exit;
        }
        
    }

}