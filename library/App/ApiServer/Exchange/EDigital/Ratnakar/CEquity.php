<?php
/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_EDigital_Ratnakar_CEquity extends App_ApiServer_Exchange_EDigital_RatnakarCard
{
    const TP_ID = TP_CEQUITY_GPR_ID;
    public $_soapServer;
    
    public function __construct($server) {
        parent::__construct($server);
        $this->_soapServer = $server;
        $this->setTP(self::TP_ID);
        $this->setProductConstant(PRODUCT_CONST_RAT_CTY);
        $this->setBankProductConstant(BANK_RATNAKAR_CEQUITY);
        $this->setAgentConstant(RBL_CEQUITY_AGENT_ID);
        $this->setManageTypeConstant(CORPORATE_MANAGE_TYPE);
        $this->setLoadExpiryConstant(LOAD_TRUE);
        $this->setOTPRequestConstant(OTP_REQUEST_TRUE);
    }
    
}