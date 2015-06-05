<?php
/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_EDigital_Ratnakar_simulator extends App_ApiServer_Exchange_EDigital_Simulator
{
    const TP_ID = TP_SIMULATOR_ID;
    public $_soapServer;
    
    public function __construct($server) {
        parent::__construct($server);
        $this->_soapServer = $server;
        $this->setTP(self::TP_ID);
        $this->setProductConstant(PRODUCT_CONST_SIMULATOR);
      //  $this->setBankProductConstant(BANK_RATNAKAR_SHOPCLUES);
      //  $this->setAgentConstant(RBL_SHOPCLUES_AGENT_ID);
        $login = array(
            'username'=>'test',
            'password'=>'simulator'
        );
    }
    public $productValues = array('18','19','20');
    public $blockwallet = array('abc000','abc123','xyz123');
    public $generatedOTPNum = array('111111','222222','123456','333333','444444');
    public $verifiedMobileNum = array('9999999999','9876543210','1234567890');
    public $customerMobileNum = array('9999999991','9876543219','1234567899');
    public $customerPARNum = array('1234567891011','112233445566','111222333444','111122223333','111112222233');
    public $customerTranRefNum = array('123456789101123','1122334455667788','1112223334445556','1111222233334444','1111122222333335');
    public $generatedQueryRefNum = array('11223344','111222333','11112222','12345678','87654321');
    public $beneCodeArr = array('123456789101123','1122334455667788','1112223334445556','1111222233334444','1111122222333335');
    public $custInfo = array(
        'PartnerRefNo'=>'111222333444',
        'ProductId'=>'18',
        'Mobile' => '9876543210',
        'Email' => 'demo@demo.com',
        'Name'=>'Demo Name',
        'TransactionStatus' => 'Active'
    );
    public $beneInfo = array(
        'title'=>'',
        'Fname'=>'',
        'Mname'=>'',
        'Lname'=>'',
        'Gender'=>'',
        'DateOfBirth'=>'',
        'Mobile2'=>'',
        'MotherMaidenName'=>'',
        'Landline'=>'',
        'AddressLine1'=>'Mayur Vihar',
        'AddressLine2'=>'Phase 1',
        'City'=>'New Delhi',
        'State'=>'Delhi',
        'Country'=>'India',
        'Pincode'=>'110011',
        'BankName'=>'SBI',
        'BankBranch'=>'Mayur Vihar ph 1',
        'BankCity'=>'Delhi',
        'BankIfscode'=>'SBIN93120',
        'BankAccountNumber'=>'20030040005000600',
        'ProductId'=>'18',
        'Mobile' => '9876543210',
        'Email' => 'demo@demo.com',
        'Name'=>'Demo Bene Name',
        'BeneCode'=>'1122334455667788',
        'TransactionStatus' => 'Active'
    );
    public $transInfo = array(
        'WalletCode'=>'RCI310',
        'Amount'=>'30000',
        'Narration' => 'Debit No.12345',
        'TxnIndicator' => 'CR',
        'TxnIdentifierType'=>'PAR',
        'MemberIDCardNo' => '111222333444',
        'TransactionStatus' => 'Active'
    );
    public $sessionValues = array('DEMO1234');
     /**
     * 
     * @param string $username
     * @param string $password
     */
    public function Login() {
        $chklogin = array(
            'username'=>'demo',
            'password'=>'demo'
        );
        
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            if($resp->Username == $chklogin['username'] && $resp->Password == $chklogin['password']){
                
                $sessionValue = $this->sessionValues[0];
                
                return self::generateSuccessResponse($sessionValue, self::$SUCCESS);
            }
            return self::Exception("Invalid Login", self::$INVALID_LOGIN);
        } catch (App_Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            return self::Exception("Invalid Login", self::$SYSTEM_ERROR);
        }
    }
    
   
    public function EchoMessage() {
       $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);        
       $sxml = $this->_soapServer->getLastRequest();
       $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
       if(!in_array($resp->SessionID,$this->sessionValues)){
            return self::Exception(self::MESSAGE_INVALID_LOGIN, App_ApiServer_Exchange::$INVALID_LOGIN);
       }
       
       $sess_id = (string) $resp->SessionID;
        return self::generateSuccessResponse($sess_id);

    }
    
    public function Logoff() {
       $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
       $sxml = $this->_soapServer->getLastRequest();
       $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);        
        if(in_array($resp->SessionID,$this->sessionValues)){
            return self::generateSuccessResponsewithoutSessionID();
       }
        return self::Exception('Invalid SessionID', self::$SYSTEM_ERROR);
    }
}