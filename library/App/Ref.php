<?php
/**
 * Logger Wrapper to integrate SNS on errors and info messages
 *
 * @category App
 * @package App_Logger
 * @copyright company
 */
class App_Ref
{
    
    private $_label;
    private $_userId;
    private $_userType;
    private $_method;
    private $_resTime;
    private $_reqTime;
    private $_userIP;
    private $_excp;
    private $_refId;
    private $_response;
    private $_request;
    private $_id;

    public function __setLabel($label) {
        $this->_label = $label;
    }
    
    public function __getLabel() {
        return $this->_label;
    }
    
    public function __setUserId($userId) {
        $this->_userId = $userId;
    }
    
    public function __getUserId() {
        return $this->_userId;
    }
    
    public function __setUserType($uType) {
        $this->_userType = $uType;
    }
    
    public function __getUserType() {
        return $this->_userType;
    }
    
    public function __setMethod($label) {
        $this->_method = $label;
    }
    
    public function __getMethod() {
        return $this->_method;
    }
    
    public function __setRequest($label) {
        $this->_request = $label;
    }
    
    public function __getRequest() {
        return $this->_request;
    }
    
    public function __setResponse($label) {
        $this->_response = $label;
    }
    
    public function __getResponse() {
        return $this->_response;
    }
    
    public function __setRefId($ref) {
        $this->_refId = $ref;
    }
    
    public function __getRefId() {
        return $this->_reqId;
    }
    
    public function __setException($exce) {
        $this->_excp = $exce;
    }
    
    public function __getException() {
        return $this->_excp;
    }
    
    public function __setUserIP($label) {
        $this->_userIP = $label;
    }
    
    public function __getUserIP() {
        return $this->_userIP;
    }
    
    public function __setRequestTime() {
        $time = gettimeofday();
        if(isset($time['usec'])){
        $this->_reqTime = date('Y-m-d H:i:s').'.'.$time['usec'];
        }else{
         $this->_reqTime = date('Y-m-d H:i:s');   
        }
    }
    
    public function __getRequestTime() {
        return $this->_reqTime;
    }
    
    public function __setResponseTime() {
        $time = gettimeofday();
        if(isset($time['usec'])){
        $this->_resTime = date('Y-m-d H:i:s').'.'.$time['usec'];
        }else{
         $this->_resTime = date('Y-m-d H:i:s');   
        }
    }
    
    public function __getResponseTime() {
        return $this->_resTime;
    }
    
    public function __setID($id) {
        $this->_id = $id;
    }
    
    public function __getID() {
        return $this->_id;
    }
    
    
    public function log($param) {

        $this->_label = isset($param['label']) ? $param['label'] : $this->_label;
        $this->_userId = isset($param['userId']) ? $param['userId'] : $this->_userId;
        $this->_userType = isset($param['userType']) ? $param['userType'] : $this->_userType;
        $this->_method= isset($param['method']) ? $param['method'] : $this->_method;
        $this->_resTime= isset($param['resTime']) ? $param['resTime'] : $this->_resTime;
        $this->_reqTime= isset($param['reqTime']) ? $param['reqTime'] : $this->_reqTime;
        $this->_userIP= isset($param['userIP']) ? $param['userIP'] : $this->_userIP;
        $this->_excp= isset($param['excp']) ? $param['excp'] : $this->_excp;
        $this->_refId= isset($param['refId']) ? $param['refId'] : $this->_refId;
        $this->_response= isset($param['response']) ? $param['response'] : $this->_response;
        $this->_request= isset($param['request']) ? $param['request'] : $this->_request;
        $ref = new Reference();        
        if(empty($this->_reqTime)) {
            $this->__setRequestTime();
        }
        if(empty($this->_resTime)) {
            $this->__setResponseTime();
        }
        if(empty($this->_userIP)) {
            $this->_userIP = $ref->formatIpAddress(Util::getIP());
        } else {
            $this->_userIP = $ref->formatIpAddress($this->_userIP);            
        }
        $param = array(
	    'date_created' => date('Y-m-d H:i:s'),
            'label' => $this->_label,
            'user_id' => $this->_userId,
            'user_type' => $this->_userType,
            'method' => $this->_method,
            'request' => $ref->addFilter($this->_request),
            'time_request' => $this->_reqTime,
            'response' => $ref->addFilter($this->_response),
            'time_response' => $this->_resTime,
            'exception' => $ref->addFilter($this->_excp),
            'ref_id' => $this->_refId,
            'user_ip' => $this->_userIP
        );
        $id = $this->__getID();
        if(!empty($id)) {
            $param['id'] = $id;
        }

        $id = $ref->logRef($param);
        $this->__setID($id);
    }
    
    
}


