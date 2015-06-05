<?php
class App_Socket_MVC_Transaction extends App_Socket_MVC_Authentificator {


    public static $auditNumber;
    
    private $attempts = 0;
    private $maxAttempts = 3;
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * createNewSession
     * 
     * App_Api_Exception exception. Returns void.
     *
     * @return \App_Api_ECS_Authentificator
     * */
    public function sendDataToMVC($iso) {
        
        try {
            //$this->initSession();
           $this->createConnectionObject();
           // if($this->initSession() === true) {
           //print 'HERE';
                return $this->getSocketObject()->isoCall("Transaction", $iso);    
                //return $resp;
               // print 'AAAAA :'.$resp;//exit;
                //return $resp;
                //echo '**'. $resp . '**';exit;
                $respISO = $this->getSocketObject()->getLastResponse();
                //print $resp . ' : ' . $respISO;exit;
                  //return '**' .$respISO . '**';exit;
                //print "Response Recivedxx :" . $respISO . "\n";
                $this->parseResponseISO($respISO);
                return $respISO;
                
               /* if ($this->isResponseSuccessful() === true) {
                    return $respISO;
                    //return true;
                } else {
                    //In case of failure
                    return $respISO;
                }*/

            //}
            //return $respISO;                
            //return false;
        } catch (Exception $e) {
            print '<pre>';
            print_r($e);exit;
            return false;
        }
    }

}
