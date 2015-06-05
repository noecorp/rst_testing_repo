<?php
class App_Mail_HtmlMailer extends Zend_Mail
{
    
    /**
     *
     * @var Zend_View
     */
    static $_defaultView;

    /**
     * current instance of our Zend_View
     * @var Zend_View
     */
    protected $_view;

    protected static function getDefaultView()
    {
        if(self::$_defaultView === null)
        {
            self::$_defaultView = new Zend_View();
            self::$_defaultView->setScriptPath(dirname(APPLICATION_PATH) . '/library/App/Mail/Templates');
        }
        return self::$_defaultView;
    }

    public function sendHtmlTemplate($template, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        try {
        $html = $this->_view->render($template);
        //$html = $this->_view->render('notification_en.phtml');
        
        App_Logger::emaillog(array(
            'to'            => $this->getRecipients(),
            'from'          => $this->getFrom(),
            'subject'       => $this->getSubject(),
            'body'          => $html,  
            'template'      => $template
        ));
        
        $this->setBodyHtml($html,$this->getCharset(), $encoding);
        $this->send();
        } catch (Exception $e) {
            //Log Error
            //Handler
           //echo "<pre>";print_r($e);exit;
        }
    }
    
    
    /**
     * sendAPIMail
     * Method use to send api mail
     * @param email $to
     * @param string $subject
     * @param string $html
     * @param email $from
     * 
     */
    //DO NOT UPDATE @author Vikram Singh <vikram@transerv.co.in>
    public function sendAPIMail($to, $subject, $html, $from = NULL) {
        
        $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
        $this->addTo($to)
            ->setSubject($subject)
            ->setBodyHtml($html,$this->getCharset(), $encoding);
        return $this->send();
        
    }

    public function setViewParam($property, $value)
    {
        $this->_view->__set($property, $value);
        return $this;
    }

    public function setViewParambyArray($dataArray)
    {
        foreach ($dataArray as $key => $value) {
            $this->setViewParam($key,$value);
        }
        return $this;
    }
    
    public function __construct($charset = 'iso-8859-1')
    {
        //App_DI_Container::get('ConfigObject')->mail->sender->fromname
       /* $config = array (
            'auth' => 'login', 
            'ssl' => 'tls', 
            'username' => App_DI_Container::get('ConfigObject')->mail->sender->username, 
            'password' => App_DI_Container::get('ConfigObject')->mail->sender->password,
            'port'=>App_DI_Container::get('ConfigObject')->mail->sender->port 
        );*/ 
        //$tr = new Zend_Mail_Transport_Smtp(App_DI_Container::get('ConfigObject')->mail->sender->smtp,$config);
        //Zend_Mail::setDefaultTransport($tr);
        //$tr = new Zend_Mail_Transport_Sendmail();
        $tr = new Zend_Mail_Transport_Sendmail('-fnoreply@shmart.in');
        Zend_Mail::setDefaultTransport($tr);
        
        parent::__construct($charset);
        $this->setFrom(App_DI_Container::get('ConfigObject')->mail->sender->fromemail, App_DI_Container::get('ConfigObject')->mail->sender->fromname);
        $this->_view = self::getDefaultView();
    }
    
    
    public function sendAuthCode(array $userData)
    {

       return $this->setSubject("Authorization Code")
            ->addTo($userData['email'])
            ->setViewParam('user_ip',  Util::getIP())
            ->setViewParam('host_name',$userData['host'])
            ->setViewParambyArray($userData)
            ->sendHtmlTemplate("auth_code.phtml");    
    }
    
     public function sendConfCode(array $userData)
    {

       return $this->setSubject("Confirmation Code")
            ->addTo($userData['email'])
            ->setViewParam('user_ip',  Util::getIP())
            ->setViewParambyArray($userData)
            ->sendHtmlTemplate("conf_code.phtml");    
    }
    
    public function sendAgentBalance(array $userData)
    {          

       return $this->setSubject($userData['email_subject'])
            ->addTo($userData['agent_email'])
            ->setViewParam('agent_name',  $userData['agent_name'])
            ->setViewParam('operation_name',$userData['operation_name'])
            ->setViewParam('amount',$userData['amount'])
            ->setViewParam('new_balance',isset($userData['new_balance'])?$userData['new_balance']:'')
            ->setViewParam('response_status',isset($userData['response_status'])?$userData['response_status']:'')
            ->setViewParam('transaction_date',$userData['transaction_date'])           
       
            ->setViewParambyArray($userData)
            ->sendHtmlTemplate("agent_balance.phtml");    
    }
    
    public function sendCardholderUpdationEmail(array $userData){
        return $this->setSubject("Your details updation with shmart")
            ->addTo($userData['email'])
            ->setViewParam('cardholder_name',  $userData['cardholder_name'])            
            ->setViewParam('updation_date',$userData['updation_date'])           
       
            ->setViewParambyArray($userData)
            ->sendHtmlTemplate("cardholder_updation.phtml"); 
        
    }
    
    
    public function sendAgentPasswordmail(array $userData)
    {          
          
       return $this->setSubject("New Agent account has been created")
            ->addTo($userData['adminmail'])
            ->setViewParam('name',  $userData['name'])
            ->setViewParam('email',$userData['email'])
            ->setViewParam('mobile',$userData['mobile1'])
            ->setViewParam('agent_code',$userData['agent_code'])
            ->setViewParambyArray($userData)
            ->sendHtmlTemplate("agent_password.phtml");    
    }
    
    public function sendUpdatePasswordmail(array $userData)
    {          
          
       return $this->setSubject("New Password for Shmart login")
            ->addTo($userData['email'])
            ->setViewParambyArray($userData)
            ->sendHtmlTemplate("update_password.phtml");    
    }
    
     public function sendAgentRejection(array $userData)
    {          
          // echo "<pre>";print_r($userData);exit;
       return $this->setSubject("Application Status")
            ->addTo($userData['email'])
            ->setViewParambyArray($userData)
            ->sendHtmlTemplate("agent_rejected.phtml");    
    }
    
    public function sendReloadEmailToAgent(array $userData)
    {          
       //echo "<pre>";print_r($userData);exit;  
       return $this->setSubject("Card NOT credited")
            ->addTo($userData['email'])
            ->setViewParambyArray($userData)
            ->sendHtmlTemplate("cardholder_reload.phtml");    
    }
    
     public function sendAgentApprovalmail(array $userData)
    {          

       return $this->setSubject("Agent Approved")
            ->addTo($userData['email'])
            ->setViewParam('name',  $userData['name'])
            ->setViewParam('email',$userData['email'])
            ->setViewParam('mobile',$userData['mobile1'])
            ->setViewParam('agent_code',$userData['agent_code'])
            ->setViewParambyArray($userData)
            ->sendHtmlTemplate("agent_approval.phtml");    
    }
    
    
     public function sendAgentEmailverificationmail(array $userData)
    {       

       return $this->setSubject("Email verification for your Shmart! Business Partner Account")
            ->addTo($userData['email'])
            ->setViewParam('name',  $userData['name'])       
            ->setViewParam('first_name',  $userData['first_name'])       
            ->setViewParam('last_name',  $userData['last_name'])       
            ->setViewParam('email',$userData['email'])
            ->setViewParam('agent_code',$userData['agent_code'])               
            ->setViewParam('ver_code',$userData['verification_code'])
            ->setViewParam('password',$userData['password'])
            ->setViewParam('id',$userData['id'])
            ->setViewParambyArray($userData)
            ->sendHtmlTemplate("agent_emailverification.phtml");    
    }
        
    /* sendCardholderBalance()
     * accepts array containing the mail sending details
     * that function will send the confirmation and load amount intimation to cardholder
     */
    public function sendCardholderBalance(array $userData)
    { 
      $mailSubject = 'Welcome to '.$userData['program_name'].' Program';
       return $this->setSubject($mailSubject)
            ->addTo($userData['email'])            
            ->setViewParam('amount',$userData['amount'])                        
            ->setViewParam('product_name',$userData['product_name'])                        
            ->setViewParam('mailBody',$userData['mailBody'])  
            ->setViewParambyArray($userData)
            ->sendHtmlTemplate("cardholder_balance.phtml");    
    }
    
    /* sendTermsConditionAuth()
     * accepts array containing the mail sending details
     * that function will send the terms and condition code to cardholder while registering
     */
    public function sendTermsConditionAuth(array $userData)
    { 
       return $this->setSubject($userData['mailSubject'])
            ->addTo($userData['email'])            
            ->setViewParam('termsconditions_auth',$userData['termsconditions_auth'])  
            ->setViewParam('product_name',$userData['product_name'])  
            ->setViewParambyArray($userData)
            ->sendHtmlTemplate("termsconditions_auth.phtml");    
    }
    
    
    
    /* sendAgentFundRequest()
     * accepts array containing the mail sending details
     * that function will send the intimation mail to ops for agent fund request
     */
    public function sendAgentFundRequest(array $userData)
    { 
       //echo '<pre>';print_r($userData);exit;
       return $this->setSubject('Agent Fund Request Intimation')
            ->addTo($userData['email'])            
            ->setViewParam('amount',$userData['amount'])  
            ->setViewParam('comments',$userData['comments'])  
            ->setViewParam('agent_code',$userData['agent_code'])  
            ->setViewParam('agent_email',$userData['agent_email'])
            ->setViewParam('agent_mobile_number',$userData['agent_mobile_number'])              
            ->setViewParambyArray($userData)
            ->sendHtmlTemplate("agent_fund_request.phtml");    
    }
    
    /* sendAgentFundRequest()
     * accepts array containing the mail sending details
     * that function will send the intimation mail to ops for agent fund request
     */
    public function sendOperationFundResponse(array $userData)
    { 
       
       return $this->setSubject('Agent Fund Response Intimation')
            ->addTo($userData['email'])            
            ->setViewParam('amount',$userData['amount'])  
            //->setViewParam('comments',$userData['comments'])  
            ->setViewParam('agent_code',$userData['agent_code'])  
            ->setViewParam('agent_email',$userData['agent_email'])
            ->setViewParam('agent_mobile_number',$userData['mobile1'])              
            ->setViewParam('transaction_date',$userData['transaction_date'])              
            ->setViewParam('responseStatus',isset($userData['response_status'])?$userData['response_status']:'')              
            ->setViewParambyArray($userData)
            ->sendHtmlTemplate("agent_fund_response.phtml");    
    }
    
    
    
     public function sendAgentLowBalanceMail(array $userData)
     {          

       return $this->setSubject("Low Balance in Account")
            ->addTo($userData['email'])
            ->setViewParam('agent_name',  $userData['agent_name'])
            ->setViewParam('current_balanc',$userData['current_balance'])
            ->setViewParam('agent_minimum_balance',$userData['agent_minimum_balance'])
                   
            ->setViewParambyArray($userData)
            ->sendHtmlTemplate("agent_low_balance.phtml");    
    }
    
    
    public function sendAgentMinMaxload(array $userData)
     {          
       // echo '<pre>';print_r($userData);exit;
       return $this->setSubject("Load Request out of Range")
            ->addTo($userData['email'])
            ->setViewParambyArray($userData)
            ->sendHtmlTemplate("agent_min_max_load.phtml");    
    }
    public function sendOpsLowBalanceMail(array $userData)
     {          

       return $this->setSubject("Low Balance in Agents Account")
            ->addTo($userData['ops_email'])
            ->setViewParam('agentInfo',  $userData['agentInfo'])
            ->setViewParam('agent_minimum_balance',$userData['agent_minimum_balance'])
                   
            ->setViewParambyArray($userData)
            ->sendHtmlTemplate("operation_low_balance.phtml");    
    }
    
    /**
     * Send Alert Generated by Cron
     * @param array $userData
     * @return type
     */
    public function sendAlertByCron(array $inputData)
    {
       $config = App_DI_Container::get('ConfigObject');
       //print_r($config->system->notifications->recipients->toArray());exit;
       return $this->setSubject("Cron Alert generated by ".$inputData['cronName'])
            //->addTo($config->system->notifications->recipients->toArray())
            ->addTo($config->system->notifications->recipients->toArray())
            ->setViewParam('cron_name',  $inputData['cronName'])
            ->setViewParam('message',  $inputData['message'])
            ->sendHtmlTemplate("cron_alert.phtml");    
    }
    
    /**
     * sendLoadFail
     * To generate mail alert for ISO Transaction failure
     * @param array $userData
     * @return type
     */
    public function sendLoadFail(array $userData)
    {
       $config = App_DI_Container::get('ConfigObject');
       return $this->setSubject("Transaction Failure")
            ->addTo($config->system->notifications->recipients->toArray())
            //->addTo("vikram@transerv.co.in")
            ->setViewParam('user_ip',  Util::getIP())
            ->setViewParam('arrData',  $userData)
            ->setViewParambyArray($userData)
            ->sendHtmlTemplate("load_fail.phtml");    
    }    
    
    

    /**
     * sendErrorAlert
     * Send Error Mail Alert
     * @param $msg
     * @return type
     */
    public function sendErrorAlert($msg)
    {
       $config = App_DI_Container::get('ConfigObject');
       return $this->setSubject("Application Error")
            ->addTo($config->system->notifications->recipients->toArray())
            ->setViewParam('message', $msg)               
            ->sendHtmlTemplate("error_alert.phtml");    
    }    

    
     public function sendLowCRNAlert(array $crnData)
     {          

       return $this->setSubject("Low CRN Alert")
            ->addTo($crnData['email'])
            ->setViewParam('current_crn_count',  $crnData['current_crn_count'])
            ->setViewParam('crn_count_required',$crnData['crn_count_required'])
            ->setViewParambyArray($crnData)
            ->sendHtmlTemplate("low_crn_alert.phtml");    
    }
    
    
    public function sendFailedMVCRegistration(array $param){
        return $this->setSubject("MVC registration failed and exceeds the maximum limit allowed Alert")
            ->addTo($param['email'])
            ->setViewParambyArray($param)
            ->sendHtmlTemplate("send_failed_mvcregistration.phtml"); 
    }
    
}
