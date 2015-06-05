<?php

class ErrorCodes {
    //Table Constants
    const ERROR_OPERATION_LOGIN_FAILURE = '1001';
    const ERROR_OPERATION_INVALID_USERNAME_PASSWORD = '1002';
     
    #######
    const ERROR_SYSTEM_ERROR  = '1009';     
    
    #######
    const ERROR_CARDHOLDER_NOT_FOUND  = '1050';
    const ERROR_INSUFFICIENT_DATA_FOR_PROCESSING  = '1051';    
    const ERROR_INSUFFICIENT_BALANCE  = '1052';    
    const ERROR_INVALID_MCC  = '1053';    
    const ERROR_INSUFFICIENT_AMOUNT  = '1054'; 
    const ERROR_TRANSACTION_LIMIT  = '1055'; 
    const ERROR_TRANSACTION_FREQUENCY  = '1056';
    const ERROR_INVALID_TID  = '1057';
    
    
    #################Edigital###########
    const ERROR_EDIGITAL_INVALID_LOGIN_CODE  = '100';
    const ERROR_EDIGITAL_INVALID_LOGIN_MSG  = 'Invalid Login';
    const ERROR_EDIGITAL_INVALID_PRODUCT_CODE  = '421';
    const ERROR_EDIGITAL_INVALID_PRODUCT_MSG  = 'Invalid parameter ProductCode';
    const ERROR_EDIGITAL_INVALID_MOB_CODE  = '422';
    const ERROR_EDIGITAL_INVALID_MOB_MSG  = 'Invalid parameter Mobile';
    const ERROR_EDIGITAL_INVALID_REQUEST_TYPE_CODE  = '423';
    const ERROR_EDIGITAL_INVALID_REQUEST_TYPE_MSG  = 'Invalid parameter RequestType';
    const ERROR_EDIGITAL_INVALID_NARRATION_CODE  = '424';
    const ERROR_EDIGITAL_INVALID_NARRATION_MSG  = 'Invalid parameter Narration';
    const ERROR_EDIGITAL_INVALID_IS_ORIGINAL_CODE  = '425';
    const ERROR_EDIGITAL_INVALID_IS_ORIGINAL_MSG  = 'Invalid parameter IsOriginal';
    const ERROR_EDIGITAL_INVALID_ORIGINAL_ACK_CODE  = '426';
    const ERROR_EDIGITAL_INVALID_ORIGINAL_ACK_MSG  = 'Invalid parameter OriginalAckNo';
    const ERROR_EDIGITAL_INVALID_FILLER1_CODE  = '427';
    const ERROR_EDIGITAL_INVALID_FILLER1_MSG  = 'Invalid parameter Filler1';
    const ERROR_EDIGITAL_INVALID_FILLER2_CODE  = '428';
    const ERROR_EDIGITAL_INVALID_FILLER2_MSG  = 'Invalid parameter Filler2';
    const ERROR_EDIGITAL_INVALID_PIN_CODE  = '468';
    const ERROR_EDIGITAL_INVALID_PIN_MSG  = 'Invalid parameter Pincode';
    const ERROR_EDIGITAL_INVALID_FILLER3_CODE  = '429';
    const ERROR_EDIGITAL_INVALID_FILLER3_MSG  = 'Invalid parameter Filler3';
    
    const ERROR_EDIGITAL_INVALID_FILLER4_CODE  = '430';
    const ERROR_EDIGITAL_INVALID_FILLER4_MSG  = 'Invalid parameter Filler4';
    
    const ERROR_EDIGITAL_INVALID_FILLER5_CODE  = '431';
    const ERROR_EDIGITAL_INVALID_FILLER5_MSG  = 'Invalid parameter Filler5';
    
    const ERROR_EDIGITAL_INVALID_WALLET_LOAD_CODE  = '418';
    const ERROR_EDIGITAL_INVALID_WALLET_LOAD_MSG  = 'Expiry/Voucher feature is not permitted for this wallet';
    const ERROR_EDIGITAL_INVALID_WALLET_LOAD_EXPIRY_CODE  = '419';
    const ERROR_EDIGITAL_INVALID_WALLET_LOAD_EXPIRY_MSG  = 'Expiry date feature is not permitted for this transaction';
 
    
    const ERROR_EDIGITAL_INVALID_TXN_REF_CODE  = '409';
    const ERROR_EDIGITAL_INVALID_TXN_REF_MSG  = 'Invalid parameter TransactionRefNo';
    const ERROR_EDIGITAL_INVALID_PARTNER_REF_CODE  = '433';
    const ERROR_EDIGITAL_INVALID_PARTNER_REF_MSG  = 'Invalid parameter PartnerRefNo';
    const ERROR_EDIGITAL_INVALID_CARD_PACK_ID_CODE  = '434';
    const ERROR_EDIGITAL_INVALID_CARD_PACK_ID_MSG  = 'Invalid parameter CardPackId';
    const ERROR_EDIGITAL_INVALID_FNAME_CODE  = '435';
    const ERROR_EDIGITAL_INVALID_FNAME_MSG  = 'Invalid parameter FirstName';
    const ERROR_EDIGITAL_INVALID_LNAME_CODE  = '436';
    const ERROR_EDIGITAL_INVALID_LNAME_MSG  = 'Invalid parameter LastName';
    const ERROR_EDIGITAL_INVALID_DOB_CODE  = '437';
    const ERROR_EDIGITAL_INVALID_DOB_MSG  = 'Invalid parameter DateOfBirth';
    const ERROR_EDIGITAL_INVALID_EMAIL_CODE  = '439';
    const ERROR_EDIGITAL_INVALID_EMAIL_MSG  = 'Invalid parameter Email';
    const ERROR_EDIGITAL_INVALID_AGE_DOB_CODE  = '438';
    const ERROR_EDIGITAL_INVALID_AGE_DOB_MSG  = 'Invalid age limit of parameter DateOfBirth';
    
    const ERROR_EDIGITAL_INVALID_LOAD_EXPIRY_MSG  = 'Invalid parameter ExpiryDate';
    
    const ERROR_EDIGITAL_INVALID_CARD_ACTIVATED_CODE  = '440';
    const ERROR_EDIGITAL_INVALID_CARD_ACTIVATED_MSG  = 'Invalid parameter IsCardActivated';
    const ERROR_EDIGITAL_INVALID_CARD_DISPATCH_CODE  = '441';
    const ERROR_EDIGITAL_INVALID_CARD_DISPATCH_MSG  = 'Invalid parameter IsCardDispatch';
    const ERROR_EDIGITAL_INVALID_QUERY_REQ_NO_CODE  = '442';
    const ERROR_EDIGITAL_INVALID_QUERY_REQ_NO_MSG  = 'Invalid parameter AckNo';
    const ERROR_EDIGITAL_INVALID_CUSTOMER_IDENTIFIRE_TYPE_CODE  = '443';
    const ERROR_EDIGITAL_INVALID_CUSTOMER_IDENTIFIRE_TYPE_MSG  = 'Invalid parameter CustomerIdentifierType';
    const ERROR_EDIGITAL_INVALID_CUSTOMERNO_CODE  = '444';
    const ERROR_EDIGITAL_INVALID_CUSTOMERNO_MSG  = 'Invalid parameter CustomerNo';
    const ERROR_EDIGITAL_INVALID_OTP_CODE  = '445';
    const ERROR_EDIGITAL_INVALID_OTP_MSG  = 'Invalid parameter OTP';
    const ERROR_EDIGITAL_INVALID_SMS_FLAG_CODE  = '446';
    const ERROR_EDIGITAL_INVALID_SMS_FLAG_MSG  = 'Invalid parameter SMSFlag';
    const ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_CODE  = '447';
    const ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_MSG  = 'Invalid parameter TxnIndentifierType';
    const ERROR_EDIGITAL_INVALID_MEMBER_ID_CARD_NO_CODE  = '448';
    const ERROR_EDIGITAL_INVALID_MEMBER_ID_CARD_NO_MSG  = 'Invalid parameter MemberIDCardNo';
    const ERROR_EDIGITAL_INVALID_AMOUNT_CODE  = '449';
    const ERROR_EDIGITAL_INVALID_AMOUNT_MSG  = 'Invalid parameter Amount';
    
    const ERROR_EDIGITAL_INVALID_CORPCODE_MSG  = 'Invalid Corporate Code';
    const ERROR_EDIGITAL_INVALID_CORPCODE_CODE  = '106';
    
    const ERROR_EDIGITAL_CUSTOMER_BLOCKED_MSG  = 'Customer is blocked';
    const ERROR_EDIGITAL_CUSTOMER_BLOCKED_CODE  = '107';
    
    const ERROR_EDIGITAL_CUSTOMER_ALREADY_ACTIVE_MSG  = 'Customer is already active';
    const ERROR_EDIGITAL_CUSTOMER_ALREADY_ACTIVE_CODE  = '108';
    
    const ERROR_EDIGITAL_INVALID_CURRENCY_CODE  = '450';
    const ERROR_EDIGITAL_INVALID_CURRENCY_MSG  = 'Invalid parameter Currency';
    const ERROR_EDIGITAL_INVALID_WALLET_CODE  = '451';
    const ERROR_EDIGITAL_INVALID_WALLET_MSG  = 'Invalid parameter WalletCode';
    const ERROR_EDIGITAL_INVALID_TXNNUM_CODE  = '452';
    const ERROR_EDIGITAL_INVALID_TXNNUM_MSG  = 'Invalid parameter TxnNo';
    const ERROR_EDIGITAL_INVALID_CARDTYPE_CODE  = '453';
    const ERROR_EDIGITAL_INVALID_CARDTYPE_MSG  = 'Invalid parameter CardType';
    const ERROR_EDIGITAL_INVALID_TXNINDICATOR_CODE  = '454';
    const ERROR_EDIGITAL_INVALID_TXNINDICATOR_MSG  = 'Invalid parameter TxnIndicator';
    const ERROR_EDIGITAL_INVALID_REMITTERFLG_CODE  = '455';
    const ERROR_EDIGITAL_INVALID_REMITTERFLG_MSG  = 'Invalid parameter RemitterFlag';
    const ERROR_EDIGITAL_INVALID_REMITTERCODE_CODE  = '456';
    const ERROR_EDIGITAL_INVALID_REMITTERCODE_MSG  = 'Invalid parameter RemitterCode';
    const ERROR_EDIGITAL_INVALID_NAME_CODE  = '457';
    const ERROR_EDIGITAL_INVALID_NAME_MSG  = 'Invalid parameter Name';
    const ERROR_EDIGITAL_INVALID_BANKIFSC_CODE  = '458';
    const ERROR_EDIGITAL_INVALID_BANKIFSC_MSG  = 'Invalid parameter BankIfscode';
    const ERROR_EDIGITAL_INVALID_BANKACCOUNT_CODE  = '459';
    const ERROR_EDIGITAL_INVALID_BANKACCOUNT_MSG  = 'Invalid parameter BankAccountNumber';
    const ERROR_EDIGITAL_INVALID_BENECODE_CODE  = '460';
    const ERROR_EDIGITAL_INVALID_BENECODE_MSG  = 'Invalid parameter BeneficiaryCode';
    
    
    const ERROR_EDIGITAL_INVALID_REMITTYPE_CODE  = '463';
    const ERROR_EDIGITAL_INVALID_REMITTYPE_MSG  = 'Invalid parameter RemittanceType';
    const ERROR_EDIGITAL_INVALID_REMITTER_WALLET_CODE  = '464';
    const ERROR_EDIGITAL_INVALID_REMITTER_WALLET_MSG  = 'Invalid parameter RemitterWalletCode';
    const ERROR_EDIGITAL_INVALID_BENEEMAIL_CODE  = '465';
    const ERROR_EDIGITAL_INVALID_BENEEMAIL_MSG  = 'Invalid parameter BeneficiaryEmail';
    const ERROR_EDIGITAL_INVALID_BENEMOBILE_CODE  = '466';
    const ERROR_EDIGITAL_INVALID_BENEMOBILE_MSG  = 'Invalid parameter BeneficiaryMobile';
    const ERROR_EDIGITAL_INVALID_BENEFICIARY_WALLET_CODE  = '467';
    const ERROR_EDIGITAL_INVALID_BENEFICIARY_WALLET_MSG  = 'Invalid parameter BeneficiaryWalletCode';
    
    
    const ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_CODE  = '111';
    const ERROR_EDIGITAL_INVALID_UNABLE_PROCESS_MSG  = 'Unable to Process Request';
    
    const ERROR_EDIGITAL_INVALID_TRANSACTION_NETWORK_CODE = '412';
    const ERROR_EDIGITAL_INVALID_TRANSACTION_NETWORK_MSG  = 'Sorry, you cannot initiate a refund as the remittance transaction originated from other network';
    
    
    const ERROR_EDIGITAL_INVALID_PARTNER_CODE  = '471';
    const ERROR_EDIGITAL_INVALID_PARTNER_CODE_MSG  = 'Invalid Partner Code';
    const ERROR_EDIGITAL_INVALID_CARD_NO_CODE  = '432';
    const ERROR_EDIGITAL_INVALID_CARD_NO_MSG  = 'Invalid parameter Card Number';
    
    const ERROR_EDIGITAL_INVALID_WALLET_CODE_MSG  = 'Invalid Wallet Code';
    const ERROR_EDIGITAL_INVALID_WALLET_CODE_CODE  = '223';
    
    const ERROR_EDIGITAL_INVALID_FUNDING_AMOUNT_CODE  = '181';
    const ERROR_EDIGITAL_INVALID_FUNDING_AMOUNT_MSG  = 'Invalid Amount for Funding';
    const ERROR_EDIGITAL_INVALID_FUNDING_DETAILS_CODE  = '180';
    const ERROR_EDIGITAL_INVALID_FUNDING_DETAILS_MSG  = 'Invalid Funding Details';
    const ERROR_EDIGITAL_INVALID_FUNDING_OTHER_TXN_CODE  = '179';
    const ERROR_EDIGITAL_INVALID_FUNDING_OTHER_TXN_MSG  = 'Invalid Txn no. for Funding';
    const ERROR_EDIGITAL_INVALID_FUNDING_OTHER_JOURNAL_NO_CODE  = '178';
    const ERROR_EDIGITAL_INVALID_FUNDING_OTHER_JOURNAL_NO_MSG  = 'Invalid Journal no for Funding';
    const ERROR_EDIGITAL_INVALID_FUNDING_OTHER_CHEQUE_NO_CODE  = '177';
    const ERROR_EDIGITAL_INVALID_FUNDING_OTHER_CHEQUE_NO_MSG  = 'Invalid Cheque no for Funding';
    const ERROR_EDIGITAL_UNSUCCESSFULL_FUNDING_CODE  = '176';
    const ERROR_EDIGITAL_UNSUCCESSFULL_FUNDING_MSG  = 'Funding request was unsuccessfull';
    const ERROR_EDIGITAL_SUCCESSFULL_FUNDING_CODE  = '000';
    const ERROR_EDIGITAL_SUCCESSFULL_FUNDING_MSG  = 'Fund request has been sent successfully';
    
    const ERROR_EDIGITAL_SUCCESSFULL_CORP_BALANCE_CODE  = '000';
    const ERROR_EDIGITAL_SUCCESSFULL_CORP_BALANCE_MSG  = 'Corporate balance request successfully';
    
    const ERROR_EDIGITAL_INVALID_COMMENTS_CODE  = '175';
    const ERROR_EDIGITAL_INVALID_COMMENTS_MSG  = 'Invalid Comments';
    
    const ERROR_EDIGITAL_INVALID_FUNDING_TYPE_CODE  = '171';
    const ERROR_EDIGITAL_INVALID_FUNDING_TYPE_MSG  = 'Invalid Funding Type';
    

    const ERROR_EDIGITAL_TXN_PROCESS_CODE  = '172';
    const ERROR_EDIGITAL_TXN_PROCESS_MSG  = 'Transaction already refunded';
    
    //const ERROR_EDIGITAL_INVALID_ADDRESSLINE_CODE  = '173';
    //const ERROR_EDIGITAL_INVALID_ADDRESSLINE_MSG  = 'Invalid parameter AddressLine1';


    const ERROR_EDIGITAL_INVALID_CORPORATE_CODE_CODE  = '167';
    const ERROR_EDIGITAL_INVALID_CORPORATE_CODE_MSG  = 'Invalid Corporate Code';
    
    const ERROR_EDIGITAL_INVALID_LOCAL_CORPORATE_CODE  = '169';
    const ERROR_EDIGITAL_INVALID_LOCAL_CORPORATE_MSG  = 'Corporate Not registered under the program';
    
    const ERROR_EDIGITAL_INVALID_PARAMETER_CODE  = '168';
    const ERROR_EDIGITAL_INVALID_PARAMETER_MSG  = 'Invalid value in ';
 
    const ERROR_EDIGITAL_INVALID_ADDRESSLINE_CODE  = '405';
    const ERROR_EDIGITAL_INVALID_ADDRESSLINE_MSG  = 'Invalid parameter AddressLine1';
    
    const ERROR_EDIGITAL_INVALID_ACK_TXN_NO_CODE  = '410';
    const ERROR_EDIGITAL_INVALID_ACK_TXN_NO_MSG  = 'Invalid Parameter AckNo/TxnNo';
    
    const ERROR_EDIGITAL_INVALID_ACK_TRAN_NO_CODE  = '411';
    const ERROR_EDIGITAL_INVALID_ACK_TRAN_NO_MSG  = 'Invalid Parameter AckNo/TransactionRefNo';
   
    ###############
    //const ERROR_EDIGITAL_INVALID_CARD_PACK_ID_MSG  = 'Invalid parameter CardPackId';
    const ERROR_EDIGITAL_INVALID_CUSTOMER_CODE = '101';
    const ERROR_EDIGITAL_INVALID_CUSTOMER_MSG = 'Customer does not exist';
    
    const ERROR_EDIGITAL_MOBILE_USED_CODE = '102';
    const ERROR_EDIGITAL_MOBILE_USED_MSG = 'Mobile Number already in use';
    
    const ERROR_EDIGITAL_PAR_USED_CODE = '103';
    const ERROR_EDIGITAL_PAR_USED_MSG = 'Partner Reference Number already in use';
    
    const ERROR_EDIGITAL_EMAIL_USED_CODE = '104';
    const ERROR_EDIGITAL_EMAIL_USED_MSG = 'Email address already in use';
    
    const ERROR_EDIGITAL_TRAN_REF_NO_USED_CODE = '105';
    const ERROR_EDIGITAL_TRAN_REF_NO_USED_MSG = 'Transaction Reference Number already in use';
    
    const ERROR_EDIGITAL_BENE_ADDR_LONG_CODE = '186';
    const ERROR_EDIGITAL_BENE_ADDR_LONG_MSG = 'Beneficiary address is too long';
    
    const ERROR_EDIGITAL_LAND_LINE_MSG = 'Invalid parameter Landline';
    
    const ERROR_EDIGITAL_INVALID_CITY_CODE  = '412';
    const ERROR_EDIGITAL_CITY_MSG = 'Invalid parameter City';
    
    const ERROR_EDIGITAL_INVALID_STATE_CODE  = '413';
    const ERROR_EDIGITAL_STATE_MSG = 'Invalid parameter State';
    
//    const ERROR_EDIGITAL_BANK_DETAIL_CODE = '187';
//    const ERROR_EDIGITAL_BANK_DETAIL_MSG = 'Bank details are incomplete';
    

    const ERROR_EDIGITAL_INVALID_TITLE_CODE  = '401';

    const ERROR_EDIGITAL_INVALID_TITLE_MSG  = 'Invalid parameter Title';
    
    const ERROR_EDIGITAL_INVALID_GENDER_CODE  = '402';
    const ERROR_EDIGITAL_INVALID_GENDER_MSG  = 'Invalid parameter Gender';
    const ERROR_EDIGITAL_INVALID_MOBILE_2_CODE  = '403';
    const ERROR_EDIGITAL_INVALID_MOBILE_2_MSG  = 'Invalid parameter Mobile2';
    const ERROR_EDIGITAL_INVALID_MOTHER_MAIDEN_NAME_CODE  = '404';
    const ERROR_EDIGITAL_INVALID_MOTHER_MAIDEN_NAME_MSG  = 'Invalid parameter MotherMaidenName';
    
    const ERROR_EDIGITAL_INVALID_ADDRESSLINE2_CODE  = '406';
    const ERROR_EDIGITAL_INVALID_ADDRESSLINE2_MSG  = 'Invalid parameter AddressLine2';
    
    const ERROR_EDIGITAL_ADDR_LINE_1_LONG_CODE = '326';
    const ERROR_EDIGITAL_ADDR_LINE_1_LONG_MSG = 'Beneficiary Address line1 too long';
    
    const ERROR_EDIGITAL_INVALID_ORG_TXN_REF_CODE = '407';
    const ERROR_EDIGITAL_INVALID_ORG_TXN_REF_MSG = 'Invalid parameter OriginalTransactionRefNo';
    
   
    const ERROR_EDIGITAL_INVALID_QUERY_REFUND_CODE = '327';
    const ERROR_EDIGITAL_INVALID_QUERY_REFUND_MSG = 'Please provide either QueryReqNo or OriginalTransactionRefNo';
    
    const ERROR_EDIGITAL_INVALID_QUERY_REQ_NUM_MSG  = 'Invalid QueryReqNo';
    const ERROR_EDIGITAL_INVALID_QUERY_REQ_NUM_CODE  = '319';
        
    const ERROR_EDIGITAL_INVALID_BENE_NOT_FOUND_MSG  = 'Invalid either QueryReqNo or BeneficiaryCode';
    const ERROR_EDIGITAL_INVALID_BENE_NOT_FOUND_CODE  = '318';
    
    const ERROR_EDIGITAL_INVALID_BENEFICIARY_LIST_MSG  = 'Customer does not have any beneficiary';
    const ERROR_EDIGITAL_INVALID_BENEFICIARY_LIST_CODE  = '321';
     
    const ERROR_EDIGITAL_WRONG_NON_KYC_CUST_REG_CODE = '328';
    const ERROR_EDIGITAL_WRONG_NON_KYC_CUST_REG_MSG ='Only Non-KYC customer registration is allowed for this program';
   const ERROR_EDIGITAL_WRONG_VOUCHER_NUMBER_MSG ='Voucher code already in use';
   const ERROR_EDIGITAL_WRONG_WALLET_TRANSFER_MSG = 'For Wallet transfer product should be same';
   
   const ERROR_EDIGITAL_INVALID_IDENTIFIRE_TYPE_CODE = '408';
   const ERROR_EDIGITAL_INVALID_IDENTIFIRE_TYPE_MSG  = 'Invalid parameter IdentifierType';
   const ERROR_EDIGITAL_INVALID_FUND_TRANSFER_TO_WALLET_MSG  = 'Funds can not be tranferred to same customer';
   const ERROR_EDIGITAL_INVALID_FUND_TRANSFER_TO_PROMO_WALLET_MSG  = 'Funds can not be tranferred to promo wallet';
   const ERROR_EDIGITAL_INVALID_FUND_TRANSFER_FROM_PROMO_WALLET_MSG  = 'Funds can not be tranferred from promo wallet';
    const ERROR_EDIGITAL_INVALID_BENEMOBILE_2_MSG  = 'Beneficiary mobile2 is not valid';
    
 
 
    const ERROR_EDIGITAL_WRONG_VOUCHER_NUMBER_CODE ='200';
    
//    const ERROR_EDIGITAL_DATA_MISSING_MSG = 'Data missing for cardload';
//    const ERROR_EDIGITAL_DATA_MISSING_CODE = '201';
    
    const ERROR_EDIGITAL_CARDHOLDER_NOT_FOUND_MSG = 'Cardholder not found';
    
    const ERROR_EDIGITAL_TXN_ALREADY_REVERSED_MSG = 'Requested original txn number is already reversed';
    const ERROR_EDIGITAL_TXN_ALREADY_REVERSED_CODE = '202';
    
    const ERROR_EDIGITAL_INVALID_ORIGINAL_TXN_NUMBER_MSG = 'Invalid original txn number';
    const ERROR_EDIGITAL_INVALID_ORIGINAL_TXN_NUMBER_CODE = '203';
    
    const ERROR_EDIGITAL_INVALID_REVERSAL_MODE_MSG = 'Invalid mode for reversal request';
    const ERROR_EDIGITAL_INVALID_REVERSAL_MODE_CODE = '204';
    
    const ERROR_EDIGITAL_TXN_EXPIRED_MSG = 'Requested transaction has been expired';
    const ERROR_EDIGITAL_TXN_EXPIRED_CODE = '205';
    
    const ERROR_EDIGITAL_INVALID_REVERSAL_AMOUNT_MSG = 'Invalid amount value';
    const ERROR_EDIGITAL_INVALID_REVERSAL_AMOUNT_CODE = '206';
    
    const ERROR_EDIGITAL_INVALID_REVERSAL_TXN_NUMBER_MSG = 'Reversal transaction number is not valid';
    const ERROR_EDIGITAL_INVALID_REVERSAL_TXN_NUMBER_CODE = '207';
    
    const ERROR_EDIGITAL_INSUFFICIENT_DATA_CARDLOAD_MSG = 'Insufficient data for validating cardLoad';
    const ERROR_EDIGITAL_INSUFFICIENT_DATA_CARDLOAD_CODE = '208';
    
    const ERROR_EDIGITAL_INVALID_AGENT_BALANCE_MSG = 'Could not find agent balance';
    const ERROR_EDIGITAL_INVALID_AGENT_BALANCE_CODE = '209';
    
    const ERROR_EDIGITAL_AGENT_INSUFFICIENT_FUND_CODE = '210';
    
    const ERROR_EDIGITAL_INVALID_CORPORATE_BALANCE_MSG = '"Could not find corporate balance"';
    const ERROR_EDIGITAL_INVALID_CORPORATE_BALANCE_CODE = '211';
    
    const ERROR_EDIGITAL_AMOUNT_LESS_PER_TXN_CODE = '212';
    const ERROR_EDIGITAL_AMOUNT_EXCEEDS_PER_TXN_CODE = '213';
    const ERROR_EDIGITAL_MAX_TXN_LIMIT_EXCEEDS_CODE = '214';
    const ERROR_EDIGITAL_MAX_AMOUNT_LIMIT_EXCEEDS_CODE = '215';
    
    const ERROR_EDIGITAL_INVALID_PURSE_MASTER_ID_MSG = 'Invalid purse master detail';
    const ERROR_EDIGITAL_INVALID_PURSE_MASTER_ID_CODE = '216';
    
    const ERROR_EDIGITAL_PURSE_MASTER_NOT_FOUND_MSG = 'No record found with this purse master detail';
    const ERROR_EDIGITAL_PURSE_MASTER_NOT_FOUND_CODE = '217';
    
    const ERROR_EDIGITAL_PURSE_MAX_BALANCE_CODE = '218';
    
    const ERROR_EDIGITAL_INVALID_PRODUCT_ID_MSG = 'No record found with this product';
    
    const ERROR_EDIGITAL_CUSTOMER_MAX_BALANCE_CODE = '219';
    
    const ERROR_EDIGITAL_INVALID_BANK_ID_MSG = 'Invalid bank id';    
    
    const ERROR_EDIGITAL_INSUFFICIENT_DATA_LOAD_WALLET_MSG = 'Insufficient data for loading wallet';
    const ERROR_EDIGITAL_INSUFFICIENT_DATA_LOAD_WALLET_CODE = '221';
    
//    const ERROR_EDIGITAL_INVALID_WALLETCODE_MSG = 'Wallet code is not valid';
//    const ERROR_EDIGITAL_INVALID_WALLETCODE_CODE = '222';
    
    const ERROR_EDIGITAL_INVALID_CARD_TYPE_MSG = 'Card type corporate ID validation failed';
    const ERROR_EDIGITAL_INVALID_CARD_TYPE_CODE = '224';
    
    const ERROR_EDIGITAL_VOUCHER_FEATURE_NOT_PERMITTED_MSG = 'Expiry/Voucher feature is not permitted for this wallet';
    const ERROR_EDIGITAL_VOUCHER_FEATURE_NOT_PERMITTED_CODE = '225';
    
    const ERROR_EDIGITAL_VOUCHER_LIMIT_EXCEED_CODE = '226';
    const ERROR_EDIGITAL_INVALID_MODE_CODE = '227';
    
    const ERROR_EDIGITAL_TXN_FAILED_MSG = 'Transaction failed due to some technical problem, please try again later.';
    const ERROR_EDIGITAL_TXN_FAILED_CODE = '228';
    
    const ERROR_EDIGITAL_SOCKET_AMOUNT_CODE = '229';
    
    const ERROR_EDIGITAL_INVALID_CRN_MSG = 'Invalid CRN number';
    const ERROR_EDIGITAL_INVALID_CRN_CODE = '230';
    
    const ERROR_EDIGITAL_INVALID_TXN_ID_MSG = 'Invalid transaction id';
    const ERROR_EDIGITAL_INVALID_TXN_ID_CODE = '231';
    
    
    public static function voucherLimitExceed() {
        return 'This transaction has exceeded voucher limit of '.ALLOW_VOUCHER_LIMIT;
    } 
    
    public static function getMode($mode) {
        return 'Invalid Mode: '.$mode;
    }
    
    public static function getSocketAmountMsg($amount) {
        return "Invalid amount passed ". $amount;
    }
    
     
    const OTP_INVALID_RESPONSE_CODE = '115';
    const OTP_INVALID_RESPONSE_MSG = 'Invalid OTP'; 
    const ERROR_EDIGITAL_BENE_CUSTOMER_NOT_FOUND_CODE = '252';
    const ERROR_EDIGITAL_BENE_CUSTOMER_NOT_FOUND_MSG = 'Active beneficiary not found';
    const ERROR_EDIGITAL_WRONG_WALLET_TRANSFER_CODE = '253';
    const ERROR_EDIGITAL_PRODUCT_ID_MISS_CODE = '254'; 
    const ERROR_EDIGITAL_PRODUCT_ID_MISS_MSG = 'Invalid ProductCode'; 
    const ERROR_EDIGITAL_INVALID_FUND_TRANSFER_FROM_PROMO_WALLET_CODE = '255';
    const ERROR_EDIGITAL_INVALID_FUND_TRANSFER_TO_PROMO_WALLET_CODE = '256';
    const ERROR_EDIGITAL_INVALID_FUND_TRANSFER_TO_WALLET_CODE = '257';
    const ERROR_EDIGITAL_WALLET_PRD_NOT_SAME_CODE = '258'; 
    const ERROR_EDIGITAL_WALLET_PRD_NOT_SAME_MSG = 'For Wallet transfer product should be same bank'; 
    const WALLET_TRANSFER_FAILURE_CODE = '259';
    const WALLET_TRANSFER_FAILURE_MSG = 'Wallet transfer not successful';
    
    
    
//    const ERROR_REMITTANCE_SMS_EXCEPTION_CODE = '281';
//    const ERROR_TRANSFER_SMS_EXCEPTION_CODE = '282';
//    const ERROR_CARD_UPDATE_SMS_EXCEPTION_CODE = '283' ; 
//    const ERROR_CARD_BENE_REG_SMS_EXCEPTION_CODE = '284';
//    const ERROR_TRANS_AUTH_SMS_EXCEPTION_CODE = '285' ;
//    const ERROR_REMITTANCE_AUTH_SMS_EXCEPTION_CODE = '286';
//    const ERROR_LOAD_AUTH_SMS_EXCEPTION_CODE = '287' ;
//    const ERROR_BENE_REG_AUTH_SMS_EXCEPTION_CODE = '288';
//    const ERROR_CUST_EDIT_AUTH_SMS_EXCEPTION_CODE = '289';
//    const ERROR_CARD_ACTIVATION_AUTH_SMS_EXCEPTION_CODE = '290';
    
    const ERROR_UNABLE_SMS_EXCEPTION_CODE = '281';
    const ERROR_UNABLE_SMS_EXCEPTION_MSG = 'Unable to send SMS';
    
    // Customer Registration 
    
    const ERROR_INVALID_CARD_FAILURE_CODE = '260';
    const ERROR_INVALID_CARD_FAILURE_MSG = 'Invalid card details provided';
    const ERROR_INVALID_DATA_ADD_CUST_FAILURE_CODE = '261';
    const ERROR_INVALID_DATA_ADD_CUST_FAILURE_MSG = 'Data missing for adding customer';
    const ERROR_UNABLE_GENERATE_SHMART_CRN_FAILURE_CODE = '262';
    const ERROR_UNABLE_GENERATE_SHMART_CRN_FAILURE_MSG = 'Unable to generate Shmart CRN';
    const ERROR_INVALID_DATA_FOR_REMITTER_REG_FAILURE_CODE = '263';
    const ERROR_INVALID_DATA_FOR_REMITTER_REG_FAILURE_MSG = 'Insufficient data found for remitter regn. fee transaction';
    const ERROR_UNABLE_GENERATE_TXN_CODE_FOR_REMITTER_REG_CODE = '264';
     const ERROR_UNABLE_GENERATE_TXN_CODE_FOR_REMITTER_REG_MSG = 'Transaction code for ratnakar remitter regn fee could not be generated at this time. Please try later.';
     const ERROR_UNABLE_GENERATE_TXN_CODE_FAILURE_CODE = '265';
     const ERROR_UNABLE_GENERATE_TXN_CODE_FAILURE_MSG = 'Unable to generate transaction code';
     const ERROR_CUSTOMER_REGISTRATION_FAIL_CODE = '266';
     const ERROR_CUSTOMER_REGISTRATION_FAIL_MSG = 'Unable to register customer';
      
     
    // UnBlock Customer 
    const ERROR_UNBLOCK_FAILED_RESPONSE_CODE = '268';
    const ERROR_UNBLOCK_FAILED_RESPONSE_MSG = 'Unable to unblock the account';
    
    // Block Customer 
    const ERROR_BLOCK_FAILED_RESPONSE_CODE = '269';
    const ERROR_BLOCK_FAILED_RESPONSE_MSG = 'Unable to block the account';
    
    // Balance Enquiry 
    const ERROR_CUSTOMER_BAL_ENQ_FAILED_RESPONSE_CODE = '270';
    const ERROR_CUSTOMER_BAL_ENQ_FAILED_RESPONSE_MSG = 'Unable to check the customer balance';
    // Wallet Balance Enquiry
    const ERROR_WALLET_BAL_ENQ_FAILED_RESPONSE_CODE = '271';
    const ERROR_WALLET_BAL_ENQ_FAILED_RESPONSE_MSG = 'Unable to check the wallet balance';
    
    //QueryRegistrationRequest
    
    const ERROR_INVALID_PRODUCT_CONST_FAILED_RESPONSE_CODE = '272';
    const ERROR_INVALID_PRODUCT_CONST_FAILED_RESPONSE_MSG = 'Product constant missing';
    const ERROR_EDIGITAL_WRONG_ACK_TRAN_NO_CODE  = '273';
    const ERROR_EDIGITAL_WRONG_ACK_TRAN_NO_MSG  = 'Invalid AckNo/TransactionRefNo';
    const ERROR_QUERY_REGISTRATION_FAIL_CODE  = '274';
    const ERROR_QUERY_REGISTRATION_FAIL_MSG  = 'Unable to get query registration record';
    
    // Mini Statement
    const ERROR_MINISTT_FAILED_RESPONSE_CODE = '275';
    const ERROR_MINISTT_FAILED_RESPONSE_MSG = 'Unable to generate the statement'; 
    
    //Update Customer
    const ERROR_INVALID_DATA_EDIT_CUSTOMER_MSG = 'Data missing for edit cardholder';
    const ERROR_INVALID_DATA_EDIT_CUSTOMER_CODE = '301';
    
    const ERROR_INVALID_PRODUCT_EDIT_CUSTOMER_MSG = 'Product missing for edit cardholder';
    const ERROR_INVALID_PRODUCT_EDIT_CUSTOMER_CODE = '302';
    
    const ERROR_INVALID_CUSTOMER_IDENTIFIER_EDIT_CUSTOMER_MSG = 'Customer identifier value is missing for edit cardholder';
    const ERROR_INVALID_CUSTOMER_IDENTIFIER_EDIT_CUSTOMER_CODE = '303';

    const ERROR_EDIGITAL_CUSTOMER_UPDATION_FAIL_MSG = 'Unable to update customer';
    const ERROR_EDIGITAL_CUSTOMER_UPDATION_FAIL_CODE = '304';
    
    //Deactivate Bene
    const ERROR_EDIGITAL_INVALID_BENEFICIARY_CODE  = '276';
    const ERROR_EDIGITAL_INVALID_BENEFICIARY_CODE_MSG  = 'Invalid BeneficiaryCode';
    
    const ERROR_DEACTIVE_BENEFICIARY_FAIL_CODE = '277';
    const ERROR_DEACTIVE_BENEFICIARY_FAIL_MSG = 'Unable to deactivate beneficiary record';
    
    // Query Bene
    const ERROR_QUERY_BENEFICIARY_FAILURE_RESPONSE_CODE = '278';
    const ERROR_QUERY_BENEFICIARY_FAILURE_RESPONSE_MSG = 'Query beneficiary feature is not allowed for this product';
    const ERROR_EDIGITAL_WRONG_ACK_TRAN_NO_BENE_CODE  = '279';
    const ERROR_EDIGITAL_WRONG_ACK_TRAN_NO_BENE_MSG  = 'Invalid AckNo/TransactionRefNo/BeneficiaryCode';
    
    //Remittance Transaction
    const ERROR_EDIGITAL_REMITTANCE_FEATURE_DISABLED_MSG = 'Remittance transaction feature is disabled for this product';
    const ERROR_EDIGITAL_REMITTANCE_FEATURE_DISABLED_CODE = '305';
    
    const ERROR_EDIGITAL_REMITTANCE_TXN_BENEFICIARY_FAILURE_MSG = 'Unable to get beneficiary record';
    const ERROR_EDIGITAL_REMITTANCE_TXN_BENEFICIARY_FAILURE_CODE = '306';
    
  //  const ERROR_EDIGITAL_REMITTANCE_TXN_FAILURE_MSG = 'Remittance transaction not successful';
  //  const ERROR_EDIGITAL_REMITTANCE_TXN_FAILURE_CODE = '307';
    
    const ERROR_EDIGITAL_REMITTANCE_NOT_ALLOWED_MSG = 'Remittance not allowed';
    const ERROR_EDIGITAL_REMITTANCE_NOT_ALLOWED_CODE = '308';
    
    const ERROR_EDIGITAL_INSUFFICIENT_DATA_REMITTANCE_MSG = 'Insufficient data for validating Remittance';
    const ERROR_EDIGITAL_INSUFFICIENT_DATA_REMITTANCE_CODE = '309';
    
    const ERROR_EDIGITAL_INSUFFICIENT_REMITTANCE_CUST_BALANCE_CODE = '310';
    
    public static function getRemitInsufficientFundMsg($custBalance, $deductedAmt) {
        return "Customer does not have sufficient fund. Available Balance: ".$custBalance." Amount to be deducted: ".$deductedAmt;
    }
    
    const ERROR_EDIGITAL_AMT_EXCEED_PER_TXN_MSG = 'Amount exceeds Max. Amount per txn';
    const ERROR_EDIGITAL_AMT_EXCEED_PER_TXN_CODE = '311';
    
    const ERROR_EDIGITAL_REMITTER_LIMIT_FLAG_CODE = '312';
    
    public static function getRemitterLimitFlagMsg($period, $txt, $amtMax, $total, $amount) {
        return "Remittance Amount will exceed Max. Amount of ".$period." Remittance Allowed for ".$txt.". Max ".$period." Remittance Allowed: ".Util::numberFormat($amtMax).". Amount of Remittance already done: ".Util::numberFormat($total).". Amount tried: ".Util::numberFormat($amount);
    }

    // Query Bene List
    const ERROR_QUERY_BENEFICIARY_LIST_FAILURE_RESPONSE_CODE = '280';
    const ERROR_QUERY_BENEFICIARY_LIST_FAILURE_RESPONSE_MSG = 'Query beneficiary list feature is not allowed for this product';
    const ERROR_QUERY_BENEFICIARY_LIST_FAIL_CODE = '291';
    const ERROR_QUERY_BENEFICIARY_LIST_FAIL_MSG = 'Unable to get query beneficiary list record';
    // Query Transaction
    const ERROR_EDIGITAL_WRONG_ACK_TXN_NO_CODE  = '292';
    const ERROR_EDIGITAL_WRONG_ACK_TXN_NO_MSG  = 'Invalid AckNo/TxnNo';
    
    const ERROR_QUERY_TRANSACTION_REQUEST_FAILURE_CODE = '293';
    const ERROR_QUERY_TRANSACTION_REQUEST_FAILURE_MSG = 'Query transaction not successful';

    const OTP_FAILED_RESPONSE_CODE = '294';
    const OTP_FAILED_RESPONSE_MSG = 'Unable to process OTP request';
 
    //BeneRegistration : - 
    const ERROR_EDIGITAL_NOT_VALID_TITLE_CODE = '351';
    const ERROR_EDIGITAL_NOT_VALID_TITLE_MSG = 'Title is not valid';
    const ERROR_EDIGITAL_ADDRESH_TOO_LONG_CODE = '352';
    const ERROR_EDIGITAL_ADDRESH_TOO_LONG_MSG = 'Beneficiary address is too long';
    const ERROR_EDIGITAL_BANK_DETAILS_INCOMPLETE_CODE = '353';
    const ERROR_EDIGITAL_BANK_DETAILS_INCOMPLETE_MSG = 'Bank details are incomplete'; 
    const BENEFICIARY_REGISTRATION_FAILED_RESPONSE_CODE = '354';
    const BENEFICIARY_REGISTRATION_FAILED_RESPONSE_MSG = 'Beneficiary registration feature is disabled for this product';
    const BENE_WITH_SAME_ACCOUNT_RESPONSE_CODE = '355';
    const BENE_WITH_SAME_ACCOUNT_RESPONSE_MSG = 'Beneficiary with same bank account no. exists';
   // const ERROR_EDIGITAL_TXN_REF_ALREADY_EXIST_CODE = '356';
  //  const ERROR_EDIGITAL_TXN_REF_ALREADY_EXIST_MSG = 'Transaction Referance Number is already exist';
    const ERROR_BENE_REGISTRATION_FAIL_CODE = '357';
    const ERROR_BENE_REGISTRATION_FAIL_MSG = 'Unable to register beneficiary';
    
    //QueryRemittanceRequest : - 
    const QUERY_REMITTANCE_FAILURE_RESPONSE_CODE = '358';
    const QUERY_REMITTANCE_FAILURE_RESPONSE_MSG = 'Query remittance feature is not allowed for this product';
    
    //Wallet Transfer
    const ERROR_EDIGITAL_REMITTER_INVALID_WALLET_CODE = '300';
    const ERROR_EDIGITAL_REMITTER_INVALID_WALLET_MSG = 'Remitter wallet code is not valid';
    
    const QUERY_TRANSFER_FAILURE_CODE = '359';
    const QUERY_TRANSFER_FAILURE_MSG = 'Query transfer not successful';
    const DEBIT_TRANSACTION_REQUEST_FAILURE_CODE = '360';
    const DEBIT_TRANSACTION_REQUEST_FAILURE_MSG = 'Debit transaction request not successful';
    const ERROR_EDIGITAL_TXN_NOT_FOUND_CODE = '361';
    const ERROR_EDIGITAL_TXN_NOT_FOUND_MSG = 'Transaction not found';
    const ERROR_EDIGITAL_REFUND_NOT_ALLOWED_CODE = '362';
    const ERROR_EDIGITAL_REFUND_NOT_ALLOWED_MSG = 'Refund transaction is not allowed';
    const ERROR_EDIGITAL_INVALID_REFUND_AMOUNT_CODE  = '363';
    const ERROR_EDIGITAL_INVALID_REFUND_AMOUNT_MSG  = 'Invalid Amount';
    const ERROR_EDIGITAL_INSUFFICIENT_DATA_REMIT_REFUND_CODE  = '364';
    const ERROR_EDIGITAL_INSUFFICIENT_DATA_REMIT_REFUND_MSG  = 'Insufficient data for remittance refunds';
    const ERROR_EDIGITAL_TXNCODE_NOT_GENERATED_CODE  = '365';
    const ERROR_EDIGITAL_TXNCODE_NOT_GENERATED_MSG  = 'Transaction code for refund amount could not be generated at this time. Please try later.';
    const ERROR_EDIGITAL_REFUND_TXN_NOT_COMPLETED_CODE  = '366';
    const ERROR_EDIGITAL_REFUND_TXN_NOT_COMPLETED_MSG  = 'Refund transaction not completed.';
    const ERROR_EDIGITAL_REMITTANCE_REQ_DATA_MISSING_CODE  = '367';
    const ERROR_EDIGITAL_REMITTANCE_REQ_DATA_MISSING_MSG  = 'Insufficient data for remittance request';
   // const ERROR_EDIGITAL_REMITTANCE_REF_DATA_MISSING_CODE  = '368';
   // const ERROR_EDIGITAL_REMITTANCE_REF_DATA_MISSING_MSG  = 'Remittance Refund data not found!';
    
    
    
  //  const ERROR_REFUND_TRANSACTION_SMS_EXCEPTION_CODE = '369';
  
    const ERROR_EDIGITAL_INSUFFICIENT_DATA_KOTAK_REMITTANCE_CODE  = '370';
    const ERROR_EDIGITAL_INSUFFICIENT_DATA_KOTAK_REMITTANCE_MSG  = 'Insufficient data for validating kotak remittance';
    const ERROR_EDIGITAL_AGENT_NOT_HAVE_SUFFICIENT_FUND_CODE  = '371';
    public static function getAgentnotHaveSuffienctFundMsg($agentAmt,$minAgentBalReqd,$amount) {
        return "Agent does not have sufficient fund. Agent Balance: ".Util::numberFormat($agentAmt).". Minimum Balance Reqd.: ".Util::numberFormat($minAgentBalReqd).". Amount to be deducted: ".Util::numberFormat($amount);
    }
    const ERROR_EDIGITAL_AMOUNT_LESS_THAN_MIN_TXN_CODE  = '372';
    public static function getAmountLessthanMinTxnMsg($txt,$limitOutMinTxn,$amount) {
        return  "Amount less than Min. Per Txn for ".$txt.". Min Per Txn Amount Allowed: ".Util::numberFormat($limitOutMinTxn).". Amount tried: ".Util::numberFormat($amount);
    }
    const ERROR_EDIGITAL_AMOUNT_EXCEED_MAX_TXN_CODE  = '373';
    public static function getAmountExceedMaxTxnMsg($txt,$limitOutMaxTxn,$amount) {
        return  "Amount exceeds Max. Per Txn for ".$txt.". Max Per Txn Amount Allowed: ".Util::numberFormat($limitOutMaxTxn).". Amount tried: ".Util::numberFormat($amount);
    }
    const ERROR_EDIGITAL_REMITTANCE_AMOUNT_EXCEED_MAX_AMOUNT_CODE  = '374';
    public static function getRemittanceAmountExceedMaxAmountPeriodMsg($period,$txt,$amtMax) {
        return "Remittance Amount will exceed Max. Amount of ".$period." Remittance Allowed for ".$txt.". Max ".$period." Remittance Allowed: ".Util::numberFormat($amtMax);
    }
    const GENERATE_OTP_FAILED_RESPONSE_CODE  = '375';
    const GENERATE_OTP_FAILED_RESPONSE_MSG = 'Generate OTP feature is disabled for this product';
    
    
//    const CUSTOMER_NOT_FOUND_CODE = '376';
//    const CUSTOMER_NOT_FOUND_MSG = 'Active Customer Not Found';
    
    //Customer does not exist
    
    
    
    
    
    
     
    const ERROR_EDIGITAL_INVALID_MOBILE_MSG = 'Invalid mobile number';
    const ERROR_EDIGITAL_INVALID_MOBILE_CODE = '313';
    
    const ERROR_EDIGITAL_MOBILE_NOT_REGISTERED_MSG = 'Mobile not registered';
    const ERROR_EDIGITAL_MOBILE_NOT_REGISTERED_CODE = '314';
    
    const ERROR_EDIGITAL_INVALID_UNICODE_MSG = 'Unicode not found for update';
    const ERROR_EDIGITAL_INVALID_UNICODE_CODE = '315';
    
   // const ERROR_REMITTANCE_STATIC_OTP_SMS_EXCEPTION_CODE = '316';
    const ERROR_PINCODE_NOT_EXIST_CODE = '322';
    const ERROR_PINCODE_NOT_EXIST_MSG = 'Pincode does not exist'; 
   // const QUERY_REGISTRATION_FAIL_CODE = '317';
   // const QUERY_REGISTRATION_FAIL_MSG = 'Unable to get query record';
     
    const QUERY_BENEFICIARY_FAIL_CODE = '320';
    const QUERY_BENEFICIARY_FAIL_MSG = 'Unable to get query beneficiary record';
    
    const ERROR_EDIGITAL_MOTHER_MAIDEN_NAME_MSG = 'Mother Maiden Name is not valid';
    const ERROR_EDIGITAL_MOTHER_MAIDEN_NAME_CODE = '329';
    
    const ERROR_TRANSACTION_REQUEST_FAILURE_CODE = '323';
    const ERROR_TRANSACTION_REQUEST_FAILURE_MSG = 'Transaction request not successful';
    
    const ERROR_QUERY_REMITTANCE_FAILURE_CODE = '324';
    const ERROR_QUERY_REMITTANCE_FAILURE_MSG = 'Query remittance not successful';
    
    const ERROR_DEACTIVE_BENEFICIARY_FAILURE_RESPONSE_CODE = '325';
    const ERROR_DEACTIVE_BENEFICIARY_FAILURE_RESPONSE_MSG = 'Deactivate beneficiary feature is not allowed for this product';
    
    const ERROR_INSUFFICIENT_BALANCE_CODE = '295';
 
    const ERROR_REMITTANCE_TRANSACTION_FAILURE_CODE = '170';
    const ERROR_REMITTANCE_TRANSACTION_FAILURE_MSG = 'Remittance transaction not successful';
    
    const REMITTANCE_TRANSACTION_NO_RESPONSE_CODE = '174';
    const REMITTANCE_TRANSACTION_NO_RESPONSE_MSG = 'Hold/No response';
    
    const ERROR_EDIGITAL_BENE_WALLET_CODE_NOT_VALID_CODE = '369';
    const ERROR_EDIGITAL_BENE_WALLET_CODE_NOT_VALID_MSG = 'Beneficiary wallet code is not valid';
    
    const ERROR_EDIGITAL_INSUFFICIENT_DATA_TO_VALIDATE_CODE = '376';
    const ERROR_EDIGITAL_INSUFFICIENT_DATA_TO_VALIDATE_MSG = 'Insufficient data for validating wallet transfer';
    
    const ERROR_EDIGITAL_MAX_BALANCE_ALLOWED_EXCEED_CODE = '377';
    const ERROR_EDIGITAL_MAX_BALANCE_ALLOWED_EXCEED_MSG = 'Max balance allowed exceeded';
    
    
    const ERROR_EDIGITAL_INSUFFICIENT_DATA_TO_FAIL_REMITT_CODE = '378';
    const ERROR_EDIGITAL_INSUFFICIENT_DATA_TO_FAIL_REMITT_MSG = 'Insufficient data for failed remittance';
     
    
    //Insufficient Data for initiating Transfer
    
    const ERROR_INSUFFICIENT_DATA_AUTHENTICATION_MSG = 'Insufficient Data for Authentication';
    const ERROR_INSUFFICIENT_DATA_AUTHENTICATION_CODE = '220';
    
    const ERROR_PARTNER_BAL_ENQ_FAILED_RESPONSE_CODE = '296';
    const ERROR_PARTNER_BAL_ENQ_FAILED_RESPONSE_MSG = 'Unable to get partner balance';

    const CRN_ALREADY_ASSIGNED_CODE = '112';
    const CRN_ALREADY_ASSIGNED_MSG = 'CRN already assigned to the Customer';
    
    const ERROR_EDIGITAL_INVALID_CUSTOMER_CARD_Code = '109';
    const ERROR_EDIGITAL_INVALID_CUSTOMER_CARD_MSG = 'Customer does not have card';
    
    const ERROR_CARD_BLOCK_FAILED_RESPONSE_CODE = '110';
    const ERROR_CARD_BLOCK_FAILED_RESPONSE_MSG = 'Unable to block the card';
    const BLOCK_CARD_SUCCSSES_RESPONSE_MSG = 'Successfully blocked the card';
    
    const ERROR_NO_AGENT_VIRTUAL_BAL_CODE  = '472';
    const ERROR_NO_AGENT_VIRTUAL_BAL_MSG  = 'Could not find Agent virtual balance';
    
    const ERROR_CARD_MAPPING_FAIL_CODE = '114';
    const ERROR_CARD_MAPPING_FAIL_MSG = 'Unable to Map Card';
    
    
     
    const ERROR_BLOCK_AMOUNT_FAIL_CODE = '473';
    const ERROR_BLOCK_AMOUNT_FAIL_MSG = 'Unable to block Amount';
    const ERROR_UNBLOCK_AMOUNT_FAIL_CODE = '474';
    const ERROR_UNBLOCK_AMOUNT_FAIL_MSG = 'Unable to Unblock Amount'; 
    const ERROR_CLAIM_AMOUNT_FAIL_CODE = '475';
    const ERROR_CLAIM_AMOUNT_FAIL_MSG = 'Unable to Claim Amount'; 
    
    const ERROR_TXN_IDENTIFIER_TYPE_MANDATORY_CODE = '476';
    const ERROR_TXN_IDENTIFIER_TYPE_MANDATORY_MSG = 'TxnIndentifierType is mandatory'; 
    
    const ERROR_INCORRECT_CUST_DETAIL_CODE = '477';
    const ERROR_INCORRECT_CUST_DETAIL_MSG = 'Incorrect Customer Detail'; 
    
    
    const ERROR_INCORRECT_AMOUNT_CODE = '478';
    const ERROR_INCORRECT_AMOUNT_MSG = 'Incorrect Amount'; 
    
    const ERROR_INCORRECT_TXNTYPE_CODE = '479';
    const ERROR_INCORRECT_TXNTYPE_MSG = 'Incorrect TxnType'; 
     
    
    //
}
