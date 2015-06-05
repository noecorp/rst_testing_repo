<?php
/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_EDigital_Ratnakar_PayU extends App_ApiServer_Exchange_EDigital_Ratnakar
{
    const TP_ID = TP_PAYU_GPR_ID;
    public $_soapServer;
    
    public function __construct($server) {
        parent::__construct($server);
        $this->_soapServer = $server;
        $this->setTP(self::TP_ID);
        $this->setProductConstant(PRODUCT_CONST_RAT_PAYU);
        $this->setBankProductConstant(BANK_RATNAKAR_PAYU);
        $this->setAgentConstant(RBL_PAYU_AGENT_ID);
        $this->setManageTypeConstant(AGENT_MANAGE_TYPE);
        $this->setLoadExpiryConstant(LOAD_FALSE);
        $this->setOTPRequestConstant(OTP_REQUEST_FALSE);
        //$this->setOTPRequestConstant(OTP_REQUEST_TRUE);
    }
    
}