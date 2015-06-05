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
class App_Operation_Navigation {

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
            self::$_instance = new App_Operation_Navigation();
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
                    'action' => 'index',
                ),
                'pages' => array(array(
                        'label' => 'Summary',
                        'controller' => 'profile',
                        'action' => 'index',
                        'scope' => '*'
                    ),
                ),
            ),
            
            array(
                'main' => array(
                    'label' => 'Wallets',
                    'controller' => 'wallets',
                    'action' => 'index',
                ),
                'pages' => array(array(
                        'label' => 'Wallets',
                        'controller' => 'wallets',
                        'action' => 'index',
                        'scope' => '*'
                    ),
                ),
            ),
            array(
                'main' => array(
                    'label' => 'Banks',
                    'controller' => 'bank',
                    'action' => 'index',
                ),
                'pages' => array(array(
                        'label' => 'Banks',
                        'controller' => 'bank',
                        'action' => 'index',
                        'scope' => '*'
                    ),
                ),
            ),
            array(
                'main' => array(
                    'label' => 'Products',
                    'controller' => 'product',
                    'action' => 'index',
                ),
                'pages' => array(array(
                        'label' => 'Products',
                        'controller' => 'product',
                        'action' => 'index',
                        'scope' => '*'
                    ),
                ),
            ),
//            array(
//                'main' => array(
//                    'label' => 'Corporates',
//                    'controller' => 'corporate',
//                    'action' => 'index',
//                ),
//                'pages' => array(array(
//                        'label' => 'Corporates',
//                        'controller' => 'corporate',
//                        'action' => 'index',
//                        'scope' => '*'
//                    ),
//                ),
//            ),
            array(
                'main' => array(
                    'label' => 'Agent Commissions',
                    'controller' => 'commissionplan',
                    'action' => 'index',
                ),
                'pages' => array(
                    array(
                        'label' => 'Agent Commissions',
                        'controller' => 'commissionplan',
                        'action' => 'index',
                        'scope' => '*'
                    ),
                ),
            ),
            array(
                'main' => array(
                    'label' => 'Fee Plans',
                    'controller' => 'feeplan',
                    'action' => 'index',
                ),
                'pages' => array(
                    array(
                        'label' => 'Fee Plans',
                        'controller' => 'feeplan',
                        'action' => 'index',
                        'scope' => '*'
                    ),
                ),
            ),
            array(
                'main' => array(
                    'label' => 'Agent Limits',
                    'controller' => 'agentlimit',
                    'action' => 'limit',
                ),
                'pages' => array(
                    array(
                        'label' => 'Agent Limits',
                        'controller' => 'agentlimit',
                        'action' => 'limit',
                        'scope' => '*'
                    ),
                ),
            ),
            array(
                'main' => array(
                    'label' => 'Agents',
                    'controller' => 'agents',
                    'action' => 'index',
                ),
                'pages' => array(
                    array(
                        'label' => 'Agents',
                        'controller' => 'agentsummary',
                        'action' => 'index',
                    ),
                    array(
                        'label' => 'Approval Pending',
                        'controller' => 'approveagent',
                        'action' => 'index',
                    ),
                    array(
                        'label' => 'Rejected Agents',
                        'controller' => 'approveagent',
                        'action' => 'rejectedlist',
                    ),
                    array(
                        'label' => 'Agents Balance Alert',
                        'controller' => 'agents',
                        'action' => 'agentbalancealert',
                    ),
                    array(
                        'label' => 'Agent Import',
                        'controller' => 'reports',
                        'action' => 'agentimport',
                    ),
                     array(
                        'label' => 'Search Agent Import',
                        'controller' => 'reports',
                        'action' => 'searchagentimport',
                    )
                ),
            ),
            array(
                'main' => array(
                    'label' => 'Agent Funding',
                    'controller' => 'agentfunding',
                    'action' => 'index',
                ),
                'pages' => array(
                    array(
                        'label' => 'Upload Bank Statement',
                        'controller' => 'agentfunding',
                        'action' => 'uploadbankstatement',
                    ),
                    array(
                        'label' => 'Upload Kotak/ Ratnakar/ ICICI Bank Statement',
                        'controller' => 'agentfunding',
                        'action' => 'uploadkotakbanktatement',
                    ),
                    array(
                        'label' => 'Pending Fund Request',
                        'controller' => 'agentfunding',
                        'action' => 'pendingfundrequest',
                    ),
                    array(
                        'label' => 'Pending Virtual Fund Request',
                        'controller' => 'agentfunding',
                        'action' => 'virtualfundrequests',
                    ),
                    array(
                        'label' => 'Unsettled Bank Statement',
                        'controller' => 'agentfunding',
                        'action' => 'unsettledbankstatement',
                    ),
                    array(
                        'label' => 'Settled Fund Request',
                        'controller' => 'agentfunding',
                        'action' => 'settledfundrequest',
                    ),
                )
            ),
      
            array(
                'main' => array(
                    'label' => 'BOI Remittance',
                    'controller' => 'remit_boi_remitter',
                    'action' => 'index',
                ),
                'pages' => array(
                    array(
                        'label' => 'Search',
                        'controller' => 'remit_boi_remitter',
                        'action' => 'search',
                    ),
                    array(
                        'label' => 'NEFT Instruction Batches',
                        'controller' => 'remit_boi_remitter',
                        'action' => 'neftrequests',
                    ),
                    array(
                        'label' => 'NEFT Response',
                        'controller' => 'remit_boi_remitter',
                        'action' => 'neftresponse',
                    ),
                ),
            ),
            array(
                'main' => array(
                    'label' => 'BOI NSDC',
                    'controller' => 'corp_boi_index',
                    'action' => 'index',
                ),
                'pages' => array(
                       array(
                        'label' => 'Search Customers',
                        'controller' => 'corp_boi_customer',
                        'action' => 'customerlist',
                    ),
                      array(
                        'label' => 'Pending Applications',
                        'controller' => 'corp_boi_customer',
                        'action' => 'search',
                    ),
                    array(
                        'label' => 'Bank Pending Applications',
                        'controller' => 'corp_boi_customer',
                        'action' => 'bankpending',
                    ),
                      array(
                        'label' => 'Bank Status Applications',
                        'controller' => 'corp_boi_customer',
                        'action' => 'bankstatus',
                    ),
                    array(
                        'label' => 'Upload CRN',
                        'controller' => 'corp_boi_index',
                        'action' => 'uploadcrn',
                    ),
                    array(
                        'label' => 'CRN Status Report',
                        'controller' => 'corp_boi_index',
                        'action' => 'crnstatus',
                    ),
                    array(
                        'label' => 'Application Output File',
                        'controller' => 'corp_boi_customer',
                        'action' => 'outputfile',
                    ),
                   array(
                        'label' => 'Upload Account Activation File',
                        'controller' => 'corp_boi_customer',
                        'action' => 'uploaddeliveryflag',
                    ),
                    array(
                        'label' => 'Account Activation File Report',
                        'controller' => 'corp_boi_customer',
                        'action' => 'deliverystatus',
                    ),

                    array(
                        'label' => 'Wallet Load',
                        'controller' => 'corp_boi_customer',
                        'action' => 'accountload',
                    ),
                    array(
                        'label' => 'Card Load',
                        'controller' => 'corp_boi_index',
                        'action' => 'load',
                    ),
                     array(
                        'label' => 'Wallet Status',
                        'controller' => 'corp_boi_customer',
                        'action' => 'walletstatus',
                    ),
                   array(
                        'label' => 'Application Status Report',
                        'controller' => 'corp_boi_customer',
                        'action' => 'customerregistration',
                    ),
                     array(
                        'label' => 'Upload Card Mapping File',
                        'controller' => 'corp_boi_customer',
                        'action' => 'cardmapping',
                    ),
                     array(
                        'label' => 'Card Mapping File Report',
                        'controller' => 'corp_boi_customer',
                        'action' => 'cardmappingstatus',
                    ),
                   array(
                        'label' => 'TTUM File Generation',
                        'controller' => 'corp_boi_customer',
                        'action' => 'cuttofffile',
                    ),
//                    array(
//                        'label' => 'Customer Consolidated Report',
//                        'controller' => 'corp_boi_customer',
//                        'action' => 'consolidatedreport',
//                    ),
                    array(
                        'label' => 'Payment Status Report',
                        'controller' => 'corp_boi_reports',
                        'action' => 'paymentstatus',
                   ),
                    array(
                        'label' => 'Upload Disbursement File',
                        'controller' => 'corp_boi_index',
                        'action' => 'disbursementload',
                    ),
                    array(
                        'label' => 'Search Disbursement Details',
                        'controller' => 'corp_boi_index',
                        'action' => 'disbursementreport',
                    ),
                    array(
                        'label' => 'Download Disbursement TTUM File',
                        'controller' => 'corp_boi_index',
                        'action' => 'disbursemenfile',
                    ),
                    array(
                        'label' => 'BOI NSDC RBI Reporting',
                        'controller' => 'corp_boi_reports',
                        'action' => 'rbi',
                    ),

                    array(
                        'label' => 'Summary Bucket Report',
                        'controller' => 'corp_boi_index',
                        'action' => 'summarybucketreport',
                    ),
                    array(
                        'label' => 'Summary Report Payment',
                        'controller' => 'corp_boi_index',
                        'action' => 'summarypaymentreport',
                    ),
                    array(
                        'label' => 'Update Disbursement Status',
                        'controller' => 'corp_boi_index',
                        'action' => 'updatedisbursementstatus',
                    ),

                     array(
                        'label' => 'TP MIS Report',
                        'controller' => 'corp_boi_reports',
                        'action' => 'tpmisreport',
                    ),
                     array(
                        'label' => 'TP MIS Generic Report',
                        'controller' => 'corp_boi_reports',
                        'action' => 'tpmisgenericreport',
                    ),
                    array(
                        'label' => 'Disbursement Status Report',
                        'controller' => 'corp_boi_index',
                        'action' => 'disbursementstatusreport',
                    ),
                    array(
                        'label' => 'Disbursement Wallet/Card load Status',
                        'controller' => 'corp_boi_index',
                        'action' => 'disbursementcardloadreport',
                    ),
                    
                ),
            ),
            array(
                'main' => array(
                    'label' => 'Kotak Remittance',
                    'controller' => 'remit_kotak_remitter',
                    'action' => 'index',
                ),
                'pages' => array(
                    array(
                        'label' => 'Search',
                        'controller' => 'remit_kotak_remitter',
                        'action' => 'search',
                    ),
                    array(
                        'label' => 'Hold Transactions',
                        'controller' => 'remit_kotak_remitter',
                        'action' => 'holdtransactions',
                    ) 
                ),
            ),
           // ********************Start code:  Ratnakar Remittace Navigation ********************** 
           
            array(
                'main' => array(
                    'label' => 'Ratnakar Remittance',
                    'controller' => 'remit_ratnakar_remitter',
                    'action' => 'index',
                ),
                'pages' => array(
                    array(
                        'label' => 'Search',
                        'controller' => 'remit_ratnakar_remitter',
                        'action' => 'search',
                    ),
                    array(
                        'label' => 'Payment History',
                        'controller' => 'remit_ratnakar_remitter',
                        'action' => 'uploadpaymenthistory',
                    ),
                    array(
                        'label' => 'Response File ',
                        'controller' => 'remit_ratnakar_remitter',
                        'action' => 'uploadresponsepaymenthistory',
                    ),
//                    array(
//                        'label' => 'Hold Transactions',
//                        'controller' => 'remit_ratnakar_remitter',
//                        'action' => 'holdtransactions',
//                    ),
                    array(
                        'label' => 'NEFT Instruction Batches',
                        'controller' => 'remit_ratnakar_remitter',
                        'action' => 'neftrequests',
                    ),
                    array(
                        'label' => 'NEFT Response',
                        'controller' => 'remit_ratnakar_remitter',
                        'action' => 'neftresponse',
                    ),
                    array(
                        'label' => 'Manual Mapping',
                        'controller' => 'remit_ratnakar_remitter',
                        'action' => 'manualmapping',
                    ),
                     array(
                        'label' => 'Remittance Report',
                        'controller' => 'remit_ratnakar_remitter',
                        'action' => 'searchreport',
                    ),
                ),
            ),
           // ********************End code:  Ratnakar Remittace Navigation ********************** 
            array(
                'main' => array(
                    'label' => 'Kotak Amul',
                    'controller' => 'corp_kotak_customer',
                    'action' => 'index',
                ),
                'pages' => array(
                   
                     array(
                        'label' => 'Search Customers',
                        'controller' => 'corp_kotak_customer',
                        'action' => 'customerlist',
                    ),
                      array(
                        'label' => 'Pending Customers',
                        'controller' => 'corp_kotak_customer',
                        'action' => 'search',
                    ),
                      array(
                        'label' => 'Bank Status Applications',
                        'controller' => 'corp_kotak_customer',
                        'action' => 'bankstatus',
                    ),
                  
                    array(
                        'label' => 'Upload CRN',
                        'controller' => 'corp_kotak_customer',
                        'action' => 'uploadcrn',
                    ),
                    array(
                        'label' => 'Upload Delivery Flag File',
                        'controller' => 'corp_kotak_customer',
                        'action' => 'uploaddeliveryflag',
                    ),
                     array(
                        'label' => 'Delivery File Status Report',
                        'controller' => 'corp_kotak_customer',
                        'action' => 'deliverystatus',
                    ),
                     array(
                        'label' => 'Upload Card Load',
                        'controller' => 'corp_kotak_cardload',
                        'action' => 'cardload',
                    ),
                    
                     array(
                        'label' => 'Wallet Status',
                        'controller' => 'corp_kotak_cardload',
                        'action' => 'walletstatus',
                    ),
                     array(
                        'label' => 'Download Authorized Applications',
                        'controller' => 'corp_kotak_customer',
                        'action' => 'authorizedapplications',
                    ),
                ),
            ),
            array(
                'main' => array(
                    'label' => 'Kotak GPR',
                    'controller' => 'corp_kotak_cardholder',
                    'action' => 'index',
                ),
                'pages' => array(
                    array(
                        'label' => 'Upload Cardholders',
                        'controller' => 'corp_kotak_cardholder',
                        'action' => 'uploadcardholders',
                    ),
                    array(
                        'label' => 'Upload Cardholders- Activation Required',
                        'controller' => 'corp_kotak_cardholder',
                        'action' => 'cardholderactivationreq',
                    ),
                    array(
                        'label' => 'Approval Pending',
                        'controller' => 'corp_kotak_cardholder',
                        'action' => 'approvalpending',
                    ),
                    array(
                        'label' => 'Cardholder Batch Status',
                        'controller' => 'corp_kotak_cardholder',
                        'action' => 'batchstatus',
                    ),
                    array(
                        'label' => 'Search Cardholders',
                        'controller' => 'corp_kotak_cardholder',
                        'action' => 'searchcardholders',
                    ),
                    array(
                        'label' => 'Pending KYC Docs',
                        'controller' => 'corp_kotak_cardholder',
                        'action' => 'pendingkyc',
                    ),
                    array(
                        'label' => 'KYC Upgradation',
                        'controller' => 'corp_kotak_cardholder',
                        'action' => 'upgradekycsearch',
                    ),
                    array(
                        'label' => 'KYC Upgrade Report',
                        'controller' => 'corp_kotak_cardholder',
                        'action' => 'kycupgradereport',
                    ),
                    array(
                        'label' => 'Upload CRN',
                        'controller' => 'corp_kotak_cardholder',
                        'action' => 'uploadcrn',
                    ),
                    array(
                        'label' => 'CRN Status Report',
                        'controller' => 'corp_kotak_cardholder',
                        'action' => 'crnstatus',
                    ),
                    array(
                        'label' => 'Upload Corporate Wallet',
                        'controller' => 'corp_kotak_cardload',
                        'action' => 'corporateload',
                    ),
                    
                    array(
                        'label' => 'Wallet Status',
                        'controller' => 'corp_kotak_cardload',
                        'action' => 'walletstatusgpr',
                    ),
                    array(
                        'label' => 'Upload Manual Adjustment',
                        'controller' => 'corp_kotak_cardholder',
                        'action' => 'uploadma',
                    ),
                    array(
                        'label' => 'Search Manual Adjustment',
                        'controller' => 'corp_kotak_cardholder',
                        'action' => 'searchma',
                    ),
                    
                ),
            ),
            array(
                'main' => array(
                    'label' => 'Ratnakar',
                    'controller' => 'corp_ratnakar_cardholder',
                    'action' => 'index',
                ),
                'pages' => array(
                    array(
                        'label' => 'Upload Cardholders',
                        'controller' => 'corp_ratnakar_cardholder',
                        'action' => 'uploadcardholders',
                    ),
                    array(
                        'label' => 'Approval Pending',
                        'controller' => 'corp_ratnakar_cardholder',
                        'action' => 'approvalpending',
                    ),
                    array(
                        'label' => 'Upload Cardholders- Activation Required',
                        'controller' => 'corp_ratnakar_cardholder',
                        'action' => 'cardholderactivationreq',
                    ),
                    array(
                        'label' => 'Cardholder Batch Status',
                        'controller' => 'corp_ratnakar_cardholder',
                        'action' => 'batchstatus',
                    ),
                    array(
                        'label' => 'Search Cardholders',
                        'controller' => 'corp_ratnakar_cardholder',
                        'action' => 'searchcardholder',
                    ),
                    array(
                        'label' => 'Pending KYC Docs',
                        'controller' => 'corp_ratnakar_cardholder',
                        'action' => 'pendingkyc',
                    ),
                    array(
                        'label' => 'KYC Upgradation',
                        'controller' => 'corp_ratnakar_cardholder',
                        'action' => 'upgradekycsearch',
                    ),
                    array(
                        'label' => 'Upload CRN',
                        'controller' => 'corp_ratnakar_cardholder',
                        'action' => 'uploadcrn',
                    ),
                    array(
                        'label' => 'CRN Status Report',
                        'controller' => 'corp_ratnakar_cardholder',
                        'action' => 'crnstatus',
                    ),
                    array(
                        'label' => 'Upload Corporate Wallet',
                        'controller' => 'corp_ratnakar_cardload',
                        'action' => 'corporateload',
                    ),
                    array(
                        'label' => 'Wallet Status',
                        'controller' => 'corp_ratnakar_cardload',
                        'action' => 'walletstatus',
                    ),
                    array(
                        'label' => 'Upload Manual Adjustment',
                        'controller' => 'corp_ratnakar_cardholder',
                        'action' => 'uploadma',
                    ),
                    array(
                        'label' => 'Search Manual Adjustment',
                        'controller' => 'corp_ratnakar_cardholder',
                        'action' => 'searchma',
                    ),
                    array(
                        'label' => 'Download Unsettled Request',
                        'controller' => 'corp_ratnakar_cardload',
                        'action' => 'unsettlementrequests',
                    ),
                    array(
                        'label' => 'Upload Settlement Response',
                        'controller' => 'corp_ratnakar_cardload',
                        'action' => 'settlementresponse',
                    ),
                ),
            ),
            array(
                'main' => array(
                    'label' => 'Help Desk',
                    'controller' => 'helpdesk',
                    'action' => 'index'
                ),
                'pages' => array(
                    array(
                        'label' => 'Resend Activation Code',
                        'controller' => 'helpdesk',
                        'action' => 'resendactivationcode',
                    ),
                    array(
                        'label' => 'Update Mobile Number',
                        'controller' => 'helpdesk',
                        'action' => 'changeregisteredmobile',
                    ),
                    array(
                        'label' => 'Block Account',
                        'controller' => 'helpdesk',
                        'action' => 'blockaccount',
                    ),
                    array(
                        'label' => 'Unblock Account',
                        'controller' => 'helpdesk',
                        'action' => 'unblockaccount',
                    ),
                    array(
                        'label' => 'Close Account',
                        'controller' => 'helpdesk',
                        'action' => 'closeaccount',
                    ),
                    array(
                        'label' => 'Account Info',
                        'controller' => 'helpdesk',
                        'action' => 'queryaccountinfo',
                    ),
                    //Hiding link on Aniket Request as MVC is not ready. Will enable again on Thursday once the audit is over.
                    array(
                        'label' => 'MVC Status',
                        'controller' => 'helpdesk',
                        'action' => 'querymvcstatus',
                    ),
                    array(
                        'label' => 'MVC Transaction',
                        'controller' => 'helpdesk',
                        'action' => 'querymvctransaction',
                    ),
                    array(
                        'label' => 'Balance Inquiry',
                        'controller' => 'helpdesk',
                        'action' => 'balanceenquiry',
                    ),
                    array(
                        'label' => 'Kotak Remittance',
                        'controller' => 'helpdesk',
                        'action' => 'kotakremittance',
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
                        'label' => 'Axis Load Reload',
                        'controller' => 'mvc_axis_reports',
                        'action' => 'agentloadreload',
                    ),
                    array(
                        'label' => 'Axis Agent Wise Load',
                        'controller' => 'mvc_axis_reports',
                        'action' => 'agentwiseload',
                    ),
                    array(
                        'label' => 'Remittance Transaction',
                        'controller' => 'remit_reports',
                        'action' => 'remittancereport',
                    ),
                    array(
                        'label' => 'Agent Wise Remittance',
                        'controller' => 'remit_reports',
                        'action' => 'agentwiseremittancereport',
                    ),
                    array(
                        'label' => 'Remittance Exception',
                        'controller' => 'remit_reports',
                        'action' => 'remittanceexception',
                    ),
                    array(
                        'label' => 'Card Activation',
                        'controller' => 'mvc_axis_reports',
                        'action' => 'cardholderactivations',
                    ),
                    array(
                        'label' => 'Agent Authorized Funding',
                        'controller' => 'reports',
                        'action' => 'agentfundrequests',
                    ),
                    array(
                        'label' => 'Agent Wise Authorized Funding',
                        'controller' => 'reports',
                        'action' => 'agentwisefundrequests',
                    ),
                    array(
                        'label' => 'Agent Unauthorized Funding',
                        'controller' => 'reports',
                        'action' => 'pendingagentfundrequests',
                    ),
                    array(
                        'label' => 'Authorized Virtual Funding',
                        'controller' => 'reports',
                        'action' => 'agentvirtualfunding',
                    ),
                    array(
                        'label' => 'Unauthorized Virtual Funding',
                        'controller' => 'reports',
                        'action' => 'unauthorizevirtualfund',
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
                        'label' => 'Agent Wise Load/Reload Commission',
                        'controller' => 'mvc_axis_reports',
                        'action' => 'agentwiseloadreloadcomm',
                    ),
                    array(
                        'label' => 'Remittance Commission',
                        'controller' => 'remit_reports',
                        'action' => 'remittancecommission',
                    ),
                    array(
                        'label' => 'Agent Wise Remittance Commission',
                        'controller' => 'remit_reports',
                        'action' => 'agentwiseremittancecommission',
                    ),
                    array(
                        'label' => 'Agent Activation',
                        'controller' => 'reports',
                        'action' => 'agentactivation',
                    ),
                    array(
                        'label' => 'Agent Balance Sheet',
                        'controller' => 'reports',
                        'action' => 'agentbalancesheet',
                    ),
                    array(
                        'label' => 'Fee',
                        'controller' => 'reports',
                        'action' => 'feereport',
                    ),
                    array(
                        'label' => 'Agent Wise Fee',
                        'controller' => 'reports',
                        'action' => 'agentwisefeereport',
                    ),
                    array(
                        'label' => 'Remitter Registration',
                        'controller' => 'remit_reports',
                        'action' => 'remitterregn',
                    ),
                    array(
                        'label' => 'Remitter Transactions',
                        'controller' => 'remit_reports',
                        'action' => 'remittertransaction',
                    ),
                    array(
                        'label' => 'Product Wise Refund',
                        'controller' => 'remit_reports',
                        'action' => 'remittancerefund',
                    ),
                    array(
                        'label' => 'Remittance Refund Yet To Claim',
                        'controller' => 'remit_reports',
                        'action' => 'remittancerefundyettoclaim',
                    ),
                    array(
                        'label' => 'Remittance Response',
                        'controller' => 'remit_reports',
                        'action' => 'neftresponse',
                    ),
                     array(
                        'label' => 'Customer Registration',
                        'controller' => 'reports',
                        'action' => 'customerregistration',
                    ),
                     array(
                        'label' => 'Transaction Report Wallet-wise',
                        'controller' => 'reports',
                        'action' => 'wallettxn',
                    ),
                    array(
                        'label' => 'Login Summary',
                        'controller' => 'reports',
                        'action' => 'loginsummary',
                    ),
                    array(
                        'label' => 'User Login',
                        'controller' => 'reports',
                        'action' => 'userlogin',
                    ),
                    array(
                        'label' => 'Debit Mandate Amount Report',
                        'controller' => 'corp_boi_reports',
                        'action' => 'debitmandateamount',
                    ),

                     array(
                        'label' => 'Wallet Trial Balance Report',
                        'controller' => 'reports',
                        'action' => 'wallettrialbalance',
                    ),
                     array(
                        'label' => 'Remittance Wallet Trial Balance Report',
                        'controller' => 'reports',
                        'action' => 'remitwallettrialbalance',
                    ),
                    array(
                        'label' => 'Wallet Balance Report',
                        'controller' => 'reports',
                        'action' => 'walletbalance',
                    ),
                    array(
                        'label' => 'Virtual Wallet Balance Report',
                        'controller' => 'reports',
                        'action' => 'virtualwalletbalance',
                    ),
                    array(
                        'label' => 'Load Report',
                        'controller' => 'corp_ratnakar_reports',
                        'action' => 'loadreport',
                        'scope' => '*'
                    ),
                    array(
                        'label' => 'Agent Remittance Report',
                        'controller' => 'reports',
                        'action' => 'agentremittance',
                        'scope' => '*'
                    ),
                    
                    array(
                        'label' => 'Download Reports',
                        'controller' => 'reports',
                        'action' => 'downloadreports',
                    ),
                    array(
                        'label' => 'Beneficiary Registration Report',
                        'controller' => 'reports',
                        'action' => 'beneregistration',
                        
                    ),
                    
                    array(
                        'label' => 'Remit Kotak Failure Recon Report',
                        'controller' => 'remit_reports',
                        'action' => 'remitkotakfailurerecon',
                        
                    ),
                    array(
                        'label' => 'Multi Wallet Balance Report',
                        'controller' => 'reports',
                        'action' => 'multiwalletbalance',
                    ),
                    array(
                        'label' => 'Unsettled Report',
                        'controller' => 'reports',
                        'action' => 'unsettledreport',
                    ),
                    array(
                        'label' => 'Settled Report',
                        'controller' => 'reports',
                        'action' => 'settledreport',
                    ),
                    array(
                        'label' => 'Remittance Transaction Recon Report',
                        'controller' => 'remit_reports',
                        'action' => 'remitrecon'               
                    ),
		    array(
                        'label' => 'Wallet To Wallet Transfer report',
                        'controller' => 'reports',
                        'action' => 'w2wtransfer'               
                    ),
		    array(
                        'label' => 'Wallet Transfer Exceptions Report',
                        'controller' => 'reports',
                        'action' => 'wwftexceptions'               
                    ),
                    array(
                        'label' => 'Balance Sync Exception Report',
                        'controller' => 'corp_ratnakar_reports',
                        'action' => 'balancesyncexception'
                    ),
                ),
            ),
            array(
                'main' => array(
                    'label' => 'Settings',
                    'controller' => 'settings',
                    'action' => 'index',
                ),
                'pages' => array(array(
                        'label' => 'Program Type',
                        'controller' => 'programtype',
                        'action' => 'index',
                        'scope' => '*'
                    ),
                    array(
                        'label' => 'Transaction Type',
                        'controller' => 'transaction',
                        'action' => 'index',
                        'scope' => '*'
                    ),
                    array(
                        'label' => 'Fund Transfer Type',
                        'controller' => 'fundtransfertype',
                        'action' => 'index',
                        'scope' => '*'
                    ),
                    array(
                        'label' => 'Agent Fund Settings',
                        'controller' => 'agentsetting',
                        'action' => 'index',
                        'scope' => '*'
                    ),
                     array(
                        'label' => 'Add Agent City',
                        'controller' => 'settings',
                        'action' => 'addagentcity',
                        'scope' => '*'
                    ),
                     array(
                        'label' => 'Add Customer City',
                        'controller' => 'settings',
                        'action' => 'addcustomercity',
                        'scope' => '*'
                    ),
                     array(
                        'label' => 'Manage IFSC Code',
                        'controller' => 'settings',
                        'action' => 'manageifsc',
                        'scope' => '*'
                    ),
                ),
            ),
              array(
                'main' => array(
                    'label' => 'Corporate Limits',
                    'controller' => 'corporatelimit',
                    'action' => 'limit',
                ),
                'pages' => array(
                    array(
                        'label' => 'Corporate Limits',
                        'controller' => 'corporatelimit',
                        'action' => 'limit',
                        'scope' => '*'
                    ),
                ),
            ),
            array(
                'main' => array(
                    'label' => 'Corporates',
                    'controller' => 'corporates',
                    'action' => 'index',
                ),
                'pages' => array(
                     array(
                        'label' => 'Corporates',
                        'controller' => 'corporates',
                        'action' => 'index',
                    ),
                    array(
                        'label' => 'Approval Pending',
                        'controller' => 'approvecorporate',
                        'action' => 'index',
                    ),
                    array(
                        'label' => 'Rejected Corporates',
                        'controller' => 'approvecorporate',
                        'action' => 'rejectedlist',
                    ),
                ),
            ),
            array(
                'main' => array(
                    'label' => 'Corporate Funding',
                    'controller' => 'corporatefunding',
                    'action' => 'index',
                ),
                'pages' => array(
                    /*array(
                        'label' => 'Upload Bank Statement',
                        'controller' => 'corporatefunding',
                        'action' => 'uploadbankstatement',
                    ),*/
                    array(
                        'label' => 'Pending Fund Request',
                        'controller' => 'corporatefunding',
                        'action' => 'pendingfundrequest',
                    ),
                    array(
                        'label' => 'Unsettled Bank Statement',
                        'controller' => 'corporatefunding',
                        'action' => 'unsettledbankstatement',
                    ),
                    array(
                        'label' => 'Settled Fund Request',
                        'controller' => 'corporatefunding',
                        'action' => 'settledfundrequest',
                    ),
                ),
            ),
            array(
               'main' => array(
                   'label' => 'Card Request',
                   'controller' => 'cardrequest',
                   'action' => 'index'
               ),
               'pages' => array(
                   array(
                       'label' => 'Card Request',
		                   'controller' => 'cardrequest',
		                   'action' => 'cardrequestchkr'
                   ),
                   array(
                       'label' => 'Card Request',
		                   'controller' => 'cardrequest',
		                   'action' => 'cardrequestmkr'
                   ),
                   array(
                       'label' => 'Show Cards',
		                   'controller' => 'cardrequest',
		                   'action' => 'showcards',
                   ),
                   array(
                       'label' => 'Transfer Request',
		                   'controller' => 'cardrequest',
		                   'action' => 'showtransferrequest'
                   ),
                   array(
                       'label' => 'Update Card Status',
		                   'controller' => 'cardrequest',
		                   'action' => 'changecardstatus',
                   ),
                   array(
                       'label' => 'Card Balance Report',
		                   'controller' => 'cardrequest',
		                   'action' => 'cardbalance',
                   ),
                              
               ),
            ),
            array(
               'main' => array(
                   'label' => 'AML',
                   'controller' => 'aml',
                   'action' => 'index'
               ),
               'pages' => array(
                   array(
                       'label' => 'Upload AML Data',
		                   'controller' => 'aml',
		                   'action' => 'uploadaml'
                   ),
                   array(
                       'label' => 'Uploaded AML Report',
		                   'controller' => 'aml',
		                   'action' => 'amlbyops'
                   ),
                   array(
                       'label' => 'Display AML Records',
		                   'controller' => 'aml',
		                   'action' => 'displayaml'
                   ),
                   array(
                       'label' => 'AML Rejected Agents',
		                   'controller' => 'aml',
		                   'action' => 'amlrejectedagents'
                   ),
                   array(
                       'label' => 'Bank AML',
		                   'controller' => 'aml',
		                   'action' => 'bankindex'
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
