<?php
/**
 * Description of ECS
 *
 * @author Vikram
 */
abstract class App_Socket_MVC {
    
    const TPUSERID = '3';

    protected $objISO ='';
    protected $responseAuditNumber ='';
    
    protected $socketObject ='';
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
    
    
    public function __construct() {
        $this->objISO = new App_ISO_ISO8583();
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
     *      
     * @param type $responseMessage
     */
    protected function setResponse($responseCode) {
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
        //print_r($resp);exit;
        if(isset($resp['39'])) {
            $this->setResponse($resp['39']);
            return $resp['39'] == '00' ? true : false;
        }
        return false; 
    }
    
    protected function getResponseTraceAuditNumber() {
        return $this->responseAuditNumber;
    }
    
    protected function getSocketObject() {
        return $this->socketObject;
    }
    
    protected function createConnection($ip,$port) {
        if(!is_a($this->socketObject, 'App_Socket_Client')) {
            //print 'Creating New Object';
            $socket = new App_Socket_Client($ip, $port, self::TPUSERID);
            $this->socketObject = $socket;
        }
    }
}