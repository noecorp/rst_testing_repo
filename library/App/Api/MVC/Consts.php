<?php

/**
 * Constants
 * MVC Api related constants
 *
 * @author Vikram
 * @company Transerv
 */
class App_Api_MVC_Consts
{

    //Api NAME
    const API_CUSTOMERAUTH = "AuthenticationRequest";

    /* API PARAM NAMES */
    //Customer Authentication Param
    const API_CUSTOMERAUTH_PARAM_MESSAGEID              = "MessageID";
    const API_CUSTOMERAUTH_PARAM_MESSAGEID_INVALID_MSG  = "Invalid Message id provided";
    const API_CUSTOMERAUTH_PARAM_MESSAGEID_INVALID_CODE = "2";
    
    const API_CUSTOMERAUTH_PARAM_PAN                    = "PAN";
    const API_CUSTOMERAUTH_PARAM_PAN_INVALID_MSG        = "Invalid PAN number provided";
    const API_CUSTOMERAUTH_PARAM_PAN_INVALID_CODE       = "2";
    
    const API_CUSTOMERAUTH_PARAM_AMOUNT                 = "Amount";
    const API_CUSTOMERAUTH_PARAM_AMOUNT_INVALID_MSG     = "Invalid amount provided";
    const API_CUSTOMERAUTH_PARAM_AMOUNT_INVALID_CODE    = "2";
    
    const API_CUSTOMERAUTH_PARAM_EXPIRYDATE             = "ExpiryDate";
    const API_CUSTOMERAUTH_PARAM_EXPIRYDATE_INVALID_MSG = "Invalid expiry date provided";
    const API_CUSTOMERAUTH_PARAM_EXPIRYDATE_INVALID_CODE= "2";
    
    const API_CUSTOMERAUTH_PARAM_OTP                    = "OTP";
    const API_CUSTOMERAUTH_PARAM_OTP_INVALID_MSG        = "Invalid OTP provided";
    const API_CUSTOMERAUTH_PARAM_OTP_INVALID_CODE       = "2";
    
    

}