<?php

/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */
class Corp_Boi_Customers extends Corp_Boi {

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
    protected $_name = DbTable::TABLE_BOI_CORP_CARDHOLDER;

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
    public function checkMemberId($memid) {
        $select = $this->select()
                ->where("member_id =?", $memid);
        $res = $this->fetchRow($select);

        $res = Util::toArray($res);

        if (empty($res)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function showPendingCustomerDetails($page = 1, $data = array(), $paginate = NULL, $force = FALSE) {
        $state = isset($data['state']) ? $data['state'] : '';
        $mobile = isset($data['mobile']) ? $data['mobile'] : '';
        $ref_num = isset($data['ref_num']) ? $data['ref_num'] : '';
        $itemsPerPage = isset($data['items_per_page']) ? $data['items_per_page'] : '';

        $pincode = isset($data['pincode']) ? $data['pincode'] : '';
        $dateApproval = isset($data['date_created']) ? $data['date_created'] : '';
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
        //Enable DB Slave
        $this->_enableDbSlave();
        
        $select = $this->select();//
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER, array('id', 'product_id', 'boi_customer_id', 'customer_master_id', 'customer_type', 'xls_boi_customer_id', 'nsdc_enrollment_no', 'sol_id', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'title', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'occupation', 'perm_country', 'aadhaar_no', 'pan', 'uid_no', 'mobile', 'email', 'landline', 'address_type', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country_code',
            'mother_maiden_name', 'employer_name', 'employer_address_line1', 'employer_address_line2', 'employer_address_city', 'employer_address_state', 'employer_address_country_id', 'employer_address_pincode', 'employer_contact_no', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'comm_country_code', 'nre_flag', 'nre_nationality', 'passport', 'passport_issue_date', 'passport_expiry_date', 'marital_status', 'cust_comm_code', 'other_bank_account_no', 'other_bank_account_type', 'other_bank_name', 'other_bank_branch', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'minor_flg', 'minor_guardian_code', 'minor_guardian_name', 'minor_guardian_address_line1', 'minor_guardian_address_line2', 'minor_guardian_city', 'minor_guardian_state', 'minor_guardian_pincode', 'minor_guardian_country_id', 'mode_of_operation', 'nomination_flg', 'nominee_name', 'nominee_relationship', 'nominee_add_line1', 'nominee_add_line2', 'nominee_city_cd', 'nominee_minor_guradian_cd', 'nominee_dob', 'nominee_minor_flag', 'amount_open', 'mode_of_payment_open', 'account_no', 'cust_id', 'sqlid', 'finacle_status', 'update_sql_status', 'staff_flg', 'staff_no', 'minor_title_guradian_code', 'passport_details', 'introducer_title_code', 'introducer_code', 'introducer_name', 'existing_cust_flg', 'account_currency_code', 'cust_id_ver_flg', 'account_id_ver_flg', 'schm_code', 'orgaization_type', 'introducer_flg', 'introducer_cust_id', 'cust_currency_code', 'account_type_id', 'boi_account_number', 'ref_num', 'training_partner_name', 'output_file_id', 'prev_output_file_ids', 'aml_status', 'traning_center_name', 'training_center_id', 'debit_mandate_amount', 'aof_ref_num', 'debit_mandate_accout', 'debit_mandate_account', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'boi_card_mapping_id', 'date_blocked', 'status', 'status_bank', 'status_ops', 'status_ecs', 'concat(first_name," ",last_name) as name'));
        $select->where("status_ops = '" . STATUS_PENDING . "'");
        if ($state != '') {
            $select->where("state = '" . $state . "'");
        }
        if ($pincode != '') {
            $select->where("pincode = '" . $pincode . "'");
        }
        if ($mobile != '') {
            $select->where("mobile LIKE '%" . $mobile . "%'");
        }
        if ($ref_num != '') {
            $select->where("ref_num = '" . $ref_num . "'");
        }
        if ($dateApproval != '') {
            $dateApproval = Util::returnDateFormatted($dateApproval, "d-m-Y", "Y-m-d", "-", "-", 'from');
            $select->where("date_created >= '" . $dateApproval . "'");
        }
        $select->order('date_created DESC'); 
        //Disable DB Slave
        $this->_disableDbSlave();
        return $this->_paginate($select, $page, $paginate, $itemsPerPage);
    }

    public function changeStatus($params) {
        $dataArr = array('status_ops' => $params['status'], 'date_approval' => new Zend_Db_Expr('NOW()'));
        $id = $params['id'];
        return $this->update($dataArr, "id = $id");
    }

    public function toggleStatus($params) {
         $dataArr = array('status_ops' => $params['status'],
                        'date_approval' => new Zend_Db_Expr('NOW()'),
                        'prev_output_file_ids' => $params['prev_output_file_ids'],
                        'output_file_id' => $params['output_file_id']
                        );
        $id = $params['id'];
        return $this->update($dataArr, "id = $id");
    }

        public function showBankPendingCustomerDetails($page = 1, $data, $paginate = NULL, $force = FALSE) {
        $cityModel = new CityList();
        $state = isset($data['state']) ? $data['state'] : '';

        $pincode = isset($data['pincode']) ? $data['pincode'] : '';
        $dateApproval = isset($data['date_approval']) ? $data['date_approval'] : '';
        $select = $this->select();
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER);
        $select->where("status_bank = '" . STATUS_PENDING . "'");
        $select->where("status_ops = '" . STATUS_APPROVED . "'");
        if ($state != '') {
            $state = $cityModel->getStateName($state);
            $select->where("state = '" . $state . "'");
        }
        if ($pincode != '') {
            $select->where("pincode = '" . $pincode . "'");
        }
        if ($dateApproval != '') {
            $dateApproval = Util::returnDateFormatted($dateApproval, "d-m-Y", "Y-m-d", "-", "-", 'from');
            $select->where("date_approval >= '" . $dateApproval . "'");
        }

        $select->order('id ASC');
        return $this->_paginate($select, $page, $paginate);
    }

    public function changeBankStatus($params) {
        if (isset($params['date_authorize']) && $params['date_authorize'] != '') {
            $dataArr = array('status_bank' => $params['status'], 'date_authorize' => $params['date_authorize']);
        } else {
            $dataArr = array('status_bank' => $params['status']);
        }
//       echo '<pre>';print_r($dataArr);exit('hghgh');
        $id = $params['id'];
        return $this->update($dataArr, "id = $id");
    }

    public function searchCustomer($param) {
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	$columnName = $param['searchCriteria'];
        $keyword = $param['keyword'];
	if($columnName == 'card_number'){
            $columnName = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."')");
        }
	$card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
	$whereString = "$columnName LIKE '%$keyword%' "; 
	$details = $this->_db->select()
                ->from(DbTable::TABLE_BOI_CORP_CARDHOLDER, array('id', $crn, 'customer_master_id', $card_number, 'afn', 'concat(first_name," ",last_name) as name', 'gender', 'date_of_birth', 'mobile', 'email', 'member_id', 'status', 'name_on_card', 'date_failed', 'failed_reason', 'status_ops', 'status_bank', 'status_ecs', 'card_pack_id'))
		->where($whereString)
		->order('first_name DESC');
	return $this->_db->fetchAll($details);
    }

    public function getApprovedCustomerForCRNUpdate($limit) {

        $details = $this->_db->select()
                ->from(DbTable::TABLE_BOI_CORP_CARDHOLDER, array('id', 'concat(first_name," ",last_name) as name', 'member_id', 'status', 'failed_reason'))
                ->where("status = ?", STATUS_PENDING)
                ->where("status_bank = ?", STATUS_APPROVED)
                ->where("status_ops = ?", STATUS_APPROVED)
                ->where("status_ecs = ?", STATUS_PENDING)
                ->order('date_created DESC');
        return $this->_db->fetchAll($details);
    }

    public function updateCRNforApprovedCustomer($limit = BOI_CRN_UPDATE_LIMIT) {
        $customerRs = $this->getApprovedCustomerForCRNUpdate($limit);
        $customerArr = Util::toArray($customerRs);
        if (!empty($customerArr)) {
            $cnt = 0;
            foreach ($customerArr as $customer) {
                if ($this->validateCRNForMemberId($customer)) {
                    $flg = $this->updateCRNRecordForCustomer($customer);
                    if ($flg) {
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
        if (!empty($rs)) {
            return TRUE;
        }
        return FALSE;
    }

    private function getCRNByMemberId($customer) {
        $crnMaster = new CRNMaster();
        return $crnMaster->getInfoByMemberId(array(
                    'status' => STATUS_FREE,
                    'member_id' => $customer['member_id'],
                    'product_id' => $this->getProductId(),
        ));
    }

    private function updateCRNRecordForCustomer($customer) {
        $crnMaster = new CRNMaster();
        $rs = $this->getCRNByMemberId($customer);
        if (!empty($rs)) {
            $crnMaster->updateStatusByMemberId(array(
                'status' => STATUS_USED,
                'member_id' => $customer['member_id'],
                'product_id' => $this->getProductId(),
            ));
            $updateArr = array(
                'card_number' => $rs['card_number'],
                'card_pack_id' => $rs['card_pack_id'],
                'status_ecs' => STATUS_WAITING,
                'date_crn_update' => new Zend_Db_Expr('NOW()')
            );
            $whereCon = ' id="' . $customer['id'] . '"';
            return $this->update($updateArr, $whereCon);
        }
        return FALSE;
    }

    public function getProductId() {
        $product = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NDSC);
        $productModel = new Products();
        $productInfo = $productModel->getProductInfoByUnicode($product->product->unicode);
        if (empty($productInfo) || !isset($productInfo->id)) {
            throw new App_Exception('Unable to fetch Product Id');
        }
        return $productInfo->id;
    }

    public function boiCorpECSRegn() {
        $custPurseModel = new Corp_Boi_CustomerPurse();
        $masterPurseModel = new MasterPurse();
        $deliveryFlag = new Corp_Boi_DeliveryFlag();
        $productModel = new Products();
        $cardMapping = new Corp_Boi_CardMapping();
        $custProductModel = new Corp_Boi_CustomerProduct();
        $customerTrackModel = new CustomerTrack();
        $m = new \App\Messaging\Corp\Boi\Operation();
        $user = Zend_Auth::getInstance()->getIdentity();


        $dataArr = $this->ecsPendingArray();
        $numCust = 0;
        foreach ($dataArr as $data) {

            $id = $data['id'];
            //$deliveryFileId = $data['delivery_file_id'];


            $msg = '';
            try {
                //if($data['delivery_status'] == STATUS_DELIVERED)
                {
                    $cardholderArray = $this->getCardholderArray($data);
                    $ecsApi = new App_Api_ECS_Corp_Boi();
                    $resp = $ecsApi->boiNSDCCardholderRegistration($cardholderArray);
                    if ($resp == false) {
                        $msg = $ecsApi->getError();
                        //$deliveryArr = array('date_ecs' => new Zend_Db_Expr('NOW()'), 'status' => STATUS_FAILURE, 'failed_reason' => $msg);
                    }
                }
            } catch (App_Exception $e) {
                $resp = false;
                $msg = $e->getMessage();
                //$deliveryArr = array('date_ecs' => new Zend_Db_Expr('NOW()'), 'status' => STATUS_FAILURE, 'failed_reason' => $msg);
                //$this->update($deliveryArr, "id = $id");
            } catch (Exception $e) {
                $resp = false;
                $msg = $e->getMessage();
                //$deliveryArr = array('date_ecs' => new Zend_Db_Expr('NOW()'), 'status' => STATUS_FAILURE, 'failed_reason' => $msg);
                //$deliveryFlag->update($deliveryArr, "id = $deliveryFileId");
            }
            if ($resp == true) {

                if ($data['boi_customer_id'] == 0) {
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
                    $ratCustomerId = $this->addBoiCustomerMaster($ratCustomerMasterData);
                    //insert into customer purse
                    $purseDetails = $masterPurseModel->getPurseDetailsbyBankIdProductId($data['product_id'], $productDetail['bank_id']);
                    foreach ($purseDetails as $purseDetail) {
                        $purseArr = array(
                            'customer_master_id' => $customerMasterId,
                            'boi_customer_id' => $ratCustomerId,
                            'product_id' => $data['product_id'],
                            'purse_master_id' => $purseDetail['id'],
                            'bank_id' => $productDetail['bank_id'],
                            'date_updated' => new Zend_Db_Expr('NOW()')
                        );
                        $purseParam = array('boi_customer_id' => $ratCustomerId, 'purse_master_id' => $purseDetail['id']);
                        $purseDetails1 = $custPurseModel->getCustPurseDetails($purseParam);
                        if (empty($purseDetails1)) { // If purse entry not found
                            $custPurseModel->save($purseArr);
                        }
                    }
                    // Get Customer product details
                    //Update customer product
                    $prodUpdateArr = array(
                        'boi_customer_id' => $ratCustomerId,
                    );
                    $custProductModel->updateCustProduct($prodUpdateArr, "product_customer_id = $id");

                    // update the status to STATUS_ACTIVE in cardholders
                    $updateArr = array('status' => STATUS_ACTIVE, 'status_ecs' => STATUS_SUCCESS,
                        'customer_master_id' => $customerMasterId,
                        'boi_customer_id' => $ratCustomerId,
                        'date_activation' => new Zend_Db_Expr('NOW()'),
                        'failed_reason' => '',
                            //'delivery_file_id' => $deliveryFileId
                    );
                    $this->_db->update(DbTable::TABLE_BOI_CORP_CARDHOLDER, $updateArr, "id= $id");
                } else {
                    // update the status to STATUS_ACTIVE in cardholders
                    $updateArr = array('status' => STATUS_ACTIVE, 'status_ecs' => STATUS_SUCCESS,
                        'date_activation' => new Zend_Db_Expr('NOW()'),
                        'failed_reason' => '',
                            //'delivery_file_id' => $deliveryFileId
                    );
                    $this->_db->update(DbTable::TABLE_BOI_CORP_CARDHOLDER, $updateArr, "id= $id");
                    $updateArr = array('status' => STATUS_ACTIVE
                    );
                    $this->_db->update(DbTable::TABLE_BOI_CUSTOMER_MASTER, $updateArr, "id= " . $data['boi_customer_id']);
                }
                //$deliveryArr = array('date_ecs' => new Zend_Db_Expr('NOW()'), 'status' => STATUS_SUCCESS, 'failed_reason' => '');
                //$deliveryFlag->update($deliveryArr, "id = $deliveryFileId");
                if (!empty($data['mobile'])) {
                    if(!isset($productDetail)) {
                           $productDetail = $productModel->getProductInfo($data['product_id']);
                    }
                    
                    $userData = array('last_four' => substr($data['card_number'], -4),
                        'product_name' => $productDetail['name'],
                        'mobile' => $data['mobile'],
                    );
                    $resp = $m->cardActivation($userData);
                }

                // Update mapping table

                $cardMappingUpdateArray = array(
                    'status' => STATUS_SUCCESS
                );
                $cardMapping->updateRecords($cardMappingUpdateArray, $data['boi_card_mapping_id']);
                // Insert into customer Track
                $customerTrackArr = array(
                    'card_number' => Util::insertCardCrn($data['card_number']),
                    'card_pack_id' => $data['card_pack_id'],
                    'member_id' => $data['member_id'],
                    'crn' => Util::insertCardCrn($data['card_number']),
                    'mobile' => $data['mobile'],
                    'email' => $data['email'],
                    'name_on_card' => $data['name_on_card'],
                );
                $customerTrackModel->customerDetails($customerTrackArr, $data['product_id'], $data['id']);
                // update Mapping
//                $updateArr = array('status' => STATUS_ACTIVE, 'status_ecs' => STATUS_SUCCESS, 
//                    'customer_master_id' => $customerMasterId, 
//                    
//                        );
//                $this->_db->update(DbTable::TABLE_BOI_CORP_CARDHOLDER, $updateArr, "id= $id");
            } else {
                //On Failure
                $updateArr = array('status' => STATUS_ECS_FAILED, 'status_ecs' => STATUS_FAILURE, 'failed_reason' => $msg,
                    'date_failed' => new Zend_Db_Expr('NOW()'));
                $this->update($updateArr, "id= $id");

                $updateprodArr = array('status' => STATUS_INACTIVE);
                $this->_db->update(DbTable::TABLE_BOI_CUSTOMER_PRODUCT, $updateprodArr, "product_customer_id= $id");

                // Update mapping table

                $cardMappingUpdateArray = array(
                    'status' => STATUS_FAILURE
                );
                $cardMapping->updateRecords($cardMappingUpdateArray, $data['boi_card_mapping_id']);

                //$deliveryArr = array('date_ecs' => new Zend_Db_Expr('NOW()'), 'status' => STATUS_FAILURE, 'failed_reason' => $msg);
                //$deliveryFlag->update($deliveryArr, "id = $deliveryFileId");
                // update Mapping
//                $updateArr = array('status' => STATUS_ACTIVE, 'status_ecs' => STATUS_SUCCESS, 
//                    'customer_master_id' => $customerMasterId, 
//                    
//                        );
//                $this->_db->update(DbTable::TABLE_BOI_CORP_CARDHOLDER, $updateArr, "id= $id");
            }

            $numCust++;
        }
        //$deliveryArr = array('status' => STATUS_FAILURE, 'failed_reason' => 'Unmatched Records');
        //$deliveryFlag->update($deliveryArr, "status = '".STATUS_PENDING."'");

        return $numCust;
    }

    public function getCardholderArray($param) {
        $ECSModel = new ECS();
        $state = new CityList();
        $dob = Util::returnDateFormatted($param['date_of_birth'], "Y-m-d", "d-m-Y", "-");
        //$cityCode = $state->getCityCode(ucfirst(strtolower($param['city'])));
        $cityCode = $param['city'];
        //if(!empty($param['unicode'])) {
        //$ECSModel->assignMediassistCRN($param['id']);
        //}

        //$cardholderDetails = $this->findById($param['id']);
        //$cardholder = Util::toArray($cardholderDetails);
        
        $paramArray['cardNumber'] = $param['card_number'];
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
        if (isset($param['gender']) && in_array(strtolower($param['gender']), array('male', 'female'))) {
            if (strtolower($param['gender']) == 'male')
                $param['gender'] = 'M';
            if (strtolower($param['gender']) == 'female')
                $param['gender'] = 'F';
        }

        $paramArray['gender'] = $param['gender'];

        return $paramArray;
    }

    public function addBoiCustomerMaster($data) {
        if (empty($data))
            throw new Exception('Data missing while adding customer details');

        $this->_db->insert(DbTable::TABLE_BOI_CUSTOMER_MASTER, $data);
        return $this->_db->lastInsertId();
    }

    public function ecsPendingArray() {

        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        
        $select = $this->_db->select();

        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as kc', array('id', 'date_of_birth', 'city', 'address_line1', 'address_line2', 'email', 'name_on_card', 'first_name', 'last_name', 'member_id', 'mother_maiden_name', 'mobile', 'pincode', 'gender', 'boi_customer_id', 'product_id', 'middle_name', 'id_proof_type', 'id_proof_number', 'boi_card_mapping_id', 'card_pack_id', $card_number));
        $select->where("kc.status_ecs = '" . STATUS_WAITING . "' OR kc.status_ecs = '" . STATUS_FAILURE . "'")
                ->where("kc.status_ops =?", STATUS_APPROVED)
                ->where("kc.status_bank =?", STATUS_APPROVED)
                ->where("kc.status = '" . STATUS_PENDING . "' OR kc.status = '" . STATUS_ECS_FAILED . "' OR kc.status = '" . STATUS_ACTIVATED . "'")
                ->order('kc.id')
                ->limit(BOI_CORP_ECS_REGN_LIMIT);
        return $this->_db->fetchAll($select);
    }

    public function customerIdDoclist($id_proof) {
        $retArr = array();
        $where = "d.id IN (" . $id_proof . ")";
        $select = $this->_db->select()
                ->from(DbTable::TABLE_DOCS . ' as d')
                ->where($where)
                ->where("d.status = '" . STATUS_ACTIVE . "' ");
        $retArr = $this->_db->fetchRow($select);
        return $retArr;
    }

    public function customerAddDoclist($address_proof) {
        $retArr = array();
        $where = "d.id IN (" . $address_proof . ")";
        $select = $this->_db->select()
                ->from(DbTable::TABLE_DOCS . ' as d')
                ->where($where)
                ->where("d.status = '" . STATUS_ACTIVE . "' ");
        $retArr = $this->_db->fetchRow($select);
        return $retArr;
    }

    public function customerProfileDoclist($profile) {
        $retArr = array();
        $where = "d.id IN (" . $profile . ")";
        $select = $this->_db->select()
                ->from(DbTable::TABLE_DOCS . ' as d')
                ->where($where)
                ->where("d.status = '" . STATUS_ACTIVE . "' ");
        $retArr = $this->_db->fetchRow($select);
        return $retArr;
    }

    public function getCardholderPurses($customer_id = 0, $page = 1) {
        //Enable DB Slave
        $this->_enableDbSlave();
        
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(DbTable::TABLE_BOI_CUSTOMER_PURSE . " as cp", array('amount'))
                ->joinLeft(DbTable::TABLE_PURSE_MASTER . ' as p', "p.id = cp.purse_master_id", array('name', 'description'))
                ->where('p.id = cp.purse_master_id')
                ->where('cp.boi_customer_id =?', $customer_id);
        
        //Disable DB Slave
        $this->_disableDbSlave();
       
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
	
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	$card_number = new Zend_Db_Expr("AES_DECRYPT(`rhc`.`card_number`,'".$decryptionKey."') as card_number");

        $select = $this->select();
$select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER . " as rhc", array('rhc.id as id', 'rhc.customer_master_id', $card_number, 'rhc.boi_customer_id',
            'rhc.member_id', 'rhc.employee_id',
            'concat(rhc.first_name," ",rhc.last_name) as cardholder_name', 'rhc.gender',
            'rhc.mobile', 'rhc.email', 'rhc.employer_name', 'rhc.status as cardholder_status',
            'rhc.afn', 'rhc.mobile', 'rhc.first_name', 'rhc.middle_name', 'rhc.last_name',
            'rhc.date_of_birth', 'rhc.batch_name', 'rhc.corporate_id', 'rhc.product_id', 'rhc.city',
            'rhc.status_bank', 'rhc.status_ops', 'rhc.status', 'rhc.account_no', 'rhc.customer_type'));
        $select->setIntegrityCheck(false);
        //$select->where("rhc.status = '".STATUS_ACTIVE."'");
        if ($mediAssistId != '') {
            $select->where("rhc.member_id = '" . $mediAssistId . "'");
        }
        if ($employerName != '') {
            $select->where("rhc.employer_name like '%" . $employerName . "%'");
        }
        if ($cardNumber != '') {
	    
	    $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	    $cardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')"); 
            
	    $select->where("rhc.card_number = ?", $cardNumber);
        }
        if ($mobile != '') {
            $select->where("rhc.mobile = '" . $mobile . "'");
        }
        if ($employeeId != '') {
            $select->where("rhc.employee_id = '" . $employeeId . "'");
        }
        if ($email != '') {
            $select->where("rhc.email = '" . $email . "'");
        }
        if ($aadhaarNo != '') {
            $select->where("rhc.aadhaar_no = '" . $aadhaarNo . "'");
        }
        if ($pan != '') {
            $select->where("rhc.pan = '" . $pan . "'");
        }
        if ($cardholderId != '') {
            $select->where("rhc.id = '" . $cardholderId . "'");
        }

        $select->order("cardholder_name");


        return $select;
    }

    public function showBankStatusDetails($page = 1, $params = array(), $paginate = NULL, $force = FALSE) {
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`c`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`c`.`crn`,'".$decryptionKey."') as crn");
        $select = $this->select();
//        $select->setIntegrityCheck(false);
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as c', array('id', 'product_id', 'boi_customer_id', 'customer_master_id', 'customer_type', 'xls_boi_customer_id', 'nsdc_enrollment_no', 'sol_id', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'title', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'occupation', 'perm_country', 'aadhaar_no', 'pan', 'uid_no', 'mobile', 'email', 'landline', 'address_type', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country_code', 'mother_maiden_name', 'employer_name', 'employer_address_line1', 'employer_address_line2', 'employer_address_city', 'employer_address_state', 'employer_address_country_id', 'employer_address_pincode', 'employer_contact_no', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'comm_country_code', 'nre_flag', 'nre_nationality', 'passport', 'passport_issue_date', 'passport_expiry_date', 'marital_status', 'cust_comm_code', 'other_bank_account_no', 'other_bank_account_type', 'other_bank_name', 'other_bank_branch', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'minor_flg', 'minor_guardian_code', 'minor_guardian_name', 'minor_guardian_address_line1', 'minor_guardian_address_line2', 'minor_guardian_city', 'minor_guardian_state', 'minor_guardian_pincode', 'minor_guardian_country_id', 'mode_of_operation', 'nomination_flg', 'nominee_name', 'nominee_relationship', 'nominee_add_line1', 'nominee_add_line2', 'nominee_city_cd', 'nominee_minor_guradian_cd', 'nominee_dob', 'nominee_minor_flag', 'amount_open', 'mode_of_payment_open', 'account_no', 'cust_id', 'sqlid', 'finacle_status', 'update_sql_status', 'staff_flg', 'staff_no', 'minor_title_guradian_code', 'passport_details', 'introducer_title_code', 'introducer_code', 'introducer_name', 'existing_cust_flg', 'account_currency_code', 'cust_id_ver_flg', 'account_id_ver_flg', 'schm_code', 'orgaization_type', 'introducer_flg', 'introducer_cust_id', 'cust_currency_code', 'account_type_id', 'boi_account_number', 'ref_num', 'training_partner_name', 'output_file_id', 'prev_output_file_ids', 'aml_status', 'traning_center_name', 'training_center_id', 'debit_mandate_amount', 'aof_ref_num', 'debit_mandate_accout', 'debit_mandate_account', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'boi_card_mapping_id', 'date_blocked', 'status', 'status_bank', 'status_ops', 'status_ecs', 'concat(first_name," ",last_name) as name'));
        $select->where("c.status_ops = '" . STATUS_APPROVED . "'");
        $select->where("c.afn = '" . $params['afn'] . "'");
        $select->where("c.date_approval >= '" . $params['from'] . "'");
        $select->where("c.date_approval <= '" . $params['to'] . "'");
        $select->order('id ASC');
        return $this->_paginate($select, $page, $paginate);
    }

    public function showOpsrejectedCustomerDetails($page = 1, $paginate = NULL, $force = FALSE) {
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`c`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`c`.`crn`,'".$decryptionKey."') as crn");
        $user = Zend_Auth::getInstance()->getIdentity();
        $select = $this->select(); 
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as c', array('id', 'product_id', 'boi_customer_id', 'customer_master_id', 'customer_type', 'xls_boi_customer_id', 'nsdc_enrollment_no', 'sol_id', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'title', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'occupation', 'perm_country', 'aadhaar_no', 'pan', 'uid_no', 'mobile', 'email', 'landline', 'address_type', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country_code', 'mother_maiden_name', 'employer_name', 'employer_address_line1', 'employer_address_line2', 'employer_address_city', 'employer_address_state', 'employer_address_country_id', 'employer_address_pincode', 'employer_contact_no', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'comm_country_code', 'nre_flag', 'nre_nationality', 'passport', 'passport_issue_date', 'passport_expiry_date', 'marital_status', 'cust_comm_code', 'other_bank_account_no', 'other_bank_account_type', 'other_bank_name', 'other_bank_branch', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'minor_flg', 'minor_guardian_code', 'minor_guardian_name', 'minor_guardian_address_line1', 'minor_guardian_address_line2', 'minor_guardian_city', 'minor_guardian_state', 'minor_guardian_pincode', 'minor_guardian_country_id', 'mode_of_operation', 'nomination_flg', 'nominee_name', 'nominee_relationship', 'nominee_add_line1', 'nominee_add_line2', 'nominee_city_cd', 'nominee_minor_guradian_cd', 'nominee_dob', 'nominee_minor_flag', 'amount_open', 'mode_of_payment_open', 'account_no', 'cust_id', 'sqlid', 'finacle_status', 'update_sql_status', 'staff_flg', 'staff_no', 'minor_title_guradian_code', 'passport_details', 'introducer_title_code', 'introducer_code', 'introducer_name', 'existing_cust_flg', 'account_currency_code', 'cust_id_ver_flg', 'account_id_ver_flg', 'schm_code', 'orgaization_type', 'introducer_flg', 'introducer_cust_id', 'cust_currency_code', 'account_type_id', 'boi_account_number', 'ref_num', 'training_partner_name', 'output_file_id', 'prev_output_file_ids', 'aml_status', 'traning_center_name', 'training_center_id', 'debit_mandate_amount', 'aof_ref_num', 'debit_mandate_accout', 'debit_mandate_account', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'boi_card_mapping_id', 'date_blocked', 'status', 'status_bank', 'status_ops', 'status_ecs'));
        $select->where("c.status_ops = '" . STATUS_REJECTED . "'");
        $select->where("c.by_agent_id = '" . $user->id . "'");
        $select->order('id ASC');
        return $this->_paginate($select, $page, $paginate);
    }

    public function acceptPhysicalDocList($page = 1, $data, $paginate = NULL, $force = FALSE) {
        $cityModel = new CityList();
        $state = isset($data['state']) ? $data['state'] : '';

        $pincode = isset($data['pincode']) ? $data['pincode'] : '';
        $dateAuthorize = isset($data['date_authorize']) ? $data['date_authorize'] : '';
        $select = $this->select();
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER);
        $select->where("status_bank = '" . STATUS_APPROVED . "'");
        $select->where("recd_doc = '" . FLAG_NO . "'");
        if ($state != '') {
            $state = $cityModel->getStateName($state);
            $select->where("state = '" . $state . "'");
        }
        if ($pincode != '') {
            $select->where("pincode = '" . $pincode . "'");
        }
        if ($dateAuthorize != '') {
            $dateAuthorize = Util::returnDateFormatted($dateAuthorize, "d-m-Y", "Y-m-d", "-", "-", 'from');
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
                $params = array('recd_doc' => FLAG_YES, 'date_recd_doc' => $date, 'recd_doc_id' => $user->id);

                $this->update($params, "id = $id");
            }// END of foreach loop
        } catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            throw new Exception($e->getMessage());
        }
        return TRUE;
    }

    public function showApplicationDetails($data) {
        $status = isset($data['bank_status']) ? $data['bank_status'] : '';

        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as c');

        if ($status == 'all') {
            $select->where("c.status_bank = '" . STATUS_APPROVED . "' AND c.status_bank = '" . STATUS_REJECTED . "' ");
        } else {
            $select->where("c.status_bank = '" . $status . "'");
        }
        $select->where("date_authorize >= '" . $data['from'] . "'");
        $select->where("date_authorize <= '" . $data['to'] . "'");
        $select->order('id ASC');
        return $this->_db->fetchAll($select);
    }

    public function applicationApprovedCount($data) {
        $agentId = isset($data['agent_id']) ? $data['agent_id'] : '';
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as c', array('count(id) as count'));
        $select->where("c.status_bank = '" . STATUS_APPROVED . "'");
        $select->where("c.date_authorize >= '" . $data['from'] . "'");
        $select->where("c.date_authorize <= '" . $data['to'] . "'");
        if ($agentId != '') {
            $select->where("c.by_agent_id IN ( '" . $agentId . "')");
        }
        return $this->_db->fetchRow($select);
    }
    
    
    public function applicationApprovedTotalCount($data) {
        $agentId = isset($data['agent_id']) ? $data['agent_id'] : '';
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as c', array('count(id) as count'));
        //$select->where("c.status_bank = '" . STATUS_APPROVED . "'");
        //$select->where("c.date_authorize >= '" . $data['from'] . "'");
        //$select->where("c.date_authorize <= '" . $data['to'] . "'");
        if ($agentId != '') {
            $select->where("c.by_agent_id IN ( '" . $agentId . "')");
        }
        
        return $this->_db->fetchRow($select);
    }

    public function physicalAppAcceptedCount($data) {
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as c', array('count(id) as count'));
//        $select->where("c.status_bank = '" . STATUS_APPROVED . "'");
        $select->where("c.recd_doc = '" . FLAG_YES . "'");
        $select->where("date_recd_doc >= '" . $data['from'] . "'");
        $select->where("date_recd_doc <= '" . $data['to'] . "'");
        return $this->_db->fetchRow($select);
    }

    public function applicationPendingCount() {
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as c', array('count(id) as count'));
        $select->where("c.status_ops = '" . STATUS_APPROVED . "'");
        $select->where("c.status_bank = '" . STATUS_PENDING . "'");
        return $this->_db->fetchRow($select);
    }

    public function pendingPhysical() {
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as c', array('count(id) as count'));
        $select->where("c.status_bank = '" . STATUS_APPROVED . "'");
        $select->where("c.recd_doc = '" . FLAG_NO . "'");
        return $this->_db->fetchRow($select);
    }

    public function getCardholders($param) {
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`c`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`c`.`crn`,'".$decryptionKey."') as crn");
        $user = Zend_Auth::getInstance()->getIdentity();
        $agentModel = new Agents();
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $product = isset($param['product_id']) && $param['product_id'] > 0 ? $param['product_id'] : '';
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $refnum = isset($param['ref_num']) ? $param['ref_num'] : '';
        $ifscCode = isset($param['ifsc_code']) ? $param['ifsc_code'] : '';
        $enrollmentno = isset($param['nsdc_enrollment_no']) ? $param['nsdc_enrollment_no'] : '';
        $status = isset($param['status']) ? $param['status'] : '';
        $checkerDate = isset($param['date_approval']) ? $param['date_approval'] : '';
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as c', array('id', 'product_id', 'boi_customer_id', 'customer_master_id', 'customer_type', 'xls_boi_customer_id', 'nsdc_enrollment_no', 'sol_id', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'title', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'occupation', 'perm_country', 'aadhaar_no', 'pan', 'uid_no', 'mobile', 'email', 'landline', 'address_type', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country_code', 'mother_maiden_name', 'employer_name', 'employer_address_line1', 'employer_address_line2', 'employer_address_city', 'employer_address_state', 'employer_address_country_id', 'employer_address_pincode', 'employer_contact_no', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'comm_country_code', 'nre_flag', 'nre_nationality', 'passport', 'passport_issue_date', 'passport_expiry_date', 'marital_status', 'cust_comm_code', 'other_bank_account_no', 'other_bank_account_type', 'other_bank_name', 'other_bank_branch', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'minor_flg', 'minor_guardian_code', 'minor_guardian_name', 'minor_guardian_address_line1', 'minor_guardian_address_line2', 'minor_guardian_city', 'minor_guardian_state', 'minor_guardian_pincode', 'minor_guardian_country_id', 'mode_of_operation', 'nomination_flg', 'nominee_name', 'nominee_relationship', 'nominee_add_line1', 'nominee_add_line2', 'nominee_city_cd', 'nominee_minor_guradian_cd', 'nominee_dob', 'nominee_minor_flag', 'amount_open', 'mode_of_payment_open', 'account_no', 'cust_id', 'sqlid', 'finacle_status', 'update_sql_status', 'staff_flg', 'staff_no', 'minor_title_guradian_code', 'passport_details', 'introducer_title_code', 'introducer_code', 'introducer_name', 'existing_cust_flg', 'account_currency_code', 'cust_id_ver_flg', 'account_id_ver_flg', 'schm_code', 'orgaization_type', 'introducer_flg', 'introducer_cust_id', 'cust_currency_code', 'account_type_id', 'boi_account_number', 'ref_num', 'training_partner_name', 'output_file_id', 'prev_output_file_ids', 'aml_status', 'traning_center_name', 'training_center_id', 'debit_mandate_amount', 'aof_ref_num', 'debit_mandate_accout', 'debit_mandate_account', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'boi_card_mapping_id', 'date_blocked', 'status', 'status_bank', 'status_ops', 'status_ecs', 'concat(c.first_name," ",c.last_name) as name','account_no as ifsc_code'));
        $select->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "c.product_id  = p.id",array('p.name as product_name'));        
        if(empty($agentId)){
            $select->join(DbTable::TABLE_AGENTS . " as ag", "c.by_agent_id = ag.id ", array('concat(ag.first_name," ",ag.last_name) as by_agent_name'));
            $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION . " as re", "ag.id = re.to_object_id AND object_relation_type_id = 2", array('re.to_object_id'));
            $select->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "ad.agent_id = re.from_object_id AND ad.status = '".STATUS_ACTIVE."'", array('ad.branch_id'));
        }else{
        if ($agentId == 'all') {

            $bclist = $agentModel->getBCListing(array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $user->id, 'user_type' => $user->user_type));
            $select->join(DbTable::TABLE_AGENTS . " as ag", "c.by_agent_id = ag.id AND ag.id IN( $bclist)", array('concat(ag.first_name," ",ag.last_name) as by_agent_name'));
            $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION . " as re", "ag.id = re.to_object_id AND object_relation_type_id = 2", array('re.to_object_id'));
            $select->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "ad.agent_id = re.from_object_id AND ad.status = '".STATUS_ACTIVE."'", array('ad.branch_id'));
            
        } else {
            $select->join(DbTable::TABLE_AGENTS . " as ag", "c.by_agent_id = ag.id and c.by_agent_id = $agentId", array('concat(ag.first_name," ",ag.last_name) as by_agent_name'));
            $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION . " as re", "ag.id = re.to_object_id AND object_relation_type_id = 2", array('re.to_object_id'));
            $select->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "ad.agent_id = re.from_object_id AND ad.status = '".STATUS_ACTIVE."'", array('ad.branch_id'));
        }
        }
        if ($ifscCode != '') {
            $select->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "c.by_agent_id = ad.agent_id AND ad.bank_ifsc_code = '".$ifscCode."' AND ad.status = '".STATUS_ACTIVE."'");
        }
        if ($product != '') {
            $select->where("c.product_id = '" . $product . "'");
        }
        if ($refnum != '') {
            $select->where("c.ref_num = '" . $refnum . "'");
        }
        if ($enrollmentno != '') {
            $select->where("c.nsdc_enrollment_no = '" . $enrollmentno . "'");
        }
    
        if ($checkerDate != '') {
            $select->where("DATE(c.date_approval) = '" . $checkerDate . "'");
        }
        if ($status != '') {
            if(CURRENT_MODULE == MODULE_AGENT)
            {
                switch ($status) {
                    case STATUS_PENDING:
                        $qry = $select->where("(c.status_ops = '" . STATUS_APPROVED . "' AND c.status_bank = '" . $status . "') OR c.status_ops = '" . $status . "'");
                        break;
                    case STATUS_APPROVED:
                        $qry = $select->where("c.status_bank = '" . $status . "' ");
                        break;
                    case STATUS_REJECTED:
                        $qry = $select->where("c.status_bank = '" . $status . "' OR c.status_ops = '" . $status . "' ");
                        break;
                    case STATUS_CARD_ISSUED:
                        $qry = $select->where("c.status = '" . STATUS_ACTIVE . "'");
                        break;
                }
            } else {
                switch ($status) {
                    case STATUS_PENDING:
                        $qry = $select->where("(c.status_ops = '" . STATUS_APPROVED . "' AND c.status_bank = '" . $status . "') OR c.status_ops = '" . $status . "'");
                        break;
                    case STATUS_APPROVED:
                        $qry = $select->where("c.status_bank = '" . $status . "' AND c.status_ecs = '" . STATUS_PENDING . "'");
                        break;
                    case STATUS_REJECTED:
                        $qry = $select->where("c.status_bank = '" . $status . "' OR c.status_ops = '" . $status . "' ");
                        break;
                    case STATUS_CARD_ISSUED:
                        $qry = $select->where("c.status = '" . STATUS_ACTIVE . "'");
                        break;
                }
                
            }
        }
        if ($from != '' && $to != '') {
            $select->where("c.date_created >= '" . $param['from'] . "'");
            $select->where("c.date_created <= '" . $param['to'] . "'");
        }

        $select->order("c.first_name");
        //echo $select; exit; 
        return $this->fetchAll($select);
    }

    /* exportAgentFundRequests function will find data for Agent fund requests report. 
     * it will accept param array with query filters e.g.. duration
     */

    public function exportgetCardholders($param) {

        $data = $this->getCardholders($param);
        $user = Zend_Auth::getInstance()->getIdentity();
        $retData = array();

        if (!empty($data)) {
            foreach ($data as $key => $data) {
                $retData[$key]['product_name'] = $data['product_name'];
                $retData[$key]['ref_num'] = $data['ref_num'];
                $retData[$key]['name'] = $data['name'];
                $retData[$key]['aadhaar_no'] = $data['aadhaar_no'];
                $retData[$key]['nsdc_enrollment_no'] = $data['nsdc_enrollment_no'];
                $retData[$key]['sol_id'] = $data['sol_id'];
                $retData[$key]['status_ops'] = $data['status_ops'];
                $retData[$key]['status_bank'] = $data['status_bank'];
                $retData[$key]['account_no'] = ($user->user_type == DISTRIBUTOR_AGENT) ? $data['account_no'] : Util::maskCard($data['account_no'], 4, 0);
                $retData[$key]['branch_id'] = ($data['account_no'] == '') ? '' : "BKID000".substr($data['account_no'], 0, 4);
                $retData[$key]['card_number'] = Util::maskCard($data['card_number']);
                $retData[$key]['by_agent_name'] = $data['by_agent_name'];
                $retData[$key]['debit_mandate_amount'] = $data['debit_mandate_amount'];
                $retData[$key]['training_center_id'] = $data['training_center_id'];
                $retData[$key]['traning_center_name'] = $data['traning_center_name'];
                $retData[$key]['training_partner_name'] = $data['training_partner_name'];
                $retData[$key]['date_created'] = $data['date_created'];
                
            }
        }

        return $retData;
    }

    
    public function exportgetCardholdersOps($param) {

        $data = $this->getCardholders($param);

        $retData = array();

        if (!empty($data)) {
            foreach ($data as $key => $data) {
                $retData[$key]['ref_num'] = $data['ref_num'];
                $retData[$key]['name'] = $data['name'];  
                $retData[$key]['aadhaar_no'] = $data['aadhaar_no']; 
                $retData[$key]['nsdc_enrollment_no'] = $data['nsdc_enrollment_no'];
                $retData[$key]['status_ops'] = $data['status_ops'];
                $retData[$key]['status_bank'] = $data['status_bank'];
                $retData[$key]['ifsc_code'] =  ($data['account_no'] == '') ? '' : "BKID000".substr($data['account_no'], 0, 4);
                $retData[$key]['account_no'] = Util::maskCard($data['account_no'], 4);
                $retData[$key]['card_number'] = Util::maskCard($data['card_number'], 4);
                $retData[$key]['by_agent_name'] = $data['by_agent_name'];
                $retData[$key]['debit_mandate_amount'] = $data['debit_mandate_amount'];
                $retData[$key]['training_center_id'] = $data['training_center_id'];
                $retData[$key]['traning_center_name'] = $data['traning_center_name'];
                $retData[$key]['training_partner_name'] = $data['training_partner_name'];
                $retData[$key]['date_created'] = $data['date_created'];
                $retData[$key]['date_approval'] = $data['date_approval'];
            }
        }

        return $retData;
    }
    public function blockCard($params) {
        $dataArr = array('status' => STATUS_BLOCKED, 'date_blocked' => new Zend_Db_Expr('NOW()'));
        $cardNumber = $params['card_number'];
        return $this->update($dataArr, "card_number ='$cardNumber' AND status='" . STATUS_ACTIVE . "'");
    }

    public function getRegisteredCardholders($param) {
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
        
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $product = isset($param['product_id']) && $param['product_id'] > 0? $param['product_id'] : '';

        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as c', array('id', 'product_id', 'boi_customer_id', 'customer_master_id', 'customer_type', 'xls_boi_customer_id', 'nsdc_enrollment_no', 'sol_id', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'title', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'occupation', 'perm_country', 'aadhaar_no', 'pan', 'uid_no', 'mobile', 'email', 'landline', 'address_type', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country_code',
            'mother_maiden_name', 'employer_name', 'employer_address_line1', 'employer_address_line2', 'employer_address_city', 'employer_address_state', 'employer_address_country_id', 'employer_address_pincode', 'employer_contact_no', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'comm_country_code', 'nre_flag', 'nre_nationality', 'passport', 'passport_issue_date', 'passport_expiry_date', 'marital_status', 'cust_comm_code', 'other_bank_account_no', 'other_bank_account_type', 'other_bank_name', 'other_bank_branch', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'minor_flg', 'minor_guardian_code', 'minor_guardian_name', 'minor_guardian_address_line1', 'minor_guardian_address_line2', 'minor_guardian_city', 'minor_guardian_state', 'minor_guardian_pincode', 'minor_guardian_country_id', 'mode_of_operation', 'nomination_flg', 'nominee_name', 'nominee_relationship', 'nominee_add_line1', 'nominee_add_line2', 'nominee_city_cd', 'nominee_minor_guradian_cd', 'nominee_dob', 'nominee_minor_flag', 'amount_open', 'mode_of_payment_open', 'account_no', 'cust_id', 'sqlid', 'finacle_status', 'update_sql_status', 'staff_flg', 'staff_no', 'minor_title_guradian_code', 'passport_details', 'introducer_title_code', 'introducer_code', 'introducer_name', 'existing_cust_flg', 'account_currency_code', 'cust_id_ver_flg', 'account_id_ver_flg', 'schm_code', 'orgaization_type', 'introducer_flg', 'introducer_cust_id', 'cust_currency_code', 'account_type_id', 'boi_account_number', 'ref_num', 'training_partner_name', 'output_file_id', 'prev_output_file_ids', 'aml_status', 'traning_center_name', 'training_center_id', 'debit_mandate_amount', 'aof_ref_num', 'debit_mandate_accout', 'debit_mandate_account', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'boi_card_mapping_id', 'date_blocked', 'status', 'status_bank', 'status_ops', 'status_ecs', 'concat(c.first_name," ",c.last_name) as name'));
        $select->joinLeft(DbTable::TABLE_AGENTS . " as ag", "c.by_agent_id = ag.id", array('concat(ag.first_name," ",ag.last_name) as by_agent_name'));
        $select->joinLeft(DbTable::TABLE_PRODUCTS . " as p", "c.product_id  = p.id",array('p.name as product_name'));
        if ($product != '') {
            $select->where("c.product_id = '" . $product . "'");
        }
        $select->where("c.status = '" . STATUS_ACTIVE . "'");
        if ($from != '' && $to != '') {
            $select->where("c.date_created >= '" . $param['from'] . "'");
            $select->where("c.date_created <= '" . $param['to'] . "'");
        }
        $select->order("c.first_name");
        return $this->fetchAll($select);
    }

    public function exportRegisteredCardholders($param) {

        $data = $this->getRegisteredCardholders($param);
        $rctModel = new RctMaster();
        $retData = array();

        if (!empty($data)) {
            foreach ($data as $key => $data) {

                $retData[$key]['product_name'] = $data['product_name'];
                $retData[$key]['nsdc_enrollment_no'] = $data['nsdc_enrollment_no'];
                $retData[$key]['ref_num'] = $data['ref_num'];
                $retData[$key]['card_number'] = UTIL :: maskCard($data['card_number']);
                $retData[$key]['first_name'] = $data['first_name'];
                $retData[$key]['middle_name'] = $data['last_name'];
                $retData[$key]['last_name'] = $data['last_name'];
                $retData[$key]['name_on_card'] = $data['name_on_card'];
                $retData[$key]['gender'] = $data['gender'];
                $retData[$key]['date_of_birth'] = $data['date_of_birth'];
                $retData[$key]['mobile'] = $data['mobile'];
                $retData[$key]['email'] = $data['email'];
                $retData[$key]['landline'] = $data['landline'];
                $retData[$key]['address_line1'] = $data['address_line1'];
                $retData[$key]['address_line2'] = $data['address_line2'];
                $retData[$key]['city'] = $rctModel->getCityName($data['city']);
                $retData[$key]['state'] = $rctModel->getStateName($data['state']);
                $retData[$key]['pincode'] = $data['pincode'];
                $retData[$key]['mother_maiden_name'] = $data['mother_maiden_name'];
                $retData[$key]['employer_name'] = $data['employer_name'];
                $retData[$key]['corporate_id'] = $data['corporate_id'];
                $retData[$key]['comm_address_line1'] = $data['comm_address_line1'];
                $retData[$key]['comm_address_line2'] = $data['comm_address_line2'];
                $retData[$key]['comm_city'] = $rctModel->getCityName($data['comm_city']);
                $retData[$key]['comm_state'] = $rctModel->getStateName($data['comm_state']);
                $retData[$key]['comm_pin'] = $data['comm_pin'];
                $retData[$key]['date_created'] = $data['date_created'];
                $retData[$key]['status'] = $data['status'];
                $retData[$key]['date_failed'] = $data['date_failed'];
                $retData[$key]['failed_reason'] = $data['failed_reason'];
            }
        }

        return $retData;
    }
      

    public function boiAccountActivation() {

        $deliveryFlag = new Corp_Boi_DeliveryFlag();
        $productModel = new Products();
        $custProductModel = new Corp_Boi_CustomerProduct();
        $m = new \App\Messaging\Corp\Boi\Operation();
        $user = Zend_Auth::getInstance()->getIdentity();
        $addLen = 45;
        $pinLen = 6;

        $pendingRecords = $deliveryFlag->getPendingRecords(BOI_ACC_ACT);
        $numCust = 0;
        foreach ($pendingRecords as $dataFile) {
            $valid = $this->isValid($dataFile);
            
            if (!$valid) {
                $errMsg = $this->getError();
                $errorMsg = empty($errMsg) ? 'details invalid' : $errMsg;
                $updateData = array();
                $updateData = array('failed_reason' => $errorMsg, 'status' => STATUS_FAILURE, 'id' => $dataFile['id']);
                $deliveryFlag->updateRecord($updateData);
            } else {
                $dataCardholder = $this->getCardholderByRefNum(array('ref_num' => $dataFile['ref_num']));
                if (!empty($dataCardholder)) {

                    if ($dataCardholder['status_ops'] == STATUS_PENDING || $dataCardholder['status_ops'] == STATUS_REJECTED) {
                        $updateData = array();
                        $updateData = array('failed_reason' => 'Ops rejected/ Pending', 'status' => STATUS_FAILURE, 'id' => $dataFile['id']);
                        $deliveryFlag->updateRecord($updateData);
                    } else if ($dataCardholder['status_ops'] == STATUS_APPROVED) {
                        if ($dataCardholder['status_bank'] == STATUS_APPROVED) {
                            $updateData = array();
                            $updateData = array('failed_reason' => 'Duplicate', 'status' => STATUS_FAILURE, 'id' => $dataFile['id']);
                            $deliveryFlag->updateRecord($updateData);
                        } else {
                            if ($dataFile['delivery_status'] == STATUS_REJECTED) {
                                $dataArr = array();
                                $dataArr = array('status_bank' => STATUS_REJECTED, 'delivery_file_id' => $dataFile['id']);
                                $this->update($dataArr, "id = '" . $dataCardholder['id'] . "'");
                                $updateData = array('status' => STATUS_SUCCESS, 'id' => $dataFile['id']);
                                $deliveryFlag->updateRecord($updateData);
                            } else {
                                $dataArr = array();


                                $dataArr['status_bank'] = STATUS_APPROVED;
                                $dataArr['status'] = STATUS_ACTIVATED;
                                $dataArr['delivery_file_id'] = $dataFile['id'];
                                $dataArr['member_id'] = $dataFile['account_no'];

                                $dataArr['sol_id'] = $dataFile['sol_id'];
                                $dataArr['title'] = $dataFile['title'];
                                $dataArr['name'] = $dataFile['name'];
                                $dataArr['occupation'] = $dataFile['occupation'];
                                $dataArr['gender'] = Util::getGenderTxt($dataFile['gender']);
                                $dataArr['date_of_birth'] = Util::returnDateFormatted($dataFile['date_of_birth'], "d-m-Y", "Y-m-d");
                                $dataArr['address_type'] = $dataFile['address_type'];
                                $dataArr['address_line1'] = (strlen($dataFile['address_line1']) > $addLen) ? substr($dataFile['address_line1'],0,$addLen):$dataFile['address_line1'];
                                $dataArr['address_line2'] = (strlen($dataFile['address_line2']) > $addLen) ? substr($dataFile['address_line2'],0,$addLen):$dataFile['address_line2'];
                                $dataArr['city'] = $dataFile['city'];
                                $dataArr['state'] = $dataFile['state'];
                                $dataArr['country_code'] = $dataFile['country_code'];
                                $dataArr['pincode'] = $dataFile['pincode'];
                                $dataArr['comm_address_line1'] = (strlen($dataFile['comm_address_line1']) > $addLen) ? substr($dataFile['comm_address_line1'],0,$addLen):$dataFile['comm_address_line1'];
                                $dataArr['comm_address_line2'] = (strlen($dataFile['comm_address_line2']) > $addLen) ? substr($dataFile['comm_address_line2'],0,$addLen):$dataFile['comm_address_line2'];
                                $dataArr['comm_city'] = (strlen($dataFile['comm_city']) > 5) ? substr($dataFile['comm_city'],0,5):$dataFile['comm_city'];
                                $dataArr['comm_state'] = $dataFile['comm_state'];
                                $dataArr['comm_country_code'] = $dataFile['comm_country_code'];
                                $dataArr['comm_pin'] = (strlen($dataFile['comm_pin']) > $pinLen) ? substr($dataFile['comm_pin'],0,$pinLen):$dataFile['comm_pin'];
                                $dataArr['landline'] = $dataFile['landline'];
                                $dataArr['mobile'] = strtolower($dataFile['mobile']) == 'mna'? '':$dataFile['mobile'];
                                $dataArr['email'] = strtolower($dataFile['email']) == 'na'? '':$dataFile['email'];
                                $dataArr['pan'] = $dataFile['pan'];
                                $dataArr['uid_no'] = $dataFile['uid_no'];
                                $dataArr['nre_flag'] = $dataFile['nre_flag'];
                                $dataArr['nre_nationality'] = $dataFile['nre_nationality'];
                                $dataArr['passport'] = $dataFile['passport'];
                                $dataArr['passport_issue_date'] = Util::returnDateFormatted($dataFile['passport_issue_date'], "d-m-Y", "Y-m-d");
                                $dataArr['passport_expiry_date'] = Util::returnDateFormatted($dataFile['passport_expiry_date'], "d-m-Y", "Y-m-d");
                                $dataArr['marital_status'] = $dataFile['marital_status'];
                                $dataArr['cust_comm_code'] = $dataFile['cust_comm_code'];
                                $dataArr['other_bank_account_no'] = $dataFile['other_bank_account_no'];
                                $dataArr['other_bank_account_type'] = $dataFile['other_bank_account_type'];
                                $dataArr['other_bank_name'] = $dataFile['other_bank_name'];
                                $dataArr['other_bank_branch'] = $dataFile['other_bank_branch'];
                                $dataArr['employer_name'] = $dataFile['employer_name'];
                                $dataArr['employer_address_line1'] = $dataFile['employer_address_line1'];
                                $dataArr['employer_address_line2'] = $dataFile['employer_address_line2'];
                                $dataArr['employer_address_city'] = $dataFile['employer_address_city'];
                                $dataArr['employer_address_state'] = $dataFile['employer_address_state'];
                                $dataArr['employer_address_country_code'] = $dataFile['employer_address_country_code'];
                                $dataArr['employer_address_pincode'] = $dataFile['employer_address_pincode'];
                                $dataArr['employer_contact_no'] = $dataFile['employer_contact_no'];
                                $dataArr['minor_flg'] = $dataFile['minor_flg'];
                                $dataArr['minor_guardian_code'] = $dataFile['minor_guardian_code'];
                                $dataArr['minor_guardian_name'] = $dataFile['minor_guardian_name'];
                                $dataArr['minor_guardian_address_line1'] = $dataFile['minor_guardian_address_line1'];
                                $dataArr['minor_guardian_address_line2'] = $dataFile['minor_guardian_address_line2'];
                                $dataArr['minor_guardian_city'] = $dataFile['minor_guardian_city'];
                                $dataArr['minor_guardian_state'] = $dataFile['minor_guardian_state'];
                                $dataArr['minor_guardian_pincode'] = $dataFile['minor_guardian_pincode'];
                                $dataArr['minor_guardian_country_code'] = $dataFile['minor_guardian_country_code'];
                                $dataArr['mode_of_operation'] = $dataFile['mode_of_operation'];
                                $dataArr['nomination_flg'] = $dataFile['nomination_flg'];
                                $dataArr['nominee_name'] = $dataFile['nominee_name'];
                                $dataArr['nominee_add_line1'] = $dataFile['nominee_add_line1'];
                                $dataArr['nominee_add_line2'] = $dataFile['nominee_add_line2'];
                                $dataArr['nominee_relationship'] = $dataFile['nominee_relationship'];
                                $dataArr['nominee_city_cd'] = (strlen($dataFile['nominee_city_cd']) > 5) ? substr($dataFile['nominee_city_cd'],0,5):$dataFile['nominee_city_cd'];
                                $dataArr['nominee_minor_guradian_cd'] = $dataFile['nominee_minor_guradian_cd'];
                                $dataArr['nominee_dob'] = $dataFile['nominee_dob'];
                                $dataArr['amount_open'] = $dataFile['amount_open'];
                                $dataArr['mode_of_payment_open'] = $dataFile['mode_of_payment_open'];
                                $dataArr['account_no'] = $dataFile['account_no'];
                                $dataArr['member_id'] = $dataFile['member_id'];
                                $dataArr['cust_id'] = $dataFile['cust_id'];
                                $dataArr['sqlid'] = $dataFile['sqlid'];
                                $dataArr['finacle_status'] = $dataFile['finacle_status'];
                                $dataArr['update_sql_status'] = $dataFile['update_sql_status'];
                                $dataArr['staff_flg'] = $dataFile['staff_flg'];
                                $dataArr['staff_no'] = $dataFile['staff_no'];
                                $dataArr['minor_title_guradian_code'] = $dataFile['minor_title_guradian_code'];
                                $dataArr['passport_details'] = $dataFile['passport_details'];
                                $dataArr['introducer_title_code'] = $dataFile['introducer_title_code'];
                                $dataArr['introducer_name'] = $dataFile['introducer_name'];
                                $dataArr['existing_cust_flg'] = $dataFile['existing_cust_flg'];
                                $dataArr['account_currency_code'] = $dataFile['account_currency_code'];
                                $dataArr['cust_id_ver_flg'] = $dataFile['cust_id_ver_flg'];
                                $dataArr['account_id_ver_flg'] = $dataFile['account_id_ver_flg'];
                                $dataArr['schm_code'] = $dataFile['schm_code'];
                                $dataArr['orgaization_type'] = $dataFile['orgaization_type'];
                                $dataArr['introducer_flg'] = $dataFile['introducer_flg'];
                                $dataArr['introducer_cust_id'] = $dataFile['introducer_cust_id'];
                                $dataArr['cust_currency_code'] = $dataFile['cust_currency_code'];
                                $dataArr['account_type_id'] = $dataFile['account_type_id'];
                                $dataArr['debit_mandate_amount'] = (isset($dataFile['debit_mandate_amount'])) ? $dataFile['debit_mandate_amount'] : '';
                                $dataArr['date_authorize'] = new Zend_Db_Expr('NOW()');
                                $this->update($dataArr, "id = '" . $dataCardholder['id'] . "'");

                                $updateData = array('status' => STATUS_SUCCESS, 'id' => $dataFile['id']);
                                $deliveryFlag->updateRecord($updateData);
                                // send sms
                                if (!empty($dataFile['mobile']) && $dataFile['mobile'] != 'MNA') {
                                    $userData = array(
                                        'name' => $dataFile['name'],
                                        'mobile' => $dataFile['mobile'],
                                        'boi_account_number' => $dataFile['boi_account_number'],
                                    );
                                    $resp = $m->accountActivation($userData);
                                }
                                $numCust++;
                            }
                        }
                    }
                } else {
                    $updateData = array('failed_reason' => 'Record not found', 'status' => STATUS_FAILURE, 'id' => $dataFile['id']);
                    $deliveryFlag->updateRecord($updateData);
                }
            }
        }

        return $numCust;
    }

    public function getCardholderByRefNum($param) {

        $ref_num = isset($param['ref_num']) ? $param['ref_num'] : '';

        $select = $this->select();
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as c', array('*', 'concat(c.first_name," ",c.last_name) as name'));
        $select->where("ref_num = '" . $ref_num . "'");
        return $this->fetchRow($select);
    }

    public function getCardholderByById($param) {

        //$ref_num = isset($param['ref_num']) ? $param['ref_num'] : '';

        $select = $this->select();
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as c', array('*', 'concat(c.first_name," ",c.last_name) as name'));
        if (isset($param['id']) && !empty($param['id'])) {
            $select->where("id = ?", $param['id']);
        }
        if (isset($param['account_no']) && !empty($param['account_no'])) {
            $select->where("account_no = ?", $param['account_no']);
        }
        if (isset($param['cust_id']) && !empty($param['cust_id'])) {
            $select->where("cust_id = ?", $param['cust_id']);
        }
        if (isset($param['status_bank']) && !empty($param['status_bank'])) {
            $select->where("status_bank = ?", $param['status_bank']);
        }
        if (isset($param['status']) && !empty($param['status'])) {
            $select->where("status = ?", $param['status']);
        }
        return $this->fetchRow($select);
    }

    public function isValid($param) {
        
        $v = new Validator();
        $ref_num = isset($param['ref_num']) ? $param['ref_num'] : '';
        $dob = isset($param['date_of_birth']) ? $param['date_of_birth'] : '';
        $gender = isset($param['gender']) ? $param['gender'] : '';
        $occupation = isset($param['occupation']) ? $param['occupation'] : '';
        $title = isset($param['title']) ? $param['title'] : '';
        $city = isset($param['city']) ? $param['city'] : '';
        $state = isset($param['state']) ? $param['state'] : '';
        $pincode = isset($param['pincode']) ? $param['pincode'] : '';
        $line1 = isset($param['address_line1']) ? $param['address_line1'] : '';
        $line2 = isset($param['address_line2']) ? $param['address_line2'] : '';
                
        $commcity = isset($param['comm_city']) ? $param['comm_city'] : '';
        $commstate = isset($param['comm_state']) ? $param['comm_state'] : '';
        $commpincode = isset($param['comm_pin']) ? $param['comm_pin'] : '';
        $commline1 = isset($param['comm_address_line1']) ? $param['comm_address_line1'] : '';
        $commline2 = isset($param['comm_address_line2']) ? $param['comm_address_line2'] : '';
        
        
        
        
        $nsdcEnrolNo = isset($param['nsdc_enrollment_no']) ? $param['nsdc_enrollment_no'] : '';
        $solId = isset($param['sol_id']) ? $param['sol_id'] : '';
        $minorFlg = isset($param['minor_flg']) ? $param['minor_flg'] : '';
        $nomineeMinorFlg = isset($param['nominee_minor_flag']) ? $param['nominee_minor_flag'] : '';
        $nominationFlg = isset($param['nomination_flg']) ? $param['nomination_flg'] : '';
        $nomineeName = isset($param['nominee_name']) ? $param['nominee_name'] : '';
        $nomineeRel = isset($param['nominee_relationship']) ? $param['nominee_relationship'] : '';
        $nomineeAdd1 = isset($param['nominee_add_line1']) ? $param['nominee_add_line1'] : '';
        $nomineeAdd2 = isset($param['nominee_add_line2']) ? $param['nominee_add_line2'] : '';
        $nomineeCity = isset($param['nominee_city_cd']) ? $param['nominee_city_cd'] : '';
        $nomineeDob = isset($param['nominee_dob']) ? $param['nominee_dob'] : '';
       
        $nomineeMinorGuardianCD = isset($param['nominee_minor_guradian_cd']) ? $param['nominee_minor_guradian_cd'] : '';
        $nomineeMinorGuardianName = isset($param['minor_guardian_name']) ? $param['minor_guardian_name'] : '';
        
        $pan = isset($param['pan']) ? $param['pan'] : '';
        $aadharNo = isset($param['aadhaar_no']) ? $param['aadhaar_no'] : '';
        $mobile = isset($param['mobile']) ? $param['mobile'] : '';
        $boiAccountNo = isset($param['account_no']) ? $param['account_no'] : '';
        $boiCustomerId = isset($param['cust_id']) ? $param['cust_id'] : '';
        $firstChar = substr($ref_num, 0, 1);
        $debitmandateAmount = isset($param['debit_mandate_amount']) ? $param['debit_mandate_amount'] : 0;
       
        
//        if($nsdcEnrolNo == ''){
//             $this->setError('Invalid NSDC Enrollment No');
//            return FALSE;
//        }
        if($solId == ''){
             $this->setError('Invalid Linked Branch ID');
            return FALSE;
        }
         if($title == ''){
             $this->setError('Invalid Title');
            return FALSE;
        }
         if($occupation == ''){
             $this->setError('Invalid Occupation');
            return FALSE;
        }
         if($gender == ''){
             $this->setError('Invalid Gender');
            return FALSE;
        }
          if(!isset($dob)){
             $this->setError('Invalid Date of Birth');
             return FALSE;
        }
        else{
           $ismidminor = $v->isMidMinor($dob, 11, 18, FALSE) ;
                if($ismidminor && $minorFlg != 'N')
                {
                   $this->setError('Invalid Minor Flag');
                   return FALSE;
                }
               
        }
               
        if($city == '' || $state == '' || $pincode == '' || $line1 == ''){
            $this->setError('Incomplete Address Details');
            return FALSE;  
        }
          if($commcity == '' || $commstate == '' || $commpincode == '' || $commline1 == '' ){
            $this->setError('Incomplete Communication Address Details');
            return FALSE;  
        }
       
        if($aadharNo != ''){
        if(!$v->validateAadhar($aadharNo,$thowException = FALSE)){
             $this->setError('Invalid Aadhaar Number');
            return FALSE;
        }
        }
        if($pan != ''){
        if(!$v->validatePAN($pan,$thowException = FALSE)){
             $this->setError('Invalid PAN');
            return FALSE;
        }
        }
       
        if($nominationFlg != '' && $nominationFlg == 'Y'){
        if($nomineeName == '' || $nomineeRel = '' || $nomineeAdd1 = '' || $nomineeCity ='' || $nomineeDob = ''){
               $this->setError('Incomplete Nominee Details');
             return FALSE; 
            }
        }
        
        if($nomineeMinorFlg != '' && $nomineeMinorFlg == 'Y'){
            if($nomineeMinorGuardianCD == '' || $nomineeMinorGuardianName = ''){
                  $this->setError('Incomplete Minor Nominee Guardian Details');
             return FALSE; 
            }
        }
      
      
       
        if (strlen($ref_num) != 10 || !(ctype_digit($ref_num)) || $firstChar != 7) {
            $this->setError('Invalid Reference Number');
            return FALSE;
        }
//        if(strlen($mobile) != 10 || !(ctype_digit($mobile))){
//           $this->setError('Invalid Mobile Number');            
//           return FALSE;
//        } 
        if (!(ctype_digit($boiAccountNo))) {
            $this->setError('Invalid BOI Account No.');
            return FALSE;
        }
        if (!(ctype_digit($boiCustomerId))) {
            $this->setError('Invalid BOI Customer Id');
            return FALSE;
        }
        
            return TRUE;
       
    }

    public function boiAccountMapping() {

        try {
            $cardMapping = new Corp_Boi_CardMapping();
            $crnMaster = new CRNMaster();

            $dataArr = $cardMapping->getPendingRecrods($this->getProductId(), '', BOI_MAP);
            //echo '<pre>';print_r($dataArr);exit;
            $this->_db->beginTransaction();
            if (!empty($dataArr)) {
                $numFail = 0;
                $numSucc = 0;
//print_r($dataArr->toArray());exit;
                foreach ($dataArr as $result) {
                    $crnInfo = $crnMaster->getInfoByCardNumber(array(
                        'card_number' => $result['card_number'],
                        'product_id' => $this->getProductId(),
                        'status' => STATUS_FREE,
                    ));
                    //echo '<pre>';print_r($crnInfo);exit;
                    if (empty($crnInfo)) {
                        $updateArr = array(
                            'failed_reason' => 'CRN/Card Pack Id not found',
                            'date_failed' => new Zend_Db_Expr('NOW()'),
                            'status' => STATUS_FAILURE
                        );
                        $cardMapping->update($updateArr, 'id="' . $result['id'] . '"');
                        //echo 'Updating failed Recrod';exit;
                        $numFail++;
                    } else {
                        $rs = $this->getCardholderByById(array(
                            'cust_id' => $result['boi_customer_id'],
                            'account_no' => $result['boi_account_number'],
                            'status_bank' => STATUS_APPROVED,
                            'status' => STATUS_ACTIVATED
                        ));
                        if (empty($rs)) {
                            $updateArr = array(
                                'failed_reason' => 'Cardholder not found',
                                'date_failed' => new Zend_Db_Expr('NOW()'),
                                'status' => STATUS_FAILURE
                            );
                            $cardMapping->update($updateArr, 'id="' . $result['id'] . '"');
                            $numFail++;
                        } else {
                            $updateArr = array(
                                'card_number' => Util::insertCardCrn($result['card_number']),
                                'boi_account_number' => $result['boi_account_number'],
                                'boi_card_mapping_id' => $result['id'],
                                'status_ecs' => STATUS_WAITING
                            );
                            $this->update($updateArr, "id='" . $rs['id'] . "'");

                            $cardMappingUpdateArray = array(
                                'failed_reason' => '',
                                'status' => STATUS_MAPPED
                            );
                            $cardMapping->update($cardMappingUpdateArray, "id='" . $result['id'] . "'");

                            $crmMasterUpdateArray = array(
                                'status' => STATUS_USED
                            );
                            $crnMaster->update($crmMasterUpdateArray, "id='" . $crnInfo['id'] . "'");
                            $numSucc++;
                        }
                    }
                }
            }
            $this->_db->commit();
        } catch (Exception $e) {
            $this->_db->rollBack();
            App_Logger::log($e->getMessage());
        }
        return array('success' => $numSucc, 'failed' => $numFail);
    }

    public function getIncompleteCustomers() {

        $select = $this->_db->select();

        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as kc');
        $select->where("kc.boi_customer_id = 0")
                ->order('kc.id')
                ->limit('2000');
        return $this->_db->fetchAll($select);
    }

    /* getOpsApproved() will return the Ops Approved
     */

    public function getOpsApproved() {
        $select = $this->select()
                ->from($this->_name, array('id', 'sol_id', 'title',
                    'concat(first_name, " ", middle_name, " ", last_name) as name',
                    'occupation', 'IF(gender="male", "M", "F") as gender',
                    'DATE_FORMAT(date_of_birth, "%d-%m-%Y") as date_of_birth', 'address_type', 'address_line1',
                    'address_line2', 'city', 'state', 'country_code', 'pincode',
                    'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_state',
                    'comm_country_code', 'comm_pin', 'landline', 'mobile', 'email',
                    'pan', 'aadhaar_no', 'nre_flag', 'nre_nationality', 'passport',
                    'passport_issue_date', 'passport_expiry_date',
                    'marital_status', 'cust_comm_code', 'other_bank_account_no',
                    'other_bank_account_type', 'other_bank_name', 'other_bank_branch',
                    'employer_name', 'employer_address_line1', 'employer_address_line2',
                    'employer_address_city', 'employer_address_state',
                    'employer_address_country_id', 'employer_address_pincode',
                    'employer_contact_no', 'minor_flg', 'minor_guardian_code',
                    'minor_guardian_name', 'minor_guardian_address_line1',
                    'minor_guardian_address_line2', 'minor_guardian_city',
                    'minor_guardian_state', 'minor_guardian_pincode',
                    'minor_guardian_country_id', 'mode_of_operation', 'nomination_flg',
                    'nominee_name', 'nominee_add_line1', 'nominee_add_line2',
                    'nominee_relationship', 'nominee_city_cd', 'nominee_minor_guradian_cd',
                    'DATE_FORMAT(nominee_dob, "%d-%m-%Y") as nominee_dob',
                    'nominee_minor_flag', 'amount_open', 'mode_of_payment_open',
                    'account_no', 'cust_id', 'sqlid', 'finacle_status', 'update_sql_status',
                    'staff_flg', 'staff_no', 'minor_title_guradian_code', 'passport_details',
                    'introducer_title_code', 'introducer_name', 'existing_cust_flg',
                    'account_currency_code', 'cust_id_ver_flg', 'account_id_ver_flg',
                    'schm_code', 'orgaization_type', 'introducer_flg', 'introducer_cust_id',
                    'cust_currency_code', 'DATE_FORMAT(date_created, "%d-%m-%Y") as date_created',
                    'account_type_id', 'ref_num', 'debit_mandate_amount', 'date_of_birth as dob_orig', 'nominee_dob as nominee_dob_orig'))
                ->where("status_ops = ?", STATUS_APPROVED)
                ->where("status_bank = ? ", STATUS_PENDING)
                ->where("output_file_id = 0");
        $rows = $this->_db->fetchAll($select);

        return $rows;
    }

    public function saveOutputFileData($fileId, $reqData) {
        $totalRequests = count($reqData);
        if ($totalRequests > 0 && $fileId > 0) {
            foreach ($reqData as $request) {
                $updArr = array('output_file_id' => $fileId);
                $this->update($updArr, "id = " . $request['id']);
            }
        }

        return TRUE;
     }
     
    

    public function getCustomerInfo($custId) {

        $select = $this->select();
        $select->where("id = ?", $custId);
        $select->where("status = '" . STATUS_ACTIVE . "'");
        $rs = $this->fetchRow($select);
        if (!empty($rs)) {
            return $rs;
        }
        return FALSE;
    }

    public function getActiveCustomers() {
        $select = $this->_db->select();
        $select->from($this->_name . ' as kc')
                ->where("kc.status_ecs = '" . STATUS_SUCCESS . "'")
                ->order('kc.id');
        return $this->_db->fetchAll($select);
    }

    public function bulkApproval($params) {
        $user = Zend_Auth::getInstance()->getIdentity();
        $ip = $this->formatIpAddress(Util::getIP());
        $customerLogModel = new Corp_Boi_CustomersLog();
        $objCustomerDetailModel = new Corp_Boi_CustomerDetail();
        foreach ($params as $id) {
            $custDetails = $this->findById($id);
            $custDetails = Util::toArray($custDetails);
//         echo '<pre>';print_r($custDetails);exit('fjdfjdfjg');
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

    public function getDebitMandateAmount($data) {
	
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`c`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`c`.`crn`,'".$decryptionKey."') as crn");
	
        $select = $this->_db->select();	
	$select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as c', array('id', 'product_id', 'boi_customer_id', 'customer_master_id', 'customer_type', 'xls_boi_customer_id', 'nsdc_enrollment_no', 'sol_id', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'title', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'occupation', 'perm_country', 'aadhaar_no', 'pan', 'uid_no', 'mobile', 'email', 'landline', 'address_type', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country_code', 'mother_maiden_name', 'employer_name', 'employer_address_line1', 'employer_address_line2', 'employer_address_city', 'employer_address_state', 'employer_address_country_id', 'employer_address_pincode', 'employer_contact_no', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'comm_country_code', 'nre_flag', 'nre_nationality', 'passport', 'passport_issue_date', 'passport_expiry_date', 'marital_status', 'cust_comm_code', 'other_bank_account_no', 'other_bank_account_type', 'other_bank_name', 'other_bank_branch', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'minor_flg', 'minor_guardian_code', 'minor_guardian_name', 'minor_guardian_address_line1', 'minor_guardian_address_line2', 'minor_guardian_city', 'minor_guardian_state', 'minor_guardian_pincode', 'minor_guardian_country_id', 'mode_of_operation', 'nomination_flg', 'nominee_name', 'nominee_relationship', 'nominee_add_line1', 'nominee_add_line2', 'nominee_city_cd', 'nominee_minor_guradian_cd', 'nominee_dob', 'nominee_minor_flag', 'amount_open', 'mode_of_payment_open', 'account_no', 'cust_id', 'sqlid', 'finacle_status', 'update_sql_status', 'staff_flg', 'staff_no', 'minor_title_guradian_code', 'passport_details', 'introducer_title_code', 'introducer_code', 'introducer_name', 'existing_cust_flg', 'account_currency_code', 'cust_id_ver_flg', 'account_id_ver_flg', 'schm_code', 'orgaization_type', 'introducer_flg', 'introducer_cust_id', 'cust_currency_code', 'account_type_id', 'boi_account_number', 'ref_num', 'training_partner_name', 'output_file_id', 'prev_output_file_ids', 'aml_status', 'traning_center_name', 'training_center_id', 'debit_mandate_amount', 'aof_ref_num', 'debit_mandate_accout', 'debit_mandate_account', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'boi_card_mapping_id', 'date_blocked', 'status', 'status_bank', 'status_ops', 'status_ecs'));
	
        $select->joinLeft(DbTable::TABLE_BOI_CUSTOMER_PURSE . ' as cp', "c.boi_customer_id = cp.boi_customer_id", array('cp.amount as purse_amount'));
        $select->where("c.status IN ('". STATUS_ACTIVE."', '".STATUS_ACTIVATED."')");
        $select->where("c.date_authorize >= '" . $data['from'] . "'");
        $select->where("c.date_authorize <= '" . $data['to'] . "'");
        $select->order('c.id ASC'); 
        return $this->_db->fetchAll($select);
    }

    public function exportGetDebitMandateAmount($param) {

        $data = $this->getDebitMandateAmount($param);

        $retData = array();

        if (!empty($data)) {
            foreach ($data as $key => $data) {

                $retData[$key]['account_no'] = $data['account_no'];
                $retData[$key]['card_number'] = Util::maskCard($data['card_number']);
                $retData[$key]['crn'] = Util::maskCard($data['crn']);
                $retData[$key]['member_id'] = $data['member_id'];
                $retData[$key]['cust_id'] = $data['cust_id'];
                $retData[$key]['debit_mandate_amount'] = $data['debit_mandate_amount'];
                $retData[$key]['purse_amount'] = $data['purse_amount'];
                $retData[$key]['status'] = $data['status'];
                $retData[$key]['aadhaar_no'] = $data['aadhaar_no'];
            }
        }

        return $retData;
    }

      public function showBankPendingCustomers($page = 1, $data = array(), $paginate = NULL, $force = FALSE) {
        $itemsPerPage = isset($data['items_per_page']) ? $data['items_per_page'] : '';

        $dateApproval = isset($data['date_approval']) ? $data['date_approval'] : '';
        $appRefNo = isset($data['appRefNo']) ? $data['appRefNo'] : '';
        
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
	$select = $this->select(); 
	$select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER, array('id', 'product_id', 'boi_customer_id', 'customer_master_id', 'customer_type', 'xls_boi_customer_id', 'nsdc_enrollment_no', 'sol_id', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'title', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'occupation', 'perm_country', 'aadhaar_no', 'pan', 'uid_no', 'mobile', 'email', 'landline', 'address_type', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country_code', 'mother_maiden_name', 'employer_name', 'employer_address_line1', 'employer_address_line2', 'employer_address_city', 'employer_address_state', 'employer_address_country_id', 'employer_address_pincode', 'employer_contact_no', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'comm_country_code', 'nre_flag', 'nre_nationality', 'passport', 'passport_issue_date', 'passport_expiry_date', 'marital_status', 'cust_comm_code', 'other_bank_account_no', 'other_bank_account_type', 'other_bank_name', 'other_bank_branch', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'minor_flg', 'minor_guardian_code', 'minor_guardian_name', 'minor_guardian_address_line1', 'minor_guardian_address_line2', 'minor_guardian_city', 'minor_guardian_state', 'minor_guardian_pincode', 'minor_guardian_country_id', 'mode_of_operation', 'nomination_flg', 'nominee_name', 'nominee_relationship', 'nominee_add_line1', 'nominee_add_line2', 'nominee_city_cd', 'nominee_minor_guradian_cd', 'nominee_dob', 'nominee_minor_flag', 'amount_open', 'mode_of_payment_open', 'account_no', 'cust_id', 'sqlid', 'finacle_status', 'update_sql_status', 'staff_flg', 'staff_no', 'minor_title_guradian_code', 'passport_details', 'introducer_title_code', 'introducer_code', 'introducer_name', 'existing_cust_flg', 'account_currency_code', 'cust_id_ver_flg', 'account_id_ver_flg', 'schm_code', 'orgaization_type', 'introducer_flg', 'introducer_cust_id', 'cust_currency_code', 'account_type_id', 'boi_account_number', 'ref_num', 'training_partner_name', 'output_file_id', 'prev_output_file_ids', 'aml_status', 'traning_center_name', 'training_center_id', 'debit_mandate_amount', 'aof_ref_num', 'debit_mandate_accout', 'debit_mandate_account', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'boi_card_mapping_id', 'date_blocked', 'status', 'status_bank', 'status_ops', 'status_ecs', 'concat(first_name," ",last_name) as name'));
	
	
	
        $select->where("status_ops = '" . STATUS_APPROVED . "'");
        $select->where("status_bank = '" . STATUS_PENDING . "'");
       
        if ($dateApproval != '') {
            $dateApproval = Util::returnDateFormatted($dateApproval, "d-m-Y", "Y-m-d", "-", "-", 'from');
            $select->where("date_approval >= '" . $dateApproval . "'");
        }
        if ($appRefNo != '') {
            $select->where("ref_num = '" . $appRefNo . "'");
        } 
        $select->order('id ASC');
        return $this->_paginate($select, $page, $paginate, $itemsPerPage);
    }
    
     public function exportshowPendingCustomerDetails($data) {
        $state = isset($data['state']) ? $data['state'] : '';
        $mobile = isset($data['mobile']) ? $data['mobile'] : '';
        $ref_num = isset($data['ref_num']) ? $data['ref_num'] : '';
        $itemsPerPage = isset($data['items_per_page']) ? $data['items_per_page'] : '';

        $pincode = isset($data['pincode']) ? $data['pincode'] : '';
        $dateApproval = isset($data['date_created']) ? $data['date_created'] : '';
        
        //Enable DB Slave
        $this->_enableDbSlave();
	
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
	
        $select = $this->select();	
	$select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER, array('id', 'product_id', 'boi_customer_id', 'customer_master_id', 'customer_type', 'xls_boi_customer_id', 'nsdc_enrollment_no', 'sol_id', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'title', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'occupation', 'perm_country', 'aadhaar_no', 'pan', 'uid_no', 'mobile', 'email', 'landline', 'address_type', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country_code', 'mother_maiden_name', 'employer_name', 'employer_address_line1', 'employer_address_line2', 'employer_address_city', 'employer_address_state', 'employer_address_country_id', 'employer_address_pincode', 'employer_contact_no', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'comm_country_code', 'nre_flag', 'nre_nationality', 'passport', 'passport_issue_date', 'passport_expiry_date', 'marital_status', 'cust_comm_code', 'other_bank_account_no', 'other_bank_account_type', 'other_bank_name', 'other_bank_branch', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'minor_flg', 'minor_guardian_code', 'minor_guardian_name', 'minor_guardian_address_line1', 'minor_guardian_address_line2', 'minor_guardian_city', 'minor_guardian_state', 'minor_guardian_pincode', 'minor_guardian_country_id', 'mode_of_operation', 'nomination_flg', 'nominee_name', 'nominee_relationship', 'nominee_add_line1', 'nominee_add_line2', 'nominee_city_cd', 'nominee_minor_guradian_cd', 'nominee_dob', 'nominee_minor_flag', 'amount_open', 'mode_of_payment_open', 'account_no', 'cust_id', 'sqlid', 'finacle_status', 'update_sql_status', 'staff_flg', 'staff_no', 'minor_title_guradian_code', 'passport_details', 'introducer_title_code', 'introducer_code', 'introducer_name', 'existing_cust_flg', 'account_currency_code', 'cust_id_ver_flg', 'account_id_ver_flg', 'schm_code', 'orgaization_type', 'introducer_flg', 'introducer_cust_id', 'cust_currency_code', 'account_type_id', 'boi_account_number', 'ref_num', 'training_partner_name', 'output_file_id', 'prev_output_file_ids', 'aml_status', 'traning_center_name', 'training_center_id', 'debit_mandate_amount', 'aof_ref_num', 'debit_mandate_accout', 'debit_mandate_account', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'boi_card_mapping_id', 'date_blocked', 'status', 'status_bank', 'status_ops', 'status_ecs', 'concat(first_name," ",last_name) as name'));
	
        $select->where("status_ops = '" . STATUS_PENDING . "'");
        if ($state != '') {
            $select->where("state = '" . $state . "'");
        }
        if ($pincode != '') {
            $select->where("pincode = '" . $pincode . "'");
        }
        if ($mobile != '') {
            $select->where("mobile = '" . $mobile . "'");
        }
        if ($ref_num != '') {
            $select->where("ref_num = '" . $ref_num . "'");
        }
        if ($dateApproval != '') {
            $dateApproval = Util::returnDateFormatted($dateApproval, "d-m-Y", "Y-m-d", "-", "-", 'from');
            $select->where("date_created >= '" . $dateApproval . "'");
        }
       
       $select->order('date_created DESC');
       
        //Disable DB Slave
        $this->_disableDbSlave();
        
        
        $data = $this->fetchAll($select);

        $retData = array();
        $rctModel = new RctMaster();
        if (!empty($data)) {
            foreach ($data as $key => $data) {
                $retData[$key]['ref_num'] = $data['ref_num'];
                $retData[$key]['status'] = $data['status'];
                $retData[$key]['sol_id'] = $data['sol_id'];
                $retData[$key]['title'] = $data['title'];
                $retData[$key]['first_name'] = $data['first_name'];
                $retData[$key]['middle_name'] = $data['middle_name'];
                $retData[$key]['last_name'] = $data['last_name'];
                $retData[$key]['aadhaar_no'] = $data['aadhaar_no'];
                $retData[$key]['uid_no'] = $data['uid_no'];
                $retData[$key]['nsdc_enrollment_no'] = $data['nsdc_enrollment_no'];
                $retData[$key]['debit_mandate_amount'] = $data['debit_mandate_amount'];
                $retData[$key]['training_center_id'] = $data['training_center_id'];
                $retData[$key]['traning_center_name'] = $data['traning_center_name'];
                $retData[$key]['training_partner_name'] = $data['training_partner_name'];
                $retData[$key]['pan'] = $data['pan'];
                $retData[$key]['gender'] = $data['gender'];
                $retData[$key]['date_of_birth'] = $data['date_of_birth'];
                $retData[$key]['marital_status'] = $data['marital_status'];
                $retData[$key]['occupation'] = $rctModel->getOccupationName($data['occupation']);
                $retData[$key]['address_line1'] = $data['address_line1'];
                $retData[$key]['address_line2'] = $data['address_line2'];
                $retData[$key]['state'] = $rctModel->getStateName($data['state']);
                $retData[$key]['city'] = $rctModel->getCityName($data['city']);
                $retData[$key]['pincode'] = $data['pincode'];
                $retData[$key]['comm_address_line1'] = $data['comm_address_line1'];
                $retData[$key]['comm_address_line2'] = $data['comm_address_line2'];
                $retData[$key]['comm_state'] = $rctModel->getStateName($data['comm_state']);
                $retData[$key]['comm_city'] = $rctModel->getCityName($data['comm_city']);
                $retData[$key]['comm_pin'] = $data['comm_pin'];
                $retData[$key]['landline'] = $data['landline'];
                $retData[$key]['mobile'] = $data['mobile'];
                $retData[$key]['email'] = $data['email'];
                
                $retData[$key]['nomination_flg'] = Util::convertIntoYesNo($data['nomination_flg']);
                $retData[$key]['nominee_name'] = $data['nominee_name'];
                $retData[$key]['nominee_relationship'] = $rctModel->getRelationName($data['nominee_relationship']);
                $retData[$key]['nominee_dob'] = $data['nominee_dob'];
                $retData[$key]['nominee_add_line1'] = $data['nominee_add_line1'];
                $retData[$key]['nominee_add_line2'] = $data['nominee_add_line2'];
                $retData[$key]['nominee_city_cd'] = $data['nominee_city_cd'];
                $retData[$key]['minor_guardian_name'] = $data['minor_guardian_name'];
                $retData[$key]['nominee_minor_guradian_cd'] = $rctModel->getRelationName($data['nominee_minor_guradian_cd']);
                $retData[$key]['date_created'] = $data['date_created'];
                $retData[$key]['date_updated'] = $data['date_updated'];
 
                
                
            }
        }
        return $retData;
    }
    
    
    public function isDuplicate($data){
        $select = $this->select();
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER, array("count(*) as cnt") )
                ->where("ref_num = ?", $data['ref_num'])
                ->where("by_agent_id = ?", $data['by_agent_id'])
                ->where("sol_id = ?", $data['sol_id'])
                ->where("status_ops = ?", $data['status_ops'])
                ->where("status = ?", $data['status']);
        return $this->fetchRow($select);
    }
    
    
    
  
    
    
    public function getPaymentstatus($data) {
        
        $aof_number = isset($data['aof_number']) ? $data['aof_number'] : '';
        $account_number = isset($data['account_number']) ? $data['account_number'] : '';
        $tp_name = isset($data['tp_name']) ? $data['tp_name'] : '';
        $tp_account_number = isset($data['tp_account_number']) ? $data['tp_account_number'] : '';
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`c`.`card_number`,'".$decryptionKey."') as card_number");
        
        $select = $this->_db->select();
       
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as c', array('c.ref_num','c.account_no','c.date_created','c.sol_id','c.title','c.first_name','c.middle_name','c.last_name','c.aadhaar_no','c.nsdc_enrollment_no','c.debit_mandate_amount','c.training_center_id','c.traning_center_name','c.training_partner_name','c.pan','c.gender','c.date_of_birth','c.marital_status','c.occupation','c.address_line1','c.address_line2','c.state','c.city','c.pincode','c.comm_address_line1','c.comm_address_line2','c.comm_state','c.comm_city','c.comm_pin','c.landline','c.mobile','c.email','c.nomination_flg','c.nominee_name','c.nominee_relationship','c.nominee_dob','c.nominee_add_line1','c.nominee_add_line2','c.nominee_city_cd','c.nominee_minor_guradian_cd',$card_number,'c.status_bank','c.status_ops','c.uid_no','c.cust_id'));
        $select->join(DbTable::TABLE_AGENT_DETAILS . ' as td', "c.by_agent_id = td.agent_id AND td.status = '".STATUS_ACTIVE."'", array('td.bank_account_number','td.bank_name','td.bank_ifsc_code'));
        $select->join(DbTable::TABLE_BOI_CORP_LOAD_REQUEST . ' as bl', "c.id = bl.cardholder_id AND bl.status IN ('". STATUS_LOADED."', '".STATUS_CUTOFF."')", array('bl.date_created as load_date','bl.amount as load_amount','bl.date_cutoff as auto_rev_date','bl.status as wallet_status', 'bl.amount_cutoff as auto_rev_amount'));
        $select->join(DbTable::TABLE_BOI_CUSTOMER_PURSE . ' as bp', "bp.id = bl.customer_purse_id", array('bp.amount as wallet_bal'));
       
        
        if ($aof_number != '') {
            $select->where("c.ref_num =?", $aof_number );
        }
        if ($account_number != '') {
            $select->where("c.account_no =? ", $account_number );
        }
        if ($tp_name != '') {
            $select->where("td.first_name =? ", $tp_name );
        }
        if ($tp_account_number != '') {
            $select->where("td.bank_account_number =? " , $tp_account_number );
        }

        //$select->where("c.status IN ('". STATUS_ACTIVE."')");
        $select->where("bl.date_created >= '" . $data['from'] . "'");
        $select->where("bl.date_created <= '" . $data['to'] . "'");
        $select->order('c.id ASC');
        
        return $this->_db->fetchAll($select);
    }

    public function exportGetPaymentstatus($param) {

        
        $rctModel = new RctMaster();
        $data = $this->getPaymentstatus($param);

        $retData = array();

        if (!empty($data)) {
            foreach ($data as $key => $data) {
                
                
                
                $nominee_relationship_code = $data['nominee_relationship'];
                $nominee_city_code = $data['nominee_city_cd'];
                $nominee_gaurdian = $data['nominee_minor_guradian_cd'];

                if($nominee_relationship_code){
                    $nominee_relation = $rctModel->getRelationName($nominee_relationship_code);
                } else {
                    $nominee_relation = '';
                }

                if($nominee_city_code){
                   $cityName = $rctModel->getCityName($nominee_city_code);
                } else {
                    $cityName = '';
                }

                if($nominee_gaurdian){
                    $nominee_gaurdian = $rctModel->getRelationName($nominee_gaurdian);
                } else {
                    $nominee_gaurdian = '';
                }
                
        
                $retData[$key]['ref_num'] = $data['ref_num'];
                $retData[$key]['account_no'] = Util::maskCard($data['account_no'],4);
                $retData[$key]['date_created'] = $data['date_created'];
                $retData[$key]['sol_id'] = $data['sol_id'];
                $retData[$key]['title'] = $data['title'];
                $retData[$key]['first_name'] = $data['first_name'];
                $retData[$key]['middle_name'] = $data['middle_name'];
                $retData[$key]['last_name'] = $data['last_name'];
                $retData[$key]['aadhaar_no'] = $data['aadhaar_no'];
                $retData[$key]['aadhar_enrollment_id'] = '';
                $retData[$key]['nsdc_enrollment_no'] = $data['nsdc_enrollment_no'];
                $retData[$key]['debit_mandate_amount'] = $data['debit_mandate_amount'];
                $retData[$key]['training_center_id'] = $data['training_center_id'];
                $retData[$key]['traning_center_name'] = $data['traning_center_name'];
                $retData[$key]['training_partner_name'] = $data['training_partner_name'];
                $retData[$key]['bank_account_number'] = $data['bank_account_number'];
                $retData[$key]['bank_name'] = $data['bank_name'];
                $retData[$key]['bank_ifsc_code'] = $data['bank_ifsc_code'];
                $retData[$key]['pan'] = $data['pan'];
                $retData[$key]['gender'] = $data['gender'];
                $retData[$key]['date_of_birth'] = $data['date_of_birth'];
                $retData[$key]['marital_status'] = $data['marital_status'];
                $retData[$key]['occupation'] = $data['occupation'];
                $retData[$key]['address_line1'] = $data['address_line1'];
                $retData[$key]['address_line2'] = $data['address_line2'];
                $retData[$key]['state'] = $data['state'];
                $retData[$key]['city'] = $data['city'];
                $retData[$key]['pincode'] = $data['pincode'];
                $retData[$key]['comm_address_line1'] = $data['comm_address_line1'];
                $retData[$key]['comm_address_line2'] = $data['comm_address_line2'];
                $retData[$key]['comm_state'] = $data['comm_state'];
                $retData[$key]['comm_city'] = $data['comm_city'];
                $retData[$key]['comm_pin'] = $data['comm_pin'];
                $retData[$key]['landline'] = $data['landline'];
                $retData[$key]['mobile'] = $data['mobile'];
                $retData[$key]['email'] = $data['email'];
                $retData[$key]['nomination_flg'] = $data['nomination_flg'];
                $retData[$key]['nominee_name'] = $data['nominee_name'];
                $retData[$key]['nominee_relationship'] = $nominee_relation;
                $retData[$key]['nominee_dob'] = $data['nominee_dob'];
                $retData[$key]['nominee_add_line1'] = $data['nominee_add_line1'];
                $retData[$key]['nominee_add_line2'] = $data['nominee_add_line2'];
                $retData[$key]['nominee_city_cd'] = $cityName;
                $retData[$key]['nominee_minor_guradian_cd'] = $nominee_gaurdian;
                $retData[$key]['card_number'] = Util::maskCard($data['card_number'],4);
                $retData[$key]['status_bank'] = $data['status_bank'];
                $retData[$key]['status_ops'] = $data['status_ops'];
                $retData[$key]['action_date'] = '';
                $retData[$key]['uid_no'] = $data['uid_no'];
                $retData[$key]['cust_id'] = $data['cust_id'];
                $retData[$key]['load_date'] = $data['load_date'];
                $retData[$key]['load_amount'] = $data['load_amount'];
                $retData[$key]['auto_rev_date'] = $data['auto_rev_date'];
                $retData[$key]['wallet_bal'] = $data['wallet_bal'];
                $retData[$key]['wallet_status'] = $data['wallet_status'];
                
            }
        }

        return $retData;
    }
    
   
    //end rnew code
    
    public function validateSOLID($id,$agentId) {
        $select = $this->select();
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER, array("sol_id") )
                //->where("sol_id = ?", $solID)
                ->where("id = ?", $id); 
        $resp =  $this->fetchRow($select);    
        
        $agentModel = new Agents();
        $agent = $agentModel->findByAgentId($agentId);
        if($resp['sol_id'] != $agent['branch_id']) {
            $this->update(array('sol_id' => $agent['branch_id']), 'id="'.$id.'"');
        }
    }
    
     public function getConsolidatedDetails($param) {
        $user = Zend_Auth::getInstance()->getIdentity();
        $agentModel = new Agents();
        $custPurse = new Corp_Boi_CustomerPurse();
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $product = isset($param['product_id']) && $param['product_id'] > 0 ? $param['product_id'] : '';
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $refnum = isset($param['ref_num']) ? $param['ref_num'] : '';
        $aadhaarNo = isset($param['aadhaar_no']) ? $param['aadhaar_no'] : '';
        $accountNo = isset($param['account_no']) ? $param['account_no'] : '';
        $ifscCode = isset($param['ifsc_code']) ? $param['ifsc_code'] : '';
        $enrollmentno = isset($param['nsdc_enrollment_no']) ? $param['nsdc_enrollment_no'] : '';
        $status = isset($param['status']) ? $param['status'] : '';
        $statusWallet = isset($param['wallet_load_status']) ? $param['wallet_load_status'] : '';
        $checkerDate = isset($param['date_approval']) ? $param['date_approval'] : '';
        $pursemaster = new MasterPurse();
       
        try{
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as c', array('c.*', 'concat(c.first_name," ",c.last_name) as name'));
        
        if($statusWallet != '' && $statusWallet == STATUS_LOADED){
            $condition = "loads.status IN  ('" . STATUS_LOADED . "', '".STATUS_CUTOFF."')";
            $select->join(DbTable::TABLE_BOI_CORP_LOAD_REQUEST .' as loads',"loads.cardholder_id = c.id AND $condition");   
         }
        
        if(empty($agentId)){
            $select->join(DbTable::TABLE_AGENTS . " as ag", "c.by_agent_id = ag.id ", array('concat(ag.first_name," ",ag.last_name) as by_agent_name'));
            $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION . " as re", "ag.id = re.to_object_id AND object_relation_type_id = 2", array('re.to_object_id'));
            $select->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "ad.agent_id = re.from_object_id AND ad.status = '".STATUS_ACTIVE."'", array('ad.branch_id'));
        }else{
        if ($agentId == 'all') {

            $bclist = $agentModel->getBCListing(array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $user->id, 'user_type' => $user->user_type));
            $select->join(DbTable::TABLE_AGENTS . " as ag", "c.by_agent_id = ag.id AND ag.id IN( $bclist)", array('concat(ag.first_name," ",ag.last_name) as by_agent_name'));
            $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION . " as re", "ag.id = re.to_object_id AND object_relation_type_id = 2", array('re.to_object_id'));
            $select->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "ad.agent_id = re.from_object_id AND ad.status = '".STATUS_ACTIVE."'", array('ad.branch_id'));
            
        } else {
            $select->join(DbTable::TABLE_AGENTS . " as ag", "c.by_agent_id = ag.id and c.by_agent_id = $agentId", array('concat(ag.first_name," ",ag.last_name) as by_agent_name'));
            $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION . " as re", "ag.id = re.to_object_id AND object_relation_type_id = 2", array('re.to_object_id'));
            $select->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "ad.agent_id = re.from_object_id AND ad.status = '".STATUS_ACTIVE."'", array('ad.branch_id'));
        }
        }
        if ($ifscCode != '') {
            $select->joinLeft(DbTable::TABLE_AGENT_DETAILS . " as ad", "c.by_agent_id = ad.agent_id AND ad.bank_ifsc_code = '".$ifscCode."' AND ad.status = '".STATUS_ACTIVE."'");
        }
        if ($product != '') {
            $select->where("c.product_id =?", $product );
        }
        if ($refnum != '') {
            $select->where("c.ref_num =? ", $refnum );
        }
        if ($aadhaarNo != '') {
            $select->where("c.aadhaar_no =? ", $aadhaarNo );
        }
        if ($accountNo != '') {
            $select->where("c.account_no =? ", $accountNo );
        }
        if ($enrollmentno != '') {
            $select->where("c.nsdc_enrollment_no =? " , $enrollmentno );
        }
    
        if ($checkerDate != '') {
            $select->where("DATE(c.date_approval) = '" . $checkerDate . "'");
        }
        
//        if($statusWallet != '' && $statusWallet == STATUS_FAILED){
//         
//            
//        }
        if ($status != '') {
            switch ($status) {
                case STATUS_PENDING:
                    $qry = $select->where("c.status_ops = '" . STATUS_APPROVED . "' AND c.status_bank = '" . $status . "'");
                    break;
                case STATUS_APPROVED:
                    $qry = $select->where("c.status_bank = '" . $status . "'");
                    break;
                case STATUS_REJECTED:
                    $qry = $select->where("c.status_bank = '" . $status . "'");
                    break;
                case STATUS_CARD_ISSUED:
                    $qry = $select->where("c.status_bank = '" . STATUS_APPROVED . "' AND c.card_number != ''");
                    break;
            }
        }
        if ($from != '' && $to != '') {
            $select->where("c.date_created >= '" . $param['from'] . "'");
            $select->where("c.date_created <= '" . $param['to'] . "'");
        }

        $select->order("c.date_created");
//        echo $select;
        $i = 0;
        $arrReport = array();
        $cardholders = $this->fetchAll($select);
      
        $cardholders = Util::toArray($cardholders);
       
        foreach( $cardholders as $boiCardholders){
       

        $select = $this->select();
        $select->from(DbTable::TABLE_BOI_CORP_LOAD_REQUEST . " as load",array('count(*) as count', 'sum(load.amount) as load_amount','load.date_load'));
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_BOI_CORP_CARDHOLDER . " as holder", "load.cardholder_id = holder.id");
        
//        if ($from != '' && $to != ''){
//            $select->where("load.date_load >=  '" . $from . "'");
//            $select->where("load.date_load <= '" . $to . "'");
//           
//        }
        $select->where("load.cardholder_id =?",$boiCardholders['id']);
//        if ($statusWallet != '') {
//           switch ($statusWallet) {
//                case STATUS_LOADED:
//                    $qry = $select->where("load.status IN  ('" . STATUS_LOADED . "', '".STATUS_COMPLETED."')");
//                    break;
//                case STATUS_FAILED:
//                    $qry = $select->where("load.status IN  ('".STATUS_FAILED."')");
//                    break;
//                
//            }
//        }
        $select->where("load.status IN  ('" . STATUS_LOADED . "', '".STATUS_CUTOFF."')");
        $select->group('load.cardholder_id');
        $loadArr = $this->fetchRow($select);
        
        $select = $this->select();
        $select->from(DbTable::TABLE_CARD_AUTH_REQUEST . " as auth",array('count(*) as cnt', 'sum(auth.amount_txn) as debit_amount_pos'));
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_BOI_CORP_CARDHOLDER . " as holder", "auth.cardholder_id = holder.id");
        
//        if ($from != '' && $to != ''){
//            $select->where("auth.date_created >=  '" . $from . "'");
//            $select->where("auth.date_created <= '" . $to . "'");
//           
//        }
        $select->where("auth.cardholder_id =? ",$boiCardholders['id']);
        $select->where("auth.product_id =? ",$product);
        $select->where("auth.status IN  ('" . STATUS_COMPLETED . "')");
        $select->group("auth.cardholder_id");
        $rsAuth = $this->fetchRow($select);
        
        // cutoff
    
        $select = $this->select();
        $select->from(DbTable::TABLE_BOI_CORP_LOAD_REQUEST . " as cutoff",array('count(*) as count', 'sum(cutoff.amount_cutoff) as auto_debit_amount','cutoff.date_cutoff as date_auto_debit'));
        $select->setIntegrityCheck(false);
        $select->joinLeft(DbTable::TABLE_BOI_CORP_CARDHOLDER . " as holder", "cutoff.cardholder_id = holder.id");
        
//        if ($from != '' && $to != ''){
//            $select->where("cutoff.date_cutoff >=  '" . $from . "'");
//            $select->where("cutoff.date_cutoff <= '" . $to . "'");
//           
//        }
        $select->where("cutoff.status IN  ('" . STATUS_LOADED . "')");
        $select->where("cutoff.cardholder_id =?",$boiCardholders['id']);        
        $select->group('cutoff.cardholder_id');
        $cutoffArr = $this->fetchRow($select);
        // Closing balance
        $onDate = explode(" ",$to);
        $purseMasterId = $pursemaster->getProductPurseDetails($boiCardholders['product_id']);
        
        
        $closingBalance = $custPurse->getClosingBalance(array(
            'customer_master_id' => $boiCardholders['customer_master_id'],
            'purse_master_id'   => $purseMasterId[0]['id'],
            'date' => $onDate[0]
            ));
        // Current wallet balance
        $walletBalance = $custPurse->getWalletBalance(array('customer_master_id' => $boiCardholders['customer_master_id'],'product_id' => $boiCardholders['product_id'],'from' => $from,'to' => $to));
 
           $arrReport[$i]['ref_num'] = $boiCardholders['ref_num']; 
           $arrReport[$i]['date_created'] = $boiCardholders['date_created']; 
           $arrReport[$i]['name'] = $boiCardholders['name']; 
           $arrReport[$i]['nsdc_enrollment_no'] = $boiCardholders['nsdc_enrollment_no']; 
           $arrReport[$i]['aadhaar_no'] = $boiCardholders['aadhaar_no']; 
           $arrReport[$i]['sol_id'] = $boiCardholders['sol_id']; 
           $arrReport[$i]['status_ops'] = $boiCardholders['status_ops']; 
           $arrReport[$i]['status_bank'] = $boiCardholders['status_bank']; 
           $arrReport[$i]['account_no'] = (isset($user->user_type) && $user->user_type == DISTRIBUTOR_AGENT) ? $boiCardholders['account_no'] : Util::maskCard($boiCardholders['account_no'], 4, 0);
           $arrReport[$i]['ifsc_code'] =  ($boiCardholders['account_no'] == '') ? '' : "BKID000".substr($boiCardholders['account_no'], 0, 4);
           $arrReport[$i]['card_number'] = Util::maskCard($boiCardholders['card_number'], 4, 4); 
           $arrReport[$i]['debit_mandate_amount'] = isset($boiCardholders['debit_mandate_amount']) && $boiCardholders['debit_mandate_amount'] > 0 ? Util::numberFormat($boiCardholders['debit_mandate_amount']) : '-'; 
           $arrReport[$i]['date_load'] = isset($loadArr['date_load'])? $loadArr['date_load'] : ''; 
           $arrReport[$i]['load_amount'] = isset($loadArr['load_amount']) && $loadArr['load_amount'] > 0 ? Util::numberFormat($loadArr['load_amount']) : '-'; 
//           $arrReport[$i]['wallet_bal'] = isset($closingBalance['closing_balance']) ? $closingBalance['closing_balance'] : ''; 
           $arrReport[$i]['current_wallet_balance'] = isset($walletBalance['wallet_sum']) && $walletBalance['wallet_sum'] > 0 ? Util::numberFormat($walletBalance['wallet_sum']) : '-'; 
           $arrReport[$i]['debit_amount_pos'] = isset($rsAuth['debit_amount_pos']) && $rsAuth['debit_amount_pos'] > 0 ? Util::numberFormat($rsAuth['debit_amount_pos']):'-'; 
           $arrReport[$i]['date_auto_debit'] = isset($cutoffArr['date_auto_debit'])?$cutoffArr['date_auto_debit']: '-'; 
           $arrReport[$i]['auto_debit_amount'] = isset($cutoffArr['auto_debit_amount']) && $cutoffArr['auto_debit_amount'] > 0 ? Util::numberFormat($cutoffArr['auto_debit_amount']):'-'; 
           $arrReport[$i]['by_agent_name'] = $boiCardholders['by_agent_name']; 
           $arrReport[$i]['training_center_id'] = $boiCardholders['training_center_id']; 
           $arrReport[$i]['traning_center_name'] = $boiCardholders['traning_center_name']; 
           $arrReport[$i]['training_partner_name'] = $boiCardholders['training_partner_name'];
           $i++;
           
           }
        
        }catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                       
                }
      
      return $arrReport;
    }
    
     public function getTPMisDetails($param) {
        $user = Zend_Auth::getInstance()->getIdentity();
        $agentModel = new Agents();
        $product = isset($param['product_id']) && $param['product_id'] > 0 ? $param['product_id'] : '';
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $agentCode = isset($param['agent_code']) ? $param['agent_code'] : '';
        $refnum = isset($param['ref_num']) ? $param['ref_num'] : '';
        $aadhaarNo = isset($param['aadhaar_no']) ? $param['aadhaar_no'] : '';
        $accountNo = isset($param['account_no']) ? $param['account_no'] : '';
	$tpId = isset($param['tp_id']) ? $param['tp_id'] : 0;
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`c`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`c`.`crn`,'".$decryptionKey."') as crn");

	$agentIds = '';
        try{

	if($tpId > 0) {
            $agentIds = $agentModel->getBCListing(
             array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $user->id, 'user_type' => $user->user_type));

        }
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as c', array('id', 'product_id', 'boi_customer_id', 'customer_master_id', 'customer_type', 'xls_boi_customer_id', 'nsdc_enrollment_no', 'sol_id', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'title', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'occupation', 'perm_country', 'aadhaar_no', 'pan', 'uid_no', 'mobile', 'email', 'landline', 'address_type', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country_code',
            'mother_maiden_name', 'employer_name', 'employer_address_line1', 'employer_address_line2', 'employer_address_city', 'employer_address_state', 'employer_address_country_id', 'employer_address_pincode', 'employer_contact_no', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'comm_country_code', 'nre_flag', 'nre_nationality', 'passport', 'passport_issue_date', 'passport_expiry_date', 'marital_status', 'cust_comm_code', 'other_bank_account_no', 'other_bank_account_type', 'other_bank_name', 'other_bank_branch', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'minor_flg', 'minor_guardian_code', 'minor_guardian_name', 'minor_guardian_address_line1', 'minor_guardian_address_line2', 'minor_guardian_city', 'minor_guardian_state', 'minor_guardian_pincode', 'minor_guardian_country_id', 'mode_of_operation', 'nomination_flg', 'nominee_name', 'nominee_relationship', 'nominee_add_line1', 'nominee_add_line2', 'nominee_city_cd', 'nominee_minor_guradian_cd', 'nominee_dob', 'nominee_minor_flag', 'amount_open', 'mode_of_payment_open', 'account_no', 'cust_id', 'sqlid', 'finacle_status', 'update_sql_status', 'staff_flg', 'staff_no', 'minor_title_guradian_code', 'passport_details', 'introducer_title_code', 'introducer_code', 'introducer_name', 'existing_cust_flg', 'account_currency_code', 'cust_id_ver_flg', 'account_id_ver_flg', 'schm_code', 'orgaization_type', 'introducer_flg', 'introducer_cust_id', 'cust_currency_code', 'account_type_id', 'boi_account_number', 'ref_num', 'training_partner_name', 'output_file_id', 'prev_output_file_ids', 'aml_status', 'traning_center_name', 'training_center_id', 'debit_mandate_amount', 'aof_ref_num', 'debit_mandate_accout', 'debit_mandate_account', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'boi_card_mapping_id', 'date_blocked', 'status', 'status_bank', 'status_ops', 'status_ecs', 'concat(c.first_name," ",c.last_name) as name'));
          if ($from != '' && $to != ''){
        $select->join(DbTable::TABLE_BOI_CORP_LOAD_REQUEST . " as l","c.id = l.cardholder_id AND l.status = '".STATUS_LOADED."'",array( 'sum(l.amount) as load_amount','l.date_load','sum(amount_available) as wallet_balance','date_cutoff as date_auto_debit','sum(amount_used) as debit_amount_pos','sum(amount_cutoff) as auto_debit_amount'));
          }else{
           $select->joinLeft(DbTable::TABLE_BOI_CORP_LOAD_REQUEST . " as l","c.id = l.cardholder_id AND l.status = '".STATUS_LOADED."'",array( 'sum(l.amount) as load_amount','l.date_load','sum(amount_available) as wallet_balance','date_cutoff as date_auto_debit','sum(amount_used) as debit_amount_pos','sum(amount_cutoff) as auto_debit_amount'));
            
          }
        $select->joinLeft(DbTable::TABLE_AGENTS . " as ag", "c.by_agent_id = ag.id ", array('concat(ag.first_name," ",ag.last_name) as by_agent_name'));
        if ($product != '') {
            $select->where("c.product_id =?", $product );
        }
        if ($refnum != '') {
            $select->where("c.ref_num =? ", $refnum );
        }
        if ($aadhaarNo != '') {
            $select->where("c.aadhaar_no =? ", $aadhaarNo );
        }
        if ($accountNo != '') {
            $select->where("c.account_no =? ", $accountNo );
        }
        
        if ($agentCode != '') {
            $select->where("ag.agent_code =? ", $agentCode );
        } elseif($agentIds != ''){
            $select->where("ag.id IN ($agentIds)" );
        }
          if ($from != '' && $to != ''){
            $select->where("l.date_load >=  '" . $from . "'");
            $select->where("l.date_load <= '" . $to . "'");
           
        }
	$select->group("c.id");
        $i = 0;
        $arrReport = array();
        $select->group("c.id");
        $cardholders = $this->fetchAll($select);
      
        $cardholders = Util::toArray($cardholders);

        foreach( $cardholders as $boiCardholders){
       

           $arrReport[$i]['ref_num'] = $boiCardholders['ref_num']; 
           $arrReport[$i]['date_created'] = $boiCardholders['date_created']; 
           $arrReport[$i]['name'] = $boiCardholders['name']; 
           $arrReport[$i]['nsdc_enrollment_no'] = $boiCardholders['nsdc_enrollment_no']; 
           $arrReport[$i]['aadhaar_no'] = $boiCardholders['aadhaar_no']; 
           $arrReport[$i]['sol_id'] = $boiCardholders['sol_id']; 
           $arrReport[$i]['status_ops'] = $boiCardholders['status_ops']; 
           $arrReport[$i]['status_bank'] = $boiCardholders['status_bank']; 
           $arrReport[$i]['account_no'] = (isset($user->user_type) && $user->user_type == DISTRIBUTOR_AGENT) ? $boiCardholders['account_no'] : Util::maskCard($boiCardholders['account_no'], 4, 0);
           $arrReport[$i]['ifsc_code'] =  ($boiCardholders['account_no'] == '') ? '' : "BKID000".substr($boiCardholders['account_no'], 0, 4);
           $arrReport[$i]['card_number'] = Util::maskCard($boiCardholders['card_number'], 4, 4); 
           $arrReport[$i]['debit_mandate_amount'] = isset($boiCardholders['debit_mandate_amount']) && $boiCardholders['debit_mandate_amount'] > 0 ? Util::numberFormat($boiCardholders['debit_mandate_amount']) : '-'; 
           $arrReport[$i]['date_load'] = isset($boiCardholders['date_load'])? $boiCardholders['date_load'] : ''; 
           $arrReport[$i]['load_amount'] = isset($boiCardholders['load_amount']) && $boiCardholders['load_amount'] > 0 ? Util::numberFormat($boiCardholders['load_amount']) : '-'; 
           $arrReport[$i]['current_wallet_balance'] = isset($boiCardholders['wallet_balance']) && $boiCardholders['wallet_balance'] > 0 ? Util::numberFormat($boiCardholders['wallet_balance']) : '-'; 
           $arrReport[$i]['debit_amount_pos'] = isset($boiCardholders['debit_amount_pos']) && $boiCardholders['debit_amount_pos'] > 0 ? Util::numberFormat($boiCardholders['debit_amount_pos']):'-'; 
           $arrReport[$i]['date_auto_debit'] = isset($boiCardholders['date_auto_debit'])?$boiCardholders['date_auto_debit']: '-'; 
           $arrReport[$i]['auto_debit_amount'] = isset($boiCardholders['auto_debit_amount']) && $boiCardholders['auto_debit_amount'] > 0 ? Util::numberFormat($boiCardholders['auto_debit_amount']):'-'; 
           $arrReport[$i]['by_agent_name'] = $boiCardholders['by_agent_name']; 
           $arrReport[$i]['training_center_id'] = $boiCardholders['training_center_id']; 
           $arrReport[$i]['traning_center_name'] = $boiCardholders['traning_center_name']; 
           $arrReport[$i]['training_partner_name'] = $boiCardholders['training_partner_name'];
           $i++;
           
           }
        
        }catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                       
                }
      
      return $arrReport;
    }

    public function generateTpMisFile() {
        
        
        $objR = New ObjectRelations();
        $seprator = ',';
        $ext = '.csv';
        $fileName = '';
        try{
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BOI_TP_MIS_REPORT . ' as rep');
        $select->where("status =?",STATUS_PENDING);
        $criteria = $this->_db->fetchAll($select);
        $i = 1;
        
        foreach($criteria as $data){
            $file = new Files();                    
            $file->setFilePermission('');
//            $this->updateTpMis(array('status'=> STATUS_STARTED), $data['id']);
            $res = $this->getTPMisBySearchCriteria($data);
            if(!$res['blankFile']){
                // Save File 
            $fileName = 'BOI_TP_MIS_'.$data['id'].$ext;


            $file->setBatch($res['arrReport'], $seprator);
            $file->setFilepath(UPLOAD_PATH_BOI_TP_MIS_REPORTS);
            $file->setFilename($fileName);
            $file->generate(TRUE);            
            
            }
            
           $this->updateTpMis(array('file_name' => $fileName,'status'=> STATUS_PROCESSED,'date_processed' => new Zend_Db_Expr('NOW()'),'remarks' => $res['remarks'].'. '.$res['count'].' records found'), $data['id']);
           $i++; 
        }
        return $i;
        }catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                       
                }
//            $batchMainArr = $acDisMainArr;

        }
       
    
        public function updateTpMis($param,$id){
           return $this->_db->update(DbTable::TABLE_BOI_TP_MIS_REPORT ,$param,"id= $id");
        }
        
       public function getTPMisBySearchCriteria($param) {
        $user = Zend_Auth::getInstance()->getIdentity();
        $tpName = isset($param['tp_name']) ? $param['tp_name'] : '';
        $agentName = isset($param['agent_name']) ? $param['agent_name'] : '';
        $tpCode = isset($param['tp_code']) ? $param['tp_code'] : '';
        $agentCode = isset($param['agent_code']) ? $param['agent_code'] : '';
        $tpmobile = isset($param['tp_mobile']) ? $param['tp_mobile'] : '';
        $agentmobile = isset($param['agent_mobile']) ? $param['agent_mobile'] : '';
        $from = isset($param['wallet_load_from']) ? $param['wallet_load_from'] : '';
        $to = isset($param['wallet_load_to']) ? $param['wallet_load_to'] : '';
        $agentIds = '';
        $remarks = '';
        $blankFile = FALSE;
        $agentModel =  new Agents();
        
        
           $i = 0;
           $arrReport = array();
           $arrReport[$i]['ref_num'] ='AOF Reference Number'; 
           $arrReport[$i]['date_created'] = 'Application Date'; 
           $arrReport[$i]['name'] = 'Name (of the Trainee)'; 
           $arrReport[$i]['nsdc_enrollment_no'] = 'NSDC Enrollment Number'; 
           $arrReport[$i]['aadhaar_no'] = 'Aadhaar No.';
           $arrReport[$i]['sol_id'] = 'Linked Branch ID';
           $arrReport[$i]['status_ops'] = 'Transerv Status';
           $arrReport[$i]['status_bank'] = 'Bank Status';
           $arrReport[$i]['account_no'] = 'Account No.' ;
           $arrReport[$i]['ifsc_code'] =  'IFSC Code';
           $arrReport[$i]['card_number'] = 'Card No.'; 
           $arrReport[$i]['debit_mandate_amount'] = 'Debit Mandate Amount'; 
           $arrReport[$i]['date_load'] = 'NSDC Wallet Load Date'; 
           $arrReport[$i]['load_amount'] = 'NSDC Load Amount'; 
           $arrReport[$i]['current_wallet_balance'] = 'Available Balance on Wallet'; 
           $arrReport[$i]['debit_amount_pos'] = 'Amount debited through POS'; 
           $arrReport[$i]['date_auto_debit'] = 'Wallet Auto Debit Date'; 
           $arrReport[$i]['auto_debit_amount'] = 'Wallet Auto Debit Amount'; 
           $arrReport[$i]['by_agent_name'] = 'Traning Center BC Name';
           $arrReport[$i]['training_center_id'] = 'Training Center ID';
           $arrReport[$i]['traning_center_name'] = 'Training Center Name';
           $arrReport[$i]['training_partner_name'] = 'Training Partner Name';
           
        if ($tpCode != '' && $agentCode == '') {
            $tpDetail = $agentModel->findagentByAgentCode($tpCode);
            if(!empty($tpDetail) && $tpDetail['id'] > 0) {
                $agentIds = $agentModel->getBCListing(
                    array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $tpDetail['id'], 'user_type' => DISTRIBUTOR_AGENT,'ret_type' => 'arr'));
               if(!empty($agentIds)){
                    $agentIds = $agentModel->getBCListing(
                    array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $tpDetail['id'], 'user_type' => DISTRIBUTOR_AGENT));
               }
               else{
                     $blankFile = TRUE;         
                     $remarks = 'No active BCs found under this TP';

               }
            }
            else{
                     $blankFile = TRUE;         
                     $remarks = 'TP does not exist';

               }
        }
        if ($agentCode != '' && $tpCode == '') {
            $agentDetail = $agentModel->findagentByAgentCode($agentCode);
            if(!empty($agentDetail)){
            $agentIds = "'".$agentDetail['id']."'";
            }
             else{
                     $blankFile = TRUE;     
                     $remarks = 'Agent does not exist';

               }
        }
        
        
        if($tpCode != '' && $agentCode != ''){
           $tpDetail = $agentModel->findagentByAgentCode($tpCode);
            $agentDetail = $agentModel->findagentByAgentCode($agentCode);
           
            if(!empty($tpDetail)) {
                $agentIds = $agentModel->getBCListing(
                    array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $tpDetail['id'], 'user_type' => DISTRIBUTOR_AGENT,'ret_type' => 'arr'));
             
                if(!empty($agentIds)){
                  
                    $agentIds = $agentModel->getBCListing(
                    array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $tpDetail['id'], 'user_type' => DISTRIBUTOR_AGENT));
                     $pos = in_array($agentDetail['id'], $agentIds);
                     if ($pos === false) {
                         $blankFile = TRUE;    
                    $remarks = 'No active BCs found under this TP';
                 }
               }
               else{
                     $blankFile = TRUE;         
                     $remarks = 'No active BCs found under this TP';

               }
            }
            else{
                     $blankFile = TRUE;         
                     $remarks = 'TP does not exist';

               } 
        }
        if($tpmobile != '' && $agentmobile == ''){
             $tpDetail = $agentModel->findagentByPhone($tpmobile);
             if(!empty($tpDetail) && $tpDetail['id'] > 0) {
                  if(!empty($agentIds)){
                    $agentIds = $agentModel->getBCListing(
                    array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $tpDetail['id'], 'user_type' => DISTRIBUTOR_AGENT));
               }
               else{
                     $blankFile = TRUE;   
                      $remarks = 'No active BCs found under this TP';

               }
               
               }
                else{
                     $blankFile = TRUE;         
                     $remarks = 'TP does not exist';

               }
              
        }
        if ($agentmobile != '' && $tpmobile == '') {
            $agentDetail = $agentModel->findagentByPhone($agentmobile);
             if(!empty($agentDetail)){
            $agentIds = "'".$agentDetail['id']."'";
            }
             else{
                     $blankFile = TRUE;  
                     $remarks = 'BC does not exist';


               }
        }
       if($agentmobile != '' && $tpmobile != ''){
           $tpDetail = $agentModel->findagentByPhone($tpCode);
            $agentDetail = $agentModel->findagentByPhone($agentCode);
            if(!empty($tpDetail) && $tpDetail['id'] > 0) {
                $agentIds = $agentModel->getBCListing(
                    array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $tpDetail['id'], 'user_type' => DISTRIBUTOR_AGENT,'ret_type' => 'arr'));
               if(!empty($agentIds)){
                    $agentIds = $agentModel->getBCListing(
                    array('status' => STATUS_UNBLOCKED, 'enroll_status' => STATUS_APPROVED, 'user_id' => $tpDetail['id'], 'user_type' => DISTRIBUTOR_AGENT));
                     $pos = in_array($agentDetail['id'], $agentIds);
                     if ($pos === false) {
                         $blankFile = TRUE;    
                    $remarks = 'No active BCs found under this TP';
                 }
               }
               else{
                     $blankFile = TRUE;         
                     $remarks = 'No active BCs found under this TP';

               }
            }
            else{
                     $blankFile = TRUE;         
                     $remarks = 'TP does not exist';

               } 
        }
      
        try{
        if(!$blankFile){
            
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`c`.`card_number`,'".$decryptionKey."') as card_number");
        $crn = new Zend_Db_Expr("AES_DECRYPT(`c`.`crn`,'".$decryptionKey."') as crn");

        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(DbTable::TABLE_BOI_CORP_CARDHOLDER . ' as c', array('id', 'product_id', 'boi_customer_id', 'customer_master_id', 'customer_type', 'xls_boi_customer_id', 'nsdc_enrollment_no', 'sol_id', $crn, 'unicode', $card_number, 'card_pack_id', 'afn', 'member_id', 'employee_id', 'title', 'first_name', 'middle_name', 'last_name', 'name_on_card', 'gender', 'date_of_birth', 'occupation', 'perm_country', 'aadhaar_no', 'pan', 'uid_no', 'mobile', 'email', 'landline', 'address_type', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country_code',
            'mother_maiden_name', 'employer_name', 'employer_address_line1', 'employer_address_line2', 'employer_address_city', 'employer_address_state', 'employer_address_country_id', 'employer_address_pincode', 'employer_contact_no', 'corporate_id', 'comm_address_line1', 'comm_address_line2', 'comm_city', 'comm_pin', 'comm_state', 'comm_country_code', 'nre_flag', 'nre_nationality', 'passport', 'passport_issue_date', 'passport_expiry_date', 'marital_status', 'cust_comm_code', 'other_bank_account_no', 'other_bank_account_type', 'other_bank_name', 'other_bank_branch', 'id_proof_type', 'id_proof_number', 'id_proof_doc_id', 'address_proof_type', 'address_proof_number', 'address_proof_doc_id', 'photo_doc_id', 'other_id_proof', 'by_ops_id', 'by_agent_id', 'batch_id', 'batch_name', 'society_id', 'society_name', 'minor_flg', 'minor_guardian_code', 'minor_guardian_name', 'minor_guardian_address_line1', 'minor_guardian_address_line2', 'minor_guardian_city', 'minor_guardian_state', 'minor_guardian_pincode', 'minor_guardian_country_id', 'mode_of_operation', 'nomination_flg', 'nominee_name', 'nominee_relationship', 'nominee_add_line1', 'nominee_add_line2', 'nominee_city_cd', 'nominee_minor_guradian_cd', 'nominee_dob', 'nominee_minor_flag', 'amount_open', 'mode_of_payment_open', 'account_no', 'cust_id', 'sqlid', 'finacle_status', 'update_sql_status', 'staff_flg', 'staff_no', 'minor_title_guradian_code', 'passport_details', 'introducer_title_code', 'introducer_code', 'introducer_name', 'existing_cust_flg', 'account_currency_code', 'cust_id_ver_flg', 'account_id_ver_flg', 'schm_code', 'orgaization_type', 'introducer_flg', 'introducer_cust_id', 'cust_currency_code', 'account_type_id', 'boi_account_number', 'ref_num', 'training_partner_name', 'output_file_id', 'prev_output_file_ids', 'aml_status', 'traning_center_name', 'training_center_id', 'debit_mandate_amount', 'aof_ref_num', 'debit_mandate_accout', 'debit_mandate_account', 'date_created', 'place_application', 'date_updated', 'delivery_file_id', 'date_activation', 'failed_reason', 'date_failed', 'date_crn_update', 'date_authorize', 'recd_doc', 'date_recd_doc', 'recd_doc_id', 'date_approval', 'boi_card_mapping_id', 'date_blocked', 'status', 'status_bank', 'status_ops', 'status_ecs', 'concat(c.first_name," ",c.last_name) as name'));
          if ($from != '0000-00-00' && $to != '0000-00-00'){
        $select->join(DbTable::TABLE_BOI_CORP_LOAD_REQUEST . " as l","c.id = l.cardholder_id AND l.status = '".STATUS_LOADED."'",array( 'sum(l.amount) as load_amount','l.date_load','sum(amount_available) as wallet_balance','date_cutoff as date_auto_debit','sum(amount_used) as debit_amount_pos','sum(amount_cutoff) as auto_debit_amount'));
          }else{
           $select->joinLeft(DbTable::TABLE_BOI_CORP_LOAD_REQUEST . " as l","c.id = l.cardholder_id AND l.status = '".STATUS_LOADED."'",array( 'sum(l.amount) as load_amount','l.date_load','sum(amount_available) as wallet_balance','date_cutoff as date_auto_debit','sum(amount_used) as debit_amount_pos','sum(amount_cutoff) as auto_debit_amount'));
            
          }
        $select->joinLeft(DbTable::TABLE_AGENTS . " as ag", "c.by_agent_id = ag.id ", array('concat(ag.first_name," ",ag.last_name) as by_agent_name'));
        
        if($agentIds != '') {
        $select->where("ag.id IN (".$agentIds.")" );
        }
          if ($from != '0000-00-00' && $to != '0000-00-00'){
            $select->where("DATE(l.date_load) >=  '" . $from . "'");
            $select->where("DATE(l.date_load) <= '" . $to . "'");
           
        }
        $select->group("c.id");
        
        $cardholders = $this->fetchAll($select);
        $cardholders = Util::toArray($cardholders);
        
     
        $i=1;   
        $j=0;   
        foreach( $cardholders as $boiCardholders){

           $arrReport[$i]['ref_num'] = $boiCardholders['ref_num']; 
           $arrReport[$i]['date_created'] = $boiCardholders['date_created']; 
           $arrReport[$i]['name'] = $boiCardholders['name']; 
           $arrReport[$i]['nsdc_enrollment_no'] = $boiCardholders['nsdc_enrollment_no']; 
           $arrReport[$i]['aadhaar_no'] = $boiCardholders['aadhaar_no']; 
           $arrReport[$i]['sol_id'] = $boiCardholders['sol_id']; 
           $arrReport[$i]['status_ops'] = $boiCardholders['status_ops']; 
           $arrReport[$i]['status_bank'] = $boiCardholders['status_bank']; 
           $arrReport[$i]['account_no'] = (isset($user->user_type) && $user->user_type == DISTRIBUTOR_AGENT) ? $boiCardholders['account_no'] : Util::maskCard($boiCardholders['account_no'], 4, 0);
           $arrReport[$i]['ifsc_code'] =  ($boiCardholders['account_no'] == '') ? '' : "BKID000".substr($boiCardholders['account_no'], 0, 4);
           $arrReport[$i]['card_number'] = Util::maskCard($boiCardholders['card_number'], 4, 4); 
           $arrReport[$i]['debit_mandate_amount'] = isset($boiCardholders['debit_mandate_amount']) && $boiCardholders['debit_mandate_amount'] > 0 ? Util::numberFormat($boiCardholders['debit_mandate_amount'],FLAG_NO) : '-'; 
           $arrReport[$i]['date_load'] = isset($boiCardholders['date_load'])? $boiCardholders['date_load'] : ''; 
           $arrReport[$i]['load_amount'] = isset($boiCardholders['load_amount']) && $boiCardholders['load_amount'] > 0 ? Util::numberFormat($boiCardholders['load_amount'],FLAG_NO) : '-'; 
           $arrReport[$i]['current_wallet_balance'] = isset($boiCardholders['wallet_balance']) && $boiCardholders['wallet_balance'] > 0 ? Util::numberFormat($boiCardholders['wallet_balance'],FLAG_NO) : '-'; 
           $arrReport[$i]['debit_amount_pos'] = isset($boiCardholders['debit_amount_pos']) && $boiCardholders['debit_amount_pos'] > 0 ? Util::numberFormat($boiCardholders['debit_amount_pos'],FLAG_NO):'-'; 
           $arrReport[$i]['date_auto_debit'] = isset($boiCardholders['date_auto_debit'])?$boiCardholders['date_auto_debit']: '-'; 
           $arrReport[$i]['auto_debit_amount'] = isset($boiCardholders['auto_debit_amount']) && $boiCardholders['auto_debit_amount'] > 0 ? Util::numberFormat($boiCardholders['auto_debit_amount'],FLAG_NO):'-'; 
           $arrReport[$i]['by_agent_name'] = $boiCardholders['by_agent_name']; 
           $arrReport[$i]['training_center_id'] = $boiCardholders['training_center_id']; 
           $arrReport[$i]['traning_center_name'] = $boiCardholders['traning_center_name']; 
           $arrReport[$i]['training_partner_name'] = $boiCardholders['training_partner_name'];
           $i++;
           $j++;
           
           }
           }
        /*else{
             $reportTpMIS = array('remarks' => $reason);
        $this->updateTpMis($reportTpMIS, $param['id']);
        }*/
   
       
        }catch (Exception $e) {
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                       
                }
      
                $retArr = array(
                  'arrReport'  => $arrReport,
                  'blankFile'     => $blankFile,
                  'remarks' => $remarks,
                  'count' => $i
                );
      return $retArr;
    }

    public function updateAmlBOICardholders(){
        $details = $this->select()
                       ->from($this->_name,array('id','concat(first_name," ",last_name) as name'))
                       ->where ("last_name <>''")
                       ->where ("status ='".STATUS_ACTIVE."'")
                       ->where ("aml_status ='".STATUS_AML."'")
                       ->order('date_created ASC');

                       $results = $this->fetchAll($details);
                       $reportsData = array();
                       foreach($results AS $data){
                           $select = $this->_db->select()
                                       ->from(DbTable::TABLE_AML_MASTER." AS a" , array('*'))
                                       ->where('a.full_name LIKE ?',$data['name']) 
                                       ->Orwhere('a.fake_names LIKE ?','%'.$data['name'].'%');

                               $row = $this->_db->fetchRow($select);

                               if($row['id']){
                                   $this->update(array('aml_status' => STATUS_IS_AML), 'id='.$data['id']);	
                               } else {
                                   $this->update(array('aml_status' => STATUS_AML_UPDATE), 'id='.$data['id']);	
                               }
                          }
        return $reportsData;
    }
}   

