<?php
namespace App\Messaging\Transport;

/**
 * SMS
 * Class to handle SMS related functionality
 * @author Vikram
 */
class SMS extends \App\Messaging\Transport\SMS\ValueFirst
{
    
   public $mobile;
   public $message;
   public $transport;
   private $product;
   private $from = 'Shmart';
   public $bceTemplate = 'bce_auth_code';
   
   //public function __construct() {
       //$this->transport = new \App\Messaging();
   //}
    
     /**
     * SendAuthSMS
     * @param type $username
     * @param type $authcode
     * @throws Exception
     */
    public function send()
    {
        try {
            $sms = new \App\Messaging\Transport\SMS\ValueFirst();
            $sms->setMobile($this->getMobile());
            $sms->setMessage($this->getMessage());
            $sms->setFrom($this->from);
           if(!$sms->_generateResponse()) {
               $this->setError($sms->_getErrorMsg());
                 throw new \Exception('Unable to send SMS'); 
               //return false;
              // Re try to send SMS 
             //$reSendResponse = $this->reSend();
               
               //if(!$reSendResponse){
              //   $this->setError($sms->_getErrorMsg()); 
               //  throw new \Exception('Re Send Response failed.');  
//               }else{
//                 return true;   
//               }
            } else {
                //$this->setMessage('SMS Sent Successfully');
                return true;
            }
        } catch (\Exception $e) {
             $this->setError($e->getMessage());
             $message = $e->getMessage();
             $message = (empty($message)) ? 'SMS Not sent' : $message;
            //return false;
            throw new \Exception($message);
        }
        
    }

    /**
     * SetView
     * Method to set view and product
     * @param type $product
     */
    public function setView($product)
    {
        if($this->_view === null)
        {
            $this->_view = new \Zend_View();
        } 
        $this->product = $product;        
    }
    
    /**
     * setViewParam
     * Setting view parameter
     * @param type $name
     * @param type $value
     */    
    public function setViewParam($name, $value)
    {
        if($this->_view != null)
        {
            $this->_view->$name = $value;
        } 
    }    

    /**
     * setParam
     * Setting Param of the Class
     * @param type $name
     * @param type $value
     */    
    public function setParam($name, $value)
    {
        //print "setting $name : " . $value;
        if($this->_view != null)
        {
            $this->_view->$name = $value;
        } 
    }    
    

    /**
     * getMobile
     * Method to get mobile name
     * @return type
     */
    public function getMobile()
    {
        if(isset($this->_view->mobile)) {
         return $this->_view->mobile;
        } 
    }    
    
    /**
     * getMessage
     * Method to SMS body
     * @return type
     */
    public function getMessage()
    {
        $tPath =  str_replace('App\Messaging\Transport', 'App\Messaging\Transport\Templates\SMS', $this->product);
        $tPath = str_replace('\\', DIRECTORY_SEPARATOR, $tPath);
        $this->_view->setScriptPath(dirname(APPLICATION_PATH) . '/library/'.$tPath.'/');
        return $this->_view->render($this->getTemplate());
    }    
    
    /**
     * setTemplate
     * Method to setTemplate
     * @param type $name
     */
    public function setTemplate($name)
    {
         $this->_template = $name;
    }
    
    /**
     * getTemplate
     * Method to get Template
     * @return <Template Name>
     */
    private function getTemplate()
    {
        ### If Terms and condition is not selected then set templated as per requirment.
        if(isset($this->_view->showBceContract) && intval($this->_view->showBceContract)==1 &&  strtolower(CURRENT_MODULE)=='agent')
        {
            $this->bceTemplate = strtolower(CURRENT_MODULE).'/'.$this->bceTemplate;
            $this->setTemplate($this->bceTemplate);
        }

        if($this->_template != null) {
            $template = str_replace('\\', DIRECTORY_SEPARATOR, $this->_template);
            return $template.'.phtml';
        }
        //Fetching template without setting it. Need to throw error
        return false;
    }    
    
    /**
     * setError
     * Set Error Message
     * @param type $error
     */
    private function setError($error)
    {
        \App_Logger::log($error, \Zend_Log::ERR);        
        $this->_error = $error;
    }
    
    /**
     * getError
     * Get Error Message
     * @return <Error Message>
     */
    public function getError()
    {
        return $this->_error;
    }
    
     /**
     * getFrom
     * Method to get mobile name
     * @return type
     */
    public function getFrom()
    {
        if(isset($this->_view->from)) {
         return $this->_view->from;
        } 
    }
    
    /**
     * setParam
     * Setting Param of the Class
     * @param type $name
     * @param type $value
     */    
    public function setFrom($from)
    {
       $this->from = $from; 
    }
    
    public function reSend()
    {
        try {
            $sms = new \App\Messaging\Transport\SMS\ValueFirst();
            $sms->setMobile($this->getMobile());
            $sms->setMessage($this->getMessage());
            $sms->setFrom($this->from);
           if(!$sms->_generateResponse()) {
              return false;
            } else {
                //SMS Sent Successfully;
                return true;
            }
        } catch (\Exception $e) {
          //  $this->setError($e->getMessage());
            return false;
           // throw new \Exception('SMS Not sent');
        }
        
    }
}