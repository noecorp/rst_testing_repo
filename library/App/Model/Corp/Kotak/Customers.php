<?php

/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */
class Corp_Kotak_Customers extends Corp_Kotak {

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
    protected $_name = DbTable::TABLE_KOTAK_CORP_CARDHOLDER;

    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Privilege';

    /**
     * Define the relationship with another tables
     *
     * @var array
     */
    
    public function checkMemberId($memid){
         $select = $this->select()
                ->where("member_id =?",$memid);
         $res = $this->fetchRow($select);
        
        $res = Util::toArray($res);
                
        if(empty($res)){
           return TRUE; 
        }
        else
        {
            return FALSE;
        }
        
    }
    
     public function showPendingCustomerDetails($page = 1, $data = array(),$paginate = NULL, $force = FALSE) {
        $cityModel = new CityList();
        $state = isset($data['state']) ? $data['state'] : '';
        $pincode = isset($data['pincode']) ? $data['pincode'] : '';
        $dateCreated = isset($data['date_created']) ? $data['date_created'] : '';
        $productId = isset($data['product_id']) && $data['product_id'] > 0 ? $data['product_id'] : '';
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`kc`.`card_number`,'".$decryptionKey."') as card_number"); 
        $crn = new Zend_Db_Expr("AES_DECRYPT(`kc`.`crn`,'".$decryptionKey."') as crn");         
        $select = $this->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER ." as kc" , array(
            'id', 'product_id', 'kotak_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no','pan', 'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'nominee_name', 'nominee_relationship', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'date_toggle_kyc', 'status', 'status_bank', 'status_ops', 'status_ecs', 'aml_status'
        ));
        $select->where("kc.status_ops = '" . STATUS_PENDING . "'");
        
        if($productId != ''){
             $select->where("kc.product_id =?" , $productId);
        }
         if($state != ''){
        $state = $cityModel->getStateName($state);
        $select->where("kc.state = '" . $state . "'");
        }
        if($pincode != ''){
             $select->where("kc.pincode = '" . $pincode . "'");
        }
        if($dateCreated != ''){
             $dateCreated = Util::returnDateFormatted($dateCreated, "d-m-Y", "Y-m-d", "-","-",'from'); 
             $select->where("kc.date_created >= '" . $dateCreated . "'");
        }
        
        $select->order('kc.id ASC');  
        return $this->_paginate($select, $page, $paginate);
    }
    
    
     public function getpendingcardholders ($param, $page = 1, $paginate = NULL, $force = FALSE){
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number"); 
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");   
        $details = $this->_db->select();
        $details->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER, array('id', $crn,'customer_master_id', $card_number, 'afn', 'member_id', 'employee_id', 'concat(first_name," ",last_name) as name', 'gender', 'date_of_birth', 'mobile', 'email', 'employer_name', 'corporate_id', 'status', 'name_on_card', 'date_created' , 'date_approval' ,'date_failed', 'failed_reason'));
        $details->where("status_ops = '".STATUS_PENDING."'");
        $details->where('product_id = ?', $param['product_id']);
        
        if($param['en_date'] != '')
        {
            $details->where('DATE(date_created) = ?', $param['en_date']);
        }
        
        $details->order('date_created ASC');

        if($force){
           return $this->_paginate($details, $page, $paginate); 
        }else{
           return $this->_db->fetchAll($details);
        }
        
    
    }
    
    public function exportpendingcardholdersdetails($dataArr) {
        
        $data = $this->getpendingcardholders($dataArr);
        
        $retData = array();
        
        if (!empty($data)) {
            foreach ($data as $key => $data) {
                $retData[$key]['member_id'] = $data['member_id'];
                $retData[$key]['name'] = ucfirst($data['name']);
                $retData[$key]['card_number'] = Util::maskCard($data['card_number']);
                $retData[$key]['name_on_card'] = ucfirst($data['name_on_card']);
                $retData[$key]['date_of_birth'] = Util::returnDateFormatted($data['date_of_birth'], "Y-m-d", "d-m-Y", "-");
                $retData[$key]['mobile'] = $data['mobile'];
                $retData[$key]['email'] = $data['email'];
                $retData[$key]['employer_name'] = ucfirst($data['employer_name']);
                $retData[$key]['date_created'] = Util::returnDateFormatted($data['date_created'], "Y-m-d", "d-m-Y", "-");
                $retData[$key]['date_approval'] = Util::returnDateFormatted($item['date_approval'], "Y-m-d", "d-m-Y", "-");
                
            }
        }
        return $retData;
    }
 
    
     public function changeStatus($params) {
        $dataArr  = array('status_ops' => $params['status'],'date_approved' => new Zend_Db_Expr('NOW()'));
        $id = $params['id'];
        return $this->update($dataArr,"id = $id");
    }
    public function showBankPendingCustomerDetails($page = 1,$data, $paginate = NULL, $force = FALSE) {
        $productId = isset($data['product_id']) && $data['product_id'] > 0 ? $data['product_id'] : '';
        $cityModel = new CityList();
        $state = isset($data['state']) ? $data['state'] : '';
        
        $pincode = isset($data['pincode']) ? $data['pincode'] : '';
        $dateApproval = isset($data['date_approval']) ? $data['date_approval'] : '';
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number"); 
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");   
        
        $select = $this->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER, array(
            'id', 'product_id', 'kotak_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no', 'pan','mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'nominee_name', 'nominee_relationship', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'date_toggle_kyc', 'status', 'status_bank', 'status_ops', 'status_ecs', 'aml_status'
        ));
        $select->where("status_bank = '" . STATUS_PENDING . "'");
        $select->where("status_ops = '" . STATUS_APPROVED . "'");
        if($productId != ''){
             $select->where("product_id =?" , $productId);
        }

        if($state != ''){
        $state = $cityModel->getStateName($state);
        $select->where("state = '" . $state . "'");
        }
        if($pincode != ''){
             $select->where("pincode = '" . $pincode . "'");
        }
        if($dateApproval != ''){
             $dateApproval = Util::returnDateFormatted($dateApproval, "d-m-Y", "Y-m-d", "-","-",'from'); 
             $select->where("date_approval >= '" . $dateApproval . "'");
        }
        
        $select->order('id ASC');
        return $this->_paginate($select, $page, $paginate);
    }
 
     public function changeBankStatus($params) {
        if(isset($params['date_authorize']) && $params['date_authorize'] != ''){
            $dataArr  = array('status_bank' => $params['status'] ,'date_authorize' => $params['date_authorize'] );
        }
        else {
         $dataArr  = array('status_bank' => $params['status']);
       }
                
        $id = $params['id'];
        return $this->update($dataArr,"id = $id");
    }
    
    
    public function searchCustomer($param) {
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`kc`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`kc`.`crn`,'".$decryptionKey."') as crn");
        
        $productId = isset($param['product_id']) && $param['product_id'] > 0 ? $param['product_id'] : '';
        $status = isset($param['status']) ? $param['status'] : '';
        $columnName = $param['searchCriteria'];
        $keyword = $param['keyword']; 
        
        if($columnName == 'card_number'){
            $card_num = new Zend_Db_Expr("AES_DECRYPT(`kc`.`card_number`,'".$decryptionKey."')"); 
            $whereString = "$card_num LIKE '%$keyword%'";
        } else{
            $whereString = "kc.$columnName LIKE '%$keyword%'";
        }
        
        $details = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER .' as kc', array('kc.id', $crn,'kc.customer_master_id', $card_number, 'kc.afn', 'concat(kc.first_name," ",kc.last_name) as name', 'kc.gender', 'kc.date_of_birth', 'kc.mobile', 'kc.email','kc.member_id', 'kc.status', 'kc.name_on_card', 'kc.date_failed', 'kc.failed_reason','kc.status_ops','kc.status_bank','kc.status_ecs','kc.card_pack_id','kc.employee_id','kc.employer_name','kc.corporate_id','kc.status'))
                ->where($whereString);
       
        if($productId != ''){
             $details->where("kc.product_id =?" , $productId);
        }
        if($status != '')
        {
            $details->where("status = '".STATUS_ACTIVE."'");
        }
        
        $details->order('kc.first_name DESC');
        return $this->_db->fetchAll($details);
    }
    
    
    public function exportsearchCustomer($params){
         $data = $this->searchCustomer($params);
//         $data = $data->toArray();
         $retData = array();
        
        if(!empty($data))
        {
                     
            foreach($data as $key=>$data){
                    
                    $retData[$key]['member_id'] = $data['member_id'];
                    $retData[$key]['employee_id'] = $data['employee_id'];
                    $retData[$key]['card_number'] = Util::maskCard($data['card_number'], 4);
                    $retData[$key]['name'] = $data['name'];
                    $retData[$key]['name_on_card'] = $data['name_on_card']; 
                    $retData[$key]['gender'] = ucfirst($data['gender']);
                    $retData[$key]['date_of_birth'] = Util::returnDateFormatted($data['date_of_birth'], "Y-m-d", "d-m-Y", "-");
                    $retData[$key]['mobile'] = $data['mobile'];
                    $retData[$key]['email'] = $data['email'];
                    $retData[$key]['employer_name'] = $data['employer_name'];
                    $retData[$key]['corporate_id'] = $data['corporate_id']; 
                    $retData[$key]['status'] = ucfirst($data['status']); 
                    
          }
        }
        
        return $retData;
         
     }
    
    
    public function getApprovedCustomerForCRNUpdate($limit) {
        
        $kotakModel = new Corp_Kotak_Customers();
        $productList = $kotakModel->corpProductList($filter_products = 'kotak_gpr');
        
        
        
        $kotal_amul_arr = array(PRODUCT_CONST_KOTAK_AMULWB,PRODUCT_CONST_KOTAK_AMULGUJ);
        $kotal_gpr_arr = array(PRODUCT_CONST_KOTAK_SEMICLOSE_GPR,PRODUCT_CONST_KOTAK_OPENLOOP_GPR);
        
        $productModel = new Products();
        $productid_amul = $productModel->getProductIDbyConstArr($kotal_amul_arr); 
        $productid_gpr = $productModel->getProductIDbyConstArr($kotal_gpr_arr); 
        
        $details = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER, array('id', 'concat(first_name," ",last_name) as name', 'member_id', 'status', 'failed_reason', 'product_id'))
                ->where("status = '". STATUS_PENDING." ' OR status = '" .STATUS_ECS_PENDING. "'")
                ->where("status_bank = '" .  STATUS_APPROVED . "' AND product_id IN ($productid_amul) OR product_id IN ($productid_gpr)")
                ->where("status_ops = ?", STATUS_APPROVED)
                ->where("status_ecs = ?", STATUS_PENDING)
                ->order('date_created DESC');
        return $this->_db->fetchAll($details);
    }
    
    
    
    public function updateCRNforApprovedCustomer($limit = KOTAK_CRN_UPDATE_LIMIT) {
        $customerRs = $this->getApprovedCustomerForCRNUpdate($limit);
        $customerArr = Util::toArray($customerRs);
        if(!empty($customerArr)) {
            $cnt = 0;
            foreach ($customerArr as $customer) {
                if($this->validateCRNForMemberIdCardpackId($customer)) {
                    $flg = $this->updateCRNRecordForCustomer($customer);
                    if($flg) {
                        $cnt++;
                    }
                } 
            }
            return $cnt;
        }
        return FALSE;
    }
    
    
    private function validateCRNForMemberId($customer) {
        $rs = $this->getCRNByMemberId($customer);
        $rs = Util::toArray($rs);
        if(!empty($rs)) {
            return TRUE;
        }
        return FALSE;
    }
    
    private function getCRNByMemberId($customer) {
        $crnMaster = new CRNMaster();
        return  $crnMaster->getInfoByMemberId(array(
           'status' => STATUS_FREE,
           'member_id' => $customer['member_id'],
           'product_id' => $customer['product_id'],
        ));
    }
     private function validateCRNForMemberIdCardpackId($customer) {
        $rs = $this->getCRNByMemberIdCardpackId($customer);
        $rs = Util::toArray($rs);
        if(!empty($rs)) {
            return TRUE;
        }
        return FALSE;
    }

    private function getCRNByMemberIdCardpackId($customer) {
        $crnMaster = new CRNMaster();
        return  $crnMaster->getInfoByMemberIdCardpackId(array(
           'status' => STATUS_FREE,
           'member_id' => $customer['member_id'],
           'card_pack_id' => $customer['card_pack_id'],
           'product_id' => $customer['product_id'],
        ));
    }
    private function updateCRNRecordForCustomer($customer) {
        $crnMaster = new CRNMaster();
        $rs = $this->getCRNByMemberIdCardpackId($customer);
	
	if(($rs['card_number'] != 0) &($rs['card_number'] != '')){
	    $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	    $card_number = new Zend_Db_Expr("AES_ENCRYPT('".$rs['card_number']."','".$encryptionKey."')");
	} else{
	    $card_number = $rs['card_number'] ;
	}
        if(!empty($rs)) {
            $crnMaster->updateStatusByMemberIdCardpackId(array(
                'status' => STATUS_USED,
                'member_id' => $customer['member_id'],
                'product_id' => $customer['product_id'],
                'card_pack_id' => $customer['card_pack_id'],
            ));
            $updateArr  = array(
                'card_number'       =>  $card_number,
                'card_pack_id'      =>  $rs['card_pack_id'],
                'member_id'         =>  $rs['member_id'],
                'status_ecs'        =>  STATUS_WAITING,
                'date_crn_update'   =>  new Zend_Db_Expr('NOW()') 
                );
            $whereCon = ' id="'.$customer['id'].'"';
            return $this->update($updateArr,$whereCon);
        }
        return FALSE;
    }
    
   
    public function getProductId() {
        $product = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_AMUL);                
        $productModel = new Products();
        $productInfo = $productModel->getProductInfoByUnicode($product->product->unicode);
        if(empty($productInfo) || !isset($productInfo->id)) {
            throw new App_Exception('Unable to fetch Product Id');
        }
        return $productInfo->id;
    }

    public function kotakAmulCorpECSRegn() {
        $custPurseModel = new Corp_Kotak_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $deliveryFlag = new Corp_Kotak_DeliveryFlag();
        $productModel = new Products();
        $customerTrackModel = new CustomerTrack();
        $custProductModel = new Corp_Kotak_CustomerProduct();
        $m = new \App\Messaging\Corp\Kotak\Operation();
        $user = Zend_Auth::getInstance()->getIdentity();
        
        
        $dataArr = $this->ecsPendingKotakAmulArray();
        $numCust = 0;
        foreach ($dataArr as $data) {
            
            $id = $data['id'];
            $deliveryFileId = $data['delivery_file_id'];


            $msg = '';
            try {
                if($data['delivery_status'] == STATUS_DELIVERED)
                {
                    $cardholderArray = $this->getCardholderArray($data);
                    $ecsApi = new App_Api_ECS_Corp_Kotak();
                    $resp = $ecsApi->kotakAmulCardholderRegistration($cardholderArray);
                    if ($resp == false) {
                        $msg = $ecsApi->getError();
                        $deliveryArr = array('date_ecs' => new Zend_Db_Expr('NOW()'), 'status' => STATUS_FAILURE, 'failed_reason' => $msg);
                    }
                }

            } catch (App_Exception $e) {
                $resp = false;
                $msg = $e->getMessage();
                $deliveryArr = array('date_ecs' => new Zend_Db_Expr('NOW()'), 'status' => STATUS_FAILURE, 'failed_reason' => $msg);
                $deliveryFlag->update($deliveryArr, "id = $deliveryFileId");
            } catch (Exception $e) {
                $resp = false;
                $msg = $e->getMessage();
                $deliveryArr = array('date_ecs' => new Zend_Db_Expr('NOW()'), 'status' => STATUS_FAILURE, 'failed_reason' => $msg);
                $deliveryFlag->update($deliveryArr, "id = $deliveryFileId");
            }
            if ($resp == true) {
                //On Success
                $productDetail = $productModel->getProductInfo($data['product_id']);
                $a = new CustomerMaster();
                $customerMasterId = $a->generateCustomerId(array('bank_id' => $productDetail['bank_id']));
                $custMasterDetails = $a->findById($customerMasterId);
                $custMasterDetail = Util::toArray($custMasterDetails);
                // adding data in rat_customer_master table

                $ratCustomerMasterData = array(
                    'customer_master_id' => $customerMasterId,
                    'shmart_crn' => $custMasterDetail['shmart_crn'],
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'],
                    'last_name' => $data['last_name'],
                    'aadhaar_no' => (strtolower($data['id_proof_type']) == 'aadhar card') ? $data['id_proof_number'] : '',
                    'pan' => (strtolower($data['id_proof_type']) == 'pan card') ? $data['id_proof_number'] : '',
                    'mobile_country_code' => isset($data['mobile_country_code']) ? $data['mobile_country_code'] : '', 
                    'mobile' => $data['mobile'],
                    'email' => $data['email'],
                    'gender' => $data['gender'],
                    'date_of_birth' => isset($data['date_of_birth']) ? $data['date_of_birth'] : '',
                    'status' => STATUS_ACTIVE,
                );
                $ratCustomerId = $this->addKotakCustomerMaster($ratCustomerMasterData);
                //insert into customer purse
                $purseDetails = $masterPurseModel->getPurseDetailsbyBankIdProductId($data['product_id'], $productDetail['bank_id']);
                foreach ($purseDetails as $purseDetail) {
                    $purseArr = array(
                        'customer_master_id' => $customerMasterId,
                        'kotak_customer_id' => $ratCustomerId,
                        'product_id' => $data['product_id'],
                        'purse_master_id' => $purseDetail['id'],
                        'bank_id' => $productDetail['bank_id'],
                        'date_updated' => new Zend_Db_Expr('NOW()')
                    );
                    $purseParam = array('kotak_customer_id' => $ratCustomerId, 'purse_master_id' => $purseDetail['id']);
                    $purseDetails1 = $custPurseModel->getCustPurseDetails($purseParam);
                    if (empty($purseDetails1)) { // If purse entry not found
                        $custPurseModel->save($purseArr);
                    }
                }
                // Get Customer product details
                //Update customer product
                  $prodUpdateArr = array(
                        'kotak_customer_id' => $ratCustomerId,
                    );
                 $custProductModel->updateCustProduct($prodUpdateArr,"product_customer_id = $id");
                
                // update the status to STATUS_ACTIVE in cardholders
                $updateArr = array('status' => STATUS_ACTIVE, 'status_ecs' => STATUS_SUCCESS, 
                    'customer_master_id' => $customerMasterId, 
                    'kotak_customer_id' => $ratCustomerId, 
                    'date_activation' => new Zend_Db_Expr('NOW()'), 
                    'failed_reason' =>'', 'delivery_file_id' => $deliveryFileId);
                $this->_db->update(DbTable::TABLE_KOTAK_CORP_CARDHOLDER, $updateArr, "id= $id");
                
                $deliveryArr = array('date_ecs' => new Zend_Db_Expr('NOW()'), 'status' => STATUS_SUCCESS, 'failed_reason' => '');
                $deliveryFlag->update($deliveryArr, "id = $deliveryFileId");
                
                if(($data['card_number'] != 0) &($data['card_number'] != '')){
		    $crnKey = App_DI_Container::get('DbConfig')->crnkey;
		    $card_numberEnc = new Zend_Db_Expr("AES_ENCRYPT('".$data['card_number']."','".$crnKey."')");
		}else{
		    $card_numberEnc = $data['card_number'];
		}
		
                 // Insert into customer Track
               $customerTrackArr =  array(
                    'card_number' => $card_numberEnc,
                    'card_pack_id' => $data['card_pack_id'],
                    'member_id' => $data['member_id'],
                    'crn' => $card_numberEnc,
                    'mobile' => $data['mobile'],
                    'email' => $data['email'],
                    'name_on_card' => $data['name_on_card'],
                );
               $customerTrackModel->customerDetails($customerTrackArr, $data['product_id'], $data['id']);
                
                $userData = array('last_four' =>substr($data['card_number'], -4),
                    'product_name' => $productDetail['name'],
                    'mobile' => $data['mobile'],
                );
                $resp = $m->cardActivation($userData);
                
                
            } else {
                //On Failure
                $updateArr = array('status' => STATUS_ECS_FAILED,'status_ecs' => STATUS_FAILURE, 'failed_reason' => $msg,
                         'delivery_file_id' => $deliveryFileId , 'date_failed' => new Zend_Db_Expr('NOW()'));
                $this->update($updateArr, "id= $id");
               
                $updateprodArr = array('status' => STATUS_INACTIVE);
                $this->_db->update(DbTable::TABLE_KOTAK_CUSTOMER_PRODUCT, $updateprodArr, "product_customer_id= $id");
                
                $deliveryArr = array('date_ecs' => new Zend_Db_Expr('NOW()'), 'status' => STATUS_FAILURE, 'failed_reason' => $msg);
                $deliveryFlag->update($deliveryArr, "id = $deliveryFileId");
            }
            
            $numCust++;
        }
        $deliveryArr = array('status' => STATUS_FAILURE, 'failed_reason' => 'Unmatched Records');
        $deliveryFlag->update($deliveryArr, "status = '".STATUS_PENDING."'");
        
        return $numCust;
    }

    public function getCardholderArray($param) {
        $ECSModel = new ECS();
        $state = new CityList();
        $dob = Util::returnDateFormatted($param['date_of_birth'], "Y-m-d", "d-m-Y", "-");
        $cityCode = $state->getCityCode(ucfirst(strtolower($param['city'])));
        //if(!empty($param['unicode'])) {
            //$ECSModel->assignMediassistCRN($param['id']);
        //}

        $cardholderDetails = $this->findById($param['id']);
        $cardholder = Util::toArray($cardholderDetails);

        $paramArray['cardNumber'] = $cardholder['card_number'];
        $paramArray['address1'] = $param['address_line1'];
        $paramArray['address2'] = $param['address_line2'];
        $paramArray['address3'] = '';
        $paramArray['address4'] = '';
        $paramArray['bankcode'] = '';
        $paramArray['birthcity'] = '';
        $paramArray['birthcountry'] = '';
        $paramArray['birthdate'] = preg_replace('/-|:/', null, $dob);
        $paramArray['citycode'] = $cityCode;
        $paramArray['countrycode'] = COUNTRY_IN_CODE;
        $paramArray['emailid'] = $param['email'];
        $paramArray['embossedname'] = $param['name_on_card'];
        $paramArray['employer'] = '';
        $paramArray['employmentstatus'] = '';
        $paramArray['familyname'] = $param['last_name'];
        $paramArray['firstname'] = $param['first_name'];
        
        $paramArray['legalid'] = (isset($param['member_id']) && !empty($param['member_id'])) ? $param['member_id'] : '';
        $paramArray['mothersmaidenname'] = $param['mother_maiden_name'];
        $paramArray['phonemobile'] = $param['mobile'];
        $paramArray['zipcode'] = $param['pincode'];
        if(isset($param['gender']) && in_array(strtolower($param['gender']), array('male','female'))) {
            if(strtolower($param['gender']) == 'male') $param['gender'] = 'M';
            if(strtolower($param['gender']) == 'female') $param['gender'] = 'F';
        }
            
        $paramArray['gender'] = $param['gender'];

        return $paramArray;
    }
    
       /* addKotakCustomerMaster() will add the info in Kotak customer master
     */

    public function addKotakCustomerMaster($data) {
        if (empty($data))
            throw new Exception('Data missing while adding customer details');

        $this->_db->insert(DbTable::TABLE_KOTAK_CUSTOMER_MASTER, $data);
        return $this->_db->lastInsertId();
    }

    
    public function ecsPendingKotakAmulArray(){
         $productModel = new Products();
         $kotal_amul_arr = array(PRODUCT_CONST_KOTAK_AMULWB,PRODUCT_CONST_KOTAK_AMULGUJ);
         $productid_amul = $productModel->getProductIDbyConstArr($kotal_amul_arr); 
        
         //Decryption of Card Number and CRN
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`kc`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`kc`.`crn`,'".$decryptionKey."') as crn");
        
         $select = $this->_db->select();
         $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER .' as kc', array('id', 'product_id', 'kotak_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no', 'pan', 'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'nominee_name', 'nominee_relationship', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'date_toggle_kyc', 'status', 'status_bank', 'status_ops', 'status_ecs', 'aml_status'));
                $select->join(DbTable::TABLE_DELIVERY_FLAG_MASTER .' as df', 
                        "kc.card_number= df.card_number AND kc.card_pack_id= df.card_pack_id AND kc.member_id = df.member_id AND kc.product_id = df.product_id",
                        array('delivery_status', 'df.id as delivery_file_id'))
                ->where("kc.status_ecs = '" . STATUS_WAITING . "' OR kc.status_ecs = '" . STATUS_FAILURE . "'")
                ->where("kc.product_id IN(?)", $productid_amul)
                ->where("kc.status_ops =?", STATUS_APPROVED)
                ->where("kc.status_bank =?", STATUS_APPROVED)
                ->where("df.status =?", STATUS_PENDING)
                ->order('kc.id')
                ->limit(KOTAK_CORP_ECS_REGN_LIMIT);
          
        return $this->_db->fetchAll($select);
    }
    
         public function customerIdDoclist($id_proof){
        $retArr = array();
        $where = "d.id IN (".$id_proof.")";
        $select = $this->_db->select() 
                ->from(DbTable::TABLE_DOCS.' as d')
                ->where($where)
                ->where("d.status = '".STATUS_ACTIVE."' ");
        $retArr = $this->_db->fetchRow($select);
        return $retArr;
    } 
    
            
        public function customerAddDoclist($address_proof){
        $retArr = array();
        $where = "d.id IN (".$address_proof.")";
        $select = $this->_db->select() 
                ->from(DbTable::TABLE_DOCS.' as d')
                ->where($where)
                ->where("d.status = '".STATUS_ACTIVE."' ");
        $retArr = $this->_db->fetchRow($select);
        return $retArr;
    }
    
        public function customerProfileDoclist($profile){
        $retArr = array();
        $where = "d.id IN (".$profile.")";
        $select = $this->_db->select() 
                ->from(DbTable::TABLE_DOCS.' as d')
                ->where($where)
                ->where("d.status = '".STATUS_ACTIVE."' ");
        $retArr = $this->_db->fetchRow($select);
        return $retArr;
    }
    
    public function getCardholderPurses($customer_id=0, $page = 1) {
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(DbTable::TABLE_KOTAK_CUSTOMER_PURSE . " as cp", array('amount'))
                ->joinLeft(DbTable::TABLE_PURSE_MASTER . ' as p', "p.id = cp.purse_master_id", array('name', 'description'))
                ->where('p.id = cp.purse_master_id')
                ->where('cp.kotak_customer_id >0')
                ->where('cp.kotak_customer_id =?', $customer_id);
        $purse = $this->_paginate($select, $page, TRUE);
        
        return $purse;
    }
    public function getCardholderInfo($param) {
        $select = $this->getCardholderSearchSql($param);
        return $this->fetchRow($select);
    }
    
    /*
     *  getCardholderSearchSql function will return the sql for cardholder search
     *  as params:- medi assis id, employer name, card number, mobile, email,aadhaar no, pan
     *  any of above params can be accepted
     */

    public function getCardholderSearchSql($param) {
        $mediAssistId = isset($param['member_id']) ? $param['member_id'] : '';
        $employerName = isset($param['employer_name']) ? $param['employer_name'] : '';
        $cardNumber = isset($param['card_number']) ? $param['card_number'] : '';
        $mobile = isset($param['mobile']) ? $param['mobile'] : '';
        $employeeId = isset($param['employee_id']) ? $param['employee_id'] : '';
        $email = isset($param['email']) ? $param['email'] : '';
        $aadhaarNo = isset($param['aadhaar_no']) ? $param['aadhaar_no'] : '';
        $pan = isset($param['pan']) ? $param['pan'] : '';
        $cardholderId = isset($param['cardholder_id']) ? $param['cardholder_id'] : '';
        $status = isset($param['status']) ? $param['status'] : '';
        
        //Decryption of Card Number
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`rhc`.`card_number`,'".$decryptionKey."') as card_number");
        
        $select = $this->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER . " as rhc", 
                array('rhc.id as id','rhc.customer_master_id', $card_number,'rhc.kotak_customer_id',
                    'rhc.member_id', 'rhc.employee_id', 
                    'concat(rhc.first_name," ",rhc.last_name) as cardholder_name', 'rhc.gender', 
                    'rhc.mobile', 'rhc.email', 'rhc.employer_name', 'rhc.status as cardholder_status', 
                    'rhc.afn', 'rhc.mobile', 'rhc.first_name', 'rhc.middle_name', 'rhc.last_name', 
                    'rhc.date_of_birth', 'rhc.batch_name', 'rhc.corporate_id', 'rhc.product_id', 'rhc.city'));
        $select->setIntegrityCheck(false);
        //$select->where("rhc.status = '".STATUS_ACTIVE."'");
        if ($mediAssistId != ''){
            $select->where("rhc.member_id = '" . $mediAssistId . "'");
        }
        if ($employerName != ''){
            $select->where("rhc.employer_name like '%" . $employerName . "%'");
        }
        if ($cardNumber != ''){
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $cardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')");
            $select->where("rhc.card_number =?", $cardNumber);
        }
        if ($mobile != ''){
            $select->where("rhc.mobile = '" . $mobile . "'");
        }
        if ($employeeId != ''){
            $select->where("rhc.employee_id = '" . $employeeId . "'");
        }
        if ($email != ''){
            $select->where("rhc.email = '" . $email . "'");
        }
        if ($aadhaarNo != ''){
            $select->where("rhc.aadhaar_no = '" . $aadhaarNo . "'");
        }
        if ($pan != ''){
            $select->where("rhc.pan = '" . $pan . "'");
        }
        if ($cardholderId != ''){
            $select->where("rhc.id = '" . $cardholderId . "'");
        }
        if ($status != ''){
            $select->where("rhc.status = '" . $status . "'");
        }
        $select->order("cardholder_name");
                
        return $select;
    }
 
    
    
 
    public function showBankStatusDetails($page = 1, $params = array() ,$paginate = NULL, $force = FALSE) {
      
        $productId = isset($params['product_id']) && $params['product_id'] > 0 ? $params['product_id'] : '';
        $state = isset($params['state']) ? $params['state'] : '';
        $pincode = isset($params['pincode']) ? $params['pincode'] : '';
        $dateApproval = isset($params['date_approval']) ? $params['date_approval'] : '';
        $status = isset($params['status']) ? $params['status'] : '';
        
        //Decryption of Card Number and CRN
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`c`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`c`.`crn`,'".$decryptionKey."') as crn");
        
        $select = $this->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER .' as c',array('id', 'product_id', 'kotak_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no','pan', 'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'nominee_name', 'nominee_relationship', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'date_toggle_kyc', 'status', 'status_bank', 'status_ops', 'status_ecs', 'aml_status'));
        
        if($productId != ''){
             $select->where("c.product_id =?" , $productId);
        }
        
        if ($state != ''){
            $select->where("c.state = '" . $state . "'");
        }
        if ($pincode != ''){
            $select->where("c.pincode = '" . $pincode . "'");
        }
        if ($dateApproval != ''){
         $dateApproval = Util::returnDateFormatted($dateApproval, "d-m-Y", "Y-m-d", "-", "-", 'from');
         $select->where("c.date_approval >= '" . $dateApproval . "'");
        }
        
        if ($status != '') {
            switch ($status) {
                case STATUS_PENDING:
                    $qry = $select->where("c.status_ops = '" . STATUS_APPROVED . "' AND c.status_bank = '" . $status . "'");
                    break;
                case STATUS_APPROVED:
                    $qry = $select->where("c.status_bank = '" . $status . "'");
                    break;
                case STATUS_REJECTED:
                    $qry = $select->where("c.status_bank = '" . $status . "' AND c.status_ops = '" .  STATUS_APPROVED . "' ");
                    break;
                case STATUS_CARD_ISSUED:
                    $qry = $select->where("c.status_bank = '" . STATUS_APPROVED . "' AND c.card_number != ''");
                    break;
            }
        } else {
                    $qry = $select->where("c.status_ops = '" .  STATUS_APPROVED . "' ");
        }
        $select->order('id ASC');
        
        return $this->_paginate($select, $page, $paginate);
    }
    
     public function showOpsrejectedCustomerDetails($page = 1,$param = array(), $paginate = NULL, $force = FALSE) {
        $productId = isset($param['product_id']) && $param['product_id'] > 0 ? $param['product_id'] : '';
        $user = Zend_Auth::getInstance()->getIdentity();
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`c`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`c`.`crn`,'".$decryptionKey."') as crn");
        $select = $this->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER . ' as c', array(
            'id', 'product_id', 'kotak_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no', 'pan', 'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'nominee_name', 'nominee_relationship', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'date_toggle_kyc', 'status', 'status_bank', 'status_ops', 'status_ecs', 'aml_status'
        ));
        $select->where("c.status_ops = '" . STATUS_REJECTED . "'");
        $select->where("c.by_agent_id = '" . $user->id . "'");
        if($productId != ''){
             $select->where("c.product_id =?" , $productId);
        }

        $select->order('id ASC');
        return $this->_paginate($select, $page, $paginate);
    }
    
    
    public function generateAuthorizeFile(array $param) {
        if(!isset($param['start_date']) || !isset($param['end_date'])) {
            throw new App_Exception('Invalid Start or End Date');
            return;
        }
        $product = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_AMUL);
        $productModel = new Products();
        $productInfo = $productModel->getProductInfoByUnicode($product->product->unicode);
        $product_id = (isset($param['product_id']) && $param['product_id'] > 0) ? $param['product_id'] : $productInfo['id'];
        $prodInfo = $productModel->getProductInfo($product_id);
        if($prodInfo['const'] == PRODUCT_CONST_KOTAK_AMULWB) {
            $rel = RELATION_TYPE_KOTAK_AUTHORIZED_APPLICATION;
        } elseif($prodInfo['const'] == PRODUCT_CONST_KOTAK_AMULGUJ) {
            $rel = RELATION_TYPE_KOTAK_GUJ_AUTHORIZED_APPLICATION;
        }
        $objectReleation = new ObjectRelations();
        $flg = $objectReleation->dateCheckUsed($rel, $param['start_date'], $param['end_date']);
        if(!$flg) {
            throw new App_Exception('Start or End Date is already used');
            return;
        }
        $start_date = $param['start_date'] . ' 00:00:00';
        $end_date   = $param['end_date'] . ' 23:59:59';
        $select = $this->_db->select()
                ->from(DbTable::TABLE_BIND_OBJECT_RELATION . ' as obr',array())
                ->join(DBTable::TABLE_BIND_OBJECT_RELATION_TYPES . ' as obt', 'obr.object_relation_type_id = obt.id AND obt.label = "'.RELATION_TYPE_KOTAK_AUTHORIZED_APPLICATION.'"',array())
                ->joinRight(DBTable::TABLE_KOTAK_CORP_CARDHOLDER . ' as kcc', 'kcc.id = obr.from_object_id AND kcc.status_bank = "'.STATUS_APPROVED.'"')
                ->where('obr.id is NULL')
                //->where('kcc.card_number is NOT NULL')
                //->where('kcc.card_number <> ""')
                ->where('kcc.date_authorize >= ?',$start_date)
                ->where('kcc.date_authorize <= ?',$end_date)
                ->where('kcc.product_id = ?', $product_id)
                ->order('kcc.date_crn_update');
       $rs = $this->_db->fetchAll($select);
       if(empty($rs)) {
           throw new App_Exception('No Record Found');
           return;
       }
       $rs = Util::toArray($rs);
       return $this->generateAuthorizeApplicationFile($rs, $param);
    }

    
    private function generateAuthorizeApplicationFile(array $data, array $param) 
    {
       $objR = New ObjectRelations();
       $seprator = '|';
//       $ext = 'txt';
       $ext = 'dat';
       if(!empty($data)) {
                $product = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_AMUL);
                $productModel = new Products();
                $productInfo = $productModel->getProductInfoByUnicode($product->product->unicode);
                $product_id = (isset($param['product_id']) && $param['product_id'] > 0) ? $param['product_id'] : $productInfo['id'];
                $prodInfo = $productModel->getProductInfo($product_id);
                if($prodInfo['const'] == PRODUCT_CONST_KOTAK_AMULWB) {
                    $rel = RELATION_TYPE_KOTAK_AUTHORIZED_APPLICATION;
                    $label = KOTAK_AMUL_AUTH_FILE;
                    $programCode = PROGRAM_CODE_KOTAK_AMUL_WB;
                } elseif($prodInfo['const'] == PRODUCT_CONST_KOTAK_AMULGUJ) {
                    $rel = RELATION_TYPE_KOTAK_GUJ_AUTHORIZED_APPLICATION;
                    $label = KOTAK_AMULGUJ_AUTH_FILE;
                    $programCode = PROGRAM_CODE_KOTAK_AMUL_GUJ;
                }
               
           foreach ($data as $value) {
              
                $batchArr['action_code'] = 'I';
                $batchArr['card_number'] = $value['card_number']; //
                $batchArr['client_code'] = $value['card_pack_id']; //
                $batchArr['inititution_code'] = '419953';
                $batchArr['branch_code'] = '001';
                $batchArr['vip_flag'] = '0';
                $batchArr['owner_code'] = '1';
                $batchArr['staff_id'] = $value['member_id'];
                $batchArr['basic_card'] = '0';
                $batchArr['basic_card_number'] = ''; //
                $batchArr['title'] = ''; //
                $batchArr['family_name'] = $value['last_name'];
                $batchArr['first_name'] = $value['first_name'];
                $batchArr['middle_name'] = $value['middle_name'];
                $batchArr['middle_name2'] = '';
                $batchArr['embossed_name'] = $value['last_name'] . ' ' . $value['first_name'];
                $batchArr['encoded_name'] = '';
                $batchArr['emboss_line3'] = '';
                $batchArr['marital_status'] = '';
                $batchArr['gender'] = Util::getGenderChar($value['gender']);
                $batchArr['legal_id'] = $value['member_id'];
                $batchArr['nationality_code'] = '356';
                $batchArr['no_of_children'] = '';
                $batchArr['credit_limit'] = '';
                $batchArr['issuers_client'] = '';
                $batchArr['lodging_period'] = '';
                $batchArr['residence_status'] = '';
                $batchArr['net_yearly_income'] = '';
                $batchArr['no_of_dependents'] = '';
                $batchArr['birth_date'] = date('Ymd',strtotime($value['date_of_birth']));
                $batchArr['birth_city'] = '';
                $batchArr['birth_country'] = '';
                $batchArr['address1'] = $value['address_line1'];
                $batchArr['address2'] = $value['address_line2'];
                $batchArr['address3'] = $value['city'];
                $batchArr['zip_code'] = $value['pincode'];
                $batchArr['phone_no_1'] = '';
                $batchArr['phone_no_2'] = '';
                $batchArr['mobile_phone'] = $value['mobile'];
                $batchArr['email_id'] = '';
                $batchArr['mailing_address1'] = $value['address_line1'];
                $batchArr['mailing_address2'] = $value['address_line2'];
                $batchArr['mailing_address3'] = $value['city'];
                $batchArr['mailing_zip_code'] = $value['pincode'];
                $batchArr['phone_home'] = '';
                $batchArr['phone_alternate'] = '';
                $batchArr['phone_mobile'] = $value['mobile'];
                $batchArr['employment_status'] = '';
                $batchArr['employer'] = $value['employer_name'];
                $batchArr['empl_address1'] = $value['comm_address_line1'];
                $batchArr['empl_address2'] = $value['comm_address_line2'];
                $batchArr['empl_address3'] = $value['comm_city'];
                $batchArr['empl_zip_code'] = $value['comm_pin'];
                $batchArr['office_phone1'] = '';
                $batchArr['office_phone2'] = '';
                $batchArr['office_mobile'] = '';
                $batchArr['preferred_mailing_address'] = '';
                $batchArr['contract_start_date'] = '';
                $batchArr['opening_date'] = '';
                $batchArr['start_val_date'] = '';
                $batchArr['expiry_date'] = '';
                $batchArr['product_code'] = $programCode;//--Need to confirm
                $batchArr['promo_code'] = '';
                $batchArr['tariff_code'] = '';
                $batchArr['cash_fees_code'] = '';
                $batchArr['primay_card_transaction_set'] = '';
                $batchArr['secondary_card_transaction_set'] = '';
                $batchArr['statement_group_id'] = '';
                $batchArr['account1'] = '';
                $batchArr['account1_currency'] = '';
                $batchArr['account1_type'] = '';
                $batchArr['limit_cash_dom'] = '';
                $batchArr['limit_purch_dom'] = '';
                $batchArr['limit_te_dom'] = '';
                $batchArr['reserved'] = '';
                $batchArr['limit_cash_int'] = '';
                $batchArr['limit_purch_int'] = '';
                $batchArr['limit_te_int'] = '';
                $batchArr['reserved'] = '';
                $batchArr['autho_limit_dom'] = '';
                $batchArr['autho_limit_int'] = '';
                $batchArr['reserved'] = '';
                $batchArr['activity_code'] = '';
                $batchArr['socio_prof_code'] = '';
                $batchArr['status_code'] = '00';
                $batchArr['delivery_mode'] = '';
                $batchArr['delivery_flag'] = '1';
                $batchArr['delivery_date'] = date('Ymd');
                $batchArr['bank/_dsa_ref'] = '';
                $batchArr['photo_indicator'] = '0';
                $batchArr['picture_code'] = '';
                $batchArr['language_ind'] = '';
                $batchArr['maiden_name'] = '';
                $batchArr['renewal_option'] = '';
                $batchArr['preference'] = '';
                $batchArr['sale_date'] = '';
                $batchArr['registration_flag'] = '0';
                $batchArr['user_defined_field1'] = '';
                $batchArr['user_defined_field2'] = '';
                $batchArr['user_defined_field3'] = '';
                $batchArr['user_defined_field4'] = '';
                $batchArr['user_defined_field5'] = '';
                $batchArr['service_code'] = '';
                $batchArr['user_approved'] = '';
                $batchArr['beneficiary_family_name'] = '';
                $batchArr['beneficiary_first_name'] = '';
                $batchArr['beneficiary_middle_name1'] = '';
                $batchArr['beneficiary_middle_name2'] = '';
                $batchArr['beneficiary_address1'] = '';
                $batchArr['beneficiary_address2'] = '';
                $batchArr['beneficiary_address3'] = '';
                $batchArr['beneficiary_zip_code'] = '';
                $batchArr['beneficiary_telephone'] = '';
                $batchArr['legal_identification_type'] = '';
                $batchArr['register_with_load_agent'] = '';
                $batchArr['depositor_bank_id'] = '';
                $batchArr['user_defined_field6'] = '';
                $batchArr['user_defined_field7'] = '';
                $batchArr['user_defined_field8'] = '';
                $batchArr['user_defined_field9'] = '';
                $batchArr['card_type'] = 'B';
                $batchArr['card_classification'] = 'P';
                $batchArr['filler'] = '                                                                                                                                                                                                          ';
                $batchArr['checksum'] = '';
                //$batchMainArr[] = $batchArr['action_code'] . '|' . $batchArr['card_number'] . '|' . $batchArr['client_code'] . '|' . $batchArr['institution_code'] . '|' . $batchArr['branch_code'] . '|' . $batchArr['vip_flag'] . '|' . $batchArr['owner_code'] . '|' . $batchArr['staff_id'] . '|' . $batchArr['basic_card'] . '|' . $batchArr['basic_card_number'] . '|' . $batchArr['title'] . '|' . $batchArr['family_name'] . '|' . $batchArr['first_name'] . '|' . $batchArr['middle_name'] . '|' . $batchArr['middle_name2'] . '|' . $batchArr['embossed_name'] . '|' . $batchArr['encoded_name'] . '|' . $batchArr['emboss_line3'] . '|' . $batchArr['marital_status'] . '|' . $batchArr['gender'] . '|' . $batchArr['legal_id'] . '|' . $batchArr['nationality_code'] . '|' . $batchArr['no_of_children'] . '|' . $batchArr['credit_limit'] . '|' . $batchArr['issuers_client'] . '|' . $batchArr['lodging_period'] . '|' . $batchArr['residence_status'] . '|' . $batchArr['net_yearly_income'] . '|' . $batchArr['no_of_dependents'] . '|' . $batchArr['birth_date'] . '|' . $batchArr['birth_city'] . '|' . $batchArr['birth_country'] . '|' . $batchArr['address1'] . '|' . $batchArr['address2'] . '|' . $batchArr['address3'] . '|' . $batchArr['zip_code'] . '|' . $batchArr['phone_no_1'] . '|' . $batchArr['phone_no_2'] . '|' . $batchArr['mobile_phone'] . '|' . $batchArr['email_id'] . '|' . $batchArr['mailing_address1'] . '|' . $batchArr['mailing_address2'] . '|' . $batchArr['mailing_address3'] . '|' . $batchArr['mailing_zip_code'] . '|' . $batchArr['phone_home'] . '|' . $batchArr['phone_alternate'] . '|' . $batchArr['phone_mobile'] . '|' . $batchArr['employment_status'] . '|' . $batchArr['employer'] . '|' . $batchArr['empl_address1'] . '|' . $batchArr['empl_address2'] . '|' . $batchArr['empl_address3'] . '|' . $batchArr['empl_zip_code'] . '|' . $batchArr['office_phone1'] . '|' . $batchArr['office_phone2'] . '|' . $batchArr['office_mobile'] . '|' . $batchArr['preferred_mailing_address'] . '|' . $batchArr['contract_start_date'] . '|' . $batchArr['opening_date'] . '|' . $batchArr['start_val_date'] . '|' . $batchArr['expiry_date'] . '|' . $batchArr['product_code'] . '|' . $batchArr['promo_code'] . '|' . $batchArr['tariff_code'] . '|' . $batchArr['cash_fees_code'] . '|' . $batchArr['primay_card_transaction_set'] . '|' . $batchArr['secondary_card_transaction_set'] . '|' . $batchArr['statement_group_id'] . '|' . $batchArr['account1'] . '|' . $batchArr['account1_currency'] . '|' . $batchArr['account1_type'] . '|' . $batchArr['limit_cash_dom'] . '|' . $batchArr['limit_purch_dom'] . '|' . $batchArr['limit_te_dom'] . '|' . $batchArr['reserved'] . '|' . $batchArr['limit_cash_int'] . '|' . $batchArr['limit_purch_int'] . '|' . $batchArr['limit_te_int'] . '|' . $batchArr['reserved'] . '|' . $batchArr['autho_limit_dom'] . '|' . $batchArr['autho_limit_int'] . '|' . $batchArr['reserved'] . '|' . $batchArr['activity_code'] . '|' . $batchArr['socio_prof_code'] . '|' . $batchArr['status_code'] . '|' . $batchArr['delivery_mode'] . '|' . $batchArr['delivery_flag'] . '|' . $batchArr['delivery_date'] . '|' . $batchArr['bank/_dsa_ref'] . '|' . $batchArr['photo_indicator'] . '|' . $batchArr['picture_code'] . '|' . $batchArr['language_ind'] . '|' . $batchArr['maiden_name'] . '|' . $batchArr['renewal_option'] . '|' . $batchArr['preference'] . '|' . $batchArr['sale_date'] . '|' . $batchArr['registration_flag'] . '|' . $batchArr['user_defined_field1'] . '|' . $batchArr['user_defined_field2'] . '|' . $batchArr['user_defined_field3'] . '|' . $batchArr['user_defined_field4'] . '|' . $batchArr['user_defined_field5'] . '|' . $batchArr['service_code'] . '|' . $batchArr['user_approved'] . '|' . $batchArr['beneficiary_family_name'] . '|' . $batchArr['beneficiary_first_name'] . '|' . $batchArr['beneficiary_middle_name1'] . '|' . $batchArr['beneficiary_middle_name2'] . '|' . $batchArr['beneficiary_address1'] . '|' . $batchArr['beneficiary_address2'] . '|' . $batchArr['beneficiary_address3'] . '|' . $batchArr['beneficiary_zip_code'] . '|' . $batchArr['beneficiary_telephone'] . '|' . $batchArr['legal_identification_type'] . '|' . $batchArr['register_with_load_agent'] . '|' . $batchArr['depositor_bank_id'] . '|' . $batchArr['user_defined_field6'] . '|' . $batchArr['user_defined_field7'] . '|' . $batchArr['user_defined_field8'] . '|' . $batchArr['user_defined_field9'] . '|' . $batchArr['card_type'] . '|' . $batchArr['card_classification'] . '|' . $batchArr['filler'] .'|';
                $batchMainArr[] = $batchArr;
                unset($batchArr);
                
                $objR->insertWithLabel(array(
                    'from_object_id'    => $value['id'],
                    'to_object_id'    => '0',
                    'date_start'    => $param['start_date'],
                    'date_end'    => $param['end_date'],
                ), $rel);
            }
            $file_name = KOTAK_EMBOSSING_FILE_NAME.date('mdyhis');                            
            $file = new Files();
            $id = $file->insert(array(
               'label'  => $label,
               'file_name'  => $file_name,
               'date_start'  => $param['start_date'],
               'date_end'  => $param['end_date'],
               'status'  => STATUS_ACTIVE,
               'comments'  => '',
               'date_created'  => new Zend_Db_Expr('NOW()')
            ));
            if($id > 0) {
                $file_name = KOTAK_EMBOSSING_FILE_NAME.date('mdyhms').'_'.$id.'.'.$ext;                            
                $file->update(array('file_name' => $file_name ), "id=$id");
            }

            $file->setBatch($batchMainArr, $seprator);
            $file->setFilepath(APPLICATION_UPLOAD_PATH);
            $file->setFilename($file_name);
            $file->generate(TRUE);
        }
        return TRUE;
    }
    
       public function acceptPhysicalDocList($page = 1,$data, $paginate = NULL, $force = FALSE) {
        $cityModel = new CityList();
        $state = isset($data['state']) ? $data['state'] : '';
        $productId = isset($data['product_id']) && $data['product_id'] > 0 ? $data['product_id'] : '';
        $pincode = isset($data['pincode']) ? $data['pincode'] : '';
        $dateAuthorize = isset($data['date_authorize']) ? $data['date_authorize'] : '';
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
        
        $select = $this->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER, array(
            'id', 'product_id', 'kotak_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no', 'pan', 'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'nominee_name', 'nominee_relationship', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'date_toggle_kyc', 'status', 'status_bank', 'status_ops', 'status_ecs', 'aml_status'
        ));
        $select->where("status_bank = '" . STATUS_APPROVED . "'");
        $select->where("recd_doc = '" . FLAG_NO . "'");
        if($state != ''){
        $state = $cityModel->getStateName($state);
        $select->where("state = '" . $state . "'");
        }
        if($pincode != ''){
             $select->where("pincode = '" . $pincode . "'");
        }
         if($productId != ''){
             $select->where("product_id =?" , $productId);
        }

        if($dateAuthorize != ''){
             $dateAuthorize = Util::returnDateFormatted($dateAuthorize, "d-m-Y", "Y-m-d", "-","-",'from'); 
             $select->where("date_authorize >= '" . $dateAuthorize . "'");
        }
        
        $select->order('id ASC');
        return $this->_paginate($select, $page, $paginate);
    }
    
    public function updatePhysicalDoc($idArr, $date) {
       
        $user = Zend_Auth::getInstance()->getIdentity();
        if (empty($idArr))
            throw new Exception('Data missing for processing');
       $date = Util::returnDateFormatted($date, "d-m-Y", "Y-m-d", "-", "-", '');
        try {
            // Foreach selected id value
            foreach ($idArr as $id) {
                $params = array('recd_doc' => FLAG_YES,'date_recd_doc' => $date,'recd_doc_id' => $user->id);
           
                $this->update($params,"id = $id");
            }// END of foreach loop

        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception($e->getMessage());
        }
        return TRUE;
    }
    
     public function showApplicationDetails($data) {
        $status = isset($data['bank_status']) ? $data['bank_status'] : '';
        $productId = isset($data['product_id']) && $data['product_id'] > 0 ? $data['product_id'] : '';
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`c`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`c`.`crn`,'".$decryptionKey."') as crn");
        
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER . ' as c',array(
            'id', 'product_id', 'kotak_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no','pan', 'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'nominee_name', 'nominee_relationship', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'date_toggle_kyc', 'status', 'status_bank', 'status_ops', 'status_ecs', 'aml_status'
        ));
         if($productId != ''){
          $select->where("c.product_id =?" , $productId);
        }
       
        if($status == 'all'){
        $select->where("c.status_bank = '" . STATUS_APPROVED. "' OR c.status_bank = '" . STATUS_REJECTED. "' OR c.status_bank = '" . STATUS_PENDING. "' ");
        }
          else{
        $select->where("c.status_bank = '" . $status . "'");
        }
         $select->where("(date_authorize BETWEEN '" . $data['from'] . "' AND '" . $data['to'] . "') OR (date_created BETWEEN '" . $data['from'] . "' AND '" . $data['to'] . "')");
         
         $select->order('id ASC');
         
        return $this->_db->fetchAll($select);
    }
    
    public function applicationApprovedCount($data){
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER . ' as c',array('count(id) as count'));
        $select->where("c.status_bank = '" . STATUS_APPROVED . "'");
        $select->where("date_authorize >= '" . $data['from'] . "'");
        $select->where("date_authorize <= '" . $data['to'] . "'");
        return $this->_db->fetchRow($select);
    }
    
     public function physicalAppAcceptedCount($data){
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER . ' as c',array('count(id) as count'));
//        $select->where("c.status_bank = '" . STATUS_APPROVED . "'");
        $select->where("c.recd_doc = '" . FLAG_YES . "'");
        $select->where("date_recd_doc >= '" . $data['from'] . "'");
        $select->where("date_recd_doc <= '" . $data['to'] . "'");
        return $this->_db->fetchRow($select);
    }
    
     public function applicationPendingCount(){
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER . ' as c',array('count(id) as count'));
        $select->where("c.status_ops = '" . STATUS_APPROVED . "'");
        $select->where("c.status_bank = '" . STATUS_PENDING . "'");
        return $this->_db->fetchRow($select);  
    }
    
     public function pendingPhysical(){
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER . ' as c',array('count(id) as count'));
        $select->where("c.status_bank = '" . STATUS_APPROVED . "'");
        $select->where("c.recd_doc = '" . FLAG_NO . "'");
        return $this->_db->fetchRow($select);
    }
    
      public function getCardholders($param){
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $product = isset($param['product_id']) && $param['product_id'] > 0 ? $param['product_id'] : '';
        $employer_name = isset($param['employer_name']) ? $param['employer_name'] : '';
        $employer_loc = isset($param['employer_loc']) ? $param['employer_loc'] : '';
        $status = isset($param['status']) ? $param['status'] : '';
       
        $byCorporateId = isset($param['by_corporate_id']) && $param['by_corporate_id'] > 0 ? $param['by_corporate_id'] : '';
        
        //Decryption of Card Number
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`c`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`c`.`crn`,'".$decryptionKey."') as crn");
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER . ' as c',array('id', 'product_id', 'kotak_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no','pan', 'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'nominee_name', 'nominee_relationship', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'date_toggle_kyc', 'status', 'status_bank', 'status_ops', 'status_ecs', 'aml_status','concat(c.first_name," ",c.last_name) as cardholder_name'));
        $select->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "c.product_id  = p.id",array('p.name as product_name'));
        if ($product != '')
            $select->where("c.product_id = '" . $product . "'");
        if ($byCorporateId != '')
            $select->where("c.by_corporate_id = '" . $byCorporateId . "'");
        if ($employer_name != '') {
            $select->where("c.employer_name LIKE  '%". $employer_name. "%'");
        }
        if ($employer_loc != '') {
            $select->where("c.comm_city LIKE  '%". $employer_loc. "%'");
        }   
        if ($from != '' && $to != ''){
            $select->where("c.date_created >= '" . $param['from'] . "'");
            $select->where("c.date_created <= '" . $param['to'] . "'");
        }
        if ($status != '') {
           $select->where('c.status = ?', $status);
        }else{
            $select->where("c.status =  '" . STATUS_ACTIVE . "'");
        }
        $select->order("c.first_name");
                
        return $this->fetchAll($select);  
    }
    
    /* exportAgentFundRequests function will find data for Agent fund requests report. 
    * it will accept param array with query filters e.g.. duration
    */
    public function exportgetCardholders($param){ 
        
        $data = $this->getCardholders($param);
                
        $retData = array();
        
        if(!empty($data))
        {
            foreach($data as $key=>$data){
    
                $retData[$key]['product_name']          = $data['product_name'];
                $retData[$key]['member_id']          = $data['member_id'];
                $retData[$key]['employee_id']          = $data['employee_id'];
                $retData[$key]['card_number']          = Util::maskCard($data['card_number']);
                $retData[$key]['first_name']          = $data['first_name'];
                $retData[$key]['middle_name']          = $data['last_name'];
                $retData[$key]['last_name']          = $data['last_name'];
                $retData[$key]['name_on_card']          = $data['name_on_card'];
                $retData[$key]['gender']          = ucfirst($data['gender']);
                $retData[$key]['date_of_birth']          = $data['date_of_birth'];
                $retData[$key]['mobile']          = $data['mobile'];
                $retData[$key]['email']          = $data['email'];
                $retData[$key]['landline']          = $data['landline'];
                $retData[$key]['address_line1']          = $data['address_line1'];
                $retData[$key]['address_line2']          = $data['address_line2'];
                $retData[$key]['city']          = ucfirst($data['city']);
                $retData[$key]['pincode']          = $data['pincode'];
                $retData[$key]['mother_maiden_name']          = $data['mother_maiden_name'];
                $retData[$key]['employer_name']          = $data['employer_name'];
                $retData[$key]['corporate_id']          = $data['corporate_id'];
                $retData[$key]['comm_address_line1']          = $data['comm_address_line1'];
                $retData[$key]['comm_address_line2']          = $data['comm_address_line2'];
                $retData[$key]['comm_city']          = ucfirst($data['comm_city']);
                $retData[$key]['comm_pin']          = $data['comm_pin'];
                $retData[$key]['date_created']          = $data['date_created'];
                $retData[$key]['status']          = ucfirst($data['status']);
                $retData[$key]['date_failed']          = $data['date_failed'];
                $retData[$key]['failed_reason']          = $data['failed_reason'];
   
          }
        }
        
        return $retData;
    }
    
      public function getCustomerInfo($custId){
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
        
        $select = $this->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER, array(
            'id', 'product_id', 'kotak_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no','pan' ,'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'nominee_name', 'nominee_relationship', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'date_toggle_kyc', 'status', 'status_bank', 'status_ops', 'status_ecs', 'aml_status'
        ));
        $select->where("id = ?",$custId);
        $select->where("status = '".STATUS_ACTIVE."'");
        $rs = $this->fetchRow($select);  
        if(!empty($rs)) {
            return $rs;
        }
        return FALSE;
     }
     
     public function getActiveCustomers(){
         
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
        
        $select = $this->_db->select();
        $select->from($this->_name .' as kc', array(
            'id', 'product_id', 'kotak_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no','pan' ,'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'nominee_name', 'nominee_relationship', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'date_toggle_kyc', 'status', 'status_bank', 'status_ops', 'status_ecs', 'aml_status'
        ))
                ->where("kc.status_ecs = '". STATUS_SUCCESS."'")
                ->order('kc.id');
        return $this->_db->fetchAll($select);
    }
    
    public function corpProductList($filter_products = FALSE){
            $bank = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
            $bankUnicode = $bank->bank->unicode;
            $objBank = new Banks();
            $bankInfo = $objBank->getBankbyUnicode($bankUnicode);
            $programType = PROGRAM_TYPE_CORP;
            $objProd = new Products();
            
            if($filter_products == 'kotak_gpr'){
                return $objProd->getProductList($bankInfo->id,$programType,$filter_products == 'kotak_gpr');
            }else{
                return $objProd->getProductList($bankInfo->id,$programType);
            }
        
    }
    
     public function addCustomer($dataArr = array(),$status = STATUS_ECS_PENDING) {
        if (empty($dataArr)) {
            throw new Exception('Data missing for add cardholder');
        }
        elseif (empty($dataArr['ProductId'])) {
            throw new Exception('Product missing for add cardholder');
        }
        $user = Zend_Auth::getInstance()->getIdentity();
        $custProductModel = new Corp_Kotak_CustomerProduct();
        $custPurseModel = new Corp_Kotak_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $objCustomerMaster = new CustomerMaster();
        $customerTrackModel = new CustomerTrack();
        $bankModel = new Banks();
        $productModel = new Products();
        $crnMaster = new CRNMaster();
        $m = new \App\Messaging\Corp\Kotak\Operation();
        $str = '';
        try {
//            $this->_db->beginTransaction();
            
            $paramChk = array(
                'mobile' => $dataArr['Mobile'],
                'member_id' => $dataArr['MemberId'],
                'card_number' => $dataArr['CardNumber'],
                'product_id' => $dataArr['ProductId']
            );
            $productDetails = $productModel->findById($dataArr['ProductId']);
            
            $statusOps = ($productDetails->const == KOTAK_SEMICLOSE_GPR) ? STATUS_APPROVED : STATUS_PENDING;
            
            $check = $this->checkCardholderDuplication($paramChk);
            $checkCard = $this->checkDuplicateCardhNumber(array('card_number' => $dataArr['CardNumber']));
            if (!$check) {
                $this->setError("Duplicate Record");
                return FALSE;
             }elseif(!$checkCard){
                  $status = STATUS_FAILED;
                  $failed_reason = 'Duplicate card number';
                  return FALSE;
            }
            else
            {
               
                $productDetail = $productModel->getProductInfo($dataArr['ProductId']);
                $customerMasterId = $objCustomerMaster->generateCustomerId(array('bank_id' => $productDetail['bank_id']));
                $custMasterDetails = $objCustomerMaster->findById($customerMasterId);
                $custMasterDetail = Util::toArray($custMasterDetails);
                // adding data in rat_customer_master table
                
                $ratCustomerMasterData = array(
                    'customer_master_id' => $customerMasterId,
                    'shmart_crn' => $custMasterDetail['shmart_crn'],
                    'first_name' => $dataArr['FirstName'],
                    'middle_name' => $dataArr['MiddleName'],
                    'last_name' => $dataArr['LastName'],
                    'aadhaar_no' => (isset($dataArr['aadhaar_no']))? $dataArr['aadhaar_no'] : '',
                    'pan' => (isset($dataArr['pan'])) ? $dataArr['pan'] : '',
                    'mobile_country_code' => isset($dataArr['mobile_country_code']) ? $dataArr['mobile_country_code'] : '',
                    'mobile' => $dataArr['Mobile'],
                    'email' => $dataArr['Email'],
                    'gender' => $dataArr['Gender'],
                    'date_of_birth' => isset($dataArr['DateOfBirth']) ? $dataArr['DateOfBirth'] : '',
                    'status' => STATUS_ACTIVE,
                );
                
                $ratCustomerId = $this->addKotakCustomerMaster($ratCustomerMasterData);
                
                $purseDetails = $masterPurseModel->getPurseDetailsbyBankIdProductId($dataArr['ProductId'], $productDetail['bank_id']);
                foreach ($purseDetails as $purseDetail) {
                    $purseArr = array(
                        'customer_master_id' => $customerMasterId,
                        'kotak_customer_id' => $ratCustomerId,
                        'product_id' => $dataArr['ProductId'],
                        'purse_master_id' => $purseDetail['id'],
                        'bank_id' => $productDetail['bank_id'],
                        //'by_ops_id' => $user->id,
                        'date_updated' => new Zend_Db_Expr('NOW()')
                    );
                    $purseParam = array('kotak_customer_id' => $ratCustomerId, 'purse_master_id' => $purseDetail['id']);
                    $purseDetails = $custPurseModel->getCustPurseDetails($purseParam);
                    if (empty($purseDetails)) { // If purse entry not found
                        $custPurseModel->save($purseArr);
                    }
                }
                /*
                 * Encryption of CRN and Card Number
                 */
                    $cardNumber = isset($dataArr['CardNumber'])?trim($dataArr['CardNumber']):'';
                    if($cardNumber!=''){
                    $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                        $dataArr['CardNumber'] = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')");
                    }
                /*
                 * 
                 */    
                    $dataCardholder = array(
                    'product_id' => $dataArr['ProductId'],
                    'kotak_customer_id' => $ratCustomerId,
                    'customer_master_id' => $customerMasterId,
                    'customer_type' => $dataArr['customer_type'],
                    'afn' => $dataArr['afn'],
                    'crn' => $dataArr['CardNumber'],
                    'card_number' => $dataArr['CardNumber'],
                    'card_pack_id' => $dataArr['CardPackId'],
                    'member_id' => $dataArr['MemberId'],
                    'first_name' => $dataArr['FirstName'],
                    'middle_name' => $dataArr['MiddleName'],
                    'last_name' => $dataArr['LastName'],
                    'gender' => $dataArr['Gender'],
                    'date_of_birth' =>$dataArr['DateOfBirth'],
                    'mobile' => $dataArr['Mobile'],
                    'email' => $dataArr['Email'],
                    'pan' => $dataArr['pan'],
                    'aadhaar_no' => $dataArr['aadhaar_no'],
                    'employer_name' => $dataArr['employer_name'],
                    'employee_id' => $dataArr['employee_id'],
                    'by_agent_id' => 0,
                    'by_ops_id' => 0,
                    'batch_id' => 0,
                    'batch_name' => '',
                    'name_on_card' => $dataArr['name_on_card'],
                    'mother_maiden_name' => $dataArr['mother_maiden_name'],
                    'address_line1' => $dataArr['address_line1'],
                    'address_line2' => $dataArr['address_line2'],
                    'state' => $dataArr['state'],
                    'city' => $dataArr['city'],
                    'pincode' => $dataArr['pincode'],
                    'corporate_id'=>$dataArr['corporate_id'],
                    'comm_address_line1' => $dataArr['comm_address_line1'],
                    'comm_address_line2' => $dataArr['comm_address_line2'],
                    'comm_state' => $dataArr['comm_state'],
                    'comm_city' => $dataArr['comm_city'],
                    'comm_pin' => $dataArr['comm_pin'],
                    'id_proof_type' => $dataArr['id_proof_type'],
                    'id_proof_number' => $dataArr['id_proof_number'],
                    'address_proof_type' => $dataArr['address_proof_type'],
                    'address_proof_number' => $dataArr['address_proof_number'],
                    'status_bank' => STATUS_PENDING,
                    'status_ecs' => STATUS_WAITING,
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'status_ops' => $statusOps,
                    'status' => $status,
                );
                 if(!empty($user->corporate_code)){
                    $dataCardholder['by_ops_id']= 0;
                    $dataCardholder['by_corporate_id']= $user->id;
                 }
             
                $this->insert($dataCardholder);
                $cardholderId = $this->_db->lastInsertId(DbTable::TABLE_KOTAK_CORP_CARDHOLDER, 'id');

                // Entry in Customer product table
                $prodArr = array(
                    'product_customer_id' => $cardholderId,
                    'kotak_customer_id' => $ratCustomerId,
                    'product_id' => $dataArr['ProductId'],
                    'program_type' => $productDetail['program_type'],
                    'bank_id' => $productDetail['bank_id'],
                    'by_agent_id' => $dataArr['by_api_user_id'],
                    'by_ops_id' => 0,
                    'by_corporate_id' => $dataArr['by_corporate_id'],
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'status' => STATUS_ACTIVE);
                $custProductModel->save($prodArr);
                
                
            } 
                    
            $this->_db->commit();
                
        } catch (Exception $e) {
           App_Logger::log($e->getMessage(), Zend_Log::ERR);            
            $msg = $e->getMessage();
            
            if(isset($cardholderId) && $cardholderId > 0) {
                $updateArr = array('status' => STATUS_ECS_FAILED, 'failed_reason' => $msg,
                    'date_failed' => new Zend_Db_Expr('NOW()'));
                $this->setError($msg);
                $this->update($updateArr, "id= $cardholderId");
            }
            return FALSE;
        }
        $this->setTxncode($cardholderId);
        return $cardholderId;
    }
    
     public function checkCardholderDuplication($param) {
        $memberId = isset($param['member_id']) ? $param['member_id'] : '';
        $cardNumber = isset($param['card_number']) ? $param['card_number'] : '';
        $mobile = isset($param['mobile']) ? $param['mobile'] : '';
        $batchName = isset($param['batch_name']) ? $param['batch_name'] : '';
        $productId = isset($param['product_id']) ? $param['product_id'] : '';
        
        $select = $this->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER, array('id'));
        if (isset($memberId) && $memberId != '') {
            $select->where("member_id =?", $memberId);
        }
        if (isset($cardNumber) && $cardNumber != '') {
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $cardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')");
            $select->where("card_number =?", $cardNumber);
        }
        if (isset($mobile) && $mobile != '') {
            $select->where("mobile =?", $mobile);
        }
        if (isset($batchName) && $batchName != '') {
            $select->where("batch_name =?", $batchName);
        }
        if (isset($productId) && $productId != '') {
        $select->where("product_id = ?", $productId);
        }
        
        $select->where("status =? ", STATUS_ACTIVE);
        
        
        $rs = $this->fetchRow($select);

        if (empty($rs)) {
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function insertCardhoderDetail($params){
        try{
	    if($params['card_number']!=''){
		$encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
		$params['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$params['card_number']."','".$encryptionKey."')");
	    }
	    if($params['crn']!=''){
		$encryptionKey = App_DI_Container::get('crn')->crnkey;
		$params['crn'] = new Zend_Db_Expr("AES_ENCRYPT('".$params['crn']."','".$encryptionKey."')");
	    }
           $this->_db->insert(DbTable::TABLE_KOTAK_CORP_CARDHOLDER_DETAILS,$params);
           return TRUE;
        }catch (Exception $e) {
              App_Logger::log($e->getMessage(), Zend_Log::ERR);
              return FALSE;
        }
}

public function insertCardholderBatch($dataArr, $batchName, $status) {
        $user = Zend_Auth::getInstance()->getIdentity();
        $validateArr = array();
        
        if (empty($dataArr))
            throw new Exception('Data missing for add cardholder');

        $failed_reason = '';
        
        $checkPin = $this->checkPinCity($dataArr[16], $dataArr[17]);
        if (!$checkPin) 
        {
            $status = STATUS_FAILED;
            $failed_reason = 'Pin Code/city master validation failed';
        }
        else
        {
             $paramChkMob = array(
                         'mobile' => $dataArr[11],
                         'product_id' => $dataArr['product_id']
                             );
             $checkMobile = $this->checkCardholderDuplication($paramChkMob);
            $paramChk = array(
                'mobile' => $dataArr[11],
                'card_number' => $dataArr[0],
                'product_id' => $dataArr['product_id']
            );
            $check = $this->checkCardholderDuplication($paramChk);
            $checkCard = $this->checkDuplicateCardhNumberBatch(array('card_number' => $dataArr[0],'batch_name' => $batchName));
            $checkCardNumber = $this->checkDuplicateCardhNumber(array('card_number' => $dataArr[0]));
            if(!$checkMobile){
                 $status = STATUS_FAILED;
                 $failed_reason = 'Duplicate Mobile'; 
                }elseif (!$check) {

                  $status = STATUS_FAILED;
                  $failed_reason = 'Duplicate record';
            }elseif(!$checkCard){
                  $status = STATUS_FAILED;
                  $failed_reason = 'Duplicate card number';
            }elseif(!$checkCardNumber){
                  $status = STATUS_FAILED;
                  $failed_reason = 'Card number already exists';
            }
            else
            {
                $validateArr['card_number'] = $dataArr[0];
                $validateArr['card_pack_id'] = $dataArr[1];
                $validateArr['employee_id'] = $dataArr[4];
                $validateArr['corporate_id'] = $dataArr[20];
                $validateArr['employer_name'] = $dataArr[19];
                $validateArr['first_name'] = $dataArr[5];
                $validateArr['last_name'] = $dataArr[7];
                $validateArr['name_on_card'] = $dataArr[8];
                $validateArr['email'] = $dataArr[12];
                $validateArr['date_of_birth'] = $dataArr[10];
                $validateArr['gender'] = $dataArr[9];
                $validateArr['city'] = $dataArr[16];
                $validateArr['pincode'] = $dataArr[17];
                $validateArr['address_line1'] = $dataArr[14];
                $validateArr['comm_city'] = $dataArr[23];
                $validateArr['comm_pin'] = $dataArr[24];
                $validateArr['comm_address_line1'] = $dataArr[21];
                $validateArr['mobile'] = $dataArr[11];
                $validateArr['mother_maiden_name'] = $dataArr[18];
                $validateArr['id_proof_type'] = $dataArr[25];
                $validateArr['address_proof_type'] = $dataArr[27];
                $validateArr['id_proof_number'] = $dataArr[26];
                $validateArr['address_proof_number'] = $dataArr[28];
                
                $valid = $this->isValid($validateArr);
                         
                if (!$valid) {
                    $errMsg = $this->getError();
                    $errorMsg = empty($errMsg) ? 'details invalid' : $errMsg;

                    $status = STATUS_FAILED;
                    $failed_reason = $errorMsg;
                } 
            }
        }

        $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $dataArr[0] = new Zend_Db_Expr("AES_ENCRYPT('".$dataArr[0]."','".$encryptionKey."')");
        $data = array(
            'card_number' => $dataArr[0],
            'card_pack_id' => $dataArr[1],
            'afn' => $dataArr[2],
            'member_id' => $dataArr[3],
            'employee_id' => $dataArr[4],
            'first_name' => $dataArr[5],
            'middle_name' => $dataArr[6],
            'last_name' => $dataArr[7],
            'name_on_card' => $dataArr[8],
            'gender' => $dataArr[9],
            'date_of_birth' => $dataArr[10],
            'mobile' => $dataArr[11],
            'email' => $dataArr[12],
            'landline' => $dataArr[13],
            'address_line1' => $dataArr[14],
            'address_line2' => $dataArr[15],
            'city' => $dataArr[16],
            'pincode' => $dataArr[17],
            'mother_maiden_name' => $dataArr[18],
            'employer_name' => $dataArr[19],
            'corporate_id' => $dataArr[20],
            'comm_address_line1' => $dataArr[21],
            'comm_address_line2' => $dataArr[22],
            'comm_city' => $dataArr[23],
            'comm_pin' => $dataArr[24],
            'id_proof_type' => $dataArr[25],
            'id_proof_number' => $dataArr[26],
            'address_proof_type' => $dataArr[27],
            'address_proof_number' => $dataArr[28],
            'by_ops_id' => $user->id,
            'batch_name' => $batchName,
            'product_id' => $dataArr['product_id'],
            'upload_status' => $status,
            'failed_reason' => $failed_reason,
            'date_created' => new Zend_Db_Expr('NOW()')
        );
        if(!empty($user->corporate_code)){
            $data['by_ops_id'] = 0;
            $data['by_corporate_id'] = $user->id;
        }
       
       
        $this->_db->insert(DbTable::TABLE_KOTAK_CORP_CARDHOLDER_BATCH, $data);
       
        return TRUE;
    }


 public function showPendingCardholderDetails($batchName, $product_id = FALSE) { 
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER_BATCH, array(
            'id', 'product_id', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no', 'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_name', 'society_id', 'society_name', 'nominee_name', 'nominee_relationship', 'date_created', 'place_application', 'date_updated', 'upload_status', 'failed_reason'
        ));
        $select->where('upload_status = ?', STATUS_TEMP);
        $select->where('batch_name = ?', $batchName);
        
        if($product_id)
        {
         $select->where('product_id = ?', $product_id);   
        }
        
        
        if(!empty($user->corporate_code)){
            $select->where('by_corporate_id = ?', $user->id);  
            
        }else{
            $select->where('by_ops_id = ?', $user->id);  
        }
        
        $select->order('id ASC');
        //return $this->_paginate($select, $page, $paginate);
        return $this->_db->fetchAll($select);
    }

  public function bulkAddCardholder($idArr, $batchName ,$status = STATUS_ECS_PENDING) {
     
        if (empty($idArr))
            throw new Exception('Data missing for add cardholder');
        $custProductModel = new Corp_Kotak_CustomerProduct();
        $custPurseModel = new Corp_Kotak_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $objCustomerMaster = new CustomerMaster();
        $productModel = new Products();
        
        $user = Zend_Auth::getInstance()->getIdentity();
        $str = '';
        try {
            // Foreach selected id value
            foreach ($idArr as $id) {
                
                $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
                
                $select = $this->_db->select();
                $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER_BATCH , array(
                             'id', 'product_id', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no', 'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_name', 'society_id', 'society_name', 'nominee_name', 'nominee_relationship', 'date_created', 'place_application', 'date_updated', 'upload_status', 'failed_reason'
                ));
                $select->where("id =?", $id);

                $dataArr = $this->_db->fetchRow($select); 
                $this->_db->beginTransaction();
                $checkFile = $this->checkFilename($dataArr['batch_name'], $dataArr['product_id']);
                
                if (!$checkFile) 
                {
                    $updateArr = array('upload_status' => STATUS_FAILED, 'failed_reason' => 'Filename already uploaded');
                    $this->_db->update(DbTable::TABLE_KOTAK_CORP_CARDHOLDER_BATCH, $updateArr, "id= $id");
                }
                else
                { 
                    $checkPin = $this->checkPinCity($dataArr['city'], $dataArr['pincode']);
                   
                    if (!$checkPin) 
                    {
                        $updateArr = array('upload_status' => STATUS_FAILED, 'failed_reason' => 'Pin Code/city master validation failed');
                        $this->_db->update(DbTable::TABLE_KOTAK_CORP_CARDHOLDER_BATCH, $updateArr, "id= $id");
                    }
                    else
                    { 
                        // Check valid Data
                         $valid = $this->isValid($dataArr);
                         
                         if (!$valid) {
                          $errMsg = $this->getError();
                          $errorMsg = empty($errMsg) ? 'details invalid' : $errMsg;
                
                         $updateArr = array('upload_status' => STATUS_FAILED, 'failed_reason' => $errorMsg);
                         $this->_db->update(DbTable::TABLE_KOTAK_CORP_CARDHOLDER_BATCH, $updateArr, "id= $id");
                       } 
                        else {
                           $paramChk = array(
                            'mobile' => $dataArr['mobile'],
                            'member_id' => $dataArr['member_id'],
                            'card_number' => $dataArr['card_number'],
                        );
                        $check = $this->checkCardholderDuplication($paramChk);
                        $checkCard = $this->checkDuplicateCardhNumber(array('card_number' => $dataArr['card_number']));    
                        if (!$check) {
                     
                              $updateArr = array('upload_status' => STATUS_FAILED, 'failed_reason' => 'Duplicate record');
                              $this->_db->update(DbTable::TABLE_KOTAK_CORP_CARDHOLDER_BATCH, $updateArr, "id= $id");
                         }elseif(!$checkCard){
                              $updateArr = array('upload_status' => STATUS_FAILED, 'failed_reason' => 'Duplicate record');
                              $this->_db->update(DbTable::TABLE_KOTAK_CORP_CARDHOLDER_BATCH, $updateArr, "id= $id");
                         }
                        else
                        {
                            $productDetail = $productModel->getProductInfo($dataArr['product_id']);
                            $customerMasterId = $objCustomerMaster->generateCustomerId(array('bank_id' => $productDetail['bank_id']));
                            $custMasterDetails = $objCustomerMaster->findById($customerMasterId);
                            $custMasterDetail = Util::toArray($custMasterDetails);
                            // adding data in rat_customer_master table
                            
                            $ratCustomerMasterData = array(
                                'customer_master_id' => $customerMasterId,
                                'shmart_crn' => $custMasterDetail['shmart_crn'],
                                'first_name' => $dataArr['first_name'],
                                'middle_name' => $dataArr['middle_name'],
                                'last_name' => $dataArr['last_name'],
                                'aadhaar_no' => (isset($dataArr['id_proof_type']) && strtolower($dataArr['id_proof_type']) == 'aadhar card') ? $dataArr['id_proof_number'] : '',
                                'pan' => (isset($dataArr['id_proof_type']) && strtolower($dataArr['id_proof_type']) == 'pan') ? $dataArr['id_proof_type'] : '',
                                'mobile_country_code' => isset($dataArr['mobile_country_code']) ? $dataArr['mobile_country_code'] : '',
                                'mobile' => $dataArr['mobile'],
                                'email' => $dataArr['email'],
                                'gender' => $dataArr['mobile'],
                                'date_of_birth' => isset($dataArr['date_of_birth']) ? $dataArr['date_of_birth'] : '',
                                'status' => STATUS_ACTIVE,
                            );
                            
                            $ratCustomerId = $this->addKotakCustomerMaster($ratCustomerMasterData);
                
                            $purseDetails = $masterPurseModel->getPurseDetailsbyBankIdProductId($dataArr['product_id'], $productDetail['bank_id']);
                            foreach ($purseDetails as $purseDetail) {
                                $purseArr = array(
                                    'customer_master_id' => $customerMasterId,
                                    'kotak_customer_id' => $ratCustomerId,
                                    'product_id' => $dataArr['product_id'],
                                    'purse_master_id' => $purseDetail['id'],
                                    'bank_id' => $productDetail['bank_id'],
                                    //'by_ops_id' => $user->id,
                                    'date_updated' => new Zend_Db_Expr('NOW()')
                                );
                                $purseParam = array('kotak_customer_id' => $ratCustomerId, 'purse_master_id' => $purseDetail['id']);
                                $purseDetails = $custPurseModel->getCustPurseDetails($purseParam);
                                if (empty($purseDetails)) { // If purse entry not found
                                    $custPurseModel->save($purseArr);
                                }
                            } 
                            
                            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                            $cardNumEnc = new Zend_Db_Expr("AES_ENCRYPT('".$dataArr['card_number']."','".$encryptionKey."')");
                            
                            $data = array(
                                'afn' => $dataArr['afn'],
                                'card_number' => $cardNumEnc,
                                'customer_type' => TYPE_NONKYC,
                                'card_pack_id' => $dataArr['card_pack_id'],
                                'kotak_customer_id' => $ratCustomerId,
                                'customer_master_id' => $customerMasterId,
                                'member_id' => $dataArr['member_id'],
                                'employee_id' => $dataArr['employee_id'],
                                'first_name' => $dataArr['first_name'],
                                'middle_name' => $dataArr['middle_name'],
                                'last_name' => $dataArr['last_name'],
                                'name_on_card' => $dataArr['name_on_card'],
                                'gender' => Util::getGenderTxt($dataArr['gender']),
                                'date_of_birth' => Util::returnDateFormatted($dataArr['date_of_birth'], "d/m/Y", "Y-m-d", "/", "-"),
                                'mobile' => $dataArr['mobile'],
                                'email' => $dataArr['email'],
                                'landline' => $dataArr['landline'],
                                'address_line1' => $dataArr['address_line1'],
                                'address_line2' => $dataArr['address_line2'],
                                'city' => $dataArr['city'],
                                'pincode' => $dataArr['pincode'],
                                'mother_maiden_name' => $dataArr['mother_maiden_name'],
                                'employer_name' => $dataArr['employer_name'],
                                'employee_id' => $dataArr['employee_id'],
                                'corporate_id' => $dataArr['corporate_id'],
                                'comm_address_line1' => $dataArr['comm_address_line1'],
                                'comm_address_line2' => $dataArr['comm_address_line2'],
                                'comm_city' => $dataArr['comm_city'],
                                'comm_pin' => $dataArr['comm_pin'],
                                'id_proof_type' => $this->mediAssistIdProofType($dataArr['id_proof_type']),
                                'id_proof_number' => $dataArr['id_proof_number'],
                                'address_proof_type' => $this->mediAssistAddressProofType($dataArr['address_proof_type']),
                                'address_proof_number' => $dataArr['address_proof_number'],
                                'pan' => $dataArr['pan'],
                                'aadhaar_no' => $dataArr['aadhaar_no'],
                                'batch_id' => $dataArr['id'],
                                'batch_name' => $dataArr['batch_name'],
                                'product_id' => $dataArr['product_id'],
                                'status' => $status,
                                'status_bank' => STATUS_PENDING,
                                'status_ecs' => STATUS_WAITING,
                                'date_created' => new Zend_Db_Expr('NOW()')
                            );
                            if(!empty($user->corporate_code)){
                                $data['by_ops_id']= 0;
                                $data['by_corporate_id']= $user->id;
                                $data['status_ops'] = ($productDetail['const'] == KOTAK_SEMICLOSE_GPR) ? STATUS_APPROVED : STATUS_PENDING;
                            } else {
                                $data['by_ops_id'] = $user->id;
                                $data['by_corporate_id']= 0;
                                $data['status_ops'] = STATUS_APPROVED;
                            }
                            $this->insert($data);
                            $cardholderId = $this->_db->lastInsertId(DbTable::TABLE_KOTAK_CORP_CARDHOLDER, 'id');
                            $updateArr = array('upload_status' => STATUS_PASS);
                            $this->_db->update(DbTable::TABLE_KOTAK_CORP_CARDHOLDER_BATCH, $updateArr, "id= $id");

                            // Entry in Customer product table
                            $prodArr = array('product_customer_id' => $cardholderId,
                                'kotak_customer_id' => $ratCustomerId,
                                'product_id' => $dataArr['product_id'],
                                'program_type' => $productDetail['program_type'],
                                'bank_id' => $productDetail['bank_id'],
                                'by_agent_id' => 0,
                                'by_ops_id' => $data['by_ops_id'],
                                'by_corporate_id' => $data['by_corporate_id'],
                                'date_created' => new Zend_Db_Expr('NOW()'),
                                'status' => STATUS_ACTIVE);
                            $custProductModel->save($prodArr);
            
                        } 
                      }  
                    }
                }
                $this->_db->commit();
            }// END of foreach loop
            $notInid = implode(",", $idArr);
            $rejectedArr = array('upload_status' => STATUS_REJECTED);


            $this->_db->update(DbTable::TABLE_KOTAK_CORP_CARDHOLDER_BATCH, $rejectedArr, "id NOT IN ($notInid) AND batch_name = '$batchName' AND upload_status = '".STATUS_TEMP."'");
        } catch (Exception $e) {
            
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_db->rollBack();
            throw new Exception($e->getMessage());
        }
        return TRUE;
    }
    
     public function checkFilename($fileName, $productId = 0) {
        $select = $this->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER, array('id'));
        $select->where("batch_name =?", $fileName);
        if($productId > 0) {
            $select->where("product_id = ?", $productId);
        }
        $select->where("status IN ('".STATUS_ACTIVE."', '".STATUS_INACTIVE."', '".STATUS_ECS_FAILED."')");
        $rs = $this->fetchRow($select);
        if (empty($rs)) {
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
  
      public function checkPinCity($city = '', $pincode = '') {
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_CITIES, array('name'));
        $select->where("pincode =?", $pincode);
        $rs = $this->_db->fetchAll($select);
        foreach($rs as $row){
            if(strtolower($row['name']) == strtolower($city)) return TRUE;
        }
        return FALSE;  
        
    }
   
      public function isValid($param) {

        $v = new Validator();
        $card_number = isset($param['card_number']) ? $param['card_number'] : '';
        $card_pack_id = isset($param['card_pack_id']) ? $param['card_pack_id'] : '';
        $employee_id = isset($param['employee_id']) ? $param['employee_id'] : '';
        $corporate_id = isset($param['corporate_id']) ? $param['corporate_id'] : '';
        $employer_name = isset($param['employer_name']) ? $param['employer_name'] : '';
        $first_name = isset($param['first_name']) ? $param['first_name'] : '';
        $last_name = isset($param['last_name']) ? $param['last_name'] : '';
        $name_on_card = isset($param['name_on_card']) ? $param['name_on_card'] : '';
        $email = isset($param['email']) ? $param['email'] : '';
        $dob = isset($param['date_of_birth']) ? $param['date_of_birth'] : '';
        $gender = isset($param['gender']) ? $param['gender'] : '';
        $city = isset($param['city']) ? $param['city'] : '';
        $pincode = isset($param['pincode']) ? $param['pincode'] : '';
        $line1 = isset($param['address_line1']) ? $param['address_line1'] : '';
        $commcity = isset($param['comm_city']) ? $param['comm_city'] : '';
        $commpincode = isset($param['comm_pin']) ? $param['comm_pin'] : '';
        $commline1 = isset($param['comm_address_line1']) ? $param['comm_address_line1'] : '';
        $mobile = isset($param['mobile']) ? $param['mobile'] : '';
        $mother_maiden_name = isset($param['mother_maiden_name']) ? $param['mother_maiden_name'] : '';
        $id_proof_type = isset($param['id_proof_type']) ? $param['id_proof_type'] : '';
        $add_proof_type = isset($param['address_proof_type']) ? $param['address_proof_type'] : '';
        $id_proof_num = isset($param['id_proof_number']) ? $param['id_proof_number'] : '';
        $address_proof_num = isset($param['address_proof_number']) ? $param['address_proof_number'] : '';
       
        
        if($card_number != ''){
            if(strlen($card_number) != 16 || !(ctype_digit($card_number))){
                $this->setError('Invalid Card Number');
                return FALSE;
            }
        }
        
        if($card_pack_id == '' || strlen($card_pack_id) != 14 || !(ctype_digit($card_pack_id))){
             $this->setError('Invalid Card Pack ID');
            return FALSE;
        }
        
        if($employee_id=='' || strlen($employee_id) <=0 || strlen($employee_id) > 16  || !(ctype_alnum($employee_id))){
             $this->setError('Invalid Employee ID');
            return FALSE;
        }
        if(is_numeric($employee_id) && $employee_id <=0){
            $this->setError('Invalid Employee ID');
            return FALSE;
        }
         if($first_name == '' || !(ctype_alpha($first_name))){
             $this->setError('Invalid First Name');
            return FALSE;
        }
         if($last_name == '' || !(ctype_alpha($last_name))){
             $this->setError('Invalid Last Name');
            return FALSE;
        }
        // if($name_on_card == '' ){
        //     $this->setError('Invalid Name on Card');
        //    return FALSE;
        //}
         if($gender == '' || ( strtolower($gender) != 'm' && strtolower($gender) != 'f')){
             $this->setError('Invalid Gender');
            return FALSE;
        }
        if(!isset($dob) || $dob == ''){
             $this->setError('Invalid Date of Birth');
             return FALSE;
        }
        if(isset($dob) || $dob != ''){
             $d = DateTime::createFromFormat('d/m/Y', $dob);
             $validDate = $d && $d->format('d/m/Y') == $dob;
             if(!$validDate){ 
               $this->setError('Invalid Date of Birth');
               return FALSE;
             } 
        }
       
        if(strlen($mobile) != 10 || !(ctype_digit($mobile))){
           $this->setError('Invalid Mobile Number');            
           return FALSE;
        } 
          if($email == '' || (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$email))){
           $this->setError('Invalid Email');            
           return FALSE;
        } 
               
        if($city == '' || $pincode == '' || !(ctype_digit($pincode)) || $line1 == ''){
            $this->setError('Incomplete Address Details');
            return FALSE;  
        }
        
        if(strlen($line1) > 30){
            $this->setError('Address line exceeds 30 characters length');
            return FALSE;  
        }
        
        if(strlen($city) > 20){
            $this->setError('City exceeds 20 characters length');
            return FALSE;  
        }
        
        if(strlen($pincode) != 6){
            $this->setError('Invalid Pincode');
            return FALSE;  
        }
        
        if($commcity == '' || $commpincode == '' || !(ctype_digit($commpincode)) || $commline1 == ''){
            $this->setError('Incomplete Communication Address Details');
            return FALSE;  
        }
       
        if(strlen($commline1) > 30){
            $this->setError('Address line exceeds 30 characters length');
            return FALSE;  
        }
        
        if(strlen($commcity) > 20){
            $this->setError('City exceeds 20 characters length');
            return FALSE;  
        }
        
        if(strlen($commpincode) != 6){
            $this->setError('Invalid Pincode');
            return FALSE;  
        }
        
//        if($aadharNo != ''){
//        if(!$v->validateAadhar($aadharNo,$thowException = FALSE)){
//             $this->setError('Invalid Aadhaar Number');
//            return FALSE;
//        }
//        }
//        if($pan != ''){
//        if(!$v->validatePAN($pan,$thowException = FALSE)){
//             $this->setError('Invalid PAN');
//            return FALSE;
//        }
//        }
      
        if($corporate_id == '' || strlen($corporate_id) > 16){
           $this->setError('Invalid Corporate ID');            
           return FALSE;
        } 
         if($employer_name == ''){
           $this->setError('Invalid Employer Name');            
           return FALSE;
        } 
         if($id_proof_type == ''){
            $this->setError('Invalid ID Proof Type');            
           return FALSE;
        }


        if($add_proof_type == ''){
            $this->setError('Invalid Address Proof Type');            
           return FALSE;
        }
         if($mother_maiden_name == '' || strlen($mother_maiden_name) > 25){
            $this->setError('Invalid Mother Maiden Name');            
           return FALSE;
        }

//        if($id_proof_num == '' || strlen($id_proof_num) > 30)
//        {
//            $this->setError('Invalid identity proof details');
//            return FALSE;
//        }
//        
//        if($address_proof_num == '' || strlen($address_proof_num) > 30)
//        {
//            $this->setError('Invalid address proof details');
//            return FALSE;
//        }
        
        if( $id_proof_num != ''){
          if($id_proof_type == '03'){
               if(!$v->validateAadhar($id_proof_num,$thowException = FALSE)){
                    $this->setError('Invalid Aadhaar Number');
                   return FALSE;
               }
          }else if($id_proof_type == '02'){
             if(!$v->validatePAN($id_proof_num,$thowException = FALSE)){
                    $this->setError('Invalid PAN');
                   return FALSE;
               }   
          }
        }
         return TRUE;
    }
    
    
    //**********************//
    public function getCardholderInfoByCardNumber(array $param) {
        if(!isset($param['card_number']) || !isset($param['product_id'])) {
            throw new App_Exception('Invalid details provided to fetch cardholder details');
            return FALSE;
        }
        //Decryption of Card Number and CRN
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`kcc`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`kcc`.`crn`,'".$decryptionKey."') as crn");
        
        //Encryption card number
        $cardNumber = isset($param['card_number']) ? $param['card_number'] : '';
        $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $cardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')");
        
            
        $select = $this->select();
        $select->from($this->_name. ' as kcc',array(
            'id', 'product_id', 'kotak_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no','pan' ,'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'nominee_name', 'nominee_relationship', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'date_toggle_kyc', 'status', 'status_bank', 'status_ops', 'status_ecs', 'aml_status'
        ));
        $select->where("status = '".STATUS_ACTIVE."'")
            ->where("card_number =?", $cardNumber)
            ->where("product_id = ?",$param['product_id']);
        
        return $this->fetchRow($select);
    }
    
    
    public function getPendingKyc($page = 1, $params,$paginate = NULL) {
        
        //Decryption of Card Number and CRN
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`rhc`.`card_number`,'".$decryptionKey."') as card_number");
        $select = $this->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER . " as rhc", 
                array('rhc.id as id','rhc.customer_master_id', $card_number,'rhc.kotak_customer_id',
                    'rhc.member_id', 'rhc.employee_id', 
                    'concat(rhc.first_name," ",rhc.last_name) as cardholder_name', 'rhc.gender', 
                    'rhc.mobile', 'rhc.email', 'rhc.employer_name', 'rhc.status', 
                    'rhc.afn', 'rhc.mobile', 'rhc.first_name', 'rhc.middle_name', 'rhc.last_name', 
                    'rhc.date_of_birth', 'rhc.name_on_card', 'rhc.batch_name', 'rhc.corporate_id',
                    'rhc.product_id','rhc.date_activation'));
         $select->where("status = '".STATUS_ACTIVE."' OR status = '".STATUS_ECS_PENDING."' OR status = '".STATUS_ACTIVATION_PENDING."' ");
        $select->where('product_id = ?',$params['product_id']);
        $select->where("id_proof_doc_id = 0 OR address_proof_doc_id = 0");
        $select->where('date_created >= ?',$params['from_date'].' 00:00:00'); 
        $select->where('date_created <= ?',$params['to_date'].' 23:59:59'); 
        $select->order("cardholder_name");
        return $this->_paginate($select, $page, $paginate);
        //return $this->fetchAll($select);

    }
    
      
    //**********************//
    public function getCardholderInfoBymemberID(array $param) {
        if(!isset($param['member_id']) || !isset($param['product_id'])) {
            throw new App_Exception('Invalid details provided to fetch cardholder details');
            return FALSE;
        }
        
        //Decryption of Card Number and CRN
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`kcc`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`kcc`.`crn`,'".$decryptionKey."') as crn");
        
        $select = $this->select();
        $select->from($this->_name. ' as kcc',array('id', 'product_id', 'kotak_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no', 'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'nominee_name', 'nominee_relationship', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'date_toggle_kyc', 'status', 'status_bank', 'status_ops', 'status_ecs', 'aml_status'));
        $select->where("status = '".STATUS_ACTIVE."'")
            ->where("member_id = ?",$param['member_id'])
            ->where("product_id = ?",$param['product_id']);
        return $this->fetchRow($select);

    }
    
    public function getBatchDetailsByDate($batchArr) {
        
        //Decryption of Card Number and CRN
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`cb`.`card_number`,'".$decryptionKey."') as card_number");
        
        $select = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER_BATCH . " as cb", array('cb.id', $card_number, 
                    'cb.card_pack_id', 'cb.member_id', 'cb.employee_id', 
                    'cb.first_name', 'cb.last_name',
                    'cb.name_on_card', 'cb.mobile', 'cb.email', 'cb.city', 'cb.pincode', 'cb.gender',
                    'cb.date_of_birth', 'cb.employer_name', 'cb.corporate_id', 'cb.upload_status', 'cb.failed_reason as batch_failed_reason'))
                ->joinLeft(DbTable::TABLE_KOTAK_CORP_CARDHOLDER . ' as c', "c.batch_id = cb.id AND cb.upload_status = '" . STATUS_PASS . "' ", array('c.id as cardholder_id', 'c.status as cardholder_status', 'c.failed_reason'))
                ->where("cb.upload_status IN ('".STATUS_PASS."', '".STATUS_DUPLICATE."', '".STATUS_FAILED."')");
        if(isset($batchArr['batch_name']) && !empty($batchArr['batch_name'])) {
            $select->where('cb.batch_name =?', $batchArr['batch_name']);
        }
        if(isset($batchArr['start_date']) && !empty($batchArr['start_date'])) {
            $select->where('cb.date_created >= ?', $batchArr['start_date']);
        }
        if(isset($batchArr['end_date']) && !empty($batchArr['end_date'])) {
            $select->where('cb.date_created <= ?', $batchArr['end_date']);
        }
        if(isset($batchArr['product_id']) && !empty($batchArr['product_id'])) {
            $select->where('cb.product_id =?', $batchArr['product_id']);
        }
        $data = array();
        $rs = $this->_db->fetchAll($select);
        $i = 0;
        foreach($rs as $val)
        {
            $data[$i]['id'] = $val['cardholder_id'];
            $data[$i]['member_id'] = $val['member_id'];
            $data[$i]['employee_id'] = $val['employee_id'];
            $data[$i]['card_number'] = Util::maskCard($val['card_number']);
            $data[$i]['cardholder_name'] = $val['first_name']." ".$val['last_name'];
            $data[$i]['name_on_card'] = $val['name_on_card'];
            $data[$i]['gender'] = $val['gender'];
            $data[$i]['date_of_birth'] = $val['date_of_birth'];
            $data[$i]['mobile'] = $val['mobile'];
            $data[$i]['email'] = $val['email'];
            $data[$i]['employer_name'] = $val['employer_name'];
            $data[$i]['corporate_id'] = $val['corporate_id'];
           
            if($val['upload_status'] == STATUS_DUPLICATE)
            {
                $data[$i]['status'] = STATUS_REJECTED;
                $data[$i]['failed_reason'] = 'Duplicate record';
            }
            elseif($val['upload_status'] == STATUS_FAILED)
            {
                $data[$i]['status'] = STATUS_REJECTED;
                $data[$i]['failed_reason'] = $val['batch_failed_reason'];
            }
            else
            {
                if($val['cardholder_status'] == STATUS_ECS_FAILED)
                {
                    $data[$i]['status'] = STATUS_REJECTED;
                    $data[$i]['failed_reason'] = 'Rejected at ECS system level';
                }
                elseif($val['cardholder_status'] == STATUS_INACTIVE)
                {
                    $data[$i]['status'] = STATUS_INACTIVE;
                    $data[$i]['failed_reason'] = 'Deactivated';
                }
                elseif($val['cardholder_status'] == STATUS_ECS_PENDING)
                {
                    $data[$i]['status'] = STATUS_ECS_PENDING;
                    $data[$i]['failed_reason'] = $val['failed_reason'];
                }
                elseif($val['cardholder_status'] == STATUS_ACTIVE)
                {
                    $data[$i]['status'] = STATUS_ACTIVE;
                    $data[$i]['failed_reason'] = '-';
                }
                
            }
            
            
            $i++;
        }
                
        
        
        return $data;
    }
    
    
    
    
    public function exportBatchDetailsByDate($params){
         $data = $this->getBatchDetailsByDate($params);
//         $data = $data->toArray();
         $retData = array();
        
        if(!empty($data))
        {
                     
            foreach($data as $key=>$data){
                    
                    $retData[$key]['member_id'] = $data['member_id'];
                    $retData[$key]['employee_id'] = $data['employee_id'];
                    $retData[$key]['card_number'] = Util::maskCard($data['card_number']);
                    $retData[$key]['cardholder_name'] = $data['cardholder_name'];
                    $retData[$key]['name_on_card'] = $data['name_on_card']; 
                    $retData[$key]['gender'] = ucfirst($data['gender']);
                    $retData[$key]['date_of_birth'] = Util::returnDateFormatted($data['date_of_birth'], "Y-m-d", "d-m-Y", "-");
                    $retData[$key]['mobile'] = $data['mobile'];
                    $retData[$key]['email'] = $data['email'];
                    $retData[$key]['employer_name'] = $data['employer_name'];
                    $retData[$key]['corporate_id'] = $data['corporate_id']; 
                    $retData[$key]['status'] = ucfirst($data['status']); 
                    $retData[$key]['failed_reason'] = $data['failed_reason']; 
                    
          }
        }
        
        return $retData;
         
     }
    
    
    public function getkotakBatchDDByDate($batchArr){
        
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER_BATCH, array('batch_name'));
        if(isset($batchArr['start_date']) && !empty($batchArr['start_date'])) {
            $select->where('date_created >= ?', Util::returnDateFormatted($batchArr['start_date'], "d-m-Y", "Y-m-d", "-", "-", 'from'));
        }
        if(isset($batchArr['end_date']) && !empty($batchArr['end_date'])) {
            $select->where('date_created <= ?', Util::returnDateFormatted($batchArr['end_date'], "d-m-Y", "Y-m-d", "-", "-", 'to'));
        }
        if(isset($batchArr['product_id']) && !empty($batchArr['product_id'])) {
            $select->where('product_id =?', $batchArr['product_id']);
        }
        $select->order('batch_name ASC');
        $select->group('batch_name');

        $batch = $this->_db->fetchAll($select);
        //$dataArray = array('' => 'Select Batch');
        $dataArray = array();
        foreach ($batch as $val) {
            $dataArray[$val['batch_name']] = $val['batch_name'];
        }
        return $dataArray;

    }
    

    public function getKycUpgradeReportByDate($reportArr) {
                
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`kch`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`kch`.`crn`,'".$decryptionKey."') as crn");
        
        $select = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER . " as kch", array('kch.id', 'kch.product_id', 'kch.kotak_customer_id', 'kch.customer_master_id', 'kch.customer_type', $crn, 'kch.unicode', $card_number, 'kch.card_pack_id', 'kch.afn', 'kch.member_id', 'kch.employee_id', 'kch.first_name', 'kch.middle_name', 'kch.last_name','concat(kch.first_name," ",kch.last_name) as cardholder_name', 'kch.name_on_card', 'kch.gender', 'kch.date_of_birth', 'kch.aadhaar_no', 'kch.pan', 'kch.mobile', 'kch.email', 'kch.landline', 'kch.address_line1', 'kch.address_line2', 'kch.city', 'kch.state', 'kch.pincode', 'kch.mother_maiden_name', 'kch.employer_name', 'kch.corporate_id', 'kch.comm_address_line1', 'kch.comm_address_line2', 'kch.comm_city', 'kch.comm_pin', 'kch.comm_state', 'kch.id_proof_type', 'kch.id_proof_number', 'kch.id_proof_doc_id', 'kch.address_proof_type', 'kch.address_proof_number', 'kch.address_proof_doc_id', 'kch.photo_doc_id', 'kch.other_id_proof', 'kch.by_ops_id', 'kch.by_agent_id', 'kch.by_corporate_id', 'kch.batch_id', 'kch.batch_name', 'kch.society_id', 'kch.society_name', 'kch.nominee_name', 'kch.nominee_relationship', 'kch.date_created', 'kch.place_application', 'kch.date_updated', 'kch.delivery_file_id', 'kch.date_activation', 'kch.failed_reason', 'kch.date_failed', 'kch.date_crn_update', 'kch.date_authorize', 'kch.recd_doc', 'kch.date_recd_doc', 'kch.recd_doc_id', 'kch.date_approval', 'kch.date_toggle_kyc', 'kch.status', 'kch.status_bank', 'kch.status_ops', 'kch.status_ecs', 'kch.aml_status'));
        
        if(isset($reportArr['from_date']) && !empty($reportArr['from_date'])) {
            $select->where('kch.date_toggle_kyc >= ?', $reportArr['from_date']);
        }
        if(isset($reportArr['to_date']) && !empty($reportArr['to_date'])) {
            $select->where('kch.date_toggle_kyc <= ?', $reportArr['to_date']);
        }
        if(isset($reportArr['product_id']) && !empty($reportArr['product_id'])) {
            $select->where('kch.product_id =?', $reportArr['product_id']);
        } 
        $result = $this->_db->fetchAll($select);
        return $result;
        
        
    }
    
    
    public function exportgetKycUpgradeReportByDate($params){
        $data = $this->getKycUpgradeReportByDate($params);
        //         $data = $data->toArray();
        $retData = array();

        if(!empty($data))
        {

           foreach($data as $key=>$data){

                   $retData[$key]['member_id'] = $data['member_id'];
                   $retData[$key]['employee_id'] = $data['employee_id'];
                   $retData[$key]['card_number'] = Util::maskCard($data['card_number'], 4);
                   $retData[$key]['cardholder_name'] = ucfirst($data['cardholder_name']);
                   $retData[$key]['name_on_card'] = ucfirst($data['name_on_card']); 
                   $retData[$key]['gender'] = ucfirst($data['gender']);
                   $retData[$key]['date_of_birth'] = Util::returnDateFormatted($data['date_of_birth'], "Y-m-d", "d-m-Y", "-");
                   $retData[$key]['mobile'] = $data['mobile'];
                   $retData[$key]['email'] = $data['email'];
                   $retData[$key]['crn'] = $data['crn'];
                   $retData[$key]['product_code'] = '';
                   $retData[$key]['bank_name'] = strtoupper($params['bank_name']);
                   $retData[$key]['aadhaar_no'] = $data['aadhaar_no']; 
                   $retData[$key]['date_created'] = Util::returnDateFormatted($data['date_created'], "Y-m-d", "d-m-Y", "-");
                   $retData[$key]['address_line1'] = $data['address_line1']; 
                   $retData[$key]['address_line2'] = $data['address_line2']; 
                   $retData[$key]['pincode'] = $data['pincode'];
                   $retData[$key]['state'] = ucfirst($data['state']);
                   $retData[$key]['date_toggle_kyc'] = Util::returnDateFormatted($data['date_toggle_kyc'], "Y-m-d", "d-m-Y", "-");
                   $retData[$key]['customer_type'] = strtoupper($data['customer_type']);

            }
         }

         return $retData;
         
    }
    
     //**********************//
    public function getCardholderBymemberID(array $param) {
        if(!isset($param['member_id']) || !isset($param['product_id'])) {
            throw new App_Exception('Invalid details provided to fetch cardholder details');
            return FALSE;
        }
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`kcc`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`kcc`.`crn`,'".$decryptionKey."') as crn");
        
        $select = $this->select();
        $select->from($this->_name. ' as kcc',array(
            'id', 'product_id', 'kotak_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no', 'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'nominee_name', 'nominee_relationship', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'date_toggle_kyc', 'status', 'status_bank', 'status_ops', 'status_ecs', 'aml_status'
        ));
        $select->where("member_id = ?",$param['member_id']);
        $select->where("product_id = ?",$param['product_id']);
        return $this->fetchRow($select);

    }
     
   
    public function updateKYC($params,$id) {
        $user = Zend_Auth::getInstance()->getIdentity();
        if (empty($params))
            throw new Exception('Data missing for processing');
        try {
            if((isset($params['card_number'])) && ($params['card_number'] != '') ) {
                $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $params['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$params['card_number']."','".$encryptionKey."')");
            }
            $this->update($params,"id = $id"); 
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception($e->getMessage());
        }
        return TRUE;
    }
    
    public function searchcustomerKYC($param) {
        $productId = isset($param['product_id']) && $param['product_id'] > 0 ? $param['product_id'] : '';
        $status = isset($param['status']) ? $param['status'] : '';
        $columnName = $param['searchCriteria'];
        $keyword = $param['keyword'];
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`kc`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`kc`.`crn`,'".$decryptionKey."') as crn");
                
        if($columnName == 'card_number'){
            $card_num = new Zend_Db_Expr("AES_DECRYPT(`kc`.`card_number`,'".$decryptionKey."')"); 
            $whereString = "$card_num LIKE '%$keyword%'";
        } else if($columnName == 'aadhaar card' || $columnName == 'passport' ){
            $whereString = "kc.id_proof_type = '$columnName' AND kc.id_proof_number = '$keyword'";
        }else{
            $whereString = "kc.$columnName LIKE '%$keyword%'"; 
        }
                
        $details = $this->_db->select()
                ->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER .' as kc', array('kc.id', $crn,'kc.customer_master_id', $card_number, 'kc.afn', 'concat(kc.first_name," ",kc.last_name) as name', 'kc.gender', 'kc.date_of_birth', 'kc.mobile', 'kc.email','kc.member_id', 'kc.status', 'kc.name_on_card', 'kc.date_failed', 'kc.failed_reason','kc.status_ops','kc.status_bank','kc.status_ecs','kc.card_pack_id','kc.employee_id','kc.employer_name','kc.corporate_id','kc.status','kc.customer_type'))
                ->where($whereString);
       
        if($productId != ''){
             $details->where("kc.product_id =?" , $productId);
        }
        if($status != '')
        {
            $details->where("status = '".STATUS_ACTIVE."'");
        }
        $details->order('kc.first_name DESC');
        return $this->_db->fetchAll($details);
    }
    
    public function getCustomerCount($param){
        
        $fromDate = isset($param['from']) ? $param['from'] : '';
        $toDate = isset($param['to']) ? $param['to'] : '';
        
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER . ' as c',array('count(id) as count'));
        if(isset($param['status']) && !empty($param['status'])) {    
            $select->where("c.status =?", $param['status']);
        }
        if(isset($param['product_id']) && !empty($param['product_id'])) {        
            $select->where("c.product_id =?", $param['product_id']);
        }
        if(isset($param['by_corporate_id']) && !empty($param['by_corporate_id'])) {            
            $select->where("c.by_corporate_id =?", $param['by_corporate_id']);
        }
        if($fromDate!="" && $toDate!=""){
            $select->where('date_created >= ?', $fromDate);
            $select->where('date_created <= ?', $toDate);
        }
                
        $data = $this->_db->fetchRow($select);
        
        if(isset($data['count'])){
            return $data['count'];
        }
        else {
            return 0;
        }
     }
     
     public function bulkApproval($params) {
        
        $user = Zend_Auth::getInstance()->getIdentity();
        $ip = $this->formatIpAddress(Util::getIP());
        
        $customerLogModel = new Corp_Kotak_CustomersLog();
        $objCustomerDetailModel = new Corp_Kotak_CustomerDetail();
        
        foreach ($params as $id) {
            $custDetails = $this->findById($id);
            $custDetails = Util::toArray($custDetails);
                
            unset($custDetails['id']);
            $custDetails['product_customer_id'] = $id;
            // Saving in details table 
            $objCustomerDetailModel->save($custDetails);


            $data = array('product_customer_id' => $id, 'by_type' => BY_CHECKER, 'by_id' => $user->id,
                'status_ops_old' => STATUS_PENDING, 'status_ops_new' => STATUS_APPROVED, 'ip' => $ip, 'comments' => 'Bulk Approval Process'
            );


            $customerLogModel->save($data);
            $params = array('status' => STATUS_APPROVED, 'id' => $id);
            $res = $this->changeStatus($params);
            
        }
        return TRUE;
    }
    
    public function kotakGPRCorpECSRegn() {
        $customerTrackModel = new CustomerTrack();
        $m = new \App\Messaging\Corp\Kotak\Operation();
        $user = Zend_Auth::getInstance()->getIdentity();
        $productModel = new Products();
        
        $dataArr = $this->ecsPendingKotakGPRArray();
        $numCust = 0;
        foreach ($dataArr as $data) {
            
            $id = $data['id'];
            $deliveryFileId = 0;


            $msg = '';
            try {
                
                    $cardholderArray = $this->getCardholderArray($data);
                    $ecsApi = new App_Api_ECS_Corp_Kotak();
                    $resp = $ecsApi->kotakGPRRegistration($cardholderArray);
                   
                    if ($resp == false) {
                        $msg = $ecsApi->getError();
                    }
               
            } catch (App_Exception $e) {
                $resp = false;
                $msg = $e->getMessage();
               
            } catch (Exception $e) {
                $resp = false;
                $msg = $e->getMessage();
               
            }
            if ($resp == true) {
                //On Success
                
                // update the status to STATUS_ACTIVE in cardholders
                $updateArr = array('status' => STATUS_ACTIVE, 'status_ecs' => STATUS_SUCCESS, 
                    'date_activation' => new Zend_Db_Expr('NOW()'), 
                    'failed_reason' =>'', 'delivery_file_id' => $deliveryFileId);
                $this->_db->update(DbTable::TABLE_KOTAK_CORP_CARDHOLDER, $updateArr, "id= $id");
                
                
                $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $card_numEnc = new Zend_Db_Expr("AES_ENCRYPT('".$data['card_number']."','".$encryptionKey."')");
                 // Insert into customer Track
                $customerTrackArr =  array(
                    'card_number' => $card_numEnc,
                    'card_pack_id' => $data['card_pack_id'],
                    'member_id' => $data['member_id'],
                    'crn' => $card_numEnc,
                    'mobile' => $data['mobile'],
                    'email' => $data['email'],
                    'name_on_card' => $data['name_on_card'],
                );
               $customerTrackModel->customerDetails($customerTrackArr, $data['product_id'], $data['id']);
               $productDetail = $productModel->getProductInfo($data['product_id']);
                             
                $userData = array('last_four' =>substr($data['card_number'], -4),
                    'product_name' => $productDetail['name'],
                    'mobile' => $data['mobile'],
                );
                $resp = $m->cardActivation($userData);
                
                
            } else {
                //On Failure
                $updateArr = array('status' => STATUS_ECS_FAILED,'status_ecs' => STATUS_FAILURE, 'failed_reason' => $msg,
                         'delivery_file_id' => $deliveryFileId , 'date_failed' => new Zend_Db_Expr('NOW()'));
                $this->update($updateArr, "id= $id");
               
                $updateprodArr = array('status' => STATUS_INACTIVE);
                $this->_db->update(DbTable::TABLE_KOTAK_CUSTOMER_PRODUCT, $updateprodArr, "product_customer_id= $id");
                
                
            }
            
            $numCust++;
        }
       
        
        return $numCust;
    }
    
    public function ecsPendingKotakGPRArray(){
        $productModel = new Products();
        $kotal_gpr_arr = array(PRODUCT_CONST_KOTAK_SEMICLOSE_GPR,PRODUCT_CONST_KOTAK_OPENLOOP_GPR);
        $productid_gpr = $productModel->getProductIDbyConstArr($kotal_gpr_arr); 
         
        //Decryption of Card Number and CRN
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`kc`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`kc`.`crn`,'".$decryptionKey."') as crn");
        
         $select = $this->_db->select();
         $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER .' as kc', array('id', 'product_id', 'kotak_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no', 'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'nominee_name', 'nominee_relationship', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'date_toggle_kyc', 'status', 'status_bank', 'status_ops', 'status_ecs', 'aml_status'));
         $select->where("kc.status_ecs = '" . STATUS_WAITING . "' OR kc.status_ecs = '" . STATUS_FAILURE . "'")
                ->where("kc.status_ops =?", STATUS_APPROVED)
                ->where("kc.product_id IN(?)", $productid_gpr)
                ->where("kc.status = '". STATUS_PENDING."' OR kc.status = '".STATUS_ECS_PENDING."'")
                ->order('kc.id')
                ->limit(KOTAK_CORP_ECS_REGN_LIMIT);
                
          return $this->_db->fetchAll($select);
    }

    
    public function exportSampleLoadRequests($param)
    {
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $product = isset($param['product_id']) && $param['product_id'] > 0 ? $param['product_id'] : '';
        $employer_name = isset($param['employer_name']) ? $param['employer_name'] : '';
        $employer_loc = isset($param['employer_loc']) ? $param['employer_loc'] : '';
        $status = isset($param['status']) ? $param['status'] : '';
       
        $byCorporateId = isset($param['by_corporate_id']) && $param['by_corporate_id'] > 0 ? $param['by_corporate_id'] : '';
        
        //Decryption of Card Number and CRN
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`c`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`c`.`crn`,'".$decryptionKey."') as crn");
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER . ' as c',array(
            'id', 'product_id', 'kotak_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no', 'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'nominee_name', 'nominee_relationship', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'date_toggle_kyc', 'status', 'status_bank', 'status_ops', 'status_ecs', 'aml_status', 
            'concat(c.first_name," ",c.last_name) as cardholder_name'));
        $select->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "c.product_id  = p.id",array('p.name as product_name'));
        $select->joinLeft(DbTable::TABLE_PURSE_MASTER . ' as pm',"c.product_id = pm.product_id",array('pm.code as wallet_code'));
        if ($product != '')
            $select->where("c.product_id = '" . $product . "'");
        if ($byCorporateId != '')
            $select->where("c.by_corporate_id = '" . $byCorporateId . "'");
        if ($employer_name != '') {
            $select->where("c.employer_name LIKE  '%". $employer_name. "%'");
        }
        if ($employer_loc != '') {
            $select->where("c.comm_city LIKE  '%". $employer_loc. "%'");
        }   
        if ($from != '' && $to != ''){
            $select->where("c.date_created >= '" . $param['from'] . "'");
            $select->where("c.date_created <= '" . $param['to'] . "'");
        }
        if ($status != '') {
           $select->where('c.status = ?', $status);
        }else{
            $select->where("c.status =  '" . STATUS_ACTIVE . "'");
        }
        $select->order("c.first_name");
        
        $rs = $this->fetchAll($select);  
                
        $retData = array();
        
        if(!empty($rs))
        {
            $i=0;         
            foreach($rs as $data){
                if(!empty($data['card_number'])){
                    $retData[$i]['txn_identifier_type']  = CORP_WALLET_TXN_IDENTIFIER_CN;
                    $retData[$i]['card_number']          = $data['card_number'];
                }elseif($data['member_id']){
                    $retData[$i]['txn_identifier_type']  = CORP_WALLET_TXN_IDENTIFIER_MI;
                    $retData[$i]['card_number']          = $data['member_id'];
                }
                $retData[$i]['amount']      = SAMPLE_AMOUNT_TEXT; 
                $retData[$i]['currency']    = CURRENCY_INR_CODE;
                $retData[$i]['narration'] = SAMPLE_NARRATION_TEXT;
                $retData[$i]['wallet_code'] = $data['wallet_code'];
                $retData[$i]['txn_no'] = '0';
                $retData[$i]['card_type']      = CORP_CARD_TYPE_NORMAL; 
                $retData[$i]['corporate_id']      = '0'; 
                $retData[$i]['mode']      = TXN_MODE_CR;
                $i++;
            }
            return $retData;
        }else{
            throw new Exception('No Records');
            return false;
        }
    }
    
    /*
     * Displays all the failed cardholders during bulk uploading
     */
    public function showFailedPendingCardholderDetails($batchName, $product_id = FALSE) {
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER_BATCH, array(
            'id', 'product_id', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no', 'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_name', 'society_id', 'society_name', 'nominee_name', 'nominee_relationship', 'date_created', 'place_application', 'date_updated', 'upload_status', 'failed_reason'
        ));
        $select->where('upload_status = ?', STATUS_FAILED);
        $select->where('batch_name = ?', $batchName);
        
        if($product_id)
        {
         $select->where('product_id = ?', $product_id);   
        }
                
        if(!empty($user->corporate_code)){
            $select->where('by_corporate_id = ?', $user->id);  
            
        }else{
            $select->where('by_ops_id = ?', $user->id);  
        }
        
        $select->order('id ASC');
        return $this->_db->fetchAll($select);
    }
    
     public function checkBatchFilename($fileName, $productId = 0) { 
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER_BATCH,array('id'));
        $select->where('batch_name = ?', $fileName);
        if($productId > 0) {
            $select->where("product_id = ?", $productId);
        }
        //$select->where("status IN ('".STATUS_ACTIVE."', '".STATUS_INACTIVE."', '".STATUS_ECS_FAILED."')");
        $rs = $this->_db->fetchRow($select);
        if (empty($rs)) {
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function exportCRNStatus($param)
    {
        $objCRNMaster = new CRNMaster();
                
        $data = $objCRNMaster->searchCRNStatus(array(
        'product_id' => $param['product_id'],
        'status' => $param['status'],
        'card_number' => $param['crn'],
        'card_pack_id' => $param['card_pack_id'],
        'file' => $param['file'],
        ),false);
        
        $retData = array();
        
        if (!empty($data)) {
            foreach ($data as $key => $data) {
                $retData[$key]['card_number'] = Util::maskCard($data['card_number']);
                $retData[$key]['card_pack_id'] = $data['card_pack_id'];
                $retData[$key]['member_id'] = $data['member_id'];
                $retData[$key]['status'] = ucfirst($data['status']);
                $retData[$key]['file'] = $data['file'];
                $retData[$key]['date_created'] = $data['date_created'];
            }
        }
        return $retData;
    }
    public function checkDuplicateCardhNumber($param) {
        
          $cardNumber = isset($param['card_number']) ? $param['card_number'] : '';
          if (isset($cardNumber) && $cardNumber != '') {
              
               //Encryption of CRN and Card Number 
               $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
               $cardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')");
               $select = $this->select();
               $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER, array('id'));
               $select->where("card_number =?", $cardNumber);
               $select->where("status = '".STATUS_ACTIVE."' OR status_ecs = '".STATUS_WAITING."'");
               $rs = $this->fetchRow($select);
               if (empty($rs)) {
                   return TRUE;
               } else {
                   return FALSE;
               }
          }else {
                   return TRUE;
          } 
        
     }
     
     public function checkDuplicateCardhNumberBatch($param) {
        
          $cardNumber = isset($param['card_number']) ? $param['card_number'] : '';
          $batch_name = isset($param['batch_name']) ? $param['batch_name'] : '';
          if (isset($cardNumber) && $cardNumber != '') {
               $select = $this->_db->select();
               $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER_BATCH, array('id'));
               if($cardNumber!=''){
                $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $cardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')");
                $select->where("card_number =?", $cardNumber);
               }
              
               $select->where("batch_name =?", $batch_name);
                
               $rs = $this->_db->fetchRow($select);
               if (empty($rs)) {
                   return TRUE;
               } else {
                   return FALSE;
               }
          }else {
                   return TRUE;
          } 
     }
     
     public function updateCardHoldersDetails($data,$id){
        $update = $this->update($data,"id = $id");
        if($update)
                 return TRUE;
             else 
                 return FALSE;
    }
    
    public function updateAmlKotakCardholders(){
        $details = $this->_db->select()
                       ->from($this->_name,array('id','first_name', 'last_name'))
                       ->where ("status ='".STATUS_ACTIVE."'")
                       ->where ("aml_status ='".STATUS_AML."'")
                       ->order('date_created ASC');

                       $results = $this->_db->fetchAll($details);
                       $reportsData = array();
                       foreach($results AS $data){
                           $select = $this->_db->select()
                                       ->from(DbTable::TABLE_AML_MASTER." AS a" , array('*'))
                                       ->where('a.first_name = "'. $data['first_name'] .'" AND a.second_name = "'. $data['last_name'].'"')
                                       ->Orwhere('a.full_name = ?', $data['first_name'].' '.$data['last_name']) 
                                       ->Orwhere('trim(concat(a.first_name," ",a.second_name))   = trim("'. $data['first_name'] .' '. $data['last_name'].'")') 
                                       ->Orwhere('a.fake_names LIKE ?','%'.$data['first_name'].' '.$data['last_name'].'%');

                               $row = $this->_db->fetchRow($select);

                               if($row['id']){
                                   $this->update(array('aml_status' => STATUS_IS_AML), 'id='.$data['id']);	
                               } else {
                                   $this->update(array('aml_status' => STATUS_AML_UPDATE), 'id='.$data['id']);	
                               }
                          }
        return $reportsData;
    }
    
    public function findById($id, $force = FALSE){
        if (!is_numeric($id)) {
            return array();
        }
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`kc`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`kc`.`crn`,'".$decryptionKey."') as crn");
        $select = $this->select();
        $select->from(DbTable::TABLE_KOTAK_CORP_CARDHOLDER ." as kc" , array(
            'id', 'product_id', 'kotak_customer_id', 'customer_master_id', 'customer_type', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'aadhaar_no', 'mobile', 'email', 'landline', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'mother_maiden_name', 'employer_name', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'by_corporate_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'nominee_name', 'nominee_relationship', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'date_toggle_kyc', 'status', 'status_bank', 'status_ops', 'status_ecs', 'aml_status'
        ));
        $column = $this->_extractTableAlias($select) . '.' . $this->_primary[1];
        $select->where($column . ' = ?', $id);
        return $this->fetchRow($select); 
    }
}
