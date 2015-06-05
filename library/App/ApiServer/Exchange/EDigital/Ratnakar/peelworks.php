<?php

/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_EDigital_Ratnakar_peelworks extends App_ApiServer_Exchange_EDigital_Ratnakar {

    const TP_ID = TP_PEW_GPR_ID;

    public $_soapServer;

    public function __construct($server) {
        parent::__construct($server);
        $this->_soapServer = $server;
        $this->setTP(self::TP_ID);
        $this->setProductConstant(PRODUCT_CONST_RAT_GPR);
        $this->setBankProductConstant(BANK_RATNAKAR_GENERIC_GPR);
        $this->setAgentConstant(RBL_PEW_CORP_ID);
        $this->setManageTypeConstant(CORPORATE_MANAGE_TYPE);
        $this->setLoadExpiryConstant(LOAD_FALSE);
        $this->setOTPRequestConstant(OTP_REQUEST_TRUE);
    }

    public function BalanceEnquiryRequest() {
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);

            if (!isset($resp->SessionID) || !$this->isLogin((string) $resp->SessionID)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }
            $corpcode = (string) trim($resp->CorporateCode); 
            Validator_Ratnakar::productcodeValidation(trim($resp->ProductCode), $this->getProductConstant());
            Validator_Ratnakar::corporatecodeValidation($corpcode);
            
            //Check if Corporate is under Peelwork Regional
            $peelWorksCorpID = $this->getAgentConstant();
            
            $objCorporate = new Corporates();
            // Get Corporate info
            $corpinfo = $objCorporate->findByCorporateCode($corpcode); 
            if(empty($corpinfo)){
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CORPORATE_CODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CORPORATE_CODE_CODE);
            }
            
            $objectRelation = new ObjectRelations();
            $localCorp = $objectRelation->getToObjectDetails($peelWorksCorpID,$corpinfo['id'], REGIONAL2LOCAL);

            if(empty($localCorp)){
               throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOCAL_CORPORATE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOCAL_CORPORATE_CODE);
            }
            
            if ($corpcode != '') {
                $objCorporateBalance = new CorporateBalance();
                $corpBal = $objCorporateBalance->getCorpinfoByCorpCode(array('corpcode' =>$corpcode,'product_id' => (string) $resp->ProductCode));
                if (!$corpBal) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CORPCODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CORPCODE_CODE);
                } else {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->amount = (string) $corpBal['amount'];
                    return $responseObj;
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CORPCODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CORPCODE_CODE);
            }
        } catch (Exception $e) {
            $this->_soapServer->_getLogger()->__setException($e->getMessage());
            App_Logger::log(serialize($e), Zend_Log::ERR);
            return self::Exception($e->getMessage(), $e->getCode());
        }
    }

    public function TransactionRequest() {
        try {
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);


            if (!isset($resp->SessionID) || !$this->isLogin((string) $resp->SessionID)) {
                return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
            }
            // Currency Check
            if ((string) trim($resp->Currency) != '') {
                if ((string) trim($resp->Currency) != CURRENCY_INR_CODE) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CURRENCY_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CURRENCY_CODE);
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CURRENCY_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CURRENCY_CODE);
            }

            //	Amount valid check  
            if ((string) trim($resp->Amount) != '') {
                if (!is_numeric((string) trim($resp->Amount))) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_CODE);
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_CODE);
            }


            Validator_Ratnakar::productcodeValidation((string) trim($resp->ProductCode), $this->getProductConstant());
            Validator_Ratnakar::txnindicatorValidation((string) trim($resp->TxnIndicator));
            Validator_Ratnakar::cardtypeValidation((string) trim($resp->CardType));
            Validator_Ratnakar::txnNumValidation((string) trim($resp->TxnNo));
            Validator_Ratnakar_Customer::memberIdCardNumValidation((string) trim($resp->MemberIDCardNo));
            Validator_Ratnakar_Customer::transactionNarrationValidation((string) trim($resp->Narration));
            Validator_Ratnakar_Beneficiary::remitterwalletcodeValidation((string) trim($resp->WalletCode));


            // Check SMS Flag
            $smsflag = (string) trim($resp->SMSFlag);
            $smsflag = strtolower($smsflag);

            if ($smsflag != '') {
                if (strlen($smsflag) > 1 || ( $smsflag != strtolower(self::SMS_FLAG_TYPE_YES) && $smsflag != strtolower(self::SMS_FLAG_TYPE_NO) )) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_CODE);
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_SMS_FLAG_CODE);
            }
            // Check TxnIdentifierType
            $txnidentifierflag = (string) trim($resp->TxnIdentifierType);
            $txnidentifierflag = strtolower($txnidentifierflag);

            if ($txnidentifierflag != '') {
                if (strlen($txnidentifierflag) > 3 || ( $txnidentifierflag != strtolower(self::TXN_IDENTIFIER_TYPE_MOB) && $txnidentifierflag != strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER) )) {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_CODE);
                }
            } else {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_TXN_IDENTIFIRE_TYPE_CODE);
            }

            try {

                $refObject = new Reference();

                $object = new Corp_Ratnakar_Cardholders();
                $param['product_id'] = (string) trim($resp->ProductCode);

                if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_MOB)) {
                    $param['mobile'] = (string) trim($resp->MemberIDCardNo);
                } else if ($txnidentifierflag == strtolower(self::TXN_IDENTIFIER_TYPE_PARTNER)) {
                    $param['partner_ref_no'] = (string) trim($resp->MemberIDCardNo);
                }
                $custInfo = $object->getCustomerInfoBy($param);

                if ($custInfo == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG;
                    return $responseObj;
                }
                $obj = new Corp_Ratnakar_Cardload();
                $productConst = $this->getProductConstant();
                $productModel = new Products();
                $productID = $productModel->getProductIDbyConst($productConst);
                $loadDetails = $obj->getLoadDetails(array('txn_no' => (string) trim($resp->TxnNo), 'product_id' => $productID));
                if (!empty($loadDetails)) {
                    return self::Exception('Transaction number already in use', App_ApiServer_Exchange::$INVALID_RESPONSE, $loadDetails['txn_code']);
                }
                //Do card load
                $baseTxn = new BaseTxn();
                $txnCode = $baseTxn->generateTxncode();
                $bankObject = new Banks();
                // get corporate id by corporate codde

                $objCorporateBalance = new CorporateBalance();
                $corpinfo = $objCorporateBalance->getCorpinfoByCorpCode((string) trim($resp->CorporateCode));

                $params['cardholder_id'] = $custInfo['id'];
                $params['product_id'] = (string) trim($resp->ProductCode);
                $params['wallet_code'] = (string) trim($resp->WalletCode);
                $params['amount'] = (string) trim($resp->Amount);
                $params['txn_no'] = (string) trim($resp->TxnNo);
                $params['txn_identifier_type'] = (string) trim($resp->TxnIdentifierType); //mob
                $params['txn_identifier_num'] = (string) trim($resp->MemberIDCardNo); //mob
                $params['narration'] = (string) trim($resp->Narration); //mob
                $params['card_type'] = trim($resp->CardType); // nCardType
                $params['corporate_id'] = 0;
                $params['mode'] = (string) trim($resp->TxnIndicator); //CR/DR
                $params['by_api_user_id'] = $corpinfo['corporate_code']; //get it from model
                $params['bank_product_const'] = $this->getBankProductConstant();
                $params['txn_code'] = $txnCode;
                $params['manageType'] = $this->getManageTypeConstant();
		$params['channel'] = CHANNEL_API ;

                $bankInfo = $bankObject->getBankidByProductid($params['product_id']);
                if (!empty($bankInfo)) {
                    $params['bank_id'] = $bankInfo['bank_id'];
                } else {
                    throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_PRODUCT_CODE);
                }

                $flg = $obj->doCardloadAPI($params);

                if ($flg == FALSE) {
                    $responseObj = new stdClass();
                    $responseObj->SessionID = (string) $resp->SessionID;
                    $responseObj->TxnNo = (string) trim($resp->TxnNo);
                    $responseObj->AckNo = $txnCode;
                    $responseObj->Filler1 = '';
                    $responseObj->Filler2 = '';
                    $responseObj->ResponseCode = self::TRANSACTION_REQUEST_FAILURE_CODE;
                    $responseObj->ResponseMessage = self::TRANSACTION_REQUEST_FAILURE_MSG;
                    return $responseObj;
                }
                $curCode = CURRENCY_INR;

                $responseObj = new stdClass();

                $responseObj->SessionID = (string) $resp->SessionID;
                $responseObj->TxnNo = (string) trim($resp->TxnNo);
                $responseObj->AckNo = $txnCode;
                $responseObj->Filler1 = '';
                $responseObj->Filler2 = '';
                $responseObj->ResponseCode = self::TRANSACTION_REQUEST_SUCCESS_CODE;
                $responseObj->ResponseMessage = self::TRANSACTION_REQUEST_SUCCESS_MSG;

                return $responseObj;
            } catch (App_Exception $e) {
                App_Logger::log(serialize($e), Zend_Log::ERR);
                $code = $e->getCode();
                $code = (empty($code)) ? self::TRANSACTION_REQUEST_FAILURE_CODE : $code;
                $message = $e->getMessage();
                $message = (empty($message)) ? self::TRANSACTION_REQUEST_FAILURE_MSG : $message;
                $this->_soapServer->_getLogger()->__setException($message);
                return self::Exception($message, $code);
            }
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $code = $e->getCode();
            $code = (empty($code)) ? self::TRANSACTION_REQUEST_FAILURE_CODE : $code;
            $message = $e->getMessage();
            $message = (empty($message)) ? self::TRANSACTION_REQUEST_FAILURE_MSG : $message;
            $this->_soapServer->_getLogger()->__setException($message);
            return self::Exception($message, $code);
        }
    }

    public function fundingRequest() {
        try {  
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
             if (!isset($resp->SessionID) || !$this->isLogin((string) $resp->SessionID)) {
                return self::Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOGIN_CODE);
            }
            $objFR = new CorporateFunding();
            $objCorporate = new Corporates();
            
            //Check if Corporate is under Peelwork Regional
            $peelWorksCorpID = $this->getAgentConstant();
           
            // Get Corporate info
            $corpinfo = $objCorporate->findByCorporateCode((string) trim($resp->CorporateCode));
            if(empty($corpinfo)){
               throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CORPORATE_CODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CORPORATE_CODE_CODE);
            }
            
            $objectRelation = new ObjectRelations();
            $localCorp = $objectRelation->getToObjectDetails($peelWorksCorpID,$corpinfo['id'], REGIONAL2LOCAL);

            if(empty($localCorp)){
               throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_LOCAL_CORPORATE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_LOCAL_CORPORATE_CODE);
            }
            
            $fundingType = strtolower((string) $resp->FundingType);
            
            if($fundingType == strtolower(FUND_TRANSFER_TYPE_CASH)){
                $fundingTypeID = FUND_TRANSFER_TYPE_ID_CASH;
            }elseif($fundingType == strtolower(FUND_TRANSFER_TYPE_CHEQUE)){
                 $fundingTypeID = FUND_TRANSFER_TYPE_ID_CHEQUE;
            }elseif($fundingType == strtolower(FUND_TRANSFER_TYPE_NEFT)){
                 $fundingTypeID = FUND_TRANSFER_TYPE_ID_NEFT;
            }elseif($fundingType == strtolower(FUND_TRANSFER_TYPE_DD)){
                 $fundingTypeID = FUND_TRANSFER_TYPE_ID_DD;
            }
            $fund_transfer_cash_or_dd = ($fundingTypeID == FUND_TRANSFER_TYPE_ID_CASH || $fundingTypeID == FUND_TRANSFER_TYPE_ID_DD );
            $fund_transfer_chk = ($fundingTypeID == FUND_TRANSFER_TYPE_ID_CHEQUE);
            $fund_transfer_neft = ($fundingTypeID == FUND_TRANSFER_TYPE_ID_NEFT);
            
            
            $otherTxn = (string) $resp->OtherTxn;
            $journalNo = (string) $resp->JournalNo;
            $chequeNo = (string) $resp->ChequeNo;
            $fundingDetails = (string) $resp->FundingDetails;
            $bankofCheque = (string) $resp->BankOfChequeIssue;
            $branchofCheque = (string) $resp->BranchOfCheque;
            $dateofCheque = (string) $resp->DateOfChequeIssue;
            $corporateCode = (string) $resp->CorporateCode;
            $amount = (string) $resp->Amount;
            $comments = (string) $resp->Comments;
           
            if (($fundingType != strtolower(FUND_TRANSFER_TYPE_CASH)) && ($fundingType != strtolower(FUND_TRANSFER_TYPE_CHEQUE)) && ($fundingType != strtolower(FUND_TRANSFER_TYPE_NEFT)) && ($fundingType != strtolower(FUND_TRANSFER_TYPE_DD))) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FUNDING_TYPE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FUNDING_TYPE_CODE);
                
            } elseif ($fund_transfer_cash_or_dd && empty($otherTxn)) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FUNDING_OTHER_TXN_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FUNDING_OTHER_TXN_CODE);
                
            } elseif ($fund_transfer_chk && (empty($chequeNo) || empty($bankofCheque) || empty($branchofCheque) || empty($dateofCheque))) {
                throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FUNDING_OTHER_CHEQUE_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FUNDING_OTHER_CHEQUE_NO_CODE);
                
            } elseif ($fund_transfer_neft && empty($journalNo)) {
               throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FUNDING_OTHER_JOURNAL_NO_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FUNDING_OTHER_JOURNAL_NO_CODE);                
            } 
//            elseif (empty($fundingDetails)) {
//               throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_FUNDING_DETAILS_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_FUNDING_DETAILS_CODE);                
//            }
            elseif (empty($corporateCode) || !is_numeric( $corporateCode)){
               throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CORPCODE_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CORPCODE_CODE);                
            } elseif (empty($amount) || !is_numeric($amount)) {
               throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_AMOUNT_CODE);                
            } elseif (empty($comments) || strlen($comments > 255)) {
               throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_COMMENTS_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_COMMENTS_CODE);
            }
            

            $data = array();
            $data['fund_transfer_type_id'] = $fundingTypeID;
            if ($fund_transfer_cash_or_dd) {
                    $data['funding_no'] = $otherTxn;
                    $data['funding_details'] = $fundingDetails; 
                } elseif ($fund_transfer_chk) {
                    $data['funding_no'] = $chequeNo;
                    $data['funding_details'] =
                            'Bank of cheque issue:'
                            . (string) $resp->BankOfChequeIssue
                            . SEPARATOR_PIPE
                            . 'Branch of cheque:'
                            . (string) $resp->BranchOfCheque
                            . SEPARATOR_PIPE
                            . 'Date of issue:'
                            . (string) $resp->DateOfChequeIssue;
                } elseif ($fund_transfer_neft) {
                    $data['funding_no'] = $journalNo;
                    $data['funding_details'] = 'NEFT transfer'; 
                }
             
                $data['status'] = STATUS_PENDING;
                $data['corporate_id'] = $corpinfo['id'];
                $data['ip_agent'] = $objFR->formatIpAddress(Util::getIP());
                $data['amount'] = (string) $resp->Amount;
                $data['comments'] = (string) $resp->Comments;
                
                try {
                    $resp = $objFR->addCorporateFunding($data);
                } catch (Exception $e) {
                    App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    // if error
             $responseObj->SessionID = (string) $resp->SessionID;
             $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_UNSUCCESSFULL_FUNDING_CODE;
             $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_UNSUCCESSFULL_FUNDING_MSG;
             return $responseObj;
                    
                }
             
               if ($resp) {
                    $minmax = $objFR->chkCorporateMinMaxLoad($data);
                    $responseObj->SessionID = (string) $resp->SessionID;
                    
                    
                    if ($minmax != FALSE) { //If chkAgentMinMaxLoad not return false 
                        $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_SUCCESSFULL_FUNDING_CODE;
                        $responseObj->ResponseMessage = 'Fund request has been sent successfully.For future please request for fund between ' . CURRENCY_INR . ' ' . Util::numberFormat($minmax['minValue']) . ' and ' . CURRENCY_INR . ' ' . Util::numberFormat($minmax['maxValue']) . '.';
                    } else {
                        
                    $responseObj->ResponseCode = ErrorCodes::ERROR_EDIGITAL_SUCCESSFULL_FUNDING_CODE;
                    $responseObj->ResponseMessage = ErrorCodes::ERROR_EDIGITAL_SUCCESSFULL_FUNDING_MSG;
                    
                    }
                    return $responseObj;
                }
             
           
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
            $code = $e->getCode();
            $code = (empty($code)) ? ErrorCodes::ERROR_SYSTEM_ERROR : $code;
            $message = $e->getMessage();
            $message = (empty($message)) ? ErrorCodes::ERROR_SYSTEM_ERROR : $message;
            $this->_soapServer->_getLogger()->__setException($message);
            return self::Exception($message, ErrorCodes::ERROR_SYSTEM_ERROR);
        }
    }

}
