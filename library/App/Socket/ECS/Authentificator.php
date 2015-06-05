<?php
class App_Socket_ECS_Authentificator extends App_Socket_ECS {


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
    public function createNewSession() {
        $config = App_Webservice::get('ecs_iso');
        try {
            $iso = $this->generateLoginISO(array());
            //echo $iso;exit('END');
            //$socket = new App_Socket_Client($config['ip'], $config['port'], self::TPUSERID);
            $this->createConnection($config['ip'], $config['port']);
            
            $this->getSocketObject()->isoCall("Login", $iso);
            $respISO = $this->getSocketObject()->getLastResponse();
            //$this->validateISO()//Validate ISO Response
            $this->parseResponseISO($respISO);
            
            if ($this->isResponseSuccessful() === true) {
                //App_Socket_ECS_Authentificator::$auditNumber = $this->
                return true;
            } else {
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
     * @return \App_Api_ECS_Authentificator
     */
    public function initSession() {
        if(!is_a($this->getSocketObject(), 'SoapClient')) {
            return $this->createNewSession();
        }
        
        $iso = $this->generateISO(array(
            'p70'   => '301'
        ));
    }
    

}
