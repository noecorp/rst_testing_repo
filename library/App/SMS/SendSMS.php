<?php

/**
 * SMS SendSMS
 *
 * @category App
 * @package App_SMS
 * @copyright transerv
 */
class App_SMS_SendSMS
{
    
    private $message;
    /**
     * Set email related info
     */
    public function __construct(){

    }
    
    /**
     * SendAuthSMS
     * @param type $username
     * @param type $authcode
     * @throws Exception
     */
    public function sendAuthSMS(Array $dataArray = array())
    {
       // echo "<pre>";print_r($dataArray);exit;
        if(empty($dataArray)) {
            throw new Exception ("No input provided");
        }
        /*if(empty($dataArray['username']) || empty($dataArray['authcode'])) {
            throw new Exception ("Invalid Username OR Authcode");
        }*/
        
        if(empty($dataArray['mobile1'])) {
            throw new Exception ("Mobile No not provided.");
        }
      // exit('here');
        try {
        $sms = new App_SMS_Transport_ValueFirst();
        $sms->_setViewMessage($dataArray, 'auth');
        //$res = $sms->_generateResponse('9899195914');
        if(!$sms->_generateResponse()) {
           $this->setMessage($sms->_getErrorMsg());
           return false;
        } else {
            //echo 'SMS Sent Successfully';
            $this->setMessage('SMS Sent Successfully');
            return true;
        }
        } catch (Exception $e) {
            //echo "<pre>";print_r($e);
            $this->setMessage($e->getMessage());
            return false;
             //echo "<pre>";print_r($e);exit;
        }
        
    }
    
     /**
     * SendConfSMS
     * @param type $username
     * @param type $authcode
     * @throws Exception
     */
    public function sendConfSMS(Array $dataArray = array())
    {
        //echo "<pre>";print_r($dataArray);
        if(empty($dataArray)) {
            throw new Exception ("No input provided");
        }
        
        
        if(empty($dataArray['mobile1'])) {
            throw new Exception ("Phone Number not provided.");
        }
        
        try {
            
        $sms = new App_SMS_Transport_ValueFirst();
        $sms->_setViewMessage($dataArray, 'conf_code');
        //$res = $sms->_generateResponse('9899195914');
        if(!$sms->_generateResponse()) {
            var_dump($sms->_getErrorMsg());
           $this->setMessage($sms->_getErrorMsg());
          // print $sms->_smsMessage;
          // exit('here2');
           return false;
        } else {
            
            //echo 'SMS Sent Successfully';
            $this->setMessage('SMS Sent Successfully');
            return true;
        }
        } catch (Exception $e) {
           // echo "<pre>";print_r($e);
            $this->setMessage($e->getMessage());
            return false;
             //echo "<pre>";print_r($e);exit;
        }
        
    }
    
    
     /**
     * sendAgentBalanceSMS
     * @param type $username
     * @param type $authcode
     * @throws Exception
     * @that function will send the sms to agent for his account balance update
     */
    public function sendAgentBalanceSMS(Array $dataArray = array())
    {        
            
        //echo "<pre>";print_r($dataArray);exit;
        if(empty($dataArray)) {
            throw new Exception ("No input provided");
        }
        if(empty($dataArray['agent_name']) || empty($dataArray['amount']) || empty($dataArray['new_balance'])) {
            throw new Exception ("Invalid Agentname OR Load Amount or New Balance");
        }
        
        if(empty($dataArray['mobile1'])) {
            throw new Exception ("Receiver  no. not provided.");
        }
        
        try {
        $dataArray['on_date'] = Util::getFormattedDate();  
        $sms = new App_SMS_Transport_ValueFirst();
       
        $sms->_setViewMessage($dataArray, 'agent_balance');
        //$res = $sms->_generateResponse('9899195914');
        if(!$sms->_generateResponse()) {
           $this->setMessage($sms->_getErrorMsg());
           return false;
        } else {
            //echo 'SMS Sent Successfully';
            $this->setMessage('SMS Sent Successfully');
            return true;
        }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
            return false;
             //echo "<pre>";print_r($e);exit;
        }
        
    }
    
    
     /**
     * sendCardholderBalanceSMS
     * @param type $username
     * @param type $authcode
     * @throws Exception
     * @that function will send the sms to cardholder for his account balance update
     */
    public function sendCardholderBalanceSMS(Array $dataArray = array())
    {
      
        //echo "<pre>";print_r($dataArray);exit;
        if(empty($dataArray)) {
            throw new Exception ("No input provided");
        }
        /*if(empty($dataArray['amount'])) {
            throw new Exception ("Invalid Load Amount");
        }*/
        
        if(empty($dataArray['mobile1'])) {
            throw new Exception ("Receiver  no. not provided.");
        }
        
        try {
            
        $sms = new App_SMS_Transport_ValueFirst();
       
        $sms->_setViewMessage($dataArray, 'cardholder_balance');
        //$res = $sms->_generateResponse('9899195914');
        if(!$sms->_generateResponse()) {
           $this->setMessage($sms->_getErrorMsg());
           return false;
        } else {
            //echo 'SMS Sent Successfully';
            $this->setMessage('SMS Sent Successfully');
            return true;
        }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
            return false;
             //echo "<pre>";print_r($e);exit;
        }
        
    }
    
    
    
    /**
     * sendCardholderAuth
     * @param type $username
     * @param type $authcode
     * @throws Exception
     * that function will send the auth code to cardholder while registering
     */
    public function sendCardholderAuth(Array $dataArray = array())
    {        
            
        //echo "<pre>";print_r($dataArray);exit;
        if(empty($dataArray)) {
            throw new Exception ("No input provided");
        }
        if(empty($dataArray['auth_code'])) {
            throw new Exception ("Invalid Auth Code");
        }
        
        if(empty($dataArray['mobile1'])) {
            throw new Exception ("Receiver  no. not provided.");
        }
        
        if(empty($dataArray['mobile_country_code'])) {
            throw new Exception ("Receiver mobile country code not provided.");
        }
        
        try {
            
        $sms = new App_SMS_Transport_ValueFirst();
        $mob_country_code = isset($dataArray['mobile_country_code'])?$dataArray['mobile_country_code']:'';
        $dataArray['mobile1'] = $mob_country_code.$dataArray['mobile1'];
       
        $sms->_setViewMessage($dataArray, 'cardholder_auth');
        
        //$res = $sms->_generateResponse('9899195914');
        if(!$sms->_generateResponse()) {
           $this->setMessage($sms->_getErrorMsg());
           return false;
        } else {
            //echo 'SMS Sent Successfully';
            $this->setMessage('SMS Sent Successfully');
            return true;
        }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
            return false;
             //echo "<pre>";print_r($e);exit;
        }
        
    }
    
    
    /**
     * sendTermsConditionAuth
     * @param type $dataArray, that will contain the mobile no. and sms content.      
     * @throws Exception
     * that function will send the auth code to cardholder while registering
     */
    public function sendTermsConditionAuth($dataArray = array())
    {        
            
        //echo "<pre>";print_r($dataArray);exit;
        if(empty($dataArray)) {
            throw new Exception ("No input provided");
        }
        if(empty($dataArray['termsconditions_auth'])) {
            throw new Exception ("Invalid Terms and Conditions Auth Code");
        }
        
        if(empty($dataArray['mobile1'])) {
            throw new Exception ("Receiver  no. not provided.");
        }
        
        
        
        try {
            
        $sms = new App_SMS_Transport_ValueFirst();
        $mob_country_code = isset($dataArray['mobile_country_code'])?$dataArray['mobile_country_code']:'';
        $dataArray['mobile1'] = $mob_country_code.$dataArray['mobile1'];
     
        $sms->_setViewMessage($dataArray, 'termsconditions_auth');
        
        if(!$sms->_generateResponse()) {
           $this->setMessage($sms->_getErrorMsg());
           return false;
        } else {
            //echo 'SMS Sent Successfully';
            $this->setMessage('SMS Sent Successfully');
            return true;
        }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
            return false;
             //echo "<pre>";print_r($e);exit;
        }
        
    }
    
    
    public function sendAgentPasswordSMS(Array $dataArray = array())
    {        
            
        //echo "<pre>";print_r($dataArray);
        if(empty($dataArray)) {
            throw new Exception ("No input provided");
        }
        if(empty($dataArray['email']) || empty($dataArray['password']) ) {
            throw new Exception ("Invalid Details for New account");
        }
        
        if(empty($dataArray['mobile1'])) {
            throw new Exception ("Receiver  no. not provided.");
        }
        
        try {
           
           
        $sms = new App_SMS_Transport_ValueFirst();
       
        $sms->_setViewMessage($dataArray, 'agent_password');
        
        if(!$sms->_generateResponse()) {
            
           $this->setMessage($sms->_getErrorMsg());
           return false;
        } else {
                
            $this->setMessage('SMS Sent Successfully');
            return true;
        }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
            return false;
            
        }
        
    }
    
    public function sendAgentApprovalSMS(Array $dataArray = array())
    {        
            
        //echo "<pre>";print_r($dataArray);
        if(empty($dataArray)) {
            throw new Exception ("No input provided");
        }
        
        
        if(empty($dataArray['mobile1'])) {
            throw new Exception ("Receiver  no. not provided.");
        }
        
        try {
           
           
        $sms = new App_SMS_Transport_ValueFirst();
       
        $sms->_setViewMessage($dataArray, 'agent_approval');
        
        if(!$sms->_generateResponse()) {
            
           $this->setMessage($sms->_getErrorMsg());
           return false;
        } else {
                
            $this->setMessage('SMS Sent Successfully');
            return true;
        }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
            return false;
            
        }
        
    }
    
    
        public function sendVerificationCodeSMS(Array $dataArray = array())
    {        
            
       
        if(empty($dataArray)) {
            throw new Exception ("No input provided");
        }
        
        
        if(empty($dataArray['mobile1'])) {
            throw new Exception ("Receiver  no. not provided.");
        }
        
        try {
           
        
        $sms = new App_SMS_Transport_ValueFirst();
       
        $sms->_setViewMessage($dataArray, 'verification_code');
        
        if(!$sms->_generateResponse()) {
            
           $this->setMessage($sms->_getErrorMsg());
           return false;
        } else {
                
            $this->setMessage('SMS Sent Successfully');
            return true;
        }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
            return false;
            
        }
        
    }
    
    private function setMessage($msg)
    {
        $this->message = $msg;
    }
    
    public function getMessage()
    {
        return $this->message;
    }    
    
    
     public function sendAgentTransaction($dataArray = array())
    {        
            
       
        if(empty($dataArray)) {
            throw new Exception ("No input provided");
        }
        
        
        if(empty($dataArray['mobile1'])) {
            throw new Exception ("Receiver  no. not provided.");
        }
        
        try {
           
        
        $sms = new App_SMS_Transport_ValueFirst();
       
        $sms->_setViewMessage($dataArray, 'agent_transaction');
        
        if(!$sms->_generateResponse()) {
            
           $this->setMessage($sms->_getErrorMsg());
           return false;
        } else {
                
            $this->setMessage('SMS Sent Successfully');
            return true;
        }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
            return false;
            
        }
        
    }
    
    
    /**
     * sendUpdateNewMobileAuth
     * @param type $dataArray, that will contain the mobile no. and sms content.      
     * @throws Exception
     * that function will send the auth code to on new mobile numner of cardholder
     */
    public function sendUpdateNewMobileAuth($dataArray = array())
    {        
            
        if(empty($dataArray)) {
            throw new Exception ("No input provided");
        }
        if(empty($dataArray['auth_code'])) {
            throw new Exception ("Invalid Auth Code");
        }
        
        if(empty($dataArray['mobile1'])) {
            throw new Exception ("Receiver  no. not provided.");
        }
        
        try {
            
        $sms = new App_SMS_Transport_ValueFirst();
        $mob_country_code = isset($dataArray['mobile_country_code'])?$dataArray['mobile_country_code']:'';
        $dataArray['mobile1'] = $mob_country_code.$dataArray['mobile1'];
     
        $sms->_setViewMessage($dataArray, 'update_new_mobile');
        
        if(!$sms->_generateResponse()) {
           $this->setMessage($sms->_getErrorMsg());
           return false;
        } else {
            //echo 'SMS Sent Successfully';
            $this->setMessage('SMS Sent Successfully');
            return true;
        }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
            return false;
             //echo "<pre>";print_r($e);exit;
        }
        
    }
    
    /**
     * sendCHMobileChangeSMS
     * @throws Exception
     * @that function will send the sms to agent for his account balance update
     */
    public function sendCHMobileChangeSMS($dataArray = array())
    {        
            
       
        if(empty($dataArray)) {
            throw new Exception ("No input provided");
        }
        
        if(empty($dataArray['mobile1']) || empty($dataArray['new_mobile'])) {
            throw new Exception ("Receiver  no. not provided.");
        }
        
        $dataArrayOld = array('mobile1'=>$dataArray['mobile1'],
            'oldPhone' =>$dataArray['mobile1'],
            'newPhone' =>$dataArray['new_mobile']); 
        
        $dataArrayNew = array('mobile1'=>$dataArray['new_mobile'],
            'oldPhone' =>$dataArray['mobile1'],
            'newPhone' =>$dataArray['new_mobile']); 
        
       //Send SMS to old number
        try {
            
            
        $sms = new App_SMS_Transport_ValueFirst();
       
        $sms->_setViewMessage($dataArrayOld, 'cardholder_mobile_change');
        
        if(!$sms->_generateResponse()) {
           $this->setMessage($sms->_getErrorMsg());
           $flg =  false;
        } else {
            $this->setMessage('SMS Sent Successfully');
            $flg =  true;
        }
        
        } catch (Exception $e) {
           
            $this->setMessage($e->getMessage());
            $flg =  false;
           
            
        }
        // Send SMS to New number
         try {
        $smsNew = new App_SMS_Transport_ValueFirst();
        $smsNew->_setViewMessage($dataArrayNew, 'cardholder_mobile_change');
        if(!$smsNew->_generateResponse()) {
           $this->setMessage($smsNew->_getErrorMsg());
           $flg =  false;
        } else {
            $this->setMessage('SMS Sent Successfully');
            $flg =  true;
        }
       
        
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
            $flg =  false;
            
        }
        return $flg;
    }
    
    
    /**
     * sendRemitterAuth
     * @param type $username
     * @param type $authcode
     * @throws Exception
     * that function will send the auth code to remitter while registering
     */
    public function sendRemitterAuth(Array $dataArray = array())
    {        
            
        if(empty($dataArray)) {
            throw new Exception ("No input provided");
        }
        if(empty($dataArray['auth_code'])) {
            throw new Exception ("Invalid Auth Code");
        }
        
        if(empty($dataArray['mobile1'])) {
            throw new Exception ("Receiver  no. not provided.");
        }
        
        if(empty($dataArray['mobile_country_code'])) {
            throw new Exception ("Receiver mobile country code not provided.");
        }
        
        try {
            
        $sms = new App_SMS_Transport_ValueFirst();
        $mob_country_code = isset($dataArray['mobile_country_code'])?$dataArray['mobile_country_code']:'';
        $dataArray['mobile1'] = $mob_country_code.$dataArray['mobile1'];
       
        $sms->_setViewMessage($dataArray, 'remitter_auth');
        
        //$res = $sms->_generateResponse('9899195914');
        if(!$sms->_generateResponse()) {
           $this->setMessage($sms->_getErrorMsg());
           return false;
        } else {
            //echo 'SMS Sent Successfully';
            $this->setMessage('SMS Sent Successfully');
            return true;
        }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
            return false;
             //echo "<pre>";print_r($e);exit;
        }
        
    }
    
    /**

     * sendRemitterAuth
     * @param type $username
     * @param type $authcode
     * @throws Exception
     * that function will send the auth code to Remitter while seraching and adding beneficiary
     */
    public function sendRemitterVerifyAuth(Array $dataArray = array())
    {        
            
        //echo "<pre>";print_r($dataArray);exit;
        if(empty($dataArray)) {
            throw new Exception ("No input provided");
        }
        if(empty($dataArray['auth_code'])) {
            throw new Exception ("Invalid Auth Code");
        }
        try {
            
        $sms = new App_SMS_Transport_ValueFirst();
        
       
        $sms->_setViewMessage($dataArray, 'remitter_verification_code');
        
        
        if(!$sms->_generateResponse()) {
           $this->setMessage($sms->_getErrorMsg());
           return false;
        } else {
            //echo 'SMS Sent Successfully';
            $this->setMessage('SMS Sent Successfully');
            return true;
        }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
            return false;
             //echo "<pre>";print_r($e);exit;
        }
    }
     /**
     * sendRemitterRegistration
     * @param type $username
     * @param type $authcode
     * @throws Exception
     * that function will send the auth code to remitter while registering
     */
    public function sendRemitterRegistration(Array $dataArray = array())
    {        
            
        if(empty($dataArray)) {
            throw new Exception ("No input provided");
        }
        
        if(empty($dataArray['mobile1'])) {
            throw new Exception ("Receiver  no. not provided.");
        }
        
        if(empty($dataArray['mobile_country_code'])) {
            throw new Exception ("Receiver mobile country code not provided.");
        }
        
        try {
            
        $sms = new App_SMS_Transport_ValueFirst();
        $mob_country_code = isset($dataArray['mobile_country_code'])?$dataArray['mobile_country_code']:'';
        $dataArray['mobile1'] = $mob_country_code.$dataArray['mobile1'];
       
        $sms->_setViewMessage($dataArray, 'remitter_registration');
        
        //$res = $sms->_generateResponse('9899195914');
        if(!$sms->_generateResponse()) {
           $this->setMessage($sms->_getErrorMsg());
           return false;
        } else {
            //echo 'SMS Sent Successfully';
            $this->setMessage('SMS Sent Successfully');
            return true;
        }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
            return false;
             //echo "<pre>";print_r($e);exit;
        }
        
    }
     /**
     * sendbeneficiaryenrollment
     * @param type $username
     * @param type $authcode
     * @throws Exception
     */
    public function sendbeneficiaryenrollment(Array $dataArray = array())
    {
       
        if(empty($dataArray)) {
            throw new Exception ("No input provided");
        }
        
        
        if(empty($dataArray['mobile1'])) {
            throw new Exception ("Phone Number not provided.");
        }
        
        
         try {
            
        $sms = new App_SMS_Transport_ValueFirst();
        
       
        $sms->_setViewMessage($dataArray, 'beneficiary_enrollment');
        
        
        if(!$sms->_generateResponse()) {
           $this->setMessage($sms->_getErrorMsg());
           return false;
        } else {
            //echo 'SMS Sent Successfully';
            $this->setMessage('SMS Sent Successfully');
            return true;
        }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
            return false;
             //echo "<pre>";print_r($e);exit;
        }
        
    }
      /* sendAddBeneficairyAuth
     * @param type $username
     * @param type $authcode
     * @throws Exception
     * that function will send the auth code to Remitter while seraching and adding beneficiary
     */
    public function sendAddBeneficairyAuth(Array $dataArray = array())
    {        
            
        //echo "<pre>";print_r($dataArray);exit;
        if(empty($dataArray)) {
            throw new Exception ("No input provided");
        }
        if(empty($dataArray['auth_code'])) {
            throw new Exception ("Invalid Auth Code");
        }
        try {
            
        $sms = new App_SMS_Transport_ValueFirst();
        
       
        $sms->_setViewMessage($dataArray, 'confirm_beneficiary');
        
        
        if(!$sms->_generateResponse()) {
           $this->setMessage($sms->_getErrorMsg());
           return false;
        } else {
            //echo 'SMS Sent Successfully';
            $this->setMessage('SMS Sent Successfully');
            return true;
        }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
            return false;
             //echo "<pre>";print_r($e);exit;
        }
    } 
      /**
     * sendfundtransferAuth
     * @param type $username
     * @param type $authcode
     * @param type $amount
     * @throws Exception
     * that function will send the auth code to remitter while registering
     */
    public function sendfundtransferAuth(Array $dataArray = array())
    {        
            
        if(empty($dataArray)) {
            throw new Exception ("No input provided");
        }
        
        if(empty($dataArray['mobile1'])) {
            throw new Exception ("Receiver  no. not provided.");
        }
        
        if(empty($dataArray['amount'])) {
            throw new Exception ("Please enter valid amount for fund transfer");
        }
        
        try {
            
        $sms = new App_SMS_Transport_ValueFirst();
        
        $sms->_setViewMessage($dataArray, 'fundtransfer_beneficiary');
        
        //$res = $sms->_generateResponse('9899195914');
        if(!$sms->_generateResponse()) {
           $this->setMessage($sms->_getErrorMsg());
           return false;
        } else {
            //echo 'SMS Sent Successfully';
            $this->setMessage('SMS Sent Successfully');
            return true;
        }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
            return false;
             //echo "<pre>";print_r($e);exit;
        }
        
    }
}
