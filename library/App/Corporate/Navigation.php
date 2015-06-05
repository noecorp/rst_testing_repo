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
class App_Corporate_Navigation {

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
    protected function __construct() {
        $this->_navigation = $this->_markActive($this->_filter($this->_getPages()));
    }

    /**
     * Returns a singleton instance of the class
     * 
     * @access public
     * @return void
     */
    public static function getInstance() {
        if (NULL === self::$_instance) {
            self::$_instance = new App_Corporate_Navigation();
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
    public function __clone() {
        throw new Zend_Exception('Cloning singleton objects is forbidden');
    }

    /**
     * Returns an array of pages
     * 
     * @access protected
     * @return void
     */
    protected function _getPages() {
        $pages = array(
            array(
                'main' => array(
                    'label' => 'Home',
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
                    'label' => 'My Funding',
                    'controller' => 'corporatefunding',
                    'action' => 'index'
                ),
                'pages' => array(
                   
                    array(
                        'label' => 'Fund Request',
                        'controller' => 'corporatefunding',
                        'action' => 'requestfund',
                        'scope' => '*'
                    ),
                    
                     array(
                        'label' => 'My Fund Requests',
                        'controller' => 'corporatefunding',
                        'action' => 'fundrequest',
                        'scope' => '*'
                    ),
                ),
            ),
                  array(
                'main' => array(
                    'label' => 'Manage Cardholders',
                    'controller' => 'corp_ratnakar_cardholder',
                    'action' => 'add'
                ),
                'pages' => array(
                   
                    array(
                        'label' => 'Add Cardholder',
                        'controller' => 'corp_ratnakar_cardholder',
                        'action' => 'add',
                    ),
                    array(
                        'label' => 'Bulk Add Cardholder',
                        'controller' => 'corp_ratnakar_cardholder',
                        'action' => 'uploadcardholders',
                    ),
                    array(
                            'label' => 'Cardholder Card Load',
                            'controller' => 'corp_ratnakar_cardload',
                            'action' => 'corporatesingleload',
                    ),
                    array(
                        'label' => 'Bulk Upload of Card Load',
                        'controller' => 'corp_ratnakar_cardload',
                        'action' => 'corporateload',
                    ),
                    array(
                        'label' => 'Applications Rejected By Operations',
                        'controller' => 'corp_ratnakar_cardholder',
                        'action' => 'opsrejected',
                        'scope' => '*'
                    ),
                ),
            ),
            array(
                'main' => array(
                    'label' => 'Manage Cardholders',
                    'controller' => 'corp_kotak_cardholder',
                    'action' => 'add'
                ),
                'pages' => array(
                   
                    array(
                        'label' => 'Add Cardholder',
                        'controller' => 'corp_kotak_cardholder',
                        'action' => 'add',
                    ),
                    array(
                            'label' => 'Bulk Add Cardholder',
                            'controller' => 'corp_kotak_cardholder',
                            'action' => 'uploadcardholders',
                    ),
                    array(
                            'label' => 'Cardholder Card Load',
                            'controller' => 'corp_kotak_cardload',
                            'action' => 'cardload',
                    ),
                   
                    array(
                            'label' => 'Bulk Upload of Card Load',
                            'controller' => 'corp_kotak_cardload',
                            'action' => 'bulkcardload',
                    ),
                    array(
                        'label' => 'Applications Rejected By Operations',
                        'controller' => 'corp_kotak_cardholder',
                        'action' => 'opsrejected',
                        'scope' => '*'
                    ),
                ),
            ),
            array(
                'main' => array(
                    'label' => 'Reports',
                    'controller' => 'linkedcorporates',
                    'action' => 'supercorporate'
                ),
                'pages' => array(
                    array(
                        'label' => 'Load Report',
                        'controller' => 'corp_ratnakar_reports',
                        'action' => 'loadreport',
                    ),
                    array(
                        'label' => 'Card Activation',
                        'controller' => 'corp_ratnakar_reports',
                        'action' => 'activecards',
                    ),
                    array(
                        'label' => 'Funding Report',
                        'controller' => 'reports',
                        'action' => 'corporatefunding',
                    ),
                    array(
                        'label' => 'Load Report',
                        'controller' => 'corp_kotak_reports',
                        'action' => 'loadreport',
                    ),
                    array(
                        'label' => 'Kotak Enrollment Report', 
                        'controller' => 'corp_kotak_reports',
                        'action' => 'activecards',
                    ),
                    array(
                        'label' => 'Funding Report',
                        'controller' => 'corp_kotak_reports',
                        'action' => 'corporatefunding',
                    ),
                    
                ),
            ),
            array(
                'main' => array(
                    'label' => 'Manage TID',
                    'controller' => 'corp_ratnakar_tid',
                    'action' => 'uploadtid'
                ),
                'pages' => array(
                   
                    array(
                        'label' => 'Upload TID File',
                        'controller' => 'corp_ratnakar_tid',
                        'action' => 'uploadtid',
                    ),
                    array(
                            'label' => 'Bind TID to Purse',
                            'controller' => 'corp_ratnakar_tid',
                            'action' => 'bindtidpurse',
                    ),
                    array(
                        'label' => 'Change TID Status',
                        'controller' => 'corp_ratnakar_tid',
                        'action' => 'changestatus',
                    ),
                ),
            ),
        );

        return $pages;
    }
    
    
      /**
     * Returns an array of pages
     * 
     * @access protected
     * @return void
     */
    protected function _getSuperCorporatePages() {
        $pages = array(
            array(
                'main' => array(
                    'label' => 'Home',
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
                    'label' => 'Signup Regional',
                    'controller' => 'signup',
                    'action' => 'index'
                ),
                'pages' => array(
                    array(
                        'label' => 'Signup Regional',
                        'controller' => 'signup',
                        'action' => 'index',
                        'scope' => '*'
                    ),
                ),
            ),
            array(
                'main' => array(
                    'label' => 'My Funding',
                    'controller' => 'corporatefunding',
                    'action' => 'index'
                ),
                'pages' => array(
                   
                    array(
                        'label' => 'Funding Request',
                        'controller' => 'corporatefunding',
                        'action' => 'requestfund',
                        'scope' => '*'
                    ),
                    
                     array(
                        'label' => 'My Fund Requests',
                        'controller' => 'corporatefunding',
                        'action' => 'fundrequest',
                        'scope' => '*'
                    ),
                ),
            ),
            array(
                'main' => array(
                    'label' => 'Manage Cardholders',
                    'controller' => 'corp_ratnakar_cardholder',
                    'action' => 'add'
                ),
                'pages' => array(
                   
                    array(
                        'label' => 'Add Cardholder',
                        'controller' => 'corp_ratnakar_cardholder',
                        'action' => 'add',
                    ),
                    array(
                        'label' => 'Bulk Add Cardholder',
                        'controller' => 'corp_ratnakar_cardholder',
                        'action' => 'uploadcardholders',
                    ),
                    array(
                            'label' => 'Cardholder Card Load',
                            'controller' => 'corp_ratnakar_cardload',
                            'action' => 'corporatesingleload',
                    ),
                    array(
                        'label' => 'Bulk Upload of Card Load',
                        'controller' => 'corp_ratnakar_cardload',
                        'action' => 'corporateload',
                    ),
                    array(
                        'label' => 'Applications Rejected By Operations',
                        'controller' => 'corp_ratnakar_cardholder',
                        'action' => 'opsrejected',
                        'scope' => '*'
                    ),
                ),
            ),
             array(
                'main' => array(
                    'label' => 'Manage Cardholders',
                    'controller' => 'corp_kotak_cardholder',
                    'action' => 'add'
                ),
                'pages' => array(
                   
                    array(
                        'label' => 'Add Cardholder',
                        'controller' => 'corp_kotak_cardholder',
                        'action' => 'add',
                    ),
                    array(
                            'label' => 'Bulk Add Cardholder',
                            'controller' => 'corp_kotak_cardholder',
                            'action' => 'uploadcardholders',
                    ),
                    array(
                            'label' => 'Cardholder Card Load',
                            'controller' => 'corp_kotak_cardload',
                            'action' => 'cardload',
                    ),
                    
                    array(
                            'label' => 'Bulk Upload of Card Load',
                            'controller' => 'corp_kotak_cardload',
                            'action' => 'bulkcardload',
                    ),
                     array(
                        'label' => 'Applications Rejected By Operations',
                        'controller' => 'corp_kotak_cardholder',
                        'action' => 'opsrejected',
                        'scope' => '*'
                    ),
                ),
            ),
            array(
                    'main' => array(
                        'label' => 'Reports',
                        'controller' => 'linkedcorporates',
                        'action' => 'supercorporate'
                    ),
                    'pages' => array(
                        array(
                            'label' => 'Regional Listing',
                            'controller' => 'linkedcorporates',
                            'action' => 'subcorporatelisting',
                        ),
                        array(
                            'label' => 'Load Report',
                            'controller' => 'corp_ratnakar_reports',
                            'action' => 'loadreport',
                        ),
                        array(
                            'label' => 'Card Activation',
                            'controller' => 'corp_ratnakar_reports',
                            'action' => 'activecards',
                        ),
                        array(
                            'label' => 'Funding Report',
                            'controller' => 'reports',
                            'action' => 'corporatefunding',
                        ),
                        array(
                           'label' => 'Load Report',
                           'controller' => 'corp_kotak_reports',
                           'action' => 'loadreport',
                       ),
                       array(
                           'label' => 'Kotak Enrollment Report',
                           'controller' => 'corp_kotak_reports',
                           'action' => 'activecards',
                       ),
                       array(
                           'label' => 'Funding Report',
                           'controller' => 'corp_kotak_reports',
                           'action' => 'corporatefunding',
                       ),
                        //array(
                        //    'label' => 'Balance Sheet',
                        //    'controller' => 'reports',
                        //    'action' => 'balancesheet',
                        //),
                        
                    ),
                ),
                array(
                'main' => array(
                    'label' => 'Manage TID',
                    'controller' => 'corp_ratnakar_tid',
                    'action' => 'uploadtid'
                ),
                'pages' => array(
                   
                    array(
                        'label' => 'Upload TID File',
                        'controller' => 'corp_ratnakar_tid',
                        'action' => 'uploadtid',
                    ),
                    array(
                        'label' => 'Bind TID to Purse',
                        'controller' => 'corp_ratnakar_tid',
                        'action' => 'bindtidpurse',
                    ),
                    array(
                        'label' => 'Change TID Status',
                        'controller' => 'corp_ratnakar_tid',
                        'action' => 'changestatus',
                    ),
                ),
            ),
       );

        return $pages;
    }
    
    
    protected function _getDistributorCorporatePages() { 
      
            $pages = array(
                array(
                    'main' => array(
                        'label' => 'Home',
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
                        'label' => 'Signup Local',
                        'controller' => 'signup',
                        'action' => 'index'
                    ),
                    'pages' => array(
                        array(
                            'label' => 'Signup Local',
                            'controller' => 'signup',
                            'action' => 'index',
                            'scope' => '*'
                        ),
                    ),
                ),
                array(
                    'main' => array(
                        'label' => 'My Funding',
                        'controller' => 'corporatefunding',
                        'action' => 'index'
                    ),
                    'pages' => array(
                       
                        array(
                            'label' => 'Funding Request',
                            'controller' => 'corporatefunding',
                            'action' => 'requestfund',
                            'scope' => '*'
                        ),
                        
                         array(
                            'label' => 'My Fund Requests',
                            'controller' => 'corporatefunding',
                            'action' => 'fundrequest',
                            'scope' => '*'
                        ),
                    ),
                ),
                array(
                    'main' => array(
                        'label' => 'Enroll Employees',
                        'controller' => 'corp_ratnakar_cardholder',
                        'action' => 'add'
                    ),
                    'pages' => array(
                        array(
                            'label' => 'Enroll Employee',
                            'controller' => 'corp_ratnakar_cardholder',
                            'action' => 'add',
                        ),
                        array(
                            'label' => 'Bulk Enroll Employees',
                            'controller' => 'corp_ratnakar_cardholder',
                            'action' => 'uploadcardholders',
                        ),
                        array(
                            'label' => 'Employee Funding',
                            'controller' => 'corp_ratnakar_cardload',
                            'action' => 'corporatesingleload',
                        ),
                        array(
                            'label' => 'Bulk Employee Funding',
                            'controller' => 'corp_ratnakar_cardload',
                            'action' => 'corporateload',
                        ),
                        
                    ),
                ),
                array(
                   'main' => array(
                       'label' => 'Manage Cardholders',
                       'controller' => 'corp_kotak_cardholder',
                       'action' => 'add'
                   ),
                   'pages' => array(
                      
                       array(
                           'label' => 'Add Cardholder',
                           'controller' => 'corp_kotak_cardholder',
                           'action' => 'add',
                       ),
                       array(
                               'label' => 'Bulk Add Cardholder',
                               'controller' => 'corp_kotak_cardholder',
                               'action' => 'uploadcardholders',
                       ),
                       array(
                               'label' => 'Cardholder Card Load',
                               'controller' => 'corp_kotak_cardload',
                               'action' => 'cardload',
                       ),
                       array(
                               'label' => 'Bulk Upload of Card Load',
                               'controller' => 'corp_kotak_cardload',
                               'action' => 'bulkcardload',
                       ),
                        array(
                        'label' => 'Applications Rejected By Operations',
                        'controller' => 'corp_kotak_cardholder',
                        'action' => 'opsrejected',
                        'scope' => '*'
                    ),
                   ),
               ),
                array(
                    'main' => array(
                        'label' => 'Reports',
                        'controller' => 'linkedcorporates',
                        
                        'action' => 'supercorporate'
                    ),
                    'pages' => array(
                        array(
                            'label' => 'Local Listing',
                            'controller' => 'linkedcorporates',
                            'action' => 'subcorporatelisting',
                        ),
                        array(
                            'label' => 'Load Report',
                            'controller' => 'corp_ratnakar_reports',
                            'action' => 'loadreport',
                        ),
                        array(
                            'label' => 'Card Activation',
                            'controller' => 'corp_ratnakar_reports',
                            'action' => 'activecards',
                        ),
                        array(
                            'label' => 'Funding Report',
                            'controller' => 'reports',
                            'action' => 'corporatefunding',
                        ),
                        array(
                           'label' => 'Load Report',
                           'controller' => 'corp_kotak_reports',
                           'action' => 'loadreport',
                       ),
                       array(
                           'label' => 'Kotak Enrollment Report',
                           'controller' => 'corp_kotak_reports',
                           'action' => 'activecards',
                       ),
                       array(
                           'label' => 'Funding Report',
                           'controller' => 'corp_kotak_reports',
                           'action' => 'corporatefunding',
                       ),
                        
                        //array(
                        //    'label' => 'Balance Sheet',
                        //    'controller' => 'reports',
                        //    'action' => 'balancesheet',
                        //),
                    ),
                ),
                 array(
                'main' => array(
                    'label' => 'Manage TID',
                    'controller' => 'corp_ratnakar_tid',
                    'action' => 'uploadtid'
                ),
                'pages' => array(
                   
                    array(
                        'label' => 'Upload TID File',
                        'controller' => 'corp_ratnakar_tid',
                        'action' => 'uploadtid',
                    ),
                    array(
                        'label' => 'Bind TID to Purse',
                        'controller' => 'corp_ratnakar_tid',
                        'action' => 'bindtidpurse',
                    ),
                    array(
                        'label' => 'Change TID Status',
                        'controller' => 'corp_ratnakar_tid',
                        'action' => 'changestatus',
                    ),
                ),
            ),
                
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
    protected function _markActive($menu) {
        $controllerName = Zend_Registry::get('controllerName');
        $actionName = Zend_Registry::get('actionName');

        foreach ($menu as $tabKey => $tab) {
            if ($controllerName === $tab['main']['controller'] && $actionName === $tab['main']['action']) {
                $menu[$tabKey]['main']['active'] = TRUE;
            }

            if (isset($tab['pages'])) {
                foreach ($tab['pages'] as $pagekey => $page) {
                    if ($controllerName === $page['controller'] && ($actionName === $page['action'] || (array_key_exists('scope', $page) && $page['scope'] == '*'))) {
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
    public function getNavigation() {
        $user = Zend_Auth::getInstance()->getIdentity();
        if( $user->user_type == HEAD_CORPORATE ) {   
            return $this->_navigation = $this->_markActive($this->_filter($this->_getSuperCorporatePages()));            
        } elseif( $user->user_type == REGIONAL_CORPORATE ) {
            return $this->_navigation = $this->_markActive($this->_filter($this->_getDistributorCorporatePages()));            
        } else {
            return $this->_navigation = $this->_markActive($this->_filter($this->_getPages()));    
        }
    }

    /**
     * Update the navigation array
     * 
     * @access public
     * @param array $menu
     * @return array
     */
    public function setNavigation(array $menu) {
        $this->_navigation = $menu;
    }

}