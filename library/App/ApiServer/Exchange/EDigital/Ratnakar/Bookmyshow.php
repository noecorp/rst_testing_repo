<?php
/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_EDigital_Ratnakar_Bookmyshow extends App_ApiServer_Exchange_EDigital_Ratnakar
{
    const TP_ID = TP_BMS_GPR_ID;
    public $_soapServer;
    
    public function __construct($server) {
        parent::__construct($server);
        $this->_soapServer = $server;
        $this->setTP(self::TP_ID);
        $this->setProductConstant(PRODUCT_CONST_RAT_BMS);
        $this->setBankProductConstant(BANK_RATNAKAR_BOOKMYSHOW);
        $this->setAgentConstant(RBL_BMS_AGENT_ID);
        $this->setManageTypeConstant(AGENT_MANAGE_TYPE);
        $this->setLoadExpiryConstant(LOAD_FALSE);
        $this->setOTPRequestConstant(OTP_REQUEST_TRUE);
        $this->setUnblockOTPRequestConstant(OTP_REQUEST_TRUE);
    }
    
}