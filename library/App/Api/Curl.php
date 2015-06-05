<?php
/**
 * CURL
 * Base Class for Sending CURL Request 
 * 
 * @copany Transerv
 * @author Vikram
 */
abstract class App_Api_Curl {

    private $_url = '';
    private $_request = '';
    private $_error = '';
    private $_response = '';
    private $_thirdPartyUserId = '4'; //Default set for KOTAK REMIT

    const TIMEOUT = 30;

    //Constructor
    public function __construct($url) {
        $this->_url = $url;
    }
    
    /**
     * sendRequest
     * Method to Send Request
     * @param type $request
     * @param type $method
     * @param type $tpId
     * @return boolean
     */
    protected function sendRequest($request, $method = '', $tpId = '') {
        $this->_request = $request;
        $resp = $this->initiate();
        //Log Request & Response
        App_Logger::apilog(array(
            'user_id' => (isset($tpId) && $tpId != '') ? $tpId : $this->_thirdPartyUserId,
            'method' => $method,
            'request' => $request,
            'response' => $this->getResponse()
        ));
        if ($resp === FALSE) {
            return FALSE;
        }
        return $resp;
    }

    /**
     * initate 
     * @return boolean
     * @throws App_Exception
     */
    private function initiate() {
        $buffer = '';
        if (!isset($this->_url) || $this->_url == '') {
            throw new App_Exception(__CLASS__ . ':' . __FUNCTION__ . ' URL not found');
        }

        if (!isset($this->_request) || $this->_request == '') {
            throw new App_Exception(__CLASS__ . ':' . __FUNCTION__ . ' Invalid request');
        }
        try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_request);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
//	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
//	curl_setopt($ch, CURLOPT_CAINFO, "/www/shmart/uploads/kotak_certificate.pem");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, App_Api_Curl::TIMEOUT);
        $buffer = curl_exec($ch);
        $this->setResponse($buffer);
//        print 'Request: '.  $this->_request . PHP_EOL;
//        print 'Response: '.  $buffer . PHP_EOL;
        curl_close($ch);
        } catch (Exception $e) {
            //Log Event
            $this->_error = $e->getMessage();
        }
        if (empty($buffer) || $buffer == '') {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * setResponse
     * Method to Set Response
     * @param <CURL RESPONSE> $resp
     */
    private function setResponse($resp) {
        $this->_response = $resp;
    }

    /**
     * getResponse
     * Method to get Response
     * @return <CURL RESPONSE>
     */
    public function getResponse() {
        return $this->_response;
    }

    /**
     * getErrorMsg
     * Method to get Error Message
     * @return type
     */
    public function getErrorMsg() {
        return $this->_error;
    }
}
