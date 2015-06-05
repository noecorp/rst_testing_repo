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
class App_Bank_Navigation {

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
        $page = $this->_getPages();
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
            self::$_instance = new App_Bank_Navigation();
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
                    'label' => 'Kotak Amul',
                    'controller' => 'corp_kotak_customer',
                    'action' => 'index',
                ),
                'pages' => array(
                    array(
                        'label' => 'Pending Customers',
                        'controller' => 'corp_kotak_customer',
                        'action' => 'search',
                    ),
                    array(
                        'label' => 'Accept Physical Document',
                        'controller' => 'corp_kotak_customer',
                        'action' => 'acceptdocument',
                    ),
                ),
            ),
             array(
                'main' => array(
                    'label' => 'Reports',
                    'controller' => 'corp_kotak_reports',
                    'action' => 'index'
                ),
                'pages' => array(
                    array(
                        'label' => 'Load/Reload',
                        'controller' => 'corp_kotak_reports',
                        'action' => 'applications',
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
        return $this->_navigation;
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