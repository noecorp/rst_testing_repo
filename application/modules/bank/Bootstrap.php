<?php
/**
 * Frontend bootstrap
 *
 * @package Frontend
 * @copyright company
 */

class Bank_Bootstrap extends App_Bootstrap_Abstract
{
    /**
     * Inits the session for the frontend
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
        
        $viewRenderer->view->addHelperPath('App/Bank/View/Helper', 'App_Bank_View_Helper');
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
        if(!is_dir(ROOT_PATH . '/logs/bank')) {
            mkdir(ROOT_PATH . '/logs/bank', '0755');
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