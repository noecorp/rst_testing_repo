<?php
class App_Api_Happay_Transactions extends App_Api_Happay_Authentificator {

    
    private $custAuthSessionId;


    public function sendIntimation($param)
    {
        $mobile = trim($mobile);
        try {
            
            $config = $this->getConfig();        
            $method = 'TransactionInformationRequest';
            $this->setHost($config['uri']);
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
            
            $response = $this->postTransaction($param);
            
            if(isset($response->return->ResponseCode) && $response->return->ResponseCode == '119') {
                return TRUE;
            } 
            
            if($response === false) {
                $this->setError($this->getError());
                return false;
            }

            if(empty($response) || $response == '') {
                throw new Exception (__CLASS__.": Invalid response received from server");
            }

            if(!isset($response['ResponseCode']) || $response['ResponseCode'] == '') {
                throw new Exception (__CLASS__.": Empty response code");
            }

            $responseMessage = isset($response['ResponseMessage']) ? $response['ResponseMessage'] : '';
            $this->setError($responseMessage);

        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
        return false;
    }
    
    
}
