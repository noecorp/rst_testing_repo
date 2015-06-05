<?php
/**
 * Logger Wrapper to integrate SNS on errors and info messages
 *
 * @category App
 * @package App_Logger
 * @copyright company
 */
class App_Logger
{
    /**
     * Write messages to the log and send notifications to email if configured
     *
     * @param string $msg
     * @param int
     * @return void
     */
    public static function log($msg, $level = Zend_Log::INFO){
        
        if(is_array($msg)) {
            $msg = self::serialzeArray($msg);
        }
        
        App_DI_Container::get('GeneralLog')->log($msg, $level);
  /*      
        $config = App_DI_Container::get('ConfigObject');
        if($config->system->notifications->notify_on_errors){
            //Send mail alert for Error's only 
            if($level == Zend_Log::ERR) {
                $command = new App_Command_SendEmail();
                $command->onCommand('sendEmail', array(
                    'type' => 'Notification',
                    'recipients' => $config->system->notifications->recipients->toArray(),
                    'message' => $msg,
                    'level' => $level
                ));
            }
        }
    */    
        //$command = new Log();
        
//        $command->insert(array(
//           'level'      => $level,
//           'exception'  => $msg,
//           'date_added' => new Zend_Db_Expr('NOW()')
//        ));
        $logger = new ShmartLogger(ShmartLogger::LOG);
        $logger->log(array(
            'Level' => $level,
            'Exception' => $msg,
            'Date Time' => date('Y-m-d h:i:s')
        ));
        
    }
    
    
    /**
     * Write messages to the log and 
     * send notifications to email if configured
     *
     * @param array $resArray
     * @return void
     */
    public static function apilog(array $resArray){
        //Logger can't afford errors
        //set variable
        $userId     = isset($resArray['user_id']) ? $resArray['user_id'] : '';
        $method     = isset($resArray['method']) ? $resArray['method'] : '';
        $requestData    = isset($resArray['request']) ?  $resArray['request'] : '';
        $responseData   = isset($resArray['response']) ?  $resArray['response'] : '';
        
        /*if($userId == 4) {
            $command = new ApiSoapCall();
             $inputArr = array(
                'tp_user_id'    => $userId,
                'method'        => $method,
                'request'       => Util::apiSerialize($request),
                'response'      => Util::apiSerialize($response),
                'user_ip'       => Util::getIP(),
                'date_created' => new Zend_Db_Expr('NOW()')
        );
            $command->insert($inputArr);
            return ;
        }*/
        
        try {
            //To supress php warning in case of invalid xml
            libxml_use_internal_errors(true);            
            $request = self::extractRequestXML($requestData,$method);
            $response = self::extractRequestXML($responseData,$method.'Response');
            
            if($response->return->ResponseCode == '0') {
                foreach ($response->return as $key)
                {
                    $keys = (array) $key;
                    $res = array();

                    foreach(array_keys($keys) as $val) {
                        if(in_array($val, Util::apiResponseArray())) {
                            $res[$val] = $keys[$val];
                        }
                    }
                }

                $failedreq = array();
                foreach ((array) $request as $key => $val)
                {
                    if(in_array($key, Util::apiRequestArray())) {
                        $failedreq[$key] = $val;
                    }
                }
                $response = (object) $res;
                $request = (object) $failedreq;
            }
        } catch (Exception $e) {
            $request = $requestData;
            $response = $responseData;
        }
        //Handling XML Error
        if(count(libxml_get_errors()) > 0) {
            $request = $requestData;
            $response = $responseData;
        }        
        
        $logger = new ShmartLogger(ShmartLogger::LOG_API_SOAP);
        
        $logger->log(array(
                'user_id'    => $userId,
                'method'        => $method,
                'request'       => $request,
                'response'       => $response,
            ));
        
//        if($config->system->api->add_call_on_db){
//            $command = new ApiSoapCall();
//            $command->log(array(
//                'user_id'    => $userId,
//                'method'        => $method,
//                'request'       => $request,
//                'response'       => $response,
//                //'source'       => $source,
//            ));
//        }
    }
    
    /**
     * Write messages to the log and 
     * send notifications to email if configured
     *
     * @param array $resArray
     * @return void
     */
    public static function isolog(array $resArray){
        
        //App_DI_Container::get('ApiLog')->log(array $resArray);
        $userId     = isset($resArray['user_id']) ? $resArray['user_id'] : '';
        $method     = isset($resArray['method']) ? $resArray['method'] : '';
        $request    = isset($resArray['request']) ?  $resArray['request'] : '';
        $response   = isset($resArray['response']) ?  $resArray['response'] : '';
        $response_message   = isset($resArray['response_message']) ?  $resArray['response_message'] : '';
        $exception  = isset($resArray['exception']) ?  $resArray['exception'] : '';
        
        $str = $method  . "\n";
        $str = $str . "User Id : " . $userId  . "\n";
        $str = $str . "Request : " . ($request) . "\n";
        $str = $str . "Response : " . ($response) . "\n";        
        $str = $str . "Response : " . ($response_message) . "\n";        
        $str = $str . "Exception : " . ($exception) . "\n";        
        
        $config = App_DI_Container::get('ConfigObject');
     //   App_DI_Container::get('ApiLog')->log($str, Zend_Log::NOTICE);
        if($config->system->api->add_call_on_db){
            //echo "in here";exit;
            $command = new ApiISOCall();
            return $command->log(array(
                'user_id'       => $userId,
                'method'        => $method,
                'request'       => $request,
                'response'      => $response,
                'response_message' => $response_message,
                'exception'      => $exception,
            ));
        }
    }
    
    
    /**
     * Write messages to the log and 
     * send notifications to email if configured
     *
     * @param array $resArray
     * @return void
     */
    public static function emaillog(array $resArray){
        //Logger can't afford errors
        //set variable
        $to     = isset($resArray['to']) ? $resArray['to'] : '';
        $from     = isset($resArray['from']) ? $resArray['from'] : '';
        $subject    = isset($resArray['subject']) ?  $resArray['subject'] : '';
        $body   = isset($resArray['body']) ?  $resArray['body'] : '';
        $template   = isset($resArray['template']) ?  $resArray['template'] : '';
        
        
        //$config = App_DI_Container::get('ConfigObject');
        //echo "<pre>";print_r($config);exit;
        //App_DI_Container::get('ApiLog')->log($str, Zend_Log::NOTICE);
       // if($config->system->email->add_email_on_db){
            //echo "in here";exit;
        $logger = new ShmartLogger(ShmartLogger::LOG_EMAIL);
        
        $logger->log(array(
                'to'        => $to,
                'from'      => $from,
                'subject'   => $subject,
                'body'      => $body,
                'template'  => $template,
            ));
        
//            $command = new Logemail();
//            $command->log(array(
//                'to'        => $to,
//                'from'      => $from,
//                'subject'   => $subject,
//                'body'      => $body,
//                'template'  => $template,
//            ));
        //}
        
    }
    
    
     /**
     * Write messages to the log and 
     * send notifications to email if configured
     *
     * @param array $resArray
     * @return void
     */
    public static function smslog(array $resArray){
        //Logger can't afford errors
        //set variable
        $to     = isset($resArray['to']) ? $resArray['to'] : '';
        $body   = isset($resArray['body']) ?  $resArray['body'] : '';
        $exception   = isset($resArray['exception']) ?  $resArray['exception'] : '';
        $status = isset($resArray['flag']) && $resArray['flag'] == true ? FLAG_SUCCESS : FLAG_FAILURE;
                
        $command = new ShmartLogger(ShmartLogger::LOG_SMS);
        
        if(!empty($exception)) {
            $command->log(array(
                'to'        => $to,
                'body'      => $body,
                'exception'   => $exception,
                'status'      => $status,
            ));
        } else {
            $command->log(array(
                'to'        => $to,
                'status'      => $status
            ));
        }
   }
   
   
   public static function serialzeArray(array $array) {
       $str = '';
       foreach ($array as $key => $value) {
           $str .= '<p>';
           $str .= '<b>'.$key.'</b>';
           $str .= $value;
           $str .= '</p>';
       }
       return $str;
   }
   
   
 /**
     * Write messages to the log and send notifications to email if configured
     *
     * @param string $msg
     * @param int
     * @return void
     */
    public static function errorLog($msg, $level = Zend_Log::INFO){
        
       /* App_DI_Container::get('GeneralLog')->log($msg, $level);
        
        $config = App_DI_Container::get('ConfigObject');
        if($config->system->notifications->notify_on_errors){
                $command = new App_Command_SendEmail();
                $command->onCommand('sendEmail', array(
                    'type' => 'Notification',
                    'recipients' => $config->system->notifications->recipients->toArray(),
                    'message' => $msg,
                    'level' => $level
                ));
        }*/
    }   
    
        /**
     * Write messages to the log and send notifications to email if configured
     *
     * @param string $msg
     * @param int
     * @return void
     */
    public static function masterLog($msg){
        
        if(is_array($msg)) {
            $param['txt_old'] = (isset($msg['txt_old']) && $msg['txt_old'] != '') ? serialize($msg['txt_old']) : '';
            $param['txt_new'] = (isset($msg['txt_new']) && $msg['txt_new'] != '') ? serialize($msg['txt_new']) : '';
            $param['by_whom'] = (isset($msg['user_type']) && $msg['user_type'] != '') ? $msg['user_type'] : '';
            $param['by_id']   = (isset($msg['user_id']) && $msg['user_id'] != '') ? $msg['user_id'] : '';
            $param['table_name']   = (isset($msg['table']) && $msg['table'] != '') ? $msg['table'] : '';
            $param['functionality']    = (isset($msg['functionality']) && $msg['functionality'] != '') ? $msg['functionality'] : '';
            $param['remarks']   = (isset($msg['remarks']) && $msg['remarks'] != '') ? $msg['remarks'] : '';            
        } elseif(is_object($msg)) {
            $param['txt_old'] = (isset($msg->txt_old) && $msg->txt_old != '') ? serialize($msg->txt_old) : '';
            $param['txt_new'] = (isset($msg->txt_new) && $msg->txt_new != '') ? serialize($msg->txt_new) : '';
            $param['by_whom'] = (isset($msg->user_type) && $msg->user_type != '') ? $msg->user_type : '';
            $param['by_id']   = (isset($msg->user_id) && $msg->user_id != '') ? $msg->user_id : '';
            $param['table_name']   = (isset($msg->table) && $msg->table != '') ? $msg->table : '';
            $param['functionality']    = (isset($msg->functionality) && $msg->functionality != '') ? $msg->functionality : '';
            $param['remarks']   = (isset($msg->remarks) && $msg->remarks != '') ? $msg->remarks : '';            
        }
        //print Zend_Session::getId();exit;
        $param['session_id'] = Zend_Session::getId();
        $param['date_stamped'] = new Zend_Db_Expr('NOW()');
        
        $command = new ShmartLogger(ShmartLogger::LOG);
        $command->log($param);
    }

    public static function extractRequestXML($xml,$method) {
        $sxml = self::sxml($xml);
        $ns = self::getNamespace($xml, $method);
        $ns = ($ns =='') ? 'sas' : $ns;
        $xpath = $ns.':'.$method;
        $xmlData = $sxml->xpath('//'.$xpath);
        if(!empty($xmlData)) {
            foreach($xmlData as $header)
            {
                $header->Message->attributes();
                return $header;
            }
        }
    }
    
    public static function getNamespace($xml, $method) {
        $a = strstr($xml, ":".$method,true);
        $b = strrpos($a, '<');
        return substr($a, $b+1);
    }
    
    public static function sxml($xml) {
        return new SimpleXMLElement($xml);
    } 
     
}
