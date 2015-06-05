<?php
/**
 * Description of Server
 *
 * @author Vikram
 */
class App_ApiServer_Server extends Zend_Soap_Server {

    protected $logger;
    /**
     * Set and log the request.
     *
     * $request may be any of:
     * - DOMDocument; if so, then cast to XML
     * - DOMNode; if so, then grab owner document and cast to XML
     * - SimpleXMLElement; if so, then cast to XML
     * - stdClass; if so, calls __toString() and verifies XML
     * - string; if so, verifies XML
     *
     * @param DOMDocument|DOMNode|SimpleXMLElement|stdClass|string $request
     * @return Zend_Soap_Server
     */
    protected function _setRequest($request) {
        try {
        // Set the request.
        if($request != '') {
            parent::_setRequest($request);
       } 
        // Log the request.
        // Return the instance to allow for chaining.
        return $this;
        } catch (Exception $e) {
            App_Logger::log(serialize($e), Zend_Log::ERR);
            //Won't print anything
            //exit;
        }
    }
    
    
        /**
     * Handle a request
     *
     * Instantiates SoapServer object with options set in object, and
     * dispatches its handle() method.
     *
     * $request may be any of:
     * - DOMDocument; if so, then cast to XML
     * - DOMNode; if so, then grab owner document and cast to XML
     * - SimpleXMLElement; if so, then cast to XML
     * - stdClass; if so, calls __toString() and verifies XML
     * - string; if so, verifies XML
     *
     * If no request is passed, pulls request using php:://input (for
     * cross-platform compatability purposes).
     *
     * @param DOMDocument|DOMNode|SimpleXMLElement|stdClass|string $request Optional request
     * @return void|string
     */
    public function handle($request = null)
    {
        if (null === $request) {
            $request = file_get_contents('php://input');
        }

        /*if(empty($request)) {
            exit;
        }*/
        
        // Set Zend_Soap_Server error handler
        $displayErrorsOriginalState = $this->_initializeSoapErrorContext();

        $setRequestException = null;
        /**
         * @see Zend_Soap_Server_Exception
         */
        require_once 'Zend/Soap/Server/Exception.php';
        try {
            $this->_setRequest($request);
        } catch (Zend_Soap_Server_Exception $e) {
            $setRequestException = $e;
        }

        $soap = $this->_getSoap();

        ob_start();
        if($setRequestException instanceof Exception) {
            // Send SOAP fault message if we've catched exception
            $soap->fault("Sender", $setRequestException->getMessage());
        } else {
            try {
                $soap->handle($this->_request);
            } catch (Exception $e) {
                $fault = $this->fault($e);
                $soap->fault($fault->faultcode, $fault->faultstring);
            }
        }
        $this->_response = ob_get_clean();

        // Restore original error handler
        restore_error_handler();
        ini_set('display_errors', $displayErrorsOriginalState);
        $this->_response = $this->removeTags($this->_response);
        if (!$this->_returnResponse) {
            echo $this->_response;
            return;
        }
        return $this->_response;
    }
    
    private function removeTags($xml) {
        $xml = str_replace(' xsi:type="SOAP-ENC:Struct"', '', $xml);
        $xml = str_replace(' xsi:nil="true"', '', $xml);
        $xml = str_replace(' xsi:type="xsd:string"', '', $xml);		
        $xml = str_replace(' xsi:type="xsd:int"', '', $xml);
        $xml = str_replace(' xsi:type="xsd:float"', '', $xml);
        $xml = str_replace(' xmlns:xsd="http://www.w3.org/2001/XMLSchema"', '', $xml);
        $xml = str_replace(' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"', '', $xml);
        $xml = str_replace(' xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"', '', $xml);
        $xml = str_replace(' SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"', '', $xml);
        return $xml;
    }
    
    public function _setLogger($logger) {
        $this->logger = $logger;
    }
    
    public function _getLogger() {
        return $this->logger;
    }

    
}

?>
