<?php

/**
 * Default entry point in the application
 *
 * @package api_controllers
 * @copyright transerv
 */

class IndexController extends Zend_Controller_Action
{
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
    }
    
    /**
     * Controller's entry point
     *
     * @access public
     * @return void
     */
    public function indexAction()
    {
        exit;
    }
    
    
    /*public function mvcAction()
    {
    
       // Generate WSDL relevant to code
        if (isset($_GET['wsdl'])){
            $autodiscover = new Zend_Soap_AutoDiscover('Zend_Soap_Wsdl_Strategy_ArrayOfTypeComplex');
            $autodiscover->setClass('App_ApiServer_MVC');
            $autodiscover->handle();
            //exit;
        } else {
            $host = App_DI_Container::get('ConfigObject')->api->url;
            $serviceURL = $host . '/index/mvc?wsdl';
            $server = new Zend_Soap_Server($serviceURL);
            $server->setClass('App_ApiServer_MVC');
            $server->setObject(new App_ApiServer_MVC());
            $server->getReturnResponse(true);
            $server->handle();
        }
        //echo "here";exit;
    }
    
    public function ecsAction()
    {
       if (isset($_GET['wsdl'])) {
            $wsdl = new Zend_Soap_AutoDiscover(); // It generates the WSDL
            $wsdl->setOperationBodyStyle(array(
                'use' => 'literal'
            ));
            $wsdl->setBindingStyle(array(
                'style' => 'document'
            ));
            $wsdl->setClass('App_ApiServer_ECS');
            $wsdl->handle();
       } else {
            //$server = new SoapServer('http://api.shmart.local/index/ecs?wsdl');
            $host = App_DI_Container::get('ConfigObject')->api->url;
            $server = new Zend_Soap_Server( $host . '/index/ecs?wsdl');
            $server->setReturnResponse(true);
            $server->setClass('App_ApiServer_ECS');
            $server->setObject(new App_ApiServer_ECS());
            $server->getReturnResponse(true);            
            return $server->handle();
        }
    }
    
    
    public function wsdlAction()
    {
        
        // Generate WSDL relevant to code
        if (isset($_GET['wsdl'])){
            $autodiscover = new Zend_Soap_AutoDiscover('Zend_Soap_Wsdl_Strategy_ArrayOfTypeComplex');
            $autodiscover->setClass('App_ApiServer_ECS');
            $autodiscover->handle();
            //exit;
        } else {
            $host = App_DI_Container::get('ConfigObject')->api->url;
            $serviceURL = $host . '/index/wsdl?wsdl';
            $server = new Zend_Soap_Server($serviceURL);
            $server->setClass('App_ApiServer_ECS');
            $server->setObject(new App_ApiServer_ECS());
            $server->getReturnResponse(true);
            return $server->handle();
        }
    }*/
      
  
    
    
}



