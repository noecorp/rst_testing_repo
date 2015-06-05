<?php

/**
 * Soap Class
 * Description of Soap
 * 
 * @copany Transerv
 * @author Vikram
 */
class App_Api_ZendSoap extends Zend_Soap_Client {
    
    //private $_namespace;
    
    private $_thirdPartyUserId;
    private $_errorMsg;

    public function __construct($wsdl = null, $options = null, $thirdPartyUserId = null) {
        
            /*if (array_key_exists('namespace', $options)) {
                $this->_namespace = $options['namespace'];
            }*/
            //echo "<pre>";print_r($options);
            //For logging 
            $this->_thirdPartyUserId = $thirdPartyUserId;
        
            parent::__construct($wsdl, $options);
    }
    
    /*public function soapCall($function_name, array $param) {
        try {
        $param = $this->filterParam($param);
        $resp = $this->__soapCall($function_name, $param);
        App_Logger::apilog(array(
            'user_id'    => $this->_thirdPartyUserId,
            'method'        => $function_name,
            'request'       => $this->__getLastRequest(),
            'response'       => $this->__getLastResponse(),            
        ));

        return $resp;
        } catch (Exception $e ) {
            //print "****ERROR*****";
            //print $this->__getLastRequest();

            //print $e->getMessage(); 
            //exit;            
            //echo "<pre>";print_r($e);exit;
            App_Logger::apilog(array(
                'user_id'    => $this->_thirdPartyUserId,
                'method'        => $function_name,
                'request'       => $this->__getLastRequest(),
                'response'       => $this->__getLastResponse(),            
            ));            
            return false;
        }
    }*/
    
    public function ecsSoapCall($function_name, array $param) {
        try {
        $obj = $this->filterParamToObject($param);
        $paramObj = new SoapParam($obj, 'arg0');
        //$resp       = $this->$function_name($paramObj);
        $resp   = $this->$function_name($paramObj);
       // $request    = $this->getLastRequest();
        //$response   = $this->getLastResponse();

        //echo "<pre>";print_r(htmlentities($this->getLastRequest()));echo "<pre>";print_r(htmlentities($this->getLastResponse()));
        //$exception = '';
        //return $resp;
        } catch (Exception $e ) {
//            echo "<pre>";print_r($e);exit;
        App_Logger::apilog(array(
                'user_id'    => $this->_thirdPartyUserId,
                'method'        => $function_name,
                'request'       => $this->getLastRequest(),
                'response'       => $e->getMessage(),            
        ));             
            throw new Exception ($e->getMessage());
            $resp = false;
        }
       // print 'Request : ' . htmlentities($request) . '<br /><br />';
       // print 'Response : ' . htmlentities($response) . '<br /><br />';
       // print '<pre>';print_r($resp);
//        exit;
        App_Logger::apilog(array(
                'user_id'    => $this->_thirdPartyUserId,
                'method'        => $function_name,
                'request'       => $this->getLastRequest(),
                'response'       => $this->getLastResponse(),            
        )); 
        //echo $function_name . '<br />';
        //echo "<pre>";print_r(htmlentities($this->getLastRequest()));echo "<pre>";print_r(htmlentities($this->getLastResponse()));
        //exit;
        //echo "<pre>";print_r($resp);
        return $resp;

    }
    
    
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
            $this->_errorMsg = $e->getMessage();
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
    
    public function getError() {
        return $this->_errorMsg;
    }

    

}
