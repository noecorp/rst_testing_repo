<?php
/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_EDigital_Ratnakar_Shopclues extends App_ApiServer_Exchange_EDigital_Ratnakar
{
    const TP_ID = TP_SHOPCLUES_GPR_ID;
    public $_soapServer;
    
    public function __construct($server) {
        parent::__construct($server);
        $this->_soapServer = $server;
        $this->setTP(self::TP_ID);
        $this->setProductConstant(PRODUCT_CONST_RAT_SHOP);
        $this->setBankProductConstant(BANK_RATNAKAR_SHOPCLUES);
        $this->setAgentConstant(RBL_SHOPCLUES_AGENT_ID);
        $this->setManageTypeConstant(AGENT_MANAGE_TYPE);
        $this->setLoadExpiryConstant(LOAD_FALSE);
        $this->setOTPRequestConstant(OTP_REQUEST_TRUE);
    }
    
}