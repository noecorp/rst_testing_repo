<?php

class DbTable {
    //Table Constants
    const TABLE_API_EMAIL_LOG = 'api_email_log';
    const TABLE_API_ISO_CALLS = 'api_iso_calls';
    const TABLE_API_PERMISSION = 'api_permission';
    const TABLE_API_SESSION = 'api_session';
    const TABLE_API_SMS_LOG = 'api_sms_log';
    const TABLE_API_SOAP_CALLS = 'api_soap_calls';
    const TABLE_API_USER = 'api_user';
    const TABLE_API_USER_IP = 'api_user_ip';
    const TABLE_API_USER_LOGIN = 'api_user_login';
    const TABLE_AGENT_BALANCE = 't_agent_balance';
    const TABLE_AGENT_VIRTUAL_BALANCE = 't_agent_virtual_balance';
    const TABLE_AGENT_CLOSING_BALANCE = 't_agent_closing_balance';
    const TABLE_AGENT_VIRTUAL_CLOSING_BALANCE = 't_agent_virtual_closing_balance'; 
    const TABLE_AGENT_DETAILS = 't_agent_details';
    const TABLE_AGENT_FUND_REQUEST = 't_agent_fund_request';
    const TABLE_AGENT_FUNDING = 'agent_funding';
    const TABLE_AGENT_VIRTUAL_FUNDING = 'agent_virtual_funding';
    
    const TABLE_AGENT_FUND_RESPONSE = 't_agent_fund_response';
    const TABLE_AGENT_LIMIT = 't_agent_limit';
    const TABLE_AGENTS = 't_agents';
    const TABLE_BANK = 't_bank';
    const TABLE_BANK_USER = 'bank_users';
    const TABLE_BANK_IFSC = 't_bank_ifsc';
    const TABLE_BANK_STATEMENT = 'bank_statement';
    const TABLE_IPAY_BANK_STATEMENT = 'ipay_bank_statment';    
    
    const TABLE_BENEFICIARIES = 't_beneficiaries';
    const TABLE_BIND_AGENT_LIMIT = 't_bind_agent_limit';
    const TABLE_BIND_AGENT_PRODUCT_COMMISSION = 't_bind_agent_product_commission';
    const TABLE_CARDHOLDER_DETAILS = 't_cardholder_details';
    const TABLE_CARDHOLDER_OFFERS = 't_cardholder_offers';
    const TABLE_CARDHOLDERS = 't_cardholders';
    const TABLE_CARDHOLDERS_MVC = 't_cardholders_mvc';
    const TABLE_CARDHOLDERS_PRODUCT = 't_cardholders_product';
    const TABLE_CARDLOADS = 't_cardloads';
    const TABLE_CHANGE_STATUS_LOG = 't_change_status_log';
    const TABLE_CITIES = 't_cities';
    const TABLE_COMMISSION_ITEMS = 't_commission_items';
    const TABLE_COMMISSION_REPORT = 't_commission_report';
    const TABLE_COUNTRIES = 't_countries';
    const TABLE_CRON = 't_cron';
    const TABLE_CRON_SCHEDULE = 't_cron_schedule';
    const TABLE_CURRENCY = 't_currency';
    const TABLE_DOCS = 't_docs';
    const TABLE_ECS_CRN = 't_ecs_crn';
    const TABLE_EMAIL_VERIFICATION = 't_email_verification';
    const TABLE_FEE_ITEMS = 't_fee_items';
    const TABLE_FLAGS = 't_flags';
    const TABLE_FLIPPERS = 't_flippers';
    const TABLE_FUND_TRANSFER_TYPE = 't_fund_transfer_type';
    const TABLE_GROUP = 't_groups';
    const TABLE_LOG = 't_log';
    const TABLE_LOG_MASTER = 'log_master';
    const TABLE_LOG_BANK = 't_log_bank';
    const TABLE_LOG_CRON = 't_log_cron';
    const TABLE_LOG_EMAIL = 't_log_email';
    const TABLE_LOG_FORGOT_PASSWORD = 't_log_forgot_password';
    const TABLE_LOG_PRODUCTS = 't_log_products';
    const TABLE_LOG_SMS = 't_log_sms';
    const TABLE_LOG_LOGIN = 't_log_login';
    const TABLE_OPERATION_USERS = 't_operation_users';
    const TABLE_OPERATION_USERS_GROUP = 't_operation_users_groups';
    const TABLE_PLAN_COMMISSION = 't_plan_commission';
    const TABLE_PLAN_FEE = 't_plan_fee';
    const TABLE_PRIVILEGES = 't_privileges';
    const TABLE_PRODUCT_LIMIT = 't_product_limit';
    const TABLE_PRODUCT_PRIVILEGES = 't_product_privileges';
    const TABLE_PRODUCTS = 't_products';
    const TABLE_REMITTANCE_REFUND = 't_remittance_refund';
    const TABLE_REMITTANCE_REQUEST = 't_remittance_request';
    const TABLE_REMITTANCE_STATUS_LOG = 't_remittance_status_log';
    const TABLE_REMITTERS = 't_remitters';
    const TABLE_ROLES = 't_roles';    
    const TABLE_ROLE_PRIVILEGES = 't_role_privileges';    
    const TABLE_OPERATION_USER_PRIVILEGES = 't_operation_user_privileges';        
    const TABLE_USER_ROLES = 't_user_roles';            
    const TABLE_SETTINGS = 't_settings';
    const TABLE_SETTING_SECTIONS = 't_settings_sections';
    const TABLE_STATES = 't_states';
    const TABLE_THIRD_PARTY_USER = 't_third_party_user';
    const TABLE_TRANSACTION_TYPE = 't_transaction_type';
    const TABLE_TXN_AGENT = 't_txn_agent';
    const TABLE_TXN_BENEFICIARY = 't_txn_beneficiary';
    const TABLE_TXN_CARDHOLDER = 't_txn_cardholder';
    const TABLE_TXN_OPS = 't_txn_ops';
    const TABLE_TXN_REMITTER = 't_txn_remitter';
    const TABLE_TXNCODE = 't_txncode';
    const TABLE_UNICODE = 't_unicode';
    const TABLE_UNICODE_CONF = 't_unicode_conf';
    const TABLE_LOG_NEFT_DOWNLOAD = 't_log_neft_download';
    const TABLE_LOG_CHANGE_PASSWORD = 't_log_change_password';

    const TABLE_CORPORATE = 'corporate_master';
    const TABLE_LOG_CORPORATE = 'log_corporate_master';
    const TABLE_CORPORATE_USER = 'corporate_users';
    const TABLE_CORPORATE_USER_DETAILS = 'corporate_users_details';
    const TABLE_CORPORATE_GROUP = 't_corporate_groups';
    const TABLE_CORPORATE_USER_GROUP = 't_corporate_users_groups';
    const TABLE_CORPORATE_LIMIT = 'corporate_limit';
    
    
    const TABLE_RAT_CORP_INSURANCE_CLAIM = 'rat_corp_insurance_claim';
    
    const TABLE_RAT_CORP_HOSPITAL = 'rat_corp_hospital';
    const TABLE_RAT_CORP_TERMINAL = 'rat_corp_terminal';
    const TABLE_LOG_RAT_CORP_HOSPITAL = 'log_rat_corp_hospital';
    const TABLE_LOG_RAT_CORP_TERMINAL = 'log_rat_corp_terminal';
    
    const TABLE_RAT_CORP_CARDHOLDER = 'rat_corp_cardholders';
    const TABLE_RAT_CORP_CARDHOLDER_DETAILS = 'rat_corp_cardholders_details';
    const TABLE_RAT_CORP_CARDHOLDER_BATCH = 'rat_corp_cardholder_batch';
    const TABLE_RAT_CUSTOMER_MASTER = 'rat_customer_master';
    const TABLE_CUSTOMER_MASTER = 'customer_master';
    const TABLE_RAT_CUSTOMER_PRODUCT = 'rat_customer_product';
    const TABLE_RAT_CUSTOMER_PURSE = 'rat_customer_purse';
    const TABLE_RAT_TXN_CUSTOMER = 'rat_txn_customer';
    const TABLE_PURSE_MASTER = 'purse_master';
    const TABLE_RAT_CORP_LOAD_REQUEST = 'rat_corp_load_request';
    const TABLE_RAT_CORP_LOAD_REQUEST_DETAIL = 'rat_corp_load_request_detail';
    const TABLE_RAT_CORP_LOAD_REQUEST_BATCH = 'rat_corp_load_request_batch';
    const TABLE_RAT_CORP_LOG_CARDHOLDER = 'rat_corp_log_cardholder';
    const TABLE_RAT_PAYMENT_HISTORY = 'rat_payment_history';
    const TABLE_RAT_RESPONSE_PAYMENT_HISTORY = 'rat_response_file';
    const TABLE_RAT_UPDATE_CORP_CARDHOLDERS_LOG = 'rat_update_corp_cardholders_log';
    const TABLE_RAT_SETTLEMENT_REQUEST = 'rat_settlement_request';
    const TABLE_RAT_DEBIT_DETAIL = 'rat_debit_detail';
    
    const TABLE_KOTAK_REMITTERS = 'kotak_remit_remitters';
    const TABLE_KOTAK_BENEFICIARIES = 'kotak_beneficiaries';
    const TABLE_KOTAK_REMITTANCE_REQUEST = 'kotak_remittance_request';
    const TABLE_KOTAK_REMITTANCE_STATUS_LOG = 'kotak_remittance_status_log';
    const TABLE_KOTAK_TXN_REMITTER = 'kotak_txn_remitter';
    const TABLE_KOTAK_TXN_BENEFICIARY = 'kotak_txn_beneficiary';
    const TABLE_KOTAK_REMITTANCE_REFUND = 'kotak_remittance_refund';
    const TABLE_AGENT_FUND_TRANSFER = 'agent_fund_transfer';
    const TABLE_LOG_PURSE_MASTER = 'log_purse_master';
    const TABLE_CRN_MASTER = 'crn_master';    
    const TABLE_BATCH_ADJUSTMENT = 't_batch_adjustment';
    const TABLE_KOTAK_BATCH_ADJUSTMENT = 'kotak_batch_adjustment'; 
    
    const TABLE_CARD_AUTH_REQUEST_MEDIASSIST = 'card_auth_request';
    const TABLE_CARD_AUTH_REQUEST = 'card_txn_processing';
    const TABLE_CARD_AUTH_REQUEST_DETAIL = 'card_auth_request_detail';
    const TABLE_MCC_MASTER = 'mcc_master';
    const TABLE_BIND_PURSE_MCC = 'bind_purse_mcc';
    
    const TABLE_BIND_OBJECT_RELATION_TYPES = 'object_relation_types';
    const TABLE_BIND_OBJECT_RELATION = 'object_relations';
    const TABLE_KOTAK_CORP_CARDHOLDER =  'kotak_corp_cardholders';
    const TABLE_KOTAK_CORP_LOG_CARDHOLDER =  'kotak_corp_log_cardholder';
    const TABLE_KOTAK_CUSTOMER_MASTER = 'kotak_customer_master';
    const TABLE_KOTAK_CUSTOMER_PRODUCT = 'kotak_customer_product';
    const TABLE_KOTAK_CUSTOMER_PURSE = 'kotak_customer_purse';
    const TABLE_KOTAK_TXN_CUSTOMER = 'kotak_txn_customer';
    const TABLE_KOTAK_CORP_LOAD_REQUEST = 'kotak_corp_load_request';
    const TABLE_KOTAK_CORP_LOAD_REQUEST_BATCH = 'kotak_corp_load_request_batch';
    const TABLE_KOTAK_CORP_CARDHOLDER_DETAILS =  'kotak_corp_cardholders_details';
    const TABLE_KOTAK_CORP_CARDHOLDER_BATCH = 'kotak_corp_cardholder_batch';
    
    const TABLE_AFN_NUMBER = 't_afn_no';
    const TABLE_DELIVERY_FLAG_MASTER = 'delivery_file_master';
    
    const TABLE_DATA_IMPORT_ECS = 'data_import_ecs';
    const TABLE_FILES = 't_files';
    
    const TABLE_BOI_CORP_CARDHOLDER =  'boi_corp_cardholders';
    const TABLE_BOI_CORP_LOG_CARDHOLDER =  'boi_corp_log_cardholder';
    const TABLE_BOI_CUSTOMER_MASTER = 'boi_customer_master';
    const TABLE_BOI_CUSTOMER_PRODUCT = 'boi_customer_product';
    const TABLE_BOI_CUSTOMER_PURSE = 'boi_customer_purse';
    const TABLE_BOI_TXN_CUSTOMER = 'boi_txn_customer';
    const TABLE_BOI_CORP_LOAD_REQUEST = 'boi_corp_load_request';
    const TABLE_BOI_CORP_LOAD_REQUEST_DETAIL = 'boi_corp_load_request_detail';
    const TABLE_BOI_CORP_LOAD_REQUEST_BATCH = 'boi_corp_load_request_batch';
    const TABLE_BOI_CORP_CARDHOLDER_DETAILS =  'boi_corp_cardholders_details';
    const TABLE_BOI_DELIVERY_FLAG_MASTER = 'boi_delivery_file_master';
    const TABLE_BOI_CARD_MAPPING = 'boi_card_mapping';
    const TABLE_BOI_OUTPUT_FILE = 'output_file';
    const TABLE_BOI_DISBURSEMENT_FILE = 'boi_disbursement_file';
    const TABLE_BOI_DISBURSEMENT_BATCH = 'boi_disbursement_batch';
    
    const TABLE_REPORT_TPMIS = 'report_tpmis';
    
    const TABLE_RCT_MASTER = 'rct_master';
    const TABLE_SOL_ID_LIST = 'sol_id_list';

    
    const TABLE_CUSTOMER_TRACK = 'customer_track';
    const TABLE_REFERENCE = 't_ref';
    
    const TABLE_GLOBAL_PURSE_MASTER = 'global_purse_master';
    const TABLE_LOG_GLOBAL_PURSE_MASTER = 'log_global_purse_master';
    
    const TABLE_BANK_GROUP = 't_bank_groups';
    const TABLE_BANK_USERS_GROUP = 't_bank_users_groups';
    
    const TABLE_BANK_CUSTOMER_LIMITS = 'bank_customer_limits';
    const TABLE_LOG_BANK_CUSTOMER_LIMITS = 'log_bank_customer_limits';
    
    const TABLE_PRODUCT_CUSTOMER_LIMITS = 'product_customer_limits';
    const TABLE_LOG_PRODUCT_CUSTOMER_LIMITS = 'log_product_customer_limits';
    
    
    const TABLE_BIND_GLOBAL_PURSE_MCC = 'bind_global_purse_mcc';
    const TABLE_LOG_BIND_GLOBAL_PURSE_MCC = 'log_bind_global_purse_mcc';
    
    const TABLE_CORPORATE_BIND_OBJECT_RELATION = 'corporate_object_relations';
    const TABLE_BIND_CORPORATE_PRODUCT_COMMISSION = 'corporate_bind_product_commission';
    const TABLE_BIND_CORPORATE_LIMIT = 'corporate_bind_limit';
    const TABLE_CORPORATE_FUNDING = 'corporate_funding';
    const TABLE_CORPORATE_TXN = 'corporate_txn';
    const TABLE_CORPORATE_BALANCE = 'corporate_balance';
    const TABLE_BOI_DISBURESEMENT_BATCH = 'boi_disbursement_batch';
    const TABLE_BOI_DISBURESEMENT_STATUS_LOG = 'boi_disbursement_status_log';
    const TABLE_BOI_DISBURESEMENT_FILE = 'boi_disbursement_file';
    const TABLE_CORPORATE_FUND_TRANSFER = 'corporate_fund_transfer';
    
    const TABLE_RAT_CUSTOMER_PURSE_CLOSING_BALANCE = 'rat_customer_closing_balance';
    const TABLE_KOTAK_CUSTOMER_PURSE_CLOSING_BALANCE = 'kotak_customer_closing_balance';
    const TABLE_BOI_CUSTOMER_PURSE_CLOSING_BALANCE = 'boi_customer_closing_balance';
    const TABLE_BOI_TP_MIS_REPORT = 'report_tpmis';
    
    const TABLE_SMS = 't_sms';
    const TABLE_CORPORATE_PRODUCT_PRIVILEGES = 't_corporate_product_privileges';
    const TABLE_AGENT_IMPORT = 'agent_import';
    
    
    const TABLE_RATNAKAR_REMITTERS = 'rat_remit_remitters';
    const TABLE_RATNAKAR_BENEFICIARIES = 'rat_beneficiaries';
    const TABLE_RATNAKAR_REMITTANCE_REQUEST = 'rat_remittance_request';
    const TABLE_RATNAKAR_REMITTANCE_STATUS_LOG = 'rat_remittance_status_log';
    const TABLE_RATNAKAR_TXN_REMITTER = 'rat_txn_remitter';
    const TABLE_RATNAKAR_TXN_BENEFICIARY = 'rat_txn_beneficiary';
    const TABLE_RATNAKAR_REMITTANCE_REFUND = 'rat_remittance_refund';
    const TABLE_RAT_LOG_NEFT_DOWNLOAD = 'rat_log_neft_download';
    const TABLE_RATNAKAR_PAYMENT_HISTORY = 'rat_payment_history';
    const TABLE_RATNAKAR_RESPONSE_FILE = 'rat_response_file';
    const TABLE_RATNAKAR_RESPONSE_FILE_STATUS_LOG = 'rat_response_file_status_log';
    const TABLE_RATNAKAR_SETTLEMENT_RESPONSE = 'rat_settlement_response';
    
    const TABLE_AML_MASTER = 't_aml_master';
    const TABLE_BENEFICIARY_CODE = 't_benecode';
    const TABLE_RATNAKAR_WALLET_TRANSFER = 'rat_wallet_transfer';
    const TABLE_WALLET_CREDIT_INFO = 'wallet_credit_info';
    
    const TABLE_CUSTOMERS = 't_customers';  
    const TABLE_CUSTOMERS_DETAIL = 'customers_detail';
    const TABLE_CUSTOMER_UPDATE_LOG = 't_customer_update_log';

    const TABLE_RAT_SETTLEMENT_BATCH = 'rat_settlement_batch';

    const TABLE_TID_MASTER = 'tid_master';
    const TABLE_BIND_PURSE_TID = 'bind_purse_tid';
    const TABLE_AGENT_FUNDING_IPAY = 'agent_funding_ipay';
    
    const TABLE_CLOSED_LOOP_AGENTS = 't_closed_loop_agents';
    const TABLE_CLOSED_LOOP_AGENTS_LOG = 't_closed_loop_agents_log';
    
    const TABLE_FEE_STRUCTURE = 't_fee_structure';
    const TABLE_FEE_STRUCTURE_LOG = 't_fee_structure_log';
    const TABLE_RAT_MVC_CARDHOLDER_DETAILS = 'rat_mvc_cardholder_details'; 
    const TABLE_RAT_MVC_CARDHOLDER_OFFERS = 'rat_mvc_cardholder_offers';
    const TABLE_RAT_MVC_CARDHOLDER_STATUS = 'rat_mvc_cardholders_status';
    
    const TABLE_AGENT_BALANCE_SETTINGS = 't_agent_balance_settings';
    
    const TABLE_BLOCK_AMOUNT = 'block_amount';
    
}
