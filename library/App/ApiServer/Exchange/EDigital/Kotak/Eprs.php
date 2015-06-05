<?php
/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_EDigital_Kotak_Eprs extends App_ApiServer_Exchange_EDigital_Kotak
{
    const TP_ID = TP_EPRS_GPR_ID;
    public $_soapServer;
    
    public function __construct($server) {
        parent::__construct($server);
        $this->_soapServer = $server;
        $this->setTP(self::TP_ID);
        $this->setProductConstant(PRODUCT_CONST_KOTAK_REMIT);
        $this->setAgentConstant(KTK_EPRS_AGENT_ID);
    }
    
}
