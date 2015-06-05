<?php

/**
 * Services Controller
 *
 * @package api_controllers
 * @copyright transerv
 * @author Vikram Singh <vikram@transerv.co.in>
 */
error_reporting(0);
ini_set('display_errors', false);
ini_set("soap.wsdl_cache_enabled", 0);
class ServicesController extends Zend_Controller_Action
{
    private $_logger;
    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_logger = new App_Ref();
        $this->_logger->__setUserType('API');

    }
    
    /**
     * ExchangeAction 
     * This is the interface for the incoming request to soap server
     */
    public function exchangeAction()
    {
    
       // Generate WSDL relevant to code
        if (isset($_GET['wsdl'])){ 
//                Do Nothing -- No WSDL will provided
//               $autodiscover = new Zend_Soap_AutoDiscover('Zend_Soap_Wsdl_Strategy_ArrayOfTypeComplex');
//               $autodiscover->setClass('App_ApiServer_Exchange_Services');
//               $autodiscover->handle();
//            exit;
        } else {
          try {
                $this->_logger->__setUserId(TP_MVC_ID);
                $this->_logger->__setRequestTime();                        
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_Services');
                $server->setObject(new App_ApiServer_Exchange_Services($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_MVC_ID);
            } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $this->_logger->__setException($e->getMessage());                
                exit;
            }
        }
    }
    
    /**
     * ExchangeAction 
     * This is the interface for the incoming request to soap server
     */
    public function kotakAction()
    {
          try {
                $this->_logger->__setUserId(TP_KOTAK_ID);
                $this->_logger->__setRequestTime();                         
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_Services_Kotak');
                $server->setObject(new App_ApiServer_Exchange_Services_Kotak($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_KOTAK_ID);
            } catch (Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $this->_logger->__setException($e->getMessage());                
                exit;
            }
    }
    
    /**
     * ExchangeAction 
     * This is the interface for the incoming request to soap server
     */
    public function ratkrAction()
    {
          try {
                $this->_logger->__setUserId(TP_RATNAKAR_ID);
                $this->_logger->__setRequestTime();                         
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_Services_Ratnakar');
                $server->setObject(new App_ApiServer_Exchange_Services_Ratnakar($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_RATNAKAR_ID);
            } catch (Exception $e) { 
		App_Logger::log(serialize($e), Zend_Log::ERR);
                $this->_logger->__setException($e->getMessage());                
                exit;
            }
    }
    /**
     * ExchangeAction 
     * This is the interface for the incoming request to soap server
     */
    public function switchAction()
    {
          try {
                $this->_logger->__setUserId(TP_SWITCH_ID);
                //$this->_logger->__setRequestTime();                 
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_Services_Switch');
                $server->setObject(new App_ApiServer_Exchange_Services_Switch($server));
                $server->getReturnResponse(true);
                $server->handle();
               // $this->_logger->__setRequest($server->getLastRequest());
               // $this->_logger->__setResponse($server->getLastResponse());
                //$this->_logger->log();
                //$this->logServerRequest($server,TP_SWITCH_ID);
		//exit;
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    }
    
    
    public function customerAction()
    {
          try {
                $this->_logger->__setUserId(TP_CUST_ID);
                $this->_logger->__setRequestTime();              
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_Services_Customer');
                $server->setObject(new App_ApiServer_Exchange_Services_Customer($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_CUST_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());                
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    }
    
    
    
    public function paytronicAction()
    {
        
          try {
                $this->_logger->__setUserId(TP_PAYTRONIC_ID);
                $this->_logger->__setRequestTime();
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_Services_Paytronic');
                $server->setObject(new App_ApiServer_Exchange_Services_Paytronic($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_PAYTRONIC_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    }
    
    
    public function copassAction()
    {
        
          try {
                $this->_logger->__setUserId(TP_COPASS_ID);
                $this->_logger->__setRequestTime();
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_Services_Copass');
                $server->setObject(new App_ApiServer_Exchange_Services_Copass($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_COPASS_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    }

    
    
    public function happayAction()
    {
        
          try {
                $this->_logger->__setUserId(TP_HAPPAY_ID);
                $this->_logger->__setRequestTime();
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_Services_Happay');
                $server->setObject(new App_ApiServer_Exchange_Services_Happay($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_HAPPAY_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    }
    
    
    
    public function ecommAction()
    {
          try {
                $this->_logger->__setUserId(TP_ECOM_ID);
                $this->_logger->__setRequestTime();
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_Services_Ecomm');
                $server->setObject(new App_ApiServer_Exchange_Services_Ecomm($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_ECOM_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());                
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    }
    

    public function oxigenAction()
    {
        
          try {
                $this->_logger->__setUserId(TP_OXIGEN_ID);
                $this->_logger->__setRequestTime();
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_Services_Oxigen');
                $server->setObject(new App_ApiServer_Exchange_Services_Oxigen($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_OXIGEN_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    }
   

    public function rblgprAction()
    {
        
          try {
                $this->_logger->__setUserId(TP_RAT_GPR_ID);
                $this->_logger->__setRequestTime();
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_Package_Ratnakar_GPR');
                $server->setObject(new App_ApiServer_Exchange_Package_Ratnakar_GPR($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_RAT_GPR_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    }
    
    /*
     * rblcnyAction : This is for Ratnakar CNERGYIS
     */
    
    public function rblcnyAction()
    {
        
          try {
                $this->_logger->__setUserId(TP_RAT_CNY_ID);
                $this->_logger->__setRequestTime();
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_Package_Ratnakar_CNY');
                $server->setObject(new App_ApiServer_Exchange_Package_Ratnakar_CNY($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_RAT_CNY_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    }
   
    public function ktkgprAction()
    {
        
          try {
                $this->_logger->__setUserId(TP_KTK_GPR_ID);
                $this->_logger->__setRequestTime();
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_Package_Kotak_GPR');
                $server->setObject(new App_ApiServer_Exchange_Package_Kotak_GPR($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_KTK_GPR_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    }


    public function payuAction()
    {
        
          try {
                $this->_logger->__setRequestTime();
                $this->_logger->__setUserId(TP_PAYU_GPR_ID);
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_EDigital_Ratnakar_PayU');
                $server->setObject(new App_ApiServer_Exchange_EDigital_Ratnakar_PayU($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_PAYU_GPR_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    }
    
    public function shopcluesAction()
    {
        
          try {
                $this->_logger->__setUserId(TP_PAYU_GPR_ID);
                $this->_logger->__setRequestTime();
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_EDigital_Ratnakar_Shopclues');
                $server->setObject(new App_ApiServer_Exchange_EDigital_Ratnakar_Shopclues($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_SHOPCLUES_GPR_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    }
    
    
    /**
     * logServerRequest
     * Log the request and response into the database
     * @param type $server
     */
    private function logServerRequest($server,$userId = SOAP_SERVER_TP_ID) {
            $resArray['user_id'] = $userId;
            $resArray['method'] = $server->_getLogger()->__getMethod();
            $resArray['request'] = $server->getLastRequest();
            $resArray['response'] = $server->getLastResponse();
            //$resArray['source'] = 'server';
            App_Logger::apilog($resArray);
    }
  
   public function simulatorAction()
    {
        
          try {
                $this->_logger->__setUserId(TP_SIMULATOR_ID);
                $this->_logger->__setRequestTime();
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_EDigital_Ratnakar_Simulator');
                $server->setObject(new App_ApiServer_Exchange_EDigital_Ratnakar_Simulator($server));
                $server->getReturnResponse(true);
                $server->handle();
//                $this->_logger->__setRequest($server->getLastRequest());
//                $this->_logger->__setResponse($server->getLastResponse());
//                $this->_logger->log();
//                $this->logServerRequest($server,TP_SIMULATOR_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    } 
    
    public function cequityAction()
    {
        
          try {
                $this->_logger->__setUserId(TP_CEQUITY_GPR_ID);
                $this->_logger->__setRequestTime();
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_EDigital_Ratnakar_CEquity');
                $server->setObject(new App_ApiServer_Exchange_EDigital_Ratnakar_CEquity($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_CEQUITY_GPR_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    } 
    
    public function hfciAction()
    {
        
          try {
                $this->_logger->__setUserId(TP_HFCI_GPR_ID);
                $this->_logger->__setRequestTime();
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_EDigital_Ratnakar_hfci');
                $server->setObject(new App_ApiServer_Exchange_EDigital_Ratnakar_hfci($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_HFCI_GPR_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    } 
    
     public function bookmyshowAction()
    {
        
          try {
                $this->_logger->__setUserId(TP_BMS_GPR_ID);
                $this->_logger->__setRequestTime();
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_EDigital_Ratnakar_Bookmyshow');
                $server->setObject(new App_ApiServer_Exchange_EDigital_Ratnakar_Bookmyshow($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_BMS_GPR_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    }
    
    public function smpAction()
    {
        
          try {
                $this->_logger->__setUserId(TP_SMP_GPR_ID);
                $this->_logger->__setRequestTime();
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_EDigital_Ratnakar_smp');
                $server->setObject(new App_ApiServer_Exchange_EDigital_Ratnakar_smp($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_SMP_GPR_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    }
    
    public function peelworksAction()
    {
        
          try {
                $this->_logger->__setUserId(TP_PEW_GPR_ID);
                $this->_logger->__setRequestTime();
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_EDigital_Ratnakar_peelworks');
                $server->setObject(new App_ApiServer_Exchange_EDigital_Ratnakar_peelworks($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_PEW_GPR_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    }
    

    public function ipayAction() { 
        try {
            $this->_logger->__setUserId(TP_IPAY_ID);
            $this->_logger->__setRequestTime();
            $host = App_DI_Container::get('ConfigObject')->api->url;
            $uri = $host . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
            $server = new App_ApiServer_Server(null,array('uri' => $uri));
            $server->_setLogger($this->_logger);
            $server->setClass('App_ApiServer_Exchange_Services_Ipay');
            $server->setObject(new App_ApiServer_Exchange_Services_Ipay($server));
            $server->getReturnResponse(true);
            $server->handle();
            $this->_logger->__setRequest($server->getLastRequest());
            $this->_logger->__setResponse($server->getLastResponse());
            $this->_logger->log();
            $this->logServerRequest($server,TP_IPAY_ID);
          } catch (Exception $e) {
            $this->_logger->__setException($e->getMessage());
            App_Logger::log(serialize($e), Zend_Log::ERR);
            exit;
        }
    }
    
    public function rblmvcAction()
    {
          try {
                $this->_logger->__setUserId(TP_RBLMVC_ID);
                $this->_logger->__setRequestTime();              
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_Services_RatnakarMvc');
                $server->setObject(new App_ApiServer_Exchange_Services_RatnakarMvc($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_RBLMVC_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());                
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    }

     public function tfsAction()
    {
        
          try {
                $this->_logger->__setUserId(TP_TFS_GPR_ID);
                $this->_logger->__setRequestTime();
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_EDigital_Ratnakar_tfs');
                $server->setObject(new App_ApiServer_Exchange_EDigital_Ratnakar_tfs($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_TFS_GPR_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    }            

    public function forbesAction()
    {
    
          try {
                $this->_logger->__setRequestTime();
                $this->_logger->__setUserId(TP_FORBES_GPR_ID);
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_EDigital_Kotak_Forbes');
                $server->setObject(new App_ApiServer_Exchange_EDigital_Kotak_Forbes($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_FORBES_GPR_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    }
    
    public function eprsAction()
    {
    
          try {
                $this->_logger->__setUserId(TP_EPRS_GPR_ID);
                $this->_logger->__setRequestTime();
                $host = App_DI_Container::get('ConfigObject')->api->url;
                $uri = $host;// . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__;
                $server = new App_ApiServer_Server(null,array('uri' => $uri));
                $server->_setLogger($this->_logger);
                $server->setClass('App_ApiServer_Exchange_EDigital_Kotak_Eprs');
                $server->setObject(new App_ApiServer_Exchange_EDigital_Kotak_Eprs($server));
                $server->getReturnResponse(true);
                $server->handle();
                $this->_logger->__setRequest($server->getLastRequest());
                $this->_logger->__setResponse($server->getLastResponse());
                $this->_logger->log();
                $this->logServerRequest($server,TP_EPRS_GPR_ID);
            } catch (Exception $e) {
                $this->_logger->__setException($e->getMessage());
                App_Logger::log(serialize($e), Zend_Log::ERR);
                exit;
            }
    }
    

}
