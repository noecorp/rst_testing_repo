<?php

/**
 * Bootstraps the Backoffice module
 *
 * @category  backoffice
 * @package   backoffice_bootstrap
 * @copyright company
 */
class Operation_Bootstrap extends App_Bootstrap_Abstract
{
    /**
     * Inits the session for the backoffice
     * 
     * @access protected
     * @return void     
     */
    protected function _initSession(){
       Zend_Session::setOptions(array('cookie_httponly' => true));
       Zend_Session::start();

        //Vallidate Single Session for logged in user
        Util::validateSingleSession();
    }
    
    /**
     * Inits the Zend Paginator component
     *
     * @access protected
     * @return void
     */
    protected function _initPaginator(){

        Zend_Paginator::setDefaultScrollingStyle(App_DI_Container::get('ConfigObject')->paginator->scrolling_style);
        Zend_View_Helper_PaginationControl::setDefaultViewPartial(
            'default.phtml'
        );

    }
    
    /**
     * Initializes the view helpers for the application
     * 
     * @access protected
     * @return void     
     */
    protected function _initViewHelpers() {

        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        if (NULL === $viewRenderer->view) {
            $viewRenderer->initView();
        }
        
        $viewRenderer->view->addHelperPath('App/Operation/View/Helper', 'App_Operation_View_Helper');

    }
    
  
     protected function _initRouter(){

        $router = new Zend_Controller_Router_Rewrite();
        
        $routes = new Zend_Config_Xml(APPLICATION_PATH . '/configs/' . CURRENT_MODULE . '_routes.xml');
        $router->addConfig($routes);
        
        $front = Zend_Controller_Front::getInstance();
        $return = Util::filterEncryptURL($_GET,$router);

        $front->setRequest($return['request']);              
        $front->setRouter($return['router']);
        Zend_Registry::set('Router', $router);

    }    
    
    protected function _initSetupDirectory()
    
{
        //Create Cache directory if not exists
        if(!is_dir(ROOT_PATH . '/logs')) {
            mkdir(ROOT_PATH . '/logs', '0755');
        } 

        
        //Create Cache directory if not exists
        if(!is_dir(ROOT_PATH . '/logs/operation')) {
            mkdir(ROOT_PATH . '/logs/operation', '0755');
        } 
        
        //Create Cache directory if not exists
        if(!is_dir(ROOT_PATH . '/uploads/operation')) {
            mkdir(ROOT_PATH . '/uploads/operation', '0777');
        } 
        if(!is_dir(ROOT_PATH . '/uploads/operation/application')) {
            mkdir(ROOT_PATH . '/uploads/operation/application', '0755');
        } 
        
        if(!is_dir(ROOT_PATH . '/uploads/operation/reports')) {
            mkdir(ROOT_PATH . '/uploads/operation/reports', '0777');
        } 

        if(!is_dir(ROOT_PATH . '/uploads/operation/reports/tpmis')) {
            mkdir(ROOT_PATH . '/uploads/operation/reports/tpmis', '0777');
        } 
        
        if(!is_dir(ROOT_PATH . '/uploads/operation/reports/failurerecon')) {
            mkdir(ROOT_PATH . '/uploads/operation/reports/failurerecon', '0777');
        }
        
        if(!is_dir(ROOT_PATH . '/uploads/operation/reports/failurerecon/kotak')) {
            mkdir(ROOT_PATH . '/uploads/operation/reports/failurerecon/kotak', '0777');
        }
         if(!is_dir(ROOT_PATH . '/uploads/corporate')) {
            mkdir(ROOT_PATH . '/uploads/corporate', '0777');
        } 
         if(!is_dir(ROOT_PATH . '/uploads/corporate/cardholder')) {
            mkdir(ROOT_PATH . '/uploads/corporate/cardholder', '0777');
        }
         if(!is_dir(ROOT_PATH . '/uploads/corporate/cardholder/ratnakar')) {
            mkdir(ROOT_PATH . '/uploads/corporate/cardholder/ratnakar', '0777');
        }
         //Create /uploads/customer/kotak/docs directory if not exists
        if(!is_dir(ROOT_PATH . '/uploads/corporate/kotak')) {
            mkdir(ROOT_PATH . '/uploads/corporate/kotak', '0755');
        } 
          //Create /uploads/customer/kotak/docs directory if not exists
        if(!is_dir(ROOT_PATH . '/uploads/corporate/kotak/cardholder')) {
            mkdir(ROOT_PATH . '/uploads/corporate/kotak/cardholder', '0755');
        }
        if(!is_dir(ROOT_PATH . '/uploads/operation/reports/agentbalance')) {
            mkdir(ROOT_PATH . '/uploads/operation/reports/agentbalance', '0777');
        }
        
         if(!is_dir(ROOT_PATH . '/uploads/operation/reports/walletbalance')) {
            mkdir(ROOT_PATH . '/uploads/operation/reports/walletbalance', '0777');
        }
        if(!is_dir(ROOT_PATH . '/uploads/operation/reports/remittancetransaction')) {
            mkdir(ROOT_PATH . '/uploads/operation/reports/remittancetransaction', '0777');
        }
        if(!is_dir(ROOT_PATH . '/uploads/operation/reports/agentvirtualbalance')) {
            mkdir(ROOT_PATH . '/uploads/operation/reports/agentvirtualbalance', '0777');
        }
    }    
    
        /**
     * Initialize the Flag and Flipper System
     *
     * @return void
     */
    protected function _initFlagFlippers()
    {

        $flipperLogFile = ROOT_PATH . '/logs/' . CURRENT_MODULE . '/flagflippers.log';
        $logger = new Zend_Log(new Zend_Log_Writer_Stream($flipperLogFile));
        if (!Zend_Registry::get('IS_PRODUCTION')) {
            $logger->addWriter(new Zend_Log_Writer_Firebug());
        }
        Zend_Registry::set('Zend_Log_FlagFlippers', $logger);
        App_FlagFlippers_Manager::load();

    }

}
