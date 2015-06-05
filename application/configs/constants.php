<?php
/**
 * @package application_config
 * @copyright company
 */

/*  Common Configuration Starts */ 
/* Which is used accross the all program and banks */ 

define('TXNTYPE_FIRST_LOAD', 'CDLD');
define('TXNTYPE_CARD_RELOAD', 'CDRL');
define('TXNTYPE_CARDHOLDER_REGISTRATION', 'CDRG');
define('TXNTYPE_AGENT_FUND_LOAD', 'AGFL');
define('TXNTYPE_CORPORATE_FUND_LOAD', 'CGFL');
define('NON_KYC_MAX_TXN_AMOUNT_LIMIT', 5000);

/*  General  Global Configuration Ends */ 

# TXN TYPE Remittance Program
define('TXNTYPE_REMITTER_REGISTRATION', 'RMRG');
define('TXNTYPE_BENEFICIARY_REGISTRATION', 'BNRG');
define('TXNTYPE_REMITTANCE', 'REMT');
define('TXNTYPE_REMITTANCE_FEE', 'RMFE');
define('TXNTYPE_REMITTANCE_REFUND', 'RMRF');
define('TXNTYPE_REMITTANCE_REFUND_FEE', 'RRFE');
define('TXNTYPE_REMITTANCE_SERVICE_TAX', 'RMST');
define('TXNTYPE_REMITTANCE_REVERSAL_REFUND_FEE', 'RVRF');
define('TXNTYPE_REMITTANCE_REVERSAL_SERVICE_TAX', 'RVST');
define('TXNTYPE_REMITTANCE_SUCCESS_TO_FAILURE', 'RMSF');

/* Ratnakar Corporate txn type */
define('TXNTYPE_RAT_CORP_CORPORATE_LOAD', 'RCCL');
define('TXNTYPE_RAT_CORP_MEDIASSIST_LOAD', 'RCML');
define('TXNTYPE_REVERSAL_RAT_CORP_CORPORATE_LOAD', 'RRCC');
define('TXNTYPE_REVERSAL_RAT_CORP_MEDIASSIST_LOAD', 'RRCM');
define('TXNTYPE_RAT_CORP_AUTH_TXN_PROCESSING', 'RCTP');
define('TXNTYPE_REVERSAL_RAT_CORP_AUTH_TXN_PROCESSING', 'RRCT');
define('TXNTYPE_CREDIT_MANUAL_ADJUSTMENT', 'CRMA');
define('TXNTYPE_DEBIT_MANUAL_ADJUSTMENT', 'DRMA');
define('TXNTYPE_RAT_CORP_MEDIASSIST_DEBIT', 'RCMD');

/* Agent commission txn types */
define('TXNTYPE_AGENT_COMMISSION', 'COMM');
define('TXNTYPE_AGENT_COMMISSION_REVERSAL', 'RCOM');


define('TXNTYPE_RAT_CORP_PAYTRONICS_LOAD', 'RCPL');
define('TXNTYPE_REVERSAL_RAT_CORP_PAYTRONICS_LOAD', 'RRCP');


define('TXNTYPE_CORP_AUTH_TXN_PROCESSING', 'CATP');
define('TXNTYPE_REVERSAL_CORP_AUTH_TXN_PROCESSING', 'RATP');
define('TXNTYPE_REVERSAL_LOAD', 'RVLD');
define('TXNTYPE_CARD_DEBIT', 'CDDR');

/* Agent to agent fund transfer */
define('TXNTYPE_AGENT_TOAGENT_FUND_TRANSFER', 'AAFT');
define('TXNTYPE_AGENT_TOAGENT_FUND_REVERSAL', 'RAFT');

define('TXNTYPE_KOTAK_CORP_CORPORATE_LOAD', 'KCCL');
define('TXNTYPE_KOTAK_CORP_GPR_LOAD', 'RCGL');
define('TXNTYPE_BOI_CORP_CORPORATE_LOAD', 'BCCL');
define('TXNTYPE_REVERSAL_BOI_CORP_CORPORATE_LOAD', 'BRCC');
define('TXNTYPE_BOI_CORP_AUTH_TXN_PROCESSING', 'BCTP');
define('TXNTYPE_REVERSAL_BOI_CORP_AUTH_TXN_PROCESSING', 'BRCT');


define('TXN_OPS_ID', '1');
define('FEE_AC_ID', '2');
define('SERVICE_TAX_AC_ID', '3');
define('SUSPENSE_AC_ID', '4');
define('CUSTOMER_MEDIASSIST_EXPENSE_ID', '5');
define('CUSTOMER_BOI_EXPENSE_ID', '6');
define('DEFAULT_PAYABLE_ID', '20'); // don't use 21-60 as of now for ops constant ids

define('TXN_APPROVE_STATUS', 'approve');
define('TXN_DECLINE_STATUS', 'decline');
define('CARDHOLDER_ACTIVE_STATUS', 'active');
define('AGENT_SECTION_SETTING_ID', 1);
define('SETTINGS_SECTION_ID_API', 2);
define('SETTINGS_SECTION_ID_PROGRAM_TYPE', 3);


define('FLAG_SUCCESS', 'success');
define('FLAG_FAILURE', 'failure');
define('FLAG_PENDING', 'pending');
define('FLAG_DECLINE', 'decline');
define('FLAG_YES', 'yes');
define('FLAG_NO', 'no');
define('FLAG_Y', 'Y');
define('FLAG_N', 'N');

define('UNBLOCKED_STATUS', 'unblocked');
define('ENROLL_APPROVED_STATUS', 'approved');
define('EMAIL_VERIFIED_STATUS', 'verified');
define('AGENT_ACTIVE_STATUS', 'active');
define('AGENT_SETTING_MIN_TYPE', 'min');
define('AGENT_SETTING_MAX_TYPE', 'max');
define('AGENT_PROFILE_PHOTO_PREFIX', 'profile_');
define('AGENT_DEFAULT_PROFILE_PHOTO', 'agnt-icon-big.gif');

define('SETTING_API_ECS','ecs_api');
define('SETTING_API_MVC','mvc_api');
define('SETTING_API_ISO','ecs_iso');
define('SETTING_ISO_ECS','ecs_iso');
define('SETTING_AGENT_MAX_BALANCE','max_balance');
define('SETTING_AGENT_MIN_BALANCE','min_balance');
define('SETTING_PROGRAM_TYPE','program_type');

define('SETTING_API_ERROR_MSG','Please try after some time');


define('TXN_MODE_DR', 'dr');
define('TXN_MODE_CR', 'cr');

define('CURRENCY_INR', 'INR');
define('CURRENCY_RUPEES', 'Rs.');

define('PRODUCT_LIMIT_CODE_PREFIX', 'PLC');
define('PRODUCT_LIMIT_CODE_SUFFIX_LENGTH', 5);

define('PRODUCT_CODE_PREFIX', '100');
define('PRODUCT_CODE_SUFFIX_LENGTH', 5);

define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_DELETED', 'deleted');
define('STATUS_PENDING', 'pending');

define('STATUS_NEW', 'new');
define('STATUS_UNSETTLED', 'unsettled');
define('STATUS_SETTLED', 'settled');

define('BY_SYSTEM', 'system');
define('BY_OPS', 'ops');
define('BY_BANK', 'bank');
define('BY_MAKER', 'maker');
define('BY_CHECKER', 'checker');
define('BY_ECS', 'ecs');
define('BY_AUTHORIZER', 'authorizer');
define('BY_API', 'api');
define('BY_AGENT', 'agent');
define('BY_CORPORATE', 'corporate');

define('STATUS_COMPLETE', 'complete');
define('STATUS_INCOMPLETE', 'incomplete');
define('STATUS_CLOSED', 'closed');
define('STATUS_BLOCKED', 'blocked');
define('STATUS_UNBLOCKED', 'unblocked');
define('STATUS_ACTIVATED', 'activated');
define('STATUS_REJECTED', 'rejected');
define('STATUS_LOCKED', 'locked');
define('STATUS_VERIFIED', 'verified');
define('STATUS_APPROVED', 'approved');
define('STATUS_SUCCESS', 'success');
define('STATUS_APPROVE', 'approve');
define('STATUS_FAILED', 'failed');
define('STATUS_IN_PROCESS', 'in_process');
define('STATUS_REFUND', 'refund');
define('STATUS_PROCESSED', 'processed');
define('STATUS_FAILURE', 'failure');
define('STATUS_NA', 'na');
define('STATUS_TEMP', 'temp');
define('STATUS_PASS', 'pass');
define('STATUS_DUPLICATE', 'duplicate');
define('STATUS_HOLD', 'hold');
define('STATUS_WAITING', 'waiting');
define('STATUS_CARD_ISSUED', 'card_issued');
define('STATUS_MAPPED', 'mapped');
define('STATUS_DEBITED', 'debited');
define('STATUS_PROCESS', 'process');
define('STATUS_REJECT', 'reject');

define('STATUS_PENDING_REG', 'pending_reg');
define('STATUS_LOADED', 'loaded');
define('STATUS_ECS_PENDING', 'ecs_pending');
define('STATUS_ECS_FAILED', 'ecs_failed');
define('STATUS_CUTOFF', 'cutoff');
define('STATUS_REVERSED', 'reversed');
define('STATUS_DELIVERED', 'delivered');
define('STATUS_UNDELIVERED', 'undelivered');
define('STATUS_ACTIVATION_PENDING', 'activation_pending');
define('STATUS_PRE_ACTIVATED', 'preactivated');
define('STATUS_REVERTED', 'reverted');
define('STATUS_CLAIMED', 'claimed');
define('STATUS_RELEASED', 'released');

/**I-PAY STATUS**/
define('STATUS_IPAY_ACCEPT', 'Accept');
define('STATUS_IPAY_REJECT', 'Reject');

/*** Cron Names ***/
define('CRON_AGENT_LOW_BALANCE_ALERT_ID', 1);
define('CRON_UPDATE_AGENT_CLOSING_BALANCE_ID', 2);
define('CRON_COMMISSION_REPORT_ID', 3);
define('CRON_ECS_ISO_VALIDATOR_ID', 4);
define('CRON_PASSWORD_REQUEST_INACTIVE_ID', 6);
define('CRON_LOW_CRN_ALERT_ID', 7);
define('CRON_ECS_SOAP_VALIDATOR_ID', 8);
define('CRON_MVC_REGISTRATION_ID', 9);
define('CRON_REMOVE_INCOMPLETE_AGENTS_CARDHOLDERS', 10); 
define('CRON_MVC_SESSION_VALIDATOR_ID', 11);
define('CRON_GENERATE_REMITTER_NEFT_REQUEST_ID', 12);
define('CRON_NEFT_RESPONSE_SEND_SMS_ID', 13);
define('CRON_NEFT_BATCH_CREATION_ID', 14);
define('CRON_RAT_CORP_ECS_REGN', 15);
define('CRON_RAT_CORPORATE_LOAD', 16);
define('CRON_PURSE_CUT_OFF_VALIDATION', 17);
define('CRON_AGENT_FUNDING_CHECK_DUPLICATE_BANK_STATEMENT', 18);
define('CRON_TRANSACTION_INFORMATION', 19);
define('CRON_RAT_MANUAL_ADJUSTMENT', 20);
define('CRON_KTK_CRN_UPDATE', 21);
define('CRON_KOTAK_CORP_ECS_REGN', 22);
define('CRON_KOTAK_CORPORATE_LOAD', 23);
define('CRON_BOI_CORP_ECS_REGN', 24);
define('CRON_BOI_CORPORATE_LOAD', 25);
define('CRON_BOI_ACCOUNT_ACTIVATION', 26);
define('CRON_BOI_ACCOUNT_MAPPING', 27);
define('CRON_BOI_ACCOUNT_LOAD', 28);
define('CRON_BOI_OUTPUT_FILE', 29);
define('CRON_BOI_UPDATE_CUSTOMER_PURSE_CLOSING_BALANCE', 30);
define('CRON_BOI_DISB_FILE_GENRATOR', 31);
define('CRON_HAPPAY_TRANSACTION_INITIMATION', 32);
define('CRON_RAT_UPDATE_CUSTOMER_PURSE_CLOSING_BALANCE', 33);
define('CRON_KOTAK_UPDATE_CUSTOMER_PURSE_CLOSING_BALANCE', 34);
define('CRON_RAT_PURSE_CUT_OFF_VALIDATION', 35);
define('CRON_BOI_PURSE_CUT_OFF_VALIDATION', 36);
define('CRON_BOI_NSDC_GENERATE_TP_MIS', 37);
define('CRON_KOTAK_MANUAL_ADJUSTMENT', 38);
define('CRON_CUSTOM_SMS', 39);
define('CRON_REMIT_KOTAK_FAILURE_RECON', 40);
define('CRON_RAT_GPR_CRN_UPDATE', 41);
define('CRON_RAT_NEFT_BATCH_CREATION_ID', 42);
define('CRON_RAT_GENERATE_REMITTER_NEFT_REQUEST_ID', 43);
define('CRON_RAT_NEFT_RESPONSE_SEND_SMS_ID', 44);
define('CRON_RAT_UTR_MAPPING_ID', 45);
define('CRON_RAT_NEFT_RESPONSE_MAPPING_ID', 46);
define('CRON_AML_ID', 47);
define('INSURANCE_CLAIM_AMOUNT_ALLOWED_TIME', '120'); // in mins
define('CRON_RAT_API_SETTLEMENT', 48);
define('CRON_REMITTANCE_TRANSACTION_FILE', 51);
define('CRON_AGENT_FUNDING_IPAY', 52);
define('CRON_RAT_SETTLEMENT_BATCH_CREATION_ID', 53);
define('CRON_RAT_SETTLEMENT_RESPONSE_ID', 54);
define('CRON_PARTNER_FUNDING', 80);
define('CRON_REMITTANCE_TRANSACTION_RECON', 60);

define('CRON_UPDATE_AGENT_VIRTUAL_CLOSING_BALANCE_ID', 81);

define('CRON_GENERATE_AGENT_VIRTUAL_BALANCE_SHEET_ID', 85);

define('CRON_REVERT_INCOMPLETE_WALLET_TRANSFER', 90);
define('CRON_RATNAKAR_REMITTANCE_NOTIFICATION', 91);

define('CRON_BENEFICIARY_INC_EXCEED', 86);
define('CRON_BENEFICIARY_REMITTER_EXCEED', 87);
  
define('CRON_BLOCK_AMOUNT_RELEASE_ID', 92);


define('STATUS_COMPLETED', 'completed');
define('STATUS_STARTED', 'started');
define('STATUS_STOPPED', 'stopped');
define('STATUS_FREE', 'free');
define('STATUS_APPLIED', 'applied');
define('STATUS_ALREADY', 'already');

define('GREATER', 'greater');

/*** Cron Names Ends ***/

/**
 * API
 * DO NOT CHANGE
 */
define('TP_MVC_ID', '1');
define('TP_ECS_ID', '2');
define('TP_ECS_API_ID', '3');
define('TP_KOTAK_ID', '4');
define('TP_RATNAKAR_ID', '5');
define('TP_SWITCH_ID', '6');
define('TP_MEDIASSIST_ID', '7');
define('TP_CUST_ID', '8');
define('TP_PAYTRONIC_ID', '9');
define('TP_ECOM_ID', '10');
define('TP_COPASS_ID', '15');
define('TP_HAPPAY_ID', '16');
define('TP_OXIGEN_ID', '17');
define('TP_RAT_GPR_ID', '18');
define('TP_KTK_GPR_ID', '19');
define('TP_RAT_CNY_ID', '20');
define('TP_PAYU_GPR_ID', '21');
define('TP_SHOPCLUES_GPR_ID', '24');
define('TP_FORBES_GPR_ID', '22');
define('TP_SIMULATOR_ID', '777');
define('SOAP_SERVER_TP_ID', '999');
define('TP_CEQUITY_GPR_ID', '25');
define('TP_HFCI_GPR_ID', '26');
define('TP_BMS_GPR_ID', '27');
define('TP_SMP_GPR_ID', '28');
define('TP_RBLMVC_ID', '35');
define('TP_EPRS_GPR_ID', '40');

define('TP_PEW_GPR_ID', '31');

define('TP_IPAY_ID', '29');
define('TP_IPAY_CLIENT_CODE', 'TRANSICICI');

//define('TP_PEW_GPR_ID', '29');
define('TP_TFS_GPR_ID', '33');

define('ECS_ISO_LOGON', 'LOGON');
define('ECS_ISO_ECHO', 'ECHO');
define('ECS_ISO_CARDLOAD', 'CARDLOAD');
define('ECS_ISO_CARDLOAD_REVERSAL', 'CARDLOADREVERSAL');

define('ECS_ISO_CARDDEBIT', 'CARDDEBIT');
define('ECS_ISO_CARDDEBIT_REVERSAL', 'CARDDEBITREVERSAL');

define('ECS_ISO_CARDRELOAD', 'CARDRELOAD');

define('ECS_API_USERID', 'transerv');
define('ECS_API_CHANNEL', 'IVR');

define('CURRENCY_INR_CODE', '356');
define('COUNTRY_IN_CODE', '356');
define('COUNTRY_CODE_INDIA', 'IN');

define('MAX_FLOAT_LIMIT', 9999999.99);
define('MAX_FLOAT_LIMIT_PCT', 99999.99);


define('FUNCTIONALITY_UPDATE_CARDHOLDER','Update Cardholder');
define('USER_TYPE_CORPORATE','corporate');
define('USER_TYPE_AGENT','agent');
define('USER_TYPE_OPERATION','operation');
define('USER_TYPE_OPS','ops');


/** API **/


/**
 * DO NOT CHANGE
 */
define('AGENT_ADMIN', 'administrators');
define('TYPE_ADMIN', 'administrators');
/** API **/


define('API_LOGON_SUCCESS', 'success');

define('GROUP_ID_ADMINISTRATOR', 1);
define('GROUP_ID_GUEST', 2);
define('CALL_CENTRE_NUMBER','(022) 6130 7070');
define('AGENT_AUTH_CALL_CENTRE_NUMBER','(022) 67304948');
define('CUSTOMER_SUPPORT_EMAIL','care@shmart.in');

define('AXIS_BANK_SHMART_PAY','Axis Bank Shmart!Pay');
define('AXIS_BANK_SHMART_CARD','Axis Bank Shmart!Pay Card');
define('AXIS_BANK_SHMART_ACCOUNT','Axis Bank Shmart!Pay Account');

define('BOI_REMITTANCE','Bank of India Remittance');
define('BOI_SHMART_REMIT','Bank of India Shmart! Remit');
define('BOI_REMITTANCE_EMAIL','boi.remittance@shmart.in');
define('BOI_SHMART_EMAIL','boi@shmart.in');
define('BOI_CALL_CENTRE_NUMBER','022 6730 4948');
define('BOI_SMS_TEXT','BOI SB A/c');
define('BOI_SMS_TEXT_END','Debit Card and Passbook');

define('NUMBER_OF_MONTHS',3);
define('BOI_SHMART_TRANSFER','BOI Shmart Transfer');

define('RATNAKAR_BANK_CORP_CARD','Ratnakar Bank Hospital Insurance Card');
define('RATNAKAR_BANK','RATNAKAR BANK');
define('RATNAKAR_PRODUCT_CODES','3,8,9,11,15,14,17,18,24');

define('NEFT_RMKS_SUCCESS', "NEFT Transaction Successful");
define('NEFT_RMKS_FAILURE', "NEFT Transaction Failed");

define('REMITTER_PROFILE_PHOTO_PREFIX', 'profile_');
define('REMITTER_DEFAULT_PROFILE_PHOTO', 'agnt-icon-big.gif');
define('REMIT_FUND_HOLDER_OPS', 'ops');
define('REMIT_FUND_HOLDER_REMITTER', 'remitter');
define('REMIT_BATCH_NAME_PREFIX', 'TSV');

define('REMIT_FUND_HOLDER_NEFT', 'neft');
define('REMIT_FUND_HOLDER_BENEFICIARY', 'beneficiary');

define('TXN_NEFT', 'NEFT');
define('TXN_IMPS', 'IMPS');
define('CORPORATE_ID_TSV', 'TRANSERVPL818');
define('PREFFERED_COMM_CHANNEL', 'M'); // M = Mobile, E = Email
define('TXN_REPORT_CODE', '90909'); 

define('BOI_REMITTANCE_MIN_AMOUNT_PER_TXN', 10);
define('BOI_REMITTANCE_TXN_LIMIT_PER_BATCHFILE', 50);
define('MEDIASSIST_CUSTOMER_LOAD_LIMIT', 50);
define('RAT_CORPORATE_LOAD_LIMIT', 100);
define('RAT_MANUAL_ADJUSTMENT_LIMIT', 100);
define('KOTAK_MANUAL_ADJUSTMENT_LIMIT', 100);

define('KOTAK_CRN_UPDATE_LIMIT', 100);
define('KOTAK_CORPORATE_LOAD_LIMIT', 500);
define('KOTAK_CORP_LOAD_LIMIT', 200);

define('BOI_CRN_UPDATE_LIMIT', 100);
define('BOI_CORPORATE_LOAD_LIMIT', 100);
define('BOI_ACC_ACT', 1000);
define('BOI_MAP', 500);

define('MIN_CUSTOMER_BALANCE', 0);

define('PROGRAM_TYPE_MVC','Mvc');
define('PROGRAM_TYPE_REMIT','Remit');
define('PROGRAM_TYPE_CORP','Corp');
define('PROGRAM_TYPE_DIGIWALLET', 'DigiWallet');

define('CUSTOMER_MVC_TYPE_MVCC','mvcc');
define('CUSTOMER_MVC_TYPE_MVCI','mvci');

//DO NOT CHANGE - THIS WILL IMAPCT ON ALL CRN GENERATION PROCESS
define('UNICODE_INITIAL_FIXED_LENGTH',8);

//USED in t_unicode
define('STATUS_USED', 'used');



define('MODULE_AGENT', 'agent');
define('MODULE_OPERATION', 'operation');
define('MODULE_CUSTOMER', 'customer');
define('MODULE_CORPORATE', 'corporate');
define('MODULE_BANK', 'bank');

//DO NOT CHANGE -  Used in Operation Import CRN
define('ECS_CRN_FILE_COLUMN_COUNT', '10');
define('SEPARATOR_PIPE', '|');
define('SEPARATOR_COMMA', ',');


define('CRON_SERVICE_SCHEDULER', '999');
define('RBL_CRON_SCHEDULER_BCAGENT', 'TRA1000315');
define('RBL_CHANNEL_PARTNER_LOGIN_PASSWORD', '6a48fd501acd7d8e095ec0ea182ea96d37b7c1e6');
define('RBL_CHANNEL_PARTNER_LOGIN_USERNAME', 'transervapi');
define('RBL_CHANNEL_CPID', '36');

// FILE formates
define('FILE_CSV', '.csv');
define('FILE_XLS', '.xls');

// Source Account Number
define('S_ACT_NO', '409000053977');


//CORP
define('CORP_CARDHOLDER_UPLOAD_DELIMITER', '|');
define('CORP_CARDHOLDER_MANDATORY_FIELD_INDEX', 11);
define('CORP_CARDHOLDER_UPLOAD_COLUMNS', 13);
define('CORP_PRODUCT', 'Medi Assist');
define('RAT_CORP_ECS_REGN_LIMIT', 50);
define('RAT_CRN_UPDATE_LIMIT', 100);
define('CORP_WALLET_UPLOAD_DELIMITER', ',');
define('CORP_WALLET_UPLOAD_COLUMNS', 8);
define('CORP_WALLET_TXN_IDENTIFIER_CN', 'cn');
define('CORP_WALLET_TXN_IDENTIFIER_MI', 'mi');
define('CORP_WALLET_TXN_IDENTIFIER_MB', 'mb');
define('CORP_WALLET_TXN_IDENTIFIER_EI', 'ei');
define('CORP_WALLET_CODE_GNRL', 'gnrl');
define('CORP_WALLET_CODE_MEDI', 'medi');
define('CORP_WALLET_CODE_NA', 'na');
define('CORP_CARD_TYPE_CORPORATE', 'c');
define('CORP_CARD_TYPE_NORMAL', 'n');
define('CORP_WALLET_MANDATORY_FIELD_INDEX', 7);
define('RAT_API_SETTLEMENT_MANDATORY_FIELD_INDEX', 2);
define('CORP_WALLET_END_OF_FILE', 'EOF');

define('CORP_ACCOUNT_MANDATORY_FIELD_INDEX', 7);
define('CORP_ACCOUNT_END_OF_FILE', 'EOF');
define('BOI_NSDC_OUTPUT_FILE_PREFIX', 'CUST_REG_NSDC_REQ_');
define('AGENT_IMPORT_FILE_UPLOAD_DELIMITER', ',');
define('PAYMENT_HISTORY_IMPORT_FILE_UPLOAD_DELIMITER', ',');
define('PAYMENT_HISTORY_RESPONSE_IMPORT_FILE_UPLOAD_DELIMITER', ',');
define('PAYMENT_HISTORY_UPLOAD_COLUMNS', 14);
define('RESPONSE_PAYMENT_HISTORY_UPLOAD_COLUMNS', 17);
define('SETTLEMENT_RESPONSE_IMPORT_FILE_UPLOAD_DELIMITER', ',');
define('SETTLEMENT_RESPONSE_UPLOAD_COLUMNS', 17);

// defining paths
define('UPLOAD_PATH', ROOT_PATH . '/uploads' );
define('UPLOAD_PATH_AGENT_PHOTO', ROOT_PATH . '/public/agent/uploads/photo' );
define('UPLOAD_PATH_REMITTER_PHOTO', ROOT_PATH . '/public/agent/uploads/photo/remitter' );
define('UPLOAD_PATH_KOTAK_REMITTER_PHOTO', ROOT_PATH . '/public/agent/uploads/photo/remitter/kotak' );
define('UPLOAD_PATH_RATNAKAR_REMITTER_PHOTO', ROOT_PATH . '/public/agent/uploads/photo/remitter/ratnakar' );
define('UPLOAD_PATH_KOTAK_AMUL_DOC', ROOT_PATH . '/public/agent/uploads/kotak/docs');
define('UPLOAD_PATH_BOI_CUST_DOC', ROOT_PATH . '/public/agent/uploads/boi/docs');
define('UPLOAD_IMPORTCRN_PATH', ROOT_PATH . '/uploads/crn' );
define('UPLOAD_REMIT_BOI_PATH', ROOT_PATH . '/uploads/remit/boi' );
define('UPLOAD_PATH_CUSTOMER_PHOTO', ROOT_PATH . '/uploads/customer/ratnakar' );
define('UPLOAD_BOI_NSDC_PATH', ROOT_PATH . '/uploads/boi/output' );
define('UPLOAD_PATH_CORPORATE_PHOTO', ROOT_PATH . '/public/corporate/uploads/photo' );
define('UPLOAD_PATH_AGENT_BALANCE_SHEET_REPORTS', ROOT_PATH . '/uploads/operation/reports/agentbalance' );
define('UPLOAD_PATH_WALLET_BALANCE_REPORTS', ROOT_PATH . '/uploads/operation/reports/walletbalance' );
define('UPLOAD_PATH_BOI_TP_MIS_REPORTS', ROOT_PATH . '/uploads/operation/reports/tpmis' );
define('UPLOAD_PATH_RAT_CORP_DOC', ROOT_PATH . '/uploads/corporate/cardholder/');
define('UPLOAD_PATH_RAT_CORPORATE_DOC', ROOT_PATH . '/uploads/corporate/');
define('UPLOAD_PATH_KOTAK_CORP_DOC', ROOT_PATH . '/uploads/corporate/kotak/cardholder/');
define('UPLOAD_PATH_REMIT_KOTAK_FAILURE_RECON_REPORTS', ROOT_PATH . '/uploads/operation/reports/failurerecon/kotak' );
define('UPLOAD_PATH_RAT_CORP_CARDHOLDER_DOC', ROOT_PATH . '/uploads/corporate/cardholder/ratnakar/');
define('UPLOAD_SAMPLE_PATH', ROOT_PATH . '/public/corporate/uploads/sample/');
define('UPLOAD_REMIT_RAT_PATH', ROOT_PATH . '/uploads/remit/ratnakar' );
define('UPLOAD_PATH_REMITTANCE_TRANSACTION_REPORTS', ROOT_PATH . '/uploads/operation/reports/remittancetransaction' );
define('UPLOAD_CUSTOMER_RATNAKAR_SETTLEMENT', ROOT_PATH . '/uploads/customer/ratnakar/settlement' );
define('UPLOAD_PATH_RAT_REMIT_TXN_RECON_REPORTS', ROOT_PATH . '/uploads/operation/reports/remitrecon/ratnakar' );
define('UPLOAD_PATH_KOTAK_REMIT_TXN_RECON_REPORTS', ROOT_PATH . '/uploads/operation/reports/remitrecon/kotak' );
define('UPLOAD_PATH_AGENT_VIRTUAL_BALANCE_SHEET_REPORT', ROOT_PATH . '/uploads/operation/reports/agentvirtualbalance' );
define('UPLOAD_PATH_BENEFICIARY_INC_EXCEED_SHEET_REPORT', ROOT_PATH . '/uploads/operation/reports/beneincexceed' );
define('UPLOAD_PATH_BENEFICIARY_REMITTER_EXCEED_REPORT', ROOT_PATH . '/uploads/operation/reports/beneremitterexceed' );

//Bank
define('BANK_ICICI', 'icici' );
define('BANK_KOTAK', 'kotak' );
define('BANK_RATNAKAR', 'ratnakar' );
define('BANK_BOI', 'boi' );
define('BANK_AXIS', 'axis' );

//Bank Product
define('BANK_BOI_REMIT', 'boi-remit' );
define('BANK_BOI_NDSC', 'boi-nsdc' );
define('BANK_RATNAKAR_CORP', 'rat-corp' );
define('BANK_AXIS_MVC', 'axis-mvc' );
define('BANK_KOTAK_REMIT', 'kotak-remit' );
define('BANK_KOTAK_AMUL', 'kotak-amul' );
define('BANK_BOI_NSDC', 'boi-nsdc' );
define('BANK_RATNAKAR_PAT', 'rat-pat' );
define('BANK_RATNAKAR_COPASS', 'rat-cop' );
define('BANK_RATNAKAR_HAPPAY', 'rat-hap' );
define('BANK_KOTAK_AMULGUJ', 'kotak-amulguj' );
define('BANK_KOTAK_SEMICLOSE_GPR', 'kotak-semiclose' );
define('BANK_KOTAK_OPENLOOP_GPR', 'kotak-openloop' );
define('BANK_RATNAKAR_CNERGYIS', 'rat-cny' );
define('BANK_RATNAKAR_GENERIC_GPR', 'rat-gpr' );
define('BANK_RATNAKAR_REMIT', 'rat-remit' );
define('BANK_RATNAKAR_SURYODAY', 'rat-sur' );
define('BANK_RATNAKAR_MVC', 'rat-mvc' );
define('BANK_RATNAKAR_PAYU', 'rat-payu' );
define('BANK_RATNAKAR_SHOPCLUES', 'rat-shop' );
define('BANK_RATNAKAR_CEQUITY', 'rat-cequity' );

define('BANK_RATNAKAR_HFCI', 'rat-hfci' );

define('BANK_RATNAKAR_BOOKMYSHOW', 'rat-bms' );
define('BANK_RATNAKAR_SMP', 'rat-smp' );
define('BANK_RATNAKAR_TFS', 'rat-tfs' );

//MediAssist Agent ID
define('MEDIASSIST_AGENT_ID', '217' );
//MVC AGENT ID
define('API_MVC_AGENT_ID', '217' );
define('API_OXIGEN_AGENT_ID', '0' );

define('MEDIASSIST_CALL_CENTRE_NUMBER','022 67304948' );
define('MEDIASSIST_SHMART_EMAIL','mediassist@shmart.in');
define('MEDIASSIST_PRODUCT','MEDI ASSIST CARD');
define('PAT_PRODUCT','PAYTRONICS CARD');
define('COP_PRODUCT','COPASS CARD');
define('HAP_PRODUCT','HAPPAY CARD');
define('CNY_PRODUCT','CNERGYIS CARD');
define('SUR_PRODUCT','SURYODAY CARD');
define('RBL_GPR_PRODUCT','RBL GPR CARD');

define('NSDC_PRODUCT','NSDC CARD');

define('KOTAK_AMUL_PRODUCT','Kotak Samriddhi Card');
define('KOTAK_GPR_PRODUCT','Kotak GPR Card');


//Paytronic
define('PAYTRONIC_AGENT_ID', '332' );
//CoPass
define('COPASS_AGENT_ID', '404' );
//CoPass
define('ECOMM_AGENT_ID', '404' );
//Happay
define('HAPPAY_AGENT_ID', '415' );
//RBL GPR
define('RBL_GPR_AGENT_ID', '415' );
//RBL GPR
define('RBL_CNY_AGENT_ID', '415' );
//kotak GPR
define('KTK_GPR_AGENT_ID', '415' );
//RBL PAYU
define('RBL_PAYU_AGENT_ID', '401' );
//KTK FORBES
define('KTK_FORBES_AGENT_ID', '449' );
//RBL SHOPCLUES
define('RBL_SHOPCLUES_AGENT_ID', '402' );

//RBL CEQUITY CORPORATE
define('RBL_CEQUITY_AGENT_ID', '47' );

//RBL HFCI AGENT
define('RBL_HFCI_AGENT_ID', '428' );

//RBL BOOK-MY-SHOW AGENT
define('RBL_BMS_AGENT_ID', '428' );

//RBL HFCI AGENT
define('RBL_SMP_AGENT_ID', '428' );

//RBL PEELWORKS AGENT
define('RBL_PEW_CORP_ID', '429' );

//RBL TAXI FOR SURE AGENT
define('RBL_TFS_AGENT_ID', '428' );
//Kotak Remittance
define('KOTAK_MAX_BENFICIARY_COUNT',10);
define('KOTAK_SHMART_TRANSFER','Kotak Bank Shmart Transfer');
define('KOTAK_REMITTANCE_MIN_AMOUNT_PER_TXN', 1);
define('KOTAK_SHMART_EMAIL','kotak.care@shmart.in');
define('KOTAK_CALL_CENTRE_NUMBER','022 67304948');

define('INSURANCE_CLAIM_CARDNUMBER_NOT_MATCH', 'Card number not matched');
define('INSURANCE_CLAIM_AMOUNT_NOT_MATCH', 'Amount not matched');
define('INSURANCE_CLAIM_TID_NOT_MATCH', 'Terminal Id not matched');
define('COMPLETE_ECS_CLAIM_INSUFFICIENT_DATA', 'card number or amount or rr no not recieved');
define('COMPLETE_ECS_CLAIM_NOT_EXISTED', 'Insurance claim not found in DB');

define('TRANSACTION_SUCCESSFUL','TRANSACTION_SUCCESSFUL');
define('TRANSACTION_FAILED','TRANSACTION_FAILED');
define('TRANSACTION_NORESPONSE','TRANSACTION_NORESPONSE');
define('TRANSACTION_CHECKSUM_FAILED','TRANSACTION_CHECKSUM_FAILED');
define('TRANSACTION_INVALID_PARAMS','TRANSACTION_INVALID_PARAMS');
define('TRANSACTION_INVALID_RESPONSE','TRANSACTION_INVALID_RESPONSE');
define('TRANSACTION_INVALID_RESPONSE_CODE','TRANSACTION_INVALID_RESPONSE_CODE');
define('TRANSACTION_TIMEOUT','TRANSACTION_TIMEOUT');
define('KOTAK_REMITTANCE_SELECT_DAYS_DATA','30');

//Agent Funding
define('BANK_STATEMENT_UPLOAD_PATH',UPLOAD_PATH ."/operation/bankstatments");
define('APPLICATION_UPLOAD_PATH',UPLOAD_PATH ."/operation/application");
define('BANK_STATEMENT_JOURNAL_NO_EXPLODE_DELIMITER','//' );
define('BANK_STATEMENT_CHEQUE_NO_EXPLODE_DELIMITER','StCon-' );
define('AGENT_FUNDING_FETCH_DATA_LIMIT',1000);


define('SEARCH_DURATION_MAX','31');

define('STATUS_CANCELLED', 'cancelled');

//FUND TRANSFER TYPE IDs
define('FUND_TRANSFER_TYPE_ID_CASH', 1);
define('FUND_TRANSFER_TYPE_ID_CHEQUE', 2);
define('FUND_TRANSFER_TYPE_ID_NEFT', 3);
define('FUND_TRANSFER_TYPE_ID_DD', 4);
define('FUND_TRANSFER_TYPE_ID_BANK_TRANSFER', 5);

define('AUTO_SETTLE', 'Auto Settle');



define('MEDI_ASSIST', 'Medi Assist'); 
define('TYPE_MCC', 'MCC'); 
define('TYPE_TID', 'TID'); 
define('MEDIASSIST', 'medi-assist'); 

define('SUPER_AGENT', 'SUPER_AGENT'); 
define('DISTRIBUTOR_AGENT', 'DISTRIBUTOR_AGENT'); 
define('SUB_AGENT', 'SUB_AGENT'); 
define('NORMAL_AGENT', 'NORMAL_AGENT'); 

define('SUPER_AGENT_DB_VALUE', '-1'); 
define('DISTRIBUTOR_AGENT_DB_VALUE', '-2'); 
define('SUB_AGENT_DB_VALUE', '-3'); 
define('AGENT_DB_VALUE', '0'); 

define('SUPER_TO_DISTRIBUTOR', 'SUAT2DIST');
define('DISTRIBUTOR_TO_AGENT', 'DIST2SBAT');
define('RELATION_TYPE_KOTAK_AUTHORIZED_APPLICATION', 'KOTAK_AUTHORIZED_APPLICATION');
define('RELATION_TYPE_KOTAK_GUJ_AUTHORIZED_APPLICATION', 'KOTAK_GUJ_AUTHORIZED_APPLICATION');
define('RAT_MAPPER', 'RAT_MAPPER');
//Agent Funding
define('TXNTYPE_REVERSAL_AGENT_FUND_LOAD', 'RAFL');//Reversal Agent Fund Load

//BOI FORM
define('BOI_NSDC_FORM_DEBIT_MANDATE_MAX_VALUE','15000');


//CRN Master
define('CRN_MASTER_FILE_SEPARATOR', ',');

define('TYPE_COMMISSION', 'comm');
define('TYPE_FEE', 'fee'); 
define('CORP_DELIVERY_FILE_DELIMITER', ',');
define('KOTAK_CORP_ECS_REGN_LIMIT', 50);

define('BOI_CORP_ECS_REGN_LIMIT', 500);

define('AMOUNT_INT', 'AMOUNT_INT');
define('AMOUNT_FLOAT', 'AMOUNT_FLOAT');

// Kotak Amul Card Load
define('KOTAK_AMUL_WALLET_END_OF_FILE', 'EOF');
define('KOTAK_AMUL_WALLET_UPLOAD_DELIMITER', ',');
define('KOTAK_AMUL_WALLET_UPLOAD_COLUMNS', 8);
define('KOTAK_AMUL_WALLET_MANDATORY_FIELD_INDEX', 7);
define('KOTAK_AMUL_WALLET_TXN_IDENTIFIER_CN', 'cn');
define('KOTAK_AMUL_WALLET_TXN_IDENTIFIER_MI', 'mi');
define('KOTAK_AMUL_WALLET_TXN_IDENTIFIER_EI', 'ei');

//FILE Types
define('KOTAK_AMUL_AUTH_FILE', 'KOTAK_AMUL_AUTH_FILE');
define('BOI_TTUM_FILE', 'BOI_TTUM_FILE');
define('KOTAK_AMULGUJ_AUTH_FILE', 'KOTAK_AMULGUJ_AUTH_FILE');
define('AGENT_IMPORT_FILE','AGENT_IMPORT_FILE');
define('REMIT_KOTAK_FAILURE_RECON_FILE','REMIT_KOTAK_FAILURE_RECON_FILE');
define('REMITTANCE_TRANSACTION_FILE', 'REMITTANCE_TRANSACTION_FILE');
define('RAT_REMIT_TXN_RECON_FILE','RAT_REMIT_TXN_RECON_FILE');
define('KOTAK_REMIT_TXN_RECON_FILE','KOTAK_REMIT_TXN_RECON_FILE');

define('BOI_NSDC_WALLET_TXN_IDENTIFIER_CN', 'cn');
define('BOI_NSDC_WALLET_TXN_IDENTIFIER_MI', 'mi');

define('BOI_NSDC_CRN_UPDATE_LIMIT', 100);
define('BOI_NSDC_CORPORATE_LOAD_LIMIT', 1000);
define('BOI_NSDC_ACCOUNT_LOAD_LIMIT', 2000);
define('BOI_NSDC_PRODUCT','NSDC CARD');
define('BOI_NSDC_PRODUCT_WALLET','NSDC Wallet for BOI A/C');
define('BOI_NSDC_DELIVERY_FILE_DELIMITER', '|');
define('BOI_NSDC_CARD_MAPPING_FILE_DELIMITER', '|');
define('RCT_MASTER_STATE_CODE', '02');
define('RCT_MASTER_CITY_CODE', '01');
define('RCT_MASTER_DISTRICT_CODE', '19');
define('RCT_MASTER_RELATIONSHIP_CODE', '04');
define('RCT_MASTER_OCCUPATION_CODE', '21');
define('RCT_MASTER_COMMUNITY_CODE', '12');

define('REF_NUM', '7000000001');

//CONST
define('PRODUCT_CONST_BOI_NSDC', 'BOI_NSDC');
define('PRODUCT_CONST_RAT_MEDI', 'RATNAKAR_MEDIASSIST');
define('PRODUCT_CONST_RAT_PAT', 'RAT_PAYTRONIC');
define('PRODUCT_CONST_RAT_COP', 'RAT_COPASS');
define('PRODUCT_CONST_RAT_HAP', 'RAT_HAPPAY');
define('PRODUCT_CONST_AXS_MVC', 'AXIS_MVC');
define('PRODUCT_CONST_KOTAK_AMULWB', 'KOTAK_AMULWB');
define('PRODUCT_CONST_KOTAK_AMULGUJ', 'KOTAK_AMULGUJ');
define('PRODUCT_CONST_KOTAK_SEMICLOSE_GPR', 'KOTAK_SEMICLOSE_GPR');
define('PRODUCT_CONST_KOTAK_OPENLOOP_GPR', 'KOTAK_OPENLOOP_GPR');
define('PRODUCT_CONST_RAT_CNY', 'RAT_CNERGYIS');
define('PRODUCT_CONST_RAT_GPR', 'RAT_GENERIC_GPR');
define('PRODUCT_CONST_RAT_REMIT', 'RAT_REMIT');
define('PRODUCT_CONST_RAT_SUR', 'RAT_SURYODAY');
define('PRODUCT_CONST_BOI_REMIT', 'BOI_REMIT');
define('PRODUCT_CONST_RAT_MVC', 'RAT_MVC');
define('PRODUCT_CONST_RAT_PAYU', 'RAT_PAYU');
define('PRODUCT_CONST_RAT_SHOP', 'RAT_SHOPCLUES');
define('PRODUCT_CONST_KOTAK_FORBES', 'KOTAK_FORBES');
define('PRODUCT_CONST_KOTAK_FORBES_KTKREMIT', 'KTK_REMIT');
define('PRODUCT_CONST_KOTAK_REMIT', 'KOTAK_REMIT');
define('PRODUCT_CONST_SIMULATOR', 'SIMULATOR');
define('PRODUCT_CONST_RAT_CTY', 'RAT_CTY');
define('PRODUCT_CONST_RAT_HFCI', 'RAT_HFCI');
define('PRODUCT_CONST_RAT_BMS', 'RAT_BOOKMYSHOW');
define('PRODUCT_CONST_RAT_SMP', 'RAT_SMP');
define('PRODUCT_CONST_RAT_TFS', 'RAT_TFS');

define('TYPE_A', 'TYPE_A');
define('TYPE_B', 'TYPE_B');
define('TYPE_C', 'TYPE_C');
define('TYPE_D', 'TYPE_D');
define('TYPE_E', 'TYPE_E');
define('TYPE_F', 'TYPE_F');
define('TYPE_G', 'TYPE_G');
define('TYPE_H', 'TYPE_H');
define('TYPE_J', 'TYPE_J');
define('TYPE_OTP', 'otp');
define('TYPE_OTP_LOAD', 'otp_load');
define('TYPE_OTP_LOAD_CONFIRMED', 'otp_load_confirmed');
define('TYPE_OTP_TXN_VERIFIED', 'otp_txn_verified');
define('TYPE_OTP_CUST_REGN', 'otp_cust');
define('TYPE_OTP_BENE_REGN', 'otp_bene');
define('TYPE_OTP_BENE_UPDATE', 'otp_bene_update');
define('TYPE_OTP_CUST_UPDATE', 'otp_cust_update');
define('TYPE_OTP_REMITTANCE', 'otp_remittance');
define('TYPE_OTP_TRANSFER', 'otp_transfer');
define('TYPE_OTP_REFUND', 'otp_refund');
define('TYPE_OTP_UNBLOCK', 'otp_unblock');


define('KOTAK_AMUL', 'KOTAK_AMUL');
define('BOI_NSDC', 'BOI_NSDC');
define('RATNAKAR_MEDIASSIST', 'RATNAKAR_MEDIASSIST');

define('TYPE_NONE', 'none'); 
define('TYPE_REMIT', 'remit'); 
define('TYPE_MVC', 'mvc'); 
define('TYPE_KYC', 'kyc'); 
define('TYPE_NONKYC', 'non-kyc'); 

define('BY_CUSTOMER', 'CUSTOMER'); 


//Wallet Balance

define('BOI_NSDC_PRODUCT_CODE', '006'); 
define('BANK_OF_INDIA', 'Bank of India'); 
define('KOTAK_BANK', 'Kotak Mahindra Bank'); 
define('RATNAKAR_BANK_NAME', 'Ratnakar Bank'); 

define('RESEND_VERIFICATION_SUCCESS','The Verification email has been re-sent. You would have received an email from noreply@shmart.in. Please check Junk/Spam folder as well.');
define('RESEND_VERIFICATION_ERROR','The Email ID and/or Mobile Number provided by you does not match our records. Kindly call us at (022) 61307021 or email us at partner@shmart.in for more information.');

//Constants used in corporate module
define('CORPORATE_SECTION_SETTING_ID', 1);
define('HEAD_CORPORATE', 'HEAD_CORPORATE'); 
define('REGIONAL_CORPORATE', 'REGIONAL_CORPORATE'); 
define('LOCAL_CORPORATE', 'LOCAL_CORPORATE'); 


define('SUPER_CORPORATE_DB_VALUE', '-1'); 
define('DISTRIBUTOR_CORPORATE_DB_VALUE', '-2'); 
define('SUB_CORPORATE_DB_VALUE', '-3'); 
define('CORPORATE_DB_VALUE', '0');

define('HEAD_CORPORATE_GROUP', '4'); 
define('REGIONAL_CORPORATE_GROUP', '2'); 
define('LOCAL_CORPORATE_GROUP', '3'); 


define('HEAD_TO_REGIONAL', 'HEAD2REGIONAL');
define('REGIONAL_TO_LOCAL', 'REGIONAL2LOCAL');
define('SETTING_CORPORATE_MAX_BALANCE','max_balance');
define('SETTING_CORPORATE_MIN_BALANCE','min_balance');
define('TXNTYPE_CORPORATE_TOCORPORATE_FUND_TRANSFER', 'CCFT');
define('TXNTYPE_CORPORATE_TOCORPORATE_FUND_REVERSAL', 'RCFT');


define('PROGRAM_CODE_KOTAK_AMUL_WB', '002'); 
define('PROGRAM_CODE_KOTAK_AMUL_GUJ', '009'); 
define('PLASTIC_CODE_KOTAK_AMUL_GUJ', '051');

define('CORP_DISBURESEMENT_UPLOAD_COLUMNS', 12);
define('CORP_DISBURESEMENT_MANDATORY_FIELD_INDEX', 0);
define('CORP_DISBURESEMENT_TITUM_MAX_RECORDS', 500);


//Bucket
define('BUCKET_TYPE_MATCHED','1');
define('BUCKET_TYPE_MATCH_AADHAAR','2');
define('BUCKET_TYPE_MATCH_ACCOUNT','3');
define('BUCKET_TYPE_NOT_MATCHED','4');
define('BUCKET_TYPE_RETURN','5');
define('BUCKET_TYPE_MANUAL','6');
define('BUCKET_TYPE_DUPLICATE_DISBURSEMENT','8');
define('BUCKET_TYPE_WRONG_AMOUNT','9');



define('KOTAK_EMBOSSING_FILE_NAME','CRD419953');

define('STATUS_GENERATED', 'generated');
define('STATUS_MANUAL', 'manual');
define('SAMPLE_AMOUNT_TEXT', '_AMOUNT_');
define('SAMPLE_NARRATION_TEXT', 'TEST LOAD');

define('SMS_PENDING', 'p_sms');
define('SMS_FAILED', 'f_sms');
define('SMS_SENT', 's_sms');
define('TYPE_SMS','sms');
define('KOTAK_BANK_STATEMENT_COLUMNS',4);
define('ICICI_BANK_STATEMENT_COLUMNS',10);

//Ratnakar Remittance
define('RATNAKAR_REMITTANCE', 'RBL Bank Shmart Transfer'); 
define('RATNAKAR_SHMART_REMIT','Ratnakar Bank Shmart Remit');
define('RATNAKAR_REMITTANCE_EMAIL','care.rbl@shmart.in');
define('RATNAKAR_CALL_CENTRE_NUMBER','022 67304948');
define('RATNAKAR_MAX_BENFICIARY_COUNT',10);
define('RATNAKAR_REMITTANCE_SELECT_DAYS_DATA','30');
define('RATNAKAR_REMITTANCE_MIN_AMOUNT_PER_TXN', 10);
define('RATNAKAR_REMIT_BATCH_NAME_PREFIX', 'RBL');
define('RATNAKAR_REMITTANCE_TXN_LIMIT_PER_BATCHFILE', 999);
define('RATNAKAR_REMITTANCE_MAX_AMOUNT_LIMIT_PER_BATCHFILE', 2450000);
//Kotak care for corporates
define('KOTAK_CORPORATE_EMAIL','kotak.care@shmart.in');
define('KOTAK_CALL_CORPORATE_CENTRE_NUMBER','022 67304948');

//File Extensions
define('FILE_TYPE_CSV', 'csv');
define('FILE_TYPE_TXT', 'txt'); 


define('RAT_NEFT_PAYMENT_TYPE', 'NFT');
define('NEFT_SENDER_NARRATION', 'To NEFT');

//Manual adjustment column count
define('CORP_KOTAK_MANUAL_ADJUSTMENT_COLUMNS', 6);
define('CORP_RBL_MANUAL_ADJUSTMENT_COLUMNS', 6);

//Bank Id's
define('RATNAKAR_BANK_ID','3');
define('KOTAK_BANK_ID','4');




define('ADMIN_EMAIL_AND_CONTACT_NO_TEXT','');
define('ADMIN_EMAIL_IDS','sridhar@transerv.co.in');
define('NEW_ADDITION','New Addition');
define('DELETION','Deleted');
define('ADMIN_USER_GROUP',3);


define('CARD_BLOCK_SMS','CARD_BLOCK_SMS');
define('CARD_UNBLOCK_SMS','CARD_UNBLOCK_SMS');
define('BALANCE_ENQUIRY_SMS','BALANCE_ENQUIRY_SMS');
define('MINI_STATEMENT_SMS','MINI_STATEMENT_SMS');
define('ENABLE_FOR_ALL', 'all');


define('TYPE_REQUEST_ECOM','ecom');

define('STATUS_AML', 0);
define('STATUS_IS_AML', 1);
define('STATUS_AML_UPDATE', 2);

define('LOAD_REQ_BATCH_LIMIT', 10);

define('UNIVERSAL_BRANCH', 'UNIVERSAL');
define('UPDATE_CUST_SMS','UPDATE_CUST_SMS');
define('BENE_REGISTRATION','BENE_REGISTRATION');
define('CUST_REGISTRATION','CUST_REGISTRATION');
define('TRANSACTION_REQUEST','TRANSACTION_REQUEST');
define('TRANSFER','TRANSFER');
define('REMITTANCE','REMITTANCE');
define('CUST_REGISTRATION_API','CUST_REGISTRATION_API');
define('DIACTIVE_BENE_SMS','DIACTIVE_BENE_SMS');
define('REMITTANCE_NORESPONSE_REQUEST_SMS','REMITTANCE_NORESPONSE_REQUEST_SMS');
define('KOTAK_IMPS_TXT_FOR_SMS','IMPS');
define('REFUND_TRANSACTION_API','REFUND_TRANSACTION_API');
define('REMITTANCE_SUCCESS_REQUEST_SMS','REMITTANCE_SUCCESS_REQUEST_SMS');
define('REMITTANCE_FAILURE_REQUEST_SMS','REMITTANCE_FAILURE_REQUEST_SMS');


//RBL Payu
define('REMITTER_FLAG_EMAIL', 'E');
define('REMITTER_FLAG_PARTNER_REFERENCE_NUMBER', 'P');
define('SUCCESS_QUERY_BENEFICIARY_MESSAGE', 'Query Beneficiary Successful');
define('SUCCESS_RESPONSE_CODE', 0);
define('SUCCESS_QUERY_BENEFICIARY_LIST_MESSAGE', 'Query Beneficiary List Successful');

define('TXN_HISTORY_COUNT',10);
define('RBL_PAYU_CUST_SERVICE_EMAIL','care@payumoney.com');
define('RBL_PAYU_PRODUCT_NAME_FOR_SMS','PayUMoney');
define('RBL_PAYU_FUND_TRANSFER_TXT_FOR_SMS','FUNDS TRANSFER');
define('RBL_PAYU_NEFT_TXT_FOR_SMS','NEFT');


define('TXNTYPE_WALLET_TOWALLET_FUND_TRANSFER', 'WWFT');

define('CUSTOMER_TRACK_MOBILE_FLAG', '4');
define('DEFAULT_MOBILE_COUNTRY_CODE','91');

define('TXN_IDENTIFIER_TYPE_MOBILE', 'MOB');
define('TXN_IDENTIFIER_TYPE_PARTNER', 'PAR');
define('AGENT_MANAGE_TYPE', 'agent');
define('CORPORATE_MANAGE_TYPE', 'corp');
define('LOAD_FALSE', 'false');
define('LOAD_TRUE', 'true');
define('OTP_REQUEST_FALSE', 'false');
define('OTP_REQUEST_TRUE', 'true');
define('SEND_SMS_FALSE', 'false');
define('SEND_SMS_TRUE', 'true');




define('WALLET_WISE_BALANCE', 'ALL000');
define('MIN_AGE_ALLOW_18', '18');


//FUND TRANSFER TYPE CONST
define('FUND_TRANSFER_TYPE_CASH', 'CASH');
define('FUND_TRANSFER_TYPE_CHEQUE', 'CD');
define('FUND_TRANSFER_TYPE_NEFT', 'NEFT');
define('FUND_TRANSFER_TYPE_DD', 'DD');

// REGIONAL TO LOCAL CORP
define('REGIONAL2LOCAL','17');

define('POOL_AC', 'pool_ac');
define('PAYABLE_AC', 'payable_ac');


define('RAT_ORDERING_ACC_NO', '409000267642');
define('COMPANY_NAME', 'TRANSERV MERCHANT SETTLEMENT A/C');
define('SETTLEMENT_REMITTER', 'TranServ');
define('SETTLEMENT_INDICATOR', 'EML');
define('SETTLEMENT_FROM', 'Settlement from TranServ');
define('TOTAL_ACCEPTED_AMOUNT', 'total_accepted_amount');

define('FAILED_RECORD_NUM','1');

define('REVERSAL_FLAG_YES','y');
define('KTK_EPRS_AGENT_ID', '448' );

define('SAVING_ACCOUNT_TYPE','savings');
define('CURRENT_ACCOUNT_TYPE','current');

define('DEBUG_MVC',TRUE);

define('RBL_FUND_TRANSFER_TXT_FOR_SMS','RBL fund transfer');

//Reports Name
define('AGENT_BALANCE_SHEET_REPORT','agent_balance_sheet_report');
define('WALLET_BALANCE_SHEET_REPORT','wallet_balance_report');
define('AGENT_VIRTUAL_BALANCE_SHEET_REPORT','agent_virtual_balance_sheet_report');
define('BENEFICIARY_INC_EXCEED_SHEET_REPORT','Beneficiary_inc_exceed_sheet_report');
define('BENEFICIARY_REMITTER_EXCEED_REPORT','Beneficiary_remitter_exceed_report');

define('KOTAK_REMITTER','remitter');
define('DEBUG_ECS',TRUE);
define('BENEFICIARY_EXCEPTION_MIN_MONEY', 100001);
define('BENEFICIARY_EXCEPTION_MIN_REMITTER', 11);
define('IPAY','ipay');
define('PRODUCT_ID_SMP', '24');
// Partner Name

define('SHMART_AGENT_NETWORK','TRANSERV');
 

define('ALLOW_VOUCHER_LIMIT','100');
define('REVERSAL_FLAG_NO','n');


define('SMS_VALID_OTP_TIME_TEMPLATE', '15');
define('TRANSACTION_REQUEST_CR','TRANSACTION_REQUEST_CR');
define('TRANSACTION_UNPROCESSED','TRANSACTION_UNPROCESSED');
define('CRON_SMP_TRANSACTION_INITIMATION', 82);
define('BASE_URL_SHMART_LOGS','/var/www/shmart_logs');

define('FLAG_RESPONSE_ONE','1');
define('FLAG_RESPONSE_TWO','2');

define('SHMARTMONEY_PRODUCT','Shmart! Visa Card');

define('CHANNEL_OPS', 'ops');
define('CHANNEL_API', 'api');
define('CHANNEL_AGENT', 'agent');
define('CHANNEL_CORPORATE', 'corporate');
define('CHANNEL_SYSTEM', 'system');
define('SHMART_WALLET','Shmart! Wallet');
define('SHMART_WEBSITE','www.shmart.in');

define('SMP_SHMART_ACCOUNT','Shmart! Wallet');
 
define('DEFAULT_SMS_TO_NAME','Customer');

define('RATNAKAR_MONEY_SERVICES', 'Ratnakar Money Services');
define('DEFAULT_TRANACTION_BY', 'cash');
define('TRANACTION_BY_WALLET', 'wallet');
define('CLAIM_BLOCK_AMOUNT_TYPE','c');
define('RATNAKAR_AGENT_CALL_CENTRE_NUMBER','022-67304948');
define('SHMART_MOBILE_APP','http://go.shmart.mobi');
