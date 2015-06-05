<?php
class App_Api_Mediassist_Transactions extends App_Api_Mediassist_Authentificator {

    
    private $custAuthSessionId;


    public function sendIntimation($param)
    {
        $mobile = trim($mobile);
        try {
            
            $config = $this->getConfig();        
            $method = 'TransactionInformationRequest';
            //$resp = $this->initSession();

//            if($resp === false) {
//                return false;
//            }
            $soapClient = $this->getSoapClient();
            $sessionKey = $this->getSessionKey();

            $param = array(
                'SessionID' => $sessionKey,
                'TxnIdentifierType' => $param['TxnIdentifierType'],
                'MemberIDCardNo' => $param['MemberIDCardNo'],
                'ResponseCode' => $param['ResponseCode'],
                'ResponseMessage' => $param['ResponseMessage'],
                'DateTime' => $param['DateTime'],
                'TxnNo' => $param['TxnNo'],
                'Amount' => $param['Amount'],
                'Currency' => $param['Currency'],
                'TxnIndicator' => $param['TxnIndicator'],
                'MCC' => $param['MCC'],
                'MerchantName' => $param['MerchantName'],
            );
            $param = array (
                'return' => $param
            );
            //exit("PRE");
            //$response = $soapClient->soapCall($method, $param);
            //$response = $soapClient->soapCall($method, $param);
            $response = $soapClient->filterSoapCall($method, $param);
            $this->setLastResponse($response);

            if(isset($response['ResponseCode']) && $response['ResponseCode'] == '119') {
                //Successful Login
                //$this->setSessionKey($response->sessionKey);
                return TRUE;
            } 
            if($response === false) {
                //print $soapClient->_errorMsg;exit;
                $this->setError($soapClient->errorMsg);
                //print 'ERROR';exit;
                return false;
            }
            //echo "<pre>";print_r($response);exit;
            if(empty($response) || $response == '') {
                throw new Exception (__CLASS__.": Invalid response received from server");
            }

            if(!isset($response['ResponseCode']) || $response['ResponseCode'] == '') {
                throw new Exception (__CLASS__.": Empty response code");
            }

            //$mvcErrorHandler = new App_Processor_MVC_ErrorCode();
            //$this->setError($this->_getErrorMessage($response['ResponseCode']));
        $responseMessage = isset($response['ResponseMessage']) ? $response['ResponseMessage'] : '';
        $this->setError($responseMessage);

        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
        return false;
    }
    
    
}
