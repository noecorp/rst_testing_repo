<?php
class App_Socket_MVC_Authentificator extends App_Socket_MVC {


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
     * @return Bool
     * */
    public function createNewSession() {
        $config = App_Webservice::get('mvc_iso');
        try {
            $iso = $this->generateLoginISO(array());
            //echo $iso;exit('END');
            //$socket = new App_Socket_Client($config['ip'], $config['port'], self::TPUSERID);
            $this->createConnection($config['ip'], $config['port']);
            
            $resp = $this->getSocketObject()->isoCall("Login", $iso);
            
            $respISO = $this->getSocketObject()->getLastResponse();

            $this->parseResponseISO($respISO);
            return true;
            if ($this->isResponseSuccessful() === true) {
                //exit('HERE');
                //App_Socket_ECS_Authentificator::$auditNumber = $this->
                return true;
            } else {
                //exit('HERE2');
                //echo 'In here';exit;
                if ($this->attempts < $this->maxAttempts) {
                    $this->attempts++;
                    return $this->createNewSession();
                }
            }
            return false;
        } catch (Exception $e) {
            print '<pre>';
            print_r($e);
            return false;
        }
    }

    /**
     * initSession
     * Validate and return new or validated session
     * @return Bool
     */
    public function initSession() {
        if(!is_a($this->getSocketObject(), 'SoapClient')) {
            return $this->createNewSession();
        }
        
        $iso = $this->generateISO(array(
            'p70'   => '301'
        ));
        $this->getSocketObject()->isoCall("Echo", $iso);        
        $this->parseResponseISO($respISO);
            
            if ($this->isResponseSuccessful() === true) {
                //App_Socket_ECS_Authentificator::$auditNumber = $this->
                return true;
            } else {        
                return $this->createNewSession();            
            }
    }
    
    
    public function createConnectionObject() {
         $config = App_Webservice::get('mvc_iso');
         $this->createConnection($config['ip'], $config['port']);

    }
    

}
