<?php

// DEPRECATED - Need to remove this class
class App_Socket_ECS_ISO {


    public function __construct() {
        //parent::__construct();
    }
    
    public function generateISO() {
        
    }
    
    public function setISOType($type) {
        $this->_isoType = $type;
    }
    
    
    /*
     * Set Header
     * @length = 9
     * @sample = 006000075
     */
    public function setHeader($header) {
        $this->_header = $type;
    }
    
    /*
     * Set MTI
     * @length = 4
     * @sample = 0100 	Authorization request,0200 	Acquirer Financial Request,0400 	Acquirer Reversal Request
     * 0800 	Network Management Request
     */
    public function setMTI($mti) {
        $this->_mti = $mti;
    }

    /**
     * setPrimaryBit
     * @length = 16
     * @param type $primaryBIT
     * @sample Logon 8220000000000000, Load Cash B238800128E19018
     */
    public function setPrimaryBit($primaryBIT) {
        $this->_primaryBitmap = $primaryBIT;
    }
    
    /**
     * setSecondryBit
     *  @length = 16
     * @param type $secondryBIT
     * @sample Logon 8220000000000000, Load Cash B238800128E19018
     */
    public function setSecondryBit($secondryBIT) {
        $this->_secondryBitmap = $secondryBIT;
    }
    
    public function setCRN($crn) {
        $this->_crn = $crn;
    }
    
    public function setProcessingCode($processingCode) {
        
    }
    
    public function setTransactionAmount($amount) {
        
    }
    
    public function setSTAN($stan) {
        
    }
    
    public function setLocalTransactionTime($time) {
        
    }
    
    public function setLocalTransactionDate($date) {
        
    }
    
    public function setExpiryDate($exDate) {
        
    }
    
    public function setCaptureDate($capDate) {
        
    }
    
    public function setMerchantCode($merchantCode) {
        
    }
    
    public function setCountryCode($cCode) {
        
    }
    
    public function setPOSEntryMode($posMode) {
        
    }
    
    public function setAcquiringInstitution($institution) {
        
    }
    
    
    public function track2Data($t2Data) {
        
    }
    
    public function retrievalReferenceNo($ref) {
        
    }
    
    public function cardAcceptorId($agentId) {
        
    }
    
    public function cardAcceptorIdentification($terminalInfo) {
        
    }
    
    public function cardAcceptorName($info) {
        
    }
    
    public function transactionCurrencyCode($currencyCode) {
        
    }
    
    
  
}
