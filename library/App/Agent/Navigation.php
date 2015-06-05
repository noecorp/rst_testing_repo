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
class App_Agent_Navigation {

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
            /*array(
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
            ),*/
            array(
                'main' => array(
                    'label' => 'Partner Funding',
                    'controller' => 'agentfunding',
                    'action' => 'index'
                ),
                'pages' => array(
                   
                    array(
                        'label' => 'Partner Funding Request',
                        'controller' => 'agentfunding',
                        'action' => 'requestfund',
                        'scope' => '*'
                    ),
                    
                     array(
                        'label' => 'My Fund Requests',
                        'controller' => 'agentfunding',
                        'action' => 'fundrequest',
                        'scope' => '*'
                    ),
                    array(
                        'label' => 'Request Virtual Fund',
                        'controller' => 'agentfunding',
                        'action' => 'requestvirtualfund',
                        'scope' => '*'
                    ),
                    array(
                        'label' => 'My Virtual Fund Requests',
                        'controller' => 'agentfunding',
                        'action' => 'virtualfundrequest',
                        'scope' => '*'
                    ),
                ),
            ),array(
                'main' => array(
                    'label' => 'Card Load',
                    'controller' => 'agentcorpcardload',
                    'action' => 'load'
                ),
                'pages' => array(
                    array(
                        'label' => 'Card Load',
                        'controller' => 'agentcorpcardload',
                        'action' => 'load',
                        'scope' => '*'
                    )
                )
            ),
            array(
                'main' => array(
                    'label' => 'Card Load',
                    'controller' => 'mvc_axis_cardholderfund',
                    'action' => 'mobile'
                ),
                'pages' => array(
                    array(
                        'label' => 'Card Load',
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
                    /*array(
                        'label' => 'Agent Transaction Summary',
                        'controller' => 'reports',
                        'action' => 'agentsummary',
                    ),*/
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
                        'label' => 'Remittance',
                        'controller' => 'remit_kotak_reports',
                        'action' => 'remittancereport',
                    ),
                     array(
                        'label' => 'Remittance',
                        'controller' => 'remit_ratnakar_reports',
                        'action' => 'remittancereport',
                    ),
                    /*array(
                        'label' => 'Agent Commission Summary',
                        'controller' => 'reports',
                        'action' => 'agentcommissionsummary',
                    ),*/
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
                     array(
                        'label' => 'Remittance Commission',
                        'controller' => 'remit_kotak_reports',
                        'action' => 'remittancecommission',
                    ), 
                     /* array(
                        'label' => 'Remittance Commission',
                        'controller' => 'remit_ratnakar_reports',
                        'action' => 'remittancecommission',
                    ),*/

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
                    /*array(
                        'label' => 'Fee',
                        'controller' => 'reports',
                        'action' => 'feereport',
                    ),*/
                    array(
                        'label' => 'Balance Sheet',
                        'controller' => 'reports',
                        'action' => 'balancesheet',
                    ),
                     array(
                        'label' => 'Application Status Report',
                        'controller' => 'corp_boi_reports',
                        'action' => 'customerregistration',
                    ),
//                    array(
//                        'label' => 'Consolidated Report',
//                        'controller' => 'corp_boi_reports',
//                        'action' => 'consolidatedreport',
//                    ),
                    
                    
                    array(
                        'label' => 'Customer Registration Report',
                        'controller' => 'corp_ratnakar_reports',
                        'action' => 'customerregistration',
                        'scope' => '*'
                    ),
                    
                    array(
                        'label' => 'Load Report',
                        'controller' => 'corp_ratnakar_reports',
                        'action' => 'loadreport',
                        'scope' => '*'
                    ),
                    
                    array(
                        'label' => 'Wallet Wise Transaction Report',
                        'controller' => 'corp_ratnakar_reports',
                        'action' => 'walletwisetransactionreport',
                        'scope' => '*'
                    ),
                     array(
                        'label' => 'Partner Funding Report',
                        'controller' => 'reports',
                        'action' => 'agentfunding',
                        'scope' => '*'
                    ),
		    array(
                        'label' => 'Wallet To Wallet Transfer Report',
                        'controller' => 'reports',
                        'action' => 'w2wtransfer',
                        'scope' => '*'
                    ),
                    
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
                    'label' => 'Ratnakar Remittance',
                    'controller' => 'remit_ratnakar_remitter',
                    'action' => 'adddetails',
                ),
                'pages' => array(
                    array(
                        'label' => 'Enroll Remitter',
                        'controller' => 'remit_ratnakar_remitter',
                        'action' => 'adddetails',
                        'scope' => '*'
                    ),
                    array(
                        'label' => 'Fund Transfer',
                        'controller' => 'remit_ratnakar_beneficiary',
                        'action' => 'searchremitter',
                        'scope' => '*'
                    ),
                    array(
                        'label' => 'Remitter Transactions',
                        'controller' => 'remit_ratnakar_remitter',
                        'action' => 'transactions',
                        'scope' => '*'
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
                        'label' => 'Enroll Customer',
                        'controller' => 'corp_kotak_customer',
                        'action' => 'adddetails',
                        'scope' => '*'
                    ),
                    array(
                        'label' => 'Applications Rejected By Operations',
                        'controller' => 'corp_kotak_customer',
                        'action' => 'opsrejected',
                        'scope' => '*'
                    ),
                ),
            ),
            // BOI Customer
                   array(
                'main' => array(
                    'label' => 'BOI NSDC',
                    'controller' => 'corp_boi_customer',
                    'action' => 'index',
                ),
                'pages' => array(
                    array(
                        'label' => 'Add Cardholder - Capture Account Opening Form (AOF)',
                        'controller' => 'corp_boi_customer',
                        'action' => 'adddetails',
                        'scope' => '*'
                    ),
                    array(
                        'label' => 'Applications Rejected By Operations',
                        'controller' => 'corp_boi_customer',
                        'action' => 'opsrejected',
                        'scope' => '*'
                    ),
                ),
            ),
            //Shmart Ideacts Navigation
            array(
                'main' => array(
                    'label' => 'Shmart Ideacts',
                    'controller' => 'remit_kotakideacts_remitter',
                    'action' => 'adddetails',
                ),
                'pages' => array(
                    array(
                        'label' => 'Enroll Remitter',
                        'controller' => 'remit_kotakideacts_remitter',
                        'action' => 'adddetails',
                        'scope' => '*'
                    ),
                    array(
                        'label' => 'Fund Transfer',
                        'controller' => 'remit_kotakideacts_beneficiary',
                        'action' => 'searchremitter',
                        'scope' => '*'
                    ),
                    array(
                        'label' => 'Remitter Transactions',
                        'controller' => 'remit_kotakideacts_remitter',
                        'action' => 'transactions',
                        'scope' => '*'
                    ),
                ),
            ),     
            
               
            
            /*array(
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
            ),*/
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
     * Returns an array of pages
     * 
     * @access protected
     * @return void
     */
    protected function _getSuperAgentPages() {
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
                    'label' => 'Signup Distributor',
                    'controller' => 'signup',
                    'action' => 'index'
                ),
                'pages' => array(
                    array(
                        'label' => 'Signup Distributor',
                        'controller' => 'signup',
                        'action' => 'index',
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
                    'label' => 'My Funding',
                    'controller' => 'agentfunding',
                    'action' => 'index'
                ),
                'pages' => array(
                   
                    array(
                        'label' => 'Request Fund',
                        'controller' => 'agentfunding',
                        'action' => 'requestfund',
                        'scope' => '*'
                    ),
                    
                     array(
                        'label' => 'My Fund Requests',
                        'controller' => 'agentfunding',
                        'action' => 'fundrequest',
                        'scope' => '*'
                    ),
                    array(
                        'label' => 'Request Virtual Fund',
                        'controller' => 'agentfunding',
                        'action' => 'requestvirtualfund',
                        'scope' => '*'
                    ),
                    array(
                        'label' => 'My Virtual Fund Requests',
                        'controller' => 'agentfunding',
                        'action' => 'virtualfundrequest',
                        'scope' => '*'
                    ),
                ),
            ),
            array(
                'main' => array(
                    'label' => 'Reports',
                    'controller' => 'linkedagents',
                    'action' => 'superagent'
                ),
                'pages' => array(
                    array(
                        'label' => 'Distributor Listing',
                        'controller' => 'linkedagents',
                        'action' => 'subagentlisting',
                    ),
                     array(
                        'label' => 'Remittance',
                        'controller' => 'remit_ratnakar_reports',
                        'action' => 'superdistributorremittancereport',
                    ),
                    array(
                        'label' => 'Remittance MIS',
                        'controller' => 'remit_ratnakar_reports',
                        'action' => 'superdistributormisreport',
                    ),
                    array(
                        'label' => 'Balance Sheet',
                        'controller' => 'reports',
                        'action' => 'balancesheet',
                    ),
//                   array(
//                        'label' => 'Consolidated Report',
//                        'controller' => 'corp_boi_reports',
//                        'action' => 'consolidatedreport',
//                         ),
                      
                ),
            ),
       );

        return $pages;
    }
    
    
    protected function _getDistributorAgentPages() {
        $user = Zend_Auth::getInstance()->getIdentity();
        $bankUnicodeArr = Util::bankUnicodesArray();
        $agentModel = new AgentUser();
        $agentProduct = $agentModel->getAgentBinding($user->id);
        $product = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NDSC);
        $productUnicode = $product->product->unicode;
        if ($productUnicode == $agentProduct['product_unicode']) {
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
                    'label' => 'Signup Training Center BC',
                    'controller' => 'signup',
                    'action' => 'index'
                ),
                'pages' => array(
                    array(
                        'label' => 'Signup Training Center BC',
                        'controller' => 'signup',
                        'action' => 'index'
                    ),
                    'pages' => array(
                        array(
                            'label' => 'Signup Partner',
                            'controller' => 'signup',
                            'action' => 'index',
                            'scope' => '*'
                        ),
                    ),
                     array(
                        'label' => 'Training Center BC Listing',
                        'controller' => 'reports',
                        'action' => 'bclisting',
                    ),
                ),
                ),
                array(
                    'main' => array(
                        'label' => 'Reports',
                        'controller' => 'linkedagents',
                        'action' => 'superagent'
                    ),
                    'pages' => array(
                        array(
                            'label' => 'Application Status Report',
                            'controller' => 'corp_boi_reports',
                            'action' => 'customerregistration',
                        ),
                        
//                       array(
//                        'label' => 'Consolidated Report',
//                        'controller' => 'corp_boi_reports',
//                        'action' => 'consolidatedreport',
//                         ),
                         array(
                        'label' => 'Training Center BC Listing',
                        'controller' => 'reports',
                        'action' => 'bclisting',
                        ),
                         array(
                        'label' => 'Payment Status Report',
                        'controller' => 'corp_boi_reports',
                        'action' => 'tpmisreport',
                    ),
                    
                    ),
                ),
            );
        } elseif($user->bank_unicode == $bankUnicodeArr[3]) {
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
                        'label' => 'Signup Agent',
                        'controller' => 'signup',
                        'action' => 'index'
                    ),
                    'pages' => array(
                        array(
                            'label' => 'Signup Agent',
                            'controller' => 'signup',
                            'action' => 'index',
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
                        'label' => 'My Funding',
                        'controller' => 'agentfunding',
                        'action' => 'index'
                    ),
                    'pages' => array(
                        array(
                            'label' => 'Request Fund',
                            'controller' => 'agentfunding',
                            'action' => 'requestfund',
                            'scope' => '*'
                        ),
                        array(
                            'label' => 'My Fund Requests',
                            'controller' => 'agentfunding',
                            'action' => 'fundrequest',
                            'scope' => '*'
                        ),
                        array(
                            'label' => 'Request Virtual Fund',
                            'controller' => 'agentfunding',
                            'action' => 'requestvirtualfund',
                            'scope' => '*'
                        ),
                        array(
                            'label' => 'My Virtual Fund Requests',
                            'controller' => 'agentfunding',
                            'action' => 'virtualfundrequest',
                            'scope' => '*'
                        ),
                    ),
                ),
                array(
                    'main' => array(
                        'label' => 'Reports',
                        'controller' => 'linkedagents',
                        'action' => 'superagent'
                    ),
                    'pages' => array(
                        array(
                            'label' => 'Agent Listing',
                            'controller' => 'linkedagents',
                            'action' => 'subagentlisting',
                        ),
                        array(
                            'label' => 'Balance Sheet',
                            'controller' => 'reports',
                            'action' => 'balancesheet',
                        ),  
                    ),
                ),
            );
        } else {
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
                        'label' => 'Signup Agent',
                        'controller' => 'signup',
                        'action' => 'index'
                    ),
                    'pages' => array(
                        array(
                            'label' => 'Signup Agent',
                            'controller' => 'signup',
                            'action' => 'index',
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
                        'label' => 'My Funding',
                        'controller' => 'agentfunding',
                        'action' => 'index'
                    ),
                    'pages' => array(
                        array(
                            'label' => 'Request Fund',
                            'controller' => 'agentfunding',
                            'action' => 'requestfund',
                            'scope' => '*'
                        ),
                        array(
                            'label' => 'My Fund Requests',
                            'controller' => 'agentfunding',
                            'action' => 'fundrequest',
                            'scope' => '*'
                        ),
                        array(
                            'label' => 'Request Virtual Fund',
                            'controller' => 'agentfunding',
                            'action' => 'requestvirtualfund',
                            'scope' => '*'
                        ),
                        array(
                            'label' => 'My Virtual Fund Requests',
                            'controller' => 'agentfunding',
                            'action' => 'virtualfundrequest',
                            'scope' => '*'
                        ),
                    ),
                ),
                array(
                    'main' => array(
                        'label' => 'Reports',
                        'controller' => 'linkedagents',
                        'action' => 'superagent'
                    ),
                    'pages' => array(
                        array(
                            'label' => 'Agent Listing',
                            'controller' => 'linkedagents',
                            'action' => 'subagentlisting',
                        ),
                        array(
                            'label' => 'Balance Sheet',
                            'controller' => 'reports',
                            'action' => 'balancesheet',
                        ),
                         array(
                        'label' => 'Remittance',
                        'controller' => 'remit_ratnakar_reports',
                        'action' => 'distributorremittancereport',
                    ),
                         array(
                        'label' => 'Remittance MIS',
                        'controller' => 'remit_ratnakar_reports',
                        'action' => 'distributormisreport',
                    ),
                        
                    ),
                ),
            );
        }

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
        if( isset($user->user_type) && $user->user_type == SUPER_AGENT ) {
            return $this->_navigation = $this->_markActive($this->_filter($this->_getSuperAgentPages()));            
        } elseif( isset($user->user_type) && $user->user_type == DISTRIBUTOR_AGENT ) {
            return $this->_navigation = $this->_markActive($this->_filter($this->_getDistributorAgentPages()));            
        } else {
            return $this->_navigation;
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
