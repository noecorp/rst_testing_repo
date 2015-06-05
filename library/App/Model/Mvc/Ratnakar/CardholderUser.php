<?php

class Mvc_Ratnakar_CardholderUser extends Mvc_Ratnakar
{
    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'id';
    
    protected $_msg = '';
    
    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_RAT_CORP_CARDHOLDER;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    protected $_rowClass = 'App_Table_RblMvcCardholderUser';
    
    
    protected $_cardholderId;
    
    public function addCardHolder($param){        
        
        $chData = $param['chData'];   
        $oldVals = $param['oldVals']; 
        $mobObj = new Mobile();  
        $emailObj = new Email();
        $validObj = new Validator();
        $custProductModel = new Corp_Ratnakar_CustomerProduct();
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $objCustomerMaster = new CustomerMaster();
        $bankModel = new Banks();
        $productModel = new Products();
        $crnModel = new CRN();
        $customerTrackModel = new CustomerTrack();
        $user = Zend_Auth::getInstance()->getIdentity(); 
        $chIdUpd = isset($oldVals['chId'])?$oldVals['chId']:0;
        $mobileNumberOld = isset($oldVals['mobile_number_old'])?$oldVals['mobile_number_old']:'';
        $emailOld = isset($oldVals['email_old'])?$oldVals['email_old']:'';
        $agentId = isset($chData['reg_agent_id'])?$chData['reg_agent_id']:'';
        $opsId = isset($chData['reg_ops_id'])?$chData['reg_ops_id']:'';
        $session = new Zend_Session_Namespace('App.Agent.Controller');
        $ip = $this->formatIpAddress(Util::getIP());
        $chDetails['ip'] = $ip;
        // arn duplicacy check
        
        // mobile duplicacy check
        if( ($chIdUpd==0) || ($mobileNumberOld!='' && $mobileNumberOld!=$chData['mobile_number']) ) {
            try {  
                $mobCheck = $mobObj->checkRatCardholderMobileDuplicate($chData['mobile_number'],$param['product_id']);                
            } catch (Exception $e ) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                return $e->getMessage();
            }   
        } 
     
        // email duplicacy check
        if( ($chIdUpd==0) || ($emailOld!='' && $emailOld!=$chData['email']) ) {
            try {                
                $emailCheck = $emailObj->checkRatCardholderEmailDuplicate($chData['email'],$param['product_id']);                
            } catch (Exception $e ) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                return $e->getMessage();
            }   
        }       
     

        if($chIdUpd<1) {
            //$resp = $this->_db->insert(DbTable::TABLE_RAT_MVC_CARDHOLDER, $chData); // adding card holder to t_cardholders
            $productDetail = $productModel->getProductInfo($param['product_id']);
            $customerMasterId = $objCustomerMaster->generateCustomerId(array('bank_id' => $productDetail['bank_id']));
            $custMasterDetails = $objCustomerMaster->findById($customerMasterId);
            $custMasterDetail = Util::toArray($custMasterDetails);
            // adding data in rat_customer_master table
            
            $ratCustomerMasterData = array(
                'customer_master_id' => $customerMasterId,
                'shmart_crn' => $custMasterDetail['shmart_crn'],
                'first_name' => $chData['first_name'],
                'middle_name' => $chData['middle_name'],
                'last_name' => $chData['last_name'],
                'aadhaar_no' => (isset($chData['IdentityProofType']) && strtolower($chData['IdentityProofType']) == 'aadhar card') ? $chData['IdentityProofDetail'] : '',
                'pan' => (isset($chData['IdentityProofType']) && strtolower($chData['IdentityProofType']) == 'pan card') ? $chData['IdentityProofDetail'] : '',
                'mobile_country_code' => isset($chData['mobile_country_code']) ? $chData['mobile_country_code'] : '',
                'mobile' => $chData['mobile_number'],
                'email' => $chData['email'],
                'gender' => $chData['gender'],
                'date_of_birth' => isset($chData['date_of_birth']) ? $chData['date_of_birth'] : '',
                'status' => STATUS_ACTIVE,
            );
            $this->_db->insert(DbTable::TABLE_RAT_CUSTOMER_MASTER, $ratCustomerMasterData);
            $ratCustomerId = $this->_db->lastInsertId();
            
            $purseDetails = $masterPurseModel->getPurseDetailsbyBankIdProductId($param['product_id'], $productDetail['bank_id']);
            foreach ($purseDetails as $purseDetail) {
                $purseArr = array(
                    'customer_master_id' => $customerMasterId,
                    'rat_customer_id' => $ratCustomerId,
                    'product_id' => $param['product_id'],
                    'purse_master_id' => $purseDetail['id'],
                    'bank_id' => $productDetail['bank_id'],
                    'date_updated' => new Zend_Db_Expr('NOW()')
                );
                $purseParam = array('rat_customer_id' => $ratCustomerId, 'purse_master_id' => $purseDetail['id']);
                $purseDetails = $custPurseModel->getCustPurseDetails($purseParam);
                if (empty($purseDetails)) { // If purse entry not found
                    $custPurseModel->save($purseArr);
                }
            } 
            
            $data = array(
                'afn' => $chData['arn'],
                'card_number' => '',
                'crn' => $custMasterDetail['shmart_crn'],
                'card_pack_id' => '',
                'rat_customer_id' => $ratCustomerId,
                'customer_master_id' => $customerMasterId,
                'medi_assist_id' => '',
                'employee_id' => '',
                'customer_type' => '',
                'title' => $chData['title'],
                'first_name' => $chData['first_name'],
                'middle_name' => $chData['middle_name'],
                'last_name' => $chData['last_name'],
                'name_on_card' =>  $chData['first_name']." ". $chData['last_name'],
                'gender' => strtolower($chData['gender']),
                'date_of_birth' => Util::returnDateFormatted($chData['date_of_birth'], "d/m/Y", "Y-m-d", "/", "-"),
                'mobile' => $chData['mobile_number'],
                'email' => $chData['email'],
                'by_agent_id' => $user->id,
                'product_id' => $param['product_id'],
                'status_ops' => STATUS_APPROVED,
                'status_ecs' => STATUS_WAITING,
                'status' => $chData['status'],
                'date_created' => new Zend_Db_Expr('NOW()')
            );
            $this->_db->insert(DbTable::TABLE_RAT_CORP_CARDHOLDER, $data);
            $cardholderId = $this->_db->lastInsertId(DbTable::TABLE_RAT_CORP_CARDHOLDER, 'id');
            
            // Entry in Customer product table
            $prodArr = array('product_customer_id' => $cardholderId,
                'product_id' => $param['product_id'],
                'program_type' => $productDetail['program_type'],
                'bank_id' => $productDetail['bank_id'],
                'by_agent_id' => (isset($user->id) && $user->id > 0 )? $user->id : 0 ,
                'by_ops_id' => (CURRENT_MODULE == MODULE_OPERATION)? $user->id:0,
                'by_corporate_id' =>  (isset($chData['corporate_id']) && $chData['corporate_id'] > 0 )? $chData['corporate_id'] : 0,
                'date_created' => new Zend_Db_Expr('NOW()'),
                'status' => STATUS_ACTIVE);
            $custProductModel->save($prodArr);
            
            $chData['cardholder_id']=$cardholderId;
            $chData['by_agent_id']=(isset($user->id) && $user->id > 0 )? $user->id : 0;
            $chData['crn']=$custMasterDetail['shmart_crn'];
            // adding cardholder details to t_cardholder_details
            $this->_db->insert(DbTable::TABLE_RAT_MVC_CARDHOLDER_DETAILS, $chData); 
            $session->cardholder_id = $chData['cardholder_id'];
            $session->cardholder_product_id = $param['product_id'];
        } else {    
            $row2 = $this->getCardHolderInfo($chIdUpd);
            $ratCustomerMasterData = array(
                'first_name' => $chData['first_name'],
                'middle_name' => $chData['middle_name'],
                'last_name' => $chData['last_name'],
                'mobile_country_code' => isset($chData['mobile_country_code']) ? $chData['mobile_country_code'] : '',
                'mobile' => $chData['mobile_number'],
                'email' => $chData['email'],
                'gender' => $chData['gender'],
                'date_of_birth' => isset($chData['date_of_birth']) ? $chData['date_of_birth'] : '',
                'status' => $chData['status'],
            );
            $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_MASTER, $ratCustomerMasterData, 'customer_master_id="'.$row2['customer_master_id'].'"');

            $productDetail = $productModel->getProductInfo($param['product_id']);
            $purseDetails = $masterPurseModel->getPurseDetailsbyBankIdProductId($param['product_id'], $productDetail['bank_id']);
            foreach ($purseDetails as $purseDetail) {
                $purseArr = array(
                    'customer_master_id' => $row2['customer_master_id'],
                    'rat_customer_id' => $row2['rat_customer_id'],
                    'product_id' => $param['product_id'],
                    'purse_master_id' => $purseDetail['id'],
                    'bank_id' => $productDetail['bank_id'],
                    'date_updated' => new Zend_Db_Expr('NOW()')
                );
                $purseParam = array('rat_customer_id' => $row2['rat_customer_id']);
                $purseDetails = $custPurseModel->getAllPurse($purseParam);
                if (!empty($purseDetails)) { // If purse entry found
                    $custPurseModel->update($purseArr, 'rat_customer_id="'.$row2['rat_customer_id'].'"');
                }
            } 

            $data = array(
                'afn' => $chData['arn'],
                'card_number' => '',
                'crn' => '',
                'card_pack_id' => '',
                'rat_customer_id' => $row2['rat_customer_id'],
                'customer_master_id' => $row2['customer_master_id'],
                'medi_assist_id' => '',
                'employee_id' => '',
                'customer_type' => '',
                'title' => $chData['title'],
                'first_name' => $chData['first_name'],
                'middle_name' => $chData['middle_name'],
                'last_name' => $chData['last_name'],
                'name_on_card' =>  $chData['first_name']." ". $chData['last_name'],
                'gender' => strtolower($chData['gender']),
                'date_of_birth' => Util::returnDateFormatted($chData['date_of_birth'], "d/m/Y", "Y-m-d", "/", "-"),
                'mobile' => $chData['mobile_number'],
                'email' => $chData['email'],
                'by_agent_id' => $user->id,
                'product_id' => $param['product_id'],
                'status_ops' => STATUS_APPROVED,
                'status_ecs' => STATUS_WAITING,
                'status' => $chData['status'],
                'date_created' => new Zend_Db_Expr('NOW()')
            );
            $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER, $data, 'id="'.$chIdUpd.'"');
            $this->_db->update(DbTable::TABLE_RAT_MVC_CARDHOLDER_DETAILS, $chData, 'cardholder_id="'.$chIdUpd.'"');             
            $oprId = isset($chDetails['by_ops_id']) ? $chDetails['by_ops_id'] : '';
            $session->cardholder_product_id = $param['product_id'];
        }    
        return 'success';               
    }
    
     public function getCardHolderInfo($cardholderId = 0, $status='', $mobile=0)
     {
        
        $mobLen = strlen($mobile);
        if(($cardholderId != 0 && is_numeric($cardholderId)) || $mobLen==10) {
                   
               if($status!=''){

                    $select = $this->_db->select()
                    ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as ch", array('ch.id','ch.by_agent_id','ch.by_ops_id','ch.date_created','ch.date_approval','ch.crn','ch.email','ch.title','ch.first_name','ch.middle_name','ch.last_name','ch.mobile','ch.status','ch.customer_master_id', 'ch.rat_customer_id', 'ch.bank_id'))
                    ->joinLeft(DbTable::TABLE_RAT_MVC_CARDHOLDER_DETAILS." as chd", "ch.id = chd.cardholder_id AND chd.status = '".STATUS_ACTIVE."'", array('chd.cardholder_id','chd.date_of_birth', 'state', 'city', 'address_line1', 'address_line2', 'country', 'gender', 'mother_maiden_name', 'pincode', 'arn','customer_mvc_type','device_id','educational_qualifications','shmart_rewards','family_members','already_bank_account','vehicle_type','alternate_contact_number','products_acknowledgement','rewards_acknowledgement','chd.mobile_country_code','chd.mobile_number','chd.title as rat_title'))                    
                    ->where('ch.id =?',$cardholderId)                    
                    ->where('chd.status =?',STATUS_ACTIVE)
                    ->where('chd.cardholder_id =?',$cardholderId)
                    ->order('chd.status asc');                   

               }else if($mobLen<10) {

                    $select = $this->_db->select()
                    ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as ch", array('ch.id','ch.by_agent_id','ch.by_ops_id','ch.date_created','ch.date_approval','ch.crn','ch.email','ch.title','ch.first_name','ch.middle_name','ch.last_name','ch.mobile','ch.status','ch.customer_master_id', 'ch.rat_customer_id', 'ch.bank_id'))
                    ->joinLeft(DbTable::TABLE_RAT_MVC_CARDHOLDER_DETAILS." as chd", "ch.id = chd.cardholder_id", array('chd.cardholder_id','chd.date_of_birth', 'state', 'city', 'address_line1', 'address_line2', 'country', 'gender', 'mother_maiden_name', 'pincode', 'arn','customer_mvc_type','device_id','educational_qualifications','shmart_rewards','family_members','already_bank_account','vehicle_type','alternate_contact_number','products_acknowledgement','rewards_acknowledgement', 'status', 'date_created','chd.mobile_country_code','chd.mobile_number','chd.title as rat_title'))                    
                    ->where('ch.id =?',$cardholderId)                    
                    ->order('chd.status asc');                    
                          
               } else { // on mobile basis
                    $select = $this->_db->select()
                    ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as ch", array('ch.id','ch.by_agent_id','ch.by_ops_id','concat(ch.first_name," ",ch.last_name) as name','ch.date_created','ch.date_approval','ch.crn','ch.email','ch.title','ch.first_name','ch.middle_name','ch.last_name','ch.mobile','ch.status','ch.customer_master_id', 'ch.rat_customer_id', 'ch.bank_id'))
                    ->joinLeft(DbTable::TABLE_RAT_MVC_CARDHOLDER_DETAILS." as chd", "ch.id = chd.cardholder_id AND chd.status = '".STATUS_ACTIVE."'", array('chd.cardholder_id','chd.date_of_birth', 'state', 'city', 'address_line1', 'address_line2', 'country', 'gender', 'mother_maiden_name', 'pincode', 'arn','customer_mvc_type','device_id','educational_qualifications','shmart_rewards','family_members','already_bank_account','vehicle_type','alternate_contact_number','products_acknowledgement','rewards_acknowledgement', 'status', 'res_type','chd.mobile_country_code','chd.mobile_number','chd.title as rat_title'))                    
                    ->where('ch.mobile=?',$mobile);                                            
                  
               }

          return  $this->_db->fetchRow($select);            
        }
        return false;
     }
     
     public function updateOffers($param) {   
       
          $session = new Zend_Session_Namespace('App.Agent.Controller');         
          $alreadyAdded = isset($session->cardholder_offers)?$session->cardholder_offers:'0'; 
        
          if($alreadyAdded==1)
               $resp = $this->_db->update(DbTable::TABLE_RAT_MVC_CARDHOLDER_OFFERS, $param, 'cardholder_id="'.$param['cardholder_id'].'"'); 
          else 
          {
              $resp = $this->_db->insert(DbTable::TABLE_RAT_MVC_CARDHOLDER_OFFERS, $param);    
              $session->cardholder_offers=1;
          }      
          return true;
     }
     
     public function updateCardholder($param) { 
          
          $opsId = isset($param['by_ops_id'])?$param['by_ops_id']:'';
          $ip = $this->formatIpAddress(Util::getIP());
          $param['ip'] = $ip;
            
           if($opsId>0){
               $resp = $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER, array('status'=>STATUS_INACTIVE), 'cardholder_id="'.$param['cardholder_id'].'"'); 
               $resp = $this->_db->insert(DbTable::TABLE_RAT_MVC_CARDHOLDER_DETAILS, $param); 
               
          }else{        
                $paramCHD = array();
                if(isset($param['status_ecs'])&& $param['status_ecs']!=''){
                    $paramCH['status_ecs'] = $param['status_ecs'];
                    $resp = $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER,$paramCH, 'id="'.$param['cardholder_id'].'"'); 
                    unset($param['status_ecs']);
                    
                }
                if(isset($param['address_line1'])){
                    $paramCHD = array();
                    $paramCH['address_line1'] = $param['address_line1'];
                    $paramCH['address_line2'] = $param['address_line2'];
                    $paramCH['country'] = $param['country'];
                    $paramCH['state'] = $param['state'];
                    $paramCH['pincode'] = $param['pincode'];
                    $paramCH['landline'] = $param['alternate_contact_number'];
                    $paramCH['mother_maiden_name'] = $param['mother_maiden_name'];
                    $paramCH['customer_type'] = TYPE_NONKYC;
                    $resp = $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER,$paramCH, 'id="'.$param['cardholder_id'].'"'); 
                }
               
               $resp = $this->_db->update(DbTable::TABLE_RAT_MVC_CARDHOLDER_DETAILS,$param, 'cardholder_id="'.$param['cardholder_id'].'"'); 
              
               $session = new Zend_Session_Namespace('App.Agent.Controller');         
               $session->cardholder_step2 = 1;
          }
          return true;
     }
     
     public function getCardHolderOffers($cardholderId = 0)
     {
          if($cardholderId != 0 && is_numeric($cardholderId) ) {
                     
               $select = $this->_db->select()
                         ->from(DbTable::TABLE_RAT_MVC_CARDHOLDER_OFFERS." as cho")                                                           
                         ->where('cardholder_id =?',$cardholderId)
                         ->where('status =?',STATUS_ACTIVE);
               return  $this->_db->fetchRow($select);            
          }
          return false;
     }
     
     public function updateEmailVerification($param){
        
          $email = new Email();    
         
          $cardholderId = $param['cardholder_id'];
          try{
              $id = $email->updateEmailVerification($param); // updating email verification
              $chData['email_verification_id'] = $id;
              $chData['email_verification_status'] = STATUS_PENDING;
              $this->update($chData,"id=".$cardholderId);
          }catch (Exception $e ) {
                  App_Logger::log($e->getMessage(), Zend_Log::ERR);
                  return $e->getMessage();
          }  
        
     }
     public function getCardHolderProducts($cardholderId = 0)
     {
          if($cardholderId != 0 && is_numeric($cardholderId) ) {
                   
               $select = $this->_db->select()
                         ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER)                                                           
                         ->where('id =?',$cardholderId)
                         ->where('status =?',STATUS_ACTIVE);
               
               return  $this->_db->fetchRow($select);            
          }
          return false;
     }
     public function getCardHolderInfoApproved($cardholderId = 0, $status='', $mobile=0,$productId=0)
     {
      
          $mobLen = strlen($mobile);
          if(($cardholderId != 0 && is_numeric($cardholderId)) || $mobLen==10) {
                
                if($status!=''){

                    $select = $this->_db->select()
                    ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as ch", array('ch.id','ch.product_id','ch.by_agent_id','ch.by_ops_id','ch.date_created','ch.date_approval','ch.crn','ch.email','ch.title','ch.first_name','ch.middle_name','ch.last_name','ch.mobile','ch.status','ch.customer_master_id', 'ch.rat_customer_id', 'ch.bank_id'))
                    ->joinLeft(DbTable::TABLE_RAT_MVC_CARDHOLDER_DETAILS." as chd", "ch.id = chd.cardholder_id AND chd.status = '".STATUS_ACTIVE."'", array('chd.cardholder_id','chd.date_of_birth', 'state', 'city', 'address_line1', 'address_line2', 'country', 'gender', 'mother_maiden_name', 'pincode', 'arn','customer_mvc_type','device_id','educational_qualifications','shmart_rewards','family_members','already_bank_account','vehicle_type','alternate_contact_number','products_acknowledgement','rewards_acknowledgement','chd.mobile_country_code','chd.mobile_number','chd.title as rat_title'))
                    ->joinLeft(DbTable::TABLE_RAT_CUSTOMER_PURSE." as chp", "ch.customer_master_id = chp.customer_master_id", array('chp.id as customer_purse_id','chp.purse_master_id'))                    
                    ->where('ch.id =?',$cardholderId)                    
                    ->where('chd.status =?',STATUS_ACTIVE)
                    ->where('chd.cardholder_id =?',$cardholderId)
                    ->order('chd.status asc');
                    if($productId){
                        $select->where('ch.product_id =?',$productId);
                    }

               }else if($mobLen<10) {

                    $select = $this->_db->select()
                    ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as ch", array('ch.id','ch.product_id','ch.by_agent_id','ch.by_ops_id','ch.date_created','ch.date_approval','ch.crn','ch.email','ch.title','ch.first_name','ch.middle_name','ch.last_name','ch.mobile','ch.status','ch.customer_master_id', 'ch.rat_customer_id', 'ch.bank_id'))
                    ->joinLeft(DbTable::TABLE_RAT_MVC_CARDHOLDER_DETAILS." as chd", "ch.id = chd.cardholder_id", array('chd.cardholder_id','chd.date_of_birth', 'state', 'city', 'address_line1', 'address_line2', 'country', 'gender', 'mother_maiden_name', 'pincode', 'arn','customer_mvc_type','device_id','educational_qualifications','shmart_rewards','family_members','already_bank_account','vehicle_type','alternate_contact_number','products_acknowledgement','rewards_acknowledgement', 'status', 'date_created','chd.mobile_country_code','chd.mobile_number','chd.title as rat_title'))
                    ->joinLeft(DbTable::TABLE_RAT_CUSTOMER_PURSE." as chp", "ch.customer_master_id = chp.customer_master_id", array('chp.id as customer_purse_id','chp.purse_master_id'))                    
                    ->where('ch.id =?',$cardholderId)   
                    ->where('ch.status =?',STATUS_ACTIVE)
                    ->order('chd.status asc');
                    if($productId){
                        $select->where('ch.product_id =?',$productId);
                    }
                          
               } else { // on mobile basis
                    $select = $this->_db->select()
                    ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER . " as ch", array('ch.id','ch.product_id','ch.by_agent_id','ch.by_ops_id','concat(ch.first_name," ",ch.last_name) as name','ch.date_created','ch.date_approval','ch.crn','ch.email','ch.title','ch.first_name','ch.middle_name','ch.last_name','ch.mobile','ch.status','ch.customer_master_id', 'ch.rat_customer_id', 'ch.bank_id'))
                    ->joinLeft(DbTable::TABLE_RAT_MVC_CARDHOLDER_DETAILS." as chd", "ch.id = chd.cardholder_id AND chd.status = '".STATUS_ACTIVE."'", array('chd.cardholder_id','chd.date_of_birth', 'state', 'city', 'address_line1', 'address_line2', 'country', 'gender', 'mother_maiden_name', 'pincode', 'arn','customer_mvc_type','device_id','educational_qualifications','shmart_rewards','family_members','already_bank_account','vehicle_type','alternate_contact_number','products_acknowledgement','rewards_acknowledgement', 'status', 'res_type','chd.mobile_country_code','chd.mobile_number','chd.title as rat_title'))
                    ->joinLeft(DbTable::TABLE_RAT_CUSTOMER_PURSE." as chp", "ch.customer_master_id = chp.customer_master_id", array('chp.id as customer_purse_id','chp.purse_master_id'))                    
                    ->where('ch.mobile=?',$mobile)
                    ->where('ch.status =?',STATUS_ACTIVE);
                    if($productId){
                        $select->where('ch.product_id =?',$productId);
                    }
                  
               }
               return $this->_db->fetchRow($select);
              
          }
          return false;
     }
      /* addCardholderMVCDetails function will add cardholder details in t_cardholders_mvc if cardholder successfully paid and registred.
     * it will accept the certain params in $param array argument, e.g.. cardholder id, mvc type, mvc enroll status etc....
     */
     public function addCardholderMVCDetails($param){
      
        if($param['cardholder_id']<1 || $param['mvc_type']=='' || $param['mvc_enroll_status']==''){
            throw new Exception('Insufficient data found for adding cardholder MVC details');
        }
       
        $resp = $this->_db->insert(DbTable::TABLE_RAT_MVC_CARDHOLDER_STATUS, $param);    
       
        return true;    
     }
     public function updateCardholderDetail($param, $cardholderId, $chDetails =array()){
      
        try{
             $this->update($param,"id=".$cardholderId);
             if(!empty($chDetails)){
                $chDetails['cardholder_id'] = $cardholderId;
                $resp = $this->updateCardholder($chDetails);
             }
            
        }catch (Exception $e ) {
             App_Logger::log($e->getMessage(), Zend_Log::ERR);
             return $e->getMessage();
        }  
     }
     public function getCHDropDownProducts($cardholderId = 0,$productId = 0)
     {
        if($cardholderId != 0 && is_numeric($cardholderId) ) {
                   
             $select = $this->_db->select()
                    ->from(DbTable::TABLE_RAT_CORP_CARDHOLDER." as cp")   
                    ->joinLeft(DbTable::TABLE_PRODUCTS." as p", "cp.product_id = p.id", array('p.name'))                    
                    ->where('cp.id =?',$cardholderId)
                    ->where('cp.product_id =?',$productId)
                    ->where('cp.status =?',STATUS_ACTIVE);
            //echo $select; exit;
             $chProds = $this->_db->fetchAll($select);   
           
             $dataArray = array();
             foreach ($chProds as $id => $val) {
                   $dataArray[$val['product_id']] = $val['name'];
             }
            
             return $dataArray;
        }
        return false;
     }
   
    public function addCardHolderAPI($param){        
         
        $mobObj = new Mobile();  
        $emailObj = new Email();
        $custProductModel = new Corp_Ratnakar_CustomerProduct();
        $custPurseModel = new Corp_Ratnakar_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $objCustomerMaster = new CustomerMaster();
        $customerTrackModel = new CustomerTrack();
        $productModel = new Products();
        $objCustomers = new Customers();
        $validator = new Validator();
        $m = new \App\Messaging\Corp\Ratnakar\Operation();
        $ip = $this->formatIpAddress(Util::getIP());
        
        // mobile duplicacy check
        try {  
            $mobObj->checkRatCardholderMobileDuplicate($param['mobile_number'], $param['product_id']);                
        } catch (Exception $e ) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception ($e->getMessage());
        }   
     
        // email duplicacy check
        try {                
            $emailObj->checkRatCardholderEmailDuplicate($param['email'], $param['product_id']);                
        } catch (Exception $e ) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception ($e->getMessage());
        }   

        try {
            $productDetail = $productModel->getProductInfo($param['product_id']);
            $customerMasterId = $objCustomerMaster->generateCustomerId(array('bank_id' => $productDetail['bank_id']));
            $custMasterDetails = $objCustomerMaster->findById($customerMasterId);
            $custMasterDetail = Util::toArray($custMasterDetails);
            // adding data in rat_customer_master table

            $ratCustomerMasterData = array(
                'bank_id' => $productDetail['bank_id'],
                'customer_master_id' => $customerMasterId,
                'shmart_crn' => $custMasterDetail['shmart_crn'],
                'first_name' => $param['first_name'],
                'middle_name' => $param['middle_name'],
                'last_name' => $param['last_name'],
                'mobile' => $param['mobile_number'],
                'email' => $param['email'],
                'gender' => $param['gender'],
                'date_of_birth' => isset($param['date_of_birth']) ? $param['date_of_birth'] : '',
                'status' => STATUS_ACTIVE
            );

            $this->_db->insert(DbTable::TABLE_RAT_CUSTOMER_MASTER, $ratCustomerMasterData);
            $ratCustomerId = $this->_db->lastInsertId();

            $purseDetails = $masterPurseModel->getPurseDetailsbyBankIdProductId($param['product_id'], $productDetail['bank_id']);
            foreach ($purseDetails as $purseDetail) {
                $purseArr = array(
                    'customer_master_id' => $customerMasterId,
                    'rat_customer_id' => $ratCustomerId,
                    'product_id' => $param['product_id'],
                    'purse_master_id' => $purseDetail['id'],
                    'bank_id' => $productDetail['bank_id'],
                    'date_updated' => new Zend_Db_Expr('NOW()')
                );
                $purseParam = array('rat_customer_id' => $ratCustomerId, 'purse_master_id' => $purseDetail['id']);
                $purseDetails = $custPurseModel->getCustPurseDetails($purseParam);
                if (empty($purseDetails)) { // If purse entry not found
                    $custPurseModel->save($purseArr);
                }
            } 

            $data = array(
                'afn' => $param['arn'],
                'crn' => $custMasterDetail['shmart_crn'],
                'rat_customer_id' => $ratCustomerId,
                'customer_master_id' => $customerMasterId,
                'customer_type' => TYPE_NONKYC,
                'title' => $param['title'],
                'first_name' => $param['first_name'],
                'middle_name' => $param['middle_name'],
                'last_name' => $param['last_name'],
                'name_on_card' =>  $param['first_name']." ". $param['last_name'],
                'gender' => strtolower($param['gender']),
                'date_of_birth' => Util::returnDateFormatted($param['date_of_birth'], "d/m/Y", "Y-m-d", "/", "-"),
                'mobile' => $param['mobile_number'],
                'email' => $param['email'],
                'landline' => $param['landline'],
                'address_line1' => $param['address_line1'],
                'address_line2' => $param['address_line2'],
                'city' => $param['city'],
                'state' => $param['state'],
                'country' => $param['country'],
                'pincode' => $param['pincode'],
                'mother_maiden_name' => $param['mother_maiden_name'],
                'by_agent_id' => $param['reg_agent_id'],
                'bank_id' => $productDetail['bank_id'],
                'product_id' => $param['product_id'],
                'status_ops' => STATUS_APPROVED,
                'status_ecs' => STATUS_WAITING,
                'status' => STATUS_ECS_PENDING,
                'date_created' => new Zend_Db_Expr('NOW()'),
		'channel' => $param['channel'],
            );

            $this->_db->insert(DbTable::TABLE_RAT_CORP_CARDHOLDER, $data);
            $cardholderId = $this->_db->lastInsertId(DbTable::TABLE_RAT_CORP_CARDHOLDER, 'id');

            // Entry in Customer product table
            $prodArr = array('product_customer_id' => $cardholderId,
                'rat_customer_id' => $ratCustomerId,
                'product_id' => $param['product_id'],
                'program_type' => $productDetail['program_type'],
                'bank_id' => $productDetail['bank_id'],
                'by_agent_id' => $param['reg_agent_id'],
                'by_ops_id' => 0,
                'by_corporate_id' =>  (isset($param['corporate_id']) && $param['corporate_id'] > 0 )? $param['corporate_id'] : 0,
                'date_created' => new Zend_Db_Expr('NOW()'),
                'status' => STATUS_ACTIVE);
            $custProductModel->save($prodArr);

            $custDetailArr = array(
                    'product_id' => $param['product_id'],
                    'cardholder_id' => $cardholderId,
                    'mobile_country_code' => DEFAULT_MOBILE_COUNTRY_CODE,
                    'mobile_number' => $param['mobile_number'],
                    'arn' => $param['arn'],
                    'mother_maiden_name' => $param['mother_maiden_name'],
                    'landline' => $param['landline'],
                    'address_line1' => $param['address_line1'],
                    'address_line2' => $param['address_line2'],
                    'city' => $param['city'],
                    'state' => $param['state'],
                    'country' => $param['country'],
                    'pincode' => $param['pincode'],
                    'already_bank_account' => $param['already_bank_account'],
                    'vehicle_type' => $param['vehicle_type'],
                    'educational_qualifications' => $param['educational_qualifications'],
                    'family_members' => $param['family_members'],
                    'device_id' => $param['device_id'],
                    'customer_mvc_type' => $param['customer_mvc_type'],
                    'by_agent_id' => $param['reg_agent_id'],
                    'ip' => $ip,
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'status' => STATUS_ACTIVE
            );
            // adding cardholder details to rat_mvc_cardholder_details
            $this->_db->insert(DbTable::TABLE_RAT_MVC_CARDHOLDER_DETAILS, $custDetailArr); 

            $objCRN = new CRNMaster();
            $objCRN->assignRatCorpCRN($cardholderId); // assigning the crn to cardholder
            
            // ECS call
            $chInfo = $this->getCardHolderInfo($cardholderId);
            $paramArray = $validator->validCardholderData($chInfo);
            
            try {
                $ecsApi = new App_Api_ECS_Transactions();
                if(DEBUG_MVC) {
                    $resp = TRUE;
                } else {
                    $resp = $ecsApi->cardholderRegistration($paramArray); // bypassing for testing
                }
            } catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);
                $msg = $ecsApi->getError();
                $msg = empty($msg) ? $e->getMessage() : $msg ;
                $updateArr = array('status' => STATUS_ECS_FAILED, 'failed_reason' => $msg, 'date_failed' => new Zend_Db_Expr('NOW()'));
                $this->setError($msg);
                $this->update($updateArr, "id= $cardholderId");
                return FALSE;
            }

            //$debug_enable = App_DI_Container::get('ConfigObject')->system->enable_debug;
            if($resp == TRUE) {
                
                //************* MVC Registration ***********//                            
                $apisettingModel = new APISettings();
                $validator = new Validator();
                $array = $validator->validMvcCardholderData($chInfo);
                $enrollMVCStatus = STATUS_PENDING;        
                if($apisettingModel->checkAPIresponse()){
                    try {
                        $mvcApi = new App_Api_MVC_Transactions();
                        if(DEBUG_MVC) {
                            $flg = TRUE;
                        } else {
                            $flg = $mvcApi->Registration($array); // bypassing for testing
                        }
                        if($flg == true) {
                            $enrollMVCStatus = STATUS_SUCCESS;
                            
                            $mvcData = array(
                                'cardholder_id'=>$cardholderId,
                                'mvc_type'=>$chInfo['customer_mvc_type'],
                                'device_id'=>$chInfo['device_id'],
                                'mvc_enroll_status'=>$enrollMVCStatus,
                                'mvc_enroll_attempts'=>'0'
                             );

                            $this->addCardholderMVCDetails($mvcData);                                 

                            if(isset($cardholderId) && $cardholderId > 0) {

                                $updateArr = array('status_ecs' => STATUS_SUCCESS, 'status' => STATUS_ACTIVE, 'date_activation' => new Zend_Db_Expr('NOW()'), 'txn_code' => $param['txnCode']);  
                                $this->update($updateArr, "id= $cardholderId");

                                $customerTrackArr =  array(
                                            'crn' => $custMasterDetail['shmart_crn'],
                                            'mobile' => $param['mobile_number'],
                                            'email' => $param['email'],
                                            'bank_id' => $productDetail['bank_id']
                                         );
                                $customerTrackModel->customerDetailsAPI($customerTrackArr, $param['product_id'], $cardholderId, $productDetail['bank_id']);

                                // Add Customers record for global products  
                                $customersArr['mobile'] = $param['mobile_number'];
                                $customersArr['bank_id'] = $productDetail['bank_id'];
                                $customersArr['product_id'] = $param['product_id'];
                                $customersArr['bank_customer_id'] = $ratCustomerId;
                                $customersArr['customer_type'] = TYPE_NONKYC;

                                $checkCustomer = $objCustomers->checkCustomer($customersArr);

                                if($checkCustomer == FALSE){ 
                                    $custArr =  array(
                                        'bank_id' => $customersArr['bank_id'],
                                        'mobile_country_code' => DEFAULT_MOBILE_COUNTRY_CODE,
                                        'mobile' => $customersArr['mobile'],
                                        'customer_type' => $customersArr['customer_type'],
                                        'status' => STATUS_ACTIVE,
                                    ); 

                                    $newcustID = $objCustomers->addCustomers($custArr);
                                }else{
                                    $newcustID = $checkCustomer['id'];
                                }

                                if($newcustID > 0){
                                    $custDetailArr =  array(
                                        'bank_id' => $customersArr['bank_id'],
                                        'product_id' => $customersArr['product_id'],
                                        'bank_customer_id' => $customersArr['bank_customer_id'],
                                        'product_customer_id' => $cardholderId,
                                        'customer_id' => $newcustID,
                                    );  
                                    $objCustomers->addCustomerDetails($custDetailArr);
                                }
                                
                                $userData = array('last_four' =>substr($chInfo['crn'], -4),
                                        'product_name' => $productDetail['name'],
                                        'mobile' => $param['mobile_number'],
                                    );
                                $m->apiCardActivation($userData);
                            }
                        } else {
                            $updateArr = array('status' => STATUS_INACTIVE, 'failed_reason' => 'MVC Registration failed', 'date_failed' => new Zend_Db_Expr('NOW()'));
                            $this->update($updateArr, "id= $cardholderId");
                            return FALSE;
                        }
                    } catch (Exception $e) {
                        $msg = $e->getMessage();
                        if(isset($cardholderId) && $cardholderId > 0) {
                            $updateArr = array('status' => STATUS_ECS_FAILED, 'failed_reason' => $msg, 'date_failed' => new Zend_Db_Expr('NOW()'));
                            $this->setError($msg);
                            $this->update($updateArr, "id= $cardholderId");
                        }            
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                        throw new Exception($e->getMessage());
                    }
                } else {
                    App_Logger::log(SETTING_API_ERROR_MSG , Zend_Log::ERR);
                    $updateArr = array('status' => STATUS_INACTIVE, 'failed_reason' => 'API Settings error', 'date_failed' => new Zend_Db_Expr('NOW()'));
                    $this->update($updateArr, "id= $cardholderId");
                    return FALSE;
                }
                //********* MVC Registration Over **********//
                
            } else {
                $updateArr = array('status' => STATUS_ECS_FAILED, 'failed_reason' => $ecsApi->getError(),
                    'date_failed' => new Zend_Db_Expr('NOW()'));
                $this->update($updateArr, "id= $cardholderId");
                $this->setError($ecsApi->getError());
                $updateprodArr = array('status' => STATUS_INACTIVE);
                $this->_db->update(DbTable::TABLE_RAT_CUSTOMER_PRODUCT, $updateprodArr, "product_customer_id= $cardholderId");
                return FALSE;
            }
            return $cardholderId;
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);            
            $msg = $e->getMessage();
            if(isset($cardholderId) && $cardholderId > 0) {
                $updateArr = array('failed_reason' => $msg, 'date_failed' => new Zend_Db_Expr('NOW()'));
                $this->setError($msg);
                $this->update($updateArr, "id= $cardholderId");
            }
            throw new Exception($e->getMessage());
        }
        return TRUE;               
    }
}