<?php
namespace App\Messaging\Transport;


/**
 * Mail
 * Class used to handle mail related functionality
 * @author Vikram
 */
class Mail extends \Zend_Mail
{

    private $_view=null;
    private $_template=null;
    private $product=null;
    private $_error=null;
    private $_use_custom_transport=FALSE;

    /**
     * Mail Contructor
     * @param type $charset
     */
    public function __construct($charset = null) {
        parent::__construct($charset);
        $this->setFrom(\App_DI_Container::get('ConfigObject')->mail->sender->fromemail);
    }
    
    /**
     * SendMail
     * Method to send mail
     * @return boolean
     * @throws Exception
     */
    public function sendMail() {
        if ($this->_use_custom_transport) {
//            $config = array(
//                'ssl' => 'tls', 
//                'port' => \App_DI_Container::get('ConfigObject')->mail->mandrill->port, 
//                'auth' => 'login', 
//                'username' => \App_DI_Container::get('ConfigObject')->mail->mandrill->username, 
//                'password' => \App_DI_Container::get('ConfigObject')->mail->mandrill->password
//            );           
            $ssl = \App_DI_Container::get('ConfigObject')->mail->mandrill->ssl;
            $config = array(
                'port' => \App_DI_Container::get('ConfigObject')->mail->mandrill->port,
                'auth' => \App_DI_Container::get('ConfigObject')->mail->mandrill->auth,
                'username' => \App_DI_Container::get('ConfigObject')->mail->mandrill->username,
                'password' => \App_DI_Container::get('ConfigObject')->mail->mandrill->password
            );
            if (!empty($ssl)) {
                $config['ssl'] = $ssl;
            }

            $tr = new \Zend_Mail_Transport_Smtp(\App_DI_Container::get('ConfigObject')->mail->mandrill->smtp, $config);
            self::setDefaultTransport($tr);
        }
        if($this->getRecipients() == '') {
            throw new Exception('No sender registered');
        }
        try {
            \App_Logger::emaillog(array(
                'to'            => $this->getRecipients(),
                'from'          => $this->getFrom(),
                'subject'       => $this->getSubject(),
                'body'          => $this->getMessage(),  
            ));

            $this->setBodyHtml($this->getMessage(),$this->getCharset(), \Zend_Mime::ENCODING_QUOTEDPRINTABLE);
            $this->send();
            //print 'Mail send successfully';
        } catch (\Exception $e) {
            //echo '<pre>';print_r($e);exit;
	    \App_Logger::log(serialize($e), \Zend_Log::ERR);
            $this->setError($e->getMessage());
            return false;
        }
        return true;
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
            //$this->_view->setScriptPath(dirname(APPLICATION_PATH) . '/library/'.$product);
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
        if($this->_view != null)
        {
            $this->_view->$name = $value;
        } 
    }
    
    /**
     * getMessage
     * Getting Mail Message
     * @return type
     */
    public function getMessage()
    {
        $tPath =  str_replace('App\Messaging\Transport', 'App\Messaging\Transport\Templates\Mail', $this->product);
        $tPath = str_replace('\\', DIRECTORY_SEPARATOR, $tPath);        
        $this->_view->setScriptPath(dirname(APPLICATION_PATH) . '/library/'.$tPath.'/');
        return $this->_view->render($this->getTemplate());
    }    
    
    /**
     * Set Template
     * Method to set template
     * @param type $name
     */
    public function setTemplate($name)
    {
         $this->_template = $name;
    }
    
    /**
     * getTemplate
     * Get View Template 
     * @return boolean
     */
    private function getTemplate()
    {
        if($this->_template != null) {
            $template = str_replace('\\', DIRECTORY_SEPARATOR, $this->_template);
            return $template.'.phtml';
        }
        //Fetching template without setting it. Need to throw error
        return false;
    }      
    
    /**
     * SetError
     * Method to set error message
     * @param type $error
     */
    public function setError($error)
    {
        $this->_error = $error;
    }
    
    /**
     * getError
     * Method to get error message
     * @return type
     */
    public function getError()
    {
        return $this->_error;
    }
    
    public function setCustomOption($opt)
    {
        $this->_use_custom_transport = $opt;
    }
}