<?php
/**
 * Holds the backoffice's navigation system
 *
 *
 * @category App
 * @package App_Backoffice
 * @subpackage Navigation
 * @copyright company
 */

class App_Customer_Navigation 
{
    /**
     * Singleton object for this class
     *
     * @var App_Backoffice_Navigation
     * @access protected
     */
    protected static $_instance;
    
    /**
     * Holds the navigation array
     * 
     * @var array
     * @access protected
     */
    protected $_navigation;
    
    /**
     * Constructs the Navigation object. Must not be called directly
     * 
     * @access public
     * @return void
     */
    protected function __construct(){
        $this->_navigation = $this->_markActive($this->_filter($this->_getPages()));
    }
    
    /**
     * Returns a singleton instance of the class
     * 
     * @access public
     * @return void
     */
    public static function getInstance(){
        if (NULL === self::$_instance) {
            self::$_instance = new App_Agent_Navigation();
        }
        
        return self::$_instance;
    }
    
    /**
     * Implements the __clone() magic method, forbidding the cloning
     * of this object
     * 
     * @access public
     * @return void
     */
    public function __clone(){
        throw new Zend_Exception('Cloning singleton objects is forbidden');
    }
    
    /**
     * Returns an array of pages
     * 
     * @access protected
     * @return void
     */
    protected function _getPages(){
        $pages = array(
            array(
                'main' => array(
                    'label' => 'Dashboard',
                    'controller' => 'profile',
                    'action' => 'index'
                ),
                'pages' => array(
                    array(
                        'label' => 'Summary',
                        'controller' => 'profile',
                        'action' => 'index',
                    ),
                ),
            ),
            array(
                'main' => array(
                    'label' => 'Enroll Cardholder',
                    'controller' => 'mvc_axis_cardholder',
                    'action' => 'step1'
                ),
                'pages' => array(
                    array(
                        'label' => 'Enroll Cardholder',
                        'controller' => 'mvc_axis_cardholder',
                        'action' => 'step1',
                        'scope' => '*'
                    ),
                   
                ),
            ),
            
            array(
                'main' => array(
                    'label' => 'Check Balance',
                    'controller' => 'profile',
                    'action' => 'checkbalance'
                ),
                'pages' => array(
                    array(
                        'label' => 'Check Balance',
                        'controller' => 'profile',
                        'action' => 'checkbalance',
                        'scope' => '*'
                    ),
                   
                ),
            ),
           array(
                'main' => array(
                    'label' => 'Fund Requests',
                    'controller' => 'fundrequest',
                    'action' => 'index'
                ),
                'pages' => array(                  
                    
                    array(
                        'label' => 'Fund Requests',
                        'controller' => 'fundrequest',
                        'action' => 'index',
                        'scope' => '*'
                    ),
                    
                     array(
                        'label' => 'Send Fund Request',
                        'controller' => 'fundrequest',
                        'action' => 'send',
                        'scope' => '*'
                    ),

                   
                ),
            ),
            
            array(
                'main' => array(
                    'label' => 'Cardholder Fund Load',
                    'controller' => 'mvc_axis_cardholderfund',
                    'action' => 'mobile'
                ),
                'pages' => array(                  
                    
                    array(
                        'label' => 'Cardholder Fund Load',
                        'controller' => 'mvc_axis_cardholderfund',
                        'action' => 'mobile',
                        'scope' => '*'
                    ),
                    
                ),
            ),
             array(
                'main' => array(
                    'label' => 'Reports',
                    'controller' => 'reports',
                    'action' => 'index'
                ),
                'pages' => array(
                   array(
                        'label' => 'Agent Transaction Summary',
                        'controller' => 'reports',
                        'action' => 'agentsummary',
                    ),
                     array(
                        'label' => 'Load/Reload',
                        'controller' => 'mvc_axis_reports',
                        'action' => 'agentwiseload',
                    ),  
                    array(
                        'label' => 'Remittance',
                        'controller' => 'remit_boi_reports',
                        'action' => 'remittancereport',
                    ),
                    array(
                        'label' => 'Agent Commission Summary',
                        'controller' => 'reports',
                        'action' => 'agentcommissionsummary',
                    ),
                     array(
                        'label' => 'Load/Reload Commission',
                        'controller' => 'mvc_axis_reports',
                        'action' => 'loadreloadcomm',
                    ), 
                    array(
                        'label' => 'Remittance Commission',
                        'controller' => 'remit_boi_reports',
                        'action' => 'remittancecommission',
                    ), 
//                    array(
//                        'label' => 'Commission',
//                        'controller' => 'reports',
//                        'action' => 'commreport',
//                    ),
                    array(
                        'label' => 'Cardholder Activation',
                        'controller' => 'mvc_axis_reports',
                        'action' => 'cardholderactivations',
                    ), 
                    array(
                        'label' => 'Authorized Funding',
                        'controller' => 'reports',
                        'action' => 'agentfundrequests',
                    ),
                    array(
                        'label' => 'Fee',
                        'controller' => 'reports',
                        'action' => 'feereport',
                    ),
//                    array(
//                        'label' => 'Summary',
//                        'controller' => 'reports',
//                        'action' => 'agentsummary',
//                    ),
                    
                ),
              ),
            
            array(
                'main' => array(
                   'label' => 'BOI Remittance',
                        'controller' => 'remit_boi_remitter',
                        'action' => 'adddetails',
                ),
                'pages' => array(
                    array(

                        'label' => 'Enroll Remitter',                       
                        'controller' => 'remit_boi_remitter',
                        'action' => 'adddetails',
                        'scope' => '*'
                    ),
                     array(
                        'label' => 'Fund Transfer',
                        'controller' => 'remit_boi_beneficiary',
                        'action' => 'searchremitter',
                        'scope' => '*'
                        ),

                ),
            ),
            
               array(
                'main' => array(
                   'label' => 'Kotak Remittance',
                        'controller' => 'remit_kotak_remitter',
                        'action' => 'adddetails',
                ),
                'pages' => array(
                    array(

                        'label' => 'Enroll Remitter',                       
                        'controller' => 'remit_kotak_remitter',
                        'action' => 'adddetails',
                        'scope' => '*'
                    ),
                     array(
                        'label' => 'Fund Transfer',
                        'controller' => 'remit_kotak_beneficiary',
                        'action' => 'searchremitter',
                        'scope' => '*'
                        ),
                     array(
                        'label' => 'Remitter Transactions',
                        'controller' => 'remit_kotak_remitter',
                        'action' => 'transactions',
                        'scope' => '*'
                        ),

                ),
            ),
            
            array(
                'main' => array(
                    'label' => 'Hospital',
                    'controller' => 'corp_ratnakar_hospital',
                    'action' => 'index'
                ),
                'pages' => array(
                    array(
                        'label' => 'Hospital',
                        'controller' => 'corp_ratnakar_hospital',
                        'action' => 'index',
                    ),
                ),
                
              
            ),
//             array(
//                'main' => array(
//                    'label' => 'Card Load',
//                        'controller' => 'corp_ratnakar_cardload',
//                        'action' => 'search',
//                ),
//               'pages' => array(
//                    array(
//                        'label' => 'Card Load',
//                        'controller' => 'corp_ratnakar_cardload',
//                        'action' => 'search',
//                    ),
//                    array(
//                        'label' => 'Check Status',
//                        'controller' => 'corp_ratnakar_cardload',
//                        'action' => 'checkstatus',
//                    ),
//                ),
//            ),
//            
            
            
//            array(
//                'main' => array(
//                    'label' => 'Corporate',
//                        'controller' => 'corp_ratnakar_cardholder',
//                        'action' => 'add',
//                ),
//               'pages' => array(
//                    array(
//                        'label' => 'Add Cardholder',
//                        'controller' => 'corp_ratnakar_cardholder',
//                        'action' => 'add',
//                    ),
//                    array(
//                        'label' => 'Search Cardholder',
//                        'controller' => 'corp_ratnakar_cardholder',
//                        'action' => 'search',
//                    ),
//                ),
//            ),
        );
        
        return $pages;
    }
    
    /**
     * Returns an array with all the pages that will be available for
     * the current user
     * 
     * @param array $data
     * @access protected
     * @return array
     */
    protected function _filter($data) {
        $filtered = array();

        foreach ($data as $tab) {
            $filteredPages = array();
            if (isset($tab['main'])) {
                if (App_FlagFlippers_Manager::isAllowed(NULL, $tab['main']['controller'], $tab['main']['action'])) {
                    if (isset($tab['pages'])) {
                        foreach ($tab['pages'] as $page) {
                            if (App_FlagFlippers_Manager::isAllowed(NULL, $page['controller'], $page['action'])) {
                                $filteredPages[] = $page;
                            }
                        }
                    }
                    if (!empty($filteredPages)) {
                        $filteredTab = array(
                            'main' => $tab['main'],
                            'pages' => $filteredPages,
                        );
                        $filtered[] = $filteredTab;
                    }
                }
            }
        }
        return $filtered;
    }

    /**
     * Marks the current tab as active
     * 
     * @param arrat $pages 
     * @access protected
     * @return array
     */
    protected function _markActive($menu){
        $controllerName = Zend_Registry::get('controllerName');
        $actionName = Zend_Registry::get('actionName');
        
        foreach($menu as $tabKey => $tab){
            if ($controllerName === $tab['main']['controller'] && $actionName === $tab['main']['action']){
                $menu[$tabKey]['main']['active'] = TRUE;
            }
            
            if(isset($tab['pages'])){
                foreach($tab['pages'] as $pagekey => $page) {
                    if($controllerName === $page['controller'] && ($actionName === $page['action'] || (array_key_exists('scope', $page) && $page['scope'] == '*'))){
                        $menu[$tabKey]['pages'][$pagekey]['active'] = TRUE;
                        $menu[$tabKey]['main']['active'] = TRUE;
                        break;
                    }
                }
            }
        }
        
        return $menu;
    }
    
    /**
     * Returns the navigation array
     * 
     * @access public
     * @return array
     */
    public function getNavigation(){
        
        return $this->_navigation;
    }
    
    /**
     * Update the navigation array
     * 
     * @access public
     * @param array $menu
     * @return array
     */
    public function setNavigation(array $menu){
        $this->_navigation = $menu;
    }
}