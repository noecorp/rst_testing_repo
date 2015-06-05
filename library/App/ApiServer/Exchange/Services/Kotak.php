<?php
/**
 * Webservice Wrapper
 *
 * @category App
 * @package App_Processor
 * @copyright transerv
 */
class App_ApiServer_Exchange_Services_Kotak extends App_ApiServer_Exchange
{
    private $_soapServer;
    const TP_ID = TP_KOTAK_ID;

        /**
     * Constructor
     * @param type $server
     */
    public function __construct($server) {
        $this->_soapServer = $server;
    }

    /**
     * 
     * @param string $username
     * @param string $password
     */
    public function Login($username, $password) {
        
            $this->_soapServer->_getLogger()->__setMethod(__FUNCTION__);
            $sxml = $this->_soapServer->getLastRequest();
            $resp = $this->extractCardTransactionRequestXML($sxml, __FUNCTION__);
            $flg = parent::login($resp->Username, $resp->Password, self::TP_ID);
        
        if($flg) {
            return self::generateSuccessResponse($flg,self::$SUCCESS);
        }
        return self::Exception("Invalid Login", self::$INVALID_LOGIN);
        

    }
    
   
    /**
     * 
     * @param string $sessionId
     * @return date
     * @throws App_ApiServer_Exception
     */
    public function EchoMessage($sessionId) {
       if(!$this->isLogin($sessionId)) {
            return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
        }
        return self::generateSuccessResponse($sessionId);

    }

    /**
     * 
     * @param string $sessionId
     * @throws App_ApiServer_Exception
     */
    public function Logoff($sessionId) {

        $flg = parent::logoff($sessionId);
        if($flg) {
            return self::generateSuccessResponsewithoutSessionID();
        }
        return self::Exception('Invalid SessionID', '168');
        
    }
  public function MiniStatementRequest($sessionID,$crn) {//Do not add comments for method summary
        
        try {
            //var_dump($obj);exit;
            //echo $sessionID,':'.$crn;exit;

             //Not using incoming object instead using Last Request method of server
            //Not Validating System Data as per requirement

            //Get Last Request XML
            $sxml = $this->_soapServer->getLastRequest();
            
            $resp = $this->extractDataFromMiniSttXML($sxml);
            
            $sessionID = $resp['sessionKey'];
            if(!isset($resp['sessionKey']) || !$this->isLogin($sessionID)) {
                 return self::Exception("Invalid Login", App_ApiServer_Exchange::$INVALID_LOGIN);
             }
            
            //print_r($resp);exit('END');
            //Creating MVC API Object
            $ecsApi = new App_Api_ECS_Transactions();
            //print_r($resp);exit;
            $response = $ecsApi->transactionHistoryFromMVC(array(
                    'cardNumber' => $resp['cardNumber'],
                    'fetchFlag'  => '1',
                    'fromDate'   => '',
                    'noOfTransactions' => '10',
                    'toDate' => '',
                )
             );            
            $ns = self::HEADER_NAMESPACE;
            //var_dump($response);exit;
            $responseMessage = isset($response->errorDesc) ? $response->errorDesc : '';
            $strResponse  = $this->getResponseString('header');
            $strResponse .= '<'.$ns.':MiniStatementResponse><return><SessionID>'.$sessionID.'</SessionID><ResponseCode>'.$response->responseCode.'</ResponseCode>';
            if(isset($response->errorDesc)) {
                	$strResponse .= '<errorCode>'.$response->errorCode.'</errorCode><errorDesc>'.$response->errorDesc.'</errorDesc>';
            }
            if(isset($response->transactionHistory)) {
                        $strResponse .= '<NumberOfRecords>'.count($response->transactionHistory).'</NumberOfRecords>';
            
            foreach ($response->transactionHistory as $transHis) {
                $str .= '<'.$ns.':MiniStatementDetail><DateTime>'.$transHis->txndatetime.'</DateTime><Description>'.$transHis->description.'</Description><DrCrFlag>'.$transHis->drcrflag.'</DrCrFlag><Currency>'.$transHis->txncurrency.'</Currency><Amount>'.$transHis->txnamount.'</Amount><Cardnumber>'.$transHis->cardnumber.'</Cardnumber></'.$ns.':MiniStatementDetail>';
            }
            $strResponse .= $str;
            } else {
                        $strResponse .= '<NumberOfRecords>0</NumberOfRecords>';
            }
            
            $strResponse .= '</return></'.$ns.':MiniStatementResponse>';
            $strResponse .= $this->getResponseString('footer');

            $this->logAuthenticationMessage($sxml, $strResponse,'MiniStatementRequest',  self::TP_ID);
            //return the response and terminate the script, So Server will not able to generate its response 
            //Setup Header as not returning as part of application
            header ("Content-Type: text/xml; charset=utf-8");  
            
            print $strResponse;
            exit;//DO NOT DELETE THIS LINE
            //return $response;
        } catch (Exception $e) {
            //echo "<pre>";print_r($e);exit;
            App_Logger::log(serialize($e), Zend_Log::ERR);
            return $this->Exception(self::$INVALID_RESPONSE_MSG, self::$INVALID_RESPONSE);
        }
    }
    
    public function __call($name, $arguments) {
        
        App_Logger::log('Invalid Method called : '.$name, Zend_Log::ERR);
        App_Logger::log(serialize($arguments), Zend_Log::ERR);
        return self::Exception( self::$INVALID_METHOD_MSG, self::$INVALID_METHOD);
    }

}