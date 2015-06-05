<?php
namespace App\Messaging\Transport\SMS;
/**
 * ValueFirst SMS
 *
 * ValueFirst SMS transport
 *
 * @category    App
 * @package     Zend_Http_Client
 * @subpackage  Transport
 * @author      Vikram Singh
 */
//class App_SMS_Transport_ValueFirst extends Zend_Http_Client
class ValueFirst //extends \Zend_Http_Client
{


    /**
     * SMS hostname or i.p.
     *
     * @var string
     */
    protected $_host;


    /**
     * SMS Provider Username
     * @var string
     */
    protected $_username;

    /**
     * SMS Provider Password
     * @var string
     */
    protected $_password;
    
    /**
     * SMS Sender Name (Company Name)
     * @var string
     */    
    protected $_from = 'Shmart';

    /**
     * SMS Template
     * @var string
     */    
    protected $_view;

    /**
     * SMS Message (Error/Success Message)
     * @var string
     */    
    protected $_msg;
    
    /**
     * SMS Message
     * @var string
     */    
    protected $_smsMessage;

    /**
     * Default SMS Template
     * @var string
     */    
    protected static $_defaultView;
    
    private $_mobile;
    
    private $_message;
    
    /**
     * Default SMS Template
     * @var string
     */    
    protected $_data = array();
    



    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        $configArray = \App_Webservice::get('sms_auth');
        
        if(!isset($configArray['gateway_url']) || $configArray['gateway_url'] == '') {
            throw new \Exception ("SMS Transport: gateway_url not found!!");
        }
        
        if(!isset($configArray['auth_user']) || $configArray['auth_user'] == '') {
            throw new \Exception ("SMS Transport: auth_user not found!!");
        }
        
        if(!isset($configArray['auth_pass']) || $configArray['auth_pass'] == '') {
            throw new \Exception ("SMS Transport: auth_pass not found!!");
        }
        
        $this->_host        = $configArray['gateway_url'];
        $this->_username    = $configArray['auth_user'];
        $this->_password    = $configArray['auth_pass'];
        //echo "<pre>";print_r($configArray);exit;
        
    }


    /**
     * Send SMS and Generate response
     *
     * @to reciver mobile number
     * @return bool (true/false)
     */
    public function _generateResponse()
    {
        $logger = new \ShmartLogger(\ShmartLogger::LOG_SMS);
        if($this->getMobile() == '') {
            throw new \Exception ("No mobile Number provided.");
        }
        //$url = 'http://api.myvaluefirst.com/psms/servlet/psms.Eservice2?data=%3C?xml%20version=%221.0%22%20encoding=%22ISO-8859-%22?%3E%3C!DOCTYPE%20 MESSAGE %20SYSTEM%20%22http://127.0.0.1/psms/dtd/message.dtd%22%20%3E%3CMESSAGE%3E%3CUSER%20USERNAME=%22'.$this->_username.'%22%20PASWORD=%22'.$this->_password.'%22/%3E%3CSMS%20%20UDH=%220%22%20CODING=%221%22%20TEXT=%22The%20flight%20%23&btnG;%20&lt;101&gt;%20&quot;DEL&quot;%20to%20&quot;BLR&quot;%20is%20delayed%20and%20it&apos;s%20%20revised%20time%20will%20be%20informed%20later.%20Have%20a%20nice%20day!%22%20PROPERTY=%220%22%20ID=%221%22%3E%3CADDRESS%20FROM=%22'.$this->_from.'%22%20TO=%22'.$to.'%22%20SEQ=%221%22%20TAG=%22some%20clientside%20random%20data%22%20/%3E%3C/SMS%3E%3C/MESSAGE%3E&action=send';
        //$url = 'http://api.myvaluefirst.com/psms/servlet/psms.Eservice2';
        /*
         * print $this->_host .'?' . 'data=<?xml version="1.0" encoding="ISO-8859-1"?><!DOCTYPE MESSAGE SYSTEM "http://127.0.0.1/psms/dtd/message.dtd" ><MESSAGE><USER USERNAME="'.$this->_username.'" PASSWORD="'.$this->_password.'" /><SMS UDH="0" CODING="1" TEXT="'.$this->_smsMessage.'" PROPERTY="0" ID="'.rand(1,99999).'"><ADDRESS FROM="'.$this->_from.'" TO="'.$to.'" SEQ="1" TAG="Random" /></SMS></MESSAGE>&action=send';
         */
        //$this->_from = "PAYUMY";
        $data = 'data=<?xml version="1.0" encoding="ISO-8859-1"?><!DOCTYPE MESSAGE SYSTEM "http://127.0.0.1/psms/dtd/message.dtd" ><MESSAGE><USER USERNAME="'.$this->_username.'" PASSWORD="'.$this->_password.'" /><SMS UDH="0" CODING="1" TEXT="'.$this->getMessage().'" PROPERTY="0" ID="'.rand(1,99999).'"><ADDRESS FROM="'.$this->_from.'" TO="'.$this->getMobile().'" SEQ="1" TAG="Random" /></SMS></MESSAGE>&action=send';        
        $this->_from = $this->getFrom();
        $ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,  $this->_host);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $logger->log($data);
	$buffer = curl_exec($ch);
        $logger->log($buffer);
      //  $buffer='';
        if(empty ($buffer)) {
            //Retry
            $buffer = curl_exec($ch);
            if(empty ($buffer)) {
                return false;
              //  throw new \Exception('No Response received');
            }
            $logger->log($buffer);            
        }
        curl_close($ch);
        return $this->_parseResponse($buffer);
	
	

    }

    /**
     *  Parsing Response
     *  Parsing response from API
     * @param type $response
     * @return boolean
     */
    private function _parseResponse($response)
    {
	$sXML = new \SimpleXMLElement($response);
        if(is_array($this->_data)) {
            $to = isset($this->_data['mobile1']) ? $this->_data['mobile1'] : '';
        } else if(is_object($this->_data)) {
            $to = isset($this->_data->mobile1) ? $this->_data->mobile1 : '';
        }        
        $flg = false;
        $msg = '';
	if(isset($sXML->GUID->ERROR)) {	
            //LOG SMS Error
            $msg =  $this->_getErrorCode($sXML->GUID->ERROR['CODE']);
            $this->_setErrorMsg($msg);
            $flg = false;
	} elseif(isset($sXML->Err)) {
            $desc = $sXML->Err->attributes();
            $msg =  $this->_getErrorCode($desc['Code']);
            $this->_setErrorMsg($msg);            
            $flg = false;
	} else {
            //LOG SMS 
            //App_Logger::log($msg, ZEND_)
            $flg = true;
	}
        $resArray['to'] = $this->getMobile();
        $resArray['body'] = $this->getMessage();
        $resArray['exception'] = $msg;
        $resArray['flag'] = $flg;
        \App_Logger::smslog($resArray);
        if(!$flg) {
            throw new \Exception($msg);
        }
        return $flg;

    }

   
    /**
     * get Error Message on the basis of Error Code
     * @param type $code Error Code
     * @return string Error Message
     */
    private function _getErrorCode($code)
    {
            $errorCodeArray = array(
          '52992'  => 'Username / Password incorrect',
          '57089'  => 'Contract expired',
          '57090'  => 'User Credit expired',
          '57091'  => 'User disabled',
          '65280'  => 'Service is temporarily unavailable',
          '65535'  => 'The specified message does not conform to DTD',
          '0'  => 'SMS submitted success NO Error',
          '28673'  => 'Destination number not numeric',
          '28674'  => 'Destination number empty',
          '28675'  => '28675',
          '28676'  => 'SMS over 160 character, Non-compliant message',
          '28677'  => 'UDH is invalid',
          '28678'  => 'Coding is invalid',
          '28679'  => 'SMS text is empty',
          '28680'  => 'Invalid sender ID',
          '28681'  => 'Invalid message, Duplicate message, Submit failed',
          '28682'  => 'Invalid Receiver ID',
          '28683'  => 'Invalid Date time for message Schedule',
          '8448'  => 'Message delivered successfully',
          '8449'  => 'Message failed',
          '8450'  => 'Message ID is invalid',
          '13568'  => 'Command Completed Successfully',
          '13569'  => 'Cannot update/delete schedule since it has already been processed',
          '13570'  => 'Cannot update schedule since the new date-time parameter is incorrect',
          '13571'  => 'Invalid SMS ID/GUID',
          '13572'  => 'Invalid Status type for schedule search query. The status strings can be PROCESSED, PENDING and ERROR.',
          '13573'  => 'Invalid date time parameter for schedule search query',
          '13574'  => 'Invalid GUID for GUID search query',
          '13575'  => 'Invalid command action'
        );
        $code = trim($code);
        if(isset($errorCodeArray[$code])) {
            return $errorCodeArray[$code];
        } else {
            return 'Code:' . $code . 'not found';
        }
    }
    
    /**
     * Get Error Message
     * @return type
     */
    public function _getErrorMsg()
    {
        return $this->_msg;
    }
    
    /**
     * Set Error Message
     * @param type $msg
     */
    public function _setErrorMsg($msg)
    {
        $this->_msg = $msg;
    }
    
    /**
     * Set Mobile
     * @param type $mobile
     */
    public function setMobile($mobile) {
        $this->mobile = $mobile;
    }
    
    /**
     * Set Message
     * @param type $message
     */
    public function setMessage($message) {
        
        $find_arr = array("&", "'", '"', ">", "<");
        $replace_arr   = array("%26amp%3B", "%26apos%3B", "%26quot%3B", "%26gt%3B", "%26lt%3B");

        $message = str_replace($find_arr, $replace_arr, $message);
        $this->method = $message;
    }
    
    /**
     * Get Mobile Name
     * @return <Mobile>
     */
    private function getMobile() {
        return $this->mobile;
    }
    
    /**
     * Get Message
     * @return <Message>
     */
    private function getMessage() {
        return $this->method;
    }
    
     /**
     * Set From
     * @param type $mobile
     */
    public function setFrom($from = "Shmart") {
        $this->_from = $from;
    }
     /**
     * Get From Name
     * @return <Mobile>
     */
    private function getFrom() {
        return $this->_from;
    }
}