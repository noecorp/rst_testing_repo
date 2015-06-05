<?php

/**
 * Soap Class
 * Description of Soap
 * 
 * @copany Transerv
 * @author Vikram
 */
class App_Api_Soap extends SoapClient {
    
    //private $_namespace;
    
    private $_thirdPartyUserId;
    public $_errorMsg;

    public function __construct($wsdl = null, $options = null, $thirdPartyUserId = null) {
        
            /*if (array_key_exists('namespace', $options)) {
                $this->_namespace = $options['namespace'];
            }*/
            //For logging 
            $options['version'] = SOAP_1_1;
            //$options['connection_timeout'] = 9999;
            $this->_thirdPartyUserId = $thirdPartyUserId;
        
            parent::__construct($wsdl, $options);
    }
    
    public function soapCall($function_name, array $param) {
        try {
        $param = $this->filterParam($param);
        //print "Method :" .$function_name. "<br />";
        //print "Param :" .print_r($param). "<br />";
        //print $function_name. '<br />';
        $resp = $this->__soapCall($function_name, $param);

        
        
        App_Logger::apilog(array(
            'user_id'    => $this->_thirdPartyUserId,
            'method'        => $function_name,
            'request'       => $this->__getLastRequest(),
            'response'       => $this->__getLastResponse(),            
        ));
        
        if(!$this->isValid($resp)) {
            
            //print 'Invalid Soap';
            return false;
        }
        //print 'Valid Soap';        

        return $resp;
        } catch (SoapFault $e ) {
            //echo "<pre>";print_r($e);exit;

            App_Logger::apilog(array(
                'user_id'    => $this->_thirdPartyUserId,
                'method'        => $function_name,
                'request'       => $this->__getLastRequest(),
                'response'       => $this->__getLastResponse(),            
            ));            
            return false;
        }
    }
    
    public function isValid($response) {
        if(is_soap_fault($response) || $response == false) {
            $this->setErrorMsg($response->faultstring);
            return false;
        }
        return true;
    }
    
    protected function getErrorMsg() {
        return $this->_errorMsg;
    }
    
    protected function setErrorMsg($msg) {
        $this->_errorMsg = $msg;
    }
    /*
    public function ecsSoapCall($function_name, array $param) {
        try {
        $obj = $this->filterParamToObject($param);
        $paramObj = new SoapParam($obj, 'arg0');
        //$resp       = $this->$function_name($paramObj);
        $resp   = $this->__soapCall($function_name, array($paramObj));
        print 'Calling Method  ' . $function_name . '<br />';
        
        print '<pre>';print_r($resp);
        print '<pre>';print_r($this);
        var_dump($this);
        
        print 'Request XML ' . htmlentities($this->__getLastRequest()) . '<br />';
        print 'Request Header ' . $this->__getLastRequestHeaders();
        
        print '<br /><br /><br />';
        
        print 'Response XML ' . htmlentities($this->__getLastResponse()) . '<br />';
        //print 'Response XML ' . htmlentities($this->__getLastResponse()) . '<br />';
        print 'Response Header ' . $this->__getLastResponseHeaders() . '<br />';
        
        exit;
        
        $request    = $this->__getLastRequest();
        $response   = $this->__getLastResponse();
        //$exception = '';
        //return $resp;
        } catch (Exception $e ) {
            //Error Log
            $resp = false;
        }
        print 'Request : ' . htmlentities($request) . '<br /><br />';
        print 'Response : ' . htmlentities($response) . '<br /><br />';
        print '<pre>';print_r($resp);
        exit;
        App_Logger::apilog(array(
                'user_id'    => $this->_thirdPartyUserId,
                'method'        => $function_name,
                'request'       => $request,
                'response'       => $response,            
        )); 
        return $resp;

    }*/
    
    
      public function customSoapCall($function_name, array $param) {
        try {
        //$param = $this->filterParam($param);
        $resp = $this->__soapCall($function_name, $param);


        //return $resp;
        } catch (Exception $e ) {
            //print "****ERROR*****";
            //print $this->__getLastRequest();

            //print $e->getMessage();
            //exit;            
            //echo "<pre>";print_r($e);exit;
            $resp = false;
        }
        App_Logger::apilog(array(
            'user_id'    => $this->_thirdPartyUserId,
            'method'        => $function_name,
            'request'       => $this->__getLastRequest(),
            'response'       => $this->__getLastResponse(),            
        ));
        return $resp;
    }
    
    
    private function filterParam(array $param) {
        $arr = array();
        foreach ($param as $key => $val) {
            $arr[] = new SoapParam($val, $key);
        }
        return $arr;
    }
    
    private function filterParamToObject(array $param) {
        $obj = new stdClass();
        foreach ($param as $key => $val) {
            //$arr[] = new SoapParam($val, $key);
            $obj->$key = $val;
        }
        return $obj;
    }

    
        
    public function filterSoapCall($function_name, array $param) {
        try {
        $param = $this->formatArrayAsXML($param);
        $resp = $this->$function_name($param);
        App_Logger::apilog(array(
            'user_id'    => $this->_thirdPartyUserId,
            'method'        => $function_name,
            'request'       => $this->__getLastRequest(),
            'response'       => $this->__getLastResponse(),            
        ));
        
        if(!$this->isValid($resp)) {
            return false;
        }
        return $resp;
        } catch (SoapFault $e ) {
           App_Logger::apilog(array(
                'user_id'    => $this->_thirdPartyUserId,
                'method'        => $function_name,
                'request'       => $this->__getLastRequest(),
                'response'       => $this->__getLastResponse(),            
            ));            
            return false;
        }
    }
    
    public function formatArrayAsXML($arr) {
        if(empty($arr)) {
            return false;
        }
        $str = '';
        foreach ($arr as $key => $value) {
            if(is_array($value)) {
                $str .= '<'.$key.'>';
                foreach ($value as $k => $v) {
                    $str .= '<'.$k.'>'.$v.'</'.$k.'>';
                }
                $str.='</'.$key.'>';                
            } else {
                $str .= '<'.$key.'>'.$value.'</'.$key.'>';
            }
        }
        return $str;
    }
}