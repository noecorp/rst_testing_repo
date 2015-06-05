<?php

class Reference extends App_Model {

    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'id';
    
    public $_key = 'PGMl51u+uvOPdmHtSgT8vp2DjbE77HYv';

    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_REFERENCE;
    
    protected $_type = '';
    
    private $_PRODUCT_ID;
    private $_ERROR_MSG;
    private $_CUSTOMER_ID;
    private $_LABEL_ID;
    
    const OTP_TYPE_LOAD = 'L';
    const OTP_TYPE_REGISTRATION = 'R';
    const OTP_TYPE_CUST_REGISTRATION = 'R';
    const OTP_TYPE_CUST_UPDATE = 'E';
    const OTP_TYPE_BENE_REGISTRATION = 'B';
    const OTP_TYPE_BENE_UPDATE = 'N';
    const OTP_TYPE_REMITTANCE = 'I';
    const OTP_TYPE_TRANSFER = 'T';
    const OTP_TYPE_REFUND = 'F';
    const OTP_TYPE_UNBLOCK = 'U';
    const OTP_VERIFIED = '_verified';
    const OTP_UNUSED = '_unused';
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    public function __construct($type='') {
          if(!empty($type)) {
            $this->setUserType($type);
          }
          parent::__construct(array());
      }


    public function insertData($param) {
        $this->save($param);
        return $this->_db->lastInsertId();
    }
    
    public function setUserType($type) {
        $this->_type = $type;
    }
    
    private function getUserType() {
        return $this->_type;
    }
    
    private function setLabel($label)
    {
       $this->_LABEL_ID = $label;
    }
    
    public function getLabel() {
        if(isset($this->_LABEL_ID) && !empty($this->_LABEL_ID)) {
            return $this->_LABEL_ID;
        }
    }
    
     private function setProductId($productId)
    {
       $this->_PRODUCT_ID = $productId;
    }
    
    public function getProductId() {
        if(isset($this->_PRODUCT_ID) && !empty($this->_PRODUCT_ID)) {
            return $this->_PRODUCT_ID;
        }
    }
    
      private function setCustomerId($customerId)
    {
       $this->_CUSTOMER_ID = $customerId;
    }
    
    public function getCustomerId() {
        if(isset($this->_CUSTOMER_ID) && !empty($this->_CUSTOMER_ID)) {
            return $this->_CUSTOMER_ID;
        }
    }
    public function log($param) {
        $ip = Util::getIP();
        $a = array(
            'product_id' => isset($param['product_id']) ? $param['product_id'] : '',
            'val' => isset($param['val']) ? $param['val'] : '',
            'label' => isset($param['label']) ? $param['label'] : '',
            'method' => isset($param['method']) ? $param['method'] : '',
            'request' => isset($param['request']) ? $param['request'] : '',
            'exception' => isset($param['exception']) ? $param['exception'] : '',
            'status' => isset($param['status']) ? $param['status'] : '',
            'ref_id' => isset($param['ref_id']) ? $param['ref_id'] : '',
            'user_id' => isset($param['user_id']) ? $param['user_id'] : '',
            'user_ip' => empty($ip) ? '' : $this->formatIpAddress($ip),
            'user_type' => $this->getUserType(),
            'date_created'  => new Zend_Db_Expr('NOW()')
        );
        return $this->insert($a);
    }
    
     public function generateOTP($params) {
        if(!isset($params['mobile']) || empty($params['mobile'])) {
            throw new Exception('Mobile not defined');   
        }
        if(!isset($params['user_id']) || empty($params['user_id'])) {
            throw new Exception('User Id not defined');   
        }
        if(!isset($params['user_type']) || empty($params['user_type'])) {
            throw new Exception('User Type not defined');   
        }
        
        $otpType = isset($params['type']) ? $params['type'] : 'R';
        
        $length = isset($params['length']) ? $params['length'] : '6';
        // Check if valid otp exists then resend same otp
        
        $custdetails = $this->getCustomerOTPById($params);
        if(!empty($custdetails)){
           $otp =  $custdetails['response'];
           
        }else{
            if($length == '4') {
                $otp = Alerts::generateAuthCode($length);                
            } else {
                $otp = Alerts::generateAuthCode();
            }
        }
        
        
        //Send Auth Code to Customer
        return $this->customerOTP(array(
            'otp'  => $otp, 
            'mobile' => $params['mobile'], 
            'request_type' => $otpType,
            'exception' => '',
            'user_id' => $params['user_id'],
            'user_type' => $params['user_type'],
            'ref_id' => $params['ref_id'],
            'product_id' => $params['product_id']
            )
        );
    }
    
    public function generateOTPAPI($params) {
        if(!isset($params['mobile']) || empty($params['mobile'])) {
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE);
        }
        if(!isset($params['user_id']) || empty($params['user_id'])) {
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE);
        }
        if(!isset($params['user_type']) || empty($params['user_type'])) {
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE);
        }
        
        $otpType = isset($params['type']) ? $params['type'] : 'R';
        
        $length = isset($params['length']) ? $params['length'] : '6';
        // Check if valid otp exists then resend same otp
        
        $custdetails = $this->getCustomerOTPAPIById($params);
        if(!empty($custdetails)){
           // Update all previous
            $this->updateCustomerOTPAPIByMobile($params);
           
        }
        if($length == '4') {
          $otp = Alerts::generateAuthCode($length);                
        } else {
          $otp = Alerts::generateAuthCode();
        }
                
        //Send Auth Code to Customer
        return $this->customerOTPAPI(array(
            'otp'  => $otp, 
            'mobile' => $params['mobile'], 
            'request_type' => $otpType,
            'exception' => '',
            'user_id' => $params['user_id'],
            'user_type' => $params['user_type'],
            'ref_id' => $params['ref_id'],
            'product_id' => $params['product_id']
            )
        );
    }
    
     public function customerOTP($params) {
        
        $productName = $this->getProductName($params['product_id']);
        
         //$this->_db->beginTransaction();
         $m = new App\Messaging\Corp\Ratnakar\Operation();
         try {
            if($params['request_type'] == self::OTP_TYPE_LOAD) {
                $this->setLabel($this->getRefLabel('otp_load'));
            } else {
                $this->setLabel($this->getRefLabel('otp'));
            }
        
        //Send Auth Code to Customer
         $userData = array('mobile'=> $params['mobile'],
                           'product_name' => $productName,
                           'auth_code' => $params['otp'],
                           'product_id' => $params['product_id'],
             );                               
              
                     $m->apiCardActivationAuth($userData);
        return $this->insertReference($params);
        } catch (Exception $e) {
            App_Logger::log($e->getMessage() , Zend_Log::ERR);

        }
        return FALSE;        
    }
 
    public function customerOTPAPI($params) {
        
       // $productName = $this->getProductName($params['product_id']);
        $productSupportInfo = $this->getProductSupportInfo($params['product_id']);
        
        $productName = $productSupportInfo['product_name'];
        $productsupportEmail = $productSupportInfo['support_email'];
        $productSMSsender = $productSupportInfo['sms_sender'];
        $productStaticOTP = $productSupportInfo['static_otp'];
         //$this->_db->beginTransaction();
             
         $m = new \App\Messaging\Remit\Kotak\Api();
         //Send Auth Code to Customer

         $userData = array('mobile'=> $params['mobile'],
                           'product_name' => $productName,
                           'auth_code' => $params['otp'],
                           'product_supportemail' => $productsupportEmail,
                           'sms_sender' => $productSMSsender,
             );                               
            
         try {
            
             switch(strtoupper($params['request_type'])){
              case self::OTP_TYPE_CUST_REGISTRATION:
                $this->setLabel($this->getRefLabel('otp-cust'));
                $m->cardActivationAuth($userData);
                break;
            case self::OTP_TYPE_CUST_UPDATE:
                $this->setLabel($this->getRefLabel('otp_cust_update'));
                $m->customerEditAuth($userData);
                break;
            case self::OTP_TYPE_BENE_REGISTRATION:
                $this->setLabel($this->getRefLabel('otp-bene'));
                $m->beneRegistrationAuth($userData);
                break;
            case self::OTP_TYPE_BENE_UPDATE:
                $this->setLabel($this->getRefLabel('otp_bene_update'));
                break;
            case self::OTP_TYPE_REMITTANCE:
                $this->setLabel($this->getRefLabel('otp_remittance'));
                if($productStaticOTP == FLAG_YES){
                    $remitterObj =  new Remit_Kotak_Remitter();
                    $Obj =  new Remit_Kotak_Remittancerequest();
                    $remitter = $remitterObj->getremitter($userData['mobile'],$params['product_id']);
                    $userData['remitter_id'] = $remitter['id'];
                    
                    $staticOTParr = $Obj->remittanceByStaticOtp($userData); 
                    if($staticOTParr == false)
                    {
                        return false;
                    }else{
                        $userData['auth_code'] = $staticOTParr['auth_code'];
                        $m->remittanceStaticOtp($userData);    
                        $params['otp'] = $staticOTParr['auth_code'];
                    }
                }else{
                 $m->remittanceAuth($userData);
                }
                break;
            case self::OTP_TYPE_TRANSFER:
                $this->setLabel($this->getRefLabel('otp_transfer'));
                $m->transactionAuth($userData);
                break;
            case self::OTP_TYPE_REFUND:
                $this->setLabel($this->getRefLabel('otp_refund'));
                $m->transactionAuth($userData);
                break;
            case self::OTP_TYPE_LOAD:
                $this->setLabel($this->getRefLabel('otp_load'));
                break;
         }
           
        return $this->insertReferenceAPI($params);
        } catch (Exception $e) {
            App_Logger::log($e->getMessage() , Zend_Log::ERR); 
            $code = $e->getCode();
            if(empty($code)) {
                $code = ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_CODE;
            }            
	    //$code = (empty($e->getCode())) ? ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG : $e->getCode();
            $message = $e->getMessage();
            if(empty($message)) {
                $message = ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG;
            }            
	    //$message = (empty($e->getMessage())) ? ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG : $e->getMessage(); 
	    throw new Exception($message,$code); 
        }
        return FALSE;        
    }
    
     public function insertReference($param) {
         
        $insertArr = array(
            'label' => $this->getLabel(),
            'product_id' => $param['product_id'],
            'user_id' => $param['user_id'],
            'user_type' => $param['user_type'],
            'method' => $param['request_type'],
            'request' => $param['mobile'],
            'response' => $param['otp'],
            'val' => $param['amount'],
            'exception' => $param['exception'],
            'ref_id' => $param['ref_id'],
            'user_ip' => $this->formatIpAddress(Util::getIP()),
            'date_created' =>  new Zend_Db_Expr('NOW()')
        );
        $rs = $this->insertData($insertArr);
        return $rs;
    }
    
    public function insertReferenceAPI($param) {
        
        $insertArr = array(
            'label' => $this->getLabel(),
            'product_id' => $param['product_id'],
            'user_id' => $param['user_id'],
            'user_type' => $param['user_type'],
            'method' => $param['request_type'],
            'request' => $param['mobile'],
            'response' => $param['otp'],
            'val' => $param['amount'],
            'exception' => $param['exception'],
            'ref_id' => $param['ref_id'],
            'user_ip' => $this->formatIpAddress(Util::getIP()),
            'date_created' =>  new Zend_Db_Expr('NOW()')
        );
        $rs = $this->insertData($insertArr);
        return $rs;
    }
    
    
     public function verifyCustomerOTP($params) {
         $otp = isset($params['otp']) ? $params['otp'] : '';
         $allowedSessionInMin = App_DI_Container::get('ConfigObject')->system->paytronic->timeout_in_min;
         if($params['request_type'] == self::OTP_TYPE_LOAD) {
                $this->setLabel($this->getRefLabel('otp_load'));
            } else {
                $this->setLabel($this->getRefLabel('otp'));
            }
         $sql = $this->_db->select();
         $sql->from(DbTable::TABLE_REFERENCE);
         $sql->where("label = '" . $this->getLabel() . "'");
         $sql->where("request = '" . $params['mobile'] . "'");
         if($otp != ''){
         $sql->where("response = '" . $params['otp'] . "'");
         }
         $sql->where("date_created > " . new Zend_Db_Expr('DATE_SUB(NOW(),INTERVAL '.$allowedSessionInMin.' MINUTE)')  );
         $sql->order('id DESC');
         $sql->limit('1');
//         echo $sql;exit;
         $rs= $this->_db->fetchRow($sql);                
        if(!empty($rs)) {
            return $rs;
        }
        return FALSE;
    }
 
     public function getRefLabel($label) {
        switch ($label) {
            case 'otp':
                $resLabel = TYPE_OTP;
                break;
            case 'otp-cust':
                $resLabel = TYPE_OTP_CUST_REGN;
                break;
            case 'otp_cust_update':
                $resLabel = TYPE_OTP_CUST_UPDATE;
                break;
            case 'otp-bene':
                $resLabel = TYPE_OTP_BENE_REGN;
                break;
            case 'otp_bene_update':
                $resLabel = TYPE_OTP_BENE_UPDATE;
                break;
            case 'otp_load':
                $resLabel = TYPE_OTP_LOAD;
                break;
            case 'otp_load_confirmed':
                $resLabel = TYPE_OTP_LOAD_CONFIRMED;
                break;
            case 'otp_remittance':
                $resLabel = TYPE_OTP_REMITTANCE;
                break;
            case 'otp_transfer':
                $resLabel = TYPE_OTP_TRANSFER;
                break;
            case 'otp_load_verified':
                $resLabel = TYPE_OTP_TXN_VERIFIED;
                break;
            case 'otp_refund':
                $resLabel = TYPE_OTP_REFUND;
                break;
            case 'otp_unblock':
                $resLabel = TYPE_OTP_UNBLOCK;
                break;
        }

      return $resLabel;
        

    }
    
    public function getCustomerOTPById($params) {
         
         $allowedSessionInMin = App_DI_Container::get('ConfigObject')->system->paytronic->timeout_in_min;
         if($params['type'] == self::OTP_TYPE_LOAD) {
                $this->setLabel($this->getRefLabel('otp_load'));
            } else {
                $this->setLabel($this->getRefLabel('otp'));
            }
         $sql = $this->_db->select();
         $sql->from(DbTable::TABLE_REFERENCE);
         $sql->where("label = '" . $this->getLabel() . "'");
         $sql->where("request = '" . $params['mobile'] . "'");
//         $sql->where("response = '" . $params['otp'] . "'");
         $sql->where("id = '" . $params['ack_no'] . "'");
         $sql->where("date_created > " . new Zend_Db_Expr('DATE_SUB(NOW(),INTERVAL '.$allowedSessionInMin.' MINUTE)')  );

         $rs= $this->_db->fetchRow($sql);                
        if(!empty($rs)) {
            return $rs;
        }
        return FALSE;
    }
    
    public function getCustomerOTPAPIById($params) {
        if($params['type'] == self::OTP_TYPE_REGISTRATION) {
            $this->setLabel($this->getRefLabel('otp-cust'));
        } elseif($params['type'] == self::OTP_TYPE_CUST_UPDATE) {
            $this->setLabel($this->getRefLabel('otp_cust_update'));
        } elseif($params['type'] == self::OTP_TYPE_BENE_REGISTRATION) {
            $this->setLabel($this->getRefLabel('otp-bene'));
        } elseif($params['type'] == self::OTP_TYPE_BENE_UPDATE) {
            $this->setLabel($this->getRefLabel('otp_bene_update'));
        } elseif($params['type'] == self::OTP_TYPE_REMITTANCE) {
            $this->setLabel($this->getRefLabel('otp_remittance'));
        } elseif($params['type'] == self::OTP_TYPE_TRANSFER) {
            $this->setLabel($this->getRefLabel('otp_transfer'));
        } elseif($params['type'] == self::OTP_TYPE_REFUND) {
            $this->setLabel($this->getRefLabel('otp_refund'));
        } elseif($params['type'] == self::OTP_TYPE_UNBLOCK) {
            $this->setLabel($this->getRefLabel('otp_unblock'));
        } else {
            $this->setLabel($this->getRefLabel('otp'));
        }
        
         $sql = $this->_db->select();
         $sql->from(DbTable::TABLE_REFERENCE, array('id'));
         $sql->where("label = '" . $this->getLabel() . "'");
         $sql->where("request = '" . $params['mobile'] . "'");
         $sql->where("product_id = '" . $params['product_id'] . "'");
         $rs= $this->_db->fetchAll($sql);                
        if(!empty($rs)) {
            return $rs;
        }
        return FALSE;
    }
    
     public function generateLoadOTP($params) {
       
        if(!isset($params['customer_id']) || empty($params['customer_id'])) {
            throw new Exception('Customer not defined');
        }
        
        if(!isset($params['product_id']) || empty($params['product_id'])) {
            throw new Exception('Product not defined');            
        }
        $length = isset($params['length']) ? $params['length'] : '6';
        
        //echo '<pre>';print_r($params);exit;
        if($length == '4') {
                $otp = Alerts::generateAuthCode($length);                
            } else {
                $otp = Alerts::generateAuthCode();
            }        

        $otpType = isset($params['type']) ? $params['type'] : 'R';
        //Send Auth Code to Customer
        return $this->customerLoadOTP(array(
            'otp'  => $otp, 
            'product_id' => $params['product_id'], 
            'customer_id' => $params['customer_id'], 
            'request_type' => $otpType,
            'amount' => $params['amount'],
            'user_id' => $params['user_id'],
            'user_type' => $params['user_type'],
            'mode'      => $params['mode'],
            'request_from'  => $params['request_from'],
            )
        );
    }
    
     public function customerLoadOTP($params) {
        $productName = $this->getProductName($params['product_id']);
        $custTrackModel = new CustomerTrack();
        $this->session = new Zend_Session_Namespace("App.Operation.Controller");
        $m = new App\Messaging\Corp\Ratnakar\Operation();
         try {
            $this->setProductId($params['product_id']);
            $this->setCustomerId($params['customer_id']);
            if($params['request_type'] == self::OTP_TYPE_LOAD) {
                $this->setLabel($this->getRefLabel('otp_load'));
            } else {
                $this->setLabel($this->getRefLabel('otp'));
            }
          $custDetails = $custTrackModel->getCustomerInfoDetails($params['product_id'],$params['customer_id']);
          $mobile = $custDetails['mobile'];

         //Send Auth Code to Customer
          $userData = array('mobile'=> $mobile,
                           'product_name' => $productName,
                           'auth_code' => $params['otp'],
                           'amount' => $params['amount'],
                           'last_four' => substr($custDetails['card_number'], -4),
                           'product_id' => $params['product_id'],
                  
                  );


                               
               if($params['mode'] == TXN_MODE_CR){
                    $m->apiCardLoadAuth($userData);
               }else{
                  if(isset($params['request_from'])){
                    $userData['request_from'] = $params['request_from'];    
                  }
                  $m->apiCardDebitAuth($userData);
               }
                $params['mobile'] = $mobile;
        return $this->insertReference($params);
            //$this->_db->commit();
        } catch (Exception $e) {
           // echo '<pre>';print_r($e->getMessage());exit;
            App_Logger::log($e->getMessage() , Zend_Log::ERR);
            //$this->_db->rollBack();

        }
        return FALSE;        
    }
    
    public function verifyCustomerLoadOTP($params) {
         
         $allowedSessionInMin = App_DI_Container::get('ConfigObject')->system->paytronic->timeout_in_min;
         
        if($params['request_type'] == self::OTP_TYPE_LOAD) {
            $flag = $this->getRefLabel('otp_load');
        } else {
            $flag = $this->getRefLabel('otp');
        }         
         
         $productId = isset($params['product_id']) ? $params['product_id'] : '';
         $customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
         
         
         $sql = $this->select();
         if($productId != ''){
          $sql->where("product_id = '" . $productId . "'");
        }
        if($customerId != ''){
          $sql->where("user_id = '" . $customerId . "'");
        }
         $sql->where("label = '" . $flag . "'");
         $sql->where("method = '" . $params['request_type'] . "'");
         if(isset($params['amount'])) {
            $sql->where("val = '" . $params['amount'] . "'");
         }
         $sql->where("response = '" . $params['otp']. "'");
         $sql->where("date_created > " . new Zend_Db_Expr('DATE_SUB(NOW(),INTERVAL '.$allowedSessionInMin.' MINUTE)')  );
        $rs= $this->fetchRow($sql);                
        if(!empty($rs)) {
            $this->update(array(
                'label'     => $this->getRefLabel('otp_load_confirmed'),   
                'time_response' => new Zend_Db_Expr('NOW()')
            ), "id='".$rs['id'] ."'");
            return $rs;
        }
        return FALSE;
    }
    
    public function addFilter($resp) {
        if(!empty($resp)) {
            return $this->encode256($resp, $this->_key);
        }
    }
    
    
    public function logRef($data) {
	/*
	 * Before we save log ref in `t log ref`  table
	 *	**by using**-->	    return $this->save($data);
	 * But Now we use txt file to store this data 
	 */ 
	$ref = new ShmartLogger(ShmartLogger::LOG_REF);
	$ref->log($data);
    }
    
    
    public function getProductName($productId){
        $productModel = new Products();
        $productDetails = $productModel->getProductInfo($productId);
        return $productDetails->name;
    }
    
    public function customSMSLogger($param)
    {
        $this->setUserType($param['type']);
        $inputArr = array(
                'user_type'    => $param['type'],
                'product_id'    => $param['product_id'],
                'val'           => $param['txn_no'],
                'method'        => $param['method'],
                'label'         => $param['mobile'],
                'request'       => $param['message'],
                'exception'     => $param['exception'],
        );
        $this->customSMSlog($inputArr);
    }
    
    public function revertCustomSMS($param)
    {
         $sql = $this->_db->select();
                $sql->from(DbTable::TABLE_SMS);
                //$sql->where("label = '" . $this->getLabel() . "'");
                $sql->where("user_type = ?", SMS_PENDING);
                $sql->where("label = ?", $param['mobile']);
                $sql->where("val = ?", $param['txn_no']);
                $sql->where("product_id = ?",$param['product_id']);
          $rs= $this->_db->fetchRow($sql);     
          if(!empty($rs)) {
              $this->_db->update(DbTable::TABLE_SMS, array(
                  'user_type' => SMS_FAILED,
                  'response' => 'Transaction reverted'
              ), "id='".$rs['id']."'");
          }
    }    
    
    public function sendCustomSMS()
    {
        $pendingSMS = $this->getPendingCustomSMS();

        if(!empty($pendingSMS)) {
            foreach ($pendingSMS as $rs) {
                try{
                    $exception = '';                
                    $res = $this->sendRefSMS($rs['label'],$rs['request']);
                    if($res == FALSE) {
                        $exception = $this->getError();
                    }
                    $rsArr = array(
                      'user_type'  => SMS_SENT,
                      'response'  =>  'SMS Sent ',
                      'exception'  =>  $exception,
                      'time_response' => new Zend_Db_Expr('NOW()')
                    );
                    $this->updateRefence($rsArr,$rs['id']);
                } catch (Exception $e) {
                    $this->setError($e->getMessage());
                    $message = $e->getMessage();
                    $message = (empty($message)) ? 'SMS Not sent' : $message;
                    
                    $rsArr = array(
                      'user_type'  => SMS_FAILED,
                      'response'  =>  'SMS Not Sent',
                      'exception'  =>  $message,
                      'time_response' => new Zend_Db_Expr('NOW()')
                    );
                    $this->updateRefence($rsArr,$rs['id']);
                }
            }
        }
        
    }
    
    public function sendRefSMS($mobile,$message) {
        if(empty($mobile) || empty($message)) {
            return FALSE;
        }        
        $valueFirst = new \App\Messaging\Transport\SMS\ValueFirst();
        $valueFirst->setMobile($mobile);
        $valueFirst->setMessage($message);
        $flg = $valueFirst->_generateResponse();
        if($flg == FALSE) {
            $this->setError($valueFirst->_getErrorMsg());
        }
        return $flg;
    }
    
    public function updateRefence($rs,$ref_id)
    {
              $this->_db->update(DbTable::TABLE_SMS, $rs, "id='".$ref_id."'");
    }
    
    public function getPendingCustomSMS()
    {
        $allowedSessionInMin = App_DI_Container::get('ConfigObject')->system->sms->send_time;        
        $sql = $this->_db->select();
               $sql->from(DbTable::TABLE_SMS);
               $sql->where("user_type = ?", SMS_PENDING);
               $sql->where("now() >= " . new Zend_Db_Expr('DATE_ADD(date_created,INTERVAL '.$allowedSessionInMin.' SECOND)')  );
               //echo $sql;exit;
         return $this->_db->fetchAll($sql);     
    }
    
    public function customSMSlog($param) {
        try {
        $ip = Util::getIP();
        $a = array(
            'product_id' => isset($param['product_id']) ? $param['product_id'] : '',
            'val' => isset($param['val']) ? $param['val'] : '',
            'label' => isset($param['label']) ? $param['label'] : '',
            'method' => isset($param['method']) ? $param['method'] : '',
            'request' => isset($param['request']) ? $param['request'] : '',
            'exception' => isset($param['exception']) ? $param['exception'] : '',
            //'status' => isset($param['status']) ? $param['status'] : '',
            'ref_id' => isset($param['ref_id']) ? $param['ref_id'] : '',
            'user_id' => isset($param['user_id']) ? $param['user_id'] : '',
            'user_ip' => empty($ip) ? '' : $this->formatIpAddress($ip),
            'user_type' => $this->getUserType(),
            'date_created'  => new Zend_Db_Expr('NOW()')
        );
        $r = $this->_db->insert(DbTable::TABLE_SMS, $a);
        //echo $r.'**';exit;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage() , Zend_Log::ERR);
        }
    }
    
    
    
    public function verifyCustomerVerifiedLoadOTP($params) {
         
         $allowedSessionInMin = App_DI_Container::get('ConfigObject')->system->paytronic->timeout_in_min;
         
         $flag = $this->getRefLabel('otp_load_confirmed');
         
         $productId = isset($params['product_id']) ? $params['product_id'] : '';
         $customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
         
         
         $sql = $this->select();
         if($productId != ''){
          $sql->where("product_id = '" . $productId . "'");
        }
        if($customerId != ''){
          $sql->where("user_id = '" . $customerId . "'");
        }
         $sql->where("label = '" . $flag . "'");
         $sql->where("method = '" . $params['request_type'] . "'");
         if(isset($params['amount'])) {
            $sql->where("val = '" . $params['amount'] . "'");
         }
         if(isset($params['otp'])) {
            $sql->where("response = '" . $params['otp']. "'");
         }
         $sql->where("date_created > " . new Zend_Db_Expr('DATE_SUB(NOW(),INTERVAL '.$allowedSessionInMin.' MINUTE)')  );
        $rs= $this->fetchRow($sql);                
        if(!empty($rs)) {
            $this->update(array(
                'label'     => $this->getRefLabel('otp_load_verified'),   
                'time_response' => new Zend_Db_Expr('NOW()')
            ), "id='".$rs['id'] ."'");
            return $rs;
        }
        return FALSE;
    }
    
/*    public function getProductSupportInfo($productId){
        $productModel = new Products();
        $productDetails = $productModel->getProductInfo($productId);
        $productConst = $productDetails->const;
        $staticOTP = $productDetails->static_otp;

        $bankproductConst = '';
        $productInfo = array();
          switch ($productConst) {
            case PRODUCT_CONST_RAT_PAYU:
                $bankproductConst = BANK_RATNAKAR_PAYU;
                break;
            case PRODUCT_CONST_RAT_FORBES:
                $bankproductConst = BANK_RATNAKAR_FORBES;
                break;
            case PRODUCT_CONST_RAT_SHOP:
                $bankproductConst = BANK_RATNAKAR_SHOPCLUES;
                break;
            case PRODUCT_CONST_KOTAK_REMIT:
                $bankproductConst = BANK_KOTAK_REMIT;
                break;           
           case PRODUCT_CONST_RAT_CTY:
                $bankproductConst = BANK_RATNAKAR_CEQUITY;
                break;
           case PRODUCT_CONST_RAT_HFCI:
                $bankproductConst = BANK_RATNAKAR_HFCI;
                break; 
           case PRODUCT_CONST_RAT_BMS:
                $bankproductConst = BANK_RATNAKAR_BOOKMYSHOW;
                break;
           case PRODUCT_CONST_RAT_SMP:
                $bankproductConst = BANK_RATNAKAR_SMP;
                break; 
            case PRODUCT_CONST_RAT_MVC:
                $bankproductConst = BANK_RATNAKAR_MVC;
                break;
        }
        $product = App_DI_Definition_BankProduct::getInstance($bankproductConst);
        $productInfo['support_email'] = $product->product->supportemail;
        $productInfo['sms_sender'] = $product->product->smssender;
        $productInfo['product_name'] = $product->product->name;
        $productInfo['smsname'] = $product->product->smsname;
        $productInfo['static_otp'] = $staticOTP;
        
        return $productInfo;
    }*/

	public function updateCustomerOTPAPI($params) {
       
         switch($params['request_type']){
              case self::OTP_TYPE_CUST_REGISTRATION:
                $this->setLabel($this->getRefLabel('otp-cust'));
                break;
            case self::OTP_TYPE_CUST_UPDATE:
                $this->setLabel($this->getRefLabel('otp_cust_update'));
                break;
            case self::OTP_TYPE_BENE_REGISTRATION:
                $this->setLabel($this->getRefLabel('otp-bene'));
                break;
            case self::OTP_TYPE_BENE_UPDATE:
                $this->setLabel($this->getRefLabel('otp_bene_update'));
                break;
            case self::OTP_TYPE_REMITTANCE:
                $this->setLabel($this->getRefLabel('otp_remittance'));
                break;
            case self::OTP_TYPE_TRANSFER:
                $this->setLabel($this->getRefLabel('otp_transfer'));
                break;
         }
         
         $verifiedLabel = $this->getLabel().self::OTP_VERIFIED;
         $id = $params['id'];
         $updateArr = array('label' => $verifiedLabel);
         $res = $this->update($updateArr,"id = $id");
         return $res;     
    }
	
public function verifyCustomerOTPAPI($params) {
         $otp = isset($params['otp']) ? $params['otp'] : '';
         $refID = isset($params['ref_id']) ? $params['ref_id'] : '';
         $allowedSessionInMin = App_DI_Container::get('ConfigObject')->system->paytronic->timeout_in_min;
       
         switch($params['request_type']){
              case self::OTP_TYPE_CUST_REGISTRATION:
                $this->setLabel($this->getRefLabel('otp-cust'));
                break;
            case self::OTP_TYPE_CUST_UPDATE:
                $this->setLabel($this->getRefLabel('otp_cust_update'));
                break;
            case self::OTP_TYPE_BENE_REGISTRATION:
                $this->setLabel($this->getRefLabel('otp-bene'));
                break;
            case self::OTP_TYPE_BENE_UPDATE:
                $this->setLabel($this->getRefLabel('otp_bene_update'));
                break;
            case self::OTP_TYPE_REMITTANCE:
                $this->setLabel($this->getRefLabel('otp_remittance'));
                break;
            case self::OTP_TYPE_TRANSFER:
                $this->setLabel($this->getRefLabel('otp_transfer'));
                break;
            case self::OTP_TYPE_LOAD:
                $this->setLabel($this->getRefLabel('otp_load'));
                break;
            case self::OTP_TYPE_REFUND:
                $this->setLabel($this->getRefLabel('otp_refund'));
                break;
            case self::OTP_TYPE_UNBLOCK:
                $this->setLabel($this->getRefLabel('otp_unblock'));
                break;
         }
         
         $sql = $this->_db->select();
         $sql->from(DbTable::TABLE_REFERENCE);
         $sql->where("label = '" . $this->getLabel() . "'");
         $sql->where("request = '" . $params['mobile'] . "'");
         if($otp!=''){
         $sql->where("response = '" . $params['otp'] . "'");
         }
         if($refID!=''){
         $sql->where("ref_id = '" . $params['ref_id'] . "'");
         }
         $sql->where("date_created > " . new Zend_Db_Expr('DATE_SUB(NOW(),INTERVAL '.$allowedSessionInMin.' MINUTE)')  );
         $sql->order('id DESC');
         $sql->limit('1');
         $rs= $this->_db->fetchRow($sql);                
        if(!empty($rs)) {
            return $rs;
        }
        return FALSE;
    }









public function getProductSupportInfo($productId){
        $productModel = new Products();
        $productDetails = $productModel->getProductInfo($productId);
        $productConst = $productDetails->const;
        $staticOTP = $productDetails->static_otp;

        $bankproductConst = '';
        $productInfo = array();
          switch ($productConst) {
            case PRODUCT_CONST_RAT_PAYU:
                $bankproductConst = BANK_RATNAKAR_PAYU;
                break;
            case PRODUCT_CONST_RAT_FORBES:
                $bankproductConst = BANK_RATNAKAR_FORBES;
                break;
            case PRODUCT_CONST_RAT_SHOP:
                $bankproductConst = BANK_RATNAKAR_SHOPCLUES;
                break;
            case PRODUCT_CONST_KOTAK_REMIT:
                $bankproductConst = BANK_KOTAK_REMIT;
                break;           
           case PRODUCT_CONST_RAT_CTY:
                $bankproductConst = BANK_RATNAKAR_CEQUITY;
                break;
           case PRODUCT_CONST_RAT_HFCI:
                $bankproductConst = BANK_RATNAKAR_HFCI;
                break; 
           case PRODUCT_CONST_RAT_BMS:
                $bankproductConst = BANK_RATNAKAR_BOOKMYSHOW;
                break;
           case PRODUCT_CONST_RAT_SMP:
                $bankproductConst = BANK_RATNAKAR_SMP;
                break;
           case PRODUCT_CONST_RAT_TFS:
                $bankproductConst = BANK_RATNAKAR_TFS;
                break;
            case PRODUCT_CONST_RAT_MVC:
                $bankproductConst = BANK_RATNAKAR_MVC;
                break;
        }
        $product = App_DI_Definition_BankProduct::getInstance($bankproductConst);
        $productInfo['support_email'] = $product->product->supportemail;
        $productInfo['sms_sender'] = $product->product->smssender;
        $productInfo['product_name'] = $product->product->name;
        $productInfo['smsname'] = $product->product->smsname;
        $productInfo['static_otp'] = $staticOTP;
        
        return $productInfo;
    }

    
    public function updateCustomerOTPAPIByMobile($params) {
         switch($params['type']){
           case self::OTP_TYPE_CUST_REGISTRATION:
                $this->setLabel($this->getRefLabel('otp-cust'));
                break;  
            case self::OTP_TYPE_CUST_UPDATE:
                $this->setLabel($this->getRefLabel('otp_cust_update'));
                break; 
            case self::OTP_TYPE_BENE_REGISTRATION:
                $this->setLabel($this->getRefLabel('otp-bene'));
                break; 
            case self::OTP_TYPE_BENE_UPDATE:
                $this->setLabel($this->getRefLabel('otp_bene_update'));
                break; 
            case self::OTP_TYPE_REMITTANCE:
                $this->setLabel($this->getRefLabel('otp_remittance'));
                break; 
            case self::OTP_TYPE_TRANSFER:
                $this->setLabel($this->getRefLabel('otp_transfer'));
                break;
            case self::OTP_TYPE_REFUND:
                $this->setLabel($this->getRefLabel('otp_refund'));
                break;
            case self::OTP_TYPE_UNBLOCK:
                $this->setLabel($this->getRefLabel('otp_unblock'));
                break;
         }
         
         $unusedLabel = $this->getLabel().self::OTP_UNUSED;
         $updateArr = array('label' => $unusedLabel);
         $mobile = $params['mobile'];
         $productID = $params['product_id'];
         $res = $this->update($updateArr,"request  = '$mobile' AND product_id = '$productID' AND label = '".$this->getLabel()."'");
         return $res; 
    }
    
    public function generateRatOTPAPI($params) {
        if(!isset($params['mobile']) || empty($params['mobile'])) {
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE);  
        }
        if(!isset($params['user_id']) || empty($params['user_id'])) {
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE);  
        }
        if(!isset($params['user_type']) || empty($params['user_type'])) {
            //throw new Exception('User Type not defined');
            throw new Exception(ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_MSG, ErrorCodes::ERROR_EDIGITAL_INVALID_CUSTOMER_CODE);
        }
        
        $otpType = isset($params['type']) ? $params['type'] : 'R';
        
        $length = isset($params['length']) ? $params['length'] : '6';
        // Check if valid otp exists then resend same otp
        
        $custdetails = $this->getCustomerOTPAPIById($params);
        if(!empty($custdetails)){
           // Update all previous
            $this->updateCustomerOTPAPIByMobile($params);
           
        }
        if($length == '4') {
          $otp = Alerts::generateAuthCode($length);                
        } else {
          $otp = Alerts::generateAuthCode();
        }
                
        //Send Auth Code to Customer
        return $this->ratCustomerOTPAPI(array(
            'otp'  => $otp, 
            'mobile' => $params['mobile'], 
            'request_type' => $otpType,
            'exception' => '',
            'user_id' => $params['user_id'],
            'user_type' => $params['user_type'],
            'ref_id' => $params['ref_id'],
            'product_id' => $params['product_id']
            )
        );
    }
    public function ratCustomerOTPAPI($params) {
        
       // $productName = $this->getProductName($params['product_id']);
        $productSupportInfo = $this->getProductSupportInfo($params['product_id']); 
        
        $productModel = new Products();
        $productinfo = $productModel->findById($params['product_id']);
        $prodConst = $productinfo['const'] ;
           
        $productName = $productSupportInfo['product_name'];
        $productsupportEmail = $productSupportInfo['support_email'];
        $productSMSsender = $productSupportInfo['sms_sender'];
        $productStaticOTP = $productSupportInfo['static_otp'];
         //$this->_db->beginTransaction();
             
          $m = new \App\Messaging\Remit\Ratnakar\Api();
         //Send Auth Code to Customer

         $userData = array(
                        'mobile'=> $params['mobile'],
                        'product_name' => $productName,
                        'auth_code' => $params['otp'],
                        'product_supportemail' => $productsupportEmail,
                        'sms_sender' => $productSMSsender,
                        'product_const' => $prodConst
                    );                               
            
         try {
            
        switch(strtoupper($params['request_type'])){
            case self::OTP_TYPE_CUST_REGISTRATION:
                $this->setLabel($this->getRefLabel('otp-cust'));
                $m->cardActivationAuth($userData);
                break;
            case self::OTP_TYPE_CUST_UPDATE:
                $this->setLabel($this->getRefLabel('otp_cust_update'));
                $m->customerEditAuth($userData); 
                break;
            case self::OTP_TYPE_BENE_REGISTRATION:
                $this->setLabel($this->getRefLabel('otp-bene'));
                $m->beneRegistrationAuth($userData);
                break;
            case self::OTP_TYPE_BENE_UPDATE:
                $this->setLabel($this->getRefLabel('otp_bene_update'));
                break;
            case self::OTP_TYPE_REMITTANCE:
                $this->setLabel($this->getRefLabel('otp_remittance'));
                if($productStaticOTP == FLAG_YES){
                    $remitterObj =  new Remit_Kotak_Remitter();
                    $remitter = $remitterObj->getremitter($userData['mobile'],$params['product_id']);
                    $userData['remitter_id'] = $remitter['id'];
                    
                    $m->remittanceStaticOtp($userData); 
                    $Obj =  new Remit_Kotak_Remittancerequest();
                    $lastOTPDetails = $Obj->getRemitterOTPDetails($userData['remitter_id']);
                    $params['otp'] = $lastOTPDetails['otp'];
                }else{
                    $m->remittanceAuth($userData);
                }
                break;
            case self::OTP_TYPE_TRANSFER:
                $this->setLabel($this->getRefLabel('otp_transfer'));
                $m->transactionAuth($userData);
                break;
            case self::OTP_TYPE_REFUND:
                $this->setLabel($this->getRefLabel('otp_refund'));
                $m->transactionAuth($userData);
                break;
            case self::OTP_TYPE_LOAD:
                $this->setLabel($this->getRefLabel('otp_load'));
                $m->loadAuth($userData);
                break;
            case self::OTP_TYPE_UNBLOCK:
                $this->setLabel($this->getRefLabel('otp_unblock'));
                if($prodConst == PRODUCT_CONST_RAT_BMS){
                    $m->cardUnblockAuth($userData);
                } else {
                    //
                }
                break;
         }
           
        return $this->insertReferenceAPI($params);
        } catch (Exception $e) {
            App_Logger::log($e->getMessage() , Zend_Log::ERR); 
            $code = $e->getCode();
            if(empty($code)) {
                $code = ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG;
            }            
	    //$code = (empty($e->getCode())) ? ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG : $e->getCode();
            $message = $e->getMessage();
            if(empty($message)) {
                $message = ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_CODE;
            }                        
	    //$code = (empty($e->getCode())) ? ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_MSG : $e->getCode();
	    //$message = (empty($e->getMessage())) ? ErrorCodes::ERROR_UNABLE_SMS_EXCEPTION_CODE : $e->getMessage(); 
	    throw new Exception($message,$code); 
        }
        return FALSE;        
    }
    
    /*
     * getBalanceSyncExceptions will return the card mapping load exceptions
     */

    public function getBalanceSyncExceptions($param) {
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($this->_name . " as r",array('r.val', 'r.exception as reason', 'r.date_created'))
                ->join(DbTable::TABLE_RAT_CORP_CARDHOLDER ." as rcc", "rcc.id = r.user_id", array('rcc.card_pack_id', 'rcc.status_ecs'))
               ->joinLeft(DbTable::TABLE_AGENTS . " as a", "rcc.by_agent_id = a.id",array('a.agent_code', 'concat(a.first_name," ", a.last_name) as agent_name'));               
        
        if ($from != '' && $to != ''){
            $select->where("r.date_created >= '" . $param['from'] . "'");
            $select->where("r.date_created <= '" . $param['to'] . "'");
        }

        $select->where('r.response = ?', STATUS_PENDING);
        return $this->fetchAll($select);
    }
    
    public function exportgetBalanceSyncException($params){
        $data = $this->getBalanceSyncExceptions($params);
        $data = $data->toArray();
        $retData = array();
        
        if(!empty($data))
        {
            foreach($data as $key=>$data){
                $retData[$key]['agent_code']  = $data['agent_code'];
                $retData[$key]['agent_name']  = $data['agent_name'];
                $retData[$key]['card_pack_id']  = $data['card_pack_id'];
                $retData[$key]['amount']  = $data['val'];
                $retData[$key]['error_reason'] = $data['reason'];
                $retData[$key]['date_time']    = $data['date_created'];
                $retData[$key]['status_ecs'] = ucfirst($data['status_ecs']);
            }
        }        
        return $retData;         
    }
}

