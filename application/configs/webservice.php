<?php
//socket_create(AF_INET, SOCK_STREAM, SOL_TCP)
/**
 * Webservices configuration
 * To define the webservice configuration
 *
 * Posibile values for active are:
 * - development
 * - staging
 * - production
 * 
 */

if(!class_exists("App_Api_MVC_Consts")) {
    require_once 'App/Api/MVC/Consts.php';
}

$_WEBSERVICE = array(

'sms_auth' => array(

    'development' => array(
        'gateway_url' => 'http://api.myvaluefirst.com/psms/servlet/psms.Eservice2',
        //'auth_user' => 'demoxmscr',
        //'auth_pass' => 'demcscre',
        'auth_user' => 'transervxml',
        'auth_pass' => 'prwser2723',
        'tran_testmode' => 1,
    ),

    'production' => array(
        'gateway_url' => 'http://api.myvaluefirst.com/psms/servlet/psms.Eservice2',
        'auth_user' => 'transervxml',
        'auth_pass' => 'prwser2723',
        'tran_testmode' => 0,
    ),
),
    
    
'ecs_auth' => array(

    'development' => array(
        'gateway_url' => null,
        //'location' => 'http://14.140.42.101:8991/WSDL',        
        //'location' => 'https://shmartpay.electra-pay.com/WSDL',        
        'location' => 'https://shmartpay.electra24x7.com:8383/WSDL',        
        'uri'      => 'http://webservice.epms.com/',
        'auth_user' => 'abc',
        'auth_pass' => 'xyz',
        'auth_channel' => 'API',
        'auth_key' => 'AOIJANI1234567ASDF90C0M9UAC809UACW38',
        'auth_login' => 'callWebServiceLogin',
        'auth_echo' => 'callEchoTest',
        'auth_validate_method' => 'validateSession',        
        'auth_custromer_registration_method' => 'callCustomerRegistration',                
        'auth_custromer_first_load' => 'CardLoad',                
        'auth_custromer_reload' => 'CardReLoad',                
        'balance_inquiry' => 'callBalanceInquiry',                
        'transaction_history' => 'callTransactionHistory',                
        'stop_card' => 'callStopCard',                
        'unblock_card' => 'callUnblockCard',                
    ),

    'production' => array(
        'gateway_url' => null,
        'location' => 'https://shmartpay.electra-pay.com/WSDL',        
        'uri'      => 'http://webservice.epms.com/',
        'auth_user' => 'abc',
        'auth_pass' => 'xyz',
        'auth_channel' => 'API',
        'auth_key' => 'AOIJANI1234567ASDF90C0M9UAC809UACW38',
        'auth_login' => 'callWebServiceLogin',
        'auth_echo' => 'callEchoTest',
        'auth_validate_method' => 'validateSession',        
        'auth_custromer_registration_method' => 'callCustomerRegistration',                
        'auth_custromer_first_load' => 'CardLoad',                
        'auth_custromer_reload' => 'CardReLoad',                
        'balance_inquiry' => 'callBalanceInquiry',   
        'stop_card' => 'callStopCard',   
        'unblock_card' => 'callUnblockCard',                        
    ),
),    
    
'mvc_auth' => array(

    'development' => array(
        'gateway_url' => 'http://196.37.195.93:7001',
        //'gateway_url' => 'http://abl.mvcservices.co.in:7001',
        //'gateway_url' => 'http://api.shmart.local/index/mvc?wsdl',
        'uri' => 'http://www.axiswebservice.net1.com/',
        'auth_user' => 'VCPaySOAP',
        'auth_pass' => 'axs@Tbp9gZ',
        'auth_channel' => 'API',
        'auth_key' => 'AOIJANI1234567ASDF90C0M9UAC809UACW38',
        'auth_method' => 'Logon',
        'auth_validate_method' => 'KeepAlive',        
        'send_download_link' => 'DownloadLinkRequest',        
        'auth_custromer_registration_method' => 'RegistrationRequest',                
        'balance_enquiry' => 'BalanceEnquiry',                
        'account_info' => 'AccountInformationRequest',                
        'mvc_query' => 'MVCQueryRequest',                
        'transaction_enquiry' => 'QueryTransactionsEnquiry',                
        'resend_activation_code' => 'ResendActivationCodeRequest',                
        'update_mobile_number' => 'UpdateMobileNumberRequest',                
        'block_account' => 'BlockAccountRequest',                
        'unblock_account' => 'UnBlockAccountRequest',                
        'close_account' => 'CloseAccountRequest',                
        'transaction_history' => 'TransactionHistoryEnquiry',                
        //'customer_authentication' => 'AuthenticationRequest',                
        'customer_authentication' => App_Api_MVC_Consts::API_CUSTOMERAUTH,                
    ),

    'production' => array(
         //'gateway_url' => 'http://196.37.195.93:7001',
        'gateway_url' => 'http://196.37.195.93:7001',
        //'gateway_url' => 'http://api.shmart.local/index/mvc?wsdl',
        'uri' => 'http://www.axiswebservice.net1.com/',
        'auth_user' => 'vikram',
        'auth_pass' => 'singh',
        'auth_channel' => 'API',
        'auth_key' => 'AOIJANI1234567ASDF90C0M9UAC809UACW38',
        'auth_method' => 'Logon',
        'auth_validate_method' => 'KeepAlive',        
        'send_download_link' => 'DownloadLinkRequest',        
        'auth_custromer_registration_method' => 'RegistrationRequest',                
        'balance_enquiry' => 'BalanceEnquiry',                
        'account_info' => 'AccountInformationRequest',                
        'mvc_query' => 'MVCQueryRequest',                
        'transaction_enquiry' => 'QueryTransactionsEnquiry',                
        'resend_activation_code' => 'ResendActivationCodeRequest',                
        'update_mobile_number' => 'UpdateMobileNumberRequest',                
        'block_account' => 'BlockAccountRequest',                
        'unblock_account' => 'UnBlockAccountRequest',                
        'close_account' => 'CloseAccountRequest',                
        'transaction_history' => 'TransactionHistoryEnquiry',                
        //'customer_authentication' => 'AuthenticationRequest',                
        'customer_authentication' => App_Api_MVC_Consts::API_CUSTOMERAUTH,      
    ),
),    
    
'mediassist_auth' => array(
    'development' => array(
        'gateway_url' => 'http://196.37.195.93:7001',
        'uri' => 'http://www.axiswebservice.net1.com/',
        'auth_user' => 'VCPaySOAP',
        'auth_pass' => 'p_blue34',
        'auth_channel' => 'API',
        'auth_key' => 'AOIJANI1234567ASDF90C0M9UAC809UACW38',
    ),
),    
    
'ecs_iso' => array(

    'development' => array(
        'ip'    => '10.10.8.193',
        'port'  => '8045'
        //'ip'    => '192.168.2.181',//For Testing -- Delete this code on live server
        //'port'  => '11112'//'12000'
        //'ip'    => '180.179.200.244',//For Testing -- Delete this code on live server
        //'ip'    => '10.0.7.14',//For Testing -- Delete this code on live server
        //'port'  => '8041'//'12000'
    ),
    'production' => array(
        'ip'    => '10.10.8.193',
        'port'  => '8045'
    ),
),        
    
'mvc_iso' => array(

    'development' => array(
        'ip'    => '192.168.2.181',
        'port'  => '11112'
        //'ip'    => '196.37.195.93',
        //'port'  => '7002'
        
    ),
    'production' => array(
        'ip'    => '196.37.195.93',
        'port'  => '7002'
    ),
),          
    
'shmart_iso' => array(

    'development' => array(
        'ip'    => '10.0.7.14',
        'port'  => '8041'
/*
        'ip'    => '192.168.2.189',
        'port'  => '1234',
        'allowed_ip' => array('192.168.2.189','192.168.2.168')
 * 
 */
    ),
    'production' => array(
        'ip'    => '10.0.7.14',
        'port'  => '8041'
    ),
),          


'happay_auth' => array(

    'development' => array(
        'gateway_url' => 'http://54.83.19.73/transaction/v1/transaction/push/s',
        'uri' => 'http://54.83.19.73/transaction/v1/transaction/push/',
        'auth_user' => 'demo',
        'auth_pass' => 'demo',
    ),

    'production' => array(
        'gateway_url' => 'http://54.83.19.73/transaction/v1/transaction/push/',
        'uri' => 'http://54.83.19.73/transaction/v1/transaction/push/',
        'auth_user' => 'demo',
        'auth_pass' => 'demo',
    ),
),    
    
);

Zend_Registry::set("WEBSERVICE",$_WEBSERVICE);


