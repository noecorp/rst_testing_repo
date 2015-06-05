<?php

/**
 * Kotak API Class
 * Base class for Kotak Remit API
 * 
 * @copany Transerv
 * @author Vikram
 */
class App_Api_Kotak extends App_Api_Curl {

    //Kotak URL
    private $_url = 'http://203.196.200.42/pggbm/fiservice';
    private $_merchantId = 'TRANSERV';
    private $_beneAccountType = '10';
    private $_key = 'KMBANK';
    //private $_delimiter         = '|';
    private $_trace = '';
    private $_qbTrace = '';
    private $_creditMessageCode = '6210';
    private $_queryMessageCode = '6310';
    private $_creditRequestType = 'R';
    private $_respAccountCredit = '';
    private $_respAccountQuery = '';
    private $_msg = '';
    private $_accountCreditRespCode = '';
    private $_accountCreditRespMsg  = '';
    private $_responseCode          = '';

    const QUERY_RESPONSE_FAILED = 'QUERY_RESPONSE_FAILED';
    const QUERY_RESPONSE_FAILED_MESSAGE = 'NO Response';
    const QUERY_RESPONSE_TRUE = 'QUERY_RESPONSE_TRUE';
    const RESPONSE_SUCCESS = 'KPY00';
    const RESPONSE_TIMEOUT = 'KPY91';
    const DELIMITER = '|';
    const FUNC_ACCOUNT_CREDIT = 'ACCOUNT_CREDIT';
    const FUNC_ACCOUNT_QUERY = 'ACCOUNT_QUERY';
    const QUERY_RESPONSE_CHECKSUM_FAILED = 'QUERY_RESPONSE_CHECKSUM_FAILED';
    const QUERY_RESPONSE_CHECKSUM_FAILED_MESSAGE = 'Invalid Checksum Received';
    const TRANSACTION_INVALID_RESPONSE = 'TRANSACTION_INVALID_RESPONSE';
    const TRANSACTION_INVALID_RESPONSE_MESSAGE = 'Invalid Transaction Response';
    const TRANSACTION_NOT_FOUND_ERROR_CODE = 'KPYERR15';
    const TRANSACTION_NOT_FOUND = 'TRANSACTION_NOT_FOUND';    
    const VALID_RESPONSE_COUNT = 11;

    //Constructor
    public function __construct() {
        parent::__construct($this->_url);
    }

    /**
     * checkInputParams
     * Method to Check Input Params
     * @param array $param
     * @return boolean
     * @throws App_Exception
     */
    private function checkInputParams($param) {
        $flag = false;

        foreach ($param as $val) {
            $flag = strpos($val, self::DELIMITER) ? TRUE : FALSE;
            if ($flag) {
                return $flag;
                break;
            } else {
                continue;
            }
        }
        return $flag;
    }

    /**
     * Credit Account
     * Method to Credit Account
     * @param array $param
     * @return boolean
     * @throws App_Exception
     */
    public function creditAccount(array $param) {

        /* if(!isset($param['messageCode']) || $param['messageCode'] == '') {
          throw new App_Exception('Invalid Message Code');
          } */

        if (!empty($param)) {
            $flag = false;
            if (!is_array($param)) {
                $flag = true;
            } else {
                $flag = $this->checkInputParams($param);
            }

            if ($flag) {
                $this->setMessage('Invalid Input Parameters');
                return TRANSACTION_INVALID_PARAMS;
            }
        }

        if (!isset($param['traceNumber']) || $param['traceNumber'] == '') {
            throw new App_Exception('Invalid Transaction Code/Trace Number');
        }

        /* if(!isset($param['requestType']) || $param['requestType'] == '') {
          throw new App_Exception('Invalid Request Type');
          } */

        if (!isset($param['beneIFSC']) || $param['beneIFSC'] == '') {
            throw new App_Exception('Invalid Beneficary IFSC');
        }

        if (!isset($param['beneAccount']) || $param['beneAccount'] == '') {
            throw new App_Exception('Invalid Beneficary Account');
        }

        if (!isset($param['amount']) || $param['amount'] == '') {
            throw new App_Exception('Invalid amount');
        }

        /* if(!isset($param['remarks']) || $param['remarks'] == '') {
          throw new App_Exception('Invalid remarks');
          } */

        //$param['messageCode'] = 
        $this->_trace = $param['traceNumber'];
        $remark = (isset($param['remarks']) && $param['remarks'] != '') ? $param['remarks'] : '';
        $remitterName = (isset($param['remitterName']) && $param['remitterName'] != '') ? $param['remitterName'] : '';
        $remitterMobile = (isset($param['remitterMobile']) && $param['remitterMobile'] != '') ? $param['remitterMobile'] : '';
        $partnerName = (isset($param['partnerName']) && $param['partnerName'] != '') ? $param['partnerName'] : '';
 
        $date = date('dmYhis');
        //Old Format
        //$string = $this->_creditMessageCode . self::DELIMITER . $date . self::DELIMITER . $this->_merchantId . self::DELIMITER . $param['traceNumber'] . self::DELIMITER . $this->_creditRequestType . self::DELIMITER . $param['beneIFSC'] . self::DELIMITER . $param['beneAccount'] . self::DELIMITER . $param['amount'] . self::DELIMITER . $remark . self::DELIMITER . $this->_beneAccountType;
        
        //New Format
         $string = $this->_creditMessageCode . self::DELIMITER . $date . self::DELIMITER . $this->_merchantId . self::DELIMITER . $param['traceNumber'] . self::DELIMITER . $this->_creditRequestType . self::DELIMITER . $param['beneIFSC'] . self::DELIMITER . $param['beneAccount'] . self::DELIMITER . $param['amount'] . self::DELIMITER . $remark . self::DELIMITER . $this->_beneAccountType . self::DELIMITER .$remitterName. self::DELIMITER .$remitterMobile. self::DELIMITER.$partnerName . self::DELIMITER . self::DELIMITER;
          
        $checksum = $this->createChecksumWithKey($string);
        $final = 'msg=' . $string . self::DELIMITER . $checksum;
        $resp = $this->initiateRequest($final);
      
        //if transaction not found send the request again
        if($resp === self::TRANSACTION_NOT_FOUND) {
        	$resp = $this->initiateRequest($final);
        }
      
        if ($resp === self::QUERY_RESPONSE_FAILED) {//NO Response in Credit and Query Both
            $flg = TRANSACTION_NORESPONSE;
            $this->setMessage(self::QUERY_RESPONSE_FAILED_MESSAGE);
        } elseif ($resp === self::QUERY_RESPONSE_TRUE) {//Didn't Received Response at first case but Get it after query
            $flg = $this->validateQueryResponse();
        } elseif ($resp === TRUE) {//Valid Response, Now Validate response
            $flg = $this->validateCreditResponse();
        } elseif ($resp === FALSE) {//No Response
            $flg = TRANSACTION_NORESPONSE;
            $this->setMessage(self::QUERY_RESPONSE_FAILED_MESSAGE);            
        } elseif ($resp == self::QUERY_RESPONSE_CHECKSUM_FAILED) {//Invalid Checksum - Need to generate Alert
            $flg = TRANSACTION_CHECKSUM_FAILED;
            $this->setMessage(self::QUERY_RESPONSE_CHECKSUM_FAILED_MESSAGE);
        } elseif ($resp == self::TRANSACTION_INVALID_RESPONSE) {//Invalid Transaction Response
            $flg = TRANSACTION_INVALID_RESPONSE;
            $this->setMessage(self::TRANSACTION_INVALID_RESPONSE_MESSAGE);//Setting up Invalid Transaction Response
        } else {
            //Handle other events
            $flg = TRANSACTION_NORESPONSE;
            $this->setMessage(self::QUERY_RESPONSE_FAILED_MESSAGE);
        }
        return $flg;
    }

    /**
     * Query Account
     * Method to Query Account based on transaction code
     * @param array $param
     * @return boolean
     * @throws App_Exception
     */
    public function queryAccount(array $param) {
        /* if(!isset($param['messageCode']) || $param['messageCode'] == '') {
          throw new App_Exception('Invalid Message Code');
          } */

        if (!isset($param['traceNumber']) || $param['traceNumber'] == '') {
            throw new App_Exception('Invalid Transaction Code/Trace Number');
        }

        if (!isset($param['qbTraceNumber']) || $param['qbTraceNumber'] == '') {
            throw new App_Exception('Invalid Query Trace Number');
        }
        $this->setQueryTraceNumber($param['traceNumber']);
        $date = date('dmYhis');
        $string = $this->_queryMessageCode . self::DELIMITER . $date . self::DELIMITER . $this->_merchantId . self::DELIMITER . $param['traceNumber'] . self::DELIMITER . $param['qbTraceNumber'];
        $checksum = $this->createChecksumWithKey($string);
        $final = 'msg=' . $string . self::DELIMITER . $checksum;
        $resp = $this->sendRequest($final, self::FUNC_ACCOUNT_QUERY);

        if ($resp == TRUE) {
            return TRUE;
            //return $this->validateQueryResponse();
        } else {//if ($resp == FALSE) {
            return FALSE;
        } /* else {
          print __CLASS__ . ' : ' . __METHOD__ . ' : Unknown :' . $resp;
          } */
    }

    /**
     * Query Account And Validate
     * Method to Query account and validate its response
     * @param array $param
     * @return boolean
     */
    public function queryAccountAndValidate(array $param) {
        $resp = $this->queryAccount($param);
        if ($resp === TRUE) {
            $response = $this->getResponse();
            $pRespnse = $this->parseQueryAccountResponse($response);
            if (empty($pRespnse)) {
                return FALSE;
            } elseif ($pRespnse == self::QUERY_RESPONSE_CHECKSUM_FAILED) {
                return self::QUERY_RESPONSE_CHECKSUM_FAILED;
            }

            $this->setAccountQueryResponse($pRespnse);
            if (isset($pRespnse['resp_desc']) && $pRespnse['resp_desc'] != '') {
                $msg = $pRespnse['resp_desc'];
            } else {
                $msg = $this->getErrorCodeDescription($pRespnse['resp_code'],FALSE);
            }
            $this->setResponseCode($pRespnse['resp_code']);            
            $this->setMessage($msg);
            return $this->validateQueryResponse();
        }
        return FALSE;
    }

    /**
     * CreateChecksum with Key
     * Method to create checksum with given key
     * @param type $str
     * @return type
     */
    private function createChecksumWithKey($str) {
        $checksum = $str . self::DELIMITER . $this->_key;
        $cs = $this->encodeString($checksum);
        return $cs;
    }

    /**
     * EncodeString
     * Encode given string using CRC encryption
     * @param String $str
     * @return String
     */
    private function encodeString($str) {
        $checksum = crc32($str);
        $cs = sprintf("%u", $checksum);
        return ($cs);
    }

    /**
     * GetQueryResponce
     * Method to getQueryResponce for qbTraceNumber and traceNumber
     * @return queryAccount result
     */
    private function getQueryResponce() {
        $queryParam = array(
            'qbTraceNumber' => $this->getTraceNumber(),
            'traceNumber' => $this->getQueryTraceNumber()
        );
        return $this->queryAccount($queryParam);
    }

    /**
     * initiateRequestWithValidateCheckSum
     * @return string 
     * 
     */
    function initiateRequestWithValidateCheckSum() {

        $qbTraceNumber = $this->generateQueryBackTrace();
        $this->setQueryTraceNumber($qbTraceNumber);
        $resp = $this->getQueryResponce();
        
        if ($resp === FALSE) {
            return self::QUERY_RESPONSE_FAILED;
        } else {
            $response = $this->getResponse();
            $pRespnse = $this->parseQueryAccountResponse($response);
            if (empty($pRespnse)) {
                return self::QUERY_RESPONSE_FAILED;
            } elseif ($pRespnse == self::TRANSACTION_INVALID_RESPONSE) {
                return self::TRANSACTION_INVALID_RESPONSE;
            } elseif (isset($pRespnse['resp_code']) && $pRespnse['resp_code'] == self::TRANSACTION_NOT_FOUND_ERROR_CODE) {
                return self::TRANSACTION_NOT_FOUND;
            }

            $this->setAccountQueryResponse($pRespnse);
            //$errorCodeArray = $this->getErrorCodeArray();
            //if(isset($errorCodeArray[$pRespnse['resp_code']])) {
            if (isset($pRespnse['resp_desc']) && $pRespnse['resp_desc'] != '') {
                $msg = $pRespnse['resp_desc'];
            } else {
                $msg = $this->getErrorCodeDescription($pRespnse['resp_code'],FALSE);
            }
            $this->setMessage($msg);
            $this->setResponseCode($pRespnse['resp_code']);
            return self::QUERY_RESPONSE_TRUE;
        }
    }

    /**
     * InitiateRequest
     * Method to initiateRequest for Customer Account Credit, This will also handle the response in case of FALSE
     * @param type $request
     * @return boolean
     */
    private function initiateRequest($request) {
         
        $resp = $this->sendRequest($request, self::FUNC_ACCOUNT_CREDIT);
        if ($resp == FALSE) {//Setting up for debugging default should be false
            return $this->initiateRequestWithValidateCheckSum();
            
        } else {
            $response = $this->getResponse();
            $pRespnse = $this->parseCreditAccountResponse($response);
            
            if ($pRespnse == self::QUERY_RESPONSE_CHECKSUM_FAILED) {
                return $this->initiateRequestWithValidateCheckSum();
            } elseif ($pRespnse == self::TRANSACTION_INVALID_RESPONSE) {
                return $this->initiateRequestWithValidateCheckSum();
            } elseif (empty($pRespnse)) {
                return $this->initiateRequestWithValidateCheckSum();
            }
            $this->setAccountCreditResponse($pRespnse);
            //
            
            if (isset($pRespnse['error_reason']) && $pRespnse['error_reason'] != '') {
                $msg = $pRespnse['error_reason'];
            } else {
                $msg = $this->getErrorCodeDescription($pRespnse['resp_code'],FALSE);
            }
            $this->setMessage($msg);
            
            //
            
          //  $this->setMessage($this->getErrorCodeDescription($pRespnse['resp_code'],FALSE));
            $this->setResponseCode($pRespnse['resp_code']);
            return TRUE;
        }
    }

    /**
     * ParseCreditAccountResponse
     * Method to parse Credit Account Response
     * @param type $response
     * @return boolean
     */
    private function parseCreditAccountResponse($response) {

        if ($response == '') {
            //throw new App_Exception('Invalid Response');
            return FALSE;
        }

        $explodeResponse = explode(self::DELIMITER, $response);

        if (count($explodeResponse) !== self::VALID_RESPONSE_COUNT) {
            //$this->setMessage('Invalid Api Responce');
            return self::TRANSACTION_INVALID_RESPONSE;
        }

        if (!$this->validateChecksum($explodeResponse)) {
            return self::QUERY_RESPONSE_CHECKSUM_FAILED;
        }

        /*if($this->getTraceNumber() != $explodeResponse[3]) {
            return self::TRANSACTION_INVALID_RESPONSE;
        }*/

        $responseArray = array(
            'message_code' => $explodeResponse[0],
            'date' => $explodeResponse[1],
            //'merchant_id'  => $resp[2],//Don't share merchant id
            'trace_number' => $explodeResponse[3],
            'resp_code' => $explodeResponse[4],
            'error_reason' => $explodeResponse[5],
            'rrn' => $explodeResponse[6],
            'bank_ref_num' => $explodeResponse[7],
            'trans_date' => $explodeResponse[8],
            'bene_name' => $explodeResponse[9],
        );
        $this->setAccountCreditRespCode($explodeResponse[4]);
        $this->setAccountCreditRespMsg($explodeResponse[5]);
        return $responseArray;
    }

    /**
     * validateChecksum
     * @param type $string
     * @return boolean
     */
    private function validateChecksum($explodeArr) {

        $count = count($explodeArr);
        $key = $explodeArr[$count - 1];
        unset($explodeArr[$count - 1]);
        $remainString = implode(self::DELIMITER, $explodeArr);
        $genCheckSum = $this->createChecksumWithKey($remainString);
        $restult = ($genCheckSum === $key) ? TRUE : FALSE;
        return $restult;
    }

    /**
     * Parse Query Account Response
     * Method to parse Query Account Response
     * @param type $response
     * @return boolean
     */
    private function parseQueryAccountResponse($response) {

        if ($response == '') {
            //throw new App_Exception('Invalid Response');
            return FALSE;
        }

        $explodeResponse = explode(self::DELIMITER, $response);

        if (count($explodeResponse) != self::VALID_RESPONSE_COUNT) {
            return self::TRANSACTION_INVALID_RESPONSE;
        }

        if (!$this->validateChecksum($explodeResponse)) {
            return self::QUERY_RESPONSE_CHECKSUM_FAILED;
        }

        if($this->getQueryTraceNumber() != $explodeResponse[3]) {
            //return self::TRANSACTION_INVALID_RESPONSE;
        }        
        
        //print 'Query Trace Number :' .$this->getQueryTraceNumber() . '<br />';
        //print 'Trace Response : '.$explodeResponse[3]. '<br />';

        $responseArray = array(
            'message_code' => $explodeResponse[0],
            'date' => $explodeResponse[1],
            //'merchant_id'  => $resp[0],//Don't share merchant id
            'trace_number' => $explodeResponse[3],
            'rrn' => $explodeResponse[4],
            'bank_ref_num' => $explodeResponse[5],
            'amount' => $explodeResponse[6],
            'auth_status' => $explodeResponse[7],
            'resp_code' => $explodeResponse[8],
            'resp_desc' => $explodeResponse[9],
        );
        return $responseArray;
    }

    /**
     * setTraceNumber
     * Method used to set trace number
     * @param type $traceNumber
     */
    private function setTraceNumber($traceNumber) {
        $this->_trace = $traceNumber;
    }

    /**
     * Get TraceNumber 
     * Method to fetch tracenumber
     * @return type
     */
    private function getTraceNumber() {
        return $this->_trace;
    }

    /**
     * setQueryTraceNumber
     * Method to setup Query Back Trace Number
     * @param type $qbTrace
     */
    private function setQueryTraceNumber($qbTrace) {
        $this->_qbTrace = $qbTrace;
    }

    /**
     * getQueryTraceNumber
     * Get Query Back Trace Number
     * @return type
     */
    private function getQueryTraceNumber() {
        return $this->_qbTrace;
    }

    /**
     * generateQueryBackTrace Number
     * Method to generate random QueryBack Trace Number
     * @return type
     */
    private function generateQueryBackTrace() {
        return Util::generateRandomNumber(8);
    }

    /**
     * SetAccountCreditResponse
     * Method to set Account Credit Response
     * @param type $resp
     */
    private function setAccountCreditResponse($resp) {
        $this->_respAccountCredit = $resp;
    }

    /**
     * setAccountQueryResponse
     * Set Account Query Response 
     * @param type $resp
     */
    private function setAccountQueryResponse($resp) {
        $this->_respAccountQuery = $resp;
    }

    /**
     * getAccountCreditResponse
     * Method to get Account Credit Response
     * @return type
     */
    public function getAccountCreditResponse() {
        return $this->_respAccountCredit;
    }

    /**
     * getAccountQueryResponse
     * Method to get Account Query Response
     * @return type
     */
    public function getAccountQueryResponse() {
        return $this->_respAccountQuery;
    }
    
    
    private function setAccountCreditRespCode($respCode) {
        $this->_accountCreditRespCode = $respCode;
    }
    
    private function setAccountCreditRespMsg($respMsg) {
        $this->_accountCreditRespMsg = $respMsg;
    }
    
        
    public function getAccountCreditRespCode() {
        return $this->_accountCreditRespCode;
    }
    
    public function getAccountCreditRespMsg() {
        return $this->_accountCreditRespMsg;
    }
    
    /**
     * validateQueryResponse
     * Method to Validate Query Response
     * @return boolean
     */
    private function validateQueryResponse() {
        $resp = $this->getAccountQueryResponse();
        if (isset($resp['resp_code']) && $resp['resp_code'] == self::RESPONSE_SUCCESS) {
            return TRANSACTION_SUCCESSFUL;
        } elseif (isset($resp['resp_code']) && $resp['resp_code'] == self::RESPONSE_TIMEOUT) {
            return TRANSACTION_TIMEOUT;
        } elseif (isset($resp['resp_code']) && $resp['resp_code'] == self::TRANSACTION_NOT_FOUND) {
            return TRANSACTION_FAILED;
        } elseif (isset($resp['resp_code']) && !$this->validateErrorCode($resp['resp_code'])) {
            return TRANSACTION_INVALID_RESPONSE_CODE;
        } 
        return TRANSACTION_FAILED;
    }

    /**
     * validateCreditResponse
     * Method to Validate Credit Response
     * @return boolean
     */
    private function validateCreditResponse() {
        $resp = $this->getAccountCreditResponse();
        if (isset($resp['resp_code']) && $resp['resp_code'] == self::RESPONSE_SUCCESS) {
            return TRANSACTION_SUCCESSFUL;
        } elseif (isset($resp['resp_code']) && $resp['resp_code'] == self::RESPONSE_TIMEOUT) {
            return TRANSACTION_TIMEOUT;
        } elseif (isset($resp['resp_code']) && $resp['resp_code'] == self::TRANSACTION_NOT_FOUND) {
            return TRANSACTION_FAILED;
        }elseif (isset($resp['resp_code']) && !$this->validateErrorCode($resp['resp_code'])) {
            return TRANSACTION_INVALID_RESPONSE_CODE;
        }
        return TRANSACTION_FAILED;
    }

    /**
     * getErrorCodeDescription
     * Method to get Error Code Description
     * @param type $code
     * @return string
     */
    private function getErrorCodeDescription($code, $withCode = TRUE) {

        $param = $this->getErrorCodeArray();
        if (isset($param[$code]) && $param[$code] != '') {
            $msg = $param[$code];
        } else {
            $msg = 'Unknown Error';
        }
        if ($withCode == TRUE && !empty($code)) {
            $msg = '(' . $code . ') ' . $msg;
        }
        return $msg;
    }

    /**
     * getErrorCodeArray
     * Method to get Error Code Array
     * @return array
     */
    private function getErrorCodeArray() {
        return array(
            "KPY20" => "Invalid Response Code",
            "KPYMM" => "Beneficiary is non-reloadable card",
            "KPYP2A92" => "Invalid MMID / IFSC",
            "KPYML" => "Payee is a merchant and not an individual",
            "KPY00" => "Successful Transaction",
            "KPY06" => "Subprogram error",
            "KPY08" => "CBS Offline",
            "KPY12" => "Invalid Transaction",
            "KPY13" => "Transaction Amount cannot be zero",
            "KPY18" => "OTP Not Present",
            "KPY51" => "Not sufficient funds",
            "KPY57" => "Transaction not permitted to account",
            "KPY65" => "Txn frequency exceeded",
            "KPY86" => "Invalid NBIN",
            "KPY90" => "Cutoff is in progress",
            "KPY91" => "Time out",
            "KPY94" => "Duplicate Transaction",
            "KPY96" => "Unable to process",
            "KPYM0" => "Verification Successful but original credit transaction failed",
            "KPYM1" => "Invalid Beneficiary Mobile Number/MMID ",
            "KPYM1" => "Invalid A/c Number or IFSC Code",
            "KPYM2" => "Amount limit Exceeded",
            "KPYM3" => "Account Blocked/Frozen",
            "KPYM4" => "NRE Account",
            "KPYM5" => "Account Closed",
            "KPYM6" => "Limit exceeded for member bank",
            "KPYM7" => "Transaction not permitted for this Account",
            "KPYM8" => "Transaction limit exceeded for this Account",
            "KPYM9" => "Incorrect OTP",
            "KPYMA" => "Merchant Validation Fail",
            "KPYMF" => "No response from merchant system",
            "KPYMH" => "OTP Amount Limit Exceeded",
            "KPYMV" => "Merchant Online Validation Required Not Available",
            "KPYMZ" => "OTP Expired",
            "KPYPK" => "Merchant key Not configured",
            "KPYPR" => "Payment Reference value is required.But value not present",
            "KPYWC" => "Check Sum Error",
            "KPY56" => "Invalid Account Type",
            "KPYMP" => "Functionality not suppored by Bank",
            "KPY92" => "Invalid IFSC",
            "KPY92" => "Invalid NBIN",
            "KPYM57" => "Transaction Not permitted to Merchant a/c",
            "KPYMM1" => "Invalid Merchant Mobile Number/MMID",
            "KPYMM4" => "Merchant A/c is NRE Account",
            "KPYN0" => "No connectivity with NPCI",
            "KPYNC" => "No connectivity with NPCI",
            "KPYP2AM1" => "Invalid A/c Number or IFSC Code",
            "KPYRM1" => "Invalid Remittor Mobile Number/MMID",
            "KPYRM4" => "Remittor A/c is NRE Account",
            "KPYR57" => "Transaction Not permitted to Remittor a/c",
            "KPYMK" => "Payee is an individual and not a merchant",
            "KPYERR01" => "Invalid Service Request",
            "KPYERR02" => "Invalid Date Time",
            "KPYERR03" => "Invalid Merchant Id",
            "KPYERR04" => "Invalid Trace Number",
            "KPYERR05" => "Invalid Request Type",
            "KPYERR06" => "Invalid Bene Mobile Number",
            "KPYERR07" => "Invalid Bene MMID",
            "KPYERR08" => "Invalid Bene IFSC Code",
            "KPYERR09" => "Invalid Bene Account Number",
            "KPYERR10" => "Invalid Amount",
            "KPYERR11" => "Invalid Checksum",
            "KPYERR12" => "Duplicate Trace Id",
            "KPYERR13" => "Invalid Message",
            "KPYERR14" => "Invalid Queryback Trace Number",
            "KPYERR15" => "No Such Transaction Request"
        );
    }

    /**
     * setMessage
     * Method to set message
     * @param type $msg
     */
    private function setMessage($msg) {
        $this->_msg = $msg;
    }

    /**
     * getMessage
     * Method to get Message
     * @return type
     */
    public function getMessage($withCode = TRUE) {
        if($withCode == TRUE) {
            return '('.$this->getResponseCode() .') ' . $this->_msg;
        }
        return $this->_msg;
    }
    
    private function setResponseCode($code) {
        $this->_responseCode = $code;
    }

    public function getResponseCode() {
        return $this->_responseCode;
    }

    
    private function validateErrorCode($errCode) {
        $errCode = strtoupper($errCode);
        if($errCode == '' || $errCode == 'null') {
            return FALSE;
        }
        $errCodeList = $this->getErrorCodeArray();
        if(isset($errCodeList[$errCode])) {
            return TRUE;
        }
        return FALSE;
    }
}
