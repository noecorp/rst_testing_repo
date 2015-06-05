<?php

/**
 * Cardholder Steps configuration
 * To define the steps required for different products
 * 
 */

$_STEPS = array(
                    '1' => array('steps'=>array('basic', 'step2'), 'activation_status'=>'signup'),
                    '3' => array('steps'=>array('basic', 'step2'), 'activation_status'=>'signup'),
                    '7' => array('steps'=>array('basic', 'step2', 'step4', 'step4'), 'activation_status'=>'operation'),
               );

$_SHMART_REWARDS = array(
                        'books'=>'Books', 
                        'travel'=>'Travel', 
                        'movies'=>'Movies', 
                        'shopping'=>'Shopping', 
                        'electronic'=>'Electronic',
                        'music'=>'Music', 
                        'automobiles'=>'Automobiles'
                       );


$_CARDHOLDER_OFFERS_FIELDS = array(
                            'books'=>'is_book', 
                            'travel'=>'is_travel', 
                            'movies'=>'is_movies', 
                            'shopping'=>'is_shopping', 
                            'electronics'=>'is_electronics',
                            'music'=>'is_music', 
                            'automobiles'=>'is_automobiles'
                           );



$_TXN_ERROR_MESSAGES = array(TXNTYPE_CARD_RELOAD=> array('101'=>'Cardholder registration failed', 
                                                         '102'=>'Cardholder fund load failed'
                                                        ),
                             TXNTYPE_CARDHOLDER_REGISTRATION=> array('050'=>'Error in allocating the buffer.', 
                                            '051'=>'Error in tpacall',
                                            '052'=>'Error in tpinit/tpalloc',
                                            '053'=>'Error in tpinit/tpalloc',
                                            '100' => 'Database Error',
                                            '101' => 'Query Failed',
                                            '102' => 'No Matching records',
                                            '110' => 'Invalid ID, Authkey and Channel combination',
                                            '111' => 'Invalid ID, Sessionkey and Channel combination',
                                            '112' => 'Deactivated Account',
                                            '113' => 'Invalid Cardnumber/Passcode',
                                            '114' => 'Error in generating STAN',
                                            
                                            
                                            )
                            );


$_TXN_TYPE_LABELS = array(
                            TXNTYPE_FIRST_LOAD=>'Card Load', 
                            TXNTYPE_CARD_RELOAD=>'Card Reload',                            
                            TXNTYPE_REMITTANCE=>'Remittance',
                            TXNTYPE_REMITTER_REGISTRATION=>'Remitter Registration Fee',                            
                            TXNTYPE_REMITTANCE_SERVICE_TAX=>'Service Tax',                            
                            TXNTYPE_REMITTANCE_REFUND=>'Remittance Refund',                            
                            TXNTYPE_REMITTANCE_REFUND_FEE=>'Remittance Refund Fee',                            
                            TXNTYPE_REMITTANCE_FEE=>'Remittance Fee',                            
                            TXNTYPE_REMITTANCE_REVERSAL_REFUND_FEE=>'Remittance Reversal Refund Fee',                            
                            TXNTYPE_REMITTANCE_REVERSAL_SERVICE_TAX=>'Remittance Reversal Service Tax',  
                            TXNTYPE_RAT_CORP_CORPORATE_LOAD => 'Ratnakar Corporate CardLoad',
                            TXNTYPE_RAT_CORP_MEDIASSIST_LOAD => 'Ratnakar Medi-Assist CardLoad',
                            TXNTYPE_REVERSAL_RAT_CORP_CORPORATE_LOAD => 'Reversal Ratnakar Corporate CardLoad',
                            TXNTYPE_REVERSAL_RAT_CORP_MEDIASSIST_LOAD => 'Reversal Ratnakar Medi-Assist CardLoad',
                            TXNTYPE_RAT_CORP_AUTH_TXN_PROCESSING => 'Ratnakar Corporate Authentication & Transaction Processing',
                            TXNTYPE_CORP_AUTH_TXN_PROCESSING => 'Corporate Authentication & Transaction Processing',
                            TXNTYPE_REVERSAL_RAT_CORP_AUTH_TXN_PROCESSING => 'Reversal Ratnakar Corporate Authentication & Transaction Processing',
                            TXNTYPE_REVERSAL_CORP_AUTH_TXN_PROCESSING => 'Reversal Corporate Authentication & Transaction Processing',
                            TXNTYPE_CREDIT_MANUAL_ADJUSTMENT => 'Credit Manual Adjustment',
                            TXNTYPE_DEBIT_MANUAL_ADJUSTMENT => 'Debit Manual Adjustment',
                            TXNTYPE_AGENT_FUND_LOAD => 'Fund Load',
                            TXNTYPE_AGENT_TOAGENT_FUND_TRANSFER => 'Agent to Agent Fund Transfer',
                            TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL => 'Agent to Agent Fund Reversal',
                            TXNTYPE_BOI_CORP_CORPORATE_LOAD => 'Card Load',
                            TXNTYPE_KOTAK_CORP_CORPORATE_LOAD => 'Card Load',
                            TXNTYPE_CARD_DEBIT => 'Card Debit',
                            TXNTYPE_AGENT_COMMISSION => 'Agent Commission',
                            TXNTYPE_AGENT_COMMISSION_REVERSAL => 'Agent Commission Reversal',
                            TXNTYPE_WALLET_TOWALLET_FUND_TRANSFER => 'Wallet to Wallet Fund Transfer'
                            
                         );

$_DISABLED_ICON_DETAILS = array('delete'=> array('img_url'=>'/images/icons/disabled-del.png' ,
                                                 'alt'=>'Delete'  
                                                ) ,
                               'edit'=> array('img_url'=>'/images/icons/disabled-edit.png' ,
                                                 'alt'=>'Edit'  
                                             ) ,
                               'view'=> array('img_url'=>'/images/icons/disabled-view.png' ,
                                                 'alt'=>'View'  
                                             ) ,
                               'download'=> array('img_url'=>'/images/icons/disabled-download-icon.png' ,
                                                  'alt'=>'Download'  
                                                 ) ,
                               'add_response'=> array('img_url'=>'/images/icons/disabled-add-response.png' ,
                                                  'alt'=>'Add Response'  
                                                 ) ,
                               );

$_ALLOWED_CONTROLLER_ACTIONS = array(
                            'agent'=>array(
                                'ajax'  => array('get-rblregistercheck','get-rblotpvalidate','mobiledup','get-city','get-pincode','emaildup','send-download-link','arndup','resend-authcode','get-ifsc','get-bankdetails','get-remitter-registration-fee','get-cities','get-branches','get-branchadd','get-state','getbasic-ifsc','getbasic-bankdetails','get-statecity','get-agentsunderdist','get-universalbanks','get-fundtransfer','get-createbene','get-validatebene','get-remitterinfo','get-loadfee'),
                                'reports'  => array('exportagentfundrequests', 'exportcommreport','exportfeereport','exportbclisting'),
                                'corp_boi_reports'  => array('exportcustomerregistration','exportwalletbalance'),
                                'remit_ratnakar_reports' => array('exportremittancereport','exportremittancecommission'),
                                'remit_kotak_reports' => array('exportremittancereport','exportremittancecommission'),
                                'corp_ratnakar_reports'  => array('exportcustomerregistration','exportloadreport','exportwalletwisetransactionreport'),
                                'mvc_axis_reports'  => array('exportagentwiseload', 'exportcardholderactivations'),
                                'emailauthorization' => array('index'),
                                'authemailauthorization' => array('index')
                            ), 
                            'operation'=> array(
                                 'ajax'  => array('get-city','get-fee','get-product','get-productlimit','get-bankbyproduct','get-agentproduct','resend-authcode','mobiledup','emaildup','send-download-link','get-ifsc','get-bankdetails', 'arndup', 'get-pincode', 'get-programproducts', 'get-agentsdropdown','get-batchname','get-bankproducts','get-bankproductscommon','get-batchnameamul','get-batchbydate','get-pincode-list-by-state','get-batchnamensdc','get-kotakbatchname','get-purse','get-agentsunderdist','get_customerbymemberid','getkotak-batchbydate','get_customerbymemberid','get-corpprogramproducts','get-distinctbatchs', 'get-ratbatchbydate','get-programagentsdropdown','get-bankproductsprogramcommon','get-bankproduct-id', 'get-bankproductslist', 'get-distributoragentdd','get-productslistbyprogram'), 
                                 'reports'  => array('exportagentfundrequests', 'exportagentwisefundrequests', 'exportcommreport', 'exportagentwisecommreport', 'exportagentactivation', 'exportagentbalancesheet','exportagentwisefeereport','exportagenttransaction','exportcustomerregistration','exportwallettrialbalance','exportsearchagentimport','exportbeneregistration','exportsettledreport','exportunsettledreport'),
                                 'mvc_axis_reports'  => array('exportagentloadreload', 'exportagentwiseload', 'exportcardholderactivations'),
                                 'remit_reports'  => array('exportremitterregn'),
                                 'remit_boi_remitter'  => array('neftrequests'),
                                 'remit_boi_remitter'  => array('neftrequests'),
                                 'corp_ratnaker_cardload' => array('exportwalletstatus','exportloadreport'),
                                 'corp_ratnaker_cardholder' => array('exportapprovalpending','exportbatchstatus','exportkycupgradation'),
                                 'corp_kotak_cardholder' => array('exportsearchcardholders','exportbatchstatus','exportkycupgradereport','exportapprovalpending','exportcrnstatus'),
                                 'corp_kotak_cardload' => array('exportwalletstatus','exportwalletstatusgpr'),
                                 'corp_boi_customer' => array('exportcustomerregistration','exportwalletstatus','exportdeliverystatus','exportsearch'),
                                 'corp_boi_reports' => array('exportdebitmandateamount','exportwalletbalance','exportpaymentstatus','exporttpmisgenericreport'),
                                 'roles'  => array('index')
                            ),
                            'customer'=> array(
                            ) ,
                           'bank'=> array( 'ajax'  => array('get-city', 'get-pincode', 'get-pincode-list-by-state' ),
                            'corp_kotak_reports' => array('exportapplications','exportremitterregn'), 
                            ),
                            'corporate'=>array(
                                'ajax'  => array('mobiledup','get-city','get-pincode','emaildup','send-download-link','arndup','resend-authcode','get-ifsc','get-bankdetails','get-remitter-registration-fee','get-cities','get-branches','get-branchadd','get-state','getbasic-ifsc','getbasic-bankdetails','get-statecity','get-batchbydate')
                            ), 
                         );
//Not in use
/*$_UNICODE_GLOBAL = array(
  'BANK_UNICODE'  => array(
      'PRODUCT_UNICODE'    => 'UNICODE_INITIALS'
  ),
  '111'  => array(
      '222'    => '11122200'
  ),
  '333'  => array(
      '444'    => '33344400'
  ),
  '222'  => array(
      '211'    => '22221100'
  ),
);*/
$_PROGRAM_TYPE = array(
                        PROGRAM_TYPE_REMIT =>  'Remit',
                        PROGRAM_TYPE_MVC =>'MVC',
                        PROGRAM_TYPE_CORP => 'Corp'
                    );


$_BANK_UNICODE_IMPORTCRN_PATH = array(
                                    '111'=>'/mvc_axis_importcrn/adddetails/' 
                                    //'222'=>'/remitter_boi_importcrn/adddetails/'                        
                                 );
$_TABLE_ECS_CRN_FIELDS = array(
                                '0'=>'crn',
                                //'1'=>'status',
                                '1'=>'relation',
                                '2'=>'product',
                                '3'=>'promotion',
                                '4'=>'branch',
                                '5'=>'statement_plan',
                                '6'=>'transaction_plan',
                                '7'=>'embossed_line3',
                                '8'=>'embossed_line4',
                                '9'=>'other'
                              );


$_CORP_CARDHOLDER_STATUS = array(
                                    STATUS_PENDING => ucfirst(STATUS_PENDING), 
                                    STATUS_ACTIVE => ucfirst(STATUS_ACTIVE), 
                                    STATUS_INACTIVE=>ucfirst(STATUS_INACTIVE), 
                                    STATUS_ECS_PENDING=>'ECS Pending', 
                                    STATUS_ECS_FAILED=>'ECS Failed',
                                    STATUS_ACTIVATION_PENDING => ucwords(str_replace('_', ' ', STATUS_ACTIVATION_PENDING))
                                   );

$_PROGRAM_TYPE_TXT = array(
                        PROGRAM_TYPE_REMIT =>  'Remittance',
                        PROGRAM_TYPE_MVC =>'MVC',
                        PROGRAM_TYPE_CORP => 'Corporate',
                        PROGRAM_TYPE_DIGIWALLET => 'DigiWallet'
                    );

$_STATUS = array(
                    STATUS_ACTIVE=>ucfirst(STATUS_ACTIVE), 
                    STATUS_INACTIVE=>ucfirst(STATUS_INACTIVE), 
                    STATUS_PENDING=>ucfirst(STATUS_PENDING), 
                    STATUS_FAILED=>ucfirst(STATUS_FAILED),
                    STATUS_FAILURE=>ucfirst(STATUS_FAILURE),
                    STATUS_LOADED=>ucfirst(STATUS_LOADED),
                    STATUS_CUTOFF=> 'Cut-Off',
                    STATUS_COMPLETED=> ucfirst(STATUS_PROCESSED),
                    STATUS_REJECTED=> ucfirst(STATUS_REJECTED),
                    STATUS_REVERSED=> ucfirst(STATUS_REVERSED),
                    STATUS_ECS_PENDING=>'ECS Pending', 
                    STATUS_ECS_FAILED=>'ECS Failed',
                    STATUS_SUCCESS => 'Successful',
      		    STATUS_HOLD => 'Hold',
                    STATUS_ACTIVATION_PENDING => 'Activation Pending',
                    STATUS_IN_PROCESS => 'In Process',
                    STATUS_REFUND => ucfirst(STATUS_REFUND),
                    STATUS_INCOMPLETE => ucfirst(STATUS_INCOMPLETE),
                    STATUS_PROCESSED => 'In Process'
                   );

/*
 * 
 */

$REMIT_STATUS = array(
                    STATUS_ACTIVE=>ucfirst(STATUS_ACTIVE), 
                    STATUS_INACTIVE=>ucfirst(STATUS_INACTIVE), 
                    STATUS_PENDING=>ucfirst(STATUS_PENDING), 
                    STATUS_FAILED=>ucfirst(STATUS_FAILED),
                    STATUS_FAILURE=>ucfirst(STATUS_FAILURE),
                    STATUS_LOADED=>ucfirst(STATUS_LOADED),
                    STATUS_CUTOFF=> 'Cut-Off',
                    STATUS_COMPLETED=> ucfirst(STATUS_PROCESSED),
                    STATUS_REJECTED=> ucfirst(STATUS_REJECTED),
                    STATUS_REVERSED=> ucfirst(STATUS_REVERSED),
                    STATUS_ECS_PENDING=>'ECS Pending', 
                    STATUS_ECS_FAILED=>'ECS Failed',
                    STATUS_SUCCESS => 'Successful',
		    STATUS_HOLD => 'Hold',
                    STATUS_ACTIVATION_PENDING => 'Activation Pending',
                    STATUS_IN_PROCESS => 'In Process',
                    STATUS_REFUND => ucfirst(STATUS_REFUND),
                    STATUS_INCOMPLETE => ucfirst(STATUS_INCOMPLETE),
                    STATUS_PROCESSED => 'Processed',
                    STATUS_DEBITED => ucfirst(STATUS_DEBITED)
                   );

$_MVC_ALLOWED_BIN = array(
    '419953'
);

$_BOI_NSDC_DISBURSEMENT_BUCKETS = array(
    '1' => 'Account Match - AadhaarMatch',
    '2' => 'Aadhaar Match - Account Not Matched',
    '3' => 'Account Match - Aadhaar Not Matched',
    '4' => 'No Match',
    '5' => 'Return',
    '9' => 'Debit Mandate amount is more than Credit',

);
//Zend_Registry::set("STEPS",$_STEPS);
Zend_Registry::set("SHMART_REWARDS", $_SHMART_REWARDS);
Zend_Registry::set("CARDHOLDER_OFFERS_FIELDS", $_CARDHOLDER_OFFERS_FIELDS);
Zend_Registry::set("TXN_ERROR_MESSAGES", $_TXN_ERROR_MESSAGES);
Zend_Registry::set("TXN_TYPE_LABELS", $_TXN_TYPE_LABELS);
Zend_Registry::set("DISABLED_ICON_DETAILS", $_DISABLED_ICON_DETAILS);
Zend_Registry::set("ALLOWED_CONTROLLER_ACTION", $_ALLOWED_CONTROLLER_ACTIONS);
//Zend_Registry::set("UNICODE_GLOBAL", $_UNICODE_GLOBAL);
Zend_Registry::set("PROGRAM_TYPE", $_PROGRAM_TYPE);
Zend_Registry::set("BANK_UNICODE_IMPORTCRN_PATH", $_BANK_UNICODE_IMPORTCRN_PATH);
Zend_Registry::set("TABLE_ECS_CRN_FIELDS", $_TABLE_ECS_CRN_FIELDS);
Zend_Registry::set("CORP_CARDHOLDER_STATUS", $_CORP_CARDHOLDER_STATUS);
Zend_Registry::set("PROGRAM_TYPE_TXT", $_PROGRAM_TYPE_TXT);
Zend_Registry::set("STATUS", $_STATUS);
Zend_Registry::set("REMIT_STATUS", $REMIT_STATUS);
Zend_Registry::set("MVC_ALLOWED_BIN", $_MVC_ALLOWED_BIN);
Zend_Registry::set("BOI_NSDC_DISBURSEMENT_BUCKETS", $_BOI_NSDC_DISBURSEMENT_BUCKETS);

