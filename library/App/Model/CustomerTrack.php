<?php

class CustomerTrack extends Track {

    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'id';

    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_CUSTOMER_TRACK;

    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
      private $_PRODUCT_ID;
      private $_CUSTOMER_ID;
      private $_LABEL_ID;
      private $_BANK_ID;
      const OTP_TYPE_LOAD = 'L';
      const OTP_TYPE_REGISTRATION = 'R';


      public function customerDetails($params, $productId, $customerId) {
         $this->_db->beginTransaction();
         try {
            $this->setProductId($productId);
            $this->setCustomerId($customerId);
            foreach ($params as $key => $val) {
                if ($this->isValidKey($key)) {
                    if($this->isValidVal($val)) { 
                        $this->setLabel($this->getLabelByKey($key));
                        $this->insertCustomer($val);
                    }
                }
            }
            $this->_db->commit();
        } catch (Exception $e) {
            App_Logger::log($e->getMessage() , Zend_Log::ERR);
            $this->_db->rollBack();
            return FALSE;
        }
    }
    
    public function customerDetailsAPI($params, $productId, $customerId, $bankId) {
         $this->_db->beginTransaction();
         try {
            $this->setProductId($productId);
            $this->setCustomerId($customerId);
            $this->setBankId($bankId);
            foreach ($params as $key => $val) {
                if ($this->isValidKey($key)) {
                    if($this->isValidVal($val)) { 
                        $this->setLabel($this->getLabelByKey($key));
                        $this->insertCustomerAPI($val);
                    }
                }
            }
            $this->_db->commit();
        } catch (Exception $e) {
            App_Logger::log($e->getMessage() , Zend_Log::ERR);
            $this->_db->rollBack();
            return FALSE;
        }
    }
    
    public function getLabelByKey($label) {
        $objectRelation = new ObjectRelations();
        switch ($label) {
            case 'mobile':
                $resLabel = TYPE_A;//Get it from constants
                break;
            case 'card_number':
                $resLabel = TYPE_B;
                break;
            case 'card_pack_id':
                $resLabel = TYPE_C;
                break;
            case 'crn':
                $resLabel = TYPE_D;
                break;
            case 'member_id':
                $resLabel = TYPE_E;
                break;
            case 'email':
                $resLabel = TYPE_F;
                break;
            case 'name_on_card':
                $resLabel = TYPE_G;
                break;
            case 'otp':
                $resLabel = TYPE_H;
                break;
            case 'otp_load':
                $resLabel = TYPE_I;
                break;
            case 'partner_ref_no':
                $resLabel = TYPE_J;
                break;
        }

        $labelId = $objectRelation->getLabelId($resLabel);
        if(!empty($labelId) && isset($labelId['id'])) {
             return $labelId['id'];
        }
        else{
             throw new Exception('Data is not Valid');
        }

    }

    public function insertCustomer($val) {
        $label = $this->getLabel();
        $custId = $this->getCustomerId();
        $productId = $this->getProductId();
        $insertArr = array(
            'product_id' => $productId,
            'customer_id' => $custId,
            'info' => $val,
            'flag' => $label,
        );
        //echo 'ADDING Customer!!!';exit;
        $this->save($insertArr);
        return $this->_db->lastInsertId();
        //return TRUE;
    }
    
    public function insertCustomerAPI($val) {
        $label = $this->getLabel();
        $custId = $this->getCustomerId();
        $bankId = $this->getBankId();
       
        $custId = $this->getCustomerId();
        $productId = $this->getProductId();
        $insertArr = array(
            'product_id' => $productId,
            'bank_id' => $bankId,
            'customer_id' => $custId,
            'info' => $val,
            'flag' => $label,
        );
        //echo 'ADDING Customer!!!';exit;
        $this->save($insertArr);
        return $this->_db->lastInsertId();
        //return TRUE;
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
    
      private function setBankId($bankId)
    {
       $this->_BANK_ID = $bankId;
    }
    
    public function getBankId() {
        if(isset($this->_BANK_ID) && !empty($this->_BANK_ID)) {
            return $this->_BANK_ID;
        }
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
    
    private function isValidKey($key){
        if(in_array($key,$this->isAllowedArray())){
            return TRUE;
        }
        else{
            return FALSE;
        }
    }
    
    private function isValidVal($val){
        if(!empty($val)){
            return TRUE;
        }
        else{
            return FALSE;
        }
    }
    
     private function isAllowedArray(){
           $allowedArr = array(
                    'card_number' ,'card_pack_id','member_id' ,'crn' ,
                    'product_id' ,'customer_id' ,'mobile','email','name_on_card','partner_ref_no'
                );
           return $allowedArr;
     }
     
      private function isAllowedExceptArray(){
           $allowedArr = array(
                    'card_number' ,'card_pack_id','member_id' ,'crn' 
                    ,'mobile','email','name_on_card','partner_ref_no'
                );
           return $allowedArr;
     }
        
    public function getCustomerDetails($params) {
         
         $label = '';
         $value = '';
//         $mobile = isset($params['mobile']) ? $params['mobile'] : '';
//         $email = isset($params['email']) ? $params['email'] : '';
//         $card = isset($params['card_number']) ? $params['card_number'] : '';
//         $pack = isset($params['card_pack_id']) ? $params['card_pack_id'] : '';
//         $memberID = isset($params['member_id']) ? $params['member_id'] : '';
//         $name = isset($params['name_on_card']) ? $params['name_on_card'] : '';
//         $crn = isset($params['crn']) ? $params['crn'] : '';
         foreach($params as $key => $val){
              if(in_array($key,$this->isAllowedExceptArray())){
              $label = $key;
              $value = $val;
           }
             
            
         }
         
         $flag = $this->getLabelByKey($label);
        //echo $flag;exit;
         $productId = isset($params['product_id']) ? $params['product_id'] : '';
         $customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
         
         
         $sql = $this->select();
         $sql->from(DbTable::TABLE_CUSTOMER_TRACK, array('id','customer_id','product_id'));
         if($productId != ''){
          $sql->where("product_id = '" . $productId . "'");
        }
        if($customerId != ''){
          $sql->where("customer_id = '" . $customerId . "'");
        }
        if($flag == '5' || $flag =='7' ) { 
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
         $sql->where("AES_DECRYPT(`info`,'".$decryptionKey."')  = '" . $value . "'");
         $sql->where("flag = '" . $flag . "'");
        } else {
         $sql->where("info = '" . $value . "'");
         $sql->where("flag = '" . $flag . "'");
            
        }
        $rs= $this->fetchRow($sql);                
        if(!empty($rs)) {
            return $rs;
        }
        return false;
    }
    
    
    public function getRatnakarCustomerDetails($params) {
         
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        
        $cust_ids = $this->getRatnakarCustomerIds($params);
		if(empty($cust_ids)) {
			return false;
		}
        $sql = 'SELECT * FROM customer_track '
                . ' WHERE customer_id IN ('.$cust_ids.') '
                . ' AND flag ="'.$this->getLabelByKey("card_number").'" AND '
                . ' SUBSTR(AES_DECRYPT(info,"'.$decryptionKey.'"),-4) ="'.$params['card_number'].'" '
                . ' AND product_id IN (' .RATNAKAR_PRODUCT_CODES .' )';
        $rs= $this->_db->fetchRow($sql);
        if(!empty($rs)) {
            return $rs;
        }
        return false;
    }
    
    
    public function getRatnakarCustomerIds($params) { 
         
        $sql = 'SELECT customer_id from customer_track '
                .' WHERE info="'.$params["mobile"].'" AND flag="'.$this->getLabelByKey("mobile").'" AND product_id IN (' .RATNAKAR_PRODUCT_CODES .' ) ';
             
        $rs= $this->_db->fetchAll($sql);
        
        $i = 0;
        foreach ($rs as $val) {
            $custidarr[$i] = $val['customer_id'];
            $i++;
        }
        
        $cust_id_str = implode(",", $custidarr);
        
        if(!empty($rs)) {
            return $cust_id_str;
        }
        return false;
         
    }
    
    
    
    public function getCustomerDetailsByLast4Digits($params) {
         
        $mobileFlag = $this->getLabelByKey('mobile');
        $cardFlag = $this->getLabelByKey('card_number');
         $productId = isset($params['product_id']) ? $params['product_id'] : '';
         $mobile    = isset($params['mobile']) ? $params['mobile'] : '';
         $cardNumber = isset($params['card_number_last_4']) ? $params['card_number_last_4'] : '';
         $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
         
         $sql = $this->_db->select()
            ->from(DbTable::TABLE_CUSTOMER_TRACK . ' as c')
            ->joinInner(DbTable::TABLE_CUSTOMER_TRACK . ' as t', ' c.customer_id= t.customer_id AND c.product_id= t.product_id ')
            ->where('c.info=?',$mobile)
            ->where('c.flag=?',$mobileFlag)
            ->where('SUBSTR(AES_DECRYPT(t.info,"'.$decryptionKey.'"),-4)=?',$cardNumber)
            ->where('t.flag=?',$cardFlag)
            ->where('c.product_id=?',$productId);
        $rs= $this->_db->fetchRow($sql);     
        if(!empty($rs)) {
            return $rs;
        }
        return false;
    }

   
     public function customerLoadOTP($params) {
//         echo '<pre>';print_r($params);exit;
        $this->session = new Zend_Session_Namespace("App.Operation.Controller");
        //$this->_db->beginTransaction();
         $m = new App\Messaging\Corp\Ratnakar\Operation();
         try {
            $this->setProductId($params['product_id']);
            $this->setCustomerId($params['customer_id']);
            if($params['request_type'] == self::OTP_TYPE_LOAD) {
                $this->setLabel($this->getLabelByKey('otp_load'));
            } else {
                $this->setLabel($this->getLabelByKey('otp'));
            }
          //echo $params['product_id']. ' : ' . $params['customer_id']. '<BR />';
          //$custInfo = $this->getCustomerInfoDetails($params['product_id'],$params['customer_id']);
          $mobile = $this->getCustomerMobile($params['product_id'],$params['customer_id']);
          //echo $mobile.'**';exit;
          //echo 'Mobile: '.$mobile;exit;
        //Send Auth Code to Customer
         $userData = array('mobile'=> $mobile,
                           'product_name' => NSDC_PRODUCT,
                           'auth_code' => $params['otp'],
                           'product_id' => $params['product_id'],
                            );
                if(isset($this->session->card_load_auth))
                    $m->cardLoadAuth($userData,$resend = TRUE);
                else
                    $m->cardLoadAuth($userData);
         
        return $this->insertCustomer($params['otp']);
            //$this->_db->commit();
        } catch (Exception $e) {
           // echo '<pre>';print_r($e->getMessage());exit;
            App_Logger::log($e->getMessage() , Zend_Log::ERR);
            //$this->_db->rollBack();

        }
        return FALSE;        
    }
    
    
    public function getCustomerMobile($productId, $customerId) {

        if(!isset($customerId) || empty($customerId)) {
            throw new Exception('Customer not defined');
        }
        
        if(!isset($productId) || empty($productId)) {
            throw new Exception('Product not defined');            
        }
        
        $custInfo = $this->getCustomerInfoDetails($productId,$customerId);
        //echo '<pre>';print_r($custInfo);exit
        if(empty($custInfo) || !isset($custInfo['mobile'])) {
            throw new Exception('Customer Mobile not found');
        }
        return $custInfo['mobile'];
    }
    
    public function generateLoadOTP($params) {
        if(!isset($params['customer_id']) || empty($params['customer_id'])) {
            throw new Exception('Customer not defined');
        }
        
        if(!isset($params['product_id']) || empty($params['product_id'])) {
            throw new Exception('Product not defined');            
        }
        //echo '<pre>';print_r($params);exit;
        $otp = Alerts::generateAuthCode();
        $otpType = isset($params['type']) ? $params['type'] : 'R';
        //Send Auth Code to Customer
        return $this->customerOTP(array(
            'otp'  => $otp, 
            'product_id' => $params['product_id'], 
            'customer_id' => $params['customer_id'], 
            'request_type' => $otpType
            )
        );
    }
    
     public function verifyCustomerOTP($params) {
         
         $allowedSessionInMin = App_DI_Container::get('ConfigObject')->system->paytronic->timeout_in_min;
         $otp = isset($params['otp']) ? $params['otp'] : '';
         
        if($params['request_type'] == self::OTP_TYPE_LOAD) {
            $flag = $this->getLabelByKey('otp_load');
        } else {
            $flag = $this->getLabelByKey('otp');
        }         
         
         $productId = isset($params['product_id']) ? $params['product_id'] : '';
         $customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
         
         
         $sql = $this->select();
         $sql->from(DbTable::TABLE_CUSTOMER_TRACK);
         if($productId != ''){
          $sql->where("product_id = '" . $productId . "'");
        }
        if($customerId != ''){
          $sql->where("customer_id = '" . $customerId . "'");
        }
         $sql->where("info = '" . $params['otp'] . "'");
         $sql->where("flag = '" . $flag . "'");
         $sql->where("date_updated > " . new Zend_Db_Expr('DATE_SUB(NOW(),INTERVAL '.$allowedSessionInMin.' MINUTE)')  );
        $rs= $this->fetchRow($sql);                
        if(!empty($rs)) {
            return $rs;
        }
        return FALSE;
    }
    
    public function isCustomerValid($productId,$custId) {
        $object = parent::getCustomerObject($productId);
        if($object != FALSE) {
            $custStatus = $object->getCustomerInfo($custId);
            if($custStatus){
                return TRUE;
            }
            else{
                return FALSE;
            }
        }
    }
    function getCustomerInfoDetails($productId,$custId) {
        $object = parent::getCustomerObject($productId);
//        var_dump($object);exit;
        if($object != FALSE) {
            return $object->getCustomerInfo($custId);
        }
        return FALSE;
    }
    
    public function getObject($productId,$params) {
        $object = parent::getCustomerObject($productId);
        if($object != FALSE) {
            return $object;
        }
        return FALSE;
    }
    
    
    public function customerRegistration($productId, $params) {
        $object = parent::getCustomerObject($productId);
        if(!empty($object)) {
            return $object->addCustomer($params);
        }
        return false;
    }
    
    public function generatenewOTP($params) {
        if(!isset($params['mobile']) || empty($params['mobile'])) {
            throw new Exception('Mobile not defined');   
        }
        if(!isset($params['user_id']) || empty($params['user_id'])) {
            throw new Exception('User Id not defined');   
        }
        if(!isset($params['user_type']) || empty($params['user_type'])) {
            throw new Exception('User Type not defined');   
        }
        
        $otp = Alerts::generateAuthCode();
        $otpType = isset($params['type']) ? $params['type'] : 'R';
        //Send Auth Code to Customer
        return $this->customernewOTP(array(
            'otp'  => $otp, 
            'mobile' => $params['mobile'], 
            'request_type' => $otpType,
            'exception' => '',
            'user_id' => $params['user_id'],
            'user_type' => $params['user_type'],
            'ref_id' => $params['ref_id']
            )
        );
    }
    
     public function customernewOTP($params) {
      
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
                           'product_name' => NSDC_PRODUCT,
                           'auth_code' => $params['otp'],
             );                               
                
                     $m->cardLoadAuth($userData);
        return $this->insertReference($params);
            //$this->_db->commit();
        } catch (Exception $e) {
            //echo '<pre>';print_r($e->getMessage());exit;
            App_Logger::log($e->getMessage() , Zend_Log::ERR);
            //$this->_db->rollBack();

        }
        return FALSE;        
    }
 
     public function insertReference($param) {
         
        $refModel = new Reference();
        $insertArr = array(
            'label' => $this->getLabel(),
            'user_id' => $param['user_id'],
            'user_type' => $param['user_type'],
            'method' => $param['request_type'],
            'request' => $param['mobile'],
            'response' => $param['otp'],
            'exception' => $param['exception'],
            'ref_id' => $params['ref_id'],
            'user_ip' => $this->formatIpAddress(Util::getIP()),
            'date_created' =>  new Zend_Db_Expr('NOW()')
        );
        $rs = $refModel->insertData($insertArr);
        return $rs;
    }
    
    
     public function verifyCustomernewOTP($params) {
         
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
         $sql->where("response = '" . $params['otp'] . "'");
         $sql->where("date_created > " . new Zend_Db_Expr('DATE_SUB(NOW(),INTERVAL '.$allowedSessionInMin.' MINUTE)')  );
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
            case 'otp_load':
                $resLabel = TYPE_OTP_LOAD;
                break;
        }

      return $resLabel;
        

    }
    
    
     public function verifyWalletCode($productId,$walletCode) {
         $sql = $this->_db->select()
                ->from(DbTable::TABLE_PURSE_MASTER)
                ->where("product_id = ?",$productId)
                ->where("code = ?",$walletCode)
                ->where("status = ?",STATUS_ACTIVE);
         $rs= $this->_db->fetchRow($sql);                
        if(!empty($rs)) {
            return $rs;
        }
        return FALSE;
    }
    
     public function getCustomerCardNumber($productId, $customerId) {

        if(!isset($customerId) || empty($customerId)) {
            throw new Exception('Customer not defined');
        }
        
        if(!isset($productId) || empty($productId)) {
            throw new Exception('Product not defined');            
        }
        
        $custInfo = $this->getCustomerInfoDetails($productId,$customerId);
       
        if(empty($custInfo) || !isset($custInfo['card_number'])) {
            throw new Exception('Customer Card Number not found');
        }
        return $custInfo['card_number'];
    }
    
    public function getCardholderInfoForActivationAPI($params)
    {
         $mobileFlag = $this->getLabelByKey('mobile');
         $cardPackIdFlag = $this->getLabelByKey('card_pack_id');
         $mobile    = isset($params['mobile']) ? $params['mobile'] : '';
         $cardPackId = isset($params['card_pack_id']) ? $params['card_pack_id'] : '';
         $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
         $sql = $this->_db->select()
            ->from(DbTable::TABLE_CUSTOMER_TRACK . ' as c')
            ->joinInner(DbTable::TABLE_CUSTOMER_TRACK . ' as t', ' c.customer_id= t.customer_id AND c.product_id= t.product_id ')
            ->where('c.info =?',$mobile)
            ->where('c.flag =?',$mobileFlag)
            ->where("AES_DECRYPT(`t.info`,'".$decryptionKey."')  = '" . $cardPackId . "'")     
            //->where('t.info =?',$cardPackId)
            ->where('t.flag =?',$cardPackIdFlag);
        
        $rs= $this->_db->fetchRow($sql);     
        if(!empty($rs)) {
           $custInfo = $this->getCustomerInfoDetails($rs['product_id'],$rs['customer_id']);
       
        return Util::toArray($custInfo);
        }
        return false;
    }

     public function getCardholderInfo($params)
    {
         $mobileFlag = $this->getLabelByKey('mobile');
         $cardNumberFlag = $this->getLabelByKey('card_number');
         $mobile    = isset($params['mobile']) ? $params['mobile'] : '';
         $cardNumberId = isset($params['card_number']) ? $params['card_number'] : '';
         $productId = isset($params['product_id']) ? $params['product_id'] : '';
         $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $sql = $this->_db->select()
            ->from(DbTable::TABLE_CUSTOMER_TRACK . ' as c');
            $sql->where('c.info =?',$mobile);
            $sql->where('c.flag =?',$mobileFlag);
            if($cardNumberId != ''){
            $sql->joinInner(DbTable::TABLE_CUSTOMER_TRACK . ' as t', ' c.customer_id= t.customer_id AND c.product_id= t.product_id ');
            
            $sql->where('SUBSTR(AES_DECRYPT(t.info,"'.$decryptionKey.'"),-4) =?',$cardNumberId);
            $sql->where('t.flag =?',$cardNumberFlag);
            }
            if($productId != ''){
            $sql->where('c.product_id=?',$productId);
            }
        $rs= $this->_db->fetchRow($sql);     
        if(!empty($rs)) {
           $custInfo = $this->getCustomerInfoDetails($rs['product_id'],$rs['customer_id']);
       
        return Util::toArray($custInfo);
        }
        return false;
    }
    
    
      public function generateSMSDetails($params,$smsType)
     {
       $m = new \App\Messaging\System\Operation();
       $productModel = new Products();
       
       $productDetails = $productModel->getProductInfo($params['product_id']);
       
       $custPurse = $this->getCustomerBalanceDetails($params['product_id'],$params['cust_id']);
       
       $smsData = array(       
                                'last_four' => substr($params['card_number'], -4),
                                'product_name' => $productDetails->name,
                                'balance' => $custPurse['sum'],
                                'mobile' => $params['mobile'],
                                'mini_stmt' => 'MINI STMT',
                        );
             switch ($smsType) {
                case 'CARD_BLOCK_SMS' :
                   $m->cardBlock($smsData);
                    break;
                case 'CARD_UNBLOCK_SMS' :
                    $m->cardUnblock($smsData);
                    break;
                case 'BALANCE_ENQUIRY_SMS' :
                    $m->balanceEnquiry($smsData);
                    break;
                case 'MINI_STATEMENT_SMS' :
                    $m->miniStatement($smsData);
                    break;
                default :
                    return FALSE;
                    
            }
       
       
     }
    
    function getCustomerBalanceDetails($productId,$custId) {
        $object = parent::getCustomerPurseObject($productId);
        if($object != FALSE) {
            return $object->getCustBalance($custId);
        }
        return FALSE;
    } 
    
     public function editCustomerDetails($params, $productId, $customerId, $bankId=0) {
         $this->_db->beginTransaction();
         try {
            $this->setProductId($productId);
            $this->setCustomerId($customerId);
            $this->setBankId($bankId);
            foreach ($params as $key => $val) {
                if ($this->isValidKey($key)) {
                    if($this->isValidVal($val)) { 
                        $this->setLabel($this->getLabelByKey($key));
                        $this->updateCustomer($val);
                    }
                }
            }
            $this->_db->commit();
        } catch (Exception $e) {
            App_Logger::log($e->getMessage() , Zend_Log::ERR);
            $this->_db->rollBack();
            return FALSE;
        }
    }
    
     public function updateCustomer($val) {
        $label = $this->getLabel();
        $custId = $this->getCustomerId();
        $productId = $this->getProductId();
        $uvalue = array(
            'info' => $val,
              );
        $where = array(
            'product_id' => $productId,
            'customer_id' => $custId,
            'flag' => $label,
        );
        
        $res = $this->getCustomerInfo($where);
        
        if($res == true) {
            $this->update($uvalue, $where);
        } else {
            $this->insertCustomerAPI($val);
        }
        return true;
    }
  
    public function getCustomerInfo($params) {

        $productId = isset($params['product_id']) ? $params['product_id'] : '';
        $customerId = isset($params['customer_id']) ? $params['customer_id'] : '';
        $flag = isset($params['flag']) ? $params['flag'] : '';
         
        $sql = $this->select();
        $sql->from(DbTable::TABLE_CUSTOMER_TRACK, array('id'))
            ->where("product_id = ?", $productId)
            ->where("customer_id = ?", $customerId)
            ->where("flag = ?", $flag);
        $rs = $this->fetchRow($sql);                
        if(!empty($rs)) {
            return true;
        }
        return false;
    }
} 